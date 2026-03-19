<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SitemapController;


Route::get('/test', fn() => request()->isSecure() ? 'HTTPS' : 'HTTP');

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('odeme')->name('payment.')->group(function () {
    Route::get('/siparis/{order}', [PaymentController::class, 'show'])
        ->name('show');

    Route::get('/basarili', [PaymentController::class, 'success'])
        ->name('success');

    Route::get('/basarisiz', [PaymentController::class, 'fail'])
        ->name('fail');

    Route::get('/beklemede/{order}', [PaymentController::class, 'pending']) 
        ->name('pending');

    Route::post('/taksit-sorgula', [PaymentController::class, 'installments'])
        ->name('installments');
});

Route::post('/paytr/webhook', [PaymentController::class, 'webhook'])
    ->name('paytr.webhook')
    ->middleware('api');



Route::livewire('/abiye-modelleri', 'pages::shop.all-products')->name('all-products');
Route::livewire('/favorilerim', 'favorites')->name('favorites');
Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/kategori/{slug}', 'pages::shop.category')->name('category');
Route::livewire('/urun/{slug}', 'pages::shop.product')->name('product');
Route::livewire('/sepet', 'pages::shop.cart')->name('cart');
Route::livewire('/odeme/bilgiler', 'pages::shop.checkout')->name('checkout')->middleware('auth');
Route::livewire('/giris', 'pages::account.login')->name('login');
Route::livewire('/kayit', 'pages::account.register')->name('register');
Route::livewire('/hesabim/siparislerim', 'pages::account.orders')->name('account.orders')->middleware('auth');
Route::livewire('/blog', 'pages::blog.index')->name('blog.index');
Route::livewire('/blog/{slug}', 'pages::blog.show')->name('blog.show');

Route::livewire('/{slug}', 'pages::page.show')->name('page');


Route::post('/cikis', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', function () {
    $content  = "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /odeme/\n";
    $content .= "Disallow: /hesabim/\n\n";
    $content .= "Sitemap: " . route('sitemap') . "\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');



