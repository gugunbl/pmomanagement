<div class="card border-0">
    <div class="card-body">

        <h6 class="heading-small text-muted mb-3">TOP Maintenance</h6>

        {{-- TOTAL TOP MAINTENANCE --}}
        <div class="form-group col-md-3 px-0">
            <label>Total TOP Maintenance</label>
            <input type="number"
                   name="top_maintenance_total"
                   class="form-control"
                   min="1"
                   max="60"
                   value="{{ old('top_maintenance_total', $project->top_maintenance_total ?? 12) }}">
        </div>

        @php
            $total = old(
                'top_maintenance_total',
                $project->top_maintenance_total ?? 12
            );
        @endphp

        <table class="table table-sm table-bordered mt-3">
            <thead class="thead-light">
                <tr>
                    <th>TOP Ke</th>
                    <th>Periode Start</th>
                    <th>Periode End</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>

            @for($i = 1; $i <= $total; $i++)
                @php
                    $row = $project->topMaintenance->firstWhere('top_ke', $i);
                    $currentStatus = old("top_maintenance.$i.status", $row->status ?? 'planned');
                    $isPaid = ($currentStatus === 'paid');
                @endphp

                <tr class="{{ $isPaid ? 'table-success' : '' }}">
                    <td>{{ $i }}</td>

                    <td>
                        <input type="date"
                               name="top_maintenance[{{ $i }}][start]"
                               value="{{ old("top_maintenance.$i.start", $row->periode_start ?? '') }}"
                               class="form-control"
                               {{ $isPaid ? 'disabled' : '' }}>
                        @if($isPaid)
                            <input type="hidden" name="top_maintenance[{{ $i }}][start]" value="{{ $row->periode_start ?? '' }}">
                        @endif
                    </td>

                    <td>
                        <input type="date"
                               name="top_maintenance[{{ $i }}][end]"
                               value="{{ old("top_maintenance.$i.end", $row->periode_end ?? '') }}"
                               class="form-control"
                               {{ $isPaid ? 'disabled' : '' }}>
                        @if($isPaid)
                            <input type="hidden" name="top_maintenance[{{ $i }}][end]" value="{{ $row->periode_end ?? '' }}">
                        @endif
                    </td>

                    <td>
                        <select name="top_maintenance[{{ $i }}][status]"
                                class="form-control top-maintenance-status"
                                data-row="{{ $i }}"
                                data-old-status="{{ $currentStatus }}"
                                {{ $isPaid ? 'disabled' : '' }}>
                            <option value="planned"   {{ $currentStatus=='planned'?'selected':'' }}>Planned</option>
                            <option value="submitted" {{ $currentStatus=='submitted'?'selected':'' }}>Submitted</option>
                            <option value="revision"  {{ $currentStatus=='revision'?'selected':'' }}>Revision</option>
                            <option value="paid"      {{ $currentStatus=='paid'?'selected':'' }}>Paid</option>
                        </select>
                        @if($isPaid)
                            <input type="hidden" name="top_maintenance[{{ $i }}][status]" value="paid">
                        @endif
                    </td>
                    <td>
                        <textarea name="top_maintenance[{{ $i }}][keterangan]"
                                  class="form-control top-maintenance-keterangan"
                                  data-row="{{ $i }}"
                                  rows="1"
                                  {{ $isPaid ? 'disabled' : '' }}>{{ old("top_maintenance.$i.keterangan", $row->keterangan ?? '') }}</textarea>
                        @if($isPaid)
                            <input type="hidden" name="top_maintenance[{{ $i }}][keterangan]" value="{{ $row->keterangan ?? '' }}">
                        @endif
                    </td>
                </tr>
            @endfor

            </tbody>
        </table>

    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Format date as "22 Apr 2026"
    function formatNarrationDate() {
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var d = new Date();
        return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
    }

    // Map status to narration label
    var statusLabels = {
        'submitted': 'Submitted',
        'revision': 'Revision',
        'paid': 'Paid'
    };

    $(document).on('change', '.top-maintenance-status', function() {
        var newStatus = $(this).val();
        var oldStatus = $(this).data('old-status');
        var rowIndex = $(this).data('row');
        var keteranganEl = $('.top-maintenance-keterangan[data-row="' + rowIndex + '"]');

        // Only append narration when changing TO submitted/revision/paid (not when already that status)
        if (newStatus !== oldStatus && statusLabels[newStatus]) {
            var narration = statusLabels[newStatus] + ' ' + formatNarrationDate();
            var currentText = keteranganEl.val().trim();
            if (currentText) {
                keteranganEl.val(currentText + '\n' + narration);
            } else {
                keteranganEl.val(narration);
            }
        }

        // Toggle green highlight for paid status
        var row = $(this).closest('tr');
        if (newStatus === 'paid') {
            row.addClass('table-success');
        } else {
            row.removeClass('table-success');
        }

        // Update the tracked old status
        $(this).data('old-status', newStatus);
    });
});
</script>
@endpush
