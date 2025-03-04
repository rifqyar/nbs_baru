<?php

namespace App\Services\Request\Stripping;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PDO;

class PerencanaanStripping
{
    function getData(Request $request)
    {
        $no_req = $request->no_request;
        $from = $request->tgl_awal;
        $to = $request->tgl_akhir;
        $noReqWhere = '';
        $dateWhere = '';

        if ($request->cari == 'true') {
            if ($request->no_request == null && ($from != null && $to != null)) {
                $dateWhere = "WHERE TGL_REQUEST BETWEEN TO_DATE ( '$from',
                 'YYYY-MM-DD ')
                                AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')";
            } else if ($no_req != null && ($from == null && $to == null)) {
                $noReqWhere = "WHERE NO_REQUEST = '$no_req'";
            } else if ($no_req != null && ($from != null && $to != null)) {
                $noReqWhere = "WHERE NO_REQUEST = '$no_req'";
                $dateWhere = "AND TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')";
            }

            $query_list        = "SELECT * FROM (
                                        SELECT
                                            prs.TGL_REQUEST
                                            , prs.APPROVE
                                            , prs.NO_REQUEST
                                            , prs.TYPE_STRIPPING
                                            , prs.NO_DO
                                            , prs.NO_BL
                                            , NVL (rs.NO_REQUEST, 'blm di approve') NO_REQUEST_APP
                                            , NVL (rs.CLOSING, 'Blm di Approve') STATUS_REQ
                                            , rs.NOTA, rs.KOREKSI, ns.LUNAS
                                            , prs.CLOSING
                                            , emkl.NM_PBM AS NAMA_PEMILIK
                                    FROM PLAN_REQUEST_STRIPPING prs
                                        LEFT JOIN REQUEST_STRIPPING rs
                                            ON rs.NO_REQUEST =
                                                REPLACE(prs.NO_REQUEST,'P', 'S')
                                            AND rs.PERP_DARI IS NULL
                                        LEFT JOIN NOTA_STRIPPING ns
                                            ON rs.NO_REQUEST = ns.NO_REQUEST
                                        LEFT JOIN PLAN_CONTAINER_STRIPPING
                                            ON prs.NO_REQUEST = PLAN_CONTAINER_STRIPPING.NO_REQUEST
                                        LEFT JOIN V_MST_PBM emkl
                                            ON prs.KD_CONSIGNEE = emkl.KD_PBM
                                            AND emkl.KD_CABANG = '05'
                                    GROUP BY prs.APPROVE,prs.TYPE_STRIPPING, prs.NO_REQUEST,prs.NO_DO, prs.NO_BL,
                                            NVL (rs.NO_REQUEST, 'blm di approve'),
                                            rs.NOTA, rs.KOREKSI, ns.LUNAS, prs.CLOSING
                                            emkl.NM_PBM, prs.TGL_REQUEST,prs.KD_CONSIGNEE,rs.CLOSING
                                    ORDER BY NO_REQUEST DESC
                                    ) $noReqWhere $dateWhere";
        } else {
            $query_list        = "SELECT *
						        FROM (SELECT DISTINCT
                                    prs.TGL_REQUEST,
                                    prs.APPROVE,
                                    prs.NO_REQUEST,
                                    prs.TYPE_STRIPPING,
                                    prs.NO_DO,
                                    prs.NO_BL,
                                    NVL (rs.NO_REQUEST, 'blm di approve') NO_REQUEST_APP,
                                    NVL (rs.CLOSING, 'Blm di Approve') STATUS_REQ,
                                    rs.NOTA,
                                    rs.KOREKSI,
                                    ns.LUNAS,
                                    prs.CLOSING,
                                    emkl.NM_PBM AS NAMA_PEMILIK
                                FROM PLAN_REQUEST_STRIPPING prs
                                    INNER JOIN V_MST_PBM emkl
                                        ON prs.KD_CONSIGNEE = emkl.KD_PBM
                                        AND emkl.KD_CABANG = '05'
                                    LEFT JOIN REQUEST_STRIPPING rs
                                        ON rs.NO_REQUEST =
                                            prs.NO_REQUEST_APP_STRIPPING
                                        AND rs.PERP_DARI IS NULL
                                    LEFT JOIN NOTA_STRIPPING ns
                                        ON rs.NO_REQUEST = ns.NO_REQUEST
                                WHERE prs.TGL_REQUEST BETWEEN SYSDATE - INTERVAL '15' DAY AND LAST_DAY (SYSDATE)
                                ORDER BY prs.TGL_REQUEST DESC)";
        }

        $query = '(' . $query_list . ') data_stripping';
        $data = DB::connection('uster')->table(DB::raw($query))->select()->get();

        return $data;
    }

    function getTotalData()
    {
        $jumlah_req = "SELECT COUNT(*) JUMLAH FROM (SELECT PLAN_REQUEST_STRIPPING.NO_REQUEST, COUNT(PLAN_CONTAINER_STRIPPING.NO_CONTAINER) BOX FROM PLAN_REQUEST_STRIPPING JOIN REQUEST_STRIPPING
                ON REPLACE(PLAN_REQUEST_STRIPPING.NO_REQUEST,'P','S') = REQUEST_STRIPPING.NO_REQUEST
                LEFT JOIN PLAN_CONTAINER_STRIPPING ON PLAN_REQUEST_STRIPPING.NO_REQUEST = PLAN_CONTAINER_STRIPPING.NO_REQUEST
                WHERE REQUEST_STRIPPING.CLOSING IS NULL AND REQUEST_STRIPPING.TGL_REQUEST BETWEEN TRUNC(ADD_MONTHS(SYSDATE,-1),'MM') AND LAST_DAY(SYSDATE)
                GROUP BY PLAN_REQUEST_STRIPPING.NO_REQUEST) Q
                WHERE Q.BOX <> 0";
        $jumlah_req = DB::connection('uster')->selectOne($jumlah_req);

        $total_req = "SELECT COUNT(NO_REQUEST) TOTAL FROM REQUEST_STRIPPING WHERE TO_CHAR(TGL_REQUEST,'DD/MM/YYYY') = TO_CHAR(SYSDATE,'DD/MM/YYYY') AND CLOSING = 'CLOSED'";
        $total_req = DB::connection('uster')->selectOne($total_req);

        $total_cont = "SELECT COUNT(NO_CONTAINER) TOTAL FROM REQUEST_STRIPPING, CONTAINER_STRIPPING
					WHERE REQUEST_STRIPPING.NO_REQUEST = CONTAINER_STRIPPING.NO_REQUEST AND
					TO_CHAR(TGL_REQUEST,'DD/MM/YYYY') = TO_CHAR(SYSDATE,'DD/MM/YYYY') AND CLOSING = 'CLOSED' AND CONTAINER_STRIPPING.TGL_APPROVE IS NOT NULL";
        $total_cont = DB::connection('uster')->selectOne($total_cont);

        return [
            'jumlah_req' => $jumlah_req->jumlah,
            'total_req' => $total_req->total,
            'total_cont' => $total_cont->total
        ];
    }

    function getOverviewData($noReq)
    {
        $query = "SELECT NO_REQUEST_RECEIVING
                FROM PLAN_REQUEST_STRIPPING
                WHERE NO_REQUEST = '$noReq'";

        $row_req2    = DB::connection('uster')->selectOne($query);
        $no_req_rec    = $row_req2->no_request_receiving;

        $no_req2    = substr($no_req_rec, 3);
        $no_req2    = "UREC" . $no_req2;

        $query_request    = "SELECT PLAN_REQUEST_STRIPPING.*,
					       emkl.NM_PBM AS NAMA_PEMILIK,
					       emkl.NM_PBM AS NAMA_PENUMPUK
					  FROM PLAN_REQUEST_STRIPPING
					       INNER JOIN V_MST_PBM emkl
					          ON PLAN_REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM
					          AND emkl.KD_CABANG = '05'
					 WHERE PLAN_REQUEST_STRIPPING.NO_REQUEST = '$noReq'";

        $rowList = DB::connection('uster')->selectOne($query_request);
        return [$rowList, $no_req2];
    }

    function getViewData($noReq)
    {
        $no_req    = $noReq;

        $query = "SELECT NO_REQUEST_RECEIVING
					FROM PLAN_REQUEST_STRIPPING
					WHERE NO_REQUEST = '$no_req'";
        $row_req2 = DB::connection('uster')->selectOne($query);
        $no_req_rec    = !empty($row_req2) ? $row_req2->no_request_receiving : '';

        $no_req2    = substr($no_req_rec, 3);
        $no_req2    = "UREC" . $no_req2;

        $query_list = "SELECT DISTINCT PLAN_CONTAINER_STRIPPING.*,
								  PLAN_CONTAINER_STRIPPING.COMMODITY COMMO
							   FROM PLAN_CONTAINER_STRIPPING
							   WHERE PLAN_CONTAINER_STRIPPING.NO_REQUEST = '$no_req'";
        $row_list        = DB::connection('uster')->select($query_list);
        $jum = count($row_list);

        $query_request = "SELECT REQUEST_STRIPPING.*,
                              emkl.NM_PBM AS NAMA_PEMILIK,
                              emkl.NM_PBM AS NAMA_PENUMPUK,
                              PLAN_REQUEST_STRIPPING.NO_SPPB,
                              PLAN_REQUEST_STRIPPING.TGL_SPPB,
                              PLAN_REQUEST_STRIPPING.APPROVE
                            FROM REQUEST_STRIPPING
                                INNER JOIN PLAN_REQUEST_STRIPPING
                                    ON REQUEST_STRIPPING.NO_REQUEST_PLAN = PLAN_REQUEST_STRIPPING.NO_REQUEST
                                INNER JOIN V_MST_PBM emkl
                                    ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM
                                    AND emkl.KD_CABANG = '05'
                            WHERE REQUEST_STRIPPING.NO_REQUEST_PLAN = '$no_req'";

        $row_request    = DB::connection('uster')->selectOne($query_request);

        $result_all = DB::connection('uster')->selectOne("SELECT TO_CHAR (TGL_MULAI, 'yyyy-mm-dd') TGL_MULAI,
									 TO_CHAR (TGL_SELESAI, 'yyyy-mm-dd') TGL_SELESAI
								FROM PLAN_CONTAINER_STRIPPING
								WHERE NO_REQUEST = '$no_req'");

        return response()->json([
            'jum' => $jum,
            'row_list' => $row_list,
            'row_request' => $row_request,
            'row_all' => $result_all,
            'no_req2' => $no_req2
        ]);
    }

    function contList($noReq, $type)
    {
        $query_list        = "SELECT DISTINCT PLAN_CONTAINER_STRIPPING.*, PLAN_CONTAINER_STRIPPING.COMMODITY COMMO, ukuran kd_size, type KD_TYPE, PLAN_CONTAINER_STRIPPING.REMARK REMARK
                           FROM PLAN_CONTAINER_STRIPPING
                           WHERE PLAN_CONTAINER_STRIPPING.NO_REQUEST = '$noReq'";

        $rowList = DB::connection('uster')->select($query_list);

        $cek_save = "SELECT CLOSING FROM PLAN_REQUEST_STRIPPING WHERE NO_REQUEST = '$noReq'";
        $r_cek = DB::connection('uster')->selectOne($cek_save);
        $close = !empty($r_cek) ? $r_cek->closing : '';

        return [$rowList, $close];
    }

    function cek($noReqStrip, $noReqCont)
    {
        $no_req_strip = str_replace('P', 'S', $noReqStrip);
        $no_cont = $noReqCont;

        $query_list_cek        = "SELECT DISTINCT CONTAINER_STRIPPING.NO_REQUEST
                                    FROM CONTAINER_STRIPPING LEFT JOIN MASTER_CONTAINER M
                                    ON CONTAINER_STRIPPING.NO_CONTAINER = M.NO_CONTAINER
                                    WHERE CONTAINER_STRIPPING.NO_REQUEST = '$no_req_strip'
                                    AND CONTAINER_STRIPPING.NO_CONTAINER = '$no_cont'
                                    ";

        $row_list_cek = DB::connection('uster')->selectOne($query_list_cek);
        $cek = !empty($row_list_cek) ? $row_list_cek->no_request : null;
        return $cek;
    }

    function getPbm($term)
    {
        $term = strtoupper($term);
        $query  = "SELECT pbm.KD_PBM,pbm.NM_PBM,pbm.ALMT_PBM,pbm.NO_NPWP_PBM, pbm.NO_ACCOUNT_PBM FROM V_MST_PBM PBM
                    where pbm.KD_CABANG='05'
                        AND UPPER(pbm.NM_PBM) LIKE '%$term%'
                        AND PELANGGAN_AKTIF = '1'
                        AND pbm.ALMT_PBM IS NOT NULL";

        $data = DB::connection('uster')->select($query);
        return $data;
    }

    function getKapal($nama_kapal)
    {
        $query             = "SELECT
                        TML_CD,
                        VESSEL_CODE,
                        OPERATOR_NAME,
                        OPERATOR_ID,
                        VESSEL,
                        VOYAGE_IN,
                        VOYAGE_OUT,
                        ID_VSB_VOYAGE,
                        CALL_SIGN,
                        TO_CHAR (TO_DATE (ATD, 'YYYYMMDDHH24MISS'), 'DD-MM-YYYY HH24:Mi:SS') ATD,
                        TO_CHAR (TO_DATE (OPEN_STACK, 'YYYYMMDDHH24MISS'), 'DD-MM-YYYY HH24:Mi:SS') OPEN_STACK,
                        TO_CHAR (TO_DATE (CLOSSING_DOC, 'YYYYMMDDHH24MISS'), 'DD-MM-YYYY HH24:Mi:SS') CLOSING_TIME_DOC,
                        TO_CHAR (TO_DATE (ETA, 'YYYYMMDDHH24MISS'), 'DD-MM-YYYY HH24:Mi:SS') ETA,
                        TO_CHAR (TO_DATE (ETD, 'YYYYMMDDHH24MISS'), 'DD-MM-YYYY HH24:Mi:SS') ETD,
                        TO_CHAR (TO_DATE (ATA, 'YYYYMMDDHH24MISS'), 'DD-MM-YYYY HH24:Mi:SS') ATA,
                        ID_POL,
                        POL,
                        ID_POD,
                        POD,
                        CONTAINER_LIMIT,
                        TO_CHAR (TO_DATE (CLOSSING_TIME, 'YYYYMMDDHH24MISS'), 'DD-MM-YYYY HH24:Mi:SS') CLOSING_TIME,
                        VOYAGE
                    FROM
                        M_VSB_VOYAGE_PALAPA
                    WHERE
                        TML_CD = 'PNK'
                        -- AND TO_DATE(TO_CHAR(SYSDATE, 'YYYYMMDD'), 'YYYYMMDD') BETWEEN TO_DATE(TO_CHAR(TO_DATE(OPEN_STACK, 'YYYYMMDDHH24MISS'), 'YYYYMMDD'), 'YYYYMMDD') AND TO_DATE(TO_CHAR(TO_DATE(CLOSSING_TIME, 'YYYYMMDDHH24MISS'), 'YYYYMMDD'), 'YYYYMMDD')
                        AND (VESSEL LIKE '%$nama_kapal%'
                        OR VOYAGE_IN LIKE '%$nama_kapal%'
                        OR VOYAGE_OUT LIKE '%$nama_kapal%'
                        OR VOYAGE LIKE '%$nama_kapal%')
                    ORDER BY VESSEL, VOYAGE_IN DESC";

        $data = DB::connection('opus_repo')->select($query);
        return $data;
    }

    function getCont($request)
    {
        try {
            $payload = array(
                "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID'),
                "vesselId" => $request->vessel_code,
                "voyageIn" => $request->voyage_in,
                "voyageOut" => $request->voyage_out,
                "voyage" => $request->voyage,
                "portCode" => env('PRAYA_ITPK_PNK_PORT_CODE'),
                "ei" => "I",
                "containerNo" => $request->search,
                "serviceCode" => "DEL"
            );

            $response = sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/containerList", 'POST', getTokenPraya());
            $response = json_decode($response['response'], true);
            if ($response['code'] == 1 && !empty($response["data"])) {
                return $response['data'];
            } else {
                return [];
            }
        } catch (Exception $ex) {
            return false;
        }
    }

    function getKomoditi($search)
    {
        $query = "SELECT KD_COMMODITY, NM_COMMODITY from BILLING_NBS.MASTER_COMMODITY WHERE UPPER(NM_COMMODITY) LIKE '%$search%'";
        $data = DB::connection('uster')->select($query);
        return $data;
    }

    function getVoyage($search)
    {
        $query = "SELECT *
                    FROM (SELECT a.NAMA_VESSEL AS VESSEL,
                                b.VOYAGE AS VOYAGE,
                                b.NO_BOOKING AS NO_BOOKING
                            FROM MASTER_VESSEL a,
                                VOYAGE b
                            WHERE  a.NAMA_VESSEL LIKE '%$search%'
                                OR b.VOYAGE LIKE '%$search%'
                            AND a.KODE_VESSEL = b.KODE_VESSEL
                            ORDER BY NO_BOOKING DESC)
                            WHERE ROWNUM <= 3
                            ORDER BY ROWNUM ASC";
        $data = DB::connection('uster')->select($query);
        return $data;
    }

    function cekSaldo($idConsignee)
    {
        $idConsignee = base64_decode($idConsignee);
        $qcek_saldo = "SELECT B.NO_CONTAINER
					FROM REQUEST_STRIPPING A ,CONTAINER_STRIPPING B
					WHERE A.NO_REQUEST = B.NO_REQUEST
					AND A.KD_CONSIGNEE = '$idConsignee'
					AND B.NO_CONTAINER IS NOT NULL
					AND B.AKTIF = 'Y'";

        $saldo = DB::connection('uster')->select($qcek_saldo);

        if (count($saldo) > 100000) {
            $dataSaldo = $this->getSaldo($idConsignee);

            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'OK',
                'available' => false,
                'dataSaldo' => $dataSaldo
            ]);
        } else {
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'OK',
                'available' => true,
                'dataSaldo' => null
            ]);
        }
        return $saldo;
    }

    function getSaldo($idConsignee)
    {
        $qcek_saldo = "SELECT CONTAINER_STRIPPING.NO_CONTAINER, REQUEST_STRIPPING.KD_CONSIGNEE, V_MST_PBM.NM_PBM ,CONTAINER_STRIPPING.TGL_APPROVE,
                    CONTAINER_STRIPPING.TGL_REALISASI FROM REQUEST_STRIPPING JOIN CONTAINER_STRIPPING
                    ON REQUEST_STRIPPING.NO_REQUEST = CONTAINER_STRIPPING.NO_REQUEST
                    LEFT JOIN V_MST_PBM ON REQUEST_STRIPPING.KD_CONSIGNEE = V_MST_PBM.KD_PBM
                    WHERE CONTAINER_STRIPPING.TGL_APPROVE IS NOT NULL
                    AND CONTAINER_STRIPPING.TGL_REALISASI IS NULL
                    AND REQUEST_STRIPPING.KD_CONSIGNEE = '$idConsignee'";

        $result = DB::connection('uster')->select($qcek_saldo);
        $saldo = count($result);
        $consignee = $result[0]->nm_pbm;

        return [
            'consignee' => $consignee,
            'saldo' => $saldo,
            'kd_consignee' => base64_encode($idConsignee)
        ];
    }

    // function addRequestPraya($param)
    // {
    //     // DB::beginTransaction();
    //     // try {
    //     $db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = " . env('DB_HOST_USTER') . ")(PORT = 1521)))(CONNECT_DATA=(SID=" . env('DB_SERVICE_NAME_USTER') . ")))";
    //     $conn = oci_connect("uster", "uster", $db);
    //     $sql = " BEGIN pack_create_req_stripping.create_header_strip_praya (
    //                 :in_accpbm,
    //                 :in_pbm,
    //                 :in_personal,
    //                 :in_do,
    //                 :in_datesppb,
    //                 :in_nosppb,
    //                 :in_keterangan,
    //                 :in_user,
    //                 :in_di,
    //                 :in_vessel,
    //                 :in_voyin,
    //                 :in_voyout,
    //                 :in_idvsb,
    //                 :in_nobooking,
    //                 :in_callsign,
    //                 :in_bl,
    //                 :in_vessel_code,
    //                 :in_tanggal_jam_tiba,
    //                 :in_tanggal_jam_berangkat,
    //                 :in_operator_name,
    //                 :in_operator_id,
    //                 :in_pod,
    //                 :in_pol,
    //                 :in_voyage,
    //                 :out_noreq,
    //                 :out_msg);
    //             END;";

    //     $outNoReq = "";
    //     $outMsg = "";
    //     $stmt = oci_parse($conn, $sql);
    //     foreach ($param as $key => &$value) {
    //         $value = $value == null ? '' : $value;
    //         oci_bind_by_name($stmt, ':'.$key, $value);
    //     }

    //     $cursor = oci_new_cursor($conn);
    //     oci_bind_by_name($stmt,":out_noreq", $cursor,-1,OCI_B_CURSOR);
    //     oci_bind_by_name($stmt,":out_msg", $cursor,-1,OCI_B_CURSOR);
    //     oci_execute($stmt);
    //     oci_execute($cursor);

    //     $noReq = '';
    //     while ($data = oci_fetch_assoc($cursor, OCI_RETURN_LOBS )) {
    //         dump($data);
    //     }
    //     dd($noReq);
    //     DB::commit();
    //     // return response()->json([
    //     //     'status' => [
    //     //         'code' => 200,
    //     //         'msg' => 'Success Processing Data',
    //     //     ], 'data' => [
    //     //         'outmsg' => $outMsg,
    //     //         'out_noreq' => $outNoReq
    //     //     ]
    //     // ], 200);
    //     // } catch (Exception $th) {
    //     //     DB::rollBack();
    //     //     return response()->json([
    //     //         'status' => [
    //     //             'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
    //     //             'code' => $th->getCode() != '' ? $th->getCode() : 500,
    //     //         ],
    //     //         'data' => null,
    //     //         'err_detail' => $th,
    //     //         'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
    //     //     ], 500);
    //     // }
    // }

    function addRequestPraya($param)
    {
        DB::beginTransaction();
        try {
            $pdo = DB::getPdo();

            $outNoReq = "";
            $outMsg = "";

            // $param['out_noreq'] = $outNoReq;
            // $param['out_msg'] = $outMsg;
            // DB::connection('uster')->statement(
            //     "
            //     begin pack_create_req_stripping.create_header_strip_praya(
            //         :in_accpbm,
            //         :in_pbm,
            //         :in_personal,
            //         :in_do,
            //         :in_datesppb,
            //         :in_nosppb,
            //         :in_keterangan,
            //         :in_user,
            //         :in_di,
            //         :in_vessel,
            //         :in_voyin,
            //         :in_voyout,
            //         :in_idvsb,
            //         :in_nobooking,
            //         :in_callsign,
            //         :in_bl,
            //         :in_vessel_code,
            //         :in_tanggal_jam_tiba,
            //         :in_tanggal_jam_berangkat,
            //         :in_operator_name,
            //         :in_operator_id,
            //         :in_pod,
            //         :in_pol,
            //         :in_voyage,
            //         :out_noreq,
            //         :out_msg
            //     ); end;",$param
            // );

            $procedureName = 'uster.pack_create_req_stripping.create_header_strip_praya';
            $stmt = $pdo->prepare(
                "
                        DECLARE BEGIN " . $procedureName . " (
                            :in_accpbm,
                            :in_pbm,
                            :in_personal,
                            :in_do,
                            :in_datesppb,
                            :in_nosppb,
                            :in_keterangan,
                            :in_user,
                            :in_di,
                            :in_vessel,
                            :in_voyin,
                            :in_voyout,
                            :in_idvsb,
                            :in_nobooking,
                            :in_callsign,
                            :in_bl,
                            :in_vessel_code,
                            :in_tanggal_jam_tiba,
                            :in_tanggal_jam_berangkat,
                            :in_operator_name,
                            :in_operator_id,
                            :in_pod,
                            :in_pol,
                            :in_voyage,
                            :out_noreq,
                            :out_msg
                        ); END;"
            );

            foreach ($param as $key => &$value) {
                $stmt->bindParam(":$key", $value, PDO::PARAM_STR);
            }

            $stmt->bindParam(":out_noreq", $outNoReq, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->bindParam(":out_msg", $outMsg, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->execute();

            DB::commit();
            return response()->json([
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ],
                'data' => [
                    'outmsg' => $outMsg,
                    'out_noreq' => $outNoReq
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
            ], 500);
        }
    }

    function saveEdit($data, $noReq)
    {
        DB::beginTransaction();
        try {
            $dataPlanStripping = generateQueryEdit($data['plan_request']);
            $dataRequestStrip = generateQueryEdit($data['request_strip']);

            $query_save = "UPDATE PLAN_REQUEST_STRIPPING SET $dataPlanStripping WHERE NO_REQUEST = '$noReq'";
            $exec = DB::connection('uster')->statement($query_save);
            if (!$exec) {
                throw new Exception('Gagal Update Data Plan Request Stripping', 500);
            }

            $query_save_ = "UPDATE REQUEST_STRIPPING SET $dataRequestStrip WHERE NO_REQUEST = REPLACE('$noReq','P','S')";
            $exec_ = DB::connection('uster')->statement($query_save_);
            if (!$exec_) {
                throw new Exception('Gagal Update Data Request Stripping', 500);
            }

            DB::commit();
            return response()->json([
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ],
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
            ], 500);
        }
    }

    function saveCont($param)
    {
        DB::beginTransaction();
        try {
            $pdo = DB::getPdo();

            $outMsg = "";

            DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'dd/mm/rrrr'");
            $procedureName = 'uster.pack_create_req_stripping.create_detail_strip';
            $stmt = $pdo->prepare(
                "
                        DECLARE BEGIN " . $procedureName . " (
                            :in_nocont,
                            :in_planreq,
                            :in_size,
                            :in_type,
                            :in_status,
                            :in_hz,
                            :in_commodity,
                            :in_voyin,
                            :in_after_strip,
                            :in_asalcont,
                            :in_datedisch,
                            :in_tglmulai,
                            :in_tglselesai,
                            :in_blok,
                            :in_slot,
                            :in_row,
                            :in_tier,
                            :in_nobooking,
                            :in_iduser,
                            :p_ErrMsg);
                        end;"
            );

            foreach ($param as $key => &$value) {
                $stmt->bindParam(":$key", $value, PDO::PARAM_STR);
            }
            $stmt->bindParam(":p_ErrMsg", $outMsg, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->execute();

            DB::commit();
            return response()->json([
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ],
                'data' => [
                    'outmsg' => $outMsg,
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
            ], 500);
        }
    }

    function approveContTPK(Request $request, $param)
    {
        DB::beginTransaction();
        try {
            $pdo = DB::getPdo();

            $outMsg = "";

            $queryProc = "
                DECLARE BEGIN USTER.PACK_CREATE_REQ_STRIPPING.CREATE_APPROVE_STRIP_PRAYA2 (:in_nocont,:in_planreq,:in_reqnbs,:in_asalcont,
                            :in_container_size,:in_container_type,:in_container_status,:in_container_hz,:in_container_imo,
                            :in_container_iso_code,:in_container_height,:in_container_carrier,:in_container_reefer_temp,
                            :in_container_booking_sl,:in_container_over_width,:in_container_over_length,:in_container_over_height,
                            :in_container_over_front,:in_container_over_rear,:in_container_over_left,:in_container_over_right,
                            :in_container_un_number,:in_container_pod,:in_container_pol,:in_container_vessel_confirm,
                            :in_container_comodity,:in_container_c_type_code,:p_ErrMsg);
                        end;
            ";

            $stmt = $pdo->prepare($queryProc);
            foreach ($param as $key => &$value) {
                $stmt->bindParam(":$key", $value, PDO::PARAM_STR);
            }

            $stmt->bindParam(":p_ErrMsg", $outMsg, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->execute();

            DB::commit();
            return response()->json([
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ],
                'data' => [
                    'outmsg' => $outMsg,
                ]
            ], 200);
        } catch (Exception $th) {
            DB::rollBack();
            $error = DB::connection('uster')->getPdo()->errorInfo();
            Log::error("Error inserting data into nota_stripping_d: " . implode(', ', $error));
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], 500);
        }
    }
}
