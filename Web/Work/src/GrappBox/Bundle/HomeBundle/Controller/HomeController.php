<?php

/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;

use GrappBox\Bundle\HomeBundle\Form\Extension;


class HomeController extends Controller
{
	private function loginRedirectAction($apiContent)
	{
		$apiBaseURL = "http://api.grappbox.com/app_dev.php/V0.2";
		$apiCurl = curl_init();

		curl_setopt($apiCurl, CURLOPT_URL, $apiBaseURL."/accountadministration/login");
		curl_setopt($apiCurl, CURLOPT_POST, 1);
		curl_setopt($apiCurl, CURLOPT_TIMEOUT, 30);
		curl_setopt($apiCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($apiCurl, CURLOPT_POSTFIELDS, $apiContent);

		$apiJSONResult = curl_exec($apiCurl);

		if (curl_error($apiCurl))
			die("div class='alert alert-danger alert-dismissible fade in' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button><h4>SURPRISE, MOTHERFUCKER !</h4 <p> Unable to connect: ".curl_errno($apiCurl)." - ".curl_error($apiCurl)."</p></div>");

		curl_close($apiCurl);

		$apiResult = json_decode($apiJSONResult, true);
		switch ($apiResult) {

			// API V0.2 UPDATE
/*			case 'Bad Login':
			$redirectResponse = new RedirectResponse("/");
			$redirectResponse->headers->setCookie(new Cookie('LASTLOGINMESSAGE', hash('sha256', '_badlogin'), 0, '/', null, false, false));
			break;

			case 'Bad Password':
			$redirectResponse = new RedirectResponse("/");
			$redirectResponse->headers->setCookie(new Cookie('LASTLOGINMESSAGE', hash('sha256', '_badpassword'), 0, '/', null, false, false));
			break;
*/
			default:
			$redirectResponse = new RedirectResponse("/app");
			$redirectResponse->headers->setCookie(new Cookie('LASTLOGINMESSAGE', hash('sha256', '_success'), 0, '/', null, false, false));
			$redirectResponse->headers->setCookie(new Cookie('USERTOKEN', $apiResult['data']['token'], 0, '/', null, false, false));
			break;
		}

		return $redirectResponse;
	}


	public function indexAction(Request $request)
	{
		$loginFormOptions = array();

		$loginForm = $this->createFormBuilder($loginFormOptions)
		->add('email', 'email', array('attr' => array('placeholder' => 'grappbox@awesome.com'), 'label' => false))
		->add('password', 'password', array('attr' => array('placeholder' => 'Password'), 'label' => false))
		->add('submit', 'submit', array('label' => 'Login'))
		->getForm();

		$loginForm->handleRequest($request);

		if ($loginForm->isValid())
		{
			$apiContent = json_encode(
				array(
					"data" =>
				array(
					"login" => $loginForm["email"]->getData(),
					"password" => $loginForm["password"]->getData()
					)
				));

			return $this->loginRedirectAction($apiContent);
		}

		return $this->render('HomeBundle:Home:index.html.twig', array('loginForm' => $loginForm->createView()));
	}
}
