
<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\Attachment;
use App\Http\Requests\StoreProposalRequest;
use App\Services\ProposalCalculationService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    protected $calculationService;
    protected $notificationService;

    public function __construct(
        ProposalCalculationService $calculationService,
        NotificationService $notificationService
    ) {
        $this->calculationService = $calculationService;
        $this->notificationService = $notificationService;
        $this->middleware('auth');
    }

    /**
     * Exibe a lista de propostas do usuário
     */
    public function index(Request $request)
    {
        $query = Proposal::with(['bidding', 'bidding.agency'])
                        ->where('user_id', Auth::id());

        // Filtros
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('bidding', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%");
            });
        }

        // Ordenação
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação
        $proposals = $query->paginate(15);

        return view('proposals.index', compact('proposals'));
    }

    /**
     * Cria uma nova proposta para uma licitação
     */
    public function create(Bidding $bidding)
    {
        // Verifica se a licitação está ativa e se pode receber propostas
        if (!$bidding->canSubmitProposal()) {
            return redirect()->route('biddings.show', $bidding)
                            ->with('error', 'Esta licitação não está aceitando propostas no momento.');
        }

        // Verifica se já existe um rascunho para esta licitação
        $existingDraft = Proposal::where('bidding_id', $bidding->id)
                                ->where('user_id', Auth::id())
                                ->where('status', 'draft')
                                ->first();

        if ($existingDraft) {
            return redirect()->route('proposals.edit', $existingDraft);
        }

        // Cria uma nova proposta em rascunho
        $proposal = $this->calculationService->createDraftProposal($bidding, Auth::id());

        return redirect()->route('proposals.edit', $proposal);
    }

    /**
     * Exibe o formulário para editar uma proposta
     */
    public function edit(Proposal $proposal)
    {
        // Verifica se a proposta pertence ao usuário logado
        if ($proposal->user_id !== Auth::id()) {
            abort(403);
        }

        // Verifica se a proposta pode ser editada
        if (!$proposal->canEdit()) {
            return redirect()->route('proposals.show', $proposal)
                            ->with('error', 'Esta proposta não pode ser editada.');
        }

        $proposal->load(['bidding', 'bidding.agency', 'items', 'items.bidding_item', 'attachments']);

        // Calcula o lucro estimado
        $profitAnalysis = $this->calculationService->calculateEstimatedProfit($proposal);

        // Simula diferentes cenários de desconto
        $discountScenarios = $this->calculationService->simulateDiscountScenarios($proposal);

        return view('proposals.edit', compact('proposal', 'profitAnalysis', 'discountScenarios'));
    }

    /**
     * Atualiza uma proposta
     */
    public function update(StoreProposalRequest $request, Proposal $proposal)
    {
        // Verifica se a proposta pertence ao usuário logado
        if ($proposal->user_id !== Auth::id()) {
            abort(403);
        }

        // Verifica se a proposta pode ser editada
        if (!$proposal->canEdit()) {
            return redirect()->route('proposals.show', $proposal)
                            ->with('error', 'Esta proposta não pode ser editada.');
        }

        try {
            DB::beginTransaction();

            // Atualiza os totais da proposta
            $proposal->updateTotals();

            DB::commit();

            return redirect()->route('proposals.edit', $proposal)
                          ->with('success', 'Proposta atualizada com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                      ->with('error', 'Erro ao atualizar proposta: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de uma proposta
     */
    public function show(Proposal $proposal)
    {
        // Verifica se a proposta pertence ao usuário logado
        if ($proposal->user_id !== Auth::id()) {
            abort(403);
        }

        $proposal->load(['bidding', 'bidding.agency', 'items', 'items.bidding_item', 'attachments']);

        // Calcula o lucro estimado
        $profitAnalysis = $this->calculationService->calculateEstimatedProfit($proposal);

        return view('proposals.show', compact('proposal', 'profitAnalysis'));
    }

    /**
     * Envia a proposta para a licitação
     */
    public function submit(Proposal $proposal)
    {
        // Verifica se a proposta pertence ao usuário logado
        if ($proposal->user_id !== Auth::id()) {
            abort(403);
        }

        // Verifica se a proposta pode ser editada e enviada
        if (!$proposal->canEdit()) {
            return redirect()->route('proposals.show', $proposal)
                          ->with('error', 'Esta proposta não pode ser enviada.');
        }

        try {
            if ($proposal->submit()) {
                // Notifica sobre o envio da proposta
                $this->notificationService->notifyProposalSubmitted($proposal);

                return redirect()->route('proposals.show', $proposal)
                              ->with('success', 'Proposta enviada com sucesso.');
            } else {
                return back()->with('error', 'Não foi possível enviar a proposta.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao enviar proposta: ' . $e->getMessage());
        }
    }

    /**
     * Cancela uma proposta
     */
    public function cancel(Proposal $proposal)
    {
        // Verifica se a proposta pertence ao usuário logado
        if ($proposal->user_id !== Auth::id()) {
            abort(403);
        }

        // Apenas propostas em rascunho ou enviadas podem ser canceladas
        if (!in_array($proposal->status, ['draft', 'submitted'])) {
            return redirect()->route('proposals.show', $proposal)
                          ->with('error', 'Esta proposta não pode ser cancelada.');
        }

        $proposal->status = 'cancelled';
        $proposal->save();

        return redirect()->route('proposals.index')
                      ->with('success', 'Proposta cancelada com sucesso.');
    }

    /**
     * Download de anexo
     */
    public function downloadAttachment(Attachment $attachment)
    {
        // Verifica se o anexo pertence a uma proposta
        if ($attachment->related_type !== 'proposal') {
            abort(404);
        }

        // Verifica se o anexo pertence a uma proposta do usuário logado
        $proposal = Proposal::find($attachment->related_id);
        if (!$proposal || $proposal->user_id !== Auth::id()) {
            abort(403);
        }

        if (!Storage::exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::download(
            $attachment->file_path,
            $attachment->file_name
        );
    }

    /**
     * Duplica uma proposta existente
     */
    public function duplicate(Proposal $proposal)
    {
        // Verifica se a proposta pertence ao usuário logado
        if ($proposal->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            // Cria uma nova proposta
            $newProposal = Proposal::create([
                'bidding_id' => $proposal->bidding_id,
                'user_id' => Auth::id(),
                'status' => 'draft',
                'total_value' => 0,
                'discount_percentage' => 0,
                'notes' => $proposal->notes . ' (Duplicado)',
            ]);

            // Duplica os itens
            foreach ($proposal->items as $item) {
                ProposalItem::create([
                    'proposal_id' => $newProposal->id,
                    'bidding_item_id' => $item->bidding_item_id,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'notes' => $item->notes,
                ]);
            }

            // Atualiza os totais
            $newProposal->updateTotals();

            DB::commit();

            return redirect()->route('proposals.edit', $newProposal)
                          ->with('success', 'Proposta duplicada com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao duplicar proposta: ' . $e->getMessage());
        }
    }
} itens da proposta
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $itemId => $itemData) {
                    $item = ProposalItem::find($itemId);

                    if ($item && $item->proposal_id == $proposal->id) {
                        $this->calculationService->updateItemPrice($item, $itemData['unit_price']);

                        if (isset($itemData['notes'])) {
                            $item->notes = $itemData['notes'];
                            $item->save();
                        }
                    }
                }
            }

            // Processa os anexos
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('proposal_attachments');

                    $attachment = new Attachment([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                        'description' => $request->input('attachment_description', ''),
                        'uploaded_by' => Auth::id(),
                    ]);

                    $proposal->attachments()->save($attachment);
                }
            }

            // Remove anexos marcados para exclusão
            if ($request->has('remove_attachments') && is_array($request->remove_attachments)) {
                $attachments = Attachment::whereIn('id', $request->remove_attachments)
                                        ->where('related_type', 'proposal')
                                        ->where('related_id', $proposal->id)
                                        ->get();

                foreach ($attachments as $attachment) {
                    Storage::delete($attachment->file_path);
                    $attachment->delete();
                }
            }

            // Atualiza as notas da proposta
            if ($request->has('notes')) {
                $proposal->notes = $request->notes;
                $proposal->save();
            }

            // Aplica desconto geral se solicitado
            if ($request->has('apply_discount') && $request->has('discount_percentage')) {
                $this->calculationService->applyOverallDiscount($proposal, $request->discount_percentage);
            }

            // Atualiza os
