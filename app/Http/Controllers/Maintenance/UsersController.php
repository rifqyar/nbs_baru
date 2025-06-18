<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{

    public function index()
    {
        return view('maintenance.reset-password');
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|confirmed|min:8',
            ]);

            $id_user = session("ID_USER");
            $new = md5($request->NEW_PASSWD . $id_user);
            $conf = $request->CONF_PASSWD;

            DB::connection('uster')->table('tb_user')->where('id', $id_user)->update(['password_enc' => $new]);

            return response()->json(['success' => true, 'message' => 'Password berhasil diubah']);
        } catch (\Exception $e) {
            // Tangani pengecualian di sini
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
