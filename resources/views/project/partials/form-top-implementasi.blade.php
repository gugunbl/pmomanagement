<div class="card border-0">
    <div class="card-body">

        <h6 class="heading-small text-muted mb-3">TOP Implementasi</h6>

        {{-- TOTAL TOP (CREATE & EDIT) --}}
        <div class="form-group col-md-3 px-0">
            <label>Total TOP Implementasi</label>
            <input type="number"
                   name="top_impl_total"
                   class="form-control"
                   min="1"
                   max="20"
                   value="{{ old('top_impl_total', $project->top_impl_total ?? 3) }}">
        </div>

        <table class="table table-sm table-bordered mt-3">
            <thead class="thead-light">
                <tr>
                    <th>TOP Ke</th>
                    <th>Tanggal TOP</th>
                    <th>Status</th>
                    <th>Berita Acara</th>
                </tr>
            </thead>
            <tbody>

            @php
                $total = old('top_impl_total', $project->top_impl_total ?? 3);
            @endphp

            @for($i = 1; $i <= $total; $i++)
                @php
                    $row = isset($project)
                        ? $project->topImplementasi->firstWhere('top_ke', $i)
                        : null;
                @endphp

                <tr>
                    <td>{{ $i }}</td>

                    <td>
                        <input type="date"
                               name="top_impl[{{ $i }}][tanggal]"
                               value="{{ old("top_impl.$i.tanggal", $row->tanggal_top ?? '') }}"
                               class="form-control">
                    </td>

                    <td>
                        <select name="top_impl[{{ $i }}][status]" class="form-control">
                            <option value="planned"   {{ old("top_impl.$i.status", $row->status ?? '')=='planned'?'selected':'' }}>Planned</option>
                            <option value="submitted" {{ old("top_impl.$i.status", $row->status ?? '')=='submitted'?'selected':'' }}>Submitted</option>
                            <option value="paid"      {{ old("top_impl.$i.status", $row->status ?? '')=='paid'?'selected':'' }}>Paid</option>
                        </select>
                    </td>

                    <td>
                        <input type="text"
                               name="top_impl[{{ $i }}][berita_acara]"
                               value="{{ old("top_impl.$i.berita_acara", $row->berita_acara ?? '') }}"
                               class="form-control">
                    </td>
                </tr>
            @endfor

            </tbody>
        </table>

    </div>
</div>
