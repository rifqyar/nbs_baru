<?php

namespace App\Services\Billing\TransferSIMKEU;

use Exception;
use Illuminate\Support\Facades\DB;

class TransferSimkeuService
{
    public function getData($request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;
        $no_nota = $request->no_nota;

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'YYYY/MM/DD'");
        $query_list1     = "SELECT TO_CHAR(TO_DATE('$tgl_awal','YYYY/MM/DD'),'YYYY/MM/DD') tgl_awal,  TO_CHAR(TO_DATE('$tgl_akhir','YYYY/MM/DD'),'dd/mon/rrrr') tgl_akhir  FROM dual";

        $row_list1        = DB::connection('uster')->selectOne($query_list1);
        $tgl_awal_         = $row_list1->tgl_awal;
        $tgl_akhir_        = $row_list1->tgl_akhir;

        $whereNoNota = '';
        $whereDate = '';
        if ($no_nota != null) {
            $whereNoNota = "AND a.TRX_NUMBER LIKE '$no_nota%'";
        }

        if ($tgl_awal != null && $tgl_akhir != null) {
            $whereDate = "AND TO_DATE( TRX_DATE, 'YYYY/MM/DD')  between to_date('$tgl_awal','YYYY/MM/DD') and to_date('$tgl_akhir','YYYY/MM/DD')";
        }

        $query_list2 = "SELECT a.TRX_NUMBER NO_NOTA,
                             a.NO_REQUEST,
                             TO_CHAR (a.KREDIT, '999,999,999,999') TOTAL_TAGIHAN,
                             b.NM_PBM NM_EMKL,
                             d.NAMA_NOTA NAMA_MODUL,
                             TO_CHAR (a.TRX_DATE, 'YYYY/MM/DD') TGL_KEGIATAN,
                             CASE a.STATUS_AR
                              WHEN 'S' THEN 'SUKSES'
                              WHEN 'F' THEN 'GAGAL'
                              ELSE 'BELUM DITRANSFER'
                            END SIMKEU_PROSES_AR,
                            CASE a.STATUS_RECEIPT
                              WHEN 'S' THEN 'SUKSES'
                              WHEN 'F' THEN 'GAGAL'
                              ELSE 'BELUM DITRANSFER'
                            END SIMKEU_PROSES_RECEIPT
                        FROM ITPK_NOTA_HEADER a, v_mst_pbm b, MASTER_NOTA_ITPK d
                       WHERE  a.JENIS_NOTA = d.KODE_NOTA
                       AND a.CUSTOMER_NUMBER = b.NO_ACCOUNT_PBM
                       AND b.KD_CABANG='05'
                       AND b.ALMT_PBM IS NOT NULL
                       $whereDate
                       AND a.status in ('2','4a','4b')
                       $whereNoNota";

        $query = '(' . $query_list2 . ') x';

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'YYYY/MM/DD'");
        $data = DB::connection('uster')->table(DB::raw($query))->select()->get();

        // DB::connection('uster')->table(DB::raw($query))->orderBy('x.TGL_KEGIATAN', 'desc')->chunk(20, function ($chunk) use (&$data) {
        //     foreach ($chunk as $dt) {
        //         $data[] = $dt;
        //     }
        // });

        return $data;
    }

    public function doTransfer($request)
    {
        DB::beginTransaction();
        try {
            $tgl_awal    = $request->tgl_awal;
            $tgl_akhir    = $request->tgl_akhir;
            $no_nota    = $request->no_nota;

            if ($no_nota != '' || $no_nota != NULL) {
                $type_transfer = 'SINGLE';
            } else {
                $type_transfer = 'TANGGAL';
            }

            $param_payment = array(
                "TGL_AWAL" => "$tgl_awal",
                "TGL_AKHIR" => "$tgl_akhir",
                "NOTA_BERHASIL" => "",
                "NOTA_GAGAL" => "",
                "NOTA_SINGLE" => "$no_nota",
                "TYPE_TRANSFER" => "$type_transfer"
            );

            $query = "declare begin USTER.uster_transfer_ipctpk(:TGL_AWAL,:TGL_AKHIR,:NOTA_BERHASIL,:NOTA_GAGAL,:NOTA_SINGLE,:TYPE_TRANSFER); end;";

            $paramif = DB::connection('uster')->statement($query, $param_payment);

            $out_noreq = $paramif["out_noreq"];
            $out_msg   = $paramif["out_msg"];

            DB::commit();
            return response()->json([
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ], 'data' => [
                    'nota_success' => 'Nota Berhasil Transfer = '.$param_payment["NOTA_BERHASIL"],
                    'nota_failed' => 'Nota Gagal Transfer = '.$param_payment["NOTA_GAGAL"],
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
}
