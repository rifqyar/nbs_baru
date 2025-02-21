<?php

namespace App\Http\Controllers\Api\Soap;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use nguyenanhung\MyNuSOAP\nusoap_server;
use Illuminate\Support\Facades\DB;



class SoapServerController extends Controller
{

    public function __invoke(Request $request)
    {
        $server = new nusoap_server();
        $server->configureWSDL('ApiNbs', 'urn:ApiNbs');

        $server->register(
            'getCopyYard',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getCopyYard',
            'urn:getCopyYard#getCopyYard',
            'rpc',
            'encoded',
            'Get Data Copy Yard'
        );

        $server->register(
            'getDelivery',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getDelivery',
            'urn:getDelivery#getDelivery',
            'rpc',
            'encoded',
            'Get Data Req Delivery'
        );

        $server->register(
            'getDeliveryNoreq',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getDeliveryNoreq',
            'urn:getDeliveryNoreq#getDeliveryNoreq',
            'rpc',
            'encoded',
            'Get Data Req Delivery by No Request'
        );

        $server->register(
            'getDeliveryExRepo',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getDeliveryExRepo',
            'urn:getDeliveryExRepo#getDeliveryExRepo',
            'rpc',
            'encoded',
            'Get Data Req Delivery karena alih kapal'
        );

        $server->register(
            'getReceiving',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getReceiving',
            'urn:getReceiving#getReceiving',
            'rpc',
            'encoded',
            'Get Data Req Receiving'
        );

        $server->register(
            'getMtiSP',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getMtiSP',
            'urn:getMtiSP#getMtiSP',
            'rpc',
            'encoded',
            'Get Data Req MTI SP (ITPK Nota Header & Detail)'
        );

        $server->register(
            'getReceivingNoreq',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getReceivingNoreq',
            'urn:getReceivingNoreq#getReceivingNoreq',
            'rpc',
            'encoded',
            'Get Data Req Receiving by No Request'
        );

        $server->register(
            'getStuffing',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getStuffing',
            'urn:getStuffing#getStuffing',
            'rpc',
            'encoded',
            'Get Data Req Stuffing'
        );

        $server->register(
            'getStuffingAlihKapal',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getStuffingAlihKapal',
            'urn:getStuffingAlihKapal#getStuffingAlihKapal',
            'rpc',
            'encoded',
            'Get Data Req Stuffing karena alih kapal'
        );

        $server->register(
            'getStuffingNoreq',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getStuffingNoreq',
            'urn:getStuffingNoreq#getStuffingNoreq',
            'rpc',
            'encoded',
            'Get Data Req Stuffing By Noreq'
        );

        $server->register(
            'getStripping',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getStripping',
            'urn:getStripping#getStripping',
            'rpc',
            'encoded',
            'Get Data Req Stripping'
        );

        $server->register(
            'getStrippingNoreq',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getStrippingNoreq',
            'urn:getStrippingNoreq#getStrippingNoreq',
            'rpc',
            'encoded',
            'Get Data Req Stripping By Noreq'
        );

        $server->register(
            'getReceivingFromTPK',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getReceivingFromTPK',
            'urn:getReceivingFromTPK#getReceivingFromTPK',
            'rpc',
            'encoded',
            'Get Data Req getReceivingFromTPK'
        );

        $server->register(
            'getGateInFromTPK',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getGateInFromTPK',
            'urn:getGateInFromTPK#getGateInFromTPK',
            'rpc',
            'encoded',
            'Get Data Req getGateInFromTPK'
        );

        $server->register(
            'getGateOutToTPK',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getGateOutToTPK',
            'urn:getGateOutToTPK#getGateOutToTPK',
            'rpc',
            'encoded',
            'Get Data Req getGateOutToTPK'
        );

        $server->register(
            'getBatalMuat',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getBatalMuat',
            'urn:getBatalMuat#getBatalMuat',
            'rpc',
            'encoded',
            'Get Data Batal Muat dari billing'
        );

        $server->register(
            'getBatalStuffStrip',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getBatalStuffStrip',
            'urn:getBatalStuffStrip#getBatalStuffStrip',
            'rpc',
            'encoded',
            'Get Data Batal SPPS dan STUFFING dari billing'
        );

        $server->register(
            'getBatalSP2',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getBatalSP2',
            'urn:getBatalSP2#getBatalSP2',
            'rpc',
            'encoded',
            'Get Data Batal SP2 dari billing'
        );

        $server->register(
            'getBatalReceivingMTY',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getBatalReceivingMTY',
            'urn:getBatalReceivingMTY#getBatalReceivingMTY',
            'rpc',
            'encoded',
            'Get Data Batal Receiving MTY dari billing'
        );

        $server->register(
            'getBatalOperation',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getBatalOperation',
            'urn:getBatalOperation#getBatalOperation',
            'rpc',
            'encoded',
            'Get Data Batal Operation dari billing'
        );

        $server->register(
            'getListContGateOut',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getListContGateOut',
            'urn:getListContGateOut#getListContGateOut',
            'rpc',
            'encoded',
            'Get Data Kontainer gate out to TPK'
        );

        $server->register(
            'getBatalNota',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getBatalNota',
            'urn:getBatalNota#getBatalNota',
            'rpc',
            'encoded',
            'Get Data Batal Nota dari billing'
        );

        $server->register(
            'getAlihKapalStuffing',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:getAlihKapalStuffing',
            'urn:getAlihKapalStuffing#getAlihKapalStuffing',
            'rpc',
            'encoded',
            'Get Alih Kapal'
        );

        //list service insert data ke billing

        $server->register(
            'setGateIn',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:setGateIn',
            'urn:setGateIn#setGateIn',
            'rpc',
            'encoded',
            'insert data gate in ke db billing'
        );

        $server->register(
            'setGateOut',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:setGateOut',
            'urn:setGateOut#setGateOut',
            'rpc',
            'encoded',
            'insert data gate out ke db billing'
        );

        $server->register(
            'setPlacement',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:setPlacement',
            'urn:setPlacement#setPlacement',
            'rpc',
            'encoded',
            'insert data Placement ke db billing'
        );

        $server->register(
            'setPlacementAll',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:setPlacementAll',
            'urn:setPlacementAll#setPlacementAll',
            'rpc',
            'encoded',
            'insert data Shifting ke db billing'
        );

        $server->register(
            'setRealStuffing',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:setRealStuffing',
            'urn:setRealStuffing#setRealStuffing',
            'rpc',
            'encoded',
            'insert data Realisasi Stuffing ke db billing'
        );

        $server->register(
            'setRealStripping',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:setRealStripping',
            'urn:setRealStripping#setRealStripping',
            'rpc',
            'encoded',
            'insert data Realisasi Stripping ke db billing'
        );

        $server->register(
            'renameContainer',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:renameContainer',
            'urn:renameContainer#renameContainer',
            'rpc',
            'encoded',
            'Rename Container'
        );

        $server->register(
            'renameContainerTest',
            array(
                'string0' => 'xsd:string',
                'string1' => 'xsd:string',
                'string2' => 'xsd:string'
            ),
            array('return' => 'xsd:string'),
            'urn:renameContainerTest',
            'urn:renameContainerTest#renameContainerTest',
            'rpc',
            'encoded',
            'Rename Container test'
        );

        // Add other method registrations here...

        // Use Laravel's response to output the SOAP response
        $response = response($server->service($request->getContent()));

        // Set the appropriate headers for SOAP response
        $response->header('Content-Type', 'text/xml; charset=utf-8');

        return $response;
    }

    private function uppercaseKeysObjectToArray($object)
    {
        // Check if the object is not null and convert it to an array with uppercase keys
        if ($object) {
            return array_change_key_case((array)$object, CASE_UPPER);
        } else {
            return null; // Handle the case where the object is null
        }
    }

    private function convertKeysToUppercase(array $array)
    {
        return array_map(function ($item) {
            $newItem = [];
            foreach ($item as $key => $value) {
                $newItem[strtoupper($key)] = $value;
            }
            return $newItem;
        }, $array);
    }


    function getCopyYard($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT * FROM (SELECT CONT_NO,CONT_TYPE,CONT_SIZE,CONT_STATUS,BLOCK,SLOT,\"ROW\",TIER,NO_REQ,TO_CHAR(STACK_DATE,'MM/DD/YYYY HH24:MI:SS') STACK_DATE, OWNER_CODE, OWNER_NAME, CONSIGNEE_NAME, CONSIGNEE_ADDRESS, NPWP FROM TP_CONTAINER WHERE FL_SEND = '0' ORDER BY STACK_DATE ASC) A WHERE rownum <=200 ";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<CONT_NO>' . $row['CONT_NO'] . '</CONT_NO>';
                    $returnXML .= '<CONT_TYPE>' . $row['CONT_TYPE'] . '</CONT_TYPE>';
                    $returnXML .= '<CONT_SIZE>' . $row['CONT_SIZE'] . '</CONT_SIZE>';
                    $returnXML .= '<CONT_STATUS>' . $row['CONT_STATUS'] . '</CONT_STATUS>';
                    $returnXML .= '<BLOCK>' . $row['BLOCK'] . '</BLOCK>';
                    $returnXML .= '<SLOT>' . $row['SLOT'] . '</SLOT>';
                    $returnXML .= '<ROW>' . $row['ROW'] . '</ROW>';
                    $returnXML .= '<TIER>' . $row['TIER'] . '</TIER>';
                    $returnXML .= '<NO_REQ>' . $row['NO_REQ'] . '</NO_REQ>';
                    $returnXML .= '<STACK_DATE>' . $row['STACK_DATE'] . '</STACK_DATE>';

                    $returnXML .= '<OWNER_CODE>' . $row['OWNER_CODE'] . '</OWNER_CODE>';
                    $returnXML .= '<OWNER_NAME>' . $row['OWNER_NAME'] . '</OWNER_NAME>';
                    $returnXML .= '<CONSIGNEE_NAME>' . $row['CONSIGNEE_NAME'] . '</CONSIGNEE_NAME>';
                    $returnXML .= '<CONSIGNEE_ADDRESS>' . $row['CONSIGNEE_ADDRESS'] . '</CONSIGNEE_ADDRESS>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '</data>';

                    $query_update    = "UPDATE TP_CONTAINER SET FL_SEND = '1' WHERE CONT_NO = '" . $row['CONT_NO'] . "'  ";
                    DB::connection('uster')->statement($query_update);
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getDelivery($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, B.EMKL NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.DELIVERY_KE
				FROM REQUEST_DELIVERY A
				INNER JOIN NOTA_DELIVERY B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') > '" . $xml . "' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_DELIVERY_DATE>' . $row['TGL_REQUEST'] . '</REQ_DELIVERY_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<DELIVERY_KE>' . $row['DELIVERY_KE'] . '</DELIVERY_KE>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_, TO_CHAR(A.TGL_DELIVERY,'MM/DD/YYYY HH24:MI:SS') TGL_DELIVERY, A.NO_SEAL  FROM CONTAINER_DELIVERY A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_CONT_STATUS>' . $dtl['STATUS'] . '</REQ_DTL_CONT_STATUS>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['KOMODITI']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_VIA>' . $dtl['VIA'] . '</REQ_DTL_VIA>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_DEL_DATE>' . $dtl['TGL_DELIVERY'] . '</REQ_DTL_DEL_DATE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_NO_SEAL>' . $dtl['NO_SEAL'] . '</REQ_DTL_NO_SEAL>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getMtiSP($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT TRX_NUMBER, NOTAPREV, TRX_DATE, CURRENCY_CODE, CUSTOMER_NUMBER, JENIS_NOTA, JENIS_MODUL, NAMA_KAPAL, PENUMPUKAN_FROM, PENUMPUKAN_TO, NOMOR_BL_PEB, NO_DO, VOY, 
            TANGGAL_TIBA, BONGKAR_MUAT, ORG_ID, NO_REQUEST, STATUS, TGL_SIMPAN, TGL_PELUNASAN, USERID_SIMPAN, USERID_LUNAS, RECEIPT_METHOD, RECEIPT_ACCOUNT, 
            RECEIPT_NAME, NO_FAKTUR_PAJAK, ADMINISTRASI, TOTAL, PPN, KREDIT, STATUS_NOTA, KD_PELUNASAN, KETERANGAN, NO_BA, STATUS_AR, STATUS_ARMSG, STATUS_RECEIPT, 
            STATUS_RECEIPTMSG, BANK_ID, ARPROCESS_DATE, RECEIPTPROCESS_DATE, CASE 
            WHEN BANK_ID = 14004 THEN 'BNI'
            WHEN BANK_ID = 14008 THEN 'MDR'
            END AS PAYMENT_CHANNEL, BRANCH_ACCOUNT, NO_NOTA_MTI, NO_FAKTUR_MTI, SP_MTI 
                FROM ITPK_NOTA_HEADER WHERE SP_MTI IS NOT NULL AND FL_MTI_SEND IS NULL AND ROWNUM <= 1  ORDER BY trx_date DESC";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<TRX_NUMBER>' . $row['TRX_NUMBER'] . '</TRX_NUMBER>';
                    $returnXML .= '<NOTAPREV>' . $row['NOTAPREV'] . '</NOTAPREV>';
                    $returnXML .= '<TRX_DATE>' . $row['TRX_DATE'] . '</TRX_DATE>';
                    $returnXML .= '<CURRENCY_CODE>' . $row['CURRENCY_CODE'] . '</CURRENCY_CODE>';
                    $returnXML .= '<CUSTOMER_NUMBER>' . $row['CUSTOMER_NUMBER'] . '</CUSTOMER_NUMBER>';
                    $returnXML .= '<JENIS_NOTA>' . $row['JENIS_NOTA'] . '</JENIS_NOTA>';
                    $returnXML .= '<JENIS_MODUL>' . $row['JENIS_MODUL'] . '</JENIS_MODUL>';
                    $returnXML .= '<NAMA_KAPAL>' . $row['NAMA_KAPAL'] . '</NAMA_KAPAL>';
                    $returnXML .= '<PENUMPUKAN_FROM>' . $row['PENUMPUKAN_FROM'] . '</PENUMPUKAN_FROM>';
                    $returnXML .= '<PENUMPUKAN_TO>' . $row['PENUMPUKAN_TO'] . '</PENUMPUKAN_TO>';
                    $returnXML .= '<NOMOR_BL_PEB>' . $row['NOMOR_BL_PEB'] . '</NOMOR_BL_PEB>';
                    $returnXML .= '<NO_DO>' . $row['NO_DO'] . '</NO_DO>';
                    $returnXML .= '<VOY>' . $row['VOY'] . '</VOY>';
                    $returnXML .= '<TANGGAL_TIBA>' . $row['TANGGAL_TIBA'] . '</TANGGAL_TIBA>';
                    $returnXML .= '<BONGKAR_MUAT>' . $row['BONGKAR_MUAT'] . '</BONGKAR_MUAT>';
                    $returnXML .= '<ORG_ID>' . $row['ORG_ID'] . '</ORG_ID>';
                    $returnXML .= '<NO_REQUEST>' . $row['NO_REQUEST'] . '</NO_REQUEST>';
                    $returnXML .= '<STATUS>' . $row['STATUS'] . '</STATUS>';
                    $returnXML .= '<TGL_SIMPAN>' . $row['TGL_SIMPAN'] . '</TGL_SIMPAN>';
                    $returnXML .= '<TGL_PELUNASAN>' . $row['TGL_PELUNASAN'] . '</TGL_PELUNASAN>';
                    $returnXML .= '<USERID_SIMPAN>' . $row['USERID_SIMPAN'] . '</USERID_SIMPAN>';
                    $returnXML .= '<USERID_LUNAS>' . $row['USERID_LUNAS'] . '</USERID_LUNAS>';
                    $returnXML .= '<RECEIPT_METHOD>' . $row['RECEIPT_METHOD'] . '</RECEIPT_METHOD>';
                    $returnXML .= '<RECEIPT_ACCOUNT>' . $row['RECEIPT_ACCOUNT'] . '</RECEIPT_ACCOUNT>';
                    $returnXML .= '<RECEIPT_NAME>' . $row['RECEIPT_NAME'] . '</RECEIPT_NAME>';
                    $returnXML .= '<NO_FAKTUR_PAJAK>' . $row['NO_FAKTUR_PAJAK'] . '</NO_FAKTUR_PAJAK>';
                    $returnXML .= '<ADMINISTRASI>' . $row['ADMINISTRASI'] . '</ADMINISTRASI>';
                    $returnXML .= '<TOTAL>' . $row['TOTAL'] . '</TOTAL>';
                    $returnXML .= '<PPN>' . $row['PPN'] . '</PPN>';
                    $returnXML .= '<KREDIT>' . $row['KREDIT'] . '</KREDIT>';
                    $returnXML .= '<STATUS_NOTA>' . $row['STATUS_NOTA'] . '</STATUS_NOTA>';
                    $returnXML .= '<KD_PELUNASAN>' . $row['KD_PELUNASAN'] . '</KD_PELUNASAN>';
                    $returnXML .= '<KETERANGAN>' . $row['KETERANGAN'] . '</KETERANGAN>';
                    $returnXML .= '<NO_BA>' . $row['NO_BA'] . '</NO_BA>';
                    $returnXML .= '<STATUS_AR>' . $row['STATUS_AR'] . '</STATUS_AR>';
                    $returnXML .= '<STATUS_ARMSG>' . $row['STATUS_ARMSG'] . '</STATUS_ARMSG>';
                    $returnXML .= '<STATUS_RECEIPT>' . $row['STATUS_RECEIPT'] . '</STATUS_RECEIPT>';
                    $returnXML .= '<STATUS_RECEIPTMSG>' . $row['STATUS_RECEIPTMSG'] . '</STATUS_RECEIPTMSG>';
                    $returnXML .= '<BANK_ID>' . $row['BANK_ID'] . '</BANK_ID>';
                    $returnXML .= '<ARPROCESS_DATE>' . $row['ARPROCESS_DATE'] . '</ARPROCESS_DATE>';
                    $returnXML .= '<RECEIPTPROCESS_DATE>' . $row['RECEIPTPROCESS_DATE'] . '</RECEIPTPROCESS_DATE>';
                    $returnXML .= '<PAYMENT_CHANNEL>' . $row['PAYMENT_CHANNEL'] . '</PAYMENT_CHANNEL>';
                    $returnXML .= '<BRANCH_ACCOUNT>' . $row['BRANCH_ACCOUNT'] . '</BRANCH_ACCOUNT>';
                    $returnXML .= '<NO_NOTA_MTI>' . $row['NO_NOTA_MTI'] . '</NO_NOTA_MTI>';
                    $returnXML .= '<NO_FAKTUR_MTI>' . $row['NO_FAKTUR_MTI'] . '</NO_FAKTUR_MTI>';
                    $returnXML .= '<SP_MTI>' . $row['SP_MTI'] . '</SP_MTI>';
                    $returnXML .= '<ARRDETAIL>';
                    $queryDTL = "SELECT TRX_NUMBER, TAX_FLAG, LINE_NUMBER, LINE_DESCRIPTION, EI, OI, CRANE, SHIFT_BY, SIZE_TYPE_STAT_HAZ, SIZE_, TYPE_, STAT_, HAZ_, IMO_CLASS, DISCOUNT, BOXES, HARI, TON, M3, TIPE_LAYANAN, AMOUNT, START_DATE, END_DATE, PLUGIN, PLUGOUT, TARIF, CURRENCY, NO_NOTA_MTI, NO_FAKTUR_MTI FROM ITPK_NOTA_DETAIL  WHERE TRX_NUMBER = '" . $row['TRX_NUMBER'] . "'";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<DETAIL>';
                            $returnXML .= '<REQ_DTL_TRX_NUMBER>' . $dtl['TRX_NUMBER'] . '</REQ_DTL_TRX_NUMBER>';
                            $returnXML .= '<REQ_DTL_TAX_FLAG>' . $dtl['TAX_FLAG'] . '</REQ_DTL_TAX_FLAG>';
                            $returnXML .= '<REQ_DTL_LINE_NUMBER>' . $dtl['LINE_NUMBER'] . '</REQ_DTL_LINE_NUMBER>';
                            $returnXML .= '<REQ_DTL_LINE_DESCRIPTION>' . $dtl['LINE_DESCRIPTION'] . '</REQ_DTL_LINE_DESCRIPTION>';
                            $returnXML .= '<REQ_DTL_EI>' . $dtl['EI'] . '</REQ_DTL_EI>';
                            $returnXML .= '<REQ_DTL_OI>' . $dtl['OI'] . '</REQ_DTL_OI>';
                            $returnXML .= '<REQ_DTL_DEL_CRANE>' . $dtl['CRANE'] . '</REQ_DTL_DEL_CRANE>';
                            $returnXML .= '<REQ_DTL_SHIFT_BY>' . $dtl['SHIFT_BY'] . '</REQ_DTL_SHIFT_BY>';
                            $returnXML .= '<REQ_DTL_SIZE_TYPE_STAT_HAZ>' . $dtl['SIZE_TYPE_STAT_HAZ'] . '</REQ_DTL_SIZE_TYPE_STAT_HAZ>';
                            $returnXML .= '<REQ_DTL_SIZE_>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE_>';
                            $returnXML .= '<REQ_DTL_TYPE_>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE_>';
                            $returnXML .= '<REQ_DTL_STAT_>' . $dtl['STAT_'] . '</REQ_DTL_STAT_>';
                            $returnXML .= '<REQ_DTL_HAZ_>' . $dtl['HAZ_'] . '</REQ_DTL_HAZ_>';
                            $returnXML .= '<REQ_DTL_IMO_CLASS>' . $dtl['IMO_CLASS'] . '</REQ_DTL_IMO_CLASS>';
                            $returnXML .= '<REQ_DTL_DISCOUNT>' . $dtl['DISCOUNT'] . '</REQ_DTL_DISCOUNT>';
                            $returnXML .= '<REQ_DTL_BOXES>' . $dtl['BOXES'] . '</REQ_DTL_BOXES>';
                            $returnXML .= '<REQ_DTL_HARI>' . $dtl['HARI'] . '</REQ_DTL_HARI>';
                            $returnXML .= '<REQ_DTL_TON>' . $dtl['TON'] . '</REQ_DTL_TON>';
                            $returnXML .= '<REQ_DTL_M3>' . $dtl['M3'] . '</REQ_DTL_M3>';
                            $returnXML .= '<REQ_DTL_TIPE_LAYANAN>' . $dtl['TIPE_LAYANAN'] . '</REQ_DTL_TIPE_LAYANAN>';
                            $returnXML .= '<REQ_DTL_AMOUNT>' . $dtl['AMOUNT'] . '</REQ_DTL_AMOUNT>';
                            $returnXML .= '<REQ_DTL_START_DATE>' . $dtl['START_DATE'] . '</REQ_DTL_START_DATE>';
                            $returnXML .= '<REQ_DTL_END_DATE>' . $dtl['END_DATE'] . '</REQ_DTL_END_DATE>';
                            $returnXML .= '<REQ_DTL_PLUGIN>' . $dtl['PLUGIN'] . '</REQ_DTL_PLUGIN>';
                            $returnXML .= '<REQ_DTL_PLUGOUT>' . $dtl['PLUGOUT'] . '</REQ_DTL_PLUGOUT>';
                            $returnXML .= '<REQ_DTL_TARIF>' . $dtl['TARIF'] . '</REQ_DTL_TARIF>';
                            $returnXML .= '<REQ_DTL_CURRENCY>' . $dtl['CURRENCY'] . '</REQ_DTL_CURRENCY>';
                            $returnXML .= '<REQ_DTL_NO_NOTA_MTI>' . $dtl['NO_NOTA_MTI'] . '</REQ_DTL_NO_NOTA_MTI>';
                            $returnXML .= '<REQ_DTL_NO_FAKTUR_MTI>' . $dtl['NO_FAKTUR_MTI'] . '</REQ_DTL_NO_FAKTUR_MTI>';

                            $returnXML .= '</DETAIL>';
                        }
                    }
                    $returnXML .= '</ARRDETAIL>';
                    $returnXML .= '</data>';

                    $query_update    = "UPDATE ITPK_NOTA_HEADER SET FL_MTI_SEND = '1' WHERE TRX_NUMBER = '" . $row['TRX_NUMBER'] . "'  ";
                    DB::connection('uster')->statement($query_update);
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getDeliveryNoreq($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, B.EMKL NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.DELIVERY_KE
				FROM REQUEST_DELIVERY A
				INNER JOIN NOTA_DELIVERY B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND A.NO_REQUEST = '" . $xml . "' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_DELIVERY_DATE>' . $row['TGL_REQUEST'] . '</REQ_DELIVERY_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<DELIVERY_KE>' . $row['DELIVERY_KE'] . '</DELIVERY_KE>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_, TO_CHAR(A.TGL_DELIVERY,'MM/DD/YYYY HH24:MI:SS') TGL_DELIVERY, A.NO_SEAL  FROM CONTAINER_DELIVERY A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_CONT_STATUS>' . $dtl['STATUS'] . '</REQ_DTL_CONT_STATUS>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['KOMODITI']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_VIA>' . $dtl['VIA'] . '</REQ_DTL_VIA>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_DEL_DATE>' . $dtl['TGL_DELIVERY'] . '</REQ_DTL_DEL_DATE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_NO_SEAL>' . $dtl['NO_SEAL'] . '</REQ_DTL_NO_SEAL>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getDeliveryExRepo($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT * FROM (
					SELECT
						A.PERP_DARI,
						A.PERP_KE,
						A.NO_REQUEST,
						C.EMKL NM_CONSIGNEE,
						A.KETERANGAN,
						TO_CHAR(A.TGL_REQUEST, 'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,
						C.NO_NOTA,
						TO_CHAR(C.TGL_NOTA, 'MM/DD/YYYY HH24:MI:SS') TGL_NOTA,
						C.ALAMAT,
						C.NPWP,
						TO_CHAR(C.TGL_LUNAS, 'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS,
						A.DELIVERY_KE
					FROM
						REQUEST_DELIVERY A,
						REQUEST_BATAL_MUAT B,
						NOTA_BATAL_MUAT C
					WHERE
						A.NO_REQUEST = B.NO_REQ_BARU
						AND B.NO_REQUEST = C.NO_REQUEST
						AND B.BIAYA = 'Y'
						AND A.NOTA = 'Y'
						AND C.LUNAS = 'YES'
						AND TO_CHAR(C.TGL_LUNAS, 'YYYYMMDDHH24MISS') > '" . $xml . "'
					UNION ALL 
					SELECT
						A.PERP_DARI,
						A.PERP_KE,
						A.NO_REQUEST,
						C.NM_PBM NM_CONSIGNEE,
						A.KETERANGAN,
						TO_CHAR(A.TGL_REQUEST, 'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,
						'' NO_NOTA,
						'' TGL_NOTA,
						C.ALMT_PBM ALAMAT,
						C.NO_NPWP_PBM NPWP,
						'' TANGGAL_LUNAS,
						A.DELIVERY_KE
					FROM
						REQUEST_DELIVERY A,
						REQUEST_BATAL_MUAT B,
						V_MST_PBM C
					WHERE
						A.NO_REQUEST = B.NO_REQ_BARU
						AND B.BIAYA = 'T'
						AND NVL(A.NOTA, '0') = '0'
						AND A.KD_EMKL = C.KD_PBM
						AND A.PERALIHAN = 'NOTA_KIRIM'
						AND TO_CHAR(A.TGL_REQUEST, 'YYYYMMDDHH24MISS') > '" . $xml . "'
				)";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_DELIVERY_DATE>' . $row['TGL_REQUEST'] . '</REQ_DELIVERY_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<DELIVERY_KE>' . $row['DELIVERY_KE'] . '</DELIVERY_KE>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_, TO_CHAR(A.TGL_DELIVERY,'MM/DD/YYYY HH24:MI:SS') TGL_DELIVERY, A.NO_SEAL  FROM CONTAINER_DELIVERY A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_CONT_STATUS>' . $dtl['STATUS'] . '</REQ_DTL_CONT_STATUS>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['KOMODITI']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_VIA>' . $dtl['VIA'] . '</REQ_DTL_VIA>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_DEL_DATE>' . $dtl['TGL_DELIVERY'] . '</REQ_DTL_DEL_DATE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_NO_SEAL>' . $dtl['NO_SEAL'] . '</REQ_DTL_NO_SEAL>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getBatalMuat($user, $pass, $tgl)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT NO_REQUEST, JENIS_BM, STATUS_GATE, NO_REQ_BARU, TO_CHAR(TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGLREQUEST FROM request_batal_muat WHERE TO_CHAR(TGL_REQUEST,'YYYYMMDDHH24MISS') > '" . $tgl . "' ORDER BY TO_CHAR(TGL_REQUEST,'YYYYMMDDHH24MISS') ASC";
            //echo $query;die();	
            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<NO_REQUEST>' . $row['NO_REQUEST'] . '</NO_REQUEST>';
                    $returnXML .= '<JENIS_BM>' . $row['JENIS_BM'] . '</JENIS_BM>';
                    $returnXML .= '<STATUS_GATE>' . $row['STATUS_GATE'] . '</STATUS_GATE>';
                    $returnXML .= '<NO_REQ_BARU>' . $row['NO_REQ_BARU'] . '</NO_REQ_BARU>';
                    $returnXML .= '<TGLREQUEST>' . $row['TGLREQUEST'] . '</TGLREQUEST>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';

                    $queryDTL = "SELECT NO_CONTAINER, NO_REQ_BATAL, STATUS FROM container_batal_muat WHERE NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<NO_CONTAINER>' . $dtl['NO_CONTAINER'] . '</NO_CONTAINER>';
                            $returnXML .= '<NO_REQ_BATAL>' . $dtl['NO_REQ_BATAL'] . '</NO_REQ_BATAL>';
                            $returnXML .= '<STATUS_CONT>' . $dtl['STATUS'] . '</STATUS_CONT>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getAlihKapalStuffing($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT A.STUFFING_DARI, A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, A.NO_BOOKING, A.O_IDVSB NO_UKK, B.EMKL NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.NO_REQUEST_RECEIVING
				FROM REQUEST_STUFFING A
				LEFT JOIN NOTA_STUFFING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND A.NO_REQUEST = '" . $xml . "' ";

            $result = DB::connection('uster')->select($query);
            // print_r($result->RecordCount());die();
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_STUFF_DATE>' . $row['TGL_REQUEST'] . '</REQ_STUFF_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NO_UKK>' . $row['NO_UKK'] . '</NO_UKK>';
                    $returnXML .= '<NO_BOOKING>' . $row['NO_BOOKING'] . '</NO_BOOKING>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<NO_REQUEST_RECEIVING>' . $row['NO_REQUEST_RECEIVING'] . '</NO_REQUEST_RECEIVING>';
                    $returnXML .= '<STUFFING_DARI>' . $row['STUFFING_DARI'] . '</STUFFING_DARI>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.COMMODITY, A.HZ, B.SIZE_, B.TYPE_, A.REMARK_SP2, A.ASAL_CONT, TO_CHAR(A.START_PERP_PNKN,'MM/DD/YYYY HH24:MI:SS') TGL_SELESAI, TO_CHAR(A.START_STACK,'MM/DD/YYYY HH24:MI:SS') TGL_MULAI  FROM CONTAINER_STUFFING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['COMMODITY']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_REMARK_SP2>' . $dtl['REMARK_SP2'] . '</REQ_DTL_REMARK_SP2>';
                            $returnXML .= '<REQ_DTL_ORIGIN>' . $dtl['ASAL_CONT'] . '</REQ_DTL_ORIGIN>';
                            $returnXML .= '<TGL_MULAI>' . $dtl['TGL_MULAI'] . '</TGL_MULAI>';
                            $returnXML .= '<TGL_SELESAI>' . $dtl['TGL_SELESAI'] . '</TGL_SELESAI>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getBatalNota($user, $pass, $tgl)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT
					A.NO_REQUEST,
					A.NO_NOTA,
					TO_CHAR(B.TGL_KOREKSI, 'MM/DD/YYYY HH24:MI:SS') TGL_KOREKSI,
					'DELIVERY' REQUEST
				FROM
					NOTA_DELIVERY A,
					REQUEST_DELIVERY B
				WHERE
					A.NO_REQUEST = B.NO_REQUEST
					AND A.STATUS = 'BATAL'
					AND TO_CHAR(B.TGL_KOREKSI, 'YYYYMMDDHH24MISS') > '" . $tgl . "'
				UNION ALL
				SELECT
					A.NO_REQUEST,
					A.NO_NOTA,
					TO_CHAR(B.TGL_KOREKSI, 'MM/DD/YYYY HH24:MI:SS') TGL_KOREKSI,
					'RECEIVING' REQUEST
				FROM
					NOTA_RECEIVING A,
					REQUEST_RECEIVING B
				WHERE
					A.NO_REQUEST = B.NO_REQUEST
					AND A.STATUS = 'BATAL'
					AND TO_CHAR(B.TGL_KOREKSI, 'YYYYMMDDHH24MISS') > '" . $tgl . "'
				UNION ALL
				SELECT
					A.NO_REQUEST,
					A.NO_NOTA,
					TO_CHAR(B.TGL_KOREKSI, 'MM/DD/YYYY HH24:MI:SS') TGL_KOREKSI,
					'STRIPPING' REQUEST
				FROM
					NOTA_STRIPPING A,
					REQUEST_STRIPPING B
				WHERE
					A.NO_REQUEST = B.NO_REQUEST
					AND A.STATUS = 'BATAL'
					AND TO_CHAR(B.TGL_KOREKSI, 'YYYYMMDDHH24MISS') > '" . $tgl . "'
				UNION ALL
				SELECT
					A.NO_REQUEST,
					A.NO_NOTA,
					TO_CHAR(B.TGL_KOREKSI, 'MM/DD/YYYY HH24:MI:SS') TGL_KOREKSI,
					'STUFFING' REQUEST
				FROM
					NOTA_STUFFING A,
					REQUEST_STUFFING B
				WHERE
					A.NO_REQUEST = B.NO_REQUEST
					AND A.STATUS = 'BATAL'
					AND TO_CHAR(B.TGL_KOREKSI, 'YYYYMMDDHH24MISS') > '" . $tgl . "'
				";
            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<NO_REQUEST>' . $row['NO_REQUEST'] . '</NO_REQUEST>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_KOREKSI>' . $row['TGL_KOREKSI'] . '</TGL_KOREKSI>';
                    $returnXML .= '<REQUEST>' . $row['REQUEST'] . '</REQUEST>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getBatalStuffStrip($user, $pass, $tgl)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT * FROM (SELECT ID_REQ_SPPS NO_REQUEST, SUBSTR(ID_REQ_SPPS,0,3) JENIS, NO_CONTAINER, NO_BA, TO_CHAR(TANGGAL_PEMBUATAN,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST 
					FROM REQ_BATAL_SPPS 	
					WHERE  TO_CHAR(TANGGAL_PEMBUATAN,'YYYYMMDDHH24MISS') > '" . $tgl . "' 
					UNION ALL
					SELECT NO_REQUEST, SUBSTR(NO_REQUEST, 0, 3) JENIS, NO_CONTAINER, 'BILLING' NO_BA ,TO_CHAR(TGL_UPDATE, 'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST 
					FROM HISTORY_CONTAINER 
					WHERE KEGIATAN IN ('BATAL STRIPPING', 'BATAL STUFFING') AND TO_CHAR(TGL_UPDATE, 'YYYYMMDDHH24MISS') > '" . $tgl . "'
					) ORDER BY TGL_REQUEST ASC";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<JENIS>' . $row['JENIS'] . '</JENIS>';
                    $returnXML .= '<NO_CONTAINER>' . $row['NO_CONTAINER'] . '</NO_CONTAINER>';
                    $returnXML .= '<NO_BA>' . $row['NO_BA'] . '</NO_BA>';
                    $returnXML .= '<TGL_REQUEST>' . $row['TGL_REQUEST'] . '</TGL_REQUEST>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getBatalSP2($user, $pass, $tgl)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT NO_REQUEST, SUBSTR(NO_REQUEST, 0, 3) JENIS, NO_CONTAINER, 'BATAL SP2' NO_BA ,TO_CHAR(TGL_UPDATE, 'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST 
					FROM HISTORY_CONTAINER 
					WHERE KEGIATAN = 'BATAL SP2' AND TO_CHAR(TGL_UPDATE, 'YYYYMMDDHH24MISS') > '" . $tgl . "' 
					ORDER BY TO_CHAR(TGL_UPDATE, 'YYYYMMDDHH24MISS') ASC";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<JENIS>' . $row['JENIS'] . '</JENIS>';
                    $returnXML .= '<NO_CONTAINER>' . $row['NO_CONTAINER'] . '</NO_CONTAINER>';
                    $returnXML .= '<NO_BA>' . $row['NO_BA'] . '</NO_BA>';
                    $returnXML .= '<TGL_REQUEST>' . $row['TGL_REQUEST'] . '</TGL_REQUEST>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getBatalReceivingMTY($user, $pass, $tgl)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT NO_REQUEST, SUBSTR(NO_REQUEST, 0, 3) JENIS, NO_CONTAINER, 'BATAL RECEIVING MTY' NO_BA ,TO_CHAR(TGL_UPDATE, 'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST 
					FROM HISTORY_CONTAINER 
					WHERE KEGIATAN = 'BATAL RECEIVING' AND TO_CHAR(TGL_UPDATE, 'YYYYMMDDHH24MISS') > '" . $tgl . "' 
					ORDER BY TO_CHAR(TGL_UPDATE, 'YYYYMMDDHH24MISS') ASC";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<JENIS>' . $row['JENIS'] . '</JENIS>';
                    $returnXML .= '<NO_CONTAINER>' . $row['NO_CONTAINER'] . '</NO_CONTAINER>';
                    $returnXML .= '<NO_BA>' . $row['NO_BA'] . '</NO_BA>';
                    $returnXML .= '<TGL_REQUEST>' . $row['TGL_REQUEST'] . '</TGL_REQUEST>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getBatalOperation($user, $pass, $tgl)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT NO_CONTAINER, NO_REQUEST, KEGIATAN, TO_CHAR(TIMES,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST FROM CANCEL_OPERATION_LOG WHERE  TO_CHAR(TIMES,'YYYYMMDDHH24MISS') > '" . $tgl . "' ORDER BY TO_CHAR(TIMES,'YYYYMMDDHH24MISS') ASC";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<NO_CONTAINER>' . $row['NO_CONTAINER'] . '</NO_CONTAINER>';
                    $returnXML .= '<NO_REQUEST>' . $row['NO_REQUEST'] . '</NO_REQUEST>';
                    $returnXML .= '<KEGIATAN>' . $row['KEGIATAN'] . '</KEGIATAN>';
                    $returnXML .= '<TGL_REQUEST>' . $row['TGL_REQUEST'] . '</TGL_REQUEST>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }


    function getListContGateOut($user, $pass, $noreq)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT 
					A.NO_REQUEST, 
					A.NO_CONTAINER,  
					A.STATUS,					
					TO_CHAR(C.TGL_UPDATE,'MM/DD/YYYY HH24:MI:SS') TGL_UPDATE
				FROM 
					CONTAINER_DELIVERY A,
					MASTER_CONTAINER B,
					HISTORY_CONTAINER C
				WHERE 
					A.NO_REQUEST = '" . $noreq . "' 
					AND A.NO_CONTAINER = B.NO_CONTAINER 
					AND B.LOCATION = 'GATO'
					AND C.NO_CONTAINER = A.NO_CONTAINER
					AND C.NO_REQUEST = A.NO_REQUEST
					AND C.KEGIATAN = 'BORDER GATE OUT'";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<NO_CONTAINER>' . $row['NO_CONTAINER'] . '</NO_CONTAINER>';
                    $returnXML .= '<NO_REQUEST>' . $row['NO_REQUEST'] . '</NO_REQUEST>';
                    $returnXML .= '<STATUS>' . $row['STATUS'] . '</STATUS>';
                    $returnXML .= '<TGL_UPDATE>' . $row['TGL_UPDATE'] . '</TGL_UPDATE>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getReceiving($user, $pass, $tgl)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT A.DI, A.NO_REQUEST, A.NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,B.NO_NOTA,TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.EMKL, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.RECEIVING_DARI
				FROM REQUEST_RECEIVING A
				INNER JOIN NOTA_RECEIVING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND NVL(A.PERALIHAN,'0') = '0' AND B.LUNAS = 'YES' AND TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') > '" . $tgl . "' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_RECEIVING_DATE>' . $row['TGL_REQUEST'] . '</REQ_RECEIVING_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<RECEIVING_DARI>' . $row['RECEIVING_DARI'] . '</RECEIVING_DARI>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<DI>' . $row['DI'] . '</DI>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_, A.KD_OWNER, A.NM_OWNER  FROM CONTAINER_RECEIVING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_CONT_STATUS>' . $dtl['STATUS'] . '</REQ_DTL_CONT_STATUS>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['KOMODITI']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_VIA>' . $dtl['VIA'] . '</REQ_DTL_VIA>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_OWNER_CODE>' . $dtl['KD_OWNER'] . '</REQ_DTL_OWNER_CODE>';
                            $returnXML .= '<REQ_DTL_OWNER_NAME>' . $dtl['NM_OWNER'] . '</REQ_DTL_OWNER_NAME>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getReceivingNoreq($user, $pass, $noreq)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT A.DI, A.NO_REQUEST, A.NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,B.NO_NOTA,TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.EMKL, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.RECEIVING_DARI
				FROM REQUEST_RECEIVING A
				INNER JOIN NOTA_RECEIVING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND A.NO_REQUEST = '" . $noreq . "' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_RECEIVING_DATE>' . $row['TGL_REQUEST'] . '</REQ_RECEIVING_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<RECEIVING_DARI>' . $row['RECEIVING_DARI'] . '</RECEIVING_DARI>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<DI>' . $row['DI'] . '</DI>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_, A.KD_OWNER, A.NM_OWNER  FROM CONTAINER_RECEIVING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "' AND A.AKTIF = 'Y' ";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_CONT_STATUS>' . $dtl['STATUS'] . '</REQ_DTL_CONT_STATUS>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['KOMODITI']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_VIA>' . $dtl['VIA'] . '</REQ_DTL_VIA>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_OWNER_CODE>' . $dtl['KD_OWNER'] . '</REQ_DTL_OWNER_CODE>';
                            $returnXML .= '<REQ_DTL_OWNER_NAME>' . $dtl['NM_OWNER'] . '</REQ_DTL_OWNER_NAME>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getGateInFromTPK($user, $pass, $NoreqReciving)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $arrData = explode("~", $NoreqReciving);
            $cekSql = "SELECT PERALIHAN FROM REQUEST_RECEIVING WHERE NO_REQUEST = '" . $arrData[0] . "'";
            $result = DB::connection('uster')->select($cekSql);
            if ($result) {
                $result = $this->convertKeysToUppercase($result);
                foreach ($result as $row) {
                    $tipePeralihan = $row['PERALIHAN'];
                }
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
                return $returnXML;
                die();
            }

            $query = "SELECT B.SIZE_ CONT_SIZE, B.TYPE_ CONT_TYPE, A.NO_CONTAINER, A.NO_REQUEST, A.STATUS_CONT, 'PELINDO' TRUK, 'TPK' ORIGIN, A.KEGIATAN , '$tipePeralihan' PERALIHAN, TO_CHAR(A.TGL_UPDATE,'MM/DD/YYYY HH24:MI:SS') TGL_GATE 
        		  FROM HISTORY_CONTAINER A INNER JOIN MASTER_CONTAINER B ON B.NO_CONTAINER =A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $arrData[0] . "' AND A.NO_CONTAINER = '" . $arrData[1] . "' AND A.KEGIATAN = 'BORDER GATE IN' ";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<NO_CONT>' . $row['NO_CONTAINER'] . '</NO_CONT>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<TGL_GATE>' . $row['TGL_GATE'] . '</TGL_GATE>';
                    $returnXML .= '<STATUS_CONT>' . $row['STATUS_CONT'] . '</STATUS_CONT>';
                    $returnXML .= '<TRUK>' . $row['TRUK'] . '</TRUK>';
                    $returnXML .= '<ORIGIN>' . $row['ORIGIN'] . '</ORIGIN>';
                    $returnXML .= '<KEGIATAN>' . $row['KEGIATAN'] . '</KEGIATAN>';
                    $returnXML .= '<PERALIHAN>' . $row['PERALIHAN'] . '</PERALIHAN>';
                    $returnXML .= '<CONT_SIZE>' . $row['CONT_SIZE'] . '</CONT_SIZE>';
                    $returnXML .= '<CONT_TYPE>' . $row['CONT_TYPE'] . '</CONT_TYPE>';
                    $returnXML .= '</header>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getGateOutToTPK($user, $pass, $NoreqDelivery)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $arrData = explode("~", $NoreqDelivery);
            $cekData = DB::connection('uster')->selectOne("SELECT DELIVERY_KE FROM REQUEST_DELIVERY WHERE NO_REQUEST = '" . $arrData[0] . "'");
            $rowdata = $this->convertKeysToUppercase($cekData);

            if ($rowdata['DELIVERY_KE'] != 'TPK') {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
                return $returnXML;
                die();
            }

            $query = "SELECT NO_CONTAINER, NO_REQUEST, STATUS_CONT, 'PELINDO' TRUK, 'TPK' DELIVERY_TO, KEGIATAN ,  TO_CHAR(TGL_UPDATE,'MM/DD/YYYY HH24:MI:SS') TGL_GATE 
				  FROM HISTORY_CONTAINER 
				  WHERE NO_REQUEST = '" . $arrData[0] . "' AND NO_CONTAINER = '" . $arrData[1] . "' AND KEGIATAN = 'BORDER GATE OUT' ";

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<NO_CONT>' . $row['NO_CONTAINER'] . '</NO_CONT>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<TGL_GATE>' . $row['TGL_GATE'] . '</TGL_GATE>';
                    $returnXML .= '<STATUS_CONT>' . $row['STATUS_CONT'] . '</STATUS_CONT>';
                    $returnXML .= '<TRUK>' . $row['TRUK'] . '</TRUK>';
                    $returnXML .= '<DELIVERY_TO>' . $row['DELIVERY_TO'] . '</DELIVERY_TO>';
                    $returnXML .= '<KEGIATAN>' . $row['KEGIATAN'] . '</KEGIATAN>';
                    $returnXML .= '</header>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getReceivingFromTPK($user, $pass, $NoreqReciving)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $cekSql = "SELECT PERALIHAN FROM REQUEST_RECEIVING WHERE NO_REQUEST = '" . $NoreqReciving . "'";
            $result = DB::connection('uster')->select($cekSql);
            if ($result) {
                $result = $this->convertKeysToUppercase($result);
                foreach ($result as $row) {
                    $tipePeralihan = $row['PERALIHAN'];
                }
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
                return $returnXML;
                die();
            }

            if ($tipePeralihan == 'STRIPPING') {
                $query = "SELECT A.NO_REQUEST, A.NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,'AUTO_RECEIVE' NO_NOTA, 
						(SELECT DISTINCT I.EMKL FROM REQUEST_STRIPPING H LEFT JOIN NOTA_STRIPPING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STRIPPING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING))  EMKL, 
						(SELECT DISTINCT I.ALAMAT FROM REQUEST_STRIPPING H LEFT JOIN NOTA_STRIPPING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STRIPPING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING)) ALAMAT,
						(SELECT DISTINCT I.NPWP FROM REQUEST_STRIPPING H LEFT JOIN NOTA_STRIPPING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STRIPPING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING)) NPWP, 
						 A.RECEIVING_DARI, A.PERALIHAN
					FROM REQUEST_RECEIVING A 
					WHERE A.NO_REQUEST = '" . $NoreqReciving . "'";
            } else {
                $query = "SELECT A.NO_REQUEST, A.NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,'AUTO_RECEIVE' NO_NOTA, 
						(SELECT DISTINCT I.EMKL FROM REQUEST_STUFFING H LEFT JOIN NOTA_STUFFING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STUFFING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING))  EMKL, 
						(SELECT DISTINCT I.ALAMAT FROM REQUEST_STUFFING H LEFT JOIN NOTA_STUFFING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STUFFING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING)) ALAMAT,
						(SELECT DISTINCT I.NPWP FROM REQUEST_STUFFING H LEFT JOIN NOTA_STUFFING I ON I.NO_REQUEST = H.NO_REQUEST  WHERE H.NO_REQUEST_RECEIVING = A.NO_REQUEST AND H.TGL_REQUEST = (SELECT MIN(TGL_REQUEST) FROM REQUEST_STUFFING WHERE NO_REQUEST_RECEIVING = H.NO_REQUEST_RECEIVING)) NPWP,	 
						 A.RECEIVING_DARI, A.PERALIHAN
					FROM REQUEST_RECEIVING A 
					WHERE A.NO_REQUEST = '" . $NoreqReciving . "'";
            }

            $result = DB::connection('uster')->select($query);
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_RECEIVING_DATE>' . $row['TGL_REQUEST'] . '</REQ_RECEIVING_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<RECEIVING_DARI>' . $row['RECEIVING_DARI'] . '</RECEIVING_DARI>';
                    $returnXML .= '<PERALIHAN>' . $row['PERALIHAN'] . '</PERALIHAN>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.STATUS, A.KOMODITI, A.VIA, A.HZ, B.SIZE_, B.TYPE_  FROM CONTAINER_RECEIVING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->select($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_CONT_STATUS>' . $dtl['STATUS'] . '</REQ_DTL_CONT_STATUS>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['KOMODITI']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_VIA>' . $dtl['VIA'] . '</REQ_DTL_VIA>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getStuffing($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT A.STUFFING_DARI, A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, A.NO_BOOKING, A.O_IDVSB NO_UKK, B.EMKL NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.NO_REQUEST_RECEIVING
				FROM REQUEST_STUFFING A
				INNER JOIN NOTA_STUFFING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') > '" . $xml . "' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->result($query);
            // print_r($result->RecordCount());die();
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_STUFF_DATE>' . $row['TGL_REQUEST'] . '</REQ_STUFF_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NO_UKK>' . $row['NO_UKK'] . '</NO_UKK>';
                    $returnXML .= '<NO_BOOKING>' . $row['NO_BOOKING'] . '</NO_BOOKING>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<NO_REQUEST_RECEIVING>' . $row['NO_REQUEST_RECEIVING'] . '</NO_REQUEST_RECEIVING>';
                    $returnXML .= '<STUFFING_DARI>' . $row['STUFFING_DARI'] . '</STUFFING_DARI>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.COMMODITY, A.HZ, B.SIZE_, B.TYPE_, A.REMARK_SP2, A.ASAL_CONT, TO_CHAR(A.START_PERP_PNKN,'MM/DD/YYYY HH24:MI:SS') TGL_MULAI, TO_CHAR(A.END_STACK_PNKN,'MM/DD/YYYY HH24:MI:SS') TGL_SELESAI , TO_CHAR(A.START_STACK,'MM/DD/YYYY HH24:MI:SS') START_STACK  FROM CONTAINER_STUFFING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->result($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['COMMODITY']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_REMARK_SP2>' . $dtl['REMARK_SP2'] . '</REQ_DTL_REMARK_SP2>';
                            $returnXML .= '<REQ_DTL_ORIGIN>' . $dtl['ASAL_CONT'] . '</REQ_DTL_ORIGIN>';
                            if ($dtl['TGL_SELESAI'] != '') {
                                $returnXML .= '<TGL_MULAI>' . $dtl['TGL_MULAI'] . '</TGL_MULAI>';
                                $returnXML .= '<TGL_SELESAI>' . $dtl['TGL_SELESAI'] . '</TGL_SELESAI>';
                            } else {
                                $returnXML .= '<TGL_MULAI>' . $dtl['START_STACK'] . '</TGL_MULAI>';
                                $returnXML .= '<TGL_SELESAI>' . $dtl['TGL_MULAI'] . '</TGL_SELESAI>';
                            }
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getStuffingAlihKapal($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT
					A.STUFFING_DARI,
					A.PERP_DARI,
					A.PERP_KE,
					A.NO_REQUEST,
					A.NO_BOOKING,
					A.O_IDVSB NO_UKK,
					C.EMKL NM_CONSIGNEE,
					A.KETERANGAN,
					TO_CHAR(A.TGL_REQUEST, 'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST,
					C.NO_NOTA,
					TO_CHAR(C.TGL_NOTA, 'MM/DD/YYYY HH24:MI:SS') TGL_NOTA,
					C.ALAMAT,
					C.NPWP,
					TO_CHAR(C.TGL_LUNAS, 'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS,	
					A.NO_REQUEST_RECEIVING,
					B.STATUS_GATE
				FROM
					REQUEST_STUFFING A, REQUEST_BATAL_MUAT B, NOTA_BATAL_MUAT C
				WHERE
					A.NO_REQUEST = B.NO_REQ_BARU
					AND B.NO_REQUEST = C.NO_REQUEST
					AND B.BIAYA = 'Y'
					AND A.NOTA = 'Y'
					AND C.LUNAS = 'YES'
					AND TO_CHAR(C.TGL_LUNAS, 'YYYYMMDDHH24MISS') > '" . $xml . "'
				ORDER BY
					TO_CHAR(C.TGL_LUNAS, 'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->result($query);
            // print_r($result->RecordCount());die();
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_STUFF_DATE>' . $row['TGL_REQUEST'] . '</REQ_STUFF_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NO_UKK>' . $row['NO_UKK'] . '</NO_UKK>';
                    $returnXML .= '<NO_BOOKING>' . $row['NO_BOOKING'] . '</NO_BOOKING>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<NO_REQUEST_RECEIVING>' . $row['NO_REQUEST_RECEIVING'] . '</NO_REQUEST_RECEIVING>';
                    $returnXML .= '<STUFFING_DARI>' . $row['STUFFING_DARI'] . '</STUFFING_DARI>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '<PERP_KE>' . $row['STATUS_GATE'] . '</STATUS_GATE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.COMMODITY, A.HZ, B.SIZE_, B.TYPE_, A.REMARK_SP2, A.ASAL_CONT, TO_CHAR(A.START_PERP_PNKN,'MM/DD/YYYY HH24:MI:SS') TGL_MULAI, TO_CHAR(A.END_STACK_PNKN,'MM/DD/YYYY HH24:MI:SS') TGL_SELESAI , TO_CHAR(A.START_STACK,'MM/DD/YYYY HH24:MI:SS') START_STACK  FROM CONTAINER_STUFFING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->result($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['COMMODITY']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_REMARK_SP2>' . $dtl['REMARK_SP2'] . '</REQ_DTL_REMARK_SP2>';
                            $returnXML .= '<REQ_DTL_ORIGIN>' . $dtl['ASAL_CONT'] . '</REQ_DTL_ORIGIN>';
                            if ($dtl['TGL_SELESAI'] != '') {
                                $returnXML .= '<TGL_MULAI>' . $dtl['TGL_MULAI'] . '</TGL_MULAI>';
                                $returnXML .= '<TGL_SELESAI>' . $dtl['TGL_SELESAI'] . '</TGL_SELESAI>';
                            } else {
                                $returnXML .= '<TGL_MULAI>' . $dtl['START_STACK'] . '</TGL_MULAI>';
                                $returnXML .= '<TGL_SELESAI>' . $dtl['TGL_MULAI'] . '</TGL_SELESAI>';
                            }
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getStuffingNoreq($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT A.STUFFING_DARI, A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, A.NO_BOOKING, A.O_IDVSB NO_UKK, B.EMKL NM_CONSIGNEE, A.KETERANGAN, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.NO_REQUEST_RECEIVING
				FROM REQUEST_STUFFING A
				INNER JOIN NOTA_STUFFING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND A.NO_REQUEST = '" . $xml . "' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->result($query);
            // print_r($result->RecordCount());die();
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);

                foreach ($result as $row) {
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_STUFF_DATE>' . $row['TGL_REQUEST'] . '</REQ_STUFF_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NO_UKK>' . $row['NO_UKK'] . '</NO_UKK>';
                    $returnXML .= '<NO_BOOKING>' . $row['NO_BOOKING'] . '</NO_BOOKING>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<NO_REQUEST_RECEIVING>' . $row['NO_REQUEST_RECEIVING'] . '</NO_REQUEST_RECEIVING>';
                    $returnXML .= '<STUFFING_DARI>' . $row['STUFFING_DARI'] . '</STUFFING_DARI>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT A.NO_CONTAINER, A.COMMODITY, A.HZ, B.SIZE_, B.TYPE_, A.REMARK_SP2, A.ASAL_CONT, TO_CHAR(A.START_PERP_PNKN,'MM/DD/YYYY HH24:MI:SS') TGL_SELESAI, TO_CHAR(A.START_STACK,'MM/DD/YYYY HH24:MI:SS') TGL_MULAI  FROM CONTAINER_STUFFING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->result($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['COMMODITY']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_REMARK_SP2>' . $dtl['REMARK_SP2'] . '</REQ_DTL_REMARK_SP2>';
                            $returnXML .= '<REQ_DTL_ORIGIN>' . $dtl['ASAL_CONT'] . '</REQ_DTL_ORIGIN>';
                            $returnXML .= '<TGL_MULAI>' . $dtl['TGL_MULAI'] . '</TGL_MULAI>';
                            $returnXML .= '<TGL_SELESAI>' . $dtl['TGL_SELESAI'] . '</TGL_SELESAI>';
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getStripping($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT 'TPK' STRIP_DARI, A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, B.EMKL NM_CONSIGNEE, A.KETERANGAN, A.NO_BL, A.NO_DO, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.NO_REQUEST_RECEIVING
				FROM REQUEST_STRIPPING A
				INNER JOIN NOTA_STRIPPING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') > '" . $xml . "' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->result($query);
            // print_r($result->RecordCount());die();
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_STRIP_DATE>' . $row['TGL_REQUEST'] . '</REQ_STRIP_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<DO>' . $row['NO_DO'] . '</DO>';
                    $returnXML .= '<BL>' . $row['NO_BL'] . '</BL>';
                    $returnXML .= '<NO_REQUEST_RECEIVING>' . $row['NO_REQUEST_RECEIVING'] . '</NO_REQUEST_RECEIVING>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<STRIP_DARI>' . $row['STRIP_DARI'] . '</STRIP_DARI>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT TO_CHAR(A.START_PERP_PNKN, 'MM/DD/YYYY HH24:MI:SS') START_PERP_PNKN, TO_CHAR(A.END_STACK_PNKN, 'MM/DD/YYYY HH24:MI:SS') END_STACK_PNKN, A.NO_CONTAINER, A.COMMODITY, A.HZ, B.SIZE_, B.TYPE_, A.VIA, TO_CHAR(A.TGL_SELESAI,'MM/DD/YYYY HH24:MI:SS') TGL_SELESAI, TO_CHAR(A.TGL_BONGKAR,'MM/DD/YYYY HH24:MI:SS') TGL_MULAI  FROM CONTAINER_STRIPPING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->result($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['COMMODITY']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_ORIGIN>' . $dtl['VIA'] . '</REQ_DTL_ORIGIN>';
                            if ($dtl['START_PERP_PNKN'] == "") {
                                $returnXML .= '<TGL_MULAI>' . $dtl['TGL_MULAI'] . '</TGL_MULAI>';
                                $returnXML .= '<TGL_SELESAI>' . $dtl['TGL_SELESAI'] . '</TGL_SELESAI>';
                            } else {
                                $returnXML .= '<TGL_MULAI>' . $dtl['START_PERP_PNKN'] . '</TGL_MULAI>';
                                $returnXML .= '<TGL_SELESAI>' . $dtl['END_STACK_PNKN'] . '</TGL_SELESAI>';
                            }

                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function getStrippingNoreq($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $query = "SELECT 'TPK' STRIP_DARI, A.PERP_DARI, A.PERP_KE, A.NO_REQUEST, B.EMKL NM_CONSIGNEE, A.KETERANGAN, A.NO_BL, A.NO_DO, TO_CHAR(A.TGL_REQUEST,'MM/DD/YYYY HH24:MI:SS') TGL_REQUEST, B.NO_NOTA, TO_CHAR(B.TGL_NOTA,'MM/DD/YYYY HH24:MI:SS') TGL_NOTA, B.ALAMAT, B.NPWP, TO_CHAR(B.TANGGAL_LUNAS,'MM/DD/YYYY HH24:MI:SS') TANGGAL_LUNAS, A.NO_REQUEST_RECEIVING
				FROM REQUEST_STRIPPING A
				INNER JOIN NOTA_STRIPPING B ON A.NO_REQUEST = B.NO_REQUEST
				WHERE A.NOTA = 'Y' AND B.LUNAS = 'YES' AND A.NO_REQUEST = '" . $xml . "' ORDER BY TO_CHAR(B.TANGGAL_LUNAS,'YYYYMMDDHH24MISS') ASC ";

            $result = DB::connection('uster')->result($query);
            // print_r($result->RecordCount());die();
            if ($result) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';
                $result = $this->convertKeysToUppercase($result);
                $a = 0;
                foreach ($result as $row) {
                    $a++;
                    $returnXML .= '<data>';
                    $returnXML .= '<header>';
                    $returnXML .= '<REQ_NO>' . $row['NO_REQUEST'] . '</REQ_NO>';
                    $returnXML .= '<REQ_STRIP_DATE>' . $row['TGL_REQUEST'] . '</REQ_STRIP_DATE>';
                    $returnXML .= '<NO_NOTA>' . $row['NO_NOTA'] . '</NO_NOTA>';
                    $returnXML .= '<TGL_NOTA>' . $row['TGL_NOTA'] . '</TGL_NOTA>';
                    $returnXML .= '<NM_CONSIGNEE>' . $row['NM_CONSIGNEE'] . '</NM_CONSIGNEE>';
                    $returnXML .= '<ALAMAT>' . $row['ALAMAT'] . '</ALAMAT>';
                    $returnXML .= '<REQ_MARK>' . $row['KETERANGAN'] . '</REQ_MARK>';
                    $returnXML .= '<NPWP>' . $row['NPWP'] . '</NPWP>';
                    $returnXML .= '<DO>' . $row['NO_DO'] . '</DO>';
                    $returnXML .= '<BL>' . $row['NO_BL'] . '</BL>';
                    $returnXML .= '<NO_REQUEST_RECEIVING>' . $row['NO_REQUEST_RECEIVING'] . '</NO_REQUEST_RECEIVING>';
                    $returnXML .= '<TANGGAL_LUNAS>' . $row['TANGGAL_LUNAS'] . '</TANGGAL_LUNAS>';
                    $returnXML .= '<STRIP_DARI>' . $row['STRIP_DARI'] . '</STRIP_DARI>';
                    $returnXML .= '<PERP_DARI>' . $row['PERP_DARI'] . '</PERP_DARI>';
                    $returnXML .= '<PERP_KE>' . $row['PERP_KE'] . '</PERP_KE>';
                    $returnXML .= '</header>';
                    $returnXML .= '<arrdetail>';
                    $queryDTL = "SELECT TO_CHAR(A.START_PERP_PNKN, 'MM/DD/YYYY HH24:MI:SS') START_PERP_PNKN, TO_CHAR(A.END_STACK_PNKN, 'MM/DD/YYYY HH24:MI:SS') END_STACK_PNKN, A.NO_CONTAINER, A.COMMODITY, A.HZ, B.SIZE_, B.TYPE_, A.VIA, TO_CHAR(A.TGL_SELESAI,'MM/DD/YYYY HH24:MI:SS') TGL_SELESAI, TO_CHAR(A.TGL_BONGKAR,'MM/DD/YYYY HH24:MI:SS') TGL_MULAI  FROM CONTAINER_STRIPPING A LEFT JOIN MASTER_CONTAINER B ON B.NO_CONTAINER = A.NO_CONTAINER WHERE A.NO_REQUEST = '" . $row['NO_REQUEST'] . "'";
                    $resultDTL = DB::connection('uster')->result($queryDTL);
                    if ($resultDTL) {
                        $resultDTL = $this->convertKeysToUppercase($resultDTL);
                        foreach ($resultDTL as $dtl) {
                            $returnXML .= '<detail>';
                            $returnXML .= '<REQ_DTL_CONT>' . $dtl['NO_CONTAINER'] . '</REQ_DTL_CONT>';
                            $returnXML .= '<REQ_DTL_COMMODITY>' . trim($dtl['COMMODITY']) . '</REQ_DTL_COMMODITY>';
                            $returnXML .= '<REQ_DTL_SIZE>' . $dtl['SIZE_'] . '</REQ_DTL_SIZE>';
                            $returnXML .= '<REQ_DTL_TYPE>' . $dtl['TYPE_'] . '</REQ_DTL_TYPE>';
                            $returnXML .= '<REQ_DTL_CONT_HAZARD>' . $dtl['HZ'] . '</REQ_DTL_CONT_HAZARD>';
                            $returnXML .= '<REQ_DTL_ORIGIN>' . $dtl['VIA'] . '</REQ_DTL_ORIGIN>';
                            if ($dtl['START_PERP_PNKN'] == "") {
                                $returnXML .= '<TGL_MULAI>' . $dtl['TGL_MULAI'] . '</TGL_MULAI>';
                                $returnXML .= '<TGL_SELESAI>' . $dtl['TGL_SELESAI'] . '</TGL_SELESAI>';
                            } else {
                                $returnXML .= '<TGL_MULAI>' . $dtl['START_PERP_PNKN'] . '</TGL_MULAI>';
                                $returnXML .= '<TGL_SELESAI>' . $dtl['END_STACK_PNKN'] . '</TGL_SELESAI>';
                            }
                            $returnXML .= '</detail>';
                        }
                    }
                    $returnXML .= '</arrdetail>';
                    $returnXML .= '</data>';
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>Tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>Username atau password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function setRealStuffing($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $xml = explode("#", $xml);
            $error = 0;

            if ($xml != '') {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';


                $totalHDR = count($xml);
                $i = 0;
                $nocont = "";
                while ($i < $totalHDR) {
                    $arrdata = explode("~", $xml[$i]);

                    $NO_CONTAINER = $arrdata[0];
                    $NO_REQUEST = $arrdata[1];
                    $COMMODITY = $arrdata[2];
                    $HZ = $arrdata[3];
                    $CONT_SIZE = $arrdata[4];
                    $CONT_TYPE = $arrdata[5];
                    $TGL_REQUEST = $arrdata[6];
                    $NO_BOOKING = $arrdata[7];
                    $NO_UKK = $arrdata[8];
                    $ID_USER = $arrdata[9];
                    $YBC_ID = $arrdata[10];
                    $TGL_REALISASI = $arrdata[11];
                    $REMARK_SP2 = $arrdata[12];
                    $ALAT = $arrdata[13];
                    $STATUS = "FCL";

                    $returnXML .= '<data>';
                    $returnXML .= '<NO_CONTAINER>' . $NO_CONTAINER . '</NO_CONTAINER>';
                    $returnXML .= '<NO_REQUEST>' . $NO_REQUEST . '</NO_REQUEST>';


                    // $cekexist = DB::connection('uster')->statement("select aktif, tgl_realisasi from container_stuffing where no_request = '$NO_CONTAINER' and no_container = '$NO_REQUEST'");
                    // $rowexist = $cekexist->fetchRow();

                    // if ($rowexist["AKTIF"] == 'T' && $rowexist["TGL_REALISASI"] != NULL) {					
                    // 	$returnXML .= '<KET>EXECUTED</KET>';
                    // 	$returnXML .= '</data>';
                    // 	$i++;
                    // 	continue;
                    // }

                    $rowclosing = 1;

                    if ($rowclosing == 0) {
                        $returnXML .= '<KET>CLOSING_TIME</KET>';
                        $returnXML .= '</data>';
                        $i++;
                        continue;
                    } else {
                        //Cek posisi container GATI, GATO, IN_YARD
                        $query_posisi    = "SELECT LOCATION FROM MASTER_CONTAINER
										WHERE NO_CONTAINER = '$NO_CONTAINER'";
                        $result_posisi    = DB::connection('uster')->selectOne($query_posisi);
                        $row_posisi        = $this->uppercaseKeysObjectToArray($result_posisi);
                        $posisi            = $row_posisi["LOCATION"];

                        if ($posisi == 'IN_YARD') {
                            //cek apakah container tersebut status stuffingnya aktif
                            $query_cek2        = "SELECT COUNT(1) AS JUM FROM CONTAINER_STUFFING WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' AND AKTIF = 'Y'";
                            $result_cek2    = DB::connection('uster')->selectOne($query_cek2);
                            $row_cek2        = $this->uppercaseKeysObjectToArray($result_cek2);
                            if ($row_cek2["JUM"] > 0) {
                                $q_perp = "SELECT STATUS_REQ, STUFFING_DARI FROM REQUEST_STUFFING WHERE NO_REQUEST = '$NO_REQUEST'";
                                $r_prep = DB::connection('uster')->selectOne($q_perp);
                                $rpre = $this->uppercaseKeysObjectToArray($r_prep);
                                $tglrealisasi = substr($TGL_REALISASI, 0, -9);

                                if ($rpre["STATUS_REQ"] == 'PERP') {
                                    $query_cek1    = "SELECT 
				                                        CASE 
				                                        WHEN TO_DATE('" . $tglrealisasi . "','MM/DD/YYYY HH24:MI:SS') <= END_STACK_PNKN THEN 'OK'
				                                        ELSE 'NO'
				                                        END AS STATUS, STATUS_REQ 
				                                FROM CONTAINER_STUFFING
				                                WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
                                } else {
                                    //cek apakah container tersebut masa stuffingnya masih berlaku
                                    if ($REMARK_SP2 == 'Y') {
                                        $query_cek1    = "SELECT 
														CASE 
														WHEN TO_DATE('" . $tglrealisasi . "','MM/DD/YYYY HH24:MI:SS') <= END_STACK_PNKN THEN 'OK'
														ELSE 'NO'
														END AS STATUS, STATUS_REQ  
												FROM CONTAINER_STUFFING
												WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
                                    } else {
                                        $query_cek1    = "SELECT 
														CASE 
														WHEN TO_DATE('" . $tglrealisasi . "','MM/DD/YYYY HH24:MI:SS') <= START_PERP_PNKN THEN 'OK'
														ELSE 'NO'
														END AS STATUS, STATUS_REQ 
												FROM CONTAINER_STUFFING
												WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
                                    }
                                }

                                $result_cek1    = DB::connection('uster')->selectOne($query_cek1);
                                $row_cek1        = $this->uppercaseKeysObjectToArray($result_cek1);
                                $row_perp         = $row_cek1['STATUS_REQ'];

                                $query_cek3        = "SELECT START_PERP_PNKN FROM CONTAINER_STUFFING WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                $result_cek3    = DB::connection('uster')->selectOne($query_cek3);
                                $row_cek3        = $this->uppercaseKeysObjectToArray($result_cek3);
                                $sampai          = $row_cek3["START_PERP_PNKN"];

                                if (strtotime($TGL_REALISASI) <= strtotime($sampai)) {
                                    $status_time = "OK";
                                }

                                $status_time = $row_cek1["STATUS"];

                                if ($status_time == "OK") {
                                    //insert ke container delivery
                                    // mengetahui tanggal start_stack
                                    $query_cek1        = "SELECT tes.NO_REQUEST, 
															CASE SUBSTR(KEGIATAN,9)
																WHEN 'RECEIVING' THEN (SELECT CONCAT('RECEIVING_',a.RECEIVING_DARI) FROM request_receiving a WHERE a.NO_REQUEST = tes.NO_REQUEST)
																ELSE SUBSTR(KEGIATAN,9)
															END KEGIATAN FROM (SELECT TGL_UPDATE, NO_REQUEST,KEGIATAN FROM history_container WHERE no_container = '$NO_CONTAINER' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI')) tes
															WHERE tes.TGL_UPDATE=(SELECT MAX(TGL_UPDATE) FROM history_container WHERE no_container = '$NO_CONTAINER' and kegiatan IN ('REQUEST RECEIVING','REQUEST STRIPPING','REQUEST STUFFING','REQUEST RELOKASI'))";
                                    $result_cek1        = DB::connection('uster')->selectOne($query_cek1);
                                    $row_cek1        = $this->uppercaseKeysObjectToArray($result_cek1);
                                    $no_request        = $row_cek1["NO_REQUEST"];
                                    $kegiatan        = $row_cek1["KEGIATAN"];

                                    if ($kegiatan == 'RECEIVING_LUAR') {
                                        $query_cek1        = "SELECT SUBSTR(TO_CHAR(b.TGL_IN, 'MM/DD/YYYY'),1,10) START_STACK FROM GATE_IN b WHERE b.NO_CONTAINER = '$NO_CONTAINER' AND b.NO_REQUEST = '$NO_REQUEST'";
                                        $result_cek1    = DB::connection('uster')->selectOne($query_cek1);
                                        $row_cek1        = $this->uppercaseKeysObjectToArray($result_cek1);
                                        $start_stack    = $row_cek1["START_STACK"];
                                        $asal_cont         = 'LUAR';
                                    } else if ($kegiatan == 'RECEIVING_TPK') {
                                        $query_cek1        = "SELECT TGL_BONGKAR START_STACK FROM container_receiving WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                        $result_cek1    = DB::connection('uster')->selectOne($query_cek1);
                                        $row_cek1        = $this->uppercaseKeysObjectToArray($result_cek1);
                                        $start_stack    = $row_cek1["START_STACK"];
                                        $asal_cont         = 'TPK';
                                    } else if ($kegiatan == 'STUFFING') {
                                        $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'MM/DD/YYYY'),1,10) START_STACK FROM container_stuffing WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                        $result_cek1    = DB::connection('uster')->selectOne($query_cek1);
                                        $row_cek1        = $this->uppercaseKeysObjectToArray($result_cek1);
                                        $start_stack    = $row_cek1["START_STACK"];
                                        $asal_cont         = 'DEPO';
                                    } else if ($kegiatan == 'STRIPPING') {
                                        $query_cek1        = "SELECT SUBSTR(TO_CHAR(TGL_REALISASI,'MM/DD/YYYY'),1,10) START_STACK FROM container_stripping WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                        $result_cek1    = DB::connection('uster')->selectOne($query_cek1);
                                        $row_cek1        = $this->uppercaseKeysObjectToArray($result_cek1);
                                        $start_stack    = $row_cek1["START_STACK"];
                                        $asal_cont         = 'DEPO';
                                    }

                                    $qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' ORDER BY TGL_UPDATE DESC";
                                    $rbook = DB::connection('uster')->selectOne($qbook);
                                    $rwbook = $this->uppercaseKeysObjectToArray($rbook);
                                    $cur_booking1 = $rwbook["NO_BOOKING"];
                                    $cur_counter1 = $rwbook["COUNTER"];
                                    $tgl_update = $rwbook["TGL_UPDATE"];

                                    $history = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, STATUS_CONT, NO_BOOKING, COUNTER) 
											VALUES ('$NO_CONTAINER','$NO_REQUEST','REALISASI STUFFING', SYSDATE,'$ID_USER','$YBC_ID','$STATUS', '$cur_booking1', $cur_counter1)";

                                    //update status aktif
                                    $query_update = "UPDATE CONTAINER_STUFFING SET AKTIF = 'T', TGL_REALISASI = TO_DATE('" . $TGL_REALISASI . "','MM/DD/YYYY HH24:MI:SS'), ID_USER_REALISASI = '$ID_USER', PEMAKAIAN_ALAT = '$ALAT' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                    $query_update_plan    = "UPDATE PLAN_CONTAINER_STUFFING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = REPLACE('$NO_REQUEST','S','P')";

                                    // $db->startTransaction();
                                    if (DB::connection('uster')->statement($history)) {
                                        if (DB::connection('uster')->statement($query_update)) {

                                            if ($row_perp != 'PERP') {

                                                if (DB::connection('uster')->statement($query_update_plan)) {
                                                    $returnXML .= '<KET>OK</KET>';
                                                    $returnXML .= '</data>';
                                                    $i++;
                                                    continue;
                                                } else {
                                                    $returnXML .= '<KET>gagal update plan stuff</KET>';
                                                    $returnXML .= '</data>';
                                                    $i++;
                                                    continue;
                                                }
                                            } else {
                                                $returnXML .= '<KET>OK1</KET>';
                                                $returnXML .= '</data>';
                                                $i++;
                                                continue;
                                            }
                                        } else {
                                            $returnXML .= '<KET>gagal update cont stuff</KET>';
                                            $returnXML .= '</data>';
                                            $i++;
                                            continue;
                                        }
                                    } else {
                                        $returnXML .= '<KET>gagal insert History</KET>';
                                        $returnXML .= '</data>';
                                        $i++;
                                        continue;
                                    }
                                    // $db->endTransaction();

                                } else {
                                    $returnXML .= '<KET>OVER</KET>';
                                    $returnXML .= '</data>';
                                    $i++;
                                    continue;
                                }
                            } else {
                                $returnXML .= '<KET>NOT_AKTIF</KET>';
                                $returnXML .= '</data>';
                                $i++;
                                continue;
                            }
                        } else {
                            $returnXML .= '<KET>NOT_IN_YARD</KET>';
                            $returnXML .= '</data>';
                            $i++;
                            continue;
                        }
                    }

                    // $returnXML .= '</data>';
                    $i++;
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
        }
        return $returnXML;
    }

    function setRealStripping($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
            $xml = explode("#", $xml);
           
            $error = 0;

            if ($xml != '') {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';

                $totalHDR = count($xml);
                $i = 0;
                $nocont = "";
                while ($i < $totalHDR) {
                    $arrdata = explode("~", $xml[$i]);

                    $NO_CONTAINER = $arrdata[0];
                    $NO_REQUEST = $arrdata[1];
                    $TGL_REQUEST = $arrdata[2];
                    $ID_USER = $arrdata[3];
                    $YBC_ID = $arrdata[4];
                    $TGL_REALISASI = $arrdata[5];
                    $ALAT = $arrdata[6];
                    $MARK = $arrdata[7];
                    $BACKDATE = $arrdata[8];

                    $returnXML .= '<data>';
                    $returnXML .= '<NO_CONTAINER>' . $NO_CONTAINER . '</NO_CONTAINER>';
                    $returnXML .= '<NO_REQUEST>' . $NO_REQUEST . '</NO_REQUEST>';

                    $nota_lunas = DB::connection('uster')->selectOne("select case when lunas = 'YES' THEN 'OK'
				        else 'NO'
				         end  STATUS_NOTA      
				 from nota_stripping where no_request = '$NO_REQUEST'
				  AND STATUS <> 'BATAL'");
                    $rnota_lunas     = $this->uppercaseKeysObjectToArray($nota_lunas);

                    if ($rnota_lunas["STATUS_NOTA"] != 'OK') {
                        $returnXML .= '<KET>NOTA_FAIL</KET>';
                        $returnXML .= '</data>';
                        $i++;
                        continue;
                    }

                    //cek apakah container tersebut status strippingnya aktif
                    $query_cek2        = "SELECT COUNT(1) AS JUM FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' AND AKTIF = 'Y'";
                    $result_cek2    = DB::connection('uster')->selectOne($query_cek2);
                    $row_cek2        = $this->uppercaseKeysForArray($result_cek2);
                    if ($row_cek2["JUM"] <= 0) {
                        $returnXML .= '<KET>NOT_AKTIF</KET>';
                        $returnXML .= '</data>';
                        $i++;
                        continue;
                    }

                    //cek apakah container telah placement
                    $qcekpl = DB::connection('uster')->selectOne("SELECT NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER'");
                    $rcekpl = $this->uppercaseKeysObjectToArray($qcekpl);
                    $no_booking = $rcekpl['NO_BOOKING'];
                    $counter = $rcekpl['COUNTER'];

                    if ($rcekpl['LOCATION'] != 'IN_YARD') {
                        $returnXML .= '<KET>NOT_PLACEMENT</KET>';
                        $returnXML .= '</data>';
                        $i++;
                        continue;
                    } else {
                        //cek apakah container tersebut status strippingnya aktif
                        $query_cek2        = "SELECT COUNT(1) AS JUM FROM CONTAINER_STRIPPING WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' AND AKTIF = 'Y'";
                        $result_cek2    = DB::connection('uster')->selectOne($query_cek2);
                        $row_cek2        = $this->uppercaseKeysObjectToArray($result_cek2);
                        if ($row_cek2["JUM"] > 0) {
                            //cek apakah container perpanjangan 
                            $q_cek_perp = "SELECT STATUS_REQ FROM REQUEST_STRIPPING WHERE NO_REQUEST = '$NO_REQUEST'";
                            $rc_perp = DB::connection('uster')->selectOne($q_cek_perp);
                            $rpep = $this->uppercaseKeysObjectToArray($rc_perp);

                            if ($BACKDATE == 'Y') {
                                $tglrealisasi = substr($TGL_REALISASI, 0, -9);
                                if ($rpep["STATUS_REQ"] == "PERP") {
                                    $query_cek1 = "SELECT END_STACK_PNKN, sysdate, 
														CASE 
														WHEN TO_DATE('" . $tglrealisasi . "','MM/DD/YYYY HH24:MI:SS') <= END_STACK_PNKN THEN 'OK'
														ELSE 'NO'
														END AS STATUS,
														CASE 
														WHEN TO_DATE('" . $tglrealisasi . "','MM/DD/YYYY HH24:MI:SS') = END_STACK_PNKN-1 THEN 'OK'
														ELSE 'NO'
														END AS WARNING
												FROM CONTAINER_STRIPPING
												WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
                                } else {
                                    //cek apakah container tersebut masa strippingnya masih berlaku
                                    $query_cek1    = "SELECT   TGL_BONGKAR+4, sysdate, 
													CASE WHEN CONTAINER_STRIPPING.TGL_SELESAI IS NULL
															THEN    CASE 
																		WHEN TO_DATE('" . $tglrealisasi . "','MM/DD/YYYY HH24:MI:SS') <= TGL_BONGKAR+4 THEN 'OK'
																		ELSE 'NO'
																		END                                                  
															ELSE       CASE 
																		WHEN TO_DATE('" . $tglrealisasi . "','MM/DD/YYYY HH24:MI:SS') <= TGL_SELESAI THEN 'OK'
																		ELSE 'NO'
																		END
														END  STATUS                                     
												FROM CONTAINER_STRIPPING                          
												WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
                                }
                                $result_cek1    = DB::connection('uster')->selectOne($query_cek1);
                                $row_cek1        = $this->uppercaseKeysObjectToArray($result_cek1);

                                if ($row_cek1["STATUS"] == "OK") {
                                    //update status aktif
                                    $query_update        = "UPDATE CONTAINER_STRIPPING SET AKTIF = 'T', TGL_REALISASI = TO_DATE('" . $TGL_REALISASI . "','MM/DD/YYYY HH24:MI:SS'), ID_USER_REALISASI = '$ID_USER', PEMAKAIAN_ALAT = '$ALAT' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                    // $query_update_plan	= "UPDATE PLAN_CONTAINER_STRIPPING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = REPLACE('$NO_REQUEST','S','P')";
                                    if (DB::connection('uster')->statement($query_update)) {
                                        // DB::connection('uster')->statement($query_update_plan);
                                        //update status aktif kartu yang masih Y
                                        $query_update2    = "UPDATE KARTU_STRIPPING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                        DB::connection('uster')->statement($query_update2);

                                        $qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' ORDER BY TGL_UPDATE DESC";
                                        $rbook = DB::connection('uster')->selectOne($qbook);
                                        $rwbook = $this->uppercaseKeysObjectToArray($rbook);
                                        $cur_booking1 = $rwbook["NO_BOOKING"];
                                        $cur_counter1 = $rwbook["COUNTER"];
                                        $tgl_update = $rwbook["TGL_UPDATE"];

                                        $hist = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT) 
											VALUES ('$NO_CONTAINER','$NO_REQUEST','REALISASI STRIPPING',SYSDATE,'$ID_USER','$YBC_ID', '$cur_booking1', '$cur_counter1','MTY')";
                                        DB::connection('uster')->statement($hist);

                                        $returnXML .= '<KET>OK</KET>';
                                        $returnXML .= '</data>';
                                        $i++;
                                    }
                                } else {
                                    $returnXML .= '<KET>OVER</KET>';
                                    $returnXML .= '</data>';
                                    $i++;
                                    continue;
                                }
                            } else { // ini jika tidak backdate
                                if ($rpep["STATUS_REQ"] == "PERP") {
                                    $query_cek1 = "SELECT END_STACK_PNKN, sysdate, 
														CASE 
														WHEN TO_DATE(SYSDATE,'dd/mm/rrrr') <= END_STACK_PNKN THEN 'OK'
														ELSE 'NO'
														END AS STATUS,
														CASE 
														WHEN TO_DATE(SYSDATE,'dd/mm/rrrr') = END_STACK_PNKN-1 THEN 'OK'
														ELSE 'NO'
														END AS WARNING
												FROM CONTAINER_STRIPPING
												WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
                                } else {
                                    //cek apakah container tersebut masa strippingnya masih berlaku
                                    $query_cek1    = "SELECT   TGL_BONGKAR+4, sysdate, 
													CASE WHEN CONTAINER_STRIPPING.TGL_SELESAI IS NULL
															THEN    CASE 
																		WHEN TO_DATE(SYSDATE,'dd/mm/rrrr') <= TGL_BONGKAR+4 THEN 'OK'
																		ELSE 'NO'
																		END                                                  
															ELSE       CASE 
																		WHEN TO_DATE(SYSDATE,'dd/mm/rrrr') <= TGL_SELESAI THEN 'OK'
																		ELSE 'NO'
																		END
														END  STATUS                                     
												FROM CONTAINER_STRIPPING                          
												WHERE NO_REQUEST = '$NO_REQUEST' AND NO_CONTAINER = '$NO_CONTAINER'";
                                }
                                $result_cek1    = DB::connection('uster')->selectOne($query_cek1);
                                $row_cek1        = $this->uppercaseKeysObjectToArray($result_cek1);

                                if ($row_cek1["STATUS"] == "OK") {
                                    //update status aktif
                                    $query_update        = "UPDATE CONTAINER_STRIPPING SET AKTIF = 'T', TGL_REALISASI = SYSDATE, ID_USER_REALISASI = '$ID_USER', PEMAKAIAN_ALAT = '$ALAT' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                    // $query_update_plan	= "UPDATE PLAN_CONTAINER_STRIPPING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = REPLACE('$NO_REQUEST','S','P')";
                                    if (DB::connection('uster')->statement($query_update)) {
                                        // DB::connection('uster')->statement($query_update_plan);
                                        //update status aktif kartu yang masih Y
                                        $query_update2    = "UPDATE KARTU_STRIPPING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                        DB::connection('uster')->statement($query_update2);

                                        $qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' ORDER BY TGL_UPDATE DESC";
                                        $rbook = DB::connection('uster')->selectOne($qbook);
                                        $rwbook = $this->uppercaseKeysObjectToArray($rbook);
                                        $cur_booking1 = $rwbook["NO_BOOKING"];
                                        $cur_counter1 = $rwbook["COUNTER"];
                                        $tgl_update = $rwbook["TGL_UPDATE"];

                                        $hist = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, ID_YARD, NO_BOOKING, COUNTER, STATUS_CONT) 
											VALUES ('$NO_CONTAINER','$NO_REQUEST','REALISASI STRIPPING',SYSDATE,'$ID_USER','$YBC_ID', '$cur_booking1', '$cur_counter1','MTY')";
                                        DB::connection('uster')->statement($hist);

                                        $returnXML .= '<KET>OK</KET>';
                                        $returnXML .= '</data>';
                                        $i++;
                                    }
                                } else {
                                    $returnXML .= '<KET>OVER</KET>';
                                    $returnXML .= '</data>';
                                    $i++;
                                    continue;
                                }
                            }
                        } else {
                            $returnXML .= '<KET>NOT_AKTIF</KET>';
                            $returnXML .= '</data>';
                            $i++;
                            continue;
                        }
                    }
                    // $returnXML .= '</data>';
                    $i++;
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
        }
        return $returnXML;
    }

    function setGateOut($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
            $xml = explode("#", $xml);
           
            $error = 0;

            if ($xml != '') {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';

                $totalHDR = count($xml);
                $i = 0;
                $nocont = "";
                while ($i < $totalHDR) {
                    $arrdata = explode("~", $xml[$i]);


                    $NO_CONTAINER = $arrdata[0];
                    $NO_REQUEST = $arrdata[1];
                    $TGL_REQ_DELIVERY = $arrdata[2];
                    $NOPOL = $arrdata[3];
                    $ID_USER = $arrdata[4];
                    $TGL_OUT = $arrdata[5];
                    $STATUS = $arrdata[6];
                    $NO_SEAL = $arrdata[7];
                    $MARK = $arrdata[8];
                    $GATE_DESTINATION = $arrdata[9];

                    $returnXML .= '<data>';
                    $returnXML .= '<NO_CONTAINER>' . $NO_CONTAINER . '</NO_CONTAINER>';
                    $returnXML .= '<NO_REQUEST>' . $NO_REQUEST . '</NO_REQUEST>';

                    $responStatus = '<STATUS>1</STATUS>';
                    $responStatus .= '<UR_STATUS>OK</UR_STATUS>';

                    $selisih    = "SELECT TRUNC(TO_DATE('$TGL_REQ_DELIVERY','MM/DD/YYYY HH24:MI:SS') - SYSDATE) SELISIH FROM dual";
                    $result_cek    = DB::connection('uster')->selectOne($selisih);
                    $row_cek    = $this->uppercaseKeysObjectToArray($result_cek);
                    $selisih_tgl    = $row_cek["SELISIH"];

                    $qcek_gati = "SELECT COUNT(NO_CONTAINER) AS JUM
							  FROM GATE_OUT
							  WHERE NO_CONTAINER = '$NO_CONTAINER'
							  AND NO_REQUEST = '$NO_REQUEST'";
                    $rcek_gati = DB::connection('uster')->selectOne($qcek_gati);
                    $rwc_gati = $this->uppercaseKeysObjectToArray($rcek_gati);
                    $jum_gati = $rwc_gati["JUM"];
                    if ($jum_gati > 0) {
                        //echo "EXIST_GATO";
                        $responStatus = '<STATUS>0</STATUS>';
                        $responStatus .= '<UR_STATUS>EXIST_GATO</UR_STATUS>';
                        $returnXML .= $responStatus;
                        $returnXML .= '</data>';
                        $i++;
                        continue;
                    }

                    $qcek_gato = "SELECT LOCATION
							  FROM MASTER_CONTAINER
							  WHERE NO_CONTAINER = '$NO_CONTAINER'";
                    $rcek_gato = DB::connection('uster')->selectOne($qcek_gato);
                    $rwc_gato = $this->uppercaseKeysObjectToArray($rcek_gato);
                    $stat_gato = $rwc_gato["LOCATION"];

                    if ($stat_gato == 'GATO') {
                        //echo "EXIST_GATO";
                        $responStatus = '<STATUS>0</STATUS>';
                        $responStatus .= '<UR_STATUS>GATO</UR_STATUS>';
                        $returnXML .= $responStatus;
                        $returnXML .= '</data>';
                        $i++;
                        continue;
                    }

                    //cek request relokasi internal
                    $q_cek_relokasi = "SELECT REQUEST_RELOKASI.NO_REQUEST NOREQ, REQUEST_RELOKASI.* FROM REQUEST_RELOKASI, CONTAINER_RELOKASI WHERE REQUEST_RELOKASI.NO_REQUEST = CONTAINER_RELOKASI.NO_REQUEST 
									AND NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST_DELIVERY = '$NO_REQUEST'";
                    $res_cek = DB::connection('uster')->selectOne($q_cek_relokasi);
                    $row_cek = $this->uppercaseKeysObjectToArray($res_cek);
                    $no_req_relokasi = $row_cek["NOREQ"];

                    if ($row_cek["TIPE_RELOKASI"] == 'INTERNAL') {
                        // $q_insert_lolo = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST)
                        // 				  VALUES('$NO_CONTAINER','DELIVERY','$STATUS',SYSDATE,'LIFT ON','$NO_REQUEST')";
                        // $q_insert_lolo_ = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST)
                        // 			 	  VALUES('$NO_CONTAINER','DELIVERY','$STATUS',SYSDATE,'LIFT OFF','$NO_REQUEST')";

                        $q_insert_lolo = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST)
				 						VALUES('$NO_CONTAINER','DELIVERY','$STATUS',TO_DATE('" . $TGL_OUT . "','MM/DD/YYYY HH24:MI:SS'),'LOLO','$NO_REQUEST')";
                        $q_insert_haulage = "INSERT INTO HANDLING_PIUTANG(NO_CONTAINER, KEGIATAN, STATUS_CONT, TANGGAL, KETERANGAN, NO_REQUEST)
								 		 VALUES('$NO_CONTAINER','DELIVERY','$STATUS',SYSDATE,'HAULAGE','$NO_REQUEST')";
                        DB::connection('uster')->statement($q_insert_lolo);
                        // DB::connection('uster')->statement($q_insert_lolo_);
                        DB::connection('uster')->statement($q_insert_haulage);

                        $query_insert    = "INSERT INTO GATE_OUT( NO_REQUEST, NO_CONTAINER, ID_USER, TGL_IN, NOPOL, STATUS, NO_SEAL, KETERANGAN) VALUES('$NO_REQUEST', '$NO_CONTAINER', '$ID_USER', TO_DATE('" . $TGL_OUT . "','MM/DD/YYYY HH24:MI:SS'), '$NOPOL', '$STATUS','$NO_SEAL','$MARK')";
                        // echo $query_insert;
                        //$id_yard	= $_SESSION["IDYARD_STORAGE"];

                        $q_getcounter1 = "SELECT NO_BOOKING, COUNTER FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' ORDER BY COUNTER DESC";
                        $r_getcounter1 = DB::connection('uster')->selectOne($q_getcounter1);
                        $rw_getcounter1 = $this->uppercaseKeysObjectToArray($r_getcounter1);
                        $cur_booking1  = $rw_getcounter1["NO_BOOKING"];
                        $cur_counter1  = $rw_getcounter1["COUNTER"];

                        $history = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER,  STATUS_CONT, NO_BOOKING, COUNTER) 
								VALUES ('$NO_CONTAINER','$NO_REQUEST','GATE OUT',SYSDATE,'$ID_USER', '$STATUS','$cur_booking1','$cur_counter1')";

                        DB::connection('uster')->statement($history);
                        DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$NO_CONTAINER'");
                        DB::connection('uster')->statement("UPDATE CONTAINER_DELIVERY SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'");
                        DB::connection('uster')->statement("UPDATE CONTAINER_RELOKASI SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$no_req_relokasi'");

                        DB::connection('uster')->statement("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$NO_CONTAINER'");
                        DB::connection('uster')->statement($query_insert);
                    } else {
                        // if ($selisih_tgl < 0) {					
                        // 	//echo "EXPIRED";
                        // 	$responStatus = '<STATUS>0</STATUS>';
                        // 	$responStatus .= '<UR_STATUS>EXPIRED</UR_STATUS>';
                        // 	$returnXML .= $responStatus; 
                        // 	$returnXML .= '</data>';
                        // 	$i++;
                        // 	continue;
                        // } else {
                        $query_insert    = "INSERT INTO GATE_OUT( NO_REQUEST, NO_CONTAINER, ID_USER, TGL_IN, NOPOL, STATUS, NO_SEAL, KETERANGAN, ENTRY_DATE) VALUES('$NO_REQUEST', '$NO_CONTAINER', '$ID_USER', TO_DATE('" . $TGL_OUT . "','MM/DD/YYYY HH24:MI:SS'), '$NOPOL', '$STATUS','$NO_SEAL','$MARK', SYSDATE)";
                        DB::connection('uster')->statement($query_insert);

                        //$id_yard	= $_SESSION["IDYARD_STORAGE"];							
                        $qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE, STATUS_CONT FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                        $rbook = DB::connection('uster')->selectOne($qbook);
                        $rwbook = $this->uppercaseKeysObjectToArray($rbook);
                        $cur_booking1 = $rwbook["NO_BOOKING"];
                        $cur_counter1 = $rwbook["COUNTER"];
                        $tgl_update = $rwbook["TGL_UPDATE"];
                        $status_ = $rwbook["STATUS_CONT"];

                        $history = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, STATUS_CONT, NO_BOOKING, COUNTER) 
									VALUES ('$NO_CONTAINER','$NO_REQUEST','GATE OUT',TO_DATE ('$tgl_update', 'MM/DD/YYYY HH:MI:SS AM'),'$ID_USER', '$status_','$cur_booking1','$cur_counter1')";
                        DB::connection('uster')->statement($history);

                        DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'GATO' WHERE NO_CONTAINER = '$NO_CONTAINER'");
                        DB::connection('uster')->statement("UPDATE CONTAINER_DELIVERY SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'");
                        DB::connection('uster')->statement("UPDATE CONTAINER_RELOKASI SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$no_req_relokasi'");

                        DB::connection('uster')->statement("DELETE FROM PLACEMENT WHERE NO_CONTAINER = '$NO_CONTAINER'");
                        $returnXML .= $responStatus;
                        // }
                    }
                    $returnXML .= '</data>';
                    $i++;
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            } else {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>0</respon>';
                $returnXML .= '<URresponse>tidak ada data</URresponse>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
            $returnXML .= '<document>';
            $returnXML .= '<respon>0</respon>';
            $returnXML .= '<URresponse>password salah</URresponse>';
            $returnXML .= '</document>';
        }
        return $returnXML;
    }

    function setPlacement($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
            $xml = explode("#", $xml);
           
            $error = 0;

            $totalHDR = count($xml);
            if ($xml != '') {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';

                $totalHDR = count($xml);
                $i = 0;
                $nocont = "";
                while ($i < $totalHDR) {

                    $arrdata = explode("~", $xml[$i]);

                    $NO_CONTAINER = $arrdata[0];
                    $NO_REQUEST = $arrdata[1];
                    $BLOCK = $arrdata[2];
                    $SLOT = $arrdata[3];
                    $ROW = $arrdata[4];
                    $TIER = $arrdata[5];
                    $ID_YARD = $arrdata[6];
                    $ID_USER = $arrdata[7];
                    $CONT_STATUS = $arrdata[8];
                    $TGL_PLACEMENT = $arrdata[9];

                    $returnXML .= '<data>';
                    $returnXML .= '<NO_CONTAINER>' . $NO_CONTAINER . '</NO_CONTAINER>';
                    $returnXML .= '<NO_REQUEST>' . $NO_REQUEST . '</NO_REQUEST>';

                    //$q_optrgate = "declare begin PROC_UPD_GATEOPTR('$NO_CONTAINER','$NO_REQUEST'); end;";
                    //DB::connection('uster')->statement($q_optrgate);

                    $query_cek_cont = "SELECT * FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER'";
                    $result_cek_c    = DB::connection('uster')->selectOne($query_cek_cont);
                    $row_cek_c        = $this->uppercaseKeysObjectToArray($result_cek_c);
                    $count_c        = count($row_cek_c);
                    $size            = $row_cek_c['SIZE_'];

                    $query_get_noreq    = "SELECT CONTAINER_RECEIVING.NO_REQUEST, MASTER_CONTAINER.LOCATION
										 FROM  CONTAINER_RECEIVING
										 INNER JOIN MASTER_CONTAINER
												ON CONTAINER_RECEIVING.NO_CONTAINER = MASTER_CONTAINER.NO_CONTAINER
										  WHERE CONTAINER_RECEIVING.NO_CONTAINER = '$NO_CONTAINER' 
											AND CONTAINER_RECEIVING.AKTIF = 'Y'
											AND MASTER_CONTAINER.LOCATION = 'GATI'";
                    $result_noreq        = DB::connection('uster')->selectOne($query_get_noreq);
                    $row_noreq            = $this->uppercaseKeysObjectToArray($result_noreq);
                    $no_req_rec            = $row_noreq["NO_REQUEST"];
                    $location            = $row_noreq["LOCATION"];

                    $query_get_rec    = "SELECT * FROM CONTAINER_RECEIVING WHERE NO_CONTAINER = '$NO_CONTAINER' AND AKTIF = 'Y'";
                    $result_req        = DB::connection('uster')->selectOne($query_get_rec);
                    $row_req        = $this->uppercaseKeysObjectToArray($result_req);
                    $req_rec        = $row_req["NO_REQUEST"];
                    $status            = $row_req["STATUS"];

                    if ($count_c > 0) {
                        if ($location == "GATI" || $location == "IN_YARD") {
                            $query_insert_placement = "INSERT INTO PLACEMENT(NO_CONTAINER, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, USER_NAME, TGL_PLACEMENT, NO_REQUEST_RECEIVING, STATUS) VALUES('$NO_CONTAINER', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('" . $TGL_PLACEMENT . "','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', TO_DATE('" . $TGL_PLACEMENT . "','MM/DD/YYYY HH24:MI:SS'), '$NO_REQUEST', '$CONT_STATUS')";

                            if (DB::connection('uster')->statement($query_insert_placement)) {
                                $query_update_rec    = "UPDATE CONTAINER_RECEIVING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER'";
                                DB::connection('uster')->statement($query_update_rec);

                                $no_req    = $row_req["NO_REQUEST"];
                                $query_insert_history = "INSERT INTO HISTORY_PLACEMENT(NO_CONTAINER, NO_REQUEST, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, NIPP_USER, BAYAR_LOLO,KETERANGAN) VALUES('$NO_CONTAINER', '$NO_REQUEST', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('" . $TGL_PLACEMENT . "','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', 'N', 'PLACEMENT' )";

                                if (DB::connection('uster')->statement($query_insert_history)) {
                                    $query_update_rec    = "UPDATE CONTAINER_RECEIVING SET AKTIF = 'T' WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST'";
                                    DB::connection('uster')->statement($query_update_rec);
                                    DB::connection('uster')->statement("UPDATE MASTER_CONTAINER SET LOCATION = 'IN_YARD' WHERE NO_CONTAINER = '$NO_CONTAINER'");
                                    $returnXML .= '<KET>OK</KET>';
                                }
                            } else {
                                $returnXML .= '<KET>DB ERROR</KET>';
                            }
                        } else if ($location == "IN_YARD") {
                            $returnXML .= '<KET>CONTAINER IN YARD</KET>';
                        } else {
                            if ($row_cek_c['LOCATION'] == 'IN_YARD') {
                                $returnXML .= '<KET>CONTAINER IN YARD</KET>';
                            } else {
                                $returnXML .= '<KET>CONTAINER BELUM GATE IN</KET>';
                            }
                        }
                    } else {
                        $returnXML .= '<KET>TIDAK ADA CONTAINER</KET>';
                    }
                    $returnXML .= '</data>';
                    $i++;
                }

                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
        }
        return $returnXML;
    }

    function setPlacementAll($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $error = 0;
            $arrxml = xml2ary($xml);
            $valResponse = $arrxml['document']['_c']['respon']['_v'];
            if ($valResponse != 0) {
                $returnXML = '<?xml version="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';

                $loop =  $arrxml['document']['_c']['loop']['_c']['data'];
                $totalHDR = count($loop);
                $i = 0;
                $nocont = "";
                while ($i < $totalHDR) {
                    if ($totalHDR == 1) {
                        $data = $loop['_c'];
                    } else {
                        $data = $loop[$i]['_c'];
                    }

                    $NO_CONTAINER = $data['NO_CONTAINER']['_v'];
                    $NO_REQUEST = $data['NO_REQUEST']['_v'];
                    $BLOCK = $data['BLOCK']['_v'];
                    $SLOT = $data['SLOT']['_v'];
                    $ROW = $data['ROW']['_v'];
                    $TIER = $data['TIER']['_v'];
                    $ID_YARD = $data['ID_YARD']['_v'];
                    $ID_USER = $data['ID_USER']['_v'];
                    $CONT_STATUS = $data['CONT_STATUS']['_v'];
                    $TGL_PLACEMENT = $data['TGL_PLACEMENT']['_v'];
                    $TIPE_ACTIVITY = $data['TIPE_ACTIVITY']['_v'];

                    $returnXML .= '<data>';
                    $returnXML .= '<NO_CONTAINER>' . $NO_CONTAINER . '</NO_CONTAINER>';
                    $returnXML .= '<NO_REQUEST>' . $NO_REQUEST . '</NO_REQUEST>';

                    $query_cek_cont = "SELECT * FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER'";
                    $result_cek_c    = DB::connection('uster')->selectOne($query_cek_cont);
                    $row_cek_c        = $this->uppercaseKeysObjectToArray($result_cek_c);
                    $count_c        = count($row_cek_c);
                    $size            = $row_cek_c['SIZE_'];

                    if ($count_c > 0) {
                        // if ($location == "GATI") {
                        //     $query_update    = "UPDATE MASTER_CONTAINER SET LOCATION = 'IN_YARD' WHERE NO_CONTAINER = '$NO_CONTAINER'";
                        //     if (DB::connection('uster')->statement($query_update)) {
                        //         $query_insert_placement = "INSERT INTO PLACEMENT(NO_CONTAINER, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, USER_NAME, TGL_PLACEMENT, STATUS) VALUES('$NO_CONTAINER', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('" . $TGL_PLACEMENT . "','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', TO_DATE('" . $TGL_PLACEMENT . "','MM/DD/YYYY HH24:MI:SS'), '$CONT_STATUS')";
                        //         if (DB::connection('uster')->statement($query_insert_placement)) {
                        //             $query_insert_history = "INSERT INTO HISTORY_PLACEMENT(NO_CONTAINER, NO_REQUEST, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, NIPP_USER, BAYAR_LOLO,KETERANGAN) VALUES('$NO_CONTAINER', '$NO_REQUEST', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('" . $TGL_PLACEMENT . "','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', 'N', 'RELOKASI')";

                        //             if (DB::connection('uster')->statement($query_insert_history)) {
                        //                 $returnXML .= '<KET>OK</KET>';
                        //             }
                        //         } else {
                        //             $returnXML .= '<KET>DB ERROR</KET>';
                        //         }
                        //     }
                        // } else if ($location == "IN_YARD") {
                        //     $query_update    = "UPDATE PLACEMENT SET ID_BLOCKING_AREA = '$BLOCK', SLOT_ = '$SLOT', ROW_ = '$ROW', TIER_ = '$TIER', TGL_UPDATE = SYSDATE WHERE NO_CONTAINER = '$NO_CONTAINER'";
                        //     if (DB::connection('uster')->statement($query_update)) {
                        //         $lolo = 'Y';
                        //         if ($TIPE_ACTIVITY = '3')
                        //             $lolo = 'N';

                        //         $query_insert_history = "INSERT INTO HISTORY_PLACEMENT(NO_CONTAINER, NO_REQUEST, ID_BLOCKING_AREA, SLOT_, ROW_, TIER_, TGL_UPDATE, NIPP_USER, BAYAR_LOLO,KETERANGAN) VALUES('$NO_CONTAINER', '$NO_REQUEST', '$BLOCK', '$SLOT', '$ROW', '$TIER', TO_DATE('" . $TGL_PLACEMENT . "','MM/DD/YYYY HH24:MI:SS'), '$ID_USER', '$lolo', 'RELOKASI')";
                        //         if (DB::connection('uster')->statement($query_insert_history)) {
                        //             $returnXML .= '<KET>OK</KET>';
                        //         }
                        //     } else {
                        //         $returnXML .= '<KET>DB ERROR</KET>';
                        //     }
                        // }
                    } else {
                        $returnXML .= '<KET>TIDAK ADA CONTAINER</KET>';
                    }
                    $returnXML .= '</data>';
                    $i++;
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
        }
        return $returnXML;
    }

    function setGateIn($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
            $xml = explode("#", $xml);

           
            $error = 0;

            if ($xml != '') {
                $returnXML = '<?xml version ="1.0" encoding="UTF-8"?>';
                $returnXML .= '<document>';
                $returnXML .= '<respon>1</respon>';
                $returnXML .= '<loop>';

                $totalHDR = count($xml);
                $i = 0;
                $nocont = "";
                while ($i < $totalHDR) {
                    $arrdata = explode("~", $xml[$i]);

                    $NO_CONTAINER = $arrdata[0];
                    $NO_REQUEST = $arrdata[1];
                    $NOPOL = $arrdata[2];
                    $ID_USER = $arrdata[3];
                    $TGL_IN = $arrdata[4];
                    $STATUS = $arrdata[5];
                    $GATE_ORIGIN = $arrdata[6];

                    $returnXML .= '<data>';
                    $returnXML .= '<NO_CONTAINER>' . $NO_CONTAINER . '</NO_CONTAINER>';
                    $returnXML .= '<NO_REQUEST>' . $NO_REQUEST . '</NO_REQUEST>';
                    //Cek posisi container
                    $query_gati = "SELECT LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER'";
                    $result_gati = DB::connection('uster')->selectOne($query_gati);
                    $row_gati = $this->uppercaseKeysObjectToArray($result_gati);
                    $gati = $row_gati["LOCATION"];
                    if ($gati == "GATI") {
                        $returnXML .= '<KET>EXIST</KET>';
                        $returnXML .= '</data>';
                        $i++;
                        continue;
                    }

                    if ($gati == "IN_YARD") {
                        $returnXML .= '<KET>IN YARD</KET>';
                        $returnXML .= '</data>';
                        $i++;
                        continue;
                    }

                    //Cek asal container
                    if ($GATE_ORIGIN == 'DEPO') { //asal depo
                        $cekGateIn = "SELECT NO_CONTAINER, NO_REQUEST FROM GATE_IN WHERE NO_CONTAINER = '" . $NO_CONTAINER . "' AND NO_REQUEST = '" . $NO_REQUEST . "'";
                        $resultcekGateIn = DB::connection('uster')->statement($cekGateIn);
                        if ($resultcekGateIn) {
                            $query_insert    = "INSERT INTO GATE_IN(NO_CONTAINER, NO_REQUEST,NOPOL, ID_USER, TGL_IN,STATUS) VALUES('" . $NO_CONTAINER . "', '" . $NO_REQUEST . "','" . $NOPOL . "', '" . $ID_USER . "', TO_DATE('" . $TGL_IN . "','MM/DD/YYYY HH24:MI:SS'), '" . $STATUS . "')";
                            $result_insert    = DB::connection('uster')->statement($query_insert);

                            $qbook = "SELECT NO_BOOKING, COUNTER, TO_CHAR (TGL_UPDATE + interval '10' minute, 'MM/DD/YYYY HH:MI:SS AM') TGL_UPDATE, STATUS_CONT FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$NO_CONTAINER' AND NO_REQUEST = '$NO_REQUEST' AND KEGIATAN = 'REQUEST RECEIVING'";
                            $rbook = DB::connection('uster')->selectOne($qbook);
                            $rwbook = $this->uppercaseKeysObjectToArray($rbook);
                            $cur_booking1 = $rwbook["NO_BOOKING"];
                            $cur_counter1 = $rwbook["COUNTER"];
                            $tgl_update = $rwbook["TGL_UPDATE"];
                            $status_ = $rwbook["STATUS_CONT"];

                            // $history  = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, STATUS_CONT, NO_BOOKING, COUNTER) 
                            // 			  VALUES ('$NO_CONTAINER','$NO_REQUEST','GATE IN',TO_DATE('".$TGL_IN."','MM/DD/YYYY HH24:MI:SS'),'$ID_USER','$STATUS','$cur_booking1','$cur_counter1')";
                            $history  = "INSERT INTO history_container(NO_CONTAINER, NO_REQUEST, KEGIATAN, TGL_UPDATE, ID_USER, STATUS_CONT, NO_BOOKING, COUNTER) 
									VALUES ('$NO_CONTAINER','$NO_REQUEST','GATE IN',TO_DATE ('$tgl_update', 'MM/DD/YYYY HH:MI:SS AM'),'$ID_USER','$STATUS','$cur_booking1','$cur_counter1')";
                            DB::connection('uster')->statement($history);

                            //Update status lokasi container, di dalam atau di luar
                            $query_upd    = "UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$NO_CONTAINER'";
                            DB::connection('uster')->statement($query_upd);

                            //hist 
                            DB::connection('uster')->statement("INSERT INTO HIST_LOCATION(NO_CONTAINER, NO_REQUEST, LOCATION) VALUES('$NO_CONTAINER','$NO_REQUEST','GATI')");

                            //Select Nopol
                            $query_nopol = "SELECT NO_TRUCK
								   FROM TRUCK
								   WHERE NO_TRUCK = '$NOPOL'";
                            if (DB::connection('uster')->statement($query_nopol)) {
                                $query_insert_nopol = "UPDATE GATE_IN SET TRUCKING ='PELINDO' WHERE NO_CONTAINER = '$NO_CONTAINER'";
                                $result_insert_nopol    = DB::connection('uster')->statement($query_insert_nopol);
                            }
                            $returnXML .= '<KET>OK</KET>';
                        }
                    } else if ($GATE_ORIGIN == 'TPK') { //asal tpk
                        //Insert data cont ke tabel get in
                        $query_insert    = "INSERT INTO GATE_IN(NO_CONTAINER, NO_REQUEST,NOPOL, ID_USER, TGL_IN) VALUES('$NO_CONTAINER', '$NO_REQUEST','$NOPOL', '$ID_USER', TO_DATE('" . $TGL_IN . "','MM/DD/YYYY HH24:MI:SS'))";
                        $result_insert    = DB::connection('uster')->statement($query_insert);

                        //Update status lokasi container, di dalam atau di luar
                        $query_upd    = "UPDATE MASTER_CONTAINER SET LOCATION = 'GATI' WHERE NO_CONTAINER = '$NO_CONTAINER'";
                        DB::connection('uster')->statement($query_upd);

                        //Insert ke handling piutang					
                        $kegiatan_ = array("LIFT_ON", "HAULAGE", "LIFT_OFF");
                        foreach ($kegiatan_ as $kegiatan) {
                            $query_insert = "INSERT INTO HANDLING_PIUTANG
													(NO_CONTAINER,
													 KEGIATAN,
													 STATUS_CONT,
													 TANGGAL,
													 PENAGIHAN,
													 KETERANGAN
													)
											VALUES	('$NO_CONTAINER',
													 '$kegiatan',
													 (SELECT STATUS 
														FROM CONTAINER_RECEIVING          
														WHERE NO_CONTAINER = '$NO_CONTAINER'
														AND NO_REQUEST ='$NO_REQUEST'  ),
													  SYSDATE,
													  'PELAYARAN',
													  'FAKTOR_YOR'
													)";
                            $result_query_insert = DB::connection('uster')->statement($query_insert);
                        }
                        //Cek Truck Apakh dari pelindo atau tidak
                        $query_nopol = "SELECT NO_TRUCK
								   FROM TRUCK
								   WHERE NO_TRUCK = '$NOPOL'
								";
                        if (DB::connection('uster')->statement($query_nopol)) {
                            $query_insert_nopol = "UPDATE GATE_IN SET TRUCKING ='PELINDO' WHERE NO_CONTAINER = '$NO_CONTAINER'";
                            $result_insert_nopol    = DB::connection('uster')->statement($query_insert_nopol);
                        }
                        $returnXML .= '<KET>OK</KET>';
                    }
                    $returnXML .= '</data>';
                    $i++;
                }
                $returnXML .= '</loop>';
                $returnXML .= '</document>';
            }
        } else {
            $returnXML = '<?xml version="1.0" encoding="UTF-8"?>
						  <document>
								<respon>0</respon>
								<URresponse>password salah</URresponse>
						  </document>';
        }
        return $returnXML;
    }

    function renameContainer($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $error = 0;
            // $arrdata = json_decode($xml);
            $arrData = explode("~", $xml);

            $no_cont_old = $arrData[0];
            $no_cont_new = $arrData[1];
            $size_new = $arrData[2];
            $type_new = $arrData[3];
            //echo $no_cont_old;die();
            //cek ketersediaan kontainer baru
            $q_cek_new = "SELECT NO_CONTAINER, NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont_new'";
            $rcek_new = DB::connection('uster')->selectOne($q_cek_new);
            $rc_new = $this->uppercaseKeysObjectToArray($rcek_new);
            if ($rc_new["NO_CONTAINER"] != NULL) {
                $returnXML = json_encode(array('respon' => 0, 'URresponse' => 'Maaf, No. Container Baru Sudah Ada'));
                return $returnXML;
                die();
            }

            $q_cek = "SELECT NO_CONTAINER, NO_BOOKING, COUNTER, LOCATION FROM MASTER_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
            $rcek = DB::connection('uster')->selectOne($q_cek);
            $rc = $this->uppercaseKeysObjectToArray($rcek);
            $no_cont = $rc["NO_CONTAINER"];
            $no_booking = $rc["NO_BOOKING"];
            $counter = $rc["COUNTER"];
            $location = $rc["LOCATION"];

            if ($no_cont != NULL) {
                $gethistory = "SELECT NO_CONTAINER,NO_REQUEST,KEGIATAN FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
                $rhist = DB::connection('uster')->select($gethistory);
                $rh = $this->convertKeysToUppercase($rhist);
                foreach ($rh as $rha) {
                    $no_req = $rha["NO_REQUEST"];
                    $keg     = $rha["KEGIATAN"];
                    if ($keg == "REQUEST RECEIVING") {
                        $qupdrec = "UPDATE CONTAINER_RECEIVING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdrec);
                    } else if ($keg == "GATE IN") {
                        $qupdgati = "UPDATE GATE_IN SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdgati);
                    } else if ($keg == "GATE OUT") {
                        $qupdgato = "UPDATE GATE_OUT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdgato);
                    } else if ($keg == "BORDER GATE IN" || $keg == "BORDER GATE OUT") {
                        $qupdgatib = "UPDATE BORDER_GATE_IN SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdgatib);
                        $qupdgatob = "UPDATE BORDER_GATE_OUT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdgatob);
                    } else if ($keg == "PERPANJANGAN STRIPPING" || $keg == "REQUEST STRIPPING") {
                        $qupdstrip = "UPDATE CONTAINER_STRIPPING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdstrip);
                    } else if ($keg == "REQUEST STUFFING" || $keg == "PERPANJANGAN STUFFING") {
                        $qupdstuf = "UPDATE CONTAINER_STUFFING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdstuf);
                    } else if ($keg == "PLAN REQUEST STRIPPING") {
                        $qupdplst = "UPDATE PLAN_CONTAINER_STRIPPING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdplst);
                    } else if ($keg == "PLAN REQUEST STUFFING") {
                        $qupdplstf = "UPDATE PLAN_CONTAINER_STUFFING SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdplstf);
                    } else if ($keg == "REQUEST BATALMUAT") {
                        $qupdbm = "UPDATE CONTAINER_BATAL_MUAT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdbm);
                    } else if ($keg == "REQUEST DELIVERY" || $keg == "PERP DELIVERY") {
                        $qupdbm = "UPDATE CONTAINER_DELIVERY SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$no_req' AND NO_CONTAINER = '$no_cont_old'";
                        DB::connection('uster')->statement($qupdbm);
                    }
                }

                $get_history_ = "SELECT NO_REQUEST, NO_CONTAINER, NO_BOOKING FROM HISTORY_CONTAINER WHERE NO_CONTAINER = '$no_cont_old'";
                $exhist        = DB::connection('uster')->select($get_history_);
                $rxhist     = $this->convertKeysToUppercase($exhist);
                foreach ($rxhist as $rxh) {
                    $noreq_hist = $rxh["NO_REQUEST"];
                    $nobook_hist = $rxh["NO_BOOKING"];

                    $update_history = "UPDATE HISTORY_CONTAINER SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST = '$noreq_hist' AND NO_BOOKING = '$nobook_hist' AND NO_CONTAINER = '$no_cont_old'";
                    DB::connection('uster')->statement($update_history);
                }

                $update_master = "INSERT INTO MASTER_CONTAINER (NO_CONTAINER, SIZE_, TYPE_, LOCATION, NO_BOOKING, COUNTER) VALUES ('$no_cont_new', '$size_new' , '$type_new', '$location', '$no_booking', '$counter')";
                DB::connection('uster')->statement($update_master);
                $update_old = "UPDATE MASTER_CONTAINER SET MLO = '-' WHERE NO_CONTAINER = '$no_cont_old'";
                DB::connection('uster')->statement($update_old);

                //update placement
                $cek_placement = "SELECT NO_CONTAINER, NO_REQUEST_RECEIVING FROM PLACEMENT WHERE NO_CONTAINER = '$no_cont_old'";
                $r_cek_placement = DB::connection('uster')->selectOne($cek_placement);
                $rwcek = $this->uppercaseKeysObjectToArray($r_cek_placement);
                $contplace = $rwcek["NO_REQUEST_RECEIVING"];
                DB::connection('uster')->statement("UPDATE PLACEMENT SET NO_CONTAINER = '$no_cont_new' WHERE NO_REQUEST_RECEIVING = '$contplace' AND NO_CONTAINER = '$no_cont_old'");

                //update history placement
                $cek_hplace = "SELECT NO_REQUEST FROM HISTORY_PLACEMENT WHERE NO_CONTAINER = '$no_cont_old'";
                $rhplace = DB::connection('uster')->select($cek_hplace);
                $rhwplace = $this->convertKeysToUppercase($rhplace);
                foreach ($rhwplace as $rhw) {
                    $reqh = $rhw["NO_REQUEST"];
                    $updhsplace = "UPDATE HISTORY_PLACEMENT SET NO_CONTAINER = '$no_cont_new' WHERE NO_CONTAINER = '$no_cont_old'";
                    DB::connection('uster')->statement("");
                }

                $returnXML = json_encode(array('respon' => 1, 'URresponse' => 'Rename Success'));
            } else {
                $returnXML = json_encode(array('respon' => 0, 'URresponse' => 'Failed'));
                return $returnXML;
                die();
            }
        } else {
            $returnXML = json_encode(array('respon' => 0, 'URresponse' => 'password salah'));
        }
        return $returnXML;
    }

    function renameContainerTest($user, $pass, $xml)
    {
        if ($user == 'npks' && $pass == '12345') {
           
            $error = 0;
            $arrData = explode("~", $xml);

            $no_cont_old = $arrData[0];
            $no_cont_new = $arrData[1];
            $size_new = $arrData[2];
            $type_new = $arrData[3];

            $balikan = array('no_cont_old' => $no_cont_old, 'no_cont_new' => $no_cont_new, 'size_new' => $size_new, 'type_new' => $type_new, 'xmlnya' => $xml);

            return json_encode($balikan);
            die();
        } else {
            $returnXML = json_encode(array('respon' => 0, 'URresponse' => 'password salah'));
        }
        return $returnXML;
    }
}
