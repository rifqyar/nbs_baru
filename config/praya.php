<?php
return [
    //Praya Config
    'host' => env('PRAYA_HOST', 'https://praya.ilcs.co.id'),
    'api_login' => env('PRAYA_API_LOGIN', env('PRAYA_HOST') . ':8016'),
    'api_master' => env('PRAYA_API_MASTER', env('PRAYA_HOST') . ':8001'),
    'api_receiving' => env('PRAYA_API_RECEIVING', env('PRAYA_HOST') . ':8003'),
    'api_delivery' => env('PRAYA_API_DELIVERY', env('PRAYA_HOST') . ':8004'),
    'api_proforma' => env('PRAYA_API_PROFORMA', env('PRAYA_HOST') . ':8007'),
    'api_loadingcancel' => env('PRAYA_API_LOADINGCANCEL', env('PRAYA_HOST') . ':8008'),
    'api_payment' => env('PRAYA_API_PAYMENT', env('PRAYA_HOST') . ':8011'),
    'api_tos' => env('PRAYA_API_TOS', env('PRAYA_HOST') . ':8013'),
    'api_integration' => env('PRAYA_API_INTEGRATION', env('PRAYA_HOST') . ':8020'),
    
    'itpk_pnk_terminal_id' => env('PRAYA_ITPK_PNK_TERMINAL_ID', 622),
    'itpk_pnk_port_code' => env('PRAYA_ITPK_PNK_PORT_CODE', 'IDPNK'),
    'itpk_pnk_org_id' => env('PRAYA_ITPK_PNK_ORG_ID', 2),
    'itpk_pnk_org_code' => env('PRAYA_ITPK_PNK_ORG_CODE', 'ITPK'),
    'itpk_pnk_branch_id' => env('PRAYA_ITPK_PNK_BRANCH_ID', 63),
    'itpk_pnk_branch_code' => env('PRAYA_ITPK_PNK_BRANCH_CODE', '05'),
    'itpk_pnk_terminal_code' => env('PRAYA_ITPK_PNK_TERMINAL_CODE', 'PNK'),
    'itpk_pnk_area_code' => env('PRAYA_ITPK_PNK_AREA_CODE', 1827),
];