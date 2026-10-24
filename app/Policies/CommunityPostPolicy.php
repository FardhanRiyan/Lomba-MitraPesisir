<?php

namespace App\Policies;

use App\Models\CommunityPost;
use App\Models\User;

class CommunityPostPolicy
{
    /** Penulis pesan atau admin (moderasi) boleh menghapus. */
    public function delete(User $user, CommunityPost $post): bool
    {
        return $user->isAdmin() || $post->user_id === $user->id;
    }
}
