<?php

namespace APIBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository
{
	public function findTeamOccupation($id)
	{
		$qb = $this->createQueryBuilder('p')->where('p.creatorId = :id')->setParameter('id', $id);
		
		$projects = $qb->getQuery()->getResult();

		$arr = array();
		$i = 0;
		$defaultDate = date_create("0000-00-00 00:00:00");

		if ($projects === null)
		{
			throw new NotFoundHttpException("No projects for the id ".$id);
		}

		foreach ($projects as $project)
		{
			$projectName = $project->getName();
			$projectUsers = $project->getUsers();
			$projectId = $project->getId();
			foreach ($projectUsers as $user) {
				$firstName = $user->getFirstname();
				$lastName = $user->getLastname();
				$tasks = $user->getTasks();
				$nbOfOngoingTasks = 0;
				$nbOfTasksBegun = 0;
				$busy = false;

				foreach ($tasks as $task) {
					if ($task->getProjectId() == $projectId)
					{
						if ($task->getFinishedAt() == $defaultDate)
						{
							$busy = true;
							$nbOfOngoingTasks++;
						}
						if ($task->getStartedAt() != $defaultDate)
							$nbOfTasksBegun++;
					}
				}
				if ($busy == true)
				{
					$arr["Person ".$i] = array("project_name" => $projectName, "first_name" => $firstName, "last_name" => $lastName, "occupation" => "busy", "number_of_tasks_begun" => $nbOfTasksBegun, "number_of_ongoing_tasks" => $nbOfOngoingTasks);
				}				
				else
				{
					$arr["Person ".$i] = array("project_name" => $projectName, "first_name" => $firstName, "last_name" => $lastName, "occupation" => "free", "number_of_tasks_begun" => $nbOfTasksBegun, "number_of_ongoing_tasks" => $nbOfOngoingTasks);
				}
				$i++;
			}
		}

		return $arr;
	}

	public function findProjectGlobalProgress($id)
	{
		$qb = $this->createQueryBuilder('p')->join('p.users', 'u')->where('u.id = :id')->setParameter('id', $id);
		
		$projects = $qb->getQuery()->getResult();

		$arr = array();
		$i = 0;
		$defaultDate = date_create("0000-00-00 00:00:00");

		if ($projects === null)
		{
			throw new NotFoundHttpException("No projects for the id ".$id);
		}


		foreach ($projects as $project) {
			$projectId = $project->getId();
			$projectName = $project->getName();
			$projectDescription = $project->getDescription();
			$projectLogo = $project->getLogo();
			$contactMail = $project->getContactEmail();
			$facebook = $project->getFacebook();
			$twitter = $project->getTwitter();
			$tasks = $project->getTasks();
			$bugs = $project->getBugs();
			$timelines = $project->getTimelines();
			$nbTasks = 0;
			$nbFinishedTasks = 0;
			$nbOngoingTasks = 0;
			$nbBugs = 0;
			$nbMessages = 0;

			foreach ($tasks as $task) {
				$nbTasks++;
				if ($task->getFinishedAt() != $defaultDate)
				{
					$nbFinishedTasks++;
				}
				else
				{
					$nbOngoingTasks++;
				}
			}

			foreach ($bugs as $bug) {
				if ($bug->getDeletedAt() == $defaultDate)
				{
					$nbBugs++;
				}
			}

			foreach ($timelines as $timeline) {
				$messages = $timeline->getTimelineMessages();
				foreach ($messages as $message) {
					$nbMessages++;
				}
			}

			$arr["Project ".$i] = array("project_id" => $projectId, "project_name" => $projectName, "project_description" => $projectDescription, "project_logo" => $projectLogo, "contact_mail" => $contactMail,
				"facebook" => $facebook, "twitter" => $twitter, "number_finished_tasks" => $nbFinishedTasks, "number_ongoing_tasks" => $nbOngoingTasks, "number_tasks" => $nbTasks, "number_bugs" => $nbBugs, "number_messages" => $nbMessages);
			$i++;
		}

		return $arr;
	}

	public function findUserProjects($id)
	{
		$qb = $this->createQueryBuilder('p');
		
		$projects = $qb->getQuery()->getResult();

		$arr = array();
		$i = 1;

		foreach ($projects as $project) {
			$creatorId = $project->getCreatorId();

			if ($creatorId == $id)
			{
				$projectId = $project->getId();
				$projectName = $project->getName();
				$projectDescription = $project->getDescription();
				$projectLogo = $project->getLogo();
				$contactMail = $project->getContactEmail();
				$facebook = $project->getFacebook();
				$twitter = $project->getTwitter();
				$arr["Project ".$i] = array("project_id" => $projectId, "project_name" => $projectName, "project_description" => $projectDescription, "project_logo" => $projectLogo, "contact_mail" => $contactMail,
					"facebook" => $facebook, "twitter" => $twitter);
				$i++;
			}
			else
			{
				$projectUsers = $project->getUsers();
				
				foreach ($projectUsers as $projectUser) {
					$userId = $projectUser->getId();

					if ($userId == $id)
					{
						$projectId = $project->getId();
						$projectName = $project->getName();
						$projectDescription = $project->getDescription();
						$projectLogo = $project->getLogo();
						$contactMail = $project->getContactEmail();
						$facebook = $project->getFacebook();
						$twitter = $project->getTwitter();
						$arr["Project ".$i] = array("project_id" => $projectId, "project_name" => $projectName, "project_description" => $projectDescription, "project_logo" => $projectLogo, "contact_mail" => $contactMail,
							"facebook" => $facebook, "twitter" => $twitter);
						$i++;
					}
				}
			}
		}
		return $arr;
	}
}
