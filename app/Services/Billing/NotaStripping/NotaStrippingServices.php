<?php

namespace App\Services\Billing\NotaStripping;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use PDO;

class NotaStrippingServices
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
                $noReqWhere = "AND request_stripping.NO_REQUEST LIKE '%$no_req%'";
            } else if ($request->no_request == null && ($from != null && $to != null)) {
                $dateWhere = "AND request_stripping.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY/MM/DD') AND TO_DATE ( '$to', 'YYYY/MM/DD') ";
            } else if ($no_req != null && ($from != null && $to != null)) {
                $noReqWhere = "AND request_stripping.NO_REQUEST LIKE '%$no_req%'";
                $dateWhere = "AND request_stripping.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY/MM/DD') AND TO_DATE ( '$to', 'YYYY/MM/DD') ";
            }

            $query_list  = "SELECT  request_stripping.PERP_DARI , REQUEST_STRIPPING.TGL_REQUEST, REQUEST_STRIPPING.TGL_APPROVE, request_stripping.NO_DO, request_stripping.NO_BL,request_stripping.NO_REQUEST, emkl.NM_PBM AS PENUMPUKAN_OLEH,  COUNT(container_stripping.NO_CONTAINER) AS JML
                            FROM REQUEST_STRIPPING  left JOIN v_mst_pbm emkl ON REQUEST_STRIPPING.KD_PENUMPUKAN_OLEH = emkl.KD_PBM
                            INNER JOIN container_stripping ON REQUEST_STRIPPING.NO_REQUEST = container_stripping.NO_REQUEST
                            WHERE request_stripping.PERP_DARI IS NULL
                            AND request_stripping.CLOSING = 'CLOSED'
                            AND emkl.KD_CABANG = '05'
                            $noReqWhere $dateWhere
                            GROUP BY  request_stripping.PERP_DARI ,REQUEST_STRIPPING.TGL_REQUEST, request_stripping.NO_REQUEST,  emkl.NM_PBM, request_stripping.TGL_APPROVE,request_stripping.NO_DO, request_stripping.NO_BL
                            ORDER BY REQUEST_STRIPPING.TGL_REQUEST DESC";
        } else {
            $query_list  = "SELECT  request_stripping.PERP_DARI , REQUEST_STRIPPING.TGL_REQUEST, REQUEST_STRIPPING.TGL_APPROVE, request_stripping.NO_DO, request_stripping.NO_BL,request_stripping.NO_REQUEST, emkl.NM_PBM AS PENUMPUKAN_OLEH,  COUNT(container_stripping.NO_CONTAINER) AS JML
                            FROM REQUEST_STRIPPING  left JOIN v_mst_pbm emkl ON REQUEST_STRIPPING.KD_PENUMPUKAN_OLEH = emkl.KD_PBM
                            INNER JOIN container_stripping ON REQUEST_STRIPPING.NO_REQUEST = container_stripping.NO_REQUEST
                            WHERE request_stripping.PERP_DARI IS NULL
                            AND request_stripping.CLOSING = 'CLOSED'
                            AND emkl.KD_CABANG = '05'
                            AND request_stripping.TGL_REQUEST BETWEEN SYSDATE - INTERVAL '15' DAY AND LAST_DAY (SYSDATE)
                            GROUP BY  request_stripping.PERP_DARI ,REQUEST_STRIPPING.TGL_REQUEST, request_stripping.NO_REQUEST,  emkl.NM_PBM, request_stripping.TGL_APPROVE,request_stripping.NO_DO, request_stripping.NO_BL
                            ORDER BY REQUEST_STRIPPING.TGL_REQUEST DESC";
        }

        $query = '(' . $query_list . ') a';
        // $data = array();
        ini_set('max_execution_time', 300);
        $data = DB::connection('uster')->table(DB::raw($query))->select()->get();
        // DB::connection('uster')->table(DB::raw($query))->orderBy('a.tgl_request', 'desc')->chunk(20, function ($chunk) use (&$data) {
        //     foreach ($chunk as $dt) {
        //         $data[] = $dt;
        //     }
        // });

        return $data;
    }

    public function fetchData($no_req)
    {
        $queryNota = "SELECT NO_NOTA FROM nota_stripping WHERE TRIM(NO_REQUEST) = TRIM('$no_req') AND STATUS <> 'BATAL'";
        $dataNota = DB::connection('uster')->selectOne($queryNota);
        $notanya = $dataNota->no_nota;

        // Data Header
        $query = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT  , a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
        CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAME, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/YYYY')
         THEN a.NO_NOTA
         ELSE A.NO_FAKTUR END NO_FAKTUR_--, F_CORPORATE(c.TGL_REQUEST) CORPORATE
                             FROM nota_stripping a, request_stripping c, BILLING_NBS.tb_user mu where
                             a.NO_REQUEST = c.NO_REQUEST
                             AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_stripping d WHERE d.NO_REQUEST = '$no_req' )
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
                        FROM nota_stripping_d a
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

        // Detail Item Nota
        $query_dtl = "SELECT a.JML_HARI,
                             TO_CHAR(SUM(a.TARIF),'999,999,999,999') TARIF,
                             TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                             a.tekstual KETERANGAN,
                             a.HZ,a.JML_CONT,
                              --case a.tekstual when 'PAKET STRIPPING'
                             --THEN (select count(no_container) from container_stripping where no_request = '$no_req')
                             --ELSE
                              --a.JML_CONT
                              --END AS JML_CONT,
                             TO_DATE (a.START_STACK, 'DD/MM/YYYY') START_STACK,
                             TO_DATE (a.END_STACK, 'DD/MM/YYYY') END_STACK,
                             b.SIZE_,
                             b.TYPE_,
                              case a.tekstual when 'PAKET STRIPPING'
                             THEN '-'
                             ELSE
                              b.STATUS
                              END AS STATUS,
                              case a.tekstual when 'PAKET STRIPPING'
                             THEN 10
                             ELSE
                              a.urut
                              END AS urut
                        FROM nota_stripping_d a, iso_code b
                       WHERE     a.KETERANGAN NOT IN ('ADMIN NOTA','MATERAI') AND a.KETERANGAN NOT LIKE '%PENUMPUKAN%'   /**Fauzan modif 27 Agustus 2020 [NOT IN MATERAI]*/
                             AND a.id_iso = b.id_iso
                             AND a.no_nota = '$notanya'
                    GROUP BY a.tekstual, a.jml_hari, a.hz, a.jml_cont, a.start_stack, a.end_stack, b.size_, b.type_,  case a.tekstual when 'PAKET STRIPPING'
                             THEN '-'
                             ELSE
                              b.STATUS
                              END, case a.tekstual when 'PAKET STRIPPING'
                             THEN 10
                             ELSE
                              a.urut
                              END
                   UNION ALL
                   SELECT a.JML_HARI,
                             TO_CHAR(a.TARIF,'999,999,999,999') TARIF,
                             TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA,
                             a.tekstual KETERANGAN,
                             a.HZ,a.JML_CONT,
                              --case a.tekstual when 'PAKET STRIPPING'
                             --THEN (select count(no_container) from container_stripping where no_request = '$no_req')
                             --ELSE
                              --a.JML_CONT
                              --END AS JML_CONT,
                             TO_DATE (a.START_STACK, 'DD/MM/YYYY') START_STACK,
                             TO_DATE (a.END_STACK, 'DD/MM/YYYY') END_STACK,
                             b.SIZE_,
                             b.TYPE_,
                              case a.tekstual when 'PAKET STRIPPING'
                             THEN '-'
                             ELSE
                              b.STATUS
                              END AS STATUS,
                              case a.tekstual when 'PAKET STRIPPING'
                             THEN 10
                             ELSE
                              a.urut
                              END AS urut
                        FROM nota_stripping_d a, iso_code b
                       WHERE     a.KETERANGAN NOT IN ('ADMIN NOTA','MATERAI') AND a.KETERANGAN LIKE '%PENUMPUKAN%'   /**Fauzan modif 27 Agustus 2020 [NOT IN MATERAI]*/
                             AND a.id_iso = b.id_iso
                             AND a.no_nota = '$notanya'
                    ORDER BY urut ASC";

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='DD/MM/YYYY'");
        $res = DB::connection('uster')->select($query_dtl);

        // List Container
        $qcont = "SELECT A.NO_CONTAINER,'FCL' STATUS,B.SIZE_,B.TYPE_ FROM CONTAINER_STRIPPING A, MASTER_CONTAINER B WHERE A.NO_CONTAINER = B.NO_CONTAINER AND A.NO_REQUEST = '$no_req'";
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
            'row2' => $res,
            'data_mtr' => $data_mtr,
            'nama_lengkap' => $nama_lengkap,
            'lunas' => $lunas,
            'listcont' => $listcont
        );
    }

    public function fetchDataRelokMty($no_req)
    {
        // No Nota
        $query = "SELECT NO_NOTA FROM nota_relokasi_mty WHERE TRIM(NO_REQUEST) = TRIM('$no_req') AND STATUS <> 'BATAL'";
        $dataNota = DB::connection('uster')->selectOne($query);
        $notanya = $dataNota->no_nota;

        // Header Nota
        $queryHeader = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT  , a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
       CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAME, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
        THEN a.NO_NOTA
        ELSE A.NO_FAKTUR END NO_FAKTUR_--, F_CORPORATE(c.TGL_REQUEST) CORPORATE
                            FROM nota_relokasi_mty a, request_stripping c, BILLING_NBS.tb_user mu where
                            a.NO_REQUEST = c.NO_REQUEST
                            AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_relokasi_mty d WHERE d.NO_REQUEST = '$no_req' )
                            and c.NO_REQUEST = '$no_req'
                            and a.nipp_user = mu.id(+)";
        $data = DB::connection('uster')->selectOne($queryHeader);
        $nama_lengkap  = $data->name;
        $lunas = $data->lunas;

        if (request('first') == null) {
            $nama_lengkap .= '<br/>' . 'Reprinted by ' . Session::get('NAMA_LENGKAP');
        }

        /**hitung materai Fauzan 28 Agustus 2020*/
        $query_mtr = "SELECT TO_CHAR (a.BIAYA, '999,999,999,999') BEA_MATERAI, a.BIAYA
                        FROM nota_relokasi_mty_d a
                        WHERE a.NO_NOTA = '$notanya' AND a.KETERANGAN ='MATERAI' ";

        $data_mtr = DB::connection('uster')->selectOne($query_mtr);

        if (!empty($data_mtr) && $data_mtr->biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }

        //get no peraturan  25 nov 2020
        if ($lunas == 'YES') {
            $mat = "SELECT * FROM itpk_nota_header WHERE NO_REQUEST='$no_req'";

            $mat3   = DB::connection('uster')->selectOne($mat);
            $no_mat    = $mat3->no_peraturan;
        } else {
            $mat = "SELECT * FROM MASTER_MATERAI WHERE STATUS='Y'";

            $mat3   = DB::connection('uster')->selectOne($mat);
            $no_mat    = $mat3->no_peraturan;
        }

        $query_dtl = "SELECT TO_CHAR (a.START_STACK, 'dd/mm/yyyy') START_STACK,
                     TO_CHAR (a.END_STACK, 'dd/mm/yyyy') END_STACK,
                     a.tekstual keterangan,a.JML_CONT,
                     a.JML_HARI,
                     b.SIZE_,
                     b.TYPE_,
                     case a.tekstual when 'GERAKAN ANTAR BLOK'
                     THEN '-'
                     ELSE
                      b.STATUS
                      END AS STATUS,
                     a.HZ,
                     TO_CHAR(SUM(a.TARIF),'999,999,999,999') TARIF,
                     TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                     case a.tekstual when 'GERAKAN ANTAR BLOK'
                     THEN 10
                     ELSE
                      a.urut
                      END AS urut
                FROM nota_relokasi_mty_d a, iso_code b
               WHERE     a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI') /**Fauzan modif 28 Agustus 2020 [NOT IN MATERAI]*/
                     AND a.ID_ISO = b.ID_ISO(+)
                     AND a.NO_NOTA = (SELECT MAX (d.NO_NOTA)
                                        FROM nota_relokasi_mty d
                                       WHERE d.NO_REQUEST = '$no_req')
                GROUP BY a.tekstual, a.START_STACK, a.END_STACK,  a.JML_CONT,  b.SIZE_,  b.TYPE_, a.HZ, a.JML_HARI, case a.tekstual when 'GERAKAN ANTAR BLOK'
                     THEN '-'
                     ELSE
                      b.STATUS
                      END,
                      case a.tekstual when 'GERAKAN ANTAR BLOK'
                     THEN 10
                     ELSE
                      a.urut
                      END
                ORDER BY urut";

        $res = DB::connection('uster')->select($query_dtl);

        $qcont = "SELECT A.NO_CONTAINER,'FCL' STATUS,B.SIZE_,B.TYPE_ FROM CONTAINER_STRIPPING A, MASTER_CONTAINER B WHERE A.NO_CONTAINER = B.NO_CONTAINER AND A.NO_REQUEST = '$no_req'";
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
            'row2' => $res,
            'data_mtr' => $data_mtr,
            'nama_lengkap' => $nama_lengkap,
            'lunas' => $lunas,
            'listcont' => $listcont
        );
    }

    public function previewNotaRelok($no_req, $koreksi)
    {
        // Data Header
        $query_nota    = "SELECT c.NM_PBM AS EMKL,
                          c.NO_NPWP_PBM AS NPWP,
                          c.ALMT_PBM AS ALAMAT,
                          b.NOTA_PNKN,
                          c.NO_ACCOUNT_PBM,
                          TO_CHAR(b.TGL_REQUEST,'DD-MM-RRRR') TGL_REQUEST
													-- F_CORPORATE(b.TGL_REQUEST) CORPORATE
                   FROM REQUEST_STRIPPING@DBCLOUD_LINK b INNER JOIN
                            V_MST_PBM@DBCLOUD_LINK c ON b.KD_CONSIGNEE = c.KD_PBM
                   WHERE b.NO_REQUEST = '$no_req'";

        $row_nota    = DB::connection('uster_dev')->selectOne($query_nota);

        DB::setDateFormat('DD-MON-YYYY');
        $query_tgl    = "SELECT TO_CHAR(TGL_REQUEST,'YYYY-MM-DD') TGL_REQUEST FROM request_stripping@DBCLOUD_LINK
                             WHERE NO_REQUEST = '$no_req'
                            ";
        $tgl_req    = DB::connection('uster_dev')->selectOne($query_tgl);
        $tgl_re     = Carbon::parse($tgl_req->tgl_request)->format('Y/m/d');

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
        $sql_xpi = "DECLARE
                        id_nota NUMBER;
                        tgl_req DATE;
                        no_request VARCHAR2(100);
                        jenis VARCHAR2 (100);
                        err_msg VARCHAR2(100);
                        BEGIN
                                id_nota := 2;
                                tgl_req := '$tgl_re';
                                no_request := '$no_req';
                                err_msg := 'NULL';
                                jenis := 'relokasimty';
                                pack_get_nota_stripping.create_detail_nota(id_nota,tgl_req,no_request,jenis, err_msg);
                        END;";
        $exec = DB::connection('uster')->statement($sql_xpi);

        $queryJum          = "SELECT COUNT(NO_CONTAINER) JUMLAH FROM container_stripping@DBCLOUD_LINK WHERE no_request = '$no_req'";
        $dataJum         = DB::connection('uster_dev')->selectOne($queryJum);
        $jumlah_cont  = $dataJum->jumlah;

        //tarif pass
        $pass          = "SELECT TO_CHAR(($jumlah_cont * a.TARIF), '999,999,999,999') PASS, ($jumlah_cont * a.TARIF) TARIF
                                FROM master_tarif@DBCLOUD_LINK a, group_tarif@DBCLOUD_LINK b
                            WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF
                                    AND TO_DATE ('$tgl_re', 'YYYY/MM/DD') BETWEEN b.START_PERIOD
                                    AND b.END_PERIOD
                                    AND a.ID_ISO = 'PASS'";

        DB::connection('uster_dev')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
        $row_pass     = DB::connection('uster_dev')->selectOne($pass);
        $tarif_pass   = $row_pass->tarif;

        // Detail Nota
        $detail_nota  = " SELECT a.JML_HARI,
                             TO_CHAR(SUM(a.TARIF),'999,999,999,999') TARIF,
                             TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                             a.tekstual KETERANGAN,
                             a.HZ,a.JML_CONT,
                              --case a.tekstual when 'PAKET STRIPPING'
                             --THEN (select count(no_container) from container_stripping where no_request = '$no_req')
                             --ELSE
                              --a.JML_CONT
                              --END AS JML_CONT,
                             TO_DATE (a.START_STACK, 'dd/mm/rrrr') START_STACK,
                             TO_DATE (a.END_STACK, 'dd/mm/rrrr') END_STACK,
                             b.SIZE_,
                             b.TYPE_,
                              case a.tekstual when 'GERAKAN ANTAR BLOK'
                             THEN '-'
                             ELSE
                              b.STATUS
                              END AS STATUS,
                              case a.tekstual when 'GERAKAN ANTAR BLOK'
                             THEN 10
                             ELSE
                              a.urut
                              END AS urut
                        FROM temp_detail_nota_i@DBCLOUD_LINK a, iso_code@DBCLOUD_LINK b
                       WHERE     a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI') /**end modify fauzan 28 AUG 2020*/
                             AND a.id_iso = b.id_iso
                             AND a.no_request = '$no_req'
                    GROUP BY a.tekstual, a.jml_hari, a.hz, a.jml_cont, a.start_stack, a.end_stack, b.size_, b.type_,  case a.tekstual when 'GERAKAN ANTAR BLOK'
                             THEN '-'
                             ELSE
                              b.STATUS
                              END, case a.tekstual when 'GERAKAN ANTAR BLOK'
                             THEN 10
                             ELSE
                              a.urut
                              END
                    ORDER BY urut ASC";
        $row_detail   = DB::connection('uster_dev')->select($detail_nota);

        $queryTotal          = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN FROM temp_detail_nota_i@DBCLOUD_LINK WHERE  no_request = '$no_req' AND KETERANGAN NOT IN ('MATERAI')";
        /**end modify fauzan 28 aug 2020 "AND KETERANGAN NOT IN ('MATERAI')"*/
        $dataTotal       = DB::connection('uster_dev')->selectOne($queryTotal);
        $total_       = $dataTotal->total;
        $ppn             = $dataTotal->ppn;

        //Discount
        $discount = 0;
        $query_discount        = "SELECT TO_CHAR($discount , '999,999,999,999') AS DISCOUNT FROM DUAL";
        $row_discount        = DB::connection('uster_dev')->selectOne($query_discount);

        //Biaya Administrasi
        $query_adm        = "SELECT TO_CHAR(TARIF , '999,999,999,999') AS ADM, TARIF FROM temp_detail_nota_i@DBCLOUD_LINK WHERE KETERANGAN = 'ADMIN NOTA' AND NO_REQUEST = '$no_req'";
        $row_adm        = DB::connection('uster_dev')->selectOne($query_adm);
        $adm             = $row_adm->tarif;

        //Menghitung Total dasar pengenaan pajak
        $query_tot        = "SELECT TO_CHAR('$total_' , '999,999,999,999') AS TOTAL_ALL FROM DUAL";
        $row_tot        = DB::connection('uster_dev')->selectOne($query_tot);

        //Menghitung Jumlah PPN
        //$ppn = $total_/10;
        $query_ppn        = "SELECT TO_CHAR('$ppn' , '999,999,999,999') AS PPN FROM DUAL";
        $row_ppn        = DB::connection('uster_dev')->selectOne($query_ppn);

        //Menghitung Bea Materai gagat modif 09 februari 2020
        $sql_mtr          = "SELECT BIAYA AS BEA_MATERAI FROM TEMP_DETAIL_NOTA_I@DBCLOUD_LINK WHERE no_request = '$no_req' AND KETERANGAN='MATERAI'";
        $row_mtr         = DB::connection('uster_dev')->selectOne($sql_mtr);
        $bea_materai = 0;
        if (!empty($row_mtr) && (int)$row_mtr->bea_materai > 0) {
            $bea_materai = $row_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        /**end modify gagat 09 feb 2020*/
        $query_materai        = "SELECT TO_CHAR('$bea_materai' , '999,999,999,999') AS MATERAI FROM DUAL";
        $row_materai        = DB::connection('uster_dev')->selectOne($query_materai);

        //Menghitung Jumlah dibayar
        $total_bayar        = $total_ + $ppn + $bea_materai; //+ $tarif_pass; /**end modify fauzan 28 aug 2020 "$bea_materai"*/
        $query_bayar        = "SELECT TO_CHAR('$total_bayar' , '999,999,999,999') AS TOTAL_BAYAR FROM DUAL";
        $row_bayar          = DB::connection('uster_dev')->selectOne($query_bayar);

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI@DBCLOUD_LINK WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster_dev')->selectOne($pegawai);

        return response()->json([
            'row_nota' => $row_nota,
            'row_detail' => $row_detail,
            'tgl_req' => $tgl_re,
            'row_pass' => $tarif_pass,
            'pegawai' => $nama_peg,
            'row_bayar' => $row_bayar->total_bayar,
            'row_materai' => $row_materai->materai,
            'row_discount' => $discount,
            'row_ppn' => $row_ppn,
            'row_adm' => $adm,
            'row_tot' => $row_tot->total_all,
            'bea_materai' => $bea_materai,
            'no_req' => $no_req,
            'koreksi' => $koreksi,
        ]);
    }

    public function previewNota($no_req, $koreksi)
    {
        // Data Header
        $query_nota    = "SELECT c.NM_PBM AS EMKL,
                            c.NO_NPWP_PBM AS NPWP,
                            c.ALMT_PBM AS ALAMAT,
                            b.NOTA,
                            c.NO_ACCOUNT_PBM,
                            TO_CHAR(b.TGL_REQUEST,'DD-MM-RRRR') TGL_REQUEST
                            -- F_CORPORATE(b.TGL_REQUEST) CORPORATE
                        FROM REQUEST_STRIPPING@DBCLOUD_LINK b INNER JOIN
                                    V_MST_PBM@DBCLOUD_LINK c ON b.KD_CONSIGNEE = c.KD_PBM
                        WHERE b.NO_REQUEST = '$no_req'";

        $row_nota    = DB::connection('uster_dev')->selectOne($query_nota);
        $st_nota = $row_nota->nota;
        $st_nota = "T";
        $kd_pbm     = $row_nota->no_account_pbm;
        $req_tgl     = $row_nota->tgl_request;
        $display     = 1;
        if ($st_nota == "Y") {
            return response()->json([
                'st_nota' => 'Y'
            ]);
        } else {
            $query_tgl    = "SELECT TO_CHAR(TGL_REQUEST,'YYYY-MM-DD') TGL_REQUEST FROM request_stripping@DBCLOUD_LINK WHERE NO_REQUEST = '$no_req'";
            $tgl_req    = DB::connection('uster_dev')->selectOne($query_tgl);
            $tgl_re     = Carbon::parse($tgl_req->tgl_request)->format('Y/m/d');

            DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
            $sql_xpi = "DECLARE
                            id_nota NUMBER;
                            tgl_req DATE;
                            no_request VARCHAR2(100);
                            jenis VARCHAR2 (100);
                            err_msg VARCHAR2(100);
                            BEGIN
                                    id_nota := 2;
                                    tgl_req := '$tgl_re';
                                    no_request := '$no_req';
                                    err_msg := 'NULL';
                                    jenis := 'stripping';
                                    pack_get_nota_stripping.create_detail_nota(id_nota,tgl_req,no_request,jenis, err_msg);
                            END;";

            $exec = DB::connection('uster')->statement($sql_xpi);

            $jum          = "SELECT COUNT(NO_CONTAINER) JUMLAH FROM container_stripping@DBCLOUD_LINK WHERE no_request = '$no_req'";
            $dataJum         = DB::connection('uster_dev')->selectOne($jum);
            $jumlah_cont  = $dataJum->jumlah;

            //tarif pass
            $pass          = "SELECT TO_CHAR(($jumlah_cont * a.TARIF), '999,999,999,999') PASS, ($jumlah_cont * a.TARIF) TARIF
                                    FROM master_tarif@DBCLOUD_LINK a, group_tarif@DBCLOUD_LINK b
                                WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF
                                        AND TO_DATE ('$tgl_re', 'YYYY/MM/DD') BETWEEN b.START_PERIOD
                                                                                    AND b.END_PERIOD
                                        AND a.ID_ISO = 'PASS'";

            DB::connection('uster_dev')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
            $row_pass     = DB::connection('uster_dev')->selectOne($pass);
            $tarif_pass   = $row_pass->tarif;

            // Detail Nota
            $detail_nota  = " SELECT a.JML_HARI,
                                        TO_CHAR(SUM(a.TARIF),'999,999,999,999') TARIF,
                                        TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                                        a.tekstual KETERANGAN,
                                        a.HZ,a.JML_CONT,
                                        --case a.tekstual when 'PAKET STRIPPING'
                                        --THEN (select count(no_container) from container_stripping where no_request = '$no_req')
                                        --ELSE
                                        --a.JML_CONT
                                        --END AS JML_CONT,
                                        TO_DATE (a.START_STACK, 'YYYY/MM/DD') START_STACK,
                                        TO_DATE (a.END_STACK, 'YYYY/MM/DD') END_STACK,
                                        b.SIZE_,
                                        b.TYPE_,
                                        case a.tekstual when 'PAKET STRIPPING'
                                        THEN '-'
                                        ELSE
                                        b.STATUS
                                        END AS STATUS,
                                        case a.tekstual when 'PAKET STRIPPING'
                                        THEN 10
                                        ELSE
                                        a.urut
                                        END AS urut
                                    FROM temp_detail_nota@DBCLOUD_LINK a, iso_code@DBCLOUD_LINK b
                                WHERE     a.KETERANGAN <> 'ADMIN NOTA' AND a.KETERANGAN NOT LIKE '%PENUMPUKAN%'
                                        AND a.id_iso = b.id_iso
                                        AND a.no_request = '$no_req'
                                        AND a.RELOK = '1'
                                GROUP BY a.tekstual, a.jml_hari, a.hz, a.jml_cont, a.start_stack, a.end_stack, b.size_, b.type_,  case a.tekstual when 'PAKET STRIPPING'
                                        THEN '-'
                                        ELSE
                                        b.STATUS
                                        END, case a.tekstual when 'PAKET STRIPPING'
                                        THEN 10
                                        ELSE
                                        a.urut
                                        END
                            UNION ALL
                            SELECT a.JML_HARI,
                                        TO_CHAR(a.TARIF,'999,999,999,999') TARIF,
                                        TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA,
                                        a.tekstual KETERANGAN,
                                        a.HZ,a.JML_CONT,
                                        --case a.tekstual when 'PAKET STRIPPING'
                                        --THEN (select count(no_container) from container_stripping where no_request = '$no_req')
                                        --ELSE
                                        --a.JML_CONT
                                        --END AS JML_CONT,
                                        TO_DATE (a.START_STACK, 'YYYY/MM/DD') START_STACK,
                                        TO_DATE (a.END_STACK, 'YYYY/MM/DD') END_STACK,
                                        b.SIZE_,
                                        b.TYPE_,
                                        case a.tekstual when 'PAKET STRIPPING'
                                        THEN '-'
                                        ELSE
                                        b.STATUS
                                        END AS STATUS,
                                        case a.tekstual when 'PAKET STRIPPING'
                                        THEN 10
                                        ELSE
                                        a.urut
                                        END AS urut
                                    FROM temp_detail_nota@DBCLOUD_LINK a, iso_code@DBCLOUD_LINK b
                                WHERE     a.KETERANGAN <> 'ADMIN NOTA' AND a.KETERANGAN LIKE '%PENUMPUKAN%'
                                        AND a.id_iso = b.id_iso
                                        AND a.no_request = '$no_req'
                                        AND a.RELOK = '1'
                                ORDER BY urut ASC";
            $row_detail   = DB::connection('uster_dev')->select($detail_nota);

            $queryTotal          = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN FROM temp_detail_nota@DBCLOUD_LINK WHERE  no_request = '$no_req' and relok = '1'";
            $total2       = DB::connection('uster_dev')->selectOne($queryTotal);

            $total_       = $total2->total;
            $ppn             = $total2->ppn;

            //Discount
            $discount = 0;
            $query_discount        = "SELECT TO_CHAR($discount , '999,999,999,999') AS DISCOUNT FROM DUAL";
            $row_discount        = DB::connection('uster_dev')->selectOne($query_discount);

            //Biaya Administrasi
            $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF@DBCLOUD_LINK a, GROUP_TARIF@DBCLOUD_LINK b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
            $row_adm        = DB::connection('uster_dev')->selectOne($query_adm);
            $adm             = $row_adm->tarif;

            //Menghitung Total dasar pengenaan pajak
            $query_tot        = "SELECT TO_CHAR('$total_' , '999,999,999,999') AS TOTAL_ALL FROM DUAL";
            $row_tot        = DB::connection('uster_dev')->selectOne($query_tot);

            //Menghitung Jumlah PPN
            $query_ppn        = "SELECT TO_CHAR('$ppn' , '999,999,999,999') AS PPN FROM DUAL";
            $row_ppn        = DB::connection('uster_dev')->selectOne($query_ppn);

            //Menghitung Bea Materai gagat modif 09 februari 2020
            $sql_mtr          = "SELECT BIAYA AS BEA_MATERAI FROM TEMP_DETAIL_NOTA@DBCLOUD_LINK WHERE no_request = '$no_req' AND KETERANGAN='MATERAI'";
            $row_mtr         = DB::connection('uster_dev')->selectOne($sql_mtr);

            if (!empty($row_mtr) && $row_mtr->bea_materai > 0) {
                $bea_materai = $row_mtr->bea_materai;
            } else {
                $bea_materai = 0;
            }
            /**end modify gagat 09 feb 2020*/

            $query_materai        = "SELECT TO_CHAR('$bea_materai' , '999,999,999,999') AS MATERAI FROM DUAL";
            $row_materai        = DB::connection('uster_dev')->selectOne($query_materai);

            //Menghitung Jumlah dibayar
            $total_bayar        = $total_ + $ppn + $bea_materai; //+ $tarif_pass;	/**Fauzan modif 28 AUG 2020 "+ $bea_materai"*/
            $query_bayar        = "SELECT TO_CHAR('$total_bayar' , '999,999,999,999') AS TOTAL_BAYAR FROM DUAL";
            $row_bayar          = DB::connection('uster_dev')->selectOne($query_bayar);

            $pegawai    = "SELECT * FROM MASTER_PEGAWAI@DBCLOUD_LINK WHERE STATUS = 'AKTIF'";
            $nama_peg    = DB::connection('uster_dev')->selectOne($pegawai);

            return response()->json([
                'row_nota' => $row_nota,
                'row_detail' => $row_detail,
                'tgl_req' => $tgl_re,
                'row_pass' => $tarif_pass,
                'pegawai' => $nama_peg,
                'row_bayar' => $row_bayar->total_bayar,
                'row_materai' => $row_materai->materai,
                'row_discount' => $discount,
                'row_ppn' => $row_ppn,
                'row_adm' => $adm,
                'row_tot' => $row_tot->total_all,
                'bea_materai' => $bea_materai,
                'no_req' => $no_req,
                'koreksi' => $koreksi,
            ]);
        }
    }

    public function insertProformaRelokMty($no_req, $koreksi)
    {
        //create no nota
        $query_cek    = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA,10,15))+1),6,0), '000001') JUM_,
                            TO_CHAR(SYSDATE, 'MM') AS MONTH,
                            TO_CHAR(SYSDATE, 'YY') AS YEAR
                            FROM NOTA_RELOKASI_MTY
                            WHERE NOTA_RELOKASI_MTY.TGL_NOTA BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)";

        $lastCounter        = DB::connection('uster')->selectOne($query_cek);
        $jum        = $lastCounter->jum_;
        $month        = $lastCounter->month;
        $year        = $lastCounter->year;

        $no_nota_relok    = "1005" . $month . $year . $jum;

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

        $query_master    = "SELECT TO_CHAR(a.TGL_REQUEST, 'YYYY/MM/DD') as TGL_REQUEST, b.KD_PBM, b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM, COUNT(d.NO_CONTAINER) JUMLAH
                            FROM request_stripping a, v_mst_pbm b, container_stripping d
                            WHERE a.KD_CONSIGNEE = b.KD_PBM
                            AND b.KD_CABANG = '05'
                            AND a.NO_REQUEST = d.NO_REQUEST
                            AND a.NO_REQUEST = '$no_req'
                            GROUP BY  a.TGL_REQUEST, b.KD_PBM, b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM";
        //echo $query_master;die;
        $master        = DB::connection('uster')->selectOne($query_master);
        $kd_pbm        = $master->kd_pbm;
        $nm_pbm        = $master->nm_pbm;
        $almt_pbm    = $master->almt_pbm;
        $npwp       = $master->no_npwp_pbm;
        $jumlah_cont       = $master->jumlah;
        $tgl_re        = $master->tgl_request;

        //tarif pass
        $pass          = "SELECT TO_CHAR(($jumlah_cont * a.TARIF), '999,999,999,999') PASS, ($jumlah_cont * a.TARIF) TARIF
                            FROM master_tarif a, group_tarif b
                            WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF
                                    AND TO_DATE ('$tgl_re', 'YYYY/MM/DD') BETWEEN b.START_PERIOD
                                                                                AND b.END_PERIOD
                                    AND a.ID_ISO = 'PASS'";

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
        $row_pass     = DB::connection('uster')->selectOne($pass);
        $tarif_pass   = $row_pass->tarif;

        $total_        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN,(SUM(BIAYA)+SUM(PPN)) TOTAL_TAGIHAN FROM temp_detail_nota_i WHERE no_request = '$no_req'";
        $total2         = DB::connection('uster')->selectOne($total_);
        $total_         = $total2->total;
        $ppn             = $total2->ppn;
        $tagihan         = $total2->total_tagihan;

        $total_r        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN, SUM(DISKON) DISKON, ((SUM(BIAYA)+SUM(PPN))-SUM(DISKON)) TOTAL_TAGIHAN FROM temp_detail_nota_i WHERE no_request = '$no_req' AND KETERANGAN NOT IN ('MATERAI')";
        /**Fauzan modif 01 SEP 2020 "AND KETERANGAN NOT IN ('MATERAI')"*/
        //echo $total_;die;
        $total2_r        = DB::connection('uster')->selectOne($total_r);
        $total_relok         = $total2_r->total;
        $ppn_relok        = $total2_r->ppn;
        $total_diskon        = $total2_r->diskon;

        $query_adm        = "SELECT TO_CHAR(TARIF , '999,999,999,999') AS ADM, TARIF FROM temp_detail_nota_i WHERE KETERANGAN = 'ADMIN NOTA' AND NO_REQUEST = '$no_req'";
        $row_adm        = DB::connection('uster')->selectOne($query_adm);
        $adm             = $row_adm->tarif;

        /*Fauzan add materai 01 September 2020*/
        $query_materai        = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota_i WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";
        $row_materai        = DB::connection('uster')->selectOne($query_materai);
        $materai            = $row_materai->bea_materai;
        $tagihan_relok        = $total2_r->total_tagihan + $materai;

        if ($koreksi <> 'Y') {
            $status_nota = 'NEW';
            $nota_lama = '';
        } else {
            $status_nota = 'KOREKSI';
            $faktur         = "SELECT NO_NOTA, NO_FAKTUR, KD_EMKL FROM NOTA_RELOKASI_MTY WHERE NO_REQUEST = '$no_req' AND NO_NOTA =(SELECT MAX(NO_NOTA) FROM NOTA_RELOKASI_MTY WHERE NO_REQUEST = '$no_req')";
            $data_faktur     = DB::connection('uster')->selectOne($faktur);
            $nota_lama    = $data_faktur->no_faktur;
        }

        $rawParamInsertNota = array(
            'NO_NOTA' => $no_nota_relok,
            'TAGIHAN' => $total_relok,
            'PPN' => $ppn_relok,
            'TOTAL_TAGIHAN' => $tagihan_relok,
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
            'TOTAL_DISKON' => $total_diskon,
            'NOTA_LAMA' => $nota_lama,
            'NO_NOTA_MTI' => $no_nota_mti,
        );

        DB::beginTransaction();
        try {
            $paramInsertNota = generateQuerySimpan($rawParamInsertNota);
            $queryInsertNota = "INSERT INTO NOTA_RELOKASI_MTY $paramInsertNota";
            $execInsertNota = DB::connection('uster')->statement($queryInsertNota);

            if ($execInsertNota) {
                //UPDATE COUNTER MTI DAN PENAMBAHAN FIELD NO_NOTA_MTI DI HEADER DAN DETAIL
                //firman 20 agustus 2020
                $query_mti = "INSERT INTO MTI_COUNTER_NOTA(NO_NOTA_MTI,TAHUN,NO_REQUEST)
                                VALUES('$no_nota_mti',TO_CHAR(SYSDATE,'YYYY'),'$no_req')";
                $execQueryMti = DB::connection('uster')->statement($query_mti);

                $query_detail    = "SELECT ID_ISO,TARIF,BIAYA,KETERANGAN,JML_CONT,HZ,TO_CHAR(START_STACK,'mm/dd/rrrr') START_STACK,TO_CHAR(END_STACK,'mm/dd/rrrr') END_STACK, JML_HARI, COA, PPN,URUT,TEKSTUAL,RELOK,DISKON  FROM temp_detail_nota_i WHERE no_request = '$no_req' ";
                $row = DB::connection('uster')->select($query_detail);

                foreach ($row as $key => $item) {
                    $id_iso         = $item->id_iso;
                    $tarif          = $item->tarif;
                    $biaya          = $item->biaya;
                    $ket            = $item->keterangan;
                    $jml_cont          = $item->jml_cont;
                    $hz             = $item->hz;
                    $start          = $item->start_stack;
                    $end            = $item->end_stack;
                    $jml            = $item->jml_hari;
                    $coa             = $item->coa;
                    $ppn             = $item->ppn;
                    $urut           = $item->urut;
                    $tekstual    = $item->tekstual;
                    $relok    = $item->relok;
                    $diskon    = $item->diskon;

                    $rawParamInsertDetail = array(
                        'ID_ISO' => $id_iso,
                        'TARIF' => $tarif,
                        'BIAYA' => $biaya,
                        'KETERANGAN' => $ket,
                        'NO_NOTA' => $no_nota_relok,
                        'JML_CONT' => $jml_cont,
                        'HZ' => $hz,
                        'START_STACK' => "TO_DATE('$start','mm/dd/rrrr')@ORA",
                        'END_STACK' => "TO_DATE('$end','mm/dd/rrrr')@ORA",
                        'JML_HARI' => $jml,
                        'COA' => $coa,
                        'LINE_NUMBER' => $key + 1,
                        'PPN' => $ppn,
                        'URUT' => $urut,
                        'TEKSTUAL' => $tekstual,
                        'DISKON' => $diskon,
                        'NO_NOTA_MTI' => $no_nota_mti,
                    );
                    $paramInsertDetail = generateQuerySimpan($rawParamInsertDetail);
                    $queryInsertDetail    = "INSERT INTO nota_relokasi_mty_d $paramInsertDetail";
                    $execInsertDetail = DB::connection('uster')->statement($queryInsertDetail);
                }

                $update_nota = "UPDATE NOTA_RELOKASI_MTY SET CETAK_NOTA = '1' WHERE NO_NOTA = '$no_nota_relok'";
                $update_req = "UPDATE REQUEST_STRIPPING SET NOTA_PNKN = 'Y' WHERE NO_REQUEST = '$no_req'";

                DB::connection('uster')->statement($update_nota);
                DB::connection('uster')->statement($update_req);
                DB::connection('uster')->statement("UPDATE PLAN_CONTAINER_STRIPPING SET AKTIF = 'T' WHERE NO_REQUEST = REPLACE ('$no_req','S', 'P')");

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

    public function insertProformaStripping($no_req, $koreksi)
    {
        //create no nota
        $query_cek    = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA,10,15))+1),6,0), '000001') JUM_,
                              TO_CHAR(SYSDATE, 'MM') AS MONTH,
                              TO_CHAR(SYSDATE, 'YY') AS YEAR
                        FROM NOTA_STRIPPING
                       WHERE NOTA_STRIPPING.TGL_NOTA BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)";

        $dataJum        = DB::connection('uster')->selectOne($query_cek);
        $jum        = $dataJum->jum_;
        $month        = $dataJum->month;
        $year        = $dataJum->year;
        $no_nota    = "0305" . $month . $year . $jum;

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
        $query_master    = "SELECT TO_CHAR(a.TGL_REQUEST, 'YYYY/MM/DD') as TGL_REQUEST, b.KD_PBM, b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM, COUNT(d.NO_CONTAINER) JUMLAH
                            FROM request_stripping a, v_mst_pbm b, container_stripping d
                            WHERE a.KD_CONSIGNEE = b.KD_PBM
                            AND b.KD_CABANG = '05'
                            AND a.NO_REQUEST = d.NO_REQUEST
                            AND a.NO_REQUEST = '$no_req'
                            GROUP BY  a.TGL_REQUEST, b.KD_PBM, b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM";

        $master        = DB::connection('uster')->selectOne($query_master);
        $kd_pbm        = $master->kd_pbm;
        $nm_pbm        = $master->nm_pbm;
        $almt_pbm    = $master->almt_pbm;
        $npwp       = $master->no_npwp_pbm;
        $jumlah_cont       = $master->jumlah;
        $tgl_re        = $master->tgl_request;

        //tarif pass
        $pass          = "SELECT TO_CHAR(($jumlah_cont * a.TARIF), '999,999,999,999') PASS, ($jumlah_cont * a.TARIF) TARIF
						  FROM master_tarif a, group_tarif b
						 WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF
							   AND TO_DATE ('$tgl_re', 'YYYY/MM/DD') BETWEEN b.START_PERIOD
								AND b.END_PERIOD
							   AND a.ID_ISO = 'PASS'";

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
        $row_pass     = DB::connection('uster')->selectOne($pass);
        $tarif_pass   = $row_pass->tarif;

        $total_        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN,(SUM(BIAYA)+SUM(PPN)) TOTAL_TAGIHAN FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN NOT IN ('MATERAI')";
        /**Fauzan modif 01 SEP 2020 "AND KETERANGAN NOT IN ('MATERAI')"*/
        $total2         = DB::connection('uster')->selectOne($total_);
        $total_         = $total2->total;
        $ppn             = $total2->ppn;

        $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
        $row_adm        = DB::connection('uster')->selectOne($query_adm);
        $adm             = $row_adm->tarif;

        /*Fauzan add materai 01 September 2020*/
        $query_materai        = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";
        $row_materai        = DB::connection('uster')->selectOne($query_materai);
        $materai            = $row_materai->bea_materai;
        $tagihan         = $total2->total_tagihan + $materai;
        /**Fauzan modif 26 AUG 2020 "+ $materai"*/

        if ($koreksi <> 'Y') {
            $status_nota = 'NEW';
            $nota_lama = '';
        } else {
            $status_nota = 'KOREKSI';
            $faktur         = "SELECT NO_NOTA, NO_FAKTUR, KD_EMKL FROM NOTA_STRIPPING WHERE NO_REQUEST = '$no_req' AND NO_NOTA =(SELECT MAX(NO_NOTA) FROM NOTA_STRIPPING WHERE NO_REQUEST = '$no_req')";
            $dataFaktur     = DB::connection('uster')->selectOne($faktur);
            $nota_lama    = $dataFaktur->no_faktur;
        }

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
            'NO_NOTA_MTI' => $no_nota_mti,
        );

        DB::beginTransaction();
        try {
            $paramInsertNota = generateQuerySimpan($rawParamInsertNota);
            $queryInsertNota = "INSERT INTO NOTA_STRIPPING $paramInsertNota";
            $execInsertNota = DB::connection('uster')->statement($queryInsertNota);

            if ($execInsertNota) {
                //UPDATE COUNTER MTI DAN PENAMBAHAN FIELD NO_NOTA_MTI DI HEADER DAN DETAIL
                //firman 20 agustus 2020
                $query_mti = "INSERT INTO MTI_COUNTER_NOTA(NO_NOTA_MTI,TAHUN,NO_REQUEST)
                                VALUES('$no_nota_mti',TO_CHAR(SYSDATE,'YYYY'),'$no_req')";
                $execQueryMti = DB::connection('uster')->statement($query_mti);

                $query_detail    = "SELECT ID_ISO,TARIF,BIAYA,KETERANGAN,JML_CONT,HZ,TO_CHAR(START_STACK,'mm/dd/rrrr') START_STACK,TO_CHAR(END_STACK,'mm/dd/rrrr') END_STACK, JML_HARI, COA, PPN,URUT,TEKSTUAL,RELOK,DISKON  FROM temp_detail_nota WHERE no_request = '$no_req' ";
                $row = DB::connection('uster')->select($query_detail);

                foreach ($row as $key => $item) {
                    $id_iso         = $item->id_iso;
                    $tarif          = $item->tarif;
                    $biaya          = $item->biaya;
                    $ket            = $item->keterangan;
                    $jml_cont          = $item->jml_cont;
                    $hz             = $item->hz;
                    $start          = $item->start_stack;
                    $end            = $item->end_stack;
                    $jml            = $item->jml_hari;
                    $coa             = $item->coa;
                    $ppn             = $item->ppn;
                    $urut           = $item->urut;
                    $tekstual    = $item->tekstual;

                    $rawParamInsertDetail = array(
                        'ID_ISO' => $id_iso,
                        'TARIF' => $tarif,
                        'BIAYA' => $biaya,
                        'KETERANGAN' => $ket,
                        'NO_NOTA' => $no_nota,
                        'JML_CONT' => $jml_cont,
                        'HZ' => $hz,
                        'START_STACK' => $start != null ? "TO_DATE('$start','mm/dd/rrrr')@ORA" : null,
                        'END_STACK' => $end != null ? "TO_DATE('$end','mm/dd/rrrr')@ORA" : null,
                        'JML_HARI' => $jml,
                        'COA' => $coa,
                        'LINE_NUMBER' => $key + 1,
                        'PPN' => $ppn,
                        'URUT' => $urut,
                        'TEKSTUAL' => $tekstual,
                        'NO_NOTA_MTI' => $no_nota_mti,
                    );

                    $paramInsertDetail = generateQuerySimpan($rawParamInsertDetail);
                    $queryInsertDetail    = "INSERT INTO nota_stripping_d $paramInsertDetail";
                    try {
                        $execInsertDetail = DB::connection('uster')->statement($queryInsertDetail);
                        if (!$execInsertDetail) {
                            // Log or inspect the error
                            $error = DB::connection('uster')->getPdo()->errorInfo();
                            Log::error("Error inserting data into nota_stripping_d: " . implode(', ', $error));
                        }
                    } catch (Exception $e) {
                        // Handle the exception
                        Log::error("Exception during insert: " . $e->getMessage());
                    }
                }

                $update_nota = "UPDATE NOTA_STRIPPING SET CETAK_NOTA = '1' WHERE NO_NOTA = '$no_nota'";
                $update_req = "UPDATE REQUEST_STRIPPING SET NOTA = 'Y' WHERE NO_REQUEST = '$no_req'";
                $delete_temp = "DELETE from temp_detail_nota WHERE no_request = '$no_req'";

                DB::connection('uster')->statement($update_nota);
                DB::connection('uster')->statement($update_req);
                DB::connection('uster')->statement($delete_temp);
                DB::connection('uster')->statement("UPDATE PLAN_CONTAINER_STRIPPING SET AKTIF = 'T' WHERE NO_REQUEST = REPLACE ('$no_req','S', 'P')");

                DB::commit();
                return response()->json([
                    'status' => 'OK',
                    'msg' => 'Berhasil Input Data Proforma',
                    'code' => 200
                ], 200);
            } else {
                DB::rollback();
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

    public function recalculateStripping($no_req, $no_nota)
    {
        try {
            $query = "BEGIN
                PACK_RECALC_NOTA.recalc_stripping('$no_req', '$no_nota');
                  END;";
            DB::connection('uster')->statement($query);

            return response()->json([
                'status' => 'OK',
                'msg' => 'Recalculate Stripping Berhasil',
                'code' => 200
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Gagal melakukan recalculate stripping',
                'code' => 500,
            ], 500);
        }
    }

    public function recalculateStrippingPnk($no_req)
    {
        try {
            $ceknota = "SELECT NO_NOTA FROM nota_relokasi_mty WHERE NO_REQUEST = '$no_req' AND STATUS <> 'BATAL'";
            $rnota = DB::connection('uster')->selectOne($ceknota);
            $no_nota = $rnota ? $rnota->no_nota : null;
            $query = "BEGIN
                PACK_RECALC_NOTA.recalc_relokasimty('$no_req', '$no_nota');
                  END;";
            DB::connection('uster')->statement($query);

            return response()->json([
                'status' => 'OK',
                'msg' => 'Recalculate Stripping PNK Berhasil',
                'code' => 200
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'Error',
                'msg' => 'Gagal melakukan recalculate stripping PNK',
                'code' => 500,
            ], 500);
        }
    }
}
