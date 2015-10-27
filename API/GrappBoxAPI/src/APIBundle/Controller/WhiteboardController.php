<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class WhiteboardController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="add a new whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * },
		 * parameters={
		 *      {"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="authentification token"},
	   *      {"name"="projectId", "dataType"="int(11)", "required"=true, "description"="related project id"},
		 *      {"name"="userId", "dataType"="int(11)", "required"=true, "description"="creator user id"},
		 *      {"name"="whiteboardName", "dataType"="varchar(255)", "required"=true, "description"="whiteboard name"},
		 *  }
     * )
	 *
	 */
	public function newWhiteboardAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->findBy('User', array('token' => $request->request->get('_token')));
		if (!$user)
			return new Response('Error, you\'re not login or have no right on this action');

		$whiteboard = new Whiteboard();
		$whiteboard->setProjectId($request->request->get('projectId'));
		$whiteboard->setUserId($request->request->get('userId'));
		$whiteboard->setUpdatorId($request->request->get('updatorId'));
		$whiteboard->setName($request->request->get('whiteboardName'));

		$em = $this->getDoctrine()->getManager();
		$em->persist($whiteboard);
		$em->flush();

		return new Response('Create new whiteboard of ID : '.$Whiteboard->getId());
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="open an already existing whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function openWhiteboardAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->findBy('User', array('token' => $request->request->get('_token')));
		if (!$user)
			return new Response('Error, you\'re not login or have no right on this action');

		return new Response('open Whiteboard '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="push a draw on a whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function pushDrawAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->findBy('User', array('token' => $request->request->get('_token')));
		if (!$user)
			return new Response('Error, you\'re not login or have no right on this action');

		return new Response('push Draw '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="pull a draw on a whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function pullDrawAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->findBy('User', array('token' => $request->request->get('_token')));
		if (!$user)
			return new Response('Error, you\'re not login or have no right on this action');

		return new Response('pull Draw '.$id.' Success');

	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="exit a whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function exitWhiteboardAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->findBy('User', array('token' => $request->request->get('_token')));
		if (!$user)
			return new Response('Error, you\'re not login or have no right on this action');

		return new Response('exit Whiteboard '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a whiteboard",
	 * views = { "whiteboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the whiteboard you want"
     *      }
     *  },
		 * parameters={
	   *      {"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="authentification token"},
		 *  }
	 * )
	 *
	 */
	public function delWhiteboardAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->findBy('User', array('token' => $request->request->get('_token')));
		if (!$user)
			return new Response('Error, you\'re not login or have no right on this action');

		$whiteboard = $em->find('User', $id);
		if ($whiteboard)
		{
			$em->remove($whiteboard);
			$em->flush();
		}
		return new Response('del Whiteboard '.$id.' Success');
	}
}
