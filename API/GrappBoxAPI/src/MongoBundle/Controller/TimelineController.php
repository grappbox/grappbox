<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MongoBundle\Document\Timeline;
use MongoBundle\Document\TimelineType;
use MongoBundle\Document\TimelineMessage;
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
	* @api {get} /mongo/timeline/gettimelines/:token/:id List the timeline of a project
	* @apiName getTimelines
	* @apiGroup Timeline
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id id of the project
	*
	* @apiSuccess {Object[]} timelines Timeline object array
	* @apiSuccess {int} timelines.id Timeline id
	* @apiSuccess {String} timelines.name Timeline name
	* @apiSuccess {int} timelines.projectId project id
	* @apiSuccess {int} timelines.typeId Timeline type id
	* @apiSuccess {String} timelines.typeName Timeline type name
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		[
	*			{"id": 2, "typeId": 1, "projectId": 12, "name": "Customer timeline project XYZ", "typeName": "customerTimeline"},
	*			{"id": 3, "typeId": 2, "projectId": 12, "name": "Team timeline project XYZ", "typeName": "teamTimeline"}
	*		]
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
		$timelines = $em->getRepository('MongoBundle:Timeline')->findBy(array("projectId" => $id));

		$timeline_array = array();
		foreach ($timelines as $key => $value) {
			$type = $em->getRepository('MongoBundle:TimelineType')->find($value->getTypeId());
			if (($this->checkRoles($user, $id, "customerTimeline") && strcmp($type->getName(), "customerTimeline") == 0)
					|| ($this->checkRoles($user, $id, "teamTimeline") && strcmp($type->getName(), "teamTimeline") == 0))
			{
				$tmp = $value->objectToArray();
				$tmp["typeName"] = $type->getName();
				$timeline_array[] = $tmp;
			}
		}

		return new JsonResponse(array("timelines" => $timeline_array));
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
	*			"return_code": "1.11.2",
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
	*			"return_code": "1.11.2",
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
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError("11.2.9", "Timeline", "postmessage"));
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
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

		return $this->setSuccess("1.11.2", "Timeline", "postmessage", "Complete Success", $message->objectToArray());
	}

	/**
	* @api {post} /mongo/timeline/editmessage/:id Edit a message
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId message's id
	* @apiParam {String} title message title
	* @apiParam {String} message message to post
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
	* 	{
	*		"message": {
	*			"id": "154",
	*			"userId": "25",
	*			"timelineId": 14,
	*			"title": "hello",
	*			"message": "What about a meeting tomorrow morning or next monday ?",
	*			"parentId": 12,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 07:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*			}
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
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$message = $em->getRepository('MongoBundle:TimelineMessage')->find($content->messageId);
		$message->setTitle($content->title);
		$message->setMessage($content->message);
		$message->setEditedAt(new DateTime('now'));

		$em->persist($message);
		$em->flush();

		return new JsonResponse(array("message" => $message->objectToArray()));
	}

	/**
	* @api {get} /mongo/timeline/getmessages/:token/:id Get all messages from a timeline except comments
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} messages array of all the timeline's messages
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.title Message title
	* @apiSuccess {String} messages.message Message content
 	* @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"messages": [
	*		{"id": "154","userId": "25", "timelineId": 14,
	*			"title": "hello", message": "What about a meeting tomorrow morning ?", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		{"id": "158","userId": "21", "timelineId": 14,
	*			"title": "hello", "message": "Ok, let's do this !", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		...
	*		]
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
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$messages = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => null), array("createdAt" => "ASC"));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		return new JsonResponse(array("messages" => $timelineMessages));
	}

	/**
	* @api {get} /mongo/timeline/getcomments/:token/:id/:message Get comments of a message
	* @apiName getComments
	* @apiGroup Timeline
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} message commented message id
	*
	* @apiSuccess {Object[]} messages array of all the message's comments
	* @apiSuccess {int} messages.id Message id
	* @apiSuccess {int} messages.userId author id
	* @apiSuccess {int} messages.timelineId timeline id
	* @apiSuccess {String} messages.title Message title
	* @apiSuccess {String} messages.message Message content
  * @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"comments": [
	*		{"id": "154","userId": "25", "timelineId": 14,
	*			"title": "hello", "message": "What about a meeting tomorrow morning ?", "parentId": 150,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		{"id": "158","userId": "21", "timelineId": 14,
	*			"title": "hello", "message": "Ok, let's do this !", "parentId": 150,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		 ...
	*		]
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
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$messages = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => $messageId), array("createdAt" => "DESC"));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		return new JsonResponse(array("comments" => $timelineMessages));
	}

	/**
	* @api {get} /mongo/timeline/getlastmessages/:token/:id/:offset/:limit Get X last message from offset Y
	* @apiName getLastMessages
	* @apiGroup Timeline
	* @apiVersion 0.11.0
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
	* @apiSuccess {String} messages.title Message title
	* @apiSuccess {String} messages.message Message content
  * @apiSuccess {int} messages.parentId parent message id
	* @apiSuccess {DateTime} messages.createdAt Message creation date
	* @apiSuccess {DateTime} messages.editedAt Message edition date
	* @apiSuccess {DateTime} messages.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"messages": [
	*		{"id": "154","userId": "25", "timelineId": 14,
	*			"title": "hello", "message": "What about a meeting tomorrow morning ?", "parentId": NULL,
	*			createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		{"id": "158","userId": "21", "timelineId": 14,
	*			"title": "hello", "message": "Ok, let's do this !", "parentId": NULL,
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null},
	*		...
	*		]
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
	* @apiErrorExample Bad Timeline Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Timeline Id"
	* 	}
	*
	*/
	public function getLastMessagesAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("Bad Timeline Id");

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$messages = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => null), array("createdAt" => "ASC"), $limit, $offset);
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		return new JsonResponse(array("messages" => $timelineMessages));
	}

	/**
	* @api {get} /mongo/timeline/archivemessage/:token/:id/:messageId Archive a message and his comments
	* @apiName ArchiveMessage
	* @apiGroup Timeline
	* @apiVersion 0.11.0
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
	* @apiErrorExample Bad Timeline Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Timeline Id"
	* 	}
	*
	*/
	public function archiveMessageAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("Bad Timeline Id");

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}

		$message = $em->getRepository('MongoBundle:TimelineMessage')->find($messageId);
		while($message instanceof TimelineMessage)
		{
			$parentMsg = $message->getId();
			$message->setDeletedAt(new DateTime('now'));

			$em->persist($message);
			$em->flush();

			$message = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("parentId" => $parentMsg));
		}

		return new JsonResponse('Success');
	}
}
