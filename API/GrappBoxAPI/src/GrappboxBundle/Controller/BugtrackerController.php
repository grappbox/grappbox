<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use GrappboxBundle\Controller\RolesAndTokenVerificationController;

use GrappboxBundle\Entity\User;
use GrappboxBundle\Entity\Bug;
use GrappboxBundle\Entity\BugState;
use GrappboxBundle\Entity\Tag;
use GrappboxBundle\Entity\Project;
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
	* @api {get} /V0.2/bugtracker/getticket/:token/:id Get ticket
	* @apiName getTicket
	* @apiGroup Bugtracker
	* @apiDescription Get ticket informations, tags and assigned users
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id ticket's id
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {int} creatorId author id
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {Object} state Ticket state
	* @apiSuccess {int} state.id state id
	* @apiSuccess {String} state.name state name
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {Object[]} users assigned user list
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.name user full name
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - getTicket - Complete Success"
	*  },
	*  "data": {
	*    "id": 1,
	*    "creator": { "id": 13, "fullname": "John Doe" },
	*    "projectId": 1,
	*    "title": "Ticket de Test",
	*    "description": "Ceci est un ticket de test",
	*    "parentId": null,
	*    "createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    "editedAt": { "date": "2015-12-29 11:54:57", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    "deletedAt": null,
	*    "state": { "id": 1, "name": "Waiting" },
	*    "tags": [
	*      { "id": 1, "name": "To Do", "projectId": 1 },
	*      { "id": 4, "name": "ASAP", "projectId": 1 }
	*    ],
	*    "users": [
	*      { "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": "100111010000110111 ....." },
	*      { "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null }
	*    ]
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.1.3",
	*			"return_message": "Bugtracker - getTicket - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.1.4",
	*			"return_message": "Bugtracker - getTicket - Bad Parameter: id"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.1.9",
	*			"return_message": "Bugtracker - getTicket - Insufficient Rights"
  *		}
	* 	}
	*
	*/
	public function getTicketAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.1.3", "Bugtracker", "getTicket"));

		$em = $this->getDoctrine()->getManager();
		$ticket = $em->getRepository("GrappboxBundle:Bug")->find($id);
		if (!($ticket instanceof Bug))
			return $this->setBadRequest("4.1.4", "Bugtracker", "getTicket", "Bad Parameter: id");
		if (!$this->checkRoles($user, $ticket->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError("4.1.9", "Bugtracker", "getTicket"));

		$object = $ticket->objectToArray();
		$object['state'] = $em->getRepository("GrappboxBundle:BugState")->find($ticket->getStateId())->objectToArray();
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

		return $this->setSuccess("1.4.3", "Bugtracker", "getTicket", "Complete Success", $object);
	}

	/**
	* @api {post} /V0.2/bugtracker/postticket Post ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
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
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {int} creatorId author id
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {Object} state Ticket state
	* @apiSuccess {int} state.id state id
	* @apiSuccess {String} state.name state name
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {Object[]} users assigned user list
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.name user full name
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - postTicket - Complete Success"
	*  },
	*  "data": {
	*    "id": 1,
	*    "creator": { "id": 13, "fullname": "John Doe" },
	*    "projectId": 1,
	*    "title": "Ticket de Test",
	*    "description": "Ceci est un ticket de test",
	*    "parentId": null,
	*    "createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    "editedAt": null,
	*    "deletedAt": null,
	*    "state": { "id": 1, "name": "Waiting" },
	*    "tags": [],
	*    "users": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.2.3",
	*			"return_message": "Bugtracker - postTicket - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: projectId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.2.4",
	*			"return_message": "Bugtracker - postTicket - Bad Parameter: projectId"
  *		}
	* 	}
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.2.6",
	*			"return_message": "Bugtracker - postTicket - Missing Parameter"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.2.9",
	*			"return_message": "Bugtracker - postTicket - Insufficient Rights"
  *		}
	* 	}
	*
	*/
	public function postTicketAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->getDoctrine()->getManager();

		if (!array_key_exists("token", $content) || !array_key_exists("projectId", $content)
			|| !array_key_exists("title", $content) || !array_key_exists("description", $content)
			|| !array_key_exists("stateId", $content) || !array_key_exists("stateName", $content))
				return $this->setBadRequest("4.2.6", "Bugtracker", "postTicket", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.2.3", "Bugtracker", "postTicket"));

		if (!$this->checkRoles($user, $content->projectId, "bugtracker"))
			return ($this->setNoRightsError("4.2.9", "Bugtracker", "postTicket"));

		$project = $em->getRepository("GrappboxBundle:Project")->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.2.4", "Bugtracker", "postTicket", "Bad Parameter: projectId");

		$bug = new Bug();
		$bug->setProjects($project);
		$bug->setCreator($user);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setCreatedAt(new DateTime('now'));

		if (array_key_exists("stateId", $content) && $content->stateId != 0)
			$state = $em->getRepository("GrappboxBundle:BugState")->find($content->stateId);
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

		return $this->setCreated("1.4.3", "Bugtracker", "postTicket", "Complete Success", $ticket);
	}

	/**
	* @api {post} /V0.2/bugtracker/editticket Edit ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
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
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {int} creatorId author id
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {Object} state Ticket state
	* @apiSuccess {int} state.id state id
	* @apiSuccess {String} state.name state name
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {Object[]} users assigned user list
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.name user full name
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - editTicket - Complete Success"
	*  },
	*  "data": {
	*    "id": 1,
	*    "creator": { "id": 13, "fullname": "John Doe" },
	*    "projectId": 1,
	*    "title": "Ticket de Test",
	*    "description": "Ceci est un ticket de test",
	*    "parentId": null,
	*    "createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    "editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    "deletedAt": null,
	*    "state": { "id": 1, "name": "Waiting" },
	*    "tags": [],
	*    "users": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.3.3",
	*			"return_message": "Bugtracker - editTicket - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: big_Iid
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.3.4",
	*			"return_message": "Bugtracker - editTicket - Bad Parameter: bugId"
  *		}
	* 	}
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.3.6",
	*			"return_message": "Bugtracker - editTicket - Missing Parameter"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.3.9",
	*			"return_message": "Bugtracker - editTicket - Insufficient Rights"
  *		}
	* 	}
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

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('GrappboxBundle:Bug')->find($content->bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.3.4", "Bugtracker", "postTicket", "Bad Parameter: bugId");

		if (!$this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError("4.3.9", "Bugtracker", "postTicket"));

		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setEditedAt(new DateTime('now'));

		if ($content->stateId != 0)
			$state = $em->getRepository("GrappboxBundle:BugState")->find($content->stateId);
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

		return $this->setSuccess("1.4.3", "Bugtracker", "editTicket", "Complete Success", $ticket);
	}

	/**
	* @-api {get} /V0.11/bugtracker/getcomments/:token/:id/:ticketId Get comments of a ticket
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id project id
	* @apiParam {String} token client authentification token
	* @apiParam {int} ticketId commented ticket id
	*
	* @apiSuccess {Object[]} tickets array of all the ticket's comments
	* @apiSuccess {int} tickets.id Ticket id
	* @apiSuccess {int} tickets.creatorId author id
	* @apiSuccess {int} tickets.projectId project id
  * @apiSuccess {int} tickets.parentId parent message id
	* @apiSuccess {int} tickets.title comment title
	* @apiSuccess {int} tickets.description comment message
	* @apiSuccess {DateTime} tickets.createdAt Message creation date
	* @apiSuccess {DateTime} tickets.editedAt Message edition date
	* @apiSuccess {DateTime} tickets.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"comments": [
	*		{"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": 150,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null
	*			},
	*		{"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": 150,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null
	*			},
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
	public function getCommentsAction(Request $request, $token, $id, $ticketId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("GrappboxBundle:Project")->find($id);
		//TODO check project id
		$tickets = $em->getRepository("GrappboxBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => $ticketId));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		return new JsonResponse(array("comments" => $ticketsArray));
	}

	/**
	* @-api {post} /V0.11/bugtracker/postcomment/:id Post a comment
	* @apiName postComment
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {String} title Comment title
	* @apiParam {String} description Comment content
	* @apiParam {int} parentId commented ticket id
	*
	* @apiSuccess {int} id Comment id
	* @apiSuccess {Object} Comment Comment object
	* @apiSuccess {int} Comment.id Comment id
	* @apiSuccess {int} Comment.creatorId author id
	* @apiSuccess {int} Comment.projectId project id
	* @apiSuccess {String} Comment.title Comment title
	* @apiSuccess {String} Comment.description Comment content
	* @apiSuccess {int} Comment.parentId parent Ticket id
	* @apiSuccess {DateTime} Comment.createdAt Comment creation date
	* @apiSuccess {DateTime} Comment.editedAt Comment edition date
	* @apiSuccess {DateTime} Comment.deletedAt Comment deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"comment": {"id": "154","creatorId": 12, "projectId": 14, "parentId": 150,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null
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
	public function postCommentAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$project = $em->getRepository("GrappboxBundle:Project")->find($id);

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

		return new JsonResponse(array("comment"=>$ticket));
	}

	/**
	* @-api {post} /V0.11/bugtracker/editcomment/:id Edit a comment
	* @apiName EditComment
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	*	@apiParam {int} commentId comment id to edit
	* @apiParam {String} title Comment title
	* @apiParam {String} description Comment content
	*
	* @apiSuccess {int} id Comment id
	* @apiSuccess {Object} Comment Comment object
	* @apiSuccess {int} Comment.id Comment id
	* @apiSuccess {int} Comment.creatorId author id
	* @apiSuccess {int} Comment.projectId project id
	* @apiSuccess {String} Comment.title Comment title
	* @apiSuccess {String} Comment.description Comment content
	* @apiSuccess {int} Comment.parentId parent Ticket id
	* @apiSuccess {DateTime} Comment.createdAt Comment creation date
	* @apiSuccess {DateTime} Comment.editedAt Comment edition date
	* @apiSuccess {DateTime} Comment.deletedAt Comment deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"comment": {"id": "154","creatorId": 12, "projectId": 14, "parentId": 150,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null
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
	public function editCommentAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$bug = $em->getRepository("GrappboxBundle:Bug")->find($content->commentId);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setEditedAt(new DateTime('now'));

		$ticket = $bug->objectToArray();

		return new JsonResponse(array("comment"=>$ticket));
	}

	/**
	* @-api {post} /V0.11/bugtracker/setparticipants/:id Add/remove users to the ticket
	* @apiName setParticipants
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id bug id
	* @apiParam {string} token user authentication token
	* @apiParam {string[]} toAdd list of users' email to add
	* @apiParam {int[]} toRemove list of users' id to remove
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {int} creatorId author id
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {Object} state Ticket state
	* @apiSuccess {int} state.id state id
	* @apiSuccess {String} state.name state name
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {Object[]} users assigned user list
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {string} users.name user full name
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"ticket": {"id": "154","creatorId": 12, "projectId": 14, "parentId": null,
	*		"title": "function getUser not working",
	*		"description": "the function does not answer the right way, fix it ASAP !",
	*		"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"deletedAt": null,
	*		"state": {"id": 1, "name": "Waiting"},
	*		"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}],
	*		"users": [
	*			{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*			{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*		]
	*		}
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
	public function setParticipantsAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository("GrappboxBundle:Bug")->find($id);
		if (!$this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError());


		$class = new NotificationController();

		$mdata['mtitle'] = "Bugtracker - Ticket Assigned";
		$mdata['mdesc'] = "You have been assigned to ticket ".$bug->getTitle();

		$wdata['type'] = "Bugtracker";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = "You have been assigned to ticket ".$bug->getTitle();

		foreach ($content->toAdd as $key => $value) {
			$toAddUser = $em->getRepository("GrappboxBundle:User")->find($value);
			if ($toAddUser instanceof User)
			{
				foreach ($bug->getUsers() as $key => $value) {
					if (($user->getId()) == $toAddUser->getId())
						return $this->setBadRequest("User already in the list");
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
			$toRemoveuser = $em->getRepository("GrappboxBundle:User")->find($value);

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
		$object['state'] = $em->getRepository("GrappboxBundle:BugState")->find($bug->getStateId())->objectToArray();
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

		return new JsonResponse(array("ticket"=>$object));
	}

	/**
	* @-api {delete} /V0.11/bugtracker/closeticket/:token/:id Close ticket or delete comment
	* @apiName closeTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id id of the ticket
	* @apiParam {String} token client authentification token
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
	public function closeTicketAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("XXX", "xxx", "XXX"));
		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository("GrappboxBundle:Bug")->find($id);
		if (!$this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError("XXX", "xxx", "XXX"));

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

		return new JsonResponse('Success');
	}

	/**
	* @-api {get} /V0.11/bugtracker/gettickets/:token/:id Get all tickets of a project
	* @apiName getTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} tickets array of all the tickets' project
	* @apiSuccess {int} tickets.id Ticket id
	* @apiSuccess {int} tickets.creatorId author id
	* @apiSuccess {int} tickets.projectId project id
	* @apiSuccess {String} tickets.title Ticket title
	* @apiSuccess {String} tickets.description Ticket content
	* @apiSuccess {int} tickets.parentId parent Ticket id
	* @apiSuccess {DateTime} tickets.createdAt Ticket creation date
	* @apiSuccess {DateTime} tickets.editedAt Ticket edition date
	* @apiSuccess {DateTime} tickets.deletedAt Ticket deletion date
	* @apiSuccess {Object} tickets.state Ticket state
	* @apiSuccess {Object[]} tickets.tags Ticket tags list
	* @apiSuccess {int} tickets.tags.id Ticket tags id
	* @apiSuccess {String} tickets.tags.name Ticket tags name
	* @apiSuccess {Object[]} tickets.users assigned user list
	*	@apiSuccess {int} tickets.users.id user id
	*	@apiSuccess {string} tickets.users.name user full name
	*	@apiSuccess {string} tickets.users.email user email
	*	@apiSuccess {string} tickets.users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*			"tickets" : [
	*		{"id": "154","creatorId": 12, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
	*		{"id": "158","creatorId": 12, "projectId": 14, "parentId": null,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
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
	public function getTicketsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("GrappboxBundle:Project")->find($id);
		//TODO check bad project id
		$tickets = $em->getRepository("GrappboxBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("GrappboxBundle:BugState")->find($value->getStateId())->objectToArray();
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

		return new JsonResponse(array("tickets" => $ticketsArray));
	}

	/**
	* @-api {get} /V0.11/bugtracker/getlasttickets/:token/:id/:offset/:limit Get X last tickets from offset Y
	* @apiName getLastTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	* @apiSuccess {Object[]} tickets array of all the tickets' project
	* @apiSuccess {int} tickets.id Ticket id
	* @apiSuccess {int} tickets.creatorId author id
	* @apiSuccess {int} tickets.projectId project id
	* @apiSuccess {String} tickets.title Ticket title
	* @apiSuccess {String} tickets.description Ticket content
	* @apiSuccess {int} tickets.parentId parent Ticket id
	* @apiSuccess {DateTime} tickets.createdAt Ticket creation date
	* @apiSuccess {DateTime} tickets.editedAt Ticket edition date
	* @apiSuccess {DateTime} tickets.deletedAt Ticket deletion date
	* @apiSuccess {Object} tickets.state Ticket state
	* @apiSuccess {Object[]} tickets.tags Ticket tags list
	* @apiSuccess {int} tickets.tags.id Ticket tags id
	* @apiSuccess {String} tickets.tags.name Ticket tags name
	* @apiSuccess {Object[]} tickets.users assigned user list
	*	@apiSuccess {int} tickets.users.id user id
	*	@apiSuccess {string} tickets.users.name user full name
	*	@apiSuccess {string} tickets.users.email user email
	*	@apiSuccess {string} tickets.users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"tickets": [
	*		{"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
	*		{"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": null,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
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
	public function getLastTicketsAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("GrappboxBundle:Project")->find($id);
		//TODO check project id
		$tickets = $em->getRepository("GrappboxBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("GrappboxBundle:BugState")->find($value->getStateId())->objectToArray();
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

		return new JsonResponse(array("tickets" => $ticketsArray));
	}

	/**
	* @-api {get} /V0.11/bugtracker/getlastclosedtickets/:token/:id/:offset/:limit Get X last closed tickets from offset Y
	* @apiName getLastClosedTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.11.2
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	* @apiSuccess {Object[]} tickets array of all the tickets' project
	* @apiSuccess {int} tickets.id Ticket id
	* @apiSuccess {int} tickets.creatorId author id
	* @apiSuccess {int} tickets.projectId project id
	* @apiSuccess {String} tickets.title Ticket title
	* @apiSuccess {String} tickets.description Ticket content
	* @apiSuccess {int} tickets.parentId parent Ticket id
	* @apiSuccess {DateTime} tickets.createdAt Ticket creation date
	* @apiSuccess {DateTime} tickets.editedAt Ticket edition date
	* @apiSuccess {DateTime} tickets.deletedAt Ticket deletion date
	* @apiSuccess {Object} tickets.state Ticket state
	* @apiSuccess {Object[]} tickets.tags Ticket tags list
	* @apiSuccess {int} tickets.tags.id Ticket tags id
	* @apiSuccess {String} tickets.tags.name Ticket tags name
	* @apiSuccess {Object[]} tickets.users assigned user list
	*	@apiSuccess {int} tickets.users.id user id
	*	@apiSuccess {string} tickets.users.name user full name
	*	@apiSuccess {string} tickets.users.email user email
	*	@apiSuccess {string} tickets.users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"tickets": [
	*		{"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
	*		{"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": null,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
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
	public function getLastClosedTicketsAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("GrappboxBundle:Project")->find($id);
		//TODO check project id
		$tickets = $em->getRepository("GrappboxBundle:Bug")->findBy(array("projects" => $project, "parentId" => null), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			if ($value->getDeletedAt() != null)
			{
				$object = $value->objectToArray();
				$object['state'] = $em->getRepository("GrappboxBundle:BugState")->find($value->getStateId())->objectToArray();
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

		return new JsonResponse(array("tickets" => $ticketsArray));
	}

	/**
	* @-api {get} /V0.11/bugtracker/getticketsbyuser/:token/:id/:user Get Tickets asssigned to a user for a project
	* @apiName getTicketsByUser
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the project
	* @apiParam {int} user id of the user
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {Object[]} tickets array of all the tickets' project
	* @apiSuccess {int} tickets.id Ticket id
	* @apiSuccess {int} tickets.creatorId author id
	* @apiSuccess {int} tickets.userId assigned user id
	* @apiSuccess {int} tickets.projectId project id
	* @apiSuccess {String} tickets.title Ticket title
	* @apiSuccess {String} tickets.description Ticket content
	* @apiSuccess {int} tickets.parentId parent Ticket id
	* @apiSuccess {DateTime} tickets.createdAt Ticket creation date
	* @apiSuccess {DateTime} tickets.editedAt Ticket edition date
	* @apiSuccess {DateTime} tickets.deletedAt Ticket deletion date
	* @apiSuccess {Object} tickets.state Ticket state
	* @apiSuccess {Object[]} tickets.tags Ticket tags list
	* @apiSuccess {int} tickets.tags.id Ticket tags id
	* @apiSuccess {String} tickets.tags.name Ticket tags name
	* @apiSuccess {Object[]} tickets.users assigned user list
	*	@apiSuccess {int} tickets.users.id user id
	*	@apiSuccess {string} tickets.users.name user full name
	*	@apiSuccess {string} tickets.users.email user email
	*	@apiSuccess {string} tickets.users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"tickets": [
	*		{"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
	*		{"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": null,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
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
	public function getTicketsByUserAction(Request $request, $token, $id, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("GrappboxBundle:Project")->find($id);
		//TODO check project id
		$tickets = $em->getRepository("GrappboxBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "user" => $user ));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("GrappboxBundle:BugState")->find($value->getStateId())->objectToArray();
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

		return new JsonResponse(array("tickets" => $ticketsArray));
	}

	/**
	* @-api {get} /V0.11/bugtracker/getticketsbystate/:token/:id/:state/:offset/:limit Get X last tickets from offset Y with status Z
	* @apiName getTicketsByStatus
	* @apiGroup Bugtracker
	* @apiVersion 0.11.3
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {int} state status id
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	* @apiSuccess {Object[]} tickets array of all the tickets' project
	* @apiSuccess {int} tickets.id Ticket id
	* @apiSuccess {int} tickets.creatorId author id
	* @apiSuccess {int} tickets.projectId project id
	* @apiSuccess {String} tickets.title Ticket title
	* @apiSuccess {String} tickets.description Ticket content
	* @apiSuccess {int} tickets.parentId parent Ticket id
	* @apiSuccess {DateTime} tickets.createdAt Ticket creation date
	* @apiSuccess {DateTime} tickets.editedAt Ticket edition date
	* @apiSuccess {DateTime} tickets.deletedAt Ticket deletion date
	* @apiSuccess {Object} tickets.state Ticket state
	* @apiSuccess {Object[]} tickets.tags Ticket tags list
	* @apiSuccess {int} tickets.tags.id Ticket tags id
	* @apiSuccess {String} tickets.tags.name Ticket tags name
	* @apiSuccess {Object[]} tickets.users assigned user list
	*	@apiSuccess {int} tickets.users.id user id
	*	@apiSuccess {string} tickets.users.name user full name
	*	@apiSuccess {string} tickets.users.email user email
	*	@apiSuccess {string} tickets.users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"tickets": [
	*		{"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
	*		{"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": null,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
	*			},
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
	public function getTicketsByStateAction(Request $request, $token, $id, $state, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();

		$project = $em->getRepository("GrappboxBundle:Project")->find($id);
		//TODO check project id
		$tickets = $em->getRepository("GrappboxBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null, "stateId" => $state), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("GrappboxBundle:BugState")->find($value->getStateId())->objectToArray();
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

		return new JsonResponse(array("tickets" => $ticketsArray));
	}

/*
* --------------------------------------------------------------------
*														TAGS MANAGEMENT
* --------------------------------------------------------------------
*/
	/**
	* @-api {get} /V0.11/bugtracker/getstates/:token Get Tickets Status
	* @apiName getStates
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token client authentification token
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
	public function getStatesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$states = $em->getRepository("GrappboxBundle:BugState")->findAll();

		$states_array = array();
		foreach ($states as $key => $value) {
			$states_array[] = $value->objectToArray();
		}

		return new JsonResponse($states_array);
	}

	/**
	* @-api {post} /V0.11/bugtracker/tagcreation Create a tag
	* @apiName tagCreation
	* @apiGroup Bugtracker
	* @apiVersion 0.11.3
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "1fez4c5ze31e5f14cze31fc",
	*		"projectId": 2,
	*		"name": "Urgent"
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"tag_id" : 1
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
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
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);

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
	* @-api {put} /V0.11/bugtracker/tagupdate Update a tag
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
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"tag_id" : 1,
	*		"tag_name": "ASAP"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
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
		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($content->tagId);

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
	* @-api {get} /V0.11/bugtracker/taginformations/:token/:tagId Get a tag informations
	* @apiName tagInformations
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*
		* @apiSuccess {Number} id Id of the tag
		* @apiSuccess {String} name Name of the tag
		*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"id": 1,
	*		"name": "To Do"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
	*/
	public function getTagInfosAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($tagId);

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
	* @-api {delete} /V0.11/bugtracker/deletetag/:token/:tagId Delete a tag
	* @apiName deleteTag
	* @apiGroup Bugtracker
	* @apiVersion 0.11.3
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
		*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Tag deleted."
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
	*/
	public function deleteTagAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($tagId);

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
	* @-api {put} /V0.11/bugtracker/assigntag Assign a tag to a bug
	* @apiName assignTag
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} bugId Id of the bug ticket
	* @apiParam {Number} tagId Id of the tag
		*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Tag assigned to bug successfull!"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No bug found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The bug with id X doesn't exist"
	* 	}
	* @apiErrorExample Tag already assigned
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"The tag is already assign to the bug"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
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

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('GrappboxBundle:Bug')->find($content->bugId);

		if ($bug === null)
		{
			throw new NotFoundHttpException("The bug with id ".$content->bugId." doesn't exist");
		}

		$projectId = $bug->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());

		$tagToAdd = $em->getRepository('GrappboxBundle:Tag')->find($content->tagId);

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
	* @-api {delete} /V0.11/bugtracker/removetag/:token/:bugId/:tagId Remove a tag to a bug
	* @apiName removeTag
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} bugId Id of the bug
	* @apiParam {Number} tagId Id of the tag
		*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Tag removed from the bug."
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No bug found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The bug with id X doesn't exist"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
	* @apiErrorExample No tag found on the bug
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X is not assigned to the bug"
	* 	}
	*/
	public function removeTagAction(Request $request, $token, $bugId, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('GrappboxBundle:Bug')->find($bugId);

		if ($bug === null)
		{
			throw new NotFoundHttpException("The bug with id ".$bugId." doesn't exist");
		}

		$projectId = $bug->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());

		$tagToRemove = $em->getRepository('GrappboxBundle:Tag')->find($tagId);

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
	* @-api {get} /V0.11/bugtracker/getprojecttags/:token/:projectId Get all the tags for a project
	* @apiName getProjectTags
	* @apiGroup Bugtracker
	* @apiVersion 0.11.3
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} Tag Array of tag
		* @apiSuccess {Number} Tag.id Id of the tag
		* @apiSuccess {String} Tag.name Name of the tag
		*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Tag 1": {
	*			"id": 1,
	*			"name": "To Do"
	*		}
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No tags found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There are no tags for the project with id X"
	* 	}
	*/
	public function getProjectTagsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $projectId, "bugtracker"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:Tag');

		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tags = $qb->getResult();

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
