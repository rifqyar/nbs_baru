<?php

namespace App\Http\Controllers\maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use \Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class PelangganController extends Controller
{
    public function index()
    {
        return view('maintenance.pelanggan');
    }

    public function datatable(Request $request)
    {
        $mst = isset($request->search['value']) ? $request->search['value'] : null;

        // Build the base query
        $query = DB::connection('uster')->table('mst_pelanggan');

        if ($mst) {
            // Remove all non-digit characters from the search value
            $mst_digits = preg_replace('/\D/', '', $mst);
            $mst_upper = strtoupper($mst);
            

            // Use orWhere at the top level to combine all conditions with OR
            $query->orWhere(function ($q) use ($mst_upper, $mst_digits) {
                $q->Where('nm_pbm', 'like', '%' . $mst_upper . '%')
                    ->orWhereRaw("REGEXP_REPLACE(no_npwp_pbm, '[^0-9]', '') LIKE ?", ['%' . $mst_digits . '%'])
                    ->orWhereRaw("REGEXP_REPLACE(no_npwp_pbm16, '[^0-9]', '') LIKE ?", ['%' . $mst_digits . '%']);
            });
        }

        $query->orderBy('NO_ACCOUNT_PBM', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            // Removed action column
            ->make(true);
    }



    public function checkNpwp(Request $request)
    {
        $npwp = $request->npwp;

        // Remove all non-digit characters from the input NPWP
        $npwp_digits = preg_replace('/\D/', '', $npwp);

        // Build the query to check both no_npwp_pbm and no_npwp_pbm16
        $pelanggan = DB::connection('uster')->table('mst_pelanggan')
            ->whereRaw("REGEXP_REPLACE(no_npwp_pbm, '[^0-9]', '') = ?", [$npwp_digits])
            ->orWhereRaw("REGEXP_REPLACE(no_npwp_pbm16, '[^0-9]', '') = ?", [$npwp_digits])
            ->first();

        if ($pelanggan) {
            return response()->json([
                'exists' => true,
                'data' => [
                    'id' => $pelanggan->no_account_pbm,
                    'npwp' => $pelanggan->no_npwp_pbm,
                    'npwp15' => $pelanggan->no_npwp_pbm,
                    'npwp16' => $pelanggan->no_npwp_pbm16,
                    'nama' => $pelanggan->nm_pbm,
                    'alamat' => $pelanggan->almt_pbm,
                    'no_telp' => $pelanggan->no_telp
                ]
            ]);
        } else {
            return response()->json(['exists' => false]);
        }
    }


    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_telp' => 'nullable|string|max:50',
            'npwp15' => 'required|string|max:15',
            'npwp16' => 'required|string|max:16',
        ]);

        // Start a database transaction
        DB::connection('uster')->beginTransaction();

        try {
            // Lock the MST_PELANGGAN table in exclusive mode
            DB::connection('uster')->statement('LOCK TABLE MST_PELANGGAN IN EXCLUSIVE MODE');

            // Retrieve the maximum NO_ACCOUNT_PBM
            $max_no_account = DB::connection('uster')->table('mst_pelanggan')->max('NO_ACCOUNT_PBM');

            // If no records exist, set max_no_account to 0
            if (is_null($max_no_account)) {
                $max_no_account = 0;
            }

            // Increment to get the new NO_ACCOUNT_PBM
            $new_no_account = $max_no_account + 1;

            // Generate KD_PBM based on initials
            $initials = $this->getInitials($request->nama);

            // Prepare data for insertion
            $data = [
                'KD_PBM' => $initials, // Using initials as KD_PBM
                'NM_PBM' => $request->nama,
                'ALMT_PBM' => $request->alamat,
                'NO_TELP' => $request->no_telp,
                'NO_ACCOUNT_PBM' => $new_no_account,
                'KD_PPN_PBM' => '',
                'NO_NPWP_PBM' => $request->npwp15,
                'KD_GUDANG1' => '',
                'KD_GUDANG2' => '',
                'KD_CABANG' => '05', // Set KD_CABANG to 5 as per your requirement
                'PELANGGAN_AKTIF' => '1',
                'UPDATE_DATE' => now(),
                'NO_NPWP_PBM16' => $request->npwp16
            ];

            // Insert the new record
            DB::connection('uster')->table('mst_pelanggan')->insert($data);

            // Commit the transaction
            DB::connection('uster')->commit();

            return response()->json(['message' => 'Pelanggan added successfully']);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::connection('uster')->rollBack();

            return response()->json(['message' => 'Failed to add Pelanggan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get the initials from a string.
     *
     * @param string $string
     * @return string
     */
    private function getInitials($string)
    {
        $words = explode(' ', $string);
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        return $initials;
    }



    public function update(Request $request)
    {
        // Validate and update existing Pelanggan
        $id = $request->id;
        $data = [
            'NO_NPWP_PBM' => $request->npwp15,
            'no_npwp_pbm16' => $request->npwp16,
            'nm_pbm' => $request->nama,
            'almt_pbm' => $request->alamat,
            'no_telp' => $request->no_telp,
            'update_date' => now()
        ];

        try {
            DB::connection('uster')->table('mst_pelanggan')->where('no_account_pbm', $id)->update($data);
            return response()->json(['message' => 'Pelanggan updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update Pelanggan: ' . $e->getMessage()], 500);
        }
    }
}
