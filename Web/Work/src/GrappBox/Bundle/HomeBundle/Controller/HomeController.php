<?php

/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use GrappBox\Bundle\HomeBundle\Form\Extension;


class HomeController extends Controller
{
	private function index_setLoginAPI($formData)
	{
		$APIBaseURL = 'http://api.grappbox.com/app_dev.php/';
		$APIBaseVersion = 'V0.2';

		$curlData = curl_init();

		curl_setopt($curlData, CURLOPT_URL, $APIBaseURL.$APIBaseVersion.'/accountadministration/login');
		curl_setopt($curlData, CURLOPT_POST, 1);
		curl_setopt($curlData, CURLOPT_TIMEOUT, 30);
		curl_setopt($curlData, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curlData, CURLOPT_POSTFIELDS, $formData);

		$curlJSONResponse = curl_exec($curlData);

		if (curl_error($curlData))
			throw new HttpException(500, 'Unable to fetch data from GRAPPBOX API.');

		curl_close($curlData);
		$curlResponse = json_decode($curlJSONResponse, true);

		if ($curlResponse['info']['return_code']) {
			$redirectResponse = new RedirectResponse($curlResponse['info']['return_code'] == '1.14.1' ? '/app' : '/');
			$redirectResponse->headers->setCookie(new Cookie('LASTLOGINMESSAGE',
				hash('sha512', ($curlResponse['info']['return_code'] == '1.14.1' ? '_SUCCESS' : (strpos($curlResponse['info']['return_message'], 'password') ? '_BADPASSWORD' : '_BADLOGIN'))), 0, '/', null, false, false));
			$redirectResponse->headers->setCookie(new Cookie('USERTOKEN', ($curlResponse['info']['return_code'] == '1.14.1' ? $curlResponse['data']['token'] : null), 0, '/', null, false, false));
		}
		else
			throw new HttpException(500, 'Invalid JSON data format from GRAPPBOX API.');

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
			return $this->index_setLoginAPI(json_encode(array('data' => array('login' => $loginForm['email']->getData(), 'password' => $loginForm['password']->getData()))));

		return $this->render('HomeBundle:Home:index.html.twig', array('loginForm' => $loginForm->createView()));
	}
}
