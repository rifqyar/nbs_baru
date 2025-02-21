<?php

namespace App\Services\Request\Stuffing;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Exception;
use Illuminate\Http\Response;
use PDO;

class PerencaanService
{
    function overviewStuffingPlan($no_req)
    {

        if (isset($no_req)) {
            $no_req    = $no_req;
            $query_r = "SELECT NO_REQUEST_RECEIVING
					FROM PLAN_REQUEST_STUFFING
					WHERE NO_REQUEST = '$no_req'";
            $row_result     =  DB::connection('uster')->selectOne($query_r);
            $no_req_rec    = $row_result->no_request_receiving;

            $row_result2 = substr($no_req_rec, 3);
            $no_req2    = "UREC" . $row_result2;
        } else {
            redirect()->route('uster.new_request.stuffing.stuffing_plan.index');
        }

        $query_request    = "SELECT
                    *
                FROM
                    (
                    SELECT
                        PLAN_REQUEST_STUFFING.NO_REQUEST,
                        PLAN_REQUEST_STUFFING.NO_REQUEST_RECEIVING,
                        PLAN_REQUEST_STUFFING.NO_REQUEST_DELIVERY,
                        EMKL.NM_PBM AS NAMA_EMKL,
                        EMKL.KD_PBM AS ID_EMKL,
                        PLAN_REQUEST_STUFFING.NO_DOKUMEN,
                        PLAN_REQUEST_STUFFING.NO_JPB,
                        PLAN_REQUEST_STUFFING.BPRP,
                        PLAN_REQUEST_STUFFING.NO_NPE,
                        PLAN_REQUEST_STUFFING.NO_PEB,
                        PLAN_REQUEST_STUFFING.KETERANGAN,
                        PLAN_REQUEST_STUFFING.KD_PENUMPUKAN_OLEH,
                        REQUEST_DELIVERY.TGL_MUAT,
                        REQUEST_DELIVERY.TGL_STACKING,
                        REQUEST_DELIVERY.TGL_BERANGKAT,
                        REQUEST_DELIVERY.KD_PELABUHAN_ASAL,
                        REQUEST_DELIVERY.KD_PELABUHAN_TUJUAN,
                        BOOK.NM_AGEN,
                        BOOK.NM_KAPAL,
                        BOOK.VOYAGE_IN,
                        BOOK.NO_BOOKING,
                        BOOK.PELABUHAN_ASAL NM_PELABUHAN_ASAL,
                        BOOK.PELABUHAN_TUJUAN NM_PELABUHAN_TUJUAN,
                        BOOK.NO_BOOKING PKK
                    FROM
                        PLAN_REQUEST_STUFFING
                    JOIN V_MST_PBM EMKL
                            ON
                        PLAN_REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM
                    JOIN V_PKK_CONT BOOK
                            ON
                        PLAN_REQUEST_STUFFING.NM_KAPAL = BOOK.NM_KAPAL
                        AND PLAN_REQUEST_STUFFING.VOYAGE = BOOK.VOYAGE_IN,
                        REQUEST_DELIVERY
                    WHERE
                        PLAN_REQUEST_STUFFING.NO_REQUEST = '$no_req')
                WHERE ROWNUM <= 1";


        $query_list   = "SELECT DISTINCT PLAN_CONTAINER_STUFFING.*, PLAN_CONTAINER_STUFFING.COMMODITY COMMO
           FROM PLAN_CONTAINER_STUFFING
           WHERE PLAN_CONTAINER_STUFFING.NO_REQUEST = '$no_req'";

        $row_request    = DB::connection('uster')->selectOne($query_request);

        $row_list    = DB::connection('uster')->select($query_request);
        $jum = count($row_list);

        return $result = [
            'jum' => $jum,
            'row_list' => $row_list,
            'row_request' => $row_request,
            'no_req2' => $no_req2
        ];
    }


    function viewStuffingPlan($no_req)
    {
        if (isset($no_req)) {
            $no_req    = $no_req;
            $query_r = "SELECT NO_REQUEST_RECEIVING,NO_REQUEST_DELIVERY
					FROM PLAN_REQUEST_STUFFING
					WHERE NO_REQUEST = '$no_req'";
            $row_result     = DB::connection('uster')->selectOne($query_r);

            $no_req_rec        = $row_result->no_request_receiving;
            $no_req_deli    = $row_result->no_request_delivery;

            $no_req2    = $no_req_rec;
        } else {
            redirect()->route('uster.new_request.stuffing.stuffing_plan.index');
        }


        $query_request    = "SELECT
					PLAN_REQUEST_STUFFING.NO_REQUEST,
					PLAN_REQUEST_STUFFING.NO_REQUEST_RECEIVING,
					PLAN_REQUEST_STUFFING.NO_REQUEST_DELIVERY,
                        	EMKL.NM_PBM AS NAMA_EMKL,
                         EMKL.KD_PBM AS ID_EMKL,
                         PLAN_REQUEST_STUFFING.NO_DOKUMEN,
                         PLAN_REQUEST_STUFFING.NO_JPB,
                         PLAN_REQUEST_STUFFING.BPRP,
                         PLAN_REQUEST_STUFFING.NO_NPE,
                         PLAN_REQUEST_STUFFING.NO_PEB,
                         PLAN_REQUEST_STUFFING.KETERANGAN,
                         PLAN_REQUEST_STUFFING.KD_PENUMPUKAN_OLEH,
                         PLAN_REQUEST_STUFFING.ID_PENUMPUKAN,
                         BOOK.NM_AGEN,
                         BOOK.NM_KAPAL,
                         BOOK.VOYAGE_IN,
                         BOOK.VOYAGE_OUT,
                         BOOK.NO_BOOKING,
                         BOOK.PELABUHAN_ASAL NM_PELABUHAN_ASAL,
                         BOOK.PELABUHAN_TUJUAN NM_PELABUHAN_TUJUAN,
                   		BOOK.VOYAGE,
                   		BOOK.NM_AGEN,
					BOOK.KD_AGEN,
					BOOK.KD_KAPAL,
					BOOK.NM_KAPAL,
					BOOK.VOYAGE_IN,
					BOOK.VOYAGE_OUT,
					BOOK.VOYAGE,
					BOOK.NO_BOOKING,
					TO_char(BOOK.tgl_jam_berangkat,'dd/mm/rrrr') TGL_BERANGKAT,
					TO_char(BOOK.tgl_jam_tiba,'dd/mm/rrrr') TGL_TIBA,
					BOOK.PELABUHAN_ASAL KD_PELABUHAN_ASAL,
					BOOK.PELABUHAN_TUJUAN KD_PELABUHAN_TUJUAN,
					BOOK.NO_UKK,
					pnmt.NM_PBM AS NAMA_PNMT,
					PLAN_REQUEST_STUFFING.STUFFING_DARI,
					PLAN_REQUEST_STUFFING.DI
               FROM PLAN_REQUEST_STUFFING
               JOIN V_MST_PBM EMKL ON PLAN_REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM
               JOIN V_PKK_CONT BOOK ON  PLAN_REQUEST_STUFFING.NO_BOOKING = BOOK.NO_BOOKING
            	JOIN V_MST_PBM pnmt ON PLAN_REQUEST_STUFFING.ID_PENUMPUKAN = pnmt.KD_PBM
               WHERE PLAN_REQUEST_STUFFING.NO_REQUEST = '$no_req'";

        $query_list        = "SELECT DISTINCT PLAN_CONTAINER_STUFFING.*, PLAN_CONTAINER_STUFFING.COMMODITY COMMO
						   FROM PLAN_CONTAINER_STUFFING
						   WHERE PLAN_CONTAINER_STUFFING.NO_REQUEST = '$no_req'";

        $row_request    = DB::connection('uster')->selectOne($query_request);

        $row_list    = DB::connection('uster')->select($query_list);
        $jum = count($row_list);

        return [
            'jum' => $jum,
            'row_list' => $row_list,
            'row_request' => $row_request,
            'no_req2' => $no_req2,
        ];
    }

    public function listPerencanaanStuffing($request)
    {
        $from = $request->has('from') ? $request->from : null;
        $to = $request->has('to') ? $request->to : null;
        $no_req = isset($request->search['value']) ? $request->search['value'] : null;


        if (isset($from) || isset($to) || isset($no_req)) {
            if ((isset($no_req)) && ($from == NULL) && ($to == NULL)) {
                $query_list = "SELECT * FROM (SELECT PLAN_REQUEST_STUFFING.TGL_REQUEST, PLAN_REQUEST_STUFFING.APPROVE,PLAN_REQUEST_STUFFING.NO_REQUEST,
                                PLAN_REQUEST_STUFFING.NO_PEB, PLAN_REQUEST_STUFFING.NO_NPE,PLAN_REQUEST_STUFFING.NM_KAPAL, PLAN_REQUEST_STUFFING.VOYAGE,
                                         NVL (REQUEST_STUFFING.NO_REQUEST, 'blm di approve') NO_REQUEST_APP,
                                         REQUEST_STUFFING.NOTA, REQUEST_STUFFING.KOREKSI, emkl.NM_PBM AS NAMA_PEMILIK
                                    FROM PLAN_REQUEST_STUFFING LEFT JOIN REQUEST_STUFFING ON  PLAN_REQUEST_STUFFING.NO_REQUEST =  REPLACE (REQUEST_STUFFING.NO_REQUEST,'S', 'P')
                                    LEFT JOIN  V_MST_PBM emkl ON PLAN_REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                    LEFT JOIN PLAN_CONTAINER_STUFFING ON PLAN_REQUEST_STUFFING.NO_REQUEST = PLAN_CONTAINER_STUFFING.NO_REQUEST
                                 WHERE PLAN_REQUEST_STUFFING.EARLY_STUFFING IS NULL	AND PLAN_REQUEST_STUFFING.NO_REQUEST = '$no_req'
                                 GROUP BY PLAN_REQUEST_STUFFING.APPROVE, PLAN_REQUEST_STUFFING.NO_REQUEST,PLAN_REQUEST_STUFFING.NO_PEB, PLAN_REQUEST_STUFFING.NO_NPE,PLAN_REQUEST_STUFFING.NM_KAPAL, PLAN_REQUEST_STUFFING.VOYAGE,
                                         NVL (REQUEST_STUFFING.NO_REQUEST, 'blm di approve'), REQUEST_STUFFING.NOTA, REQUEST_STUFFING.KOREKSI, emkl.NM_PBM, PLAN_REQUEST_STUFFING.TGL_REQUEST,
                                         PLAN_REQUEST_STUFFING.KD_CONSIGNEE
                                ORDER BY PLAN_REQUEST_STUFFING.TGL_REQUEST DESC)";
            } else if ((isset($from)) && (isset($to)) && ($no_req == NULL)) {
                $query_list = "SELECT * FROM (SELECT PLAN_REQUEST_STUFFING.TGL_REQUEST, PLAN_REQUEST_STUFFING.APPROVE,PLAN_REQUEST_STUFFING.NO_REQUEST,
                                PLAN_REQUEST_STUFFING.NO_PEB, PLAN_REQUEST_STUFFING.NO_NPE,PLAN_REQUEST_STUFFING.NM_KAPAL, PLAN_REQUEST_STUFFING.VOYAGE,
                                         NVL (REQUEST_STUFFING.NO_REQUEST, 'blm di approve') NO_REQUEST_APP,
                                         REQUEST_STUFFING.NOTA, REQUEST_STUFFING.KOREKSI, emkl.NM_PBM AS NAMA_PEMILIK
                                    FROM PLAN_REQUEST_STUFFING LEFT JOIN REQUEST_STUFFING ON  PLAN_REQUEST_STUFFING.NO_REQUEST =  REPLACE (REQUEST_STUFFING.NO_REQUEST,'S', 'P')
                                    LEFT JOIN  V_MST_PBM emkl ON PLAN_REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM
                                    LEFT JOIN PLAN_CONTAINER_STUFFING ON PLAN_REQUEST_STUFFING.NO_REQUEST = PLAN_CONTAINER_STUFFING.NO_REQUEST
                                WHERE PLAN_REQUEST_STUFFING.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                     AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                 GROUP BY PLAN_REQUEST_STUFFING.APPROVE, PLAN_REQUEST_STUFFING.NO_REQUEST,PLAN_REQUEST_STUFFING.NO_PEB, PLAN_REQUEST_STUFFING.NO_NPE,PLAN_REQUEST_STUFFING.NM_KAPAL, PLAN_REQUEST_STUFFING.VOYAGE,
                                         NVL (REQUEST_STUFFING.NO_REQUEST, 'blm di approve'), REQUEST_STUFFING.NOTA, REQUEST_STUFFING.KOREKSI, emkl.NM_PBM, PLAN_REQUEST_STUFFING.TGL_REQUEST,
                                         PLAN_REQUEST_STUFFING.KD_CONSIGNEE
                                ORDER BY PLAN_REQUEST_STUFFING.TGL_REQUEST DESC)
                                where rownum <= $request->length+20";
            } else if ((isset($from)) && (isset($to)) && (isset($no_req))) {

                $query_list = "SELECT * FROM (SELECT PLAN_REQUEST_STUFFING.TGL_REQUEST, PLAN_REQUEST_STUFFING.APPROVE,PLAN_REQUEST_STUFFING.NO_REQUEST,
                                PLAN_REQUEST_STUFFING.NO_PEB, PLAN_REQUEST_STUFFING.NO_NPE,PLAN_REQUEST_STUFFING.NM_KAPAL, PLAN_REQUEST_STUFFING.VOYAGE,
                                         NVL (REQUEST_STUFFING.NO_REQUEST, 'blm di approve') NO_REQUEST_APP,
                                         REQUEST_STUFFING.NOTA, REQUEST_STUFFING.KOREKSI, emkl.NM_PBM AS NAMA_PEMILIK
                                    FROM PLAN_REQUEST_STUFFING LEFT JOIN REQUEST_STUFFING ON  PLAN_REQUEST_STUFFING.NO_REQUEST =  REPLACE (REQUEST_STUFFING.NO_REQUEST,'S', 'P')
                                    LEFT JOIN  V_MST_PBM emkl ON PLAN_REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM
                                    LEFT JOIN PLAN_CONTAINER_STUFFING ON PLAN_REQUEST_STUFFING.NO_REQUEST = PLAN_CONTAINER_STUFFING.NO_REQUEST
                                WHERE PLAN_REQUEST_STUFFING.NO_REQUEST = '$no_req'
                                     AND PLAN_REQUEST_STUFFING.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY-MM-DD ')
                                     AND TO_DATE (  CONCAT('$to', '23:59:59'), 'YYYY-MM-DD HH24:MI:SS')
                                 GROUP BY PLAN_REQUEST_STUFFING.APPROVE, PLAN_REQUEST_STUFFING.NO_REQUEST,PLAN_REQUEST_STUFFING.NO_PEB, PLAN_REQUEST_STUFFING.NO_NPE,PLAN_REQUEST_STUFFING.NM_KAPAL, PLAN_REQUEST_STUFFING.VOYAGE,
                                         NVL (REQUEST_STUFFING.NO_REQUEST, 'blm di approve'), REQUEST_STUFFING.NOTA, REQUEST_STUFFING.KOREKSI, emkl.NM_PBM, PLAN_REQUEST_STUFFING.TGL_REQUEST,
                                         PLAN_REQUEST_STUFFING.KD_CONSIGNEE
                                ORDER BY PLAN_REQUEST_STUFFING.TGL_REQUEST DESC)
                                WHERE rownum <= $request->length+20";
            }
        } else {
            $query_list        = "SELECT *
                              FROM (  SELECT PLAN_REQUEST_STUFFING.TGL_REQUEST,
                                             PLAN_REQUEST_STUFFING.APPROVE,
                                             PLAN_REQUEST_STUFFING.NO_REQUEST,
                                             PLAN_REQUEST_STUFFING.NO_PEB,
                                             PLAN_REQUEST_STUFFING.NO_NPE,
                                             PLAN_REQUEST_STUFFING.NM_KAPAL,
                                             PLAN_REQUEST_STUFFING.VOYAGE,
                                             NVL (REQUEST_STUFFING.NO_REQUEST, 'blm di approve')
                                                NO_REQUEST_APP,
                                             REQUEST_STUFFING.NOTA,
                                             REQUEST_STUFFING.KOREKSI,
                                             emkl.NM_PBM AS NAMA_PEMILIK
                                        FROM PLAN_REQUEST_STUFFING
                                             LEFT JOIN REQUEST_STUFFING
                                                ON PLAN_REQUEST_STUFFING.NO_REQUEST_APP =
                                                      REQUEST_STUFFING.NO_REQUEST
                                             LEFT JOIN V_MST_PBM emkl
                                                ON PLAN_REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM
                                                   AND emkl.KD_CABANG = '05'
                                             LEFT JOIN PLAN_CONTAINER_STUFFING
                                                ON PLAN_REQUEST_STUFFING.NO_REQUEST =
                                                      PLAN_CONTAINER_STUFFING.NO_REQUEST
                                       WHERE PLAN_REQUEST_STUFFING.EARLY_STUFFING IS NULL
                                    GROUP BY PLAN_REQUEST_STUFFING.APPROVE,
                                             PLAN_REQUEST_STUFFING.NO_REQUEST,
                                             PLAN_REQUEST_STUFFING.NO_PEB,
                                             PLAN_REQUEST_STUFFING.NO_NPE,
                                             PLAN_REQUEST_STUFFING.NM_KAPAL,
                                             PLAN_REQUEST_STUFFING.VOYAGE,
                                             NVL (REQUEST_STUFFING.NO_REQUEST, 'blm di approve'),
                                             REQUEST_STUFFING.NOTA,
                                             REQUEST_STUFFING.KOREKSI,
                                             emkl.NM_PBM,
                                             PLAN_REQUEST_STUFFING.TGL_REQUEST,
                                             PLAN_REQUEST_STUFFING.KD_CONSIGNEE
                                    ORDER BY PLAN_REQUEST_STUFFING.TGL_REQUEST DESC)
                                    WHERE ROWNUM <=$request->length+20";
        }
        return DB::connection('uster')->select($query_list);
    }

    public function checkNotaPerencaaanStuffing($noReq)
    {
        $queryCheck = "SELECT NOTA, KOREKSI, LUNAS FROM request_stuffing LEFT JOIN nota_stuffing ON request_stuffing.NO_REQUEST = nota_stuffing.NO_REQUEST WHERE request_stuffing.NO_REQUEST = REPLACE (:noReq,'P', 'S')";

        $resultCheck = DB::connection('uster')->select($queryCheck, ['noReq' => $noReq]);
        $rowCheck = count($resultCheck) > 0 ? $resultCheck[0] : null;

        $nota        = $rowCheck->nota;
        $koreksi    = $rowCheck->koreksi;
        $lunas        = $rowCheck->lunas;

        if ($lunas == 'NO') {
            return '<a href="' . route('uster.new_request.stuffing.stuffing_plan.view', ['no_req' => $noReq]) . '" target="_blank" class="btn btn-primary w-100"><b><i class="fas fa-edit"></i> Edit Request</b></a>';
        } else {
            if (($rowCheck->nota <> 'Y') and ($rowCheck->koreksi <> 'Y')) {
                return '<a href="' . route('uster.new_request.stuffing.stuffing_plan.view', ['no_req' => $noReq]) . '" target="_blank" class="btn btn-primary w-100 "><b><i class="fas fa-edit"></i> Edit Request</b></a>';
            } else if (($rowCheck->nota == NULL) and ($rowCheck->koreksi == NULL)) {
                return '<a href="' . route('uster.new_request.stuffing.stuffing_plan.view', ['no_req' => $noReq]) . '" target="_blank" class="btn btn-primary w-100"><b><i class="fas fa-edit"></i> Edit Request</b></a>';
            } else if (($rowCheck->nota == 'Y') and ($rowCheck->koreksi <> 'Y')) {
                return '<a href="' . route('uster.new_request.stuffing.stuffing_plan.overview', ['no_req' => $noReq]) . '" target="_blank" class="btn btn-success w-100"><i class="fas fa-print"></i> Nota sudah cetak</a>';
            } else if (($rowCheck->nota == 'Y') and ($rowCheck->koreksi == 'Y')) {
                return '<a href="' . route('uster.new_request.stuffing.stuffing_plan.overview', ['no_req' => $noReq]) . '" target="_blank" class="btn btn-success w-100"><i class="fas fa-print"></i> Nota sudah cetak</a>';
            } else if (($rowCheck->nota <> 'Y') and ($rowCheck->koreksi == 'Y')) {
                return '<a href="' . route('uster.new_request.stuffing.stuffing_plan.view', ['no_req' => $noReq]) . '" target="_blank" class="btn btn-danger w-100"><b><i class="fas fa-edit"></i> Edit Request</b></a>';
            }
        }
    }

    function getVesselPalapa($request)
    {
        $term        = strtoupper($request->term);


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
            AND SYSDATE BETWEEN TO_DATE(OPEN_STACK, 'YYYYMMDDHH24MISS') AND TO_DATE(CLOSSING_TIME, 'YYYYMMDDHH24MISS')
            AND (VESSEL LIKE '%$term%'
            OR VOYAGE_IN LIKE '%$term%'
            OR VOYAGE_OUT LIKE '%$term%'
            OR VOYAGE LIKE '%$term%')
            ORDER BY VESSEL, VOYAGE_IN DESC";

        return DB::connection('opus_repo')->select($query);
    }

    function getCommodityByName($request)
    {
        $komo    = strtoupper($request->term);


        $query    = "select * from PETIKEMAS_CABANG.MST_COMMODITY@DBINT_KAPALPROD WHERE NM_COMMODITY LIKE '%$komo%' ORDER BY NM_COMMODITY ASC";

        return DB::connection('uster')->select($query);
    }

    function getContainerByName($request)
    {
        $no_cont        = strtoupper($request->term);
        $remark_sp2        = strtoupper($request->remark_sp2);

        if ($remark_sp2 == 'Y') {
            $result = "SELECT cd.no_container,
		       mc.size_ kd_size,
		       mc.type_ kd_type,
		       TO_CHAR (cd.tgl_delivery, 'DD-MM-YYYY') tgl_bongkar,
		       TO_CHAR (cd.tgl_delivery+4, 'DD-MM-YYYY') EMPTY_SD,
		       cd.status status_cont,
		       mc.no_booking bp_id,
		       '' no_ukk,
		       'DEPO' asal_cont,
		       ba.name blok_,
		       pl.slot_,
		       pl.row_,
		       pl.tier_,
		       '' voyage_in,
		       '' nm_kapal,
		       '' nm_agen,
		       CASE
		          WHEN rd.status = 'PERP' THEN TO_CHAR (cd.start_perp, 'DD-MM-YYYY')
		          ELSE TO_CHAR (cd.start_stack, 'DD-MM-YYYY')
		       END
		          tgl_stack,
		       cd.no_request
		  FROM container_delivery cd
		       JOIN master_container mc
		          ON cd.no_container = mc.no_container
		       JOIN request_delivery rd
		          ON cd.no_request = rd.no_request
		       LEFT JOIN placement pl
		          ON mc.no_container = pl.no_container
		       LEFT JOIN blocking_area ba
		          ON pl.id_blocking_area = ba.id
		 WHERE     cd.remark_batal = 'Y'
		       AND cd.aktif = 'T'
		       AND cd.no_container = '$no_cont'";
        } else {

            $result =  "SELECT DISTINCT MASTER_CONTAINER.NO_CONTAINER,
                    MASTER_CONTAINER.SIZE_ KD_SIZE,
                    MASTER_CONTAINER.TYPE_ KD_TYPE,
                    '' TGL_BONGKAR,
                    '' EMPTY_SD,
                    HISTORY_CONTAINER.STATUS_CONT,
                    '' BP_ID,
                    0 NO_UKK,
                    'DEPO' ASAL_CONT,
                    blocking_area.NAME BLOK_,
                    TO_CHAR(placement.SLOT_) SLOT_,
                    TO_CHAR(placement.ROW_) ROW_,
                    TO_CHAR(placement.TIER_) TIER_,
                    '' VOYAGE_IN,
                    '' VOYAGE_OUT,
                    '' NM_KAPAL,
                    '' NM_AGEN,
                    '' CALL_SIGN,
                    '' VESSEL_CODE,
                    '' TGL_STACK,
                    '' NO_BOOKING
            FROM MASTER_CONTAINER
            INNER JOIN HISTORY_CONTAINER
            ON MASTER_CONTAINER.NO_CONTAINER = HISTORY_CONTAINER.NO_CONTAINER
            AND HISTORY_CONTAINER.TGL_UPDATE = (SELECT MAX(HISTORY_CONTAINER.TGL_UPDATE) FROM HISTORY_CONTAINER WHERE NO_CONTAINER LIKE '$no_cont%' )
            left JOIN
            PLACEMENT
            ON placement.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
            left JOIN
            BLOCKING_AREA
            ON blocking_area.ID = placement.ID_BLOCKING_AREA
            WHERE
            MASTER_CONTAINER.LOCATION = 'IN_YARD'
            AND HISTORY_CONTAINER.STATUS_CONT = 'MTY'
            AND master_container.NO_CONTAINER NOT IN (SELECT container_stuffing.NO_CONTAINER FROM container_stuffing where container_stuffing.no_container LIKE '$no_cont%' AND container_stuffing.AKTIF = 'Y' )
            AND MASTER_CONTAINER.NO_CONTAINER LIKE '$no_cont%'
            AND HISTORY_CONTAINER.KEGIATAN IN ('REALISASI STRIPPING', 'GATE IN', 'REQUEST DELIVERY', 'REQUEST BATALMUAT','BATAL STUFFING')
            AND placement.SLOT_ = (SELECT max(SLOT_) FROM PLACEMENT WHERE NO_CONTAINER LIKE '$no_cont%' ) ";
        }

        return DB::connection('uster')->select($result);;
    }


    function getInfoStuffingPlan()
    {
        $qselect = "SELECT
                A.NO,
                A.KEGIATAN,
                A.JUMLAH H,
                B.JUMLAH H1,
                C.JUMLAH H2,
                D.JUMLAH H3
            FROM
                (
                SELECT
                    1 NO,
                    'KAPASITAS' KEGIATAN,
                    SUM(CAPACITY)JUMLAH
                FROM
                    BLOCKING_AREA
                WHERE
                    KETERANGAN = 'STUFFING'
            UNION
                SELECT
                    2 NO,
                    'BERJALAN' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) BETWEEN to_date(TGL_APPROVE) AND to_date(TGL_APPROVE) + 5
            UNION
                SELECT
                    3 NO,
                    'RENCANA MULAI' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) = TGL_APPROVE + 1
            UNION
                SELECT
                    4 NO,
                    'SELESAI' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) = to_date(TGL_APPROVE) + 5
            UNION
                SELECT
                    5 NO,
                    'SISA KAPASITAS' KEGIATAN,
                    (
                    SELECT
                        SUM(CAPACITY)JUMLAH
                    FROM
                        BLOCKING_AREA
                    WHERE
                        KETERANGAN = 'STUFFING')
                        - (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate) BETWEEN to_date(TGL_APPROVE) AND to_date(TGL_APPROVE) + 5)
                        - (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate) = TGL_APPROVE + 1)
                        + (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate) = to_date(TGL_APPROVE) + 5)
                FROM
                    DUAL) A,
                (
                SELECT
                    1 NO,
                    'KAPASITAS' KEGIATAN,
                    SUM(CAPACITY)JUMLAH
                FROM
                    BLOCKING_AREA
                WHERE
                    KETERANGAN = 'STUFFING'
            UNION
                SELECT
                    2 NO,
                    'BERJALAN' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) + 1 BETWEEN to_date(TGL_APPROVE) AND to_date(TGL_APPROVE) + 5
            UNION
                SELECT
                    3 NO,
                    'RENCANA MULAI' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) + 1 = TGL_APPROVE + 1
            UNION
                SELECT
                    4 NO,
                    'SELESAI' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) + 1 = to_date(TGL_APPROVE) + 5
            UNION
                SELECT
                    5 NO,
                    'SISA KAPASITAS' KEGIATAN,
                    (
                    SELECT
                        SUM(CAPACITY)JUMLAH
                    FROM
                        BLOCKING_AREA
                    WHERE
                        KETERANGAN = 'STUFFING')
                        - (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 1 BETWEEN to_date(TGL_APPROVE) AND to_date(TGL_APPROVE) + 5)
                        - (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 1 = TGL_APPROVE + 1)
                        + (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 1 = to_date(TGL_APPROVE) + 5)
                FROM
                    DUAL) B,
                (
                SELECT
                    1 NO,
                    'KAPASITAS' KEGIATAN,
                    SUM(CAPACITY)JUMLAH
                FROM
                    BLOCKING_AREA
                WHERE
                    KETERANGAN = 'STUFFING'
            UNION
                SELECT
                    2 NO,
                    'BERJALAN' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) + 2 BETWEEN to_date(TGL_APPROVE) AND to_date(TGL_APPROVE) + 5
            UNION
                SELECT
                    3 NO,
                    'RENCANA MULAI' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) + 2 = TGL_APPROVE + 1
            UNION
                SELECT
                    4 NO,
                    'SELESAI' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) + 2 = to_date(TGL_APPROVE) + 5
            UNION
                SELECT
                    5 NO,
                    'SISA KAPASITAS' KEGIATAN,
                    (
                    SELECT
                        SUM(CAPACITY)JUMLAH
                    FROM
                        BLOCKING_AREA
                    WHERE
                        KETERANGAN = 'STUFFING')
                        - (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 2 BETWEEN to_date(TGL_APPROVE) AND to_date(TGL_APPROVE) + 5)
                        - (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 2 = TGL_APPROVE + 1)
                        + (
                    SELECT
                        COUNT (NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 2 = to_date(TGL_APPROVE) + 5)
                FROM
                    DUAL) C,
                (
                SELECT
                    1 NO,
                    'KAPASITAS' KEGIATAN,
                    SUM(CAPACITY)JUMLAH
                FROM
                    BLOCKING_AREA
                WHERE
                    KETERANGAN = 'STUFFING'
            UNION
                SELECT
                    2 NO,
                    'BERJALAN' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate) + 3 BETWEEN to_date(TGL_APPROVE) AND to_date(TGL_APPROVE) + 5
            UNION
                SELECT
                    3 NO,
                    'RENCANA MULAI' KEGIATAN,
                    COUNT (NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate)+ 3 = TGL_APPROVE + 1
            UNION
                SELECT
                    4 NO,
                    'SELESAI' KEGIATAN ,
                    count(NO_CONTAINER) JUMLAH
                FROM
                    CONTAINER_STUFFING
                WHERE
                    to_date(sysdate)+ 3 = to_date(TGL_APPROVE)+ 5
            UNION
                SELECT
                    5 NO,
                    'SISA KAPASITAS' KEGIATAN,
                    (
                    SELECT
                        SUM(CAPACITY)JUMLAH
                    FROM
                        BLOCKING_AREA
                    WHERE
                        KETERANGAN = 'STUFFING')
                            -(
                    SELECT
                        count(NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 3 BETWEEN to_date(TGL_APPROVE) AND to_date(TGL_APPROVE)+ 5)
                            -(
                    SELECT
                        count(NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 3 = TGL_APPROVE + 1)
                            +(
                    SELECT
                        count(NO_CONTAINER) JUMLAH
                    FROM
                        CONTAINER_STUFFING
                    WHERE
                        to_date(sysdate)+ 3 = to_date(TGL_APPROVE)+ 5)
                FROM
                    DUAL) D
            WHERE
                A.NO = B.NO
                AND B.NO = C.NO
                AND C.NO = D.NO";
        $qselect =  DB::connection('uster')->select($qselect);


        $tanggalx = "SELECT TO_CHAR(SYSDATE,'dd/mm/yy') H, TO_CHAR(SYSDATE+1,'dd/mm/yy') H_1, TO_CHAR(SYSDATE+2,'dd/mm/yy') H_2, TO_CHAR(SYSDATE+3,'dd/mm/yy') H_3 FROM DUAL";
        $tanggalx =  DB::connection('uster')->select($tanggalx);


        $q_capacity = "SELECT COUNT(PL.NO_CONTAINER) STF
					FROM PLACEMENT PL JOIN BLOCKING_AREA BA
					ON PL.ID_BLOCKING_AREA = BA.ID
					WHERE BA.KETERANGAN = 'STUFFING'";
        $q_capacity =  DB::connection('uster')->select($q_capacity);

        return [
            'row_cap' => $q_capacity,
            'tanggal' =>  $tanggalx,
            'row' =>  $qselect,
        ];
    }

    function listContainerOverview($no_req)
    {
        $query_list = "SELECT DISTINCT PLAN_CONTAINER_STUFFING.*,
                TO_CHAR(PLAN_CONTAINER_STUFFING.TGL_APPROVE,'dd-mm-yyyy') APPROVE,
                TO_CHAR(PLAN_CONTAINER_STUFFING.START_STACK,'dd-mm-yyyy') STACK,
                CASE WHEN PLAN_CONTAINER_STUFFING.TYPE_STUFFING = 'STUFFING_GUD_TRUCK' THEN 'TRUCK'
                WHEN PLAN_CONTAINER_STUFFING.TYPE_STUFFING = 'STUFFING_LAP' THEN 'LAPANGAN'
                ELSE 'TONGKANG' END TYPE_STUFFING,
            PLAN_CONTAINER_STUFFING.COMMODITY COMMO,
            M.SIZE_ KD_SIZE, M.TYPE_ KD_TYPE
        FROM PLAN_CONTAINER_STUFFING LEFT JOIN MASTER_CONTAINER M
        ON PLAN_CONTAINER_STUFFING.NO_CONTAINER = M.NO_CONTAINER
        WHERE PLAN_CONTAINER_STUFFING.NO_REQUEST = '$no_req'";

        return DB::connection('uster')->select($query_list);
    }


    function GetContainerStuffingById($no_req_stuf, $no_cont)
    {
        $no_req_stuf = str_replace('P', 'S', $no_req_stuf);

        $query_list_cek = "SELECT DISTINCT CONTAINER_STUFFING.NO_REQUEST
                        FROM CONTAINER_STUFFING
                        LEFT JOIN MASTER_CONTAINER M ON CONTAINER_STUFFING.NO_CONTAINER = M.NO_CONTAINER
                        WHERE CONTAINER_STUFFING.NO_REQUEST = '$no_req_stuf'
                        AND CONTAINER_STUFFING.NO_CONTAINER = '$no_cont'";

        return DB::connection('uster')->selectOne($query_list_cek);
    }

    function GetPmbByName($request)
    {
        $nama    = strtoupper($request->term);

        $query = "SELECT pbm.KD_PBM,pbm.NM_PBM,pbm.ALMT_PBM,pbm.NO_NPWP_PBM,pbm.NO_ACCOUNT_PBM FROM V_MST_PBM pbm
        where pbm.KD_CABANG='05' AND UPPER(pbm.NM_PBM) LIKE '%$nama%' AND PELANGGAN_AKTIF = '1' AND pbm.ALMT_PBM IS NOT NULL";

        return DB::connection('uster')->selectOne($query);
    }

    function getNameRequestStuffingPlan()
    {
        $query_cek = "select NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_REQUEST,8,13)))+1,6,0),'000001') AS JUM,
        TO_CHAR(SYSDATE, 'MM') AS MONTH,
        TO_CHAR(SYSDATE, 'YY') AS YEAR
            FROM REQUEST_STUFFING
            WHERE TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)
            AND request_stuffing.NO_REQUEST LIKE '%SFP%'";

        return  DB::connection('uster')->selectOne($query_cek);
    }

    function CheckCapacityTPK()
    {
        return 'OKE';
    }


    function getTanggalStack($request)
    {
        $no_cont = $request->no_cont;
        $row_tgl_stack_depo = DB::connection('uster')->select("SELECT TGL_UPDATE, NO_REQUEST, KEGIATAN
            FROM HISTORY_CONTAINER
            WHERE no_container = '$no_cont'
            AND kegiatan IN ('GATE IN','REALISASI STRIPPING')
            ORDER BY TGL_UPDATE DESC")[0];

        $ex_keg = $row_tgl_stack_depo->kegiatan;
        $no_re_st = $row_tgl_stack_depo->no_request;
        if ($ex_keg == "REALISASI STRIPPING") {
            $tgl_stack = DB::connection('uster')->selectOne("SELECT TGL_REALISASI FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'")->tgl_realisasi;
        } else if ($ex_keg == "GATE IN") {
            $tgl_stack = DB::connection('uster')->selectOne("SELECT TGL_IN FROM GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'")->tgl_in;
        }

        $hasil = DB::connection('uster')->selectOne("SELECT TO_CHAR(TO_DATE('$tgl_stack','YYYY-MM-DD HH24:MI:SS'),'YYYY-MM-DD') TGL_BONGKAR, TO_CHAR(TO_DATE('$tgl_stack','YYYY-MM-DD HH24:MI:SS')+4,'YYYY-MM-DD') EMPTY_SD FROM DUAL");

        return $hasil;
    }

    function storeStuffingPlan($request)
    {
        $ACC_EMKL           = $request->input('ACC_EMKL');
        $ID_EMKL            = $request->input('ID_EMKL');
        $ACC_PNKN           = $request->input('ACC_PNKN');
        $ID_PNKN_BY         = $request->input('ID_PNKN_BY');
        $ID_PENUMPUKAN      = $request->input('ID_PENUMPUKAN');
        $NM_PENUMPUKAN      = $request->input('PENUMPUKAN');
        $ALMT_PENUMPUKAN    = $request->input('ALMT_PENUMPUKAN');
        $NPWP_PENUMPUKAN    = $request->input('NPWP_PENUMPUKAN');
        $NO_DOC             = $request->input('NO_DOC');
        $NO_JPB             = $request->input('NO_JPB');
        $NO_DO              = $request->input('NO_DO');
        $NO_BL              = $request->input('NO_BL');
        $NO_SPPB            = $request->input('NO_SPPB');
        $TGL_SPPB           = $request->input('TGL_SPPB');
        $BPRP               = $request->input('BPRP');
        $KETERANGAN         = $request->input('keterangan');
        $ID_USER            = session('LOGGED_STORAGE');
        $id_yard            = session('IDYARD_STORAGE');
        $TGL_BERANGKAT      = $request->input('TGL_BERANGKAT');
        $TGL_REQ            = $request->input('TGL_REQ');
        $PEB                = $request->input('NO_PEB');
        $NPE                = $request->input('NO_NPE');
        $KD_PELABUHAN_ASAL = $request->input('KD_PELABUHAN_ASAL');
        $KD_PELABUHAN_TUJUAN = $request->input('KD_PELABUHAN_TUJUAN');
        $NM_KAPAL           = $request->input('NM_KAPAL');
        $VOYAGE_IN          = $request->input('VOYAGE_IN');
        $VOYAGE_OUT         = $request->input('VOYAGE_OUT');
        $NO_BOOKING         = $request->input('NO_BOOKING');
        $CALL_SIGN          = $request->input('CALL_SIGN');
        $NM_USER            = session('NAME');
        $NO_UKK             = $request->input('NO_UKK');
        $SHIFT_RFR          = $request->input('SHIFT_RFR');
        $TGL_MUAT           = $request->input('TGL_MUAT');
        $TGL_STACKING       = $request->input('TGL_STACKING');
        $DI                 = $request->input('DI');
        $OPEN_STACK         = $request->input('OPEN_STACK');
        $NM_AGEN            = $request->input('NM_AGEN');
        $KD_AGEN            = $request->input('KD_AGEN');
        $CONT_LIMIT         = $request->input('CONT_LIMIT');
        $KD_KAPAL           = $request->input('KD_KAPAL');
        $ETD                = $request->input('ETD');
        $ETA                = $request->input('ETA');
        $VOYAGE             = $request->input('VOYAGE');
        $YARD_STACK         = $request->input('YARD_STACK');


        DB::connection('uster')->beginTransaction();

        try {

            if ($TGL_SPPB == NULL) {
                $TGL_SPPB = '';
            }
            $pdo = DB::connection('uster')->getPdo();
            error_reporting(E_ALL);
            $param_req = array(
                "in_accpbm"  => $ACC_EMKL,
                "in_pbm"     => $ID_EMKL,
                "in_accpbm_pnkn" => $ACC_PNKN,
                "in_pbm_pnkn" => $ID_PNKN_BY,
                "in_do" => $NO_DO,
                "in_doc" => $NO_DOC,
                "in_datesppb" => $TGL_SPPB,
                "in_nosppb" => $NO_SPPB,
                "in_keterangan" => $KETERANGAN,
                "in_peb" => $PEB,
                "in_npe" => $NPE,
                "in_bl" => $NO_BL,
                "in_jpb" => $NO_JPB,
                "in_bprp" => $BPRP,
                "in_user" => $ID_USER,
                "in_di" => $DI,
                "in_vessel" => $NM_KAPAL,
                "in_voyin" => $VOYAGE_IN,
                "in_voyout" => $VOYAGE_OUT,
                "in_idvsb" => $NO_UKK,
                "in_nobooking" => $NO_BOOKING,
                "in_callsign" => $CALL_SIGN,
                "in_kdkapal" => $KD_KAPAL,
                "in_etd" => $ETD,
                "in_eta" => $ETA,
                "in_openstack" => $OPEN_STACK,
                "in_nmagen" => $NM_AGEN,
                "in_kdagen" => $KD_AGEN,
                "in_pod" => $KD_PELABUHAN_TUJUAN,
                "in_pol" => $KD_PELABUHAN_ASAL,
                "in_voyage" => $VOYAGE,
                "in_yardstack" => $YARD_STACK,
                "out_noreq" => "",
                "out_msg"    => ""
            );

            // echo var_dump($param_req);
            // die;

            $query_req_s = "DECLARE
            BEGIN
                pack_create_req_stuffing.create_header_stuf_praya(
                    :in_accpbm, :in_pbm, :in_accpbm_pnkn, :in_pbm_pnkn,
                    :in_do, :in_doc, :in_datesppb, :in_nosppb, :in_keterangan,
                    :in_peb, :in_npe, :in_bl, :in_jpb, :in_bprp, :in_user,
                    :in_di, :in_vessel, :in_voyin, :in_voyout, :in_idvsb,
                    :in_nobooking, :in_callsign, :in_kdkapal, :in_etd,
                    :in_eta, :in_openstack, :in_nmagen, :in_kdagen,
                    :in_pod, :in_pol, :in_voyage, :in_yardstack,
                    :out_noreq, :out_msg
                );
            END;";
            // Menyiapkan statement
            $stmt = $pdo->prepare($query_req_s);

            // Mengikat parameter-parameter input
            $stmt->bindParam(':in_accpbm', $param_req["in_accpbm"], PDO::PARAM_STR);
            $stmt->bindParam(':in_pbm', $param_req["in_pbm"], PDO::PARAM_STR);
            $stmt->bindParam(':in_accpbm_pnkn', $param_req["in_accpbm_pnkn"], PDO::PARAM_STR);
            $stmt->bindParam(':in_pbm_pnkn', $param_req["in_pbm_pnkn"], PDO::PARAM_STR);
            $stmt->bindParam(':in_do', $param_req["in_do"], PDO::PARAM_STR);
            $stmt->bindParam(':in_doc', $param_req["in_doc"], PDO::PARAM_STR);
            $stmt->bindParam(':in_datesppb', $param_req["in_datesppb"], PDO::PARAM_STR);
            $stmt->bindParam(':in_nosppb', $param_req["in_nosppb"], PDO::PARAM_STR);
            $stmt->bindParam(':in_keterangan', $param_req["in_keterangan"], PDO::PARAM_STR);
            $stmt->bindParam(':in_peb', $param_req["in_peb"], PDO::PARAM_STR);
            $stmt->bindParam(':in_npe', $param_req["in_npe"], PDO::PARAM_STR);
            $stmt->bindParam(':in_bl', $param_req["in_bl"], PDO::PARAM_STR);
            $stmt->bindParam(':in_jpb', $param_req["in_jpb"], PDO::PARAM_STR);
            $stmt->bindParam(':in_bprp', $param_req["in_bprp"], PDO::PARAM_STR);
            $stmt->bindParam(':in_user', $param_req["in_user"], PDO::PARAM_STR);
            $stmt->bindParam(':in_di', $param_req["in_di"], PDO::PARAM_STR);
            $stmt->bindParam(':in_vessel', $param_req["in_vessel"], PDO::PARAM_STR);
            $stmt->bindParam(':in_voyin', $param_req["in_voyin"], PDO::PARAM_STR);
            $stmt->bindParam(':in_voyout', $param_req["in_voyout"], PDO::PARAM_STR);
            $stmt->bindParam(':in_idvsb', $param_req["in_idvsb"], PDO::PARAM_STR);
            $stmt->bindParam(':in_nobooking', $param_req["in_nobooking"], PDO::PARAM_STR);
            $stmt->bindParam(':in_callsign', $param_req["in_callsign"], PDO::PARAM_STR);
            $stmt->bindParam(':in_kdkapal', $param_req["in_kdkapal"], PDO::PARAM_STR);
            $stmt->bindParam(':in_etd', $param_req["in_etd"], PDO::PARAM_STR);
            $stmt->bindParam(':in_eta', $param_req["in_eta"], PDO::PARAM_STR);
            $stmt->bindParam(':in_openstack', $param_req["in_openstack"], PDO::PARAM_STR);
            $stmt->bindParam(':in_nmagen', $param_req["in_nmagen"], PDO::PARAM_STR);
            $stmt->bindParam(':in_kdagen', $param_req["in_kdagen"], PDO::PARAM_STR);
            $stmt->bindParam(':in_pod', $param_req["in_pod"], PDO::PARAM_STR);
            $stmt->bindParam(':in_pol', $param_req["in_pol"], PDO::PARAM_STR);
            $stmt->bindParam(':in_voyage', $param_req["in_voyage"], PDO::PARAM_STR);
            $stmt->bindParam(':in_yardstack', $param_req["in_yardstack"], PDO::PARAM_STR);

            // Mengikat parameter-parameter output
            $stmt->bindParam(':out_noreq', $param_req["out_noreq"], PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
            $stmt->bindParam(':out_msg', $param_req["out_msg"], PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);

            // Menjalankan statement
            $stmt->execute();

            // Mendapatkan nilai dari parameter output
            $no_req_s = $param_req["out_noreq"];
            $msg      = $param_req["out_msg"];


            if ($msg == 'OK') {
                $pdo->commit();
                return response()->json([
                    'no_req_s' => $no_req_s,
                    'msg' => $msg
                ]);
            } else {
                return response()->json([
                    'no_req_s' => $no_req_s,
                    'msg' => $msg
                ]);
            }
        } catch (\Exception $e) {
            DB::connection('uster')->rollBack();
            return  "Terjadi kesalahan: " . $e->getMessage();
        }
    }

    function containerApprove($request)
    {

        $tgl_approve = $request->input("tgl_approve");
        $no_cont = $request->input("no_cont");
        $no_req = $request->input("no_req");



        try {
            DB::connection('uster')->beginTransaction();


            $query_cek1        = "SELECT tes.NO_REQUEST,
								CASE SUBSTR(KEGIATAN,9)
									WHEN 'RECEIVING' THEN (SELECT CONCAT('RECEIVING_',a.RECEIVING_DARI) FROM request_receiving a WHERE a.NO_REQUEST = tes.NO_REQUEST)
									ELSE SUBSTR(KEGIATAN,9)
								END KEGIATAN FROM (SELECT TGL_UPDATE, NO_REQUEST,KEGIATAN FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI')) tes
								WHERE tes.TGL_UPDATE=(SELECT MAX(TGL_UPDATE) FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI'))";
            $row_cek1 =  DB::connection('uster')->selectOne($query_cek1);

            $no_request        = $row_cek1->no_request;
            $kegiatan        = $row_cek1->kegiatan;


            if ($kegiatan == 'RECEIVING_LUAR') {
                $query_cek1        = "SELECT SUBSTR(TO_CHAR(b.TGL_IN, 'MM/DD/YYYY'),1,10) START_STACK FROM GATE_IN b WHERE b.NO_CONTAINER = '$no_cont' AND b.NO_REQUEST = '$no_request'";
                $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                $asal_cont         = 'LUAR';
            } else if ($kegiatan == 'RECEIVING_TPK') {
                $query_cek1        = "SELECT TGL_BONGKAR START_STACK FROM container_receiving WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                $row_cek1    = DB::connection('uster')->selectOne($query_cek1);
                $asal_cont         = 'TPK';
            } else if ($kegiatan == 'STRIPPING') {
                $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'MM/DD/YYYY'),1,9) START_STACK FROM container_stripping WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                $row_cek1    = DB::connection('uster')->selectOne($query_cek1);
                $asal_cont         = 'DEPO';
            }

            $query_r = "SELECT * FROM PLAN_REQUEST_STUFFING WHERE NO_REQUEST = '$no_req'";
            $row_r = DB::connection('uster')->selectOne($query_r);

            $no_request = $row_r->no_request;
            $id_yard = $row_r->id_yard;
            $keterangan = $row_r->keterangan;
            $no_book = $row_r->no_booking;
            $tgl_req = $row_r->tgl_request;
            $dokumen = $row_r->no_dokumen;
            $jpb = $row_r->no_jpb;
            $bprp = $row_r->bprp;
            $rec = $row_r->no_request_receiving;
            $id_user = $row_r->id_user;
            $dev = $row_r->no_request_delivery;
            $consig = $row_r->kd_consignee;
            $tumpuk = $row_r->kd_penumpukan_oleh;
            $kapal = $row_r->nm_kapal;
            $peb = $row_r->no_peb;
            $npe = $row_r->no_npe;
            $voy = $row_r->voyage;
            $query_c = "SELECT DISTINCT    PLAN_CONTAINER_STUFFING.NO_CONTAINER,
								   PLAN_CONTAINER_STUFFING.HZ,
								   PLAN_CONTAINER_STUFFING.AKTIF,
								   PLAN_CONTAINER_STUFFING.TYPE_STUFFING,
								   PLAN_CONTAINER_STUFFING.ASAL_CONT,
								   PLAN_CONTAINER_STUFFING.NO_SEAL,
								   PLAN_CONTAINER_STUFFING.BERAT,
								   PLAN_CONTAINER_STUFFING.KETERANGAN,
								   PLAN_CONTAINER_STUFFING.TGL_APPROVE,
								   PLAN_CONTAINER_STUFFING.TGL_MULAI,
								   TO_CHAR(PLAN_CONTAINER_STUFFING.START_STACK,'dd-mm-yyyy') STACK,
								   PLAN_CONTAINER_STUFFING.COMMODITY,
								   PLAN_CONTAINER_STUFFING.KD_COMMODITY,
								   PLAN_CONTAINER_STUFFING.NO_REQ_SP2
					   FROM PLAN_CONTAINER_STUFFING
					   WHERE PLAN_CONTAINER_STUFFING.NO_REQUEST = '$no_req' AND PLAN_CONTAINER_STUFFING.NO_CONTAINER = '$no_cont'";
            $row_c = DB::connection('uster')->select($query_c);

            $no_req_stuf = str_replace('P', 'S', $no_req);
            $query_cek_request = "SELECT * FROM REQUEST_STUFFING WHERE NO_REQUEST = '$no_req_stuf'";
            $row_cek_request =  DB::connection('uster')->select($query_cek_request);


            //query apakah kontainer telah ada di table cont stuffing
            $query_cek_cont = "SELECT * FROM CONTAINER_STUFFING WHERE NO_REQUEST = '$no_req_stuf' AND NO_CONTAINER = '$no_cont'";
            $row_cek_cont =  DB::connection('uster')->select($query_cek_cont);

            if (count($row_cek_request) > 0 && count($row_cek_cont) > 0) { //jika request telah ada dan container telah ada
                // DB::connection('uster')->selectOne($query);

                // DB::connection('uster')->selectOne($query_tgl_app);
            } else if (count($row_cek_request) > 0 && count($row_cek_cont) == 0) {
                $no_req_stuf = str_replace('P', 'S', $no_req);
                foreach ($row_c as $rc) {
                    $hz = $rc->hz;
                    $cont = $rc->no_container ?? NULL;


                    //CEK TGL GATE
                    $tes = "select TO_CHAR(TGL_UPDATE,'dd/mm/rrrr') TGL_GATE from history_container where no_container = '$cont' AND KEGIATAN = 'BORDER GATE IN' AND TGL_UPDATE = (SELECT MAX(TGL_UPDATE) FROM history_container WHERE NO_CONTAINER = '$cont')";
                    $gate = DB::connection('uster')->selectOne($tes);
                    $tgl_gate = $gate->tgl_gate ?? '';



                    $start_stack = $rc->stack;
                    $aktif = $rc->aktif;
                    $comm = $rc->commodity;
                    $type_st = $rc->type_stuffing;
                    $asal = $rc->asal_cont;
                    $seal = $rc->no_seal;
                    $berat = $rc->berat;
                    $keterangan = $rc->keterangan;
                    $tgl_app = $rc->tgl_approve;
                    $tgl_mulai = $rc->tgl_mulai;
                    $req_sp2 = $rc->no_req_sp2;
                    if ($req_sp2 == NULL) {
                        $query_ic    = "INSERT INTO CONTAINER_STUFFING (	NO_CONTAINER, NO_REQUEST,
															AKTIF, HZ, COMMODITY, TYPE_STUFFING,
															START_STACK,
															ASAL_CONT, NO_SEAL, BERAT, KETERANGAN,
															TGL_APPROVE,
															TGL_GATE,
															START_PERP_PNKN)
													VALUES(	'$cont',
															'$no_req_stuf',
															'$aktif',
															'$hz',
															'$comm',
															'$type_st',
															to_date('" . $start_stack . "','dd-mm-rrrr'),
															'$asal',
															'$seal',
															'$berat',
															'$keterangan',
															SYSDATE,
															TO_DATE('$tgl_gate','dd-mm-rrrr'),
															TO_DATE('$tgl_app','YYYY-MM-DD HH24:MI:SS'))";
                    } else {
                        $query_ic    = "INSERT INTO CONTAINER_STUFFING (	NO_CONTAINER, NO_REQUEST,
															AKTIF, HZ, COMMODITY, TYPE_STUFFING,
															START_STACK,
															ASAL_CONT, NO_SEAL, BERAT, KETERANGAN,
															TGL_APPROVE,
															TGL_GATE,
															START_PERP_PNKN,
															END_STACK_PNKN,
															REMARK_SP2)
													VALUES(	'$cont',
															'$no_req_stuf',
															'$aktif',
															'$hz',
															'$comm',
															'$type_st',
															'$tgl_mulai',
															'$asal',
															'$seal',
															'$berat',
															'$keterangan',
															TO_DATE('$tgl_app','dd-mm-rrrr'),
															TO_DATE('$tgl_gate','dd-mm-rrrr'),
															TO_DATE('" . $start_stack . "','dd-mm-rrrr'),
															TO_DATE('$tgl_approve','YYYY-MM-DD HH24:MI:SS'),
															'Y')";
                    }
                    DB::connection('uster')->insert($query_ic);
                }
            } else {
                $no_req_stuf = str_replace('P', 'S', $no_req);
                $query_ir = "INSERT INTO REQUEST_STUFFING(NO_REQUEST, ID_YARD, CETAK_KARTU_SPPS, KETERANGAN, NO_BOOKING,
						TGL_REQUEST, NO_DOKUMEN, NO_JPB, BPRP, ID_PEMILIK, ID_EMKL, NO_REQUEST_RECEIVING, ID_USER,
						NO_REQUEST_DELIVERY, KD_CONSIGNEE, KD_PENUMPUKAN_OLEH, NM_KAPAL, NO_PEB, NO_NPE, VOYAGE, STUFFING_DARI)
						VALUES('$no_req_stuf',
						'$id_yard',
						0,
						'$keterangan',
						'$no_book',
						'$tgl_req',
						'$dokumen',
						'$jpb',
						'$bprp',
						'',
						'',
						'$rec',
						'$id_user',
						'$dev',
						'$consig',
						'$tumpuk',
						'$kapal',
						'$peb',
						'$npe',
						'$voy',
						'DEPO')";
                DB::connection('uster')->insert($query_ir);

                foreach ($row_c as $rc) {

                    $start_stack = $rc->stack;
                    $hz = $rc->hz;
                    $cont = $rc->no_container;
                    $aktif = $rc->aktif;
                    $comm = $rc->commodity;
                    $kd_comm = $rc->kd_commodity;
                    $type_st = $rc->type_stuffing;
                    $asal = $rc->asal_cont;
                    $seal = $rc->no_seal;
                    $berat = $rc->berat;
                    $keterangan = $rc->keterangan;
                    $stat_req = $rc->status_req ?? '';
                    $tgl_app = $rc->tgl_approve;

                    //CEK TGL GATE
                    $tes = "select TO_CHAR(TGL_UPDATE,'dd/mm/rrrr') TGL_GATE from history_container where no_container = '$cont' AND KEGIATAN = 'BORDER GATE IN' AND TGL_UPDATE = (SELECT MAX(TGL_UPDATE) FROM history_container WHERE NO_CONTAINER = '$cont')";
                    $gate = DB::connection('uster')->selectOne($tes);
                    $tgl_gate = $gate->tgl_gate ?? '';



                    $query_ic    = "INSERT INTO CONTAINER_STUFFING (NO_CONTAINER,
														   NO_REQUEST,
														   AKTIF,
														   HZ,
														   COMMODITY,
														   TYPE_STUFFING,
														   START_STACK,
														   ASAL_CONT,
														   NO_SEAL,
														   BERAT,
														   KETERANGAN,
														   STATUS_REQ,
														   TGL_APPROVE,
														   TGL_GATE,
														   TGL_MULAI_FULL,
														   TGL_SELESAI_FULL,
														   KD_COMMODITY)
													VALUES('$cont',
														   '$no_req_stuf',
														   '$aktif',
														   '$hz',
														   '$comm',
														   '$type_st',
														   to_date('" . $start_stack . "','dd-mm-rrrr'),
														   '$asal',
														   '$seal',
														   '$berat',
														   '$keterangan',
														   '$stat_req',
													       SYSDATE,
													       TO_DATE('$tgl_gate','dd-mm-rrrr'),
													       TO_DATE('$tgl_app','dd-mm-rrrr')+1,
													       TO_DATE('$tgl_app','YYYY-MM-DD HH24:MI:SS')+5,
														   '$kd_comm')";
                    DB::connection('uster')->insert($query_ic);
                }
            }

            $q_getcounter4 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont' ORDER BY COUNTER DESC";
            $rw_getcounter4 = DB::connection('uster')->selectOne($q_getcounter4);

            $cur_booking4  = $rw_getcounter4->no_booking;
            $cur_counter4  = $rw_getcounter4->counter;

            $history_stuf        = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, STATUS_CONT, NO_BOOKING, COUNTER)
        												  VALUES ('$no_cont','$no_req_stuf','REQUEST STUFFING',SYSDATE,'$id_user', '46', 'MTY', '$cur_booking4', '$cur_counter4')";
            DB::connection('uster')->insert($history_stuf);
            DB::connection('uster')->commit();
            return response()->json('OK');
        } catch (Exception $e) {
            DB::connection('uster')->rollBack();
            return response()->json($e->getMessage());
        }
    }

    function deleteContainer($request)
    {

        $no_cont    = $request->NO_CONT;
        $no_req        = $request->NO_REQ;
        $no_req2    = $request->NO_REQ2;


        try {
            DB::connection('uster')->beginTransaction();

            $query_master    = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
            $data         = DB::connection('uster')->selectOne($query_master);
            $counter        = $data->counter;
            $no_book        = $data->no_booking;



            //ambil data kegiatan kontainer yang masih satu siklus/counter
            $query_history    = "SELECT NO_REQUEST FROM history_container WHERE no_container = '$no_cont' and no_booking = '$no_book' and counter = '$counter'";
            $data_         = DB::connection('uster')->select($query_history);

            foreach ($data_ as $row) {
                $req = $row->no_request;
                $query_del2    = "DELETE FROM PLAN_CONTAINER_STUFFING WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$req'";
                $query_del3    = "DELETE FROM CONTAINER_STUFFING WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$req'";
                $query_del4    = "DELETE FROM CONTAINER_RECEIVING WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$req'";
                $query_del5    = "DELETE FROM CONTAINER_DELIVERY WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$req'";

                DB::connection('uster')->delete($query_del2);
                DB::connection('uster')->delete($query_del3);
                DB::connection('uster')->delete($query_del4);
                DB::connection('uster')->delete($query_del5);
            }

            $query_del6    = "DELETE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont' AND COUNTER = '$counter'";


            if (DB::connection('uster')->delete($query_del6)) {
                $query_update_counter = "UPDATE MASTER_CONTAINER SET COUNTER = '$counter'-1 WHERE NO_CONTAINER = '$no_cont'";
                DB::connection('uster')->delete($query_update_counter);
            }

            DB::connection('uster')->commit();
            return response()->json('OK');
        } catch (Exception $e) {
            DB::connection('uster')->rollBack();
            return response()->json($e->getMessage());
        }
    }

    function addContainer($request)
    {
        $nm_user        = session("NAME");
        $id_user        = session("LOGGED_STORAGE");
        $id_yard        = session("IDYARD_STORAGE");
        $no_cont        = $request->NO_CONT;
        $no_req_stuf    = $request->NO_REQ_STUF;
        $no_req_rec        = $request->NO_REQ_REC;
        $no_req_del        = $request->NO_REQ_DEL;
        $no_do            = $request->NO_DO;
        $no_bl            = $request->NO_BL;
        $hz                = $request->BERBAHAYA;
        $commodity        = $request->COMMODITY;
        $kd_commodity    = $request->KD_COMMODITY;
        $type_stuffing    = $request->JENIS;
        $no_seal        = $request->NO_SEAL;
        $berat            = $request->BERAT;
        $keterangan        = $request->KETERANGAN;
        $no_req_ict        = $request->NO_REQ_ICT;
        $size            = $request->SIZE;
        $type            = $request->TYPE;
        $hz                = $request->BERBAHAYA;
        $depo_tujuan    = $request->DEPO_TUJUAN;
        $no_booking        = $request->NO_BOOKING;
        $no_ukk            = $request->NO_UKK;
        $voyage            = $request->VOYAGE;
        $vessel            = $request->VESSEL;
        $tgl_stack        = $request->TGL_STACK;
        $tgl_stack_awal    = $request->TGL_STACK;
        $status            = $request->STATUS;
        $bp_id            = $request->BP_ID;
        $sp2            = $request->SP2;
        $tgl_stack        = $request->TGL_BONGKAR ? date('d-m-Y', strtotime($request->TGL_BONGKAR)) : '';
        $no_sppb        = $request->NO_SPPB;
        $tgl_sppb        = $request->TGL_SPPB;
        $blok_tpk        = $request->BLOK;
        $slot_tpk        = $request->SLOT;
        $row_tpk        = $request->ROW;
        $tier_tpk        = $request->TIER;
        $lokasi_        = $blok_tpk . "/" . $row_tpk . "-" . $slot_tpk . "-" . $tier_tpk;
        $asal_cont_stuf    = $request->ASAL_CONT;
        $tgl_empty         = $request->TGL_EMPTY;
        $early_stuff    = $request->EARLY_STUFF;
        $remark_sp2        = $request->REMARK_SP2;
        $no_req_sp2        = $request->NO_REQ_SP2;
        $no_ukk_cont        = $request->ID_VSB;

        try {
            $query_cek2 = "SELECT NO_CONTAINER FROM CONTAINER_DELIVERY WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y'";
            $row_cek2        = DB::connection('uster')->selectOne($query_cek2);
            $no_cont_cek        = $row_cek2->no_container ?? NULL;

            if ($no_cont_cek != NULL) {
                echo "EXIST_DEL";
                DB::connection('uster')->rollBack();
                die();
            }

            $cek_gati = "SELECT AKTIF FROM CONTAINER_RECEIVING WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y'";
            $rw_gati = DB::connection('uster')->selectOne($cek_gati);
            $aktif_rec = $rw_gati->aktif ?? NULL;
            if ($aktif_rec == 'Y') {
                echo 'EXIST_REC';
                DB::connection('uster')->rollBack();
                die();
            }

            if ($asal_cont_stuf == 'DEPO') {
                $cek_loc = "SELECT LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
                $rw_loc = DB::connection('uster')->selectOne($cek_loc);
                $aktif_loc = $rw_loc->location;
                if ($aktif_loc != 'IN_YARD' && $aktif_loc != 'GATO') {
                    echo 'BELUM_REC';
                    DB::connection('uster')->rollBack();
                    die();
                }
            }

            if ($no_booking == NULL) {
                $no_booking = "VESSEL_NOTHING";
            }
            //Cek status container, apakah masih aktif di table planning(container sedang direquest)
            $q_cek_cont = "SELECT NO_CONTAINER FROM PLAN_CONTAINER_STUFFING WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y'";
            $row_cek_plan = DB::connection('uster')->selectOne($q_cek_cont);

            $check = $row_cek_plan->no_container ?? NULL;
            if ($check == NULL) {

                $query_cek_aktif    = "SELECT NO_CONTAINER FROM PLAN_CONTAINER_STUFFING WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y'";
                $row_cek_aktif   = DB::connection('uster')->selectOne($query_cek_aktif);
                $cek_aktif            = $row_cek_aktif->no_container ?? NULL;

                if ($hz == NULL) {
                    echo "BERBAHAYA";
                    DB::connection('uster')->rollBack();
                    die();
                } else if ($cek_aktif <> NULL) {
                    echo "EXIST";
                    DB::connection('uster')->rollBack();
                    die();
                } else {



                    $query_cek1        = "SELECT tes.NO_REQUEST,
                                    CASE SUBSTR(KEGIATAN,9)
                                        WHEN 'RECEIVING' THEN (SELECT CONCAT('RECEIVING_',a.RECEIVING_DARI) FROM request_receiving a WHERE a.NO_REQUEST = tes.NO_REQUEST)
                                        ELSE SUBSTR(KEGIATAN,9)
                                    END KEGIATAN FROM (SELECT TGL_UPDATE, NO_REQUEST,KEGIATAN FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI')) tes
                                    WHERE tes.TGL_UPDATE=(SELECT MAX(TGL_UPDATE) FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI'))";
                    $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                    $no_request        = $row_cek1->no_request;
                    $kegiatan        = $row_cek1->kegiatan;

                    if ($kegiatan == 'RECEIVING_LUAR') {
                        $query_cek1        = " SELECT SUBSTR(TO_CHAR(b.TGL_IN+5,'dd/mm/rrrr'),1,10) START_STACK FROM GATE_IN b WHERE b.NO_CONTAINER = '$no_cont' AND b.NO_REQUEST = '$no_request'";
                        $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                        $start_stack          = $row_cek1->start_stack;
                        $asal_cont         = 'LUAR';
                    } else if ($kegiatan == 'RECEIVING_TPK') {
                        $query_cek1        = "SELECT TGL_BONGKAR START_STACK FROM container_receiving WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                        $row_cek1    = DB::connection('uster')->selectOne($query_cek1);
                        $start_stack    = $row_cek1->start_stack;
                        $asal_cont         = 'TPK';
                    } else if ($kegiatan == 'STRIPPING') {
                        $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'dd/mm/rrrr'),1,10) START_STACK FROM container_stripping WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                        $row_cek1    = DB::connection('uster')->selectOne($query_cek1);
                        $start_stack    = $row_cek1->start_stack;
                        $asal_cont         = 'DEPO';
                    }




                    if ($asal_cont_stuf == 'DEPO' && $remark_sp2 != "Y") {


                        $query_tgl_stack_depo = "SELECT TGL_UPDATE , NO_REQUEST, KEGIATAN
                                            FROM HISTORY_CONTAINER
                                            WHERE no_container = '$no_cont'
                                            AND kegiatan IN ('GATE IN','REALISASI STRIPPING')
                                            ORDER BY TGL_UPDATE DESC";

                        $row_tgl_stack_depo    = DB::connection('uster')->selectOne($query_tgl_stack_depo);
                        $ex_keg    = $row_tgl_stack_depo->kegiatan;
                        $no_re_st    = $row_tgl_stack_depo->no_request;
                        if ($ex_keg == "REALISASI STRIPPING") {
                            $rtgl_r = DB::connection('uster')->selectOne("SELECT TGL_REALISASI FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'");
                            $tgl_stack = $rtgl_r->tgl_realisasi;
                            $tgl_stack        =  $tgl_stack ? date('d-m-Y', strtotime($tgl_stack)) : '';
                        } else if ($ex_keg == "GATE IN") {
                            $rtgl_r = DB::connection('uster')->selectOne("SELECT TGL_IN FROM GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'");
                            $tgl_stack = $rtgl_r->tgl_in;
                            $tgl_stack        =  $tgl_stack ? date('d-m-Y', strtotime($request->TGL_BONGKAR)) : '';
                        }
                    }


                    if ($asal_cont_stuf == 'TPK') {
                        $asal_cont_stuf = 'TPK';
                        $new_location = 'GATO';
                    } else {
                        $new_location = NULL;
                    }

                    if ($remark_sp2 == "Y") {
                        $query_insert_stuff    = "INSERT INTO PLAN_CONTAINER_STUFFING(NO_CONTAINER,
															   NO_REQUEST,
															   AKTIF,
															   HZ,
															   COMMODITY,
															   TYPE_STUFFING,
															   START_STACK,
															   ASAL_CONT,
															   NO_SEAL,
															   BERAT,
															   KETERANGAN,
															   DEPO_TUJUAN,
															   TGL_APPROVE,
															   TGL_MULAI,
															   KD_COMMODITY,
															   NO_REQ_SP2,
                                                               ID_VSB
															   )
														VALUES('$no_cont',
															   '$no_req_stuf',
															   'Y',
															   '$hz',
															   '$commodity',
															   '$type_stuffing',
															   TO_DATE('$tgl_stack','dd-mm-rrrr')+1,
															   '$asal_cont_stuf',
															   '$no_seal',
															   '$berat',
															   '$keterangan',
															   '$depo_tujuan',
															   TO_DATE('$tgl_empty','dd-mm-rrrr'),
															   TO_DATE('$tgl_stack_awal','dd-mm-rrrr'),
															   '$kd_commodity',
															   '$no_req_sp2',
                                                               '$no_ukk'
															   )";
                    } else {
                        $query_insert_stuff    = "INSERT INTO PLAN_CONTAINER_STUFFING(NO_CONTAINER,
															   NO_REQUEST,
															   AKTIF,
															   HZ,
															   COMMODITY,
															   TYPE_STUFFING,
															   START_STACK,
															   ASAL_CONT,
															   NO_SEAL,
															   BERAT,
															   KETERANGAN,
															   DEPO_TUJUAN,
															   TGL_APPROVE,
															   KD_COMMODITY,
															   LOKASI_TPK,
                                                               ID_VSB
															   )
														VALUES('$no_cont',
															   '$no_req_stuf',
															   'Y',
															   '$hz',
															   '$commodity',
															   '$type_stuffing',
															   TO_DATE('$tgl_stack','dd/mm/rrrr'),
															   '$asal_cont_stuf',
															   '$no_seal',
															   '$berat',
															   '$keterangan',
															   '$depo_tujuan',
															   TO_DATE('$tgl_empty','dd/mm/rrrr'),
															   '$kd_commodity',
															   '$lokasi_',
                                                               '$no_ukk'
															   )";
                    }




                    DB::connection('uster')->insert($query_insert_stuff);

                    $query_cek_mascont    = "SELECT NO_CONTAINER
										FROM  MASTER_CONTAINER
										WHERE NO_CONTAINER ='$no_cont'
									";
                    $row_cek_mascont = DB::connection('uster')->selectOne($query_cek_mascont);
                    $cek_mascont         = $row_cek_mascont->no_container ?? NULL;

                    if ($cek_mascont == NULL) {
                        $query_insert_mstr    = "INSERT INTO MASTER_CONTAINER(NO_CONTAINER,
																SIZE_,
																TYPE_,
																LOCATION, NO_BOOKING, COUNTER)
														 VALUES('$no_cont',
																'$size',
																'$type',
																'$new_location', '$no_booking', 1)
									";
                        DB::connection('uster')->insert($query_insert_mstr);
                    } else {


                        $q_getcounter2 = "SELECT NO_BOOKING, COUNTER, MLO FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
                        $rw_getcounter2 = DB::connection('uster')->selectOne($q_getcounter2);
                        $last_counter = $rw_getcounter2->counter + 1;
                        $cek_mlo = $rw_getcounter2->mlo;

                        if ($asal_cont_stuf == 'TPK' && $cek_mlo == 'MLO' && $last_counter == 2) {
                            $q_update_book2 = "UPDATE MASTER_CONTAINER SET NO_BOOKING = '$no_booking' WHERE NO_CONTAINER = '$no_cont'";
                            DB::connection('uster')->update($q_update_book2);
                        }
                        if ($asal_cont_stuf == 'TPK') {
                            $q_update_book2 = "UPDATE MASTER_CONTAINER SET NO_BOOKING = '$no_booking', COUNTER = '$last_counter' WHERE NO_CONTAINER = '$no_cont'";
                            DB::connection('uster')->update($q_update_book2);
                        } else {
                            if ($new_location != NULL) {
                                $q_update_book2 = "UPDATE MASTER_CONTAINER SET NO_BOOKING = '$no_booking', LOCATION = '$new_location' , COUNTER = '$last_counter' WHERE NO_CONTAINER = '$no_cont'";
                            } else {
                                $q_update_book2 = "UPDATE MASTER_CONTAINER SET NO_BOOKING = '$no_booking', COUNTER = '$last_counter' WHERE NO_CONTAINER = '$no_cont'";
                            }

                            DB::connection('uster')->update($q_update_book2);
                        }
                    }

                    $q_getcounter1 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont' ORDER BY COUNTER DESC";
                    $rw_getcounter1 = DB::connection('uster')->selectOne($q_getcounter1);
                    $cur_booking1  = $rw_getcounter1->no_booking;
                    $cur_counter1  = $rw_getcounter1->counter;

                    $history_del        = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD,STATUS_CONT, NO_BOOKING, COUNTER)
									VALUES ('$no_cont','$no_req_stuf','PLAN REQUEST STUFFING',SYSDATE,'$id_user', '$id_yard','MTY', '$cur_booking1', '$cur_counter1')";

                    DB::connection('uster')->insert($history_del);
                    DB::connection('uster')->commit();
                    echo "OK";
                }
            } else {
                echo "SUDAH_REQUEST";
                DB::connection('uster')->rollBack();
                die();
            }
        } catch (Exception $e) {
            DB::connection('uster')->rollBack();
            return $e;
        }
    }
}
