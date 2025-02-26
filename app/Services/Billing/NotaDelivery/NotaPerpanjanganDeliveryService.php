<?php

namespace App\Services\Billing\NotaDelivery;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Elibyy\TCPDF\Facades\TCPDF;
use Exception;
use Illuminate\Http\Response;
use PDF;

class NotaPerpanjanganDeliveryService
{
    function dataPerpanjanganDelivery($request)
    {
        $CARI    = $request->CARI;
        $no_req    = $request->NO_REQ;
        $from   = $request->from;
        $to     = $request->to;
        $id_yard    =     session()->get('IDYARD_STORAGE');

        if ((isset($from)) && (isset($to)) && ($no_req == NULL)) {
            $query_list        = "SELECT *
                                    FROM (
                                        SELECT NVL(nota_delivery.LUNAS, 0) LUNAS,
                                            NVL(nota_delivery.NO_FAKTUR, '-') NO_NOTA,
                                            request_delivery.NO_REQUEST,
                                            TO_CHAR(request_delivery.TGL_REQUEST, 'dd/mm/yyyy') TGL_REQUEST,
                                            TO_CHAR(request_delivery.TGL_REQUEST_DELIVERY, 'dd/mm/yyyy') TGL_REQUEST_DELIVERY,
                                            emkl.NM_PBM AS NAMA_EMKL,
                                            COUNT(container_delivery.NO_CONTAINER) JML_CONT,
                                            request_delivery.PERP_DARI,
                                            request_delivery.NOTA,
                                            request_delivery.KOREKSI
                                        FROM request_delivery,
                                            nota_delivery,
                                            V_MST_PBM emkl,
                                            container_delivery
                                        WHERE request_delivery.KD_EMKL = emkl.KD_PBM
                                            AND emkl.KD_CABANG = '05'
                                            AND request_delivery.NO_REQUEST = container_delivery.NO_REQUEST
                                            AND nota_delivery.NO_REQUEST(+) = request_delivery.NO_REQUEST
                                            AND request_delivery.STATUS = 'PERP'
                                            AND request_delivery.TGL_REQUEST >= DATE '$from' -- Changed date comparison
                                            AND request_delivery.TGL_REQUEST < DATE '$to' -- Changed date comparison
                                        GROUP BY NVL(nota_delivery.LUNAS, 0),
                                                NVL(nota_delivery.NO_FAKTUR, '-'),
                                                request_delivery.NO_REQUEST,
                                                TO_CHAR(request_delivery.TGL_REQUEST, 'dd/mm/yyyy'),
                                                TO_CHAR(request_delivery.TGL_REQUEST_DELIVERY, 'dd/mm/yyyy'),
                                                emkl.NM_PBM,
                                                request_delivery.PERP_DARI,
                                                request_delivery.TGL_REQUEST,
                                                request_delivery.NOTA,
                                                    request_delivery.KOREKSI
                                        ORDER BY request_delivery.TGL_REQUEST DESC
                                    )
                                    WHERE ROWNUM <= 100";
        } else {
            $query_list     = "SELECT *
                                FROM (
                                    SELECT NVL(nota_delivery.LUNAS, 0) LUNAS,
                                        NVL(nota_delivery.NO_FAKTUR, '-') NO_NOTA,
                                        request_delivery.NO_REQUEST,
                                        TO_CHAR(request_delivery.TGL_REQUEST, 'dd/mm/yyyy') TGL_REQUEST,
                                        TO_CHAR(request_delivery.TGL_REQUEST_DELIVERY, 'dd/mm/yyyy') TGL_REQUEST_DELIVERY,
                                        emkl.NM_PBM AS NAMA_EMKL,
                                        COUNT(container_delivery.NO_CONTAINER) JML_CONT,
                                        request_delivery.PERP_DARI,
                                        request_delivery.NOTA,
                                        request_delivery.KOREKSI
                                    FROM request_delivery,
                                        nota_delivery,
                                        V_MST_PBM emkl,
                                        container_delivery
                                    WHERE request_delivery.KD_EMKL = emkl.KD_PBM
                                        AND emkl.KD_CABANG = '05'
                                        AND request_delivery.NO_REQUEST = container_delivery.NO_REQUEST
                                        AND nota_delivery.NO_REQUEST(+) = request_delivery.NO_REQUEST
                                        AND request_delivery.STATUS = 'PERP'
                                    GROUP BY NVL(nota_delivery.LUNAS, 0),
                                            NVL(nota_delivery.NO_FAKTUR, '-'),
                                            request_delivery.NO_REQUEST,
                                            TO_CHAR(request_delivery.TGL_REQUEST, 'dd/mm/yyyy'),
                                            TO_CHAR(request_delivery.TGL_REQUEST_DELIVERY, 'dd/mm/yyyy'),
                                            emkl.NM_PBM,
                                            request_delivery.PERP_DARI,
                                            request_delivery.TGL_REQUEST,
                                            request_delivery.NOTA,
                                                request_delivery.KOREKSI
                                    ORDER BY request_delivery.TGL_REQUEST DESC
                                )
                                WHERE ROWNUM <= 100";
        }

        // echo $query_list;
        return DB::connection('uster')->select($query_list);
    }

    function printProforma($req)
    {

        $koreksi = $req->input('koreksi');
        $no_req = $req->input('no_req');

        $id_user = session()->get('PENGGUNA_ID');
        $query = "SELECT NO_NOTA FROM nota_delivery WHERE TRIM(NO_REQUEST) = TRIM('$no_req') AND STATUS <> 'BATAL'";
        $hasil_ = DB::connection('uster')->selectOne($query);

        if (!isset($hasil_->no_nota)) {
            $this->insertProforma($no_req, $koreksi);
        }

        $query = "SELECT NO_NOTA FROM nota_delivery WHERE TRIM(NO_REQUEST) = TRIM('$no_req') AND STATUS <> 'BATAL'";
        $hasil_ = DB::connection('uster')->selectOne($query);
        $notanya = $hasil_->no_nota;



        //NOTA MTI -> NO_NOTA_MTI
        $query = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT  , a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
        CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAME, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
         THEN a.NO_NOTA
         ELSE A.NO_FAKTUR END NO_FAKTUR_, F_CORPORATE(c.TGL_REQUEST) CORPORATE
                             FROM nota_delivery a, request_delivery c, BILLING.tb_user mu where
                             a.NO_REQUEST = c.NO_REQUEST
                             AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_delivery d WHERE d.NO_REQUEST = '$no_req' )
                             and c.NO_REQUEST = '$no_req'
                             and a.nipp_user = mu.id(+)";
        $data = DB::connection('uster')->selectOne($query);
        $req_tgl = $data->tgl_request;
        $nama_lengkap  = 'Printed By ' . $data->name;
        $lunas = $data->lunas;
        // if (!$request['first']) {
        $nama_lengkap .= '<br/>' . 'Reprinted by ' . session()->get('NAMA_LENGKAP');
        // }
        date_default_timezone_set('Asia/Jakarta');
        $date = date('d M Y H:i:s');
        $corporate_name     = $data->corporate;
        $query_mtr = "SELECT BIAYA AS BEA_MATERAI, BIAYA FROM NOTA_DELIVERY_D WHERE ID_NOTA='$notanya' AND KETERANGAN='MATERAI'";
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }

        if ($lunas == 'YES') {
            $mat = "SELECT * FROM itpk_nota_header WHERE NO_REQUEST='$no_req'";

            $mat3   = DB::connection('uster')->selectOne($mat);
            $no_mat    = $mat3->no_peraturan;
        } else {
            $mat = "SELECT * FROM MASTER_MATERAI WHERE STATUS='Y'";

            $mat3   = DB::connection('uster')->selectOne($mat);
            $no_mat    = $mat3->no_peraturan;
        }

        $query_dtl  = "SELECT TO_CHAR(a.START_STACK,'dd/mm/yyyy') START_STACK,TO_CHAR(a.END_STACK,'dd/mm/yyyy') END_STACK, a.KETERANGAN, a.JML_CONT, a.JML_HARI, b.SIZE_, b.TYPE_, b.STATUS, a.HZ, TO_CHAR(a.TARIF,'999,999,999,999') TARIF , TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA FROM nota_delivery_d a, iso_code b, nota_delivery c WHERE a.ID_NOTA = c.NO_NOTA AND a.ID_ISO = b.ID_ISO(+)
        AND c.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM NOTA_DELIVERY d WHERE d.NO_REQUEST = '$no_req') AND a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI')";
        $i = 0;
        $row2 = DB::connection('uster')->select($query_dtl);

        $qcont = "SELECT A.NO_CONTAINER,A.STATUS,B.SIZE_,B.TYPE_ FROM CONTAINER_DELIVERY A, MASTER_CONTAINER B WHERE A.NO_CONTAINER = B.NO_CONTAINER AND A.NO_REQUEST = '$no_req'";
        $rcont = DB::connection('uster')->select($qcont);
        $listcont = "<br/>Daftar Container<br/><b>";
        foreach ($rcont as $rc) {
            $listcont .= $rc->no_container . "+" . $rc->size_ . "-" . $rc->type_ . "-" . $rc->status . " ";
        }
        $listcont .= "</b>";
        // jumlah detail barangnya
        $query_jum = "SELECT COUNT(1) JUM_DETAIL FROM NOTA_RECEIVING_D A WHERE A.NO_NOTA='$notanya'";
        $data_jum = DB::connection('uster')->selectOne($query_jum);
        $jum_data_page = 18; //jumlah data dibatasi per page 18 data
        $jum_page = ceil($data_jum->jum_detail / $jum_data_page);   //hasil bagi pembulatan ke atas
        if (($data_jum->jum_detail % $jum_data_page) > 10 || ($data_jum->jum_detail % $jum_data_page) == 0)  $jum_page++;    //jika pada page terakhir jumlah data melebihi 12, tambah 1 page lagi
        $jum_page = 1;


        return array(
            'data' => $data,
            'date' => $date,
            'detail' => $row2,
            'nama_lengkap' => $nama_lengkap,
            'data_mtr' => $data_mtr,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'rcont' => $rcont,
            'lunas' => $lunas
        );
    }

    function printNota($request)
    {
        $no_req        = $request->no_req;
        $koreksi        = $request->koreksi;

        $query_nota    = "SELECT c.NM_PBM AS EMKL,
                          c.NO_NPWP_PBM AS NPWP,
                          c.ALMT_PBM AS ALAMAT,
						  b.DELIVERY_KE,
						  TO_CHAR(b.TGL_REQUEST,'DD-MM-RRRR') TGL_REQUEST,
							F_CORPORATE(b.TGL_REQUEST) CORPORATE,
						  c.NO_ACCOUNT_PBM
                   FROM REQUEST_DELIVERY b INNER JOIN
                            V_MST_PBM c ON b.KD_EMKL = c.KD_PBM AND c.KD_CABANG = '05'
                   WHERE b.NO_REQUEST = '$no_req'";

        $row_nota    = DB::connection('uster')->selectOne($query_nota);
        $req_tgl     = $row_nota->tgl_request;
        $kd_pbm     = $row_nota->no_account_pbm;
        $display     = 1;

        $query_tgl    = "SELECT TO_CHAR(TGL_REQUEST,'YYYY/MM/DD') TGL_REQUEST FROM request_delivery
                             WHERE NO_REQUEST = '$no_req'
                            ";
        $tgl_req    = DB::connection('uster')->selectOne($query_tgl);
        $tgl_re         = $tgl_req->tgl_request;

        $parameter = array(
            'id_nota' => 2,
            'tgl_req' => $tgl_re,
            'no_request:20' => $no_req,
            'err_msg:100' => 'NULL'
        );
        //debug($parameter);
        $delivery_ke         = $row_nota->delivery_ke;

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
        $sql_xpi = "DECLARE id_nota NUMBER; tgl_req DATE; no_request VARCHAR2(100); jenis VARCHAR2 (100); err_msg VARCHAR2(100); BEGIN  id_nota := 4; tgl_req := '$tgl_re'; no_request := '$no_req'; err_msg := 'NULL';jenis := 'delivery'; pack_get_nota_delivery.create_detail_nota(id_nota,tgl_req,no_request,jenis, err_msg); END;";
        // //echo $sql_xpi;
        DB::connection('uster')->statement($sql_xpi);

        $detail_nota  = "SELECT a.JML_HARI, TO_CHAR(a.TARIF, '999,999,999,999') AS TARIF, TO_CHAR(a.BIAYA, '999,999,999,999') AS BIAYA, a.KETERANGAN,
							a.HZ, a.JML_CONT, TO_CHAR(a.START_STACK,'dd/mm/yyyy') START_STACK, TO_CHAR(a.END_STACK,'dd/mm/yyyy') END_STACK, b.SIZE_, b.TYPE_, b.STATUS
					FROM temp_detail_nota a, iso_code b
					WHERE a.id_iso = b.id_iso and a.no_request = '$no_req'
					and a.KETERANGAN NOT IN ('ADMIN NOTA','MATERAI')";/*gagat modif 09 feb 2020*/

        $row_detail   = DB::connection('uster')->select($detail_nota);

        // echo json_encode($row_detail);die();


        //jumlah container per request
        $jum          = "SELECT COUNT(NO_CONTAINER) JUMLAH FROM container_delivery WHERE no_request = '$no_req'";
        $jum_         = DB::connection('uster')->selectOne($jum);

        $jumlah_cont  = $jum_->jumlah;

        //get_via

        $q_via = "SELECT NO_CONTAINER, VIA FROM CONTAINER_DELIVERY WHERE NO_REQUEST = '$no_req'";
        $row_v = DB::connection('uster')->select($q_via);


        //tarif pass
        $pass          = "SELECT TO_CHAR(($jumlah_cont * a.TARIF), '999,999,999,999') PASS, ($jumlah_cont * a.TARIF) TARIF
					  FROM master_tarif a, group_tarif b
					 WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF
					       AND TO_DATE ('$tgl_re', 'YYYY/MM/DD') BETWEEN b.START_PERIOD
					                                                    AND b.END_PERIOD
					       AND a.ID_ISO = 'PASS'";

        $row_pass     = DB::connection('uster')->selectOne($pass);
        $tarif_pass   = $row_pass->tarif;


        $total_          = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN, (SUM(BIAYA) + SUM(PPN)) TOTAL_TAGIHAN FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN NOT IN ('MATERAI')";
        /**fauzan modif 26 AUG 2020*/

        $total2           = DB::connection('uster')->selectOne($total_);

        $total = $total2->total;
        $total_ppn = $total2->ppn;
        $total_tagihan = $total2->total_tagihan;

        //$no_request = $row_nota["NO_REQUEST"];

        //Discount
        $discount = 0;
        $query_discount        = "SELECT TO_CHAR($discount , '999,999,999,999') AS DISCOUNT FROM DUAL";
        $row_discount        = DB::connection('uster')->selectOne($query_discount);

        //Biaya Administrasi
        $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
        $row_adm        = DB::connection('uster')->selectOne($query_adm);
        $adm             = $row_adm->tarif;

        //Menghitung Total dasar pengenaan pajak
        $total_ = $total;
        $query_tot        = "SELECT TO_CHAR('$total_' , '999,999,999,999') AS TOTAL_ALL FROM DUAL";
        $row_tot        = DB::connection('uster')->selectOne($query_tot);

        //Menghitung Jumlah PPN
        //$ppn = $total_ppn;
        $ppn = round($total_ppn);
        $query_ppn        = "SELECT TO_CHAR('$ppn' , '999,999,999,999') AS PPN FROM DUAL";
        $row_ppn        = DB::connection('uster')->selectOne($query_ppn);

        //Menghitung Bea Materai gagat modif 09 februari 2020
        $sql_mtr          = "SELECT BIAYA AS BEA_MATERAI FROM TEMP_DETAIL_NOTA WHERE no_request = '$no_req' AND KETERANGAN='MATERAI'";

        $row_mtr         = DB::connection('uster')->selectOne($sql_mtr);
        $data_mtr_biaya = $row_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $row_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        /**end modify gagat 09 feb 2020*/
        $query_materai        = "SELECT TO_CHAR('$bea_materai' , '999,999,999,999') AS MATERAI FROM DUAL";
        $row_materai        = DB::connection('uster')->selectOne($query_materai);

        //Menghitung Jumlah dibayar
        $total_bayar        = $total_tagihan + $bea_materai; //+ $tarif_pass;	/**Fauzan modif 26 AUG 2020 "+ $bea_materai"*/
        $query_bayar        = "SELECT TO_CHAR('$total_bayar' , '999,999,999,999') AS TOTAL_BAYAR FROM DUAL";
        $row_bayar          = DB::connection('uster')->selectOne($query_bayar);

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);

        $dataArr = [
            'corporate_name' => $row_nota->corporate,
            "url_ins" => "insert_proforma",
            "row_discount" => $row_discount,
            "nama_peg" => $nama_peg,
            "tgl_nota" => $tgl_re,
            "row_adm" => $row_adm,
            "row_pass" => $row_pass,
            "row_tot" => $row_tot,
            "row_ppn" => $row_ppn,
            "row_materai" => $row_materai,
            "bea_materai" => $bea_materai,
            "row_bayar" => $row_bayar,
            // "row_trf_brsh"=> $row_trf_brsh,
            "row_nota" => $row_nota,
            // "no_nota"=> $no_nota,
            "no_req" => $no_req,
            "koreksi" => $koreksi,
            "row_detail" => $row_detail,
            "jenis" => "gerak",
            "rnota" => ''
        ];

        return $dataArr;
    }

    function insertProforma($request)
    {
        DB::transaction();
        try {
            $nipp   = session()->get("LOGGED_STORAGE");
            $no_req = $request->no_req;
            $koreksi = $request->koreksi;

            $query_cek    = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA,10,15))+1),6,0), '000001') JUM_,
                              TO_CHAR(SYSDATE, 'MM') AS MONTH,
                              TO_CHAR(SYSDATE, 'YY') AS YEAR
                        FROM NOTA_DELIVERY
                       WHERE NOTA_DELIVERY.TGL_NOTA BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)";


            $jum_        = DB::connection('uster')->selectOne($query_cek);
            $jum        = $jum_->jum_;
            $month        = $jum_->month;
            $year        = $jum_->year;

            $no_nota    = "0505" . $month . $year . $jum;

            // Cek NO NOTA MTI
            // firman 20 agustus 2020
            $query_mti = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA_MTI,10,15))+1),6,0),'000001') JUM_,
                           TO_CHAR(SYSDATE, 'YYYY') AS YEAR
                           FROM MTI_COUNTER_NOTA WHERE TAHUN =  TO_CHAR(SYSDATE,'YYYY')";

            $jum_mti    = DB::connection('uster')->selectOne($query_mti);
            $jum_nota_mti    = $jum_mti->jum_;
            $year_mti        = $jum_mti->year;

            $no_nota_mti    = "17." . $year_mti . "." . $jum_nota_mti;

            //select master pbm
            $query_master    = "SELECT b.KD_PBM, b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM FROM request_delivery a, v_mst_pbm b WHERE a.KD_EMKL = b.KD_PBM AND a.NO_REQUEST = '$no_req'";
            //echo $query_master;die;
            $master        = DB::connection('uster')->selectOne($query_master);
            $kd_pbm        = $master->kd_pbm;
            $nm_pbm        = $master->nm_pbm;
            $almt_pbm    = $master->almt_pbm;
            $npwp       = $master->no_npwp_pbm;


            $total_        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN, (SUM(BIAYA) + SUM(PPN)) TOTAL_TAGIHAN FROM temp_detail_nota WHERE no_request = '$no_req'
						 AND KETERANGAN NOT IN ('MATERAI')";
            /**Fauzan modif 31 AUG 2020 "AND KETERANGAN NOT IN ('MATERAI')"*/
            //echo $total_;die;

            $total2         = DB::connection('uster')->selectOne($total_);

            $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
            $row_adm        = DB::connection('uster')->selectOne($query_adm);
            $adm             = $row_adm->tarif;

            /*Fauzan add materai 31 Agustus 2020*/
            $query_materai        = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";

            $row_materai        = DB::connection('uster')->selectOne($query_materai);
            $materai            = $row_materai->bea_materai;
            /*end Fauzan add materai 31 Agustus 2020*/

            $total = $total2->total;
            $total_ppn = $total2->ppn;
            $total_tagihan = $total2->total_tagihan;
            //  $adm   = 10000;
            $total_ = $total;
            $ppn   = $total_ppn;
            $tagihan = $total_tagihan + $materai;
            /**Fauzan modif 31 AUG 2020 "+ $materai"*/


            if ($koreksi <> 'Y') {

                //$faktur	 	= "SELECT CONCAT(a.NO_FAKTUR ,(LPAD(NVL((MAX(SEQ_FAKTUR)+1),1),6,0))) FAKTUR, NVL((MAX(SEQ_FAKTUR)+1),1) SEQ FROM NOTA_ALL_H, (SELECT NO_FAKTUR FROM FAKTUR WHERE TAHUN =  to_char(sysdate, 'RRRR') AND KETERANGAN = 'NEW') a GROUP BY a.NO_FAKTUR";

                $status_nota = 'NEW';
                $nota_lama = '';
            } else {
                $status_nota = 'KOREKSI';
                $faktur         = "SELECT NO_NOTA, NO_FAKTUR, KD_EMKL FROM NOTA_DELIVERY WHERE NO_REQUEST = '$no_req' AND NO_NOTA =(SELECT MAX(NO_NOTA) FROM NOTA_DELIVERY WHERE NO_REQUEST = '$no_req')";

                $faktur_     = DB::connection('uster')->selectOne($faktur);
                $nota_lama    = $faktur_->no_faktur;
                $update = "UPDATE NOTA_DELIVERY SET STATUS = 'BATAL' WHERE NO_NOTA = '$nota_lama'";
                DB::connection('uster')->statement($update);
            }
            $query_insert_nota    = "INSERT INTO NOTA_DELIVERY(NO_NOTA,
                                        TAGIHAN,
                                        PPN,
                                        TOTAL_TAGIHAN,
										NO_REQUEST,
										NIPP_USER,
										LUNAS,
										CETAK_NOTA,
										TGL_NOTA,
                                        EMKL,
                                        ALAMAT,
                                        NPWP,
                                        STATUS,
                                        ADM_NOTA,
										KD_EMKL,
										TGL_NOTA_1,
                                        NOTA_LAMA,
										NO_NOTA_MTI)
										VALUES('$no_nota',
                                        '$total_',
                                        '$ppn',
                                        '$tagihan',
										'$no_req',
										'$nipp',
										'NO',
										1,
										SYSDATE,
                                        '$nm_pbm',
                                        '$almt_pbm',
                                        '$npwp',
                                        'PERP',
                                        '$adm',
										'$kd_pbm',
										SYSDATE,'$nota_lama',
										'$no_nota_mti')";




            //echo $query_insert_nota;die;

            //echo $query_insert_nota;die;
            if (DB::connection('uster')->statement($query_insert_nota)) //(TRUE)
            {
                //UPDATE COUNTER MTI DAN PENAMBAHAN FIELD NO_NOTA_MTI DI HEADER DAN DETAIL
                //firman 20 agustus 2020

                $query_mti = "INSERT INTO MTI_COUNTER_NOTA
							(
							 NO_NOTA_MTI,
							 TAHUN,
							 NO_REQUEST
							)
							VALUES
							(
							'$no_nota_mti',
							TO_CHAR(SYSDATE,'YYYY'),
							'$no_req'
							)";

                DB::connection('uster')->statement($query_mti);

                $query_detail    = "SELECT ID_ISO,
										  TARIF,
										  BIAYA,
										  KETERANGAN,
										  JML_CONT,
										  START_STACK,
										  END_STACK,
										  HZ,
										  COA,
										  PPN,
										  JML_HARI
									FROM temp_detail_nota
									WHERE no_request = '$no_req' ";

                $row        = DB::connection('uster')->select($query_detail);
                $i = 1;
                foreach ($row as $item) {
                    $id_iso = $item->id_iso;
                    $tarif  = $item->tarif;
                    $biaya  = $item->biaya;
                    $ket    = $item->keterangan;
                    $jml_cont  = $item->jml_cont;
                    $hz     = $item->hz;
                    $start  = $item->start_stack;
                    $end    = $item->end_stack;
                    $jml    = $item->jml_hari;
                    $coa    = $item->coa;
                    $ppn_d    = $item->ppn;

                    $query_insert    = "INSERT INTO nota_DELIVERY_d
                                            (
                                             ID_ISO,
                                             TARIF,
                                             BIAYA,
                                             KETERANGAN,
                                             ID_NOTA,
                                             JML_CONT,
                                             HZ,
                                             START_STACK,
                                             END_STACK,
                                             JML_HARI,
											 COA,
											 LINE_NUMBER,
											 PPN,
											 NO_NOTA_MTI
                                            ) VALUES
                                            (
                                            '$id_iso',
                                            '$tarif',
                                            '$biaya',
                                            '$ket',
                                            '$no_nota',
                                            '$jml_cont',
                                            '$hz',
                                            '$start',
                                            '$end',
                                            '$jml',
											'$coa',
											'$i',
											'$ppn_d',
											'$no_nota_mti')";

                    DB::connection('uster')->statement($query_insert);
                    // $db4->query($query_insert);

                    $i++;
                }


                $update_nota = "UPDATE NOTA_DELIVERY SET CETAK_NOTA = '1' WHERE NO_NOTA = '$no_nota'";
                $update_req = "UPDATE request_delivery SET NOTA = 'Y' WHERE no_request = '$no_req'";
                DB::connection('uster')->statement($update_nota);
                // $db4->query($update_nota);
                DB::connection('uster')->statement($update_req);
                //  $update_nota1 = "UPDATE REQUEST_DELIVERY SET NOTA = 'Y' WHERE NO_REQUEST = '$no_req'";
                // DB::connection('uster')->statement($update_nota1);
                $delete_temp = "DELETE from temp_detail_nota WHERE no_request = '$no_req'";
                DB::connection('uster')->statement($delete_temp);
            }
            DB::commit();
            return;
        } catch (Exception $e) {
            DB::rollBack();
            return;
        }
    }
}
