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
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} id id of the project
	*/
	public function getTimelinesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {post} /mongo/timeline/postmessage/:id Post a new message or comment
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
	*/
	public function postMessageAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("11.2.3", "Timeline", "postmessage"));

		$em = $this->get('doctrine_mongodb')->getManager();
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
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId message's id
	* @apiParam {String} title message title
	* @apiParam {String} message message to post
	*/
	public function editMessageAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
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
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	*/
	public function getMessagesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->get('doctrine_mongodb')->getManager();
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
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} message commented message id
	*/
	public function getCommentsAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->get('doctrine_mongodb')->getManager();
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
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} offset message offset from where to get the messages (start to 0)
	* @apiParam {int} limit number max of messages to get
	*/
	public function getLastMessagesAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->get('doctrine_mongodb')->getManager();
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
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} messageId id of the message
	*/
	public function archiveMessageAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
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
