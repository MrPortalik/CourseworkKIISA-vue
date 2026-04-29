<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function validateUser(Request $request, Article $article) {
        if ($request->user()->id!==$article->user_id && $request->user()->role!=='admin') {
            abort(403);
        }
    }

    public function test()
    {
        return response()->json([
            'status' => 'controller works',
            'articles_count' => Article::count(),
            'first_article' => Article::first(),
        ]);
    }

    public function index(Request $request)
    {
        $articles = Article::with('user')
            ->published()
            ->latest()
            ->paginate(12);
            
        return Inertia::render('Articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function drafts(Request $request)
    {
        $articles = Article::with('user')
            ->unpublished()
            ->currentauthor($request->user())
            ->latest()
            ->paginate(12);
            
        return Inertia::render('Articles/Index', [
            'articles' => $articles,
        ]);
    }

    public function show(Article $article)
    {   
        $article->load('user');
        
        // Простой ответ для теста
        return Inertia::render('Articles/Show', [
            'article' => $article->toArray(),
        ]);
    }

    public function profile(Request $request)
    {
        $articles = Article::with('user')
            ->currentauthor($request->user())
            ->latest()
            ->paginate(12);
            
        return Inertia::render('Dashboard', [
            'articles' => $articles,
        ]);
    }

    public function create()
    {
        return Inertia::render('Articles/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean',
        ]);
        
        $slug = Str::slug($request->title);
        $count = Article::where('slug', 'like', $slug . '%')->count();
        
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }
        
        $article = Article::create([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'user_id' => auth()->id(),
            'is_published' => $request->boolean('is_published', false),
        ]);
        
        return redirect()->route('articles.show', $article->slug);
    }

    public function edit(Article $article, Request $request) 
    {
        $this->validateUser($request, $article);
        
        return Inertia::render('Articles/Edit', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $this->validateUser($request, $article);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_published' => 'boolean',
        ]);
        
        if ($article->title !== $request->title) {
            $slug = Str::slug($request->title);
            $count = Article::where('slug', 'like', $slug . '%')
                ->where('id', '!=', $article->id)
                ->count();
            
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }
            
            $article->slug = $slug;
        }
        
        $article->update([
            'title' => $request->title,
            'content' => $request->content,
            'is_published' => $request->boolean('is_published'),
        ]);
        
        return redirect()->route('articles.show', $article->slug);
    }

    public function destroy(Article $article)
    {
        $this->validateUser($request, $article);
        
        $article->delete();
        
        return redirect()->route('articles.index');
    }
}