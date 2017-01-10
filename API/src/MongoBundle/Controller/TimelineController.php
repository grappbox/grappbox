<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MongoBundle\Document\Timeline;
use MongoBundle\Document\TimelineType;
use MongoBundle\Document\TimelineMessage;
use MongoBundle\Document\TimelineComment;
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

/*
* NOTE: customer timeline => typeId = 1 , team timeline => typeId = 2
*/

class TimelineController extends RolesAndTokenVerificationController
{
	/**
	* @-api {post} /0.3/timeline/message/:id Post a new message
	* @apiName postMessage
	* @apiGroup Timeline
	* @apiDescription Post a new message for the given timeline, to post message see postMessage request
	* @apiVersion 0.3.0
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.2.4", "Timeline", "postmessage", "Bad Parameter: id");

		if ($timeline->getTypeId() == 1)
		{
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.2.9", "Timeline", "postmessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "teamTimeline") < 2)
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

		$messageArray = $message->objectToArray();
		$messageArray['projectId'] = $message->getTimelines()->getProjects()->getId();

		//notifs
		$mdata['mtitle'] = "new message";
		$mdata['mdesc'] = json_encode($messageArray);
		$wdata['type'] = "new message";
		$wdata['targetId'] = $message->getId();
		$wdata['message'] = json_encode($messageArray);
		$userNotif = array();
		foreach ($timeline->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.11.1", "Timeline", "postmessage", "Complete Success", $message->objectToArray());
	}

	/**
	* @-api {put} /0.3/timeline/message/:id/:messageId Edit a message
	* @apiName editMessage
	* @apiGroup Timeline
	* @apiDescription Edit a given message
	* @apiVersion 0.3.0
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.3.4", "Timeline", "editmessage", "Bad Parameter: id");

		if ($timeline->getTypeId() == 1)
		{
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.3.9", "Timeline", "editmessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.3.9", "Timeline", "editmessage"));
		}

		$message = $em->getRepository('MongoBundle:TimelineMessage')->find($messageId);
		$message->setTitle($content->title);
		$message->setMessage($content->message);
		$message->setEditedAt(new DateTime('now'));

		$em->persist($message);
		$em->flush();

		$messageArray = $message->objectToArray();
		$messageArray['projectId'] = $message->getTimelines()->getProjects()->getId();

		//notifs
		$mdata['mtitle'] = "update message";
		$mdata['mdesc'] = json_encode($messageArray);
		$wdata['type'] = "update message";
		$wdata['targetId'] = $message->getId();
		$wdata['message'] = json_encode($messageArray);
		$userNotif = array();
		foreach ($timeline->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.11.1", "Timeline", "editmessage", "Complete Success", $message->objectToArray());
	}

	/**
	* @-api {delete} /0.3/timeline/message/:id/:messageId Delete a message and his comments
	* @apiName DeleteMessage
	* @apiGroup Timeline
	* @apiDescription Delete the given message and his comments
	* @apiVersion 0.3.0
	*
	*/
	public function archiveMessageAction(Request $request, $id, $messageId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.6.4", "Timeline", "archivemessage", "Bad Parameter: id");

		if ($timeline->getTypeId() == 1)
		{
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.6.9", "Timeline", "archivemessage"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.6.9", "Timeline", "archivemessage"));
		}

		$message = $em->getRepository('MongoBundle:TimelineMessage')->find($messageId);
		if ($message === null)
			return $this->setBadRequest("11.6.4", "Timeline", "deletemessage", "Bad Parameter: messageId");

		$messageArray = $message->objectToArray();
		$messageArray['projectId'] = $message->getTimelines()->getProjects()->getId();

		//notifs
		$mdata['mtitle'] = "delete message";
		$mdata['mdesc'] = json_encode($messageArray);
		$wdata['type'] = "delete message";
		$wdata['targetId'] = $message->getId();
		$wdata['message'] = json_encode($messageArray);
		$userNotif = array();
		foreach ($timeline->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($message);
		$em->flush();

		return $this->setSuccess("1.11.1", "Timeline", "deletemessage", "Complete Success", array("id" => $messageId));
	}

	/*
	 * --------------------------------------------------------------------
	 *											TIMELINE/MESSAGE GETTERS
	 * --------------------------------------------------------------------
	*/

	/**
	* @-api {get} /0.3/timelines/:id List project timelines
	* @apiName getTimelines
	* @apiGroup Timeline
	* @apiDescription List all the timelines of a project
	* @apiVersion 0.3.0
	*
	*/
	public function getTimelinesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.1.3", "Task", "gettimelines"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$timelines = $em->getRepository('MongoBundle:Timeline')->findBy(array("projects.id" => $id));

		$timeline_array = array();
		foreach ($timelines as $key => $value) {
			$tmp = $value->objectToArray();
			$tmp["typeName"] = ($value->getTypeId() == 1 ? 'customerTimeline' : 'teamTimeline');
			$timeline_array[] = $tmp;
		}

		if (count($timeline_array) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "gettimelines");

		return $this->setSuccess("1.11.1", "Timeline", "gettimelines", "Complete Success", array("array" => $timeline_array));
	}

	/**
	* @-api {get} /0.3/timeline/messages/:id Get timeline's messages
	* @apiName getMessages
	* @apiGroup Timeline
	* @apiDescription Get all the messages from a timeline without comments
	* @apiVersion 0.3.0
	*
	*/
	public function getMessagesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.4.3", "Timeline", "getmessages"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);

		if ($timeline->getTypeId() == 1)
		{
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.4.9", "Timeline", "getmessages"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.4.9", "Timeline", "getmessages"));
		}

		$messages = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("timelines.id" => $timeline->getId()), array("createdAt" => "DESC"));
		$timelineMessages = array();
		foreach ($messages as $key => $value) {

			$elem = $value->objectToArray();

			$req = $em->getRepository('MongoBundle:TimelineComment')->createQueryBuilder()
								->field("messages.id")->equals($value->getId())
								->getQuery()->execute();

			$elem['nbComment'] = count($req);
			$timelineMessages[] = $elem;
		}

		if (count($timelineMessages) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getmessages");

		return $this->setSuccess("1.11.1", "Timeline", "getmessages", "Complete Success", array("array" => $timelineMessages));
	}

	/**
	* @-api {get} /0.3/timeline/messages/:id/:offset/:limit Get X messages from offset Y
	* @apiName getLastMessages
	* @apiGroup Timeline
	* @apiDescription Get the last X messages from offset Y of the given timeline
	* @apiVersion 0.3.0
	*
	*/
	public function getLastMessagesAction(Request $request, $id, $offset, $limit)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.5.3", "Timeline", "getlastmessages"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.5.4", "Timeline", "getlastmessages", "Bad Parameter: id");

		if ($timeline->getTypeId() == 1)
		{
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.5.9", "Timeline", "getlastmessages"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.5.9", "Timeline", "getlastmessages"));
		}

		$messages = $em->getRepository('MongoBundle:TimelineMessage')->findBy(array("timelines.id" => $timeline->getId(), "deletedAt" => null, "parentId" => null), array("createdAt" => "DESC"), $limit, $offset);
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
	* @-api {get} /0.3/timeline/message/comments/:id/:messageId Get message's comments
	* @apiName getComments
	* @apiGroup Timeline
	* @apiVersion 0.3.0
	*
	*/
	public function getCommentsAction(Request $request, $id, $messageId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.6.3", "Timeline", "getComments"));
		$em = $this->get('doctrine_mongodb')->getManager();

		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.6.4", "Timeline", "getComments", "Bad Parameter: id");

		if ($timeline->getTypeId() == 1)
		{
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "customerTimeline") < 1)
				return ($this->setNoRightsError("11.6.9", "Timeline", "getComments"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "teamTimeline") < 1)
				return ($this->setNoRightsError("11.6.9", "Timeline", "getComments"));
		}

		$message = $em->getRepository('MongoBundle:TimelineMessage')->find($messageId);
		if (!($message instanceof TimelineMessage))
			return $this->setBadRequest("11.6.4", "Timeline", "getComments", "Bad Parameter: messageId");

		$comments = $em->getRepository('MongoBundle:TimelineComment')->createQueryBuilder()
									->field('messages.id')->equals($message->getId())
									->sort('createdAt', 'asc')
									->getQuery()->execute();
		//->findBy(array("messages.id" => $message->getId()), array("createdAt" => "ASC"));

		$commentsArray = array();
		foreach ($comments as $key => $value) {
			$commentsArray[] = $value->objectToArray();
		}

		if (count($commentsArray) == 0)
			return $this->setNoDataSuccess("1.11.3", "Timeline", "getComments");

		return $this->setSuccess("1.11.1", "Timeline", "getComments", "Complete Success", array("array" => $commentsArray));
	}

	/**
	* @-api {post} /0.3/timeline/comment/:id Post comment
	* @apiName postComment
	* @apiGroup Timeline
	* @apiDescription Post a new comment for the given message
	* @apiVersion 0.3.0
	*/
	public function postCommentAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("comment", $content) || !array_key_exists("commentedId", $content))
			return $this->setBadRequest("11.8.6", "Timeline", "postcomment", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.8.3", "Timeline", "postcomment"));

		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.8.4", "Timeline", "postcomment", "Bad Parameter: id");

		if ($timeline->getTypeId() == 1)
		{
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.8.9", "Timeline", "postcomment"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.8.9", "Timeline", "postcomment"));
		}

		$message = $em->getRepository('MongoBundle:TimelineMessage')->find($content->commentedId);
		if (!($message instanceof TimelineMessage))
			return $this->setBadRequest("11.8.4", "Timeline", "postcomment", "Bad Parameter: commentedId");

		$comment = new TimelineComment();
		$comment->setCreator($user);
		$comment->setComment($content->comment);
		$comment->setMessages($message);
		$comment->setCreatedAt(new DateTime('now'));

		$em->persist($comment);
		$em->flush();

		$commentArray = $comment->objectToArray();
		$commentArray['projectId'] = $comment->getMessages()->getTimelines()->getProjects()->getId();

		//notifs
		$mdata['mtitle'] = "new comment message";
		$mdata['mdesc'] = json_encode($commentArray);
		$wdata['type'] = "new comment message";
		$wdata['targetId'] = $comment->getId();
		$wdata['message'] = json_encode($commentArray);
		$userNotif = array();
		foreach ($timeline->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.11.1", "Timeline", "postcomment", "Complete Success", $comment->objectToArray());
	}

	/**
	* @-api {put} /0.3/timeline/comment/:id Edit comment
	* @apiName editComment
	* @apiGroup Timeline
	* @apiDescription Edit a given comment
	* @apiVersion 0.3.0
	*/
	public function editCommentAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("comment", $content) || !array_key_exists("commentId", $content))
			return $this->setBadRequest("11.9.6", "Timeline", "editcomment", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.3.3", "Timeline", "editcomment"));

		$timeline = $em->getRepository('MongoBundle:Timeline')->find($id);
		if (!($timeline instanceof Timeline))
			return $this->setBadRequest("11.9.4", "Timeline", "editcomment", "Bad Parameter: id");

		if ($timeline->getTypeId() == 1)
		{
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "customerTimeline") < 2)
				return ($this->setNoRightsError("11.9.9", "Timeline", "editcomment"));
		} else {
			if ($this->checkRoles($user, $timeline->getProjects()->getId(), "teamTimeline") < 2)
				return ($this->setNoRightsError("11.9.9", "Timeline", "editcomment"));
		}

		$comment = $em->getRepository('MongoBundle:TimelineComment')->find($content->commentId);
		if (!($comment instanceof TimelineComment))
			return $this->setBadRequest("11.9.4", "Timeline", "editcomment", "Bad Parameter: commentId");

		$comment->setComment($content->comment);
		$comment->setEditedAt(new DateTime('now'));

		$em->persist($comment);
		$em->flush();

		$commentArray = $comment->objectToArray();
		$commentArray['projectId'] = $comment->getMessages()->getTimelines()->getProjects()->getId();

		//notifs
		$mdata['mtitle'] = "update comment message";
		$mdata['mdesc'] = json_encode($commentArray);
		$wdata['type'] = "update comment message";
		$wdata['targetId'] = $comment->getId();
		$wdata['message'] = json_encode($commentArray);
		$userNotif = array();
		foreach ($timeline->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.11.1", "Timeline", "editcomment", "Complete Success", $comment->objectToArray());
	}

	/**
	* @-api {delete} /0.3/timeline/comment/:id Delete comment
	* @apiName deleteComment
	* @apiGroup Timeline
	* @apiDescription Delete the given comment
	* @apiVersion 0.3.0
	*/
	public function deleteCommentAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("11.6.3", "Timeline", "deleteComment"));

		$em = $this->get('doctrine_mongodb')->getManager();

		$comment = $em->getRepository('MongoBundle:TimelineComment')->find($id);
		if (!($comment instanceof TimelineComment))
			return $this->setBadRequest("11.10.4", "Timeline", "deleteComment", "Bad Parameter: id");

		if ($user->getId() != $comment->getCreator()->getId())
			return ($this->setNoRightsError("11.10.9", "Timeline", "deleteComment"));

		$commentArray = $comment->objectToArray();
		$commentArray['projectId'] = $comment->getMessages()->getTimelines()->getProjects()->getId();

		//notifs
		$mdata['mtitle'] = "delete comment message";
		$mdata['mdesc'] = json_encode($commentArray);
		$wdata['type'] = "delete comment message";
		$wdata['targetId'] = $comment->getId();
		$wdata['message'] = json_encode($commentArray);
		$userNotif = array();
		foreach ($comment->getMessages()->getTimelines()->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($comment);
		$em->flush();

		return $this->setSuccess("1.11.1", "Timeline", "deleteComment", "Complete Success", array("id" => $id));
	}


}
