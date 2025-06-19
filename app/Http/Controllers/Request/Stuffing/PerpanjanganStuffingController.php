<?php

namespace App\Http\Controllers\Request\Stuffing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Request\Stuffing\PerpanjanganService;
use Illuminate\Http\JsonResponse;
use \Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class PerpanjanganStuffingController extends Controller
{
    protected $stuffing;

    public function __construct(PerpanjanganService $stuffing)
    {
        $this->stuffing = $stuffing;
    }

    public function index()
    {
        return view('request.stuffing.perpanjangan.index');
    }



    public function view(Request $request)
    {
        $request->validate([
            'no_req' => 'required',
        ]);
        $list = $this->stuffing->viewPerpajanganStuffing($request->no_req);

        return view('request.stuffing.perpanjangan.view', $list);
    }

    public function infoContainerPerpanjanganStuffing()
    {
        $data = $this->stuffing->getInfoPerpanjanganStuffing();

        return view('request.stuffing.perpanjangan.info', $data);
    }

    public function checkClose(Request $request)
    {
        return $this->stuffing->checkClose($request->input('no_booking'));
    }


    public function addContainer(Request $request)
    {
        return $this->stuffing->addContainer($request);
    }



    public function datatable(Request $request): JsonResponse
    {
        $listStuffing = $this->stuffing->listPerpanjanganStuffing($request);
        return Datatables::of($listStuffing)
            ->addColumn('action', function ($listStuffing) {
                if (isset($listStuffing->o_idvsb) || $listStuffing->o_idvsb == null) {
                    return $this->stuffing->checkNotaPerpanjanganStuffing($listStuffing);
                }
            })
            ->make(true);
    }

    public function listContainer($no_req)
    {
        $listStuffing = $this->stuffing->listContainerViewPerpanjangan($no_req);
        return Datatables::of($listStuffing)
            ->addColumn('tgl_approve_input', function ($listStuffing) {
                $date = $listStuffing->end_stack_pnkn ?? '';
                return "<input class='form-control'  type='date' name='TGL_APPROVE[]' value='$date'  />";
            })
            ->addColumn('action', function ($listStuffing) {
                return '<submit class="btn btn-info w-100" onclick="info_lapangan();">Info</submit>';
            })
            ->make(true);
    }


}
