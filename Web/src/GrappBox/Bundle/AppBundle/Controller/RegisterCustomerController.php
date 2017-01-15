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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use BrowscapPHP\Browscap;


class RegisterCustomerController extends Controller
{
  private $api_baseURL = "https://api.grappbox.com/";
  private $api_version = "0.3";
  private $cookies;


  // Routine definition
  // RegisterCustomerController constructor
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
  private function onCriticalError($token)
  {
    $redirect = new RedirectResponse("/register/customer?t=".$token);
    $redirect->headers->setCookie(new Cookie("G_LOGIN", base64_encode("_critical"),
      $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

    return $redirect;
  }


  // Routine definition
  // On password mismatch error behavior
  private function onPasswordMismatchError($token)
  {
    $redirect = new RedirectResponse("/register/customer?t=".$token);
    $redirect->headers->setCookie(new Cookie("G_LOGIN", base64_encode("_mismatch"),
      $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

    return $redirect;
  }


  // Routine definition
  // On missing access token error behavior
  private function onMissingTokenError()
  {
    $redirect = new RedirectResponse("/register");
    $redirect->headers->setCookie(new Cookie("G_LOGIN", base64_encode("_missingtoken"),
      $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

    return $redirect;
  }


  // Routine definition
  // On wrong (or empty) access code behavior
  private function onWrongCodeError($token)
  {
    $redirect = new RedirectResponse("/register/customer?t=".$token);
    $redirect->headers->setCookie(new Cookie("G_LOGIN", base64_encode("_badcode"),
      $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

    return $redirect;
  }


  // Routine definition
  // Get initial user data from GrappBox API
  private function getUserData($formData, $token)
  {
    $data = curl_init();

    curl_setopt($data, CURLOPT_URL, $this->api_baseURL.$this->api_version."/account/registercustomer");
    curl_setopt($data, CURLOPT_POST, 1);
    curl_setopt($data, CURLOPT_TIMEOUT, 30);
    curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($data, CURLOPT_POSTFIELDS, $formData);

    $JSON_data = curl_exec($data);

    if (curl_error($data))
      return $this->onCriticalError($token);
    curl_close($data);

    $response = json_decode($JSON_data, true);
    $redirect = ($response["info"]["return_code"] ? new RedirectResponse($response["info"]["return_code"] == "1.14.1" ? "/app" : "/register/customer?t=".$token) : null);

    if ($response["info"]["return_code"]) {
      switch ($response["info"]["return_code"]) {
        case "1.14.1":
        $redirect->headers->setCookie(new Cookie("G_LOGIN", base64_encode("_success"),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("G_TOKEN", base64_encode($response["data"]["token"]),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("G_ID", base64_encode($response["data"]["id"]),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("G_CUSTOMER", base64_encode(($response["data"]["is_client"] == true ? "_true" : "_false")),
          $this->cookies["time"], $this->cookies["base"], $this->cookies["domain"], $this->cookies["secure"], $this->cookies["httponly"]));
        break;

        case "14.4.4":
        $redirect->headers->setCookie(new Cookie("G_CUSTOMER", base64_encode(
          (strpos($response["info"]["return_message"], "password") ? "_badpassword" :
            (strpos($response["info"]["return_message"], "access") ? "_bad" :
              (strpos($response["info"]["return_message"], "project") ? "_already" :
                "_critical")))),
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
  // Load APP register page  
  public function indexAction(Request $request)
  {
    $request = Request::createFromGlobals();
    $cookieData = $request->cookies;
    
    $browscap = new Browscap();
    $browserData = $browscap->getBrowser();

    $data = array("token" => $request->query->get("t"));
    if ($data["token"] == "")
      return $this->onMissingTokenError();

    $form = $this->createFormBuilder($data)
    ->add("token", HiddenType::class)
    ->add("firstname", TextType::class)
    ->add("lastname", TextType::class)
    ->add("email", EmailType::class)
    ->add('password', PasswordType::class)
    ->add('password_confirmation', PasswordType::class)
    ->add('code', PasswordType::class)
    ->add("submit", SubmitType::class, array("label" => "Create account"))
    ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
      $token = $form["token"]->getData();

      $code = $form["code"]->getData();
      if ($code != "XP16")
        return $this->onWrongCodeError($token);

      if (strcmp($form["password"]->getData(), $form["password_confirmation"]->getData()) !== 0)
        return $this->onPasswordMismatchError($token);

      return $this->getUserData(json_encode(array("data" => array(
        "token" => $token,
        "firstname" => $form["firstname"]->getData(),
        "lastname" => $form["lastname"]->getData(),
        "email" => strtolower($form["email"]->getData()),
        "password" => $form["password"]->getData(),
        "is_client" => true,
        "mac" => null,
        "flag" => "web",
        "device_name" => $browserData->parent))), $token);
    }

    return $this->render("AppBundle:home:register-customer.html.twig", array("form" => $form->createView()));   
  }

}