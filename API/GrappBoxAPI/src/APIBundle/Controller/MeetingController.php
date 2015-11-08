<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class MeetingController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="edit an already existing meeting",
	 * views = { "meeting" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the meeting you want to edit"
     *      }
     *  }
	 * )
	 *
	 */
	public function editMeetingAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('edit Meeting '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete an already existing meeting",
	 * views = { "meeting" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the meeting you want to delete"
     *      }
     *  }
	 * )
	 *
	 */
	public function delMeetingAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('del Meeting '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="invite someone on an already existing meeting",
	 * views = { "meeting" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the meeting you want to invite a person to join"
     *      }
     *  }
	 * )
	 *
	 */
	public function invitePersonToMeetingAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('invite Person To Meeting '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="add an alert on an already existing meeting",
	 * views = { "meeting" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the meeting you want to add an alert to"
     *      }
     *  }
	 * )
	 *
	 */
	public function addAlertAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
			
		return new Response('add Alert '.$id.' Success');
	}
}
