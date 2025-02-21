<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CancelInvoiceController extends Controller
{
    function index()
    {
        return view('maintenance.cancel-invoice');
    }

    function invoiceBackdate()
    {
        return view('maintenance.cancel-invoice-backdate');
    }

    function getNota(Request $request)
    {
        $no_nota = $request->term;
        $query             = "SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'RECEIVING' KEGIATAN FROM NOTA_RECEIVING WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER,'DELIVERY' KEGIATAN FROM NOTA_DELIVERY WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'STUFFING' KEGIATAN FROM NOTA_STUFFING WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI' )
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'STRIPPING' KEGIATAN FROM NOTA_STRIPPING WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'RELOKASI' KEGIATAN FROM NOTA_RELOKASI WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'STRIPPING' KEGIATAN FROM NOTA_RELOKASI_MTY WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'PNKN_DEL' KEGIATAN FROM NOTA_PNKN_DEL WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER,'PNKN_STUF' KEGIATAN FROM NOTA_PNKN_STUF WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TGL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER,'BATAL_MUAT' KEGIATAN FROM NOTA_BATAL_MUAT WHERE NO_NOTA_MTI = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')";

        return DB::connection('uster')->select($query);
    }

    function getNotaFaktur(Request $request)
    {
        $no_nota = $request->term;
        $query  = "SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'RECEIVING' KEGIATAN FROM NOTA_RECEIVING WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER,'DELIVERY' KEGIATAN FROM NOTA_DELIVERY WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'STUFFING' KEGIATAN FROM NOTA_STUFFING WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI' )
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'STRIPPING' KEGIATAN FROM NOTA_STRIPPING WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'RELOKASI' KEGIATAN FROM NOTA_RELOKASI WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'STRIPPING' KEGIATAN FROM NOTA_RELOKASI_MTY WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER, 'PNKN_DEL' KEGIATAN FROM NOTA_PNKN_DEL WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TANGGAL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER,'PNKN_STUF' KEGIATAN FROM NOTA_PNKN_STUF WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')
        UNION
        SELECT NO_NOTA, NO_FAKTUR, NO_REQUEST, EMKL, ALAMAT, NPWP, TOTAL_TAGIHAN, PPN, TAGIHAN TOTAL, NO_FAKTUR, TRANSFER, LUNAS, TO_CHAR(TGL_LUNAS,'dd/mm/yyyy') TGL_LUNAS, NVL(TRANSFER, '-') TRANSFER,'BATAL_MUAT' KEGIATAN FROM NOTA_BATAL_MUAT WHERE NO_FAKTUR = '$no_nota' AND (STATUS = 'NEW' OR STATUS = 'PERP' OR STATUS = 'KOREKSI')";

        return DB::connection('uster')->select($query);
    }

    function storeCancel(Request $request)
    {
        $nm_user    = session("NAME");
        $id_user    = session("LOGGED_STORAGE");
        $no_nota     = $request->NO_NOTA;
        $trx_number     = $request->TRX_NUMBER;
        $no_request = $request->NO_REQUEST;
        $kegiatan = $request->KEGIATAN;
        $keterangan = $request->KETERANGAN;


        // $cancel = cancelInvoice($no_request, 'cancel invoice');
        $cancel['code'] = '1';
        DB::beginTransaction();
        try {
            if ($cancel['code'] == '1') {
                if ($kegiatan == 'STRIPPING') {
                    $query_add4 = "UPDATE REQUEST_STRIPPING SET NOTA = 'N', NOTA_PNKN='N', KOREKSI = 'Y', TGL_KOREKSI = SYSDATE, ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                    $query_up4 = "UPDATE NOTA_STRIPPING SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
                    $query_up7 = "UPDATE NOTA_RELOKASI_MTY SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

                    DB::connection('uster')->update($query_add4);
                    DB::connection('uster')->update($query_up7);
                    DB::connection('uster')->update($query_up4);
                }
                if ($kegiatan == 'STUFFING') {
                    $query_add3 = "UPDATE REQUEST_STUFFING SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = SYSDATE, ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                    DB::connection('uster')->select($query_add3);
                } else if ($kegiatan == 'PNKN_STUF') {
                    $query_add3_ = "UPDATE REQUEST_STUFFING SET NOTA_PNKN = 'N', KOREKSI_PNKN = 'Y', TGL_KOREKSI = SYSDATE, ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                    DB::connection('uster')->select($query_add3_);
                }

                if ($kegiatan == 'PNKN_DEL') {
                    $query_add2 = "UPDATE REQUEST_DELIVERY SET NOTA_PNKN = 'N', KOREKSI_PNKN = 'Y', TGL_KOREKSI = SYSDATE, ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                    DB::connection('uster')->select($query_add2);
                } else if ($kegiatan == 'DELIVERY') {
                    $query_add2 = "UPDATE REQUEST_DELIVERY SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = SYSDATE, ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                    DB::connection('uster')->select($query_add2);
                }
                if ($kegiatan == 'BATAL_MUAT') {
                    $q_batal = "UPDATE REQUEST_BATAL_MUAT SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = SYSDATE, ID_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                    DB::connection('uster')->select($q_batal);
                    $query_up5_ = "UPDATE NOTA_BATAL_MUAT SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
                    DB::connection('uster')->select($query_up5_);
                }

                $query_add1 = "UPDATE REQUEST_RECEIVING SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = SYSDATE, ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";

                $query_add5 = "UPDATE REQUEST_RELOKASI SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = SYSDATE, ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                $query_up1 = "UPDATE NOTA_RECEIVING SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
                $query_up2 = "UPDATE NOTA_DELIVERY SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
                $query_up3 = "UPDATE NOTA_STUFFING SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

                $query_up5 = "UPDATE NOTA_RELOKASI SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";


                $mat = "SELECT B.NO_PERATURAN, B.SALDO,B.TERPAKAI,A.KREDIT FROM ITPK_NOTA_HEADER  A INNER JOIN MASTER_MATERAI B ON A.NO_PERATURAN=B.NO_PERATURAN where A.TRX_NUMBER='" . $trx_number . "'";

                $mat3 = DB::connection('uster')->selectOne($mat);
                $no_matx    = $mat3->no_peraturan ?? NULL;
                $saldo = $mat3->saldo ?? NULL;
                $terpakai = $mat3->terpakai ?? NULL;
                $jum = $mat3->kredit ?? NULL;
                $no_nota = $mat3->no_nota ?? NULL;

                $jum1 = $jum - 10000;

                $cek_itpk     = "SELECT COUNT(*) JUM FROM NOTA_ALL_H WHERE NO_FAKTUR = '$trx_number'";
                $ritpk         = DB::connection('uster')->selectOne($cek_itpk);
                if ($ritpk->jum == 0) {

                    if ($jum1 > 5000000) {
                        $query_up20 = "UPDATE MASTER_MATERAI SET SALDO = SALDO + 10000, TERPAKAI = TERPAKAI - 10000 WHERE NO_PERATURAN='$no_matx'";
                    } else {
                        $query_up20 = "UPDATE MASTER_MATERAI SET SALDO = SALDO - 0, TERPAKAI = TERPAKAI + 0 WHERE NO_PERATURAN='$no_matx'";
                    }
                    $query_up6 = "UPDATE ITPK_NOTA_HEADER SET STATUS = '5' WHERE TRX_NUMBER = '$trx_number'";
                } else {
                    $query_up6 = "UPDATE NOTA_ALL_H SET STATUS = 'BATAL' WHERE NO_NOTA = '$no_nota'";
                }

                $query_up8 = "UPDATE NOTA_PNKN_DEL SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
                $query_up9 = "UPDATE NOTA_PNKN_STUF SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

                $query_up4 = "UPDATE NOTA_STRIPPING SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
                $query_up7 = "UPDATE NOTA_RELOKASI_MTY SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

                DB::connection('uster')->update($query_up7);
                DB::connection('uster')->update($query_up4);

                DB::connection('uster')->update($query_add1);


                DB::connection('uster')->update($query_add5);
                DB::connection('uster')->update($query_up1);
                DB::connection('uster')->update($query_up2);
                DB::connection('uster')->update($query_up3);
                DB::connection('uster')->update($query_up5);
                DB::connection('uster')->update($query_up6);

                DB::connection('uster')->update($query_up8);
                DB::connection('uster')->update($query_up9);
                DB::connection('uster')->update($query_up20);
                DB::connection('uster')->commit();
                echo 'sukses';
                die;
            } else {
                DB::connection('uster')->rollBack();
                echo 'Gagal Merubah Data';
                die;
            }
        } catch (Exception $th) {
            DB::connection('uster')->rollBack();
            echo $th->getMessage();
        }
    }

    function storeCancelBackdate(Request $request)
    {
        $nm_user    = session("NAME");
        $id_user    = session("LOGGED_STORAGE");
        $no_nota     = $request->NO_NOTA;
        $no_request = $request->NO_REQUEST;
        $keterangan = $request->KETERANGAN;
        $kegiatan = $request->KEGIATAN;
        $TGL_REAL = date("d-m-Y", strtotime($request->TGL_REAL));

        DB::beginTransaction();
        try {

            if ($kegiatan == 'STRIPPING') {
                $query_add4 = "UPDATE REQUEST_STRIPPING SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = TO_DATE('$TGL_REAL','dd-mm-rrrr'), ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                $query_up4 = "UPDATE NOTA_STRIPPING SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
                $query_up7 = "UPDATE NOTA_RELOKASI_MTY SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

                DB::connection('uster')->update($query_add4);
                DB::connection('uster')->update($query_up7);
                DB::connection('uster')->update($query_up4);
            }
            if ($kegiatan == 'STUFFING') {
                $query_add3 = "UPDATE REQUEST_STUFFING SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = TO_DATE('$TGL_REAL','dd-mm-rrrr'), ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                DB::connection('uster')->update($query_add3);
            } else if ($kegiatan == 'PNKN_STUF') {
                $query_add3_ = "UPDATE REQUEST_STUFFING SET NOTA_PNKN = 'N', KOREKSI_PNKN = 'Y', TGL_KOREKSI = TO_DATE('$TGL_REAL','dd-mm-rrrr'), ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                DB::connection('uster')->update($query_add3_);
            }

            if ($kegiatan == 'PNKN_DEL') {
                $query_add2 = "UPDATE REQUEST_DELIVERY SET NOTA_PNKN = 'N', KOREKSI_PNKN = 'Y', TGL_KOREKSI = TO_DATE('$TGL_REAL','dd-mm-rrrr'), ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                DB::connection('uster')->update($query_add2);
            } else if ($kegiatan == 'DELIVERY') {
                $query_add2 = "UPDATE REQUEST_DELIVERY SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = TO_DATE('$TGL_REAL','dd-mm-rrrr'), ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                DB::connection('uster')->update($query_add2);
            }
            if ($kegiatan == 'BATAL_MUAT') {
                $q_batal = "UPDATE REQUEST_BATAL_MUAT SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = TO_DATE('$TGL_REAL','dd-mm-rrrr'), ID_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
                DB::connection('uster')->update($q_batal);

                $query_up5_ = "UPDATE NOTA_BATAL_MUAT SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
                DB::connection('uster')->update($query_up5_);
            }
            $query_add1 = "UPDATE REQUEST_RECEIVING SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = TO_DATE('$TGL_REAL','dd-mm-rrrr'), ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";

            $query_add5 = "UPDATE REQUEST_RELOKASI SET NOTA = 'N', KOREKSI = 'Y', TGL_KOREKSI = TO_DATE('$TGL_REAL','dd-mm-rrrr'), ID_USER_KOREKSI = '$id_user' WHERE NO_REQUEST = '$no_request'";
            $query_up1 = "UPDATE NOTA_RECEIVING SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
            $query_up2 = "UPDATE NOTA_DELIVERY SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
            $query_up3 = "UPDATE NOTA_STUFFING SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

            $query_up5 = "UPDATE NOTA_RELOKASI SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

            $trx_number = '';
            $cek_itpk     = "SELECT COUNT(*) JUM FROM NOTA_ALL_H WHERE NO_FAKTUR = '$trx_number'";
            $ritpk         = DB::connection('uster')->selectOne($cek_itpk);
            if ($ritpk->jum == 0) {
                $query_up6 = "UPDATE ITPK_NOTA_HEADER SET STATUS = '5' WHERE TRX_NUMBER = '$trx_number'";
            } else {
                $query_up6 = "UPDATE NOTA_ALL_H SET STATUS = 'BATAL' WHERE NO_NOTA = '$no_nota'";
            }

            $query_up8 = "UPDATE NOTA_PNKN_DEL SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
            $query_up9 = "UPDATE NOTA_PNKN_STUF SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

            $query_up4 = "UPDATE NOTA_STRIPPING SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";
            $query_up7 = "UPDATE NOTA_RELOKASI_MTY SET STATUS = 'BATAL', KET_KOREKSI = '$keterangan' WHERE NO_NOTA = '$no_nota'";

            DB::connection('uster')->update($query_up7);
            DB::connection('uster')->update($query_up4);

            DB::connection('uster')->update($query_add1);

            DB::connection('uster')->update($query_add5);
            DB::connection('uster')->update($query_up1);
            DB::connection('uster')->update($query_up2);
            DB::connection('uster')->update($query_up3);
            DB::connection('uster')->update($query_up5);
            DB::connection('uster')->update($query_up6);

            DB::connection('uster')->update($query_up8);
            DB::connection('uster')->update($query_up9);


            echo 'sukses';
            DB::connection('uster')->rollBack();
        } catch (Exception $th) {
            DB::connection('uster')->rollBack();
            echo $th->getMessage();
        }
    }
}
