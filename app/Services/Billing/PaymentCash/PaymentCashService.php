<?php

namespace App\Services\Billing\PaymentCash;

use App\Services\Others\Praya;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Response;
use TCPDF;
use DateTime;

class PaymentCashService


{
    protected $praya;

    public function __construct(Praya $praya)
    {
        $this->praya = $praya;
    }


    public function getNotaSapData($request)
    {
        $no_req = $request->input('no_req');
        $kegiatan = $request->input('kegiatan');

        // Query the database using Laravel's DB facade for Oracle
        $result = DB::connection('uster')->selectOne("
         SELECT
            *
            FROM
            (
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA_1,
                PAYMENT_CODE,
                'RECEIVING' KEGIATAN,
                TANGGAL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_receiving
            WHERE
                STATUS <> 'BATAL'
            UNION
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA_1,
                PAYMENT_CODE,
                CASE
                    WHEN STATUS = 'PERP' THEN 'PERP_PNK'
                    ELSE 'STUFFING'
                END KEGIATAN,
                TANGGAL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_stuffing
            WHERE
                STATUS <> 'BATAL'
            UNION
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA_1,
                PAYMENT_CODE,
                CASE
                    WHEN SUBSTR(NO_NOTA, 0, 2) = '03' THEN 'STRIPPING'
                    ELSE 'PERP_STRIP'
                END KEGIATAN,
                TANGGAL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_stripping
            WHERE
                STATUS <> 'BATAL'
            UNION
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA_1,
                PAYMENT_CODE,
                CASE
                    WHEN STATUS = 'PERP' THEN 'PERP_DEV'
                    ELSE 'DELIVERY'
                END KEGIATAN,
                TANGGAL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_delivery
            WHERE
                STATUS <> 'BATAL'
            UNION
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA_1,
                PAYMENT_CODE,
                'RELOKASI' KEGIATAN,
                TANGGAL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_relokasi
            WHERE
                STATUS <> 'BATAL'
            UNION
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA,
                PAYMENT_CODE,
                'BATAL_MUAT' KEGIATAN,
                TGL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_batal_muat
            WHERE
                STATUS <> 'BATAL'
            UNION
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA_1,
                PAYMENT_CODE,
                'RELOK_MTY' KEGIATAN,
                TANGGAL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_relokasi_mty
            WHERE
                STATUS <> 'BATAL'
            UNION
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA_1,
                PAYMENT_CODE,
                'DEL_PNK' KEGIATAN,
                TANGGAL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_pnkn_del
            WHERE
                STATUS <> 'BATAL'
            UNION
            SELECT
                NO_REQUEST,
                NO_NOTA,
                TGL_NOTA_1,
                PAYMENT_CODE,
                'STUF_PNK' KEGIATAN,
                TANGGAL_LUNAS,
                LUNAS,
                EMKL,
                TGL_NOTA,
                TOTAL_TAGIHAN
            FROM
                nota_pnkn_stuf
            WHERE
                STATUS <> 'BATAL')
            WHERE
            NO_NOTA IS NOT NULL
            AND PAYMENT_CODE IS NOT NULL
            AND TANGGAL_LUNAS IS NULL
            AND LUNAS = 'NO'
            AND NO_REQUEST = '$no_req'
            AND KEGIATAN = '$kegiatan'
            ORDER BY
            DBMS_RANDOM.VALUE
                    FETCH NEXT 1 ROWS ONLY
           ");
        if ($result) {
            // Generate the PDF
            return $this->generatePdf($result);
        }

        return null;
    }

    private function generatePdf($result)
    {
        $pdf = new TCPDF('P', 'mm', array(80, 90), true, 'UTF-8', false);
        $pdf->SetMargins(5, 5, 5); // Set margins
        $pdf->SetAutoPageBreak(true, 5); // Set auto page break
        $pdf->AddPage(); // Add a new page

        $pdf->SetFont('helvetica', '', 8);

        // Add the Payment Code Title
        $pdf->Ln(4);
        $pdf->Cell(0, 10, 'Payment Code', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);

        // Add the Payment Code Value
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, $result->payment_code ?? null, 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(3);

        // Define Barcode Style
        $style = [
            'position' => 'C',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'border' => true,
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false,
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4,
        ];

        // Add the Barcode
        $pdf->write1DBarcode($result->payment_code ?? null, 'C128A', '', '', '', 15, 0.4, $style, 'N');
        $pdf->Ln(2);

        // Add Biller and Customer Information
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(0, 7, 'Biller Name: ', 0, 0, 'L');
        $pdf->Cell(0, 7, 'Multi Terminal Indonesia', 0, 1, 'R');

        $pdf->Cell(0, 7, 'Customer Name: ', 0, 0, 'L');
        $emkl = (strlen($result->emkl ?? null) > 27) ? substr($result->emkl ?? null, 0, 27) . ".." : $result->emkl ?? null;
        $pdf->Cell(0, 7, $emkl, 0, 1, 'R');

        // Add Date Information
        $pdf->Cell(0, 7, 'Date: ', 0, 0, 'L');
        // $date = DateTime::createFromFormat('d-M-y h.i.s.u A', $result->tgl_nota ?? null);
        // $formattedDate = $date ? $date->format('d-M-y h:i:s') : 'N/A';
        $formattedDate = Carbon::parse($result->tgl_nota)->format('d-M-y H:i:s');
        $pdf->Cell(0, 7, $formattedDate, 0, 1, 'R');

        // Add NO_NOTA and Amount
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(0, 7, 'NO NOTA', 0, 0, 'L');
        $pdf->Cell(0, 7, 'AMOUNT', 0, 1, 'R');

        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(0, 7, $result->no_nota ?? null, 0, 0, 'L');
        $rupiah = 'Rp ' . number_format($result->total_tagihan ?? null, 0, ',', '.');
        $pdf->Cell(0, 7, $rupiah, 0, 1, 'R');

        // Add Total
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(0, 7, 'TOTAL', 0, 0, 'L');
        $pdf->Cell(0, 7, $rupiah, 0, 1, 'R');

        // Output PDF as a string
        return $pdf->Output('nota_sap.pdf', 'S'); // Return as string to be used in Response
    }


    public function dataNota($request)
    {
        $start = intval($request->start ?? 0);
        $length = intval($request->length ?? 0);
        $searchValue = $request->search['value'] ?? '';
        $nm_emkl = strtoupper($request->nm_emkl) ?? '';

        // Persiapkan kondisi pencarian untuk setiap subquery
        $searchAll = '';
        if ($searchValue != '') {
            $searchAll = "(NO_NOTA LIKE '%$searchValue%' OR NO_REQUEST LIKE '%$searchValue%')";
        }

        if ($request->cari == 'true') {
            $search = "AND EMKL LIKE '%$nm_emkl%'";
            $search .= $searchAll != '' ?  " OR " . $searchAll : '';
        } else {
            $search = $searchAll != '' ? "AND $searchAll" : '';
        }

        // Optimasi: Terapkan pencarian di setiap subquery UNION dan tambahkan ROWNUM pada setiap subquery
        $query = "WITH NotaData AS (
                SELECT * FROM (
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, 'RECEIVING' KEGIATAN, TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_receiving
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                    UNION ALL
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, CASE WHEN STATUS = 'PERP' THEN 'PERP_PNK' ELSE 'STUFFING' END KEGIATAN, TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_stuffing
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                    UNION ALL
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, CASE WHEN SUBSTR(NO_NOTA, 0, 2) = '03' THEN 'STRIPPING' ELSE 'PERP_STRIP' END KEGIATAN, TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_stripping
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                    UNION ALL
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, CASE WHEN STATUS = 'PERP' THEN 'PERP_DEV' ELSE 'DELIVERY' END KEGIATAN, TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_delivery
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                    UNION ALL
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, 'RELOKASI' KEGIATAN, TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_relokasi
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                    UNION ALL
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, 'BATAL_MUAT' KEGIATAN, TGL_NOTA TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_batal_muat
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                    UNION ALL
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, 'RELOK_MTY' KEGIATAN, TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_relokasi_mty
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                    UNION ALL
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, 'DEL_PNK' KEGIATAN, TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_pnkn_del
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                    UNION ALL
                    SELECT * FROM (
                        SELECT NO_NOTA, PAYMENT_CODE, NO_FAKTUR, NO_REQUEST, NO_NOTA_MTI, NO_FAKTUR_MTI, EMKL, 'STUF_PNK' KEGIATAN, TGL_NOTA_1, TOTAL_TAGIHAN, STATUS, KD_EMKL
                        FROM nota_pnkn_stuf
                        WHERE STATUS <> 'BATAL' $search
                        ORDER BY TGL_NOTA_1 DESC
                    ) WHERE ROWNUM <= $length
                )
                ORDER BY TGL_NOTA_1 DESC
            )
            SELECT nd.*,
                   (SELECT count(*) FROM itpk_nota_header WHERE trx_number = nd.NO_FAKTUR) AS CEK
            FROM NotaData nd
            WHERE ROWNUM < ($length*2) + $start";

        return DB::connection('uster')->select($query);
    }


    public function print($request)
    {
        // $img = "data:image/jpeg;base64,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";
        $img = "data:image/png;base64,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";
        $noreq = $request->no_req;
        $jn = $request->jn;
        $tgl = $request->tgl;
        $tgl_new = strtotime($tgl);
        if ($tgl_new >= 1601398800) {
            if ($jn == 'RECEIVING') {
                return (new printNotaMTI($img))->printNotaLunasRecMti($noreq); // done
            } else if ($jn == 'STRIPPING') {
                return (new printNotaMTI($img))->printNotaLunasStripMti($noreq); //sone
            } else if ($jn == 'RELOK_MTY') {
                return (new printNotaMTI($img))->printNotaLunasRelokmtyMti($noreq); //done
            } else if ($jn == 'PERP_STRIP') {
                return (new printNotaMTI($img))->printNotaLunasPerpstripMti($noreq); //done
            } else if ($jn == 'STUF_PNK') {
                return (new printNotaMTI($img))->printnNotaLunasPnknstufMti($noreq); //done
            } else if ($jn == 'STUFFING') {
                return (new printNotaMTI($img))->printNotaLunasStufMti($noreq); // done
            } else if ($jn == 'DELIVERY') {
                $q = "select delivery_ke, no_req_ict from request_delivery where no_request = '$noreq'";
                $r = DB::connection('uster')->selectOne($q);
                if ($r->delivery_ke == 'TPK') {
                    return (new printNotaMTI($img))->printNotaLunasDeltpkMti($noreq); //done
                } else {
                    return (new printNotaMTI($img))->printNotaLunasDeluarMti($noreq); //done
                }
            } else if ($jn == 'PERP_DEV') {
                return (new printNotaMTI($img))->printNotaLunasPerpdelMti($noreq);
            } else if ($jn == 'PERP_PNK') {
                return (new printNotaMTI($img))->printNotaLunasPerpstufMti($noreq);
            } else if ($jn == 'BATAL_MUAT') {
                return (new printNotaMTI($img))->printNotaLunasBamuPti($noreq); //done
            } else if ($jn == 'DEL_PNK') {
                return (new printNotaMTI($img))->printNotaLunasPnkndelMti($noreq);
            }
        } else {
            if ($jn == 'RECEIVING') {
                return null;
            } else if ($jn == 'STRIPPING') {
                return null;
            } else if ($jn == 'RELOK_MTY') {
                return null;
            } else if ($jn == 'PERP_STRIP') {
                return null;
            } else if ($jn == 'STUF_PNK') {
                return null;
            } else if ($jn == 'STUFFING') {
                return null;
            } else if ($jn == 'DELIVERY') {
                return null;
            } else if ($jn == 'PERP_DEV') {
                return null;
            } else if ($jn == 'PERP_PNK') {
                return null;
            } else if ($jn == 'BATAL_MUAT') {
                return null;
            } else if ($jn == 'DEL_PNK') {
                return null;
            }
        }
    }

    function savePaymentPraya($request)
    {
        include 'esbhelper/class_lib.php';

        // require_lib('praya.php');

        // $esb = new esbclass();
        //===END ESB===

        $id_nota = $request->idn;
        $id_req = $request->idr;
        $jenis = $request->jenis;
        $emkl = $request->emkl;
        $koreksi = $request->koreksi;
        $bank_id = $request->bank_id;
        $via = $request->via;
        $user = session()->get('PENGGUNA_ID');
        $jum = $request["JUM"];
        $mti_nota = $request["MTI"];

        // adding by firman 25 nov 2020
        $no_mat = $request["NO_PERATURAN"];
        //print_r($no_mat); die();
        $flag_opus = 0;
        //echo 'jenisnya: '.$jenis;die;




        // save_payment_uster($id_req, $jenis, $bank_id);
        // die();
        // echo "save_payment_praya";
        // die();

        // echo $jenis;;die;

        if ($jenis == 'STRIPPING' || $jenis == 'DELIVERY' || $jenis == 'STUFFING' || $jenis == 'PERP_STRIP' || $jenis == 'PERP_PNK' || $jenis == 'BATAL_MUAT') {

            if ($jenis == 'STRIPPING' || $jenis == 'PERP_STRIP') {
                $q = "select via from container_stripping WHERE no_request = '$id_req'";
                $r = DB::connection('uster')->selectOne($q);
                //if($r['VIA'] == 'TPK'){
                $flag_opus = 1;
                $ropus = "select o_reqnbs from request_stripping where no_request = '$id_req'";
                $mopus = DB::connection('uster')->selectOne($ropus);
                $reqopus = $mopus->o_reqnbs;
                //}
                // if($jenis == 'STRIPPING'){
                //     save_payment_uster($id_req, $jenis, $bank_id);
                // }
            } else if ($jenis == 'STUFFING' || $jenis == 'PERP_PNK') {
                $q = "select asal_cont from container_stuffing WHERE no_request = '$id_req'";
                $r = DB::connection('uster')->selectOne($q);
                if ($r->asal_cont == 'TPK') {
                    $flag_opus = 1;
                    $ropus = "select o_reqnbs from request_stuffing where no_request = '$id_req'";
                    $mopus = DB::connection('uster')->selectOne($ropus);
                    $reqopus = $mopus->o_reqnbs;

                    // if($jenis == 'STUFFING'){
                    //     save_payment_uster($id_req, $jenis, $bank_id);
                    // }
                }
            } else if ($jenis == 'DELIVERY') {
                $q = "select delivery_ke, no_req_ict from request_delivery where no_request = '$id_req'";
                $r = DB::connection('uster')->selectOne($q);
                if ($r->delivery_ke == 'TPK') {
                    $flag_opus = 1;
                    $reqopus = $r->no_req_ict;

                    // save_payment_uster($id_req, $jenis, $bank_id);
                }
            } else if ($jenis == 'BATAL_MUAT') {
                $q = "select status_gate,o_reqnbs from request_batal_muat where no_request = '$id_req'";
                $r = DB::connection('uster')->selectOne($q);
                if ($r->status_gate == '2') {
                    $flag_opus = 1;
                    $reqopus = $r->o_reqnbs;

                    // save_payment_uster($id_req, $jenis, $bank_id);
                }
            }

            if ($flag_opus == 1) {
                //echo $reqopus; die();
                $param_payment2 = array(
                    "ID_NOTA" => $id_nota,
                    "ID_REQ" => $reqopus,
                    "OUT" => '',
                    "OUT_MSG" => ''
                );
                $query2 = "declare begin opus_repo.payment_opusbill(:ID_REQ,:ID_NOTA,:OUT,:OUT_MSG); end;";

                DB::connection('uster')->statement($query2, $param_payment2);
            } else {
                $param_payment2["OUT"] = 'S';
            }
        } else {
            $param_payment2["OUT"] = 'S';
        }



        if ($param_payment2["OUT"] == 'S') {


            //// SAVE TO TOS PRAYA START
            if ($jenis == 'STUFFING') {
                // echo "save_payment_praya stuffing";
                $q_get_asal_stuffing = "SELECT STUFFING_DARI FROM REQUEST_STUFFING WHERE NO_REQUEST = '$id_req'";
                $get_asal_stuffing = DB::connection('uster')->selectOne($q_get_asal_stuffing);;
                $stuffing_dari = $get_asal_stuffing->stuffing_dari;
            }
            if ($jenis == 'DELIVERY') {
                // echo "save_payment_praya delivery";
                $q_get_tujuan_delivery = "SELECT DELIVERY_KE FROM REQUEST_DELIVERY WHERE NO_REQUEST = '$id_req'";
                $get_tujuan_delivery = DB::connection('uster')->selectOne($q_get_tujuan_delivery);;
                $delivery_ke = $get_tujuan_delivery->delivery_ke;
            }
            if ($jenis == 'BATAL_MUAT') {
                // echo "save_payment_praya batal_muat";
                $q_get_status_gate = "SELECT STATUS_GATE, JENIS_BM, BIAYA FROM REQUEST_BATAL_MUAT WHERE NO_REQUEST = '$id_req'";
                $get_status_gate = DB::connection('uster')->selectOne($q_get_status_gate);;
                $status_gate = $get_status_gate->status_gate;
                $jenis_bm = $get_status_gate->jenis_bm;
                $biaya = $get_status_gate->biaya;
            }

            if (
                $jenis == 'STRIPPING' || $jenis == 'PERP_STRIP' ||
                ($jenis == 'DELIVERY' && $delivery_ke == 'TPK') ||
                ($jenis == 'STUFFING' && $stuffing_dari == 'TPK') ||
                ($jenis == 'BATAL_MUAT' && $status_gate == '2' && $jenis_bm == 'alih_kapal' && $biaya == 'Y')
            ) {
                // echo "save_payment_praya faktur awal";
                $param_faktur = array(
                    "ID_REQ" => $id_req,
                    "IN_PROFORMA" => $id_nota,
                    "IN_USER" => $user,
                    "MTI_NOTA" => $mti_nota,
                    "OUT_FAKTUR_MTI" => $mti_faktur
                );
                $execute_faktur = "BEGIN USTER.ITPK_POPULATE_STAGING_PRAYA.GENERATE_FAKTUR_CODE ( :ID_REQ, :IN_PROFORMA, :IN_USER, :MTI_NOTA, :OUT_FAKTUR_MTI ); END;";
                DB::connection('uster')->statement($execute_faktur, $param_faktur);


                $param_update_nota = array(
                    "v_faktur_mti" => $param_faktur['OUT_FAKTUR_MTI'],
                    "ID_REQ" => $id_req,
                    "IN_PROFORMA" => $id_nota
                );


                if ($jenis == 'STRIPPING' || $jenis == 'PERP_STRIP') {
                    $nota_from_table = 'nota_stripping';
                    // echo "<<query nota stripping";
                }
                if ($jenis == 'DELIVERY') {
                    $nota_from_table = 'nota_delivery';
                    // echo "<<query nota delviery";
                }
                if ($jenis == 'STUFFING') {
                    $nota_from_table = 'nota_stuffing';
                    // echo "<<query nota stuffing";
                }
                if ($jenis == 'BATAL_MUAT') {
                    $nota_from_table = 'nota_batal_muat';
                    // echo "<<query nota batal muat";
                }

                $execute_update_nota = "update " . $nota_from_table . "
        SET
            no_faktur_mti = :v_faktur_mti
        WHERE
            no_request = :ID_REQ
            AND no_nota = :IN_PROFORMA";

                $ex_update_nota = DB::connection('uster')->statement($execute_update_nota, $param_update_nota);

                $payload = array(
                    "JENIS" => $jenis,
                    "ID_REQUEST" => $id_req,
                    "BANK_ACCOUNT_NUMBER" => $bank_id
                    // "PAYMENT_CODE" => $param_faktur['OUT_FAKTUR_MTI']
                );
                $res_save = save_payment_uster($payload);
                // $res_save = $this->save_payment_uster($id_req, $jenis, $bank_id);


                $response_from_praya = json_decode($res_save["response"], true);
                if ($res_save["status"] == "error" || $response_from_praya["code"] == "0") {
                    if ($response_from_praya["msg"] != 'Data already exists.') {
                        // echo "return error dari save_payment_uster tertrigger";
                        $execute_revert_nota = "UPDATE
                " . $nota_from_table . "
                SET
                    no_faktur_mti = NULL
                WHERE
                    no_request = :ID_REQ
                    AND no_nota = :IN_PROFORMA";

                        echo "Failed to save payment praya, please try again\n";
                        if ($res_save['response']) {
                            $res = json_decode($res_save['response']);
                            if ($res->msg) echo "Error response : " . $res->msg;
                        }
                        DB::connection('uster')->statement($execute_revert_nota, $param_update_nota);
                        die();
                    }
                }
            }


            $param_payment = array(
                "ID_REQ" => $id_req,
                "IN_MODUL" => $jenis,
                "IN_PROFORMA" => $id_nota,
                "IN_IDNOTA" => $id_nota,
                "IN_KOREKSI" => $koreksi,
                "IN_USER" => $user,
                "IN_BANKID" => $bank_id,
                "IN_BAYAR" => $via,
                "IN_EMKL" => $emkl,
                "IN_JUM" => $jum,
                "MTI_NOTA" => $mti_nota,
                "IN_MAT" => $no_mat,
                "INOUT_TRXNUMBER" => ''
            ); //print_r($param_payment);die();
            $sql_xpi = "BEGIN USTER.ITPK_POPULATE_STAGING.INSERT_NOTA_ITPK ( :ID_REQ, :IN_MODUL, :IN_PROFORMA, :IN_IDNOTA, :IN_KOREKSI, :IN_USER, :IN_BANKID, :IN_BAYAR, :IN_EMKL,:IN_JUM, :MTI_NOTA,:IN_MAT ,:INOUT_TRXNUMBER);
    END; ";

            DB::connection('uster')->statement($sql_xpi, $param_payment);

            $trx_number = $param_payment["OUT_TRX_NUMBER"];

            $sql_header = "select distinct * from itpk_nota_header
        where status in ('2','4a','4b')
            and status_nota=0
            and trx_number = '" . $trx_number . "'";

            $rs = DB::connection('uster')->selectOne($sql_header);


            $sql_detail = "select * from itpk_nota_detail where trx_number = '" . $trx_number . "'";
            $rsLines = DB::connection('uster')->select($sql_detail);


            $response = 'S';
            $erroMessage = 'Succes';


            if ($response == "S") {

                $responseReceipt = 'S';
                $erroMessageReceipt = 'Sukses';

                if ($responseReceipt == "S") {
                } else {
                }
            } else {
            }
            echo $erroMessage;
            die;
        } else {
            echo 'failed ' . $param_payment2["OUT_MSG"];
        }
    }

    /** GA KEPAKE, PINDAH KE HELPER PRAYAINTEGRATION */
    // function save_payment_uster($id_request, $jenis_payment, $bank_id)
    // {


    //     // require_lib('request_integration.php');

    //     // takes raw data from the request
    //     $json = file_get_contents('php://input');
    //     // Converts it into a PHP object
    //     $payload_uster_save = json_decode($json, true);

    //     $url_uster_save = env('PRAYA_API_INTEGRATION') . "/api/usterSave";

    //     $payloadBatalMuat = $payload_uster_save["PAYLOAD_BATAL_MUAT"];
    //     // $jenis = $payload_uster_save["JENIS"];
    //     $jenis = $jenis_payment;
    //     // $id_req = $payload_uster_save["ID_REQUEST"];
    //     $id_req = $id_request;
    //     // $bankAccountNumber = $payload_uster_save["BANK_ACCOUNT_NUMBER"];
    //     $payment_via = "CMS";
    //     $bankAccountNumber = $bank_id;
    //     $paymentCode = $payload_uster_save["PAYMENT_CODE"];
    //     $charge = empty($payloadBatalMuat) ? "Y" : "N"; //kalau payload batal muat ada berarti tdk bayar


    //     // Variable untuk logging container, hanya variable penting yg dimasukan karena container bisa jadi sngt bnyk (Sementara utk PERP_STRIP)
    //     $containerListLog = array();


    //     $del_no_request = empty($payloadBatalMuat) ? $id_req : $payloadBatalMuat->ex_noreq;
    //     $queryDelivery =
    //         "SELECT

    //             rd.NO_REQUEST,
    //             rd.NO_BOOKING,
    //             rd.KD_EMKL,
    //             rd.O_VESSEL,
    //             rd.VOYAGE,
    //             rd.KD_PELABUHAN_ASAL, --POL
    //             rd.KD_PELABUHAN_TUJUAN, --POD
    //             rd.O_VOYIN,
    //             rd.O_VOYOUT,
    //             rd.DELIVERY_KE,
    //             rd.TGL_REQUEST,
    //             rd.DI,
    //             -- vpc.*,
    //             vpc.KD_KAPAL,
    //             vpc.NM_KAPAL,
    //             vpc.VOYAGE_IN,
    //             vpc.VOYAGE_OUT,
    //             vpc.PELABUHAN_TUJUAN,
    //             vpc.PELABUHAN_ASAL,
    //             vpc.NM_AGEN,
    //             vpc.KD_AGEN,
    //             --	nd.*,
    //             nd.NO_NOTA,
    //             nd.NO_FAKTUR_MTI,
    //             nd.TAGIHAN,
    //             nd.PPN,
    //             nd.TOTAL_TAGIHAN,
    //             nd.EMKL,
    //             nd.ALAMAT,
    //             nd.NPWP,
    //             TO_CHAR(nd.TGL_NOTA ,'YYYY-MM-DD HH24:MI:SS') TGLNOTA,
    //             TO_CHAR(rd.TGL_REQUEST,'YYYY-MM-DD HH24:MI:SS') TGLSTART,
    //             TO_CHAR(rd.TGL_REQUEST + INTERVAL '4' DAY,'YYYY-MM-DD HH24:MI:SS') TGLEND,
    //             -- vmp.*,
    //             vmp.NO_ACCOUNT_PBM KD_PELANGGAN
    //         FROM
    //             REQUEST_DELIVERY rd
    //         LEFT JOIN V_PKK_CONT vpc ON
    //             rd.NO_BOOKING = vpc.NO_BOOKING
    //         JOIN NOTA_DELIVERY nd ON
    //             nd.NO_REQUEST = rd.NO_REQUEST
    //         JOIN V_MST_PBM vmp ON
    //             vmp.KD_PBM = rd.KD_EMKL
    //         WHERE
    //             rd.NO_REQUEST = '$del_no_request'";

    //     if ($jenis == 'STRIPPING' || $jenis == 'PERP_STRIP') { //DELIVERY KALO DARI SISI TPK
    //         $queryStripping =
    //             "SELECT
    //             rs.NO_BOOKING,
    //             rs.NO_BL,
    //             rs.TYPE_STRIPPING,
    //             vpc.KD_KAPAL,
    //             vpc.NM_KAPAL,
    //             vpc.VOYAGE_IN,
    //             vpc.VOYAGE_OUT,
    //             vpc.VOYAGE, -- KOLOM BELUM DIISI DI DEV
    //             vpc.PELABUHAN_TUJUAN,
    //             vpc.PELABUHAN_ASAL,
    //             vpc.NM_AGEN,
    //             vpc.KD_AGEN,
    //             vpc.NO_UKK,
    //             ns.NO_NOTA,
    //             ns.NO_FAKTUR_MTI,
    //             ns.TAGIHAN,
    //             ns.PPN,
    //             ns.TOTAL_TAGIHAN,
    //             ns.EMKL,
    //             ns.ALAMAT,
    //             ns.NPWP,
    //             vmp.NO_ACCOUNT_PBM KD_PELANGGAN,
    //             TO_CHAR(rs.TGL_AWAL,'YYYY-MM-DD HH24:MI:SS') TGLAWAL,
    //             TO_CHAR(rs.TGL_AKHIR,'YYYY-MM-DD HH24:MI:SS') TGLAKHIR,
    //             TO_CHAR(cs.TGL_APPROVE, 'YYYY-MM-DD HH24:MI:SS') TGLAPPROVE,
    //             TO_CHAR(cs.TGL_APP_SELESAI, 'YYYY-MM-DD HH24:MI:SS') TGLAPPROVE_SELESAI,
    //             TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA,
    //             -- TO_CHAR(pcs.TGL_SELESAI, 'YYYY-MM-DD HH24:MI:SS') TGLSELESAI,
    //             CASE
    //                 WHEN rs.TGL_AWAL IS NULL OR rs.TGL_AKHIR IS NULL THEN 4
    //                 ELSE rs.TGL_AKHIR - rs.TGL_AWAL
    //             END AS COUNT_DAYS
    //         FROM
    //             REQUEST_STRIPPING rs
    //         LEFT JOIN V_PKK_CONT vpc ON
    //             rs.NO_BOOKING = vpc.NO_BOOKING
    //         JOIN NOTA_STRIPPING ns ON
    //             ns.NO_REQUEST = rs.NO_REQUEST
    //         JOIN CONTAINER_STRIPPING cs ON
    //             rs.NO_REQUEST = cs.NO_REQUEST
    //         JOIN V_MST_PBM vmp ON
    //             vmp.KD_PBM = ns.KD_EMKL
    //         --   JOIN PLAN_REQUEST_STRIPPING prs ON rs.NO_REQUEST = prs.NO_REQUEST_APP_STRIPPING
    //         --   JOIN PLAN_CONTAINER_STRIPPING pcs ON pcs.NO_REQUEST = prs.NO_REQUEST
    //         WHERE
    //                 rs.NO_REQUEST = '$id_req'";
    //         $fetchStripping = DB::connection('uster')->selectOne($queryStripping);
    //         $queryContainerStripping =
    //             "SELECT cs.*, mc.*, TO_CHAR(cs.TGL_SELESAI, 'YYYY-MM-DD HH24:MI:SS') TGLSELESAI, TO_CHAR(cs.END_STACK_PNKN, 'YYYY-MM-DD HH24:MI:SS') TGLSELESAI_PERP FROM CONTAINER_STRIPPING cs JOIN MASTER_CONTAINER mc ON cs.NO_CONTAINER = mc.NO_CONTAINER WHERE cs.NO_REQUEST = '$id_req'";
    //         $fetchContainerStripping =  DB::connection('uster')->select($queryContainerStripping);
    //         $queryNotaStripping =
    //             "SELECT ns.*, nsd.*, TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA, (SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = nsd.ID_ISO) STATUS, TO_CHAR(nsd.START_STACK,'YYYY-MM-DD HH24:MI:SS') AWAL_PENUMPUKAN, TO_CHAR(nsd.END_STACK,'YYYY-MM-DD HH24:MI:SS') AKHIR_PENUMPUKAN FROM NOTA_STRIPPING ns JOIN NOTA_STRIPPING_D nsd ON nsd.NO_NOTA = ns.NO_NOTA WHERE ns.NO_REQUEST = '$id_req' ";
    //         $fetchNotaStripping = DB::connection('uster')->select($queryNotaStripping);
    //         $queryGetAdmin =
    //             "SELECT TARIF FROM NOTA_STRIPPING ns JOIN NOTA_STRIPPING_D nsd ON nsd.NO_NOTA = ns.NO_NOTA WHERE ns.NO_REQUEST = '$id_req' AND nsd.ID_ISO = 'ADM' ";
    //         $adminComponent = DB::connection('uster')->selectOne($queryGetAdmin);

    //         $get_vessel = $this->getVessel($fetchStripping->nm_kapal, $fetchStripping->voyage, $fetchStripping->voyage_in, $fetchStripping->voyage_out);

    //         $get_container_list = $this->getContainer(NULL, $fetchStripping->kd_kapal, $fetchStripping->voyage_in, $fetchStripping->voyage_out, $fetchStripping->voyage, "I", "DEL");

    //         $get_iso_code = $this->getIsoCode();

    //         if (empty($get_iso_code)) {
    //             $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
    //             $response_uster_save = array(
    //                 'code' => "0",
    //                 'msg' => "Gagal mengambil Iso Code ke Praya"
    //             );
    //             $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //             echo json_encode($response_uster_save);
    //             die();
    //         }

    //         $tgl_awal = $fetchStripping->tglawal;
    //         $tgl_akhir = $fetchStripping->tglakhir;
    //         if (empty($tgl_awal) || empty($tgl_akhir)) {
    //             $tgl_awal = $fetchStripping->tglapprove;
    //             $tgl_akhir = $fetchStripping->tglapprove_selesai;
    //         }

    //         // echo json_encode($get_vessel);
    //         // echo json_encode($get_container_list);

    //         $pelabuhan_asal = $fetchStripping->pelabuhan_asal;
    //         $pelabuhan_tujuan = $fetchStripping->pelabuhan_tujuan;

    //         $idRequest = $id_req;
    //         $trxNumber = $fetchStripping->no_nota;
    //         $paymentDate = $fetchStripping->tglnota;
    //         $invoiceNumber = $fetchStripping->no_faktur_mti;
    //         $requestType = 'STRIPPING';
    //         $parentRequestId = '';
    //         $parentRequestType = 'STRIPPING';
    //         $serviceCode = 'DEL';
    //         $vesselId = $fetchStripping->kd_kapal;
    //         $vesselName = $fetchStripping->nm_kapal;
    //         $voyage = empty($fetchStripping->voyage) ? '' : $fetchStripping->voyage;
    //         $voyageIn = empty($fetchStripping->voyage_in) ? '' : $fetchStripping->voyage_in;
    //         $voyageOut = empty($fetchStripping->voyage_out) ? '' : $fetchStripping->voyage_out;
    //         $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut;
    //         $eta = empty($get_vessel['eta']) ? '' : $get_vessel['eta'];
    //         $etb = empty($get_vessel['etb']) ? '' : $get_vessel['etb'];
    //         $etd = empty($get_vessel['etd']) ? '' : $get_vessel['etd'];
    //         $ata = empty($get_vessel['ata']) ? '' : $get_vessel['ata'];
    //         $atb = empty($get_vessel['atb']) ? '' : $get_vessel['atb'];
    //         $atd = empty($get_vessel['atd']) ? '' : $get_vessel['atd'];
    //         $startWork = empty($get_vessel['start_work']) ? '' : $get_vessel['start_work'];
    //         $endWork = empty($get_vessel['end_work']) ? '' : $get_vessel['end_work'];
    //         $pol = $pelabuhan_asal;
    //         $pod = $pelabuhan_tujuan;
    //         $dischargeDate = $get_vessel['discharge_date'];
    //         $shippingLineName = $fetchStripping->nm_agen;
    //         $customerCode = $fetchStripping->kd_pelanggan;
    //         $customerCodeOwner = '';
    //         $customerName = $fetchStripping->emkl;
    //         $customerAddress = $fetchStripping->alamat;
    //         $npwp = $fetchStripping->npwp;
    //         $blNumber = $fetchStripping->no_bl;
    //         $bookingNo = $fetchStripping->no_booking;
    //         // $deliveryDate = $fetchStripping->tglselesai; //paythruDate
    //         $doNumber = $fetchStripping->no_booking;
    //         // $doDate = "";
    //         $tradeType = $fetchStripping->type_stripping == 'D' ? 'I' : 'O';
    //         $customsDocType = "";
    //         $customsDocNo = "";
    //         $customsDocDate = "";
    //         if ((int)$fetchStripping->total_tagihan > 5000000) {
    //             $amount = (int)$fetchStripping->total_tagihan + 10000;
    //         } else {
    //             (int)$amount = $fetchStripping->total_tagihan;
    //         }
    //         if ($adminComponent) {
    //             $administration = $adminComponent->tarif;
    //         }
    //         if (empty($fetchStripping->ppn)) {
    //             $ppn =  'N';
    //         } else {
    //             $ppn = 'Y';
    //         };
    //         $amountPpn  = (int)$fetchStripping->ppn;
    //         $amountDpp = (int)$fetchStripping->tagihan;
    //         if ((int)$fetchStripping->tagihan > 5000000) {
    //             $amountMaterai = 10000;
    //         } else {
    //             $amountMaterai = 0;
    //         }
    //         $approvalDate = empty($fetchStripping->tglapprove) ? '' : $fetchStripping->tglapprove;
    //         $status = 'PAID';
    //         $changeDate = $fetchStripping->tglnota;
    //         $charge = 'Y';

    //         $detailList = array();
    //         $containerList = array();
    //         foreach ($fetchContainerStripping as $k => $v) {
    //             foreach ($get_container_list as $k_container => $v_container) {
    //                 if ($v_container->containerno  == $v->no_container) {
    //                     $_get_container = $v_container;
    //                     break;
    //                 }
    //             }
    //             $reslt = array();
    //             foreach ($get_iso_code as $key => $value) {
    //                 if (strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_)) {
    //                     array_push($reslt, $value);
    //                 }
    //             }
    //             $array_iso_code = array_values($reslt);
    //             $new_iso = $this->mapNewIsoCode($array_iso_code[0]["isoCode"]);

    //             // CHOSSY.P (26/12/2023)
    //             // penambahan paythru utk perp strip
    //             $paythru = $v->tglselesai;
    //             if (substr($idRequest, 0, 3) == "STP") {
    //                 $paythru = $v->tglselesai_perp;

    //                 array_push($containerListLog, array(
    //                     "containerNo" => $v->no_container,
    //                     "containerDeliveryDate" => $paythru
    //                 ));
    //             }
    //             // END

    //             array_push($containerList, $v->no_container);
    //             array_push(
    //                 $detailList,
    //                 array(
    //                     "detailDescription" => "CONTAINER",
    //                     "containerNo" => $v->no_container,
    //                     "containerSize" => $v->size_,
    //                     "containerType" => $v->type_,
    //                     "containerStatus" => "FULL",
    //                     "containerHeight" => "8.5",
    //                     "hz" => empty($v->hz) ? (empty($_get_container['hz']) ? 'N' : $_get_container['hz']) : $v->hz,
    //                     "imo" => "N",
    //                     "unNumber" => empty($_get_container['unNumber']) ? '' : $_get_container['unNumber'],
    //                     "reeferNor" => "N",
    //                     "temperatur" => "",
    //                     "ow" => "",
    //                     "oh" => "",
    //                     "ol" => "",
    //                     "overLeft" => "",
    //                     "overRight" => "",
    //                     "overFront" => "",
    //                     "overBack" => "",
    //                     "weight" => "",
    //                     "commodityCode" => trim($v->commodity, " "),
    //                     "commodityName" => trim($v->commodity, " "),
    //                     "carrierCode" => $fetchStripping->kd_agen,
    //                     "carrierName" => $fetchStripping->nm_agen,
    //                     "isoCode" => $new_iso,
    //                     "plugInDate" => "",
    //                     "plugOutDate" => "",
    //                     "ei" => "I",
    //                     "dischLoad" => "",
    //                     "flagOog" => empty($_get_container['flagOog']) ? '' : $_get_container['flagOog'],
    //                     "gateInDate" => "",
    //                     "gateOutDate" => "",
    //                     "startDate" => $tgl_awal,
    //                     "endDate" => $tgl_akhir,
    //                     "containerDeliveryDate" => $paythru,
    //                     "containerLoadingDate" => "",
    //                     "containerDischargeDate" => $get_vessel['discharge_date'],
    //                     "disabled" => "Y"
    //                 )
    //             );
    //         }

    //         $strContList = implode(", ", $containerList);
    //         $detailPranotaList = array();
    //         foreach ($fetchNotaStripping as $k => $v) {
    //             $status = "";
    //             if (!empty($v->status) && $v->status != "-") {
    //                 $status = $v->status == "FCL" ? "FULL" : "EMPTY";
    //             }
    //             array_push(
    //                 $detailPranotaList,
    //                 array(
    //                     "lineNumber" => $v->line_number,
    //                     "description" => $v->keterangan,
    //                     "flagTax" => "Y",
    //                     "componentCode" => $v->keterangan,
    //                     "componentName" => $v->keterangan,
    //                     "startDate" => $v->awal_penumpukan,
    //                     "endDate" => $v->akhir_penumpukan,
    //                     "quantity" => $v->jml_cont,
    //                     "tarif" => $v->tarif,
    //                     "basicTarif" => $v->tarif,
    //                     "containerList" => $strContList,
    //                     "containerSize" => $fetchContainerStripping[0]['SIZE_'],
    //                     "containerType" => $fetchContainerStripping[0]['TYPE_'],
    //                     "containerStatus" => $status,
    //                     "containerHeight" => "8.5",
    //                     "hz" => empty($v->hz) ? 'N' : $v->hz,
    //                     "ei" => "I",
    //                     "equipment" => "",
    //                     "strStartDate" => $v->awal_penumpukan,
    //                     "strEndDate" => $v->akhir_penumpukan,
    //                     "days" => $fetchStripping->count_days,
    //                     "amount" => $v->biaya,
    //                     "via" => "YARD",
    //                     "package" => "",
    //                     "unit" => "BOX",
    //                     "qtyLoading" => "",
    //                     "qtyDischarge" => "",
    //                     "equipmentName" => "",
    //                     "duration" => "",
    //                     "flagTool" => "N",
    //                     "itemCode" => "",
    //                     "oog" => "N",
    //                     "imo" => "",
    //                     "blNumber" => empty($fetchStripping->no_bl) ? '' : $fetchStripping->no_bl,
    //                     "od" => "N",
    //                     "dg" => "N",
    //                     "sling" => "N",
    //                     "changeDate" => $v->tglnota,
    //                     "changeBy" => "Admin Uster"
    //                 )
    //             );
    //         }
    //     } elseif ($jenis == 'STUFFING' /* || $jenis == 'PERP_PNK' */) { //RECEIVING
    //         $queryStuffing =
    //             "SELECT
    //                 rs.NO_BOOKING,
    //                 rs.NO_BL,
    //                 rs.NO_NPE, --customDocs
    //                 rs.DI,
    //                 rs.STUFFING_DARI, --ASAL STUFFING HARUS DARI TPK
    //                 vpc.KD_KAPAL,
    //                 vpc.NM_KAPAL,
    //                 vpc.VOYAGE_IN,
    //                 vpc.VOYAGE_OUT,
    //                 vpc.VOYAGE, -- KOLOM BELUM DIISI
    //                 vpc.PELABUHAN_TUJUAN,
    //                 vpc.PELABUHAN_ASAL,
    //                 vpc.NM_AGEN,
    //                 vpc.KD_AGEN,
    //                 vpc.NO_UKK,
    //                 ns.NO_NOTA,
    //                 ns.NO_FAKTUR_MTI,
    //                 ns.TAGIHAN,
    //                 ns.PPN,
    //                 ns.TOTAL_TAGIHAN,
    //                 ns.EMKL,
    //                 ns.ALAMAT,
    //                 ns.NPWP,
    //                 vmp.NO_ACCOUNT_PBM KD_PELANGGAN,
    //                 cs.ASAL_CONT, --ASAL CONTAINER HARUS DARI TPK
    //                 TO_CHAR(pcs.TGL_APPROVE,'YYYY-MM-DD HH24:MI:SS') TGLAPPROVE,
    //                 TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA,
    //                 TO_CHAR(rs.TGL_REQUEST,'YYYY-MM-DD HH24:MI:SS') TGLSTART,
    //                 TO_CHAR(rs.TGL_REQUEST + INTERVAL '4' DAY,'YYYY-MM-DD HH24:MI:SS') TGLEND
    //             FROM
    //                 REQUEST_STUFFING rs
    //             LEFT JOIN V_PKK_CONT vpc ON
    //                 rs.NO_BOOKING = vpc.NO_BOOKING
    //             JOIN NOTA_STUFFING ns ON
    //                 ns.NO_REQUEST = rs.NO_REQUEST
    //             JOIN V_MST_PBM vmp ON
    //                 vmp.KD_PBM = ns.KD_EMKL
    //             JOIN CONTAINER_STUFFING cs ON
    //                 cs.NO_REQUEST = rs.NO_REQUEST
    //             JOIN PLAN_REQUEST_STUFFING prs ON
    //                 prs.NO_REQUEST_APP = rs.NO_REQUEST
    //             JOIN PLAN_CONTAINER_STUFFING pcs ON
    //                 pcs.NO_REQUEST = prs.NO_REQUEST
    //             WHERE rs.NO_REQUEST = '$id_req'";
    //         $fetchStuffing = DB::connection('uster')->selectOne($queryStuffing);


    //         if ($fetchStuffing->stuffing_dari == 'TPK') {
    //             $queryContainerStuffing =
    //                 "SELECT cs.*, mc.*, TO_CHAR(cs.START_PERP_PNKN,'YYYY-MM-DD HH24:MI:SS') TGLPAYTHRU FROM CONTAINER_STUFFING cs JOIN MASTER_CONTAINER mc ON cs.NO_CONTAINER = mc.NO_CONTAINER WHERE cs.NO_REQUEST = '$id_req'";
    //             $fetchContainerStuffing = DB::connection('uster')->select($queryContainerStuffing);
    //             $queryNotaStuffing =
    //                 "SELECT ns.*, nsd.*, TO_CHAR(ns.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA, (SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = nsd.ID_ISO) STATUS, TO_CHAR(nsd.START_STACK,'YYYY-MM-DD HH24:MI:SS') AWAL_PENUMPUKAN, TO_CHAR(nsd.END_STACK,'YYYY-MM-DD HH24:MI:SS') AKHIR_PENUMPUKAN FROM NOTA_STUFFING ns JOIN NOTA_STUFFING_D nsd ON nsd.NO_NOTA = ns.NO_NOTA WHERE ns.NO_REQUEST = '$id_req' ";
    //             $fetchNotaStuffing = DB::connection('uster')->select($queryNotaStuffing);
    //             $queryGetAdmin =
    //                 "SELECT TARIF FROM NOTA_STUFFING ns JOIN NOTA_STUFFING_D nsd ON nsd.NO_NOTA = ns.NO_NOTA WHERE ns.NO_REQUEST = '$id_req' AND nsd.ID_ISO = 'ADM' ";
    //             $adminComponent = DB::connection('uster')->selectOne($queryGetAdmin);

    //             // $get_vessel = getVessel($fetchStuffing->nm_kapal, $fetchStuffing->voyage, $fetchStuffing->voyage_in, $fetchStuffing->voyage_out);

    //             // $get_container_list = getContainer(NULL, $fetchStuffing->kd_kapal, $fetchStuffing->voyage_in, $fetchStuffing->voyage_out, $fetchStuffing->voyage, "E", "REC");

    //             $get_iso_code = $this->getIsoCode();

    //             if (empty($get_iso_code)) {
    //                 $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
    //                 $response_uster_save = array(
    //                     'code' => "0",
    //                     'msg' => "Gagal mengambil Iso Code ke Praya"
    //                 );
    //                 $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //                 return json_encode($response_uster_save);
    //             }

    //             // echo json_encode($get_vessel) . '<<ves';
    //             // echo json_encode($get_container_list) . '<<cont';

    //             $tgl_awal = $fetchStuffing->tglstart;
    //             $tgl_akhir = $fetchStuffing->tglend;

    //             $pelabuhan_asal = $fetchStuffing->pelabuhan_asal;
    //             $pelabuhan_tujuan = $fetchStuffing->pelabuhan_tujuan;

    //             $idRequest = $id_req;
    //             $trxNumber = $fetchStuffing->no_nota;
    //             $paymentDate = $fetchStuffing->tglnota;
    //             $invoiceNumber = $fetchStuffing->no_faktur_mti;
    //             $requestType = 'STUFFING';
    //             $parentRequestId = '';
    //             $parentRequestType = 'STUFFING';
    //             $serviceCode = 'DEL';
    //             $vesselId = "";
    //             $vesselName = "";
    //             $voyage = "";
    //             $voyageIn = "";
    //             $voyageOut = "";
    //             $voyageInOut = "";
    //             $eta = "";
    //             $etb = "";
    //             $etd = "";
    //             $ata = "";
    //             $atb = "";
    //             $atd = "";
    //             $startWork = "";
    //             $endWork = "";
    //             $pol = $pelabuhan_asal;
    //             $pod = $pelabuhan_tujuan;
    //             $dischargeDate = ""; //$get_vessel['discharge_date'];
    //             $shippingLineName = $fetchStuffing->nm_agen;
    //             $customerCode = $fetchStuffing->kd_pelanggan;
    //             $customerCodeOwner = '';
    //             $customerName = $fetchStuffing->emkl;
    //             $customerAddress = $fetchStuffing->alamat;
    //             $npwp = $fetchStuffing->npwp;
    //             $blNumber = empty($fetchStuffing->no_bl) ? "" : $fetchStuffing->no_bl;
    //             $bookingNo = $fetchStuffing->no_booking;
    //             $deliveryDate = $fetchStuffing->tglapprove; //paythrudate
    //             $doNumber = $fetchStuffing->no_booking;
    //             // $doDate = '';
    //             $tradeType = $fetchStuffing->di == 'D' ? 'I' : 'O';
    //             $customsDocType = $fetchStuffing->di == 'D' ? "NPE" : "";
    //             $customsDocNo = $fetchStuffing->di == 'D' ? (empty($fetchStuffing["NO_NPE"]) ? "" : $fetchStuffing["NO_NPE"]) : "";
    //             $customsDocDate = "";
    //             if ((int)$fetchStuffing->total_tagihan > 5000000) {
    //                 $amount = (int)$fetchStuffing->total_tagihan + 10000;
    //             } else {
    //                 (int)$amount = $fetchStuffing->total_tagihan;
    //             }
    //             if ($adminComponent) {
    //                 $administration = $adminComponent->tarif;
    //             }
    //             if (empty($fetchStuffing->ppn)) {
    //                 $ppn =  'N';
    //             } else {
    //                 $ppn = 'Y';
    //             };
    //             $amountPpn  = (int)$fetchStuffing->ppn;
    //             $amountDpp = (int)$fetchStuffing->tagihan;
    //             if ($fetchStuffing->tagihan > 5000000) {
    //                 $amountMaterai = 10000;
    //             } else {
    //                 $amountMaterai = 0;
    //             }
    //             $approvalDate = empty($fetchStuffing->tglapprove) ? '' : $fetchStuffing->tglapprove;
    //             $status = 'PAID';
    //             $changeDate = $fetchStuffing->tglnota;
    //             $charge = 'Y';

    //             $detailList = array();
    //             $containerList = array();
    //             foreach ($fetchContainerStuffing as $k => $v) {
    //                 // foreach ($get_container_list as $k_container => $v_container) {
    //                 //   if ($v_container->containerno  == $v->no_container) {
    //                 //     $_get_container = $v_container;
    //                 //     break;
    //                 //   }
    //                 // }
    //                 // $array_iso_code = array_values(array_filter($get_iso_code, function ($value) use ($v) {
    //                 //     return strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_);
    //                 // }));

    //                 $reslt = array();
    //                 foreach ($get_iso_code as $key => $value) {
    //                     if (strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_)) {
    //                         array_push($reslt, $value);
    //                     }
    //                 }
    //                 $array_iso_code = array_values($reslt);
    //                 $new_iso = $this->mapNewIsoCode($array_iso_code[0]["isoCode"]);

    //                 array_push($containerList, $v->no_container);
    //                 array_push(
    //                     $detailList,
    //                     array(
    //                         "detailDescription" => "CONTAINER",
    //                         "containerNo" => $v->no_container,
    //                         "containerSize" => $v->size_,
    //                         "containerType" => $v->type_,
    //                         "containerStatus" => "FULL",
    //                         "containerHeight" => "8.5",
    //                         "hz" => empty($v->hz) ? 'N' : $v->hz,
    //                         "imo" => "N",
    //                         // "unNumber" => empty($_get_container->unnumber) ? '' : $_get_container->unnumber,
    //                         "unNumber" => "",
    //                         "reeferNor" => "N",
    //                         "temperatur" => "",
    //                         "ow" => "",
    //                         "oh" => "",
    //                         "ol" => "",
    //                         "overLeft" => "",
    //                         "overRight" => "",
    //                         "overFront" => "",
    //                         "overBack" => "",
    //                         "weight" => $v->berat,
    //                         "commodityCode" => trim($v->commodity, " "),
    //                         "commodityName" => trim($v->commodity, " "),
    //                         "carrierCode" => $fetchStuffing->kd_agen,
    //                         "carrierName" => $fetchStuffing->nm_agen,
    //                         "isoCode" => $new_iso,
    //                         "plugInDate" => "",
    //                         "plugOutDate" => "",
    //                         "ei" => "I",
    //                         "dischLoad" => "",
    //                         // "flagOog" => empty($_get_container->flagoog) ? '' : $_get_container->flagoog,
    //                         "flagOog" => "",
    //                         "gateInDate" => "",
    //                         "gateOutDate" => "",
    //                         "startDate" => $fetchStuffing->tglstart,
    //                         "endDate" => $fetchStuffing->tglend,
    //                         "containerDeliveryDate" => $v->tglpaythru,
    //                         "containerLoadingDate" => "",
    //                         "containerDischargeDate" => "",
    //                         "disabled" => "Y"
    //                     )
    //                 );
    //             }

    //             $strContList = implode(", ", $containerList);
    //             $detailPranotaList = array();
    //             foreach ($fetchNotaStuffing as $k => $v) {
    //                 $status = "";
    //                 if (!empty($v->status) && $v->status != "-") {
    //                     $status = $v->status == "FCL" ? "FULL" : "EMPTY";
    //                 }
    //                 array_push(
    //                     $detailPranotaList,
    //                     array(
    //                         "lineNumber" => $v->line_number,
    //                         "description" => $v->keterangan,
    //                         "flagTax" => "Y",
    //                         "componentCode" => $v->keterangan,
    //                         "componentName" => $v->keterangan,
    //                         "startDate" => $v->awal_penumpukan,
    //                         "endDate" => $v->akhir_penumpukan,
    //                         "quantity" => $v->jml_cont,
    //                         "tarif" => $v->tarif,
    //                         "basicTarif" => $v->tarif,
    //                         "containerList" => $strContList,
    //                         "containerSize" => $fetchContainerStuffing[0]['SIZE_'],
    //                         "containerType" => $fetchContainerStuffing[0]['TYPE_'],
    //                         "containerStatus" => $status,
    //                         "containerHeight" => "8.5",
    //                         "hz" => empty($v->hz) ? 'N' : $v->hz,
    //                         "ei" => "I",
    //                         "equipment" => "",
    //                         "strStartDate" => $v->awal_penumpukan,
    //                         "strEndDate" => $v->akhir_penumpukan,
    //                         "days" => "4", //TGL_END - TGL_START INTERVAL 4 HARI
    //                         "amount" => $v->biaya,
    //                         "via" => "YARD",
    //                         "package" => "",
    //                         "unit" => "BOX",
    //                         "qtyLoading" => "",
    //                         "qtyDischarge" => "",
    //                         "equipmentName" => "",
    //                         "duration" => "",
    //                         "flagTool" => "N",
    //                         "itemCode" => "",
    //                         "oog" => "N",
    //                         "imo" => "",
    //                         "blNumber" => empty($fetchStuffing->no_bl) ? '' : $fetchStuffing->no_bl,
    //                         "od" => "N",
    //                         "dg" => "N",
    //                         "sling" => "N",
    //                         "changeDate" => $v->tglnota,
    //                         "changeBy" => "Admin Uster"
    //                     )
    //                 );
    //             }
    //         } else {
    //             $notes = "Payment Cash - " . $jenis . " - STUFFING BUKAN DARI TPK";
    //             $response_uster_save = array(
    //                 'code' => "0",
    //                 'msg' => "Asal Stuffing Bukan Dari TPK"
    //             );
    //             $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //             return json_encode($response_uster_save);
    //         }
    //     } elseif ($jenis == 'DELIVERY') {
    //         $fetchDelivery = DB::connection('uster')->selectOne($queryDelivery);

    //         //IF DELIVERY KE TPK
    //         if ($fetchDelivery->delivery_ke == 'TPK') {

    //             // UPDATE BY CHOSSY PRATAMA
    //             $queryContainerDelivery =
    //                 "SELECT cd.*, mc.*, TO_CHAR(cd.START_STACK,'YYYY-MM-DD HH24:MI:SS') AWAL_PENUMPUKAN, TO_CHAR(cd.TGL_DELIVERY,'YYYY-MM-DD HH24:MI:SS') AKHIR_PENUMPUKAN FROM CONTAINER_DELIVERY cd JOIN MASTER_CONTAINER mc ON cd.NO_CONTAINER = mc.NO_CONTAINER WHERE cd.NO_REQUEST = '$id_req'";
    //             // END UPDATE
    //             $fetchContainerDelivery = DB::connection('uster')->select($queryContainerDelivery);
    //             $queryNotaDelivery =
    //                 "SELECT nd.*, ndd.*, (SELECT STATUS FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) STATUS, (SELECT SIZE_ FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) SIZE_, (SELECT TYPE_ FROM ISO_CODE ic WHERE ic.ID_ISO = ndd.ID_ISO) TYPE_, TO_CHAR(ndd.START_STACK,'YYYY-MM-DD HH24:MI:SS') AWAL_PENUMPUKAN, TO_CHAR(ndd.END_STACK,'YYYY-MM-DD HH24:MI:SS') AKHIR_PENUMPUKAN FROM NOTA_DELIVERY nd
    //         JOIN NOTA_DELIVERY_D ndd ON
    //         ndd.ID_NOTA = nd.NO_NOTA WHERE nd.NO_REQUEST = '$id_req'";
    //             $fetchNotaDelivery = DB::connection('uster')->select($queryNotaDelivery);
    //             $queryGetAdmin =
    //                 "SELECT TARIF FROM NOTA_DELIVERY nd
    //         JOIN NOTA_DELIVERY_D ndd ON
    //         ndd.ID_NOTA = nd.NO_NOTA WHERE nd.NO_REQUEST = '$id_req' AND ndd.ID_ISO = 'ADM' ";
    //             $adminComponent = DB::connection('uster')->selectOne($queryGetAdmin);

    //             $get_vessel = $this->getVessel($fetchDelivery->nm_kapal, $fetchDelivery->voyage, $fetchDelivery->voyage_in, $fetchDelivery->voyage_out);

    //             // $get_container_list = getContainer(NULL, $fetchDelivery->kd_kapal, $fetchDelivery->voyage_in, $fetchDelivery->voyage_out, $fetchDelivery->voyage);

    //             $get_iso_code = $this->getIsoCode();

    //             if (empty($get_iso_code)) {
    //                 $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
    //                 $response_uster_save = array(
    //                     'code' => "0",
    //                     'msg' => "Gagal mengambil Iso Code ke Praya"
    //                 );
    //                 $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //                 return json_encode($response_uster_save);
    //             }

    //             $pelabuhan_asal = $fetchDelivery->kd_pelabuhan_asal;
    //             $pelabuhan_tujuan = $fetchDelivery->kd_pelabuhan_tujuan;

    //             $idRequest = $id_req;
    //             $trxNumber = $fetchDelivery->no_nota;
    //             $paymentDate = $fetchDelivery->tglnota;
    //             $invoiceNumber = $fetchDelivery->no_faktur_mti;
    //             $requestType = 'RECEIVING';
    //             $parentRequestId = $id_req;
    //             $parentRequestType = 'RECEIVING';
    //             $serviceCode = 'REC';
    //             $vesselId = $fetchDelivery->kd_kapal; //
    //             $vesselName = $fetchDelivery->nm_kapal; //
    //             $voyage = empty($fetchDelivery->voyage) ? '' : $fetchDelivery->voyage; //
    //             $voyageIn = empty($fetchDelivery->voyage_in) ? '' : $fetchDelivery->voyage_in; //
    //             $voyageOut = empty($fetchDelivery->voyage_out) ? '' : $fetchDelivery->voyage_out; //
    //             $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut; //
    //             $eta = empty($get_vessel['eta']) ? '' : $get_vessel['eta'];
    //             $etb = empty($get_vessel['etb']) ? '' : $get_vessel['etb'];
    //             $etd = empty($get_vessel['etd']) ? '' : $get_vessel['etd'];
    //             $ata = empty($get_vessel['ata']) ? '' : $get_vessel['ata'];
    //             $atb = empty($get_vessel['atb']) ? '' : $get_vessel['atb'];
    //             $atd = empty($get_vessel['atd']) ? '' : $get_vessel['atd'];
    //             $startWork = empty($get_vessel['start_work']) ? '' : $get_vessel['start_work'];
    //             $endWork = empty($get_vessel['end_work']) ? '' : $get_vessel['end_work'];
    //             $pol = $pelabuhan_asal;
    //             $pod = $pelabuhan_tujuan;
    //             $fpod = $pelabuhan_tujuan;
    //             $dischargeDate = $get_vessel['discharge_date'];
    //             $shippingLineName = $fetchDelivery->nm_agen; //
    //             $customerCode = $fetchDelivery->kd_pelanggan; //
    //             $customerCodeOwner = '';
    //             $customerName = $fetchDelivery->emkl; //KD_EMKL
    //             $customerAddress = $fetchDelivery->alamat; //ALAMAT EMKL
    //             $npwp = $fetchDelivery->npwp; //
    //             $blNumber = '';
    //             $bookingNo = $fetchDelivery->no_booking; //
    //             $deliveryDate = '';
    //             $doNumber = $fetchDelivery->no_booking;  //
    //             // $doDate = '';
    //             $tradeType = $fetchDelivery->di == 'D' ? 'I' : 'O';
    //             $customsDocType = "";
    //             $customsDocNo = "";
    //             $customsDocDate = "";
    //             if ((int)$fetchDelivery->total_tagihan > 5000000) {
    //                 $amount = (int)$fetchDelivery->total_tagihan + 10000;
    //             } else {
    //                 (int)$amount = $fetchDelivery->total_tagihan;
    //             }
    //             if ($adminComponent) {
    //                 $administration = $adminComponent->tarif;
    //             }
    //             if (empty($fetchDelivery->ppn)) {
    //                 $ppn =  'N';
    //             } else {
    //                 $ppn = 'Y';
    //             };
    //             $amountPpn  = (int)$fetchDelivery->ppn; //
    //             $amountDpp = (int)$fetchDelivery->tagihan; //
    //             if ($fetchDelivery->tagihan > 5000000) {
    //                 $amountMaterai = 10000;
    //             } else {
    //                 $amountMaterai = 0;
    //             } //
    //             $approvalDate = empty($fetchDelivery->tglapprove) ? '' : $fetchDelivery->tglapprove;
    //             $status = 'PAID';
    //             $changeDate = $fetchDelivery->tglnota;
    //             $charge = 'Y';

    //             $detailList = array();
    //             $containerList = array();
    //             foreach ($fetchContainerDelivery as $k => $v) {
    //                 $container_status = $v->status == 'FCL' ? 'FULL' : 'EMPTY';

    //                 $cont = $v->no_container;

    //                 $reslt = array();
    //                 foreach ($get_iso_code as $key => $value) {
    //                     if (strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_)) {
    //                         array_push($reslt, $value);
    //                     }
    //                 }

    //                 $array_iso_code = array_values($reslt);
    //                 $new_iso = $this->mapNewIsoCode($array_iso_code[0]["isoCode"]);

    //                 array_push($containerList, $v->no_container);
    //                 array_push(
    //                     $detailList,
    //                     array(
    //                         "detailDescription" => "CONTAINER",
    //                         "containerNo" => $v->no_container,
    //                         "containerSize" => $v->size_,
    //                         "containerType" => $v->type_,
    //                         "containerStatus" => $container_status,
    //                         "containerHeight" => "8.5", //hardcode
    //                         "hz" => empty($v->hz) ? 'N' : $v->hz,
    //                         "imo" => "N",
    //                         "unNumber" => "",
    //                         "reeferNor" => "N",
    //                         "temperatur" => "",
    //                         "ow" => "",
    //                         "oh" => "",
    //                         "ol" => "",
    //                         "overLeft" => "",
    //                         "overRight" => "",
    //                         "overFront" => "",
    //                         "overBack" => "",
    //                         "weight" => $v->berat,
    //                         "commodityCode" => trim($v->komoditi, " "),
    //                         "commodityName" => trim($v->komoditi, " "),
    //                         "carrierCode" => $fetchDelivery->kd_agen,
    //                         "carrierName" => $fetchDelivery->nm_agen,
    //                         "isoCode" => $new_iso,
    //                         "plugInDate" => "",
    //                         "plugOutDate" => "",
    //                         "ei" => "E",
    //                         "dischLoad" => "",
    //                         "flagOog" => "N",
    //                         // UPDATE BY CHOSSY PRATAMA
    //                         "gateInDate" => $v->awal_penumpukan,
    //                         "gateOutDate" => $v->akhir_penumpukan,
    //                         "startDate" => $v->awal_penumpukan,
    //                         "endDate" => $v->akhir_penumpukan,
    //                         // END UPDATE
    //                         "containerDeliveryDate" => "",
    //                         "containerLoadingDate" => "",
    //                         "containerDischargeDate" => "",
    //                     )
    //                 );
    //             }

    //             $strContList = implode(", ", $containerList);
    //             $detailPranotaList = array();
    //             foreach ($fetchNotaDelivery as $k => $v) {
    //                 // Menghilangkan nota materai
    //                 if ($v->keterangan == "MATERAI") {
    //                     continue;
    //                 }
    //                 // Pemisahan Container Stack yg muncul di container listnya (edited by Chossy PIP (11962624) - Tonus)
    //                 if ($v->start_stack) {
    //                     $newContainerList = array();
    //                     foreach ($fetchContainerDelivery as $kContainer => $vContainer) {
    //                         if ($vContainer->start_stack == $v->start_stack && $vContainer->tgl_delivery == $v->end_stack && $vContainer->status == $v->status && $vContainer->size_ == $v->size_ && $vContainer->type_ == $v->type_) {
    //                             array_push($newContainerList, $vContainer->no_container);
    //                         }
    //                     }
    //                     $newStrContList = implode(", ", $newContainerList);
    //                 } else {
    //                     $newStrContList = $strContList;
    //                 }
    //                 $status = "";
    //                 if (!empty($v->status) && $v->status != "-") {
    //                     $status = $v->status == "FCL" ? "FULL" : "EMPTY";
    //                 }
    //                 $type = $v->type_ == "-" ? "" : $v->type_;
    //                 $size = $v->size_ == "-" ? "" : $v->size_;
    //                 array_push(
    //                     $detailPranotaList,
    //                     array(
    //                         "lineNumber" => $v->line_number,
    //                         "description" => $v->keterangan,
    //                         "flagTax" => "Y",
    //                         "componentCode" => $v->keterangan,
    //                         "componentName" => $v->keterangan,
    //                         "startDate" => $v->awal_penumpukan,
    //                         "endDate" => $v->akhir_penumpukan,
    //                         "quantity" => $v->jml_cont,
    //                         "quantity" => '1',
    //                         "tarif" => $v->tarif,
    //                         "basicTarif" => $v->tarif,
    //                         "containerList" => $newStrContList,
    //                         "containerSize" => $size,
    //                         "containerType" => $type,
    //                         "containerStatus" => $status,
    //                         "containerHeight" => "8.5",
    //                         "hz" => $v->hz,
    //                         "ei" => "I",
    //                         "equipment" => "",
    //                         "strStartDate" => $v->awal_penumpukan,
    //                         "strEndDate" => $v->akhir_penumpukan,
    //                         "days" => "4", //TGL_END - TGL_START INTERVAL 4 HARI
    //                         "amount" => $v->biaya,
    //                         "via" => "YARD",
    //                         "package" => "",
    //                         "unit" => "BOX",
    //                         "qtyLoading" => "",
    //                         "qtyDischarge" => "",
    //                         "equipmentName" => "",
    //                         "duration" => "",
    //                         "flagTool" => "N",
    //                         "itemCode" => "",
    //                         "oog" => "N",
    //                         "imo" => "",
    //                         "blNumber" => "",
    //                         "od" => "N",
    //                         "dg" => "N",
    //                         "sling" => "N",
    //                         "changeDate" => $fetchDelivery->tglnota,
    //                         "changeBy" => "Admin Uster"
    //                     )
    //                 );
    //             }
    //         } else {
    //             $notes = "Payment Cash - " . $jenis . " - DELIVERY BUKAN KE TPK";
    //             $response_uster_save = array(
    //                 'code' => "0",
    //                 'msg' => "Tujuan Delivery bukan menuju TPK"
    //             );
    //             $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //             return json_encode($response_uster_save);
    //         }
    //     } elseif ($jenis == 'BATAL_MUAT') {

    //         if ($charge == "N") {
    //             $fetchExDelivery = DB::connection('uster')->selectOne($queryDelivery);
    //             if ($fetchExDelivery->delivery_ke == 'TPK') {

    //                 // NOTA TIDAK ADA UNTUK BATAL MUAT TANPA BAYAR
    //                 // $queryNotaExDelivery =
    //                 //   "SELECT * FROM NOTA_DELIVERY nd
    //                 // JOIN NOTA_DELIVERY_D ndd ON
    //                 // ndd.ID_NOTA = nd.NO_NOTA WHERE nd.NO_REQUEST = '$id_req'";
    //                 // $fetchNotaExDelivery = $db->query($queryNotaExDelivery)->getAll();
    //                 // $queryGetAdmin =
    //                 //   "SELECT TARIF FROM NOTA_DELIVERY nd
    //                 // JOIN NOTA_DELIVERY_D ndd ON
    //                 // ndd.ID_NOTA = nd.NO_NOTA WHERE nd.NO_REQUEST = '$id_req' AND ndd.ID_ISO = 'ADM' ";
    //                 // $adminComponent = $db->query($queryGetAdmin)->fetchRow();

    //                 $get_vessel = $this->getVessel($payloadBatalMuat->vesselname, $payloadBatalMuat->voyage, $payloadBatalMuat->voyagein, $payloadBatalMuat->voyageout);

    //                 $get_iso_code = $this->getIsoCode();

    //                 if (empty($get_iso_code)) {
    //                     $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
    //                     $response_uster_save = array(
    //                         'code' => "0",
    //                         'msg' => "Gagal mengambil Iso Code ke Praya"
    //                     );
    //                     $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //                     return json_encode($response_uster_save);
    //                 }

    //                 $pelabuhan_asal = $payloadBatalMuat->pelabuhan_asal;
    //                 $pelabuhan_tujuan = $payloadBatalMuat->pelabuhan_tujuan;

    //                 $idRequest = $id_req;
    //                 $trxNumber = "";
    //                 $paymentDate = "";
    //                 $invoiceNumber = ""; //NO CHARGE KOSONG
    //                 $requestType = 'LOADING CANCEL - BEFORE GATEIN';
    //                 $parentRequestId = "";
    //                 $parentRequestType = 'LOADING CANCEL - BEFORE GATEIN';
    //                 $serviceCode = 'LCB';
    //                 $jenisBM = "alih_kapal";
    //                 $vesselId = $payloadBatalMuat["vesselId"];
    //                 $vesselName = $payloadBatalMuat["vesselName"];
    //                 $voyage = $payloadBatalMuat->voyage; //
    //                 $voyageIn = $payloadBatalMuat->voyagein; //
    //                 $voyageOut = $payloadBatalMuat->voyageout; //
    //                 $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut; //
    //                 $eta = empty($get_vessel['eta']) ? '' : $get_vessel['eta'];
    //                 $etb = empty($get_vessel['etb']) ? '' : $get_vessel['etb'];
    //                 $etd = empty($get_vessel['etd']) ? '' : $get_vessel['etd'];
    //                 $ata = empty($get_vessel['ata']) ? '' : $get_vessel['ata'];
    //                 $atb = empty($get_vessel['atb']) ? '' : $get_vessel['atb'];
    //                 $atd = empty($get_vessel['atd']) ? '' : $get_vessel['atd'];
    //                 $startWork = empty($get_vessel['start_work']) ? '' : $get_vessel['start_work'];
    //                 $endWork = empty($get_vessel['end_work']) ? '' : $get_vessel['end_work'];
    //                 $pol = $pelabuhan_asal;
    //                 $pod = $pelabuhan_tujuan;
    //                 $dischargeDate = $get_vessel['discharge_date'];
    //                 $shippingLineName = $payloadBatalMuat->nm_agen; //
    //                 $customerCode = $fetchExDelivery->kd_pelanggan; //
    //                 $customerCodeOwner = '';
    //                 $customerName = $fetchExDelivery->emkl; //
    //                 $customerAddress = $fetchExDelivery->alamat; //
    //                 $npwp = $fetchExDelivery->npwp; //
    //                 $blNumber = "";
    //                 $bookingNo = $fetchExDelivery->no_booking;
    //                 $deliveryDate = '';
    //                 $doNumber = "";
    //                 // $doDate = '';
    //                 $tradeType = $fetchExDelivery->di; //Value : I / O
    //                 $customsDocType = "";
    //                 $customsDocNo = "";
    //                 $customsDocDate = "";
    //                 $amount = 0;
    //                 $administration = 0;
    //                 if (empty($fetchExDelivery->ppn)) {
    //                     $ppn =  'N';
    //                 } else {
    //                     $ppn = 'Y';
    //                 };
    //                 $amountPpn  = 0;
    //                 $amountDpp = 0;
    //                 $amountMaterai = 0;
    //                 $approvalDate = empty($fetchExDelivery->tglapprove) ? '' : $fetchExDelivery->tglapprove;
    //                 $status = 'PAID';
    //                 $changeDate = $fetchExDelivery->tglnota;
    //                 $charge = 'N';

    //                 $detailList = array();
    //                 $containerList = $payloadBatalMuat->cont_list;
    //                 foreach ($payloadBatalMuat->cont_list as $no_cont) {
    //                     $queryContainerExDelivery =
    //                         "SELECT cd.NO_CONTAINER, cd.KOMODITI, mc.SIZE_, mc.TYPE_, mc.NO_BOOKING, vpc.KD_KAPAL, vpc.VOYAGE, vpc.VOYAGE_IN, vpc.VOYAGE_OUT FROM CONTAINER_DELIVERY cd JOIN MASTER_CONTAINER mc ON cd.NO_CONTAINER = mc.NO_CONTAINER JOIN V_PKK_CONT vpc ON mc.NO_BOOKING = vpc.NO_BOOKING WHERE cd.NO_CONTAINER = '$no_cont'";
    //                     $fetchContainerExDelivery = DB::connection('uster')->selectOne($queryContainerExDelivery);

    //                     $get_container_list = $this->getContainer($no_cont, $fetchContainerExDelivery->kd_kapal, $fetchContainerExDelivery->voyage_in, $fetchContainerExDelivery->voyage_out, $fetchContainerExDelivery->voyage, NULL, NULL);
    //                     // $array_iso_code = array_values(array_filter($get_iso_code, function ($value) use ($v) {
    //                     //     return strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_);
    //                     // }));

    //                     $reslt = array();
    //                     foreach ($get_iso_code as $key => $value) {
    //                         if (strtoupper($value['type']) == strtoupper($fetchContainerExDelivery->type_) && strtoupper($value['size']) == strtoupper($fetchContainerExDelivery->size_)) {
    //                             array_push($reslt, $value);
    //                         }
    //                     }

    //                     $array_iso_code = array_values($reslt);
    //                     $new_iso = $this->mapNewIsoCode($array_iso_code[0]["isoCode"]);

    //                     // echo json_encode($array_iso_code);
    //                     array_push(
    //                         $detailList,
    //                         array(
    //                             "detailDescription" => "CONTAINER",
    //                             "containerNo" => $fetchContainerExDelivery->no_container,
    //                             "containerSize" => $fetchContainerExDelivery->size_,
    //                             "containerType" => $fetchContainerExDelivery->type_,
    //                             "containerStatus" => "FULL",
    //                             "containerHeight" => "8.5",
    //                             "hz" => empty($fetchContainerExDelivery->hz) ? (empty($get_container_list[0]['hz']) ? 'N' : $get_container_list[0]['hz']) : "N",
    //                             "imo" => "N",
    //                             "unNumber" => empty($get_container_list[0]['unNumber']) ? '' : $get_container_list[0]['unNumber'],
    //                             "reeferNor" => "N",
    //                             "temperatur" => "",
    //                             "ow" => "",
    //                             "oh" => "",
    //                             "ol" => "",
    //                             "overLeft" => "",
    //                             "overRight" => "",
    //                             "overFront" => "",
    //                             "overBack" => "",
    //                             "weight" => "",
    //                             "commodityCode" => trim($fetchContainerExDelivery->komoditi, " "),
    //                             "commodityName" => trim($fetchContainerExDelivery->komoditi, " "),
    //                             "carrierCode" => $payloadBatalMuat->kd_agen,
    //                             "carrierName" => $payloadBatalMuat->nm_agen,
    //                             "isoCode" => $new_iso,
    //                             "plugInDate" => "",
    //                             "plugOutDate" => "",
    //                             "ei" => "E",
    //                             "dischLoad" => "",
    //                             "flagOog" => empty($get_container_list[0]['flagOog']) ? '' : $get_container_list[0]['flagOog'],
    //                             "gateInDate" => "",
    //                             "gateOutDate" => "",
    //                             "startDate" => "",
    //                             "endDate" => "",
    //                             "containerDeliveryDate" => "",
    //                             "containerLoadingDate" => "",
    //                             "containerDischargeDate" => "",
    //                         )
    //                     );
    //                 }
    //             } else {
    //                 $notes = "Payment Cash - " . $jenis . " - BUKAN EX KEGIATAN REPO";
    //                 $response_uster_save = array(
    //                     'code' => "0",
    //                     'msg' => "Nota Batal Muat bukan Ex Kegiatan Repo (Status Gate 2)"
    //                 );
    //                 $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //                 return json_encode($response_uster_save);
    //             }
    //         } else {
    //             $queryBatalMuat = "SELECT
    //         --	rbm.*,
    //         rbm.NO_REQUEST, --REQUIRED
    //         rbm.KD_EMKL, --UNTUK LOG
    //         rbm.JENIS_BM, --hanya yg alih_kapal
    //         rbm.KAPAL_TUJU, --NO_BOOKING
    //         rbm.STATUS_GATE, --IF STATUS GATE 2
    //         rbm.NO_REQ_BARU,
    //         rbm.O_VESSEL,
    //         rbm.BIAYA,
    //         -- rbm.O_VOYIN,
    //         -- rbm.O_VOYOUT,
    //         rbm.DI,
    //         -- nbm.*,
    //         nbm.NO_NOTA,
    //         nbm.NO_FAKTUR_MTI,
    //         nbm.EMKL,
    //         nbm.ALAMAT,
    //         nbm.NPWP,
    //         nbm.TAGIHAN,
    //         nbm.TOTAL_TAGIHAN,
    //         nbm.STATUS,
    //         nbm.PPN,
    //         TO_CHAR(nbm.TGL_NOTA ,'YYYY-MM-DD HH24:MI:SS') TGLNOTA,
    //         vpc.VOYAGE,
    //         vpc.VOYAGE_IN,
    //         vpc.VOYAGE_OUT,
    //         vpc.PELABUHAN_ASAL,
    //         vpc.PELABUHAN_TUJUAN,
    //         vpc.NM_AGEN,
    //         vpc.KD_AGEN,
    //         vpc.NM_KAPAL,
    //         vpc.KD_KAPAL,
    //         vmp.NO_ACCOUNT_PBM KD_PELANGGAN,
    //         cbm.NO_REQ_BATAL
    //         FROM
    //         REQUEST_BATAL_MUAT rbm
    //         LEFT JOIN V_PKK_CONT vpc ON
    //         rbm.KAPAL_TUJU = vpc.NO_BOOKING
    //         LEFT JOIN NOTA_BATAL_MUAT nbm ON
    //         nbm.NO_REQUEST = rbm.NO_REQUEST
    //         JOIN V_MST_PBM vmp ON
    //             vmp.KD_PBM = rbm.KD_EMKL
    //         JOIN CONTAINER_BATAL_MUAT cbm ON
    //         cbm.NO_REQUEST = rbm.NO_REQUEST
    //         WHERE rbm.NO_REQUEST = '$id_req'";

    //             $fetchBatalMuat = DB::connection('uster')->selectOne($queryBatalMuat);

    //             if ($fetchBatalMuat->status_gate == '2' && $fetchBatalMuat->jenis_bm == 'alih_kapal') {
    //                 $queryContainerBatalMuat =
    //                     "SELECT * FROM CONTAINER_BATAL_MUAT cbm JOIN MASTER_CONTAINER mc ON cbm.NO_CONTAINER = mc.NO_CONTAINER WHERE cbm.NO_REQUEST = '$id_req'";
    //                 $fetchContainerBatalMuat = DB::connection('uster')->select($queryContainerBatalMuat);
    //                 $queryNotaBatalMuat =
    //                     "SELECT nbm.*, nbmd.*, TO_CHAR(nbm.TGL_NOTA,'YYYY-MM-DD HH24:MI:SS') TGLNOTA FROM NOTA_BATAL_MUAT nbm JOIN NOTA_BATAL_MUAT_D nbmd ON nbm.NO_NOTA = nbmd.ID_NOTA WHERE nbm.NO_REQUEST = '$id_req'";
    //                 $fetchNotaBatalMuat = DB::connection('uster')->select($queryNotaBatalMuat);
    //                 $queryGetAdmin =
    //                     "SELECT TARIF FROM NOTA_BATAL_MUAT nbm JOIN NOTA_BATAL_MUAT_D nbmd ON nbm.NO_NOTA = nbmd.ID_NOTA WHERE nbm.NO_REQUEST = '$id_req' AND nbmd.ID_ISO = 'ADM' ";
    //                 $adminComponent = DB::connection('uster')->selectOne($queryGetAdmin);

    //                 $get_vessel = $this->getVessel($fetchBatalMuat->nm_kapal, $fetchBatalMuat->voyage, $fetchBatalMuat->voyage_in, $fetchBatalMuat->voyage_out);

    //                 $get_container_list = $this->getContainer(NULL, $fetchBatalMuat->kd_kapal, $fetchBatalMuat->voyage_in, $fetchBatalMuat->voyage_out, $fetchBatalMuat->voyage, "E", "LCB");

    //                 $get_iso_code = $this->getIsoCode();

    //                 if (empty($get_iso_code)) {
    //                     $notes = "Payment Cash - " . $jenis . " - GAGAL GET ISO CODE";
    //                     $response_uster_save = array(
    //                         'code' => "0",
    //                         'msg' => "Gagal mengambil Iso Code ke Praya"
    //                     );
    //                     $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //                     return json_encode($response_uster_save);
    //                 }

    //                 // echo json_encode($get_vessel . ' <<getvessel');
    //                 // echo json_encode($get_container_list . ' <<getcontainerlist');
    //                 // die();

    //                 $pelabuhan_asal = $fetchBatalMuat->pelabuhan_asal;
    //                 $pelabuhan_tujuan = $fetchBatalMuat->pelabuhan_tujuan;

    //                 $idRequest = $id_req;
    //                 $trxNumber = $fetchBatalMuat->no_nota;
    //                 $paymentDate = $fetchBatalMuat->tglnota;
    //                 $invoiceNumber = $fetchBatalMuat->no_faktur_mti;
    //                 $requestType = 'LOADING CANCEL - BEFORE GATEIN';
    //                 $parentRequestId = $fetchBatalMuat->no_req_batal;
    //                 $parentRequestType = 'LOADING CANCEL - BEFORE GATEIN';
    //                 $serviceCode = 'LCB';
    //                 $jenisBM = $fetchBatalMuat->jenis_bm;
    //                 $vesselId = $fetchBatalMuat->kd_kapal; //
    //                 $vesselName = $fetchBatalMuat->nm_kapal; //
    //                 $voyage = empty($fetchBatalMuat->voyage) ? '' : $fetchBatalMuat->voyage; //
    //                 $voyageIn = empty($fetchBatalMuat->voyage_in) ? '' : $fetchBatalMuat->voyage_in; //
    //                 $voyageOut = empty($fetchBatalMuat->voyage_out) ? '' : $fetchBatalMuat->voyage_out; //
    //                 $voyageInOut = empty($voyageIn) || empty($voyageOut) ? '' : $voyageIn . '/' . $voyageOut; //
    //                 $eta = empty($get_vessel['eta']) ? '' : $get_vessel['eta'];
    //                 $etb = empty($get_vessel['etb']) ? '' : $get_vessel['etb'];
    //                 $etd = empty($get_vessel['etd']) ? '' : $get_vessel['etd'];
    //                 $ata = empty($get_vessel['ata']) ? '' : $get_vessel['ata'];
    //                 $atb = empty($get_vessel['atb']) ? '' : $get_vessel['atb'];
    //                 $atd = empty($get_vessel['atd']) ? '' : $get_vessel['atd'];
    //                 $startWork = empty($get_vessel['start_work']) ? '' : $get_vessel['start_work'];
    //                 $endWork = empty($get_vessel['end_work']) ? '' : $get_vessel['end_work'];
    //                 $pol = $pelabuhan_asal;
    //                 $pod = $pelabuhan_tujuan;
    //                 $dischargeDate = $get_vessel['discharge_date'];
    //                 $shippingLineName = $fetchBatalMuat->nm_agen; //
    //                 $customerCode = $fetchBatalMuat->kd_pelanggan; //
    //                 $customerCodeOwner = '';
    //                 $customerName = $fetchBatalMuat->emkl; //
    //                 $customerAddress = $fetchBatalMuat->alamat; //
    //                 $npwp = $fetchBatalMuat->npwp; //
    //                 $blNumber = "";
    //                 $bookingNo = $fetchBatalMuat->kapal_tuju;
    //                 $deliveryDate = '';
    //                 $doNumber = "";
    //                 // $doDate = '';
    //                 $tradeType = $fetchBatalMuat->di; //Value : I / O
    //                 $customsDocType = "";
    //                 $customsDocNo = "";
    //                 $customsDocDate = "";
    //                 if ((int)$fetchBatalMuat->total_tagihan > 5000000) {
    //                     $amount = (int)$fetchBatalMuat->total_tagihan + 10000;
    //                 } else {
    //                     (int)$amount = $fetchBatalMuat->total_tagihan;
    //                 }
    //                 if ($adminComponent) {
    //                     $administration = $adminComponent->tarif;
    //                 }
    //                 if (empty($fetchBatalMuat->ppn)) {
    //                     $ppn =  'N';
    //                 } else {
    //                     $ppn = 'Y';
    //                 };
    //                 $amountPpn  = (int)$fetchBatalMuat->ppn;
    //                 $amountDpp = (int)$fetchBatalMuat->tagihan;
    //                 if ($fetchBatalMuat->tagihan > 5000000) {
    //                     $amountMaterai = 10000;
    //                 } else {
    //                     $amountMaterai = 0;
    //                 }
    //                 $approvalDate = empty($fetchBatalMuat->tglapprove) ? '' : $fetchBatalMuat->tglapprove;
    //                 $status = 'PAID';
    //                 $changeDate = $fetchBatalMuat->tglnota;
    //                 $charge = 'Y';

    //                 $detailList = array();
    //                 $containerList = array();

    //                 foreach ($fetchContainerBatalMuat as $k => $v) {
    //                     foreach ($get_container_list as $k_container => $v_container) {
    //                         if ($v_container->containerno  == $v->no_container) {
    //                             $_get_container = $v_container;
    //                             break;
    //                         }
    //                     }
    //                     // $array_iso_code = array_values(array_filter($get_iso_code, function ($value) use ($v) {
    //                     //     return strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_);
    //                     // }));

    //                     $reslt = array();
    //                     foreach ($get_iso_code as $key => $value) {
    //                         if (strtoupper($value['type']) == strtoupper($v->type_) && strtoupper($value['size']) == strtoupper($v->size_)) {
    //                             array_push($reslt, $value);
    //                         }
    //                     }

    //                     $array_iso_code = array_values($reslt);
    //                     $new_iso = $this->mapNewIsoCode($array_iso_code[0]["isoCode"]);

    //                     array_push($containerList, $v->no_container);
    //                     array_push(
    //                         $detailList,
    //                         array(
    //                             "detailDescription" => "CONTAINER",
    //                             "containerNo" => $v->no_container,
    //                             "containerSize" => $v->size_,
    //                             "containerType" => $v->type_,
    //                             "containerStatus" => "FULL",
    //                             "containerHeight" => "8.5",
    //                             "hz" => empty($v->hz) ? (empty($_get_container['hz']) ? 'N' : $_get_container['hz']) : $v->hz,
    //                             "imo" => "N",
    //                             "unNumber" => empty($_get_container['unNumber']) ? '' : $_get_container['unNumber'],
    //                             "reeferNor" => "N",
    //                             "temperatur" => "",
    //                             "ow" => "",
    //                             "oh" => "",
    //                             "ol" => "",
    //                             "overLeft" => "",
    //                             "overRight" => "",
    //                             "overFront" => "",
    //                             "overBack" => "",
    //                             "weight" => "",
    //                             "commodityCode" => trim($v->commodity, " "),
    //                             "commodityName" => trim($v->commodity, " "),
    //                             "carrierCode" => $fetchBatalMuat->kd_agen,
    //                             "carrierName" => $fetchBatalMuat->nm_agen,
    //                             "isoCode" => $new_iso,
    //                             "plugInDate" => "",
    //                             "plugOutDate" => "",
    //                             "ei" => "E",
    //                             "dischLoad" => "",
    //                             "flagOog" => empty($_get_container['flagOog']) ? '' : $_get_container['flagOog'],
    //                             "gateInDate" => "",
    //                             "gateOutDate" => "",
    //                             "startDate" => "",
    //                             "endDate" => "",
    //                             "containerDeliveryDate" => "",
    //                             "containerLoadingDate" => "",
    //                             "containerDischargeDate" => "",
    //                         )
    //                     );
    //                 }

    //                 $strContList = implode(", ", $containerList);
    //                 $detailPranotaList = array();
    //                 foreach ($fetchNotaBatalMuat as $k => $v) {

    //                     array_push(
    //                         $detailPranotaList,
    //                         array(
    //                             "lineNumber" => $v->line_number,
    //                             "description" => $v->keterangan,
    //                             "flagTax" => "Y",
    //                             "componentCode" => $v->keterangan,
    //                             "componentName" => $v->keterangan,
    //                             "startDate" => "",
    //                             "endDate" => "",
    //                             "quantity" => $v->jml_cont,
    //                             "tarif" => $v->tarif,
    //                             "basicTarif" => $v->tarif,
    //                             "containerList" => $strContList,
    //                             "containerSize" => $fetchContainerBatalMuat[0]['SIZE_'],
    //                             "containerType" => $fetchContainerBatalMuat[0]['TYPE_'],
    //                             "containerStatus" => "",
    //                             "containerHeight" => "8.5",
    //                             "hz" => empty($v->hz) ? "N" : $v->hz,
    //                             "ei" => "I",
    //                             "equipment" => "",
    //                             "strStartDate" => "",
    //                             "strEndDate" => "",
    //                             "days" => "1", //REQUEST DATE - REQUEST DATE
    //                             "amount" => $v->biaya,
    //                             "via" => "YARD",
    //                             "package" => "",
    //                             "unit" => "BOX",
    //                             "qtyLoading" => "",
    //                             "qtyDischarge" => "",
    //                             "equipmentName" => "",
    //                             "duration" => "",
    //                             "flagTool" => "N",
    //                             "itemCode" => "",
    //                             "oog" => "",
    //                             "imo" => "",
    //                             "blNumber" => "",
    //                             "od" => "N",
    //                             "dg" => "N",
    //                             "sling" => "N",
    //                             "changeDate" => $v->tglnota,
    //                             "changeBy" => "Admin Uster"
    //                         )
    //                     );
    //                 }
    //             } else {
    //                 $notes = "Payment Cash - " . $jenis . " - BUKAN EX KEGIATAN REPO";
    //                 $response_uster_save = array(
    //                     'code' => "0",
    //                     'msg' => "Nota Batal Muat bukan Ex Kegiatan Repo (Status Gate 2)"
    //                 );
    //                 $this->praya->insertPrayaServiceLog($url_uster_save, $payload_uster_save, $response_uster_save, $notes);

    //                 return json_encode($response_uster_save);
    //             }
    //         }
    //     }

    //     $payload_header = array(
    //         "PNK_REQUEST_ID" => $id_req,
    //         "PNK_NO_PROFORMA" => "",
    //         "PNK_CONTAINER_LIST" => $strContList,
    //         "PNK_JENIS_SERVICE" => $jenis,
    //         "PNK_JENIS_BATAL_MUAT" => $jenisBM,
    //         "PNK_PAYMENT_VIA" => $payment_via,
    //         "EBPP_CREATED_DATE" => $_POST["EBPP_CREATED_DATE"],
    //         "idRequest" => $idRequest,
    //         "billerId" => "00009",
    //         "trxNumber" => $trxNumber,
    //         "paymentDate" => $paymentDate,
    //         "invoiceNumber" => $invoiceNumber,
    //         "orgId" => (string)env('PRAYA_ITPK_PNK_ORG_ID'),
    //         "orgCode" => env('PRAYA_ITPK_PNK_ORG_CODE'),
    //         "terminalId" => (string)env('PRAYA_ITPK_PNK_TERMINAL_ID'),
    //         "terminalCode" => env('PRAYA_ITPK_PNK_TERMINAL_CODE'),
    //         "branchId" => (string)env('PRAYA_ITPK_PNK_BRANCH_ID'),
    //         "branchCode" => (string)env('PRAYA_ITPK_PNK_BRANCH_CODE'),
    //         "areaTerminal" => (string)env('PRAYA_ITPK_PNK_AREA_CODE'),
    //         "bankAccountNumber" => $bankAccountNumber,
    //         "administration" => $administration,
    //         "requestType" => $requestType,
    //         "parentRequestId" => $parentRequestId,
    //         "parentRequestType" => $parentRequestType,
    //         "serviceCode" => $serviceCode,
    //         "vesselId" => $vesselId,
    //         "vesselName" => $vesselName,
    //         "voyage" => $voyage,
    //         "voyageIn" => $voyageIn,
    //         "voyageOut" => $voyageOut,
    //         "voyageInOut" => $voyageInOut,
    //         "eta" => $eta,
    //         "etb" => $etb,
    //         "etd" => $etd,
    //         "ata" => $ata,
    //         "atb" => $atb,
    //         "atd" => $atd,
    //         "startWork" => $startWork,
    //         "endWork" => $endWork,
    //         "pol" => $pol,
    //         "pod" => $pod,
    //         "fpod" => $fpod,
    //         "dischargeDate" => $dischargeDate,
    //         "shippingLineName" => $shippingLineName,
    //         "customerCodeOwner" => $customerCodeOwner,
    //         "customerCode" => $customerCode,
    //         "customerName" => $customerName,
    //         "customerAddress" => $customerAddress,
    //         "npwp" => $npwp,
    //         "blNumber" => $blNumber,
    //         "bookingNo" => $bookingNo,
    //         "deliveryDate" => $deliveryDate,
    //         "via" => "YARD",
    //         "doNumber" => $doNumber,
    //         // "doDate" => $doDate,
    //         "tradeType" => $tradeType,
    //         "customsDocType" => $customsDocType,
    //         "customsDocNo" => $customsDocNo,
    //         "customsDocDate" => $customsDocDate,
    //         "amount" => $amount,
    //         "ppn" => $ppn,
    //         "amountPpn" => $amountPpn,
    //         "amountMaterai" => $amountMaterai,
    //         "amountDpp" => $amountDpp,
    //         "approval" => "Y",
    //         "approvalDate" => $approvalDate,
    //         "approvalBy" => "Admin Uster",
    //         "remarkReject" => "",
    //         "status" => "PAID",
    //         "changeBy" => "Admin Uster",
    //         "changeDate" => $changeDate,
    //         "charge" => $charge
    //     );


    //     if (!empty($paymentCode)) {
    //         $payment_code = array(
    //             "paymentCode" => $paymentCode
    //         );
    //         $payload_header = array_merge($payload_header, $payment_code);
    //     }

    //     $payload_body = array(
    //         "detailList" => $detailList,
    //         "detailPranotaList" => $detailPranotaList
    //     );

    //     $payload = array_merge($payload_header, $payload_body);

    //     $response_uster_save = $this->praya->sendDataFromUrlTryCatch($payload, $url_uster_save, 'POST', $this->praya->getTokenPraya());

    //     $notes = $jenis == "DELIVERY" ? "Payment Cash - " . $jenis . " EX REPO" : "Payment Cash - " . $jenis;

    //     // var_dump($response_uster_save);
    //     // echo "<<uster_save";

    //     $first_char_http_code = substr(strval($response_uster_save['httpCode']), 0, 1);

    //     if ($first_char_http_code == 5 || $first_char_http_code == 1) {
    //         echo "0";
    //         $decodedRes = json_decode($response_uster_save["response"]["msg"]) ? json_decode($response_uster_save["response"]["msg"]) : $response_uster_save["response"]["msg"];
    //         $defaultRes = "Service is Unavailable, please try again (HTTP Error Code : " . $response_uster_save["httpCode"] . ")";
    //         $response_uster_save_logging = array(
    //             "code" => "0",
    //             "msg" => $defaultRes,
    //             "response" => $decodedRes
    //         );
    //         echo "3";
    //         $this->praya->insertPrayaServiceLog($url_uster_save, $payload_header, $response_uster_save_logging, $notes);
    //         echo "2";

    //         return json_encode($response_uster_save_logging);
    //     }

    //     $response_uster_save_decode = json_decode($response_uster_save['response'], true);

    //     $response_uster_save_logging = $response_uster_save_decode["code"] == 0 ? array(
    //         "code" => $response_uster_save_decode['code'],
    //         "msg" => $response_uster_save_decode['msg']
    //     ) : $response_uster_save_decode;

    //     if (!empty($idRequest) && substr($idRequest, 0, 3) == "STP") {
    //         $payload_stp_logging = array(
    //             "PNK_REQUEST_ID" => $id_req,
    //             "PNK_NO_PROFORMA" => $_POST["NO_PROFORMA"],
    //             "PNK_CONTAINER_LIST" => $strContList,
    //             "PNK_JENIS_SERVICE" => $jenis,
    //             "PNK_JENIS_BATAL_MUAT" => $jenisBM,
    //             "PNK_PAYMENT_VIA" => $payment_via,
    //             "EBPP_CREATED_DATE" => $_POST["EBPP_CREATED_DATE"],
    //             "detailList" => $containerListLog
    //         );
    //         $notes_stp_logging = "STP - " . $idRequest . " - CONTAINER LOGGING";

    //         // LOGGING FOR PAYTHRU PERP_STRIP
    //         $this->praya->insertPrayaServiceLog($url_uster_save, $payload_stp_logging, $response_uster_save_logging, $notes_stp_logging);
    //     }

    //     $this->praya->insertPrayaServiceLog($url_uster_save, $payload_header, $response_uster_save_logging, $notes);

    //     if ($response_uster_save['response']['code'] == 0) {
    //         return json_encode($response_uster_save_logging);
    //     } else {
    //         return $response_uster_save['response'];
    //     }
    // }

    // function getVessel($vessel, $voy, $voyIn, $voyOut)
    // {

    //     $vessel = str_replace(" ", "+", $vessel);

    //     try {
    //         $url = env('PRAYA_API_TOS') . "/api/getVessel?pol=" . env('PRAYA_ITPK_PNK_PORT_CODE') . "&eta=1&etd=1&orgId=" . env('PRAYA_ITPK_PNK_ORG_ID') . "&terminalId=" . env('PRAYA_ITPK_PNK_TERMINAL_ID') . "&search=$vessel";
    //         $json = $this->praya->getDatafromUrl($url);
    //         $json = json_decode($json, true);

    //         // echo $voy . ' | ' . $voyIn . ' | ' . $voyOut;
    //         // echo json_encode($json);

    //         if ($json['code'] == 1) {
    //             $vessel_resp = '';
    //             foreach ($json['data'] as $k => $v) {
    //                 if ($v->voyage == $voy && $v->voyage_in == $voyIn && $v->voyage_out == $voyOut) {
    //                     $vessel_resp = $v;
    //                 }
    //             }
    //             // echo json_encode($vessel_resp);
    //             return $vessel_resp;
    //         } else {
    //             echo $json['msg'];
    //         }
    //     } catch (Exception $ex) {
    //         echo $ex->getMessage();
    //     }
    // }

    // function getContainer($no_container, $vessel_code, $voyage_in, $voyage_out, $voy, $ei, $serviceCode)
    // {

    //     // echo $no_container  . ' | ' .  $vessel_code  . ' | ' .  $voyage_in  . ' | ' .  $voyage_out  . ' | ' .  $voy;

    //     try {
    //         $payload = array(
    //             "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
    //             "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID'),
    //             "vesselId" => $vessel_code,
    //             "voyageIn" => $voyage_in,
    //             "voyageOut" => $voyage_out,
    //             "voyage" => $voy,
    //             "portCode" => env('PRAYA_ITPK_PNK_PORT_CODE'),
    //             "ei" => $ei,
    //             "containerNo" => $no_container,
    //             "serviceCode" => $serviceCode
    //         );

    //         $response = $this->praya->sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/containerList", 'POST', $this->praya->getTokenPraya());
    //         $response = json_decode($response['response'], true);

    //         if ($response['code'] == 1 && !empty($response["data"])) {
    //             return $response['data'];
    //         }
    //     } catch (Exception $ex) {
    //         echo $ex->getMessage();
    //     }
    // }

    // function getStuffingContainer($no_container)
    // {
    //     try {
    //         $payload = array(
    //             "orgId" => env('PRAYA_ITPK_PNK_ORG_ID'),
    //             "terminalId" => env('PRAYA_ITPK_PNK_TERMINAL_ID'),
    //             "containerNo" => $no_container
    //         );

    //         $response = $this->praya->sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/stuffingContainerList", 'POST', $this->praya->getTokenPraya());
    //         $response = json_decode($response['response'], true);

    //         if ($response['code'] == 1 && !empty($response["dataRec"])) {
    //             return $response['dataRec'];
    //         }
    //     } catch (Exception $ex) {
    //         echo $ex->getMessage();
    //     }
    // }

    // function getIsoCode()
    // {

    //     try {
    //         $searchFieldColumn = array(
    //             "size" => "",
    //             "type" => "",
    //             "height" => "",
    //         );

    //         $payload = array(
    //             "terminalCode" => env('PRAYA_ITPK_PNK_TERMINAL_CODE'),
    //             "searchFieldColumn" => $searchFieldColumn,
    //             "page" => 1,
    //             "record" => 1000
    //         );

    //         // echo json_encode($payload);
    //         // echo "<<payload";

    //         $response = $this->praya->sendDataFromUrl($payload, env('PRAYA_API_TOS') . "/api/isoCodeList", 'POST', $this->praya->getTokenPraya());
    //         $response = json_decode($response['response'], true);

    //         if ($response['code'] == 1 && !empty($response["dataRec"])) {
    //             return $response['dataRec'];
    //         }
    //     } catch (Exception $ex) {
    //         echo $ex->getMessage();
    //     }
    // }

    // function mapNewIsoCode($iso)
    // {
    //     $new_iso = "";

    //     switch ($iso) {
    //         case "42B0":
    //             $new_iso = "4500"; //DRY 40
    //             break;
    //         case "2650":
    //             $new_iso = "22U1"; //OT 20
    //             break;
    //         case "42U0":
    //             $new_iso = "45G1"; //OT 40
    //             break;
    //         case "4260":
    //             $new_iso = "45G1"; //FLT 40
    //             break;
    //         // Penambahan iso code baru untuk container 21ft (Chossy PIP (11962624))
    //         case "2280":
    //             $new_iso = "22G1"; //DRY 20
    //             break;
    //         // End Penambahan
    //         default:
    //             $new_iso = $iso;
    //     };

    //     return $new_iso;
    // }
}

class printNotaMTI
{
    private $img;
    public function __construct($img)
    {
        $this->img = $img;
    }

    function printNotaLunasRecMti($noreq)
    {
        global $no_request, $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;


        $query_get    = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA,a.TGL_NOTA_1 TGL_NOTA_1, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, c.NO_DO, c.NO_BL, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, inh.NO_NOTA_MTI, inh.NO_FAKTUR_MTI,inh.NO_PERATURAN,
	   CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, a.NIPP_USER, mu.NAMA_LENGKAP, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD-MM-RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_receiving a, request_receiving c, master_user mu, itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM NOTA_RECEIVING d WHERE d.NO_REQUEST = '$no_req' )
                            and c.NO_REQUEST = '$no_req'
							and a.nipp_user = mu.id(+)
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";

        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota        = $row_nota->no_faktur_;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '-';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap  = $row_nota->nama_lengkap;
        $no_do  = $row_nota->no_do;
        $no_bl  = $row_nota->no_bl;
        $nota_mti          = $row_nota->no_nota_mti;
        $faktur_mti      = $row_nota->no_faktur_mti;
        $tgl_nota_1     = $row_nota->tgl_nota_1;
        $no_mat         = $row_nota->no_peraturan;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));

        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
					  FROM nota_receiving_d a, nota_receiving b
					 WHERE a.no_nota = b.no_nota
					   AND b.no_request = '$no_req'
					   AND a.keterangan = 'MATERAI'";
        //    echo $query_mtr;die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }

        $query_get2    = "/* Formatted on 12/28/2012 1:46:36 PM (QP5 v5.163.1008.3004) */
						SELECT a.KETERANGAN,
							   a.JML_CONT,
							   a.JML_HARI,
							   b.SIZE_,
							   b.TYPE_,
							   b.STATUS,
							   a.HZ,
							   TO_CHAR (a.TARIF, '999,999,999,999') TARIF,
							   TO_CHAR (a.BIAYA, '999,999,999,999') BIAYA
						  FROM nota_receiving_d a, iso_code b, nota_receiving c
						 WHERE     a.ID_ISO = b.ID_ISO(+)
							   AND a.NO_NOTA = c.NO_NOTA
							   AND a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI') /*modify fauzan 23 September 2020*/
							   AND c.TGL_NOTA = (SELECT MAX (d.TGL_NOTA)
												   FROM NOTA_RECEIVING d
												  WHERE d.NO_REQUEST = '$no_req') ";
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA RECEIVING',
            'no_do' => $no_do,
            'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Receiving.pdf"',
        ]);
    }

    function printNotaLunasStripMti($noreq)
    {
        global $no_request;
        global $dt;
        global $no_nota;
        global $no_nota_;
        global $no_nota_mti;
        global $no_faktur_mti;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;
        //$dt      = date('d-M-Y H:i:s');

        $query_get    = "SELECT a.NOTA_LAMA, c.NO_REQUEST, a.NO_NOTA,a.NO_NOTA_MTI,a.NO_FAKTUR_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA,
	   TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, c.NO_DO, c.NO_BL,
	   TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN,
	   TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
	   CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, c.NO_DO, c.NO_BL, m.NAME NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD-MM-RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_stripping a, request_stripping c, BILLING_NBS.TB_USER m, itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
							AND a.nipp_user = m.id(+)
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM NOTA_STRIPPING d WHERE d.NO_REQUEST = '$no_req' )
                            and a.NO_REQUEST = '$no_req'
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";

        $row_nota       = DB::connection('uster')->selectOne($query_get);

        $no_nota_        = $row_nota->no_nota;
        $no_nota        = $row_nota->no_faktur_;
        $no_mat         = $row_nota->no_peraturan;
        $no_nota_mti        = $row_nota->no_nota_mti;
        $no_faktur_mti      = $row_nota->no_faktur_mti;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $no_do  = $row_nota->no_do;
        $no_bl  = $row_nota->no_bl;
        $nm_insert  = $row_nota->nama_lengkap;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nm_insert,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
            "NO_BL" => $no_bl,
            "NO_DO" => $no_do,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));

        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_stripping_d a
			 WHERE a.no_nota = '$no_nota_' AND a.keterangan = 'MATERAI'";
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }

        $query_get2 = "SELECT a.JML_HARI,
                             TO_CHAR(SUM(a.TARIF),'999,999,999,999') TARIF,
                             TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
							 a.tekstual KETERANGAN,
                             a.HZ,a.JML_CONT,
                              --case a.tekstual when 'PAKET STRIPPING'
                             --THEN (select count(no_container) from container_stripping where no_request = '$no_req')
                             --ELSE
                              --a.JML_CONT
                              --END AS JML_CONT,
                             TO_CHAR (a.START_STACK, 'DD-MM-RRRR') START_STACK,
                             TO_CHAR (a.END_STACK, 'DD-MM-RRRR') END_STACK,
                             b.SIZE_,
                             b.TYPE_,
                              case a.tekstual when 'PAKET STRIPPING'
                             THEN '-'
                             ELSE
                              b.STATUS
                              END AS STATUS,
                              case a.tekstual when 'PAKET STRIPPING'
                             THEN 10
                             ELSE
                              a.urut
                              END AS urut
                        FROM nota_stripping_d a, iso_code b
                       WHERE     a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI') /*modify fauzan 23 September 2020*/ AND a.KETERANGAN NOT LIKE '%PENUMPUKAN%'
                             AND a.id_iso = b.id_iso
                             AND a.no_nota = '$no_nota_'
                    GROUP BY a.tekstual, a.jml_hari, a.hz, a.jml_cont, a.start_stack, a.end_stack, b.size_, b.type_,  case a.tekstual when 'PAKET STRIPPING'
                             THEN '-'
                             ELSE
                              b.STATUS
                              END, case a.tekstual when 'PAKET STRIPPING'
                             THEN 10
                             ELSE
                              a.urut
                              END
                   UNION ALL
                   SELECT a.JML_HARI,
                             TO_CHAR(a.TARIF,'999,999,999,999') TARIF,
                             TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA,
                             a.tekstual KETERANGAN,
                             a.HZ,a.JML_CONT,
                              --case a.tekstual when 'PAKET STRIPPING'
                             --THEN (select count(no_container) from container_stripping where no_request = '$no_req')
                             --ELSE
                              --a.JML_CONT
                              --END AS JML_CONT,
                             TO_CHAR (a.START_STACK, 'DD-MM-RRRR') START_STACK,
                             TO_CHAR (a.END_STACK, 'DD-MM-RRRR') END_STACK,
                             b.SIZE_,
                             b.TYPE_,
                              case a.tekstual when 'PAKET STRIPPING'
                             THEN '-'
                             ELSE
                              b.STATUS
                              END AS STATUS,
                              case a.tekstual when 'PAKET STRIPPING'
                             THEN 10
                             ELSE
                              a.urut
                              END AS urut
                        FROM nota_stripping_d a, iso_code b
                       WHERE     a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI') /*modify fauzan 23 September 2020*/ AND a.KETERANGAN LIKE '%PENUMPUKAN%'
                             AND a.id_iso = b.id_iso
                             AND a.no_nota = '$no_nota_'
                    ORDER BY urut ASC";

        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA STRIPPING',
            'no_do' => $no_do,
            'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Stripping.pdf"',
        ]);
    }

    function printNotaLunasRelokmtyMti($noreq)
    {
        global $no_request, $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;
        //$dt      = date('d-M-Y H:i:s');

        $query_get    = "SELECT a.NOTA_LAMA, c.NO_REQUEST, a.NO_NOTA, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/rrrr') TGL_REQUEST, CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, TO_CHAR(a.TOTAL_DISKON,'999,999,999,999') TOTAL_DISKON, TOTAL_TAGIHAN AS TOTAL_TAGIHANR, PPN AS PPNR, TAGIHAN AS TAGIHANR, TOTAL_DISKON AS TOTAL_DISKONR, ADM_NOTA AS ADM_NOTAR, m.Name NAMA_LENGKAP, TO_CHAR(a.TGL_NOTA,'dd/mm/rrrr') TGL_NOTA, A.NO_NOTA_MTI, A.NO_FAKTUR_MTI, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_relokasi_mty a, request_stripping c, BILLING_NBS.TB_USER m where
                            a.NO_REQUEST = c.NO_REQUEST
							AND a.nipp_user = m.id
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_relokasi_mty d WHERE d.NO_REQUEST = '$no_req' )
                            and a.NO_REQUEST = '$no_req'";


        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota        = $row_nota->no_faktur_;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap  = $row_nota->nama_lengkap;
        $no_do  = $row_nota->no_do ?? '';
        $no_bl  = $row_nota->no_bl ?? '';
        $nota_mti          = $row_nota->no_nota_mti;
        $faktur_mti      = $row_nota->no_faktur_mti;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        $no_per = "SELECT * FROM ITPK_NOTA_HEADER WHERE NO_NOTA_MTI='$nota_mti'";
        $nom    = DB::connection('uster')->selectOne($no_per);
        $no_mat        = $nom->no_peraturan;


        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
            "NO_BL" => $no_bl,
            "NO_DO" => $no_do,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));

        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_relokasi_mty_d a, nota_relokasi_mty b
			 WHERE a.no_nota = b.no_nota
			   AND b.no_request = '$no_req'
			   AND a.keterangan = 'MATERAI'";
        //print_r($query_mtr);	die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        /*end hitung materai Fauzan 23 September 2020*/

        $query_get2 = "SELECT TO_CHAR (a.START_STACK, 'dd/mm/yyyy') START_STACK,
                    TO_CHAR (a.END_STACK, 'dd/mm/yyyy') END_STACK,
                    a.tekstual keterangan,a.JML_CONT,
                    a.JML_HARI,
                    b.SIZE_,
                    b.TYPE_,
                    case a.tekstual when 'GERAKAN ANTAR BLOK'
                    THEN '-'
                    ELSE
                    b.STATUS
                    END AS STATUS,
                    a.HZ,
                    TO_CHAR(SUM(a.TARIF),'999,999,999,999') TARIF,
                    TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                    case a.tekstual when 'GERAKAN ANTAR BLOK'
                    THEN 10
                    ELSE
                    a.urut
                    END AS urut
                FROM nota_relokasi_mty_d a, iso_code b
            WHERE     a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI') /*modify fauzan 23 September 2020*/
                    AND a.ID_ISO = b.ID_ISO(+)
                    AND a.NO_NOTA = (SELECT MAX (d.NO_NOTA)
                                        FROM nota_relokasi_mty d
                                    WHERE d.NO_REQUEST = '$no_req')
                GROUP BY a.tekstual, a.START_STACK, a.END_STACK,  a.JML_CONT,  b.SIZE_,  b.TYPE_, a.HZ, a.JML_HARI, case a.tekstual when 'GERAKAN ANTAR BLOK'
                    THEN '-'
                    ELSE
                    b.STATUS
                    END,
                    case a.tekstual when 'GERAKAN ANTAR BLOK'
                    THEN 10
                    ELSE
                    a.urut
                    END
                ORDER BY urut";

        $row_detail    = DB::connection('uster')->select($query_get2);


        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA RELOKASI MTY',
            'no_do' => $no_do,
            'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Relokasi_MTY.pdf"',
        ]);
    }

    function printNotaLunasPerpstripMti($noreq)
    {
        global $no_request, $no_faktur_mti, $no_nota_mti;
        global $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;

        $query_get    = "SELECT a.NOTA_LAMA, c.NO_REQUEST, a.NO_NOTA,a.NO_NOTA_MTI,a.NO_FAKTUR_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
	    CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, m.NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_stripping a, request_stripping c, master_user m,itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
                          	AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_stripping d WHERE d.NO_REQUEST = '$no_req' )
                            and a.NO_REQUEST = '$no_req'
							AND A.nipp_user = m.id(+)
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";
        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota_mti        = $row_nota->no_nota_mti;
        $no_faktur_mti    = $row_nota->no_faktur_mti;
        $no_nota        = $row_nota->no_faktur_;
        $no_request     = $row_nota->no_request;
        $no_mat         = $row_nota->no_peraturan;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap  = $row_nota->nama_lengkap;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
            // "NO_BL" => $no_bl,
            // "NO_DO" => $no_do,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));


        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_stripping_d a, nota_stripping b
			 WHERE a.no_nota = b.no_nota
			   AND b.no_request = '$no_req'
			   AND a.keterangan = 'MATERAI'";
        //print_r($query_mtr);	die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        /*end hitung materai Fauzan 23 September 2020*/

        $query_get2    = "SELECT TO_CHAR(a.START_STACK,'dd/mm/yyyy') START_STACK,TO_CHAR(a.END_STACK,'dd/mm/yyyy') END_STACK, a.KETERANGAN, a.JML_CONT, a.JML_HARI, b.SIZE_, b.TYPE_, b.STATUS, a.HZ, TO_CHAR(a.TARIF,'999,999,999,999') TARIF , TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA FROM nota_stripping_d a, iso_code b, nota_stripping c WHERE a.ID_ISO = b.ID_ISO(+) AND a.NO_NOTA = c.NO_NOTA AND a.NO_NOTA = (SELECT MAX(d.NO_NOTA) FROM NOTA_STRIPPING d WHERE d.NO_REQUEST = '$no_req')
		 and a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI')"; /*modify fauzan 23 September 2020*/
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA PERPANJANGAN STRIPPING',
            // 'no_do' => $no_do,
            // 'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Relokasi_MTY.pdf"',
        ]);
    }

    function printnNotaLunasPnknstufMti($noreq)
    {
        global $no_request, $no_faktur_mti, $no_nota_mti;
        global $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;

        $query_get    = "SELECT NOTA_LAMA, c.NO_REQUEST, a.NO_NOTA, a.NO_NOTA_MTI, c.NO_DO, c.NO_BL, a.NO_FAKTUR_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
			 CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG , m.NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_pnkn_stuf a, request_stuffing c, master_user m,itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_pnkn_stuf d WHERE d.NO_REQUEST = '$no_req' )
                            and a.NO_REQUEST = '$no_req'
							AND a.nipp_user = m.id(+)
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";
        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota_       = $row_nota->no_nota;
        $no_nota        = $row_nota->no_faktur_;
        $no_nota_mti    = $row_nota->no_nota_mti;
        $no_mat         = $row_nota->no_peraturan;
        $no_faktur_mti  = $row_nota->no_faktur_mti;
        $no_do             = $row_nota->no_do;
        $no_bl            = $row_nota->no_bl;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama      = $row_nota->nota_lama;
        $terbilang        = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap   = $row_nota->nama_lengkap;

        $pegawai        = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg       = DB::connection('uster')->selectOne($pegawai);
        $nama           = $nama_peg->nama_pegawai;
        $jabatan        = $nama_peg->jabatan;
        $nipp           = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
            // "NO_BL" => $no_bl,
            // "NO_DO" => $no_do,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));


        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
        FROM nota_pnkn_stuf_d a
       WHERE a.no_nota = '$no_nota_' AND a.keterangan = 'MATERAI'";
        //print_r($query_mtr);	die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        /*end hitung materai Fauzan 23 September 2020*/

        $query_get2 = "SELECT a.JML_HARI,
                             TO_CHAR(a.TARIF,'999,999,999,999') TARIF,
                             TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA,
                             a.KETERANGAN,
                             a.HZ,a.JUMLAH_CONT JML_CONT,
                             TO_CHAR (a.START_STACK, 'dd/mm/rrrr') START_STACK,
                             TO_CHAR (a.END_STACK, 'dd/mm/rrrr') END_STACK,
                             b.SIZE_,
                             b.TYPE_,b.STATUS,urut
                        FROM nota_pnkn_stuf_d a, iso_code b
                       WHERE a.id_iso = b.id_iso
                             AND a.no_nota = '$no_nota_'
							 AND  a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI') /*modify fauzan 23 September 2020*/
                    ORDER BY urut ASC";
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA PENUMPUKAN STUFFING',
            'no_do' => $no_do,
            'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Relokasi_MTY.pdf"',
        ]);
    }
    function printNotaLunasStufMti($noreq)
    {
        global $no_request, $no_faktur_mti, $no_nota_mti;
        global $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;

        $query_get    = "SELECT NOTA_LAMA, c.NO_REQUEST, a.NO_NOTA, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG,
             mu.NAMA_LENGKAP, c.NO_DO, c.NO_BL, c.NO_DOKUMEN,a.NO_NOTA_MTI, a.NO_FAKTUR_MTI,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
        THEN a.NO_NOTA
        ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_stuffing a, request_stuffing c, master_user mu, itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
                            AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_stuffing d WHERE d.NO_REQUEST = '$no_req' )
                            and a.NO_REQUEST = '$no_req'
                            and a.nipp_user = mu.id(+)
                            and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";
        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota_mti        = $row_nota->no_nota_mti;
        $no_faktur_mti        = $row_nota->no_faktur_mti;
        //print_r($no_nota_mti);die();
        $no_nota_        = $row_nota->no_nota;
        $no_mat         = $row_nota->no_peraturan;
        $no_nota        = $row_nota->no_faktur_;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = ucwords(strtolower($row_nota->terbilang));
        $total_tagihan  = $row_nota->total_tagihan;
        $nm_lengkap  = $row_nota->nama_lengkap;
        $no_do  = $row_nota->no_do;
        $no_bl  = $row_nota->no_bl;
        $no_dokumen  = $row_nota->no_dokumen;

        $pegawai        = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg       = DB::connection('uster')->selectOne($pegawai);
        $nama           = $nama_peg->nama_pegawai;
        $jabatan        = $nama_peg->jabatan;
        $nipp           = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nm_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
            "NO_BL" => $no_bl,
            "NO_DO" => $no_do,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));


        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
                  FROM nota_stuffing_d a
                 WHERE a.no_nota = '$no_nota_' AND a.keterangan = 'MATERAI'";
        //print_r($query_mtr);	die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        /*end hitung materai Fauzan 23 September 2020*/

        $cek_jenis = "select count( distinct asal_cont) jenis from container_stuffing where no_request = '$no_req'";
        $r_jns = DB::connection('uster')->selectOne($cek_jenis);
        if ($r_jns->jenis > 1) {

            $query_get2    = "SELECT partone.*, partwo.*, TO_CHAR(partone.biaya_/partwo.jml_cont,'999,999,999,999')  tarif FROM (SELECT a.JML_HARI,
                                     SUM(a.BIAYA) biaya_,
                                     TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                                     a.tekstual KETERANGAN,
                                     a.HZ,
                                     TO_CHAR (a.START_STACK, 'dd/mm/rrrr') START_STACK,
                                     TO_CHAR (a.END_STACK, 'dd/mm/rrrr') END_STACK,
                                     b.SIZE_,
                                     b.TYPE_,
                                      case a.tekstual
                                          when 'PAKET STUFF LAPANGAN' THEN '-'
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                                     ELSE
                                      b.STATUS
                                      END AS STATUS,
                                      case a.tekstual
                                         when 'PAKET STUFF LAPANGAN' THEN 10
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                                     ELSE
                                      a.urut
                                      END AS urut
                                FROM nota_stuffing_d a, iso_code b
                               WHERE     a.TEKSTUAL <> 'ADMIN NOTA'
                                     AND a.id_iso = b.id_iso
                                     AND a.no_nota = '$no_nota_'
                            GROUP BY a.jml_hari, a.hz, a.start_stack, a.end_stack, b.size_, b.type_,  a.tekstual,
                            case a.tekstual
                                          when 'PAKET STUFF LAPANGAN' THEN '-'
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                                     ELSE
                                      b.STATUS
                                      END,
                            case a.tekstual
                                         when 'PAKET STUFF LAPANGAN' THEN 10
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                                     ELSE
                                      a.urut
                                      END) partone,
                        (SELECT case a.tekstual
                                          when 'PAKET STUFF LAPANGAN' THEN (select count(*) from container_stuffing where no_request = '$no_req' and type_stuffing = 'STUFFING_LAP')
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN (select count(*) from container_stuffing where no_request = '$no_req' and type_stuffing = 'STUFFING_GUD_TONGKANG')
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN (select count(*) from container_stuffing where no_request = '$no_req' and type_stuffing = 'STUFFING_GUD_TRUCK')
                                     ELSE 0
                                      END AS jml_cont FROM nota_stuffing_d a WHERE no_nota = '$no_nota_'
                                      and a.tekstual in ('PAKET STUFF LAPANGAN','PAKET STUFF GUDANG EKS TONGKANG','PAKET STUFF GUDANG EKS TRUCK')
                           group by a.tekstual) partwo";
        } else {
            $query_get2 = "SELECT a.JML_HARI,
                                     SUM(a.BIAYA) biaya_,
                                     TO_CHAR(SUM(a.BIAYA),'999,999,999,999') BIAYA,
                                     TO_CHAR(SUM(a.TARIF),'999,999,999,999') TARIF,
                                     a.tekstual KETERANGAN,
                                     a.jumlah_cont JML_CONT,
                                     a.HZ,
                                     TO_CHAR (a.START_STACK, 'dd/mm/rrrr') START_STACK,
                                     TO_CHAR (a.END_STACK, 'dd/mm/rrrr') END_STACK,
                                     b.SIZE_,
                                     b.TYPE_,
                                      case a.tekstual
                                          when 'PAKET STUFF LAPANGAN' THEN '-'
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                                     ELSE
                                      b.STATUS
                                      END AS STATUS,
                                      case a.tekstual
                                         when 'PAKET STUFF LAPANGAN' THEN 10
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                                     ELSE
                                      a.urut
                                      END AS urut
                                FROM nota_stuffing_d a, iso_code b
                               WHERE     a.TEKSTUAL <> 'ADMIN NOTA'
                                     AND a.id_iso = b.id_iso
                                     AND a.no_nota = '$no_nota_'
                            GROUP BY a.jml_hari, a.hz, a.start_stack, a.end_stack, b.size_, b.type_,  a.tekstual, jumlah_cont,
                            case a.tekstual
                                          when 'PAKET STUFF LAPANGAN' THEN '-'
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN '-'
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN '-'
                                     ELSE
                                      b.STATUS
                                      END,
                            case a.tekstual
                                         when 'PAKET STUFF LAPANGAN' THEN 10
                                          when 'PAKET STUFF GUDANG EKS TONGKANG' THEN 10
                                          when 'PAKET STUFF GUDANG EKS TRUCK' THEN 10
                                     ELSE
                                      a.urut
                                      END";
        }

        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA STUFFING',
            'no_do' => $no_do,
            'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Relokasi_MTY.pdf"',
        ]);
    }

    function printNotaLunasDeltpkMti($noreq)
    {
        global $no_request, $no_faktur_mti, $no_nota_mti;
        global $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;

        $query_get    = "SELECT NOTA_LAMA, c.NO_REQUEST, a.NO_NOTA, a.NO_NOTA_MTI,a.NO_FAKTUR_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG , c.VESSEL, c.VOYAGE, c.DELIVERY_KE, c.JN_REPO, m.NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_delivery a, request_delivery c, master_user m,itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_delivery d WHERE d.NO_REQUEST = '$no_req' )
                            and a.NO_REQUEST = '$no_req'
							and a.nipp_user = m.id(+)
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";
        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota_mti        = $row_nota->no_nota_mti;
        $no_faktur_mti       = $row_nota->no_faktur_mti;
        $no_mat         = $row_nota->no_peraturan;
        $no_nota        = $row_nota->no_faktur_;
        $no_nota_        = $row_nota->no_nota;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $vessel  = $row_nota->vessel;
        $voyage  = $row_nota->voyage;
        $nama_lengkap  = $row_nota->nama_lengkap;

        $pegawai        = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg       = DB::connection('uster')->selectOne($pegawai);
        $nama           = $nama_peg->nama_pegawai;
        $jabatan        = $nama_peg->jabatan;
        $nipp           = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
            // "NO_BL" => $no_bl,
            // "NO_DO" => $no_do,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }
        if ($row_nota->jn_repo == "EKS_STUFFING") {
            session(["nm_nota" => 'LAMPIRAN NOTA RELOKASI KE TPK EKS STUFFING']);
        } else {
            session(["nm_nota" => 'LAMPIRAN NOTA RELOKASI KE TPK']);
        }

        $bilang = ucwords(session()->get('terbilang'));


        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_delivery_d a
			 WHERE a.id_nota = '$no_nota_' AND a.keterangan = 'MATERAI'";
        //print_r($query_mtr);	die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        /*end hitung materai Fauzan 23 September 2020*/

        $query_get2 = "SELECT * from (SELECT a.JML_HARI,
                                TO_CHAR (a.TARIF, '999,999,999,999') AS TARIF,
                                TO_CHAR (a.BIAYA, '999,999,999,999') AS BIAYA,
                                case when a.tekstual is null
                                        then a.KETERANGAN
                                        else a.tekstual
                                        end keterangan,
                                a.HZ,
                                a.JML_CONT,
                                TO_CHAR (a.START_STACK, 'dd/mm/yyyy') START_STACK,
                                TO_CHAR (a.END_STACK, 'dd/mm/yyyy') END_STACK,
                                b.SIZE_,
                                b.TYPE_,
                                b.STATUS
                        FROM nota_delivery_d a, iso_code b
                        WHERE
                        a.id_iso = b.id_iso
                            AND a.id_nota = '$no_nota_'
                            AND a.keterangan NOT IN ('ADMIN NOTA','MATERAI') /*fauzan modif 28 aug 2020*/
                        --          AND a.keterangan = 'PENUMPUKAN MASA II'
                            AND a.JML_HARI IS NOT NULL
                            AND KETERANGAN LIKE '%PENUMPUKAN%'
                                    UNION ALL
                                SELECT a.JML_HARI,
                                    TO_CHAR (SUM(a.TARIF), '999,999,999,999') AS TARIF,
                                    TO_CHAR (SUM(a.BIAYA), '999,999,999,999') AS BIAYA,
                                    case when a.tekstual is null
                                            then a.KETERANGAN
                                            else a.tekstual
                                            end keterangan,
                                    a.HZ,
                                    a.JML_CONT JML_CONT,
                                    TO_CHAR (a.START_STACK, 'dd/mm/yyyy') START_STACK,
                                    TO_CHAR (a.END_STACK, 'dd/mm/yyyy') END_STACK,
                                    b.SIZE_,
                                    b.TYPE_,
                                    b.STATUS
                                FROM nota_delivery_d a, iso_code b
                                WHERE a.id_iso = b.id_iso
                                    AND a.id_nota = '$no_nota_'
                                    AND a.keterangan NOT IN ('ADMIN NOTA','MATERAI') /*fauzan modif 28 aug 2020*/
                                    AND a.JML_HARI IS NULL
                                    --AND a.TEKSTUAL in ('GERAKAN ANTAR BLOK')
                                GROUP BY case when a.tekstual is null
                                    then a.KETERANGAN
                                    else a.tekstual
                                    end,a.JML_HARI, a.HZ, a.JML_CONT, b.SIZE_, b.TYPE_, b.STATUS, a.START_STACK, a.END_STACK) cs
                        order by cs.keterangan ";
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => session()->get('nm_nota'),
            // 'no_do' => $no_do,
            // 'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Relokasi_MTY.pdf"',
        ]);
    }

    function printNotaLunasDeluarMti($noreq)
    {
        global $no_request, $no_faktur_mti, $no_nota_mti;
        global $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;

        $query_get    = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, a.NO_NOTA_MTI, a.NO_FAKTUR_MTI,
	   CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, m.name NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_delivery a, request_delivery c, BILLING_NBS.TB_USER m,itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST and a.no_request = '$no_req'
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM NOTA_DELIVERY d WHERE d.NO_REQUEST = '$no_req' )
							and a.nipp_user = m.id
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";
        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota        = $row_nota->no_nota_mti;
        $no_mat         = $row_nota->no_peraturan;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur_mti;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap  = $row_nota->nama_lengkap;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
            // "NO_BL" => $no_bl,
            // "NO_DO" => $no_do,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));


        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_delivery_d a, nota_delivery b
			 WHERE a.id_nota = b.no_nota
			   AND b.no_request = '$no_req'
			   AND a.keterangan = 'MATERAI'";
        //print_r($query_mtr);	die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }
        /*end hitung materai Fauzan 23 September 2020*/

        $query_get2    = "SELECT TO_CHAR(a.START_STACK,'dd/mm/yyyy') START_STACK,TO_CHAR(a.END_STACK,'dd/mm/yyyy') END_STACK,
        a.KETERANGAN, a.JML_CONT, a.JML_HARI, b.SIZE_, b.TYPE_, b.STATUS, a.HZ, TO_CHAR(a.TARIF,'999,999,999,999') TARIF , TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA
        FROM nota_delivery_d a, iso_code b, nota_delivery c
        WHERE a.ID_NOTA = c.NO_NOTA and c.no_request = '$no_req'
        AND a.ID_ISO = b.ID_ISO(+)
        AND c.TGL_NOTA = (SELECT MAX(d.TGL_NOTA)
                            FROM NOTA_DELIVERY d
                            WHERE d.NO_REQUEST = '$no_req') AND a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI')";
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA DELIVERY',
            // 'no_do' => $no_do,
            // 'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            // 'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img,
            "no_mat" => $no_mat,
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Relokasi_MTY.pdf"',
        ]);
    }

    function printNotaLunasPerpdelMti($noreq)
    {

        global $no_request, $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;


        $query_get    = "SELECT c.NO_REQUEST, a.NO_NOTA, a.NOTA_LAMA, a.NO_NOTA_MTI,a.NO_FAKTUR_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
		CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, mu.NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_delivery a, request_delivery c, master_user mu,itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM NOTA_DELIVERY d WHERE d.NO_REQUEST = '$no_req' )
							and a.nipp_user = mu.id(+)
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";

        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota_mti        = $row_nota->no_nota_mti;
        $no_faktur_mti        = $row_nota->no_faktur_mti;
        $no_mat         = $row_nota->no_peraturan;
        $no_nota        = $row_nota->no_faktur_;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap  = $row_nota->nama_lengkap;
        $no_do  = isset($row_nota->no_do) ? $row_nota->no_do : '-';
        $no_bl  = isset($row_nota->no_bl) ? $row_nota->no_bl : '-';
        $nota_mti          = $row_nota->no_nota_mti;
        $faktur_mti      = $row_nota->no_faktur_mti;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));

        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_delivery_d a, nota_delivery b
			 WHERE a.id_nota = b.no_nota
			   AND b.no_request = '$no_req'
			   AND a.keterangan = 'MATERAI'";
        //    echo $query_mtr;die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }

        $query_get2    = "SELECT TO_CHAR(a.START_STACK,'dd/mm/yyyy') START_STACK,TO_CHAR(a.END_STACK,'dd/mm/yyyy') END_STACK, a.KETERANGAN, a.JML_CONT, a.JML_HARI, b.SIZE_, b.TYPE_, b.STATUS, a.HZ, TO_CHAR(a.TARIF,'999,999,999,999') TARIF , TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA FROM nota_delivery_d a, iso_code b, nota_delivery c WHERE a.ID_NOTA = c.NO_NOTA AND a.ID_ISO = b.ID_ISO(+)
			 AND c.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM NOTA_DELIVERY d WHERE d.NO_REQUEST = '$no_req') AND a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI')";
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA PERPANJANGAN DELIVERY',
            'no_do' => $no_do,
            'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Receiving.pdf"',
        ]);
    }

    function printNotaLunasPerpstufMti($noreq)
    {

        global $no_request, $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;


        $query_get    = "SELECT NOTA_LAMA, c.NO_REQUEST, a.NO_NOTA,a.NO_NOTA_MTI,a.NO_FAKTUR_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
			 CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, m.NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_stuffing a, request_stuffing c, master_user m,itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_stuffing d WHERE d.NO_REQUEST = '$no_req' )
                            and a.NO_REQUEST = '$no_req'
							AND a.nipp_user = m.id(+)
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";

        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota_mti        = $row_nota->no_nota_mti;
        $no_faktur_mti        = $row_nota->no_faktur_mti;
        $no_mat         = $row_nota->no_peraturan;
        $no_nota        = $row_nota->no_faktur_;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap  = $row_nota->nama_lengkap;
        $no_do  = $row_nota->no_do ?? null;
        $no_bl  = $row_nota->no_bl ?? null;
        $nota_mti          = $row_nota->no_nota_mti;
        $faktur_mti      = $row_nota->no_faktur_mti;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));

        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_stuffing_d a, nota_stuffing b
			 WHERE a.no_nota = b.no_nota
			   AND b.no_request = '$no_req'
			   AND a.keterangan = 'MATERAI'";
        //    echo $query_mtr;die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }

        $query_get2    = "SELECT TO_CHAR(a.START_STACK,'dd/mm/yyyy') START_STACK,
        TO_CHAR(a.END_STACK,'dd/mm/yyyy') END_STACK, a.KETERANGAN, a.JUMLAH_CONT, a.JML_HARI, b.SIZE_, b.TYPE_, b.STATUS, a.HZ, TO_CHAR(a.TARIF,'999,999,999,999') TARIF , TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA
        FROM nota_stuffing_d a, iso_code b
        WHERE a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI') /*modify fauzan 23 September 2020*/
            AND a.ID_ISO = b.ID_ISO(+)
            AND a.NO_NOTA = (SELECT MAX(d.NO_NOTA) FROM NOTA_STUFFING d WHERE d.NO_REQUEST = '$no_req')
        ORDER BY URUT";
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA PERPANJANGAN PNKN STUFFING',
            'no_do' => $no_do,
            'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img
        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Receiving.pdf"',
        ]);
    }

    function printNotaLunasBamuPti($noreq)
    {

        global $no_request, $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;


        $query_get    = "SELECT A.NOTA_LAMA, c.NO_REQUEST, a.NO_NOTA,a.NO_NOTA_MTI,a.NO_FAKTUR_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST, CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, c.NO_REQ_BARU, c.STATUS_GATE, m.NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM nota_batal_muat a, request_batal_muat c, master_user m,itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST
                            AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM nota_batal_muat d WHERE d.NO_REQUEST = '$no_req' )
							AND a.nipp_user = m.id(+)
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";

        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota_mti        = $row_nota->no_nota_mti;
        $no_faktur_mti        = $row_nota->no_faktur_mti;
        $no_mat         = $row_nota->no_peraturan;
        $no_nota_        = $row_nota->no_nota;
        $no_nota        = $row_nota->no_faktur_;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari ?? '';
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? '';
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap  = $row_nota->nama_lengkap;
        $status_gate  = $row_nota->status_gate;
        $no_do  = $row_nota->no_do ?? '';
        $no_bl  = $row_nota->no_bl ?? '';
        $nota_mti          = $row_nota->no_nota_mti;
        $faktur_mti      = $row_nota->no_faktur_mti;
        $no_req_baru  = $row_nota->no_req_baru;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        if ($status_gate == 2) {
            $teks_baru = "No. Request Repo Baru";
        } else {
            $teks_baru = "No. Stuffing Baru";
        }

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));

        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_batal_muat_d a
			 WHERE a.id_nota = '$no_nota_' AND a.keterangan = 'MATERAI'";
        //    echo $query_mtr;die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }

        $query_get2    = "SELECT a.JML_HARI, TO_CHAR(a.TARIF, '999,999,999,999') AS TARIF, TO_CHAR(a.BIAYA, '999,999,999,999') AS BIAYA, a.KETERANGAN, a.HZ,
        a.JML_CONT, TO_DATE(a.START_STACK,'dd/mm/yyyy') START_STACK, TO_DATE(a.END_STACK,'dd/mm/yyyy') END_STACK, b.SIZE_, b.TYPE_, b.STATUS FROM NOTA_BATAL_MUAT_D a
        , iso_code b WHERE a.id_iso = b.id_iso and a.ID_NOTA = '$no_nota_' AND A.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI')";
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA BATAL MUAT',
            'no_do' => $no_do,
            'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img,
            // 'teks_baru' => $teks_baru,
            // 'no_req_baru' => $no_req_baru

        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Receiving.pdf"',
        ]);
    }

    function printNotaLunasPnkndelMti($noreq)
    {

        global $no_request, $dt;
        $no_req = $noreq;
        $qtime = "SELECT TO_CHAR(SYSDATE, 'DD-MM-YYYY HH24:MI:SS') TIME_ FROM DUAL";
        $rtime = DB::connection('uster')->selectOne($qtime);
        $dt    = $rtime->time_;


        $query_get    = "SELECT c.NO_REQUEST, a.NOTA_LAMA, a.NO_NOTA, a.NO_NOTA_MTI, a.NO_FAKTUR_MTI, TO_CHAR(a.ADM_NOTA,'999,999,999,999') ADM_NOTA, TO_CHAR(a.PASS,'999,999,999,999') PASS, a.EMKL NAMA, a.ALAMAT, a.NPWP, c.PERP_DARI, a.LUNAS,a.NO_FAKTUR, TO_CHAR(a.TAGIHAN,'999,999,999,999') TAGIHAN, TO_CHAR(a.PPN,'999,999,999,999') PPN, TO_CHAR(a.TOTAL_TAGIHAN,'999,999,999,999') TOTAL_TAGIHAN, a.STATUS, TO_CHAR(c.TGL_REQUEST,'dd/mm/yyyy') TGL_REQUEST,
	   CONCAT(TERBILANG(a.TOTAL_TAGIHAN),'rupiah') TERBILANG, m.NAMA_LENGKAP,inh.NO_PERATURAN, CASE WHEN TRUNC(TGL_NOTA) < TO_DATE('1/6/2013','DD/MM/RRRR')
		THEN a.NO_NOTA
		ELSE A.NO_FAKTUR END NO_FAKTUR_
                            FROM NOTA_PNKN_DEL a, request_delivery c, master_user m,itpk_nota_header inh where
                            a.NO_REQUEST = c.NO_REQUEST and a.NO_REQUEST = '$no_req'
							AND a.TGL_NOTA = (SELECT MAX(d.TGL_NOTA) FROM NOTA_PNKN_DEL d WHERE d.NO_REQUEST = '$no_req' ) and
							a.nipp_user = m.id(+)
							and a.NO_NOTA_MTI = inh.NO_NOTA_MTI";

        $row_nota       = DB::connection('uster')->selectOne($query_get);
        $no_nota        = $row_nota->no_nota_mti;
        $no_mat        = $row_nota->no_peraturan;
        $no_request     = $row_nota->no_request;
        $no_faktur      = $row_nota->no_faktur_mti;
        $emkl           = $row_nota->nama;
        $npwp           = $row_nota->npwp;
        $perp_dari      = $row_nota->perp_dari;
        $alamat         = $row_nota->alamat;
        $status         = $row_nota->status;
        $tagihan        = $row_nota->tagihan;
        $formulir       = $row_nota->formulir ?? null;
        $ppn            = $row_nota->ppn;
        $pass           = $row_nota->pass;
        $adm_nota       = $row_nota->adm_nota;
        $nota_lama       = $row_nota->nota_lama;
        $terbilang       = $row_nota->terbilang;
        $total_tagihan  = $row_nota->total_tagihan;
        $nama_lengkap  = $row_nota->nama_lengkap;

        $pegawai    = "SELECT * FROM MASTER_PEGAWAI WHERE STATUS = 'AKTIF'";
        $nama_peg    = DB::connection('uster')->selectOne($pegawai);
        $nama        = $nama_peg->nama_pegawai;
        $jabatan    = $nama_peg->jabatan;
        $nipp        = $nama_peg->nipp;

        session([
            "no_nota" => $no_nota,
            "NOTA_MTI" => $row_nota->no_nota_mti,
            "FAKTUR_MTI" => $row_nota->no_faktur_mti,
            "NO_FAKTUR" => $row_nota->no_faktur_,
            "jabatan" => $jabatan,
            "nama_pegawai" => $nama,
            "nipp" => $nipp,
            "emkl" => $emkl,
            "npwp" => $npwp,
            "alamat" => $alamat,
            "terbilang" => $terbilang,
            "total_tagihan" => $total_tagihan,
            "PRINTED_BY" => $nama_lengkap,
            "KET_NOTA" => "Nota Berlaku Sebagai Pajak Berdasarkan Peraturan Dirjen Pajak PER-13/PJ/2019",
            "date" => $dt,
        ]);

        if ($nota_lama == NULL) {
            session(["nota_lama" => '']);
        } else {
            session(["nota_lama" => 'EX ' . $nota_lama]);
        }

        $bilang = ucwords(session()->get('terbilang'));

        $query_mtr = "SELECT TO_CHAR (a.biaya, '999,999,999,999') bea_materai, a.BIAYA
			  FROM nota_pnkn_del_d a, nota_pnkn_del b
			 WHERE a.id_nota = b.no_nota
			   AND b.no_request = '$no_req'
			   AND a.keterangan = 'MATERAI'";
        //    echo $query_mtr;die();
        $data_mtr = DB::connection('uster')->selectOne($query_mtr);
        $data_mtr_biaya = $data_mtr->biaya ?? 0;
        if ($data_mtr_biaya > 0) {
            $bea_materai = $data_mtr->bea_materai;
        } else {
            $bea_materai = 0;
        }

        $query_get2    = "SELECT TO_CHAR(a.START_STACK,'dd/mm/yyyy') START_STACK,TO_CHAR(a.END_STACK,'dd/mm/yyyy') END_STACK,
                    a.KETERANGAN, a.JML_CONT, a.JML_HARI, b.SIZE_, b.TYPE_, b.STATUS, a.HZ, TO_CHAR(a.TARIF,'999,999,999,999') TARIF , TO_CHAR(a.BIAYA,'999,999,999,999') BIAYA
            FROM NOTA_PNKN_DEL_D a, iso_code b, NOTA_PNKN_DEL c
            WHERE a.ID_NOTA = c.NO_NOTA
            AND a.ID_ISO = b.ID_ISO(+)
            AND c.TGL_NOTA = (SELECT MAX(d.TGL_NOTA)
                      FROM NOTA_PNKN_DEL d
                      WHERE d.NO_REQUEST = '$no_req') and a.KETERANGAN NOT IN ('ADMIN NOTA', 'MATERAI')";
        $row_detail    = DB::connection('uster')->select($query_get2);

        $data = [
            'nota' => $row_nota,
            'ln' => 0,
            'dt' => $dt,
            'nota_kd' => 'LAMPIRAN NOTA PENUMPUKAN DELIVERY',
            // 'no_do' => $no_do,
            // 'no_bl' => $no_bl,
            'rowdetail' => $row_detail,
            'bea_materai' => $bea_materai,
            'no_mat' => $no_mat,
            'data_mtr_biaya' => $data_mtr_biaya,
            'date' => date('d-m-Y', strtotime($dt)),
            "nama" => $nama,
            "nipp" => $nipp,
            "jabatan" => $jabatan,
            'bilang' => $bilang,
            'img' => $this->img,
            // 'teks_baru' => $teks_baru,
            // 'no_req_baru' => $no_req_baru

        ];

        $html = view('billing.paymentcash.pdf.print', $data)->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // Get the PDF content as a string
        $pdfContent = $dompdf->output();

        // Return a response with the PDF content and appropriate headers
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Nota_Receiving.pdf"',
        ]);
    }
}
