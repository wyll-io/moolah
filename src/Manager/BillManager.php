<?php

namespace App\Manager;

use App\Repository\BillRepository;
use App\Repository\UserRepository;

class BillManager
{
	public function __construct(public BillRepository $billRepository, public UserRepository $userRepository){}

	public function balance(): array
	{
		/* // List the bills
		$bills = $this->billRepository->beforeToday();
		if (!$bills) {
			return null;
		}

		// List the users
		$users = $this->userRepository->findAll();
		if (!$users) {
			return null;
		}

		$userBalances = array();
		foreach($users as $key => $user) {
			$userBalances[$user->getId()] = 0;
		}

		// Balance
	  foreach($bills as $key => $bill) {
			$amountPerUser = $bill->getAmount() / (count($bill->getParticipants()) +1);

			// The payer
			$userBalances[$bill->getPayer()->getId()] += ($bill->getAmount() - $amountPerUser);

			foreach($bill->getParticipants() as $key => $participant) {
				$userBalances[$participant->getId()] -= $amountPerUser;
			}
		}

		dump($userBalances);

	  return $userBalances; */

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
			$balances = [];

			// List the bills
			$expenses = $this->billRepository->beforeToday();
			if (!$expenses) {
				return null;
			}

      // Calcul des soldes des utilisateurs
			foreach ($expenses as $expense) {
				$payer = $expense->getPayer();
				$amount = $expense->getAmount();
				$participants = $expense->getParticipants();

				// Ajout du montant payé par le payeur
				$balances[$payer->getId()] = isset($balances[$payer->getId()]) ?
						$balances[$payer->getId()] - $amount :
						-$amount;

				// Division du montant par le nombre de participants et ajout aux soldes des participants
				$numParticipants = count($participants);
				foreach ($participants as $participant) {
						$balances[$participant->getId()] = isset($balances[$participant->getId()]) ?
								$balances[$participant->getId()] + ($amount / $numParticipants) :
								($amount / $numParticipants);
				}
		}

		// Suppression des soldes nuls
		$balances = array_filter($balances, function ($balance) {
				return $balance != 0;
		});

		// Calcul des dettes
		$debts = [];

		// Séparer les utilisateurs avec des soldes positifs et négatifs
		$positiveBalances = [];
		$negativeBalances = [];
		foreach ($balances as $userId => $balance) {
				if ($balance > 0) {
						$positiveBalances[$userId] = $balance;
				} elseif ($balance < 0) {
						$negativeBalances[$userId] = -$balance;
				}
		}

		// Récupération des entités User correspondant aux IDs
		$userRepository = $this->userRepository;
		$positiveUsers = $userRepository->findBy(['id' => array_keys($positiveBalances)]);
		$negativeUsers = $userRepository->findBy(['id' => array_keys($negativeBalances)]);

		// Tant qu'il y a des utilisateurs avec des soldes positifs et négatifs
		while (!empty($positiveBalances) && !empty($negativeBalances)) {
				$payerId = key($positiveBalances);
				$receiverId = key($negativeBalances);
				$amount = min($positiveBalances[$payerId], $negativeBalances[$receiverId]);
				$payer = $userRepository->find($payerId);
				$receiver = $userRepository->find($receiverId);
				$debts[] = [
						'payer' => $payer,
						'receiver' => $receiver,
						'amount' => $amount
				];
				$positiveBalances[$payerId] -= $amount;
				$negativeBalances[$receiverId] -= $amount;
				if ($positiveBalances[$payerId] == 0) {
						unset($positiveBalances[$payerId]);
				}
				if ($negativeBalances[$receiverId] == 0) {
						unset($negativeBalances[$receiverId]);
				}
		}

			dump($debts);
			return $debts;
    	
    }
}	