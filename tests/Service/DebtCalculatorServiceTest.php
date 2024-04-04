<?php

// tests/Service/DebtCalculatorServiceTest.php

namespace App\Tests\Service;

use App\Entity\Bill;
use App\Entity\User;
use App\Repository\BillRepository;
use App\Repository\UserRepository;
use App\Service\DebtCalculatorService;
use PHPUnit\Framework\TestCase;

class DebtCalculatorServiceTest extends TestCase
{
    private function createUser(int $id, string $name): User
    {
        $user = new User();
        $user->setFirstname($name);
        return $user;
    }

    private function createBill(User $payer, float $amount, array $participants): Bill
    {
        $bill = new Bill();
        $bill->setPayer($payer);
        $bill->setAmount($amount);
        
        foreach ($participants as $participant) {
          $bill->addParticipant($participant);
      }

        return $bill;
    }

    public function testCalculateDebts()
    {
        $user1 = $this->createUser(1, "User 1");
        $user2 = $this->createUser(2, "User 2");
        $user3 = $this->createUser(3, "User 3");

        $bill1 = $this->createBill($user1, 100.0, [$user2, $user3]);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findAll')->willReturn([$user1, $user2, $user3]);

        $billRepository = $this->createMock(BillRepository::class);
        $billRepository->method('findAllWithParticipants')->willReturn([$bill1]);

        $debtCalculatorService = new DebtCalculatorService($userRepository, $billRepository);
        $transactions = $debtCalculatorService->calculateDebts();

        $this->assertCount(1, $transactions); // Assuming we expect one transaction for simplification
        $this->assertEquals(33.33, round($transactions[0]['amount'], 2));
        $this->assertSame($user2, $transactions[0]['from']);
        $this->assertSame($user1, $transactions[0]['to']);
    }
}
