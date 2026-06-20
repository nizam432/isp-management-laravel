<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\ResellerTutorial;

class ResellerTutorialController extends Controller
{
    public function index()
    {
        $tutorials = ResellerTutorial::active()->orderBy('sort_order')->orderByDesc('id')->get();
        return view('reseller.tutorials.index', compact('tutorials'));
    }
}
