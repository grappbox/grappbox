<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use SQLBundle\Controller\RolesAndTokenVerificationController;
use SQLBundle\Entity\Project;
use SQLBundle\Entity\User;
use SQLBundle\Entity\Task;

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
	* @api {get} /V0.3/dashboard/getteamoccupation/:token/:id Get team occupation
	* @apiName getTeamOccupation
	* @apiGroup Dashboard
	* @apiDescription Getting a team occupation for a project for the user connected
	* @apiVersion 0.3.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {Object[]} array Array of user occupation
	* @apiSuccess {Object[]} array.user User in the team informations
	* @apiSuccess {Number} array.user.id Id of the user
	* @apiSuccess {String} array.user.firstname First name of the user
	* @apiSuccess {String} array.user.lastname Last name of the user
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
	*					"user": {
	*						"id": 1,
	*						"firstname": "John",
	*						"lastname": "Doe"
	*					},
	*					"occupation": "free",
	*					"number_of_tasks_begun": 0,
	*					"number_of_ongoing_tasks": 0
	*				},
	*				{
	*					"user": {
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
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "2.1.9",
	*			"return_message": "Dashboard - getteamoccupation - Insufficient Rights"
	*		}
	*	}
	*
	*/
	/**
	* @api {get} /V0.2/dashboard/getteamoccupation/:token/:id Get team occupation
	* @apiName getTeamOccupation
	* @apiGroup Dashboard
	* @apiDescription Getting a team occupation for a project for the user connected
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {Object[]} array Array of user occupation
	* @apiSuccess {Object[]} array.user User in the team informations
	* @apiSuccess {Number} array.user.id Id of the user
	* @apiSuccess {String} array.user.firstname First name of the user
	* @apiSuccess {String} array.user.lastname Last name of the user
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
	*					"user": {
	*						"id": 1,
	*						"firstname": "John",
	*						"lastname": "Doe"
	*					},
	*					"occupation": "free",
	*					"number_of_tasks_begun": 0,
	*					"number_of_ongoing_tasks": 0
	*				},
	*				{
	*					"user": {
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
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "2.1.9",
	*			"return_message": "Dashboard - getteamoccupation - Insufficient Rights"
	*		}
	*	}
	*
	*/
	public function getTeamOccupationAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.1.3", "Dashboard", "getteamoccupation"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($id);

		if ($project === null)
			return $this->setBadRequest("2.1.4", "Dashboard", "getteamoccupation", "Bad Parameter: projectId");

		return $this->getDoctrine()->getManager()->getRepository('SQLBundle:Project')->findTeamOccupation($project->getId());
	}

	/**
	* @api {get} /V0.3/dashboard/getnextmeetings/:token/:id Get next meetings
	* @apiName getNextMeetings
	* @apiGroup Dashboard
	* @apiDescription Get all next meetings, in 7 days, of the connected user
	* @apiVersion 0.3.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {Object[]} array Array of events
	* @apiSuccess {Number} array.Id Id of the event
	* @apiSuccess {String} array.type Type of the event
	* @apiSuccess {String} array.title Title of the event
	* @apiSuccess {String} array.description Description of the event
	* @apiSuccess {string} array.begin_date Begin date of the event
	* @apiSuccess {string} array.end_date End date of the event
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
	*					"id": 1,
	*					"type": "Customer",
	*					"title": "Project presentation",
	*					"description": "Project presentation to the customer",
	*					"begin_date": "2015-10-15 11:00:00",
	*					"end_date": "2015-10-15 16:00:00"
	*				},
	*				{
	*					"id": 3,
	*					"type": "Internal",
	*					"title": "Weekly meeting",
	*					"description": "Weekly meeting with the team",
	*					"begin_date": "2015-10-17 11:30:00",
	*					"end_date": "2015-10-17 12:00:00"
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
	/**
	* @api {get} /V0.2/dashboard/getnextmeetings/:token/:id Get next meetings
	* @apiName getNextMeetings
	* @apiGroup Dashboard
	* @apiDescription Get all next meetings, in 7 days, of the connected user
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {Object[]} array Array of events
	* @apiSuccess {Number} array.Id Id of the event
	* @apiSuccess {String} array.type Type of the event
	* @apiSuccess {String} array.title Title of the event
	* @apiSuccess {String} array.description Description of the event
	* @apiSuccess {string} array.begin_date Begin date of the event
	* @apiSuccess {string} array.end_date End date of the event
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
	*					"id": 1,
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
	*					"id": 3,
	*					"type": "Internal",
	*					"title": "Weekly meeting",
	*					"description": "Weekly meeting with the team",
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
	public function getNextMeetingsAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("2.2.3", "Dashboard", "getnextmeetings"));

		return $this->getDoctrine()->getManager()->getRepository('SQLBundle:Event')->findNextMeetings($user->getId(), $id, "2", "Dashboard", "getnextmeetings");
	}

	/**
	* @api {get} /V0.3/dashboard/getprojectsglobalprogress/:token Get projects global progress
	* @apiName getProjectsGlobalProgress
	* @apiGroup Dashboard
	* @apiDescription Get the global progress of the projects of a user
	* @apiVersion 0.3.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {Number} id Id of the project
	* @apiSuccess {String} name Name of the project
	* @apiSuccess {String} description Description of the project
	* @apiSuccess {String} phone Phone of the project
	* @apiSuccess {String} company Company of the project
	* @apiSuccess {String} logo Logo of the project
	* @apiSuccess {String} contact_mail Contact mail of the project
	* @apiSuccess {String} facebook Facebook of the project
	* @apiSuccess {String} twitter Twitter of the project
	* @apiSuccess {string} deleted_at Date of deletion of the project, null if not deleted
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
	*				"id": 1,
	*				"name": "Grappbox",
	*				"description": "Grappbox est un projet de gestion de projet",
	*				"phone": "+339 56 23 02 14",
	*				"company": "Ubisoft",
	*				"logo": "data logo...",
	*				"contact_mail": "contact@grappbox.com",
	*				"facebook": "http://facebook.com/Grappbox",
	*				"twitter": "http://twitter.com/Grappbox",
	*				"deleted_at": "2016-06-14 19:22:00",
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
	* @apiSuccess {Datetime} deleted_at Date of deletion of the project, null if not deleted
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
	*				"deleted_at":{
	*					"date": "2016-06-14 19:22:00"
	*					"timezone_type": 3,
	*					"timezone": "Europe\/Paris"
	*				}
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

		return ($this->getDoctrine()->getManager()->getRepository('SQLBundle:Project')->findProjectGlobalProgress($user->getId()));
	}
}
