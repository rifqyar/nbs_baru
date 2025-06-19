<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ResendPrayaController extends Controller
{
    public function resendPraya(Request $request)
    {
        $jenis = $request->input('JENIS');
        $id_req = $request->input('ID_REQUEST');
        $bankAccountNumber = $request->input('BANK_ACCOUNT_NUMBER');
        $paymentCode = $request->input('PAYMENT_CODE');

        $lunasResponse = $this->checkLunas($request);
        $lunasData = $lunasResponse->getData(true);

        if ($lunasData['code'] !== '1') {
            return response()->json([
                'code' => '0',
                'msg' => 'Request belum lunas, tidak dapat resend Praya',
                'data' => [
                    'jenis' => $jenis,
                    'id_request' => $id_req,
                    'bank_account_number' => $bankAccountNumber,
                    'payment_code' => $paymentCode
                ]
            ]);
        }

        $payload = new Request([
            'JENIS' => $jenis,
            'ID_REQUEST' => $id_req,
            'BANK_ACCOUNT_NUMBER' => $bankAccountNumber,
            'PAYMENT_CODE' => $paymentCode
        ]);

        $response = $this->savePaymentExternal($payload);
        $result = $response->getData(true);

        if ($result['code'] !== '1') {
            return response()->json([
                'code' => '0',
                'msg' => 'Failed to save payment: ' . ($result['msg'] ?? 'Unknown error'),
                'data' => [
                    'jenis' => $jenis,
                    'id_request' => $id_req,
                    'bank_account_number' => $bankAccountNumber,
                    'payment_code' => $paymentCode
                ]
            ]);
        } else {
            return response()->json([
                'code' => '1',
                'msg' => 'Praya request has been resent successfully',
                'data' => [
                    'jenis' => $jenis,
                    'id_request' => $id_req,
                    'bank_account_number' => $bankAccountNumber,
                    'payment_code' => $paymentCode
                ]
            ]);
        }
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
            if (isset($result['code']) && ($result['code'] == 500 || ($result['code'] == '0'))) {
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

    public function checkKoneksiBackend()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_PORT => "8013",
            CURLOPT_URL => "https://praya.ilcs.co.id:8013/api/getVessel?pol=IDPNK&eta=&etd=&orgId=2&terminalId=622&search=ICON",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "[]",
            CURLOPT_COOKIE => "X-Oracle-BMC-LBS-Route=f740610cd85b7f9f8d355e25c346298586e573fe",
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6ImFkbWlubmJzIiwiYmlsbGVySWQiOiIwMDAwOSIsImFjY2Vzc0xpc3QiOlsiREVMSVZFUlkiLCJSRUFMSVpBVElPTiBESVNDSEFSR0UgTE9BRCIsIlJFRVhQT1JUIiwiUEVSSU9ERSBUUkFOU0FDVElPTiIsIkNPTlRBSU5FUiBISVNUT1JZIiwiTElTVCBDT05UQUlORVIgQlkgU1RBVFVTIiwiVVBFUiBCTSIsIlNFRSIsIlRJQ0tFVCAmIFRSQU5TUE9SVCIsIlRDQSIsIkJFSEFORExFIiwiSU5DT01FIFNURVZFRE9SSU5HIiwiUkVBTElaQVRJT04iLCJUVEQgTUFOQUdFUiIsIlJFUE9SVCBNQU5BR0VNRU5UIiwiTE9BRElORyBDQU5DRUwiLCJFWFRSQSBNT1ZFTUVOVCIsIkFQUFJPVkFMIiwiVElDS0VUIE1BTkFHRU1FTlQiLCJSRVFVRVNUIEJPT0tJTkciLCJSRUNFSVZJTkciLCJERUxJVkVSWSBFWFRFTlNJT04iLCJJTkNPTUUgUkVDRUlWSU5HL0RFTElWRVJZIiwiTU9OSVRPUklORyJdLCJzdGF0dXNBcHAiOiJXZWIiLCJ2ZXJzaW9uU2VydmljZSI6IiIsImlhdCI6MTc1MDMwNzUzOSwiZXhwIjoxNzUwMzM2MzM5fQ.gZegQffk4FdePfPucM95sFGyR0Ba_KHqq03Hx5uRrog",
                "Content-Type: application/json"
                // "User-Agent: insomnia/11.2.0"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }
        // $response = Http::get(env('NODE_API_URL') . '/api/hello');
        // return $response->json();
    }
}
