<?php

namespace App\Services\Billing\NotaReceiving;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PDO;

class NotaReceivingService
{
    public function getData(Request $request)
    {
        $no_req = $request->no_request;
        $from = $request->tgl_awal;
        $to = $request->tgl_akhir;
        $noReqWhere = '';
        $dateWhere = '';
        if ($no_req != null || $from != null || $to != null) {
            if ($no_req != null && ($from == null && $to == null)) {
                $noReqWhere = "AND a.NO_REQUEST LIKE '%$no_req%'";
            } else if ($request->no_request == null && ($from != null && $to != null)) {
                $dateWhere = "AND a.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY/MM/DD') AND TO_DATE ( '$to', 'YYYY/MM/DD') ";
            } else if ($no_req != null && ($from != null && $to != null)) {
                $noReqWhere = "AND a.NO_REQUEST LIKE '%$no_req%'";
                $dateWhere = "AND a.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY/MM/DD') AND TO_DATE ( '$to', 'YYYY/MM/DD') ";
            }

            $query_list = "SELECT * FROM (SELECT a.NO_REQUEST,
                                TO_CHAR(a.TGL_REQUEST,'YYYY/MM/DD') TGL_REQUEST,
                                a.NM_CONSIGNEE CONSIGNEE,
                                COUNT(c.NO_CONTAINER) JUMLAH
                            FROM request_receiving a, container_receiving c
                            WHERE     a.NO_REQUEST = c.NO_REQUEST
                                AND a.RECEIVING_DARI = 'LUAR'
                                $noReqWhere
                                $dateWhere
                            GROUP BY a.NO_REQUEST,
                                a.TGL_REQUEST,
                                a.NM_CONSIGNEE
                            ORDER BY a.TGL_REQUEST DESC) WHERE ROWNUM <= 100";
        } else {
            $query_list = "SELECT * FROM (SELECT a.NO_REQUEST,
                                TO_CHAR(a.TGL_REQUEST,'YYYY/MM/DD') TGL_REQUEST,
                                a.NM_CONSIGNEE CONSIGNEE,
                                COUNT(c.NO_CONTAINER) JUMLAH
                            FROM request_receiving a, container_receiving c
                            WHERE     a.NO_REQUEST = c.NO_REQUEST
                                AND a.RECEIVING_DARI = 'LUAR'
                            GROUP BY a.NO_REQUEST,
                                a.TGL_REQUEST,
                                a.NM_CONSIGNEE
                            ORDER BY a.TGL_REQUEST DESC) WHERE ROWNUM <= 100";
        }

        // dd($query_list);
        $query = '(' . $query_list . ') nota_receiving';
        // $data = array();
        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'YYYY/MM/DD'");
        $data = DB::connection('uster')->table(DB::raw($query))->select()->get();
        // DB::connection('uster')->table(DB::raw($query))->orderBy('nota_receiving.tgl_request', 'desc')->chunk(20, function ($chunk) use (&$data) {
        //     foreach ($chunk as $dt) {
        //         $data[] = $dt;
        //     }
        // });

        return $data;
    }

    public function printProforma($no_req)
    {
        $no_req = base64_decode($no_req);
        $query = "SELECT NO_NOTA FROM nota_receiving WHERE TRIM(NO_REQUEST) = TRIM('$no_req') AND STATUS <> 'BATAL'";
        $hasil = DB::connection('uster')->selectOne($query);
        $notanya = $hasil->no_nota ?? '';

        $query = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT  , a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
                    CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAME,
                        CASE
                        WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
                            THEN a.NO_NOTA
                        ELSE
                            A.NO_FAKTUR
                        END NO_FAKTUR_
                    FROM nota_receiving a, request_receiving c, billing_nbs.tb_user mu where
                    a.NO_REQUEST = c.NO_REQUEST
                    AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM NOTA_RECEIVING d WHERE d.NO_REQUEST = '$no_req' )
                    and c.NO_REQUEST = '$no_req'
                    and a.nipp_user = mu.id(+)";

        $data = DB::connection('uster')->selectOne($query);
        $nama_lengkap  = $data->name;
        $lunas = $data->lunas;


        if (request('first') == null) {
            $nama_lengkap .= '<br/>' . 'Reprinted by ' . Session::get('NAMA_LENGKAP');
        }

        /**hitung materai Fauzan 24 Agustus 2020*/
        $query_mtr = "SELECT TO_CHAR (a.BIAYA, '999,999,999,999') BEA_MATERAI, a.BIAYA
                        FROM nota_receiving_d a
                        WHERE a.NO_NOTA = '$notanya' AND a.KETERANGAN ='MATERAI' ";

        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        if (!empty($data_mtr) && $data_mtr->biaya > 0) {
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

        $query_dtl = "SELECT a.KETERANGAN,
                        a.JML_CONT,
                        a.JML_HARI,
                        b.SIZE_,
                        b.TYPE_,
                        b.STATUS,
                        a.HZ,
                        TO_CHAR (a.TARIF, '999,999,999,999') TARIF,
                        TO_CHAR (a.BIAYA, '999,999,999,999') BIAYA
                    FROM nota_receiving_d a, iso_code b, nota_receiving c
                    WHERE     a.ID_ISO = b.ID_ISO(+)
                        AND a.NO_NOTA = c.NO_NOTA
                        AND a.KETERANGAN NOT IN ('ADMIN NOTA','MATERAI') /**Fauzan modif 24 Agustus 2020 [NOT IN MATERAI]*/
                        AND c.TGL_NOTA = (SELECT MAX (d.TGL_NOTA)
                                            FROM NOTA_RECEIVING d
                                            WHERE d.NO_REQUEST = '$no_req')";

        $row2 = DB::connection('uster')->select($query_dtl);
        $qcont = "SELECT A.NO_CONTAINER,A.STATUS,B.SIZE_,B.TYPE_ FROM CONTAINER_RECEIVING A, MASTER_CONTAINER B WHERE A.NO_CONTAINER = B.NO_CONTAINER AND A.NO_REQUEST = '$no_req'";
        $rcont = DB::connection('uster')->select($qcont);

        $listcont = "<br/>Daftar Container<br/><b>";
        foreach ($rcont as $rc) {
            $listcont .= $rc->no_container . "+" . $rc->size_ . "-" . $rc->type_ . "-" . $rc->status . " ";
        }
        $listcont .= "</b>";

        return array(
            'data' => $data,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'row2' => $row2,
            'data_mtr' => $data_mtr,
            'nama_lengkap' => $nama_lengkap,
            'lunas' => $lunas,
            'listcont' => $listcont
        );
    }

    public function recalc(array $data)
    {
        DB::beginTransaction();
        try {
            DB::connection('uster')->statement("begin PACK_RECALC_NOTA.recalc_receiving(:req, :no_nota); end;", ['req' => base64_decode($data[0]), 'no_nota' => base64_decode($data[1])]);

            DB::commit();
            return response()->json([
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ]
            ], 200);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], 500);
        }
    }

    public function previewNota($no_req, $koreksi)
    {
        $query_nota    = "SELECT c.NM_PBM AS EMKL,
                            c.NO_NPWP_PBM AS NPWP,
                            c.ALMT_PBM AS ALAMAT,
                            c.NO_ACCOUNT_PBM,
                            c.KD_PBM,
                            TO_CHAR(b.TGL_REQUEST,'DD-MM-RRRR') TGL_REQUEST
                        FROM REQUEST_RECEIVING b INNER JOIN
                                V_MST_PBM c ON b.KD_CONSIGNEE = c.KD_PBM
                                AND c.KD_CABANG = '05'
                        WHERE b.NO_REQUEST = '$no_req'";

        $row_nota    = DB::connection('uster')->selectOne($query_nota);
        $kd_pbm     = $row_nota->no_account_pbm;
        $display     = 1;
        $req_tgl     = $row_nota->tgl_request;

        $query_tgl    = "SELECT TO_DATE(TO_CHAR(TGL_REQUEST,'YYYY/MM/DD')) TGL_REQUEST FROM request_receiving
                            WHERE NO_REQUEST = '$no_req'";

        $tgl_req    = DB::connection('uster')->selectOne($query_tgl);
        $tgl_re     = Carbon::parse($tgl_req->tgl_request)->format('Y/m/d');

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
        $sql_xpi = "DECLARE
                        id_nota NUMBER;
                        tgl_req DATE;
                        no_request VARCHAR2(100);
                        jenis VARCHAR2 (100);
                        err_msg VARCHAR2(100);
                        BEGIN
                                id_nota := 1;
                                tgl_req := '$tgl_re';
                                no_request := '$no_req';
                                err_msg := 'NULL';
                                jenis := 'receiving';
                            pack_get_nota_receiving.create_detail_nota(id_nota,tgl_req,no_request,jenis, err_msg);
                        END;";

        $exec = DB::connection('uster')->statement($sql_xpi);

        $detail_nota  = "SELECT TO_CHAR (a.TARIF, '999,999,999,999') AS TARIF,
						   TO_CHAR (a.BIAYA, '999,999,999,999') AS BIAYA,
						   a.KETERANGAN,
						   a.HZ,
						   a.JML_CONT,
						   TO_DATE (a.START_STACK, 'dd/mm/yyyy') START_STACK,
						   TO_DATE (a.END_STACK, 'dd/mm/yyyy') END_STACK,
						   b.SIZE_,
						   b.TYPE_,
						   b.STATUS,
						   a.JML_HARI
					  FROM temp_detail_nota a, iso_code b
					 WHERE a.id_iso = b.id_iso AND a.no_request = '$no_req'
					 AND KETERANGAN NOT IN ('ADMIN NOTA','MATERAI')";

        $row_detail   = DB::connection('uster')->select($detail_nota);

        $queryTotal = "SELECT SUM(BIAYA) TOTAL FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN NOT IN ('ADMIN NOTA','MATERAI')";
        $total = DB::connection('uster')->selectOne($queryTotal);
        $total = $total->total;

        $jum   = "SELECT COUNT(NO_CONTAINER) JUMLAH FROM container_receiving WHERE no_request = '$no_req'";
        $jumlah_cont  = DB::connection('uster')->selectOne($jum);
        $jumlah_cont  = $jumlah_cont->jumlah;

        //tarif pass
        $pass          = "SELECT TO_CHAR(($jumlah_cont * a.TARIF), '999,999,999,999') PASS, ($jumlah_cont * a.TARIF) TARIF
                                FROM master_tarif a, group_tarif b
                            WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF
                                    AND TO_DATE ('$tgl_re', 'yyyy/mm/dd') BETWEEN b.START_PERIOD
                                                                                AND b.END_PERIOD
                                    AND a.ID_ISO = 'PASS'";
        $row_pass     = DB::connection('uster')->selectOne($pass);
        $tarif_pass   = $row_pass->tarif;

        //Discount
        $discount = 0;
        $query_discount        = "SELECT TO_CHAR($discount , '999,999,999,999') AS DISCOUNT FROM DUAL";
        $row_discount        = DB::connection('uster')->selectOne($query_discount);
        $discount = $row_discount->discount;

        //Biaya Administrasi
        $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
        $row_adm        = DB::connection('uster')->selectOne($query_adm);
        $adm             = $row_adm->tarif;

        //Menghitung Total dasar pengenaan pajak
        $total_ = (int)$total + (int)$adm;
        $query_tot        = "SELECT TO_CHAR('$total_' , '999,999,999,999') AS TOTAL_ALL FROM DUAL";
        $row_tot        = DB::connection('uster')->selectOne($query_tot);
        $total_all = $row_tot->total_all;

        //Menghitung Jumlah PPN
        $ppn = round($total_ / 10);
        $query_ppn        = "SELECT TO_CHAR('$ppn' , '999,999,999,999') AS PPN FROM DUAL";
        $row_ppn        = DB::connection('uster')->selectOne($query_ppn);
        $ppn = $row_ppn->ppn;

        //gagat add materai 09 februari 2020
        $materai_  = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";
        $materai   = DB::connection('uster')->selectOne($materai_);
        if ($materai->bea_materai != null) {
            $bea_materai = $materai->bea_materai;
        } else {
            $bea_materai = 0;
        }
        $query_materai        = "SELECT TO_CHAR('$bea_materai' , '999,999,999,999') AS BEA_MATERAI FROM DUAL";
        $row_materai        = DB::connection('uster')->selectOne($query_materai);

        //Menghitung ppn per item
        $query_ppn_item        = "SELECT sum(ppn) ppn, TO_CHAR(sum(ppn), '999,999,999,999') AS ppn_item FROM temp_detail_nota where no_request = '$no_req'";
        $row_ppn_item        = DB::connection('uster')->selectOne($query_ppn_item);
        $ppn_item             = $row_ppn_item->ppn;

        //Menghitung Jumlah dibayar
        $total_bayar         = $total_ + $ppn_item + $bea_materai;/*gagat modif 09 februari 2020*/
        $query_bayar        = "SELECT TO_CHAR('$total_bayar' , '999,999,999,999') AS TOTAL_BAYAR FROM DUAL";
        $row_bayar          = DB::connection('uster')->selectOne($query_bayar);
        $total_bayar = $row_bayar->total_bayar;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $pegawai    = DB::connection('uster')->selectOne($pegawai);

        return response()->json([
            'row_nota' => $row_nota,
            'row_detail' => $row_detail,
            'tgl_req' => $tgl_re,
            'row_pass' => $tarif_pass,
            'pegawai' => $pegawai,
            'row_bayar' => $total_bayar,
            'row_materai' => $row_materai->bea_materai,
            'row_discount' => $discount,
            'row_ppn' => $ppn,
            'row_ppn_item' => $ppn_item,
            'row_adm' => $adm,
            'row_tot' => $total_all,
            'bea_materai' => $bea_materai,
            'no_req' => $no_req,
            'koreksi' => $koreksi,
        ]);
    }

    public function insertProforma($no_req, $koreksi)
    {
        $query_cek    = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA,10,15))+1),6,0), '000001') JUM_,
                                TO_CHAR(SYSDATE, 'MM') AS MONTH,
                                TO_CHAR(SYSDATE, 'YY') AS YEAR
                        FROM NOTA_RECEIVING
                        WHERE NOTA_RECEIVING.TGL_NOTA BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)";

        $exec_cek = DB::connection('uster')->selectOne($query_cek);
        $jum = $exec_cek->jum_;
        $month = $exec_cek->month;
        $year = $exec_cek->year;

        $no_nota    = "0205" . $month . $year . $jum;

        // CEK NO NOTA MTI
        // firman 20 agustus 2020
        $query_mti = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA_MTI,10,15))+1),6,0),'000001') JUM_,
            TO_CHAR(SYSDATE, 'YYYY') AS YEAR
            FROM MTI_COUNTER_NOTA WHERE TAHUN =  TO_CHAR(SYSDATE,'YYYY')";

        $jum_mti    = DB::connection('uster')->selectOne($query_mti);
        $jum_nota_mti    = $jum_mti->jum_;
        $year_mti        = $jum_mti->year;

        $no_nota_mti    = "17." . $year_mti . "." . $jum_nota_mti;

        //select master pbm
        $query_master    = "SELECT b.KD_PBM, b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM, TO_CHAR(a.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST , COUNT(c.NO_CONTAINER) JUMLAH FROM request_receiving a, v_mst_pbm b, container_receiving c WHERE a.KD_CONSIGNEE = b.KD_PBM AND a.NO_REQUEST = c.NO_REQUEST AND a.NO_REQUEST = '$no_req' GROUP BY  b.KD_PBM, b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM, TO_CHAR(a.TGL_REQUEST,'dd/mm/yyyy')";
        $master        = DB::connection('uster')->selectOne($query_master);
        $kd_pbm        = $master->kd_pbm;
        $nm_pbm        = $master->nm_pbm;
        $almt_pbm    = $master->almt_pbm;
        $npwp       = $master->no_npwp_pbm;
        $tgl_re       = $master->tgl_request;
        $jumlah_cont = $master->jumlah;

        //tarif pass
        $pass          = "SELECT TO_CHAR(($jumlah_cont * a.TARIF), '999,999,999,999') PASS, ($jumlah_cont * a.TARIF) TARIF
                            FROM master_tarif a, group_tarif b
                        WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF
                                AND TO_DATE ('$tgl_re', 'dd/mm/yyyy') BETWEEN b.START_PERIOD AND b.END_PERIOD
                                AND a.ID_ISO = 'PASS'";

        $row_pass     = DB::connection('uster')->selectOne($pass);
        $tarif_pass   = $row_pass->tarif;

        $total_        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN NOT IN ('MATERAI')";
        /**Fauzan modif 26 AUG 2020 "AND KETERANGAN NOT IN ('MATERAI')"*/
        $total2         = DB::connection('uster')->selectOne($total_);
        $total_         = $total2->total;
        $ppn             = $total2->ppn;

        $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
        $row_adm        = DB::connection('uster')->selectOne($query_adm);
        $adm             = $row_adm->tarif;

        /*Fauzan add materai 26 Agustus 2020*/
        $query_materai        = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";
        $row_materai        = DB::connection('uster')->selectOne($query_materai);
        $materai            = $row_materai->bea_materai;
        /*end Fauzan add materai 26 Agustus 2020*/

        $tagihan = $total_ + $ppn + $materai;
        /**Fauzan modif 26 AUG 2020 "+ $materai"*/
        if ($koreksi <> 'Y') {
            $status_nota = 'NEW';
            $nota_lama = '';
        } else {
            $status_nota = 'KOREKSI';
            $query_faktur         = "SELECT NO_NOTA, NO_FAKTUR, KD_EMKL FROM NOTA_RECEIVING WHERE NO_REQUEST = '$no_req' AND NO_NOTA =(SELECT MAX(NO_NOTA) FROM NOTA_RECEIVING WHERE NO_REQUEST = '$no_req')";
            $faktur     = DB::connection('uster')->selectOne($query_faktur);
            $nota_lama    = $faktur->no_faktur;
        }

        $query_mti = "INSERT INTO MTI_COUNTER_NOTA(NO_NOTA_MTI,TAHUN,NO_REQUEST)
						VALUES ('$no_nota_mti',TO_CHAR(SYSDATE,'YYYY'),'$no_req')";
        $execInsert = DB::connection('uster')->statement($query_mti);

        $rawParamInsertNota = array(
            'NO_NOTA' => $no_nota,
            'TAGIHAN' => $total_,
            'PPN' => $ppn,
            'TOTAL_TAGIHAN' => $tagihan,
            'NO_REQUEST' => $no_req,
            'NIPP_USER' => Session::get('LOGGED_STORAGE'),
            'LUNAS' => 'NO',
            'CETAK_NOTA' => 1,
            'TGL_NOTA' => OCI_SYSDATE,
            'EMKL' => $nm_pbm,
            'ALAMAT' => $almt_pbm,
            'NPWP' => $npwp,
            'STATUS' => $status_nota,
            'ADM_NOTA' => $adm,
            'PASS' => $tarif_pass,
            'KD_EMKL' => $kd_pbm,
            'TGL_NOTA_1' => OCI_SYSDATE,
            'NOTA_LAMA' => $nota_lama,
            'TGL_REQUEST' => "TO_DATE('$tgl_re','dd/mm/rrrr')@ORA",
            'NO_NOTA_MTI' => $no_nota_mti,
        );

        DB::beginTransaction();
        try {
            $paramInsertNota = generateQuerySimpan($rawParamInsertNota);
            $queryInsertNota = "INSERT INTO NOTA_RECEIVING $paramInsertNota";
            $execInsertNota = DB::connection('uster')->statement($queryInsertNota);

            if ($execInsertNota) {
                $query_detail    = "SELECT
                                        ID_ISO,
                                        TARIF,
                                        BIAYA,
                                        KETERANGAN,
                                        JML_CONT,
                                        HZ,
                                        COA,
                                        JML_HARI,
                                        PPN
                                    FROM temp_detail_nota WHERE no_request = '$no_req' ";

                $row = DB::connection('uster')->select($query_detail);
                foreach ($row as $key => $item) {
                    $id_iso         = $item->id_iso;
                    $tarif          = $item->tarif;
                    $biaya          = $item->biaya;
                    $ket            = $item->keterangan;
                    $jml_cont          = $item->jml_cont;
                    $hz             = $item->hz;
                    $coa             = $item->coa;
                    $ppn             = $item->ppn;

                    $rawParamInsertDetail = array(
                        'ID_ISO' => $id_iso,
                        'TARIF' => $tarif,
                        'BIAYA' => $biaya,
                        'KETERANGAN' => $ket,
                        'NO_NOTA' => $no_nota,
                        'JML_CONT' => $jml_cont,
                        'HZ' => $hz,
                        'COA' => $coa,
                        'JML_HARI' => 5,
                        'LINE_NUMBER' => $key + 1,
                        'PPN' => $ppn,
                        'NO_NOTA_MTI' => $no_nota_mti,
                    );
                    $paramInsertDetail = generateQuerySimpan($rawParamInsertDetail);
                    $queryInsertDetail    = "INSERT INTO nota_receiving_d $paramInsertDetail";
                    $execInsertDetail = DB::connection('uster')->statement($queryInsertDetail);
                }

                $update_nota = "UPDATE NOTA_RECEIVING SET CETAK_NOTA = '1' WHERE NO_NOTA = '$no_nota'";
                DB::connection('uster')->statement($update_nota);
                $update_nota1 = "UPDATE REQUEST_RECEIVING SET NOTA = 'Y' WHERE NO_REQUEST = '$no_req'";
                DB::connection('uster')->statement($update_nota1);
                $delete_temp = "DELETE from temp_detail_nota WHERE no_request = '$no_req'";
                DB::connection('uster')->statement($delete_temp);

                DB::commit();
                return response()->json([
                    'status' => 'OK',
                    'msg' => 'Berhasil Input Data Proforma',
                    'code' => 200
                ], 200);
            } else {
                throw new Exception('Gagal Menyimpan Proforma', 500);
            }
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'Error',
                'msg' => 'Gagal Menyimpan Proforma Nota Ini',
                'code' => 500,
            ], 500);
        }
    }
}
