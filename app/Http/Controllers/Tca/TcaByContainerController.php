<?php

namespace App\Http\Controllers\Tca;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TcaByContainerController extends Controller
{
    public function index()
    {
        return view('tca.tca_by_container');
    }

    public function getInvoiceNumbers(Request $request)
    {
        $invoice = strtoupper($request->query('term'));

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

        $results = DB::connection('uster')->select($query);

        return response()->json($results);
    }

    public function list_by_cont_praya(Request $request)
    {
        $id_req = $request->NO_REQ;
        $cancel = $request->CANCEL ?? NULL;

        try {
            $payload = array(
                "idRequest" => $id_req,
                "orgId" => ENV('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => ENV('PRAYA_ITPK_PNK_TERMINAL_ID'),
            );

            if ($cancel == "Y") {
                $payload["tcaCancelation"] = true;
            }

            $response = sendDataFromUrl($payload, ENV('PRAYA_API_TOS') . "/api/getRequestTca", 'POST', getTokenPraya());
            $response = json_decode($response['response'], true);

 
            if ($response['code'] == 1 && !empty($response["dataRec"])) {
                echo json_encode($response['dataRec']);
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }



    function get_truck_list(Request $request)
    {
        $truckid = strtoupper($request->term);
        set_time_limit(360);

        try {
            $payload = array(
                "search" => $truckid,
                // "tid" => $,
                "orgId" => ENV('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => ENV('PRAYA_ITPK_PNK_TERMINAL_ID'),
            );

            $response = sendDataFromUrl($payload, ENV('PRAYA_API_TOS') . "/api/truckList", 'POST', getTokenPraya());
            $response = json_decode($response['response'], true);

            if ($response['code'] == 1 && !empty($response["data"])) {
                $row =  $response['data'];


                echo json_encode($row);
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

    public function save_associate_praya(Request $request)
    {
        $multiple = $request->multiple;

        $terminal_arr = array(
            "orgId" => ENV('PRAYA_ITPK_PNK_ORG_ID'),
            "terminalId" => ENV('PRAYA_ITPK_PNK_TERMINAL_ID')
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
                "truckType" => $_POST['truckType'],
                "truckNumber" => $_POST['truckNumber'],
                "createdBy" => $_POST['createdBy'],
                "tid" => $_POST['tid'],
                "axle" => $_POST['axle'],
                "type" => $_POST['type'],
                "actionCode" => $_POST['actionCode'],
                "detail" => $_POST['detail']
            );
            $payload = array_merge($terminal_arr, $payload);
        }

        // echo json_encode($payload);

        try {
            $response = sendDataFromUrlTryCatch($payload, ENV('PRAYA_API_TOS') . '/api/tcaSaveContainerNew', 'POST', getTokenPraya());
            if ($response['httpCode'] < 200 && $response['httpCode'] >= 300) {
                $response_decode = json_decode($response['response'], true);
                $msg = $response_decode['msg'] ? $response_decode['msg'] : $response['response'];
                $res = array(
                    "code" => "0",
                    "msg" => "Error " . $response['httpCode'] . " : " . $msg
                );
                echo json_encode($res);
                insertPrayaServiceLog(ENV('PRAYA_API_TOS') . '/api/tcaSaveContainerNew', $payload, $res, "TCA Save Container");
                exit();
            } else {
                echo $response['response'];
                insertPrayaServiceLog(ENV('PRAYA_API_TOS') . '/api/tcaSaveContainerNew', $payload, $response['response'], "TCA Save Container");
                exit();
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
}
