<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use APIBundle\Controller\RolesAndTokenVerificationController;
use APIBundle\Entity\Project;
use APIBundle\Entity\User;

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
class DashboardController extends RolesAndTokenVerificationController
{
	/**
  	* @api {get} /V1/API/Dashboard/getTeamOccupation/:id Get a team occupation
  	* @apiName getTeamOccupation
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.1
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {String} Person.project_name Name of the project
  	* @apiSuccess {String} Person.first_name First name of the person
  	* @apiSuccess {String} Person.last_name Last name of the person
  	* @apiSuccess {String} Person.occupation Occupation of the person
  	* @apiSuccess {Number} Person.number_of_tasks_begun Number of tasks begun
  	* @apiSuccess {Number} Person.number_of_ongoing_tasks Number of ongoing tasks
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
  	*		{
	*			"project_name": "Grappbox",
	*			"first_name": "John",
	*			"last_name": "Doe",
	*			"occupation": "Busy",
	*			"number_of_tasks_begun": 2,
	*			"number_of_ongoing_tasks": 3
  	*		},
  	*		"Person 2":
  	*		{
	*			"project_name": "Grappbox",
	*			"first_name": "Thierry",
	*			"last_name": "Doe",
	*			"occupation": "Free",
	*			"number_of_tasks_begun": 0,
	*			"number_of_ongoing_tasks": 0
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"No projects for the id X"
	* 	}
  	*
  	*/
	public function getTeamOccupationAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Project')->findTeamOccupation($id));
	}

	/**
  	* @api {get} /V1/API/Dashboard/getNextMeetings/:id Get a person next meetings
  	* @apiName getNextMeetings
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.1
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Object[]} Event Array of events
  	* @apiSuccess {String} Event.project_name Name of the project
  	* @apiSuccess {String} Event.project_logo Logo of the project
  	* @apiSuccess {String} Event.event_type Type of the event
  	* @apiSuccess {String} Event.event_title Title of the event
  	* @apiSuccess {String} Event.event_description Description of the event
  	* @apiSuccess {Date} Event.event_begin_date Begin date of the event
  	* @apiSuccess {Date} Event.event_end_date End date of the event
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Event 1":
  	*		{
	*			"project_name": "Grappbox",
	*			"project_logo": "data logo...",
	*			"event_type": "Client",
	*			"event_title": "Présentation du projet",
	*			"event_description": "Présentation du projet grappbox au client",
	*			"event_begin_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"event_end_date":
	*			{
	*				"date":"2015-10-15 16:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
  	*		},
  	*		"Event 2":
  	*		{
	*			"project_name": "",
	*			"project_logo": "",
	*			"event_type": "Personnel",
	*			"event_title": "RDV dentiste",
	*			"event_description": "Rendez-vous avec le dentiste pour changer la couronne",
	*			"event_begin_date":
	*			{
	*				"date":"2015-10-17 11:30:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"event_end_date":
	*			{
	*				"date":"2015-10-17 12:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"No events for the id  X"
	* 	}
  	*
  	*/
	public function getNextMeetingsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Event')->findNextMeetings($id));
	}

	/**
  	* @api {get} /V1/API/Dashboard/getProjectsGlobalProgress/:id Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.1
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Id of the project
  	* @apiSuccess {String} Project.project_name Name of the project
  	* @apiSuccess {String} Project.project_description Description of the project
  	* @apiSuccess {String} Project.project_logo Logo of the project
  	* @apiSuccess {String} Project.contact_mail Contact mail of the project
  	* @apiSuccess {String} Project.facebook Facebook of the project
  	* @apiSuccess {String} Project.twitter Twitter of the project
  	* @apiSuccess {Number} Project.number_finished_tasks Number of finished tasks
  	* @apiSuccess {Number} Project.number_ongoing_tasks Number of ongoing tasks
  	* @apiSuccess {Number} Project.number_tasks Total number of tasks
  	* @apiSuccess {Number} Project.number_bugs Number of bugs
  	* @apiSuccess {Number} Project.number_messages Number of messages
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Project 1":
  	*		{
  	*			"project_id": 1,
	*			"project_name": "Grappbox",
	*			"project_description": "Grappbox est un projet de gestion de projet",
	*			"project_logo": "data logo...",
	*			"contact_mail": "contact@grappbox.com",
	*			"facebook": "http://facebook.com/Grappbox",
	*			"twitter": "http://twitter.com/Grappbox",
	*			"number_finished_tasks": 58,
	*			"number_ongoing_tasks": 10,
	*			"number_tasks": 600,
	*			"number_bugs": 10,
	*			"number_messages": 150
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"No projects for the id  X"
	* 	}
  	*
  	*/
	public function getProjectsGlobalProgressAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('APIBundle:Project')->findProjectGlobalProgress($id));
	}

	/**
  	* @api {get} /V1/API/Dashboard/getProjectCreator/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Number} project_id Id of the project creator
  	* @apiSuccess {String} first_name First name of the project creator
  	* @apiSuccess {String} last_name Last name of the project creator
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"creator_id": 5,
	*		"first_name": "John",
	*		"last_name": "Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getProjectCreatorAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
			throw new NotFoundHttpException("The creator user with id ".$id." doesn't exist");
		}

		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();

		return new JsonResponse(array("creator_id" => $creatorId, "first_name" => $firstName, "last_name" => $lastName));
	}

	/**
  	* @api {get} /V1/API/Dashboard/getProjectBasicInformations/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {String} name Name of the project
  	* @apiSuccess {String} description Description of the project
  	* @apiSuccess {String} logo Logo of the project
  	* @apiSuccess {String} contact_mail Contact mail of the project
  	* @apiSuccess {String} facebook Facebook of the project
  	* @apiSuccess {String} twitter Twitter of the project
  	* @apiSuccess {Date} creation_date Creation date of the project
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet de gestion de projet",
	*		"logo": "logo data",
	*		"contact_mail": "contact@grappbox.com",
	*		"facebook": "http://facebook.com/Grappbox",
	*		"twitter": "http://twitter.com/Grappbox",
	*		"creation_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getProjectBasicInformationsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
  	* @api {get} /V1/API/Dashboard/getProjectTasks/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Object[]} Task Array of tasks
  	* @apiSuccess {Number} Task.creator_id Creator id of the task
  	* @apiSuccess {String} Task.title Title of the task
  	* @apiSuccess {String} Task.description Description of the task
  	* @apiSuccess {Date} Task.due_date Due date of the task
  	* @apiSuccess {Date} Task.started_at Date of the begining of the task
  	* @apiSuccess {Date} Task.finished_at Date of finishing the task
  	* @apiSuccess {Date} Task.created_at Date of creation of the task
  	* @apiSuccess {Date} Task.deleted_at Deletion date of the task
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Task 1":
  	*		{
	*			"creator_id": 6,
	*			"title": "Site vitrine",
	*			"description": "Faire le site vitrine de Grappbox",
	*			"due_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"started_at":
	*			{
	*				"date":"2015-10-15 16:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"finished_at":
	*			{
	*				"date":"2015-10-15 16:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"created_at":
	*			{
	*				"date":"2015-10-15 16:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"deleted_at":
	*			{
	*				"date":"2015-10-15 16:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/
	public function getProjectTasksAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$tasks = $em->getRepository('APIBundle:Task')->findByprojectId($id);

		if ($tasks === null)
		{
			throw new NotFoundHttpException("The're no tasks for the project ".$id);
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
  	* @api {get} /V1/API/Dashboard/getUserBasicInformations/:id Get a user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {Date} birthday birthday date
  	* @apiSuccess {String} avatar avatar of the user
  	* @apiSuccess {String} email Email of the user
  	* @apiSuccess {Number} phone Phone number of the user
  	* @apiSuccess {String} country Country of the user
  	* @apiSuccess {String} linkedin Linkedin of the user
  	* @apiSuccess {String} viadeo Viadeo of the user
  	* @apiSuccess {String} twitter Twitter of the user
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*			"first_name": "John",
	*			"last_name": "Doe",
	*			"birthday":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"avatar": "avatar data ...",
	*			"email": "john.doe@gmail.com",
	*			"phone": +33631245478,
	*			"country": "France",
	*			"linkedin": "http://linkedin.com/John.Doe",
	*			"viadeo": "http://viadeo.com/John.Doe",
	*			"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getUserBasicInformationsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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

		return new JsonResponse(array("first_name" => $firstName, "last_name" => $lastName, "birthday" => $birthday, "avatar" => $avatar, "email" => $mail,
			"phone" => $phone, "country" => $country, "linkedin" => $linkedin, "viadeo" => $viadeo, "twitter" => $twitter));
	}

	/**
  	* @api {get} /V1/API/Dashboard/getProjectPersons/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Task 1":
  	*		{
	*			"user_id": 6,
	*			"first_name": "John",
	*			"last_name": "Doe"
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getProjectPersonsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
  	* @api {get} /V1/API/Dashboard/getPersonMeetings/:id Get a person meetings
  	* @apiName getPersonMeetings
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Object[]} Event Array of events
  	* @apiSuccess {Number} Event.event_id Event id
  	* @apiSuccess {String} Event.title Event title
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Event 1":
  	*		{
	*			"event_id": 3,
	*			"title": "Meeting with the client"
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getPersonMeetingsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
  	* @api {get} /V1/API/Dashboard/getMeetingBasicInformations/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {String} creator_first_name Creator first name
  	* @apiSuccess {String} creator_last_name Creator last name
  	* @apiSuccess {String} project_name Name of the project
  	* @apiSuccess {String} event_type Type of the event
  	* @apiSuccess {String} title Event title
  	* @apiSuccess {String} description Event description
  	* @apiSuccess {Date} begin_date Date of finishing the task
  	* @apiSuccess {Date} end_date Deletion date of the task
  	* @apiSuccess {Date} created_at Date of creation of the task
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*			"creator_first_name": "John,
	*			"creator_last_naùe": "Doe",
	*			"project_name": "Grappbox",
	*			"event_type": "Client",
	*			"title": "déjeuné client",
	*			"descriptio,": "déjeuné avec un client potentiel",
	*			"begin_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"end_date":
	*			{
	*				"date":"2015-10-15 16:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"created_at":
	*			{
	*				"date":"2015-10-15 16:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No event type found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The Event type with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getMeetingBasicInformationsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
			throw new NotFoundHttpException("The project with id ".$id." doesn't exist");
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
  	* @api {get} /V1/API/Dashboard/getProjectList/:id Get a list of projects the user is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Project id
  	* @apiSuccess {String} Project.name Project name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Project 1":
  	*		{
	*			"project_id": 3,
	*			"name": "Grappbox"
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project user role found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project user role with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getProjectListAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
  	* @api {get} /V1/API/Dashboard/getTasksStatus/:id Get the user tasks status
  	* @apiName getTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Object[]} Status Array of status
  	* @apiSuccess {Number} Status.task_id Task id
  	* @apiSuccess {String} Status.status Status of the task
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Status 1":
  	*		{
	*			"task_id": 3,
	*			"status": "Doing"
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project with id X"
	* 	}
	*
	* @apiErrorExample No task tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task tag id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/
	public function getTasksStatusAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$tasks = $em->getRepository('APIBundle:Task')->findByprojectId($id);

		if ($tasks === null)
		{
			throw new NotFoundHttpException("The're no tasks for the project with id ".$id);
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
  	* @api {get} /V1/API/Dashboard/getNumberTimelineMessages/:id Get the number of timeline messages
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Number} message_number Number of messages in a timeline 
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"bmessage_number": 10
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No messages found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no messages for the timeline with id X"
	* 	}
  	*
  	*/
	public function getNumberTimelineMessagesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
  	* @api {get} /V1/API/Dashboard/getNumberBugs/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {Number} bug_number Number of bugs 
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"bug_number": 10
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No bugs found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no bugs for the project with id X"
	* 	}
  	*
  	*/
	public function getNumberBugsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
  	* @api {get} /V1/API/Dashboard/getPersonOccupation/:idPerson.:idProject Get a user occupation
  	* @apiName getPersonOccupation
  	* @apiGroup Dashboard
  	* @apiVersion 0.0.0
  	*
  	* @apiParam {String} _token Token of the person connected
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*			"_token": "aeqf231ced651qcd"
  	* 	}
  	*
  	* @apiSuccess {String} occupation Occupation of the user 
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"occupation": "busy"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No tasks found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project with id X"
	* 	}
	*
	* @apiErrorExample No task user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no task user for the task with id X"
	* 	}
  	*
  	*/
	public function getPersonOccupationAction(Request $request, $idPerson, $idProject)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

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
				throw new NotFoundHttpException("The're no task user for the task with id ".$taskId);
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
