<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ $title }}</h6>
    </div>
    <div class="card-body">
        <div class="chart-{{ $type ?? 'area' }}">
            <canvas id="{{ $id }}"></canvas>
        </div>
        @if(isset($footer))
        <div class="mt-4 text-center small">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
