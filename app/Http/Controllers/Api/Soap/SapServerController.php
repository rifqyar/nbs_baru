<?php

namespace App\Http\Controllers\Api\Soap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use nguyenanhung\MyNuSOAP\nusoap_server;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class SapServerController extends Controller
{

    public function __invoke(Request $request)
    {
        $server = new nusoap_server();
        $server->configureWSDL('ApiNbs', 'urn:ApiNbs');

        $server->register(
            'getPaymentCode',
            array(),
            array('return' => 'xsd:string'),
            'urn:getPaymentCode',
            'urn:getPaymentCode#getPaymentCode',
            'rpc',
            'encoded',
            'Get Payment Code'
        );

        $server->register(
            'GetStatusPayment',
            array(),
            array('return' => 'xsd:string'),
            'urn:GetStatusPayment',
            'urn:GetStatusPayment#GetStatusPayment',
            'rpc',
            'encoded',
            'Get Status Payment'
        );

        // Use Laravel's response to output the SOAP response
        $response = response($server->service($request->getContent()));

        // Set the appropriate headers for SOAP response
        $response->header('Content-Type', 'text/xml; charset=utf-8');

        return $response;
    }

    /**
     * Fungsi ini digunakan untuk mendapatkan Payment Code dari tabel-tabel nota yang berbeda.
     * Payment Code akan diambil secara acak dari salah satu nota yang belum memiliki Payment Code.
     * Jika Payment Code sudah ada di SAP, maka Payment Code tersebut akan diupdate ke tabel nota yang sesuai.
     * Jika Payment Code tidak ditemukan di SAP, maka akan dikembalikan pesan bahwa Payment Code tidak ditemukan.
     * Jika semua Payment Code sudah ada, maka akan dikembalikan pesan bahwa semua Payment Code sudah tersedia.
     *
     * @return string Pesan hasil operasi yang dilakukan.
     */
    public function getPaymentCode()
    {
        $service = 'getPaymentCode';

        // Main query using the DB facade
        $result = DB::connection('uster')->select("
            SELECT *
            FROM (
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'RECEIVING' AS KEGIATAN FROM nota_receiving
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, CASE WHEN STATUS = 'PERP' THEN 'PERP_PNK' ELSE 'STUFFING' END AS KEGIATAN FROM nota_stuffing
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, CASE WHEN SUBSTR(NO_NOTA, 0, 2) = '03' THEN 'STRIPPING' ELSE 'PERP_STRIP' END AS KEGIATAN FROM nota_stripping
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, CASE WHEN STATUS = 'PERP' THEN 'PERP_DEV' ELSE 'DELIVERY' END AS KEGIATAN FROM nota_delivery
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'RELOKASI' AS KEGIATAN FROM nota_relokasi
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'BATAL_MUAT' AS KEGIATAN FROM nota_batal_muat
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'RELOK_MTY' AS KEGIATAN FROM nota_relokasi_mty
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'DEL_PNK' AS KEGIATAN FROM nota_pnkn_del
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'STUF_PNK' AS KEGIATAN FROM nota_pnkn_stuf
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND (PAYMENT_CODE IS NULL OR PAYMENT_CODE = 'X')
            ) subquery
            JOIN MTI_CUSTOMER_SS.SAP_NOTA_HEADER_NBS_V@CSS_PROD ON subquery.NO_NOTA = SAP_NOTA_HEADER_NBS_V.SOURCE_NOTA_REF
            WHERE SAP_NOTA_HEADER_NBS_V.SAP_KD_BAYAR IS NOT NULL
            ORDER BY DBMS_RANDOM.VALUE
            FETCH NEXT 1 ROWS ONLY
        ");

        // Check if a result was found
        if ($result) {
            $result = $result[0];
            $noNota = $result->NO_NOTA;

            // Check SAP payment code
            $resultSAP = DB::connection('uster')->select("SELECT * FROM MTI_CUSTOMER_SS.SAP_NOTA_HEADER_NBS_V@CSS_PROD WHERE SOURCE_NOTA_REF = ?", [$noNota]);

            if (!empty($resultSAP) && $resultSAP[0]->SAP_KD_BAYAR !== null) {
                $paymentCode = $resultSAP[0]->SAP_KD_BAYAR;
                $tb_name = $this->getTableName($result->KEGIATAN);

                // Update payment code in the respective table
                $update = DB::connection('uster')->update("UPDATE $tb_name SET PAYMENT_CODE = ? WHERE NO_NOTA = ?", [$paymentCode, $noNota]);

                if ($update) {
                    $this->logService($service, "Inserted Payment Code $noNota");
                    return "Inserted Payment Code $noNota";
                } else {
                    $this->logService($service, "Failed Inserted Payment Code $noNota", false);
                    return "Failed Inserted Payment Code $noNota";
                }
            } else {
                $this->logService($service, "Payment Code Not Found $noNota", false);
                return "Payment Code Not Found $noNota";
            }
        } else {
            return "All Payment Codes Are Available";
        }
    }

    private function getTableName($kegiatan)
    {
        // Return the table name based on KEGIATAN
        switch ($kegiatan) {
            case 'RECEIVING':
                return 'NOTA_RECEIVING';
            case 'STUFFING':
            case 'PERP':
            case 'PERP_PNK':
                return 'NOTA_STUFFING';
            case 'STRIPPING':
            case 'PERP_STRIP':
                return 'NOTA_STRIPPING';
            case 'DELIVERY':
            case 'PERP_DEV':
                return 'NOTA_DELIVERY';
            case 'RELOKASI':
                return 'NOTA_RELOKASI';
            case 'BATAL_MUAT':
                return 'NOTA_BATAL_MUAT';
            case 'RELOK_MTY':
                return 'NOTA_RELOKASI_MTY';
            case 'DEL_PNK':
                return 'NOTA_PNKN_DEL';
            case 'STUF_PNK':
                return 'NOTA_PNKN_STUF';
            default:
                return null;
        }
    }

    private function logService($service, $message, $success = true)
    {
        $code = $success ? "1" : "0";
        $logData = json_encode(['code' => $code, 'msg' => $message]);

        // Log into the service log table
        DB::connection('uster')->insert("
            INSERT INTO USTER.SAP_SERVICE_LOG (URL, PAYLOAD, RESPONSE, NOTES)
            VALUES (?, NULL, ?, ?)
        ", [$service, $logData, $message]);

        Log::info($message);
    }



    /**
     * Fungsi GetStatusPayment digunakan untuk mendapatkan status pembayaran dari beberapa tabel nota berdasarkan kriteria tertentu.
     * Fungsi ini mengambil data dari tabel nota_receiving, nota_stuffing, nota_stripping, nota_delivery, nota_relokasi, nota_batal_muat, nota_relokasi_mty, nota_pnkn_del, dan nota_pnkn_stuf.
     * Data yang diambil adalah data yang memiliki STATUS bukan 'BATAL', NO_NOTA dan PAYMENT_CODE tidak null, TANGGAL_LUNAS null, dan LUNAS 'NO'.
     * Data yang diambil diurutkan secara acak dan hanya diambil 1 baris.
     * Jika ditemukan data, fungsi ini akan melakukan pengecekan apakah Payment Code sudah ada di SAP.
     * Jika Payment Code sudah ada di SAP, fungsi ini akan melakukan beberapa operasi seperti menyimpan pembayaran dengan service Praya dan memanggil service NBS.
     * Jika operasi berhasil, fungsi ini akan mengembalikan pesan "Status Payment Changed to Paid [NO_NOTA]".
     * Jika operasi gagal, fungsi ini akan mengembalikan pesan error.
     * Jika tidak ditemukan data, fungsi ini akan mengembalikan pesan "No New Payments Yet".
     *
     * @return string Pesan status pembayaran atau error.
     */
    public function getStatusPayment()
    {
        $service = 'GetStatusPayment';

        // Main query using the DB facade
        $result = DB::connection('oracle')->select("
            SELECT *
            FROM (
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'RECEIVING' AS KEGIATAN, TANGGAL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_receiving
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TANGGAL_LUNAS IS NULL AND LUNAS = 'NO'
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, CASE WHEN STATUS = 'PERP' THEN 'PERP_PNK' ELSE 'STUFFING' END AS KEGIATAN, TANGGAL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_stuffing
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TANGGAL_LUNAS IS NULL AND LUNAS = 'NO'
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, CASE WHEN SUBSTR(NO_NOTA, 0, 2) = '03' THEN 'STRIPPING' ELSE 'PERP_STRIP' END AS KEGIATAN, TANGGAL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_stripping
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TANGGAL_LUNAS IS NULL AND LUNAS = 'NO'
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, CASE WHEN STATUS = 'PERP' THEN 'PERP_DEV' ELSE 'DELIVERY' END AS KEGIATAN, TANGGAL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_delivery
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TANGGAL_LUNAS IS NULL AND LUNAS = 'NO'
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'RELOKASI' AS KEGIATAN, TANGGAL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_relokasi
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TANGGAL_LUNAS IS NULL AND LUNAS = 'NO'
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'BATAL_MUAT' AS KEGIATAN, TGL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_batal_muat
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TGL_LUNAS IS NULL AND LUNAS = 'NO'
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'RELOK_MTY' AS KEGIATAN, TANGGAL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_relokasi_mty
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TANGGAL_LUNAS IS NULL AND LUNAS = 'NO'
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'DEL_PNK' AS KEGIATAN, TANGGAL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_pnkn_del
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TANGGAL_LUNAS IS NULL AND LUNAS = 'NO'
                UNION
                SELECT NO_NOTA, TGL_NOTA, PAYMENT_CODE, 'STUF_PNK' AS KEGIATAN, TANGGAL_LUNAS, LUNAS, NO_NOTA_MTI FROM nota_pnkn_stuf
                WHERE STATUS <> 'BATAL' AND NO_NOTA IS NOT NULL AND PAYMENT_CODE IS NOT NULL AND TANGGAL_LUNAS IS NULL AND LUNAS = 'NO'
            ) subquery
            JOIN MTI_CUSTOMER_SS.SAP_NOTA_HEADER_NBS_V@CSS_PROD ON subquery.NO_NOTA = SAP_NOTA_HEADER_NBS_V.SOURCE_NOTA_REF
            WHERE SAP_NOTA_HEADER_NBS_V.SAP_KD_BAYAR IS NOT NULL AND SAP_NOTA_HEADER_NBS_V.SAP_TGL_PELUNASAN IS NOT NULL AND SAP_NOTA_HEADER_NBS_V.SAP_BANK IS NOT NULL
            ORDER BY DBMS_RANDOM.VALUE FETCH NEXT 1 ROWS ONLY
        ");

        // Check if any payment records were found
        if ($result) {
            $result = $result[0]; // Retrieve the first result
            $noNota = $result->NO_NOTA;

            // Query for the SAP payment details
            $resultSAP = DB::connection('oracle')->select("
                SELECT TO_CHAR(SAP_TGL_PELUNASAN, 'YYYY-MM-DD HH24:MI:SS') AS TANGGAL_PELUNASAN, SAP_BANK, SAP_NO_FAKTUR
                FROM MTI_CUSTOMER_SS.SAP_NOTA_HEADER_NBS_V@CSS_PROD
                WHERE SAP_KD_BAYAR IS NOT NULL AND SAP_TGL_PELUNASAN IS NOT NULL AND SAP_BANK IS NOT NULL AND SOURCE_NOTA_REF = ?
            ", [$noNota]);

            if ($resultSAP) {
                $resultSAP = $resultSAP[0]; // Retrieve the first result from SAP query
                $tb_name = $this->getTableName($result->KEGIATAN);

                $PaymentDate = $resultSAP->TANGGAL_PELUNASAN;
                $SapFaktur = $resultSAP->SAP_NO_FAKTUR;
                $noNotaMti = $result->NO_NOTA_MTI;
                $sapBank = $resultSAP->SAP_BANK;

                // Retrieve the bank ID
                $bank = DB::connection('oracle')->select("
                    SELECT BANK_ID from BILLING_NBS.mst_bank_simkeu WHERE BANK_ACCOUNT_NAME = ?
                ", [$sapBank]);

                $idBank = $bank ? $bank[0]->BANK_ID : null;

                // Call NBS service (assume a service method exists)
                $data = $this->SapPaymentPaid($SapFaktur, $noNotaMti, false, $idBank, false, false, $PaymentDate);

                if ($data == 'Berhasil') {
                    // Call Praya service (assume a method for saving payment exists)
                    $uster = $this->save_payment_uster_external($result, $result->KEGIATAN, $idBank);
                    $response = json_decode($uster['response']);

                    if ($response->code == '1') {
                        $msg = [
                            'code' => true,
                            'msg' => "Status Payment Changed to Paid $noNota"
                        ];
                        $log_notes = "Status Payment Changed to Paid $noNota";
                    } else {
                        $msg = [
                            'code' => false,
                            'message' => $uster
                        ];
                        $log_notes = "Failed Praya Status Payment Changed to Paid $noNota";
                    }
                } else {
                    $msg = [
                        'code' => false,
                        'message' => $data
                    ];
                    $log_notes = "Failed NBS Status Payment Changed to Paid $noNota";
                }

                // Log the result
                DB::connection('oracle')->insert("
                    INSERT INTO USTER.SAP_SERVICE_LOG (URL, PAYLOAD, RESPONSE, NOTES, PRAYA_RESPONSE)
                    VALUES (?, ?, ?, ?, ?)
                ", [$service, $noNota, json_encode($msg), $log_notes, json_encode($uster)]);

                return $log_notes;
            } else {
                return "No Nota Unpaid $noNota";
            }
        } else {
            return "No New Payments Yet";
        }
    }


    #Helper Function
    #----------------------------------------------------------------------------#

    /**
     * Fungsi SapPaymentPaid digunakan untuk melakukan pembayaran pada sistem SAP.
     * Fungsi ini akan melakukan koneksi ke database USTER dan melakukan query untuk mendapatkan jenis transaksi berdasarkan nomor transaksi.
     * Jika jenis transaksi ditemukan, fungsi akan melakukan validasi terhadap status dan pembayaran transaksi.
     * Jika pembayaran sudah dilakukan atau status transaksi dibatalkan, fungsi akan mengembalikan pesan error.
     * Jika pembayaran belum dilakukan, fungsi akan melakukan query untuk mendapatkan informasi rekening bank.
     * Jika informasi rekening bank ditemukan, fungsi akan melakukan validasi terhadap jenis transaksi untuk menentukan apakah pembayaran perlu dilakukan di TPK atau tidak.
     * Jika pembayaran perlu dilakukan di TPK, fungsi akan melakukan koneksi ke database OPUS_REPO dan melakukan proses flagging pembayaran.
     * Jika proses flagging pembayaran berhasil, fungsi akan mengembalikan pesan sukses.
     * Jika terjadi kesalahan dalam proses koneksi atau query, fungsi akan mengembalikan pesan error.
     *
     * @param string $faktur Nomor faktur
     * @param string $trx_number Nomor transaksi
     * @param string $user_id ID pengguna
     * @param string $bank_id ID bank
     * @param string $paid_date Tanggal pembayaran
     * @param string $paid_channel Channel pembayaran
     * @param string $date Tanggal
     * @return string Pesan sukses atau pesan error
     */
    function SapPaymentPaid($faktur, $trx_number, $user_id, $bank_id, $paid_date, $paid_channel, $date)
    {

        $conn = oci_connect('uster', 'uster', '10.15.42.43/datamti');
        // check connection if fails return error
        if (!$conn) {
            // close connection
            oci_close($conn);
            return "^87^ORA - Database problem, cant connect to database.";
        }

        $jenis = '';

        //query from nota_receiving
        if (empty($jenis)) {
            $query   = "SELECT * FROM NOTA_RECEIVING WHERE NO_NOTA_MTI='{$trx_number}'";
            $parse   = oci_parse($conn, $query);
            $execute = oci_execute($parse);

            if (!$execute) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row  = oci_fetch_assoc($parse);

            if (!empty($row)) {
                $jenis = 'RECEIVING';
            }
        }
        //query from nota_stuffing
        if (empty($jenis)) {
            $query   = "SELECT * FROM NOTA_STUFFING WHERE NO_NOTA_MTI='{$trx_number}'";
            $parse   = oci_parse($conn, $query);
            $execute = oci_execute($parse);

            if (!$execute) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row  = oci_fetch_assoc($parse);

            if (!empty($row)) {
                if ($row['STATUS'] == 'PERP') {
                    $jenis = 'PERP_PNK';
                } else {
                    $jenis = 'STUFFING';
                }
            }
        }
        // query from pnkn stuffing
        if (empty($jenis)) {
            $query   = "SELECT * FROM NOTA_PNKN_STUF WHERE NO_NOTA_MTI='{$trx_number}'";
            $parse   = oci_parse($conn, $query);
            $execute = oci_execute($parse);

            if (!$execute) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row  = oci_fetch_assoc($parse);

            if (!empty($row)) {
                $jenis = 'STUF_PNK';
            }
        }
        //query from nota_stripping
        if (empty($jenis)) {
            $query   = "SELECT * FROM NOTA_STRIPPING WHERE NO_NOTA_MTI='{$trx_number}'";
            $parse   = oci_parse($conn, $query);
            $execute = oci_execute($parse);

            if (!$execute) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row  = oci_fetch_assoc($parse);

            if (!empty($row)) {
                if ($row['STATUS'] == 'PERP') {
                    $jenis = 'PERP_STRIP';
                } else {
                    $jenis = 'STRIPPING';
                }
            }
        }
        //query from nota_delivery
        if (empty($jenis)) {
            $query   = "SELECT * FROM NOTA_DELIVERY WHERE NO_NOTA_MTI='{$trx_number}'";
            $parse   = oci_parse($conn, $query);
            $execute = oci_execute($parse);

            if (!$execute) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row  = oci_fetch_assoc($parse);

            if (!empty($row)) {
                if ($row['STATUS'] == 'PERP') {
                    $jenis = 'PERP_DEV';
                } else {
                    $jenis = 'DELIVERY';
                }
            }
        }
        // query from pnkn delivery
        if (empty($jenis)) {
            $query   = "SELECT * FROM NOTA_PNKN_DEL WHERE NO_NOTA_MTI='{$trx_number}'";
            $parse   = oci_parse($conn, $query);
            $execute = oci_execute($parse);

            if (!$execute) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row  = oci_fetch_assoc($parse);

            if (!empty($row)) {
                $jenis = 'DEL_PNK';
            }
        }
        //query from nota_batal_muat
        if (empty($jenis)) {
            $query   = "SELECT * FROM NOTA_BATAL_MUAT WHERE NO_NOTA_MTI='{$trx_number}'";
            $parse   = oci_parse($conn, $query);
            $execute = oci_execute($parse);

            if (!$execute) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row  = oci_fetch_assoc($parse);

            if (!empty($row)) {
                $jenis = 'BATAL_MUAT';
            }
        }
        // query from relokasi_mty
        if (empty($jenis)) {
            $query   = "SELECT * FROM NOTA_RELOKASI_MTY WHERE NO_NOTA_MTI='{$trx_number}'";
            $parse   = oci_parse($conn, $query);
            $execute = oci_execute($parse);

            if (!$execute) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row  = oci_fetch_assoc($parse);

            if (!empty($row)) {
                $jenis = 'RELOK_MTY';
            }
        }

        // check if row not empty
        if (!empty($row)) {

            // if status is batal, return error
            if ($row['STATUS'] == 'BATAL') {
                // close connection
                oci_close($conn);
                return '^C0^Bill Suspend';
            }

            // if lunas is not equal NO, return error
            if ($row['LUNAS'] != 'NO') {
                // close connection
                oci_close($conn);
                return '^B8^Already Paid';
            }

            $query_bank = "SELECT bank_account_name
                   from BILLING_NBS.mst_bank_simkeu
                   WHERE bank_id = '{$bank_id}'";

            $parse_bank   = oci_parse($conn, $query_bank);
            $execute_bank = oci_execute($parse_bank);

            if (!$execute_bank) {
                // close connection
                oci_close($conn);
                return '^87^ORA - Table / View / Column not found';
            }

            $row_bank  = oci_fetch_assoc($parse_bank);

            if (empty($row_bank)) {
                oci_close($conn);
                return "^B5^Not Found^";
            }

            $flag_opus = false; //set default

            // query to get asal_cont from container_stuffing to validate from TPK
            if ($jenis == 'DELIVERY') {
                // query for get asal cont from request delivery
                $query_cont = "select delivery_ke, no_req_ict from request_delivery where no_request = '{$row['NO_REQUEST']}'";
                $parse_cont = oci_parse($conn, $query_cont);
                $execute_cont = oci_execute($parse_cont);
                $row_cont = oci_fetch_assoc($parse_cont);

                if ($row_cont['DELIVERY_KE'] == 'TPK') {
                    $flag_opus = true;
                    $reqopus = $row_cont['NO_REQ_ICT'];
                }
            } else if ($jenis == 'STUFFING' || $jenis == 'PERP_PNK') {
                //query for get asal cont from container_stuffing
                $query_cont = "select asal_cont from container_stuffing WHERE no_request = '{$row['NO_REQUEST']}'";
                $parse_cont = oci_parse($conn, $query_cont);
                $execute_cont = oci_execute($parse_cont);
                $row_cont = oci_fetch_assoc($parse_cont);

                // if ($row_count['ASAL_CONT'] == 'TPK') {
                //     //query for get asal cont from request_stuffing
                //     $query_opus = "select o_reqnbs from request_stuffing where no_request = '{$row['NO_REQUEST']}'";
                //     $parse_opus = oci_parse($conn, $query_opus);
                //     $execute_opus = oci_execute($parse_opus);
                //     $row_opus = oci_fetch_assoc($parse_opus);
                //     $flag_opus = true;
                //     $reqopus = $row_opus['O_REQNBS'];
                // }
            } else if ($jenis == 'STRIPPING' || $jenis == 'PERP_STRIP') {
                //query for get asal cont from container_stripping
                $query_opus = "select o_reqnbs from request_stripping WHERE no_request = '{$row['NO_REQUEST']}'";
                $parse_opus = oci_parse($conn, $query_opus);
                $execute_opus = oci_execute($parse_opus);
                $row_opus = oci_fetch_assoc($parse_opus);
                $flag_opus = true;
                $reqopus = $row_opus['O_REQNBS'];
            } else if ($jenis == 'BATAL_MUAT') {
                $query_cont = "select status_gate,o_reqnbs from request_batal_muat where no_request = '{$row['NO_REQUEST']}'";
                $parse_cont = oci_parse($conn, $query_cont);
                $execute_cont = oci_execute($parse_cont);
                $row_cont = oci_fetch_assoc($parse_cont);
                if ($row_cont['STATUS_GATE'] == '2') {
                    $flag_opus = true;
                    $reqopus = $row_cont['O_REQNBS'];
                }
            }

            // check flag opus is true, disabled for development
            if ($flag_opus == true) {
                // connect to opus_repo, flagging payment to opus
                $conn_opus = oci_connect('OPUS_REPO', 'OPUS_REPO', '10.15.42.43/datamti');

                if (!$conn_opus) {
                    return "^87^ORA - Database problem, cant connect to database. OPUS BILL";
                    die();
                }

                $out_status = '';
                $outmsg = '';
                //
                // // call procedure for flagging payment opus
                $query_payment = "declare begin payment_opusbill(:ID_REQ,:ID_NOTA,:OUT,:OUT_MSG); end;";
                $parse_payment = oci_parse($conn_opus, $query_payment);
                //
                oci_bind_by_name($parse_payment, "ID_REQ", $reqopus, 30) or die('Can not bind variable');
                oci_bind_by_name($parse_payment, "ID_NOTA", $row['NO_NOTA'], 50) or die('Can not bind variable');
                oci_bind_by_name($parse_payment, "OUT", $outstatus, 20) or die('Can not bind variable');
                oci_bind_by_name($parse_payment, "OUT_MSG", $outmsg, 100) or die('Can not bind variable');
                //
                $execute_opus = oci_execute($parse_payment);

                if (!$execute_opus) {
                    // Get OCI error
                    $error = oci_error($parse_payment);

                    // Close connections
                    oci_close($parse_payment);
                    oci_close($conn_opus);
                    oci_close($conn);
                    return '^01^General Failure - OPUS PAYMENT NOT GENERATED. OCI Error: ';
                    die();
                }
            }

            //set for default payment by ILCS
            $user_id = 0;
            $via = 'BANK';
            $bank_name = $row_bank['BANK_ACCOUNT_NAME'];
            $out_trx_number = '';

            // call procedure for flagging payment
            $query_insert_inota = "BEGIN
        USTER.SAP_INSERT_STAGING.INSERT_NOTA_ITPK(
            :ID_LUNAS,
            :ID_FAKTUR,
            :ID_REQ,
            :IN_MODUL,
            :IN_PROFORMA,
            :IN_NOTA,
            :IN_KOREKSI,
            :IN_USERID,
            :IN_BANKID,
            :IN_VIA,
            :IN_EMKL,
            :IN_JUM,
            :IN_MTI,
            :IN_MATERAI,
            :OUT_TRX_NUMBER);
        END;";

            $materai = null;

            $parse_insert_inota = oci_parse($conn, $query_insert_inota) or die('oci parse failed');

            oci_bind_by_name($parse_insert_inota, "ID_LUNAS", $date, 30) or die('Can not bind variable tgl_lunas');
            oci_bind_by_name($parse_insert_inota, "ID_FAKTUR", $faktur, 30) or die('Can not bind variable id_faktur');
            oci_bind_by_name($parse_insert_inota, "ID_REQ", $row['NO_REQUEST'], 30) or die('Can not bind variable id_req');
            oci_bind_by_name($parse_insert_inota, "IN_MODUL", $jenis, 50) or die('Can not bind variable in_modul');
            oci_bind_by_name($parse_insert_inota, "IN_PROFORMA", $row['NO_NOTA'], 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_NOTA", $row['NO_NOTA'], 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_KOREKSI", $row['STATUS'], 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_USERID", $user_id, 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_BANKID", $bank_name, 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_VIA", $via, 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_EMKL", $row['KD_EMKL'], 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_JUM", $row['TOTAL_TAGIHAN'], 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_MTI", $row['NO_NOTA_MTI'], 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "IN_MATERAI", $materai, 100) or die('Can not bind variable');
            oci_bind_by_name($parse_insert_inota, "OUT_TRX_NUMBER", $out_trx_number, 100) or die('Can not bind variable out_trx_number');

            $execute_nota_itpk = oci_execute($parse_insert_inota);

            // if fails execute query return error
            if (!$execute_nota_itpk) {
                $error = oci_error($parse_insert_inota);
                return "Error: " . $error['message'];
                die();
            }

            // get all data from itpk nota header
            $query_itpk = "SELECT * FROM ITPK_NOTA_HEADER WHERE TRX_NUMBER='{$out_trx_number}'";
            $parse_itpk = oci_parse($conn, $query_itpk);
            $execute_itpk = oci_execute($parse_itpk);
            $row_itpk = oci_fetch_assoc($parse_itpk);
            $limit_materai = $row_itpk['KREDIT'] - 3000;

            // check nota have need e-materai
            if ($limit_materai > 250000) {
                $peraturan = 'SI-00153/SK/WPJ.19/KP.0403/2020';
                $update_materai = "UPDATE ITPK_NOTA_HEADER SET NO_PERATURAN='{$peraturan}' WHERE TRX_NUMBER='{$out_trx_number}'";
                $parse_update_materai = oci_parse($conn, $update_materai);
                $execute_update_materai = oci_execute($parse_update_materai);
            }

            //query from nota_receiving
            if ($jenis == 'RECEIVING') {
                $query   = "SELECT * FROM NOTA_RECEIVING WHERE NO_NOTA_MTI='{$trx_number}'";
                $parse   = oci_parse($conn, $query);
                $execute = oci_execute($parse);

                if (!$execute) {
                    // close connection
                    oci_close($conn);
                    return '^87^ORA - Table / View / Column not found';
                }

                $row  = oci_fetch_assoc($parse);
            }
            //query from nota_stuffing
            if ($jenis == 'STUFFING' || $jenis == 'PERP_PNK') {
                $query   = "SELECT * FROM NOTA_STUFFING WHERE NO_NOTA_MTI='{$trx_number}'";
                $parse   = oci_parse($conn, $query);
                $execute = oci_execute($parse);

                if (!$execute) {
                    // close connection
                    oci_close($conn);
                    return '^87^ORA - Table / View / Column not found';
                }

                $row  = oci_fetch_assoc($parse);
            }
            //query from pnkn stuffing
            if ($jenis == 'STUF_PNK') {
                $query   = "SELECT * FROM NOTA_PNKN_STUF WHERE NO_NOTA_MTI='{$trx_number}'";
                $parse   = oci_parse($conn, $query);
                $execute = oci_execute($parse);

                if (!$execute) {
                    // close connection
                    oci_close($conn);
                    return '^87^ORA - Table / View / Column not found';
                }

                $row  = oci_fetch_assoc($parse);
            }
            //query from nota_stripping
            if ($jenis == 'STRIPPING' || $jenis == 'PERP_STRIP') {
                $query   = "SELECT * FROM NOTA_STRIPPING WHERE NO_NOTA_MTI='{$trx_number}'";
                $parse   = oci_parse($conn, $query);
                $execute = oci_execute($parse);

                if (!$execute) {
                    // close connection
                    oci_close($conn);
                    return '^87^ORA - Table / View / Column not found';
                }

                $row  = oci_fetch_assoc($parse);
            }
            //query from nota_delivery
            if ($jenis == 'DELIVERY' || $jenis == 'PERP_DEV') {
                $query   = "SELECT * FROM NOTA_DELIVERY WHERE NO_NOTA_MTI='{$trx_number}'";
                $parse   = oci_parse($conn, $query);
                $execute = oci_execute($parse);

                if (!$execute) {
                    // close connection
                    oci_close($conn);
                    return '^87^ORA - Table / View / Column not found';
                }

                $row  = oci_fetch_assoc($parse);
            }

            // query from pnkn delivery
            if ($jenis == 'DEL_PNK') {
                $query   = "SELECT * FROM NOTA_PNKN_DEL WHERE NO_NOTA_MTI='{$trx_number}'";
                $parse   = oci_parse($conn, $query);
                $execute = oci_execute($parse);

                if (!$execute) {
                    // close connection
                    oci_close($conn);
                    return '^87^ORA - Table / View / Column not found';
                }

                $row  = oci_fetch_assoc($parse);
            }
            //query from nota_batal_muat
            if ($jenis == 'BATAL_MUAT') {
                $query   = "SELECT * FROM NOTA_BATAL_MUAT WHERE NO_NOTA_MTI='{$trx_number}'";
                $parse   = oci_parse($conn, $query);
                $execute = oci_execute($parse);

                if (!$execute) {
                    // close connection
                    oci_close($conn);
                    return '^87^ORA - Table / View / Column not found';
                }

                $row  = oci_fetch_assoc($parse);
            }
            //query from relokasi mty
            if ($jenis == 'RELOK_MTY') {
                $query   = "SELECT * FROM NOTA_RELOKASI_MTY WHERE NO_NOTA_MTI='{$trx_number}'";
                $parse   = oci_parse($conn, $query);
                $execute = oci_execute($parse);

                if (!$execute) {
                    // close connection
                    oci_close($conn);
                    return '^87^ORA - Table / View / Column not found';
                }

                $row  = oci_fetch_assoc($parse);
            }


            // close connection
            oci_close($conn);

            return 'Berhasil';
        } else {
            // close connection
            oci_close($conn);
            // data not found
            return "^B5^Not Found";
        }
    }


    /**
     * Fungsi ini digunakan untuk menyimpan pembayaran eksternal Uster.
     *
     * @param array $nota - Data nota yang berisi NO_REQUEST.
     * @param string $kegiatan - Jenis kegiatan.
     * @param string $bank_id - Nomor rekening bank.
     * @return array - Hasil dari permintaan penyimpanan pembayaran.
     */
    function save_payment_uster_external($nota, $kegiatan, $bank_id)
    {
        // return array(
        //     "code" => "1",
        //     "msg" => "Success",
        //     "dataRec" => array(
        //         "idRequest" => $nota['NO_REQUEST'],
        //         "trxNumber" => $nota['NO_NOTA']
        //     )
        // );

        $NO_REQUEST = $nota['NO_REQUEST'];


        set_time_limit(3600);

        try {

            $curl = curl_init();
            /* set configure curl */
            // $authorization = "Authorization: Bearer $token";
            $payload_request = array(
                "ID_REQUEST" => $NO_REQUEST,
                "JENIS" => $kegiatan,
                "BANK_ACCOUNT_NUMBER" => $bank_id,
                "PAYMENT_CODE" => ''
            );
            // echo json_encode($payload_request) . '<<payload_req';
            $url = env('SEND_PRAYA_LINK');

            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL             => $url,
                    CURLOPT_RETURNTRANSFER  => true,
                    CURLOPT_ENCODING        => "",
                    CURLOPT_MAXREDIRS       => 10,
                    CURLOPT_CUSTOMREQUEST   => "POST",
                    CURLOPT_POSTFIELDS      => json_encode($payload_request),
                    CURLOPT_HTTPHEADER      => array(
                        "Content-Type: application/json"
                    ),
                )
            );



            $response = curl_exec($curl);

            if ($response === false) {
                throw new Exception(curl_error($curl));
            }

            // Get HTTP status code
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            //Success
            if ($httpCode >= 200 && $httpCode < 300) {
                $response_curl = array(
                    'no_request' => $NO_REQUEST,
                    "jenis" => $kegiatan,
                    "bank_account_number" => $bank_id,
                    'status'   => 'success',
                    'httpCode' => $httpCode,
                    'response' => $response
                );
            } else if ($httpCode >= 400 && $httpCode < 500) {
                //Client Error
                $response_curl = array(
                    'no_request' => $NO_REQUEST,
                    "jenis" => $kegiatan,
                    "bank_account_number" => $bank_id,
                    'status'   => 'error',
                    'httpCode' => $httpCode,
                    'response' => $response
                );
            } else {
                //Server Error
                throw new Exception('HTTP Server Error: ' . $httpCode);
            }

            /* execute curl */
            curl_close($curl);

            return $response_curl;
        } catch (Exception $e) {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $response_curl = array(
                'no_request' => $NO_REQUEST,
                "jenis" => $kegiatan,
                "bank_account_number" => $bank_id,
                'status'   => 'error',
                'url' => $url,
                'httpCode' => $httpCode,
                'response' => "cURL Error # " . $e->getMessage()
            );

            return $response_curl;
        }
    }
}
