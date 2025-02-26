<?php

namespace App\Http\Controllers\Billing\PaymentCash;

use App\Http\Controllers\Controller;
use App\Services\Billing\PaymentCash\PaymentCashService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class PaymentCashController extends Controller
{
    protected $paymentCash;

    public function __construct(PaymentCashService $paymentCash)
    {
        $this->paymentCash = $paymentCash;
    }

    function index()
    {
        return view('billing.paymentcash.index');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->paymentCash->dataNota($request);

        return DataTables::of($listDelivery)->make(true);
    }

    function print(Request $request)
    {
        $viewData = $this->paymentCash->print($request);
        return new Response($viewData, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="examples.pdf"',
        ]);
    }

    public function printNotaSap(Request $request)
    {
        $viewData = $this->paymentCash->getNotaSapData($request);

        if ($viewData) {
            // Generate the PDF as inline content
            return new Response($viewData, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="nota_sap.pdf"',
            ]);
        } else {
            // Handle the case where no data is found
            return response()->json(['message' => 'No data found for the given request.'], 404);
        }
    }

    function pay(Request $request)
    {
        try {
            $nota = $request->idn;
            $mti_nota = $request->mti;
            $req = $request->req;
            $jenis = $request->ket;
            $emkl = $request->emkl;
            $tgl = $request->tgl;
            $koreksi = $request->koreksi;
            $status = 'Y';
            $total = number_format($request->total, 0, ".", ",");
            $jum = $request->total;

            $mat = "SELECT * FROM MASTER_MATERAI where STATUS='" . $status . "'";
            $mat3    = DB::connection('uster')->selectOne($mat);
            $no_matx    = $mat3->no_peraturan;






            $query = "select count(*) as JUM from itpk_nota_header where trim(trx_number)=trim('" . $nota . "') and trim(NO_REQUEST)=trim('" . $req . "')";
            $row = DB::connection('uster')->selectOne($query);
            $hasil = $row->jum;

            if ($hasil > 0) {    //sudah payment
                $response = [
                    'status' => 'failed',
                    'message' => "Request / Nota ini sudah payment",
                ];
                return json_encode($response);
            }


            if ($jum > 5000000) {
                $no_mat = $no_matx;
            } else {
                $no_mat = '';
            }

            $data = [
                'nota' => $nota,
                'mti_nota' => $mti_nota,
                'req' => $req,
                'jenis' => $jenis,
                'emkl' => $emkl,
                'tgl' => $tgl,
                'koreksi' => $koreksi,
                'status' => $status,
                'total' => $total,
                'jum' => $jum,
                'no_mat' => $no_mat
            ];

            $output = View::make("components.pay")
                ->with("dt_item", $data)
                ->with("route", 'asdasd')
                ->with("formId", "tentang-kami-edit")
                ->with("formMethod", "PUT")
                ->render();

            $response = [
                'status' => 'success',
                'output'  => $output,
                'message' => 'Berhasil Parsing',
            ];
            return json_encode($response);
        } catch (Exception $e) {
            $response = [
                'status' => 'failed',
                'message' => "Terjadi Kesalahan pada sistem.",
            ];
        }
        return json_encode($response);
    }

    function paidView(Request $request)
    {
        if (($request->KD_PELUNASAN) == "1") {
            $jns_bayar = "BANK";
            //}elseif (($request->KD_PELUNASAN)=="2") {
            //	$jns_bayar="CASH";
        } elseif (($request->KD_PELUNASAN) == "3") {
            $jns_bayar = "BANK";
        } elseif (($request->KD_PELUNASAN) == "4") {
            $jns_bayar = "BANK";
        }

        $nota = $request->idn;
        $tgl = $request->tgl;
        $tgl_new = strtotime($tgl);

        // $getorgid = "select org_id from BILLING_NBS.TTH_NOTA_ALL2 where trim(NO_NOTA)=trim('" . $nota . "')";
        // $rorg     = DB::connection('uster')->selectOne($getorgid);
        // $org_id = $rorg->ORG_ID;

        $query_nota     = "SELECT bank_account_name receipt_account, bank_id,
                                   CASE
                                      WHEN bank_account_name = 'IPTK CASH' THEN 'IPTK CASH'
                                      ELSE 'IPTK BANK'
                                   END
                                      receipt_method
                              from BILLING_NBS.mst_bank_simkeu
                              where org_id = '88'";
        $rwsql_bank            = DB::connection('uster')->select($query_nota);

        $html = '';

        $html .= '<select id="via" class="form-control">';

        foreach ($rwsql_bank as $key) {


            $html .= '<option value="' . $key->receipt_account . '">' . $key->receipt_account . '</option>';
        }

        $html .= '</select>';

        $response = [
            'status' => 'success',
            'output'  => $html,
            'message' => 'Berhasil Parsing',
        ];
        return json_encode($response);
    }

    function savePaymentPraya(Request $request)
    {

        $viewData = $this->paymentCash->savePaymentPraya($request);
        return response()->json($viewData);
    }
}
