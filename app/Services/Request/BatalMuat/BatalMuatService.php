<?php

namespace App\Services\Request\BatalMuat;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class BatalMuatService
{
   function getData($request)
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
            $query_list = "SELECT NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, REQUEST_DELIVERY.NO_REQUEST,  TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd Mon yyyy') TGL_REQUEST,  TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,'dd Mon yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM as NAMA_EMKL ,request_delivery.VESSEL as NAMA_VESSEL, request_delivery.VOYAGE, yard_area.NAMA_YARD, request_delivery.NO_REQ_ICT
                                FROM REQUEST_DELIVERY, NOTA_DELIVERY, v_mst_pbm emkl, yard_area
                                WHERE  REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                                AND REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID 
                                AND NOTA_DELIVERY.NO_REQUEST(+) = REQUEST_DELIVERY.NO_REQUEST
                                AND REQUEST_DELIVERY.NO_REQUEST LIKE '%$no_req%'
                                AND request_delivery.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING')     
                                AND request_delivery.DELIVERY_KE = 'TPK'
                                ORDER BY REQUEST_DELIVERY.NO_REQUEST DESC";
         } else if ((isset($from)) && (isset($to)) && ($no_req == NULL)) {
            $query_list = "SELECT NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, REQUEST_DELIVERY.NO_REQUEST,  TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd Mon yyyy') TGL_REQUEST,  TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM as NAMA_EMKL ,request_delivery.VESSEL as NAMA_VESSEL, request_delivery.VOYAGE, yard_area.NAMA_YARD, request_delivery.NO_REQ_ICT
            FROM REQUEST_DELIVERY, NOTA_DELIVERY, v_mst_pbm emkl, yard_area
            WHERE  REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
            AND REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
            AND NOTA_DELIVERY.NO_REQUEST(+) = REQUEST_DELIVERY.NO_REQUEST
            AND request_delivery.TGL_REQUEST BETWEEN TO_DATE('$from','DD/MM/YYYY') AND TO_DATE('$to','DD/MM/YYYY')
           AND request_delivery.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING') 
           AND request_delivery.DELIVERY_KE = 'TPK'
            ORDER BY REQUEST_DELIVERY.NO_REQUEST DESC";
         } else if ((isset($from)) && (isset($to)) && (isset($no_req))) {

            $query_list = "SELECT NVL(NOTA_DELIVERY.LUNAS, 0) LUNAS, REQUEST_DELIVERY.NO_REQUEST,  TO_CHAR( REQUEST_DELIVERY.TGL_REQUEST,'dd Mon yyyy') TGL_REQUEST,  TO_CHAR(REQUEST_DELIVERY.TGL_REQUEST_DELIVERY,'dd/mm/yyyy') TGL_REQUEST_DELIVERY, emkl.NM_PBM as NAMA_EMKL ,request_delivery.VESSEL as NAMA_VESSEL, request_delivery.VOYAGE, yard_area.NAMA_YARD, request_delivery.NO_REQ_ICT
                                FROM REQUEST_DELIVERY, NOTA_DELIVERY, v_mst_pbm emkl, yard_area
                                WHERE  REQUEST_DELIVERY.KD_EMKL = emkl.KD_PBM
                                AND REQUEST_DELIVERY.ID_YARD = YARD_AREA.ID
                                AND NOTA_DELIVERY.NO_REQUEST(+) = REQUEST_DELIVERY.NO_REQUEST
                                AND request_delivery.NO_REQUEST = '$no_req'
                                AND request_delivery.TGL_REQUEST BETWEEN TO_DATE('$from','DD/MM/YYYY') AND TO_DATE('$to','DD/MM/YYYY')
                               AND request_delivery.PERALIHAN NOT IN ('RELOKASI','STUFFING','STRIPPING') 
                               AND request_delivery.DELIVERY_KE = 'TPK'
                                ORDER BY REQUEST_DELIVERY.NO_REQUEST DESC";
         }
      } else {
         $query_list = "SELECT rbm.no_request,
                  no_req_baru,
                  tgl_request,
                  kd_emkl,
                  v_mst_pbm.nm_pbm,
                  CASE jenis_bm WHEN 'alih_kapal' THEN 'ALIH KAPAL' ELSE 'DELIVERY' END
                     AS jenis_bm,
                  CASE status_gate
                     WHEN '1' THEN 'AFTER STUFFING'
                     WHEN '2' THEN 'EX REPO'
                     ELSE 'BEFORE STUFFING'
                  END
                     AS status_gate,
                  CASE nota
                     WHEN 'Y' THEN 'NOTA SUDAH DICETAK'
                     ELSE 'NOTA BELUM CETAK'
                  END
                     AS nota,
                  COUNT (cbm.no_container) box
            FROM request_batal_muat rbm
                  INNER JOIN v_mst_pbm v_mst_pbm
                     ON rbm.kd_emkl = v_mst_pbm.kd_pbm
                  INNER JOIN container_batal_muat cbm
                     ON rbm.no_request = cbm.no_request
         GROUP BY rbm.no_request,
                  tgl_request,
                  kd_emkl,
                  v_mst_pbm.nm_pbm,
                  jenis_bm,
                  nota,
                  status_gate,
                  no_req_baru
         ORDER BY tgl_request DESC";
      }
      return DB::connection('uster')->select($query_list);
   }

   function GetContainerByNoReq($no_cont)
   {
      $query_list = "select *
      from container_batal_muat cbm
      inner join master_container mc
      on cbm.no_container = mc.no_container where no_request = '$no_cont'";

      return DB::connection('uster')->select($query_list);
   }

   function getPMB($pmb)
   {
      $pmb = strtoupper($pmb);
      $query_list = "SELECT pbm.KD_PBM,pbm.NM_PBM,pbm.ALMT_PBM,pbm.NO_NPWP_PBM,pbm.NO_ACCOUNT_PBM FROM v_mst_pbm pbm
      where pbm.KD_CABANG='05' AND UPPER(pbm.NM_PBM) LIKE '%$pmb%' AND PELANGGAN_AKTIF = '1' AND pbm.ALMT_PBM IS NOT NULL";

      return DB::connection('uster')->select($query_list);
   }

   function GetDataByNoReq($no_req)
   {
      $query_request   = "select rbm.no_request, em.nm_pbm, rbm.biaya, rbm.jenis_bm, rbm.status_gate, rbm.kapal_tuju, vb.nm_kapal, rbm.kd_emkl
						from request_batal_muat rbm inner join v_mst_pbm em on rbm.kd_emkl = em.kd_pbm and em.kd_cabang = '05'
						inner join v_pkk_cont vb on  REPLACE(rbm.kapal_tuju,'VESSEL_NOTHING','BSK100000023') = vb.no_booking
						where rbm.no_request = '$no_req'";
      $row_request   = DB::connection('uster')->selectOne($query_request);

      $jenis_bm = '<select class="form-control" name="jenis_batal" id="jenis_batal">';
      $jenis_bm .= '<option value=""> PILIH</option>';
      if ($row_request->jenis_bm == 'alih_kapal') {
         $jenis_bm .= '<option selected value="alih_kapal"> ALIH KAPAL </option>';
         $jenis_bm .= '<option value="delivery"> DELIVERY </option>';
      } else if ($row_request->jenis_bm == 'delivery') {
         $jenis_bm .= '<option value="alih_kapal"> ALIH KAPAL </option>';
         $jenis_bm .= '<option selected value="delivery"> DELIVERY </option>';
      }
      $jenis_bm .= '</select>';

      $res_kapal = "select * from v_pkk_cont where no_booking =REPLACE('$row_request->kapal_tuju','VESSEL_NOTHING','BSK100000023')";
      $rwk = DB::connection('uster')->selectOne($res_kapal);

      $biaya = '<select class="form-control" name="biaya">';
      if ($row_request->biaya == 'Y') {
         $biaya .= '<option value="Y" selected>YA</option>
                     <option value="T">TIDAK</option>';
      } else if ($row_request->biaya == 'T') {
         $biaya .= '<option value="Y">YA</option>
                     <option value="T" selected>TIDAK</option>';
      } else {
         $biaya .= '<option value="" selected>Pilih</option>
                     <option value="Y">YA</option>
                     <option value="T">TIDAK</option>';
      }
      $biaya .= '</select>';


      $status_gate = '<select class="form-control" name="status_gate" id="status_gate" >
							<option value=""> PILIH</option>';
      if ($row_request->status_gate == '1') {
         $status_gate .= '<option selected value="1"> AFTER STUFFING </option>
						<option value="2"> EX REPO </option>
						<option value="3"> BEFORE STUFFING </option>';
      } else if ($row_request->status_gate == '2') {
         $status_gate .= '<option value="1"> AFTER STUFFING </option>
						<option  selected value="2"> EX REPO </option>
						<option value="3"> BEFORE REPO </option>';
      } else if ($row_request->status_gate == '3') {
         $status_gate .= '<option value="1"> AFTER STUFFING </option>
						<option value="2"> EX REPO </option>
						<option  selected value="3"> BEFORE REPO </option>';
      }
      $status_gate .= '</select>';


      return array(
         'status_gate' => $status_gate,
         'biaya' => $biaya,
         'jenis_bm' => $jenis_bm,
         'rwk' => $rwk,
         'row_request' => $row_request
      );
   }

   function getContainer($jenis, $no_cont)
   {
      if ($jenis == 1) {
         $query          = "SELECT *
                                    FROM (SELECT *
                                            FROM (  SELECT mc.no_container,
                                                           cs.no_request,
                                                           mc.type_,
                                                           mc.size_,
                                                           hc.status_cont,
                                                           mc.no_booking,
                                                           vb.nm_kapal,
                                                           cs.hz,
                                                           cs.tgl_realisasi,
                                                           cs.aktif,
                                                           hc.kegiatan
                                                      FROM master_container mc
                                                           INNER JOIN container_stuffing cs
                                                              ON mc.no_container = cs.no_container
                                                           INNER JOIN history_container hc
                                                              ON cs.no_container = hc.no_container
                                                           INNER JOIN v_pkk_cont vb
                                                              ON mc.no_booking = vb.no_booking
                                                     WHERE     cs.tgl_realisasi IS NOT NULL
                                                           AND cs.aktif = 'T'
                                                           AND mc.no_container LIKE '$no_cont%'
                                                  ORDER BY hc.tgl_update DESC) q
                                           WHERE ROWNUM < 2) j
                                   WHERE j.kegiatan IN ('REALISASI STUFFING', 'REQUEST BATALMUAT')";
      } else if ($jenis == 2) { //ex repo
         $query         = "SELECT mc.no_container,
                                    rd.no_req_ict,
                                    RD.TGL_REQUEST,
                                    cd.no_request,
                                    mc.type_,
                                    mc.size_,
                                    hc.status_cont,
                                    mc.no_booking,
                                    vb.nm_kapal,
                                    hc.kegiatan,
                                    cd.hz,
                                    cd.komoditi,
                                    cd.start_stack,
                                    rd.voyage voyage_in,
                                    rd.o_idvsb ukk_lama,
                                    trunc(vb.tgl_jam_berangkat) etd_old
                               FROM master_container mc
                                    INNER JOIN container_delivery cd
                                       ON mc.no_container = cd.no_container
                                    INNER JOIN history_container hc
                                       ON cd.no_request = hc.no_request
                                          AND cd.no_container = hc.no_container
                                    INNER JOIN request_delivery rd
                                       ON cd.no_request = rd.no_request
                                    INNER JOIN v_pkk_cont vb
                                       ON mc.no_booking = vb.no_booking
                              WHERE     cd.aktif = 'Y'
                                    AND mc.location = 'IN_YARD'
                                    AND cd.no_container LIKE '$no_cont%'
                                    AND rd.delivery_ke = 'TPK'
                                    AND hc.kegiatan IN ('REQUEST DELIVERY', 'PERP DELIVERY')";
      } else if ($jenis == 3) { //before stuffing
         $query          = "SELECT mc.no_container,
                                         cs.no_request,
                                         mc.type_,
                                         mc.size_,
                                         hc.status_cont,
                                         mc.no_booking,
                                         vb.nm_kapal,
                                         cs.end_stack_pnkn,
                                         cs.commodity,
                                         cs.hz
                                    FROM master_container mc
                                         INNER JOIN container_stuffing cs
                                            ON mc.no_container = cs.no_container
                                         INNER JOIN history_container hc
                                            ON cs.no_request = hc.no_request
                                               AND cs.no_container = hc.no_container
                                         INNER JOIN v_pkk_cont vb
                                            ON mc.no_booking = vb.no_booking
                                   WHERE     mc.no_container LIKE '$no_cont%'
                                         AND cs.tgl_realisasi IS NULL
                                         AND cs.aktif = 'Y'";
      }

      return DB::connection('uster')->select($query);
   }

   function getContainerHistory($no_cont)
   {
      $query = "SELECT tes.NO_REQUEST, 
      CASE SUBSTR(KEGIATAN,9)
          WHEN 'RECEIVING' THEN (SELECT CONCAT('RECEIVING_',a.RECEIVING_DARI) FROM request_receiving a WHERE a.NO_REQUEST = tes.NO_REQUEST)            
          WHEN 'NGAN STUFFING' THEN
          SUBSTR(KEGIATAN,14)
          ELSE SUBSTR(KEGIATAN,9)
      END KEGIATAN FROM (SELECT TGL_UPDATE, NO_REQUEST,KEGIATAN FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST STRIPPING', 'REQUEST DELIVERY','REQUEST STUFFING','REQUEST RELOKASI','PERPANJANGAN STUFFING')) tes
      WHERE tes.TGL_UPDATE=(SELECT MAX(TGL_UPDATE) FROM history_container WHERE no_container = '$no_cont' and kegiatan IN ('REQUEST STRIPPING', 'REQUEST DELIVERY','REQUEST STUFFING','REQUEST RELOKASI,','PERPANJANGAN STUFFING'))";
      $res = DB::connection('uster')->selectOne($query);
      $no_request = $res->no_request;
      $kegiatan = $res->kegiatan;
      if ($kegiatan == 'RECEIVING_LUAR') {
         $query_cek1      = "SELECT SUBSTR(TO_CHAR(b.TGL_IN,'dd/mm/rrrr'),1,10) START_STACK FROM GATE_IN b WHERE b.NO_CONTAINER = '$no_cont' AND b.NO_REQUEST = '$no_request'";
         $row_cek1 = DB::connection('uster')->selectOne($query_cek1);
         $start_stack   = $row_cek1->start_stack;
      } else if ($kegiatan == 'RECEIVING_TPK') {
         $query_cek1      = "SELECT TO_CHAR(TGL_BONGKAR,'dd/mm/rrrr) START_STACK FROM container_receiving WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
         $row_cek1 = DB::connection('uster')->selectOne($query_cek1);
         $start_stack   = $row_cek1->start_stack;
      } else if ($kegiatan == 'STUFFING') {
         $query_cek1      = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'dd/mm/rrrr'),1,10) START_STACK FROM container_stuffing WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
         $row_cek1 = DB::connection('uster')->selectOne($query_cek1);
         $start_stack   = $row_cek1->start_stack;
      } else if ($kegiatan == 'STRIPPING') {
         $query_cek1      = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'dd/mm/rrrr'),1,10) START_STACK FROM container_stripping WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
         $row_cek1 = DB::connection('uster')->selectOne($query_cek1);
         $start_stack   = $row_cek1->start_stack;
      } else if ($kegiatan == 'DELIVERY') {
         $query_cek1      = "SELECT SUBSTR (TO_CHAR (TGL_DELIVERY, 'dd/mm/rrrr'), 1, 10) START_STACK FROM container_delivery WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$no_request'";
         $row_cek1 = DB::connection('uster')->selectOne($query_cek1);
         $start_stack   = $row_cek1->start_stack;
      }

      return $start_stack;
   }

   function masterVesselPalapa($term)
   {
      $nama_kapal      = strtoupper($term);

      $query          = "SELECT
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
                        VOYAGE,
                        START_WORK
                    FROM
                        M_VSB_VOYAGE_PALAPA
                    WHERE
                        TML_CD = 'PNK'
                        AND (VESSEL LIKE '%$nama_kapal%'
                        OR VOYAGE_IN LIKE '%$nama_kapal%'
                        OR VOYAGE_OUT LIKE '%$nama_kapal%'
                        OR VOYAGE LIKE '%$nama_kapal%')
                    ORDER BY VESSEL, VOYAGE_IN DESC";
      return  DB::connection('opus_repo')->select($query);
   }

   function masterPelabuhanPalapa($term)
   {
      $nama_kapal      = strtoupper($term);

      $query          = "SELECT
            TML_CD,
            CDG_PORT_CODE,
            CDG_PORT_NAME
      FROM
            CDG_PORT_PALAPA cpp
      WHERE 
            TML_CD = 'PNK'
            AND cpp.CDG_PORT_NAME LIKE '%$nama_kapal%'";

      return  DB::connection('opus_repo')->select($query);
   }

   function validateContainer($request)
   {
      $no_req = $request->input('no_req');
      $no_cont = $request->input('no_cont');
      $jenis_bm = $request->input('jenis_bm');
      $newvessel = $request->input('newvessel');


      $cek =  1;
      $qvalidves = "select count(1) cek from request_stuffing a, container_stuffing b
                  where a.no_request = b.no_request and b.no_container = '$no_cont' and a.no_booking = '$newvessel' and a.no_request = '$no_req'";
      $rvalidves = DB::connection('uster')->selectOne($qvalidves);
      if ($rvalidves->cek > 0) {
         return 'X';
      } else {
         if ($cek == 0) {
            return 'T';
         } else {
            return 'Y';
         }
      }
   }

   function debug($var)
   {
      echo "<pre>";
      print_r($var);
      echo "</pre>";
   }

   function save_bm_praya($request)
   {
      try {
         DB::connection('uster')->beginTransaction();
         $jenis_bm = $request->jenis_batal;
         $status_gate = $request->status_gate;
         $biaya = $request->biaya;
         $kd_pbm = $request->KD_PELANGGAN;
         $kd_kapal = $request->KD_KAPAL;
         $nm_agen = $request->NM_AGEN;
         $account_pbm = $request->NO_ACCOUNT_PBM;
         $kd_pelabuhan_asal = $request->KD_PELABUHAN_ASAL;
         $kd_pelabuhan_tujuan = $request->KD_PELABUHAN_TUJUAN;
         $no_booking_new = $request->NO_BOOKING ?? "VESSEL_NOTHING";
         $no_req_ict = $request->NO_REQ_ICT;
         $id_user = session('LOGGED_STORAGE');
         $id_yard = session('IDYARD_STORAGE');
         $etd = $request->ETD;
         $openstack = $request->TGL_STACKING;
         $no_ukk_new = $request->NO_UKK;
         $TGL_MUAT = $request->TGL_MUAT;
         $TGL_STACKING = $request->TGL_STACKING;
         $nm_kapal = $request->NM_KAPAL;
         $voyage_in = $request->VOYAGE_IN;
         $type = $request->KDTYPE;
         $oi = $request->OI;
         $NPE = $request->NPE;
         $PEB = $request->PEB;
         $voyage_out = $request->VOYAGE_OUT;
         $eta = $request->ETA;
         $kd_agen = $request->KD_AGEN;
         $voyage = $request->VOYAGE;
         $call_sign = $request->CALL_SIGN;

         $query_cek = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_REQUEST,8,13)))+1,6,0),'000001') AS JUM,
                             TO_CHAR(SYSDATE, 'MM') AS MONTH, 
                             TO_CHAR(SYSDATE, 'YY') AS YEAR 
                      FROM REQUEST_BATAL_MUAT
                      WHERE TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)";
         $jum_ = DB::connection('uster')->selectOne($query_cek);
         $jum = $jum_->jum;
         $month = $jum_->month;
         $year = $jum_->year;
         $no_req_bm = "BMU" . $month . $year . $jum;

         if ($status_gate == 1) { // Batal muat after stuffing
            $q_batal = "INSERT INTO request_batal_muat(no_request, tgl_request, kd_emkl, biaya, jenis_bm, kapal_tuju, status_gate)
                        VALUES ('$no_req_bm', SYSDATE, '$kd_pbm', '$biaya', '$jenis_bm', '$no_booking_new', '$status_gate')";
            if (DB::connection('uster')->insert($q_batal)) {
               foreach ($request->BM_CONT as $i => $no_cont) {
                  $start_pnkn = $request->AWAL[$i];
                  $end_pnkn = $request->AKHIR[$i];
                  $exno_req = $request->BMNO_REQ[$i];
                  $status = $request->KDSTATUS[$i];

                  $date = Carbon::createFromFormat('Y-m-d', $start_pnkn);
                  $start_pnkn =  $date->format('d-m-Y');

                  $q_getcounter = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont' ORDER BY COUNTER DESC";
                  $rw_getcounter = DB::connection('uster')->selectOne($q_getcounter);
                  $cur_counter = $rw_getcounter->counter;

                  $q_ex_req = "SELECT no_request, no_booking, no_npe, no_peb FROM request_stuffing WHERE no_request = '$exno_req'";
                  $r_ex = DB::connection('uster')->selectOne($q_ex_req);
                  $no_booking_old = $r_ex->no_booking;

                  $q_disable_cont = "UPDATE container_stuffing SET aktif = 'T', f_batal_muat = 'Y' WHERE no_container = '$no_cont' AND no_request = '$exno_req'";
                  $query_update_plan = "UPDATE PLAN_CONTAINER_STUFFING SET AKTIF = 'T' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = REPLACE('$exno_req','S','P')";
                  $q_history_c = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT)  
                                    VALUES ('$no_cont','$no_req_bm','REQUEST BATALMUAT',SYSDATE,'$id_user','$id_yard','$no_booking_new', '$cur_counter','$status')";
                  $q_cont_batal = "INSERT INTO container_batal_muat(no_container, no_request, status, start_pnkn, end_pnkn, no_req_batal, ex_kapal) 
                                     VALUES ('$no_cont','$no_req_bm','$status',TO_DATE('$start_pnkn','dd/mm/rrrr'),TO_DATE('" . date('d-m-Y', strtotime($end_pnkn)) . "','dd-mm-rrrr'), '$exno_req', '$no_booking_old')";

                
                  DB::connection('uster')->update($q_disable_cont);
                  DB::connection('uster')->update($query_update_plan);
                  DB::connection('uster')->insert($q_history_c);
                  DB::connection('uster')->insert($q_cont_batal);
               }
            }
         } else if ($status_gate == 2) { // Batal muat ex repo
            if ($no_booking_new == "VESSEL_NOTHING") {
               $ex_noreq = $request->NO_REQUEST;

               $q_get_nobooking = "SELECT NO_BOOKING FROM REQUEST_DELIVERY rd WHERE NO_REQUEST = '$ex_noreq'";
               $rw_get_nobooking = DB::connection('uster')->selectOne($q_get_nobooking);
               $no_booking_new = $rw_get_nobooking->no_booking;
            }

            $q_batal = "INSERT INTO request_batal_muat(no_request, tgl_request, kd_emkl, biaya, jenis_bm, kapal_tuju, status_gate, o_idvsb, o_vessel, o_voyin, o_voyout, di)
                        VALUES ('$no_req_bm', SYSDATE, '$kd_pbm', '$biaya', '$jenis_bm', '$no_booking_new', '$status_gate', '$no_ukk_new', '$nm_kapal', '$voyage_in', '$voyage_in', '$oi')";
            if (DB::connection('uster')->insert($q_batal)) {
               foreach ($request->BM_CONT as $i => $no_cont) {
                  $start_pnkn = $request->AWAL[$i];
                  $end_pnkn = $request->AKHIR[$i];
                  $oldvsb = $request->UKKLAMA[$i];
                  $exno_req = $request->BMNO_REQ[$i];
                  $status = $request->KDSTATUS[$i];

                  $q_getcounter = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont' ORDER BY COUNTER DESC";
                  $rw_getcounter = DB::connection('uster')->selectOne($q_getcounter);
                  $cur_counter = $rw_getcounter->counter;

                  $q_ex_req = "SELECT no_request, kd_pelabuhan_asal, kd_pelabuhan_tujuan, no_booking, no_ro, npe, peb, tgl_berangkat, no_req_ict FROM request_delivery WHERE no_request = '$exno_req'";
                  $r_ex = DB::connection('uster')->selectOne($q_ex_req);
                  $no_booking_old = $r_ex->no_booking;
                  $no_req_ict = $r_ex->no_req_ict;

                  $q_history_c = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT)  
                                    VALUES ('$no_cont','$no_req_bm','REQUEST BATALMUAT',SYSDATE,'$id_user','$id_yard','$no_booking_new', '$cur_counter','$status')";
                  $q_cont_batal = "INSERT INTO container_batal_muat(no_container, no_request, status, start_pnkn, end_pnkn, no_req_batal, ex_kapal, ex_vsb) 
                                     VALUES ('$no_cont','$no_req_bm','$status',TO_DATE('$start_pnkn','dd-mm-rrrr'),TO_DATE('$end_pnkn','DD-MM-YYYY HH24:MI:SS'), '$exno_req', '$no_booking_old','$oldvsb')";
                  $update_batal_delivery = "UPDATE CONTAINER_DELIVERY SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$exno_req' AND NO_CONTAINER='$no_cont'";

                  DB::connection('uster')->insert($q_history_c);
                  DB::connection('uster')->insert($q_cont_batal);
                  DB::connection('uster')->update($update_batal_delivery);
               }
            }
         } else if ($status_gate == 3) { // Batal muat before stuffing
            $q_batal = "INSERT INTO request_batal_muat(no_request, tgl_request, kd_emkl, biaya, jenis_bm, kapal_tuju, status_gate)
                        VALUES ('$no_req_bm', SYSDATE, '$kd_pbm', '$biaya', '$jenis_bm', '$no_booking_new', '$status_gate')";
            if (DB::connection('uster')->insert($q_batal)) {
               foreach ($request->BM_CONT as $i => $no_cont) {
                  $start_pnkn = $request->AWAL[$i];
                  $end_pnkn = $request->AKHIR[$i];
                  $exno_req = $request->BMNO_REQ[$i];
                  $status = $request->KDSTATUS[$i];

                  $q_getcounter = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont' ORDER BY COUNTER DESC";
                  $rw_getcounter = DB::connection('uster')->selectOne($q_getcounter);
                  $cur_counter = $rw_getcounter->counter;

                  $q_ex_req = "SELECT no_request, no_booking, no_npe, no_peb FROM request_stuffing WHERE no_request = '$exno_req'";
                  $r_ex = DB::connection('uster')->selectOne($q_ex_req);
                  $no_booking_old = $r_ex->no_booking;

                  $q_disable_cont = "UPDATE container_stuffing SET aktif = 'T', f_batal_muat = 'Y' WHERE no_container = '$no_cont' AND no_request = '$exno_req'";
                  if ($status_gate == 3) {
                     $query_update_plan = "UPDATE PLAN_CONTAINER_STUFFING SET AKTIF = 'T' WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = REPLACE('$exno_req','S','P')";
                     DB::connection('uster')->update($query_update_plan);
                  }
                  $q_history_c = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT)  
                                    VALUES ('$no_cont','$no_req_bm','REQUEST BATALMUAT',SYSDATE,'$id_user','$id_yard','$no_booking_new', '$cur_counter','$status')";
                  $q_cont_batal = "INSERT INTO container_batal_muat(no_container, no_request, status, start_pnkn, end_pnkn, no_req_batal, ex_kapal) 
                                     VALUES ('$no_cont','$no_req_bm','$status',TO_DATE('$start_pnkn','dd-mm-rrrr'),TO_DATE('$end_pnkn','DD-MM-YYYY HH24:MI:SS'), '$exno_req', '$no_booking_old')";

                  DB::connection('uster')->insert($q_history_c);
                  DB::connection('uster')->insert($q_cont_batal);
                  DB::connection('uster')->update($q_disable_cont);
               }
            }
         }

         if ($jenis_bm == 'alih_kapal') { // Batal muat alih kapal
            $r_pkk = DB::connection('uster')->selectOne("SELECT NO_UKK FROM V_PKK_CONT WHERE NO_BOOKING='$no_booking_new'");
            $no_ukk = $r_pkk->no_ukk ?? NULL;
            if ($no_ukk == NULL) {
               $q_insert = "INSERT INTO V_PKK_CONT (KD_KAPAL, NM_KAPAL, VOYAGE_IN, VOYAGE_OUT, TGL_JAM_TIBA, TGL_JAM_BERANGKAT, NO_UKK, NM_AGEN, KD_AGEN, PELABUHAN_ASAL, PELABUHAN_TUJUAN, KD_CABANG, NO_BOOKING, VOYAGE, CALL_SIGN)
                             VALUES ('$kd_kapal', '$nm_kapal', '$voyage_in', '$voyage_out', TO_DATE('$eta','DD-MM-YYYY HH24:MI:SS'), TO_DATE('$etd','DD-MM-YYYY HH24:MI:SS'), '$no_ukk_new', '$nm_agen', '$kd_agen', '$kd_pelabuhan_asal', '$kd_pelabuhan_tujuan', '05', '$no_booking_new', '$voyage', '$call_sign')";
               DB::connection('uster')->insert($q_insert);
            }

            foreach ($request->BM_CONT as $no_cont) {
               $q_update_m = "UPDATE master_container SET no_booking = '$no_booking_new' WHERE no_container = '$no_cont'";
               DB::connection('uster')->update($q_update_m);
            }

            if ($status_gate == 2) {
               $paramif = [
                  "in_reqbm" => $no_req_bm,
                  "in_accpbm" => $account_pbm,
                  "in_pbm" => $kd_pbm,
                  "in_vessel" => $nm_kapal,
                  "in_voyin" => $voyage_in,
                  "in_voyout" => $voyage_in,
                  "in_user" => $id_user,
                  "in_shipping" => $nm_agen,
                  "in_tglberangkat" => $etd,
                  "in_openstack" => $openstack,
                  "in_custnum" => '',
                  "in_npe" => $NPE,
                  "in_peb" => $PEB,
                  "in_booknum" => '',
                  "in_fpod" => '',
                  "in_idfpod" => '',
                  "in_di" => $oi,
                  "in_accpbm_pnkn" => '',
                  "in_idvsbnew" => $no_ukk_new,
                  "in_vessel_code" => $kd_kapal,
                  "out_noreq" => '',
                  "out_msg" => '',
               ];

               $queryif = "DECLARE BEGIN pack_create_bamu_exrepo.create_bamu_praya(:in_reqbm,:in_accpbm,:in_pbm,:in_vessel,:in_voyin,:in_voyout,:in_user,:in_shipping,:in_tglberangkat,:in_openstack,:in_custnum,:in_npe,:in_peb,:in_booknum,:in_fpod,:in_idfpod,:in_di,:in_accpbm_pnkn,:in_idvsbnew,:in_vessel_code,:out_noreq,:out_msg); END;";
               DB::connection('uster')->statement($queryif, $paramif);

               $out_noreq = $paramif["out_noreq"];
               $out_msg = $paramif["out_msg"];

               if ($biaya == 'T') {
                  $param_payment2 = [
                     "ID_NOTA" => 'BMtanpabiaya',
                     "ID_REQ" => $out_noreq,
                     "OUT" => '',
                     "OUT_MSG" => ''
                  ];
                  $query2 = "DECLARE BEGIN payment_opusbill(:ID_REQ,:ID_NOTA,:OUT,:OUT_MSG); END;";
                  DB::connection('opus')->statement($query2, $param_payment2);
               }
            } else if ($status_gate == 1 || $status_gate == 3) {
               if ($status_gate == 1) {
                  $aktif = 'T';
                  $status_cont_st = 'FCL';
               } else {
                  $aktif = 'Y';
                  $status_cont_st = 'MTY';
               }

               $q_perp_dari = "SELECT PERP_DARI, ID_PENUMPUKAN FROM request_stuffing WHERE no_request = '$exno_req'";
               $row_perp_dari = DB::connection('uster')->selectOne($q_perp_dari);
               $perp_dari = $row_perp_dari->perp_dari;
               $kd_id_tumpuk = $row_perp_dari->id_penumpukan;

               $query_select = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_REQUEST,8,13)))+1,6,0),'000001') AS JUM, 
                                      TO_CHAR(SYSDATE, 'MM') AS MONTH, 
                                      TO_CHAR(SYSDATE, 'YY') AS YEAR 
                                 FROM REQUEST_STUFFING
                                 WHERE TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)
                                   AND SUBSTR(request_stuffing.NO_REQUEST,0,3) = 'ASF'";
               $row_select = DB::connection('uster')->selectOne($query_select);
               $no_req = $row_select->jum;
               $month = $row_select->month;
               $year = $row_select->year;
               $no_req_p = "ASF" . $month . $year . $no_req;

               $query_ir = "INSERT INTO REQUEST_STUFFING(NO_REQUEST, ID_YARD, CETAK_KARTU_SPPS, NO_BOOKING, TGL_REQUEST, ID_USER, KD_CONSIGNEE, KD_PENUMPUKAN_OLEH, NM_KAPAL, VOYAGE, STUFFING_DARI, NOTA, PERP_DARI, ID_PENUMPUKAN) 
                             VALUES('$no_req_p', '$id_yard', 0, '$no_booking_new', SYSDATE, '$id_user', '$kd_pbm', '$kd_pbm', '$nm_kapal', '$voyage_in', 'AUTO', 'Y', '$perp_dari', '$kd_id_tumpuk')";
               if (DB::connection('uster')->insert($query_ir)) {
                  foreach ($request->BM_CONT as $i => $no_cont) {
                     $query = "SELECT * FROM container_stuffing WHERE no_container = '$no_cont' AND no_request = '$exno_req'";
                     $rwstuf = DB::connection('uster')->selectOne($query);

                     $hz = $rwstuf->hz;
                     $comm = $rwstuf->commodity;
                     $type_st = $rwstuf->type_stuffing;
                     $start_stack = $rwstuf->start_stack;
                     $asal = $rwstuf->asal_cont;
                     $seal = $rwstuf->no_seal;
                     $berat = $rwstuf->berat;
                     $keterangan = $rwstuf->keterangan;
                     $tgl_app = $rwstuf->tgl_approve;
                     $tgl_gate = $rwstuf->tgl_gate;
                     $tgl_approve = $rwstuf->start_perp_pnkn;
                     $kd_comm = $rwstuf->kd_commodity;

                     $query_ic = "INSERT INTO CONTAINER_STUFFING (NO_CONTAINER, NO_REQUEST, AKTIF, HZ, COMMODITY, KD_COMMODITY, TYPE_STUFFING, START_STACK, ASAL_CONT, NO_SEAL, BERAT, KETERANGAN, STATUS_REQ, TGL_APPROVE, TGL_GATE, START_PERP_PNKN, END_STACK_PNKN, TGL_MULAI_FULL, TGL_SELESAI_FULL, TGL_REALISASI, ID_USER_REALISASI)
                                     SELECT NO_CONTAINER, '$no_req_p', '$aktif', HZ, COMMODITY, KD_COMMODITY, TYPE_STUFFING, START_STACK, ASAL_CONT, NO_SEAL, BERAT, KETERANGAN, 'PERP', TGL_APPROVE, TGL_GATE, START_PERP_PNKN, END_STACK_PNKN, TGL_MULAI_FULL, TGL_SELESAI_FULL, TGL_REALISASI, ID_USER_REALISASI
                                     FROM CONTAINER_STUFFING
                                     WHERE NO_CONTAINER = '$no_cont' AND NO_REQUEST = '$exno_req'";
                     DB::connection('uster')->insert($query_ic);

                     $q_getc = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont' ORDER BY COUNTER DESC";
                     $rw_getc = DB::connection('uster')->selectOne($q_getc);
                     $cur_c = $rw_getc->counter;
                     $cur_booking = $rw_getc->no_booking;

                     $history_st = "INSERT INTO HISTORY_CONTAINER(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT) 
                                       VALUES ('$no_cont','$no_req_p','REQUEST STUFFING', SYSDATE,'$id_user','$id_yard','$cur_booking','$cur_c','$status')";
                     DB::connection('uster')->insert($history_st);
                  }

                  $query_upd = "UPDATE request_batal_muat SET NO_REQ_BARU = '$no_req_p' WHERE NO_REQUEST = '$no_req_bm'";
                  DB::connection('uster')->update($query_upd);
               }
            }
         } else if ($jenis_bm == 'delivery') { // Batal muat alih delivery/sp2
            foreach ($request->BM_CONT as $i => $no_cont) {
               $exno_req = $request->BMNO_REQ[$i];

               $get_disable_container = getDisableContainer($no_cont, $jenis_bm);
               if ($get_disable_container['code'] == '1' && !empty($get_disable_container['dataRec'])) {
                  disableContainerSave($get_disable_container, $jenis_bm);
                  $update_batal_delivery = "UPDATE container_stuffing SET STATUS_REQ='BATAL', AKTIF='T' WHERE NO_REQUEST ='$exno_req' AND NO_CONTAINER='$no_cont'";
                  DB::connection('uster')->update($update_batal_delivery);
               }
            }
         }
         DB::connection('uster')->commit();

        return response()->json(['success' => true, 'message' => 'Data saved successfully']);
      } catch (\Exception $e) {
         DB::connection('uster')->rollBack();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
   }
}
