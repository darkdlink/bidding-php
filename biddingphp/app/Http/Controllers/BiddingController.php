<?php

namespace App\Http\Controllers;

use App\Models\Bidding;
use App\Models\BiddingAgency;
use App\Models\BiddingItem;
use App\Models\Attachment;
use App\Http\Requests\StoreBiddingRequest;
use App\Services\ScrapingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BiddingController extends Controller
{
    protected $scrapingService;
    protected $notificationService;

    public function __construct(ScrapingService $scrapingService, NotificationService $notificationService)
    {
        $this->scrapingService = $scrapingService;
        $this->notificationService = $notificationService;
        $this->middleware('auth');
    }

    /**
     * Exibe a lista de licitações
     */
    public function index(Request $request)
    {
        $query = Bidding::with('agency');

        // Filtros
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('agency_id') && !empty($request->agency_id)) {
            $query->where('agency_id', $request->agency_id);
        }

        if ($request->has('type') && !empty($request->type)) {
            $query->where('bidding_type', $request->type);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ordenação
        $sortField = $request->get('sort', 'closing_date');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação
        $biddings = $query->paginate(15);

        // Dados para filtros no frontend
        $agencies = BiddingAgency::orderBy('name')->get();
        $biddingTypes = Bidding::select('bidding_type')
                                ->distinct()
                                ->orderBy('bidding_type')
                                ->pluck('bidding_type');

        return view('biddings.index', compact('biddings', 'agencies', 'biddingTypes'));
    }

    /**
     * Exibe o formulário para criar uma nova licitação
     */
    public function create()
    {
        $agencies = BiddingAgency::orderBy('name')->get();
        return view('biddings.create', compact('agencies'));
    }

    /**
     * Armazena uma nova licitação
     */
    public function store(StoreBiddingRequest $request)
    {
        try {
            DB::beginTransaction();

            // Cria a licitação
            $bidding = Bidding::create($request->validated());

            // Processa os itens da licitação
            if ($request->has('items') && is_array($request->items)) {
                foreach ($request->items as $itemData) {
                    $bidding->items()->create([
                        'item_number' => $itemData['item_number'] ?? null,
                        'description' => $itemData['description'],
                        'quantity' => $itemData['quantity'],
                        'unit' => $itemData['unit'] ?? null,
                        'estimated_unit_price' => $itemData['estimated_unit_price'] ?? null,
                    ]);
                }
            }

            // Processa os anexos
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('bidding_attachments');

                    $attachment = new Attachment([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                        'description' => $request->input('attachment_description', ''),
                        'uploaded_by' => Auth::id(),
                    ]);

                    $bidding->attachments()->save($attachment);
                }
            }

            // Notifica usuários interessados
            $this->notificationService->notifyNewBidding($bidding);

            DB::commit();

            return redirect()->route('biddings.show', $bidding)
                            ->with('success', 'Licitação criada com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'Erro ao criar licitação: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de uma licitação
     */
    public function show(Bidding $bidding)
    {
        $bidding->load(['agency', 'items', 'attachments', 'proposals' => function($query) {
            $query->where('user_id', Auth::id());
        }]);

        $userHasProposal = $bidding->proposals->where('user_id', Auth::id())->count() > 0;

        return view('biddings.show', compact('bidding', 'userHasProposal'));
    }

    /**
     * Exibe o formulário para editar uma licitação
     */
    public function edit(Bidding $bidding)
    {
        $this->authorize('update', $bidding);

        $bidding->load(['agency', 'items', 'attachments']);
        $agencies = BiddingAgency::orderBy('name')->get();

        return view('biddings.edit', compact('bidding', 'agencies'));
    }

    /**
     * Atualiza uma licitação existente
     */
    public function update(StoreBiddingRequest $request, Bidding $bidding)
    {
        $this->authorize('update', $bidding);

        try {
            DB::beginTransaction();

            // Atualiza a licitação
            $bidding->update($request->validated());

            // Atualiza os itens
            if ($request->has('items') && is_array($request->items)) {
                // Remove itens que não estão na requisição
                $keepIds = collect($request->items)->pluck('id')->filter()->toArray();
                $bidding->items()->whereNotIn('id', $keepIds)->delete();

                foreach ($request->items as $itemData) {
                    if (isset($itemData['id']) && !empty($itemData['id'])) {
                        // Atualiza item existente
                        $item = BiddingItem::find($itemData['id']);
                        if ($item && $item->bidding_id == $bidding->id) {
                            $item->update([
                                'item_number' => $itemData['item_number'] ?? null,
                                'description' => $itemData['description'],
                                'quantity' => $itemData['quantity'],
                                'unit' => $itemData['unit'] ?? null,
                                'estimated_unit_price' => $itemData['estimated_unit_price'] ?? null,
                            ]);
                        }
                    } else {
                        // Cria novo item
                        $bidding->items()->create([
                            'item_number' => $itemData['item_number'] ?? null,
                            'description' => $itemData['description'],
                            'quantity' => $itemData['quantity'],
                            'unit' => $itemData['unit'] ?? null,
                            'estimated_unit_price' => $itemData['estimated_unit_price'] ?? null,
                        ]);
                    }
                }
            }

            // Processa os anexos
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('bidding_attachments');

                    $attachment = new Attachment([
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                        'description' => $request->input('attachment_description', ''),
                        'uploaded_by' => Auth::id(),
                    ]);

                    $bidding->attachments()->save($attachment);
                }
            }

            // Remove anexos marcados para exclusão
            if ($request->has('remove_attachments') && is_array($request->remove_attachments)) {
                $attachments = Attachment::whereIn('id', $request->remove_attachments)
                                        ->where('related_type', 'bidding')
                                        ->where('related_id', $bidding->id)
                                        ->get();

                foreach ($attachments as $attachment) {
                    Storage::delete($attachment->file_path);
                    $attachment->delete();
                }
            }

            // Notifica sobre alterações
            if ($bidding->wasChanged()) {
                $this->notificationService->notifyBiddingUpdated($bidding);
            }

            DB::commit();

            return redirect()->route('biddings.show', $bidding)
                            ->with('success', 'Licitação atualizada com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'Erro ao atualizar licitação: ' . $e->getMessage());
        }
    }

    /**
     * Remove uma licitação
     */
    public function destroy(Bidding $bidding)
    {
        $this->authorize('delete', $bidding);

        try {
            // Remove anexos
            foreach ($bidding->attachments as $attachment) {
                Storage::delete($attachment->file_path);
            }

            $bidding->delete();

            return redirect()->route('biddings.index')
                            ->with('success', 'Licitação removida com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao remover licitação: ' . $e->getMessage());
        }
    }

    /**
     * Exibe a página para realizar scraping manual
     */
    public function showScrapeForm()
    {
        $this->authorize('scrape-biddings');

        $agencies = BiddingAgency::whereHas('scrapingConfigs')
                                ->orderBy('name')
                                ->get();

        return view('biddings.scrape', compact('agencies'));
    }

    /**
     * Executa o scraping manual
     */
    public function scrape(Request $request)
    {
        $this->authorize('scrape-biddings');

        $request->validate([
            'agency_id' => 'required|exists:bidding_agencies,id',
        ]);

        $agency = BiddingAgency::findOrFail($request->agency_id);
        $config = $agency->scrapingConfigs()->where('active', true)->first();

        if (!$config) {
            return back()->with('error', 'Não há configuração de scraping ativa para esta agência.');
        }

        $result = $this->scrapingService->scrapeAgencyBiddings($config);

        if ($result) {
            return back()->with('success', 'Scraping realizado com sucesso.');
        } else {
            return back()->with('error', 'Erro ao realizar scraping. Verifique os logs.');
        }
    }

    /**
     * Download de anexo
     */
    public function downloadAttachment(Attachment $attachment)
    {
        // Verifica se o anexo pertence a uma licitação
        if ($attachment->related_type !== 'bidding') {
            abort(404);
        }

        if (!Storage::exists($attachment->file_path)) {
            abort(404);
        }

        return Storage::download(
            $attachment->file_path,
            $attachment->file_name
        );
    }
}
