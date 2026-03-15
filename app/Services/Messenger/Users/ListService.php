<?php

namespace App\Services\Messenger\Users;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListService
{
    const ITEMS_PER_PAGE = 16;

    public function getUsersWithUnreadCount(int $currentUserId, $page = 1): LengthAwarePaginator
    {
        return User::where('id', '!=', $currentUserId)
            ->withCount(['messagesSent as unread_count' => function ($query) use ($currentUserId) {
                $query->where('receiver_id', $currentUserId)
                ->whereNull('read_at');
            }])
            ->paginate(self::ITEMS_PER_PAGE, ['*'], 'page', $page);
    }
}
