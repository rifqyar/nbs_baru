<?php

namespace App\Http\Controllers\Billing\NotaDelivery;

use App\Http\Controllers\Controller;
use App\Services\Billing\NotaDelivery\NotaPerpanjanganDeliveryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Yajra\DataTables\Facades\DataTables;

class NotaPerpanjanganDeliveryController extends Controller
{
    protected $delivery;

    public function __construct(NotaPerpanjanganDeliveryService $delivery)
    {
        $this->delivery = $delivery;
    }

    function index()
    {
        return view('billing.notaperpanjangandelivery.index');
    }

    function dataTables(Request $request): JsonResponse
    {

        $listDelivery = $this->delivery->dataPerpanjanganDelivery($request);
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
            return redirect()->route('uster.billing.notaperpanjangandelivery');
        }

        $data = array();
        $data =  $this->delivery->PrintProforma($request);
        if ($data == 'NOT_FOUND') {
            return redirect()->route('uster.billing.notaperpanjangandelivery');
        }
        $generator = new BarcodeGeneratorPNG();
        $nota = $data['data']->no_nota_mti ?? 'NOT_FOUND';
        $barcode = $generator->getBarcode($nota, $generator::TYPE_CODE_128);
        $data['barcode'] = $barcode;


        $pdf = Pdf::loadView('billing.notaperpanjangandelivery.print.proforma', $data);
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
    function insertProforma(Request $request)
    {
        $this->delivery->insertProforma($request);
        $this->printProforma($request);
    }
}
