<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use App\Services\Print\CetakSP2;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class CetakSP2Controller extends Controller
{
    protected $service;
    public function __construct(CetakSP2 $cetakSP2)
    {
        $this->service = $cetakSP2;
    }

    public function index()
    {
        return view('print.sp2.index');
    }

    public function data(Request $request)
    {
        $data = $this->service->getData($request->all());

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tgl_request', function ($data) {
                return '<span class="badge badge-pill badge-info p-2"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tgl_request)->translatedFormat('d M Y') . '</span>';
            })
            ->editColumn('no_request', function ($data) {
                $html = "<b class='font-bold'> <span class='text-primary'>NO. REQUEST</span> : $data->no_request </b> <br>";
                $html .= "<b class='font-bold'> <span class='text-success'>NO. NOTA</span> : $data->no_nota </b> <br>";

                return $html;
            })
            ->editColumn('action', function ($data) {
                $btn = self::cekNota($data);
                return $btn;
            })
            ->rawColumns(['tgl_request', 'no_request', 'action'])
            ->make(true);
    }

    public function cekNota($data)
    {
        $html = '';
        $data = json_decode(json_encode($data), true);
        if ($data->lunas == "YES") {

        } else if (($data->lunas== 0) && ($data->peralihan == 'T') && ($data->delivery_ke == 'TPK')) {

        } else if (($data->lunas== 0) && ($data->peralihan == 'NOTA_KIRIM')){

        } else {

        }

        return $html;
    }
}
