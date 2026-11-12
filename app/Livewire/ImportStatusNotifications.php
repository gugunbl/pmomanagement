<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ImportStatusNotifications extends Component
{
    public function dismiss(int $id): void
    {
        DB::table('daily_task_report_imports')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['completed', 'failed'])
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function render()
    {
        $imports = DB::table('daily_task_report_imports')
            ->where('user_id', auth()->id())
            ->where(function ($query) {
                $query->whereIn('status', ['queued', 'processing'])
                    ->orWhereNull('read_at');
            })
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'status', 'message']);

        return view('livewire.import-status-notifications', compact('imports'));
    }
}
