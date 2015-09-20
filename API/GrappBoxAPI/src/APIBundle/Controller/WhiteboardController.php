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
     * }
     * )
	 *
	 */
	public function newWhiteboardAction(Request $request)
	{
		return new Response('new Whiteboard Success');
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
     *  }
	 * )
	 *
	 */
	public function delWhiteboardAction(Request $request, $id)
	{
		return new Response('del Whiteboard '.$id.' Success');
	}
}