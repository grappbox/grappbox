<?php

namespace GrappboxBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\HttpFoundation\JsonResponse;

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
		$qb = $this->createQueryBuilder('p')->where('p.creator_user = :id')->setParameter('id', $id);

		$projects = $qb->getQuery()->getResult();

		$arr = array();
		$i = 0;
		$defaultDate = date_create("0000-00-00 00:00:00");

		if ($projects === null)
		{
			throw new NotFoundHttpException("No projects for the id ".$id);
		}

		if (count($projects) == 0)
		{
			return (Object)$arr;
		}

		foreach ($projects as $project)
		{
			$projectName = $project->getName();
			$projectUsers = $project->getUsers();
			$projectId = $project->getId();
			foreach ($projectUsers as $user) {
				$id = $user->getId();
				$firstName = $user->getFirstname();
				$lastName = $user->getLastname();
				$tasks = $user->getRessources();
				$nbOfOngoingTasks = 0;
				$nbOfTasksBegun = 0;
				$busy = false;

				foreach ($tasks as $task) {
					$task = $task->getTask();
					if ($task->getProjects()->getId() == $projectId)
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
					$arr["Person ".$i] = array("project_name" => $projectName, "user_id" => $id, "first_name" => $firstName, "last_name" => $lastName, "occupation" => "busy", "number_of_tasks_begun" => $nbOfTasksBegun, "number_of_ongoing_tasks" => $nbOfOngoingTasks);
				}
				else
				{
					$arr["Person ".$i] = array("project_name" => $projectName, "user_id" => $id, "first_name" => $firstName, "last_name" => $lastName, "occupation" => "free", "number_of_tasks_begun" => $nbOfTasksBegun, "number_of_ongoing_tasks" => $nbOfOngoingTasks);
				}
				$i++;
			}
		}

		return $arr;
	}

	public function findTeamOccupationV2($id)
	{
		$qb = $this->createQueryBuilder('p')->where('p.creator_user = :id')->setParameter('id', $id);

		$projects = $qb->getQuery()->getResult();

		$defaultDate = date_create("0000-00-00 00:00:00");

		$resp = new JsonResponse();
		$ret = array();
		$arr = array();

		if ($projects === null || count($projects) == 0)
		{
			$ret["info"] = array("return_code" => "1.2.3", "return_message" => "Dashboard - getteamoccupation - No Data Success");
			$ret["data"] = array("array" => []);
			$resp->setStatusCode(JsonResponse::HTTP_OK);
			$resp->setData($ret);

			return $resp;
		}

		foreach ($projects as $project)
		{
			$projectName = $project->getName();
			$projectUsers = $project->getUsers();
			$projectId = $project->getId();
			foreach ($projectUsers as $user) {
				$id = $user->getId();
				$firstName = $user->getFirstname();
				$lastName = $user->getLastname();
				$tasks = $user->getRessources();
				$nbOfOngoingTasks = 0;
				$nbOfTasksBegun = 0;
				$busy = false;

				foreach ($tasks as $task) {
					$task = $task->getTask();
					if ($task->getProjects()->getId() == $projectId)
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
					$arr[] = array("name" => $projectName, "users" => array("id" => $id, "firstname" => $firstName, "lastname" => $lastName), "occupation" => "busy", "number_of_tasks_begun" => $nbOfTasksBegun, "number_of_ongoing_tasks" => $nbOfOngoingTasks);
				}
				else
				{
					$arr[] = array("name" => $projectName, "users" => array("id" => $id, "firstname" => $firstName, "lastname" => $lastName), "occupation" => "free", "number_of_tasks_begun" => $nbOfTasksBegun, "number_of_ongoing_tasks" => $nbOfOngoingTasks);
				}
			}
		}

		$ret["info"] = array("return_code" => "1.2.1", "return_message" => "Dashboard - getteamoccupation - Complete success");
		$ret["data"] = array("array" => $arr);
		$resp->setStatusCode(JsonResponse::HTTP_OK);
		$resp->setData($ret);

		return $resp;
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
		if (count($projects) == 0)
		{
			return (Object)$arr;
		}


		foreach ($projects as $project) {
			$projectId = $project->getId();
			$projectName = $project->getName();
			$projectDescription = $project->getDescription();
			$phone = $project->getPhone();
			$company = $project->getCompany();
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

			$arr["Project ".$i] = array("project_id" => $projectId, "project_name" => $projectName, "project_description" => $projectDescription, "project_phone" => $phone, "project_company" => $company , "project_logo" => $projectLogo, "contact_mail" => $contactMail,
				"facebook" => $facebook, "twitter" => $twitter, "number_finished_tasks" => $nbFinishedTasks, "number_ongoing_tasks" => $nbOngoingTasks, "number_tasks" => $nbTasks, "number_bugs" => $nbBugs, "number_messages" => $nbMessages);
			$i++;
		}

		return $arr;
	}

	public function findProjectGlobalProgressV2($id)
	{
		$qb = $this->createQueryBuilder('p')->join('p.users', 'u')->where('u.id = :id')->setParameter('id', $id);

		$projects = $qb->getQuery()->getResult();

		$arr = array();
		$i = 0;
		$defaultDate = date_create("0000-00-00 00:00:00");

		$resp = new JsonResponse();
		$ret = array();

		if ($projects === null || count($projects) == 0)
		{
			$ret["info"] = array("return_code" => "1.2.3", "return_message" => "Dashboard - getProjectsGlobalProgress - No Data Success");
			$ret["data"] = array("array" => []);
			$resp->setStatusCode(JsonResponse::HTTP_OK);
			$resp->setData($ret);

			return $resp;
		}


		foreach ($projects as $project) {
			$projectId = $project->getId();
			$projectName = $project->getName();
			$projectDescription = $project->getDescription();
			$phone = $project->getPhone();
			$company = $project->getCompany();
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
				if ($bug->getDeletedAt() == $defaultDate || $bug->getDeletedAt() == null)
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

			$arr[] = array("project_id" => $projectId, "project_name" => $projectName, "project_description" => $projectDescription, "project_phone" => $phone, "project_company" => $company , "project_logo" => $projectLogo, "contact_mail" => $contactMail,
				"facebook" => $facebook, "twitter" => $twitter, "number_finished_tasks" => $nbFinishedTasks, "number_ongoing_tasks" => $nbOngoingTasks, "number_tasks" => $nbTasks, "number_bugs" => $nbBugs, "number_messages" => $nbMessages);
			$i++;
		}

		$ret["info"] = array("return_code" => "1.2.1", "return_message" => "Dashboard - getProjectsGlobalProgress - Complete success");
		$ret["data"] = array("array" => $arr);
		$resp->setStatusCode(JsonResponse::HTTP_OK);
		$resp->setData($ret);

		return $resp;
	}

	public function findUserProjects($id)
	{
		$qb = $this->createQueryBuilder('p');

		$projects = $qb->getQuery()->getResult();

		if ($projects === null)
		{
			throw new NotFoundHttpException("No projects for the user with id ".$id);
		}

		$arr = array();
		$i = 1;

		foreach ($projects as $project) {
			$creatorId = $project->getCreatorUser()->getId();

			if ($creatorId == $id)
			{
				$projectId = $project->getId();
				$projectName = $project->getName();
				$projectDescription = $project->getDescription();
				$projectLogo = $project->getLogo();
				$projectPhone = $project->getPhone();
				$projectCompany = $project->getCompany();
				$contactMail = $project->getContactEmail();
				$facebook = $project->getFacebook();
				$twitter = $project->getTwitter();
				$arr["Project ".$i] = array("id" => $projectId, "name" => $projectName, "description" => $projectDescription, "logo" => $projectLogo, "phone" => $projectPhone, "company" => $projectCompany , "contact_mail" => $contactMail,
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
						$projectPhone = $project->getPhone();
						$projectCompany = $project->getCompany();
						$contactMail = $project->getContactEmail();
						$facebook = $project->getFacebook();
						$twitter = $project->getTwitter();
						$arr["Project ".$i] = array("id" => $projectId, "name" => $projectName, "description" => $projectDescription, "logo" => $projectLogo, "phone" => $projectPhone, "company" => $projectCompany , "contact_mail" => $contactMail,
							"facebook" => $facebook, "twitter" => $twitter);
						$i++;
					}
				}
			}
		}

		if (count($arr) == 0 || count($projects))
		{
			return (Object)$arr;
		}

		return $arr;
	}

	public function findUserProjectsV2($id, $code, $part, $function)
	{
		$qb = $this->createQueryBuilder('p');
		$projects = $qb->getQuery()->getResult();

		$resp = new JsonResponse();
		$ret = array();
		$arr = array();

		if ($projects === null)
		{
			$ret["info"] = array("return_code" => "1.".$code.".3", "return_message" => $part." - ".$function." - No Data Success");
			$ret["data"] = array("array" => []);
			$resp->setStatusCode(JsonResponse::HTTP_OK);
			$resp->setData($ret);

			return $resp;
		}

		$arr = array();

		foreach ($projects as $project) {
			$creatorId = $project->getCreatorUser()->getId();

			if ($creatorId == $id)
			{
				$projectId = $project->getId();
				$projectName = $project->getName();
				$projectDescription = $project->getDescription();
				$projectLogo = $project->getLogo();
				$projectPhone = $project->getPhone();
				$projectCompany = $project->getCompany();
				$contactMail = $project->getContactEmail();
				$facebook = $project->getFacebook();
				$twitter = $project->getTwitter();
				$deletedAt = $project->getDeletedAt();
				$creator = $project->getCreatorUser();
				$creatorArr = array("id" => $creator->getId(), "firstname" => $creator->getFirstname(), "lastname" => $creator->getLastname());

				$arr[] = array("id" => $projectId, "name" => $projectName, "description" => $projectDescription, "creator" => $creatorArr, "logo" => $projectLogo,
					"phone" => $projectPhone, "company" => $projectCompany , "contact_mail" => $contactMail, "facebook" => $facebook, "twitter" => $twitter, "deleted_at" => $deletedAt);
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
						$projectPhone = $project->getPhone();
						$projectCompany = $project->getCompany();
						$contactMail = $project->getContactEmail();
						$facebook = $project->getFacebook();
						$twitter = $project->getTwitter();
						$deletedAt = $project->getDeletedAt();
						$creator = $project->getCreatorUser();
						$creatorArr = array("id" => $creator->getId(), "firstname" => $creator->getFirstname(), "lastname" => $creator->getLastname());

						$arr[] = array("id" => $projectId, "name" => $projectName, "description" => $projectDescription, "creator" => $creatorArr, "logo" => $projectLogo,
							"phone" => $projectPhone, "company" => $projectCompany , "contact_mail" => $contactMail, "facebook" => $facebook, "twitter" => $twitter, "deleted_at" => $deletedAt);
					}
				}
			}
		}

		if (count($arr) == 0 || count($projects) == 0)
		{
			$ret["info"] = array("return_code" => "1.".$code.".3", "return_message" => $part." - ".$function." projects + arr - No Data Success");
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
