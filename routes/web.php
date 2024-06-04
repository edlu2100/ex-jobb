<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DNSController;
use Illuminate\Support\Facades\Log;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Http\Controllers\SSLController;
use App\Http\Controllers\Auth\RegisteredUserController;




Route::post('/scan-url', [WebsiteController::class, 'scanUrl'])->name('scan-url');
// Company routes
Route::post('/companies/create', [CompanyController::class, 'create'])->name('createCompany');

Route::post('/websites/process', [WebsiteController::class, 'processWebsites'])->name('processWebsites');

Route::post('/websites/checkLink', [WebsiteController::class, 'checkLinkStatus']);

Route::post('/process-website', [WebsiteController::class, 'processWebsite'])->name('processWebsite');

Route::post('/websites/dns_scan', [DNSController::class, 'dns_scan'])->name('dns_scan');
Route::post('/dns_scan', [DNSController::class, 'dns_scan'])->name('dns_scan');


Route::post('ssl_check', [SSLController::class, 'ssl_check'])->name('ssl_check');


// When not logged in go to login-page
Route::get('/', function () {
    return Inertia::render('Auth/Login');
})->name('login');
// Register
Route::get('/register', function () {
    return Inertia::render('Welcome', [

        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
// Add Website
Route::get('/AddWebsite', function () {
    return Inertia::render('AddWebsite');
})->middleware(['auth', 'verified'])->name('AddWebsite');



// Edit company and website
Route::get('/edit/{id}/{company_name}', function ($id, $company_name) {
    $website = Website::findOrFail($id);
    // Fetch additional company information if needed
    return Inertia::render('EditWebsite', ['website' => $website, 'company_name' => $company_name]);
})->name('EditWebsite');
// CRUD Website and company
Route::post('/websites/{id}', [CompanyController::class, 'update'])->name('updateWebsite');
Route::post('/deleteWebsite/{id}', [CompanyController::class, 'delete'])->name('deleteWebsite');
Route::get('/websites/{id}', [CompanyController::class, 'getCompanyName'])->name('getCompanyName');
Route::get('/searchWebsites', [CompanyController::class, 'searchWebsites'])->name('searchWebsites');
// Get websites
Route::get('/websites', [CompanyController::class, 'index'])->name('websites.index');
// CRUD Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
