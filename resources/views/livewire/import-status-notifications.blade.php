<div wire:poll.10s class="import-status-stack" aria-live="polite" aria-atomic="false">
    @foreach ($imports as $import)
        @php
            $isActive = in_array($import->status, ['queued', 'processing'], true);
            $isFailed = $import->status === 'failed';
        @endphp

        <div
            wire:key="import-status-{{ $import->id }}"
            class="import-status-notice{{ $isActive ? ' is-active' : '' }}{{ $isFailed ? ' is-failed' : '' }}"
        >
            <span class="import-status-icon">
                <i class="fas {{ $isActive ? 'fa-spinner fa-spin' : ($isFailed ? 'fa-exclamation-circle' : 'fa-check-circle') }}" aria-hidden="true"></i>
            </span>
            <p class="import-status-message">{{ $import->message ?: 'Status import diperbarui.' }}</p>

            @unless ($isActive)
                <button
                    type="button"
                    wire:click="dismiss({{ $import->id }})"
                    class="import-status-close"
                    aria-label="Tutup notifikasi"
                >
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            @endunless
        </div>
    @endforeach
</div>
