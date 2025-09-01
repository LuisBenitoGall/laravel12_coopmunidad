<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AccountingAccountTypeController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BlockedIpController;
use App\Http\Controllers\Admin\BusinessAreaController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\CommunityAccountController;
use App\Http\Controllers\Admin\CommunitySettingController;
use App\Http\Controllers\Admin\CompanyAccountController;
use App\Http\Controllers\Admin\CompanyCommunityController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CompanySectorController;
use App\Http\Controllers\Admin\CompanySettingController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CostCenterController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CustomerProviderController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\FunctionalityController;
use App\Http\Controllers\Admin\IvaTypeController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProvinceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\SecondaryMenuController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\TownController;
use App\Http\Controllers\Admin\UserCommunityController;
use App\Http\Controllers\Admin\UserCompanyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkplaceController;

Route::middleware(['web', 'auth'])->prefix('admin/api')->group(function (){
    //Companies:
    Route::get('/companies', [CompanyController::class, 'table'])->name('api.companies.index');
    Route::get('/companies/export/xls', [CompanyController::class,'exportXls'])->name('api.companies.export.xls');
    Route::get('/companies/export/pdf', [CompanyController::class,'exportPdf'])->name('api.companies.export.pdf');

    //Modules:
    Route::get('/modules', [ModuleController::class, 'table'])->name('api.modules.index');
    Route::get('/modules/export/xls', [ModuleController::class,'exportXls'])->name('api.modules.export.xls');
    Route::get('/modules/export/pdf', [ModuleController::class,'exportPdf'])->name('api.modules.export.pdf');

	//Roles:
    Route::get('/roles', [RoleController::class, 'table'])->name('api.roles.index');
    Route::get('/roles/export/xls', [RoleController::class,'exportXls'])->name('api.roles.export.xls');
    Route::get('/roles/export/pdf', [RoleController::class,'exportPdf'])->name('api.roles.export.pdf');

    //Users:
    Route::get('/users', [UserController::class, 'table'])->name('api.users.index');
    Route::get('/users/export/xls', [UserController::class,'exportXls'])->name('api.users.export.xls');
    Route::get('/users/export/pdf', [UserController::class,'exportPdf'])->name('api.users.export.pdf');
});