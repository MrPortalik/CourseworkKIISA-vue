<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\PlatformReportController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\UserMessageController;
use App\Http\Controllers\ArticleModerationController;
use App\Http\Controllers\AdminTaxonomyController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentVoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SubscriptionController;
use App\Models\Faq;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('/');

Route::get('/dashboard', [ArticleController::class, 'profile'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/aboutus', function () {
    return Inertia::render('AboutUs');
})->name('aboutus');

Route::get('/faq', function () {
    return Inertia::render('Faq/Index', [
        'faqs' => Faq::orderBy('sort_order')->get(),
    ]);
})->name('faq.index');

Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/search-preview', [ArticleController::class, 'searchPreview'])->name('articles.search-preview');
Route::get('/articles/objects/{range}', [ArticleController::class, 'objects'])
    ->name('articles.objects')
    ->where('range', '[0-9]+');

Route::middleware('auth')->group(function () {
    Route::get('/articles/drafts', [ArticleController::class, 'drafts'])->name('articles.drafts');
    Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create');
    Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::post('/articles/content-images', [ArticleController::class, 'uploadContentImage'])->name('articles.content-images.store');
    Route::get('/users/search', [\App\Http\Controllers\CoauthorController::class, 'searchUsers'])->name('users.search');
    Route::post('/articles/{article:slug}/coauthors', [\App\Http\Controllers\CoauthorController::class, 'invite'])->name('articles.coauthors.invite');
    Route::post('/coauthors/{coauthor}/accept', [\App\Http\Controllers\CoauthorController::class, 'accept'])->name('coauthors.accept');
    Route::post('/coauthors/{coauthor}/decline', [\App\Http\Controllers\CoauthorController::class, 'decline'])->name('coauthors.decline');
    Route::get('/articles/{article:slug}/edit', [ArticleController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article:slug}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article:slug}', [ArticleController::class, 'destroy'])->name('articles.destroy');

    Route::post('/articles/{article:slug}/rate', [RatingController::class, 'store'])->name('articles.rate');
    Route::delete('/articles/{article:slug}/rate', [RatingController::class, 'destroy'])->name('articles.unrate');
    Route::post('/articles/{article:slug}/comments', [CommentController::class, 'store'])->name('articles.comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/comments/{comment}/vote', [CommentVoteController::class, 'store'])->name('comments.vote');
    Route::post('/reports', [PlatformReportController::class, 'store'])->name('reports.store');
    Route::post('/messages/{message}/reply', [UserMessageController::class, 'reply'])->name('messages.reply');
});

Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('/');
    })->name('logout');

    Route::get('/authors/{user}', [AuthorController::class, 'show'])->name('authors.show');
    Route::post('/profile/dashboard', [AuthorController::class, 'updateProfile'])->name('profile.dashboard.update');
    Route::post('/authors/{user}/subscribe', [SubscriptionController::class, 'store'])->name('authors.subscribe');
    Route::delete('/authors/{user}/subscribe', [SubscriptionController::class, 'destroy'])->name('authors.unsubscribe');

    Route::redirect('/subscriptions', '/notifications');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::post('/admin/articles/{article}/approve', [ArticleModerationController::class, 'approve'])->name('admin.articles.approve');
    Route::post('/admin/articles/{article}/reject', [ArticleModerationController::class, 'reject'])->name('admin.articles.reject');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/categories', [AdminTaxonomyController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/tags', [AdminTaxonomyController::class, 'tags'])->name('admin.taxonomy');
    Route::post('/admin/categories', [AdminTaxonomyController::class, 'storeCategory'])->name('admin.categories.store');
    Route::put('/admin/categories/{category}', [AdminTaxonomyController::class, 'updateCategory'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [AdminTaxonomyController::class, 'destroyCategory'])->name('admin.categories.destroy');
    Route::post('/admin/tags', [AdminTaxonomyController::class, 'storeTag'])->name('admin.tags.store');
    Route::put('/admin/tags/{tag}', [AdminTaxonomyController::class, 'updateTag'])->name('admin.tags.update');
    Route::delete('/admin/tags/{tag}', [AdminTaxonomyController::class, 'destroyTag'])->name('admin.tags.destroy');
    Route::get('/admin/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::post('/admin/reports/{report}/respond', [AdminReportController::class, 'respond'])->name('admin.reports.respond');
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
    Route::post('/admin/users/{user}/promote', [AdminUserController::class, 'promoteAdmin'])->name('admin.users.promote');
    Route::post('/admin/users/{user}/demote', [AdminUserController::class, 'demoteAdmin'])->name('admin.users.demote');
    Route::post('/admin/users/{user}/block', [AdminUserController::class, 'block'])->name('admin.users.block');
    Route::post('/admin/users/{user}/unblock', [AdminUserController::class, 'unblock'])->name('admin.users.unblock');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/users/{user}/message', [AdminUserController::class, 'sendMessage'])->name('admin.users.message');
});

require __DIR__.'/auth.php';
