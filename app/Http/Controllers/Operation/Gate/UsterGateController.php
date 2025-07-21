<?php

namespace App\Http\Controllers\Operation\Gate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsterGateController extends Controller
{
    public function handleGate(Request $request)
    {
        $username = $request->header('USERNAME');
        $password = $request->header('PASSWORD');

        if ($username !== 'uster' || $password !== 'uster') {
            return response('Not Authorized', 401);
        }

        $payload = $request->all();

        $inTipe = $payload['inOut'] ?? null;
        $inContainer = $payload['containerNo'] ?? null;
        $vessel = $payload['vessel'] ?? null;
        $voyIn = $payload['voyIn'] ?? null;
        $voyOut = $payload['voyOut'] ?? null;
        $user = $payload['user'] ?? null;
        $truckId = $payload['truckId'] ?? null;
        $status = $payload['containerStatus'] ?? null;
        $seal = $payload['seal'] ?? null;
        $gateDate = $payload['date'] ?? null;
        $requestId = $payload['requestId'] ?? null;
        $serviceName = $payload['serviceName'] ?? null;

        if (!$inTipe || !$inContainer || !$status || !$gateDate || !$serviceName) {
            return response('Request is not complete', 400);
        }

        $db = DB::connection('uster');

        $statusContRow = $db->table('HISTORY_CONTAINER')
            ->select('STATUS_CONT')
            ->where('NO_REQUEST', $requestId)
            ->where('NO_CONTAINER', $inContainer)
            ->orderByDesc('TGL_UPDATE')
            ->limit(1)
            ->first();

        if (!$statusContRow) {
            return response('Container Status Not Found', 404);
        }

        $latestStatus = $statusContRow->STATUS_CONT;
        $vUser = 'opus';
        $yardId = 46;
        $via = 'TRIG_OPUS';

        if ($inTipe === 'OUT') {
            $exists = $db->table('BORDER_GATE_IN')
                ->where('NO_CONTAINER', $inContainer)
                ->where('NO_REQUEST', $requestId)
                ->whereRaw("TO_CHAR(TGL_IN, 'YYYY-MM-DD HH24:MI:SS') = TO_CHAR(TO_DATE(?, 'YYYY-MM-DD HH24:MI:SS'), 'YYYY-MM-DD HH24:MI:SS')", [$gateDate])
                ->exists();

            if ($exists) return response('Data Exists', 409);

            $db->table('BORDER_GATE_IN')->insert([
                'NO_REQUEST' => $requestId,
                'NO_CONTAINER' => $inContainer,
                'ID_USER' => $vUser,
                'TGL_IN' => DB::raw("TO_DATE('$gateDate', 'YYYY-MM-DD HH24:MI:SS')"),
                'NOPOL' => $truckId,
                'STATUS' => $latestStatus,
                'NO_SEAL' => $seal,
                'ID_YARD' => $yardId,
                'VIA' => $via,
            ]);

            $this->updateStrippingOrStuffing($db, $serviceName, $requestId, $inContainer, $gateDate);

            $db->table('MASTER_CONTAINER')
                ->where('NO_CONTAINER', $inContainer)
                ->update(['LOCATION' => 'GATI']);

            $container = $db->table('MASTER_CONTAINER')
                ->where('NO_CONTAINER', $inContainer)
                ->orderByDesc('COUNTER')
                ->first();

            $db->table('HISTORY_CONTAINER')->insert([
                'NO_CONTAINER' => $inContainer,
                'NO_REQUEST' => $requestId,
                'KEGIATAN' => 'BORDER GATE IN',
                'TGL_UPDATE' => DB::raw('SYSDATE'),
                'ID_USER' => $vUser,
                'ID_YARD' => $yardId,
                'STATUS_CONT' => $latestStatus,
                'NO_BOOKING' => $container->NO_BOOKING ?? null,
                'COUNTER' => $container->COUNTER ?? null,
            ]);

            return response('SUCCESS');
        }

        if ($inTipe === 'IN') {
            $exists = $db->table('BORDER_GATE_OUT')
                ->where('NO_CONTAINER', $inContainer)
                ->where('NO_REQUEST', $requestId)
                ->whereRaw("TO_CHAR(TGL_IN, 'YYYY-MM-DD HH24:MI:SS') = TO_CHAR(TO_DATE(?, 'YYYY-MM-DD HH24:MI:SS'), 'YYYY-MM-DD HH24:MI:SS')", [$gateDate])
                ->exists();

            if ($exists) return response('Data Exists', 409);

            $db->table('BORDER_GATE_OUT')->insert([
                'NO_REQUEST' => $requestId,
                'NO_CONTAINER' => $inContainer,
                'ID_USER' => $vUser,
                'TGL_IN' => DB::raw("TO_DATE('$gateDate', 'YYYY-MM-DD HH24:MI:SS')"),
                'NOPOL' => $truckId,
                'STATUS' => $latestStatus,
                'NO_SEAL' => $seal,
                'TRUCKING' => $truckId,
                'ID_YARD' => $yardId,
                'VIA' => $via,
            ]);

            $db->table('PLACEMENT')->where('NO_CONTAINER', $inContainer)->delete();

            $this->updateStrippingOrStuffing($db, $serviceName, $requestId, $inContainer, $gateDate);

            $db->table('MASTER_CONTAINER')
                ->where('NO_CONTAINER', $inContainer)
                ->update(['LOCATION' => 'GATO']);

            $db->table('CONTAINER_DELIVERY')
                ->where('NO_CONTAINER', $inContainer)
                ->where('NO_REQUEST', $requestId)
                ->update(['AKTIF' => 'T']);

            $container = $db->table('MASTER_CONTAINER')
                ->where('NO_CONTAINER', $inContainer)
                ->orderByDesc('COUNTER')
                ->first();

            $db->table('HISTORY_CONTAINER')->insert([
                'NO_CONTAINER' => $inContainer,
                'NO_REQUEST' => $requestId,
                'KEGIATAN' => 'BORDER GATE OUT',
                'TGL_UPDATE' => DB::raw('SYSDATE'),
                'ID_USER' => $vUser,
                'ID_YARD' => $yardId,
                'STATUS_CONT' => $latestStatus,
                'NO_BOOKING' => $container->NO_BOOKING ?? null,
                'COUNTER' => $container->COUNTER ?? null,
            ]);

            return response('SUCCESS');
        }

        return response('Type Only IN/OUT', 400);
    }

    private function updateStrippingOrStuffing($db, $serviceName, $requestId, $containerNo, $gateDate)
    {
        if ($serviceName === 'STRIPPING') {
            $req = $db->table('REQUEST_STRIPPING')
                ->select('NO_REQUEST')
                ->where('NO_REQUEST_RECEIVING', $requestId)
                ->orderByDesc('TGL_REQUEST')
                ->first();

            if ($req) {
                $db->table('CONTAINER_STRIPPING')
                    ->where('NO_CONTAINER', $containerNo)
                    ->where('NO_REQUEST', $req->NO_REQUEST)
                    ->update(['TGL_GATE' => $gateDate]);
            }
        } elseif ($serviceName === 'STUFFING') {
            $req = $db->table('REQUEST_STUFFING')
                ->select('NO_REQUEST')
                ->where('NO_REQUEST_RECEIVING', $requestId)
                ->orderByDesc('TGL_REQUEST')
                ->first();

            if ($req) {
                $db->table('CONTAINER_STUFFING')
                    ->where('NO_CONTAINER', $containerNo)
                    ->where('NO_REQUEST', $req->NO_REQUEST)
                    ->update(['TGL_GATE' => $gateDate]);
            }
        }
    }
}
