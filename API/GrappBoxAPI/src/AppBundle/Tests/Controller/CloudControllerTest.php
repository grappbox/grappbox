<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CloudControllerTest extends WebTestCase
{
    public function testSendnotsecuredfile()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/v1/sendFile');
    }

    public function testSendsecuredfile()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/v1/sendSecuredFile');
    }

    public function testListfilesanddirectories()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/v1/cloud/ls');
    }

}
