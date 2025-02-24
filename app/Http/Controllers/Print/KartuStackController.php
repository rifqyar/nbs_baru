<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use App\Services\Print\KartuMerahService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class KartuStackController extends Controller
{
    protected $service;
    public function __construct(KartuMerahService $realisasiService)
    {
        $this->service = $realisasiService;
    }

    public function index()
    {
        return view('print.kartu_merah.index');
    }

    public function data(Request $request)
    {
        $data = $this->service->getData($request->all());

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tgl_request', function ($data) {
                return '<span class="badge badge-pill badge-info p-2"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tgl_request)->translatedFormat('d M Y') . '</span>';
            })
            ->editColumn('action', function ($data) {
                $btn = self::cekNota($data);
                return $btn;
            })
            ->rawColumns(['tgl_request', 'action'])
            ->make(true);
    }

    public function cekNota($data)
    {
        $html = '';
        $data = json_decode(json_encode($data), true);
        if ($data["receiving_dari"] == "LUAR") {
            if (count($data) > 0) {
                if ($data["lunas"] == "YES" && $data["cetak_kartu"] == 0) {
                    $no_req    = $data["no_request"];
                    $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded w-100" href="' . route('uster.print.kartu_merah.print', ['no_req' => base64_encode($data['no_request']), 'rec_from' => $data['receiving_dari'], 'type' => 'view']) . '" target="_blank"> CETAK KARTU </a> <br/> ';
                    $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded mt-2 w-100" href="' . route('uster.print.kartu_merah.print', ['no_req' => base64_encode($data['no_request']), 'rec_from' => $data['receiving_dari'], 'type' => 'pdf']) . '" target="_blank"> CETAK KARTU PDF </a>';
                } else if ($data["cetak_kartu"] > 0 && $data["lunas"] == "YES") {
                    $no_req    = $data["no_request"];
                    $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded w-100" href="' . route('uster.print.kartu_merah.print', ['no_req' => base64_encode($data['no_request']), 'rec_from' => $data['receiving_dari'], 'type' => 'view']) . '" target="_blank"> CETAK ULANG</a> <br/>';
                    $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded mt-2 w-100" href="' . route('uster.print.kartu_merah.print', ['no_req' => base64_encode($data['no_request']), 'rec_from' => $data['receiving_dari'], 'type' => 'pdf']) . '" target="_blank"> CETAK ULANG PDF </a>';
                } else {
                    $html .= "BELUM LUNAS";
                }
            } else {
                $html .= ' BELUM LUNAS ';
            }
        } else if ($data["receiving_dari"] == "TPK") {
            $kartu = $data["cetak_kartu"];
            if ($kartu == 0) {
                $no_req    = $data["no_request"];
                $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded" href="' . route('uster.print.kartu_merah.print', ['no_req' => base64_encode($data['no_request']), 'rec_from' => $data['receiving_dari'], 'type' => 'view']) . '" target="_blank"> CETAK KARTU </a>';
            } else if ($kartu > 0) {
                $no_req    = $data["no_request"];
                $html .=  '<a class="btn btn-sm btn-outline-info btn-rounded" href="' . route('uster.print.kartu_merah.print', ['no_req' => base64_encode($data['no_request']), 'rec_from' => $data['receiving_dari'], 'type' => 'pdf']) . '" target="_blank"> CETAK ULANG </a>';
            }
        }

        return $html;
    }

    public function print(Request $request)
    {
        $no_request = base64_decode($request->no_req);
        $rec_dari = $request->rec_from;

        $statusLunas = $this->service->cekStatusLunas($no_request);
        if ($statusLunas != 'YES') {
            return redirect()->back()->with('error', 'Nota Belum Lunas');
        }

        if ($request->type == 'view') {
            $query_update_kartu = "UPDATE REQUEST_RECEIVING
                                    SET CETAK_KARTU = CETAK_KARTU +1
                                WHERE NO_REQUEST = '$no_request'";
            // DB::connection('uster')->statement($query_update_kartu);

            if ($rec_dari == "LUAR") {
                $dataNota = $this->service->getDataRecLuar($no_request);
            } else {
                $dataNota = $this->service->getDataTPK($no_request);
            }

            return view('print.kartu_merah.cetak_kartu', ['data' => $dataNota]);
        } else if ($request->type == 'pdf') {
            $data = $this->service->getDataNota($no_request);

            $pdf = Pdf::loadView('print.kartu_merah.cetak_kartu_pdf', ['nota' => $data['dataNota'], 'pegawai' => $data['pegawai']]);
            return $pdf->stream('kartu_merah.pdf');
        }
    }
}
