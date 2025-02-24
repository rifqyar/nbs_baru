<?php

namespace App\Http\Controllers\Billing\NotaReceiving;

use App\Http\Controllers\Controller;
use App\Services\Billing\NotaReceiving\NotaReceivingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Yajra\DataTables\DataTables;

class NotaReceivingController extends Controller
{
    protected $service;
    public function __construct(NotaReceivingService $notaService)
    {
        $this->service = $notaService;
    }

    public function index()
    {
        return view('billing.notareceiving.index');
    }

    public function data(Request $request)
    {
        $dataNota = $this->service->getData($request);

        return DataTables::of($dataNota)
            ->addIndexColumn()
            ->editColumn('tgl_request', function ($data) {
                return '<span class="badge badge-pill badge-info p-2"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tgl_request)->translatedFormat('d M Y') . '</span>';
            })
            ->editColumn('action', function ($data) {
                $btn = self::cekNota($data->no_request);
                return $btn;
                // return '<a href="#" class="btn btn-sm btn-rounded w-100"> <i class="fa fa-file text-success mr-2"></i> Cetak Ulang</a>';
            })
            ->rawColumns(['tgl_request', 'action'])
            ->make(true);
    }

    public function cekNota($no_req)
    {
        $query_cek    = "SELECT NOTA, KOREKSI,LUNAS,a.NO_REQUEST,b.NO_NOTA FROM REQUEST_RECEIVING a, nota_receiving b WHERE a.no_request = b.no_request(+) and a.NO_REQUEST = '$no_req'";
        $row_cek     = DB::connection('uster')->selectOne($query_cek);

        if (!empty($row_cek)) {
            $nota        = $row_cek->nota;
            $koreksi    = $row_cek->koreksi;
            $req        = $row_cek->no_request;
            $notas        = base64_encode($row_cek->no_nota);

            $encReq = base64_encode($req);

            $html = '';
            if (($nota <> 'Y') and ($koreksi <> 'Y')) {
                $html = '<a href="' . route('uster.billing.nota_receiving.preview_nota', [$encReq, 'koreksi=N']) . '" target="_blank" class="btn btn-sm btn-rounded"> <i class="fa fa-file text-success h6 mr-2"></i> Preview Nota</a>';
            } else if (($nota == 'Y') and ($koreksi <> 'Y')) {
                // $html = '<a href="' . route('uster.billing.nota_receiving.preview_nota', [$encReq, 'koreksi=N']) . '" target="_blank" class="btn btn-sm btn-rounded"> <i class="fa fa-file text-success h6 mr-2"></i> Preview Nota</a>'; --buat test
                $html .= '<br>';
                $html = '<a href="'.route('uster.billing.nota_receiving.print_proforma', $encReq).'" target="_blank" class="btn btn-sm btn-rounded"> <i class="fa fa-file text-success h6 mr-2"></i> Cetak Ulang</a> <br>';
                $html .= "<a href='javascript:void(0)' class='btn btn-sm btn-rounded' onclick='recalc(`$encReq`,`$notas`)'><i class='fa fa-calculator text-success h6 mr-2'></i> Recalculate Nota </a>";
            } else if (($nota == 'Y') and ($koreksi == 'Y')) {
                $html = '<a href="' . route('uster.billing.nota_receiving.print_proforma', $encReq) . '" target="_blank" class="btn btn-sm btn-rounded"> <i class="fa fa-file text-success h6 mr-2"></i> Cetak Ulang</a> <br>';
                $html .= '<br>';
                $html .= "<a href='javascript:void(0)' class='btn btn-sm btn-rounded' onclick='recalc(`$encReq`,`$notas`)'><i class='fa fa-calculator text-success h6 mr-2'></i> Recalculate Nota </a>";
            } else if (($nota <> 'Y') and ($koreksi == 'Y')) {
                $html = '<a href="' . route('uster.billing.nota_receiving.preview_nota', [$encReq, 'koreksi=Y']) . '" target="_blank" class="btn btn-sm btn-rounded"> <i class="fa fa-file text-success h6 mr-2"></i> Preview Nota</a>';
            }

            return $html;
        }
    }

    public function printProforma($noReq)
    {
        $id_user = Session::get('PENGGUNA_ID');
        $allData = $this->service->printProforma($noReq);

        $req_tgl = $allData['data']->tgl_request;
        $date = date("d M Y H:i:s");

        $generator = new BarcodeGeneratorPNG();
        $nota = $allData['data']->no_nota_mti ?? 'NOT_FOUND';
        $barcode = $generator->getBarcode($nota, $generator::TYPE_CODE_128);
        $data = $allData;
        $data['barcode'] = $barcode;
        $data['date'] = $date;

        $pdf = Pdf::loadView('billing.notareceiving.printproforma', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('invoice.pdf');
    }

    public function recalculate(Request $request)
    {
        try {
            $param = [
                $request->noReq,
                $request->noNota
            ];

            $exec = $this->service->recalc($param);
            if ($exec->getStatusCode() != 200) {
                throw new Exception('Gagal menghitung ulang nota, terdapat kesalahan di server', 500);
            }

            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Menghitung Ulang Nota Receiving',
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Menghitung Ulang Nota!'
            ], $th->getCode() != '' ? $th->getCode() : 500);
        }
    }

    public function previewNota(Request $request, $noReq)
    {
        $no_req = base64_decode($noReq);
        $koreksi = $request->koreksi;
        $data = $this->service->previewNota($no_req, $koreksi);
        $data = $data->getData(true);
        $data['row_nota'] = json_decode(json_encode($data['row_nota']));

        return view('billing.notareceiving.previewnota', $data);
    }

    public function insertProforma(Request $request, $noReq)
    {
        $no_req = base64_decode($noReq);
        $query_cek_nota 	= "SELECT NO_NOTA, STATUS FROM NOTA_RECEIVING WHERE NO_REQUEST = '$no_req'";
        $nota				= DB::connection('uster')->selectOne($query_cek_nota);
        $no_nota_cek		= $nota->no_nota ?? '';
        $nota_status		= $nota->status ?? '';

        try {
            if (($no_nota_cek != NULL && $nota_status == 'BATAL') || ($no_nota_cek == NULL && $nota_status == NULL)) {
                $saveProforma = $this->service->insertProforma($no_req, $request->koreksi);
                if ($saveProforma->getStatusCode() != 200) {
                    throw new Exception('Gagal Menyimpan Proforma Nota', 500);
                }
            } else {
                return response()->redirectToRoute('uster.billing.nota_receiving.print_proforma', [$noReq]);
            }

            return response()->redirectToRoute('uster.billing.nota_receiving.print_proforma', [$noReq, 'first=1']);
        } catch (Exception $th) {
            return redirect()->back()->with(['error' => 'Gagal Menyimpan Proforma Nota Ini, Harap Coba Lagi Nanti']);
        }
    }
}
