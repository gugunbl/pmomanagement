@extends('layouts.app')

@section('content')
@php
    $baGroups = $listBA->getCollection()->groupBy('project_id');
    $projectCounter = $startProjectCounter ?? 0;
@endphp

<div class="monitor-workspace ba-workspace">
    <section class="monitor-overview ba-overview">
        <div class="monitor-overview-copy">
            <span class="monitor-eyebrow"><i class="fas fa-file-signature"></i> Berita Acara Maintenance</span>
            <h2>Monitoring BA</h2>
            <p>Pantau jatuh tempo TOP, progres signer, status BA, serta tindak lanjut setiap project.</p>
        </div>
        <div class="monitor-stat-grid ba-stat-grid">
            <div class="monitor-stat"><span>Project</span><strong>{{ $totalProjects }}</strong></div>
            <div class="monitor-stat is-info"><span>Total BA</span><strong>{{ $totalBA }}</strong></div>
        </div>
    </section>

    <section class="ba-control-panel">
        <div class="ba-tabs" role="tablist" aria-label="Status periode BA">
            <a class="{{ $activeTab === 'progress' ? 'active' : '' }}" href="{{ route('project.ba.monitoring', array_merge(request()->query(), ['tab' => 'progress', 'page' => null])) }}"><i class="fas fa-exclamation-circle"></i><span>Progress BA<small>Jatuh tempo / overdue</small></span></a>
            <a class="{{ $activeTab === 'open' ? 'active' : '' }}" href="{{ route('project.ba.monitoring', array_merge(request()->query(), ['tab' => 'open', 'page' => null])) }}"><i class="far fa-clock"></i><span>BA Open<small>Periode mendatang</small></span></a>
        </div>

        <form action="{{ route('project.ba.monitoring') }}" method="GET" class="ba-filter-form">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="app-filter-field">
                <label for="ba_pm">PM Maintenance</label>
                <select id="ba_pm" name="pm_maintenance" class="form-control">
                    <option value="">Semua PM</option>
                    @foreach($listPM as $pm)
                        <option value="{{ $pm->id_pm }}" @selected((string) $pm_maintenance === (string) $pm->id_pm)>{{ $pm->nama_pm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="app-filter-field">
                <label for="ba_search">Cari Project</label>
                <input id="ba_search" type="search" name="search" class="form-control" placeholder="Nama project..." value="{{ $search }}">
            </div>
            <div class="ba-filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i><span>Cari</span></button>
                <a href="{{ route('project.ba.monitoring', ['tab' => $activeTab]) }}" class="btn btn-light" title="Reset filter"><i class="fas fa-undo"></i><span>Reset</span></a>
                <a href="{{ route('project.ba.export.pdf', array_merge(request()->query(), ['tab' => $activeTab])) }}" class="btn btn-outline-danger"><i class="far fa-file-pdf"></i><span>PDF</span></a>
                <a href="{{ route('project.ba.export.excel', array_merge(request()->query(), ['tab' => $activeTab])) }}" class="btn btn-outline-success"><i class="far fa-file-excel"></i><span>Excel</span></a>
            </div>
        </form>

        <div class="ba-info-note"><i class="fas fa-info-circle"></i><div><strong>Signer dinamis</strong><span>Nama signer mengikuti field “Nama User Tanda Tangan” pada project. Hijau berarti selesai, kuning berarti menunggu.</span></div></div>
    </section>

    <div class="monitor-section-heading">
        <div><h3>{{ $activeTab === 'progress' ? 'BA perlu tindak lanjut' : 'BA periode mendatang' }}</h3><p>Urutan berdasarkan tanggal akhir BA terdekat.</p></div>
        <span>{{ $listBA->firstItem() ?? 0 }}–{{ $listBA->lastItem() ?? 0 }} dari {{ $listBA->total() }}</span>
    </div>

    <div class="monitor-project-list ba-project-list">
        @forelse($baGroups as $projectId => $projectBAs)
            @php
                $projectCounter++;
                $firstBA = $projectBAs->first();
                $project = $firstBA->project;
                $latestComment = $project->comments->first();
            @endphp
            <article class="monitor-project-card ba-project-card">
                <header class="monitor-project-header">
                    <div class="monitor-project-number">{{ str_pad($projectCounter, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="monitor-project-identity">
                        <span>Project</span>
                        <h3><a href="{{ route('project.edit', $projectId) }}">{{ $project->nama_project ?? '-' }}</a></h3>
                        <div class="monitor-project-meta">
                            <span><i class="far fa-file-alt"></i>{{ $project->nomor_spk ?? 'SPK belum tersedia' }}</span>
                            <span><i class="far fa-user"></i>{{ $project->mPMMaintenance->nama_pm ?? 'PM belum ditentukan' }}</span>
                            <span><i class="fas fa-layer-group"></i>{{ $projectBAs->count() }} TOP</span>
                        </div>
                    </div>
                    <div class="monitor-comment-summary">
                        @if($latestComment)
                            <span class="monitor-comment-label">Komentar terakhir</span>
                            <p>{{ Str::limit($latestComment->comment, 100) }}</p>
                            <small>{{ $latestComment->user->name ?? 'Unknown' }} · {{ optional($latestComment->created_at)->format('d M Y H:i') }}</small>
                        @else
                            <span class="monitor-comment-empty"><i class="far fa-comment-dots"></i> Belum ada komentar</span>
                        @endif
                        <button type="button" class="btn monitor-comment-button" data-toggle="modal" data-target="#projectCommentsModal{{ $projectId }}"><i class="far fa-comments"></i> Komentar ({{ $project->comments->count() }})</button>
                    </div>
                </header>

                <div class="ba-record-head ba-record-grid"><span>TOP</span><span>Periode BA</span><span>Status</span><span>Progress signer</span></div>
                <div class="monitor-record-list">
                    @foreach($projectBAs as $ba)
                        @php
                            $endDate = $ba->periode_end ? \Carbon\Carbon::parse($ba->periode_end) : null;
                            $urgencyClass = '';
                            if ($activeTab === 'progress' && $endDate) {
                                $urgencyClass = $endDate->lt(now()->subMonths(3)) ? 'is-severely-overdue' : ($endDate->lte(now()) ? 'is-overdue' : '');
                            }
                            $projectSignerTotal = (int) ($project->top_maintenance_signer_total ?? 0);
                            $selectedSigners = json_decode($ba->signers ?? '[]', true) ?: [];
                            $isPaid = $ba->status === 'paid';
                            $signerLabels = !empty($project->top_maintenance_notes) ? array_map('trim', explode(';', $project->top_maintenance_notes)) : [];
                        @endphp
                        <div class="ba-record ba-record-grid {{ $urgencyClass }}">
                            <div data-label="TOP"><span class="monitor-record-index">TOP {{ $ba->top_ke }}</span></div>
                            <div data-label="Periode BA" class="ba-period">
                                <span><small>Mulai</small>{{ $ba->periode_start ? \Carbon\Carbon::parse($ba->periode_start)->format('d M Y') : '-' }}</span>
                                <i class="fas fa-arrow-right"></i>
                                <span><small>Selesai</small>{{ $endDate ? $endDate->format('d M Y') : '-' }}</span>
                                @if($urgencyClass)<em><i class="fas fa-exclamation-triangle"></i>{{ $urgencyClass === 'is-severely-overdue' ? 'Overdue > 3 bulan' : 'Jatuh tempo' }}</em>@endif
                            </div>
                            <div data-label="Status">
                                <select class="form-control editable monitor-status-select status-{{ $ba->status }}" data-id="{{ $ba->id }}" data-field="status" aria-label="Status TOP {{ $ba->top_ke }}">
                                    <option value="planned" @selected($ba->status === 'planned')>Planned</option>
                                    <option value="submitted" @selected($ba->status === 'submitted')>Submitted</option>
                                    <option value="revision" @selected($ba->status === 'revision')>Revision</option>
                                    <option value="paid" @selected($ba->status === 'paid')>Paid</option>
                                </select>
                            </div>
                            <div data-label="Signer" class="ba-signers">
                                @if($projectSignerTotal > 0)
                                    @for($signerIndex = 1; $signerIndex <= $projectSignerTotal; $signerIndex++)
                                        @php($checked = in_array($signerIndex, $selectedSigners))
                                        <label class="ba-signer {{ $checked ? 'is-complete' : 'is-pending' }}" for="signer_checkbox_{{ $ba->id }}_{{ $signerIndex }}">
                                            <input type="checkbox" class="project-signer-checkbox" id="signer_checkbox_{{ $ba->id }}_{{ $signerIndex }}" data-ba-id="{{ $ba->id }}" data-signer-index="{{ $signerIndex }}" @checked($checked) @disabled($isPaid) value="1">
                                            <span><i class="fas {{ $checked ? 'fa-check' : 'fa-hourglass-half' }}"></i>{{ $signerLabels[$signerIndex - 1] ?? 'User '.$signerIndex }}</span>
                                        </label>
                                    @endfor
                                @else
                                    <span class="monitor-action-hint"><i class="fas fa-user-slash"></i> Signer belum diatur</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <div class="modal fade monitor-modal" id="projectCommentsModal{{ $projectId }}" tabindex="-1" role="dialog" aria-labelledby="projectCommentsModalLabel{{ $projectId }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div><span class="monitor-eyebrow">Diskusi project</span><h5 class="modal-title" id="projectCommentsModalLabel{{ $projectId }}">{{ Str::limit($project->nama_project ?? '-', 60) }}</h5></div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="monitor-modal-project-meta three-columns">
                                <div><span>Nama project</span><strong>{{ $project->nama_project ?? '-' }}</strong></div>
                                <div><span>Nomor SPK</span><strong>{{ $project->nomor_spk ?? '-' }}</strong></div>
                                <div><span>PM Maintenance</span><strong>{{ $project->mPMMaintenance->nama_pm ?? '-' }}</strong></div>
                            </div>
                            <div class="table-responsive monitor-modal-table">
                                <table class="table table-sm mb-0"><thead><tr><th>TOP</th><th>Mulai</th><th>Selesai</th><th>Status</th></tr></thead><tbody>
                                    @foreach($projectBAs as $pendingBA)
                                        <tr><td>TOP {{ $pendingBA->top_ke }}</td><td>{{ $pendingBA->periode_start ? \Carbon\Carbon::parse($pendingBA->periode_start)->format('d M Y') : '-' }}</td><td>{{ $pendingBA->periode_end ? \Carbon\Carbon::parse($pendingBA->periode_end)->format('d M Y') : '-' }}</td><td><span class="monitor-status-badge status-{{ $pendingBA->status }}">{{ ucfirst($pendingBA->status) }}</span></td></tr>
                                    @endforeach
                                </tbody></table>
                            </div>
                            <div class="monitor-comment-history">
                                <h6>Riwayat komentar</h6>
                                @forelse($project->comments as $comment)
                                    <div class="monitor-comment-item"><div class="monitor-avatar">{{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}</div><div><div class="monitor-comment-author"><strong>{{ $comment->user->name ?? 'Unknown' }}</strong><time>{{ optional($comment->created_at)->format('d M Y H:i') }}</time></div><p>{!! nl2br(e($comment->comment)) !!}</p></div></div>
                                @empty
                                    <div class="monitor-empty"><i class="far fa-comments"></i><span>Belum ada komentar untuk project ini.</span></div>
                                @endforelse
                            </div>
                            <form action="{{ route('project.comments.store', $projectId) }}" method="POST" class="monitor-comment-form">
                                @csrf
                                <label for="comment_project_{{ $projectId }}">Tambahkan komentar</label>
                                <textarea id="comment_project_{{ $projectId }}" name="comment" class="form-control" rows="3" placeholder="Tulis komentar atau alasan perubahan status..."></textarea>
                                <input type="hidden" name="tab" value="{{ $activeTab }}">
                                <div class="text-right mt-3"><button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan komentar</button></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="monitor-empty-state"><i class="far fa-folder-open"></i><h3>Tidak ada data BA</h3><p>Coba ubah tab atau filter yang digunakan.</p></div>
        @endforelse
    </div>

    <div class="app-pagination monitor-pagination"><span class="app-pagination-summary">Menampilkan {{ $listBA->firstItem() ?? 0 }}–{{ $listBA->lastItem() ?? 0 }} dari {{ $listBA->total() }} BA</span>{{ $listBA->withQueryString()->links() }}</div>
</div>

<div id="save-notification" class="save-toast"><i class="fas fa-check-circle mr-2"></i> Data berhasil disimpan</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Pastikan modal dan backdrop berada pada stacking context yang sama.
    $('.monitor-modal').appendTo(document.body);

    function showNotification() {
        var toast = $('#save-notification');
        toast.addClass('show');
        setTimeout(function () { toast.removeClass('show'); }, 3000);
    }

    $('.editable').on('change', function () {
        var element = $(this);
        element.addClass('is-loading');
        $.ajax({
            url: '/project/monitoring-ba/' + element.data('id') + '/update',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', status: element.val(), field: element.data('field') },
            success: function (response) { if (response.success) { showNotification(); if (response.reload) location.reload(); } },
            error: function (xhr) { alert('Error updating data'); console.error(xhr); },
            complete: function () { element.removeClass('is-loading'); }
        });
    });

    $(document).on('change', '.project-signer-checkbox', function () {
        var checkbox = $(this);
        var record = checkbox.closest('.ba-record');
        var selected = [];
        record.find('.project-signer-checkbox:checked').each(function () { selected.push($(this).data('signer-index')); });

        $.ajax({
            url: '/project/monitoring-ba/' + checkbox.data('ba-id') + '/signers',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', signers: selected },
            success: function (response) {
                if (!response.success) return;
                if (response.reload) { location.reload(); return; }
                record.find('.project-signer-checkbox').each(function () {
                    var signer = $(this).closest('.ba-signer');
                    var icon = signer.find('i');
                    if ($(this).is(':checked')) {
                        signer.removeClass('is-pending').addClass('is-complete');
                        icon.removeClass('fa-hourglass-half').addClass('fa-check');
                    } else {
                        signer.removeClass('is-complete').addClass('is-pending');
                        icon.removeClass('fa-check').addClass('fa-hourglass-half');
                    }
                });
                showNotification();
            },
            error: function (xhr) { console.error(xhr); }
        });
    });
});
</script>
@endpush
