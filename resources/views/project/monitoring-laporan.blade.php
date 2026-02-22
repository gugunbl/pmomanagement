@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">Monitoring Laporan Project</h3>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-items-center">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Nama Project</th>
                                    <th scope="col">Nomor SPK</th>
                                    <th scope="col">Report Ke</th>
                                    <th scope="col">Periode Start</th>
                                    <th scope="col">Periode End</th>
                                    <th scope="col" style="width: 150px;">Status</th>
                                    <th scope="col">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach($reports as $report)
                                    <tr>
                                        <td>
                                            <span data-toggle="tooltip" data-placement="top" title="{{ $report->project->nama_project ?? '-' }}">
                                                {{ Str::limit($report->project->nama_project ?? '-', 30) }}
                                            </span>
                                        </td>
                                        <td>{{ $report->project->nomor_spk ?? '-' }}</td>
                                        <td>{{ $report->report_ke }}</td>
                                        <td>{{ $report->period_start }}</td>
                                        <td>{{ $report->period_end }}</td>
                                        <td>
                                            <select class="form-control form-control-sm editable" 
                                                    data-id="{{ $report->id }}" 
                                                    data-field="status">
                                                <option value="planned"   {{ $report->status == 'planned' ? 'selected' : '' }}>Planned</option>
                                                <option value="submitted" {{ $report->status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                                <option value="paid"      {{ $report->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea class="form-control form-control-sm editable" 
                                                      data-id="{{ $report->id }}" 
                                                      data-field="keterangan" 
                                                      rows="4"
                                                      style="min-width: 250px;">{{ $report->keterangan }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="save-notification" class="save-toast">
        <i class="fas fa-check-circle mr-2"></i> Data berhasil disimpan
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function showNotification() {
                var toast = $('#save-notification');
                toast.addClass('show');
                setTimeout(function() {
                    toast.removeClass('show');
                }, 3000);
            }

            $('.editable').on('change', function() {
                var id = $(this).data('id');
                var field = $(this).data('field');
                var value = $(this).val();
                var element = $(this);

                element.addClass('is-loading');

                $.ajax({
                    url: "{{ route('project.report.monitoring.update') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        field: field,
                        value: value
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification();
                        }
                    },
                    error: function(xhr) {
                        alert('Error updating data');
                        console.error(xhr);
                    },
                    complete: function() {
                        element.removeClass('is-loading');
                    }
                });
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
    <style>
        .is-loading {
            opacity: 0.5;
            border-color: #5e72e4;
        }
        
        .save-toast {
            visibility: hidden;
            min-width: 250px;
            background-color: #2dce89;
            color: #fff;
            text-align: center;
            border-radius: 4px;
            padding: 16px;
            position: fixed;
            z-index: 9999;
            right: 30px;
            bottom: 30px;
            font-size: 14px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
        }

        .save-toast.show {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }
    </style>
@endpush
