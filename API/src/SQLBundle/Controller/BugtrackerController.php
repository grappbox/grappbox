<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SQLBundle\Controller\RolesAndTokenVerificationController;

use SQLBundle\Entity\User;
use SQLBundle\Entity\Bug;
use SQLBundle\Entity\BugState;
use SQLBundle\Entity\BugtrackerTag;
use SQLBundle\Entity\Project;
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
	* @api {post} /V0.2/bugtracker/postticket Post ticket
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
	* @apiParam {bool} clientOrigin true if bug created by/from client
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
  * 		"token": "ThisIsMyToken",
  * 		"projectId": 1,
  * 		"title": "J'ai un petit problème",
  * 		"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
  * 		"stateId": 1,
  * 		"stateName": "To Do",
	* 		"clientOrigin": false
  * 	}
	*   }
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {Object} state Ticket state
	* @apiSuccess {int} state.id state id
	* @apiSuccess {String} state.name state name
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {Object[]} users assigned user list
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {date} users.name user full name
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
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
	*    "clientOrigin": false,
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
	/**
	* @api {post} /V0.3/bugtracker/postticket Post ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
	* @apiDescription Post a ticket
	* @apiVersion 0.3.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} projectId id of the project
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {int} stateId Ticket state (0 if new)
	* @apiParam {String} stateName Ticket state
	* @apiParam {bool} clientOrigin true if bug created by/from client
	* @apiParam {int[]} tags array of tags id
	* @apiParam {int[]} users array of users id
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
  * 		"token": "ThisIsMyToken",
  * 		"projectId": 1,
  * 		"title": "J'ai un petit problème",
  * 		"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
  * 		"stateId": 1,
  * 		"stateName": "To Do",
	* 		"clientOrigin": false,
	*			"tags": [15, 2, ...],
	*			"users": [125, 20, ...]
  * 	}
	*   }
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {Object} state Ticket state
	* @apiSuccess {int} state.id state id
	* @apiSuccess {String} state.name state name
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {Object[]} users assigned user list
	*	@apiSuccess {int} users.id user id
	*	@apiSuccess {date} users.name user full name
	*	@apiSuccess {string} users.email user email
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
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
	*    "clientOrigin": false,
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
			|| !array_key_exists("stateId", $content) || !array_key_exists("stateName", $content)
			|| !array_key_exists("clientOrigin", $content) || !array_key_exists("tags", $content)
			|| !array_key_exists("users", $content))
				return $this->setBadRequest("4.2.6", "Bugtracker", "postTicket", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.2.3", "Bugtracker", "postTicket"));

		if (($this->checkRoles($user, $content->projectId, "bugtracker")) < 2)
			return ($this->setNoRightsError("4.2.9", "Bugtracker", "postTicket"));

		$project = $em->getRepository("SQLBundle:Project")->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.2.4", "Bugtracker", "postTicket", "Bad Parameter: projectId");

		$bug = new Bug();
		$bug->setProjects($project);
		$bug->setCreator($user);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setClientOrigin($content->clientOrigin);
		$bug->setCreatedAt(new DateTime('now'));

		$state = null;
		if (array_key_exists("stateId", $content) && $content->stateId != 0)
			$state = $em->getRepository("SQLBundle:BugState")->find($content->stateId);
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
		$project->addBug($bug);
		$em->flush();

		foreach ($content->tags as $key => $tag) {
			$tagToAdd = $em->getRepository('SQLBundle:BugtrackerTag')->find($tag);
			if ($tagToAdd instanceof BugtrackerTag) {
				$assigned = false;

				$bugTags = $bug->getTags();
				if ($bugTags) {
					foreach ($bugTags as $key => $value) {
						if ($value->getId() == $tag)
							$assigned = true;
					}
				}

				if (!$assigned) {
					$bug->addTag($tagToAdd);
					$em->flush();
				}
			}
		}

		foreach ($content->users as $key => $guest) {
				$newGuest = $em->getRepository('SQLBundle:User')->find($guest);
				if ($newGuest instanceof User) {
					$alreadyAdded = false;
					foreach ($bug->getUsers() as $key => $bug_value) {
						if ($guest == $bug_value->getId())
							$alreadyAdded = true;
					}
					if (!$alreadyAdded) {
						$bug->addUser($newGuest);
						$em->flush();
					}
				}
		}


		$ticket = $bug->objectToArray();
		$ticket['state'] = $state->objectToArray();
		foreach ($bug->getTags() as $key => $tag_value) {
			$ticket['tags'][] = $tag_value->objectToArray();
		}

		$participants = array();
		foreach ($bug->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatarDate()
			);
		}
		$ticket["users"] = $participants;

		$this->get('service_stat')->updateStat($content->projectId, 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($content->projectId, 'BugAssignationTracker');
		$this->get('service_stat')->updateStat($content->projectId, 'BugsTagsRepartition');

		return $this->setCreated("1.4.1", "Bugtracker", "postTicket", "Complete Success", $ticket);
	}

	/**
	* @api {put} /V0.2/bugtracker/editticket Edit ticket
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
	* @apiParam {bool} clientOrigin true if bug created by/from client
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
  * 		"token": "ThisIsMyToken",
  * 		"bugId": 1,
  * 		"title": "J'ai un petit problème",
  * 		"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
  * 		"stateId": 1,
  * 		"stateName": "To Do",
	* 		"clientOrigin": false
  * 	}
	*   }
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
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
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
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
	*    "clientOrigin": false,
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
	* @apiErrorExample Bad Parameter: bugId
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
	/**
	* @api {put} /V0.3/bugtracker/editticket Edit ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
	*	@apiDescription Edit ticket
	* @apiVersion 0.3.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} bugId id of the bug ticket
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {int} stateId Ticket state (0 if new)
	* @apiParam {String} stateName Ticket state
	* @apiParam {bool} clientOrigin true if bug created by/from client
	* @apiParam {int[]} addTags array of id of tags to add
	* @apiParam {int[]} removeTags array of id of tags to remove
	* @apiParam {int[]} addUsers array of id of users to add
	* @apiParam {int[]} removeUsers array of id of users to remove
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 	"data": {
  * 		"token": "ThisIsMyToken",
  * 		"bugId": 1,
  * 		"title": "J'ai un petit problème",
  * 		"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
  * 		"stateId": 1,
  * 		"stateName": "To Do",
	* 		"clientOrigin": false,
	* 		"addTags": [12, 5, ...],
	* 		"removeTags": [],
	* 		"addUsers": [152, 50, ...],
	* 		"removeUsers": []
  * 	}
	*   }
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
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
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
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
	*    "clientOrigin": false,
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
	* @apiErrorExample Bad Parameter: bugId
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
			|| !array_key_exists("stateId", $content) || !array_key_exists("stateName", $content)
			|| !array_key_exists("clientOrigin", $content) || !array_key_exists("addTags", $content)
			|| !array_key_exists("removeTags", $content) || !array_key_exists("addUsers", $content)
			|| !array_key_exists("removeUsers", $content))
				return $this->setBadRequest("4.3.6", "Bugtracker", "editTicket", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.3.3", "Bugtracker", "editTicket"));

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('SQLBundle:Bug')->find($content->bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.3.4", "Bugtracker", "postTicket", "Bad Parameter: bugId");

		if (($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker")) < 2)
			return ($this->setNoRightsError("4.3.9", "Bugtracker", "postTicket"));

		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setClientOrigin($content->clientOrigin);
		$bug->setEditedAt(new DateTime('now'));

		$state = null;
		if ($content->stateId != 0)
			$state = $em->getRepository("SQLBundle:BugState")->find($content->stateId);
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

		$assigned = false;
		foreach ($content->removeTags as $tag) {
			$bugTags = $bug->getTags();
			if($bugTags) {
				foreach ($bugTags as $key => $value) {
					if ($value->getId() == $tag) {
						$assigned = true;
						break;
					}
				}
			}

			if ($assigned) {
				$tagToRemove = $em->getRepository('SQLBundle:BugtrackerTag')->find($tag);
				$bug->removeTag($tagToRemove);
				$em->flush();
			}
		}

		foreach ($content->addTags as $tag) {
			$tagToAdd = $em->getRepository('SQLBundle:BugtrackerTag')->find($tag);
			if ($tagToAdd instanceof BugtrackerTag) {
				$assigned = false;
				$bugTags = $bug->getTags();
				if ($bugTags) {
					foreach ($bug->getTags() as $key => $value) {
						if ($value->getId() == $tag) {
							$assigned = true;
							break;
						}
					}
				}
				if (!$assigned) {
					$bug->addTag($tagToAdd);
					$em->flush();
				}
			}
		}

		foreach ($content->removeUsers as $key => $guest) {
				$oldGuest = $em->getRepository('SQLBundle:User')->find($guest);
				if ($oldGuest instanceof User) {
						$bug->removeUser($oldGuest);
						$em->flush();
				}
		}

		foreach ($content->addUsers as $key => $guest) {
				$newGuest = $em->getRepository('SQLBundle:User')->find($guest);
				if ($newGuest instanceof User) {
					$alreadyAdded = false;
					foreach ($bug->getUsers() as $key => $bug_value) {
						if ($guest == $bug_value->getId())
							$alreadyAdded = true;
					}
					if (!$alreadyAdded) {
						$bug->addUser($newGuest);
						$em->flush();
					}
				}
		}

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
				"avatar" => $value->getAvatarDate()
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

		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "editTicket", "Complete Success", $ticket);
	}

	/**
	* @api {delete} /V0.2/bugtracker/closeticket/:token/:id Close ticket / Remove comment
	* @apiName closeTicket
	* @apiGroup Bugtracker
	* @apiDescription Close a ticket or remove a comment
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the ticket/comment
	* @apiParam {String} token client authentification token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - closeTicket - Complete Success"
	*		}
	* 	}
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.8.3",
	*			"return_message": "Bugtracker - closeTicket - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.8.4",
	*			"return_message": "Bugtracker - closeTicket - Bad Parameter: id"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.8.9",
	*			"return_message": "Bugtracker - closeTicket - Insufficient Rights"
  *		}
	* 	}
	*
	*/
	public function closeTicketAction(Request $request, $token, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.8.3", "Bugtracker", "closeTicket"));

		$bug = $em->getRepository("SQLBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.8.4", "Bugtracker", "closeTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
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

		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - closeTicket - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {put} /V0.2/bugtracker/reopenticket/:token/:id Reopen closed ticket
	* @apiName reopenTicket
	* @apiGroup Bugtracker
	* @apiDescription Reopen a closed ticket
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the ticket
	* @apiParam {String} token client authentification token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - reopenTicket - Complete Success"
	*		}
	* 	}
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.23.3",
	*			"return_message": "Bugtracker - reopenTicket - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.23.4",
	*			"return_message": "Bugtracker - reopenTicket - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.23.9",
	*			"return_message": "Bugtracker - reopenTicket - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	public function reopenTicketAction(Request $request, $token, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.23.3", "Bugtracker", "reopenTicket"));

		$bug = $em->getRepository("SQLBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.23.4", "Bugtracker", "reopenTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.23.9", "Bugtracker", "reopenTicket"));

		$bug->setDeletedAt(null);

		$em->persist($bug);
		$em->flush();

		$class = new NotificationController();

		$mdata['mtitle'] = "Bugtracker - Ticket reopen";
		$mdata['mdesc'] = "The ticket ".$bug->getTitle()." has been reopen";

		$wdata['type'] = "Bugtracker";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = "The ticket ".$bug->getTitle()." has been reopen";

		$userNotif = array();
		foreach ($bug->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}

		if (count($userNotif) > 0)
			$class->pushNotification($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - reopenTicket - Complete Success";
		return new JsonResponse($response);
	}

	/*
	 * --------------------------------------------------------------------
	 *													TICKET GETTERS
	 * --------------------------------------------------------------------
	*/

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
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
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
	*	@apiSuccess {date} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* {
	*  "info": {
	*    "return_code": "1.4.1",
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
	*    "clientOrigin": false,
	*    "state": { "id": 1, "name": "Waiting" },
	*    "tags": [
	*      { "id": 1, "name": "To Do", "projectId": 1 },
	*      { "id": 4, "name": "ASAP", "projectId": 1 }
	*    ],
	*    "users": [
	*      { "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"} },
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
		$ticket = $em->getRepository("SQLBundle:Bug")->find($id);
		if (!($ticket instanceof Bug))
			return $this->setBadRequest("4.1.4", "Bugtracker", "getTicket", "Bad Parameter: id");

		if (($this->checkRoles($user, $ticket->getProjects()->getId(), "bugtracker")) < 1)
			return ($this->setNoRightsError("4.1.9", "Bugtracker", "getTicket"));

		$object = $ticket->objectToArray();
		$object['state'] = $em->getRepository("SQLBundle:BugState")->find($ticket->getStateId())->objectToArray();
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
				"avatar" => $value->getAvatarDate()
			);
		}
		$object["users"] = $participants;

		return $this->setSuccess("1.4.1", "Bugtracker", "getTicket", "Complete Success", $object);
	}

	/**
	* @api {get} /V0.2/bugtracker/gettickets/:token/:id Get open tickets
	* @apiName getTickets
	* @apiGroup Bugtracker
	* @apiDescription Get all open tickets of a project
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
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
	*	@apiSuccess {date} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getTickets - Complete Success"
	*  },
	*  "data": {
	*    "array": [
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": null,
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": null,
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	...
	*    ]
	*  }
	* }
	* @apiSuccessExample {json} Success-No Data:
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - getTickets - No Data Success"
	*  },
	*  "data": {
	*    "array": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.9.3",
	*			"return_message": "Bugtracker - getTickets - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.9.4",
	*			"return_message": "Bugtracker - getTickets - Bad Parameter: id"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.9.9",
	*			"return_message": "Bugtracker - getTickets - Insufficient Rights"
  *		}
	* 	}
	*
	*/
	public function getTicketsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.9.3", "Bugtracker", "getTickets"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.9.4", "Bugtracker", "getTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.9.9", "Bugtracker", "getTickets"));

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("SQLBundle:BugState")->find($value->getStateId())->objectToArray();
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
					"avatar" => $user_value->getAvatarDate()
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
	* @api {get} /V0.2/bugtracker/getclosedtickets/:token/:id Get closed tickets
	* @apiName getClosedTickets
	* @apiGroup Bugtracker
	* @apiDescription Get all closed tickets of a project
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
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
	*	@apiSuccess {date} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getClosedTickets - Complete Success"
	*  },
	*  "data": {
	*    "array": [
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	...
	*    ]
	*  }
	* }
	* @apiSuccessExample {json} Success-No Data:
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - getClosedTickets - No Data Success"
	*  },
	*  "data": {
	*    "array": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.22.3",
	*			"return_message": "Bugtracker - getClosedTickets - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.22.4",
	*			"return_message": "Bugtracker - getClosedTickets - Bad Parameter: id"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.22.9",
	*			"return_message": "Bugtracker - getClosedTickets - Insufficient Rights"
  *		}
	* 	}
	*
	*/
	public function getClosedTicketsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.22.3", "Bugtracker", "getClosedTickets"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.22.4", "Bugtracker", "getClosedTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.22.9", "Bugtracker", "getClosedTickets"));

		$tickets = $em->getRepository("SQLBundle:Bug")->createQueryBuilder('b')
						->where("b.projects = :bug_project")->andWhere("b.deletedAt IS NOT NULL")->andWhere("b.parentId IS NULL")
						->setParameter("bug_project", $project)->getQuery()->getResult();
						//->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("SQLBundle:BugState")->find($value->getStateId())->objectToArray();
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
					"avatar" => $user_value->getAvatarDate()
				);
			}
			$object["users"] = $participants;

			$ticketsArray[] = $object;
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getClosedTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getClosedTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} /V0.2/bugtracker/getlasttickets/:token/:id/:offset/:limit Get last tickets
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
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
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
	*	@apiSuccess {date} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getLastTickets - Complete Success"
	*  },
	*  "data": {
	*    "array": [
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": null,
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": null,
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	...
	*    ]
	*  }
	* }
	* @apiSuccessExample {json} Success-No Data:
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - getLastTickets - No Data Success"
	*  },
	*  "data": {
	*    "array": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.10.3",
	*			"return_message": "Bugtracker - getLastTickets - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.10.4",
	*			"return_message": "Bugtracker - getLastTickets - Bad Parameter: id"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.10.9",
	*			"return_message": "Bugtracker - getLastTickets - Insufficient Rights"
  *		}
	* 	}
	*
	*/
	public function getLastTicketsAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.10.3", "Bugtracker", "getLastTickets"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.10.4", "Bugtracker", "getLastTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.10.9", "Bugtracker", "getLastTickets"));

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("SQLBundle:BugState")->find($value->getStateId())->objectToArray();
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
					"avatar" => $user_value->getAvatarDate()
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
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
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
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getLastClosedTickets - Complete Success"
	*  },
	*  "data": {
	*    "array": [
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": { "date": "2015-11-30 21:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": { "date": "2015-11-30 21:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	...
	*    ]
	*  }
	* }
	* @apiSuccessExample {json} Success-No Data:
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - getLastClosedTickets - No Data Success"
	*  },
	*  "data": {
	*    "array": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.11.3",
	*			"return_message": "Bugtracker - getLastClosedTickets - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.11.4",
	*			"return_message": "Bugtracker - getLastClosedTickets - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.11.9",
	*			"return_message": "Bugtracker - getLastClosedTickets - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	public function getLastClosedTicketsAction(Request $request, $token, $id, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.11.3", "Bugtracker", "getLastClosedTickets"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.11.4", "Bugtracker", "getLastClosedTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.11.9", "Bugtracker", "getLastClosedTickets"));

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "parentId" => null), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			if ($value->getDeletedAt() != null)
			{
				$object = $value->objectToArray();
				$object['state'] = $em->getRepository("SQLBundle:BugState")->find($value->getStateId())->objectToArray();
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
						"avatar" => $user_value->getAvatarDate()
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
	* @api {get} /V0.2/bugtracker/getticketsbyuser/:token/:id/:user Get tickets by user
	* @apiName getTicketsByUser
	* @apiGroup Bugtracker
	*	@apiDescription Get Tickets asssigned to a user for a project
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id id of the project
	* @apiParam {int} user id of the user
	* @apiParam {String} token client authentification token
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
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
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getTicketsByUser - Complete Success"
	*  },
	*  "data": {
	*    "array": [
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": { "date": "2015-11-30 21:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": { "date": "2015-11-30 21:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	...
	*    ]
	*  }
	* }
	* @apiSuccessExample {json} Success-No Data:
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - getTicketsByUser - No Data Success"
	*  },
	*  "data": {
	*    "array": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.12.3",
	*			"return_message": "Bugtracker - getTicketsByUser - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.12.4",
	*			"return_message": "Bugtracker - getTicketsByUser - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.12.9",
	*			"return_message": "Bugtracker - getTicketsByUser - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	public function getTicketsByUserAction(Request $request, $token, $id, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.12.3", "Bugtracker", "getTicketsByUser"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.12.4", "Bugtracker", "getTicketsByUser", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.12.9", "Bugtracker", "getTicketsByUser"));

		//$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "user" => $user));

		$tickets = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
									 ->where("b.projects = :project")
									 ->andWhere(':user MEMBER OF b.users')
									 ->setParameters(array('project' => $project, 'user' => $userId))
									 ->getQuery()->getResult();

		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("SQLBundle:BugState")->find($value->getStateId())->objectToArray();
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
					"avatar" => $user_value->getAvatarDate()
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
	* @api {get} /V0.2/bugtracker/getticketsbystate/:token/:id/:state/:offset/:limit Get tickets by status
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
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
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
	*	@apiSuccess {string} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getTicketsByStatus - Complete Success"
	*  },
	*  "data": {
	*    "array": [
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": { "date": "2015-11-30 21:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	{ "id": 1,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": null,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": { "date": "2015-11-30 10:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"deletedAt": { "date": "2015-11-30 21:26:58", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"state": { "id": 1, "name": "Waiting" },
	*    	"tags": [
	*    		{ "id": 1, "name": "To Do", "projectId": 1 },
	*    		{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    	],
	*    	"users": [
	*    		{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    		{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    	]
	*    	},
	*    	...
	*    ]
	*  }
	* }
	* @apiSuccessExample {json} Success-No Data:
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - getTicketsByStatus - No Data Success"
	*  },
	*  "data": {
	*    "array": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.13.3",
	*			"return_message": "Bugtracker - getTicketsByStatus - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.13.4",
	*			"return_message": "Bugtracker - getTicketsByStatus - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.13.9",
	*			"return_message": "Bugtracker - getTicketsByStatus - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	public function getTicketsByStateAction(Request $request, $token, $id, $state, $offset, $limit)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.13.3", "Bugtracker", "getTicketsByStatus"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.13.4", "Bugtracker", "getTicketsByStatus", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.13.9", "Bugtracker", "getTicketsByStatus"));

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => null, "stateId" => $state), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$object = $value->objectToArray();
			$object['state'] = $em->getRepository("SQLBundle:BugState")->find($value->getStateId())->objectToArray();
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
					"avatar" => $user_value->getAvatarDate()
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
	* @apiSuccess {Object[]} states stated list
	* @apiSuccess {int} states.id status id
	* @apiSuccess {string} states.name status name
	*
	* @apiSuccessExample {json} Success-Response:
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getStates - Complete Success"
	*  },
	*  "data": {
	*    "array": [
	*      { "id": 1, "name": "To Do"},
	*      { "id": 4, "name": "Doing"}
	*    ]
	*  }
	* }
	* @apiSuccessExample {json} Success-No Data:
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getStates - Complete Success"
	*  },
	*  "data": {
	*    "array": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.14.3",
	*			"return_message": "Bugtracker - getStates - Bad id"
	*		}
	* 	}
	*
	*/
	public function getStatesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.14.3", "Bugtracker", "getStates"));

		$em = $this->getDoctrine()->getManager();
		$states = $em->getRepository("SQLBundle:BugState")->findAll();

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
	 *														USERS
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {put} /V0.2/bugtracker/setparticipants Set participants
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
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
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
	*	@apiSuccess {date} users.avatar user avatar last modif date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
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
	*    "tags": [
	*    	{ "id": 1, "name": "To Do", "projectId": 1 },
	*    	{ "id": 4, "name": "ASAP", "projectId": 1 }
	*    ],
	*    "users": [
	*    	{ "id": 13, "name": "John Doe", "email": "john.doe@gmail.com", "avatar": null},
	*    	{ "id": 16, "name": "jane doe", "email": "jane.doe@gmail.com", "avatar": null}
	*    ]
	*  }
	* }
	*
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.7.6",
	*			"return_message": "Bugtracker - setParticipants - Missing Parameter"
  *		}
	* 	}
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.7.3",
	*			"return_message": "Bugtracker - setParticipants - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: bugId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.7.4",
	*			"return_message": "Bugtracker - setParticipants - Bad Parameter: bugId"
  *		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.7.9",
	*			"return_message": "Bugtracker - setParticipants - Insufficient Rights"
  *		}
	* 	}
	* @apiErrorExample Already in Database
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.7.7",
	*			"return_message": "Bugtracker - setParticipants - Already in Database"
  *		}
	* 	}
	*
	*/
	public function setParticipantsAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->getDoctrine()->getManager();

		if (!array_key_exists("token", $content) || !array_key_exists("bugId", $content) || !array_key_exists("toAdd", $content) || !array_key_exists("toRemove", $content))
			return $this->setBadRequest("4.7.6", "Bugtracker", "setParticipants", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.7.3", "Bugtracker", "setParticipants"));

		$bug = $em->getRepository("SQLBundle:Bug")->find($content->bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.7.4", "Bugtracker", "setParticipants", "Bad Parameter: bugId");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.7.9", "Bugtracker", "setParticipants"));


		$class = new NotificationController();

		$mdata['mtitle'] = "Bugtracker - Ticket Assigned";
		$mdata['mdesc'] = "You have been assigned to ticket ".$bug->getTitle();

		$wdata['type'] = "Bugtracker";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = "You have been assigned to ticket ".$bug->getTitle();

		foreach ($content->toAdd as $key => $value) {
			$toAddUser = $em->getRepository("SQLBundle:User")->find($value);
			if ($toAddUser instanceof User)
			{
				foreach ($bug->getUsers() as $key => $bug_value) {
					if (($bug_value->getId()) == $toAddUser->getId())
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
			$toRemoveuser = $em->getRepository("SQLBundle:User")->find($value);

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
		$object['state'] = $em->getRepository("SQLBundle:BugState")->find($bug->getStateId())->objectToArray();
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
				"avatar" => $value->getAvatarDate()
			);
		}
		$object["users"] = $participants;

		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');

		return $this->setSuccess("1.4.1", "Bugtracker", "setParticipants", "Complete Success", $object);
	}

	/*
	 * --------------------------------------------------------------------
	 *														COMMENTS
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {get} /V0.2/bugtracker/getcomments/:token/:id/:ticketId Get comments
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiDescription Get all comments of a bug ticket
	* @apiVersion 0.2.0
	*
	* @apiParam {int} id project id
	* @apiParam {String} token client authentification token
	* @apiParam {int} ticketId commented ticket id
	*
	* @apiSuccess {int} id comment id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - getComments - Complete Success"
	*  },
	*  "data": {
	*    "array": [
	*    	{ "id": 11,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": 1,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": null,
	*    	"deletedAt": null },
	*    	{ "id": 12,
	*    	"creator": { "id": 13, "fullname": "John Doe" },
	*    	"projectId": 1,
	*    	"title": "Ticket de Test",
	*    	"description": "Ceci est un ticket de test",
	*    	"parentId": 1,
	*    	"createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    	"editedAt": null,
	*    	"deletedAt": null },
	*    	...
	*    ]
	*  }
	* }
	* @apiSuccessExample {json} Success-No Data:
	* {
	*  "info": {
	*    "return_code": "1.4.3",
	*    "return_message": "Bugtracker - getComments - No Data Success"
	*  },
	*  "data": {
	*    "array": []
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.4.3",
	*			"return_message": "Bugtracker - getComments - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.4.4",
	*			"return_message": "Bugtracker - getComments - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.4.9",
	*			"return_message": "Bugtracker - getComments - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	public function getCommentsAction(Request $request, $token, $id, $ticketId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.4.3", "Bugtracker", "getComments"));
		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.4.9", "Bugtracker", "getComments"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.4.4", "Bugtracker", "getComments", "Bad Parameter: id");

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "deletedAt" => null, "parentId" => $ticketId));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getComments");
		return $this->setSuccess("1.4.1", "Bugtracker", "getComments", "Complete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {post} /V0.2/bugtracker/postcomment Post comment
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
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - postComments - Complete Success"
	*  },
	*  "data": {
	*    "id": 11,
	*    "creator": { "id": 13, "fullname": "John Doe" },
	*    "projectId": 1,
	*    "title": "Ticket de Test",
	*    "description": "Ceci est un ticket de test",
	*    "parentId": 1,
	*    "createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    "editedAt": null,
	*    "deletedAt": null
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.5.3",
	*			"return_message": "Bugtracker - postComments - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: projectId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.5.4",
	*			"return_message": "Bugtracker - postComments - Bad Parameter: projectId"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.5.9",
	*			"return_message": "Bugtracker - postComments - Insufficient Rights"
	*		}
	* 	}
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.5.6",
	*			"return_message": "Bugtracker - postComments - Missing Parameter"
	*		}
	* 	}
	*
	*/
	public function postCommentAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->getDoctrine()->getManager();

		if (!array_key_exists("token", $content) || !array_key_exists("projectId", $content) || !array_key_exists("parentId", $content)
				|| !array_key_exists("title", $content) || !array_key_exists("description", $content))
				return $this->setBadRequest("4.5.6", "Bugtracker", "postComment", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.5.3", "Bugtracker", "postComments"));

		if ($this->checkRoles($user, $content->projectId, "bugtracker") < 1)
			return ($this->setNoRightsError("4.5.9", "Bugtracker", "postComments"));

		$project = $em->getRepository("SQLBundle:Project")->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.5.4", "Bugtracker", "postComments", "Bad Parameter: projectId");

		$bug = new Bug();
		$bug->setProjects($project);
		$bug->setCreator($user);
		$bug->setParentId($content->parentId);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setCreatedAt(new DateTime('now'));
		$bug->setClientOrigin(false);

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

		return $this->setCreated("1.4.1", "Bugtracker", "postComment", "Complete Success", $ticket);
	}

	/**
	* @api {put} /V0.2/bugtracker/editcomment Edit comment
	* @apiName EditComment
	* @apiGroup Bugtracker
	* @apiDescription Edit a comment
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token client authentification token
	* @apiParam {int} projectId id of the project
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
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fullname author fullname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {DateTime} createdAt Ticket creation date
	* @apiSuccess {DateTime} editedAt Ticket edition date
	* @apiSuccess {DateTime} deletedAt Ticket deletion date
	*
	* @apiSuccessExample {json} Success-Response:
	* HTTP/1.1 201 Created
	* {
	*  "info": {
	*    "return_code": "1.4.1",
	*    "return_message": "Bugtracker - editComments - Complete Success"
	*  },
	*  "data": {
	*    "id": 11,
	*    "creator": { "id": 13, "fullname": "John Doe" },
	*    "projectId": 1,
	*    "title": "Ticket de Test",
	*    "description": "Ceci est un ticket de test",
	*    "parentId": 1,
	*    "createdAt": { "date": "2015-11-30 00:00:00", "timezone_type": 3, "timezone": "Europe/Paris" },
	*    "editedAt": null,
	*    "deletedAt": null
	*  }
	* }
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.6.3",
	*			"return_message": "Bugtracker - editComments - Bad id"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: projectId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.6.4",
	*			"return_message": "Bugtracker - editComments - Bad Parameter: commentId"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.6.9",
	*			"return_message": "Bugtracker - editComments - Insufficient Rights"
	*		}
	* 	}
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.6.6",
	*			"return_message": "Bugtracker - editComments - Missing Parameter"
	*		}
	* 	}
	*
	*/
	public function editCommentAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->getDoctrine()->getManager();

		if (!array_key_exists("token", $content) || !array_key_exists("projectId", $content) || !array_key_exists("commentId", $content)
				|| !array_key_exists("title", $content) || !array_key_exists("description", $content))
				return $this->setBadRequest("4.6.6", "Bugtracker", "editComments", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.6.3", "Bugtracker", "editComments"));

		if ($this->checkRoles($user, $content->projectId, "bugtracker") < 1)
			return ($this->setNoRightsError("4.6.9", "Bugtracker", "editComments"));

		$bug = $em->getRepository("SQLBundle:Bug")->find($content->commentId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.6.4", "Bugtracker", "editComments", "Bad Parameter: commentId");

		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setEditedAt(new DateTime('now'));

		$em->persist($bug);
		$em->flush();

		$ticket = $bug->objectToArray();

		return $this->setSuccess("1.4.1", "Bugtracker", "editComment", "Complete Success", $ticket);
	}

	/*
	 * --------------------------------------------------------------------
	 *														TAGS MANAGEMENT
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {post} /V0.2/bugtracker/tagcreation Create tag
	* @apiName tagCreation
	* @apiGroup Bugtracker
	* @apiDescription Create a tag
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "ThisIsMyToken",
	*			"projectId": 2,
	*			"name": "Urgent"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the tag created
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - tagCreation - Complete Success"
	*		},
	*		"data": {
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.15.3",
	*			"return_message": "Bugtracker - tagCreation - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.15.6",
	*			"return_message": "Bugtracker - tagCreation - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "4.15.9",
	*			"return_message": "Bugtracker - tagCreation - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.15.4",
	*			"return_message": "Bugtracker - tagCreation - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function tagCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || !array_key_exists('name', $content) || !array_key_exists('token', $content) || !array_key_exists('projectId', $content))
			return $this->setBadRequest("4.15.6", "Bugtracker", "tagCreation", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.15.3", "Bugtracker", "tagCreation"));

		if ($this->checkRoles($user, $content->projectId, "bugtracker") < 2)
			return ($this->setNoRightsError("4.15.9", "Bugtracker", "tagCreation"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.15.4", "Bugtracker", "tagCreation", "Bad Parameter: projectId");

		$tag = new BugtrackerTag();
		$tag->setName($content->name);
		$tag->setProject($project);

		$em->persist($tag);
		$em->flush();

		$this->get('service_stat')->updateStat($content->projectId, 'BugsTagsRepartition');

		return $this->setCreated("1.4.1", "Bugtracker", "tagCreation", "Complete Success", array("id" => $tag->getId()));
	}

	/**
	* @api {put} /V0.2/bugtracker/tagupdate Update tag
	* @apiName tagUpdate
	* @apiGroup Bugtracker
	* @apiDescription Update a tag
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "ThisIsMyToken",
	*			"tagId": 1,
	*			"name": "ASAP"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the tag
	* @apiSuccess {String} name Name of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 Ok
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - tagUpdate - Complete Success"
	*		},
	*		"data": {
	*			"id" : 1,
	*			"name": "ASAP"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.16.3",
	*			"return_message": "Bugtracker - tagUpdate - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.16.6",
	*			"return_message": "Bugtracker - tagUpdate - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "4.16.9",
	*			"return_message": "Bugtracker - tagUpdate - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.16.4",
	*			"return_message": "Bugtracker - tagUpdate - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function tagUpdateAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || !array_key_exists('name', $content) && !array_key_exists('token', $content) && !array_key_exists('tagId', $content))
			return $this->setBadRequest("4.16.6", "Bugtracker", "tagUpdate", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.16.3", "Bugtracker", "tagUpdate"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('SQLBundle:BugtrackerTag')->find($content->tagId);
		if (!($tag instanceof BugtrackerTag))
			return $this->setBadRequest("4.16.4", "Bugtracker", "tagUpdate", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if ($this->checkRoles($user, $projectId, "bugtracker") < 2)
			return ($this->setNoRightsError("4.16.9", "Bugtracker", "tagUpdate"));

		$tag->setName($content->name);
		$em->flush();

		$this->get('service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "tagUpdate", "Complete Success", array("id" => $tag->getId(), "name" => $tag->getName()));
	}

	/**
	* @api {get} /V0.2/bugtracker/taginformations/:token/:tagId Get tag info
	* @apiName tagInformations
	* @apiGroup Bugtracker
	* @apiDescription Get a tag informations
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiSuccess {Number} id Id of the tag
	* @apiSuccess {String} name Name of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 Ok
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - tagInformations - Complete Success"
	*		},
	*		"data": {
	*			"id" : 1,
	*			"name": "ASAP"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.17.3",
	*			"return_message": "Bugtracker - tagInformations - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "4.17.9",
	*			"return_message": "Bugtracker - tagInformations - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: tagId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.17.4",
	*			"return_message": "Bugtracker - tagInformations - Bad Parameter: tagId"
	*		}
	*	}
	*/
	public function getTagInfosAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.17.3", "Bugtracker", "tagInformations"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('SQLBundle:BugtrackerTag')->find($tagId);
		if (!($tag instanceof BugtrackerTag))
			return $this->setBadRequest("4.17.4", "Bugtracker", "tagInformations", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if ($this->checkRoles($user, $projectId, "bugtracker") < 1)
			return ($this->setNoRightsError("4.17.9", "Bugtracker", "tagInformations"));

		return $this->setSuccess("4.17.3", "Bugtracker", "tagInformations", "Complete Success", array("id" => $tag->getId(), "name" => $tag->getName()));
	}

	/**
	* @api {delete} /V0.2/bugtracker/deletetag/:token/:tagId Delete tag
	* @apiName deleteTag
	* @apiGroup Bugtracker
	* @apiDescription Delete a tag
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - deleteTag - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.18.3",
	*			"return_message": "Bugtracker - deleteTag - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "4.18.9",
	*			"return_message": "Bugtracker - deleteTag - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: tagId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.18.4",
	*			"return_message": "Bugtracker - deleteTag - Bad Parameter: tagId"
	*		}
	*	}
	*/
	public function deleteTagAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.18.3", "Bugtracker", "deleteTag"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('SQLBundle:BugtrackerTag')->find($tagId);
		if (!($tag instanceof BugtrackerTag))
			return $this->setBadRequest("4.18.4", "Bugtracker", "deleteTag", "Bad Parameter: tagId");

		if ($this->checkRoles($user, $tag->getProject()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.18.9", "Bugtracker", "deleteTag"));

		$em->remove($tag);
		$em->flush();

		$this->get('service_stat')->updateStat($tag->getProject()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - deleteTag - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {put} /V0.2/bugtracker/assigntag Assign tag
	* @apiName assignTagToBug
	* @apiGroup Bugtracker
	* @apiDescription Assign a tag to a bug
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} bugId Id of the bug
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "1fez4c5ze31e5f14cze31fc",
	*			"bugId": 1,
	*			"tagId": 3
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the bug
	* @apiSuccess {Object[]} tag Tag's informations
	* @apiSuccess {Number} tag.id Id of the tag
	* @apiSuccess {String} tag.name Name of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - assignTagToBug - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*			"tag": {
	*				"id": 18
	*				"name": "To Do"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.19.3",
	*			"return_message": "Bugtracker - assignTagToBug - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.19.6",
	*			"return_message": "Bugtracker - assignTagToBug - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "4.19.9",
	*			"return_message": "Bugtracker - assignTagToBug - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: bugId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.19.4",
	*			"return_message": "Bugtracker - assignTagToBug - Bad Parameter: bugkId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: tagId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.19.4",
	*			"return_message": "Bugtracker - assignTagToBug - Bad Parameter: tagId"
	*		}
	*	}
	* @apiErrorExample Already In Database
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.19.7",
	*			"return_message": "Bugtracker - assignTagToBug - Already In Database"
	*		}
	*	}
	*/
	public function assignTagAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('tagId', $content) && !array_key_exists('token', $content) && !array_key_exists('bugId', $content)))
			return $this->setBadRequest("4.19.6", "Bugtracker", "assignTagToBug", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("4.19.3", "Bugtracker", "assignTagToBug"));

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('SQLBundle:Bug')->find($content->bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.19.4", "Bugtracker", "assignTagToBug", "Bad Parameter: bugId");

		$projectId = $bug->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "bugtracker") < 2)
			return ($this->setNoRightsError("4.19.9", "Bugtracker", "assignTagToBug"));

		$tagToAdd = $em->getRepository('SQLBundle:BugtrackerTag')->find($content->tagId);
		if (!($tagToAdd instanceof BugtrackerTag))
			return $this->setBadRequest("4.19.4", "Bugtracker", "assignTagToBug", "Bad Parameter: tagId");

		$tags = $bug->getTags();
		foreach ($tags as $tag) {
			if ($tag === $tagToAdd)
				return $this->setBadRequest("4.192.7", "Bugtracker", "assignTagToBug", "Already In Database");
		}

		$bug->addTag($tagToAdd);

		$em->flush();

		$this->get('service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "assignTagToBug", "Complete Success",
			array("id" => $bug->getId(), "tag" => array("id" => $tagToAdd->getId(), "name" => $tagToAdd->getName())));
	}

	/**
	* @api {delete} /V0.2/bugtracker/removetag/:token/:bugId/:tagId Remove tag
	* @apiName removeTagToBug
	* @apiGroup Bugtracker
	* @apiDescription Remove a tag to a bug
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} bugId Id of the bug
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - removeTagToBug - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.20.3",
	*			"return_message": "Bugtracker - removeTagToBug - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "4.20.9",
	*			"return_message": "Bugtracker - removeTagToBug - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: bugId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.20.4",
	*			"return_message": "Bugtracker - removeTagToBug - Bad Parameter: bugId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: tagId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.20.4",
	*			"return_message": "Bugtracker - removeTagToBug - Bad Parameter: tagId"
	*		}
	*	}
	*/
	public function removeTagAction(Request $request, $token, $bugId, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.20.3", "Bugtracker", "removeTagToBug"));

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('SQLBundle:Bug')->find($bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.20.4", "Bugtracker", "removeTagToBug", "Bad Parameter: bugId");

		$projectId = $bug->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "bugtracker") < 2)
			return ($this->setNoRightsError("4.20.9", "Bugtracker", "removeTagToBug"));

		$tagToRemove = $em->getRepository('SQLBundle:BugtrackerTag')->find($tagId);
		if (!($tagToRemove instanceof BugtrackerTag))
			return $this->setBadRequest("4.20.4", "Bugtracker", "removeTagToBug", "Bad Parameter: tagId");

		$tags = $bug->getTags();
		$isAssign = false;
		foreach ($tags as $tag) {
			if ($tag === $tagToRemove)
			{
				$isAssign = true;
			}
		}

		if ($isAssign === false)
			return $this->setBadRequest("4.20.4", "Bugtracker", "removeTagToBug", "Bad Parameter: tagId");

		$bug->removeTag($tagToRemove);

		$em->flush();

		$this->get('service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - removeTagToBug - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /V0.2/bugtracker/getprojecttags/:token/:projectId Get tags by project
	* @apiName getProjectTags
	* @apiGroup Bugtracker
	* @apiDescription Get all the tags for a project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {int} id Id of the tag
	* @apiSuccess {string} name name of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - getProjectTags - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{ "id": 1, "name": "To Do" },
	*				{ "id": 1, "name": "Doing" },
	*				...
	*			]
	*		}
	*	}
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.4.3",
	*			"return_message": "Bugtracker - getProjectTags - No Data Success"
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
	*			"return_code": "4.21.3",
	*			"return_message": "Bugtracker - getProjectTags - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "4.21.9",
	*			"return_message": "Bugtracker - getProjectTags - Insufficient Rights"
	*		}
	*	}
	*
	*/
	public function getProjectTagsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("4.21.3", "Bugtracker", "getProjectTags"));

		if ($this->checkRoles($user, $projectId, "bugtracker") < 1)
			return ($this->setNoRightsError("4.21.9", "Bugtracker", "getProjectTags"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:BugtrackerTag');
		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tags = $qb->getResult();

		$arr = array();
		$i = 1;

		foreach ($tags as $t) {
			$id = $t->getId();
			$name = $t->getName();

			$arr[] = array("id" => $id, "name" => $name);
			$i++;
		}

		if (count($arr) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getProjectTags");
		return $this->setSuccess("1.4.1", "Bugtracker", "getProjectTags", "Complete Success", array("array" => $arr));
	}

}
