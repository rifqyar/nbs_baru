<?php

namespace App\Http\Controllers\Billing\NotaStripping;

use App\Http\Controllers\Controller;
use App\Services\Billing\NotaStripping\NotaStrippingServices;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Yajra\DataTables\DataTables;

class NotaStrippingController extends Controller
{
    protected $service;
    public function __construct(NotaStrippingServices $notaService)
    {
        $this->service = $notaService;
    }

    public function index()
    {
        return view('billing.stripping.notastripping.index');
    }

    public function data(Request $request)
    {
        $data = $this->service->getData($request);

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('tgl_request', function ($data) {
                return '<span class="badge badge-pill badge-info p-2"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tgl_request)->translatedFormat('d M Y') . '</span>';
            })
            ->editColumn('action', function ($data) {
                $btn = self::cekNota($data->no_request);
                return $btn;
            })
            ->editColumn('do_bl', function ($data) {
                return $data->no_do . ' | ' . $data->no_bl;
            })
            ->rawColumns(['tgl_request', 'action', 'do_bl'])
            ->make(true);
    }

    private function previewRelokBtn($koreksi, $no_req){
        $btn = '<a href="'.route('uster.billing.nota_stripping.print.preview_relok', ['no_req' => $no_req, 'n' => '999', 'koreksi' => $koreksi]).'" target="_blank" class="link font-14" data-toggle="tooltip" data-placement="top" title="Preview Proforma Relokasi MTY"> <b><i> Preview Proforma Relokasi MTY</i></b></a> ';
        return $btn;
    }

    private function previewProformaBtn($koreksi, $no_req){
        $btn = '<a href="'.route('uster.billing.nota_stripping.print.preview_proforma', ['no_req' => $no_req, 'n' => '999', 'koreksi' => $koreksi]).'" target="_blank" class="link font-14" data-toggle="tooltip" data-placement="top" title="Preview Proforma Stripping"> <b><i> Preview Proforma Stripping</i></b></a> ';
        return $btn;
    }

    public function cekNOta($no_req)
    {
        $query_cek    = "SELECT NOTA, KOREKSI, NOTA_PNKN, KOREKSI_PNKN, CASE WHEN TRUNC(TGL_REQUEST) < TO_DATE('01/01/2015','DD/MM/RRRR') THEN 'NO'
		                    ELSE 'YES' END STATUS_CUTOFF, NO_NOTA FROM REQUEST_STRIPPING LEFT JOIN NOTA_STRIPPING ON REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST WHERE REQUEST_STRIPPING.NO_REQUEST = '$no_req'";
        $row_cek     = DB::connection('uster')->selectOne($query_cek);

        $btn = '';
        if (!empty($row_cek)) {
            $cetak        = $row_cek->nota;
            $cetak1        = $row_cek->nota_pnkn;
            $lunas        = $row_cek->koreksi;
            $lunas1        = $row_cek->koreksi_pnkn;
            $req        = $no_req;
            $no_req = base64_encode($no_req);
            $notas      = $row_cek->no_nota;

            $cetakProformaStripBtn = '<a href="'.route('uster.billing.nota_stripping.print.print_proforma', 'no_req='.$no_req).'" target="_blank" class="link font-14" data-toggle="tooltip" data-placement="top" title="Cetak Proforma Stripping"><b><i> Cetak Proforma Stripping </i></b></a>';
            $cetakRelokasiBtn = '<a href="'.route('uster.billing.nota_stripping.print.print_relok', 'no_req='.$no_req).'" target="_blank" class="link font-14" data-toggle="tooltip" data-placement="top" title="Cetak Proforma Relokasi MTY"> <b><i> Cetak Proforma Relokasi MTY </i></b></a> ';

            if ($row_cek->status_cutoff == 'YES') {
                if ($row_cek->nota <> 'Y' && $row_cek->koreksi <> 'Y' && $row_cek->nota_pnkn <> 'Y' && $row_cek->koreksi_pnkn <> 'Y') {
                    $btn .= $this->previewProformaBtn('N', $no_req);
                    $btn .= $this->previewRelokBtn('N', $no_req);
                } else if ($row_cek->nota == 'Y' && $row_cek->koreksi <> 'Y' && $row_cek->nota_pnkn <> 'Y' && $row_cek->koreksi_pnkn <> 'Y') {
                    $btn = $cetakProformaStripBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc(\"$req\",\"$notas\")' data-toggle='tooltip' data-placement='top' title='Recalculate Stripping'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a> <br>";

                    $btn .= $this->previewRelokBtn('N', $no_req);
                } else if (($row_cek->nota <> 'Y') and ($row_cek->koreksi <> 'Y') and ($row_cek->nota_pnkn == 'Y') and ($row_cek->koreksi_pnkn <> 'Y')) {
                    $btn .= $this->previewProformaBtn('N', $no_req);

                    $btn .= $cetakRelokasiBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc_relok(\"$req\",\"$notas\")' data-toggle='tooltip' data-placement='top' title='Recalculate Relokasi'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a>";
                } else if (($row_cek->nota == 'Y') and ($row_cek->koreksi <> 'Y') and ($row_cek->nota_pnkn == 'Y') and ($row_cek->koreksi_pnkn <> 'Y')) {
                    $btn = $cetakProformaStripBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc(\"$req\",\"$notas\")' data-toggle='tooltip' data-placement='top' title='Recalculate Stripping'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a> <br>";

                    $btn .= $cetakRelokasiBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc_relok(\"$req\",\"$notas\")' data-toggle='tooltip' data-placement='top' title='Recalculate Relokasi'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a> <br>";
                } else if (($row_cek->nota <> 'Y') and ($row_cek->koreksi == 'Y') and ($row_cek->nota_pnkn <> 'Y') and ($row_cek->koreksi_pnkn == 'Y')) {
                    $btn .= $this->previewProformaBtn('N', $no_req);
                    $btn .= $this->previewRelokBtn('Y', $no_req);
                } else if (($row_cek->nota <> 'Y') and ($row_cek->koreksi == 'Y') and ($row_cek->nota_pnkn <> 'Y') and ($row_cek->koreksi_pnkn <> 'Y')) {
                    $btn .= $this->previewProformaBtn('N', $no_req);
                    $btn .= $this->previewRelokBtn('Y', $no_req);
                } else if (($row_cek->nota == 'Y') and ($row_cek->koreksi == 'Y') and ($row_cek->nota_pnkn <> 'Y') and ($row_cek->koreksi_pnkn == 'Y')) {
                    $btn = $cetakProformaStripBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc(\"$req\",\"$notas\")' data-toggle='tooltip' data-placement='top' title='Recalculate Stripping'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a> <br>";

                    $btn .= $this->previewRelokBtn('Y', $no_req);
                } else if (($row_cek->nota <> 'Y') and ($row_cek->koreksi == 'Y') and ($row_cek->nota_pnkn == 'Y') and ($row_cek->koreksi_pnkn == 'Y')) {
                    $btn .= $this->previewProformaBtn('N', $no_req);

                    $btn .= $cetakRelokasiBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc_relok(\"$req\",\"$notas\")' data-placement='top' title='Recalculate Relokasi'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a>";
                } else if (($row_cek->nota == 'Y') and ($row_cek->koreksi == 'Y') and ($row_cek->nota_pnkn == 'Y') and ($row_cek->koreksi_pnkn == 'Y')) {
                    $btn = $cetakProformaStripBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc(\"$req\",\"$notas\")' data-toggle='tooltip' data-placement='top' title='Recalculate Stripping'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a> <br>";

                    $btn .= $cetakRelokasiBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc_relok(\"$req\",\"$notas\")' data-placement='top' title='Recalculate Relokasi'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a>";
                } else if (($row_cek->nota == 'N') and ($row_cek->koreksi == 'Y') and ($row_cek->nota_pnkn == 'Y') and ($row_cek->koreksi_pnkn <> 'Y')) {
                    $btn .= $this->previewProformaBtn('N', $no_req);

                    $btn .= $cetakRelokasiBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc_relok(\"$req\",\"$notas\")' data-placement='top' title='Recalculate Relokasi'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a>";
                } else if (($row_cek->nota == 'Y') and ($row_cek->koreksi == 'Y') and ($row_cek->nota_pnkn == 'Y') and ($row_cek->koreksi_pnkn <> 'Y')) {
                    $btn .= $this->previewProformaBtn('N', $no_req);

                    $btn .= $cetakRelokasiBtn;
                    $btn .= " | <a href='javascript:void(0)' onclick='recalc_relok(\"$req\",\"$notas\")' data-placement='top' title='Recalculate Relokasi'> <i class='fa fa-calculator text-success h6 mr-2'></i> </a>";
                }
            } else {
                $btn = $cetakProformaStripBtn . "<br>" . $cetakRelokasiBtn;
            }

            return $btn;
        }
    }

    public function printProformaStrip(Request $request)
    {
        $no_req = base64_decode($request->no_req);
        $allData = $this->service->fetchData($no_req);

        $date = date("d M Y H:i:s");

        $generator = new BarcodeGeneratorPNG();
        $nota = $allData['data']->no_nota_mti ?? 'NOT_FOUND';
        $barcode = $generator->getBarcode($nota, $generator::TYPE_CODE_128);
        $data = $allData;
        $data['barcode'] = $barcode;
        $data['date'] = $date;

        $pdf = Pdf::loadView('billing.stripping.notastripping.printproforma', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('invoice.pdf');
    }

    public function printProformaRelok(Request $request)
    {
        $no_req = base64_decode($request->no_req);
        $allData = $this->service->fetchDataRelokMty($no_req);

        $req_tgl = $allData['data']->tgl_request;
        $date = date("d M Y H:i:s");

        $generator = new BarcodeGeneratorPNG();
        $nota = $allData['data']->no_nota_mti ?? 'NOT_FOUND';
        $barcode = $generator->getBarcode($nota, $generator::TYPE_CODE_128);
        $data = $allData;
        $data['barcode'] = $barcode;
        $data['date'] = $date;

        $pdf = Pdf::loadView('billing.stripping.notastripping.printrelokmty', $data);
        $pdf->setPaper('a7');
        $pdf->setOption('margin-top', 1);
        $pdf->setOption('margin-right', 1);
        $pdf->setOption('margin-bottom', 1);
        $pdf->setOption('margin-left', 1);
        return $pdf->stream('invoice.pdf');
    }

    public function previewProformaRelok(Request $request)
    {
        $no_req = base64_decode($request->no_req);
        $koreksi = $request->koreksi;
        $data = $this->service->previewNotaRelok($no_req, $koreksi);
        $data = $data->getData(true);
        $data['row_nota'] = json_decode(json_encode($data['row_nota']));

        return view('billing.stripping.notastripping.previewrelok', $data);
    }

    public function previewProforma(Request $request)
    {
        $no_req = base64_decode($request->no_req);
        $koreksi = $request->koreksi;
        $data = $this->service->previewNota($no_req, $koreksi);
        $data = $data->getData(true);

        if(isset($data['st_nota']) && $data['st_nota'] == 'Y'){
            return response()->redirectToRoute('uster.billing.nota_stripping.print.print_proforma', ['no_req' => base64_encode($no_req)]);
        } else {
            $data['row_nota'] = json_decode(json_encode($data['row_nota']));
            return view('billing.stripping.notastripping.previewproforma', $data);
        }
    }

    public function insertProformaRelokMTY(Request $request,$no_req)
    {
        $no_req = base64_decode($no_req);
        $query_cek_nota 	= "SELECT NO_NOTA, STATUS FROM NOTA_RELOKASI_MTY WHERE NO_REQUEST = '$no_req'";
        $nota				= DB::connection('uster')->selectOne($query_cek_nota);
        $no_nota_cek		= $nota->no_nota;
        $nota_status		= $nota->status;

        try {
            if (($no_nota_cek != NULL && $nota_status == 'BATAL') || ($no_nota_cek == NULL && $nota_status == NULL)){
                $saveProforma = $this->service->insertProformaRelokMty($no_req, $request->koreksi);
                if($saveProforma->getStatusCode() != 200){
                    throw new Exception('Gagal Menyimpan Proforma Nota', 500);
                }
            } else {
                return response()->redirectToRoute('uster.billing.nota_stripping.print.print_relok', ['no_req' => base64_encode($no_req)]);
            }

            return response()->redirectToRoute('uster.billing.nota_stripping.print.print_relok', ['no_req' => base64_encode($no_req), 'first=1']);
        } catch (Exception $th) {
            return redirect()->back()->with(['error' => 'Gagal Menyimpan Proforma Nota Ini, Harap Coba Lagi Nanti']);
        }
    }

    public function insertProformaStripping(Request $request, $no_req)
    {
        $no_req = base64_decode($no_req);
        $query_cek_nota 	= "SELECT NO_NOTA, STATUS FROM NOTA_STRIPPING WHERE NO_REQUEST = '$no_req'";
        $nota				= DB::connection('uster')->selectOne($query_cek_nota);
        $no_nota_cek		= $nota->no_nota;
        $nota_status		= $nota->status;

        try {
            if (($no_nota_cek != NULL && $nota_status == 'BATAL') || ($no_nota_cek == NULL && $nota_status == NULL)){
                $saveProforma = $this->service->insertProformaStripping($no_req, $request->koreksi);
                if($saveProforma->getStatusCode() != 200){
                    throw new Exception('Gagal Menyimpan Proforma Nota', 500);
                }
            } else {
                return response()->redirectToRoute('uster.billing.nota_stripping.print.print_proforma', ['no_req' => base64_encode($no_req)]);
            }

            return response()->redirectToRoute('uster.billing.nota_stripping.print.print_proforma', ['no_req' => base64_encode($no_req), 'first=1']);
        } catch (Exception $th) {
            return redirect()->back()->with(['error' => 'Gagal Menyimpan Proforma Nota Ini, Harap Coba Lagi Nanti']);
        }
    }
}
