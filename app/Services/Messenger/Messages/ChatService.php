<?php

namespace App\Services\Messenger\Messages;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class ChatService
{
    public function getMessages(User $user): Collection
    {
        return Message::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
        })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function sendMessage(array $data): Message
    {
        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'content' => $data['content'],
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    public function markAsRead(int $senderId): void
    {
        Message::where('sender_id', $senderId)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
