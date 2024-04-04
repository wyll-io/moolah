<?php
namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
  name: 'app:create-user',
  description: 'Creates a new user.',
  hidden: false,
  aliases: ['app:add-user']
)]
class CreateUserCommand extends Command
{
  private $entityManager;
  private $hasher;

  public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher)
  {
    $this->entityManager = $entityManager;
    $this->hasher = $hasher;

    parent::__construct();
  }

  protected function configure()
  {
    $this->setHelp('This command allows you to create a user for the backend');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $io->title('Create a user');

    $firstname = $io->ask('Firstname', "John");
    $lastname = $io->ask('Lastname', "Doe");
    $email = $io->ask('Email', "john.doa@yopmail.com");
    $password = $io->askHidden('Password ?');

    $user = new User();
    $user->setFirstname($firstname);
    $user->setLastname($lastname);
    $user->setEmail($email);
    $user->setRoles(["ROLE_ADMIN"]);
    $user->setPassword($this->hasher->hashPassword($user, $password));

    $this->entityManager->persist($user);
    $this->entityManager->flush();

    return Command::SUCCESS;
  }
}