<?php

namespace App\DataFixtures;

use App\DataFixtures\BaseFixture;
use App\Entity\Transport;
use App\Entity\TransportRun;
use App\Entity\TransportStart;
use App\Entity\TransportStop;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TransportStartFixtures extends BaseFixture implements FixtureGroupInterface
{
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(TransportStart::class, 10, function (TransportStart $start, $i) {
            $start->setTransport($this->getReference(Transport::class . '_' . $i));
            $arr = range(1, 1440);
            shuffle($arr);
            $arr = array_slice($arr, 0, 10);
            $start->setTimes($arr);
        });

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 5;
    }

    public static function getGroups (): array
    {
        return ['transport'];
    }
}
