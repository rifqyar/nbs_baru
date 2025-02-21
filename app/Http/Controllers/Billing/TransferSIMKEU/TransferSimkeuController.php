<?php

namespace App\Http\Controllers\Billing\TransferSIMKEU;

use App\Http\Controllers\Controller;
use App\Services\Billing\TransferSIMKEU\TransferSimkeuService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class TransferSimkeuController extends Controller
{
    protected $service;
    public function __construct(TransferSimkeuService $simkeuService)
    {
        $this->service = $simkeuService;
    }

    public function index(){
        return view('billing.transfersimkeu.index');
    }

    public function getData(Request $request)
    {
        $data = $this->service->getData($request);
        $blade = view('billing.transfersimkeu.nota-list', compact('data'))->render();

        return response()->json([
            'status' => [
                'code' => 200,
                'msg' => 'OK'
            ], 'view' => $blade
        ], 200);
    }

    public function transfer(Request $request)
    {
        try {
            $process = $this->service->doTransfer($request);

            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil Tambah Request Stripping',
                'data' => $process->getData()->data,
                'redirect' => [
                    'need' => false,
                    'to' => null
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
