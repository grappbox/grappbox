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
  	* @api {get} /V0.2/dashboard/getteamoccupation/:token Get a team occupation
  	* @apiName getTeamOccupation
  	* @apiGroup Dashboard
  	* @apiDescription This method is for getting a team occupation for all the projects the user connected is the creator
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
	*			"return_message": "Dashboard - getteamoccupation - Success but no data"
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

		return $this->getDoctrine()->getManager()->getRepository('MongoBundle:Project')->findTeamOccupationV2($user->getId());
	}

	/**
  	* @api {get} /V0.2/dashboard/getnextmeetings/:token Get the person connected next meetings
  	* @apiName getNextMeetings
  	* @apiGroup Dashboard
  	* @apiDescription Get all the user connected next meetings
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
	*			"return_message": "Dashboard - getnextmeetings - Success but no data"
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
  	*/
	public function getNextMeetingsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.2.3", "Dashboard", "getnextmeetings"));

		return $this->getDoctrine()->getManager()->getRepository('MongoBundle:Event')->findNextMeetingsV2($user->getId());
	}

	/**
  	* @api {get} /V0.6/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
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

	/**
  	* @api {get} /V0.6/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Id of the project
  	* @apiSuccess {String} Project.project_name Name of the project
  	* @apiSuccess {String} Project.project_description Description of the project
  	* @apiSuccess {String} Project.project_phone Phone of the project
  	* @apiSuccess {String} Project.project_company Company of the project
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
	*			"project_phone": "+339 56 23 02 14",
	*			"project_company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.7/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Id of the project
  	* @apiSuccess {String} Project.project_name Name of the project
  	* @apiSuccess {String} Project.project_description Description of the project
  	* @apiSuccess {String} Project.project_phone Phone of the project
  	* @apiSuccess {String} Project.project_company Company of the project
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
	*			"project_phone": "+339 56 23 02 14",
	*			"project_company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.7/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Id of the project
  	* @apiSuccess {String} Project.project_name Name of the project
  	* @apiSuccess {String} Project.project_description Description of the project
  	* @apiSuccess {String} Project.project_phone Phone of the project
  	* @apiSuccess {String} Project.project_company Company of the project
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
	*			"project_phone": "+339 56 23 02 14",
	*			"project_company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.8/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Id of the project
  	* @apiSuccess {String} Project.project_name Name of the project
  	* @apiSuccess {String} Project.project_description Description of the project
  	* @apiSuccess {String} Project.project_phone Phone of the project
  	* @apiSuccess {String} Project.project_company Company of the project
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
	*			"project_phone": "+339 56 23 02 14",
	*			"project_company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.9/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Id of the project
  	* @apiSuccess {String} Project.project_name Name of the project
  	* @apiSuccess {String} Project.project_description Description of the project
  	* @apiSuccess {String} Project.project_phone Phone of the project
  	* @apiSuccess {String} Project.project_company Company of the project
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
	*			"project_phone": "+339 56 23 02 14",
	*			"project_company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.10/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Id of the project
  	* @apiSuccess {String} Project.project_name Name of the project
  	* @apiSuccess {String} Project.project_description Description of the project
  	* @apiSuccess {String} Project.project_phone Phone of the project
  	* @apiSuccess {String} Project.project_company Company of the project
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
	*			"project_phone": "+339 56 23 02 14",
	*			"project_company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.11/dashboard/getprojectsglobalprogress/:token Get the global progress of the projects of a user
  	* @apiName getProjectsGlobalProgress
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {Object[]} Project Array of projects
  	* @apiSuccess {Number} Project.project_id Id of the project
  	* @apiSuccess {String} Project.project_name Name of the project
  	* @apiSuccess {String} Project.project_description Description of the project
  	* @apiSuccess {String} Project.project_phone Phone of the project
  	* @apiSuccess {String} Project.project_company Company of the project
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
	*			"project_phone": "+339 56 23 02 14",
	*			"project_company": "Ubisoft",
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
	public function getProjectsGlobalProgressAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		return new JsonResponse($this->getDoctrine()->getManager()->getRepository('MongoBundle:Project')->findProjectGlobalProgress($user->getId()));
	}

	/**
  	* @api {get} /V0.6/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
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
  	*		"creator_id": 5,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe"
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
	* @apiErrorExample No creator user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.6/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
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
  	*		"creator_id": 5,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe"
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
	* @apiErrorExample No creator user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
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
  	*		"creator_id": 5,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe"
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
	* @apiErrorExample No creator user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
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
  	*		"creator_id": 5,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe"
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
	* @apiErrorExample No creator user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.8/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
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
  	*		"creator_id": 5,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe"
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
	* @apiErrorExample No creator user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.9/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
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
  	*		"creator_id": 5,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe"
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
	* @apiErrorExample No creator user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.10/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
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
  	*		"creator_id": 5,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe"
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
	* @apiErrorExample No creator user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.11/dashboard/getprojectcreator/:token/:id Get a project creator
  	* @apiName getProjectCreator
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
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
  	*		"creator_id": 5,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe"
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
	* @apiErrorExample No creator user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The creator user with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getProjectCreatorAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
  	* @api {get} /V0.6/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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

	/**
  	* @api {get} /V0.6/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
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
  	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet de gestion de projet",
	*		"logo": "logo data",
	*		"phone": "+339 76 13 45 78",
	*		"company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.7/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
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
  	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet de gestion de projet",
	*		"logo": "logo data",
	*		"phone": "+339 76 13 45 78",
	*		"company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.7/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
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
  	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet de gestion de projet",
	*		"logo": "logo data",
	*		"phone": "+339 76 13 45 78",
	*		"company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.8/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
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
  	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet de gestion de projet",
	*		"logo": "logo data",
	*		"phone": "+339 76 13 45 78",
	*		"company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.9/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
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
  	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet de gestion de projet",
	*		"logo": "logo data",
	*		"phone": "+339 76 13 45 78",
	*		"company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.10/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
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
  	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet de gestion de projet",
	*		"logo": "logo data",
	*		"phone": "+339 76 13 45 78",
	*		"company": "Ubisoft",
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

  	/**
  	* @api {get} /V0.11/dashboard/getprojectbasicinformations/:token/:id Get a project basic informations
  	* @apiName getProjectBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
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
  	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet de gestion de projet",
	*		"logo": "logo data",
	*		"phone": "+339 76 13 45 78",
	*		"company": "Ubisoft",
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
	public function getProjectBasicInformationsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
  	* @api {get} /V0.6/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/

	/**
  	* @api {get} /V0.6/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.8/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.9/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.10/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.11/dashboard/getprojecttasks/:token/:id Get a project tasks
  	* @apiName getProjectTasks
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The're no tasks for the project X"
	* 	}
  	*
  	*/
	public function getProjectTasksAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
  	* @api {get} /V0.6/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {DateTime} birthday birthday date of the user
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.6/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {DateTime} birthday birthday date of the user
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {DateTime} birthday birthday date of the user
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {DateTime} birthday birthday date of the user
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.8/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {DateTime} birthday birthday date of the user
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.9/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {DateTime} birthday birthday date of the user
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.10/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {DateTime} birthday birthday date of the user
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.11/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	*
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	* @apiSuccess {DateTime} birthday birthday date of the user
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.11/dashboard/getuserbasicinformations/:token Get the connected user basic informations
  	* @apiName getUserBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.1
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
	*		"first_name": "John",
	*		"last_name": "Doe",
	*		"birthday": "2015-10-15",
	*		"avatar": "avatar data ...",
	*		"email": "john.doe@gmail.com",
	*		"phone": +33631245478,
	*		"country": "France",
	*		"linkedin": "http://linkedin.com/John.Doe",
	*		"viadeo": "http://viadeo.com/John.Doe",
	*		"twitter": "http://twitter.com/John.Doe"
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
  	*
  	*/
	public function getUserBasicInformationsAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();

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
  	* @api {get} /V0.6/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
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
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no users for the project with id X"
	* 	}
  	*
  	*/

	/**
  	* @api {get} /V0.6/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
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
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no users for the project with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
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
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no users for the project with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
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
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no users for the project with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.8/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
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
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no users for the project with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.9/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
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
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no users for the project with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.10/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
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
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no users for the project with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.11/dashboard/getprojectpersons/:token/:id Get all the persons on a project
  	* @apiName getProjectPersons
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
  	*
  	* @apiSuccess {Object[]} Person Array of persons
  	* @apiSuccess {Number} Person.user_id User id
  	* @apiSuccess {String} Person.first_name User first name
  	* @apiSuccess {String} Person.last_name User last name
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"Person 1":
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
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no users for the project with id X"
	* 	}
  	*
  	*/
	public function getProjectPersonsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
  	* @api {get} /V0.6/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"_token": "aeqf231ced651qcd"
  	* 	}
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
  	*		"creator_id": 1,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe",
	*		"project_name": "Grappbox",
	*		"event_type": "Client",
	*		"title": "déjeuné client",
	*		"description": "déjeuné avec un client potentiel",
	*		"users_assigned":
	*		[{
	*			"id": 1,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		},
	*		{
	*			"id": 2,
	*			"first_name": "Jane",
	*			"last_name": "Doe"
	*		}],
	*		"begin_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"end_date":
	*		{
	*			"date":"2015-10-15 16:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"created_at":
	*		{
	*			"date":"2015-10-15 16:00:00",
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
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/

	/**
  	* @api {get} /V0.6/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"_token": "aeqf231ced651qcd"
  	* 	}
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
  	*		"creator_id": 1,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe",
	*		"project_name": "Grappbox",
	*		"event_type": "Client",
	*		"title": "déjeuné client",
	*		"description": "déjeuné avec un client potentiel",
	*		"users_assigned":
	*		[{
	*			"id": 1,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		},
	*		{
	*			"id": 2,
	*			"first_name": "Jane",
	*			"last_name": "Doe"
	*		}],
	*		"begin_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"end_date":
	*		{
	*			"date":"2015-10-15 16:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"created_at":
	*		{
	*			"date":"2015-10-15 16:00:00",
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
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"_token": "aeqf231ced651qcd"
  	* 	}
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
  	*		"creator_id": 1,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe",
	*		"project_name": "Grappbox",
	*		"event_type": "Client",
	*		"title": "déjeuné client",
	*		"description": "déjeuné avec un client potentiel",
	*		"users_assigned":
	*		[{
	*			"id": 1,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		},
	*		{
	*			"id": 2,
	*			"first_name": "Jane",
	*			"last_name": "Doe"
	*		}],
	*		"begin_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"end_date":
	*		{
	*			"date":"2015-10-15 16:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"created_at":
	*		{
	*			"date":"2015-10-15 16:00:00",
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
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"_token": "aeqf231ced651qcd"
  	* 	}
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
  	*		"creator_id": 1,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe",
	*		"project_name": "Grappbox",
	*		"event_type": "Client",
	*		"title": "déjeuné client",
	*		"description": "déjeuné avec un client potentiel",
	*		"users_assigned":
	*		[{
	*			"id": 1,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		},
	*		{
	*			"id": 2,
	*			"first_name": "Jane",
	*			"last_name": "Doe"
	*		}],
	*		"begin_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"end_date":
	*		{
	*			"date":"2015-10-15 16:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"created_at":
	*		{
	*			"date":"2015-10-15 16:00:00",
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
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.8/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"_token": "aeqf231ced651qcd"
  	* 	}
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
  	*		"creator_id": 1,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe",
	*		"project_name": "Grappbox",
	*		"event_type": "Client",
	*		"title": "déjeuné client",
	*		"description": "déjeuné avec un client potentiel",
	*		"users_assigned":
	*		[{
	*			"id": 1,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		},
	*		{
	*			"id": 2,
	*			"first_name": "Jane",
	*			"last_name": "Doe"
	*		}],
	*		"begin_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"end_date":
	*		{
	*			"date":"2015-10-15 16:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"created_at":
	*		{
	*			"date":"2015-10-15 16:00:00",
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
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.9/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"_token": "aeqf231ced651qcd"
  	* 	}
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
  	*		"creator_id": 1,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe",
	*		"project_name": "Grappbox",
	*		"event_type": "Client",
	*		"title": "déjeuné client",
	*		"description": "déjeuné avec un client potentiel",
	*		"users_assigned":
	*		[{
	*			"id": 1,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		},
	*		{
	*			"id": 2,
	*			"first_name": "Jane",
	*			"last_name": "Doe"
	*		}],
	*		"begin_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"end_date":
	*		{
	*			"date":"2015-10-15 16:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"created_at":
	*		{
	*			"date":"2015-10-15 16:00:00",
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
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.10/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"_token": "aeqf231ced651qcd"
  	* 	}
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
  	*		"creator_id": 1,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe",
	*		"project_name": "Grappbox",
	*		"event_type": "Client",
	*		"title": "déjeuné client",
	*		"description": "déjeuné avec un client potentiel",
	*		"users_assigned":
	*		[{
	*			"id": 1,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		},
	*		{
	*			"id": 2,
	*			"first_name": "Jane",
	*			"last_name": "Doe"
	*		}],
	*		"begin_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"end_date":
	*		{
	*			"date":"2015-10-15 16:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"created_at":
	*		{
	*			"date":"2015-10-15 16:00:00",
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
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.11/dashboard/getmeetingbasicinformations/:token/:id Get a meeting basic informations
  	* @apiName getMeetingBasicInformations
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {String} id Id of the meeting
  	*
  	* @apiParamExample {json} Request-Example:
  	* 	{
  	*		"_token": "aeqf231ced651qcd"
  	* 	}
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
  	*		"creator_id": 1,
	*		"creator_first_name": "John",
	*		"creator_last_name": "Doe",
	*		"project_name": "Grappbox",
	*		"event_type": "Client",
	*		"title": "déjeuné client",
	*		"description": "déjeuné avec un client potentiel",
	*		"users_assigned":
	*		[{
	*			"id": 1,
	*			"first_name": "John",
	*			"last_name": "Doe"
	*		},
	*		{
	*			"id": 2,
	*			"first_name": "Jane",
	*			"last_name": "Doe"
	*		}],
	*		"begin_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"end_date":
	*		{
	*			"date":"2015-10-15 16:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"created_at":
	*		{
	*			"date":"2015-10-15 16:00:00",
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
	* @apiErrorExample No event found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The event with id X doesn't exist"
	* 	}
  	*
  	*/
	public function getMeetingBasicInformationsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
  	* @api {get} /V0.6/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
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
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no projects for the user with id X"
	* 	}
  	*
  	*/

	/**
  	* @api {get} /V0.6/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
  	*
  	* @apiParam {String} token Token of the person connected
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
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no projects for the user with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
  	*
  	* @apiParam {String} token Token of the person connected
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
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no projects for the user with id X"
	* 	}
  	*
  	*/

	/**
  	* @api {get} /V0.7/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
  	*
  	* @apiParam {String} token Token of the person connected
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
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no projects for the user with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.8/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
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
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no projects for the user with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.9/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
  	*
  	* @apiParam {String} token Token of the person connected
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
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no projects for the user with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.10/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
  	*
  	* @apiParam {String} token Token of the person connected
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
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no projects for the user with id X"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.11/dashboard/getprojectlist/:token Get a list of projects the user connected is on
  	* @apiName getProjectList
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
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
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There're no projects for the user with id X"
	* 	}
  	*
  	*/
	public function getProjectListAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
  	* @api {get} /V0.6/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
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
  	*		"Task 1":
  	*		{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
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
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/

	/**
  	* @api {get} /V0.6/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
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
  	*		"Task 1":
  	*		{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
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
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
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
  	*		"Task 1":
  	*		{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
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
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.7/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
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
  	*		"Task 1":
  	*		{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
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
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.8/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
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
  	*		"Task 1":
  	*		{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
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
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.9/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
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
  	*		"Task 1":
  	*		{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
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
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.10/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
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
  	*		"Task 1":
  	*		{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
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
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/

  	/**
  	* @api {get} /V0.11/dashboard/getprojecttasksstatus/:token/:id Get the project tasks status
  	* @apiName getProjectTasksStatus
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
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
  	*		"Task 1":
  	*		{
	*			"task_id": 3,
	*			"status": ["Doing","Urgent"]
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
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag id X doesn't exist"
	* 	}
  	*
  	*/
	public function getProjectTasksStatusAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
  	* @api {get} /V0.6/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	* @apiSuccess {Number} message_number Number of messages of a timeline
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"message_number": 10
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

  	/**
  	* @api {get} /V0.6/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	* @apiSuccess {Number} message_number Number of messages of a timeline
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"message_number": 10
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

  	/**
  	* @api {get} /V0.7/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	* @apiSuccess {Number} message_number Number of messages of a timeline
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"message_number": 10
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

  	/**
  	* @api {get} /V0.7/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	* @apiSuccess {Number} message_number Number of messages of a timeline
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"message_number": 10
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

  	/**
  	* @api {get} /V0.8/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	* @apiSuccess {Number} message_number Number of messages of a timeline
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"message_number": 10
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

  	/**
  	* @api {get} /V0.9/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	* @apiSuccess {Number} message_number Number of messages of a timeline
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"message_number": 10
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

  	/**
  	* @api {get} /V0.10/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	* @apiSuccess {Number} message_number Number of messages of a timeline
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"message_number": 10
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

  	/**
  	* @api {get} /V0.11/dashboard/getnumbertimelinemessages/:token/:id Get the number of messages for a timeline
  	* @apiName getNumberTimelineMessages
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the timeline
  	*
  	* @apiSuccess {Number} message_number Number of messages of a timeline
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"message_number": 10
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
	public function getNumberTimelineMessagesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
  	* @api {get} /V0.6/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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

	/**
  	* @api {get} /V0.6/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.6.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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

  	/**
  	* @api {get} /V0.7/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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

  	/**
  	* @api {get} /V0.7/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.7.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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

  	/**
  	* @api {get} /V0.8/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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

  	/**
  	* @api {get} /V0.9/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.9.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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

  	/**
  	* @api {get} /V0.10/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.10.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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

  	/**
  	* @api {get} /V0.11/dashboard/getnumberbugs/:token/:id Get the number of bugs for a project
  	* @apiName getNumberBugs
  	* @apiGroup Dashboard
  	* @apiVersion 0.11.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} id Id of the project
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
	public function getNumberBugsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
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
