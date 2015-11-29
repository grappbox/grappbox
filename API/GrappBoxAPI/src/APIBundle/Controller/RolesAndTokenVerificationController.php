<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use APIBundle\Entity\Role;
use APIBundle\Entity\ProjectUserRole;
use DateTime;
use DateInterval;

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
*/
class RolesAndTokenVerificationController extends Controller
{
  // return user if token is correct
  // return null if token is incorrect
  protected function checkToken($token)
  {
    if (!$token)
      return NULL;
    $em = $this->getDoctrine()->getManager();
    $user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $token));

    if (!$user)
      return $user;

    $now = new DateTime('now');
    if ($user->getToken() && $user->getTokenValidity() && $user->getTokenValidity() < $now)
    {
      $this->token = null;
      return null;
    }
    else if ($user->getToken() && $user->getTokenValidity())
    {
      $user->setTokenValidity($now->add(new DateInterval("P1D")));

      $em = $this->getDoctrine()->getManager();
      $em->persist($user);
      $em->flush();
    }

    return $user;
  }

  // return 0 if user has no rigths on this role
  // return 1 if user has rights
  protected function checkRoles($user, $projectId, $role)
  {
    $em = $this->getDoctrine()->getManager();
    $query = $em->createQuery(
                      'SELECT roles.'.$role.'
                      FROM APIBundle:Role roles
                      JOIN APIBundle:ProjectUserRole projectUser WITH roles.id = projectUser.roleId
                      WHERE projectUser.projectId = '.$projectId.' AND projectUser.userId = '.$user->getId());
    $result = $query->setMaxResults(1)->getOneOrNullResult();
    return $result[$role];
  }

  protected function setBadTokenError()
  {
    $response = new JsonResponse('Bad Authentication Token', JsonResponse::HTTP_BAD_REQUEST);

    return $response;
  }

  protected function setNoRightsError()
  {
    $response = new JsonResponse('Insufficient User Rights', JsonResponse::HTTP_FORBIDDEN);

    return $response;
  }

  protected function setBadRequest($message)
  {
    $response = new JsonResponse($message, JsonResponse::HTTP_BAD_REQUEST);

    return $response;
  }

  /**
  * @api {post} /V0.6/roles/addprojectroles Add a project role
  * @apiName addProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.6.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {String} name Name of the role
  * @apiParam {Number} teamTimeline Access rights on the project's team timeline
  * @apiParam {Number} customerTimeline Access rights on the project's customer timeline
  * @apiParam {Number} gantt Access rights on the project's gantt
  * @apiParam {Number} whiteboard Access rights on the project's whiteboard
  * @apiParam {Number} bugtracker Access rights on the project's bugracker
  * @apiParam {Number} event Access rights on the project's meetings
  * @apiParam {Number} task Access rights on the project's tasks
  * @apiParam {Number} projectSettings Access rights on the project's settings
  * @apiParam {Number} cloud Access rights on the project's cloud
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *			"_token": "aeqf231ced651qcd",
  *			"projectId": 1,
  *			"name": "Admin",
  *			"teamTimeline": 1,
  *			"customerTimeline": 1,
  *			"gantt": 1,
  *			"whiteboard": 1,
  *			"bugtracker": 1,
  *			"event": 1,
  *			"task": 1,
  *			"projectSettings": 1,
  *			"cloud": 1
  * 	}
  *
  * @apiSuccess {Number} roleId Id of the role created
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"roleId":1
  * 	}
  *
  * @apiErrorExample Missing Parameter
  *		HTTP/1.1 400 Bad Request
  * 	{
  * 		"Missing Parameter"
  * 	}
  * @apiErrorExample Bad Authentication Token
  * 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
  * @apiErrorExample Insufficient User Rights
  * 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  *
  */

  /**
  * @api {post} /V0.7/roles/addprojectroles Add a project role
  * @apiName addProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.7.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {String} name Name of the role
  * @apiParam {Number} teamTimeline Access rights on the project's team timeline
  * @apiParam {Number} customerTimeline Access rights on the project's customer timeline
  * @apiParam {Number} gantt Access rights on the project's gantt
  * @apiParam {Number} whiteboard Access rights on the project's whiteboard
  * @apiParam {Number} bugtracker Access rights on the project's bugracker
  * @apiParam {Number} event Access rights on the project's meetings
  * @apiParam {Number} task Access rights on the project's tasks
  * @apiParam {Number} projectSettings Access rights on the project's settings
  * @apiParam {Number} cloud Access rights on the project's cloud
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "name": "Admin",
  *     "teamTimeline": 1,
  *     "customerTimeline": 1,
  *     "gantt": 1,
  *     "whiteboard": 1,
  *     "bugtracker": 1,
  *     "event": 1,
  *     "task": 1,
  *     "projectSettings": 1,
  *     "cloud": 1
  *   }
  *
  * @apiSuccess {Number} roleId Id of the role created
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "roleId":1
  *   }
  *
  * @apiErrorExample Missing Parameter
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Missing Parameter"
  *   }
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Insufficient User Rights
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "Insufficient User Rights"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  *
  * @apiErrorExample Project Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project with id X doesn't exist"
  *     }
  *
  */
  public function addProjectRolesAction(Request $request)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($content->_token);
  	if (!$content->projectId)
		return $this->setBadRequest("Missing Parameter");
	  if (!$user)
		  return ($this->setBadTokenError());
    if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
		  return $this->setNoRightsError();

  	$em = $this->getDoctrine()->getManager();
  	$role = new Role();

    $project = $em->getRepository('APIBundle:Project')->find($content->projectId);
    if ($project === null)
    {
      throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
      
    }

  	$role->setProjects($project);
  	$role->setName($content->name);
  	$role->setTeamTimeline($content->teamTimeline);
  	$role->setCustomerTimeline($content->customerTimeline);
  	$role->setGantt($content->gantt);
  	$role->setWhiteboard($content->whiteboard);
  	$role->setBugtracker($content->bugtracker);
  	$role->setEvent($content->event);
  	$role->setTask($content->task);
  	$role->setProjectSettings($content->projectSettings);
  	$role->setCloud($content->cloud);

  	$em->persist($role);
  	$em->flush();

  	return new JsonResponse(array("roleId" => $role->getId()));
  }

  /**
  * @api {delete} /V0.6/roles/delprojectroles Delete a project role
  * @apiName delProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.6.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *		"_token": "aeqf231ced651qcd",
  *		"projectId": 1,
  *		"roleId": 3
  * 	}
  *
  * @apiSuccess message Remove role success.
  * @apiSuccessExample Success-Response
  *     HTTP/1.1 200 OK
  *	  {
  *		"message" : "Remove role success."
  *	  }
  *
  * @apiErrorExample Missing Parameter
  *		HTTP/1.1 400 Bad Request
  * 	{
  * 		"Missing Parameter"
  * 	}
  * @apiErrorExample Bad Authentication Token
  * 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
  * @apiErrorExample Insufficient User Rights
  * 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The role with id 3 doesn't exist."
  *     }
  *
  */

  /**
  * @api {delete} /V0.7/roles/delprojectroles Delete a project role
  * @apiName delProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.7.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *   "_token": "aeqf231ced651qcd",
  *   "projectId": 1,
  *   "roleId": 3
  *   }
  *
  * @apiSuccess message Remove role success.
  * @apiSuccessExample Success-Response
  *     HTTP/1.1 200 OK
  *   {
  *   "message" : "Remove role success."
  *   }
  *
  * @apiErrorExample Missing Parameter
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Missing Parameter"
  *   }
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Insufficient User Rights
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "Insufficient User Rights"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The role with id 3 doesn't exist."
  *     }
  *
  */
  public function delProjectRolesAction(Request $request)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($content->_token);
  	if (!$content->projectId && !$content->roleId)
		  return $this->setBadRequest("Missing Parameters");
    if (!$user)
		  return ($this->setBadTokenError());
    if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
		  return $this->setNoRightsError();

    $em = $this->getDoctrine()->getManager();

    $role = $em->getRepository('APIBundle:Role')->find($content->roleId);

    if ($role === null)
    {
      throw new NotFoundHttpException("The role with id ".$content->roleId." doesn't exist.");
    }

    $em->remove($role);
    $em->flush();

    return new JsonResponse("Remove role success.");
  }

  /**
  * @api {put} /V0.6/roles/putprojectroles Update a project role
  * @apiName updateProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.6.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} roleId Id of the role
  * @apiParam {Number} projectId Id of the project
  * @apiParam {String} [name] Name of the role
  * @apiParam {Number} [teamTimeline] Access rights on the project's team timeline
  * @apiParam {Number} [customerTimeline] Access rights on the project's customer timeline
  * @apiParam {Number} [gantt] Access rights on the project's gantt
  * @apiParam {Number} [whiteboard] Access rights on the project's whiteboard
  * @apiParam {Number} [bugtracker] Access rights on the project's bugracker
  * @apiParam {Number} [event] Access rights on the project's meetings
  * @apiParam {Number} [task] Access rights on the project's tasks
  * @apiParam {Number} [projectSettings] Access rights on the project's settings
  * @apiParam {Number} [cloud] Access rights on the project's cloud
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *			"_token": "aeqf231ced651qcd",
  *			"roleId": 2,
  *			"projectId": 1,
  *			"name": "Admin",
  *			"customerTimeline": 0,
  *			"event": 1,
  *			"cloud": 0
  * 	}
  *
  * @apiSuccess message Update role success.
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"Update role success."
  * 	}
  *
  * @apiErrorExample Missing Parameter
  *		HTTP/1.1 400 Bad Request
  * 	{
  * 		"Missing Parameter"
  * 	}
  * @apiErrorExample Bad Authentication Token
  * 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
  * @apiErrorExample Insufficient User Rights
  * 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The role with id 2 doesn't exist."
  *     }
  *
  */

  /**
  * @api {put} /V0.7/roles/putprojectroles Update a project role
  * @apiName updateProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.7.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} roleId Id of the role
  * @apiParam {Number} projectId Id of the project
  * @apiParam {String} [name] Name of the role
  * @apiParam {Number} [teamTimeline] Access rights on the project's team timeline
  * @apiParam {Number} [customerTimeline] Access rights on the project's customer timeline
  * @apiParam {Number} [gantt] Access rights on the project's gantt
  * @apiParam {Number} [whiteboard] Access rights on the project's whiteboard
  * @apiParam {Number} [bugtracker] Access rights on the project's bugracker
  * @apiParam {Number} [event] Access rights on the project's meetings
  * @apiParam {Number} [task] Access rights on the project's tasks
  * @apiParam {Number} [projectSettings] Access rights on the project's settings
  * @apiParam {Number} [cloud] Access rights on the project's cloud
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "roleId": 2,
  *     "projectId": 1,
  *     "name": "Admin",
  *     "customerTimeline": 0,
  *     "event": 1,
  *     "cloud": 0
  *   }
  *
  * @apiSuccess message Update role success.
  *
  * @apiSuccessExample Success-Response:
  *   {
  *   "Update role success."
  *   }
  *
  * @apiErrorExample Missing Parameter
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Missing Parameter"
  *   }
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Insufficient User Rights
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "Insufficient User Rights"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The role with id 2 doesn't exist."
  *     }
  *
  */
  public function updateProjectRolesAction(Request $request)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($content->_token);
  	if (!$content->projectId && !$content->roleId)
		  return $this->setBadRequest("Missing Parameters");
    if (!$user)
		  return ($this->setBadTokenError());
    if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
		  return $this->setNoRightsError();

    $em = $this->getDoctrine()->getManager();

    $role = $em->getRepository('APIBundle:Role')->find($content->roleId);

    if ($role === null)
    {
		  throw new NotFoundHttpException("The role with id ".$content->roleId." doesn't exist.");
    }

    if (array_key_exists('name', $content))
      $role->setName($content->name);
    if (array_key_exists('teamTimeline', $content))
      $role->setTeamTimeline($content->teamTimeline);
    if (array_key_exists('customerTimeline', $content))
      $role->setCustomerTimeline($content->customerTimeline);
    if (array_key_exists('gantt', $content))
      $role->setGantt($content->gantt);
    if (array_key_exists('whiteboard', $content))
      $role->setWhiteboard($content->whiteboard);
    if (array_key_exists('bugtracker', $content))
      $role->setBugtracker($content->bugtracker);
    if (array_key_exists('event', $content))
      $role->setEvent($content->event);
    if (array_key_exists('task', $content))
      $role->setTask($content->task);
    if (array_key_exists('projectSettings', $content))
      $role->setProjectSettings($content->projectSettings);
    if (array_key_exists('cloud', $content))
      $role->setcloud($content->cloud);

	  $em->flush();

	  return new JsonResponse("Update role success.");
  }

  /**
  * @api {get} /V0.6/roles/getprojectroles/:token/:projectId Get all project roles
  * @apiName GetProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.6.0
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} projectId Id of the projectId
  *
  * @apiSuccess {Object[]} Role Array of roles
  * @apiSuccess {Number} Role.id Role id
  * @apiSuccess {String} Role.name Role name
  * @apiSuccess {Number} Role.team_timeline Team timeline role
  * @apiSuccess {Number} Role.customer_timeline Customer timeline role
  * @apiSuccess {Number} Role.gantt Gantt role
  * @apiSuccess {Number} Role.whiteboard Whiteboard role
  * @apiSuccess {Number} Role.bugtracker Bugtracker role
  * @apiSuccess {Number} Role.event Event role
  * @apiSuccess {Number} Role.task Task role
  * @apiSuccess {Number} Role.project_settings Project settings role
  * @apiSuccess {Number} Role.cloud Cloud role
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"Role 1":
  *			{
  *				"id": 10,
  *				"name": "Intern roles",
  *				"team_timeline": 1,
  *				"customer_timeline": 0,
  *				"gantt": 0,
  *				"whiteboard": 0,
  *				"bugtracker": 1,
  *				"event": 0,
  *				"task": 0,
  *				"project_settings": 0,
  *				"cloud": 1
  * 		}
  *		}
  *
  * @apiErrorExample Bad Authentication Token
  * 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
  * @apiErrorExample Insufficient User Rights
  * 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Roles not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The're no roles for the project with id 2"
  *     }
  *
  */

  /**
  * @api {get} /V0.7/roles/getprojectroles/:token/:projectId Get all project roles
  * @apiName GetProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.7.0
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} projectId Id of the projectId
  *
  * @apiSuccess {Object[]} Role Array of roles
  * @apiSuccess {Number} Role.id Role id
  * @apiSuccess {String} Role.name Role name
  * @apiSuccess {Number} Role.team_timeline Team timeline role
  * @apiSuccess {Number} Role.customer_timeline Customer timeline role
  * @apiSuccess {Number} Role.gantt Gantt role
  * @apiSuccess {Number} Role.whiteboard Whiteboard role
  * @apiSuccess {Number} Role.bugtracker Bugtracker role
  * @apiSuccess {Number} Role.event Event role
  * @apiSuccess {Number} Role.task Task role
  * @apiSuccess {Number} Role.project_settings Project settings role
  * @apiSuccess {Number} Role.cloud Cloud role
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"Role 1":
  *			{
  *				"id": 10,
  *				"name": "Intern roles",
  *				"team_timeline": 1,
  *				"customer_timeline": 0,
  *				"gantt": 0,
  *				"whiteboard": 0,
  *				"bugtracker": 1,
  *				"event": 0,
  *				"task": 0,
  *				"project_settings": 0,
  *				"cloud": 1
  * 		}
  *		}
  *
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Insufficient User Rights
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "Insufficient User Rights"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Roles not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The're no roles for the project with id 2"
  *     }
  *
  */
  public function getProjectRolesAction(Request $request, $token, $projectId)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($token);
  	if (!$user)
  		return ($this->setBadTokenError());
  	if (!$this->checkRoles($user, $projectId, "projectSettings"))
  		return $this->setNoRightsError();

  	$em = $this->getDoctrine()->getManager();

  	$roles = $em->getRepository('APIBundle:Role')->findByprojects($projectId);

  	if ($roles === null)
  	{
  		throw new NotFoundHttpException("The're no roles for the project with id ".$projectId);
  	}

  	$arr =array();
  	$i = 1;

  	foreach ($roles as $role) {
  		$roleId = $role->getId();
  		$roleName = $role->getName();
  		$teamTimeline = $role->getTeamTimeline();
  		$customerTimeline = $role->getCustomerTimeline();
  		$gantt = $role->getGantt();
  		$whiteboard = $role->getWhiteboard();
  		$bugtracker = $role->getBugtracker();
  		$event = $role->getEvent();
  		$task = $role->getTask();
  		$projectSettings = $role->getProjectSettings();
  		$cloud = $role->getCloud();

  		$arr["Role ".$i] = array("id" => $roleId, "name" => $roleName, "team_timeline" => $teamTimeline, "customer_timeline" => $customerTimeline, "gantt" => $gantt,
  			"whiteboard" => $whiteboard, "bugtracker" => $bugtracker, "event" => $event, "task" => $task, "project_settings" => $projectSettings, "cloud" => $cloud);
  		$i++;
	}

	return new JsonResponse($arr);
  }

  /**
  * @api {post} /V0.6/roles/assignpersontorole Assign a person to a role
  * @apiName assignPersonToRole
  * @apiGroup Roles
  * @apiVersion 0.6.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userId Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *		"_token": "aeqf231ced651qcd",
  *		"projectId": 1,
  *		"userId": 6,
  *		"roleId": 2
  * 	}
  *
  * @apiSuccess {Number} purId Id of the project user role created
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"purId":1
  * 	}
  *
  * @apiErrorExample Missing Parameter
  *		HTTP/1.1 400 Bad Request
  * 	{
  * 		"Missing Parameter"
  * 	}
  * @apiErrorExample Bad Authentication Token
  * 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
  * @apiErrorExample Insufficient User Rights
  * 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  *
  */

  /**
  * @api {post} /V0.7/roles/assignpersontorole Assign a person to a role
  * @apiName assignPersonToRole
  * @apiGroup Roles
  * @apiVersion 0.7.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userId Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *   "_token": "aeqf231ced651qcd",
  *   "projectId": 1,
  *   "userId": 6,
  *   "roleId": 2
  *   }
  *
  * @apiSuccess {Number} purId Id of the project user role created
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "purId":1
  *   }
  *
  * @apiErrorExample Missing Parameter
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Missing Parameter"
  *   }
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Insufficient User Rights
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "Insufficient User Rights"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  *
  */
  public function assignPersonToRoleAction(Request $request)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($content->_token);
  	if (!$content->projectId && !$content->roleId && !$content->userId)
	    return $this->setBadRequest("Missing Parameters");
    if (!$user)
		  return ($this->setBadTokenError());
    if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
		  return $this->setNoRightsError();

    $em = $this->getDoctrine()->getManager();
    $ProjectUserRole = new ProjectUserRole();

    $ProjectUserRole->setProjectId($content->projectId);
    $ProjectUserRole->setUserId($content->userId);
    $ProjectUserRole->setRoleId($content->roleId);

    $em->persist($ProjectUserRole);
    $em->flush();

    return new JsonResponse(array("purId" => $ProjectUserRole->getId()));
  }

  /**
  * @api {put} /V0.6/roles/putpersonrole Update a person role
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.6.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} roleId Id of the role
  * @apiParam {Number} purId Id of the Project user role to update
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *			"_token": "aeqf231ced651qcd",
  *			"projectId": 1,
  *			"roleId": 2,
  *			"purId": 1
  * 	}
  *
  * @apiSuccess message Update of the project user role success.
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"Update of the project user role success."
  * 	}
  *
  * @apiErrorExample Missing Parameter
  *		HTTP/1.1 400 Bad Request
  * 	{
  * 		"Missing Parameter"
  * 	}
  * @apiErrorExample Bad Authentication Token
  * 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
  * @apiErrorExample Insufficient User Rights
  * 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Project user role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project user role 1 doesn't exist."
  *     }
  *
  */

  /**
  * @api {put} /V0.7/roles/putpersonrole Update a person role
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.7.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} roleId Id of the role
  * @apiParam {Number} purId Id of the Project user role to update
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "roleId": 2,
  *     "purId": 1
  *   }
  *
  * @apiSuccess message Update of the project user role success.
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "Update of the project user role success."
  *   }
  *
  * @apiErrorExample Missing Parameter
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Missing Parameter"
  *   }
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Insufficient User Rights
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "Insufficient User Rights"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Project user role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project user role 1 doesn't exist."
  *     }
  *
  */
  public function updatePersonRoleAction(Request $request)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($content->_token);
  	if (!$content->projectId && !$content->roleId && !$content->purId)
		return $this->setBadRequest("Missing Parameters");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();
	$ProjectUserRole = $em->getRepository('APIBundle:ProjectUserRole')->find($content->purId);

	if ($ProjectUserRole === null)
	{
		throw new NotFoundHttpException("The project user role ".$content->purId." doesn't exist.");
	}

	$ProjectUserRole->setRoleId($content->roleId);
	$em->flush();

	return new JsonResponse("Update of the project user role success.");
  }

  /**
  * @api {delete} /V0.6/roles/delpersonrole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.6.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} purId Id of the Project user role to update
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *			"_token": "aeqf231ced651qcd",
  *			"projectId": 1,
  *			"purId": 1
  * 	}
  *
  * @apiSuccess message Update of the project user role success.
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"Remove project user role success."
  * 	}
  *
  * @apiErrorExample Missing Parameter
  *		HTTP/1.1 400 Bad Request
  * 	{
  * 		"Missing Parameter"
  * 	}
  * @apiErrorExample Bad Authentication Token
  * 	HTTP/1.1 400 Bad Request
  * 	{
  * 		"Bad Authentication Token"
  * 	}
  * @apiErrorExample Insufficient User Rights
  * 	HTTP/1.1 403 Forbidden
  * 	{
  * 		"Insufficient User Rights"
  * 	}
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Project user role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project user role with id 1 doesn't exist."
  *     }
  *
  */

  /**
  * @api {delete} /V0.7/roles/delpersonrole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.7.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} purId Id of the Project user role to update
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "purId": 1
  *   }
  *
  * @apiSuccess message Update of the project user role success.
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "Remove project user role success."
  *   }
  *
  * @apiErrorExample Missing Parameter
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Missing Parameter"
  *   }
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Insufficient User Rights
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "Insufficient User Rights"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample Project user role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project user role with id 1 doesn't exist."
  *     }
  *
  */
  public function delPersonRoleAction(Request $request)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($content->_token);
  	if (!$content->projectId && !$content->purId)
		return $this->setBadRequest("Missing Parameters");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();

	$role = $em->getRepository('APIBundle:ProjectUserRole')->find($content->purId);

	if ($role === null)
	{
		throw new NotFoundHttpException("The project user role with id ".$content->purId." doesn't exist.");
	}

	$em->remove($role);
	$em->flush();

	return new JsonResponse("Remove project user role success.");
  }
}
