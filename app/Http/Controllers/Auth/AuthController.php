<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function loginPage()
    {
        return view('auth.login');
    }

    public function loginProcess(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        try {
            // $checkUser = DB::connection('default')->select("SELECT A.*, B.* FROM TB_USER A, TB_GROUP B WHERE A.USERNAME = '$request->username' AND A.ID_GROUP=B.ID_GROUP");
            $checkUser = User::getUser($request->username);
            if (count($checkUser) > 0) {
                $data = $checkUser[0];
                $inputedPass = md5($request->password . $data->id);
                if ($data->password_enc == $inputedPass) {
                    if ($data->id_group == null) {
                        throw new Exception('Anda bukanlah user yang valid', 500);
                    }

                    foreach ($data as $key => $value) {
                        Session::put($key, $value);
                    }

                    Session::put('is_logged_in', TRUE);
                    Session::put('PENGGUNA_ID', $data->id);
                    Session::put('KATA_KUNCI', 'PASSWORD');
                    Session::put('ID_GROUP', $data->id_group);
                    Session::put('ATURAN', $data->name_group);
                    Session::put('NAMA_PENGGUNA', $data->username);
                    Session::put('NAMA_LENGKAP', $data->name);
                    Session::put('ID_USER', $data->id);
                    Session::put('LOGGED_STORAGE', $data->id);

                    if ($data->username == 'USTER_PRINT') {
                        config(['session.lifetime' => 1440]);
                    }

                    return response()->json([
                        'status' => 200,
                        'message' => 'Login Success',
                    ]);
                } else {
                    throw new Exception('Password yang anda masukan Salah', 500);
                }
            } else {
                throw new Exception('Username tidak terdaftar di sistem, harap hubungi administrator', 500);
            }
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

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
