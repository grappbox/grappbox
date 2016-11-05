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

  // Routine definition (public)
  // Beautify AngularJS URLs by removing "/#/" 
  public function rewriteAction(Request $request)
  {
    $path = $request->getpathInfo();

    if (($token = strpos($path, "/app/")) !== false)
      return $this->redirect(substr_replace($path, "/app/#/", $token, strlen("/app/")), 301);
  }


  // Routine definition (public)
  // Load APP homepage
  public function indexAction()
  {
    $cookies = array(
      "time" => time() + 2592000,
      "base" => "/",
      "domain" => null,
      "secure" => false,
      "httponly" => false
      );

    $request = Request::createFromGlobals();
    $cookieData = $request->cookies;

    if ($cookieData->has("G_TOKEN") && $cookieData->get("G_TOKEN"))
      return $this->render("AppBundle:App:index.html.twig");

    $redirect = new RedirectResponse("/login");

    $redirect->headers->setCookie(new Cookie("G_LOGIN", base64_encode("_denied"),
      $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

    $redirect->headers->setCookie(new Cookie("G_TOKEN", null,
      $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

    $redirect->headers->setCookie(new Cookie("G_ID", null,
      $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

    return $redirect;
  }

}