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


   public function reportpdf(Request $request)
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

      // Inisialisasi TCPDF
      //$pdf = new TCPDF();
      $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
      $pdf->SetCreator(PDF_CREATOR);
      $pdf->SetAuthor('Admin');
      $pdf->SetTitle('Laporan Harian');
      $pdf->SetHeaderData('', 0, 'Laporan Harian', 'Generated by NBS');
      $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
      $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
      $pdf->SetMargins(10, 10, 10);
      $pdf->SetHeaderMargin(5);
      $pdf->SetFooterMargin(5);
      $pdf->SetAutoPageBreak(TRUE, 10);
      $pdf->AddPage();

      // $pdf = new TCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
      // $pdf->SetCreator(PDF_CREATOR);
      // $pdf->SetAuthor('Admin');
      // $pdf->SetTitle('Laporan Harian');
      // $pdf->SetMargins(10, 10, 10);
      // $pdf->SetAutoPageBreak(TRUE, 10);
      // $pdf->AddPage();
      // $pdf->Image(public_path('logo.png'), 85, 10, 40); 
      // $pdf->SetFont('dejavusans', 'B', 16);
      // $pdf->SetXY(10, 50);
      // $pdf->Cell(190, 10, 'Laporan Harian', 0, 1, 'C');
      // $pdf->Ln(10);

      $pdf->SetFont('dejavusans', 'B', 10);
      $pdf->SetX(5); // Geser header tabel ke kiri
      $pdf->Cell(10, 8, 'No', 1, 0, 'C');
      $pdf->Cell(30, 8, 'No.Request', 1, 0, 'C');
      $pdf->Cell(40, 8, 'No.Nota', 1, 0, 'C');
      $pdf->Cell(40, 8, 'No.Faktur', 1, 0, 'C');
      $pdf->Cell(25, 8, 'Total', 1, 0, 'C');
      $pdf->Cell(55, 8, 'Customer', 1, 1, 'C'); // '1' di akhir baris agar pindah ke baris berikutnya
      $pdf->SetFont('dejavusans', '', 8);

      // Loop Data
      foreach ($listDelivery as $index => $data) {
         $pdf->SetX(5); // Geser ke kiri sebelum mencetak isi tabel
         
         // Konversi <br> ke newline
         $no_nota = str_replace('<br>', "\n", $data->no_nota_mti);
         $no_faktur = str_replace('<br>', "\n", $data->no_faktur_mti);
         $customer = str_replace('<br>', "\n", $data->nm_pbm);
     
         // Hitung tinggi maksimum dari MultiCell
         $h1 = $pdf->GetStringHeight(40, $no_nota);
         $h2 = $pdf->GetStringHeight(40, $no_faktur);
         $h3 = $pdf->GetStringHeight(55, $customer);
         $max_height = max($h1, $h2, $h3, 8); // Minimal tinggi 8
     
         // Tampilkan data
         $pdf->Cell(10, $max_height, ($index + 1), 1, 0, 'C');
         $pdf->Cell(30, $max_height, $data->no_request, 1, 0, 'C');
     
         $x = $pdf->GetX(); $y = $pdf->GetY();
         $pdf->MultiCell(40, $max_height, $no_nota, 1, 'C');
         $pdf->SetXY($x + 40, $y);
     
         $x = $pdf->GetX(); $y = $pdf->GetY();
         $pdf->MultiCell(40, $max_height, $no_faktur, 1, 'C');
         $pdf->SetXY($x + 40, $y);
     
         $pdf->Cell(25, $max_height, number_format($data->kredit, 0, ',', '.'), 1, 0, 'C');
     
         $x = $pdf->GetX(); $y = $pdf->GetY();
         $pdf->MultiCell(55, $max_height, $customer, 1, 'L');
         $pdf->SetXY($x + 55, $y);
     
         $pdf->Ln($max_height);
     }
     
     

      // Outputkan file
      $pdf->Output('Laporan_Harian.pdf', 'D');
      exit;
   }


}
