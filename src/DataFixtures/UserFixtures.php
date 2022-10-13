<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends BaseFixture
{
    public function loadData(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@tt.com');
        $user->setRoles(['USER_ADMIN']);
        //password: testPass123
        $user->setPassword('\$2a\$13\$VNNpe55rhGCObCs5JhCXzuImVawSQaY5SrXP96ww/8mq6YuhmrSwS');
        $user->setApiToken('11111111111111111111111111111111111111111111111111111111111111111111111111111111111111');
        $manager->persist($user);

        $user = new User();
        $user->setEmail('user@tt.com');
        $user->setPassword('\$2a\$13\$VNNpe55rhGCObCs5JhCXzuImVawSQaY5SrXP96ww/8mq6YuhmrSwS');
        $user->setApiToken('22222222222222222222222222222222222222222222222222222222222222222222222222222222222222');
        $manager->persist($user);

        $manager->flush();
    }
}
