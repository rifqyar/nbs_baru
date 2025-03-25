<?php

namespace App\Http\Controllers\Request\Receiving;

use App\Http\Controllers\Controller;
use App\Services\Request\Receiving\ReceivingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use App\Traits\NpwpCheckPengkinianTrait;

class RequestReceivingController extends Controller
{
    use NpwpCheckPengkinianTrait;

    protected $receiving;

    public function __construct(ReceivingService $receiving)
    {
        $this->receiving = $receiving;
    }

    public function index()
    {
        return view('request.receiving.index');
    }

    public function data(Request $reqeust)
    {
        $data = $this->receiving->getData($reqeust);
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tgl_request', function ($data) {
                return '<span class="badge badge-pill badge-success p-2"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tgl_request)->translatedFormat('d M Y H:i') . ' </span>';
            })
            ->editColumn('nama_emkl', function ($data) {
                return Str::length($data->nama_emkl) > 40 ? Str::substr($data->nama_emkl, 0, 40) . '...' : $data->nama_emkl;
            })
            ->editColumn('action', function ($data) {
                return self::renderAction($data);
            })
            ->rawColumns(['tgl_request', 'action'])
            ->make(true);
    }

    private function renderAction($data)
    {
        $nota        = $data->nota_cek_nota;
        $koreksi    = $data->koreksi_cek_nota;
        $lunas    = $data->lunas_cek_nota;
        $noReq = base64_encode($data->no_request);
        $html = '';

        if ($lunas == 'NO') {
            $html = '<a href="' . url('/request/receiving/view/' . $noReq) . '" class="badge badge-pill badge-warning p-2 w-100"> Edit <i class="mdi mdi-pencil-box ml-1"></i> </a> ';
        } else {
            if ($nota != 'Y' && $koreksi != 'Y') {
                $html = '<a href="' . url('/request/receiving/view/' . $noReq) . '" class="badge badge-pill badge-warning p-2 w-100">  Edit <i class="mdi mdi-pencil-box ml-1"></i> </a> ';
            } else if ($nota == 'Y' && $koreksi != 'Y') {
                if ($data->status_nota_header = '2') {
                    $html = '<a href="' . url('/request/receiving/overview/' . $noReq) . '" class="badge badge-pill badge-success p-2 w-100"> Nota sudah cetak <i class="mdi mdi-check-circle ml-1"></i> </a> ';
                } else {
                    $html = '<a href="' . url('/request/receiving/view/' . $noReq) . '" class="badge badge-pill badge-info p-2 w-100"> Proforma sudah cetak <i class="mdi mdi-check-circle ml-1"></i> </a>';
                    $html .= '<br>';
                    $html .= '<a href="' . url('/request/receiving/view/' . $noReq) . '" class="badge badge-pill badge-warning py-2 w-100 px-3 mt-2"> Edit <i class="mdi mdi-pencil-box ml-1"></i> </a> ';
                }
            } else if ($nota == 'Y' && $koreksi == 'Y') {
                $html = '<a href="' . url('/request/receiving/view/' . $noReq) . '" class="badge badge-pill badge-info p-2 w-100"> Proforma sudah cetak <i class="mdi mdi-check-circle ml-1"></i> </a>';
                $html .= '<br>';
                $html .= '<a href="' . url('/request/receiving/view/' . $noReq) . '" class="badge badge-pill badge-warning py-2 w-100 px-3 mt-2"> Edit <i class="mdi mdi-pencil-box ml-1"></i> </a> ';
            } else if ($nota == 'F' && $koreksi != 'Y') {
                $html = '<a href="' . url('/request/receiving/overview/' . $noReq) . '" class="badge badge-pill badge-success p-2 w-100"> Nota sudah cetak <i class="mdi mdi-check-circle ml-1"></i>  </a> ';
            } else if ($nota != 'Y' && $koreksi == 'Y') {
                $html = '<a href="' . url('/request/receiving/view/' . $noReq) . '" class="badge badge-pill badge-warning p-2 w-100"> Edit <i class="mdi mdi-pencil-box ml-1"></i> </a> ';
            }
        }

        return $html;
    }

    public function addRequest()
    {
        return view('request.receiving.add');
    }

    public function overview($noReq)
    {
        $noReq = base64_decode($noReq);
        $data['request'] = $this->receiving->getOverviewData($noReq, 'overview');
        $data['request'] = $data['request'][0];
        $data['container'] = $this->receiving->contList($noReq);
        $data['overview'] = true;

        return view('request.receiving.overview-nota', $data);
    }

    public function view($noReq)
    {
        // Validate Paid
        $noReq = base64_decode($noReq);
        $queryCek = "SELECT PAID_VALIDATE('$noReq') CEK FROM DUAL";
        $dataCek = DB::connection('uster')->select($queryCek);

        // if($dataCek[0]->cek > 0){
        //     return redirect()->route('uster.new_request.receiving.receiving_luar')->with(['notifCekPaid' => 'Nota dengan nomor request <b> ' . $noReq . ' </b> belum dilakukan pembayaran']);
        // }

        $data['request'] = $this->receiving->getOverviewData($noReq, 'view');
        $data['request'] = $data['request'][0];
        $data['overview'] = false;

        return view('request.receiving.view-nota', $data);
    }

    public function contList(Request $request)
    {
        $overview = false;
        $data = $this->receiving->contList($request->no_request);
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('action', function ($data) use ($request) {
                $html = "<button class='btn btn-rounded btn-danger' onclick='delCont(`{{base64_encode($data->no_container)}}`, `{{base64_encode($request->no_request)}}`)'>
                            <i class='mdi mdi-delete h5'></i>
                        </button>";

                return $html;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function addEdit(Request $request)
    {
        $validatedNpwp = $this->validateNpwp($request);

        // Check if the response is a failed validation JSON response
        if ($validatedNpwp instanceof \Illuminate\Http\JsonResponse) {
            return $validatedNpwp; // Return error response if NPWP validation failed
        }

        $validatePconnect = pconnectIntegration($request->acc_consignee);
        if ($validatePconnect != 'MATCH') {
            if ($validatePconnect == '404') {
                throw new Exception('Data Customer tidak ditemukan di PConnect', 400);
            } else if ($validatePconnect == 'BELUM PENGKINIAN NPWP') {
                throw new Exception('Customer belum melakukan pengkinian data NPWP di Pconnect', 400);
            }
        }

        DB::beginTransaction();
        try {
            $noReq = '';
            if ($request->type == 'edit') {
                $data = array(
                    'KD_CONSIGNEE' => $request->kd_consignee,
                    'KD_PENUMPUKAN_OLEH' => $request->kd_consignee,
                    'NM_CONSIGNEE' => $request->consignee,
                    'RECEIVING_DARI' => 'LUAR',
                    'NO_RO'       => $request->no_ro,
                    'KETERANGAN' => $request->keterangan,
                    'DI'            => $request->di
                );
            } else {
                $noReq = generateNoReqReceiving();
                $data = array(
                    'NO_REQUEST' => $noReq,
                    'KD_CONSIGNEE' => $request->id_consignee,
                    'NM_CONSIGNEE' => $request->consignee,
                    'KD_PENUMPUKAN_OLEH' => $request->id_consignee,
                    'TGL_REQUEST' => OCI_SYSDATE,
                    'KETERANGAN' => $request->keterangan,
                    'CETAK_KARTU' => '0',
                    'ID_USER' => Session::get('id'),
                    'ID_YARD' => 1,
                    'RECEIVING_DARI' => $request->rec_dari,
                    'NO_RO' => $request->no_ro,
                    'DI' => $request->di
                );
            }

            $process = $this->receiving->addEdit($data, $request->no_req);
            $statusCode = $process->getData()->status->code;

            if ($statusCode != 200) {
                throw new Exception('Gagal Update Data', 500);
            }

            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => $request->type == 'edit' ? 'Berhasil Rubah Data Request Receiving' : 'Berhasil membuat request receiving baru',
                'redirect' => [
                    'need' => $request->type == 'add' ? true : false,
                    'to' => $request->type == 'add' ? route('uster.new_request.receiving.view', base64_encode($noReq)) : null,
                ]
            ]);
            DB::commit();
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
            $this->validate($request, [
                'NO_CONTAINER' => 'max:11'
            ]);

            $validate = $this->validasiSaveCont($request);
            $ukk = $validate['ukk'];
            $ex_kapal = $validate['ex_kapal'];

            $cekCont = $this->cekCont($request);

            $data = array(
                'NO_CONTAINER' => $request->no_cont,
                'NO_REQUEST' => $request->no_req,
                'STATUS' => $request->status,
                'AKTIF' => 'Y',
                'HZ' => $request->berbahaya,
                'NO_BOOKING' => $ukk,
                'KOMODITI' => $request->komoditi,
                'DEPO_TUJUAN' => $request->depo_tujuan,
                'VIA' => $request->via,
                'EX_KAPAL' => $ex_kapal,
                'KD_OWNER' => $request->kd_owner,
                'NM_OWNER' => $request->owner
            );

            $input = $this->receiving->saveCont($data);
            DB::commit();
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Tambah Container',
                'redirect' => [
                    'need' => false,
                    'to' => null, //route('uster.new_request.receiving.view', base64_encode($request->no_req)),
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

    function cekCont($request)
    {
        // Cek Container
        $queryCekCont = "SELECT NO_CONTAINER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '" . $request->no_cont . "'";
        $result_cek_cont = DB::connection('uster')->selectOne($queryCekCont);
        $cek_cont         = $result_cek_cont;
        if ($cek_cont == null) {
            // insert into master
            $queryInsertCont = generateQuerySimpan([
                'NO_CONTAINER' => $request->no_cont,
                'SIZE_' => $request->size,
                'TYPE_' => $request->type,
                'LOCATION' => 'GATO',
                'NO_BOOKING' => 'VESSEL_NOTHING',
                'COUNTER' => 1
            ]);

            $query_insert_mstr    = "INSERT INTO MASTER_CONTAINER $queryInsertCont";
            DB::connection('uster')->statement($query_insert_mstr);
        } else {
            $query_counter = "SELECT COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$request->no_cont'";
            $rw_counter = DB::connection('uster')->selectOne($query_counter);
            $last_counter = $rw_counter->counter + 1;

            $queryUpdate = generateQueryEdit([
                'NO_BOOKING' => 'VESSEL_NOTHING',
                'COUNTER' => $last_counter,
                'SIZE_' => $request->size,
                'TYPE_' => $request->type,
                'LOCATION' => 'GATO'
            ]);

            $q_update_book2 = "UPDATE MASTER_CONTAINER SET $queryUpdate WHERE NO_CONTAINER = '$request->no_cont'";
            DB::connection('uster')->statement($q_update_book2);
        }

        //Insert ke history container
        $q_getcounter = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$request->no_cont'";
        $rw_getcounter = DB::connection('uster')->selectOne($q_getcounter);
        $cur_counter = $rw_getcounter->counter;
        $cur_booking = $rw_getcounter->no_booking;
        $queryInsertHistory = generateQuerySimpan([
            'NO_CONTAINER' => $request->no_cont,
            'NO_REQUEST' => $request->no_req,
            'KEGIATAN' => 'REQUEST RECEIVING',
            'TGL_UPDATE' => Carbon::now(),
            'ID_USER' => Session::get('id'),
            'ID_YARD' => 1,
            'STATUS_CONT' => $request->status,
            'NO_BOOKING' => $cur_booking,
            'COUNTER' => $cur_counter
        ]);
        $query_insert_history = "INSERT INTO HISTORY_CONTAINER $queryInsertHistory";
        DB::connection('uster')->statement($query_insert_history);
    }

    function validasiSaveCont($request)
    {
        $queryCekGato = "SELECT AKTIF
                            FROM CONTAINER_DELIVERY
                        WHERE NO_CONTAINER = '$request->no_cont' AND AKTIF = 'Y' ORDER BY AKTIF DESC";
        $cekGato = DB::connection('uster')->select($queryCekGato);
        if (count($cekGato) > 0 && $cekGato[0]->aktif == 'Y') {
            throw new Exception('Container Masih Aktif di Request SP2 / Belum Gate Out, Cek History', 400);
        }
        $queryCekStuf = "SELECT AKTIF
                            FROM CONTAINER_STUFFING
                        WHERE NO_CONTAINER = '$request->no_cont' AND AKTIF = 'Y' ORDER BY AKTIF DESC";
        $cekStuf = DB::connection('uster')->select($queryCekStuf);
        if (count($cekStuf) > 0 && $cekStuf[0]->aktif == 'Y') {
            throw new Exception('Container Masih Aktif di Request Stuffing', 400);
        }
        $queryCekStrip = "SELECT AKTIF
                            FROM CONTAINER_STRIPPING
                        WHERE NO_CONTAINER = '$request->no_cont' AND AKTIF = 'Y'";
        $cekStrip = DB::connection('uster')->select($queryCekStrip);
        if (count($cekStrip) > 0 && $cekStrip[0]->aktif == 'Y') {
            throw new Exception('Container Masih Aktif di Request Stripping', 400);
        }

        $cekMaster = "SELECT COUNT(*) CEK
                    FROM MASTER_CONTAINER
                    WHERE NO_CONTAINER = '$request->no_cont'";
        $cekMaster = DB::connection('uster')->selectOne($cekMaster);
        $aktif = '';
        if ($cekMaster->cek == 0) {
            $aktif == 'T';
        } else {
            $cek_locate = "SELECT LOCATION, MLO, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$request->no_cont'";
            $rw_locate =  DB::connection('uster')->selectOne($cek_locate);
            $count_hist = DB::connection('uster')->selectOne("SELECT COUNT(*) JUM FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$request->no_cont'");
            if ($count_hist->jum > 1 && $rw_locate->location != "GATO") {
                throw new Exception('Container Masih Aktif di Siklus Sebelumnya / Belum GATO', 400);
            }

            $query_cek1        = " SELECT CONTAINER_RECEIVING.AKTIF
					   FROM CONTAINER_RECEIVING, REQUEST_RECEIVING
					   WHERE CONTAINER_RECEIVING.NO_REQUEST = REQUEST_RECEIVING.NO_REQUEST
					   AND CONTAINER_RECEIVING.NO_CONTAINER = '$request->no_cont'
					   AND REQUEST_RECEIVING.TGL_REQUEST = (SELECT MAX(REQUEST_RECEIVING.TGL_REQUEST)
					   FROM CONTAINER_RECEIVING, REQUEST_RECEIVING
					   WHERE CONTAINER_RECEIVING.NO_REQUEST = REQUEST_RECEIVING.NO_REQUEST
					   AND CONTAINER_RECEIVING.NO_CONTAINER = '$request->no_cont')";

            $query_rec_dari = "SELECT RECEIVING_DARI
                                    FROM REQUEST_RECEIVING
                                    WHERE NO_REQUEST = '$request->no_req'";

            $result_rec_dari = DB::connection('uster')->selectOne($query_rec_dari);
            $rec_dari = $result_rec_dari->receiving_dari;

            $result_cek1    = DB::connection('uster')->selectOne($query_cek1);
            $aktif            = $result_cek1->aktif ?? 'T';
        }

        if ($aktif == 'Y') {
            throw new Exception('Container Sudah Terdaftar Receiving', 400);
        } else if (($aktif != "Y") &&  ($request->berbahaya != NULL) && $request->status != NULL) {
            $ukk_ = $request->id_vsb;
            $ex_kapal = $request->vessel;
            if ($ukk_ == "") {
                $ukk_ = "NO";
                $ex_kapal = "VESSEL_NOTHING";
            }

            return ['ukk' => $ukk_, 'ex_kapal' => $ex_kapal, 'canInsert' => true];
        } else {
            throw new Exception('Terjadi Kesalahan yang tidak diketahui, harap hubungi tim IT', 500);
        }
    }

    public function delCont($noCont, $noReq)
    {
        $noCont = base64_decode($noCont);
        $noReq = base64_decode($noReq);

        DB::beginTransaction();
        try {
            $input = $this->receiving->delContProcess($noCont, $noReq);
            DB::commit();
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Menhapus Container',
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

    // Get Master Data
    public function getDataPBM(Request $request)
    {
        $data['PBM'] = $this->receiving->getPbm(strtoupper($request->search));
        return response()->json($data['PBM']);
    }

    public function getDataContainer(Request $request)
    {
        $data['Container'] = $this->receiving->getContainer($request->search);
        return response()->json($data['Container']);
    }

    public function getDataKomoditi(Request $reqeust)
    {
        $data['komoditi'] = $this->receiving->getKomoditi($reqeust->search);
        return response()->json($data['komoditi']);
    }

    public function getDataOwner(Request $request)
    {
        $data['owner'] = $this->receiving->getOwner($request->search);
        return response()->json($data['owner']);
    }

    public function getContList($noReq)
    {
        $data['container'] = $this->receiving->contList($noReq);
        $data['no_req'] = $noReq;
        return response()->json($data);
    }
}
