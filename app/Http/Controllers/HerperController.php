<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorPNG;

class HerperController extends Controller
{
    function getBarcode(Request $request){
        $generator = new BarcodeGeneratorPNG();
        $value = $request->input('value');
        $barcode = $generator->getBarcode($value, $generator::TYPE_CODE_128);
        return response($barcode)->header('Content-Type', 'image/png');
    }
}
