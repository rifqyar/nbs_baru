<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\GatePeriodikService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class GatePeriodikController extends Controller
{
    protected $service;
    public function __construct(GatePeriodikService $notaService)
    {
        $this->service = $notaService;
    }

    public function index()
    {
        return view('report.gateperiodik.index');
    }

    public function generateNota(Request $request)
    {
        $this->validate($request, [
            'tgl_akhir' => 'after_or_equal:tgl_awal'
        ],[
            'tgl_akhir' => 'Periode gate akhir harus lebih besar dari periode gate awal'
        ]);

        try {
            $data = $this->service->getDataNota($request->all());
            if ($data->getStatusCode() != 200) {
                throw new Exception('Terjadi Kesalahan saat mengambil data gate periodik, harap coba lagi nanti', 500);
            } else {
                $data = $data->getData()->data;
                $jenis = $request->option_kegiatan;
                $lokasi = $request->lokasi;
                $blade = view('report.gateperiodik.dataList', compact('data', 'jenis', 'lokasi'))->render();

                return response()->json([
                    'status' => [
                        'msg' => 'OK',
                        'code' => 200
                    ], 'blade' => $blade
                ], 200);
            }
        } catch (Exception $th) {
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Mengambil Data, Harap Coba lagi!'
            ], 500);
        }
    }

    public function exportNota(Request $request)
    {
        $this->validate($request, [
            'tgl_akhir' => 'after_or_equal:tgl_awal'
        ],[
            'tgl_akhir' => 'Periode gate akhir harus lebih besar dari periode gate awal'
        ]);

        $data = $this->service->getDataNota($request->all());
        if ($data->getStatusCode() != 200) {
            throw new Exception('Terjadi Kesalahan saat mengambil data gate periodik, harap coba lagi nanti', 500);
        } else {
            $data = $data->getData()->data;

            // INITIATE SPREADSHEET
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();
            $activeWorksheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            if (($request->option_kegiatan == 'GATO' && $request->lokasi == '06') || ($request->option_kegiatan == 'GATO' && $request->lokasi == 'ALL')) {
                $column = ['No.', 'No. Container', 'Size', 'Type', 'Status', 'No. Request', 'Tgl. Gate', 'No. Polisi', 'No. Seal', 'Nama PBM', 'Nama Yard', 'Kegiatan', 'Vessel', 'Voyage', 'Operator Gate'];
                $columnWidth = [5, 18, 7, 10, 10, 15, 17, 17, 15, 35, 15, 15, 20, 15, 20];
            } else {
                $column = ['No.', 'No. Container', 'Size', 'Type', 'Status', 'No. Request', 'Tgl. Gate', 'No. Polisi', 'No. Seal', 'Nama PBM', 'Nama Yard', 'Kegiatan', 'Operator Gate'];
                $columnWidth = [5, 18, 7, 10, 10, 15, 17, 17, 15, 35, 15, 15, 20];
            }

            /** SET EXCEL TITLE */
            $last_column = Coordinate::stringFromColumnIndex(count($column));
            $rangeTitle = 'A1:' . $last_column . '1';
            $activeWorksheet->mergeCells($rangeTitle);
            $activeWorksheet->setCellValue('A1', "REPORT GATE PER PERIODIK");
            $activeWorksheet->getStyle($rangeTitle)
                ->getFont()
                ->setSize('12')
                ->setBold(true);
            $activeWorksheet->getStyle($rangeTitle)
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rangeTitle = 'A2:' . $last_column . '2';
            $activeWorksheet->mergeCells($rangeTitle);
            $activeWorksheet->setCellValue('A2', "PT. PELABUHAN INDONESIA II - CABANG PONTIANAK");
            $activeWorksheet->getStyle($rangeTitle)
                ->getFont()
                ->setSize('12')
                ->setBold(true);
            $activeWorksheet->getStyle($rangeTitle)
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $rangeTitle = 'A3:' . $last_column . '3';
            $activeWorksheet->mergeCells($rangeTitle);
            $activeWorksheet->setCellValue('A3', Carbon::parse($request->tgl_awal)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($request->tgl_akhir)->translatedFormat('d M Y'));
            $activeWorksheet->getStyle($rangeTitle)
                ->getFont()
                ->setSize('12')
                ->setBold(true);
            $activeWorksheet->getStyle($rangeTitle)
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            /** SET HEADER */
            $startColumn = 'A';
            for ($i = 0; $i < count($column); $i++) {
                // set Field Value
                $activeWorksheet->setCellValue($startColumn . '4', str_replace('_', ' ', $column[$i]));
                $activeWorksheet->getColumnDimension($startColumn)->setWidth($columnWidth[$i]);

                /** SET STYLING HEADER */
                // Allignment
                $activeWorksheet
                    ->getStyle($startColumn . '4')
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Font Size & Color
                $activeWorksheet
                    ->getStyle($startColumn . '4')
                    ->getFont()
                    ->setSize('11');

                // Row
                $activeWorksheet->getRowDimension('4')->setRowHeight(20);
                $startColumn++;
            }

            /** SET VALUE */
            $arrayData = [];
            foreach ($data as $key => $value) {
                if (($request->option_kegiatan == 'GATO' && $request->lokasi == '06') || ($request->option_kegiatan == 'GATO' && $request->lokasi == 'ALL')) {
                    array_push($arrayData, [
                        $key + 1,
                        $value->no_container,
                        $value->size_,
                        $value->type_,
                        $value->status,
                        $value->no_request,
                        Carbon::parse($value->tgl_in)->translatedFormat('Y/m/d H:i'),
                        $value->nopol,
                        $value->no_seal,
                        $value->nm_pbm,
                        $value->nama_yard,
                        $value->kegiatan,
                        $value->vessel,
                        $value->voyage,
                        $value->username,
                    ]);
                } else {
                    array_push($arrayData, [
                        $key + 1,
                        $value->no_container,
                        $value->size_,
                        $value->type_,
                        $value->status,
                        $value->no_request,
                        Carbon::parse($value->tgl_in)->translatedFormat('Y/m/d H:i'),
                        $value->nopol,
                        $value->no_seal,
                        $value->nm_pbm,
                        $value->nama_yard,
                        $value->kegiatan,
                        $value->username,
                    ]);
                }
            }
            $activeWorksheet->fromArray($arrayData, null, 'A5');

            $writer = new Xlsx($spreadsheet);
            $fileName = "LAPORAN GATE PER PERIODIK " . $request->tgl_awal . ' - ' . $request->tgl_akhir;
            $file = $fileName . '.xlsx';
            $path = public_path() . "/storage/report/gate_periodik/" . $file;
            $writer->save($path);

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => 200
                ],
                'file' => $file,
                'filePath' => asset('storage/report/gate_periodik/' . $file)
            ], 200);
        }
    }

    // Get Master Data
    public function masterVessel(Request $request)
    {
        $year = Carbon::parse($request->year)->translatedFormat('Y');
        $data['PBM'] = $this->service->masterVessel(strtoupper($request->search), $year);

        return response()->json($data['PBM']);
    }
}
