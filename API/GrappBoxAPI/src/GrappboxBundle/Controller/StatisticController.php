<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use GrappboxBundle\Controller\RolesAndTokenVerificationController;
use GrappboxBundle\Entity\StatProjectAdvancement;
use GrappboxBundle\Entity\StatLateTasks;
use GrappboxBundle\Entity\StatBugsEvolution;
use GrappboxBundle\Entity\StatBugsTagsRepartition;
use GrappboxBundle\Entity\StatBugAssignationTracker;
use GrappboxBundle\Entity\StatBugsUsersRepartition;
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
  * @api {get} /V0.2/statistics/getall/:token/:projectId get all stats info
  * @apiName getAllStat
  * @apiGroup Stat
  * @apiDescription Get all statistics info
  * @apiVersion 0.2.0
  *
  * @apiParam {String} token token of the person connected
  * @apiParam {int} projectId id of the related project
  *
  * @apiSuccess {Object} statName stat data asked
  *
  * @apiSuccessExample Success-Response:
  *	HTTP/1.1 200 OK
  *	{
  *		"info": {
  *			"return_code": "1.16.1",
  *			"return_message": "Stat - getAll - Complete Success"
  *		},
  *		"data": {
  *		 "array": {
  *		   "statName": {
  *		     "data content"
  *		   },
  *		   ...
  *		 }
  *		}
  *	}
  *
  * @apiErrorExample Bad Authentication Token
  *	HTTP/1.1 401 Unauthorized
  *	{
  *		"info": {
  *			"return_code": "16.1.3",
  *			"return_message": "Stat - getAll - Bad ID"
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
    $user = $this->checkToken($token);
    if (!$user)
      return $this->setBadTokenError("16.1.3", "Stat", "getAll");

    $em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($projectId);

		if ($project === null)
			return $this->setBadRequest("16.1.4", "Stat", "getAll", "Bad Parameter: projectId");

    return $this->setSuccess("1.16.1", "Stat", "getAll", "Complete Success", "Here will be statistics data");
  }

  /**
  * @api {get} /V0.2/statistics/getStat/:token/:projectId/:statName Get a stat info
  * @apiName getAllStat
  * @apiGroup Stat
  * @apiDescription Get a particaular statistics info
  * @apiVersion 0.2.0
  *
  * @apiParam {String} token token of the person connected
  * @apiParam {int} projectId id of the related project
  * @apiParam {string} statName name of the statistic
  *
  * @apiSuccess {Object} statName stat data asked
  *
  * @apiSuccessExample Success-Response:
  *	HTTP/1.1 200 OK
  *
  *	/!\ following is just an exemple, refer to getAll request for complete list of each stat data content
  *
  *	{
  *		"info": {
  *			"return_code": "1.16.1",
  *			"return_message": "Stat - getStat - Complete Success"
  *		},
  *		"data": {
  *		 "statName": {
  *		   "dataName": "datacontent"
  *		 }
  *		}
  *	}
  *
  * @apiErrorExample Bad Authentication Token
  *	HTTP/1.1 401 Unauthorized
  *	{
  *		"info": {
  *			"return_code": "16.2.3",
  *			"return_message": "Stat - getStat - Bad ID"
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
	*			"return_code": "7.2.4",
	*			"return_message": "Stat - getStat- Bad Parameter: statName"
	*		}
	*	}
  */
  public function getStatAction(Request $request, $projectId, $statName)
  {
    $user = $this->checkToken($token);
    if (!$user)
      return $this->setBadTokenError("16.2.3", "Stat", "getStat");

    $em = $this->getDoctrine()->getManager();
    $project = $em->getRepository('GrappboxBundle:Project')->find($projectId);

    if ($project === null)
      return $this->setBadRequest("16.2.4", "Stat", "getStat", "Bad Parameter: projectId");

    return $this->setSuccess("1.16.1", "Stat", "getStat", "Complete Success", "Here will be statistic data");

    $stat = array();
    switch ($statName) {
      case 'ProjectAdvancement':
        $stat["ProjectAdvancement"] = $em->getRepository('GrappboxBundle:statProjectAdvancement')->findBy(array("project" => $project));
        break;
      case 'LateTasks':
        $stat["Latetasks"] = $em->getRepository('GrappboxBundle:statLateTasks')->findBy(array("project" => $project));
        break;
      case 'ProjectTimeLimits':
        $stat["ProjectTimeLimits"] = $this->getProjectTimeLimits($project);
        break;
      case 'TimelinesMessageNumber':
        $stat["TimelinesMessageNumber"] = $this->getTimelineMessageNumber($project);
        break;
      case 'CustomerAccesseNumber':
        $stat["CustomerAccesseNumber"] = $this->getCustomerAccesseNumber($project);
        break;
      default:
        return $this->setBadRequest("16.2.4", "Stat", "getStat", "Bad Parameter: statName");
        break;
    }
  }



  // -----------------------------------------------------------------------
  //                 STATISTICS DATA - GET INSTANT VALUES
  // -----------------------------------------------------------------------

  // GENERIC INSTANT GETTER FOR ALL PROJECTS
  public function instantValuesAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $projects = $em->getRepository('GrappboxBundle:Project')->findBy(array('deletedAt' => NULL));

    $result = array();
    foreach ($projects as $key => $project) {
      $result['project'.$project->getId()] = $this->getTimelinesMessageNumber($project);
      // TODO complete with all weekly stat update
    }
    return $this->setSuccess("1.16.1", "Stat", "instantUpdate", "Complete Success", $result);
  }

  private function getProjectTimeLimits($project)
  {
    $em = $this->getDoctrine()->getManager();
    $dueDate = $em->getRepository('GrappboxBundle:Task')->createQueryBuilder('t')
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
      $result[$timeline->getName()] = $em->getRepository('GrappboxBundle:TimelineMessage')->createQueryBuilder('t')
                 ->select('count(t)')
                 ->where('t.parentId IS NULL AND t = :timeline AND t.deletedAt IS NULL')
                 ->setParameters(array('timeline' => $timeline))
                 ->getQuery()->getSingleScalarResult();
    }

    return $result;
  }

  private function getCustomerAccessNumber($project)
  {
    $em = $this->getDoctrine()->getManager();

    $result['actual'] = $project->getCustomersAccess()->count();

    $result['maximum'] = "XX";

    return $result;
  }


  // -----------------------------------------------------------------------
  //                    STATISTICS DATA - INSTANT UPDATE
  // -----------------------------------------------------------------------

  // GENERIC INSTANT UPDATE FOR ALL PROJECTS
  public function instantUpdateAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $projects = $em->getRepository('GrappboxBundle:Project')->findBy(array('deletedAt' => NULL));

    $result = array();
    foreach ($projects as $key => $project) {
      $result['lateTasks'] = $this->updateLateTasks($project);
      // TODO complete with all instant stat update
    }
    return $this->setSuccess("1.16.1", "Stat", "dailyUpdate", "Complete Success", $result);
  }

  public function updateStorageLimitsAction(Request $request, $token, $projectId)
  {

    //TODO : ajouter appel de la method sur toute les modif de cloud ou ajouter un apel à partir d'un script

    $user = $this->checkToken($token);
    if (!$user)
      return $this->setBadTokenError("16.??.3", "Stat", "updateStorageLimits");

    $em = $this->getDoctrine()->getManager();
    $project = $em->getRepository('GrappboxBundle:Project')->find($projectId);

    if ($project === null)
      return $this->setBadRequest("16.2.4", "Stat", "updateStorageLimits", "Bad Parameter: projectId");

    // ',' = racine

    // $path = ',';
    // $baseUrl = $this->container->get('router')->getContext()->getBaseUrl();
    // $url =  $baseUrl.'/V0.2/cloud/list/'.$token.'/'.$projectId.'/'.$path.'/'.$project->getSafePassword();
    // $http = new HttpRequest($url, HttpRequest::METH_GET);
    //
    // $response = $http->send();
    //
    // if ($response->getBody()->info->return_code != "1.3.1")
    //   return $respone;
    //
    // $results = $response->data->array;
    // $folderList = array();
    // $fileList = array();
    // foreach ($results as $key => $result) {
    //   if ($result->type == "dir")
    //     $folderList[] = $result;
    //   else
    //     $fileList[] = $result;
    // }
    //
    // $i = 0;
    // while (count($folderList)) {
    //   $path = $path.$folderList[$i]->fileName
    // }
    return $this->setSuccess("1.16.1", "Stat", "updateStorageLimits", "Complete Success", "Not implemented yet");
  }

  // -----------------------------------------------------------------------
  //                    STATISTICS DATA - DAILY UPDATE
  // -----------------------------------------------------------------------

  // GENERIC DAYLY UPDATE FOR ALL PROJECTS
  public function dailyUpdateAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $projects = $em->getRepository('GrappboxBundle:Project')->findBy(array('deletedAt' => NULL));

    $result = array();
    foreach ($projects as $key => $project) {
      //$result['LateTasks'] = $this->updateLateTasks($project);
      //$result['BugsEvolution'] = $this->updateBugsEvolution($project);
      //$result['BugsTagsRepartition'] = $this->updateBugsTagsRepartition($project);
      //$result['StatBugAssignationTracker'] = $this->updateBugAssignationTracker($project);
      $result['BugsTagsRepartition'] = $this->updateBugsUsersRepartition($project);
      // TODO complete with all daily stat update
    }
    return $this->setSuccess("1.16.1", "Stat", "dailyUpdate", "Complete Success", $result);
  }

  private function updateLateTasks($project)
  {
    $em = $this->getDoctrine()->getManager();

    $users = $project->getUsers();

    $ontimeProjectTasks = $em->getRepository('GrappboxBundle:Task')->createQueryBuilder('t')
                        ->where('t.projects = :project')
                        ->andWhere('t.deletedAt IS NULL')
                        ->andWhere('t.finishedAt IS NOT NULL')
                        ->andWhere('t.finishedAt <= t.dueDate')
                        ->setParameters(array('project' => $project))
                        ->getQuery()->getResult();


    $lateProjectTasks = $em->getRepository('GrappboxBundle:Task')->createQueryBuilder('t')
                      ->where('t.projects = :project')
                      ->andWhere('t.deletedAt IS NULL')
                      ->andWhere('(t.finishedAt IS NOT NULL AND t.finishedAt > t.dueDate) OR (t.finishedAt IS NULL AND t.dueDate < :now)')
                      ->setParameters(array('project' => $project, 'now' => new DateTime('now')))
                      ->getQuery()->getResult();


    $result = array();
    foreach ($users as $key => $user) {
      $ontimeTasks = 0;
      $lateTasks = 0;
      $role = $em->getRepository('GrappboxBundle:ProjectUserRole')->createQueryBuilder('u')
              ->select('r.name')
              ->join('GrappboxBundle\Entity\Role', 'r', 'WITH', 'r.id = u.roleId')
              ->where('u.projectId = :projectId')
              ->setParameter('projectId', $project->getId())
              ->setMaxResults(1)
              ->getQuery()->getResult();

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
      $statLateTasks->setUser($user->getFirstname().' '.$user->getLastName());
      $statLateTasks->setRole($role[0]['name']);
      $statLateTasks->setLateTasks($lateTasks);
      $statLateTasks->setOntimeTasks($ontimeTasks);
      $statLateTasks->setDate(new DateTime('now'));

      $em->persist($statLateTasks);
      $em->flush();
    }

    return "Data updated";
  }

  private function updateBugsEvolution($project)
  {
    $em = $this->getDoctrine()->getManager();

    $date = new DateTime('now');
    // remove one day

    $createdBugs = $em->getRepository('GrappboxBundle:Bug')->createQueryBuilder('b')
                   ->select('count(b)')
                   ->where("b.projects = :project")
                   ->andWhere("b.createdAt BETWEEN :date_begin AND :date_end")
                   ->setParameters(array('project' => $project, 'date_begin' => $date->format('Y-m-d').' 00:00:00', 'date_end' => $date->format('Y-m-d').' 23:59:59'))
                   ->getQuery()->getSingleScalarResult();

    $closedBugs =  $em->getRepository('GrappboxBundle:Bug')->createQueryBuilder('b')
                   ->select('count(b)')
                   ->where("b.projects = :project")
                   ->andWhere("b.deletedAt BETWEEN :date_begin AND :date_end")
                   ->setParameters(array('project' => $project, 'date_begin' => $date->format('Y-m-d').' 00:00:00', 'date_end' => $date->format('Y-m-d').' 23:59:59'))
                   ->getQuery()->getSingleScalarResult();

    $statBugsEvolution = new statBugsEvolution();
    $statBugsEvolution->setProject($project);
    $statBugsEvolution->setCreatedBugs($createdBugs);
    $statBugsEvolution->setClosedbugs($closedBugs);
    $statBugsEvolution->setDate($date);

    $em->persist($statBugsEvolution);
    $em->flush();

    return "Data updated";
  }

  private function updateBugsTagsRepartition($project)
  {
    $em = $this->getDoctrine()->getManager();

    $tags = $em->getRepository('GrappboxBundle:Tag')->findBy(array('project' => $project));

    $totalBugs = $em->getRepository('GrappboxBundle:Bug')->createQueryBuilder('t')
                   ->select('count(t)')
                   ->where("t.projects = :project")
                   ->setParameters(array('project' => $project))
                   ->getQuery()->getSingleScalarResult();

    foreach ($tags as $key => $tag) {
      $number = $em->getRepository('GrappboxBundle:Bug')->createQueryBuilder('t')
                     ->select('count(t)')
                     ->where("t.projects = :project")
                     ->andWhere(":tag MEMBER OF t.tags")
                     ->setParameters(array('project' => $project, 'tag' => $tag))
                     ->getQuery()->getSingleScalarResult();

      $percentage = ($number * 100) / $totalBugs;

      $statBugsTagsRepartition = $em->getRepository('GrappboxBundle:StatBugsTagsRepartition')->findOneBy(array('project' => $project, 'name' => $tag->getName()));
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

  private function updateBugAssignationTracker($project)
  {
    $em = $this->getDoctrine()->getManager();

    $bugs = $em->getRepository('GrappboxBundle:Bug')->findBy(array('projects' => $project, 'deletedAt' => NULL));

    $assigned = 0;
    $unassigned = 0;
    foreach ($bugs as $key => $bug) {
      if($bug->getUsers() != null)
        $assigned += 1;
      else
        $unassigned += 1;
    }

    $statBugAssignationTracker = $em->getRepository('GrappboxBundle:StatBugAssignationTracker')->findOneBy(array('project' => $project));
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

  private function updateBugsUsersRepartition($project)
  {
    $em = $this->getDoctrine()->getManager();

    $users = $project->getUsers();

    $totalBugs = $em->getRepository('GrappboxBundle:Bug')->createQueryBuilder('b')
                   ->select('count(b)')
                   ->where("b.projects = :project")
                   ->setParameters(array('project' => $project))
                   ->getQuery()->getSingleScalarResult();

    foreach ($users as $key => $user) {
      if ($totalBugs != 0)
      {
        $number = $em->getRepository('GrappboxBundle:Bug')->createQueryBuilder('b')
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

      $statBugsTagsRepartition = $em->getRepository('GrappboxBundle:StatBugsUsersRepartition')->findOneBy(array('project' => $project));
      if ($statBugsTagsRepartition === null)
      {
        $statBugsTagsRepartition = new StatBugsUsersRepartition();
        $statBugsTagsRepartition->setProject($project);
        $statBugsTagsRepartition->setUser($user->getFirstname().' '.$user->getLastName());
      }
      $statBugsTagsRepartition->setValue($number);
      $statBugsTagsRepartition->setPercentage($percentage);

      $em->persist($statBugsTagsRepartition);
      $em->flush();
    }
  }

  // -----------------------------------------------------------------------
  //                    STATISTICS DATA - WEEKLY UPDATE
  // -----------------------------------------------------------------------

  // GENERIC WEEKLY UPDATE FOR ALL PROJECTS
  public function weeklyUpdateAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    $projects = $em->getRepository('GrappboxBundle:Project')->findBy(array('deletedAt' => NULL));

    $result = array();
    foreach ($projects as $key => $project) {
      $result["ProjectAdvancement"] = $this->updateProjectAdvancement($project);
      // TODO complete with all weekly stat update
    }
    return $this->setSuccess("1.16.1", "Stat", "weeklyUpdate", "Complete Success", $result);
  }

  // INDIVIDUAL UPDATE METHODS
  private function updateProjectAdvancement($project)
  {
    $em = $this->getDoctrine()->getManager();

    $totalTasks = $em->getRepository('GrappboxBundle:Task')->createQueryBuilder('t')
                  ->select('count(t)')
                  ->where('t.projects = :project')
                  ->andWhere('t.deletedAt IS NULL')
                  ->setParameter('project', $project)
                  ->getQuery()->getSingleScalarResult();

    $finishedTasks = $em->getRepository('GrappboxBundle:Task')->createQueryBuilder('t')
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

    $prev = $em->getRepository('GrappboxBundle:statProjectAdvancement')->findBy(array("project" => $project), array('date' => 'DESC'));
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

}
