<?php

namespace App\DataFixtures;

use App\Entity\Transport;
use App\Entity\TransportRun;
use App\Entity\TransportStop;
use App\DataFixtures\BaseFixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class TransportRunFixtures extends BaseFixture implements FixtureGroupInterface
{
    public function loadData(ObjectManager $manager): void
    {
        $this->createMany(TransportRun::class, 100, function (TransportRun $run, $i) {
            $run->setTransportStop($this->getReference(TransportStop::class . '_' . $i));
            $run->setTransport($this->getReference(Transport::class . '_' . intdiv($i, 10)));
            $run->setArrivalTime(rand(1, 100));
        });

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 4;
    }

    public static function getGroups (): array
    {
        return ['transport'];
    }
}
