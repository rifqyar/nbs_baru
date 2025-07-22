<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Billing\NotaDelivery\NotaDeliveryController;
use App\Http\Controllers\Billing\NotaDelivery\NotaDeliveryLuarController;
use App\Http\Controllers\Billing\NotaDelivery\NotaPerpanjanganDeliveryController;
use App\Http\Controllers\Billing\NotaReceiving\NotaReceivingController;
use App\Http\Controllers\Billing\NotaStripping\NotaStrippingController;
use App\Http\Controllers\Billing\NotaStripping\PerpanjanganStrippingController as NotaPerpanjanganStrippingController;
use App\Http\Controllers\Billing\PaymentCash\PaymentCashController;
use App\Http\Controllers\Billing\NotaBatalMuat\NotaBatalController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Request\BatalSPPS\BatalSppsController;
use App\Http\Controllers\Request\Delivery\DeliveryKeLuarController;
use App\Http\Controllers\Request\Delivery\DeliveryKeTpkRepoController;
use App\Http\Controllers\Request\Delivery\PerpanjanganDeliveryKeLuar;
use App\Http\Controllers\Request\Receiving\RequestReceivingController;
use App\Http\Controllers\Request\Stripping\PerencanaanStrippingController;
use App\Http\Controllers\Request\Stripping\PerpanjanganStrippingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Request\Stuffing\PerencanaanStuffingController;
use App\Http\Controllers\Request\Stuffing\PerpanjanganStuffingController;
use App\Http\Controllers\Billing\Stuffing\NotaExtStuffingController;
use App\Http\Controllers\Billing\Stuffing\NotaStuffingController;
use App\Http\Controllers\Billing\TransferSIMKEU\TransferSimkeuController;
use App\Http\Controllers\Koreksi\BatalReceivingController;
use App\Http\Controllers\Koreksi\BatalSp2Controller;
use App\Http\Controllers\Maintenance\CancelOperation\CancelDeliveryController;
use App\Http\Controllers\Maintenance\CancelOperation\CancelReceivingController;
use App\Http\Controllers\Maintenance\CancelOperation\CancelStrippingController;
use App\Http\Controllers\Maintenance\CancelOperation\CancelStuffingController;
use App\Http\Controllers\Maintenance\UsersController;
use App\Http\Controllers\Report\Container\InvoiceController;
use App\Http\Controllers\Report\Container\RequestController;
use App\Http\Controllers\Report\Container\VoyController;
use App\Http\Controllers\Report\ContStuffingByController;
use App\Http\Controllers\Report\DeliveryPerKapalController;
use App\Http\Controllers\Report\ExportCopyYardController;
use App\Http\Controllers\Report\SpssPerKapalController;
use App\Http\Controllers\Report\LaporanHarianController;
use App\Http\Controllers\Report\SharingPenumpukanController;
use App\Http\Controllers\Tca\TcaController;
use App\Http\Controllers\Tca\TcaReportController;
use App\Http\Controllers\Request\BatalSPPS\BatalMuatController;
use App\Http\Controllers\Request\BatalSPPS\BatalStuffingController;
use App\Http\Controllers\Monitoring\HistoryContainerController;
use App\Http\Controllers\Monitoring\ListContainerStatusContoller;
use App\Http\Controllers\Operation\Gate\GateInController;
use App\Http\Controllers\Report\EmateraiController;
use App\Http\Controllers\Operation\Gate\GateOutController;
use App\Http\Controllers\Koreksi\BatalStrippingController;
use FontLib\Table\Type\name;
use App\Http\Controllers\Maintenance\CancelInvoiceController;
use App\Http\Controllers\Maintenance\RenameController;
use App\Http\Controllers\Maintenance\PconnectController;
use App\Http\Controllers\Maintenance\DisableContainerController;
use App\Http\Controllers\Maintenance\SendDeliveryTPKController;
use App\Http\Controllers\Print\KartuRepoController;
use App\Http\Controllers\Print\KartuStrippingController;
use App\Http\Controllers\Print\KartuStuffingController;
use App\Http\Controllers\HerperController;
use App\Http\Controllers\Maintenance\GateAdmin\GateInTpkController;
use App\Http\Controllers\Maintenance\GateAdmin\GateOutTpkController;
use App\Http\Controllers\Maintenance\Master\MasterContainer;
use App\Http\Controllers\Maintenance\Master\MasterTarif;
use App\Http\Controllers\Maintenance\Master\KomponenData;
use App\Http\Controllers\Maintenance\Master\RegisterMLO;
use App\Http\Controllers\Print\KartuStackController;
use App\Http\Controllers\Report\GatePeriodikController;
use App\Http\Controllers\Report\NotaPeriodikController;
use App\Http\Controllers\Report\RealisasiController;
use App\Http\Controllers\Report\StuffingStrippingController;
use App\Http\Controllers\Tca\TcaByCancelationController;
use App\Http\Controllers\Tca\TcaByContainerController;
use App\Http\Controllers\Maintenance\PelangganController;
use App\Http\Controllers\Maintenance\ResendPrayaController;
use App\Http\Controllers\Operation\Gate\UsterGateController;
use App\Http\Controllers\Print\CetakSP2Controller;
use Illuminate\Support\Facades\Http;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth
Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/login-process', [AuthController::class, 'loginProcess']);
Route::get('/comingsoon/{route}', function ($route) {
    $route = base64_decode($route);
    return view('coomingsoon', compact('route'));
})->name('wip');

Route::get('info', function () {
    return phpinfo();
});

Route::group([], function () {
    Route::get('/resend-praya', [ResendPrayaController::class, 'resendPraya'])
        ->name('');

    Route::any('/uster-gate', [UsterGateController::class, 'handleGate'])
        ->name('');

    Route::get('/praya-api', [ResendPrayaController::class, 'checkKoneksiPraya'])
        ->name('');
    // Check koneksi backend praya integration
    // Route::get('/node-api', function () {
    //     $response = Http::get(env('NODE_API_URL') . '/api/hello');
    //     return $response->json();
    // });
});

Route::get('/api/dashboard-data', [HomeController::class, 'getDashboardData'])->name('api.dashboard.data');

Route::middleware(['checkLogin'])->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

Route::group(['prefix' => 'request', 'as' => 'uster.new_request.', 'middleware' => 'checkLogin'], function () {
    // Receiving
    Route::group(['prefix' => 'receiving', 'as' => 'receiving.'], function () {
        Route::get('/', [RequestReceivingController::class, 'index'])->name('receiving_luar');
        Route::post('/data', [RequestReceivingController::class, 'data'])->name('data');
        Route::post('/datatable-cont', [RequestReceivingController::class, 'contList'])->name('datatable-cont');
        Route::get('/overview/{id}', [RequestReceivingController::class, 'overview'])->name('overview');
        Route::get('/view/{id}', [RequestReceivingController::class, 'view'])->name('view');
        Route::post('/add-edit', [RequestReceivingController::class, 'addEdit'])->name('add-edit');
        Route::post('/save-container', [RequestReceivingController::class, 'saveCont'])->name('save-container');
        Route::get('/del-cont/{noCont}/{noReq}', [RequestReceivingController::class, 'delCont'])->name('del-cont');
        Route::get('/add-request', [RequestReceivingController::class, 'addRequest'])->name('add-request');

        // Get Master Data
        Route::get('/data-pbm', [RequestReceivingController::class, 'getDataPBM'])->name('data-pbm');
        Route::get('/data-container', [RequestReceivingController::class, 'getDataContainer'])->name('data-container');
        Route::get('/data-komoditi', [RequestReceivingController::class, 'getDataKomoditi'])->name('data-komoditi');
        Route::get('/data-owner', [RequestReceivingController::class, 'getDataOwner'])->name('data-owner');
        Route::get('/get-contlist/{noReq}', [RequestReceivingController::class, 'getContList'])->name('get-contlist');
    });

    // Stripping
    Route::group(['prefix' => 'stripping', 'as' => 'stripping.'], function () {
        // Perencanaan
        Route::group(['prefix' => 'stripping-plan', 'as' => 'stripping_plan.'], function () {
            Route::get('/', [PerencanaanStrippingController::class, 'index'])->name('awal_tpk');
            Route::post('/data', [PerencanaanStrippingController::class, 'data'])->name('data');
            Route::get('/add-request', [PerencanaanStrippingController::class, 'addRequest'])->name('add');
            Route::get('/overview/{noReq}', [PerencanaanStrippingController::class, 'overview']);
            Route::get('/view/{noReq}', [PerencanaanStrippingController::class, 'view'])->name('view');
            Route::get('/cetak-saldo/{kd_consignee}', [PerencanaanStrippingController::class, 'cetakSaldo']);
            Route::post('/post-praya', [PerencanaanStrippingController::class, 'postPraya'])->name('post-praya');
            Route::post('/save-edit', [PerencanaanStrippingController::class, 'saveEdit'])->name('save-edit');
            Route::post('/save-cont', [PerencanaanStrippingController::class, 'saveCont'])->name('save-cont');
            Route::post('/approve-cont', [PerencanaanStrippingController::class, 'approveCont'])->name('approve-cont');
            Route::post('/save-req', [PerencanaanStrippingController::class, 'saveReq'])->name('save-req');
            Route::get('/delete-cont/{no_cont}/{noReq}/{noReq2}', [PerencanaanStrippingController::class, 'deleteCont'])->name('.delcont');

            // Get master data
            Route::get('/data-pbm', [PerencanaanStrippingController::class, 'getDataPBM'])->name('data-pbm');
            Route::get('/data-kapal', [PerencanaanStrippingController::class, 'getDataKapal'])->name('data-kapal');
            Route::POST('/data-cont', [PerencanaanStrippingController::class, 'getDataCont'])->name('data-cont');
            Route::get('/data-komoditi', [PerencanaanStrippingController::class, 'getDataKomoditi'])->name('data-komoditi');
            Route::get('/data-voyage', [PerencanaanStrippingController::class, 'getDataVoyage'])->name('data-voy');
            Route::get('/cek-saldo-emkl/{idConsignee}', [PerencanaanStrippingController::class, 'cekSaldoEmkl'])->name('cek-saldo');
        });

        // Perpanjangan
        Route::group(['prefix' => 'perpanjangan', 'as' => 'perpanjangan'], function () {
            Route::get('/', [PerpanjanganStrippingController::class, 'index']);
            Route::post('/data', [PerpanjanganStrippingController::class, 'data'])->name('data');
            Route::get('/preview-nota/{noReq}', [PerpanjanganStrippingController::class, 'previewNota'])->name('.preview');
            Route::get('/view/{noReq}', [PerpanjanganStrippingController::class, 'view'])->name('.view');
            Route::post('/add', [PerpanjanganStrippingController::class, 'store'])->name('.add');
            Route::get('/edit/{no_req}', [PerpanjanganStrippingController::class, 'edit'])->name('.edit');
            Route::post('/update', [PerpanjanganStrippingController::class, 'update'])->name('.update');
            Route::get('/delete-cont/{no_cont}/{noReq}', [PerpanjanganStrippingController::class, 'deleteCont'])->name('.delcont');
            Route::post('/approve', [PerpanjanganStrippingController::class, 'approve'])->name('.approve');
        });
    });

    // Batal SPPS
    Route::group(['prefix' => 'batal-spps', 'as' => 'batal_spps'], function () {
        Route::get('/', [BatalSppsController::class, 'index'])->name('');
        Route::get('/add', [BatalSppsController::class, 'add'])->name('.add');
        Route::post('/data', [BatalSppsController::class, 'data'])->name('.data');
        Route::post('/store', [BatalSppsController::class, 'store'])->name('.store');
        Route::get('/data-container', [BatalSppsController::class, 'getContData'])->name('.getContData');
    });


    // Batal Stuffing
    Route::group(['prefix' => 'batal-stuffing', 'as' => 'batal_stuffing'], function () {
        Route::get('/', [BatalStuffingController::class, 'index'])->name('');
        Route::get('/add', [BatalStuffingController::class, 'add'])->name('.add');
        Route::get('/store', [BatalStuffingController::class, 'store'])->name('.store');
        Route::get('/data-container', [BatalStuffingController::class, 'getContData'])->name('.getContData');
    });

    // Receiving
    Route::group(['prefix' => 'receiving', 'as' => 'receiving.'], function () {
        Route::get('/', [RequestReceivingController::class, 'index'])->name('receiving_luar');
        Route::post('/data', [RequestReceivingController::class, 'data'])->name('data');
        Route::get('/overview/{id}', [RequestReceivingController::class, 'overview'])->name('overview');
        Route::get('/view/{id}', [RequestReceivingController::class, 'view'])->name('view');
        Route::post('/add-edit', [RequestReceivingController::class, 'addEdit'])->name('add-edit');
        Route::post('/save-container', [RequestReceivingController::class, 'saveCont'])->name('save-container');
        Route::get('/del-cont/{noCont}/{noReq}', [RequestReceivingController::class, 'delCont'])->name('del-cont');
        Route::get('/add-request', [RequestReceivingController::class, 'addRequest'])->name('add-request');

        // Get Master Data
        Route::get('/data-pbm', [RequestReceivingController::class, 'getDataPBM'])->name('data-pbm');
        Route::get('/data-container', [RequestReceivingController::class, 'getDataContainer'])->name('data-container');
        Route::get('/data-komoditi', [RequestReceivingController::class, 'getDataKomoditi'])->name('data-komoditi');
        Route::get('/data-owner', [RequestReceivingController::class, 'getDataOwner'])->name('data-owner');
        Route::get('/get-contlist/{noReq}', [RequestReceivingController::class, 'getContList'])->name('get-contlist');
    });

    // Stuffing
    Route::group(['prefix' => 'stuffing', 'as' => 'stuffing.'], function () {

        //Stuffing Plan
        Route::group(['prefix' => 'stuffing_plan', 'as' => 'stuffing_plan'], function () {

            Route::get('/', [PerencanaanStuffingController::class, 'index']);

            Route::get('/overview', [PerencanaanStuffingController::class, 'overview'])
                ->name('.overview');

            Route::get('/add', [PerencanaanStuffingController::class, 'add'])
                ->name('.add');

            Route::post('/storeStuffing', [PerencanaanStuffingController::class, 'storeStuffing'])
                ->name('.storeStuffing');

            Route::get('/overview/datatable/{id}', [PerencanaanStuffingController::class, 'listContainer'])
                ->name('.overview.datatable');

            Route::get('/view', [PerencanaanStuffingController::class, 'view'])
                ->name('.view');

            Route::get('/datatable', [PerencanaanStuffingController::class, 'datatable'])
                ->name('.datatable');

            Route::get('/master_vessel_palapa', [PerencanaanStuffingController::class, 'masterVesselPalapa'])
                ->name('.master_vessel_palapa');

            Route::get('/getContainerTPKByName', [PerencanaanStuffingController::class, 'getContainerTPKByName'])
                ->name('.getContainerTPKByName');

            Route::get('/getContainerByName', [PerencanaanStuffingController::class, 'getContainerByName'])
                ->name('.getContainerByName');

            Route::get('/getCommodityByName', [PerencanaanStuffingController::class, 'getCommodityByName'])
                ->name('.getCommodityByName');

            Route::get('/getPmbByName', [PerencanaanStuffingController::class, 'getPmbByName'])
                ->name('.getPmbByName');

            Route::post('/getTanggalStack', [PerencanaanStuffingController::class, 'getTanggalStack'])
                ->name('.getTanggalStack');

            Route::get('/CheckCapacityTPK', [PerencanaanStuffingController::class, 'CheckCapacityTPK'])
                ->name('.CheckCapacityTPK');

            Route::post('/addContainer', [PerencanaanStuffingController::class, 'addContainer'])
                ->name('.addContainer');

            Route::post('/deleteContainer', [PerencanaanStuffingController::class, 'deleteContainer'])
                ->name('.deleteContainer');

            Route::post('/containerApprove', [PerencanaanStuffingController::class, 'containerApprove'])
                ->name('.containerApprove');

            Route::post('/info', [PerencanaanStuffingController::class, 'infoContainerStuffingPlan'])
                ->name('.infoContainerStuffingPlan');
        });

        //Perpanjangan Stuffing
        Route::group(['prefix' => 'perpanjangan', 'as' => 'perpanjangan'], function () {
            Route::get('/', [PerpanjanganStuffingController::class, 'index']);

            Route::get('/datatable', [PerpanjanganStuffingController::class, 'datatable'])
                ->name('.datatable');

            Route::get('/view/datatable/{id}', [PerpanjanganStuffingController::class, 'listContainer'])
                ->name('.view.datatable');

            Route::get('/view', [PerpanjanganStuffingController::class, 'view'])
                ->name('.view');

            Route::post('/info', [PerpanjanganStuffingController::class, 'infoContainerPerpanjanganStuffing'])
                ->name('.infoContainerPerpanjanganStuffing');

            Route::post('/checkClose', [PerpanjanganStuffingController::class, 'checkClose'])
                ->name('.checkClose');

            Route::post('/addContainer', [PerpanjanganStuffingController::class, 'addContainer'])
                ->name('.addContainer');
        });
    });

    //Delivery
    Route::group(['prefix' => 'delivery', 'as' => 'delivery.'], function () {

        //Ke Luar
        Route::group(['prefix' => 'delivery_luar', 'as' => 'delivery_luar.'], function () {
            Route::get('/', [DeliveryKeLuarController::class, 'index'])
                ->name('index');

            Route::get('/datatable', [DeliveryKeLuarController::class, 'dataTables'])
                ->name('datatable');

            Route::get('/view', [DeliveryKeLuarController::class, 'view'])
                ->name('view');

            Route::get('/edit', [DeliveryKeLuarController::class, 'edit'])
                ->name('edit');

            Route::get('/add', [DeliveryKeLuarController::class, 'add'])
                ->name('add');

            Route::get('/edit-datatable', [DeliveryKeLuarController::class, 'editDataTables'])
                ->name('editdatatable');

            Route::post('/delete-datatable', [DeliveryKeLuarController::class, 'deleteEditDataTables'])
                ->name('delcont');

            Route::post('/save-data', [DeliveryKeLuarController::class, 'saveDeliveryLuar'])
                ->name('savedata');

            Route::post('/save-data-edit', [DeliveryKeLuarController::class, 'saveEditDeliveryLuar'])
                ->name('savedataedit');

            Route::post('/update-data-delivery', [DeliveryKeLuarController::class, 'updateDataDelivery'])
                ->name('updatedatadelivery');

            Route::post('/add-cont-datatable', [DeliveryKeLuarController::class, 'addContDeliveryLuar'])
                ->name('addcont');

            Route::get('/get-cont', [DeliveryKeLuarController::class, 'getNoContainer'])
                ->name('getnocontainer');

            Route::post('/get-tgl-stack', [DeliveryKeLuarController::class, 'getTglStack'])
                ->name('gettglstack');

            Route::get('/pbm', [DeliveryKeLuarController::class, 'pbm'])
                ->name('pbm');

            Route::get('/commodity', [DeliveryKeLuarController::class, 'commodity'])
                ->name('commodity');

            Route::get('/master-pelabuhan-palapa', [DeliveryKeLuarController::class, 'masterPelabuhanPalapa'])
                ->name('master_pelabuhan_palapa');

            Route::get('/master-vessel-palapa', [DeliveryKeLuarController::class, 'masterVesselPalapa'])
                ->name('master_vessel_palapa');

            Route::post('/get-tgl-stack-tpk', [DeliveryKeLuarController::class, 'getTglStack2'])
                ->name('get_tgl_stack');
        });

        Route::group(['prefix' => 'perpanjangan_delivery_luar', 'as' => 'perpanjangan_delivery_luar.'], function () {
            Route::get('/', [PerpanjanganDeliveryKeLuar::class, 'index'])
                ->name('index');

            Route::get('/datatable', [PerpanjanganDeliveryKeLuar::class, 'dataTables'])
                ->name('datatable');

            Route::get('/view', [PerpanjanganDeliveryKeLuar::class, 'view'])
                ->name('view');

            Route::get('/edit', [PerpanjanganDeliveryKeLuar::class, 'edit'])
                ->name('edit');

            Route::get('/cont-list', [PerpanjanganDeliveryKeLuar::class, 'contList'])
                ->name('contlist');

            Route::get('/edit-cont-list', [PerpanjanganDeliveryKeLuar::class, 'editContList'])
                ->name('editcontlist');

            Route::post('/update-cont', [PerpanjanganDeliveryKeLuar::class, 'addDo'])
                ->name('updateperpanjangandelivery');

            Route::post('/edit-cont', [PerpanjanganDeliveryKeLuar::class, 'editDo'])
                ->name('editperpanjangandelivery');
        });

        Route::group(['prefix' => 'delivery_ke_luar_tpk', 'as' => 'delivery_ke_luar_tpk.'], function () {
            Route::get('/', [DeliveryKeTpkRepoController::class, 'index'])
                ->name('index');

            Route::get('/datatable', [DeliveryKeTpkRepoController::class, 'dataTables'])
                ->name('datatable');

            Route::get('/view', [DeliveryKeTpkRepoController::class, 'view'])
                ->name('view');

            Route::get('/edit', [DeliveryKeTpkRepoController::class, 'edit'])
                ->name('edit');

            Route::get('/edit-cont-list', [DeliveryKeTpkRepoController::class, 'editContList'])
                ->name('editcontlist');

            Route::post('/cekearly', [DeliveryKeTpkRepoController::class, 'cekEarly'])
                ->name('cekearly');

            Route::post('/edit-cont', [DeliveryKeTpkRepoController::class, 'editDo'])
                ->name('edit_do');

            Route::get('/cont-delivery', [DeliveryKeTpkRepoController::class, 'contDelivery'])
                ->name('contdelivery');

            Route::get('/carrier-praya', [DeliveryKeTpkRepoController::class, 'carrierPraya'])
                ->name('carrier_praya');

            Route::post('/cek-no-cont', [DeliveryKeTpkRepoController::class, 'cekNoCont'])
                ->name('ceknocont');

            Route::post('/add-cont', [DeliveryKeTpkRepoController::class, 'addCont'])
                ->name('addcont');

            Route::post('/delete-container', [DeliveryKeTpkRepoController::class, 'delCont'])
                ->name('delcont');

            Route::get('/pbm', [DeliveryKeTpkRepoController::class, 'pbm'])
                ->name('pbm');

            Route::get('/add', [DeliveryKeTpkRepoController::class, 'add'])
                ->name('add');

            Route::post('/refcal', [DeliveryKeTpkRepoController::class, 'refcal'])
                ->name('refcal');

            Route::post('/add-do-tpk', [DeliveryKeTpkRepoController::class, 'addDoTpk'])
                ->name('add_do_tpk');
        });
    });
});



Route::group(['prefix' => 'billing', 'as' => 'uster.billing.', 'middleware' => 'checkLogin'], function () {

    // Stuffing
    Route::group([], function () {

        //Stuffing Plan
        Route::group(['prefix' => 'stuffing/stuffing_plan', 'as' => 'nota_stuffing'], function () {

            Route::get('/', [NotaStuffingController::class, 'index']);

            Route::get('/datatable', [NotaStuffingController::class, 'datatable'])
                ->name('.datatable');

            Route::get('/print_proforma', [NotaStuffingController::class, 'PrintProforma'])
                ->name('.print_proforma');

            Route::get('/print_proforma_pnkn', [NotaStuffingController::class, 'PrintProformPNKN'])
                ->name('.print_proforma_pnkn');


            Route::get('/print_nota', [NotaStuffingController::class, 'PrintNota'])
                ->name('.print_nota_simple');


            Route::get('/print_nota_pnkn', [NotaStuffingController::class, 'PrintNotaPNKN'])
                ->name('.print_nota_pnkn');

            Route::post('/recalc', [NotaStuffingController::class, 'recalcStuffing'])
                ->name('.recalc_stuffing');

            Route::post('/recalc_pnkn', [NotaStuffingController::class, 'recalcStuffingPNKN'])
                ->name('.recalc_stuffing_pnkn');

            Route::post('/insert_proforma', [NotaStuffingController::class, 'InsertProforma'])
                ->name('.insert_proforma');

            Route::post('/insert_proforma_pnkn', [NotaStuffingController::class, 'InsertProformaPNKN'])
                ->name('.insert_proforma_pnkn');
        });

        //Perpanjangan Stuffing
        Route::group(['prefix' => 'stuffing/ext_stuffing', 'as' => 'nota_ext_pnkn_stuffing'], function () {
            Route::get('/', [NotaExtStuffingController::class, 'index']);

            Route::get('/datatable', [NotaExtStuffingController::class, 'datatable'])
                ->name('.datatable');

            Route::get('/print_proforma', [NotaExtStuffingController::class, 'PrintProforma'])
                ->name('.print_proforma');

            Route::get('/print_nota', [NotaExtStuffingController::class, 'PrintNota'])
                ->name('.print_nota');

            Route::post('/insert_proforma', [NotaExtStuffingController::class, 'InsertProforma'])
                ->name('.insert_proforma');
        });
    });

    Route::group(['prefix' => 'paymentcash', 'as' => 'paymentcash.'], function () {
        Route::get('/', [PaymentCashController::class, 'index'])
            ->name('index');

        Route::get('/datatable', [PaymentCashController::class, 'dataTables'])
            ->name('datatable');

        Route::get('/print', [PaymentCashController::class, 'print'])
            ->name('print');

        Route::get('/print_kode_bayar', [PaymentCashController::class, 'printNotaSap'])
            ->name('print_kode_bayar');

        Route::get('/pay', [PaymentCashController::class, 'pay'])
            ->name('pay');

        Route::get('/paidview', [PaymentCashController::class, 'paidView'])
            ->name('paidview');

        Route::post('/savepaymentpraya', [PaymentCashController::class, 'savePaymentPraya'])
            ->name('savepaymentpraya');
    });
    Route::group(['prefix' => 'notadelivery', 'as' => 'notadelivery.'], function () {
        Route::get('/', [NotaDeliveryLuarController::class, 'index'])
            ->name('index');

        Route::get('/datatable', [NotaDeliveryLuarController::class, 'dataTables'])
            ->name('datatable');

        Route::get('/printnota', [NotaDeliveryLuarController::class, 'printNota'])
            ->name('printnota');

        Route::get('/printnotapnkn', [NotaDeliveryLuarController::class, 'printNotaPnkn'])
            ->name('printnotapnkn');

        Route::get('/printproforma', [NotaDeliveryLuarController::class, 'printProforma'])
            ->name('printproforma');

        Route::get('/printproformapnkn', [NotaDeliveryLuarController::class, 'printProformaPnkn'])
            ->name('printproformapnkn');

        Route::post('/recalc', [NotaDeliveryLuarController::class, 'recalc'])
            ->name('recalc');

        Route::post('/recalcpnkn', [NotaDeliveryLuarController::class, 'recalcPnkn'])
            ->name('recalcpnkn');
    });

    Route::group(['prefix' => 'notaperpanjangandelivery', 'as' => 'notaperpanjangandelivery.'], function () {
        Route::get('/', [NotaPerpanjanganDeliveryController::class, 'index'])
            ->name('index');

        Route::get('/datatable', [NotaPerpanjanganDeliveryController::class, 'dataTables'])
            ->name('datatable');

        Route::get('/printnota', [NotaPerpanjanganDeliveryController::class, 'printNota'])
            ->name('printnota');

        Route::get('/printproforma', [NotaPerpanjanganDeliveryController::class, 'printProforma'])
            ->name('printproforma');

        Route::post('/insertproforma', [NotaPerpanjanganDeliveryController::class, 'insertProforma'])
            ->name('insertproforma');

        // Route::post('/recalcpnkn', [NotaDeliveryLuarController::class, 'recalcPnkn'])
        //     ->name('recalcpnkn');
    });

    Route::group(['prefix' => 'notadeliverytpk', 'as' => 'notadeliverytpk.'], function () {
        Route::get('/', [NotaDeliveryController::class, 'index'])
            ->name('index');

        Route::get('/datatable', [NotaDeliveryController::class, 'dataTables'])
            ->name('datatable');

        Route::get('/printnota', [NotaDeliveryController::class, 'printNota'])
            ->name('printnota');

        Route::get('/printproforma', [NotaDeliveryController::class, 'printProforma'])
            ->name('printproforma');

        Route::get('/insertproforma', [NotaDeliveryController::class, 'insertProforma'])
            ->name('insertproforma');

        Route::post('/recalc', [NotaDeliveryController::class, 'recalc'])
            ->name('recalc');
    });

    // Receiving
    Route::group(['prefix' => 'nota-receiving', 'as' => 'nota_receiving'], function () {
        Route::get('/', [NotaReceivingController::class, 'index']);
        Route::post('/data', [NotaReceivingController::class, 'data'])->name('.data');
        Route::get('/print/print-proforma/{noReq}', [NotaReceivingController::class, 'printProforma'])->name('.print_proforma');
        Route::get('/preview-nota/{noReq}', [NotaReceivingController::class, 'previewNota'])->name('.preview_nota');
        Route::post('/recalculate', [NotaReceivingController::class, 'recalculate'])->name('.recalculate');
        Route::get('/insert-proforma/{noReq}', [NotaReceivingController::class, 'insertProforma'])->name('.insert_proforma');
    });

    //Stripping
    Route::group(['prefix' => 'nota-stripping', 'as' => ''], function () {
        // Nota Stripping
        Route::group(['as' => 'nota_stripping'], function () {
            Route::get('/', [NotaStrippingController::class, 'index'])->name('');
            Route::post('/data', [NotaStrippingController::class, 'data'])->name('.data');
            Route::group(['as' => '.print.', 'prefix' => 'print'], function () {
                Route::get('/print-proforma-strip', [NotaStrippingController::class, 'printProformaStrip'])->name('print_proforma');
                Route::get('/print-proforma-relok', [NotaStrippingController::class, 'printProformaRelok'])->name('print_relok');
                Route::get('/preview-proforma-relok', [NotaStrippingController::class, 'previewProformaRelok'])->name('preview_relok');
                Route::get('/preview-proforma-stripping', [NotaStrippingController::class, 'previewProforma'])->name('preview_proforma');
            });
            Route::get('/insert-proforma-relokmty/{no_req}', [NotaStrippingController::class, 'insertProformaRelokMTY'])->name('.insert_proforma_relokmty');
            Route::get('/insert-proforma-stripping/{no_req}', [NotaStrippingController::class, 'insertProformaStripping'])->name('.insert_proforma_strip');
            Route::post('/recalculate', [NotaStrippingController::class, 'recalculate'])->name('recalc');
            Route::post('/recalculate-pnk', [NotaStrippingController::class, 'recalculatePnk'])->name('recalc_pnk');
        });

        // Perpanjangan Stripping
        Route::group(['prefix' => 'perpanjangan-stripping', 'as' => 'nota_ext_stripping'], function () {
            Route::get('/', [NotaPerpanjanganStrippingController::class, 'index'])->name('');
            Route::post('/data', [NotaPerpanjanganStrippingController::class, 'data'])->name('.data');
            Route::group(['as' => '.print.', 'prefix' => 'print'], function () {
                Route::get('/print-proforma-strip', [NotaPerpanjanganStrippingController::class, 'printProformaStrip'])->name('print_proforma');
                Route::get('/preview-proforma-stripping', [NotaPerpanjanganStrippingController::class, 'previewProforma'])->name('preview_proforma');
            });
            Route::get('/insert-proforma-stripping/{no_req}', [NotaPerpanjanganStrippingController::class, 'insertProformaStripping'])->name('.insert_proforma_strip');
        });
    });

    // Transfer SIMKEU
    Route::group(['prefix' => 'transfer-simkeu', 'as' => 'transfer_simkeu'], function () {
        Route::get('/', [TransferSimkeuController::class, 'index'])->name('');
        Route::get('/get-data', [TransferSimkeuController::class, 'getData'])->name('get-data');
        Route::post('/transfer', [TransferSimkeuController::class, 'transfer'])->name('transfer');
    });

    // NOTA BATAL MUAT
    Route::group(['prefix' => 'nota_batalmuat', 'as' => 'nota_batalmuat'], function () {
        Route::get('/', [NotaBatalController::class, 'index'])->name('');
        Route::get('/datatable', [NotaBatalController::class, 'datatable'])->name('.datatable');
        Route::get('/print/print_proforma', [NotaBatalController::class, 'print_proforma'])->name('.print_proforma');
        Route::get('/print_nota', [NotaBatalController::class, 'print_nota'])->name('.print_nota');
        Route::get('/insert_proforma/{no_req}', [NotaBatalController::class, 'insert_proforma'])->name('.insert_proforma');
    });
});

Route::group(['prefix' => 'monitoring', 'as' => 'uster.', 'middleware' => 'checkLogin'], function () {

    Route::group(['prefix' => 'history_container', 'as' => 'monitoring.'], function () {
        Route::get('/', [HistoryContainerController::class, 'index'])
            ->name('history_container');

        Route::get('/container', [HistoryContainerController::class, 'container'])
            ->name('listContainer');

        Route::post('/getBooking', [HistoryContainerController::class, 'getBooking'])
            ->name('getBooking');

        Route::post('/getLocation', [HistoryContainerController::class, 'getLocation'])
            ->name('getLocation');

        Route::post('/getStatusContainer', [HistoryContainerController::class, 'getStatusContainer'])
            ->name('getStatusContainer');

        Route::get('/ContainerVessel', [HistoryContainerController::class, 'ContainerVessel'])
            ->name('ContainerVessel');
    });
});

Route::group(['prefix' => 'koreksi', 'as' => 'uster.koreksi.', 'middleware' => 'checkLogin'], function () {
    Route::group(['prefix' => 'batalsp2', 'as' => 'batalsp2.'], function () {
        Route::get('/', [BatalSp2Controller::class, 'index'])
            ->name('index');

        Route::get('/get-cont', [BatalSp2Controller::class, 'getNoContainer'])
            ->name('getnocontainer');

        Route::post('/batalsp2cont', [BatalSp2Controller::class, 'batalSp2Cont'])
            ->name('batalsp2cont');
    });
    Route::group(['prefix' => 'batalreceiving', 'as' => 'batalreceiving.'], function () {
        Route::get('/', [BatalReceivingController::class, 'index'])
            ->name('index');
        Route::get('/get-cont', [BatalReceivingController::class, 'getNoContainer'])
            ->name('getnocontainer');

        Route::post('/batalreceivingcont', [BatalReceivingController::class, 'batalReceivingCont'])
            ->name('batalreceivingcont');
    });

    // Batal Mutat
    Route::group(['prefix' => 'batal-muat', 'as' => 'batal_muat'], function () {
        Route::get('/', [BatalMuatController::class, 'index'])->name('');
        Route::get('/add', [BatalMuatController::class, 'add'])->name('.add');
        Route::get('/view', [BatalMuatController::class, 'view'])->name('.view');
        Route::get('/viewContainerByRequest', [BatalMuatController::class, 'viewContainerByRequest'])->name('.viewContainerByRequest');
        Route::get('/datatable', [BatalMuatController::class, 'datatable'])->name('.datatable');
        Route::get('/getPMB', [BatalMuatController::class, 'getPMB'])->name('.getPMB');
        Route::get('/getContainer', [BatalMuatController::class, 'getContainer'])->name('.getContainer');
        Route::post('/prayaGetContainer', [BatalMuatController::class, 'prayaGetContainer'])->name('.prayaGetContainer');
        Route::GET('/getContainerHistory', [BatalMuatController::class, 'getContainerHistory'])->name('.getContainerHistory');
        Route::get('/masterVesselPalapa', [BatalMuatController::class, 'masterVesselPalapa'])->name('.masterVesselPalapa');
        Route::get('/masterPelabuhanPalapa', [BatalMuatController::class, 'masterPelabuhanPalapa'])->name('.masterPelabuhanPalapa');
        Route::post('/validateContainer', [BatalMuatController::class, 'validateContainer'])->name('.validateContainer');
        Route::post('/save_payment_uster_batal_muat', [BatalMuatController::class, 'save_payment_uster_batal_muat'])->name('.save_payment_uster_batal_muat');
        Route::post('/save_bm_praya', [BatalMuatController::class, 'save_bm_praya'])->name('.save_bm_praya');
        Route::post('/get-start-stack', [BatalMuatController::class, 'getStartStack'])->name('.getStarStack');
    });

    // Batal Stripping
    Route::group(['prefix' => 'batal-stripping', 'as' => 'batal_stripping'], function () {
        Route::get('/', [BatalStrippingController::class, 'index'])->name('');
        Route::get('/data-cont', [BatalStrippingController::class, 'dataCont'])->name('.data-cont');
        Route::post('/batal-container', [BatalStrippingController::class, 'batalCont'])->name('.do-batal');
    });

    // Batal Stuffing
    Route::group(['prefix' => 'batal-stuffing', 'as' => 'batal_stuffing'], function () {
        Route::get('/', [BatalStuffingController::class, 'index'])->name('');
        Route::get('/data-cont', [BatalStuffingController::class, 'dataCont'])->name('.data-cont');
        Route::post('/batal-container', [BatalStuffingController::class, 'batalCont'])->name('.do-batal');
    });
});


Route::group(['prefix' => 'maintenance', 'as' => 'uster.maintenance.', 'middleware' => 'checkLogin'], function () {
    Route::group(['prefix' => 'cancel_operation', 'as' => 'cancel_operation.'], function () {
        Route::group(['prefix' => 'delivery', 'as' => 'delivery.'], function () {
            Route::get('/', [CancelDeliveryController::class, 'index'])
                ->name('index');

            Route::get('/datatable', [CancelDeliveryController::class, 'dataTables'])
                ->name('datatable');


            Route::get('/get-cont', [CancelDeliveryController::class, 'getNoContainer'])
                ->name('getnocontainer');

            Route::get('/get-request', [CancelDeliveryController::class, 'getRequest'])
                ->name('getrequest');

            Route::post('/delete-operation', [CancelDeliveryController::class, 'deleteOperation'])
                ->name('deleteoperation');
        });
        Route::group(['prefix' => 'receiving', 'as' => 'receiving.'], function () {
            Route::get('/', [CancelReceivingController::class, 'index'])
                ->name('index');

            Route::get('/datatable', [CancelReceivingController::class, 'dataTables'])
                ->name('datatable');


            Route::get('/get-cont', [CancelReceivingController::class, 'getNoContainer'])
                ->name('getnocontainer');

            Route::get('/get-request', [CancelReceivingController::class, 'getRequest'])
                ->name('getrequest');

            Route::post('/delete-operation', [CancelReceivingController::class, 'deleteOperation'])
                ->name('deleteoperation');
        });
        Route::group(['prefix' => 'stripping', 'as' => 'stripping.'], function () {
            Route::get('/', [CancelStrippingController::class, 'index'])
                ->name('index');

            Route::get('/datatable', [CancelStrippingController::class, 'dataTables'])
                ->name('datatable');


            Route::get('/get-cont', [CancelStrippingController::class, 'getNoContainer'])
                ->name('getnocontainer');

            Route::get('/get-request', [CancelStrippingController::class, 'getRequest'])
                ->name('getrequest');

            Route::post('/delete-operation', [CancelStrippingController::class, 'deleteOperation'])
                ->name('deleteoperation');
        });
        Route::group(['prefix' => 'stuffing', 'as' => 'stuffing.'], function () {
            Route::get('/', [CancelStuffingController::class, 'index'])
                ->name('index');

            Route::get('/datatable', [CancelStuffingController::class, 'dataTables'])
                ->name('datatable');


            Route::get('/get-cont', [CancelStuffingController::class, 'getNoContainer'])
                ->name('getnocontainer');

            Route::get('/get-request', [CancelStuffingController::class, 'getRequest'])
                ->name('getrequest');

            Route::post('/delete-operation', [CancelStuffingController::class, 'deleteOperation'])
                ->name('deleteoperation');
        });
    });
    //uster.maintenance.pconnect
    Route::group(['prefix' => 'pconnect', 'as' => 'pconnect'], function () {
        Route::get('/', [PconnectController::class, 'index'])->name('');
        Route::post('/data', [PconnectController::class, 'data'])->name('.data');
        Route::get('/view/{id}', [PconnectController::class, 'view'])->name('.view');
        Route::post('/update', [PconnectController::class, 'update'])->name('.update');
        Route::post('/insert', [PconnectController::class, 'insert'])->name('.insert');
    });


    Route::group([], function () {

        Route::get('/cancel_invoice', [CancelInvoiceController::class, 'index'])
            ->name('cancel_invoice');


        Route::post('/storeCancel', [CancelInvoiceController::class, 'storeCancel'])
            ->name('storeCancel');
        Route::get('/getNota', [CancelInvoiceController::class, 'getNota'])
            ->name('getNota');
    });

    Route::group([], function () {

        Route::get('/invoiceBackdate', [CancelInvoiceController::class, 'invoiceBackdate'])
            ->name('cancel_invoice_backdate');

        Route::post('/storeCancelBackdate', [CancelInvoiceController::class, 'storeCancelBackdate'])
            ->name('storeCancelBackdate');

        Route::get('/getNotaFaktur', [CancelInvoiceController::class, 'getNotaFaktur'])
            ->name('getNotaFaktur');
    });

    Route::group(['prefix' => 'register_container_mlo', 'as' => 'register_container_mlo'], function () {

        Route::get('/', [RegisterMLO::class, 'index']);
    });

    Route::group([], function () {

        Route::get('/send_delivery_tpk', [SendDeliveryTPKController::class, 'index'])
            ->name('send_delivery_tpk');

        Route::post('/send_delivery_tpk/checklunas', [SendDeliveryTPKController::class, 'checkLunas'])
            ->name('send_delivery_tpk.checklunas');

        Route::post('/save_payment_external', [SendDeliveryTPKController::class, 'savePaymentExternal'])
            ->name('send_delivery_tpk.save_payment_external');
    });

    Route::group([], function () {

        Route::get('/disable_container', [DisableContainerController::class, 'index'])
            ->name('disable_container');

        Route::get('/disable_container/getcontainer', [DisableContainerController::class, 'GetContainer'])
            ->name('disable_container.getcontainer');

        Route::post('/disable_container/store', [DisableContainerController::class, 'disableContainer'])
            ->name('disable_container.store');
    });

    Route::group([['prefix' => 'rename_container', 'as' => 'rename_container']], function () {
        Route::get('/rename_container', [RenameController::class, 'index'])->name('rename_container');
        Route::post('/storeRename', [RenameController::class, 'storeRename'])->name('storeRename');
        Route::get('/rename_container/getContainer', [RenameController::class, 'getContainer'])
            ->name('getContainer');
    });

    Route::group([], function () {

        Route::group(['prefix' => 'master_container', 'as' => 'master_container'], function () {

            Route::get('/', [MasterContainer::class, 'index']);



            Route::get('/datatable', [MasterContainer::class, 'datatable'])
                ->name('.datatable');

            Route::get('/getContainerByID', [MasterContainer::class, 'getContainerByID'])
                ->name('.getContainerByID');

            Route::post('/EditContainer', [MasterContainer::class, 'EditContainer'])
                ->name('.EditContainer');
        });


        Route::group(['prefix' => 'pelanggan', 'as' => 'pelanggan.'], function () {
            Route::get('/', [PelangganController::class, 'index'])->name('index');
            Route::get('/datatable', [PelangganController::class, 'datatable'])->name('datatable');
            Route::get('/checkNpwp', [PelangganController::class, 'checkNpwp'])->name('checkNpwp');
            Route::post('/store', [PelangganController::class, 'store'])->name('store');
            Route::post('/update', [PelangganController::class, 'update'])->name('update');
        });



        Route::group(['prefix' => 'master_container_receiving', 'as' => 'master_container_receiving'], function () {

            Route::get('/', [MasterContainer::class, 'receiving']);


            Route::get('/getRecivingByContainer', [MasterContainer::class, 'getRecivingByContainer'])
                ->name('.getRecivingByContainer');

            Route::post('/EditContainerReceiving', [MasterContainer::class, 'EditContainerReceiving'])
                ->name('.EditContainerReceiving');
        });


        Route::group(['prefix' => 'master_container_receiving', 'as' => 'master_container_receiving'], function () {

            Route::get('/', [MasterContainer::class, 'receiving']);


            Route::get('/getRecivingByContainer', [MasterContainer::class, 'getRecivingByContainer'])
                ->name('.getRecivingByContainer');

            Route::post('/EditContainerReceiving', [MasterContainer::class, 'EditContainerReceiving'])
                ->name('.EditContainerReceiving');
        });



        Route::group(['prefix' => 'master_tarif', 'as' => 'master_tarif'], function () {

            Route::get('/', [MasterTarif::class, 'index']);

            Route::get('/datatable', [MasterTarif::class, 'datatable'])
                ->name('.datatable');

            Route::get('/detail_tarif', [MasterTarif::class, 'detail_tarif'])
                ->name('.detail_tarif');

            Route::get('/detail', [MasterTarif::class, 'detail'])
                ->name('.detail');
        });


        Route::group(['prefix' => 'master_komp_tarif', 'as' => 'master_komp_tarif'], function () {

            Route::get('/', [KomponenData::class, 'index']);

            Route::get('/detail_komp_nota', [KomponenData::class, 'detail_komp_nota'])
                ->name('.detail_komp_nota');

            Route::get('/edit', [KomponenData::class, 'edit'])
                ->name('.edit');
        });
    });

    // Gate Admin
    Route::group(['prefix' => 'gate_admin', 'as' => 'gate_admin.'], function () {
        Route::group(['prefix' => 'gate-in-tpk', 'as' => ''], function () {
            Route::get('/', [GateInTpkController::class, 'index'])
                ->name('gate_in_tpk');

            Route::post('/data-cont', [GateInTpkController::class, 'getDataCont'])
                ->name('');

            Route::post('/add-gatein', [GateInTpkController::class, 'addGateIn'])
                ->name('');
        });

        Route::group(['prefix' => 'gate-out-tpk', 'as' => ''], function () {
            Route::get('/', [GateOutTpkController::class, 'index'])
                ->name('gate_out_tpk');

            Route::post('/data-cont', [GateOutTpkController::class, 'getDataCont'])
                ->name('');

            Route::post('/add-gatein', [GateOutTpkController::class, 'addGateOut'])
                ->name('');
        });
    });

    // Log Praya Integration
    // Route::group(['prefix' => 'log-praya', 'as' => 'log-praya.'], function () {
    //     Route::get('/log-praya', function(){
    //         dd('test');
    //         return Redirect(route('logs', ['l' => 'praya.log']));
    //     });
    // });
});

Route::group(['prefix' => 'report', 'as' => 'uster.report.', 'middleware' => 'checkLogin'], function () {
    Route::group(['prefix' => 'spps', 'as' => 'spps.'], function () {
        Route::get('/', [SpssPerKapalController::class, 'index'])
            ->name('index');

        Route::get('/master-vessel', [SpssPerKapalController::class, 'masterVessel'])
            ->name('mastervessel');

        Route::post('/datatable', [SpssPerKapalController::class, 'dataTables'])
            ->name('datatable');

        Route::get('/generate-excel', [SpssPerKapalController::class, 'generateExcel'])
            ->name('generateexcel');
    });

    Route::group(['prefix' => 'contstuffingby', 'as' => 'contstuffingby.'], function () {
        Route::get('/', [ContStuffingByController::class, 'index'])
            ->name('index');

        Route::post('/datatable', [ContStuffingByController::class, 'dataTables'])
            ->name('datatable');

        Route::get('/generate-excel', [ContStuffingByController::class, 'generateExcel'])
            ->name('generateexcel');

        Route::get('/generate-pdf', [ContStuffingByController::class, 'generatePdf'])
            ->name('generatepdf');
    });

    Route::group(['prefix' => 'container', 'as' => 'container.'], function () {
        Route::group(['prefix' => 'invoice', 'as' => 'invoice.'], function () {
            Route::get('/', [InvoiceController::class, 'index'])
                ->name('index');

            Route::post('/datatable', [InvoiceController::class, 'dataTables'])
                ->name('datatable');

            Route::get('/nota', [InvoiceController::class, 'nota'])
                ->name('nota');

            Route::get('/generate-excel', [InvoiceController::class, 'generateExcel'])
                ->name('generateexcel');

            Route::get('/generate-pdf', [InvoiceController::class, 'generatePdf'])
                ->name('generatepdf');
        });
        Route::group(['prefix' => 'request', 'as' => 'request.'], function () {
            Route::get('/', [RequestController::class, 'index'])
                ->name('index');

            Route::post('/datatable', [RequestController::class, 'dataTables'])
                ->name('datatable');

            Route::get('/nota', [RequestController::class, 'nota'])
                ->name('nota');

            Route::get('/generate-excel', [RequestController::class, 'generateExcel'])
                ->name('generateexcel');

            Route::get('/generate-pdf', [RequestController::class, 'generatePdf'])
                ->name('generatepdf');
        });
        Route::group(['prefix' => 'area', 'as' => 'area.'], function () {
            Route::get('/', [VoyController::class, 'index'])
                ->name('index');

            Route::post('/datatable', [VoyController::class, 'dataTables'])
                ->name('datatable');

            Route::get('/generate-excel', [VoyController::class, 'generateExcel'])
                ->name('generateexcel');
        });
    });

    Route::group(['prefix' => 'exportcopyyard', 'as' => 'exportcopyyard.'], function () {
        Route::get('/', [ExportCopyYardController::class, 'index'])
            ->name('index');

        Route::post('/read-excel', [ExportCopyYardController::class, 'readExcel'])
            ->name('readexcel');
    });

    Route::group(['prefix' => 'deliveryperkapal', 'as' => 'deliveryperkapal.'], function () {
        Route::get('/', [DeliveryPerKapalController::class, 'index'])
            ->name('index');

        Route::post('/datatable', [DeliveryPerKapalController::class, 'dataTables'])
            ->name('datatable');

        Route::get('/master-vessel', [DeliveryPerKapalController::class, 'masterVessel'])
            ->name('mastervessel');

        Route::get('/generate-excel', [DeliveryPerKapalController::class, 'generateExcel'])
            ->name('generateexcel');

        Route::get('/generate-pdf', [DeliveryPerKapalController::class, 'generatePdf'])
            ->name('generatepdf');
    });

    Route::group(['prefix' => 'report_materai', 'as' => 'report_materai'], function () {
        Route::get('/', [EmateraiController::class, 'index']);

        Route::get('/report', [EmateraiController::class, 'report'])->name('.report');
    });

    Route::group(['prefix' => 'nota-periodik', 'as' => 'nota_per_periodik'], function () {
        Route::get('/', [NotaPeriodikController::class, 'index'])->name('');
        Route::get('/generate-nota', [NotaPeriodikController::class, 'generateNota'])->name('.generateNota');
        Route::get('/export-nota', [NotaPeriodikController::class, 'exportNota'])->name('.exportNota');
    });

    Route::group(['prefix' => 'gate-periodik', 'as' => 'gate_per_periodik'], function () {
        Route::get('/', [GatePeriodikController::class, 'index'])->name('');
        Route::get('/master-vessel', [GatePeriodikController::class, 'masterVessel'])->name('.masterVessel');
        Route::get('/generate-nota', [GatePeriodikController::class, 'generateNota'])->name('.generateNota');
        Route::get('/export-nota', [GatePeriodikController::class, 'exportNota'])->name('.exportNota');
    });

    Route::group(['prefix' => 'approval-stuffing-stripping', 'as' => 'approval_stuffing_stripping'], function () {
        Route::get('/', [StuffingStrippingController::class, 'index'])->name('');
        Route::get('/generate-nota', [StuffingStrippingController::class, 'generateNota'])->name('.generateNota');
        Route::get('/export-nota', [StuffingStrippingController::class, 'exportNota'])->name('.exportNota');
    });

    Route::group(['prefix' => 'realisasi', 'as' => 'report_realisasi'], function () {
        Route::get('/', [RealisasiController::class, 'index'])->name('');
        Route::get('/generate-nota', [RealisasiController::class, 'generateNota'])->name('.generateNota');
        Route::get('/export-nota', [RealisasiController::class, 'exportNota'])->name('.exportNota');
    });

    Route::group(['prefix' => 'report_materai', 'as' => 'report_materai'], function () {
        Route::get('/', [EmateraiController::class, 'index']);

        Route::get('/report', [EmateraiController::class, 'report'])->name('.report');
    });

    Route::group(['prefix' => 'laporan_harian', 'as' => 'laporan_harian'], function () {
        Route::get('/', [LaporanHarianController::class, 'index']);
        Route::post('/datatable', [LaporanHarianController::class, 'dataTables'])->name('.datatable');
        Route::get('/report', [LaporanHarianController::class, 'report'])->name('.report');
        Route::post('/generatenosp', [LaporanHarianController::class, 'generatenosp'])->name('.generatenosp');
    });
    Route::group(['prefix' => 'sharing_penumpukan', 'as' => 'sharing_penumpukan'], function () {
        Route::get('/', [SharingPenumpukanController::class, 'index']);
        Route::post('/datatable', [SharingPenumpukanController::class, 'dataTable'])->name('.datatable');
        Route::get('/report', [SharingPenumpukanController::class, 'report'])->name('.report');
    });
});

Route::group(['prefix' => 'monitoring', 'as' => 'uster.', 'middleware' => 'checkLogin'], function () {

    Route::group(['prefix' => 'history_container', 'as' => 'monitoring.'], function () {
        Route::get('/', [HistoryContainerController::class, 'index'])
            ->name('history_container');

        Route::get('/container', [HistoryContainerController::class, 'container'])
            ->name('listContainer');

        Route::post('/getBooking', [HistoryContainerController::class, 'getBooking'])
            ->name('getBooking');

        Route::post('/getLocation', [HistoryContainerController::class, 'getLocation'])
            ->name('getLocation');

        Route::post('/getStatusContainer', [HistoryContainerController::class, 'getStatusContainer'])
            ->name('getStatusContainer');

        Route::get('/ContainerVessel', [HistoryContainerController::class, 'ContainerVessel'])
            ->name('ContainerVessel');

        Route::get('/getDetail', [HistoryContainerController::class, 'getDetail'])
            ->name('getDetail');
    });

    Route::group(['prefix' => 'list_cont_bystatus', 'as' => 'monitoring.'], function () {
        Route::get('/', [ListContainerStatusContoller::class, 'index'])
            ->name('list_cont_bystatus');

        Route::get('/list', [ListContainerStatusContoller::class, 'listContainerStatus'])
            ->name('listdata');

        Route::get('/toExcel', [ListContainerStatusContoller::class, 'toExcel'])
            ->name('toExcel');
    });
});


Route::group(['prefix' => 'operation', 'as' => 'uster.operation.', 'middleware' => 'checkLogin'], function () {

    Route::group(['prefix' => 'gate', 'as' => 'gate.'], function () {

        Route::get('/gate_in', [GateInController::class, 'index'])
            ->name('gate_in');


        Route::post('/gate_in', [GateInController::class, 'addGateIn'])
            ->name('Storegate_in');

        Route::get('/gate_in/getContainer', [GateInController::class, 'getContainer'])
            ->name('gate_in.getContainer');

        Route::get('/gate_out', [GateOutController::class, 'index'])
            ->name('gate_out');

        Route::post('/gate-out', [GateOutController::class, 'addGateOut'])
            ->name('Storegate-out');

        Route::get('/gate_out/getContainer', [GateOutController::class, 'getContainer'])
            ->name('gate_out.getContainer');
    });
});

Route::group(['prefix' => 'tca', 'as' => 'uster.tca.', 'middleware' => 'checkLogin'], function () {
    Route::group(['prefix' => 'tidcontainer', 'as' => 'tidcontainer.'], function () {
        Route::get('/', [TcaController::class, 'index'])
            ->name('index');

        Route::post('/datatable', [TcaController::class, 'dataTables'])
            ->name('datatable');

        Route::post('/save-associate-praya', [TcaController::class, 'saveAssociatePraya'])
            ->name('saveassociatepraya');

        Route::get('get-truck-list', [TcaController::class, 'getTruckList'])
            ->name('gettrucklist');

        Route::get('invoice-number-praya', [TcaController::class, 'invoiceNumberPraya'])
            ->name('invoicenumberpraya');
    });

    // Route::group(['prefix' => 'report', 'as' => 'report.'], function () {
    //     Route::get('/', [TcaReportController::class, 'index'])
    //         ->name('index');

    //     Route::post('/datatable', [TcaReportController::class, 'dataTables'])
    //         ->name('datatable');

    //     Route::get('/master-vessel', [TcaReportController::class, 'masterVessel'])
    //         ->name('mastervessel');

    //     Route::get('/generate-excel', [TcaReportController::class, 'generateExcel'])
    //         ->name('generateexcel');

    //     Route::get('/generate-pdf', [TcaReportController::class, 'generatePdf'])
    //         ->name('generatepdf');
    // });



});

Route::group(['prefix' => 'print', 'as' => 'uster.print.', 'middleware' => 'checkLogin'], function () {

    Route::group(['prefix' => 'stripping', 'as' => 'stripping.'], function () {

        Route::get('/spp_stripping', [KartuStrippingController::class, 'KartuStripping'])
            ->name('spp_stripping');

        Route::get('/GetSSPStripping', [KartuStrippingController::class, 'GetSSPStripping'])
            ->name('GetSSPStripping');

        Route::get('/Cetak', [KartuStrippingController::class, 'Cetak'])
            ->name('Cetak');

        Route::get('/CetakSPK', [KartuStrippingController::class, 'CetakSPK'])
            ->name('CetakSPK');

        Route::get('/Cetakan', [KartuStrippingController::class, 'Cetakan'])
            ->name('Cetakan');


        Route::post('/ShowContainer', [KartuStrippingController::class, 'ShowContainer'])
            ->name('ShowContainer');

        #------

        Route::get('/trucking', [KartuStrippingController::class, 'KartuTruck'])
            ->name('trucking');

        Route::get('/GetTruckStripping', [KartuStrippingController::class, 'GetTruckStripping'])
            ->name('GetTruckStripping');
    });

    Route::group(['prefix' => 'stuffing', 'as' => 'stuffing.'], function () {
        Route::get('/spp_stuffing', [KartuStuffingController::class, 'KartuStuffing'])
            ->name('spp_stuffing');

        Route::get('/GetSSPStuffing', [KartuStuffingController::class, 'GetSSPStuffing'])
            ->name('GetSSPStuffing');

        Route::get('/Cetak', [KartuStuffingController::class, 'Cetak'])
            ->name('Cetak');

        Route::get('/CetakSPK', [KartuStuffingController::class, 'CetakSPK'])
            ->name('CetakSPK');

        Route::get('/Cetakan', [KartuStuffingController::class, 'Cetakan'])
            ->name('Cetakan');


        Route::post('/ShowContainer', [KartuStuffingController::class, 'ShowContainer'])
            ->name('ShowContainer');

        Route::get('/trucking', [KartuStuffingController::class, 'KartuTruck'])
            ->name('trucking');

        Route::get('/GetTruckStuffing', [KartuStuffingController::class, 'GetTruckStuffing'])
            ->name('GetTruckStuffing');
    });

    Route::group([], function () {
        Route::get('/rec_card_repo', [KartuRepoController::class, 'KartuRepo'])
            ->name('rec_card_repo');

        Route::get('/GetCardRepo', [KartuRepoController::class, 'GetCardRepo'])
            ->name('GetCardRepo');

        Route::get('/GetPrayaNota', [KartuRepoController::class, 'GetPrayaNota'])
            ->name('GetPrayaNota');
    });

    Route::group(['prefix' => 'kartu-merah', 'as' => 'kartu_merah'], function () {
        Route::get('/', [KartuStackController::class, 'index'])->name('');
        Route::post('/data', [KartuStackController::class, 'data'])->name('.data');
        Route::get('/print', [KartuStackController::class, 'print'])->name('.print');
    });

    Route::group(['prefix' => 'sp2', 'as' => 'sp2'], function () {
        Route::get('/', [CetakSP2Controller::class, 'index'])->name('');
        Route::post('/data', [CetakSP2Controller::class, 'data'])->name('.data');
        Route::get('/print', [CetakSP2Controller::class, 'print'])->name('.print');
    });
});

Route::group(['prefix' => 'maintenance', 'as' => 'maintenance.', 'middleware' => 'checkLogin'], function () {
    Route::get('/changepasswd', [UsersController::class, 'index'])->name('changepasswd/');

    Route::post('/editpassword', [UsersController::class, 'resetPassword'])->name('changepasswdStore');
});


Route::group(['prefix' => 'tca_cancel', 'as' => 'maintenance.tca'], function () {
    Route::get('/', [TcaByCancelationController::class, 'index'])->name('/tca_cancel');
});

Route::group(['prefix' => 'tca_percontainer', 'as' => 'maintenance.tca'], function () {
    Route::get('/', [TcaByContainerController::class, 'index'])->name('/tca_percontainer');
    Route::get('/invoice_number_praya', [TcaByContainerController::class, 'getInvoiceNumbers'])->name('.invoice_number_praya');
    Route::get('/list_by_cont_praya', [TcaByContainerController::class, 'list_by_cont_praya'])->name('.list_by_cont_praya');
    Route::get('/get_truck_list', [TcaByContainerController::class, 'get_truck_list'])->name('.get_truck_list');
    Route::get('/save_associate_praya ', [TcaByContainerController::class, 'save_associate_praya '])->name('.save_associate_praya ');
});


Route::group(['prefix' => 'helper', 'as' => 'uster.helper.', 'middleware' => 'checkLogin'], function () {


    Route::get('/getBarcode', [HerperController::class, 'getBarcode'])
        ->name('getBarcode');
});
