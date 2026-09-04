<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Http\Requests\ComplaintResponseRequest;
use App\Http\Requests\StoreComplaintRequest;
use App\Models\Complaint;
use App\Models\User;
use App\Notifications\ComplaintResponded;
use App\Notifications\ComplaintSubmitted;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    /**
     * Admins see every complaint; Travelers and Travel Partners see only
     * their own.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Complaint::class);

        $user = $request->user();
        $isAdmin = $user->hasRole(UserRole::ADMIN->value);

        $status = in_array($request->query('status'), ComplaintStatus::values(), true)
            ? $request->query('status')
            : null;

        $complaints = Complaint::query()
            ->when(! $isAdmin, fn ($query) => $query->where('user_id', $user->id))
            ->with(['user', 'booking'])
            ->when($status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('complaints.index', [
            'complaints' => $complaints,
            'status' => $status,
            'statuses' => ComplaintStatus::values(),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Complaint::class);

        $bookings = $request->user()->hasRole(UserRole::TRAVELER->value)
            ? $request->user()->bookings()->latest()->get()
            : collect();

        return view('complaints.create', [
            'categories' => ComplaintCategory::values(),
            'bookings' => $bookings,
        ]);
    }

    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        Gate::authorize('create', Complaint::class);

        $complaint = $request->user()->complaints()->create($request->validated() + [
            'status' => ComplaintStatus::OPEN->value,
        ]);

        Notification::send(
            User::role(UserRole::ADMIN->value)->get(),
            new ComplaintSubmitted($complaint->load('user')),
        );

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('status', 'Your complaint has been submitted. Our support team will respond soon.');
    }

    public function show(Complaint $complaint): View
    {
        Gate::authorize('view', $complaint);

        return view('complaints.show', [
            'complaint' => $complaint->load(['user', 'booking', 'resolver']),
            'statuses' => ComplaintStatus::values(),
        ]);
    }

    /**
     * An Admin writes a response and moves the complaint to a new status.
     */
    public function respond(ComplaintResponseRequest $request, Complaint $complaint): RedirectResponse
    {
        Gate::authorize('respond', Complaint::class);

        $data = $request->validated();
        $isSettled = in_array($data['status'], [ComplaintStatus::RESOLVED->value, ComplaintStatus::CLOSED->value], true);

        $complaint->update([
            'status' => $data['status'],
            'admin_response' => $data['admin_response'],
            'resolved_by' => $isSettled ? $request->user()->id : null,
            'resolved_at' => $isSettled ? now() : null,
        ]);

        $complaint->user->notify(new ComplaintResponded($complaint));

        return redirect()
            ->route('complaints.show', $complaint)
            ->with('status', 'Response sent.');
    }
}
