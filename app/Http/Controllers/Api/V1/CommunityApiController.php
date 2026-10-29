<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityPostReplyRequest;
use App\Http\Requests\CommunityPostRequest;
use App\Models\CommunityPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommunityApiController extends Controller
{
    public function index(): JsonResponse
    {
        $posts = CommunityPost::query()
            ->whereNull('parent_id')
            ->with(['user:id,name', 'replies.user:id,name'])
            ->latest()
            ->orderByDesc('id')
            ->paginate(8);

        return response()->json([
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function store(CommunityPostRequest $request): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $post = $request->user()->communityPosts()->create($request->validated());

        return response()->json([
            'message' => 'Pesan berhasil dikirim ke komunitas.',
            'data' => $post->fresh(['user:id,name']),
        ], 201);
    }

    public function reply(CommunityPostReplyRequest $request, CommunityPost $post): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $rootPost = $post->parent_id ? $post->parent : $post;
        if ($rootPost === null) {
            return response()->json(['message' => 'Postingan tidak ditemukan.'], 404);
        }

        $reply = $rootPost->replies()->create([
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'area' => $request->user()->location?->name ?? 'Pesisir',
            'message' => $request->validated('message'),
        ]);

        return response()->json([
            'message' => 'Balasan berhasil dikirim.',
            'data' => $reply->fresh(['user:id,name']),
        ], 201);
    }

    public function destroy(CommunityPost $post): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        Gate::authorize('delete', $post);
        $post->delete();

        return response()->json([
            'message' => 'Pesan dihapus.',
        ]);
    }
}
