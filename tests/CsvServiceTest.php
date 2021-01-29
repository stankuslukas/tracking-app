<?php

namespace App\Tests;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsvServiceTest extends WebTestCase
{
    public function testSomething()
    {
        $client = static::createClient();
        $userRepository = static::$container->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('lukas.stankus@hotmail.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/tasks');

        $form = $crawler->selectButton('Download CSV report')->form();

        $form['report_form[date_from]'] = "2021-01-01";
        $form['report_form[date_to]'] = "2021-01-21";

        $crawler = $client->submit($form);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('application/force-download', $client->getResponse()->headers->get('Content-Type'));

    }
}
