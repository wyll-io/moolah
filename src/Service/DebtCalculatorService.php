<?php

// src/Service/DebtCalculatorService.php

namespace App\Service;

use App\Repository\BillRepository;
use App\Repository\UserRepository;

class DebtCalculatorService
{
    private $userRepository;
    private $billRepository;

    public function __construct(UserRepository $userRepository, BillRepository $billRepository)
    {
        $this->userRepository = $userRepository;
        $this->billRepository = $billRepository;
    }

    public function calculateDebts(): array
    {
        $debts = [];
        $users = $this->userRepository->findAll();
        $bills = $this->billRepository->findAllWithParticipants();

        foreach ($users as $user) {
            $debts[$user->getId()] = [
                'user' => $user,
                'debts' => array_fill_keys(array_map(fn($u) => $u->getId(), $users), 0)
            ];
        }

        foreach ($bills as $bill) {
            $ownerId = $bill->getPayer()->getId();
            $amountPerUser = $bill->getAmount() / ($bill->getParticipants()->count() + 1);

            foreach ($bill->getParticipants() as $participant) {
                $participantId = $participant->getId();
                if ($ownerId !== $participantId) {
                    $debts[$ownerId]['debts'][$participantId] -= $amountPerUser;
                    $debts[$participantId]['debts'][$ownerId] += $amountPerUser;
                }
            }
        }

        return $debts;
    }
}