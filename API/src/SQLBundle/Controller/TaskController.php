<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use SQLBundle\Entity\Task;
use SQLBundle\Entity\Tag;
use SQLBundle\Entity\Dependencies;
use SQLBundle\Entity\Ressources;
use SQLBundle\Entity\Contains;
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
 *  @IgnoreAnnotation("apiHeader")
 *  @IgnoreAnnotation("apiHeaderExample")
 */
class TaskController extends RolesAndTokenVerificationController
{
	private function checkDependencies($task)
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
	* @api {post} /0.3/task Create a task
	* @apiName taskCreation
	* @apiGroup Task
	* @apiDescription Create a task
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projecId Id of the project
	* @apiParam {String} title Title of the task
	* @apiParam {String} description Description of the task
	* @apiParam {string} due_date Due date of the task
	* @apiParam {string} started_at Date of start of the task
	* @apiParam {Boolean} is_milestone If true, set the task to a milestone. If is_container is true, then set is_containre to false.
	* @apiParam {Boolean} is_container If true, set the task as a container. If is_milestone is true, then set is_milestone to false.
	* @apiParam {Int[]} [tasksAdd] Array of tasks id to add. To set all the tasks contains in the container (is_container must be true for that)
	* @apiParam {Int[]} [tasksRemove] Array of tasks id to remove. To set all the tasks contains in the container (is_container must be true for that)
	* @apiParam {Object[]} [dependencies] Array of infos on the dependencies
	* @apiParam {String} dependencies.name name of the dependence, it should be: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiParam {Number} dependencies.id Id of the task the new task dependes on
	* @apiParam {string} [finished_at] Date of finish of the task
	* @apiParam {Number} [advance] Advance percent of the task
	* @apiParam {Object[]} [usersAdd] Array of users to add
	* @apiParam {Int} usersAdd.id Id of the user to add
	* @apiParam {Int} usersAdd.percent Percent of the user
	* @apiParam {Int[]} [usersRemove] Array of users id to remove
	* @apiParam {Int[]} [tagsAdd] Array of tags id to add
	* @apiParam {Int[]} [tagsRemove] Array of tags id to remove
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
	*			"projectId": 2,
	*			"title": "Update server",
	*			"description": "update the server apache to a newer version",
	*			"due_date":  "2015-10-15 11:00:00",
	*			"started_at": "2015-10-15 11:00:00",
	*			"is_milstone": false,
	*			"is_container": true,
	*			"tasksAdd": [1, 50, 13],
	*			"tasksRemove": [6],
	*			"dependencies":
	*			[
	*				{
	*					"name": "fs",
	*					"id": 1
	*				},
	*				{
	*					"name": "ss",
	*					"id": 3
	*				}
	*			],
	*			"finished_at": "2015-10-15 11:00:00",
	*			"advance": 20,
	*			"usersAdd": [
	*				{
	*					"id": 1,
	*					"percent": 50
	*				 },
	*				{
	*					"id": 3,
	*					"percent": 100
	*				}
	*			],
	*			"usersRemove": [6],
	*			"tagsAdd": [1, 3, 5],
	*			"tagsRemove": [9]
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	*	{
	*		"data": {
	*			"projectId": 2,
	*			"title": "Update server",
	*			"description": "update the server apache to a newer version",
	*			"due_date": "2015-10-15 11:00:00",
	*			"started_at": "2015-10-15 11:00:00",
	*			"is_milestone": true,
	*			"is_container": false
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"projectId": 2,
	*			"title": "Update server",
	*			"description": "update the server apache to a newer version",
	*			"due_date": "2015-10-15 11:00:00",
	*			"started_at": "2015-10-15 11:00:00",
	*			"is_milestone": false,
	*			"is_container": true,
	*			"tasksAdd": [1, 50, 13],
	*			"usersAdd": {
	*				{
	*					"id": 1,
	*					"percent": 50
	*				 },
	*				{
	*					"id": 3,
	*					"percent": 100
	*				}
	*			}
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
	* @apiSuccess {String} description Description of the task
	* @apiSuccess {Number} projectId Id of the project
	* @apiSuccess {string} due_date Due date of the task
	* @apiSuccess {string} started_at Date of start of the task
	* @apiSuccess {string} finished_at Date of finish of the task
	* @apiSuccess {string} created_at Date of creation of the task
	* @apiSuccess {Boolean} is_milestone Is the task a milestone
	* @apiSuccess {Boolean} is_container Is the task a container
	* @apiSuccess {Object[]} tasks Array of tasks for the container
	* @apiSuccess {Number} tasks.id Id of the task
	* @apiSuccess {String} task.title Title of the task
	* @apiSuccess {string} tasks.started_at Date of start of the task
	* @apiSuccess {string} tasks.due_date Due date of the task
	* @apiSuccess {Number} advance Advance percent of the task
	* @apiSuccess {Object[]} creator Creator informations
	* @apiSuccess {Number} creator.id Id of the creator
	* @apiSuccess {String} creator.firstname Frist name of the creator
	* @apiSuccess {String} creator.lastname Last name of the creator
	* @apiSuccess {Object[]} users Array of users assigned to the task
	* @apiSuccess {Number} users.id Id of the user assigned
	* @apiSuccess {String} users.firstname Frist name of the user assigned
	* @apiSuccess {String} users.lastname Last name of the user assigned
	* @apiSuccess {Number} users.percent Percent of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
	* @apiSuccess {Number} tags.id Id of the tag
	* @apiSuccess {String} tags.name Name of the tag
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} dependencies Array of infos on the dependencies
	* @apiSuccess {Number} dependencies.id Id of the task the dependence
	* @apiSuccess {String} dependencies.name Name of the dependence, it's: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiSuccess {Object[]} dependencies.task Task depende infos
	* @apiSuccess {int} dependencies.task.id Task depende id
	* @apiSuccess {string} dependencies.task.title Task depende title
	* @apiSuccess {string} dependencies.task.started_at Task depende started date
	* @apiSuccess {string} dependencies.task.due_date Task depende due date
	* @apiSuccess {Object[]} tasks_modified Array of infos on the tasks modified because of the dependencies
	* @apiSuccess {Number} tasks_modified.id Id of the task modified
	* @apiSuccess {String} tasks_modified.title Title of the task modified
	* @apiSuccess {string} tasks_modified.started_at Date of start of the task modified
	* @apiSuccess {string} tasks_modified.due_date Due date of the task modified
	*
	* @apiSuccessExample Success-Full-Data-Response
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskcreation - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 2,
	*			"title": "Update servers",
	*			"description": "update all the servers",
	*			"projectId": 1,
	*			"due_date": "2015-10-15 11:00:00",
	*			"started_at": "2015-10-10 11:00:00",
	*			"finished_at": "2015-10-15 18:23:00",
	*			"created_at": "2015-10-09 11:00:00",
	*			"is_milestone": false,
	*			"is_container": true,
	*			"tasks":
	*			[
	*				{
	*					"id": 1,
	*					"title": "Add users to project",
	*					"started_at": "2015-10-10 11:00:00",
	*					"due_date": "2015-10-15 18:23:00"
	*				},
	*				{
	*					"id": 3,
	*					"title": "Add customers to project",
	*					"started_at": "2015-10-10 11:00:00",
	*					"due_date": "2015-10-15 18:23:00"
	*				}
	*			],
	*			"advance": 20,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users": [
	*				{
	*					"id": 13,
	*					"firstname": "Jane",
	*					"lastname": "Doe",
	*					"percent": 50
	*				}
	*			],
	*			"tags": [
	*				{
	*					"id": 2,
	*					"name": "Urgent",
	*					"color": "FFFFFF"
	*				}
	*			],
	*			"dependencies":
	*			[
	*				{
	*					"id": 1,
	*					"name": "fs",
	*					"task": {
	*						"id": 12,
	*						"title": "Add users to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					}
	*				},
	*				{
	*					"id": 3,
	*					"name": "ss",
	*					"task": {
	*						"id": 10,
	*						"title": "Add customers to project",
	*						"started_at": "2015-10-15 11:00:00",
	*						"due_date": "2015-10-20 18:23:00"
	*					}
	*				}
	*			],
	*			"tasks_modified": []
	*		}
	*	}
	*
	* @apiSuccessExample Success-Partial-Data-Response
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskcreation - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 2,
	*			"title": "Update servers",
	*			"description": "update all the servers",
	*			"due_date": "2015-10-15 11:00:00",
	*			"is_milestone": true,
	*			"is_container": false,
	*			"tasks": [],
	*			"started_at": null,
	*			"finished_at": null,
	*			"created_at": "2015-10-09 11:00:00",
	*			"advance": 20,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users": [],
	*			"tags": [],
	*			"dependencies": [],
	*			"tasks_modified": []
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "new task",
	*			"body": {
	*				"id": 2,
	*				"title": "Update servers",
	*				"description": "update all the servers",
	*				"due_date": "2015-10-15 11:00:00",
	*				"started_at": "2015-10-10 11:00:00",
	*				"finished_at": "2015-10-15 18:23:00",
	*				"created_at": "2015-10-09 11:00:00",
	*				"is_milestone": false,
	*				"is_container": true,
	*				"tasks":
	*				[
	*					{
	*						"id": 1,
	*						"title": "Add users to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					},
	*					{
	*						"id": 3,
	*						"title": "Add customers to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					}
	*				],
	*				"advance": 20,
	*				"creator": {
	*					"id": 1,
	*					"firstname": "john",
	*					"lastname": "doe"
	*				},
	*				"users": [
	*					{
	*						"id": 13,
	*						"firstname": "Jane",
	*						"lastname": "Doe",
	*						"percent": 50
	*					}
	*				],
	*				"tags": [
	*					{
	*						"id": 2,
	*						"name": "Urgent",
	*						"color": "FFFFFF"
	*					}
	*				],
	*				"dependencies":
	*				[
	*					{
	*						"id": 1,
	*						"name": "fs",
	*						"task": {
	*							"id": 12,
	*							"title": "Add users to project",
	*							"started_at": "2015-10-10 11:00:00",
	*							"due_date": "2015-10-15 18:23:00"
	*						}
	*					},
	*					{
	*						"id": 3,
	*						"name": "ss",
	*						"task": {
	*							"id": 10,
	*							"title": "Add customers to project",
	*							"started_at": "2015-10-15 11:00:00",
	*							"due_date": "2015-10-20 18:23:00"
	*						}
	*					}
	*				],
	*				"tasks_modified": []
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.1.3",
	*			"return_message": "Task - taskcreation - Bad Token"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.1.6",
	*			"return_message": "Task - taskcreation - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.1.9",
	*			"return_message": "Task - taskcreation - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.1.4",
	*			"return_message": "Task - taskcreation - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: dependencies
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.1.4",
	*			"return_message": "Task - taskcreation - Bad Parameter: dependencies"
	*		}
	*	}
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

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($content->projectId);
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
				$dependence = $em->getRepository('SQLBundle:Task')->find($dep->id);
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
					$taskAdd = $em->getRepository("SQLBundle:Task")->find($ta);
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
					$taskRemove = $em->getRepository("SQLBundle:Task")->find($ta);
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

				$userToAdd = $em->getRepository('SQLBundle:User')->find($userAdd->id);
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
				$userToRemove = $em->getRepository('SQLBundle:User')->find($userId);

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
				$tagToAdd = $em->getRepository('SQLBundle:Tag')->find($tag);
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
				$tagToRemove = $em->getRepository('SQLBundle:Tag')->find($tagRemove);
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
		$mdata['mtitle'] = "new task";
		$mdata['mdesc'] = json_encode($task->objectToArray(array()));
		$wdata['type'] = "new task";
		$wdata['targetId'] = $task->getId();
		$wdata['message'] = json_encode($task->objectToArray(array()));
		$userNotif = array();
		if ($task->getProjects() != null) {
			foreach ($task->getProjects()->getUsers() as $key => $value) {
				$userNotif[] = $value->getId();
			}
		}
		else {
			foreach ($task->getRessources() as $key => $value) {
				$userNotif[] = $value->getUser()->getId();
			}
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($task->getProjects(), 'UserTasksAdvancement');
		$this->get('service_stat')->updateStat($task->getProjects(), 'UserWorkingCharge');
		$this->get('service_stat')->updateStat($task->getProjects(), 'TasksRepartition');

		return $this->setCreated("1.12.1", "Task", "taskcreation", "Complete Success", $task->objectToArray(array()));
	}

	/**
	* @api {put} /0.3/task/:id Update a task
	* @apiName taskUpdate
	* @apiGroup Task
	* @apiDescription Update a given task
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the task
	* @apiParam {String} [title] Title of the task
	* @apiParam {String} [description] Description of the task
	* @apiParam {Datetime} [due_date] Due date of the task
	* @apiParam {Boolean} [is_container] If true, set the task as a container. If is_milestone is true, then set is_milestone to false.
	* @apiParam {Int[]} [tasksAdd] Array of tasks id to add. To set all the tasks contains in the container (is_container must be true for that)
	* @apiParam {Int[]} [tasksRemove] Array of tasks id to remove. To set all the tasks contains in the container (is_container must be true for that)
	* @apiParam {Object[]} [dependencies] Array of infos on the dependencies
	* @apiParam {String} dependencies.name name of the dependence, it should be: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiParam {Number} dependencies.id Id of the task the new task dependes on
	* @apiParam {Object[]} [dependenciesUpdate] Array of infos on the dependencies to update
	* @apiParam {String} dependenciesUpdate.oldName name of the dependence, it should be: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiParam {String} dependenciesUpdate.newName New name of the dependence, it should be: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiParam {Number} dependenciesUpdate.id Id of the task the new task dependes on
	* @apiParam {Int[]} [dependenciesRemove] Array of dependencies Id to remove.
	* @apiParam {Datetime} [started_at] Date of start of the task
	* @apiParam {Datetime} [finished_at] Date of finish of the task
	* @apiParam {Number} [advance] Advance percent of the task
	* @apiParam {Object[]} [usersAdd] Array of users to add
	* @apiParam {Int} usersAdd.id Id of the user to add
	* @apiParam {Int} usersAdd.percent Percent of the user
	* @apiParam {Object[]} [usersUpdate] Array of users to update
	* @apiParam {Int} usersUpdate.id Id of the user to update
	* @apiParam {Int} usersUpdate.percent New percent of the user
	* @apiParam {Int[]} [usersRemove] Array of users id to remove
	* @apiParam {Int[]} [tagsAdd] Array of tags id to add
	* @apiParam {Int[]} [tagsRemove] Array of tags id to remove
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
	*			"title": "User management",
	*			"description": "User: creation, uptade and delete",
	*			"due_date": "2015-10-10 11:00:00",
	*			"is_container": true,
	*			"tasksAdd": [1, 50, 13],
	*			"tasksRemove": [3],
	*			"dependencies":
	*			[
	*				{
	*					"name": "fs",
	*					"id": 1
	*				},
	*				{
	*					"name": "ss",
	*					"id": 3
	*				}
	*			],
	*			"dependenciesUpdate":
	*			[
	*				{
	*					"oldName": "fs",
	*					"newName": "ff",
	*					"id": 1
	*				}
	*			],
	*			"dependenciesRemove": [6, 9],
	*			"started_at": "2015-10-10 12:00:00",
	*			"finished_at": "2015-10-15 18:23:00",
	*			"advance" : 30,
	*			"usersAdd": [
	*				{
	*					"id": 1,
	*					"percent": 50
	*				 },
	*				{
	*					"id": 3,
	*					"percent": 100
	*				}
	*			],
	*			"usersRemove": [6],
	*			"tagsAdd": [1, 3, 5],
	*			"tagsRemove": [9]
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	*	{
	*		"data": {
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"started_at": "2015-10-10 12:00:00",
	*			"finished_at": "2015-10-15 18:23:00"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
	* @apiSuccess {String} description Description of the task
	* @apiSuccess {Number} projectId Id of the project
	* @apiSuccess {string} due_date Due date of the task
	* @apiSuccess {string} started_at Date of start of the task
	* @apiSuccess {string} finished_at Date of finish of the task
	* @apiSuccess {string} created_at Date of creation of the task
	* @apiSuccess {Boolean} is_milestone Is the task a milestone
	* @apiSuccess {Boolean} is_container Is the task a container
	* @apiSuccess {Object[]} tasks Array of tasks for the container
	* @apiSuccess {Number} tasks.id Id of the task
	* @apiSuccess {String} task.title Title of the task
	* @apiSuccess {string} tasks.started_at Date of start of the task
	* @apiSuccess {string} tasks.due_date Due date of the task
	* @apiSuccess {Number} advance Advance percent of the task
	* @apiSuccess {Object[]} creator Creator informations
	* @apiSuccess {Number} creator.id Id of the creator
	* @apiSuccess {String} creator.firstname Frist name of the creator
	* @apiSuccess {String} creator.lastname Last name of the creator
	* @apiSuccess {Object[]} users Array of users assigned to the task
	* @apiSuccess {Number} users.id Id of the user assigned
	* @apiSuccess {String} users.firstname Frist name of the user assigned
	* @apiSuccess {String} users.lastname Last name of the user assigned
	* @apiSuccess {Number} users.percent Percent of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
	* @apiSuccess {Number} tags.id Id of the tag
	* @apiSuccess {String} tags.name Name of the tag
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} dependencies Array of infos on the dependencies
	* @apiSuccess {Number} dependencies.id Id of the task the dependence
	* @apiSuccess {String} dependencies.name Name of the dependence, it's: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiSuccess {Object[]} dependencies.task Task depende infos
	* @apiSuccess {int} dependencies.task.id Task depende id
	* @apiSuccess {string} dependencies.task.title Task depende title
	* @apiSuccess {string} dependencies.task.started_at Task depende started date
	* @apiSuccess {string} dependencies.task.due_date Task depende due date
	* @apiSuccess {Object[]} tasks_modified Array of infos on the tasks modified because of the dependencies
	* @apiSuccess {Number} tasks_modified.id Id of the task modified
	* @apiSuccess {String} tasks_modified.title Title of the task modified
	* @apiSuccess {string} tasks_modified.started_at Date of start of the task modified
	* @apiSuccess {string} tasks_modified.due_date Due date of the task modified
	*
	* @apiSuccessExample Success-Full-Data-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskupdate - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 2,
	*			"title": "Update servers",
	*			"description": "update all the servers",
	*			"projectId": 1,
	*			"due_date": "2015-10-15 11:00:00",
	*			"started_at": "2015-10-10 11:00:00",
	*			"finished_at": "2015-10-15 18:23:00",
	*			"created_at": "2015-10-09 11:00:00",
	*			"is_milestone": false,
	*			"is_container": true,
	*			"tasks":
	*			[
	*				{
	*					"id": 1,
	*					"title": "Add users to project",
	*					"started_at": "2015-10-10 11:00:00",
	*					"due_date": "2015-10-15 18:23:00"
	*				},
	*				{
	*					"id": 3,
	*					"title": "Add cidject",
	*					"started_at": "2015-10-10 11:00:00",
	*					"due_date": "2015-10-15 18:23:0id	],
	*			"advance": 20,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users": [
	*				{
	*					"id": 13,
	*					"firstname": "Jane",
	*					"lastname": "Doe",
	*					"percent": 50
	*				}
	*			],
	*			"tags": [
	*				{
	*					"id": 2,
	*					"name": "Urgent",
	*					"color": "FFFFFF"
	*				}
	*			],
	*			"dependencies":
	*			[
	*				{
	*					"id": 1,
	*					"name": "fs",
	*					"task": {
	*						"id": 12,
	*						"title": "Add users to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					}
	*				},
	*				{
	*					"id": 3,
	*					"name": "ss",
	*					"task": {
	*						"id": 10,
	*						"title": "Add customers to project",
	*						"started_at": "2015-10-15 11:00:00",
	*						"due_date": "2015-10-20 18:23:00"
	*					}
	*				}
	*			],
	*			"tasks_modified":
	*			[
	*				{
	*					"id": 1,
	*					"title": "Add users to project",
	*					"started_at": "2015-10-10 11:00:00",
	*					"due_date": "2015-10-15 11:00:00",
	*				},
	*				{
	*					"id": 3,
	*					"title": "Add customers to project",
	*					"started_at": "2015-10-10 11:00:00",
	*					"due_date": "2015-10-15 11:00:00"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-Partial-Data-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskupdate - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 2,
	*			"title": "Update servers",
	*			"description": "update all the servers",
	*			"projectId": 1,
	*			"due_date": "2015-10-15 11:00:00",
	*			"is_milestone": true,
	*			"is_container": false,
	*			"tasks": [],
	*			"started_at": null,
	*			"finished_at": null,
	*			"created_at": "2015-10-09 11:00:00",
	*			"advance": 20,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users": [],
	*			"tags": [],
	*			"dependencies": [],
	*			"tasks_modified": []
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "update task",
	*			"body": {
	*				"id": 2,
	*				"title": "Update servers",
	*				"description": "update all the servers",
	*				"projectId": 1,
	*				"due_date": "2015-10-15 11:00:00",
	*				"started_at": "2015-10-10 11:00:00",
	*				"finished_at": "2015-10-15 18:23:00",
	*				"created_at": "2015-10-09 11:00:00",
	*				"is_milestone": false,
	*				"is_container": true,
	*				"tasks":
	*				[
	*					{
	*						"id": 1,
	*						"title": "Add users to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					},
	*					{
	*						"id": 3,
	*						"title": "Add customers to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					}
	*				],
	*				"advance": 20,
	*				"creator": {
	*					"id": 1,
	*					"firstname": "john",
	*					"lastname": "doe"
	*				},
	*				"users": [
	*					{
	*						"id": 13,
	*						"firstname": "Jane",
	*						"lastname": "Doe",
	*						"percent": 50
	*					}
	*				],
	*				"tags": [
	*					{
	*						"id": 2,
	*						"name": "Urgent",
	*						"color": "FFFFFF"
	*					}
	*				],
	*				"dependencies":
	*				[
	*					{
	*						"id": 1,
	*						"name": "fs",
	*						"task": {
	*							"id": 12,
	*							"title": "Add users to project",
	*							"started_at": "2015-10-10 11:00:00",
	*							"due_date": "2015-10-15 18:23:00"
	*						}
	*					},
	*					{
	*						"id": 3,
	*						"name": "ss",
	*						"task": {
	*							"id": 10,
	*							"title": "Add customers to project",
	*							"started_at": "2015-10-15 11:00:00",
	*							"due_date": "2015-10-20 18:23:00"
	*						}
	*					}
	*				],
	*				"tasks_modified": []
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.2.3",
	*			"return_message": "Task - taskupdate - Bad Token"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.2.6",
	*			"return_message": "Task - taskupdate - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.2.9",
	*			"return_message": "Task - taskupdate - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: taskId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.2.4",
	*			"return_message": "Task - taskupdate - Bad Parameter: taskId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: dependencies
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.2.4",
	*			"return_message": "Task - taskupdate - Bad Parameter: dependencies"
	*		}
	*	}
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

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('SQLBundle:Task')->find($id);

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
				$dependence = $em->getRepository('SQLBundle:Task')->find($dep->id);
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
					$taskAdd = $em->getRepository("SQLBundle:Task")->find($ta);
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
					$taskRemove = $em->getRepository("SQLBundle:Task")->find($ta);
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

				$userToAdd = $em->getRepository('SQLBundle:User')->find($userAdd->id);
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
				$userToRemove = $em->getRepository('SQLBundle:User')->find($userId);

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
				$tagToAdd = $em->getRepository('SQLBundle:Tag')->find($tag);
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
				$tagToRemove = $em->getRepository('SQLBundle:Tag')->find($tagRemove);
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
		if (count($userNotif) != 0)
		{
			$mdata['mtitle'] = "update task";
			$mdata['mdesc'] = json_encode($task->objectToArray($taskModified));
			$wdata['type'] = "update task";
			$wdata['targetId'] = $task->getId();
			$wdata['message'] = json_encode($task->objectToArray($taskModified));
			$userNotif = array();
			if ($task->getProjects() != null) {
				foreach ($task->getProjects()->getUsers() as $key => $value) {
					$userNotif[] = $value->getId();
				}
			}
			else {
				foreach ($task->getRessources() as $key => $value) {
					$userNotif[] = $value->getUser()->getId();
				}
			}
			if (count($userNotif) > 0)
				$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);
		}

		return $this->setSuccess("1.12.1", "Task", "taskupdate", "Complete Success", $task->objectToArray($taskModified));
	}

	/**
	* @api {get} /0.3/task/:taskId Get a task informations
	* @apiName taskInformations
	* @apiGroup Task
	* @apiDescription Get the informations of the given task
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} taskId Id of the task
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
	* @apiSuccess {String} description Description of the task
	* @apiSuccess {string} due_date Due date of the task
	* @apiSuccess {string} started_at Date of start of the task
	* @apiSuccess {string} finished_at Date of finish of the task
	* @apiSuccess {string} created_at Date of creation of the task
	* @apiSuccess {Boolean} is_milestone Is the task a milestone
	* @apiSuccess {Boolean} is_container Is the task a container
	* @apiSuccess {Object[]} tasks Array of tasks for the container
	* @apiSuccess {Number} tasks.id Id of the task
	* @apiSuccess {String} task.title Title of the task
	* @apiSuccess {string} tasks.started_at Date of start of the task
	* @apiSuccess {string} tasks.due_date Due date of the task
	* @apiSuccess {Number} advance Advance percent of the task
	* @apiSuccess {Object[]} creator Creator informations
	* @apiSuccess {Number} creator.id Id of the creator
	* @apiSuccess {String} creator.firstname Frist name of the creator
	* @apiSuccess {String} creator.lastname Last name of the creator
	* @apiSuccess {Object[]} users Array of users assigned to the task
	* @apiSuccess {Number} users.id Id of the user assigned
	* @apiSuccess {String} users.firstname Frist name of the user assigned
	* @apiSuccess {String} users.lastname Last name of the user assigned
	* @apiSuccess {Number} users.percent Percent of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
	* @apiSuccess {Number} tags.id Id of the tag
	* @apiSuccess {String} tags.name Name of the tag
	* @apiSuccess {string} tags.color Color of the tag in hexa
	* @apiSuccess {Object[]} dependencies Array of infos on the dependencies
	* @apiSuccess {Number} dependencies.id Id of the task the dependence
	* @apiSuccess {String} dependencies.name Name of the dependence, it's: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiSuccess {Object[]} dependencies.task Task depende infos
	* @apiSuccess {int} dependencies.task.id Task depende id
	* @apiSuccess {string} dependencies.task.title Task depende title
	* @apiSuccess {string} dependencies.task.started_at Task depende started date
	* @apiSuccess {string} dependencies.task.due_date Task depende due date
	* @apiSuccess {Object[]} tasks_modified Array of infos on the tasks modified because of the dependencies. Always null because no modifications on GET
	*
	* @apiSuccessExample Success-Full-Data-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskinformations - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 2,
	*			"title": "Update servers",
	*			"description": "update all the servers",
	*			"due_date": "2015-10-15 11:00:00",
	*			"started_at": "2015-10-10 11:00:00",
	*			"finished_at": "2015-10-15 18:23:00",
	*			"created_at": "2015-10-09 11:00:00",
	*			"is_milestone": false,
	*			"is_container": true,
	*			"tasks":
	*			[
	*				{
	*					"id": 1,
	*					"title": "Add users to project",
	*					"started_at": "2015-10-10 11:00:00",
	*					"due_date": "2015-10-15 18:23:00"
	*				},
	*				{
	*					"id": 3,
	*					"title": "Add customers to project",
	*					"started_at": "2015-10-10 11:00:00",
	*					"due_date": "2015-10-15 18:23:00"
	*				}
	*			],
	*			"advance": 20,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users": [
	*				{
	*					"id": 13,
	*					"firstname": "Jane",
	*					"lastname": "Doe",
	*					"percent": 50
	*				}
	*			],
	*			"tags": [
	*				{
	*					"id": 2,
	*					"name": "Urgent",
	*					"color": "FFFFFF"
	*				}
	*			],
	*			"dependencies":
	*			[
	*				{
	*					"id": 1,
	*					"name": "fs",
	*					"task": {
	*						"id": 12,
	*						"title": "Add users to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					}
	*				},
	*				{
	*					"id": 3,
	*					"name": "ss",
	*					"task": {
	*						"id": 10,
	*						"title": "Add customers to project",
	*						"started_at": "2015-10-15 11:00:00",
	*						"due_date": "2015-10-20 18:23:00"
	*					}
	*				}
	*			],
	*			"tasks_modified": []
	*		}
	*	}
	*
	* @apiSuccessExample Success-Partial-Data-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskinformations - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 2,
	*			"title": "Update servers",
	*			"description": "update all the servers",
	*			"due_date": "2015-10-15 11:00:00",
	*			"is_milestone": true,
	*			"is_container": false,
	*			"tasks": [],
	*			"started_at": null,
	*			"finished_at": null,
	*			"created_at": "2015-10-09 11:00:00",
	*			"advance": 20,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users": [],
	*			"tags": [],
	*			"dependencies": [],
	*			"tasks_modified": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.3.3",
	*			"return_message": "Task - taskinformations - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.3.9",
	*			"return_message": "Task - taskinformations - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: taskId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.3.4",
	*			"return_message": "Task - taskinformations - Bad Parameter: taskId"
	*		}
	*	}
	*/
	public function getTaskInfosAction(Request $request, $taskId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.3.3", "Task", "taskinformations"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('SQLBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.3.4", "Task", "taskinformations", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.3.9", "Task", "taskinformations"));

		return $this->setSuccess("1.12.1", "Task", "taskinformations", "Complete Success", $task->objectToArray(array()));
	}

	/**
	* @api {put} /0.3/task/archive/:id Archive a task
	* @apiName archiveTask
	* @apiGroup Task
	* @apiDescription Archive the given task
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} taskId Id of the task
	*
	* @apiSuccess {Number} id Id of the task archived
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - archivetask - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id" : 3
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "archive task",
	*			"body": {
	*				"id": 2,
	*				"title": "Update servers",
	*				"description": "update all the servers",
	*				"project_id": 1,
	*				"due_date": "2015-10-15 11:00:00",
	*				"started_at": "2015-10-10 11:00:00",
	*				"finished_at": "2015-10-15 18:23:00",
	*				"created_at": "2015-10-09 11:00:00",
	*				"is_milestone": false,
	*				"is_container": true,
	*				"tasks":
	*				[
	*					{
	*						"id": 1,
	*						"title": "Add users to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					},
	*					{
	*						"id": 3,
	*						"title": "Add customers to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					}
	*				],
	*				"advance": 20,
	*				"creator": {
	*					"id": 1,
	*					"firstname": "john",
	*					"lastname": "doe"
	*				},
	*				"users": [
	*					{
	*						"id": 13,
	*						"firstname": "Jane",
	*						"lastname": "Doe",
	*						"percent": 50
	*					}
	*				],
	*				"tags": [
	*					{
	*						"id": 2,
	*						"name": "Urgent",
	*						"color": "FFFFFF"
	*					}
	*				],
	*				"dependencies":
	*				[
	*					{
	*						"id": 1,
	*						"name": "fs",
	*						"task": {
	*							"id": 12,
	*							"title": "Add users to project",
	*							"started_at": "2015-10-10 11:00:00",
	*							"due_date": "2015-10-15 18:23:00"
	*						}
	*					},
	*					{
	*						"id": 3,
	*						"name": "ss",
	*						"task": {
	*							"id": 10,
	*							"title": "Add customers to project",
	*							"started_at": "2015-10-15 11:00:00",
	*							"due_date": "2015-10-20 18:23:00"
	*						}
	*					}
	*				],
	*				"tasks_modified": []
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.4.3",
	*			"return_message": "Task - archivetask - Bad Token"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.4.6",
	*			"return_message": "Task - archivetask - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.4.9",
	*			"return_message": "Task - archivetask - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: taskId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.4.4",
	*			"return_message": "Task - archivetask - Bad Parameter: taskId"
	*		}
	*	}
	*/
	public function archiveTaskAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.4.3", "Task", "archivetask"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('SQLBundle:Task')->find($id);
		if ($task === null)
			return $this->setBadRequest("12.4.4", "Task", "archivetask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.4.9", "Task", "archivetask"));

		$task->setDeletedAt(new \Datetime);
		$em->flush();

		//notifs
		$mdata['mtitle'] = "archive task";
		$mdata['mdesc'] = json_encode($task->objectToArray(array()));
		$wdata['type'] = "archive task";
		$wdata['targetId'] = $task->getId();
		$wdata['message'] = json_encode($task->objectToArray(array()));
		$userNotif = array();
		if ($task->getProjects() != null) {
			foreach ($task->getProjects()->getUsers() as $key => $value) {
				$userNotif[] = $value->getId();
			}
		}
		else {
			foreach ($task->getRessources() as $key => $value) {
				$userNotif[] = $value->getUser()->getId();
			}
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($projectId, 'UserTasksAdvancement');
		$this->get('service_stat')->updateStat($projectId, 'UserWorkingCharge');
		$this->get('service_stat')->updateStat($projectId, 'TasksRepartition');

		return $this->setSuccess("1.12.1", "Task", "archivetask", "Complete Success", array("id" => $task->getId()));
	}

	/**
	* @api {delete} /0.3/task/:taskId Delete a task
	* @apiName taskDelete
	* @apiGroup Task
	* @apiDescription Delete definitely the given task
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} taskId Id of the task
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskdelete - Complete Success"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "delete task",
	*			"body": {
	*				"id": 2,
	*				"title": "Update servers",
	*				"description": "update all the servers",
	*				"project_id": 1,
	*				"due_date": "2015-10-15 11:00:00",
	*				"started_at": "2015-10-10 11:00:00",
	*				"finished_at": "2015-10-15 18:23:00",
	*				"created_at": "2015-10-09 11:00:00",
	*				"is_milestone": false,
	*				"is_container": true,
	*				"tasks":
	*				[
	*					{
	*						"id": 1,
	*						"title": "Add users to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					},
	*					{
	*						"id": 3,
	*						"title": "Add customers to project",
	*						"started_at": "2015-10-10 11:00:00",
	*						"due_date": "2015-10-15 18:23:00"
	*					}
	*				],
	*				"advance": 20,
	*				"creator": {
	*					"id": 1,
	*					"firstname": "john",
	*					"lastname": "doe"
	*				},
	*				"users": [
	*					{
	*						"id": 13,
	*						"firstname": "Jane",
	*						"lastname": "Doe",
	*						"percent": 50
	*					}
	*				],
	*				"tags": [
	*					{
	*						"id": 2,
	*						"name": "Urgent",
	*						"color": "FFFFFF"
	*					}
	*				],
	*				"dependencies":
	*				[
	*					{
	*						"id": 1,
	*						"name": "fs",
	*						"task": {
	*							"id": 12,
	*							"title": "Add users to project",
	*							"started_at": "2015-10-10 11:00:00",
	*							"due_date": "2015-10-15 18:23:00"
	*						}
	*					},
	*					{
	*						"id": 3,
	*						"name": "ss",
	*						"task": {
	*							"id": 10,
	*							"title": "Add customers to project",
	*							"started_at": "2015-10-15 11:00:00",
	*							"due_date": "2015-10-20 18:23:00"
	*						}
	*					}
	*				],
	*				"tasks_modified": []
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.5.3",
	*			"return_message": "Task - taskdelete - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.5.9",
	*			"return_message": "Task - taskdelete - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: taskId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.5.4",
	*			"return_message": "Task - taskdelete - Bad Parameter: taskId"
	*		}
	*	}
	*/
	public function deleteTaskAction(Request $request, $taskId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.5.3", "Task", "taskdelete"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('SQLBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.5.4", "Task", "taskdelete", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.5.9", "Task", "taskdelete"));

		//notifs
		$mdata['mtitle'] = "delete task";
		$mdata['mdesc'] = json_encode($task->objectToArray(array()));
		$wdata['type'] = "delete task";
		$wdata['targetId'] = $task->getId();
		$wdata['message'] = json_encode($task->objectToArray(array()));
		$userNotif = array();
		if ($task->getProjects() != null) {
			foreach ($task->getProjects()->getUsers() as $key => $value) {
				$userNotif[] = $value->getId();
			}
		}
		else {
			foreach ($task->getRessources() as $key => $value) {
				$userNotif[] = $value->getUser()->getId();
			}
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($task);
		$em->flush();

		$this->get('service_stat')->updateStat($projectId, 'UserTasksAdvancement');
		$this->get('service_stat')->updateStat($projectId, 'UserWorkingCharge');
		$this->get('service_stat')->updateStat($projectId, 'TasksRepartition');

		$response["info"]["return_code"] = "1.12.1";
		$response["info"]["return_message"] = "Task - taskdelete - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {post} /0.3/tasks/tag Create a tag
	* @apiName tagCreation
	* @apiGroup Task
	* @apiDescription Create a tag
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} name Name of the tag
	* @apiParam {string} color Color of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"projectId": 2,
	*			"name": "Urgent",
	*			"color": "FFFFFF"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the tag
	* @apiSuccess {String} name Name of the tag
	* @apiSuccess {string} color Color of the tag in hexa
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - tagcreation - Complete Success"
	*		},
	*		"data": {
	*			"id": 1,
	*			"name": "Urgent",
	*			"color": "FFFFFF"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "new tag task",
	*			"body": {
	*				"id": 1,
	*				"projectId": 1,
	*				"name": "Urgent",
	*				"color": "FFFFFF"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.8.3",
	*			"return_message": "Task - tagcreation - Bad Token"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.8.6",
	*			"return_message": "Task - tagcreation - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.8.9",
	*			"return_message": "Task - tagcreation - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.8.4",
	*			"return_message": "Task - tagcreation - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function tagCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('name', $content) || !array_key_exists('projectId', $content) || !array_key_exists('color', $content)))
			return $this->setBadRequest("12.8.6", "Task", "tagcreation", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.8.3", "Task", "tagcreation"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($content->projectId);
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
		$mdata['mtitle'] = "new tag task";
		$mdata['mdesc'] = json_encode($tagArray);
		$wdata['type'] = "new tag task";
		$wdata['targetId'] = $tag->getId();
		$wdata['message'] = json_encode($tagArray);
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($content->projectId, 'BugsTagsRepartition');

		return $this->setCreated("1.12.1", "Task", "tagcreation", "Complete Success", $tag->objectToArray());
	}

	/**
	* @api {put} /0.3/tasks/tag/:id Update a tag
	* @apiName tagUpdate
	* @apiGroup Task
	* @apiDescription Update a given task
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the tag
	* @apiParam {String} name Name of the tag
	* @apiParam {string} color Color of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"name": "ASAP",
	*			"color": "FFFFFF"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the tag
	* @apiSuccess {String} name Name of the tag
	* @apiSuccess {string} color Color of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 Ok
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - tagupdate - Complete Success"
	*		},
	*		"data": {
	*			"id" : 1,
	*			"name": "ASAP",
	*			"color": "FFFFFF"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "update tag task",
	*			"body": {
	*				"id": 1,
	*				"projectId": 1,
	*				"name": "Urgent",
	*				"color": "FFFFFF"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.9.3",
	*			"return_message": "Task - tagupdate - Bad Token"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.9.6",
	*			"return_message": "Task - tagupdate - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.9.9",
	*			"return_message": "Task - tagupdate - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.9.4",
	*			"return_message": "Task - tagupdate - Bad Parameter: projectId"
	*		}
	*	}
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

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('SQLBundle:Tag')->find($id);
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
		$mdata['mtitle'] = "update tag task";
		$mdata['mdesc'] = json_encode($tagArray);
		$wdata['type'] = "update tag task";
		$wdata['targetId'] = $tag->getId();
		$wdata['message'] = json_encode($tagArray);
		$userNotif = array();
		foreach ($tag->getProject()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->updateStat($projectId, 'BugsTagsRepartition');

		return $this->setSuccess("1.12.1", "Task", "tagupdate", "Complete Success", $tag->objectToArray());
	}

	/**
	* @api {get} /0.3/tasks/tag/:tagId Get a tag informations
	* @apiName tagInformations
	* @apiGroup Task
	* @apiDescription Get the informations of the given tag
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiSuccess {Number} id Id of the tag
	* @apiSuccess {String} name Name of the tag
	* @apiSuccess {string} color Color of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 Ok
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taginformations - Complete Success"
	*		},
	*		"data": {
	*			"id" : 1,
	*			"name": "ASAP",
	*			"color": "FFFFFF"
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.10.3",
	*			"return_message": "Task - taginformations - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.10.9",
	*			"return_message": "Task - taginformations - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: tagId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.10.4",
	*			"return_message": "Task - taginformations - Bad Parameter: tagId"
	*		}
	*	}
	*/
	public function getTagInfosAction(Request $request, $tagId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.10.3", "Task", "taginformations"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('SQLBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.10.4", "Task", "taginformations", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.10.9", "Task", "taginformations"));

		return $this->setSuccess("1.12.1", "Task", "taginformations", "Complete Success", $tag->objectToArray());
	}

	/**
	* @api {delete} /0.3/tasks/tag/:tagId Delete a tag
	* @apiName deleteTag
	* @apiGroup Task
	* @apiDescription Delete the given tag
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} tagId Id of the tag
  	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - deletetag - Complete Success"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "delete tag task",
	*			"body": {
	*				"id": 1,
	*				"projectId": 1,
	*				"name": "Urgent",
	*				"color": "FFFFFF"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.11.3",
	*			"return_message": "Task - deletetag - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.11.9",
	*			"return_message": "Task - deletetag - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: tagId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.11.4",
	*			"return_message": "Task - deletetag - Bad Parameter: tagId"
	*		}
	*	}
	*/
	public function deleteTagAction(Request $request, $tagId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.11.3", "Task", "deletetag"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('SQLBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.11.4", "Task", "deletetag", "Bad Parameter: tagId");

		if ($this->checkRoles($user, $tag->getProject()->getId(), "task") < 2)
			return ($this->setNoRightsError("12.11.9", "Task", "deletetag"));

		$tagArray = $tag->objectToArray();
		$tagArray['projectId'] = $tag->getProject()->getId();

		//notifs
		$mdata['mtitle'] = "delete tag task";
		$mdata['mdesc'] = json_encode($tagArray);
		$wdata['type'] = "delete tag task";
		$wdata['targetId'] = $tag->getId();
		$wdata['message'] = json_encode($tagArray);
		$userNotif = array();
		foreach ($tag->getProject()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($tag);
		$em->flush();

		$this->get('service_stat')->updateStat($tag->getProject()->getId(), 'BugsTagsRepartition');

		$response["info"]["return_code"] = "1.12.1";
		$response["info"]["return_message"] = "Task - deletetag - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /0.3/tasks/project/:projectId Get all the tasks for a project
	* @apiName getProjectTasks
	* @apiGroup Task
	* @apiDescription Get all the tasks for a given project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
	* @apiSuccess {String} description Description of the task
	* @apiSuccess {string} due_date Due date of the task
	* @apiSuccess {string} started_at Date of start of the task
	* @apiSuccess {string} finished_at Date of finish of the task
	* @apiSuccess {string} created_at Date of creation of the task
	* @apiSuccess {Boolean} is_milestone Is the task a milestone
	* @apiSuccess {Boolean} is_container Is the task a container
	* @apiSuccess {Object[]} tasks Array of tasks for the container
	* @apiSuccess {Number} tasks.id Id of the task
	* @apiSuccess {String} task.title Title of the task
	* @apiSuccess {string} tasks.started_at Date of start of the task
	* @apiSuccess {string} tasks.due_date Due date of the task
	* @apiSuccess {Number} advance Advance percent of the task
	* @apiSuccess {Object[]} creator Creator informations
	* @apiSuccess {Number} creator.id Id of the creator
	* @apiSuccess {String} creator.firstname Frist name of the creator
	* @apiSuccess {String} creator.lastname Last name of the creator
	* @apiSuccess {Object[]} users Array of users assigned to the task
	* @apiSuccess {Number} users.id Id of the user assigned
	* @apiSuccess {String} users.firstname Frist name of the user assigned
	* @apiSuccess {String} users.lastname Last name of the user assigned
	* @apiSuccess {Number} users.percent Percent of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
	* @apiSuccess {Number} tags.id Id of the tag
	* @apiSuccess {String} tags.name Name of the tag
	* @apiSuccess {String} tags.color Color of the tag
	* @apiSuccess {Object[]} dependencies Array of infos on the dependencies
	* @apiSuccess {Number} dependencies.id Id of the task the dependence
	* @apiSuccess {String} dependencies.name Name of the dependence, it's: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiSuccess {Object[]} dependencies.task Task depende infos
	* @apiSuccess {int} dependencies.task.id Task depende id
	* @apiSuccess {string} dependencies.task.title Task depende title
	* @apiSuccess {string} dependencies.task.started_at Task depende started date
	* @apiSuccess {string} dependencies.task.due_date Task depende due date
	* @apiSuccess {Object[]} tasks_modified Array of infos on the tasks modified because of the dependencies
	* @apiSuccess {Number} tasks_modified.id Id of the task modified
	* @apiSuccess {String} tasks_modified.title Title of the task modified
	* @apiSuccess {string} tasks_modified.started_at Date of start of the task modified
	* @apiSuccess {string} tasks_modified.due_date Due date of the task modified
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - getprojecttasks - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{
	*					"id": 2,
	*					"title": "Update servers",
	*					"description": "update all the servers",
	*					"due_date": "2015-10-15 11:00:00",
	*					"started_at": "2015-10-10 11:00:00",
	*					"finished_at": "2015-10-15 18:23:00",
	*					"created_at": "2015-10-09 11:00:00",
	*					"is_milestone": false,
	*					"is_container": true,
	*					"tasks":
	*					[
	*						{
	*							"id": 1,
	*							"title": "Add users to project",
	*							"started_at": "2015-10-10 11:00:00",
	*							"due_date": "2015-10-15 18:23:00"
	*						},
	*						{
	*							"id": 3,
	*							"title": "Add customers to project",
	*							"started_at": "2015-10-10 11:00:00",
	*							"due_date": "2015-10-15 18:23:00"
	*						}
	*					],
	*					"advance": 20,
	*					"creator": {
	*						"id": 1,
	*						"firstname": "john",
	*						"lastname": "doe"
	*					},
	*					"users": [
	*						{
	*							"id": 13,
	*							"firstname": "Jane",
	*							"lastname": "Doe",
	*							"percent": 50
	*						}
	*					],
	*					"tags": [
	*						{
	*							"id": 2,
	*							"name": "Urgent",
	*							"color": "FFFFFF"
	*						}
	*					],
	*					"dependencies":
	*					[
	*						{
	*							"id": 1,
	*							"name": "fs",
	*							"task": {
	*								"id": 12,
	*								"title": "Add users to project",
	*								"started_at": "2015-10-10 11:00:00",
	*								"due_date": "2015-10-15 18:23:00"
	*							}
	*						},
	*						{
	*							"id": 3,
	*							"name": "ss",
	*							"task": {
	*								"id": 10,
	*								"title": "Add customers to project",
	*								"started_at": "2015-10-15 11:00:00",
	*								"due_date": "2015-10-20 18:23:00"
	*							}
	*						}
	*					],
	*					"tasks_modified": []
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.12.3",
	*			"return_message": "Task - getprojecttasks - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.14.3",
	*			"return_message": "Task - getprojecttasks - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.14.9",
	*			"return_message": "Task - getprojecttasks - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.14.4",
	*			"return_message": "Task - getprojecttasks - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function getProjectTasksAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.14.3", "Task", "getprojecttasks"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("12.14.4", "Task", "getprojecttasks", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.14.9", "Task", "getprojecttasks"));

		$repository = $em->getRepository('SQLBundle:Task');
		$qb = $repository->createQueryBuilder('t')->join('t.projects', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tasks = $qb->getResult();
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
	* @api {get} /0.3/tasks/tags/project/:projectId Get all the tags for a project
	* @apiName getProjectTags
	* @apiGroup Task
	* @apiDescription Get all the tags for a given project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} array Array of tag
	* @apiSuccess {Number} array.id Id of the tag
	* @apiSuccess {String} array.name Name of the tag
	* @apiSuccess {string} array.color Color of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - getprojecttags - Complete Success"
	*		},
	*		"data":
	*		{
	*			"array": [
	*				{
	*					"id": 1,
	*					"name": "To Do",
	*					"color": "FFFFFF"
	*				},
	*				{
	*					"id": 2,
	*					"name": "Doing",
	*					"color": "FFFFFF"
	*				},
	*				{
	*					"id": 3,
	*					"name": "Done",
	*					"color": "FFFFFF"
	*				},
	*				{
	*					"id": 15,
	*					"name": "Urgent",
	*					"color": "FFFFFF"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.12.3",
	*			"return_message": "Task - getprojecttags - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.15.3",
	*			"return_message": "Task - getprojecttags - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.15.9",
	*			"return_message": "Task - getprojecttags - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.15.4",
	*			"return_message": "Task - getprojecttags - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function getProjectTagsAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("12.15.3", "Task", "getprojecttags"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("12.15.4", "Task", "getprojecttags", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.15.9", "Task", "getprojecttags"));

		$repository = $em->getRepository('SQLBundle:Tag');
		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tags = $qb->getResult();
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
