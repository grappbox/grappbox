<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SQLBundle\Controller\RolesAndTokenVerificationController;

use SQLBundle\Entity\User;
use SQLBundle\Entity\Bug;
use SQLBundle\Entity\BugComment;
use SQLBundle\Entity\BugState;
use SQLBundle\Entity\BugtrackerTag;
use SQLBundle\Entity\Project;
use DateTime;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *	@IgnoreAnnotation("apiDescription")
 *  @IgnoreAnnotation("apiVersion")
 *  @IgnoreAnnotation("apiSuccess")
 *  @IgnoreAnnotation("apiSuccessExample")
 *  @IgnoreAnnotation("apiError")
 *  @IgnoreAnnotation("apiErrorExample")
 *  @IgnoreAnnotation("apiParam")
 *  @IgnoreAnnotation("apiParamExample")
 *  @IgnoreAnnotation("apiHeader")
 *  @IgnoreAnnotation("apiHeaderExample")
 */
class BugtrackerController extends RolesAndTokenVerificationController
{
	
}