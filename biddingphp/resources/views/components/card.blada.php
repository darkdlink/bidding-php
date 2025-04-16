<div class="card shadow mb-4 {{ $class ?? '' }}">
    @if(isset($header))
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ $header }}</h6>
        @if(isset($headerActions))
        <div class="dropdown no-arrow">
            {{ $headerActions }}
        </div>
        @endif
    </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
    @if(isset($footer))
    <div class="card-footer">
        {{ $footer }}
    </div>
    @endif
</div>
