<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Infrastructure\Persistence\PdoUserRepository;
use App\Domain\Repository\ExpenseRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\ExpenseService;
use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ExpenseController extends BaseController
{
    private const PAGE_SIZE = 10;

    public function __construct(
        Twig $view,
        private readonly ExpenseService $expenseService,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ExpenseRepositoryInterface $expenseRepository
    ) {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the expenses page
    
        // Hints:
        // - use the session to get the current user ID
        // - use the request query parameters to determine the page number and page size
        // - use the expense service to fetch expenses for the current user
        $months = [
            1 => 'Ianuarie',
            2 => 'Februarie',
            3 => 'Martie',
            4 => 'Aprilie',
            5 => 'Mai',
            6 => 'Iunie',
            7 => 'Iulie',
            8 => 'August',
            9 => 'Septembrie',
            10 => 'Octombrie',
            11 => 'Noiembrie',
            12 => 'Decembrie',
        ];

        // parse request parameters
        $userId = (int) $_SESSION['id']; // TODO: obtain logged-in user ID from session
        $user = $this->userRepository->find($userId);
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int)date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int)date('m');

        $years = $this->expenseRepository->listExpenditureYears($user);
        rsort($years); 
        
        $page = (int)($request->getQueryParams()['page'] ?? 1);
        $pageSize = (int)($request->getQueryParams()['pageSize'] ?? self::PAGE_SIZE);

        $expenses = $this->expenseService->list($user, $year, $month, $page, $pageSize);

        return $this->render($response, 'expenses/index.twig', [
            'expenses' => $expenses['items'],
            'page'     => $expenses['page'],
            'totalPages' => $expenses['totalPages'],
            'total' => $expenses['total'],
            'pageSize' => $expenses['pageSize'],
            'years' => $years,
            'months' => $months,
            'year' => $year,
            'month' => $month

        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the create expense page
        $oldInput = $_SESSION['old_input'] ?? [];
        $error = $_SESSION['error'] ?? null;

        unset($_SESSION['old_input'], $_SESSION['error']);


        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        $categoriesJson = $_ENV['EXPENSE_CATEGORIES_JSON'];
        $categories = json_decode($categoriesJson, true);
        return $this->render($response, 'expenses/create.twig', [
            'categories' => $categories,
            'oldInput' => $oldInput,
            'error' => $error
        ]);
    }

    public function store(Request $request, Response $response): Response
    {
        // TODO: implement this action method to create a new expense
        $userId = (int)$_SESSION['id'];
        $data = $request->getParsedBody();
        // var_dump($data);
        $date = new DateTimeImmutable($data['date']);
        // $date->format('Y-m-d');
        $category = $data['category'];
        $amount = $data['amount'];
        $description = $data['description'];
        $user = $this->userRepository->find($userId);

        try{
            $this->expenseService->create($user, (int)$amount, $description, $date, $category);
            return $response->withHeader('Location', '/expenses')->withStatus(302);
        } catch (\RuntimeException $e) {
            $message = $e->getMessage();
            echo $message;
            $_SESSION['old_input'] = $data;
            $_SESSION['error'] = $message;

            return $response->withHeader('Location', '/expenses/create')->withStatus(302);
        }

        // Hints:
        // - use the session to get the current user ID
        // - use the expense service to create and persist the expense entity
        // - rerender the "expenses.create" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success

        // return $response;
    }

    public function edit(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to display the edit expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not

        $expense = ['id' => 1];

        return $this->render($response, 'expenses/edit.twig', ['expense' => $expense, 'categories' => []]);
    }

    public function update(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to update an existing expense

        // Hints:
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - get the new values from the request and prepare for update
        // - update the expense entity with the new values
        // - rerender the "expenses.edit" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success

        return $response;
    }

    public function destroy(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to delete an existing expense

        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - call the repository method to delete the expense
        // - redirect to the "expenses.index" page

        return $response;
    }
}
