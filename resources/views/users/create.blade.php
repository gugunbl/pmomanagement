@extends('layouts.app')
@push('pg_btn')
    <a href="{{route('users.index')}}" class="btn btn-sm btn-neutral">All Users</a>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-body">
                    {!! Form::open(['route' => 'users.store', 'files' => true]) !!}
                    <h6 class="heading-small text-muted mb-4">User information</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('name', 'Name', ['class' => 'form-control-label']) }}
                                        {{ Form::text('name', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('email', 'E-mail', ['class' => 'form-control-label']) }}
                                        {{ Form::email('email', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('phone_number', 'Phone number', ['class' => 'form-control-label']) }}
                                        {{ Form::text('phone_number', null, ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('profile_photo', 'Photo', ['class' => 'form-control-label']) }}
                                        <div class="input-group">
                                            <span class="input-group-btn">
                                              <a id="uploadFile" data-input="thumbnail" data-preview="holder" class="btn btn-secondary">
                                                <i class="fa fa-picture-o"></i> Choose Photo
                                              </a>
                                            </span>
                                            <input id="thumbnail" class="form-control d-none" type="text" name="profile_photo">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('role', 'Select Role', ['class' => 'form-control-label']) }}
                                        {{ Form::select('role', $roles, null, [ 'class'=> 'selectpicker form-control', 'data-live-search' => 'true', 'placeholder' => 'Select role...']) }}
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        {{ Form::label('id_project', 'Handle Project (Select 1 or more, Leave empty for All)', ['class' => 'form-control-label']) }}
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input type="checkbox" class="custom-control-input" id="all_projects_check">
                                            <label class="custom-control-label" for="all_projects_check">All Projects (Admin Mode)</label>
                                        </div>
                                        {{ Form::select('id_project[]', $projects, null, [ 
                                            'class'=> 'selectpicker form-control', 
                                            'id' => 'project_select', 
                                            'multiple' => 'multiple',
                                            'data-live-search' => 'true',
                                            'data-actions-box' => 'true',
                                            'data-selected-text-format' => 'count > 3',
                                            'title' => 'Pilih Project...'
                                        ]) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4" />
                        <!-- Address -->
                        <h6 class="heading-small text-muted mb-4">Password information</h6>
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('password', 'Password', ['class' => 'form-control-label']) }}
                                        {{ Form::password('password', ['class' => 'form-control']) }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('password_confirmation', 'Confirm password', ['class' => 'form-control-label']) }}
                                        {{ Form::password('password_confirmation', ['class' => 'form-control']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4" />
                        <div class="pl-lg-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" name="status" value="1" class="custom-control-input" id="status">
                                        {{ Form::label('status', 'Status', ['class' => 'custom-control-label']) }}
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    {{ Form::submit('Submit', ['class'=> 'mt-5 btn btn-primary']) }}
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* === SELECT2 ARGON THEME FIX === */
.select2-container--bootstrap4 .select2-selection {
    min-height: 44px;
    border: 1px solid #cad1d7;
    border-radius: .375rem;
    padding: .375rem .75rem;
    background-color: #fff;
}

.select2-container--bootstrap4 .select2-selection--single {
    display: flex;
    align-items: center;
}

.select2-container--bootstrap4 .select2-selection__rendered {
    padding-left: 0;
    line-height: normal;
    color: #8898aa;
}

.select2-container--bootstrap4 .select2-selection__arrow {
    height: 100%;
    top: 0;
    right: 8px;
}

.select2-container--bootstrap4.select2-container--focus .select2-selection,
.select2-container--bootstrap4.select2-container--open .select2-selection {
    border-color: #5e72e4;
    box-shadow: 0 0 0 0.2rem rgba(94,114,228,.25);
}

.select2-search--dropdown .select2-search__field {
    height: 40px;
    padding: .375rem .75rem;
    border-radius: .375rem;
}
</style>
@endpush

@push('scripts')
    <script src="{{ asset('vendor/laravel-filemanager/js/stand-alone-button.js') }}"></script>
    <script>
        jQuery(document).ready(function(){
            jQuery('#uploadFile').filemanager('file');
            
            // Initialize selectpicker
            $('.selectpicker').selectpicker();

            $('#all_projects_check').on('change', function() {
                if($(this).is(':checked')) {
                    $('#project_select').selectpicker('deselectAll');
                    $('#project_select').prop('disabled', true);
                } else {
                    $('#project_select').prop('disabled', false);
                }
                $('#project_select').selectpicker('refresh');
            });
        })
    </script>
@endpush
