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
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('status', function ($data) {
                return self::checkStatusData($data, 'info');
            })
            ->editColumn('action', function ($data) {
                return self::renderAction($data);
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    private function checkStatusData($data, $type)
    {
        $check = DB::connection('uster')->table('MST_PELANGGAN')
            ->select(DB::raw("COUNT(1) as total"))
            ->where(DB::raw("REPLACE(REPLACE(NO_NPWP_PBM, '.', ''), '-', '')"), $data->npwp)
            ->orWhere(DB::raw("REPLACE(REPLACE(NO_NPWP_PBM16, '.', ''), '-', '')"), $data->npwp)
            ->value('total');

        if ((int)$check > 0) {
            $mstPelanggan = DB::connection('uster')->table('MST_PELANGGAN')
                ->select(['no_account_pbm', 'nm_pbm', 'almt_pbm', DB::raw("REPLACE(REPLACE(NO_NPWP_PBM16, '.', ''), '-', '') as no_npwp_pbm16"), 'no_telp'])
                ->where(DB::raw("REPLACE(REPLACE(NO_NPWP_PBM, '.', ''), '-', '')"), $data->npwp)
                ->orWhere(DB::raw("REPLACE(REPLACE(NO_NPWP_PBM16, '.', ''), '-', '')"), $data->npwp)
                ->first();

            $mstPelanggan = (array)$mstPelanggan;
            $keySap = ['nama', 'alamat', 'new_npwp', 'telepon'];
            $keyMst = ['nm_pbm', 'almt_pbm', 'no_npwp_pbm16', 'no_telp'];

            $data = (array)$data;
            $queryUpdate = "UPDATE MST_PELANGGAN SET ";
            $doUpdate = false;
            for ($i = 0; $i < count($keySap); $i++) {
                if ($mstPelanggan[$keyMst[$i]] != $data[$keySap[$i]]) {
                    $queryUpdate .= $keyMst[$i] . " = '" . $data[$keySap[$i]] . "', ";

                    $doUpdate = true;
                }
            }
            $queryUpdate = str_replace(', ', ' ', $queryUpdate);
            $queryUpdate .= "WHERE NO_ACCOUNT_PBM = '" . $mstPelanggan['no_account_pbm'] . "'";

            if ($type == 'info') {
                if ($doUpdate) {
                    return '<span class="badge badge-pill badge-warning p-2"> Terdapat Perbedaan Data, Harap Update Data </span>';
                } else {
                    return '<span class="badge badge-pill badge-success p-2"> Data Sinkron </span>';
                }
            } else {
                if ($doUpdate) {
                    return '001';
                } else {
                    return '000';
                }
            }
        } else {
            if ($type == 'info') {
                return '<span class="badge badge-pill badge-danger p-2"> Belum Terdaftar di NBS </span>';
            } else {
                return '002';
            }
        }
    }

    private function renderAction($data)
    {
        $nonpwp = base64_encode($data->npwp);

        $html = '<a href="' . url('/maintenance/pconnect/view/' . $nonpwp) . '" target="_blank" class="btn btn-sm btn-rounded btn-info p-2 w-100"> <i class="mdi mdi-information ml-1"></i> View </a> </br>';
        $statusData = self::checkStatusData($data, 'status');
        if ($statusData == '001') {
            $html .= '<button class="btn btn-sm btn-rounded btn-warning p-2 w-100 mt-2" onclick=updateData(`' . $data->npwp . '`)> Update Data </button>';
        } else if ($statusData == '002') {
            $html .= '<button class="btn btn-sm btn-rounded btn-primary p-2 w-100 mt-2" onclick=insertData(`' . $data->npwp . '`)> Insert Data </button>';
        }

        return $html;
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->pconect->getNoNpwp($request);
            $data = collect($data)->first();
            $data->alamat = str_replace("'", " ", $data->alamat);

            $check = DB::connection('uster')->table('MST_PELANGGAN')
                ->select(DB::raw("COUNT(1) as total"))
                ->where(DB::raw("REPLACE(REPLACE(NO_NPWP_PBM, '.', ''), '-', '')"), $data->npwp)
                ->orWhere(DB::raw("REPLACE(REPLACE(NO_NPWP_PBM16, '.', ''), '-', '')"), $data->npwp)
                ->value('total');

            if ((int)$check > 0) {
                $mstPelanggan = DB::connection('uster')->table('MST_PELANGGAN')
                    ->select(['no_account_pbm', 'nm_pbm', 'almt_pbm', DB::raw("REPLACE(REPLACE(NO_NPWP_PBM16, '.', ''), '-', '') as no_npwp_pbm16"), 'no_telp', 'no_npwp_pbm'])
                    ->where(DB::raw("REPLACE(REPLACE(NO_NPWP_PBM, '.', ''), '-', '')"), $data->npwp)
                    ->orWhere(DB::raw("REPLACE(REPLACE(NO_NPWP_PBM16, '.', ''), '-', '')"), $data->npwp)
                    ->first();

                $mstPelanggan = (array)$mstPelanggan;
                $keySap = ['nama', 'alamat', 'new_npwp', 'npwp', 'telepon'];
                $keyMst = ['nm_pbm', 'almt_pbm', 'no_npwp_pbm16', 'no_npwp_pbm', 'no_telp'];

                $data = (array)$data;
                $queryUpdate = "UPDATE MST_PELANGGAN SET KD_CABANG = '05', PELANGGAN_AKTIF = '1', UPDATE_DATE = SYSDATE, ";
                $doUpdate = false;
                for ($i = 0; $i < count($keySap); $i++) {
                    if ($mstPelanggan[$keyMst[$i]] != $data[$keySap[$i]]) {
                        $queryUpdate .= $keyMst[$i] . " = '" . $data[$keySap[$i]] . "', ";

                        $doUpdate = true;
                    }
                }
                $queryUpdate = rtrim($queryUpdate, ', ');
                $queryUpdate .= " WHERE NO_ACCOUNT_PBM = '" . $mstPelanggan['no_account_pbm'] . "'";

                if ($doUpdate) {
                    $exec = DB::connection('uster')->statement($queryUpdate);
                }
            }
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil update data pelanggan',
                'redirect' => [
                    'need' => true,
                    'to' => route('uster.maintenance.pconnect'),
                ]
            ]);
            DB::commit();
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Update Data, Harap Coba lagi!'
            ]);
        }
    }

    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->pconect->getNoNpwp($request);
            $getdata = collect($data)->first();

            if ($data) {
                $namaPerusahaan = strtoupper($getdata->nama);
                $namaPerusahaan = preg_replace('/\s+PT$/', '', $namaPerusahaan);
                $kata = explode(' ', $namaPerusahaan);
                $singkatan = '';

                foreach ($kata as $k) {
                    $singkatan .= substr($k, 0, 1);
                }
                $this->pconect->MstPelanggan($getdata, $singkatan);
            }
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Berhasil insert data pelanggan',
                'redirect' => [
                    'need' => true,
                    'to' => route('uster.maintenance.pconnect'),
                ]
            ]);
            DB::commit();
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat insert Data, Harap Coba lagi!'
            ]);
        }
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
