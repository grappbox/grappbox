<?php

namespace Tests\SQLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WhiteboardControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();

        $crawler = $client->request(
        	'POST',
        	'/whiteboard',
        	array(),
        	array(),
        	array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
        	'{ "data": { "whiteboardName": "whiteboard test", "projectId": '.$_ENV["PROJECT_ID"].' } }'
        );

        // Assert a specific 200 status code
		$this->assertEquals(
		    201,
		    $client->getResponse()->getStatusCode()
		);

		$data = json_decode($client->getResponse()->getContent(), true);
		$data = $data['data'];
		$this->assertEquals($_ENV["PROJECT_ID"], $data['projectId']);
		$this->assertEquals("whiteboard test", $data['name']);
        $this->assertEquals($_ENV["USER_ID"], $data['user']['id']);
        $this->assertEquals("john", $data['user']['firstname']);
        $this->assertEquals("doe", $data['user']['lastname']);
        $this->assertEquals($_ENV["USER_ID"], $data['updator']['id']);
        $this->assertEquals("john", $data['updator']['firstname']);
        $this->assertEquals("doe", $data['updator']['lastname']);
        $_ENV["WHITEBOARD_ID"] = $data['id'];
    }

    public function testOpen()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'GET',
            '/whiteboard/'.$_ENV["WHITEBOARD_ID"],
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
        $this->assertEquals($_ENV["WHITEBOARD_ID"], $data['id']);
        $this->assertEquals($_ENV["PROJECT_ID"], $data['projectId']);
        $this->assertEquals("whiteboard test", $data['name']);
        $this->assertEquals($_ENV["USER_ID"], $data['user']['id']);
        $this->assertEquals("john", $data['user']['firstname']);
        $this->assertEquals("doe", $data['user']['lastname']);
        $this->assertEquals($_ENV["USER_ID"], $data['updator']['id']);
        $this->assertEquals("john", $data['updator']['firstname']);
        $this->assertEquals("doe", $data['updator']['lastname']);
        $this->assertEquals(0, count($data['content']));
        $this->assertEquals(0, count($data['users']));
    }

    public function testPushObject()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'PUT',
            '/whiteboard/draw/'.$_ENV["WHITEBOARD_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
            '{ "data": { "object": { "type": "RECTANGLE", "color": "#ABCDEF", "background": "#ABCDEF", "lineWeight": 3, "positionStart": { "x": 10.5, "y": 25.7 }, "positionEnd": { "x": 15.2, "y": 16.7 } } } }'
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals($_ENV["WHITEBOARD_ID"], $data['whiteboardId']);
        $this->assertEquals("RECTANGLE", $data['object']['type']);
        $this->assertEquals("#ABCDEF", $data['object']['color']);
        $this->assertEquals("#ABCDEF", $data['object']['background']);
        $this->assertEquals(3, $data['object']['lineWeight']);
        $this->assertEquals(10.5, $data['object']['positionStart']['x']);
        $this->assertEquals(25.7, $data['object']['positionStart']['y']);
        $this->assertEquals(15.2, $data['object']['positionEnd']['x']);
        $this->assertEquals(16.7, $data['object']['positionEnd']['y']);

        $crawler = $client->request(
            'PUT',
            '/whiteboard/draw/'.$_ENV["WHITEBOARD_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
            '{ "data": { "object": { "type": "LINE", "color": "#ABCDEF", "background": "#ABCDEF", "lineWeight": 3, "positionStart": { "x": 10.5, "y": 25.7 }, "positionEnd": { "x": 15.2, "y": 16.7 } } } }'
        );
    }

    public function testDeleteObject()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'DELETE',
            '/whiteboard/object/'.$_ENV["WHITEBOARD_ID"],
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json', 'HTTP_Authorization' => $_ENV['TOKEN']),
            '{ "data": { "center": { "x": 15.2, "y": 16.78 }, "radius": 15.6 } }'
        );

        // Assert a specific 200 status code
        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode()
        );

        $data = json_decode($client->getResponse()->getContent(), true);
        $data = $data['data'];
        $this->assertEquals($_ENV["WHITEBOARD_ID"], $data['whiteboardId']);
        $this->assertEquals("#ABCDEF", $data['object']['color']);
        $this->assertEquals("#ABCDEF", $data['object']['background']);
        $this->assertEquals(3, $data['object']['lineWeight']);
        $this->assertEquals(10.5, $data['object']['positionStart']['x']);
        $this->assertEquals(25.7, $data['object']['positionStart']['y']);
        $this->assertEquals(15.2, $data['object']['positionEnd']['x']);
        $this->assertEquals(16.7, $data['object']['positionEnd']['y']);
    }

    public function testClose()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'PUT',
            '/whiteboard/'.$_ENV["WHITEBOARD_ID"],
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

    public function testDelete()
    {
        $client = static::createClient();

        $crawler = $client->request(
            'DELETE',
            '/whiteboard/'.$_ENV["WHITEBOARD_ID"],
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