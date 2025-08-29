<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the posts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $posts = Post::with('user')->get();

        return response()->json([
            'data' => $posts,
            'message' => 'Posts retrieved successfully'
        ]);
    }
}
