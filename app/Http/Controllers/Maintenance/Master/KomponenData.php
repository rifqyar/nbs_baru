<?php

namespace App\Http\Controllers\Maintenance\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class KomponenData extends Controller
{
    public function index()
    {
        $komp_nota = "SELECT a.ID_NOTA,a.KODE_NOTA, a.KATEGORI_NOTA JENIS_NOTA
        FROM MASTER_NOTA a";

        $nota = "SELECT a.ID_NOTA, a.NAMA_NOTA FROM MASTER_NOTA a";


        $row_list    = DB::connection('uster')->select($komp_nota);

        $notaa          = DB::connection('uster')->select($nota);

        return view('maintenance.master.komponen-data.index', compact('row_list', 'notaa'));
    }

    public function detail_komp_nota(Request $request)
    {

        $id_nota    = $request->id_nota;

        $komp_nota = "SELECT ID_NOTA, ID_KOMP_NOTA,KOMPONEN_NOTA, STATUS FROM MASTER_KOMP_NOTA WHERE ID_NOTA = '$id_nota' ORDER BY KOMPONEN_NOTA DESC";
        $nota = "SELECT NAMA_NOTA, KATEGORI_NOTA FROM MASTER_NOTA WHERE ID_NOTA = '$id_nota'";


        $row_list    = DB::connection('uster')->select($komp_nota);

        $notaa          = DB::connection('uster')->selectOne($nota);

        return view('maintenance.master.komponen-data.detail_komp_nota', compact('row_list', 'notaa', 'id_nota'));
    }

    public function edit(Request $request)
    {
        $id_nota    = $_GET["id_nota"];
        $komp_nota     = $_GET["komp_nota"];

        $komp_nota = "SELECT a.ID_KOMP_NOTA,a.STATUS, a.KOMPONEN_NOTA, a.ID_NOTA
                                FROM MASTER_KOMP_NOTA a
								WHERE a.ID_NOTA = '$id_nota' AND a.ID_KOMP_NOTA = '$komp_nota'";

        $row_list    = DB::connection('uster')->selectOne($komp_nota);

        $nama_nota = "SELECT NAMA_NOTA FROM MASTER_NOTA WHERE ID_NOTA = '$id_nota'";

        $row_list2    = DB::connection('uster')->selectOne($nama_nota);

        $nama_nota         = $row_list2->nama_nota;

        return view('maintenance.master.komponen-data.edit', compact('row_list', 'nama_nota'));
    }
}
