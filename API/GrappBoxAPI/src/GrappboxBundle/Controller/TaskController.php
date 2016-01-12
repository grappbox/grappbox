<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use GrappboxBundle\Entity\Task;
use GrappboxBundle\Entity\Tag;

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

		if ($content === null || (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('title', $content)
			|| !array_key_exists('description', $content) || !array_key_exists('due_date', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "task"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);

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
	* @apiParam {Datetime} [due_date] Due date of the task
	* @apiParam {Datetime} [started_at] Date of start of the task
	* @apiParam {Datetime} [finished_at] Date of finish of the task
	*
	* @apiParamExample {json} Request-Full-Example:
	* 	{
	*		"data": {
	*			"token": "13135",
	*			"taskId": 10,
	*			"title": "User management",
	*			"description": "User: creation, uptade and delete",
	*			"due_date": {
	*				"date":"2015-10-10 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
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
	* 	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	* 	{
	*		"data": {
	*			"token": "13135",
	*			"taskId": 10
	*		}
	* 	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	* 	{
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
  	* @apiSuccess {String} creator.firstname Frist name of the creator
  	* @apiSuccess {String} creator.lastname Last name of the creator
  	* @apiSuccess {Object[]} users_assigned Array of users assigned to the task
  	* @apiSuccess {Number} users_assigned.id Id of the user assigned
  	* @apiSuccess {String} users_assigned.firstname Frist name of the user assigned
  	* @apiSuccess {String} users_assigned.lastname Last name of the user assigned
	* @apiSuccess {Object[]} tags Array of tags assigned to the task
  	* @apiSuccess {Number} tags.id Id of the tag
  	* @apiSuccess {String} tags.name Name of the tag
  	*
	* @apiSuccessExample Success-Full-Data-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - projectcreation - Complete Success"
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
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-Partial-Data-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - projectcreation - Complete Success"
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
	*			"started_at": null,
	*			"finished_at": null,
	*			"created_at":
	*			{
	*				"date":"2015-10-09 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"creator": {
	*				"id": 1,
	*				"firstname": "john",
	*				"lastname": "doe"
	*			},
	*			"users_assigned": [],
	*			"tags": []
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "12.2.3",
	*			"return_message": "Task - taskupdate - Bad ID"
  	*		}
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.2.6",
	*			"return_message": "Task - taskupdate - Missing Parameter"
  	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "12.2.9",
	*			"return_message": "Task - taskupdate - Insufficient Rights"
  	*		}
	* 	}
	* @apiErrorExample Bad Parameter: taskId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.2.4",
	*			"return_message": "Task - taskupdate - Bad Parameter: taskId"
  	*		}
	* 	}
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
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.2.9", "Task", "taskupdate"));

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
			if (array_key_exists('timezone', $content->finished_at) && $content->finished_at->timezone != "")
				$deletedAt = new \Datetime($content->finished_at->date, new \DatetimeZone($content->finished_at->timezone));
			else
				$deletedAt = new \Datetime($content->finished_at->date);
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
		$creatorInfos = array("id" => $creator_id, "firstname" => $creator_firstname, "lastname" => $creator_lastname);
		$userNotif[] = $creator_id;

		$userArray = array();

		foreach ($users as $u) {
			$uid = $u->getId();
			$firstname = $u->getFirstname();
			$lastname = $u->getLastname();

			$userArray[] = array("id" => $uid, "firstname" => $firstname, "lastname" => $lastname);
			if ($uid != $creator_id)
				$userNotif[] = $uid;
		}

		$tagArray = array();
		foreach ($tags as $t) {
			$tid = $t->getId();
			$name = $t->getName();

			$tagArray[] = array("id" => $tid, "name" => $name);
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

		return $this->setSuccess("1.12.2", "Task", "taskupdate", "Complete Success",
			array("id" => $id, "title" => $title, "description" => $description, "due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt,
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
		$task = $em->getRepository('GrappboxBundle:Task')->find($taskId);

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

		if ($content === null || (!array_key_exists('token', $content) || !array_key_exists('taskId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();

		$task = $em->getRepository('GrappboxBundle:Task')->find($content->taskId);

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
		$task = $em->getRepository('GrappboxBundle:Task')->find($taskId);

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
	* @api {put} /V0.2/tasks/assignusertotask Assign a user to a task
	* @apiName assignUserToTask
	* @apiGroup Task
	* @apiDescription Assign a given user to the task wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} userId Id of the user
  	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"data": {
	*			"token": "nfeq34efbfkqf54",
	*			"taskId": 2,
	*			"userId": 18
	*		}
	* 	}
  	*
  	* @apiSuccess {Number} id Id of the task
  	* @apiSuccess {Object[]} user User's informations
  	* @apiSuccess {Number} user.id Id of the user
  	* @apiSuccess {String} user.firstname Firstname of the user
  	* @apiSuccess {String} user.lastname Lastname of the user
  	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"info": {
	*			"return_code": "1.12.6",
	*			"return_message": "Task - assignusertotask - Complete Success"
  	*		},
  	*		"data":
  	*		{
  	*			"id": 1
  	*			"user": {
  	*				"id": 18
  	*				"firstname": "john",
  	*				"lastname": "doe",
  	*			}
  	*		}
	*	  }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "12.6.3",
	*			"return_message": "Task - assignusertotask - Bad ID"
  	*		}
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.6.6",
	*			"return_message": "Task - assignusertotask - Missing Parameter"
  	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "12.6.9",
	*			"return_message": "Task - assignusertotask - Insufficient Rights"
  	*		}
	* 	}
	* @apiErrorExample Bad Parameter: taskId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.6.4",
	*			"return_message": "Task - assignusertotask - Bad Parameter: taskId"
  	*		}
	* 	}
	* @apiErrorExample Bad Parameter: userId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.6.4",
	*			"return_message": "Task - assignusertotask - Bad Parameter: userId"
  	*		}
	* 	}
	* @apiErrorExample Already In Database
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.6.7",
	*			"return_message": "Task - assignusertotask - Already In Database"
  	*		}
	* 	}
	*/
	public function assignUserToTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('userId', $content) || !array_key_exists('token', $content) || !array_key_exists('taskId', $content)))
			return $this->setBadRequest("12.6.6", "Task", "assignusertotask", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.6.3", "Task", "assignusertotask"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($content->taskId);

		if ($task === null)
			return $this->setBadRequest("12.6.4", "Task", "assignusertotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.6.9", "Task", "assignusertotask"));

		$userToAdd = $em->getRepository('GrappboxBundle:User')->find($content->userId);

		if ($userToAdd === null)
			return $this->setBadRequest("12.6.4", "Task", "assignusertotask", "Bad Parameter: userId");

		$users = $task->getUsers();
		foreach ($users as $user) {
			if ($user === $userToAdd)
				return $this->setBadRequest("12.6.7", "Task", "assignusertotask", "Already In Database");
		}

		$task->addUser($userToAdd);
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

		return $this->setSuccess("1.12.6", "Task", "assignusertotask", "Complete Success",
			array("id" => $task->getId(), "user" => array("id" => $userToAdd->getId(), "firstname" => $userToAdd->getFirstname(), "lastname" => $userToAdd->getLastname())));
	}

	/**
	* @api {delete} /V0.2/tasks/removeusertotask/:token/:taskId/:userId Remove a user to a task
	* @apiName removeUserToTask
	* @apiGroup Task
	* @apiDescription Remove a given user to the task wanted
	* @apiVersion 0..0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} userId Id of the user
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"data": {
	*			"token": "nfeq34efbfkqf54",
	*			"taskId": 2,
	*			"userId": 18
	*		}
	* 	}
	*
	* @apiSuccess {Number} id Id of the user removed
  	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.7",
	*			"return_message": "Task - removeusertotask - Complete Success"
  	*		},
  	*		"data": {
	*			"id": 1
  	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 401 Unauthorized
	* 	{
	*		"info": {
	*			"return_code": "12.7.3",
	*			"return_message": "Task - removeusertotask - Bad ID"
  	*		}
	* 	}
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.7.6",
	*			"return_message": "Task - removeusertotask - Missing Parameter"
  	*		}
	* 	}
	* @apiErrorExample Insufficient Rights
	* 	HTTP/1.1 403 Forbidden
	* 	{
	*		"info": {
	*			"return_code": "12.7.9",
	*			"return_message": "Task - removeusertotask - Insufficient Rights"
  	*		}
	* 	}
	* @apiErrorExample Bad Parameter: taskId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.7.4",
	*			"return_message": "Task - removeusertotask - Bad Parameter: taskId"
  	*		}
	* 	}
	* @apiErrorExample Bad Parameter: userId
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "12.7.4",
	*			"return_message": "Task - removeusertotask - Bad Parameter: userId"
  	*		}
	* 	}
	*/
	public function removeUserToTaskAction(Request $request, $token, $taskId, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.7.6", "Task", "removeusertotask", "Missing Parameter"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($taskId);

		if ($task === null)
			return $this->setBadRequest("12.7.4", "Task", "removeusertotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.7.9", "Task", "removeusertotask"));

		$userToRemove = $em->getRepository('GrappboxBundle:User')->find($userId);

		if ($userToRemove === null)
			return $this->setBadRequest("12.7.4", "Task", "removeusertotask", "Bad Parameter: userId");

		$users = $task->getUsers();
		$isAssign = false;
		foreach ($users as $user) {
			if ($user === $userToRemove)
			{
				$isAssign = true;
			}
		}

		if ($isAssign === false)
			return $this->setBadRequest("12.7.4", "Task", "removeusertotask", "Bad Parameter: userId");

		$task->removeUser($userToRemove);
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

		return $this->setSuccess("1.12.7", "Task", "removeusertotask", "Complete Success", array("id" => $userToRemove->getId()));
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

		if ($content === null || (!array_key_exists('name', $content) || !array_key_exists('token', $content) || !array_key_exists('projectId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "task"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);

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

		if ($content === null || (!array_key_exists('name', $content) || !array_key_exists('token', $content) || !array_key_exists('tagId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($content->tagId);

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
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($tagId);

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
		$tag = $em->getRepository('GrappboxBundle:Tag')->find($tagId);

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

		if ($content === null || (!array_key_exists('tagId', $content) || !array_key_exists('token', $content) || !array_key_exists('taskId', $content)))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('GrappboxBundle:Task')->find($content->taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$content->taskId." doesn't exist");
		}

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$tagToAdd = $em->getRepository('GrappboxBundle:Tag')->find($content->tagId);

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
		$task = $em->getRepository('GrappboxBundle:Task')->find($taskId);

		if ($task === null)
		{
			throw new NotFoundHttpException("The task with id ".$taskId." doesn't exist");
		}

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError());

		$tagToRemove = $em->getRepository('GrappboxBundle:Tag')->find($tagId);

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
		$repository = $em->getRepository('GrappboxBundle:Task');

		$qb = $repository->createQueryBuilder('t')->join('t.projects', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tasks = $qb->getResult();

		if ($tasks === null)
		{
			throw new NotFoundHttpException("There are no tasks for the project with id ".$projectId);
		}
		if (count($tasks) == 0)
		{
			return new JsonResponse((Object)$arr);
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
		$repository = $em->getRepository('GrappboxBundle:Tag');

		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tags = $qb->getResult();

		if ($tags === null)
		{
			throw new NotFoundHttpException("There are no tags for the project with id ".$projectId);
		}
		if (count($tags) == 0)
		{
			return new JsonResponse((Object)$arr);
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
