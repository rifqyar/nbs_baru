<?php

namespace App\Services\Request\Stripping;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PDO;

class PerpanjanganStripping
{
    function getData(Request $request)
    {
        $no_req = $request->no_request;
        $from = $request->tgl_awal;
        $to = $request->tgl_akhir;
        $noReqWhere = '';
        $dateWhere = '';

        if ($request->cari == 'true') {
            if ($no_req != null && ($from == null && $to == null)) {
                $noReqWhere = "AND REQUEST_STRIPPING.NO_REQUEST LIKE '%$no_req%'";
            } else if ($request->no_request == null && ($from != null && $to != null)) {
                $dateWhere = "AND REQUEST_STRIPPING.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY/MM/DD') AND TO_DATE ( '$to', 'YYYY/MM/DD') ";
            } else if ($no_req != null && ($from != null && $to != null)) {
                $noReqWhere = "AND REQUEST_STRIPPING.NO_REQUEST LIKE '%$no_req%'";
                $dateWhere = "AND REQUEST_STRIPPING.TGL_REQUEST BETWEEN TO_DATE ( '$from', 'YYYY/MM/DD') AND TO_DATE ( '$to', 'YYYY/MM/DD') ";
            }

            $query_list = "SELECT *
			FROM (  SELECT REQUEST_STRIPPING.NO_REQUEST,
						TO_CHAR (REQUEST_STRIPPING.TGL_APPROVE, 'dd/mm/yyyy')
							TGL_APPROVE,
						NOTA_STRIPPING.EMKL NAMA_CONSIGNEE,
						NVL (REQUEST_STRIPPING.PERP_DARI, '-') AS EX_REQ,
						REQUEST_STRIPPING.PERP_KE,
						REQUEST_STRIPPING.NO_DO,
						REQUEST_STRIPPING.NO_BL,
						REQUEST_STRIPPING.NOTA,
						REQUEST_STRIPPING.KOREKSI,
						NVL (nota_stripping.LUNAS, 0) LUNAS,
						REQUEST_STRIPPING.TGL_REQUEST,
						REQUEST_STRIPPING.CLOSING,
						COUNT (container_stripping.NO_CONTAINER) JUMLAH
					FROM REQUEST_STRIPPING, CONTAINER_STRIPPING, NOTA_STRIPPING
					WHERE REQUEST_STRIPPING.NO_REQUEST = CONTAINER_STRIPPING.NO_REQUEST
						AND REQUEST_STRIPPING.NO_REQUEST = NOTA_STRIPPING.NO_REQUEST(+)
						AND CONTAINER_STRIPPING.NO_CONTAINER IS NOT NULL
						$noReqWhere
                        $dateWhere
				GROUP BY REQUEST_STRIPPING.NO_REQUEST,
						REQUEST_STRIPPING.NO_DO,
						REQUEST_STRIPPING.TGL_REQUEST,
						NVL (nota_stripping.LUNAS, 0),
						REQUEST_STRIPPING.NOTA,
						REQUEST_STRIPPING.NO_BL,
						REQUEST_STRIPPING.TGL_APPROVE,
						NOTA_STRIPPING.EMKL,
						REQUEST_STRIPPING.PERP_DARI,
						REQUEST_STRIPPING.PERP_KE,
						REQUEST_STRIPPING.KOREKSI,
						REQUEST_STRIPPING.CLOSING)
			ORDER BY TGL_REQUEST DESC";
        } else {
            $query_list = " SELECT *
				FROM (  SELECT REQUEST_STRIPPING.NO_REQUEST,
							TO_CHAR (REQUEST_STRIPPING.TGL_APPROVE, 'dd/mm/yyyy')
								TGL_APPROVE,
							NOTA_STRIPPING.EMKL NAMA_CONSIGNEE,
							NVL (REQUEST_STRIPPING.PERP_DARI, '-') AS EX_REQ,
							REQUEST_STRIPPING.PERP_KE,
							REQUEST_STRIPPING.NO_DO,
							REQUEST_STRIPPING.NO_BL,
							REQUEST_STRIPPING.NOTA,
							REQUEST_STRIPPING.KOREKSI,
							NVL (nota_stripping.LUNAS, 0) LUNAS,
							REQUEST_STRIPPING.TGL_REQUEST,
							COUNT (container_stripping.NO_CONTAINER) JUMLAH,
							REQUEST_STRIPPING.CLOSING
						FROM REQUEST_STRIPPING, CONTAINER_STRIPPING, NOTA_STRIPPING
						WHERE REQUEST_STRIPPING.NO_REQUEST = CONTAINER_STRIPPING.NO_REQUEST
							AND REQUEST_STRIPPING.NO_REQUEST =
									NOTA_STRIPPING.NO_REQUEST(+)
							AND CONTAINER_STRIPPING.NO_CONTAINER IS NOT NULL
							AND REQUEST_STRIPPING.TGL_REQUEST BETWEEN SYSDATE - INTERVAL '15' DAY
                            AND LAST_DAY (SYSDATE)
                        GROUP BY REQUEST_STRIPPING.NO_REQUEST,
                                REQUEST_STRIPPING.NO_DO,
                                REQUEST_STRIPPING.TGL_REQUEST,
                                NVL (nota_stripping.LUNAS, 0),
                                REQUEST_STRIPPING.NOTA,
                                REQUEST_STRIPPING.NO_BL,
                                REQUEST_STRIPPING.TGL_APPROVE,
                                NOTA_STRIPPING.EMKL,
                                REQUEST_STRIPPING.PERP_DARI,
                                REQUEST_STRIPPING.PERP_KE,
                                REQUEST_STRIPPING.KOREKSI,
                                REQUEST_STRIPPING.CLOSING
					) ORDER BY TGL_REQUEST DESC";
        }

        $query = '(' . $query_list . ') data_stripping';
        $data = array();
        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'YYYY/MM/DD'");
        $data = DB::connection('uster')->table(DB::raw($query))->select()->get();
        // DB::connection('uster')->table(DB::raw($query))->orderBy('data_stripping.tgl_request', 'desc')->chunk(100, function ($chunk) use (&$data) {
        //     foreach ($chunk as $dt) {
        //         $data[] = $dt;
        //     }
        // });

        return $data;
    }

    function getViewData($no_req)
    {
        $query = "SELECT NO_REQUEST_RECEIVING
					FROM PLAN_REQUEST_STRIPPING
					WHERE NO_REQUEST = '$no_req'";

        $row_req2    = DB::connection('uster')->selectOne($query);
        $no_req_rec  = !empty($row_req2->no_request_receiving) ? $row_req2->no_request->receiving : '';

        $no_req2    = substr($no_req_rec, 3);
        $no_req2    = "UREC" . $no_req2;

        $query_list    = "SELECT DISTINCT PLAN_CONTAINER_STRIPPING.*, PLAN_CONTAINER_STRIPPING.COMMODITY COMMO, MASTER_CONTAINER.SIZE_, MASTER_CONTAINER.TYPE_
                           FROM PLAN_CONTAINER_STRIPPING INNER JOIN
						   MASTER_CONTAINER ON PLAN_CONTAINER_STRIPPING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
                           WHERE PLAN_CONTAINER_STRIPPING.NO_REQUEST = '$no_req'";
        $row_list    = DB::connection('uster')->select($query_list);
        $jum = count($row_list);

        $q_cekperp = "SELECT STATUS_REQ from request_stripping where no_request = '$no_req'";
        $rowperp = DB::connection('uster')->selectOne($q_cekperp);
        $rcperp = $rowperp->status_req;

        if ($rcperp == 'PERP') {
            $query_request = "SELECT REQUEST_STRIPPING.*,
                               emkl.NM_PBM AS NAMA_PEMILIK,
                               pnmt.NM_PBM AS NAMA_PENUMPUK
                            FROM REQUEST_STRIPPING
                                INNER JOIN V_MST_PBM emkl
                                    ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM and emkl.KD_CABANG = '05'
                                JOIN V_MST_PBM pnmt
                                    ON REQUEST_STRIPPING.KD_PENUMPUKAN_OLEH = pnmt.KD_PBM and pnmt.KD_CABANG = '05'
                            WHERE REQUEST_STRIPPING.NO_REQUEST = '$no_req'";

            $get_tgl    = " SELECT
                                a.NO_CONTAINER,
                                a.END_STACK_PNKN+1 TGL_SELESAI
                            FROM CONTAINER_STRIPPING a
                            WHERE a.NO_REQUEST = '$no_req' AND AKTIF = 'Y' AND a.NO_CONTAINER IS NOT NULL
                            ORDER BY a.NO_CONTAINER";
        } else {
            $query_request = "SELECT REQUEST_STRIPPING.*, PL.NO_SPPB, PL.TGL_SPPB,
                               emkl.NM_PBM AS NAMA_PEMILIK,
                               pnmt.NM_PBM AS NAMA_PENUMPUK
                            FROM REQUEST_STRIPPING
                                INNER JOIN V_MST_PBM emkl
                                    ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM and emkl.KD_CABANG = '05'
                                JOIN V_MST_PBM pnmt
                                    ON REQUEST_STRIPPING.KD_PENUMPUKAN_OLEH = pnmt.KD_PBM and pnmt.KD_CABANG = '05'
                                JOIN PLAN_REQUEST_STRIPPING PL
                                    ON REQUEST_STRIPPING.NO_REQUEST = PL.NO_REQUEST_APP_STRIPPING
                            WHERE REQUEST_STRIPPING.NO_REQUEST = '$no_req'";

            $get_tgl    = " SELECT
                                a.NO_CONTAINER,
                                CASE WHEN a.TGL_SELESAI  IS NULL
                                    THEN TO_DATE (a.TGL_BONGKAR + 4, 'YYYY-MM-DD')
                                ELSE
                                    TO_DATE (a.TGL_SELESAI, 'YYYY-MM-DD')
                                END AS TGL_SELESAI
                            FROM CONTAINER_STRIPPING a
                            WHERE a.NO_REQUEST = '$no_req' AND AKTIF = 'Y'
                            ORDER BY a.NO_CONTAINER";
        }

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT= 'YYYY/MM/DD'");
        $row_request = DB::connection('uster')->selectOne($query_request);
        $row_tgl        = DB::connection('uster')->selectOne($get_tgl);

        return response()->json([
            'row_request' => $row_request,
            'row_tgl' => $row_tgl,
            'jum' => $jum
        ]);
    }

    function getEditData($no_req)
    {
        $query_request    = " SELECT REQUEST_STRIPPING.NO_REQUEST,
                                REQUEST_STRIPPING.NO_DO,
                                REQUEST_STRIPPING.NO_BL,
                                REQUEST_STRIPPING.TYPE_STRIPPING,
                                REQUEST_STRIPPING.KD_CONSIGNEE,
                                REQUEST_STRIPPING.CONSIGNEE_PERSONAL,
                                emkl.NM_PBM AS NAMA_PEMILIK,
                                REQUEST_STRIPPING.CLOSING,
                                REQUEST_STRIPPING.PERP_DARI,
                                REQUEST_STRIPPING.O_VESSEL,
                                REQUEST_STRIPPING.O_VOYIN,
                                REQUEST_STRIPPING.O_VOYOUT,
                                REQUEST_STRIPPING.O_REQNBS,
                                REQUEST_STRIPPING.O_IDVSB
                            FROM REQUEST_STRIPPING
                                INNER JOIN V_MST_PBM emkl
                                    ON REQUEST_STRIPPING.KD_CONSIGNEE = emkl.KD_PBM
                            WHERE REQUEST_STRIPPING.NO_REQUEST = '$no_req'";

        $row_request = DB::connection('uster')->selectOne($query_request);
        $count          = "SELECT COUNT(a.NO_CONTAINER) JUMLAH FROM CONTAINER_STRIPPING a WHERE a.NO_REQUEST = '$no_req' AND a.AKTIF = 'Y'";
        $row_count        = DB::connection('uster')->selectOne($count);

        $get_tgl         = " SELECT a.NO_CONTAINER,
                                TO_DATE (a.TGL_SELESAI, 'dd/mm/rrrr') + 1 TGL_SELESAI
                            FROM CONTAINER_STRIPPING a
                            WHERE a.NO_REQUEST = '$no_req' AND AKTIF = 'Y'
                            ORDER BY a.NO_CONTAINER";
        $row_tgl        = DB::connection('uster')->selectOne($get_tgl);

        return response()->json([
            'row_request' => $row_request,
            'row_count' => $row_count,
            'row_tgl' => $row_tgl
        ]);
    }

    function contList($no_req)
    {
        $query_list        = "SELECT DISTINCT CONTAINER_STRIPPING.NO_CONTAINER,CONTAINER_STRIPPING.COMMODITY,
                                CONTAINER_STRIPPING.TGL_APPROVE, CONTAINER_STRIPPING.TGL_BONGKAR, CONTAINER_STRIPPING.START_PERP_PNKN,CONTAINER_STRIPPING.END_STACK_PNKN,CONTAINER_STRIPPING.TGL_APP_SELESAI,
                                CASE WHEN CONTAINER_STRIPPING.TGL_SELESAI  IS NULL
                                    THEN TO_DATE (CONTAINER_STRIPPING.TGL_BONGKAR + 4, 'YYYY/MM/DD')
                                ELSE
                                    TO_DATE (CONTAINER_STRIPPING.TGL_SELESAI, 'YYYY/MM/DD')
                                END AS TGL_SELESAI,
                                A.SIZE_ KD_SIZE, A.TYPE_ KD_TYPE, CONTAINER_STRIPPING.REMARK REMARK
                            FROM CONTAINER_STRIPPING
                            --INNER JOIN PETIKEMAS_CABANG.TTD_BP_CONT A ON CONTAINER_STRIPPING.NO_CONTAINER = A.CONT_NO_BP
                            INNER JOIN MASTER_CONTAINER A ON CONTAINER_STRIPPING.NO_CONTAINER = A.NO_CONTAINER
                            WHERE CONTAINER_STRIPPING.NO_REQUEST = '$no_req'
                            AND CONTAINER_STRIPPING.AKTIF = 'Y'";

        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
        $rowList = DB::connection('uster')->select($query_list);

        $cek_save = "SELECT CLOSING,PERP_KE FROM REQUEST_STRIPPING WHERE NO_REQUEST = '$no_req'";
        $r_cek = DB::connection('uster')->selectOne($cek_save);

        return response()->json([
            'row_list' => $rowList,
            'cek' => $r_cek
        ]);
    }

    function savePerp(array $param)
    {
        DB::beginTransaction();
        try {
            $request = $param['request'];
            $row_old = $param['row_old'];
            $no_req_s = $param['no_req_s'];

            $insertReqStrippingData = [
                'NO_REQUEST' => $no_req_s,
                'KD_CONSIGNEE' => $row_old->kd_consignee,
                'KD_PENUMPUKAN_OLEH' => $row_old->kd_consignee,
                'KETERANGAN' => $request->keterangan,
                'TGL_REQUEST' => OCI_SYSDATE,
                'TGL_APPROVE' => OCI_SYSDATE,
                'ID_USER' => Session::get('ID_USER'),
                'TYPE_STRIPPING' => $row_old->type_stripping,
                'NO_DO' => $row_old->no_do,
                'NO_BL' => $row_old->no_bl,
                'NOTA' => 'T',
                'STRIPPING_DARI' => '',
                'NO_REQUEST_PLAN' => $row_old->no_request_plan,
                'NO_REQUEST_RECEIVING' => $row_old->no_request_receiving,
                'PERP_KE' => (int)$row_old->perp_ke + 1,
                'PERP_DARI' => $request->no_request,
                'ID_YARD' => SESSION::get('IDYARD_STORAGE'),
                'STATUS_REQ' => 'PERP',
                'CONSIGNEE_PERSONAL' => $row_old->consignee_personal,
                'DI' => $row_old->di,
                'O_VESSEL' => $row_old->o_vessel,
                'O_VOYIN' => $row_old->o_voyin,
                'O_VOYOUT' => $row_old->o_voyout,
                'O_IDVSB' => $row_old->o_idvsb,
                'NO_BOOKING' => $row_old->no_booking
            ];

            $query_req = generateQuerySimpan($insertReqStrippingData);
            $query_req = "INSERT INTO REQUEST_STRIPPING $query_req";
            $exec_req = DB::connection('uster')->statement($query_req);
            // $exec_req = true;

            if ($exec_req) {
                $get_jumlah        = "SELECT COUNT(NO_CONTAINER) COUNT FROM CONTAINER_STRIPPING WHERE NO_REQUEST = '$request->no_request' AND AKTIF = 'Y'";
                $count            = DB::connection('uster')->selectOne($get_jumlah);
                $jml             = $count->count;

                //insert container_stripping satu persatu
                for ($i = 0; $i < $jml; $i++) {
                    if ($request->tgl_perp[$i] != NULL) {
                        $NO_CONT = $request->no_container[$i];
                        $TGL_PERP = $request->tgl_perp[$i];
                        $START_PERP_PNKN = $request->start_perp_pnkn[$i];

                        $query_select        = "SELECT HZ, VIA, VOYAGE, TO_DATE(TGL_BONGKAR, 'YYYY/MM/DD') TGL_BONGKAR, TO_DATE(TGL_SELESAI, 'YYYY/MM/DD') TGL_SELESAI, AFTER_STRIP, TO_DATE(TGL_APPROVE, 'YYYY/MM/DD') TGL_APPROVE,
                                                CASE WHEN CONTAINER_STRIPPING.TGL_SELESAI  IS NULL
                                                                THEN TO_DATE (CONTAINER_STRIPPING.TGL_BONGKAR + 5, 'YYYY/MM/DD')
                                                                ELSE
                                                                TO_DATE (CONTAINER_STRIPPING.TGL_SELESAI + 1, 'YYYY/MM/DD')
                                                                END AS START_PERP,
                                                TO_DATE(END_STACK_PNKN, 'YYYY/MM/DD')+1 START_PERP_I,
                                                COMMODITY  FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$NO_CONT' AND NO_REQUEST = '$request->no_request' AND AKTIF = 'Y'";

                        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
                        $row_cont = DB::connection('uster')->selectOne($query_select);

                        $hz = $row_cont->hz;
                        $via = $row_cont->via;
                        $voy = $row_cont->voyage;
                        $tgl_bongkar = $row_cont->tgl_bongkar;
                        $tgl_selesai = $row_cont->tgl_selesai;
                        $tgl_approve = $row_cont->tgl_approve;
                        $after_strip = $row_cont->after_strip;
                        $start_perp = $row_cont->start_perp;
                        if ($row_old->perp_ke > 1) {
                            $start_perp            = $row_cont->start_perp_i;
                        } else {
                            $start_perp            = $row_cont->start_perp;
                        }
                        $commodity            = $row_cont->commodity;

                        //non aktifkan container_stripping dengan nomor request lama
                        $query_update    = "UPDATE CONTAINER_STRIPPING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONT' AND NO_REQUEST = '$request->no_request'";
                        $execUpdate = DB::connection('uster')->statement($query_update);

                        //non aktifkan status aktif kartu stripping lama
                        $query_update2    = "UPDATE KARTU_STRIPPING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONT' AND NO_REQUEST = '$request->no_request'";
                        $execUpdate = DB::connection('uster')->statement($query_update2);

                        $dataInsertStrip = [
                            'NO_CONTAINER' => $NO_CONT,
                            'NO_REQUEST' => $no_req_s,
                            'AKTIF' => 'Y',
                            'VIA' => $via,
                            'HZ' => $hz,
                            'VOYAGE' => $voy,
                            'TGL_BONGKAR' => "TO_DATE('$tgl_bongkar','YYYY/MM/DD')@ORA",
                            'TGL_SELESAI' => "TO_DATE('$tgl_selesai','YYYY/MM/DD')@ORA",
                            'AFTER_STRIP' => $after_strip,
                            'TGL_APPROVE' => "TO_DATE('$tgl_approve','YYYY/MM/DD')@ORA",
                            'START_PERP_PNKN' => "TO_DATE('$start_perp','YYYY/MM/DD')@ORA",
                            'END_STACK_PNKN' => "TO_DATE('$TGL_PERP','YYYY/MM/DD')@ORA",
                            'COMMODITY' => $commodity
                        ];

                        DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
                        $queryInsert = generateQuerySimpan($dataInsertStrip);
                        $queryInsert = "INSERT INTO CONTAINER_STRIPPING $queryInsert";
                        $execCont = DB::connection('uster')->statement($queryInsert);

                        $q_getcounter2 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONT'";
                        $rw_getcounter2 = DB::connection('uster')->selectOne($q_getcounter2);

                        $cur_counter2 = $rw_getcounter2->counter;
                        $cur_booking2 = $rw_getcounter2->no_booking;
                        $history    = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD,STATUS_CONT, NO_BOOKING, COUNTER)
                                        VALUES ('$NO_CONT','$no_req_s','PERPANJANGAN STRIPPING',SYSDATE,'" . Session::get('ID_USER') . "','" . SESSION::get('IDYARD_STORAGE') . "','FCL','$cur_booking2','$cur_counter2')";
                        $execHistory = DB::connection('uster')->statement($history);
                    }
                }
            } else {
                throw new Exception("Gagal Insert Request Stripping", 500);
            }

            DB::commit();
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Data Processed',
            ], 200);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], 500);
        }
    }

    function updateReq(Request $request)
    {
        DB::beginTransaction();
        try {
            $no_req = $request->no_request;

            $get_jumlah = "SELECT COUNT(NO_CONTAINER) COUNT FROM CONTAINER_STRIPPING WHERE NO_REQUEST = '$no_req' AND AKTIF = 'Y'";
            $count = DB::connection('uster')->selectOne($get_jumlah);
            $jml = $count->count;

            for ($i = 0; $i < $jml; $i++) {
                if ($request->tgl_perp[$i] != NULL) {
                    $NO_CONT[$i] = $request->no_container[$i];
                    $TGL_PERP[$i] = $request->tgl_perp[$i];
                }
            }

            //insert container_stripping satu persatu
            DB::connection('uster')->statement("ALTER SESSION SET NLS_DATE_FORMAT='YYYY/MM/DD'");
            for ($i = 0; $i < $jml; $i++) {
                if ($request->tgl_perp[$i] != NULL) {
                    $query_insert_strip    = "UPDATE CONTAINER_STRIPPING SET END_STACK_PNKN = TO_DATE('$TGL_PERP[$i]','YYYY/MM/DD')
                                                    WHERE NO_CONTAINER = '$NO_CONT[$i]' AND NO_REQUEST = '$no_req'";
                    $exec = DB::connection('uster')->statement($query_insert_strip);
                }
            }

            DB::commit();
            return response()->json([
                'status' => JsonResponse::HTTP_OK,
                'message' => 'Data Processed',
            ], 200);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], 500);
        }
    }

    function deleteCont($noReq, $noCont)
    {
        DB::beginTransaction();
        try {
            $qdata = "SELECT * FROM REQUEST_STRIPPING WHERE NO_REQUEST = '$noReq'";
            $rwdata = DB::connection('uster')->selectOne($qdata);
            $oldno_req = $rwdata->perp_dari;

            $rcek = DB::connection('uster')->selectOne("SELECT * FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$noCont' AND NO_REQUEST = '$noReq'");
            if (!empty($rcek) && $rcek->no_container != null) {
                DB::connection('uster')->statement("DELETE CONTAINER_STRIPPING WHERE NO_CONTAINER = '$noCont' AND NO_REQUEST = '$noReq'");
                DB::connection('uster')->statement("DELETE HISTORY_CONTAINER WHERE NO_CONTAINER = '$noCont' AND NO_REQUEST = '$noReq'");
                DB::connection('uster')->statement("UPDATE CONTAINER_STRIPPING SET AKTIF = 'Y' WHERE NO_CONTAINER = '$noCont' AND NO_REQUEST = '$oldno_req'");

                DB::commit();
                return response()->json([
                    'status' => JsonResponse::HTTP_OK,
                    'message' => 'Data Processed',
                ], 200);
            } else {
                throw new Exception('Nomor Container Tidak Ditemukan Di Database', 500);
            }
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => $th->getCode() != '' ? $th->getCode() : 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ], 500);
        }
    }

    function approveReq(array $param)
    {
        DB::beginTransaction();
        try {
            $pdo = DB::getPdo();
            $outMsg = "";
            $declare = "DECLARE
                        in_req_old VARCHAR2(100);
                        in_req_new VARCHAR2(100);
                        in_asalcont VARCHAR2(100);
                        in_iduser VARCHAR2 (100);
                        in_ket VARCHAR2 (100);
                        p_ErrMsg VARCHAR2(100);
                        BEGIN
                                in_req_old := '" . $param['in_req_old'] . "';
                                in_req_new := '" . $param['in_req_new'] . "';
                                in_asalcont := '" . $param['in_asalcont'] . "';
                                in_iduser := '" . $param['in_iduser'] . "';
                                in_ket := '" . $param['in_ket'] . "';
                                p_ErrMsg := 'NULL';
                            USTER.PACK_CREATE_REQ_STRIPPING.CREATE_PERPANJANGAN_STRIP(in_req_old,in_req_new,in_asalcont,in_iduser, in_ket, p_ErrMsg);
                        END;";

            $exec = DB::connection('uster')->statement($declare);
            // $procedureName = " DECLARE
            //             BEGIN USTER.PACK_CREATE_REQ_STRIPPING.CREATE_PERPANJANGAN_STRIP (
            //                 :in_req_old,
            //                 :in_req_new,
            //                 :in_asalcont,
            //                 :in_iduser,
            //                 :in_ket,
            //                 :p_ErrMsg
            //             ); END;";
            // $stmt = $pdo->prepare($procedureName);

            // foreach ($param as $key => &$value) {
            //     $stmt->bindParam(":$key", $value);
            // }

            // $stmt->bindParam(":p_ErrMsg", $outMsg);
            // $stmt->execute();

            DB::commit();
            return response()->json([
                'status' => [
                    'code' => 200,
                    'msg' => 'Success Processing Data',
                ],
                'data' => [
                    'outmsg' => $outMsg,
                ]
            ], 200);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => [
                    'msg' => $th->getMessage() != '' ? $th->getMessage() : 'Err',
                    'code' => 500,
                ],
                'data' => null,
                'err_detail' => $th,
                'message' => $th->getMessage() != '' ? $th->getMessage() : 'Terjadi Kesalahan Saat Input Data, Harap Coba lagi!'
            ]);
        }
    }
}
