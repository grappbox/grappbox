<?php

namespace GrappboxBundle\Controller;

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
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	*	@apiSuccess {string} events.title event title
	*	@apiSuccess {DateTime} events.beginDate beginning date of the event
	*	@apiSuccess {DateTime} events.endDate ending date of the event
	* @apiSuccess {Object[]} tasks list of tasks
	* @apiSuccess {int} tasks.id task id
	*	@apiSuccess {string} tasks.title event title
	*	@apiSuccess {DateTime} tasks.startedAt date when the task started
	*	@apiSuccess {DateTime} tasks.dueDate deadline date of the task
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getDayPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				"events": [
	*					{
	*					"id": 12,
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"icon": "100011001010...",
	*					"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*					},
	*					...
	*				],
	*				"tasks": [
	*					{
	*					"id": 12,
	*					"title": "Brainstorming",
	*					"startedAt":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"dueDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*					},
	*					...
	*				]
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
		$repository = $em->getRepository('GrappboxBundle:Event');
		$query = $repository->createQueryBuilder('e')
    	->innerJoin('e.users', 'u')
    	->where('u.id = :user_id')
			->andWhere('e.deletedAt IS NULL')
			->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
    	->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
    	->getQuery()->getResult();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = array(
				"id" => $value->getId(),
				"type" => array(
					"id" => $value->getEventtypes()->getId(),
					"name" => $value->getEventtypes()->getName()
				),
				"title" => $value->getTitle(),
				"icon" => $value->getIcon(),
				"beginDate" => $value->getBeginDate(),
				"endDate" => $value->getEndDate()
			);
		}

		$repository = $em->getRepository('GrappboxBundle:Task');
		$query = $repository->createQueryBuilder('t')
					->join('t.users', 'u')
					->where('u.id = :user_id')
					->andWhere('t.deletedAt IS NULL')
					->andWhere('t.finishedAt IS NULL')
					->andWhere('t.startedAt IS NOT NULL')
		    	->setParameters(array('user_id' => $user->getId()))
					->getQuery()->getResult();

		$tasks = array();
		foreach ($query as $key => $value) {
			$events[] = array(
				"id" => $value->getId(),
				"title" => $value->getTitle(),
				"startedAt" => $value->getStartedAt(),
				"dueDate" => $value->getDueDate()
			);
		}

		if (count($events) <= 0 && count($tasks) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getDayPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getDayPlanning", "Complete Success", array("array" => array("events" => $events, "tasks" => $tasks)));
	}

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
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	*	@apiSuccess {string} events.title event title
	*	@apiSuccess {DateTime} events.beginDate beginning date of the event
	*	@apiSuccess {DateTime} events.endDate ending date of the event
	* @apiSuccess {Object[]} tasks list of tasks
	* @apiSuccess {int} tasks.id task id
	*	@apiSuccess {string} tasks.title event title
	*	@apiSuccess {DateTime} tasks.startedAt date when the task started
	*	@apiSuccess {DateTime} tasks.dueDate deadline date of the task
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getWeekPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				"events": [
	*					{
	*					"id": 12,
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"icon": "100011001010...",
	*					"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*					},
	*					...
	*				],
	*				"tasks": [
	*					{
	*					"id": 12,
	*					"title": "Brainstorming",
	*					"startedAt":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"dueDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*					},
	*					...
	*				]
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
		$repository = $em->getRepository('GrappboxBundle:Event');
		$query = $repository->createQueryBuilder('e')
			->innerJoin('e.users', 'u')
			->where('u.id = :user_id')
			->andWhere('e.deletedAt IS NULL')
			->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
			->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
			->getQuery()->getResult();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = array(
				"id" => $value->getId(),
				"type" => array(
					"id" => $value->getEventtypes()->getId(),
					"name" => $value->getEventtypes()->getName()
				),
				"title" => $value->getTitle(),
				"beginDate" => $value->getBeginDate(),
				"endDate" => $value->getEndDate()
			);
		}

		$repository = $em->getRepository('GrappboxBundle:Task');
		$query = $repository->createQueryBuilder('t')
					->join('t.users', 'u')
					->where('u.id = :user_id')
					->andWhere('t.deletedAt IS NULL')
					->andWhere('t.finishedAt IS NULL')
					->andWhere('t.startedAt IS NOT NULL')
					->setParameters(array('user_id' => $user->getId()))
					->getQuery()->getResult();

		$tasks = array();
		foreach ($query as $key => $value) {
			$events[] = array(
				"id" => $value->getId(),
				"title" => $value->getTitle(),
				"startedAt" => $value->getStartedAt(),
				"dueDate" => $value->getDueDate()
			);
		}

		if (count($events) <= 0 && count($tasks) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getWeekPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getWeekPlanning", "Complete Success", array("array" => array("events" => $events, "tasks" => $tasks)));
	}

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
	* @apiSuccess {Object} events.type Event type object
	* @apiSuccess {int} events.type.id Event type id
	* @apiSuccess {string} events.type.name Event type name
	*	@apiSuccess {string} events.title event title
	*	@apiSuccess {DateTime} events.beginDate beginning date of the event
	*	@apiSuccess {DateTime} events.endDate ending date of the event
	* @apiSuccess {Object[]} tasks list of tasks
	* @apiSuccess {int} tasks.id task id
	*	@apiSuccess {string} tasks.title event title
	*	@apiSuccess {DateTime} tasks.startedAt date when the task started
	*	@apiSuccess {DateTime} tasks.dueDate deadline date of the task
	*
	* @apiSuccessExample Complete Success:
	* 	{
	*		"info": {
	*			"return_code": "1.5.1",
	*			"return_message": "Calendar - getMonthPlanning - Complete success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				"events": [
	*					{
	*					"id": 12,
	*					"type": {"id": 1, "name": "Event"},
	*					"title": "Brainstorming",
	*					"icon": "100011001010...",
	*					"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*					},
	*					...
	*				],
	*				"tasks": [
	*					{
	*					"id": 12,
	*					"title": "Brainstorming",
	*					"startedAt":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*					"dueDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*					},
	*					...
	*				]
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
		$repository = $em->getRepository('GrappboxBundle:Event');
		$query = $repository->createQueryBuilder('e')
			->innerJoin('e.users', 'u')
			->where('u.id = :user_id')
			->andWhere('e.deletedAt IS NULL')
			->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
			->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
			->getQuery()->getResult();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = array(
				"id" => $value->getId(),
				"type" => array(
					"id" => $value->getEventtypes()->getId(),
					"name" => $value->getEventtypes()->getName()
				),
				"title" => $value->getTitle(),
				"beginDate" => $value->getBeginDate(),
				"endDate" => $value->getEndDate()
			);
		}

		$repository = $em->getRepository('GrappboxBundle:Task');
		$query = $repository->createQueryBuilder('t')
					->join('t.users', 'u')
					->where('u.id = :user_id')
					->andWhere('t.deletedAt IS NULL')
					->andWhere('t.finishedAt IS NULL')
					->andWhere('t.startedAt IS NOT NULL')
					->setParameters(array('user_id' => $user->getId()))
					->getQuery()->getResult();

		$tasks = array();
		foreach ($query as $key => $value) {
			$events[] = array(
				"id" => $value->getId(),
				"title" => $value->getTitle(),
				"startedAt" => $value->getStartedAt(),
				"dueDate" => $value->getDueDate()
			);
		}

		if (count($events) <= 0 && count($tasks) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getMonthPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getMonthPlanning", "Complete Success", array("array" => array("events" => $events, "tasks" => $tasks)));
	}
}
