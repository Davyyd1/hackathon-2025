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

        $query = 'INSERT INTO 
                expenses (user_id, date, category, amount_cents, description) 
                VALUES   (:user_id, :date, :category, :amount_cents, :description)';
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':user_id', $expense->userId, PDO::PARAM_INT);
        $statement->bindValue(':date', $expense->date->format('Y-m-d'));
        $statement->bindValue(':category', $expense->category, PDO::PARAM_STR);
        $statement->bindValue(':amount_cents', $expense->amountCents);
        $statement->bindValue(':description', $expense->description, PDO::PARAM_STR);
        $statement->execute();

    }

    public function update(Expense $expense): void {
        $query = 'UPDATE expenses SET
            date = :date,
            category = :category,
            amount_cents = :amount_cents,
            description = :description
            WHERE id = :id';

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':date', $expense->date->format('Y-m-d'));
        $stmt->bindValue(':category', $expense->category, PDO::PARAM_STR);
        $stmt->bindValue(':amount_cents', $expense->amountCents, PDO::PARAM_INT);
        $stmt->bindValue(':description', $expense->description, PDO::PARAM_STR);
        $stmt->bindValue(':id', $expense->id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM expenses WHERE id=?');
        $statement->execute([$id]);
    }

    public function findBy(array $criteria, int $from, int $limit): array
    {
        // TODO: Implement findBy() method.
        $query = "SELECT * FROM expenses
        WHERE user_id = :user_id
        AND strftime('%Y', date) = :year
        AND strftime('%m', date) = :month
        ORDER BY date DESC
        LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($query);
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
        $query = "SELECT COUNT(*) FROM expenses
                WHERE user_id = :user_id
                AND strftime('%Y', date) = :year
                AND strftime('%m', date) = :month";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $criteria['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':year', (string)$criteria['year'], PDO::PARAM_STR);
        $stmt->bindValue(':month', str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT), PDO::PARAM_STR);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function listExpenditureYears(User $user): array
    {
        // TODO: Implement listExpenditureYears() method.
        $query = "
        SELECT DISTINCT strftime('%Y', date) AS year
        FROM expenses
        WHERE user_id = :user_id
        ORDER BY year DESC
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_map('intval', $rows); 
    }

    public function sumAmountsByCategory(array $criteria): array
    {
        // TODO: Implement sumAmountsByCategory() method.
        $query = "SELECT category, sum(amount_cents) as total FROM expenses
                  WHERE user_id = :user_id
                  AND strftime('%Y', date) = :year
                  AND strftime('%m', date) = :month
                  GROUP BY category
                  ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $criteria['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':year', (string)$criteria['year'], PDO::PARAM_STR);
        $stmt->bindValue(':month', str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT), PDO::PARAM_STR);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_column($rows, 'total', 'category');
    }

    public function averageAmountsByCategory(array $criteria): array
    {
        // TODO: Implement averageAmountsByCategory() method.
        $query = "SELECT category, avg(amount_cents) as average FROM expenses
                  WHERE user_id = :user_id
                  AND strftime('%Y', date) = :year
                  AND strftime('%m', date) = :month
                  GROUP BY category
                  ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $criteria['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':year', (string)$criteria['year'], PDO::PARAM_STR);
        $stmt->bindValue(':month', str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT), PDO::PARAM_STR);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_column($rows, 'average', 'category');
        // return [];
    }

    public function sumAmounts(array $criteria): float
    {
        // TODO: Implement sumAmounts() method.
        $query = "SELECT sum(amount_cents) FROM expenses
                  WHERE user_id = :user_id
                  AND strftime('%Y', date) = :year
                  AND strftime('%m', date) = :month";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':user_id', $criteria['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':year', (string)$criteria['year'], PDO::PARAM_STR);
        $stmt->bindValue(':month', str_pad((string)$criteria['month'], 2, '0', STR_PAD_LEFT), PDO::PARAM_STR);
        $stmt->execute();

        $sum=$stmt->fetchColumn();
        if($sum) {
            return $sum;
        } return 0;
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
