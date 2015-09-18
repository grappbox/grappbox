<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class CloudController extends Controller
{
    private $POSTRequestMethodError = new JsonResponse()->setData(array(
      "success" => false,
      "infos" => "This have to be a POST request"
    ));

    private $authorizationError = new JsonResponse()->setData(array(
      "success" => false,
      "infos" => "You haven't the authorization to have this information"
    ));

    private function checkTokenAuthorization($token, $idProject)
    {
        //Check if the person behind the $token have the authorization to use
        //cloud in the $idProject project
        return (false);
    }

    //This have to be a POST request
    /* Requested json
    {
      token : "userToken",
      project_id : 42,
      filename : "Awesomeness"
    }
    */
    public function openStreamAction(Request $request)
    {
        if ($request->getMethod() != "POST")
          return $this->POSTRequestMethodError;
        $dbManager = $this->getDoctrine()->getManager();
        $token = $request->get("token");
        $idProject = $request->get("project_id");
        if (!$this->checkTokenAuthorization($token, $idProject))
          return $this->authorizationError;
    }

    //This have to be a POST request
    public function closeStreamAction(Request $request)
    {
        if ($request->getMethod() != "POST")
          return $this->POSTRequestMethodError;
        //Check if the token in the request have an id_project transfer launched
    }

    //This have to be a POST request
    public function sendFileAction(Request $request)
    {
        if ($request->getMethod() != "POST")
          return $this->POSTRequestMethodError;
        if (!$this->checkTokenAuthorization($token, $idProject))
          return $this->authorizationError;
    }

    //This have to be a POST request
    public function listFilesAndDirectoriesAction(Request $request)
    {
        if ($request->getMethod() != "POST")
          return $this->POSTRequestMethodError;
        if (!$this->checkTokenAuthorization($token, $idProject))
          return $this->authorizationError;
    }

}
