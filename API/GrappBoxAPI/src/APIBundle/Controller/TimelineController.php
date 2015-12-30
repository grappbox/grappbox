<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use APIBundle\Entity\Timeline;
use APIBundle\Entity\TimelineType;
use APIBundle\Entity\TimelineMessage;
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
class TimelineController extends RolesAndTokenVerificationController
{
	/**
	* @api {get} /V0.8/timeline/gettimelines/:token/:id List the timeline of a project
	* @apiName getTimelines
	* @apiGroup Timeline
	* @apiVersion 0.8.1
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id id of the project
	*
	* @apiSuccess {Object[]} timelines Timeline object array
	* @apiSuccess {int} timelines.id Timeline id
	* @apiSuccess {String} timelines.name Timeline name
	* @apiSuccess {int} timelines.prjectId project id
	* @apiSuccess {int} timelines.typeId Timeline type id
	* @apiSuccess {String} timelines.typeName Timeline type name
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*			0: {"id": 2, "projectId": 12, "typeId": 1, "typeName": "customerTimeline", "name": "Customer timeline project XYZ"},
	*			1: {"id": 3, "projectId": 12, "typeId": 2, "typeName": "teamTimeline", "name": "Team timeline project XYZ"}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.9/timeline/gettimelines/:token/:id List the timeline of a project
	* @apiName getTimelines
	* @apiGroup Timeline
	* @apiVersion 0.9.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id id of the project
	*
	* @apiSuccess {Object[]} timelines Timeline object array
	* @apiSuccess {int} timelines.id Timeline id
	* @apiSuccess {String} timelines.name Timeline name
	* @apiSuccess {int} timelines.prjectId project id
	* @apiSuccess {int} timelines.typeId Timeline type id
	* @apiSuccess {String} timelines.typeName Timeline type name
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*			0: {"id": 2, "projectId": 12, "typeId": 1, "typeName": "customerTimeline", "name": "Customer timeline project XYZ"},
	*			1: {"id": 3, "projectId": 12, "typeId": 2, "typeName": "teamTimeline", "name": "Team timeline project XYZ"}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	public function getTimelinesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$timelines = $em->getRepository('APIBundle:Timeline')->findBy(array("projectId" => $id));

		$timeline_array = array();
		foreach ($timelines as $key => $value) {
			$type = $em->getRepository('APIBundle:TimelineType')->find($value->getTypeId());
			if (($this->checkRoles($user, $id, "customerTimeline") && strcmp($type->getName(), "customerTimeline") == 0)
					|| ($this->checkRoles($user, $id, "teamTimeline") && strcmp($type->getName(), "teamTimeline") == 0))
			{
				$tmp = $value->objectToArray();
				$tmp["typeName"] = $type->getName();
				$timeline_array[] = $tmp;
			}
		}

		return new JsonResponse($timeline_array);
	}

	/**
	* @api {post} /V0.7/timeline/postmessage/:id Post a new message or comment
	* @apiName postMessage/Comment
	* @apiGroup Timeline
	* @apiVersion 0.7.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {String} message message to post
	* @apiParam {int} commentedId (required only for comments) message commented id
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {int} userId author id
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"id": "154",
	*		"userId": "25",
	*		"timelineId": 14,
	*		"message": "What about a meeting tomorrow morning ?",
	*		"parentId": 12,
	*		"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"editedAt": NULL
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {post} /V0.8/timeline/postmessage/:id Post a new message or comment
	* @apiName postMessage/Comment
	* @apiGroup Timeline
	* @apiVersion 0.8.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {String} message message to post
	* @apiParam {int} commentedId (required only for comments) message commented id
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {int} userId author id
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"id": "154",
	*		"userId": "25",
	*		"timelineId": 14,
	*		"message": "What about a meeting tomorrow morning ?",
	*		"parentId": 12,
	*		"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"editedAt": NULL
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {post} /V0.9/timeline/postmessage/:id Post a new message or comment
	* @apiName postMessage/Comment
	* @apiGroup Timeline
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {String} message message to post
	* @apiParam {int} commentedId (required only for comments) message commented id
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {int} userId author id
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"id": "154",
	*		"userId": "25",
	*		"timelineId": 14,
	*		"message": "What about a meeting tomorrow morning ?",
	*		"parentId": 12,
	*		"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"editedAt": NULL
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function postMessageAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('APIBundle:Timeline')->find($id);

		$type = $em->getRepository('APIBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$message = new TimelineMessage();
		$message->setUserId($user->getId());
		$message->setMessage($content->message);
		$message->setTimelineId($timeline->getId());
		$message->setTimelines($timeline);
		if (array_key_exists("commentedId", $content))
			$message->setParentId($content->commentedId);
		$message->setCreatedAt(new DateTime('now'));

		$em->persist($message);
		$em->flush();

		return new JsonResponse($message->objectToArray());
	}

	/**
	* @api {post} /V0.7/timeline/editmessage/:id Edit a message
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiVersion 0.7.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId message's id
	* @apiParam {String} message message to post
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {int} userId author id
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"id": "154",
	*		"userId": "25",
	*		"timelineId": 14,
	*		"message": "What about a meeting tomorrow morning or next monday ?",
	*		"parentId": 12,
	*		"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"editedAt": {"date": "1945-06-18 07:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {post} /V0.8/timeline/editmessage/:id Edit a message
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiVersion 0.8.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId message's id
	* @apiParam {String} message message to post
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {int} userId author id
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"id": "154",
	*		"userId": "25",
	*		"timelineId": 14,
	*		"message": "What about a meeting tomorrow morning or next monday ?",
	*		"parentId": 12,
	*		"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"editedAt": {"date": "1945-06-18 07:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {post} /V0.9/timeline/editmessage/:id Edit a message
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId message's id
	* @apiParam {String} message message to post
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {int} userId author id
	* @apiSuccess {int} timelineId timeline id
	* @apiSuccess {String} message Message content
	* @apiSuccess {int} parentId parent message id
	* @apiSuccess {DateTime} createdAt Message creation date
	* @apiSuccess {DateTime} editedAt Message last modification date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"id": "154",
	*		"userId": "25",
	*		"timelineId": 14,
	*		"message": "What about a meeting tomorrow morning or next monday ?",
	*		"parentId": 12,
	*		"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"editedAt": {"date": "1945-06-18 07:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function editMessageAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('APIBundle:Timeline')->find($id);

		$type = $em->getRepository('APIBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$message = $em->getRepository('APIBundle:TimelineMessage')->find($content->messageId);
		$message->setMessage($content->message);
		$message->setEditedAt(new DateTime('now'));

		$em->persist($message);
		$em->flush();

		return new JsonResponse($message->objectToArray());
	}

	/**
	* @api {get} /V0.7/timeline/getmessages/:token/:id Get all messages from a timeline
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiVersion 0.7.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} messages array of all the timeline's messages
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
  	* @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": NULL, "createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": 154, "createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.8/timeline/getmessages/:token/:id Get all messages from a timeline
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiVersion 0.8.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} messages array of all the timeline's messages
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
  	* @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": NULL, "createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": 154, "createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.8/timeline/getmessages/:token/:id Get all messages from a timeline
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiVersion 0.8.1
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} messages array of all the timeline's messages
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
  * @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": 154,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.8/timeline/getmessages/:token/:id Get all messages from a timeline except comments
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiVersion 0.8.2
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} messages array of all the timeline's messages
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
 	* @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.9/timeline/getmessages/:token/:id Get all messages from a timeline except comments
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} messages array of all the timeline's messages
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
 	* @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function getMessagesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('APIBundle:Timeline')->find($id);

		$type = $em->getRepository('APIBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$messages = $em->getRepository('APIBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => null));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		return new JsonResponse($timelineMessages);
	}

	/**
	* @api {get} /V0.8/timeline/getcomments/:token/:id/:message Get comments of a message
	* @apiName getComments
	* @apiGroup Timeline
	* @apiVersion 0.8.1
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} message commented message id
	*
	* @apiSuccess {Object[]} messages array of all the message's comments
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
  * @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": 150,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": 150,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.9/timeline/getcomments/:token/:id/:message Get comments of a message
	* @apiName getComments
	* @apiGroup Timeline
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} message commented message id
	*
	* @apiSuccess {Object[]} messages array of all the message's comments
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
  * @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": 150,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": 150,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function getCommentsAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('APIBundle:Timeline')->find($id);

		$type = $em->getRepository('APIBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$messages = $em->getRepository('APIBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => $messageId));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		return new JsonResponse($timelineMessages);
	}

	/**
	* @api {get} /V0.8/timeline/getlastmessages/:token/:id/:offset/:limit Get X last message from offset Y
	* @apiName getLastMessages
	* @apiGroup Timeline
	* @apiVersion 0.8.1
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} offset message offset from where to get the messages (start to 0)
	* @apiParam {int} limit number max of messages to get
	*
	* @apiSuccess {Object[]} messages array of all the timeline's messages
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
  * @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.9/timeline/getlastmessages/:token/:id/:offset/:limit Get X last message from offset Y
	* @apiName getLastMessages
	* @apiGroup Timeline
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} offset message offset from where to get the messages (start to 0)
	* @apiParam {int} limit number max of messages to get
	*
	* @apiSuccess {Object[]} messages array of all the timeline's messages
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.message Message content
  * @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","userId": "25", "timelineId": 14, "message": "What about a meeting tomorrow morning ?", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		1 : {"id": "158","userId": "21", "timelineId": 14, "message": "Ok, let's do this !", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		2 : ...
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function getLastMessagesAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('APIBundle:Timeline')->find($id);

		$type = $em->getRepository('APIBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$messages = $em->getRepository('APIBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => null), array(), $limit, $offset);
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		return new JsonResponse($timelineMessages);
	}

	/**
	* @api {get} /V0.7/timeline/archivemessage/:token/:id/:messageId Archive a message and his comments
	* @apiName ArchiveMessage
	* @apiGroup Timeline
	* @apiVersion 0.7.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId id of the message
	*
	* @apiSuccess {String} success succes message
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*			"Success"
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.8/timeline/archivemessage/:token/:id/:messageId Archive a message and his comments
	* @apiName ArchiveMessage
	* @apiGroup Timeline
	* @apiVersion 0.8.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId id of the message
	*
	* @apiSuccess {String} success succes message
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*			"Success"
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/

	/**
	* @api {get} /V0.9/timeline/archivemessage/:token/:id/:messageId Archive a message and his comments
	* @apiName ArchiveMessage
	* @apiGroup Timeline
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId id of the message
	*
	* @apiSuccess {String} success succes message
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*			"Success"
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
 	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function archiveMessageAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('APIBundle:Timeline')->find($id);

		$type = $em->getRepository('APIBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$message = $em->getRepository('APIBundle:TimelineMessage')->find($messageId);
		while($message instanceof TimelineMessage)
		{
			$parentMsg = $message->getId();
			$message->setDeletedAt(new DateTime('now'));

			$em->persist($message);
			$em->flush();

			$message = $em->getRepository('APIBundle:TimelineMessage')->findBy(array("parentId" => $parentMsg));
		}

		return new JsonResponse('Success');
	}
}
