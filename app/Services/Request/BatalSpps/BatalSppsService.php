<?php

namespace App\Services\Request\BatalSpps;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BatalSppsService
{
    function getData()
    {
        $query = "SELECT
                    NO_BA,
                    ID_REQ_SPPS,
                    NO_CONTAINER,
                    TANGGAL_PEMBUATAN,
                    VESSEL,
                    VOYAGE_IN
                FROM REQ_BATAL_SPPS
                ORDER BY TANGGAL_PEMBUATAN DESC";

        $query = '(' . $query . ') req_batal_spps';
        $data = array();
        DB::connection('uster')->table(DB::raw($query))->orderBy('req_batal_spps.tanggal_pembuatan', 'desc')->chunk(100, function ($chunk) use (&$data) {
            foreach ($chunk as $dt) {
                $data[] = $dt;
            }
        });

        return $data;
    }

    function getContainer($noCont)
    {
        $query = "SELECT
                        A.NO_CONTAINER,
                        A.NO_REQUEST,
                        B.SIZE_,
                        B.TYPE_,
                        D.O_VESSEL AS VESSEL,
                        D.O_VOYIN AS VOYAGE_IN,
                        D.O_VOYOUT AS VOYAGE_OUT,
                        B.LOCATION,
                        CASE WHEN C.LUNAS='YES' THEN 'Sudah Bayar'
                                WHEN C.LUNAS = 'NO' THEN 'Belum Bayar'
                        END AS STATUS,
                        D.O_REQNBS AS ID_UREQ
                    FROM
                        container_stripping A
                    LEFT JOIN
                        MASTER_CONTAINER B
                        ON (TRIM(A.NO_CONTAINER) = TRIM(B.NO_CONTAINER))
                    LEFT JOIN
                        NOTA_STRIPPING C ON
                        (TRIM(A.NO_REQUEST) = TRIM(C.NO_REQUEST))
                    LEFT JOIN
                        REQUEST_STRIPPING D ON
                        TRIM(A.NO_REQUEST) = TRIM(D.NO_REQUEST)
                    WHERE
                        A.NO_CONTAINER = '$noCont'";
        $data = DB::connection('uster')->select($query);
        return $data;
    }

    function insertReq(Request $request)
    {
        try {
            $container = trim($request->no_cont);
            $size_cont = $request->sc;
            $type_cont = $request->tc;
            $status_cont = $request->stc;
            $status_payment = $request->status;
            $id_req = $request->id_req;
            $id_ureq = $request->id_ureq;
            $rename_container = trim($container . '_' . "rename");
            $no_ukk = $request->no_ukk;
            $vessel = $request->vessel;
            $voyage_in = $request->voyage_in;
            $no_ba = $request->no_ba;
            $billing_user = Session::get("NAMA_PENGGUNA");
            $id_user = Session::get("LOGGED_STORAGE");

            $cek_location = "SELECT NO_CONTAINER, LOCATION
                    FROM MASTER_CONTAINER mc
                    WHERE NO_CONTAINER = '$container'";

            $row_location = DB::connection('uster')->selectOne($cek_location);
            $location = $row_location->location;

            if ($location != 'IN_YARD') {
                $batal = batalContainer($container, $id_req, 'batal spps');
                $status = $batal->getData()->code;
                if ($status != 200) {
                    throw new Exception('Gagal melakukan pembatalan container di praya', 500);
                }
            }

            $selheader = "SELECT
							NO_REQUEST_RECEIVING
				        FROM
							REQUEST_STRIPPING
				        WHERE
							NO_REQUEST = '$id_req'";

            $row_header = DB::connection('uster')->selectOne($selheader);
            $idreq_rec = $row_header->no_request_receiving;

            $query = "INSERT INTO REQ_BATAL_SPPS
				       VALUES
						('$no_ba',
						'$id_req',
						'$container',
						SYSDATE,
						'$billing_user',
						'$rename_container',
						'$no_ukk',
						'$vessel',
						'$voyage_in')";

            $execInsert = DB::connection('uster')->statement($query);

            $q_update = "	UPDATE
                                CONTAINER_STRIPPING
                            SET
							    NO_CONTAINER='$rename_container'
					        WHERE
							    no_container='$container'
							and no_request = '$id_req'";
            $execUpdate = DB::connection('uster')->statement($q_update);

            $q_update2 = "	UPDATE
							    CONTAINER_RECEIVING
					        SET
							    NO_CONTAINER='$rename_container'
					        WHERE
							    no_container='$container'
							and no_request = '$idreq_rec'";
            $execUpdate = DB::connection('uster')->statement($q_update2);

            $q_update3 = "	UPDATE
                                REQ_DELIVERY_D@DBBIL_LINK
                            SET
                                NO_CONTAINER='$rename_container'
                            WHERE
                                no_container='$container'
                                and id_req = '$id_ureq'";
            $execUpdate = DB::connection('uster')->statement($q_update3);

            //insert history container
            $q_getcounter1 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$container' ORDER BY COUNTER DESC";
            $row_counter = DB::connection('uster')->selectOne($q_getcounter1);
            $cur_booking1  = $row_counter->no_booking;
            $cur_counter1  = $row_counter->counter;

            $query_log = "INSERT INTO
                            HISTORY_CONTAINER(
                                NO_CONTAINER,
                                NO_REQUEST,
                                KEGIATAN,
                                TGL_UPDATE,
                                ID_USER,
                                STATUS_CONT,
                                NO_BOOKING,
                                COUNTER
                            ) VALUES (
                                '$container',
                                '$id_req',
                                'BATAL STRIPPING',
                                SYSDATE,
                                '$id_user',
                                'FCL',
                                '$cur_booking1',
                                '$cur_counter1'
                            )";

            $insertLog= DB::connection('uster')->statement($query_log);

            return response()->json([
                'status' => [
                    'code' => 200,
                    'msg' => 'OK'
                ]
            ], 200);
        } catch (Exception $th) {
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], $th->getCode() != '' ? $th->getCode() : 500);
        }
    }
}
