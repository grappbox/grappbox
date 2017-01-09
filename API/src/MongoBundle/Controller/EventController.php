<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MongoBundle\Document\Event;
use MongoBundle\Document\EventType;
use MongoBundle\Document\User;
use MongoBundle\Document\Project;
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


class EventController extends RolesAndTokenVerificationController
{
	/**
	* @-api {post} /0.3/event Post event
	* @apiName postEvent
	* @apiGroup Event
	* @apiDescription Post an event/meeting
	* @apiVersion 0.3.0
	*
	*/
	public function postEventAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("title", $content) || !array_key_exists("description", $content)
			|| !array_key_exists("begin", $content)|| !array_key_exists("end", $content) || !array_key_exists("users", $content))
			return $this->setBadRequest("5.4.6", "Calendar", "postEvent", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("5.4.3", "Calendar", "postEvent"));

		$em = $this->get('doctrine_mongodb')->getManager();
		if (array_key_exists("projectId", $content))
		{
			$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
			if ($this->checkRoles($user, $content->projectId, "event") < 2)
				return ($this->setNoRightsError("5.3.9", "Calendar", "postEvent"));
		}

		$event = new Event();
		$event->setCreatorUser($user);
		if (array_key_exists("projectId", $content))
			$event->setProjects($project);
		$event->setTitle($content->title);
		$event->setDescription($content->description);
		$event->setBeginDate(new DateTime($content->begin));
		$event->setEndDate(new DateTime($content->end));
		$event->setCreatedAt(new DateTime('now'));

		$em->persist($event);
		$em->flush();

		$event->addUser($user);
		$em->flush();

		foreach ($content->users as $key => $guest) {
			if ($guest != $user->getId()) {
				$newGuest = $em->getRepository('MongoBundle:User')->find($guest);
				if ($newGuest instanceof User) {
					$alreadyAdded = false;
					foreach ($event->getUsers() as $key => $event_value) {
						if ($guest == $event_value->getId())
							$alreadyAdded = true;
					}
					if (!$alreadyAdded) {
						$event->addUser($newGuest);
						$em->flush();
					}
				}
			}
		}

		$mdata['mtitle'] = "new event";
		$mdata['mdesc'] = json_encode($event->objectToArray());
		$wdata['type'] = "new event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = json_encode($event->objectToArray());
		$userNotif = array();
		$userNotif[] = $event->getCreatorUser()->getId();
		foreach ($event->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.5.1", "Calendar", "postEvent", "Complete Success", $event->objectToArray());
	}

	/**
	* @-api {put} /0.3/event/:id Edit event
	* @apiName editEvent
	* @apiGroup Event
	* @apiDescription Edit an event/meeting
	* @apiVersion 0.3.0
	*
	*/
	public function editEventAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("title", $content) || !array_key_exists("description", $content)
			|| !array_key_exists("begin", $content)|| !array_key_exists("end", $content))
			return $this->setBadRequest("5.5.6", "Calendar", "editEvent", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("5.5.3", "Calendar", "editEvent"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("5.5.9", "Calendar", "editEvent", "Bad Parameter: eventId");

		if ($event->getProjects() instanceof Project)
		{
			if ($this->checkRoles($user, $event->getProjects()->getId(), "event") < 2)
				return ($this->setNoRightsError("5.5.9", "Calendar", "editEvent"));
		}
		else {
			$check = false;
			foreach ($event->getUsers() as $key => $value) {
				 if ($user->getId() == $value->getId())
						$check = true;
			}
			if (!$check)
				return ($this->setNoRightsError("5.5.9", "Calendar", "editEvent"));
		}
		if (array_key_exists("projectId", $content))
		{
			$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
			if ($this->checkRoles($user, $content->projectId, "event") < 2)
				return ($this->setNoRightsError("5.5.9", "Calendar", "editEvent"));
		}

		if (array_key_exists("projectId", $content))
			$event->setProjects($project);
		$event->setTitle($content->title);
		$event->setDescription($content->description);
		$event->setBeginDate(new DateTime($content->begin));
		$event->setEndDate(new DateTime($content->end));
		$event->setEditedAt(new DateTime('now'));

		$em->persist($event);
		$em->flush();

		$userNotif = array();
		$userNotif[] = $event->getCreatorUser()->getId();

		if (array_key_exists("toRemoveUsers", $content)) {
			foreach ($content->toRemoveUsers as $key => $guest) {
					$oldGuest = $em->getRepository('MongoBundle:User')->find($guest);
					if ($oldGuest instanceof User) {
						$creator = false;
						if ($guest == $event->getCreatorUser()->getId())
							$creator = true;
						if (!$creator) {
							$userNotif[] = $oldGuest;

							$event->removeUser($oldGuest);
							$em->flush();
						}
					}
			}
		}

		if (array_key_exists("toAddUsers", $content)) {
			foreach ($content->toAddUsers as $key => $guest) {
					$newGuest = $em->getRepository('MongoBundle:User')->find($guest);
					if ($newGuest instanceof User) {
						$alreadyAdded = false;
						foreach ($event->getUsers() as $key => $event_value) {
							if ($guest == $event_value->getId())
								$alreadyAdded = true;
						}
						if (!$alreadyAdded) {
							$event->addUser($newGuest);
							$em->flush();
						}
					}
			}
		}

		$mdata['mtitle'] = "update event";
		$mdata['mdesc'] = json_encode($event->objectToArray());
		$wdata['type'] = "update event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = json_encode($event->objectToArray());
		foreach ($event->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.5.1", "Calendar", "editEvent", "Complete Success", $event->objectToArray());
	}

	/**
	* @-api {delete} /0.3/event/:id Delete event
	* @apiName delEvent
	* @apiGroup Event
	* @apiDescription Delete an event/meeting
	* @apiVersion 0.3.0
	*
	*/
	public function delEventAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("5.6.3", "Calendar", "delEvent"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("5.6.4", "Calendar", "delEvent", "Bad Parameter: id");

		if ($event->getProjects() instanceof Project)
		{
			$project = $event->getProjects();
			if ($this->checkRoles($user, $project->getId(), "event") < 2)
				return ($this->setNoRightsError("5.6.9", "Calendar", "delEvent"));
		}
		else if ($user->getId() != $event->getCreatorUser()->getId())
		{
			return ($this->setNoRightsError("5.6.9", "Calendar", "delEvent"));
		}

		$mdata['mtitle'] = "delete event";
		$mdata['mdesc'] = json_encode($event->objectToArray());
		$wdata['type'] = "delete event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = json_encode($event->objectToArray());
		$userNotif = array();
		$userNotif[] = $event->getCreatorUser()->getId();
		foreach ($event->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($event);
		$em->flush();

		$response["info"]["return_code"] = "1.5.1";
		$response["info"]["return_message"] = "Calendar - delEvent - Complete Success";
		return new JsonResponse($response);
	}


	/*
	 * --------------------------------------------------------------------
	 *													GETTERS
	 * --------------------------------------------------------------------
	*/

	/**
	* @-api {get} /0.3/event/:id Get event
	* @apiName getEvent
	* @apiGroup Event
	* @apiDescription Get an event informations
	* @apiVersion 0.3.0
	*
	*/
	public function getEventAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("5.2.3", "Calendar", "getEvent"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("5.2.4", "Calendar", "getEvent", "Bad Parameter: id");

		if ($event->getProjects() instanceof Project)
		{
			$project = $event->getProjects();
			if ($this->checkRoles($user, $project->getId(), "event") < 1)
				return ($this->setNoRightsError("5.2.9", "Calendar", "getEvent"));
		}
		else
		{
			$check = false;
			foreach ($event->getUsers() as $key => $value) {
				 if ($user->getId() == $value->getId())
						$check = true;
			}
			if (!$check)
				return ($this->setNoRightsError("5.2.9", "Calendar", "getEvent"));
		}

		return $this->setSuccess("1.5.1", "Calendar", "getEvent", "Complete Success", $event->objectToArray());
	}


	/*
	 * --------------------------------------------------------------------
	 *														USERS
	 * --------------------------------------------------------------------
	*/

	/**
	* @-api {put} /0.3/event/users/:id Set participants
	* @apiName setParticipants
	* @apiGroup Event
	* @apiDescription Add/remove users to the event
	* @apiVersion 0.3.0
	*
	*/
	public function setParticipantsAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists("toAdd", $content) || !array_key_exists("toRemove", $content))
			return $this->setBadRequest("5.3.6", "Calendar", "setParticipants", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("5.3.3", "Calendar", "setParticipants"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("5.3.4", "Calendar", "setParticipants", "Bad Parameter: eventId");

		if ($event->getProjects() instanceof Project)
		{
			if ($this->checkRoles($user, $event->getProjects()->getId(), "event") < 2)
				return ($this->setNoRightsError("5.3.9", "Calendar", "setParticipants"));
		}
		else {
			$check = false;
			foreach ($event->getUsers() as $key => $value) {
				 if ($user->getId() == $value->getId())
						$check = true;
			}
			if (!$check)
				return ($this->setNoRightsError("5.3.9", "Calendar", "setParticipants"));
		}

		foreach ($content->toAdd as $key => $value) {
			$toAddUser = $em->getRepository("MongoBundle:User")->find($value);
			if ($toAddUser instanceof User)
			{
				foreach ($event->getUsers() as $key => $event_value) {
					if ($toAddUser->getId() == $event_value->getId())
						return $this->setBadRequest("5.3.4", "Calendar", "setParticipants", "Already in Database");
				}

				$event->addUser($toAddUser);
			}
		}

		$userNotif = array();
		$userNotif[] = $event->getCreatorUser()->getId();

		foreach ($content->toRemove as $key => $value) {
			$toRemoveUser = $em->getRepository("MongoBundle:User")->find($value);
			if ($toRemoveUser instanceof User)
			{
				if ($toRemoveUser->getId() == $event->getCreatorUser()->getId())
					return $this->setBadRequest("5.3.7", "Calendar", "setParticipants", "Bad Parameter: toRemove-id");

				$userNotif[] = $toRemoveUser;

				$event->removeUser($toRemoveUser);
			}
		}

		$em->persist($event);
		$em->flush();

		$mdata['mtitle'] = "participants event";
		$mdata['mdesc'] = json_encode($event->objectToArray());
		$wdata['type'] = "participants event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = json_encode($event->objectToArray());
		foreach ($event->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.5.1", "Calendar", "setParticipants", "Complete Success", $event->objectToArray());
	}

}
