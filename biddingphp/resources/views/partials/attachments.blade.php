{{-- Componente para exibir lista de anexos --}}
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Anexos</h6>
    </div>
    <div class="card-body">
        @if(isset($attachments) && $attachments->count() > 0)
            <ul class="list-group">
                @foreach($attachments as $attachment)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-file mr-2"></i>
                            {{ $attachment->file_name }}
                            @if($attachment->description)
                                <br><small class="text-muted">{{ $attachment->description }}</small>
                            @endif
                        </div>

                        <div class="btn-group">
                            <a href="{{ route($downloadRoute ?? 'attachments.download', $attachment) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-download"></i>
                            </a>

                            @if(isset($canRemove) && $canRemove)
                                <button type="button" class="btn btn-sm btn-outline-danger remove-attachment"
                                        data-attachment-id="{{ $attachment->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <input type="hidden" name="remove_attachments[]" id="remove_attachment_{{ $attachment->id }}" disabled>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-center text-muted">Nenhum anexo disponível.</p>
        @endif

        @if(isset($canAdd) && $canAdd)
        <div class="mt-3">
            <div class="form-group">
                <label for="attachments">Adicionar Anexos:</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="attachments" name="attachments[]" multiple>
                    <label class="custom-file-label" for="attachments">Escolher arquivos</label>
                </div>
                <small class="form-text text-muted">Você pode adicionar múltiplos arquivos (PDF, DOC, XLS, etc).</small>
            </div>

            <div class="form-group">
                <label for="attachment_description">Descrição dos Anexos:</label>
                <input type="text" class="form-control" id="attachment_description" name="attachment_description">
            </div>
        </div>
        @endif
    </div>
</div>
