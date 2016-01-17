<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use MongoBundle\Document\Task;
use MongoBundle\Document\Tag;

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
	* @apiParam {Datetime} due_date Due date of the task
	* @apiParam {Datetime} [started_at] Date of start of the task
	* @apiParam {Datetime} [finished_at] Date of finish of the task
	*
	* @apiParamExample {json} Request-Full-Example:
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
	*			}
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
	*			}
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
	*			"started_at":
	*			{
	*				"date":"2015-10-15 10:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
	*	}
	*
	* @apiSuccess {Number} id Id of the task created
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskcreation - Complete Success"
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
	*/
	public function createTaskAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if ($content === null || (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('title', $content)
			|| !array_key_exists('description', $content) || !array_key_exists('due_date', $content)))
			return $this->setBadRequest("12.1.6", "Task", "taskcreation", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.1.3", "Task", "taskcreation"));

		if (!$this->checkRoles($user, $content->projectId, "task"))
			return ($this->setNoRightsError("12.1.9", "Task", "taskcreation"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("12.1.4", "Task", "taskcreation", "Bad Parameter: projectId");

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
		return $this->setCreated("1.12.1", "Task", "taskcreation", "Complete Success", array("id" => $taskId));
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
	*	{
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
		$task = $em->getRepository('MongoBundle:Task')->find($content->taskId);

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

		return $this->setSuccess("1.12.1", "Task", "taskupdate", "Complete Success",
			array("id" => $id, "title" => $title, "description" => $description, "due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt,
			"created_at" => $createdAt, "deleted_at" => $deletedAt, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray));
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
	* @apiSuccessExample Success-Full-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskinformations - Complete Success"
	*		},
	*		"data":
	*		{
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
	*			"users_assigned": [
	*				{
	*					"id": 1,
	*					"first_name": "john",
	*					"last_name": "doe"
	*				},
	*				{
	*					"id": 3,
	*					"first_name": "jane",
	*					"last_name": "doe"
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
		$task = $em->getRepository('MongoBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.3.4", "Task", "taskinformations", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.3.9", "Task", "taskinformations"));

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
		foreach ($users as $u) {
			$uid = $u->getId();
			$firstname = $u->getFirstname();
			$lastname = $u->getLastname();

			$userArray[] = array("id" => $uid, "first_name" => $firstname, "last_name" => $lastname);
		}

		$tagArray = array();
		foreach ($tags as $t) {
			$tid = $t->getId();
			$name = $t->getName();

			$tagArray[] = array("id" => $tid, "name" => $name);
		}

		return $this->setSuccess("1.12.1", "Task", "taskinformations", "Complete Success",
			array("id" => $id, "title" => $title, "description" => $description, "due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt,
			"created_at" => $createdAt, "deleted_at" => $deletedAt, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray));
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
		$task = $em->getRepository('MongoBundle:Task')->find($content->taskId);
		if ($task === null)
			return $this->setBadRequest("12.4.4", "Task", "archivetask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
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
	* @apiSuccess {Number} id Id of the task delete
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - taskdelete - Complete Success"
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
		$task = $em->getRepository('MongoBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.5.4", "Task", "taskdelete", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.5.9", "Task", "taskdelete"));

		$em->remove($task);

		$em->flush();
		return $this->setSuccess("1.12.1", "Task", "taskdelete", "Complete Success", array("id" => $taskId));
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

		if ($content === null || (!array_key_exists('userId', $content) || !array_key_exists('token', $content) || !array_key_exists('taskId', $content)))
			return $this->setBadRequest("12.6.6", "Task", "assignusertotask", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("12.6.3", "Task", "assignusertotask"));

		$em = $this->getDoctrine()->getManager();
		$task = $em->getRepository('MongoBundle:Task')->find($content->taskId);

		if ($task === null)
			return $this->setBadRequest("12.6.4", "Task", "assignusertotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.6.9", "Task", "assignusertotask"));

		$userToAdd = $em->getRepository('MongoBundle:User')->find($content->userId);

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

		return $this->setSuccess("1.12.1", "Task", "assignusertotask", "Complete Success",
			array("id" => $task->getId(), "user" => array("id" => $userToAdd->getId(), "firstname" => $userToAdd->getFirstname(), "lastname" => $userToAdd->getLastname())));
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
	* @apiSuccess {Number} id Id of the user removed
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - removeusertotask - Complete Success"
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
		$task = $em->getRepository('MongoBundle:Task')->find($taskId);

		if ($task === null)
			return $this->setBadRequest("12.7.4", "Task", "removeusertotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.7.9", "Task", "removeusertotask"));

		$userToRemove = $em->getRepository('MongoBundle:User')->find($userId);

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

		return $this->setSuccess("1.12.1", "Task", "removeusertotask", "Complete Success", array("id" => $userId));
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

		if (!$this->checkRoles($user, $content->projectId, "task"))
			return ($this->setNoRightsError("12.8.9", "Task", "tagcreation"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
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
		$tag = $em->getRepository('MongoBundle:Tag')->find($content->tagId);
		if ($tag === null)
			return $this->setBadRequest("12.9.4", "Task", "tagupdate", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
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
		$tag = $em->getRepository('MongoBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.10.4", "Task", "taginformations", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
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
  	* @apiSuccess {Number} id Id of the deleted tag
  	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - deletetag - Complete Success"
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
		$tag = $em->getRepository('MongoBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.11.4", "Task", "deletetag", "Bad Parameter: tagId");

		if (!$this->checkRoles($user, $tag->getProject()->getId(), "task"))
			return ($this->setNoRightsError("12.11.9", "Task", "deletetag"));

		$em->remove($tag);
		$em->flush();

		return $this->setSuccess("1.12.1", "Task", "deletetag", "Complete Success", array("id" => $tagId));
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
		$task = $em->getRepository('MongoBundle:Task')->find($content->taskId);
		if ($task === null)
			return $this->setBadRequest("12.12.4", "Task", "assigntagtotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.12.9", "Task", "assigntagtotask"));

		$tagToAdd = $em->getRepository('MongoBundle:Tag')->find($content->tagId);
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
	* @apiSuccess {Number} id Id of the tag removed
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.12.1",
	*			"return_message": "Task - removetagtotask - Complete Success"
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
		$task = $em->getRepository('MongoBundle:Task')->find($taskId);
		if ($task === null)
			return $this->setBadRequest("12.13.4", "Task", "removetagtotask", "Bad Parameter: taskId");

		$projectId = $task->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.13.9", "Task", "removetagtotask"));

		$tagToRemove = $em->getRepository('MongoBundle:Tag')->find($tagId);
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

		return $this->setSuccess("1.12.1", "Task", "removetagtotask", "Complete Success", array("id" => $tagId));
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
	* @apiSuccess {Datetime} array.due_date Due date of the task
	* @apiSuccess {Datetime} array.started_at Date of start of the task
	* @apiSuccess {Datetime} array.finished_at Date of finish of the task
	* @apiSuccess {Datetime} array.created_at Date of creation of the task
	* @apiSuccess {Datetime} array.started_at Date of start of the task
	* @apiSuccess {Object[]} array.creator Creator informations
	* @apiSuccess {Number} array.creator.id Id of the creator
	* @apiSuccess {String} array.creator.first_name Frist name of the creator
	* @apiSuccess {String} array.creator.last_name Last name of the creator
	* @apiSuccess {Object[]} array.users_assigned Array of users assigned to the task
	* @apiSuccess {Number} array.users_assigned.id Id of the user assigned
	* @apiSuccess {String} array.users_assigned.first_name Frist name of the user assigned
	* @apiSuccess {String} array.users_assigned.last_name Last name of the user assigned
	* @apiSuccess {Object[]} array.tags Array of tags assigned to the task
	* @apiSuccess {Number} array.tags.id Id of the tag
	* @apiSuccess {String} array.tags.name Name of the tag
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
	*					"due_date":
	*					{
	*						"date":"2015-10-15 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					},
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
	*						},
	*						{
	*							"id": 3,
	*							"firstname": "jane",
	*							"lastname": "doe"
	*						}
	*					],
	*					"tags": [
	*						{
	*							"id": 1,
	*							"name": "To Do"
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

		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.14.9", "Task", "getprojecttasks"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('MongoBundle:Task');
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
			foreach ($users as $u) {
				$uid = $u->getId();
				$firstname = $u->getFirstname();
				$lastname = $u->getLastname();

				$userArray[] = array("id" => $uid, "first_name" => $firstname, "last_name" => $lastname);
			}

			$tagArray = array();
			foreach ($tags as $t) {
				$tid = $t->getId();
				$name = $t->getName();

				$tagArray[] = array("id" => $tid, "name" => $name);
			}

			$arr[] = array("id" => $id, "title" => $title, "description" => $description, "due_date" => $dueDate, "started_at" => $startedAt, "finished_at" => $finishedAt,
				"created_at" => $createdAt, "deleted_at" => $deletedAt, "creator" => $creatorInfos, "users_assigned" => $userArray, "tags" => $tagArray);
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

		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.15.9", "Task", "getprojecttags"));

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('MongoBundle:Tag');
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
