<?php

namespace App\Http\Controllers\Request\BatalSPPS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Request\BatalStuffing\BatalStuffingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
class BatalStuffingController extends Controller
{
    private $batalStuffing;
    public function __construct(BatalStuffingService $batalStuffing)
    {
        $this->batalStuffing = $batalStuffing;
    }

    public function index()
    {
        return view('request.batal-stuffing.index');
    }

    public function add()
    {
        return view('request.batal-stuffing.add');
    }

    public function getContData(Request $request)
    {
        $data['Container'] = $this->batalStuffing->getContainer($request->search);
        return response()->json($data['Container']);
    }
    
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $insert = $this->batalStuffing->insertReq($request);
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
}
