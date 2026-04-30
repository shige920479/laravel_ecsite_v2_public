<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;

class ReviewPageController extends Controller
{
    public function index(Item $item)
    {
        return view('user.reviews.index', ['itemId' => $item->id]);
    }
}
