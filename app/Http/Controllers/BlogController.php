<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $categorySlug = trim((string) $request->string('category')->toString());

        $categories = BlogCategory::query()
            ->withCount(['posts' => fn ($query) => $query->whereNotNull('published_at')->where('published_at', '<=', now())])
            ->whereHas('posts', fn ($query) => $query->whereNotNull('published_at')->where('published_at', '<=', now()))
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (BlogCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => (int) ($category->posts_count ?? 0),
            ])
            ->values();

        $postsQuery = BlogPost::query()
            ->with(['category:id,name,slug'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        if ($categorySlug !== '') {
            $postsQuery->whereHas('category', fn ($query) => $query->where('slug', $categorySlug));
        }

        $featuredPosts = (clone $postsQuery)
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn (BlogPost $post) => $this->serializePost($post))
            ->values();

        $posts = $postsQuery
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (BlogPost $post) => $this->serializePost($post));

        return Inertia::render('Blog', [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'categories' => $categories,
            'post' => null,
            'relatedPosts' => [],
            'activeCategory' => $categorySlug,
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        $post = BlogPost::query()
            ->with(['category:id,name,slug'])
            ->where('slug', $slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $sessionId = (string) $request->session()->getId();
        if ($sessionId !== '') {
            try {
                $recent = DB::table('blog_post_views')
                    ->where('blog_post_id', $post->id)
                    ->where('session_id', $sessionId)
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->exists();

                if (!$recent) {
                    DB::table('blog_post_views')->insert([
                        'blog_post_id' => $post->id,
                        'session_id' => $sessionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable) {
            }
        }

        $categories = BlogCategory::query()
            ->withCount(['posts' => fn ($query) => $query->whereNotNull('published_at')->where('published_at', '<=', now())])
            ->whereHas('posts', fn ($query) => $query->whereNotNull('published_at')->where('published_at', '<=', now()))
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (BlogCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => (int) ($category->posts_count ?? 0),
            ])
            ->values();

        $featuredPosts = BlogPost::query()
            ->with(['category:id,name,slug'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $post->id)
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn (BlogPost $item) => $this->serializePost($item))
            ->values();

        $relatedPosts = BlogPost::query()
            ->with(['category:id,name,slug'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($query) => $query->where('category_id', $post->category_id))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get()
            ->map(fn (BlogPost $item) => $this->serializePost($item))
            ->values();

        $posts = BlogPost::query()
            ->with(['category:id,name,slug'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString()
            ->through(fn (BlogPost $item) => $this->serializePost($item));

        return Inertia::render('Blog', [
            'posts' => $posts,
            'featuredPosts' => $featuredPosts,
            'categories' => $categories,
            'post' => $this->serializePost($post),
            'relatedPosts' => $relatedPosts,
            'activeCategory' => $post->category?->slug,
        ]);
    }

    private function serializePost(BlogPost $post): array
    {
        $image = trim((string) ($post->featured_image ?? ''));
        $imageUrl = $image !== ''
            ? (Str::startsWith($image, ['http://', 'https://', 'data:']) ? $image : url('/media/' . ltrim($image, '/')))
            : 'data:image/svg+xml,' . rawurlencode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="700" viewBox="0 0 1200 700"><rect width="1200" height="700" fill="#111111"/><text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-family="Arial, sans-serif" font-size="48" fill="rgba(255,255,255,0.65)">Blog</text></svg>'
            );

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'url' => '/blog/' . $post->slug,
            'excerpt' => $post->excerpt ?: Str::limit(strip_tags((string) $post->content), 180),
            'content' => $post->content,
            'category' => $post->category?->name ?? 'Blog',
            'category_slug' => $post->category?->slug,
            'published_at' => optional($post->published_at)->format('d/m/Y'),
            'published_at_iso' => optional($post->published_at)->toIso8601String(),
            'featured' => (bool) $post->is_featured,
            'image' => $imageUrl,
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
        ];
    }
}
