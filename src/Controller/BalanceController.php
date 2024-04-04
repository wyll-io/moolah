<?php
namespace App\Controller;

use App\Manager\BalanceService;
use App\Manager\BillManager;
use App\Repository\BalanceRepository;
use App\Repository\UserRepository;
use App\Service\DebtCalculatorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BalanceController extends AbstractController
{
  #[Route('/balance', name: 'balance')]
  public function list(UserRepository $userRepository, DebtCalculatorService $debtCalculator): Response
  {
    $users = $userRepository->findAll();
    $debts = $debtCalculator->calculateDebts($users);
    $balance = $debtCalculator->balance();

    return $this->render('balance/balance.html.twig', [
      'balance' => $balance,
      'transactions'=> $debts,
    ]);
  }
}