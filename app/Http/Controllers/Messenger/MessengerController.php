<?php

namespace App\Http\Controllers\Messenger;

use App\Http\Controllers\Controller;
use App\Events\MessageSent;
use App\Http\Requests\Messenger\Messages\StoreMessageRequest;
use App\Models\Message;
use App\Models\User;
use App\Services\Messenger\Messages\ChatService;
use App\Services\Messenger\Users\ListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MessengerController extends Controller
{

    public function __construct(
        protected ChatService $chatService,
        protected ListService $listService
    ) {}

    public function index(): \Inertia\Response
    {
        $users = $this->listService->getUsersWithUnreadCount(Auth::id());

        return Inertia::render('Dashboard', [
            'users' => $users,
        ]);
    }

    public function getUsersForPage(Request $request): \Illuminate\Http\JsonResponse
    {
        $page = $request->integer('page', 1);
        $usersPaginator = $this->listService->getUsersWithUnreadCount(Auth::id(), $page);

        return response()->json($usersPaginator);
    }

    public function markAsRead(User $user): \Illuminate\Http\JsonResponse
    {
        $this->chatService->markAsRead($user->id);

        return response()->json(['status' => 'success']);
    }

    public function getMessages(User $user): \Illuminate\Http\JsonResponse
    {
        $messages = $this->chatService->getMessages($user);

        return response()->json($messages);
    }

    public function sendMessage(StoreMessageRequest $request): \Illuminate\Http\JsonResponse
    {
        $message = $this->chatService->sendMessage($request->validated());

        return response()->json($message);
    }
}
