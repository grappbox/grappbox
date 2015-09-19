<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BugtrackerController extends Controller
{
	public function postTicketAction(Request $request)
	{
		return new Response('post Ticket Success');
	}

	public function modifyTicketAction(Request $request, $id)
	{
		return new Response('modify Ticket '.$id.' Success');
	}

	public function getTicketListAction(Request $request, $id)
	{
		return new Response('get '.$id.' Ticket List Success');
	}

	public function commentTicketAction(Request $request, $id)
	{
		return new Response('comment '.$id.' Ticket Success');
	}

	public function closeTicketAction(Request $request, $id)
	{
		return new Response('close '.$id.' Ticket Success');
	}

	public function getTicketDetailsAction(Request $request, $id)
	{
		return new Response('get '.$id.' Ticket Deatils Success');
	}
}