<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogViewController extends Controller
{
    public function index()
    {
        $blogs = Blog::with('user')
            // ->where('status', Blog::OPEN)
            ->onlyOpen()
            ->withCount('comments')
            ->orderByDesc('comments_count')
            ->get();

        return view('index', compact('blogs'));
    }

    public function show(Blog $blog)
    {
        if ($blog->status == Blog::CLOSED) {
            abort(403);
        }
        
        return view('blog.show', compact('blog'));
    }
}
