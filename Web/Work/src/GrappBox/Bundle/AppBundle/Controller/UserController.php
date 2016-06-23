<?php

/*!
* This file is subject to the terms and conditions defined in
* file "LICENSE.txt", which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UserController extends Controller
{

  // Routine definition
  // Get initial user data from GrappBox API  
  private function getLoginData($formData)
  {
    $api_baseURL = "http://api.grappbox.com/app_dev.php/";
    $api_version = "V0.2";

    $cookies = array(
      "time" => time() + 2592000,
      "base" => "/",
      "domain" => null,
      "secure" => false,
      "httponly" => false
      );

    $data = curl_init();

    curl_setopt($data, CURLOPT_URL, $api_baseURL.$api_version."/accountadministration/login");
    curl_setopt($data, CURLOPT_POST, 1);
    curl_setopt($data, CURLOPT_TIMEOUT, 30);
    curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($data, CURLOPT_POSTFIELDS, $formData);

    $JSON_data = curl_exec($data);

    if (curl_error($data))
      throw new HttpException(500, "Unable to fetch data from GrappBox API. Please try again.");

    curl_close($data);

    $response = json_decode($JSON_data, true);
    $redirect = ($response["info"]["return_code"] ? new RedirectResponse($response["info"]["return_code"] == "1.14.1" ? "/app" : "/login") : null);

    if ($response["info"]["return_code"]) {
      switch ($response["info"]["return_code"]) {
        case "1.14.1":
        $redirect->headers->setCookie(new Cookie("_LOGIN", base64_encode("_success"),
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("_TOKEN", base64_encode($response["data"]["token"]),
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("_ID", base64_encode($response["data"]["id"]),
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));
        break;

        case "14.1.4":
        $redirect->headers->setCookie(new Cookie("_LOGIN", base64_encode((strpos($response["info"]["return_message"], "password") ? "_badpassword" : "_badlogin")),
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("_TOKEN", null,
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("_ID", null,
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));
        break;

        default:
        break;
      }
    }
    else
      throw new HttpException(500, 'Invalid JSON data format from GRAPPBOX API.');
    return $redirect;
  }


  // Routine definition
  // Check stored user data before login   
  private function checkLoginData($token)
  {
    $api_baseURL = "http://api.grappbox.com/app_dev.php/";
    $api_version = "V0.2";

    $data = curl_init();

    curl_setopt($data, CURLOPT_URL, $api_baseURL.$api_version."/user/basicinformations/".$token);
    curl_setopt($data, CURLOPT_TIMEOUT, 30);
    curl_setopt($data, CURLOPT_RETURNTRANSFER, 1);

    $JSON_data = curl_exec($data);

    if (curl_error($data))
      throw new HttpException(500, "Unable to fetch data from GrappBox API. Please try again.");

    curl_close($data);

    $response = json_decode($JSON_data, true);
    $redirect = ($response["info"]["return_code"] ? new RedirectResponse($response["info"]["return_code"] == "1.7.1" ? "/app" : "/login") : null);

    if ($response["info"]["return_code"]) {
      switch ($response["info"]["return_code"]) {
        case "1.7.1":
        break;

        case "7.1.3":
        $redirect->headers->setCookie(new Cookie("_LOGIN", base64_encode("_denied"),
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("_TOKEN", null,
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));

        $redirect->headers->setCookie(new Cookie("_ID", null,
          $cookies["time"], $cookies["base"], $cookies["domain"], $cookies["secure"], $cookies["httponly"]));
        break;

        default:
        break;
      }
    }
    else
      throw new HttpException(500, "Invalid JSON data format from GrappBox API. Please try again.");
    return $redirect;
  }


  // Start point
  // Load APP login page (public)  
  public function loginAction(Request $request)
  {
    $request = $this->get("request");
    $cookieData = $request->cookies;

    if ($cookieData->has("_TOKEN") && $cookieData->get("_TOKEN"))
      return $this->checkLoginData(base64_decode($cookieData->get("_TOKEN")));

    $form_options = array();
    $form = $this->createFormBuilder($form_options)
    ->add("email", "email")
    ->add("password", "password")
    ->add("submit", "submit", array("label" => "Login"))
    ->getForm();

    $form->handleRequest($request);

    if ($form->isValid())
      return $this->getLoginData(json_encode(array("data" => array(
        "login" => $form["email"]->getData(),
        "password" => $form["password"]->getData()))));

    return $this->render("AppBundle:Home:login.html.twig", array("form" => $form->createView()));   
  }

}