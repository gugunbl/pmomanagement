@extends('layouts.app')

@section('hide_page_header', '1')

@section('content')
@php
    $averageUtilization = $reportsForChart->count() ? round($reportsForChart->avg('work_utilization_percentage'), 1) : 0;
    $totalActivities = $reportsForChart->sum('total_activities');
    $employeeCount = $reportsForChart->pluck('employee_name')->unique()->count();
@endphp
<div class="util-page">
    <section class="util-hero">
        <div class="util-hero-main">
            <div>
                <span class="util-eyebrow">People Analytics</span>
                <h1>Dashboard Utilisasi</h1>
                <p>Pantau produktivitas harian, tren mingguan, dan detail aktivitas tim dalam satu workspace.</p>
            </div>
            <div class="util-hero-actions">
                <a href="{{ route('dashboard.utilisasi.manual') }}" class="util-action util-action-primary"><i class="fas fa-plus"></i> Tambah Manual</a>
                <button type="button" class="util-action util-action-glass" data-toggle="modal" data-target="#modalImport"><i class="fas fa-file-upload"></i> Import Data</button>
            </div>
        </div>
        <div class="util-kpi-grid">
            <div class="util-kpi"><span class="util-kpi-icon"><i class="fas fa-chart-line"></i></span><span><small>Rata-rata utilisasi</small><strong>{{ $averageUtilization }}%</strong></span></div>
            <div class="util-kpi"><span class="util-kpi-icon"><i class="fas fa-users"></i></span><span><small>Karyawan terpantau</small><strong>{{ $employeeCount }}</strong></span></div>
            <div class="util-kpi"><span class="util-kpi-icon"><i class="fas fa-calendar-check"></i></span><span><small>Laporan periode ini</small><strong>{{ $reportsForChart->count() }}</strong></span></div>
            <div class="util-kpi"><span class="util-kpi-icon"><i class="fas fa-list-alt"></i></span><span><small>Total aktivitas</small><strong>{{ $totalActivities }}</strong></span></div>
        </div>
    </section>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow util-workspace-card">
            <div class="card-body">
                <!-- Filter Section -->
                <div class="util-section-heading"><span class="util-section-icon"><i class="fas fa-filter"></i></span><div><h2>Filter laporan</h2><p>Pilih karyawan dan rentang tanggal untuk memperbarui seluruh insight.</p></div></div>
                <form action="{{ route('dashboard.utilisasi') }}" method="GET" class="util-filter mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="form-control-label">Nama karyawan</label>
                                <select name="employee_name[]" class="form-control select2" multiple data-placeholder="Semua karyawan">
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp }}" {{ is_array(request('employee_name')) && in_array($emp, request('employee_name')) ? 'selected' : '' }}>{{ $emp }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label class="form-control-label">Rentang tanggal laporan</label>
                                <div class="input-daterange datepicker row align-items-center">
                                    <div class="col">
                                        <input class="form-control" name="start_date" placeholder="Start date" type="date" value="{{ $start_date }}">
                                    </div>
                                    <div class="col-auto util-date-separator">hingga</div>
                                    <div class="col">
                                        <input class="form-control" name="end_date" placeholder="End date" type="date" value="{{ $end_date }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="util-filter-actions">
                                <button type="submit" class="util-button util-button-primary"><i class="fas fa-search"></i> Terapkan</button>
                                <a href="{{ route('dashboard.utilisasi') }}" class="util-button util-button-muted">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Tabs -->
                <ul class="nav nav-pills nav-fill flex-column flex-sm-row mb-4 util-tabs" id="tabs-text" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0 {{ !request('page') ? 'active' : '' }}" id="tabs-dashboard-tab" data-toggle="tab" href="#tabs-dashboard" role="tab" aria-controls="tabs-dashboard" aria-selected="{{ !request('page') ? 'true' : 'false' }}"><i class="fas fa-chart-bar mr-1"></i> Tren Harian</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0" id="tabs-weekly-tab" data-toggle="tab" href="#tabs-weekly" role="tab" aria-controls="tabs-weekly" aria-selected="false"><i class="fas fa-calendar-week mr-1"></i> Tren Mingguan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0" id="tabs-highlevel-tab" data-toggle="tab" href="#tabs-highlevel" role="tab" aria-controls="tabs-highlevel" aria-selected="false"><i class="fas fa-th-large mr-1"></i> Ringkasan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0 {{ request('page') ? 'active' : '' }}" id="tabs-detail-tab" data-toggle="tab" href="#tabs-detail" role="tab" aria-controls="tabs-detail" aria-selected="{{ request('page') ? 'true' : 'false' }}"><i class="fas fa-list mr-1"></i> Detail Aktivitas</a>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    <!-- Tab 1: Chart Daily -->
                    <div class="tab-pane fade {{ !request('page') ? 'show active' : '' }}" id="tabs-dashboard" role="tabpanel" aria-labelledby="tabs-dashboard-tab">
                        <div class="chart-container mb-5" style="position: relative; height:400px; width:100%">
                            <canvas id="utilizationChart"></canvas>
                        </div>

                        <div class="table-responsive mt-4">
                            <h4 class="mb-3">Summary Reports</h4>
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Report Date</th>
                                        <th>Day</th>
                                        <th>Productive Time</th>
                                        <th>Utilization (%)</th>
                                        <th>Total Activities</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reports as $rep)
                                        <tr>
                                            <td><span class="badge badge-dot mr-4"><i style="background-color: hsl(0, 0%, 0%);"></i> {{ $rep->employee_name }}</span></td>
                                            <td>{{ date('d-m-Y', strtotime($rep->report_date)) }}</td>
                                            <td>{{ $rep->day_name }}</td>
                                            <td>{{ $rep->total_productive_time }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="mr-2">{{ $rep->work_utilization_percentage }}%</span>
                                                    <div>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-info" role="progressbar" aria-valuenow="{{ $rep->work_utilization_percentage }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $rep->work_utilization_percentage }}%;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $rep->total_activities }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer py-4">
                            <div class="d-flex justify-content-end">
                                {{ $reports->links() }}
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Chart Weekly -->
                    <div class="tab-pane fade" id="tabs-weekly" role="tabpanel" aria-labelledby="tabs-weekly-tab">
                        <div class="chart-container mb-5" style="position: relative; height:400px; width:100%">
                            <canvas id="weeklyUtilizationChart"></canvas>
                        </div>

                        <div class="table-responsive mt-4">
                            <h4 class="mb-3">Summary Reports (Weekly)</h4>
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Week</th>
                                        <th>Productive Time</th>
                                        <th>Utilization (%)</th>
                                        <th>Total Activities</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($weeklyReports as $rep)
                                        <tr>
                                            <td><span class="badge badge-dot mr-4"><i style="background-color: hsl(0, 0%, 0%);"></i> {{ $rep->employee_name }}</span></td>
                                            <td>Week {{ $rep->report_week }} ({{ date('d-m-Y', strtotime($rep->week_start)) }} - {{ date('d-m-Y', strtotime($rep->week_end)) }})</td>
                                            <td>{{ $rep->total_productive_formatted }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="mr-2">{{ $rep->avg_utilization }}%</span>
                                                    <div>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-info" role="progressbar" aria-valuenow="{{ $rep->avg_utilization }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $rep->avg_utilization }}%;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $rep->total_activities }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 3: High Level Report -->
                    <div class="tab-pane fade" id="tabs-highlevel" role="tabpanel" aria-labelledby="tabs-highlevel-tab">
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered align-items-center">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>TW Name</th>
                                        @foreach($highLevelWeeks as $index => $yw)
                                            <th class="text-center" style="min-width: 150px; border-left: 2px solid #dee2e6; {{ $index % 2 == 0 ? 'background-color: #f8f9fe;' : 'background-color: #ffffff;' }}">
                                                <div style="font-size: 1rem; font-weight: 800; color: #32325d;">Week {{ $weekLabels[$yw]['week_num'] }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $weekLabels[$yw]['start'] }} - {{ $weekLabels[$yw]['end'] }}</div>
                                            </th>
                                        @endforeach
                                        <th class="text-center" style="width: 100px; border-left: 2px solid #dee2e6; background-color: #f8f9fe; vertical-align: middle;">
                                            <div style="font-size: 1rem; font-weight: 800; color: #32325d;">Average</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach($targetEmployees as $emp)
                                        <tr>
                                            <td class="text-center">{{ $no++ }}</td>
                                            <td style="font-weight: 600;">{{ $emp }}</td>
                                            @foreach($highLevelWeeks as $index => $yw)
                                                @php $val = $highLevelData[$emp][$yw] ?? 0; @endphp
                                                <td class="p-0" style="position: relative; vertical-align: middle; border-left: 2px solid #dee2e6; {{ $index % 2 == 0 ? 'background-color: #fcfcfe;' : 'background-color: #ffffff;' }}">
                                                    @if($val > 0)
                                                        <div style="position: absolute; top: 0; left: 0; bottom: 0; width: {{ min($val, 100) }}%; background-color: rgba(94, 114, 228, 0.15); border-right: 2px solid #5e72e4;"></div>
                                                    @endif
                                                    <div class="px-3 py-3 text-right" style="position: relative; z-index: 1; font-weight: bold;">
                                                        {{ $val > 0 ? $val . '%' : '-' }}
                                                    </div>
                                                </td>
                                            @endforeach
                                            <td class="text-center font-weight-bold bg-light" style="border-left: 2px solid #dee2e6;">
                                                {{ $employeeAverages[$emp] }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Grid -->
                    <div class="tab-pane fade {{ request('page') ? 'show active' : '' }}" id="tabs-detail" role="tabpanel" aria-labelledby="tabs-detail-tab">
                        <div class="table-responsive">
                            <table class="table align-items-center table-flush">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Report Date</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Activity Description</th>
                                        <th>Duration (Min)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activities as $act)
                                        <tr>
                                            <td>{{ $act->employee_name }}</td>
                                            <td>{{ date('d-m-Y', strtotime($act->report_date)) }}</td>
                                            <td>{{ $act->start_time }}</td>
                                            <td>{{ $act->end_time }}</td>
                                            <td style="max-width: 350px; white-space: normal;">
                                                @if(strlen($act->activity_description) > 100)
                                                    <span data-toggle="tooltip" data-placement="top" title="{{ $act->activity_description }}">
                                                        {{ substr($act->activity_description, 0, 100) }}...
                                                    </span>
                                                @else
                                                    {{ $act->activity_description }}
                                                @endif
                                            </td>
                                            <td>{{ $act->duration_minutes }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer py-4">
                            <div class="d-flex justify-content-end">
                                {{ $activities->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/utilization-workspace.css') }}?v={{ @filemtime(public_path('assets/css/utilization-workspace.css')) ?: '1' }}">
@endpush

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportLabel">Upload Screenshot / Foto Daily Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAIImport" action="{{ route('dashboard.utilisasi.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-control-label font-weight-bold">File Gambar</label>
                        <input type="file" name="file" class="form-control" required accept="image/png, image/jpeg, image/jpg">
                        <small class="text-muted d-block mt-1">
                            Upload screenshot atau foto template laporan harian. Sistem akan memproses gambar menggunakan Gemini AI dan mengekstrak semua tanggal &amp; aktivitas secara otomatis.
                        </small>
                    </div>
                    <div class="alert alert-info py-2 px-3 mb-0" style="font-size: 0.8rem;">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Format yang didukung:</strong> Screenshot template laporan (tabel dengan kolom Tanggal, Aktivitas, Waktu, Durasi) <em>atau</em> screenshot chat/WhatsApp dengan rentang waktu (08:00 - 09:00).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" id="btnProsesAI" class="btn btn-primary">
                        <i class="fas fa-brain mr-1"></i> Proses dengan AI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="aiLoadingOverlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center; flex-direction:column;">
    <div style="background:#fff; border-radius:16px; padding:40px 50px; text-align:center; box-shadow:0 10px 40px rgba(0,0,0,0.3);">
        <div style="margin-bottom:20px;">
            <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem;">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <h5 style="margin-bottom:8px; color:#333;"><i class="fas fa-brain mr-1"></i> Memproses dengan Gemini AI</h5>
        <p style="color:#666; margin-bottom:5px; font-size:14px;">Sedang menganalisis gambar dan mengekstrak data...</p>
        <p style="color:#999; margin:0; font-size:12px;" id="aiLoadingTimer">Waktu berjalan: 0 detik</p>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true
    });

    const ctx = document.getElementById('utilizationChart').getContext('2d');
    const reports = @json($reportsForChart);
    
    // Get unique dates for labels
    const labels = [...new Set(reports.map(r => r.report_date))].sort();
    
    // Get unique employees for datasets
    const employees = [...new Set(reports.map(r => r.employee_name))];

    const getEmployeeColor = (name) => {
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        const h = Math.abs(hash % 360);
        return `hsl(${h}, 70%, 50%)`;
    };

    // Create datasets for each employee
    const datasets = [];
    employees.forEach(emp => {
        const color = getEmployeeColor(emp);
        
        // Bar dataset for Utilization %
        datasets.push({
            label: emp + ' (%)',
            type: 'bar',
            data: labels.map(date => {
                const report = reports.find(r => r.report_date === date && r.employee_name === emp);
                return report ? report.work_utilization_percentage : 0;
            }),
            backgroundColor: color,
            borderColor: color.replace('50%', '40%'),
            borderWidth: 1,
            yAxisID: 'y'
        });

        // Line dataset for Activity Count
        datasets.push({
            label: emp + ' (Activities)',
            type: 'line',
            data: labels.map(date => {
                const report = reports.find(r => r.report_date === date && r.employee_name === emp);
                return report ? report.total_activities : 0;
            }),
            borderColor: color,
            backgroundColor: 'transparent',
            pointBackgroundColor: color,
            borderWidth: 2,
            yAxisID: 'y1',
            tension: 0.1
        });
    });

    new Chart(ctx, {
        data: {
            labels: labels.map(d => {
                const date = new Date(d);
                return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
            }),
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Utilization (%)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Activities Count'
                    }
                }
            }
        }
    });

    // Update summary table dot colors to match chart colors
    $('.badge-dot i').each(function() {
        const empName = $(this).parent().text().trim();
        $(this).css('background-color', getEmployeeColor(empName));
    });

    // Weekly Chart Logic
    const ctxWeekly = document.getElementById('weeklyUtilizationChart').getContext('2d');
    const weeklyReports = @json($weeklyReports);
    
    const weeklyLabelsRaw = [...new Set(weeklyReports.map(r => `${r.report_year}-W${r.report_week}`))].sort();
    
    const datasetsWeekly = [];
    employees.forEach(emp => {
        const color = getEmployeeColor(emp);
        
        // Bar dataset for Avg Utilization %
        datasetsWeekly.push({
            label: emp + ' (%)',
            type: 'bar',
            data: weeklyLabelsRaw.map(weekStr => {
                const [year, week] = weekStr.split('-W');
                const report = weeklyReports.find(r => r.report_year == year && r.report_week == week && r.employee_name === emp);
                return report ? report.avg_utilization : 0;
            }),
            backgroundColor: color,
            borderColor: color.replace('50%', '40%'),
            borderWidth: 1,
            yAxisID: 'y'
        });

        // Line dataset for Total Activity Count
        datasetsWeekly.push({
            label: emp + ' (Activities)',
            type: 'line',
            data: weeklyLabelsRaw.map(weekStr => {
                const [year, week] = weekStr.split('-W');
                const report = weeklyReports.find(r => r.report_year == year && r.report_week == week && r.employee_name === emp);
                return report ? report.total_activities : 0;
            }),
            borderColor: color,
            backgroundColor: 'transparent',
            pointBackgroundColor: color,
            borderWidth: 2,
            yAxisID: 'y1',
            tension: 0.1
        });
    });

    new Chart(ctxWeekly, {
        data: {
            labels: weeklyLabelsRaw.map(w => {
                const parts = w.split('-W');
                return 'Week ' + parts[1] + ' (' + parts[0] + ')';
            }),
            datasets: datasetsWeekly
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Avg Utilization (%)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Activities Count'
                    }
                }
            }
        }
    });

    // Initialize tooltips for truncated descriptions
    $('[data-toggle="tooltip"]').tooltip();

    // AI Processing Loading Overlay
    $('#formAIImport').on('submit', function(e) {
        var fileInput = $(this).find('input[name="file"]');
        if (!fileInput.val()) return false;

        // Disable button & show spinner inside it
        var btn = $('#btnProsesAI');
        btn.prop('disabled', true);
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

        // Show fullscreen loading overlay
        $('#aiLoadingOverlay').css('display', 'flex');

        // Close modal backdrop so overlay is visible
        $('#modalImport').modal('hide');

        // Timer counter
        var seconds = 0;
        var timer = setInterval(function() {
            seconds++;
            $('#aiLoadingTimer').text('Waktu berjalan: ' + seconds + ' detik');
        }, 1000);

        // Store timer for cleanup
        $(this).data('timer', timer);
    });
});
</script>
@endpush
