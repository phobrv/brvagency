<?php
Route::middleware(['web', 'auth', 'auth:sanctum', 'lang', 'verified'])->namespace('Phobrv\BrvCore\Http\Controllers')->group(function () {
    Route::middleware(['can:post_manage'])->prefix('admin')->group(function () {
        Route::resource('province', 'TermController');
    });
});

Route::middleware(['web', 'auth', 'auth:sanctum', 'lang', 'verified'])->namespace('Phobrv\Brvagency\Controllers')->group(function () {
    Route::middleware(['can:post_manage'])->prefix('admin')->group(function () {
        Route::resource('agency', 'AgencyController');
        Route::post('/agency/updateUserSelect', 'AgencyController@updateUserSelect')->name('agency.updateUserSelect');
        Route::get('/agency/changeOrder/{agency_id}/{type}', 'AgencyController@changeOrder')->name('agency.changeOrder');
    });
});
