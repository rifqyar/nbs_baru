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
use PDO;

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
            ->editColumn('no_request_app', function ($data) {
                if ($data->status_req == 'Blm di Approve') {
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
            ->editColumn('no_do_bl', function ($data) {
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

        if ($data->no_request_app != 'blm di approve' && $nota != 'Y' && $koreksi != 'Y' && $closing == "CLOSED") {
            return '<a href="' . url('/request/stripping/stripping-plan/view/' . $noReq) . '" class="badge badge-pill badge-info p-2 w-100">Request Approved <i class="mdi mdi-check-circle ml-1"></i> </a>';
        } else if ($data->no_request_app == 'blm di approve' && $nota != 'Y' && $koreksi != 'Y' && $closing != "CLOSED") {
            return '<a href="' . url('/request/stripping/stripping-plan/view/' . $noReq) . '" class="badge badge-pill badge-warning p-2 w-100"> Edit <i class="mdi mdi-pencil-box ml-1"></i> </a>';
        } else if ($data->no_request_app != 'blm di approve' && $nota == 'Y' && $koreksi != 'Y' && $closing == "CLOSED") {
            if ($lunas == 'NO') {
                return '<a href="' . url('/request/stripping/stripping-plan/view/' . $noReq) . '" class="badge badge-pill badge-info p-2 w-100">Request Approved <i class="mdi mdi-check-circle ml-1"></i> </a>';
            } else {
                return '<a href="' . url('/request/stripping/stripping-plan/overview/' . $noReq) . '" class="badge badge-pill badge-success p-2 w-100">Nota Sudah Cetak <i class="mdi mdi-check-circle ml-1"></i> </a>';
            }
        } else if ($data->no_request_app != 'blm di approve' && $nota != 'Y' && $koreksi == 'Y' && $closing == "CLOSED") {
            return '<a href="' . url('/request/stripping/stripping-plan/view/' . $noReq) . '" class="badge badge-pill badge-warning p-2 w-100"> Edit <i class="mdi mdi-pencil-box ml-1"></i></a>';
        } else if ($data->no_request_app != 'blm di approve' && $nota == 'Y' && $koreksi == 'Y' && $closing == "CLOSED") {
            if ($lunas == 'NO') {
                return '<a href="' . url('/request/stripping/stripping-plan/view/' . $noReq) . '" class="badge badge-pill badge-info p-2 w-100">Request Approved <i class="mdi mdi-check-circle ml-1"></i> </a>';
            } else {
                return '<a href="' . url('/request/stripping/stripping-plan/overview/' . $noReq) . '" class="badge badge-pill badge-success p-2 w-100">Nota Sudah Cetak <i class="mdi mdi-check-circle ml-1"></i> </a>';
            }
        } else {
            return '<a href="' . url('/request/stripping/stripping-plan/view/' . $noReq) . '" class="badge badge-pill badge-warning p-2 w-100"> Edit <i class="mdi mdi-pencil-box ml-1"></i></a>';
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

            if ($storeData->getData()->status->code != 200) {
                throw new Exception('Gagal Membuat Perencanaan Stripping' . $storeData->getData()->status->msg, 500);
            } else if ($storeData->getData()->data->outmsg == 'F') {
                throw new Exception('Gagal Membuat Perencanaan Stripping' . $storeData->getData()->status->msg, 500);
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
            if ($statusCode != 200) {
                throw new Exception('Gagal Update Data', 500);
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

            if ($request->TGL_BONGKAR == null) {
                throw new Exception('Tanggal Bongkar Kosong, Silahkan Hubungi Admin', 400);
            }

            $no_booking = $request->NO_BOOKING;
            if ($no_booking == NULL) {
                $no_booking = "VESSEL_NOTHING";
            }

            $no_req_rec    = substr($request->no_req2, 4);
            $no_req_rec    = "REC" . $no_req_rec;

            //HANYA YANG GATO YANG BISA STRIPPING
            $flag = 1;
            if ($request->ASAL_CONT != "DEPO") {
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
                "in_row" => $request->ROW,
                "in_tier" => $request->TIER,
                "in_nobooking" => $no_booking,
                "in_iduser" => Session::get('id')
            );

            $process = $this->stripping_plan->saveCont($param);
            $statusCode = $process->getData()->status->code;

            if ($statusCode != 200) {
                throw new Exception('Gagal Simpan Container' . $process->getData()->status->msg, 500);
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
        if (!empty($cekGato) && $cekGato->aktif == 'Y') {
            throw new Exception('Container Masih Aktif di Request SP2 / Belum Gate Out, Cek History', 400);
        }

        $cek_gati = "SELECT AKTIF
                        FROM CONTAINER_RECEIVING
                    WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y' ORDER BY AKTIF DESC";
        $l_gati = DB::connection('uster')->selectOne($cek_gati);

        if (!empty($l_gati) && $l_gati->aktif == 'Y') {
            throw new Exception('Container Masih Aktif di Request Receiving', 400);
        }

        $cek_stuf = "SELECT AKTIF
                            FROM CONTAINER_STUFFING
                        WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y'";
        $l_stuf = DB::connection('uster')->selectOne($cek_stuf);
        if (!empty($l_stuf) && $l_stuf->aktif == 'Y') {
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

    public function approveCont(Request $request)
    {
        DB::beginTransaction();
        try {
            $id_user = Session::get('ID_USER');
            $id_yard = Session::get("IDYARD_STORAGE");

            $q_cek_double = "SELECT count(no_container) jum from container_stripping where no_request = REPLACE('$request->no_req','P','S') and no_container = '$request->no_cont' ";
            $rcekd = DB::connection('uster')->selectOne($q_cek_double);

            if ($rcekd->jum > 0) {
                $q_update_p = "UPDATE PLAN_CONTAINER_STRIPPING SET TGL_APPROVE = TO_DATE('$request->tgl_approve','yyyy-mm-dd'), TGL_APP_SELESAI = TO_DATE('$request->tgl_app_selesai','yyyy-mm-dd') WHERE NO_REQUEST = '$request->no_req' AND NO_CONTAINER = '$request->no_cont'";
                $rcekd = DB::connection('uster')->statement($q_update_p);
                $q_update_r = "UPDATE CONTAINER_STRIPPING SET TGL_APPROVE = TO_DATE('$request->tgl_approve','yyyy-mm-dd'), TGL_APP_SELESAI = TO_DATE('$request->tgl_app_selesai','yyyy-mm-dd') WHERE NO_REQUEST = REPLACE('$request->no_req','P','S') AND NO_CONTAINER = '$request->no_cont'";
                $rcekd = DB::connection('uster')->statement($q_update_r);

                DB::commit();
                return response()->json([
                    'status' => JsonResponse::HTTP_OK,
                    'message' => 'Tgl Approve Updated',
                    'redirect' => [
                        'need' => false,
                        'to' => null,
                    ]
                ], 200);
            }

            $query_cx         = "SELECT DISTINCT PLAN_CONTAINER_STRIPPING.HZ,
                                        PLAN_CONTAINER_STRIPPING.COMMODITY,
                                        PLAN_CONTAINER_STRIPPING.UKURAN,
                                        PLAN_CONTAINER_STRIPPING.TYPE,
                                        PLAN_CONTAINER_STRIPPING.NO_BOOKING,
                                        PLAN_CONTAINER_STRIPPING.ID_YARD
                                        FROM PLAN_CONTAINER_STRIPPING
                                        WHERE PLAN_CONTAINER_STRIPPING.NO_REQUEST = '$request->no_req'
                                        AND PLAN_CONTAINER_STRIPPING.NO_CONTAINER = '$request->no_cont'";
            $row_cx = DB::connection('uster')->selectOne($query_cx);
            $hz             = $row_cx->hz;
            $komoditi         = $row_cx->commodity;
            $size             = $row_cx->ukuran;
            $type             = $row_cx->type;
            $no_booking_     = $row_cx->no_booking;
            $depo_tujuan     = $row_cx->id_yard;

            if ($request->ASAL_CONT == 'TPK') {
                $param = array(
                    "in_nocont" => $request->no_cont,
                    "in_planreq" => $request->no_req,
                    "in_reqnbs" => $request->NO_REQ2,
                    "in_asalcont" => $request->ASAL_CONT,
                    "in_container_size" => $request->CONTAINER_SIZE ?? '',
                    "in_container_type" => $request->CONTAINER_TYPE ?? '',
                    "in_container_status" => $request->CONTAINER_STATUS ?? '',
                    "in_container_hz" => $request->CONTAINER_HZ ?? '',
                    "in_container_imo" => $request->CONTAINER_IMO ?? '',
                    "in_container_iso_code" => $request->CONTAINER_ISO_CODE ?? '',
                    "in_container_height" => $request->CONTAINER_HEIGHT ?? '',
                    "in_container_carrier" => $request->CONTAINER_CARRIER ?? '',
                    "in_container_reefer_temp" => $request->CONTAINER_REEFER_TEMP ?? '',
                    "in_container_booking_sl" => $request->CONTAINER_BOOKING_SL ?? '',
                    "in_container_over_width" => $request->CONTAINER_OVER_WIDTH ?? '',
                    "in_container_over_length" => $request->CONTAINER_OVER_LENGTH ?? '',
                    "in_container_over_height" => $request->CONTAINER_OVER_HEIGHT ?? '',
                    "in_container_over_front" => $request->CONTAINER_OVER_FRONT ?? '',
                    "in_container_over_rear" => $request->CONTAINER_OVER_REAR ?? '',
                    "in_container_over_left" => $request->CONTAINER_OVER_LEFT ?? '',
                    "in_container_over_right" => $request->CONTAINER_OVER_RIGHT ?? '',
                    "in_container_un_number" => $request->CONTAINER_UN_NUMBER ?? '',
                    "in_container_pod" => $request->CONTAINER_POD ?? '',
                    "in_container_pol" => $request->CONTAINER_POL ?? '',
                    "in_container_vessel_confirm" => $request->CONTAINER_VESSEL_CONFIRM ?? null,
                    "in_container_comodity" => $komoditi != null ? trim($komoditi) : '',
                    "in_container_c_type_code" => $request->CONTAINER_COMODITY_TYPE_CODE ?? '',
                );

                $process = $this->stripping_plan->approveContTPK($request, $param);
                $statusCode = $process->getData()->status->code;

                if ($statusCode != 200) {
                    DB::rollBack();
                    throw new Exception('Gagal Approve Container' . $process->getData()->status->msg, 500);
                } else {
                    $query_insert_rec    = "INSERT INTO CONTAINER_RECEIVING(NO_CONTAINER,
                                                    NO_REQUEST,
                                                    STATUS,
                                                    AKTIF,
                                                    HZ,
                                                    TGL_BONGKAR,
                                                    KOMODITI,
                                                    DEPO_TUJUAN)
                                            VALUES('$request->no_cont',
                                                    '$request->NO_REQ_REC',
                                                    'FCL',
                                                    'Y',
                                                    '$hz',
                                                    TO_DATE('$request->tgl_bongkar','yyyy-mm-dd'),
                                                    '$komoditi',
                                                    '$depo_tujuan')";

                    $row_cx = DB::connection('uster')->statement($query_insert_rec);

                    $q_getcounter = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$request->no_cont' ORDER BY COUNTER DESC";
                    $row_cx = DB::connection('uster')->selectOne($q_getcounter);
                    $cur_counter = $row_cx->counter;
                    $cur_booking = $row_cx->no_booking;
                    $history_rec    = "INSERT INTO history_container
                                            (NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT)
                                        VALUES ('$request->no_cont','$request->NO_REQ_REC','REQUEST RECEIVING',SYSDATE,'$id_user','$id_yard','$cur_booking', '$cur_counter','FCL')";
                    $execHistory = DB::connection('uster')->statement($history_rec);

                    $query = "UPDATE PLAN_CONTAINER_STRIPPING SET TGL_APPROVE = TO_DATE('$request->tgl_approve','yyyy-mm-dd'), TGL_APP_SELESAI = TO_DATE('$request->tgl_app_selesai','yyyy-mm-dd'), REMARK = '$request->remark'
                                WHERE NO_REQUEST = '$request->no_req' AND NO_CONTAINER = '$request->no_cont'";
                    $query_r = "SELECT NO_REQUEST,
                                    --ID_YARD,
                                    KETERANGAN,
                                    -- NO_BOOKING,
                                    TGL_REQUEST,
                                    TGL_AWAL,
                                    TGL_AKHIR,
                                    NO_DO,
                                    NO_BL,
                                    TYPE_STRIPPING,
                                    STRIPPING_DARI,
                                    NO_REQUEST_RECEIVING,
                                    ID_USER,
                                    KD_CONSIGNEE,
                                    KD_PENUMPUKAN_OLEH,
                                    NO_SPPB,
                                    TGL_SPPB,
                                    AFTER_STRIP,
                                    CONSIGNEE_PERSONAL
                                FROM PLAN_REQUEST_STRIPPING
                                WHERE NO_REQUEST = '$request->no_req'";
                    $row_r    = DB::connection('uster')->selectOne($query_r);

                    $no_request_a         = $row_r->no_request;
                    $keterangan         = $row_r->keterangan;
                    $tgl_req             = $row_r->tgl_request;
                    $tgl_awal             = $row_r->tgl_awal;
                    $tgl_akhir             = $row_r->tgl_akhir;
                    $nodo                 = $row_r->no_do;
                    $nobl                 = $row_r->no_bl;
                    $types                 = $row_r->type_stripping;
                    $strip_d             = $row_r->stripping_dari;
                    $rec                 = $row_r->no_request_receiving;
                    $id_user             = $row_r->id_user;
                    $consig             = $row_r->kd_consignee;
                    $tumpuk             = $row_r->kd_penumpukan_oleh;
                    $nosppb             = $row_r->no_sppb;
                    $tglsppb             = $row_r->tgl_sppb;
                    $after_s             = $row_r->after_strip;
                    $CONSIGNEE_PERSONAL = $row_r->consignee_personal;


                    $query_c = "SELECT DISTINCT
                                    PLAN_CONTAINER_STRIPPING.AFTER_STRIP,
                                    PLAN_CONTAINER_STRIPPING.ID_YARD,
                                    PLAN_CONTAINER_STRIPPING.HZ,
                                    PLAN_CONTAINER_STRIPPING.NO_REQUEST,
                                    PLAN_CONTAINER_STRIPPING.NO_CONTAINER,
                                    PLAN_CONTAINER_STRIPPING.AKTIF,
                                    --PLAN_CONTAINER_STRIPPING.KETERANGAN,
                                    PLAN_CONTAINER_STRIPPING.TGL_APPROVE,
                                    PLAN_CONTAINER_STRIPPING.TGL_BONGKAR,
                                    PLAN_CONTAINER_STRIPPING.TGL_SELESAI,
                                    PLAN_CONTAINER_STRIPPING.VIA,
                                    PLAN_CONTAINER_STRIPPING.VOYAGE,
                                    PLAN_CONTAINER_STRIPPING.REMARK,
                                    PLAN_CONTAINER_STRIPPING.UKURAN,
                                    PLAN_CONTAINER_STRIPPING.TYPE,
                                    PLAN_CONTAINER_STRIPPING.COMMODITY
                                FROM PLAN_CONTAINER_STRIPPING
                                WHERE PLAN_CONTAINER_STRIPPING.NO_REQUEST = '$request->no_req'
                                    AND PLAN_CONTAINER_STRIPPING.NO_CONTAINER = '$request->no_cont'";
                    $row_c    = DB::connection('uster')->select($query_c);

                    $query_cek_request = "SELECT NO_REQUEST FROM REQUEST_STRIPPING WHERE NO_REQUEST = '$request->no_req'";
                    $row_cek_request    = DB::connection('uster')->select($query_cek_request);

                    $query_cek_cont = "SELECT NO_CONTAINER FROM CONTAINER_STRIPPING WHERE NO_REQUEST = '$request->no_req' AND NO_CONTAINER = '$request->no_cont'";
                    $row_cek_cont    = DB::connection("uster")->select($query_cek_cont);
                    $no_req_strip = str_replace('P', 'S', $request->no_req);
                    $query_tgl_app = "UPDATE CONTAINER_STRIPPING
                                        SET TGL_APPROVE = TO_DATE('$request->tgl_approve','yyyy-mm-dd'),
                                        TGL_APP_SELESAI = TO_DATE('$request->tgl_app_selesai','yyyy-mm-dd'),
                                        REMARK = '$request->remark'
                                    WHERE NO_REQUEST = '$no_req_strip' AND NO_CONTAINER = '$request->no_cont'";

                    if (count($row_cek_request) > 0 && count($row_cek_cont) > 0) {
                        DB::connection('uster')->statement($query);
                        DB::connection('uster')->statement($query_tgl_app);
                        DB::connection('uster')->statement("UPDATE REQUEST_STRIPPING SET STRIPPING_DARI = '$request->ASAL_CONT' WHERE NO_REQUEST = '$no_req_strip'");
                    } else if (count($row_cek_request) > 0 && count($row_cek_cont) == 0) {
                        DB::connection('uster')->statement($query);
                        $no_req_strip = str_replace('P', 'S', $request->no_req);
                        foreach ($row_c as $rc) {
                            $after_strip = $rc->after_strip;
                            $idyard_c = $rc->id_yard;
                            $hz = $rc->hz;
                            $req = $rc->no_request;
                            $cont = $rc->no_container;

                            //CEK TGL GATE
                            $tes             = "select TO_CHAR(TGL_UPDATE,'dd/mm/rrrr') TGL_GATE from history_container where no_container = '$request->no_cont' AND KEGIATAN = 'BORDER GATE IN' AND TGL_UPDATE = (SELECT MAX(TGL_UPDATE) FROM history_container WHERE NO_CONTAINER = '$request->no_cont')";
                            $gate            = DB::connection('uster')->selectOne($tes);
                            $tgl_gate         = $gate->tgl_gate;
                            $aktif             = $rc->aktif;
                            $keterangan     = $rc->keterangan;
                            $tgl_app         = $rc->tgl_approve;
                            $tgl_bongkar     = $rc->tgl_bongkar;
                            $tgl_selesai     = $rc->tgl_selesai;
                            $via             = $rc->via;
                            $commo             = $rc->commodity;
                            $voyage         = $rc->voyage;
                            $remark         = $rc->remark;
                            $query_ic        = "INSERT INTO CONTAINER_STRIPPING (NO_CONTAINER,NO_REQUEST, AKTIF,
                                                VIA, VOYAGE, TGL_BONGKAR, COMMODITY, HZ, ID_YARD, AFTER_STRIP, TGL_APPROVE, TGL_APP_SELESAI, REMARK, TGL_GATE, TGL_SELESAI)
                                                VALUES('$cont',
                                                '$no_req_strip',
                                                '$aktif',
                                                '$request->ASAL_CONT',
                                                '$voyage',
                                                '$tgl_bongkar',
                                                '$commo',
                                                '$hz',
                                                '$idyard_c',
                                                '$after_strip',
                                                TO_DATE('$request->tgl_approve','yyyy-mm-dd'),
                                                TO_DATE('$request->tgl_app_selesai','yyyy-mm-dd'),
                                                '$remark',
                                                TO_DATE('$tgl_gate','dd/mm/rrrr'),
                                                '$tgl_selesai')";

                            $execInsertContainer = DB::connection('uster')->statement($query_ic);
                        }
                    } else {
                        $exec = DB::connection('uster')->statement($query);
                        $no_req_strip = str_replace('P', 'S', $request->no_req);
                        foreach ($row_c as $rc) {
                            $after_strip     = $rc->after_strip;
                            $idyard_c         = $rc->id_yard;
                            $hz             = $rc->hz;
                            $req             = $rc->no_request;
                            $cont             = $rc->no_container;
                            $aktif             = $rc->aktif;
                            $tgl_app         = $rc->tgl_approve;
                            $tgl_bongkar     = $rc->tgl_bongkar;
                            $tgl_selesai     = $rc->tgl_selesai;
                            $via             = $rc->via;
                            $voyage         = $rc->voyage;
                            $remark         = $rc->remark;
                            $commo             = $rc->commodity;
                            $query_ic        = "INSERT INTO CONTAINER_STRIPPING (NO_CONTAINER,NO_REQUEST, AKTIF,
                                                    VIA, VOYAGE, TGL_BONGKAR,COMMODITY, HZ, ID_YARD, AFTER_STRIP, TGL_APPROVE, TGL_APP_SELESAI, REMARK, TGL_SELESAI)
                                                    VALUES('$cont',
                                                    '$no_req_strip',
                                                    '$aktif',
                                                    '$request->ASAL_CONT',
                                                    '$voyage',
                                                    '$tgl_bongkar',
                                                    '$commo',
                                                    '$hz',
                                                    '$idyard_c',
                                                    '$after_strip',
                                                    TO_DATE('$request->tgl_approve','yyyy-mm-dd'),
                                                    TO_DATE('$request->tgl_app_selesai','yyyy-mm-dd'),
                                                    '$remark',
                                                    '$tgl_selesai')";
                            $execInsertContainer = DB::connection('uster')->statement($query_ic);
                        }
                    }

                    $history_stripp        = "INSERT INTO history_container
                                                    (NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT)
                                                VALUES ('$request->no_cont','$no_req_strip','REQUEST STRIPPING',SYSDATE,'$id_user','$id_yard','$cur_booking','$cur_counter','FCL')";
                    $execHistoryStrip = DB::connection('uster')->statement($history_stripp);
                }

                DB::commit();
                return response()->json([
                    'status' => JsonResponse::HTTP_OK,
                    'message' => 'Berhasil Approve Container',
                    'redirect' => [
                        'need' => false,
                        'to' => null,
                    ]
                ], 200);
            } elseif ($request->ASAL_CONT == 'DEPO') {
                $query = "UPDATE PLAN_CONTAINER_STRIPPING SET TGL_APPROVE = TO_DATE('$request->tgl_approve','yy-mm-dd'), TGL_APP_SELESAI = TO_DATE('$request->tgl_app_selesai','yy-mm-dd'), REMARK = '$request->remark'
                                WHERE NO_REQUEST = '$request->no_req' AND NO_CONTAINER = '$request->no_cont'";

                $query_r = "SELECT NO_REQUEST,
                           --ID_YARD,
                           KETERANGAN,
                          -- NO_BOOKING,
                           TGL_REQUEST,
                           TGL_AWAL,
                           TGL_AKHIR,
                           NO_DO,
                           NO_BL,
                           TYPE_STRIPPING,
                           STRIPPING_DARI,
                           NO_REQUEST_RECEIVING,
                           ID_USER,
                           KD_CONSIGNEE,
                           KD_PENUMPUKAN_OLEH,
                           NO_SPPB,
                           TGL_SPPB,
                           AFTER_STRIP,
                           CONSIGNEE_PERSONAL
                      FROM PLAN_REQUEST_STRIPPING
                      WHERE NO_REQUEST = '$request->no_req'";
                $row_r    = DB::connection('uster')->selectOne($query_r);

                $no_request_a   = $row_r->no_request;
                $keterangan     = $row_r->keterangan;
                $tgl_req        = $row_r->tgl_request;
                $tgl_awal       = $row_r->tgl_awal;
                $tgl_akhir      = $row_r->tgl_akhir;
                $nodo           = $row_r->no_do;
                $nobl           = $row_r->no_bl;
                $types          = $row_r->type_stripping;
                $strip_d        = $row_r->stripping_dari;
                $rec            = $row_r->no_request_receiving;
                $id_user        = $row_r->id_user;
                $consig         = $row_r->kd_consignee;
                $tumpuk         = $row_r->kd_penumpukan_oleh;
                $nosppb         = $row_r->no_sppb;
                $tglsppb        = $row_r->tgl_sppb;
                $after_s        = $row_r->after_strip;
                $CONSIGNEE_PERSONAL = $row_r->consignee_personal;

                $query_c = "SELECT DISTINCT PLAN_CONTAINER_STRIPPING.AFTER_STRIP,
                                PLAN_CONTAINER_STRIPPING.ID_YARD,
                                PLAN_CONTAINER_STRIPPING.HZ,
                                PLAN_CONTAINER_STRIPPING.NO_REQUEST,
                                PLAN_CONTAINER_STRIPPING.NO_CONTAINER,
                                PLAN_CONTAINER_STRIPPING.AKTIF,
                                PLAN_CONTAINER_STRIPPING.UKURAN KD_SIZE,
                                PLAN_CONTAINER_STRIPPING.TYPE KD_TYPE,
                                PLAN_CONTAINER_STRIPPING.TGL_APPROVE,
                                PLAN_CONTAINER_STRIPPING.TGL_BONGKAR,
                                PLAN_CONTAINER_STRIPPING.TGL_SELESAI,
                                PLAN_CONTAINER_STRIPPING.VIA,
                                PLAN_CONTAINER_STRIPPING.VOYAGE,
                                PLAN_CONTAINER_STRIPPING.REMARK,
                                PLAN_CONTAINER_STRIPPING.COMMODITY
                               FROM PLAN_CONTAINER_STRIPPING
                               WHERE PLAN_CONTAINER_STRIPPING.NO_REQUEST = '$request->no_req'
                               AND PLAN_CONTAINER_STRIPPING.NO_CONTAINER = '$request->no_cont'";
                $row_c    = DB::connection('uster')->select($query_c);
                $no_req_strip = str_replace('P', 'S', $request->no_req);
                $query_cek_request = "SELECT NO_REQUEST FROM REQUEST_STRIPPING WHERE NO_REQUEST = '$no_req_strip'";
                $row_cek_request    = DB::connection('uster')->select($query_cek_request);

                $query_cek_cont = "SELECT NO_CONTAINER FROM CONTAINER_STRIPPING WHERE NO_REQUEST = '$no_req_strip' AND NO_CONTAINER = '$request->no_cont'";
                $row_cek_cont    = DB::connection('uster')->select($query_cek_cont);

                $query_tgl_app = "UPDATE CONTAINER_STRIPPING SET TGL_APPROVE = TO_DATE('$request->tgl_approve','yy-mm-dd'), TGL_APP_SELESAI = TO_DATE('$request->tgl_app_selesai','yy-mm-dd'), REMARK = '$request->remark'
                                    WHERE NO_REQUEST = '$no_req_strip' AND NO_CONTAINER = '$request->no_cont'";

                if (count($row_cek_request) > 0 && count($row_cek_cont) > 0) {
                    DB::connection('uster')->statement($query);
                    DB::connection('uster')->statement($query_tgl_app);
                    DB::connection('uster')->statement("UPDATE REQUEST_STRIPPING SET STRIPPING_DARI = '$request->ASAL_CONT' WHERE NO_REQUEST = '$no_req_strip'");
                } else if (count($row_cek_request) > 0 && count($row_cek_cont) == 0) {
                    DB::connection('uster')->statement($query);

                    foreach ($row_c as $rc) {
                        $after_strip   = $rc->after_strip;
                        $idyard_c      = $rc->id_yard;
                        $hz            = $rc->hz;
                        $req           = $rc->no_request;
                        $cont          = $rc->no_container;
                        $aktif         = $rc->aktif;
                        $keterangan    = $rc->keterangan;
                        $tgl_app       = $rc->tgl_approve;
                        $tgl_bongkar   = $rc->tgl_bongkar;
                        $tgl_selesai   = $rc->tgl_selesai;
                        $via           = $rc->via;
                        $voyage        = $rc->voyage;
                        $remark        = $rc->remark;
                        $commo         = $rc->commodity;

                        $query_ic        = "INSERT INTO CONTAINER_STRIPPING (NO_CONTAINER,NO_REQUEST, AKTIF,
                                        VIA, VOYAGE, TGL_BONGKAR, COMMODITY, HZ, ID_YARD, AFTER_STRIP, TGL_APPROVE, TGL_APP_SELESAI, REMARK,TGL_SELESAI)
                                        VALUES('$cont',
                                        '$no_req_strip',
                                        '$aktif',
                                        '$request->ASAL_CONT',
                                        '$voyage',
                                        '$tgl_bongkar',
                                        '$commo',
                                        '$hz',
                                        '$idyard_c',
                                        '$after_strip',
                                        TO_DATE('$request->tgl_approve','yy-mm-dd'),
                                        TO_DATE('$request->tgl_app_selesai','yy-mm-dd'),
                                        '$remark',
                                        '$tgl_selesai')";

                        $execContainerStrip = DB::connection('uster')->statement($query_ic);
                    }
                } else {
                    DB::connection('uster')->statement($query);
                    $no_req_strip = str_replace('P', 'S', $request->no_req);

                    foreach ($row_c as $rc) {
                        $after_strip   = $rc->after_strip;
                        $idyard_c      = $rc->id_yard;
                        $hz            = $rc->hz;
                        $req           = $rc->no_request;
                        $cont          = $rc->no_container;
                        $aktif         = $rc->aktif;
                        $keterangan    = $rc->keterangan;
                        $tgl_app       = $rc->tgl_approve;
                        $tgl_bongkar   = $rc->tgl_bongkar;
                        $tgl_selesai   = $rc->tgl_selesai;
                        $via           = $rc->via;
                        $voyage        = $rc->voyage;
                        $remark        = $rc->remark;
                        $commo         = $rc->commodity;

                        $query_ic        = "INSERT INTO CONTAINER_STRIPPING (NO_CONTAINER,NO_REQUEST, AKTIF,
                                        VIA, VOYAGE, TGL_BONGKAR, COMMODITY, HZ, ID_YARD, AFTER_STRIP, TGL_APPROVE, TGL_APP_SELESAI, REMARK, TGL_GATE, TGL_SELESAI)
                                        VALUES('$cont',
                                        '$no_req_strip',
                                        '$aktif',
                                        '$request->ASAL_CONT',
                                        '$voyage',
                                        '$tgl_bongkar',
                                        '$commo',
                                        '$hz',
                                        '$idyard_c',
                                        '$after_strip',
                                        TO_DATE('$request->tgl_approve','yy-mm-dd'),
                                        TO_DATE('$request->tgl_app_selesai','yy-mm-dd'),
                                        '$remark',
                                        TO_DATE('$request->tgl_gate','yy-mm-dd'),
                                        '$tgl_selesai')";

                        $execContainerStrip = DB::connection('uster')->statement($query_ic);
                    }
                }

                $q_getcounter2 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$request->no_cont'";
                $rw_getcounter2 = DB::connection('uster')->selectOne($q_getcounter2);
                $cur_counter2 = $rw_getcounter2->counter;
                $cur_booking2 = $rw_getcounter2->no_booking;

                $history_stripp2  = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT)
                                                              VALUES ('$request->no_cont','$no_req_strip','REQUEST STRIPPING',SYSDATE,'$id_user','$id_yard','$cur_booking2','$cur_counter2','FCL')";

                $execHistory = DB::connection('uster')->statement($history_stripp2);
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

    public function saveReq(Request $request)
    {
        DB::beginTransaction();
        try {
            $updatePlanContStrip = "UPDATE PLAN_CONTAINER_STRIPPING SET REMARK = '$request->remark' WHERE NO_REQUEST = '$request->no_req' AND NO_CONTAINER = '$request->no_cont'";
            $updateContStrip = "UPDATE CONTAINER_STRIPPING SET REMARK = '$request->remark' WHERE NO_REQUEST = REPLACE('$request->no_req', 'P' , 'S') AND NO_CONTAINER = '$request->no_cont'";

            $execPlanContStrip = DB::connection('uster')->statement($updatePlanContStrip);
            $execContStrip = DB::connection('uster')->statement($updateContStrip);

            $updatePlanStrip = "UPDATE PLAN_REQUEST_STRIPPING SET CLOSING = 'CLOSED' WHERE NO_REQUEST = '$request->no_req'";
            $execPlanStrip = DB::connection('uster')->statement($updatePlanStrip);

            if ($execPlanStrip) {
                $updateStrip = "UPDATE REQUEST_STRIPPING SET CLOSING = 'CLOSED' WHERE NO_REQUEST = REPLACE('$request->no_req', 'P' , 'S')";
                $execStrip = DB::connection('uster')->statement($updateStrip);
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
