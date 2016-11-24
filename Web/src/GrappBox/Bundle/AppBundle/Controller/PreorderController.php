<?php

/*!
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use BrowscapPHP\Browscap;


class PreorderController extends Controller
{
  private $api_baseURL = "https://api.grappbox.com/";
  private $api_version = "0.3";
  private $cookies;


  // Routine definition
  // PreorderController constructor
  public function __construct() {
    $this->cookies = array(
      "time" => time() + 2592000,
      "base" => "/",
      "domain" => null,
      "secure" => false,
      "httponly" => false
    );
  }


  // Routine definition
  // On API critical error behavior
  private function onCriticalError()
  {
    $redirect = new RedirectResponse("/register");
    $redirect->headers->setCookie(new Cookie("G_PREORDER", base64_encode("_critical"),
      $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

    return $redirect;
  }


  // Routine definition
  // Get initial user data from GrappBox API
  private function setUserPreOrder($formData)
  {
    $data = curl_init();

    curl_setopt($data, CURLOPT_URL, $this->api_baseURL.$this->api_version."/account/preorder");
    curl_setopt($data, CURLOPT_POST, 1);
    curl_setopt($data, CURLOPT_TIMEOUT, 30);
    curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($data, CURLOPT_POSTFIELDS, $formData);

    $JSON_data = curl_exec($data);

    if (curl_error($data))
      return $this->onCriticalError();
    curl_close($data);

    $response = json_decode($JSON_data, true);
    $redirect = ($response["info"]["return_code"] ? new RedirectResponse($response["info"]["return_code"] == "1.14.1" ? "/register" : "/register") : null);

    if ($response["info"]["return_code"]) {
      switch ($response["info"]["return_code"]) {
        case "1.14.1":
        $redirect->headers->setCookie(new Cookie("G_PREORDER", base64_encode("_success"),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));
        break;

        case "14.1.7":
        $redirect->headers->setCookie(new Cookie("G_PREORDER", base64_encode("_already"),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));
        break;

        default:
        $redirect->headers->setCookie(new Cookie("G_PREORDER", base64_encode("_critical"),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));
        break;
      }
    }
    else
      return $this->onCriticalError();
    
    return $redirect;
  }


  // Routine definition (public)
  // Load APP pre-order page  
  public function indexAction(Request $request)
  {
    $request = Request::createFromGlobals();
    $cookieData = $request->cookies;
    
    $browscap = new Browscap();
    $browserData = $browscap->getBrowser();

    $form_options = array();
    $form = $this->createFormBuilder($form_options)
    ->add("firstname", TextType::class)
    ->add("lastname", TextType::class)
    ->add("email", EmailType::class)
    ->add("submit", SubmitType::class, array("label" => "Pre-order"))
    ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
      return $this->setUserPreorder(json_encode(array("data" => array(
        "firstname" => $form["firstname"]->getData(),
        "lastname" => $form["lastname"]->getData(),
        "email" => strtolower($form["email"]->getData())))));
    }

    return $this->render("AppBundle:Home:preorder.html.twig", array("form" => $form->createView()));   
  }

}