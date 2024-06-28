<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActualView;
use App\Models\PlanningView;
use App\Models\NgView;
use App\Models\StdView;

class ViewController extends Controller
{
    public function actual()
    {
        $item = ActualView::all();
        return view('view.actual', compact('item'));
    }

    public function plan(){
        $item = PlanningView::all();
        return view('view.plan', compact('item'));
    }

    public function ng(){
        $item = NgView::all();
        return view('view.ng', compact('item'));
    }

    public function std(){
        $item = StdView::all();
        return view('view.std', compact('item'));
    }
}
