<?php

namespace App\Services\Request\Receiving;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use PDO;

class ReceivingService
{
    function getData(Request $request)
    {
        $no_req = $request->no_request;
        $from = $request->tgl_awal;
        $to = $request->tgl_akhir;
        $noReqWhere = '';
        $dateWhere = '';
        $start = intval($request->start ?? 0);
        $length = intval($request->length ?? 0);

        if ($request->cari == 'true') {
            if ($request->no_request == null && ($from != null && $to != null)) {
                $dateWhere = "AND a.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')";
            } else if ($no_req != null && ($from == null && $to == null)) {
                $noReqWhere = "AND a.NO_REQUEST = '$no_req'";
            } else if ($no_req != null && ($from != null && $to != null)) {
                $noReqWhere = "AND a.NO_REQUEST = '$no_req'";
                $dateWhere = "AND a.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')";
            }

            $query_list        = "   SELECT  a.NM_CONSIGNEE AS NAMA_EMKL,
                                            a.NO_REQUEST,
                                            a.TGL_REQUEST,
                                            a.RECEIVING_DARI,
                                            a.NOTA as nota_cek_nota,
                                            a.KOREKSI as koreksi_cek_nota,
                                            b.LUNAS as lunas_cek_nota,
                                            c.STATUS as status_nota_header
                                    FROM REQUEST_RECEIVING a
                                    LEFT JOIN NOTA_RECEIVING b
                                        ON a.NO_REQUEST = b.NO_REQUEST(+)
                                    LEFT JOIN ITPK_NOTA_HEADER c
                                        ON a.NO_REQUEST = c.NO_REQUEST
                                    WHERE  a.RECEIVING_DARI = 'LUAR'
                                        $noReqWhere
                                        $dateWhere
                                    AND ROWNUM < 100
                                    ORDER BY a.TGL_REQUEST DESC";
        } else {
            $query_list        = "SELECT *
						  FROM (  SELECT  a.NM_CONSIGNEE AS NAMA_EMKL,
                                            a.NO_REQUEST,
                                            a.TGL_REQUEST,
                                            a.RECEIVING_DARI,
                                            a.NOTA as nota_cek_nota,
                                            a.KOREKSI as koreksi_cek_nota,
                                            b.LUNAS as lunas_cek_nota,
                                            c.STATUS as status_nota_header
                                    FROM REQUEST_RECEIVING a
                                    LEFT JOIN NOTA_RECEIVING b
                                        ON a.NO_REQUEST = b.NO_REQUEST(+)
                                    LEFT JOIN ITPK_NOTA_HEADER c
                                        ON a.NO_REQUEST = c.NO_REQUEST
						            WHERE     a.RECEIVING_DARI = 'LUAR'
						                 AND a.TGL_REQUEST BETWEEN SYSDATE - INTERVAL '15' DAYa AND LAST_DAY (SYSDATE)
						        ORDER BY a.TGL_REQUEST DESC)
                               ";
        }

        $query = '(' . $query_list . ') data_receiving';
        $data = DB::connection('uster')->table(DB::raw($query))->select()->get();

        return $data;
    }

    function getOverviewData($noReq, $from)
    {
        $otherCondJoin = $from == 'view' ? "AND d.KD_CABANG = '05'" : '';
        $query_request	= "SELECT a.NO_REQUEST AS NO_REQUEST,
							  a.KETERANGAN AS KETERANGAN,
							  a.RECEIVING_DARI AS RECEIVING_DARI,
							  a.KD_CONSIGNEE AS KD_CONSIGNEE,
							  d.NM_PBM AS CONSIGNEE,
							  d.NO_NPWP_PBM AS NO_NPWP_PBM,
							  d.ALMT_PBM AS ALMT_PBM,
                              a.NO_RO
					   FROM   REQUEST_RECEIVING a INNER JOIN
							  V_MST_PBM d ON a.KD_CONSIGNEE = d.KD_PBM
                              $otherCondJoin
					   WHERE a.NO_REQUEST = '$noReq'";

        $query = '(' . $query_request . ') data_receiving';

        $data = array();
        DB::connection('uster')->table(DB::raw($query))->orderBy('data_receiving.NO_REQUEST')->chunk(100, function($chunk) use (&$data){
            foreach ($chunk as $dt) {
                $data[] = $dt;
            }
        });

        return $data;
    }

    function contList($noReq)
    {
        $query_list		= "SELECT a.*,
                                    b.*,
                                    'USTER' AS NAMA_YARD,
                                    c.TGL_UPDATE
                            FROM MASTER_CONTAINER a
                            INNER JOIN CONTAINER_RECEIVING b
                            ON a.NO_CONTAINER = b.NO_CONTAINER
                            LEFT JOIN HISTORY_CONTAINER c
                            ON b.NO_CONTAINER = c.NO_CONTAINER
                                AND b.NO_REQUEST = c.NO_REQUEST
                                AND c.KEGIATAN = 'REQUEST RECEIVING'
                            WHERE b.NO_REQUEST = '$noReq'
                            ORDER BY c.TGL_UPDATE DESC";


        $data = DB::connection('uster')->select($query_list);
        return $data;
    }

    function addEdit($data, $noReq = null)
    {
        if($noReq != null){
            $updateQuery = generateQueryEdit($data);
            $query	= "UPDATE REQUEST_RECEIVING SET $updateQuery WHERE NO_REQUEST = '$noReq'";
        } else {
            $insertQuery = generateQuerySimpan($data);
            $query = "INSERT INTO REQUEST_RECEIVING $insertQuery";
        }

        DB::beginTransaction();
        try {
            $exec = DB::connection('uster')->statement($query);

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

    function saveCont($data){
        DB::beginTransaction();
        try {
            $insertQuery = generateQuerySimpan($data);
            $query = "INSERT INTO CONTAINER_RECEIVING $insertQuery";

            $exec = DB::connection('uster')->statement($query);

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

    function delContProcess($noCont, $noReq)
    {
        DB::beginTransaction();
        try {
            $query_counter = "SELECT COUNTER,NO_BOOKING FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$noCont'";
            $rw_counter = DB::connection('uster')->selectOne($query_counter);
            $counter = $rw_counter->counter;
            $book = $rw_counter->no_booking;

            $queryDelHistory = "DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$noCont' AND COUNTER = '$counter' AND NO_REQUEST = '$noReq'";
            $execDelHistory = DB::connection('uster')->statement($queryDelHistory);

            $query_del	= "DELETE FROM CONTAINER_RECEIVING WHERE NO_CONTAINER = '$noCont' AND NO_REQUEST = '$noReq'";
            $execDelContRec = DB::connection('uster')->statement($query_del);

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

    // Get Master Data
    function getPbm($term)
    {
        $query  = "SELECT pbm.KD_PBM,pbm.NM_PBM,pbm.ALMT_PBM,pbm.NO_NPWP_PBM FROM V_MST_PBM PBM
				    where pbm.KD_CABANG='05'
                        AND UPPER(pbm.NM_PBM) LIKE '%$term%'
                        AND PELANGGAN_AKTIF = '1'
                        AND pbm.ALMT_PBM IS NOT NULL";

        $data = DB::connection('uster')->select($query);
        return $data;
    }

    function getContainer($noCont)
    {
        $query = "SELECT * from (
                    select
                        d.no_container
                        , d.size_cont
                        , trim(d.type_cont) type_cont
                        , d.vessel||'|'||d.voyage_in||' '||d.voyage_out as vessel
                        , d.no_ukk
                    from billing.req_delivery_h h, billing.req_delivery_d d
                    where trim(h.id_req) = trim(d.id_req)
                        and no_container like '%$noCont%'
                    order by tgl_request desc
                ) where rownum <=1";
        $data = DB::connection('uster')->select($query);
        return $data;
    }

    function getKomoditi($search)
    {
        $query = "SELECT KD_COMMODITY, NM_COMMODITY from BILLING.MASTER_COMMODITY WHERE UPPER(NM_COMMODITY) LIKE '%$search%'";
        $data = DB::connection('uster')->select($query);
        return $data;
    }

    function getOwner($search)
    {
        $query = "SELECT KD_PBM KD_OWNER, NM_PBM NM_OWNER  FROM V_MST_PBM WHERE UPPER(NM_PBM)  LIKE '%$search%'";
        $data = DB::connection('uster')->select($query);
        return $data;
    }
}
