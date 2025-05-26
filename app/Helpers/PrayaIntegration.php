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

if (!function_exists('savePaymentExternal')) {
    function save_payment_uster($id_request, $jenis_payment, $bank_id)
    {
        $json = file_get_contents('php://input');
        // Converts it into a PHP object
        $payload_uster_save = json_decode($json, true);

        $url_uster_save = env('PRAYA_API_INTEGRATION') . "/api/usterSave";

        $payloadBatalMuat = $payload_uster_save["PAYLOAD_BATAL_MUAT"];
        // $jenis = $payload_uster_save["JENIS"];
        $jenis = $jenis_payment;
        // $id_req = $payload_uster_save["ID_REQUEST"];
        $id_req = $id_request;
        // $bankAccountNumber = $payload_uster_save["BANK_ACCOUNT_NUMBER"];
        $payment_via = "CMS";
        $bankAccountNumber = $bank_id;
        $paymentCode = $payload_uster_save["PAYMENT_CODE"];
        $charge = empty($payloadBatalMuat) ? "Y" : "N"; //kalau payload batal muat ada berarti tdk bayar


        // Variable untuk logging container, hanya variable penting yg dimasukan karena container bisa jadi sngt bnyk (Sementara utk PERP_STRIP)
        $containerListLog = array();


        $del_no_request = empty($payloadBatalMuat) ? $id_req : $payloadBatalMuat->ex_noreq;
        $queryDelivery =
            "SELECT

                rd.NO_REQUEST,
                rd.NO_BOOKING,
                rd.KD_EMKL,
                rd.O_VESSEL,
                rd.VOYAGE,
                rd.KD_PELABUHAN_ASAL, --POL
                rd.KD_PELABUHAN_TUJUAN, --POD
                rd.O_VOYIN,
                rd.O_VOYOUT,
                rd.DELIVERY_KE,
                rd.TGL_REQUEST,
                rd.DI,
                -- vpc.*,
                vpc.KD_KAPAL,
                vpc.NM_KAPAL,
                vpc.VOYAGE_IN,
                vpc.VOYAGE_OUT,
                vpc.PELABUHAN_TUJUAN,
                vpc.PELABUHAN_ASAL,
                vpc.NM_AGEN,
                vpc.KD_AGEN,
                --	nd.*,
                nd.NO_NOTA,
                nd.NO_FAKTUR_MTI,
                nd.TAGIHAN,
                nd.PPN,
                nd.TOTAL_TAGIHAN,
                nd.EMKL,
                nd.ALAMAT,
                nd.NPWP,
                TO_CHAR(nd.TGL_NOTA ,'YYYY-MM-DD HH24:MI:SS') TGLNOTA,
                TO_CHAR(rd.TGL_REQUEST,'YYYY-MM-DD HH24:MI:SS') TGLSTART,
                TO_CHAR(rd.TGL_REQUEST + INTERVAL '4' DAY,'YYYY-MM-DD HH24:MI:SS') TGLEND,
                -- vmp.*,
                vmp.NO_ACCOUNT_PBM KD_PELANGGAN
            FROM
                REQUEST_DELIVERY rd
            LEFT JOIN V_PKK_CONT vpc ON
                rd.NO_BOOKING = vpc.NO_BOOKING
            JOIN NOTA_DELIVERY nd ON
                nd.NO_REQUEST = rd.NO_REQUEST
            JOIN V_MST_PBM vmp ON
                vmp.KD_PBM = rd.KD_EMKL
            WHERE
                rd.NO_REQUEST = '$del_no_request'";

        if ($jenis == 'STRIPPING' || $jenis == 'PERP_STRIP') { //DELIVERY KALO DARI SISI TPK
            $queryStripping =
                "SELECT
                rs.NO_BOOKING,
                rs.NO_BL,
                rs.TYPE_STRIPPING,
                vpc.KD_KAPAL,
                vpc.NM_KAPAL,
                vpc.VOYAGE_IN,
                vpc.VOYAGE_OUT,
                vpc.VOYAGE, -- KOLOM BELUM DIISI DI DEV
                vpc.PELABUHAN_TUJUAN,
                vpc.PELABUHAN_ASAL,
                vpc.NM_AGEN,
                vpc.KD_AGEN,
                vpc.NO_UKK,
                ns.NO_NOTA,
                ns.NO_FAKTUR_MTI,
                ns.TAGIHAN,
                ns.PPN,
                ns.TOTAL_TAGIHAN,
                ns.EMKL,
                ns.ALAMAT,
                ns.NPWP,
                vmp.NO_ACCOUNT_PBM KD_PELANGGAN,
                TO_CHAR(rs.TGL_AWAL,'YYYY-MM-DD HH24:MI:SS') TGLAWAL,
                TO_CHAR(rs.TGL_AKHIR,'YYYY-MM-DD HH24:MI:SS') TGLAKHIR,
                TO_CHAR(cs.TGL_APPROVE, 'YYYY-MM-DD HH24:MI:SS') TGLAPPROVE,
                TO_CHAR(cs.TGL_APP_SELESAI, 'YYYY-MM-DD HH24:MI:SS') TGLAPPROVE_SELESAI,
                TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA,
                -- TO_CHAR(pcs.TGL_SELESAI, 'YYYY-MM-DD HH24:MI:SS') TGLSELESAI,
                CASE
                    WHEN rs.TGL_AWAL IS NULL OR rs.TGL_AKHIR IS NULL THEN 4
                    ELSE rs.TGL_AKHIR - rs.TGL_AWAL
                END AS COUNT_DAYS
            FROM
                REQUEST_STRIPPING rs
            LEFT JOIN V_PKK_CONT vpc ON
                rs.NO_BOOKING = vpc.NO_BOOKING
            JOIN NOTA_STRIPPING ns ON
                ns.NO_REQUEST = rs.NO_REQUEST
            JOIN CONTAINER_STRIPPING cs ON
                rs.NO_REQUEST = cs.NO_REQUEST
            JOIN V_MST_PBM vmp ON
                vmp.KD_PBM = ns.KD_EMKL
            --   JOIN PLAN_REQUEST_STRIPPING prs ON rs.NO_REQUEST = prs.NO_REQUEST_APP_STRIPPING
            --   JOIN PLAN_CONTAINER_STRIPPING pcs ON pcs.NO_REQUEST = prs.NO_REQUEST
            WHERE
                    rs.NO_REQUEST = '$id_req'";
            $fetchStripping = DB::connection('uster')->selectOne($queryStripping);
            $queryContainerStripping =
                "SELECT cs.*, mc.*, TO_CHAR(cs.TGL_SELESAI, 'YYYY-MM-DD HH24:MI:SS') TGLSELESAI, TO_CHAR(cs.END_STACK_PNKN, 'YYYY-MM-DD HH24:MI:SS') TGLSELESAI_PERP FROM CONTAINER_STRIPPING cs JOIN MASTER_CONTAINER mc ON cs.NO_CONTAINER = mc.NO_CONTAINER WHERE cs.NO_REQUEST = '$id_req'";
            $fetchContainerStripping =  DB::connection('uster')->select($queryContainerStripping);
            $queryNotaStripping =
                "SELECT ns.*, nsd.*, TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA, (SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = nsd.ID_ISO) STATUS, TO_CHAR(nsd.START_STACK,'YYYY-MM-DD HH24:MI:SS') AWAL_PENUMPUKAN, TO_CHAR(nsd.END_STACK,'YYYY-MM-DD HH24:MI:SS') AKHIR_PENUMPUKAN FROM NOTA_STRIPPING ns JOIN NOTA_STRIPPING_D nsd ON nsd.NO_NOTA = ns.NO_NOTA WHERE ns.NO_REQUEST = '$id_req' ";
            $fetchNotaStripping = DB::connection('uster')->select($queryNotaStripping);
            $queryGetAdmin =
                "SELECT TARIF FROM NOTA_STRIPPING ns JOIN NOTA_STRIPPING_D nsd ON nsd.NO_NOTA = ns.NO_NOTA WHERE ns.NO_REQUEST = '$id_req' AND nsd.ID_ISO = 'ADM' ";
            $adminComponent = DB::connection('uster')->selectOne($queryGetAdmin);

            $get_vessel = getVessel($fetchStripping->nm_kapal, $fetchStripping->voyage, $fetchStripping->voyage_in, $fetchStripping->voyage_out);

            $get_container_list = getContainer(NULL, $fetchStripping->kd_kapal, $fetchStripping->voyage_in, $fetchStripping->voyage_out, $fetchStripping->voyage, "I", "DEL");

            $get_iso_code = getIsoCode();

            if (empty($get_iso_code)) {
                $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                $response_uster_save = array(
                    'code' => "0",
                    'msg' => "Gagal mengambil Iso Code ke Praya"
                );
                insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

                echo json_encode($response_uster_save);
                die();
            }

            $tgl_awal = $fetchStripping->tglawal;
            $tgl_akhir = $fetchStripping->tglakhir;
            if (empty($tgl_awal) || empty($tgl_akhir)) {
                $tgl_awal = $fetchStripping->tglapprove;
                $tgl_akhir = $fetchStripping->tglapprove_selesai;
            }

            // echo json_encode($get_vessel);
            // echo json_encode($get_container_list);

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
            $customerName = $fetchStripping->emkl;
            $customerAddress = $fetchStripping->alamat;
            $npwp = $fetchStripping->npwp;
            $blNumber = $fetchStripping->no_bl;
            $bookingNo = $fetchStripping->no_booking;
            // $deliveryDate = $fetchStripping->tglselesai; //paythruDate
            $doNumber = $fetchStripping->no_booking;
            // $doDate = "";
            $tradeType = $fetchStripping->type_stripping == 'D' ? 'I' : 'O';
            $customsDocType = "";
            $customsDocNo = "";
            $customsDocDate = "";
            if ((int)$fetchStripping->total_tagihan > 5000000) {
                $amount = (int)$fetchStripping->total_tagihan + 10000;
            } else {
                (int)$amount = $fetchStripping->total_tagihan;
            }
            if ($adminComponent) {
                $administration = $adminComponent->tarif;
            }
            if (empty($fetchStripping->ppn)) {
                $ppn =  'N';
            } else {
                $ppn = 'Y';
            };
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
                foreach ($get_container_list as $k_container => $v_container) {
                    if ($v_container->containerno  == $v->no_container) {
                        $_get_container = $v_container;
                        break;
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
                        "containerSize" => $fetchContainerStripping[0]['SIZE_'],
                        "containerType" => $fetchContainerStripping[0]['TYPE_'],
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
            $queryStuffing =
                "SELECT
                    rs.NO_BOOKING,
                    rs.NO_BL,
                    rs.NO_NPE, --customDocs
                    rs.DI,
                    rs.STUFFING_DARI, --ASAL STUFFING HARUS DARI TPK
                    vpc.KD_KAPAL,
                    vpc.NM_KAPAL,
                    vpc.VOYAGE_IN,
                    vpc.VOYAGE_OUT,
                    vpc.VOYAGE, -- KOLOM BELUM DIISI
                    vpc.PELABUHAN_TUJUAN,
                    vpc.PELABUHAN_ASAL,
                    vpc.NM_AGEN,
                    vpc.KD_AGEN,
                    vpc.NO_UKK,
                    ns.NO_NOTA,
                    ns.NO_FAKTUR_MTI,
                    ns.TAGIHAN,
                    ns.PPN,
                    ns.TOTAL_TAGIHAN,
                    ns.EMKL,
                    ns.ALAMAT,
                    ns.NPWP,
                    vmp.NO_ACCOUNT_PBM KD_PELANGGAN,
                    cs.ASAL_CONT, --ASAL CONTAINER HARUS DARI TPK
                    TO_CHAR(pcs.TGL_APPROVE,'YYYY-MM-DD HH24:MI:SS') TGLAPPROVE,
                    TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA,
                    TO_CHAR(rs.TGL_REQUEST,'YYYY-MM-DD HH24:MI:SS') TGLSTART,
                    TO_CHAR(rs.TGL_REQUEST + INTERVAL '4' DAY,'YYYY-MM-DD HH24:MI:SS') TGLEND
                FROM
                    REQUEST_STUFFING rs
                LEFT JOIN V_PKK_CONT vpc ON
                    rs.NO_BOOKING = vpc.NO_BOOKING
                JOIN NOTA_STUFFING ns ON
                    ns.NO_REQUEST = rs.NO_REQUEST
                JOIN V_MST_PBM vmp ON
                    vmp.KD_PBM = ns.KD_EMKL
                JOIN CONTAINER_STUFFING cs ON
                    cs.NO_REQUEST = rs.NO_REQUEST
                JOIN PLAN_REQUEST_STUFFING prs ON
                    prs.NO_REQUEST_APP = rs.NO_REQUEST
                JOIN PLAN_CONTAINER_STUFFING pcs ON
                    pcs.NO_REQUEST = prs.NO_REQUEST
                WHERE rs.NO_REQUEST = '$id_req'";
            $fetchStuffing = DB::connection('uster')->selectOne($queryStuffing);


            if ($fetchStuffing->stuffing_dari == 'TPK') {
                $queryContainerStuffing =
                    "SELECT cs.*, mc.*, TO_CHAR(cs.START_PERP_PNKN,'YYYY-MM-DD HH24:MI:SS') TGLPAYTHRU FROM CONTAINER_STUFFING cs JOIN MASTER_CONTAINER mc ON cs.NO_CONTAINER = mc.NO_CONTAINER WHERE cs.NO_REQUEST = '$id_req'";
                $fetchContainerStuffing = DB::connection('uster')->select($queryContainerStuffing);
                $queryNotaStuffing =
                    "SELECT ns.*, nsd.*, TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA, (SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = nsd.ID_ISO) STATUS, TO_CHAR(nsd.START_STACK,'YYYY-MM-DD HH24:MI:SS') AWAL_PENUMPUKAN, TO_CHAR(nsd.END_STACK,'YYYY-MM-DD HH24:MI:SS') AKHIR_PENUMPUKAN FROM NOTA_STUFFING ns JOIN NOTA_STUFFING_D nsd ON nsd.NO_NOTA = ns.NO_NOTA WHERE ns.NO_REQUEST = '$id_req' ";
                $fetchNotaStuffing = DB::connection('uster')->select($queryNotaStuffing);
                $queryGetAdmin =
                    "SELECT TARIF FROM NOTA_STUFFING ns JOIN NOTA_STUFFING_D nsd ON nsd.NO_NOTA = ns.NO_NOTA WHERE ns.NO_REQUEST = '$id_req' AND nsd.ID_ISO = 'ADM' ";
                $adminComponent = DB::connection('uster')->selectOne($queryGetAdmin);

                // $get_vessel = getVessel($fetchStuffing->nm_kapal, $fetchStuffing->voyage, $fetchStuffing->voyage_in, $fetchStuffing->voyage_out);

                // $get_container_list = getContainer(NULL, $fetchStuffing->kd_kapal, $fetchStuffing->voyage_in, $fetchStuffing->voyage_out, $fetchStuffing->voyage, "E", "REC");

                $get_iso_code = getIsoCode();

                if (empty($get_iso_code)) {
                    $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                    $response_uster_save = array(
                        'code' => "0",
                        'msg' => "Gagal mengambil Iso Code ke Praya"
                    );
                    insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

                    return json_encode($response_uster_save);
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
                $dischargeDate = ""; //$get_vessel['discharge_date'];
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
                $customsDocNo = $fetchStuffing->di == 'D' ? (empty($fetchStuffing["NO_NPE"]) ? "" : $fetchStuffing["NO_NPE"]) : "";
                $customsDocDate = "";
                if ((int)$fetchStuffing->total_tagihan > 5000000) {
                    $amount = (int)$fetchStuffing->total_tagihan + 10000;
                } else {
                    (int)$amount = $fetchStuffing->total_tagihan;
                }
                if ($adminComponent) {
                    $administration = $adminComponent->tarif;
                }
                if (empty($fetchStuffing->ppn)) {
                    $ppn =  'N';
                } else {
                    $ppn = 'Y';
                };
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

                $detailList = array();
                $containerList = array();
                foreach ($fetchContainerStuffing as $k => $v) {
                    // foreach ($get_container_list as $k_container => $v_container) {
                    //   if ($v_container->containerno  == $v->no_container) {
                    //     $_get_container = $v_container;
                    //     break;
                    //   }
                    // }
                    // $array_iso_code = array_values(array_filter($get_iso_code, function ($value) use ($v) {
                    //     return strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_);
                    // }));

                    $reslt = array();
                    foreach ($get_iso_code as $key => $value) {
                        if (strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_)) {
                            array_push($reslt, $value);
                        }
                    }
                    $array_iso_code = array_values($reslt);
                    $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"]);

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
                            "hz" => empty($v->hz) ? 'N' : $v->hz,
                            "imo" => "N",
                            // "unNumber" => empty($_get_container->unnumber) ? '' : $_get_container->unnumber,
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
                            "commodityCode" => trim($v->commodity, " "),
                            "commodityName" => trim($v->commodity, " "),
                            "carrierCode" => $fetchStuffing->kd_agen,
                            "carrierName" => $fetchStuffing->nm_agen,
                            "isoCode" => $new_iso,
                            "plugInDate" => "",
                            "plugOutDate" => "",
                            "ei" => "I",
                            "dischLoad" => "",
                            // "flagOog" => empty($_get_container->flagoog) ? '' : $_get_container->flagoog,
                            "flagOog" => "",
                            "gateInDate" => "",
                            "gateOutDate" => "",
                            "startDate" => $fetchStuffing->tglstart,
                            "endDate" => $fetchStuffing->tglend,
                            "containerDeliveryDate" => $v->tglpaythru,
                            "containerLoadingDate" => "",
                            "containerDischargeDate" => "",
                            "disabled" => "Y"
                        )
                    );
                }

                $strContList = implode(", ", $containerList);
                $detailPranotaList = array();
                foreach ($fetchNotaStuffing as $k => $v) {
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
                            "containerSize" => $fetchContainerStuffing[0]['SIZE_'],
                            "containerType" => $fetchContainerStuffing[0]['TYPE_'],
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
                        )
                    );
                }
            } else {
                $notes = "Payment Cash - " . $jenis . " - STUFFING BUKAN DARI TPK";
                $response_uster_save = array(
                    'code' => "0",
                    'msg' => "Asal Stuffing Bukan Dari TPK"
                );
                insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

                return json_encode($response_uster_save);
            }
        } elseif ($jenis == 'DELIVERY') {
            $fetchDelivery = DB::connection('uster')->selectOne($queryDelivery);

            //IF DELIVERY KE TPK
            if ($fetchDelivery->delivery_ke == 'TPK') {

                // UPDATE BY CHOSSY PRATAMA
                $queryContainerDelivery =
                    "SELECT cd.*, mc.*, TO_CHAR(cd.START_STACK,'YYYY-MM-DD HH24:MI:SS') AWAL_PENUMPUKAN, TO_CHAR(cd.TGL_DELIVERY,'YYYY-MM-DD HH24:MI:SS') AKHIR_PENUMPUKAN FROM CONTAINER_DELIVERY cd JOIN MASTER_CONTAINER mc ON cd.NO_CONTAINER = mc.NO_CONTAINER WHERE cd.NO_REQUEST = '$id_req'";
                // END UPDATE
                $fetchContainerDelivery = DB::connection('uster')->select($queryContainerDelivery);
                $queryNotaDelivery =
                    "SELECT nd.*, ndd.*, (SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) STATUS, (SELECT SIZE_ FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) SIZE_, (SELECT TYPE_ FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) TYPE_, TO_CHAR(ndd.START_STACK,'YYYY-MM-DD HH24:MI:SS') AWAL_PENUMPUKAN, TO_CHAR(ndd.END_STACK,'YYYY-MM-DD HH24:MI:SS') AKHIR_PENUMPUKAN FROM NOTA_DELIVERY nd
            JOIN NOTA_DELIVERY_D ndd ON
            ndd.ID_NOTA = nd.NO_NOTA WHERE nd.NO_REQUEST = '$id_req'";
                $fetchNotaDelivery = DB::connection('uster')->select($queryNotaDelivery);
                $queryGetAdmin =
                    "SELECT TARIF FROM NOTA_DELIVERY nd
            JOIN NOTA_DELIVERY_D ndd ON
            ndd.ID_NOTA = nd.NO_NOTA WHERE nd.NO_REQUEST = '$id_req' AND ndd.ID_ISO = 'ADM' ";
                $adminComponent = DB::connection('uster')->selectOne($queryGetAdmin);

                $get_vessel = getVessel($fetchDelivery->nm_kapal, $fetchDelivery->voyage, $fetchDelivery->voyage_in, $fetchDelivery->voyage_out);

                // $get_container_list = getContainer(NULL, $fetchDelivery->kd_kapal, $fetchDelivery->voyage_in, $fetchDelivery->voyage_out, $fetchDelivery->voyage);

                $get_iso_code = getIsoCode();

                if (empty($get_iso_code)) {
                    $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                    $response_uster_save = array(
                        'code' => "0",
                        'msg' => "Gagal mengambil Iso Code ke Praya"
                    );
                    insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

                    return json_encode($response_uster_save);
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
                $vesselId = $fetchDelivery->kd_kapal; //
                $vesselName = $fetchDelivery->nm_kapal; //
                $voyage = empty($fetchDelivery->voyage) ? '' : $fetchDelivery->voyage; //
                $voyageIn = empty($fetchDelivery->voyage_in) ? '' : $fetchDelivery->voyage_in; //
                $voyageOut = empty($fetchDelivery->voyage_out) ? '' : $fetchDelivery->voyage_out; //
                $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut; //
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
                $dischargeDate = $get_vessel['discharge_date'];
                $shippingLineName = $fetchDelivery->nm_agen; //
                $customerCode = $fetchDelivery->kd_pelanggan; //
                $customerCodeOwner = '';
                $customerName = $fetchDelivery->emkl; //KD_EMKL
                $customerAddress = $fetchDelivery->alamat; //ALAMAT EMKL
                $npwp = $fetchDelivery->npwp; //
                $blNumber = '';
                $bookingNo = $fetchDelivery->no_booking; //
                $deliveryDate = '';
                $doNumber = $fetchDelivery->no_booking;  //
                // $doDate = '';
                $tradeType = $fetchDelivery->di == 'D' ? 'I' : 'O';
                $customsDocType = "";
                $customsDocNo = "";
                $customsDocDate = "";
                if ((int)$fetchDelivery->total_tagihan > 5000000) {
                    $amount = (int)$fetchDelivery->total_tagihan + 10000;
                } else {
                    (int)$amount = $fetchDelivery->total_tagihan;
                }
                if ($adminComponent) {
                    $administration = $adminComponent->tarif;
                }
                if (empty($fetchDelivery->ppn)) {
                    $ppn =  'N';
                } else {
                    $ppn = 'Y';
                };
                $amountPpn  = (int)$fetchDelivery->ppn; //
                $amountDpp = (int)$fetchDelivery->tagihan; //
                if ($fetchDelivery->tagihan > 5000000) {
                    $amountMaterai = 10000;
                } else {
                    $amountMaterai = 0;
                } //
                $approvalDate = empty($fetchDelivery->tglapprove) ? '' : $fetchDelivery->tglapprove;
                $status = 'PAID';
                $changeDate = $fetchDelivery->tglnota;
                $charge = 'Y';

                $detailList = array();
                $containerList = array();
                foreach ($fetchContainerDelivery as $k => $v) {
                    $container_status = $v->status == 'FCL' ? 'FULL' : 'EMPTY';

                    $cont = $v->no_container;

                    $reslt = array();
                    foreach ($get_iso_code as $key => $value) {
                        if (strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_)) {
                            array_push($reslt, $value);
                        }
                    }

                    $array_iso_code = array_values($reslt);
                    $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"]);

                    array_push($containerList, $v->no_container);
                    array_push(
                        $detailList,
                        array(
                            "detailDescription" => "CONTAINER",
                            "containerNo" => $v->no_container,
                            "containerSize" => $v->size_,
                            "containerType" => $v->type_,
                            "containerStatus" => $container_status,
                            "containerHeight" => "8.5", //hardcode
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
                            "commodityCode" => trim($v->komoditi, " "),
                            "commodityName" => trim($v->komoditi, " "),
                            "carrierCode" => $fetchDelivery->kd_agen,
                            "carrierName" => $fetchDelivery->nm_agen,
                            "isoCode" => $new_iso,
                            "plugInDate" => "",
                            "plugOutDate" => "",
                            "ei" => "E",
                            "dischLoad" => "",
                            "flagOog" => "N",
                            // UPDATE BY CHOSSY PRATAMA
                            "gateInDate" => $v->awal_penumpukan,
                            "gateOutDate" => $v->akhir_penumpukan,
                            "startDate" => $v->awal_penumpukan,
                            "endDate" => $v->akhir_penumpukan,
                            // END UPDATE
                            "containerDeliveryDate" => "",
                            "containerLoadingDate" => "",
                            "containerDischargeDate" => "",
                        )
                    );
                }

                $strContList = implode(", ", $containerList);
                $detailPranotaList = array();
                foreach ($fetchNotaDelivery as $k => $v) {
                    // Menghilangkan nota materai
                    if ($v->keterangan == "MATERAI") {
                        continue;
                    }
                    // Pemisahan Container Stack yg muncul di container listnya (edited by Chossy PIP (11962624) - Tonus)
                    if ($v->start_stack) {
                        $newContainerList = array();
                        foreach ($fetchContainerDelivery as $kContainer => $vContainer) {
                            if ($vContainer->start_stack == $v->start_stack && $vContainer->tgl_delivery == $v->end_stack && $vContainer->status == $v->status && $vContainer->size_ == $v->size_ && $vContainer->type_ == $v->type_) {
                                array_push($newContainerList, $vContainer->no_container);
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
                            "quantity" => '1',
                            "tarif" => $v->tarif,
                            "basicTarif" => $v->tarif,
                            "containerList" => $newStrContList,
                            "containerSize" => $size,
                            "containerType" => $type,
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
                        )
                    );
                }
            } else {
                $notes = "Payment Cash - " . $jenis . " - DELIVERY BUKAN KE TPK";
                $response_uster_save = array(
                    'code' => "0",
                    'msg' => "Tujuan Delivery bukan menuju TPK"
                );
                insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);
                return json_encode($response_uster_save);
            }
        } elseif ($jenis == 'BATAL_MUAT') {
            if ($charge == "N") {
                $fetchExDelivery = DB::connection('uster')->selectOne($queryDelivery);
                if ($fetchExDelivery->delivery_ke == 'TPK') {
                    $get_vessel = getVessel($payloadBatalMuat->vesselname, $payloadBatalMuat->voyage, $payloadBatalMuat->voyagein, $payloadBatalMuat->voyageout);
                    $get_iso_code = getIsoCode();
                    if (empty($get_iso_code)) {
                        $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                        $response_uster_save = array(
                            'code' => "0",
                            'msg' => "Gagal mengambil Iso Code ke Praya"
                        );
                        insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

                        return json_encode($response_uster_save);
                    }
                    $pelabuhan_asal = $payloadBatalMuat->pelabuhan_asal;
                    $pelabuhan_tujuan = $payloadBatalMuat->pelabuhan_tujuan;

                    $idRequest = $id_req;
                    $trxNumber = "";
                    $paymentDate = "";
                    $invoiceNumber = ""; //NO CHARGE KOSONG
                    $requestType = 'LOADING CANCEL - BEFORE GATEIN';
                    $parentRequestId = "";
                    $parentRequestType = 'LOADING CANCEL - BEFORE GATEIN';
                    $serviceCode = 'LCB';
                    $jenisBM = "alih_kapal";
                    $vesselId = $payloadBatalMuat["vesselId"];
                    $vesselName = $payloadBatalMuat["vesselName"];
                    $voyage = $payloadBatalMuat->voyage; //
                    $voyageIn = $payloadBatalMuat->voyagein; //
                    $voyageOut = $payloadBatalMuat->voyageout; //
                    $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut; //
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
                    $shippingLineName = $payloadBatalMuat->nm_agen; //
                    $customerCode = $fetchExDelivery->kd_pelanggan; //
                    $customerCodeOwner = '';
                    $customerName = $fetchExDelivery->emkl; //
                    $customerAddress = $fetchExDelivery->alamat; //
                    $npwp = $fetchExDelivery->npwp; //
                    $blNumber = "";
                    $bookingNo = $fetchExDelivery->no_booking;
                    $deliveryDate = '';
                    $doNumber = "";
                    // $doDate = '';
                    $tradeType = $fetchExDelivery->di; //Value : I / O
                    $customsDocType = "";
                    $customsDocNo = "";
                    $customsDocDate = "";
                    $amount = 0;
                    $administration = 0;
                    if (empty($fetchExDelivery->ppn)) {
                        $ppn =  'N';
                    } else {
                        $ppn = 'Y';
                    };
                    $amountPpn  = 0;
                    $amountDpp = 0;
                    $amountMaterai = 0;
                    $approvalDate = empty($fetchExDelivery->tglapprove) ? '' : $fetchExDelivery->tglapprove;
                    $status = 'PAID';
                    $changeDate = $fetchExDelivery->tglnota;
                    $charge = 'N';

                    $detailList = array();
                    $containerList = $payloadBatalMuat->cont_list;
                    foreach ($payloadBatalMuat->cont_list as $no_cont) {
                        $queryContainerExDelivery =
                            "SELECT cd.NO_CONTAINER, cd.KOMODITI, mc.SIZE_, mc.TYPE_, mc.NO_BOOKING, vpc.KD_KAPAL, vpc.VOYAGE, vpc.VOYAGE_IN, vpc.VOYAGE_OUT FROM CONTAINER_DELIVERY cd JOIN MASTER_CONTAINER mc ON cd.NO_CONTAINER = mc.NO_CONTAINER JOIN V_PKK_CONT vpc ON mc.NO_BOOKING = vpc.NO_BOOKING WHERE cd.NO_CONTAINER = '$no_cont'";
                        $fetchContainerExDelivery = DB::connection('uster')->selectOne($queryContainerExDelivery);

                        $get_container_list = getContainer($no_cont, $fetchContainerExDelivery->kd_kapal, $fetchContainerExDelivery->voyage_in, $fetchContainerExDelivery->voyage_out, $fetchContainerExDelivery->voyage, NULL, NULL);
                        // $array_iso_code = array_values(array_filter($get_iso_code, function ($value) use ($v) {
                        //     return strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_);
                        // }));

                        $reslt = array();
                        foreach ($get_iso_code as $key => $value) {
                            if (strtoupper($value['type']) == strtoupper($fetchContainerExDelivery->type_) && strtoupper($value['size']) == strtoupper($fetchContainerExDelivery->size_)) {
                                array_push($reslt, $value);
                            }
                        }

                        $array_iso_code = array_values($reslt);
                        $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"]);

                        // echo json_encode($array_iso_code);
                        array_push(
                            $detailList,
                            array(
                                "detailDescription" => "CONTAINER",
                                "containerNo" => $fetchContainerExDelivery->no_container,
                                "containerSize" => $fetchContainerExDelivery->size_,
                                "containerType" => $fetchContainerExDelivery->type_,
                                "containerStatus" => "FULL",
                                "containerHeight" => "8.5",
                                "hz" => empty($fetchContainerExDelivery->hz) ? (empty($get_container_list[0]['hz']) ? 'N' : $get_container_list[0]['hz']) : "N",
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
                                "commodityCode" => trim($fetchContainerExDelivery->komoditi, " "),
                                "commodityName" => trim($fetchContainerExDelivery->komoditi, " "),
                                "carrierCode" => $payloadBatalMuat->kd_agen,
                                "carrierName" => $payloadBatalMuat->nm_agen,
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
                            )
                        );
                    }
                } else {
                    $notes = "Payment Cash - " . $jenis . " - BUKAN EX KEGIATAN REPO";
                    $response_uster_save = array(
                        'code' => "0",
                        'msg' => "Nota Batal Muat bukan Ex Kegiatan Repo (Status Gate 2)"
                    );
                    insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

                    return json_encode($response_uster_save);
                }
            } else {
                $queryBatalMuat = "SELECT
                    --	rbm.*,
                    rbm.NO_REQUEST, --REQUIRED
                    rbm.KD_EMKL, --UNTUK LOG
                    rbm.JENIS_BM, --hanya yg alih_kapal
                    rbm.KAPAL_TUJU, --NO_BOOKING
                    rbm.STATUS_GATE, --IF STATUS GATE 2
                    rbm.NO_REQ_BARU,
                    rbm.O_VESSEL,
                    rbm.BIAYA,
                    -- rbm.O_VOYIN,
                    -- rbm.O_VOYOUT,
                    rbm.DI,
                    -- nbm.*,
                    nbm.NO_NOTA,
                    nbm.NO_FAKTUR_MTI,
                    nbm.EMKL,
                    nbm.ALAMAT,
                    nbm.NPWP,
                    nbm.TAGIHAN,
                    nbm.TOTAL_TAGIHAN,
                    nbm.STATUS,
                    nbm.PPN,
                    TO_CHAR(nbm.TGL_NOTA ,'YYYY-MM-DD HH24:MI:SS') TGLNOTA,
                    vpc.VOYAGE,
                    vpc.VOYAGE_IN,
                    vpc.VOYAGE_OUT,
                    vpc.PELABUHAN_ASAL,
                    vpc.PELABUHAN_TUJUAN,
                    vpc.NM_AGEN,
                    vpc.KD_AGEN,
                    vpc.NM_KAPAL,
                    vpc.KD_KAPAL,
                    vmp.NO_ACCOUNT_PBM KD_PELANGGAN,
                    cbm.NO_REQ_BATAL
                    FROM
                    REQUEST_BATAL_MUAT rbm
                    LEFT JOIN V_PKK_CONT vpc ON
                    rbm.KAPAL_TUJU = vpc.NO_BOOKING
                    LEFT JOIN NOTA_BATAL_MUAT nbm ON
                    nbm.NO_REQUEST = rbm.NO_REQUEST
                    JOIN V_MST_PBM vmp ON
                        vmp.KD_PBM = rbm.KD_EMKL
                    JOIN CONTAINER_BATAL_MUAT cbm ON
                    cbm.NO_REQUEST = rbm.NO_REQUEST
                    WHERE rbm.NO_REQUEST = '$id_req'";

                $fetchBatalMuat = DB::connection('uster')->selectOne($queryBatalMuat);

                if ($fetchBatalMuat->status_gate == '2' && $fetchBatalMuat->jenis_bm == 'alih_kapal') {
                    $queryContainerBatalMuat =
                        "SELECT * FROM CONTAINER_BATAL_MUAT cbm JOIN MASTER_CONTAINER mc ON cbm.NO_CONTAINER = mc.NO_CONTAINER WHERE cbm.NO_REQUEST = '$id_req'";
                    $fetchContainerBatalMuat = DB::connection('uster')->select($queryContainerBatalMuat);
                    $queryNotaBatalMuat =
                        "SELECT nbm.*, nbmd.*, TO_CHAR(nbm.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA FROM NOTA_BATAL_MUAT nbm JOIN NOTA_BATAL_MUAT_D nbmd ON nbm.NO_NOTA = nbmd.ID_NOTA WHERE nbm.NO_REQUEST = '$id_req'";
                    $fetchNotaBatalMuat = DB::connection('uster')->select($queryNotaBatalMuat);
                    $queryGetAdmin =
                        "SELECT TARIF FROM NOTA_BATAL_MUAT nbm JOIN NOTA_BATAL_MUAT_D nbmd ON nbm.NO_NOTA = nbmd.ID_NOTA WHERE nbm.NO_REQUEST = '$id_req' AND nbmd.ID_ISO = 'ADM' ";
                    $adminComponent = DB::connection('uster')->selectOne($queryGetAdmin);

                    $get_vessel = getVessel($fetchBatalMuat->nm_kapal, $fetchBatalMuat->voyage, $fetchBatalMuat->voyage_in, $fetchBatalMuat->voyage_out);

                    $get_container_list = getContainer(NULL, $fetchBatalMuat->kd_kapal, $fetchBatalMuat->voyage_in, $fetchBatalMuat->voyage_out, $fetchBatalMuat->voyage, "E", "LCB");

                    $get_iso_code = getIsoCode();

                    if (empty($get_iso_code)) {
                        $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
                        $response_uster_save = array(
                            'code' => "0",
                            'msg' => "Gagal mengambil Iso Code ke Praya"
                        );
                        insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

                        return json_encode($response_uster_save);
                    }

                    // echo json_encode($get_vessel . ' <<getvessel');
                    // echo json_encode($get_container_list . ' <<getcontainerlist');
                    // die();

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
                    $vesselId = $fetchBatalMuat->kd_kapal; //
                    $vesselName = $fetchBatalMuat->nm_kapal; //
                    $voyage = empty($fetchBatalMuat->voyage) ? '' : $fetchBatalMuat->voyage; //
                    $voyageIn = empty($fetchBatalMuat->voyage_in) ? '' : $fetchBatalMuat->voyage_in; //
                    $voyageOut = empty($fetchBatalMuat->voyage_out) ? '' : $fetchBatalMuat->voyage_out; //
                    $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut; //
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
                    $shippingLineName = $fetchBatalMuat->nm_agen; //
                    $customerCode = $fetchBatalMuat->kd_pelanggan; //
                    $customerCodeOwner = '';
                    $customerName = $fetchBatalMuat->emkl; //
                    $customerAddress = $fetchBatalMuat->alamat; //
                    $npwp = $fetchBatalMuat->npwp; //
                    $blNumber = "";
                    $bookingNo = $fetchBatalMuat->kapal_tuju;
                    $deliveryDate = '';
                    $doNumber = "";
                    // $doDate = '';
                    $tradeType = $fetchBatalMuat->di; //Value : I / O
                    $customsDocType = "";
                    $customsDocNo = "";
                    $customsDocDate = "";
                    if ((int)$fetchBatalMuat->total_tagihan > 5000000) {
                        $amount = (int)$fetchBatalMuat->total_tagihan + 10000;
                    } else {
                        (int)$amount = $fetchBatalMuat->total_tagihan;
                    }
                    if ($adminComponent) {
                        $administration = $adminComponent->tarif;
                    }
                    if (empty($fetchBatalMuat->ppn)) {
                        $ppn =  'N';
                    } else {
                        $ppn = 'Y';
                    };
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

                    $detailList = array();
                    $containerList = array();

                    foreach ($fetchContainerBatalMuat as $k => $v) {
                        foreach ($get_container_list as $k_container => $v_container) {
                            if ($v_container->containerno  == $v->no_container) {
                                $_get_container = $v_container;
                                break;
                            }
                        }
                        // $array_iso_code = array_values(array_filter($get_iso_code, function ($value) use ($v) {
                        //     return strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_);
                        // }));

                        $reslt = array();
                        foreach ($get_iso_code as $key => $value) {
                            if (strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_)) {
                                array_push($reslt, $value);
                            }
                        }

                        $array_iso_code = array_values($reslt);
                        $new_iso = mapNewIsoCode($array_iso_code[0]["isoCode"]);

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
                            )
                        );
                    }

                    $strContList = implode(", ", $containerList);
                    $detailPranotaList = array();
                    foreach ($fetchNotaBatalMuat as $k => $v) {

                        array_push(
                            $detailPranotaList,
                            array(
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
                                "containerSize" => $fetchContainerBatalMuat[0]['SIZE_'],
                                "containerType" => $fetchContainerBatalMuat[0]['TYPE_'],
                                "containerStatus" => "",
                                "containerHeight" => "8.5",
                                "hz" => empty($v->hz) ? "N" : $v->hz,
                                "ei" => "I",
                                "equipment" => "",
                                "strStartDate" => "",
                                "strEndDate" => "",
                                "days" => "1", //REQUEST DATE - REQUEST DATE
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
                            )
                        );
                    }
                } else {
                    $notes = "Payment Cash - " . $jenis . " - BUKAN EX KEGIATAN REPO";
                    $response_uster_save = array(
                        'code' => "0",
                        'msg' => "Nota Batal Muat bukan Ex Kegiatan Repo (Status Gate 2)"
                    );
                    insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

                    return json_encode($response_uster_save);
                }
            }
        }

        $payload_header = array(
            "PNK_REQUEST_ID" => $id_req,
            "PNK_NO_PROFORMA" => "",
            "PNK_CONTAINER_LIST" => $strContList,
            "PNK_JENIS_SERVICE" => $jenis,
            "PNK_JENIS_BATAL_MUAT" => $jenisBM,
            "PNK_PAYMENT_VIA" => $payment_via,
            "EBPP_CREATED_DATE" => $_POST["EBPP_CREATED_DATE"],
            "idRequest" => $idRequest,
            "billerId" => "00009",
            "trxNumber" => $trxNumber,
            "paymentDate" => $paymentDate,
            "invoiceNumber" => $invoiceNumber,
            "orgId" => (string)env('PRAYA_ITPK_PNK_ORG_ID'),
            "orgCode" => env('PRAYA_ITPK_PNK_ORG_CODE'),
            "terminalId" => (string)env('PRAYA_ITPK_PNK_TERMINAL_ID'),
            "terminalCode" => env('PRAYA_ITPK_PNK_TERMINAL_CODE'),
            "branchId" => (string)env('PRAYA_ITPK_PNK_BRANCH_ID'),
            "branchCode" => (string)env('PRAYA_ITPK_PNK_BRANCH_CODE'),
            "areaTerminal" => (string)env('PRAYA_ITPK_PNK_AREA_CODE'),
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
            "fpod" => $fpod,
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
            // "doDate" => $doDate,
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

        // var_dump($response_uster_save);
        // echo "<<uster_save";

        $first_char_http_code = substr(strval($response_uster_save['httpCode']), 0, 1);

        if ($first_char_http_code == 5 || $first_char_http_code == 1) {
            echo "0";
            $decodedRes = json_decode($response_uster_save["response"]["msg"]) ? json_decode($response_uster_save["response"]["msg"]) : $response_uster_save["response"]["msg"];
            $defaultRes = "Service is Unavailable, please try again (HTTP Error Code : " . $response_uster_save["httpCode"] . ")";
            $response_uster_save_logging = array(
                "code" => "0",
                "msg" => $defaultRes,
                "response" => $decodedRes
            );
            echo "3";
            insertPrayaServiceLog($url_uster_save, $payload_header, $response_uster_save_logging, $notes);
            echo "2";

            return json_encode($response_uster_save_logging);
        }

        $response_uster_save_decode = json_decode($response_uster_save['response'], true);

        $response_uster_save_logging = $response_uster_save_decode["code"] == 0 ? array(
            "code" => $response_uster_save_decode['code'],
            "msg" => $response_uster_save_decode['msg']
        ) : $response_uster_save_decode;

        if (!empty($idRequest) && substr($idRequest, 0, 3) == "STP") {
            $payload_stp_logging = array(
                "PNK_REQUEST_ID" => $id_req,
                "PNK_NO_PROFORMA" => $_POST["NO_PROFORMA"],
                "PNK_CONTAINER_LIST" => $strContList,
                "PNK_JENIS_SERVICE" => $jenis,
                "PNK_JENIS_BATAL_MUAT" => $jenisBM,
                "PNK_PAYMENT_VIA" => $payment_via,
                "EBPP_CREATED_DATE" => $_POST["EBPP_CREATED_DATE"],
                "detailList" => $containerListLog
            );
            $notes_stp_logging = "STP - " . $idRequest . " - CONTAINER LOGGING";

            // LOGGING FOR PAYTHRU PERP_STRIP
            insertPrayaServiceLog($url_uster_save, $payload_stp_logging, $response_uster_save_logging, $notes_stp_logging);
        }

        insertPrayaServiceLog($url_uster_save, $payload_header, $response_uster_save_logging, $notes);

        if ($response_uster_save['response']['code'] == 0) {
            return json_encode($response_uster_save_logging);
        } else {
            return $response_uster_save['response'];
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
        CURLOPT_CONNECTTIMEOUT => 15,      // timeout on connect
        CURLOPT_TIMEOUT        => 15,      // timeout on response
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
    $content = curl_exec($ch);
    $err     = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    // $header['errno']   = $err;
    // $header['errmsg']  = $errmsg;

    //change errmsg here to errno
    if ($errmsg) {
        echo "CURL:" . $errmsg . "<BR>";
    }
    return $content;
}
