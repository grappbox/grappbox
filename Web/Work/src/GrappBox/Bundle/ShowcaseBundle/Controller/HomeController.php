<?php

/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 * COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\ShowcaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;

use GrappBox\Bundle\ShowcaseBundle\Form\Extension;


class HomeController extends Controller
{
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
			$apiCurl = curl_init();
			$apiBaseURL = "http://api.grappbox.com/app_dev.php/V0.9";
			$apiContent = json_encode(array(
				"login" => $loginForm["email"]->getData(),
				"password" => $loginForm["password"]->getData()
				));

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
				case 'Bad Login':
					$redirectResponse = new RedirectResponse("/");
					$redirectResponse->headers->setCookie(new Cookie('LASTLOGINMESSAGE', hash('sha256', '_badlogin'), time() + (3600 * 48), '/', null, false, false));
					break;

				case 'Bad Password':
					$redirectResponse = new RedirectResponse("/");
					$redirectResponse->headers->setCookie(new Cookie('LASTLOGINMESSAGE', hash('sha256', '_badpassword'), time() + (3600 * 48), '/', null, false, false));
					break;

				default:
					$redirectResponse = new RedirectResponse("/app");
					$redirectResponse->headers->setCookie(new Cookie('LASTLOGINMESSAGE', hash('sha256', '_success'), time() + (3600 * 48), '/', null, false, false));
					break;
			}

			return $redirectResponse;
		}

		return $this->render('ShowcaseBundle:Home:index.html.twig', array('loginForm' => $loginForm->createView()));
	}
}
