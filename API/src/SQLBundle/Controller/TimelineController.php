<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use SQLBundle\Entity\Timeline;
use SQLBundle\Entity\TimelineType;
use SQLBundle\Entity\TimelineMessage;
use SQLBundle\Entity\TimelineComment;
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
*  @IgnoreAnnotation("apiDescription")
*  @IgnoreAnnotation("apiHeader")
*  @IgnoreAnnotation("apiHeaderExample")
*/
class TimelineController extends RolesAndTokenVerificationController
{
	/**
	* @api {post} /0.3/timeline/message/:id Post a new message
	* @apiName postMessage
	* @apiGroup Timeline
	* @apiDescription Post a new message for the given timeline, to post message see postMessage request
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id Id of the timeline
	* @apiParam {String} title Title of the message
	* @apiParam {String} message Message to post on the timeline
	*
	* @apiParamExample {json} Request-Minimum-Example:
	* 	{
	*		"data": {
	*			"title": "Project delayed",
	*			"message": "Hi, i think we should delay the delivery date of the project, what do you think about it?"
	*		}
	* 	}
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} timelineId Id of the timeline
	* @apiSuccess {String} title Message title
	* @apiSuccess {String} message Message content
	* @apiSuccess {string} createdAt Message creation date
	* @apiSuccess {string} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Message-Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - postmessage - Complete Success"
	*		},
	*		"data": {
	*			"id": "154",
	*			"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*			"timelineId": 14,
	*			"title": "hello",
	*			"message": "What about a meeting tomorrow morning ?",
	*			"createdAt": "1945-06-18 06:00:00",
	*			"editedAt": null
	*		}
	* 	}
	*
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "11.2.3",
	*			"return_message": "Timeline - postmessage - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "11.2.9",
	*			"return_message": "Timeline - postmessage - Insufficient Rights"
	*		}
	* 	}
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.2.6",
	*			"return_message": "Timeline - postmessage - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.2.4",
	*			"return_message": "Timeline - postmessage - Bad Parameter: id"
	*		}
	* 	}
	*/
	/**
	* @api {post} /V0.2/timeline/postmessage/:id Post a new message or comment
	* @apiName postMessage
	* @apiGroup Timeline
	* @apiDescription Post a new message or a comment for the given timeline
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id Id of the timeline
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} title Title of the message
	* @apiParam {String} message Message to post on the timeline
	* @apiParam {int} [commentedId] (required only for comments) Id of the message you want to comment
	*
	* @apiParamExample {json} Request-Minimum-Example:
	* 	{
	*		"data": {
	*			"token": "13135",
	*			"title": "Project delayed",
	*			"message": "Hi, i think we should delay the delivery date of the project, what do you think about it?"
	*		}
	* 	}
	*
	* @apiParamExample {json} Request-Full-Example:
	* 	{
	*		"data": {
	*			"token": "13135",
	*			"title": "RE: Project delayed",
	*			"message": "Like you said previously, I agree that the delivery date should be later, because of the customer wishes we have a lot more to do and the same deadline.",
	*			"commentedId": 1
	*		}
	* 	}
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.fullname author name
	* @apiSuccess {int} timelineId Id of the timeline
	* @apiSuccess {String} title Message title
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId Id of the parent message
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Message-Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - postmessage - Complete Success"
  	*		},
	*		"data": {
	*			"id": "154",
	*			"creator": {"id": 25, "fullname": "John Doe"},
	*			"timelineId": 14,
	*			"title": "hello",
	*			"message": "What about a meeting tomorrow morning ?",
	*			"parentId": null,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": null
	*		}
	* 	}
	*
	* @apiSuccessExample {json} Comment-Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - postmessage - Complete Success"
  	*		},
	*		"data": {
	*			"id": "169",
	*			"creator": {"id": 25, "fullname": "John Doe"},
	*			"timelineId": 14,
	*			"title": "RE: hello",
	*			"message": "Why not, i'am completly free tomorrow",
	*			"parentId": 154,
	*			"createdAt": {"date": "1945-06-18 10:53:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": null
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "11.2.3",
	*			"return_message": "Timeline - postmessage - Bad ID"
  	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "11.2.9",
	*			"return_message": "Timeline - postmessage - Insufficient Rights"
  	*		}
	* 	}
	*/
	public function postMessageAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("title", $content) || !array_key_exists("message", $content))
			return $this->setBadRequest("11.2.6", "Timeline", "postmessage", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.2.3", "Timeline", "postmessage"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('SQLBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.2.4", "Timeline", "postmessage", "Bad Parameter: id");

		$type = $em->getRepository('SQLBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.2.9", "Timeline", "postmessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.2.9", "Timeline", "postmessage"));
		}

		$message = new TimelineMessage();
		$message->setCreator($user);
		$message->setTitle($content->title);
		$message->setMessage($content->message);
		$message->setTimelineId($timeline->getId());
		$message->setTimelines($timeline);
		$message->setCreatedAt(new DateTime('now'));

		$em->persist($message);
		$em->flush();

		// Notifications
		$class = new NotificationController();

		$mdata['mtitle'] = "Timeline - New message";
		$mdata['mdesc'] = "There is a new message on the timeline ".$timeline->getName();

		$wdata['type'] = "Timeline";
		$wdata['targetId'] = $message->getId();
		$wdata['message'] = "There is a new message on the timeline ".$timeline->getName();

		$projectUsers = $timeline->getProjects()->getUsers();
		foreach ($projectUsers as $u) {
			$userNotif[] = $u->getId();
		}

		$class->pushNotification($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.11.1", "Timeline", "postmessage", "Complete Success", $message->objectToArray());
	}

	/**
	* @api {put} /0.3/timeline/message/:id/:messageId Edit a message
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiDescription Edit a given message
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {int} messageId message's id
	* @apiParam {String} title message title
	* @apiParam {String} message message to post
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"data": {
	*			"messageId": 15,
	*			"title": "Hello there!",
	*			"message": "Hi, i think we should delay the delivery date of the project, what do you think about it?"
	*		}
	* 	}
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} title Message title
	* @apiSuccess {String} message Message content
	* @apiSuccess {string} createdAt Message creation date
	* @apiSuccess {string} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - editmessage - Complete Success"
  	*		},
	*		"data": {
	*			"id": "154",
	*			"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*			"timelineId": 14,
	*			"title": "hello",
	*			"message": "What about a meeting tomorrow morning or next monday ?",
	*			"createdAt": "1945-06-18 06:00:00",
	*			"editedAt": "1945-06-18 07:00:00"
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "11.3.3",
	*			"return_message": "Timeline - editmessage - Bad Token"
  	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "11.3.9",
	*			"return_message": "Timeline - editmessage - Insufficient Rights"
	*		}
	* 	}
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.3.6",
	*			"return_message": "Timeline - editmessage - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.3.4",
	*			"return_message": "Timeline - editmessage - Bad Parameter: id"
	*		}
	* 	}
	*/
	/**
	* @api {put} /V0.2/timeline/editmessage/:id Edit a message or comment
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiDescription Edit a given message or comment
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId message's id
	* @apiParam {String} title message title
	* @apiParam {String} message message to post
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"data": {
	*			"token": "13135",
	*			"messageId": 15,
	*			"title": "Hello there!",
	*			"message": "Hi, i think we should delay the delivery date of the project, what do you think about it?"
	*		}
	* 	}
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.fullname author name
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} title Message title
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - editmessage - Complete Success"
  	*		},
	*		"data": {
	*			"id": "154",
	*			"creator": {"id": 25, "fullname": "John Doe"},
	*			"timelineId": 14,
	*			"title": "hello",
	*			"message": "What about a meeting tomorrow morning or next monday ?",
	*			"parentId": 12,
	*			"createdAt": {
	*				"date": "1945-06-18 06:00:00",
	*				"timezone_type": 3,
	*				"timezone": "Europe\/Paris"
	*			},
	*			"editedAt": {
	*				"date": "1945-06-18 07:00:00",
	*				"timezone_type": 3,
	*				"timezone": "Europe\/Paris"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "11.3.3",
	*			"return_message": "Timeline - editmessage - Bad ID"
  	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "11.3.9",
	*			"return_message": "Timeline - editmessage - Insufficient Rights"
  	*		}
	* 	}
	*/
	public function editMessageAction(Request $request, $id, $messageId)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("title", $content) || !array_key_exists("message", $content))
			return $this->setBadRequest("11.3.6", "Timeline", "editmessage", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.3.3", "Timeline", "editmessage"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('SQLBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.3.4", "Timeline", "editmessage", "Bad Parameter: id");

		$type = $em->getRepository('SQLBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.3.9", "Timeline", "editmessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.3.9", "Timeline", "editmessage"));
		}

		$message = $em->getRepository('SQLBundle:TimelineMessage')->find($messageId);
		$message->setTitle($content->title);
		$message->setMessage($content->message);
		$message->setEditedAt(new DateTime('now'));

		$em->persist($message);
		$em->flush();

		return $this->setSuccess("1.11.1", "Timeline", "editmessage", "Complete Success", $message->objectToArray());
	}

	/**
	* @api {delete} /0.3/timeline/message/:id/:messageId Delete a message and his comments
	* @apiName DeleteMessage
	* @apiGroup Timeline
	* @apiDescription Delete the given message and his comments
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id Id of the timeline
	* @apiParam {int} messageId Id of the message
	*
	* @apiSuccess {Number} id Id of the message archived
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - archivemessage - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id" : 3
	*		}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.6.3",
	*			"return_message": "Timeline - archivemessage - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "11.6.9",
	*			"return_message": "Timeline - archivemessage - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "11.6.4",
	*			"return_message": "Timeline - archivemessage - Bad Parameter: id"
	*		}
	*	}
	*/
	/**
	* @api {delete} /V0.2/timeline/archivemessage/:token/:id/:messageId Archive a comment or a message and his comments
	* @apiName ArchiveMessage
	* @apiGroup Timeline
	* @apiDescription Archive the given message and his comments or just a given comment. This request no longer exists. See [DeleteMessage](0.3/#api-Timeline-DeleteMessage)
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id Id of the timeline
	* @apiParam {String} token Client authentification token
	* @apiParam {int} messageId Id of the message
	*
	* @apiSuccess {Number} id Id of the message archived
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - archivemessage - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id" : 3
	*		}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.6.3",
	*			"return_message": "Timeline - archivemessage - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "11.6.9",
	*			"return_message": "Timeline - archivemessage - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "11.6.4",
	*			"return_message": "Timeline - archivemessage - Bad Parameter: id"
	*		}
	*	}
	*/
	public function archiveMessageAction(Request $request, $id, $messageId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.6.3", "Timeline", "archivemessage"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('SQLBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.6.4", "Timeline", "archivemessage", "Bad Parameter: id");

		$type = $em->getRepository('SQLBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.6.9", "Timeline", "archivemessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.6.9", "Timeline", "archivemessage"));
		}

		$message = $em->getRepository('SQLBundle:TimelineMessage')->find($messageId);
		$em->remove($message);
		$em->flush();

		return $this->setSuccess("1.11.1", "Timeline", "archivemessage", "Complete Success", array("id" => $messageId));
	}

	/*
	 * --------------------------------------------------------------------
	 *											TIMELINE/MESSAGE GETTERS
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {get} /0.3/timelines/:id List project timelines
	* @apiName getTimelines
	* @apiGroup Timeline
	* @apiDescription List all the timelines of a project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the project
	*
	* @apiSuccess {Object[]} array Array of timeline informations
	* @apiSuccess {int} array.id Timeline id
	* @apiSuccess {String} array.name Timeline name
	* @apiSuccess {int} array.projectId project id
	* @apiSuccess {int} array.typeId Timeline type id
	* @apiSuccess {String} array.typeName Timeline type name
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - gettimelines - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{
	*					"id": 2,
	*					"typeId": 1,
	*					"projectId": 12,
	*					"name": "Customer timeline project XYZ",
	*					"typeName": "customerTimeline"
	*				},
	*				{
	*					"id": 3,
	*					"typeId": 2,
	*					"projectId": 12,
	*					"name": "Team timeline project XYZ",
	*					"typeName": "teamTimeline"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.11.3",
	*			"return_message": "Timeline - gettimelines - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.1.3",
	*			"return_message": "Timeline - gettimelines - Bad Token"
	*		}
	*	}
	*/
	/**
	* @api {get} /V0.2/timeline/gettimelines/:token/:id List project timelines
	* @apiName getTimelines
	* @apiGroup Timeline
	* @apiDescription List all the timelines of a project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id id of the project
	*
	* @apiSuccess {Object[]} array Array of timeline informations
	* @apiSuccess {int} array.id Timeline id
	* @apiSuccess {String} array.name Timeline name
	* @apiSuccess {int} array.projectId project id
	* @apiSuccess {int} array.typeId Timeline type id
	* @apiSuccess {String} array.typeName Timeline type name
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - gettimelines - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{
	*					"id": 2,
	*					"typeId": 1,
	*					"projectId": 12,
	*					"name": "Customer timeline project XYZ",
	*					"typeName": "customerTimeline"
	*				},
	*				{
	*					"id": 3,
	*					"typeId": 2,
	*					"projectId": 12,
	*					"name": "Team timeline project XYZ",
	*					"typeName": "teamTimeline"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.11.3",
	*			"return_message": "Timeline - gettimelines - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.1.3",
	*			"return_message": "Timeline - gettimelines - Bad ID"
	*		}
	*	}
	*/
	public function getTimelinesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.1.3", "Task", "gettimelines"));

		$em = $this->getDoctrine()->getManager();
		$timelines = $em->getRepository('SQLBundle:Timeline')->findBy(array("projectId" => $id));

		$timeline_array = array();
		foreach ($timelines as $key => $value) {
			$type = $em->getRepository('SQLBundle:TimelineType')->find($value->getTypeId());
			$tmp = $value->objectToArray();
			$tmp["typeName"] = $type->getName();
			$timeline_array[] = $tmp;
		}

		if (count($timeline_array) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "gettimelines");

		return $this->setSuccess("1.11.1", "Timeline", "gettimelines", "Complete Success", array("array" => $timeline_array));
	}

	/**
	* @api {get} /0.3/timeline/messages/:id Get timeline's messages
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiDescription Get all the messages from a timeline without comments
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the timeline
	*
	* @apiSuccess {Object[]} array Array of all the timeline's messages
	* @apiSuccess {int} array.id Message id
	* @apiSuccess {Object} array.creator author
	* @apiSuccess {int} array.creator.id author id
	* @apiSuccess {string} array.creator.firstname author firstname
	* @apiSuccess {string} array.creator.lastname author lastname
	* @apiSuccess {int} array.timelineId Id of the timeline
	* @apiSuccess {String} array.title Message title
	* @apiSuccess {String} array.message Message content
	* @apiSuccess {string} array.createdAt Message creation date
	* @apiSuccess {string} array.editedAt Message edition date
	* @apiSuccess {String} array.nbComment numbe of comments
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - getmessages - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": "154",
	*					"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*					"timelineId": 14,
	*					"title": "hello",
	*					"message": "What about a meeting tomorrow morning ?",
	*					"createdAt": "1945-06-18 06:00:00",
	*					"editedAt": "1945-06-18 06:00:00",
	*					"nbComment": "6"
	*				},
	*				{
	*					"id": "158",
	*					"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*					"timelineId": 14,
	*					"title": "hello",
	*					"message": "Ok, let's do this !",
	*					"createdAt": "1945-06-18 06:00:00",
	*					"editedAt": "1945-06-18 06:00:00",
	*					"nbComment": "0"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.11.3",
	*			"return_message": "Timeline - gettimelines - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "11.4.3",
	*			"return_message": "Timeline - getmessages - Bad Token"
  	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "11.4.9",
	*			"return_message": "Timeline - getmessages - Insufficient Rights"
  	*		}
	* 	}
	*/
	/**
	* @api {get} /V0.2/timeline/getmessages/:token/:id Get all messages from a timeline except comments
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiDescription Get all the messages but not the comments from a timeline
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} array Array of all the timeline's messages
	* @apiSuccess {int} array.id Message id
	* @apiSuccess {Object} array.creator author
	* @apiSuccess {int} array.creator.id author id
	* @apiSuccess {string} array.creator.fullname author name
	* @apiSuccess {int} array.timelineId Id of the timeline
	* @apiSuccess {String} array.title Message title
	* @apiSuccess {String} array.message Message content
 	* @apiSuccess {int} array.parentId Parent message id if it's a comment
	* @apiSuccess {DateTime} array.createdAt Message creation date
	* @apiSuccess {DateTime} array.editedAt Message edition date
	* @apiSuccess {DateTime} array.deletedAt Message deletion date
	* @apiSuccess {String} array.nbComment numbe of comments
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - getmessages - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": "154",
	*					"creator": {"id": 25, "fullname": "John Doe"},
	*					"timelineId": 14,
	*					"title": "hello",
	*					"message": "What about a meeting tomorrow morning ?",
	*					"parentId": null,
	*					"createdAt": {
	*						"date": "1945-06-18 06:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"editedAt": {
	*						"date": "1945-06-18 06:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"deletedAt": null,
	*					"nbComment": "6"
	*				},
	*				{
	*					"id": "158",
	*					"creator": {"id": 25, "fullname": "John Doe"},
	*					"timelineId": 14,
	*					"title": "hello",
	*					"message": "Ok, let's do this !",
	*					"parentId": null,
	*					"createdAt": {
	*						"date": "1945-06-18 06:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"editedAt": {
	*						"date": "1945-06-18 06:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"deletedAt": null,
	*					"nbComment": "0"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.11.3",
	*			"return_message": "Timeline - gettimelines - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "11.4.3",
	*			"return_message": "Timeline - getmessages - Bad ID"
  	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "11.4.9",
	*			"return_message": "Timeline - getmessages - Insufficient Rights"
  	*		}
	* 	}
	*/
	public function getMessagesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.4.3", "Timeline", "getmessages"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('SQLBundle:Timeline')->find($id);
		$type = $em->getRepository('SQLBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.4.9", "Timeline", "getmessages"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.4.9", "Timeline", "getmessages"));
		}

		$messages = $em->getRepository('SQLBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId()), array("createdAt" => "DESC"));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {

			$elem = $value->objectToArray();
			$elem['nbComment'] = count($value->getComments());
			$timelineMessages[] = $elem;
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getmessages");

		return $this->setSuccess("1.11.1", "Timeline", "getmessages", "Complete Success", array("array" => $timelineMessages));
	}

	/**
	* @api {get} /0.3/timeline/messages/:id/:offset/:limit Get X messages from offset Y
	* @apiName getLastMessages
	* @apiGroup Timeline
	* @apiDescription Get the last X messages from offset Y of the given timeline
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id Id of the timeline
	* @apiParam {int} offset Message offset from where to get the messages (start to 0)
	* @apiParam {int} limit Number max of messages to get
	*
	* @apiSuccess {Object[]} array Array of all the timeline's messages
	* @apiSuccess {int} array.id Message id
	* @apiSuccess {Object} array.creator author
	* @apiSuccess {int} array.creator.id author id
	* @apiSuccess {string} array.creator.firstname author firstname
	* @apiSuccess {string} array.creator.lastname author lastname
	* @apiSuccess {int} array.timelineId timeline id
	* @apiSuccess {String} array.title Message title
	* @apiSuccess {String} array.message Message content
	* @apiSuccess {string} array.createdAt Message creation date
	* @apiSuccess {string} array.editedAt Message edition date
	* @apiSuccess {String} array.nbComment number of comments
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - getlastmessages - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": "154",
	*					"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*					"timelineId": 14,
	*					"title": "hello",
	*					"message": "What about a meeting tomorrow morning ?",
	*					"createdAt": "1945-06-18 06:00:00",
	*					"editedAt": "1945-06-18 06:00:00",
	*					"nbComment": "0"
	*				},
	*				{
	*					"id": "158",
	*					"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*					"timelineId": 14,
	*					"title": "hello",
	*					"message": "Ok, let's do this !",
	*					"createdAt": "1945-06-18 06:00:00",
	*					"editedAt": "1945-06-18 06:00:00",
	*					"nbComment": "0"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.11.3",
	*			"return_message": "Timeline - getlastmessages - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.5.3",
	*			"return_message": "Timeline - getlastmessages - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "11.5.9",
	*			"return_message": "Timeline - getlastmessages - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "11.5.4",
	*			"return_message": "Timeline - getlastmessages - Bad Parameter: id"
	*		}
	*	}
	*/
	/**
	* @api {get} /V0.2/timeline/getlastmessages/:token/:id/:offset/:limit Get X last message from offset Y
	* @apiName getLastMessages
	* @apiGroup Timeline
	* @apiDescription Get the last X messages from offset Y of the given timeline
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id Id of the timeline
	* @apiParam {String} token Client authentification token
	* @apiParam {int} offset Message offset from where to get the messages (start to 0)
	* @apiParam {int} limit Number max of messages to get
	*
	* @apiSuccess {Object[]} array Array of all the timeline's messages
	* @apiSuccess {int} array.id Message id
	* @apiSuccess {Object} array.creator author
	* @apiSuccess {int} array.creator.id author id
	* @apiSuccess {string} array.creator.fullname author name
	* @apiSuccess {int} array.timelineId timeline id
	* @apiSuccess {String} array.title Message title
	* @apiSuccess {String} array.message Message content
	* @apiSuccess {int} array.parentId parent message id
	* @apiSuccess {DateTime} array.createdAt Message creation date
	* @apiSuccess {DateTime} array.editedAt Message edition date
	* @apiSuccess {DateTime} array.deletedAt Message deletion date
	* @apiSuccess {String} array.nbComment numbe of comments
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - getlastmessages - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": "154",
	*					"creator": {"id": 25, "fullname": "John Doe"},
	*					"timelineId": 14,
	*					"title": "hello",
	*					"message": "What about a meeting tomorrow morning ?",
	*					"parentId": null,
	*					"createdAt": {
	*						"date": "1945-06-18 06:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"editedAt": {
	*						"date": "1945-06-18 06:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"deletedAt": null,
	*					"nbComment": "0"
	*				},
	*				{
	*					"id": "158",
	*					"creator": {"id": 25, "fullname": "John Doe"},
	*					"timelineId": 14,
	*					"title": "hello",
	*					"message": "Ok, let's do this !",
	*					"parentId": null,
	*					"createdAt": {
	*						"date": "1945-06-18 06:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"
	*					},
	*					"editedAt": {
	*						"date": "1945-06-18 06:00:00",
	*						"timezone_type": 3,
	*						"timezone": "Europe\/Paris"},
	*					"deletedAt": null,
	*					"nbComment": "0"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.11.3",
	*			"return_message": "Timeline - getlastmessages - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.5.3",
	*			"return_message": "Timeline - getlastmessages - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "11.5.9",
	*			"return_message": "Timeline - getlastmessages - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "11.5.4",
	*			"return_message": "Timeline - getlastmessages - Bad Parameter: id"
	*		}
	*	}
	*/
	public function getLastMessagesAction(Request $request, $id, $offset, $limit)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.5.3", "Timeline", "getlastmessages"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('SQLBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.5.4", "Timeline", "getlastmessages", "Bad Parameter: id");

		$type = $em->getRepository('SQLBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.5.9", "Timeline", "getlastmessages"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.5.9", "Timeline", "getlastmessages"));
		}

		$messages = $em->getRepository('SQLBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId()), array("createdAt" => "DESC"), $limit, $offset);
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$elem = $value->objectToArray();
			$elem['nbComment'] = count($value->getComments());
			$timelineMessages[] = $elem;
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getlastmessages");

		return $this->setSuccess("1.11.1", "Timeline", "getlastmessages", "Complete Success", array("array" => $timelineMessages));
	}

	/*
	 * --------------------------------------------------------------------
	 *														COMMENTS
	 * --------------------------------------------------------------------
	*/
	/**
	* @api {get} /0.3/timeline/message/comments/:id/:messageId Get message's comments
	* @apiName getComments
	* @apiGroup Timeline
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {int} messageId commented message id
	*
	* @apiSuccess {int} id Comment id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {String} comment Comment content
	* @apiSuccess {string} createdAt Comment creation date
	* @apiSuccess {string} editedAt Comment edition datezz
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - getComments - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*			{"id": "154",
	*				"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*				"parentId": 150,
	*				"comment": "What about a meeting tomorrow morning ?",
	*				"createdAt": "1945-06-18 06:00:00",
	*				"editedAt": "1945-06-18 06:00:00"},
	*			{"id": "158",
	*				"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*				"parentId": 150,
	*				"comment": "What about a meeting tomorrow morning ?",
	*				"createdAt": "1945-06-18 06:00:00",
	*				"editedAt": "1945-06-18 06:00:00"},
	*		 	...
	*		]
	*		}
	* 	}
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.11.3",
	*			"return_message": "Timeline - getComments - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.6.3",
	*			"return_message": "Timeline - getComments - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "11.6.9",
	*			"return_message": "Timeline - getComments - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "11.6.4",
	*			"return_message": "Timeline - getComments - Bad Parameter: id"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: messageId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "11.6.4",
	*			"return_message": "Timeline - getComments - Bad Parameter: messageId"
	*		}
	*	}
	*/
	/**
	* @api {get} /V0.2/timeline/getcomments/:token/:id/:message Get comments of a message
	* @apiName getComments
	* @apiGroup Timeline
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id id of the timeline
	* @apiParam {int} message commented message id
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.fullname author name
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} title Message title
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message edition date
	* @apiSuccess {DateTime} deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - getComments - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*			{"id": "154","creator": {"id": 25, "fullname": "John Doe"}, "timelineId": 14, "parentId": 150,
	*				"title": "hello", "message": "What about a meeting tomorrow morning ?",
	*				"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*				"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*				"deletedAt": null},
	*			{"id": "158","creator": {"id": 25, "fullname": "John Doe"}, "timelineId": 14, "parentId": 150,
	*				"title": "hello", "message": "Ok, let's do this !",
	*				"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*				"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*				"deletedAt": null},
	*		 	...
	*		]
	*		}
	* 	}
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.11.3",
	*			"return_message": "Timeline - getComments - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.6.3",
	*			"return_message": "Timeline - getComments - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "11.6.9",
	*			"return_message": "Timeline - getComments - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "11.6.4",
	*			"return_message": "Timeline - getComments - Bad Parameter: id"
	*		}
	*	}
	*/
	public function getCommentsAction(Request $request, $id, $messageId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.6.3", "Timeline", "getComments"));
		$em = $this->getDoctrine()->getManager();

		$timeline = $em->getRepository('SQLBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.6.4", "Timeline", "getComments", "Bad Parameter: id");

		$type = $em->getRepository('SQLBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError("11.6.9", "Timeline", "getComments"));
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError("11.6.9", "Timeline", "getComments"));
		}

		$message = $em->getRepository('SQLBundle:TimelineMessage')->find($messageId);
		if (!($message instanceof TimelineMessage))
			return $this->setBadRequest("11.6.4", "Timeline", "getComments", "Bad Parameter: messageId");

		$comments = $em->getRepository('SQLBundle:TimelineComment')->findBy(array("messages" => $message), array("createdAt" => "ASC"));
		$commentsArray = array();
		foreach ($comments as $key => $value) {
			$commentsArray[] = $value->objectToArray();
		}

		if (count($commentsArray) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getComments");

		return $this->setSuccess("1.11.1", "Timeline", "getComments", "Complete Success", array("array" => $commentsArray));
	}

	/**
	* @api {post} /0.3/timeline/comment/:id Post comment
	* @apiName postComment
	* @apiGroup Timeline
	* @apiDescription Post a new comment for the given message
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id Id of the timeline
	* @apiParam {String} comment Comment to post
	* @apiParam {int} commentedId Id of the message you want to comment
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"data": {
	*			"token": "ThisIsMyToken",
	*			"comment": "Like you said previously, I agree that the delivery date should be later, because of the customer wishes we have a lot more to do and the same deadline.",
	*			"commentedId": 1
	*		}
	* 	}
	*
	* @apiSuccess {int} id Comment id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} parentId Id of the parent message
	* @apiSuccess {String} comment Comment content
	* @apiSuccess {string} createdAt Comment creation date
	* @apiSuccess {string} editedAt Comment last modification date
	*
	* @apiSuccessExample {json} Message-Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - postcomment - Complete Success"
  	*		},
	*		"data": {
	*			"id": "154",
	*			"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*			"parentId": 10,
	*			"comment": "What about a meeting tomorrow morning ?",
	*			"createdAt": "1945-06-18 06:00:00",
	*			"editedAt": null
	*		}
	* 	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "11.8.3",
	*			"return_message": "Timeline - postcomment - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.8.6",
	*			"return_message": "Timeline - postcomment - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.8.4",
	*			"return_message": "Timeline - postcomment - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: commentedId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.8.4",
	*			"return_message": "Timeline - postcomment - Bad Parameter: commentedId"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "11.8.9",
	*			"return_message": "Timeline - postcomment - Insufficient Rights"
  	*		}
	* 	}
	*/
	public function postCommentAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->getDoctrine()->getManager();

		if (!array_key_exists("comment", $content) || !array_key_exists("commentedId", $content))
			return $this->setBadRequest("11.8.6", "Timeline", "postcomment", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.8.3", "Timeline", "postcomment"));

		$timeline = $em->getRepository('SQLBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.8.4", "Timeline", "postcomment", "Bad Parameter: id");

		$type = $em->getRepository('SQLBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.8.9", "Timeline", "postcomment"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.8.9", "Timeline", "postcomment"));
		}

		$message = $em->getRepository('SQLBundle:TimelineMessage')->find($content->commentedId);
		if (!($message instanceof TimelineMessage))
			return $this->setBadRequest("11.8.4", "Timeline", "postcomment", "Bad Parameter: commentedId");

		$comment = new TimelineComment();
		$comment->setCreator($user);
		$comment->setComment($content->comment);
		$comment->setMessages($message);
		$comment->setCreatedAt(new DateTime('now'));

		$em->persist($comment);
		$em->flush();

		return $this->setCreated("1.11.1", "Timeline", "postcomment", "Complete Success", $comment->objectToArray());
	}

	/**
	* @api {put} /0.3/timeline/comment/:id Edit comment
	* @apiName editComment
	* @apiGroup Timeline
	* @apiDescription Edit a given comment
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {int} commentId comment's id
	* @apiParam {String} comment comment content
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"data": {
	*			"commentId": 10,
	*			"comment": "Hi, I think we should delay the delivery date of the project, what do you think about it?"
	*		}
	* 	}
	*
	* @apiSuccess {int} id Comment id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {String} comment Comment content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {string} createdAt Comment creation date
	* @apiSuccess {string} editedAt Comment last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - editcomment - Complete Success"
	*		},
	*		"data": {
	*			"id": "154",
	*			"creator": {"id": 25, "firstname": "John", "lastname": "Doe"},
	*			"comment": "What about a meeting tomorrow morning or next monday ?",
	*			"parentId": 12,
	*			"createdAt": "1945-06-18 06:00:00",
	*			"editedAt": "1945-06-18 07:00:00"
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "11.9.3",
	*			"return_message": "Timeline - editcomment - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "11.9.9",
	*			"return_message": "Timeline - editcomment - Insufficient Rights"
	*		}
	* 	}
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.9.6",
	*			"return_message": "Timeline - editcomment - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.9.4",
	*			"return_message": "Timeline - editcomment - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: commentId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "11.9.4",
	*			"return_message": "Timeline - editcomment - Bad Parameter: commentId"
	*		}
	* 	}
	*/
	public function editCommentAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->getDoctrine()->getManager();

		if (!array_key_exists("comment", $content) || !array_key_exists("commentId", $content))
			return $this->setBadRequest("11.9.6", "Timeline", "editcomment", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.3.3", "Timeline", "editcomment"));

		$timeline = $em->getRepository('SQLBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.9.4", "Timeline", "editcomment", "Bad Parameter: id");

		$type = $em->getRepository('SQLBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.9.9", "Timeline", "editcomment"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.9.9", "Timeline", "editcomment"));
		}

		$comment = $em->getRepository('SQLBundle:TimelineComment')->find($content->commentId);
		if (!($comment instanceof TimelineComment))
			return $this->setBadRequest("11.9.4", "Timeline", "editcomment", "Bad Parameter: commentId");

		$comment->setComment($content->comment);
		$comment->setEditedAt(new DateTime('now'));

		$em->persist($comment);
		$em->flush();

		return $this->setSuccess("1.11.1", "Timeline", "editcomment", "Complete Success", $comment->objectToArray());
	}

	/**
	* @api {delete} /0.3/timeline/comment/:id Delete comment
	* @apiName deleteComment
	* @apiGroup Timeline
	* @apiDescription Delete the given comment
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id Id of the timeline
	* @apiParam {int} commentId Id of the comment
	*
	* @apiSuccess {Number} id Id of the message archived
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.11.1",
	*			"return_message": "Timeline - deleteComment - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id" : 3
	*		}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "11.10.3",
	*			"return_message": "Timeline - deleteComment - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "11.10.9",
	*			"return_message": "Timeline - deleteComment - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "11.10.4",
	*			"return_message": "Timeline - deleteComment - Bad Parameter: id"
	*		}
	*	}
	*/
	public function deleteCommentAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.6.3", "Timeline", "deleteComment"));

		$em = $this->getDoctrine()->getManager();

		$comment = $em->getRepository('SQLBundle:TimelineComment')->find($id);
		if (!($comment instanceof TimelineComment))
			return $this->setBadRequest("11.10.4", "Timeline", "deleteComment", "Bad Parameter: id");

		if ($user->getId() != $comment->getCreator()->getId())
			return ($this->setNoRightsError("11.10.9", "Timeline", "deleteComment"));

		$em->remove($comment);
		$em->flush();

		return $this->setSuccess("1.11.1", "Timeline", "deleteComment", "Complete Success", array("id" => $id));
	}
}