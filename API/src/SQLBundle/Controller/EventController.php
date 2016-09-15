<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use SQLBundle\Entity\Event;
use SQLBundle\Entity\EventType;
use SQLBundle\Entity\User;
use SQLBundle\Entity\Project;
use DateTime;

/**
*  @IgnoreAnnotation("apiName")
*  @IgnoreAnnotation("apiGroup")
*  @IgnoreAnnotation("apiDescription")
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
	* @api {post} /V0.2/event/postevent Post event
	* @apiName postEvent
	* @apiGroup Event
	* @apiDescription Post an event/meeting
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {int}	[projectId] project's id (if related to a project)
	*	@apiParam {string} title event title
	*	@apiParam {string} description event description
	*	@apiParam {Text} icon Icon of the event
	*	@apiParam {int} typeId event type id
	*	@apiParam {DateTime} begin beginning date & hour of the event
	*	@apiParam {DateTime} end ending date & hour of the event
	*
	* @apiParamExample {json} Request-Exemple No project:
	* 	{
	*		"data":
	*		{
	*			"token": "ThisIsMyToken",
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"typeId":  1,
	*			"begin": "1945-06-18 06:00:00",
	*			"end": "1945-06-18 08:00:00"
	*		}
	* 	}
	* @apiParamExample {json} Request-Exemple With project:
	* 	{
	*		"data":
	*		{
	*			"token": "ThisIsMyToken",
	*			"projectId": 21,
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"typeId":  1,
	*			"begin":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"end":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		}
	* 	}
	*
	* @apiSuccess {int} id Event id
	* @apiSuccess {Object} creator creator object
	* @apiSuccess {int} creator.id creator's id
	* @apiSuccess {String} creator.fullname creator's fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {Object} type Event type object
	* @apiSuccess {int} type.id Event type id
	* @apiSuccess {string} type.name Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {string} description event description
	*	@apiSuccess {Text} icon Icon of the event
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*	@apiSuccess {DateTime} createAt event creation date
	*	@apiSuccess {DateTime} editedAt event edition date
	*	@apiSuccess {DateTime} deletedAt event delete date
	*	@apiSuccess {Object[]} users list of participants
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.name user full name
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - postEvent - Complete success"
	*		},
	*		"data":
	*		{
	*			"id": 12, "projectId": 21,
	*			"creator": {"id": 15, "fullname": "John Doe"},
	*			"type": {"id": 1, "name": "Event"},
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"createdAt":{"date": "1945-02-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": null,
	*			"deletedAt": null,
	*			"users": []
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.4.6",
	*			"return_message": "Calendar - postEvent - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.4.3",
	*			"return_message": "Calendar - postEvent - Bad id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.4.9",
	*			"return_message": "Calendar - postEvent - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	/**
	* @api {post} /V0.3/event/postevent Post event
	* @apiName postEvent
	* @apiGroup Event
	* @apiDescription Post an event/meeting
	* @apiVersion 0.3.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {int}	[projectId] project's id (if related to a project)
	*	@apiParam {string} title event title
	*	@apiParam {string} description event description
	*	@apiParam {Text} icon Icon of the event
	*	@apiParam {int} typeId event type id
	*	@apiParam {DateTime} begin beginning date & hour of the event
	*	@apiParam {DateTime} end ending date & hour of the event
	* @apiParam {int[]} users array of users id invited to the event
	*
	* @apiParamExample {json} Request-Exemple No project:
	* 	{
	*		"data":
	*		{
	*			"token": "ThisIsMyToken",
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"typeId":  1,
	*			"begin": "1945-06-18 06:00:00",
	*			"end": "1945-06-18 08:00:00",
	*			"users": [1,26,...]
	*		}
	* 	}
	* @apiParamExample {json} Request-Exemple With project:
	* 	{
	*		"data":
	*		{
	*			"token": "ThisIsMyToken",
	*			"projectId": 21,
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"typeId":  1,
	*			"begin":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"end":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"users": []
	*		}
	* 	}
	*
	* @apiSuccess {int} id Event id
	* @apiSuccess {Object} creator creator object
	* @apiSuccess {int} creator.id creator's id
	*	@apiSuccess {string} creator.firstname author firstname
	*	@apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {Object} type Event type object
	* @apiSuccess {int} type.id Event type id
	* @apiSuccess {string} type.name Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {string} description event description
	*	@apiSuccess {Text} icon Icon of the event
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*	@apiSuccess {DateTime} createAt event creation date
	*	@apiSuccess {DateTime} editedAt event edition date
	*	@apiSuccess {DateTime} deletedAt event delete date
	*	@apiSuccess {Object[]} users list of participants
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.firstname user firstname
	*	@apiSuccess {string} users.lastname user lastname
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - postEvent - Complete success"
	*		},
	*		"data":
	*		{
	*			"id": 12, "projectId": 21,
	*			"creator": {"id": 15, "firstname": "John", "lastname": "Doe"},
	*			"type": {"id": 1, "name": "Event"},
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"createdAt":{"date": "1945-02-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": null,
	*			"deletedAt": null,
	*			"users": []
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.4.6",
	*			"return_message": "Calendar - postEvent - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.4.3",
	*			"return_message": "Calendar - postEvent - Bad id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.4.9",
	*			"return_message": "Calendar - postEvent - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	public function postEventAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("token", $content) || !array_key_exists("title", $content) || !array_key_exists("description", $content) || !array_key_exists("icon", $content)
			|| !array_key_exists("typeId", $content) || !array_key_exists("begin", $content)|| !array_key_exists("end", $content) || !array_key_exists("users", $content))
			return $this->setBadRequest("5.4.6", "Calendar", "postEvent", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("5.4.3", "Calendar", "postEvent"));

		$em = $this->getDoctrine()->getManager();
		if (array_key_exists("projectId", $content))
		{
			$project = $em->getRepository("SQLBundle:Project")->find($content->projectId);
			if ($this->checkRoles($user, $content->projectId, "event") < 2)
				return ($this->setNoRightsError("5.3.9", "Calendar", "postEvent"));
		}

		$event = new Event();
		$event->setCreatorUser($user);
		if (array_key_exists("projectId", $content))
			$event->setProjects($project);
		$type = $em->getRepository("SQLBundle:EventType")->find($content->typeId);
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
		foreach ($content->users as $key => $guest) {
			if ($guest != $user->getId()) {
				$newGuest = $em->getRepository('SQLBundle:User')->find($guest);
				if ($newGuest instanceof User) {
					$alreadyAdded = false;
					foreach ($event->getUsers() as $key => $event_value) {
						if ($guest == $event_value->getId())
							$alreadyAdded = true;
					}
					if (!$alreadyAdded) {
						$event->addUser($newGuest);
						$em->flush();
					}
				}
			}
		}

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"firstname" => $value->getFirstname(),
				"lastname" => $value->getLastname(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatarDate()
			);
		}
		$object = $event->objectToArray();
		$object["users"] = $participants;

		return $this->setSuccess("1.5.1", "Calendar", "postEvent", "Complete Success", $object);
	}

	/**
	* @api {put} /V0.2/event/editevent Edit event
	* @apiName editEvent
	* @apiGroup Event
	* @apiDescription Edit an event/meeting
	* @apiVersion 0.2.0
	*
	* @apiParam {int} eventId event id
	* @apiParam {string} token user authentication token
	* @apiParam {int}	[projectId] project's id (if related to a project)
	*	@apiParam {string} title event title
	*	@apiParam {string} description event description
	*	@apiParam {Text} icon Icon of the event
	*	@apiParam {int} typeId event type id
	*	@apiParam {DateTime} begin beginning date & hour of the event
	*	@apiParam {DateTime} end ending date & hour of the event
	*
	* @apiParamExample {json} Request-Exemple No project:
	* 	{
	*		"data":
	*		{
	*			"token": "ThisIsMyToken",
	*			"eventId": 15,
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"typeId":  1,
	*			"begin": "1945-06-18 06:00:00",
	*			"end": "1945-06-18 08:00:00"
	*		}
	* 	}
	* @apiParamExample {json} Request-Exemple With project:
	* 	{
	*		"data":
	*		{
	*			"token": "ThisIsMyToken",
	*			"projectId": 21,
	*			"eventId": 15,
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"typeId":  1,
	*			"begin": "1945-06-18 06:00:00",
	*			"end": "1945-06-18 08:00:00"
	*		}
	* 	}
	*
	* @apiSuccess {int} id Event id
	* @apiSuccess {Object} creator creator object
	* @apiSuccess {int} creator.id creator's id
	* @apiSuccess {String} creator.fullname creator's fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {Object} type Event type object
	* @apiSuccess {int} type.id Event type id
	* @apiSuccess {string} type.name Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {string} description event description
	*	@apiSuccess {Text} icon Icon of the event
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*	@apiSuccess {DateTime} createAt event creation date
	*	@apiSuccess {DateTime} editedAt event edition date
	*	@apiSuccess {DateTime} deletedAt event delete date
	*	@apiSuccess {Object[]} users list of participants
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.name user full name
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - editEvent - Complete success"
	*		},
	*		"data":
	*		{
	*			"id": 12, "projectId": 21,
	*			"creator": {"id": 15, "fullname": "John Doe"},
	*			"type": {"id": 1, "name": "Event"},
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"createdAt":{"date": "1945-02-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": null,
	*			"deletedAt": null,
	*			"users": []
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.5.6",
	*			"return_message": "Calendar - editEvent - Missing Parameter"
  *		}
	* 	}
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.5.3",
	*			"return_message": "Calendar - editEvent - Bad id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.5.9",
	*			"return_message": "Calendar - editEvent - Insufficient Rights"
  *		}
	* 	}
	* @apiErrorExample Bad Parameter: eventId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.5.4",
	*			"return_message": "Calendar - editEvent - Bad Parameter: eventId"
  *		}
	* 	}
	*
	*/
	/**
	* @api {put} /V0.3/event/editevent Edit event
	* @apiName editEvent
	* @apiGroup Event
	* @apiDescription Edit an event/meeting
	* @apiVersion 0.3.0
	*
	* @apiParam {int} eventId event id
	* @apiParam {string} token user authentication token
	* @apiParam {int}	[projectId] project's id (if related to a project)
	*	@apiParam {string} title event title
	*	@apiParam {string} description event description
	*	@apiParam {Text} icon Icon of the event
	*	@apiParam {int} typeId event type id
	*	@apiParam {DateTime} begin beginning date & hour of the event
	*	@apiParam {DateTime} end ending date & hour of the event
	*	@apiParam {int[]} toAddUsers array of users id to add to the event
	*	@apiParam {int[]} toRemoveUsers array of users id to remove of the event
	*
	* @apiParamExample {json} Request-Exemple No project:
	* 	{
	*		"data":
	*		{
	*			"token": "ThisIsMyToken",
	*			"eventId": 15,
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"typeId":  1,
	*			"begin": "1945-06-18 06:00:00",
	*			"end": "1945-06-18 08:00:00",
	*			"toAddUsers": [1,25,...],
	*			"toRemoveUsers": [12,...]
	*		}
	* 	}
	* @apiParamExample {json} Request-Exemple With project:
	* 	{
	*		"data":
	*		{
	*			"token": "ThisIsMyToken",
	*			"projectId": 21,
	*			"eventId": 15,
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"typeId":  1,
	*			"begin": "1945-06-18 06:00:00",
	*			"end": "1945-06-18 08:00:00",
	*			"toAddUsers": [],
	*			"toRemoveUsers": [12,...]
	*		}
	* 	}
	*
	* @apiSuccess {int} id Event id
	* @apiSuccess {Object} creator creator object
	* @apiSuccess {int} creator.id creator's id
	*	@apiSuccess {string} creator.firstname author firstname
	*	@apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {Object} type Event type object
	* @apiSuccess {int} type.id Event type id
	* @apiSuccess {string} type.name Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {string} description event description
	*	@apiSuccess {Text} icon Icon of the event
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*	@apiSuccess {DateTime} createAt event creation date
	*	@apiSuccess {DateTime} editedAt event edition date
	*	@apiSuccess {DateTime} deletedAt event delete date
	*	@apiSuccess {Object[]} users list of participants
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.firstname user firstname
	*	@apiSuccess {string} users.lastname user lastname
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - editEvent - Complete success"
	*		},
	*		"data":
	*		{
	*			"id": 12, "projectId": 21,
	*			"creator": {"id": 15, "firstname": "John", "lastname": "Doe"},
	*			"type": {"id": 1, "name": "Event"},
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"createdAt":{"date": "1945-02-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": null,
	*			"deletedAt": null,
	*			"users": []
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.5.6",
	*			"return_message": "Calendar - editEvent - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.5.3",
	*			"return_message": "Calendar - editEvent - Bad id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.5.9",
	*			"return_message": "Calendar - editEvent - Insufficient Rights"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: eventId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.5.4",
	*			"return_message": "Calendar - editEvent - Bad Parameter: eventId"
	*		}
	* 	}
	*
	*/
	public function editEventAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("token", $content) || !array_key_exists("eventId", $content) || !array_key_exists("title", $content) || !array_key_exists("description", $content)
			|| !array_key_exists("icon", $content) || !array_key_exists("typeId", $content) || !array_key_exists("begin", $content)|| !array_key_exists("end", $content)
			|| !array_key_exists("toAddUsers", $content) || !array_key_exists("toRemoveUsers", $content))
			return $this->setBadRequest("5.5.6", "Calendar", "editEvent", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("5.5.3", "Calendar", "editEvent"));

		$em = $this->getDoctrine()->getManager();
		$event = $em->getRepository("SQLBundle:Event")->find($content->eventId);
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
			$project = $em->getRepository("SQLBundle:Project")->find($content->projectId);
			if ($this->checkRoles($user, $content->projectId, "event") < 2)
				return ($this->setNoRightsError("5.5.9", "Calendar", "editEvent"));
		}

		if (array_key_exists("projectId", $content))
			$event->setProjects($project);
		$type = $em->getRepository("SQLBundle:EventType")->find($content->typeId);
		$event->setEventtypes($type);
		$event->setTitle($content->title);
		$event->setDescription($content->description);
		$event->setIcon($content->icon);
		$event->setBeginDate(new DateTime($content->begin));
		$event->setEndDate(new DateTime($content->end));
		$event->setEditedAt(new DateTime('now'));

		$em->persist($event);
		$em->flush();

		foreach ($content->toRemoveUsers as $key => $guest) {
				$oldGuest = $em->getRepository('SQLBundle:User')->find($guest);
				if ($oldGuest instanceof User) {
					$creator = false;
					if ($guest == $event->getCreatorUser()->getId())
						$creator = true;
					if (!$creator) {
						$event->removeUser($oldGuest);
						$em->flush();
					}
				}
		}

		foreach ($content->toAddUsers as $key => $guest) {
				$newGuest = $em->getRepository('SQLBundle:User')->find($guest);
				if ($newGuest instanceof User) {
					$alreadyAdded = false;
					foreach ($event->getUsers() as $key => $event_value) {
						if ($guest == $event_value->getId())
							$alreadyAdded = true;
					}
					if (!$alreadyAdded) {
						$event->addUser($newGuest);
						$em->flush();
					}
				}
		}

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"firstname" => $value->getFirstname(),
				"lastname" => $value->getLastname(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatarDate()
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
	* @api {delete} /V0.2/event/delevent/:token/:id Delete event
	* @apiName delEvent
	* @apiGroup Event
	* @apiDescription Delete an event/meeting
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id event id
	* @apiParam {string} token user authentication token
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - delEvent - Complete success"
	*		}
	* 	}
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.6.3",
	*			"return_message": "Calendar - delEvent - Bad id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.6.9",
	*			"return_message": "Calendar - delEvent - Insufficient Rights"
  *		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.6.4",
	*			"return_message": "Calendar - delEvent - Bad Parameter: id"
  *		}
	* 	}
	*
	*/
	public function delEventAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.6.3", "Calendar", "delEvent"));

		$em = $this->getDoctrine()->getManager();
		$event = $em->getRepository("SQLBundle:Event")->find($id);
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

	/*
	 * --------------------------------------------------------------------
	 *													GETTERS
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {get} /V0.2/event/gettypes/:token Get event types
	* @apiName getTypes
	* @apiGroup Event
	* @apiDescription Get all event types
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user authentication token
	*
	* @apiSuccess {int} id type id
	* @apiSuccess {string} name type name
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getTypes - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{"id": 1, "name": "Event"},
	*				{"id": 2, "name": "Meeting"},
	*				{"id": 3, "name": "Private"}
	*			]
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.5.3",
	*			"return_message": "Calendar - getTypes - No Data Success"
	*		},
	*		"data":
	*		{
	*			"array": []
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "5.1.3",
	*	    "return_message": "Dashboard - getteamoccupation - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getTypesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.1.3", "Calendar", "getTypes"));

		$em = $this->getDoctrine()->getManager();
		$types = $em->getRepository("SQLBundle:EventType")->findAll();

		if (count($types) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getTypes");

		$types_array = array();
		foreach ($types as $key => $value) {
			$types_array[] = $value->objectToArray();
		}

		return $this->setSuccess("1.5.1", "Calendar", "getTypes", "Complete Success", array("array" => $types_array));
	}

	/**
	* @api {get} /V0.2/event/getevent/:token/:id Get event
	* @apiName getEvent
	* @apiGroup Event
	* @apiDescription Get an event informations
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id event id
	* @apiParam {string} token user authentication token
	*
	* @apiSuccess {int} id Event id
	* @apiSuccess {Object} creator creator object
	* @apiSuccess {int} creator.id creator's id
	*	@apiSuccess {string} creator.firstname author firstname
	*	@apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {Object} type Event type object
	* @apiSuccess {int} type.id Event type id
	* @apiSuccess {string} type.name Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {string} description event description
	*	@apiSuccess {Text} icon Icon of the event
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*	@apiSuccess {DateTime} createAt event creation date
	*	@apiSuccess {DateTime} editedAt event edition date
	*	@apiSuccess {DateTime} deletedAt event delete date
	*	@apiSuccess {Object[]} users list of participants
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.firstname user firstname
	*	@apiSuccess {string} users.lastname user lastname
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {date} users.avatar user avatar last modif date
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getEvent - Complete success"
	*		},
	*		"data":
	*		{
	*			"id": 12, "projectId": 21,
	*			"creator": {"id": 15, "firstname": "John", "lastname": "Doe"},
	*			"type": {"id": 1, "name": "Event"},
	*			"title": "Brainstorming",
	*			"description": "blablabla",
	*			"icon": "DATA",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"createdAt":{"date": "1945-02-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": null,
	*			"deletedAt": null,
	*			"users": [
	*				{"id": 95, "firsname": "John", "lastname": "Doe", "email": "john.doe@wanadoo.fr", "avatar": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}},
	*				{"id": 96, "firsname": "Joanne", "lastname": "Doe", "email": "joanne.doe@wanadoo.fr", "avatar": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}}
	*			]
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "5.2.3",
	*	    "return_message": "Calendar - getEvent - Bad ID"
	*	  }
	*	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.2.4",
	*			"return_message": "Calendar - getEvent - Bad Parameter: id"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.2.9",
	*			"return_message": "Calendar - getEvent - Insufficient Rights"
  *		}
	* 	}
	*
	*/
	public function getEventAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.2.3", "Calendar", "getEvent"));

		$em = $this->getDoctrine()->getManager();
		$event = $em->getRepository("SQLBundle:Event")->find($id);
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
				"firstname" => $value->getFirstname(),
				"lastname" => $value->getLastname(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatarDate()
			);
		}
		$object = $event->objectToArray();
		$object["users"] = $participants;

		return $this->setSuccess("1.5.1", "Calendar", "getEvent", "Complete Success", $object);
	}


	/*
	 * --------------------------------------------------------------------
	 *														USERS
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {put} /V0.2/event/setparticipants Set participants
	* @apiName setParticipants
	* @apiGroup Event
	* @apiDescription Add/remove users to the event
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {int} eventId event id
	* @apiParam {int[]} toAdd list of users' id to add
	* @apiParam {int[]} toRemove list of users' id to remove
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
	* 		"token": "ThisIsMyToken",
	* 		"eventId": 1,
	* 		"toAdd": [1, 15, 6],
	* 		"toRemove": []
	* 	}
	*   }
	*
	* @apiSuccess {int} id Event id
	* @apiSuccess {Object} creator creator object
	* @apiSuccess {int} creator.id creator's id
	*	@apiSuccess {string} creator.firstname author firstname
	*	@apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {Object} type Event type object
	* @apiSuccess {int} type.id Event type id
	* @apiSuccess {string} type.name Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {string} description event description
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*	@apiSuccess {DateTime} createAt event creation date
	*	@apiSuccess {DateTime} editedAt event edition date
	*	@apiSuccess {DateTime} deletedAt event delete date
	*	@apiSuccess {Object[]} users list of participants
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.firstname user firstname
	*	@apiSuccess {string} users.lastname user lastname
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {date} users.avatar user avatar last modif date
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - setParticipants - Complete success"
	*		},
	*		"data":
	*		{
	*			"id": 12, "projectId": 21,
	*			"creator": {"id": 15, "firstname": "John", "lastname": "Doe"},
	*			"type": {"id": 1, "name": "Event"},
	*			"title": "Brainstorming", "description": "blablabla",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"createdAt":{"date": "1945-02-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": null,
	*			"deletedAt": null,
	*			"users": [
	*				{"id": 95, "firstname": "John", "lastname": "Doe", "email": "john.doe@wanadoo.fr", "avatar": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}},
	*				{"id": 96, "firstname": "Joanne", "lastname": "Doe", "email": "joanne.doe@wanadoo.fr", "avatar": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}}
	*			]
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.3.6",
	*			"return_message": "Calendar - setParticipants - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.3.3",
	*			"return_message": "Calendar - setParticipants - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: eventId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.3.4",
	*			"return_message": "Calendar - setParticipants - Bad Parameter: eventId"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.3.9",
	*			"return_message": "Calendar - setParticipants - Insufficient Rights"
	*		}
	* 	}
	* @apiErrorExample Already in Database
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.3.7",
	*			"return_message": "Calendar - setParticipants - Already in Database"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: Can't remove event creator
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "5.3.4",
	*			"return_message": "Calendar - setParticipants - Bad Parameter: Can't remove event creator"
	*		}
	* 	}
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

		$em = $this->getDoctrine()->getManager();
		$event = $em->getRepository("SQLBundle:Event")->find($content->eventId);
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
			$toAddUser = $em->getRepository("SQLBundle:User")->find($value);
			if ($toAddUser instanceof User)
			{
				foreach ($event->getUsers() as $key => $event_value) {
					if ($toAddUser->getId() == $event_value->getId())
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
			$toRemoveUser = $em->getRepository("SQLBundle:User")->find($value);
			if ($toRemoveUser instanceof User)
			{
				if ($toRemoveUser->getId() == $event->getCreatorUser()->getId())
					return $this->setBadRequest("5.3.7", "Calendar", "setParticipants", "Bad Parameter: Can't remove event creator");

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
				"firstname" => $value->getFirstname(),
				"lastname" => $value->getLastname(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatarDate()
			);
		}
		$object = $event->objectToArray();
		$object["users"] = $participants;

		return $this->setSuccess("1.5.1", "Calendar", "setParticipants", "Complete Success", $object);
	}

}
