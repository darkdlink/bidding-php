@if($attachments->count() > 0)
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
                    <a href="{{ route($downloadRoute, $attachment) }}" class="btn btn-sm btn-outline-primary">
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
