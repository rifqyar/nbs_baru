<?php

namespace App\Http\Controllers\Maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class RenameController extends Controller
{
    function index()
    {
        return view('maintenance.rename-container');
    }

    function getContainer(Request $request)
    {
        $no_cont = strtoupper($request->search);
        $query_list = "SELECT * FROM master_container where no_container = '$no_cont'";
        return DB::connection('uster')->select($query_list);
    }

    function storeRename(Request $request)
    {
        DB::beginTransaction();
        try {
            $ID_USER = session('LOGGED_STORAGE');
            $no_cont_old = strtoupper($request->NO_CONT_OLD);
            $no_cont_new = strtoupper($request->NO_CONT_NEW);
            $size_new = $request->SIZE_NEW;
            $type_new = $request->TYPE_NEW;

            $q_cek = "SELECT NO_CONTAINER, NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
            $rc = DB::connection('uster')->selectOne($q_cek);
            $no_booking = $rc->no_booking;
            $counter = $rc->counter;
            $location = $rc->location;

            $check = $rc->no_container ?? NULL;
            if ($check != NULL) {
                $gethistory = "SELECT NO_CONTAINER,NO_REQUEST,KEGIATAN FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
                $rh = DB::connection('uster')->selectOne($gethistory);

                if ($rh !== null) {
                    foreach ($rh as $rha) {
                        $no_req = $rha->no_request ?? NULL;
                        $keg     = $rha->kegiatan ?? NULL;
                        if ($keg == "REQUEST RECEIVING") {
                            $qupdrec = "UPDATE CONTAINER_RECEIVING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdrec);
                        } else if ($keg == "GATE IN") {
                            $qupdgati = "UPDATE GATE_IN SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdgati);
                        } else if ($keg == "GATE OUT") {
                            $qupdgato = "UPDATE GATE_OUT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdgato);
                        } else if ($keg == "BORDER GATE IN" || $keg == "BORDER GATE OUT") {
                            $qupdgatib = "UPDATE BORDER_GATE_IN SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdgatib);
                            $qupdgatob = "UPDATE BORDER_GATE_OUT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdgatob);
                        } else if ($keg == "PERPANJANGAN STRIPPING" || $keg == "REQUEST STRIPPING") {
                            $qupdstrip = "UPDATE CONTAINER_STRIPPING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdstrip);
                        } else if ($keg == "REQUEST STUFFING" || $keg == "PERPANJANGAN STUFFING") {
                            $qupdstuf = "UPDATE CONTAINER_STUFFING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdstuf);
                        } else if ($keg == "PLAN REQUEST STRIPPING") {
                            $qupdplst = "UPDATE PLAN_CONTAINER_STRIPPING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdplst);
                        } else if ($keg == "PLAN REQUEST STUFFING") {
                            $qupdplstf = "UPDATE PLAN_CONTAINER_STUFFING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdplstf);
                        } else if ($keg == "REQUEST BATALMUAT") {
                            $qupdbm = "UPDATE CONTAINER_BATAL_MUAT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdbm);
                        } else if ($keg == "REQUEST DELIVERY" || $keg == "PERP DELIVERY") {
                            $qupdbm = "UPDATE CONTAINER_DELIVERY SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                            DB::connection('uster')->update($qupdbm);
                        }
                    }
                }

                $get_history_ = "SELECT NO_REQUEST, NO_CONTAINER, NO_BOOKING FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
                $rxhist        = DB::connection('uster')->selectOne($get_history_);

                if ($rxhist !== null) {
                    foreach ($rxhist as $rxh) {
                        $noreq_hist = $rxh->no_request ?? NULL;
                        $nobook_hist = $rxh->no_booking ?? NULL;

                        $update_history = "UPDATE HISTORY_CONTAINER SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$noreq_hist' AND NO_BOOKING = '$nobook_hist' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->update($update_history);
                    }
                }

                $q_cek_new = "SELECT NO_CONTAINER, NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont_new'";
                $rc_new = DB::connection('uster')->selectOne($q_cek_new);
                $check = $rc_new->no_container ?? NULL;
                if ($check != NULL) {
                    // DB::rollBack();
                    // echo "Z";
                    // exit();

                    $updCounter = (int)$rc_new->counter + 1;
                    $upd_master = "UPDATE MASTER_CONTAINER SET NO_BOOKING = '$no_booking', SIZE_ = '$size_new', TYPE_ = '$type_new', LOCATION = '$location', COUNTER = '$updCounter' WHERE NO_CONTAINER = '$no_cont_new'";
                    DB::connection('uster')->update($upd_master);
                } else {
                    $update_master = "INSERT INTO MASTER_CONTAINER (NO_CONTAINER, SIZE_, TYPE_, LOCATION, NO_BOOKING, COUNTER) VALUES ('$no_cont_new', '$size_new' , '$type_new', '$location', '$no_booking', '$counter')";
                    DB::connection('uster')->insert($update_master);
                }

                $update_old = "UPDATE MASTER_CONTAINER SET MLO = '-' WHERE NO_CONTAINER = '$no_cont_old'";
                DB::connection('uster')->update($update_old);

                //update placement
                $cek_placement = "SELECT NO_CONTAINER, NO_REQUEST_RECEIVING FROM PLACEMENT WHERE NO_CONTAINER = '$no_cont_old'";
                $rwcek = DB::connection('uster')->selectOne($cek_placement);
                $contplace = $rwcek->no_request_receiving ?? NULL;
                DB::connection('uster')->update("UPDATE PLACEMENT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST_RECEIVING = '$contplace' AND NO_CONTAINER = '$no_cont_old'");

                //update history placement
                $cek_hplace = "SELECT NO_REQUEST FROM HISTORY_PLACEMENT WHERE NO_CONTAINER = '$no_cont_old'";
                $rhwplace = DB::connection('uster')->selectOne($cek_hplace);

                if ($rhwplace !== null) {
                    foreach ($rhwplace as $rhw) {
                        $reqh = $rhw["NO_REQUEST"];
                        $updhsplace = "UPDATE HISTORY_PLACEMENT SET NO_CONTAINER = '$no_cont_new' WHERE NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->update($updhsplace);
                    }
                }

                DB::connection('uster')->commit();
                echo "Y";
                exit();
            } else {
                DB::connection('uster')->rollBack();
                echo "X";
                exit();
            }
        } catch (Exception $th) {
            DB::connection('uster')->rollBack();
            echo $th->getMessage();
        }
    }
}
