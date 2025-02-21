<?php

namespace App\Services\Tca;

use App\Services\Others\Praya;
use App\Services\Tca;
use Exception;
use Illuminate\Support\Facades\DB;

class TcaService
{
    protected $praya;

    public function __construct(Praya $praya)
    {
        $this->praya = $praya;
    }

    function DataTable($request)
    {
        $id_req = $request->NO_REQ;
        $jn_req = substr($id_req, 0, 2);
        set_time_limit(360);
        
        try {
            $payload = array(
                "idRequest" => $id_req,
                "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID')
            );

            $response = $this->praya->sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/getRequestTca", 'POST', $this->praya->getTokenPraya());
            $response = json_decode($response['response'], true);            
            if ($response['code'] == 1 && !empty($response["dataRec"])) {
                return $response['dataRec'];
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    function getTruckList($request)
    {
        $truckid = strtoupper($request);
        set_time_limit(360);

        try {
            $payload = array(
                "search" => $truckid,
                // "tid" => $,
                "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID')
            );

            $response = $this->praya->sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/truckList", 'POST', $this->praya->getTokenPraya());
            $response = json_decode($response['response'], true);

            if ($response['code'] == 1 && !empty($response["data"])) {
                return $response['data'];
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    function invoiceNumberPraya($request)
    {
        $invoice            = strtoupper($request);
        $query = "SELECT * FROM (SELECT
            NO_NOTA,
            NO_REQUEST,
            EMKL,
            LUNAS
            FROM
                NOTA_STRIPPING nstr
            WHERE nstr.LUNAS = 'YES'
            UNION 
                SELECT
                nstf.NO_NOTA,
                nstf.NO_REQUEST,
                nstf.EMKL,
                nstf.LUNAS
            FROM
                NOTA_STUFFING nstf
            JOIN REQUEST_STUFFING rs ON
                nstf.NO_REQUEST = rs.NO_REQUEST
            WHERE
                rs.STUFFING_DARI = 'TPK' AND nstf.LUNAS = 'YES')
            WHERE (NO_NOTA LIKE '$invoice%' OR NO_REQUEST LIKE '$invoice%')";

        return DB::connection('uster')->select($query);
    }

    function saveAssociatePraya($request)
    {
        $multiple = $request->multiple;

        $terminal_arr = array(
            "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
            "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID')
        );

        if ($multiple == "Y") {
            $data = $request->data;
            $new_data = array();
            foreach ($data as $arr => $value) {
                array_push($new_data, array_merge($terminal_arr, $value));
            }
            $payload = array(
                "multiple" => "Y",
                "data" => $new_data
            );
        } else {
            $payload = array(
                "truckType" => $request->truckType,
                "truckNumber" => $request->truckNumber,
                "createdBy" => $request->createdBy,
                "tid" => $request->tid,
                "axle" => $request->axle,
                "type" => $request->type,
                "actionCode" => $request->actionCode,
                "detail" => $request->detail
            );
            $payload = array_merge($terminal_arr, $payload);
        }

        // echo json_encode($payload);

        try {
            $response = $this->praya->sendDataFromUrlTryCatch($payload, env('PRAYA_API_TOS') . '/api/tcaSaveContainerNew', 'POST', $this->praya->getTokenPraya());
            if ($response['httpCode'] < 200 && $response['httpCode'] >= 300) {
                $response_decode = json_decode($response['response'], true);
                $msg = $response_decode['msg'] ? $response_decode['msg'] : $response['response'];
                $res = array(
                    "code" => "0",
                    "msg" => "Error " . $response['httpCode'] . " : " . $msg
                );
                echo json_encode($res);
                $this->praya->insertPrayaServiceLog(env('PRAYA_API_TOS') . '/api/tcaSaveContainerNew', $payload, $res, "TCA Save Container");
                exit();
            } else {
                echo $response['response'];
                $this->praya->insertPrayaServiceLog(env('PRAYA_API_TOS') . '/api/tcaSaveContainerNew', $payload, $response['response'], "TCA Save Container");
                exit();
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
}
