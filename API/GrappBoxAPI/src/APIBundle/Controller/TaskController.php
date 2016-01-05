<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use APIBundle\Entity\Task;
use APIBundle\Entity\Tag;

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
 *	@IgnoreAnnotation("apiIgnore")
 */
class TaskController extends RolesAndTokenVerificationController
{
	/**
	* @api {post} /V0.11/tasks/taskcreation Create a task
	* @apiName taskCreation
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projecId Id of the project
	* @apiParam {String} title Title of the task
	* @apiParam {String} description Description of the task
	* @apiParam {Datetime} due_date Due date of the task
	* @apiParam {Datetime} [started_at] Date of start of the task
	* @apiParam {Datetime} [finished_at] Date of finish of the task
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "1fez4c5ze31e5f14cze31fc",
	*		"project_id": 2,
	*		"title": "Update server",
	*		"description": "update the server apache to a newer version",
	*		"due_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		}
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"task_id" : 3
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	*
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	*/
	public function createTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('projectId', $content) && !array_key_exists('token', $content) && !array_key_exists('title', $content)
			&& !array_key_exists('description', $content) && !array_key_exists('due_date', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "task"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($content->projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		$task = new Task();
		$task->setTitle($content->title);
		$task->setDescription($content->description);
		$task->setProjects($project);
		$task->setCreatedAt(new \Datetime);
		$task->setCreatorUser($user);

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

		$em->persist($task);
		$em->flush();

		$taskId = $task->getId();
		return new JsonResponse(array("task_id" => $taskId));
	}

	/**
	* @api {put} /V0.11/tasks/taskupdate Update a task
	* @apiName taskUpdate
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {String} [title] Title of the task
	* @apiParam {String} [description] Description of the task
	* @apiParam {Datetime} [due_date] Due date of the task
	* @apiParam {Datetime} [started_at] Date of start of the task
	* @apiParam {Datetime} [finished_at] Date of finish of the task
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "nfeq34efbfkqf54",
	*		"taskId": 2,
	*		"title": "Update servers",
	*		"description": "update all the servers",
	*		"started_at":
	*		{
	*			"date":"2015-10-10 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		}
	* 	}
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
  	* @apiSuccess {String} description Description of the task
  	* @apiSuccess {Datetime} due_date Due date of the task
  	* @apiSuccess {Datetime} started_at Date of start of the task
  	* @apiSuccess {Datetime} finished_at Date of finish of the task
  	* @apiSuccess {Datetime} created_at Date of creation of the task
  	* @apiSuccess {Datetime} started_at Date of start of the task
  	* @apiSuccess {Object[]} creator Creator informations
  	* @apiSuccess {Number} creator.id Id of the creator
  	* @apiSuccess {String} creator.first_name Frist name of the creator
  	* @apiSuccess {String} creator.last_name Last name of the creator
  	* @apiSuccess {Object[]} users_assigned Array of users assigned to the task
  	* @apiSuccess {Number} users_assigned.id Id of the user assigned
  	* @apiSuccess {String} users_assigned.first_name Frist name of the user assigned
  	* @apiSuccess {String} users_assigned.last_name Last name of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
  	* @apiSuccess {Number} tags.id Id of the tag
  	* @apiSuccess {String} tags.name Name of the tag
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"id": 2,
	*		"title": "Update servers"
	*		"description": "update all the servers",
	*		"due_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"started_at":
	*		{
	*			"date":"2015-10-10 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"finished_at": null,
	*		"created_at":
	*		{
	*			"date":"2015-10-09 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"started_at": null,
	*		"creator": {
	*			"id": 1,
	*			"first_name": "john",
	*			"last_name": "doe"
	*		},
	*		"users_assigned": {
	*			"1": {
	*				"id": 1,
	*				"first_name": "john",
	*				"last_name": "doe"
	*			},
	*			"2": {
	*				"id": 3,
	*				"first_name": "jane",
	*				"last_name": "doe"
	*			}
	*		},
	*		"tags": {
	*			"1": {
	*				"id": 1,
	*				"name": "To Do"
	*			}
	*		},
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task with id X doesn't exist"
	* 	}
	*/
	public function updateTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('token', $content) && !array_key_exists('taskId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();

		$task = $em->getRepository('APIBundle:Task')->find($content->taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$content->taskId." doesn't exist");
		}

		$projectId = $task->getProject()->getId();
		if (!$this->checkRoles($user, $content->projectId, "task"))
			return ($this->setNoRightsError());

		if (array_key_exists('title', $content))
			$task->setTitle($content->title);
		if (array_key_exists('description', $content))
			$task->setDescription($content->description);
		if (array_key_exists('due_date', $content))
		{
			if (array_key_exists('timezone', $content->due_date) && $content->due_date->timezone != "")
				$dueDate = new \Datetime($content->due_date->date, new \DatetimeZone($content->due_date->timezone));
			else
				$dueDate = new \Datetime($content->due_date->date);
			$task->setDueDate($dueDate);
		}
		if (array_key_exists('started_at', $content))
		{
			if (array_key_exists('timezone', $content->started_at) && $content->started_at->timezone != "")
				$startedAt = new \Datetime($content->started_at->date, new \DatetimeZone($content->started_at->timezone));
			else
				$startedAt = new \Datetime($content->started_at->date);
			$task->setStartedAt($startedAt);
		}
		if (array_key_exists('finished_at', $content))
		{
			if (array_key_exists('timezone', $content->deleted_at) && $content->deleted_at->timezone != "")
				$deletedAt = new \Datetime($content->deleted_at->date, new \DatetimeZone($content->deleted_at->timezone));
			else
				$deletedAt = new \Datetime($content->deleted_at->date);
			$task->setFinishedAt($deletedAt);
		}

		$em->flush();

		$id = $task->getId();
		$title = $task->getTitle();
		$description = $task->getDescription();
		$dueDate = $task->getDueDate();
		$startedAt = $task->getStartedAt();
		$finishedAt = $task->getFinishedAt();
		$createdAt = $task->getCreatedAt();
		$deletedAt = $task->getDeletedAt();
		$creator = $task->getCreatorUser();
		$users = $task->getUsers();
		$tags = $task->getTags();

		$creator_id = $creator->getId();
		$creator_firstname = $creator->getFirstname();
		$creator_lastname = $creator->getLastname();
		$creatorInfos = array("id" => $creator_id, "first_name" => $creator_firstname, "last_name" => $creator_lastname);

		$userArray = array();
		$userI = 1;
		foreach ($users as $u) {
			$uid = $u->getId();
			$firstname = $u->getFirstname();
			$lastname = $u->getLastname();

			$userArray[$userI] = array("id" => $uid, "first_name" => $firstname, "last_name" => $lastname);
			$userI++;
		}

		$tagArray = array();
		$tagI = 1;
		foreach ($tags as $t) {
			$tid = $t->getId();
			$name = $t->getName();

			$tagArray[$tagI] = array("id" => $tid, "name" => $name);
			$tagI++;
		}

		return new JsonResponse(array("id" => $id, "title" => $title, "description" => $description, "due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt,
			"created_at" => $createdAt, "deleted_at" => $deletedAt, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray));
	}

	/**
	* @api {get} /V0.11/tasks/taskinformations/:token/:taskId Get a task informations
	* @apiName taskInformations
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	*
	* @apiSuccess {Number} id Id of the task
	* @apiSuccess {String} title Title of the task
  	* @apiSuccess {String} description Description of the task
  	* @apiSuccess {Datetime} due_date Due date of the task
  	* @apiSuccess {Datetime} started_at Date of start of the task
  	* @apiSuccess {Datetime} finished_at Date of finish of the task
  	* @apiSuccess {Datetime} created_at Date of creation of the task
  	* @apiSuccess {Datetime} started_at Date of start of the task
  	* @apiSuccess {Object[]} creator Creator informations
  	* @apiSuccess {Number} creator.id Id of the creator
  	* @apiSuccess {String} creator.first_name Frist name of the creator
  	* @apiSuccess {String} creator.last_name Last name of the creator
  	* @apiSuccess {Object[]} users_assigned Array of users assigned to the task
  	* @apiSuccess {Number} users_assigned.id Id of the user assigned
  	* @apiSuccess {String} users_assigned.first_name Frist name of the user assigned
  	* @apiSuccess {String} users_assigned.last_name Last name of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
  	* @apiSuccess {Number} tags.id Id of the tag
  	* @apiSuccess {String} tags.name Name of the tag
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"id": 2,
	*		"title": "Update servers"
	*		"description": "update all the servers",
	*		"due_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"started_at":
	*		{
	*			"date":"2015-10-10 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"finished_at": null,
	*		"created_at":
	*		{
	*			"date":"2015-10-09 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		},
	*		"started_at": null,
	*		"creator": {
	*			"id": 1,
	*			"first_name": "john",
	*			"last_name": "doe"
	*		},
	*		"users_assigned": {
	*			"1": {
	*				"id": 1,
	*				"first_name": "john",
	*				"last_name": "doe"
	*			},
	*			"2": {
	*				"id": 3,
	*				"first_name": "jane",
	*				"last_name": "doe"
	*			}
	*		},
	*		"tags": {
	*			"1": {
	*				"id": 1,
	*				"name": "To Do"
	*			}
	*		}
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task with id X doesn't exist"
	* 	}
	*/
	public function getTaskInfosAction(Request $request, $token, $taskId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('APIBundle:Task')->find($taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$taskId." doesn't exist");
		}

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$id = $task->getId();
		$title = $task->getTitle();
		$description = $task->getDescription();
		$dueDate = $task->getDueDate();
		$startedAt = $task->getStartedAt();
		$finishedAt = $task->getFinishedAt();
		$createdAt = $task->getCreatedAt();
		$deletedAt = $task->getDeletedAt();
		$creator = $task->getCreatorUser();
		$users = $task->getUsers();
		$tags = $task->getTags();

		$creator_id = $creator->getId();
		$creator_firstname = $creator->getFirstname();
		$creator_lastname = $creator->getLastname();
		$creatorInfos = array("id" => $creator_id, "first_name" => $creator_firstname, "last_name" => $creator_lastname);

		$userArray = array();
		$userI = 1;
		foreach ($users as $u) {
			$uid = $u->getId();
			$firstname = $u->getFirstname();
			$lastname = $u->getLastname();

			$userArray[$userI] = array("id" => $uid, "first_name" => $firstname, "last_name" => $lastname);
			$userI++;
		}

		$tagArray = array();
		$tagI = 1;
		foreach ($tags as $t) {
			$tid = $t->getId();
			$name = $t->getName();

			$tagArray[$tagI] = array("id" => $tid, "name" => $name);
			$tagI++;
		}

		return new JsonResponse(array("id" => $id, "title" => $title, "description" => $description, "due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt,
			"created_at" => $createdAt, "deleted_at" => $deletedAt, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray));	
	}


	/**
	* @api {put} /V0.11/tasks/archivetask Archive a task
	* @apiName archiveTask
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Task archived successfully!"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task with id X doesn't exist"
	* 	}
	*/
	public function archiveTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('token', $content) && !array_key_exists('taskId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();

		$task = $em->getRepository('APIBundle:Task')->find($content->taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$content->taskId." doesn't exist");
		}

		$projectId = $task->getProject();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$task->setDeletedAt(new \Datetime);

		$em->flush();
		return new JsonResponse("Task archived successfully!");
	}

	/**
	* @api {delete} /V0.11/tasks/taskdelete/:token/:taskId Delete a task
	* @apiName taskDelete
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Task deletion successfull!"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task with id X doesn't exist"
	* 	}
	*/
	public function deleteTaskAction(Request $request, $token, $taskId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('APIBundle:Task')->find($taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$taskId." doesn't exist");
		}
		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$em->remove($task);

		$em->flush();
		return new JsonResponse("Task deletion successfull!");
	}

	/**
	* @api {put} /V0.11/tasks/assignusertotask Assign a user to a task
	* @apiName assignUserToTask
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} userId Id of the user
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"User assigned to task successfull!"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task with id X doesn't exist"
	* 	}
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with id X doesn't exist"
	* 	}
	* @apiErrorExample User already assigned
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"The user is already assign to the task"
	* 	}
	*/
	public function assignUserToTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('userId', $content) && !array_key_exists('token', $content) && !array_key_exists('taskId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('APIBundle:Task')->find($content->taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$content->taskId." doesn't exist");
		}

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$userToAdd = $em->getRepository('APIBundle:User')->find($content->userId);

		if ($userToAdd === null)
		{
			throw new NotFoundHttpException("The user with id ".$content->userId." doesn't exist");
		}

		$users = $task->getUsers();
		foreach ($users as $user) {
			if ($user === $userToAdd)
			{
				return new JsonResponse('The user is already assign to the task', JsonResponse::HTTP_BAD_REQUEST);
			}
		}

		$task->addUser($userToAdd);

		$em->flush();
		return new JsonResponse("User assigned to task successfull!");
	}

	/**
	* @api {delete} /V0.11/tasks/removeusertotask/:token/:taskId/:userId Remove a user to a task
	* @apiName removeUserToTask
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} userId Id of the user
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"User removed from the task."
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task with id X doesn't exist"
	* 	}
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with id X doesn't exist"
	* 	}
	* @apiErrorExample No user found on the task
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with id X is not assigned to the task"
	* 	}
	*/
	public function removeUserToTaskAction(Request $request, $token, $taskId, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('APIBundle:Task')->find($taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$taskId." doesn't exist");
		}

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$userToRemove = $em->getRepository('APIBundle:User')->find($userId);

		if ($userToRemove === null)
		{
			throw new NotFoundHttpException("The user with id ".$userId." doesn't exist");
		}

		$users = $task->getUsers();
		$isAssign = false;
		foreach ($users as $user) {
			if ($user === $userToRemove)
			{
				$isAssign = true;
			}
		}

		if ($isAssign === false)
		{
			throw new NotFoundHttpException("The user with id ".$userId." is not assigned to the task");
		}

		$task->removeUser($userToRemove);
		$em->flush();
		return new JsonResponse("User removed from the task.");
	}

	/**
	* @api {post} /V0.11/tasks/tagcreation Create a tag
	* @apiName tagCreation
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "1fez4c5ze31e5f14cze31fc",
	*		"projectId": 2,
	*		"name": "Urgent"
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"tag_id" : 1
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*/
	public function tagCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('name', $content) && !array_key_exists('token', $content) && !array_key_exists('projectId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "task"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($content->projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		$tag = new Tag();
		$tag->setName($content->name);
		$tag->setProject($project);

		$em->persist($tag);
		$em->flush();

		$id = $tag->getId();

		return new JsonResponse(array("tag_id" => $id));
	}

	/**
	* @api {put} /V0.11/tasks/tagupdate Update a tag
	* @apiName tagUpdate
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	* @apiParam {String} name Name of the tag
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "1fez4c5ze31e5f14cze31fc",
	*		"tagId": 1,
	*		"name": "ASAP"
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"tag_id" : 1,
	*		"tag_name": "ASAP"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
	*/
	public function tagUpdateAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('name', $content) && !array_key_exists('token', $content) && !array_key_exists('tagId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('APIBundle:Tag')->find($content->tagId);

		if ($tag === null)
		{
			throw new NotFoundHttpException("The tag with id ".$content->tagId." doesn't exist");
		}

		$projectId = $tag->getProject()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$tag->setName($content->name);
		$em->flush();

		$id = $tag->getId();
		$name = $tag->getName();

		return new JsonResponse(array("tag_id" => $id, "tag_name" => $name));
	}

		/**
	* @api {get} /V0.11/tasks/taginformations/:token/:tagId Get a tag informations
	* @apiName tagInformations
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*
  	* @apiSuccess {Number} id Id of the tag
  	* @apiSuccess {String} name Name of the tag
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"id": 1,
	*		"name": "To Do"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
	*/
	public function getTagInfosAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('APIBundle:Tag')->find($tagId);

		if ($tag === null)
		{
			throw new NotFoundHttpException("The tag with id ".$tagId." doesn't exist");
		}

		$projectId = $tag->getProject()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$id = $tag->getId();
		$name = $tag->getName();

		return new JsonResponse(array("id" => $id, "name" => $name));
	}

	/**
	* @api {delete} /V0.11/tasks/deletetag/:token/:tagId Delete a tag
	* @apiName deleteTag
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Tag deleted."
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
	*/
	public function deleteTagAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('APIBundle:Tag')->find($tagId);

		if ($tag === null)
		{
			throw new NotFoundHttpException("The tag with id ".$tagId." doesn't exist");
		}

		$project = $tag->getProject();
		if ($project === null)
			return ($this->setNoRightsError());
		$projectId = $project->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$em->remove($tag);
		$em->flush();

		return new JsonResponse("Tag deleted.");
	}

	/**
	* @api {put} /V0.11/tasks/assigntagtotask Assign a tag to a task
	* @apiName assignTagToTask
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} tagId Id of the tag
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Tag assigned to task successfull!"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task with id X doesn't exist"
	* 	}
	* @apiErrorExample Tag already assigned
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"The tag is already assign to the task"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
	*/
	public function assignTagToTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if ($content === null || (!array_key_exists('tagId', $content) && !array_key_exists('token', $content) && !array_key_exists('taskId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('APIBundle:Task')->find($content->taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$content->taskId." doesn't exist");
		}

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$tagToAdd = $em->getRepository('APIBundle:Tag')->find($content->tagId);

		if ($tagToAdd === null)
		{
			throw new NotFoundHttpException("The tag with id ".$content->tagId." doesn't exist");
		}

		$tags = $task->getTags();
		foreach ($tags as $tag) {
			if ($tag === $tagToAdd)
			{
				return new JsonResponse('The tag is already assign to the task', JsonResponse::HTTP_BAD_REQUEST);
			}
		}

		$task->addTag($tagToAdd);

		$em->flush();
		return new JsonResponse("Tag assigned to task successfull!");
	}

	/**
	* @api {delete} /V0.11/tasks/removetagtotask/:token/:taskId/:tagId Remove a tag to a task
	* @apiName removeTagToTask
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} tagId Id of the tag
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Tag removed from the task."
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The task with id X doesn't exist"
	* 	}
	* @apiErrorExample No tag found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X doesn't exist"
	* 	}
	* @apiErrorExample No tag found on the task
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The tag with id X is not assigned to the task"
	* 	}
	*/
	public function removeTagToTaskAction(Request $request, $token, $taskId, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('APIBundle:Task')->find($taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$taskId." doesn't exist");
		}

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$tagToRemove = $em->getRepository('APIBundle:Tag')->find($tagId);

		if ($tagToRemove === null)
		{
			throw new NotFoundHttpException("The tag with id ".$tagId." doesn't exist");
		}

		$tags = $task->getTags();
		$isAssign = false;
		foreach ($tags as $tag) {
			if ($tag === $userToRemove)
			{
				$isAssign = true;
			}
		}

		if ($isAssign === false)
		{
			throw new NotFoundHttpException("The tag with id ".$tagId." is not assigned to the task");
		}

		$task->removeTag($tagToRemove);

		$em->flush();
		return new JsonResponse("Tag removed from the task.");
	}

	/**
	* @api {get} /V0.11/tasks/getprojecttasks/:token/:projectId Get all the tasks for a project
	* @apiName getProjectTasks
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} Task Array of tasks
	* @apiSuccess {Number} Task.id Id of the task
	* @apiSuccess {String} Task.title Title of the task
  	* @apiSuccess {String} Task.description Description of the task
  	* @apiSuccess {Datetime} Task.due_date Due date of the task
  	* @apiSuccess {Datetime} Task.started_at Date of start of the task
  	* @apiSuccess {Datetime} Task.finished_at Date of finish of the task
  	* @apiSuccess {Datetime} Task.created_at Date of creation of the task
  	* @apiSuccess {Datetime} Task.started_at Date of start of the task
  	* @apiSuccess {Object[]} Task.creator Creator informations
  	* @apiSuccess {Number} Task.creator.id Id of the creator
  	* @apiSuccess {String} Task.creator.first_name Frist name of the creator
  	* @apiSuccess {String} Task.creator.last_name Last name of the creator
  	* @apiSuccess {Object[]} Task.users_assigned Array of users assigned to the task
  	* @apiSuccess {Number} Task.users_assigned.id Id of the user assigned
  	* @apiSuccess {String} Task.users_assigned.first_name Frist name of the user assigned
  	* @apiSuccess {String} Task.users_assigned.last_name Last name of the user assigned
	* @apiSuccess {Object[]} Task.tags Array of tags assigned to the task
  	* @apiSuccess {Number} Task.tags.id Id of the tag
  	* @apiSuccess {String} Task.tags.name Name of the tag
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Task 1": {
	*			"id": 2,
	*			"title": "Update servers"
	*			"description": "update all the servers",
	*			"due_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"started_at":
	*			{
	*				"date":"2015-10-10 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"finished_at": null,
	*			"created_at":
	*			{
	*				"date":"2015-10-09 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"started_at": null,
	*			"creator": {
	*				"id": 1,
	*				"first_name": "john",
	*				"last_name": "doe"
	*			},
	*			"users_assigned": {
	*				"1": {
	*					"id": 1,
	*					"first_name": "john",
	*					"last_name": "doe"
	*				},
	*				"2": {
	*					"id": 3,
	*					"first_name": "jane",
	*					"last_name": "doe"
	*				}
	*			},
	*			"tags": {
	*				"1": {
	*					"id": 1,
	*					"name": "To Do"
	*				}
	*			}
	*		}
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No task found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There are no tasks for the project with id X"
	* 	}
	*/
	public function getProjectTasksAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('APIBundle:Task');

		$qb = $repository->createQueryBuilder('t')->join('t.projects', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tasks = $qb->getResult();

		if ($tasks === null || count($tasks) == 0)
		{
			throw new NotFoundHttpException("There are no tasks for the project with id ".$projectId);
		}

		$arr = array();
		$i = 1;

		foreach ($tasks as $task) {
			$id = $task->getId();
			$title = $task->getTitle();
			$description = $task->getDescription();
			$dueDate = $task->getDueDate();
			$startedAt = $task->getStartedAt();
			$finishedAt = $task->getFinishedAt();
			$createdAt = $task->getCreatedAt();
			$deletedAt = $task->getDeletedAt();
			$creator = $task->getCreatorUser();
			$users = $task->getUsers();
			$tags = $task->getTags();

			$creator_id = $creator->getId();
			$creator_firstname = $creator->getFirstname();
			$creator_lastname = $creator->getLastname();
			$creatorInfos = array("id" => $creator_id, "first_name" => $creator_firstname, "last_name" => $creator_lastname);

			$userArray = array();
			$userI = 1;
			foreach ($users as $u) {
				$uid = $u->getId();
				$firstname = $u->getFirstname();
				$lastname = $u->getLastname();

				$userArray[$userI] = array("id" => $uid, "first_name" => $firstname, "last_name" => $lastname);
				$userI++;
			}

			$tagArray = array();
			$tagI = 1;
			foreach ($tags as $t) {
				$tid = $t->getId();
				$name = $t->getName();

				$tagArray[$tagI] = array("id" => $tid, "name" => $name);
				$tagI++;
			}

			$arr["Task ".$i] = array("id" => $id, "title" => $title, "description" => $description, "due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt,
				"created_at" => $createdAt, "deleted_at" => $deletedAt, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray);
		}

		return new JsonResponse($arr);
	}

	/**
	* @api {get} /V0.11/tasks/getprojecttags/:token/:projectId Get all the tags for a project
	* @apiName getProjectTags
	* @apiGroup Task
	* @apiVersion 0.11.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} Tag Array of tag
  	* @apiSuccess {Number} Tag.id Id of the tag
  	* @apiSuccess {String} Tag.name Name of the tag
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Tag 1": {
	*			"id": 1,
	*			"name": "To Do"
	*		}
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	* @apiErrorExample No tags found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"There are no tags for the project with id X"
	* 	}
	*/
	public function getProjectTagsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('APIBundle:Tag');

		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tags = $qb->getResult();

		if ($tags === null || count($tags) == 0)
		{
			throw new NotFoundHttpException("There are no tags for the project with id ".$projectId);
		}

		$arr = array();
		$i = 1;

		foreach ($tags as $t) {
			$id = $t->getId();
			$name = $t->getName();
		
			$arr["Tag ".$i] = array("id" => $id, "name" => $name);
			$i++;
		}

		return new JsonResponse($arr);
	}
}