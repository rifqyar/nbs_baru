<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SendDeliveryTPKController extends Controller
{
    function index() {
        return view('maintenance.send-delivery-tpk');
    }

    public function checkLunas(Request $request)
    {
        $jenis = $request->input('JENIS');
        $id_req = $request->input('ID_REQUEST');
        $bankAccountNumber = $request->input('BANK_ACCOUNT_NUMBER');
        $paymentCode = $request->input('PAYMENT_CODE');

        $lunas = false;

        if (!empty($jenis)) {
            $tables = [
                'NOTA_RECEIVING',
                'NOTA_STUFFING',
                'NOTA_PNKN_STUF',
                'NOTA_STRIPPING',
                'NOTA_DELIVERY',
                'NOTA_BATAL_MUAT',
                'NOTA_RELOKASI_MTY'
            ];

            foreach ($tables as $table) {
                $query = DB::connection('uster')->table($table)->select('TANGGAL_LUNAS', 'LUNAS')->where('NO_REQUEST', $id_req)->first();
                
                if ($query && !empty($query->TANGGAL_LUNAS) && $query->LUNAS == 'YES') {
                    $lunas = true;
                    break; // No need to continue checking other tables if one is found
                }
            }
        }

        if ($lunas) {
            return response()->json([
                'code' => '1',
                'msg' => 'Request Lunas'
            ]);
        } else {
            return response()->json([
                'code' => '0',
                'msg' => 'Request Belum lunas'
            ]);
        }
    }
}
