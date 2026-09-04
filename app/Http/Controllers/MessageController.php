<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * The authenticated user's inbox — every conversation they participate
     * in, most recently active first.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Conversation::class);

        $conversations = Conversation::query()
            ->forParticipant($request->user())
            ->with(['traveler', 'partner', 'latestMessage'])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->paginate(15);

        return view('messages.index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Start a new conversation with a recipient (or reuse the existing one),
     * post the opening message, and land on the thread.
     *
     * Which side is "traveler" and which is "partner" is derived from the
     * two users' roles — a Traveler always messages a Travel Partner and
     * vice versa, never a peer of the same role.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Conversation::class);

        $data = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id', 'different:'.$request->user()->id],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $recipient = User::findOrFail($data['recipient_id']);

        [$travelerId, $partnerId] = $this->resolveParticipants($user, $recipient);

        $conversation = Conversation::firstOrCreate([
            'traveler_id' => $travelerId,
            'partner_id' => $partnerId,
        ]);

        // A "Message Provider" shortcut (e.g. from a booking page) reuses the
        // existing thread without re-posting its canned opener every visit —
        // only a genuinely new conversation gets the opening message.
        if ($conversation->wasRecentlyCreated && $data['body']) {
            $message = $this->postMessage($conversation, $user, $data['body']);
            $conversation->other($user)->notify(new NewMessageReceived($message));
        }

        return redirect()
            ->route('messages.show', $conversation)
            ->with('status', 'Message sent.');
    }

    /**
     * Show a conversation thread and mark the other participant's messages
     * as read.
     */
    public function show(Request $request, Conversation $conversation): View
    {
        Gate::authorize('view', $conversation);

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('messages.show', [
            'conversation' => $conversation->load(['traveler', 'partner', 'messages.sender']),
        ]);
    }

    /**
     * Post a reply into an existing conversation.
     */
    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        Gate::authorize('view', $conversation);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $message = $this->postMessage($conversation, $user, $data['body']);
        $conversation->other($user)->notify(new NewMessageReceived($message));

        return redirect()->route('messages.show', $conversation);
    }

    private function postMessage(Conversation $conversation, User $sender, string $body): Message
    {
        $message = $conversation->messages()->create([
            'sender_id' => $sender->id,
            'body' => $body,
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        return $message;
    }

    /**
     * @return array{0: int, 1: int} [traveler_id, partner_id]
     */
    private function resolveParticipants(User $user, User $recipient): array
    {
        if ($user->hasRole(UserRole::TRAVELER->value)) {
            abort_unless($recipient->hasRole(UserRole::TRAVEL_PARTNER->value), 422, 'You can only message a Travel Partner.');

            return [$user->id, $recipient->id];
        }

        abort_unless($user->hasRole(UserRole::TRAVEL_PARTNER->value), 403);
        abort_unless($recipient->hasRole(UserRole::TRAVELER->value), 422, 'You can only message a Traveler.');

        return [$recipient->id, $user->id];
    }
}
