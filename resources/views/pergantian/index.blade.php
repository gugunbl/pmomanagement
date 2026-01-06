@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="row mb-3">
        <div class="col">
            <h3 class="mb-0">Riwayat Pergantian Perangkat</h3>
            <small class="text-muted">
                Daftar pergantian perangkat berdasarkan project
            </small>
        </div>
    </div>

{{-- FILTER --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('pergantian.index') }}">
            <div class="form-row align-items-end">

                <div class="col-lg-4">
                    <div class="form-group">
                        {{ Form::label('id_project', 'Select Project', ['class' => 'form-control-label']) }}
                        {{ Form::select(
                            'id_project',
                            ['' => ''] + $projects->toArray(),
                            null,
                            [
                                'class'=> 'form-control select2',
                                'data-placeholder' => 'Ketik nama project...'
                            ]
                        ) }}
                    </div>
                </div>

                <div class="form-group col-md-4">
                    <label>Cari</label>
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Serial / Alasan..."
                           value="{{ request('search') }}">
                </div>

                <div class="form-group col-md-4">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('pergantian.index') }}" class="btn btn-secondary">Reset</a>
                </div>

            </div>
        </form>
    </div>
</div>



    {{-- TABLE --}}
    <div class="card">
        <div class="table-responsive" style="max-height:65vh">
        <table class="table table-bordered table-hover table-sm">
                <thead class="thead-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Project</th>
                    <th>Serial Lama</th>
                    <th>Serial Baru</th>
                    <th>Alasan</th>
                    <th width="120">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($pergantians as $row)
                    <tr>
                        <td>{{ $row->tanggal_pergantian }}</td>
                        <td style="white-space: normal">
                            {{ optional($row->perangkatLama->project)->nama_project }}
                        </td>
                        <td>{{ $row->perangkatLama->serialnumber }}</td>
                        <td>{{ optional($row->perangkatBaru)->serialnumber ?? '-' }}</td>
                        <td style="white-space: normal">
                            {{ $row->alasan_penggantian }}
                        </td>
                        <td>
                            <a href="{{ route('pergantian.pdf', $row->id_pergantian) }}"
                               class="btn btn-sm btn-danger">
                                PDF
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Data tidak ditemukan
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $pergantians->withQueryString()->links() }}
        </div>
    </div>

</div>
@endsection



@push('styles')



<style>
/* === FINAL SELECT2 FIX (Bootstrap 4 / Argon) === */
.select2-container {
    width: 100% !important;
}

.select2-container--bootstrap4 .select2-selection {
    min-height: 44px;
    border: 1px solid #cad1d7;
    border-radius: .375rem;
    padding: .375rem .75rem;
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

/* Search input di dropdown */
.select2-search--dropdown .select2-search__field {
    height: 40px;
    padding: .375rem .75rem;
    border-radius: .375rem;
}
</style>
@endpush


@push('scripts')
<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        allowClear: true,
        placeholder: function () {
            return $(this).data('placeholder');
        }
    });
});
</script>

@endpush
