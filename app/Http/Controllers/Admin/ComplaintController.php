<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ComplaintActionRequest;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use App\Notifications\AccountStatusChanged;
use App\Notifications\ComplaintUpdated;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['open', 'warned', 'blocked', 'dismissed'])],
        ]);
        $complaints = Complaint::with(['complainant', 'against'])
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.complaints.index', compact('complaints'));
    }

    public function show(Complaint $complaint): View
    {
        $complaint->load(['complainant', 'against']);
        return view('admin.complaints.show', compact('complaint'));
    }

    public function warn(ComplaintActionRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint = DB::transaction(function () use ($request, $complaint) {
            $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);
            abort_unless($lockedComplaint->status === 'open', 422, 'Only open complaints can be actioned.');
            $lockedComplaint->status = 'warned';
            $lockedComplaint->admin_note = $request->validated('admin_note');
            $lockedComplaint->save();

            return $lockedComplaint->load('complainant');
        });
        $complaint->complainant->notify(new ComplaintUpdated($complaint));

        return back()->with('success', 'Complaint marked as warned.');
    }

    public function block(ComplaintActionRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint = DB::transaction(function () use ($request, $complaint) {
            $lockedComplaint = Complaint::query()
                ->lockForUpdate()
                ->findOrFail($complaint->id);
            abort_unless($lockedComplaint->status === 'open', 422, 'Only open complaints can be actioned.');
            $reportedUser = User::query()->lockForUpdate()->findOrFail($lockedComplaint->against_user_id);
            abort_if($reportedUser->role === 'admin', 403, 'Admin accounts cannot be blocked.');

            $reason = $request->validated('blocked_reason') ?: 'Blocked following an accepted complaint.';
            $reportedUser->status = 'blocked';
            $reportedUser->blocked_reason = $reason;
            $reportedUser->save();
            $reportedUser->laptops()->where('status', 'available')->update(['status' => 'blocked']);
            $lockedComplaint->status = 'blocked';
            $lockedComplaint->admin_note = $request->validated('admin_note');
            $lockedComplaint->save();

            return $lockedComplaint->load(['against', 'complainant']);
        });
        $complaint->against->notify(new AccountStatusChanged('blocked'));
        $complaint->complainant->notify(new ComplaintUpdated($complaint));

        return back()->with('success', 'Reported user blocked and complaint resolved.');
    }

    public function dismiss(ComplaintActionRequest $request, Complaint $complaint): RedirectResponse
    {
        $complaint = DB::transaction(function () use ($request, $complaint) {
            $lockedComplaint = Complaint::query()->lockForUpdate()->findOrFail($complaint->id);
            abort_unless($lockedComplaint->status === 'open', 422, 'Only open complaints can be actioned.');
            $lockedComplaint->status = 'dismissed';
            $lockedComplaint->admin_note = $request->validated('admin_note');
            $lockedComplaint->save();

            return $lockedComplaint->load('complainant');
        });
        $complaint->complainant->notify(new ComplaintUpdated($complaint));

        return back()->with('success', 'Complaint dismissed.');
    }
}
