<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProjectSettingsController extends Controller
{
	public function addTeamMemberAction(Request $request, $id)
	{
		return new Response('add Team '.$id.' Member Success');
	}

	public function generateClientAccessAction(Request $request, $id)
	{
		return new Response('genreate Client Access '.$id.' Success');
	}

	public function delTeamMemberAction(Request $request, $id)
	{
		return new Response('del Team Member '.$id.' Success');
	}

	public function assignRoleAction(Request $request, $id)
	{
		return new Response('assign Role '.$id.' Success');	
	}

	public function editRoleAction(Request $request, $id)
	{
		return new Response('edit Role '.$id.' Success');
	}

	public function getRolesAction(Request $request, $id)
	{
		return new Response('getRoles '.$id.' Success');
	}

	public function checkPermissionsAction(Request $request, $id)
	{
		return new Response('check Permissions '.$id.' Success');
	}

	public function createProjectAction(Request $request)
	{
		return new Response('create Project Success');
	}

	public function archiveProjectAction(Request $request, $id)
	{
		return new Response('archive Project '.$id.' Success');
	}

	public function setProjectFinishedAction(Request $request, $id)
	{
		return new Response('set Project Finished '.$id.' Success');
	}
}