<?php

namespace App\Services;

use App\Models\Bidding;
use App\Models\Proposal;
use App\Models\ProposalItem;
use App\Models\BiddingItem;
use Illuminate\Support\Collection;

class ProposalCalculationService
{
    /**
     * Cria uma nova proposta em rascunho para uma licitação
     */
    public function createDraftProposal(Bidding $bidding, $userId)
    {
        // Verifica se já existe um rascunho para este usuário e licitação
        $existingDraft = Proposal::where('bidding_id', $bidding->id)
                                ->where('user_id', $userId)
                                ->where('status', 'draft')
                                ->first();

        if ($existingDraft) {
            return $existingDraft;
        }

        // Cria uma nova proposta
        $proposal = Proposal::create([
            'bidding_id' => $bidding->id,
            'user_id' => $userId,
            'status' => 'draft',
            'total_value' => 0,
            'discount_percentage' => 0,
        ]);

        // Cria itens da proposta com base nos itens da licitação
        foreach ($bidding->items as $biddingItem) {
            ProposalItem::create([
                'proposal_id' => $proposal->id,
                'bidding_item_id' => $biddingItem->id,
                'unit_price' => $biddingItem->estimated_unit_price,
                'total_price' => $biddingItem->estimated_unit_price * $biddingItem->quantity,
                'notes' => '',
            ]);
        }

        // Atualiza os totais da proposta
        $proposal->updateTotals();

        return $proposal;
    }

    /**
     * Aplica um desconto em todos os itens da proposta
     */
    public function applyOverallDiscount(Proposal $proposal, float $discountPercentage)
    {
        if (!$proposal->canEdit()) {
            return false;
        }

        if ($discountPercentage < 0 || $discountPercentage > 100) {
            return false;
        }

        foreach ($proposal->items as $item) {
            $originalPrice = $item->bidding_item->estimated_unit_price;
            $discountFactor = (100 - $discountPercentage) / 100;

            $item->unit_price = round($originalPrice * $discountFactor, 2);
            $item->total_price = round($item->unit_price * $item->bidding_item->quantity, 2);
            $item->save();
        }

        $proposal->updateTotals();

        return true;
    }

    /**
     * Aplica um desconto específico a um item da proposta
     */
    public function applyItemDiscount(ProposalItem $item, float $discountPercentage)
    {
        if (!$item->proposal->canEdit()) {
            return false;
        }

        if ($discountPercentage < 0 || $discountPercentage > 100) {
            return false;
        }

        $originalPrice = $item->bidding_item->estimated_unit_price;
        $discountFactor = (100 - $discountPercentage) / 100;

        $item->unit_price = round($originalPrice * $discountFactor, 2);
        $item->total_price = round($item->unit_price * $item->bidding_item->quantity, 2);
        $item->save();

        $item->proposal->updateTotals();

        return true;
    }

    /**
     * Atualiza o preço unitário de um item e recalcula os totais
     */
    public function updateItemPrice(ProposalItem $item, float $newUnitPrice)
    {
        if (!$item->proposal->canEdit()) {
            return false;
        }

        if ($newUnitPrice < 0) {
            return false;
        }

        $item->unit_price = $newUnitPrice;
        $item->total_price = round($newUnitPrice * $item->bidding_item->quantity, 2);
        $item->save();

        $item->proposal->updateTotals();

        return true;
    }

    /**
     * Simula o impacto de diferentes cenários de desconto
     */
    public function simulateDiscountScenarios(Proposal $proposal, array $scenarios = [5, 10, 15, 20])
    {
        $results = [];

        // Guarda os valores originais
        $originalItems = $proposal->items->map(function ($item) {
            return [
                'id' => $item->id,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ];
        })->toArray();

        foreach ($scenarios as $discountPercentage) {
            // Aplica o desconto
            $this->applyOverallDiscount($proposal, $discountPercentage);

            // Salva os resultados
            $results[$discountPercentage] = [
                'total_value' => $proposal->total_value,
                'discount_percentage' => $proposal->discount_percentage,
                'estimated_profit' => $this->calculateEstimatedProfit($proposal),
            ];

            // Restaura os valores originais para próxima simulação
            foreach ($proposal->items as $item) {
                $original = collect($originalItems)->firstWhere('id', $item->id);
                if ($original) {
                    $item->unit_price = $original['unit_price'];
                    $item->total_price = $original['total_price'];
                    $item->save();
                }
            }

            $proposal->updateTotals();
        }

        return $results;
    }

    /**
     * Calcula o lucro estimado para uma proposta
     * Nota: Esta é uma função simplificada, a lógica real dependeria dos custos específicos
     */
    public function calculateEstimatedProfit(Proposal $proposal, float $costPercentage = 70)
    {
        // Assume-se que o custo representa X% do valor estimado na licitação
        $totalCost = 0;

        foreach ($proposal->items as $item) {
            $estimatedPrice = $item->bidding_item->estimated_unit_price;
            $estimatedCost = $estimatedPrice * ($costPercentage / 100);
            $quantity = $item->bidding_item->quantity;

            $itemCost = $estimatedCost * $quantity;
            $totalCost += $itemCost;
        }

        $profit = $proposal->total_value - $totalCost;
        $profitMargin = ($proposal->total_value > 0)
                        ? ($profit / $proposal->total_value) * 100
                        : 0;

        return [
            'total_cost' => round($totalCost, 2),
            'profit' => round($profit, 2),
            'profit_margin' => round($profitMargin, 2),
        ];
    }
}
