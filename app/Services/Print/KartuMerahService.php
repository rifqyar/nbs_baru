<?php

namespace App\Services\Print;

use Exception;
use Illuminate\Support\Facades\DB;

class KartuMerahService
{
    public function getData($data)
    {
        $cari    = $data["cari"];
        $from    = $data["tgl_awal"];
        $to        = $data["tgl_akhir"];
        $no_req    = $data["no_request"];

        if ($cari) {
            if ((isset($no_req)) && ($from == NULL) && ($to == NULL)) {
                $query_list        = "SELECT * FROM (SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.RECEIVING_DARI,
                                              b.NM_PBM AS NAMA_EMKL, c.LUNAS, a.CETAK_KARTU
                                       FROM REQUEST_RECEIVING a
                                            JOIN V_MST_PBM b ON a.KD_CONSIGNEE = b.KD_PBM
                                            JOIN NOTA_RECEIVING c ON a.NO_REQUEST = c.NO_REQUEST
                                       WHERE a.TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)
                                       AND a.KD_CONSIGNEE = b.KD_PBM
                                       AND b.KD_CABANG = '05'
                                       AND a.RECEIVING_DARI = 'LUAR'
                                       AND a.NO_REQUEST LIKE '%$no_req%'
                                       ORDER BY a.TGL_REQUEST DESC)";
            } else if ((isset($from)) && (isset($to)) && ($no_req == NULL)) {
                $query_list        = "SELECT * FROM (SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.RECEIVING_DARI,
                                              b.NM_PBM AS NAMA_EMKL, c.LUNAS, a.CETAK_KARTU
                                       FROM REQUEST_RECEIVING a
                                            JOIN V_MST_PBM b ON a.KD_CONSIGNEE = b.KD_PBM
                                            JOIN NOTA_RECEIVING c ON a.NO_REQUEST = c.NO_REQUEST
                                       WHERE a.TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)
                                       AND a.KD_CONSIGNEE = b.KD_PBM
                                       AND b.KD_CABANG = '05'
                                       AND a.RECEIVING_DARI = 'LUAR'
                                        AND a.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                        AND TO_DATE (CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                       ORDER BY a.TGL_REQUEST DESC)";
            } else if ((isset($from)) && (isset($to)) && (isset($no_req))) {
                $query_list        = " SELECT * FROM (SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.RECEIVING_DARI,
                                              b.NM_PBM AS NAMA_EMKL, c.LUNAS, a.CETAK_KARTU
                                       FROM REQUEST_RECEIVING a
                                            JOIN V_MST_PBM b ON a.KD_CONSIGNEE = b.KD_PBM
                                            JOIN NOTA_RECEIVING c ON a.NO_REQUEST = c.NO_REQUEST
                                       WHERE a.TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)
                                       AND a.KD_CONSIGNEE = b.KD_PBM
                                       AND b.KD_CABANG = '05'
                                       AND a.RECEIVING_DARI = 'LUAR'
                                       AND a.NO_REQUEST = '$no_req'
                                        AND a.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                        AND TO_DATE (CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                       ORDER BY a.TGL_REQUEST DESC)";
            }
        } else {
            $query_list        = "   SELECT * FROM (SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.RECEIVING_DARI,
                                              b.NM_PBM AS NAMA_EMKL, c.LUNAS, a.CETAK_KARTU
                                       FROM REQUEST_RECEIVING a
                                            JOIN V_MST_PBM b ON a.KD_CONSIGNEE = b.KD_PBM
                                            JOIN NOTA_RECEIVING c ON a.NO_REQUEST = c.NO_REQUEST
                                        JOIN NOTA_RECEIVING c on a.NO_REQUEST = c.NO_REQUEST
                                       WHERE a.TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)
                                       AND b.KD_CABANG = '05'
                                       AND a.KD_CONSIGNEE = b.KD_PBM
                                       AND a.RECEIVING_DARI = 'LUAR'
                                       ORDER BY a.TGL_REQUEST DESC)";
        }

        $data = DB::connection('uster')->select($query_list);
        return $data;
    }

    public function cekStatusLunas($no_req)
    {
        $nota = DB::connection("uster")->selectOne("SELECT LUNAS FROM NOTA_RECEIVING WHERE NO_REQUEST = '$no_req' ORDER BY NO_NOTA DESC");

        return $nota->lunas ?? null;
    }

    public function getDataRecLuar($no_req)
    {
        $query_nota =  "SELECT
                            NOTA_RECEIVING.NO_NOTA,
                            TO_CHAR (NOTA_RECEIVING.TGL_NOTA, 'DD-MM-YYYY') TGL_NOTA,
                            V_MST_PBM.NM_PBM EMKL,
                            REQ.NO_REQUEST,
                            CONTAINER_RECEIVING.NO_CONTAINER,
                            MASTER_CONTAINER.SIZE_,
                            MASTER_CONTAINER.TYPE_,
                            CONTAINER_RECEIVING.STATUS STATUS,
                            REQ.CETAK_KARTU,
                            REQ.RECEIVING_DARI,
                            YARD_AREA.NAMA_YARD,
                            MASTER_CONTAINER.NO_BOOKING NM_KAPAL,
                            CASE
                            WHEN CONTAINER_RECEIVING.VIA ='darat' THEN 'DARAT'
                            ELSE 'TONGKANG'
                            END
                            VIA,
                            CASE WHEN MASTER_CONTAINER.MLO = 'MLO' THEN '03' ELSE '02' END AREA_,
                            CONTAINER_RECEIVING.EX_KAPAL
                        FROM NOTA_RECEIVING
                            INNER JOIN REQUEST_RECEIVING REQ
                                ON NOTA_RECEIVING.NO_REQUEST = REQ.NO_REQUEST
                            INNER JOIN CONTAINER_RECEIVING
                                ON REQ.NO_REQUEST = CONTAINER_RECEIVING.NO_REQUEST
                            INNER JOIN MASTER_CONTAINER
                                ON CONTAINER_RECEIVING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
                            LEFT JOIN  V_MST_PBM V_MST_PBM
                                ON REQ.KD_CONSIGNEE = V_MST_PBM.KD_PBM
                                    AND V_MST_PBM.kd_cabang = '05'
                            LEFT JOIN YARD_AREA
                                ON CONTAINER_RECEIVING.DEPO_TUJUAN = YARD_AREA.ID
                        WHERE NOTA_RECEIVING.TGL_NOTA = (SELECT MAX (NOTA.TGL_NOTA)
                                                            FROM NOTA_RECEIVING NOTA
                                                            WHERE NOTA.NO_REQUEST = REQ.NO_REQUEST)
                        AND REQ.NO_REQUEST = '$no_req'";

        $dataNota = DB::connection('uster')->select($query_nota);
        return $dataNota;
    }

    public function getDataTPK($no_req)
    {
        $query_nota   = "SELECT b.NM_PBM AS EMKL,
                            a.NO_REQUEST AS NO_REQUEST,
                            a.RECEIVING_DARI,
                            c.NO_CONTAINER AS NO_CONTAINER,
                            d.SIZE_ AS SIZE_,
                            d.TYPE_ AS TYPE_,
                            c.STATUS AS STATUS,
                            c.BLOK_TPK AS BLOK_TPK,
                            c.SLOT_TPK AS SLOT_TPK,
                            c.ROW_TPK AS ROW_TPK,
                            c.TIER_TPK AS TIER_TPK,
                            a.CETAK_KARTU AS CETAK_KARTU,
                            f.NAMA_YARD AS NAMA_YARD,
                            V_PKK_CONT.NM_KAPAL,
                            V_PKK_CONT.VOYAGE_IN,
                            V_PKK_CONT.VOYAGE_OUT
                        FROM REQUEST_RECEIVING a
                        INNER JOIN V_MST_PBM b ON a.KD_CONSIGNEE = b.KD_PBM
                        JOIN CONTAINER_RECEIVING c ON  a.NO_REQUEST = c.NO_REQUEST
                        JOIN MASTER_CONTAINER d ON c.NO_CONTAINER = d.NO_CONTAINER
                        JOIN YARD_AREA f ON c.DEPO_TUJUAN = f.ID ,
                                PETIKEMAS_CABANG.TTM_BP_CONT TTM_BP_CONT,PETIKEMAS_CABANG.TTD_BP_CONT TTD_BP_CONT,
                                PETIKEMAS_CABANG.V_PKK_CONT V_PKK_CONT,PETIKEMAS_CABANG.TTD_BP_CONFIRM TTD_BP_CONFIRM
                        WHERE TTM_BP_CONT.BP_ID = TTD_BP_CONT.BP_ID
                            AND TTM_BP_CONT.NO_UKK = V_PKK_CONT.NO_UKK
                            AND TTD_BP_CONT.CONT_NO_BP = TTD_BP_CONFIRM.CONT_NO_BP
                            AND TTM_BP_CONT.NO_UKK = TTD_BP_CONFIRM.NO_UKK
                            AND TTD_BP_CONT.BP_ID = TTD_BP_CONFIRM.BP_ID
                            AND TTM_BP_CONT.KD_CABANG ='05'
							AND TTD_BP_CONT.STATUS_CONT = '04'
                            AND TTD_BP_CONT.CONT_NO_BP = C.NO_CONTAINER
                            AND a.NO_REQUEST = '$no_req'";

        $dataNota = DB::connection('uster')->select($query_nota);
        return $dataNota;
    }

    public function getDataNota($no_req)
    {
        $query_nota =  "SELECT NOTA_RECEIVING.NO_NOTA, TO_CHAR(NOTA_RECEIVING.TGL_NOTA, 'DD-MM-YYYY') TGL_NOTA, V_MST_PBM.NM_PBM EMKL, REQ.NO_REQUEST, CONTAINER_RECEIVING.NO_CONTAINER,
                MASTER_CONTAINER.SIZE_, MASTER_CONTAINER.TYPE_, CONTAINER_RECEIVING.STATUS STATUS, REQ.CETAK_KARTU,
                REQ.RECEIVING_DARI, YARD_AREA.NAMA_YARD, MASTER_CONTAINER.NO_BOOKING NM_KAPAL, CASE WHEN CONTAINER_RECEIVING.VIA IS NULL THEN 'DARAT' ELSE 'TONGKANG' END VIA, CASE WHEN MASTER_CONTAINER.MLO = 'MLO' THEN '03'
                ELSE '02' END AREA_, CONTAINER_RECEIVING.EX_KAPAL
                FROM NOTA_RECEIVING INNER JOIN REQUEST_RECEIVING REQ ON NOTA_RECEIVING.NO_REQUEST = REQ.NO_REQUEST
                INNER JOIN CONTAINER_RECEIVING ON REQ.NO_REQUEST = CONTAINER_RECEIVING.NO_REQUEST
                INNER JOIN MASTER_CONTAINER ON CONTAINER_RECEIVING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
                LEFT JOIN V_MST_PBM ON REQ.KD_CONSIGNEE = V_MST_PBM.KD_PBM and V_MST_PBM.kd_cabang = '05'
                LEFT JOIN YARD_AREA ON CONTAINER_RECEIVING.DEPO_TUJUAN = YARD_AREA.ID
                WHERE NOTA_RECEIVING.TGL_NOTA = (SELECT MAX(NOTA.TGL_NOTA) FROM NOTA_RECEIVING NOTA WHERE NOTA.NO_REQUEST =  REQ.NO_REQUEST)
                AND REQ.NO_REQUEST = '$no_req'";
        $returnData['dataNota'] = DB::connection('uster')->select($query_nota);

        $returnData['pegawai']    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";

        return $returnData;
    }
}
