<?php

namespace App\Services\Billing\Stuffing;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Request;
use Exception;

class NotaStuffingPlan
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

        $query_list     = "SELECT *
        FROM (  SELECT NVL (nota_stuffing.lunas, 0) lunas,
                       CASE
                          WHEN nota_stuffing.status = 'BATAL'
                          THEN
                             CONCAT ('BATAL - ', nota_stuffing.no_faktur)
                          ELSE
                             NVL (nota_stuffing.no_faktur, '-')
                       END
                          no_nota,
                       request_stuffing.no_request,
                       tgl_request,
                       request_stuffing.tgl_request tglr,
                       emkl.nm_pbm AS nama_emkl,
                       request_stuffing.voyage,
                       request_stuffing.nm_kapal nama_vessel,
                       COUNT (container_stuffing.no_container) jml_cont
                  FROM request_stuffing,
                       nota_stuffing,
                       nota_pnkn_stuf,
                       V_MST_PBM emkl,
                       container_stuffing
                 WHERE request_stuffing.kd_consignee = emkl.kd_pbm
                       AND emkl.KD_CABANG = '05'
                       AND request_stuffing.no_request =
                              container_stuffing.no_request
                       AND request_stuffing.perp_dari IS NULL
                       AND nota_stuffing.no_request(+) = request_stuffing.no_request
                       AND nota_pnkn_stuf.no_request(+) = request_stuffing.no_request
                       AND request_stuffing.STUFFING_DARI  <> 'AUTO'
              GROUP BY NVL (nota_stuffing.lunas, 0),
                       CASE
                          WHEN nota_stuffing.status = 'BATAL'
                          THEN
                             CONCAT ('BATAL - ', nota_stuffing.no_faktur)
                          ELSE
                             NVL (nota_stuffing.no_faktur, '-')
                       END,
                       request_stuffing.no_request,
                       TO_CHAR (request_stuffing.tgl_request, 'dd/mm/yyyy'),
                       request_stuffing.tgl_request,
                       emkl.nm_pbm,
                       request_stuffing.voyage,
                       request_stuffing.nm_kapal
                       ORDER BY request_stuffing.tgl_request DESC
                       ) c
                       WHERE $filter ROWNUM <=$request->length + 20";



        return DB::connection('uster')->select($query_list);
    }


    function checkNotaPerencaaanStuffing($no_req)
    {
        $query_cek    = "SELECT NOTA, KOREKSI, NOTA_PNKN, KOREKSI_PNKN,
		CASE WHEN TGL_REQUEST <= TO_DATE('16/04/2013','dd/mm/yy') THEN 'NO' ELSE 'YES' END AS CEK_TGL
		FROM REQUEST_STUFFING WHERE NO_REQUEST = '$no_req'";

        $nota = DB::connection('uster')->selectOne($query_cek);
        if ($nota != null) {
            $cetak        = $nota->nota;
            $cetak1        = $nota->nota_pnkn;
            $lunas        = $nota->koreksi;
            $lunas1        = $nota->koreksi_pnkn;
            $ok            = $nota->cek_tgl;
            if ($ok == 'NO') {
                if ($lunas == 'Y' && $cetak <> 'Y') {
                    $ok = 'YES';
                } else if ($lunas == 'Y' && $cetak == 'Y') {
                    $ok = 'NO';
                }
            }
            $req = $no_req;
            $notas = "";

            if ($ok == 'YES') {
                if (($cetak <> 'Y') and ($lunas <> 'Y') and ($cetak1 <> 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=N" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Stuffing</i></b></a> <br/>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_pnkn', ['no_req' => $no_req]) . '&n=999&koreksi=N" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Penumpukan</i></b></a> ';
                } else if (($cetak == 'Y') and ($lunas <> 'Y') and ($cetak1 <> 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Stuffing </i></b></a>' .
                        ' | <a onclick="recalc(\'' . $req . '\',\'' . $notas . '\')" title="recalculate stuffing"><img src="' . asset('assets/images/money2.png') . '" ></a> <br>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_pnkn', ['no_req' => $no_req]) . '&n=999&koreksi=N" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Penumpukan</i></b></a> ';
                } else if (($cetak <> 'Y') and ($lunas <> 'Y') and ($cetak1 == 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=N" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Stuffing</i></b></a> <br/>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma_pnkn', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Penumpukan </i></b></a> ' .
                        ' | <a onclick="recalc_pnkn(\'' . $req . '\',\'' . $notas . '\')" title="recalculate penumpukan"><img src="' . asset('assets/images/money2.png') . '" ></a>';
                } else if (($cetak == 'Y') and ($lunas <> 'Y') and ($cetak1 == 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Stuffing </i></b></a>' .
                        ' | <a onclick="recalc(\'' . $req . '\',\'' . $notas . '\')" title="recalculate stuffing"><img src="' . asset('assets/images/money2.png') . '" ></a> <br>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma_pnkn', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Penumpukan </i></b></a> ' .
                        ' | <a onclick="recalc_pnkn(\'' . $req . '\',\'' . $notas . '\')" title="recalculate penumpukan"><img src="' . asset('assets/images/money2.png') . '" ></a>';
                } else if (($cetak <> 'Y') and ($lunas == 'Y') and ($cetak1 <> 'Y') and ($lunas == 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Stuffing</i></b></a> <br/>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_pnkn', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Penumpukan</i></b></a>';
                } else if (($cetak <> 'Y') and ($lunas == 'Y') and ($cetak1 <> 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Stuffing</i></b></a> <br/>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_pnkn', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Penumpukan</i></b></a>';
                } else if (($cetak == 'Y') and ($lunas == 'Y') and ($cetak1 <> 'Y') and ($lunas == 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Stuffing </i></b></a> <br/>' .
                        ' | <a onclick="recalc(\'' . $req . '\',\'' . $notas . '\')" title="recalculate stuffing"><img src="' . asset('assets/images/money2.png') . '" ></a> <br>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_pnkn', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Penumpukan</i></b></a>';
                } else if (($cetak <> 'Y') and ($lunas == 'Y') and ($cetak1 == 'Y') and ($lunas == 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Stuffing</i></b></a> <br/>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma_pnkn', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Penumpukan </i></b></a> ' .
                        ' | <a onclick="recalc_pnkn(\'' . $req . '\',\'' . $notas . '\')" title="recalculate penumpukan"><img src="' . asset('assets/images/money2.png') . '" ></a> <br>';
                } else if (($cetak == 'Y') and ($lunas == 'Y') and ($cetak1 == 'Y') and ($lunas == 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Stuffing </i></b></a>' .
                        ' | <a onclick="recalc(\'' . $req . '\',\'' . $notas . '\')" title="recalculate stuffing"><img src="' . asset('assets/images/money2.png') . '" ></a> <br>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma_pnkn', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Penumpukan </i></b></a> ' .
                        ' | <a onclick="recalc_pnkn(\'' . $req . '\',\'' . $notas . '\')" title="recalculate penumpukan"><img src="' . asset('assets/images/money2.png') . '" ></a> ';
                } else if (($cetak == 'N') and ($lunas == 'Y') and ($cetak1 == 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Stuffing</i></b></a> <br/>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma_pnkn', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Penumpukan </i></b></a> ' .
                        ' | <a onclick="recalc_pnkn(\'' . $req . '\',\'' . $notas . '\')" title="recalculate penumpukan"><img src="' . asset('assets/images/money2.png') . '" ></a>';
                } else if (($cetak == 'Y') and ($lunas == 'Y') and ($cetak1 == 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma Stuffing</i></b></a> <br/>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma_pnkn', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Penumpukan </i></b></a> ' .
                        ' | <a onclick="recalc_pnkn(\'' . $req . '\',\'' . $notas . '\')" title="recalculate penumpukan"><img src="' . asset('assets/images/money2.png') . '" ></a>';
                }
            } else if ($ok == 'NO') {
                if (($cetak <> 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=N" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma</i></b></a> ';
                } else if (($cetak == 'Y') and ($lunas <> 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i></i></b></a> <br>';
                } else if (($cetak == 'Y') and ($lunas == 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Stuffing </i></b></a>' .
                        '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_proforma_pnkn', ['no_req' => $no_req]) . '" target="_blank"><b><i> <i class="fas fa-print"> </i>Proforma Penumpukan </i></b></a> ';
                } else if (($cetak <> 'Y') and ($lunas == 'Y')) {
                    return '<a class="btn btn-info btn-sm mb-2" href="' . route('uster.billing.nota_stuffing.print_nota_simple', ['no_req' => $no_req]) . '&n=999&koreksi=Y" target="_blank"> <b><i> <i class="fas fa-search-dollar"> </i>Proforma </i></b></a> ';
                }
            }
        }
    }

    function previewProforma($no_req, $koreksi)
    {
        $row_nota = DB::connection('uster')
            ->table('REQUEST_STUFFING as b')
            ->join('V_MST_PBM as c', 'b.KD_CONSIGNEE', '=', 'c.KD_PBM')
            ->select([
                'c.NM_PBM as EMKL',
                'c.NO_NPWP_PBM as NPWP',
                'c.ALMT_PBM as ALAMAT',
                DB::raw("TO_CHAR(b.TGL_REQUEST, 'DD-MM-RRRR') AS TGL_REQUEST"),
                'c.NO_ACCOUNT_PBM',
                DB::raw("F_CORPORATE(b.TGL_REQUEST) AS CORPORATE")
            ])
            ->where('b.NO_REQUEST', $no_req)
            ->first();

        $req_tgl  = $row_nota->tgl_request;
        $kd_pbm  = $row_nota->no_account_pbm;
        $display = 1;

        $tgl_req = DB::connection('uster')
            ->table('REQUEST_STUFFING')
            ->selectRaw("TO_CHAR(TGL_REQUEST, 'dd/mon/yyyy') AS TGL_REQUEST")
            ->where('NO_REQUEST', $no_req)
            ->first();

        $tgl_re = $tgl_req ? $tgl_req->tgl_request : null;

        $sql_xpi = "DECLARE
                        id_nota NUMBER;
                        tgl_req DATE;
                        no_request VARCHAR2(100);
                        jenis VARCHAR2(100);
                        err_msg VARCHAR2(100);
                    BEGIN
                        id_nota := 3;
                        tgl_req := TO_DATE('$tgl_re', 'DD/mon/YYYY');
                        no_request := '$no_req';
                        err_msg := 'NULL';
                        jenis := 'stuffing';

                        pack_get_nota_stuffing_new.create_detail_nota(id_nota, tgl_req, no_request, jenis, err_msg);
                    END;";

        $execDetailNota = DB::connection('uster')->statement($sql_xpi);

        $jenis = DB::connection('uster')
            ->table('CONTAINER_STUFFING')
            ->where('NO_REQUEST', $no_req)
            ->distinct()
            ->count('ASAL_CONT');

        if ($jenis > 1) {
            $partOne = DB::connection('uster')
                ->table('TEMP_DETAIL_NOTA as a')
                ->join('ISO_CODE as b', 'a.ID_ISO', '=', 'b.ID_ISO')
                ->selectRaw("
                    a.JML_HARI,
                    SUM(a.BIAYA) AS biaya_,
                    TO_CHAR(SUM(a.BIAYA), '999,999,999,999') AS BIAYA,
                    a.TEKSTUAL AS KETERANGAN,
                    a.HZ,
                    TO_DATE(a.START_STACK, 'dd/mm/rrrr') AS START_STACK,
                    TO_DATE(a.END_STACK, 'dd/mm/rrrr') AS END_STACK,
                    b.SIZE_,
                    b.TYPE_,
                    CASE a.TEKSTUAL
                        WHEN 'PAKET STUFF LAPANGAN' THEN '-'
                        WHEN 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                        WHEN 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                        ELSE b.STATUS
                    END AS STATUS,
                    CASE a.TEKSTUAL
                        WHEN 'PAKET STUFF LAPANGAN' THEN 10
                        WHEN 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                        WHEN 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                        ELSE a.URUT
                    END AS urut
                ")
                ->where('a.NO_REQUEST', $no_req)
                ->where('a.TEKSTUAL', '<>', 'ADMIN NOTA')
                ->groupBy(
                    'a.JML_HARI',
                    'a.HZ',
                    'a.START_STACK',
                    'a.END_STACK',
                    'b.SIZE_',
                    'b.TYPE_',
                    'a.TEKSTUAL',
                    'a.URUT',
                    'b.STATUS'
                );

            $partTwo = DB::connection('uster')
                ->table('TEMP_DETAIL_NOTA as a')
                ->selectRaw(
                    "
                    CASE a.TEKSTUAL
                        WHEN 'PAKET STUFF LAPANGAN' THEN (SELECT COUNT(*) FROM CONTAINER_STUFFING WHERE NO_REQUEST = ? AND TYPE_STUFFING = 'STUFFING_LAP')
                        WHEN 'PAKET STUFF GUDANG EKS TONGKANG' THEN (SELECT COUNT(*) FROM CONTAINER_STUFFING WHERE NO_REQUEST = ? AND TYPE_STUFFING = 'STUFFING_GUD_TONGKANG')
                        WHEN 'PAKET STUFF GUDANG EKS TRUCK' THEN (SELECT COUNT(*) FROM CONTAINER_STUFFING WHERE NO_REQUEST = ? AND TYPE_STUFFING = 'STUFFING_GUD_TRUCK')
                        ELSE 0
                    END AS jml_cont",
                    [$no_req, $no_req, $no_req]
                )
                ->where('a.NO_REQUEST', $no_req)
                ->whereIn('a.TEKSTUAL', [
                    'PAKET STUFF LAPANGAN',
                    'PAKET STUFF GUDANG EKS TONGKANG',
                    'PAKET STUFF GUDANG EKS TRUCK'
                ])
                ->groupBy('a.TEKSTUAL');

            $detail_nota = DB::connection('uster')
                ->table(DB::raw("({$partOne->toSql()}) as partone"))
                ->mergeBindings($partOne)
                ->join(DB::raw("({$partTwo->toSql()}) as partwo"), DB::raw('1'), '=', DB::raw('1'))
                ->selectRaw("partone.*, partwo.*, TO_CHAR(partone.biaya_/partwo.jml_cont, '999,999,999,999') as tarif")
                ->get();
        } else {
            $detail_nota = DB::connection('uster')
                ->table('TEMP_DETAIL_NOTA as a')
                ->join('ISO_CODE as b', 'a.ID_ISO', '=', 'b.ID_ISO')
                ->selectRaw("
                    a.JML_HARI,
                    SUM(a.BIAYA) AS biaya_,
                    TO_CHAR(SUM(a.BIAYA), '999,999,999,999') AS BIAYA,
                    TO_CHAR(SUM(a.TARIF), '999,999,999,999') AS TARIF,
                    a.TEKSTUAL AS KETERANGAN,
                    a.JML_CONT,
                    a.HZ,
                    TO_DATE(a.START_STACK, 'dd/mm/rrrr') AS START_STACK,
                    TO_DATE(a.END_STACK, 'dd/mm/rrrr') AS END_STACK,
                    b.SIZE_,
                    b.TYPE_,
                    CASE a.TEKSTUAL
                        WHEN 'PAKET STUFF LAPANGAN' THEN '-'
                        WHEN 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                        WHEN 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                        ELSE b.STATUS
                    END AS STATUS,
                    CASE a.TEKSTUAL
                        WHEN 'PAKET STUFF LAPANGAN' THEN 10
                        WHEN 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                        WHEN 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                        ELSE a.URUT
                    END AS urut
                ")
                ->where('a.NO_REQUEST', $no_req)
                ->where('a.TEKSTUAL', '<>', 'ADMIN NOTA')
                ->groupByRaw("
                    a.JML_HARI, a.HZ, a.START_STACK, a.END_STACK, b.SIZE_, b.TYPE_,
                    a.TEKSTUAL, a.JML_CONT,
                    CASE a.TEKSTUAL
                        WHEN 'PAKET STUFF LAPANGAN' THEN '-'
                        WHEN 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                        WHEN 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                        ELSE b.STATUS
                    END,
                    CASE a.TEKSTUAL
                        WHEN 'PAKET STUFF LAPANGAN' THEN 10
                        WHEN 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                        WHEN 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                        ELSE a.URUT
                    END
                ")
                ->get();
        }

        $row_detail   = $detail_nota;

        //jumlah container per request
        $jumlah_cont = DB::connection('uster')
            ->table('container_stuffing')
            ->where('NO_REQUEST', $no_req)
            ->count();

        //tarif pass
        $row_pass = DB::connection('uster')
            ->table('master_tarif as a')
            ->join('group_tarif as b', 'a.ID_GROUP_TARIF', '=', 'b.ID_GROUP_TARIF')
            ->selectRaw("
                TO_CHAR((? * a.TARIF), '999,999,999,999') as PASS,
                (? * a.TARIF) as TARIF
            ", [$jumlah_cont, $jumlah_cont])
            ->whereRaw("TO_DATE(?, 'dd/mm/yyyy') BETWEEN b.START_PERIOD AND b.END_PERIOD", [$tgl_re])
            ->where('a.ID_ISO', 'PASS')
            ->first();

        $tarif_pass = $row_pass ? $row_pass->tarif : 0;

        $total2 = DB::connection('uster')
            ->table('temp_detail_nota')
            ->selectRaw('SUM(BIAYA) as total, SUM(PPN) as ppn, SUM(BIAYA) + SUM(PPN) as total_tagihan')
            ->where('NO_REQUEST', $no_req)
            ->whereNotIn('ID_ISO', ['MATERAI'])
            ->first();

        $total_ = $total2 ? $total2->total : 0;
        $ppn = $total2 ? $total2->ppn : 0;
        $total_bayar = $total2 ? $total2->total_tagihan : 0;

        // Discount
        $discount = 0;
        $query_discount   = "SELECT TO_CHAR($discount , '999,999,999,999') AS DISCOUNT FROM DUAL";
        $row_discount   = DB::connection('uster')->selectOne($query_discount);

        // Biaya Administrasi
        $row_adm = DB::connection('uster')
            ->table('MASTER_TARIF as a')
            ->join('GROUP_TARIF as b', 'a.ID_GROUP_TARIF', '=', 'b.ID_GROUP_TARIF')
            ->where('b.KATEGORI_TARIF', 'ADMIN_NOTA')
            ->select('a.TARIF')
            ->first();

        // Bea Materai
        $row_mtr = DB::connection('uster')
            ->table('TEMP_DETAIL_NOTA')
            ->where('NO_REQUEST', $no_req)
            ->where('KETERANGAN', 'MATERAI')
            ->select('BIAYA as BEA_MATERAI')
            ->first();

        $bea_materai = $row_mtr && $row_mtr->BEA_MATERAI > 0 ? $row_mtr->BEA_MATERAI : 0;

        // Menghitung Jumlah Dibayar
        $total_bayar += $bea_materai;
        $total_bayar_formatted = number_format($total_bayar, 0, ',', '.');

        // Format Materai
        $materai = number_format($bea_materai, 0, ',', '.');

        // Pegawai Aktif
        $nama_peg = DB::connection('uster')
            ->table('MASTER_PEGAWAI')
            ->where('STATUS', 'AKTIF')
            ->first();

        $returnData = [
            "row_discount" => $row_discount,
            "nama_peg" => $nama_peg,
            "tgl_nota" => $tgl_re,
            "row_adm" => $row_adm,
            "row_tot" => $total_,
            "row_ppn" => $ppn,
            "row_materai" => $materai,
            "bea_materai" => $bea_materai,
            "row_pass" => $row_pass,
            "row_bayar" => $total_bayar_formatted,
            "row_nota" => $row_nota,
            "no_req" => $no_req,
            "row_detail" => $row_detail,
            "koreksi" => $koreksi,
        ];

        return $returnData;
    }

    function previewProformaPNKN($no_req, $koreksi)
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
            ->where('b.no_request', $no_req)
            ->first();

        if ($rowNota) {
            $reqTgl = $rowNota->tgl_request;
            $kdPbm = $rowNota->no_account_pbm;
            $display = 1;
        }

        if ($rowNota->emkl == NULL) {
            $data_nota = DB::connection('uster')
                ->table('request_stuffing as b')
                ->join('v_mst_pbm as c', 'b.kd_consignee', '=', 'c.kd_pbm')
                ->select([
                    'c.nm_pbm as emkl',
                    'c.no_npwp_pbm as npwp',
                    'c.almt_pbm as alamat'
                ])
                ->where('b.no_request', $no_req)
                ->first();
        }

        $query_tgl    = "SELECT TO_CHAR(TGL_REQUEST,'dd/mon/yyyy') TGL_REQUEST FROM request_stuffing WHERE NO_REQUEST = '$no_req'";
        $tgl_req    = DB::connection('uster')->selectOne($query_tgl);
        $tgl_re         = $tgl_req->tgl_request;

        $sql_xpi = "DECLARE
                        id_nota NUMBER;
                        tgl_req DATE;
                        no_request VARCHAR2(100);
                        jenis VARCHAR2(100);
                        err_msg VARCHAR2(100);
                    BEGIN
                        id_nota := 3;
                        tgl_req := TO_DATE('$tgl_re', 'DD/mon/YYYY');
                        no_request := '$no_req';
                        err_msg := 'NULL';
                        jenis := 'pnkn_stuffing';

                        pack_get_nota_stuffing_new.create_detail_nota(id_nota, tgl_req, no_request, jenis, err_msg);
                    END;";

        $execDetailNota = DB::connection('uster')->statement($sql_xpi);

        // Detail Nota
        $row_detail = DB::connection('uster')
            ->table('temp_detail_nota_i as a')
            ->join('iso_code as b', 'a.id_iso', '=', 'b.id_iso')
            ->select(
                'a.jml_hari',
                DB::raw("TO_CHAR(a.tarif, '999,999,999,999') as tarif"),
                DB::raw("TO_CHAR(a.biaya, '999,999,999,999') as biaya"),
                'a.keterangan',
                'a.hz',
                'a.jml_cont',
                DB::raw("TO_CHAR(a.start_stack, 'DD/MM/YYYY') as start_stack"),
                DB::raw("TO_CHAR(a.end_stack, 'DD/MM/YYYY') as end_stack"),
                'b.size_',
                'b.type_',
                'b.status',
                'a.urut'
            )
            ->where('a.no_request', $no_req)
            ->whereNotIn('a.keterangan', ['ADMIN NOTA', 'MATERAI']) // Sesuai dengan filter
            ->orderBy('a.urut', 'ASC')
            ->get();

        // Jumlah Container
        $jumlah_cont = DB::connection('uster')
            ->table('container_stuffing')
            ->where('no_request', $no_req)
            ->count();

        // Tarif Pass
        $row_pass = DB::connection('uster')
            ->table('master_tarif as a')
            ->join('group_tarif as b', 'a.id_group_tarif', '=', 'b.id_group_tarif')
            ->select(
                DB::raw("TO_CHAR(($jumlah_cont * a.tarif), '999,999,999,999') as pass"),
                DB::raw("($jumlah_cont * a.tarif) as tarif")
            )
            ->whereRaw("TO_DATE(?, 'dd/mm/yyyy') BETWEEN b.start_period AND b.end_period", [$tgl_re])
            ->where('a.id_iso', 'PASS')
            ->first();

        $tarif_pass = $row_pass ? $row_pass->tarif : 0;

        // Total Biaya
        $total2 = DB::connection('uster')
            ->table('temp_detail_nota_i')
            ->select(
                DB::raw('SUM(biaya) as total'),
                DB::raw('SUM(ppn) as ppn'),
                DB::raw('SUM(biaya) + SUM(ppn) as total_tagihan')
            )
            ->where('no_request', $no_req)
            ->whereNotIn('id_iso', ['MATERAI'])
            ->first();

        $total = $total2->total ?? 0;
        $ppn = $total2->ppn ?? 0;
        $total_bayar = $total2->total_tagihan ?? 0;

        //Discount
        $discount = 0;
        $query_discount        = "SELECT TO_CHAR($discount , '999,999,999,999') AS DISCOUNT FROM DUAL";
        $row_discount        = DB::connection('uster')->selectOne($query_discount);

        // Biaya Administrasi
        $row_adm = DB::connection('uster')
            ->table('master_tarif as a')
            ->join('group_tarif as b', 'a.id_group_tarif', '=', 'b.id_group_tarif')
            ->select('a.tarif as adm')
            ->where('b.kategori_tarif', 'ADMIN_NOTA')
            ->first();

        $adm = $row_adm ? $row_adm->adm : 0;

        // Menghitung Total Dasar Pengenaan Pajak
        $total_ = $total + $adm;
        $row_tot = number_format($total_, 0, ',', '.');

        // Menghitung Jumlah PPN (10%)
        $row_ppn = number_format($ppn, 0, ',', '.');

        // Menghitung Bea Materai
        $row_mtr = DB::connection('uster')
            ->table('temp_detail_nota_i')
            ->select('biaya as bea_materai')
            ->where('no_request', $no_req)
            ->where('keterangan', 'MATERAI')
            ->first();

        $bea_materai = $row_mtr && $row_mtr->bea_materai > 0 ? $row_mtr->bea_materai : 0;
        $row_materai = number_format($bea_materai, 0, ',', '.');

        // Menghitung Pass Truck
        $row_pass = number_format($tarif_pass, 0, ',', '.');

        // Menghitung Jumlah Dibayar (Total + Bea Materai)
        $total_bayar = $total_bayar + $bea_materai;
        $row_bayar = number_format($total_bayar, 0, ',', '.');

        // Pegawai Aktif
        $nama_peg = DB::connection('uster')
            ->table('master_pegawai')
            ->where('status', 'AKTIF')
            ->first();

        $returnData = [
            "row_discount" => $row_discount,
            "nama_peg" => $nama_peg,
            "tgl_nota" => $tgl_re,
            "row_adm" => $row_adm,
            "row_tot" => $row_tot,
            "row_ppn" => $row_ppn,
            "row_pass" => $row_pass,
            "row_materai" => $row_materai,
            "bea_materai" => $bea_materai,
            "row_bayar" => $row_bayar,
            "row_nota" => $rowNota,
            "no_req" => $no_req,
            "row_detail" => $row_detail,
            "koreksi" => $koreksi,
            "pnkn" => "yes",
        ];

        return $returnData;
    }

    function PrintProforma($no_req)
    {
        $no_req = request()->input('no_req');
        $id_user = session('PENGGUNA_ID');

        // Ambil NO_NOTA dari tabel nota_stuffing
        $notanya = DB::connection('uster')->table('nota_stuffing')
            ->whereRaw("TRIM(NO_REQUEST) = TRIM('$no_req')")
            ->where('STATUS', '<>', 'BATAL')
            ->value('NO_NOTA');

        if ($notanya == null) {
            return 'NOT_FOUND';
        }

        // Ambil data nota_stuffing dan request_stuffing dengan menggunakan Eloquent ORM
        $data = DB::connection('uster')->table('nota_stuffing as a')
            ->join('request_stuffing as c', 'a.NO_REQUEST', '=', 'c.NO_REQUEST')
            ->leftJoin('BILLING_NBS.TB_USER as mu', 'a.nipp_user', '=', 'mu.id')
            ->selectRaw("c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS, a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAME, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR') THEN a.NO_NOTA ELSE A.NO_FAKTUR END NO_FAKTUR_, F_CORPORATE(c.TGL_REQUEST) CORPORATE")
            ->where('a.NO_REQUEST', $no_req)
            ->whereRaw("a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_stuffing d WHERE d.NO_REQUEST = '$no_req')")
            ->first();

        $req_tgl = $data->tgl_request;
        $nama_lengkap = '<p>' . 'Printed by ' . $data->name . '</p>';
        $lunas = $data->lunas;

        // Cek apakah parameter 'first' tidak disetel, jika ya tambahkan informasi 'Reprinted by'
        if (!request()->has('first')) {
            $nama_lengkap .= '<p>' . 'Reprinted by ' . session('NAMA_LENGKAP') . '</p>';
        }

        $date = now()->format('d M Y H:i:s');

        // Ambil daftar container dari tabel CONTAINER_STUFFING dan MASTER_CONTAINER
        $rcont = DB::connection('uster')->table('CONTAINER_STUFFING as A')
            ->join('MASTER_CONTAINER as B', 'A.NO_CONTAINER', '=', 'B.NO_CONTAINER')
            ->select('A.NO_CONTAINER', 'B.SIZE_', 'B.TYPE_', DB::connection('uster')->raw("'MTY' as STATUS"))
            ->where('A.NO_REQUEST', $no_req)
            ->get();



        // Hitung biaya materai
        $data_mtr = DB::connection('uster')->table('nota_stuffing_d')
            ->select(DB::connection('uster')->raw("TO_CHAR (BIAYA, '999,999,999,999') AS BEA_MATERAI, BIAYA"))
            ->where('NO_NOTA', $notanya)
            ->where('KETERANGAN', 'MATERAI')
            ->first();
        $bea_materai = $data_mtr->bea_materai ?? 0;


        if ($lunas == 'YES') {
            $mat =  DB::connection('uster')->table('itpk_nota_header')
                ->where('NO_REQUEST', $no_req)
                ->first();

            $no_mat = $mat ? $mat->no_peraturan : null;
        } else {
            $mat =  DB::connection('uster')->table('MASTER_MATERAI')
                ->where('STATUS', 'Y')
                ->first();

            $no_mat = $mat ? $mat->no_peraturan : null;
        }

        $cek_jenis = DB::connection('uster')->table('container_stuffing')
            ->where('no_request', $no_req)
            ->distinct()
            ->count('asal_cont');

        $r_jns = $cek_jenis > 1 ? true : false;

        if ($r_jns) {
            $query_dtl = "SELECT partone.*, partwo.*, TO_CHAR(partone.biaya_/partwo.jml_cont,'999,999,999,999')  tarif FROM (SELECT a.JML_HARI,
                        SUM(a.BIAYA) biaya_,
                        TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                        a.tekstual KETERANGAN,
                        a.HZ,
                        TO_DATE (a.START_STACK, 'dd/mm/rrrr') START_STACK,
                        TO_DATE (a.END_STACK, 'dd/mm/rrrr') END_STACK,
                        b.SIZE_,
                        b.TYPE_,
                        case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN '-'
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                        ELSE
                        b.STATUS
                        END AS STATUS,
                        case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN 10
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                        ELSE
                        a.urut
                        END AS urut
                FROM nota_stuffing_d a, iso_code b
                WHERE     a.TEKSTUAL NOT IN ('ADMIN NOTA','MATERAI') /**Fauzan modif 24 Agustus 2020 [NOT IN MATERAI]*/
                        AND a.id_iso = b.id_iso
                        AND a.no_nota = '$notanya'
            GROUP BY a.jml_hari, a.hz, a.start_stack, a.end_stack, b.size_, b.type_,  a.tekstual,
            case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN '-'
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                        ELSE
                        b.STATUS
                        END,
            case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN 10
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                        ELSE
                        a.urut
                        END) partone,
            (SELECT case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN (select count(*) from container_stuffing where no_request = '$no_req' and type_stuffing = 'STUFFING_LAP')
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN (select count(*) from container_stuffing where no_request = '$no_req' and type_stuffing = 'STUFFING_GUD_TONGKANG')
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN (select count(*) from container_stuffing where no_request = '$no_req' and type_stuffing = 'STUFFING_GUD_TRUCK')
                        ELSE 0
                        END AS jml_cont FROM nota_stuffing_d a WHERE no_nota = '$notanya'
                        and a.tekstual in ('PAKET STUFF LAPANGAN','PAKET STUFF GUDANG EKS TONGKANG','PAKET STUFF GUDANG EKS TRUCK')
            group by a.tekstual) partwo";
        } else {
            $query_dtl = "SELECT a.JML_HARI,
                        SUM(a.BIAYA) biaya_,
                        TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                        TO_CHAR(SUM(a.TARIF),'999,999,999,999') TARIF,
                        a.tekstual KETERANGAN,
                        a.jumlah_cont,
                        a.HZ,
                        TO_DATE (a.START_STACK, 'dd/mm/rrrr') START_STACK,
                        TO_DATE (a.END_STACK, 'dd/mm/rrrr') END_STACK,
                        b.SIZE_,
                        b.TYPE_,
                        case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN '-'
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                        ELSE
                        b.STATUS
                        END AS STATUS,
                        case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN 10
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                        ELSE
                        a.urut
                        END AS urut
                FROM nota_stuffing_d a, iso_code b
                WHERE     a.TEKSTUAL NOT IN ('ADMIN NOTA','MATERAI') /**Fauzan modif 24 Agustus 2020 [NOT IN MATERAI]*/
                        AND a.id_iso = b.id_iso
                        AND a.no_nota = '$notanya'
            GROUP BY a.jml_hari, a.hz, a.start_stack, a.end_stack, b.size_, b.type_,  a.tekstual, jumlah_cont,
            case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN '-'
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                        ELSE
                        b.STATUS
                        END,
            case a.tekstual
                            when 'PAKET STUFF LAPANGAN' THEN 10
                            when 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                            when 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                        ELSE
                        a.urut
                        END";
        }

        $res = DB::connection('uster')->select($query_dtl);



        return array(
            'data' => $data,
            'date' => $date,
            'detail' => $res,
            'nama_lengkap' => $nama_lengkap,
            'data_mtr' => $data_mtr,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'rcont' => $rcont,
        );
    }

    function PrintProformaPNKN($no_req)
    {
        // Ambil NO_NOTA dari tabel nota_stuffing
        $notanya = DB::connection('uster')->table('nota_pnkn_stuf')
            ->whereRaw("TRIM(NO_REQUEST) = TRIM('$no_req')")
            ->where('STATUS', '<>', 'BATAL')
            ->value('NO_NOTA');

        if (!$notanya) {
            return 'NOT_FOUND';
        }

        // Ambil data nota_stuffing dan request_stuffing dengan menggunakan Eloquent ORM
        $query = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT  , a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
        CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAME, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
         THEN a.NO_NOTA
         ELSE A.NO_FAKTUR END NO_FAKTUR_, F_CORPORATE(c.TGL_REQUEST) CORPORATE
                             FROM nota_pnkn_stuf a, request_stuffing c, BILLING_NBS.TB_USER mu where
                             a.NO_REQUEST = c.NO_REQUEST
                             AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_pnkn_stuf d WHERE d.NO_REQUEST = '$no_req' )
                             and c.NO_REQUEST = '$no_req'
                             and a.nipp_user = mu.id(+)";
        $data = DB::connection('uster')->selectOne($query);
        $req_tgl = $data->tgl_request;
        $nama_lengkap = '<p>' . 'Printed by ' . $data->name . '</p>';
        $lunas = $data->lunas;

        // Cek apakah parameter 'first' tidak disetel, jika ya tambahkan informasi 'Reprinted by'
        if (!request()->has('first')) {
            $nama_lengkap .= '<p>' . 'Reprinted by ' . session('NAMA_LENGKAP') . '</p>';
        }

        $date = now()->format('d M Y H:i:s');

        // Ambil daftar container dari tabel CONTAINER_STUFFING dan MASTER_CONTAINER
        $rcont = DB::connection('uster')->table('CONTAINER_STUFFING as A')
            ->join('MASTER_CONTAINER as B', 'A.NO_CONTAINER', '=', 'B.NO_CONTAINER')
            ->select('A.NO_CONTAINER', DB::raw("'MTY' as STATUS"), 'B.SIZE_', 'B.TYPE_')
            ->where('A.NO_REQUEST', '=', $no_req)
            ->get();

        // Hitung biaya materai
        $bea_materai = DB::connection('uster')->table('nota_pnkn_stuf_d')
            ->select(DB::raw("TO_CHAR(BIAYA, '999,999,999,999') as BEA_MATERAI"), 'BIAYA')
            ->where('NO_NOTA', '=', $notanya)
            ->where('KETERANGAN', '=', 'MATERAI')
            ->value('BEA_MATERAI') ?? 0;

        if ($lunas == 'YES') {
            $mat = DB::connection('uster')->table('itpk_nota_header')
                ->where('NO_REQUEST', $no_req)
                ->first();

            $no_mat = $mat ? $mat->no_peraturan : null;
        } else {
            $mat = DB::connection('uster')->table('MASTER_MATERAI')
                ->where('STATUS', 'Y')
                ->first();

            $no_mat = $mat ? $mat->no_peraturan : null;
        }

        $cek_jenis = DB::connection('uster')->table('container_stuffing')
            ->where('no_request', $no_req)
            ->distinct()
            ->count('asal_cont');

        $r_jns = $cek_jenis > 1;

        $query_dtl = "SELECT a.JML_HARI,
                TO_CHAR(a.TARIF,'999,999,999,999') TARIF,
                TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA,
                a.KETERANGAN,
                a.HZ,a.JUMLAH_CONT JML_CONT,
                START_STACK,
                END_STACK,
                b.SIZE_,
                b.TYPE_,b.STATUS,urut
        FROM nota_pnkn_stuf_d a, iso_code b
        WHERE a.id_iso = b.id_iso
                AND a.no_nota = '$notanya'
                AND  a.KETERANGAN NOT IN ('ADMIN NOTA','MATERAI')
        ORDER BY urut ASC";
        $res = DB::connection('uster')->select($query_dtl);

        return [
            'data' => $data,
            'date' => $date,
            'detail' => $res,
            'nama_lengkap' => $nama_lengkap,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'rcont' => $rcont,
        ];
    }

    function recalcStuffing($request)
    {
        try {
            $req = $request->input('REQ');

            try {
                // Ambil nomor nota
                $rnota = DB::connection('uster')->select("select no_nota from nota_stuffing where no_request = :req and status <> 'BATAL'", ['req' => $req]);
                $no_nota = $rnota[0]->no_nota;

                // Mulai transaksi
                DB::connection('uster')->beginTransaction();

                // Eksekusi prosedur tersimpan
                DB::connection('uster')->statement("begin PACK_RECALC_NOTA.recalc_stuffing(:req, :no_nota); end;", ['req' => $req, 'no_nota' => $no_nota]);

                // Commit transaksi jika sukses
                DB::connection('uster')->commit();

                return 'OK';
            } catch (\Exception $e) {
                // Rollback transaksi jika terjadi kesalahan
                DB::connection('uster')->rollback();
                return $e->getMessage();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }



    function recalcStuffingPNKN($request)
    {
        $req = $request->input('REQ');

        try {
            // Ambil nomor nota
            $rnota = DB::connection('uster')->select("select no_nota from nota_pnkn_stuf where no_request = :req and status <> 'BATAL'", ['req' => $req]);
            $no_nota = $rnota[0]->no_nota;

            // Mulai transaksi
            DB::connection('uster')->beginTransaction();

            // Eksekusi prosedur tersimpan
            DB::connection('uster')->statement("begin PACK_RECALC_NOTA.recalc_pnknstuffing(:req, :no_nota); end;", ['req' => $req, 'no_nota' => $no_nota]);

            // Commit transaksi jika sukses
            DB::connection('uster')->commit();

            return 'OK';
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::connection('uster')->rollback();
            return $e->getMessage();
        }
    }



    function InsertProforma($request)
    {
        DB::beginTransaction();
        try {
            // Mulai transaksi
            $nipp   = session('LOGGED_STORAGE');
            $no_req = $request->input("no_req");
            $koreksi = $request->input("koreksi");

            $query_cek_nota     = "SELECT NO_NOTA, STATUS FROM NOTA_STUFFING WHERE NO_REQUEST = '$no_req'";
            $nota    = DB::connection('uster')->selectOne($query_cek_nota);
            $no_nota_cek        = $nota->no_nota ?? null;
            $no_status        = $nota->status ?? null;

            //cek no nota sudah ada atau belom
            if (($no_nota_cek != NULL && $no_status == 'BATAL') || ($no_nota_cek == NULL && $no_status == NULL)) {
                //Insert ke tabel nota

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
                $query_master    = "/* Formatted on 9/6/2012 3:13:17 AM (QP5 v5.163.1008.3004) */
                                              SELECT b.KD_PBM,
                                                     b.NM_PBM,
                                                     b.ALMT_PBM,
                                                     b.NO_NPWP_PBM,
                                                     COUNT (d.NO_CONTAINER) JUMLAH,
                                                     TO_CHAR(TGL_REQUEST,'dd/mon/yyyy') TGL_REQUEST
                                                FROM request_stuffing a,
                                                     v_mst_pbm b,
                                                     container_stuffing d
                                               WHERE     a.KD_CONSIGNEE = b.KD_PBM
                                                     AND a.NO_REQUEST = d.NO_REQUEST
                                                     AND a.NO_REQUEST = '$no_req'
                                                GROUP BY b.KD_PBM,b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM, TO_CHAR(TGL_REQUEST,'dd/mon/yyyy')";
                //echo $query_master;die;
                $master    = DB::connection('uster')->selectOne($query_master);
                $kd_pbm        = $master->kd_pbm;
                $nm_pbm        = $master->nm_pbm;
                $almt_pbm    = $master->almt_pbm;
                $npwp       = $master->no_npwp_pbm;
                $jumlah_cont      = $master->jumlah;
                $tgl_re      = $master->tgl_request;


                $total_        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN,(SUM(BIAYA)+SUM(PPN)) TOTAL_TAGIHAN FROM temp_detail_nota WHERE no_request = '$no_req'
                                            AND KETERANGAN NOT IN ('MATERAI')";
                /**Fauzan modif 31 AUG 2020 "AND KETERANGAN NOT IN ('MATERAI')"*/

                $total2        = DB::connection('uster')->selectOne($total_);
                $total_         = $total2->total;
                $ppn             = $total2->ppn;


                $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
                $row_adm        = DB::connection('uster')->selectOne($query_adm);
                $adm             = $row_adm->tarif;

                /*Fauzan add materai 31 Agustus 2020*/
                $query_materai        = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";
                $row_materai        = DB::connection('uster')->selectOne($query_materai);
                $materai            = $row_materai->bea_materai;
                /*end Fauzan add materai 31 Agustus 2020*/

                $tagihan         = $total2->total_tagihan + $materai;


                $tarif_pass = 0;



                if ($koreksi <> 'Y') {
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
                                                        PASS,
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
                                                        '$status_nota',
                                                        '$adm',
                                                        '$tarif_pass',
                                                        '$kd_pbm',
                                                        SYSDATE,
                                                        '$nota_lama',
                                                        '$no_nota_mti')";

                if (DB::connection('uster')->insert($query_insert_nota)) {
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

                    $query_detail    = "SELECT ID_ISO,TARIF,BIAYA,KETERANGAN,JML_CONT,HZ,TO_CHAR(START_STACK,'dd/mm/rrrr') START_STACK,TO_CHAR(END_STACK,'dd/mm/rrrr') END_STACK, JML_HARI, COA, PPN, URUT,TEKSTUAL  FROM temp_detail_nota WHERE no_request = '$no_req' ";

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
                        $ppn    = $item->ppn;
                        $urut    = $item->urut;
                        $tekstual = $item->tekstual;

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
                                                             PPN,
                                                             LINE_NUMBER,
                                                             URUT,
                                                             TEKSTUAL,
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
                                                            TO_DATE('$start','dd/mm/yyyy'),
                                                            TO_DATE('$end','dd/mm/yyyy'),
                                                            '$jml',
                                                            '$coa',
                                                            '$ppn',
                                                            '$i',
                                                            '$urut',
                                                            '$tekstual',
                                                            '$no_nota_mti')";
                        DB::connection('uster')->insert($query_insert);
                        $i++;
                    }

                    $update_nota = "UPDATE NOTA_STUFFING SET CETAK_NOTA = '1' WHERE NO_NOTA = '$no_nota'";
                    DB::connection('uster')->update($update_nota);

                    $update_aktif = "UPDATE plan_container_stuffing set AKTIF = 'T' where no_request = replace('$no_req','S','T') and tgl_approve IS NULL";
                    DB::connection('uster')->update($update_aktif);
                    $update_req = "UPDATE REQUEST_STUFFING SET NOTA = 'Y' WHERE NO_REQUEST = '$no_req'";
                    DB::connection('uster')->update($update_req);

                    $delete_temp = "DELETE from temp_detail_nota WHERE no_request = '$no_req'";
                    DB::connection('uster')->delete($delete_temp);

                    return "OK-INSERT";
                    DB::commit();
                }
            } else {
                DB::rollBack();
                return "OK";
            }
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            return $e->getMessage();
        }
    }

    function InsertProformaPNKN($request)
    {
        try {
            // Mulai transaksi
            DB::beginTransaction();

            $nipp   = session('LOGGED_STORAGE');
            $no_req = $request->input("no_req");
            $koreksi = $request->input("koreksi");

            $query_cek_nota     = "SELECT NO_NOTA, STATUS FROM NOTA_PNKN_STUF WHERE NO_REQUEST = '$no_req'";
            $nota    = DB::connection('uster')->selectOne($query_cek_nota);
            $no_nota_cek        = $nota->no_nota ?? null;
            $st_nota        = $nota->status ?? null;

            //cek no nota sudah ada atau belom
            if (($no_nota_cek == NULL && $st_nota == NULL) || ($no_nota_cek != NULL && $st_nota == 'BATAL')) {
                //Insert ke tabel nota

                $query_cek    = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA,10,15))+1),6,0), '000001') JUM_,
                                          TO_CHAR(SYSDATE, 'MM') AS MONTH,
                                          TO_CHAR(SYSDATE, 'YY') AS YEAR
                                    FROM NOTA_PNKN_STUF
                                   WHERE NOTA_PNKN_STUF.TGL_NOTA BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)";

                $jum_    = DB::connection('uster')->selectOne($query_cek);
                $jum        = $jum_->jum_;
                $month        = $jum_->month;
                $year        = $jum_->year;

                $no_nota    = "0405" . $month . $year . $jum;

                // Cek NO NOTA MTI
                // firman 20 agustus 2020
                $query_mti = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA_MTI,10,15))+1),6,0),'000001') JUM_,
                                       TO_CHAR(SYSDATE, 'YYYY') AS YEAR
                                       FROM MTI_COUNTER_NOTA WHERE TAHUN =  TO_CHAR(SYSDATE,'YYYY')";
                $jum_mti = DB::connection('uster')->selectOne($query_mti);
                $jum_nota_mti    = $jum_mti->jum_;
                $year_mti        = $jum_mti->year;
                $no_nota_mti    = "17." . $year_mti . "." . $jum_nota_mti;


                //select master pbm
                $query_master    = "/* Formatted on 9/6/2012 3:13:17 AM (QP5 v5.163.1008.3004) */
                                              SELECT b.KD_PBM,
                                                     b.NM_PBM,
                                                     b.ALMT_PBM,
                                                     b.NO_NPWP_PBM,
                                                     COUNT (d.NO_CONTAINER) JUMLAH,
                                                     TO_CHAR(TGL_REQUEST,'dd/mon/yyyy') TGL_REQUEST
                                                FROM request_stuffing a,
                                                     v_mst_pbm b,
                                                     container_stuffing d
                                               WHERE     a.ID_PENUMPUKAN = b.KD_PBM
                                                     AND a.NO_REQUEST = d.NO_REQUEST
                                                     AND a.NO_REQUEST = '$no_req'
                                                GROUP BY b.KD_PBM,b.NM_PBM, b.ALMT_PBM, b.NO_NPWP_PBM, TO_CHAR(TGL_REQUEST,'dd/mon/yyyy')";
                //echo $query_master;die;
                $master    = DB::connection('uster')->selectOne($query_master);
                $kd_pbm        = $master->kd_pbm;
                $nm_pbm        = $master->nm_pbm;
                $almt_pbm    = $master->almt_pbm;
                $npwp       = $master->no_npwp_pbm;
                $jumlah_cont      = $master->jumlah;
                $tgl_re      = $master->tgl_request;


                $total_        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN,(SUM(BIAYA)+SUM(PPN)) TOTAL_TAGIHAN FROM temp_detail_nota_i WHERE no_request = '$no_req' AND KETERANGAN NOT IN ('MATERAI')";
                $total2        = DB::connection('uster')->selectOne($total_);
                $total_         = $total2->total;
                $ppn             = $total2->ppn;

                $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
                $row_adm        = DB::connection('uster')->selectOne($query_adm);
                $adm             = $row_adm->tarif;

                /*Fauzan add materai 31 Agustus 2020*/
                $query_materai        = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota_i WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";
                $row_materai        = DB::connection('uster')->selectOne($query_materai);
                $materai            = $row_materai->bea_materai;
                /*end Fauzan add materai 31 Agustus 2020*/

                $tagihan         = $total2->total_tagihan + $materai;
                /*end Fauzan add materai 31 Agustus 2020*/


                $tarif_pass = 0;


                if ($koreksi <> 'Y') {
                    $status_nota = 'NEW';
                    $nota_lama = '';
                } else {
                    $status_nota = 'KOREKSI';
                    $faktur         = "SELECT NO_NOTA, NO_FAKTUR, KD_EMKL FROM NOTA_PNKN_STUF WHERE NO_REQUEST = '$no_req' AND NO_NOTA =(SELECT MAX(NO_NOTA) FROM NOTA_PNKN_STUF WHERE NO_REQUEST = '$no_req')";
                    $faktur_        = DB::connection('uster')->selectOne($faktur);
                    $nota_lama    = $faktur_->no_faktur;

                    $update = "UPDATE NOTA_PNKN_STUF SET STATUS = 'BATAL' WHERE NO_NOTA = '$nota_lama'";
                    DB::connection('uster')->update($update);
                }
                $query_insert_nota    = "INSERT INTO NOTA_PNKN_STUF(NO_NOTA,
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
                                                        PASS,
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
                                                        '$status_nota',
                                                        '$adm',
                                                        '$tarif_pass',
                                                        '$kd_pbm',
                                                        SYSDATE,
                                                        '$nota_lama',
                                                        '$no_nota_mti')";

                //echo $query_insert_nota;die;
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


                    $query_detail    = "SELECT ID_ISO,TARIF,BIAYA,KETERANGAN,JML_CONT,HZ,TO_CHAR(START_STACK,'dd/mm/rrrr') START_STACK,TO_CHAR(END_STACK,'dd/mm/rrrr') END_STACK, JML_HARI, COA, PPN, URUT,TEKSTUAL  FROM temp_detail_nota_i WHERE no_request = '$no_req' ";
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
                        $ppn    = $item->ppn;
                        $urut    = $item->urut;
                        $tekstual = $item->tekstual;

                        $query_insert    = "INSERT INTO NOTA_PNKN_STUF_D
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
                                                             PPN,
                                                             LINE_NUMBER,
                                                             URUT,
                                                             TEKSTUAL,
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
                                                            TO_DATE('$start','dd/mm/yyyy'),
                                                            TO_DATE('$end','dd/mm/yyyy'),
                                                            '$jml',
                                                            '$coa',
                                                            '$ppn',
                                                            '$i',
                                                            '$urut',
                                                            '$tekstual',
                                                            '$no_nota_mti')";
                        DB::connection('uster')->insert($query_insert);
                        // $db4->query($query_insert);


                        $i++;
                    }


                    $update_nota = "UPDATE NOTA_PNKN_STUF SET CETAK_NOTA = '1' WHERE NO_NOTA = '$no_nota'";
                    DB::connection('uster')->update($update_nota);

                    $update_aktif = "UPDATE plan_container_stuffing set AKTIF = 'T' where no_request = replace('$no_req','S','T') and tgl_approve IS NULL";
                    DB::connection('uster')->update($update_aktif);
                    $update_req = "UPDATE REQUEST_STUFFING SET NOTA_PNKN = 'Y' WHERE NO_REQUEST = '$no_req'";
                    DB::connection('uster')->update($update_req);

                    $delete_temp = "DELETE from temp_detail_nota_i WHERE no_request = '$no_req'";
                    DB::connection('uster')->delete($delete_temp);

                    return "OK-INSERT";
                    DB::commit();
                }
            } else {
                DB::rollBack();
                return "OK";
            }
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
