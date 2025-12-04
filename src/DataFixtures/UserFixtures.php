<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\SchoolClass;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'une classe pour les utilisateurs avec ROLE_USER
        $schoolClass = new SchoolClass();
        $schoolClass->setYear('2025-2026');
        $schoolClass->setLevel('CP');
        $manager->persist($schoolClass);

        // Premier utilisateur
        $user1 = new User();
        $user1->setEmail('thomaroger@gmail.com');
        $user1->setFirstName('Thomas');
        $user1->setLastName('Roger');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, 'troger'));
        $user1->setRoles(['ROLE_ADMIN']);
        $manager->persist($user1);

        // Deuxième utilisateur (avec ROLE_USER, donc associé à une classe)
        $user2 = new User();
        $user2->setEmail('laporte.aurelie91@gmail.com');
        $user2->setFirstName('Aurélie');
        $user2->setLastName('Laporte');
        $user2->setPassword($this->passwordHasher->hashPassword($user2, 'alaporte'));
        $user2->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $user2->setSchoolClass($schoolClass);
        $manager->persist($user2);

        $manager->flush();
    }
}
