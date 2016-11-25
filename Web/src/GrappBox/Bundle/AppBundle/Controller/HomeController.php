<?php

/*!
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{

  // Routine definition (public)
  // Beautify Symfony URLs by removing tailing slash 
  public function rewriteAction(Request $request)
  {
    $path = $request->getPathInfo();
    $url = $request->getRequestUri();

    return $this->redirect(str_replace($path, rtrim($path, " /"), $url), 301);
  }


  // Routine definition (public)
  // Load APP homepage
  public function indexAction()
  {
    return $this->render("AppBundle:home:index.html.twig");
  }

}