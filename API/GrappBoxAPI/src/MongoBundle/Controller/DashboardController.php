<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use MongoBundle\Controller\RolesAndTokenVerificationController;
use MongoBundle\Document\Project;
use MongoBundle\Document\User;

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
 *	@IgnoreAnnotation("apiDescription")
 */
class DashboardController extends RolesAndTokenVerificationController
{
	/**
  	* @api {get} /mongo/dashboard/getteamoccupation/:token Get a team occupation
  	* @apiName getTeamOccupation
  	* @apiGroup Dashboard
  	* @apiDescription This method is for getting a team occupation for all the projects the user connected is the creator
  	* @apiVersion 0.2.0
  	*
  	*/
	public function getTeamOccupationAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.1.3", "Dashboard", "getteamoccupation"));

		$project = $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Project')->find($id);

		if ($project === null)
			return $this->setBadRequest("2.1.4", "Dashboard", "getteamoccupation", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $project->getId(), "projectSettings") < 2)
			return ($this->setNoRightsError("2.1.9", "Dashboard", "getteamoccupation"));

		return $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Project')->findTeamOccupationV2($project->getId());
	}

	/**
	* @api {get} /mongo/dashboard/getnextmeetings/:token/:id Get the person connected next meetings
	* @apiName getNextMeetings
	* @apiGroup Dashboard
	* @apiDescription Get all next meetings, in 7 days, of the connected user
	* @apiVersion 0.2.0
	*
	*/
	public function getNextMeetingsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.2.3", "Dashboard", "getnextmeetings"));

		return $this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Event')->findNextMeetingsV2($user->getId(), $id, "2", "Dashboard", "getnextmeetings");
	}

	/**
	* @api {get} /mongo/dashboard/getprojectsglobalprogress/:token Get projects global progress
	* @apiName getProjectsGlobalProgress
	* @apiGroup Dashboard
	* @apiDescription Get the global progress of the projects of a user
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectsGlobalProgressAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.3.3", "Dashboard", "getProjectsGlobalProgress"));

		return ($this->get('doctrine_mongodb')->getManager()->getRepository('MongoBundle:Project')->findProjectGlobalProgressV2($user->getId()));
	}

	/**
	* @api {get} /mongo/dashboard/getprojectcreator/:token/:id Get a project creator
	* @apiName getProjectCreator
	* @apiGroup Dashboard
	* @apiDescription Get the creator of the project
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectCreatorAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.4.3", "Dashboard", "getProjectCreator"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("2.4.4", "Dashboard", "getProjectCreator", "Bad Parameter: id");

		$creatorId = $project->getCreatorUser()->getId();

		$user = $em->getRepository('MongoBundle:User')->find($creatorId);
		if (!($user instanceof User))
			return $this->setBadRequest("2.4.4", "Dashboard", "getProjectCreator", "Bad Parameter: creatorId");

		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectCreator", "Complete Success", array("creator_id" => $creatorId, "creator_first_name" => $firstName, "creator_last_name" => $lastName));
	}

	/**
	* @api {get} /mongo/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
	* @apiName getProjectBasicInformations
	* @apiGroup Dashboard
	* @apiDescription Get a project basic informations
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectBasicInformationsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.5.3", "Dashboard", "getProjectBasicInformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($id);
		if(!($project instanceof Project))
			return $this->setBadRequest("2.5.4", "Dashboard", "getProjectBasicInformations", "Bad Parameter: id");

		$name = $project->getName();
		$description = $project->getDescription();
		$logo = $project->getLogo();
		$phone = $project->getPhone();
		$company = $project->getCompany();
		$contactMail = $project->getContactEmail();
		$facebook = $project->getFacebook();
		$twitter = $project->getTwitter();
		$creation = $project->getCreatedAt();

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectBasicInformations", "Complete Success", array("name" => $name, "description" => $description, "logo" => $logo, "phone" => $phone, "company" => $company , "contact_mail" => $contactMail,
			"facebook" => $facebook, "twitter" => $twitter, "creation_date" => $creation));
	}

	/**
	* @api {get} /mongo/dashboard/getprojecttasks/:token/:id Get a project tasks
	* @apiName getProjectTasks
	* @apiGroup Dashboard
	* @apiDescription Get all tasks from a project
	* @apiVersion 0.2.0
	*
	*
	*/
	public function getProjectTasksAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.6.3", "Dashboard", "getProjectTasks"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$tasks = $em->getRepository('MongoBundle:Task')->findByprojects($id);

		$arr = array();

  	if (count($tasks) == 0)
  		return $this->setNoDataSuccess("1.2.3", "Dashboard", "getProjectTasks");

		foreach ($tasks as $task) {
			$creatorId = $task->getCreatorId();
			$title = $task->getTitle();
			$description = $task->getDescription();
			$dueDate = $task->getDueDate();
			$startedAt = $task->getStartedAt();
			$finishedAt = $task->getFinishedAt();
			$createdAt = $task->getCreatedAt();
			$deletedAt = $task->getDeletedAt();

			$arr[] = array("creator_id" => $creatorId, "title" => $title, "description" => $description, "due_date" => $dueDate,
				"started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt, "deleted_at" => $deletedAt);
		}

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectTasks", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /mongo/dashboard/getuserbasicinformations/:token Get user basic informations
	* @apiName getUserBasicInformations
	* @apiGroup Dashboard
	*	@apiDescription Get the connected user basic informations
	* @apiVersion 0.2.0
	*
	*/
	public function getUserBasicInformationsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.7.3", "Dashboard", "getUserBasicInformations"));

		$em = $this->get('doctrine_mongodb')->getManager();

		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday()->format('Y-m-d');
		if ($birthday != null)
			$birthday = $birthday->format('Y-m-d');
		$avatar = $user->getAvatar();
		$mail = $user->getEmail();
		$phone = $user->getPhone();
		$country = $user->getCountry();
		$linkedin = $user->getLinkedin();
		$viadeo = $user->getViadeo();
		$twitter = $user->getTwitter();

		return $this->setSuccess("1.2.1", "Dashboard", "getUserBasicInformations", "Complete Success", array("first_name" => $firstName, "last_name" => $lastName, "birthday" => $birthday, "avatar" => $avatar, "email" => $mail,
			"phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

	/**
	* @api {get} /mongo/dashboard/getprojectpersons/:token/:id Get all persons on a project
	* @apiName getProjectPersons
	* @apiGroup Dashboard
	* @apiDescription Get all the persons on a project
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectPersonsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.8.3", "Dashboard", "getProjectPersons"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:User');

		$qb = $repository->createQueryBuilder()->field('projects.id')->equals($id);
		$users = $qb->getQuery()->execute();
		if (count($users) <= 0)
			return $this->setNoDataSuccess("1.2.3", "Dashboard", "getProjectPersons");

		$arr = array();

		foreach ($users as $us) {
			$userId = $us->getId();
			$firstName = $us->getFirstName();
			$lastName = $us->getLastName();

			$arr[] = array("user_id" => $userId, "first_name" => $firstName, "last_name" => $lastName);
		}

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectPersons", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /mongo/dashboard/getmeetingbasicinformations/:token/:id Get a meetinginformations
	* @apiName getMeetingBasicInformations
	* @apiGroup Dashboard
	* @apiDescription Get a meeting basic informations
	* @apiVersion 0.2.0
	*
	*/
	public function getMeetingBasicInformationsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.9.3", "Dashboard", "getMeetingBasicInformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$event = $em->getRepository('MongoBundle:Event')->find($id);
		if (!($event instanceof Event))
			return $this->setBadRequest("2.9.4", "Dashboard", "getMeetingBasicInformations", "Bad Parameter: id");

		$creator = $event->getCreatorUser();
		$project = $event->getProjects();
		$type = $event->getEventtypes();
		$users = $event->getUsers();

		$title = $event->getTitle();
		$description = $event->getDescription();
		$beginDate = $event->getBeginDate();
		$endDate = $event->getEndDate();
		$createdAt = $event->getCreatedAt();

		$creatorId = $creator->getId();
		$creatorFirstName = $creator->getFirstname();
		$creatorLastName = $creator->getLastname();

		$projectName = $project->getName();

		$typeName = $type->getName();
		$users_array = array();

		foreach ($users as $us) {
			$userId = $us->getId();
			$userFirstName = $us->getFirstname();
			$userLastName = $us->getLastname();
			$users_array[] = array("id" => $userId, "first_name" => $userFirstName, "last_name" => $userLastName);
		}

		return $this->setSuccess("1.2.1", "Dashboard", "getMeetingBasicInformations", "Complete Success", array("creator_id" => $creatorId, "creator_first_name" => $creatorFirstName, "creator_last_name" => $creatorLastName, "project_name" => $projectName,
			"event_type" => $typeName, "title" => $title, "description" => $description, "users_assigned" => $users_array,
			"begin_date" => $beginDate, "end_date" => $endDate, "created_at" => $createdAt));
	}

	/**
	* @api {get} /mongo/dashboard/getprojectlist/:token Get user's projects
	* @apiName getProjectList
	* @apiGroup Dashboard
	* @apiDescription Get a list of projects the user connected is on
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectListAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.9.3", "Dashboard", "getProjectList"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Project');

		$qb = $repository->createQueryBuilder()->field('users.id')->equals($user->getId());
		$projects = $qb->getQuery()->execute();

		$arr = array();

		if (count($projects) == 0)
			return $this->setNoDataSuccess("1.2.3", "Dashboard", "getProjectList");

		foreach ($projects as $project) {
			$projectId = $project->getId();
			$name = $project->getName();

			$arr[] = array("project_id" => $projectId, "name" => $name);
		}

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectList", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /mongo/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
	* @apiName getProjectTasksStatus
	* @apiGroup Dashboard
	* @apiDescription Get the project tasks status
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectTasksStatusAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.11.3", "Dashboard", "getProjectTasksStatus"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$tasks = $em->getRepository('MongoBundle:Task')->findByprojects($id);

		$arr = array();

	  if (count($tasks) == 0)
			return $this->setNoDataSuccess("1.2.3", "Dashboard", "getProjectTasksStatus");

		foreach ($tasks as $task) {
			$taskId = $task->getId();
			$tags = $task->getTags();
			$tagNames = array();

			foreach ($tags as $tag) {
				$tagName = $tag->getName();
				$tagNames[] = $tagName;
			}

			$arr[] = array("task_id" => $taskId, "status" => $tagNames);
		}
		return $this->setSuccess("1.2.1", "Dashboard", "getProjectTasksStatus", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /mongo/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
	* @apiName getNumberTimelineMessages
	* @apiGroup Dashboard
	* @apiDescription Get the number of messages for a timeline
	* @apiVersion 0.2.0
	*
	*/
	public function getNumberTimelineMessagesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.12.3", "Dashboard", "getNumberTimelineMessages"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$timelineMessages = $em->getRepository('MongoBundle:TimelineMessage')->findBytimelineId($id);

		return $this->setSuccess("1.2.1", "Dashboard", "getNumberTimelineMessages", "Complete Success", array("message_number" => count($timelineMessages)));
	}

	/**
	* @api {get} /mongo/dashboard/getnumberbugs/:token/:id Get bugs number for a project
	* @apiName getNumberBugs
	* @apiGroup Dashboard
	* @apiDescription Get the number of bugs for a project
	* @apiVersion 0.2.0
	*
	*/
	public function getNumberBugsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.13.3", "Dashboard", "getNumberBugs"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$bugs = $em->getRepository('MongoBundle:Bug')->findByprojectId($id);

		return $this->setSuccess("1.2.1", "Dashboard", "getNumberBugs", "Complete Success", array("bug_number" => count($bugs)));
	}
}
