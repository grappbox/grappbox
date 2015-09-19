<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
	public function getTeamOccupationAction(Request $request, $id)
	{
		return new Response('get Team '.$id.' Occupation Success');
	}

	public function getNextMeetingsAction(Request $request, $id)
	{
		return new Response('get Next '.$id.' Meetings Success');
	}

	public function getGlobalProgressAction(Request $request, $id)
	{
		return new Response('get Global Progress '.$id.' Success');
	}

	public function getPersonnalProgressAction(Request $request, $id)
	{
		return new Response('get Personnal Progress '.$id.' Success');
	}

	public function getAlertsAction(Request $request, $id)
	{
		return new Response('get '.$id.' Alerts Success');
	}

	public function getAssignedProjectsAction(Request $request, $id)
	{
		return new Response('get Assigned Projects '.$id.' Success');
	}
}