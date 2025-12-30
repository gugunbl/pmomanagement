@extends('layouts.app')

@push('pg_btn')
@can('tambah-warehouse')
    <a href="{{ route('warehouse.tambah') }}" class="btn btn-sm btn-neutral">
        <i class="fa fa-plus"></i> Tambah Transaksi Warehouse
    </a>
@endcan
@endpush

@section('content')
<div class="container-fluid">

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Data Barang Masuk & Keluar Warehouse</h5>
        </div>

        <div class="card-body">

            <!-- FILTER -->
            <form method="GET" class="mb-3" onsubmit="return showLoading()">
                <div class="row">
                    <div class="col-md-3">
                        <select name="jenis_dokumen" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Semua Jenis --</option>
                            <option value="1" {{ request('jenis_dokumen') == 1 ? 'selected' : '' }}>
                                Barang Masuk
                            </option>
                            <option value="2" {{ request('jenis_dokumen') == 2 ? 'selected' : '' }}>
                                Barang Keluar
                            </option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No Dokumen</th>
                            <th>Tanggal Dokumen</th>
                            <th>Jenis Dokumen</th>
                            <th>Nama Pemohon</th>
                            <th class="text-center">Jumlah Barang</th>
                            <th class="text-center" width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($warehouses as $row)
                            <tr>
                                <td>{{ $row->no_dokumen }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->tgl_dokumen)->format('d-m-Y') }}</td>
                                <td>
                                    @if ($row->jenis_dokumen == 1)
                                        <span class="badge bg-primary">
                                            Barang Masuk
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            Barang Keluar
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $row->nama_pemohon }}</td>
                                <td class="text-center">
                                    {{ $row->jmlbarang }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('warehouse.show', $row->no_dokumen) }}"
                                       class="btn btn-sm btn-info"
                                       title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    @can('edit-warehouse')
                                    <a href="{{ route('warehouse.edit', $row->no_dokumen) }}"
                                       class="btn btn-sm btn-warning"
                                       title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @endcan
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

            <!-- PAGINATION -->
            <div class="d-flex justify-content-end mt-3">
                {{ $warehouses->withQueryString()->links() }}
            </div>

        </div>
    </div>

</div>
@endsection


@push('scripts')
<script>
jQuery(document).ready(function(){
    $('.delete').on('click', function(e){
        e.preventDefault();
        let that = jQuery(this);
        jQuery.confirm({
            icon: 'fas fa-wind-warning',
            closeIcon: true,
            title: 'Are you sure!',
            content: 'You can not undo this action.!',
            type: 'red',
            typeAnimated: true,
            buttons: {
                confirm: function () {
                    that.parent('form').submit();
                },
                cancel: function () {}
            }
        });
    });
});
</script>

<script>
function showLoading() {
    const loading = document.createElement('div');
    loading.id = 'loading';
    loading.innerHTML = `
        <div style="position:fixed;top:0;left:0;width:100%;height:100%;
                    background:rgba(0,0,0,0.5);z-index:9999;">
            <div style="position:absolute;top:50%;left:50%;
                        transform:translate(-50%,-50%);text-align:center;">
                <div class="spinner-border text-light"></div>
                <p class="text-white mt-2">Searching...</p>
            </div>
        </div>
    `;
    document.body.appendChild(loading);

    setTimeout(() => {
        const el = document.getElementById('loading');
        if (el) el.remove();
    }, 3000);

    return true;
}
</script>
@endpush
