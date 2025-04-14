<?php

namespace App\Services;

use App\Models\Bidding;
use App\Models\BiddingItem;
use App\Models\ScrapingConfig;
use App\Models\ScrapingLog;
use App\Jobs\ProcessScrapedData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ScrapingService
{
    /**
     * Executa o scraping para uma configuração específica
     */
    public function scrapeAgencyBiddings(ScrapingConfig $config)
    {
        // Inicializa o log de scraping
        $log = new ScrapingLog();
        $log->config_id = $config->id;
        $log->start_time = now();
        $log->status = 'failed'; // Assume falha inicialmente
        $log->save();

        try {
            // Faz a requisição HTTP
            $response = Http::timeout(60)->get($config->url);

            if (!$response->successful()) {
                $log->error_message = "HTTP request failed with status: " . $response->status();
                $log->end_time = now();
                $log->save();
                return false;
            }

            // Parseia o HTML
            $crawler = new Crawler($response->body());

            // Obtém os seletores do JSON de configuração
            $selectors = json_decode($config->selectors, true);

            // Localiza os elementos de licitação na página
            $biddingElements = $crawler->filter($selectors['bidding_list_selector'] ?? 'table tr');

            $log->items_found = $biddingElements->count();
            $processedCount = 0;

            // Extrai os dados de cada licitação
            $biddingElements->each(function ($element) use ($config, $selectors, &$processedCount) {
                try {
                    // Extrair dados básicos da licitação
                    $externalId = $this->extractData($element, $selectors['external_id_selector']);

                    // Verifica se a licitação já existe no sistema
                    $existingBidding = Bidding::where('external_id', $externalId)
                                              ->where('agency_id', $config->agency_id)
                                              ->first();

                    if (!$existingBidding) {
                        // Extrai os dados básicos
                        $biddingData = [
                            'external_id' => $externalId,
                            'agency_id' => $config->agency_id,
                            'title' => $this->extractData($element, $selectors['title_selector']),
                            'bidding_type' => $this->extractData($element, $selectors['type_selector']),
                            'modality' => $this->extractData($element, $selectors['modality_selector']),
                            'status' => 'published',
                            'publication_date' => $this->parseDate($this->extractData($element, $selectors['publication_date_selector'])),
                            'opening_date' => $this->parseDateTime($this->extractData($element, $selectors['opening_date_selector'])),
                            'closing_date' => $this->parseDateTime($this->extractData($element, $selectors['closing_date_selector'])),
                            'document_url' => $this->extractData($element, $selectors['document_url_selector']),
                        ];

                        // Enfileira um job para processar os detalhes da licitação
                        ProcessScrapedData::dispatch($biddingData, $config->id);
                        $processedCount++;
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing bidding element: " . $e->getMessage());
                }
            });

            // Atualiza o log
            $log->items_processed = $processedCount;
            $log->status = $processedCount > 0 ? ($processedCount == $log->items_found ? 'success' : 'partial') : 'failed';
            $log->end_time = now();
            $log->save();

            // Atualiza a data da última execução
            $config->last_run = now();
            $config->save();

            return $log->status !== 'failed';

        } catch (\Exception $e) {
            Log::error("Scraping error for config {$config->id}: " . $e->getMessage());

            $log->error_message = $e->getMessage();
            $log->end_time = now();
            $log->save();

            return false;
        }
    }

    /**
     * Extrai informações detalhadas de uma licitação acessando sua página específica
     */
    public function scrapeDetailedBidding(array $biddingData, ScrapingConfig $config)
    {
        try {
            // Se tiver URL do documento, acesse para obter detalhes
            if (!empty($biddingData['document_url'])) {
                $response = Http::timeout(60)->get($biddingData['document_url']);

                if ($response->successful()) {
                    $detailCrawler = new Crawler($response->body());
                    $selectors = json_decode($config->selectors, true);

                    // Extrair descrição detalhada
                    if (isset($selectors['description_selector'])) {
                        $biddingData['description'] = $this->extractData($detailCrawler, $selectors['description_selector']);
                    }

                    // Extrair valor estimado
                    if (isset($selectors['estimated_value_selector'])) {
                        $valueText = $this->extractData($detailCrawler, $selectors['estimated_value_selector']);
                        $biddingData['estimated_value'] = $this->parseMonetaryValue($valueText);
                    }

                    // Extrair contatos
                    if (isset($selectors['contact_email_selector'])) {
                        $biddingData['contact_email'] = $this->extractData($detailCrawler, $selectors['contact_email_selector']);
                    }

                    if (isset($selectors['contact_phone_selector'])) {
                        $biddingData['contact_phone'] = $this->extractData($detailCrawler, $selectors['contact_phone_selector']);
                    }

                    // Extrair itens da licitação
                    $items = [];
                    if (isset($selectors['items_list_selector'])) {
                        $itemElements = $detailCrawler->filter($selectors['items_list_selector']);

                        $itemElements->each(function ($itemElement) use ($selectors, &$items) {
                            $item = [
                                'item_number' => $this->extractData($itemElement, $selectors['item_number_selector'] ?? null),
                                'description' => $this->extractData($itemElement, $selectors['item_description_selector'] ?? null),
                                'quantity' => $this->parseNumericValue($this->extractData($itemElement, $selectors['item_quantity_selector'] ?? null)),
                                'unit' => $this->extractData($itemElement, $selectors['item_unit_selector'] ?? null),
                                'estimated_unit_price' => $this->parseMonetaryValue($this->extractData($itemElement, $selectors['item_price_selector'] ?? null)),
                            ];

                            $items[] = $item;
                        });
                    }

                    // Criar a licitação no banco de dados
                    $bidding = Bidding::create($biddingData);

                    // Adicionar itens
                    foreach ($items as $itemData) {
                        $bidding->items()->create($itemData);
                    }

                    return $bidding;
                }
            }

            // Caso não consiga acessar os detalhes, cria a licitação com dados básicos
            return Bidding::create($biddingData);

        } catch (\Exception $e) {
            Log::error("Error scraping detailed bidding: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Métodos auxiliares para extração e formatação de dados
     */
    private function extractData(Crawler $element, $selector = null)
    {
        if (!$selector) {
            return trim($element->text());
        }

        try {
            $selected = $element->filter($selector);

            // Verifica se é um seletor de atributo (ex: a@href)
            if (strpos($selector, '@') !== false) {
                $parts = explode('@', $selector);
                $attributeName = $parts[1] ?? 'href';
                return $selected->attr($attributeName);
            }

            return trim($selected->text());
        } catch (\Exception $e) {
            return '';
        }
    }

    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDateTime($dateTimeString)
    {
        if (empty($dateTimeString)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($dateTimeString)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseMonetaryValue($valueString)
    {
        if (empty($valueString)) {
            return null;
        }

        // Remove caracteres não numéricos, exceto ponto e vírgula
        $cleaned = preg_replace('/[^\d,.]/', '', $valueString);

        // Converte vírgula para ponto (formato brasileiro para internacional)
        $cleaned = str_replace(',', '.', $cleaned);

        // Retorna como float
        return (float) $cleaned;
    }

    private function parseNumericValue($valueString)
    {
        if (empty($valueString)) {
            return null;
        }

        // Remove caracteres não numéricos, exceto ponto e vírgula
        $cleaned = preg_replace('/[^\d,.]/', '', $valueString);

        // Converte vírgula para ponto
        $cleaned = str_replace(',', '.', $cleaned);

        return (float) $cleaned;
    }
}
