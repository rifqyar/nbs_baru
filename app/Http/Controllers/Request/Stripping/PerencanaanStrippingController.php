<?php

namespace App\Http\Controllers\Request\Stripping;

use App\Http\Controllers\Controller;
use App\Services\Request\Stripping\PerencanaanStripping;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use App\Traits\NpwpCheckPengkinianTrait;

class PerencanaanStrippingController extends Controller
{
    protected $stripping_plan;
    use NpwpCheckPengkinianTrait;

    public function __construct(PerencanaanStripping $stripping_plan)
    {
        $this->stripping_plan = $stripping_plan;
    }

    public function index()
    {
        $totalData = $this->stripping_plan->getTotalData();
        return view('request.stripping.plan.index', $totalData);
    }

    public function data(Request $request)
    {
        $data = $this->stripping_plan->getData($request);
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tgl_request', function ($data) {
                return '<span class="badge badge-pill badge-success p-2"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tgl_request)->translatedFormat('d M Y H:i') . ' WIB</span>';
            })
            ->editColumn('no_request_app', function($data){
                if($data->status_req == 'Blm di Approve'){
                    return "
                        <div class='row justify-content-center text-center'>
                        <div class='col-12'>
                            <strong> $data->no_request_app </strong>
                        </div>
                        <div class='col-12'>
                            <span class='badge badge-pill badge-danger p-2 w-100'><i class='fas fa-exclamation-circle'></i> $data->status_req </span>
                        </div>";
                } else {
                    return "
                        <div class='row justify-content-center text-center'>
                        <div class='col-12'>
                            <strong> $data->no_request_app </strong>
                        </div>
                        <div class='col-12'>
                            <span class='badge badge-pill badge-success p-2 w-100'><i class='fas fa-check-circle'></i> $data->status_req </span>
                        </div>";
                }
            })
            ->editColumn('no_do_bl', function($data){
                return $data->no_do . ' | ' . $data->no_bl;
            })
            ->editColumn('action', function ($data) {
                return self::renderAction($data);
            })
            ->rawColumns(['tgl_request', 'action', 'no_request_app'])
            ->make(true);
    }

    private function renderAction($data)
    {
        $nota = $data->nota;
        $koreksi = $data->koreksi;
        $lunas = $data->lunas;
        $closing = $data->closing;
        $noReq = base64_encode($data->no_request);

        if($data->no_request_app != 'blm di approve' && $nota != 'Y' && $koreksi != 'Y' && $closing == "CLOSED"){
            return '<a href="'.url('/request/stripping/stripping-plan/view/'.$noReq).'" class="badge badge-pill badge-info p-2 w-100">Request Approved <i class="mdi mdi-check-circle ml-1"></i> </a>';
        }
        else if($data->no_request_app == 'blm di approve' && $nota != 'Y' && $koreksi != 'Y' && $closing != "CLOSED"){
            return '<a href="'.url('/request/stripping/stripping-plan/view/'.$noReq).'" class="badge badge-pill badge-warning p-2 w-100"> Edit <i class="mdi mdi-pencil-box ml-1"></i> </a>';
        }
        else if($data->no_request_app != 'blm di approve' && $nota == 'Y' && $koreksi != 'Y' && $closing == "CLOSED"){
            if($lunas == 'NO'){
                return '<a href="'.url('/request/stripping/stripping-plan/view/'.$noReq).'" class="badge badge-pill badge-info p-2 w-100">Request Approved <i class="mdi mdi-check-circle ml-1"></i> </a>';
            } else {
                return '<a href="'.url('/request/stripping/stripping-plan/overview/'.$noReq).'" class="badge badge-pill badge-success p-2 w-100">Nota Sudah Cetak <i class="mdi mdi-check-circle ml-1"></i> </a>';
            }
        }
        else if($data->no_request_app != 'blm di approve' && $nota != 'Y' && $koreksi == 'Y' && $closing == "CLOSED"){
            return '<a href="'.url('/request/stripping/stripping-plan/view/'.$noReq).'" class="badge badge-pill badge-warning p-2 w-100"> Edit <i class="mdi mdi-pencil-box ml-1"></i></a>';
        } else if($data->no_request_app != 'blm di approve' && $nota == 'Y' && $koreksi == 'Y' && $closing == "CLOSED"){
            if($lunas == 'NO'){
                return '<a href="'.url('/request/stripping/stripping-plan/view/'.$noReq).'" class="badge badge-pill badge-info p-2 w-100">Request Approved <i class="mdi mdi-check-circle ml-1"></i> </a>';
            }
            else {
            return '<a href="'.url('/request/stripping/stripping-plan/overview/'.$noReq).'" class="badge badge-pill badge-success p-2 w-100">Nota Sudah Cetak <i class="mdi mdi-check-circle ml-1"></i> </a>';
            }
        }else{
            return '<a href="'.url('/request/stripping/stripping-plan/view/'.$noReq).'" class="badge badge-pill badge-warning p-2 w-100"> Edit <i class="mdi mdi-pencil-box ml-1"></i></a>';
        }
    }

    public function addRequest()
    {
        return view('request.stripping.plan.add');
    }

    public function overview($noReq)
    {
        $noReq = base64_decode($noReq);

        $data['request'] = $this->stripping_plan->getOverviewData($noReq);
        $data['no_req2'] = $data['request'][1];
        $data['request'] = $data['request'][0];
        $data['container'] = $this->stripping_plan->contList($noReq, 'overview');
        $data['closing'] = $data['container'][1];
        $data['container'] = $data['container'][0];
        $data['overview'] = true;

        return view('request.stripping.plan.overview-nota', $data);
    }

    public function view($noReq)
    {
        $noReq = base64_decode($noReq);
        $data['request'] = $this->stripping_plan->getViewData($noReq)->getData();
        $data['container'] = $this->stripping_plan->contList($noReq, 'view');
        $data['closing'] = $data['container'][1];
        $data['container'] = $data['container'][0];
        $data['overview'] = false;
        $data['cekFunction'] = $this->stripping_plan;

        return view('request.stripping.plan.view-nota', $data);
    }

    public function cetakSaldo($kdConsignee)
    {
        $route = route('uster.new_request.stripping.stripping_plan.awal_tpk');
        return view('coomingsoon', compact('route'));
    }

    public function postPraya(Request $request)
    {
        $validatedNpwp = $this->validateNpwp($request);

        // Check if the response is a failed validation JSON response
        if ($validatedNpwp instanceof \Illuminate\Http\JsonResponse) {
            return $validatedNpwp; // Return error response if NPWP validation failed
        }
        
        DB::beginTransaction();
        try {
            $param = array(
                "in_accpbm" => $request->NO_ACC_CONS,
                "in_pbm"    => $request->ID_CONSIGNEE,
                "in_personal" => $request->CONSIGNEE_PERSONAL,
                "in_do" => $request->NO_DO,
                "in_datesppb" => $request->TGL_SPPB == null ? '' : $request->TGL_SPPB,
                "in_nosppb" => $request->NO_SPPB,
                "in_keterangan" => $request->KETERANGAN,
                "in_user" => Session::get('id'),
                "in_di" => $request->TYPE_S,
                "in_vessel" => $request->NM_KAPAL,
                "in_voyin" => $request->VOYAGE_IN,
                "in_voyout" => $request->VOYAGE_OUT,
                "in_idvsb" => $request->IDVSB,
                "in_nobooking" => $request->NO_BOOKING,
                "in_callsign" => $request->CALLSIGN,
                "in_bl" => $request->NO_BL,
                "in_vessel_code" => $request->VESSEL_CODE,
                "in_tanggal_jam_tiba" => $request->TANGGAL_JAM_TIBA,
                "in_tanggal_jam_berangkat" => $request->TANGGAL_JAM_BERANGKAT,
                "in_operator_name" => $request->OPERATOR_NAME,
                "in_operator_id" => $request->OPERATOR_ID,
                "in_pod" => $request->POD,
                "in_pol" => $request->POL,
                "in_voyage" => $request->VOYAGE
            );

            $storeData = $this->stripping_plan->addRequestPraya($param);
            $noReq = null;

            if($storeData->getData()->status->code != 200){
                throw new Exception('Gagal Membuat Perencanaan Stripping',500);
            } else if($storeData->getData()->data->outmsg == 'F'){
                throw new Exception('Gagal Membuat Perencanaan Stripping', 500);
            } else {
                $noReq = base64_encode($storeData->getData()->data->out_noreq);
            }

            DB::commit();
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Tambah Request Stripping',
                'redirect' => [
                    'need' => true,
                    'to' => route('uster.new_request.stripping.stripping_plan.view', $noReq),
                ]
            ]);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ]);
        }
    }

    public function saveEdit(Request $request)
    {
        DB::beginTransaction();
        try {
            $data['plan_request'] = [
                'KD_CONSIGNEE' => $request->id_consignee,
                'KD_PENUMPUKAN_OLEH' => $request->id_consignee,
                'NO_DO' => $request->no_do,
                'NO_BL' => $request->no_bl,
                'NO_SPPB' => $request->no_sppb,
                'TGL_SPPB' => "TO_DATE('$request->tgl_sppb', 'yyyy-mm-dd')@ORA",
                'TYPE_STRIPPING' => $request->type_s,
                'KETERANGAN' => $request->keterangan,
            ];

            $data['request_strip'] = [
                'KD_CONSIGNEE' => $request->id_consignee,
                'KD_PENUMPUKAN_OLEH' => $request->id_consignee,
                'NO_DO' => $request->no_do,
                'NO_BL' => $request->no_bl,
                'TYPE_STRIPPING' => $request->type_s,
                'KETERANGAN' => $request->keterangan,
            ];

            $process = $this->stripping_plan->saveEdit($data, $request->no_req);
            $statusCode = $process->getData()->status->code;
            if($statusCode != 200){
                throw new Exception('Gagal Update Data',500);
            }

            DB::commit();
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Rubah Data Perencanaan Stripping',
                'redirect' => [
                    'need' => false,
                    'to' => null,
                ]
            ]);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ]);
        }
    }

    public function saveCont(Request $request)
    {
        DB::beginTransaction();
        try {
            $validasiContainerAktif = $this->cekCont($request->NO_CONT);

            if($request->TGL_BONGKAR != null){
                throw new Exception('Tanggal Bongkar Kosong, Silahkan Hubungi Admin', 400);
            }

            $no_booking = $request->NO_BOOKING;
            if($no_booking == NULL){
                $no_booking = "VESSEL_NOTHING";
            }

            $no_req_rec	= substr($request->no_req2,4);
            $no_req_rec	= "REC".$no_req_rec;

            //HANYA YANG GATO YANG BISA STRIPPING
            $flag = 1;
            if($request->ASAL_CONT != "DEPO" ) {
                $cek_locate = "SELECT LOCATION, MLO, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$request->NO_CONT'";
                $rw_locate = DB::connection('uster')->selectOne($cek_locate);

                $r_counthi = DB::connection('uster')->selectOne("SELECT COUNT(*) JUM FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$request->NO_CONT'");
                if ($r_counthi->jum > 1) {
                    if ($rw_locate->location != "GATO") {
                        throw new Exception('Container Masih Aktif di Siklus Sebelumnya / Bukan GATO', 400);
                    }
                }
                $flag = 0;
            }

            $param = array(
                "in_nocont" => $request->NO_CONT,
                "in_planreq" => $request->no_req,
                "in_size" => $request->SIZE,
                "in_type" => $request->TIPE,
                "in_status" => $request->STATUS,
                "in_hz" => $request->BERBAHAYA,
                "in_commodity" => $request->KOMODITI,
                "in_voyin" => $request->VOYAGE,
                "in_after_strip" => $request->AFTER_STRIP,
                "in_asalcont" => $request->ASAL_CONT,
                "in_datedisch" => $request->TGL_BONGKAR ? date('d-m-Y', strtotime($request->TGL_BONGKAR)) : '',
                "in_tglmulai" => $request->tgl_mulai ? date('d-m-Y', strtotime($request->tgl_mulai)) : '',
                "in_tglselesai" => $request->tgl_selesai ? date('d-m-Y', strtotime($request->tgl_selesai)) : '',
                "in_blok" => $request->BLOK,
                "in_slot" => $request->SLOT,
                "in_row"=> $request->ROW,
                "in_tier" => $request->TIER,
                "in_nobooking"=> $no_booking,
                "in_iduser" => Session::get('id')
            );

            $process = $this->stripping_plan->saveCont($param);
            $statusCode = $process->getData()->status->code;

            if($statusCode != 200){
                throw new Exception('Gagal Simpan Container',500);
            }

            DB::commit();
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Simpan Data Container',
                'redirect' => [
                    'need' => false,
                    'to' => null,
                ]
            ], 200);
        } catch (Exception $th) {
            DB::rollBack();
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

    function cekCont($no_cont)
    {
        $cekGato = "SELECT AKTIF
                        FROM CONTAINER_DELIVERY
                    WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y' ORDER BY AKTIF DESC";
        $cekGato = DB::connection('uster')->selectOne($cekGato);
        if(!empty($cekGato) && $cekGato->aktif == 'Y'){
            throw new Exception('Container Masih Aktif di Request SP2 / Belum Gate Out, Cek History', 400);
        }

        $cek_gati = "SELECT AKTIF
                        FROM CONTAINER_RECEIVING
                    WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y' ORDER BY AKTIF DESC";
        $l_gati = DB::connection('uster')->selectOne($cek_gati);

        if(!empty($l_gati) && $l_gati->aktif == 'Y'){
            throw new Exception('Container Masih Aktif di Request Receiving', 400);
        }

        $cek_stuf = "SELECT AKTIF
                            FROM CONTAINER_STUFFING
                        WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y'";
        $l_stuf = DB::connection('uster')->selectOne($cek_stuf);
        if(!empty($l_stuf) && $l_stuf->aktif == 'Y'){
            throw new Exception('Container Masih Aktif di Request Stuffing', 400);
        }

        $cek_plan_strip = "SELECT AKTIF
                                FROM CONTAINER_STRIPPING
                            WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y'";
        $l_strip = DB::connection('uster')->selectOne($cek_plan_strip);

        if (!empty($l_strip) && $l_strip->aktif == 'Y') {
            throw new Exception('Container Masih Aktif di Request Stripping', 400);
        }
    }

    // Get Master Data
    public function getDataPBM(Request $request)
    {
        $data['PBM'] = $this->stripping_plan->getPbm($request->search);
        return response()->json($data['PBM']);
    }

    public function getDataKapal(Request $request)
    {
        $data = $this->stripping_plan->getKapal($request->search);
        return response()->json($data);
    }

    public function getDataCont(Request $request)
    {
        $data = $this->stripping_plan->getCont($request);
        return response()->json($data);
    }

    public function getDataKomoditi(Request $request)
    {
        $data = $this->stripping_plan->getKomoditi($request->search);
        return response()->json($data);
    }

    public function getDataVoyage(Request $request)
    {
        $data = $this->stripping_plan->getVoyage($request->search);
        return response()->json($data);
    }

    public function cekSaldoEmkl($idConsignee)
    {
        $data = $this->stripping_plan->cekSaldo($idConsignee);
        $data = $data->getData();

        return $data;
    }
}
