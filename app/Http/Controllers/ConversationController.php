<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimulateConversationRequest;
use App\Services\ConversationService;
use Illuminate\Http\RedirectResponse;

class ConversationController extends Controller
{
    protected ConversationService $conversationService;

    public function __construct(ConversationService $conversationService)
    {
        $this->conversationService = $conversationService;
    }

    public function simulate(SimulateConversationRequest $request): RedirectResponse
    {
        $result = $this->conversationService->startSimulation(
            $request->input('from_email'),
            $request->input('to_email')
        );

        return back()->with($result['status'], $result['message']);
    }
}
