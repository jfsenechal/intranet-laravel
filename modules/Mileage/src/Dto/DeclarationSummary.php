<?php

namespace AcMarche\Mileage\Dto;

final readonly class DeclarationSummary
{
    public function __construct(
        public int $totalKilometers,
        public float $totalMileageAllowance,
        public float $totalOmnium,
        public float $totalRefund,
        public float $mealExpense,
        public float $trainExpense,
        public float $totalExpense,
    ) {}

    public function toArray(): array
    {
        return [
            'total_kilometers' => $this->totalKilometers,
            'total_mileage_allowance' => $this->totalMileageAllowance,
            'total_omnium' => $this->totalOmnium,
            'total_refund' => $this->totalRefund,
            'meal_expense' => $this->mealExpense,
            'train_expense' => $this->trainExpense,
            'total_expense' => $this->totalExpense,
        ];
    }
}
