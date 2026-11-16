<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\PublicContentController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicContentController::class, 'page'])->defaults('slug', 'home')->name('home');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/sitemap/static.xml', [SitemapController::class, 'static'])->name('sitemap.static');
Route::get('/sitemap/{type}/{page}.xml', [SitemapController::class, 'content'])->whereNumber('page')->name('sitemap.content');
Route::get('/robots.txt', fn () => response(file_get_contents(public_path('robots.txt')), 200, ['Content-Type' => 'text/plain; charset=UTF-8']))->name('robots');
Route::get('/solucoes', [PublicContentController::class, 'services'])->name('services.index');
Route::get('/solucoes/{slug}', [PublicContentController::class, 'service'])->name('services.show');
Route::get('/paginas/sobre', [PublicContentController::class, 'redirectAbout']);
Route::get('/paginas/home', [PublicContentController::class, 'redirectHome']);
Route::get('/paginas/{slug}', [PublicContentController::class, 'page'])->name('pages.show');
Route::get('/sobre', [PublicContentController::class, 'page'])->defaults('slug', 'sobre')->name('pages.about');
Route::get('/produtos', [PublicContentController::class, 'products'])->name('products.index');
Route::get('/produtos/{slug}', [PublicContentController::class, 'product'])->name('products.show');
Route::get('/projetos', [PublicContentController::class, 'projects'])->name('projects.index');
Route::get('/projetos/{slug}', [PublicContentController::class, 'project'])->name('projects.show');
Route::get('/insights', [PublicContentController::class, 'posts'])->name('posts.index');
Route::get('/insights/{slug}', [PublicContentController::class, 'post'])->name('posts.show');
Route::get('/contato', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contato', [ContactController::class, 'submit'])->middleware('throttle:5,1')->name('contact.submit');
