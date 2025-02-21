<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Exception;

trait NpwpCheckPengkinianTrait
{
    public function validateNpwp($request)
    {
        // Check for multiple NPWP fields and select the one that's not null
        $npwpFields = ['npwp_consignee','npwp','NPWP'];
        $NPWP_CONSIGNEE = null;

        foreach ($npwpFields as $field) {
            if ($request->input($field)) {
                $NPWP_CONSIGNEE = $request->input($field);
                break;
            }
        }

        if (is_null($NPWP_CONSIGNEE)) {
            return response()->json([
                "status" => "0",
                "message" => "No NPWP field provided"
            ]);
        }

        // Store the default NPWP value
        $NPWP_DEFAULT = $NPWP_CONSIGNEE;

        // Remove non-numeric characters
        $NPWP_CONSIGNEE = preg_replace("/[^0-9]/", "", $NPWP_CONSIGNEE);

        try {
            // Query to check if NPWP exists in the local database (lowercase column names)
            $result = DB::connection('uster')->table('mst_pelanggan')
                ->select('no_npwp_pbm16')
                ->where('no_npwp_pbm', $NPWP_DEFAULT)
                ->orWhere('no_npwp_pbm16', $NPWP_DEFAULT)
                ->first();

            $NPWP16 = $result ? $result->no_npwp_pbm16 : null;

            if ($NPWP16 == null) {
                // Use Guzzle to make an API request to validate NPWP
                $client = new Client();
                try {
                    $response = $client->post('https://ibs-unicorn.pelindo.co.id/api/ApiBupot/ValidasiNpwpV3', [
                        'query' => ['NPWP' => $NPWP_CONSIGNEE], // NPWP as query string
                        'headers' => ['Content-Type' => 'application/json']
                    ]);

                    $response_data = json_decode($response->getBody(), true);

                    if ($response_data['status'] == 1) {
                        $NPWP16 = $response_data['data']['npwp16'];

                        // Update the NPWP16 in the local database (lowercase column names)
                        $updateNPWP = DB::connection('uster')->table('mst_pelanggan')
                            ->where('no_npwp_pbm', $NPWP_DEFAULT)
                            ->update(['no_npwp_pbm16' => $NPWP16]);

                        if ($updateNPWP) {
                            // Only proceed if NPWP is successfully updated to 16 characters
                            if (strlen($NPWP16) == 16) {
                                // Update the original request value to 16-character NPWP
                                foreach ($npwpFields as $field) {
                                    if ($request->input($field)) {
                                        $request->merge([$field => $NPWP16]);
                                    }
                                }
                                return $NPWP16;
                            } else {
                                return response()->json([
                                    "status" => "0",
                                    "message" => "DATA Gagal Melakukan Pengkinian NPWP (Invalid NPWP16 length)"
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => "0",
                                "message" => "DATA Gagal Melakukan Pengkinian NPWP"
                            ]);
                        }
                    } else {
                        return response()->json([
                            "status" => "0",
                            "message" => "DATA Belum Melakukan Pengkinian NPWP"
                        ]);
                    }
                } catch (RequestException $e) {
                    return response()->json([
                        "status" => "0",
                        "message" => "HTTP Request Error: " . $e->getMessage()
                    ]);
                }
            } elseif (strlen($NPWP16) == 16) {
                // Proceed if NPWP16 is already valid
                foreach ($npwpFields as $field) {
                    if ($request->input($field)) {
                        $request->merge([$field => $NPWP16]);
                    }
                }
                return $NPWP16;
            } else {
                return response()->json([
                    "status" => "0",
                    "message" => "Invalid NPWP_CONSIGNEE length"
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => "0",
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }


    public function validateNpwpEMKL($request)
    {
        // Check for EMKL field
        $EMKL = $request->input('EMKL');
    
        if (is_null($EMKL)) {
            return response()->json([
                "status" => "0",
                "message" => "No EMKL field provided"
            ]);
        }
    
        try {
            // Query to check if NPWP exists in the local database (corrected column names)
            $result = DB::connection('uster')->table('mst_pelanggan')
                ->select('nm_pbm', 'no_npwp_pbm16', 'no_npwp_pbm') // Correct column names
                ->where('nm_pbm', $EMKL) // Use correct column for EMKL (NM_PBM)
                ->first();
    
            $NPWP16 = $result ? $result->no_npwp_pbm16 : null;
            $NPWP15 = $result ? $result->no_npwp_pbm : null;
    
            if ($NPWP16 == null) {
                // Use Guzzle to make an API request to validate NPWP
                $client = new Client();
                try {
                    $response = $client->post('https://ibs-unicorn.pelindo.co.id/api/ApiBupot/ValidasiNpwpV3', [
                        'query' => ['NPWP' => $NPWP15], // NPWP as query string
                        'headers' => ['Content-Type' => 'application/json']
                    ]);
    
                    $response_data = json_decode($response->getBody(), true);
    
                    if ($response_data['status'] == 1) {
                        $NPWP16 = $response_data['data']['npwp16'];
    
                        // Update the NPWP16 in the local database (correct column names)
                        $updateNPWP = DB::connection('uster')->table('mst_pelanggan')
                            ->where('no_npwp_pbm', $NPWP15)
                            ->update(['no_npwp_pbm16' => $NPWP16]);
    
                        if ($updateNPWP) {
                            // Only proceed if NPWP is successfully updated to 16 characters
                            if (strlen($NPWP16) == 16) {
                                return $NPWP16;
                            } else {
                                return response()->json([
                                    "status" => "0",
                                    "message" => "DATA Gagal Melakukan Pengkinian NPWP (Invalid NPWP16 length)"
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => "0",
                                "message" => "DATA Gagal Melakukan Pengkinian NPWP"
                            ]);
                        }
                    } else {
                        return response()->json([
                            "status" => "0",
                            "message" => "DATA Belum Melakukan Pengkinian NPWP"
                        ]);
                    }
                } catch (RequestException $e) {
                    return response()->json([
                        "status" => "0",
                        "message" => "HTTP Request Error: " . $e->getMessage()
                    ]);
                }
            } elseif (strlen($NPWP16) == 16) {
                return $NPWP16;
            } else {
                return response()->json([
                    "status" => "0",
                    "message" => "Invalid NPWP_CONSIGNEE length"
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => "0",
                "message" => "Error: " . $e->getMessage()
            ]);
        }
    }
    
}
