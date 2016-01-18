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
	* @api {post} /mongo/tasks/taskcreation Create a task
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

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {put} /mongo/tasks/taskupdate Update a task
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

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {get} /mongo/tasks/taskinformations/:token/:taskId Get a task informations
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	*/
	public function getTaskInfosAction(Request $request, $token, $taskId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.3.3", "Task", "taskinformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {put} /mongo/tasks/archivetask Archive a task
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

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {delete} /mongo/tasks/taskdelete/:token/:taskId Delete a task
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	*/
	public function deleteTaskAction(Request $request, $token, $taskId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.5.3", "Task", "taskdelete"));

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {put} /mongo/tasks/assignusertotask Assign a user to a task
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

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {delete} /mongo/tasks/removeusertotask/:token/:taskId/:userId Remove a user to a task
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} userId Id of the user
	*/
	public function removeUserToTaskAction(Request $request, $token, $taskId, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.7.3", "Task", "removeusertotask"));

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {post} /mongo/tasks/tagcreation Create a tag
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

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {put} /mongo/tasks/tagupdate Update a tag
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

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {get} /mongo/tasks/taginformations/:token/:tagId Get a tag informations
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*/
	public function getTagInfosAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.10.3", "Task", "taginformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$tag = $em->getRepository('MongoBundle:Tag')->find($tagId);
		if ($tag === null)
			return $this->setBadRequest("12.10.4", "Task", "taginformations", "Bad Parameter: tagId");

		$projectId = $tag->getProject()->getId();
		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.10.9", "Task", "taginformations"));

		return $this->setSuccess("1.12.1", "Task", "taginformations", "Complete Success", array("id" => $tag->getId(), "name" => $tag->getName()));
	}

	/**
	* @api {delete} /mongo/tasks/deletetag/:token/:tagId Delete a tag
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} tagId Id of the tag
	*/
	public function deleteTagAction(Request $request, $token, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.11.3", "Task", "deletetag"));

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {put} /mongo/tasks/assigntagtotask Assign a tag to a task
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

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {delete} /mongo/tasks/removetagtotask/:token/:taskId/:tagId Remove a tag to a task
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} taskId Id of the task
	* @apiParam {Number} tagId Id of the tag
	*/
	public function removeTagToTaskAction(Request $request, $token, $taskId, $tagId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.13.3", "Task", "removetagtotask"));

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {get} /mongo/tasks/getprojecttasks/:token/:projectId Get all the tasks for a project
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*/
	public function getProjectTasksAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.14.3", "Task", "getprojecttasks"));

		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.14.9", "Task", "getprojecttasks"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Task');
		$qb = $repository->createQueryBuilder('t')->join('t.projects', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tasks = $qb->execute();
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
	* @api {get} /mongo/tasks/getprojecttags/:token/:projectId Get all the tags for a project
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*/
	public function getProjectTagsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("12.15.3", "Task", "getprojecttags"));

		if (!$this->checkRoles($user, $projectId, "task"))
			return ($this->setNoRightsError("12.15.9", "Task", "getprojecttags"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Tag');
		$qb = $repository->createQueryBuilder('t')->join('t.project', 'p')->where('p.id = :id')->setParameter('id', $projectId)->getQuery();
		$tags = $qb->execute();
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
