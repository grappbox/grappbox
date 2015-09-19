<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GanttController extends Controller
{
	public function addTaskAction(Request $request, $id)
	{
		return new Response('add Task '.$id.' Success');
	}

	public function assignTaskAction(Request $request, $id)
	{
		return new Response('assignTask '.$id.' Success');
	}

	public function editTaskAction(Request $request, $id)
	{
		return new Response('edit Task '.$id.' Success');
	}

	public function delTaskAction(Request $request, $id)
	{
		return new Response('del Task '.$id.' Success');
	}

	public function setTaskPropertiesAction(Request $request, $id)
	{
		return new Response('set Task Properties '.$id.' Success');
	}
}