<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PlanningController extends Controller
{
	public function getDayPlanningAction(Request $request)
	{
		return new Response('get Day Planning Success');
	}

	public function getWeekPlanningAction(Request $request)
	{
		return new Response('get Week Planning Success');
	}

	public function getMonthPlanningAction(Request $request)
	{
		return new Response('get Month Plannning Success');
	}

	public function modifyEventAction(Request $request)
	{
		return new Response('modify Event Success');
	}

	public function getEventDetailsAction(Request $request, $id)
	{
		return new Response('get '.$id.' Event Details Success');
	}
}