@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header border-0">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0">{{ $title }}</h3>
                    </div>
                    <div class="col text-right">
                        <a href="{{ route('cases.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                {!! Form::model($case, ['route' => ['cases.update', $case->id_case], 'method' => 'PUT']) !!}
                
                <h6 class="heading-small text-muted mb-4">Informasi User & Project</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project</label>
                            @if(count(Auth::user()->projects) == 1)
                                {{ Form::select('id_project', $projects, null, ['class' => 'form-control', 'readonly' => 'readonly', 'id' => 'project_select']) }}
                            @else
                                {{ Form::select('id_project', ['' => 'Pilih Project...'] + $projects->toArray(), null, ['class' => 'form-control select2', 'id' => 'project_select']) }}
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Open Case</label>
                            <input type="datetime-local" name="open_date" class="form-control" required value="{{ date('Y-m-d\TH:i', strtotime($case->open_date)) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tgl Closed Case</label>
                            <input type="datetime-local" name="closed_date" id="closed_date" class="form-control" value="{{ $case->closed_date ? date('Y-m-d\TH:i', strtotime($case->closed_date)) : '' }}" {{ $case->status == 'Closed' ? '' : 'disabled' }}>
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <h6 class="heading-small text-muted mb-4">Detail Case</h6>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Subject Case</label>
                            {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => 'Subject case...', 'required']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="manual_location_check" {{ !$case->affected_sn ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="manual_location_check">Input Lokasi Manual (Gunakan jika data perangkat tidak ditemukan di sistem)</label>
                        </div>
                    </div>
                </div>

                <div class="row" id="dropdown_selection_row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Perangkat Terdampak (Jenis - Merk - SN - Lantai/Rak)</label>
                            {{ Form::select('affected_sn', [], null, ['class' => 'form-control select2', 'id' => 'sn_select', 'data-selected' => $case->affected_sn, $case->affected_sn ? '' : 'disabled']) }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Pilih Site / Lokasi Terdaftar</label>
                            <select id="location_dropdown" class="form-control select2" {{ $case->affected_sn ? '' : 'disabled' }}>
                                <option value="">Pilih Lokasi...</option>
                                @foreach($lokasis as $lok)
                                    <option value="{{ $lok->nama_lokasi }}" {{ ($case->location == $lok->nama_lokasi) ? 'selected' : '' }}>
                                        {{ $lok->nama_lokasi }} - {{ $lok->alamat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" id="manual_location_row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Ketik Lokasi Perangkat (Manual)</label>
                            {{ Form::text('location', null, ['class' => 'form-control', 'id' => 'location_manual', 'placeholder' => 'Contoh: DC GTI - LT 20', !$case->affected_sn ? 'required' : 'disabled']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Case</label>
                            {{ Form::textarea('detail', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Jelaskan detail case...', 'required']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Severity</label>
                            {{ Form::select('severity', ['Minor' => 'Minor', 'Major' => 'Major', 'Critical' => 'Critical'], null, ['class' => 'form-control']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Status</label>
                            {{ Form::select('status', ['Open' => 'Open', 'In Progress' => 'In Progress', 'Closed' => 'Closed'], null, ['class' => 'form-control', 'id' => 'status_select']) }}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">No. Tiket Prinsipal (Opsional)</label>
                            {{ Form::text('principal_ticket_no', null, ['class' => 'form-control', 'placeholder' => 'Tiket prinsipal...']) }}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Detail Penyelesaian (Jika sudah Closed)</label>
                            {{ Form::textarea('resolution_detail', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Jelaskan langkah penyelesaian...']) }}
                        </div>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-warning px-5">Update Case</button>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: false
    });

    // Function to fetch SNs
    function fetchSNs(projectId) {
        if (!projectId) {
            $('#sn_select').html('<option value="">Pilih Perangkat...</option>').trigger('change');
            return;
        }

        let url = "{{ route('cases.get-sn', ':id') }}";
        url = url.replace(':id', projectId);
        let selectedSn = $('#sn_select').data('selected');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                let options = '<option value="">Pilih Perangkat...</option>';
                $.each(data, function(key, value) {
                    let isSelected = (key === selectedSn) ? 'selected' : '';
                    options += '<option value="' + key + '" ' + isSelected + '>' + value + '</option>';
                });
                $('#sn_select').html(options).trigger('change');
            }
        });
    }

    // Trigger on project change
    $('#project_select').on('change', function() {
        let projectId = $(this).val();
        fetchSNs(projectId);
    });

    // If project is already selected
    let initialProject = $('#project_select').val();
    if (initialProject) {
        fetchSNs(initialProject);
    }

    // Status logic for Closed Date
    function toggleClosedDate() {
        if ($('#status_select').val() === 'Closed') {
            $('#closed_date').prop('disabled', false).prop('required', true);
            if (!$('#closed_date').val()) {
                let now = new Date();
                let year = now.getFullYear();
                let month = String(now.getMonth() + 1).padStart(2, '0');
                let day = String(now.getDate()).padStart(2, '0');
                let hours = String(now.getHours()).padStart(2, '0');
                let minutes = String(now.getMinutes()).padStart(2, '0');
                let formatted = `${year}-${month}-${day}T${hours}:${minutes}`;
                $('#closed_date').val(formatted);
            }
        } else {
            $('#closed_date').prop('disabled', true).prop('required', false).val('');
        }
    }

    $('#status_select').on('change', function() {
        toggleClosedDate();
    });

    // Manual Location toggle logic
    function updateLocationInputState(isInitial = false) {
        let isManual = $('#manual_location_check').is(':checked');
        if (isManual) {
            // Disable dropdowns
            $('#sn_select').prop('disabled', true).trigger('change');
            if(!isInitial) $('#sn_select').val(null).trigger('change');
            
            $('#location_dropdown').prop('disabled', true).trigger('change').prop('required', false);
            if(!isInitial) $('#location_dropdown').val(null).trigger('change');
            $('#location_dropdown').attr('name', '');
            
            // Enable manual text
            $('#location_manual').prop('disabled', false).prop('required', true).attr('name', 'location');
            $('#manual_location_row').show();
            $('#dropdown_selection_row').css('opacity', '0.5');
        } else {
            // Enable dropdowns
            $('#sn_select').prop('disabled', false);
            $('#location_dropdown').prop('disabled', false).prop('required', true).attr('name', 'location');

            // Disable & hide manual text
            $('#location_manual').prop('disabled', true).prop('required', false).attr('name', '');
            if(!isInitial) $('#location_manual').val('');
            $('#manual_location_row').hide();
            $('#dropdown_selection_row').css('opacity', '1');
        }
    }

    $('#manual_location_check').on('change', function() {
        updateLocationInputState();
    });

    // Initial state check
    updateLocationInputState(true);
});
</script>
@endpush
