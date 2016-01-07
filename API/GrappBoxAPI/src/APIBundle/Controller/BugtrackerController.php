<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use APIBundle\Controller\RolesAndTokenVerificationController;

use APIBundle\Entity\User;
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
	* @api {get} /V0.11/bugtracker/getticket/:token/:id Get a ticket
	* @apiName getTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id id of the ticket
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {int} creatorId author id
	* @apiSuccess {int} userId assigned user id
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
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		{"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": null,
	*		"title": "function getUser not working",
	*		"description": "the function does not answer the right way, fix it ASAP !",
	*		"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*		"deletedAt": null,
	*		"state": {"id": 1, "name": "Waiting"},
	*		"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}]
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

	/**
	* @api {get} /V0.11/bugtracker/getticket/:token/:id Get a ticket
	* @apiName getTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the ticket
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
	public function getTicketAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$ticket = $em->getRepository("APIBundle:Bug")->find($id);
		if (!$this->checkRoles($user, $ticket->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError());

		$object = $ticket->objectToArray();
		$object['state'] = $em->getRepository("APIBundle:BugState")->find($ticket->getStateId())->objectToArray();
		$object['tags'] = array();
		$tags = $em->getRepository("APIBundle:BugTag")->findBy(array("bugId"=> $ticket->getId()));
		foreach ($tags as $key => $tag_value) {
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

		return new JsonResponse(array("ticket"=>$object));
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

	/**
	* @api {get} /V0.10/bugtracker/gettickets/:token/:id Get all tickets of a project
	* @apiName getTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.10.0
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

	/**
	* @api {get} /V0.11/bugtracker/gettickets/:token/:id Get all tickets of a project
	* @apiName getTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
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

	/**
	* @api {get} /V0.11/bugtracker/gettickets/:token/:id Get all tickets of a project
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

	/**
	* @api {post} /V0.10/bugtracker/postticket/:id Post bug ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.10.0
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

	/**
	* @api {post} /V0.11/bugtracker/postticket/:id Post bug ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
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

	/**
	* @api {post} /V0.11/bugtracker/postticket/:id Post bug ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {int} stateId Ticket state (0 if new)
	* @apiParam {String} stateName Ticket state
	* @apiParam {String[]} tags Ticket tags list
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} ticket ticket object
	* @apiSuccess {int} ticket.id Ticket id
	* @apiSuccess {int} ticket.creatorId author id
	* @apiSuccess {int} ticket.projectId project id
	* @apiSuccess {String} ticket.title Ticket title
	* @apiSuccess {String} ticket.description Ticket content
	* @apiSuccess {int} ticket.parentId parent Ticket id
	* @apiSuccess {DateTime} ticket.createdAt Ticket creation date
	* @apiSuccess {DateTime} ticket.editedAt Ticket edition date
	* @apiSuccess {DateTime} ticket.deletedAt Ticket deletion date
	* @apiSuccess {String} ticket.state Ticket state
	* @apiSuccess {Object[]} ticket.tags Ticket tags list
	* @apiSuccess {int} ticket.tags.id Ticket tags id
	* @apiSuccess {String} ticket.tags.name Ticket tags name
	* @apiSuccess {Object[]} ticket.users assigned user list
	*	@apiSuccess {int} ticket.users.id user id
	*	@apiSuccess {string} ticket.users.name user full name
	*	@apiSuccess {string} ticket.users.email user email
	*	@apiSuccess {string} ticket.users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"ticket": {"id": "154","creatorId": 12, "projectId": 14, "parentId": 150,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": "Wainting",
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
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

		return new JsonResponse(array("ticket"=>$ticket));
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

	/**
	* @api {post} /V0.10/bugtracker/editticket/:id Edit a bug ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.10.0
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

	/**
	* @api {post} /V0.11/bugtracker/editticket/:id Edit a bug ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
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

	/**
	* @api {post} /V0.11/bugtracker/editticket/:id Edit a bug ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.11.1
	*
	* @apiParam {int} id id of the ticket
	* @apiParam {String} token client authentification token
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {int} stateId Ticket state (0 if new)
	* @apiParam {String} stateName Ticket state
	* @apiParam {Object[]} tags Ticket tags list
	* @apiParam {int} tags.id tag id (0 if new)
	* @apiParam {String} tags.name tag name
	* @apiParam {int} parentId (required only for comments) ticket commented id
	*
	* @apiSuccess {int} id Message id
	* @apiSuccess {Object} ticket ticket object
	* @apiSuccess {int} ticket.id Ticket id
	* @apiSuccess {int} ticket.creatorId author id
	* @apiSuccess {int} ticket.projectId project id
	* @apiSuccess {String} ticket.title Ticket title
	* @apiSuccess {String} ticket.description Ticket content
	* @apiSuccess {int} ticket.parentId parent Ticket id
	* @apiSuccess {DateTime} ticket.createdAt Ticket creation date
	* @apiSuccess {DateTime} ticket.editedAt Ticket edition date
	* @apiSuccess {DateTime} ticket.deletedAt Ticket deletion date
	* @apiSuccess {String} ticket.state Ticket state
	* @apiSuccess {Object[]} ticket.tags Ticket tags list
	* @apiSuccess {int} ticket.tags.id Ticket tags id
	* @apiSuccess {String} ticket.tags.name Ticket tags name
	* @apiSuccess {Object[]} ticket.users assigned user list
	*	@apiSuccess {int} ticket.users.id user id
	*	@apiSuccess {string} ticket.users.name user full name
	*	@apiSuccess {string} ticket.users.email user email
	*	@apiSuccess {string} ticket.users.avatar user avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"ticket": {"id": "154","creatorId": 12, "projectId": 14, "parentId": null,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null,
	*			"state": "Wainting",
	*			"tags" : [{"id": 1, "name": "Urgent"}, {"id": 51, "name": "API"}],
	*			"users": [
	*				{"id": 95, "name": "John Doe", "email": "john.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"},
	*				{"id": 96, "name": "Joanne Doe", "email": "joanne.doe@wanadoo.fr", "avatar": "XXXXXXXXXXX"}
	*			]
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

		return new JsonResponse(array("ticket"=>$ticket));
	}

	/**
	* @api {post} /V0.11/bugtracker/postcomment/:id Post a comment
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

		$bug = new Bug();
		$bug->setProjects($em->getRepository("APIBundle:Project")->find($id));
		$bug->setProjectId($id);
		$bug->setCreatorId($user->getId());
		$bug->setParentId($content->parentId);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setCreatedAt(new DateTime('now'));

		$ticket = $bug->objectToArray();

		return new JsonResponse(array("comment"=>$ticket));
	}

	/**
	* @api {get} /V0.9/bugtracker/getcomments/:token/:id/:message Get comments of a ticket
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiVersion 0.9.0
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

	/**
	* @api {get} /V0.10/bugtracker/getcomments/:token/:id/:message Get comments of a ticket
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiVersion 0.10.0
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

	/**
	* @api {get} /V0.11/bugtracker/getcomments/:token/:id/:ticketId Get comments of a ticket
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id project id
	* @apiParam {String} token client authentification token
	* @apiParam {int} ticketId commented ticket id
	*
	* @apiSuccess {Object[]} tickets array of all the ticket's comments
	* @apiSuccess {int} tickets.id Ticket id
	* @apiSuccess {int} tickets.creatorId author id
	* @apiSuccess {int} tickets.userId assigned user id
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
	*		0 : {"id": "154","creatorId": 12, "userId": 25, "projectId": 14, "parentId": 150,
	*			"title": "function getUser not working",
	*			"description": "the function does not answer the right way, fix it ASAP !",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null
	*			},
	*		1 : {"id": "158","creatorId": 12, "userId": 21, "projectId": 14, "parentId": 150,
	*			"title": "Bad menu disposition on mobile",
	*			"description": "the menu is unsusable on mobile",
	*			"createdAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"editedAt": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"deletedAt": null
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

	/**
	* @api {get} /V0.11/bugtracker/getcomments/:token/:id/:ticketId Get comments of a ticket
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
		$tickets = $em->getRepository("APIBundle:Bug")->findBy(array("projectId" => $id, "deletedAt" => null, "parentId" => $ticketId));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		return new JsonResponse(array("comments" => $ticketsArray));
	}

	/**
	* @api {get} /V0.9/bugtracker/getlasttickets/:token/:id/:offset/:limit Get X last tickets from offset Y
	* @apiName getLastTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.9.0
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

	/**
	* @api {get} /V0.10/bugtracker/getlasttickets/:token/:id/:offset/:limit Get X last tickets from offset Y
	* @apiName getLastTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.10.0
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

	/**
	* @api {get} /V0.11/bugtracker/getlasttickets/:token/:id/:offset/:limit Get X last tickets from offset Y
	* @apiName getLastTickets
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
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

	/**
	* @api {get} /V0.11/bugtracker/getlasttickets/:token/:id/:offset/:limit Get X last tickets from offset Y
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

	/**
	* @api {get} /V0.10/bugtracker/getticketsbyuser/:token/:id/:user Get Tickets asssigned to a user for a project
	* @apiName getTicketsByUser
	* @apiGroup Bugtracker
	* @apiVersion 0.10.0
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

	/**
	* @api {get} /V0.11/bugtracker/getticketsbyuser/:token/:id/:user Get Tickets asssigned to a user for a project
	* @apiName getTicketsByUser
	* @apiGroup Bugtracker
	* @apiVersion 0.11.0
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

	/**
	* @api {get} /V0.11/bugtracker/getticketsbyuser/:token/:id/:user Get Tickets asssigned to a user for a project
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
	* @api {delete} /V0.9/bugtracker/closeticket/:token/:id Close ticket
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

	/**
	* @api {delete} /V0.10/bugtracker/closeticket/:token/:id Close ticket
	* @apiName closeTicket
	* @apiGroup Bugtracker
	* @apiVersion 0.10.0
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

	/**
	* @api {delete} /V0.11/bugtracker/closeticket/:token/:id Close ticket or delete comment
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

	/**
	* @api {get} /V0.10/bugtracker/getStates/:token Get Tickets Status
	* @apiName getStates
	* @apiGroup Bugtracker
	* @apiVersion 0.10.0
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

	/**
	* @api {get} /V0.11/bugtracker/getStates/:token Get Tickets Status
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
		$states = $em->getRepository("APIBundle:BugState")->findAll();

		$states_array = array();
		foreach ($states as $key => $value) {
			$states_array[] = $value->objectToArray();
		}

		return new JsonResponse($states_array);
	}

	/**
	* @api {post} /V0.11/bugtracker/setparticipants/:id Add/remove users to the ticket
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

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository("APIBundle:Bug")->find($id);
		if (!$this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker"))
			return ($this->setNoRightsError());

		foreach ($content->toAdd as $key => $value) {
			$user = $em->getRepository("APIBundle:User")->findOneByEmail($value);
			if ($user instanceof User)
			{
				foreach ($bug->getUsers() as $key => $value) {
					if ($user->getId() == $value->getId())
						return $this->setBadRequest("User already in the list");
					}

				$bug->addUser($user);
			}
		}
		foreach ($content->toRemove as $key => $value) {
			$user = $em->getRepository("APIBundle:User")->find($value);

			if ($user instanceof User)
				$bug->removeUser($user);
		}

		$em->persist($bug);
		$em->flush();

		$object = $bug->objectToArray();
		$object['state'] = $em->getRepository("APIBundle:BugState")->find($bug->getStateId())->objectToArray();
		$object['tags'] = array();
		$tags = $em->getRepository("APIBundle:BugTag")->findBy(array("bugId"=> $bug->getId()));
		foreach ($tags as $key => $tag_value) {
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

}
