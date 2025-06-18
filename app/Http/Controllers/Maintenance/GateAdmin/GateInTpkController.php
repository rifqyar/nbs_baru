<?php

namespace App\Http\Controllers\Maintenance\GateAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function addGateIn(Request $request)
    {
        $no_cont    = strtoupper($request->input('NO_CONT'));
        $no_req     = strtoupper($request->input('NO_REQ'));
        $no_truck   = strtoupper($request->input('NO_TRUCK'));
        $no_req_tpk = strtoupper($request->input('NO_REQ_TPK'));
        $bp_id      = strtoupper($request->input('BP_ID'));

        $no_seal    = strtoupper($request->input('NO_SEAL'));
        $status     = strtoupper($request->input('STATUS'));
        $tgl_gati   = strtoupper($request->input('tgl_gati'));
        // $masa_berlaku = strtoupper($request->input('MASA_BERLAKU'));
        $keterangan = strtoupper($request->input('KETERANGAN'));
        $id_yard    = strtoupper($request->input('ID_YARD'));

        $id_user    = Session::get('LOGGED_STORAGE');


        // Cek apakah data sudah ada
        $jum_gati = DB::connection('uster')->table('BORDER_GATE_IN')
            ->where('NO_CONTAINER', $no_cont)
            ->where('NO_REQUEST', $no_req)
            ->count();

        if ($jum_gati > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Container Sudah Gate in'
            ], 200);
        }

        // Cek posisi container
        $row_gati = DB::connection('uster')->table('MASTER_CONTAINER')
            ->select('LOCATION')
            ->where('NO_CONTAINER', $no_cont)
            ->first();
        $gati = $row_gati ? $row_gati->location : null;

        if (empty($no_truck)) {
            return response()->json([
                'success' => false,
                'message' => 'TRUCK'
            ], 200);
        } elseif ($gati == "GATI") {
            return response()->json([
                'success' => false,
                'message' => 'Container Sudah Gate In'
            ], 200);
        } else {
            DB::beginTransaction();
            try {
                // Insert into BORDER_GATE_IN
                DB::connection('uster')->table('BORDER_GATE_IN')->insert([
                    'NO_REQUEST'  => $no_req,
                    'NO_CONTAINER' => $no_cont,
                    'ID_USER'     => $id_user,
                    'TGL_IN'      => DB::raw("TO_DATE('$tgl_gati','YYYY-MM-DD HH24:MI:SS')"),
                    'NOPOL'       => $no_truck,
                    'STATUS'      => $status,
                    'NO_SEAL'     => $no_seal,
                    'ID_YARD'     => $id_yard,
                    'KETERANGAN'  => $keterangan,
                    'ENTRY_DATE'  => DB::raw('SYSDATE')
                ]);

                // Update MASTER_CONTAINER LOCATION
                DB::connection('uster')->table('MASTER_CONTAINER')
                    ->where('NO_CONTAINER', $no_cont)
                    ->update(['LOCATION' => 'GATI']);

                // Get NO_BOOKING and COUNTER
                $container = DB::connection('uster')->table('MASTER_CONTAINER')
                    ->where('NO_CONTAINER', $no_cont)
                    ->orderByDesc('COUNTER')
                    ->first();

                $cur_booking1 = $container ? $container->no_booking : null;
                $cur_counter1 = $container ? $container->counter : null;

                $id_yard_ = Session::get("IDYARD_STORAGE");

                // Insert into history_container
                DB::connection('uster')->table('history_container')->insert([
                    'NO_CONTAINER' => $no_cont,
                    'NO_REQUEST'   => $no_req,
                    'KEGIATAN'     => 'BORDER GATE IN',
                    'TGL_UPDATE'   => DB::raw('SYSDATE'),
                    'ID_USER'      => $id_user,
                    'ID_YARD'      => $id_yard_,
                    'STATUS_CONT'  => $status,
                    'NO_BOOKING'   => $cur_booking1,
                    'COUNTER'      => $cur_counter1
                ]);

                // Optionally update CONTAINER_RECEIVING if needed
                // DB::connection('uster')->table('CONTAINER_RECEIVING')
                //     ->where('NO_CONTAINER', $no_cont)
                //     ->where('NO_REQUEST', $no_req)
                //     ->update(['AKTIF' => 'T']);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'OK',
                    'code' => 200
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'GAGAL',
                    'error'   => $e->getMessage()
                ], 500);
            }
        }
    }
}
