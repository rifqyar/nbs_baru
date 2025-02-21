<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\RealisasiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RealisasiController extends Controller
{
    protected $service;
    public function __construct(RealisasiService $notaService)
    {
        $this->service = $notaService;
    }

    public function index()
    {
        return view('report.realisasi.index');
    }

    public function generateNota(Request $request)
    {
        $this->validate($request, [
            'tgl_akhir' => 'after_or_equal:tgl_awal'
        ], [
            'tgl_akhir' => 'Periode tanggal akhir harus lebih besar dari periode tanggal awal'
        ]);

        try {
            $data = $this->service->getDataNota($request->all());
            if ($data->getStatusCode() != 200) {
                throw new Exception('Terjadi Kesalahan saat mengambil data, harap coba lagi nanti', 500);
            } else {
                $data = $data->getData()->data;
                $blade = view('report.realisasi.dataList', compact('data'))->render();

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
        ], [
            'tgl_akhir' => 'Periode gate akhir harus lebih besar dari periode gate awal'
        ]);

        $data = $this->service->getDataNota($request->all());
        if ($data->getStatusCode() != 200) {
            throw new Exception('Terjadi Kesalahan saat mengambil data gate periodik, harap coba lagi nanti', 500);
        } else {
            $data = $data->getData()->data;
            $kegiatan = $request->option_kegiatan ?? 'STUFFING & STRIPPING';

            // INITIATE SPREADSHEET
            $spreadsheet = new Spreadsheet();
            $activeWorksheet = $spreadsheet->getActiveSheet();
            $activeWorksheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $column = ['No.', 'No. Container', 'No. Request', 'Size', 'Type', 'Status', 'Kegiatan', 'Tgl. Approval', 'Tgl. Realisasi', 'Eksekutor', 'Tgl. Placement', 'Pemilik Barang', 'Kapal'];
            $columnWidth = [5, 18, 18, 10, 10, 10, 15, 18, 19, 20, 18, 30, 25];

            /** SET EXCEL TITLE */
            $last_column = Coordinate::stringFromColumnIndex(count($column));
            $rangeTitle = 'A1:' . $last_column . '1';
            $activeWorksheet->mergeCells($rangeTitle);
            $activeWorksheet->setCellValue('A1', "REPORT REALISASI $kegiatan");
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
            $activeWorksheet->setCellValue('A2', "PERIODE " . Carbon::parse($request->tgl_awal)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($request->tgl_akhir)->translatedFormat('d M Y'));
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
                $activeWorksheet->setCellValue($startColumn . '3', str_replace('_', ' ', $column[$i]));
                $activeWorksheet->getColumnDimension($startColumn)->setWidth($columnWidth[$i]);

                /** SET STYLING HEADER */
                // Allignment
                $activeWorksheet
                    ->getStyle($startColumn . '3')
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Font Size & Color
                $activeWorksheet
                    ->getStyle($startColumn . '3')
                    ->getFont()
                    ->setSize('11');

                // Row
                $activeWorksheet->getRowDimension('3')->setRowHeight(20);
                $startColumn++;
            }

            /** SET VALUE */
            $arrayData = [];
            foreach ($data as $key => $value) {
                array_push($arrayData, [
                    $key + 1,
                    $value->no_container,
                    $value->no_request,
                    $value->size_,
                    $value->type_,
                    $value->status_cont,
                    $value->kegiatan,
                    Carbon::parse($value->tgl_approve)->translatedFormat('d-M-Y'),
                    Carbon::parse($value->tgl_realisasi)->translatedFormat('d-M-Y'),
                    $value->nama_lengkap,
                    Carbon::parse($value->tgl_placement)->translatedFormat('d-M-Y'),
                    $value->nm_pbm,
                    $value->nm_kapal,
                ]);
            }
            $activeWorksheet->fromArray($arrayData, null, 'A4');

            $writer = new Xlsx($spreadsheet);
            $fileName = "LAPORAN REALISASI $kegiatan PERIODE " . $request->tgl_awal . ' - ' . $request->tgl_akhir;
            $file = $fileName . '.xlsx';
            $path = public_path() . "/storage/report/realisasi/" . $file;
            $writer->save($path);

            return response()->json([
                'status' => [
                    'msg' => 'OK',
                    'code' => 200
                ],
                'file' => $file,
                'filePath' => asset('storage/report/realisasi/' . $file)
            ], 200);
        }
    }
}
