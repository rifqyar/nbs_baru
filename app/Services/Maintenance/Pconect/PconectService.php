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

    

    function MstPelanggan($data,$kd_pbm)
    {
        $nompwp = preg_replace('/\D/', '', $data->npwp);
        $query             = "SELECT *
        FROM mst_pelanggan ORDER BY NO_ACCOUNT_PBM DESC";
        $result_query    = DB::connection('uster')->select($query);

        $getdata=collect($result_query)->first();
        $noaccoutpbm= $getdata->no_account_pbm + 1;
       
        //dd($result_query);
        foreach ($result_query as $value) {
            $nompwpmst = preg_replace('/\D/', '', $value->no_npwp_pbm);
            
            if ($nompwpmst == $data->npwp) {
                //dd('update');
                $query = "UPDATE mst_pelanggan
                SET NM_PBM = '$data->nama', ALMT_PBM = '$data->alamat', NO_NPWP_PBM = '$data->npwp', NO_NPWP_PBM16 = '$data->new_npwp'
                WHERE KD_PBM = '$value->kd_pbm'";
                return true; 
                
            }
            else
            {
                //dd('insert');
                $query = "INSERT INTO mst_pelanggan (KD_PBM,NO_NPWP_PBM, NM_PBM, ALMT_PBM,NO_NPWP_PBM16,NO_ACCOUNT_PBM)
                VALUES ('$kd_pbm','$nompwp', '$data->nama', '$data->alamat','$data->new_npwp','$noaccoutpbm')";
                $result_query = DB::connection('uster')->insert($query);
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
