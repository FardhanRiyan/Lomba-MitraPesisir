<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommunityPostReplyRequest;
use App\Http\Requests\CommunityPostRequest;
use App\Models\CommunityPost;
use App\Support\CurrentLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Papan komunitas pesisir: baca publik, kirim/balas setelah login. */
class CommunityController extends Controller
{
    public function __construct(
        private readonly CurrentLocation $current,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $location = $this->current->get();
        $q = $this->textFilter($request->query('q'));

        $posts = CommunityPost::query()
            ->whereNull('parent_id')
            ->with(['user:id,name', 'replies.user:id,name'])
            ->search($q)
            ->latest()->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('komunitas.index', [
            'posts' => $posts,
            'q' => $q,
            'defaultName' => $user?->name ?? '',
            'defaultArea' => $location?->name ?? '',
            'prefillMessage' => '',
        ]);
    }

    public function store(CommunityPostRequest $request): RedirectResponse
    {
        $request->user()->communityPosts()->create($request->validated());

        return redirect()->route('komunitas.index')->with('success', 'Pesan berhasil dikirim ke komunitas.');
    }

    public function reply(CommunityPostReplyRequest $request, CommunityPost $post): RedirectResponse
    {
        $rootPost = $post->parent_id ? $post->parent : $post;
        abort_if($rootPost === null, 404);

        $user = $request->user();
        $rootPost->replies()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'area' => $user->location?->name ?? 'Pesisir',
            'message' => $request->validated('message'),
        ]);

        return redirect()->route('komunitas.index')->with('success', 'Balasan berhasil dikirim.');
    }

    public function destroy(CommunityPost $post): RedirectResponse
    {
        Gate::authorize('delete', $post);
        $post->delete();

        return redirect()->back()->with('success', 'Pesan dihapus.');
    }
}
