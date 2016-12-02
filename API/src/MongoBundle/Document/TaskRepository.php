<?php

namespace MongoBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * TaskRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaskRepository extends DocumentRepository
{
	public function findUserAllTasks($id)
	{
		$qb = $this->createQueryBuilder()->field('users.id')->equals($id);

		$tasks = $qb->getQuery()->execute();

		if ($tasks === null)
		{
			throw new NotFoundHttpException("No tasks for the user with id ".$id);
		}

		$arr = array();
		$i = 1;

		if (count($tasks) == 0)
		{
			return (Object)$arr;
		}

		foreach ($tasks as $task) {
			$project = $task->getProjects();

			$projectId = $project->getId();
			$projectName = $project->getName();
			$taskId = $task->getId();
			$taskTitle = $task->getTitle();
			$description = $task->getDescription();
			$dueDate = $task->getDueDate();
			$startedAt = $task->getStartedAt();
			$finishedAt = $task->getFinishedAt();
			$createdAt = $task->getCreatedAt();

			$arr["Task ".$i] = array("id" => $taskId, "title" => $taskTitle, "description" => $description, "project_id" => $projectId, "project_name" => $projectName,
				"due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt);
			$i++;
		}

		return $arr;
	}

	public function findUserAllTasksV2($id, $code, $part, $function)
	{
		$qb = $this->createQueryBuilder()->field('users.id')->equals($id);
		$tasks = $qb->getQuery()->execute();

		$resp = new JsonResponse();
		$ret = array();
		$arr = array();

		if ($tasks === null || count($tasks) == 0)
		{
			$ret["info"] = array("return_code" => "1.".$code.".3", "return_message" => $part." - ".$function." - No Data Success");
			$ret["data"] = array("array" => []);
			$resp->setStatusCode(JsonResponse::HTTP_PARTIAL_CONTENT);
			$resp->setData($ret);

			return $resp;
		}

		foreach ($tasks as $task) {
			$project = $task->getProjects();

			$projectId = $project->getId();
			$projectName = $project->getName();
			$taskId = $task->getId();
			$taskTitle = $task->getTitle();
			$description = $task->getDescription();
			$dueDate = $task->getDueDate();
			$startedAt = $task->getStartedAt();
			$finishedAt = $task->getFinishedAt();
			$createdAt = $task->getCreatedAt();

			$arr[] = array("id" => $taskId, "title" => $taskTitle, "description" => $description, "project" => array("id" => $projectId, "name" => $projectName),
				"due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt);
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

	public function findUserCurrentAndNextTasks($id)
	{
		$qb = $this->createQueryBuilder()->field('users.id')->equals($id);

		$tasks = $qb->getQuery()->execute();

		if ($tasks === null)
		{
			throw new NotFoundHttpException("No tasks for the user with id ".$id);
		}

		$arr = array();
		$i = 1;
		$defaultDate = date_create("0000-00-00 00:00:00");

		if (count($tasks) == 0)
		{
			return (Object)$arr;
		}

		foreach ($tasks as $task) {
			$finishedAt = $task->getFinishedAt();

			if ($finishedAt == $defaultDate)
			{
				$project = $task->getProjects();
				$projectId = $project->getId();
				$projectName = $project->getName();
				$taskId = $task->getId();
				$taskTitle = $task->getTitle();
				$description = $task->getDescription();
				$dueDate = $task->getDueDate();
				$startedAt = $task->getStartedAt();
				$createdAt = $task->getCreatedAt();

				$arr["Task ".$i] = array("id" => $taskId, "title" => $taskTitle, "description" => $description, "project_id" => $projectId, "project_name" => $projectName,
					"due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt);
				$i++;
			}
		}

		return $arr;
	}

	public function findUserCurrentAndNextTasksV2($id, $code, $part, $function)
	{
		$qb = $this->createQueryBuilder()->field('users.id')->equals($id);
		$tasks = $qb->getQuery()->execute();

		$resp = new JsonResponse();
		$ret = array();
		$arr = array();
		$defaultDate = date_create("0000-00-00 00:00:00");

		if (count($tasks) == 0 || $tasks === null)
		{
			$ret["info"] = array("return_code" => "1.".$code.".3", "return_message" => $part." - ".$function." - No Data Success");
			$ret["data"] = array("array" => []);
			$resp->setStatusCode(JsonResponse::HTTP_PARTIAL_CONTENT);
			$resp->setData($ret);

			return $resp;
		}

		foreach ($tasks as $task) {
			$finishedAt = $task->getFinishedAt();

			if ($finishedAt == $defaultDate || $finishedAt == null)
			{
				$project = $task->getProjects();
				$projectId = $project->getId();
				$projectName = $project->getName();
				$taskId = $task->getId();
				$taskTitle = $task->getTitle();
				$description = $task->getDescription();
				$dueDate = $task->getDueDate();
				$startedAt = $task->getStartedAt();
				$createdAt = $task->getCreatedAt();

				$arr[] = array("id" => $taskId, "title" => $taskTitle, "description" => $description, "project" => array("id" => $projectId, "name" => $projectName),
					"due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt);
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
