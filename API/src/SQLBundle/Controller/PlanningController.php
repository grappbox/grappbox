<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GrappBundle\Entity\Event;
use GrappBundle\Entity\Task;
use DateTime;
use DateInterval;

/**
*  @IgnoreAnnotation("apiName")
*  @IgnoreAnnotation("apiGroup")
*  @IgnoreAnnotation("apiDescription")
*  @IgnoreAnnotation("apiVersion")
*  @IgnoreAnnotation("apiSuccess")
*  @IgnoreAnnotation("apiSuccessExample")
*  @IgnoreAnnotation("apiError")
*  @IgnoreAnnotation("apiErrorExample")
*  @IgnoreAnnotation("apiParam")
*  @IgnoreAnnotation("apiParamExample")
*/

class PlanningController extends RolesAndTokenVerificationController
{
	/**
	* @api {get} /0.3/planning/day/:token/:date Get day planning
	* @apiName getDayPlanning
	* @apiGroup Planning
	* @apiDescription Get a one day planning
	* @apiVersion 0.3.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {string} date date of event to list (into YYYY-MM-DD format)
	*
	* @apiSuccess {Object[]} events list of events
	* @apiSuccess {int} events.id Event id
	* @apiSuccess {int} events.projectId project id of the event (could be null)
	* @apiSuccess {Object} events.creator Event type object
	* @apiSuccess {int} events.creator.id creator id
	* @apiSuccess {string} events.creator.firstname creator firstname
	* @apiSuccess {string} events.creator.lastname creator lastname
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	* @apiSuccess {string} events.title event title
	* @apiSuccess {string} events.description event description
	* @apiSuccess {string} events.beginDate beginning date of the event
	* @apiSuccess {string} events.endDate ending date of the event
	* @apiSuccess {string} events.createdAt date of creation of the event
	* @apiSuccess {string} events.editedAt date of edition of the event
	* @apiSuccess {Object[]} users list of participants
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getDayPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": {
	*				"events": [
	*					{
	*					"id": 12,
	*					"projectId": 1,
	*					"creator": {"id": 1, "firstname": "John", "lastname": "Doe"},
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"beginDate": "1945-06-18 06:00:00",
	*					"endDate": "1945-06-18 08:00:00",
	*					"createdAt": "1945-06-18 08:00:00",
	*					"editedAt": "1945-06-18 08:00:00",
	*					"users": []
	*					},
	*					...
	*				]
	*			}
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.5.3",
	*			"return_message": "Calendar - getDayPlanning - No Data Success"
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
	*	    "return_code": "5.1.3",
	*	    "return_message": "Calendar - getDayPlanning - Bad ID"
	*	  }
	*	}
	*
	*/
	/**
	* @api {get} /V0.2/planning/getday/:token/:date Get day planning
	* @apiName getDayPlanning
	* @apiGroup Planning
	* @apiDescription Get a one day planning
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {string} date date of event to list (into YYYY-MM-DD format)
	*
	* @apiSuccess {Object[]} events list of events
	* @apiSuccess {int} events.id Event id
	* @apiSuccess {int} events.projectId project id of the event (could be null)
	* @apiSuccess {Object} events.creator Event type object
	* @apiSuccess {int} events.creator.id creator id
	* @apiSuccess {string} events.creator.fullname creator fullname
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	* @apiSuccess {string} events.title event title
	* @apiSuccess {string} events.description event description
	* @apiSuccess {DateTime} events.beginDate beginning date of the event
	* @apiSuccess {DateTime} events.endDate ending date of the event
	* @apiSuccess {DateTime} events.createdAt date of creation of the event
	* @apiSuccess {DateTime} events.editedAt date of edition of the event
	* @apiSuccess {DateTime} events.deletedAt date of deletion of the event
	* @apiSuccess {Object[]} tasks list of tasks
	* @apiSuccess {int} tasks.id task id
	* @apiSuccess {int} tasks.creatorId creator id
	* @apiSuccess {string} tasks.title event title
	* @apiSuccess {string} tasks.description task description
	* @apiSuccess {DateTime} tasks.startedAt date when the task was started
	* @apiSuccess {DateTime} tasks.dueDate deadline date of the task
	* @apiSuccess {DateTime} tasks.finishedAt date when the task was finished
	* @apiSuccess {int} tasks.projectId project's id
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getDayPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": {
	*				"events": [
	*					{
	*					"id": 12,
	*					"projectId": 1,
	*					"creator": {"id": 1, "fullname": "John Doe"},
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"icon": "100011001010...",
	*					"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"createdAt":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"editedAt":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"deletedAt": null
	*					},
	*					...
	*				],
	*				"tasks": [
	*					{
	*					"id": 12,
	*					"creatorId": 10,
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"startedAt":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"dueDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"finishedAt": null,
	*					"projectId": 1
	*					},
	*					...
	*				]
	*			}
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.5.3",
	*			"return_message": "Calendar - getDayPlanning - No Data Success"
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
	*	    "return_code": "5.1.3",
	*	    "return_message": "Calendar - getDayPlanning - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getDayPlanningAction(Request $request, $token, $date)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.1.3", "Calendar", "getDayPlanning"));

		$date_begin = new DateTime($date);
		$date_end = new DateTime($date);
		$date_end->add(new DateInterval('P1D'));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:Event');
		$query = $repository->createQueryBuilder('e')
    						->innerJoin('e.users', 'u')
    						->where('u.id = :user_id')
							->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
    						->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
    						->getQuery()->getResult();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = $value->objectToArray();
		}

		 if (count($events) <= 0 && count($tasks) <= 0)
		 	return $this->setNoDataSuccess("1.5.3", "Calendar", "getDayPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getDayPlanning", "Complete Success", array("array" => array("events" => $events)));
	}

	/**
	* @api {get} /0.3/planning/week/:token/:date Get week planning
	* @apiName getWeekPlanning
	* @apiGroup Planning
	* @apiDescription Get planning of a week
	* @apiVersion 0.3.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {string} date date of the first day of the week (into YYYY-MM-DD format)
	*
	* @apiSuccess {Object[]} events list of events
	* @apiSuccess {int} events.id Event id
	* @apiSuccess {int} events.projectId project id of the event (could be null)
	* @apiSuccess {Object} events.creator Event type object
	* @apiSuccess {int} events.creator.id creator id
	* @apiSuccess {string} events.creator.firstname creator firstname
	* @apiSuccess {string} events.creator.lastname creator lastname
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	* @apiSuccess {string} events.title event title
	* @apiSuccess {string} events.description event description
	* @apiSuccess {string} events.beginDate beginning date of the event
	* @apiSuccess {string} events.endDate ending date of the event
	* @apiSuccess {string} events.createdAt date of creation of the event
	* @apiSuccess {string} events.editedAt date of edition of the event
	* @apiSuccess {Object[]} users list of participants
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getWeekPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": {
	*				"events": [
	*					{
	*					"id": 12,
	*					"projectId": 1,
	*					"creator": {"id": 1, "firstname": "John", "lastname": "Doe"},
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"beginDate": "1945-06-18 06:00:00",
	*					"endDate": "1945-06-18 08:00:00",
	*					"createdAt": "1945-06-18 08:00:00",
	*					"editedAt": "1945-06-18 08:00:00",
	*					"users": []
	*					},
	*					...
	*				]
	*			}
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.5.3",
	*			"return_message": "Calendar - getWeekPlanning - No Data Success"
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
	*	    "return_code": "5.2.3",
	*	    "return_message": "Calendar - getWeekPlanning - Bad ID"
	*	  }
	*	}
	*
	*/
	/**
	* @api {get} /V0.2/planning/getweek/:token/:date Get week planning
	* @apiName getWeekPlanning
	* @apiGroup Planning
	* @apiDescription Get planning of a week
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {string} date date of the first day of the week (into YYYY-MM-DD format)
	*
	* @apiSuccess {Object[]} events list of events
	* @apiSuccess {int} events.id Event id
	* @apiSuccess {int} events.projectId project id of the event (could be null)
	* @apiSuccess {Object} events.creator Event type object
	* @apiSuccess {int} events.creator.id creator id
	* @apiSuccess {string} events.creator.fullname creator fullname
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	* @apiSuccess {string} events.title event title
	* @apiSuccess {string} events.description event description
	* @apiSuccess {DateTime} events.beginDate beginning date of the event
	* @apiSuccess {DateTime} events.endDate ending date of the event
	* @apiSuccess {DateTime} events.createdAt date of creation of the event
	* @apiSuccess {DateTime} events.editedAt date of edition of the event
	* @apiSuccess {DateTime} events.deletedAt date of deletion of the event
	* @apiSuccess {Object[]} tasks list of tasks
	* @apiSuccess {int} tasks.id task id
	* @apiSuccess {int} tasks.creatorId creator id
	* @apiSuccess {string} tasks.title event title
	* @apiSuccess {string} tasks.description task description
	* @apiSuccess {DateTime} tasks.startedAt date when the task was started
	* @apiSuccess {DateTime} tasks.dueDate deadline date of the task
	* @apiSuccess {DateTime} tasks.finishedAt date when the task was finished
	* @apiSuccess {int} tasks.projectId project's id
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getWeekPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": {
	*				"events": [
	*					{
	*					"id": 12,
	*					"projectId": 1,
	*					"creator": {"id": 1, "fullname": "John Doe"},
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"icon": "100011001010...",
	*					"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"createdAt":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"editedAt":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"deletedAt": null
	*					},
	*					...
	*				],
	*				"tasks": [
	*					{
	*					"id": 12,
	*					"creatorId": 10,
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"startedAt":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"dueDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"finishedAt": null,
	*					"projectId": 1
	*					},
	*					...
	*				]
	*			}
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.5.3",
	*			"return_message": "Calendar - getWeekPlanning - No Data Success"
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
	*	    "return_code": "5.2.3",
	*	    "return_message": "Calendar - getWeekPlanning - Bad ID"
	*	  }
	*	}
	*
	*/

	public function getWeekPlanningAction(Request $request, $token, $date)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.2.3", "Calendar", "getWeekPlanning"));

		$date_begin = new DateTime($date);
		$date_end = new DateTime($date);
		$date_end->add(new DateInterval('P7D'));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:Event');
		$query = $repository->createQueryBuilder('e')
			->innerJoin('e.users', 'u')
			->where('u.id = :user_id')
			->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
			->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
			->getQuery()->getResult();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = $value->objectToArray();
		}

		if (count($events) <= 0 && count($tasks) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getWeekPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getWeekPlanning", "Complete Success", array("array" => array("events" => $events)));
	}

	/**
	* @api {get} /0.3/planning/month/:token/:date Get month planning
	* @apiName getMonthPlanning
	* @apiGroup Planning
	* @apiDescription Get planning of a month
	* @apiVersion 0.3.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {string} date date of the first day of the month (into YYYY-MM-DD format)
	*
	* @apiSuccess {Object[]} events list of events
	* @apiSuccess {int} events.id Event id
	* @apiSuccess {int} events.projectId project id of the event (could be null)
	* @apiSuccess {Object} events.creator Event type object
	* @apiSuccess {int} events.creator.id creator id
	* @apiSuccess {string} events.creator.firstname creator firstname
	* @apiSuccess {string} events.creator.lastname creator lastname
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	* @apiSuccess {string} events.title event title
	* @apiSuccess {string} events.description event description
	* @apiSuccess {string} events.beginDate beginning date of the event
	* @apiSuccess {string} events.endDate ending date of the event
	* @apiSuccess {string} events.createdAt date of creation of the event
	* @apiSuccess {string} events.editedAt date of edition of the event
	* @apiSuccess {Object[]} users list of participants
	* @apiSuccess {int} users.id user id
	* @apiSuccess {string} users.firstname user firstname
	* @apiSuccess {string} users.lastname user lastname
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getMonthPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": {
	*				"events": [
	*					{
	*					"id": 12,
	*					"projectId": 1,
	*					"creator": {"id": 1, "firstname": "John", "lastname": "Doe"},
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"beginDate": "1945-06-18 06:00:00",
	*					"endDate": "1945-06-18 08:00:00",
	*					"createdAt": "1945-06-18 08:00:00",
	*					"editedAt": "1945-06-18 08:00:00",
	*					"users": []
	*					},
	*					...
	*				]
	*			}
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.5.3",
	*			"return_message": "Calendar - getMonthPlanning - No Data Success"
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
	*	    "return_code": "5.3.3",
	*	    "return_message": "Calendar - getMonthPlanning - Bad ID"
	*	  }
	*	}
	*
	*/
	/**
	* @api {get} /V0.2/planning/getmonth/:token/:date Get month planning
	* @apiName getMonthPlanning
	* @apiGroup Planning
	* @apiDescription Get planning of a month
	* @apiVersion 0.2.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {string} date date of the first day of the month (into YYYY-MM-DD format)
	*
	* @apiSuccess {Object[]} events list of events
	* @apiSuccess {int} events.id Event id
	* @apiSuccess {int} events.projectId project id of the event (could be null)
	* @apiSuccess {Object} events.creator Event type object
	* @apiSuccess {int} events.creator.id creator id
	* @apiSuccess {string} events.creator.fullname creator fullname
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	* @apiSuccess {string} events.title event title
	* @apiSuccess {string} events.description event description
	* @apiSuccess {DateTime} events.beginDate beginning date of the event
	* @apiSuccess {DateTime} events.endDate ending date of the event
	* @apiSuccess {DateTime} events.createdAt date of creation of the event
	* @apiSuccess {DateTime} events.editedAt date of edition of the event
	* @apiSuccess {DateTime} events.deletedAt date of deletion of the event
	* @apiSuccess {Object[]} tasks list of tasks
	* @apiSuccess {int} tasks.id task id
	* @apiSuccess {int} tasks.creatorId creator id
	* @apiSuccess {string} tasks.title event title
	* @apiSuccess {string} tasks.description task description
	* @apiSuccess {DateTime} tasks.startedAt date when the task was started
	* @apiSuccess {DateTime} tasks.dueDate deadline date of the task
	* @apiSuccess {DateTime} tasks.finishedAt date when the task was finished
	* @apiSuccess {int} tasks.projectId project's id
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getMonthPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": {
	*				"events": [
	*					{
	*					"id": 12,
	*					"projectId": 1,
	*					"creator": {"id": 1, "fullname": "John Doe"},
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"icon": "100011001010...",
	*					"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"createdAt":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"editedAt":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"deletedAt": null
	*					},
	*					...
	*				],
	*				"tasks": [
	*					{
	*					"id": 12,
	*					"creatorId": 10,
	*					"title": "Brainstorming",
	*					"description": "blablabla blablabla ...",
	*					"startedAt":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"dueDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"finishedAt": null,
	*					"projectId": 1
	*					},
	*					...
	*				]
	*			}
	*		}
	* 	}
	* @apiSuccessExample Success But No Data:
	* 	{
	*		"info": {
	*			"return_code": "1.5.3",
	*			"return_message": "Calendar - getMonthPlanning - No Data Success"
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
	*	    "return_code": "5.3.3",
	*	    "return_message": "Calendar - getMonthPlanning - Bad ID"
	*	  }
	*	}
	*
	*/
	public function getMonthPlanningAction(Request $request, $token, $date)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.3.3", "Calendar", "getMonthPlanning"));

		$date_begin = new DateTime($date);
		$date_end = new DateTime($date);
		$date_end->add(new DateInterval('P1M'));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:Event');
		$query = $repository->createQueryBuilder('e')
			->innerJoin('e.users', 'u')
			->where('u.id = :user_id')
			->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
			->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
			->getQuery()->getResult();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = $value->objectToArray();
		}

		if (count($events) <= 0 && count($tasks) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getMonthPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getMonthPlanning", "Complete Success", array("array" => array("events" => $events)));
	}
}
