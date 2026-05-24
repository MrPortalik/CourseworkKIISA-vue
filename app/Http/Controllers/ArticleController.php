<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Tag;
use App\Services\ArticleSearchService;
use App\Support\ObjectNumberParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function validateUser(Request $request, Article $article): void
    {
        if ($request->user()->id !== $article->user_id && ! $request->user()->isAdmin()) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        return Inertia::render('Articles/Index', $this->buildArticlesListing($request));
    }

    public function searchPreview(Request $request, ArticleSearchService $search)
    {
        $q = trim((string) $request->q);
        if ($q === '') {
            return response()->json(['items' => []]);
        }

        $query = Article::published()->with('user');
        $this->applyListingFilters($query, $request);
        $articles = $query->get(['id', 'title', 'slug', 'user_id']);
        $ranked = $search->rankArticles($articles, $q);
        $items = $ranked['exact']->concat($ranked['related'])->take(30)->map(fn ($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'slug' => $a->slug,
        ])->values();

        return response()->json(['items' => $items]);
    }

    public function objects(Request $request, int $range)
    {
        $min = $range === 0 ? 1 : $range * 100;
        $max = ($range + 1) * 100 - 1; // 0→1–99, 1→100–199, …

        $payload = $this->buildArticlesListing($request, function ($query) use ($min, $max) {
            $query->whereNotNull('object_number')
                ->whereBetween('object_number', [$min, $max])
                ->orderBy('object_number');
        });

        $payload['objectRange'] = [
            'index' => $range,
            'label' => ObjectNumberParser::hundredRangeLabel($range),
            'min' => $min,
            'max' => $max,
        ];

        return Inertia::render('Articles/Index', $payload);
    }

    public function drafts(Request $request)
    {
        $query = Article::with(['user', 'category'])->unpublished()->latest();

        if (! $request->user()->isAdmin()) {
            $query->currentAuthor($request->user());
        }

        return Inertia::render('Articles/Drafts', [
            'articles' => $query->paginate(12),
            'isAdmin' => $request->user()->isAdmin(),
        ]);
    }

    public function show(Request $request, Article $article)
    {
        if (! $article->is_published) {
            $user = $request->user();
            if (! $user || ($user->id !== $article->user_id && ! $user->isAdmin())) {
                abort(404);
            }
        }

        $article->load(['user', 'category', 'tags']);
        $article->loadAvg('votes as average_rating', 'vote');
        $article->loadCount('votes as ratings_count');

        $comments = Comment::where('article_id', $article->id)
            ->whereNull('parent_id')
            ->with([
                'user',
                'children' => fn ($q) => $q->with(['user', 'children.user'])->withCount([
                    'votes as upvotes_count' => fn ($v) => $v->where('vote', 1),
                    'votes as downvotes_count' => fn ($v) => $v->where('vote', -1),
                ]),
            ])
            ->withCount([
                'votes as upvotes_count' => fn ($q) => $q->where('vote', 1),
                'votes as downvotes_count' => fn ($q) => $q->where('vote', -1),
            ])
            ->latest()
            ->get();

        $userRating = null;
        $userCommentVotes = [];
        if ($request->user()) {
            $userRating = $article->votes()->where('user_id', $request->user()->id)->value('vote');
            $commentIds = $this->collectCommentIds($comments);
            $userCommentVotes = CommentVote::where('user_id', $request->user()->id)
                ->whereIn('comment_id', $commentIds)
                ->pluck('vote', 'comment_id');
        }

        $canRate = $request->user() && $request->user()->id !== $article->user_id;

        return Inertia::render('Articles/Show', [
            'article' => $article,
            'comments' => $comments,
            'userRating' => $userRating,
            'userCommentVotes' => $userCommentVotes,
            'canRate' => $canRate,
        ]);
    }

    public function profile(Request $request)
    {
        return redirect()->route('authors.show', $request->user());
    }

    public function create()
    {
        return Inertia::render('Articles/Create', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->normalizeArticleRequest($request);

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_publishable' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'hero_banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        ];

        if ($request->user()->isAdmin()) {
            $rules['is_published'] = 'boolean';
        }

        $request->validate($rules);

        $slug = $this->makeUniqueSlug($request->title);
        $isAdmin = $request->user()->isAdmin();

        $article = new Article([
            'title' => $request->title,
            'content' => $this->normalizeContent($request->content),
            'slug' => $slug,
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'is_publishable' => $request->boolean('is_publishable', false),
            'is_published' => $isAdmin ? $request->boolean('is_published', false) : false,
        ]);

        if ($request->hasFile('banner')) {
            $article->banner = Storage::url($request->file('banner')->store('banners', 'public'));
        }
        if ($request->hasFile('hero_banner')) {
            $article->hero_banner = Storage::url($request->file('hero_banner')->store('hero_banners', 'public'));
        }

        Article::syncObjectNumberFromTitle($article);
        $article->save();
        $this->syncPublicationTimestamp($article, false);
        $article->tags()->sync($request->input('tag_ids', []));

        if ($isAdmin && $article->is_published) {
            $article->notifySubscribersOfPublication();
        }

        return redirect()->route('articles.show', $article->slug);
    }

    public function edit(Article $article, Request $request)
    {
        $this->validateUser($request, $article);
        $article->load('tags');

        return Inertia::render('Articles/Edit', [
            'article' => $article,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $this->validateUser($request, $article);
        $this->normalizeArticleRequest($request);

        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'is_publishable' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'hero_banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
        ];

        if ($request->user()->isAdmin()) {
            $rules['is_published'] = 'boolean';
            $rules['is_hit'] = 'boolean';
            $rules['is_editors_choice'] = 'boolean';
            $rules['is_new'] = 'boolean';
        }

        $request->validate($rules);

        if ($article->title !== $request->title) {
            $article->slug = $this->makeUniqueSlug($request->title, $article->id);
        }

        $wasPublished = $article->is_published;

        $data = [
            'title' => $request->title,
            'content' => $this->normalizeContent($request->content),
            'category_id' => $request->category_id,
        ];

        if ($request->hasFile('banner')) {
            $this->deleteStoredFile($article->banner);
            $data['banner'] = Storage::url($request->file('banner')->store('banners', 'public'));
        }
        if ($request->hasFile('hero_banner')) {
            $this->deleteStoredFile($article->hero_banner);
            $data['hero_banner'] = Storage::url($request->file('hero_banner')->store('hero_banners', 'public'));
        }

        if ($request->user()->isAdmin()) {
            $data['is_published'] = $request->boolean('is_published');
            $data['is_hit'] = $request->boolean('is_hit');
            $data['is_editors_choice'] = $request->boolean('is_editors_choice');
            $data['is_new'] = $request->boolean('is_new');
        } elseif ($request->user()->id === $article->user_id) {
            $data['is_publishable'] = $request->boolean('is_publishable');
        }

        $article->fill($data);
        Article::syncObjectNumberFromTitle($article);
        $article->save();
        $this->syncPublicationTimestamp($article, $wasPublished);
        $article->tags()->sync($request->input('tag_ids', []));

        if ($request->user()->isAdmin() && ! $wasPublished && $article->is_published) {
            $article->notifySubscribersOfPublication();
        }

        return redirect()->route('articles.show', $article->slug);
    }

    public function uploadContentImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $path = $request->file('image')->store('articles/content', 'public');

        return response()->json([
            'url' => Storage::url($path),
        ]);
    }

    public function destroy(Article $article, Request $request)
    {
        $this->validateUser($request, $article);

        $this->deleteStoredFile($article->banner);
        $this->deleteStoredFile($article->hero_banner);
        $article->delete();

        return redirect()->route('articles.index');
    }

    private function buildArticlesListing(Request $request, ?callable $extraQuery = null): array
    {
        $baseQuery = Article::with(['user', 'category', 'tags'])->published();

        if ($extraQuery) {
            $extraQuery($baseQuery);
        }

        $this->applyListingFilters($baseQuery, $request);

        $payload = [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'filters' => [
                'q' => $request->q,
                'category' => $request->category,
                'categories' => $this->normalizeIdList($request->input('categories', [])),
                'tags' => $this->normalizeIdList($request->input('tags', [])),
                'section' => $request->section,
                'search' => $request->boolean('search'),
            ],
            'activeCategory' => $request->filled('category')
                ? Category::find($request->category)
                : null,
        ];

        if ($request->filled('q') && $request->boolean('search')) {
            $split = app(ArticleSearchService::class)->splitForListing($request->q);
            $exactIds = $this->filterIdsByRequest($split['exact']->pluck('id'), $request, $extraQuery);
            $relatedIds = $this->filterIdsByRequest($split['related']->pluck('id'), $request, $extraQuery);

            $payload['exactArticles'] = $this->paginateIds($exactIds, $request);
            $payload['otherArticles'] = $this->paginateIds($relatedIds, $request, 'others_page');
            $payload['searchMeta'] = [
                'hasExact' => $exactIds->isNotEmpty(),
                'showOthers' => $relatedIds->isNotEmpty(),
            ];
            $payload['articles'] = ['data' => [], 'links' => []];

            return $payload;
        }

        if ($extraQuery) {
            $baseQuery->orderBy('object_number');
        } else {
            $baseQuery->latest();
        }

        if ($request->filled('q') && ! $request->boolean('search')) {
            $all = (clone $baseQuery)->with('user')->get();
            $ranked = app(ArticleSearchService::class)->rankArticles($all, $request->q);
            $orderedIds = $ranked['exact']->pluck('id')->concat($ranked['related']->pluck('id'));
            $payload['articles'] = $this->paginateIds($orderedIds, $request);
        } else {
            $payload['articles'] = $baseQuery->paginate(12)->withQueryString();
        }

        $currentPage = max(1, (int) $request->input('page', 1));

        if (
            $currentPage === 1
            && ! $extraQuery
            && ! $request->filled('category')
            && ! $request->filled('q')
            && ! $request->filled('section')
            && ! $request->boolean('search')
        ) {
            $payload['categorySliders'] = Category::orderBy('name')
                ->with(['articles' => fn ($q) => $q->published()->with('user')->latest()->limit(15)])
                ->get()
                ->filter(fn ($cat) => $cat->articles->isNotEmpty())
                ->map(fn ($cat) => [
                    'category' => $cat->only(['id', 'name', 'description']),
                    'articles' => $cat->articles,
                ])
                ->values();
        }

        return $payload;
    }

    private function applyListingFilters($query, Request $request): void
    {
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $categoryIds = $this->normalizeIdList($request->input('categories', []));
        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
        }

        $tagIds = $this->normalizeIdList($request->input('tags', []));
        if ($tagIds !== []) {
            $query->whereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds));
        }

        if ($request->section === 'hits') {
            $query->hits();
        } elseif ($request->section === 'editors_choice') {
            $query->editorsChoice();
        } elseif ($request->section === 'new') {
            $query->newItems();
        }
    }

    private function filterIdsByRequest($ids, Request $request, ?callable $extraQuery)
    {
        if ($ids->isEmpty()) {
            return collect();
        }

        $q = Article::published()->whereIn('id', $ids);
        if ($extraQuery) {
            $extraQuery($q);
        }
        $this->applyListingFilters($q, $request);

        return $q->pluck('id');
    }

    private function paginateIds($ids, Request $request, string $pageName = 'page')
    {
        $ids = collect($ids)->values();
        $page = max(1, (int) $request->input($pageName, 1));
        $perPage = 12;
        $slice = $ids->slice(($page - 1) * $perPage, $perPage)->values();

        $articles = Article::with(['user', 'category', 'tags'])
            ->whereIn('id', $slice)
            ->get()
            ->sortBy(fn ($a) => $slice->search($a->id))
            ->values();

        return [
            'data' => $articles,
            'current_page' => $page,
            'last_page' => max(1, (int) ceil($ids->count() / $perPage)),
            'per_page' => $perPage,
            'total' => $ids->count(),
            'links' => [],
        ];
    }

    private function collectCommentIds($comments): array
    {
        $ids = [];
        foreach ($comments as $comment) {
            $ids[] = $comment->id;
            foreach ($comment->children as $child) {
                $ids[] = $child->id;
                foreach ($child->children as $grand) {
                    $ids[] = $grand->id;
                }
            }
        }

        return $ids;
    }

    private function makeUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $count = Article::where('slug', 'like', $slug.'%')
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->count();

        if ($count > 0) {
            $slug .= '-'.($count + 1);
        }

        return $slug;
    }

    private function normalizeContent(string $content): string
    {
        if (str_contains($content, '<img') || str_contains($content, '<p>')) {
            return $content;
        }

        return collect(preg_split('/\r\n|\r|\n/', $content))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->map(fn ($p) => '<p>'.e($p).'</p>')
            ->implode('');
    }

    private function syncPublicationTimestamp(Article $article, bool $wasPublished): void
    {
        if ($article->is_published) {
            if (! $article->published_at || ! $wasPublished) {
                $article->published_at = now();
                $article->saveQuietly();
            }
        } elseif ($article->published_at !== null) {
            $article->published_at = null;
            $article->saveQuietly();
        }
    }

    private function normalizeArticleRequest(Request $request): void
    {
        $request->merge([
            'category_id' => $request->filled('category_id') ? $request->category_id : null,
            'tag_ids' => $request->input('tag_ids', []),
        ]);
    }

    private function normalizeIdList(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $ids = is_array($value) ? $value : explode(',', (string) $value);

        return array_values(array_filter(array_map('intval', $ids)));
    }

    private function deleteStoredFile(?string $url): void
    {
        if (! $url) {
            return;
        }

        $path = str_replace('/storage/', '', parse_url($url, PHP_URL_PATH) ?? '');
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
