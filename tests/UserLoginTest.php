<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserLoginTest extends WebTestCase
{
    public function testSomething()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('lukas.stankus@hotmail.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        // test e.g. the profile page
        $client->request('GET', '/tasks');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Available options');
    }
}
