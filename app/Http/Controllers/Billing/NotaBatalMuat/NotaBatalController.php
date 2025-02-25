<?php

namespace App\Http\Controllers\Billing\NotaBatalMuat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use \Yajra\DataTables\DataTables;
use Elibyy\TCPDF\Facades\TCPDF;
use Elibyy\TCPDF\TCPDF as TCPDFTCPDF;



class NotaBatalController extends Controller
{
    public function index()
    {
        return view('billing.nota-batal-muat.index');
    }

    public function datatable(Request $request)
    {

        $from = $request->has('from') ? $request->from : null;
        $to = $request->has('to') ? $request->to : null;


        if ($from) {
            $from = Carbon::createFromFormat('Y-m-d', $from)->format('d-m-Y');
        }

        if ($to) {
            $to = Carbon::createFromFormat('Y-m-d', $to)->format('d-m-Y');
        }

        $no_req = isset($request->search['value']) ? $request->search['value'] : null;

        $start = $request->start;
        $length = $request->length;
        $limit = ($length * 3) + $start;


        if ((isset($_POST['NO_REQ'])) && ($from == NULL) && ($to == NULL)) {
            $query_list = "SELECT * FROM (SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.NOTA,
            b.NM_PBM AS NAMA_EMKL,
            COUNT(c.NO_CONTAINER) JUMLAH
            FROM REQUEST_BATAL_MUAT a,
            V_MST_PBM b,
            CONTAINER_BATAL_MUAT c
            WHERE a.KD_EMKL = b.KD_PBM AND b.KD_CABANG = '05'
            AND a.NO_REQUEST = c.NO_REQUEST
            AND a.NO_REQUEST LIKE '$no_req%'
            AND a.BIAYA = 'Y'
            GROUP BY a.NO_REQUEST, a.TGL_REQUEST, a.NOTA,
            b.NM_PBM
            ORDER BY a.TGL_REQUEST DESC) WHERE ROWNUM <= $limit";
        } else if (($no_req == NULL) && (isset($_POST['FROM'])) && (isset($_POST['TO']))) {
            $query_list = "SELECT * FROM (SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.NOTA,
            b.NM_PBM AS NAMA_EMKL,
            COUNT(c.NO_CONTAINER) JUMLAH
            FROM REQUEST_BATAL_MUAT a,
            V_MST_PBM b,
            CONTAINER_BATAL_MUAT c
            WHERE a.KD_EMKL = b.KD_PBM AND b.KD_CABANG = '05'
            AND a.BIAYA = 'Y'
            AND a.NO_REQUEST = c.NO_REQUEST
            AND a.TGL_REQUEST BETWEEN TO_DATE('$from','yyyy/mm/dd') AND TO_DATE('$to','yyyy/mm/dd')
            GROUP BY a.NO_REQUEST, a.TGL_REQUEST, a.NOTA,
            b.NM_PBM
            ORDER BY a.TGL_REQUEST DESC) WHERE ROWNUM <= $limit ";
        } else if ((isset($_POST['NO_REQ'])) && (isset($_POST['FROM'])) && (isset($_POST['TO']))) {
            $query_list = "SELECT * FROM (SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.NOTA,
            b.NM_PBM AS NAMA_EMKL,
            COUNT(c.NO_CONTAINER) JUMLAH
            FROM REQUEST_BATAL_MUAT a,
            V_MST_PBM b,
            CONTAINER_BATAL_MUAT c
            WHERE a.KD_EMKL = b.KD_PBM AND b.KD_CABANG = '05'
            AND a.BIAYA = 'Y'
            AND a.NO_REQUEST = c.NO_REQUEST
            AND a.NO_REQUEST LIKE '$no_req%'
            AND a.TGL_REQUEST BETWEEN TO_DATE('$from','yyyy/mm/dd') AND TO_DATE('$to','yyyy/mm/dd')
            GROUP BY a.NO_REQUEST, a.TGL_REQUEST, a.NOTA,
            b.NM_PBM
            ORDER BY a.TGL_REQUEST DESC) WHERE ROWNUM <= $limit ";
        } else {
            $query_list    = "SELECT * FROM (SELECT  a.NO_REQUEST, a.TGL_REQUEST, a.NOTA,
            b.NM_PBM AS NAMA_EMKL,
            COUNT(c.NO_CONTAINER) JUMLAH
            FROM REQUEST_BATAL_MUAT a,
            V_MST_PBM b,
            CONTAINER_BATAL_MUAT c
            WHERE a.KD_EMKL = b.KD_PBM AND b.KD_CABANG = '05'
            AND a.BIAYA = 'Y'
            AND a.NO_REQUEST = c.NO_REQUEST
            AND a.BIAYA = 'Y'
            GROUP BY a.NO_REQUEST, a.TGL_REQUEST, a.NOTA,
            b.NM_PBM
            ORDER BY a.TGL_REQUEST DESC) WHERE ROWNUM <= $limit ";
        }

        $data =  DB::connection('uster')->select($query_list);
        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                $no_req = $data->no_request;

                // Query to check if the request exists
                $row_cek = DB::connection('uster')
                    ->table('REQUEST_BATAL_MUAT')
                    ->select('NOTA', 'KOREKSI')
                    ->where('NO_REQUEST', $no_req)
                    ->first();

                if ($row_cek) {
                    $nota = $row_cek->nota ?? '';
                    $koreksi = $row_cek->koreksi ?? '';

                    // Generate the appropriate link based on the NOTA and KOREKSI values
                    if ($nota !== 'Y' && $koreksi !== 'Y') {
                        return '<a href="nota_batalmuat/print_nota?no_req=' . $no_req . '&koreksi=N" target="_blank" class="btn btn-primary"><i class="fas fa-file-alt"></i> Preview Proforma</a>';
                    } elseif ($nota !== 'Y' && $koreksi === 'Y') {
                        return '<a href="nota_batalmuat/print_nota?no_req=' . $no_req . '&koreksi=Y" target="_blank" class="btn btn-primary"><i class="fas fa-file-alt"></i> Preview Proforma</a>';
                    } elseif (($nota === 'Y' && $koreksi !== 'Y') || ($nota === 'Y' && $koreksi === 'Y')) {
                        return '<a href="nota_batalmuat/print/print_proforma?no_req=' . $no_req . '" target="_blank" class="btn btn-success"><i class="fas fa-print"></i> Cetak Proforma</a>';
                    }
                }

                return ''; // Return empty string if no action is required
            })
            ->make(true);
    }

    public function print_proforma(Request $request)
    {

        $pdf = new TCPDF();
        $pdf::changeFormat('A7');
        $pdf::reset();

        // set default monospaced font
        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf::SetMargins(1, 3, 0);
        //$pdf::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf::SetFooterMargin(PDF_MARGIN_FOOTER);

        $pdf::setPrintHeader(false);

        //set auto page breaks
        $pdf::SetAutoPageBreak(TRUE, 10);

        //set image scale factor
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);



        // ---------------------------------------------------------


        $no_req = 'BMU0723000001';
        $id_user = session('PENGGUNA_ID');
        $query = "SELECT NO_NOTA FROM nota_batal_muat WHERE TRIM(NO_REQUEST) = TRIM('$no_req') AND STATUS <> 'BATAL'";
        $hasil = DB::connection('uster')->selectOne($query);
        $notanya = $hasil->no_nota;

        //NOTA MTI -> NO_NOTA_MTI
        $query = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT  , a.NPWP, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
       CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAME, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
        THEN a.NO_NOTA
        ELSE A.NO_FAKTUR END NO_FAKTUR_, F_CORPORATE(c.TGL_REQUEST) CORPORATE
                            FROM nota_batal_muat a, request_batal_muat c, billing_nbs.tb_user mu where
                            a.NO_REQUEST = c.NO_REQUEST
                            AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_batal_muat d WHERE d.NO_REQUEST = '$no_req' )
                            and c.NO_REQUEST = '$no_req'
                            and a.nipp_user = mu.id(+)";
        $data = DB::connection('uster')->selectOne($query);
        $req_tgl = $data->tgl_request;
        $nama_lengkap  = $data->name;
        $lunas = $data->lunas;

        if (!$request->first) {
            $nama_lengkap .= '<br/>' . 'Reprinted by ' . session('NAMA_LENGKAP');
        }
        date_default_timezone_set('Asia/Jakarta');
        $date = date('d M Y H:i:s');
        //print_r("SELECT * from TB_NOTA_DELIVERY_D A, TB_NOTA_DELIVERY_H B, MASTER_BARANG C WHERE A.ID_NOTA='$p1'  AND A.ID_NOTA=B.ID_NOTA AND C.KODE_BARANG=A.ID_CONT");die;

        $corporate_name     = 'PT. Multi Terminal Indonesia';

        /**hitung materai Fauzan 24 Agustus 2020*/
        $query_mtr = "SELECT TO_CHAR (a.BIAYA, '999,999,999,999') BEA_MATERAI, a.BIAYA
          FROM NOTA_BATAL_MUAT_D a
         WHERE a.ID_NOTA = '$notanya' AND a.KETERANGAN ='MATERAI' ";
        //print_r($query_mtr);
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        if ($data_mtr->biaya ?? NULL > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        ///print_r($bea_materai);die();
        /*end hitung materai Fauzan 24 Agustus 2020*/

        if ($lunas == 'YES') {
            $mat = "SELECT * FROM itpk_nota_header WHERE NO_REQUEST='$no_req'";

            $mat2 = DB::connection('uster')->selectOne($mat);
            $no_mat    = $mat2->no_peraturan;
        } else {
            $mat = "SELECT * FROM MASTER_MATERAI WHERE STATUS='Y'";

            $mat2 = DB::connection('uster')->selectOne($mat);
            $no_mat    = $mat2->no_peraturan;
        }


        //get data no peraturan master materai

        //end

        $query_dtl = "SELECT a.JML_HARI, TO_CHAR(a.TARIF, '999,999,999,999') AS TARIF, TO_CHAR(a.BIAYA, '999,999,999,999') AS BIAYA, a.KETERANGAN, a.HZ,
        a.JML_CONT, TO_DATE(a.START_STACK,'dd/mm/yyyy') START_STACK, TO_DATE(a.END_STACK,'dd/mm/yyyy') END_STACK, b.SIZE_, b.TYPE_, b.STATUS FROM NOTA_BATAL_MUAT_D a
        , iso_code b WHERE a.id_iso = b.id_iso and a.ID_NOTA = '$notanya' AND A.KETERANGAN NOT IN ('ADMIN NOTA','MATERAI')";
        /**Fauzan modif 24 Agustus 2020 [NOT IN MATERAI]*/
        $row2 = DB::connection('uster')->select($query_dtl);

        $i = 0;
        $detail = '';
        foreach ($row2 as $rows) {
            if ($rows->keterangan != 'MONITORING DAN LISTRIK') {
                $rows->tgl_start_stack =  $rows->tgl_start_stack ?? null;
                $den = '(' . $rows->tgl_start_stack ?? null . ' s/d ' . $rows->tgl_end_stack ?? null . ')' . $rows->jumlah_hari ?? null . 'hari';
            } else {
                $den = $rows->jumlah_hari . ' Shift';
            }

            if ($rows->tgl_start_stack <> '') {
                $detail .= '<tr><td colspan="3" width="100"><font size="6">' . '<b>' . $rows->keterangan . '</b>' . '</font></td>
                        <td width="10" align="left"><font size="6">' . '<b>' . $rows->jml_cont . '</b>' . '</font></td>
                        <td width="50" align="left"><font size="6">' . '<b>' . $rows->size_ . " " . $rows->type_ . " " . $rows->status . '</b>' . '</font></td>
                        <td width="10" align="left"><font size="6">' . '<b>' . $rows->hz . '</b>' . '</font></td>
                        <td width="43" align="right"><font size="6">' . '<b>' . $rows->tarif . '</b>' . '</font></td>
                        <td width="35" align="right"><font size="6">' . '<b>' . $rows->biaya . '</b>' . '</font></td>
                        </tr>
                        <tr><td colspan="8"><font size="6"><i><b>' . $den . '</i></b></font></td>
                         </tr>';
            } else {
                $detail .= '<tr><td colspan="3" width="100"><font size="6">' . '<b>' . $rows->keterangan . '</b>' . '</font></td>
                        <td width="10" align="left"><font size="6">' . '<b>' . $rows->jml_cont . '</b>' . '</font></td>
                        <td width="50" align="left"><font size="6">' . '<b>' . $rows->size_ . " " . $rows->type_ . " " . $rows->status . '</b>' . '</font></td>
                        <td width="10" align="left"><font size="6">' . '<b>' . $rows->hz . '</b>' . '</font></td>
                        <td width="43" align="right"><font size="6">' . '<b>' . $rows->tarif . '</b>' . '</font></td>
                        <td width="35" align="right"><font size="6">' . '<b>' . $rows->biaya . '</b>' . '</font></td>
                        </tr>   ';
            }

            $i++;
        }


        // jumlah detail barangnya
        $query_jum = "SELECT COUNT(1) JUM_DETAIL FROM NOTA_RECEIVING_D A WHERE A.NO_NOTA='$notanya'";
        $data_jum = DB::connection('uster')->selectOne($query_jum);
        $jum_data_page = 18; //jumlah data dibatasi per page 18 data
        $jum_page = ceil($data_jum->jum_detail / $jum_data_page);   //hasil bagi pembulatan ke atas
        if (($data_jum->jum_detail % $jum_data_page) > 10 || ($data_jum->jum_detail % $jum_data_page) == 0)  $jum_page++;    //jika pada page terakhir jumlah data melebihi 12, tambah 1 page lagi
        $jum_page = 1;
        // echo $jum_page;
        // die();
        for ($pg = 1; $pg <= $jum_page; $pg++) {
            // add a page
            $pdf::AddPage();
            // set font
            $pdf::SetFont('helvetica', '', 6);

            $data->pelabuhan_tujuan =  $data->pelabuhan_tujuan ?? '';
            $data->ipod =  $data->ipod ?? '';
            $data->voyage =  $data->voyage ?? '';
            $data->vessel =  $data->vessel ?? '';
            $data->tgl_muat =  $data->tgl_muat ?? '';
            $data->tgl_stack =  $data->tgl_stack ?? '';
            $tbl = <<<EOD
            <table border='0'>
              <tr>
                    <td COLSPAN="2" align="left"><font size="8"><b>$data->no_nota_mti</b></font></td>
                 <td width="100"><font size="8"><b>$date</b></font></td>
                </tr>
                <tr>
                    <td COLSPAN="3" align="left"><font size="8"><b>$data->no_request</b></font>  </td>
                </tr>
                <tr>
                    <td COLSPAN="6">POD: $data->ipod | $data->pelabuhan_tujuan</td>
                </tr>
                <tr>
                    <td COLSPAN="6"><b>BATAL MUAT</b></td>
                </tr>
                <tr>
                    <td COLSPAN="4" align="left"><font size="6"><b>$data->nama</b></font></td>
                </tr>
                <tr>
                    <td COLSPAN="4" align="left"><font size="6"><b>$data->npwp</b></font></td>
                </tr>
                <tr>
                    <td COLSPAN="4" align="left"><font size="6"><b>$data->alamat</b></font></td>
                </tr>
                <tr>
                    <td COLSPAN="4" align="left"><font size="6"><b>$data->vessel / $data->voyage</b></font></td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td width="80" align="left"><font size="6"><b>PENUMPUKAN DARI :</b></font> </td>
                    <td colspan="5"><font size="6"><b>$data->tgl_stack s/d $data->tgl_muat</b></font></td>
                </tr>
                <tr>
                    <th colspan="3" width="100"><font size="6"><b>KETERANGAN</b></font></th>
                    <th width="10" align="left"><font size="6"><b>BX</b></font></th>

                    <th width="50" align="left"><font size="6"><b>CONTENT</b></font></th>
                    <th width="10" align="left"><font size="6"><b>HZ</b></font></th>

                    <th width="43" align="left"><font size="6"><b>TARIF</b></font></th>
                    <th width="43" align="left"><font size="6" ><b>JUMLAH</b></font></th>
                </tr>

                <tr>
                    <td colspoan="14">
                        <hr style="border: 2px dashed #C0C0C0" color="#FFFFFF" size="6" width="700">
                    </td>
                </tr>
                $detail
                <tr>
                    <td colspoan="14">
                        <hr style="border: 2px dashed #C0C0C0" color="#FFFFFF" size="6" width="700">
                    </td>
                </tr>

                </table>

            EOD;
            $row = '';
            $tbl_materai = '';
            $kotak = '';
            $space = '';
            if ($data_mtr->biaya ?? NULL > 0) {
                $tbl_materai .= ' <tr>
                                <td colspan="6" align="right"><b>Bea Materai :</b></td>
                                <td colspan="2" align="right"><b>' . $bea_materai . '</b></td>
                            </tr> ';
                $row .= '<tr><td colspan="6"></td>
                                <td width="225" colspan="2" align="right"></td>
                            </tr>';
                $kotak .=  '<tr><td colspan="4" align="left"><font size="5px">Bea Materai Lunas Dengan Sistem Nomor Ijin :' . $no_mat . '</font></td><td></td>

                                <td width="80" colspan="2" align="center" border="1">Termasuk Bea Materai<br><font size="7">Rp.' . $bea_materai . '</font></td><td></td></tr>
                            ';
            }


            $tbl .= <<<EOD
            <table>
                    <tr>
                        <td colspan="6" align="right"><b>Discount :</b></td>
                        <td width="50" colspan="2" align="right"><b>0.00</b></td>
                    </tr>
                    <tr>

                        <td colspan="6" align="right"><b>Administrasi :</b></td>
                        <td colspan="2" align="right"><b>$data->adm_nota</b></td>
                    </tr>
                    <tr>

                        <td colspan="6" align="right"><b>Dasar Peng. Pajak :</b></td>
                        <td colspan="2" align="right"><b>$data->tagihan</b></td>
                    </tr>
                    <tr>

                        <td colspan="6" align="right"><b>Jumlah PPN :</b></td>
                        <td colspan="2" align="right"><b>$data->ppn</b></td>
                    </tr>
                    $tbl_materai
                    <tr>

                        <td colspan="6" align="right"><font size="8"><b>Jumlah Dibayar :</b></font></td>
                        <td colspan="2" align="right"><font size="8"><b>$data->total_tagihan</b></font></td>
                    </tr>
                    </table>
                    printed by $nama_lengkap
                    <br>
                    <table>
                    $row
                    $kotak
                    </table>
                    <h2>PT Multi Terminal Indonesia</h2>


            EOD;
            $tbl .= <<<EOD
            <table>
                    <tr>
                    <td colspan="8">
                        <hr style="border: dashed 2px #C0C0C0" color="#FFFFFF" size="6" width="700">
                    </td>
                    </tr>
                    <tr>
                    <td colspan="8">
                        <i>form untuk Bank</i>
                    </td>
                    </tr>
                    <tr>
                    <td colspan="8">
                        &nbsp;
                    </td>
                    </tr>
                    <tr>
                    <td colspan="8">
                        &nbsp;
                    </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right"><font size="8"><b>Nomor Invoice :</b></font></td>
                        <td colspan="4" align="left"><font size="8"> <b>$data->no_nota_mti</b></font></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right"><font size="8"><b>Customer :</b></font></td>
                        <td colspan="4" align="left"><font size="8"> <b>$data->nama</b></font></td>
                    </tr>
                    <tr>

                        <td colspan="3" align="right"><font size="8"><b>Jumlah Dibayar :</b></font></td>
                        <td colspan="4" align="left"><font size="8"> Rp. <b>$data->total_tagihan</b></font></td>
                    </tr>

                    </table>
                    <br>
                    <br>
                    <br>
                    <br>

        EOD;

            $style = array(
                'position' => '',
                'align' => 'C',
                'stretch' => false,
                'fitwidth' => true,
                'cellfitalign' => '',
                //'border' => true,
                'hpadding' => 'auto',
                'vpadding' => 'auto',
                'fgcolor' => array(0, 0, 0),
                'bgcolor' => false, //array(255,255,255),
                'text' => true,
                'font' => 'helvetica',
                'fontsize' => 4,
                'stretchtext' => 4
            );

            $nota_mti = $data->no_nota_mti;
            $pdf::write1DBarcode("$nota_mti", 'C128', 0, 0, '', 18, 0.9, $style, 'N');
            //$pdf::write2DBarcode("$notanya", 'PDF417', 0, 0, 0, 0, $style, 'N');


            //$pdf::Image('images/ipc2.jpg', 50, 7, 20, 10, '', '', '', true, 72);
            $pdf::writeHTML($tbl, true, false, false, false, '');

            $limit1 = ($jum_data_page * ($pg - 1)) + 1;   //limit bawah
            $limit2 = $jum_data_page * $pg;             //limit atas


            if ($pg < $jum_page) {   //buat garis silang bagian bawah nota
                $style = array('width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => '10,10', 'color' => array(0, 0, 0));
                $pdf::Line(10, 200, 205, 280, $style);
                $pdf::Line(10, 280, 205, 200, $style);
            }
        }

        while ($i < 10) {  // apabila jumlah barang kurang dari 12 pada page terakhir, ditambahkan space
            $space .= "<tr><td></td><tr>";
            $i++;
        }


        $pdf::SetFont('courier', '', 6);
        $pdf::Write(0, $data->tgl_nota ?? NULL, '', 0, 'R', true, 0, false, false, 0);

        $pdf::ln();
        $pdf::ln();
        $pdf::ln();
        $pdf::ln();
        $pdf::ln();
        $pdf::ln();
        $pdf::ln();
        $pdf::ln();
        $pdf::ln();
        $pdf::SetFont('helvetica', 'B', 6);
        //Close and output PDF document
        $pdf::Output('sample.pdf', 'I');
    }

    public function print_nota(Request $request)
    {
        $corporate_name     = 'PT. Multi Terminal Indonesia';
        $no_req        = $request->no_req;
        $koreksi        = $request->koreksi;
        //--------------------------
        $query_nota    = "SELECT c.NM_PBM AS EMKL,
            c.NO_NPWP_PBM AS NPWP,
            c.ALMT_PBM AS ALAMAT,
            b.NO_REQ_BARU,
            c.NO_ACCOUNT_PBM,
            b.STATUS_GATE,
            TO_CHAR(b.TGL_REQUEST,'DD-MM-RRRR') TGL_REQUEST,
            F_CORPORATE(b.TGL_REQUEST) CORPORATE
        FROM REQUEST_BATAL_MUAT b INNER JOIN
            v_mst_pbm c ON b.KD_EMKL = c.KD_PBM AND c.KD_CABANG = '05'
        WHERE b.NO_REQUEST = '$no_req'";

        //echo $query_nota;die;
        $row_nota        = DB::connection('uster')->selectOne($query_nota);
        $req_tgl     = $row_nota->tgl_request;
        $kd_pbm     = $row_nota->no_account_pbm;
        $display    = 1;
        //cek subsidiary



        $st_gate = $row_nota->status_gate;
        //$no_request = $row_nota["NO_REQUEST"];

        $query_tgl    = "SELECT TO_CHAR(TGL_REQUEST,'dd/mon/yyyy') TGL_REQUEST FROM request_batal_muat
       WHERE NO_REQUEST = '$no_req'
      ";
        //echo $query_tgl;die;
        $tgl_req        = DB::connection('uster')->selectOne($query_tgl);
        $tgl_re         = $tgl_req->tgl_request;
        // echo $tgl_re;die;

        $parameter = array(
            'id_nota' => 2,
            'tgl_req' => $tgl_re,
            'no_request:20' => $no_req,
            'err_msg:100' => 'NULL'
        );


            $sql_xpi = "DECLARE
          id_nota NUMBER;
          tgl_req DATE;
          no_request VARCHAR2(100);
          jenis VARCHAR2 (100);
          err_msg VARCHAR2(100);
          BEGIN
               id_nota := 9;
               tgl_req := '$tgl_re';
               no_request := '$no_req';
                err_msg := 'NULL';
                jenis := 'batal_muat';
              create_detail_nota(id_nota,tgl_req,no_request,jenis, err_msg);
          END;";

            DB::connection('uster')->statement($sql_xpi);


        $detail_nota  = "SELECT a.JML_HARI, TO_CHAR(a.TARIF, '999,999,999,999') AS TARIF, TO_CHAR(a.BIAYA, '999,999,999,999') AS BIAYA, a.KETERANGAN, a.HZ,
        a.JML_CONT, TO_DATE(a.START_STACK,'dd/mm/yyyy') START_STACK, TO_DATE(a.END_STACK,'dd/mm/yyyy') END_STACK, b.SIZE_, b.TYPE_, b.STATUS FROM temp_detail_nota a
        , iso_code b WHERE a.id_iso = b.id_iso and a.no_request = '$no_req' AND A.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI')";

        $row_detail       = DB::connection('uster')->select($detail_nota);



        $jum          = "SELECT COUNT(NO_CONTAINER) JUMLAH FROM container_batal_muat WHERE no_request = '$no_req'";

        $jum_      = DB::connection('uster')->selectOne($jum);

        $jumlah_cont  = $jum_->jumlah;


        $pass          = "SELECT TO_CHAR(($jumlah_cont * a.TARIF), '999,999,999,999') PASS, ($jumlah_cont * a.TARIF) TARIF
        FROM master_tarif a, group_tarif b
        WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF
     AND TO_DATE ('$tgl_re', 'dd/mm/yyyy') BETWEEN b.START_PERIOD
                                                  AND b.END_PERIOD
     AND a.ID_ISO = 'BAMU'";

        $row_pass     = DB::connection('uster')->selectOne($pass);
        $tarif_pass   = $row_pass->tarif ?? NULL;


        $total_          = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN, (SUM(BIAYA)+SUM(PPN)) TOTAL_TAGIHAN FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN NOT IN ('MATERAI')"; //Fauzan add NOT IN MATERAI 27 Agustus 2020

        $total2          = DB::connection('uster')->selectOne($total_);


        $total_1 = $total2->total;
        $ppn_1 = $total2->ppn;
        $total_tagihan_1 = $total2->total_tagihan;


        $discount = 0;
        $query_discount        = "SELECT TO_CHAR($discount , '999,999,999,999') AS DISCOUNT FROM DUAL";
        $row_discount    = DB::connection('uster')->selectOne($query_discount);


        $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
        $row_adm        = DB::connection('uster')->selectOne($query_adm);
        $adm             = $row_adm->tarif;;

        $query_tot        = "SELECT TO_CHAR('$total_1' , '999,999,999,999') AS TOTAL_ALL FROM DUAL";
        $row_tot        = DB::connection('uster')->selectOne($query_tot);


        //Menghitung Jumlah PPN
        //$ppn = $total_/10;
        $query_ppn        = "SELECT TO_CHAR('$ppn_1' , '999,999,999,999') AS PPN FROM DUAL";
        $row_ppn        = DB::connection('uster')->selectOne($query_ppn);


        //Menghitung Bea Materai
        //Fauzan add materai 24 Agustus 2020
        $materai_          = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";

        $materai          = DB::connection('uster')->selectOne($materai_);


        if ($materai->bea_materai > 0) {
            $bea_materai = $materai->bea_materai;
        } else {
            $bea_materai = 0;
        }
        $query_materai        = "SELECT TO_CHAR('$bea_materai' , '999,999,999,999') AS BEA_MATERAI FROM DUAL";
        $row_materai        = DB::connection('uster')->selectOne($query_materai);

        /*end Fauzan add materai 24 Agustus 2020*/

        //Menghitung Jumlah dibayar
        $total_bayar        = $total_tagihan_1 + $bea_materai;    /*Fauzan modif 24 Agustus 2020*/
        $query_bayar        = "SELECT TO_CHAR('$total_bayar' , '999,999,999,999') AS TOTAL_BAYAR FROM DUAL";
        $row_bayar       = DB::connection('uster')->selectOne($query_bayar);

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);



        $tgl_nota = $tgl_re;
        $no_req_baru = $row_nota->no_req_baru;

        return view('billing.nota-batal-muat.print-nota', compact(
            'corporate_name',
            'row_discount',
            'nama_peg',
            'st_gate',
            'tgl_nota',
            'row_adm',
            'row_pass',
            'row_tot',
            'row_ppn',
            'row_materai',
            'bea_materai',
            'row_bayar',
            'row_nota',
            'no_req',
            'koreksi',
            'row_detail',
            'no_req_baru'
        ));
    }

    public function insert_proforma(Request $request)
    {

        $nipp   = session('LOGGED_STORAGE');
        $no_req = $request->no_req;
        $koreksi = $request->koreksi;


        $query_cek_nota     = "SELECT NO_NOTA, STATUS FROM NOTA_BATAL_MUAT WHERE NO_REQUEST = '$no_req'";
        $nota    = DB::connection('uster')->selectOne($query_cek_nota);
        $no_nota_cek        = $nota->no_nota;
        $no_status            = $nota->status;

        if (($no_nota_cek != NULL && $no_status == 'BATAL') || ($no_nota_cek == NULL && $no_status == NULL)) {

            $query_cek    = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA,10,15))+1),6,0), '000001') JUM_,
                              TO_CHAR(SYSDATE, 'MM') AS MONTH,
                              TO_CHAR(SYSDATE, 'YY') AS YEAR
                        FROM NOTA_BATAL_MUAT
                       WHERE NOTA_BATAL_MUAT.TGL_NOTA BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE)";


            $jum_    = DB::connection('uster')->selectOne($query_cek);
            $jum        = $jum_->jum;
            $month        = $jum_->month;
            $year        = $jum_->year;

            $no_nota    = "0905" . $month . $year . $jum;

            // Cek NO NOTA MTI firman 20 agustus 2020
            $query_mti = "SELECT NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA_MTI,10,15))+1),6,0),'000001') JUM_,
                           TO_CHAR(SYSDATE, 'YYYY') AS YEAR
                           FROM MTI_COUNTER_NOTA WHERE TAHUN =  TO_CHAR(SYSDATE,'YYYY')";

            $jum_mti = DB::connection('uster')->selectOne($query_mti);
            $jum_nota_mti    = $jum_mti->jum_;
            $year_mti        = $jum_mti->year;

            $no_nota_mti    = "17." . $year_mti . "." . $jum_nota_mti;


            //select master pbm
            $query_master    = "SELECT b.KD_PBM, b.nm_pbm, b.almt_pbm, b.no_npwp_pbm,  TO_CHAR(a.TGL_REQUEST,'dd/Mon/yyyy') TGL_REQUEST, COUNT(c.NO_CONTAINER) JUMLAH,
				a.NO_REQ_BARU
				FROM request_batal_muat a, v_mst_pbm b , container_batal_muat c
				WHERE a.KD_EMKL = b.kd_pbm
				AND a.NO_REQUEST = c.NO_REQUEST
				AND a.no_request = '$no_req'
				GROUP BY  b.KD_PBM, b.nm_pbm, b.almt_pbm, b.no_npwp_pbm, TO_CHAR(a.TGL_REQUEST,'dd/Mon/yyyy'), a.NO_REQ_BARU";
            //echo $query_master;die;
            $master    = DB::connection('uster')->selectOne($query_master);
            $kd_pbm        = $master->kd_pbm;
            $nm_pbm        = $master->nm_pbm;
            $almt_pbm    = $master->almt_pbm;
            $npwp       = $master->no_npwp_pbm;
            $jumlah_cont     = $master->jumlah;
            $tgl_re       = $master->tgl_request;
            $no_req_baru       = $master->no_req_baru;




            $total_        = "SELECT SUM(BIAYA) TOTAL, SUM(PPN) PPN  FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN NOT IN ('MATERAI')";
            /**Fauzan modif 10 Sep 2020 "AND KETERANGAN NOT IN ('MATERAI')"*/
            //echo $total_;die;
            $total2        = DB::connection('uster')->selectOne($total_);

            $query_adm        = "SELECT TO_CHAR(a.TARIF , '999,999,999,999') AS ADM, a.TARIF FROM MASTER_TARIF a, GROUP_TARIF b WHERE a.ID_GROUP_TARIF = b.ID_GROUP_TARIF AND b.KATEGORI_TARIF = 'ADMIN_NOTA'";
            $row_adm        = DB::connection('uster')->selectOne($query_adm);
            $adm             = $row_adm->tarif;

            /*Fauzan add materai 10 September 2020*/
            $query_materai        = "SELECT SUM(BIAYA) BEA_MATERAI FROM temp_detail_nota WHERE no_request = '$no_req' AND KETERANGAN = 'MATERAI'";
            $row_materai        = DB::connection('uster')->selectOne($query_materai);
            $materai            = $row_materai->bea_materai;
            /*end Fauzan add materai 10 September 2020*/

            $total_1 = $total2->total;
            $ppn_1 = $total2->ppn;

            $tagihan = $total_1 + $ppn_1 + +$materai; // + $tarif_pass;		/**Fauzan modif 10 SEP 2020 "+ $materai"*/

            if ($koreksi <> 'Y') {

                $status_nota = 'NEW';
                $nota_lama = '';
            } else {
                $status_nota = 'KOREKSI';
                $faktur         = "SELECT NO_NOTA, NO_FAKTUR, KD_EMKL FROM NOTA_RECEIVING WHERE NO_REQUEST = '$no_req' AND NO_NOTA =(SELECT MAX(NO_NOTA) FROM NOTA_RECEIVING WHERE NO_REQUEST = '$no_req')";
                $faktur_        = DB::connection('uster')->selectOne($faktur);
                $nota_lama    = $faktur_->no_faktur;
            }



            $query_mti = "INSERT INTO MTI_COUNTER_NOTA
							(
							 NO_NOTA_MTI,
							 TAHUN,
							 NO_REQUEST
							)
							VALUES
							(
							'$no_nota_mti',
							TO_CHAR(SYSDATE,'YYYY'),
							'$no_req'
							)";

            DB::connection('uster')->insert($query_mti);

            $tarif_pass = null;
            $query_insert_nota    = "INSERT INTO NOTA_BATAL_MUAT(NO_NOTA,
                                        TAGIHAN,
                                        PPN,
                                        TOTAL_TAGIHAN,
										NO_REQUEST,
										NIPP_USER,
										LUNAS,
										CETAK_NOTA,
										TGL_NOTA,
                                        EMKL,
                                        ALAMAT,
                                        NPWP,
                                        STATUS,
                                        ADM_NOTA,
										PASS,
										KD_EMKL,
                                        NOTA_LAMA,
										NO_NOTA_MTI)
										VALUES('$no_nota',
                                        '$total_1',
                                        '$ppn_1',
                                        '$tagihan',
										'$no_req',
										'$nipp',
										'NO',
										1,
										SYSDATE,
                                        '$nm_pbm',
                                        '$almt_pbm',
                                        '$npwp',
                                        'NEW',
                                        '$adm',
										'$tarif_pass',
										'$kd_pbm',
                                        '$nota_lama',
										'$no_nota_mti')";

            //$db->startTransaction();
            if (DB::connection('uster')->insert($query_insert_nota)) {
                $query_detail    = "SELECT * FROM temp_detail_nota WHERE no_request = '$no_req' ";

                $row        = DB::connection('uster')->select($query_detail);
                //echo $query_detail;
                $i = 1;
                foreach ($row as $item) {
                    $id_iso = $item->id_iso;
                    $tarif  = $item->tarif;
                    $biaya  = $item->biaya;
                    $ket    = $item->keterangan;
                    $jml_cont  = $item->jml_cont;
                    $hz     = $item->hz;
                    $start  = $item->start_stack;
                    $end    = $item->end_stack;
                    $jml    = $item->jml_hari;
                    $coa    = $item->coa;
                    $ppn_d    = $item->ppn;


                    $query_insert    = "INSERT INTO nota_batal_muat_d
                                            (
                                             ID_ISO,
                                             TARIF,
                                             BIAYA,
                                             KETERANGAN,
                                             ID_NOTA,
                                             JML_CONT,
                                             HZ,
                                             START_STACK,
                                             END_STACK,
                                             JML_HARI,
											 COA,
											 LINE_NUMBER,
											 PPN,
											 NO_NOTA_MTI
                                            ) VALUES
                                            (
                                            '$id_iso',
                                            '$tarif',
                                            '$biaya',
                                            '$ket',
                                            '$no_nota',
                                            '$jml_cont',
                                            '$hz',
                                            '$start',
                                            '$end',
                                            '$jml',
											'$coa',
											'$i',
											'$ppn_d',
											'$no_nota_mti')";
                    //echo $query_insert; die;
                    DB::connection('uster')->insert($query_insert);
                    // $db4->query($query_insert);

                    $i++;
                }

                $update_nota = "UPDATE NOTA_BATAL_MUAT SET CETAK_NOTA = 'Y' WHERE NO_NOTA = '$no_nota'";
                $update_req = "UPDATE request_batal_muat SET NOTA = 'Y' WHERE no_request = '$no_req'";
                $update_req_ = "UPDATE request_delivery SET NOTA = 'Y' WHERE no_request = '$no_req_baru'";
                DB::connection('uster')->update($update_nota);
                // $db4->query($update_nota);
                DB::connection('uster')->update($update_req);
                DB::connection('uster')->update($update_req_);
                $delete_temp = "DELETE from temp_detail_nota WHERE no_request = '$no_req'";
                DB::connection('uster')->delete($delete_temp);

                header('Location:print/print_proforma?no_nota=' . $no_nota . "&no_req=" . $no_req . "&first=1");
                //$db->endTransaction();
            }
        } else {
            return redirect()->route('uster.billing.nota_batalmuat.print_proforma', ['no_req' => $no_req]);

        }
    }
}
