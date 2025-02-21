<?php

namespace App\Http\Controllers\Maintenance\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterMLO extends Controller
{
    
    public function index()
    {
        $query = "SELECT * FROM MASTER_CONTAINER WHERE MLO = 'MLO'";

        $containers          = DB::connection('uster')->select($query);

        return view('maintenance.master.register_container_mlo',compact('containers'));
    }
}
