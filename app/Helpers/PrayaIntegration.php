<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        Log::channel('praya')->info('Request to Praya', ['payload' => $payload_request, 'url' => $url, 'method' => $method]);
        $start = microtime(true);

        set_time_limit(0);
        putenv('http_proxy');
        putenv('https_proxy');

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
                CURLOPT_TIMEOUT         => 120,
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

        /* execute curl */
        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        $end = microtime(true);
        $info     = curl_getinfo($curl);

        Log::channel('praya')->info('Praya Response Info', [
            'time' => $end - $start,
            'curl_info' => $info,
            'response' => $response,
            'error' => curl_error($curl),
        ]);

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
        set_time_limit(0);
        putenv('http_proxy');
        putenv('https_proxy');

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
                    CURLOPT_CONNECTTIMEOUT  => 120,
                    CURLOPT_TIMEOUT         => 120,
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

            Log::channel('praya')->info('Request to ILCS', ['payload' => $payload_request, 'url' => $url, 'method' => $method]);
            $start = microtime(true);

            $response = curl_exec($curl);

            if ($response === false) {
                throw new Exception(curl_error($curl));
            }

            // Get HTTP status code
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $end = microtime(true);

            Log::channel('praya')->info('ILCS Response Info', [
                'time' => $end - $start,
                'curl_info' => $httpCode,
                'response' => $response,
                'error' => curl_error($curl),
            ]);

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

if (!function_exists('savePaymentExternal')) {
    function save_payment_uster($payload)
    {
        try {
            $payload_uster_save = $payload;
            $url_uster_save = env('PRAYA_API_INTEGRATION') . "/api/usterSave";

            $payloadBatalMuat = $payload_uster_save["PAYLOAD_BATAL_MUAT"] ?? null;
            $jenis = $payload_uster_save["JENIS"];
            $id_req = $payload_uster_save["ID_REQUEST"];
            $bankAccountNumber = $payload_uster_save["BANK_ACCOUNT_NUMBER"];
            $paymentCode = $payload_uster_save["PAYMENT_CODE"];
            $charge = empty($payloadBatalMuat) ? "Y" : "N"; //kalau payload batal muat ada berarti tdk bayar

            $del_no_request = empty($payloadBatalMuat) ? $id_req : $payloadBatalMuat['ex_noreq'];
            $containerListLog = array();
            $jenisBM = '';
            //INI UNTUK QUERY JENIS DELIVERY & BATAL MUAT TANPA KENA CHARGE
            //Kalau menggunakan NO_PROFORMA, ID_REQUEST jangan dikirim dan sebaliknya
            if (!empty(request()->input("NO_PROFORMA")) && $jenis == 'DELIVERY') {
                $payload_log = '';
                if (empty($payload_uster_save)) {
                    $payload_log = [
                        "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                        "JENIS" => request()->input('JENIS'),
                        "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                        "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                    ];
                } else {
                    $payload_log = $payload_uster_save;
                }
                $del_no_request = "";
                $proforma = request()->input("NO_PROFORMA");
                $fetch_id_req = DB::connection('uster')->table('NOTA_DELIVERY')
                    ->select('NO_REQUEST')
                    ->where('NO_NOTA_MTI', $proforma)
                    ->first();
                if (!empty($fetch_id_req) && isset($fetch_id_req->no_request)) {
                    $del_no_request = $fetch_id_req->no_request;
                    $id_req = $fetch_id_req->no_request;
                } else {
                    $notes = "Payment Cash - " . $jenis . " - NO PROFORMA DELIVERY BUKAN MILIK MTI";
                    $response_uster_save = [
                        'code' => "0",
                        'msg' => "No. Proforma Delivery bukan milik MTI"
                    ];
                    insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                    return response()->json($response_uster_save);
                }
            }

            $fetchDelivery = DB::connection('uster')
                ->table('REQUEST_DELIVERY as rd')
                ->leftJoin('V_PKK_CONT as vpc', 'rd.NO_BOOKING', '=', 'vpc.NO_BOOKING')
                ->join('NOTA_DELIVERY as nd', 'nd.NO_REQUEST', '=', 'rd.NO_REQUEST')
                ->join('V_MST_PBM as vmp', 'vmp.KD_PBM', '=', 'rd.KD_EMKL')
                ->select([
                    'rd.NO_REQUEST',
                    'rd.NO_BOOKING',
                    'rd.KD_EMKL',
                    'rd.O_VESSEL',
                    'rd.VOYAGE',
                    'rd.KD_PELABUHAN_ASAL',
                    'rd.KD_PELABUHAN_TUJUAN',
                    'rd.O_VOYIN',
                    'rd.O_VOYOUT',
                    'rd.DELIVERY_KE',
                    'rd.TGL_REQUEST',
                    'rd.DI',
                    'vpc.KD_KAPAL',
                    'vpc.NM_KAPAL',
                    'vpc.VOYAGE_IN',
                    'vpc.VOYAGE_OUT',
                    'vpc.PELABUHAN_TUJUAN',
                    'vpc.PELABUHAN_ASAL',
                    'vpc.NM_AGEN',
                    'vpc.KD_AGEN',
                    'nd.NO_NOTA',
                    'nd.NO_FAKTUR_MTI',
                    'nd.TAGIHAN',
                    'nd.PPN',
                    'nd.TOTAL_TAGIHAN',
                    'nd.EMKL',
                    'nd.ALAMAT',
                    'nd.NPWP',
                    DB::raw("TO_CHAR(nd.TGL_NOTA ,'YYYY-MM-DD HH24:MI:SS') as TGLNOTA"),
                    DB::raw("TO_CHAR(rd.TGL_REQUEST,'YYYY-MM-DD HH24:MI:SS') as TGLSTART"),
                    DB::raw("TO_CHAR(rd.TGL_REQUEST + INTERVAL '4' DAY,'YYYY-MM-DD HH24:MI:SS') as TGLEND"),
                    'vmp.NO_ACCOUNT_PBM as KD_PELANGGAN'
                ])
                ->where('rd.NO_REQUEST', $del_no_request)
                ->first();

            if ($jenis == 'STRIPPING' || $jenis == 'PERP_STRIP') { //DELIVERY KALO DARI SISI TPK
                //Kalau menggunakan NO_PROFORMA, ID_REQUEST jangan dikirim dan sebaliknya
                if (!empty(request()->input("NO_PROFORMA"))) {
                    $payload_log = '';
                    if (empty($payload_uster_save)) {
                        $payload_log = [
                            "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                            "JENIS" => request()->input('JENIS'),
                            "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                            "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                        ];
                    } else {
                        $payload_log = $payload_uster_save;
                    }
                    $id_req = "";
                    $proforma = request()->input("NO_PROFORMA");
                    $fetch_id_req = DB::connection('uster')->table('NOTA_STRIPPING')
                        ->select('NO_REQUEST')
                        ->where('NO_NOTA_MTI', $proforma)
                        ->first();
                    if (!empty($fetch_id_req) && isset($fetch_id_req->no_request)) {
                        $id_req = $fetch_id_req->no_request;
                    } else {
                        $notes = "Payment Cash - " . $jenis . " - NO PROFORMA STRIPPING BUKAN MILIK MTI";
                        $response_uster_save = [
                            'code' => "0",
                            'msg' => "No. Proforma Stripping bukan milik MTI"
                        ];
                        insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                        return response()->json($response_uster_save);
                    }
                }

                $fetchStripping = DB::connection('uster')
                    ->table('REQUEST_STRIPPING as rs')
                    ->leftJoin('V_PKK_CONT as vpc', 'rs.NO_BOOKING', '=', 'vpc.NO_BOOKING')
                    ->join('NOTA_STRIPPING as ns', 'ns.NO_REQUEST', '=', 'rs.NO_REQUEST')
                    ->join('CONTAINER_STRIPPING as cs', 'rs.NO_REQUEST', '=', 'cs.NO_REQUEST')
                    ->join('V_MST_PBM as vmp', 'vmp.KD_PBM', '=', 'ns.KD_EMKL')
                    ->select([
                        'rs.NO_BOOKING',
                        'rs.NO_BL',
                        'rs.TYPE_STRIPPING',
                        'vpc.KD_KAPAL',
                        'vpc.NM_KAPAL',
                        'vpc.VOYAGE_IN',
                        'vpc.VOYAGE_OUT',
                        'vpc.VOYAGE',
                        'vpc.PELABUHAN_TUJUAN',
                        'vpc.PELABUHAN_ASAL',
                        'vpc.NM_AGEN',
                        'vpc.KD_AGEN',
                        'vpc.NO_UKK',
                        'ns.NO_NOTA',
                        'ns.NO_FAKTUR_MTI',
                        'ns.TAGIHAN',
                        'ns.PPN',
                        'ns.TOTAL_TAGIHAN',
                        'ns.EMKL',
                        'ns.ALAMAT',
                        'ns.NPWP',
                        DB::raw('vmp.NO_ACCOUNT_PBM as KD_PELANGGAN'),
                        DB::raw("TO_CHAR(rs.TGL_AWAL,'YYYY-MM-DD HH24:MI:SS') as TGLAWAL"),
                        DB::raw("TO_CHAR(rs.TGL_AKHIR,'YYYY-MM-DD HH24:MI:SS') as TGLAKHIR"),
                        DB::raw("TO_CHAR(cs.TGL_APPROVE, 'YYYY-MM-DD HH24:MI:SS') as TGLAPPROVE"),
                        DB::raw("TO_CHAR(cs.TGL_APP_SELESAI, 'YYYY-MM-DD HH24:MI:SS') as TGLAPPROVE_SELESAI"),
                        DB::raw("TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') as TGLNOTA"),
                        DB::raw("CASE WHEN rs.TGL_AWAL IS NULL OR rs.TGL_AKHIR IS NULL THEN 4 ELSE rs.TGL_AKHIR - rs.TGL_AWAL END as COUNT_DAYS"),
                    ])
                    ->where('rs.NO_REQUEST', $id_req)
                    ->first();

                // Ambil data container stripping
                $fetchContainerStripping = DB::connection('uster')
                    ->table('CONTAINER_STRIPPING as cs')
                    ->join('MASTER_CONTAINER as mc', 'cs.NO_CONTAINER', '=', 'mc.NO_CONTAINER')
                    ->select(
                        'cs.*',
                        'mc.*',
                        DB::raw("TO_CHAR(cs.TGL_SELESAI, 'YYYY-MM-DD HH24:MI:SS') as TGLSELESAI"),
                        DB::raw("TO_CHAR(cs.END_STACK_PNKN, 'YYYY-MM-DD HH24:MI:SS') as TGLSELESAI_PERP")
                    )
                    ->where('cs.NO_REQUEST', $id_req)
                    ->get()
                    ->toArray();

                // Ambil data nota stripping
                $fetchNotaStripping = DB::connection('uster')
                    ->table('NOTA_STRIPPING as ns')
                    ->join('NOTA_STRIPPING_D as nsd', 'nsd.NO_NOTA', '=', 'ns.NO_NOTA')
                    ->select(
                        'ns.*',
                        'nsd.*',
                        DB::raw("TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') as TGLNOTA"),
                        DB::raw("(SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = nsd.ID_ISO) as STATUS"),
                        DB::raw("TO_CHAR(nsd.START_STACK,'YYYY-MM-DD HH24:MI:SS') as AWAL_PENUMPUKAN"),
                        DB::raw("TO_CHAR(nsd.END_STACK,'YYYY-MM-DD HH24:MI:SS') as AKHIR_PENUMPUKAN")
                    )
                    ->where('ns.NO_REQUEST', $id_req)
                    ->get()
                    ->toArray();

                // Ambil data admin component
                $adminComponent = DB::connection('uster')
                    ->table('NOTA_STRIPPING as ns')
                    ->join('NOTA_STRIPPING_D as nsd', 'nsd.NO_NOTA', '=', 'ns.NO_NOTA')
                    ->select('nsd.TARIF')
                    ->where('ns.NO_REQUEST', $id_req)
                    ->where('nsd.ID_ISO', 'ADM')
                    ->first();

                $get_vessel = getVessel(
                    $fetchStripping->nm_kapal,
                    $fetchStripping->voyage,
                    $fetchStripping->voyage_in,
                    $fetchStripping->voyage_out
                );
                $get_container_list = getContainer(
                    null,
                    $fetchStripping->kd_kapal,
                    $fetchStripping->voyage_in,
                    $fetchStripping->voyage_out,
                    $fetchStripping->voyage,
                    "I",
                    "DEL"
                );
                $get_iso_code = getIsoCode();

                if (empty($get_iso_code)) {
                    $payload_log = [];
                    if (empty($payload_uster_save)) {
                        $payload_log = [
                            "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                            "JENIS" => request()->input('JENIS'),
                            "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                            "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                        ];
                    } else {
                        $payload_log = $payload_uster_save;
                    }
                    $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                    $response_uster_save = [
                        'code' => "0",
                        'msg' => "Gagal mengambil Iso Code ke Praya, silahkan ulangi Kembali hit process ini."
                    ];
                    insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                    return response()->json($response_uster_save);
                }

                $tgl_awal = $fetchStripping->tglawal;
                $tgl_akhir = $fetchStripping->tglakhir;
                if (empty($tgl_awal) || empty($tgl_akhir)) {
                    $tgl_awal = $fetchStripping->tglapprove;
                    $tgl_akhir = $fetchStripping->tglapprove_selesai;
                }

                $pelabuhan_asal = $fetchStripping->pelabuhan_asal;
                $pelabuhan_tujuan = $fetchStripping->pelabuhan_tujuan;

                $idRequest = $id_req;
                $trxNumber = $fetchStripping->no_nota;
                $paymentDate = $fetchStripping->tglnota;
                $invoiceNumber = $fetchStripping->no_faktur_mti;
                $requestType = 'STRIPPING';
                $parentRequestId = '';
                $parentRequestType = 'STRIPPING';
                $serviceCode = 'DEL';
                $vesselId = $fetchStripping->kd_kapal;
                $vesselName = $fetchStripping->nm_kapal;
                $voyage = empty($fetchStripping->voyage) ? '' : $fetchStripping->voyage;
                $voyageIn = empty($fetchStripping->voyage_in) ? '' : $fetchStripping->voyage_in;
                $voyageOut = empty($fetchStripping->voyage_out) ? '' : $fetchStripping->voyage_out;
                $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut;
                $eta = empty($get_vessel['eta']) ? '' : $get_vessel['eta'];
                $etb = empty($get_vessel['etb']) ? '' : $get_vessel['etb'];
                $etd = empty($get_vessel['etd']) ? '' : $get_vessel['etd'];
                $ata = empty($get_vessel['ata']) ? '' : $get_vessel['ata'];
                $atb = empty($get_vessel['atb']) ? '' : $get_vessel['atb'];
                $atd = empty($get_vessel['atd']) ? '' : $get_vessel['atd'];
                $startWork = empty($get_vessel['start_work']) ? '' : $get_vessel['start_work'];
                $endWork = empty($get_vessel['end_work']) ? '' : $get_vessel['end_work'];
                $pol = $pelabuhan_asal;
                $pod = $pelabuhan_tujuan;
                $dischargeDate = $get_vessel['discharge_date'];
                $shippingLineName = $fetchStripping->nm_agen;
                $customerCode = $fetchStripping->kd_pelanggan;
                $customerCodeOwner = '';
                $deliveryDate = '';
                $customerName = $fetchStripping->emkl;
                $customerAddress = $fetchStripping->alamat;
                $npwp = $fetchStripping->npwp;
                $blNumber = $fetchStripping->no_bl;
                $bookingNo = $fetchStripping->no_booking;
                $doNumber = $fetchStripping->no_booking;
                $tradeType = $fetchStripping->type_stripping == 'D' ? 'I' : 'O';
                $customsDocType = "";
                $customsDocNo = "";
                $customsDocDate = "";
                if ((int)$fetchStripping->total_tagihan > 5000000) {
                    $amount = (int)$fetchStripping->total_tagihan + 10000;
                } else {
                    $amount = (int)$fetchStripping->total_tagihan;
                }
                if ($adminComponent) {
                    $administration = $adminComponent->tarif;
                }
                if (empty($fetchStripping->ppn)) {
                    $ppn =  'N';
                } else {
                    $ppn = 'Y';
                }
                $amountPpn  = (int)$fetchStripping->ppn;
                $amountDpp = (int)$fetchStripping->tagihan;
                if ((int)$fetchStripping->tagihan > 5000000) {
                    $amountMaterai = 10000;
                } else {
                    $amountMaterai = 0;
                }
                $approvalDate = empty($fetchStripping->tglapprove) ? '' : $fetchStripping->tglapprove;
                $status = 'PAID';
                $changeDate = $fetchStripping->tglnota;
                $charge = 'Y';

                $detailList = array();
                $containerList = array();
                foreach ($fetchContainerStripping as $k => $v) {
                    $_get_container = null;
                    if (!empty($get_container_list) && is_array($get_container_list)) {
                        foreach ($get_container_list as $k_container => $v_container) {
                            if ($v_container['containerNo'] == $v->no_container) {
                                $_get_container = $v_container;
                                break;
                            }
                        }
                    }

                    $reslt = array();
                    foreach ($get_iso_code as $key => $value) {
                        if (strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_)) {
                            array_push($reslt, $value);
                        }
                    }
                    $array_iso_code = array_values($reslt);
                    $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"]);

                    // CHOSSY.P (26/12/2023)
                    // penambahan paythru utk perp strip
                    $paythru = $v->tglselesai;
                    if (substr($idRequest, 0, 3) == "STP") {
                        $paythru = $v->tglselesai_perp;

                        array_push($containerListLog, array(
                            "containerNo" => $v->no_container,
                            "containerDeliveryDate" => $paythru
                        ));
                    }
                    // END

                    array_push($containerList, $v->no_container);
                    array_push(
                        $detailList,
                        array(
                            "detailDescription" => "CONTAINER",
                            "containerNo" => $v->no_container,
                            "containerSize" => $v->size_,
                            "containerType" => $v->type_,
                            "containerStatus" => "FULL",
                            "containerHeight" => "8.5",
                            "hz" => empty($v->hz) ? (empty($_get_container['hz']) ? 'N' : $_get_container['hz']) : $v->hz,
                            "imo" => "N",
                            "unNumber" => empty($_get_container['unNumber']) ? '' : $_get_container['unNumber'],
                            "reeferNor" => "N",
                            "temperatur" => "",
                            "ow" => "",
                            "oh" => "",
                            "ol" => "",
                            "overLeft" => "",
                            "overRight" => "",
                            "overFront" => "",
                            "overBack" => "",
                            "weight" => "",
                            "commodityCode" => trim($v->commodity, " "),
                            "commodityName" => trim($v->commodity, " "),
                            "carrierCode" => $fetchStripping->kd_agen,
                            "carrierName" => $fetchStripping->nm_agen,
                            "isoCode" => $new_iso,
                            "plugInDate" => "",
                            "plugOutDate" => "",
                            "ei" => "I",
                            "dischLoad" => "",
                            "flagOog" => empty($_get_container['flagOog']) ? '' : $_get_container['flagOog'],
                            "gateInDate" => "",
                            "gateOutDate" => "",
                            "startDate" => $tgl_awal,
                            "endDate" => $tgl_akhir,
                            "containerDeliveryDate" => $paythru,
                            "containerLoadingDate" => "",
                            "containerDischargeDate" => $get_vessel['discharge_date'],
                            "disabled" => "Y"
                        )
                    );
                }

                $strContList = implode(", ", $containerList);
                $detailPranotaList = array();
                foreach ($fetchNotaStripping as $k => $v) {
                    $status = "";
                    if (!empty($v->status) && $v->status != "-") {
                        $status = $v->status == "FCL" ? "FULL" : "EMPTY";
                    }
                    array_push(
                        $detailPranotaList,
                        array(
                            "lineNumber" => $v->line_number,
                            "description" => $v->keterangan,
                            "flagTax" => "Y",
                            "componentCode" => $v->keterangan,
                            "componentName" => $v->keterangan,
                            "startDate" => $v->awal_penumpukan,
                            "endDate" => $v->akhir_penumpukan,
                            "quantity" => $v->jml_cont,
                            "tarif" => $v->tarif,
                            "basicTarif" => $v->tarif,
                            "containerList" => $strContList,
                            "containerSize" => $fetchContainerStripping[0]->size_,
                            "containerType" => $fetchContainerStripping[0]->type_,
                            "containerStatus" => $status,
                            "containerHeight" => "8.5",
                            "hz" => empty($v->hz) ? 'N' : $v->hz,
                            "ei" => "I",
                            "equipment" => "",
                            "strStartDate" => $v->awal_penumpukan,
                            "strEndDate" => $v->akhir_penumpukan,
                            "days" => $fetchStripping->count_days,
                            "amount" => $v->biaya,
                            "via" => "YARD",
                            "package" => "",
                            "unit" => "BOX",
                            "qtyLoading" => "",
                            "qtyDischarge" => "",
                            "equipmentName" => "",
                            "duration" => "",
                            "flagTool" => "N",
                            "itemCode" => "",
                            "oog" => "N",
                            "imo" => "",
                            "blNumber" => empty($fetchStripping->no_bl) ? '' : $fetchStripping->no_bl,
                            "od" => "N",
                            "dg" => "N",
                            "sling" => "N",
                            "changeDate" => $v->tglnota,
                            "changeBy" => "Admin Uster"
                        )
                    );
                }
            } elseif ($jenis == 'STUFFING' /* || $jenis == 'PERP_PNK' */) { //RECEIVING
                //Kalau menggunakan NO_PROFORMA, ID_REQUEST jangan dikirim dan sebaliknya
                if (!empty(request()->input("NO_PROFORMA"))) {
                    $payload_log = '';
                    if (empty($payload_uster_save)) {
                        $payload_log = [
                            "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                            "JENIS" => request()->input('JENIS'),
                            "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                            "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                        ];
                    } else {
                        $payload_log = $payload_uster_save;
                    }
                    $id_req = "";
                    $proforma = request()->input("NO_PROFORMA");
                    $fetch_id_req = DB::connection('uster')->table('NOTA_STUFFING')
                        ->select('NO_REQUEST')
                        ->where('NO_NOTA_MTI', $proforma)
                        ->first();
                    if (!empty($fetch_id_req) && isset($fetch_id_req->no_request)) {
                        $id_req = $fetch_id_req->no_request;
                    } else {
                        $notes = "Payment Cash - " . $jenis . " - NO PROFORMA STUFFING BUKAN MILIK MTI";
                        $response_uster_save = [
                            'code' => "0",
                            'msg' => "No. Proforma Stuffing bukan milik MTI"
                        ];
                        insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                        return response()->json($response_uster_save);
                    }
                }

                $fetchStuffing = DB::connection('uster')
                    ->table('REQUEST_STUFFING as rs')
                    ->leftJoin('V_PKK_CONT as vpc', 'rs.NO_BOOKING', '=', 'vpc.NO_BOOKING')
                    ->join('NOTA_STUFFING as ns', 'ns.NO_REQUEST', '=', 'rs.NO_REQUEST')
                    ->join('V_MST_PBM as vmp', 'vmp.KD_PBM', '=', 'ns.KD_EMKL')
                    ->join('CONTAINER_STUFFING as cs', 'cs.NO_REQUEST', '=', 'rs.NO_REQUEST')
                    ->join('PLAN_REQUEST_STUFFING as prs', 'prs.NO_REQUEST_APP', '=', 'rs.NO_REQUEST')
                    ->join('PLAN_CONTAINER_STUFFING as pcs', 'pcs.NO_REQUEST', '=', 'prs.NO_REQUEST')
                    ->select([
                        'rs.NO_BOOKING',
                        'rs.NO_BL',
                        'rs.NO_NPE',
                        'rs.DI',
                        'rs.STUFFING_DARI',
                        'vpc.KD_KAPAL',
                        'vpc.NM_KAPAL',
                        'vpc.VOYAGE_IN',
                        'vpc.VOYAGE_OUT',
                        'vpc.VOYAGE',
                        'vpc.PELABUHAN_TUJUAN',
                        'vpc.PELABUHAN_ASAL',
                        'vpc.NM_AGEN',
                        'vpc.KD_AGEN',
                        'vpc.NO_UKK',
                        'ns.NO_NOTA',
                        'ns.NO_FAKTUR_MTI',
                        'ns.TAGIHAN',
                        'ns.PPN',
                        'ns.TOTAL_TAGIHAN',
                        'ns.EMKL',
                        'ns.ALAMAT',
                        'ns.NPWP',
                        DB::raw('vmp.NO_ACCOUNT_PBM as KD_PELANGGAN'),
                        'cs.ASAL_CONT',
                        DB::raw("TO_CHAR(pcs.TGL_APPROVE,'YYYY-MM-DD HH24:MI:SS') as TGLAPPROVE"),
                        DB::raw("TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') as TGLNOTA"),
                        DB::raw("TO_CHAR(rs.TGL_REQUEST,'YYYY-MM-DD HH24:MI:SS') as TGLSTART"),
                        DB::raw("TO_CHAR(rs.TGL_REQUEST + INTERVAL '4' DAY,'YYYY-MM-DD HH24:MI:SS') as TGLEND"),
                    ])
                    ->where('rs.NO_REQUEST', $id_req)
                    ->first();

                if (!empty($fetchStuffing) && isset($fetchStuffing->stuffing_dari) && $fetchStuffing->stuffing_dari == 'TPK') {
                    // Ambil data container stuffing
                    $fetchContainerStuffing = DB::connection('uster')
                        ->table('CONTAINER_STUFFING as cs')
                        ->join('MASTER_CONTAINER as mc', 'cs.NO_CONTAINER', '=', 'mc.NO_CONTAINER')
                        ->select(
                            'cs.*',
                            'mc.*',
                            DB::raw("TO_CHAR(cs.START_PERP_PNKN,'YYYY-MM-DD HH24:MI:SS') as TGLPAYTHRU")
                        )
                        ->where('cs.NO_REQUEST', $id_req)
                        ->get()
                        ->toArray();

                    // Ambil data nota stuffing
                    $fetchNotaStuffing = DB::connection('uster')
                        ->table('NOTA_STUFFING as ns')
                        ->join('NOTA_STUFFING_D as nsd', 'nsd.NO_NOTA', '=', 'ns.NO_NOTA')
                        ->select(
                            'ns.*',
                            'nsd.*',
                            DB::raw("TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') as TGLNOTA"),
                            DB::raw("(SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = nsd.ID_ISO) as STATUS"),
                            DB::raw("TO_CHAR(nsd.START_STACK,'YYYY-MM-DD HH24:MI:SS') as AWAL_PENUMPUKAN"),
                            DB::raw("TO_CHAR(nsd.END_STACK,'YYYY-MM-DD HH24:MI:SS') as AKHIR_PENUMPUKAN")
                        )
                        ->where('ns.NO_REQUEST', $id_req)
                        ->get()
                        ->toArray();

                    // Ambil data admin component
                    $adminComponent = DB::connection('uster')
                        ->table('NOTA_STUFFING as ns')
                        ->join('NOTA_STUFFING_D as nsd', 'nsd.NO_NOTA', '=', 'ns.NO_NOTA')
                        ->select('nsd.TARIF')
                        ->where('ns.NO_REQUEST', $id_req)
                        ->where('nsd.ID_ISO', 'ADM')
                        ->first();

                    $get_iso_code = getIsoCode();
                    if (empty($get_iso_code)) {
                        $payload_log = [];
                        if (empty($payload_uster_save)) {
                            $payload_log = [
                                "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                                "JENIS" => request()->input('JENIS'),
                                "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                                "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                            ];
                        } else {
                            $payload_log = $payload_uster_save;
                        }
                        $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                        $response_uster_save = [
                            'code' => "0",
                            'msg' => "Gagal mengambil Iso Code ke Praya, silahkan ulangi Kembali hit process ini."
                        ];
                        insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                        return response()->json($response_uster_save);
                    }

                    // echo json_encode($get_vessel) . '<<ves';
                    // echo json_encode($get_container_list) . '<<cont';

                    $tgl_awal = $fetchStuffing->tglstart;
                    $tgl_akhir = $fetchStuffing->tglend;

                    $pelabuhan_asal = $fetchStuffing->pelabuhan_asal;
                    $pelabuhan_tujuan = $fetchStuffing->pelabuhan_tujuan;

                    $idRequest = $id_req;
                    $trxNumber = $fetchStuffing->no_nota;
                    $paymentDate = $fetchStuffing->tglnota;
                    $invoiceNumber = $fetchStuffing->no_faktur_mti;
                    $requestType = 'STUFFING';
                    $parentRequestId = '';
                    $parentRequestType = 'STUFFING';
                    $serviceCode = 'DEL';
                    $vesselId = "";
                    $vesselName = "";
                    $voyage = "";
                    $voyageIn = "";
                    $voyageOut = "";
                    $voyageInOut = "";
                    $eta = "";
                    $etb = "";
                    $etd = "";
                    $ata = "";
                    $atb = "";
                    $atd = "";
                    $startWork = "";
                    $endWork = "";
                    $pol = $pelabuhan_asal;
                    $pod = $pelabuhan_tujuan;
                    $dischargeDate = $get_vessel['discharge_date'] ?? null;
                    $shippingLineName = $fetchStuffing->nm_agen;
                    $customerCode = $fetchStuffing->kd_pelanggan;
                    $customerCodeOwner = '';
                    $customerName = $fetchStuffing->emkl;
                    $customerAddress = $fetchStuffing->alamat;
                    $npwp = $fetchStuffing->npwp;
                    $blNumber = empty($fetchStuffing->no_bl) ? "" : $fetchStuffing->no_bl;
                    $bookingNo = $fetchStuffing->no_booking;
                    $deliveryDate = $fetchStuffing->tglapprove; //paythrudate
                    $doNumber = $fetchStuffing->no_booking;
                    // $doDate = '';
                    $tradeType = $fetchStuffing->di == 'D' ? 'I' : 'O';
                    $customsDocType = $fetchStuffing->di == 'D' ? "NPE" : "";
                    $customsDocNo = $fetchStuffing->di == 'D' ? (empty($fetchStuffing->no_npe) ? "" : $fetchStuffing->no_npe) : "";
                    $customsDocDate = "";
                    if ((int)$fetchStuffing->total_tagihan > 5000000) {
                        $amount = (int)$fetchStuffing->total_tagihan + 10000;
                    } else {
                        $amount = (int)$fetchStuffing->total_tagihan;
                    }
                    if ($adminComponent) {
                        $administration = $adminComponent->tarif;
                    }
                    if (empty($fetchStuffing->ppn)) {
                        $ppn =  'N';
                    } else {
                        $ppn = 'Y';
                    }
                    $amountPpn  = (int)$fetchStuffing->ppn;
                    $amountDpp = (int)$fetchStuffing->tagihan;
                    if ($fetchStuffing->tagihan > 5000000) {
                        $amountMaterai = 10000;
                    } else {
                        $amountMaterai = 0;
                    }
                    $approvalDate = empty($fetchStuffing->tglapprove) ? '' : $fetchStuffing->tglapprove;
                    $status = 'PAID';
                    $changeDate = $fetchStuffing->tglnota;
                    $charge = 'Y';

                    $detailList = [];
                    $containerList = [];
                    foreach ($fetchContainerStuffing as $k => $v) {
                        $reslt = [];
                        foreach ($get_iso_code as $key => $value) {
                            if (
                                strtolower($value['type']) == strtolower($v->type_) &&
                                strtolower($value['size']) == strtolower($v->size_)
                            ) {
                                $reslt[] = $value;
                            }
                        }

                        $array_iso_code = array_values($reslt);
                        $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"] ?? '');

                        $containerList[] = $v->no_container;
                        $detailList[] = [
                            "detailDescription" => "CONTAINER",
                            "containerNo" => $v->no_container,
                            "containerSize" => $v->size_,
                            "containerType" => $v->type_,
                            "containerStatus" => "FULL",
                            "containerHeight" => "8.5",
                            "hz" => empty($v->hz) ? 'N' : $v->hz,
                            "imo" => "N",
                            "unNumber" => "",
                            "reeferNor" => "N",
                            "temperatur" => "",
                            "ow" => "",
                            "oh" => "",
                            "ol" => "",
                            "overLeft" => "",
                            "overRight" => "",
                            "overFront" => "",
                            "overBack" => "",
                            "weight" => $v->berat,
                            "commodityCode" => trim($v->commodity ?? ""),
                            "commodityName" => trim($v->commodity ?? ""),
                            "carrierCode" => $fetchStuffing->kd_agen ?? "",
                            "carrierName" => $fetchStuffing->nm_agen ?? "",
                            "isoCode" => $new_iso,
                            "plugInDate" => "",
                            "plugOutDate" => "",
                            "ei" => "I",
                            "dischLoad" => "",
                            "flagOog" => "",
                            "gateInDate" => "",
                            "gateOutDate" => "",
                            "startDate" => $fetchStuffing->tglstart ?? "",
                            "endDate" => $fetchStuffing->tglend ?? "",
                            "containerDeliveryDate" => $v->tglpaythru ?? "",
                            "containerLoadingDate" => "",
                            "containerDischargeDate" => "",
                            "disabled" => "Y"
                        ];
                    }

                    $strContList = implode(", ", $containerList);
                    $detailPranotaList = array();
                    foreach ($fetchNotaStuffing as $k => $v) {
                        $status = "";
                        if (!empty($v->status) && $v->status != "-") {
                            $status = $v->status == "FCL" ? "FULL" : "EMPTY";
                        }
                        $detailPranotaList[] = [
                            "lineNumber" => $v->line_number,
                            "description" => $v->keterangan,
                            "flagTax" => "Y",
                            "componentCode" => $v->keterangan,
                            "componentName" => $v->keterangan,
                            "startDate" => $v->awal_penumpukan,
                            "endDate" => $v->akhir_penumpukan,
                            "quantity" => $v->jml_cont,
                            "tarif" => $v->tarif,
                            "basicTarif" => $v->tarif,
                            "containerList" => $strContList,
                            "containerSize" => $fetchContainerStuffing[0]->size_,
                            "containerType" => $fetchContainerStuffing[0]->type_,
                            "containerStatus" => $status,
                            "containerHeight" => "8.5",
                            "hz" => empty($v->hz) ? 'N' : $v->hz,
                            "ei" => "I",
                            "equipment" => "",
                            "strStartDate" => $v->awal_penumpukan,
                            "strEndDate" => $v->akhir_penumpukan,
                            "days" => "4", //TGL_END - TGL_START INTERVAL 4 HARI
                            "amount" => $v->biaya,
                            "via" => "YARD",
                            "package" => "",
                            "unit" => "BOX",
                            "qtyLoading" => "",
                            "qtyDischarge" => "",
                            "equipmentName" => "",
                            "duration" => "",
                            "flagTool" => "N",
                            "itemCode" => "",
                            "oog" => "N",
                            "imo" => "",
                            "blNumber" => empty($fetchStuffing->no_bl) ? '' : $fetchStuffing->no_bl,
                            "od" => "N",
                            "dg" => "N",
                            "sling" => "N",
                            "changeDate" => $v->tglnota,
                            "changeBy" => "Admin Uster"
                        ];
                    }
                } else {
                    $payload_log = [];
                    if (empty($payload_uster_save)) {
                        $payload_log = [
                            "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                            "JENIS" => request()->input('JENIS'),
                            "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                            "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                        ];
                    } else {
                        $payload_log = $payload_uster_save;
                    }
                    $notes = "Payment Cash - " . $jenis . " - STUFFING BUKAN DARI TPK";
                    $response_uster_save = [
                        'code' => "0",
                        'msg' => "Asal Stuffing Bukan Dari TPK"
                    ];
                    insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                    return response()->json($response_uster_save);
                }
            } elseif ($jenis == 'DELIVERY') {
                //IF DELIVERY KE TPK
                if (!empty($fetchDelivery) && isset($fetchDelivery->delivery_ke) && $fetchDelivery->delivery_ke == 'TPK') {
                    // Ambil data container delivery
                    $fetchContainerDelivery = DB::connection('uster')
                        ->table('CONTAINER_DELIVERY as cd')
                        ->join('MASTER_CONTAINER as mc', 'cd.NO_CONTAINER', '=', 'mc.NO_CONTAINER')
                        ->select(
                            'cd.*',
                            'mc.*',
                            DB::raw("TO_CHAR(cd.START_STACK,'YYYY-MM-DD HH24:MI:SS') as AWAL_PENUMPUKAN"),
                            DB::raw("TO_CHAR(cd.TGL_DELIVERY,'YYYY-MM-DD HH24:MI:SS') as AKHIR_PENUMPUKAN")
                        )
                        ->where('cd.NO_REQUEST', $id_req)
                        ->get()
                        ->toArray();

                    // Ambil data nota delivery
                    $fetchNotaDelivery = DB::connection('uster')
                        ->table('NOTA_DELIVERY as nd')
                        ->join('NOTA_DELIVERY_D as ndd', 'ndd.ID_NOTA', '=', 'nd.NO_NOTA')
                        ->select(
                            'nd.*',
                            'ndd.*',
                            DB::raw("(SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) as STATUS"),
                            DB::raw("(SELECT SIZE_ FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) as SIZE_"),
                            DB::raw("(SELECT TYPE_ FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) as TYPE_"),
                            DB::raw("TO_CHAR(ndd.START_STACK,'YYYY-MM-DD HH24:MI:SS') as AWAL_PENUMPUKAN"),
                            DB::raw("TO_CHAR(ndd.END_STACK,'YYYY-MM-DD HH24:MI:SS') as AKHIR_PENUMPUKAN")
                        )
                        ->where('nd.NO_REQUEST', $id_req)
                        ->get()
                        ->toArray();

                    // Ambil data admin component
                    $adminComponent = DB::connection('uster')
                        ->table('NOTA_DELIVERY as nd')
                        ->join('NOTA_DELIVERY_D as ndd', 'ndd.ID_NOTA', '=', 'nd.NO_NOTA')
                        ->select('ndd.TARIF')
                        ->where('nd.NO_REQUEST', $id_req)
                        ->where('ndd.ID_ISO', 'ADM')
                        ->first();

                    $get_vessel = getVessel($fetchDelivery->nm_kapal, $fetchDelivery->voyage, $fetchDelivery->voyage_in, $fetchDelivery->voyage_out);
                    // $get_container_list = getContainer(NULL, $fetchDelivery['KD_KAPAL'], $fetchDelivery['VOYAGE_IN'], $fetchDelivery['VOYAGE_OUT'], $fetchDelivery['VOYAGE']);
                    $get_iso_code = getIsoCode();

                    if (empty($get_iso_code)) {
                        $payload_log = [];
                        if (empty($payload_uster_save)) {
                            $payload_log = [
                                "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                                "JENIS" => request()->input('JENIS'),
                                "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                                "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                            ];
                        } else {
                            $payload_log = $payload_uster_save;
                        }
                        $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                        $response_uster_save = [
                            'code' => "0",
                            'msg' => "Gagal mengambil Iso Code ke Praya, silahkan ulangi Kembali hit process ini."
                        ];
                        insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                        return response()->json($response_uster_save);
                    }

                    $pelabuhan_asal = $fetchDelivery->kd_pelabuhan_asal;
                    $pelabuhan_tujuan = $fetchDelivery->kd_pelabuhan_tujuan;

                    $idRequest = $id_req;
                    $trxNumber = $fetchDelivery->no_nota;
                    $paymentDate = $fetchDelivery->tglnota;
                    $invoiceNumber = $fetchDelivery->no_faktur_mti;
                    $requestType = 'RECEIVING';
                    $parentRequestId = $id_req;
                    $parentRequestType = 'RECEIVING';
                    $serviceCode = 'REC';
                    $vesselId = $fetchDelivery->kd_kapal;
                    $vesselName = $fetchDelivery->nm_kapal;
                    $voyage = empty($fetchDelivery->voyage) ? '' : $fetchDelivery->voyage;
                    $voyageIn = empty($fetchDelivery->voyage_in) ? '' : $fetchDelivery->voyage_in;
                    $voyageOut = empty($fetchDelivery->voyage_out) ? '' : $fetchDelivery->voyage_out;
                    $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut;
                    $eta = empty($get_vessel['eta']) ? '' : $get_vessel['eta'];
                    $etb = empty($get_vessel['etb']) ? '' : $get_vessel['etb'];
                    $etd = empty($get_vessel['etd']) ? '' : $get_vessel['etd'];
                    $ata = empty($get_vessel['ata']) ? '' : $get_vessel['ata'];
                    $atb = empty($get_vessel['atb']) ? '' : $get_vessel['atb'];
                    $atd = empty($get_vessel['atd']) ? '' : $get_vessel['atd'];
                    $startWork = empty($get_vessel['start_work']) ? '' : $get_vessel['start_work'];
                    $endWork = empty($get_vessel['end_work']) ? '' : $get_vessel['end_work'];
                    $pol = $pelabuhan_asal;
                    $pod = $pelabuhan_tujuan;
                    $fpod = $pelabuhan_tujuan;
                    $dischargeDate = $get_vessel['discharge_date'] ?? null;
                    $shippingLineName = $fetchDelivery->nm_agen;
                    $customerCode = $fetchDelivery->kd_pelanggan;
                    $customerCodeOwner = '';
                    $customerName = $fetchDelivery->emkl;
                    $customerAddress = $fetchDelivery->alamat;
                    $npwp = $fetchDelivery->npwp;
                    $blNumber = '';
                    $bookingNo = $fetchDelivery->no_booking;
                    $deliveryDate = '';
                    $doNumber = $fetchDelivery->no_booking;
                    // $doDate = '';
                    $tradeType = "I";
                    $customsDocType = "";
                    $customsDocNo = "";
                    $customsDocDate = "";
                    if ((int)$fetchDelivery->total_tagihan > 5000000) {
                        $amount = (int)$fetchDelivery->total_tagihan + 10000;
                    } else {
                        $amount = (int)$fetchDelivery->total_tagihan;
                    }
                    if ($adminComponent) {
                        $administration = $adminComponent->tarif;
                    }
                    if (empty($fetchDelivery->ppn)) {
                        $ppn = 'N';
                    } else {
                        $ppn = 'Y';
                    }
                    $amountPpn = (int)$fetchDelivery->ppn;
                    $amountDpp = (int)$fetchDelivery->tagihan;
                    if ($fetchDelivery->tagihan > 5000000) {
                        $amountMaterai = 10000;
                    } else {
                        $amountMaterai = 0;
                    }
                    $approvalDate = empty($fetchDelivery->tglapprove) ? '' : $fetchDelivery->tglapprove;
                    $status = 'PAID';
                    $changeDate = $fetchDelivery->tglnota;
                    $charge = 'Y';

                    $detailList = [];
                    $containerList = [];
                    foreach ($fetchContainerDelivery as $k => $v) {
                        $container_status = $v->status == 'FCL' ? 'FULL' : 'EMPTY';
                        $cont = $v->no_container;
                        $reslt = [];
                        foreach ($get_iso_code as $key => $value) {
                            if (
                                strtolower($value['type']) == strtolower($v->type_) &&
                                strtolower($value['size']) == strtolower($v->size_)
                            ) {
                                $reslt[] = $value;
                            }
                        }

                        $array_iso_code = array_values($reslt);
                        $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"] ?? '');

                        $containerList[] = $v->no_container;
                        $detailList[] = [
                            "detailDescription" => "CONTAINER",
                            "containerNo" => $v->no_container,
                            "containerSize" => $v->size_,
                            "containerType" => $v->type_,
                            "containerStatus" => $container_status,
                            "containerHeight" => "8.5",
                            "hz" => empty($v->hz) ? 'N' : $v->hz,
                            "imo" => "N",
                            "unNumber" => "",
                            "reeferNor" => "N",
                            "temperatur" => "",
                            "ow" => "",
                            "oh" => "",
                            "ol" => "",
                            "overLeft" => "",
                            "overRight" => "",
                            "overFront" => "",
                            "overBack" => "",
                            "weight" => $v->berat,
                            "commodityCode" => trim($v->komoditi ?? ""),
                            "commodityName" => trim($v->komoditi ?? ""),
                            "carrierCode" => $fetchDelivery->kd_agen ?? "",
                            "carrierName" => $fetchDelivery->nm_agen ?? "",
                            "isoCode" => $new_iso,
                            "plugInDate" => "",
                            "plugOutDate" => "",
                            "ei" => "E",
                            "dischLoad" => "",
                            "flagOog" => "N",
                            "gateInDate" => $v->awal_penumpukan ?? "",
                            "gateOutDate" => $v->akhir_penumpukan ?? "",
                            "startDate" => $v->awal_penumpukan ?? "",
                            "endDate" => $v->akhir_penumpukan ?? "",
                            "containerDeliveryDate" => $v->akhir_penumpukan ?? "",
                            "containerLoadingDate" => "",
                            "containerDischargeDate" => "",
                        ];
                    }

                    $strContList = implode(", ", $containerList);
                    $detailPranotaList = array();
                    foreach ($fetchNotaDelivery as $k => $v) {
                        if ($v->keterangan == "MATERAI") {
                            continue;
                        }
                        if ($v->awal_penumpukan) {
                            $newContainerList = [];
                            foreach ($fetchContainerDelivery as $vContainer) {
                                if (
                                    $vContainer->awal_penumpukan == $v->awal_penumpukan &&
                                    $vContainer->akhir_penumpukan == $v->akhir_penumpukan &&
                                    $vContainer->status == $v->status &&
                                    $vContainer->size_ == $v->size_ &&
                                    $vContainer->type_ == $v->type_
                                ) {
                                    $newContainerList[] = $vContainer->no_container;
                                }
                            }
                            $newStrContList = implode(", ", $newContainerList);
                        } else {
                            $newStrContList = $strContList;
                        }
                        $status = "";
                        if (!empty($v->status) && $v->status != "-") {
                            $status = $v->status == "FCL" ? "FULL" : "EMPTY";
                        }
                        $type = $v->type_ == "-" ? "" : $v->type_;
                        $size = $v->size_ == "-" ? "" : $v->size_;
                        $detailPranotaList[] = [
                            "lineNumber" => $v->line_number,
                            "description" => $v->keterangan,
                            "flagTax" => "Y",
                            "componentCode" => $v->keterangan,
                            "componentName" => $v->keterangan,
                            "startDate" => $v->awal_penumpukan,
                            "endDate" => $v->akhir_penumpukan,
                            "quantity" => $v->jml_cont,
                            "tarif" => $v->tarif,
                            "basicTarif" => $v->tarif,
                            "containerList" => $newStrContList,
                            "containerSize" => $fetchContainerDelivery[0]->size_,
                            "containerType" => $fetchContainerDelivery[0]->type_,
                            "containerStatus" => $status,
                            "containerHeight" => "8.5",
                            "hz" => $v->hz,
                            "ei" => "I",
                            "equipment" => "",
                            "strStartDate" => $v->awal_penumpukan,
                            "strEndDate" => $v->akhir_penumpukan,
                            "days" => "4", //TGL_END - TGL_START INTERVAL 4 HARI
                            "amount" => $v->biaya,
                            "via" => "YARD",
                            "package" => "",
                            "unit" => "BOX",
                            "qtyLoading" => "",
                            "qtyDischarge" => "",
                            "equipmentName" => "",
                            "duration" => "",
                            "flagTool" => "N",
                            "itemCode" => "",
                            "oog" => "N",
                            "imo" => "",
                            "blNumber" => "",
                            "od" => "N",
                            "dg" => "N",
                            "sling" => "N",
                            "changeDate" => $fetchDelivery->tglnota,
                            "changeBy" => "Admin Uster"
                        ];
                    }
                } else {
                    $payload_log = [];
                    if (empty($payload_uster_save)) {
                        $payload_log = [
                            "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                            "JENIS" => request()->input('JENIS'),
                            "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                            "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                        ];
                    } else {
                        $payload_log = $payload_uster_save;
                    }
                    $notes = "Payment Cash - " . $jenis . " - DELIVERY BUKAN KE TPK";
                    $response_uster_save = [
                        'code' => "0",
                        'msg' => "Tujuan Delivery bukan menuju TPK"
                    ];
                    insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                    return response()->json($response_uster_save);
                }
            } elseif ($jenis == 'BATAL_MUAT') {
                //Kalau menggunakan NO_PROFORMA, ID_REQUEST jangan dikirim dan sebaliknya
                if (!empty(request()->input("NO_PROFORMA"))) {
                    $payload_log = '';
                    if (empty($payload_uster_save)) {
                        $payload_log = [
                            "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                            "JENIS" => request()->input('JENIS'),
                            "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                            "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                        ];
                    } else {
                        $payload_log = $payload_uster_save;
                    }
                    $id_req = "";
                    $proforma = request()->input("NO_PROFORMA");
                    $fetch_id_req = DB::connection('uster')->table('NOTA_BATAL_MUAT')
                        ->select('NO_REQUEST')
                        ->where('NO_NOTA_MTI', $proforma)
                        ->first();
                    if (!empty($fetch_id_req) && isset($fetch_id_req->no_request)) {
                        $id_req = $fetch_id_req->no_request;
                    } else {
                        $notes = "Payment Cash - " . $jenis . " - NO PROFORMA BATAL MUAT BUKAN MILIK MTI";
                        $response_uster_save = [
                            'code' => "0",
                            'msg' => "No. Proforma Batal Muat bukan milik MTI"
                        ];
                        insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                        return response()->json($response_uster_save);
                    }
                }

                if ($charge == "N") {
                    $fetchExDelivery = $fetchDelivery;
                    if ($fetchExDelivery->delivery_ke == 'TPK') {
                        $get_vessel = getVessel(
                            $payloadBatalMuat['vesselName'],
                            $payloadBatalMuat['voyage'],
                            $payloadBatalMuat['voyageIn'],
                            $payloadBatalMuat['voyageOut']
                        );
                        $get_iso_code = getIsoCode();

                        if (empty($get_iso_code)) {
                            $payload_log = [];
                            if (empty($payload_uster_save)) {
                                $payload_log = [
                                    "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                                    "JENIS" => request()->input('JENIS'),
                                    "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                                    "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                                ];
                            } else {
                                $payload_log = $payload_uster_save;
                            }
                            $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                            $response_uster_save = [
                                'code' => "0",
                                'msg' => "Gagal mengambil Iso Code ke Praya, silahkan ulangi Kembali hit process ini."
                            ];
                            insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                            return response()->json($response_uster_save);
                        }

                        $pelabuhan_asal = $payloadBatalMuat['pelabuhan_asal'];
                        $pelabuhan_tujuan = $payloadBatalMuat['pelabuhan_tujuan'];

                        $idRequest = $id_req;
                        $trxNumber = "";
                        $paymentDate = "";
                        $invoiceNumber = "";
                        $requestType = 'LOADING CANCEL - BEFORE GATEIN';
                        $parentRequestId = "";
                        $parentRequestType = 'LOADING CANCEL - BEFORE GATEIN';
                        $serviceCode = 'LCB';
                        $jenisBM = "alih_kapal";
                        $vesselId = $payloadBatalMuat["vesselId"];
                        $vesselName = $payloadBatalMuat["vesselName"];
                        $voyage = $payloadBatalMuat['voyage'];
                        $voyageIn = $payloadBatalMuat['voyageIn'];
                        $voyageOut = $payloadBatalMuat['voyageOut'];
                        $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut;
                        $eta = empty($get_vessel['eta']) ? '' : $get_vessel['eta'];
                        $etb = empty($get_vessel['etb']) ? '' : $get_vessel['etb'];
                        $etd = empty($get_vessel['etd']) ? '' : $get_vessel['etd'];
                        $ata = empty($get_vessel['ata']) ? '' : $get_vessel['ata'];
                        $atb = empty($get_vessel['atb']) ? '' : $get_vessel['atb'];
                        $atd = empty($get_vessel['atd']) ? '' : $get_vessel['atd'];
                        $startWork = empty($get_vessel['start_work']) ? '' : $get_vessel['start_work'];
                        $endWork = empty($get_vessel['end_work']) ? '' : $get_vessel['end_work'];
                        $pol = $pelabuhan_asal;
                        $pod = $pelabuhan_tujuan;
                        $dischargeDate = $get_vessel['discharge_date'] ?? null;
                        $shippingLineName = $payloadBatalMuat['nm_agen'];
                        $customerCode = $fetchExDelivery->kd_pelanggan;
                        $customerCodeOwner = '';
                        $customerName = $fetchExDelivery->emkl;
                        $customerAddress = $fetchExDelivery->alamat;
                        $npwp = $fetchExDelivery->npwp;
                        $blNumber = "";
                        $bookingNo = $fetchExDelivery->no_booking;
                        $deliveryDate = '';
                        $doNumber = "";
                        $tradeType = $fetchExDelivery->di;
                        $customsDocType = "";
                        $customsDocNo = "";
                        $customsDocDate = "";
                        $amount = 0;
                        $administration = 0;
                        $ppn = empty($fetchExDelivery->ppn) ? 'N' : 'Y';
                        $amountPpn = 0;
                        $amountDpp = 0;
                        $amountMaterai = 0;
                        $approvalDate = empty($fetchExDelivery->tglapprove) ? '' : $fetchExDelivery->tglapprove;
                        $status = 'PAID';
                        $changeDate = $fetchExDelivery->tglnota;
                        $charge = 'N';

                        $detailList = [];
                        $containerList = $payloadBatalMuat['cont_list'];
                        foreach ($payloadBatalMuat['cont_list'] as $no_cont) {
                            $fetchContainerExDelivery = DB::connection('uster')
                                ->table('container_delivery as cd')
                                ->join('master_container as mc', 'cd.no_container', '=', 'mc.no_container')
                                ->join('v_pkk_cont as vpc', 'mc.no_booking', '=', 'vpc.no_booking')
                                ->select(
                                    'cd.no_container',
                                    'cd.komoditi',
                                    'mc.size_',
                                    'mc.type_',
                                    'mc.no_booking',
                                    'vpc.kd_kapal',
                                    'vpc.voyage',
                                    'vpc.voyage_in',
                                    'vpc.voyage_out'
                                )
                                ->where('cd.no_container', $no_cont)
                                ->first();

                            $get_container_list = getContainer(
                                $no_cont,
                                $fetchContainerExDelivery->kd_kapal,
                                $fetchContainerExDelivery->voyage_in,
                                $fetchContainerExDelivery->voyage_out,
                                $fetchContainerExDelivery->voyage,
                                null,
                                null
                            );

                            $reslt = [];
                            foreach ($get_iso_code as $value) {
                                if (
                                    strtoupper($value['type']) == strtoupper($fetchContainerExDelivery->type_) &&
                                    strtoupper($value['size']) == strtoupper($fetchContainerExDelivery->size_)
                                ) {
                                    $reslt[] = $value;
                                }
                            }

                            $array_iso_code = array_values($reslt);
                            $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"] ?? '');

                            $detailList[] = [
                                "detailDescription" => "CONTAINER",
                                "containerNo" => $fetchContainerExDelivery->no_container,
                                "containerSize" => $fetchContainerExDelivery->size_,
                                "containerType" => $fetchContainerExDelivery->type_,
                                "containerStatus" => "FULL",
                                "containerHeight" => "8.5",
                                "hz" => empty($fetchContainerExDelivery->hz) ? (empty($get_container_list[0]['hz']) ? 'N' : $get_container_list[0]['hz']) : $fetchContainerExDelivery->hz,
                                "imo" => "N",
                                "unNumber" => empty($get_container_list[0]['unNumber']) ? '' : $get_container_list[0]['unNumber'],
                                "reeferNor" => "N",
                                "temperatur" => "",
                                "ow" => "",
                                "oh" => "",
                                "ol" => "",
                                "overLeft" => "",
                                "overRight" => "",
                                "overFront" => "",
                                "overBack" => "",
                                "weight" => "",
                                "commodityCode" => trim($fetchContainerExDelivery->komoditi ?? ""),
                                "commodityName" => trim($fetchContainerExDelivery->komoditi ?? ""),
                                "carrierCode" => $payloadBatalMuat['kd_agen'],
                                "carrierName" => $payloadBatalMuat['nm_agen'],
                                "isoCode" => $new_iso,
                                "plugInDate" => "",
                                "plugOutDate" => "",
                                "ei" => "E",
                                "dischLoad" => "",
                                "flagOog" => empty($get_container_list[0]['flagOog']) ? '' : $get_container_list[0]['flagOog'],
                                "gateInDate" => "",
                                "gateOutDate" => "",
                                "startDate" => "",
                                "endDate" => "",
                                "containerDeliveryDate" => "",
                                "containerLoadingDate" => "",
                                "containerDischargeDate" => "",
                            ];
                        }
                    } else {
                        $payload_log = [];
                        if (empty($payload_uster_save)) {
                            $payload_log = [
                                "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                                "JENIS" => request()->input('JENIS'),
                                "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                                "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                            ];
                        } else {
                            $payload_log = $payload_uster_save;
                        }
                        $notes = "Payment Cash - " . $jenis . " - BUKAN EX KEGIATAN REPO ATAU JENIS BM BUKAN ALIH KAPAL";
                        $response_uster_save = [
                            'code' => "0",
                            'msg' => "Nota Batal Muat bukan Ex Kegiatan Repo (Status Gate 2) atau Jenis Batal Muat Bukan Alih Kapal"
                        ];
                        insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                        return response()->json($response_uster_save);
                    }
                } else {
                    $fetchBatalMuat = DB::connection('uster')
                        ->table('request_batal_muat as rbm')
                        ->leftJoin('v_pkk_cont as vpc', 'rbm.kapal_tuju', '=', 'vpc.no_booking')
                        ->leftJoin('nota_batal_muat as nbm', 'nbm.no_request', '=', 'rbm.no_request')
                        ->join('v_mst_pbm as vmp', 'vmp.kd_pbm', '=', 'rbm.kd_emkl')
                        ->join('container_batal_muat as cbm', 'cbm.no_request', '=', 'rbm.no_request')
                        ->select([
                            'rbm.no_request',
                            'rbm.kd_emkl',
                            'rbm.jenis_bm',
                            'rbm.kapal_tuju',
                            'rbm.status_gate',
                            'rbm.no_req_baru',
                            'rbm.o_vessel',
                            'rbm.biaya',
                            'rbm.di',
                            'nbm.no_nota',
                            'nbm.no_faktur_mti',
                            'nbm.emkl',
                            'nbm.alamat',
                            'nbm.npwp',
                            'nbm.tagihan',
                            'nbm.total_tagihan',
                            'nbm.status',
                            'nbm.ppn',
                            DB::raw("TO_CHAR(nbm.tgl_nota ,'YYYY-MM-DD HH24:MI:SS') as tglnota"),
                            'vpc.voyage',
                            'vpc.voyage_in',
                            'vpc.voyage_out',
                            'vpc.pelabuhan_asal',
                            'vpc.pelabuhan_tujuan',
                            'vpc.nm_agen',
                            'vpc.kd_agen',
                            'vpc.nm_kapal',
                            'vpc.kd_kapal',
                            DB::raw('vmp.no_account_pbm as kd_pelanggan'),
                            'cbm.no_req_batal'
                        ])
                        ->where('rbm.no_request', $id_req)
                        ->first();

                    if (!empty($fetchBatalMuat) && $fetchBatalMuat->status_gate == '2' && $fetchBatalMuat->jenis_bm == 'alih_kapal') {
                        $fetchContainerBatalMuat = DB::connection('uster')
                            ->table('container_batal_muat as cbm')
                            ->join('master_container as mc', 'cbm.no_container', '=', 'mc.no_container')
                            ->where('cbm.no_request', $id_req)
                            ->get();

                        $fetchNotaBatalMuat = DB::connection('uster')
                            ->table('nota_batal_muat as nbm')
                            ->join('nota_batal_muat_d as nbmd', 'nbm.no_nota', '=', 'nbmd.id_nota')
                            ->select(
                                'nbm.*',
                                'nbmd.*',
                                DB::raw("TO_CHAR(nbm.tgl_nota,'YYYY-MM-DD HH24:MI:SS') as tglnota")
                            )
                            ->where('nbm.no_request', $id_req)
                            ->get();

                        $adminComponent = DB::connection('uster')
                            ->table('nota_batal_muat as nbm')
                            ->join('nota_batal_muat_d as nbmd', 'nbm.no_nota', '=', 'nbmd.id_nota')
                            ->select('nbmd.tarif')
                            ->where('nbm.no_request', $id_req)
                            ->where('nbmd.id_iso', 'ADM')
                            ->first();

                        $get_vessel = getVessel($fetchBatalMuat->nm_kapal, $fetchBatalMuat->voyage, $fetchBatalMuat->voyage_in, $fetchBatalMuat->voyage_out);

                        $get_container_list = getContainer(
                            null,
                            $fetchBatalMuat->kd_kapal,
                            $fetchBatalMuat->voyage_in,
                            $fetchBatalMuat->voyage_out,
                            $fetchBatalMuat->voyage,
                            "E",
                            "LCB"
                        );

                        $get_iso_code = getIsoCode();

                        if (empty($get_iso_code)) {
                            $payload_log = [];
                            if (empty($payload_uster_save)) {
                                $payload_log = [
                                    "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                                    "JENIS" => request()->input('JENIS'),
                                    "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                                    "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                                ];
                            } else {
                                $payload_log = $payload_uster_save;
                            }
                            $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                            $response_uster_save = [
                                'code' => "0",
                                'msg' => "Gagal mengambil Iso Code ke Praya, silahkan ulangi Kembali hit process ini."
                            ];
                            insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                            return response()->json($response_uster_save);
                        }

                        $pelabuhan_asal = $fetchBatalMuat->pelabuhan_asal;
                        $pelabuhan_tujuan = $fetchBatalMuat->pelabuhan_tujuan;

                        $idRequest = $id_req;
                        $trxNumber = $fetchBatalMuat->no_nota;
                        $paymentDate = $fetchBatalMuat->tglnota;
                        $invoiceNumber = $fetchBatalMuat->no_faktur_mti;
                        $requestType = 'LOADING CANCEL - BEFORE GATEIN';
                        $parentRequestId = $fetchBatalMuat->no_req_batal;
                        $parentRequestType = 'LOADING CANCEL - BEFORE GATEIN';
                        $serviceCode = 'LCB';
                        $jenisBM = $fetchBatalMuat->jenis_bm;
                        $vesselId = $fetchBatalMuat->kd_kapal;
                        $vesselName = $fetchBatalMuat->nm_kapal;
                        $voyage = empty($fetchBatalMuat->voyage) ? '' : $fetchBatalMuat->voyage;
                        $voyageIn = empty($fetchBatalMuat->voyage_in) ? '' : $fetchBatalMuat->voyage_in;
                        $voyageOut = empty($fetchBatalMuat->voyage_out) ? '' : $fetchBatalMuat->voyage_out;
                        $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut;
                        $eta = empty($get_vessel['eta']) ? '' : $get_vessel['eta'];
                        $etb = empty($get_vessel['etb']) ? '' : $get_vessel['etb'];
                        $etd = empty($get_vessel['etd']) ? '' : $get_vessel['etd'];
                        $ata = empty($get_vessel['ata']) ? '' : $get_vessel['ata'];
                        $atb = empty($get_vessel['atb']) ? '' : $get_vessel['atb'];
                        $atd = empty($get_vessel['atd']) ? '' : $get_vessel['atd'];
                        $startWork = empty($get_vessel['start_work']) ? '' : $get_vessel['start_work'];
                        $endWork = empty($get_vessel['end_work']) ? '' : $get_vessel['end_work'];
                        $pol = $pelabuhan_asal;
                        $pod = $pelabuhan_tujuan;
                        $dischargeDate = $get_vessel['discharge_date'] ?? null;
                        $shippingLineName = $fetchBatalMuat->nm_agen;
                        $customerCode = $fetchBatalMuat->kd_pelanggan;
                        $customerCodeOwner = '';
                        $customerName = $fetchBatalMuat->emkl;
                        $customerAddress = $fetchBatalMuat->alamat;
                        $npwp = $fetchBatalMuat->npwp;
                        $blNumber = "";
                        $bookingNo = $fetchBatalMuat->kapal_tuju;
                        $deliveryDate = '';
                        $doNumber = "";
                        $tradeType = $fetchBatalMuat->di;
                        $customsDocType = "";
                        $customsDocNo = "";
                        $customsDocDate = "";
                        if ((int)$fetchBatalMuat->total_tagihan > 5000000) {
                            $amount = (int)$fetchBatalMuat->total_tagihan + 10000;
                        } else {
                            $amount = (int)$fetchBatalMuat->total_tagihan;
                        }
                        $administration = $adminComponent ? $adminComponent->tarif : 0;
                        $ppn = empty($fetchBatalMuat->ppn) ? 'N' : 'Y';
                        $amountPpn  = (int)$fetchBatalMuat->ppn;
                        $amountDpp = (int)$fetchBatalMuat->tagihan;
                        if ($fetchBatalMuat->tagihan > 5000000) {
                            $amountMaterai = 10000;
                        } else {
                            $amountMaterai = 0;
                        }
                        $approvalDate = empty($fetchBatalMuat->tglapprove) ? '' : $fetchBatalMuat->tglapprove;
                        $status = 'PAID';
                        $changeDate = $fetchBatalMuat->tglnota;
                        $charge = 'Y';

                        $detailList = [];
                        $containerList = [];
                        foreach ($fetchContainerBatalMuat as $v) {
                            $_get_container = null;
                            foreach ($get_container_list as $v_container) {
                                if ($v_container['containerNo'] == $v->no_container) {
                                    $_get_container = $v_container;
                                    break;
                                }
                            }

                            $reslt = [];
                            foreach ($get_iso_code as $value) {
                                if (
                                    strtoupper($value['type']) == strtoupper($v->type_) &&
                                    strtoupper($value['size']) == strtoupper($v->size_)
                                ) {
                                    $reslt[] = $value;
                                }
                            }
                            $array_iso_code = array_values($reslt);
                            $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"] ?? '');

                            $containerList[] = $v->no_container;
                            $detailList[] = [
                                "detailDescription" => "CONTAINER",
                                "containerNo" => $v->no_container,
                                "containerSize" => $v->size_,
                                "containerType" => $v->type_,
                                "containerStatus" => "FULL",
                                "containerHeight" => "8.5",
                                "hz" => empty($v->hz) ? (empty($_get_container['hz']) ? 'N' : $_get_container['hz']) : $v->hz,
                                "imo" => "N",
                                "unNumber" => empty($_get_container['unNumber']) ? '' : $_get_container['unNumber'],
                                "reeferNor" => "N",
                                "temperatur" => "",
                                "ow" => "",
                                "oh" => "",
                                "ol" => "",
                                "overLeft" => "",
                                "overRight" => "",
                                "overFront" => "",
                                "overBack" => "",
                                "weight" => "",
                                "commodityCode" => trim($v->commodity ?? ""),
                                "commodityName" => trim($v->commodity ?? ""),
                                "carrierCode" => $fetchBatalMuat->kd_agen,
                                "carrierName" => $fetchBatalMuat->nm_agen,
                                "isoCode" => $new_iso,
                                "plugInDate" => "",
                                "plugOutDate" => "",
                                "ei" => "E",
                                "dischLoad" => "",
                                "flagOog" => empty($_get_container['flagOog']) ? '' : $_get_container['flagOog'],
                                "gateInDate" => "",
                                "gateOutDate" => "",
                                "startDate" => "",
                                "endDate" => "",
                                "containerDeliveryDate" => "",
                                "containerLoadingDate" => "",
                                "containerDischargeDate" => "",
                            ];
                        }

                        $strContList = implode(", ", $containerList);
                        $detailPranotaList = [];
                        foreach ($fetchNotaBatalMuat as $v) {
                            $detailPranotaList[] = [
                                "lineNumber" => $v->line_number,
                                "description" => $v->keterangan,
                                "flagTax" => "Y",
                                "componentCode" => $v->keterangan,
                                "componentName" => $v->keterangan,
                                "startDate" => "",
                                "endDate" => "",
                                "quantity" => $v->jml_cont,
                                "tarif" => $v->tarif,
                                "basicTarif" => $v->tarif,
                                "containerList" => $strContList,
                                "containerSize" => $fetchContainerBatalMuat[0]->size_,
                                "containerType" => $fetchContainerBatalMuat[0]->type_,
                                "containerStatus" => "",
                                "containerHeight" => "8.5",
                                "hz" => empty($v->hz) ? "N" : $v->hz,
                                "ei" => "I",
                                "equipment" => "",
                                "strStartDate" => "",
                                "strEndDate" => "",
                                "days" => "1",
                                "amount" => $v->biaya,
                                "via" => "YARD",
                                "package" => "",
                                "unit" => "BOX",
                                "qtyLoading" => "",
                                "qtyDischarge" => "",
                                "equipmentName" => "",
                                "duration" => "",
                                "flagTool" => "N",
                                "itemCode" => "",
                                "oog" => "",
                                "imo" => "",
                                "blNumber" => "",
                                "od" => "N",
                                "dg" => "N",
                                "sling" => "N",
                                "changeDate" => $v->tglnota,
                                "changeBy" => "Admin Uster"
                            ];
                        }
                    } else {
                        $payload_log = [];
                        if (empty($payload_uster_save)) {
                            $payload_log = [
                                "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                                "JENIS" => request()->input('JENIS'),
                                "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                                "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                            ];
                        } else {
                            $payload_log = $payload_uster_save;
                        }
                        $notes = "Payment Cash - " . $jenis . " - BUKAN EX KEGIATAN REPO ATAU JENIS BM BUKAN ALIH KAPAL";
                        $response_uster_save = [
                            'code' => "0",
                            'msg' => "Nota Batal Muat bukan Ex Kegiatan Repo (Status Gate 2) atau Jenis Batal Muat Bukan Alih Kapal"
                        ];
                        insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                        return response()->json($response_uster_save);
                    }
                }
            } else {
                $payload_log = [];
                if (empty($payload_uster_save)) {
                    $payload_log = [
                        "NO_PROFORMA" => request()->input('NO_PROFORMA'),
                        "JENIS" => request()->input('JENIS'),
                        "BANK_ACCOUNT_NUMBER" => request()->input("BANK_ACCOUNT_NUMBER"),
                        "PAYMENT_CODE" => request()->input("PAYMENT_CODE")
                    ];
                } else {
                    $payload_log = $payload_uster_save;
                }
                $notes = "Payment Cash - " . $jenis . " - BUKAN STRIPPING, STUFFING, DELIVERY, BATAL MUAT";
                $response_uster_save = [
                    'code' => "0",
                    'msg' => "Request ini bukan milik MTI - " . $jenis . " - (Bukan service STRIPPING, STUFFING, DELIVERY, BATAL MUAT)"
                ];
                insertPrayaServiceLog($url_uster_save, $payload_log, $response_uster_save, $notes);

                return response()->json($response_uster_save);
            }

            $PNK_PAYMENT_VIA = '';
            if (empty($_POST["NO_PROFORMA"])) {
                $PNK_PAYMENT_VIA = "CMS OR BY POSTMAN";
            } else {
                $PNK_PAYMENT_VIA = "EBPP";
            }

            $payload_header = array(
                "PNK_REQUEST_ID" => $id_req,
                "PNK_NO_PROFORMA" => null,
                "PNK_CONTAINER_LIST" => $strContList,
                "PNK_JENIS_SERVICE" => $jenis,
                "PNK_JENIS_BATAL_MUAT" => $jenisBM ?? null,
                "PNK_PAYMENT_VIA" => $PNK_PAYMENT_VIA,
                "EBPP_CREATED_DATE" => null,
                "idRequest" => $idRequest,
                "billerId" => "00009",
                "trxNumber" => $trxNumber,
                "paymentDate" => $paymentDate,
                "invoiceNumber" => $invoiceNumber,
                "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
                "orgCode" => env('PRAYA_ITPK_PNK_ORG_CODE'),
                "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID'),
                "terminalCode" => env('PRAYA_ITPK_PNK_TERMINAL_CODE'),
                "branchId" => env('PRAYA_ITPK_PNK_BRANCH_ID'),
                "branchCode" => env('PRAYA_ITPK_PNK_BRANCH_CODE'),
                "areaTerminal" => env('PRAYA_ITPK_PNK_AREA_CODE'),
                "bankAccountNumber" => $bankAccountNumber,
                "administration" => $administration,
                "requestType" => $requestType,
                "parentRequestId" => $parentRequestId,
                "parentRequestType" => $parentRequestType,
                "serviceCode" => $serviceCode,
                "vesselId" => $vesselId,
                "vesselName" => $vesselName,
                "voyage" => $voyage,
                "voyageIn" => $voyageIn,
                "voyageOut" => $voyageOut,
                "voyageInOut" => $voyageInOut,
                "eta" => $eta,
                "etb" => $etb,
                "etd" => $etd,
                "ata" => $ata,
                "atb" => $atb,
                "atd" => $atd,
                "startWork" => $startWork,
                "endWork" => $endWork,
                "pol" => $pol,
                "pod" => $pod,
                "fpod" => null,
                "dischargeDate" => $dischargeDate,
                "shippingLineName" => $shippingLineName,
                "customerCodeOwner" => $customerCodeOwner,
                "customerCode" => $customerCode,
                "customerName" => $customerName,
                "customerAddress" => $customerAddress,
                "npwp" => $npwp,
                "blNumber" => $blNumber,
                "bookingNo" => $bookingNo,
                "deliveryDate" => $deliveryDate,
                "via" => "YARD",
                "doNumber" => $doNumber,
                "tradeType" => $tradeType,
                "customsDocType" => $customsDocType,
                "customsDocNo" => $customsDocNo,
                "customsDocDate" => $customsDocDate,
                "amount" => $amount,
                "ppn" => $ppn,
                "amountPpn" => $amountPpn,
                "amountMaterai" => $amountMaterai,
                "amountDpp" => $amountDpp,
                "approval" => "Y",
                "approvalDate" => $approvalDate,
                "approvalBy" => "Admin Uster",
                "remarkReject" => "",
                "status" => "PAID",
                "changeBy" => "Admin Uster",
                "changeDate" => $changeDate,
                "charge" => $charge
            );

            if (!empty($paymentCode)) {
                $payment_code = array(
                    "paymentCode" => $paymentCode
                );
                $payload_header = array_merge($payload_header, $payment_code);
            }

            $payload_body = array(
                "detailList" => $detailList,
                "detailPranotaList" => $detailPranotaList
            );

            $payload = array_merge($payload_header, $payload_body);

            $response_uster_save = sendDataFromUrlTryCatch($payload, $url_uster_save, 'POST', getTokenPraya());
            $notes = $jenis == "DELIVERY" ? "Payment Cash - " . $jenis . " EX REPO" : "Payment Cash - " . $jenis;
            $first_char_http_code = substr(strval($response_uster_save['httpCode']), 0, 1);

            if ($first_char_http_code == 5 || $first_char_http_code == 1) {
                $decodedRes = json_decode($response_uster_save["response"]["msg"]) ? json_decode($response_uster_save["response"]["msg"]) : $response_uster_save["response"]["msg"];
                $defaultRes = "Service is Unavailable, please try again (HTTP Error Code : " . $response_uster_save["httpCode"] . ")";
                $response_uster_save_logging = array(
                    "code" => "0",
                    "msg" => $defaultRes,
                    "response" => $decodedRes
                );

                insertPrayaServiceLog($url_uster_save, $payload_header, $response_uster_save_logging, $notes);

                return response()->json($response_uster_save_logging, 500);
            }

            $response_uster_save_decode = json_decode($response_uster_save['response'], true);

            $response_uster_save_logging = $response_uster_save_decode["code"] == 0 ? array(
                "code" => $response_uster_save_decode['code'],
                "msg" => $response_uster_save_decode['msg']
            ) : $response_uster_save_decode;

            if (!empty($idRequest) && substr($idRequest, 0, 3) == "STP") {
                $payload_stp_logging = array(
                    "PNK_REQUEST_ID" => $id_req,
                    "PNK_NO_PROFORMA" => $_POST["NO_PROFORMA"] ?? null,
                    "PNK_CONTAINER_LIST" => $strContList,
                    "PNK_JENIS_SERVICE" => $jenis,
                    "PNK_JENIS_BATAL_MUAT" => $jenisBM,
                    "PNK_PAYMENT_VIA" => $PNK_PAYMENT_VIA,
                    "EBPP_CREATED_DATE" => $_POST["EBPP_CREATED_DATE"] ?? null,
                    "detailList" => $containerListLog ?? []
                );
                $notes_stp_logging = "STP - " . $idRequest . " - CONTAINER LOGGING";

                // LOGGING FOR PAYTHRU PERP_STRIP
                insertPrayaServiceLog($url_uster_save, $payload_stp_logging, $response_uster_save_logging, $notes_stp_logging);
            }

            insertPrayaServiceLog($url_uster_save, $payload_header, $response_uster_save_logging, $notes);

            if (isset($response_uster_save_decode['code']) && $response_uster_save_decode['code'] == 0) {
                return response()->json($response_uster_save_logging, 500);
            } else {
                return response()->json($response_uster_save_logging, 200);
            }
        } catch (Exception $ex) {
            return response()->json([
                'code' => 500,
                'msg' => $ex->getMessage()
            ], 500);
        }
    }
}


function getVessel($vessel, $voy, $voyIn, $voyOut)
{

    $vessel = str_replace(" ", "+", $vessel);

    try {
        $url = env('PRAYA_API_TOS') . "/api/getVessel?pol=" . env('PRAYA_ITPK_PNK_PORT_CODE') . "&eta=1&etd=1&orgId=" . env('PRAYA_ITPK_PNK_ORG_ID') . "&terminalId=" . env('PRAYA_ITPK_PNK_TERMINAL_ID') . "&search=$vessel";
        $json = getDatafromUrl($url);
        $json = json_decode($json, true);

        if ($json['code'] == 1) {
            $vessel_resp = '';
            foreach ($json['data'] as $k => $v) {
                if ($v['voyage'] == $voy && $v['voyage_in'] == $voyIn && $v['voyage_out'] == $voyOut) {
                    $vessel_resp = $v;
                }
            }
            return $vessel_resp;
        } else {
            return $json['msg'];
        }
    } catch (Exception $ex) {
        return $ex->getMessage();
    }
}

function getContainer($no_container, $vessel_code, $voyage_in, $voyage_out, $voy, $ei, $serviceCode)
{

    // echo $no_container  . ' | ' .  $vessel_code  . ' | ' .  $voyage_in  . ' | ' .  $voyage_out  . ' | ' .  $voy;

    try {
        $payload = array(
            "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
            "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID'),
            "vesselId" => $vessel_code,
            "voyageIn" => $voyage_in,
            "voyageOut" => $voyage_out,
            "voyage" => $voy,
            "portCode" => env('PRAYA_ITPK_PNK_PORT_CODE'),
            "ei" => $ei,
            "containerNo" => $no_container,
            "serviceCode" => $serviceCode
        );

        $response = sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/containerList", 'POST', getTokenPraya());
        $response = json_decode($response['response'], true);

        if ($response['code'] == 1 && !empty($response["data"])) {
            return $response['data'];
        }
    } catch (Exception $ex) {
        return $ex->getMessage();
    }
}

function getStuffingContainer($no_container)
{
    try {
        $payload = array(
            "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
            "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID'),
            "containerNo" => $no_container
        );

        $response = sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/stuffingContainerList", 'POST', getTokenPraya());
        $response = json_decode($response['response'], true);

        if ($response['code'] == 1 && !empty($response["dataRec"])) {
            return $response['dataRec'];
        }
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}

function getIsoCode()
{

    try {
        $searchFieldColumn = array(
            "size" => "",
            "type" => "",
            "height" => "",
        );

        $payload = array(
            "terminalCode" => env('PRAYA_ITPK_PNK_TERMINAL_CODE'),
            "searchFieldColumn" => $searchFieldColumn,
            "page" => 1,
            "record" => 1000
        );

        // echo json_encode($payload);
        // echo "<<payload";

        $response = sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/isoCodeList", 'POST', getTokenPraya());
        $response = json_decode($response['response'], true);

        if ($response['code'] == 1 && !empty($response["dataRec"])) {
            return $response['dataRec'];
        }
    } catch (Exception $ex) {
        echo $ex->getMessage();
    }
}

function mapNewIsoCode($iso)
{
    $new_iso = "";

    switch ($iso) {
        case "42B0":
            $new_iso = "4500"; //DRY 40
            break;
        case "2650":
            $new_iso = "22U1"; //OT 20
            break;
        case "42U0":
            $new_iso = "45G1"; //OT 40
            break;
        case "4260":
            $new_iso = "45G1"; //FLT 40
            break;
        // Penambahan iso code baru untuk container 21ft (Chossy PIP (11962624))
        case "2280":
            $new_iso = "22G1"; //DRY 20
            break;
        // End Penambahan
        default:
            $new_iso = $iso;
    };

    return $new_iso;
}

function getDatafromUrl($url)
{
    $token = getTokenPraya();

    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on respon0120e
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        // dicomment, kena error ssl ca cert
        // CURLOPT_CAINFO		   => "/var/www/html/ibis_qa/tmp/cacert.pem",
        // CURLOPT_SSL_VERIFYPEER => false, // <- dihapus sebelum di push
        CURLOPT_HTTPHEADER      => array(
            "Content-Type: application/json",
            "Authorization: Bearer $token",
        ),
    );

    $ch      = curl_init($url);
    curl_setopt_array($ch, $options);

    Log::channel('praya')->info('Request to Praya', ['url' => $url]);
    $start = microtime(true);

    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $end = microtime(true);

    Log::channel('praya')->info('ILCS Response Info', [
        'time' => $end - $start,
        'curl_info' => $header,
        'response' => $content,
        'error' => curl_error($ch),
    ]);
    curl_close($ch);

    // $header['errno']   = $err;
    // $header['errmsg']  = $errmsg;

    //change errmsg here to errno
    if ($errmsg) {
        echo "CURL:" . $errmsg . "<BR>";
    }
    return $content;
}
