<?php

namespace App\DataFixtures;

use App\Entity\Transport;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\BaseFixture;

class TransportFixtures extends BaseFixture implements FixtureGroupInterface
{
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(Transport::class, 10, function (Transport $transport, $i) {
            $transport->setActive(true);
            $transport->setNumber($i);
            $transport->setType($this->getReference($i % 2 ? TransportTypeFixtures::BUS_TYPE : TransportTypeFixtures::ELECTRIC_TYPE));
        });

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 2;
    }

    public static function getGroups (): array
    {
        return ['transport'];
    }
}
