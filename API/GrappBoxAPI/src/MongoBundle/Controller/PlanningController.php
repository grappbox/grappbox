<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GrappBundle\Document\Event;
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
	* @api {post} /mongo/planning/getday get planning of a day
	* @apiName getDayPlanning
	* @apiGroup Planning
	* @apiVersion 0.11.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {DateTime} date date of event to list (hour, min and second MUST be set to zero)
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Event');
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
	* @api {post} /mongo/planning/getweek get planning of a week
	* @apiName getWeekPlanning
	* @apiGroup Planning
	* @apiVersion 0.11.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {DateTime} date date of the first day of the week (hour, min and second MUST be set to zero)
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Event');
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
	* @api {put} /mongo/planning/getmonth get planning of a month
	* @apiName getMonthPlanning
	* @apiGroup Planning
	* @apiVersion 0.11.0
	*
	* @apiParam {string} token user authentication token
	* @apiParam {DateTime} date date of the first day of the month (hour, min and second MUST be set to zero)
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Event');
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
