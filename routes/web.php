<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->can('menu-dashboard') 
            ? redirect()->route('home') 
            : redirect()->route('home_user');
    }
    return redirect()->route('login');
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
    Route::get('/home_user', 'HomeController@homeUser')
        ->name('home_user')
        ->middleware('permission:menu-dashboard-user');

    /*
    | UI Components
    */
    Route::view('/components', 'components')->name('components');

    /*
    | User & Access Management
    */
    Route::resource('users', 'UserController');
    Route::post('/users/{user}/send-credentials', 'UserController@sendAccessCredentials')->name('users.send-credentials');
    Route::get('/profile/{user}', 'UserController@profile')->name('profile.edit');
    Route::post('/profile/{user}', 'UserController@profileUpdate')->name('profile.update');
    Route::get('/dashboard-utilisasi', 'UtilizationDashboardController@index')->name('dashboard.utilisasi');
    Route::post('/dashboard-utilisasi/import', 'UtilizationDashboardController@import')->name('dashboard.utilisasi.import');
    Route::post('/dashboard-utilisasi/import-excel', 'UtilizationDashboardController@importExcel')->name('dashboard.utilisasi.import.excel');

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

    Route::post('/project/report-monitoring/{report}/comments', 'ProjectController@reportCommentStore')
        ->name('project.report.comments.store');

    Route::get('/project/report-monitoring/{report}/upload', 'ProjectController@reportUploadForm')
        ->middleware('permission:monitoring-laporan-upload-laporan')
        ->name('project.report.upload.form');

    Route::post('/project/report-monitoring/{report}/upload', 'ProjectController@reportUpload')
        ->middleware('permission:monitoring-laporan-upload-laporan')
        ->name('project.report.upload');

    Route::get('/project/report-monitoring/{report}/download', 'ProjectController@reportDownload')
        ->middleware('permission:monitoring-laporan-upload-laporan')
        ->name('project.report.download');

    Route::get('/project/monitoring-ba', 'ProjectController@monitoringBA')->name('project.ba.monitoring');
    Route::post('/project/monitoring-ba/{ba}/update', 'ProjectController@monitoringBAUpdate')->name('project.ba.monitoring.update');
    Route::post('/project/monitoring-ba/{ba}/signers', 'ProjectController@monitoringBASignersUpdate')->name('project.ba.monitoring.signers.update');
    Route::post('/project/monitoring-ba/{ba}/comments', 'ProjectController@monitoringBACommentStore')->name('project.ba.comments.store');
    Route::post('/project/{project}/comments', 'ProjectController@projectCommentStore')->name('project.comments.store');
    Route::get('/project/monitoring-ba/export/pdf', 'ProjectController@exportBAPdf')->name('project.ba.export.pdf');
    Route::get('/project/monitoring-ba/export/excel', 'ProjectController@exportBAExcel')->name('project.ba.export.excel');

    Route::resource('category', 'CategoryController')->except('show');
    Route::resource('customer', 'MCustomerController')->except('show');
    Route::resource('sales', 'MSalesController')->except('show');
    Route::resource('team-support', 'TeamSupportController')->except('show');
    Route::resource('post', 'PostController');
    Route::resource('project', 'ProjectController');
    Route::get('/projects/get-team-supports', 'ProjectController@getTeamSupports')->name('projects.get-team-supports');
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

    Route::get('/perangkat/view-detail-summary', 'PerangkatController@viewDetailSummary')
        ->name('perangkat.view-detail-summary');

    Route::post('/perangkat/view-detail-summary-update', 'PerangkatController@viewDetailSummaryUpdate')
        ->name('perangkat.view-detail-summary-update');

    Route::get('/perangkat/template', 'PerangkatController@templateExport')
        ->name('perangkat.template');

    Route::post('/perangkat/import', 'PerangkatController@importExcel')
        ->name('perangkat.import');

    Route::get('/perangkat/export', 'PerangkatController@export')
        ->name('perangkat.export');

    Route::get('/daily-monitoring/{daily_monitoring}/images/{imageIndex}', 'DailyMonitoringController@image')
        ->where('imageIndex', '[0-9]+')
        ->name('daily-monitoring.image');
    Route::get('/daily-monitoring/project/{project}/cases', 'DailyMonitoringController@projectCases')
        ->name('daily-monitoring.project-cases');
    Route::resource('daily-monitoring', 'DailyMonitoringController')->except('destroy');
    Route::get('/cases/get-sn/{id_project}', 'CaseController@getSerialNumbers')->name('cases.get-sn');
    Route::post('/cases/{case}/comments', 'CaseController@storeComment')->name('cases.comments.store');
    Route::resource('cases', 'CaseController');
    Route::resource('interference-ratio', 'InterferenceRatioController');

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
