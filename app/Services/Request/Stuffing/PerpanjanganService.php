<?php

namespace App\Services\Request\Stuffing;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Exception;
use Illuminate\Support\Facades\Session;
use PDO;

class PerpanjanganService
{
    function viewPerpajanganStuffing($no_req)
    {
        if (isset($_GET["no_req"])) {
            $no_req    = $_GET["no_req"];
            $query_r = "SELECT NO_REQUEST_RECEIVING,NO_REQUEST_DELIVERY, NO_BOOKING
                            FROM REQUEST_STUFFING
                            WHERE NO_REQUEST = '$no_req'";
            $row_result     = DB::connection('uster')->selectOne($query_r);

            $no_req_rec        = $row_result->no_request_receiving;
            $no_req_deli    = $row_result->no_request_delivery;
            $no_booking_cur    = $row_result->no_booking;

            $row_result2 = substr($no_req_rec, 3);
            $no_req2    = "UREC" . $row_result2;

            $row_result3 = substr($no_req_deli, 3);
            $no_req3    = "US" . $row_result3;
        } else {
            redirect()->route('uster.new_request.stuffing.perpanjangan.index');
        }


        $query_request    = "SELECT REQUEST_STUFFING.NO_REQUEST, REQUEST_STUFFING.NO_REQUEST_RECEIVING, REQUEST_STUFFING.NO_REQUEST_DELIVERY,
                                EMKL.NM_PBM AS NAMA_EMKL,
                                EMKL.KD_PBM AS ID_EMKL,
                                REQUEST_STUFFING.NO_DOKUMEN,
                                REQUEST_STUFFING.NO_JPB,
                                REQUEST_STUFFING.BPRP,
                                REQUEST_STUFFING.NO_NPE,
                                REQUEST_STUFFING.NO_PEB,
                                REQUEST_STUFFING.KETERANGAN,
                                REQUEST_STUFFING.KD_PENUMPUKAN_OLEH,
                                BOOK.NM_AGEN,
                                BOOK.NM_KAPAL,
                                BOOK.VOYAGE_IN,
                                BOOK.NO_BOOKING,
                                BOOK.PELABUHAN_ASAL NM_PELABUHAN_ASAL,
                                BOOK.PELABUHAN_TUJUAN NM_PELABUHAN_TUJUAN
                       FROM REQUEST_STUFFING
                       JOIN v_mst_pbm EMKL
                            ON REQUEST_STUFFING.KD_CONSIGNEE = emkl.KD_PBM and EMKL.kd_cabang = '05'
                           JOIN V_PKK_CONT BOOK
                            ON  REQUEST_STUFFING.NO_BOOKING = BOOK.NO_BOOKING
                       WHERE REQUEST_STUFFING.NO_REQUEST ='$no_req'";

        $query_list        = " SELECT DISTINCT e.NM_PBM NAMA_EMKL,
                                request_stuffing.KD_CONSIGNEE ID_EMKL,
                                request_stuffing.NM_KAPAL,
                                request_stuffing.VOYAGE VOYAGE_IN,
                                request_stuffing.NO_BOOKING,
                                request_stuffing.NO_PEB,
                                request_stuffing.NO_NPE,
                                request_stuffing.NO_DOKUMEN,
                                request_stuffing.NO_JPB,
                               request_stuffing.BPRP,
                                container_stuffing.KETERANGAN,
                                container_stuffing.COMMODITY COMMO
                                   FROM CONTAINER_STUFFING
                                            INNER JOIN REQUEST_STUFFING
                                           ON CONTAINER_STUFFING.NO_REQUEST = REQUEST_STUFFING.NO_REQUEST
                                            INNER JOIN v_mst_pbm e
                                           ON REQUEST_STUFFING.KD_CONSIGNEE =e.KD_PBM AND e.KD_CABANG = '05'
                                           WHERE CONTAINER_STUFFING.NO_REQUEST = '$no_req'";

        $row_request    = DB::connection('uster')->selectOne($query_request);


        $row_list    =  DB::connection('uster')->select($query_list);

        $jum = count($row_list);


        $get_tgl         = "SELECT a.NO_CONTAINER,
                                   a.START_PERP_PNKN,
                                   rs.NO_BOOKING
                              FROM CONTAINER_STUFFING a, request_stuffing rs
                             WHERE     a.NO_REQUEST = '$no_req'
                                   AND AKTIF = 'Y'
                                   AND a.no_request = rs.no_request
                             ORDER BY a.NO_CONTAINER";

        $row_tgl        = DB::connection('uster')->select($get_tgl);


        $count          = "SELECT COUNT(a.NO_CONTAINER) JUMLAH FROM container_stuffing a WHERE a.NO_REQUEST = '$no_req' AND a.AKTIF = 'Y'";
        $row_count    = DB::connection('uster')->selectOne($count);


        return [
            "row_count" => $row_count,
            "no_booking_cur" => $no_booking_cur,
            "row_tgl" => $row_tgl,
            "jum" => $jum,
            "row_list" => $row_list,
            "result_request" => $row_request,
            "no_req2" => $no_req2,
            "no_req3" => $no_req3
        ];
    }

    public function listPerpanjanganStuffing($request)
    {
        $from = $request->has('from') ? $request->from : null;
        $to = $request->has('to') ? $request->to : null;


        if ($from) {
            $from = Carbon::createFromFormat('Y-m-d', $from)->format('d-m-Y');
        }

        if ($to) {
            $to = Carbon::createFromFormat('Y-m-d', $to)->format('d-m-Y');
        }

        $no_req = isset($request->search['value']) ? $request->search['value'] : null;


        if (isset($from) || isset($to) || isset($no_req)) {
            if ((isset($no_req)) && ($from == NULL) && ($to == NULL)) {
                $query_list = "SELECT * FROM (
							SELECT rb.NO_REQUEST,
                                     emkl.NM_PBM AS NAMA_CONSIGNEE,
                                    --pnmt.NM_PBM AS NAMA_PENUMPUK,
                                    NVL(rb.PERP_DARI,'-') AS EX_REQ,
                                    rb.PERP_KE,
                                    rb.NOTA CETAK_NOTA_BARU,
                                    rl.NOTA CETAK_NOTA_LAMA,
                                    rb.NM_KAPAL,
                                    rb.VOYAGE,
                                    NVL(nl.LUNAS, 0) LUNAS_NOTA_LAMA,
                                    NVL(nb.LUNAS, 0) LUNAS_NOTA_BARU,
                                    rb.TGL_REQUEST,
                                    COUNT(container_stuffing.NO_CONTAINER) JUMLAH,
									rb.no_booking,
									rb.stuffing_dari,
                                    rb.o_idvsb
                            FROM request_stuffing rb, --request baru
                                      request_stuffing rl, --request lama
                                      V_MST_PBM emkl,
                                      container_stuffing,
                                      nota_stuffing nb,
                                      nota_stuffing nl
                            WHERE rb.KD_CONSIGNEE = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                AND rb.NO_REQUEST = container_stuffing.NO_REQUEST
                                AND rb.NO_REQUEST = nb.NO_REQUEST(+)
                                AND rb.NO_REQUEST = '$no_req'
                                AND rl.NO_REQUEST = nl.NO_REQUEST(+)
                                AND container_stuffing.NO_REQUEST IS NOT NULL
                                AND container_stuffing.AKTIF = 'Y'
                                AND rb.PERP_DARI = rl.NO_REQUEST(+)
                            GROUP BY  rb.NO_REQUEST,rb.TGL_REQUEST,
                                            NVL(nl.LUNAS, 0),
                                            NVL(nb.LUNAS, 0),
                                            rb.NOTA,
                                            rl.NOTA,
                                            rb.NM_KAPAL,
                                            rb.VOYAGE,
                                            emkl.NM_PBM,
                                            --pnmt.NM_PBM,
                                            rb.PERP_DARI,
                                            rb.PERP_KE,
											rb.no_booking,
											rb.stuffing_dari,
                                            rb.o_idvsb
                            ORDER BY rb.TGL_REQUEST DESC
                            )
                WHERE
	                rownum <= $request->length + 20
                ORDER BY TGL_REQUEST DESC";
            } else if ((isset($from)) && (isset($to)) && ($no_req == NULL)) {
                $query_list = "SELECT
                        *
                    FROM
                        (
                        SELECT
                            rb.NO_REQUEST,
                            emkl.NM_PBM AS NAMA_CONSIGNEE,
                            NVL(rb.PERP_DARI, '-') AS EX_REQ,
                            rb.PERP_KE,
                            rb.NOTA CETAK_NOTA_BARU,
                            rl.NOTA CETAK_NOTA_LAMA,
                            rb.NM_KAPAL,
                            rb.VOYAGE,
                            NVL(nl.LUNAS, 0) LUNAS_NOTA_LAMA,
                            NVL(nb.LUNAS, 0) LUNAS_NOTA_BARU,
                            rb.TGL_REQUEST,
                            COUNT(container_stuffing.NO_CONTAINER) JUMLAH,
                            rb.no_booking,
                            rb.stuffing_dari,
                            rb.o_idvsb
                        FROM
                            request_stuffing rb,
                            request_stuffing rl,
                            v_mst_pbm emkl,
                            container_stuffing,
                            nota_stuffing nb,
                            nota_stuffing nl
                        WHERE
                            rb.KD_CONSIGNEE = emkl.KD_PBM
                            AND emkl.KD_CABANG = '05'
                            AND rb.NO_REQUEST = container_stuffing.NO_REQUEST
                            AND rb.NO_REQUEST = nb.NO_REQUEST(+)
                            AND rl.NO_REQUEST = nl.NO_REQUEST(+)
                            AND rb.TGL_REQUEST BETWEEN TO_DATE ( '$from',
                            'dd-mm-rrrr') AND TO_DATE ( '$to',
                            'dd-mm-rrrr')
                        GROUP BY
                            rb.NO_REQUEST,
                            rb.TGL_REQUEST,
                            NVL(nl.LUNAS, 0),
                            NVL(nb.LUNAS, 0),
                            rb.NOTA,
                            rl.NOTA,
                            rb.NM_KAPAL,
                            rb.VOYAGE,
                            emkl.NM_PBM,
                            rb.PERP_DARI,
                            rb.PERP_KE,
                            rb.no_booking,
                            rb.stuffing_dari,
                            rb.o_idvsb
                        ORDER BY
                            rb.TGL_REQUEST DESC
                                                )
                    WHERE
	                    rownum <= $request->length + 20
                    ORDER BY
                        TGL_REQUEST DESC";
            } else if ((isset($from)) && (isset($to)) && (isset($no_req))) {
                $query_list = "SELECT
                    *
                FROM
                    (
                    SELECT
                        rb.NO_REQUEST,
                        emkl.NM_PBM AS NAMA_CONSIGNEE,
                        NVL(rb.PERP_DARI, '-') AS EX_REQ,
                        rb.PERP_KE,
                        rb.NOTA CETAK_NOTA_BARU,
                        rl.NOTA CETAK_NOTA_LAMA,
                        rb.NM_KAPAL,
                        rb.VOYAGE,
                        NVL(nl.LUNAS, 0) LUNAS_NOTA_LAMA,
                        NVL(nb.LUNAS, 0) LUNAS_NOTA_BARU,
                        rb.TGL_REQUEST,
                        COUNT(container_stuffing.NO_CONTAINER) JUMLAH,
                        rb.no_booking,
                        rb.stuffing_dari,
                        rb.o_idvsb
                    FROM
                        request_stuffing rb,
                        request_stuffing rl,
                        v_mst_pbm emkl,
                        container_stuffing,
                        nota_stuffing nb,
                        nota_stuffing nl
                    WHERE
                        rb.KD_CONSIGNEE = emkl.KD_PBM
                        AND emkl.KD_CABANG = '05'
                        AND rb.NO_REQUEST = container_stuffing.NO_REQUEST
                        AND rb.NO_REQUEST = nb.NO_REQUEST(+)
                        AND rl.NO_REQUEST = nl.NO_REQUEST(+)
                        AND rb.NO_REQUEST LIKE  '%$no_req%'
                        AND rb.TGL_REQUEST BETWEEN TO_DATE ( '$from',
                        'dd-mm-rrrr') AND TO_DATE ( '$to',
                        'dd-mm-rrrr')
                    GROUP BY
                        rb.NO_REQUEST,
                        rb.TGL_REQUEST,
                        NVL(nl.LUNAS, 0),
                        NVL(nb.LUNAS, 0),
                        rb.NOTA,
                        rl.NOTA,
                        rb.NM_KAPAL,
                        rb.VOYAGE,
                        emkl.NM_PBM,
                        rb.PERP_DARI,
                        rb.PERP_KE,
                        rb.no_booking,
                        rb.stuffing_dari,
                        rb.o_idvsb
                    ORDER BY
                        rb.TGL_REQUEST DESC
                                            )
                WHERE
	                rownum <= $request->length + 20
                ORDER BY
                    TGL_REQUEST DESC";
            }
        } else {
            $query_list = "SELECT * FROM (
							SELECT rb.NO_REQUEST,
                                     emkl.NM_PBM AS NAMA_CONSIGNEE,
                                    --pnmt.NM_PBM AS NAMA_PENUMPUK,
                                    NVL(rb.PERP_DARI,'-') AS EX_REQ,
                                    rb.PERP_KE,
                                    rb.NOTA CETAK_NOTA_BARU,
                                    rl.NOTA CETAK_NOTA_LAMA,
                                    rb.NM_KAPAL,
                                    rb.VOYAGE,
                                    NVL(nl.LUNAS, 0) LUNAS_NOTA_LAMA,
                                    NVL(nb.LUNAS, 0) LUNAS_NOTA_BARU,
                                    rb.TGL_REQUEST,
                                    COUNT(container_stuffing.NO_CONTAINER) JUMLAH,
									rb.no_booking,
									rb.stuffing_dari,
                                    rb.o_idvsb
                            FROM request_stuffing rb, --request baru
                                      request_stuffing rl, --request lama
                                      v_mst_pbm emkl,
                                      --V_MST_PBM pnmt,
                                      container_stuffing,
                                      nota_stuffing nb,
                                      nota_stuffing nl
                            WHERE rb.KD_CONSIGNEE = emkl.KD_PBM AND emkl.KD_CABANG = '05'
                                AND rb.NO_REQUEST = container_stuffing.NO_REQUEST
                                AND rb.NO_REQUEST = nb.NO_REQUEST(+)
                                AND rl.NO_REQUEST = nl.NO_REQUEST(+)
                                AND container_stuffing.NO_REQUEST IS NOT NULL
                                AND container_stuffing.AKTIF = 'Y'
                                AND rb.PERP_DARI = rl.NO_REQUEST(+)
                            GROUP BY  rb.NO_REQUEST,rb.TGL_REQUEST,
                                            NVL(nl.LUNAS, 0),
                                            NVL(nb.LUNAS, 0),
                                            rb.NOTA,
                                            rl.NOTA,
                                            rb.NM_KAPAL,
                                            rb.VOYAGE,
                                            emkl.NM_PBM,
                                            --pnmt.NM_PBM,
                                            rb.PERP_DARI,
                                            rb.PERP_KE,
											rb.no_booking,
											rb.stuffing_dari,
                                            rb.o_idvsb
                            ORDER BY rb.TGL_REQUEST DESC
                            )
                WHERE
	                rownum <= $request->length + 20
                ORDER BY TGL_REQUEST DESC";
        }
        return DB::connection('uster')->select($query_list);
    }
    function checkNotaPerpanjanganStuffing($stuffing)
    {
        $query_list = "select count(*) cek from m_vsb_voyage@dbint_link where id_vsb_voyage = '$stuffing->o_idvsb'
        and sysdate > to_date(clossing_time,'yyyymmddhh24miss')";

        $result = DB::connection('uster')->selectOne($query_list);
        $text  = '';

        if ($result->cek > 0) {
            $text = 'Sudah melewati closing time';
        } else {
            $html = "<a href=" . route('uster.new_request.stuffing.perpanjangan.view', ['no_req' => $stuffing->no_request]) . " class='btn btn-success w-100'><i class='fas fa-check-square'>&nbsp; Perpanjangan</a>";
            $text = $html;
        }

        if (($stuffing->ex_req == '-') and ($stuffing->lunas_nota_baru == 'NO')) {
            if ($stuffing->stuffing_dari == 'AUTO') {
                $text = '<a href="' . route('uster.new_request.stuffing.perpanjangan.view', ['no_req' => $stuffing->no_request]) . '" class="btn btn-success w-100"><i class="fas fa-check-square">&nbsp; Perpanjangan</i></a>';
            } else {
                $text = "<button type=\"button\" class=\"btn btn-secondary\"><i>&nbsp; Nota awal belum lunas</i></button>";
            }
        } else if (($stuffing->ex_req == '-') and ($stuffing->lunas_nota_baru == 'YES')) {
            return $text;
        } else if (($stuffing->ex_req == '-') and ($stuffing->lunas_nota_baru == '0')) {
            if ($stuffing->stuffing_dari == 'AUTO') {
                $text = '<a href="' . route('uster.new_request.stuffing.perpanjangan.view', ['no_req' => $stuffing->no_request]) . '" class="btn btn-success w-100"><i class="fas fa-check-square">&nbsp; Perpanjangan</i></a>';
            } else {
                $text = "<button type=\"button\" class=\"btn btn-secondary\"><i>&nbsp; Nota awal belum lunas</i></button>";
            }
        } else if (($stuffing->ex_req <> '-') and ($stuffing->lunas_nota_lama == '0') and ($stuffing->lunas_nota_baru == '0')) {
            $text = "<button type=\"button\" class=\"btn btn-warning\"><i>&nbsp; Nota lama belum cetak</i></button>";
        } else if (($stuffing->ex_req <> '-') and ($stuffing->lunas_nota_lama == '0') and ($stuffing->lunas_nota_baru == 'NO')) {
            $text = "<button type=\"button\" class=\"btn btn-warning\"><i>&nbsp; Nota lama belum cetak</i></button>";
        } else if (($stuffing->ex_req <> '-') and ($stuffing->lunas_nota_lama == 'NO') and ($stuffing->lunas_nota_baru == '0')) {
            $text = "<button type=\"button\" class=\"btn btn-danger w-100\"><i>&nbsp; Nota lama belum lunas</i></button>";
        } else if (($stuffing->ex_req <> '-') and ($stuffing->lunas_nota_lama == 'YES') and ($stuffing->lunas_nota_baru == 'YES')) {
            return $text;
        } else if (($stuffing->ex_req <> '-') and ($stuffing->lunas_nota_lama == 'YES') and ($stuffing->lunas_nota_baru == 'NO')) {
            $text = "<button type=\"button\" class=\"btn btn-danger w-100\"><i>&nbsp; Nota baru belum lunas</i></button>";
        } else if (($stuffing->ex_req <> '-') and ($stuffing->lunas_nota_lama == 'YES') and ($stuffing->lunas_nota_baru == '0')) {
            if ($stuffing->stuffing_dari == 'AUTO') {
                $text = '<a href="' . route('uster.new_request.stuffing.perpanjangan.view', ['no_req' => $stuffing->no_request]) . '" class="btn btn-success w-100"><i class="fas fa-check-square">&nbsp; Perpanjangan</i></a>';
            } else {
                $text = "<button type=\"button\" class=\"btn btn-warning\"><i>&nbsp; Nota baru belum cetak</i></button>";
            }
        }

        return $text;
    }
    function listContainerViewPerpanjangan($no_req)
    {

        $query_cek_perp = "SELECT PERP_DARI
						FROM REQUEST_STUFFING
						WHERE NO_REQUEST='$no_req'";
        $result = DB::connection('uster')->selectOne($query_cek_perp);
        $no_req_lama    = $result->perp_dari;
        if ($no_req_lama == NULL) {
            $query_list        = "SELECT DISTINCT CONTAINER_STUFFING.NO_CONTAINER,
											   CONTAINER_STUFFING.HZ,
											   CONTAINER_STUFFING.START_PERP_PNKN+1 TGL_MULAI,
                                               CONTAINER_STUFFING.END_STACK_PNKN,
											   CONTAINER_STUFFING.COMMODITY, M.SIZE_ KD_SIZE, M.TYPE_ KD_TYPE
							   FROM CONTAINER_STUFFING LEFT JOIN MASTER_CONTAINER M
							   ON CONTAINER_STUFFING.NO_CONTAINER = M.NO_CONTAINER
							   WHERE CONTAINER_STUFFING.NO_REQUEST = '$no_req'
							   AND AKTIF = 'Y'";
        } else {
            $query_list        = "SELECT DISTINCT CB.NO_REQUEST NO_BARU,
							   CL.NO_REQUEST NO_LAMA,
							   CB.NO_CONTAINER,
							   CB .HZ,
							   CB.END_STACK_PNKN+1 TGL_MULAI,
                               CB.END_STACK_PNKN,
							   CB.COMMODITY, M.SIZE_ KD_SIZE, M.TYPE_ KD_TYPE
							   FROM CONTAINER_STUFFING CB
											INNER JOIN CONTAINER_STUFFING CL
										ON CB.NO_CONTAINER = CL.NO_CONTAINER
											LEFT JOIN MASTER_CONTAINER M
										ON CB.NO_CONTAINER = M.NO_CONTAINER
							   WHERE CB.NO_REQUEST = '$no_req'
							   AND CL.NO_REQUEST = '$no_req_lama'
							   AND CB.AKTIF = 'Y'";
        }

        return DB::connection('uster')->select($query_list);
    }

    function getInfoPerpanjanganStuffing()
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
            KETERANGAN = 'STRIPING'
    UNION
        SELECT
            2 NO,
            'BERJALAN' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) BETWEEN to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') AND to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss')
    UNION
        SELECT
            3 NO,
            'RENCANA MULAI' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) = to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') + 1
    UNION
        SELECT
            4 NO,
            'SELESAI' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) = to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss')
    UNION
        SELECT
            5 NO,
            'SISA KAPASITAS' KEGIATAN,
            (
            SELECT
                SUM(CAPACITY)
            FROM
                BLOCKING_AREA
            WHERE
                KETERANGAN = 'STRIPING')
                   - (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate) BETWEEN to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') AND to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss'))
                   - (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate) = to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') + 1)
                   + (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate) = to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss'))
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
            KETERANGAN = 'STRIPING'
    UNION
        SELECT
            2 NO,
            'BERJALAN' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 1 BETWEEN to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') AND to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss')
    UNION
        SELECT
            3 NO,
            'RENCANA MULAI' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 1 = to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') + 1
    UNION
        SELECT
            4 NO,
            'SELESAI' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 1 = to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss')
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
                KETERANGAN = 'STRIPING')
                   - (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 1 BETWEEN to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') AND to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss'))
                   - (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 1 = to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') + 1)
                   + (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 1 = to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss'))
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
            KETERANGAN = 'STRIPING'
    UNION
        SELECT
            2 NO,
            'BERJALAN' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 2 BETWEEN to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') AND to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss')
    UNION
        SELECT
            3 NO,
            'RENCANA MULAI' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 2 = to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') + 1
    UNION
        SELECT
            4 NO,
            'SELESAI' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 2 = to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss')
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
                KETERANGAN = 'STRIPING')
                   - (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 2 BETWEEN to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') AND to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss'))
                   - (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 2 = to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') + 1)
                   + (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 2 = to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss'))
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
            KETERANGAN = 'STRIPING'
    UNION
        SELECT
            2 NO,
            'BERJALAN' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 3 BETWEEN to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') AND to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss')
    UNION
        SELECT
            3 NO,
            'RENCANA MULAI' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 3 = to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') + 1
    UNION
        SELECT
            4 NO,
            'SELESAI' KEGIATAN,
            COUNT (NO_CONTAINER) JUMLAH
        FROM
            CONTAINER_STRIPPING
        WHERE
            to_date(sysdate) + 3 = to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss')
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
                KETERANGAN = 'STRIPING')
                   - (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 3 BETWEEN to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') AND to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss'))
                   - (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 3 = to_date(tgl_approve, 'yyyy-mm-dd hh24:mi:ss') + 1)
                   + (
            SELECT
                COUNT (NO_CONTAINER) JUMLAH
            FROM
                CONTAINER_STRIPPING
            WHERE
                to_date(sysdate)+ 3 = to_date(tgl_app_selesai, 'yyyy-mm-dd hh24:mi:ss'))
        FROM
            DUAL) D
    WHERE
        A.NO = B.NO
        AND B.NO = C.NO
        AND C.NO = D.NO";

        $qselect =  DB::connection('uster')->select($qselect);


        $tanggalx = "SELECT TO_CHAR(SYSDATE,'dd/mm/yyyy') H, TO_CHAR(SYSDATE+1,'dd/mm/yyyy') H_1, TO_CHAR(SYSDATE+2,'dd/mm/yyyy') H_2, TO_CHAR(SYSDATE+3,'dd/mm/yyyy') H_3 FROM DUAL";
        $tanggalx =  DB::connection('uster')->select($tanggalx);

        return [
            'tanggal' =>  $tanggalx,
            'row' =>  $qselect,
        ];
    }

    function checkClose($no_booking)
    {
        $no_booking = $no_booking;
        $querys            = "select count(*) cek from m_vsb_voyage@dbint_link where 'BS'||vessel_code||id_vsb_voyage =  '$no_booking' and sysdate > to_date(clossing_time,'yyyymmddhh24miss')";

        $rows        = DB::connection('uster')->selectOne($querys);
        if ($rows->cek > 0) {
            return response()->json('Y');
            exit();
        } else {
            return response()->json('A');
        }
    }

    function addContainer($request)
    {
        // Mulai transaksi database
        DB::beginTransaction();
        try {
            $no_cont         = $request->input('NO_CONT');
            $no_req            = $request->input('NO_REQ');
            $keterangan        = $request->input('KETERANGAN');

            // Menggunakan koneksi "uster"
            $connection = DB::connection('uster');

            // Query untuk mendapatkan nomor request terbaru
            $row_select = $connection->table('REQUEST_STUFFING')
                ->selectRaw("NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_REQUEST,8,13)))+1,6,0), '000001') AS jum")
                ->selectRaw("TO_CHAR(SYSDATE, 'MM') AS month")
                ->selectRaw("TO_CHAR(SYSDATE, 'YY') AS year")
                ->whereBetween('TGL_REQUEST', [DB::raw("TRUNC(SYSDATE, 'MONTH')"), DB::raw("LAST_DAY(SYSDATE)")])
                ->where('NO_REQUEST', 'like', '%SFP%')
                ->first();

            // Menggabungkan komponen nomor request
            $no_req_s = "SFP" . $row_select->month . $row_select->year . $row_select->jum;

            // Query untuk menghitung jumlah container yang aktif berdasarkan nomor request
            $jml = $connection->table('CONTAINER_STUFFING')
                ->where('NO_REQUEST', $no_req)
                ->where('AKTIF', 'Y')
                ->count();

            // Query untuk insert data ke tabel REQUEST_STUFFING
            $query = "
                INSERT INTO REQUEST_STUFFING (
                    NO_REQUEST,
                    ID_YARD,
                    CETAK_KARTU_SPPS,
                    KETERANGAN,
                    NO_BOOKING,
                    TGL_REQUEST,
                    NO_DOKUMEN,
                    NO_JPB,
                    BPRP,
                    ID_PEMILIK,
                    ID_EMKL,
                    NO_REQUEST_RECEIVING,
                    ID_USER,
                    NO_REQUEST_DELIVERY,
                    KD_CONSIGNEE,
                    KD_PENUMPUKAN_OLEH,
                    NM_KAPAL,
                    NO_PEB,
                    NO_NPE,
                    VOYAGE,
                    STUFFING_DARI,
                    NOTA,
                    STATUS_REQ,
                    PERP_DARI,
                    PERP_KE,
                    ID_PENUMPUKAN,
                    O_VESSEL,
                    O_VOYIN,
                    O_VOYOUT,
                    O_IDVSB,
                    O_REQNBS,
                    DI
                )
                SELECT
                    :no_req_s,
                    ID_YARD,
                    CETAK_KARTU_SPPS,
                    KETERANGAN,
                    NO_BOOKING,
                    SYSDATE,
                    NO_DOKUMEN,
                    NO_JPB,
                    BPRP,
                    ID_PEMILIK,
                    ID_EMKL,
                    NO_REQUEST_RECEIVING,
                    ID_USER,
                    NO_REQUEST_DELIVERY,
                    CASE WHEN ID_PENUMPUKAN IS NULL THEN KD_CONSIGNEE ELSE ID_PENUMPUKAN END,
                    CASE WHEN ID_PENUMPUKAN IS NULL THEN KD_CONSIGNEE ELSE ID_PENUMPUKAN END,
                    NM_KAPAL,
                    NO_PEB,
                    NO_NPE,
                    VOYAGE,
                    STUFFING_DARI,
                    'T',
                    'PERP',
                    :no_req,
                    NVL(PERP_KE + 1, 1),
                    ID_PENUMPUKAN,
                    O_VESSEL,
                    O_VOYIN,
                    O_VOYOUT,
                    O_IDVSB,
                    O_REQNBS,
                    DI
                FROM REQUEST_STUFFING
                WHERE NO_REQUEST = :no_req
            ";

            $result = DB::connection('uster')->insert($query, [
                'no_req_s' => $no_req_s,
                'no_req'   => $no_req,
            ]);

            if ($result) {
                // Mengambil jumlah container yang aktif berdasarkan NO_REQUEST
                $jml = DB::connection('uster')
                    ->table('CONTAINER_STUFFING')
                    ->where('NO_REQUEST', $no_req)
                    ->where('AKTIF', 'Y')
                    ->count();
                $NO_CONT = [];
                $TGL_PERP = [];

                // Loop untuk memproses data berdasarkan jumlah container
                for ($i = 0; $i < $jml; $i++) {
                    if (!empty($request->TGL_APPROVE[$i])) { // Pastikan tidak NULL
                        $x = $i + 1;
                        $NO_CONT[$i] = $request->input("NO_CONT_$x");
                        $TGL_PERP[$i] = $request->TGL_APPROVE[$i];
                    }
                }

                // Mengambil nilai PERP_DARI berdasarkan NO_REQUEST
                $no_req_lama = DB::connection('uster')
                    ->table('REQUEST_STUFFING')
                    ->where('NO_REQUEST', $no_req)
                    ->value('PERP_DARI');

                // Ini berarti merupakan perpanjangan pertama
                if ($no_req_lama == NULL) {
                    for ($i = 0; $i < $jml; $i++) {
                        if (!empty($request->TGL_APPROVE[$i])) {
                            $NO_CONT = $request->input("NO_CONT_" . ($i + 1));
                            $TGL_PERP = $request->TGL_APPROVE[$i];

                            // **1. Insert ke CONTAINER_STUFFING (Insert-Select)**
                            $exec = DB::connection('uster')->insert("
                                INSERT INTO CONTAINER_STUFFING (
                                    NO_CONTAINER, NO_REQUEST, AKTIF, HZ, COMMODITY, KD_COMMODITY, TYPE_STUFFING, START_STACK,
                                    ASAL_CONT, NO_SEAL, BERAT, KETERANGAN, STATUS_REQ, TGL_APPROVE, TGL_GATE, START_PERP_PNKN,
                                    END_STACK_PNKN, TGL_MULAI_FULL, TGL_SELESAI_FULL
                                )
                                SELECT NO_CONTAINER, ?, 'Y', HZ, COMMODITY, KD_COMMODITY, TYPE_STUFFING, START_STACK,
                                       ASAL_CONT, NO_SEAL, BERAT, KETERANGAN, 'PERP', ?, '', START_PERP_PNKN + 1,
                                       TO_DATE(?, 'YYYY-MM-DD'), TGL_MULAI_FULL, TGL_SELESAI_FULL
                                FROM CONTAINER_STUFFING
                                WHERE NO_CONTAINER = ? AND NO_REQUEST = ? AND AKTIF = 'Y'
                            ", [$no_req_s, $TGL_PERP, $TGL_PERP, $NO_CONT, $no_req]);

                            if (!$exec) {
                                throw new Exception('Gagal Simpan Container Perpanjangan Stuffing');
                            }

                            // **2. Update Status ke 'T' untuk Request Lama**
                            DB::connection('uster')->table('CONTAINER_STUFFING')
                                ->where('NO_CONTAINER', $NO_CONT)
                                ->where('NO_REQUEST', $no_req)
                                ->update(['AKTIF' => 'T']);

                            // **3. Ambil STATUS_CONT dari HISTORY_CONTAINER**
                            $cur_status = DB::connection('uster')->table('HISTORY_CONTAINER')
                                ->where('NO_CONTAINER', $NO_CONT)
                                ->where('NO_REQUEST', $no_req)
                                ->value('STATUS_CONT');

                            // **4. Ambil NO_BOOKING dan COUNTER dari MASTER_CONTAINER**
                            $counterData = DB::connection('uster')->table('MASTER_CONTAINER')
                                ->where('NO_CONTAINER', $NO_CONT)
                                ->orderByDesc('COUNTER')
                                ->first();

                            $cur_booking = $counterData->no_booking ?? '';
                            $cur_counter = $counterData->counter ?? '';

                            // **5. Insert ke HISTORY_CONTAINER**
                            DB::connection('uster')->insert("
                                INSERT INTO HISTORY_CONTAINER (NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, COUNTER, NO_BOOKING, STATUS_CONT)
                                VALUES (?, ?, 'PERPANJANGAN STUFFING', SYSDATE, ?, ?, ?, ?, ?)
                            ", [$NO_CONT, $no_req_s, Session::get('PENGGUNA_ID'), Session::get('IDYARD_STORAGE'), $cur_counter, $cur_booking, $cur_status]);
                        } else {
                            throw new Exception("Tgl Perpanjangan tidak boleh kosong");
                        }
                    }
                } else {
                    for ($i = 0; $i < $jml; $i++) {
                        if (!empty($request->TGL_APPROVE[$i])) {
                            $NO_CONT = $request->input("NO_CONT_" . ($i + 1));
                            $TGL_PERP = $request->TGL_APPROVE[$i];

                            // **1. Insert ke CONTAINER_STUFFING**
                            $exec = DB::connection('uster')->insert("
                                INSERT INTO CONTAINER_STUFFING (
                                    NO_CONTAINER, NO_REQUEST, AKTIF, HZ, COMMODITY, KD_COMMODITY, TYPE_STUFFING,
                                    START_STACK, ASAL_CONT, NO_SEAL, BERAT, KETERANGAN, STATUS_REQ,
                                    TGL_APPROVE, TGL_GATE, START_PERP_PNKN, END_STACK_PNKN, TGL_MULAI_FULL, TGL_SELESAI_FULL
                                )
                                SELECT NO_CONTAINER, ?, 'Y', HZ, COMMODITY, KD_COMMODITY, TYPE_STUFFING,
                                       START_STACK, ASAL_CONT, NO_SEAL, BERAT, KETERANGAN, 'PERP',
                                       ?, '', END_STACK_PNKN + 1, TO_DATE(?, 'YYYY-MM-DD'),
                                       TGL_MULAI_FULL, TGL_SELESAI_FULL
                                FROM CONTAINER_STUFFING
                                WHERE NO_CONTAINER = ? AND NO_REQUEST = ? AND AKTIF = 'Y'
                            ", [$no_req_s, $TGL_PERP, $TGL_PERP, $NO_CONT, $no_req]);

                            if (!$exec) {
                                throw new Exception('Gagal Simpan Container Perpanjangan Stuffing');
                            }

                            // **2. Nonaktifkan CONTAINER_STUFFING dengan request lama**
                            DB::connection('uster')->table('CONTAINER_STUFFING')
                                ->where('NO_CONTAINER', $NO_CONT)
                                ->where('NO_REQUEST', $no_req)
                                ->update(['AKTIF' => 'T']);

                            // **3. Nonaktifkan PLAN_CONTAINER_STUFFING dengan request lama**
                            $no_req_plan = str_replace('S', 'P', $no_req);
                            DB::connection('uster')->table('PLAN_CONTAINER_STUFFING')
                                ->where('NO_CONTAINER', $NO_CONT)
                                ->where('NO_REQUEST', $no_req_plan)
                                ->update(['AKTIF' => 'T']);

                            // **4. Ambil STATUS_CONT dari HISTORY_CONTAINER**
                            $cur_status = DB::connection('uster')->table('HISTORY_CONTAINER')
                                ->where('NO_CONTAINER', $NO_CONT)
                                ->where('NO_REQUEST', $no_req)
                                ->value('STATUS_CONT');

                            // **5. Ambil NO_BOOKING dan COUNTER dari MASTER_CONTAINER**
                            $counterData = DB::connection('uster')->table('MASTER_CONTAINER')
                                ->where('NO_CONTAINER', $NO_CONT)
                                ->orderByDesc('COUNTER')
                                ->first();

                            $cur_booking = $counterData->no_booking ?? '';
                            $cur_counter = $counterData->counter ?? '';

                            $ID_USER = Session::get('PENGGUNA_ID');
                            $id_yard = Session::get("IDYARD_STORAGE");

                            // **6. Insert ke HISTORY_CONTAINER**
                            DB::connection('uster')->insert("
                                INSERT INTO HISTORY_CONTAINER (
                                    NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, COUNTER, NO_BOOKING, STATUS_CONT
                                )
                                VALUES (?, ?, 'PERPANJANGAN STUFFING', SYSDATE, ?, ?, ?, ?, ?)
                            ", [$NO_CONT, $no_req_s, $ID_USER, $id_yard, $cur_counter, $cur_booking, $cur_status]);
                        } else {
                            throw new Exception("Tgl Perpanjangan tidak boleh kosong");
                        }
                    }
                }

                $qparam = [
                    "in_req_old" => $no_req,
                    "in_req_new" => $no_req_s,
                    "in_iduser" => $ID_USER,
                    "in_ket" => $keterangan,
                    "p_ErrMsg" => "",
                ];
                $errMsg = "";

                // $queryif = "DECLARE BEGIN pack_create_req_stuffing.perpanjangan(:in_req_old, :in_req_new, :in_iduser, :in_ket, :p_ErrMsg); END;";
                // // Eksekusi stored procedure dengan output parameter
                // DB::connection('uster')->getPdo()->prepare($queryif)->execute($qparam);
                // Buat koneksi PDO
                $pdo = DB::connection('uster')->getPdo();

                // Siapkan statement
                $stmt = $pdo->prepare("BEGIN pack_create_req_stuffing.perpanjangan(:in_req_old, :in_req_new, :in_iduser, :in_ket, :p_ErrMsg); END;");

                // Bind input parameter
                $stmt->bindParam(':in_req_old', $no_req);
                $stmt->bindParam(':in_req_new', $no_req_s);
                $stmt->bindParam(':in_iduser', $ID_USER);
                $stmt->bindParam(':in_ket', $keterangan);
                // Bind output parameter
                $stmt->bindParam(':p_ErrMsg', $errMsg, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
                // Eksekusi stored procedure
                $stmt->execute();

                // Ambil pesan error dari output parameter
                $msg = $errMsg ?? null;

                if ($msg == 'OK') {
                    return response()->json('OK');
                    DB::commit();
                } else {
                    return response()->json('Gagal insert Perpanjangan Stuffing');
                    DB::rollBack();
                }
            } else {
                // Kembalikan respon JSON jika gagal insert req strip
                return response()->json('Gagal insert Perpanjangan Stuffing');
            }
        } catch (\Exception $e) {
            // Rollback transaksi database jika terjadi exception
            DB::rollBack();
            // Lakukan penanganan exception
            return response()->json('Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
