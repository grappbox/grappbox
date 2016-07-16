<?php

/*!
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AppController extends Controller
{

  // Routine definition
  // Beautify angularJS URLs (remove "/#/") 
  public function angularRouteManagementAction(Request $request)
  {
    $path = $request->getpathInfo();

    if (($token = strpos($path, "/app/")) !== false)
      return $this->redirect(substr_replace($path, "/app/#/", $token, strlen("/app/")), 301);
  }


  // Start point
  // Load APP homepage (connected)
  public function indexAction()
  {
    $cookies = array(
      "time" => time() + 2592000,
      "base" => "/",
      "domain" => null,
      "secure" => false,
      "httponly" => false
      );

    $request = $this->get("request");
    $cookieData = $request->cookies;

    if ($cookieData->has("TOKEN") && $cookieData->get("TOKEN"))
      return $this->render("AppBundle:App:index.html.twig");

    $redirect = new RedirectResponse("/login");

    $redirect->headers->setCookie(new Cookie("LOGIN", base64_encode("_denied"),
      $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

    $redirect->headers->setCookie(new Cookie("TOKEN", null,
      $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

    $redirect->headers->setCookie(new Cookie("ID", null,
      $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

    return $redirect;
  }

}