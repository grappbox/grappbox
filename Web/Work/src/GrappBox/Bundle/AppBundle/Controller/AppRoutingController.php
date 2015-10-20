<?php

namespace GrappBox\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AppRoutingController extends Controller
{
    public function enableAngularRouteManagementAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();

        $tokenPos = strpos($pathInfo, "/app/");
        if ($tokenPos !== false)
        {
            $angularUrl = substr_replace($pathInfo, "/app/#/", $tokenPos, strlen("/app/"));
            return $this->redirect($angularUrl, 301);
        }
    }
}
