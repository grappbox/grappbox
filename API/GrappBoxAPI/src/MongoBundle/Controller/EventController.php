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
*/


class EventController extends RolesAndTokenVerificationController
{
	/**
	* @api {get} /mongo/event/getTypes/:token Get event types
	* @apiName getTypes
	* @apiGroup Event
	* @apiVersion 0.11.0
	*
	* @apiParam {string} token user authentication token
	*
	*
	*/
	public function getTypesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$types = $em->getRepository("MongoBundle:EventType")->findAll();

		$types_array = array();
		foreach ($types as $key => $value) {
			$types_array[] = $value->objectToArray();
		}

		return new JsonResponse(array($types_array));
	}

	/**
	* @api {get} /mongo/event/getevent/:token/:id get event
	* @apiName getEvent
	* @apiGroup Event
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id event id
	* @apiParam {string} token user authentication token
	*
	*
	*/
	public function getEventAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if ($event->getProjects() instanceof Project)
			{
				$project = $event->getProjects();
				if (!$this->checkRoles($user, $project->getId(), "event"))
					return ($this->setNoRightsError());
			}
		else
			{
				$check = false;
				foreach ($event->getUsers() as $key => $value) {
					 if ($user->getId() == $value->getId())
					 		$check = true;
				}
				if (!$check)
					return ($this->setNoRightsError());
			}

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}

		return new JsonResponse(array("event" => $event->objectToArray(), "users" => $participants));
	}

	/**
	* @api {post} /mongo/event/setparticipants/:id Add/remove users to the event
	* @apiName setParticipants
	* @apiGroup Event
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id event id
	* @apiParam {string} token user authentication token
	* @apiParam {string[]} toAdd list of users' email to add
	* @apiParam {int[]} toRemove list of users' id to remove
	*
	*
	*/
	public function setParticipantsAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if ($event->getProjects() instanceof Project)
		{
			if (!$this->checkRoles($user, $event->getProjects()->getId(), "event"))
				return ($this->setNoRightsError());
		}
		else {
			$check = false;
			foreach ($event->getUsers() as $key => $value) {
				 if ($user->getId() == $value->getId())
						$check = true;
			}
			if (!$check)
				return ($this->setNoRightsError());
		}
		if (array_key_exists("projectId", $content))
		{
			$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
			if (!$this->checkRoles($user, $content->projectId, "event"))
				return ($this->setNoRightsError());
		}

		$class = new NotificationController();

		$mdata['mtitle'] = "Event - Event Assigned";
		$mdata['mdesc'] = "You have been assigned to event ".$event->getTitle();

		$wdata['type'] = "Event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = "You have been assigned to event ".$event->getTitle();

		foreach ($content->toAdd as $key => $value) {
			$toAddUser = $em->getRepository("MongoBundle:User")->find($value);
			if ($toAddUser instanceof User)
			{
				foreach ($event->getUsers() as $key => $value) {
					if ($user->getId() == $toAddUser->getId())
						return $this->setBadRequest("User already in the list");
				}

				$event->addUser($toAddUser);

				$userNotif = array($value);
				$class->pushNotification($userNotif, $mdata, $wdata, $em);
			}
		}

		$mdata['mtitle'] = "Event - Event Remove";
		$mdata['mdesc'] = "You have been removed of event ".$event->getTitle();

		$wdata['type'] = "Event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = "You have been removed of event ".$event->getTitle();

		foreach ($content->toRemove as $key => $value) {
			$toRemoveUser = $em->getRepository("MongoBundle:User")->find($value);
			if ($toRemoveUser instanceof User)
			{
				if ($toRemoveUser->getId() == $event->getCreatorUser()->getId())
					return $this->setBadRequest("Try to remove creator");

				$event->removeUser($toRemoveUser);

				$userNotif = array($value);
				$class->pushNotification($userNotif, $mdata, $wdata, $em);
			}
		}

		$em->persist($event);
		$em->flush();

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}

		return new JsonResponse(array("event" => $event->objectToArray(), "users" => $participants));
	}

	/**
	* @api {post} /mongo/event/postevent/:id Post an event/meeting
	* @apiName postEvent
	* @apiGroup Event
	* @apiVersion 0.11.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {int}	[projectId] project's id (if related to a project)
	*	@apiParam {string} title event title
	*	@apiParam {string} description event description
	*	@apiParam {int} typeId event type id
	*	@apiParam {DateTime} begin beginning date & hour of the event
	*	@apiParam {DateTime} end ending date & hour of the event
	*
	*
	*/
	public function postEventAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		if (array_key_exists("projectId", $content))
		{
			$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
			if (!$this->checkRoles($user, $content->projectId, "event"))
				return ($this->setNoRightsError());
		}

		$event = new Event();
		$event->setCreatorUser($user);
		if (array_key_exists("projectId", $content))
			$event->setProjects($project);
		$type = $em->getRepository("MongoBundle:EventType")->find($content->typeId);
		$event->setEventtypes($type);
		$event->setTitle($content->title);
		$event->setDescription($content->description);
		$event->setBeginDate(new DateTime($content->begin));
		$event->setEndDate(new DateTime($content->end));
		$event->setCreatedAt(new DateTime('now'));

		$em->persist($event);
		$em->flush();

		$event->addUser($user);
		$em->flush();

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}

		return new JsonResponse(array("event" => $event->objectToArray(), "users" => $participants));
	}

	/**
	* @api {post} /mongo/event/editevent/:id Edit an event/meeting
	* @apiName editEvent
	* @apiGroup Event
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id event id
	* @apiParam {string} token user authentication token
	* @apiParam {int}	[projectId] project's id (if related to a project)
	*	@apiParam {string} title event title
	*	@apiParam {string} description event description
	*	@apiParam {int} typeId event type id
	*	@apiParam {DateTime} begin beginning date & hour of the event
	*	@apiParam {DateTime} end ending date & hour of the event
	*
	*/
	public function editEventAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if ($event->getProjects() instanceof Project)
		{
			if (!$this->checkRoles($user, $event->getProjects()->getId(), "event"))
				return ($this->setNoRightsError());
		}
		else {
			$check = false;
			foreach ($event->getUsers() as $key => $value) {
				 if ($user->getId() == $value->getId())
						$check = true;
			}
			if (!$check)
				return ($this->setNoRightsError());
		}
		if (array_key_exists("projectId", $content))
		{
			$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
			if (!$this->checkRoles($user, $content->projectId, "event"))
				return ($this->setNoRightsError());
		}

		if (array_key_exists("projectId", $content))
			$event->setProjects($project);
		$type = $em->getRepository("MongoBundle:EventType")->find($content->typeId);
		$event->setEventtypes($type);
		$event->setTitle($content->title);
		$event->setDescription($content->description);
		$event->setBeginDate(new DateTime($content->begin));
		$event->setEndDate(new DateTime($content->end));
		$event->setEditedAt(new DateTime('now'));

		$em->persist($event);
		$em->flush();

		$participants = array();
		foreach ($event->getUsers() as $key => $value) {
			$participants[] = array(
				"id" => $value->getId(),
				"name" => $value->getFirstname()." ".$value->getLastName(),
				"email" => $value->getEmail(),
				"avatar" => $value->getAvatar()
			);
		}

		$class = new NotificationController();

		$mdata['mtitle'] = "Event - Event Edited";
		$mdata['mdesc'] = "The event ".$event->getTitle()." has been edited";

		$wdata['type'] = "Event";
		$wdata['targetId'] = $event->getId();
		$wdata['message'] = "The event ".$event->getTitle()." has been edited";

		$userNotif = array();
		foreach ($event->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}

		if (count($userNotif) > 0)
			$class->pushNotification($userNotif, $mdata, $wdata, $em);

		return new JsonResponse(array("event" => $event->objectToArray(), "users" => $participants));
	}

	/**
	* @api {delete} /mongo/event/delevent/:token/:id Delete an event/meeting
	* @apiName delEvent
	* @apiGroup Event
	* @apiVersion 0.11.0
	*
	* @apiParam {int} id event id
	* @apiParam {string} token user authentication token
	*
	*
	*/
	public function delEventAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$event = $em->getRepository("MongoBundle:Event")->find($id);
		if ($event->getProjects() instanceof Project)
			{
				$project = $event->getProjects();
				if (!$this->checkRoles($user, $project->getId(), "event"))
					return ($this->setNoRightsError());
			}
		else if ($user->getId() != $event->getCreatorUser()->getId())
			{
				return ($this->setNoRightsError());
			}

		$event->setDeletedAt(new DateTime('now'));

		$em->flush();

		return new JsonResponse('Success');
	}
}
