<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('home');
});

Auth::routes(['verify' => true]);

/*
|--------------------------------------------------------------------------
| Authenticated & Verified Routes
|--------------------------------------------------------------------------
*/
Route::group(['middleware' => ['auth', 'verified']], function () {

    /*
    |------------------------------------------------------------------
    | PETTY CASH
    |------------------------------------------------------------------
    */
    Route::resource('entertainment', 'EntertainmentController');


    /*
    | Dashboard
    */
    Route::get('/home', 'HomeController@index')->name('home');

    /*
    | UI Components
    */
    Route::view('/components', 'components')->name('components');

    /*
    | User & Access Management
    */
    Route::resource('users', 'UserController');
    Route::get('/profile/{user}', 'UserController@profile')->name('profile.edit');
    Route::post('/profile/{user}', 'UserController@profileUpdate')->name('profile.update');

    Route::resource('roles', 'RoleController')->except('show');
    Route::resource('permissions', 'PermissionController')->except(['show','destroy','update']);

    /*
    | Master Data
    */
    Route::get('/project/maintenance', 'ProjectController@maintenance')
        ->name('project.maintenance');

    Route::get('/project/maintenance/preview-reminder', 'ProjectController@previewMaintenanceReminder')
        ->name('project.maintenance.preview-reminder');

    Route::post('/project/maintenance/send-reminder', 'ProjectController@sendMaintenanceReminder')
        ->name('project.maintenance.send-reminder');

    Route::get('/project/report-monitoring', 'ProjectController@reportMonitoring')
        ->name('project.report.monitoring');

    Route::post('/project/report-monitoring/update', 'ProjectController@reportMonitoringUpdate')
        ->name('project.report.monitoring.update');

    Route::get('/project/monitoring-ba', 'ProjectController@monitoringBA')->name('project.ba.monitoring');
    Route::get('/project/monitoring-ba/export/pdf', 'ProjectController@exportBAPdf')->name('project.ba.export.pdf');
    Route::get('/project/monitoring-ba/export/excel', 'ProjectController@exportBAExcel')->name('project.ba.export.excel');

    Route::resource('category', 'CategoryController')->except('show');
    Route::resource('customer', 'MCustomerController')->except('show');
    Route::resource('sales', 'MSalesController')->except('show');
    Route::resource('post', 'PostController');
    Route::resource('project', 'ProjectController');
    /*
    | Settings & Logs
    */
    Route::get('/activity-log', 'SettingController@activity')
        ->name('activity-log.index');

    Route::get('/settings', 'SettingController@index')
        ->name('settings.index');
    Route::post('/settings', 'SettingController@update')
        ->name('settings.update');




    /*
    |--------------------------------------------------------------------------
    | PERANGKAT
    |--------------------------------------------------------------------------
    */

    // summary HARUS di atas resource
    Route::get('/perangkat/summary', 'PerangkatController@summary')
        ->name('perangkat.summary');

    Route::get('/perangkat/template', 'PerangkatController@templateExport')
        ->name('perangkat.template');

    Route::post('/perangkat/import', 'PerangkatController@importExcel')
        ->name('perangkat.import');

    Route::get('/perangkat/export', 'PerangkatController@export')
        ->name('perangkat.export');

    Route::get('/cases/get-sn/{id_project}', 'CaseController@getSerialNumbers')->name('cases.get-sn');
    Route::resource('cases', 'CaseController');

    Route::resource('perangkat', 'PerangkatController');
    
    Route::get('/perangkat/{perangkat}', 'PerangkatController@show')
        ->name('perangkat.show');
    /*
    |--------------------------------------------------------------------------
    | PERGANTIAN PERANGKAT
    |--------------------------------------------------------------------------
    */
Route::group(['prefix' => 'pergantian-perangkat'], function () {

    Route::get('/', 'PergantianPerangkatController@index')
        ->name('pergantian.index');

    Route::get('/create/{id_perangkat}', 'PergantianPerangkatController@create')
        ->name('pergantian.create');

    Route::post('/store', 'PergantianPerangkatController@store')
        ->name('pergantian.store');

    Route::post('/approve/{id}', 'PergantianPerangkatController@approve')
        ->name('pergantian.approve');

    Route::get('/{id}/pdf', 'PergantianPerangkatController@pdf')
        ->name('pergantian.pdf');
});


    Route::resource('lokasi', 'LokasiController');

    /*
    |--------------------------------------------------------------------------
    | WAREHOUSE
    |--------------------------------------------------------------------------
    */

    Route::get('/warehouse', 'WarehouseController@index')
        ->name('warehouse.index');
    Route::get('/warehouse/tambah', 'WarehouseController@create')
        ->name('warehouse.tambah');
    Route::post('/warehouse/tambah', 'WarehouseController@store')
        ->name('warehouse.simpan');


    // route statis WAJIB di atas parameter
    Route::get('/warehouse/perangkat', 'WarehouseController@perangkatWarehouse')
        ->name('warehouse.perangkat');

    Route::get('/warehouse/{no_dokumen}', 'WarehouseController@show')
        ->name('warehouse.show');

    Route::get('/warehouse/{no_dokumen}/edit', 'WarehouseController@edit')
        ->name('warehouse.edit');

    Route::put('/warehouse/{no_dokumen}', 'WarehouseController@update')
        ->name('warehouse.update');

    Route::get('/warehouse/{no_dokumen}/pdf', 'WarehouseController@exportPdf')
        ->name('warehouse.pdf');
    






});

/*
|--------------------------------------------------------------------------
| Utilities / Testing
|--------------------------------------------------------------------------
*/
Route::get('/test-pdf', function () {
    return \Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>PDF OK</h1>')
        ->download('test.pdf');
});

Route::view('/media', 'media.index')->name('media.index');
