<?php

namespace App\Http\Controllers;

use App\Models\ScrapingConfig;
use App\Models\BiddingAgency;
use App\Models\ScrapingLog;
use App\Http\Requests\ScrapingConfigRequest;
use App\Services\ScrapingService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class ScrapingConfigController extends Controller
{
    protected $scrapingService;

    /**
     * Construtor
     */
    public function __construct(ScrapingService $scrapingService)
    {
        $this->middleware(['auth', 'can:manage-scraping']);
        $this->scrapingService = $scrapingService;
    }

    /**
     * Exibe a lista de configurações de scraping.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = ScrapingConfig::with('agency');

        // Filtros
        if ($request->filled('agency_id')) {
            $query->where('agency_id', $request->agency_id);
        }

        if ($request->has('active') && $request->active !== '') {
            $query->where('active', (bool) $request->active);
        }

        // Ordenação
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação
        $configs = $query->paginate(15);

        // Lista de agências para o filtro
        $agencies = BiddingAgency::orderBy('name')->get();

        return view('admin.scraping-configs.index', compact('configs', 'agencies'));
    }

    /**
     * Exibe o formulário para criar uma nova configuração de scraping.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $agencies = BiddingAgency::orderBy('name')->get();
        return view('admin.scraping-configs.create', compact('agencies'));
    }

    /**
     * Armazena uma nova configuração de scraping.
     *
     * @param  \App\Http\Requests\ScrapingConfigRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ScrapingConfigRequest $request)
    {
        try {
            // Converte os seletores em JSON para armazenamento
            $selectors = json_encode($request->selectors);

            // Cria a configuração
            $config = ScrapingConfig::create([
                'agency_id' => $request->agency_id,
                'url' => $request->url,
                'selectors' => $selectors,
                'schedule' => $request->schedule,
                'active' => $request->has('active'),
            ]);

            return redirect()->route('admin.scraping-configs.index')
                           ->with('success', 'Configuração de scraping criada com sucesso.');
        } catch (\Exception $e) {
            return back()->withInput()
                       ->with('error', 'Erro ao criar configuração: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de uma configuração de scraping.
     *
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\View\View
     */
    public function show(ScrapingConfig $scrapingConfig)
    {
        $scrapingConfig->load('agency');

        // Obtém logs recentes
        $logs = ScrapingLog::where('config_id', $scrapingConfig->id)
                         ->orderBy('start_time', 'desc')
                         ->limit(10)
                         ->get();

        // Estatísticas
        $successCount = ScrapingLog::where('config_id', $scrapingConfig->id)
                               ->where('status', 'success')
                               ->count();

        $totalProcessed = ScrapingLog::where('config_id', $scrapingConfig->id)
                                 ->sum('items_processed');

        return view('admin.scraping-configs.show', compact('scrapingConfig', 'logs', 'successCount', 'totalProcessed'));
    }

    /**
     * Exibe o formulário para editar uma configuração de scraping.
     *
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\View\View
     */
    public function edit(ScrapingConfig $scrapingConfig)
    {
        $agencies = BiddingAgency::orderBy('name')->get();

        // Decodifica os seletores
        $selectors = json_decode($scrapingConfig->selectors, true) ?? [];

        return view('admin.scraping-configs.edit', compact('scrapingConfig', 'agencies', 'selectors'));
    }

    /**
     * Atualiza uma configuração de scraping.
     *
     * @param  \App\Http\Requests\ScrapingConfigRequest  $request
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ScrapingConfigRequest $request, ScrapingConfig $scrapingConfig)
    {
        try {
            // Converte os seletores em JSON para armazenamento
            $selectors = json_encode($request->selectors);

            // Atualiza a configuração
            $scrapingConfig->update([
                'agency_id' => $request->agency_id,
                'url' => $request->url,
                'selectors' => $selectors,
                'schedule' => $request->schedule,
                'active' => $request->has('active'),
            ]);

            return redirect()->route('admin.scraping-configs.index')
                           ->with('success', 'Configuração de scraping atualizada com sucesso.');
        } catch (\Exception $e) {
            return back()->withInput()
                       ->with('error', 'Erro ao atualizar configuração: ' . $e->getMessage());
        }
    }

    /**
     * Remove uma configuração de scraping.
     *
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ScrapingConfig $scrapingConfig)
    {
        try {
            DB::beginTransaction();

            // Remove os logs associados
            ScrapingLog::where('config_id', $scrapingConfig->id)->delete();

            // Remove a configuração
            $scrapingConfig->delete();

            DB::commit();

            return redirect()->route('admin.scraping-configs.index')
                           ->with('success', 'Configuração de scraping excluída com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir configuração: ' . $e->getMessage());
        }
    }

    /**
     * Executa manualmente uma configuração de scraping.
     *
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Http\RedirectResponse
     */
    public function runManually(ScrapingConfig $scrapingConfig)
    {
        try {
            // Executa o scraping
            $result = $this->scrapingService->scrapeAgencyBiddings($scrapingConfig);

            if ($result) {
                return back()->with('success', 'Scraping executado com sucesso. Verifique os logs para detalhes.');
            } else {
                return back()->with('error', 'Erro ao executar scraping. Verifique os logs para detalhes.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao executar scraping: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os logs de uma configuração de scraping.
     *
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\View\View
     */
    public function viewLogs(ScrapingConfig $scrapingConfig)
    {
        $logs = ScrapingLog::where('config_id', $scrapingConfig->id)
                         ->orderBy('start_time', 'desc')
                         ->paginate(20);

        return view('admin.scraping-configs.logs', compact('scrapingConfig', 'logs'));
    }

    /**
     * Testa a configuração de scraping sem salvar dados.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConfig(Request $request)
    {
        try {
            // Valida os dados de entrada
            $validatedData = $request->validate([
                'url' => 'required|url',
                'selectors' => 'required|array',
                'selectors.bidding_list_selector' => 'required|string',
            ]);

            // Cria uma configuração temporária para teste
            $tempConfig = new ScrapingConfig([
                'url' => $request->url,
                'selectors' => json_encode($request->selectors),
            ]);

            // Executa um teste de scraping
            $result = $this->scrapingService->testScraping($tempConfig);

            return response()->json([
                'success' => true,
                'message' => 'Teste de scraping concluído com sucesso.',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao testar configuração: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Clona uma configuração de scraping existente.
     *
     * @param  \App\Models\ScrapingConfig  $scrapingConfig
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clone(ScrapingConfig $scrapingConfig)
    {
        try {
            // Cria uma cópia da configuração
            $newConfig = $scrapingConfig->replicate();
            $newConfig->active = false; // Desativa por padrão
            $newConfig->save();

            return redirect()->route('admin.scraping-configs.edit', $newConfig)
                           ->with('success', 'Configuração clonada com sucesso. Faça as alterações necessárias.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao clonar configuração: ' . $e->getMessage());
        }
    }
}
