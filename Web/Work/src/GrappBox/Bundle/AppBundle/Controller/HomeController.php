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

  // Routine definition
  // Beautify Symfony URLs (remove tailing slash) 
  public function tailingSlashAction(Request $request)
  {
    $path = $request->getPathInfo();
    $url = $request->getRequestUri();

    return $this->redirect(str_replace($path, rtrim($path, " /"), $url), 301);
  }


  // Start point
  // Load APP homepage (public)	
  public function indexAction()
  {
    return $this->render("AppBundle:Home:index.html.twig");
  }

}