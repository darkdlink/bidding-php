<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ $title ?? 'Filtros' }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ $action }}" method="GET" class="form-inline">
            {{ $slot }}

            <button type="submit" class="btn btn-primary mb-2">Filtrar</button>
            <a href="{{ $action }}" class="btn btn-secondary mb-2 ml-2">Limpar</a>
        </form>
    </div>
</div>
