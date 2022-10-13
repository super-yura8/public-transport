<?php

namespace App\DataFixtures;

use App\Entity\Transport;
use App\Entity\TransportStop;
use App\DataFixtures\BaseFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class TransportStopFixtures extends BaseFixture implements FixtureGroupInterface
{
    public function loadData(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $this->createMany(TransportStop::class, 100, function (TransportStop $stop, $i) use ($faker) {
            $stop->setAddress($faker->address());
        });

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 3;
    }

    public static function getGroups (): array
    {
        return ['transport'];
    }
}
