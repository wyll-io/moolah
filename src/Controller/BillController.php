<?php
namespace App\Controller;

use App\Repository\BillRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BillController extends AbstractController
{
  #[Route('/bill', name: 'bill_list')]
  public function list(BillRepository $billRepository): Response
  {
    return $this->render('bill/list.html.twig', [
      'nextBills' => $billRepository->nextBills(),
      'previousBills' => $billRepository->previousBills()
    ]);
  }

  #[Route('/bill/{id}', name: 'bill_get')]
  public function show($id, BillRepository $billRepository): Response
  {
    return $this->render('bill/get.html.twig', [
      'bill' => $billRepository->find($id)
    ]);
  }
}