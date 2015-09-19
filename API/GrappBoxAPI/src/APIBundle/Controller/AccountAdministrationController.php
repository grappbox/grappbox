<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AccountAdministrationController extends Controller
{
	public function loginAction(Request $request)
	{
		return new Response('login Success');
	}

	public function disconnectAction(Request $request)
	{
		return new Response('disconnect Success');
	}

	public function signInAction(Request $request)
	{
		return new Response('sign In Success');
	}
}