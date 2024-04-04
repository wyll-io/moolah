<?php
namespace App\Controller;

use App\Manager\BalanceService;
use App\Manager\BillManager;
use App\Repository\BalanceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BalanceController extends AbstractController
{
  #[Route('/balance', name: 'balance')]
  public function list(BillManager $billManager): Response
  {
    return $this->render('balance/balance.html.twig', [
      'balance' => $billManager->balance(),
      'debts'=> $billManager->calculateDebts($billManager->balance()),
    ]);
  }
}