<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MongoBundle\Document\Event;
use MongoBundle\Document\EventType;
use MongoBundle\Document\User;
use MongoBundle\Document\Project;
use DateTime;

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


class EventController extends RolesAndTokenVerificationController
{
	/**
	* @api {get} /mongo/event/getTypes/:token Get event types
	* @apiName getTypes
	* @apiGroup Event
	* @apiDescription Get all event types
	* @apiVersion 0.2.0
	*
	*/
	public function getTypesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.1.3", "Calendar", "getTypes"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$types = $em->getRepository("MongoBundle:EventType")->findAll();

		if (count($types) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getTypes");

		$types_array = array();
		foreach ($types as $key => $value) {
			$types_array[] = $value->objectToArray();
		}

		return $this->setSuccess("1.5.1", "Calendar", "getTypes", "Complete Success", array("array" => $types_array));
	}

	/**
	* @api {get} /mongo/event/getevent/:token/:id get event
	* @apiName getEvent
	* @apiGroup Event
	* @apiDescription Get an event informations
	* @apiVersion 0.2.0
	*
	*/
	public function getEventAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.2.3", "Calendar", "getEvent"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("5.2.4", "Calendar", "getEvent", "Bad Parameter: id");

		if ($event->getProjects() instanceof Project)
			{
				$project = $event->getProjects();
				if ($this->checkRoles($user, $project->getId(), "event") < 1)
					return ($this->setNoRightsError("5.2.9", "Calendar", "getEvent"));
			}
		else
			{
				$check = false;
				foreach ($event->getUsers() as $key => $value) {
					 if ($user->getId() == $value->getId())
					 		$check = true;
				}
				if (!$check)
					return ($this->setNoRightsError("5.2.9", "Calendar", "getEvent"));
			}

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}
		$object = $event->objectToArray();
		$object["users"] = $participants;

		return $this->setSuccess("1.5.1", "Calendar", "getEvent", "Complete Success", $object);
	}

	/**
	* @api {put} /mongo/event/setparticipants Set participants
	* @apiName setParticipants
	* @apiGroup Event
	* @apiDescription Add/remove users to the event
	* @apiVersion 0.2.0
	*
	*/
	public function setParticipantsAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("token", $content) || !array_key_exists("eventId", $content) || !array_key_exists("toAdd", $content) || !array_key_exists("toRemove", $content))
			return $this->setBadRequest("5.3.6", "Calendar", "setParticipants", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("5.3.3", "Calendar", "setParticipants"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("5.3.4", "Calendar", "setParticipants", "Bad Parameter: eventId");

		if ($event->getProjects() instanceof Project)
		{
			if ($this->checkRoles($user, $event->getProjects()->getId(), "event") < 2)
				return ($this->setNoRightsError("5.3.9", "Calendar", "setParticipants"));
		}
		else {
			$check = false;
			foreach ($event->getUsers() as $key => $value) {
				 if ($user->getId() == $value->getId())
						$check = true;
			}
			if (!$check)
				return ($this->setNoRightsError("5.3.9", "Calendar", "setParticipants"));
		}

		$class = new NotificationController();

		$mdata['mtitle'] = "Event - Event Assigned";
		$mdata['mdesc'] = "You have been assigned to event ".$event->getTitle();

		$wdata['type'] = "Event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = "You have been assigned to event ".$event->getTitle();

		foreach ($content->toAdd as $key => $value) {
			$toAddUser = $em->getRepository("MongoBundle:User")->find($value);
			if ($toAddUser instanceof User)
			{
				foreach ($event->getUsers() as $key => $value) {
					if ($user->getId() == $toAddUser->getId())
						return $this->setBadRequest("5.3.4", "Calendar", "setParticipants", "Already in Database");
				}

				$event->addUser($toAddUser);

				$userNotif = array($value);
				$class->pushNotification($userNotif, $mdata, $wdata, $em);
			}
		}

		$mdata['mtitle'] = "Event - Event Remove";
		$mdata['mdesc'] = "You have been removed of event ".$event->getTitle();

		$wdata['type'] = "Event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = "You have been removed of event ".$event->getTitle();

		foreach ($content->toRemove as $key => $value) {
			$toRemoveUser = $em->getRepository("MongoBundle:User")->find($value);
			if ($toRemoveUser instanceof User)
			{
				// if ($toRemoveUser->getId() == $event->getCreatorUser()->getId())
				// 	return $this->setBadRequest("5.3.7", "Calendar", "setParticipants", "Bad Parameter: toRemove-id");

				$event->removeUser($toRemoveUser);

				$userNotif = array($value);
				$class->pushNotification($userNotif, $mdata, $wdata, $em);
			}
		}

		$em->persist($event);
		$em->flush();

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}
		$object = $event->objectToArray();
		$object["users"] = $participants;

		return $this->setSuccess("1.5.1", "Calendar", "setParticipants", "Complete Success", $object);
	}

	/**
	* @api {post} /mongo/event/postevent/:id Post event
	* @apiName postEvent
	* @apiGroup Event
	* @apiDescription Post an event/meeting
	* @apiVersion 0.2.0
	*
	*/
	public function postEventAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("token", $content) || !array_key_exists("title", $content) || !array_key_exists("description", $content) || !array_key_exists("icon", $content)
			|| !array_key_exists("typeId", $content) || !array_key_exists("begin", $content)|| !array_key_exists("end", $content))
			return $this->setBadRequest("5.4.6", "Calendar", "postEvent", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("5.4.3", "Calendar", "postEvent"));

		$em = $this->get('doctrine_mongodb')->getManager();
		if (array_key_exists("projectId", $content))
		{
			$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
			if ($this->checkRoles($user, $content->projectId, "event") < 2)
				return ($this->setNoRightsError("5.3.9", "Calendar", "postEvent"));
		}

		$event = new Event();
		$event->setCreatorUser($user);
		if (array_key_exists("projectId", $content))
			$event->setProjects($project);
		$type = $em->getRepository("MongoBundle:EventType")->find($content->typeId);
		$event->setEventtypes($type);
		$event->setTitle($content->title);
		$event->setDescription($content->description);
		$event->setIcon($content->icon);
		$event->setBeginDate(new DateTime($content->begin));
		$event->setEndDate(new DateTime($content->end));
		$event->setCreatedAt(new DateTime('now'));

		$em->persist($event);
		$em->flush();

		$event->addUser($user);
		$em->flush();

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}
		$object = $event->objectToArray();
		$object["users"] = $participants;

		return $this->setSuccess("1.5.1", "Calendar", "postEvent", "Complete Success", $object);
	}

	/**
	* @api {put} /mongo/event/editevent/:id Edit event
	* @apiName editEvent
	* @apiGroup Event
	* @apiDescription Edit an event/meeting
	* @apiVersion 0.2.0
	*
	*/
	public function editEventAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("token", $content) || !array_key_exists("eventId", $content) || !array_key_exists("title", $content) || !array_key_exists("description", $content)
			|| !array_key_exists("icon", $content) || !array_key_exists("typeId", $content) || !array_key_exists("begin", $content)|| !array_key_exists("end", $content))
			return $this->setBadRequest("5.5.6", "Calendar", "editEvent", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("5.5.3", "Calendar", "editEvent"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("5.5.9", "Calendar", "editEvent", "Bad Parameter: eventId");

		if ($event->getProjects() instanceof Project)
		{
			if ($this->checkRoles($user, $event->getProjects()->getId(), "event") < 2)
				return ($this->setNoRightsError("5.5.9", "Calendar", "editEvent"));
		}
		else {
			$check = false;
			foreach ($event->getUsers() as $key => $value) {
				 if ($user->getId() == $value->getId())
						$check = true;
			}
			if (!$check)
				return ($this->setNoRightsError("5.5.9", "Calendar", "editEvent"));
		}
		if (array_key_exists("projectId", $content))
		{
			$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
			if ($this->checkRoles($user, $content->projectId, "event") < 2)
				return ($this->setNoRightsError("5.5.9", "Calendar", "editEvent"));
		}

		if (array_key_exists("projectId", $content))
			$event->setProjects($project);
		$type = $em->getRepository("MongoBundle:EventType")->find($content->typeId);
		$event->setEventtypes($type);
		$event->setTitle($content->title);
		$event->setDescription($content->description);
		$event->setIcon($content->icon);
		$event->setBeginDate(new DateTime($content->begin));
		$event->setEndDate(new DateTime($content->end));
		$event->setEditedAt(new DateTime('now'));

		$em->persist($event);
		$em->flush();

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}

		$class = new NotificationController();

		$mdata['mtitle'] = "Event - Event Edited";
		$mdata['mdesc'] = "The event ".$event->getTitle()." has been edited";

		$wdata['type'] = "Event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = "The event ".$event->getTitle()." has been edited";

		$userNotif = array();
		foreach ($event->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}

		if (count($userNotif) > 0)
			$class->pushNotification($userNotif, $mdata, $wdata, $em);

		$object = $event->objectToArray();
		$object["users"] = $participants;

		return $this->setSuccess("1.5.1", "Calendar", "editEvent", "Complete Success", $object);
	}

	/**
	* @api {delete} /mongo/event/delevent/:token/:id Delete event
	* @apiName delEvent
	* @apiGroup Event
	* @apiDescription Delete an event/meeting
	* @apiVersion 0.2.0
	*
	*/
	public function delEventAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.6.3", "Calendar", "delEvent"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("5.6.4", "Calendar", "delEvent", "Bad Parameter: id");

		if ($event->getProjects() instanceof Project)
			{
				$project = $event->getProjects();
				if ($this->checkRoles($user, $project->getId(), "event") < 2)
					return ($this->setNoRightsError("5.6.9", "Calendar", "delEvent"));
			}
		else if ($user->getId() != $event->getCreatorUser()->getId())
			{
				return ($this->setNoRightsError("5.6.9", "Calendar", "delEvent"));
			}

		$event->setDeletedAt(new DateTime('now'));

		$em->flush();

		$response["info"]["return_code"] = "1.5.1";
		$response["info"]["return_message"] = "Calendar - delEvent - Complete Success";
		return new JsonResponse($response);
	}
}
