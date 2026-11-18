@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            {{ Form::open(['route' => 'settings.update', 'files'=>true])}}
            <div class="card mb-5">
                <div class="card-header bg-transparent"><h3 class="mb-0">General Settings</h3></div>
                <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('company_name', 'Company Name', ['class' => 'form-control-label'])}}
                                    {{ Form::text('company_name', setting('company_name'), ['class'=>"form-control"])}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('company_email', 'Company Email', ['class' => 'form-control-label'])}}
                                    {{ Form::text('company_email', setting('company_email'), ['class'=>"form-control"])}}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('company_phone', 'Company Phone', ['class' => 'form-control-label'])}}
                                    {{ Form::text('company_phone', setting('company_phone'), ['class'=>"form-control"])}}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('company_address', 'Company Address', ['class' => 'form-control-label'])}}
                                    {{ Form::text('company_address', setting('company_address'), ['class'=>"form-control"])}}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{Form::label('company_city', 'City', ['class' => 'form-control-label'])}}
                                    {{ Form::text('company_city', setting('company_city'), ['class'=>"form-control"])}}
                                </div>
                            </div>
                            <div class="col-md-4">
                                {{Form::label('company_logo', 'Company Logo', ['class' => 'form-control-label'])}}
                                    <div class="input-group">
                                        <span class="input-group-btn">
                                          <a id="uploadFile" data-input="thumbnail" data-preview="holder" class="btn btn-secondary">
                                            <i class="fa fa-picture-o"></i> Choose Logo
                                          </a>
                                        </span>
                                        @if (setting('company_logo'))
                                            <input id="thumbnail" class="form-control d-none" type="text" value="{{ setting('company_logo') }}" name="company_logo">
                                        @else
                                            <input id="thumbnail" class="form-control d-none" type="text" name="company_logo">
                                        @endif
                                    </div>
                            </div>
                            <div class="col-md-2 text-right">
                                @if (setting('company_logo'))
                                <img alt="Image placeholder"
                                    class="avatar avatar-xl  rounded-circle"
                                    data-toggle="tooltip" data-original-title="{{ setting('company_name') }} Logo"
                                    src="{{ asset(setting('company_logo')) }}">
                                @endif
                            </div>
                        </div>
                    </div>
            </div>
            <div class="card mb-5">
                <div class="card-header bg-transparent"><h4 class="mb-0">Display Settings</h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{Form::label('record_per_page', 'Record Per Page', ['class' => 'form-control-label'])}}
                                {{ Form::text('record_per_page', setting('record_per_page'), ['class'=>"form-control"])}}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {{Form::label('company_currency_symbol', 'Currency Symbol', ['class' => 'form-control-label'])}}
                                {{ Form::text('company_currency_symbol', setting('company_currency_symbol'), ['class'=>"form-control"])}}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('utilization_effective_work_hours', 'Jam Kerja Efektif Utilisasi', ['class' => 'form-control-label']) }}
                                <div class="input-group">
                                    {{ Form::number('utilization_effective_work_hours', setting('utilization_effective_work_hours', 8), ['class' => 'form-control', 'min' => 1, 'max' => 24, 'step' => '0.5']) }}
                                    <div class="input-group-append"><span class="input-group-text">jam/hari</span></div>
                                </div>
                                <small class="form-text text-muted">Digunakan sebagai dasar perhitungan persentase pada Dashboard Utilisasi.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-5">
                <div class="card-header bg-transparent"><h4 class="mb-0">Other Settings</h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            {{ Form::label('default_role', 'User Registeration Admin Notification Email', ['class' => 'form-control-label']) }}
                            <div class="custom-control custom-checkbox">
                                {!! Form::hidden('register_notification_email', 0) !!}
                                <input type="checkbox" name="register_notification_email" value="1" {{ setting('register_notification_email') ? 'checked' : ''}} class="custom-control-input" id="register_notification_email">
                                {{ Form::label('register_notification_email', 'Activate', ['class' => 'custom-control-label form-control-label']) }}
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                {{ Form::label('default_role', 'Select default register role', ['class' => 'form-control-label']) }}
                                {{ Form::select('default_role', $roles, setting('default_role', null), [ 'class'=> 'selectpicker form-control', 'placeholder' => 'Select role...']) }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {{Form::label('max_login_attempts', 'Maximum invaild login attempts', ['class' => 'form-control-label'])}}
                                {{ Form::text('max_login_attempts', setting('max_login_attempts'), ['class'=>"form-control"])}}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                {{Form::label('lockout_delay', 'Lockout delay (minutes)', ['class' => 'form-control-label'])}}
                                {{ Form::text('lockout_delay', setting('lockout_delay'), ['class'=>"form-control"])}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header bg-transparent"><h4 class="mb-0">Maintenance Reminder Email Settings</h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('maintenance_reminder_to', 'Recipient Email(s) (separate with comma)', ['class' => 'form-control-label'])}}
                                {{ Form::text('maintenance_reminder_to', setting('maintenance_reminder_to'), ['class'=>"form-control", 'placeholder' => 'example@gmail.com, admin@gmail.com'])}}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('maintenance_reminder_subject', 'Email Subject', ['class' => 'form-control-label'])}}
                                {{ Form::text('maintenance_reminder_subject', setting('maintenance_reminder_subject', 'Reminder Maintenance Project'), ['class'=>"form-control"])}}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('maintenance_reminder_body', 'Email Body Greeting/Message', ['class' => 'form-control-label'])}}
                                {{ Form::textarea('maintenance_reminder_body', setting('maintenance_reminder_body', "Dear Pak Wida dan Team,\n\nIzin reminder kembali untuk data project yang sudah berakhir dan akan berakhir dalam 5 bulan ke depan. Informasi ini untuk memastikan kelancaran project agar sesuai dengan timeline masing-masing project."), ['class'=>"form-control", 'rows' => 4])}}
                                <small class="text-muted">The project list table will be automatically appended after this message.</small>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 text-right">
                            {!! Form::submit('Update Settings', ['class'=> 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header bg-transparent"><h4 class="mb-0">Ticketing System Settings</h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('ticketing_url', 'Ticketing System URL', ['class' => 'form-control-label'])}}
                                {{ Form::text('ticketing_url', setting('ticketing_url', config('settings.defaults.ticketing_url', config('app.url'))), ['class'=>"form-control", 'placeholder' => config('settings.defaults.ticketing_url', 'https://pmomaintenance.web.id')])}}
                                <small class="text-muted">This URL will be included in the access credentials email.</small>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 text-right">
                            {!! Form::submit('Update Settings', ['class'=> 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-header bg-transparent"><h4 class="mb-0">WhatsApp Sharing Settings</h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {{Form::label('wa_interference_template', 'Interference Ratio Narrative Template', ['class' => 'form-control-label'])}}
                                {{ Form::textarea('wa_interference_template', setting('wa_interference_template', "Selamat Siang,\n\nBerikut Report Adjust Channel Interference - Pukul [WAKTU]. Detail interference Ratio yang cukup tinggi sebagai berikut :\n\n• [AP_NAME] : [UTIL_BEFORE]% / Channel [CHANNEL_BEFORE]\n\nSetelah dilakukan penyesuaian, Rate interference nya menjadi berikut :\n\n• [AP_NAME] : [UTIL_AFTER]% / Channel [CHANNEL_AFTER]\n\nBerikut laporan interference Ratio kami sampaikan.\n\nTerimakasih"), ['class'=>"form-control", 'rows' => 12])}}
                                <small class="text-muted">
                                    Gunakan tag variabel berikut ini yang akan terganti secara otomatis: 
                                    <code>[WAKTU]</code>, <code>[AP_NAME]</code>, <code>[CHANNEL_BEFORE]</code>, <code>[CHANNEL_AFTER]</code>, <code>[UTIL_BEFORE]</code>, <code>[UTIL_AFTER]</code>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-12 mt-4 text-right">
                            {!! Form::submit('Update Settings', ['class'=> 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
            </div>
            {{Form::close()}}

        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>
    <script>
        jQuery(document).ready(function(){
            jQuery('#uploadFile').filemanager('file');
        })
    </script>
@endpush
