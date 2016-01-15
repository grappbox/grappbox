<?php

/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

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
