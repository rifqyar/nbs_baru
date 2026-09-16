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
            $tgl_awal = isset($data['tgl_awal']) ? Carbon::parse($data['tgl_awal'])->format('d-m-Y') : Carbon::now()->format('d-m-Y');
            $tgl_akhir = isset($data['tgl_akhir']) ? Carbon::parse($data['tgl_akhir'])->format('d-m-Y') : Carbon::now()->format('d-m-Y');
            $kegiatan = strtoupper($data['option_kegiatan'] ?? 'ALL');

            $whereKegiatan = "";
            if ($kegiatan !== 'ALL') {
                $whereKegiatan = " AND UPPER(CASE 
                    WHEN h.no_request LIKE 'REC%' THEN 'RECEIVING'
                    WHEN h.no_request LIKE 'STR%' THEN 'STRIPPING'
                    WHEN h.no_request LIKE 'DEL%' THEN 'DELIVERY'
                    WHEN h.no_request LIKE 'STF%' THEN 'STUFFING'
                    ELSE 'LAINNYA'
                END) = '$kegiatan'";
            }

            // Sorting handler
            $orderBy = "tgl ASC, no_request ASC";
            if (!empty($data['menu2']) && is_array($data['menu2'])) {
                $allowedCols = ['NO_REQUEST', 'NO_NOTA', 'KEGIATAN', 'JUMLAH_PASS', 'JUMLAH', 'TANGGAL', 'BIAYA', 'TGL'];
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
                SELECT 
                    h.no_request,
                    h.no_nota_mti AS no_nota,
                    h.no_faktur_mti AS no_faktur,
                    'PASS TRUCK' AS keterangan,
                    'RUPA' AS coa,
                    CASE 
                        WHEN h.no_request LIKE 'REC%' THEN 'RECEIVING'
                        WHEN h.no_request LIKE 'STR%' THEN 'STRIPPING'
                        WHEN h.no_request LIKE 'DEL%' THEN 'DELIVERY'
                        WHEN h.no_request LIKE 'STF%' THEN 'STUFFING'
                        ELSE 'LAINNYA'
                    END AS kegiatan,
                    d.tarif,
                    d.boxes AS jumlah,
                    d.boxes AS jumlah_pass,
                    d.amount AS biaya,
                    NVL(h.tgl_pelunasan, h.trx_date) AS tgl,
                    TO_CHAR(NVL(h.tgl_pelunasan, h.trx_date), 'YYYY-MM-DD') AS tanggal
                FROM itpk_nota_detail d
                JOIN itpk_nota_header h ON d.trx_number = h.trx_number
                WHERE UPPER(d.line_description) = 'PASTR RUPA'
                  AND TRUNC(NVL(h.tgl_pelunasan, h.trx_date)) BETWEEN TO_DATE('$tgl_awal', 'DD-MM-YYYY') AND TO_DATE('$tgl_akhir', 'DD-MM-YYYY')
                  $whereKegiatan
                ORDER BY $orderBy
            ";

            $results = DB::connection('uster')->select($finalSql);

            $totalPass = 0;
            $totalBiaya = 0;

            foreach ($results as $item) {
                $totalPass += intval($item->jumlah_pass ?? $item->jumlah ?? 0);
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
