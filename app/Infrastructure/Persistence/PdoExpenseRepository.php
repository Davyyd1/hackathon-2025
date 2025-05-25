<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Expense;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;

class PdoExpenseRepository implements ExpenseRepositoryInterface
{
    public function __construct(
        private readonly PDO $pdo,
    ) {}

    /**
     * @throws Exception
     */
    public function find(int $id): ?Expense
    {
        $query = 'SELECT * FROM expenses WHERE id = :id';
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $data = $statement->fetch();
        if (false === $data) {
            return null;
        }

        return $this->createExpenseFromData($data);
    }

    public function save(Expense $expense): void
    {
        // TODO: Implement save() method.
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM expenses WHERE id=?');
        $statement->execute([$id]);
    }

    public function findBy(array $criteria, int $from, int $limit): array
    {
        // TODO: Implement findBy() method.
        $sql = "SELECT * FROM expenses
        WHERE user_id = :user_id
        AND strftime('%Y', date) = :year
        AND strftime('%m', date) = :month
        ORDER BY date DESC
        LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $criteria['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':year', (string)$criteria['year'], PDO::PARAM_STR);
        $stmt->bindValue(':month', str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT), PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $from, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(fn($row) => $this->createExpenseFromData($row), $rows);
    }


    public function countBy(array $criteria): int
    {
        // TODO: Implement countBy() method.
        $sql = "SELECT COUNT(*) FROM expenses
                WHERE user_id = :user_id
                AND strftime('%Y', date) = :year
                AND strftime('%m', date) = :month";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $criteria['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':year', (string)$criteria['year'], PDO::PARAM_STR);
        $stmt->bindValue(':month', str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT), PDO::PARAM_STR);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function listExpenditureYears(User $user): array
    {
        // TODO: Implement listExpenditureYears() method.
        $sql = "
        SELECT DISTINCT strftime('%Y', date) AS year
        FROM expenses
        WHERE user_id = :user_id
        ORDER BY year DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_map('intval', $rows); 
    }

    public function sumAmountsByCategory(array $criteria): array
    {
        // TODO: Implement sumAmountsByCategory() method.
        return [];
    }

    public function averageAmountsByCategory(array $criteria): array
    {
        // TODO: Implement averageAmountsByCategory() method.
        return [];
    }

    public function sumAmounts(array $criteria): float
    {
        // TODO: Implement sumAmounts() method.
        return 0;
    }

    /**
     * @throws Exception
     */
    private function createExpenseFromData(mixed $data): Expense
    {
        return new Expense(
            $data['id'],
            $data['user_id'],
            new DateTimeImmutable($data['date']),
            $data['category'],
            $data['amount_cents'],
            $data['description'],
        );
    }
}
