<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TimelineController extends Controller
{
	public function getTimelineTypeAction(Request $request, $id)
	{
		return new Response('get Timeline Type Success');
	}

	public function postMessageAction(Request $request, $id)
	{
		return new Response('post Message Success');
	}

	public function getMessagesAction(Request $request, $id)
	{
		return new Response('get Messages Success');
	}

	public function delMessageAction(Request $request, $id)
	{
		return new Response('del Message Success');
	}
}