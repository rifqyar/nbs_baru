<?php

namespace App\Http\Controllers\Billing\Stuffing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Billing\Stuffing\NotaStuffingPlan;
use Illuminate\Http\JsonResponse;
use \Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;

class NotaStuffingController extends Controller
{
    protected $stuffing;

    public function __construct(NotaStuffingPlan $stuffing)
    {
        $this->stuffing = $stuffing;
    }

    function index()
    {
        return view('billing.stuffing.plan.nota');
    }

    function datatable(Request $request)
    {
        $listStuffing = $this->stuffing->listNota($request);
        return Datatables::of($listStuffing)
            ->addColumn('action', function ($listStuffing) {
                return $this->stuffing->checkNotaPerencaaanStuffing($listStuffing->no_request);
            })
            ->make(true);
    }

    function PrintProforma(Request $request)
    {
        // Generate barcode
        $validator = Validator::make($request->all(), [
            'no_req' => 'required', // Sesuaikan dengan aturan validasi yang Anda butuhkan
        ]);

        // Periksa apakah validasi gagal
        if ($validator->fails()) {
            return redirect()->route('uster.billing.nota_stuffing');
        }

        $data = array();
        $data =  $this->stuffing->PrintProforma($request->input('no_req'));
        if($data == 'NOT_FOUND'){
            return redirect()->route('uster.billing.nota_stuffing');
        }

        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($data['data']->no_nota_mti, $generator::TYPE_CODE_128);
        $date = date("d M Y H:i:s");
        $data['barcode'] = $barcode;
        $data['date'] = $date;

        $pdf = Pdf::loadView('billing.stuffing.plan.print.proforma', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('invoice.pdf');
    }

    function PrintProformPNKN(Request $request)
    {
        // Generate barcode

        $validator = Validator::make($request->all(), [
            'no_req' => 'required', // Sesuaikan dengan aturan validasi yang Anda butuhkan
        ]);

        // Periksa apakah validasi gagal
        if ($validator->fails()) {
            return redirect()->route('uster.billing.nota_stuffing');
        }

        $data = array();
        $data =  $this->stuffing->PrintProformaPNKN($request->input('no_req'));
        if($data == 'NOT_FOUND'){
            return redirect()->route('uster.billing.nota_stuffing');
        }
        $generator = new BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($data['data']->no_nota_mti, $generator::TYPE_CODE_128);
        $date = date("d M Y H:i:s");
        $data['barcode'] = $barcode;
        $data['date'] = $date;

        $pdf = Pdf::loadView('billing.stuffing.plan.print.proforma_pnkn', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('invoice.pdf');
    }

    function PrintNota(Request $request)
    {
        // Generate barcode

        $validator = Validator::make($request->all(), [
            'no_req' => 'required', // Sesuaikan dengan aturan validasi yang Anda butuhkan
        ]);

        // Periksa apakah validasi gagal
        if ($validator->fails()) {
            return redirect()->route('uster.billing.nota_stuffing');
        }

        $data = array();
        $data =  $this->stuffing->previewProforma($request->input('no_req'), $request->koreksi);

        return view('billing.stuffing.plan.print.previewproforma', $data);
    }

    function PrintNotaPNKN(Request $request)
    {
        // Generate barcode

        $validator = Validator::make($request->all(), [
            'no_req' => 'required', // Sesuaikan dengan aturan validasi yang Anda butuhkan
        ]);

        // Periksa apakah validasi gagal
        if ($validator->fails()) {
            return redirect()->route('uster.billing.nota_stuffing');
        }

        $data = array();
        $data =  $this->stuffing->previewProformaPNKN($request->input('no_req'), $request->koreksi);
        return view('billing.stuffing.plan.print.previewproforma_pnkn', $data);
    }

    function recalcStuffing(Request $Request)
    {
        $data =  $this->stuffing->recalcStuffing($Request);
        return response()->json($data);
    }

    function recalcStuffingPNKN(Request $Request)
    {
        $data =  $this->stuffing->recalcStuffingPNKN($Request);
        return response()->json($data);
    }

    function InsertProforma(Request $Request)
    {
        $data =  $this->stuffing->InsertProforma($Request);
        return response()->json($data);
    }

    function InsertProformaPNKN(Request $Request)
    {
        $data =  $this->stuffing->InsertProformaPNKN($Request);
        return response()->json($data);
    }
}
