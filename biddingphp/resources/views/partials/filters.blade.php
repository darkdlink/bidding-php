{{-- Componente de filtros para listagens --}}
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
    </div>
    <div class="card-body">
        <form action="{{ $route ?? route('biddings.index') }}" method="GET" class="form-inline">
            <div class="form-group mb-2 mr-2">
                <label for="search" class="sr-only">Busca</label>
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="Buscar..." value="{{ request('search') }}">
            </div>

            @if(isset($statuses))
            <div class="form-group mb-2 mr-2">
                <label for="status" class="sr-only">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="">Todos os Status</option>
                    @foreach($statuses as $key => $value)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(isset($agencies))
            <div class="form-group mb-2 mr-2">
                <label for="agency_id" class="sr-only">Órgão</label>
                <select class="form-control" id="agency_id" name="agency_id">
                    <option value="">Todos os Órgãos</option>
                    @foreach($agencies as $agency)
                        <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                            {{ $agency->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if(isset($types))
            <div class="form-group mb-2 mr-2">
                <label for="type" class="sr-only">Tipo</label>
                <select class="form-control" id="type" name="type">
                    <option value="">Todos os Tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <button type="submit" class="btn btn-primary mb-2">Filtrar</button>
            <a href="{{ $route ?? route('biddings.index') }}" class="btn btn-secondary mb-2 ml-2">Limpar</a>
        </form>
    </div>
</div>
