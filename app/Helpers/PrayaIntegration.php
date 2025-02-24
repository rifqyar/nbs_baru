<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('getTokenPraya')) {
    function getTokenPraya()
    {
        $data_payload = array(
            "username" => "adminnbs",
            "password" => "Nbs2023!",
            "statusApp" => "Web"
        );
        $response = sendDataFromUrl($data_payload, env('PRAYA_API_LOGIN') . "/api/login");
        $obj = json_decode($response['response'], true);
        return $obj["token"];
    }
}

if (!function_exists('sendDataFromUrl')) {
    function sendDataFromUrl($payload_request, $url, $method = "POST", $token = "")
    {
        $curl = curl_init();
        /* set configure curl */
        $authorization = "Authorization: Bearer $token";
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL             => $url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 60,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => $method,
                CURLOPT_POSTFIELDS      => json_encode($payload_request),
                CURLOPT_HTTPHEADER      => array(
                    "Content-Type: application/json",
                    $authorization
                ),
                CURLOPT_SSL_VERIFYPEER => false
            )
        );

        /* execute curl */
        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        /* get response */
        if ($err) {
            $response_curl = array(
                'status'   => 'error',
                'response' => "cURL Error #:" . $err
            );
        } else {
            $response_curl = array(
                'status'   => 'success',
                'response' => $response
            );
        }

        return $response_curl;
    }
}

if (!function_exists('batalContainer')) {
    function batalContainer($no_container, $requestId, $note = null)
    {
        try {

            $payload_batal = array(
                "requestId" => $requestId,
                "terminalCode" => env('PRAYA_ITPK_PNK_TERMINAL_CODE'),
                "containerNo" => $no_container,
            );

            $url_batal = env('PRAYA_API_TOS') . "/api/usterProcess";
            $response_batal = sendDataFromUrl($payload_batal, $url_batal, 'POST', getTokenPraya());
            $response_batal = json_decode($response_batal['response'], true);

            insertPrayaServiceLog($url_batal, $payload_batal, $response_batal, $note);

            return response()->json([
                'code' => 200,
                'msg' => 'Berhasil Membatalkan Container Praya'
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                'code' => $ex->getCode() == '' ? 500 : $ex->getCode(),
                'msg' => $ex->getMessage() == '' ? 'Gagal Membatalkan Container' : $ex->getMessage(),
            ], $ex->getCode() == '' ? 500 : $ex->getCode());
        }
    }
}


if (!function_exists('insertPrayaServiceLog')) {
    function insertPrayaServiceLog($url, $payload, $response, $notes)
    {
        try {
            $query_insert = "INSERT INTO PRAYA_SERVICE_LOGS
                                            (
                                             URL,
                                             PAYLOAD,
                                             RESPONSE,
                                             CODE,
                                             NOTES
                                            ) VALUES
                                            (
                                            '$url',
                                            '" . json_encode($payload) . "',
											'" . json_encode($response) . "',
											'" . $response['code'] . "',
											'$notes')";

            DB::connection('uster')->statement($query_insert);
            return response()->json([
                'code' => 200,
                'msg' => 'Berhasil Input Log Praya'
            ], 200);
        } catch (Exception $ex) {
            return response()->json([
                'code' => $ex->getCode() == '' ? 500 : $ex->getCode(),
                'msg' => $ex->getMessage() == '' ? 'Gagal Input Log Praya' : $ex->getMessage(),
            ], $ex->getCode() == '' ? 500 : $ex->getCode());
        }
    }
}

if (!function_exists('getDisableContainer')) {
    function getDisableContainer($no_container, $jenis_bm = null, $note = null)
    {
        try {

            $payload_disable_container = array(
                "orgId" => ENV('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => ENV('PRAYA_ITPK_PNK_TERMINAL_ID'),
                "noContainer" => $no_container,
            );

            $url_disable_container = ENV('PRAYA_API_TOS') . "/api/disableContainer";
            $response_disable_container = sendDataFromUrl($payload_disable_container, $url_disable_container, 'POST', getTokenPraya());

            $response_disable_container = json_decode($response_disable_container['response'], true);

            $notes = $jenis_bm ? 'batal muat -> ' . $jenis_bm . ' -> ex repo' : $note;

            insertPrayaServiceLog($url_disable_container, $payload_disable_container, $response_disable_container, $notes);

            return $response_disable_container;
        } catch (Exception $ex) {
            echo $ex->getMessage();

            return array(
                'code' => "0",
                'msg' => $ex->getMessage()
            );
        }
    }
}


if (!function_exists('disableContainerSave')) {
    function disableContainerSave($data, $jenis_bm = null, $note = null)
    {
        try {

            $payload_disable_container_save = array(
                "containerNo" => $data['dataRec'][0]['containerNo'],
                "isoCode" => $data['dataRec'][0]['isoCode'],
                "containerSize" => $data['dataRec'][0]['containerSize'],
                "containerType" => $data['dataRec'][0]['containerType'],
                "containerStatus" => $data['dataRec'][0]['containerStatus'],
                "containerHeight" => $data['dataRec'][0]['containerHeight'],
                "hz" => $data['dataRec'][0]['hz'],
                "disturb" => $data['dataRec'][0]['disturb'],
                "ei" => $data['dataRec'][0]['ei'],
                "reeferNor" => $data['dataRec'][0]['reeferNor'],
                "flagOog" => $data['dataRec'][0]['flagOog'],
                "orgId" => ENV('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => ENV('PRAYA_ITPK_PNK_TERMINAL_ID'),
                "requestId" => $data['dataRec'][0]['requestId'],
                "trxNumber" => $data['dataRec'][0]['trxNumber'],
                "via" => $data['dataRec'][0]['via'],
                "requestDate" => date('Y-m-d H:i:s'),
                "requestType" => "DISABLE CONTAINER",
                "serviceCode" => "DSB",
                "customerCode" => $data['dataRec'][0]['customerCode'],
                "customerName" => $data['dataRec'][0]['customerName'],
                "npwp" => $data['dataRec'][0]['npwp'],
                "customerAddress" => $data['dataRec'][0]['customerAddress'],
                "vesselId" => $data['dataRec'][0]['vesselId'],
                "vesselName" => $data['dataRec'][0]['vesselName'],
                "voyage" => $data['dataRec'][0]['voyage'],
                "voyageIn" => $data['dataRec'][0]['voyageIn'],
                "voyageOut" => $data['dataRec'][0]['voyageOut'],
                "eta" => $data['dataRec'][0]['eta'],
                "etd" => $data['dataRec'][0]['etd'],
                "tradeType" => $data['dataRec'][0]['tradeType'],
                "approval" => $data['dataRec'][0]['approval'],
                "approvalDate" => $data['dataRec'][0]['approvalDate'],
                "approvalBy" => $data['dataRec'][0]['approvalBy'],
                "status" => $data['dataRec'][0]['status'],
                "title" => $data['dataRec'][0]['containerNo'],
                "subTitle" => "",
                "subTitle2" => "",
                "remark" => "",
                "changeBy" => "adminnbs"
            );

            $url_disable_container_save = ENV('PRAYA_API_TOS') . "/api/disableContainerSave";

            $response_disable_container_save = sendDataFromUrl($payload_disable_container_save, $url_disable_container_save, 'POST', getTokenPraya());

            $response_disable_container_save = json_decode($response_disable_container_save['response'], true);

            $notes = $jenis_bm ? 'batal muat -> ' . $jenis_bm . ' -> ex repo' : $note;

            insertPrayaServiceLog($url_disable_container_save, $payload_disable_container_save, $response_disable_container_save, $notes);

            return $response_disable_container_save;
        } catch (Exception $ex) {
            echo $ex->getMessage();

            return array(
                'code' => "0",
                'msg' => $ex->getMessage()
            );
        }
    }
}

if (!function_exists('cancelInvoice')) {
    function cancelInvoice($requestId, $note = null)
    {
        try {

            $payload_cancel_invoice = array(
                "orgId" => ENV('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => ENV('PRAYA_ITPK_PNK_TERMINAL_ID'),
                "requestId" => $requestId,
            );

            $url_cancel_invoice = ENV('PRAYA_API_INTEGRATION') . "/api/usterDelete";
            $response_cancel_invoice = sendDataFromUrl($payload_cancel_invoice, $url_cancel_invoice, 'POST', getTokenPraya());

            $response_cancel_invoice = json_decode($response_cancel_invoice['response'], true);

            insertPrayaServiceLog($url_cancel_invoice, $payload_cancel_invoice, $response_cancel_invoice, $note);

            return $response_cancel_invoice;
        } catch (Exception $ex) {
            echo $ex->getMessage();

            return array(
                'code' => "0",
                'msg' => $ex->getMessage()
            );
        }
    }
}


if (!function_exists('sendDataFromUrlTryCatch')) {
    function sendDataFromUrlTryCatch($payload_request, $url, $method = "POST", $token = "")
    {
        set_time_limit(360);

        try {
            $curl = curl_init();

            /* set configure curl */
            $authorization = "Authorization: Bearer $token";
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL             => $url,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_ENCODING        => "",
                    CURLOPT_MAXREDIRS       => 10,
                    CURLOPT_CONNECTTIMEOUT  => 0,
                    CURLOPT_TIMEOUT         => 1000,
                    CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST   => $method,
                    CURLOPT_POSTFIELDS      => json_encode($payload_request),
                    CURLOPT_HTTPHEADER      => array(
                        "Content-Type: application/json",
                        $authorization
                    ),
                    // CURLOPT_SSL_VERIFYPEER => false // <- dihapus sebelum di push
                )
            );

            $response = curl_exec($curl);

            // echo json_encode($response);
            // echo "<<from integration";

            if ($response === false) {
                throw new Exception(curl_error($curl));
            }

            // Get HTTP status code
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            //Success
            if ($httpCode >= 200 && $httpCode < 300) {
                $response_curl = array(
                    'status'   => 'Success',
                    'httpCode' => $httpCode,
                    'response' => $response
                );
            } else if ($httpCode >= 100 && $httpCode < 200) {
                // Continue with request
                // $response_curl = array(
                //     'status'   => 'Continue -' . $statusMessage,
                //     'httpCode' => $httpCode,
                //     'response' => $response
                // );
                throw new Exception('HTTP Server Responding Too Long: ' . $httpCode);
            } else if ($httpCode >= 400 && $httpCode < 500) {
                //Client Error
                $response_curl = array(
                    'status'   => 'Error',
                    'httpCode' => $httpCode,
                    'response' => $response
                );
            } else {
                //Server Error
                throw new Exception('HTTP Server Error: ' . $httpCode);
            }

            /* execute curl */
            curl_close($curl);

            return $response_curl;
        } catch (Exception $e) {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response_curl = array(
                'status'   => 'error',
                'httpCode' => $httpCode,
                'response' => "cURL Error # " . $e->getMessage()
            );

            return $response_curl;
        }
    }
}
