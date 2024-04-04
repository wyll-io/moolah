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
}	