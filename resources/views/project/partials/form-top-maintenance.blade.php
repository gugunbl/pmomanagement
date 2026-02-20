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
                @endphp

                <tr>
                    <td>{{ $i }}</td>

                    <td>
                        <input type="date"
                               name="top_maintenance[{{ $i }}][start]"
                               value="{{ old("top_maintenance.$i.start", $row->periode_start ?? '') }}"
                               class="form-control">
                    </td>

                    <td>
                        <input type="date"
                               name="top_maintenance[{{ $i }}][end]"
                               value="{{ old("top_maintenance.$i.end", $row->periode_end ?? '') }}"
                               class="form-control">
                    </td>

                    <td>
                        <select name="top_maintenance[{{ $i }}][status]" class="form-control">
                            <option value="planned"   {{ old("top_maintenance.$i.status", $row->status ?? '')=='planned'?'selected':'' }}>Planned</option>
                            <option value="submitted" {{ old("top_maintenance.$i.status", $row->status ?? '')=='submitted'?'selected':'' }}>Submitted</option>
                            <option value="paid"      {{ old("top_maintenance.$i.status", $row->status ?? '')=='paid'?'selected':'' }}>Paid</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="top_maintenance[{{ $i }}][keterangan]" 
                                  class="form-control" 
                                  rows="1">{{ old("top_maintenance.$i.keterangan", $row->keterangan ?? '') }}</textarea>
                    </td>
                </tr>
            @endfor

            </tbody>
        </table>

    </div>
</div>
