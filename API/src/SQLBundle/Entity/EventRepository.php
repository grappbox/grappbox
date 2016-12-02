<?php

namespace SQLBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * EventRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class EventRepository extends EntityRepository
{
	public function findNextMeetings($id, $projectId, $code, $part, $function)
	{
		$defaultDate = new \DateTime;
		$date_end = new \DateTime($defaultDate->format('Y-m-d'));
		$date_end->add(new \DateInterval('P7D'));
		$qb = $this->createQueryBuilder('e')
					->where('e.projects = :projectId AND e.beginDate < :end_day AND e.endDate > :begin_day')
					->setParameter('projectId', $projectId)->setParameter('begin_day', $defaultDate)->setParameter('end_day', $date_end)
					->orderBy('e.beginDate', 'ASC');
		$meetings = $qb->getQuery()->getResult();

		$resp = new JsonResponse();
		$ret = array();
		$arr = array();

		if ($meetings === null || count($meetings) == 0)
		{
			$ret["info"] = array("return_code" => "1.".$code.".3", "return_message" => $part." - ".$function." - No Data Success");
			$ret["data"] = array("array" => []);
			$resp->setStatusCode(JsonResponse::HTTP_PARTIAL_CONTENT);
			$resp->setData($ret);

			return $resp;
		}

		foreach ($meetings as $meeting) {
			$endDate = $meeting->getEndDate();
			$creatorId = $meeting->getCreatorUser()->getId();

			if ($creatorId == $id)
			{
				$eventTitle = $meeting->getTitle();
				$eventDescription = $meeting->getDescription();
				$beginDate = $meeting->getBeginDate();
				if ($beginDate != null)
            		$beginDate = $beginDate->format('Y-m-d H:i:s');
		        if ($endDate != null)
		            $endDate = $endDate->format('Y-m-d H:i:s');

				$arr[] = array("id" => $meeting->getId(), "title" => $eventTitle,
					"description" => $eventDescription, "begin_date" => $beginDate, "end_date" => $endDate);
			}
			else
			{
				$users = $meeting->getUsers();

				foreach ($users as $user) {
					$userId = $user->getId();

					if ($userId == $id)
					{
						$eventTitle = $meeting->getTitle();
						$eventDescription = $meeting->getDescription();
						$beginDate = $meeting->getBeginDate();
						if ($beginDate != null)
		            		$beginDate = $beginDate->format('Y-m-d H:i:s');
				        if ($endDate != null)
				            $endDate = $endDate->format('Y-m-d H:i:s');

						$arr[] = array("id" => $meeting->getId(), "title" => $eventTitle,
							"description" => $eventDescription, "begin_date" => $beginDate, "end_date" => $endDate);
					}
				}
			}
		}

		if (count($arr) == 0)
		{
			$ret["info"] = array("return_code" => "1.".$code.".3", "return_message" => $part." - ".$function." - No Data Success");
			$ret["data"] = array("array" => []);
			$resp->setStatusCode(JsonResponse::HTTP_PARTIAL_CONTENT);
			$resp->setData($ret);

			return $resp;
		}

		$ret["info"] = array("return_code" => "1.".$code.".1", "return_message" => $part." - ".$function." - Complete success");
		$ret["data"] = array("array" => $arr);
		$resp->setStatusCode(JsonResponse::HTTP_OK);
		$resp->setData($ret);

		return ($resp);
	}
}
