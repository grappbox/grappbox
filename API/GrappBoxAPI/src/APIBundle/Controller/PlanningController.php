<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use DateTime;
use DateInterval;

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

class PlanningController extends RolesAndTokenVerificationController
{

	/**
	* @api {post} /V0.11/planning/getday get planning of a day
	* @apiName getDayPlanning
	* @apiGroup Planning
	* @apiVersion 0.11.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {Date} date date of event to list (hour, min and second MUST be set to zero)
	*
	* @apiSuccess {Object[]} data event list
	* @apiSuccess {int} id Event id
	* @apiSuccess {int} projectId project id
	* @apiSuccess {int} eventTypeId Event type id
	* @apiSuccess {string} eventType Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		[
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		},
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		},
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		}
	*		]
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function getDayPlanningAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$date_begin = new DateTime($content->date);
		$date_end = new DateTime($content->date);
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
				"eventTypeId" => $value->getEventtypes()->getId(),
				"eventtType" => $value->getEventtypes()->getName(),
				"title" => $value->getTitle(),
				"beginDate" => $value->getBeginDate(),
				"endDate" => $value->getEndDate()
			);
		}

		return new JsonResponse($events);
	}

	/**
	* @api {post} /V0.11/planning/getweek get planning of a week
	* @apiName getWeekPlanning
	* @apiGroup Planning
	* @apiVersion 0.11.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {Date} date date of the first day of the week (hour, min and second MUST be set to zero)
	*
	* @apiSuccess {Object[]} data event list
	* @apiSuccess {int} id Event id
	* @apiSuccess {int} projectId project id
	* @apiSuccess {int} eventTypeId Event type id
	* @apiSuccess {string} eventType Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		[
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		},
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		},
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		}
	*		]
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function getWeekPlanningAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$date_begin = new DateTime($content->date);
		$date_end = new DateTime($content->date);
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
				"eventTypeId" => $value->getEventtypes()->getId(),
				"eventtType" => $value->getEventtypes()->getName(),
				"title" => $value->getTitle(),
				"beginDate" => $value->getBeginDate(),
				"endDate" => $value->getEndDate()
			);
		}

		return new JsonResponse($events);
	}

	/**
	* @api {post} /V0.11/planning/getmonth get planning of a month
	* @apiName getMonthPlanning
	* @apiGroup Planning
	* @apiVersion 0.11.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {Date} date date of the first day of the month (hour, min and second MUST be set to zero)
	*
	* @apiSuccess {Object[]} data event list
	* @apiSuccess {int} id Event id
	* @apiSuccess {int} projectId project id
	* @apiSuccess {int} eventTypeId Event type id
	* @apiSuccess {string} eventType Event type name
	*	@apiSuccess {string} title event title
	*	@apiSuccess {DateTime} beginDate beginning date of the event
	*	@apiSuccess {DateTime} endDate ending date of the event
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		[
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		},
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		},
	*		{
	*			"id": 12, "projectId": 21, "eventTypeId": 1, "eventType": "Event",
	*			"title": "Brainstorming",
	*			"beginDate":{"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"endDate":{"date": "1945-06-18 08:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*		}
	*		]
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	*/
	public function getMonthPlanningAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$date_begin = new DateTime($content->date);
		$date_end = new DateTime($content->date);
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
				"eventTypeId" => $value->getEventtypes()->getId(),
				"eventtType" => $value->getEventtypes()->getName(),
				"title" => $value->getTitle(),
				"beginDate" => $value->getBeginDate(),
				"endDate" => $value->getEndDate()
			);
		}

		return new JsonResponse($events);
	}
}
