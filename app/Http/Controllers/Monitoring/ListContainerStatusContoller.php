<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Monitoring\ContainerService;
use \Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
class ListContainerStatusContoller extends Controller
{
    protected $container;

    public function __construct(ContainerService $container)
    {
        $this->container = $container;
    }

    public function index() {
        return view('monitoring.list-container-status');
    }

    public function listContainerStatus(Request $request) {
        
        $data = $this->container->listContainerStatus($request);
        return Datatables::of($data) ->make(true);
    }
    
    public function toExcel(Request $request) {
        $row_list = $this->container->listContainerStatus($request);
        $kegiatan   = $request->KEGIATAN;
        $status   = $request->STATUS;
        $tgl_awal = date('d/m/Y', strtotime($request->TGL_AWAL));
        $tgl_akhir = date('d/m/Y', strtotime($request->TGL_AKHIR));

        if ($kegiatan == 'receiving_luar') {
            $kegiatan_ = 'Receiving dari Luar / MTY';
        } else if ($kegiatan == 'delivery_luar') {
            $kegiatan_ = 'Delivery ke Luar / SP2';
        } else if ($kegiatan == 'delivery_tpk_mty') {
            $kegiatan_ = 'Repo MTY';
        } else if ($kegiatan == 'stuffing_depo') {
            $kegiatan_ = 'Stuffing';
        } else if ($kegiatan == 'stripping_tpk') {
            $kegiatan_ = 'Stripping';
        }

        $pdf = Pdf::loadView('monitoring.print-list-container', compact('row_list','kegiatan','status','kegiatan_','tgl_awal','tgl_akhir'));
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        $title = "Laporan Container By Status untuk kegiatan  $kegiatan_<br /> Periode  $tgl_awal s/d $tgl_akhir";
        return $pdf->stream("$title.pdf");
        
    }
}
