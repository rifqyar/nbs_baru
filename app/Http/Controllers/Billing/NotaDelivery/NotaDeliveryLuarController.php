<?php

namespace App\Http\Controllers\Billing\NotaDelivery;

use App\Http\Controllers\Controller;
use App\Services\Billing\NotaDelivery\NotaDeliveryLuarService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Yajra\DataTables\Facades\DataTables;

class NotaDeliveryLuarController extends Controller
{
    protected $delivery;

    public function __construct(NotaDeliveryLuarService $delivery)
    {
        $this->delivery = $delivery;
    }

    function index()
    {
        return view('billing.notadeliveryluar.index');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->delivery->dataDeliveryLuar($request);
        return DataTables::of($listDelivery)->make(true);
    }

    function printProforma(Request $request)
    {
        // Generate barcode
        $validator = Validator::make($request->all(), [
            'no_req' => 'required', // Sesuaikan dengan aturan validasi yang Anda butuhkan
        ]);

        // Periksa apakah validasi gagal
        if ($validator->fails()) {
            return redirect()->route('uster.billing.notadelivery');
        }

        $data = array();
        $data =  $this->delivery->PrintProforma($request);
        if ($data == 'NOT_FOUND') {
            return redirect()->route('uster.billing.notadelivery');
        }
        $generator = new BarcodeGeneratorPNG();
        $nota = $data['data']->no_nota_mti ?? 'NOT_FOUND';
        $barcode = $generator->getBarcode($nota, $generator::TYPE_CODE_128);
        $data['barcode'] = $barcode;


        $pdf = Pdf::loadView('billing.notadeliveryluar.print.proforma', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('invoice.pdf');
    }

    function printProformaPnkn(Request $request)
    {
        // Generate barcode
        $validator = Validator::make($request->all(), [
            'no_req' => 'required', // Sesuaikan dengan aturan validasi yang Anda butuhkan
        ]);

        // Periksa apakah validasi gagal
        if ($validator->fails()) {
            return redirect()->route('uster.billing.notadelivery');
        }

        $data = array();
        $data =  $this->delivery->PrintProformaPnkn($request);
        if ($data == 'NOT_FOUND') {
            return redirect()->route('uster.billing.notadelivery');
        }
        $generator = new BarcodeGeneratorPNG();
        $nota = $data['data']->no_nota_mti ?? 'NOT_FOUND';
        $barcode = $generator->getBarcode($nota, $generator::TYPE_CODE_128);
        $data['barcode'] = $barcode;


        $pdf = Pdf::loadView('billing.notadeliveryluar.print.proformapnkn', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('invoice.pdf');
    }

    function printNota(Request $request)
    {

        $data = $this->delivery->printNota($request);
        return view('billing.notadeliveryluar.printnota', $data);
    }

    function printNotaPnkn(Request $request)
    {

        $data = $this->delivery->printNotaPnkn($request);
        return view('billing.notadeliveryluar.printnota', $data);
    }

    function recalc(Request $Request)
    {
        $data =  $this->delivery->recalc($Request);
        return response()->json($data);
    }

    function recalcPnkn(Request $Request)
    {
        $data =  $this->delivery->recalcPnkn($Request);
        return response()->json($data);
    }
}
