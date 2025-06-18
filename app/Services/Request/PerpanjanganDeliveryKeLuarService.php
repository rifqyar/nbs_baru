<?php

namespace App\Services\Request;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PerpanjanganDeliveryKeLuarService
{
    public function dataDeliveryLuar($request)
    {

        $from       = $request->has('from') ? $request->from : null;
        $to         = $request->has('to') ? $request->to : null;
        $no_req    = isset($request->search['value']) ? $request->search['value'] : null; //$_POST["no_req"];
        // $id_yard    =    session()->get('IDYARD_STORAGE"];     ')
        // if (isset($from) || isset($to) || isset($no_req)) {
        if (($no_req == NULL) && (isset($from)) && (isset($to))) {
            $query_list = " SELECT
                                    t.*,
                                    (SELECT LUNAS
                                    FROM NOTA_DELIVERY
                                    WHERE NO_REQUEST = t.NO_REQUEST
                                    AND TGL_NOTA = (SELECT MAX(a.TGL_NOTA)
                                                    FROM NOTA_DELIVERY a
                                                    WHERE a.NO_REQUEST = t.NO_REQUEST)
                                    ) AS LUNAS
                                FROM
                                    (
                                        SELECT
                                            a.NO_REQUEST,
                                            a.KOREKSI,
                                            a.NOTA,
                                            NVL(a.PERP_DARI, '-') PERP_DARI,
                                            TO_CHAR(TGL_REQUEST, 'dd-MON-yyyy') AS TGL_REQUEST,
                                            TO_CHAR(TGL_REQUEST_DELIVERY, 'dd-MON-yyyy') AS TGL_REQUEST_DELIVERY,
                                            b.NM_PBM,
                                            COUNT(d.no_container) JUMLAH
                                        FROM
                                            request_delivery a
                                            JOIN v_mst_pbm b ON a.KD_EMKL = b.KD_PBM
                                            JOIN container_delivery d ON a.no_request = d.no_request
                                        WHERE
                                            b.KD_CABANG = '05'
                                            AND a.DELIVERY_KE = 'LUAR'
                                            AND a.TGL_REQUEST BETWEEN TO_DATE('$from','yyyy-mm-dd') AND TO_DATE('$to','yyyy-mm-dd')
                                        GROUP BY
                                            a.NO_REQUEST,
                                            a.KOREKSI,
                                            a.NOTA,
                                            NVL(a.PERP_DARI,'-'),
                                            TGL_REQUEST,
                                            TGL_REQUEST_DELIVERY,
                                            b.NM_PBM
                                        ORDER BY
                                            TGL_REQUEST DESC
                                    ) t
                                ";
        } else if (($no_req != NULL) && (!isset($from)) && (!isset($to))) {
            $query_list = " WITH LatestNota AS (
                            SELECT NO_REQUEST, LUNAS, TGL_NOTA,
                                ROW_NUMBER() OVER (PARTITION BY NO_REQUEST ORDER BY TGL_NOTA DESC) AS rn
                            FROM NOTA_DELIVERY
                        ),
                        ContainerCount AS (
                            SELECT no_request, COUNT(no_container) AS jumlah
                            FROM container_delivery
                            GROUP BY no_request
                        )
                        SELECT t.*, ln.LUNAS
                        FROM (
                            SELECT
                                a.NO_REQUEST,
                                a.KOREKSI,
                                a.NOTA,
                                COALESCE(a.PERP_DARI, '-') AS PERP_DARI,
                                TO_CHAR(a.TGL_REQUEST, 'dd-MON-yyyy') AS TGL_REQUEST,
                                TO_CHAR(a.TGL_REQUEST_DELIVERY, 'dd-MON-yyyy') AS TGL_REQUEST_DELIVERY,
                                b.NM_PBM,
                                cc.JUMLAH
                            FROM request_delivery a
                            JOIN v_mst_pbm b ON a.KD_EMKL = b.KD_PBM
                            JOIN ContainerCount cc ON a.NO_REQUEST = cc.no_request
                            WHERE b.KD_CABANG = '05'
                                AND a.DELIVERY_KE = 'LUAR'
                                AND a.NO_REQUEST like '%". strtoupper($no_req) ."%'
                            ORDER BY a.TGL_REQUEST DESC
                        ) t
                        LEFT JOIN LatestNota ln ON t.NO_REQUEST = ln.NO_REQUEST AND ln.rn = 1";
        } else {
            $query_list = "WITH LatestNota AS (
                            SELECT NO_REQUEST, LUNAS, TGL_NOTA,
                                ROW_NUMBER() OVER (PARTITION BY NO_REQUEST ORDER BY TGL_NOTA DESC) AS rn
                            FROM NOTA_DELIVERY
                        ),
                        ContainerCount AS (
                            SELECT no_request, COUNT(no_container) AS jumlah
                            FROM container_delivery
                            GROUP BY no_request
                        )
                        SELECT t.*, ln.LUNAS
                        FROM (
                            SELECT
                                a.NO_REQUEST,
                                a.KOREKSI,
                                a.NOTA,
                                COALESCE(a.PERP_DARI, '-') AS PERP_DARI,
                                TO_CHAR(a.TGL_REQUEST, 'dd-MON-yyyy') AS TGL_REQUEST,
                                TO_CHAR(a.TGL_REQUEST_DELIVERY, 'dd-MON-yyyy') AS TGL_REQUEST_DELIVERY,
                                b.NM_PBM,
                                cc.JUMLAH
                            FROM request_delivery a
                            JOIN v_mst_pbm b ON a.KD_EMKL = b.KD_PBM
                            JOIN ContainerCount cc ON a.NO_REQUEST = cc.no_request
                            WHERE b.KD_CABANG = '05'
                                AND a.DELIVERY_KE = 'LUAR'
                            ORDER BY a.TGL_REQUEST DESC
                        ) t
                        LEFT JOIN LatestNota ln ON t.NO_REQUEST = ln.NO_REQUEST AND ln.rn = 1
                        FETCH FIRST 300 ROWS ONLY";
        }

        return DB::connection('uster')->select($query_list);
        // }
    }

    function view($noReq)
    {
        $query_request    = "SELECT REQUEST_DELIVERY.NO_REQUEST, TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, emkl.NM_PBM AS NAMA_EMKL, REQUEST_DELIVERY.NO_RO
		FROM REQUEST_DELIVERY INNER JOIN v_mst_pbm emkl ON REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
		WHERE REQUEST_DELIVERY.NO_REQUEST = '$noReq'";
        $row_request    = DB::connection('uster')->selectOne($query_request);

        $detail_cont    = "SELECT a.NO_REQUEST,
                                a.NO_CONTAINER,
                                a.STATUS,
                                b.SIZE_,
                                b.TYPE_
                            FROM CONTAINER_DELIVERY a, MASTER_CONTAINER b
                            WHERE a.NO_CONTAINER = b.NO_CONTAINER
                                AND a.NO_REQUEST = '$noReq'
                                AND AKTIF = 'Y'
                                AND a.NO_CONTAINER NOT IN (SELECT c.NO_CONTAINER FROM container_delivery c, request_delivery d where c.no_request = d.no_request and d.STATUS = 'EXT' AND d.PERP_DARI = '$noReq')";
        $row_detail    = DB::connection('uster')->select($detail_cont);

        $count      = "SELECT COUNT(a.NO_CONTAINER) JUMLAH FROM CONTAINER_DELIVERY a WHERE a.NO_REQUEST = '$noReq' AND AKTIF = 'Y'";
        $row_count    = DB::connection('uster')->selectOne($count);

        $get_tgl     = "SELECT a.NO_CONTAINER, a.TGL_DELIVERY TGL_DELIVERY FROM CONTAINER_DELIVERY a WHERE a.NO_REQUEST = '$noReq' AND AKTIF = 'Y' ORDER BY a.NO_CONTAINER";
        $row_tgl    = DB::connection('uster')->select($get_tgl);

        return  [
            "row_tgl" =>  $row_tgl,
            "row_request" =>  $row_request,
            "row_detail" =>  $row_detail,
            "row_count" =>  $row_count,
        ];
    }

    function contList($noReq)
    {
        $query_list = "SELECT MASTER_CONTAINER.*, CONTAINER_DELIVERY.* FROM MASTER_CONTAINER INNER JOIN CONTAINER_DELIVERY ON MASTER_CONTAINER.NO_CONTAINER = CONTAINER_DELIVERY.NO_CONTAINER WHERE
		CONTAINER_DELIVERY.NO_REQUEST = '$noReq' AND CONTAINER_DELIVERY.AKTIF = 'Y' ORDER BY CONTAINER_DELIVERY.NO_CONTAINER";
        $result_table    = DB::connection('uster')->select($query_list);

        return  $result_table;
    }

    function updatePerpanjangan($request)
    {

        DB::beginTransaction();;
        try {
            $tgl_perp   = $request->tgl_perpanjangan;
            $tgl_dev    = $request->tgl_dev;
            $no_req     = $request->NO_REQ;
            $total = $request->total;
            for ($i = 0; $i <= $total; $i++) {
                if ($request['TGL_PERP_' . $i] != NULL) {
                    $NO_CONT[$i] = $request['NO_CONT_' . $i];
                    $TGL_PERP[$i] = $request['TGL_PERP_' . $i];
                }
            }
            $id_user    = session()->get('LOGGED_STORAGE');
            $id_yard_   = session()->get('ID_YARD_STORAGE');

            $query      = "SELECT REQ_AWAL, NVL((PERP_KE+1),1) PERP_KE, ID_YARD, DELIVERY_KE, request_delivery.VESSEL, request_delivery.VOYAGE FROM REQUEST_DELIVERY WHERE NO_REQUEST = '$no_req'";
            $cek        = DB::connection('uster')->selectOne($query);

            //$end_stack	= $cek["TGL_END"];
            $perp_ke     = $cek->perp_ke;
            $id_yard     = $cek->id_yard;
            $req_awal    = $cek->req_awal;
            $id_voyage   = $cek->voyage;
            $id_vessel   = $cek->vessel;
            $dev_ke      = $cek->delivery_ke;

            $query_cek    = "select NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_REQUEST,8,13)))+1,6,0),'000001') AS JUM,
                              TO_CHAR(SYSDATE, 'MM') AS MONTH,
                              TO_CHAR(SYSDATE, 'YY') AS YEAR
                       FROM REQUEST_DELIVERY
                       WHERE TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)
                       AND SUBSTR(request_delivery.NO_REQUEST,0,3) = 'DEL'";
            $jum_        = DB::connection('uster')->selectOne($query_cek);
            $jum        = $jum_->jum;
            $month        = $jum_->month;
            $year        = $jum_->year;

            $no_req_dev     = 'DEL' . $month . $year . $jum;

            $query      = "SELECT CASE WHEN KD_AGEN IS NOT NULL THEN KD_AGEN ELSE KD_EMKL END KD_AGEN FROM request_delivery where NO_REQUEST = '$no_req'";
            $cek        = DB::connection('uster')->selectOne($query);
            $id_emkl        = $cek->kd_agen;

            $KETERANGAN = '';

            DB::connection('uster')
                ->table('request_delivery')
                ->insert([
                    'NO_REQUEST' => $no_req_dev,
                    'KD_EMKL' => $id_emkl,
                    'TGL_REQUEST' => DB::raw('SYSDATE'),
                    'REQ_AWAL' => $req_awal,
                    'KETERANGAN' => $KETERANGAN,
                    'CETAK_KARTU' => 0,
                    'ID_USER' => $id_user,
                    'DELIVERY_KE' => $dev_ke,
                    'VESSEL' => $id_vessel,
                    'VOYAGE' => $id_voyage,
                    'PERALIHAN' => 'T',
                    'ID_YARD' => $id_yard,
                    'STATUS' => 'PERP',
                    'PERP_KE' => $perp_ke,
                    'PERP_DARI' => $no_req,
                    'NOTA' => 'T',
                ]);

            for ($g = 0; $g <= $total; $g++) {
                if ($request['TGL_PERP_' . $g] != NULL) {
                    $query      = "SELECT a.KOMODITI, a.STATUS, a.HZ, a.START_STACK, a.VIA, a.ID_YARD, a.NOREQ_PERALIHAN, a.ASAL_CONT, a.TGL_DELIVERY FROM container_delivery a WHERE  a.NO_CONTAINER = '$NO_CONT[$g]' AND NO_REQUEST = '$no_req'";
                    $cek    = DB::connection('uster')->selectOne($query);
                    $status    = $cek->status;
                    $hz     = $cek->hz;
                    $start_stack = $cek->start_stack;
                    $via     = $cek->via;
                    $id_yard_ = $cek->id_yard;
                    $noreq_per     = $cek->noreq_peralihan;
                    $asal_cont = $cek->asal_cont;
                    $endstack = $cek->tgl_delivery;
                    $komoditi = $cek->komoditi;



                    $query_insert           = DB::connection('uster')
                        ->table('CONTAINER_DELIVERY')
                        ->insert([
                            'NO_CONTAINER' => $NO_CONT[$g],
                            'NO_REQUEST' => $no_req_dev,
                            'STATUS' => $status,
                            'AKTIF' => 'Y',
                            'KELUAR' => 'N',
                            'HZ' => $hz,
                            'START_STACK' => DB::raw("TO_DATE('$start_stack','yyyy/mm/dd hh24:mi:ss')"),
                            'START_PERP' => DB::raw("TO_DATE('$endstack','yyyy/mm/dd hh24:mi:ss')"),
                            'TGL_DELIVERY' => DB::raw("TO_DATE('$TGL_PERP[$g]','yyyy/mm/dd')"),
                            'VIA' => $via,
                            'NOREQ_PERALIHAN' => $noreq_per,
                            'ID_YARD' => $id_yard_,
                            'ASAL_CONT' => $asal_cont,
                            'KOMODITI' => $komoditi,
                        ]);
                    $query_update           = DB::connection('uster')
                        ->table('container_delivery')
                        ->where('NO_CONTAINER', $NO_CONT[$g])
                        ->where('NO_REQUEST', $no_req)
                        ->update(['AKTIF' => 'T']);

                    if ($query_insert) {
                        $q_getc = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONT[$g]'";
                        $rw_getc = DB::connection('uster')->selectOne($q_getc);
                        $cur_book = $rw_getc->no_booking;
                        $cur_c = $rw_getc->counter;

                        // echo "insert new container ok";
                        $history                = DB::connection('uster')
                            ->table('history_container')
                            ->insert([
                                'NO_CONTAINER' => $NO_CONT[$g],
                                'NO_REQUEST' => $no_req_dev,
                                'KEGIATAN' => 'PERP DELIVERY',
                                'TGL_UPDATE' => DB::raw('SYSDATE'),
                                'ID_USER' => $id_user,
                                'ID_YARD' => $id_yard_,
                                'STATUS_CONT' => $status,
                                'NO_BOOKING' => 'VESSEL_NOTHING',
                                'COUNTER' => $cur_c
                            ]);
                    }
                }
            }

            DB::commit();
            return [
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ],
            ];
        } catch (Exception $th) {
            DB::rollBack();
            return [
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ];
        }
    }

    function edit($no_req, $no_req_old)
    {


        $query_request    = "SELECT REQUEST_DELIVERY.NO_REQUEST, TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, emkl.NM_PBM AS NAMA_EMKL
                            FROM REQUEST_DELIVERY INNER JOIN v_mst_pbm emkl ON REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                            WHERE REQUEST_DELIVERY.NO_REQUEST = '$no_req'";

        // dd($query_request);

        $row_request    = DB::connection('uster')->selectOne($query_request);

        $detail_cont    = "SELECT a.NO_REQUEST,
                                a.NO_CONTAINER,
                                a.STATUS,
                                b.SIZE_,
                                b.TYPE_
                            FROM CONTAINER_DELIVERY a, MASTER_CONTAINER b
                            WHERE a.NO_CONTAINER = b.NO_CONTAINER
                                AND a.NO_REQUEST = '$no_req_old'
                                AND AKTIF = 'Y'";

        $row_detail    = DB::connection('uster')->select($detail_cont);

        $count      = "SELECT COUNT(a.NO_CONTAINER) JUMLAH FROM CONTAINER_DELIVERY a WHERE a.NO_REQUEST = '$no_req_old' AND AKTIF = 'Y'";

        $row_count    = DB::connection('uster')->selectOne($count);

        $get_tgl     = "SELECT a.NO_CONTAINER, a.TGL_DELIVERY TGL_DELIVERY FROM CONTAINER_DELIVERY a WHERE a.NO_REQUEST = '$no_req_old' AND AKTIF = 'Y' ORDER BY a.NO_CONTAINER";

        $row_tgl    = DB::connection('uster')->select($get_tgl);

        return  [
            "row_tgl" => $row_tgl,
            "no_req" => $no_req_old,
            "no_reqnew" => $no_req,
            "row_request" => $row_request,
            "row_detail" => $row_detail,
            "row_count" => $row_count,
        ];
    }
    function editContList($noReq)
    {
        $query_list = "SELECT MASTER_CONTAINER.*, CONTAINER_DELIVERY.* FROM MASTER_CONTAINER INNER JOIN CONTAINER_DELIVERY ON MASTER_CONTAINER.NO_CONTAINER = CONTAINER_DELIVERY.NO_CONTAINER WHERE
		CONTAINER_DELIVERY.NO_REQUEST = '$noReq' AND CONTAINER_DELIVERY.AKTIF = 'Y' ORDER BY CONTAINER_DELIVERY.NO_CONTAINER";
        $result_table    = DB::connection('uster')->select($query_list);

        return  $result_table;
    }
    function editPerpanjangan($request)
    {

        DB::beginTransaction();
        try {
            $tgl_perp   = $request->tgl_perpanjangan;
            $tgl_dev    = $request->tgl_dev;
            $no_req     = $request->NO_REQ;
            $no_req_new     = $request->NO_REQ_NEW;
            $total = $request->total;
            $id_user    = session()->get('LOGGED_STORAGE');
            $id_yard_   = session()->get('IDYARD_STORAGE');



            for ($i = 0; $i <= $total; $i++) {
                if ($request['TGL_PERP_' . $i] != NULL) {
                    $NO_CONT[$i] = $request['NO_CONT_' . $i];
                    $TGL_PERP[$i] = $request['TGL_PERP_' . $i];
                }
            }

            for ($g = 0; $g <= $total; $g++) {

                if ($request['TGL_PERP_' . $g] != NULL) {
                    $query      = "SELECT a.NO_CONTAINER FROM container_delivery a WHERE  a.NO_CONTAINER = '$NO_CONT[$g]' AND NO_REQUEST = '$no_req_new'";
                    $cek        = DB::connection('uster')->selectOne($query);
                    $no_cont_   = $cek->no_container ?? null;

                    if ($no_cont_ == NULL) {

                        $query      = "SELECT * FROM container_delivery a WHERE a.NO_CONTAINER = '$NO_CONT[$g]' AND NO_REQUEST = '$no_req'";
                        $cek        = DB::connection('uster')->selectOne($query);
                        $status        = $cek->status;
                        $hz         = $cek->hz;
                        $start_stack = $cek->start_stack;
                        $via         = $cek->via;
                        $id_yard_     = $cek->id_yard;
                        $asal_cont = $cek->asal_cont;
                        $noreq_per = $cek->noreq_peralihan;


                        $query      = "SELECT TO_CHAR(TGL_DELIVERY+1, 'dd/mm/yyyy') TGL_END FROM CONTAINER_DELIVERY WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$NO_CONT[$g]'";
                        $cek        = DB::connection('uster')->selectOne($query);
                        $end_stack    = $cek->tgl_end;

                        $start_stack = date('d-m-Y H:i:s', strtotime($start_stack));
                        $end_stack = date('d-m-Y H:i:s', strtotime($end_stack));
                        $TGL_PERP[$g] = date('d-m-Y H:i:s', strtotime($TGL_PERP[$g]));


                        $query_insert           = DB::connection('uster')
                            ->table('CONTAINER_DELIVERY')
                            ->insert([
                                'NO_CONTAINER' => $NO_CONT[$g],
                                'NO_REQUEST' => $no_req_new,
                                'STATUS' => $status,
                                'AKTIF' => 'Y',
                                'KELUAR' => 'N',
                                'HZ' => $hz,
                                'START_STACK' => DB::raw("TO_DATE('$start_stack', 'DD-MM-YYYY HH24:MI:SS')"),
                                'START_PERP' => DB::raw("TO_DATE('$end_stack', 'DD-MM-YYYY HH24:MI:SS')"),
                                'TGL_DELIVERY' => DB::raw("TO_DATE('$TGL_PERP[$g]', 'DD-MM-YYYY HH24:MI:SS')"),
                                'VIA' => $via,
                                'NOREQ_PERALIHAN' => $noreq_per,
                                'ID_YARD' => $id_yard_,
                                'ASAL_CONT' => $asal_cont,
                            ]);

                        DB::connection('uster')
                            ->table('container_delivery')
                            ->where('NO_CONTAINER', $NO_CONT[$g])
                            ->where('NO_REQUEST', $no_req)
                            ->update(['AKTIF' => 'T']);

                        // if ($query_insert) {
                        //     echo "insert new container ok";
                        // }

                        // if ($query_update) {
                        //     echo "update old container OK";
                        // }

                        DB::connection('uster')
                            ->table('history_container')
                            ->insert([
                                'NO_CONTAINER' => $NO_CONT[$g],
                                'NO_REQUEST' => $no_req_new,
                                'KEGIATAN' => 'PERP DELIVERY',
                                'TGL_UPDATE' => DB::raw('SYSDATE'),
                                'ID_USER' => $id_user,
                                'ID_YARD' => $id_yard_,
                                'STATUS_CONT' => $status,
                            ]);
                    } else {
                        DB::connection('uster')
                            ->table('CONTAINER_DELIVERY')
                            ->where('NO_CONTAINER', $NO_CONT[$g])
                            ->where('NO_REQUEST', $no_req_new)
                            ->update(['TGL_DELIVERY' => DB::raw("TO_DATE('$TGL_PERP[$g]', 'yyyy/mm/dd')")]);
                    }
                }
            }

            DB::commit();
            return [
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ],
            ];
        } catch (Exception $th) {
            DB::rollBack();
            return [
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ];
        }
    }
}
