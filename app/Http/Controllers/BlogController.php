<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Blog');
    }

    public function show(Request $request, string $slug): Response
    {
        $post = BlogPost::query()->where('slug', $slug)->first();

        if ($post) {
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
        }

        return Inertia::render('Blog');
    }
}
