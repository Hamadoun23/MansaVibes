<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class CommerceWebController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->orderBy('name')->paginate(20);

        return view('modules.commerce.index', compact('products'));
    }
}
