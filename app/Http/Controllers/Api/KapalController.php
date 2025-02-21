<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**
 * @OA\Info(
 *     title="NBS API",
 *     version="1.0.0",
 *     description="This is the API documentation for NBS version 1.",
 *     @OA\Contact(
 *         email="support@mti.com"
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="ApiKeyAuth",
 *     type="apiKey",
 *     in="header",  
 *     name="API_KEY" 
 * )
 */
class KapalController extends Controller
{

    /**
     * @OA\Get(
     *     path="/voyages",
     *     summary="Retrieve a list of voyages",
     *     tags={"Voyages"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                *     @OA\Property(property="TML_CD", type="string", example="PNK"),
     *     @OA\Property(property="VESSEL", type="string", example="SPIL RENATA"),
     *     @OA\Property(property="VOYAGE_IN", type="string", example="28/2024"),
     *     @OA\Property(property="VOYAGE_OUT", type="string", example="28/2024"),
     *     @OA\Property(property="CALL_SIGN", type="string", example="YDDF2"),
     *     @OA\Property(property="OPERATOR_ID", type="string", example="SPL"),
     *     @OA\Property(property="OPERATOR_NAME", type="string", example="SALAM PACIFIC INDONESIA LINES"),
     *     @OA\Property(property="ETA", type="string", format="date-time", example="2024-10-15T07:00:00Z"),
     *     @OA\Property(property="ETB", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="ETD", type="string", format="date-time", example="2024-10-16T17:16:00Z"),
     *     @OA\Property(property="ATA", type="string", format="date-time", example="2024-10-15T17:00:00Z"),
     *     @OA\Property(property="ATB", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="ATD", type="string", format="date-time", example="2024-10-16T17:33:00Z"),
     *     @OA\Property(property="ID_POD", type="string", example="IDPNK"),
     *     @OA\Property(property="POD", type="string", example="PONTIANAK"),
     *     @OA\Property(property="ID_POL", type="string", example="IDPNK"),
     *     @OA\Property(property="POL", type="string", example="PONTIANAK"),
     *     @OA\Property(property="OPEN_STACK", type="string", format="date-time", example="2024-10-12T10:00:00Z"),
     *     @OA\Property(property="CLOSSING_TIME", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="CLOSSING_DOC", type="string", nullable=true, example=null),
     *     @OA\Property(property="BERTHING_TIME", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="START_WORK", type="string", format="date-time", example="2024-10-15T18:37:00Z"),
     *     @OA\Property(property="END_WORK", type="string", format="date-time", example="2024-10-16T17:03:00Z"),
     *     @OA\Property(property="DATE_SEND", type="string", format="date-time", example="2024-10-12T09:46:50Z"),
     *     @OA\Property(property="FLAG_SEND", type="string", example="1"),
     *     @OA\Property(property="ID_VSB_VOYAGE", type="string", example="202410120946504941"),
     *     @OA\Property(property="VESSEL_CODE", type="string", example="SPRE"),
     *     @OA\Property(property="VOYAGE", type="string", example="SPRE-0031"),
     *     @OA\Property(property="FIRST_ETD", type="string", nullable=true, example=null),
     *     @OA\Property(property="RBM_DLD", type="string", nullable=true, example=null),
     *     @OA\Property(property="UPD_USER", type="string", example="0304002"),
     *     @OA\Property(property="UPD_DATE", type="string", format="date-time", example="2024-10-17T07:01:28Z"),
     *     @OA\Property(property="DLT_FLG", type="string", nullable=true, example=null),
     *     @OA\Property(property="CONTAINER_LIMIT", type="string", example="402"),
     *     @OA\Property(property="BERTH", type="string", example="402"),
     *     @OA\Property(property="NO_PKK", type="string", example="08"),
     *     @OA\Property(property="ORIGIN_PORT", type="string", example="IDPNK"),
     *     @OA\Property(property="LAST_PORT", type="string", example="IDJK1"),
     *     @OA\Property(property="NEXT_PORT", type="string", example="IDJKT"),
     *     @OA\Property(property="ACTIVE", type="string", example="N"),
     *     @OA\Property(property="VESSEL_CLOSE", type="string", example="N"),
     *     @OA\Property(property="ACTWKSL_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKCL_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKS_DATE", type="string", format="date", example="20241015"),
     *     @OA\Property(property="ACTWKC_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKSL_TIME", type="string", example="011600"),
     *     @OA\Property(property="ACTWKCL_TIME", type="string", example="170300"),
     *     @OA\Property(property="ACTWKS_TIME", type="string", example="183700"),
     *     @OA\Property(property="ACTWKC_TIME", type="string", example="150000"),
     *     @OA\Property(property="NOBC11", type="string", nullable=true, example=null),
     *     @OA\Property(property="TGBC11", type="string", nullable=true, example=null),
     *     @OA\Property(property="EARLY_STACK", type="string", example="20241012100000"),
     *     @OA\Property(property="SERVICE_LANE_IN", type="string", example="SPLJKT"),
     *     @OA\Property(property="SERVICE_LANE_OUT", type="string", example="SPLJKT"),
     *     @OA\Property(property="VESEL_TYPE_CD", type="string", example="UCC"),
     *     @OA\Property(property="SERVICE_TYPE", type="string", example="DOMESTIC"),
     *     @OA\Property(property="CANCEL_FLAG", type="string", nullable=true, example=null)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving voyages",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error retrieving voyages"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     ),
     *     security={{"ApiKeyAuth":{}}} 
     * )
     */
    public function index()
    {
        try {
            // Retrieve 10 records from the table
            $voyages = DB::connection('opus_repo')->table('M_VSB_VOYAGE_PALAPA')->limit(10)->get();

            return response()->json($voyages, 200);
        } catch (\Exception $e) {
            // Log the error for debugging (optional)
            Log::error('Error retrieving voyages: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['message' => 'Error retrieving voyages', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/voyages/{id}",
     *     summary="Retrieve a specific voyage by ID",
     *     tags={"Voyages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the voyage",
     *         required=true,
     *         @OA\Schema(type="string", example="202410120946504941")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *     @OA\Property(property="TML_CD", type="string", example="PNK"),
     *     @OA\Property(property="VESSEL", type="string", example="SPIL RENATA"),
     *     @OA\Property(property="VOYAGE_IN", type="string", example="28/2024"),
     *     @OA\Property(property="VOYAGE_OUT", type="string", example="28/2024"),
     *     @OA\Property(property="CALL_SIGN", type="string", example="YDDF2"),
     *     @OA\Property(property="OPERATOR_ID", type="string", example="SPL"),
     *     @OA\Property(property="OPERATOR_NAME", type="string", example="SALAM PACIFIC INDONESIA LINES"),
     *     @OA\Property(property="ETA", type="string", format="date-time", example="2024-10-15T07:00:00Z"),
     *     @OA\Property(property="ETB", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="ETD", type="string", format="date-time", example="2024-10-16T17:16:00Z"),
     *     @OA\Property(property="ATA", type="string", format="date-time", example="2024-10-15T17:00:00Z"),
     *     @OA\Property(property="ATB", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="ATD", type="string", format="date-time", example="2024-10-16T17:33:00Z"),
     *     @OA\Property(property="ID_POD", type="string", example="IDPNK"),
     *     @OA\Property(property="POD", type="string", example="PONTIANAK"),
     *     @OA\Property(property="ID_POL", type="string", example="IDPNK"),
     *     @OA\Property(property="POL", type="string", example="PONTIANAK"),
     *     @OA\Property(property="OPEN_STACK", type="string", format="date-time", example="2024-10-12T10:00:00Z"),
     *     @OA\Property(property="CLOSSING_TIME", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="CLOSSING_DOC", type="string", nullable=true, example=null),
     *     @OA\Property(property="BERTHING_TIME", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="START_WORK", type="string", format="date-time", example="2024-10-15T18:37:00Z"),
     *     @OA\Property(property="END_WORK", type="string", format="date-time", example="2024-10-16T17:03:00Z"),
     *     @OA\Property(property="DATE_SEND", type="string", format="date-time", example="2024-10-12T09:46:50Z"),
     *     @OA\Property(property="FLAG_SEND", type="string", example="1"),
     *     @OA\Property(property="ID_VSB_VOYAGE", type="string", example="202410120946504941"),
     *     @OA\Property(property="VESSEL_CODE", type="string", example="SPRE"),
     *     @OA\Property(property="VOYAGE", type="string", example="SPRE-0031"),
     *     @OA\Property(property="FIRST_ETD", type="string", nullable=true, example=null),
     *     @OA\Property(property="RBM_DLD", type="string", nullable=true, example=null),
     *     @OA\Property(property="UPD_USER", type="string", example="0304002"),
     *     @OA\Property(property="UPD_DATE", type="string", format="date-time", example="2024-10-17T07:01:28Z"),
     *     @OA\Property(property="DLT_FLG", type="string", nullable=true, example=null),
     *     @OA\Property(property="CONTAINER_LIMIT", type="string", example="402"),
     *     @OA\Property(property="BERTH", type="string", example="402"),
     *     @OA\Property(property="NO_PKK", type="string", example="08"),
     *     @OA\Property(property="ORIGIN_PORT", type="string", example="IDPNK"),
     *     @OA\Property(property="LAST_PORT", type="string", example="IDJK1"),
     *     @OA\Property(property="NEXT_PORT", type="string", example="IDJKT"),
     *     @OA\Property(property="ACTIVE", type="string", example="N"),
     *     @OA\Property(property="VESSEL_CLOSE", type="string", example="N"),
     *     @OA\Property(property="ACTWKSL_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKCL_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKS_DATE", type="string", format="date", example="20241015"),
     *     @OA\Property(property="ACTWKC_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKSL_TIME", type="string", example="011600"),
     *     @OA\Property(property="ACTWKCL_TIME", type="string", example="170300"),
     *     @OA\Property(property="ACTWKS_TIME", type="string", example="183700"),
     *     @OA\Property(property="ACTWKC_TIME", type="string", example="150000"),
     *     @OA\Property(property="NOBC11", type="string", nullable=true, example=null),
     *     @OA\Property(property="TGBC11", type="string", nullable=true, example=null),
     *     @OA\Property(property="EARLY_STACK", type="string", example="20241012100000"),
     *     @OA\Property(property="SERVICE_LANE_IN", type="string", example="SPLJKT"),
     *     @OA\Property(property="SERVICE_LANE_OUT", type="string", example="SPLJKT"),
     *     @OA\Property(property="VESEL_TYPE_CD", type="string", example="UCC"),
     *     @OA\Property(property="SERVICE_TYPE", type="string", example="DOMESTIC"),
     *     @OA\Property(property="CANCEL_FLAG", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Voyage not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Voyage not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error retrieving the voyage",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error retrieving the voyage"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     ),
     *     security={{"ApiKeyAuth":{}}} 
     * )
     */
    public function show($id)
    {
        try {
            // Retrieve a specific record by its ID
            $voyage = DB::connection('opus_repo')->table('M_VSB_VOYAGE_PALAPA')->where('ID_VSB_VOYAGE', $id)->first();

            if ($voyage) {
                return response()->json($voyage, 200);
            } else {
                return response()->json(['message' => 'Voyage not found'], 404);
            }
        } catch (\Exception $e) {
            // Log the error for debugging (optional)
            Log::error('Error retrieving voyage: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['message' => 'Error retrieving voyage', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/voyages",
     *     summary="Create a new voyage",
     *     tags={"Voyages"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             *     @OA\Property(property="TML_CD", type="string", example="PNK"),
     *     @OA\Property(property="VESSEL", type="string", example="SPIL RENATA"),
     *     @OA\Property(property="VOYAGE_IN", type="string", example="28/2024"),
     *     @OA\Property(property="VOYAGE_OUT", type="string", example="28/2024"),
     *     @OA\Property(property="CALL_SIGN", type="string", example="YDDF2"),
     *     @OA\Property(property="OPERATOR_ID", type="string", example="SPL"),
     *     @OA\Property(property="OPERATOR_NAME", type="string", example="SALAM PACIFIC INDONESIA LINES"),
     *     @OA\Property(property="ETA", type="string", format="date-time", example="2024-10-15T07:00:00Z"),
     *     @OA\Property(property="ETB", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="ETD", type="string", format="date-time", example="2024-10-16T17:16:00Z"),
     *     @OA\Property(property="ATA", type="string", format="date-time", example="2024-10-15T17:00:00Z"),
     *     @OA\Property(property="ATB", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="ATD", type="string", format="date-time", example="2024-10-16T17:33:00Z"),
     *     @OA\Property(property="ID_POD", type="string", example="IDPNK"),
     *     @OA\Property(property="POD", type="string", example="PONTIANAK"),
     *     @OA\Property(property="ID_POL", type="string", example="IDPNK"),
     *     @OA\Property(property="POL", type="string", example="PONTIANAK"),
     *     @OA\Property(property="OPEN_STACK", type="string", format="date-time", example="2024-10-12T10:00:00Z"),
     *     @OA\Property(property="CLOSSING_TIME", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="CLOSSING_DOC", type="string", nullable=true, example=null),
     *     @OA\Property(property="BERTHING_TIME", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="START_WORK", type="string", format="date-time", example="2024-10-15T18:37:00Z"),
     *     @OA\Property(property="END_WORK", type="string", format="date-time", example="2024-10-16T17:03:00Z"),
     *     @OA\Property(property="DATE_SEND", type="string", format="date-time", example="2024-10-12T09:46:50Z"),
     *     @OA\Property(property="FLAG_SEND", type="string", example="1"),
     *     @OA\Property(property="ID_VSB_VOYAGE", type="string", example="202410120946504941"),
     *     @OA\Property(property="VESSEL_CODE", type="string", example="SPRE"),
     *     @OA\Property(property="VOYAGE", type="string", example="SPRE-0031"),
     *     @OA\Property(property="FIRST_ETD", type="string", nullable=true, example=null),
     *     @OA\Property(property="RBM_DLD", type="string", nullable=true, example=null),
     *     @OA\Property(property="UPD_USER", type="string", example="0304002"),
     *     @OA\Property(property="UPD_DATE", type="string", format="date-time", example="2024-10-17T07:01:28Z"),
     *     @OA\Property(property="DLT_FLG", type="string", nullable=true, example=null),
     *     @OA\Property(property="CONTAINER_LIMIT", type="string", example="402"),
     *     @OA\Property(property="BERTH", type="string", example="402"),
     *     @OA\Property(property="NO_PKK", type="string", example="08"),
     *     @OA\Property(property="ORIGIN_PORT", type="string", example="IDPNK"),
     *     @OA\Property(property="LAST_PORT", type="string", example="IDJK1"),
     *     @OA\Property(property="NEXT_PORT", type="string", example="IDJKT"),
     *     @OA\Property(property="ACTIVE", type="string", example="N"),
     *     @OA\Property(property="VESSEL_CLOSE", type="string", example="N"),
     *     @OA\Property(property="ACTWKSL_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKCL_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKS_DATE", type="string", format="date", example="20241015"),
     *     @OA\Property(property="ACTWKC_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKSL_TIME", type="string", example="011600"),
     *     @OA\Property(property="ACTWKCL_TIME", type="string", example="170300"),
     *     @OA\Property(property="ACTWKS_TIME", type="string", example="183700"),
     *     @OA\Property(property="ACTWKC_TIME", type="string", example="150000"),
     *     @OA\Property(property="NOBC11", type="string", nullable=true, example=null),
     *     @OA\Property(property="TGBC11", type="string", nullable=true, example=null),
     *     @OA\Property(property="EARLY_STACK", type="string", example="20241012100000"),
     *     @OA\Property(property="SERVICE_LANE_IN", type="string", example="SPLJKT"),
     *     @OA\Property(property="SERVICE_LANE_OUT", type="string", example="SPLJKT"),
     *     @OA\Property(property="VESEL_TYPE_CD", type="string", example="UCC"),
     *     @OA\Property(property="SERVICE_TYPE", type="string", example="DOMESTIC"),
     *     @OA\Property(property="CANCEL_FLAG", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Voyage created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Voyage created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating voyage",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error creating voyage"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     ),
     *     security={{"ApiKeyAuth":{}}} 
     * )
     */
    public function store(Request $request)
    {
        // Validation rules for all fields
        $validatedData = $request->validate([
            'TML_CD' => 'required|string',
            'VESSEL' => 'required|string',
            'VOYAGE_IN' => 'required|string',
            'VOYAGE_OUT' => 'required|string',
            'CALL_SIGN' => 'required|string',
            'OPERATOR_ID' => 'required|string',
            'OPERATOR_NAME' => 'required|string',
            'ETA' => 'required|date_format:YmdHis',
            'ETB' => 'required|date_format:YmdHis',
            'ETD' => 'required|date_format:YmdHis',
            'ATA' => 'required|date_format:YmdHis',
            'ATB' => 'required|date_format:YmdHis',
            'ATD' => 'required|date_format:YmdHis',
            'ID_POD' => 'required|string',
            'POD' => 'required|string',
            'ID_POL' => 'required|string',
            'POL' => 'required|string',
            'OPEN_STACK' => 'nullable|date_format:YmdHis',
            'CLOSSING_TIME' => 'nullable|date_format:YmdHis',
            'CLOSSING_DOC' => 'nullable|date_format:YmdHis',
            'BERTHING_TIME' => 'nullable|date_format:YmdHis',
            'START_WORK' => 'nullable|date_format:YmdHis',
            'END_WORK' => 'nullable|date_format:YmdHis',
            'DATE_SEND' => 'required|date_format:YmdHis',
            'FLAG_SEND' => 'required|boolean',
            'ID_VSB_VOYAGE' => 'required|string|unique:M_VSB_VOYAGE_PALAPA',
            'VESSEL_CODE' => 'required|string',
            'VOYAGE' => 'required|string',
            'FIRST_ETD' => 'nullable|date_format:YmdHis',
            'RBM_DLD' => 'nullable|boolean',
            'UPD_USER' => 'required|string',
            'UPD_DATE' => 'required|date_format:Y-m-d H:i:s.u',
            'DLT_FLG' => 'nullable|boolean',
            'CONTAINER_LIMIT' => 'nullable|integer',
            'BERTH' => 'nullable|string',
            'NO_PKK' => 'nullable|string',
            'ORIGIN_PORT' => 'required|string',
            'LAST_PORT' => 'required|string',
            'NEXT_PORT' => 'nullable|string',
            'ACTIVE' => 'required|string',
            'VESSEL_CLOSE' => 'nullable|string',
            'ACTWKSL_DATE' => 'nullable|date_format:Ymd',
            'ACTWKCL_DATE' => 'nullable|date_format:Ymd',
            'ACTWKS_DATE' => 'nullable|date_format:Ymd',
            'ACTWKC_DATE' => 'nullable|date_format:Ymd',
            'ACTWKSL_TIME' => 'nullable|string',
            'ACTWKCL_TIME' => 'nullable|string',
            'ACTWKS_TIME' => 'nullable|string',
            'ACTWKC_TIME' => 'nullable|string',
            'NOBC11' => 'nullable|string',
            'TGBC11' => 'nullable|string',
            'EARLY_STACK' => 'nullable|date_format:YmdHis',
            'SERVICE_LANE_IN' => 'required|string',
            'SERVICE_LANE_OUT' => 'required|string',
            'VESEL_TYPE_CD' => 'required|string',
            'SERVICE_TYPE' => 'required|string',
            'CANCEL_FLAG' => 'nullable|boolean'
        ]);

        // Use try-catch to handle exceptions
        try {
            // Insert into database
            $voyage = DB::connection('opus_repo')->table('M_VSB_VOYAGE_PALAPA')->insert($validatedData);

            return response()->json(['message' => 'Voyage created successfully'], 201);
        } catch (\Exception $e) {
            // Log the error for debugging (optional)
            Log::error('Error creating voyage: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['message' => 'Error creating voyage', 'error' => $e->getMessage()], 500);
        }
    }



    /**
     * @OA\Put(
     *     path="/voyages/{id}",
     *     summary="Update an existing voyage",
     *     tags={"Voyages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the voyage to be updated",
     *         required=true,
     *         @OA\Schema(type="string", example="202410120946504941")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            *     @OA\Property(property="TML_CD", type="string", example="PNK"),
     *     @OA\Property(property="VESSEL", type="string", example="SPIL RENATA"),
     *     @OA\Property(property="VOYAGE_IN", type="string", example="28/2024"),
     *     @OA\Property(property="VOYAGE_OUT", type="string", example="28/2024"),
     *     @OA\Property(property="CALL_SIGN", type="string", example="YDDF2"),
     *     @OA\Property(property="OPERATOR_ID", type="string", example="SPL"),
     *     @OA\Property(property="OPERATOR_NAME", type="string", example="SALAM PACIFIC INDONESIA LINES"),
     *     @OA\Property(property="ETA", type="string", format="date-time", example="2024-10-15T07:00:00Z"),
     *     @OA\Property(property="ETB", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="ETD", type="string", format="date-time", example="2024-10-16T17:16:00Z"),
     *     @OA\Property(property="ATA", type="string", format="date-time", example="2024-10-15T17:00:00Z"),
     *     @OA\Property(property="ATB", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="ATD", type="string", format="date-time", example="2024-10-16T17:33:00Z"),
     *     @OA\Property(property="ID_POD", type="string", example="IDPNK"),
     *     @OA\Property(property="POD", type="string", example="PONTIANAK"),
     *     @OA\Property(property="ID_POL", type="string", example="IDPNK"),
     *     @OA\Property(property="POL", type="string", example="PONTIANAK"),
     *     @OA\Property(property="OPEN_STACK", type="string", format="date-time", example="2024-10-12T10:00:00Z"),
     *     @OA\Property(property="CLOSSING_TIME", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="CLOSSING_DOC", type="string", nullable=true, example=null),
     *     @OA\Property(property="BERTHING_TIME", type="string", format="date-time", example="2024-10-15T18:00:00Z"),
     *     @OA\Property(property="START_WORK", type="string", format="date-time", example="2024-10-15T18:37:00Z"),
     *     @OA\Property(property="END_WORK", type="string", format="date-time", example="2024-10-16T17:03:00Z"),
     *     @OA\Property(property="DATE_SEND", type="string", format="date-time", example="2024-10-12T09:46:50Z"),
     *     @OA\Property(property="FLAG_SEND", type="string", example="1"),
     *     @OA\Property(property="ID_VSB_VOYAGE", type="string", example="202410120946504941"),
     *     @OA\Property(property="VESSEL_CODE", type="string", example="SPRE"),
     *     @OA\Property(property="VOYAGE", type="string", example="SPRE-0031"),
     *     @OA\Property(property="FIRST_ETD", type="string", nullable=true, example=null),
     *     @OA\Property(property="RBM_DLD", type="string", nullable=true, example=null),
     *     @OA\Property(property="UPD_USER", type="string", example="0304002"),
     *     @OA\Property(property="UPD_DATE", type="string", format="date-time", example="2024-10-17T07:01:28Z"),
     *     @OA\Property(property="DLT_FLG", type="string", nullable=true, example=null),
     *     @OA\Property(property="CONTAINER_LIMIT", type="string", example="402"),
     *     @OA\Property(property="BERTH", type="string", example="402"),
     *     @OA\Property(property="NO_PKK", type="string", example="08"),
     *     @OA\Property(property="ORIGIN_PORT", type="string", example="IDPNK"),
     *     @OA\Property(property="LAST_PORT", type="string", example="IDJK1"),
     *     @OA\Property(property="NEXT_PORT", type="string", example="IDJKT"),
     *     @OA\Property(property="ACTIVE", type="string", example="N"),
     *     @OA\Property(property="VESSEL_CLOSE", type="string", example="N"),
     *     @OA\Property(property="ACTWKSL_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKCL_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKS_DATE", type="string", format="date", example="20241015"),
     *     @OA\Property(property="ACTWKC_DATE", type="string", format="date", example="20241016"),
     *     @OA\Property(property="ACTWKSL_TIME", type="string", example="011600"),
     *     @OA\Property(property="ACTWKCL_TIME", type="string", example="170300"),
     *     @OA\Property(property="ACTWKS_TIME", type="string", example="183700"),
     *     @OA\Property(property="ACTWKC_TIME", type="string", example="150000"),
     *     @OA\Property(property="NOBC11", type="string", nullable=true, example=null),
     *     @OA\Property(property="TGBC11", type="string", nullable=true, example=null),
     *     @OA\Property(property="EARLY_STACK", type="string", example="20241012100000"),
     *     @OA\Property(property="SERVICE_LANE_IN", type="string", example="SPLJKT"),
     *     @OA\Property(property="SERVICE_LANE_OUT", type="string", example="SPLJKT"),
     *     @OA\Property(property="VESEL_TYPE_CD", type="string", example="UCC"),
     *     @OA\Property(property="SERVICE_TYPE", type="string", example="DOMESTIC"),
     *     @OA\Property(property="CANCEL_FLAG", type="string", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Voyage updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Voyage updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Voyage not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Voyage not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating voyage",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error updating voyage"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     ),
     *     security={{"ApiKeyAuth":{}}} 
     * )
     */

    public function update(Request $request, $id)
    {
        // Validation rules for all fields
        $validatedData = $request->validate([
            'TML_CD' => 'sometimes|required|string',
            'VESSEL' => 'sometimes|required|string',
            'VOYAGE_IN' => 'sometimes|required|string',
            'VOYAGE_OUT' => 'sometimes|required|string',
            'CALL_SIGN' => 'sometimes|required|string',
            'OPERATOR_ID' => 'sometimes|required|string',
            'OPERATOR_NAME' => 'sometimes|required|string',
            'ETA' => 'sometimes|required|date_format:YmdHis',
            'ETB' => 'sometimes|required|date_format:YmdHis',
            'ETD' => 'sometimes|required|date_format:YmdHis',
            'ATA' => 'sometimes|required|date_format:YmdHis',
            'ATB' => 'sometimes|required|date_format:YmdHis',
            'ATD' => 'sometimes|required|date_format:YmdHis',
            'ID_POD' => 'sometimes|required|string',
            'POD' => 'sometimes|required|string',
            'ID_POL' => 'sometimes|required|string',
            'POL' => 'sometimes|required|string',
            'OPEN_STACK' => 'nullable|date_format:YmdHis',
            'CLOSSING_TIME' => 'nullable|date_format:YmdHis',
            'CLOSSING_DOC' => 'nullable|date_format:YmdHis',
            'BERTHING_TIME' => 'nullable|date_format:YmdHis',
            'START_WORK' => 'nullable|date_format:YmdHis',
            'END_WORK' => 'nullable|date_format:YmdHis',
            'DATE_SEND' => 'sometimes|required|date_format:YmdHis',
            'FLAG_SEND' => 'sometimes|required|boolean',
            'VESSEL_CODE' => 'sometimes|required|string',
            'VOYAGE' => 'sometimes|required|string',
            'FIRST_ETD' => 'nullable|date_format:YmdHis',
            'RBM_DLD' => 'nullable|boolean',
            'UPD_USER' => 'sometimes|required|string',
            'UPD_DATE' => 'sometimes|required|date_format:Y-m-d H:i:s.u',
            'DLT_FLG' => 'nullable|boolean',
            'CONTAINER_LIMIT' => 'nullable|integer',
            'BERTH' => 'nullable|string',
            'NO_PKK' => 'nullable|string',
            'ORIGIN_PORT' => 'sometimes|required|string',
            'LAST_PORT' => 'sometimes|required|string',
            'NEXT_PORT' => 'nullable|string',
            'ACTIVE' => 'sometimes|required|string',
            'VESSEL_CLOSE' => 'nullable|string',
            'ACTWKSL_DATE' => 'nullable|date_format:Ymd',
            'ACTWKCL_DATE' => 'nullable|date_format:Ymd',
            'ACTWKS_DATE' => 'nullable|date_format:Ymd',
            'ACTWKC_DATE' => 'nullable|date_format:Ymd',
            'ACTWKSL_TIME' => 'nullable|string',
            'ACTWKCL_TIME' => 'nullable|string',
            'ACTWKS_TIME' => 'nullable|string',
            'ACTWKC_TIME' => 'nullable|string',
            'NOBC11' => 'nullable|string',
            'TGBC11' => 'nullable|string',
            'EARLY_STACK' => 'nullable|date_format:YmdHis',
            'SERVICE_LANE_IN' => 'sometimes|required|string',
            'SERVICE_LANE_OUT' => 'sometimes|required|string',
            'VESEL_TYPE_CD' => 'sometimes|required|string',
            'SERVICE_TYPE' => 'sometimes|required|string',
            'CANCEL_FLAG' => 'nullable|boolean'
        ]);

        // Use try-catch to handle exceptions
        try {
            // Update the record
            $voyage = DB::connection('opus_repo')->table('M_VSB_VOYAGE_PALAPA')->where('ID_VSB_VOYAGE', $id)->update($validatedData);

            if ($voyage) {
                return response()->json(['message' => 'Voyage updated successfully']);
            } else {
                return response()->json(['message' => 'Error updating voyage or voyage not found'], 404);
            }
        } catch (\Exception $e) {
            // Log the error for debugging (optional)
            Log::error('Error updating voyage: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['message' => 'Error updating voyage', 'error' => $e->getMessage()], 500);
        }
    }



    /**
     * @OA\Delete(
     *     path="/voyages/{id}",
     *     summary="Delete an existing voyage",
     *     tags={"Voyages"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the voyage to be deleted",
     *         required=true,
     *         @OA\Schema(type="string", example="202410120946504941")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Voyage deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Voyage deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Voyage not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Voyage not found or already deleted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error deleting voyage",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Error deleting voyage"),
     *             @OA\Property(property="error", type="string", example="Exception message")
     *         )
     *     ),
     *     security={{"ApiKeyAuth":{}}} 
     * )
     */
    public function destroy($id)
    {
        try {
            // Attempt to delete the record
            $voyage = DB::connection('opus_repo')->table('M_VSB_VOYAGE_PALAPA')->where('ID_VSB_VOYAGE', $id)->delete();

            // Check if the delete operation was successful
            if ($voyage) {
                return response()->json(['message' => 'Voyage deleted successfully'], 200);
            } else {
                return response()->json(['message' => 'Voyage not found or already deleted'], 404);
            }
        } catch (\Exception $e) {
            // Log the error for debugging (optional)
            Log::error('Error deleting voyage: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['message' => 'Error deleting voyage', 'error' => $e->getMessage()], 500);
        }
    }
}
