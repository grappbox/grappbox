<?php

namespace Tests\SQLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TimelineControllerTest extends WebTestCase
{
    public function testGetTimelines()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/timelines/'.$_ENV["PROJECT_ID"],
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
        $_ENV["TEAM_ID"] = $data['array'][0]["id"];
        $_ENV["CUSTOMER_ID"] = $data['array'][1]["id"];
    }

    public function testMessageTeam()
    {
        $client = static::createClient();

        $crawler = $client->request(
        	'POST',
        	'/timeline/message/'.$_ENV["TEAM_ID"],
        	array(),
        	array(),
        	array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
        	'{ "data": { "title": "This is a message on team timeline", "message": "That is a really or not so long message" } }'
        );

        // Assert a specific 200 status code
		$this->assertEquals(
		    201,
		    $client->getResponse()->getStatusCode()
		);

		$data = json_decode($client->getResponse()->getContent(), true);
		$data = $data['data'];
		$this->assertEquals("This is a message on team timeline", $data['title']);
		$this->assertEquals("That is a really or not so long message", $data['message']);
        $this->assertEquals($_ENV["USER_ID"], $data['creator']['id']);
        $this->assertEquals("john", $data['creator']['firstname']);
        $this->assertEquals("doe", $data['creator']['lastname']);
        $this->assertEquals($_ENV["TEAM_ID"], $data['timelineId']);
        $_ENV["MESSAGE_TEAM_ID"] = $data['id'];
    }

    public function testMessageCustomer()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'POST',
            '/timeline/message/'.$_ENV["CUSTOMER_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
            '{ "data": { "title": "This is a message on customer timeline", "message": "That is a really or not so long message" } }'
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            201,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals("This is a message on customer timeline", $data['title']);
        $this->assertEquals("That is a really or not so long message", $data['message']);
        $this->assertEquals($_ENV["USER_ID"], $data['creator']['id']);
        $this->assertEquals("john", $data['creator']['firstname']);
        $this->assertEquals("doe", $data['creator']['lastname']);
        $this->assertEquals($_ENV["CUSTOMER_ID"], $data['timelineId']);
        $_ENV["MESSAGE_CUSTOMER_ID"] = $data['id'];
    }

    public function testCommentTeam()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'POST',
            '/timeline/comment/'.$_ENV["TEAM_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
            '{ "data": { "comment": "This is a comment on a certain team message", "commentedId": '.$_ENV["MESSAGE_TEAM_ID"].' } }'
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            201,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals("This is a comment on a certain team message", $data['comment']);
        $this->assertEquals($_ENV["USER_ID"], $data['creator']['id']);
        $this->assertEquals("john", $data['creator']['firstname']);
        $this->assertEquals("doe", $data['creator']['lastname']);
        $this->assertEquals($_ENV["MESSAGE_TEAM_ID"], $data['parentId']);
    }

    public function testCommentCustomer()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'POST',
            '/timeline/comment/'.$_ENV["CUSTOMER_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
            '{ "data": { "comment": "This is a comment on a certain customer message", "commentedId": '.$_ENV["MESSAGE_CUSTOMER_ID"].' } }'
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            201,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals("This is a comment on a certain customer message", $data['comment']);
        $this->assertEquals($_ENV["USER_ID"], $data['creator']['id']);
        $this->assertEquals("john", $data['creator']['firstname']);
        $this->assertEquals("doe", $data['creator']['lastname']);
        $this->assertEquals($_ENV["MESSAGE_CUSTOMER_ID"], $data['parentId']);
    }

    public function testGetMessageComment()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/timeline/message/comments/'.$_ENV["TEAM_ID"].'/'.$_ENV["MESSAGE_TEAM_ID"],
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
        $this->assertEquals(1, count($data['array']));
    }

    public function testDeleteTeam()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'DELETE',
            '/timeline/message/'.$_ENV["TEAM_ID"].'/'.$_ENV["MESSAGE_TEAM_ID"],
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

    public function testDeleteCustomer()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'DELETE',
            '/timeline/message/'.$_ENV["CUSTOMER_ID"].'/'.$_ENV["MESSAGE_CUSTOMER_ID"],
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