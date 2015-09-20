<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class TimelineController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the type of a timeline",
	 * views = { "timeline" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the timeline you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function getTimelineTypeAction(Request $request, $id)
	{
		return new Response('get Timeline Type Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="post a message on a timeline",
	 * views = { "timeline" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the timeline you want to post the message on"
     *      }
     *  }
	 * )
	 *
	 */
	public function postMessageAction(Request $request, $id)
	{
		return new Response('post Message Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get all the messages on a timeline",
	 * views = { "timeline" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the timeline you want"
     *      }
     *  }
	 * )
	 *
	 */
	public function getMessagesAction(Request $request, $id)
	{
		return new Response('get Messages Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a timeline",
	 * views = { "timeline" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the timeline you want to delete"
     *      }
     *  }
	 * )
	 *
	 */
	public function delMessageAction(Request $request, $id)
	{
		return new Response('del Message Success');
	}
}