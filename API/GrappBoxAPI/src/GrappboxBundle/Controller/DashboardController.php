<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use GrappboxBundle\Controller\RolesAndTokenVerificationController;
use GrappboxBundle\Entity\Project;
use GrappboxBundle\Entity\User;
use GrappboxBundle\Entity\Task;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 * @IgnoreAnnotation("apiDescription")
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
	* @api {get} /V0.2/dashboard/getteamoccupation/:token Get team occupation
	* @apiName getTeamOccupation
	* @apiGroup Dashboard
	* @apiDescription Getting a team occupation for all the projects the user connected is the creator
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {Object[]} array Array of user occupation
	* @apiSuccess {String} array.name Name of the project
	* @apiSuccess {Object[]} array.users User in the team informations
	* @apiSuccess {Number} array.users.id Id of the user
	* @apiSuccess {String} array.users.firstname First name of the user
	* @apiSuccess {String} array.users.lastname Last name of the user
	* @apiSuccess {String} array.occupation Occupation of the user
	* @apiSuccess {Number} array.number_of_tasks_begun Number of tasks begun
	* @apiSuccess {Number} array.number_of_ongoing_tasks Number of ongoing tasks
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getteamoccupation - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{
	*					"name": "Grappbox",
	*					"users": {
	*						"id": 1,
	*						"firstname": "John",
	*						"lastname": "Doe"
	*					},
	*					"occupation": "free",
	*					"number_of_tasks_begun": 0,
	*					"number_of_ongoing_tasks": 0
	*				},
	*				{
	*					"name": "Grappbox",
	*					"users": {
	*						"id": 3,
	*						"firstname": "James",
	*						"lastname": "Bond"
	*					},
	*					"occupation": "busy",
	*					"number_of_tasks_begun": 2,
	*					"number_of_ongoing_tasks": 5
	*				}
	*			]
	*		}
	* 	}
	*
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.2.3",
	*			"return_message": "Dashboard - getteamoccupation - No Data Success"
	*		},
	*		"data":
	*		{
	*			"array": []
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.1.3",
	*	    "return_message": "Dashboard - getteamoccupation - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getTeamOccupationAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.1.3", "Dashboard", "getteamoccupation"));

		return $this->getDoctrine()->getManager()->getRepository('GrappboxBundle:Project')->findTeamOccupationV2($user->getId());
	}

	/**
	* @api {get} /V0.2/dashboard/getnextmeetings/:token Get next meetings
	* @apiName getNextMeetings
	* @apiGroup Dashboard
	* @apiDescription Get all next meetings of the connected user
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {Object[]} array Array of events
	* @apiSuccess {Object[]} array.projects Informations of the project of the event
	* @apiSuccess {String} array.projects.name Name of the project
	* @apiSuccess {String} array.projects.logo Logo of the project
	* @apiSuccess {String} array.type Type of the event
	* @apiSuccess {String} array.title Title of the event
	* @apiSuccess {String} array.description Description of the event
	* @apiSuccess {Date} array.begin_date Begin date of the event
	* @apiSuccess {Date} array.end_date End date of the event
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getteamoccupation - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{
	*					"projects": {
	*						"name": "Grappbox",
	*						"logo": "data"
	*					},
	*					"type": "Customer",
	*					"title": "Project presentation",
	*					"description": "Project presentation to the customer",
	*					"begin_date":
	*					{
	*						"date":"2015-10-15 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
	*					"end_date":
	*					{
	*						"date":"2015-10-15 16:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					}
	*				},
	*				{
	*					"projects": {
	*						"name": "",
	*						"logo": ""
	*					},
	*					"type": "Personnal",
	*					"title": "Doctor",
	*					"description": "Meeting with the doctor for annual full checkup",
	*					"begin_date":
	*					{
	*						"date":"2015-10-17 11:30:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
	*					"end_date":
	*					{
	*						"date":"2015-10-17 12:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					}
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.2.3",
	*			"return_message": "Dashboard - getnextmeetings - No Data Success"
	*		},
	*		"data":
	*		{
	*			"array": []
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.2.3",
	*	    "return_message": "Dashboard - getnextmeetings - Bad ID"
	*	  }
	*	}
	*
  */
	public function getNextMeetingsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.2.3", "Dashboard", "getnextmeetings"));

		return $this->getDoctrine()->getManager()->getRepository('GrappboxBundle:Event')->findNextMeetingsV2($user->getId(), "2", "Dashboard", "getnextmeetings");
	}

	/**
	* @api {get} /V0.2/dashboard/getprojectsglobalprogress/:token Get projects global progress
	* @apiName getProjectsGlobalProgress
	* @apiGroup Dashboard
	* @apiDescription Get the global progress of the projects of a user
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {Number} project_id Id of the project
	* @apiSuccess {String} project_name Name of the project
	* @apiSuccess {String} project_description Description of the project
	* @apiSuccess {String} project_phone Phone of the project
	* @apiSuccess {String} project_company Company of the project
	* @apiSuccess {String} project_logo Logo of the project
	* @apiSuccess {String} contact_mail Contact mail of the project
	* @apiSuccess {String} facebook Facebook of the project
	* @apiSuccess {String} twitter Twitter of the project
	* @apiSuccess {Number} number_finished_tasks Number of finished tasks
	* @apiSuccess {Number} number_ongoing_tasks Number of ongoing tasks
	* @apiSuccess {Number} number_tasks Total number of tasks
	* @apiSuccess {Number} number_bugs Number of bugs
	* @apiSuccess {Number} number_messages Number of messages
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getProjectsGlobalProgress - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*			{
	*				"project_id": 1,
	*				"project_name": "Grappbox",
	*				"project_description": "Grappbox est un projet de gestion de projet",
	*				"project_phone": "+339 56 23 02 14",
	*				"project_company": "Ubisoft",
	*				"project_logo": "data logo...",
	*				"contact_mail": "contact@grappbox.com",
	*				"facebook": "http://facebook.com/Grappbox",
	*				"twitter": "http://twitter.com/Grappbox",
	*				"number_finished_tasks": 58,
	*				"number_ongoing_tasks": 10,
	*				"number_tasks": 600,
	*				"number_bugs": 10,
	*				"number_messages": 150
	*			},
	*			...
	*			]
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.2.3",
	*			"return_message": "Dashboard - getProjectsGlobalProgress - No Data Success"
	*		},
	*		"data":
	*		{
	*			"array": []
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.3.3",
	*	    "return_message": "Dashboard - getProjectsGlobalProgress - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getProjectsGlobalProgressAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.3.3", "Dashboard", "getProjectsGlobalProgress"));

		return ($this->getDoctrine()->getManager()->getRepository('GrappboxBundle:Project')->findProjectGlobalProgressV2($user->getId()));
	}

	/**
	* @api {get} /V0.2/dashboard/getprojectcreator/:token/:id Get project creator
	* @apiName getProjectCreator
	* @apiGroup Dashboard
	* @apiDescription Get the creator of the project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Project id
	*
	* @apiSuccess {Number} creator_id Id of the project creator
	* @apiSuccess {String} creator_first_name First name of the project creator
	* @apiSuccess {String} creator_last_name Last name of the project creator
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getProjectCreator - Complete Success"
	*		},
	*		"data":
	*			{
	*			"creator_id": 5,
	*			"creator_first_name": "John",
	*			"creator_last_name": "Doe"
	*			}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.4.3",
	*	    "return_message": "Dashboard - getProjectCreator - Bad ID"
	*	  }
	*	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "2.4.4",
	*			"return_message": "Dashboard - getProjectCreator - Bad Parameter: id"
  *		}
	* 	}
	* @apiErrorExample Bad Parameter: creatorId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "2.4.4",
	*			"return_message": "Dashboard - getProjectCreator - Bad Parameter: creatorId"
  *		}
	* 	}
	*
	*/
	public function getProjectCreatorAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.4.3", "Dashboard", "getProjectCreator"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($id);
		if (!($project instanceof Project))
			return $this->setBadRequest("2.4.4", "Dashboard", "getProjectCreator", "Bad Parameter: id");

		$creatorId = $project->getCreatorUser()->getId();

		$user = $em->getRepository('GrappboxBundle:User')->find($creatorId);
		if (!($user instanceof User))
			return $this->setBadRequest("2.4.4", "Dashboard", "getProjectCreator", "Bad Parameter: creatorId");

		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectCreator", "Complete Success", array("creator_id" => $creatorId, "creator_first_name" => $firstName, "creator_last_name" => $lastName));
	}

	/**
	* @api {get} /V0.2/dashboard/getprojectbasicinformations/:token/:id Get project basic informations
	* @apiName getProjectBasicInformations
	* @apiGroup Dashboard
	* @apiDescription Get a project basic informations
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {String} name Name of the project
	* @apiSuccess {String} description Description of the project
	* @apiSuccess {String} logo Logo of the project
	* @apiSuccess {String} phone Phone of the project
	* @apiSuccess {String} company Company of the project
	* @apiSuccess {String} contact_mail Contact mail of the project
	* @apiSuccess {String} facebook Facebook of the project
	* @apiSuccess {String} twitter Twitter of the project
	* @apiSuccess {Date} creation_date Creation date of the project
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getProjectBasicInformations - Complete Success"
	*		},
	*		"data":
	*			{
	*			"name": "Grappbox",
	*			"description": "Grappbox est un projet de gestion de projet",
	*			"logo": "logo data",
	*			"phone": "+339 76 13 45 78",
	*			"company": "Ubisoft",
	*			"contact_mail": "contact@grappbox.com",
	*			"facebook": "http://facebook.com/Grappbox",
	*			"twitter": "http://twitter.com/Grappbox",
	*			"creation_date": { "date":"2015-10-15 11:00:00", "timezone_type":3, "timezone":"Europe\/Paris" }
	*			}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.5.3",
	*	    "return_message": "Dashboard - getProjectBasicInformations - Bad ID"
	*	  }
	*	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "2.5.4",
	*			"return_message": "Dashboard - getProjectBasicInformations - Bad Parameter: id"
  *		}
	* 	}
	*
	*/
	public function getProjectBasicInformationsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.5.3", "Dashboard", "getProjectBasicInformations"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($id);
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
	* @api {get} /V0.2/dashboard/getprojecttasks/:token/:id Get project tasks
	* @apiName getProjectTasks
	* @apiGroup Dashboard
	* @apiDescription Get all tasks from a project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {Number} creator_id Creator id of the task
	* @apiSuccess {String} title Title of the task
	* @apiSuccess {String} description Description of the task
	* @apiSuccess {Date} due_date Due date of the task
	* @apiSuccess {Date} started_at Date of the begining of the task
	* @apiSuccess {Date} finished_at Date of finishing the task
	* @apiSuccess {Date} created_at Date of creation of the task
	* @apiSuccess {Date} deleted_at Deletion date of the task
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getProjectTasks - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{
	*				"creator_id": 6,
	*				"title": "Site vitrine",
	*				"description": "Faire le site vitrine de Grappbox",
	*				"due_date":{ "date":"2015-10-15 11:00:00", "timezone_type":3,"timezone":"Europe\/Paris"},
	*				"started_at": { "date":"2015-10-15 16:00:00", "timezone_type":3, "timezone":"Europe\/Paris"	},
	*				"finished_at": { "date":"2015-10-15 16:00:00", "timezone_type":3, "timezone":"Europe\/Paris" },
	*				"created_at":	{ "date":"2015-10-15 16:00:00", "timezone_type":3, "timezone":"Europe\/Paris" },
	*				"deleted_at": { "date":"2015-10-15 16:00:00","timezone_type":3, "timezone":"Europe\/Paris" }
	*				},
	*				...
	*			]
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.2.3",
	*			"return_message": "Dashboard - getProjectTasks - No Data Success"
	*		},
	*		"data":
	*		{
	*			"array": []
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.6.3",
	*	    "return_message": "Dashboard - getProjectTasks - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getProjectTasksAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.6.3", "Dashboard", "getProjectTasks"));

		$em = $this->getDoctrine()->getManager();
		$tasks = $em->getRepository('GrappboxBundle:Task')->findByprojects($id);

		$arr = array();
		$i = 1;

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

			$arr["Task ".$i] = array("creator_id" => $creatorId, "title" => $title, "description" => $description, "due_date" => $dueDate,
				"started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt, "deleted_at" => $deletedAt);
			$i++;
		}

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectTasks", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /V0.2/dashboard/getuserbasicinformations/:token Get user basic informations
	* @apiName getUserBasicInformations
	* @apiGroup Dashboard
	*	@apiDescription Get the connected user basic informations
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {String} first_name First name of the user
	* @apiSuccess {String} last_name Last name of the user
	* @apiSuccess {Date} birthday birthday date of the user
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
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getProjectTasks - Complete Success"
	*		},
	*		"data":
	*			{
	*			"first_name": "John",
	*			"last_name": "Doe",
	*			"birthday": "2015-10-15",
	*			"avatar": "avatar data ...",
	*			"email": "john.doe@gmail.com",
	*			"phone": +33631245478,
	*			"country": "France",
	*			"linkedin": "http://linkedin.com/John.Doe",
	*			"viadeo": "http://viadeo.com/John.Doe",
	*			"twitter": "http://twitter.com/John.Doe"
	*			}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.7.3",
	*	    "return_message": "Dashboard - getUserBasicInformations - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getUserBasicInformationsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.7.3", "Dashboard", "getUserBasicInformations"));

		$em = $this->getDoctrine()->getManager();

		$firstName = $user->getFirstname();
		$lastName = $user->getLastname();
		$birthday = $user->getBirthday();
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
	* @api {get} /V0.2/dashboard/getprojectpersons/:token/:id Get all persons on project
	* @apiName getProjectPersons
	* @apiGroup Dashboard
	* @apiDescription Get all the persons on a project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {Number} user_id User id
	* @apiSuccess {String} first_name User first name
	* @apiSuccess {String} last_name User last name
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getProjectPersons - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*			{
	*				"user_id": 6,
	*				"first_name": "John",
	*				"last_name": "Doe"
	*			},
	*			...
	*			]
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.2.3",
	*			"return_message": "Dashboard - getProjectPersons - No Data Success"
	*		},
	*		"data":
	*		{
	*			"array": []
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.8.3",
	*	    "return_message": "Dashboard - getProjectPersons - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getProjectPersonsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.8.3", "Dashboard", "getProjectPersons"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:User');

		$qb = $repository->createQueryBuilder('u')->join('u.projects', 'p')->where('p.id = :id')->setParameter('id', $id)->getQuery();
		$users = $qb->getResult();
		if (count($users) <= 0)
			return $this->setNoDataSuccess("1.2.3", "Dashboard", "getProjectPersons");

		$arr = array();
		$i = 0;

		foreach ($users as $us) {
			$userId = $us->getId();
			$firstName = $us->getFirstName();
			$lastName = $us->getLastName();

			$arr[] = array("user_id" => $userId, "first_name" => $firstName, "last_name" => $lastName);
			$i++;
		}

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectPersons", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /V0.2/dashboard/getmeetingbasicinformations/:token/:id Get meeting informations
	* @apiName getMeetingBasicInformations
	* @apiGroup Dashboard
	* @apiDescription Get a meeting basic informations
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} id Id of the meeting
	*
	* @apiSuccess {Number} creator_id Id of the creator
	* @apiSuccess {String} creator_first_name Creator first name
	* @apiSuccess {String} creator_last_name Creator last name
	* @apiSuccess {String} project_name Name of the project
	* @apiSuccess {String} event_type Type of the event
	* @apiSuccess {String} title Event title
	* @apiSuccess {String} description Event description
	* @apiSuccess {Object[]} users_assigned Array of users assigned to the event
	* @apiSuccess {Number} users_assigned.id Id of the user assigned
	* @apiSuccess {String} users_assigned.first_name First name of the user assigned
	* @apiSuccess {String} users_assigned.last_name Last name of the user assigned
	* @apiSuccess {Date} begin_date Date of finishing the task
	* @apiSuccess {Date} end_date Deletion date of the task
	* @apiSuccess {Date} created_at Date of creation of the task
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getMeetingBasicInformations - Complete Success"
	*		},
	*		"data": {
	*			"creator_id": 1,
	*			"creator_first_name": "John",
	*			"creator_last_name": "Doe",
	*			"project_name": "Grappbox",
	*			"event_type": "Client",
	*			"title": "déjeuné client",
	*			"description": "déjeuné avec un client potentiel",
	*			"users_assigned": [
	*				{ "id": 1, "first_name": "John", "last_name": "Doe" },
	*				{ "id": 2, "first_name": "Jane", "last_name": "Doe"}
	*			],
	*			"begin_date": { "date":"2015-10-15 11:00:00", "timezone_type":3, "timezone":"Europe\/Paris" },
	*			"end_date": { "date":"2015-10-15 16:00:00", "timezone_type":3, "timezone":"Europe\/Paris" },
	*			"created_at": { "date":"2015-10-15 16:00:00", "timezone_type":3, "timezone":"Europe\/Paris" }
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.9.3",
	*	    "return_message": "Dashboard - getMeetingBasicInformations - Bad ID"
	*	  }
	*	}
	* @apiErrorExample Bad Parameter: id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "2.9.4",
	*			"return_message": "Dashboard - getMeetingBasicInformations - Bad Parameter: id"
  *		}
	* 	}
	*
	*/
	public function getMeetingBasicInformationsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.9.3", "Dashboard", "getMeetingBasicInformations"));

		$em = $this->getDoctrine()->getManager();
		$event = $em->getRepository('GrappboxBundle:Event')->find($id);
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
		$i = 1;

		foreach ($users as $us) {
			$userId = $us->getId();
			$userFirstName = $us->getFirstname();
			$userLastName = $us->getLastname();
			$users_array[] = array("id" => $userId, "first_name" => $userFirstName, "last_name" => $userLastName);
			$i++;
		}

		return $this->setSuccess("1.2.1", "Dashboard", "getMeetingBasicInformations", "Complete Success", array("creator_id" => $creatorId, "creator_first_name" => $creatorFirstName, "creator_last_name" => $creatorLastName, "project_name" => $projectName,
			"event_type" => $typeName, "title" => $title, "description" => $description, "users_assigned" => $users_array,
			"begin_date" => $beginDate, "end_date" => $endDate, "created_at" => $createdAt));
	}

	/**
	* @api {get} /V0.2/dashboard/getprojectlist/:token Get user's projects
	* @apiName getProjectList
	* @apiGroup Dashboard
	* @apiDescription Get a list of projects the user connected is on
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {Number} project_id Project id
	* @apiSuccess {String} name Project named
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getProjectList - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*			{
	*				"project_id": 3,
	*				"name": "Grappbox"
	*			},
	*			...
	*			]
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.2.3",
	*			"return_message": "Dashboard - getProjectList - No Data Success"
	*		},
	*		"data":
	*		{
	*			"array": []
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.9.3",
	*	    "return_message": "Dashboard - getProjectList - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getProjectListAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.9.3", "Dashboard", "getProjectList"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:Project');

		$qb = $repository->createQueryBuilder('p')->join('p.users', 'u')->where('u.id = :id')->setParameter('id', $user->getId())->getQuery();
		$projects = $qb->getResult();

		$arr = array();
		$i = 0;

    if (count($projects) == 0)
			return $this->setNoDataSuccess("1.2.3", "Dashboard", "getProjectList");

		foreach ($projects as $project) {
			$projectId = $project->getId();
			$name = $project->getName();

			$arr["Project ".$i] = array("project_id" => $projectId, "name" => $name);
			$i++;
		}

		return $this->setSuccess("1.2.1", "Dashboard", "getProjectList", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /V0.2/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
	* @apiName getProjectTasksStatus
	* @apiGroup Dashboard
	* @apiDescription Get the project tasks status
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {Object[]} Task Array of tasks
	* @apiSuccess {Number} Task.task_id Task id
	* @apiSuccess {String} Task.Status Array of status of the task
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getProjectTasksStatus - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*			{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
	*			},
	*			...
	*			]
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.2.3",
	*			"return_message": "Dashboard - getProjectTasksStatus - No Data Success"
	*		},
	*		"data":
	*		{
	*			"array": []
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.11.3",
	*	    "return_message": "Dashboard - getProjectTasksStatus - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getProjectTasksStatusAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.11.3", "Dashboard", "getProjectTasksStatus"));

		$em = $this->getDoctrine()->getManager();
		$tasks = $em->getRepository('GrappboxBundle:Task')->findByprojects($id);

		$arr = array();
		$i = 1;

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
			$i++;
		}
		return $this->setSuccess("1.2.1", "Dashboard", "getProjectTasksStatus", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /V0.2/dashboard/getnumbertimelinemessages/:token/:id Get number of messages for a timeline
	* @apiName getNumberTimelineMessages
	* @apiGroup Dashboard
	* @apiDescription Get the number of messages for a timeline
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the timeline
	*
	* @apiSuccess {Number} message_number Number of messages of a timeline
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getNumberTimelineMessages - Complete Success"
	*		},
	*		"data": {
	*			"message_number": 10
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.12.3",
	*	    "return_message": "Dashboard - getNumberTimelineMessages - Bad ID"
	*	  }
	*	}
	*
	*
	*/
	public function getNumberTimelineMessagesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.12.3", "Dashboard", "getNumberTimelineMessages"));

		$em = $this->getDoctrine()->getManager();
		$timelineMessages = $em->getRepository('GrappboxBundle:TimelineMessage')->findBytimelineId($id);

		return $this->setSuccess("1.2.1", "Dashboard", "getNumberTimelineMessages", "Complete Success", array("message_number" => count($timelineMessages)));
	}

	/**
	* @api {get} /V0.2/dashboard/getnumberbugs/:token/:id Get bugs number for a project
	* @apiName getNumberBugs
	* @apiGroup Dashboard
	* @apiDescription Get the number of bugs for a project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {Number} bug_number Number of bugs
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"info": {
	*			"return_code": "1.2.1",
	*			"return_message": "Dashboard - getNumberBugs - Complete Success"
	*		},
	*		"data": {
	*			"bug_number": 10
	*		}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token:
	* 	HTTP/1.1 401 Unauthorized
	*	{
	*	  "info": {
	*	    "return_code": "2.13.3",
	*	    "return_message": "Dashboard - getNumberBugs - Bad ID"
	*	  }
	*	}
	*
	*
	*/
	public function getNumberBugsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.13.3", "Dashboard", "getNumberBugs"));

		$em = $this->getDoctrine()->getManager();
		$bugs = $em->getRepository('GrappboxBundle:Bug')->findByprojectId($id);

		return $this->setSuccess("1.2.1", "Dashboard", "getNumberBugs", "Complete Success", array("bug_number" => count($bugs)));
	}
}
