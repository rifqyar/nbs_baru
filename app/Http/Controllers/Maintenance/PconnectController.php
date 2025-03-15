<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use App\Services\Maintenance\Pconect\PconectService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
class PconnectController extends Controller
{
    protected $pconect;

    public function __construct(PconectService $pconect)
    {
        $this->pconect = $pconect;
    }

    public function index()
    {
        return view('maintenance.pconect');
    }

  

    public function data(Request $request)
    {
        
        $data = $this->pconect->getNoNpwp($request);
        $getdata=collect($data)->first();
        
        if ($data) {
            $namaPerusahaan = strtoupper($getdata->nama);
            $namaPerusahaan = preg_replace('/\s+PT$/', '', $namaPerusahaan);
            $kata = explode(' ', $namaPerusahaan);
            $singkatan = '';

            foreach ($kata as $k) {
                $singkatan .= substr($k, 0, 1);
            }
            $this->pconect->MstPelanggan($getdata,$singkatan);
        }
        

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('action', function ($data) {
                return self::renderAction($data);
            })
            ->make(true);
    }

    private function renderAction($data)
    {
        $nonpwp = base64_encode($data->npwp);
       
        $html = '<a href="' . url('/maintenance/pconnect/view/' . $nonpwp) . '" class="badge badge-pill badge-warning p-2 w-100"> View <i class="mdi mdi-check-circle ml-1"></i> </a>';

        return $html;
    }


    public function view($nonpwp)
    {
        // Validate Paid
        $no_npwp = base64_decode($nonpwp);
        

        $data['request'] = $this->pconect->getviewData($no_npwp);
        
        $data['request'] = $data['request'][0];
        $data['overview'] = false;

        return view('maintenance.view-pconect', $data);
    }

    
}
