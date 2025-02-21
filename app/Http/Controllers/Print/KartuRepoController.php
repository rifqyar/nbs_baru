<?php

namespace App\Http\Controllers\Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Print\KartuRepo;
use Yajra\DataTables\Facades\DataTables;
use Exception;
class KartuRepoController extends Controller
{

    protected $kartu;

    public function __construct(KartuRepo $kartu)
    {
        $this->kartu = $kartu;
    }

    function KartuRepo()
    {
        return view('print.repo.kartu-repo');
    }

    function GetPrayaNota(Request $request)
    {
        $requestId = $request->no_req;
        $containerNo = $request->no_cont;

        try {
            $payload = array(
                "orgId" => ENV('PRAYA_ITPK_PNK_ORG_ID'),
                "terminalId" => ENV('PRAYA_ITPK_PNK_TERMINAL_ID'),
                "requestId" => $requestId,
                "changeBy" => "uster",
                "changeDate" => date('Y-m-d'),
                "password" => ENV('PRAYA_ITPK_PNK_PASS_PRINT'),
                "containerNo" => $containerNo
            );

            // echo var_dump($payload);die;

            $response = sendDataFromUrl($payload, ENV('PRAYA_API_PROFORMA') . "/api/printCard", 'POST', getTokenPraya());
            $response = json_decode($response['response'], true);

            if ($response['code'] == 1 && !empty($response["dataRec"])) {
                // echo json_encode($response['dataRec']['fn']);

                $pdfUrl = $response['dataRec']['fn'];
                header("Location: " . $pdfUrl);
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }


    function GetCardRepo(Request $request)
    {
        $data = $this->kartu->GetCardRepo($request);
        return DataTables::of($data)
            ->addColumn('action', function ($repo) {
                return $this->kartu->GetAction($repo->no_request);
            })->make(true);
    }
}
