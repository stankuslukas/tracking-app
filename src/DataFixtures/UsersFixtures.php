<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixtures extends Fixture
{
    private $encode;

    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encode = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user->setEmail('lukas.stankus@hotmail.com');
        $plainPassword = 'miami100';
        $encoded = $this->encode->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
        $manager->persist($user);

        $manager->flush();
    }
}
