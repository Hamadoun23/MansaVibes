<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use Illuminate\View\View;

class CommunicationsWebController extends Controller
{
    public function index(): View
    {
        $logs = NotificationLog::query()->orderByDesc('created_at')->limit(30)->get();

        return view('modules.communications.index', compact('logs'));
    }
}
