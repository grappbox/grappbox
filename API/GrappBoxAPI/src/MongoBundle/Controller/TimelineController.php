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
	* @apiDescription List all the timelines of a project
	* @apiVersion 0.2.0
	*
	*/
	public function getTimelinesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.1.3", "Task", "gettimelines"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$timelines = $em->getRepository('MongoBundle:Timeline')->findBy(array("projectId" => $id));

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
	* @api {post} /mongo/timeline/postmessage/:id Post a new message or comment
	* @apiName postMessage/Comment
	* @apiGroup Timeline
	* @apiDescription Post a new message or a comment for the given timeline
	* @apiVersion 0.2.0
	*
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
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.2.9", "Timeline", "postmessage"));
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
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
	* @api {put} /mongo/timeline/editmessage/:id Edit a message or comment
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiDescription Edit a given message or comment
	* @apiVersion 0.2.0
	*
	*/
	public function editMessageAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("11.3.3", "Timeline", "editmessage"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.3.9", "Timeline", "editmessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.3.9", "Timeline", "editmessage"));
		}

		$message = $em->getRepository('MongoBundle:TimelineMessage')->find($content->messageId);
		$message->setTitle($content->title);
		$message->setMessage($content->message);
		$message->setEditedAt(new DateTime('now'));

		$em->persist($message);
		$em->flush();

		return $this->setSuccess("1.11.1", "Timeline", "editmessage", "Complete Success", $message->objectToArray());
	}

	/**
	* @api {get} /mongo/timeline/getmessages/:token/:id Get all messages from a timeline except comments
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiDescription Get all the messages but not the comments from a timeline
	* @apiVersion 0.2.0
	*
	*/
	public function getMessagesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.4.3", "Timeline", "getmessages"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.4.9", "Timeline", "getmessages"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.4.9", "Timeline", "getmessages"));
		}

		$messages = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => null), array("createdAt" => "DESC"));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {

			$query = $em->getRepository('MongoBundle:TimelineMessage')->createQueryBuilder('m');
			$commentsNb = $query->select($query->expr()->count('m.id'))
						->where("m.parentId = :parent AND m.deletedAt IS NULL")
						->setParameter("parent", $value->getId())
						->getQuery()->getSingleScalarResult();

			$elem = $value->objectToArray();
			$elem['nbComment'] = $commentsNb;
			$timelineMessages[] = $elem;
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getmessages");

		return $this->setSuccess("1.11.1", "Timeline", "getmessages", "Complete Success", array("array" => $timelineMessages));
	}


	/**
	* @api {get} /mongo/timeline/getcomments/:token/:id/:message Get comments of a message
	* @apiName getComments
	* @apiGroup Timeline
	* @apiVersion 0.2.0
	*
	*/
	public function getCommentsAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.6.3", "Timeline", "getComments"));
		$em = $this->get('doctrine_mongodb')->getManager();

		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.6.4", "Timeline", "getComments", "Bad Parameter: id");

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if (!$this->checkRoles($user, $timeline->getProjectId(), "customerTimeline"))
				return ($this->setNoRightsError());
		} else {
			if (!$this->checkRoles($user, $timeline->getProjectId(), "teamTimeline"))
				return ($this->setNoRightsError());
		}
		$messages = $em->getRepository('GrappboxBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => $messageId), array("createdAt" => "ASC"));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$timelineMessages[] = $value->objectToArray();
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getComments");

		return $this->setSuccess("1.11.1", "Timeline", "getComments", "Complete Success", array("array" => $timelineMessages));
	}

	/**
	* @api {get} /mongo/timeline/getlastmessages/:token/:id/:offset/:limit Get X last message from offset Y
	* @apiName getLastMessages
	* @apiGroup Timeline
	* @apiDescription Get the last X messages from offset Y of the given timeline
	* @apiVersion 0.2.0
	*
	*/
	public function getLastMessagesAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("11.5.3", "Timeline", "getlastmessages"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.5.4", "Timeline", "getlastmessages", "Bad Parameter: id");

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.5.9", "Timeline", "getlastmessages"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.5.9", "Timeline", "getlastmessages"));
		}

		$messages = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("timelineId" => $timeline->getId(), "deletedAt" => null, "parentId" => null), array("createdAt" => "DESC"), $limit, $offset);
		$timelineMessages = array();
		foreach ($messages as $key => $value) {
			$query = $em->getRepository('GrappboxBundle:TimelineMessage')->createQueryBuilder('m');
			$commentsNb = $query->select($query->expr()->count('m.id'))
						->where("m.parentId = :parent AND m.deletedAt IS NULL")
						->setParameter("parent", $value->getId())
						->getQuery()->getSingleScalarResult();

			$elem = $value->objectToArray();
			$elem['nbComment'] = $commentsNb;
			$timelineMessages[] = $elem;
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getlastmessages");

		return $this->setSuccess("1.11.1", "Timeline", "getlastmessages", "Complete Success", array("array" => $timelineMessages));
	}

	/**
	* @api {delete} /mongo/timeline/archivemessage/:token/:id/:messageId Archive a comment or a message and his comments
	* @apiName ArchiveMessage
	* @apiGroup Timeline
	* @apiDescription Archive the given message and his comments or just a given comment
	* @apiVersion 0.2.0
	*
	*/
	public function archiveMessageAction(Request $request, $token, $id, $messageId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.6.4", "Timeline", "archivemessage", "Bad Parameter: id");

		$type = $em->getRepository('MongoBundle:TimelineType')->find($timeline->getTypeId());
		if ($type->getName() == "customerTimeline")
		{
			if ($this->checkRoles($user, $timeline->getProjectId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.6.9", "Timeline", "archivemessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjectId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.6.9", "Timeline", "archivemessage"));
		}

		$message = $em->getRepository('MongoBundle:TimelineMessage')->find($messageId);
		$message->setDeletedAt(new DateTime('now'));
		$em->persist($message);
		$em->flush();

		$comments = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("parentId" => $message->getId()));
		foreach ($comments as $key => $value) {
			$value->setDeletedAt(new DateTime('now'));
			$em->persist($value);
			$em->flush();
		}

		return $this->setSuccess("1.11.1", "Timeline", "archivemessage", "Complete Success", array("id" => $messageId));
	}
}
