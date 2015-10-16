<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use APIBundle\Entity\Project;
use APIBundle\Entity\User;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class DashboardController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get project creator",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array containing: creator_id, first_name, last_name"
     *		}
     *  }
	 * )
	 *
	 */
	public function getProjectCreatorAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($id);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$id." doesn't exist");			
		}

		$creatorId = $project->getCreatorId();

		$user = $em->getRepository('APIBundle:User')->find($creatorId);

		if ($user === null)
		{
			throw new NotFoundHttpException("The project with id ".$id." doesn't exist");			
		}

		$userId = $user->getId();
		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();

		return new JsonResponse(array("creator_id" => $creatorId, "first_name" => $firstName, "last_name" => $lastName));
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get project basic informations",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array containing: name, description, logo, contact_mail, facebook, twitter, creation_date"
     *		}
     *  }
	 * )
	 *
	 */
	public function getBasicInformationsAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($id);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$id." doesn't exist");			
		}

		$name = $project->getName();
		$description = $project->getDescription();
		$logo = $project->getLogo();
		$contactMail = $project->getContactEmail();
		$facebook = $project->getFacebook();
		$twitter = $project->getTwitter();
		$creation = $project->getCreatedAt();

		return new JsonResponse(array("name" => $name, "description" => $description, "logo" => $logo, "contact_mail" => $contactMail,
			"facebook" => $facebook, "twitter" => $twitter, "creation_date" => $creation));
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get project tasks",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array of 'Task n' array containing: creator_id, title, description, due_date, started_at, finished_at, created_at, deleted_at"
     *		}
     *  }
	 * )
	 *
	 */
	public function getProjectTasksAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$tasks = $em->getRepository('APIBundle:Task')->findByprojectId($id);

		if ($tasks === null)
		{
			throw new NotFoundHttpException("The task with project id ".$id." doesn't exist");			
		}

		$arr = array();
		$i = 1;

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
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get user basic informations",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the user you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array containing: first_name, last_name, birthday, avatar, e-mail, phone, country, linkedin, viadeo"
     *		}
     *  }
	 * )
	 *
	 */
	public function getUserBasicInformationsAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->find($id);

		if ($user === null)
		{
			throw new NotFoundHttpException("The user with id ".$id." doesn't exist");			
		}

		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday();
		$avatar = $user->getAvatar();
		$mail = $user->getEmail();
		$phone = $user->getPhone();
		$country = $user->getCountry();
		$linkedin = $user->getLinkedin();
		$viadeo = $user->getViadeo();
		$twitter = $user->getTwitter();

		return new JsonResponse(array("first_name" => $firstName, "last_name" => $lastName, "birthday" => $birthday, "avatar" => $avatar, "e-mail" => $mail,
			"phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get all the persons on a project",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array of 'Person n' array containing: user_id, first_name, last_name"
     *		}
     *  }
	 * )
	 *
	 */
	public function getProjectPersonsAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$projectUsers = $em->getRepository('APIBundle:ProjectUserRole')->findByprojectId($id);

		if ($projectUsers === null)
		{
			throw new NotFoundHttpException("The project with id ".$id." doesn't exist");			
		}

		$arr = array();
		$idArray = array();
		$i = 1;
		$userRepository = $em->getRepository('APIBundle:User');

		foreach ($projectUsers as $projectUser) {
			$userId = $projectUser->getUserId();
			$idNotFound = true;
			foreach ($idArray as $value) {
				if ($value == $userId)
				{
					$idNotFound = false;
				}
			}

			if ($idNotFound == true)
			{
				$idArray[] = $userId;
				$user = $userRepository->find($userId);

				if ($user === null)
				{
					throw new NotFoundHttpException("The user with id ".$id." doesn't exist");			
				}

				$firstName = $user->getFirstname();
				$lastName = $user->getLastname();

				$arr["Person ".$i] = array("user_id" => $userId, "first_name" => $firstName, "last_name", $lastName);
				$i++;
			}
		}
		return new JsonResponse($arr);
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a person meeting",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array of 'Event n' array containing: event_id, title"
     *		}
     *  }
	 * )
	 *
	 */
	public function getPersonMeetingsAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$userEvents = $em->getRepository('APIBundle:EventUser')->findByuserId($id);

		if ($userEvents === null)
		{
			throw new NotFoundHttpException("The user with id ".$id." doesn't exist");			
		}

		$arr = array();
		$i = 1;

		$eventRepository = $em->getRepository('APIBundle:Event');

		foreach ($userEvents as $userEvent){
			$eventId = $userEvent->getEventId();
			$event = $eventRepository->find($eventId);

			if ($event === null)
			{
				throw new NotFoundHttpException("The event with id ".$id." doesn't exist");
			}

			$title = $event->getTitle();

			$arr["Event ".$i] = array("event_id" => $eventId, "title" => $title);
			$i++;
		}
		return new JsonResponse($arr);
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the basic informations of a meeting",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the meeting you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array containing: creator_first_name, creator_last_name, project_name, event_type, title, description, begin_date, end_date, created_at"
     *		}
     *  }
	 * )
	 *
	 */
	public function getMeetingBasicInformationsAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$event = $em->getRepository('APIBundle:Event')->find($id);

		if ($event === null)
		{
			throw new NotFoundHttpException("The event with id ".$id." doesn't exist");			
		}

		$creatorId = $event->getCreatorId();
		$projectId = $event->getProjectId();
		$typeId = $event->getTypeId();

		$title = $event->getTitle();
		$description = $event->getDescription();
		$beginDate = $event->getBeginDate();
		$endDate = $event->getEndDate();
		$createdAt = $event->getCreatedAt();

		$user = $em->getRepository('APIBundle:User')->find($creatorId);
		if ($user === null)
		{
			throw new NotFoundHttpException("The user with id ".$id." doesn't exist");			
		}
		$creatorFirstName = $user->getFirstname();
		$creatorLastName = $user->getLastname();

		$project = $em->getRepository('APIBundle:Project')->find($projectId);
		if ($project === null)
		{
			throw new NotFoundHttpException("The user with id ".$id." doesn't exist");			
		}
		$projectName = $project->getName();

		$eventType = $em->getRepository('APIBundle:EventType')->find($typeId);
		if ($eventType === null)
		{
			throw new NotFoundHttpException("The event type with id ".$id." doesn't exist");			
		}
		$typeName = $eventType->getName();

		return new JsonResponse(array("creator_first_name" => $creatorFirstName, "creator_last_name" => $creatorLastName, "project_name" => $projectName,
			"event_type" => $typeName, "title" => $title, "description" => $description, "begin_date" => $beginDate, "end_date" => $endDate, "created_at" => $createdAt));
	}


	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a list of project",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array of 'Project n' array containing: project_id, name"
     *		}
     *  }
	 * )
	 *
	 */
	public function getProjectListAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$projectUserRoles = $em->getRepository('APIBundle:ProjectUserRole')->findByuserId($id);

		if ($projectUserRoles === null)
		{
			throw new NotFoundHttpException("The project user role with id ".$id." doesn't exist");			
		}

		$arr = array();
		$idArray = array();
		$i = 1;

		$projectRepository = $em->getRepository('APIBundle:Project');

		foreach ($projectUserRoles as $userRole){
			$projectId = $userRole->getProjectId();
			$idNotFound = true;
			foreach ($idArray as $value) {
				if ($value == $projectId)
				{
					$idNotFound = false;
				}
			}

			if ($idNotFound == true)
			{
				$idArray[] = $projectId;
				$project = $projectRepository->find($projectId);

				if ($project === null)
				{
					throw new NotFoundHttpException("The project with id ".$id." doesn't exist");
				}

				$name = $project->getName();

				$arr["Project ".$i] = array("project_id" => $projectId, "name" => $name);
				$i++;
			}
		}
		return new JsonResponse($arr);
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the status of the tasks",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array of 'Status n' array containing: task_id, status"
     *		}
     *  }
	 * )
	 *
	 */
	public function getTasksStatusAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$tasks = $em->getRepository('APIBundle:Task')->findByprojectId($id);

		if ($tasks === null)
		{
			throw new NotFoundHttpException("The task with project id ".$id." doesn't exist");			
		}

		$arr = array();
		$i = 1;

		$TaskTagRepository = $em->getRepository('APIBundle:TaskTag');
		$TagRepository = $em->getRepository('APIBundle:Tag');

		foreach ($tasks as $task) {
			$taskId = $task->getId();
			$taskTag = $TaskTagRepository->findOneBytaskId($taskId);

			if ($taskTag === null)
			{
				throw new NotFoundHttpException("The task tag id ".$id." doesn't exist");			
			}

			$tagId = $taskTag->getTagId();
			$tag = $TagRepository->find($tagId);

			if ($tag === null)
			{
				throw new NotFoundHttpException("The tag id ".$id." doesn't exist");			
			}

			$tagName = $tag->getName();

			$arr["Status ".$i] = array("task_id" => $taskId, "status" => $tagName);
			$i++;
		}
		return new JsonResponse($arr);		
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the number of messages on a timeline",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the timeline you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array containing: message_number"
     *		}
     *  }
	 * )
	 *
	 */
	public function getNumberTimelineMessagesAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$timelineMessages = $em->getRepository('APIBundle:TimelineMessage')->findBytimelineId($id);

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
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the number of bugs",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array containing: bug_number"
     *		}
     *  }
	 * )
	 *
	 */
	public function getNumberBugsAction(Request $request, $id)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$bugs = $em->getRepository('APIBundle:Bug')->findByprojectId($id);

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

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a person occupation",
	 * views = { "dashboard" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="idPerson",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person you want",
     *          "requirement"="\d+"
     *      },
     *      {
     *          "name"="idProject",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want",
     *          "requirement"="\d+"
     *      },
     *		{
     *          "name"="return",
     *          "dataType"="array",
     *          "description"="array containing: occupation"
     *		}
     *  }
	 * )
	 *
	 */
	public function getPersonOccupationAction(Request $request, $idPerson, $idProject)
	{
		$method = $request->getMethod();
		if ($method != "GET")
			return header("HTTP/1.0 404 Not Found", True, 404);

		$em = $this->getDoctrine()->getManager();
		$tasks = $em->getRepository('APIBundle:Task')->findByprojectId($idProject);

		if ($tasks === null)
		{
			throw new NotFoundHttpException("The're no tasks for the project with id ".$idProject);			
		}

		$taskUserRepository = $em->getRepository('APIBundle:TaskUser');
		$defaultDate = date_create("0000-00-00 00:00:00");
		$busy = false;

		foreach ($tasks as $task){
			$taskId = $task->getId();
			$finishedAt = $task->getFinishedAt();
			$taskUsers = $taskUserRepository->findBytaskId($taskId);

			if ($taskUsers === null)
			{
				throw new NotFoundHttpException("The're no user for the task with id ".$taskId);			
			}

			foreach ($taskUsers as $taskUser){
				$userId = $taskUser->getUserId();

				if (($userId == $idPerson) && ($finishedAt == $defaultDate))
				{
					$busy = true;
				}
			}
		}
		if ($busy == true)
		{
			return new JsonResponse(array("occupation" => "busy"));	
		}
		return new JsonResponse(array("occupation" => "free"));
	}
}