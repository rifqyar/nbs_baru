<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use TCPDF;

class LaporanHarianController extends Controller
{
   function index()
   {
      return view('report.harian.laporan');
   }

   public function dataTables(Request $request): JsonResponse
   {
      $idreq = $request->input('id_req');
      $idtime = $request->input('id_time');

      $bindings = [];

      $query = "SELECT * FROM (
           SELECT A.NO_REQUEST,
                  A.KREDIT,
                  A.NO_NOTA_MTI || '<br>' || TO_CHAR(A.TGL_SIMPAN, 'DD-MM-YYYY') AS NO_NOTA_MTI,
                  A.NO_FAKTUR_MTI || '<br>' || TO_CHAR(A.TGL_SIMPAN, 'DD-MM-YYYY') AS NO_FAKTUR_MTI,
                  A.TGL_SIMPAN,
                  A.SP_MTI,
                  B.NM_PBM || '<br>' || B.NO_NPWP_PBM AS NM_PBM,
                  RANK() OVER (ORDER BY A.TGL_SIMPAN DESC, ROWNUM DESC) r
           FROM ITPK_NOTA_HEADER A
           INNER JOIN MST_PELANGGAN B ON A.CUSTOMER_NUMBER = B.NO_ACCOUNT_PBM
           ORDER BY A.TGL_SIMPAN DESC
       ) WHERE r < 500";

      if ($idreq) {
         $query .= " AND NO_REQUEST = :idreq";
         $bindings['idreq'] = $idreq;
      }

      if ($idtime) {
         $query .= " AND TO_CHAR(TGL_SIMPAN,'DD-MM-YYYY') = :idtime";
         $bindings['idtime'] = $idtime;
      }

      $listDelivery = DB::connection('uster')->select($query, $bindings);

      return DataTables::of($listDelivery)->make(true);
   }

   public function report(Request $request)
   {
      $idreq = $request->input('id_req');
      $idtime = $request->input('id_time');

      $bindings = [];

      $query = "SELECT * FROM (
        SELECT A.NO_REQUEST,
               A.KREDIT,
               A.NO_NOTA_MTI || '<br>' || TO_CHAR(A.TGL_SIMPAN, 'DD-MM-YYYY') AS NO_NOTA_MTI,
               A.NO_FAKTUR_MTI || '<br>' || TO_CHAR(A.TGL_SIMPAN, 'DD-MM-YYYY') AS NO_FAKTUR_MTI,
               A.TGL_SIMPAN,
               A.SP_MTI,
               B.NM_PBM || '<br>' || B.NO_NPWP_PBM AS NM_PBM,
               RANK() OVER (ORDER BY A.TGL_SIMPAN DESC, ROWNUM DESC) r
        FROM ITPK_NOTA_HEADER A
        INNER JOIN MST_PELANGGAN B ON A.CUSTOMER_NUMBER = B.NO_ACCOUNT_PBM
        ORDER BY A.TGL_SIMPAN DESC
    ) WHERE r < 500";

      if ($idreq) {
         $query .= " AND NO_REQUEST = :idreq";
         $bindings['idreq'] = $idreq;
      }

      if ($idtime) {
         $query .= " AND TO_CHAR(TGL_SIMPAN,'DD-MM-YYYY') = :idtime";
         $bindings['idtime'] = $idtime;
      }

      $listDelivery = DB::connection('uster')->select($query, $bindings);

      // Create a new Spreadsheet
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();

      // Set header row
      $sheet->setCellValue('A1', 'No');
      $sheet->setCellValue('B1', 'No. Request');
      $sheet->setCellValue('C1', 'No. Nota');
      $sheet->setCellValue('D1', 'No. Faktur');
      $sheet->setCellValue('E1', 'Total');
      $sheet->setCellValue('F1', 'Customer');
      $sheet->setCellValue('G1', 'No. SP');

      // Apply bold font to header row
      $sheet->getStyle('A1:G1')->getFont()->setBold(true);

      // Initialize row number
      $rowNumber = 2;

      foreach ($listDelivery as $index => $data) {
         // Replace HTML <br> tags with newlines for Excel
         $no_nota_mti = str_replace('<br>', "\n", $data->no_nota_mti);
         $no_faktur_mti = str_replace('<br>', "\n", $data->no_faktur_mti);
         $nm_pbm = str_replace('<br>', "\n", $data->nm_pbm);

         // Set cell values
         $sheet->setCellValue('A' . $rowNumber, $index + 1);
         $sheet->setCellValue('B' . $rowNumber, $data->no_request);
         $sheet->setCellValue('C' . $rowNumber, $no_nota_mti);
         $sheet->setCellValue('D' . $rowNumber, $no_faktur_mti);
         $sheet->setCellValue('E' . $rowNumber, $data->kredit);
         $sheet->setCellValue('F' . $rowNumber, $nm_pbm);
         $sheet->setCellValue('G' . $rowNumber, $data->sp_mti);

         // Enable text wrapping for cells with newlines
         $sheet->getStyle('C' . $rowNumber)->getAlignment()->setWrapText(true);
         $sheet->getStyle('D' . $rowNumber)->getAlignment()->setWrapText(true);
         $sheet->getStyle('F' . $rowNumber)->getAlignment()->setWrapText(true);

         // Format the 'Total' column as currency
         $sheet->getStyle('E' . $rowNumber)
            ->getNumberFormat()
            ->setFormatCode('#,##0');

         $rowNumber++;
      }

      // Auto-size columns
      foreach (range('A', 'G') as $columnID) {
         $sheet->getColumnDimension($columnID)->setAutoSize(true);
      }

      // Set the filename
      $filename = 'Laporan_Laporan_Harian.xlsx';

      // Redirect output to a client’s web browser (Excel)
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header("Content-Disposition: attachment;filename=\"{$filename}\"");
      header('Cache-Control: max-age=0');
      // If you're serving to IE over SSL, then the following may be needed
      header('Cache-Control: max-age=1');

      // Create a Writer and save the file to output
      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
      exit;
   }


   public function generateNosp(Request $request)
   {
        $id_req = $request->input('id_req');
        $id_time = $request->input('id_time');

        // Ambil data counter dari database Oracle
        $counter = DB::connection('uster')->selectOne("
            SELECT TAHUN, LPAD(SEQUENCE+1,'4','0') SEQ, (SEQUENCE+1) SEQUE 
            FROM MTI_COUNTER_SP WHERE TAHUN = TO_CHAR(SYSDATE,'YYYY')
        ");

        if (!$counter || empty($counter->seq)) {
            DB::connection('uster')->insert("
                INSERT INTO MTI_COUNTER_SP (TAHUN, SEQUENCE) 
                VALUES (TO_CHAR(SYSDATE,'YYYY'), 0)
            ");

            // Ambil kembali setelah insert
            $counter = DB::connection('uster')->selectOne("
                SELECT TAHUN, LPAD(SEQUENCE+1,'4','0') SEQ, (SEQUENCE+1) SEQUE 
                FROM MTI_COUNTER_SP WHERE TAHUN = TO_CHAR(SYSDATE,'YYYY')
            ");
        }
        $NOSP = "SPPTK" . $counter->tahun . "-" . $counter->seq;

        // Ambil data berdasarkan tanggal simpan
        $data = DB::connection('uster')->select("
            SELECT NO_REQUEST, TO_CHAR(TGL_SIMPAN,'DD-MM-YYYY') TGL_SIMPAN, NO_NOTA_MTI, NO_FAKTUR_MTI, SP_MTI 
            FROM ITPK_NOTA_HEADER WHERE TO_CHAR(TGL_SIMPAN,'YYYY-MM-DD') = :id_time
        ", ['id_time' => $id_time]);
         //dd($data);
        $message = "";
        $stat = false;

        $updatedRecords = []; 

         foreach ($data as $row) {
            if (!is_null($row->sp_mti)) {
               $message = "NO SP Sudah Ada, ";
            } else {
               
               $stat = DB::connection('uster')->update("
                     UPDATE ITPK_NOTA_HEADER SET SP_MTI = :SP_MTI 
                     WHERE STATUS <> 5 AND NO_REQUEST = :NO_REQUEST 
                     AND TO_CHAR(TGL_SIMPAN,'DD-MM-YYYY') = :TGL_SIMPAN
               ", [
                     'SP_MTI' => $NOSP,
                     'NO_REQUEST' => $row->no_request,
                     'TGL_SIMPAN' => $row->tgl_simpan
               ]);

              
               if ($stat > 0) {
                     $updatedRecords[] = [
                        'SP_MTI' => $NOSP,
                        'NO_REQUEST' => $row->no_request,
                        'TGL_SIMPAN' => $row->tgl_simpan
                     ];
               }

              
               DB::connection('uster')->update("
                     UPDATE MTI_COUNTER_SP SET SEQUENCE = :SEQUE WHERE TAHUN = TO_CHAR(SYSDATE,'YYYY')
               ", ['SEQUE' => $counter->seque]);
            }
         }

        
         Log::info('Data yang berhasil diperbarui:', $updatedRecords);

        

        if (!$stat) {
            return response()->json(['message' => $message . "Data Gagal Diproses!"], 400);
        }

        return response()->json(['message' => "Data Berhasil Diproses!"], 200);
   }



}
