<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Bill;
use App\Entity\User;

class BillFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public const USER1_REFERENCE = 'user1';
    public const USER2_REFERENCE = 'user2';
    public const USER3_REFERENCE = 'user3';
    public const USER4_REFERENCE = 'user4';
    public const USER5_REFERENCE = 'user5';

    private $firstNames = [
        'Jean', 'Pierre', 'Michel', 'Philippe', 'Alain', 'Patrick', 'Jacques', 'François', 'Paul', 'Daniel',
        'Claude', 'Bernard', 'Robert', 'Richard', 'Henri', 'Georges', 'Roger', 'André', 'Marcel', 'René',
        'Louis', 'Guy', 'Christian', 'Jean-Pierre', 'Jacqueline', 'Marie', 'Michèle', 'Sylvie', 'Nathalie',
        'Monique', 'Martine', 'Isabelle', 'Nicole', 'Catherine', 'Christine', 'Annie', 'Danielle', 'Brigitte',
        'Chantal', 'Sophie', 'Élisabeth', 'Valérie', 'Évelyne', 'Sandrine', 'Caroline', 'Anne', 'Julie', 'Véronique',
    ];

    private $lastNames = [
        'Durand', 'Dupont', 'Lefebvre', 'Moreau', 'Girard', 'Fournier', 'Dubois', 'Leroy', 'Lefèvre', 'André',
        'Robert', 'Richard', 'Petit', 'Blanc', 'Rousseau', 'Gauthier', 'Clement', 'Faure', 'Mercier', 'Martinez',
        'Legrand', 'Garcia', 'Lopez', 'Bonnet', 'Thomas', 'Laurent', 'Rey', 'Colin', 'Robin', 'Bourgeois', 'Masson',
        'Guerin', 'Nicollet', 'Huet', 'Pierre', 'Meyer', 'Jean', 'Marchand', 'Duval', 'Gillet', 'Roy', 'Noel',
        'Bourgeois', 'Mathieu', 'Michel', 'Bertrand', 'Roussel', 'Leclerc', 'Guillaume',
    ];

    private $billNames = [
        'Déjeuner au restaurant',
        'Dîner en famille',
        'Courses au supermarché',
        'Café et croissants',
        'Pique-nique au parc',
        'Soirée cinéma',
        'Apéro entre amis',
        'Repas du dimanche',
        'Pizza à emporter',
        'Sushi delivery',
        'Petit-déjeuner continental',
        'Côte de boeuf',
        'Burger gourmet',
        'Salade César',
        'Pâtes fraîches maison',
        'Tacos mexicains',
        'Cocktail exotique',
        'Plateau de fromages',
        'Fondue savoyarde',
        'Gâteau d\'anniversaire',
        'Brunch du samedi',
        'Grillades estivales',
        'Tartare de saumon',
        'Buffet asiatique',
        'Barbecue en plein air',
        'Raclette montagnarde',
        'Crêpes bretonnes',
        'Petit café',
        'Thé à la menthe',
        'Bagel au saumon',
        'Assiette de fruits de mer',
        'Plateau de charcuterie',
        'Délice sucré',
        'Smoothie vitaminé',
        'Pain perdu',
        'Croque-monsieur',
        'Tiramisu maison',
        'Gaufres croustillantes',
        'Mojito rafraîchissant',
        'Salade de fruits frais',
        'Tarte aux pommes',
        'Bière artisanale',
        'Cocktail sans alcool',
        'Hot-dog new-yorkais',
        'Fondant au chocolat',
        'Pâtisserie française',
        'Boisson énergisante',
        'Gâteau basque',
    ];

    public function load(ObjectManager $manager)
    {
        // Générer des utilisateurs
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $user->setFirstname($this->firstNames[array_rand($this->firstNames)]);
            $user->setLastname($this->lastNames[array_rand($this->lastNames)]);
            $user->setEmail("user".$i."@email.com");
            $user->setPassword("password");
            $manager->persist($user);
            $users[] = $user;
        }
        $manager->flush();

        // Ajouter des références pour les utilisateurs
        $this->addReference(self::ADMIN_USER_REFERENCE, $users[0]);
        $this->addReference(self::USER1_REFERENCE, $users[1]);
        $this->addReference(self::USER2_REFERENCE, $users[2]);
        $this->addReference(self::USER3_REFERENCE, $users[3]);
        $this->addReference(self::USER4_REFERENCE, $users[4]);

        // Générer des factures
        for ($i = 0; $i < 100; $i++) { // Générer 100 factures
            $date = new \DateTime();
            $date->modify('-' . mt_rand(0, 13) . ' days'); // Choisissez une date aléatoire entre il y a 0 et 13 jours (moins de deux semaines)
            $bill = new Bill();
            $bill->setName($this->billNames[array_rand($this->billNames)]);
            $bill->setAmount(mt_rand(5, 150)); // Montant aléatoire entre 5 et 150
            if (mt_rand(1, 8) === 1) { // Probabilité de 1 sur 8 pour une facture de remboursement
                $bill->setBillType(Bill::BILL_TYPE_REFUND);
                $bill->setName('Remboursement '.$i);
            }
            $bill->setDate($date);

            // Choisir un payeur aléatoire parmi les utilisateurs
            $payer = $users[array_rand($users)];
            $bill->setPayer($payer);

            // Filtrer les utilisateurs non utilisés pour sélectionner les participants
            $participants = array_filter($users, function ($user) use ($payer) {
                return $user !== $payer;
            });

            shuffle($participants);
            $numParticipants = mt_rand(1, count($participants));
            for ($j = 0; $j < $numParticipants; $j++) {
                $bill->addParticipant($participants[$j]);
            }

            $manager->persist($bill);
        }
        $manager->flush();
    }
}