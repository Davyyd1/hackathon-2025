<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Psr\Http\Message\UploadedFileInterface;

class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function list(User $user, int $year, int $month, int $pageNumber, int $pageSize): array
    {
        // TODO: implement this and call from controller to obtain paginated list of expenses
        $from = ($pageNumber - 1) * $pageSize;

        $criteria = [
            'user_id' => $user->id,
            'year' => $year,
            'month' => $month,
        ];
        
        $items = $this->expenses->findBy($criteria, $from, $pageSize);
        $total = $this->expenses->countBy($criteria);

        return [
        'items' => $items,
        'total' => $total,
        'page' => $pageNumber,
        'pageSize' => $pageSize,
        'totalPages' => (int)ceil($total / $pageSize),
        ];
    }

    public function create(
        User $user,
        float $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        // TODO: implement this to create a new expense entity, perform validation, and persist
        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $dateFormatted = $date->format('Y-m-d');
        if($dateFormatted > $today ) {
            throw new \RuntimeException("Data can't be greater than today.");
        }
        if(!$category) {
            throw new \RuntimeException("Category must be set.");
        }
        if($amount <= 0) {
            throw new \RuntimeException("Amount must be set.");
        }
        if(!$description) {
            throw new \RuntimeException("Description must be set.");
        }

        // TODO: here is a code sample to start with
        $expense = new Expense(null, $user->id, $date, $category, (int)$amount, $description);
        $this->expenses->save($expense);
    }

    public function update(
        Expense $expense,
        int $amount,
        string $description,
        DateTimeImmutable $date,
        string $category,
    ): void {
        // TODO: implement this to update expense entity, perform validation, and persist
        $today = (new \DateTimeImmutable())->format('Y-m-d');
        $dateFormatted = $date->format('Y-m-d');
        if($dateFormatted > $today ) {
            throw new \RuntimeException("Data can't be greater than today.");
        }
        if(!$category) {
            throw new \RuntimeException("Category must be set.");
        }
        if($amount <= 0) {
            throw new \RuntimeException("Amount must be set.");
        }
        if(!$description) {
            throw new \RuntimeException("Description must be set.");
        }

        $expense->amountCents = $amount;
        $expense->description = $description;
        $expense->date = $date;
        $expense->category= $category;
        $this->expenses->update($expense);
    }

    public function importFromCsv(User $user, UploadedFileInterface $csvFile): int
    {
        // TODO: process rows in file stream, create and persist entities
        // TODO: for extra points wrap the whole import in a transaction and rollback only in case writing to DB fails

        return 0; // number of imported rows
    }
}
