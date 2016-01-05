<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use APIBundle\Controller\RolesAndTokenVerificationController;

use APIBundle\Entity\Bug;
use APIBundle\Entity\BugState;
use APIBundle\Entity\BugTag;
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
class BugtrackerController extends RolesAndTokenVerificationController
{
	/**
	* @api {post} /V0.9/bugtracker/postticket/:id Post bug ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {int} stateId Ticket state (0 if new)
	* @apiParam {String} stateName Ticket state
	* @apiParam {String[]} tags Ticket tags list
	* @apiParam {int} parentId (required only for comments) ticket commented id
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} ticket ticket object
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
	* @apiSuccess {String} tickets.state Ticket state
	* @apiSuccess {Object[]} tickets.tags Ticket tags list
	* @apiSuccess {int} tickets.tags.id Ticket tags id
	* @apiSuccess {String} tickets.tags.name Ticket tags name
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		{"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": 150,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": "Wainting",
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}]
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
	public function postTicketAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$bug = new Bug();
		$bug->setProjects($em->getRepository("APIBundle:Project")->find($id));
		$bug->setProjectId($id);
		$bug->setCreatorId($user->getId());
		if ($content->userId != 0)
			$bug->setUserId($content->userId);
		if (array_key_exists("commentedId", $content))
			$bug->setParentId($content->parentId);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setCreatedAt(new DateTime('now'));

		if (array_key_exists("stateId", $content) && $content->stateId != 0)
			$state = $em->getRepository("APIBundle:BugState")->find($content->stateId);
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

		foreach ($content->tags as $key => $value) {
			$tag = new BugTag();
			$tag->setBugId($bug->getId());
			$tag->setName($value);

			$em->persist($tag);
			$em->flush();
		}

		$ticket = $bug->objectToArray();
		$ticket['state'] = $state->getName();
		$tags = $em->getRepository("APIBundle:BugTag")->findBy(array("bugId"=> $bug->getId()));
		foreach ($tags as $key => $tag_value) {
			$ticket['tags'][] = $tag_value->objectToArray();
		}

		// if (!array_key_exists('projectId', $content))
		// 	return $this->setBadRequest("Missing Parameter");

		return new JsonResponse($ticket);
	}

	/**
	* @api {post} /V0.9/bugtracker/editticket/:id Edit a bug ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the ticket
	* @apiParam {String} token client authentification token
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {int} userId id of assigned user
	* @apiParam {int} stateId Ticket state (0 if new)
	* @apiParam {String} stateName Ticket state
	* @apiParam {Object[]} tags Ticket tags list
	* @apiParam {int} tags.id tag id (0 if new)
	* @apiParam {String} tags.name tag name
	* @apiParam {int} parentId (required only for comments) ticket commented id
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} ticket ticket object
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
	* @apiSuccess {String} tickets.state Ticket state
	* @apiSuccess {Object[]} tickets.tags Ticket tags list
	* @apiSuccess {int} tickets.tags.id Ticket tags id
	* @apiSuccess {String} tickets.tags.name Ticket tags name
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		{"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": "Wainting",
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}]
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
	public function editTicketAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('APIBundle:Bug')->find($id);
		if (!$this->checkRoles($user, $bug->getProjectId(), "bugtracker"))
			return ($this->setNoRightsError());

		if ($content->userId == 0)
			$bug->setUserId(null);
		else
			$bug->setUserId($content->userId);
		if (array_key_exists("commentedId", $content))
			$bug->setParentId($content->parentId);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setEditedAt(new DateTime('now'));

		if (array_key_exists("stateId", $content) && $content->stateId != 0)
			$state = $em->getRepository("APIBundle:BugState")->find($content->stateId);
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

		$tags = $em->getRepository('APIBundle:BugTag')->findBy(array("bugId" => $id));
		foreach ($tags as $key => $value) {
			$remove = true;
			foreach ($content->tags as $tag_key => $tag_value) {
				if ($value->getId() == $tag_value->id)
					$remove = false;
			}
			if ($remove)
			{
				$em->remove($value);
				$em->flush();
			}
		}
		foreach ($content->tags as $key => $value) {
			if ($value->id == 0)
				{
					$tag = new BugTag();
					$tag->setBugId($bug->getId());
					$tag->setName($value->name);

					$em->persist($tag);
					$em->flush();
				}
		}

		$ticket = $bug->objectToArray();
		$ticket['state'] = $state->getName();
		$tags = $em->getRepository("APIBundle:BugTag")->findBy(array("bugId"=> $bug->getId()));
		foreach ($tags as $key => $tag_value) {
			$ticket['tags'][] = $tag_value->objectToArray();
		}

		return new JsonResponse($ticket);
	}

	/**
	* @api {get} /V0.9/bugtracker/gettickets/:token/:id Get all tickets of a project
	* @apiName getTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {int} id Message id
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
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}]
	*			},
	*		1 : {"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": null,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}]
	*			},
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
	public function getTicketsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$tickets = $em->getRepository("APIBundle:Bug")->findBy(array("projectId" => $id, "deletedAt" => null, "parentId" => null));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("APIBundle:BugState")->find($value->getStateId())->objectToArray();
			$object['tags'] = array();
			$tags = $em->getRepository("APIBundle:BugTag")->findBy(array("bugId"=> $value->getId()));
			foreach ($tags as $key => $tag_value) {
				$object['tags'][] = $tag_value->objectToArray();
			}
			$ticketsArray[] = $object;
		}

		return new JsonResponse($ticketsArray);
	}

	/**
	* @api {get} /V0.9/bugtracker/getcomments/:token/:id/:message Get comments of a ticket
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiVersion 0.9.2
	*
	* @apiParam {int} id id of the timeline
	* @apiParam {String} token client authentification token
	* @apiParam {int} message commented message id
	*
	* @apiSuccess {Object[]} tickets array of all the ticket's comments
	* @apiSuccess {int} tickets.id Message id
	* @apiSuccess {int} tickets.userId author id
	* @apiSuccess {int} tickets.timelineId timeline id
	* @apiSuccess {String} tickets.message Message content
  * @apiSuccess {int} tickets.parentId parent message id
	* @apiSuccess {DateTime} tickets.createdAt Message creation date
	* @apiSuccess {DateTime} tickets.editedAt Message edition date
	* @apiSuccess {DateTime} tickets.deletedAt Message deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": 150,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}]
	*			},
	*		1 : {"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": 150,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}]
	*			},
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
	public function getCommentsAction(Request $request, $token, $id, $ticketId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$tickets = $em->getRepository("APIBundle:Bug")->findBy(array("projectId" => $id, "deletedAt" => null, "parentId" => $ticketId));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("APIBundle:BugState")->find($value->getStateId())->objectToArray();
			$object['tags'] = array();
			$tags = $em->getRepository("APIBundle:BugTag")->findBy(array("bugId"=> $value->getId()));
			foreach ($tags as $key => $tag_value) {
				$object['tags'][] = $tag_value->objectToArray();
			}
			$ticketsArray[] = $object;
		}

		return new JsonResponse($ticketsArray);
	}

	/**
	* @api {get} /V0.9/bugtracker/getlasttickets/:token/:id/:offset/:limit Get X last tickets from offset Y
	* @apiName getLastTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.9.1
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	* @apiSuccess {int} id Message id
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
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}]
	*			},
	*		1 : {"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": null,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}]
	*			},
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
	public function getLastTicketsAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$tickets = $em->getRepository("APIBundle:Bug")->findBy(array("projectId" => $id, "deletedAt" => null, "parentId" => null), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("APIBundle:BugState")->find($value->getStateId())->objectToArray();
			$object['tags'] = array();
			$tags = $em->getRepository("APIBundle:BugTag")->findBy(array("bugId"=> $value->getId()));
			foreach ($tags as $key => $tag_value) {
				$object['tags'][] = $tag_value->objectToArray();
			}
			$ticketsArray[] = $object;
		}

		return new JsonResponse($ticketsArray);
	}


	/**
	* @api {get} /V0.9/bugtracker/getticketsbyuser/:token/:id/:user Get Tickets asssigned to a user for a project
	* @apiName getTicketsByUser
	* @apiGroup Bugtracker
	* @apiVersion 0.9.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {int} user id of the user
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {int} id Message id
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
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		0 : {"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 1, "name": "Waiting"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}]
	*			},
	*		1 : {"id": "158","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": {"id": 2, "name": "In traitment"},
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "UI"}]
	*			},
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
	public function getTicketsByUserAction(Request $request, $token, $id, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "bugtracker"))
			return ($this->setNoRightsError());

		$em = $this->getDoctrine()->getManager();
		$tickets = $em->getRepository("APIBundle:Bug")->findBy(array("projectId" => $id, "deletedAt" => null, "userId" => $userId ));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("APIBundle:BugState")->find($value->getStateId())->objectToArray();
			$object['tags'] = array();
			$tags = $em->getRepository("APIBundle:BugTag")->findBy(array("bugId"=> $value->getId()));
			foreach ($tags as $key => $tag_value) {
				$object['tags'][] = $tag_value->objectToArray();
			}
			$ticketsArray[] = $object;
		}

		return new JsonResponse($ticketsArray);
	}

	/**
	* @api {get} /V0.9/bugtracker/closeticket/:token/:id Close ticket
	* @apiName closeTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.9.0
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
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository("APIBundle:Bug")->find($id);
		if (!$this->checkRoles($user, $bug->getProjectId(), "bugtracker"))
			return ($this->setNoRightsError());

		$bug->setDeletedAt(new DateTime('now'));

		$em->persist($bug);
		$em->flush();

		return new JsonResponse('Success');
	}

	/**
	* @api {get} /V0.9/bugtracker/getStates/:token Get Tickets Status
	* @apiName getStates
	* @apiGroup Bugtracker
	* @apiVersion 0.9.0
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
		$states = $em->getRepository("APIBundle:BugState")->findAll();

		$states_array = array();
		foreach ($states as $key => $value) {
			$states_array[] = $value->objectToArray();
		}

		return new JsonResponse($states_array);
	}

}
