@extends('layouts.app')
@push('pg_btn')
@can('create-project')
    <a href="{{ route('project.create') }}" class="btn btn-sm btn-neutral">Tambah Data Projects</a>
@endcan
@endpush


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-5">
                <div class="card-header bg-transparent">
                    <div class="row">
                        <div class="col-lg-6">
                            <h3 class="mb-0">Data Semua Project</h3>
                        </div>
                                
                        



<div class="col-lg-6">
    {!! Form::open(['route' => 'project.index', 'method'=>'get', 'onsubmit' => 'return showLoading()']) !!}
    <div class="row g-2 align-items-end">

        <!-- SEARCH -->
        <div class="col-md-7">
            <label class="small text-muted">Search Project</label>
            {{ Form::text('search', request()->query('search'), [
                'class' => 'form-control form-control-sm',
                'placeholder' => 'Nama project / Tahun',
                'onkeypress' => 'if(event.keyCode==13) showLoading()'
            ]) }}
        </div>

        <!-- FILTER TIPE -->
        <div class="col-md-5">
            <label class="small text-muted">Tipe Project</label>
            {{ Form::select('status_maintenance', $tipepm, request('status_maintenance'), [ 
                'class'=> 'selectpicker form-control form-control-sm',
                'placeholder' => 'Semua Tipe',
                'onchange' => 'this.form.submit()'
            ]) }}
        </div>

    </div>
    {!! Form::close() !!}
</div>

                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div>
                            <table class="table table-hover align-items-center">
                                <thead class="thead-light">
                                <tr>
                                    <th scope="col">Tahun</th>
                                    <th scope="col">Nama Project </th>
                                    <th scope="col">Nomor SPK</th>
                                    <th scope="col">Customer / Client</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Project Manager</th>

                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody class="list">
                                @foreach($projects as $project)
                                    <tr>
                                        <th scope="row">
                                            <div class="mx-w-440 d-flex flex-wrap">
                                                {{$project->tahun }}
                                            </div>
                                        </th>
                                        <td class="budget">
                                            {{$project->nama_project}}
                                        </td>
                                        <td>
                                            {{$project->nomor_spk}}
                                        </td>
                                        <td>
                                            {{$project->customer}}
                                        </td>
                                      
                                    <!--  {{-- Kolom Status --}} -->
        
<td>
    @if($project->status_implementation == 1)
        <span class="badge badge-primary">Implementation</span>
        <small class="d-block">{{ $project->sta_i ?? 'N/A' }}</small>
    @elseif($project->status_implementation == 2)
        <span class="badge badge-success">Maintenance</span>
        <small class="d-block">{{ $project->sta_m ?? 'N/A' }}</small>
    @else
        <span class="badge badge-secondary">Other</span>
        <small class="d-block">{{ $project->sta_i ?? $project->sta_m ?? 'N/A' }}</small>
    @endif
</td>
                                        
                                    <!--  {{-- Kolom Project Manager --}} -->
                                   
<td>
    @if($project->status_implementation == 1)
        {{-- Jika status implementation = 1, tampilkan pm_implementasi saja --}}
        <div>
            <strong>Implementation PM:</strong> {{ $project->pm_implementasi ?? 'N/A' }}
        </div>
    @elseif($project->status_implementation == 2)
        {{-- Jika status implementation = 2, cek status_maintenance --}}
        @if($project->status_maintenance == 2)
            {{-- Jika status_maintenance = 2, tampilkan KEDUANYA --}}
            <div>
                <strong>Implementation PM:</strong> {{ $project->pm_implementasi ?? 'N/A' }}
            </div>
            <div>
                <strong>Maintenance PM:</strong> {{ $project->pm_maintenance ?? 'N/A' }}
            </div>
        @else
            {{-- Jika status_maintenance bukan 2, tampilkan pm_maintenance saja --}}
            <div>
                <strong>Maintenance PM:</strong> {{ $project->pm_maintenance ?? 'N/A' }}
            </div>
        @endif
    @else
        {{-- Default --}}
        <div>
            {{ $project->pm_implementasi ?? $project->pm_maintenance ?? 'N/A' }}
        </div>
    @endif
</td>
                                        
                                <td class="text-center">
                                            @can('view-project')
                                            <a class="btn btn-primary btn-sm m-1" data-toggle="tooltip" data-placement="top" title="View and edit post details" href="{{route('project.show', $project->id_project)}}">
                                                <i class="fa fa-eye" aria-hidden="true"></i>
                                            </a>
                                            @endcan
                                            @can('update-project')
                                            <a class="btn btn-info btn-sm m-1" data-toggle="tooltip" data-placement="top" title="Edit post details" href="{{route('project.edit',$project->id_project)}}">
                                                <i class="fa fa-edit" aria-hidden="true"></i>
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot >
                                <tr>
                                    <td colspan="6">
                                        {{ $projects->withQueryString()->links() }}
                                    </td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
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
                            //$.alert('Confirmed!');
                        },
                        cancel: function () {
                            //$.alert('Canceled!');
                        }
                    }
                });
            })
        })

    </script>

<script>
function showLoading() {
    // Tampilkan loading dengan alert sederhana
    const loading = document.createElement('div');
    loading.id = 'loading';
    loading.innerHTML = `
        <div style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;">
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);">
                <div class="spinner-border text-light"></div>
                <p class="text-white mt-2">Searching...</p>
            </div>
        </div>
    `;
    document.body.appendChild(loading);
    
    // Otomatis hilang setelah 3 detik (jika halaman lambat)
    setTimeout(() => {
        const loadingEl = document.getElementById('loading');
        if (loadingEl) loadingEl.remove();
    }, 3000);
    
    return true; // Lanjutkan submit form
}
</script>


@endpush
