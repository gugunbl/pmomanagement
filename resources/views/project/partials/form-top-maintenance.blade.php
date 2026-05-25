<div class="card border-0">
    <div class="card-body">

        <h6 class="heading-small text-muted mb-3">TOP Maintenance</h6>

        {{-- TOTAL TOP MAINTENANCE --}}
        <div class="form-row">
            <div class="form-group col-md-3 px-0">
                <label class="font-weight-bold">Total TOP Maintenance</label>
                <input type="number"
                       name="top_maintenance_total"
                       class="form-control"
                       min="1"
                       max="60"
                       value="{{ old('top_maintenance_total', $project->top_maintenance_total ?? 12) }}">
            </div>

            <div class="form-group col-md-3 px-0">
                <label class="font-weight-bold">Jumlah User Tanda Tangan</label>
                <input type="number"
                       id="top_maintenance_signer_total"
                       name="top_maintenance_signer_total"
                       class="form-control"
                       min="0"
                       max="10"
                       value="{{ old('top_maintenance_signer_total', $project->top_maintenance_signer_total ?? 0) }}">
            </div>

            <div class="form-group col-md-6 px-0">
                <label class="font-weight-bold">Nama User Tanda Tangan</label>
                <input type="text"
                       name="top_maintenance_notes"
                       class="form-control"
                       placeholder="Misal: Team Lead SCR; Dept Head; Group Head INF"
                       value="{{ old('top_maintenance_notes', $project->top_maintenance_notes ?? '') }}">
                <small class="form-text text-muted">Pisahkan dengan titik koma (;) sesuai jumlah user tanda tangan</small>
            </div>
        </div>

        @php
            $total = old(
                'top_maintenance_total',
                $project->top_maintenance_total ?? 12
            );

            $savedSignerValues = [];
            foreach ($project->topMaintenance as $signerRow) {
                $savedSignerValues[$signerRow->top_ke] = $signerRow->signers ? array_flip(json_decode($signerRow->signers, true) ?? []) : [];
            }
        @endphp

        <table class="table table-sm table-bordered mt-3">
            <thead class="thead-light">
                <tr>
                    <th>TOP Ke</th>
                    <th>Periode Start</th>
                    <th>Periode End</th>
                    <th>Status</th>
                    <th>Checklist Tanda Tangan</th>
                </tr>
            </thead>
            <tbody>

            @for($i = 1; $i <= $total; $i++)
                @php
                    $row = $project->topMaintenance->firstWhere('top_ke', $i);
                    $currentStatus = old("top_maintenance.$i.status", $row->status ?? 'planned');
                    $isPaid = ($currentStatus === 'paid');
                @endphp

                <tr class="top-maintenance-row {{ $isPaid ? 'table-success' : '' }}" data-row="{{ $i }}">
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

                    <td style="vertical-align: middle;">
                        <div class="signer-checkboxes" 
                             id="top-maintenance-signers-{{ $i }}"
                             style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;"></div>
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

    // Render dynamic checklist items inside each TOP row based on the signer count
    var savedSignerValues = {!! json_encode($savedSignerValues) !!};
    var oldSignerValues = {!! json_encode(old('top_maintenance_signers', [])) !!};
    var signerValues = Object.assign({}, savedSignerValues, oldSignerValues);

    function renderSignerChecklist(count) {
        count = parseInt(count, 10) || 0;

        $('.top-maintenance-row').each(function() {
            var rowIndex = $(this).data('row');
            var container = $('#top-maintenance-signers-' + rowIndex);
            var statusSelect = $(this).find('.top-maintenance-status');
            var currentStatus = statusSelect.val();
            var isPaid = (currentStatus === 'paid');
            
            container.empty();

            if (count <= 0) {
                container.html('<p class="text-muted mb-0" style="font-size: 0.85rem; grid-column: 1/-1;">-</p>');
                return;
            }

            for (var j = 1; j <= count; j++) {
                var checked = signerValues[rowIndex] && signerValues[rowIndex][j] ? 'checked' : '';
                var fieldId = 'top_maintenance_signer_' + rowIndex + '_' + j;
                var disabledAttr = isPaid ? 'disabled' : '';
                var itemHtml =
                    '<div class="form-check">' +
                        '<input class="form-check-input" type="checkbox" name="top_maintenance_signers[' + rowIndex + '][' + j + ']" id="' + fieldId + '" value="1" ' + checked + ' ' + disabledAttr + '>' +
                        '<label class="form-check-label text-nowrap" for="' + fieldId + '" style="font-size: 0.9rem; ' + (isPaid ? 'color: #6c757d;' : '') + '">User ' + j + '</label>' +
                    '</div>';
                container.append(itemHtml);
            }
        });
    }

    $('#top_maintenance_signer_total').on('input change', function() {
        renderSignerChecklist($(this).val());
    });

    renderSignerChecklist($('#top_maintenance_signer_total').val());

    $(document).on('change', '.top-maintenance-status', function() {
        var newStatus = $(this).val();
        var row = $(this).closest('tr');

        // Toggle green highlight for paid status
        if (newStatus === 'paid') {
            row.addClass('table-success');
        } else {
            row.removeClass('table-success');
        }

        // Re-render signer checkboxes to update disabled state
        var count = parseInt($('#top_maintenance_signer_total').val(), 10) || 0;
        renderSignerChecklist(count);

        // Update the tracked old status
        $(this).data('old-status', newStatus);
    });
});
</script>
@endpush
