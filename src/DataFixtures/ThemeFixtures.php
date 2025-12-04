<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ThemeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $theme1 = new Theme();
        $theme1->setName('Français');
        $manager->persist($theme1);

        $theme2 = new Theme();
        $theme2->setName('Mathématiques');
        $manager->persist($theme2);

        $manager->flush();
    }
}
