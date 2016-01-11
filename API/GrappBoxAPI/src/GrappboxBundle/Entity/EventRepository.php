<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
}
