<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class PlanningController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the planning of the day",
	 * views = { "planning" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function getDayPlanningAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('get Day Planning Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the planning of the week",
	 * views = { "planning" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function getWeekPlanningAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('get Week Planning Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the planning of the month",
	 * views = { "planning" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function getMonthPlanningAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('get Month Plannning Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="modify an event",
	 * views = { "planning" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function modifyEventAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$content->projectId)
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $content->projectId, "event"))
			return ($this->setNoRightsError());

		return new Response('modify Event Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get an event details",
	 * views = { "planning" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the event you want the details"
     *      }
     *  }
	 * )
	 *
	 */
	public function getEventDetailsAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('get '.$id.' Event Details Success');
	}
}
