<?php

use App\Http\Controllers\CounterpartyController;
use App\Http\Controllers\Setting\AddFieldsController;
use App\Http\Controllers\Entity\PopapController;
use App\Http\Controllers\Entity\widgetController;
use App\Http\Controllers\initialization\indexController;
use App\Http\Controllers\Setting\AttributeController;
use App\Http\Controllers\Setting\AutomationController;
use App\Http\Controllers\Setting\AutomatizationController;
use App\Http\Controllers\Setting\CreateAuthTokenController;
use App\Http\Controllers\Setting\LidController;
use App\Http\Controllers\Setting\organizationController;
use App\Http\Controllers\Setting\templateController;
use App\Http\Controllers\Setting\webCounterpartyController;
use App\Http\Controllers\vendor\vendorEndpoint;
use Illuminate\Support\Facades\Route;

//main windows
Route::get('/', [indexController::class, 'initialization']);
Route::get('/{accountId}/', [indexController::class, 'index'])->name('main');
Route::get('error/{accountId}/', [indexController::class, 'error'])->name('error');

//Setting get Employee
Route::group(["prefix" => "Setting"], function () {
    Route::get('/createToken/{accountId}', [CreateAuthTokenController::class, 'getCreateAuthToken'])->name('creatEmployee');
    Route::post('/createToken/{accountId}', [CreateAuthTokenController::class, 'postCreateAuthToken']);

    Route::get('/get/employee/{accountId}', [CreateAuthTokenController::class, 'getEmployee']);
    Route::get('/create/employee/{accountId}', [CreateAuthTokenController::class, 'createEmployee']);
    Route::get('/delete/employee/{accountId}', [CreateAuthTokenController::class, 'deleteEmployee']);
});


//Setting for
Route::group(["prefix" => "Setting"], function () {
    Route::get('/organization/{accountId}', [organizationController::class, 'getCreate'])->name('creatOrganization');
    Route::post('/organization/{accountId}', [organizationController::class, 'postCreate']);

    Route::get('/organization/get/Licenses/{accountId}', [organizationController::class, 'getLicenses']);
    Route::get('/organization/create/Licenses/{accountId}', [organizationController::class, 'createLicenses']);
    Route::get('/organization/delete/Licenses/{accountId}', [organizationController::class, 'deleteLicenses']);
});





Route::get('/Setting/addFields/{accountId}', [AddFieldsController::class, 'getAddFields']);
Route::get('/Setting/filledAddFields/{accountId}', [AddFieldsController::class, 'getFilledAddFields']);

Route::post('/Setting/addFields/{accountId}', [AddFieldsController::class, 'saveAddField']);

Route::delete('/Setting/addFields/{accountId}/{uuid}', [AddFieldsController::class, 'deleteAddField']);

Route::get('/Setting/getAttributes/{accountId}', [AttributeController::class, 'getAttributes']);



// get list templates to front
Route::post('/Setting/template/{accountId}', [templateController::class, 'postCreate']);
Route::get('/Setting/template/{accountId}', [templateController::class, 'getCreated'])->name('template');
//

// save new templates
Route::get('/Setting/template/create/poles/{accountId}', [templateController::class, 'getCreatePoles']);

//update block
//get template text by uuid
Route::get('/Setting/template/nameuid/poles/{accountId}', [templateController::class, 'getNameUIDPoles']);
Route::put('/Setting/template/{accountId}', [templateController::class, 'putTemplateByUuid']);
//

//delete
Route::delete('Setting/template/{accountId}/{uuid}', [templateController::class, 'deleteTemplate']);


Route::get('/Setting/template/info/fields/', [templateController::class, 'getMainFields']);



Route::post('/Setting/getTemplate/{accountId}', [templateController::class, 'getTemplate']);

Route::get('/Setting/template/get/attributes/{accountId}', [templateController::class, 'getAttributes']);
Route::get('/Setting/template/delete/poles/{accountId}', [templateController::class, 'deletePoles']);



/***
НОВЫЕ ОБНОВЛЕНИЯ СЦЕНАРИИ И АВТОМАТИЗАЦИЯ
 **/
Route::group(["prefix" => "Setting"], function () {
    Route::get('/scenario/{accountId}', [AutomatizationController::class, 'getScenario'])->name('scenario');
    Route::post('/scenario/{accountId}', [AutomatizationController::class, 'saveScenario']);
    Route::delete('/scenario/{accountId}', [AutomatizationController::class, 'deleteScenario']);


    Route::get('/automation/{accountId}', [AutomationController::class, 'getAutomation'])->name('automation');
    Route::post('/automation/{accountId}', [AutomationController::class, 'postAutomation']);
});
/***
НОВЫЕ ОБНОВЛЕНИЯ ПО ЛИД
 **/
Route::group(["prefix" => "Setting"], function () {
    Route::get('/lid/{accountId}', [LidController::class, 'getLid'])->name('lid');
    Route::post('/lid/{accountId}', [LidController::class, 'saveLid']);
});
/***
НОВЫЕ ОБНОВЛЕНИЯ ВЫГРУЗКИ КОТРАГЕНТОВ
 **/
Route::group(["prefix" => "Setting"], function () {
    Route::get('/counterparty/{accountId}', [webCounterpartyController::class, 'get'])->name('counterparty');
    Route::post('/counterparty/{accountId}', [webCounterpartyController::class, 'save']);
});



//widget
Route::group(["prefix" => "widget"], function () {
    Route::get('/{object}', [widgetController::class, 'widgetObject']);
    Route::get('/get/Data', [widgetController::class, 'widgetGetData']);
});


//Popup
Route::group(["prefix" => "Popup"], function () {
    Route::get('/{object}', [PopapController::class, 'Popup']);
    Route::get('/template/message/Show', [PopapController::class, 'template']);
    Route::get('/template/message/get/All', [templateController::class, 'getTemplates']);
    Route::get('/template/message/get/where/name', [PopapController::class, 'searchTemplate']);
    Route::get('/template/message/get/information/messenger', [PopapController::class, 'messenger']);
    Route::get('/template/message/get/information/chatapp', [PopapController::class, 'information']);
    Route::get('/template/message/get/send/message', [PopapController::class, 'sendMessage']);
});


//Install or delete web app
Route::put('Config/vendor-endpoint/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'put']);
Route::delete('Config/vendor-endpoint/api/moysklad/vendor/1.0/apps/{apps}/{accountId}', [vendorEndpoint::class, 'delete']);
