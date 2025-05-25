<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Repository\ExpenseRepositoryInterface;
use App\Domain\Service\AuthService;
use App\Domain\Service\MonthlySummaryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController extends BaseController
{
    public function __construct(
        Twig $view,
        private readonly MonthlySummaryService $monthlyss,
        private readonly AuthService $authService,
        private readonly ExpenseRepositoryInterface $expenseRepository

        // TODO: add necessary services here and have them injected by the DI container
    )
    {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: parse the request parameters
        // TODO: load the currently logged-in user
        // TODO: get the list of available years for the year-month selector
        // TODO: call service to compute total expenditure per selected year/month

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
        $userId = $_SESSION['id'];
        $user = $this->authService->getUser($userId);
        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int)date('Y');
        $month = isset($_GET['month']) ? (int) $_GET['month'] : (int)date('m');
        $years = $this->expenseRepository->listExpenditureYears($user);
        rsort($years);
        $total = $this->monthlyss->computeTotalExpenditure($user, $year,$month);
        
        // TODO: call service to generate the overspending alerts for current month
        

        // TODO: call service to compute category totals per selected year/month
        $totalPerCategory = $this->monthlyss->computePerCategoryTotals($user, $year, $month);

        $avgPerCategory = $this->monthlyss->computePerCategoryAverages($user, $year, $month);
        // TODO: call service to compute category averages per selected year/month

        return $this->render($response, 'dashboard.twig', [
            // 
            'total' => $total,
            'years' => $years,
            'year' => $year,
            'month' => $month,
            'months' => $months,
            'totalsForCategories' => $totalPerCategory,
            'avgPerCategory' => $avgPerCategory,
            // 
            'alerts'                => [],
            'totalForMonth'         => [],
            // 'totalsForCategories'   => [],
            'averagesForCategories' => [],
        ]);
    }
}
