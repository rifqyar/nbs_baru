<?php

namespace App\Services\Billing\Stuffing;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Exception;


class NotaStuffingExt
{

    function ListNota($request)
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




        // Jik 'no_re$no_req' ada, tetapi 'from' dan 'to' kosong
        if (isset($from) || isset($to) || isset($no_req)) {
            if ((isset($no_req)) && ($from == NULL) && ($to == NULL)) {
                $filter  = "NO_REQUEST LIKE '%$no_req%' AND";
            } else if ((isset($from)) && (isset($to)) && ($no_req == NULL)) {
                $filter  = "c.tgl_request BETWEEN TO_DATE ('$from','dd-mm-rrrr') AND TO_DATE ('$to','dd-mm-rrrr') AND";
            } else if ((isset($from)) && (isset($to)) && (isset($no_req))) {
                $filter  = "NO_REQUEST LIKE '%$no_req%'  AND c.tgl_request BETWEEN TO_DATE ('$from','dd-mm-rrrr') AND TO_DATE ('$to','dd-mm-rrrr') AND";
            }
        } else {
            $filter = '1=1 AND';
        }

        $query_list     = "SELECT
                *
            FROM
                (
                SELECT
                    *
                FROM
                    (
                    SELECT
                        NVL (nota_stuffing.lunas,
                        0) lunas,
                        NVL (nota_stuffing.no_faktur,
                        '-') no_nota,
                        request_stuffing.no_request,
                        TO_CHAR (request_stuffing.tgl_request,
                        'dd/mm/yyyy') tgl_request,
                        request_stuffing.tgl_request tglr,
                        emkl.nm_pbm AS nama_emkl,
                        request_stuffing.voyage,
                        request_stuffing.nm_kapal nama_vessel,
                        COUNT (container_stuffing.no_container) jml_cont
                    FROM
                        request_stuffing,
                        nota_stuffing,
                        V_MST_PBM emkl,
                        container_stuffing
                    WHERE
                        request_stuffing.kd_consignee = emkl.kd_pbm
                        AND emkl.KD_CABANG = '05'
                        AND request_stuffing.no_request = container_stuffing.no_request
                        AND request_stuffing.perp_dari IS NOT NULL
                        AND nota_stuffing.no_request(+) = request_stuffing.no_request
                    GROUP BY
                        NVL (nota_stuffing.lunas,
                        0),
                        NVL (nota_stuffing.no_faktur,
                        '-'),
                        request_stuffing.no_request,
                        TO_CHAR (request_stuffing.tgl_request,
                        'dd/mm/yyyy'),
                        request_stuffing.tgl_request,
                        emkl.nm_pbm,
                        request_stuffing.voyage,
                        request_stuffing.nm_kapal) c
                ORDER BY
                    c.tglr DESC ) c
            WHERE $filter ROWNUM <=$request->length + 20";



        return DB::connection('uster')->select($query_list);
    }


    function checkNotaPerencaaanStuffing($no_req)
    {

        $query_cek    = "SELECT NOTA, KOREKSI FROM REQUEST_STUFFING WHERE NO_REQUEST = '$no_req'";
        $dataNota = DB::connection('uster')->selectOne($query_cek);

        if (!empty($dataNota)) {
            if (($dataNota->nota <> 'Y') and ($dataNota->koreksi <> 'Y')) {
                return '<a class="btn btn-info btn-sm" href="' . route('uster.billing.nota_ext_pnkn_stuffing.print_nota', ['no_req' => $no_req, 'n' => '999', 'koreksi' => 'N']) . '" target="_blank"> <b><i>  <i class="fas fa-search-dollar"> Proforma</i></b></a> ';
            } else if (($dataNota->nota == 'Y') and ($dataNota->koreksi <> 'Y')) {
                return '<a class="btn btn-info btn-sm" href="' . route('uster.billing.nota_ext_pnkn_stuffing.print_proforma', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i> Proforma </i></b></a> <br>';
            } else if (($dataNota->nota == 'Y') and ($dataNota->koreksi == 'Y')) {
                return '<a class="btn btn-info btn-sm" href="' . route('uster.billing.nota_ext_pnkn_stuffing.print_proforma', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i> Proforma </i></b></a> <br>';
            } else if (($dataNota->nota <> 'Y') and ($dataNota->koreksi == 'Y')) {
                return '<a class="btn btn-info btn-sm" href="' . route('uster.billing.nota_ext_pnkn_stuffing.print_nota', ['no_req' => $no_req, 'n' => '999', 'koreksi' => 'Y']) . '" target="_blank"> <b><i>  <i class="fas fa-search-dollar"> Proforma </i></b></a> ';
            }
        }
    }

    function PrintProforma($no_req)
    {
        $no_req = request()->input('no_req');
        $id_user = session('PENGGUNA_ID');

        // Ambil NO_NOTA dari tabel nota_stuffing
        $notanya = DB::connection('uster_dev')->table('USTER.nota_stuffing@DBCLOUD_LINK')
            ->whereRaw("TRIM(NO_REQUEST) = TRIM('$no_req')")
            ->where('STATUS', '<>', 'BATAL')
            ->value('NO_NOTA');



        // Ambil data nota_stuffing dan request_stuffing dengan menggunakan Eloquent ORM
        $data = DB::connection('uster_dev')->table('nota_stuffing@DBCLOUD_LINK as a')
            ->join('request_stuffing@DBCLOUD_LINK as c', 'a.NO_REQUEST', '=', 'c.NO_REQUEST')
            ->leftJoin('BILLING_NBS.TB_USER@DBCLOUD_LINK as mu', 'a.nipp_user', '=', 'mu.id')
            ->selectRaw("c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS, a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAME, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR') THEN a.NO_NOTA ELSE A.NO_FAKTUR END NO_FAKTUR_, F_CORPORATE(c.TGL_REQUEST) CORPORATE")
            ->where('a.NO_REQUEST', $no_req)
            ->whereRaw("a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_stuffing@DBCLOUD_LINK d WHERE d.NO_REQUEST = '$no_req')")
            ->first();

        $req_tgl = $data->tgl_request ?? '';
        $nama = $data->nama ?? '';
        $nama_lengkap = '<p>' . 'Printed by ' . $nama ?? '' . '</p>';
        $lunas = $data->lunas ?? '';

        // Cek apakah parameter 'first' tidak disetel, jika ya tambahkan informasi 'Reprinted by'
        if (!request()->has('first')) {
            $nama_lengkap .= '<p>' . 'Reprinted by ' . session('NAMA_LENGKAP') . '</p>';
        }

        $date = now()->format('d M Y H:i:s');

        // Ambil daftar container dari tabel CONTAINER_STUFFING dan MASTER_CONTAINER
        $rcont = DB::connection('uster_dev')->table('CONTAINER_STUFFING@DBCLOUD_LINK as A')
            ->join('MASTER_CONTAINER@DBCLOUD_LINK as B', 'A.NO_CONTAINER', '=', 'B.NO_CONTAINER')
            ->select('A.NO_CONTAINER', 'B.SIZE_', 'B.TYPE_', DB::connection('uster_dev')->raw("'MTY' as STATUS"))
            ->where('A.NO_REQUEST', $no_req)
            ->get();



        // Hitung biaya materai
        $data_mtr = DB::connection('uster_dev')->table('nota_stuffing_d@DBCLOUD_LINK')
            ->select(DB::connection('uster_dev')->raw("TO_CHAR (BIAYA, '999,999,999,999') AS BEA_MATERAI, BIAYA"))
            ->where('NO_NOTA', $notanya)
            ->where('KETERANGAN', 'MATERAI')
            ->first();


        $bea_materai = $data_mtr->bea_materai ?? 0;


        if ($lunas == 'YES') {
            $mat =  DB::connection('uster_dev')->table('itpk_nota_header@DBCLOUD_LINK')
                ->where('NO_REQUEST', $no_req)
                ->first();

            $no_mat = $mat ? $mat->no_peraturan : null;
        } else {
            $mat =  DB::connection('uster_dev')->table('MASTER_MATERAI@DBCLOUD_LINK')
                ->where('STATUS', 'Y')
                ->first();

            $no_mat = $mat ? $mat->no_peraturan : null;
        }

        $maxNota = DB::connection('uster_dev')
            ->table('nota_stuffing@DBCLOUD_LINK')
            ->where('no_request', $no_req)
            ->max('no_nota');

        $queryDtl = DB::connection('uster_dev')
            ->table('nota_stuffing_d@DBCLOUD_LINK as a')
            ->leftJoin('iso_code@DBCLOUD_LINK as b', 'a.id_iso', '=', 'b.id_iso')
            ->select([
                DB::raw("TO_CHAR(a.start_stack, 'dd/mm/yyyy') AS start_stack"),
                DB::raw("TO_CHAR(a.end_stack, 'dd/mm/yyyy') AS end_stack"),
                'a.keterangan',
                'a.jumlah_cont',
                'a.jml_hari',
                'b.size_',
                'b.type_',
                'b.status',
                'a.hz',
                DB::raw("TO_CHAR(a.tarif, '999,999,999,999') AS tarif"),
                DB::raw("TO_CHAR(a.biaya, '999,999,999,999') AS biaya"),
            ])
            ->whereNotIn('a.keterangan', ['ADMIN NOTA', 'MATERAI'])
            ->where('a.no_nota', $maxNota)
            ->orderBy('urut')
            ->get();

        return array(
            'data' => $data,
            'date' => $date,
            'detail' => $queryDtl,
            'nama_lengkap' => $nama_lengkap,
            'data_mtr' => $data_mtr,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'rcont' => $rcont,
        );
    }

    function InsertProforma($request)
    {
        $req = $request->input('REQ');
        try {
            // Mulai transaksi
            DB::beginTransaction();
            // DB::connection('uster')->beginTransaction();

            $nipp   = session('LOGGED_STORAGE');
            $no_req = $request->input("no_req");
            $koreksi = $request->input("koreksi");


            $query_cek_nota     = "SELECT NO_NOTA,STATUS FROM NOTA_STUFFING WHERE NO_REQUEST = '$no_req'";
            $nota    = DB::connection('uster')->selectOne($query_cek_nota);
            $no_nota_cek        = $nota->no_nota ?? NULL;
            $nota_status        = $nota->status ?? NULL;



            if (($no_nota_cek != NULL && $nota_status == 'BATAL') || ($no_nota_cek == NULL && $nota_status == NULL)) {

                $query_cek    = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA,10,15))+1),6,0), '000001') JUM_,
                              TO_CHAR(SYSDATE, 'MM') AS MONTH,
                              TO_CHAR(SYSDATE, 'YY') AS YEAR
                        FROM NOTA_STUFFING
                       WHERE NOTA_STUFFING.TGL_NOTA BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)";


                $jum_    = DB::connection('uster')->selectOne($query_cek);
                $jum        = $jum_->jum_;
                $month        = $jum_->month;
                $year        = $jum_->year;

                $no_nota    = "0405" . $month . $year . $jum;

                // Cek NO NOTA MTI

                $query_mti = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA_MTI,10,15))+1),6,0),'000001') JUM_,
                           TO_CHAR(SYSDATE, 'YYYY') AS YEAR
                           FROM MTI_COUNTER_NOTA WHERE TAHUN =  TO_CHAR(SYSDATE,'YYYY')";

                $jum_mti = DB::connection('uster')->selectOne($query_mti);
                $jum_nota_mti    = $jum_mti->jum_;
                $year_mti        = $jum_mti->year;
                $no_nota_mti    = "17." . $year_mti . "." . $jum_nota_mti;

                //select master pbm
                $query_master    = "SELECT b.KD_PBM, b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM FROM request_stuffing a, v_mst_pbm b WHERE a.KD_CONSIGNEE = b.KD_PBM AND b.KD_CABANG = '05' AND a.NO_REQUEST = '$no_req'";

                $master    = DB::connection('uster')->selectOne($query_master);
                $kd_pbm        = $master->kd_pbm;
                $nm_pbm        = $master->nm_pbm;
                $almt_pbm    = $master->almt_pbm;
                $npwp       = $master->no_npwp_pbm;


                $total_        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN, SUM(BIAYA)+SUM(PPN) TOTAL_TAGIHAN FROM temp_detail_nota WHERE no_request = '$no_req'
						AND KETERANGAN NOT IN ('MATERAI')";
                $total2        = DB::connection('uster')->selectOne($total_);

                //Biaya Administrasi

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

                $total_ = $total;
                $ppn   = $total_ppn;
                $tagihan = $total_tagihan + $materai;


                if ($koreksi <> 'Y') {
                    //$faktur	 	= "SELECT CONCAT(a.NO_FAKTUR ,(LPAD(NVL((MAX(SEQ_FAKTUR)+1),1),6,0))) FAKTUR, NVL((MAX(SEQ_FAKTUR)+1),1) SEQ FROM NOTA_ALL_H, (SELECT NO_FAKTUR FROM FAKTUR WHERE TAHUN =  to_char(sysdate, 'RRRR') AND KETERANGAN = 'NEW') a GROUP BY a.NO_FAKTUR";

                    $status_nota = 'NEW';
                    $nota_lama = '';
                } else {
                    $status_nota = 'KOREKSI';
                    $faktur         = "SELECT NO_NOTA, NO_FAKTUR, KD_EMKL FROM NOTA_STUFFING WHERE NO_REQUEST = '$no_req' AND NO_NOTA =(SELECT MAX(NO_NOTA) FROM NOTA_STUFFING WHERE NO_REQUEST = '$no_req')";
                    $faktur_        = DB::connection('uster')->selectOne($faktur);
                    $nota_lama    = $faktur_->no_faktur;

                    $update = "UPDATE NOTA_STUFFING SET STATUS = 'BATAL' WHERE NO_NOTA = '$nota_lama'";
                    DB::connection('uster')->update($update);
                }

                $query_insert_nota    = "INSERT INTO NOTA_STUFFING(NO_NOTA,
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
										SYSDATE,
                                        '$nota_lama',
										'$no_nota_mti')";

                if (DB::connection('uster')->insert($query_insert_nota)) {
                    //UPDATE COUNTER MTI DAN PENAMBAHAN FIELD NO_NOTA_MTI DI HEADER DAN DETAIL
                    // firman 20 agustus 2020

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

                    DB::connection('uster')->insert($query_mti);

                    $query_detail    = "SELECT * FROM temp_detail_nota WHERE no_request = '$no_req' ";
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
                        $coa   = $item->coa;
                        $ppn_d   = $item->ppn;

                        $query_insert    = "INSERT INTO nota_stuffing_d
                                            (
                                             ID_ISO,
                                             TARIF,
                                             BIAYA,
                                             KETERANGAN,
                                             NO_NOTA,
                                             JUMLAH_CONT,
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
                        DB::connection('uster')->insert($query_insert);

                        $i++;
                    }

                    $update_nota = "UPDATE NOTA_STUFFING SET CETAK_NOTA = '1' WHERE NO_NOTA = '$no_nota'";
                    $update_req = "UPDATE REQUEST_STUFFING SET NOTA = 'Y' WHERE NO_REQUEST = '$no_req'";
                    DB::connection('uster')->update($update_nota);

                    DB::connection('uster')->update($update_req);

                    $delete_temp = "DELETE from temp_detail_nota WHERE no_request = '$no_req'";
                    DB::connection('uster')->delete($delete_temp);
                    return "OK-INSERT";
                    // DB::connection('uster')->commit();
                    DB::commit();
                }
            } else {        //echo HOME;
                // DB::connection('uster')->rollback();
                DB::rollBack();
                return "OK";
            }
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            // DB::connection('uster')->rollback();
            DB::rollBack();
            return $e->getMessage();
        }
    }

    function previewProforma($noReq, $koreksi)
    {
        $rowNota = DB::connection('uster')
            ->table('request_stuffing as b')
            ->join('v_mst_pbm as c', 'b.id_penumpukan', '=', 'c.kd_pbm')
            ->selectRaw("
                c.nm_pbm AS emkl,
                c.no_npwp_pbm AS npwp,
                c.almt_pbm AS alamat,
                c.no_account_pbm,
                TO_CHAR(b.tgl_request, 'DD-MM-RRRR') AS tgl_request,
                F_CORPORATE(b.tgl_request) AS corporate
            ")
            ->where('b.no_request', $noReq)
            ->first();

        if ($rowNota) {
            $reqTgl = $rowNota->tgl_request;
            $kdPbm = $rowNota->no_account_pbm;
            $display = 1;
        }

        $tglReq = DB::connection('uster')->table('request_stuffing')
            ->where('NO_REQUEST', $noReq)
            ->selectRaw("TO_CHAR(TGL_REQUEST, 'dd/mon/yyyy') as TGL_REQUEST")
            ->first();

        $tglRe = $tglReq ? $tglReq->tgl_request : null;

        $execNota = DB::connection('uster')->statement("
            DECLARE tgl_req DATE;
            no_request VARCHAR2(100);
            jenis VARCHAR2(100);
            BEGIN
                no_request := :no_request;
                tgl_req := TO_DATE(:tgl_req, 'dd/mon/yyyy');
                jenis := 'stuffing';
                perp_pnkn_stufstrip(no_request, tgl_req, jenis);
            END;
        ", [
            'no_request' => $noReq,
            'tgl_req'    => $tglRe,
        ]);

        $detailNota = DB::connection('uster')->table('temp_detail_nota as a')
            ->join('iso_code as b', 'a.id_iso', '=', 'b.id_iso')
            ->whereNotIn('a.KETERANGAN', ['ADMIN NOTA', 'MATERAI'])
            ->where('a.no_request', $noReq)
            ->selectRaw("
                TO_CHAR(a.TARIF, '999,999,999,999') AS TARIF,
                TO_CHAR(a.BIAYA, '999,999,999,999') AS BIAYA,
                a.KETERANGAN, a.HZ, a.JML_CONT,
                START_STACK,
                END_STACK,
                b.SIZE_, b.TYPE_, b.STATUS,
                a.JML_HARI
            ")
            ->orderBy('a.URUT')
            ->get();

        $jumlahCont = DB::connection('uster')->table('container_stuffing')
            ->where('no_request', $noReq)
            ->count();

        $tarifPass = DB::connection('uster')->table('master_tarif as a')
            ->join('group_tarif as b', 'a.ID_GROUP_TARIF', '=', 'b.ID_GROUP_TARIF')
            ->whereRaw("TO_DATE(?, 'dd/mm/yyyy') BETWEEN b.START_PERIOD AND b.END_PERIOD", [$tglRe])
            ->where('a.ID_ISO', 'PASS')
            ->selectRaw("TO_CHAR((? * a.TARIF), '999,999,999,999') AS PASS, (? * a.TARIF) AS TARIF", [$jumlahCont, $jumlahCont])
            ->first();

        $row_pass = $tarifPass->tarif ?? 0;

        $total2 = DB::connection('uster')->table('temp_detail_nota')
            ->where('no_request', $noReq)
            ->whereNotIn('KETERANGAN', ['MATERAI'])
            ->selectRaw("SUM(BIAYA) AS TOTAL, SUM(PPN) AS PPN, SUM(BIAYA) + SUM(PPN) AS TOTAL_TAGIHAN")
            ->first();

        $total = $total2->total ?? 0;
        $totalPpn = $total2->ppn ?? 0;
        $totalBayar = $total2->total_tagihan ?? 0;

        $total2 = DB::connection('uster')->table('temp_detail_nota')
            ->where('no_request', $noReq)
            ->whereNotIn('KETERANGAN', ['MATERAI'])
            ->selectRaw("SUM(BIAYA) AS TOTAL, SUM(PPN) AS PPN, SUM(BIAYA) + SUM(PPN) AS TOTAL_TAGIHAN")
            ->first();

        $total = $total2->total ?? 0;
        $totalPpn = $total2->ppn ?? 0;
        $totalBayar = $total2->total_tagihan ?? 0;

        $discount = 0;
        $formattedDiscount = number_format($discount, 0, ',', ',');

        //Biaya Administrasi
        $row_adm = DB::connection('uster')->table('MASTER_TARIF as a')
            ->join('GROUP_TARIF as b', 'a.ID_GROUP_TARIF', '=', 'b.ID_GROUP_TARIF')
            ->where('b.KATEGORI_TARIF', 'ADMIN_NOTA')
            ->selectRaw("TO_CHAR(a.TARIF, '999,999,999,999') AS ADM, a.TARIF")
            ->first();

        $row_adm = $adm->tarif ?? 0;

        $row_tot = number_format($total, 0, ',', ',');
        $row_ppn = number_format($totalPpn, 0, ',', ',');

        $row_materai = DB::connection('uster')->table('TEMP_DETAIL_NOTA')
            ->where('no_request', $noReq)
            ->where('KETERANGAN', 'MATERAI')
            ->value('BIAYA') ?? 0;

        $beaMateraiFormatted = number_format($row_materai, 0, ',', ',');
        $row_pass = number_format($row_pass, 0, ',', ',');

        $totalBayar += $row_materai; // Add stamp duty
        $totalBayarFormatted = number_format($totalBayar, 0, ',', ',');

        // Pegawai Aktif
        $nama_peg = DB::connection('uster')
            ->table('master_pegawai')
            ->where('status', 'AKTIF')
            ->first();

        $returnData = [
            "row_discount" => $formattedDiscount,
            "nama_peg" => $nama_peg,
            "tgl_nota" => $tglReq,
            "row_adm" => $row_adm,
            "row_tot" => $row_tot,
            "row_ppn" => $row_ppn,
            "row_pass" => $row_pass,
            "row_materai" => $row_materai,
            "bea_materai" => $beaMateraiFormatted,
            "row_bayar" => $totalBayarFormatted,
            "row_nota" => $rowNota,
            "no_req" => $noReq,
            "row_detail" => $detailNota,
            "koreksi" => $koreksi,
            "pnkn" => "yes",
        ];

        return $returnData;
    }
}
