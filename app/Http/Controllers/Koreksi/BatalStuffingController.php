<?php

namespace App\Http\Controllers\Koreksi;

use App\Http\Controllers\Controller;
use App\Services\Koreksi\BatalStuffingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatalStuffingController extends Controller
{
    protected $service;
    public function __construct(BatalStuffingService $batalService)
    {
        $this->service = $batalService;
    }

    public function index()
    {
        return view('koreksi.batalstuffing.index');
    }

    public function dataCont(Request $request)
    {
        $data['cont'] = $this->service->getCont($request->search);
        return response()->json($data['cont']);
    }

    public function batalCont(Request $request)
    {
        try {
            $doBatal = $this->service->processBatal($request);
            $statusCode = $doBatal->getData()->code;

            if($statusCode != 200){
                throw new Exception('Gagal Melakukan Batal Container Stuffing',500);
            }

            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Batal Container Stuffing',
                'redirect' => [
                    'need' => false,
                    'to' => null,
                ]
            ]);
        } catch (Exception $th) {
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
