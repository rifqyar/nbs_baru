<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SendDeliveryTPKController extends Controller
{
    function index()
    {
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
            switch ($jenis) {
                case 'RECEIVING':
                    $result = DB::connection('uster')->table('nota_receiving')
                        ->select('tanggal_lunas', 'lunas')
                        ->where('no_request', $id_req)
                        ->first();

                    if ($result && !empty($result->tanggal_lunas) && $result->lunas == 'YES') {
                        $lunas = true;
                    }
                    break;
                case 'STUFFING':
                    $result = DB::connection('uster')->table('nota_stuffing')
                        ->select('tanggal_lunas', 'lunas')
                        ->where('no_request', $id_req)
                        ->first();

                    $result_pnkn = DB::connection('uster')->table('nota_pnkn_stuf')
                        ->select('tanggal_lunas', 'lunas')
                        ->where('no_request', $id_req)
                        ->first();

                    if (
                        $result && !empty($result->tanggal_lunas) && $result->lunas == 'YES' &&
                        $result_pnkn && !empty($result_pnkn->tanggal_lunas) && $result_pnkn->lunas == 'YES'
                    ) {
                        $lunas = true;
                    }
                    break;
                case 'STRIPPING':
                case 'PERP_STRIP':
                    $result = DB::connection('uster')->table('nota_stripping')
                        ->select('tanggal_lunas', 'lunas')
                        ->where('no_request', $id_req)
                        ->first();

                    if ($result && !empty($result->tanggal_lunas) && $result->lunas == 'YES') {
                        $lunas = true;
                    }
                    break;
                case 'DELIVERY':
                    $result = DB::connection('uster')->table('nota_delivery')
                        ->select('tanggal_lunas', 'lunas')
                        ->where('no_request', $id_req)
                        ->first();

                    if ($result && !empty($result->tanggal_lunas) && $result->lunas == 'YES') {
                        $lunas = true;
                    }
                    break;
                case 'BATAL_MUAT':
                    $result = DB::connection('uster')->table('nota_batal_muat')
                        ->select('tgl_lunas', 'lunas')
                        ->where('no_request', $id_req)
                        ->first();

                    if ($result && !empty($result->tgl_lunas) && $result->lunas == 'YES') {
                        $lunas = true;
                    }
                    break;
                default:
                    $lunas = false;
                    break;
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

    public function savePaymentExternal(Request $request)
    {
        try {
            $response = save_payment_uster($request->all());
            $result = $response instanceof \Illuminate\Http\JsonResponse ? $response->getData(true) : $response;
            if (isset($result['code']) && ($result['code'] == 500 || $result['code'] == 0)) {
                throw new \Exception($result['msg'] ?? 'Internal Server Error');
            }

            return response()->json([
                'code' => '1',
                'msg' => 'Payment saved successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => '0',
                'msg' => 'Failed to save payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
