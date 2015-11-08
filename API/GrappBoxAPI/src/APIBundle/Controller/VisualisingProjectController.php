<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class VisualisingProjectController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="add an action to the project",
	 * views = { "visualisingproject" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to add an action to"
     *      }
     *  }
	 * )
	 *
	 */
	public function addActionAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('add Action '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="add an actor to the project",
	 * views = { "visualisingproject" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to add an actor to"
     *      }
     *  }
	 * )
	 *
	 */
	public function addActorAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('add actor '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="add a link to the project",
	 * views = { "visualisingproject" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to add an link to"
     *      }
     *  }
	 * )
	 *
	 */
	public function addLinkAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('add Link '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="create a new diagram for a project",
	 * views = { "visualisingproject" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to add a diagram to"
     *      }
     *  }
	 * )
	 *
	 */
	public function newDiagramAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('new Diagram '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="open an already existing diagram",
	 * views = { "visualisingproject" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the diagram you want to open"
     *      }
     *  }
	 * )
	 *
	 */
	public function openDiagramAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('open Diagram '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="invite a person on a project",
	 * views = { "visualisingproject" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to add an person to"
     *      }
     *  }
	 * )
	 *
	 */
	public function invitePersonAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
			
		return new Response('invite Person '.$id.' Success');
	}
}
