@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">Dashboard Utilisasi</h3>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <form action="{{ route('dashboard.utilisasi') }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="form-control-label">Employee Name</label>
                                <select name="employee_name" class="form-control select2">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp }}" {{ request('employee_name') == $emp ? 'selected' : '' }}>{{ $emp }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-control-label">Report Date</label>
                                <input type="date" name="report_date" class="form-control" value="{{ request('report_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('dashboard.utilisasi') }}" class="btn btn-secondary">Reset</a>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalImport">
                                <i class="fas fa-file-upload mr-1"></i> Upload
                            </button>
                        </div>
                    </div>
                </form>

                <hr class="my-4">

                <!-- Tabs -->
                <ul class="nav nav-pills nav-fill flex-column flex-sm-row mb-4" id="tabs-text" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0 {{ !request('page') ? 'active' : '' }}" id="tabs-dashboard-tab" data-toggle="tab" href="#tabs-dashboard" role="tab" aria-controls="tabs-dashboard" aria-selected="{{ !request('page') ? 'true' : 'false' }}">Dashboard Chart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link mb-sm-3 mb-md-0 {{ request('page') ? 'active' : '' }}" id="tabs-detail-tab" data-toggle="tab" href="#tabs-detail" role="tab" aria-controls="tabs-detail" aria-selected="{{ request('page') ? 'true' : 'false' }}">Detail Grid</a>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    <!-- Tab 1: Chart -->
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
                    </div>

                    <!-- Tab 2: Grid -->
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
@endsection

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportLabel">Upload Screenshot Daily Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAIImport" action="{{ route('dashboard.utilisasi.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-control-label">File Gambar (Screenshot)</label>
                        <input type="file" name="file" class="form-control" required accept="image/png, image/jpeg, image/jpg">
                        <small class="text-muted">Upload screenshot task report (contoh: dari chat WhatsApp). Sistem akan memproses gambar menggunakan Gemini AI.</small>
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
        width: '100%'
    });

    const ctx = document.getElementById('utilizationChart').getContext('2d');
    const reports = @json($reports);
    
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

    // Create datasets for each employee (Bar Chart)
    const datasets = employees.map(emp => {
        const color = getEmployeeColor(emp);
        return {
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
        };
    });

    // Add dataset for Total Activities (Line Chart - Aggregated per date)
    const aggregatedActivities = labels.map(date => {
        return reports
            .filter(r => r.report_date === date)
            .reduce((sum, r) => sum + r.total_activities, 0);
    });

    datasets.push({
        label: 'Sum of Activities',
        data: aggregatedActivities,
        type: 'line',
        borderColor: 'rgba(251, 99, 64, 1)',
        backgroundColor: 'transparent',
        pointBackgroundColor: 'rgba(251, 99, 64, 1)',
        borderWidth: 2,
        yAxisID: 'y1',
        tension: 0.1
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
