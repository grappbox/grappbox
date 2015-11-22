<?php

/*!
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
 * COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\ShowcaseBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
			return $this->redirectToRoute('app_home');

		return $this->render('ShowcaseBundle:Home:index.html.twig', array('loginForm' => $loginForm->createView()));
	}
}
