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

    public function balance(): array
	{
		// List the bills
		$bills = $this->billRepository->beforeToday();
		if (!$bills) {
			return null;
		}

		$balances = [];

        foreach ($bills as $bill) {
            $payer = $bill->getPayer();
            $amount = round($bill->getAmount(), 2);
            $participants = $bill->getParticipants();
            $splitAmount = $amount / count($participants);

            if (!isset($balances[$payer->getId()])) {
                $balances[$payer->getId()] = 0;
            }

            $balances[$payer->getId()] -= $amount;

            foreach ($participants as $participant) {
                if (!isset($balances[$participant->getId()])) {
                    $balances[$participant->getId()] = 0;
                }

                $balances[$participant->getId()] += $splitAmount;
            }
        }

        $balancesWithPersons = [];
        foreach ($balances as $personId => $balance) {
            $person = $this->userRepository->find($personId);
            $balancesWithPersons[] = [
                'person' => $person,
                'balance' => $balance
            ];
        }

		return $balancesWithPersons;
	}

    public function calculateDebts(): array
    {
        $users = $this->userRepository->findAll();
        $bills = $this->billRepository->findAllWithParticipants();

        $balances = array_fill_keys(array_map(fn($u) => $u->getId(), $users), 0.0);

        foreach ($bills as $bill) {
            $payerId = $bill->getPayer()->getId();
            $amount = $bill->getAmount();
            $splitAmount = $amount / ($bill->getParticipants()->count() + 1);  // Including payer in the split

            $balances[$payerId] += $amount - $splitAmount;

            foreach ($bill->getParticipants() as $participant) {
                $participantId = $participant->getId();
                $balances[$participantId] -= $splitAmount;
            }
        }

        return $this->minimizeTransactions($balances, $users);
    }

    private function minimizeTransactions(array $balances, array $users): array
    {
        $transactions = [];
        $debtors = new \SplPriorityQueue();
        $creditors = new \SplPriorityQueue();

        foreach ($balances as $userId => $balance) {
            if ($balance < 0) {
                $debtors->insert($userId, $balance);
            } elseif ($balance > 0) {
                $creditors->insert($userId, $balance);
            }
        }

        while (!$debtors->isEmpty() && !$creditors->isEmpty()) {
            $debtorId = $debtors->extract();
            $creditorId = $creditors->extract();

            $debtAmount = $balances[$debtorId];
            $creditAmount = $balances[$creditorId];

            $amount = min(-$debtAmount, $creditAmount);
            $transactions[] = [
                'from' => $this->findUserById($users, $debtorId),
                'to' => $this->findUserById($users, $creditorId),
                'amount' => $amount
            ];

            $balances[$debtorId] += $amount;
            $balances[$creditorId] -= $amount;

            if ($balances[$debtorId] < 0) {
                $debtors->insert($debtorId, $balances[$debtorId]);
            }

            if ($balances[$creditorId] > 0) {
                $creditors->insert($creditorId, $balances[$creditorId]);
            }
        }

        return $transactions;
    }

    private function findUserById(array $users, int $userId)
    {
        foreach ($users as $user) {
            if ($user->getId() == $userId) {
                return $user;
            }
        }

        return null;
    }
}