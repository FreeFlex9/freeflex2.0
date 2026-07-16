<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificacoesController extends Controller
{
    public function marcarLida(string $id)
    {
        Auth::guard('admin')->user()
            ->notifications()
            ->where('id', $id)
            ->first()
            ?->markAsRead();

        return back();
    }

    public function marcarTodasLidas()
    {
        Auth::guard('admin')->user()->unreadNotifications->markAsRead();

        return back();
    }
}
