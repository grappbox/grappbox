<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MongoBundle\Controller\RolesAndTokenVerificationController;
use MongoBundle\Document\StatProjectAdvancement;
use MongoBundle\Document\StatLateTasks;
use MongoBundle\Document\StatBugsEvolution;
use MongoBundle\Document\StatBugsTagsRepartition;
use MongoBundle\Document\StatBugAssignationTracker;
use MongoBundle\Document\StatBugsUsersRepartition;
use MongoBundle\Document\StatTasksRepartition;
use MongoBundle\Document\StatUserWorkingCharge;
use MongoBundle\Document\StatUserTasksAdvancement;
use MongoBundle\Document\StatStorageSize;
use DateTime;

/**
*  @IgnoreAnnotation("apiName")
*  @IgnoreAnnotation("apiGroup")
*  @IgnoreAnnotation("apiVersion")
*  @IgnoreAnnotation("apiSuccess")
*  @IgnoreAnnotation("apiSuccessExample")
*  @IgnoreAnnotation("apiError")
*  @IgnoreAnnotation("apiErrorExample")
*  @IgnoreAnnotation("apiParam")
*  @IgnoreAnnotation("apiDescription")
*  @IgnoreAnnotation("apiParamExample")
*/
class StatisticController extends RolesAndTokenVerificationController
{

  // -----------------------------------------------------------------------
  //                       CLIENT REQUESTS
  // -----------------------------------------------------------------------

  /**
  * @-api {get} /0.3/statistics/:projectId Get all statistics
	* @apiName getAllStat
	* @apiGroup Stat
	* @apiDescription Get all statistics of a project.
	* See ["API - Statistic Content Description"](https://docs.google.com/document/d/1BqN96XF1GJmVVYN-NALbyzE8d_UVp3LHhli15B1OBJU/edit) for more details about the return content related to each statistic
	* @apiVersion 0.3.0
  *
  */
  public function getAllStatAction(Request $request, $projectId)
  {
    $em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("16.1.4", "Stat", "getAll", "Bad Parameter: projectId");

    $user = $this->checkToken($request->headers->get('Authorization'));
    if (!$user)
      return $this->setBadTokenError("16.1.3", "Stat", "getAll");

    // GET INSTANT DATA
    $stat["projectTimeLimits"] = $this->getProjectTimeLimits($project);

    $stat["timelinesMessageNumber"] = $this->getTimelinesMessageNumber($project);

    $stat["customerAccessNumber"] = $this->getCustomerAccessNumber($project);

    $stat["openCloseBug"] = $this->getOpenCloseBug($project);

    $stat["taskStatus"] = $this->getTaskStatus($project);

    $stat["totalTasks"] = $this->getTotalTasks($project);

    $stat["clientBugTracker"] = $this->getClientBugTracker($project);

    // GET CUSTOM UPDATE DATA
    $data = $em->getRepository('MongoBundle:StatStorageSize')->findBy(array("project.id" => $project->getId()));
    if (!count($data))
      $stat["storageSize"] = array("occupied" => 0, "total" => 1000000000);
    else
      $stat["storageSize"] = array("occupied" => $data[0]->getValue(), "total" => 1000000000);

    $stat["userTasksAdvancement"] = array();
    $data = $em->getRepository('MongoBundle:StatUserTasksAdvancement')->findBy(array("project.id" => $project->getId()));
    foreach ($data as $key => $value) {
      $stat["userTasksAdvancement"][] = $value->objectToArray();
    }

    $stat["bugsTagsRepartition"] = array();
    $data = $em->getRepository('MongoBundle:StatBugsTagsRepartition')->findBy(array("project.id" => $project->getId()));
    foreach ($data as $key => $value) {
      $stat["bugsTagsRepartition"][] = $value->objectToArray();
    }

    $data = $em->getRepository('MongoBundle:StatBugAssignationTracker')->findBy(array("project.id" => $project->getId()));
    if (!count($data))
      $stat["bugAssignationTracker"] = array("assigned" => 0, "unassigned" => 0);
    else
      $stat["bugAssignationTracker"] = array("assigned" => $data[0]->getAssignedBugs(), "unassigned" => $data[0]->getUnassignedBugs());

    $stat["bugsUsersRepartition"] = array();
    $data = $em->getRepository('MongoBundle:StatBugsUsersRepartition')->findBy(array("project.id" => $project->getId()));
    foreach ($data as $key => $value) {
      $stat["bugsUsersRepartition"][] = $value->objectToArray();
    }

    $stat["tasksRepartition"] = array();
    $data = $em->getRepository('MongoBundle:StatTasksRepartition')->findBy(array("project.id" => $project->getId()));
    foreach ($data as $key => $value) {
      $stat["tasksRepartition"][] = $value->objectToArray();
    }

    $stat["userWorkingCharge"] = array();
    $data = $em->getRepository('MongoBundle:StatUserWorkingCharge')->findBy(array("project.id" => $project->getId()));
    foreach ($data as $key => $value) {
      $stat["userWorkingCharge"][] = $value->objectToArray();
    }

    // GET DAILY UPDATE DATA
    $stat["lateTask"] = array();
    $data = $em->getRepository('MongoBundle:StatLateTasks')->createQueryBuilder()
                ->field('project.id')->equals($project->getId())
                ->getQuery()->execute();
    foreach ($data as $key => $value) {
      $stat["lateTask"][] = $value->objectToArray();
    }

    $stat["bugsEvolution"] = array();
    $data = $em->getRepository('MongoBundle:StatBugsEvolution')->createQueryBuilder()
                ->field('project.id')->equals($project->getId())
                ->getQuery()->execute();
    foreach ($data as $key => $value) {
      $stat["bugsEvolution"][] = $value->objectToArray();
    }

    // GET WEEKLY UPDATE DATA
    $stat["projectAdvancement"] = array();
    $data = $em->getRepository('MongoBundle:StatProjectAdvancement')->createQueryBuilder()
              ->field('project.id')->equals($project->getId())
              ->getQuery()->execute();
              //->findBy(array("project.id" => $project->getId()));
    foreach ($data as $key => $value) {
      $stat["projectAdvancement"][] = $value->objectToArray();
    }

    return $this->setSuccess("1.16.1", "Stat", "getAll", "Complete Success", $stat);
  }

  /**
  * @-api {get} /0.3/statistic/:projectId/:statName Get a stat info
	* @apiName getStat
	* @apiGroup Stat
	* @apiDescription Get a particaular statistics info.
	* See ["API - Statistic Content Description"](https://docs.google.com/document/d/1BqN96XF1GJmVVYN-NALbyzE8d_UVp3LHhli15B1OBJU/edit) for more details about statName and return content related to each statistic
	* @apiVersion 0.3.0
  *
  */
  public function getStatAction(Request $request, $projectId, $statName)
  {
    $em = $this->get('doctrine_mongodb')->getManager();
    $project = $em->getRepository('MongoBundle:Project')->find($projectId);
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
        $data = $em->getRepository('MongoBundle:StatStorageSize')->findBy(array("project.id" => $project->getId()));
        if (!count($data))
          $stat["storageSize"] = array("occupied" => 0, "total" => 1000000000);
        else
          $stat["storageSize"] = array("occupied" => $data[0]->getValue(), "total" => 1000000000);
        break;
      case 'usertasksadvancement':
        $stat["userTasksAdvancement"] = array();
        $data = $em->getRepository('MongoBundle:StatUserTasksAdvancement')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
        //->findBy(array("project.id" => $project->getId()));
        foreach ($data as $key => $value) {
          $stat["userTasksAdvancement"][] = $value->objectToArray();
        }
        break;
      case 'latetask':
        $stat["lateTask"] = array();
        $data = $em->getRepository('MongoBundle:StatLateTasks')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
        //->findBy(array("project.id" => $project->getId()));
        foreach ($data as $key => $value) {
          $stat["lateTask"][] = $value->objectToArray();
        }
        break;
      case 'bugsevolution':
        $stat["bugsEvolution"] = array();
        $data = $em->getRepository('MongoBundle:StatBugsEvolution')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
                  //->findBy(array("project.id" => $project->getId()));
        foreach ($data as $key => $value) {
          $stat["bugsEvolution"][] = $value->objectToArray();
        }
        break;
      case 'bugstagsrepartition':
        $stat["bugsTagsRepartition"] = array();
        $data = $em->getRepository('MongoBundle:StatBugsTagsRepartition')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
                  //->findBy(array("project.id" => $project->getId()));
        foreach ($data as $key => $value) {
          $stat["bugsTagsRepartition"][] = $value->objectToArray();
        }
        break;
      case 'bugassignationtracker':
        $data = $em->getRepository('MongoBundle:StatBugAssignationTracker')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
                  //->findBy(array("project.id" => $project->getId()));
        if (!count($data))
          $stat["bugAssignationTracker"] = array("assigned" => 0, "unassigned" => 0);
        else
          $stat["bugAssignationTracker"] = array("assigned" => $data[0]->getAssignedBugs(), "unassigned" => $data[0]->getUnassignedBugs());
        break;
      case 'bugsusersrepartition':
        $stat["bugsUsersRepartition"] = array();
        $data = $em->getRepository('MongoBundle:StatBugsUsersRepartition')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
                  //->findBy(array("project.id" => $project->getId()));
        foreach ($data as $key => $value) {
          $stat["bugsUsersRepartition"][] = $value->objectToArray();
        }
        break;
      case 'tasksrepartition':
        $stat["tasksRepartition"] = array();
        $data = $em->getRepository('MongoBundle:StatTasksRepartition')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
                  //->findBy(array("project.id" => $project->getId()));
        foreach ($data as $key => $value) {
          $stat["tasksRepartition"][] = $value->objectToArray();
        }
        break;
      case 'userworkingcharge':
        $stat["userWorkingCharge"] = array();
        $data = $em->getRepository('MongoBundle:StatUserWorkingCharge')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
                  //->findBy(array("project.id" => $project->getId()));
        foreach ($data as $key => $value) {
          $stat["userWorkingCharge"][] = $value->objectToArray();
        }
        break;
      case 'projectadvancement':
        $stat["projectAdvancement"] = array();
        $data = $em->getRepository('MongoBundle:StatProjectAdvancement')->createQueryBuilder()
                  ->field('project.id')->equals($project->getId())
                  ->getQuery()->execute();
                  //->findBy(array("project.id" => $project->getId()));
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
    $em = $this->get('doctrine_mongodb')->getManager();

    $qb = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
        ->field('projects.id')->equals($project->getId())
        ->sort('dueDate', 'desc');
		$req = $qb->getQuery()->getSingleResult();

    if ($req === null)
      $dueDate = null;
    else {
      $dueDate = $req->getDueDate();
    }

    return array('projectStart' => $project->getCreatedAt()->format('Y-m-d H:i:s'),
                  'projectEnd' => $dueDate->format('Y-m-d H:i:s'));
  }

  private function getTimelinesMessageNumber($project)
  {
    $em = $this->get('doctrine_mongodb')->getManager();

    $result = array();
    foreach ($project->getTimelines() as $key => $timeline) {
      $req = $em->getRepository('MongoBundle:TimelineMessage')->createQueryBuilder()
           ->field('timelines.id')->equals($timeline->getId())
           ->getQuery()->execute();
      $result[($timeline->getTypeId() == 1 ? 'customer' : 'team')] = count($req);
    }

    return $result;
  }

  private function getCustomerAccessNumber($project)
  {
    $em = $this->get('doctrine_mongodb')->getManager();

    $result['actual'] = $project->getCustomersAccess()->count();

    $result['maximum'] = 10;

    return $result;
  }

  private function getOpenCloseBug($project)
  {
    $em = $this->get('doctrine_mongodb')->getManager();

    $req_open = $em->getRepository('MongoBundle:Bug')->createQueryBuilder()
                        ->field('state')->equals(true)
                        ->field('projects.id')->equals($project->getId())
                        ->getQuery()->execute();
    $result['open'] = count($req_open);

    $req_close =  $em->getRepository('MongoBundle:Bug')->createQueryBuilder()
                        ->field('state')->equals(false)
                        ->field('projects.id')->equals($project->getId())
                        ->getQuery()->execute();
    $result['closed'] = count($req_close);

    return $result;
  }

  private function getTaskStatus($project)
  {
    $em = $this->get('doctrine_mongodb')->getManager();
    $date = new DateTime('now');

    $req_done = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                    ->field('projects.id')->equals($project->getId())
                    ->field('advance')->equals(100)
                    //->where('finishedAt IS NOT NULL')
                    ->getQuery()->execute();
    $result['done'] = count($req_done);

    $req_doing = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                    ->field('projects.id')->equals($project->getId())
                    ->field('advance')->gt(0)
                    ->field('advance')->lt(100)
                    ->field('dueDate')->gt($date)
                    // ->where('t.finishedAt IS NULL AND t.startedAt IS NOT NULL AND t.dueDate > :date AND t.projects.id = :project')
                    ->getQuery()->execute();
    $result['doing'] = count($req_doing);

    $req_todo = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                    ->field('projects.id')->equals($project->getId())
                    ->field('advance')->equals(0)
                    ->field('dueDate')->gt($date)
                    // ->where('t.startedAt IS NULL AND t.dueDate > :date AND t.projects.id = :project')
                    ->getQuery()->execute();
    $result['toDo'] = count($req_todo);

    $req_late = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                    ->field('projects.id')->equals($project->getId())
                    ->field('advance')->lt(100)
                    ->field('dueDate')->lte($date)
                    // ->where('t.finishedAt IS NULL AND t.dueDate <= :date AND t.projects.id = :project')
                    ->getQuery()->execute();
    $result['late'] = count($req_late);

    return $result;
  }

  private function getTotalTasks($project)
  {
    $em = $this->get('doctrine_mongodb')->getManager();

    $req = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                ->field('projects.id')->equals($project->getId())
                ->getQuery()->execute();
    $result = count($req);

    return $result;
  }

  private function getClientBugTracker($project)
  {
      $em = $this->get('doctrine_mongodb')->getManager();

      $req = $em->getRepository('MongoBundle:Bug')->createQueryBuilder()
                  ->field('projects.id')->equals($project->getId())
                  ->field('state')->equals(true)
                  ->field('clientOrigin')->equals(true)
                  ->getQuery()->execute();
      $result = count($req);

      return $result;
  }


  // -----------------------------------------------------------------------
  //                    STATISTICS DATA - CUSTOM UPDATE
  // -----------------------------------------------------------------------

  public function manuallyUpdateStatAction() {
    $em = $this->get('doctrine_mongodb')->getManager();
      $projects = $em->getRepository('MongoBundle:Project')->findBy(array('deletedAt' => NULL));

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
    $em = $this->get('doctrine_mongodb')->getManager();
    $project = $em->getRepository('MongoBundle:Project')->find($projectId);

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
    $em = $this->get('doctrine_mongodb')->getManager();
    $date = new DateTime('now');
    $users = $project->getUsers();
    $resources = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                    ->field('projects.id')->equals($project->getId())
                    ->getQuery()->execute();

    foreach ($users as $key => $user) {
      $result['Done'] = 0;
      $result['ToDo'] = 0;
      $result['Doing'] = 0;
      $result['Late'] = 0;

      foreach ($resources as $key => $task) {
        foreach ($task->getRessources() as $user_key => $res) {
          $user_value = $res->getUser();
          if ($user_value->getId() == $user->getId())
          {
            if($task->getAdvance() == 100)
              $result['Done'] += 1;
            else if ($task->getAdvance() == 0)
              $result['ToDo'] += 1;
            else if ($task->getAdvance() > 0 && $task->getAdvance() < 100 && $task->getDueDate() > $date)
              $result['Doing'] += 1;
            else if ($task->getAdvance() < 100 && $task->getDueDate() <= $date)
              $result['Late'] += 1;
          }
        }
      }

      $statUserTasksAdvancement = $em->getRepository('MongoBundle:StatUserTasksAdvancement')->createQueryBuilder()
        ->field('project.id')->equals($project->getId())
        ->field('user.id')->equals($user->getId())
        ->getQuery()->getSingleResult();
        //->findOneBy(array('project.id' => $project->getId(), 'user.id' => $user->getId()));

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


    $stats = $em->getRepository('MongoBundle:StatUserTasksAdvancement')->createQueryBuilder()
      ->field('project.id')->equals($project->getId())
      ->getQuery()->execute();
    //->findBy(array('project.id' => $project->getId()));

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
    $em = $this->get('doctrine_mongodb')->getManager();

    $users = $project->getUsers();

    $resources = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                      ->field('projects.id')->equals($project->getId())
                      ->getQuery()->execute();

    foreach ($users as $key => $user) {
      $charge = 0;
      foreach ($resources as $key => $res) {
        $users_res = $res->getRessources();
        foreach ($users_res as $key => $user_value) {
          if ($user_value->getUser()->getId() == $user->getId()) {
              $charge += $user_value->getResource();
          }
        }
      }

      $statUserWorkingCharge = $em->getRepository('MongoBundle:StatUserWorkingCharge')->createQueryBuilder()
                                  ->field('project.id')->equals($project->getId())
                                  ->field('user.id')->equals($user->getId())
                                  ->getQuery()->getSingleResult();

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

    $stats = $em->getRepository('MongoBundle:StatUserWorkingCharge')->createQueryBuilder()
                ->field('project.id')->equals($project->getId())
                ->getQuery()->execute();

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
    $em = $this->get('doctrine_mongodb')->getManager();

    $users = $project->getUsers();

    $tasks = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                  ->field('projects.id')->equals($project->getId())
                  ->getQuery()->execute();

    foreach ($users as $key => $user) {
      $number = 0;
      $pur = $em->getRepository('MongoBundle:ProjectUserRole')->createQueryBuilder()
                ->field('projectId')->equals($project->getId())
                ->field('userId')->equals($user->getId())
                ->getQuery()->getSingleResult();

      $role = $em->getRepository('MongoBundle:Role')->find($pur->getRoleId());

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

      $statTasksRepartition = $em->getRepository('MongoBundle:StatTasksRepartition')->findOneBy(array('project.id' => $project->getId(), 'user.id' => $user->getId()));
      if (!$statTasksRepartition instanceof StatTasksRepartition)
      {
        $statTasksRepartition = new StatTasksRepartition();
        $statTasksRepartition->setProject($project);
        $statTasksRepartition->setUser($user);
        $statTasksRepartition->setRole($role->getName());
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
    $em = $this->get('doctrine_mongodb')->getManager();

    $users = $project->getUsers();

    $req_totalBugs = $em->getRepository('MongoBundle:Bug')->createQueryBuilder()
                   ->field('projects.id')->equals($project->getId())
                   ->getQuery()->execute();
    $totalBugs = count($req_totalBugs);

    foreach ($users as $key => $user) {
      if ($totalBugs != 0)
      {
        $req_number = $em->getRepository('MongoBundle:Bug')->createQueryBuilder()
                       ->field('projects.id')->equals($project->getId())
                       ->field('users.id')->equals($user->getId())
                       //->andWhere(':user MEMBER OF b.users')
                       ->getQuery()->execute();
        $number = count($req_number);

        $percentage = ($number * 100) / $totalBugs;
      }
      else {
        $number = 0;
        $percentage = 0;
      }

      $statBugsUsersRepartition = $em->getRepository('MongoBundle:StatBugsUsersRepartition')->findOneBy(array('project.id' => $project->getId(), "user.id" => $user->getId()));
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

    $stats = $em->getRepository('MongoBundle:StatBugsUsersRepartition')->findBy(array('project.id' => $project->getId()));
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
    $em = $this->get('doctrine_mongodb')->getManager();

    $bugs = $em->getRepository('MongoBundle:Bug')->findBy(array('projects.id' => $project->getId(), 'state' => true));

    $assigned = 0;
    $unassigned = 0;
    foreach ($bugs as $key => $bug) {
      if($bug->getUsers() != null)
        $assigned += 1;
      else
        $unassigned += 1;
    }

    $statBugAssignationTracker = $em->getRepository('MongoBundle:StatBugAssignationTracker')->findOneBy(array('project.id' => $project->getId()));
    if ($statBugAssignationTracker === null)
    {
      $statBugAssignationTracker = new StatBugAssignationTracker();
      $statBugAssignationTracker->setProject($project);
    }
    $statBugAssignationTracker->setAssignedBugs($assigned);
    $statBugAssignationTracker->setUnassignedBugs($unassigned);

    $em->persist($statBugAssignationTracker);
    $em->flush();

    return 'Data updated';
  }

  private function updateBugsTagsRepartition($project)
  {
    $em = $this->get('doctrine_mongodb')->getManager();

    $tags = $em->getRepository('MongoBundle:Tag')->createQueryBuilder()
              ->field('project.id')->equals($project->getId())
              ->getQuery()->execute();

    $req_totalBugs = $em->getRepository('MongoBundle:Bug')->createQueryBuilder()
                   ->field("projects.id")->equals($project->getId())
                   ->getQuery()->execute();
    $totalBugs = count($req_totalBugs);

    foreach ($tags as $key => $tag) {
      $number = 0;
      foreach ($req_totalBugs as $key => $bug_value) {
        foreach ($bug_value->getBugtrackerTags() as $key => $tag_value) {
          if ($tag_value->getId() == $tag->getId())
            $number += 1;
        }
      }

      if ($totalBugs > 0)
        $percentage = ($number * 100) / $totalBugs;
      else
        $percentage = 0;

      $statBugsTagsRepartition = $em->getRepository('MongoBundle:StatBugsTagsRepartition')->createQueryBuilder()
                                      ->field('project.id')->equals($project->getId())
                                      ->field('name')->equals($tag->getName())
                                      ->getQuery()->getSingleResult();
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

    return 'Data updated';
  }

  // ------------ CLOUD STATISTICS UPDATE METHODS --------------------

  public function updateCloudStat($projectId, $token, Request $request)
  {
    $em = $this->get('doctrine_mongodb')->getManager();
    $project = $em->getRepository('MongoBundle:Project')->find($projectId);

    if ($project === null)
      return "Error: Bad project Id";

    $this->updateStorageSize($project, $request);
    return "Success: Stat 'StorrageSize' updated.";
  }

  private function updateStorageSize($project, Request $request)
  {
    $res = $this->calculateStorageSize($project, ",", $request);

    $em = $this->get('doctrine_mongodb')->getManager();
    $statStorageSize = $em->getRepository('MongoBundle:StatStorageSize')->createQueryBuilder()
                      ->field('project.id')->equals($project->getId())
                      ->getQuery()->getSingleResult();

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
    $response = $this->get('mongo_service_cloud')->getListAction($project->getId(), $path, $project->getSafePassword(), $request);
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
    $em = $this->get('doctrine_mongodb')->getManager();
    $projects = $em->getRepository('MongoBundle:Project')->findBy(array('deletedAt' => NULL));

    $result = array();
    foreach ($projects as $key => $project) {
      $result['BugsEvolution'] = $this->updateBugsEvolution($project);
      $result['LateTasks'] = $this->updateLateTasks($project);
    }
    return $this->setSuccess("1.16.1", "Stat", "dailyUpdate", "Complete Success", $result);
  }

  private function updateLateTasks($project)
  {
    $em = $this->get('doctrine_mongodb')->getManager();

    $users = $project->getUsers();

    $ontimeProjectTasks = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                        ->field('t.projects.id')->equals($project->getId())
                        ->field('deletedAt')->equals(null)
                        ->field('advance')->equals(100)
                        ->field('finishedAt')->lte('dueDate')
                        ->getQuery()->execute();

    $lateProjectTasks = array();

    $lateProjectTasksOne = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                        ->field('projects.id')->equals($project->getId())
                        ->field('deletedAt')->equals(null)
                        ->field('advance')->equals(100)
                        // ->field('finishedAt')->gt('dueDate')
                        ->getQuery()->execute();
    $lateProjectTasksTwo = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                        ->field('projects.id')->equals($project->getId())
                        ->field('deletedAt')->equals(null)
                        ->field('advance')->lt(100)
                        ->field('dueDate')->lt(new Datetime('now'))
                        ->getQuery()->execute();

    $result = array();
    foreach ($users as $key => $user) {
  		$ontimeTasks = 0;
  		$lateTasks = 0;
  		$pur = $em->getRepository('MongoBundle:ProjectUserRole')->createQueryBuilder()
              ->field('projectId')->equals($project->getId())
              ->field('userId')->equals($user->getId())
              ->getQuery()->getSingleResult();
      $role = $em->getRepository('MongoBundle:Role')->createQueryBuilder()
                ->field('id')->equals($pur->getRoleId())
                ->getQuery()->getSingleResult();

  		if ($role && $role != null) {
  			foreach ($ontimeProjectTasks as $key => $task) {
  				foreach ($task->getRessources() as $key => $res) {
  					if ($res->getUser()->getId() == $user->getId())
  						$ontimeTasks += 1;
  				}
  			}

  			foreach ($lateProjectTasksOne as $key => $task) {
          if ($task->getFinishedAt() <= $task->getDueDate()) {
            foreach ($task->getRessources() as $key => $res) {
    					if ($res->getUser()->getId() == $user->getId())
    						$lateTasks += 1;
    				}
          }
  			}

        foreach ($lateProjectTasksTwo as $key => $task) {
  				foreach ($task->getRessources() as $key => $res) {
  					if ($res->getUser()->getId() == $user->getId())
  						$lateTasks += 1;
  				}
  			}

        $statLateTasks = new statLateTasks();
        $statLateTasks->setProject($project);
        $statLateTasks->setUser($user);
        $statLateTasks->setRole($role->getName());
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
    // $em = $this->get('doctrine_mongodb')->getManager();
    //
    // $date = new DateTime('now');
    // //TODO remove one day
    //
    // $createdBugs = $em->getRepository('MongoBundle:Bug')->createQueryBuilder('b')
    //                ->select('count(b)')
    //                ->where("b.projects = :project")
    //                ->andWhere("b.createdAt BETWEEN :date_begin AND :date_end")
    //                ->setParameters(array('project' => $project, 'date_begin' => $date->format('Y-m-d').' 00:00:00', 'date_end' => $date->format('Y-m-d').' 23:59:59'))
    //                ->getQuery()->getSingleScalarResult();
    //
    // $closedBugs =  $em->getRepository('MongoBundle:Bug')->createQueryBuilder('b')
    //                ->select('count(b)')
    //                ->where("b.projects = :project")
    //                ->andWhere("b.deletedAt BETWEEN :date_begin AND :date_end")
    //                ->setParameters(array('project' => $project, 'date_begin' => $date->format('Y-m-d').' 00:00:00', 'date_end' => $date->format('Y-m-d').' 23:59:59'))
    //                ->getQuery()->getSingleScalarResult();
    //
    // $statBugsEvolution = new statBugsEvolution();
    // $statBugsEvolution->setProject($project);
    // $statBugsEvolution->setCreatedBugs($createdBugs);
    // $statBugsEvolution->setClosedbugs($closedBugs);
    // $statBugsEvolution->setDate($date);
    //
    // $em->persist($statBugsEvolution);
    // $em->flush();

    return "Data updated";
  }


  // -----------------------------------------------------------------------
  //                    STATISTICS DATA - WEEKLY UPDATE
  // -----------------------------------------------------------------------

  public function weeklyUpdateAction(Request $request)
  {
    $em = $this->get('doctrine_mongodb')->getManager();
    $projects = $em->getRepository('MongoBundle:Project')->findBy(array('deletedAt' => NULL));

    $result = array();
    foreach ($projects as $key => $project) {
      $result["ProjectAdvancement"] = $this->updateProjectAdvancement($project);
    }
    return $this->setSuccess("1.16.1", "Stat", "weeklyUpdate", "Complete Success", $result);
  }

  private function updateProjectAdvancement($project)
  {
    $em = $this->get('doctrine_mongodb')->getManager();

    $req_totalTasks = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                    ->field('projects.id')->equals($project->getId())
                    ->getQuery()->execute();
    $totalTasks = count($req_totalTasks);

    $req_finishedTasks = $em->getRepository('MongoBundle:Task')->createQueryBuilder()
                  ->field('projects.id')->equals($project->getId())
                  ->field('finishedAt')->notEqual(null)
                  ->getQuery()->execute();
    $finishedTasks = count($req_finishedTasks);

    if ($totalTasks != 0)
      $percentage = ($finishedTasks / $totalTasks) * 100;
    else
      $percentage = 0;

    $prev = $em->getRepository('MongoBundle:StatProjectAdvancement')->createQueryBuilder()
                ->field('project.id')->equals($project->getId())
                ->sort('date', 'desc')
                ->getQuery()->getSingleResult();
    if ($prev == null)
      $progress = $percentage;
    else
      $progress = $percentage - $prev->getPercentage();

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
    // $this->updateTasksRepartition($project);
    $this->updateBugsUsersRepartition($project);
    $this->updateBugAssignationTracker($project);
    $this->updateBugsTagsRepartition($project);

    // INITIATE CLOUD UPDATE STAT
    $this->updateStorageSize($project, $request);

    // INITIATE DAILY UPDATE STAT
    $this->updateBugsEvolution($project);
    // ??? $this->updateTasksRepartition($project);

    // INITIATE WEEKLY UDATE STAT
    // $this->updateProjectAdvancement($project);

    return "Statistics initiated";
  }

}
