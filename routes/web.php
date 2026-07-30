<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\CustomScriptController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureCanAccessAdmin;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

$adminPath = 'admin';
$loginPath = 'login';

try {
    $settings = Setting::query()->pluck('valor', 'chave');
    $adminPath = trim((string) ($settings['admin_path'] ?? 'admin'), '/');
    $loginPath = trim((string) ($settings['login_path'] ?? 'login'), '/');
} catch (\Throwable) {
    $adminPath = 'admin';
    $loginPath = 'login';
}

$adminPath = $adminPath !== '' ? $adminPath : 'admin';
$loginPath = $loginPath !== '' ? $loginPath : 'login';

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/feed/imoveis.xml', [HomeController::class, 'feedImoveisXml'])->name('feed.imoveis');
Route::get('/imoveis', [HomeController::class, 'properties'])->name('properties');
Route::get('/imoveis/{slug}', [HomeController::class, 'showProperty'])->name('property.show');
Route::get('/venda-seu-imovel', [HomeController::class, 'sell'])->name('sell');
Route::get('/quem-somos', [PageController::class, 'about'])->name('about');
Route::get('/gestao-exclusiva', [PageController::class, 'exclusiveManagement'])->name('exclusive-management');
Route::get('/calculadora', [CalculatorController::class, 'index'])->name('calculator');
Route::get('/avalie-seu-imovel', [PageController::class, 'evaluate'])->name('evaluate');
Route::get('/corretor-parceiro', [PageController::class, 'partnerAgent'])->name('partner-agent');
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/contato', [ContactController::class, 'index'])->name('contact');
Route::get('/instagram/media/{mediaId}', [HomeController::class, 'instagramMedia'])->name('instagram.media');
Route::get('/media/{path}', [HomeController::class, 'storageMedia'])->where('path', '.*')->name('media.file');
Route::get('/storage/{path}', [HomeController::class, 'storageMedia'])->where('path', '.*')->name('storage.media');

Route::middleware('guest')->get("/{$loginPath}", [AdminController::class, 'showLogin'])->name('login');
Route::middleware('guest')->post("/{$loginPath}", [AdminController::class, 'login'])->name('login.store');

if ($loginPath !== 'login') {
    Route::middleware('guest')->get('/login', fn () => redirect("/{$loginPath}"));
    Route::middleware('guest')->post('/login', [AdminController::class, 'login']);
}

Route::post('/logout', [AdminController::class, 'logout'])->middleware('auth')->name('logout');

// Admin Routes
Route::prefix($adminPath)->name('admin.')->middleware(['auth', EnsureCanAccessAdmin::class])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/properties', [AdminController::class, 'properties'])->name('properties');
    Route::get('/properties/trash', [AdminController::class, 'propertiesTrash'])->name('properties.trash');
    Route::get('/properties/create', [AdminController::class, 'createProperty'])->name('properties.create');
    Route::post('/properties/uploads', [AdminController::class, 'stagePropertyImageUpload'])->middleware('throttle:property-image-uploads')->name('properties.uploads.store');
    Route::delete('/properties/uploads/{token}', [AdminController::class, 'destroyStagedPropertyImage'])->middleware('throttle:property-image-uploads')->name('properties.uploads.destroy');
    Route::get('/properties/{property}/image-processing-status', [AdminController::class, 'propertyImageProcessingStatus'])->name('properties.images.status');
    Route::post('/properties/{property}/images/{photo}/reprocess', [AdminController::class, 'reprocessPropertyImage'])->name('properties.images.reprocess');
    Route::post('/properties', [AdminController::class, 'storeProperty'])->name('properties.store');
    Route::get('/properties/{property}/edit', [AdminController::class, 'editProperty'])->name('properties.edit');
    Route::put('/properties/{property}', [AdminController::class, 'updateProperty'])->name('properties.update');
    Route::delete('/properties/{property}', [AdminController::class, 'destroyProperty'])->name('properties.destroy');
    Route::post('/properties/{property}/duplicate', [AdminController::class, 'duplicateProperty'])->name('properties.duplicate');
    Route::post('/properties/{property}/restore', [AdminController::class, 'restoreProperty'])->name('properties.restore');
    Route::delete('/properties/{property}/force', [AdminController::class, 'forceDestroyProperty'])->name('properties.force-destroy');
    Route::post('/properties/bulk', [AdminController::class, 'bulkProperties'])->name('properties.bulk');
    Route::get('/business-types', [AdminController::class, 'businessTypes'])->name('business-types');
    Route::post('/business-types', [AdminController::class, 'storeBusinessType'])->name('business-types.store');
    Route::put('/business-types/{businessType}', [AdminController::class, 'updateBusinessType'])->name('business-types.update');
    Route::delete('/business-types/{businessType}', [AdminController::class, 'destroyBusinessType'])->name('business-types.destroy');
    Route::get('/condominiums', [AdminController::class, 'condominiums'])->name('condominiums');
    Route::post('/condominiums', [AdminController::class, 'storeCondominium'])->name('condominiums.store');
    Route::put('/condominiums/{condominium}', [AdminController::class, 'updateCondominium'])->name('condominiums.update');
    Route::delete('/condominiums/{condominium}', [AdminController::class, 'destroyCondominium'])->name('condominiums.destroy');
    Route::get('/categories/property-types', [AdminController::class, 'propertyTypes'])->name('property-types');
    Route::post('/categories/property-types', [AdminController::class, 'storePropertyType'])->name('property-types.store');
    Route::put('/categories/property-types/{propertyType}', [AdminController::class, 'updatePropertyType'])->name('property-types.update');
    Route::delete('/categories/property-types/{propertyType}', [AdminController::class, 'destroyPropertyType'])->name('property-types.destroy');
    Route::get('/categories/special', [AdminController::class, 'specialCategories'])->name('special-categories');
    Route::post('/categories/special', [AdminController::class, 'storeSpecialCategory'])->name('special-categories.store');
    Route::put('/categories/special/{specialCategory}', [AdminController::class, 'updateSpecialCategory'])->name('special-categories.update');
    Route::delete('/categories/special/{specialCategory}', [AdminController::class, 'destroySpecialCategory'])->name('special-categories.destroy');
    Route::get('/leads', [AdminController::class, 'leads'])->name('leads');
    Route::patch('/leads/{lead}', [AdminController::class, 'updateLead'])->name('leads.update');
    Route::put('/leads/settings', [AdminController::class, 'updateLeadsSettings'])->name('leads.settings.update');
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::put('/profile', [AdminController::class, 'updateProfileInfo'])->name('profile.update');
    Route::put('/profile/password', [AdminController::class, 'updateProfilePassword'])->name('profile.password.update');
    Route::post('/profile/avatar', [AdminController::class, 'updateProfileAvatar'])->name('profile.avatar.update');
    Route::get('/pages', [AdminController::class, 'pages'])->name('pages');
    Route::get('/pages/create', [AdminController::class, 'createPage'])->name('pages.create');
    Route::post('/pages', [AdminController::class, 'storePage'])->name('pages.store');
    Route::get('/pages/{page}', [AdminController::class, 'editPage'])->name('pages.edit');
    Route::put('/pages/{page}', [AdminController::class, 'updatePage'])->name('pages.update');
    Route::delete('/pages/{page}', [AdminController::class, 'destroyPage'])->name('pages.destroy');
    Route::post('/pages/{page}/duplicate', [AdminController::class, 'duplicatePage'])->name('pages.duplicate');
    Route::post('/pages/{page}/media', [AdminController::class, 'uploadPageMedia'])->name('pages.media');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/instagram', [AdminController::class, 'instagram'])->name('instagram');
    Route::put('/instagram', [AdminController::class, 'updateInstagram'])->name('instagram.update');
    Route::post('/instagram/refresh', [AdminController::class, 'refreshInstagramFeed'])->name('instagram.refresh');
    Route::middleware(EnsureAdminRole::class)->prefix('/integracoes')->name('integrations.')->group(function () {
        Route::get('/pixels-tags', [IntegrationController::class, 'index'])->name('pixels-tags');
        Route::put('/pixels-tags/{provider}', [IntegrationController::class, 'update'])->name('pixels-tags.update');

        Route::get('/codigo-personalizado', [CustomScriptController::class, 'index'])->name('custom-scripts.index');
        Route::get('/codigo-personalizado/criar', [CustomScriptController::class, 'create'])->name('custom-scripts.create');
        Route::post('/codigo-personalizado', [CustomScriptController::class, 'store'])->name('custom-scripts.store');
        Route::get('/codigo-personalizado/{customScript}/editar', [CustomScriptController::class, 'edit'])->name('custom-scripts.edit');
        Route::put('/codigo-personalizado/{customScript}', [CustomScriptController::class, 'update'])->name('custom-scripts.update');
        Route::delete('/codigo-personalizado/{customScript}', [CustomScriptController::class, 'destroy'])->name('custom-scripts.destroy');
        Route::post('/codigo-personalizado/{customScript}/duplicar', [CustomScriptController::class, 'duplicate'])->name('custom-scripts.duplicate');
        Route::patch('/codigo-personalizado/{customScript}/toggle', [CustomScriptController::class, 'toggle'])->name('custom-scripts.toggle');
    });
    Route::middleware(EnsureAdminRole::class)->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });

    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/posts', [AdminController::class, 'blogPosts'])->name('posts');
        Route::get('/posts/create', [AdminController::class, 'createBlogPost'])->name('posts.create');
        Route::post('/posts', [AdminController::class, 'storeBlogPost'])->name('posts.store');
        Route::get('/posts/{post}/edit', [AdminController::class, 'editBlogPost'])->name('posts.edit');
        Route::put('/posts/{post}', [AdminController::class, 'updateBlogPost'])->name('posts.update');
        Route::delete('/posts/{post}', [AdminController::class, 'destroyBlogPost'])->name('posts.destroy');
        Route::get('/categories', [AdminController::class, 'blogCategories'])->name('categories');
        Route::post('/categories', [AdminController::class, 'storeBlogCategory'])->name('categories.store');
        Route::put('/categories/{category}', [AdminController::class, 'updateBlogCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'destroyBlogCategory'])->name('categories.destroy');
    });
});

if ($adminPath !== 'admin') {
    Route::middleware(['auth', EnsureCanAccessAdmin::class])->get('/admin/{any?}', function (?string $any = null) use ($adminPath) {
        $tail = $any ? '/' . ltrim($any, '/') : '';
        return redirect('/' . $adminPath . $tail);
    })->where('any', '.*');
}

// Form submissions
Route::post('/contato/send', [ContactController::class, 'send'])->name('contact.send');
Route::post('/venda-seu-imovel/send', [HomeController::class, 'sendSell'])->name('sell.send');
Route::post('/avalie-seu-imovel/send', [PageController::class, 'sendEvaluate'])->name('evaluate.send');
Route::post('/corretor-parceiro/send', [PageController::class, 'sendPartnerAgent'])->name('partner-agent.send');
Route::get('/{page:slug}', [PageController::class, 'show'])->name('page.show');
