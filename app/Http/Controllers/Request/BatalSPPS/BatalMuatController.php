<?php

namespace App\Http\Controllers\Request\BatalSPPS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use \Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Services\Request\BatalMuat\BatalMuatService;
use Exception;
class BatalMuatController extends Controller
{
    private $batalMuat;
    public function __construct(BatalMuatService $batalMuat)
    {
        $this->batalMuat = $batalMuat;
    }


    function index()
    {
        return view('request.batal-muat.index');
    }

    function add()
    {
        return view('request.batal-muat.add');
    }

    function view(Request $request)
    {
        return view('request.batal-muat.view', $this->batalMuat->GetDataByNoReq($request->input('no_req')));
    }

    public function datatable(Request $request): JsonResponse
    {
        $listBatalMuat = $this->batalMuat->getData($request);
        return Datatables::of($listBatalMuat)
            ->addColumn('action', function ($listBatalMuat) {
                $link = route('uster.koreksi.batal_muat.view');
                if ($listBatalMuat->nota == 'Y') {
                    return "<i class='fas fa-print'></i>&nbsp; LUNAS <br>
                        <a href='$link?no_req=$listBatalMuat->no_request&no_req2=$listBatalMuat->no_req_itc' class='btn btn-primary'><img src='images/ico_approval.gif' class='img-fluid'>&nbsp; preview data </a>";
                } else {
                    return "<a href='$link?no_req=$listBatalMuat->no_request' class='btn btn-success'><i class='fas fa-print'></i>&nbsp; Lihat Data</a>";
                }
            })
            ->make(true);
    }

    public function viewContainerByRequest(Request $request): JsonResponse
    {
        $listBatalMuat = $this->batalMuat->GetContainerByNoReq($request->input('no_req'));
        return Datatables::of($listBatalMuat)->make(true);
    }

    public function getPMB(Request $request)
    {
        return $this->batalMuat->getPMB($request->input('q'));
    }

    public function getContainer(Request $request)
    {
        return $this->batalMuat->getContainer($request->input('jns'), $request->input('term'));
    }

    public function prayaGetContainer(Request $request)
    {
        $no_cont = $request->no_cont;
        $jenis_bm = $request->jenis_bm;
        return getDisableContainer($no_cont, $jenis_bm);
    }

    public function getContainerHistory(Request $request)
    {
        return $this->batalMuat->getContainerHistory($request->input('no_cont'));
    }

    public function masterVesselPalapa(Request $request)
    {
        return $this->batalMuat->masterVesselPalapa($request->input('term'));
    }


    public function masterPelabuhanPalapa(Request $request)
    {
        return $this->batalMuat->masterPelabuhanPalapa($request->input('term'));
    }

    public function validateContainer(Request $request)
    {
        return $this->batalMuat->validateContainer($request);
    }

    public function save_bm_praya(Request $request)
    {
        return $this->batalMuat->save_bm_praya($request);
    }

    public function save_payment_uster_batal_muat(Request $request)
    {
        $query_cek  = "select NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_REQUEST,8,13)))+1,6,0),'000001') AS JUM,
        TO_CHAR(SYSDATE, 'MM') AS MONTH, 
        TO_CHAR(SYSDATE, 'YY') AS YEAR 
        FROM REQUEST_BATAL_MUAT
        WHERE TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE) ";

        $jum_  = DB::connection('uster')->selectOne($query_cek);
        $jum        = $jum_->jum;
        $month        = $jum_->month;
        $year        = $jum_->year;
  

        $no_req_bm    = "BMU" . $month . $year . $jum;

        $new_id_request   = $no_req_bm;
        $payload_batal_muat = $_POST["payload_batal_muat"];

        // $payload_batal_muat = array(
        //   "ex_noreq" => $ex_noreq,
        //   "vesselId" => $kd_kapal,
        //   "vesselName" => $nm_kapal,
        //   "voyage" => $voyage,
        //   "voyageIn" => $voyage_in,
        //   "voyageOut" => $voyage_out,
        //   "nm_agen" => $nm_agen,
        //   "kd_agen" => $kd_agen,
        //   "pelabuhan_tujuan" => $kd_pelabuhan_tujuan,
        //   "pelabuhan_asal" => $kd_pelabuhan_asal,
        //   "cont_list" => $_POST['BM_CONT'],
        // );

        set_time_limit(360);

        try {

            $curl = curl_init();
            /* set configure curl */
            // $authorization = "Authorization: Bearer $token";
            $payload_request = array(
                "ID_REQUEST" => $new_id_request,
                "JENIS" => "BATAL_MUAT",
                "BANK_ACCOUNT_NUMBER" => "",
                "PAYMENT_CODE" => "",
                "PAYLOAD_BATAL_MUAT" => $payload_batal_muat
            );
            // echo json_encode($payload_request) . '<<payload_req';
            $url = ENV('APP_URL') . "/uster.billing.paymentcash.ajax/save_payment_external";
            // echo var_dump($url);
            // die();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL             => $url,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_ENCODING        => "",
                    CURLOPT_MAXREDIRS       => 10,
                    CURLOPT_CUSTOMREQUEST   => "POST",
                    CURLOPT_POSTFIELDS      => json_encode($payload_request),
                    CURLOPT_HTTPHEADER      => array(
                        "Content-Type: application/json"
                    ),
                )
            );


            $response = curl_exec($curl);
            // $err = curl_error($curl);
            // echo var_dump($response);

            if ($response === false) {
                throw new Exception(curl_error($curl));
            }

            // Get HTTP status code
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            //Success
            if ($httpCode >= 200 && $httpCode < 300) {
                $response_curl = array(
                    'status'   => 'success',
                    'httpCode' => $httpCode,
                    'response' => $response
                );
            } else if ($httpCode >= 400 && $httpCode < 500) {
                //Client Error
                $response_curl = array(
                    'status'   => 'error',
                    'httpCode' => $httpCode,
                    'response' => $response
                );
            } else {
                //Server Error
                throw new Exception('HTTP Server Error: ' . $httpCode);
            }

            /* execute curl */
            curl_close($curl);

            echo $response_curl['response'];
            exit();
        } catch (Exception $e) {
            // echo $e . "<< error-aftercurl";
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response_curl = array(
                'status'   => 'error',
                'httpCode' => $httpCode,
                'response' => "cURL Error # " . $e->getMessage()
            );

            echo $response_curl;
            exit();
        }
    }
}
