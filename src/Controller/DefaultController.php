<?php
namespace App\Controller;

use App\Repository\BillRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
  #[Route('/', name: 'homepage')]
  public function index(BillRepository $billRepository): Response
  {
    return $this->render('default/index.html.twig', [
      'bills' => $billRepository->beforeToday(),
    ]);
  }
}