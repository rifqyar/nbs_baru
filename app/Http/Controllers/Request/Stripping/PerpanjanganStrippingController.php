<?php

namespace App\Http\Controllers\Request\Stripping;

use App\Http\Controllers\Controller;
use App\Services\Request\Stripping\PerpanjanganStripping;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class PerpanjanganStrippingController extends Controller
{
    protected $perpanjangan;

    public function __construct(PerpanjanganStripping $perpanjangan)
    {
        $this->perpanjangan = $perpanjangan;
    }

    public function index()
    {
        return view('request.stripping.perp.index');
    }

    public function data(Request $request)
    {
        $data = $this->perpanjangan->getData($request);

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tgl_request', function ($data) {
                return '<span class="badge badge-pill badge-info p-2"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tgl_request)->translatedFormat('d M Y H:i') . ' WIB</span>';
            })
            ->editColumn('no_do_bl', function ($data) {
                return $data->no_do . ' | ' . $data->no_bl;
            })
            ->editColumn('total_box', function ($data) {
                $jumlah = DB::connection('uster')->selectOne("SELECT COUNT(NO_CONTAINER) TOTAL FROM CONTAINER_STRIPPING WHERE NO_REQUEST = '" . $data->no_request . "' AND AKTIF = 'Y'");
                $jumlah = $jumlah->total;
                return $jumlah . " BOX";
            })
            ->editColumn('action', function ($data) {
                // return 'INI TOMBOL';
                return self::renderAction($data);
            })
            ->rawColumns(['tgl_request', 'action'])
            ->make(true);
    }

    private function renderAction($data)
    {
        $noReq = base64_encode($data->no_request);
        $comingsoon = base64_encode(route('uster.new_request.stripping.perpanjangan'));
        $jumlah = DB::connection('uster')->selectOne("SELECT COUNT(NO_CONTAINER) TOTAL FROM CONTAINER_STRIPPING WHERE NO_REQUEST = '" . $data->no_request . "' AND AKTIF = 'Y'");
        $jumlah = $jumlah->total;


        $previewBtn = '<a href="' . route('uster.new_request.stripping.perpanjangan.preview', $noReq) . '" class="btn btn-sm btn-rounded btn-info w-100"> <i class="fa fa-info-circle mr-2"></i> Preview</a>';
        $perpanjanganBtn = '<a href="' . route('uster.new_request.stripping.perpanjangan.view', $noReq) . '" class="btn btn-sm btn-rounded btn-info w-100"> <i class="fa fas fa-check mr-2"></i> Perpanjangan</a>';
        $editBtn = '<a href="' . route('uster.new_request.stripping.perpanjangan.edit', $noReq) . '" class="btn btn-sm btn-rounded btn-warning w-100"> <i class="fa fas fa-edit mr-2"></i> Edit</a>';

        $notaLamaBelumLunas = '<span class="badge"><i>Nota lama belum lunas</i></span> <br>';
        $notaSudahLunas = '<span class="badge"><i>Nota Sudah Lunas</i></span> <br>';
        $notaSudahLunas .= $previewBtn;
        $notaSudahCetak = '<span class="badge"><i>Nota Sudah Cetak</i></span> <br>';
        $notaSudahCetak .= $previewBtn;
        $notaBelumLunas = '<span class="badge"><i>Nota Belum Lunas</i></span> <br>';
        $notaBelumLunas .= $editBtn;

        if ($data->ex_req == '-' and ($data->nota == 'T' || ($data->nota == 'Y' and $data->lunas == 'NO')) and $data->koreksi <> 'Y') {
            return $notaLamaBelumLunas;
        } else if ($data->ex_req == '-' and $data->nota == 'Y' and $data->lunas == 'YES' and $jumlah <> 0 and ($data->koreksi <> 'Y' || $data->koreksi == 'Y')) {
            return $perpanjanganBtn;
        } else if (($data->ex_req <> '-' || $data->ex_req == '-') and $data->nota == 'Y' and $data->lunas == 'YES' and $jumlah == 0 and $data->koreksi <> 'Y') {
            return $notaSudahLunas;
        } else if ($data->ex_req == '-' and $data->nota == 'T' and $data->lunas == 0 and $jumlah <> 0 and $data->koreksi <> 'Y') {
            return $notaLamaBelumLunas;
        } else if ($data->ex_req <> '-' and $data->nota == 'T' and $data->lunas == 0 and $jumlah <> 0 and $data->koreksi <> 'Y') {
            return $editBtn;
        } else if ($data->ex_req <> '-' and $data->nota == 'Y' and $data->lunas == 'YES' and ($data->koreksi <> 'Y' || $data->koreksi == 'Y')) {
            return $perpanjanganBtn;
        } else if ($data->ex_req <> '-' and $data->nota == 'Y' and $data->lunas == 'NO' and $data->koreksi <> 'Y') {
            return $notaSudahCetak;
        } else if ($data->ex_req <> '-' and $data->nota <> 'Y' and ($data->lunas == 'NO' || $data->lunas == 'YES') and $data->koreksi == 'Y') {
            return $editBtn;
        } else if ($data->ex_req <> '-' and $data->nota == 'Y' and $data->lunas == 'NO' and $data->koreksi == 'Y') {
            return $notaBelumLunas;
        }
    }

    public function previewNota($noReq)
    {
        $noReq = base64_decode($noReq);
        $data['request'] = $this->perpanjangan->getEditData($noReq)->getData();
        $data['container'] = $this->perpanjangan->contList($noReq)->getData();
        $data['overview'] = true;
        // dd($data['container']);
        return view('request.stripping.perp.view-nota', $data);
    }

    public function view($noReq)
    {
        $noReq = base64_decode($noReq);
        $data['request'] = $this->perpanjangan->getViewData($noReq)->getData();
        $data['container'] = $this->perpanjangan->contList($noReq)->getData();
        $data['overview'] = false;

        // dd($data);
        return view('request.stripping.perp.view-nota', $data);
    }

    public function store(Request $request)
    {
        try {
            $no_req	        = $request->no_request;
            $query_cek	= "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_REQUEST,8,13)))+1,6,0),'000001') AS JUM,
                                TO_CHAR(SYSDATE, 'MM') AS MONTH,
                                TO_CHAR(SYSDATE, 'YY') AS YEAR
                           FROM REQUEST_STRIPPING
                           WHERE TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)
                           AND SUBSTR(request_stripping.NO_REQUEST,0,3) = 'STP'";

            $row_select = DB::connection('uster')->selectOne($query_cek);
            $no_req_		= $row_select->jum;
            $month			= $row_select->month;
            $year			= $row_select->year;
            $no_req_s		= "STP".$month.$year.$no_req_;

            $query_get_old	= "SELECT a.KD_CONSIGNEE, a.CONSIGNEE_PERSONAL, a.NO_REQUEST_PLAN,a.TYPE_STRIPPING, a.NO_DO, a.NO_BL, NVL(a.PERP_KE,1) PERP_KE,
                                a.NO_REQUEST_RECEIVING, a.O_VESSEL,a.O_VOYIN,a.O_VOYOUT,a.O_REQNBS,a.O_IDVSB,a.DI,a.NO_BOOKING
                                FROM REQUEST_STRIPPING a WHERE NO_REQUEST = '$no_req'";
            $row_old = DB::connection('uster')->selectOne($query_get_old);

            $param = [
                'request' => json_decode(json_encode($request->all())),
                'no_req_s' => $no_req_s,
                'row_old' => $row_old
            ];

            $insert = $this->perpanjangan->savePerp($param);
            if($insert->getStatusCode() != 200){
                throw new Exception("Gagal Menyimpan Request Perpanjangan Stripping", 500);
            }

            $no_req_s = base64_encode($no_req_s);
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Simpan Data Request Perpanjangan Stripping',
                'redirect' => [
                    'need' => true,
                    'to' => route('uster.new_request.stripping.perpanjangan.edit', $no_req_s),
                ]
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], $th->getCode() != '' ? $th->getCode() : 500);
        }
    }

    public function edit($no_req)
    {
        $no_req = base64_decode($no_req);
        $data['request'] = $this->perpanjangan->getViewData($no_req)->getData();
        $data['container'] = $this->perpanjangan->contList($no_req)->getData();
        // dd($data['container']);
        $data['overview'] = false;

        return view('request.stripping.perp.edit', $data);
    }

    public function update(Request $request)
    {
        try {
            $insert = $this->perpanjangan->updateReq($request);
            if($insert->getStatusCode() != 200){
                throw new Exception("Gagal Merubah Request Perpanjangan Stripping", 500);
            }

            $no_req = base64_encode($request->no_request);
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Update Data Request Perpanjangan Stripping',
                'redirect' => [
                    'need' => true,
                    'to' => route('uster.new_request.stripping.perpanjangan.edit', $no_req),
                ]
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], $th->getCode() != '' ? $th->getCode() : 500);
        }
    }

    public function deleteCont($noCont, $noReq)
    {
        try {
            $exec = $this->perpanjangan->deleteCont($noReq, $noCont);
            if($exec->getStatusCode() != 200){
                throw new Exception("Gagal Menghapus Container Stripping", 500);
            }

            $noReq = base64_encode($noReq);
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Menghapus Container Stripping',
                'redirect' => [
                    'need' => true,
                    'to' => route('uster.new_request.stripping.perpanjangan.edit', $noReq),
                ]
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], $th->getCode() != '' ? $th->getCode() : 500);
        }

    }

    public function approve(Request $request)
    {
        try {
            $param = [
                'in_req_old' => $request->perp_dari,
                'in_req_new' => $request->no_req,
                'in_asalcont'=>'TPK',
                'in_iduser'=> Session::get('id'),
                'in_ket'=> 'OK',
            ];

            $exec = $this->perpanjangan->approveReq($param);
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Approve Request Perpanjangan Stripping',
                'redirect' => [
                    'need' => true,
                    'to' => route('uster.new_request.stripping.perpanjangan'),
                ]
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Approve Data, Harap Coba lagi!'
            ], $th->getCode() != '' ? $th->getCode() : 500);
        }
    }
}
