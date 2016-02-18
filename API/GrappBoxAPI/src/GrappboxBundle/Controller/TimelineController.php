<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GrappboxBundle\Entity\Timeline;
use GrappboxBundle\Entity\TimelineType;
use GrappboxBundle\Entity\TimelineMessage;
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
*/
class TimelineController extends RolesAndTokenVerificationController
{
	/**
	* @api {get} /V0.2/timeline/gettimelines/:token/:id List the timeline of a project
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
	public function getTimelinesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.1.3", "Task", "gettimelines"));

		$em = $this->getDoctrine()->getManager();
		$timelines = $em->getRepository('GrappboxBundle:Timeline')->findBy(array("projectId" => $id));

		$timeline_array = array();
		foreach ($timelines as $key => $value) {
			$type = $em->getRepository('GrappboxBundle:TimelineType')->find($value->getTypeId());
			if (($this->checkRoles($user, $id, "customerTimeline") > 1 && strcmp($type->getName(), "customerTimeline") == 0)
					|| ($this->checkRoles($user, $id, "teamTimeline") > 1 && strcmp($type->getName(), "teamTimeline") == 0))
			{
				$tmp = $value->objectToArray();
				$tmp["typeName"] = $type->getName();
				$timeline_array[] = $tmp;
			}
		}

		if (count($timeline_array) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "gettimelines");

		return $this->setSuccess("1.11.1", "Timeline", "gettimelines", "Complete Success", array("array" => $timeline_array));
	}

	/**
	* @api {post} /V0.2/timeline/postmessage/:id Post a new message or comment
	* @apiName postMessage/Comment
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
	* @apiSuccess {int} userId Id of the user
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
	*			"userId": "25",
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
	*			"userId": "33",
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

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("11.2.3", "Timeline", "postmessage"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('GrappboxBundle:Timeline')->find($id);

		$type = $em->getRepository('GrappboxBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.2.9", "Timeline", "postmessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.2.9", "Timeline", "postmessage"));
		}

		$message = new TimelineMessage();
		$message->setUserId($user->getId());
		$message->setTitle($content->title);
		$message->setMessage($content->message);
		$message->setTimelineId($timeline->getId());
		$message->setTimelines($timeline);
		if (array_key_exists("commentedId", $content))
			$message->setParentId($content->commentedId);
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
	* @api {post} /V0.2/timeline/editmessage/:id Edit a message
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiDescription Edit a given message
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId message's id
	* @apiParam {String} title message title
	* @apiParam {String} message message to post
	*
	* @apiParamExample {json} Request-Minimum-Example:
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
	* @apiSuccess {int} userId author id
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
	*			"userId": "25",
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
	public function editMessageAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("11.3.3", "Timeline", "editmessage"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('GrappboxBundle:Timeline')->find($id);

		$type = $em->getRepository('GrappboxBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.3.9", "Timeline", "editmessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.3.9", "Timeline", "editmessage"));
		}

		$message = $em->getRepository('GrappboxBundle:TimelineMessage')->find($content->messageId);
		$message->setTitle($content->title);
		$message->setMessage($content->message);
		$message->setEditedAt(new DateTime('now'));

		$em->persist($message);
		$em->flush();

		return $this->setSuccess("1.11.1", "Timeline", "editmessage", "Complete Success", $message->objectToArray());
	}

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
	* @apiSuccess {int} array.userId Id of the author of the message
	* @apiSuccess {int} array.timelineId Id of the timeline
	* @apiSuccess {String} array.title Message title
	* @apiSuccess {String} array.message Message content
 	* @apiSuccess {int} array.parentId Parent message id if it's a comment
	* @apiSuccess {DateTime} array.createdAt Message creation date
	* @apiSuccess {DateTime} array.editedAt Message edition date
	* @apiSuccess {DateTime} array.deletedAt Message deletion date
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
	*					"userId": "25",
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
	*					"deletedAt": null
	*				},
	*				{
	*					"id": "158",
	*					"userId": "21",
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
	*					"deletedAt": null
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
	public function getMessagesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.4.3", "Timeline", "getmessages"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('GrappboxBundle:Timeline')->find($id);
		$type = $em->getRepository('GrappboxBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.4.9", "Timeline", "getmessages"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.4.9", "Timeline", "getmessages"));
		}

		$messages = $em->getRepository('GrappboxBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => null), array("createdAt" => "ASC"));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getmessages");

		return $this->setSuccess("1.11.1", "Timeline", "getmessages", "Complete Success", array("array" => $timelineMessages));
	}


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
	* @apiSuccess {int} userId author id
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
	*			{"id": "154","userId": "25", "timelineId": 14, "parentId": 150,
	*				"title": "hello", "message": "What about a meeting tomorrow morning ?",
	*				"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*				"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*				"deletedAt": null},
	*			{"id": "158","userId": "21", "timelineId": 14, "parentId": 150,
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
	public function getCommentsAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.6.3", "Timeline", "getComments"));
		$em = $this->getDoctrine()->getManager();

		$timeline = $em->getRepository('GrappboxBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.6.4", "Timeline", "getComments", "Bad Parameter: id");

		$type = $em->getRepository('GrappboxBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError("11.6.9", "Timeline", "getComments"));
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError("11.6.9", "Timeline", "getComments"));
		}
		$messages = $em->getRepository('GrappboxBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => $messageId), array("createdAt" => "DESC"));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getComments");

		return $this->setSuccess("1.11.1", "Timeline", "getComments", "Complete Success", array("array" => $timelineMessages));
	}

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
	* @apiSuccess {int} array.userId author id
	* @apiSuccess {int} array.timelineId timeline id
	* @apiSuccess {String} array.title Message title
	* @apiSuccess {String} array.message Message content
	* @apiSuccess {int} array.parentId parent message id
	* @apiSuccess {DateTime} array.createdAt Message creation date
	* @apiSuccess {DateTime} array.editedAt Message edition date
	* @apiSuccess {DateTime} array.deletedAt Message deletion date
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
	*					"userId": "25",
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
	*					"deletedAt": null
	*				},
	*				{
	*					"id": "158",
	*					"userId": "21",
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
	*					"deletedAt": null
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
	public function getLastMessagesAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.5.3", "Timeline", "getlastmessages"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('GrappboxBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.5.4", "Timeline", "getlastmessages", "Bad Parameter: id");

		$type = $em->getRepository('GrappboxBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.5.9", "Timeline", "getlastmessages"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.5.9", "Timeline", "getlastmessages"));
		}

		$messages = $em->getRepository('GrappboxBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => null), array("createdAt" => "ASC"), $limit, $offset);
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getlastmessages");

		return $this->setSuccess("1.11.1", "Timeline", "getlastmessages", "Complete Success", array("array" => $timelineMessages));
	}

	/**
	* @api {get} /V0.2/timeline/archivemessage/:token/:id/:messageId Archive a message and his comments
	* @apiName ArchiveMessage
	* @apiGroup Timeline
	* @apiDescription Archive the given message and his comments
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
	public function archiveMessageAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.6.3", "Timeline", "archivemessage"));

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('GrappboxBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.6.4", "Timeline", "archivemessage", "Bad Parameter: id");

		$type = $em->getRepository('GrappboxBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.6.9", "Timeline", "archivemessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.6.9", "Timeline", "archivemessage"));
		}

		$message = $em->getRepository('GrappboxBundle:TimelineMessage')->find($messageId);
		while($message instanceof TimelineMessage)
		{
			$parentMsg = $message->getId();
			$message->setDeletedAt(new DateTime('now'));

			$em->persist($message);
			$em->flush();

			$message = $em->getRepository('GrappboxBundle:TimelineMessage')->findBy(array("parentId" => $parentMsg));
		}

		return $this->setSuccess("1.11.1", "Timeline", "archivemessage", "Complete Success", array("id" => $messageId));
	}
}
