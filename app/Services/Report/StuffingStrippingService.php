<?php

namespace App\Services\Report;

use Exception;
use Illuminate\Support\Facades\DB;

class StuffingStrippingService
{
    public function getDataNota($data)
    {
        try {
            $tgl_awal    = $data["tgl_awal"];
            $tgl_akhir    = $data["tgl_akhir"];
            $jenis        = $data["option_kegiatan"];
            $status_req    = $data["status_req"];

            if ($status_req == 'PERP') {
                $status_req1 = " AND REQUEST_STUFFING.STATUS_REQ = 'PERP'";
                $status_req = " AND REQUEST_STRIPPING.STATUS_REQ = 'PERP'";
            } else if ($status_req == 'NEW') {
                $status_req1 = " AND REQUEST_STUFFING.STATUS_REQ IS NULL";
                $status_req = " AND REQUEST_STRIPPING.STATUS_REQ IS NULL";
            } else {
                $status_req1 = "";
                $status_req = "";
            }

            DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'YYYY-MM-DD'");
            $query_list_     = "SELECT * FROM (
                SELECT CONTAINER_STUFFING.NO_CONTAINER , REQUEST_STUFFING.NO_REQUEST , REQUEST_STUFFING.TGL_REQUEST, V_MST_PBM.NM_PBM, 'STUFFING' KEGIATAN, CONTAINER_STUFFING.TGL_APPROVE, LOKASI_TPK,
                CONTAINER_STUFFING.TGL_REALISASI, PLACEMENT.TGL_PLACEMENT, MASTER_CONTAINER.SIZE_, MASTER_CONTAINER.TYPE_, REQUEST_STUFFING.NM_KAPAL, REQUEST_STUFFING.VOYAGE, REQUEST_STUFFING.NO_REQUEST_RECEIVING, CONTAINER_STUFFING.COMMODITY, container_stuffing.TYPE_STUFFING,
                CASE WHEN REMARK_SP2 = 'Y' THEN CONTAINER_STUFFING.END_STACK_PNKN
                ELSE CONTAINER_STUFFING.START_PERP_PNKN END ACTIVE_TO, RD.PIN_NUMBER
                FROM REQUEST_STUFFING INNER JOIN
                CONTAINER_STUFFING ON REQUEST_STUFFING.NO_REQUEST = CONTAINER_STUFFING.NO_REQUEST
                LEFT JOIN PLAN_CONTAINER_STUFFING ON CONTAINER_STUFFING.NO_CONTAINER = PLAN_CONTAINER_STUFFING.NO_CONTAINER AND
                CONTAINER_STUFFING.NO_REQUEST = REPLACE(PLAN_CONTAINER_STUFFING.NO_REQUEST,'P','S')
                INNER JOIN MASTER_CONTAINER ON CONTAINER_STUFFING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
                LEFT JOIN V_MST_PBM ON REQUEST_STUFFING.KD_CONSIGNEE = V_MST_PBM.KD_PBM and V_MST_PBM.KD_CABANG = '05'
                LEFT JOIN PLACEMENT ON CONTAINER_STUFFING.NO_CONTAINER  = PLACEMENT.NO_CONTAINER AND REQUEST_STUFFING.NO_REQUEST_RECEIVING = PLACEMENT.NO_REQUEST_RECEIVING
                LEFT JOIN BILLING_NBS.REQ_DELIVERY_D RD ON REQUEST_STUFFING.O_REQNBS = trim(RD.ID_REQ) AND CONTAINER_STUFFING.NO_CONTAINER = RD.NO_CONTAINER
                WHERE TRUNC(TO_DATE(REQUEST_STUFFING.TGL_REQUEST,'YYYY-MM-DD'))  BETWEEN TRUNC(TO_DATE('$tgl_awal','YYYY-MM-DD')) AND TRUNC(TO_DATE('$tgl_akhir','YYYY-MM-DD'))
                AND CONTAINER_STUFFING.STATUS_REQ IS NULL AND REQUEST_STUFFING.NOTA = 'Y' $status_req1
                UNION
                SELECT DISTINCT CONTAINER_STRIPPING.NO_CONTAINER , REQUEST_STRIPPING.NO_REQUEST , REQUEST_STRIPPING.TGL_REQUEST,  V_MST_PBM.NM_PBM, 'STRIPPING' KEGIATAN, CONTAINER_STRIPPING.TGL_APPROVE,
                CASE
                WHEN LOKASI_TPK IS NULL
                THEN
                (
                    SELECT
                        LOKASI_TPK
                    FROM
                        PLAN_CONTAINER_STRIPPING,
                        PLAN_REQUEST_STRIPPING
                    WHERE
                        PLAN_CONTAINER_STRIPPING.NO_REQUEST = PLAN_REQUEST_STRIPPING.NO_REQUEST
                        AND NO_CONTAINER = CONTAINER_STRIPPING.NO_CONTAINER
                        ORDER BY PLAN_REQUEST_STRIPPING.TGL_REQUEST DESC
                        FETCH FIRST 1 ROWS ONLY)
                ELSE LOKASI_TPK
                END LOKASI_TPK,
                CONTAINER_STRIPPING.TGL_REALISASI, PLACEMENT.TGL_PLACEMENT, MASTER_CONTAINER.SIZE_, MASTER_CONTAINER.TYPE_, VP.NM_KAPAL NM_KAPAL, VP.VOYAGE_IN VOYAGE, REQUEST_STRIPPING.NO_REQUEST_RECEIVING, CONTAINER_STRIPPING.COMMODITY, '' TYPE_STUFFING, CONTAINER_STRIPPING.TGL_SELESAI ACTIVE_TO, RD.PIN_NUMBER
                FROM REQUEST_STRIPPING INNER JOIN
                CONTAINER_STRIPPING ON REQUEST_STRIPPING.NO_REQUEST = CONTAINER_STRIPPING.NO_REQUEST
                LEFT JOIN PLAN_CONTAINER_STRIPPING ON CONTAINER_STRIPPING.NO_CONTAINER = PLAN_CONTAINER_STRIPPING.NO_CONTAINER AND
                CONTAINER_STRIPPING.NO_REQUEST = REPLACE(PLAN_CONTAINER_STRIPPING.NO_REQUEST,'P','S')
                INNER JOIN HISTORY_CONTAINER ON CONTAINER_STRIPPING.NO_REQUEST = HISTORY_CONTAINER.NO_REQUEST AND CONTAINER_STRIPPING.NO_CONTAINER = HISTORY_CONTAINER.NO_CONTAINER
                AND HISTORY_CONTAINER.KEGIATAN IN ('REQUEST STRIPPING','PERPANJANGAN STRIPPING')
                INNER JOIN MASTER_CONTAINER ON CONTAINER_STRIPPING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
                LEFT JOIN V_MST_PBM ON REQUEST_STRIPPING.KD_CONSIGNEE = V_MST_PBM.KD_PBM AND V_MST_PBM.KD_CABANG = '05'
                LEFT JOIN PLACEMENT ON CONTAINER_STRIPPING.NO_CONTAINER  = PLACEMENT.NO_CONTAINER AND REQUEST_STRIPPING.NO_REQUEST_RECEIVING = PLACEMENT.NO_REQUEST_RECEIVING
                LEFT JOIN V_PKK_CONT VP ON VP.NO_BOOKING = HISTORY_CONTAINER.NO_BOOKING
                LEFT JOIN BILLING_NBS.REQ_DELIVERY_D RD ON REQUEST_STRIPPING.O_REQNBS = trim(RD.ID_REQ) AND CONTAINER_STRIPPING.NO_CONTAINER = RD.NO_CONTAINER
                WHERE TRUNC(TO_DATE(REQUEST_STRIPPING.TGL_REQUEST,'YYYY-MM-DD')) BETWEEN TRUNC(TO_DATE('$tgl_awal','YYYY-MM-DD')) AND TRUNC(TO_DATE('$tgl_akhir','YYYY-MM-DD'))
                AND container_stripping.STATUS_REQ IS NULL AND REQUEST_STRIPPING.NOTA = 'Y' $status_req
                ) A WHERE A.KEGIATAN LIKE '%$jenis%' ORDER BY NO_REQUEST DESC ";

            $query = '(' . $query_list_ . ') data_all';
            $result = DB::connection('uster')->select($query_list_);
            $data = [];
            foreach ($result as $key => $value) {
                $no_cont = $value->no_container;
                $no_receive = $value->no_request_receiving;
                $no_req = $value->no_request;
                $cek_ust = "SELECT
                            DISTINCT BLOCKING_AREA.NAME,
                            PLACEMENT.SLOT_,
                            PLACEMENT.ROW_,
                            PLACEMENT.TIER_
                        FROM
                            PLACEMENT
                            JOIN BLOCKING_AREA ON PLACEMENT.ID_BLOCKING_AREA = BLOCKING_AREA.ID
                        WHERE
                            NO_CONTAINER = '$no_cont'
                            AND PLACEMENT.TGL_UPDATE = (
                            SELECT MAX(TGL_UPDATE)
                            FROM
                                PLACEMENT
                            WHERE
                                NO_CONTAINER = '$no_cont')
                            AND PLACEMENT.NO_REQUEST_RECEIVING = '$no_receive'
                        GROUP BY
                            BLOCKING_AREA.NAME,
                            PLACEMENT.ROW_,
                            PLACEMENT.TIER_,
                            PLACEMENT.SLOT_";
                $res = DB::connection('uster')->selectOne($cek_ust);

                $blok = $res->name ?? null;
                $value->blok = $res->name ?? null;
                $value->slot = $res->slot_ ?? null;
                $value->row = $res->row_ ?? null;
                $value->tier = $res->tier_ ?? null;
                if ($blok == NULL) {
                    $cek_ust = "SELECT HISTORY_PLACEMENT.SLOT_, HISTORY_PLACEMENT.ROW_, HISTORY_PLACEMENT.TIER_, BLOCKING_AREA.NAME FROM
                    HISTORY_PLACEMENT INNER JOIN BLOCKING_AREA
                    ON HISTORY_PLACEMENT.ID_BLOCKING_AREA = BLOCKING_AREA.ID
                    LEFT JOIN YARD_AREA ON BLOCKING_AREA.ID_YARD_AREA = YARD_AREA.ID
                    LEFT JOIN MASTER_USER ON HISTORY_PLACEMENT.NIPP_USER = MASTER_USER.NIPP WHERE NO_CONTAINER = '$no_cont'
                    AND HISTORY_PLACEMENT.NO_REQUEST = '$no_receive'
                    ORDER BY HISTORY_PLACEMENT.TGL_UPDATE ASC";
                    $res = DB::connection('uster')->selectOne($cek_ust);
                    $value->blok = $res->name ?? null;
                    $value->slot = $res->slot_ ?? null;
                    $value->row = $res->row_ ?? null;
                    $value->tier = $res->tier_ ?? null;
                }

                $data[] = $value;
            }

            $data_ = [];
            foreach ($data as $key => $value) {
                if ($value->kegiatan == 'STRIPPING') {
                    $lokasi_tpk = $value->lokasi_tpk;
                    if ($value->blok == NULL) {
                        $loc_uster = '';
                    } else {
                        $loc_uster = $value->blok . "/" . $value->slot . "-" . $value->row . "-" . $value->tier;
                    }
                } else {
                    $row_asal_cont = DB::connection('uster')->selectOne("SELECT PLAN_CONTAINER_STUFFING.ASAL_CONT
                    FROM CONTAINER_STUFFING
                    INNER JOIN PLAN_CONTAINER_STUFFING ON CONTAINER_STUFFING.NO_CONTAINER = PLAN_CONTAINER_STUFFING.NO_CONTAINER AND CONTAINER_STUFFING.NO_REQUEST = REPLACE(PLAN_CONTAINER_STUFFING.NO_REQUEST,'P','S')
                    WHERE CONTAINER_STUFFING.NO_CONTAINER='$no_cont'
                    AND    CONTAINER_STUFFING.NO_REQUEST = '$no_req'");
                    $asal = $row_asal_cont->asal_cont;

                    if ($asal == 'DEPO') {
                        $loc_uster = $value->lokasi_tpk;
                        $lokasi_tpk = '--';
                    } else {
                        $lokasi_tpk = $value->lokasi_tpk;
                        if ($value->blok == NULL) {
                            $loc_uster = '--';
                        } else {
                            $loc_uster = $value->blok . "/" . $value->slot . "-" . $value->row . "-" . $value->tier;
                        }
                    }
                }

                $value->lokasi_tpk = $lokasi_tpk;
                $value->loc_uster = $loc_uster;
                $data_[] = $value;
            }

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => 200
                ], 'data' => $result
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Mengambil Data, Harap Coba lagi!'
            ], 500);
        }
    }
}
