<?php

namespace App\Http\Controllers\Maintenance\GateAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GateInTpkController extends Controller
{
    public function index()
    {
        return view('maintenance.gate_admin.gate_in_tpk');
    }

    public function getDataCont(Request $request)
    {
        $no_cont = strtoupper($request->input('search'));

        try {
            $payload = [
            "terminalCode" => 'PNK',
            "containerNo" => $no_cont,
            "inOut" => 'OUT' // Gate Out dari TPK dan Gate In di USTER (Location GATI)
            ];

            $apiUrl = env('PRAYA_API_INTEGRATION') . "/api/getContainerInOut";
            $response = sendDataFromUrlTryCatch($payload, $apiUrl, 'POST', getTokenPraya());
            dd(getIsoCode());
            $responseData = json_decode($response['response'], true);

            if (isset($responseData['dataRec'])) {
            return response()->json([
                'success' => true,
                'data' => $responseData['dataRec']
            ]);
            } else {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ], 404);
            }
        } catch (\Exception $ex) {
            return response()->json([
            'success' => false,
            'message' => $ex->getMessage()
            ], 500);
        }
    }
}
