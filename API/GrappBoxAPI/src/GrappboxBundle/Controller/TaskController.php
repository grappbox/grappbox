<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use GrappboxBundle\Entity\Task;
use GrappboxBundle\Entity\Tag;
use GrappboxBundle\Entity\Dependencies;
use GrappboxBundle\Entity\Ressources;
use GrappboxBundle\Entity\Contains;

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
	* @api {post} /V0.2/tasks/taskcreation Create a task
	* @apiName taskCreation
	* @apiGroup Task
	* @apiDescription Create a task
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projecId Id of the project
	* @apiParam {String} title Title of the task
	* @apiParam {String} description Description of the task
	* @apiParam {String} [color] Color of the task
	* @apiParam {Datetime} due_date Due date of the task
	* @apiParam {Boolean} is_milestone If true, set the task to a milestone. If is_container is true, then set is_containre to false.
	* @apiParam {Boolean} is_container If true, set the task as a container. If is_milestone is true, then set is_milestone to false.
	* @apiParam {Int[]} [tasksAdd] Array of tasks id to add. To set all the tasks contains in the container (is_container must be true for that)
	* @apiParam {Int[]} [tasksRemove] Array of tasks id to remove. To set all the tasks contains in the container (is_container must be true for that)
	* @apiParam {Object[]} [dependencies] Array of infos on the dependencies
	* @apiParam {String} dependencies.name name of the dependence, it should be: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiParam {Number} dependencies.id Id of the task the new task dependes on
	* @apiParam {Datetime} [started_at] Date of start of the task
	* @apiParam {Datetime} [finished_at] Date of finish of the task
	* @apiParam {Number} [advance] Advance percent of the task
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
	*			"token": "1fez4c5ze31e5f14cze31fc",
	*			"projectId": 2,
	*			"title": "Update server",
	*			"description": "update the server apache to a newer version",
	*			"color": "#25D5C2",
	*			"due_date":
	*			{
	*				"date":"2015-10-16 19:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milstone": false,
	*			"is_container": true,
	*			"tasksAdd": [1, 50, 13],
	*			"tasksRemove": [],
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
	*			"started_at":
	*			{
	*				"date":"2015-10-15 10:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"finished_at":
	*			{
	*				"date":"2015-10-16 13:26:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"advance": 20
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	*	{
	*		"data": {
	*			"token": "1fez4c5ze31e5f14cze31fc",
	*			"projectId": 2,
	*			"title": "Update server",
	*			"description": "update the server apache to a newer version",
	*			"due_date":
	*			{
	*				"date":"2015-10-16 19:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milestone": true,
	*			"is_container": false
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"token": "1fez4c5ze31e5f14cze31fc",
	*			"projectId": 2,
	*			"title": "Update server",
	*			"description": "update the server apache to a newer version",
	*			"due_date":
	*			{
	*				"date":"2015-10-16 19:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milestone": false,
	*			"is_container": true,
	*			"tasksAdd": [1, 50, 13],
	*			"started_at":
	*			{
	*				"date":"2015-10-15 10:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
	* @apiSuccess {String} description Description of the task
	* @apiSuccess {String} color Color of the task
	* @apiSuccess {Datetime} due_date Due date of the task
	* @apiSuccess {Boolean} is_milestone Is the task a milestone
	* @apiSuccess {Boolean} is_container Is the task a container
	* @apiSuccess {Object[]} tasks Array of tasks for the container
	* @apiSuccess {Number} tasks.id Id of the task
	* @apiSuccess {String} task.title Title of the task
	* @apiSuccess {Datetime} started_at Date of start of the task
	* @apiSuccess {Datetime} finished_at Date of finish of the task
	* @apiSuccess {Datetime} created_at Date of creation of the task
	* @apiSuccess {Number} advance Advance percent of the task
	* @apiSuccess {Object[]} creator Creator informations
	* @apiSuccess {Number} creator.id Id of the creator
	* @apiSuccess {String} creator.firstname Frist name of the creator
	* @apiSuccess {String} creator.lastname Last name of the creator
	* @apiSuccess {Object[]} users_assigned Array of users assigned to the task
	* @apiSuccess {Number} users_assigned.id Id of the user assigned
	* @apiSuccess {String} users_assigned.firstname Frist name of the user assigned
	* @apiSuccess {String} users_assigned.lastname Last name of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
	* @apiSuccess {Number} tags.id Id of the tag
	* @apiSuccess {String} tags.name Name of the tag
	* @apiSuccess {Object[]} dependencies Array of infos on the dependencies
	* @apiSuccess {String} dependencies.name Name of the dependence, it's: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiSuccess {Number} dependencies.id Id of the task the task dependes on
	* @apiSuccess {String} dependencies.title Title of the task the task dependes on
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
	*			"color": "#26D85A"
	*			"due_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milestone": false,
	*			"is_container": true,
	*			"tasks":
	*			[
	*				{
	*					"id": 1,
	*					"title": "Add users to project"
	*				},
	*				{
	*					"id": 3,
	*					"title": "Add customers to project"
	*				}
	*			],
	*			"started_at":
	*			{
	*				"date":"2015-10-10 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"finished_at":
	*			{
	*				"date":"2015-10-15 18:23:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"created_at":
	*			{
	*				"date":"2015-10-09 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"advance": 20,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users_assigned": [],
	*			"tags": [],
	*			"dependencies":
	*			[
	*				{
	*					"name": "fs",
	*					"id": 1,
	*					"title": "Add users to project"
	*				},
	*				{
	*					"name": "ss",
	*					"id": 3,
	*					"title": "Add customers to project"
	*				}
	*			]
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
	*			"due_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milestone": true,
	*			"is_container": false,
	*			"tasks": [],
	*			"started_at": null,
	*			"finished_at": null,
	*			"created_at":
	*			{
	*				"date":"2015-10-09 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"advance": 20,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users_assigned": [],
	*			"tags": [],
	*			"dependencies": [],
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.1.3",
	*			"return_message": "Task - taskcreation - Bad ID"
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

		if ($content === null || (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('title', $content)
			|| !array_key_exists('description', $content) || !array_key_exists('due_date', $content) || !array_key_exists('is_milestone', $content) || !array_key_exists('is_container', $content)))
			return $this->setBadRequest("12.1.6", "Task", "taskcreation", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.1.3", "Task", "taskcreation"));

		if ($this->checkRoles($user, $content->projectId, "task") < 2)
			return ($this->setNoRightsError("12.1.9", "Task", "taskcreation"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: projectId");

		$task = new Task();
		$task->setTitle($content->title);
		$task->setDescription($content->description);
		$task->setProjects($project);
		$task->setCreatedAt(new \Datetime);
		$task->setCreatorUser($user);

		if (array_key_exists('color', $content))
			$task->setColor($content->color);

		if (array_key_exists('timezone', $content->due_date) && $content->due_date->timezone != "")
			$dueDate = new \Datetime($content->due_date->date, new \DatetimeZone($content->due_date->timezone));
		else
			$dueDate = new \Datetime($content->due_date->date);
		$task->setDueDate($dueDate);

		if (array_key_exists('started_at', $content))
		{
			if (array_key_exists('timezone', $content->started_at) && $content->started_at->timezone != "")
				$startedAt = new \Datetime($content->started_at->date, new \DatetimeZone($content->started_at->timezone));
			else
				$startedAt = new \Datetime($content->started_at->date);
			$task->setStartedAt($startedAt);
		}
		else
		{
			$task->setStartedAt(date_create("0000-00-00 00:00:00"));
		}

		if (array_key_exists('finished_at', $content))
		{
			if (array_key_exists('timezone', $content->finished_at) && $content->finished_at->timezone != "")
				$finishedAt = new \Datetime($content->finished_at->date, new \DatetimeZone($content->finished_at->timezone));
			else
				$finishedAt = new \Datetime($content->finished_at->date);
			$task->setFinishedAt($finishedAt);
		}

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

		if (array_key_exists('dependencies', $content))
		{
			$dependencies = $content->dependencies;
			foreach ($dependencies as $dep) {
				$id = $dep->id;
				$cnt = 0;
				foreach ($dependencies as $d) {
					if ($id == $d->id)
						$cnt++;
				}
				if ($cnt > 1)
					return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: dependencies");
			}
			foreach ($dependencies as $dep) {
				$dependence = $em->getRepository('GrappboxBundle:Task')->find($dep->id);
				if ($dependence != null)
				{
					$newDep = new Dependencies();
					$newDep->setName($dep->name);

					$newDep->setDependence($dependence);
					$newDep->setTask($task);
					$em->persist($newDep);
					$dependence->addTaskDepended($newDep);
					$task->addDependence($newDep);
				}
			}
			$this->checkDependencies($task);
		}


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


		$arrTasks = array();
		$task->setIsContainer($content->is_container);
		if ($content->is_container == true)
		{
			$task->setIsMilestone(false);
			if (array_key_exists('tasksAdd', $content) && count($content->tasksAdd) > 0)
			{
				foreach ($content->tasksAdd as $ta) {
					$taskAdd = $em->getRepository("GrappboxBundle:Task")->find($ta);
					if ($taskAdd instanceof Task)
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
					$taskRemove = $em->getRepository("GrappboxBundle:Task")->find($ta);
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
		$id = $task->getId();
		$title = $task->getTitle();
		$description = $task->getDescription();
		$color = $task->getColor();
		$dueDate = $task->getDueDate();
		$startedAt = $task->getStartedAt();
		$finishedAt = $task->getFinishedAt();
		$createdAt = $task->getCreatedAt();
		$deletedAt = $task->getDeletedAt();
		$advance = $task->getAdvance();
		$creator = $task->getCreatorUser();
		$dependencies = $task->getDependence();

		$creator_id = $creator->getId();
		$creator_firstname = $creator->getFirstname();
		$creator_lastname = $creator->getLastname();
		$creatorInfos = array("id" => $creator_id, "firstname" => $creator_firstname, "lastname" => $creator_lastname);

		$userArray = array();
		$tagArray = array();
		$depArray = array();
		if ($dependencies != null)
		{
			foreach ($dependencies as $d) {
				$dname = $d->getName();
				$did = $d->getDependence()->getId();
				$dtitle = $d->getDependence()->getTitle();

				$depArray[] = array("name" => $dname, "id" => $did, "title" => $dtitle);
			}
		}

		return $this->setCreated("1.12.1", "Task", "taskcreation", "Complete Success", array("id" => $id, "title" => $title, "description" => $description, "color" => $color,
			"due_date" => $dueDate, "is_milestone" => $task->getIsMilestone(),"is_container" => $task->getIsContainer(), "tasks" => $arrTasks, "started_at" => $startedAt, "finished_at" => $finishedAt,
			"created_at" => $createdAt, "deleted_at" => $deletedAt, "advance" => $advance, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray, "dependencies" => $depArray));
	}

	/**
	* @api {put} /V0.2/tasks/taskupdate Update a task
	* @apiName taskUpdate
	* @apiGroup Task
	* @apiDescription Update a given task
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {String} [title] Title of the task
	* @apiParam {String} [description] Description of the task
	* @apiParam {String} [color] Color of the task
	* @apiParam {Datetime} [due_date] Due date of the task
	* @apiParam {Boolean} [is_container] If true, set the task as a container. If is_milestone is true, then set is_milestone to false.
	* @apiParam {Int[]} [tasksAdd] Array of tasks id to add. To set all the tasks contains in the container (is_container must be true for that)
	* @apiParam {Int[]} [tasksRemove] Array of tasks id to remove. To set all the tasks contains in the container (is_container must be true for that)
	* @apiParam {Object[]} [dependencies] Array of infos on the dependencies
	* @apiParam {String} dependencies.name name of the dependence, it should be: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiParam {Number} dependencies.id Id of the task the new task dependes on
	* @apiParam {Int[]} [dependenciesRemove] Array of dependencies Id to remove.
	* @apiParam {Datetime} [started_at] Date of start of the task
	* @apiParam {Datetime} [finished_at] Date of finish of the task
	* @apiParam {Number} [advance] Advance percent of the task
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"taskId": 10,
	*			"title": "User management",
	*			"description": "User: creation, uptade and delete",
	*			"color": "#25D86A",
	*			"due_date": {
	*				"date":"2015-10-10 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
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
	*			"dependenciesRemove": [6, 9],
	*			"started_at": {
	*				"date":"2015-10-10 12:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"finished_at": {
	*				"date":"2015-10-15 18:23:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"advance" : 30
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"taskId": 10
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"taskId": 10,
	*			"started_at": {
	*				"date":"2015-10-10 12:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"finished_at": {
	*				"date":"2015-10-15 18:23:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
	* @apiSuccess {String} description Description of the task
	* @apiSuccess {String} [color] Color of the task
	* @apiSuccess {Datetime} due_date Due date of the task
	* @apiSuccess {Boolean} is_milestone Is the task a milestone
	* @apiSuccess {Boolean} is_container Is the task a container
	* @apiSuccess {Object[]} tasks Array of tasks for the container
	* @apiSuccess {Datetime} started_at Date of start of the task
	* @apiSuccess {Datetime} finished_at Date of finish of the task
	* @apiSuccess {Datetime} created_at Date of creation of the task
	* @apiSuccess {Number} advance Advance percent of the task
	* @apiSuccess {Object[]} creator Creator informations
	* @apiSuccess {Number} creator.id Id of the creator
	* @apiSuccess {String} creator.firstname Frist name of the creator
	* @apiSuccess {String} creator.lastname Last name of the creator
	* @apiSuccess {Object[]} users_assigned Array of users assigned to the task
	* @apiSuccess {Number} users_assigned.id Id of the user assigned
	* @apiSuccess {String} users_assigned.firstname Frist name of the user assigned
	* @apiSuccess {String} users_assigned.lastname Last name of the user assigned
	* @apiSuccess {String} users_assigned.percent Percent of charge of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
	* @apiSuccess {Number} tags.id Id of the tag
	* @apiSuccess {String} tags.name Name of the tag
	* @apiSuccess {Object[]} dependencies Array of infos on the dependencies
	* @apiSuccess {String} dependencies.name Name of the dependence, it's: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiSuccess {Number} dependencies.id Id of the task the task dependes on
	* @apiSuccess {String} dependencies.title Title of the task the task dependes on
	* @apiSuccess {Object[]} tasks_modified Array of infos on the tasks modified because of the dependencies
	* @apiSuccess {Number} tasks_modified.id Id of the task modified
	* @apiSuccess {String} tasks_modified.title Title of the task modified
	* @apiSuccess {Datetime} tasks_modified.started_at Date of start of the task modified
	* @apiSuccess {Datetime} tasks_modified.due_date Due date of the task modified
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
	*			"color": "#54D8A2",
	*			"due_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milestone": false,
	*			"is_container": true,
	*			"tasks":
	*			[
	*				{
	*					"id": 1,
	*					"title": "Add users to project"
	*				},
	*				{
	*					"id": 3,
	*					"title": "Add customers to project"
	*				}
	*			],
	*			"started_at":
	*			{
	*				"date":"2015-10-10 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"finished_at":
	*			{
	*				"date":"2015-10-15 18:23:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"created_at":
	*			{
	*				"date":"2015-10-09 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"advance": 30,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users_assigned": [
	*				{
	*					"id": 1,
	*					"firstname": "john",
	*					"lastname": "doe",
	*					"percent": 150
	*				},
	*				{
	*					"id": 3,
	*					"firstname": "jane",
	*					"lastname": "doe"
	*				}
	*			],
	*			"tags": [
	*				{
	*					"id": 1,
	*					"name": "To Do"
	*				}
	*			],
	*			"dependencies":
	*			[
	*				{
	*					"name": "fs",
	*					"id": 1,
	*					"title": "Add users to project"
	*				},
	*				{
	*					"name": "ss",
	*					"id": 3,
	*					"title": "Add customers to project"
	*				}
	*			],
	*			"tasks_modified":
	*			[
	*				{
	*					"id": 1,
	*					"title": "Add users to project",
	*					"started_at":
	*					{
	*						"date":"2015-10-10 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
	*					"due_date":
	*					{
	*						"date":"2015-10-15 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					}
	*				},
	*				{
	*					"id": 3,
	*					"title": "Add customers to project",
	*					"started_at":
	*					{
	*						"date":"2015-10-10 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
	*					"due_date":
	*					{
	*						"date":"2015-10-15 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					}
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
	*			"due_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milestone": true,
	*			"is_container": false,
	*			"tasks": [],
	*			"started_at": null,
	*			"finished_at": null,
	*			"created_at":
	*			{
	*				"date":"2015-10-09 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"advance": 30,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users_assigned": [],
	*			"tags": [],
	*			"dependencies": [],
	*			"tasks_modified": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.2.3",
	*			"return_message": "Task - taskupdate - Bad ID"
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
	*			"return_message": "Task - taskcreation - Bad Parameter: dependencies"
	*		}
	*	}
	*/
	public function updateTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('token', $content) || !array_key_exists('taskId', $content)))
			return $this->setBadRequest("12.2.6", "Task", "taskupdate", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.2.3", "Task", "taskupdate"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($content->taskId);

		if ($task === null)
			return $this->setBadRequest("12.2.4", "Task", "taskupdate", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.2.9", "Task", "taskupdate"));

		$taskDep = $task->getTaskDepended();
		$taskModified = array();

		if (array_key_exists('title', $content))
			$task->setTitle($content->title);
		if (array_key_exists('description', $content))
		$task->setDescription($content->description);
		if (array_key_exists('color', $content))
			$task->setColor($content->color);
		if (array_key_exists('due_date', $content))
		{
			$dueDate = $task->getDueDate();
			$newDate;
			$diff;
			if (array_key_exists('timezone', $content->due_date) && $content->due_date->timezone != "")
			{
				$newDate = new \Datetime($content->due_date->date, new \DatetimeZone($content->due_date->timezone));
				if ($dueDate != null)
					$diff = date_diff($dueDate, $newDate);
				$dueDate = $newDate;
			}
			else
			{
				$newDate = new \Datetime($content->due_date->date);
				if ($dueDate != null)
					$diff = date_diff($dueDate, $newDate);
				$dueDate = $newDate;
			}

			foreach ($taskDep as $td) {
				if ($td->getName() == "fs")
				{
					$date = $td->getTask()->getStartedAt();
					date_add($date, $diff);
					$td->getTask()->setStartedAt(new \Datetime($date->format('Y-m-d H:i:s')));
					$taskModified[] = array("id" => $td->getId(), "title" => $td->getTitle(), "started_at" => $td->getStartedAt(), "due_date" => $td->getDueDate());
				}
				else if ($td->getName() == "ff")
				{
					$date = $td->getTask()->getDueDate();
					date_add($date, $diff);
					$td->getTask()->setDueDate(new \Datetime($date->format('Y-m-d H:i:s')));
					$taskModified[] = array("id" => $td->getId(), "title" => $td->getTitle(), "started_at" => $td->getStartedAt(), "due_date" => $td->getDueDate());
				}
			}
			$task->setDueDate($newDate);
		}
		if (array_key_exists('started_at', $content))
		{
			$startedAt = $task->getStartedAt();
			$newDate;
			$diff;
			if (array_key_exists('timezone', $content->started_at) && $content->started_at->timezone != "")
			{
				$newDate = new \Datetime($content->started_at->date, new \DatetimeZone($content->started_at->timezone));
				if ($startedAt != null)
					$diff = date_diff($startedAt, $newDate);
				$startedAt = $newDate;
			}
			else
			{
				$newDate = new \Datetime($content->started_at->date);
				if ($startedAt != null)
					$diff = date_diff($startedAt, $newDate);
				$startedAt = $newDate;
			}

			foreach ($taskDep as $td) {
				if ($td->getName() == "ss")
				{
					$date = $td->getTask()->getStartedAt();
					date_add($date, $diff);
					$td->getTask()->setStartedAt(new \Datetime($date->format('Y-m-d H:i:s')));
					$taskModified[] = array("id" => $td->getTask()->getId(), "title" => $td->getTask()->getTitle(), "started_at" => $td->getTask()->getStartedAt(), "due_date" => $td->getTask()->getDueDate());
				}
				else if ($td->getName() == "sf")
				{
					$date = $td->getTask()->getDueDate();
					date_add($date, $diff);
					$td->getTask()->setDueDate(new \Datetime($date->format('Y-m-d H:i:s')));
					$taskModified[] = array("id" => $td->getTask()->getId(), "title" => $td->getTask()->getTitle(), "started_at" => $td->getTask()->getStartedAt(), "due_date" => $td->getTask()->getDueDate());
				}
			}
			$task->setStartedAt($newDate);
		}
		if (array_key_exists('finished_at', $content))
		{
			if (array_key_exists('timezone', $content->finished_at) && $content->finished_at->timezone != "")
				$deletedAt = new \Datetime($content->finished_at->date, new \DatetimeZone($content->finished_at->timezone));
			else
				$deletedAt = new \Datetime($content->finished_at->date);
			$task->setFinishedAt($deletedAt);
		}
		if (array_key_exists('advance', $content))
		{
			if($content->advance > 100)
				$content->advance = 100;
			else if ($content->advance < 0)
				$content->advance = 0;
			$content->setAdvance($content->advance);
		}
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
				$dependence = $em->getRepository('GrappboxBundle:Task')->find($dep->id);
				if ($dependence != null)
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

		if ($task->getIsMilestone() == true)
		{
			$task->setStartedAt(null);
			$task->setFinishedAt(null);
		}

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
					$taskAdd = $em->getRepository("GrappboxBundle:Task")->find($ta);
					if ($taskAdd instanceof Task)
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
					$taskRemove = $em->getRepository("GrappboxBundle:Task")->find($ta);
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

		$id = $task->getId();
		$title = $task->getTitle();
		$description = $task->getDescription();
		$color = $task->getColor();
		$dueDate = $task->getDueDate();
		$startedAt = $task->getStartedAt();
		$finishedAt = $task->getFinishedAt();
		$createdAt = $task->getCreatedAt();
		$deletedAt = $task->getDeletedAt();
		$advance = $task->getAdvance();
		$creator = $task->getCreatorUser();
		$users = $task->getRessources();
		$tags = $task->getTags();
		$dependencies = $task->getDependence();

		$creator_id = $creator->getId();
		$creator_firstname = $creator->getFirstname();
		$creator_lastname = $creator->getLastname();
		$creatorInfos = array("id" => $creator_id, "firstname" => $creator_firstname, "lastname" => $creator_lastname);
		$userNotif[] = $creator_id;

		$userArray = array();

		foreach ($users as $res) {
			$percent = $res->getResource();
			$u = $res->getUser();
			$uid = $u->getId();
			$firstname = $u->getFirstname();
			$lastname = $u->getLastname();

			$userArray[] = array("id" => $uid, "firstname" => $firstname, "lastname" => $lastname, "percent" => $percent);
			if ($uid != $creator_id)
				$userNotif[] = $uid;
		}

		$tagArray = array();
		foreach ($tags as $t) {
			$tid = $t->getId();
			$name = $t->getName();

			$tagArray[] = array("id" => $tid, "name" => $name);
		}

		$depArray = array();
		foreach ($dependencies as $d) {
			$dname = $d->getName();
			$did = $d->getDependenceTask()->getId();
			$dtitle = $d->getDependenceTask()->getTitle();

			$depArray[] = array("name" => $dname, "id" => $did, "title" => $dtitle);
		}

		// Notifications
		if (count($userNotif) != 0)
		{
			$class = new NotificationController();

			$mdata['mtitle'] = "Task - Update";
			$mdata['mdesc'] = "The task ".$task->getTitle()." has been updated";

			$wdata['type'] = "Task";
			$wdata['targetId'] = $task->getId();
			$wdata['message'] = "The task ".$task->getTitle()." has been updated";

			$class->pushNotification($userNotif, $mdata, $wdata, $em);
		}

		return $this->setSuccess("1.12.1", "Task", "taskupdate", "Complete Success", array("id" => $id, "title" => $title, "description" => $description, "color" => $color, "due_date" => $dueDate,
			"is_milestone" => $task->getIsMilestone(),"is_container" => $task->getIsContainer(), "tasks" => $arrTasks, "started_at" => $startedAt, "finished_at" => $finishedAt,"created_at" => $createdAt,
			"deleted_at" => $deletedAt, "advance" => $advance, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray, "dependencies" => $depArray, "tasks_modified" => $taskModified));
	}

	/**
	* @api {get} /V0.2/tasks/taskinformations/:token/:taskId Get a task informations
	* @apiName taskInformations
	* @apiGroup Task
	* @apiDescription Get the informations of the given task
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
	* @apiSuccess {String} description Description of the task
	* @apiSuccess {String} color Color of the task
	* @apiSuccess {Datetime} due_date Due date of the task
	* @apiSuccess {Boolean} is_milestone Is the task a milestone
	* @apiSuccess {Boolean} is_container Is the task a container
	* @apiSuccess {Object[]} tasks Array of tasks for the container
	* @apiSuccess {Datetime} started_at Date of start of the task
	* @apiSuccess {Datetime} finished_at Date of finish of the task
	* @apiSuccess {Datetime} created_at Date of creation of the task
	* @apiSuccess {Number} advance Advance percent of the task
	* @apiSuccess {Object[]} creator Creator informations
	* @apiSuccess {Number} creator.id Id of the creator
	* @apiSuccess {String} creator.firstname Frist name of the creator
	* @apiSuccess {String} creator.lastname Last name of the creator
	* @apiSuccess {Object[]} users_assigned Array of users assigned to the task
	* @apiSuccess {Number} users_assigned.id Id of the user assigned
	* @apiSuccess {String} users_assigned.firstname Frist name of the user assigned
	* @apiSuccess {String} users_assigned.lastname Last name of the user assigned
	* @apiSuccess {String} users_assigned.percent Percent of charge of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
	* @apiSuccess {Number} tags.id Id of the tag
	* @apiSuccess {String} tags.name Name of the tag
	* @apiSuccess {Object[]} dependencies Array of infos on the dependencies
	* @apiSuccess {String} dependencies.name Name of the dependence, it's: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiSuccess {Number} dependencies.id Id of the task the task dependes on
	* @apiSuccess {String} dependencies.title Title of the task the task dependes on
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
	*			"color": "#54D823",
	*			"due_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milestone": false,
	*			"is_container": true,
	*			"tasks":
	*			[
	*				{
	*					"id": 1,
	*					"title": "Add users to project"
	*				},
	*				{
	*					"id": 3,
	*					"title": "Add customers to project"
	*				}
	*			],
	*			"started_at":
	*			{
	*				"date":"2015-10-10 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"finished_at":
	*			{
	*				"date":"2015-10-15 18:23:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"created_at":
	*			{
	*				"date":"2015-10-09 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"advance": 30,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users_assigned": [
	*				{
	*					"id": 1,
	*					"firstname": "john",
	*					"lastname": "doe"
	*					"percent": 150
	*				},
	*				{
	*					"id": 3,
	*					"firstname": "jane",
	*					"lastname": "doe"
	*					"percent": 50
	*				}
	*			],
	*			"tags": [
	*				{
	*					"id": 1,
	*					"name": "To Do"
	*				}
	*			],
	*			"dependencies":
	*			[
	*				{
	*					"name": "fs",
	*					"id": 1,
	*					"title": "Add users to project"
	*				},
	*				{
	*					"name": "ss",
	*					"id": 3,
	*					"title": "Add customers to project"
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
	*			"return_message": "Task - taskinformations - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 2,
	*			"title": "Update servers",
	*			"description": "update all the servers",
	*			"due_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"is_milestone": true,
	*			"is_container": false,
	*			"tasks": [],
	*			"started_at": null,
	*			"finished_at": null,
	*			"created_at":
	*			{
	*				"date":"2015-10-09 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"advance": 30,
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users_assigned": [],
	*			"tags": [],
	*			"dependencies": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.3.3",
	*			"return_message": "Task - taskinformations - Bad ID"
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
	public function getTaskInfosAction(Request $request, $token, $taskId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.3.3", "Task", "taskinformations"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.3.4", "Task", "taskinformations", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.3.9", "Task", "taskinformations"));

		$arrTasks = array();
		foreach ($task->getTasksContainer() as $t) {
			$arrTasks[] = array("id" => $t->getId(), "title" => $t->getTitle());
		}

		$id = $task->getId();
		$title = $task->getTitle();
		$description = $task->getDescription();
		$color = $task->getColor();
		$dueDate = $task->getDueDate();
		$startedAt = $task->getStartedAt();
		$finishedAt = $task->getFinishedAt();
		$createdAt = $task->getCreatedAt();
		$deletedAt = $task->getDeletedAt();
		$advance = $task->getAdvance();
		$creator = $task->getCreatorUser();
		$users = $task->getRessources();
		$tags = $task->getTags();
		$dependencies = $task->getDependence();

		$creator_id = $creator->getId();
		$creator_firstname = $creator->getFirstname();
		$creator_lastname = $creator->getLastname();
		$creatorInfos = array("id" => $creator_id, "first_name" => $creator_firstname, "last_name" => $creator_lastname);

		$userArray = array();
		foreach ($users as $res) {
			$percent = $res->getResource();
			$u = $res->getUser();
			$uid = $u->getId();
			$firstname = $u->getFirstname();
			$lastname = $u->getLastname();

			$userArray[] = array("id" => $uid, "firstname" => $firstname, "lastname" => $lastname, "percent" => $percent);
			if ($uid != $creator_id)
				$userNotif[] = $uid;
		}

		$tagArray = array();
		foreach ($tags as $t) {
			$tid = $t->getId();
			$name = $t->getName();

			$tagArray[] = array("id" => $tid, "name" => $name);
		}

		$depArray = array();
		foreach ($dependencies as $d) {
			$dname = $d->getName();
			$did = $d->getDependence()->getId();
			$dtitle = $d->getDependence()->getTitle();

			$depArray[] = array("name" => $dname, "id" => $did, "title" => $dtitle);
		}

		return $this->setSuccess("1.12.1", "Task", "taskinformations", "Complete Success",
			array("id" => $id, "title" => $title, "description" => $description, "color" => $color, "due_date" => $dueDate, "is_milestone" => $task->getIsMilestone(),
				"is_container" => $task->getIsContainer(), "tasks" => $arrTasks, "started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt,
				"deleted_at" => $deletedAt, "advance" => $advance, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray, "dependencies" => $depArray));
	}


	/**
	* @api {put} /V0.2/tasks/archivetask Archive a task
	* @apiName archiveTask
	* @apiGroup Task
	* @apiDescription Archive the given task
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"data": {
	*			"token": "13135",
	*			"taskId": 10
	*		}
	* 	}
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
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.4.3",
	*			"return_message": "Task - archivetask - Bad ID"
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
	public function archiveTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('token', $content) || !array_key_exists('taskId', $content)))
			return $this->setBadRequest("12.4.6", "Task", "archivetask", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.4.3", "Task", "archivetask"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($content->taskId);
		if ($task === null)
			return $this->setBadRequest("12.4.4", "Task", "archivetask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.4.9", "Task", "archivetask"));

		$task->setDeletedAt(new \Datetime);

		$em->flush();
		return $this->setSuccess("1.12.1", "Task", "archivetask", "Complete Success", array("id" => $task->getId()));
	}

	/**
	* @api {delete} /V0.2/tasks/taskdelete/:token/:taskId Delete a task
	* @apiName taskDelete
	* @apiGroup Task
	* @apiDescription Delete definitely the given task
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
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
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.5.3",
	*			"return_message": "Task - taskdelete - Bad ID"
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
	public function deleteTaskAction(Request $request, $token, $taskId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.5.3", "Task", "taskdelete"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.5.4", "Task", "taskdelete", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.5.9", "Task", "taskdelete"));

		$em->remove($task);

		$em->flush();
		$response["info"]["return_code"] = "1.12.1";
		$response["info"]["return_message"] = "Task - taskdelete - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {put} /V0.2/tasks/assignusertotask Assign a user to a task
	* @apiName assignUserToTask
	* @apiGroup Task
	* @apiDescription Assign a given user to the task wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} userId Id of the user
	* @apiParam {Number} percent Percent of charge of the user
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"data": {
	*			"token": "nfeq34efbfkqf54",
	*			"taskId": 2,
	*			"userId": 18,
	*			"percent": 100
	*		}
	* 	}
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {Object[]} user User's informations
	* @apiSuccess {Number} user.id Id of the user
	* @apiSuccess {String} user.firstname Firstname of the user
	* @apiSuccess {String} user.lastname Lastname of the user
	* @apiSuccess {Number} percent Percent of charge of the user
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - assignusertotask - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*			"user": {
	*				"id": 18
	*				"firstname": "john",
	*				"lastname": "doe",
	*				"percent": 100
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.6.3",
	*			"return_message": "Task - assignusertotask - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.6.6",
	*			"return_message": "Task - assignusertotask - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.6.9",
	*			"return_message": "Task - assignusertotask - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: taskId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.6.4",
	*			"return_message": "Task - assignusertotask - Bad Parameter: taskId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You can't add someone on a milestone
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.6.4",
	*			"return_message": "Task - assignusertotask - Bad Parameter: You can't add someone on a milestone"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.6.4",
	*			"return_message": "Task - assignusertotask - Bad Parameter: userId"
	*		}
	*	}
	* @apiErrorExample Already In Database
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.6.7",
	*			"return_message": "Task - assignusertotask - Already In Database"
	*		}
	*	}
	*/
	public function assignUserToTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('percent', $content) || !array_key_exists('userId', $content) || !array_key_exists('token', $content) || !array_key_exists('taskId', $content)))
			return $this->setBadRequest("12.6.6", "Task", "assignusertotask", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.6.3", "Task", "assignusertotask"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($content->taskId);
		if ($task === null)
			return $this->setBadRequest("12.6.4", "Task", "assignusertotask", "Bad Parameter: taskId");

		if ($task->getIsMilestone() == true)
			return $this->setBadRequest("12.6.4", "Task", "assignusertotask", "Bad Parameter: You can't add someone on a milestone");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.6.9", "Task", "assignusertotask"));

		$userToAdd = $em->getRepository('GrappboxBundle:User')->find($content->userId);
		if ($userToAdd === null)
			return $this->setBadRequest("12.6.4", "Task", "assignusertotask", "Bad Parameter: userId");

		$users = $task->getRessources();
		foreach ($users as $res) {
			$user = $res->getUser();
			if ($user === $userToAdd)
				return $this->setBadRequest("12.6.7", "Task", "assignusertotask", "Already In Database");
		}

		$resource = new Ressources();
		$resource->setResource($content->percent);
		$resource->setTask($task);
		$resource->setUser($userToAdd);

		$em->persist($resource);
		$em->flush();

		// Notifications
		$class = new NotificationController();

		$mdata['mtitle'] = "Task - Add";
		$mdata['mdesc'] = "You have been added on the task ".$task->getTitle();

		$wdata['type'] = "Task";
		$wdata['targetId'] = $task->getId();
		$wdata['message'] = "You have been added on the task ".$task->getTitle();

		$userNotif[] = $userToAdd->getId();

		$class->pushNotification($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.12.1", "Task", "assignusertotask", "Complete Success",
			array("id" => $task->getId(), "user" => array("id" => $userToAdd->getId(), "firstname" => $userToAdd->getFirstname(), "lastname" => $userToAdd->getLastname(), "percent" => $resource->getResource())));
	}

	/**
	* @api {delete} /V0.2/tasks/removeusertotask/:token/:taskId/:userId Remove a user to a task
	* @apiName removeUserToTask
	* @apiGroup Task
	* @apiDescription Remove a given user to the task wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} userId Id of the user
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - removeusertotask - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.7.3",
	*			"return_message": "Task - removeusertotask - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.7.9",
	*			"return_message": "Task - removeusertotask - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: taskId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.7.4",
	*			"return_message": "Task - removeusertotask - Bad Parameter: taskId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.7.4",
	*			"return_message": "Task - removeusertotask - Bad Parameter: userId"
	*		}
	*	}
	*/
	public function removeUserToTaskAction(Request $request, $token, $taskId, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.7.3", "Task", "removeusertotask"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($taskId);

		if ($task === null)
			return $this->setBadRequest("12.7.4", "Task", "removeusertotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.7.9", "Task", "removeusertotask"));

		$userToRemove = $em->getRepository('GrappboxBundle:User')->find($userId);

		if ($userToRemove === null)
			return $this->setBadRequest("12.7.4", "Task", "removeusertotask", "Bad Parameter: userId");

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

		if ($isAssign === false)
			return $this->setBadRequest("12.7.4", "Task", "removeusertotask", "Bad Parameter: userId");

		$em->remove($resToRemove);
		$em->flush();

		// Notifications
		$class = new NotificationController();

		$mdata['mtitle'] = "Task - Remove";
		$mdata['mdesc'] = "You have been removed from the task ".$task->getTitle();

		$wdata['type'] = "Task";
		$wdata['targetId'] = $task->getId();
		$wdata['message'] = "You have been removed from the task ".$task->getTitle();

		$userNotif[] = $userToRemove->getId();

		$class->pushNotification($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.12.1";
		$response["info"]["return_message"] = "Task - removeusertotask - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {post} /V0.2/tasks/tagcreation Create a tag
	* @apiName tagCreation
	* @apiGroup Task
	* @apiDescription Create a tag
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "1fez4c5ze31e5f14cze31fc",
	*			"projectId": 2,
	*			"name": "Urgent"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the tag created
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - tagcreation - Complete Success"
	*		},
	*		"data": {
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.8.3",
	*			"return_message": "Task - tagcreation - Bad ID"
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

		if ($content === null || (!array_key_exists('name', $content) || !array_key_exists('token', $content) || !array_key_exists('projectId', $content)))
			return $this->setBadRequest("12.8.6", "Task", "tagcreation", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.8.3", "Task", "tagcreation"));

		if ($this->checkRoles($user, $content->projectId, "task") < 2)
			return ($this->setNoRightsError("12.8.9", "Task", "tagcreation"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("12.8.4", "Task", "tagcreation", "Bad Parameter: projectId");

		$tag = new Tag();
		$tag->setName($content->name);
		$tag->setProject($project);

		$em->persist($tag);
		$em->flush();

		return $this->setCreated("1.12.1", "Task", "tagcreation", "Complete Success", array("id" => $tag->getId()));
	}

	/**
	* @api {put} /V0.2/tasks/tagupdate Update a tag
	* @apiName tagUpdate
	* @apiGroup Task
	* @apiDescription Update a given task
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "1fez4c5ze31e5f14cze31fc",
	*			"tagId": 1,
	*			"name": "ASAP"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the tag
	* @apiSuccess {String} name Name of the tag
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
	*			"name": "ASAP"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.9.3",
	*			"return_message": "Task - tagupdate - Bad ID"
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
	public function tagUpdateAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('name', $content) || !array_key_exists('token', $content) || !array_key_exists('tagId', $content)))
			return $this->setBadRequest("12.9.6", "Task", "tagupdate", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.9.3", "Task", "tagupdate"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($content->tagId);
		if ($tag === null)
			return $this->setBadRequest("12.9.4", "Task", "tagupdate", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.9.9", "Task", "tagupdate"));

		$tag->setName($content->name);
		$em->flush();

		return $this->setSuccess("1.12.1", "Task", "tagupdate", "Complete Success", array("id" => $tag->getId(), "name" => $tag->getName()));
	}

	/**
	* @api {get} /V0.2/tasks/taginformations/:token/:tagId Get a tag informations
	* @apiName tagInformations
	* @apiGroup Task
	* @apiDescription Get the informations of the given tag
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiSuccess {Number} id Id of the tag
	* @apiSuccess {String} name Name of the tag
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
	*			"name": "ASAP"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.10.3",
	*			"return_message": "Task - taginformations - Bad ID"
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
	public function getTagInfosAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.10.3", "Task", "taginformations"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.10.4", "Task", "taginformations", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.10.9", "Task", "taginformations"));

		return $this->setSuccess("1.12.1", "Task", "taginformations", "Complete Success", array("id" => $tag->getId(), "name" => $tag->getName()));
	}

	/**
	* @api {delete} /V0.2/tasks/deletetag/:token/:tagId Delete a tag
	* @apiName deleteTag
	* @apiGroup Task
	* @apiDescription Delete the given tag
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
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
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.11.3",
	*			"return_message": "Task - deletetag - Bad ID"
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
	public function deleteTagAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.11.3", "Task", "deletetag"));

		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.11.4", "Task", "deletetag", "Bad Parameter: tagId");

		if ($this->checkRoles($user, $tag->getProject()->getId(), "task") < 2)
			return ($this->setNoRightsError("12.11.9", "Task", "deletetag"));

		$em->remove($tag);
		$em->flush();

		$response["info"]["return_code"] = "1.12.1";
		$response["info"]["return_message"] = "Task - deletetag - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {put} /V0.2/tasks/assigntagtotask Assign a tag to a task
	* @apiName assignTagToTask
	* @apiGroup Task
	* @apiDescription Assign a given tag to the task wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "1fez4c5ze31e5f14cze31fc",
	*			"taskId": 1,
	*			"tagId": 3
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {Object[]} tag Tag's informations
	* @apiSuccess {Number} tag.id Id of the tag
	* @apiSuccess {String} tag.name Name of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - assigntagtotask - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*			"tag": {
	*				"id": 18
	*				"name": "To Do"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.12.3",
	*			"return_message": "Task - assigntagtotask - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.12.6",
	*			"return_message": "Task - assigntagtotask - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.12.9",
	*			"return_message": "Task - assigntagtotask - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: taskId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.12.4",
	*			"return_message": "Task - assigntagtotask - Bad Parameter: taskId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: tagId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.12.4",
	*			"return_message": "Task - assigntagtotask - Bad Parameter: tagId"
	*		}
	*	}
	* @apiErrorExample Already In Database
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.12.7",
	*			"return_message": "Task - assigntagtotask - Already In Database"
	*		}
	*	}
	*/
	public function assignTagToTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('tagId', $content) || !array_key_exists('token', $content) || !array_key_exists('taskId', $content)))
			return $this->setBadRequest("12.12.6", "Task", "assigntagtotask", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.12.3", "Task", "assigntagtotask"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($content->taskId);
		if ($task === null)
			return $this->setBadRequest("12.12.4", "Task", "assigntagtotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.12.9", "Task", "assigntagtotask"));

		$tagToAdd = $em->getRepository('GrappboxBundle:Tag')->find($content->tagId);
		if ($tagToAdd === null)
			return $this->setBadRequest("12.12.4", "Task", "assigntagtotask", "Bad Parameter: tagId");

		$tags = $task->getTags();
		foreach ($tags as $tag) {
			if ($tag === $tagToAdd)
				return $this->setBadRequest("12.12.7", "Task", "assigntagtotask", "Already In Database");
		}

		$task->addTag($tagToAdd);

		$em->flush();
		return $this->setSuccess("1.12.1", "Task", "assigntagtotask", "Complete Success",
			array("id" => $task->getId(), "tag" => array("id" => $tagToAdd->getId(), "name" => $tagToAdd->getName())));
	}

	/**
	* @api {delete} /V0.2/tasks/removetagtotask/:token/:taskId/:tagId Remove a tag to a task
	* @apiName removeTagToTask
	* @apiGroup Task
	* @apiDescription Remove the given tag from the task wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} tagId Id of the tag
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - removetagtotask - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.13.3",
	*			"return_message": "Task - removetagtotask - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "12.13.9",
	*			"return_message": "Task - removetagtotask - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: taskId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.13.4",
	*			"return_message": "Task - removetagtotask - Bad Parameter: taskId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: tagId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "12.13.4",
	*			"return_message": "Task - removetagtotask - Bad Parameter: tagId"
	*		}
	*	}
	*/
	public function removeTagToTaskAction(Request $request, $token, $taskId, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.13.3", "Task", "removetagtotask"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.13.4", "Task", "removetagtotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "task") < 2)
			return ($this->setNoRightsError("12.13.9", "Task", "removetagtotask"));

		$tagToRemove = $em->getRepository('GrappboxBundle:Tag')->find($tagId);
		if ($tagToRemove === null)
			return $this->setBadRequest("12.13.4", "Task", "removetagtotask", "Bad Parameter: tagId");

		$tags = $task->getTags();
		$isAssign = false;
		foreach ($tags as $tag) {
			if ($tag === $tagToRemove)
			{
				$isAssign = true;
			}
		}

		if ($isAssign === false)
			return $this->setBadRequest("12.13.4", "Task", "removetagtotask", "Bad Parameter: tagId");

		$task->removeTag($tagToRemove);
		$em->flush();

		$response["info"]["return_code"] = "1.12.1";
		$response["info"]["return_message"] = "Task - removetagtotask - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /V0.2/tasks/getprojecttasks/:token/:projectId Get all the tasks for a project
	* @apiName getProjectTasks
	* @apiGroup Task
	* @apiDescription Get all the tasks for a given project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} array Array of tasks
	* @apiSuccess {Number} array.id Id of the task
	* @apiSuccess {String} array.title Title of the task
	* @apiSuccess {String} array.description Description of the task
	* @apiSuccess {String} array.color Color of the task
	* @apiSuccess {Datetime} array.due_date Due date of the task
	* @apiSuccess {Boolean} array.is_milestone Is the task a milestone
	* @apiSuccess {Boolean} array.is_container Is the task a container
	* @apiSuccess {Object[]} array.tasks Array of tasks for the container
	* @apiSuccess {Datetime} array.started_at Date of start of the task
	* @apiSuccess {Datetime} array.finished_at Date of finish of the task
	* @apiSuccess {Datetime} array.created_at Date of creation of the task
	* @apiSuccess {Number} array.advance Advance percent of the task
	* @apiSuccess {Object[]} array.creator Creator informations
	* @apiSuccess {Number} array.creator.id Id of the creator
	* @apiSuccess {String} array.creator.first_name Frist name of the creator
	* @apiSuccess {String} array.creator.last_name Last name of the creator
	* @apiSuccess {Object[]} array.users_assigned Array of users assigned to the task
	* @apiSuccess {Number} array.users_assigned.id Id of the user assigned
	* @apiSuccess {String} array.users_assigned.first_name Frist name of the user assigned
	* @apiSuccess {String} array.users_assigned.last_name Last name of the user assigned
	* @apiSuccess {String} array.users_assigned.percent Percent of charge of the user assigned
	* @apiSuccess {Object[]} array.tags Array of tags assigned to the task
	* @apiSuccess {Number} array.tags.id Id of the tag
	* @apiSuccess {String} array.tags.name Name of the tag
	* @apiSuccess {Object[]} array.dependencies Array of infos on the dependencies
	* @apiSuccess {String} array.dependencies.name Name of the dependence, it's: fs (Finish to Start), ss (Start to Start), ff (Finish to Finish) or sf (Start to Finish)
	* @apiSuccess {Number} array.dependencies.id Id of the task the task dependes on
	* @apiSuccess {String} array.dependencies.title Title of the task the task dependes on
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
	*					"color": "#54D820",
	*					"due_date":
	*					{
	*						"date":"2015-10-15 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
	*					"is_milestone": false,
	*					"is_container": true,
	*					"tasks":
	*					[
	*						{
	*							"id": 1,
	*							"title": "Add users to project"
	*						},
	*						{
	*							"id": 3,
	*							"title": "Add customers to project"
	*						}
	*					],
	*					"started_at":
	*					{
	*						"date":"2015-10-10 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
	*					"finished_at":
	*					{
	*						"date":"2015-10-15 18:23:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
	*					"created_at":
	*					{
	*						"date":"2015-10-09 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
	*					"advance": 30,
	*					"creator": {
	*						"id": 1,
	*						"firstname": "john",
	*						"lastname": "doe"
	*					},
	*					"users_assigned": [
	*						{
	*							"id": 1,
	*							"firstname": "john",
	*							"lastname": "doe"
	*							"percent": 150
	*						},
	*						{
	*							"id": 3,
	*							"firstname": "jane",
	*							"lastname": "doe"
	*							"percent": 50
	*						}
	*					],
	*					"tags": [
	*						{
	*							"id": 1,
	*							"name": "To Do"
	*						}
	*					],
	*					"dependencies":
	*					[
	*						{
	*							"name": "fs",
	*							"id": 1,
	*							"title": "Add users to project"
	*						},
	*						{
	*							"name": "ss",
	*							"id": 3,
	*							"title": "Add customers to project"
	*						}
	*					]
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
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.14.3",
	*			"return_message": "Task - getprojecttasks - Bad ID"
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
	public function getProjectTasksAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.14.3", "Task", "getprojecttasks"));

		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.14.9", "Task", "getprojecttasks"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:Task');
		$qb = $repository->createQueryBuilder('t')->join('t.projects', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tasks = $qb->getResult();
		if ($tasks === null)
			return $this->setBadRequest("12.14.4", "Task", "getprojecttasks", "Bad Parameter: projectId");

		if (count($tasks) == 0)
			return $this->setNoDataSuccess("1.12.3", "Task", "getprojecttasks");

		$arr = array();

		foreach ($tasks as $task) {
			$id = $task->getId();
			$title = $task->getTitle();
			$description = $task->getDescription();
			$color = $task->getColor();
			$dueDate = $task->getDueDate();
			$startedAt = $task->getStartedAt();
			$finishedAt = $task->getFinishedAt();
			$createdAt = $task->getCreatedAt();
			$deletedAt = $task->getDeletedAt();
			$advance = $task->getAdvance();
			$creator = $task->getCreatorUser();
			$users = $task->getRessources();
			$tags = $task->getTags();
			$dependencies = $task->getDependence();

			$creator_id = $creator->getId();
			$creator_firstname = $creator->getFirstname();
			$creator_lastname = $creator->getLastname();
			$creatorInfos = array("id" => $creator_id, "first_name" => $creator_firstname, "last_name" => $creator_lastname);

			$arrTasks = array();
			foreach ($task->getTasksContainer() as $t) {
				$arrTasks[] = array("id" => $t->getId(), "title" => $t->getTitle());
			}

			$userArray = array();
			if ($users != null)
			{
				foreach ($users as $res) {
					$percent = $res->getResource();
					$u = $res->getUser();
					$uid = $u->getId();
					$firstname = $u->getFirstname();
					$lastname = $u->getLastname();

					$userArray[] = array("id" => $uid, "firstname" => $firstname, "lastname" => $lastname, "percent" => $percent);
					if ($uid != $creator_id)
						$userNotif[] = $uid;
				}
			}

			$tagArray = array();
			if ($tags != null)
			{
				foreach ($tags as $t) {
					$tid = $t->getId();
					$name = $t->getName();

					$tagArray[] = array("id" => $tid, "name" => $name);
				}
			}

			$depArray = array();
			if ($dependencies != null)
			{
				foreach ($dependencies as $d) {
					$dname = $d->getName();
					$did = $d->getDependence()->getId();
					$dtitle = $d->getDependence()->getTitle();

					$depArray[] = array("name" => $dname, "id" => $did, "title" => $dtitle);
				}
			}

			$arr[] = array("id" => $id, "title" => $title, "description" => $description, "color" => $color,  "due_date" => $dueDate, "is_milestone" => $task->getIsMilestone(),
				"is_container" => $task->getIsContainer(), "tasks" => $arrTasks, "started_at" => $startedAt, "finished_at" => $finishedAt, "created_at" => $createdAt,
				"deleted_at" => $deletedAt, "advance" => $advance, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray, "dependencies" => $depArray);
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.12.3", "Task", "getprojecttasks");

		return $this->setSuccess("1.12.1", "Task", "getprojecttasks", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /V0.2/tasks/getprojecttags/:token/:projectId Get all the tags for a project
	* @apiName getProjectTags
	* @apiGroup Task
	* @apiDescription Get all the tags for a given project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} array Array of tag
	* @apiSuccess {Number} array.id Id of the tag
	* @apiSuccess {String} array.name Name of the tag
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
	*					"name": "To Do"
	*				},
	*				{
	*					"id": 2,
	*					"name": "Doing"
	*				},
	*				{
	*					"id": 3,
	*					"name": "Done"
	*				},
	*				{
	*					"id": 15,
	*					"name": "Urgent"
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
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "12.15.3",
	*			"return_message": "Task - getprojecttags - Bad ID"
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
	public function getProjectTagsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.15.3", "Task", "getprojecttags"));

		if ($this->checkRoles($user, $projectId, "task") < 1)
			return ($this->setNoRightsError("12.15.9", "Task", "getprojecttags"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:Tag');
		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tags = $qb->getResult();
		if ($tags === null)
			return $this->setBadRequest("12.15.4", "Task", "getprojecttags", "Bad Parameter: projectId");

		if (count($tags) == 0)
			return $this->setNoDataSuccess("1.12.3", "Task", "getprojecttags");

		$arr = array();
		foreach ($tags as $t) {
			$id = $t->getId();
			$name = $t->getName();

			$arr[] = array("id" => $id, "name" => $name);
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.12.3", "Task", "getprojecttags");

		return $this->setSuccess("1.12.1", "Task", "getprojecttags", "Complete Success", array("array" => $arr));
	}
}
