<?php

namespace App\Http\Controllers\Maintenance\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class MasterContainer extends Controller
{
    public function index()
    {
        return view('maintenance.master.master-container');
    }

    public function receiving()
    {
        return view('maintenance.master.master-container-receiving');
    }

    public function datatable(Request $request): JsonResponse
    {
        $nocont = isset($request->search['value']) ? $request->search['value'] : null;

        $start = $request->start;
        $length = $request->length;
        $limit = ($length * 3) + $start;

        if (isset($nocont)) {
            $nocont = strtoupper($nocont);
            $query_list = "SELECT * FROM ( SELECT * FROM master_container where no_container like '%$nocont%') WHERE ROWNUM <= $limit";
        } else {
            $query_list = "SELECT * FROM (SELECT * FROM master_container) WHERE ROWNUM <= $limit ";
        }

        $container =  DB::connection('uster')->select($query_list);
        return Datatables::of($container)
            ->addColumn('action', function ($listStuffing) {
                return '<button type="button" class="btn btn-primary btn-sm" onclick="GetById(\'' . $listStuffing->no_container . '\')">Edit</button>';
            })
            ->make(true);
    }

    function getContainerByID(Request $request)
    {
        $no_container = $request->no_container;
        $container = DB::connection('uster')->selectOne("SELECT * FROM master_container WHERE no_container = '$no_container'");
        return response()->json($container);
    }

    function getRecivingByContainer(Request $request)
    {
        $no_container = $request->no_container;

        try {
            // Query with Oracle-specific syntax to limit the result to 5 rows
            $container = DB::connection('uster')->select("
            SELECT * FROM (
                SELECT * FROM container_receiving
                WHERE no_container = :no_container
                ORDER BY TABLEIDX DESC
            ) WHERE ROWNUM <= 5
        ", ['no_container' => $no_container]);

            // Return the result as a JSON response
            return response()->json($container);
        } catch (\Exception $e) {
            // Handle the error and return a JSON response with the error message
            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil data container',
                'message' => 'Query Errror'
            ], 500); // 500 status code indicates server error
        }
    }



    function EditContainer(Request $request)
    {
        $NO_CONT = $request->no_container;
        $SIZE_ = $request->size;
        $TYPE_ = $request->type;
        $LOCATION = $request->location;
        $q = "UPDATE MASTER_CONTAINER SET SIZE_ = '$SIZE_', TYPE_ = '$TYPE_', LOCATION = '$LOCATION' WHERE NO_CONTAINER = '$NO_CONT'";
        if (DB::connection('uster')->update($q)) {
            $response = [
                'status' => 'success',
                'message' => 'Data updated successfully'
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Failed to update data'
            ];
        }


        // Return JSON response
        echo json_encode($response);
        die();
    }

    public function EditContainerReceiving(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'containers' => 'required|array', // Ensure the 'containers' field is an array
                'containers.*.no_container' => 'required|string',
                'containers.*.no_request' => 'required|string',
                'containers.*.status' => 'required|string',
                'containers.*.aktif' => 'required|string|in:T,Y', // Must be either 'T' or 'Y'
            ]);

            // Process each container and update the database
            foreach ($validated['containers'] as $container) {
                // Update the database with the new data for each container
                DB::connection('uster')->table('container_receiving')
                    ->where('no_container', $container['no_container'])
                    ->where('no_request', $container['no_request'])
                    ->update([
                        'status' => $container['status'],
                        'aktif' => $container['aktif'],
                    ]);
            }

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully'
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions and return an error response
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
