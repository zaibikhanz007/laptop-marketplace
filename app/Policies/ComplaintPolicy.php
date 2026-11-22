<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function create(User $complainant, User $against): bool
    {
        if ($complainant->status !== 'active') {
            return false;
        }

        if ($complainant->id === $against->id) {
            return false;
        }

        return Complaint::where('complainant_id', $complainant->id)
            ->where('against_user_id', $against->id)
            ->where('status', 'open')
            ->count() < 3;
    }

    public function viewOwn(User $user, Complaint $complaint): bool
    {
        return $complaint->complainant_id === $user->id;
    }
}
