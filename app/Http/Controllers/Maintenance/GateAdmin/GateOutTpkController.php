<?php

namespace App\Http\Controllers\Maintenance\GateAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GateOutTpkController extends Controller
{
    public function index()
    {
        return view('maintenance.gate_admin.gate_out_tpk');
    }

    public function getDataCont(Request $request)
    {
        $no_cont = strtoupper($request->search);

        $result = DB::connection('uster')->table('CONTAINER_DELIVERY as a')
            ->distinct()
            ->selectRaw("
                a.NO_CONTAINER AS NO_CONTAINER,
                a.NO_REQUEST AS NO_REQUEST,
                b.SIZE_ AS SIZE_,
                a.STATUS AS STATUS,
                b.TYPE_ AS TYPE_,
                a.TGL_DELIVERY AS TGL_REQUEST_DELIVERY,
                n.NO_NOTA AS NO_NOTA,
                n.EMKL AS NM_PBM
            ")
            ->join('MASTER_CONTAINER as b', 'a.NO_CONTAINER', '=', 'b.NO_CONTAINER')
            ->join('REQUEST_DELIVERY as d', 'a.NO_REQUEST', '=', 'd.NO_REQUEST')
            ->join('nota_delivery as n', 'n.no_request', '=', 'd.no_request')
            ->where('b.LOCATION', 'IN_YARD')
            ->where('a.AKTIF', 'Y')
            ->where('d.DELIVERY_KE', 'TPK')
            ->where('n.LUNAS', 'YES')
            ->where('n.STATUS', '<>', 'BATAL')
            ->when($no_cont, function ($query, $no_cont) {
                return $query->where('a.NO_CONTAINER', 'like', "%$no_cont%");
            })
            ->orderBy('a.NO_REQUEST')
            ->get();

        $results = $result->map(function ($item) {
            $uppercased = [];
            foreach ($item as $key => $value) {
                $uppercased[strtoupper($key)] = $value;
            }
            return $uppercased;
        });

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    public function addGateOut(Request $request)
    {
        $requestId              = $request->input('NO_REQ');
        $in_nocont              = $request->input('NO_CONT');
        $gateDate               = $request->input('tgl_gato');
        $in_notruck             = $request->input('NO_TRUCK') ?? '0';
        $in_seal                = $request->input('NO_SEAL') ?? '-';
        $latest_status_container = $request->input('STATUS');
        $serviceName            = $request->input('service'); // STRIPPING or STUFFING
        $v_user                 = auth()->user()->id ?? 'SYSTEM'; // asumsi login

        $out_msg = 'Failed';

        try {
            DB::connection('uster')->beginTransaction();

            // 1. Cek apakah data sudah ada
            $existing = DB::connection('uster')->table('BORDER_GATE_OUT')
                ->where('NO_CONTAINER', $in_nocont)
                ->where('NO_REQUEST', $requestId)
                ->whereRaw("TO_CHAR(TGL_IN, 'YYYY-MM-DD HH24:MI:SS') = TO_CHAR(TO_DATE(?, 'YYYY-MM-DD HH24:MI:SS'), 'YYYY-MM-DD HH24:MI:SS')", [$gateDate])
                ->count();

            if ($existing > 0) {
                return response()->json([
                    'message' => 'Data Exists',
                    'status' => 'Failed'
                ]);
            }

            if (!$in_nocont) {
                return response()->json([
                    'message' => 'Container is not found',
                    'status' => 'Failed'
                ]);
            }

            // 2. Insert ke BORDER_GATE_OUT
            DB::connection('uster')->table('BORDER_GATE_OUT')->insert([
                'NO_REQUEST' => $requestId,
                'NO_CONTAINER' => $in_nocont,
                'ID_USER' => $v_user,
                'TGL_IN' => DB::raw("TO_DATE('$gateDate', 'YYYY-MM-DD HH24:MI:SS')"),
                'NOPOL' => $in_notruck,
                'STATUS' => $latest_status_container,
                'NO_SEAL' => $in_seal,
                'TRUCKING' => $in_notruck,
                'ID_YARD' => 46,
                'VIA' => 'TRIG_OPUS'
            ]);

            // 3. Delete from PLACEMENT
            DB::connection('uster')->table('PLACEMENT')
                ->where('NO_CONTAINER', $in_nocont)
                ->delete();

            // 4. Update GATE pada STRIPPING/STUFFING jika perlu
            if ($serviceName === 'STRIPPING') {
                $v_noreq_strip = DB::connection('uster')->table('REQUEST_STRIPPING')
                    ->where('NO_REQUEST_RECEIVING', $requestId)
                    ->orderByDesc('TGL_REQUEST')
                    ->value('NO_REQUEST');

                if ($v_noreq_strip) {
                    DB::connection('uster')->table('CONTAINER_STRIPPING')
                        ->where('NO_CONTAINER', $in_nocont)
                        ->where('NO_REQUEST', $v_noreq_strip)
                        ->update([
                            'TGL_GATE' => DB::raw("TO_DATE('$gateDate', 'YYYY-MM-DD HH24:MI:SS')")
                        ]);
                }
            } elseif ($serviceName === 'STUFFING') {
                $v_noreq_stuf = DB::connection('uster')->table('REQUEST_STUFFING')
                    ->where('NO_REQUEST_RECEIVING', $requestId)
                    ->orderByDesc('TGL_REQUEST')
                    ->value('NO_REQUEST');

                if ($v_noreq_stuf) {
                    DB::connection('uster')->table('CONTAINER_STUFFING')
                        ->where('NO_CONTAINER', $in_nocont)
                        ->where('NO_REQUEST', $v_noreq_stuf)
                        ->update([
                            'TGL_GATE' => DB::raw("TO_DATE('$gateDate', 'YYYY-MM-DD HH24:MI:SS')")
                        ]);
                }
            }

            // 5. Update lokasi container
            DB::connection('uster')->table('MASTER_CONTAINER')
                ->where('NO_CONTAINER', $in_nocont)
                ->update(['LOCATION' => 'GATO']);

            // 6. Update CONTAINER_DELIVERY
            DB::connection('uster')->table('CONTAINER_DELIVERY')
                ->where('NO_CONTAINER', $in_nocont)
                ->where('NO_REQUEST', $requestId)
                ->update(['AKTIF' => 'T']);

            // 7. Ambil booking dan counter
            $latestContainer = DB::connection('uster')->table('MASTER_CONTAINER')
                ->where('NO_CONTAINER', $in_nocont)
                ->orderByDesc('COUNTER')
                ->first();

            $v_nobooking = $latestContainer->NO_BOOKING ?? null;
            $v_counter = $latestContainer->COUNTER ?? null;

            // 8. Insert ke HISTORY_CONTAINER
            DB::connection('uster')->table('HISTORY_CONTAINER')->insert([
                'NO_CONTAINER' => $in_nocont,
                'NO_REQUEST' => $requestId,
                'KEGIATAN' => 'BORDER GATE OUT',
                'TGL_UPDATE' => DB::raw('SYSDATE'),
                'ID_USER' => $v_user,
                'ID_YARD' => 46,
                'STATUS_CONT' => $latest_status_container,
                'NO_BOOKING' => $v_nobooking,
                'COUNTER' => $v_counter
            ]);

            DB::connection('uster')->commit();

            return response()->json([
                'message' => 'SUCCESS',
                'status' => 'OK'
            ]);
        } catch (\Exception $e) {
            DB::connection('uster')->rollBack();
            Log::error('Gate Out Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed: ' . $e->getMessage(),
                'status' => 'ERROR'
            ], 500);
        }
    }
}
