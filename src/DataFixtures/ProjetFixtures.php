<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Projet;

class ProjetFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for($i = 1; $i <= 20; $i++)
        {
          $projets = new Projet();
          $projets->setNom("Nom du projet n° $i")
                  ->setDescription("<p> Contenu du projets n°$i</p>")
                  ->setImage("http://placehold.it/350x150")
                  ->setDate(new \DateTime());

          $manager->persist($projets);
        }
        $manager->flush();
    }
}
