<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CloudController extends Controller
{
    private function checkTokenAuthorization($token, $idProject)
    {
        //Check if the person behind the $token have the authorization to use
        //cloud in the $idProject project
        return (false);
    }

    //This have to be a POST request
    public function openStreamAction(Request $request)
    {
        if (!$this->checkTokenAuthorization($token, $idProject))
        {
          // If the user haven't authorization, send error json
        }
    }

    //This have to be a POST request
    public function closeStreamAction(Request $request)
    {
        if (!$this->checkTokenAuthorization($token, $idProject))
        {
          // If the user haven't authorization, send error message
        }
    }

    public function sendFileAction(Request $request)
    {
        if (!$this->checkTokenAuthorization($token, $idProject))
        {
          // If the user haven't authorization, send error message
        }
    }

    //This have to be a GET request
    public function listFilesAndDirectoriesAction(Request $request)
    {
        if (!$this->checkTokenAuthorization($token, $idProject))
        {
          // If the user haven't authorization, send error message
        }
    }

}
