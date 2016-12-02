<?php

namespace Tests\SQLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectControllerTest extends WebTestCase
{

    public function testCreate()
    {
        $client = static::createClient();

        $crawler = $client->request(
        	'POST',
        	'/project',
        	array(),
        	array(),
        	array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
        	'{ "data": { "name": "Grappbox Test", "description": "This is a test", "phone": "+33 6 74 36 65 95", "company": "Grappbox&Co", "email": "contact@grappbox.com", "facebook": "grappbox", "twitter": "@grappbox", "password": "yolo" } }'
        );

        // Assert a specific 200 status code
		$this->assertEquals(
		    201,
		    $client->getResponse()->getStatusCode()
		);

		$data = json_decode($client->getResponse()->getContent(), true);
		$data = $data['data'];
		$this->assertEquals("Grappbox Test", $data['name']);
		$this->assertEquals("This is a test", $data['description']);
        $this->assertEquals($_ENV["USER_ID"], $data['creator']['id']);
        $this->assertEquals("john", $data['creator']['firstname']);
        $this->assertEquals("doe", $data['creator']['lastname']);
        $this->assertEquals("+33 6 74 36 65 95", $data['phone']);
        $this->assertEquals("Grappbox&Co", $data['company']);
        $this->assertEquals("contact@grappbox.com", $data['contact_mail']);
        $this->assertEquals("grappbox", $data['facebook']);
        $this->assertEquals("@grappbox", $data['twitter']);
        $_ENV["PROJECT_ID"] = $data['id'];
    }
}