<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserVisit;
use App\Models\UserVisitExclusion;
use App\Services\UserVisitService;
use Illuminate\Http\Request;

/**
 * =====================================================================
 * КОНТРОЛЛЕР: Админ-панель учёта посещений (v2)
 * =====================================================================
 * 
 * Добавлено:
 * - Управление исключениями
 * - Очистка данных
 */
class UserVisitsController extends Controller
{
    protected UserVisitService $visitService;

    public function __construct(UserVisitService $visitService)
    {
        $this->visitService = $visitService;
    }

    /**
     * Главная страница - обзор и статистика
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));
        $userId = $request->input('user_id');
        $ipAddress = $request->input('ip_address');

        $statistics = $this->visitService->getStatistics(
            \Carbon\Carbon::parse($dateFrom)->startOfDay(),
            \Carbon\Carbon::parse($dateTo)->endOfDay()
        );

        $activeSessions = UserVisit::active()
            ->with('user')
            ->orderBy('session_start', 'desc')
            ->limit(10)
            ->get();

        $todayVisits = UserVisit::today()
            ->with('user')
            ->orderBy('session_start', 'desc')
            ->get();

        $visitsQuery = UserVisit::with('user')
            ->whereBetween('session_start', [
                \Carbon\Carbon::parse($dateFrom)->startOfDay(),
                \Carbon\Carbon::parse($dateTo)->endOfDay()
            ]);

        if ($userId) {
            $visitsQuery->where('user_id', $userId);
        }

        if ($ipAddress) {
            $visitsQuery->where('ip_address', 'like', "%{$ipAddress}%");
        }

        $visits = $visitsQuery->orderBy('session_start', 'desc')
            ->paginate(20)
            ->withQueryString();

        $users = User::orderBy('name')->get();

        $topUsers = UserVisit::selectRaw('user_id, user_name, COUNT(*) as visits_count, SUM(pages_count) as total_pages')
            ->whereBetween('session_start', [
                \Carbon\Carbon::parse($dateFrom)->startOfDay(),
                \Carbon\Carbon::parse($dateTo)->endOfDay()
            ])
            ->groupBy('user_id', 'user_name')
            ->orderByDesc('visits_count')
            ->limit(10)
            ->get();

        $topPages = $this->getTopPages($dateFrom, $dateTo, 10);

        // Количество исключений для бейджа
        $exclusionsCount = UserVisitExclusion::active()->count();

        return view('admin.user-visits.index', compact(
            'statistics',
            'activeSessions',
            'todayVisits',
            'visits',
            'users',
            'topUsers',
            'topPages',
            'dateFrom',
            'dateTo',
            'userId',
            'ipAddress',
            'exclusionsCount'
        ));
    }

    /**
     * Детали конкретной сессии
     */
    public function show(UserVisit $visit)
    {
        return view('admin.user-visits.show', compact('visit'));
    }

    /**
     * История посещений конкретного пользователя
     */
    public function userHistory(User $user, Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $visits = UserVisit::byUser($user->id)
            ->whereBetween('session_start', [
                \Carbon\Carbon::parse($dateFrom)->startOfDay(),
                \Carbon\Carbon::parse($dateTo)->endOfDay()
            ])
            ->orderBy('session_start', 'desc')
            ->paginate(20)
            ->withQueryString();

        $userStats = [
            'total_visits' => UserVisit::byUser($user->id)->count(),
            'total_pages' => UserVisit::byUser($user->id)->sum('pages_count'),
            'avg_duration' => UserVisit::byUser($user->id)->avg('duration_seconds') ?? 0,
            'first_visit' => UserVisit::byUser($user->id)->min('session_start'),
            'last_visit' => UserVisit::byUser($user->id)->max('session_start'),
            'unique_ips' => UserVisit::byUser($user->id)->distinct('ip_address')->count('ip_address'),
        ];

        // Проверяем, исключён ли пользователь
        $isExcluded = UserVisitExclusion::isExcluded($user->id);
        $exclusion = UserVisitExclusion::where('user_id', $user->id)->first();

        return view('admin.user-visits.user-history', compact(
            'user',
            'visits',
            'userStats',
            'dateFrom',
            'dateTo',
            'isExcluded',
            'exclusion'
        ));
    }

    /**
     * Онлайн пользователи
     */
    public function online()
    {
        $activeSessions = UserVisit::active()
            ->with('user')
            ->orderBy('session_end', 'desc')
            ->get();

        return view('admin.user-visits.online', compact('activeSessions'));
    }

    /**
     * Закрыть устаревшие сессии
     */
    public function closeStaleSessions()
    {
        $closed = $this->visitService->closeStaleSessions();
        return back()->with('success', "Закрыто устаревших сессий: {$closed}");
    }

    /**
     * Завершить конкретную сессию
     */
    public function endSession(UserVisit $visit)
    {
        if ($visit->is_active) {
            $visit->endSession();
            return back()->with('success', 'Сессия завершена');
        }
        return back()->with('info', 'Сессия уже была завершена');
    }

    /**
     * ================================================================
     * УПРАВЛЕНИЕ ИСКЛЮЧЕНИЯМИ
     * ================================================================
     */

    /**
     * Страница управления исключениями
     */
    public function exclusions()
    {
        $exclusions = $this->visitService->getExclusions();
        
        // Пользователи, которых можно добавить в исключения
        $excludedUserIds = UserVisitExclusion::pluck('user_id')->toArray();
        $availableUsers = User::whereNotIn('id', $excludedUserIds)
            ->orderBy('name')
            ->get();

        return view('admin.user-visits.exclusions', compact('exclusions', 'availableUsers'));
    }

    /**
     * Добавить пользователя в исключения
     */
    public function addExclusion(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:255',
        ]);

        $this->visitService->addExclusion(
            $request->input('user_id'),
            $request->input('reason')
        );

        $user = User::find($request->input('user_id'));

        return back()->with('success', "Пользователь {$user->name} добавлен в исключения");
    }

    /**
     * Удалить пользователя из исключений
     */
    public function removeExclusion(UserVisitExclusion $exclusion)
    {
        $userName = $exclusion->user->name ?? 'Неизвестный';
        
        $this->visitService->removeExclusion($exclusion->user_id);

        return back()->with('success', "Пользователь {$userName} удалён из исключений");
    }

    /**
     * Переключить статус исключения
     */
    public function toggleExclusion(UserVisitExclusion $exclusion)
    {
        if ($exclusion->is_active) {
            $this->visitService->deactivateExclusion($exclusion->user_id);
            $message = "Исключение для {$exclusion->user->name} деактивировано";
        } else {
            $this->visitService->activateExclusion($exclusion->user_id);
            $message = "Исключение для {$exclusion->user->name} активировано";
        }

        return back()->with('success', $message);
    }

    /**
     * ================================================================
     * ОЧИСТКА ДАННЫХ
     * ================================================================
     */

    /**
     * Страница очистки данных
     */
    public function cleanup()
    {
        $tableStats = $this->visitService->getTableStats();
        $users = User::orderBy('name')->get();

        return view('admin.user-visits.cleanup', compact('tableStats', 'users'));
    }

    /**
     * Предпросмотр очистки
     */
    public function cleanupPreview(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $preview = $this->visitService->getCleanupPreview(
            $request->input('date_from'),
            $request->input('date_to'),
            $request->input('user_id')
        );

        return response()->json($preview);
    }

    /**
     * Выполнить очистку по периоду
     */
    public function cleanupExecute(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'user_id' => 'nullable|exists:users,id',
            'confirm' => 'required|accepted',
        ]);

        $deleted = $this->visitService->deleteByPeriod(
            $request->input('date_from'),
            $request->input('date_to'),
            $request->input('user_id')
        );

        return back()->with('success', "Удалено записей: {$deleted}");
    }

    /**
     * Очистить старые записи (старше N дней)
     */
    public function cleanupOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:3650',
            'confirm' => 'required|accepted',
        ]);

        $deleted = $this->visitService->deleteOlderThan($request->input('days'));

        return back()->with('success', "Удалено записей старше {$request->input('days')} дней: {$deleted}");
    }

    /**
     * Удалить все данные пользователя
     */
    public function deleteUserData(User $user, Request $request)
    {
        $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $deleted = $this->visitService->deleteUserData($user->id);

        return back()->with('success', "Удалено {$deleted} записей пользователя {$user->name}");
    }

    /**
     * ================================================================
     * ЭКСПОРТ
     * ================================================================
     */

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $visits = UserVisit::whereBetween('session_start', [
            \Carbon\Carbon::parse($dateFrom)->startOfDay(),
            \Carbon\Carbon::parse($dateTo)->endOfDay()
        ])->orderBy('session_start', 'desc')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Посещения');

        $headers = ['№', 'Пользователь', 'Email', 'IP', 'Начало сессии', 'Конец сессии', 'Длительность', 'Страниц', 'Статус'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4472C4');
        $sheet->getStyle('A1:I1')->getFont()->getColor()->setRGB('FFFFFF');

        $row = 2;
        foreach ($visits as $index => $visit) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $visit->user_name);
            $sheet->setCellValue('C' . $row, $visit->user_email);
            $sheet->setCellValue('D' . $row, $visit->ip_address);
            $sheet->setCellValue('E' . $row, $visit->session_start->format('d.m.Y H:i:s'));
            $sheet->setCellValue('F' . $row, $visit->session_end ? $visit->session_end->format('d.m.Y H:i:s') : '-');
            $sheet->setCellValue('G' . $row, $visit->getDurationFormatted());
            $sheet->setCellValue('H' . $row, $visit->pages_count);
            $sheet->setCellValue('I' . $row, $visit->is_active ? 'Активна' : 'Завершена');
            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'user_visits_' . $dateFrom . '_' . $dateTo . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * ================================================================
     * ПРИВАТНЫЕ МЕТОДЫ
     * ================================================================
     */

    private function getTopPages(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $visits = UserVisit::whereBetween('session_start', [
            \Carbon\Carbon::parse($dateFrom)->startOfDay(),
            \Carbon\Carbon::parse($dateTo)->endOfDay()
        ])->get();

        $pages = [];
        foreach ($visits as $visit) {
            if ($visit->visited_pages) {
                foreach ($visit->visited_pages as $page) {
                    $url = $page['url'] ?? '';
                    if (!isset($pages[$url])) {
                        $pages[$url] = [
                            'url' => $url,
                            'title' => $page['title'] ?? $url,
                            'count' => 0
                        ];
                    }
                    $pages[$url]['count']++;
                }
            }
        }

        usort($pages, fn($a, $b) => $b['count'] <=> $a['count']);

        return array_slice($pages, 0, $limit);
    }
}
