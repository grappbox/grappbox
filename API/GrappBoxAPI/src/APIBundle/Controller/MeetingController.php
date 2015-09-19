<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MeetingController extends Controller
{
	public function editMeetingAction(Request $request, $id)
	{
		return new Response('edit Meeting '.$id.' Success');
	}

	public function delMeetingAction(Request $request, $id)
	{
		return new Response('del Meeting '.$id.' Success');
	}

	public function invitePersonToMeetingAction(Request $request, $id)
	{
		return new Response('invite Person To Meeting '.$id.' Success');
	}

	public function addAlertAction(Request $request, $id)
	{
		return new Response('add Alert '.$id.' Success');
	}
}