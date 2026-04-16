<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // считаю, что здесь нужно отдать dashboard.blade.php,
        // а в нем через @include подключается includes.dashboard_03
        //return view('includes.dashboard');
		//dd($this->getZimaByRegion());
		return view('index');
    }

    // 1) Свод по РДЖВ (для Bar chart)
    public function getZimaByRegion()
    {
        $data = DB::table('preparationdatazima')
            ->select(
                'rdzv',
                DB::raw('SUM(plan_value) as total_plan'),
                DB::raw('SUM(fact_value) as total_fact')
            )
            ->groupBy('rdzv')
            ->orderBy('rdzv')
            ->get();

        return response()->json($data);
    }

    // 2) Свод по видам работ (для Line chart)
    public function getZimaByWork()
    {
        $data = DB::table('preparationdatazima')
            ->select(
                'name_work',
                DB::raw('SUM(plan_value) as total_plan'),
                DB::raw('SUM(fact_value) as total_fact')
            )
            ->groupBy('name_work')
            ->orderBy('name_work')
            ->get();

        return response()->json($data);
    }

    // 3) Общий план/факт (для Donut chart)
    public function getZimaSummary()
    {
        $row = DB::table('preparationdatazima')
            ->select(
                DB::raw('SUM(plan_value) as total_plan'),
                DB::raw('SUM(fact_value) as total_fact')
            )
            ->first();

        return response()->json($row);
    }
}
