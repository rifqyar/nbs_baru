<?php

namespace App\Services\Request;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class DeliveryService
{

    public function dataDeliveryLuar($request)
    {

        $from       = $request->has('from') ? $request->from : null;
        $to         = $request->has('to') ? $request->to : null;
        $no_req    = isset($request->search['value']) ? $request->search['value'] : null; //$request->no_req;
        // $id_yard    =     $_SESSION["IDYARD_STORAGE"];

        if (isset($from) || isset($to) || isset($no_req)) {
            if ((isset($no_req)) && ($from == NULL) && ($to == NULL)) {
                $query_list = "select * from ( SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.TGL_REQUEST_DELIVERY,a.NOTA, a.KOREKSI,
                                      b.NM_PBM AS NAMA_EMKL,
                                      COUNT(c.NO_CONTAINER) JUMLAH,d.LUNAS
                                FROM REQUEST_DELIVERY a,
                                     V_MST_PBM b,
                                     CONTAINER_DELIVERY c,
                                     NOTA_DELIVERY d
                                WHERE a.DELIVERY_KE = 'LUAR'
                                AND a.NO_REQUEST = d.NO_REQUEST
                                AND a.KD_EMKL = b.KD_PBM
                                AND a.NO_REQUEST = c.NO_REQUEST
								 AND a.NO_REQUEST LIKE '%$no_req%'
								 and A.perp_dari is null
							   AND a.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING')
                                GROUP BY a.NO_REQUEST, a.TGL_REQUEST, a.TGL_REQUEST_DELIVERY,a.NOTA, a.KOREKSI,
                                      b.NM_PBM,d.LUNAS
                                ORDER BY a.TGL_REQUEST DESC) where rownum <= 20";
            } else if (($no_req == NULL) && (isset($from)) && (isset($to))) {
                $query_list = "select * from ( SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.TGL_REQUEST_DELIVERY,a.NOTA, a.KOREKSI,
                                      b.NM_PBM AS NAMA_EMKL,
                                      COUNT(c.NO_CONTAINER) JUMLAH,d.LUNAS
                                FROM REQUEST_DELIVERY a,
                                     V_MST_PBM b,
                                     CONTAINER_DELIVERY c,
                                     NOTA_DELIVERY d
                                WHERE a.DELIVERY_KE = 'LUAR'
                                AND a.NO_REQUEST = d.NO_REQUEST
                                AND a.KD_EMKL = b.KD_PBM
                                AND a.NO_REQUEST = c.NO_REQUEST
								and A.perp_dari is null
								AND a.TGL_REQUEST_DELIVERY BETWEEN TO_DATE('$from','yyyy/mm/dd') AND TO_DATE('$to','yyyy/mm/dd')
							   AND a.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING')
                                GROUP BY a.NO_REQUEST, a.TGL_REQUEST, a.TGL_REQUEST_DELIVERY,a.NOTA, a.KOREKSI,
                                      b.NM_PBM,d.LUNAS
                                ORDER BY a.TGL_REQUEST DESC) where rownum <= 20";
            } else if ((isset($request->no_req)) && (isset($from)) && (isset($to))) {
                $query_list = "select * from ( SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.TGL_REQUEST_DELIVERY,a.NOTA, a.KOREKSI,
                                      b.NM_PBM AS NAMA_EMKL,
                                      COUNT(c.NO_CONTAINER) JUMLAH, d.LUNAS
                                FROM REQUEST_DELIVERY a,
                                      KAPAL_CABANG.MST_PBM b,
                                     CONTAINER_DELIVERY c,
                                     NOTA_DELIVERY d
                                WHERE a.DELIVERY_KE = 'LUAR'
                                AND a.NO_REQUEST = d.NO_REQUEST
                                AND a.KD_EMKL = b.KD_PBM
								AND b.KD_CABANG = '05'
                                AND a.NO_REQUEST = c.NO_REQUEST
								 AND a.NO_REQUEST = '$no_req'
								 and A.perp_dari is null
								AND a.TGL_REQUEST_DELIVERY BETWEEN TO_DATE('$from','yyyy/mm/dd') AND TO_DATE('$to','yyyy/mm/dd')
							   AND a.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING')
                                GROUP BY a.NO_REQUEST, a.TGL_REQUEST, a.TGL_REQUEST_DELIVERY,a.NOTA, a.KOREKSI,
                                      b.NM_PBM,d.LUNAS
                                ORDER BY a.TGL_REQUEST DESC) where rownum <= 20";
            }
        } else {
            $query_list     = "SELECT
                                        *
                                    FROM
                                        (
                                        SELECT
                                            a.NO_REQUEST,
                                            a.TGL_REQUEST,
                                            a.NOTA,
                                            a.KOREKSI,
                                            a.TGL_REQUEST_DELIVERY,
                                            b.NM_PBM AS NAMA_EMKL,
                                            count(c.no_container) jumlah,
                                            d.LUNAS
                                        FROM
                                            REQUEST_DELIVERY a,
                                            container_delivery c,
                                            v_mst_pbm b,
                                            NOTA_DELIVERY d
                                        WHERE
                                            a.DELIVERY_KE = 'LUAR'
                                            AND a.NO_REQUEST = d.NO_REQUEST
                                            AND a.no_request = c.no_request
                                            AND a.KD_EMKL = b.KD_PBM
                                            AND b.KD_CABANG = '05'
                                            AND A.perp_dari IS NULL
                                            AND a.PERALIHAN NOT IN ('RELOKASI', 'STUFFING', 'STRIPPING')
                                        GROUP BY
                                            a.NO_REQUEST,
                                            a.TGL_REQUEST,
                                            a.NOTA,
                                            a.KOREKSI,
                                            b.NM_PBM,
                                            a.TGL_REQUEST_DELIVERY,
                                            d.LUNAS
                                        ORDER BY
                                            a.TGL_REQUEST DESC)
                                    WHERE
                                        ROWNUM <= 100";
        }
        return DB::connection('uster')->select($query_list);
    }

    public function cekNota($noReq)
    {

        $queryCheck    = "SELECT A.NOTA, A.KOREKSI, B.LUNAS FROM REQUEST_DELIVERY A LEFT JOIN NOTA_DELIVERY B ON A.NO_REQUEST = B.NO_REQUEST WHERE A.NO_REQUEST = '$noReq' ORDER BY A.TGL_REQUEST DESC";
        $resultCheck   = DB::connection('uster')->select($queryCheck);
        $rowCheck      = count($resultCheck) > 0 ? $resultCheck[0] : null;

        $nota        = $rowCheck->nota;
        $koreksi     = $rowCheck->koreksi;
        $lunas       = $rowCheck->lunas;



        if ($lunas == 'NO') {
            return '<a class="btn btn-warning w-100" href="' . route('request.delivery.delivery_luar.edit', ['no_req' => $noReq]) . '" target="_blank"> EDIT </a> ';
        } else {
            if (($nota <> 'Y') and ($koreksi <> 'Y')) {
                return '<a class="btn btn-warning w-100" href="' . route('request.delivery.delivery_luar.edit', ['no_req' => $noReq]) . '" target="_blank"> EDIT </a> ';
                //return '<a class="btn btn-primary w-100" href="'.HOME.APPID.'/cetak_nota?no_nota='.$no_nota.'&n='.$cetak.'" target="_blank"> CETAK ULANG </a> ';
            } else if (($nota == 'Y') and ($koreksi <> 'Y')) {
                return '<a class="btn btn-primary w-100" href="' . route('request.delivery.delivery_luar.view', ['no_req' => $noReq]) . '" target="_blank" > Nota sudah cetak </a> ';
            } else if (($nota == 'Y') and ($koreksi == 'Y')) {
                return '<a class="btn btn-primary w-100" href="' . route('request.delivery.delivery_luar.view', ['no_req' => $noReq]) . '" target="_blank" > Nota sudah cetak </a> ';
                //return '<a class="btn btn-primary w-100" href="'.HOME.APPID.'/edit?no_req='.$noReq.'" target="_blank"> EDIT </a> ';
            } else if (($nota <> 'Y') and ($koreksi == 'Y')) {
                return '<a class="btn btn-warning w-100" href="' . route('request.delivery.delivery_luar.edit', ['no_req' => $noReq]) . '" target="_blank"> EDIT </a> ';
            }
        }
    }

    function view($noReq)
    {
        $query_request    = "SELECT REQUEST_DELIVERY.KETERANGAN, REQUEST_DELIVERY.NO_RO, request_delivery.DELIVERY_KE, REQUEST_DELIVERY.NO_REQUEST, TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM AS NAMA_EMKL, emkl.ALMT_PBM, emkl.NO_NPWP_PBM, request_delivery.VESSEL, request_delivery.VOYAGE, emkll.NM_PBM AS NM_PENUMPUKAN
        FROM REQUEST_DELIVERY INNER JOIN v_mst_pbm emkl ON REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
		inner join v_mst_pbm emkll on request_delivery.kd_agen = emkll.kd_pbm
        WHERE REQUEST_DELIVERY.NO_REQUEST = '$noReq'";
        $result_request    = DB::connection('uster')->selectOne($query_request);

        $query_list = "SELECT MASTER_CONTAINER.*, CONTAINER_DELIVERY.* FROM MASTER_CONTAINER INNER JOIN CONTAINER_DELIVERY ON MASTER_CONTAINER.NO_CONTAINER = CONTAINER_DELIVERY.NO_CONTAINER WHERE CONTAINER_DELIVERY.NO_REQUEST = '$noReq'";
        $result_table    = DB::connection('uster')->select($query_list);

        return  [
            'result_request' => $result_request,
            'result_table' => $result_table
        ];
    }

    function edit($noReq)
    {
        $query_request    = "SELECT  REQUEST_DELIVERY.KETERANGAN, REQUEST_DELIVERY.NO_RO, REQUEST_DELIVERY.NO_REQUEST, agen.NM_PBM NM_AGEN, REQUEST_DELIVERY.KD_AGEN, TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,'yyyy/mm/dd') TGL_REQUEST_DELIVERY, emkl.NM_PBM AS NAMA_EMKL, emkl.KD_PBM ID,  emkl.ALMT_PBM ALAMAT,  emkl.NO_NPWP_PBM NPWP, request_delivery.VESSEL, request_delivery.VOYAGE
                                FROM request_delivery, V_MST_PBM emkl, V_MST_PBM agen
                                WHERE request_delivery.KD_EMKL = emkl.KD_PBM
                                AND request_delivery.KD_AGEN = agen.KD_PBM
                                AND REQUEST_DELIVERY.NO_REQUEST = '$noReq'";
        $result_request    = DB::connection('uster')->selectOne($query_request);

        // $query_list = "SELECT MASTER_CONTAINER.*, CONTAINER_DELIVERY.*, YARD_AREA.NAMA_YARD
        //                 FROM MASTER_CONTAINER LEFT JOIN CONTAINER_DELIVERY ON MASTER_CONTAINER.NO_CONTAINER = CONTAINER_DELIVERY.NO_CONTAINER
        //                 LEFT JOIN YARD_AREA ON CONTAINER_DELIVERY.ID_YARD= YARD_AREA.ID
        //                 WHERE CONTAINER_DELIVERY.NO_REQUEST = '$noReq'";



        return  [
            'row_request' => $result_request
        ];
    }

    function editDataTables($noReq)
    {
        $query_list = "SELECT MASTER_CONTAINER.*,
                                CONTAINER_DELIVERY.*,
                                YARD_AREA.NAMA_YARD,
                                TO_CHAR (CONTAINER_DELIVERY.TGL_DELIVERY, 'YYYY-MM-DD') as TGL_DELIVERY
                        FROM MASTER_CONTAINER
                                LEFT JOIN CONTAINER_DELIVERY
                                ON MASTER_CONTAINER.NO_CONTAINER = CONTAINER_DELIVERY.NO_CONTAINER
                                LEFT JOIN YARD_AREA ON CONTAINER_DELIVERY.ID_YARD= YARD_AREA.ID
                                LEFT JOIN HISTORY_CONTAINER
                                ON CONTAINER_DELIVERY.NO_CONTAINER = HISTORY_CONTAINER.NO_CONTAINER
                                AND CONTAINER_DELIVERY.NO_REQUEST = HISTORY_CONTAINER.NO_REQUEST
                                AND HISTORY_CONTAINER.KEGIATAN = 'REQUEST_DELIVERY'
                        WHERE CONTAINER_DELIVERY.NO_REQUEST = '$noReq'
                        ORDER BY HISTORY_CONTAINER.TGL_UPDATE ASC";
        $result_table    = DB::connection('uster')->select($query_list);

        return  $result_table;
    }

    function getNoContainer($no_cont)
    {

        $no_cont = strtoupper($no_cont);
        $query             = "SELECT DISTINCT a.NO_CONTAINER,
                                   a.SIZE_ AS SIZE_,
                                   a.TYPE_ AS TYPE_ ,
                                   b.STATUS_CONT STATUS,
                                   '' BP_ID,
                                   TO_DATE('','dd/mm/rrrr') TGL_BONGKAR,
                                   TO_DATE('','dd/mm/rrrr') TGL_END,
								   'UST' ASAL
                    FROM MASTER_CONTAINER a
                        LEFT JOIN HISTORY_CONTAINER b
                            ON A.NO_CONTAINER = B.NO_CONTAINER
                            --AND A.NO_BOOKING = B.NO_BOOKING
                            --AND A.COUNTER = B.COUNTER
                        LEFT JOIN PLACEMENT
                            ON a.NO_CONTAINER = PLACEMENT.NO_CONTAINER
                    --WHERE a.NO_CONTAINER LIKE '$no_cont%' AND a.LOCATION = 'IN_YARD'
                    WHERE a.NO_CONTAINER LIKE '$no_cont%'
                    --AND B.TGL_UPDATE = (SELECT MAX(TGL_UPDATE) FROM HISTORY_CONTAINER WHERE NO_CONTAINER LIKE '$no_cont%')
                   -- AND B.STATUS_CONT = 'MTY'
                    --and b.kegiatan IN ('REALISASI STRIPPING','GATE IN','BATAL STUFFING','BATAL RECEIVING','REQUEST BATALMUAT','REQUEST DELIVERY', 'BORDER GATE IN', 'BATAL STRIPPING', 'PERPANJANGAN STRIPPING')
					--UNION
                 -- select NO_CONTAINER, KD_SIZE SIZE_,KD_TYPE TYPE_, STATUS_CONT STATUS, BP_ID,
				--	TO_DATE(TGL_STACK,'dd/mm/rrrr') TGL_BONGKAR, TO_DATE(TGL_STACK,'dd/mm/rrrr')+4 TGL_END, 'TPK' ASAL
                --  from petikemas_cabang.V_MTY_AREA_TPK
				--	where NO_CONTAINER LIKE '$no_cont%' and TO_DATE(TGL_STACK,'dd/mm/rrrr') > TO_DATE('01/04/2013','dd/mm/rrrr')
                ";

        // $query             = "SELECT DISTINCT a.NO_CONTAINER,
        //                         a.SIZE_ AS SIZE_,
        //                         a.TYPE_ AS TYPE_ ,
        //                         b.STATUS_CONT STATUS,
        //                         '' BP_ID,
        //                         TO_DATE('','dd/mm/rrrr') TGL_BONGKAR,
        //                         TO_DATE('','dd/mm/rrrr') TGL_END,
        //                         'UST' ASAL
        //                         FROM MASTER_CONTAINER a
        //                         LEFT JOIN HISTORY_CONTAINER b
        //                         ON A.NO_CONTAINER = B.NO_CONTAINER
        //                         --AND A.NO_BOOKING = B.NO_BOOKING
        //                         --AND A.COUNTER = B.COUNTER
        //                         LEFT JOIN PLACEMENT
        //                         ON a.NO_CONTAINER = PLACEMENT.NO_CONTAINER
        //                         WHERE a.NO_CONTAINER LIKE '$no_cont%' AND a.LOCATION = 'IN_YARD'
        //                         AND B.TGL_UPDATE = (SELECT MAX(TGL_UPDATE) FROM HISTORY_CONTAINER WHERE NO_CONTAINER LIKE '$no_cont%')
        //                         -- AND B.STATUS_CONT = 'MTY'
        //                         and b.kegiatan IN ('REALISASI STRIPPING','GATE IN','BATAL STUFFING','BATAL RECEIVING','REQUEST BATALMUAT','REQUEST DELIVERY', 'BORDER GATE IN', 'BATAL STRIPPING', 'PERPANJANGAN STRIPPING')
        //                         --UNION
        //                         -- select NO_CONTAINER, KD_SIZE SIZE_,KD_TYPE TYPE_, STATUS_CONT STATUS, BP_ID,
        //                         --	TO_DATE(TGL_STACK,'dd/mm/rrrr') TGL_BONGKAR, TO_DATE(TGL_STACK,'dd/mm/rrrr')+4 TGL_END, 'TPK' ASAL
        //                         --  from petikemas_cabang.V_MTY_AREA_TPK
        //                         --	where NO_CONTAINER LIKE '$no_cont%' and TO_DATE(TGL_STACK,'dd/mm/rrrr') > TO_DATE('01/04/2013','dd/mm/rrrr')
        //                     ";
        $result_query    = DB::connection('uster')->select($query);

        return $result_query;
    }

    function getTglStack($no_cont)
    {
        $query_tgl_stack_depo = "SELECT NO_REQUEST, KEGIATAN
											FROM HISTORY_CONTAINER
											WHERE no_container = '$no_cont'
											AND kegiatan IN ('GATE IN','REALISASI STRIPPING','BORDER GATE IN')
											ORDER BY TGL_UPDATE DESC";

        $row_tgl_stack_depo        = DB::connection('uster')->selectOne($query_tgl_stack_depo);
        // $tgl_stack	= $row_tgl_stack_depo->tgl_stack;
        $ex_keg    = $row_tgl_stack_depo->kegiatan;
        $no_re_st    = $row_tgl_stack_depo->no_request;
        if ($ex_keg == "REALISASI STRIPPING") {
            $qtgl_r = "SELECT TGL_REALISASI FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'";
            $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
            $tgl_stack = $rtgl_r->tgl_realisasi;
        } else if ($ex_keg == "GATE IN") {
            $qtgl_r = "SELECT TGL_IN FROM GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'";
            $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
            $tgl_stack = $rtgl_r->tgl_in;
        } else if ($ex_keg == "BORDER GATE IN") {
            $qtgl_r = "SELECT TGL_IN FROM BORDER_GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'";
            $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
            $tgl_stack = $rtgl_r->tgl_in;
        }


        $hasil = "SELECT TO_CHAR(TO_DATE('$tgl_stack','yyyy-mm-dd hh24:mi:ss'),'rrrr-mm-dd') TGL_BONGKAR, TO_CHAR(TO_DATE('$tgl_stack','yyyy-mm-dd hh24:mi:ss')+4,'rrrr-mm-dd') EMPTY_SD FROM DUAL";
        $rhasil = DB::connection('uster')->selectOne($hasil);

        return $rhasil;
    }

    function deleteEditDataTables($request)
    {
        DB::beginTransaction();;
        try {
            $no_cont            = $request->NO_CONT;
            $no_req             = $request->NO_REQ;
            $bp_id              = $request->BP_ID;


            $query_del          = DB::connection('uster')->table('CONTAINER_DELIVERY')
                ->where('NO_CONTAINER', $no_cont)
                ->where('NO_REQUEST', $no_req)
                ->delete();

            //history
            DB::connection('uster')->table('history_container')
                ->where('NO_CONTAINER', $no_cont)
                ->where('NO_REQUEST', $no_req)
                ->delete();


            if ($query_del) {
                //update_location
                DB::connection('uster')->table('MASTER_CONTAINER')
                    ->where('NO_CONTAINER', $no_cont)
                    ->update(['LOCATION' => 'IN_YARD']);
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


    function addContDeliveryLuar($request)
    {
        DB::beginTransaction();;
        try {
            $no_cont        = $request->NO_CONT;
            $no_req         = $request->NO_REQ;
            $status         = $request->STATUS;
            $hz             = $request->HZ;
            $keterangan     = $request->KETERANGAN;
            $no_seal        = $request->NO_SEAL;
            $berat          = $request->BERAT;
            $via            = $request->VIA;
            $komoditi       = $request->KOMODITI;
            $start_pnkn     = $request->start_pnkn;
            $end_pnkn       = $request->end_pnkn;
            $size           = $request->SIZE;
            $type           = $request->TYPE;
            $id_user        = session()->get('LOGGED_STORAGE');
            $bp_id          = $request->BP_ID;
            $asal_auto_cont = $request->ASAL_CONT;

            $query_cek_cont            = "SELECT NO_BOOKING, COUNTER, LOCATION
							FROM  MASTER_CONTAINER
							WHERE NO_CONTAINER ='$no_cont'
							";

            $row_cek_cont     = DB::connection('uster')->selectOne($query_cek_cont);
            $cek_book         = 'VESSEL_NOTHING';
            $cek_counter    = $row_cek_cont->counter;
            $cek_location    = $row_cek_cont->location;

            if ($cek_location != 'IN_YARD') {
                echo "con_yard";
                exit();
            }

            $cek_gato = "SELECT AKTIF
                FROM CONTAINER_RECEIVING
               WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y' ORDER BY AKTIF DESC";
            $r_gato = DB::connection('uster')->selectOne($cek_gato);
            $l_gato = $r_gato->aktif ?? '';
            if ($l_gato == 'Y') {
                echo "con_yard";
                die();
            }


            if ($cek_book == NULL) {

                // [2] - Update ke tabel MASTER_CONTAINER
                // Masih belum jelas, karena no_booking tidak didapat
                // $q_update_book = "UPDATE MASTER_CONTAINER SET NO_BOOKING = '$no_booking' WHERE NO_CONTAINER = '$no_cont'";

                // DB::connection('uster')
                //     ->table('MASTER_CONTAINER')
                //     ->where('NO_CONTAINER', $no_cont)
                //     ->update(['NO_BOOKING' => $no_booking]);

                // echo "update booking master_cont baru";die;

                // $db->query($q_update_book);
            }

            $query_cek_mst_cont            = "SELECT COUNT(NO_CONTAINER) JML
							FROM  MASTER_CONTAINER
							WHERE NO_CONTAINER ='$no_cont'
							";
            // $result_cek_mst_cont = $db->query($query_cek_mst_cont);
            $row_cek_mst_cont     = DB::connection('uster')->selectOne($query_cek_mst_cont);
            $cek_mst         = $row_cek_mst_cont->jml;

            // debug($cek_mst);die;

            if ($cek_book == NULL) {
                $cek_book == 'VESSEL_NOTHING';
            }

            if ($cek_mst == 0) {

                // [3] - Insert ke tabel MASTER_CONTAINER
                // Jika container baru pertama masuk ke Uster, atau belem ada di master container, maka diinsert di master_container
                // $q_insert_no_container = "INSERT INTO MASTER_CONTAINER(NO_CONTAINER, SIZE_, TYPE_, LOCATION, NO_BOOKING, COUNTER) VALUES('$no_cont','$size','$type','IN_YARD','$cek_book','1')";
                DB::connection('uster')
                    ->table('MASTER_CONTAINER')
                    ->insert([
                        'NO_CONTAINER' => $no_cont,
                        'SIZE_' => $size,
                        'TYPE_' => $type,
                        'LOCATION' => 'IN_YARD',
                        'NO_BOOKING' => $cek_book,
                        'COUNTER' => 1,
                    ]);

                // echo "insert master_cont";die;

                // $db->query($q_insert_no_container);
            } else {
                // [4] - Update ke tabel MASTER_CONTAINER
                // belum jelas peruntukkannya
                // $q_update_book2 = "UPDATE MASTER_CONTAINER SET NO_BOOKING = '$cek_book', COUNTER = '$cek_counter' WHERE NO_CONTAINER = '$no_cont'";

                DB::connection('uster')
                    ->table('MASTER_CONTAINER')
                    ->where('NO_CONTAINER', $no_cont)
                    ->update([
                        'NO_BOOKING' => $cek_book,
                        'COUNTER' => $cek_counter
                    ]);


                // echo "update book master_cont lama";die;

                // $db->query($q_update_book2);
            }

            // $q_update_book2 = "UPDATE MASTER_CONTAINER SET NO_BOOKING = '$no_booking' WHERE NO_CONTAINER = '$no_cont'";
            // $db->query($q_update_book2);

            $query_cek        = "SELECT b.NO_CONTAINER, b.LOCATION, NVL((SELECT NO_CONTAINER FROM CONTAINER_DELIVERY WHERE NO_CONTAINER = '$no_cont' AND AKTIF = 'Y' AND ROWNUM = 1), '') as STATUS FROM  MASTER_CONTAINER b WHERE  b.NO_CONTAINER = '$no_cont'";
            //echo $query_cek;die;
            // $result_cek        = $db->query($query_cek);
            $row_cek        = DB::connection('uster')->selectOne($query_cek);
            $no_cont        = $row_cek->no_container;
            $location        = $row_cek->location;
            $req_dev                = $row_cek->status;
            if ($asal_auto_cont == 'TPK') {
                $location = 'IN_YARD';
            }

            // debug($row_cek);die;

            //ECHO $query_cek;

            if (($no_cont <> NULL) && ($location == 'IN_YARD') && ($req_dev <> NULL)) {
                echo "SDH_REQUEST";
                exit();
            } else if (($no_cont <> NULL) && ($location == 'GATI') && ($req_dev == NULL)) {
                echo "BLM_PLACEMENT";
                exit();
            } else  if (($no_cont <> NULL) && ($location == 'IN_YARD') && ($req_dev == NULL)) {
                $query_cek        = "SELECT a.ID_YARD_AREA FROM placement b, blocking_area a
                        WHERE b.ID_BLOCKING_AREA = a.ID
                        AND b.NO_CONTAINER = '$no_cont'";
                // $result_cek        = $db->query($query_cek);
                $row_cek        = DB::connection('uster')->selectOne($query_cek);
                $id_yard        = $row_cek->id_yard_area ?? '';

                $q_getc = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont'";
                // $r_getc = $db->query($q_getc);
                $rw_getc = DB::connection('uster')->selectOne($q_getc);
                $cur_book = $rw_getc->no_booking;
                $cur_c = $rw_getc->counter;

                if ($asal_auto_cont == 'TPK') {
                    if ($cur_book == NULL) {
                        $cur_book = "VESSEL_NOTHING";
                        // $db->query("UPDATE MASTER_CONTAINER SET NO_BOOKING = 'VESSEL_NOTHING', LOCATION = 'IN_YARD' WHERE NO_CONTAINER = '$no_cont'");
                        DB::connection('uster')
                            ->table('MASTER_CONTAINER')
                            ->where('NO_CONTAINER', $no_cont)
                            ->update([
                                'NO_BOOKING' => 'VESSEL_NOTHING',
                                'LOCATION' => 'IN_YARD'
                            ]);
                    }
                } else {
                    if ($cur_book == NULL) {
                        $cur_book = "VESSEL_NOTHING";
                        // $db->query("UPDATE MASTER_CONTAINER SET NO_BOOKING = 'VESSEL_NOTHING' WHERE NO_CONTAINER = '$no_cont'"
                        DB::connection('uster')
                            ->table('MASTER_CONTAINER')
                            ->where('NO_CONTAINER', $no_cont)
                            ->update([
                                'NO_BOOKING' => 'VESSEL_NOTHING'
                            ]);
                    }
                }



                // $history  = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT)
                //                                           VALUES ('$no_cont','$no_req','REQUEST DELIVERY',SYSDATE,'$id_user','$id_yard','VESSEL_NOTHING','$cur_c','$status')";

                // $db->query($history);

                DB::connection('uster')
                    ->table('history_container')
                    ->insert([
                        'NO_CONTAINER' => $no_cont,
                        'NO_REQUEST' => $no_req,
                        'KEGIATAN' => 'REQUEST DELIVERY',
                        'TGL_UPDATE' => DB::raw('SYSDATE'), // Menggunakan fungsi raw untuk memasukkan fungsi SQL langsung
                        'ID_USER' => $id_user,
                        'ID_YARD' => $id_yard,
                        'NO_BOOKING' => 'VESSEL_NOTHING',
                        'COUNTER' => $cur_c,
                        'STATUS_CONT' => $status
                    ]);

                // mengetahui tanggal start_stack
                $query_cek1        = "SELECT tes.NO_REQUEST,
                                    CASE SUBSTR(KEGIATAN,9)
                                        WHEN 'RECEIVING' THEN (SELECT CONCAT('RECEIVING_',a.RECEIVING_DARI) FROM request_receiving a WHERE a.NO_REQUEST = tes.NO_REQUEST)
                                        ELSE SUBSTR(KEGIATAN,9)
                                    END KEGIATAN FROM (SELECT TGL_UPDATE, NO_REQUEST,KEGIATAN FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI')) tes
                                    WHERE tes.TGL_UPDATE=(SELECT MAX(TGL_UPDATE) FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI'))";
                // $result_cek1        = $db->query($query_cek1);
                $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                $no_request        = $row_cek1->no_request;
                $kegiatan        = $row_cek1->kegiatan;

                if ($kegiatan == 'RECEIVING_LUAR') {
                    $query_cek1        = " SELECT SUBSTR(TO_CHAR(b.TGL_IN,'dd/mm/rrrr'),1,10) START_STACK FROM GATE_IN b WHERE b.NO_CONTAINER = '$no_cont' AND b.NO_REQUEST = '$no_request'";
                    // $result_cek1    = $db->query($query_cek1);
                    $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                    $start_stack    = $row_cek1->start_stack;
                    $asal_cont         = 'DEPO';
                } else if ($kegiatan == 'RECEIVING_TPK') {
                    $query_cek1        = "SELECT TGL_BONGKAR START_STACK FROM container_receiving WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                    // $result_cek1    = $db->query($query_cek1);
                    $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                    $start_stack    = $row_cek1->start_stack;
                    $asal_cont         = 'TPK';
                } else if ($kegiatan == 'STUFFING') {
                    $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'MM/DD/YYYY'),1,10) START_STACK FROM container_stuffing WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                    // $result_cek1    = $db->query($query_cek1);
                    $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                    $start_stack    = $row_cek1->start_stack;
                    $asal_cont         = 'DEPO';
                } else if ($kegiatan == 'STRIPPING') {
                    $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'MM/DD/YYYY'),1,10) START_STACK FROM container_stripping WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                    // $result_cek1    = $db->query($query_cek1);
                    $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                    $start_stack    = $row_cek1->start_stack;
                    $asal_cont         = 'DEPO';
                }

                $query_tgl_stack_depo = "SELECT TGL_UPDATE , NO_REQUEST, KEGIATAN, NO_BOOKING
                                            FROM HISTORY_CONTAINER
                                            WHERE no_container = '$no_cont'
                                            AND kegiatan IN ('GATE IN','REALISASI STRIPPING', 'BORDER GATE IN')
                                            ORDER BY TGL_UPDATE DESC";

                // $tgl_stack_depo    = $db->query($query_tgl_stack_depo);
                $row_tgl_stack_depo        = DB::connection('uster')->selectOne($query_tgl_stack_depo);
                // $start_stack    = $row_tgl_stack_depo->tgl_stack;
                $ex_keg    = $row_tgl_stack_depo->kegiatan;
                $no_re_st    = $row_tgl_stack_depo->no_request;
                $no_booking_l    = $row_tgl_stack_depo->no_booking;
                if ($ex_keg == "REALISASI STRIPPING") {
                    $qtgl_r = "SELECT TGL_REALISASI FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'";
                    $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
                    $start_stack = $rtgl_r->tgl_realisasi;
                    $asal_cont         = 'DEPO';
                } else if ($ex_keg == "GATE IN") {
                    $qtgl_r = "SELECT SUBSTR(TO_CHAR(b.TGL_IN,'dd/mm/rrrr'),1,10) START_STACK FROM GATE_IN b WHERE b.NO_CONTAINER = '$no_cont' AND b.NO_REQUEST = '$no_re_st'";
                    $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
                    $start_stack = $rtgl_r->start_stack;
                    $asal_cont         = 'DEPO';
                } else if ($ex_keg == "BORDER GATE IN") {
                    $qtgl_r = "SELECT SUBSTR(TO_CHAR(b.TGL_IN,'dd/mm/rrrr'),1,10) START_STACK FROM BORDER_GATE_IN b WHERE b.NO_CONTAINER = '$no_cont' AND b.NO_REQUEST = '$no_re_st'";
                    $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
                    $start_stack = $rtgl_r->start_stack;
                    $asal_cont         = 'DEPO';
                }

                // $start_stack = Carbon::parse($start_stack)->format('d/m/Y');
                // $formattedStartStack = Carbon::parse('Y-m-d H:i:s', trim($start_stack))->format('d/m/Y');
                // $formattedEndPnkn = Carbon::parse('Y-m-d', trim($end_pnkn))->format('d/m/Y');
                $formattedStartStack = date('d/m/Y', strtotime($start_pnkn));
                $formattedEndPnkn = date('d/m/Y', strtotime($end_pnkn));
                // $query_insert   = "INSERT INTO CONTAINER_DELIVERY(NO_CONTAINER, NO_REQUEST, STATUS, AKTIF, KELUAR,HZ, KOMODITI,KETERANGAN,NO_SEAL,BERAT,VIA, ID_YARD, NOREQ_PERALIHAN, START_STACK, ASAL_CONT, TGL_DELIVERY)
                // VALUES('$no_cont', '$no_req', '$status','Y','N','$hz','$komoditi','$keterangan','$no_seal','$berat','$via','$id_yard','$no_request',TO_DATE('$start_stack','dd/mm/rrrr'),'$asal_cont', TO_DATE('$end_pnkn','dd/mm/rrrr'))";

                $query_insert =  DB::connection('uster')
                    ->table('CONTAINER_DELIVERY')
                    ->insert([
                        'NO_CONTAINER' => $no_cont,
                        'NO_REQUEST' => $no_req,
                        'STATUS' => $status,
                        'AKTIF' => 'Y',
                        'KELUAR' => 'N',
                        'HZ' => $hz,
                        'KOMODITI' => $komoditi,
                        'KETERANGAN' => $keterangan,
                        'NO_SEAL' => $no_seal,
                        'BERAT' => $berat,
                        'VIA' => $via,
                        'ID_YARD' => $id_yard,
                        'NOREQ_PERALIHAN' => $no_request,
                        'START_STACK' => DB::raw("TO_DATE('$formattedStartStack', 'DD/MM/YYYY')"),
                        'ASAL_CONT' => $asal_cont,
                        'TGL_DELIVERY' => DB::raw("TO_CHAR(TO_DATE('$formattedEndPnkn', 'DD-MM-YYYY'), 'YYYY-MM-DD')")
                    ]);


                if ($start_stack == NULL) {
                    // $start_pnkn = '1/4/2013';
                    //echo "TGL_TUMPUK";
                    // $history  = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, NO_BOOKING, COUNTER, STATUS_CONT)
                    //                                       VALUES ('$no_cont','$no_req','REQUEST DELIVERY',SYSDATE,'$id_user','VESSEL_NOTHING','$cur_c','$status')";
                    DB::connection('uster')
                        ->table('history_container')
                        ->insert([
                            'NO_CONTAINER' => $no_cont,
                            'NO_REQUEST' => $no_req,
                            'KEGIATAN' => 'REQUEST DELIVERY',
                            'TGL_UPDATE' => DB::raw('SYSDATE'), // Menggunakan fungsi raw untuk memasukkan fungsi SQL langsung
                            'ID_USER' => $id_user,
                            'NO_BOOKING' => 'VESSEL_NOTHING',
                            'COUNTER' => $cur_c,
                            'STATUS_CONT' => $status
                        ]);

                    //$db->query($history);

                    //     $query_insert   = "INSERT INTO CONTAINER_DELIVERY(NO_CONTAINER, NO_REQUEST, STATUS, AKTIF, KELUAR,HZ, KOMODITI,KETERANGAN,NO_SEAL,BERAT,VIA, ID_YARD, NOREQ_PERALIHAN, START_STACK, ASAL_CONT, TGL_DELIVERY, EX_BP_ID)
                    // VALUES('$no_cont', '$no_req', '$status','Y','N','$hz','$komoditi','$keterangan','$no_seal','$berat','$via','$id_yard','$no_request',TO_DATE('$start_pnkn','dd/mm/rrrr'),'DEPO', TO_DATE('$end_pnkn','dd/mm/rrrr'), '$bp_id')";

                    //     $db->query($query_insert);
                    //     echo "OK";
                    // exit();
                    DB::connection('uster')
                        ->table('CONTAINER_DELIVERY')
                        ->insert([
                            'NO_CONTAINER' => $no_cont,
                            'NO_REQUEST' => $no_req,
                            'STATUS' => $status,
                            'AKTIF' => 'Y',
                            'KELUAR' => 'N',
                            'HZ' => $hz,
                            'KOMODITI' => $komoditi,
                            'KETERANGAN' => $keterangan,
                            'NO_SEAL' => $no_seal,
                            'BERAT' => $berat,
                            'VIA' => $via,
                            'ID_YARD' => $id_yard,
                            'NOREQ_PERALIHAN' => $no_request,
                            'START_STACK' => DB::raw("TO_DATE('$start_pnkn', 'DD/MM/YYYY')"),
                            'ASAL_CONT' => 'DEPO',
                            'TGL_DELIVERY' => DB::raw("TO_DATE('$end_pnkn', 'DD/MM/YYYY')"),
                            'EX_BP_ID' => $bp_id
                        ]);
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

    function saveDeliveryLuar($request)
    {
        DB::beginTransaction();
        try {
            $TGL_REQ    = $request->tgl_dev;
            $DEV_KE     = $request->dev_ke;

            //$ID_PBM	= $request->ID_PBM;
            $EMKL        = $request->ID_EMKL;
            $ALMT_PBM    = $request->ALMT_PBM;
            $NPWP        = $request->NPWP;
            $ASAL       = $request->ASAL;
            $TUJUAN     = $request->TUJUAN;
            $PEB        = $request->peb;
            $NPE         = $request->npe;
            $NO_BOOKING    = $request->NO_BOOKING;
            $NO_RO        = $request->NO_RO;
            $AC_EMKL        = $request->AC_EMKL;
            $KD_AGEN        = $request->KD_AGEN;
            $KETERANGAN    = $request->keterangan;
            $ID_USER    = session()->get('LOGGED_STORAGE');
            $ID_YARD    = session()->get('IDYARD_STORAGE');

            $query_cek    = "SELECT LPAD(NVL(MAX(SUBSTR(NO_REQUEST,8,13)),0)+1,6,0) AS JUM ,
				   TO_CHAR(SYSDATE, 'MM') AS MONTH,
				   TO_CHAR(SYSDATE, 'YY') AS YEAR
				   FROM REQUEST_DELIVERY
				   WHERE TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE) ";

            // $result_cek    = $db->query($query_cek);
            $jum_        = DB::connection('uster')->selectOne($query_cek);
            $jum        = $jum_->jum;
            $month        = $jum_->month;
            $year        = $jum_->year;

            $no_req    = "DEL" . $month . $year . $jum;

            // $query_req     = "INSERT INTO request_delivery(NO_REQUEST, REQ_AWAL, TGL_REQUEST, TGL_REQUEST_DELIVERY, KETERANGAN, DELIVERY_KE, CETAK_KARTU, ID_USER,  PERALIHAN, ID_YARD, STATUS,KD_EMKL, NO_RO, KD_AGEN)
            //                                   VALUES ('$no_req', '$no_req',SYSDATE, TO_DATE('" . $TGL_REQ . "','yyyy/mm/dd'),'$KETERANGAN', 'LUAR','0', $ID_USER,'T','$ID_YARD','NEW','$EMKL', '$NO_RO', '$KD_AGEN')";
            $query_req     = DB::connection('uster')
                ->table('request_delivery')
                ->insert([
                    'NO_REQUEST' => $no_req,
                    'REQ_AWAL' => $no_req,
                    'TGL_REQUEST' => DB::raw('SYSDATE'),
                    'TGL_REQUEST_DELIVERY' => DB::raw("TO_DATE('$TGL_REQ', 'YYYY/MM/DD')"),
                    'KETERANGAN' => $KETERANGAN,
                    'DELIVERY_KE' => 'LUAR',
                    'CETAK_KARTU' => '0',
                    'ID_USER' => $ID_USER,
                    'PERALIHAN' => 'T',
                    'ID_YARD' => $ID_YARD,
                    'STATUS' => 'NEW',
                    'KD_EMKL' => $EMKL,
                    'NO_RO' => $NO_RO,
                    'KD_AGEN' => $KD_AGEN
                ]);

            DB::commit();
            return [
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                    'no_req' => $no_req
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

    function saveEditDeliveryLuar($request)
    {
        DB::beginTransaction();;
        try {
            $TGL_REQ    = $request["tgl_dev"];
            $DEV_KE     = $request["dev_ke"];
            $NO_REQ     = $request["NO_REQ"];
            $ID_EMKL    = $request["ID_EMKL"];
            $ASAL       = $request["ASAL"];
            $TUJUAN     = $request["TUJUAN"];
            $NO_BOOKING    = $request["NO_BOOKING"];
            $PEB        = $request["peb"];
            $NPE         = $request["npe"];
            $KD_AGEN        = $request["KD_AGEN"];
            $KETERANGAN    = $request["keterangan"];
            $ID_USER    = session()->get("LOGGED_STORAGE");
            $ID_YARD    = session()->get("IDYARD_STORAGE");

            $query_req = DB::connection('uster')
                ->table('request_delivery')
                ->where('NO_REQUEST', $NO_REQ)
                ->update([
                    'KD_EMKL' => $ID_EMKL,
                    'KETERANGAN' => $KETERANGAN,
                    'ID_USER' => $ID_USER,
                    'ID_YARD' => $ID_YARD,
                    'KD_AGEN' => $KD_AGEN
                ]);

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

    function updateDataDelivery($request)
    {

        DB::beginTransaction();;
        try {
            $no_req = $request->NO_REQ;
            $total = $request->TOTAL;
            $index = $request->INDEX;
            $no_cont = $request->NO_CONT;
            $tgl_delivery = $request->TGL_DELIVERY;

             $q_save = "UPDATE CONTAINER_DELIVERY SET TGL_DELIVERY = TO_DATE('$tgl_delivery','yyyy-mm-dd') WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont'";
             DB::connection('uster')->statement($q_save);
            // //  echo $q_save;die();
            // DB::connection('uster')->table('CONTAINER_DELIVERY')
            //     ->where('NO_REQUEST', $no_req)
            //     ->where('NO_CONTAINER', $no_cont)
            //     ->update(['TGL_DELIVERY' => "TO_DATE('$tgl_delivery','yyyy-mm-dd')"]);

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

    function pbm($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT pbm.KD_PBM,pbm.NM_PBM,pbm.ALMT_PBM,pbm.NO_NPWP_PBM FROM v_mst_pbm pbm
                        where pbm.KD_CABANG='05' AND UPPER(pbm.NM_PBM) LIKE '%$nama%' AND PELANGGAN_AKTIF = '1'";
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }


    function commodity($term)
    {
        $nama            = strtoupper($term);
        $query             = "SELECT KD_COMMODITY, NM_COMMODITY from BILLING_NBS.MASTER_COMMODITY WHERE UPPER(NM_COMMODITY) LIKE '%$nama%'";
        $result_query    = DB::connection('uster')->select($query);
        return $result_query;
    }

    function masterPelabuhanPalapa($nama)
    {
        $nama = strtoupper($nama);
        $query             = "SELECT
                                TML_CD,
                                CDG_PORT_CODE,
                                CDG_PORT_NAME
                            FROM
                                CDG_PORT_PALAPA cpp
                            WHERE
                                TML_CD = 'PNK'
                                AND cpp.CDG_PORT_NAME LIKE '%$nama%'";
        $result_query    = DB::connection('opus_repo')->select($query);
        return $result_query;
    }

    function masterVesselPalapa($nama_kapal)
    {
        $nama_kapal = strtoupper($nama_kapal);
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
                                AND (VESSEL LIKE '%$nama_kapal%'
                                OR VOYAGE_IN LIKE '%$nama_kapal%'
                                OR VOYAGE_OUT LIKE '%$nama_kapal%'
                                OR VOYAGE LIKE '%$nama_kapal%')
                            ORDER BY VESSEL, VOYAGE_IN DESC";
        $result_query    = DB::connection('opus_repo')->select($query);
        return $result_query;
    }

    function getTglStack2($request)
    {
        $no_cont = $request->no_cont;
        $jn_repo = $request->JN_REPO;

        if ($jn_repo != 'EMPTY') {
            $query_cek1        = "SELECT tes.NO_REQUEST,
                                    CASE SUBSTR(KEGIATAN,9)
                                        WHEN 'RECEIVING' THEN (SELECT CONCAT('RECEIVING_',a.RECEIVING_DARI) FROM request_receiving a WHERE a.NO_REQUEST = tes.NO_REQUEST)
										WHEN 'NGAN STUFFING' THEN
										SUBSTR(KEGIATAN,14)
										WHEN 'NGAN STRIPPING' THEN
										SUBSTR(KEGIATAN,14)
										WHEN 'I STRIPPING' THEN
                                        SUBSTR(KEGIATAN,11)
										WHEN 'I STUFFING' THEN
                                        SUBSTR(KEGIATAN,11)
                                        WHEN 'IVERY' THEN
							            SUBSTR (KEGIATAN, 6)
                                        ELSE SUBSTR(KEGIATAN,9)
                                    END KEGIATAN FROM (SELECT TGL_UPDATE, NO_REQUEST,KEGIATAN FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI','PERPANJANGAN STUFFING','PERPANJANGAN STRIPPING','REALISASI STRIPPING', 'REALISASI STUFFING', 'REQUEST DELIVERY','PERP DELIVERY') AND AKTIF IS NULL) tes
                                    WHERE tes.TGL_UPDATE=(SELECT MAX(TGL_UPDATE) FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI','PERPANJANGAN STUFFING','PERPANJANGAN STRIPPING','REALISASI STRIPPING', 'REALISASI STUFFING', 'REQUEST DELIVERY','PERP DELIVERY') AND AKTIF IS NULL)
									ORDER BY KEGIATAN DESC";
            // $result_cek1        = $db->query($query_cek1);
            $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
            $no_request        = $row_cek1->no_request;
            $kegiatan        = $row_cek1->kegiatan;

            if ($kegiatan == 'RECEIVING_LUAR') {
                $query_cek1        = " SELECT SUBSTR(TO_CHAR(b.TGL_IN,'dd/mm/rrrr'),1,10) START_STACK FROM GATE_IN b WHERE b.NO_CONTAINER = '$no_cont' AND b.NO_REQUEST = '$no_request'";
                // $result_cek1    = $db->query($query_cek1);
                $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                $tgl_stack    = $row_cek1->start_stack;
                $asal_cont         = 'DEPO';
            } else if ($kegiatan == 'RECEIVING_TPK') {
                $query_cek1        = "SELECT TO_CHAR(TGL_BONGKAR,'dd/mm/rrrr') START_STACK FROM container_receiving WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                // $result_cek1    = $db->query($query_cek1);
                $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                $tgl_stack    = $row_cek1->start_stack;
                $asal_cont         = 'TPK';
            } else if ($kegiatan == 'STUFFING') {
                $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'dd/mm/rrrr'),1,10) START_STACK FROM container_stuffing WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                // $result_cek1    = $db->query($query_cek1);
                $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                $tgl_stack    = $row_cek1->start_stack;
                $asal_cont         = 'DEPO';
            } else if ($kegiatan == 'STRIPPING') {
                $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'dd/mm/rrrr'),1,10) START_STACK FROM container_stripping WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                // $result_cek1    = $db->query($query_cek1);
                $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                $tgl_stack    = $row_cek1->start_stack;
                $asal_cont         = 'DEPO';
            } else if ($kegiatan == 'DELIVERY') {
                $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_DELIVERY,'dd/mm/rrrr'),1,10) START_STACK FROM container_delivery WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
                // $result_cek1    = $db->query($query_cek1);
                $row_cek1        = DB::connection('uster')->selectOne($query_cek1);
                $tgl_stack    = $row_cek1->start_stack;
                $asal_cont         = 'DEPO';
                //echo $no_request;  exit();
            }
        } else {
            $query_tgl_stack_depo = "SELECT TGL_UPDATE , NO_REQUEST, KEGIATAN
                                            FROM HISTORY_CONTAINER
                                            WHERE no_container = '$no_cont'
                                            AND kegiatan IN ('GATE IN','REALISASI STRIPPING','PERPANJANGAN STUFFING','REQUEST STUFFING')
                                            ORDER BY TGL_UPDATE DESC";

            $row_tgl_stack_depo        = DB::connection('uster')->selectOne($query_tgl_stack_depo);
            $ex_keg    = $row_tgl_stack_depo->kegiatan;
            $no_re_st    = $row_tgl_stack_depo->no_request;
            if ($ex_keg == "REALISASI STRIPPING") {
                $qtgl_r = "SELECT TGL_REALISASI FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'";
                $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
                $tgl_stack = $rtgl_r->tgl_realisasi;
            } else if ($ex_keg == "GATE IN") {
                $qtgl_r = "SELECT TGL_IN FROM GATE_IN WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_re_st'";
                $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
                $tgl_stack = $rtgl_r->tgl_in;
            } else if ($ex_keg == "PERPANJANGAN STUFFING") {
                $qtgl_r = "SELECT END_STACK_PNKN FROM CONTAINER_STUFFING WHERE NO_REQUEST = '$no_re_st' AND NO_CONTAINER = '$no_cont'";
                $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
                $tgl_stack = $rtgl_r->end_stack_pnkn;
                $asal_cont         = 'DEPO';
            } else if ($ex_keg == "REQUEST STUFFING") {
                $qtgl_r = "SELECT START_PERP_PNKN FROM CONTAINER_STUFFING WHERE NO_REQUEST = '$no_re_st' AND NO_CONTAINER = '$no_cont'";
                $rtgl_r = DB::connection('uster')->selectOne($qtgl_r);
                $tgl_stack = $rtgl_r->start_perp_pnkn;
                $asal_cont         = 'DEPO';
            }
        }

        $hasil = "SELECT TO_CHAR(TO_DATE('$tgl_stack','yyyy-mm-dd hh24:mi:ss'),'dd-mm-rrrr') TGL_BONGKAR, TO_CHAR(TO_DATE('$tgl_stack','yyyy-mm-dd hh24:mi:ss')+4,'dd-mm-rrrr') EMPTY_SD FROM DUAL";
        $rhasil = DB::connection('uster')->selectOne($hasil);
        return $rhasil;
    }
}
