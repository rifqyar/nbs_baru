<?php

namespace App\Services\Maintenance\Pconect;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use PDO;

class PconectService
{
    function getNoNpwp(Request $request)
    {
        //dd($request);
        $nompwp = preg_replace('/\D/', '', $request->no_npwp);
        $query = "SELECT *
            FROM mti_customer_ss.sap_customer
            WHERE mti_customer_ss.sap_customer.npwp = '$nompwp'";

        $result_query = DB::connection('uster')->select($query);

        return $result_query;
    }



    function MstPelanggan($data, $kd_pbm)
    {
        $nompwp = preg_replace('/\D/', '', $data->npwp);
        $query             = "SELECT *
        FROM mst_pelanggan ORDER BY NO_ACCOUNT_PBM DESC";
        $result_query    = DB::connection('uster')->select($query);

        $getdata = collect($result_query)->first();
        $noaccoutpbm = $getdata->no_account_pbm + 1;

        //dd($result_query);
        foreach ($result_query as $value) {
            $nompwpmst = preg_replace('/\D/', '', $value->no_npwp_pbm);
            $data->alamat = str_replace("'", "''", $data->alamat);
            if ($nompwpmst == $data->npwp) {
                //dd('update');
                $query = "UPDATE mst_pelanggan
                SET NM_PBM = '$data->nama', ALMT_PBM = '$data->alamat', NO_NPWP_PBM = '$data->npwp', NO_NPWP_PBM16 = '$data->new_npwp'
                WHERE KD_PBM = '$value->kd_pbm'";
                return true;
            } else {
                //dd('insert');
                $query = "INSERT INTO mst_pelanggan (KD_PBM,NO_NPWP_PBM, NM_PBM, ALMT_PBM,NO_NPWP_PBM16,NO_ACCOUNT_PBM, PELANGGAN_AKTIF, KD_CABANG, UPDATE_DATE)
                VALUES ('$kd_pbm','$nompwp', '$data->nama', '$data->alamat','$data->new_npwp','$noaccoutpbm', '1', '05', SYSDATE)";
                $result_query = DB::connection('uster')->insert($query);

                $queryBilling = "INSERT INTO BILLING_NBS.MST_PELANGGAN
                (KODE_CABANG, NAMA_CABANG, KD_PELANGGAN, NAMA_PERUSAHAAN, KELOMPOK_PELANGGAN, ALAMAT_PERUSAHAAN, KOTA_PERUSAHAAN, KODE_POS_PERUSAHAAN, EMAIL_PERUSAHAAN, BIDANG_USAHA, GRUP_USAHA, TINGKAT_ORGANISASI, NAMA_PENANGGUNG_JAWAB, JABATAN_PENANGGUNG_JAWAB, TL_PENANGGUNG_JAWAB, TGL_LAHIR_PENANGGUNG_JAWAB, ALAMAT_KTP, KOTA_PENANGGUNG_JAWAB, KODE_POS_PENANGGUNG_JAWAB, TELP_PENANGGUNG_JAWAB, TELP_PERUSAHAAN, FAX_PERUSAHAAN, ALAMAT_KANTOR_PJ, KOTA_KANTOR_PJ, KODE_POS_KANTOR_PJ, TELP_KANTOR_PJ, EMAIL_KANTOR_PJ, NO_KTP_PJ, NO_SIM_PJ, NO_PASPOR_PJ, NO_LAIN_PJ, ALAMAT_PENAGIHAN, JANGKA_WAKTU, LAMP_KTP, LAMP_SIM, LAMP_PASPOR, LAMP_LAIN, AGAMA, JENIS_KELAMIN, JASA_KAPAL, JASA_BARANG, JASA_RUPA, JASA_LAIN, KD_CABANG, STATUS_PELANGGAN, NO_NPWP, CMS_FLAG, CREATED_DATE, CREATED_BY, UPDATE_DATE, UPDATED_BY, IS_IN_VIEW, KODE_PROSES, KD_AGEN, KD_PBM, NEGARA, KD_PPN_AGEN, KD_THREE_PARTIED, KD_GUDANG1, KD_GUDANG2, BENDERA, NM_SINGKAT, MS_BERLAKU_KTP, MS_BERLAKU_SIM, MS_BERLAKU_PASPOR, MS_BERLAKU_LAIN, PELANGGAN_AKTIF)
                VALUES ('PTK', 'Pontianak', '$noaccoutpbm', '$data->nama', '04', '$data->alamat', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$data->new_npwp', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1)";
                $result_query = DB::connection('uster')->insert($queryBilling);
                return true;
            }
        }


        return true;
    }


    function getviewData($no_npwp)
    {
        $nompwp = preg_replace('/\D/', '', $no_npwp);
        $query = "SELECT *
            FROM mti_customer_ss.sap_customer
            WHERE mti_customer_ss.sap_customer.npwp = '$nompwp'";

        $result_query = DB::connection('uster')->select($query);

        return $result_query;
    }
}
