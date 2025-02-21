<?php

namespace App\Http\Controllers\Maintenance\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class MasterTarif extends Controller
{
    public function index()
    {
        return view('maintenance.master.master-tarif.index');
    }

    public function datatable(Request $request): JsonResponse
    {

        $query_list = "SELECT * FROM GROUP_TARIF";

        $container =  DB::connection('uster')->select($query_list);
        return Datatables::of($container)
            ->addColumn('action', function ($listStuffing) {
                $url = route('uster.maintenance.master_tarif.detail_tarif', ['group_tarif' => $listStuffing->id_group_tarif]);
                return "<a href='$url' class='btn btn-primary btn-sm' >Edit";
            })
            ->make(true);
    }

    public function detail_tarif(Request $request)
    {
        $id_group_tarif    = $request->group_tarif;

        $query_list = "SELECT CONCAT('CONTAINER ', TO_CHAR(a.SIZE_)) KATEGORI_TARIF, a.TYPE_, a.STATUS, b.TARIF,  b.ID_ISO
        FROM ISO_CODE a, MASTER_TARIF b, GROUP_TARIF c
        WHERE a.ID_ISO = b.ID_ISO
        AND c.ID_GROUP_TARIF = b.ID_GROUP_TARIF
        AND b.ID_GROUP_TARIF = '$id_group_tarif'
        ORDER BY a.SIZE_, a.TYPE_, a.STATUS DESC";

        $kategori = "SELECT KATEGORI_TARIF FROM GROUP_TARIF WHERE ID_GROUP_TARIF = '$id_group_tarif'";

        $kategori_    = DB::connection('uster')->selectOne($kategori);

        $row_list    = DB::connection('uster')->select($query_list);
        return view('maintenance.master.master-tarif.detail_tarif', compact('row_list', 'kategori_','id_group_tarif'));
    }

    public function detail(Request $request)
    {
        $id_group_tarif    = $request->id_group_tarif;
        $id_iso    = $request->id_iso;

        $query_list = "SELECT a.ID_ISO, a.ID_GROUP_TARIF, b.KATEGORI_TARIF, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND a.ID_GROUP_TARIF = '$id_group_tarif' AND ID_ISO = '$id_iso'";

    
        $row    = DB::connection('uster')->selectOne($query_list);
        
        return view('maintenance.master.master-tarif.detail', compact('row'));
    }
}
