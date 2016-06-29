<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GrappBundle\Document\Event;
use GrappBundle\Entity\Task;
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
	* @api {get} /mongo/planning/getday/:token/:date Get day planning
	* @apiName getDayPlanning
	* @apiGroup Planning
	* @apiDescription Get a one day planning
	* @apiVersion 0.2.0
	*
	*/
	public function getDayPlanningAction(Request $request, $token, $date)
	{
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("5.1.3", "Calendar", "getDayPlanning"));

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
    	->getQuery()->execute();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = $value->objectToArray();
		}

		$repository = $em->getRepository('MongoBundle:Task');
		$query = $repository->createQueryBuilder('t')
					->join('t.ressources', 'r')
					->where('r.user = :user_id')
					->andWhere('t.deletedAt IS NULL')
					->andWhere('t.finishedAt IS NULL')
					->andWhere('t.startedAt IS NOT NULL')
					->setParameters(array('user_id' => $user->getId()))
					->getQuery()->execute();

		$tasks = array();
		foreach ($query as $key => $value) {
			$tasks[] = $value->objectToArray();
		}

		$query = $repository->createQueryBuilder('t')
					->where('t.creator_user = :user_id ')
					->andWhere('t.deletedAt IS NULL')
					->andWhere('t.finishedAt IS NULL')
					->andWhere('t.startedAt IS NOT NULL')
					->setParameters(array('user_id' => $user->getId()))
					->getQuery()->execute();

		foreach ($query as $key => $value) {
			$tasks[] = $value->objectToArray();
		}

	 if (count($events) <= 0 && count($tasks) <= 0)
		return $this->setNoDataSuccess("1.5.3", "Calendar", "getDayPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getDayPlanning", "Complete Success", array("array" => array("events" => $events, "tasks" => $tasks)));
	}

	/**
	* @api {get} /mongo/planning/getweek/:token/:date Get week planning
	* @apiName getWeekPlanning
	* @apiGroup Planning
	* @apiDescription Get planning of a week
	* @apiVersion 0.2.0
	*
	*/
	public function getWeekPlanningAction(Request $request, $token, $date)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.2.3", "Calendar", "getWeekPlanning"));

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
			->getQuery()->execute();

			$events = array();
			foreach ($query as $key => $value) {
				$events[] = $value->objectToArray();
			}

			$repository = $em->getRepository('MongoBundle:Task');
			$query = $repository->createQueryBuilder('t')
						->join('t.ressources', 'r')
						->where('r.user = :user_id')
						->andWhere('t.deletedAt IS NULL')
						->andWhere('t.finishedAt IS NULL')
						->andWhere('t.startedAt IS NOT NULL')
						->setParameters(array('user_id' => $user->getId()))
						->getQuery()->execute();

			$tasks = array();
			foreach ($query as $key => $value) {
				$tasks[] = $value->objectToArray();
			}

			$query = $repository->createQueryBuilder('t')
						->where('t.creator_user = :user_id ')
						->andWhere('t.deletedAt IS NULL')
						->andWhere('t.finishedAt IS NULL')
						->andWhere('t.startedAt IS NOT NULL')
						->setParameters(array('user_id' => $user->getId()))
						->getQuery()->execute();

			foreach ($query as $key => $value) {
				$tasks[] = $value->objectToArray();
			}

			if (count($events) <= 0 && count($tasks) <= 0)
				return $this->setNoDataSuccess("1.5.3", "Calendar", "getWeekPlanning");

			return $this->setSuccess("1.5.1", "Calendar", "getWeekPlanning", "Complete Success", array("array" => array("events" => $events, "tasks" => $tasks)));
		}

	/**
	* @api {get} /mongo/planning/getmonth/:token/:date Get month planning
	* @apiName getMonthPlanning
	* @apiGroup Planning
	* @apiDescription Get planning of a month
	* @apiVersion 0.2.0
	*
	*/
	public function getMonthPlanningAction(Request $request)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("5.3.3", "Calendar", "getMonthPlanning"));

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
			->getQuery()->execute();

			$events = array();
			foreach ($query as $key => $value) {
				$events[] = $value->objectToArray();
			}

			$repository = $em->getRepository('MongoBundle:Task');
			$query = $repository->createQueryBuilder('t')
						->join('t.ressources', 'r')
						->where('r.user = :user_id')
						->andWhere('t.deletedAt IS NULL')
						->andWhere('t.finishedAt IS NULL')
						->andWhere('t.startedAt IS NOT NULL')
						->setParameters(array('user_id' => $user->getId()))
						->getQuery()->execute();

			$tasks = array();
			foreach ($query as $key => $value) {
				$tasks[] = $value->objectToArray();
			}

			$query = $repository->createQueryBuilder('t')
						->where('t.creator_user = :user_id ')
						->andWhere('t.deletedAt IS NULL')
						->andWhere('t.finishedAt IS NULL')
						->andWhere('t.startedAt IS NOT NULL')
						->setParameters(array('user_id' => $user->getId()))
						->getQuery()->execute();

			foreach ($query as $key => $value) {
				$tasks[] = $value->objectToArray();
			}

			if (count($events) <= 0 && count($tasks) <= 0)
				return $this->setNoDataSuccess("1.5.3", "Calendar", "getMonthPlanning");

			return $this->setSuccess("1.5.1", "Calendar", "getMonthPlanning", "Complete Success", array("array" => array("events" => $events, "tasks" => $tasks)));
		}
	}
