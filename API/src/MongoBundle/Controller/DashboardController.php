<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use MongoBundle\Controller\RolesAndTokenVerificationController;
use MongoBundle\Document\Project;
use MongoBundle\Document\User;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *  @IgnoreAnnotation("apiVersion")
 *  @IgnoreAnnotation("apiSuccess")
 *  @IgnoreAnnotation("apiSuccessExample")
 *  @IgnoreAnnotation("apiError")
 *  @IgnoreAnnotation("apiErrorExample")
 *  @IgnoreAnnotation("apiParam")
 *  @IgnoreAnnotation("apiParamExample")
 *	@IgnoreAnnotation("apiDescription")
 */
class DashboardController extends RolesAndTokenVerificationController
{
	/**
	* @-api {get} /0.3/dashboard/occupation/:id Get team occupation
	* @apiName getTeamOccupation
	* @apiGroup Dashboard
	* @apiDescription Getting a team occupation for a project for the user connected
	* @apiVersion 0.3.0
	*
	*/
	public function getTeamOccupationAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("2.1.3", "Dashboard", "getteamoccupation"));

		$project = $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Project')->find($id);

		if ($project === null)
			return $this->setBadRequest("2.1.4", "Dashboard", "getteamoccupation", "Bad Parameter: projectId");

		return $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Project')->findTeamOccupation($project->getId());
	}

	/**
	* @-api {get} /0.3/dashboard/meetings/:id Get next meetings
	* @apiName getNextMeetings
	* @apiGroup Dashboard
	* @apiDescription Get all next meetings, in 7 days, of the connected user
	* @apiVersion 0.3.0
	*
	*/
	public function getNextMeetingsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("2.2.3", "Dashboard", "getnextmeetings"));

		return $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Event')->findNextMeetings($user->getId(), $id, "2", "Dashboard", "getnextmeetings");
	}

	/**
	* @-api {get} /0.3/dashboard/projects Get projects global progress
	* @apiName getProjectsGlobalProgress
	* @apiGroup Dashboard
	* @apiDescription Get the global progress of the projects of a user
	* @apiVersion 0.3.0
	*
	*/
	public function getProjectsGlobalProgressAction(Request $request)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("2.3.3", "Dashboard", "getProjectsGlobalProgress"));

		return ($this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Project')->findProjectGlobalProgress($user->getId()));
	}
}
