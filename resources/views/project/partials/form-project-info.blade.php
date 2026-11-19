<div class="card border-0">
    <div class="card-body">
        <h6 class="heading-small text-muted mb-4">Informasi Project</h6>

        <div class="row">

            {{-- Nama Project --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('nama_project', 'Nama Project') }}
                    {{ Form::text('nama_project', null, ['class'=>'form-control', 'required']) }}
                </div>
            </div>

            {{-- Nomor SPK --}}
            <div class="col-md-3">
                <div class="form-group">
                    {{ Form::label('nomor_spk', 'Nomor SPK') }}
                    {{ Form::text('nomor_spk', null, ['class'=>'form-control', 'required']) }}
                </div>
            </div>

            {{-- Tanggal SPK --}}
            <div class="col-md-3">
                <div class="form-group">                    {{ Form::label('tanggal_spk', 'Tanggal SPK') }}
                    {{ Form::date('tanggal_spk', null, ['class'=>'form-control', 'required']) }}
                </div>
            </div>


            {{-- Tanggal KOM --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('tanggal_kom', 'Tanggal KOM') }}
                    {{ Form::date('tanggal_kom', null, ['class'=>'form-control', 'required']) }}
                </div>
            </div>

            {{-- Tanggal Akhir Implementasi --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('tanggal_akhir_implementasi', 'Tanggal Akhir Implementasi') }}
                    {{ Form::date('tanggal_akhir_implementasi', null, ['class'=>'form-control', 'required']) }}
                </div>
            </div>

        <hr width="100%" align="left">

            {{-- Tanggal Awal Maintenance --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('tanggal_mulai_maintenance', 'Tanggal Awal Maintenance') }}
                    {{ Form::date('tanggal_mulai_maintenance', null, ['class'=>'form-control', 'required']) }}
                </div>
            </div>

            {{-- Tanggal Akhir Maintenance --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('tanggal_akhir_maintenance', 'Tanggal Akhir Maintenance') }}
                    {{ Form::date('tanggal_akhir_maintenance', null, ['class'=>'form-control', 'required']) }}
                </div>
            </div>
        <hr width="100%" align="left">

            {{-- Status Implementasi --}}
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('status_implementation', 'Status Implementasi') }}
                    {{ Form::select('status_implementation', $statusim, null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih Status'
                    ]) }}
                </div>
            </div>

            {{-- Status Maintenance --}}
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('status_maintenance', 'Status Maintenance') }}
                    {{ Form::select('status_maintenance', $statusma, null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih Status',
                        'id' => 'status_maintenance'
                    ]) }}
                </div>
            </div>

            {{-- Tipe Maintenance --}}
            <div class="col-md-4">
                <div class="form-group">
                    {{ Form::label('tipe_maintenance', 'Tipe Maintenance') }}
                    {{ Form::select('tipe_maintenance', ['Manage Service'=>'Manage Service', 'PM-CM'=>'PM-CM'], null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih Tipe',
                        'id' => 'tipe_maintenance',
                        'disabled' => true
                    ]) }}
                </div>
            </div>

            {{-- Team Support --}}
            <div class="col-md-12">
                <div class="form-group">
                    <div class="d-flex align-items-center mb-1">
                        {{ Form::label('team_support[]', 'Team Support', ['class' => 'mb-0 mr-3']) }}
                        @if(isset($teamSupports) && $teamSupports->count())
                            <a href="#" id="btn-select-all-ts" class="btn btn-sm btn-outline-primary py-0 px-2 mr-1" style="font-size:0.75rem;">
                                <i class="fas fa-check-double mr-1"></i>Select All
                            </a>
                            <a href="#" id="btn-deselect-all-ts" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.75rem;">
                                <i class="fas fa-times mr-1"></i>Deselect All
                            </a>
                        @endif
                    </div>
                    <div class="border rounded p-3" style="max-height:160px; overflow-y:auto; background:#f8f9fa;">
                        @if(isset($teamSupports) && $teamSupports->count())
                            <div class="row">
                            @foreach($teamSupports as $id => $name)
                                <div class="col-md-3 col-sm-4 col-6 mb-1">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input ts-checkbox"
                                            id="ts_{{ $id }}"
                                            name="team_support[]"
                                            value="{{ $id }}"
                                            @if(isset($selectedTeamSupports) && in_array($id, $selectedTeamSupports)) checked @endif
                                        >
                                        <label class="custom-control-label" for="ts_{{ $id }}">{{ $name }}</label>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        @else
                            <span class="text-muted small">Belum ada data team support. <a href="{{ route('team-support.index') }}">Tambah disini</a></span>
                        @endif
                    </div>
                </div>
            </div>


            {{-- Customer --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('customer', 'Customer') }}
                    {{ Form::select('customer', $clients, null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih Customer'
                    ]) }}
                </div>
            </div>

            {{-- Category --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('category', 'Category Project') }}
                    {{ Form::select('category', $categories, null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih Category'
                    ]) }}
                </div>
            </div>

            {{-- PM Implementasi --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('pm_implementasi', 'Project Manager Implementasi') }}
                    {{ Form::select('pm_implementasi', $mprojectmanager, null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih PM Implementasi',
                        'required'
                    ]) }}
                </div>
            </div>

            {{-- PM Maintenance --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('pm_maintenance', 'Project Manager Maintenance') }}
                    {{ Form::select('pm_maintenance', $mprojectmanager, null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih PM Maintenance',
                        'required'
                    ]) }}
                </div>
            </div>

            {{-- Sales --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('sales', 'Sales') }}
                    {{ Form::select('sales', $saless, null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih Sales'
                    ]) }}
                </div>
            </div>

            {{-- Link Dokumen --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('link_dokumen', 'Folder Project di Nextcloud') }}
                    {{ Form::text('link_dokumen', null, [
                        'class'=>'form-control'.($errors->has('link_dokumen') ? ' is-invalid' : ''),
                        'placeholder'=>'Contoh: 2025_VDI_Phase2'
                    ]) }}
                    @error('link_dokumen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Folder project beserta struktur standar <strong>01–13</strong>, <strong>30_Preventive_Maintenance</strong>, dan <strong>31_Document_Maintenance</strong> akan diperiksa dan dilengkapi otomatis. Spasi diperbolehkan; jangan gunakan karakter / atau \\.
                    </small>
                </div>
            </div>

            {{-- Keterangan --}}
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('keterangan', 'Keterangan') }}
                    {{ Form::textarea('keterangan', null, [
                        'class'=>'form-control',
                        'rows'=>3
                    ]) }}
                </div>
            </div>

        </div>
    </div>
</div>
