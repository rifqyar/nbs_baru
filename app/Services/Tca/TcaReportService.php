<?php

namespace App\Services\Tca;

use App\Services\Others\Praya;
use Exception;
use Illuminate\Support\Facades\DB;

class TcaReportService
{
    protected $praya;

    public function __construct(Praya $praya)
    {
        $this->praya = $praya;
    }

    function DataTable($request)
    {
        $voyage      = $request->voyage;
        $destination    = $request->terminal;
        $page = $request->page;
        $rowNum = $request->rowNum;
        if (!isset($page) || $page == "undefined") {
            $page = 1;
        }

        $payload = array(
            "terminalCode" => env('PRAYA_ITPK_PNK_TERMINAL_CODE'),
            "search" => $voyage,
            "record" => $rowNum,
            "destination" => $destination,
            "page" => $page
        );


        try {
            $response = $this->praya->sendDataFromUrlTryCatch($payload, env('PRAYA_API_TOS') . '/api/getReportTca', 'POST', $this->praya->getTokenPraya());
            if ($response['httpCode'] < 200 && $response['httpCode'] >= 300) {
                $response_decode = json_decode($response['response'], true);
                $msg = $response_decode['msg'] ? $response_decode['msg'] : $response['response'];
                echo json_encode(array(
                    "code" => "0",
                    "msg" => "Error " . $response['httpCode'] . " : " . $msg
                ));
            } else {
                $response_decode = json_decode($response['response'], true);
                $row = $response_decode['dataRec']['dataReq'];
                $pagination = $response_decode['dataRec']['pagination'];
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }


        $i = 0;
        foreach ($row as $rowm) {
            $data->rows[$i]['id'] = $rowm["NO_CONTAINER"];
            $data->rows[$i]['cell'] = array($rowm["NO_CONTAINER"], $rowm["F_TCATN"], $rowm["TRUCK_NUMBER"], $rowm["F_TCAST"], $rowm["ACTIVITY"]);
            // $data-
            $i++;
        }

        $data->total = $pagination['totalPage'];
        $data->records = $pagination['totalRow'];
        $data->page = $page;
        $data->rowNum = $rowNum;

        echo json_encode($data);
    }
    function masterVessel($request)
    {
        $term = strtoupper($request);

        $term = str_replace(" ", "+", $term);

        try {
            $json = $this->praya->getDatafromUrl(env('PRAYA_API_TOS') . "/api/getVessel?pol=" . env('PRAYA_ITPK_PNK_PORT_CODE') . "&orgId=" . env('PRAYA_ITPK_PNK_ORG_ID') . "&terminalId=" . env('PRAYA_ITPK_PNK_TERMINAL_ID') . "&search=$term");
            $json = json_decode($json, true);

            if ($json['code'] == 1) {
                echo json_encode($json['data']);
            } else {
                echo '<script>alert(\'' . $json['msg'] . '\')</script>';
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
