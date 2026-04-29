<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SiemolaLayout extends Component
{
    public function __construct(
        public string $title = 'Dashboard',
        public string $activeMenu = 'Dashboard',
        public string $userRole = 'Admin',
        public string $sidebarNote = 'SIEMOLA dipakai untuk menghubungkan RFID, locker, dan riwayat peminjaman alat kampus.',
        public bool $autoRefresh = false,
        public int $autoRefreshInterval = 5000,
    ) {
    }

    public function render(): View|Closure|string
    {
        return view('layouts.siemola');
    }
}
