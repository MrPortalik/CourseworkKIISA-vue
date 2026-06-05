<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminTaxonomyController extends Controller
{
    public function categories()
    {
        return Inertia::render('Admin/Categories', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function tags()
    {
        return Inertia::render('Admin/Taxonomy', [
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create($request->only('name', 'description'));

        return back();
    }

    public function updateCategory(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'description' => 'nullable|string',
        ]);

        $category->update($request->only('name', 'description'));

        return back();
    }

    public function destroyCategory(Category $category)
    {
        Article::query()->where('category_id', $category->id)->update(['category_id' => null]);
        DB::table('article_category')->where('category_id', $category->id)->delete();
        $category->delete();

        return back();
    }

    public function storeTag(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Tag::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return back();
    }

    public function updateTag(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,'.$tag->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $tag->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return back();
    }

    public function destroyTag(Tag $tag)
    {
        $tag->articles()->detach();
        $tag->delete();

        return back();
    }
}
