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
use Illuminate\Support\Facades\Redirect;

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

        $pdf::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf::SetMargins(1, 3, 0);
        $pdf::SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf::setPrintHeader(false);
        $pdf::SetAutoPageBreak(TRUE, 10);
        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        // ---------------------------------------------------------
        $no_req = $request->no_req;
        $id_user = session('PENGGUNA_ID');

        // 🔹 Query: ambil nomor nota
        $notanya = DB::connection('uster_dev')
            ->table(DB::raw('nota_batal_muat@DBCLOUD_LINK'))
            ->whereRaw('TRIM(NO_REQUEST) = TRIM(?)', [$no_req])
            ->where('STATUS', '<>', 'BATAL')
            ->value('NO_NOTA');

        // 🔹 Query utama
        $data = DB::connection('uster_dev')
            ->table(DB::raw('nota_batal_muat@DBCLOUD_LINK a'))
            ->join(DB::raw('request_batal_muat@DBCLOUD_LINK c'), 'a.NO_REQUEST', '=', 'c.NO_REQUEST')
            ->leftJoin(DB::raw('BILLING_NBS.TB_USER@DBCLOUD_LINK mu'), 'a.nipp_user', '=', 'mu.id')
            ->selectRaw("
                c.NO_REQUEST,
                a.NOTA_LAMA,
                a.NO_NOTA,
                a.NO_NOTA_MTI,
                TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA,
                TO_CHAR(a.PASS,'999,999,999,999') PASS,
                a.EMKL NAMA,
                a.ALAMAT,
                a.NPWP,
                a.LUNAS,
                a.NO_FAKTUR,
                TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN,
                TO_CHAR(a.PPN,'999,999,999,999') PPN,
                TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN,
                a.STATUS,
                TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
                CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG,
                a.NIPP_USER,
                mu.NAME,
                CASE
                    WHEN TRUNC(a.TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR') THEN a.NO_NOTA
                    ELSE a.NO_FAKTUR
                END NO_FAKTUR_
                --F_CORPORATE(c.TGL_REQUEST) CORPORATE
            ")
            ->where('c.NO_REQUEST', $no_req)
            ->whereRaw('a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_batal_muat@DBCLOUD_LINK d WHERE d.NO_REQUEST = ?)', [$no_req])
            ->first();

        $req_tgl = $data->tgl_request ?? null;
        $nama_lengkap = $data->name ?? '';
        $lunas = $data->lunas ?? '';

        if (!$request->first) {
            $nama_lengkap .= '<br/>' . 'Reprinted by ' . session('NAMA_LENGKAP');
        }

        date_default_timezone_set('Asia/Jakarta');
        $date = date('d M Y H:i:s');

        // 🔹 Materai
        $data_mtr = DB::connection('uster_dev')
            ->table(DB::raw('NOTA_BATAL_MUAT_D@DBCLOUD_LINK'))
            ->selectRaw("TO_CHAR(BIAYA, '999,999,999,999') AS bea_materai, BIAYA")
            ->where('ID_NOTA', $notanya)
            ->where('KETERANGAN', 'MATERAI')
            ->first();

        $bea_materai = ($data_mtr && $data_mtr->biaya > 0) ? $data_mtr->bea_materai : 0;

        // 🔹 No peraturan
        if ($lunas == 'YES') {
            $no_mat = DB::connection('uster_dev')
                ->table(DB::raw('itpk_nota_header@DBCLOUD_LINK'))
                ->where('NO_REQUEST', $no_req)
                ->value('no_peraturan');
        } else {
            $no_mat = DB::connection('uster_dev')
                ->table(DB::raw('MASTER_MATERAI@DBCLOUD_LINK'))
                ->where('STATUS', 'Y')
                ->value('no_peraturan');
        }

        // 🔹 Detail nota
        $row2 = DB::connection('uster_dev')
            ->table(DB::raw('NOTA_BATAL_MUAT_D@DBCLOUD_LINK a'))
            ->join(DB::raw('iso_code@DBCLOUD_LINK b'), 'a.id_iso', '=', 'b.id_iso')
            ->selectRaw("
            a.JML_HARI,
            TO_CHAR(a.TARIF, '999,999,999,999') tarif,
            TO_CHAR(a.BIAYA, '999,999,999,999') biaya,
            a.KETERANGAN,
            a.HZ,
            a.JML_CONT,
            TO_CHAR(a.START_STACK,'dd/mm/yyyy') tgl_start_stack,
            TO_CHAR(a.END_STACK,'dd/mm/yyyy') tgl_end_stack,
            b.SIZE_,
            b.TYPE_,
            b.STATUS
        ")
            ->where('a.ID_NOTA', $notanya)
            ->whereNotIn('a.KETERANGAN', ['ADMIN NOTA', 'MATERAI'])
            ->get();

        // 🔹 Jumlah detail
        $jum_data_page = 18;
        $jum_detail = DB::connection('uster_dev')
            ->table(DB::raw('NOTA_RECEIVING_D@DBCLOUD_LINK'))
            ->where('NO_NOTA', $notanya)
            ->count();
        $jum_page = max(1, ceil($jum_detail / $jum_data_page));
        if (($jum_detail % $jum_data_page) > 10 || ($jum_detail % $jum_data_page) == 0) {
            $jum_page++;
        }
        $jum_page = 1;

        $i = 0;
        $detail = '';
        foreach ($row2 as $rows) {
            $den = ($rows->keterangan != 'MONITORING DAN LISTRIK')
                ? '(' . ($rows->tgl_start_stack ?? '') . ' s/d ' . ($rows->tgl_end_stack ?? '') . ')' . ($rows->jml_hari ?? '') . 'hari'
                : ($rows->jml_hari ?? '') . ' Shift';

            $detail .= '<tr><td colspan="3" width="100"><font size="6"><b>' . $rows->keterangan . '</b></font></td>
            <td width="10" align="left"><font size="6"><b>' . $rows->jml_cont . '</b></font></td>
            <td width="50" align="left"><font size="6"><b>' . $rows->size_ . " " . $rows->type_ . " " . $rows->status . '</b></font></td>
            <td width="10" align="left"><font size="6"><b>' . $rows->hz . '</b></font></td>
            <td width="43" align="right"><font size="6"><b>' . $rows->tarif . '</b></font></td>
            <td width="35" align="right"><font size="6"><b>' . $rows->biaya . '</b></font></td></tr>';
            if ($rows->keterangan != 'MONITORING DAN LISTRIK') {
                $detail .= '<tr><td colspan="8"><font size="6"><i><b>' . $den . '</b></i></font></td></tr>';
            }
            $i++;
        }

        for ($pg = 1; $pg <= $jum_page; $pg++) {
            $pdf::AddPage();
            $pdf::SetFont('helvetica', '', 6);

            $data->pelabuhan_tujuan = $data->pelabuhan_tujuan ?? '';
            $data->ipod = $data->ipod ?? '';
            $data->voyage = $data->voyage ?? '';
            $data->vessel = $data->vessel ?? '';
            $data->tgl_muat = $data->tgl_muat ?? '';
            $data->tgl_stack = $data->tgl_stack ?? '';

            $tbl = <<<EOD
            <table border='0'>
            <tr>
                <td width="150" COLSPAN="1" align="left"><font size="8"><b>$data->no_nota_mti</b></font></td>
                <td width="100"><font size="8"><b>$date</b></font></td>
            </tr>
            <tr>
                <td width="150" COLSPAN="1" align="left"><font size="8"><b>$data->no_request</b></font></td>
                <td width="120"><font size="7">No Nota : <b>$data->no_nota</b></font></td>
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
            <tr><td></td></tr>
            <tr>
                <td width="80" align="left"><font size="6"><b>PENUMPUKAN DARI :</b></font></td>
                <td colspan="5"><font size="6"><b>$data->tgl_stack s/d $data->tgl_muat</b></font></td>
            </tr>
            <tr>
                <th colspan="3" width="100"><font size="6"><b>KETERANGAN</b></font></th>
                <th width="10" align="left"><font size="6"><b>BX</b></font></th>
                <th width="50" align="left"><font size="6"><b>CONTENT</b></font></th>
                <th width="10" align="left"><font size="6"><b>HZ</b></font></th>
                <th width="43" align="left"><font size="6"><b>TARIF</b></font></th>
                <th width="43" align="left"><font size="6"><b>JUMLAH</b></font></th>
            </tr>
            <tr>
                <td colspan="14">
                <hr style="border: 2px dashed #C0C0C0" color="#FFFFFF" size="6" width="700">
                </td>
            </tr>
            $detail
            <tr>
                <td colspan="14">
                <hr style="border: 2px dashed #C0C0C0" color="#FFFFFF" size="6" width="700">
                </td>
            </tr>
            </table>
            EOD;

            $tbl_materai = '';
            $kotak = '';
            $row = '';
            $space = '';

            if (($data_mtr->biaya ?? 0) > 0) {
                $tbl_materai .= '<tr>
                <td colspan="6" align="right"><b>Bea Materai :</b></td>
                <td colspan="2" align="right"><b>' . $bea_materai . '</b></td>
            </tr>';
                $row .= '<tr><td colspan="6"></td>
                <td width="225" colspan="2" align="right"></td>
            </tr>';
                $kotak .= '<tr>
                <td colspan="4" align="left"><font size="5px">Bea Materai Lunas Dengan Sistem Nomor Ijin :' . $no_mat . '</font></td><td></td>
                <td width="80" colspan="2" align="center" border="1">Termasuk Bea Materai<br><font size="7">Rp.' . $bea_materai . '</font></td><td></td>
            </tr>';
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
                <td colspan="8"><i>form untuk Bank</i></td>
            </tr>
            <tr><td colspan="8">&nbsp;</td></tr>
            <tr><td colspan="8">&nbsp;</td></tr>
            <tr>
                <td colspan="3" align="right"><font size="8"><b>Nomor Invoice :</b></font></td>
                <td colspan="4" align="left"><font size="8"><b>$data->no_nota_mti</b></font></td>
            </tr>
            <tr>
                <td colspan="3" align="right"><font size="8"><b>Customer :</b></font></td>
                <td colspan="4" align="left"><font size="8"><b>$data->nama</b></font></td>
            </tr>
            <tr>
                <td colspan="3" align="right"><font size="8"><b>Jumlah Dibayar :</b></font></td>
                <td colspan="4" align="left"><font size="8">Rp. <b>$data->total_tagihan</b></font></td>
            </tr>
            </table>
            <br><br><br><br>
            EOD;

            $style = [
                'position' => '',
                'align' => 'C',
                'stretch' => false,
                'fitwidth' => true,
                'cellfitalign' => '',
                'hpadding' => 'auto',
                'vpadding' => 'auto',
                'fgcolor' => [0, 0, 0],
                'bgcolor' => false,
                'text' => true,
                'font' => 'helvetica',
                'fontsize' => 4,
                'stretchtext' => 4
            ];

            $nota_mti = $data->no_nota_mti;
            $pdf::write1DBarcode("$nota_mti", 'C128', 0, 0, '', 18, 0.9, $style, 'N');
            $pdf::writeHTML($tbl, true, false, false, false, '');

            // Paging logic (if needed)
            $limit1 = ($jum_data_page * ($pg - 1)) + 1;
            $limit2 = $jum_data_page * $pg;

            if ($pg < $jum_page) {
                $styleLine = ['width' => 0.5, 'cap' => 'butt', 'join' => 'miter', 'dash' => '10,10', 'color' => [0, 0, 0]];
                $pdf::Line(10, 200, 205, 280, $styleLine);
                $pdf::Line(10, 280, 205, 200, $styleLine);
            }
        }

        // Tambahkan space jika jumlah barang kurang dari 10 pada page terakhir
        while ($i < 10) {
            $space .= "<tr><td></td></tr>";
            $i++;
        }

        $pdf::SetFont('courier', '', 6);
        $pdf::Write(0, $data->tgl_nota ?? null, '', 0, 'R', true, 0, false, false, 0);

        for ($j = 0; $j < 9; $j++) {
            $pdf::ln();
        }
        $pdf::SetFont('helvetica', 'B', 6);
        $pdf::Output('sample.pdf', 'I');
    }

    public function print_nota(Request $request)
    {
        $corporate_name = 'PT. Multi Terminal Indonesia';
        $no_req = $request->no_req;
        $koreksi = $request->koreksi;

        // --- Ambil data utama dari REQUEST_BATAL_MUAT@DBCLOUD_LINK ---
        $row_nota = DB::connection('uster_dev')
            ->table(DB::raw('REQUEST_BATAL_MUAT@DBCLOUD_LINK b'))
            ->join(DB::raw('v_mst_pbm@DBCLOUD_LINK c'), function ($join) {
                $join->on('b.KD_EMKL', '=', 'c.KD_PBM')
                    ->where('c.KD_CABANG', '=', '05');
            })
            ->select([
                'c.NM_PBM as emkl',
                'c.NO_NPWP_PBM as npwp',
                'c.ALMT_PBM as alamat',
                'b.NO_REQ_BARU as no_req_baru',
                'c.NO_ACCOUNT_PBM as no_account_pbm',
                'b.STATUS_GATE as status_gate',
                DB::raw("TO_CHAR(b.TGL_REQUEST,'DD-MM-RRRR') as tgl_request"),
                // DB::raw("F_CORPORATE(b.TGL_REQUEST) as corporate"),
            ])
            ->where('b.NO_REQUEST', $no_req)
            ->first();

        $req_tgl  = $row_nota->tgl_request ?? null;
        $kd_pbm   = $row_nota->no_account_pbm ?? null;
        $st_gate  = $row_nota->status_gate ?? null;

        // --- Ambil tanggal request ---
        $tgl_req = DB::connection('uster_dev')
            ->table(DB::raw('request_batal_muat@DBCLOUD_LINK'))
            ->where('NO_REQUEST', $no_req)
            ->value(DB::raw("TO_CHAR(TGL_REQUEST,'YYYY-MM-DD')"));
        $tgl_re = \Carbon\Carbon::parse($tgl_req)->format('Y/m/d');

        // --- Panggil prosedur PL/SQL create_detail_nota ---
        $pdo = DB::connection('uster')->getPdo();
        $stmt = $pdo->prepare("
            BEGIN
                create_detail_nota(
                    :id_nota,
                    TO_DATE(:tgl_req, 'yyyy/mm/dd'),
                    :no_request,
                    :jenis,
                    :err_msg
                );
            END;
        ");

        $errMsg = str_repeat(' ', 4000);
        $stmt->bindValue(':id_nota', 9);
        $stmt->bindValue(':tgl_req', $tgl_re);
        $stmt->bindValue(':no_request', $no_req);
        $stmt->bindValue(':jenis', 'batal_muat');
        $stmt->bindParam(':err_msg', $errMsg, \PDO::PARAM_INPUT_OUTPUT, 4000);
        $stmt->execute();

        // --- Ambil detail nota ---
        $row_detail = DB::connection('uster_dev')
            ->table(DB::raw('temp_detail_nota@DBCLOUD_LINK a'))
            ->join(DB::raw('iso_code@DBCLOUD_LINK b'), 'a.id_iso', '=', 'b.id_iso')
            ->selectRaw("
            a.JML_HARI,
            TO_CHAR(a.TARIF, '999,999,999,999') AS TARIF,
            TO_CHAR(a.BIAYA, '999,999,999,999') AS BIAYA,
            a.KETERANGAN,
            a.HZ,
            a.JML_CONT,
            TO_DATE(a.START_STACK,'dd/mm/yyyy') AS START_STACK,
            TO_DATE(a.END_STACK,'dd/mm/yyyy') AS END_STACK,
            b.SIZE_,
            b.TYPE_,
            b.STATUS
        ")
            ->where('a.no_request', $no_req)
            ->whereNotIn('a.KETERANGAN', ['ADMIN NOTA', 'MATERAI'])
            ->get();

        // --- Hitung jumlah kontainer ---
        $jum_ = DB::connection('uster_dev')
            ->table(DB::raw('container_batal_muat@DBCLOUD_LINK'))
            ->where('no_request', $no_req)
            ->selectRaw('COUNT(*) as jumlah')
            ->first();
        $jumlah_cont = $jum_->jumlah ?? 0;

        // --- Ambil tarif pass ---
        $row_pass = DB::connection('uster_dev')
            ->table(DB::raw('master_tarif@DBCLOUD_LINK a'))
            ->join(DB::raw('group_tarif@DBCLOUD_LINK b'), 'a.ID_GROUP_TARIF', '=', 'b.ID_GROUP_TARIF')
            ->selectRaw('TO_CHAR((? * a.TARIF), \'999,999,999,999\') AS PASS, (? * a.TARIF) AS TARIF', [$jumlah_cont, $jumlah_cont])
            ->whereRaw("TO_DATE(?, 'yyyy/mm/dd') BETWEEN b.START_PERIOD AND b.END_PERIOD", [$tgl_re])
            ->where('a.ID_ISO', 'BAMU')
            ->first();
        $tarif_pass = $row_pass->tarif ?? null;

        // --- Hitung total, PPN, dan total tagihan ---
        $total2 = DB::connection('uster_dev')
            ->table(DB::raw('temp_detail_nota@DBCLOUD_LINK'))
            ->selectRaw('SUM(BIAYA) AS total, SUM(PPN) AS ppn, (SUM(BIAYA) + SUM(PPN)) AS total_tagihan')
            ->where('no_request', $no_req)
            ->whereNotIn('KETERANGAN', ['MATERAI'])
            ->first();

        $total_1 = $total2->total ?? 0;
        $ppn_1 = $total2->ppn ?? 0;
        $total_tagihan_1 = $total2->total_tagihan ?? 0;

        // --- Discount ---
        $discount = 0;
        $row_discount = (object)[
            'discount' => number_format($discount, 0, ',', ',')
        ];

        // --- Admin Fee ---
        $row_adm = DB::connection('uster_dev')
            ->table(DB::raw('master_tarif@DBCLOUD_LINK a'))
            ->join(DB::raw('group_tarif@DBCLOUD_LINK b'), 'a.ID_GROUP_TARIF', '=', 'b.ID_GROUP_TARIF')
            ->where('b.KATEGORI_TARIF', 'ADMIN_NOTA')
            ->selectRaw("TO_CHAR(a.TARIF, '999,999,999,999') AS adm, a.TARIF")
            ->first();
        $adm = $row_adm->tarif ?? 0;

        // --- Total ---
        $row_tot = (object)[
            'total_all' => number_format($total_1, 0, ',', ',')
        ];

        // --- PPN ---
        $row_ppn = (object)[
            'ppn' => number_format($ppn_1, 0, ',', ',')
        ];

        // --- Bea Materai ---
        $materai = DB::connection('uster_dev')
            ->table(DB::raw('temp_detail_nota@DBCLOUD_LINK'))
            ->where('no_request', $no_req)
            ->where('KETERANGAN', 'MATERAI')
            ->sum('BIAYA');
        $bea_materai = $materai > 0 ? $materai : 0;
        $row_materai = (object)[
            'bea_materai' => number_format($bea_materai, 0, ',', ',')
        ];

        // --- Jumlah dibayar ---
        $total_bayar = $total_tagihan_1 + $bea_materai;
        $row_bayar = (object)[
            'total_bayar' => number_format($total_bayar, 0, ',', ',')
        ];

        // --- Pegawai aktif ---
        $nama_peg = DB::connection('uster_dev')
            ->table(DB::raw('MASTER_PEGAWAI@DBCLOUD_LINK'))
            ->where('STATUS', 'AKTIF')
            ->first();

        $tgl_nota = $tgl_re;
        $no_req_baru = $row_nota->no_req_baru ?? null;

        return view('billing.nota-batal-muat.print-nota', compact(
            'corporate_name',
            'row_discount',
            'nama_peg',
            'st_gate',
            'tgl_nota',
            'adm',
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

    public function insert_proforma($no_req, Request $request)
    {
        $nipp   = session('LOGGED_STORAGE');
        $koreksi = $request->koreksi;
        $no_req = base64_decode($no_req);

        $nota = DB::connection('uster')
            ->table('NOTA_BATAL_MUAT')
            ->select('NO_NOTA as no_nota', 'STATUS as status')
            ->where('NO_REQUEST', $no_req)
            ->first();

        $no_nota_cek = $nota->no_nota ?? null;
        $no_status = $nota->status ?? null;

        DB::beginTransaction();
        try {
            if (($no_nota_cek != NULL && $no_status == 'BATAL') || ($no_nota_cek == NULL && $no_status == NULL)) {
                // Optimized: Use Query Builder for better readability and security
                $notaBatalMuat = DB::connection('uster')
                    ->table('NOTA_BATAL_MUAT')
                    ->selectRaw("NVL(LPAD(MAX(TO_NUMBER(SUBSTR(NO_NOTA,10,15))+1),6,0), '000001') AS jum,
                     TO_CHAR(SYSDATE, 'MM') AS month,
                     TO_CHAR(SYSDATE, 'YY') AS year")
                    ->whereBetween('TGL_NOTA', [
                        DB::raw("TRUNC(SYSDATE,'MONTH')"),
                        DB::raw("LAST_DAY(SYSDATE)")
                    ])
                    ->first();

                $jum   = $notaBatalMuat->jum;
                $month = $notaBatalMuat->month;
                $year  = $notaBatalMuat->year;

                $no_nota = "0905" . $month . $year . $jum;

                // Cek NO NOTA MTI firman 20 agustus 2020
                // Optimized: Use Query Builder for better readability and maintainability
                $jum_mti = DB::connection('uster')
                    ->table('MTI_COUNTER_NOTA')
                    ->selectRaw("COALESCE(LPAD(MAX(CAST(SUBSTR(NO_NOTA_MTI, 10, 15) AS NUMBER)) + 1, 6, '0'), '000001') AS jum_, TO_CHAR(SYSDATE, 'YYYY') AS year")
                    ->whereRaw("TAHUN = TO_CHAR(SYSDATE,'YYYY')")
                    ->first();

                $jum_nota_mti = $jum_mti->jum_;
                $year_mti = $jum_mti->year;
                $no_nota_mti = "17.$year_mti.$jum_nota_mti";


                //select master pbm
                $master = DB::connection('uster')
                    ->table('request_batal_muat as a')
                    ->join('v_mst_pbm as b', 'a.KD_EMKL', '=', 'b.kd_pbm')
                    ->join('container_batal_muat as c', 'a.NO_REQUEST', '=', 'c.NO_REQUEST')
                    ->select(
                        'b.KD_PBM as kd_pbm',
                        'b.nm_pbm',
                        'b.almt_pbm',
                        'b.no_npwp_pbm',
                        DB::raw("TO_CHAR(a.TGL_REQUEST,'dd/Mon/yyyy') as tgl_request"),
                        DB::raw('COUNT(c.NO_CONTAINER) as jumlah'),
                        'a.NO_REQ_BARU as no_req_baru'
                    )
                    ->where('a.no_request', $no_req)
                    ->groupBy(
                        'b.KD_PBM',
                        'b.nm_pbm',
                        'b.almt_pbm',
                        'b.no_npwp_pbm',
                        DB::raw("TO_CHAR(a.TGL_REQUEST,'dd/Mon/yyyy')"),
                        'a.NO_REQ_BARU'
                    )
                    ->first();

                $kd_pbm      = $master->kd_pbm ?? null;
                $nm_pbm      = $master->nm_pbm ?? null;
                $almt_pbm    = $master->almt_pbm ?? null;
                $npwp        = $master->no_npwp_pbm ?? null;
                $jumlah_cont = $master->jumlah ?? 0;
                $tgl_re      = $master->tgl_request ?? null;
                $no_req_baru = $master->no_req_baru ?? null;

                // Optimized: Use Query Builder for better readability and security
                $total2 = DB::connection('uster')
                    ->table('temp_detail_nota')
                    ->selectRaw('SUM(BIAYA) AS total, SUM(PPN) AS ppn')
                    ->where('no_request', $no_req)
                    ->whereNotIn('KETERANGAN', ['MATERAI'])
                    ->first();

                $row_adm = DB::connection('uster')
                    ->table('master_tarif as a')
                    ->join('group_tarif as b', 'a.ID_GROUP_TARIF', '=', 'b.ID_GROUP_TARIF')
                    ->where('b.KATEGORI_TARIF', 'ADMIN_NOTA')
                    ->selectRaw("TO_CHAR(a.TARIF, '999,999,999,999') AS adm, a.TARIF")
                    ->first();
                $adm = $row_adm->tarif ?? 0;

                /*Fauzan add materai 10 September 2020*/
                $materai = DB::connection('uster')
                    ->table('temp_detail_nota')
                    ->where('no_request', $no_req)
                    ->where('KETERANGAN', 'MATERAI')
                    ->sum('BIAYA');
                /*end Fauzan add materai 10 September 2020*/

                $total_1 = $total2->total ?? 0;
                $ppn_1 = $total2->ppn ?? 0;
                $tagihan = $total_1 + $ppn_1 + $materai; // + $tarif_pass;		/**Fauzan modif 10 SEP 2020 "+ $materai"*/

                if ($koreksi !== 'Y') {
                    $status_nota = 'NEW';
                    $nota_lama = '';
                } else {
                    $status_nota = 'KOREKSI';
                    // Ambil nota_lama langsung dengan query yang lebih efisien
                    $faktur_ = DB::connection('uster')
                        ->table('NOTA_RECEIVING')
                        ->select('NO_FAKTUR')
                        ->where('NO_REQUEST', $no_req)
                        ->orderByDesc('NO_NOTA')
                        ->first();
                    $nota_lama = $faktur_->no_faktur ?? '';
                }

                // Optimized: Use Query Builder for better readability and security
                DB::connection('uster')->table('MTI_COUNTER_NOTA')->insert([
                    'NO_NOTA_MTI' => $no_nota_mti,
                    'TAHUN'       => date('Y'),
                    'NO_REQUEST'  => $no_req,
                ]);

                $tarif_pass = null;
                // Insert into NOTA_BATAL_MUAT using Query Builder for better readability and security
                $inserted = DB::connection('uster')->table('NOTA_BATAL_MUAT')->insert([
                    'NO_NOTA'      => $no_nota,
                    'TAGIHAN'      => $total_1,
                    'PPN'          => $ppn_1,
                    'TOTAL_TAGIHAN' => $tagihan,
                    'NO_REQUEST'   => $no_req,
                    'NIPP_USER'    => $nipp,
                    'LUNAS'        => 'NO',
                    'CETAK_NOTA'   => 0,
                    'TGL_NOTA'     => DB::raw('SYSDATE'),
                    'EMKL'         => $nm_pbm,
                    'ALAMAT'       => $almt_pbm,
                    'NPWP'         => $npwp,
                    'STATUS'       => 'NEW',
                    'ADM_NOTA'     => $adm,
                    'PASS'         => $tarif_pass,
                    'KD_EMKL'      => $kd_pbm,
                    'NOTA_LAMA'    => $nota_lama,
                    'NO_NOTA_MTI'  => $no_nota_mti,
                ]);

                if ($inserted) {
                    // Get all temp_detail_nota rows for this request
                    $rows = DB::connection('uster')->table('temp_detail_nota')->where('no_request', $no_req)->get();
                    $detailData = [];
                    $i = 1;
                    foreach ($rows as $item) {
                        $detailData[] = [
                            'ID_ISO'      => $item->id_iso,
                            'TARIF'       => $item->tarif,
                            'BIAYA'       => $item->biaya,
                            'KETERANGAN'  => $item->keterangan,
                            'ID_NOTA'     => $no_nota,
                            'JML_CONT'    => $item->jml_cont,
                            'HZ'          => $item->hz,
                            'START_STACK' => $item->start_stack,
                            'END_STACK'   => $item->end_stack,
                            'JML_HARI'    => $item->jml_hari,
                            'COA'         => $item->coa,
                            'LINE_NUMBER' => $i,
                            'PPN'         => $item->ppn,
                            'NO_NOTA_MTI' => $no_nota_mti,
                        ];
                        $i++;
                    }
                    // Bulk insert detail rows
                    if (!empty($detailData)) {
                        DB::connection('uster')->table('nota_batal_muat_d')->insert($detailData);
                    }

                    // Update related tables using Query Builder
                    DB::connection('uster')->table('NOTA_BATAL_MUAT')->where('NO_NOTA', $no_nota)->update(['CETAK_NOTA' => 1]);
                    DB::connection('uster')->table('request_batal_muat')->where('no_request', $no_req)->update(['NOTA' => 'Y']);
                    DB::connection('uster')->table('request_delivery')->where('no_request', $no_req_baru)->update(['NOTA' => 'Y']);

                    // Delete temp_detail_nota rows for this request
                    DB::connection('uster')->table('temp_detail_nota')->where('no_request', $no_req)->delete();

                    DB::commit();
                    // Redirect to print page
                    return Redirect::route('uster.billing.nota_batalmuat.print_nota', [
                        'no_nota' => $no_nota,
                        'no_req' => $no_req
                    ]);
                    // redirect()->route('uster.billing.nota_batalmuat.print_nota', [
                    //     'no_nota' => $no_nota,
                    //     'no_req' => $no_req
                    // ]);
                    // header('Location:print/print_proforma?no_nota=' . $no_nota . "&no_req=" . $no_req . "&first=1");
                    // exit;
                }
            } else {
                DB::rollBack();
                return redirect()->route('uster.billing.nota_batalmuat.print_proforma', ['no_req' => $no_req]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            // \Log::error('Insert Proforma Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menyimpan proforma: ' . $e->getMessage());
        }
    }
}
