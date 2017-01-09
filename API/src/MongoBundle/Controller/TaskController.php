<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use MongoBundle\Document\Task;
use MongoBundle\Document\Tag;
use MongoBundle\Document\Dependencies;
use MongoBundle\Document\Ressources;
use MongoBundle\Document\Contains;
use Datetime;

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
 *	@IgnoreAnnotation("apiDescription")
 *	@IgnoreAnnotation("apiIgnore")
 */
class TaskController extends RolesAndTokenVerificationController
{
	private function checkDependencies(Task $task)
	{
		$dependencies = $task->getDependence();
		foreach ($dependencies as $dep) {
			if ($dep instanceof Dependencies)
			{
				$depName = $dep->getName();
				$taskDep = $dep->getDependenceTask();

				switch ($depName) {
					case "fs":
						if ($task->getStartedAt() < $taskDep->getDueDate())
							$task->setStartedAt($taskDep->getDueDate());
						break;
					case "ss":
						if ($task->getStartedAt() < $taskDep->getStartedAt())
							$task->setStartedAt($taskDep->getStartedAt());
						break;
					case "ff":
						if ($task->getDueDate() < $taskDep->getDueDate())
							$task->setDueDate($taskDep->getDueDate());
						break;
					case "sf":
						if ($task->getDueDate() < $taskDep->getStartedAt())
							$task->setDueDate($taskDep->getStartedAt());
						break;
					default:
						break;
				}
			}
		}
		return $task;
	}

	/**
	* @-api {post} /0.3/task Create a task
	* @apiName taskCreation
	* @apiGroup Task
	* @apiDescription Create a task
	* @apiVersion 0.3.0
	*
	*/
	public function createTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('projectId', $content) || !array_key_exists('title', $content)
			|| !array_key_exists('description', $content) || !array_key_exists('due_date', $content) || !array_key_exists('started_at', $content)
			|| !array_key_exists('is_milestone', $content) || !array_key_exists('is_container', $content)))
			return $this->setBadRequest("12.1.6", "Task", "taskcreation", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.1.3", "Task", "taskcreation"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "task") < 2)
			return ($this->setNoRightsError("12.1.9", "Task", "taskcreation"));

		$task = new Task();
		$task->setTitle($content->title);
		$task->setDescription($content->description);
		$task->setProjects($project);
		$task->setCreatedAt(new \Datetime);
		$task->setCreatorUser($user);
		$task->setDueDate(new \Datetime($content->due_date));
		$task->setStartedAt(new \Datetime($content->started_at));

		if ((!$content->is_container) && $task->getStartedAt() >= $task->getDueDate())
			return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: due_date can't be prior to started_at");

		//finished at
		if (array_key_exists('finished_at', $content)) {
			$task->setFinishedAt(new \Datetime($content->finished_at));
			if ($task->getStartedAt() >= $task->getFinishedAt())
				return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: finished_at can't be prior to started_at");
		}

		//advance
		if (array_key_exists('advance', $content))
		{
			if($content->advance > 100)
				$content->advance = 100;
			else if ($content->advance < 0)
				$content->advance = 0;
			$task->setAdvance($content->advance);
		}
		else
			$task->setAdvance(0);

		$em->persist($task);

		//dependencies
		if (array_key_exists('dependencies', $content))
		{
			$dependencies = $content->dependencies;
			foreach ($dependencies as $dep) {
				$cnt = 0;
				foreach ($dependencies as $d) {
					if ($dep->id == $d->id)
						$cnt++;
				}
				foreach ($task->getDependence() as $d) {
					if ($d->getDependenceTask()->getId() == $dep->id)
						$cnt++;
				}
				if ($cnt > 1)
					return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: dependencies");
			}
			foreach ($dependencies as $dep) {
				$dependence = $em->getRepository('MongoBundle:Task')->find($dep->id);
				if ($dependence != null && $dependence->getProjects() === $task->getProjects())
				{
					$newDep = new Dependencies();
					$newDep->setName($dep->name);
					$newDep->setDependenceTask($dependence);
					$newDep->setTask($task);
					$em->persist($newDep);
					$task->addDependence($newDep);
				}
			}
			$task = $this->checkDependencies($task);
		}

		//milestone
		$task->setIsMilestone($content->is_milestone);
		if ($content->is_milestone == true)
		{
			$task->setStartedAt(null);
			$task->setFinishedAt(null);
			$task->setIsContainer(false);
			foreach ($task->getTasksContainer() as $tasks) {
				$task->removeTasksContainer($tasks);
			}
		}

		//container
		$arrTasks = array();
		$task->setIsContainer($content->is_container);
		if ($content->is_container == true)
		{
			$task->setIsMilestone(false);
			if (array_key_exists('tasksAdd', $content) && count($content->tasksAdd) > 0)
			{
				foreach ($content->tasksAdd as $ta) {
					$taskAdd = $em->getRepository("MongoBundle:Task")->find($ta);
					if ($taskAdd instanceof Task && $taskAdd->getIsMilestone() === false && $taskAdd->getProjects() === $task->getProjects())
					{
						$isInArray = false;
						foreach ($task->getTasksContainer() as $t) {
							if ($t->getId() == $taskAdd->getId())
								$isInArray = true;
						}
						if ($isInArray == false)
						{
							$task->addTasksContainer($taskAdd);
							$taskAdd->setContainer($task);
						}
					}
				}
			}

			if (array_key_exists('tasksRemove', $content) && count($content->tasksRemove))
			{
				foreach ($content->tasksRemove as $ta) {
					$taskRemove = $em->getRepository("MongoBundle:Task")->find($ta);
					if ($taskRemove instanceof Task)
					{
						$isInArray = false;
						foreach ($task->getTasksContainer() as $t) {
							if ($t->getId() == $taskRemove->getId())
								$isInArray = true;
						}
						if ($isInArray == true)
						{
							$task->removeTasksContainer($taskRemove);
							$taskAdd->setContainer(null);
						}
					}
				}
			}

			$tasksAdvance = 0;
			foreach ($task->getTasksContainer() as $t) {
				$arrTasks[] = array("id" => $t->getId(), "title" => $t->getTitle());
				$tasksAdvance += $t->getAdvance();

				if ($task->getStartedAt() != null)
				{
					$date = $task->getStartedAt();
					$diff = date_diff($date, $t->getStartedAt());
					if ($diff->format('R') == '-')
						$task->setStartedAt($t->getStartedAt());
				}
				else
					$task->setStartedAt($t->getStartedAt());
				if ($task->getDueDate() != null)
				{
					$date = $task->getDueDate();
					$diff = date_diff($date, $t->getDueDate());
					if ($diff->format('R') == '+')
						$task->setDueDate($t->getDueDate());
				}
				else
					$task->setDueDate($t->getDueDate());
				$task->setFinishedAt(null);
			}

			if (array_key_exists('color', $content))
				$task->setColor($content->color);

			if (array_key_exists('timezone', $content->due_date) && $content->due_date->timezone != "")
				$dueDate = new \Datetime($content->due_date->date, new \DatetimeZone($content->due_date->timezone));
			else
				$dueDate = new \Datetime($content->due_date->date);
			$task->setDueDate($dueDate);

			if (count($task->getTasksContainer()) > 0)
				$task->setAdvance($tasksAdvance / count($task->getTasksContainer()));
		}

		$em->flush();

		//usersAdd
		if (array_key_exists('usersAdd', $content))
		{
			if ($task->getIsMilestone() == true)
				return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: You can't add someone on a milestone");

			foreach ($content->usersAdd as $userAdd) {
				if (!array_key_exists('percent', $userAdd) || !array_key_exists('id', $userAdd))
					return $this->setBadRequest("12.1.6", "Task", "taskcreation", "Missing Parameter in usersAdd");

				$userToAdd = $em->getRepository('MongoBundle:User')->find($userAdd->id);
				if ($userToAdd !== null) {
					$users = $task->getRessources();
					$isInDB = false;
					foreach ($users as $res) {
						$us = $res->getUser();
						if ($us === $userToAdd)
							$isInDB = true;
					}

					if ($isInDB == false) {
						$resource = new Ressources();
						$resource->setResource($userAdd->percent);
						$resource->setTask($task);
						$resource->setUser($userToAdd);

						$em->persist($resource);
						$task->addRessource($resource);
					}
				}
			}
		}

		//usersRemove
		if (array_key_exists('usersRemove', $content))
		{
			foreach ($content->usersRemove as $userId) {
				$userToRemove = $em->getRepository('MongoBundle:User')->find($userId);

				if ($userToRemove !== null) {

					$resources = $task->getRessources();
					$isAssign = false;
					$resToRemove;
					foreach ($resources as $res) {
						if ($res->getUser() === $userToRemove)
						{
							$isAssign = true;
							$resToRemove = $res;
						}
					}

					if ($isAssign !== false) {
						$task->removeRessource($resToRemove);
						$em->remove($resToRemove);
					}
				}
			}
		}

		//add tag to task
		if (array_key_exists('tagsAdd', $content)) {
			foreach ($content->tagsAdd as $tag) {
				$tagToAdd = $em->getRepository('MongoBundle:Tag')->find($tag);
				if ($tagToAdd !== null) {
					$tags = $task->getTags();
					$isInDB = false;
					foreach ($tags as $tag) {
						if ($tag === $tagToAdd)
							$isInDB = true;
					}
					if ($isInDB == false)
						$task->addTag($tagToAdd);
				}
			}
		}

		//remove tag to task
		if (array_key_exists('tagsRemove', $content)) {
			foreach ($content->tagsRemove as $tagRemove) {
				$tagToRemove = $em->getRepository('MongoBundle:Tag')->find($tagRemove);
				$tags = $task->getTags();
				$isAssign = false;
				foreach ($tags as $tag) {
					if ($tag === $tagToRemove)
						$isAssign = true;
				}
				if ($isAssign == true)
					$task->removeTag($tagToRemove);
			}
		}

		$em->flush();


		//notifs
		// $mdata['mtitle'] = "new task";
		// $mdata['mdesc'] = json_encode($task->objectToArray(array()));
		// $wdata['type'] = "new task";
		// $wdata['targetId'] = $task->getId();
		// $wdata['message'] = json_encode($task->objectToArray(array()));
		// $userNotif = array();
		// if ($task->getProjects() != null) {
		// 	foreach ($task->getProjects()->getUsers() as $key => $value) {
		// 		$userNotif[] = $value->getId();
		// 	}
		// }
		// else {
		// 	foreach ($task->getRessources() as $key => $value) {
		// 		$userNotif[] = $value->getUser()->getId();
		// 	}
		// }
		// if (count($userNotif) > 0)
		// 	$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($task->getProjects(), 'UserTasksAdvancement');
		$this->get('mongo_service_stat')->updateStat($task->getProjects(), 'UserWorkingCharge');
		$this->get('mongo_service_stat')->updateStat($task->getProjects(), 'TasksRepartition');

		return $this->setCreated("1.12.1", "Task", "taskcreation", "Complete Success", $task->objectToArray(array()));
	}

	/**
	* @-api {put} /0.3/task/:id Update a task
	* @apiName taskUpdate
	* @apiGroup Task
	* @apiDescription Update a given task
	* @apiVersion 0.3.0
	*
	*/
	public function updateTaskAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null)
			return $this->setBadRequest("12.2.6", "Task", "taskupdate", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.2.3", "Task", "taskupdate"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$task = $em->getRepository('MongoBundle:Task')->find($id);

		if ($task === null || $task->getDeletedAt() != null)
			return $this->setBadRequest("12.2.4", "Task", "taskupdate", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.2.9", "Task", "taskupdate"));

		$taskDep = $task->getTaskDepended();
		$taskModified = array();

		//title
		if (array_key_exists('title', $content))
			$task->setTitle($content->title);

		//description
		if (array_key_exists('description', $content))
			$task->setDescription($content->description);

		$task = $this->checkDependencies($task);

		//due_date
		if (array_key_exists('due_date', $content))
		{
			$dueDate = $task->getDueDate();
			$newDate = new \Datetime($content->due_date);
			if ((!$task->getIsContainer()) && $task->getStartedAt() >= $newDate)
				return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: due_date can't be prior to started_at");

			if ($dueDate != null)
				$diff = date_diff($dueDate, $newDate);

			foreach ($taskDep as $td) {
				if ($td->getName() == "fs")
				{
					$date = $td->getTask()->getStartedAt();
					$end = $td->getTask()->getDueDate();
					date_add($date, $diff);
					date_add($end, $diff);
					$td->getTask()->setStartedAt($date);
					$td->getTask()->setDueDate($end);
					$taskModified[] = array("id" => $td->getTask()->getId(), "title" => $td->getTask()->getTitle(), "started_at" => $td->getTask()->getStartedAt(), "due_date" => $td->getTask()->getDueDate());
				}
				else if ($td->getName() == "ff")
				{
					$date = $td->getTask()->getStartedAt();
					$end = $td->getTask()->getDueDate();
					date_add($date, $diff);
					date_add($end, $diff);
					$td->getTask()->setDueDate($end);
					$td->getTask()->setStartedAt($date);
					$taskModified[] = array("id" => $td->getTask()->getId(), "title" => $td->getTask()->getTitle(), "started_at" => $td->getTask()->getStartedAt(), "due_date" => $td->getTask()->getDueDate());
				}
			}
			$task->setDueDate($newDate);
		}

		//started at
		if (array_key_exists('started_at', $content))
		{
			$startedAt = $task->getStartedAt();
			$newDate = new \Datetime($content->started_at);
			if ((!$task->getIsContainer()) && $newDate >= $task->getDueDate())
				return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: started_at can't be after due_date");

			if ($startedAt != null)
				$diff = date_diff($startedAt, $newDate);

			foreach ($taskDep as $td) {
				if ($td->getName() == "ss")
				{
					$date = $td->getTask()->getStartedAt();
					$end = $td->getTask()->getDueDate();
					date_add($date, $diff);
					date_add($end, $diff);
					$td->getTask()->setDueDate($end);
					$td->getTask()->setStartedAt($date);
					$taskModified[] = array("id" => $td->getTask()->getId(), "title" => $td->getTask()->getTitle(), "started_at" => $td->getTask()->getStartedAt()->format('Y-m-d H:i:s'), "due_date" => $td->getTask()->getDueDate()->format('Y-m-d H:i:s'));
				}
				else if ($td->getName() == "sf")
				{
					$date = $td->getTask()->getStartedAt();
					$end = $td->getTask()->getDueDate();
					date_add($date, $diff);
					date_add($end, $diff);
					$td->getTask()->setDueDate($end);
					$td->getTask()->setStartedAt($date);
					$taskModified[] = array("id" => $td->getTask()->getId(), "title" => $td->getTask()->getTitle(), "started_at" => $td->getTask()->getStartedAt()->format('Y-m-d H:i:s'), "due_date" => $td->getTask()->getDueDate()->format('Y-m-d H:i:s'));
				}
			}
			$task->setStartedAt($newDate);
		}

		//finished at
		if (array_key_exists('finished_at', $content))
		{
			$dueDate = $task->getDueDate();
			$newDate = new \Datetime($content->finished_at);
			if ($task->getStartedAt() >= $newDate)
				return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: finished_at can't be prior to started_at");

			if ($dueDate != null)
				$diff = date_diff($dueDate, $newDate);

			foreach ($taskDep as $td) {
				if ($td->getName() == "fs")
				{
					$date = $td->getTask()->getStartedAt();
					$end = $td->getTask()->getDueDate();
					date_add($date, $diff);
					date_add($end, $diff);
					$td->getTask()->setStartedAt($date);
					$td->getTask()->setDueDate($end);
					$taskModified[] = array("id" => $td->getTask()->getId(), "title" => $td->getTask()->getTitle(), "started_at" => $td->getTask()->getStartedAt()->format('Y-m-d H:i:s'), "due_date" => $td->getTask()->getDueDate()->format('Y-m-d H:i:s'));
				}
				else if ($td->getName() == "ff")
				{
					$date = $td->getTask()->getStartedAt();
					$end = $td->getTask()->getDueDate();
					date_add($date, $diff);
					date_add($end, $diff);
					$td->getTask()->setDueDate($end);
					$td->getTask()->setStartedAt($date);
					$taskModified[] = array("id" => $td->getTask()->getId(), "title" => $td->getTask()->getTitle(), "started_at" => $td->getTask()->getStartedAt()->format('Y-m-d H:i:s'), "due_date" => $td->getTask()->getDueDate()->format('Y-m-d H:i:s'));
				}
			}

			$task->setFinishedAt($newDate);
			$task->setDueDate($newDate);
		}

		//advance
		if (array_key_exists('advance', $content))
		{
			if($content->advance > 100)
				$content->advance = 100;
			else if ($content->advance < 0)
				$content->advance = 0;
			$task->setAdvance($content->advance);
		}

		//dependencies
		if (array_key_exists('dependencies', $content))
		{
			$dependencies = $content->dependencies;
			foreach ($dependencies as $dep) {
				$cnt = 0;
				foreach ($dependencies as $d) {
					if ($dep->id == $d->id)
						$cnt++;
				}
				foreach ($task->getDependence() as $d) {
					if ($d->getDependenceTask()->getId() == $dep->id)
						$cnt++;
				}
				if ($cnt > 1)
					return $this->setBadRequest("12.2.4", "Task", "taskcreation", "Bad Parameter: dependencies");
			}
			foreach ($dependencies as $dep) {
				$dependence = $em->getRepository('MongoBundle:Task')->find($dep->id);
				if ($dependence != null && $dependence->getProjects() === $task->getProjects())
				{
					$newDep = new Dependencies();
					$newDep->setName($dep->name);
					$newDep->setDependenceTask($dependence);
					$newDep->setTask($task);
					$em->persist($newDep);
					$task->addDependence($newDep);
				}
			}
			$this->checkDependencies($task);
		}

		//dependencies update
		if (array_key_exists('dependenciesUpdate', $content)) {
			foreach ($content->dependenciesUpdate as $up) {
				$dependencies = $task->getDependence();
				foreach ($dependencies as $dep) {
					if ($dep->getName() == $up->oldName && $dep->getDependenceTask()->getId() == $up->id) {
						$dep->setName($up->newName);
					}
				}
			}
			$task = $this->checkDependencies($task);
		}

		//remove dependencies
		if (array_key_exists('dependenciesRemove', $content))
		{
			foreach ($content->dependenciesRemove as $depId) {
				foreach ($task->getDependence() as $dep) {
					if ($dep->getDependenceTask()->getId() == $depId)
					{
						$task->removeDependence($dep);
						$em->remove($dep);
					}
				}
			}
		}

		//milestone
		if ($task->getIsMilestone() == true)
		{
			$task->setStartedAt(null);
			$task->setFinishedAt(null);
		}

		//container
		$arrTasks = array();
		if (array_key_exists('is_container', $content))
		{
			$task->setIsContainer($content->is_container);
		}
		if ($task->getIsContainer() == true)
		{
			$task->setIsMilestone(false);
			if (array_key_exists('tasksAdd', $content) && count($content->tasksAdd) > 0)
			{
				foreach ($content->tasksAdd as $ta) {
					$taskAdd = $em->getRepository("MongoBundle:Task")->find($ta);
					if ($taskAdd instanceof Task && $taskAdd->getIsMilestone() === false && $taskAdd->getProjects() === $task->getProjects())
					{
						$isInArray = false;
						foreach ($task->getTasksContainer() as $t) {
							if ($t->getId() == $taskAdd->getId())
								$isInArray = true;
						}
						if ($isInArray == false)
						{
							$task->addTasksContainer($taskAdd);
							$taskAdd->setContainer($task);
						}
					}
				}
			}

			if (array_key_exists('tasksRemove', $content) && count($content->tasksRemove))
			{
				foreach ($content->tasksRemove as $ta) {
					$taskRemove = $em->getRepository("MongoBundle:Task")->find($ta);
					if ($taskRemove instanceof Task)
					{
						$isInArray = false;
						foreach ($task->getTasksContainer() as $t) {
							if ($t->getId() == $taskRemove->getId())
								$isInArray = true;
						}
						if ($isInArray == true)
						{
							$task->removeTasksContainer($taskRemove);
							$taskRemove->setContainer(null);
						}
					}
				}
			}

			$tasksAdvance = 0;
			foreach ($task->getTasksContainer() as $t) {
				$arrTasks[] = array("id" => $t->getId(), "title" => $t->getTitle());
				$tasksAdvance += $t->getAdvance();

				if ($task->getStartedAt() != null)
				{
					$date = $task->getStartedAt();
					$diff = date_diff($date, $t->getStartedAt());
					if ($diff->format('R') == '-')
						$task->setStartedAt($t->getStartedAt());
				}
				else
					$task->setStartedAt($t->getStartedAt());
				if ($task->getDueDate() != null)
				{
					$date = $task->getDueDate();
					$diff = date_diff($date, $t->getDueDate());
					if ($diff->format('R') == '+')
						$task->setDueDate($t->getDueDate());
				}
				else
					$task->setDueDate($t->getDueDate());
				$task->setFinishedAt(null);
			}
			if (count($task->getTasksContainer()) > 0)
				$task->setAdvance($tasksAdvance / count($task->getTasksContainer()));
		}

		$em->flush();

		//usersAdd
		if (array_key_exists('usersAdd', $content))
		{
			if ($task->getIsMilestone() == true)
				return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: You can't add someone on a milestone");

			foreach ($content->usersAdd as $userAdd) {
				if (!array_key_exists('percent', $userAdd) || !array_key_exists('id', $userAdd))
					return $this->setBadRequest("12.1.6", "Task", "taskcreation", "Missing Parameter in usersAdd");

				$userToAdd = $em->getRepository('MongoBundle:User')->find($userAdd->id);
				if ($userToAdd !== null) {
					$users = $task->getRessources();
					$isInDB = false;
					foreach ($users as $res) {
						$us = $res->getUser();
						if ($us === $userToAdd)
							$isInDB = true;
					}

					if ($isInDB == false) {
						$resource = new Ressources();
						$resource->setResource($userAdd->percent);
						$resource->setTask($task);
						$resource->setUser($userToAdd);

						$em->persist($resource);
						$task->addRessource($resource);
					}
				}
			}
		}

		//user update
		if (array_key_exists('usersUpdate', $content)) {
			foreach ($content->usersUpdate as $usUp) {
				$users = $task->getRessources();
				foreach ($users as $us) {
					if ($us->getUser()->getId() == $usUp->id) {
						$us->setResource($usUp->percent);
					}
				}
			}
		}

		//usersRemove
		if (array_key_exists('usersRemove', $content))
		{
			foreach ($content->usersRemove as $userId) {
				$userToRemove = $em->getRepository('MongoBundle:User')->find($userId);

				if ($userToRemove !== null) {

					$resources = $task->getRessources();
					$isAssign = false;
					$resToRemove;
					foreach ($resources as $res) {
						if ($res->getUser() === $userToRemove)
						{
							$isAssign = true;
							$resToRemove = $res;
						}
					}

					if ($isAssign !== false) {
						$task->removeRessource($resToRemove);
						$em->remove($resToRemove);
					}
				}
			}
		}

		//add tag to task
		if (array_key_exists('tagsAdd', $content)) {
			foreach ($content->tagsAdd as $tag) {
				$tagToAdd = $em->getRepository('MongoBundle:Tag')->find($tag);
				if ($tagToAdd !== null) {
					$tags = $task->getTags();
					$isInDB = false;
					foreach ($tags as $tag) {
						if ($tag === $tagToAdd)
							$isInDB = true;
					}
					if ($isInDB == false)
						$task->addTag($tagToAdd);
				}
			}
		}

		//remove tag to task
		if (array_key_exists('tagsRemove', $content)) {
			foreach ($content->tagsRemove as $tagRemove) {
				$tagToRemove = $em->getRepository('MongoBundle:Tag')->find($tagRemove);
				$tags = $task->getTags();
				$isAssign = false;
				foreach ($tags as $tag) {
					if ($tag === $tagToRemove)
						$isAssign = true;
				}
				if ($isAssign == true)
					$task->removeTag($tagToRemove);
			}
		}

		$em->flush();

		$creator_id = $task->getCreatorUser()->getId();
		$userNotif[] = $creator_id;

		$userArray = array();

		foreach ($task->getRessources() as $res) {
			$uid = $res->getUser()->getId();
			if ($uid != $creator_id)
				$userNotif[] = $uid;
		}

		// Notifications
		// if (count($userNotif) != 0)
		// {
		// 	$mdata['mtitle'] = "update task";
		// 	$mdata['mdesc'] = json_encode($task->objectToArray($taskModified));
		// 	$wdata['type'] = "update task";
		// 	$wdata['targetId'] = $task->getId();
		// 	$wdata['message'] = json_encode($task->objectToArray($taskModified));
		// 	$userNotif = array();
		// 	if ($task->getProjects() != null) {
		// 		foreach ($task->getProjects()->getUsers() as $key => $value) {
		// 			$userNotif[] = $value->getId();
		// 		}
		// 	}
		// 	else {
		// 		foreach ($task->getRessources() as $key => $value) {
		// 			$userNotif[] = $value->getUser()->getId();
		// 		}
		// 	}
		// 	if (count($userNotif) > 0)
		// 		$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);
		// }

		return $this->setSuccess("1.12.1", "Task", "taskupdate", "Complete Success", $task->objectToArray($taskModified));
	}

	/**
	* @-api {get} /0.3/task/:taskId Get a task informations
	* @apiName taskInformations
	* @apiGroup Task
	* @apiDescription Get the informations of the given task
	* @apiVersion 0.3.0
	*
	*/
	public function getTaskInfosAction(Request $request, $taskId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.3.3", "Task", "taskinformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$task = $em->getRepository('MongoBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.3.4", "Task", "taskinformations", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.3.9", "Task", "taskinformations"));

		return $this->setSuccess("1.12.1", "Task", "taskinformations", "Complete Success", $task->objectToArray(array()));
	}

	/**
	* @-api {put} /0.3/task/archive/:id Archive a task
	* @apiName archiveTask
	* @apiGroup Task
	* @apiDescription Archive the given task
	* @apiVersion 0.3.0
	*
	*/
	public function archiveTaskAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.4.3", "Task", "archivetask"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$task = $em->getRepository('MongoBundle:Task')->find($id);
		if ($task === null)
			return $this->setBadRequest("12.4.4", "Task", "archivetask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.4.9", "Task", "archivetask"));

		$task->setDeletedAt(new \Datetime);
		$em->flush();

		//notifs
		// $mdata['mtitle'] = "archive task";
		// $mdata['mdesc'] = json_encode($task->objectToArray(array()));
		// $wdata['type'] = "archive task";
		// $wdata['targetId'] = $task->getId();
		// $wdata['message'] = json_encode($task->objectToArray(array()));
		// $userNotif = array();
		// if ($task->getProjects() != null) {
		// 	foreach ($task->getProjects()->getUsers() as $key => $value) {
		// 		$userNotif[] = $value->getId();
		// 	}
		// }
		// else {
		// 	foreach ($task->getRessources() as $key => $value) {
		// 		$userNotif[] = $value->getUser()->getId();
		// 	}
		// }
		// if (count($userNotif) > 0)
		// 	$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($projectId, 'UserTasksAdvancement');
		$this->get('mongo_service_stat')->updateStat($projectId, 'UserWorkingCharge');
		$this->get('mongo_service_stat')->updateStat($projectId, 'TasksRepartition');

		return $this->setSuccess("1.12.1", "Task", "archivetask", "Complete Success", array("id" => $task->getId()));
	}

	/**
	* @-api {delete} /0.3/task/:taskId Delete a task
	* @apiName taskDelete
	* @apiGroup Task
	* @apiDescription Delete definitely the given task
	* @apiVersion 0.3.0
	*
	*/
	public function deleteTaskAction(Request $request, $taskId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.5.3", "Task", "taskdelete"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$task = $em->getRepository('MongoBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.5.4", "Task", "taskdelete", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.5.9", "Task", "taskdelete"));

		//notifs
		// $mdata['mtitle'] = "delete task";
		// $mdata['mdesc'] = json_encode($task->objectToArray(array()));
		// $wdata['type'] = "delete task";
		// $wdata['targetId'] = $task->getId();
		// $wdata['message'] = json_encode($task->objectToArray(array()));
		// $userNotif = array();
		// if ($task->getProjects() != null) {
		// 	foreach ($task->getProjects()->getUsers() as $key => $value) {
		// 		$userNotif[] = $value->getId();
		// 	}
		// }
		// else {
		// 	foreach ($task->getRessources() as $key => $value) {
		// 		$userNotif[] = $value->getUser()->getId();
		// 	}
		// }
		// if (count($userNotif) > 0)
		// 	$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($task);
		$em->flush();

		$this->get('mongo_service_stat')->updateStat($projectId, 'UserTasksAdvancement');
		$this->get('mongo_service_stat')->updateStat($projectId, 'UserWorkingCharge');
		$this->get('mongo_service_stat')->updateStat($projectId, 'TasksRepartition');

		$response["info"]["return_code"] = "1.12.1";
		$response["info"]["return_message"] = "Task - taskdelete - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {post} /0.3/tasks/tag Create a tag
	* @apiName tagCreation
	* @apiGroup Task
	* @apiDescription Create a tag
	* @apiVersion 0.3.0
	*
	*/
	public function tagCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('name', $content) || !array_key_exists('projectId', $content)
		 || !array_key_exists('color', $content)))
			return $this->setBadRequest("12.8.6", "Task", "tagcreation", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.8.3", "Task", "tagcreation"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("12.8.4", "Task", "tagcreation", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "task") < 2)
			return ($this->setNoRightsError("12.8.9", "Task", "tagcreation"));

		$tag = new Tag();
		$tag->setName($content->name);
		$tag->setProject($project);
		$tag->setColor($content->color);

		$em->persist($tag);
		$em->flush();

		$tagArray = $tag->objectToArray();
		$tagArray['projectId'] = $tag->getProject()->getId();

		//notifs
		// $mdata['mtitle'] = "new tag task";
		// $mdata['mdesc'] = json_encode($tagArray);
		// $wdata['type'] = "new tag task";
		// $wdata['targetId'] = $tag->getId();
		// $wdata['message'] = json_encode($tagArray);
		// $userNotif = array();
		// foreach ($project->getUsers() as $key => $value) {
		// 	$userNotif[] = $value->getId();
		// }
		// if (count($userNotif) > 0)
		// 	$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($content->projectId, 'BugsTagsRepartition');

		return $this->setCreated("1.12.1", "Task", "tagcreation", "Complete Success", $tag->objectToArray());
	}

	/**
	* @-api {put} /0.3/tasks/tag/:id Update a tag
	* @apiName tagUpdate
	* @apiGroup Task
	* @apiDescription Update a given task
	* @apiVersion 0.3.0
	*
	*/
	public function tagUpdateAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('name', $content) || !array_key_exists('color', $content)))
			return $this->setBadRequest("12.9.6", "Task", "tagupdate", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.9.3", "Task", "tagupdate"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:Tag')->find($id);
		if ($tag === null)
			return $this->setBadRequest("12.9.4", "Task", "tagupdate", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.9.9", "Task", "tagupdate"));

		$tag->setName($content->name);
		$tag->setColor($content->color);
		$em->flush();

		$tagArray = $tag->objectToArray();
		$tagArray['projectId'] = $tag->getProject()->getId();

		//notifs
		// $mdata['mtitle'] = "update tag task";
		// $mdata['mdesc'] = json_encode($tagArray);
		// $wdata['type'] = "update tag task";
		// $wdata['targetId'] = $tag->getId();
		// $wdata['message'] = json_encode($tagArray);
		// $userNotif = array();
		// foreach ($tag->getProject()->getUsers() as $key => $value) {
		// 	$userNotif[] = $value->getId();
		// }
		// if (count($userNotif) > 0)
		// 	$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		return $this->setSuccess("1.12.1", "Task", "tagupdate", "Complete Success", $tag->objectToArray());
	}

	/**
	* @-api {get} /0.3/tasks/tag/:tagId Get a tag informations
	* @apiName tagInformations
	* @apiGroup Task
	* @apiDescription Get the informations of the given tag
	* @apiVersion 0.3.0
	*
	*/
	public function getTagInfosAction(Request $request, $tagId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.10.3", "Task", "taginformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.10.4", "Task", "taginformations", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.10.9", "Task", "taginformations"));

		return $this->setSuccess("1.12.1", "Task", "taginformations", "Complete Success", array("id" => $tag->getId(), "name" => $tag->getName()));
	}

	/**
	* @-api {delete} /0.3/tasks/tag/:tagId Delete a tag
	* @apiName deleteTag
	* @apiGroup Task
	* @apiDescription Delete the given tag
	* @apiVersion 0.3.0
	*
	*/
	public function deleteTagAction(Request $request, $tagId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.11.3", "Task", "deletetag"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.11.4", "Task", "deletetag", "Bad Parameter: tagId");

		if ($this->checkRoles($user, $tag->getProject()->getId(), "task") < 2)
			return ($this->setNoRightsError("12.11.9", "Task", "deletetag"));

		$tagArray = $tag->objectToArray();
		$tagArray['projectId'] = $tag->getProject()->getId();

		//notifs
		// $mdata['mtitle'] = "delete tag task";
		// $mdata['mdesc'] = json_encode($tagArray);
		// $wdata['type'] = "delete tag task";
		// $wdata['targetId'] = $tag->getId();
		// $wdata['message'] = json_encode($tagArray);
		// $userNotif = array();
		// foreach ($tag->getProject()->getUsers() as $key => $value) {
		// 	$userNotif[] = $value->getId();
		// }
		// if (count($userNotif) > 0)
		// 	$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($tag);
		$em->flush();

		$this->get('mongo_service_stat')->updateStat($tag->getProject()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.12.1";
		$response["info"]["return_message"] = "Task - deletetag - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {get} /0.3/tasks/project/:projectId Get all the tasks for a project
	* @apiName getProjectTasks
	* @apiGroup Task
	* @apiDescription Get all the tasks for a given project
	* @apiVersion 0.3.0
	*
	*/
	public function getProjectTasksAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.14.3", "Task", "getprojecttasks"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("12.14.4", "Task", "getprojecttasks", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.14.9", "Task", "getprojecttasks"));

		$repository = $em->getRepository('MongoBundle:Task');
		$qb = $repository->createQueryBuilder()->field('projects.id')->equals($projectId)->getQuery();
		$tasks = $qb->execute();
		if ($tasks === null)
			return $this->setBadRequest("12.14.4", "Task", "getprojecttasks", "Bad Parameter: projectId");

		if (count($tasks) == 0)
			return $this->setNoDataSuccess("1.12.3", "Task", "getprojecttasks");

		$arr = array();
		foreach ($tasks as $task) {
			$arr[] = $task->objectToArray(array());
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.12.3", "Task", "getprojecttasks");

		return $this->setSuccess("1.12.1", "Task", "getprojecttasks", "Complete Success", array("array" => $arr));
	}

	/**
	* @-api {get} /0.3/tasks/tags/project/:projectId Get all the tags for a project
	* @apiName getProjectTags
	* @apiGroup Task
	* @apiDescription Get all the tags for a given project
	* @apiVersion 0.3.0
	*
	*/
	public function getProjectTagsAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.15.3", "Task", "getprojecttags"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("12.15.4", "Task", "getprojecttags", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.15.9", "Task", "getprojecttags"));

		$repository = $em->getRepository('MongoBundle:Tag');
		$qb = $repository->createQueryBuilder()->field('project.id')->equals($projectId)->getQuery();
		$tags = $qb->execute();
		if ($tags === null)
			return $this->setBadRequest("12.15.4", "Task", "getprojecttags", "Bad Parameter: projectId");

		if (count($tags) == 0)
			return $this->setNoDataSuccess("1.12.3", "Task", "getprojecttags");

		$arr = array();
		foreach ($tags as $t) {
			$arr[] = $t->objectToArray();
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.12.3", "Task", "getprojecttags");

		return $this->setSuccess("1.12.1", "Task", "getprojecttags", "Complete Success", array("array" => $arr));
	}
}
