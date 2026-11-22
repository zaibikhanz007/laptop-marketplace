<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function create(User $user): View
    {
        Gate::authorize('create', $user);

        return view('complaints.create', ['against' => $user]);
    }

    public function store(StoreComplaintRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('create', $user);

        DB::transaction(function () use ($request, $user) {
            User::query()->lockForUpdate()->findOrFail($request->user()->id);
            Gate::authorize('create', $user);

            $complaint = new Complaint([
                'subject' => $request->validated('subject'),
                'description' => $request->validated('description'),
            ]);
            $complaint->complainant()->associate($request->user());
            $complaint->against()->associate($user);
            $complaint->status = 'open';
            $complaint->save();
        });

        return redirect()->route('complaints.index')->with('success', 'Complaint submitted for moderation.');
    }

    public function index(): View
    {
        $complaints = request()->user()->complaintsFiled()
            ->with('against')
            ->latest()
            ->paginate(10);

        return view('complaints.index', compact('complaints'));
    }
}
