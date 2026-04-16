<?php
namespace App\Http\Controllers\VksController;

use App\Http\Controllers\Controller;
use App\Models\vksnew2\Vks;
use App\Models\vksnew2\VksKab;
use App\Models\vksnew2\VksPrior;
use App\Models\vksnew2\VksAttach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class vksnew2 extends Controller
{
    // ── Текущий пользователь (берём из Laravel Auth портала) ──────────────
    private function currentUser(): array
{
    $user = auth()->user();
    return [
        'id'   => (string)($user->id ?? '1'),
        'user' => $user->name ?? 'unknown',
        'fio'  => $user->name ?? 'Пользователь',
        'vks'  => [
            'list'          => 1,
            'edit'          => 1,
            'recipientlist' => 1,
            'closestatus'   => 1,
        ],
    ];
}
private function canDelete(): bool
{
    $user = auth()->user();
    //return (int)($user->status ?? 0) >= User::STATUS_DZV_MANAGER;
	return (int)($user->status ?? 0) >= User::STATUS_MIN;
}
    // ── Список заявок ────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $login = $this->currentUser();
        $year  = (int)$request->get('y', 0);
        $month = (int)$request->get('m', 0);

        if (!$year && !$month) {
            $year  = (int)date('Y');
            $month = (int)date('n');
        }

        $query = Vks::with('status');

        if (($login['vks']['list'] ?? 0) !== 1) {
            $query->where('userid', $login['id']);
        }

        if ($year && $month) {
            $start = mktime(0,0,0,$month,1,$year);
            $end   = $month == 12
                ? mktime(0,0,0,1,1,$year+1)
                : mktime(0,0,0,$month+1,1,$year);
            $query->whereBetween('datan', [$start, $end - 1]);
        } elseif ($year) {
            $start = mktime(0,0,0,1,1,$year);
            $end   = mktime(0,0,0,1,1,$year+1);
            $query->whereBetween('datan', [$start, $end - 1]);
        }

        $vksList = $query->orderBy('datan','desc')->paginate(20);

        $years = DB::connection('mysql2')
            ->table('vks')
            ->selectRaw("FROM_UNIXTIME(datan, '%Y') as y, COUNT(*) as cnt")
            ->groupBy('y')->orderByDesc('y')->get();

        $months = collect();
        if ($year) {
            $ystart = mktime(0,0,0,1,1,$year);
            $yend   = mktime(0,0,0,1,1,$year+1);
            $months = DB::connection('mysql2')
                ->table('vks')
                ->selectRaw("FROM_UNIXTIME(datan, '%m') as m, COUNT(*) as cnt")
                ->whereBetween('datan', [$ystart, $yend - 1])
                ->groupBy('m')->orderByDesc('m')->get();
        }

        return view('vksnew2.index', compact(
            'vksList','years','months','year','month','login'
        ))->with('canDelete', $this->canDelete());
    }

    // ── Просмотр заявки ──────────────────────────────────────────────────
    public function view(int $id)
    {
        $login = $this->currentUser();
        $prior = VksPrior::where('userid', $login['id'])->first();
        if ($prior) $login['vks']['list'] = (int)$prior->alllist;

        $query = Vks::where('id', $id);
        if (($login['vks']['list'] ?? 0) !== 1) {
            $query->where('userid', $login['id']);
        }
        $vks = $query->firstOrFail();

        $kab         = VksKab::find($vks->kab);
        $attachments = $vks->attach
            ? VksAttach::where('vksid', $id)->get()
            : collect();

        return view('vksnew2.view', compact('vks','kab','attachments','login'));
    }

    // ── Форма добавления ─────────────────────────────────────────────────
    public function addForm()
    {
        $kabs = VksKab::where('on', 1)->orderBy('order')->orderBy('name')->get();
        return view('vksnew2.add', compact('kabs'));
    }

    // ── Сохранение новой заявки ──────────────────────────────────────────
    public function addStore(Request $request)
    {
        $login = $this->currentUser();

        $request->validate([
            'title'      => 'required|string|max:255',
            'organ'      => 'required|string|max:255',
            'date'       => 'required|string',
            'time_start' => 'required|regex:/^\d{1,2}:\d{2}$/',
            'time_end'   => 'required|regex:/^\d{1,2}:\d{2}$/',
            'kab'        => 'required|integer',
        ], [
            'title.required'      => 'Укажите название',
            'organ.required'      => 'Укажите организатора',
            'date.required'       => 'Укажите дату',
            'time_start.required' => 'Укажите время начала',
            'time_end.required'   => 'Укажите время окончания',
            'time_start.regex'    => 'Формат времени: чч:мм',
            'time_end.regex'      => 'Формат времени: чч:мм',
        ]);

        $date  = $request->input('date');
        $vrn   = $request->input('time_start');
        $vrk   = $request->input('time_end');
        $datan = strtotime("$date $vrn");
        $datak = strtotime("$date $vrk");

        if (!$datan || !$datak || $datan >= $datak) {
            return back()
                ->withErrors(['time' => 'Некорректные даты или время конца раньше начала'])
                ->withInput();
        }

        $overlap = Vks::where('kab', $request->kab)
            ->whereNotIn('status', [3])
            ->where('datan', '<', $datak)
            ->where('datak', '>', $datan)
            ->exists();

        if ($overlap) {
            return back()
                ->withErrors(['time' => 'Кабинет уже занят в это время'])
                ->withInput();
        }

        $vks = Vks::create([
            'title'     => $request->input('title'),
            'organ'     => $request->input('organ'),
            'userid'    => $login['id'],
            'zakfio'    => $login['fio'],
            'datan'     => $datan,
            'datan_str' => "$date $vrn",
            'datak'     => $datak,
            'datak_str' => "$date $vrk",
            'datadob'   => time(),
            'kab'       => $request->input('kab'),
            'koment'    => $request->input('comment',''),
            'status'    => 1,
            'attach'    => 0,
        ]);

        // Файлы
        if ($request->hasFile('files')) {
            $hasAttach = false;
            foreach ($request->file('files') as $file) {
                if (!$file || !$file->isValid()) continue;
                $hash = md5(uniqid());
                $ext  = $file->getClientOriginalExtension();
                $name = $file->getClientOriginalName();
                $att  = VksAttach::create([
                    'hash'     => $hash,
                    'vksid'    => $vks->id,
                    'name'     => $name,
                    'filename' => $name,
                    'type'     => $ext,
                    'size'     => $file->getSize(),
                ]);
                $file->storeAs("vks/{$vks->id}", "{$att->id}_$name", 'public');
                $hasAttach = true;
            }
            if ($hasAttach) $vks->update(['attach' => 1]);
        }

        return redirect()->route('vksnew2.index');
    }

    // ── Удаление ─────────────────────────────────────────────────────────
    public function delete(int $id)
    {
        if (!$this->canDelete()) {
            return redirect()->route('vksnew2.index');
        }
        Vks::findOrFail($id)->delete();
        return redirect()->route('vksnew2.index');
    }

    // ── Закрыть просроченные заявки ──────────────────────────────────────
    public function closeStatus()
    {
        $login = $this->currentUser();
        $prior = VksPrior::where('userid', $login['id'])->first();

        if ($prior && $prior->closestatus == 1) {
            Vks::where('status', 1)
               ->where('datan', '<', time())
               ->update(['status' => 2]);
        }

        return redirect()->route('vksnew2.index');
    }

    // ── Список рассылки ───────────────────────────────────────────────────
    public function recipientForm()
    {
        $login = $this->currentUser();
        $prior = VksPrior::where('userid', $login['id'])->first();

        if (!$prior || $prior->recipientlist != 1) {
            return redirect()->route('vksnew2.index');
        }

        $path = storage_path('app/vks/recipientlist.txt');
        $list = file_exists($path) ? file_get_contents($path) : '';

        return view('vksnew2.recipient', compact('list'));
    }

    public function recipientSave(Request $request)
    {
        $login = $this->currentUser();
        $prior = VksPrior::where('userid', $login['id'])->first();

        if (!$prior || $prior->recipientlist != 1) {
            return redirect()->route('vksnew2.index');
        }

        $path = storage_path('app/vks/recipientlist.txt');
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $request->input('recipient_list',''));

        return redirect()->route('vksnew2.recipient');
    }
}