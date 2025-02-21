<?php

namespace App\Http\Controllers\Tca;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TcaByCancelationController extends Controller
{
    public function index()
    {
        return view('tca.tca_by_cancelation');
    }
}
