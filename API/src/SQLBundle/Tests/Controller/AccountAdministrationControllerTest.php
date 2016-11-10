<?php

namespace SQLBundle\Tests\Controller;

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
        	'{ "data": { "firstname": "john", "lastname": "doe", "password": "toto", "email": "yolo@toto.com", "is_client": false, "mac": "XXXX", "flag": "web", "device_name": "yolo" } }'
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
		$this->assertEquals("yolo@toto.com", $data['email']);

        $crawler = $client->request(
            'POST',
            '/account/register',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{ "data": { "firstname": "john", "lastname": "doe", "password": "toto", "email": "yolo@toto.com", "is_client": false, "mac": "XXXX", "flag": "web", "device_name": "yolo" } }'
        );

        // Assert a specific 400 status code
        $this->assertEquals(
            400,
            $client->getResponse()->getStatusCode()
        );
    }
}