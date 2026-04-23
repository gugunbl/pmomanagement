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
                </div>
            </div>
            <div class="card-body">
                {!! Form::open(['route' => 'interference-ratio.store', 'method' => 'POST', 'id' => 'formRatio']) !!}
                
                <h6 class="heading-small text-muted mb-4">Informasi Dasar & Perangkat</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Project</label>
                            @if($fixedProject)
                                {{ Form::select('id_project', $projects, null, ['class' => 'form-control', 'readonly' => 'readonly', 'id' => 'project_select']) }}
                                <small class="text-info">Project ini sudah ditentukan untuk akun Anda.</small>
                            @else
                                {{ Form::select('id_project', ['' => 'Pilih Project...'] + $projects->toArray(), null, ['class' => 'form-control select2', 'id' => 'project_select']) }}
                            @endif
                            @error('id_project') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required value="{{ date('Y-m-d') }}">
                            @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-control-label">Waktu Monitor</label>
                            @php
                                $waktu_list = ['' => 'Pilih Waktu...'];
                                for ($i = 6; $i <= 23; $i++) {
                                    $h = str_pad($i, 2, '0', STR_PAD_LEFT) . '.00 WIB';
                                    $waktu_list[$h] = $h;
                                }
                            @endphp
                            {{ Form::select('waktu', $waktu_list, null, ['class' => 'form-control', 'required']) }}
                            @error('waktu') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4" />
                <div class="d-flex align-items-center mb-4">
                    <h6 class="heading-small text-muted mb-0">AP Name & Interference Data</h6>
                    <button type="button" id="btn-add-entry" class="btn btn-sm btn-success ml-3" title="Tambah AP Entry">
                        <i class="fas fa-plus"></i> Tambah AP
                    </button>
                </div>

                <div id="ap-entries-container">
                    {{-- Entry #1 (default) --}}
                    <div class="ap-entry-block border rounded p-3 mb-3 bg-white" data-index="0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge badge-primary entry-label">AP #1</span>
                            <button type="button" class="btn btn-sm btn-danger btn-remove-entry" title="Hapus entry ini" style="display:none;">
                                <i class="fas fa-minus"></i> Hapus
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-group mb-0">
                                    <label class="form-control-label d-block">AP Name (Pilih Perangkat)</label>
                                    <select name="entries[0][ap_name]" class="form-control ap-name-select select2" required disabled>
                                        <option value="">Pilih AP Name...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-secondary shadow-none border">
                                    <div class="card-body">
                                        <h5 class="card-title text-center">CHANNEL</h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label text-xs">Before</label>
                                                    <input type="number" name="entries[0][channel_before]" class="form-control interference-input" required disabled>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label text-xs">After</label>
                                                    <input type="number" name="entries[0][channel_after]" class="form-control interference-input" required disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-secondary shadow-none border">
                                    <div class="card-body">
                                        <h5 class="card-title text-center">UTILISASI INTERFERENCE</h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label text-xs">Before (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" name="entries[0][utilisasi_before]" class="form-control interference-input" required disabled>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="form-group mb-0">
                                                    <label class="form-control-label text-xs">After (%)</label>
                                                    <div class="input-group">
                                                        <input type="number" name="entries[0][utilisasi_after]" class="form-control interference-input" required disabled>
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Keterangan</label>
                            {{ Form::textarea('keterangan', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Isian keterangan tambahan...']) }}
                            @error('keterangan') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mt-4">
                    <div class="col-6 text-right offset-6 d-flex justify-content-end">
                        <a href="{{ route('interference-ratio.index') }}" class="btn btn-secondary px-5 mr-3" style="background-color: #0b5e79; color: white; border: none;">Batal</a>
                        <button type="submit" class="btn btn-primary px-5" style="background-color: #0b4ab1;">Simpan Data</button>
                    </div>
                </div>

                {!! Form::close() !!}
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
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Store AP options globally so we can populate new entries
    var apOptions = '<option value="">Pilih AP Name...</option>';
    var entryIndex = 0;

    function initSelect2(el) {
        $(el).select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: false
        });
    }

    // Init select2 on existing elements
    $('.select2').each(function() {
        initSelect2(this);
    });

    function fetchSNs(projectId) {
        if (!projectId) {
            apOptions = '<option value="">Pilih AP Name...</option>';
            $('.ap-name-select').html(apOptions).trigger('change');
            return;
        }

        let url = "{{ route('cases.get-sn', ':id') }}";
        url = url.replace(':id', projectId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                apOptions = '<option value="">Pilih AP Name...</option>';
                $.each(data, function(key, value) {
                    apOptions += '<option value="' + value + '">' + value + '</option>';
                });
                // Populate all existing AP selects
                $('.ap-name-select').each(function() {
                    var currentVal = $(this).val();
                    $(this).html(apOptions);
                    if (currentVal) $(this).val(currentVal);
                    $(this).trigger('change');
                });
            }
        });
    }

    // Enable/disable AP selects based on project selection
    function toggleApSelects(hasProject) {
        $('.ap-name-select').each(function() {
            $(this).prop('disabled', !hasProject);
            if (!hasProject) {
                $(this).val('').trigger('change');
                // Also disable interference inputs
                $(this).closest('.ap-entry-block').find('.interference-input').prop('disabled', true).val('');
            }
        });
    }

    // Enable/disable interference inputs based on AP Name selection
    $(document).on('change', '.ap-name-select', function() {
        var block = $(this).closest('.ap-entry-block');
        var hasValue = !!$(this).val();
        block.find('.interference-input').prop('disabled', !hasValue);
        if (!hasValue) {
            block.find('.interference-input').val('');
        }
    });

    $('#project_select').on('change', function() {
        var projectId = $(this).val();
        toggleApSelects(!!projectId);
        fetchSNs(projectId);
    });

    let initialProject = $('#project_select').val();
    if (initialProject) {
        toggleApSelects(true);
        fetchSNs(initialProject);
    }

    // Update labels and remove button visibility
    function updateEntries() {
        var blocks = $('.ap-entry-block');
        blocks.each(function(i) {
            $(this).find('.entry-label').text('AP #' + (i + 1));
            // Show remove button only if more than 1 entry
            if (blocks.length > 1) {
                $(this).find('.btn-remove-entry').show();
            } else {
                $(this).find('.btn-remove-entry').hide();
            }
        });
    }

    // Add new AP entry
    $('#btn-add-entry').on('click', function() {
        entryIndex = $('.ap-entry-block').length;

        var newBlock = $('.ap-entry-block').first().clone();
        
        // Destroy select2 on the cloned element before manipulating
        newBlock.find('.select2-container').remove();
        newBlock.find('.ap-name-select').removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('aria-hidden').removeAttr('tabindex');
        
        // Update data-index
        newBlock.attr('data-index', entryIndex);

        // Update all input names to new index
        newBlock.find('select, input').each(function() {
            var name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace(/entries\[\d+\]/, 'entries[' + entryIndex + ']'));
            }
            // Clear values
            if ($(this).is('select')) {
                $(this).html(apOptions);
                // AP select enabled if project is selected
                $(this).prop('disabled', !$('#project_select').val());
            } else {
                $(this).val('');
                // Interference inputs always disabled on new entry (until AP is selected)
                if ($(this).hasClass('interference-input')) {
                    $(this).prop('disabled', true);
                }
            }
        });

        // Append and init select2
        $('#ap-entries-container').append(newBlock);
        initSelect2(newBlock.find('.ap-name-select'));

        updateEntries();

        // Scroll to new entry
        $('html, body').animate({
            scrollTop: newBlock.offset().top - 100
        }, 300);
    });

    // Remove AP entry
    $(document).on('click', '.btn-remove-entry', function() {
        if ($('.ap-entry-block').length > 1) {
            $(this).closest('.ap-entry-block').remove();
            updateEntries();
        }
    });

    // Form validation
    $('#formRatio').on('submit', function() {
        var valid = true;
        $('.ap-name-select').each(function() {
            if (!$(this).val()) {
                valid = false;
            }
        });
        if (!valid) {
            Swal.fire('Peringatan', 'Silahkan pilih AP Name untuk semua entry!', 'warning');
            return false;
        }
        return true;
    });
});
</script>
@endpush
