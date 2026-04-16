<?php


namespace App\Http\Controllers\ProductionDevelopment;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Station;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionDevelopmentController extends Controller
{
  public function index($folder, $page = null)
{
    // === Вариант 1 ===
    if ($page === null) {

        $slug = $folder;

        $allowedPages = ['index', 'seasons', 'zima', 'leto', 'ckim', 'zdaniya', 'lighting', 'boilers'];

        if (!in_array($slug, $allowedPages)) {
            abort(404);
        }

        return view("productionDevelopment.$slug");
    }

    // === Вариант 2 ===
    $view = "productionDevelopment.$folder.$page";

    if (!view()->exists($view)) {
        abort(404);
    }

    // 👉 ВАЖНО: только для страницы часов
    if ($folder === 'clock') {
    $regions = \App\Models\Region::all();
    return view($view, compact('regions'));
}

    return view($view);
}	
	
	/*
	public function index(Request $request)
    {
        return view('productionDevelopment.index');
    //, compact();	
	
	}*/
	

}
