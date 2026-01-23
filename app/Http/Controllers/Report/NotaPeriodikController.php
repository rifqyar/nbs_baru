<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\NotaPeriodikService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NotaPeriodikController extends Controller
{
    protected $service;
    public function __construct(NotaPeriodikService $notaService)
    {
        $this->service = $notaService;
    }

    public function index()
    {
        return view('report.notaperiodik.index');
    }

    public function generateNota(Request $request)
    {
        $this->validate($request, [
            'tgl_akhir' => 'after_or_equal:tgl_awal'
        ], [
            'tgl_akhir' => 'Periode tanggal akhir harus lebih besar dari periode tanggal awal'
        ]);

        try {
            $draw   = $request->draw;
            $start  = $request->start;
            $length = $request->length;

            $data = $this->service->getDataNota($request->all());
            if ($data->getStatusCode() != 200) {
                throw new Exception('Terjadi Kesalahan saat mengambil data nota, harap coba lagi nanti', 500);
            } else {
                $baseQuery = $data->getData()->query;

                // total
                $total = DB::connection('uster')
                    ->selectOne("SELECT COUNT(*) TOTAL FROM ($baseQuery)")->total;

                // paging
                $query = "
                    SELECT * FROM (
                        SELECT a.*, ROWNUM rn FROM ($baseQuery) a
                    ) WHERE rn BETWEEN " . ($start + 1) . " AND " . ($start + $length) . "
                ";

                $rows = DB::connection('uster')->select($query);

                $blade = view('report.notaperiodik.dataList')->render();
                $data = collect($rows)->map(function ($dt) {
                    return [
                        'no_nota_mti'      => $dt->no_nota_mti,
                        'no_faktur_mti'    => $dt->no_faktur_mti,
                        'no_request'       => $dt->no_request,
                        'kegiatan'         => $dt->kegiatan,
                        'tgl_nota'         => \Carbon\Carbon::parse($dt->tgl_nota)->format('Y-m-d'),
                        'emkl_full'        => $dt->emkl,
                        'emkl_short'       => Str::limit($dt->emkl, 25),
                        'bayar'            => $dt->bayar,
                        'total_tagihan'    => str_replace(',', '.', $dt->total_tagihan),
                        'lunas'            => $dt->lunas,
                        'status'           => $dt->status,
                        'transfer'         => $dt->transfer,
                        'receipt_account'  => $dt->receipt_account,
                    ];
                });

                return response()->json([
                    'status' => [
                        'msg' => 'OK',
                        'code' => 200
                    ],
                    'blade' => $blade,
                    'data' => $data,
                    "draw" => intval($draw),
                    "recordsTotal" => $total,
                    "recordsFiltered" => $total,
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
        try {
            $data = $this->service->getDataNota($request->all());
            if ($data->getStatusCode() != 200) {
                throw new Exception('Terjadi Kesalahan saat mengambil data nota, harap coba lagi nanti', 500);
            } else {
                $data = $data->getData()->data;

                // INITIATE SPREADSHEET
                $spreadsheet = new Spreadsheet();
                $activeWorksheet = $spreadsheet->getActiveSheet();
                $activeWorksheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                /** SET EXCEL TITLE */
                $column = ['No.', 'No. Nota', 'No. Faktur Pajak', 'No. Request', 'Kegiatan', 'Tgl. Kegiatan', 'Pemilik Barang', 'Pembayaran', 'Total Tagihan', 'Status Lunas', 'Status Batal/Tidak', 'Status Nota', 'Status Transfer ke Simkeu', 'Bank'];
                $columnSameTable = ['No.', 'no_nota_mti', 'no_faktur_mti', 'no_request', 'kegiatan', 'tgl_nota', 'emkl', 'bayar', 'total_tagihan', 'lunas', 'status', 'status_nota', 'transfer', 'receipt_account'];
                $columnWidth = [5, 17, 25, 20, 20, 18, 40, 15, 15, 15, 20, 17, 27, 32];

                $last_column = Coordinate::stringFromColumnIndex(count($column));
                $rangeTitle = 'A1:' . $last_column . '2';
                $activeWorksheet->mergeCells($rangeTitle);
                $activeWorksheet->setCellValue('A1', "LAPORAN NOTA PER PERIODIK TANGGAL  " . Carbon::parse($request->tgl_awal)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($request->tgl_akhir)->translatedFormat('d M Y'));
                $activeWorksheet->getStyle($rangeTitle)
                    ->getFont()
                    ->setSize('12')
                    ->setBold(true);
                $activeWorksheet->getStyle($rangeTitle)
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $activeWorksheet
                    ->getStyle('A1')
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFF00');

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
                $startValueRow = 4;
                foreach ($data as $key => $value) {
                    $startValueCol = 'A';
                    $valArr = json_decode(json_encode($value), true);

                    for ($i = 0; $i < count($columnSameTable); $i++) {
                        if ($columnSameTable[$i] == 'No.') {
                            $colValue = $key + 1;
                        } else if ($columnSameTable[$i] == 'tgl_nota') {
                            $colValue = Carbon::parse($valArr[$columnSameTable[$i]])->translatedFormat('d M Y');
                        } else if ($columnSameTable[$i] == 'status_nota') {
                            $colValue = 'Ready To Transfer';
                        } else if ($columnSameTable[$i] == 'transfer') {
                            if ($valArr[$columnSameTable[$i]] == 'Y') {
                                $colValue = 'Sudah Transfer';
                            } else {
                                $colValue = 'Belum Transfer';
                            }
                        } else {
                            $colValue = $valArr[$columnSameTable[$i]];
                        }

                        $activeWorksheet->setCellValue($startValueCol . $startValueRow, $colValue);
                        $activeWorksheet->getStyle($startValueCol . $startValueRow)
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_TEXT);

                        $activeWorksheet->getStyle($startValueCol . $startValueRow)
                            ->getAlignment()
                            ->setVertical(Alignment::HORIZONTAL_LEFT);

                        $startValueCol++;
                    }

                    $startValueRow++;
                }

                $writer = new Xlsx($spreadsheet);
                $fileName = "LAPORAN NOTA PER PERIODIK " . $request->tgl_awal . ' - ' . $request->tgl_akhir;
                $file = $fileName . '.xlsx';
                $path = public_path() . "/storage/report/nota_periodik/" . $file;
                $writer->save($path);

                return response()->json([
                    'status' => [
                        'msg' => 'OK',
                        'code' => 200
                    ],
                    'file' => $file,
                    'filePath' => asset('storage/report/nota_periodik/' . $file)
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
}
