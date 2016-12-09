<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GrappBundle\Document\Event;
use GrappBundle\Document\Task;
use DateTime;
use DateInterval;

/**
*  @IgnoreAnnotation("apiName")
*  @IgnoreAnnotation("apiGroup")
*  @IgnoreAnnotation("apiVersion")
*  @IgnoreAnnotation("apiDescription")
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
	* @-api {get} /0.3/planning/day/:date Get day planning
	* @apiName getDayPlanning
	* @apiGroup Planning
	* @apiDescription Get a one day planning
	* @apiVersion 0.3.0
	*
	*/
	public function getDayPlanningAction(Request $request, $date)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("5.1.3", "Calendar", "getDayPlanning"));

		$date_begin = new DateTime($date);
		$date_end = new DateTime($date);
		$date_end->add(new DateInterval('P1D'));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Event');
		$query = $repository->createQueryBuilder('e')
    	->innerJoin('e.users', 'u')
    	->where('u.id = :user_id')
			->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
    	->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
    	->getQuery()->execute();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = $value->objectToArray();
		}

	 	if (count($events) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getDayPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getDayPlanning", "Complete Success", array("array" => array("events" => $events)));
	}

	/**
	* @-api {get} /0.3/planning/week/:date Get week planning
	* @apiName getWeekPlanning
	* @apiGroup Planning
	* @apiDescription Get planning of a week
	* @apiVersion 0.3.0
	*
	*/
	public function getWeekPlanningAction(Request $request, $date)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("5.2.3", "Calendar", "getWeekPlanning"));

		$date_begin = new DateTime($date);
		$date_end = new DateTime($date);
		$date_end->add(new DateInterval('P7D'));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Event');
		$query = $repository->createQueryBuilder('e')
			->innerJoin('e.users', 'u')
			->where('u.id = :user_id')
			->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
			->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
			->getQuery()->execute();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = $value->objectToArray();
		}

		if (count($events) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getWeekPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getWeekPlanning", "Complete Success", array("array" => array("events" => $events)));
	}

	/**
	* @-api {get} /0.3/planning/month/:date Get month planning
	* @apiName getMonthPlanning
	* @apiGroup Planning
	* @apiDescription Get planning of a month
	* @apiVersion 0.3.0
	*
	*/
	public function getMonthPlanningAction(Request $request, $date)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("5.3.3", "Calendar", "getMonthPlanning"));

		$date_begin = new DateTime($date);
		$date_end = new DateTime($date);
		$date_end->add(new DateInterval('P1M'));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Event');
		$query = $repository->createQueryBuilder('e')
			->innerJoin('e.users', 'u')
			->where('u.id = :user_id')
			->andWhere('e.beginDate < :end_day AND e.endDate > :begin_day')
			->setParameters(array('user_id' => $user->getId(), 'begin_day' => $date_begin, 'end_day' => $date_end))
			->getQuery()->execute();

		$events = array();
		foreach ($query as $key => $value) {
			$events[] = $value->objectToArray();
		}

		if (count($events) <= 0)
			return $this->setNoDataSuccess("1.5.3", "Calendar", "getMonthPlanning");

		return $this->setSuccess("1.5.1", "Calendar", "getMonthPlanning", "Complete Success", array("array" => array("events" => $events)));
	}
}
