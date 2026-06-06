<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $staticUrls = [
            ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['loc' => route('articles.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('aboutus'), 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['loc' => route('faq.index'), 'changefreq' => 'monthly', 'priority' => '0.7'],
        ];

        $articles = Article::published()
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $xml = view('sitemap', [
            'staticUrls' => $staticUrls,
            'articles' => $articles,
        ])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
