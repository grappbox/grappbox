<?php

/*!
* This file is subject to the terms and conditions defined in
* file 'LICENSE.txt', which is part of the GRAPPBOX source code package.
* COPYRIGHT GRAPPBOX. ALL RIGHTS RESERVED.
*/

namespace GrappBox\Bundle\ShowcaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResourceController extends Controller
{
	public function removeTrailingSlashAction(Request $request)
	{
	    $pathInfo = $request->getPathInfo();
	    $requestUri = $request->getRequestUri();

	    $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

	    return $this->redirect($url, 301);
	}
}
