<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MongoBundle\Controller\RolesAndTokenVerificationController;

use MongoBundle\Document\User;
use MongoBundle\Document\Bug;
use MongoBundle\Document\BugComment;
use MongoBundle\Document\BugState;
use MongoBundle\Document\Tag;
use MongoBundle\Document\BugtrackerTag;
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
	* @-api {post} /0.3/bugtracker/ticket Post ticket
	* @apiName postTicket
	* @apiGroup Bugtracker
	* @apiDescription Post a ticket
	* @apiVersion 0.3.0
	*
	*/
	public function postTicketAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("projectId", $content) || !array_key_exists("title", $content)
			|| !array_key_exists("description", $content) || !array_key_exists("clientOrigin", $content)
			|| !array_key_exists("tags", $content) || !array_key_exists("users", $content))
				return $this->setBadRequest("4.2.6", "Bugtracker", "postTicket", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.2.3", "Bugtracker", "postTicket"));

		$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.2.4", "Bugtracker", "postTicket", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "bugtracker") < 2)
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
			$tagToAdd = $em->getRepository('MongoBundle:BugtrackerTag')->find($tag);
			if ($tagToAdd instanceof BugtrackerTag) {
				$assigned = false;

				$bugTags = $bug->getBugtrackerTags();
				if ($bugTags) {
					foreach ($bugTags as $key => $value) {
						if ($value->getId() == $tag)
							$assigned = true;
					}
				}

				if (!$assigned) {
					$bug->addBugtrackerTag($tagToAdd);
					$em->flush();
				}
			}
		}

		foreach ($content->users as $key => $guest) {
				$newGuest = $em->getRepository('MongoBundle:User')->find($guest);
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($content->projectId, 'BugsUsersRepartition');
		$this->get('mongo_service_stat')->updateStat($content->projectId, 'BugAssignationTracker');
		$this->get('mongo_service_stat')->updateStat($content->projectId, 'BugsTagsRepartition');

		return $this->setCreated("1.4.1", "Bugtracker", "postTicket", "Complete Success", $bug->objectToArray());
	}

	/**
	* @-api {put} /0.3/bugtracker/ticket/:id Edit ticket
	* @apiName editTicket
	* @apiGroup Bugtracker
	* @apiDescription Edit ticket
	* @apiVersion 0.3.0
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$bug = $em->getRepository('MongoBundle:Bug')->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.3.4", "Bugtracker", "postTicket", "Bad Parameter: bugId");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.3.9", "Bugtracker", "postTicket"));

		$bug->setTitle($content->title);
		$bug->setDescription($content->description);
		$bug->setClientOrigin($content->clientOrigin);
		$bug->setEditedAt(new DateTime('now'));

		$em->persist($bug);
		$em->flush();

		foreach ($content->removeTags as $tag) {
			$bugTags = $bug->getBugtrackerTags();
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
				$tagToRemove = $em->getRepository('MongoBundle:BugtrackerTag')->find($tag);
				$bug->removeBugtrackerTag($tagToRemove);
				$em->flush();
			}
		}

		foreach ($content->addTags as $tag) {
			$tagToAdd = $em->getRepository('MongoBundle:BugtrackerTag')->find($tag);
			if ($tagToAdd instanceof BugtrackerTag) {
				$assigned = false;
				$bugTags = $bug->getBugtrackerTags();
				if ($bugTags) {
					foreach ($bug->getBugtrackerTags() as $key => $value) {
						if ($value->getId() == $tag) {
							$assigned = true;
							break;
						}
					}
				}
				if (!$assigned) {
					$bug->addBugtrackerTag($tagToAdd);
					$em->flush();
				}
			}
		}

		$userNotif = array();
		foreach ($content->removeUsers as $key => $guest) {
			$oldGuest = $em->getRepository('MongoBundle:User')->find($guest);
			if ($oldGuest instanceof User) {
				$userNotif[] = $oldGuest;
				$bug->removeUser($oldGuest);
				$em->flush();
			}
		}

		foreach ($content->addUsers as $key => $guest) {
			$newGuest = $em->getRepository('MongoBundle:User')->find($guest);
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

		// NOTIFICATION
		$mdata['mtitle'] = "update bug";
		$mdata['mdesc'] = json_encode($bug->objectToArray());
		$wdata['type'] = "update bug";
		$wdata['targetId'] = $bug->getId();
		$wdata['message'] = json_encode($bug->objectToArray());
		foreach ($bug->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		// STATISTICS
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "editTicket", "Complete Success", $bug->objectToArray());
	}

	/**
	* @-api {delete} /0.3/bugtracker/ticket/close/:id Close ticket
	* @apiName closeTicket
	* @apiGroup Bugtracker
	* @apiDescription Close a ticket, to delete a comment see [deleteComment](/#api-Bugtracker-deleteComment) request
	* @apiVersion 0.3.0
	*
	*/
	public function closeTicketAction(Request $request, $id)
	{
		$em = $this->get('doctrine_mongodb')->getManager();

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.8.3", "Bugtracker", "closeTicket"));

		$bug = $em->getRepository("MongoBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.8.4", "Bugtracker", "closeTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.8.9", "Bugtracker", "closeTicket"));

		$bug->setState(false);

		$em->persist($bug);
		$em->flush();

		//notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - closeTicket - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {delete} /0.3/bugtracker/ticket/:id Delete ticket
	* @apiName deleteTicket
	* @apiGroup Bugtracker
	* @apiDescription Delete a ticket
	* @apiVersion 0.3.0
	*/
	public function deleteTicketAction(Request $request, $id)
	{
		$em = $this->get('doctrine_mongodb')->getManager();

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.25.3", "Bugtracker", "deleteTicket"));

		$bug = $em->getRepository("MongoBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.25.4", "Bugtracker", "deleteTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.25.9", "Bugtracker", "deleteTicket"));

		//notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($bug);
		$em->flush();

		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - deleteTicket - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {get} /0.3/bugtracker/ticket/reopen/:id Reopen closed ticket
	* @apiName reopenTicket
	* @apiGroup Bugtracker
	* @apiDescription Reopen a closed ticket
	* @apiVersion 0.3.0
	*
	*/
	public function reopenTicketAction(Request $request, $id)
	{
		$em = $this->get('doctrine_mongodb')->getManager();

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.23.3", "Bugtracker", "reopenTicket"));

		$bug = $em->getRepository("MongoBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.23.4", "Bugtracker", "reopenTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.23.9", "Bugtracker", "reopenTicket"));

		$bug->setState(true);

		$em->persist($bug);
		$em->flush();

		$class = new NotificationController();

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsTagsRepartition');

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
	* @-api {get} /0.3/bugtracker/ticket/:id Get ticket
	* @apiName getTicket
	* @apiGroup Bugtracker
	* @apiDescription Get ticket informations, tags and assigned users
	* @apiVersion 0.
	*
	*/
	public function getTicketAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.1.3", "Bugtracker", "getTicket"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$ticket = $em->getRepository("MongoBundle:Bug")->find($id);
		if (!($ticket instanceof Bug))
			return $this->setBadRequest("4.1.4", "Bugtracker", "getTicket", "Bad Parameter: id");

		if ($this->checkRoles($user, $ticket->getProjects()->getId(), "bugtracker") < 1)
			return ($this->setNoRightsError("4.1.9", "Bugtracker", "getTicket"));

			return $this->setSuccess("1.4.1", "Bugtracker", "getTicket", "Complete Success", $ticket->objectToArray());
		}

	/**
	* @-api {get} /0.3/bugtracker/tickets/opened/:id Get open tickets
	* @apiName getTickets
	* @apiGroup Bugtracker
	* @apiDescription Get all open tickets of a project
	* @apiVersion 0.3.0
	*
	*/
	public function getTicketsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.9.3", "Bugtracker", "getTickets"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.9.4", "Bugtracker", "getTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.9.9", "Bugtracker", "getTickets"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects.id" => $project->getId(), "state" => true));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @-api {get} /0.3/bugtracker/tickets/closed/:id Get closed tickets
	* @apiName getClosedTickets
	* @apiGroup Bugtracker
	* @apiDescription Get all closed tickets of a project
	* @apiVersion 0.3.0
	*
	*/
	public function getClosedTicketsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.22.3", "Bugtracker", "getClosedTickets"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.22.4", "Bugtracker", "getClosedTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.22.9", "Bugtracker", "getClosedTickets"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects.id" => $project->getId(), "state" => false));
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getClosedTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getClosedTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @-api {get} /0.3/bugtracker/tickets/opened/:id/:offset/:limit Get last opened tickets
	* @apiName getLastOpenedTickets
	* @apiGroup Bugtracker
	* @apiDescription Get X last opened tickets from offset Y
	* @apiVersion 0.3.0
	*
	*/
	public function getLastTicketsAction(Request $request, $id, $offset, $limit)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.10.3", "Bugtracker", "getLastTickets"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.10.4", "Bugtracker", "getLastTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.10.9", "Bugtracker", "getLastTickets"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects.id" => $project->getId(), "state" => true), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getLastTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getLastTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @-api {get} /0.3/bugtracker/tickets/closed/:id/:offset/:limit Get last closed tickets
	* @apiName getLastClosedTickets
	* @apiGroup Bugtracker
	* @apiDescription Get X last closed tickets from offset Y
	* @apiVersion 0.3
	*
	*/
	public function getLastClosedTicketsAction(Request $request, $id, $offset, $limit)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.11.3", "Bugtracker", "getLastClosedTickets"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.11.4", "Bugtracker", "getLastClosedTickets", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.11.9", "Bugtracker", "getLastClosedTickets"));

		$tickets = $em->getRepository("MongoBundle:Bug")->findBy(array("projects.id" => $project->getId(), "state" => false), array(), $limit, $offset);
		$ticketsArray = array();
		foreach ($tickets as $key => $value) {
			$ticketsArray[] = $value->objectToArray();
		}

		if (count($ticketsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getLastClosedTickets");
		return $this->setSuccess("1.4.1", "Bugtracker", "getLastClosedTickets", "Commplete Success", array("array" => $ticketsArray));
	}

	/**
	* @-api {get} /0.3/bugtracker/tickets/user/:id/:user Get opened tickets by user
	* @apiName getTicketsByUser
	* @apiGroup Bugtracker
	* @apiDescription Get open tickets asssigned to a user for a project
	* @apiVersion 0.3.0
	*
	*/
	public function getTicketsByUserAction(Request $request, $id, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.12.3", "Bugtracker", "getTicketsByUser"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("4.12.4", "Bugtracker", "getTicketsByUser", "Bad Parameter: id");

		if ($this->checkRoles($user, $id, "bugtracker") < 1)
			return ($this->setNoRightsError("4.12.9", "Bugtracker", "getTicketsByUser"));

		$tickets = $em->getRepository("MongoBundle:Bug")->createQueryBuilder()
									 ->field('projects.id')->equals($project->getId())
									 ->field('state')->equals(true)
									 ->field('users.id')->equals($userId)
									 ->getQuery()->execute();

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
	* @-api {put} /0.3/bugtracker/users/:id Set participants
	* @apiName setParticipants
	* @apiGroup Bugtracker
	* @apiDescription Assign/unassign users to a ticket
	* @apiVersion 0.3.0
	*
	*/
	public function setParticipantsAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("toAdd", $content) || !array_key_exists("toRemove", $content))
			return $this->setBadRequest("4.7.6", "Bugtracker", "setParticipants", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.7.3", "Bugtracker", "setParticipants"));

		$bug = $em->getRepository("MongoBundle:Bug")->find($id);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.7.4", "Bugtracker", "setParticipants", "Bad Parameter: bugId");

		if ($this->checkRoles($user, $bug->getProjects()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.7.9", "Bugtracker", "setParticipants"));

		foreach ($content->toAdd as $key => $value) {
			$toAddUser = $em->getRepository("MongoBundle:User")->find($value);
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
			$toRemoveuser = $em->getRepository("MongoBundle:User")->find($value);

			if ($toRemoveuser instanceof User)
			{
				$bug->removeUser($toRemoveuser);
			}
		}

		$em->persist($bug);
		$em->flush();

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugsUsersRepartition');
		$this->get('mongo_service_stat')->updateStat($bug->getProjects()->getId(), 'BugAssignationTracker');

		return $this->setSuccess("1.4.1", "Bugtracker", "setParticipants", "Complete Success", $bug->objectToArray());
	}

	/*
	 * --------------------------------------------------------------------
	 *														COMMENTS
	 * --------------------------------------------------------------------
	*/

	/**
	* @-api {get} /0.3/bugtracker/comments/:ticketId Get comments by bug
	* @apiName getComments
	* @apiGroup Bugtracker
	* @apiDescription Get all comments of a bug ticket
	* @apiVersion 0.3.0
	*
	*/
	public function getCommentsAction(Request $request, $ticketId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.4.3", "Bugtracker", "getComments"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$ticket = $em->getRepository("MongoBundle:Bug")->find($ticketId);
		if (!($ticket instanceof Bug))
			return $this->setBadRequest("4.4.4", "Bugtracker", "getComments", "Bad Parameter: id");

		if ($this->checkRoles($user, $ticket->getProjects()->getId(), "bugtracker") < 1)
			return ($this->setNoRightsError("4.4.9", "Bugtracker", "getComments"));

		$comments = $em->getRepository("MongoBundle:BugComment")->findByBugs($ticket);
		$commentsArray = array();
		foreach ($comments as $key => $value) {
			$commentsArray[] = $value->objectToArray();
		}

		if (count($commentsArray) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getComments");
		return $this->setSuccess("1.4.1", "Bugtracker", "getComments", "Complete Success", array("array" => $commentsArray));
	}

	/**
	* @-api {post} /0.3/bugtracker/comment Post comment
	* @apiName postComment
	* @apiGroup Bugtracker
	* @apiDescription Post comment on a bug ticket
	* @apiVersion 0.3.0
	*
	*/
	public function postCommentAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("parentId", $content) || !array_key_exists("comment", $content))
			return $this->setBadRequest("4.5.6", "Bugtracker", "postComment", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.5.3", "Bugtracker", "postComments"));

		$parent = $em->getRepository("MongoBundle:Bug")->find($content->parentId);
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

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.4.1", "Bugtracker", "postComment", "Complete Success", $ticket);
	}

	/**
	* @-api {put} /0.3/bugtracker/comment/:id Edit comment
	* @apiName EditComment
	* @apiGroup Bugtracker
	* @apiDescription Edit a comment
	* @apiVersion 0.3.0
	*
	*/
	public function editCommentAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("comment", $content))
				return $this->setBadRequest("4.6.6", "Bugtracker", "editComments", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.6.3", "Bugtracker", "editComments"));

		$comment = $em->getRepository("MongoBundle:BugComment")->find($id);
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

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.4.1", "Bugtracker", "editComment", "Complete Success", $comment->objectToArray());
	}

	/**
	* @-api {delete} /0.3/bugtracker/comment/:id Delete comment
	* @apiName deletecomment
	* @apiGroup Bugtracker
	* @apiDescription Delete a comment (creator allowed only)
	* @apiVersion 0.3.0
	*/
	public function deleteCommentAction(Request $request, $id)
	{
		$em = $this->get('doctrine_mongodb')->getManager();

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.24.3", "Bugtracker", "deleteComment"));

		$comment = $em->getRepository("MongoBundle:BugComment")->find($id);
		if (!($comment instanceof BugComment))
			return $this->setBadRequest("4.24.4", "Bugtracker", "deleteComment", "Bad Parameter: id");

		if ($comment->getCreator()->getId() != $user->getId())
			return ($this->setNoRightsError("4.24.9", "Bugtracker", "deleteComment"));

		$com = $comment->objectToArray();
		$com['projectId'] = $comment->getBugs()->getProjects()->getId();

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

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
	* @-api {post} /0.3/bugtracker/tag Create tag
	* @apiName tagCreation
	* @apiGroup Bugtracker
	* @apiDescription Create a tag
	* @apiVersion 0.3.0
	*
	*/
	public function tagCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || !array_key_exists('name', $content) || !array_key_exists('projectId', $content)
			|| !array_key_exists('color', $content))
			return $this->setBadRequest("4.15.6", "Bugtracker", "tagCreation", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.15.3", "Bugtracker", "tagCreation"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
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

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($content->projectId, 'BugsTagsRepartition');

		return $this->setCreated("1.4.1", "Bugtracker", "tagCreation", "Complete Success", $tag->objectToArray());
	}

	/**
	* @-api {put} /0.3/bugtracker/tag/:id Update tag
	* @apiName tagUpdate
	* @apiGroup Bugtracker
	* @apiDescription Update a tag
	* @apiVersion 0.3.0
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:BugtrackerTag')->find($content->tagId);
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

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "tagUpdate", "Complete Success", $tag->objectToArray());
	}

	/**
	* @-api {get} /0.3/bugtracker/tag/:id Get tag info
	* @apiName tagInformations
	* @apiGroup Bugtracker
	* @apiDescription Get a tag informations
	* @apiVersion 0.3.0
	*
	*/
	public function getTagInfosAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.17.3", "Bugtracker", "tagInformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:BugtrackerTag')->find($id);
		if (!($tag instanceof BugtrackerTag))
			return $this->setBadRequest("4.17.4", "Bugtracker", "tagInformations", "Bad Parameter: tag id");

		if ($this->checkRoles($user, $tag->getProject()->getId(), "bugtracker") < 1)
			return ($this->setNoRightsError("4.17.9", "Bugtracker", "tagInformations"));

		return $this->setSuccess("4.17.3", "Bugtracker", "tagInformations", "Complete Success", $tag->objectToArray());
	}

	/**
	* @-api {delete} /0.3/bugtracker/tag/:id Delete tag
	* @apiName deleteTag
	* @apiGroup Bugtracker
	* @apiDescription Delete a tag
	* @apiVersion 0.3.0
	*
	*/
	public function deleteTagAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.18.3", "Bugtracker", "deleteTag"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:BugtrackerTag')->find($id);
		if (!($tag instanceof BugtrackerTag))
			return $this->setBadRequest("4.18.4", "Bugtracker", "deleteTag", "Bad Parameter: tag id");

		if ($this->checkRoles($user, $tag->getProject()->getId(), "bugtracker") < 2)
			return ($this->setNoRightsError("4.18.9", "Bugtracker", "deleteTag"));

		$tagArray = $tag->objectToArray();
		$tagArray['projectId'] = $tag->getProject()->getId();

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($tag);
		$em->flush();

		$this->get('mongo_service_stat')->updateStat($tag->getProject()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - deleteTag - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {put} /0.3/bugtracker/tag/assign/:bugId Assign tag
	* @apiName assignTagToBug
	* @apiGroup Bugtracker
	* @apiDescription Assign a tag to a bug
	* @apiVersion 0.3.0
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$bug = $em->getRepository('MongoBundle:Bug')->find($bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.19.4", "Bugtracker", "assignTagToBug", "Bad Parameter: bugId");

		$projectId = $bug->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "bugtracker") < 2)
			return ($this->setNoRightsError("4.19.9", "Bugtracker", "assignTagToBug"));

		$tagToAdd = $em->getRepository('MongoBundle:BugtrackerTag')->find($content->tagId);
		if (!($tagToAdd instanceof BugtrackerTag))
			return $this->setBadRequest("4.19.4", "Bugtracker", "assignTagToBug", "Bad Parameter: tagId");

		$tags = $bug->getBugtrackerTags();
		foreach ($tags as $tag) {
			if ($tag === $tagToAdd)
				return $this->setBadRequest("4.192.7", "Bugtracker", "assignTagToBug", "Already In Database");
		}

		$bug->addBugtrackerTag($tagToAdd);
		$em->flush();

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		return $this->setSuccess("1.4.1", "Bugtracker", "assignTagToBug", "Complete Success",
			array("id" => $bug->getId(), "tag" => $tagToAdd->objectToArray()));
	}

	/**
	* @-api {delete} /0.3/bugtracker/tag/remove/:bugId/:tagId Remove tag
	* @apiName removeTagToBug
	* @apiGroup Bugtracker
	* @apiDescription Remove a tag to a bug
	* @apiVersion 0.3.0
	*
	*/
	public function removeTagAction(Request $request, $bugId, $tagId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.20.3", "Bugtracker", "removeTagToBug"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$bug = $em->getRepository('MongoBundle:Bug')->find($bugId);
		if (!($bug instanceof Bug))
			return $this->setBadRequest("4.20.4", "Bugtracker", "removeTagToBug", "Bad Parameter: bugId");

		$projectId = $bug->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "bugtracker") < 2)
			return ($this->setNoRightsError("4.20.9", "Bugtracker", "removeTagToBug"));

		$tagToRemove = $em->getRepository('MongoBundle:BugtrackerTag')->find($tagId);
		if (!($tagToRemove instanceof BugtrackerTag))
			return $this->setBadRequest("4.20.4", "Bugtracker", "removeTagToBug", "Bad Parameter: tagId");

		$tags = $bug->getBugtrackerTags();
		$isAssign = false;
		foreach ($tags as $tag) {
			if ($tag === $tagToRemove)
			{
				$isAssign = true;
			}
		}

		if ($isAssign === false)
			return $this->setBadRequest("4.20.4", "Bugtracker", "removeTagToBug", "Bad Parameter: tagId");

		// notifs
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$bug->removeBugtrackerTag($tagToRemove);
		$em->flush();

		$this->get('mongo_service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.4.1";
		$response["info"]["return_message"] = "Bugtracker - removeTagToBug - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {get} /0.3/bugtracker/project/tags/:projectId Get tags by project
	* @apiName getProjectTags
	* @apiGroup Bugtracker
	* @apiDescription Get all the tags for a project
	* @apiVersion 0.3.0
	*
	*/
	public function getProjectTagsAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("4.21.3", "Bugtracker", "getProjectTags"));

		if ($this->checkRoles($user, $projectId, "bugtracker") < 1)
			return ($this->setNoRightsError("4.21.9", "Bugtracker", "getProjectTags"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:BugtrackerTag');
		$qb = $repository->createQueryBuilder()->field('project.id')->equals($projectId);
		$tags = $qb->getQuery()->execute();

		$arr = array();

		foreach ($tags as $t) {
			$arr[] = $t->objectToArray();
		}

		if (count($arr) <= 0)
			return $this->setNoDataSuccess("1.4.3", "Bugtracker", "getProjectTags");
		return $this->setSuccess("1.4.1", "Bugtracker", "getProjectTags", "Complete Success", array("array" => $arr));
	}

}
