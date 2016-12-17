<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use SQLBundle\Controller\RolesAndTokenVerificationController;
use SQLBundle\Entity\StatProjectAdvancement;
use SQLBundle\Entity\StatLateTasks;
use SQLBundle\Entity\StatBugsEvolution;
use SQLBundle\Entity\StatBugsTagsRepartition;
use SQLBundle\Entity\StatBugAssignationTracker;
use SQLBundle\Entity\StatBugsUsersRepartition;
use SQLBundle\Entity\StatTasksRepartition;
use SQLBundle\Entity\StatUserWorkingCharge;
use SQLBundle\Entity\StatUserTasksAdvancement;
use SQLBundle\Entity\StatStorageSize;
use DateTime;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *  @IgnoreAnnotation("apiDescription")
 *  @IgnoreAnnotation("apiVersion")
 *  @IgnoreAnnotation("apiSuccess")
 *  @IgnoreAnnotation("apiSuccessExample")
 *  @IgnoreAnnotation("apiError")
 *  @IgnoreAnnotation("apiErrorExample")
 *  @IgnoreAnnotation("apiParam")
 *  @IgnoreAnnotation("apiParamExample")
 *  @IgnoreAnnotation("apiHeader")
 *  @IgnoreAnnotation("apiHeaderExample")
 */
class StatisticController extends RolesAndTokenVerificationController
{

  // -----------------------------------------------------------------------
  //                       CLIENT REQUESTS
  // -----------------------------------------------------------------------

	/**
	* @api {get} /0.3/statistics/:projectId Get all statistics
	* @apiName getAllStat
	* @apiGroup Stat
	* @apiDescription Get all statistics of a project.
	* See ["API - Statistic Content Description"](https://docs.google.com/document/d/1BqN96XF1GJmVVYN-NALbyzE8d_UVp3LHhli15B1OBJU/edit) for more details about the return content related to each statistic
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} projectId id of the related project
	*
	* @apiSuccess {Object[]/Integer} statName stat data asked
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.16.1",
	*			"return_message": "Stat - getAll - Complete Success"
	*		},
	*		"data": {
	*		  "projectTimeLimits": {...},
	*		  "clientBugTracker": 12,
	*		  ...
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "16.1.3",
	*			"return_message": "Stat - getAll - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "16.1.4",
	*			"return_message": "Stat - getAll - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function getAllStatAction(Request $request, $projectId)
	{
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("16.1.4", "Stat", "getAll", "Bad Parameter: projectId");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return $this->setBadTokenError("16.1.3", "Stat", "getAll");


		$stat["projectTimeLimits"] = $this->getProjectTimeLimits($project);

		$stat["timelinesMessageNumber"] = $this->getTimelinesMessageNumber($project);

		$stat["customerAccessNumber"] = $this->getCustomerAccessNumber($project);

		$stat["openCloseBug"] = $this->getOpenCloseBug($project);

		$stat["taskStatus"] = $this->getTaskStatus($project);

		$stat["totalTasks"] = $this->getTotalTasks($project);

		$stat["clientBugTracker"] = $this->getClientBugTracker($project);

		$data = $em->getRepository('SQLBundle:StatStorageSize')->findBy(array("project" => $project));
		if (!count($data))
			$stat["storageSize"] = array("occupied" => 0, "total" => 1000000000);
		else
			$stat["storageSize"] = array("occupied" => $data[0]->getValue(), "total" => 1000000000);

		$stat["userTasksAdvancement"] = array();
		$data = $em->getRepository('SQLBundle:StatUserTasksAdvancement')->findBy(array("project" => $project));
		foreach ($data as $key => $value) {
			$stat["userTasksAdvancement"][] = $value->objectToArray();
		}

		$stat["lateTask"] = array();
		$data = $em->getRepository('SQLBundle:StatLateTasks')->findBy(array("project" => $project));
		foreach ($data as $key => $value) {
			$stat["lateTask"][] = $value->objectToArray();
		}

		$stat["bugsEvolution"] = array();
		$data = $em->getRepository('SQLBundle:StatBugsEvolution')->findBy(array("project" => $project));
		foreach ($data as $key => $value) {
			$stat["bugsEvolution"][] = $value->objectToArray();
		}

		$stat["bugsTagsRepartition"] = array();
		$data = $em->getRepository('SQLBundle:StatBugsTagsRepartition')->findBy(array("project" => $project));
		foreach ($data as $key => $value) {
			$stat["bugsTagsRepartition"][] = $value->objectToArray();
		}

		$data = $em->getRepository('SQLBundle:StatBugAssignationTracker')->findBy(array("project" => $project));
		if (!count($data))
			$stat["bugAssignationTracker"] = array("assigned" => 0, "unassigned" => 0);
		else
			$stat["bugAssignationTracker"] = array("assigned" => $data[0]->getAssignedBugs(), "unassigned" => $data[0]->getUnassignedBugs());

		$stat["bugsUsersRepartition"] = array();
		$data = $em->getRepository('SQLBundle:StatBugsUsersRepartition')->findBy(array("project" => $project));
		foreach ($data as $key => $value) {
			$stat["bugsUsersRepartition"][] = $value->objectToArray();
		}

		$stat["tasksRepartition"] = array();
		$data = $em->getRepository('SQLBundle:StatTasksRepartition')->findBy(array("project" => $project));
		foreach ($data as $key => $value) {
			$stat["tasksRepartition"][] = $value->objectToArray();
		}

		$stat["userWorkingCharge"] = array();
		$data = $em->getRepository('SQLBundle:StatUserWorkingCharge')->findBy(array("project" => $project));
		foreach ($data as $key => $value) {
			$stat["userWorkingCharge"][] = $value->objectToArray();
		}

		$stat["projectAdvancement"] = array();
		$data = $em->getRepository('SQLBundle:StatProjectAdvancement')->findBy(array("project" => $project));
		foreach ($data as $key => $value) {
			$stat["projectAdvancement"][] = $value->objectToArray();
		}

		return $this->setSuccess("1.16.1", "Stat", "getAll", "Complete Success", $stat);
	}

	/**
	* @api {get} /0.3/statistic/:projectId/:statName Get a stat info
	* @apiName getStat
	* @apiGroup Stat
	* @apiDescription Get a particaular statistics info.
	* See ["API - Statistic Content Description"](https://docs.google.com/document/d/1BqN96XF1GJmVVYN-NALbyzE8d_UVp3LHhli15B1OBJU/edit) for more details about statName and return content related to each statistic
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {int} projectId id of the related project
	* @apiParam {string} statName name of the statistic
	*
	* @apiSuccess {Object[]/Integer} statName stat data asked
	*
	* @apiSuccessExample Success-Object-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.16.1",
	*			"return_message": "Stat - getStat - Complete Success"
	*		},
	*		"data": {
	*		 "projectTimeLimits": {...}
	*		}
	*	}
	* @apiSuccessExample Success-Integer-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.16.1",
	*			"return_message": "Stat - getStat - Complete Success"
	*		},
	*		"data": {
	*		 "clientBugTracker": 12
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "16.2.3",
	*			"return_message": "Stat - getStat - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "16.2.4",
	*			"return_message": "Stat - getStat - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: statName
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "16.2.4",
	*			"return_message": "Stat - getStat - Bad Parameter: statName"
	*		}
	*	}
	*/
	public function getStatAction(Request $request, $projectId, $statName)
	{
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("16.2.4", "Stat", "getStat", "Bad Parameter: projectId");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return $this->setBadTokenError("16.2.3", "Stat", "getStat");

		$stat = array();
		switch ($statName) {
			case 'projecttimelimits':
				$stat["projectTimeLimits"] = $this->getProjectTimeLimits($project);
				break;
			case 'timelinesmessagenumber':
				$stat["timelinesMessageNumber"] = $this->getTimelinesMessageNumber($project);
				break;
			case 'customeraccessnumber':
				$stat["customerAccessNumber"] = $this->getCustomerAccessNumber($project);
				break;
			case 'openclosebug':
				$stat["openCloseBug"] = $this->getOpenCloseBug($project);
				break;
			case 'taskstatus':
				$stat["taskStatus"] = $this->getTaskStatus($project);
				break;
			case 'totaltasks':
				$stat["totalTasks"] = $this->getTotalTasks($project);
				break;
			case 'clientbugtracker':
				$stat["clientBugTracker"] = $this->getClientBugTracker($project);
				break;
			case 'storagesize':
				$data = $em->getRepository('SQLBundle:StatStorageSize')->findBy(array("project" => $project));
				if (!count($data))
					$stat["storageSize"] = array("occupied" => 0, "total" => 1000000000);
				else
					$stat["storageSize"] = array("occupied" => $data[0]->getValue(), "total" => 1000000000);
				break;
			case 'usertasksadvancement':
				$stat["userTasksAdvancement"] = array();
				$data = $em->getRepository('SQLBundle:StatUserTasksAdvancement')->findBy(array("project" => $project));
				foreach ($data as $key => $value) {
					$stat["userTasksAdvancement"][] = $value->objectToArray();
				}
				break;
			case 'latetask':
				$stat["lateTask"] = array();
				$data = $em->getRepository('SQLBundle:StatLateTasks')->findBy(array("project" => $project));
				foreach ($data as $key => $value) {
					$stat["lateTask"][] = $value->objectToArray();
				}
				break;
			case 'bugsevolution':
				$stat["bugsEvolution"] = array();
				$data = $em->getRepository('SQLBundle:StatBugsEvolution')->findBy(array("project" => $project));
				foreach ($data as $key => $value) {
					$stat["bugsEvolution"][] = $value->objectToArray();
				}
				break;
			case 'bugstagsrepartition':
				$stat["bugsTagsRepartition"] = array();
				$data = $em->getRepository('SQLBundle:StatBugsTagsRepartition')->findBy(array("project" => $project));
				foreach ($data as $key => $value) {
				  $stat["bugsTagsRepartition"][] = $value->objectToArray();
				}
				break;
			case 'bugassignationtracker':
				$data = $em->getRepository('SQLBundle:StatBugAssignationTracker')->findBy(array("project" => $project));
				if (!count($data))
					$stat["bugAssignationTracker"] = array("assigned" => 0, "unassigned" => 0);
				else
					$stat["bugAssignationTracker"] = array("assigned" => $data[0]->getAssignedBugs(), "unassigned" => $data[0]->getUnassignedBugs());
				break;
			case 'bugsusersrepartition':
				$stat["bugsUsersRepartition"] = array();
				$data = $em->getRepository('SQLBundle:StatBugsUsersRepartition')->findBy(array("project" => $project));
				foreach ($data as $key => $value) {
					$stat["bugsUsersRepartition"][] = $value->objectToArray();
				}
				break;
			case 'tasksrepartition':
				$stat["tasksRepartition"] = array();
				$data = $em->getRepository('SQLBundle:StatTasksRepartition')->findBy(array("project" => $project));
				foreach ($data as $key => $value) {
					$stat["tasksRepartition"][] = $value->objectToArray();
				}
				break;
			case 'userworkingcharge':
				$stat["userWorkingCharge"] = array();
				$data = $em->getRepository('SQLBundle:StatUserWorkingCharge')->findBy(array("project" => $project));
				foreach ($data as $key => $value) {
					$stat["userWorkingCharge"][] = $value->objectToArray();
				}
				break;
			case 'projectadvancement':
				$stat["projectAdvancement"] = array();
				$data = $em->getRepository('SQLBundle:StatProjectAdvancement')->findBy(array("project" => $project));
				foreach ($data as $key => $value) {
					$stat["projectAdvancement"][] = $value->objectToArray();
				}
				break;
			default:
				return $this->setBadRequest("16.2.4", "Stat", "getStat", "Bad Parameter: statName");
				break;
		}

		return $this->setSuccess("1.16.1", "Stat", "getStat", "Complete Success", $stat);
	}



  // -----------------------------------------------------------------------
  //                 STATISTICS DATA - INSTANT VALUES
  // -----------------------------------------------------------------------

	private function getProjectTimeLimits($project)
	{
		$em = $this->getDoctrine()->getManager();
		$dueDate = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
						->select('t.dueDate')
						->where('t.projects = :project')
						->orderBy('t.dueDate', 'DESC')
						->setParameter('project', $project)
						->setMaxResults(1)
						->getQuery()->getResult();

		if ($dueDate == null)
			$dueDate = null;
		else
			$dueDate = $dueDate[0];

		return array('projectStart' => $project->getCreatedAt(), 'projectEnd' => $dueDate['dueDate']);
	}

	private function getTimelinesMessageNumber($project)
	{
		$em = $this->getDoctrine()->getManager();

		$result = array();
		foreach ($project->getTimelines() as $key => $timeline) {
			$result[($timeline->getTypeId() == 1 ? 'customer' : 'team')] = $em->getRepository('SQLBundle:TimelineMessage')->createQueryBuilder('t')
												->select('count(t)')
												->where('t.timelines = :timeline')
												->setParameters(array('timeline' => $timeline))
												->getQuery()->getSingleScalarResult();
		}

		return $result;
	}

	private function getCustomerAccessNumber($project)
	{
		$em = $this->getDoctrine()->getManager();

		$result['actual'] = $project->getCustomersAccess()->count();

		$result['maximum'] = 10;

		return $result;
	}

	private function getOpenCloseBug($project)
	{
		$em = $this->getDoctrine()->getManager();

		$result['open'] = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
								->select('count(b)')
								->where('b.state = true AND b.projects = :project')
								->setParameters(array('project' => $project))
								->getQuery()->getSingleScalarResult();

		$result['closed'] = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
								->select('count(b)')
								->where('b.state = false AND b.projects = :project')
								->setParameters(array('project' => $project))
								->getQuery()->getSingleScalarResult();

		return $result;
	}

	private function getTaskStatus($project)
	{
		$em = $this->getDoctrine()->getManager();
		$date = new DateTime('now');

		$result['done'] = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
								->select('count(t)')
								->where('t.finishedAt IS NOT NULL AND t.projects = :project')
								->setParameters(array('project' => $project))
								->getQuery()->getSingleScalarResult();

		$result['doing'] = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
								->select('count(t)')
								->where('t.finishedAt IS NULL AND t.startedAt IS NOT NULL AND t.dueDate > :date AND t.projects = :project')
								->setParameters(array('project' => $project, 'date' => $date))
								->getQuery()->getSingleScalarResult();

		$result['toDo'] = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
								->select('count(t)')
								->where('t.startedAt IS NULL AND t.dueDate > :date AND t.projects = :project')
								->setParameters(array('project' => $project, 'date' => $date))
								->getQuery()->getSingleScalarResult();

		$result['late'] = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
								->select('count(t)')
								->where('t.finishedAt IS NULL AND t.dueDate <= :date AND t.projects = :project')
								->setParameters(array('project' => $project, 'date' => $date))
								->getQuery()->getSingleScalarResult();

		return $result;
	}

	private function getTotalTasks($project)
	{
		$em = $this->getDoctrine()->getManager();

		$result = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
						->select('count(t)')
						->where('t.projects = :project')
						->setParameters(array('project' => $project))
						->getQuery()->getSingleScalarResult();

		return $result;
	}

	private function getClientBugTracker($project)
	{
		$em = $this->getDoctrine()->getManager();

		$result = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
						->select('count(b)')
						->where('b.projects = :project AND b.clientOrigin = TRUE')
						->andWhere('b.state = true')
						->setParameters(array('project' => $project))
						->getQuery()->getSingleScalarResult();

		return $result;
	}


	// -----------------------------------------------------------------------
	//                    STATISTICS DATA - CUSTOM UPDATE
	// -----------------------------------------------------------------------

	public function manuallyUpdateStatAction() {
			$em = $this->getDoctrine()->getManager();
			$projects = $em->getRepository('SQLBundle:Project')->findBy(array('deletedAt' => NULL));

			$result = array();
			foreach ($projects as $key => $project) {
				$result['UserTasksAdvancement'] = $this->updateUserTasksAdvancement($project);
				$result['UserWorkingCharge'] = $this->updateUserWorkingCharge($project);
				$result['TasksRepartition'] = $this->updateTasksRepartition($project);
				$result['BugsUsersRepartition'] = $this->updateBugsUsersRepartition($project);
				$result['BugAssignationTracker'] = $this->updateBugAssignationTracker($project);
				$result['BugsTagsRepartition'] = $this->updateBugsTagsRepartition($project);
			}
			return $this->setSuccess("1.16.1", "Stat", "customUpdate", "Complete Success", $result);
	}

	public function updateStat($projectId, $statName)
	{
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);

		if ($project === null)
			return "Error: Bad project Id";

		switch ($statName) {
			case 'UserTasksAdvancement':
				$this->updateUserTasksAdvancement($project);
				break;
			case 'UserWorkingCharge':
				$this->updateUserWorkingCharge($project);
				break;
			case 'TasksRepartition':
				$this->updateTasksRepartition($project);
				break;
			case 'BugsUsersRepartition':
				$this->updateBugsUsersRepartition($project);
				break;
			case 'BugAssignationTracker':
				$this->updateBugAssignationTracker($project);
				break;
			case 'BugsTagsRepartition':
				$this->updateBugsTagsRepartition($project);
				break;
			default:
				return "Error: Bad statName";
				break;
		}

		return "Success: Stat '".$statName."' updated.";
	}

	private function updateUserTasksAdvancement($project)
	{
		$em = $this->getDoctrine()->getManager();
		$date = new DateTime('now');
		$users = $project->getUsers();

		foreach ($users as $key => $user) {
			$result['Done'] = 0;
			$result['ToDo'] = 0;
			$result['Doing'] = 0;
			$result['Late'] = 0;
			$resources = $user->getRessources();
			foreach ($resources as $key => $res) {
				$task = $res->getTask();
				if ($task->getProjects()->getId() == $project->getId())
				{
					if($task->getAdvance() == 100)
						$result['Done'] += 1;
					else if ($task->getAdvance() == 0)
						$result['ToDo'] += 1;
					else if ($task->getAdvance() > 0 && $task->getAdvance() < 100)
						$result['Doing'] += 1;
					else if ($task->getAdvance() < 100 && $task->getDueDate() <= $date)
						$result['Late'] += 1;
				}
			}

			$statUserTasksAdvancement = $em->getRepository('SQLBundle:StatUserTasksAdvancement')->findOneBy(array('project' => $project, 'user' => $user));
			if (!$statUserTasksAdvancement instanceof StatUserTasksAdvancement)
			{
				$statUserTasksAdvancement = new StatUserTasksAdvancement();
				$statUserTasksAdvancement->setProject($project);
				$statUserTasksAdvancement->setUser($user);
			}
			$statUserTasksAdvancement->setTasksToDo($result['ToDo']);
			$statUserTasksAdvancement->setTasksDoing($result['Doing']);
			$statUserTasksAdvancement->setTasksDone($result['Done']);
			$statUserTasksAdvancement->setTasksLate($result['Late']);

			$em->persist($statUserTasksAdvancement);
			$em->flush();
		}

		$stats = $em->getRepository('SQLBundle:StatUserTasksAdvancement')->findBy(array('project' => $project));
		foreach ($stats as $key => $stat) {
			$isPresent = false;
			foreach ($users as $key2 => $user) {
				if ($user->getId() == $stat->getUser()->getId())
					$isPresent = true;
			}
			if (!$isPresent) {
				$em->remove($stat);
				$em->flush();
			}
		}

		return "Data updated";
	}

	private function updateUserWorkingCharge($project)
	{
		$em = $this->getDoctrine()->getManager();

		$users = $project->getUsers();

		foreach ($users as $key => $user) {
			$charge = 0;
			$resources = $user->getRessources();
			foreach ($resources as $key => $res) {
				$task = $res->getTask();
					if ($task->getProjects()->getId() == $project->getId())
						$charge += $res->getResource();
			}

			$statUserWorkingCharge = $em->getRepository('SQLBundle:StatUserWorkingCharge')->findOneBy(array('project' => $project, 'user' => $user));
			if (!$statUserWorkingCharge instanceof statUserWorkingCharge)
			{
				$statUserWorkingCharge = new StatUserWorkingCharge();
				$statUserWorkingCharge->setProject($project);
				$statUserWorkingCharge->setUser($user);
			}
			$statUserWorkingCharge->setCharge($charge);

			$em->persist($statUserWorkingCharge);
			$em->flush();
		}

		$stats = $em->getRepository('SQLBundle:StatUserWorkingCharge')->findBy(array('project' => $project));
		foreach ($stats as $key => $stat) {
			$isPresent = false;
			foreach ($users as $key2 => $user) {
				if ($user->getId() == $stat->getUser()->getId())
					$isPresent = true;
			}
			if (!$isPresent) {
				$em->remove($stat);
				$em->flush();
			}
		}

		return "Data updated";
	}

	private function updateTasksRepartition($project)
	{
		$em = $this->getDoctrine()->getManager();

		$users = $project->getUsers();

		$tasks = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
					->where("t.projects = :project")
					->setParameters(array('project' => $project))
					->getQuery()->getResult();

		foreach ($users as $key => $user) {
			$number = 0;
			$role = $em->getRepository('SQLBundle:ProjectUserRole')->createQueryBuilder('u')
						->select('r.name')
						->join('SQLBundle\Entity\Role', 'r', 'WITH', 'r.id = u.roleId')
						->where('u.projectId = :projectId')
						->setParameter('projectId', $project->getId())
						->setMaxResults(1)
						->getQuery()->getResult();

			foreach ($tasks as $key => $task) {
				foreach ($task->getRessources() as $key => $res) {
					if ($res->getUser()->getId() == $user->getId())
						$number += 1;
				}
			}

			if (count($tasks) != 0)
				$percentage = ($number * 100) / count($tasks);
			else {
				$percentage = 0;
			}

			$statTasksRepartition = $em->getRepository('SQLBundle:StatTasksRepartition')->findOneBy(array('project' => $project, 'user' => $user));
			if (!$statTasksRepartition instanceof StatTasksRepartition)
			{
				$statTasksRepartition = new StatTasksRepartition();
				$statTasksRepartition->setProject($project);
				$statTasksRepartition->setUser($user);
				$statTasksRepartition->setRole($role[0]['name']);
			}
			$statTasksRepartition->setValue($number);
			$statTasksRepartition->setPercentage($percentage);

			$em->persist($statTasksRepartition);
			$em->flush();
		}
		return "Data updated";
	}

	private function updateBugsUsersRepartition($project)
	{
		$em = $this->getDoctrine()->getManager();

		$users = $project->getUsers();

		$totalBugs = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
						->select('count(b)')
						->where("b.projects = :project")
						->setParameters(array('project' => $project))
						->getQuery()->getSingleScalarResult();

		foreach ($users as $key => $user) {
			if ($totalBugs != 0)
			{
				$number = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
								->select('count(b)')
								->where("b.projects = :project")
								->andWhere(':user MEMBER OF b.users')
								->setParameters(array('project' => $project, 'user' => $user))
								->getQuery()->getSingleScalarResult();

				$percentage = ($number * 100) / $totalBugs;
			}
			else {
				$number = 0;
				$percentage = 0;
			}

			$statBugsUsersRepartition = $em->getRepository('SQLBundle:StatBugsUsersRepartition')->findOneBy(array('project' => $project, "user" => $user));
			if (!$statBugsUsersRepartition instanceof StatBugsUsersRepartition)
			{
				$statBugsUsersRepartition = new StatBugsUsersRepartition();
				$statBugsUsersRepartition->setProject($project);
				$statBugsUsersRepartition->setUser($user);
			}
			$statBugsUsersRepartition->setValue($number);
			$statBugsUsersRepartition->setPercentage($percentage);

			$em->persist($statBugsUsersRepartition);
			$em->flush();
		}

		$stats = $em->getRepository('SQLBundle:StatBugsUsersRepartition')->findBy(array('project' => $project));
		foreach ($stats as $key => $stat) {
			$isPresent = false;
			foreach ($users as $key2 => $user) {
				if ($user->getId() == $stat->getUser()->getId())
					$isPresent = true;
			}
			if (!$isPresent) {
				$em->remove($stat);
				$em->flush();
			}
		}

		return 'Data updated';
	}

	private function updateBugAssignationTracker($project)
	{
		$em = $this->getDoctrine()->getManager();

		$bugs = $em->getRepository('SQLBundle:Bug')->findBy(array('projects' => $project, 'state' => true));

		$assigned = 0;
		$unassigned = 0;
		foreach ($bugs as $key => $bug) {
			if($bug->getUsers() != null)
				$assigned += 1;
			else
				$unassigned += 1;
		}

		$statBugAssignationTracker = $em->getRepository('SQLBundle:StatBugAssignationTracker')->findOneBy(array('project' => $project));
		if ($statBugAssignationTracker === null)
		{
			$statBugAssignationTracker = new StatBugAssignationTracker();
			$statBugAssignationTracker->setProject($project);
		}
		$statBugAssignationTracker->setAssignedBugs($assigned);
		$statBugAssignationTracker->setUnassignedBugs($unassigned);

		$em->persist($statBugAssignationTracker);
		$em->flush();

		return array('assignedBug' => $assigned, "unassignedBug" => $unassigned);
	}

	private function updateBugsTagsRepartition($project)
	{
		$em = $this->getDoctrine()->getManager();

		$tags = $em->getRepository('SQLBundle:BugtrackerTag')->findBy(array('project' => $project));

		$totalBugs = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('t')
						->select('count(t)')
						->where("t.projects = :project")
						->setParameters(array('project' => $project))
						->getQuery()->getSingleScalarResult();

		foreach ($tags as $key => $tag) {
			$number = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('t')
							->select('count(t)')
							->where("t.projects = :project")
							->andWhere(":tag MEMBER OF t.bugtracker_tags")
							->setParameters(array('project' => $project, 'tag' => $tag))
							->getQuery()->getSingleScalarResult();

			if ($totalBugs > 0)
				$percentage = ($number * 100) / $totalBugs;
			else
				$percentage = 0;

			$statBugsTagsRepartition = $em->getRepository('SQLBundle:StatBugsTagsRepartition')->findOneBy(array('project' => $project, 'name' => $tag->getName()));
			if ($statBugsTagsRepartition === null)
			{
				$statBugsTagsRepartition = new StatBugsTagsRepartition();
				$statBugsTagsRepartition->setProject($project);
				$statBugsTagsRepartition->setName($tag->getName());
			}
			$statBugsTagsRepartition->setValue($number);
			$statBugsTagsRepartition->setPercentage($percentage);

			$em->persist($statBugsTagsRepartition);
			$em->flush();
		}
	}

	// ------------ CLOUD STATISTICS UPDATE METHODS --------------------

	public function updateCloudStat($projectId, $token, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);

		if ($project === null)
			return "Error: Bad project Id";

		$this->updateStorageSize($project, $request);
		return "Success: Stat 'StorrageSize' updated.";
	}

	private function updateStorageSize($project, Request $request)
	{
		$res = $this->calculateStorageSize($project, ",", $request);

		$em = $this->getDoctrine()->getManager();
		$statStorageSize = $em->getRepository('SQLBundle:StatStorageSize')->findOneBy(array('project' => $project));

		if ($statStorageSize === null)
		{
			$statStorageSize = new StatStorageSize();
			$statStorageSize->setProject($project);
			$statStorageSize->setValue(0);
		}

		if ($res["result"] != "error")
		{
			$statStorageSize->setValue($res["data"]);
			$em->persist($statStorageSize);
			$em->flush();
		}

		return "Data updated";
	}

	private function calculateStorageSize($project, $path, Request $request)
	{
		$response = $this->get('service_cloud')->getListAction($project->getId(), $path, $project->getSafePassword(), $request);
		$response = json_decode($response->getContent());

		if ($response->info->return_code != "1.3.1")
			return array("result" => "error", "data" => $response);

		$results = $response->data->array;
		$size = 0;

		foreach ($results as $key => $result) {
			if ($result->type == "dir")
			{
				$newPath = $path.$result->filename.",";
				$subResult = $this->calculateStorageSize($project, $newPath, $request);

				if ($subResult['result'] == "error")
				  return $subResult;
				else
				  $size += $subResult['data'];
			}
			else
				$size += $result->size;
		}

		return array("result" => "success", "data" => $size);
	}


	// -----------------------------------------------------------------------
	//                    STATISTICS DATA - DAILY UPDATE
	// -----------------------------------------------------------------------

	public function dailyUpdateAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$projects = $em->getRepository('SQLBundle:Project')->findBy(array('deletedAt' => NULL));

		$result = array();
		foreach ($projects as $key => $project) {
			$result['BugsEvolution'] = $this->updateBugsEvolution($project);
			$result['LateTasks'] = $this->updateLateTasks($project);
		}
		return $this->setSuccess("1.16.1", "Stat", "dailyUpdate", "Complete Success", $result);
	}

	private function updateLateTasks($project)
	{
		$em = $this->getDoctrine()->getManager();

		$users = $project->getUsers();

		$ontimeProjectTasks = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
									->where('t.projects = :project')
									->andWhere('t.deletedAt IS NULL')
									->andWhere('t.finishedAt IS NOT NULL')
									->andWhere('t.finishedAt <= t.dueDate')
									->setParameters(array('project' => $project))
									->getQuery()->getResult();

		$lateProjectTasks = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
								->where('t.projects = :project')
								->andWhere('t.deletedAt IS NULL')
								->andWhere('(t.finishedAt IS NOT NULL AND t.finishedAt > t.dueDate) OR (t.finishedAt IS NULL AND t.dueDate < :now)')
								->setParameters(array('project' => $project, 'now' => new DateTime('now')))
								->getQuery()->getResult();

		$result = array();
		foreach ($users as $key => $user) {
			$ontimeTasks = 0;
			$lateTasks = 0;
			$role = $em->getRepository('SQLBundle:ProjectUserRole')->createQueryBuilder('u')
						->select('r.name')
						->join('SQLBundle\Entity\Role', 'r', 'WITH', 'r.id = u.roleId')
						->where('u.projectId = :projectId')
						->setParameter('projectId', $project->getId())
						->setMaxResults(1)
						->getQuery()->getResult();

			if ($role && $role[0]) {
				foreach ($ontimeProjectTasks as $key => $task) {
					foreach ($task->getRessources() as $key => $res) {
						if ($res->getUser()->getId() == $user->getId())
							$ontimeTasks += 1;
					}
				}

				foreach ($lateProjectTasks as $key => $task) {
					foreach ($task->getRessources() as $key => $res) {
						if ($res->getUser()->getId() == $user->getId())
							$lateTasks += 1;
					}
				}

				$statLateTasks = new statLateTasks();
				$statLateTasks->setProject($project);
				$statLateTasks->setUser($user);
				$statLateTasks->setRole($role[0]['name']);
				$statLateTasks->setLateTasks($lateTasks);
				$statLateTasks->setOntimeTasks($ontimeTasks);
				$statLateTasks->setDate(new DateTime('now'));

				$em->persist($statLateTasks);
				$em->flush();
			}
		}

		return "Data updated";
	}

	private function updateBugsEvolution($project)
	{
		// $em = $this->getDoctrine()->getManager();

		// $date = new DateTime('now');
		// //TODO remove one day

		// $createdBugs = $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
		//                ->select('count(b)')
		//                ->where("b.projects = :project")
		//                ->andWhere("b.createdAt BETWEEN :date_begin AND :date_end")
		//                ->setParameters(array('project' => $project, 'date_begin' => $date->format('Y-m-d').' 00:00:00', 'date_end' => $date->format('Y-m-d').' 23:59:59'))
		//                ->getQuery()->getSingleScalarResult();

		// $closedBugs =  $em->getRepository('SQLBundle:Bug')->createQueryBuilder('b')
		//                ->select('count(b)')
		//                ->where("b.projects = :project")
		//                ->andWhere("b.deletedAt BETWEEN :date_begin AND :date_end")
		//                ->setParameters(array('project' => $project, 'date_begin' => $date->format('Y-m-d').' 00:00:00', 'date_end' => $date->format('Y-m-d').' 23:59:59'))
		//                ->getQuery()->getSingleScalarResult();

		// $statBugsEvolution = new statBugsEvolution();
		// $statBugsEvolution->setProject($project);
		// $statBugsEvolution->setCreatedBugs($createdBugs);
		// $statBugsEvolution->setClosedbugs($closedBugs);
		// $statBugsEvolution->setDate($date);

		// $em->persist($statBugsEvolution);
		// $em->flush();

		return "Data updated";
	}


	// -----------------------------------------------------------------------
	//                    STATISTICS DATA - WEEKLY UPDATE
	// -----------------------------------------------------------------------

	public function weeklyUpdateAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$projects = $em->getRepository('SQLBundle:Project')->findBy(array('deletedAt' => NULL));

		$result = array();
		foreach ($projects as $key => $project) {
			$result["ProjectAdvancement"] = $this->updateProjectAdvancement($project);
		}
		return $this->setSuccess("1.16.1", "Stat", "weeklyUpdate", "Complete Success", $result);
	}

	private function updateProjectAdvancement($project)
	{
		$em = $this->getDoctrine()->getManager();

		$totalTasks = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
							->select('count(t)')
							->where('t.projects = :project')
							->andWhere('t.deletedAt IS NULL')
							->setParameter('project', $project)
							->getQuery()->getSingleScalarResult();

		$finishedTasks = $em->getRepository('SQLBundle:Task')->createQueryBuilder('t')
							->select('count(t)')
							->where('t.projects = :project')
							->andWhere('t.deletedAt IS NULL')
							->andWhere('t.finishedAt IS NOT NULL')
							->setParameter('project', $project)
							->getQuery()->getSingleScalarResult();

		if ($totalTasks != 0)
			$percentage = ($finishedTasks / $totalTasks) * 100;
		else
			$percentage = 0;

		$prev = $em->getRepository('SQLBundle:statProjectAdvancement')->findBy(array("project" => $project), array('date' => 'DESC'));
		if ($prev == null)
			$progress = $percentage;
		else
			$progress = $percentage - $prev[0]->getPercentage();

		$statProjectAdvancement = new StatProjectAdvancement();
		$statProjectAdvancement->setProject($project);
		$statProjectAdvancement->setTotalTasks($totalTasks);
		$statProjectAdvancement->setFinishedTasks($finishedTasks);
		$statProjectAdvancement->setPercentage($percentage);
		$statProjectAdvancement->setProgress($progress);
		$statProjectAdvancement->setDate(new DateTime('now'));

		$em->persist($statProjectAdvancement);
		$em->flush();

		return "Data updated";
	}


	// -----------------------------------------------------------------------
	//                    STATISTICS DATA - INITIATE PROJECT
	// -----------------------------------------------------------------------

	public function initiateStatistics($project, $token, $request)
	{
		// INITIATE CUSTOM UPDATE STAT
		$this->updateUserTasksAdvancement($project);
		$this->updateUserWorkingCharge($project);
		$this->updateTasksRepartition($project);
		$this->updateBugsUsersRepartition($project);
		$this->updateBugAssignationTracker($project);
		$this->updateBugsTagsRepartition($project);

		// INITIATE CLOUD UPDATE STAT
		$this->updateStorageSize($project, $request);

		// INITIATE DAILY UPDATE STAT
		$this->updateBugsEvolution($project);
		$this->updateTasksRepartition($project);

		// INITIATE WEEKLY UDATE STAT
		$this->updateProjectAdvancement($project);

		return "Statistics initiated";
	}

}
