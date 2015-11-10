<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use APIBundle\Controller\RolesAndTokenVerificationController;

//use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
 */
class BugtrackerController extends RolesAndTokenVerificationController
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="post a ticket",
	 * views = { "bugtracker" },
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
	public function postTicketAction(Request $request)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "bugtracker"))
			return ($this->setNoRightsError());

		return new Response('post Ticket Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="modify an already existing ticket",
	 * views = { "bugtracker" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the ticket you want to modify"
     *      }
     *  }
	 * )
	 *
	 */
	public function modifyTicketAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "bugtracker"))
			return ($this->setNoRightsError());

		return new Response('modify Ticket '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a list of all tickets",
	 * views = { "bugtracker" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want that list"
     *      }
     *  }
	 * )
	 *
	 */
	public function getTicketListAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "bugtracker"))
			return ($this->setNoRightsError());
		return new Response('get '.$id.' Ticket List Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="comment an already existing ticket",
	 * views = { "bugtracker" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the ticket you want to comment"
     *      }
     *  }
	 * )
	 *
	 */
	public function commentTicketAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "bugtracker"))
			return ($this->setNoRightsError());
		return new Response('comment '.$id.' Ticket Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="close an already existing ticket and set him as done",
	 * views = { "bugtracker" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the ticket you want to close"
     *      }
     *  }
	 * )
	 *
	 */
	public function closeTicketAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "bugtracker"))
			return ($this->setNoRightsError());

		return new Response('close '.$id.' Ticket Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the details of an already existing ticket",
	 * views = { "bugtracker" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the ticket you want the details"
     *      }
     *  }
	 * )
	 *
	 */
	public function getTicketDetailsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "bugtracker"))
			return ($this->setNoRightsError());

		return new Response('get '.$id.' Ticket Deatils Success');
	}
}
