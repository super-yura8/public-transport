<?php

namespace App\DataFixtures;

use App\Entity\TransportType;
use App\DataFixtures\BaseFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TransportTypeFixtures extends BaseFixture implements FixtureGroupInterface
{
    const BUS_TYPE = 'bus-type';
    const ELECTRIC_TYPE = 'electric-type';

    public function loadData(ObjectManager $manager): void
    {
        $type = new TransportType();
        $type->setName('bus');
        $type2 = new TransportType();
        $type2->setName('electric');
        $manager->persist($type);
        $manager->persist($type2);

        $manager->flush();

        $this->addReference(self::BUS_TYPE, $type);
        $this->addReference(self::ELECTRIC_TYPE, $type2);
    }

    public function getOrder(): int
    {
        return 1;
    }

    public static function getGroups (): array
    {
        return ['transport'];
    }
}
