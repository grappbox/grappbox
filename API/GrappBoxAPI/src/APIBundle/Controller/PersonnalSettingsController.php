<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PersonnalSettingsController extends Controller
{
	public function editPhotoAction(Request $request, $id)
	{
		return new Response('edit Photo '.$id.' Success');
	}

	public function editPersonalInfosAction(Request $request, $id)
	{
		return new Response('edit Personal Infos '.$id.' Success');
	}

	public function changePasswordAction(Request $request, $id)
	{
		return new Response('change Password '.$id.' Success');
	}

	public function editPreferencesAction(Request $request, $id)
	{
		return new Response('edit Preferences '.$id.' Success');
	}

	public function getRoleAction(Request $request, $id)
	{
		return new Response('get Role '.$id.' Success');
	}
}