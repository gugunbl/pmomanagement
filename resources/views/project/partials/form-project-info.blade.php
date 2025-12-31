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
                <div class="form-group">
                    {{ Form::label('tanggal_spk', 'Tanggal SPK') }}
                    {{ Form::date('tanggal_spk', null, ['class'=>'form-control', 'required']) }}
                </div>
            </div>


            {{-- Tanggal KOM --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('tanggal_spk', 'Tanggal KOM') }}
                    {{ Form::date('tanggal_spk', null, ['class'=>'form-control', 'required']) }}
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
                        'placeholder'=>'Pilih Status'
                    ]) }}
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
                        'placeholder'=>'Pilih PM Implementasi'
                    ]) }}
                </div>
            </div>

            {{-- PM Maintenance --}}
            <div class="col-md-6">
                <div class="form-group">
                    {{ Form::label('pm_maintenance', 'Project Manager Maintenance') }}
                    {{ Form::select('pm_maintenance', $mprojectmanager, null, [
                        'class'=>'form-control',
                        'placeholder'=>'Pilih PM Maintenance'
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
                    {{ Form::label('link_dokumen', 'Link Dokumen') }}
                    {{ Form::text('link_dokumen', null, [
                        'class'=>'form-control',
                        'placeholder'=>'https://...'
                    ]) }}
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
