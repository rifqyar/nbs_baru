<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class GateInNoPlacementController extends Controller
{
    /**
     * Tampilkan halaman Gate In No Placement
     */
    public function index()
    {
        return view('monitoring.gate-in-no-placement');
    }

    /**
     * Server-side DataTables — query V_GATEIN_NOPLACEMENT (USTER DB)
     *
     * Kolom view (Oracle → dikembalikan lowercase oleh driver):
     * no_container, no_request, id_user, nopol, status,
     * no_seal, trucking, id_yard, keterangan, tgl_in, entry_date, tableidx
     */
    public function data(Request $request)
    {
        $data = DB::connection('uster')->select('SELECT * FROM V_GATEIN_NOPLACEMENT');

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Export data ke CSV
     */
    public function export(Request $request)
    {
        $data = DB::connection('uster')->select('SELECT * FROM V_GATEIN_NOPLACEMENT');

        $filename = 'gate_in_no_placement_' . date('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Header kolom
            fputcsv($file, [
                'No', 'No Container', 'No Request', 'Status', 'Trucking', 'Tgl Gate In',
                'Entry Date', 'Keterangan',
            ]);

            $no = 1;
            foreach ($data as $row) {
                $row = (array) $row; // convert stdClass ke array
                fputcsv($file, [
                    $no++,
                    $row['no_container'] ?? $row['NO_CONTAINER'] ?? '',
                    $row['no_request']   ?? $row['NO_REQUEST']   ?? '',
                    $row['status']       ?? $row['STATUS']       ?? '',
                    $row['trucking']     ?? $row['TRUCKING']     ?? '',
                    $row['tgl_in']       ?? $row['TGL_IN']       ?? '',
                    $row['entry_date']   ?? $row['ENTRY_DATE']   ?? '',
                    $row['keterangan']   ?? $row['KETERANGAN']   ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
