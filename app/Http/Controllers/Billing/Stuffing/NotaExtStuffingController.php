<?php

namespace App\Http\Controllers\Billing\Stuffing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Billing\Stuffing\NotaStuffingExt;
use \Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Illuminate\Support\Facades\Validator;

class NotaExtStuffingController extends Controller
{

    protected $stuffing;

    public function __construct(NotaStuffingExt $stuffing)
    {
        $this->stuffing = $stuffing;
    }


    function index()
    {
        return view('billing.stuffing.perpanjangan.nota_ext');
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
            return redirect()->route('uster.billing.nota_ext_pnkn_stuffing');
        }

        $data = array();
        $data =  $this->stuffing->PrintProforma($request->input('no_req'));

        if ($data == 'NOT_FOUND') {
            return redirect()->route('uster.billing.nota_stuffing');
        }
        $generator = new BarcodeGeneratorPNG();
        $nota = $data['data']->no_nota_mti ?? 'NOT_FOUND';
        $barcode = $generator->getBarcode($nota, $generator::TYPE_CODE_128);
        $data['barcode'] = $barcode;

        $pdf = Pdf::loadView('billing.stuffing.perpanjangan.print.proforma', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('invoice.pdf');
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
            return redirect()->route('uster.billing.nota_ext_pnkn_stuffing');
        }

        $data = array();
        $data =  $this->stuffing->previewProforma($request->input('no_req'), $request->koreksi);
        if ($data == 'NOT_FOUND') {
            return redirect()->route('uster.billing.nota_stuffing');
        }

        return view('billing.stuffing.perpanjangan.print.previewproforma', $data);

    }

    function InsertProforma(Request $Request)
    {
        $data =  $this->stuffing->InsertProforma($Request);
        // return redirect()->route('uster.billing.nota_ext_pnkn_stuffing.print_proforma', ['no_req' => $Request->no_request]);
        return response()->json($data);
    }
}
