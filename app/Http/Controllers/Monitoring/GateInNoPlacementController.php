<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

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
     */
    public function data(Request $request)
    {
        $data = DB::connection('uster')->select('SELECT * FROM V_GATEIN_NOPLACEMENT');

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Export ke Excel (.xlsx) menggunakan PhpSpreadsheet
     */
    public function export(Request $request)
    {
        $data = DB::connection('uster')->select('SELECT * FROM V_GATEIN_NOPLACEMENT');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Gate In No Placement');

        // ── Header style ──────────────────────────────────────────────────────
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A6AB1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                                             'color'       => ['argb' => 'FF000000']]],
        ];

        // ── Judul ─────────────────────────────────────────────────────────────
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'DATA GATE IN NO PLACEMENT');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Tanggal Cetak: ' . date('d/m/Y H:i:s'));
        $sheet->getStyle('A2')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Header kolom ──────────────────────────────────────────────────────
        $headers = ['No', 'No Container', 'No Request', 'Status', 'Trucking', 'Tgl Gate In'];
        $cols    = ['A', 'B', 'C', 'D', 'E', 'F'];

        foreach ($headers as $i => $header) {
            $sheet->setCellValue($cols[$i] . '4', $header);
        }
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // ── Isi data ──────────────────────────────────────────────────────────
        $row = 5;
        $no  = 1;
        foreach ($data as $item) {
            $item = (array) $item;

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item['no_container'] ?? $item['NO_CONTAINER'] ?? '');
            $sheet->setCellValue('C' . $row, $item['no_request']   ?? $item['NO_REQUEST']   ?? '');
            $sheet->setCellValue('D' . $row, $item['status']       ?? $item['STATUS']       ?? '');
            $sheet->setCellValue('E' . $row, $item['trucking']     ?? $item['TRUCKING']     ?? '');
            $sheet->setCellValue('F' . $row, $item['tgl_in']       ?? $item['TGL_IN']       ?? '');

            // Zebra striping
            if ($no % 2 === 0) {
                $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEEF4FC']],
                ]);
            }

            $row++;
        }

        // ── Border data area ──────────────────────────────────────────────────
        $lastRow = $row - 1;
        if ($lastRow >= 5) {
            $sheet->getStyle('A4:F' . $lastRow)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
        }

        // ── Auto width ────────────────────────────────────────────────────────
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // ── Output ────────────────────────────────────────────────────────────
        $filename = 'Gate_In_No_Placement_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
