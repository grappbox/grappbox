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
  	* @apiParam {String} token Token of the person connected
  	*
  	*
  	*/
	public function getTeamOccupationAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.1.3", "Dashboard", "getteamoccupation"));

		return $this->getDoctrine()->getManager()->getRepository('MongoBundle:Project')->findTeamOccupationV2($user->getId());
	}

	/**
  	* @api {get} /mongo/dashboard/getnextmeetings/:token Get the person connected next meetings
  	* @apiName getNextMeetings
  	* @apiGroup Dashboard
  	* @apiDescription Get all the user connected next meetings
  	* @apiVersion 0.2.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
		*	}
  	*/
	public function getNextMeetingsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.2.3", "Dashboard", "getnextmeetings"));

		return $this->getDoctrine()->getManager()->getRepository('MongoBundle:Event')->findNextMeetingsV2($user->getId());
	}

  	/**
  	* @api {get} /mongo/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	*
  	*/
	public function getProjectsGlobalProgressAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('MongoBundle:Project')->findProjectGlobalProgress($user->getId()));
	}

  	/**
  	* @api {get} /mongo/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Project id
  	*
  	*
  	*/
	public function getProjectCreatorAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$project = $em->getRepository('MongoBundle:Project')->find($id);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$id." doesn't exist");
		}

		$creatorId = $project->getCreatorUser();

		$user = $em->getRepository('MongoBundle:User')->find($creatorId);

		if ($user === null)
		{
			throw new NotFoundHttpException("The creator user with id ".$id." doesn't exist");
		}

		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();

		return new JsonResponse(array("creator_id" => $creatorId, "creator_first_name" => $firstName, "creator_last_name" => $lastName));
	}

  	/**
  	* @api {get} /mongo/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	*
  	*/
	public function getProjectBasicInformationsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$project = $em->getRepository('MongoBundle:Project')->find($id);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$id." doesn't exist");
		}

		$name = $project->getName();
		$description = $project->getDescription();
		$logo = $project->getLogo();
		$phone = $project->getPhone();
		$company = $project->getCompany();
		$contactMail = $project->getContactEmail();
		$facebook = $project->getFacebook();
		$twitter = $project->getTwitter();
		$creation = $project->getCreatedAt();

		return new JsonResponse(array("name" => $name, "description" => $description, "logo" => $logo, "phone" => $phone, "company" => $company , "contact_mail" => $contactMail,
			"facebook" => $facebook, "twitter" => $twitter, "creation_date" => $creation));
	}

  	/**
  	* @api {get} /mongo/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	*
  	*/
	public function getProjectTasksAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$tasks = $em->getRepository('MongoBundle:Task')->findByprojects($id);

		if ($tasks === null)
		{
			throw new NotFoundHttpException("The're no tasks for the project ".$id);
		}

		$arr = array();
		$i = 1;

    if (count($tasks) == 0)
    {
      return new JsonResponse((Object)$arr);
    }

		foreach ($tasks as $task) {
			$creatorId = $task->getCreatorId();
			$title = $task->getTitle();
			$description = $task->getDescription();
			$dueDate = $task->getDueDate();
			$startedAt = $task->getStartedAt();
			$finishedAt = $task->getFinishedAt();
			$createdAt = $task->getCreatedAt();
			$deletedAt = $task->getDeletedAt();

			$arr["Task ".$i] = array("creator_id" => $creatorId, "title" => $title, "description" => $description, "due_date" => $dueDate,
				"started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt, "deleted_at" => $deletedAt);
			$i++;
		}

		return new JsonResponse($arr);
	}

  	/**
  	* @api {get} /mongo/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.1
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	*
  	*/
	public function getUserBasicInformationsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');

		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday()->format('Y-m-d');
		$avatar = $user->getAvatar();
		$mail = $user->getEmail();
		$phone = $user->getPhone();
		$country = $user->getCountry();
		$linkedin = $user->getLinkedin();
		$viadeo = $user->getViadeo();
		$twitter = $user->getTwitter();

		return new JsonResponse(array("first_name" => $firstName, "last_name" => $lastName, "birthday" => $birthday, "avatar" => $avatar, "email" => $mail,
			"phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

  	/**
  	* @api {get} /mongo/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	*
  	*/
	public function getProjectPersonsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$repository = $em->getRepository('MongoBundle:User');

		$qb = $repository->createQueryBuilder('u')->join('u.projects', 'p')->where('p.id = :id')->setParameter('id', $id)->getQuery();
		$users = $qb->getResult();

		if ($users === null)
		{
			throw new NotFoundHttpException("There're no users for the project with id ".$id);
		}

		$arr = array();
		$i = 0;

    if (count($users) == 0)
    {
      return new JsonResponse((Object)$arr);
    }

		foreach ($users as $us) {
			$userId = $us->getId();
			$firstName = $us->getFirstName();
			$lastName = $us->getLastName();

			$arr["Person ".$i] = array("user_id" => $userId, "first_name" => $firstName, "last_name" => $lastName);
			$i++;
		}
		return new JsonResponse($arr);
	}

  	/**
  	* @api {get} /mongo/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"token": "aeqf231ced651qcd"
  	* 	}
  	*
  	*
  	*/
	public function getMeetingBasicInformationsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$event = $em->getRepository('MongoBundle:Event')->find($id);

		if ($event === null)
		{
			throw new NotFoundHttpException("The event with id ".$id." doesn't exist");
		}

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
		$i = 1;

		foreach ($users as $us) {
			$userId = $us->getId();
			$userFirstName = $us->getFirstname();
			$userLastName = $us->getLastname();
			$users_array[] = array("id" => $userId, "first_name" => $userFirstName, "last_name" => $userLastName);
			$i++;
		}


		return new JsonResponse(array("creator_id" => $creatorId, "creator_first_name" => $creatorFirstName, "creator_last_name" => $creatorLastName, "project_name" => $projectName,
			"event_type" => $typeName, "title" => $title, "description" => $description, "users_assigned" => $users_array,
			"begin_date" => $beginDate, "end_date" => $endDate, "created_at" => $createdAt));
	}

  	/**
  	* @api {get} /mongo/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	*
  	*/
	public function getProjectListAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$repository = $em->getRepository('MongoBundle:Project');

		$qb = $repository->createQueryBuilder('p')->join('p.users', 'u')->where('u.id = :id')->setParameter('id', $user->getId())->getQuery();
		$projects = $qb->getResult();

		if ($projects === null)
		{
			throw new NotFoundHttpException("There're no projects for the user with id ".$user->getId());
		}

		$arr = array();
		$i = 0;

    if (count($projects) == 0)
    {
      return new JsonResponse((Object)$arr);
    }

		foreach ($projects as $project) {
			$projectId = $project->getId();
			$name = $project->getName();

			$arr["Project ".$i] = array("project_id" => $projectId, "name" => $name);
			$i++;
		}
		return new JsonResponse($arr);
	}

  	/**
  	* @api {get} /mongo/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	*
  	*/
	public function getProjectTasksStatusAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$tasks = $em->getRepository('MongoBundle:Task')->findByprojects($id);

		if ($tasks === null)
		{
			throw new NotFoundHttpException("The're no tasks for the project with id ".$id);
		}

		$arr = array();
		$i = 1;

    if (count($tasks) == 0)
    {
      return new JsonResponse((Object)$arr);
    }

		foreach ($tasks as $task) {
			$taskId = $task->getId();
			$tags = $task->getTags();
			$tagNames = array();

			if ($tags === null)
			{
				throw new NotFoundHttpException("The tag id ".$id." doesn't exist");
			}

			foreach ($tags as $tag) {
				$tagName = $tag->getName();
				$tagNames[] = $tagName;
			}

			$arr["Task ".$i] = array("task_id" => $taskId, "status" => $tagNames);
			$i++;
		}
		return new JsonResponse($arr);
	}

  	/**
  	* @api {get} /mongo/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	*
  	*/
	public function getNumberTimelineMessagesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$timelineMessages = $em->getRepository('MongoBundle:TimelineMessage')->findBytimelineId($id);

		if ($timelineMessages === null)
		{
			throw new NotFoundHttpException("The're no messages for the timeline with id ".$id);
		}

		$messageCount = 0;

		foreach ($timelineMessages as $timelineMessage){
			$messageCount++;
		}
		return new JsonResponse(array("message_number" => $messageCount));
	}

  	/**
  	* @api {get} /mongo/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	*
  	*/
	public function getNumberBugsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->get('doctrine_mongodb');
		$bugs = $em->getRepository('MongoBundle:Bug')->findByprojectId($id);

		if ($bugs === null)
		{
			throw new NotFoundHttpException("The're no bugs for the project with id ".$id);
		}

		$bugCount = 0;

		foreach ($bugs as $bug){
			$bugCount++;
		}
		return new JsonResponse(array("bug_number" => $bugCount));
	}
}
