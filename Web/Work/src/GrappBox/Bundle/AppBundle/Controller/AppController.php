<?php

/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AppController extends Controller
{
	public function setAngularRouteManagementAction(Request $request)
	{
		$pathData = $request->getpathInfo();

		if (($token = strpos($pathData, '/app/')) !== false)
			return $this->redirect(substr_replace($pathData, '/app/#/', $token, strlen('/app/')), 301);
	}

	public function indexAction()
	{
		$requestData = $this->get('request');
		$cookieData = $requestData->cookies;

		if ($cookieData->has('USERTOKEN') && $cookieData->get('USERTOKEN'))
			return $this->render('AppBundle:App:index.html.twig');

		$redirectResponse = new RedirectResponse('/#login');
		$redirectResponse->headers->setCookie(new Cookie('LASTLOGINMESSAGE', hash('sha512', '_DENIED'), 0, '/', null, false, false));
		$redirectResponse->headers->setCookie(new Cookie('USERTOKEN', null, 0, '/', null, false, false));

		return $redirectResponse;
	}
}
