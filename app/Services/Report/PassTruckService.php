<?php

namespace App\Services\Report;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PassTruckService
{
    public function getDataReport($data)
    {
        try {
            $tgl_awal = isset($data['tgl_awal']) ? Carbon::parse($data['tgl_awal'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $tgl_akhir = isset($data['tgl_akhir']) ? Carbon::parse($data['tgl_akhir'])->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            $kegiatan = strtoupper($data['option_kegiatan'] ?? 'ALL');

            $queries = [];

            // 1. RECEIVING
            if ($kegiatan === 'ALL' || $kegiatan === 'RECEIVING') {
                $queries[] = "
                    SELECT 
                        h.NO_REQUEST,
                        TO_CHAR(h.TGL_NOTA, 'YYYY-MM-DD') AS TANGGAL,
                        NVL(h.NO_NOTA_MTI, h.NO_NOTA) AS NO_NOTA,
                        NVL(h.NO_FAKTUR_MTI, h.NO_FAKTUR) AS NO_FAKTUR,
                        d.KETERANGAN,
                        NVL(d.COA, 'RUPA') AS COA,
                        'RECEIVING' AS KEGIATAN,
                        d.TARIF,
                        CASE WHEN NVL(d.TARIF, 0) > 0 AND NVL(d.BIAYA, 0) > 0 THEN ROUND(d.BIAYA / d.TARIF) ELSE 1 END AS JUMLAH_PASS,
                        d.BIAYA,
                        TRUNC(h.TGL_NOTA) AS TGL_NOTA
                    FROM NOTA_RECEIVING h
                    JOIN NOTA_RECEIVING_D d ON h.NO_NOTA = d.NO_NOTA
                    WHERE UPPER(d.KETERANGAN) LIKE '%PASS TRUCK%' 
                      AND NVL(h.STATUS, 'NEW') <> 'BATAL'
                      AND UPPER(NVL(h.LUNAS, 'NO')) = 'YES'
                ";
            }

            // 2. STRIPPING
            if ($kegiatan === 'ALL' || $kegiatan === 'STRIPPING') {
                $queries[] = "
                    SELECT 
                        h.NO_REQUEST,
                        TO_CHAR(h.TGL_NOTA, 'YYYY-MM-DD') AS TANGGAL,
                        NVL(h.NO_NOTA_MTI, h.NO_NOTA) AS NO_NOTA,
                        NVL(h.NO_FAKTUR_MTI, h.NO_FAKTUR) AS NO_FAKTUR,
                        d.KETERANGAN,
                        NVL(d.COA, 'RUPA') AS COA,
                        'STRIPPING' AS KEGIATAN,
                        d.TARIF,
                        CASE WHEN NVL(d.TARIF, 0) > 0 AND NVL(d.BIAYA, 0) > 0 THEN ROUND(d.BIAYA / d.TARIF) ELSE 1 END AS JUMLAH_PASS,
                        d.BIAYA,
                        TRUNC(h.TGL_NOTA) AS TGL_NOTA
                    FROM NOTA_STRIPPING h
                    JOIN NOTA_STRIPPING_D d ON h.NO_NOTA = d.NO_NOTA
                    WHERE UPPER(d.KETERANGAN) LIKE '%PASS TRUCK%' 
                      AND NVL(h.STATUS, 'NEW') <> 'BATAL'
                      AND UPPER(NVL(h.LUNAS, 'NO')) = 'YES'
                ";
            }

            // 3. DELIVERY
            if ($kegiatan === 'ALL' || $kegiatan === 'DELIVERY') {
                $queries[] = "
                    SELECT 
                        h.NO_REQUEST,
                        TO_CHAR(h.TGL_NOTA, 'YYYY-MM-DD') AS TANGGAL,
                        NVL(h.NO_NOTA_MTI, h.NO_NOTA) AS NO_NOTA,
                        NVL(h.NO_FAKTUR_MTI, h.NO_FAKTUR) AS NO_FAKTUR,
                        d.KETERANGAN,
                        NVL(d.COA, 'RUPA') AS COA,
                        'DELIVERY' AS KEGIATAN,
                        d.TARIF,
                        CASE WHEN NVL(d.TARIF, 0) > 0 AND NVL(d.BIAYA, 0) > 0 THEN ROUND(d.BIAYA / d.TARIF) ELSE 1 END AS JUMLAH_PASS,
                        d.BIAYA,
                        TRUNC(h.TGL_NOTA) AS TGL_NOTA
                    FROM NOTA_DELIVERY h
                    JOIN NOTA_DELIVERY_D d ON h.NO_NOTA = d.ID_NOTA
                    WHERE UPPER(d.KETERANGAN) LIKE '%PASS TRUCK%' 
                      AND NVL(h.STATUS, 'NEW') <> 'BATAL'
                      AND UPPER(NVL(h.LUNAS, 'NO')) = 'YES'
                ";
            }

            // 4. STUFFING
            if ($kegiatan === 'ALL' || $kegiatan === 'STUFFING') {
                $queries[] = "
                    SELECT 
                        h.NO_REQUEST,
                        TO_CHAR(h.TGL_NOTA, 'YYYY-MM-DD') AS TANGGAL,
                        NVL(h.NO_NOTA_MTI, h.NO_NOTA) AS NO_NOTA,
                        NVL(h.NO_FAKTUR_MTI, h.NO_FAKTUR) AS NO_FAKTUR,
                        d.KETERANGAN,
                        NVL(d.COA, 'RUPA') AS COA,
                        'STUFFING' AS KEGIATAN,
                        d.TARIF,
                        CASE WHEN NVL(d.TARIF, 0) > 0 AND NVL(d.BIAYA, 0) > 0 THEN ROUND(d.BIAYA / d.TARIF) ELSE 1 END AS JUMLAH_PASS,
                        d.BIAYA,
                        TRUNC(h.TGL_NOTA) AS TGL_NOTA
                    FROM NOTA_STUFFING h
                    JOIN NOTA_STUFFING_D d ON h.NO_NOTA = d.NO_NOTA
                    WHERE UPPER(d.KETERANGAN) LIKE '%PASS TRUCK%' 
                      AND NVL(h.STATUS, 'NEW') <> 'BATAL'
                      AND UPPER(NVL(h.LUNAS, 'NO')) = 'YES'
                ";
            }

            if (empty($queries)) {
                return response()->json([
                    'status' => ['msg' => 'OK', 'code' => 200],
                    'data' => [],
                    'total_pass' => 0,
                    'total_biaya' => 0
                ], 200);
            }

            $unionQuery = implode(" UNION ALL ", $queries);

            // Sorting handler
            $orderBy = "TGL_NOTA DESC, NO_REQUEST ASC";
            if (!empty($data['menu2']) && is_array($data['menu2'])) {
                $allowedCols = ['NO_REQUEST', 'NO_NOTA', 'KEGIATAN', 'JUMLAH_PASS', 'TANGGAL', 'BIAYA'];
                $validOrders = [];
                foreach ($data['menu2'] as $orderCol) {
                    $cleanCol = strtoupper(trim(str_replace(['ASC', 'DESC'], '', $orderCol)));
                    $dir = str_contains(strtoupper($orderCol), 'DESC') ? 'DESC' : 'ASC';
                    if (in_array($cleanCol, $allowedCols)) {
                        $validOrders[] = "$cleanCol $dir";
                    }
                }
                if (!empty($validOrders)) {
                    $orderBy = implode(", ", $validOrders);
                }
            }

            $finalSql = "
                SELECT * FROM (
                    $unionQuery
                )
                WHERE TGL_NOTA BETWEEN TO_DATE('$tgl_awal', 'YYYY-MM-DD') AND TO_DATE('$tgl_akhir', 'YYYY-MM-DD')
                ORDER BY $orderBy
            ";

            $results = DB::connection('uster')->select($finalSql);

            $totalPass = 0;
            $totalBiaya = 0;

            foreach ($results as $item) {
                $totalPass += intval($item->jumlah_pass ?? 0);
                $totalBiaya += floatval($item->biaya ?? 0);
            }

            return response()->json([
                'status' => ['msg' => 'OK', 'code' => 200],
                'data' => $results,
                'total_pass' => $totalPass,
                'total_biaya' => $totalBiaya
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => ['msg' => $e->getMessage(), 'code' => 500],
                'data' => [],
                'total_pass' => 0,
                'total_biaya' => 0,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
