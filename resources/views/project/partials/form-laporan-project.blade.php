<div class="card border-0">
    <div class="card-body">
        <h6 class="heading-small text-muted mb-3">Laporan Preventive Maintenance / Bulanan</h6>

        {{-- TOTAL REPORTS --}}
        <div class="form-group col-md-3 px-0">
            <label>Total Laporan</label>
            <input type="number"
                   name="project_report_total"
                   class="form-control"
                   min="0"
                   max="60"
                   value="{{ old('project_report_total', $project->project_report_total ?? 0) }}">
        </div>

        @php
            $totalReports = old(
                'project_report_total',
                $project->project_report_total ?? 0
            );
        @endphp

        <table class="table table-sm table-bordered mt-3">
            <thead class="thead-light">
                <tr>
                    <th>Laporan Ke</th>
                    <th>Periode Start</th>
                    <th>Periode End</th>
                    <th>Tanggal Submit</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>

            @for($i = 1; $i <= $totalReports; $i++)
                @php
                    $report = $project->reports->firstWhere('report_ke', $i);
                    // HTML date inputs only accept an ISO date (Y-m-d). Passing a
                    // Carbon instance directly renders a timestamp and makes the
                    // browser show an empty dd/mm/yyyy field even though the date
                    // is already stored in the database.
                    $periodStart = optional(optional($report)->period_start)->format('Y-m-d');
                    $periodEnd = optional(optional($report)->period_end)->format('Y-m-d');
                    $submissionDate = optional(optional($report)->submission_date)->format('Y-m-d');
                @endphp

                <tr>
                    <td>{{ $i }}</td>

                    <td>
                        <input type="date"
                               name="project_reports[{{ $i }}][start]"
                               value="{{ old("project_reports.$i.start", $periodStart) }}"
                               class="form-control">
                    </td>

                    <td>
                        <input type="date"
                               name="project_reports[{{ $i }}][end]"
                               value="{{ old("project_reports.$i.end", $periodEnd) }}"
                               class="form-control">
                    </td>

                    <td>
                        <input type="date"
                               name="project_reports[{{ $i }}][submission_date]"
                               value="{{ old("project_reports.$i.submission_date", $submissionDate) }}"
                               class="form-control">
                    </td>

                    <td>
                        <select name="project_reports[{{ $i }}][status]" class="form-control">
                            <option value="open"      {{ old("project_reports.$i.status", optional($report)->status)=='open'?'selected':'' }}>Open</option>
                            <option value="progress"  {{ old("project_reports.$i.status", optional($report)->status)=='progress'?'selected':'' }}>Progress</option>
                            <option value="submitted" {{ old("project_reports.$i.status", optional($report)->status)=='submitted'?'selected':'' }}>Submitted</option>
                            <option value="signoff"   {{ old("project_reports.$i.status", optional($report)->status)=='signoff'?'selected':'' }}>Signoff</option>
                        </select>
                    </td>
                    <td>
                        <textarea name="project_reports[{{ $i }}][keterangan]" 
                                  class="form-control" 
                                  rows="1">{{ old("project_reports.$i.keterangan", optional($report)->keterangan) }}</textarea>
                    </td>
                </tr>
            @endfor

            </tbody>
        </table>

    </div>
</div>
