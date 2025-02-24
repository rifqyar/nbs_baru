<?php

namespace App\Services\Report;

use Exception;
use Illuminate\Support\Facades\DB;

class  RealisasiService
{
    public function getDataNota($data)
    {
        try {
            $tgl_awal    = $data["tgl_awal"];
            $tgl_akhir    = $data["tgl_akhir"];
            $jenis        = $data["option_kegiatan"];
            $id_menu2     = $data['menu2'] ?? [];

            $jum = count($id_menu2);
            $orderby = 'ORDER BY ';
            for ($i = 0; $i < count($id_menu2); $i++) {
                $orderby .= $id_menu2[$i];
                if ($i != $jum - 1) {
                    $orderby .= ",";
                }
            }

            if ($jum  == 0) {
                $orderby = "";
            }

            DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'YYYY-MM-DD'");
            if ($jenis == 'STRIPPING') {
                $query_list_ = "SELECT DISTINCT CONTAINER_STRIPPING.NO_CONTAINER , MASTER_CONTAINER.SIZE_, MASTER_CONTAINER.TYPE_, 'MTY' STATUS_CONT, REQUEST_STRIPPING.NO_REQUEST , REQUEST_STRIPPING.TGL_REQUEST,  V_MST_PBM.NM_PBM, 'STRIPPING' KEGIATAN, CONTAINER_STRIPPING.TGL_APPROVE,
                               TO_CHAR(CONTAINER_STRIPPING.TGL_REALISASI,'DD-MM-YYYY HH24:MI:SS') TGL_REALISASI, CONTAINER_STRIPPING.ID_USER_REALISASI, TO_CHAR(HISTORY_PLACEMENT.TGL_UPDATE,'DD-MM-YYYY HH24:MI:SS') TGL_PLACEMENT,
                               MASTER_CONTAINER.NO_BOOKING, PKK.NM_KAPAL, MU.NAMA_LENGKAP, PKK.VOYAGE_IN
                               FROM REQUEST_STRIPPING INNER JOIN
                               CONTAINER_STRIPPING ON REQUEST_STRIPPING.NO_REQUEST = CONTAINER_STRIPPING.NO_REQUEST
                               INNER JOIN MASTER_USER MU ON CONTAINER_STRIPPING.ID_USER_REALISASI = MU.ID
                               INNER JOIN MASTER_CONTAINER ON CONTAINER_STRIPPING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
                               INNER JOIN V_PKK_CONT PKK ON PKK.NO_BOOKING = MASTER_CONTAINER.NO_BOOKING
                               LEFT JOIN V_MST_PBM ON REQUEST_STRIPPING.KD_CONSIGNEE = V_MST_PBM.KD_PBM AND V_MST_PBM.KD_CABANG = '05'
                               LEFT JOIN HISTORY_PLACEMENT ON CONTAINER_STRIPPING.NO_CONTAINER  = HISTORY_PLACEMENT.NO_CONTAINER AND REQUEST_STRIPPING.NO_REQUEST_RECEIVING = HISTORY_PLACEMENT.NO_REQUEST
                               WHERE TO_DATE(CONTAINER_STRIPPING.TGL_REALISASI,'YYYY-MM-DD') BETWEEN TO_DATE('$tgl_awal','YYYY-MM-DD') AND TO_DATE('$tgl_akhir','YYYY-MM-DD') " . $orderby;
            } else if ($jenis == 'STUFFING') {
                $query_list_ = "SELECT CONTAINER_STUFFING.NO_CONTAINER , MC.SIZE_, MC.TYPE_, 'FCL' STATUS_CONT,  REQUEST_STUFFING.NO_REQUEST , REQUEST_STUFFING.TGL_REQUEST, V_MST_PBM.NM_PBM, 'STUFFING' KEGIATAN, CONTAINER_STUFFING.TGL_APPROVE,
                               TO_CHAR(CONTAINER_STUFFING.TGL_REALISASI,'DD-MM-YYYY HH24:MI:SS') TGL_REALISASI, CONTAINER_STUFFING.ID_USER_REALISASI, TO_CHAR(HISTORY_PLACEMENT.TGL_UPDATE,'DD-MM-YYYY HH24:MI:SS') TGL_PLACEMENT, REQUEST_STUFFING.NO_BOOKING, VB.NM_KAPAL, VB.VOYAGE_IN, MU.NAMA_LENGKAP
                               FROM REQUEST_STUFFING INNER JOIN
                               CONTAINER_STUFFING ON REQUEST_STUFFING.NO_REQUEST = CONTAINER_STUFFING.NO_REQUEST
                               INNER JOIN MASTER_USER MU ON CONTAINER_STUFFING.ID_USER_REALISASI = MU.ID
                               INNER JOIN MASTER_CONTAINER MC ON CONTAINER_STUFFING.NO_CONTAINER = MC.NO_CONTAINER
                               INNER JOIN V_PKK_CONT VB ON REQUEST_STUFFING.NO_BOOKING = VB.NO_BOOKING
                               LEFT JOIN V_MST_PBM ON REQUEST_STUFFING.KD_CONSIGNEE = V_MST_PBM.KD_PBM AND V_MST_PBM.KD_CABANG = '05'
                               LEFT JOIN HISTORY_PLACEMENT ON CONTAINER_STUFFING.NO_CONTAINER  = HISTORY_PLACEMENT.NO_CONTAINER AND REQUEST_STUFFING.NO_REQUEST_RECEIVING = HISTORY_PLACEMENT.NO_REQUEST
                                WHERE TO_DATE(CONTAINER_STUFFING.TGL_REALISASI,'YYYY-MM-DD') BETWEEN TO_DATE('$tgl_awal','YYYY-MM-DD') AND TO_DATE('$tgl_akhir','YYYY-MM-DD') " . $orderby;
            }


            $result = DB::connection('uster')->select($query_list_);
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
