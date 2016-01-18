<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MongoBundle\Controller\RolesAndTokenVerificationController;

use MongoBundle\Document\User;
use MongoBundle\Document\Bug;
use MongoBundle\Document\BugState;
use MongoBundle\Document\Tag;
use MongoBundle\Document\Project;
use DateTime;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *	@IgnoreAnnotation("apiDescription")
 *  @IgnoreAnnotation("apiVersion")
 *  @IgnoreAnnotation("apiSuccess")
 *  @IgnoreAnnotation("apiSuccessExample")
 *  @IgnoreAnnotation("apiError")
 *  @IgnoreAnnotation("apiErrorExample")
 *  @IgnoreAnnotation("apiParam")
 *  @IgnoreAnnotation("apiParamExample")
 */
class BugtrackerController extends RolesAndTokenVerificationController
{

	/**
	* @api {get} mongo/bugtracker/getticket/:token/:id Get ticket
	* @apiName getTicket
	* @apiGroup Bugtracker
	* @apiDescription Get ticket informations, tags and assigned users
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id ticket's id
	* @apiParam {String} token client authentification token
	*
	*/
	public function getTicketAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.1.3", "Bugtracker", "getTicket"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$ticket = $em->getRepository("MongoBundle:Bug")->find($id);
		if (!($ticket instanceof Bug))
			return $this->setBadRequest("4.1.4", "Bugtracker", "getTicket", "Bad Parameter: id");
		if (!$this->checkRoles($user, $ticket->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError("4.1.9", "Bugtracker", "getTicket"));

		$object = $ticket->objectToArray();
		$object['state'] = $em->getRepository("MongoBundle:BugState")->find($ticket->getStateId())->objectToArray();
		$object['tags'] = array();
		foreach ($ticket->getTags() as $key => $tag_value) {
			$object['tags'][] = $tag_value->objectToArray();
		}
		$participants = array();
		foreach ($ticket->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}
		$object["users"] = $participants;

		return $this->setSuccess("1.4.1", "Bugtracker", "getTicket", "Complete Success", $object);
	}

	/**
	* @api {post} mongo/bugtracker/postticket Post ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
	* @apiDescription Post a ticket
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} projectId id of the project
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {int} stateId Ticket state (0 if new)
	* @apiParam {String} stateName Ticket state
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
  * 		"token": "ThisIsMyToken",
  * 		"projectId": 1,
  * 		"title": "J'ai un petit problème",
  * 		"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
  * 		"stateId": 1,
  * 		"stateName": "To Do"
  * 	}
	*   }
	*
	*
	*/
	public function postTicketAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("token", $content) || !array_key_exists("projectId", $content)
			|| !array_key_exists("title", $content) || !array_key_exists("description", $content)
			|| !array_key_exists("stateId", $content) || !array_key_exists("stateName", $content))
				return $this->setBadRequest("4.2.6", "Bugtracker", "postTicket", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.2.3", "Bugtracker", "postTicket"));

		if (!$this->checkRoles($user, $content->projectId, "bugtracker"))
			return ($this->setNoRightsError("4.2.9", "Bugtracker", "postTicket"));

		$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.2.4", "Bugtracker", "postTicket", "Bad Parameter: projectId");

		$bug = new Bug();
		$bug->setProjects($project);
		$bug->setCreator($user);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setCreatedAt(new DateTime('now'));

		if (array_key_exists("stateId", $content) && $content->stateId != 0)
			$state = $em->getRepository("MongoBundle:BugState")->find($content->stateId);
		if ($state instanceof BugState)
			$bug->setStateId($content->stateId);
		else {
			$state = new BugState();
			$state->setName($content->stateName);

			$em->persist($state);
			$em->flush();

			$bug->setStateId($state->getId());
		}

		$em->persist($bug);
		$em->flush();

		$ticket = $bug->objectToArray();
		$ticket['state'] = $state->getName();
		foreach ($bug->getTags() as $key => $tag_value) {
			$ticket['tags'][] = $tag_value->objectToArray();
		}

		$participants = array();
		foreach ($bug->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}
		$ticket["users"] = $participants;

		return $this->setCreated("1.4.1", "Bugtracker", "postTicket", "Complete Success", $ticket);
	}

	/**
	* @api {put} mongo/bugtracker/editticket Edit ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
	*	@apiDescription Edit ticket
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} bugId id of the bug ticket
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {int} stateId Ticket state (0 if new)
	* @apiParam {String} stateName Ticket state
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
  * 		"token": "ThisIsMyToken",
  * 		"bugId": 1,
  * 		"title": "J'ai un petit problème",
  * 		"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
  * 		"stateId": 1,
  * 		"stateName": "To Do"
  * 	}
	*   }
	*
	*
	*/
	public function editTicketAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("token", $content) || !array_key_exists("bugId", $content)
			|| !array_key_exists("title", $content) || !array_key_exists("description", $content)
			|| !array_key_exists("stateId", $content) || !array_key_exists("stateName", $content))
				return $this->setBadRequest("4.3.6", "Bugtracker", "editTicket", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.3.3", "Bugtracker", "editTicket"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$bug = $em->getRepository('MongoBundle:Bug')->find($content->bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.3.4", "Bugtracker", "postTicket", "Bad Parameter: bugId");

		if (!$this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError("4.3.9", "Bugtracker", "postTicket"));

		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setEditedAt(new DateTime('now'));

		if ($content->stateId != 0)
			$state = $em->getRepository("MongoBundle:BugState")->find($content->stateId);
		if ($state instanceof BugState)
			$bug->setStateId($content->stateId);
		else {
			$state = new BugState();
			$state->setName($content->stateName);

			$em->persist($state);
			$em->flush();

			$bug->setStateId($state->getId());
		}

		$em->persist($bug);
		$em->flush();

		$ticket = $bug->objectToArray();
		$ticket['state'] = $state->getName();
		foreach ($bug->getTags() as $key => $tag_value) {
			$ticket['tags'][] = $tag_value->objectToArray();
		}

		$participants = array();
		foreach ($bug->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}
		$ticket["users"] = $participants;


		$class = new NotificationController();

		$mdata['mtitle'] = "Bugtracker - Ticket edited";
		$mdata['mdesc'] = "The ticket ".$bug->getTitle()." has been edited";

		$wdata['type'] = "Bugtracker";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = "The ticket ".$bug->getTitle()." has been edited";

		$userNotif = array();
		foreach ($bug->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}

		if (count($userNotif) > 0)
			$class->pushNotification($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.4.1", "Bugtracker", "editTicket", "Complete Success", $ticket);
	}

	/**
	* @api {get} mongo/bugtracker/getcomments/:token/:id/:ticketId Get comments
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiDescription Get all comments of a bug ticket
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id project id
	* @apiParam {String} token client authentification token
	* @apiParam {int} ticketId commented ticket id
	*
	*
	*/
	public function getCommentsAction(Request $request, $token, $id, $ticketId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.4.3", "Bugtracker", "getComments"));
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError("4.4.9", "Bugtracker", "getComments"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.4.4", "Bugtracker", "getComments", "Bad Parameter: id");

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => $ticketId));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getComments");
		return $this->setSuccess("1.4.1", "Bugtracker", "getComments", "Complete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {post} mongo/bugtracker/postcomment Post comment
	* @apiName postComment
	* @apiGroup Bugtracker
	* @apiDescription Post comment on a bug ticket
	* @apiVersion 0.2.0
	*
	* @apiParam {int} projectId id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {String} title Comment title
	* @apiParam {String} description Comment content
	* @apiParam {int} parentId commented ticket id
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
  * 		"token": "ThisIsMyToken",
  * 		"projectId": 1,
  * 		"title": "J'ai un petit problème",
  * 		"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
  * 		"parentId": 1
  * 	}
	*   }
	*
	*
	*/
	public function postCommentAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("token", $content) || !array_key_exists("projectId", $content) || !array_key_exists("parentId", $content)
				|| !array_key_exists("title", $content) || !array_key_exists("description", $content))
				return $this->setBadRequest("4.5.6", "Bugtracker", "postComment", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.5.3", "Bugtracker", "postComments"));

		if (!$this->checkRoles($user, $content->projectId, "bugtracker"))
			return ($this->setNoRightsError("4.5.9", "Bugtracker", "postComments"));

		$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.5.4", "Bugtracker", "postComments", "Bad Parameter: projectId");

		$bug = new Bug();
		$bug->setProjects($project);
		$bug->setCreator($user);
		$bug->setParentId($content->parentId);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setCreatedAt(new DateTime('now'));

		$em->persist($bug);
		$em->flush();

		$ticket = $bug->objectToArray();

		$class = new NotificationController();

		$mdata['mtitle'] = "Bugtracker - Ticket Commented";
		$mdata['mdesc'] = "The ticket ".$bug->getTitle()." has been commented";

		$wdata['type'] = "Bugtracker";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = "The ticket ".$bug->getTitle()." has been commented";

		$userNotif = array();
		foreach ($bug->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}

		if (count($userNotif) > 0)
			$class->pushNotification($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.4.1", "Bugtracker", "postComment", "Complete Success", $bug);
	}

	/**
	* @api {put} mongo/bugtracker/editcomment/:id Edit comment
	* @apiName EditComment
	* @apiGroup Bugtracker
	* @apiDescription Edit a comment
	* @apiVersion 0.2.0
	*
	* @apiParam {int} projectId id of the project
	* @apiParam {String} token client authentification token
	*	@apiParam {int} commentId comment id to edit
	* @apiParam {String} title Comment title
	* @apiParam {String} description Comment content
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
  * 		"token": "ThisIsMyToken",
	* 		"projectId": 1,
  * 		"commentId": 1,
  * 		"title": "J'ai un petit problème",
  * 		"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?"
  * 	}
	*   }
	*
	*
	*/
	public function editCommentAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("token", $content) || !array_key_exists("projectId", $content) || !array_key_exists("commentId", $content)
				|| !array_key_exists("title", $content) || !array_key_exists("description", $content))
				return $this->setBadRequest("4.6.6", "Bugtracker", "editComments", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.6.3", "Bugtracker", "editComments"));

		if (!$this->checkRoles($user, $content->projectId, "bugtracker"))
			return ($this->setNoRightsError("4.6.9", "Bugtracker", "editComments"));

		$bug = $em->getRepository("MongoBundle:Bug")->find($content->commentId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.6.4", "Bugtracker", "editComments", "Bad Parameter: commentId");

		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setEditedAt(new DateTime('now'));

		$ticket = $bug->objectToArray();

		return $this->setSuccess("1.4.1", "Bugtracker", "editComment", "Complete Success", $bug);
	}

	/**
	* @api {put} mongo/bugtracker/setparticipants Set participants
	* @apiName setParticipants
	* @apiGroup Bugtracker
	* @apiDescription Assign/unassign users to a ticket
	* @apiVersion 0.2.0
	*
	* @apiParam {int} bugId bug id
	* @apiParam {string} token user authentication token
	* @apiParam {int[]} toAdd list of users' id to assign
	* @apiParam {int[]} toRemove list of users' id to unassign
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
	* 		"token": "ThisIsMyToken",
	* 		"bugId": 1,
	* 		"toAdd": [1, 15, 6],
	* 		"toRemove": []
	* 	}
	*   }
	*
	*
	*/
	public function setParticipantsAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("token", $content) || !array_key_exists("bugId", $content) || !array_key_exists("toAdd", $content) || !array_key_exists("toRemove", $content))
			return $this->setBadRequest("4.7.6", "Bugtracker", "setParticipants", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.7.3", "Bugtracker", "setParticipants"));

		$bug = $em->getRepository("MongoBundle:Bug")->find($content->bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.7.4", "Bugtracker", "setParticipants", "Bad Parameter: bugId");

		if (!$this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError("4.7.9", "Bugtracker", "setParticipants"));


		$class = new NotificationController();

		$mdata['mtitle'] = "Bugtracker - Ticket Assigned";
		$mdata['mdesc'] = "You have been assigned to ticket ".$bug->getTitle();

		$wdata['type'] = "Bugtracker";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = "You have been assigned to ticket ".$bug->getTitle();

		foreach ($content->toAdd as $key => $value) {
			$toAddUser = $em->getRepository("MongoBundle:User")->find($value);
			if ($toAddUser instanceof User)
			{
				foreach ($bug->getUsers() as $key => $value) {
					if (($user->getId()) == $toAddUser->getId())
						return $this->setBadRequest("4.7.7", "Bugtracker", "setParticipants", "Already in Database");
					}

				$bug->addUser($toAddUser);

				$userNotif = array($value);
				$class->pushNotification($userNotif, $mdata, $wdata, $em);
			}
		}

		$mdata['mtitle'] = "Bugtracker - Ticket Remove";
		$mdata['mdesc'] = "You have been removed of ticket ".$bug->getTitle();

		$wdata['type'] = "Bugtracker";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = "You have been removed of ticket ".$bug->getTitle();

		foreach ($content->toRemove as $key => $value) {
			$toRemoveuser = $em->getRepository("MongoBundle:User")->find($value);

			if ($toRemoveuser instanceof User)
			{
				$bug->removeUser($toRemoveuser);

				$userNotif = array($value);
				$class->pushNotification($userNotif, $mdata, $wdata, $em);
			}
		}

		$em->persist($bug);
		$em->flush();

		$object = $bug->objectToArray();
		$object['state'] = $em->getRepository("MongoBundle:BugState")->find($bug->getStateId())->objectToArray();
		$object['tags'] = array();
		foreach ($bug->getTags() as $key => $tag_value) {
			$object['tags'][] = $tag_value->objectToArray();
		}

		$participants = array();
		foreach ($bug->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}
		$object["users"] = $participants;

		return $this->setSuccess("1.4.1", "Bugtracker", "setParticipants", "Complete Success", $object);
	}

	/**
	* @api {delete} mongo/bugtracker/closeticket/:token/:id Close ticket / Remove comment
	* @apiName closeTicket
	* @apiGroup Bugtracker
	* @apiDescription Close a ticket or remove a comment
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the ticket/comment
	* @apiParam {String} token client authentification token
	*
	*
	*/
	public function closeTicketAction(Request $request, $token, $id)
	{
		$em = $this->get('doctrine_mongodb')->getManager();

		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.8.3", "Bugtracker", "closeTicket"));

		$bug = $em->getRepository("MongoBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.8.4", "Bugtracker", "closeTicket", "Bad Parameter: id");

		if (!$this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError("4.8.9", "Bugtracker", "closeTicket"));

		$bug->setDeletedAt(new DateTime('now'));

		$em->persist($bug);
		$em->flush();

		$class = new NotificationController();

		$mdata['mtitle'] = "Bugtracker - Ticket closed";
		$mdata['mdesc'] = "The ticket ".$bug->getTitle()." has been closed";

		$wdata['type'] = "Bugtracker";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = "The ticket ".$bug->getTitle()." has been closed";

		$userNotif = array();
		foreach ($bug->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}

		if (count($userNotif) > 0)
			$class->pushNotification($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.4.1", "Bugtracker", "closeTicket", "Complete Success", array("id" => $bug));
	}

	/**
	* @api {get} mongo/bugtracker/gettickets/:token/:id Get tickets
	* @apiName getTickets
	* @apiGroup Bugtracker
	* @apiDescription Get all tickets of a project
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	*
	*
	*/
	public function getTicketsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.9.3", "Bugtracker", "getTickets"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.9.4", "Bugtracker", "getTickets", "Bad Parameter: id");

		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError("4.9.9", "Bugtracker", "getTickets"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("MongoBundle:BugState")->find($value->getStateId())->objectToArray();
			$object['tags'] = array();
			foreach ($value->getTags() as $key => $tag_value) {
				$object['tags'][] = $tag_value->objectToArray();
			}

			$participants = array();
			foreach ($value->getUsers() as $key => $user_value) {
				$participants[] = array(
					"id" => $user_value->getId(),
					"name" => $user_value->getFirstname()." ".$user_value->getLastName(),
					"email" => $user_value->getEmail(),
					"avatar" => $user_value->getAvatar()
				);
			}
			$object["users"] = $participants;

			$ticketsArray[] = $object;
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} mongo/bugtracker/getlasttickets/:token/:id/:offset/:limit Get last tickets
	* @apiName getLastTickets
	* @apiGroup Bugtracker
	* @apiDescription Get X last tickets from offset Y
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	*
	*/
	public function getLastTicketsAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.10.3", "Bugtracker", "getLastTickets"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.10.4", "Bugtracker", "getLastTickets", "Bad Parameter: id");

		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError("4.10.9", "Bugtracker", "getLastTickets"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("MongoBundle:BugState")->find($value->getStateId())->objectToArray();
			$object['tags'] = array();
			foreach ($value->getTags() as $key => $tag_value) {
				$object['tags'][] = $tag_value->objectToArray();
			}

			$participants = array();
			foreach ($value->getUsers() as $key => $user_value) {
				$participants[] = array(
					"id" => $user_value->getId(),
					"name" => $user_value->getFirstname()." ".$user_value->getLastName(),
					"email" => $user_value->getEmail(),
					"avatar" => $user_value->getAvatar()
				);
			}
			$object["users"] = $participants;

			$ticketsArray[] = $object;
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getLastTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getLastTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} /V0.2/bugtracker/getlastclosedtickets/:token/:id/:offset/:limit Get last closed tickets
	* @apiName getLastClosedTickets
	* @apiGroup Bugtracker
	* @apiDescription Get X last closed tickets from offset Y
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	*
	*/
	public function getLastClosedTicketsAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.11.3", "Bugtracker", "getLastClosedTickets"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.11.4", "Bugtracker", "getLastClosedTickets", "Bad Parameter: id");

		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError("4.11.9", "Bugtracker", "getLastClosedTickets"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects" => $project, "parentId" => null), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			if ($value->getDeletedAt() != null)
			{
				$object = $value->objectToArray();
				$object['state'] = $em->getRepository("MongoBundle:BugState")->find($value->getStateId())->objectToArray();
				$object['tags'] = array();
				foreach ($value->getTags() as $key => $tag_value) {
					$object['tags'][] = $tag_value->objectToArray();
				}

				$participants = array();
				foreach ($value->getUsers() as $key => $user_value) {
					$participants[] = array(
						"id" => $user_value->getId(),
						"name" => $user_value->getFirstname()." ".$user_value->getLastName(),
						"email" => $user_value->getEmail(),
						"avatar" => $user_value->getAvatar()
					);
				}
				$object["users"] = $participants;

				$ticketsArray[] = $object;
			}
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getLastClosedTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getLastClosedTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} mongo/bugtracker/getticketsbyuser/:token/:id/:user Get tickets by user
	* @apiName getTicketsByUser
	* @apiGroup Bugtracker
	*	@apiDescription Get Tickets asssigned to a user for a project
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the project
	* @apiParam {int} user id of the user
	* @apiParam {String} token client authentification token
	*
	*
	*/
	public function getTicketsByUserAction(Request $request, $token, $id, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.12.3", "Bugtracker", "getTicketsByUser"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.12.4", "Bugtracker", "getTicketsByUser", "Bad Parameter: id");

		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError("4.12.9", "Bugtracker", "getTicketsByUser"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "user" => $user));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("MongoBundle:BugState")->find($value->getStateId())->objectToArray();
			$object['tags'] = array();
			foreach ($value->getTags() as $key => $tag_value) {
				$object['tags'][] = $tag_value->objectToArray();
			}

			$participants = array();
			foreach ($value->getUsers() as $key => $user_value) {
				$participants[] = array(
					"id" => $user_value->getId(),
					"name" => $user_value->getFirstname()." ".$user_value->getLastName(),
					"email" => $user_value->getEmail(),
					"avatar" => $user_value->getAvatar()
				);
			}
			$object["users"] = $participants;

			$ticketsArray[] = $object;
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getTicketsByUser");
		return $this->setSuccess("1.4.1", "Bugtracker", "getTicketsByUser", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} mongo/bugtracker/getticketsbystate/:token/:id/:state/:offset/:limit Get tickets by status
	* @apiName getTicketsByStatus
	* @apiGroup Bugtracker
	* @apiDescription Get X last tickets from offset Y with status Z
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {int} state status id
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	*
	*/
	public function getTicketsByStateAction(Request $request, $token, $id, $state, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.13.3", "Bugtracker", "getTicketsByStatus"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.13.4", "Bugtracker", "getTicketsByStatus", "Bad Parameter: id");

		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError("4.13.9", "Bugtracker", "getTicketsByStatus"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null, "stateId" => $state), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("MongoBundle:BugState")->find($value->getStateId())->objectToArray();
			$object['tags'] = array();
			foreach ($value->getTags() as $key => $tag_value) {
				$object['tags'][] = $tag_value->objectToArray();
			}

			$participants = array();
			foreach ($value->getUsers() as $key => $user_value) {
				$participants[] = array(
					"id" => $user_value->getId(),
					"name" => $user_value->getFirstname()." ".$user_value->getLastName(),
					"email" => $user_value->getEmail(),
					"avatar" => $user_value->getAvatar()
				);
			}
			$object["users"] = $participants;

			$ticketsArray[] = $object;
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getTicketsByStatus");
		return $this->setSuccess("1.4.1", "Bugtracker", "getTicketsByStatus", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} /V0.2/bugtracker/getstates/:token Get status
	* @apiName getStates
	* @apiGroup Bugtracker
	* @apiDescription Get tickets status
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client authentification token
	*
	*
	*/
	public function getStatesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.14.3", "Bugtracker", "getStates"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$states = $em->getRepository("MongoBundle:BugState")->findAll();

		$states_array = array();
		foreach ($states as $key => $value) {
			$states_array[] = $value->objectToArray();
		}

		if (count($states_array) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getStates");
		return $this->setSuccess("1.4.1", "Bugtracker", "getStates", "Commplete Success", array("array" => $states_array));
	}

	/*
	 * --------------------------------------------------------------------
	 *														TAGS MANAGEMENT
	 * --------------------------------------------------------------------
	*/

	/**
	* @-api {post} /mongo/bugtracker/tagcreation Create a tag
	* @apiName tagCreation
	* @apiGroup Bugtracker
	* @apiVersion 0.11.3
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "1fez4c5ze31e5f14cze31fc",
	*			"projectId": 2,
	*			"name": "Urgent"
	*		}
	*	}
	*
	*/
	public function tagCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('name', $content) && !array_key_exists('token', $content) && !array_key_exists('projectId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "bugtracker"))
			return ($this->setNoRightsError());
		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		$tag = new Tag();
		$tag->setName($content->name);
		$tag->setProject($project);

		$em->persist($tag);
		$em->flush();

		$id = $tag->getId();

		return new JsonResponse(array("tag_id" => $id));
	}

	/**
	* @-api {put} /mongo/bugtracker/tagupdate Update a tag
	* @apiName tagUpdate
	* @apiGroup Bugtracker
	* @apiVersion 0.11.3
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "1fez4c5ze31e5f14cze31fc",
	*		"tagId": 1,
	*		"name": "ASAP"
	* 	}
	*
	*/
	public function tagUpdateAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('name', $content) && !array_key_exists('token', $content) && !array_key_exists('tagId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:Tag')->find($content->tagId);

		if ($tag === null)
		{
			throw new NotFoundHttpException("The tag with id ".$content->tagId." doesn't exist");
		}

		$projectId = $tag->getProject()->getId();
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());

		$tag->setName($content->name);
		$em->flush();

		$id = $tag->getId();
		$name = $tag->getName();

		return new JsonResponse(array("tag_id" => $id, "tag_name" => $name));
	}

	/**
	* @-api {get} /mongo/bugtracker/taginformations/:token/:tagId Get a tag informations
	* @apiName tagInformations
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*
	*/
	public function getTagInfosAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:Tag')->find($tagId);

		if ($tag === null)
		{
			throw new NotFoundHttpException("The tag with id ".$tagId." doesn't exist");
		}

		$projectId = $tag->getProject()->getId();
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());

		$id = $tag->getId();
		$name = $tag->getName();

		return new JsonResponse(array("id" => $id, "name" => $name));
	}

	/**
	* @-api {delete} /mongo/bugtracker/deletetag/:token/:tagId Delete a tag
	* @apiName deleteTag
	* @apiGroup Bugtracker
	* @apiVersion 0.11.3
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*/
	public function deleteTagAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:Tag')->find($tagId);

		if ($tag === null)
		{
			throw new NotFoundHttpException("The tag with id ".$tagId." doesn't exist");
		}

		$project = $tag->getProject();
		if ($project === null)
			return ($this->setNoRightsError());
		$projectId = $project->getId();
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());

		$em->remove($tag);
		$em->flush();

		return new JsonResponse("Tag deleted.");
	}

	/**
	* @-api {put} /mongo/bugtracker/assigntag Assign a tag to a bug
	* @apiName assignTag
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} bugId Id of the bug ticket
	* @apiParam {Number} tagId Id of the tag
	*
	*/
	public function assignTagAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('tagId', $content) && !array_key_exists('token', $content) && !array_key_exists('bugId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
		$bug = $em->getRepository('MongoBundle:Bug')->find($content->bugId);

		if ($bug === null)
		{
			throw new NotFoundHttpException("The bug with id ".$content->bugId." doesn't exist");
		}

		$projectId = $bug->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());

		$tagToAdd = $em->getRepository('MongoBundle:Tag')->find($content->tagId);

		if ($tagToAdd === null)
		{
			throw new NotFoundHttpException("The tag with id ".$content->tagId." doesn't exist");
		}

		$tags = $bug->getTags();
		foreach ($tags as $tag) {
			if ($tag === $tagToAdd)
			{
				return new JsonResponse('The tag is already assign to the bug', JsonResponse::HTTP_BAD_REQUEST);
			}
		}

		$bug->addTag($tagToAdd);

		$em->flush();
		return new JsonResponse("Tag assigned to bug successfull!");
	}

	/**
	* @-api {delete} /mongo/bugtracker/removetag/:token/:bugId/:tagId Remove a tag to a bug
	* @apiName removeTag
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} bugId Id of the bug
	* @apiParam {Number} tagId Id of the tag
	*
	*/
	public function removeTagAction(Request $request, $token, $bugId, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb')->getManager();
		$bug = $em->getRepository('MongoBundle:Bug')->find($bugId);

		if ($bug === null)
		{
			throw new NotFoundHttpException("The bug with id ".$bugId." doesn't exist");
		}

		$projectId = $bug->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());

		$tagToRemove = $em->getRepository('MongoBundle:Tag')->find($tagId);

		if ($tagToRemove === null)
		{
			throw new NotFoundHttpException("The tag with id ".$tagId." doesn't exist");
		}

		$tags = $bug->getTags();
		$isAssign = false;
		foreach ($tags as $tag) {
			if ($tag === $tagToRemove)
			{
				$isAssign = true;
			}
		}

		if ($isAssign === false)
		{
			throw new NotFoundHttpException("The tag with id ".$tagId." is not assigned to the bug");
		}

		$bug->removeTag($tagToRemove);

		$em->flush();
		return new JsonResponse("Tag removed from the bug.");
	}

	/**
	* @-api {get} /mongo/bugtracker/getprojecttags/:token/:projectId Get all the tags for a project
	* @apiName getProjectTags
	* @apiGroup Bugtracker
	* @apiVersion 0.11.3
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	*/
	public function getProjectTagsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());
		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Tag');

		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId);
		$tags = $qb->getQuery()->execute();

		if ($tags === null)
		{
			throw new NotFoundHttpException("There are no tags for the project with id ".$projectId);
		}
		if (count($tags) == 0)
		{
			return new JsonResponse((Object)array());
		}

		$arr = array();
		$i = 1;

		foreach ($tags as $t) {
			$id = $t->getId();
			$name = $t->getName();

			$arr["Tag ".$i] = array("id" => $id, "name" => $name);
			$i++;
		}

		return new JsonResponse($arr);
	}

}
