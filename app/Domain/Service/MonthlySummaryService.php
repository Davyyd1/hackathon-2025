<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;

class MonthlySummaryService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function computeTotalExpenditure(User $user, int $year, int $month): float
    {
        // TODO: compute expenses total for year-month for a given user
        $criteria = [
            'user_id' => $user->id,
            'year' => $year,
            'month' => $month
        ];

        $sum = $this->expenses->sumAmounts($criteria);
        return $sum;
        // return $test;
    }

    public function computePerCategoryTotals(User $user, int $year, int $month): array
    {
        // TODO: compute totals for year-month for a given user
        
        $criteria = [
        'user_id' => $user->id,
        'year' => $year,
        'month' => $month
        ];

        $totals = $this->expenses->sumAmountsByCategory($criteria);
        $grandTotal = array_sum($totals);

        $result = [];

        foreach ($totals as $category => $amount) {
        $percentage = $grandTotal > 0 ? round(($amount / $grandTotal) * 100, 2) : 0;
        $result[$category] = [
            'value' => $amount,
            'percentage' => $percentage,
        ];
        }

        return $result;
    }

    public function computePerCategoryAverages(User $user, int $year, int $month): array
    {
        // TODO: compute averages for year-month for a given user
        $criteria = [
        'user_id' => $user->id,
        'year' => $year,
        'month' => $month
        ];

        $totals = $this->expenses->averageAmountsByCategory($criteria);
        $grandTotal = array_sum($totals);

        $result = [];

        foreach ($totals as $category => $amount) {
        $percentage = $grandTotal > 0 ? round(($amount / $grandTotal) * 100, 2) : 0;
        $result[$category] = [
            'value' => $amount,
            'percentage' => $percentage,
        ];
        }

        return $result;

        // return [];
    }
}
