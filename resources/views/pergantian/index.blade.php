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
    div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('pergantian.index') }}">
                            <div class="form-row align-items-end">

                                {{-- Project --}}
                                <div class="form-group col-md-4">
                                    <label>Project</label>
                <select name="project_id" class="form-control select-project">
                    <option value="">Pilih Project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}"
                            {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->nama_project }}
                        </option>
                    @endforeach
                </select>                </div>

                {{-- Search --}}
                <div class="form-group col-md-4">
                    <label>Cari</label>
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Serial / Alasan..."
                           value="{{ request('search') }}">
                </div>

                {{-- Button --}}
                <div class="form-group col-md-4">
                    <button class="btn btn-primary">
                        Filter
                    </button>
                    <a href="{{ route('pergantian.index') }}"
                       class="btn btn-secondary">
                        Reset
                    </a>
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

<script>
$(function () {
    $('.select-project').select2({
        theme: 'bootstrap4',
        placeholder: 'Pilih Project',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0 // ← PAKSA SEARCH MUNCUL
    });
});
</script>

<style>
.select2-container--bootstrap4 .select2-selection--single {
    height: calc(1.5em + .75rem + 2px);
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: calc(1.5em + .75rem);
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
    height: calc(1.5em + .75rem);
}
</style>
@endpush


