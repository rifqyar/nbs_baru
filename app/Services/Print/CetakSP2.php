<?php

namespace App\Services\Print;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CetakSP2
{
    function getData($data)
    {
        $cari    = $data["cari"];
        $from    = $data["tgl_awal"];
        $to        = $data["tgl_akhir"];
        $no_req    = $data["no_request"];

        if ($cari) {
            if ((isset($no_req)) && ($from == NULL) && ($to == NULL)) {
                $query_list = "SELECT REQUEST_DELIVERY.DELIVERY_KE,
                                        REQUEST_DELIVERY.PERALIHAN,
                                        NVL (NOTA_DELIVERY.LUNAS, 0) LUNAS,
                                        NVL (NOTA_DELIVERY.NO_FAKTUR, '-') NO_NOTA,
                                        REQUEST_DELIVERY.NO_REQUEST,
                                        TO_CHAR (REQUEST_DELIVERY.TGL_REQUEST, 'dd/mm/yyyy')
                                            TGL_REQUEST,
                                        TO_DATE (TGL_REQUEST_DELIVERY, 'dd/mm/yyyy')
                                            TGL_REQUEST_DELIVERY,
                                        emkl.NM_PBM AS NAMA_EMKL,
                                        request_delivery.VOYAGE,
                                        request_delivery.VESSEL NAMA_VESSEL,
                                        COUNT (container_delivery.NO_CONTAINER) JML_CONT
                                    FROM REQUEST_DELIVERY
                                        LEFT JOIN NOTA_DELIVERY
                                            ON NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                        INNER JOIN v_mst_pbm emkl
                                            ON REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                                                AND emkl.KD_CABANG = '05'
                                        INNER JOIN container_delivery
                                            ON REQUEST_DELIVERY.NO_REQUEST =
                                                container_delivery.NO_REQUEST
                                    WHERE REQUEST_DELIVERY.delivery_ke = 'LUAR' AND REQUEST_DELIVERY.NO_REQUEST = '$no_req'
                                GROUP BY REQUEST_DELIVERY.DELIVERY_KE,
                                        REQUEST_DELIVERY.PERALIHAN,
                                        NVL (NOTA_DELIVERY.LUNAS, 0),
                                        NVL (NOTA_DELIVERY.NO_FAKTUR, '-'),
                                        REQUEST_DELIVERY.NO_REQUEST,
                                        TO_CHAR (REQUEST_DELIVERY.TGL_REQUEST, 'dd/mm/yyyy'),
                                        TO_DATE (TGL_REQUEST_DELIVERY, 'dd/mm/yyyy'),
                                        emkl.NM_PBM,
                                        request_delivery.VOYAGE,
                                        request_delivery.VESSEL)";
            } else if ((isset($from)) && (isset($to)) && ($no_req == NULL)) {
                $query_list = "SELECT DISTINCT * FROM (
                                        SELECT REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, NVL(NOTA_DELIVERY.NO_NOTA, '-') NO_NOTA, REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM AS NAMA_EMKL, request_delivery.VOYAGE, request_delivery.VESSEL NAMA_VESSEL, yard_area.NAMA_YARD, COUNT(container_delivery.NO_CONTAINER) JML_CONT
                                        FROM REQUEST_DELIVERY left join NOTA_DELIVERY on NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                        left join  v_mst_pbm emkl on REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                        left join yard_area on REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                        inner join container_delivery on REQUEST_DELIVERY.NO_REQUEST = container_delivery.NO_REQUEST
                                        where
                                        nota_delivery.TGL_NOTA = (SELECT MAX(e.TGL_NOTA) FROM NOTA_DELIVERY e WHERE e.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST)
                                        and
                                        request_delivery.PERALIHAN NOT IN ('RELOKASI', 'STRIPPING', 'STUFFING')
                                    GROUP BY REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0), NVL(NOTA_DELIVERY.NO_NOTA, '-'),REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy'), TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy'), emkl.NM_PBM, request_delivery.VOYAGE, request_delivery.VESSEL, yard_area.NAMA_YARD
                                    union SELECT REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, NVL(NOTA_DELIVERY.NO_NOTA, '-') NO_NOTA, REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM AS NAMA_EMKL, request_delivery.VOYAGE, request_delivery.VESSEL NAMA_VESSEL, yard_area.NAMA_YARD, COUNT(container_delivery.NO_CONTAINER) JML_CONT
                                        FROM REQUEST_DELIVERY left join NOTA_DELIVERY on NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                        left join  v_mst_pbm emkl on REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                        left join yard_area on REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                        inner join container_delivery on REQUEST_DELIVERY.NO_REQUEST = container_delivery.NO_REQUEST
                                        where
                                        request_delivery.PERALIHAN NOT IN ('RELOKASI', 'STRIPPING', 'STUFFING')
                                    GROUP BY REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0), NVL(NOTA_DELIVERY.NO_NOTA, '-'),REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy'), TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy'), emkl.NM_PBM, request_delivery.VOYAGE, request_delivery.VESSEL, yard_area.NAMA_YARD ) agr
                                    WHERE request_delivery.TGL_REQUEST_DELIVERY BETWEEN TO_DATE('$from','yyyy/mm/dd') AND TO_DATE('$to','yyyy/mm/dd')
                                ORDER BY agr.NO_REQUEST DESC";
            } else if ((isset($from)) && (isset($to)) && (isset($no_req))) {
                $query_list = "SELECT DISTINCT * FROM (
                                        SELECT REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, NVL(NOTA_DELIVERY.NO_NOTA, '-') NO_NOTA, REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM AS NAMA_EMKL, request_delivery.VOYAGE, request_delivery.VESSEL NAMA_VESSEL, yard_area.NAMA_YARD, COUNT(container_delivery.NO_CONTAINER) JML_CONT
                                        FROM REQUEST_DELIVERY left join NOTA_DELIVERY on NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                        left join  v_mst_pbm emkl on REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                        left join yard_area on REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                        inner join container_delivery on REQUEST_DELIVERY.NO_REQUEST = container_delivery.NO_REQUEST
                                        where
                                        nota_delivery.TGL_NOTA = (SELECT MAX(e.TGL_NOTA) FROM NOTA_DELIVERY e WHERE e.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST)
                                        and
                                        request_delivery.PERALIHAN NOT IN ('RELOKASI', 'STRIPPING', 'STUFFING')
                                    GROUP BY REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0), NVL(NOTA_DELIVERY.NO_NOTA, '-'),REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy'), TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy'), emkl.NM_PBM, request_delivery.VOYAGE, request_delivery.VESSEL, yard_area.NAMA_YARD
                                    union SELECT REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, NVL(NOTA_DELIVERY.NO_NOTA, '-') NO_NOTA, REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM AS NAMA_EMKL, request_delivery.VOYAGE, request_delivery.VESSEL NAMA_VESSEL, yard_area.NAMA_YARD, COUNT(container_delivery.NO_CONTAINER) JML_CONT
                                        FROM REQUEST_DELIVERY left join NOTA_DELIVERY on NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                        left join  v_mst_pbm emkl on REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                        left join yard_area on REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                        inner join container_delivery on REQUEST_DELIVERY.NO_REQUEST = container_delivery.NO_REQUEST
                                        where
                                        request_delivery.PERALIHAN NOT IN ('RELOKASI', 'STRIPPING', 'STUFFING')
                                    GROUP BY REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0), NVL(NOTA_DELIVERY.NO_NOTA, '-'),REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy'), TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy'), emkl.NM_PBM, request_delivery.VOYAGE, request_delivery.VESSEL, yard_area.NAMA_YARD ) agr
                                    WHERE agr.NO_REQUEST = '$no_req' AND request_delivery.TGL_REQUEST_DELIVERY BETWEEN TO_DATE('$from','yyyy/mm/dd') AND TO_DATE('$to','yyyy/mm/dd')
                                ORDER BY agr.NO_REQUEST DESC";
            } else {
                $query_list = "SELECT DISTINCT * FROM (
                                    SELECT REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, NVL(NOTA_DELIVERY.NO_NOTA, '-') NO_NOTA, REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM AS NAMA_EMKL, request_delivery.VOYAGE, request_delivery.VESSEL NAMA_VESSEL, yard_area.NAMA_YARD, COUNT(container_delivery.NO_CONTAINER) JML_CONT
                                    FROM REQUEST_DELIVERY left join NOTA_DELIVERY on NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                    left join  v_mst_pbm emkl on REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                    left join yard_area on REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                    inner join container_delivery on REQUEST_DELIVERY.NO_REQUEST = container_delivery.NO_REQUEST
                                    where
                                    nota_delivery.TGL_NOTA = (SELECT MAX(e.TGL_NOTA) FROM NOTA_DELIVERY e WHERE e.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST)
                                    and
                                    request_delivery.PERALIHAN NOT IN ('RELOKASI', 'STRIPPING', 'STUFFING')
                                GROUP BY REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0), NVL(NOTA_DELIVERY.NO_NOTA, '-'),REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy'), TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy'), emkl.NM_PBM, request_delivery.VOYAGE, request_delivery.VESSEL, yard_area.NAMA_YARD
                                union SELECT REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, NVL(NOTA_DELIVERY.NO_NOTA, '-') NO_NOTA, REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM AS NAMA_EMKL, request_delivery.VOYAGE, request_delivery.VESSEL NAMA_VESSEL, yard_area.NAMA_YARD, COUNT(container_delivery.NO_CONTAINER) JML_CONT
                                    FROM REQUEST_DELIVERY left join NOTA_DELIVERY on NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                    left join  v_mst_pbm emkl on REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                    left join yard_area on REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                    inner join container_delivery on REQUEST_DELIVERY.NO_REQUEST = container_delivery.NO_REQUEST
                                    where
                                    request_delivery.PERALIHAN NOT IN ('RELOKASI', 'STRIPPING', 'STUFFING')
                                GROUP BY REQUEST_DELIVERY.DELIVERY_KE, REQUEST_DELIVERY.PERALIHAN, NVL(NOTA_DELIVERY.LUNAS, 0), NVL(NOTA_DELIVERY.NO_NOTA, '-'),REQUEST_DELIVERY.NO_REQUEST, TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy'), TO_DATE(TGL_REQUEST_DELIVERY,'dd/mm/yyyy'), emkl.NM_PBM, request_delivery.VOYAGE, request_delivery.VESSEL, yard_area.NAMA_YARD ) agr
                                ORDER BY agr.NO_REQUEST DESC";
            }
        } else {
            $query_list = "SELECT * FROM (
                                SELECT REQUEST_DELIVERY.DELIVERY_KE,
                                    REQUEST_DELIVERY.PERALIHAN,
                                    NVL (NOTA_DELIVERY.LUNAS, 0) LUNAS,
                                    NVL (NOTA_DELIVERY.NO_FAKTUR, '-') NO_NOTA,
                                    REQUEST_DELIVERY.NO_REQUEST,
                                    REQUEST_DELIVERY.TGL_REQUEST,
                                    REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,
                                    emkl.NM_PBM AS NAMA_EMKL,
                                    request_delivery.VOYAGE,
                                    request_delivery.VESSEL NAMA_VESSEL,
                                    COUNT (container_delivery.NO_CONTAINER) JML_CONT
                                FROM REQUEST_DELIVERY
                                    LEFT JOIN NOTA_DELIVERY
                                        ON NOTA_DELIVERY.NO_REQUEST = REQUEST_DELIVERY.NO_REQUEST
                                    INNER JOIN v_mst_pbm emkl
                                        ON REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                                            AND emkl.KD_CABANG = '05'
                                    INNER JOIN container_delivery
                                        ON REQUEST_DELIVERY.NO_REQUEST =
                                                container_delivery.NO_REQUEST
                                WHERE REQUEST_DELIVERY.delivery_ke = 'LUAR'
                                GROUP BY REQUEST_DELIVERY.DELIVERY_KE,
                                        REQUEST_DELIVERY.PERALIHAN,
                                        NVL (NOTA_DELIVERY.LUNAS, 0),
                                        NVL (NOTA_DELIVERY.NO_FAKTUR, '-'),
                                        REQUEST_DELIVERY.NO_REQUEST,
                                        REQUEST_DELIVERY.TGL_REQUEST,
                                        REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,
                                        emkl.NM_PBM,
                                        request_delivery.VOYAGE,
                                        request_delivery.VESSEL
                                ORDER BY TGL_REQUEST DESC
                            ) WHERE ROWNUM <= 100";
        }

        $data = DB::connection('uster')->select($query_list);
        return $data;
    }
}
