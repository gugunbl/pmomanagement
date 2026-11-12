<div class="header bg-primary app-page-hero">
    <div class="container-fluid">
        <div class="header-body">
            <div class="row align-items-center app-page-heading">
                <div class="col-lg-7 col-12">
                    <span class="app-page-eyebrow">Workspace</span>
                    <h1 class="app-page-title">{{ ($title ?? 'Dashboard') }}</h1>
                    <p class="app-page-subtitle mb-0">Kelola informasi dan aktivitas operasional dalam satu tampilan.</p>
                </div>
                <div class="col-lg-5 col-12 text-lg-right app-page-actions">
                    @stack('pg_btn')
                </div>
                <div class="col-lg-12 col-12">
                    @include('flash::message')
                    @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger mt-4">{{$error}}</div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
