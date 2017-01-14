<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use SQLBundle\Controller\RolesAndTokenVerificationController;

use SQLBundle\Entity\User;
use SQLBundle\Entity\Bug;
use SQLBundle\Entity\BugComment;
use SQLBundle\Entity\BugState;
use SQLBundle\Entity\BugtrackerTag;
use SQLBundle\Entity\Project;
use DateTime;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *  @IgnoreAnnotation("apiDescription")
 *  @IgnoreAnnotation("apiVersion")
 *  @IgnoreAnnotation("apiSuccess")
 *  @IgnoreAnnotation("apiSuccessExample")
 *  @IgnoreAnnotation("apiError")
 *  @IgnoreAnnotation("apiErrorExample")
 *  @IgnoreAnnotation("apiParam")
 *  @IgnoreAnnotation("apiParamExample")
 *  @IgnoreAnnotation("apiHeader")
 *  @IgnoreAnnotation("apiHeaderExample")
 */
class BugtrackerController extends RolesAndTokenVerificationController
{
	/**
	* @api {post} /0.3/bugtracker/ticket Post ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
	* @apiDescription Post a ticket
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} projectId id of the project
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {bool} clientOrigin true if bug created by/from client
	* @apiParam {int[]} tags array of tags id
	* @apiParam {int[]} users array of users id
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 		"data": {
  	* 			"projectId": 1,
  	* 			"title": "J'ai un petit problème",
  	* 			"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
	* 			"clientOrigin": false,
	*			"tags": [15, 2, ...],
	*			"users": [125, 20, ...]
  	* 		}
	*   }
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {bool} state true if bug is open
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - postTicket - Complete Success"
	*		},
	*		"data": {
	*			"id": 1,
	*			"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*			"projectId": 1,
	*			"title": "Ticket de Test",
	*			"description": "Ceci est un ticket de test",
	*			"createdAt": "2015-11-30 00:00:00",
	*			"editedAt": null,
	*			"clientOrigin": false,
	*			"state": true,
	*			"tags": [
	*			  	{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  	{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*			],
	*			"users": [
	*			  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*			  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*			]
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "new bug",
	*			"body": {
	*				"id": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*				"projectId": 1,
	*				"title": "Ticket de Test",
	*				"description": "Ceci est un ticket de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null,
	*				"clientOrigin": false,
	*				"state": true,
	*				"tags": [
	*			  		{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  		{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*				],
	*				"users": [
	*				  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*				  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*				]
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.2.3",
	*			"return_message": "Bugtracker - postTicket - Bad Token"
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

		if (!array_key_exists("projectId", $content) || !array_key_exists("title", $content)
			|| !array_key_exists("description", $content) || !array_key_exists("clientOrigin", $content)
			|| !array_key_exists("tags", $content) || !array_key_exists("users", $content))
				return $this->setBadRequest("4.2.6", "Bugtracker", "postTicket", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.2.3", "Bugtracker", "postTicket"));

		$project = $em->getRepository("SQLBundle:Project")->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.2.4", "Bugtracker", "postTicket", "Bad Parameter: projectId");

		if (($this->checkRoles($user, $content->projectId, "bugtracker")) < 2)
			return ($this->setNoRightsError("4.2.9", "Bugtracker", "postTicket"));

		$bug = new Bug();
		$bug->setProjects($project);
		$bug->setCreator($user);
		$bug->setState(true);
		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setClientOrigin($content->clientOrigin);
		$bug->setCreatedAt(new DateTime('now'));

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

		$mdata['mtitle'] = "new bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "new bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		$userNotif = array();
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($content->projectId, 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($content->projectId, 'BugAssignationTracker');
		$this->get('service_stat')->updateStat($content->projectId, 'BugsTagsRepartition');

		return $this->setCreated("1.4.1", "Bugtracker", "postTicket", "Complete Success", $bug->objectToArray());
	}

	/**
	* @api {put} /0.3/bugtracker/ticket/:id Edit ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
	* @apiDescription Edit ticket
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the bug ticket
	* @apiParam {String} title Ticket title
	* @apiParam {String} description Ticket content
	* @apiParam {bool} clientOrigin true if bug created by/from client
	* @apiParam {int[]} addTags array of id of tags to add
	* @apiParam {int[]} removeTags array of id of tags to remove
	* @apiParam {int[]} addUsers array of id of users to add
	* @apiParam {int[]} removeUsers array of id of users to remove
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 		"data": {
 	* 			"title": "J'ai un petit problème",
 	* 			"description": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?",
	* 			"clientOrigin": false,
	* 			"addTags": [12, 5, ...],
	* 			"removeTags": [],
	* 			"addUsers": [152, 50, ...],
	* 			"removeUsers": []
 	* 		}
	*   }
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {bool} state true if bug is open
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - editTicket - Complete Success"
	*		},
	*		"data": {
	*			"id": 1,
	*			"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*			"projectId": 1,
	*			"title": "Ticket de Test",
	*			"description": "Ceci est un ticket de test",
	*			"createdAt": "2015-11-30 00:00:00",
	*			"editedAt": "2015-11-30 10:26:58",
	*			"clientOrigin": false,
	*			"state": true,
	*			"tags": [
	*			  	{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  	{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*			],
	*			"users": [
	*			  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*			  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*			]
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "update bug",
	*			"body": {
	*				"id": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*				"projectId": 1,
	*				"title": "Ticket de Test",
	*				"description": "Ceci est un ticket de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null,
	*				"clientOrigin": false,
	*				"state": true,
	*				"tags": [
	*			  		{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  		{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*				],
	*				"users": [
	*				  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*				  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*				]
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.3.3",
	*			"return_message": "Bugtracker - editTicket - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: bugId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.3.4",
	*			"return_message": "Bugtracker - editTicket - Bad Parameter: bug id"
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
	public function editTicketAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("title", $content) || !array_key_exists("description", $content)
			|| !array_key_exists("clientOrigin", $content) || !array_key_exists("addTags", $content)
			|| !array_key_exists("removeTags", $content) || !array_key_exists("addUsers", $content)
			|| !array_key_exists("removeUsers", $content))
				return $this->setBadRequest("4.3.6", "Bugtracker", "editTicket", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.3.3", "Bugtracker", "editTicket"));

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('SQLBundle:Bug')->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.3.4", "Bugtracker", "postTicket", "Bad Parameter: bug id");

		if (($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker")) < 2)
			return ($this->setNoRightsError("4.3.9", "Bugtracker", "postTicket"));

		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setClientOrigin($content->clientOrigin);
		$bug->setEditedAt(new DateTime('now'));

		$em->persist($bug);
		$em->flush();


		foreach ($content->removeTags as $tag) {
			$bugTags = $bug->getTags();
			if($bugTags) {
				$assigned = false;
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

		$userNotif = array();
		foreach ($content->removeUsers as $key => $guest) {
			$oldGuest = $em->getRepository('SQLBundle:User')->find($guest);
			if ($oldGuest instanceof User) {
				$userNotif[] = $oldGuest;
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

		$mdata['mtitle'] = "update bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "update bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "editTicket", "Complete Success", $bug->objectToArray());
	}

	/**
	* @api {delete} /0.3/bugtracker/ticket/close/:id Close ticket
	* @apiName closeTicket
	* @apiGroup Bugtracker
	* @apiDescription Close a ticket, to delete a comment see [deleteComment](/#api-Bugtracker-deleteComment) request
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the ticket
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - closeTicket - Complete Success"
	*		}
	* 	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "close bug",
	*			"body": {
	*				"id": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*				"projectId": 1,
	*				"title": "Ticket de Test",
	*				"description": "Ceci est un ticket de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null,
	*				"clientOrigin": false,
	*				"state": true,
	*				"tags": [
	*			  		{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  		{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*				],
	*				"users": [
	*				  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*				  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*				]
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.8.3",
	*			"return_message": "Bugtracker - closeTicket - Bad Token"
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
	public function closeTicketAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.8.3", "Bugtracker", "closeTicket"));

		$bug = $em->getRepository("SQLBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.8.4", "Bugtracker", "closeTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.8.9", "Bugtracker", "closeTicket"));

		$bug->setState(false);

		$em->persist($bug);
		$em->flush();

		$mdata['mtitle'] = "close bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "close bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		$userNotif = array();
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - closeTicket - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {delete} /0.3/bugtracker/ticket/:id Delete ticket
	* @apiName deleteTicket
	* @apiGroup Bugtracker
	* @apiDescription Delete a ticket
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the ticket
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - deleteTicket - Complete Success"
	*		}
	* 	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "delete bug",
	*			"body": {
	*				"id": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*				"projectId": 1,
	*				"title": "Ticket de Test",
	*				"description": "Ceci est un ticket de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null,
	*				"clientOrigin": false,
	*				"state": true,
	*				"tags": [
	*			  		{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  		{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*				],
	*				"users": [
	*				  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*				  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*				]
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.25.3",
	*			"return_message": "Bugtracker - deleteTicket - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.25.4",
	*			"return_message": "Bugtracker - deleteTicket - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.25.9",
	*			"return_message": "Bugtracker - deleteTicket - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	public function deleteTicketAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.25.3", "Bugtracker", "deleteTicket"));

		$bug = $em->getRepository("SQLBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.25.4", "Bugtracker", "deleteTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.25.9", "Bugtracker", "deleteTicket"));

		$mdata['mtitle'] = "delete bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "delete bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		$userNotif = array();
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($bug);
		$em->flush();

		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - deleteTicket - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /0.3/bugtracker/ticket/reopen/:id Reopen closed ticket
	* @apiName reopenTicket
	* @apiGroup Bugtracker
	* @apiDescription Reopen a closed ticket
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the ticket
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - reopenTicket - Complete Success"
	*		}
	* 	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "reopen bug",
	*			"body": {
	*				"id": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*				"projectId": 1,
	*				"title": "Ticket de Test",
	*				"description": "Ceci est un ticket de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null,
	*				"clientOrigin": false,
	*				"state": true,
	*				"tags": [
	*			  		{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  		{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*				],
	*				"users": [
	*				  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*				  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*				]
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.23.3",
	*			"return_message": "Bugtracker - reopenTicket - Bad Token"
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
	public function reopenTicketAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.23.3", "Bugtracker", "reopenTicket"));

		$bug = $em->getRepository("SQLBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.23.4", "Bugtracker", "reopenTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.23.9", "Bugtracker", "reopenTicket"));

		$bug->setState(true);

		$em->persist($bug);
		$em->flush();

		$mdata['mtitle'] = "reopen bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "reopen bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		$userNotif = array();
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

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
	* @api {get} /0.3/bugtracker/ticket/:id Get ticket
	* @apiName getTicket
	* @apiGroup Bugtracker
	* @apiDescription Get ticket informations, tags and assigned users
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id ticket's id
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {bool} state true if bug is open
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - getTicket - Complete Success"
	*		},
	*		"data": {
	*			"id": 1,
	*			"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*			"projectId": 1,
	*			"title": "Ticket de Test",
	*			"description": "Ceci est un ticket de test",
	*			"createdAt": "2015-11-30 00:00:00",
	*			"editedAt": "2015-12-29 11:54:57",
	*			"clientOrigin": false,
	*			"state": true,
	*			"tags": [
	*			  { "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  { "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*			],
	*			"users": [
	*			  { "id": 13, "firstname": "John", "lastname": "Doe" },
	*			  { "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*			]
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.1.3",
	*			"return_message": "Bugtracker - getTicket - Bad Token"
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
	public function getTicketAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.1.3", "Bugtracker", "getTicket"));

		$em = $this->getDoctrine()->getManager();
		$ticket = $em->getRepository("SQLBundle:Bug")->find($id);
		if (!($ticket instanceof Bug))
			return $this->setBadRequest("4.1.4", "Bugtracker", "getTicket", "Bad Parameter: id");

		if (($this->checkRoles($user, $ticket->getProjects()->getId(), "bugtracker")) < 1)
			return ($this->setNoRightsError("4.1.9", "Bugtracker", "getTicket"));

		return $this->setSuccess("1.4.1", "Bugtracker", "getTicket", "Complete Success", $ticket->objectToArray());
	}

	/**
	* @api {get} /0.3/bugtracker/tickets/opened/:id Get open tickets
	* @apiName getTickets
	* @apiGroup Bugtracker
	* @apiDescription Get all open tickets of a project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the project
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {bool} state true if bug is open
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - getTickets - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": true,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "F0F0F0" },
	*						{ "id": 4, "name": "ASAP", "FFFFFF" }
	*					],
	*					"users": [
	*						{ "id": 13, "firstname": "John", "lastname": "Doe"},
	*						{ "id": 16, "firstname": "Jane", "lastname": "Doe"}
	*					]
	*				},
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "fullname": "John Doe" },
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": true,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "FF41B0"},
	*						{ "id": 4, "name": "ASAP", "color": "0000FF"}
	*					],
	*					"users": [
	*						{ "id": 13, "name": "John Doe"},
	*						{ "id": 16, "name": "jane doe"}
	*					]
	*				},
	*				...
	*			]
	*		}
	*	}
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
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.9.3",
	*			"return_message": "Bugtracker - getTickets - Bad Token"
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
	public function getTicketsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.9.3", "Bugtracker", "getTickets"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.9.4", "Bugtracker", "getTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.9.9", "Bugtracker", "getTickets"));

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "state" => true));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} /0.3/bugtracker/tickets/closed/:id Get closed tickets
	* @apiName getClosedTickets
	* @apiGroup Bugtracker
	* @apiDescription Get all closed tickets of a project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the project
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {bool} state true if bug is open
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - getClosedTickets - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": false,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "F0F0F0"},
	*						{ "id": 4, "name": "ASAP", "color": "D0D0D0"}
	*					],
	*					"users": [
	*						{ "id": 13, "firstname": "John", "lastname": "Doe"},
	*						{ "id": 16, "firstname": "Jane", "lastname": "Doe"}
	*					]
	*				},
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": false,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "FFFFFF"},
	*						{ "id": 4, "name": "ASAP", "color": "D0D0D0"}
	*					],
	*					"users": [
	*						{ "id": 13, "firstname": "John", "lastname": "Doe"}
	*					]
	*				},
	*				...
	*			]
	*		}
	*	}
	* @apiSuccessExample {json} Success-No Data:
	*	{
	*		"info": {
	*			"return_code": "1.4.3",
	*			"return_message": "Bugtracker - getClosedTickets - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.22.3",
	*			"return_message": "Bugtracker - getClosedTickets - Bad Token"
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
	public function getClosedTicketsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.22.3", "Bugtracker", "getClosedTickets"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.22.4", "Bugtracker", "getClosedTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.22.9", "Bugtracker", "getClosedTickets"));

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array('projects' => $project, 'state' => false));

		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getClosedTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getClosedTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} /0.3/bugtracker/tickets/opened/:id/:offset/:limit Get last opened tickets
	* @apiName getLastOpenedTickets
	* @apiGroup Bugtracker
	* @apiDescription Get X last opened tickets from offset Y
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the project
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {bool} state true if bug is open
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - getLastTickets - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": true,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "FFFFFF"},
	*						{ "id": 4, "name": "ASAP", "color": "AAAAAA"}
	*					],
	*					"users": [
	*						{ "id": 13, "firstname": "John", "lastname": "Doe"},
	*						...
	*					]
	*				},
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": true,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "F1F3FF"},
	*						{ "id": 4, "name": "ASAP", "color": "012546"}
	*					],
	*					"users": [
	*						{ "id": 13, "firstname": "John", "lastname": "Doe"},
	*						...
	*					]
	*				},
	*				...
	*			]
	*		}
	*	}
	* @apiSuccessExample {json} Success-No Data:
	*	{
	*		"info": {
	*			"return_code": "1.4.3",
	*			"return_message": "Bugtracker - getLastTickets - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.10.3",
	*			"return_message": "Bugtracker - getLastTickets - Bad Token"
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
	public function getLastTicketsAction(Request $request, $id, $offset, $limit)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.10.3", "Bugtracker", "getLastTickets"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.10.4", "Bugtracker", "getLastTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.10.9", "Bugtracker", "getLastTickets"));

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "state" => true), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getLastTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getLastTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} /0.3/bugtracker/tickets/closed/:id/:offset/:limit Get last closed tickets
	* @apiName getLastClosedTickets
	* @apiGroup Bugtracker
	* @apiDescription Get X last closed tickets from offset Y
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the project
	* @apiParam {int} offset ticket offset from where to get the tickets (start to 0)
	* @apiParam {int} limit number max of tickets to get
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {bool} state true if bug is open
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - getLastClosedTickets - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"parentId": null,
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": true,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color" : "333333"},
	*						{ "id": 4, "name": "ASAP", "color": "666666"}
	*					],
	*					"users": [
	*						{ "id": 13, "firstname": "John", "lastname": "Doe"},
	*						{ "id": 16, "firstname": "Jane", "lastname": "Doe"}
	*					]
	*				},
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"parentId": null,
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": true,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "333333"},
	*						{ "id": 4, "name": "ASAP", "color": "666666"}
	*					],
	*					"users": [
	*						{ "id": 13, "firstname": "John", "lastname": "Doe"},
	*						{ "id": 16, "firstname": "Jane", "lastname": "Doe"}
	*					]
	*				},
	*				...
	*			]
	*		}
	*	}
	* @apiSuccessExample {json} Success-No Data:
	*	{
	*		"info": {
	*			"return_code": "1.4.3",
	*			"return_message": "Bugtracker - getLastClosedTickets - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.11.3",
	*			"return_message": "Bugtracker - getLastClosedTickets - Bad Token"
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
	public function getLastClosedTicketsAction(Request $request, $id, $offset, $limit)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.11.3", "Bugtracker", "getLastClosedTickets"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.11.4", "Bugtracker", "getLastClosedTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.11.9", "Bugtracker", "getLastClosedTickets"));

		$tickets = $em->getRepository("SQLBundle:Bug")->findBy(array("projects" => $project, "state" => false), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
				$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getLastClosedTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getLastClosedTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @api {get} /0.3/bugtracker/tickets/user/:id/:user Get opened tickets by user
	* @apiName getTicketsByUser
	* @apiGroup Bugtracker
	* @apiDescription Get open tickets asssigned to a user for a project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the project
	* @apiParam {int} user id of the user
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {bool} clientOrigin true if bug created by/from client
	* @apiSuccess {bool} state true if bug is open
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - getTicketsByUser - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": true,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "FFFFFF"},
	*						{ "id": 4, "name": "ASAP", "color": "AAAAAA"}
	*					],
	*					"users": []
	*				},
	*				{
	*					"id": 1,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*					"projectId": 1,
	*					"title": "Ticket de Test",
	*					"description": "Ceci est un ticket de test",
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": "2015-11-30 10:26:58",
	*					"clientOrigin": false,
	*					"state": true,
	*					"tags": [
	*						{ "id": 1, "name": "To Do", "color": "FFFFFF"},
	*						{ "id": 4, "name": "ASAP", "color": "AAAAAA"}
	*					],
	*					"users": []
	*				},
	*				...
	*		  ]
	*		}
	*	}
	* @apiSuccessExample {json} Success-No Data:
	*	{
	*		"info": {
	*			"return_code": "1.4.3",
	*			"return_message": "Bugtracker - getTicketsByUser - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.12.3",
	*			"return_message": "Bugtracker - getTicketsByUser - Bad Token"
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
	public function getTicketsByUserAction(Request $request, $id, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.12.3", "Bugtracker", "getTicketsByUser"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository("SQLBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.12.4", "Bugtracker", "getTicketsByUser", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.12.9", "Bugtracker", "getTicketsByUser"));

		$tickets = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
									 ->where("b.projects = :project AND b.state = true")
									 ->andWhere(':user MEMBER OF b.users')
									 ->setParameters(array('project' => $project, 'user' => $userId))
									 ->getQuery()->getResult();

		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getTicketsByUser");
		return $this->setSuccess("1.4.1", "Bugtracker", "getTicketsByUser", "Commplete Success", array("array" => $ticketsArray));
	}

	/*
	 * --------------------------------------------------------------------
	 *														USERS
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {put} /0.3/bugtracker/users/:id Set participants
	* @apiName setParticipants
	* @apiGroup Bugtracker
	* @apiDescription Assign/unassign users to a ticket
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id bug id
	* @apiParam {int[]} toAdd list of users' id to assign
	* @apiParam {int[]} toRemove list of users' id to unassign
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 		"data": {
	* 			"toAdd": [1, 15, 6],
	* 			"toRemove": []
	* 		}
	*   }
	*
	* @apiSuccess {int} id Ticket id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {string} creator.firstname author firstname
	* @apiSuccess {string} creator.lastname author lastname
	* @apiSuccess {int} projectId project id
	* @apiSuccess {String} title Ticket title
	* @apiSuccess {String} description Ticket content
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	* @apiSuccess {Object} state Ticket state
	* @apiSuccess {int} state.id state id
	* @apiSuccess {String} state.name state name
	* @apiSuccess {Object[]} tags Ticket tags list
	* @apiSuccess {int} tags.id Ticket tags id
	* @apiSuccess {String} tags.name Ticket tags name
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} users assigned user list
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - editTicket - Complete Success"
	*		},
	*		"data": {
	*			"id": 1,
	*			"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*			"projectId": 1,
	*			"title": "Ticket de Test",
	*			"description": "Ceci est un ticket de test",
	*			"parentId": null,
	*			"createdAt": "2015-11-30 00:00:00",
	*			"editedAt": "2015-11-30 10:26:58",
	*			"state": true,
	*			"tags": [
	*				{ "id": 1, "name": "To Do", "color": "FFFFFF"},
	*				{ "id": 4, "name": "ASAP", "color": "FFFFFF"}
	*			],
	*			"users": [
	*				{ "id": 13, "firstname": "John", "lastname": "Doe"},
	*				{ "id": 16, "firstname": "Jane", "lastname": "Doe"}
	*			]
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "participants bug",
	*			"body": {
	*				"id": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*				"projectId": 1,
	*				"title": "Ticket de Test",
	*				"description": "Ceci est un ticket de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null,
	*				"clientOrigin": false,
	*				"state": true,
	*				"tags": [
	*			  		{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  		{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*				],
	*				"users": [
	*				  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*				  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*				]
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.7.6",
	*			"return_message": "Bugtracker - setParticipants - Missing Parameter"
	*		}
	* 	}
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.7.3",
	*			"return_message": "Bugtracker - setParticipants - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: bugId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.7.4",
	*			"return_message": "Bugtracker - setParticipants - Bad Parameter: bug id"
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
	public function setParticipantsAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->getDoctrine()->getManager();

		if (!array_key_exists("toAdd", $content) || !array_key_exists("toRemove", $content))
			return $this->setBadRequest("4.7.6", "Bugtracker", "setParticipants", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.7.3", "Bugtracker", "setParticipants"));

		$bug = $em->getRepository("SQLBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.7.4", "Bugtracker", "setParticipants", "Bad Parameter: bug id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.7.9", "Bugtracker", "setParticipants"));

		foreach ($content->toAdd as $key => $value) {
			$toAddUser = $em->getRepository("SQLBundle:User")->find($value);
			if ($toAddUser instanceof User)
			{
				foreach ($bug->getUsers() as $key => $bug_value) {
					if (($bug_value->getId()) == $toAddUser->getId())
						return $this->setBadRequest("4.7.7", "Bugtracker", "setParticipants", "Already in Database");
					}

				$bug->addUser($toAddUser);
			}
		}

		foreach ($content->toRemove as $key => $value) {
			$toRemoveuser = $em->getRepository("SQLBundle:User")->find($value);

			if ($toRemoveuser instanceof User)
			{
				$bug->removeUser($toRemoveuser);
			}
		}

		$em->persist($bug);
		$em->flush();

		$mdata['mtitle'] = "participants bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "participants bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		$userNotif = array();
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');

		return $this->setSuccess("1.4.1", "Bugtracker", "setParticipants", "Complete Success", $bug->objectToArray());
	}

	/*
	 * --------------------------------------------------------------------
	 *														COMMENTS
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {get} /0.3/bugtracker/comments/:ticketId Get comments by bug
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiDescription Get all comments of a bug ticket
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} ticketId commented ticket id
	*
	* @apiSuccess {int} id comment id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.fisrtname author firstname
	* @apiSuccess {String} creator.lastname author lastname
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {String} comment comment content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	*
	* @apiSuccessExample {json} Success-Response:
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - getComments - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 11,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe" },
	*					"comment": "Ceci est un commentaire de test",
	*					"parentId": 1,
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": null
	*				},
	*				{
	*					"id": 12,
	*					"creator": { "id": 13, "firstname": "John", "lastname": "Doe" },
	*					"comment": "Ceci est un commentaire de test",
	*					"parentId": 1,
	*					"createdAt": "2015-11-30 00:00:00",
	*					"editedAt": null
	*				},
	*				...
	*			]
	*		}
	*	}
	* @apiSuccessExample {json} Success-No Data:
	*	{
	*		"info": {
	*			"return_code": "1.4.3",
	*			"return_message": "Bugtracker - getComments - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.4.3",
	*			"return_message": "Bugtracker - getComments - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: ticketId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.4.4",
	*			"return_message": "Bugtracker - getComments - Bad Parameter: ticketId"
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
	public function getCommentsAction(Request $request, $ticketId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.4.3", "Bugtracker", "getComments"));

		$em = $this->getDoctrine()->getManager();
		$ticket = $em->getRepository("SQLBundle:Bug")->find($ticketId);
		if (!($ticket instanceof Bug))
			return $this->setBadRequest("4.4.4", "Bugtracker", "getComments", "Bad Parameter: ticketId");

		if ($this->checkRoles($user, $ticket->getProjects()->getId(), "bugtracker") < 1)
			return ($this->setNoRightsError("4.4.9", "Bugtracker", "getComments"));

		$comments = $em->getRepository("SQLBundle:BugComment")->findByBugs($ticket);
		$commentsArray = array();
		foreach ($comments as $key => $value) {
			$commentsArray[] = $value->objectToArray();
		}

		if (count($commentsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getComments");
		return $this->setSuccess("1.4.1", "Bugtracker", "getComments", "Complete Success", array("array" => $commentsArray));
	}

	/**
	* @api {post} /0.3/bugtracker/comment Post comment
	* @apiName postComment
	* @apiGroup Bugtracker
	* @apiDescription Post comment on a bug ticket
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} parentId commented ticket id
	* @apiParam {String} comment Comment content
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 		"data": {
	* 			"parentId": 1,
	* 			"comment": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?"
	* 		}
	*   }
	*
	* @apiSuccess {int} id Comment id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.firstname author firsname
	* @apiSuccess {String} creator.lastname author lastname
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {String} comment Comment content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - postComments - Complete Success"
	*		},
	*		"data": {
	*			"id": 11,
	*			"creator": { "id": 13, "firstname": "John", "lastname": "Doe" },
	*			"parentId": 1,
	*			"comment": "Ceci est un comment de test",
	*			"createdAt": "2015-11-30 00:00:00",
	*			"editedAt": null
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "new comment bug",
	*			"body": {
	*				"id": 11,
	*				"projectId": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe" },
	*				"parentId": 1,
	*				"comment": "Ceci est un comment de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.5.3",
	*			"return_message": "Bugtracker - postComments - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: parentId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.5.4",
	*			"return_message": "Bugtracker - postComments - Bad Parameter: parentId"
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

		if (!array_key_exists("parentId", $content) || !array_key_exists("comment", $content))
				return $this->setBadRequest("4.5.6", "Bugtracker", "postComment", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.5.3", "Bugtracker", "postComments"));

		$parent = $em->getRepository("SQLBundle:Bug")->find($content->parentId);
		if (!($parent instanceof Bug))
			return $this->setBadRequest("4.5.4", "Bugtracker", "postComments", "Bad Parameter: parentId");

		if ($this->checkRoles($user, $parent->getProjects()->getId(), "bugtracker") < 1)
			return ($this->setNoRightsError("4.5.9", "Bugtracker", "postComments"));


		$comment = new BugComment();
		$comment->setCreator($user);
		$comment->setBugs($parent);
		$comment->setComment($content->comment);
		$comment->setCreatedAt(new DateTime('now'));

		$em->persist($comment);
		$em->flush();

		$ticket = $comment->objectToArray();
		$ticket['projectId'] = $parent->getProjects()->getId();

		$mdata['mtitle'] = "new comment bug";
		$mdata['mdesc'] = json_encode($ticket);
		$wdata['type'] = "new comment bug";
		$wdata['targetId'] = $comment->getId();
		$wdata['message'] = json_encode($ticket);
		$userNotif = array();
		foreach ($parent->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.4.1", "Bugtracker", "postComment", "Complete Success", $ticket);
	}

	/**
	* @api {put} /0.3/bugtracker/comment/:id Edit comment
	* @apiName EditComment
	* @apiGroup Bugtracker
	* @apiDescription Edit a comment
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id comment id to edit
	* @apiParam {String} comment Comment content
	*
	* @apiParamExample {json} Request-Example:
	*   {
	* 		"data": {
	* 			"comment": "J'ai un petit problème dans ma plantation, pourquoi ça pousse pas ?"
	* 		}
	*   }
	*
	* @apiSuccess {int} id Comment id
	* @apiSuccess {Object} creator author
	* @apiSuccess {int} creator.id author id
	* @apiSuccess {String} creator.firstname author firsname
	* @apiSuccess {String} creator.lastname author lastname
	* @apiSuccess {int} parentId parent Ticket id
	* @apiSuccess {String} comment Comment content
	* @apiSuccess {string} createdAt Ticket creation date
	* @apiSuccess {string} editedAt Ticket edition date
	*
	* @apiSuccessExample {json} Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - editComments - Complete Success"
	*		},
	*		"data": {
	*			"id": 11,
	*			"creator": { "id": 13, "firstname": "John", "lastname": "Doe" },
	*			"parentId": 1,
	*			"comment": "Ceci est un comment de test",
	*			"createdAt": "2015-11-30 00:00:00",
	*			"editedAt": null
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "edit comment bug",
	*			"body": {
	*				"id": 11,
	*				"projectId": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe" },
	*				"parentId": 1,
	*				"comment": "Ceci est un comment de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.6.3",
	*			"return_message": "Bugtracker - editComments - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: projectId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.6.4",
	*			"return_message": "Bugtracker - editComments - Bad Parameter: comment id"
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
	public function editCommentAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->getDoctrine()->getManager();

		if (!array_key_exists("comment", $content))
			return $this->setBadRequest("4.6.6", "Bugtracker", "editComments", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.6.3", "Bugtracker", "editComments"));

		$comment = $em->getRepository("SQLBundle:BugComment")->find($id);
		if (!($comment instanceof BugComment))
			return $this->setBadRequest("4.6.4", "Bugtracker", "editComments", "Bad Parameter: id");

		if ($user->getId() != $comment->getCreator()->getId())
			return ($this->setNoRightsError("4.6.9", "Bugtracker", "editComments"));

		$comment->setComment($content->comment);
		$comment->setEditedAt(new DateTime('now'));

		$em->persist($comment);
		$em->flush();
		$com = $comment->objectToArray();
		$com['projectId'] = $comment->getBugs()->getProjects()->getId();

		$mdata['mtitle'] = "edit comment bug";
		$mdata['mdesc'] = json_encode($com);
		$wdata['type'] = "edit comment bug";
		$wdata['targetId'] = $comment->getId();
		$wdata['message'] = json_encode($com);
		$userNotif = array();
		foreach ($comment->getBugs()->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.4.1", "Bugtracker", "editComment", "Complete Success", $comment->objectToArray());
	}

	/**
	* @api {delete} /0.3/bugtracker/comment/:id Delete comment
	* @apiName deletecomment
	* @apiGroup Bugtracker
	* @apiDescription Delete a comment (creator allowed only)
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} id id of the ticket
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - deleteComment - Complete Success"
	*		}
	* 	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "delete comment bug",
	*			"body": {
	*				"id": 11,
	*				"projectId": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe" },
	*				"parentId": 1,
	*				"comment": "Ceci est un comment de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.24.3",
	*			"return_message": "Bugtracker - deleteComment - Bad Token"
	*		}
	* 	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.24.4",
	*			"return_message": "Bugtracker - deleteComment - Bad Parameter: id"
	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "4.24.9",
	*			"return_message": "Bugtracker - deleteComment - Insufficient Rights"
	*		}
	* 	}
	*
	*/
	public function deleteCommentAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.24.3", "Bugtracker", "deleteComment"));

		$comment = $em->getRepository("SQLBundle:BugComment")->find($id);
		if (!($comment instanceof BugComment))
			return $this->setBadRequest("4.24.4", "Bugtracker", "deleteComment", "Bad Parameter: id");

		if ($comment->getCreator()->getId() != $user->getId())
			return ($this->setNoRightsError("4.24.9", "Bugtracker", "deleteComment"));

		$com = $comment->objectToArray();
		$com['projectId'] = $comment->getBugs()->getProjects()->getId();

		$mdata['mtitle'] = "delete comment bug";
		$mdata['mdesc'] = json_encode($com);
		$wdata['type'] = "delete comment bug";
		$wdata['targetId'] = $comment->getId();
		$wdata['message'] = json_encode($com);
		$userNotif = array();
		foreach ($comment->getBugs()->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($comment);
		$em->flush();

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - deleteComment - Complete Success";
		return new JsonResponse($response);
	}

	/*
	 * --------------------------------------------------------------------
	 *														TAGS MANAGEMENT
	 * --------------------------------------------------------------------
	*/

	/**
	* @api {post} /0.3/bugtracker/tag Create tag
	* @apiName tagCreation
	* @apiGroup Bugtracker
	* @apiDescription Create a tag
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} name Name of the tag
	* @apiParam {string} color Color of the tag in hexa
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"projectId": 2,
	*			"name": "Urgent",
	*			"color": "FFFFFF"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the tag created
	* @apiSuccess {String} name Ticket tags name
	* @apiSuccess {string} color Color of the tag in hexa
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - tagCreation - Complete Success"
	*		},
	*		"data": {
	*			"id": 1,
	*			"name": "Urgent",
	*			"color": "FFFFFF"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "new tag bug",
	*			"body": {
	*				"id": 1,
	*				"projectId": 1,
	*				"name": "Urgent",
	*				"color": "FFFFFF"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.15.3",
	*			"return_message": "Bugtracker - tagCreation - Bad Token"
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

		if ($content === null || !array_key_exists('name', $content) || !array_key_exists('projectId', $content) || !array_key_exists('color', $content))
			return $this->setBadRequest("4.15.6", "Bugtracker", "tagCreation", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.15.3", "Bugtracker", "tagCreation"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.15.4", "Bugtracker", "tagCreation", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "bugtracker") < 2)
			return ($this->setNoRightsError("4.15.9", "Bugtracker", "tagCreation"));

		$tag = new BugtrackerTag();
		$tag->setName($content->name);
		$tag->setProject($project);
		$tag->setColor($content->color);

		$em->persist($tag);
		$em->flush();

		$tagArray = $tag->objectToArray();
		$tagArray['projectId'] = $tag->getProject()->getId();

		$mdata['mtitle'] = "new tag bug";
		$mdata['mdesc'] = json_encode($tagArray);
		$wdata['type'] = "new tag bug";
		$wdata['targetId'] = $tag->getId();
		$wdata['message'] = json_encode($tagArray);
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($content->projectId, 'BugsTagsRepartition');

		return $this->setCreated("1.4.1", "Bugtracker", "tagCreation", "Complete Success", $tag->objectToArray());
	}

	/**
	* @api {put} /0.3/bugtracker/tag/:id Update tag
	* @apiName tagUpdate
	* @apiGroup Bugtracker
	* @apiDescription Update a tag
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the tag
	* @apiParam {String} name Name of the tag
	* @apiParam {string} color Color of the tag in hexa
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"name": "ASAP",
	*			"color": "FFFFFF"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the tag created
	* @apiSuccess {String} name Ticket tags name
	* @apiSuccess {string} color Color of the tag in hexa
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
	*			"name": "ASAP",
	*			"color": "FFFFFF"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "update tag bug",
	*			"body": {
	*				"id": 1,
	*				"projectId": 1,
	*				"name": "Urgent",
	*				"color": "FFFFFF"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.16.3",
	*			"return_message": "Bugtracker - tagUpdate - Bad Token"
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

		if ($content === null || !array_key_exists('name', $content) || !array_key_exists('tagId', $content) || !array_key_exists('color', $content))
			return $this->setBadRequest("4.16.6", "Bugtracker", "tagUpdate", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
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
		$tag->setColor($content->color);
		$em->flush();

		$tagArray = $tag->objectToArray();
		$tagArray['projectId'] = $tag->getProject()->getId();

		$mdata['mtitle'] = "update tag bug";
		$mdata['mdesc'] = json_encode($tagArray);
		$wdata['type'] = "update tag bug";
		$wdata['targetId'] = $tag->getId();
		$wdata['message'] = json_encode($tagArray);
		$userNotif = array();
		foreach ($tag->getProject()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "tagUpdate", "Complete Success", $tag->objectToArray());
	}

	/**
	* @api {get} /0.3/bugtracker/tag/:id Get tag info
	* @apiName tagInformations
	* @apiGroup Bugtracker
	* @apiDescription Get a tag informations
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiSuccess {Number} id Id of the tag
	* @apiSuccess {String} name Name of the tag
	* @apiSuccess {string} color Color of the tag in hexa
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
	*			"name": "ASAP",
	*			"color": "FFFFFF"
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.17.3",
	*			"return_message": "Bugtracker - tagInformations - Bad Token"
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
	* @apiErrorExample Bad Parameter: tag id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.17.4",
	*			"return_message": "Bugtracker - tagInformations - Bad Parameter: tag id"
	*		}
	*	}
	*/
	public function getTagInfosAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.17.3", "Bugtracker", "tagInformations"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('SQLBundle:BugtrackerTag')->find($id);
		if (!($tag instanceof BugtrackerTag))
			return $this->setBadRequest("4.17.4", "Bugtracker", "tagInformations", "Bad Parameter: tag id");

		if ($this->checkRoles($user, $tag->getProject()->getId(), "bugtracker") < 1)
			return ($this->setNoRightsError("4.17.9", "Bugtracker", "tagInformations"));

		return $this->setSuccess("4.17.3", "Bugtracker", "tagInformations", "Complete Success", $tag->objectToArray());
	}

	/**
	* @api {delete} /0.3/bugtracker/tag/:id Delete tag
	* @apiName deleteTag
	* @apiGroup Bugtracker
	* @apiDescription Delete a tag
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.4.1",
	*			"return_message": "Bugtracker - deleteTag - Complete Success"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "delete tag bug",
	*			"body": {
	*				"id": 1,
	*				"proejctId": 1,
	*				"name": "Urgent",
	*				"color": "FFFFFF"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.18.3",
	*			"return_message": "Bugtracker - deleteTag - Bad Token"
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
	* @apiErrorExample Bad Parameter: tag id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "4.18.4",
	*			"return_message": "Bugtracker - deleteTag - Bad Parameter: tag id"
	*		}
	*	}
	*/
	public function deleteTagAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.18.3", "Bugtracker", "deleteTag"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('SQLBundle:BugtrackerTag')->find($id);
		if (!($tag instanceof BugtrackerTag))
			return $this->setBadRequest("4.18.4", "Bugtracker", "deleteTag", "Bad Parameter: tag id");

		if ($this->checkRoles($user, $tag->getProject()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.18.9", "Bugtracker", "deleteTag"));

		$tagArray = $tag->objectToArray();
		$tagArray['projectId'] = $tag->getProject()->getId();

		$mdata['mtitle'] = "delete tag bug";
		$mdata['mdesc'] = json_encode($tagArray);
		$wdata['type'] = "delete tag bug";
		$wdata['targetId'] = $tag->getId();
		$wdata['message'] = json_encode($tagArray);
		$userNotif = array();
		foreach ($tag->getProject()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($tag);
		$em->flush();

		$this->get('service_stat')->updateStat($tag->getProject()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - deleteTag - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {put} /0.3/bugtracker/tag/assign/:bugId Assign tag
	* @apiName assignTagToBug
	* @apiGroup Bugtracker
	* @apiDescription Assign a tag to a bug
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} bugId Id of the bug
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"tagId": 3
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the bug
	* @apiSuccess {Object[]} tag Tag's informations
	* @apiSuccess {Number} tag.id Id of the tag
	* @apiSuccess {String} tag.name Name of the tag
	* @apiSuccess {string} tag.color Color of the tag in hexa
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
	*				"name": "To Do",
	*				"color": "FFFFFF"
	*			}
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "assign tag bug",
	*			"body": {
	*				"id": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*				"projectId": 1,
	*				"title": "Ticket de Test",
	*				"description": "Ceci est un ticket de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null,
	*				"clientOrigin": false,
	*				"state": true,
	*				"tags": [
	*			  		{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  		{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*				],
	*				"users": [
	*				  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*				  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*				]
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.19.3",
	*			"return_message": "Bugtracker - assignTagToBug - Bad Token"
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
	*			"return_message": "Bugtracker - assignTagToBug - Bad Parameter: bugId"
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
	public function assignTagAction(Request $request, $bugId)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || !array_key_exists('tagId', $content))
			return $this->setBadRequest("4.19.6", "Bugtracker", "assignTagToBug", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.19.3", "Bugtracker", "assignTagToBug"));

		$em = $this->getDoctrine()->getManager();
		$bug = $em->getRepository('SQLBundle:Bug')->find($bugId);
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

		$mdata['mtitle'] = "assign tag bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "assign tag bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		$userNotif = array();
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "assignTagToBug", "Complete Success",
			array("id" => $bug->getId(), "tag" => $tagToAdd->objectToArray()));
	}

	/**
	* @api {delete} /0.3/bugtracker/tag/remove/:bugId/:tagId Remove tag
	* @apiName removeTagToBug
	* @apiGroup Bugtracker
	* @apiDescription Remove a tag to a bug
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
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
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "remove tag bug",
	*			"body": {
	*				"id": 1,
	*				"creator": { "id": 13, "firstname": "John", "lastname": "Doe"},
	*				"projectId": 1,
	*				"title": "Ticket de Test",
	*				"description": "Ceci est un ticket de test",
	*				"createdAt": "2015-11-30 00:00:00",
	*				"editedAt": null,
	*				"clientOrigin": false,
	*				"state": true,
	*				"tags": [
	*			  		{ "id": 1, "name": "To Do", "color": "FFFAFA"},
	*			  		{ "id": 4, "name": "ASAP", "color": "F0F0F0"}
	*				],
	*				"users": [
	*				  	{ "id": 13, "firstname": "John", "lastname": "Doe" },
	*				  	{ "id": 16, "firstname": "Jane", "lastname": "Doe" }
	*				]
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.20.3",
	*			"return_message": "Bugtracker - removeTagToBug - Bad Token"
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
	public function removeTagAction(Request $request, $bugId, $tagId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
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

		$mdata['mtitle'] = "remove tag bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "remove tag bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		$userNotif = array();
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$bug->removeTag($tagToRemove);
		$em->flush();

		$this->get('service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - removeTagToBug - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /0.3/bugtracker/project/tags/:projectId Get tags by project
	* @apiName getProjectTags
	* @apiGroup Bugtracker
	* @apiDescription Get all the tags for a project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {int} id Id of the tag
	* @apiSuccess {string} name name of the tag
	* @apiSuccess {string} color Color of the tag
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
	*				{ "id": 1, "name": "To Do", "color": "FFFFFF" },
	*				{ "id": 1, "name": "Doing", "color": "134058" },
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "4.21.3",
	*			"return_message": "Bugtracker - getProjectTags - Bad Token"
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
	public function getProjectTagsAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.21.3", "Bugtracker", "getProjectTags"));

		if ($this->checkRoles($user, $projectId, "bugtracker") < 1)
			return ($this->setNoRightsError("4.21.9", "Bugtracker", "getProjectTags"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:BugtrackerTag');
		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tags = $qb->getResult();

		$arr = array();

		foreach ($tags as $t) {
			$arr[] = $t->objectToArray();
		}

		if (count($arr) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getProjectTags");
		return $this->setSuccess("1.4.1", "Bugtracker", "getProjectTags", "Complete Success", array("array" => $arr));
	}

}
