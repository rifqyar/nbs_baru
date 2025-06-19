<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmateraiController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::connection('uster')->table('master_materai')
            ->select(
                'no_peraturan AS NO_PERATURAN',
                'NOMINAL AS NOMINAL',
                'tgl_peraturan AS TGL_PERATURAN',
                'TERPAKAI AS TERPAKAI',
                'SALDO AS SALDO'
            )
            ->whereNotNull('NO_PERATURAN');

        if ($request->has('id_req') && !empty($request->id_req)) {
            $query->where('NO_PERATURAN', $request->id_req);
        } elseif ($request->has('id_time') && !empty($request->id_time) && !$request->has('id_time2')) {
            $query->whereDate('tgl_peraturan', $request->id_time);
        } elseif ($request->has('id_time') && !empty($request->id_time) && $request->has('id_time2') && !empty($request->id_time2)) {
            $query->whereBetween('tgl_peraturan', [$request->id_time, $request->id_time2]);
        } else {
            $query->orderByDesc('id');
        }

        $results = $query->paginate(10); // Or whatever pagination limit you desire

        return view('report.ematerai.index', ['results' => $results]);
    }

    public function report(Request $request)
    {
        $id_req = $request->input('id_req');
        $id_time = $request->input('id_time');
        $id_time2 = $request->input('id_time2');

        try {
            $results = $this->getQueryResults($id_req, $id_time, $id_time2, 'B.NO_NPWP_PBM16');
        } catch (\Exception $e) {
            $results = $this->getQueryResults($id_req, $id_time, $id_time2, 'B.NO_NPWP_PBM');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'INVOICE DATE');
        $sheet->setCellValue('B1', 'NO NOTA');
        $sheet->setCellValue('C1', 'TARIF');
        $sheet->setCellValue('D1', 'NO FAKTUR');
        $sheet->setCellValue('E1', 'NPWP');
        $sheet->setCellValue('F1', 'CUSTOMER');
        $sheet->setCellValue('G1', 'ADMINISTRASI');
        $sheet->setCellValue('H1', 'DPP');
        $sheet->setCellValue('I1', 'PPN');
        $sheet->setCellValue('J1', 'MATERAI');
        $sheet->setCellValue('K1', 'TOTAL');
        $sheet->setCellValue('L1', 'BANK');
        $sheet->setCellValue('M1', 'NO PERATURAN');

        // Add data
        $rowNumber = 2;
        foreach ($results as $row) {
            $sheet->setCellValue('A' . $rowNumber, $row->tgl_simpan);
            $sheet->setCellValue('B' . $rowNumber, $row->no_nota_mti);
            $sheet->setCellValue('C' . $rowNumber, $row->tarif);
            $sheet->setCellValue('D' . $rowNumber, $row->no_faktur_mti);
            $sheet->setCellValue('E' . $rowNumber, $row->no_npwp_pbm);
            $sheet->setCellValue('F' . $rowNumber, $row->nm_pbm);
            $sheet->setCellValue('G' . $rowNumber, $row->administrasi);
            $sheet->setCellValue('H' . $rowNumber, $row->total);
            $sheet->setCellValue('I' . $rowNumber, $row->ppn);
            $sheet->setCellValue('J' . $rowNumber, $row->tarif);
            $sheet->setCellValue('K' . $rowNumber, $row->kredit);
            $sheet->setCellValue('L' . $rowNumber, $row->bank_id == '14004' ? 'BNI' : 'MANDIRI');
            $sheet->setCellValue('M' . $rowNumber, $row->no_peraturan);
            $rowNumber++;
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'ReportLaporanBeaMaterai-' . date("dmY") . '.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'. $filename .'"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    private function getQueryResults($id_req, $id_time, $id_time2, $npwp_column)
    {
        $query = DB::connection('uster')->table('ITPK_NOTA_DETAIL AS C')
            ->join('ITPK_NOTA_HEADER AS A', 'C.NO_NOTA_MTI', '=', 'A.NO_NOTA_MTI')
            ->join('MST_PELANGGAN AS B', 'A.CUSTOMER_NUMBER', '=', 'B.NO_ACCOUNT_PBM')
            ->select(
                'A.TGL_SIMPAN', 
                'A.NO_NOTA_MTI', 
                'C.TARIF', 
                'A.NO_FAKTUR_MTI', 
                DB::raw("$npwp_column AS NO_NPWP_PBM"), 
                'B.NM_PBM', 
                'A.ADMINISTRASI', 
                'A.TOTAL', 
                'A.PPN', 
                'A.KREDIT', 
                'A.BANK_ID', 
                'A.NO_PERATURAN'
            )
            ->where(function($query) {
                $query->where('C.tipe_layanan', 'MATERAI_N_USTER')
                      ->orWhere('C.tarif', 3000)
                      ->orWhere('C.tarif', 6000);
            })
            ->where('A.STATUS', '<>', 5);

        if ($id_time && $id_time2 && $id_req) {
            $query->whereBetween('A.TGL_SIMPAN', [$id_time, $id_time2])
                  ->where('A.NO_PERATURAN', $id_req);
        } elseif ($id_time && $id_time2) {
            $query->whereBetween('A.TGL_SIMPAN', [$id_time, $id_time2]);
        } elseif ($id_req && $id_time) {
            $query->whereDate('A.TGL_SIMPAN', $id_time)
                  ->where('A.NO_PERATURAN', $id_req);
        } elseif ($id_time) {
            $query->whereDate('A.TGL_SIMPAN', $id_time);
        } elseif ($id_req) {
            $query->where('A.NO_PERATURAN', $id_req);
        }

        return $query->orderByDesc('A.TGL_SIMPAN')->get();
    }
}
