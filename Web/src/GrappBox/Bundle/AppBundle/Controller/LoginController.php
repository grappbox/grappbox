<?php

/*!
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use BrowscapPHP\Browscap;

class LoginController extends Controller
{
  private $api_baseURL = "https://api.grappbox.com/";
  private $api_version = "0.3";
  private $cookies;


  // Routine definition
  // LoginController constructor
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
    $redirect = new RedirectResponse("/login");
    $redirect->headers->setCookie(new Cookie("LOGIN", base64_encode("_critical"),
      $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

    return $redirect;
  }


  // Routine definition
  // Get initial user data from GrappBox API
  private function getLoginData($formData)
  {
    $data = curl_init();

    curl_setopt($data, CURLOPT_URL, $this->api_baseURL.$this->api_version."/account/login");
    curl_setopt($data, CURLOPT_POST, 1);
    curl_setopt($data, CURLOPT_TIMEOUT, 30);
    curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($data, CURLOPT_POSTFIELDS, $formData);

    $JSON_data = curl_exec($data);

    if (curl_error($data))
      return $this->onCriticalError();
    curl_close($data);

    $response = json_decode($JSON_data, true);
    $redirect = ($response["info"]["return_code"] ? new RedirectResponse($response["info"]["return_code"] == "1.14.1" ? "/app" : "/login") : null);

    if ($response["info"]["return_code"]) {
      switch ($response["info"]["return_code"]) {
        case "1.14.1":
        $redirect->headers->setCookie(new Cookie("LOGIN", base64_encode("_success"),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("TOKEN", base64_encode($response["data"]["token"]),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("ID", base64_encode($response["data"]["id"]),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));
        break;

        case "14.1.4":
        $redirect->headers->setCookie(new Cookie("LOGIN", base64_encode((strpos($response["info"]["return_message"], "password") ? "_badpassword" : "_badlogin")),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));
        break;

        default:
        break;
      }
    }
    else
      return $this->onCriticalError();
    
    return $redirect;
  }


  // Routine definition
  // Check stored user data before login   
  private function setLoginState($token)
  {
    $data = curl_init();

    $header = array();
    $header[] = "Content-length: 0";
    $header[] = "Content-type: application/json";
    $header[] = "Authorization: ".$token;

    curl_setopt($data, CURLOPT_URL, $this->api_baseURL.$this->api_version."/user");
    curl_setopt($data, CURLOPT_HTTPHEADER, $header);
    curl_setopt($data, CURLOPT_TIMEOUT, 30);
    curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);

    $JSON_data = curl_exec($data);

    if (curl_error($data))
      return $this->onCriticalError();
    curl_close($data);

    $response = json_decode($JSON_data, true);
    $redirect = ($response["info"]["return_code"] ? new RedirectResponse($response["info"]["return_code"] == "1.7.1" ? "/app" : "/login") : null);

    if ($response["info"]["return_code"]) {
      switch ($response["info"]["return_code"]) {
        case "1.7.1":
        break;

        case "7.1.3":
        $redirect->headers->setCookie(new Cookie("LOGIN", base64_encode("_denied"),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));
        break;

        default:
        break;
      }
    }
    else
      return $this->onCriticalError();
    return $redirect;
  }


  // Routine definition (public)
  // Load APP login page  
  public function indexAction(Request $request)
  {
    $request = Request::createFromGlobals();
    $cookieData = $request->cookies;

    $browscap = new Browscap();
    $browserData = $browscap->getBrowser();

    if ($cookieData->has("TOKEN") && $cookieData->get("TOKEN"))
      return $this->setLoginState(base64_decode($cookieData->get("TOKEN")));

    $form_options = array();
    $form = $this->createFormBuilder($form_options)
    ->add("email", EmailType::class)
    ->add("password", PasswordType::class)
    ->add("submit", SubmitType::class, array("label" => "Login"))
    ->getForm();

    $form->handleRequest($request);

    if ($form->isValid())
      return $this->getLoginData(json_encode(array("data" => array(
        "login" => strtolower($form["email"]->getData()),
        "password" => $form["password"]->getData(),
        "is_client" => false,
        "mac" => null,
        "flag" => "web",
        "device_name" => $browserData->parent))));        

    return $this->render("AppBundle:Home:login.html.twig", array("form" => $form->createView()));   
  }

}