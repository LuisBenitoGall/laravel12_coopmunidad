<?php

//Frontend:
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Frontend\HomeController;

//Admin:
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AccountingAccountController;
use App\Http\Controllers\Admin\AccountingAccountTypeController;
use App\Http\Controllers\Admin\AccountingConceptController;
use App\Http\Controllers\Admin\AccountingOptionController;
use App\Http\Controllers\Admin\BankAccountController;
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
use App\Http\Controllers\Admin\EffectController;
use App\Http\Controllers\Admin\FileController;
use App\Http\Controllers\Admin\FunctionalityController;
use App\Http\Controllers\Admin\IncidenceController;
use App\Http\Controllers\Admin\IncidencePatternController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoicePatternController;
use App\Http\Controllers\Admin\InvoiceSettingController;
use App\Http\Controllers\Admin\IvaTypeController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProcedureController;
use App\Http\Controllers\Admin\ProcedurePatternController;
use App\Http\Controllers\Admin\ProvinceController;
use App\Http\Controllers\Admin\RemittanceController;
use App\Http\Controllers\Admin\RemittancePatternController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\SecondaryMenuController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\TownController;
use App\Http\Controllers\Admin\UserCommunityController;
use App\Http\Controllers\Admin\UserCompanyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkplaceController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//Cambio idioma:
Route::get('/language/{language}', function ($language) {
    Session()->put('locale', $language);
    return redirect()->back();
})->name('language');

Route::get('/csrf-cookie', function () {
    return response()->json([], 204);
});

//FRONTEND:
Route::get('/', [HomeController::class, 'index'])->name('home');

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//MENU ADMIN:
Route::middleware(['web','auth','only.ajax','throttle:120,1'])->group(function () {
    Route::get('/secondary-menu', SecondaryMenuController::class)
        ->name('secondary-menu');
});

//ADMIN:
Route::middleware(['web', 'auth'])->prefix('admin')->group(function(){
    //Accounting Account Types:
    Route::get('/accounting-account-types', [AccountingAccountTypeController::class, 'index'])->name('accounting-account-types.index');
    Route::get('/accounting-account-types/filtered-data', [AccountingAccountTypeController::class, 'filteredData'])->name('accounting-account-types.filtered-data');
    Route::get('/accounting-account-types/create', [AccountingAccountTypeController::class, 'create'])->name('accounting-account-types.create');
    Route::post('/accounting-account-types/store', [AccountingAccountTypeController::class, 'store'])->name('accounting-account-types.store');
    Route::get('/accounting-account-types/{type}/edit', [AccountingAccountTypeController::class, 'edit'])->name('accounting-account-types.edit');
    Route::put('/accounting-account-types/{type}', [AccountingAccountTypeController::class, 'update'])->name('accounting-account-types.update');
    Route::delete('/accounting-account-types/{type}', [AccountingAccountTypeController::class, 'destroy'])->name('accounting-account-types.destroy');

    //Accounting Accounts:
    Route::get('/accounting-accounts', [AccountingAccountController::class, 'index'])->name('accounting-accounts.index');

    //Accounting Concepts:
    Route::get('/accounting-concepts', [AccountingConceptController::class, 'index'])->name('accounting-concepts.index');

    //Accounting Options:
    Route::get('/accounting-options', [AccountingOptionController::class, 'index'])->name('accounting-options.index');

    //Accounts:
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index')->middleware('can:accounts.index');
    Route::get('/accounts/filtered-data', [AccountController::class, 'filteredData'])->name('accounts.filtered-data');
    Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create')->middleware('can:accounts.create');
    Route::post('/accounts/store', [AccountController::class, 'store'])->name('accounts.store')->middleware('can:accounts.create');
    Route::get('/accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit')->middleware('can:accounts.edit');
    Route::put('/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update')->middleware('can:accounts.update');
    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy')->middleware('can:accounts.destroy');
    Route::get('/accounts/status/{id}/{status}', [AccountController::class, 'status'])->name('accounts.status')->middleware('can:accounts.edit');

    //Admin Dashboard:
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    //Bank Accounts:
    Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bank-accounts.index');

    //Banks:
    Route::get('/banks', [BankController::class, 'index'])->name('banks.index');
    Route::get('/banks/filtered-data', [BankController::class, 'filteredData'])->name('banks.filtered-data');
    Route::get('/banks/create', [BankController::class, 'create'])->name('banks.create');
    Route::post('/banks/store', [BankController::class, 'store'])->name('banks.store');
    Route::get('/banks/{bank}/edit', [BankController::class, 'edit'])->name('banks.edit');
    Route::put('/banks/{bank}', [BankController::class, 'update'])->name('banks.update');
    Route::get('/banks/status/{id}/{status}', [BankController::class, 'status'])->name('banks.status');
    Route::delete('/banks/{bank}', [BankController::class, 'destroy'])->name('banks.destroy');

    //Blocked Ips:
    Route::resource('blocked-ips', BlockedIpController::class)->except(['show', 'create', 'edit']);

    //Business Areas:
    Route::get('/business-areas', [BusinessAreaController::class, 'index'])->name('business-areas.index');

    //Categories:
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('categories/attach', [CategoryController::class, 'attachTo'])->name('categories.attach');

    //Chat:
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');

    //Community:
    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::get('/communities/filtered-data', [CommunityController::class, 'filteredData'])->name('communities.filtered-data');
    Route::get('/communities/create', [CommunityController::class, 'create'])->name('communities.create');
    Route::post('/communities/store', [CommunityController::class, 'store'])->name('communities.store');
    Route::get('/communities/{community}/edit', [CommunityController::class, 'edit'])->name('communities.edit');
    Route::put('/communities/{community}', [CommunityController::class, 'update'])->name('communities.update');
    Route::delete('/communities/{community}', [CommunityController::class, 'destroy'])->name('communities.destroy');
    Route::delete('/communities/{community}/logo', [CommunityController::class, 'deleteLogo'])->name('communities.logo.delete');
    Route::get('/communities/status/{id}/{status}', [CommunityController::class, 'status'])->name('communities.status');
    Route::get('/communities/{community}/select', [CommunityController::class, 'selectCompany'])->name('communities.select-get');
    Route::post('/communities/select', [CommunityController::class, 'selectCompanyPost'])->name('communities.select')->withoutMiddleware(VerifyCsrfToken::class);
    Route::get('/communities/refresh-session', [CommunityController::class, 'refreshSession'])->name('communities.refresh-session');

    //Community Accounts:
    Route::get('/community-accounts', [CommunityAccountController::class, 'index'])->name('community-accounts.index');

    //Community Settings:
    Route::get('/community-settings', [CommunitySettingController::class, 'index'])->name('community-settings.index');

    //Company:
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/filtered-data', [CompanyController::class, 'filteredData'])->name('companies.filtered-data');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies/store', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::delete('/companies/{company}/logo', [CompanyController::class, 'deleteLogo'])->name('companies.logo.delete');
    Route::get('/companies/status/{id}/{status}', [CompanyController::class, 'status'])->name('companies.status');
    Route::get('/companies/{company}/select', [CompanyController::class, 'selectCompany'])->name('companies.select-get');
    Route::post('/companies/select', [CompanyController::class, 'selectCompanyPost'])->name('companies.select')->withoutMiddleware(VerifyCsrfToken::class);
    Route::get('/companies/refresh-session', [CompanyController::class, 'refreshSession'])->name('companies.refresh-session');

    //Company Accounts:
    Route::get('/company-accounts', [CompanyAccountController::class, 'index'])->name('company-accounts.index');
    Route::get('/company-accounts/filtered-data', [CompanyAccountController::class, 'filteredData'])->name('company-accounts.filtered-data');
    Route::get('/company-accounts/create', [CompanyAccountController::class, 'create'])->name('company-accounts.create');
    Route::delete('company-accounts/{account}', [CompanyAccountController::class, 'destroy'])->name('company-accounts.destroy');

    //Company Sectors:
    Route::get('/company-sectors', [CompanySectorController::class, 'index'])->name('company-sectors.index');

    //Company Settings:
    Route::get('/company-settings', [CompanySettingController::class, 'index'])->name('company-settings.index');

    //Contents:
    Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
    Route::get('/contents/create', [ContentController::class, 'create'])->name('contents.create');
    Route::post('/contents/store', [ContentController::class, 'store'])->name('contents.store');
    Route::get('/contents/{content}/edit', [ContentController::class, 'edit'])->name('contents.edit');
    Route::put('/contents/{content}/update', [ContentController::class, 'update'])->name('contents.update');
    Route::delete('/contents/{content}', [ContentController::class, 'destroy'])->name('contents.destroy');
    Route::get('/contents/status/{id}/{status}', [ContentController::class, 'status'])->name('contents.status');

    //Cost Centers:
    Route::get('/cost-centers', [CostCenterController::class, 'index'])->name('cost-centers.index');

    //Countries:
    Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');
    Route::get('/countries/filtered-data', [CountryController::class, 'filteredData'])->name('countries.filtered-data');
    Route::get('/countries/create', [CountryController::class, 'create'])->name('countries.create');
    Route::post('/countries', [CountryController::class, 'store'])->name('countries.store');
    Route::get('/countries/{country}/edit', [CountryController::class, 'edit'])->name('countries.edit');
    Route::put('/countries/{country}', [CountryController::class, 'update'])->name('countries.update');
    Route::delete('/countries/{country}', [CountryController::class, 'destroy'])->name('countries.delete');
    Route::get('/countries/status/{id}/{status}', [CountryController::class, 'status'])->name('countries.status');

    //Currencies:
    Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');

    //Customer Providers:
    Route::get('/customers', [CustomerProviderController::class, 'index'])->name('customers.index');
    Route::get('/providers', [CustomerProviderController::class, 'index'])->name('providers.index');

    //Effects:
    Route::get('/effects', [EffectController::class, 'index'])->name('effects.index');

    //Files:
    Route::get('/files', [FileController::class, 'index'])->name('files.index');

    //Functionalities:
    Route::get('/functionalities', [FunctionalityController::class, 'index'])->name('functionalities.index');

    //Incidence Patterns:
    Route::get('/incidence-patterns', [IncidencePatternController::class, 'index'])->name('incidence-patterns.index');

    //Incidences:
    Route::get('/incidences', [IncidenceController::class, 'index'])->name('incidences.index');

    //Invoice Patterns:
    Route::get('/invoice-patterns', [InvoicePatternController::class, 'index'])->name('invoice-patterns.index');

    //Invoice Patterns:
    Route::get('/invoice-settings', [InvoiceSettingController::class, 'index'])->name('invoice-settings.index');

    //Invoices:
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');

    //IP Access:
    Route::get('/ip-access', [IpAccessController::class, 'index'])->name('ip-access.index');

    //Iva Types:
    Route::get('/iva-types', [IvaTypeController::class, 'index'])->name('iva-types.index');
    Route::get('/iva-types/filtered-data', [IvaTypeController::class, 'filteredData'])->name('iva-types.filtered-data');
    Route::get('/iva-types/create', [IvaTypeController::class, 'create'])->name('iva-types.create');
    Route::post('/iva-types/store', [IvaTypeController::class, 'store'])->name('iva-types.store');
    Route::get('/iva-types/{type}/edit', [IvaTypeController::class, 'edit'])->name('iva-types.edit');
    Route::put('/iva-types/{type}/update', [IvaTypeController::class, 'update'])->name('iva-types.update');
    Route::delete('/iva-types/{type}', [IvaTypeController::class, 'destroy'])->name('iva-types.destroy');
    Route::get('iva-types/status/{id}/{status}', [IvaTypeController::class, 'status'])->name('iva-types.status');

    //Modules:
    Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index')->middleware('can:modules.index');
    Route::get('/modules/filtered-data', [ModuleController::class, 'filteredData'])->name('modules.filtered-data');
    Route::get('/modules/create', [ModuleController::class, 'create'])->name('modules.create')->middleware('can:modules.create');
    Route::post('/modules/store', [ModuleController::class, 'store'])->name('modules.store')->middleware('can:modules.create');
    Route::get('/modules/{module}/edit', [ModuleController::class, 'edit'])->name('modules.edit')->middleware('can:modules.edit');
    Route::put('/modules/{module}', [ModuleController::class, 'update'])->name('modules.update')->middleware('can:modules.update');
    Route::post('/modules/status', [ModuleController::class, 'status'])->name('modules.status')->middleware('can:modules.edit');
    Route::delete('/modules/{module}', [ModuleController::class, 'destroy'])->name('modules.destroy')->middleware('can:modules.destroy');

    //Payment Methods:
    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');

    //Permissions:
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/filtered-data', [PermissionController::class, 'filteredData'])->name('permissions.filtered-data');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions/store', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    //Procedure Patterns:
    Route::get('/procedure-patterns', [ProcedurePatternController::class, 'index'])->name('procedure-patterns.index');

    //Procedures:
    Route::get('/procedures', [ProcedureController::class, 'index'])->name('procedures.index');

    //Provinces:
    Route::get('/provinces/{country}', [ProvinceController::class, 'index'])->name('provinces.index');
    Route::get('/provinces/filtered-data/{country}', [ProvinceController::class, 'filteredData'])->name('provinces.filtered-data');
    Route::get('/provinces/{country}/create', [ProvinceController::class, 'create'])->name('provinces.create');
    Route::post('/provinces', [ProvinceController::class, 'store'])->name('provinces.store');
    Route::get('/provinces/{province}/edit', [ProvinceController::class, 'edit'])->name('provinces.edit');
    Route::put('/provinces/{province}', [ProvinceController::class, 'update'])->name('provinces.update');
    Route::delete('/provinces/{province}', [ProvinceController::class, 'destroy'])->name('provinces.destroy');
    Route::get('/provinces/status/{id}/{status}', [ProvinceController::class, 'status'])->name('provinces.status');

    //Remittance Patterns:
    Route::get('/remittance-patterns', [RemittancePatternController::class, 'index'])->name('remittance-patterns.index');

    //Remittances:
    Route::get('/remittances', [RemittanceController::class, 'index'])->name('remittances.index');

    //Roles:
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('can:roles.index');
    Route::get('/roles/filtered-data', [RoleController::class, 'filteredData'])->name('roles.filtered-data');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('can:roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('can:roles.create');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('can:roles.edit');
    Route::put('/roles/{role}/update', [RoleController::class, 'update'])->name('roles.update')->middleware('can:roles.edit');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('can:roles.destroy');
    Route::post('/roles/{role}/set-permission', [RoleController::class, 'setPermission'])->name('roles.set-permission')->middleware('can:roles.edit');
    Route::post('/roles/{role}/set-multiple-permissions', [RoleController::class, 'setMultiplePermissions'])->name('roles.set-multiple-permissions')->middleware('can:roles.edit');

    //Seats:
    Route::get('/seats', [SeatController::class, 'index'])->name('seats.index');

    //Sectors:
    Route::get('/sectors', [SectorController::class, 'index'])->name('sectors.index');

    //Towns:
    Route::get('/towns/{province}', [TownController::class, 'index'])->name('towns.index');
    Route::get('/towns/filtered-data/{province}', [TownController::class, 'filteredData'])->name('towns.filtered-data');
    Route::get('/towns/{province}/create', [TownController::class, 'create'])->name('towns.create');
    Route::post('/towns', [TownController::class, 'store'])->name('towns.store');
    Route::get('/towns/{town}/edit', [TownController::class, 'edit'])->name('towns.edit');
    Route::put('/towns/{town}', [TownController::class, 'update'])->name('towns.update');
    Route::delete('/towns/{town}', [TownController::class, 'destroy'])->name('towns.destroy');
    Route::get('/towns/status/{id}/{status}', [TownController::class, 'status'])->name('towns.status');

    //Users:
    Route::get('/users/{company_id?}', [UserController::class, 'index'])->name('users.index')->where('company_id', '[0-9]+');
    Route::get('/users/filtered-data', [UserController::class, 'filteredData'])->name('users.filtered-data');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('can:users.create');
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store')->middleware('can:users.create');
    Route::get('/users/{user}/edit{profile?}', [UserController::class, 'edit'])->name('users.edit')->middleware('can:users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('can:users.edit');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('can:users.destroy');
    Route::get('users/status/{id}/{status}', [UserController::class, 'status'])->name('users.status')->middleware('can:users.edit');
    Route::delete('/users/{user}/signature', [UserController::class, 'deleteSignature'])->name('users.signature.delete')->middleware('can:users.edit');
    Route::put('/users/{user}/update-pwd', [UserController::class, 'updatePwd'])->name('users.pwd.update');

    //Users Profile:
    Route::get('/profile', [ProfileController::class, 'editProfile'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //Workplaces:
    Route::get('/workplaces', [WorkplaceController::class, 'index'])->name('workplaces.index');
});

require __DIR__.'/auth.php';
