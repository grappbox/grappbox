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

    public function testUpdate()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'PUT',
            '/project/'.$_ENV["PROJECT_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
            '{ "data": { "name": "Grappbox Test Update", "description": "This is a test updated", "phone": "+33 6 74 36 65 95", "company": "Grappbox&Co", "email": "contact@grappbox.com", "facebook": "grappbox", "twitter": "@grappbox" } }'
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals($_ENV["PROJECT_ID"], $data['id']);
        $this->assertEquals("Grappbox Test Update", $data['name']);
        $this->assertEquals("This is a test updated", $data['description']);
        $this->assertEquals("+33 6 74 36 65 95", $data['phone']);
        $this->assertEquals("Grappbox&Co", $data['company']);
        $this->assertEquals("contact@grappbox.com", $data['contact_mail']);
        $this->assertEquals("grappbox", $data['facebook']);
        $this->assertEquals("@grappbox", $data['twitter']);
    }

    public function testGet()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/project/'.$_ENV["PROJECT_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN'])
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals($_ENV["PROJECT_ID"], $data['id']);
        $this->assertEquals("Grappbox Test Update", $data['name']);
        $this->assertEquals("This is a test updated", $data['description']);
        $this->assertEquals("+33 6 74 36 65 95", $data['phone']);
        $this->assertEquals("Grappbox&Co", $data['company']);
        $this->assertEquals("contact@grappbox.com", $data['contact_mail']);
        $this->assertEquals("grappbox", $data['facebook']);
        $this->assertEquals("@grappbox", $data['twitter']);
    }

    public function testDelete()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'DELETE',
            '/project/'.$_ENV["PROJECT_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN'])
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testCreateCustomerAccess()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'POST',
            '/project/customeraccess',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
            '{ "data": { "name": "Grappbox Test", "projectId": '.$_ENV["PROJECT_ID"].' } }'
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals($_ENV["PROJECT_ID"], $data['project_id']);
        $this->assertEquals("Grappbox Test", $data['name']);
        $_ENV["CUSTOMER_ID"] = $data['id'];
    }

    public function testDeleteCustomerAccess()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'DELETE',
            '/project/customeraccess/'.$_ENV["PROJECT_ID"].'/'.$_ENV["CUSTOMER_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN'])
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
    }

    public function testRetrieve()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/project/retrieve/'.$_ENV["PROJECT_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN'])
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );
    }
}