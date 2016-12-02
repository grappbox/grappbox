<?php

namespace Tests\SQLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccountAdministrationControllerTest extends WebTestCase
{

    public function testRegister()
    {
        $client = static::createClient();

        $crawler = $client->request(
        	'POST',
        	'/account/register',
        	array(),
        	array(),
        	array('CONTENT_TYPE' => 'application/json'),
        	'{ "data": { "firstname": "john", "lastname": "doe", "password": "toto", "email": "yolo.swag@toto.com", "is_client": false, "mac": "XXXX", "flag": "web", "device_name": "yolo" } }'
        );

        // Assert a specific 200 status code
		$this->assertEquals(
		    201,
		    $client->getResponse()->getStatusCode()
		);

		$data = json_decode($client->getResponse()->getContent(), true);
		$data = $data['data'];
		$this->assertEquals("john", $data['firstname']);
		$this->assertEquals("doe", $data['lastname']);
		$this->assertEquals("yolo.swag@toto.com", $data['email']);

        $crawler = $client->request(
            'POST',
            '/account/register',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{ "data": { "firstname": "john", "lastname": "doe", "password": "toto", "email": "yolo.swag@toto.com", "is_client": false, "mac": "XXXX", "flag": "web", "device_name": "yolo" } }'
        );

        // Assert a specific 400 status code
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'POST',
            '/account/login',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{ "data": { "password": "toto", "login": "yolo.swag@toto.com", "mac": "XXXX", "flag": "web", "device_name": "yolo" } }'
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals("john", $data['firstname']);
        $this->assertEquals("doe", $data['lastname']);
        $this->assertEquals("yolo.swag@toto.com", $data['email']);
        $_ENV['TOKEN'] = $data['token'];
    }

    public function testLogout()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/account/logout',
            array(),
            array(),
            array('HTTP_Authorization' => $_ENV['TOKEN'])
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        $crawler = $client->request(
            'POST',
            '/account/login',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{ "data": { "password": "toto", "login": "yolo.swag@toto.com", "mac": "XXXX", "flag": "web", "device_name": "yolo" } }'
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals("john", $data['firstname']);
        $this->assertEquals("doe", $data['lastname']);
        $this->assertEquals("yolo.swag@toto.com", $data['email']);
        $_ENV["TOKEN"] = $data['token'];
        $_ENV["USER_ID"] = $data['id'];
    }
}