<?php

namespace App\Services\Others;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PrayaService
{
    function getDatafromUrl($url)
    {
        $token = $this->getTokenPraya();

        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 0,      // timeout on connect
            CURLOPT_TIMEOUT        => 0,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            // dicomment, kena error ssl ca cert
            // CURLOPT_CAINFO		   => "/var/www/html/ibis_qa/tmp/cacert.pem",
            // CURLOPT_SSL_VERIFYPEER => false, // <- dihapus sebelum di push
            CURLOPT_HTTPHEADER      => array(
                "Content-Type: application/json",
                "Authorization: Bearer $token",
            ),
        );

        $ch      = curl_init($url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $err     = curl_errno($ch);
        $errmsg  = curl_error($ch);
        $header  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        // $header['errno']   = $err;
        // $header['errmsg']  = $errmsg;

        //change errmsg here to errno
        if ($errmsg) {
            echo "CURL:" . $errmsg . "<BR>";
        }
        return $content;
    }

    function getTokenPraya()
    {
        $data_payload = array(
            "username" => "adminnbs",
            "password" => "Nbs2023!",
            "statusApp" => "Web"
        );
        $response = $this->sendDataFromUrl($data_payload, env('PRAYA_API_LOGIN') . "/api/login");
        $obj = json_decode($response['response'], true);
        return $obj["token"];
    }

    function sendDataFromUrl($payload_request, $url, $method = "POST", $token = "")
    {
        $curl = curl_init();
        /* set configure curl */
        $authorization = "Authorization: Bearer $token";
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL             => $url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => "",
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 60,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => $method,
                CURLOPT_POSTFIELDS      => json_encode($payload_request),
                CURLOPT_HTTPHEADER      => array(
                    "Content-Type: application/json",
                    $authorization
                ),
                // CURLOPT_SSL_VERIFYPEER => false // <- dihapus sebelum di push
            )
        );

        /* execute curl */
        $response = curl_exec($curl);
        $err      = curl_error($curl);

        curl_close($curl);

        /* get response */
        if ($err) {
            $response_curl = array(
                'status'   => 'error',
                'response' => "cURL Error #:" . $err
            );
        } else {
            $response_curl = array(
                'status'   => 'success',
                'response' => $response
            );
        }

        return $response_curl;
    }
}
