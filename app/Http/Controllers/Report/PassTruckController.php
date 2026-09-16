<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\PassTruckService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PassTruckController extends Controller
{
    protected $service;

    public function __construct(PassTruckService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('report.passtruck.index');
    }

    public function generateReport(Request $request)
    {
        $this->validate($request, [
            'tgl_akhir' => 'after_or_equal:tgl_awal'
        ], [
            'tgl_akhir' => 'Periode tanggal akhir harus lebih besar dari periode tanggal awal'
        ]);

        try {
            $res = $this->service->getDataReport($request->all());
            if ($res->getStatusCode() != 200) {
                throw new Exception('Terjadi kesalahan saat mengambil data pass truck', 500);
            }

            $resData = $res->getData();
            $data = $resData->data ?? [];
            $total_pass = $resData->total_pass ?? 0;
            $total_biaya = $resData->total_biaya ?? 0;

            $blade = view('report.passtruck.dataList', compact('data', 'total_pass', 'total_biaya'))->render();

            return response()->json([
                'status' => ['msg' => 'OK', 'code' => 200],
                'blade' => $blade,
                'data' => $data,
                'total_pass' => number_format($total_pass, 0, ',', '.'),
                'total_biaya' => number_format($total_biaya, 0, ',', '.')
            ], 200);

        } catch (Exception $th) {
            return response()->json([
                'status' => ['msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err', 'code' => 500],
                'data' => [],
                'total_pass' => 0,
                'total_biaya' => 0,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Mengambil Data!'
            ], 500);
        }
    }

    public function generateExcel(Request $request)
    {
        $this->validate($request, [
            'tgl_akhir' => 'after_or_equal:tgl_awal'
        ], [
            'tgl_akhir' => 'Periode tanggal akhir harus lebih besar dari periode tanggal awal'
        ]);

        try {
            $res = $this->service->getDataReport($request->all());
            if ($res->getStatusCode() != 200) {
                throw new Exception('Terjadi kesalahan saat mengambil data pass truck', 500);
            }

            $resData = $res->getData();
            $data = $resData->data ?? [];
            $total_pass = $resData->total_pass ?? 0;
            $total_biaya = $resData->total_biaya ?? 0;

            $tgl_awal = Carbon::parse($request->tgl_awal)->format('d-m-Y');
            $tgl_akhir = Carbon::parse($request->tgl_akhir)->format('d-m-Y');
            $kegiatan = strtoupper($request->option_kegiatan ?? 'ALL');

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

            $columns = [
                'NO', 'NO REQUEST', 'TANGGAL', 'NO NOTA', 'NO FAKTUR', 
                'KETERANGAN', 'COA', 'KEGIATAN', 'TARIF', 'JUMLAH PASS', 'BIAYA'
            ];

            $lastColumn = Coordinate::stringFromColumnIndex(count($columns));

            // Title
            $sheet->mergeCells("A1:{$lastColumn}1");
            $sheet->setCellValue('A1', 'LAPORAN PASS TRUCK');
            $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setSize(14)->setBold(true);
            $sheet->getStyle("A1:{$lastColumn}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Subtitle / Filter Info
            $sheet->mergeCells("A2:{$lastColumn}2");
            $sheet->setCellValue('A2', "PERIODE: {$tgl_awal} s/d {$tgl_akhir} | KEGIATAN: {$kegiatan}");
            $sheet->getStyle("A2:{$lastColumn}2")->getFont()->setSize(11)->setItalic(true);
            $sheet->getStyle("A2:{$lastColumn}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Summary Totals Info
            $sheet->setCellValue('A4', "TOTAL PASS: " . number_format($total_pass, 0, ',', '.'));
            $sheet->setCellValue('A5', "TOTAL BIAYA: " . number_format($total_biaya, 0, ',', '.'));
            $sheet->getStyle('A4:A5')->getFont()->setBold(true);

            // Table Headers (Row 7)
            $startRow = 7;
            foreach ($columns as $idx => $colName) {
                $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
                $sheet->setCellValue("{$colLetter}{$startRow}", $colName);
            }

            $headerRange = "A{$startRow}:{$lastColumn}{$startRow}";
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0080C0');
            $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFFFF');

            // Data Rows
            $currentRow = $startRow + 1;
            $no = 1;

            foreach ($data as $row) {
                $sheet->setCellValue("A{$currentRow}", $no++);
                $sheet->setCellValue("B{$currentRow}", $row->no_request ?? '-');
                $sheet->setCellValue("C{$currentRow}", $row->tanggal ?? '-');
                $sheet->setCellValue("D{$currentRow}", $row->no_nota ?? '-');
                $sheet->setCellValue("E{$currentRow}", $row->no_faktur ?? '-');
                $sheet->setCellValue("F{$currentRow}", $row->keterangan ?? '-');
                $sheet->setCellValue("G{$currentRow}", $row->coa ?? '-');
                $sheet->setCellValue("H{$currentRow}", $row->kegiatan ?? '-');
                $sheet->setCellValue("I{$currentRow}", floatval($row->tarif ?? 0));
                $sheet->setCellValue("J{$currentRow}", intval($row->jumlah_pass ?? 0));
                $sheet->setCellValue("K{$currentRow}", floatval($row->biaya ?? 0));

                $sheet->getStyle("I{$currentRow}:K{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("A{$currentRow}:H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currentRow++;
            }

            // Total Row
            $sheet->mergeCells("A{$currentRow}:I{$currentRow}");
            $sheet->setCellValue("A{$currentRow}", 'TOTAL');
            $sheet->setCellValue("J{$currentRow}", $total_pass);
            $sheet->setCellValue("K{$currentRow}", $total_biaya);
            $sheet->getStyle("A{$currentRow}:K{$currentRow}")->getFont()->setBold(true);
            $sheet->getStyle("J{$currentRow}:K{$currentRow}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Table Border
            $tableRange = "A{$startRow}:{$lastColumn}{$currentRow}";
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            // Auto-size columns
            foreach (range(1, count($columns)) as $colIdx) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $fileName = "Laporan_Pass_Truck_{$tgl_awal}_sd_{$tgl_akhir}.xlsx";
            $writer = new Xlsx($spreadsheet);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
