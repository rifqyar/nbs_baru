<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

function generateQueryEdit(array $data)
{
    $query = '';
    foreach ($data as $key => $value) {
        if($value == null || $value == '' || $value == 'null'){
            $query .= "$key = null, ";
        } else if($value == 'SYSDATE') {
            $query .= "SYSDATE, ";
        } else {
            if(str_contains($value, '@ORA')){
                $value = str_replace('@ORA', '', $value);
                $query .= "$key = $value, ";
            }else {
                $query .= "$key = '$value', ";
            }
        }
    }

    $query = rtrim($query, ', ');
    return $query;
}

function generateQuerySimpan(array $data)
{
    $query = '(';
    foreach ($data as $key => $value) {
        $query .= $key.',';
    }

    $query = rtrim($query, ',');
    $query .= ') VALUES (';

    foreach ($data as $key => $value) {
        if($value == null || $value == '' || $value == 'null'){
            $query .= "null,";
        } else if($value == 'SYSDATE') {
            $query .= "SYSDATE,";
        } else {
            if(str_contains($value, '@ORA')){
                $value = str_replace('@ORA', '', $value);
                $query .= "$value,";
            }else {
                $query .= "'$value',";
            }
        }
    }

    $query = rtrim($query, ',');
    $query .= ')';

    return $query;
}

function generateNoReqReceiving()
{
    $query_cek	= "SELECT LPAD(NVL(MAX(SUBSTR(NO_REQUEST,8,13)),0)+1,6,0) AS JUM ,
							  TO_CHAR(SYSDATE, 'MM') AS MONTH,
							  TO_CHAR(SYSDATE, 'YY') AS YEAR
                      FROM REQUEST_RECEIVING
                      WHERE TGL_REQUEST BETWEEN TRUNC(SYSDATE,'MONTH') AND LAST_DAY(SYSDATE) ";

	$result_cek = DB::connection('uster')->selectOne($query_cek);
	$jum		= $result_cek->jum;
	$month		= $result_cek->month;
	$year		= $result_cek->year;

	$no_req_rec	= "REC".$month.$year.$jum;

    return $no_req_rec;
}
