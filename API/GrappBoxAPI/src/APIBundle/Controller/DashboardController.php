<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class DashboardController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get team occupation",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the team you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function getTeamOccupationAction(Request $request, $id)
	{
		return new Response('get Team '.$id.' Occupation Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get next meetings",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the meeting you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function getNextMeetingsAction(Request $request, $id)
	{
		return new Response('get Next '.$id.' Meetings Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get global progress of a project",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function getGlobalProgressAction(Request $request, $id)
	{
		return new Response('get Global Progress '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get personnal progress",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person connected"
     *      }
     *  }
	 * )
	 *
	 */
	public function getPersonnalProgressAction(Request $request, $id)
	{
		return new Response('get Personnal Progress '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get alerts on a project",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function getAlertsAction(Request $request, $id)
	{
		return new Response('get '.$id.' Alerts Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get assigned projects to the person connected",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person connected"
     *      }
     *  }
	 * )
	 *
	 */
	public function getAssignedProjectsAction(Request $request, $id)
	{
		return new Response('get Assigned Projects '.$id.' Success');
	}
}