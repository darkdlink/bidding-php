<?php

namespace App\Http\Controllers;

use App\Models\BiddingAgency;
use App\Models\Bidding;
use App\Models\ScrapingConfig;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BiddingAgencyController extends Controller
{
    /**
     * Construtor
     */
    public function __construct()
    {
        $this->middleware(['auth', 'can:manage-agencies']);
    }

    /**
     * Exibe a lista de órgãos licitantes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = BiddingAgency::withCount('biddings');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Ordenação
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação
        $agencies = $query->paginate(15);

        return view('admin.agencies.index', compact('agencies'));
    }

    /**
     * Exibe o formulário para criar um novo órgão licitante.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.agencies.create');
    }

    /**
     * Armazena um novo órgão licitante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bidding_agencies',
            'code' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'contact_info' => 'nullable|string',
        ]);

        try {
            $agency = BiddingAgency::create($validated);

            return redirect()->route('admin.agencies.index')
                           ->with('success', 'Órgão licitante criado com sucesso.');
        } catch (\Exception $e) {
            return back()->withInput()
                       ->with('error', 'Erro ao criar órgão licitante: ' . $e->getMessage());
        }
    }

    /**
     * Exibe os detalhes de um órgão licitante.
     *
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\View\View
     */
    public function show(BiddingAgency $agency)
    {
        // Contagem de licitações
        $totalBiddings = $agency->biddings()->count();
        $activeBiddings = $agency->biddings()->active()->count();

        // Licitações por status
        $biddingsByStatus = $agency->biddings()
                                 ->select('status', DB::raw('count(*) as total'))
                                 ->groupBy('status')
                                 ->pluck('total', 'status')
                                 ->toArray();

        // Licitações por tipo
        $biddingsByType = $agency->biddings()
                               ->select('bidding_type', DB::raw('count(*) as total'))
                               ->groupBy('bidding_type')
                               ->pluck('total', 'bidding_type')
                               ->toArray();

        // Licitações recentes
        $recentBiddings = $agency->biddings()
                               ->orderBy('created_at', 'desc')
                               ->limit(5)
                               ->get();

        // Configurações de scraping
        $scrapingConfigs = $agency->scrapingConfigs()->get();

        // Estatísticas de propostas
        $proposalsCount = DB::table('proposals')
                          ->join('biddings', 'proposals.bidding_id', '=', 'biddings.id')
                          ->where('biddings.agency_id', $agency->id)
                          ->count();

        $wonProposalsCount = DB::table('proposals')
                             ->join('biddings', 'proposals.bidding_id', '=', 'biddings.id')
                             ->where('biddings.agency_id', $agency->id)
                             ->where('proposals.status', 'won')
                             ->count();

        return view('admin.agencies.show', compact(
            'agency',
            'totalBiddings',
            'activeBiddings',
            'biddingsByStatus',
            'biddingsByType',
            'recentBiddings',
            'scrapingConfigs',
            'proposalsCount',
            'wonProposalsCount'
        ));
    }

    /**
     * Exibe o formulário para editar um órgão licitante.
     *
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\View\View
     */
    public function edit(BiddingAgency $agency)
    {
        return view('admin.agencies.edit', compact('agency'));
    }

    /**
     * Atualiza um órgão licitante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, BiddingAgency $agency)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bidding_agencies,name,' . $agency->id,
            'code' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'contact_info' => 'nullable|string',
        ]);

        try {
            $agency->update($validated);

            return redirect()->route('admin.agencies.index')
                           ->with('success', 'Órgão licitante atualizado com sucesso.');
        } catch (\Exception $e) {
            return back()->withInput()
                       ->with('error', 'Erro ao atualizar órgão licitante: ' . $e->getMessage());
        }
    }

    /**
     * Remove um órgão licitante.
     *
     * @param  \App\Models\BiddingAgency  $agency
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(BiddingAgency $agency)
    {
        try {
            // Verifica se há licitações associadas
            $biddingsCount = $agency->biddings()->count();

            if ($biddingsCount > 0) {
                return back()->with('error', 'Este órgão possui licitações associadas e não pode ser excluído.');
            }

            // Verifica se há configurações de scraping associadas
            $scrapingConfigsCount = $agency->scrapingConfigs()->count();

            if ($scrapingConfigsCount > 0) {
                return back()->with('error', 'Este órgão possui configurações de scraping associadas e não pode ser excluído.');
            }

            // Remove o órgão
            $agency->delete();

            return redirect()->route('admin.agencies.index')
                           ->with('success', 'Órgão licitante excluído com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao excluir órgão licitante: ' . $e->getMessage());
        }
    }

    /**
     * Importa múltiplos órgãos licitantes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('file');

            // Determina o tipo de arquivo e processa de acordo
            $extension = $file->getClientOriginalExtension();

            if ($extension == 'csv' || $extension == 'txt') {
                $this->importFromCsv($file);
            } else {
                $this->importFromExcel($file);
            }

            return redirect()->route('admin.agencies.index')
                           ->with('success', 'Órgãos licitantes importados com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao importar órgãos licitantes: ' . $e->getMessage());
        }
    }

    /**
     * Importa órgãos licitantes de um arquivo CSV.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return void
     */
    private function importFromCsv($file)
    {
        $path = $file->getRealPath();
        $records = array_map('str_getcsv', file($path));

        // Assume que a primeira linha é o cabeçalho
        $headers = array_shift($records);

        $nameIndex = array_search('name', array_map('strtolower', $headers));
        $codeIndex = array_search('code', array_map('strtolower', $headers));
        $websiteIndex = array_search('website', array_map('strtolower', $headers));
        $contactInfoIndex = array_search('contact_info', array_map('strtolower', $headers));

        if ($nameIndex === false) {
            throw new \Exception('A coluna "name" é obrigatória no arquivo CSV.');
        }

        DB::beginTransaction();

        try {
            foreach ($records as $record) {
                $name = $record[$nameIndex];

                // Verifica se o nome não está vazio
                if (empty(trim($name))) {
                    continue;
                }

                // Verifica se já existe um órgão com este nome
                $exists = BiddingAgency::where('name', $name)->exists();

                if (!$exists) {
                    $agency = new BiddingAgency();
                    $agency->name = $name;

                    if ($codeIndex !== false && isset($record[$codeIndex])) {
                        $agency->code = $record[$codeIndex];
                    }

                    if ($websiteIndex !== false && isset($record[$websiteIndex])) {
                        $agency->website = $record[$websiteIndex];
                    }

                    if ($contactInfoIndex !== false && isset($record[$contactInfoIndex])) {
                        $agency->contact_info = $record[$contactInfoIndex];
                    }

                    $agency->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Importa órgãos licitantes de um arquivo Excel.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return void
     */
    private function importFromExcel($file)
    {
        // Carrega o arquivo Excel
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $worksheet = $spreadsheet->getActiveSheet();

        // Obtém os dados como um array
        $data = $worksheet->toArray();

        // Assume que a primeira linha é o cabeçalho
        $headers = array_shift($data);

        $nameIndex = array_search('name', array_map('strtolower', $headers));
        $codeIndex = array_search('code', array_map('strtolower', $headers));
        $websiteIndex = array_search('website', array_map('strtolower', $headers));
        $contactInfoIndex = array_search('contact_info', array_map('strtolower', $headers));

        if ($nameIndex === false) {
            throw new \Exception('A coluna "name" é obrigatória no arquivo Excel.');
        }

        DB::beginTransaction();

        try {
            foreach ($data as $row) {
                $name = $row[$nameIndex];

                // Verifica se o nome não está vazio
                if (empty(trim($name))) {
                    continue;
                }

                // Verifica se já existe um órgão com este nome
                $exists = BiddingAgency::where('name', $name)->exists();

                if (!$exists) {
                    $agency = new BiddingAgency();
                    $agency->name = $name;

                    if ($codeIndex !== false && isset($row[$codeIndex])) {
                        $agency->code = $row[$codeIndex];
                    }

                    if ($websiteIndex !== false && isset($row[$websiteIndex])) {
                        $agency->website = $row[$websiteIndex];
                    }

                    if ($contactInfoIndex !== false && isset($row[$contactInfoIndex])) {
                        $agency->contact_info = $row[$contactInfoIndex];
                    }

                    $agency->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Exporta a lista de órgãos licitantes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');

        // Obtém todos os órgãos
        $agencies = BiddingAgency::all();

        // Prepara os dados para exportação
        $export = new \App\Exports\BiddingAgencyExport($agencies);

        // Retorna o arquivo no formato solicitado
        if ($format == 'csv') {
            return \Maatwebsite\Excel\Facades\Excel::download($export, 'orgaos_licitantes.csv', \Maatwebsite\Excel\Excel::CSV);
        } else {
            return \Maatwebsite\Excel\Facades\Excel::download($export, 'orgaos_licitantes.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        }
    }
}
