<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $vokzalList = $this->getUniqueVokzalList();
		$rdzvList = $this->getUniqueRdzvList();
        return view('index', compact(
		'rdzvList',
		'vokzalList'
		
		));
    }


// Возвращает список вокзалов по выбранному РДЖВ
public function getVokzalsByRdzv(Request $request)
{
    $rdzv = $request->query('rdzv');

    if (!$rdzv || $rdzv === 'all') {
        return response()->json([]); // Если "все" — не показываем вокзалы
    }

    $vokzals = DB::table('preparationdatazima')
        ->where('rdzv', $rdzv)
        ->select('vokzal')
        ->distinct()
        ->orderBy('vokzal')
        ->pluck('vokzal')
        ->toArray();

    return response()->json($vokzals);
}


    // Вспомогательный метод: уникальные вокзалы
    private function getUniqueVokzalList()
    {
        return DB::table('preparationdatazima')
            ->select('vokzal')
            ->distinct()
            ->orderBy('vokzal')
            ->pluck('vokzal')
            ->toArray();
    }
	
	// Вспомогательный метод: уникальные РДЖВ
    private function getUniqueRdzvList()
    {
        return DB::table('preparationdatazima')
            ->select('rdzv')
            ->distinct()
            ->orderBy('rdzv')
            ->pluck('rdzv')
            ->toArray();
    }

    // 1) Свод по РДЖВ
    public function getZimaByRegion(Request $request)
    {
        $query = DB::table('preparationdatazima')
            ->select(
                'rdzv',
                DB::raw('SUM(plan_value) as total_plan'),
                DB::raw('SUM(fact_value) as total_fact')
            )
            ->groupBy('rdzv');

        if ($request->has('rdzv')) {
            $rdzvFilter = $request->input('rdzv');
            if (is_array($rdzvFilter)) {
                $query->whereIn('rdzv', $rdzvFilter);
            } else {
                $query->where('rdzv', $rdzvFilter);
            }
        }

        $data = $query
            ->orderByDesc('total_fact')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    // 2) Свод по видам работ
public function getZimaByWork(Request $request)
{
    $query = DB::table('preparationdatazima')
        ->select(
            'name_work',
            DB::raw('SUM(plan_value) as total_plan'),
            DB::raw('SUM(fact_value) as total_fact')
        )
        ->groupBy('name_work');

    // Фильтр по РДЖВ
    if ($request->has('rdzv')) {
        $rdzv = $request->input('rdzv');
        $query->where('rdzv', $rdzv);
    }

    // Фильтр по вокзалу
    if ($request->has('vokzal')) {
        $vokzal = $request->input('vokzal');
        $query->where('vokzal', $vokzal);
    }

    $data = $query
        ->orderByDesc('total_fact')
        ->limit(10)
        ->get();

    return response()->json($data);
}

    // 3) Общий итог
    public function getZimaSummary(Request $request)
    {
        $query = DB::table('preparationdatazima')
            ->select(
                DB::raw('SUM(plan_value) as total_plan'),
                DB::raw('SUM(fact_value) as total_fact')
            );

        if ($request->has('rdzv')) {
            $rdzvFilter = $request->input('rdzv');
            if (is_array($rdzvFilter)) {
                $query->whereIn('rdzv', $rdzvFilter);
            } else {
                $query->where('rdzv', $rdzvFilter);
            }
        }

        $row = $query->first();
        return response()->json($row);
    }
}