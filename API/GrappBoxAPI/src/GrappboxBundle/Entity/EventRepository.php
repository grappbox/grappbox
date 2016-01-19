<?php

namespace GrappboxBundle\Entity;

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
	public function findNextMeetings($id)
	{
		$qb = $this->createQueryBuilder('e');
		$meetings = $qb->getQuery()->getResult();
		$arr = array();
		$i = 0;
		$defaultDate = new \DateTime;
		if ($meetings === null)
		{
			throw new NotFoundHttpException("No events for the id ".$id);
		}
		foreach ($meetings as $meeting) {
			$endDate = $meeting->getEndDate();
			$creatorId = $meeting->getCreatorUser()->getId();
			if ($endDate > $defaultDate && $creatorId == $id)
			{
				$project = $meeting->getProjects();
				$eventType = $meeting->getEventtypes();
				$projectName = null;
				$projectLogo = null;
			 	$typeName = $eventType->getName();
			 	if ($project)
			 	{
					$projectName = $project->getName();
					$projectLogo = $project->getLogo();
				}
					$eventTitle = $meeting->getTitle();
					$eventDescription = $meeting->getDescription();
					$beginDate = $meeting->getBeginDate();
					$arr["Meeting ".$i] = array("project_name" => $projectName, "project_logo" => $projectLogo, "event_type" => $typeName, "event_title" => $eventTitle,
						"event_description" => $eventDescription, "event_begin_date" => $beginDate, "event_end_date" => $endDate);
				$i++;
			}
			else if ($endDate > $defaultDate)
			{
				$users = $meeting->getUsers();
				foreach ($users as $user) {
					$userId = $user->getId();
					if ($userId == $id)
					{
						$project = $meeting->getProjects();
						$eventType = $meeting->getEventtypes();
						$projectName = null;
						$projectLogo = null;
					 	$typeName = $eventType->getName();
					 	if ($project)
					 	{
							$projectName = $project->getName();
							$projectLogo = $project->getLogo();
						}
							$eventTitle = $meeting->getTitle();
							$eventDescription = $meeting->getDescription();
							$beginDate = $meeting->getBeginDate();
							$arr["Meeting ".$i] = array("project_name" => $projectName, "project_logo" => $projectLogo, "event_type" => $typeName, "event_title" => $eventTitle,
								"event_description" => $eventDescription, "event_begin_date" => $beginDate, "event_end_date" => $endDate);
						$i++;
					}
				}
			}
		}
		if (count($meetings) == 0 || count($arr) == 0)
		{
			return (Object)$arr;
		}
		return ($arr);
	}

	public function findNextMeetingsV2($id, $code, $part, $function)
	{
		$defaultDate = new \DateTime;
		$qb = $this->createQueryBuilder('e')->where('e.endDate > :defaultDate')->setParameter('defaultDate', $defaultDate);
		$meetings = $qb->getQuery()->getResult();

		$resp = new JsonResponse();
		$ret = array();
		$arr = array();

		if ($meetings === null || count($meetings) == 0)
		{
			$ret["info"] = array("return_code" => "1.2.3", "return_message" => "Dashboard - getnextmeetings - No Data Success");
			$ret["data"] = array("array" => []);
			$resp->setStatusCode(JsonResponse::HTTP_OK);
			$resp->setData($ret);

			return $resp;
		}

		foreach ($meetings as $meeting) {
			$endDate = $meeting->getEndDate();
			$creatorId = $meeting->getCreatorUser()->getId();

			if ($creatorId == $id)
			{
				$project = $meeting->getProjects();
				$eventType = $meeting->getEventtypes();
				$projectName = null;
				$projectLogo = null;

			 	$typeName = $eventType->getName();
			 	if ($project)
			 	{
					$projectName = $project->getName();
					$projectLogo = $project->getLogo();
				}
				$eventTitle = $meeting->getTitle();
				$eventDescription = $meeting->getDescription();
				$beginDate = $meeting->getBeginDate();

				$arr[] = array("projects" => array("name" => $projectName, "logo" => $projectLogo), "type" => $typeName, "title" => $eventTitle,
					"description" => $eventDescription, "begin_date" => $beginDate, "end_date" => $endDate);
			}
			else
			{
				$users = $meeting->getUsers();

				foreach ($users as $user) {
					$userId = $user->getId();

					if ($userId == $id)
					{
						$project = $meeting->getProjects();
						$eventType = $meeting->getEventtypes();
						$projectName = null;
						$projectLogo = null;

					 	$typeName = $eventType->getName();
					 	if ($project)
					 	{
							$projectName = $project->getName();
							$projectLogo = $project->getLogo();
						}
						$eventTitle = $meeting->getTitle();
						$eventDescription = $meeting->getDescription();
						$beginDate = $meeting->getBeginDate();

						$arr[] = array("projects" => array("name" => $projectName, "logo" => $projectLogo), "type" => $typeName, "title" => $eventTitle,
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
