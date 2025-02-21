<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DisableContainerController extends Controller
{
    function index()
    {
        return view('maintenance.disable-container');
    }

    function getContainer(Request $request)
    {
        $no_cont = strtoupper($request->search);
        $query            = "select master_container.no_container, master_container.size_, master_container.type_, container_delivery.status, container_delivery.no_request,  
        case when to_date(to_char(nota_delivery.tgl_nota,'DD-MM-RRRR'),'DD-MM-RRRR') < to_date('01-06-2013','DD-MM-RRRR') then nota_delivery.no_nota
        else nota_delivery.no_faktur end as no_nota 
        from master_container, container_delivery, nota_delivery where master_container.no_container = container_delivery.no_container
        and container_delivery.aktif = 'Y' and container_delivery.no_request = nota_delivery.no_request and nota_delivery.lunas = 'YES'
        and nota_delivery.status <> 'BATAL'
        and container_delivery.no_container LIKE '%$no_cont%'";
        return DB::connection('uster')->select($query);
    }

    function disableContainer(Request $request)
    {
        $no_cont = $request->NO_CONT;
        $no_req = $request->NO_REQ;
        $Q_cek = "SELECT COUNT(*) JUM FROM (SELECT NO_CONTAINER FROM GATE_OUT WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req'
        UNION SELECT NO_CONTAINER FROM BORDER_GATE_OUT WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_req')";
        $r_cek = DB::connection('uster')->selectOne($Q_cek);
        if ($r_cek->jum == 0) {
            DB::connection('uster')->update("UPDATE CONTAINER_DELIVERY SET CONTAINER_DELIVERY.AKTIF='T' WHERE CONTAINER_DELIVERY.NO_CONTAINER = '$no_cont'");
            echo "OK";
            exit();
        } else {
            echo "GATO";
            exit();
        }
    }
}
