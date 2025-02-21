<?php

namespace App\Http\Controllers\Request\BatalSPPS;

use App\Http\Controllers\Controller;
use App\Services\Request\BatalSpps\BatalSppsService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class BatalSppsController extends Controller
{
    protected $service;

    public function __construct(BatalSppsService $batalSpps)
    {
        $this->service = $batalSpps;
    }

    public function index()
    {
        return view('request.batal-spps.index');
    }

    public function data()
    {
        $data = $this->service->getData();

        return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('tgl_batal', function($data){
                    $html = '<span class="badge badge-pill badge-success p-2 w-100"><i class="mdi mdi-calendar"></i> ' . Carbon::parse($data->tanggal_pembuatan)->translatedFormat('d M Y H:i') . ' </span>';
                    return $html;
                })
                ->editColumn('no_ba', function($data){
                    $html = '<b class="text-info text-center font-weight-bold"> ' . $data->no_ba . ' </b>';
                    return $html;
                })
                ->rawColumns(['tgl_batal', 'no_ba'])
                ->make(true);
    }

    public function add()
    {
        return view('request.batal-spps.add');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $insert = $this->service->insertReq($request);
            if($insert->getData()->status->code != 200)
            {
                throw new Exception('Gagal Insert Request Batal SPPS', 500);
            }

            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Input Request Batal SPPS',
                'redirect' => [
                    'need' => true,
                    'to' => route('uster.new_request.batal_spps'),
                ]
            ],JsonResponse::HTTP_OK);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ]);
        }
    }

    public function getContData(Request $request)
    {
        $data['Container'] = $this->service->getContainer($request->search);
        return response()->json($data['Container']);
    }
}
