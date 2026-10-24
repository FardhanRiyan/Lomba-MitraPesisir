<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    /** Pemilik laporan atau admin boleh mengubah/menghapus. */
    public function update(User $user, Report $report): bool
    {
        return $user->isAdmin() || $report->user_id === $user->id;
    }

    public function delete(User $user, Report $report): bool
    {
        return $this->update($user, $report);
    }

    /** Mengubah status tindak lanjut khusus admin. */
    public function updateStatus(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }
}
