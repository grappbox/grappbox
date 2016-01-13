<?php

namespace GrappboxBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use GrappboxBundle\Entity\Role;
use GrappboxBundle\Entity\ProjectUserRole;
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
    $user = $em->getRepository('GrappboxBundle:User')->findOneBy(array('token' => $token));

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
                      FROM GrappboxBundle:Role roles
                      JOIN GrappboxBundle:ProjectUserRole projectUser WITH roles.id = projectUser.roleId
                      WHERE projectUser.projectId = '.$projectId.' AND projectUser.userId = '.$user->getId());
    $result = $query->setMaxResults(1)->getOneOrNullResult();
    return $result[$role];
  }

  protected function setBadTokenError($code, $part, $function)
  {
    $ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - Bad ID");
    $response = new JsonResponse($ret);
    $response->setStatusCode(JsonResponse::HTTP_UNAUTHORIZED);

    return $response;
  }

  protected function setNoRightsError($code, $part, $function)
  {
    $ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - Insufficient Rights");
    $response = new JsonResponse($ret);
    $response->setStatusCode(JsonResponse::HTTP_FORBIDDEN);

    return $response;
  }

  protected function setBadRequest($code, $part, $function, $message)
  {
  	$ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - ".$message);
    $response = new JsonResponse($ret);
    $response->setStatusCode(JsonResponse::HTTP_BAD_REQUEST);

    return $response;
  }

  protected function setNoDataSuccess($code, $part, $function)
  {
    $ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - "."Success but no data");
    $ret["data"] = array("array" => array());
    $response = new JsonResponse($ret);
    $response->setStatusCode(JsonResponse::HTTP_PARTIAL_CONTENT);

    return $response;
  }

  protected function setSuccess($code, $part, $function, $message, $data)
  {
    $ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - ".$message);
    $ret["data"] = $data;
    $response = new JsonResponse($ret);
    $response->setStatusCode(JsonResponse::HTTP_OK);

    return $response;
  }

  protected function setCreated($code, $part, $function, $message, $data)
  {
    $ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - ".$message);
    $ret["data"] = $data;
    $response = new JsonResponse($ret);
    $response->setStatusCode(JsonResponse::HTTP_CREATED);

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

  /**
  * @api {post} /V0.8/roles/addprojectroles Add a project role
  * @apiName addProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.8.0
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

  /**
  * @api {post} /V0.8/roles/addprojectroles Add a project role
  * @apiName addProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.8.1
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

  /**
  * @api {post} /V0.9/roles/addprojectroles Add a project role
  * @apiName addProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.9.0
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

  /**
  * @api {post} /V0.10/roles/addprojectroles Add a project role
  * @apiName addProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.10.0
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

  /**
  * @api {post} /V0.11/roles/addprojectroles Add a project role
  * @apiName addProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.11.0
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

    $project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);
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

  /**
  * @api {delete} /V0.8/roles/delprojectroles Delete a project role
  * @apiName delProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.8.0
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

  /**
  * @api {delete} /V0.8/roles/delprojectroles Delete a project role
  * @apiName delProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.8.1
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
  * @apiErrorExample Can't remove
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "You can't remove the Admin role"
  *   }
  *
  */

  /**
  * @api {delete} /V0.9/roles/delprojectroles Delete a project role
  * @apiName delProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.9.0
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
  * @apiErrorExample Can't remove
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "You can't remove the Admin role"
  *   }
  *
  */

  /**
  * @api {delete} /V0.10/roles/delprojectroles Delete a project role
  * @apiName delProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.10.0
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
  * @apiErrorExample Can't remove
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "You can't remove the Admin role"
  *   }
  *
  */

  /**
  * @api {delete} /V0.11/roles/delprojectroles Delete a project role
  * @apiName delProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.11.0
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
  * @apiErrorExample Can't remove
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "You can't remove the Admin role"
  *   }
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

    $role = $em->getRepository('GrappboxBundle:Role')->find($content->roleId);

    if ($role === null)
    {
      throw new NotFoundHttpException("The role with id ".$content->roleId." doesn't exist.");
    }

    if ($role->getName() == "Admin")
    {
      return new JsonResponse('You can\'t remove the Admin role', JsonResponse::HTTP_FORBIDDEN);
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

  /**
  * @api {put} /V0.8/roles/putprojectroles Update a project role
  * @apiName updateProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.8.0
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

  /**
  * @api {put} /V0.8/roles/putprojectroles Update a project role
  * @apiName updateProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.8.1
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

  /**
  * @api {put} /V0.9/roles/putprojectroles Update a project role
  * @apiName updateProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.9.0
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

  /**
  * @api {put} /V0.10/roles/putprojectroles Update a project role
  * @apiName updateProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.10.0
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

  /**
  * @api {put} /V0.11/roles/putprojectroles Update a project role
  * @apiName updateProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.11.0
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

    $role = $em->getRepository('GrappboxBundle:Role')->find($content->roleId);

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

  /**
  * @api {get} /V0.8/roles/getprojectroles/:token/:projectId Get all project roles
  * @apiName GetProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.8.0
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
  *   {
  *     "Role 1":
  *     {
  *       "id": 10,
  *       "name": "Intern roles",
  *       "team_timeline": 1,
  *       "customer_timeline": 0,
  *       "gantt": 0,
  *       "whiteboard": 0,
  *       "bugtracker": 1,
  *       "event": 0,
  *       "task": 0,
  *       "project_settings": 0,
  *       "cloud": 1
  *     }
  *   }
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

  /**
  * @api {get} /V0.8/roles/getprojectroles/:token/:projectId Get all project roles
  * @apiName GetProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.8.1
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
  *   {
  *     "Role 1":
  *     {
  *       "id": 10,
  *       "name": "Intern roles",
  *       "team_timeline": 1,
  *       "customer_timeline": 0,
  *       "gantt": 0,
  *       "whiteboard": 0,
  *       "bugtracker": 1,
  *       "event": 0,
  *       "task": 0,
  *       "project_settings": 0,
  *       "cloud": 1
  *     }
  *   }
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

  /**
  * @api {get} /V0.9/roles/getprojectroles/:token/:projectId Get all project roles
  * @apiName GetProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.9.0
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
  *   {
  *     "Role 1":
  *     {
  *       "id": 10,
  *       "name": "Intern roles",
  *       "team_timeline": 1,
  *       "customer_timeline": 0,
  *       "gantt": 0,
  *       "whiteboard": 0,
  *       "bugtracker": 1,
  *       "event": 0,
  *       "task": 0,
  *       "project_settings": 0,
  *       "cloud": 1
  *     }
  *   }
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

  /**
  * @api {get} /V0.10/roles/getprojectroles/:token/:projectId Get all project roles
  * @apiName GetProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.10.0
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
  *   {
  *     "Role 1":
  *     {
  *       "id": 10,
  *       "name": "Intern roles",
  *       "team_timeline": 1,
  *       "customer_timeline": 0,
  *       "gantt": 0,
  *       "whiteboard": 0,
  *       "bugtracker": 1,
  *       "event": 0,
  *       "task": 0,
  *       "project_settings": 0,
  *       "cloud": 1
  *     }
  *   }
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

  /**
  * @api {get} /V0.11/roles/getprojectroles/:token/:projectId Get all project roles
  * @apiName GetProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.11.0
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
  *   {
  *     "Role 1":
  *     {
  *       "id": 10,
  *       "name": "Intern roles",
  *       "team_timeline": 1,
  *       "customer_timeline": 0,
  *       "gantt": 0,
  *       "whiteboard": 0,
  *       "bugtracker": 1,
  *       "event": 0,
  *       "task": 0,
  *       "project_settings": 0,
  *       "cloud": 1
  *     }
  *   }
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

  	$roles = $em->getRepository('GrappboxBundle:Role')->findByprojects($projectId);

  	if ($roles === null)
  	{
  		throw new NotFoundHttpException("The're no roles for the project with id ".$projectId);
  	}

  	$arr =array();
  	$i = 1;

    if (count($roles) == 0)
    {
      return new JsonResponse((Object)$arr);
    }

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
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 6,
  *     "roleId": 2
  *   }
  *
  * @apiSuccess {Number} purId Id of the project user role created
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "person assign to role"
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

  /**
  * @api {post} /V0.8/roles/assignpersontorole Assign a person to a role
  * @apiName assignPersonToRole
  * @apiGroup Roles
  * @apiVersion 0.8.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userId Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 6,
  *     "roleId": 2
  *   }
  *
  * @apiSuccess {Number} purId Id of the project user role created
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "person assign to role"
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

  /**
  * @api {post} /V0.8/roles/assignpersontorole Assign a person to a role
  * @apiName assignPersonToRole
  * @apiGroup Roles
  * @apiVersion 0.8.1
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userId Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 6,
  *     "roleId": 2
  *   }
  *
  * @apiSuccess {Number} purId Id of the project user role created
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "person assign to role"
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

  /**
  * @api {post} /V0.9/roles/assignpersontorole Assign a person to a role
  * @apiName assignPersonToRole
  * @apiGroup Roles
  * @apiVersion 0.9.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userId Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 6,
  *     "roleId": 2
  *   }
  *
  * @apiSuccess {Number} purId Id of the project user role created
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "person assign to role"
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

  /**
  * @api {post} /V0.10/roles/assignpersontorole Assign a person to a role
  * @apiName assignPersonToRole
  * @apiGroup Roles
  * @apiVersion 0.10.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userId Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 6,
  *     "roleId": 2
  *   }
  *
  * @apiSuccess {Number} purId Id of the project user role created
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "person assign to role"
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

  /**
  * @api {post} /V0.11/roles/assignpersontorole Assign a person to a role
  * @apiName assignPersonToRole
  * @apiGroup Roles
  * @apiVersion 0.11.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userId Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 6,
  *     "roleId": 2
  *   }
  *
  * @apiSuccess {Number} purId Id of the project user role created
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "person assign to role"
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

    return new JsonResponse("person assign to role");
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
  * @apiParam {Number} projectId Id of the project for searching
  * @apiParam {Number} userId Id of the user for searching
  * @apiParam {Number} old_roleId Old id of the role for searching
  * @apiParam {Number} roleId new role id
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "old_roleId": 2,
  *     "roleId": 3
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
  *       "The project user role doesn't exist."
  *     }
  *
  */

  /**
  * @api {put} /V0.8/roles/putpersonrole Update a person role
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.8.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project for searching
  * @apiParam {Number} userId Id of the user for searching
  * @apiParam {Number} old_roleId Old id of the role for searching
  * @apiParam {Number} roleId new role id
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "old_roleId": 2,
  *     "roleId": 3
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
  *       "The project user role doesn't exist."
  *     }
  *
  */

  /**
  * @api {put} /V0.8/roles/putpersonrole Update a person role
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.8.1
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project for searching
  * @apiParam {Number} userId Id of the user for searching
  * @apiParam {Number} old_roleId Old id of the role for searching
  * @apiParam {Number} roleId new role id
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "old_roleId": 2,
  *     "roleId": 3
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
  *       "The project user role doesn't exist."
  *     }
  *
  */

  /**
  * @api {put} /V0.9/roles/putpersonrole Update a person role
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.9.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project for searching
  * @apiParam {Number} userId Id of the user for searching
  * @apiParam {Number} old_roleId Old id of the role for searching
  * @apiParam {Number} roleId new role id
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "old_roleId": 2,
  *     "roleId": 3
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
  *       "The project user role doesn't exist."
  *     }
  *
  */

  /**
  * @api {put} /V0.10/roles/putpersonrole Update a person role
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.10.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project for searching
  * @apiParam {Number} userId Id of the user for searching
  * @apiParam {Number} old_roleId Old id of the role for searching
  * @apiParam {Number} roleId new role id
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "old_roleId": 2,
  *     "roleId": 3
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
  *       "The project user role doesn't exist."
  *     }
  *
  */

  /**
  * @api {put} /V0.11/roles/putpersonrole Update a person role
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.11.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project for searching
  * @apiParam {Number} userId Id of the user for searching
  * @apiParam {Number} old_roleId Old id of the role for searching
  * @apiParam {Number} roleId new role id
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "old_roleId": 2,
  *     "roleId": 3
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
  *       "The project user role doesn't exist."
  *     }
  *
  */
  public function updatePersonRoleAction(Request $request)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($content->_token);
  	if (!$content->projectId && !$content->old_roleId && !$content->userId && !$content->roleId)
		return $this->setBadRequest("Missing Parameters");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();

    $repository = $em->getRepository('GrappboxBundle:ProjectUserRole');

    $qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
    ->setParameter('projectId', $content->projectId)->setParameter('userId', $content->userId)->setParameter('roleId', $content->old_roleId)->getQuery();
    $ProjectUserRoles = $qb->getResult();

	if ($ProjectUserRoles === null)
	{
		throw new NotFoundHttpException("The project user role doesn't exist.");
	}

  foreach ($ProjectUserRoles as $pur) {
    $pur->setRoleId($content->roleId);
  }

	$em->flush();

	return new JsonResponse("Update of the project user role success.");
  }

  /**
  * @api {get} /V0.8/roles/getuserroles/:token Get the roles of the user connected
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.8.0
  *
  * @apiParam {String} token Token of the person connected
  *
  * @apiSuccess {Object[]} UserRole Array of user roles
  * @apiSuccess {Number} UserRole.id Project user role id
  * @apiSuccess {Number} UserRole.project_id Id of the project
  * @apiSuccess {Number} UserRole.role_id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "UserRole 1":
  *     {
  *       "id": 10,
  *       "project_id": 5,
  *       "role_id": 1
  *     }
  *   }
  *
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample User roles not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The user X don't have roles."
  *     }
  *
  */

  /**
  * @api {get} /V0.8/roles/getuserroles/:token Get the roles of the user connected
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.8.1
  *
  * @apiParam {String} token Token of the person connected
  *
  * @apiSuccess {Object[]} UserRole Array of user roles
  * @apiSuccess {Number} UserRole.id Project user role id
  * @apiSuccess {Number} UserRole.project_id Id of the project
  * @apiSuccess {Number} UserRole.role_id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "UserRole 1":
  *     {
  *       "id": 10,
  *       "project_id": 5,
  *       "role_id": 1
  *     }
  *   }
  *
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample User roles not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The user X don't have roles."
  *     }
  *
  */

  /**
  * @api {get} /V0.9/roles/getuserroles/:token Get the roles of the user connected
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.9.0
  *
  * @apiParam {String} token Token of the person connected
  *
  * @apiSuccess {Object[]} UserRole Array of user roles
  * @apiSuccess {Number} UserRole.id Project user role id
  * @apiSuccess {Number} UserRole.project_id Id of the project
  * @apiSuccess {Number} UserRole.role_id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "UserRole 1":
  *     {
  *       "id": 10,
  *       "project_id": 5,
  *       "role_id": 1
  *     }
  *   }
  *
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample User roles not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The user X don't have roles."
  *     }
  *
  */

  /**
  * @api {get} /V0.10/roles/getuserroles/:token Get the roles of the user connected
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.10.0
  *
  * @apiParam {String} token Token of the person connected
  *
  * @apiSuccess {Object[]} UserRole Array of user roles
  * @apiSuccess {Number} UserRole.id Project user role id
  * @apiSuccess {Number} UserRole.project_id Id of the project
  * @apiSuccess {Number} UserRole.role_id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "UserRole 1":
  *     {
  *       "id": 10,
  *       "project_id": 5,
  *       "role_id": 1
  *     }
  *   }
  *
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample User roles not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The user X don't have roles."
  *     }
  *
  */

  /**
  * @api {get} /V0.11/roles/getuserroles/:token Get the roles of the user connected
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.11.0
  *
  * @apiParam {String} token Token of the person connected
  *
  * @apiSuccess {Object[]} UserRole Array of user roles
  * @apiSuccess {Number} UserRole.id Project user role id
  * @apiSuccess {Number} UserRole.project_id Id of the project
  * @apiSuccess {Number} UserRole.role_id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "UserRole 1":
  *     {
  *       "id": 10,
  *       "project_id": 5,
  *       "role_id": 1
  *     }
  *   }
  *
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  * @apiErrorExample User roles not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The user X don't have roles."
  *     }
  *
  */
  public function getUserRolesAction(Request $request, $token)
  {
  	$user = $this->checkToken($token);
  	if (!$user)
		return ($this->setBadTokenError());

  	$em = $this->getDoctrine()->getManager();
	$userRoles = $em->getRepository('GrappboxBundle:ProjectUserRole')->findByuserId($user->getId());

	if ($userRoles === null)
	{
		throw new NotFoundHttpException("The user ".$user->getId()." don't have roles.");
	}

	$arr = array();
	$i = 1;

  if (count($userRoles) == 0)
  {
    return new JsonResponse((Object)$arr);
  }

	foreach ($userRoles as $role) {
		$purId = $role->getId();
		$projectId = $role->getProjectId();
		$roleId = $role->getRoleId();

		$arr["UserRole ".$i] = array("id" => $purId, "project_id" => $projectId, "role_id" => $roleId);
		$i++;
	}

	return new JsonResponse($arr);
  }

  /**
  * @api {delete} /V0.6/roles/delpersonrole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.6.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} purId Id of the Project user role
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *			"_token": "aeqf231ced651qcd",
  *			"projectId": 1,
  *			"purId": 1
  * 	}
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
  * @apiParam {Number} userd Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "roleId": 3
  *   }
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

  /**
  * @api {delete} /V0.8/roles/delpersonrole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.8.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userd Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "roleId": 3
  *   }
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

  /**
  * @api {delete} /V0.8/roles/delpersonrole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.8.1
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userd Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "roleId": 3
  *   }
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
    * @apiErrorExample Project or role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project or the role doesn't exist."
  *     }
  *
  * @apiErrorExample Can't remove
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "You can't remove the creator from Admin role"
  *   }
  *
  */

  /**
  * @api {delete} /V0.9/roles/delpersonrole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.9.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userd Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "roleId": 3
  *   }
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
    * @apiErrorExample Project or role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project or the role doesn't exist."
  *     }
  *
  * @apiErrorExample Can't remove
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "You can't remove the creator from Admin role"
  *   }
  *
  */

  /**
  * @api {delete} /V0.10/roles/delpersonrole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.10.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userd Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "roleId": 3
  *   }
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
    * @apiErrorExample Project or role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project or the role doesn't exist."
  *     }
  *
  * @apiErrorExample Can't remove
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "You can't remove the creator from Admin role"
  *   }
  *
  */

  /**
  * @api {delete} /V0.11/roles/delpersonrole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.11.0
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} userd Id of the user
  * @apiParam {Number} roleId Id of the role
  *
  * @apiParamExample {json} Request-Example:
  *   {
  *     "_token": "aeqf231ced651qcd",
  *     "projectId": 1,
  *     "userId": 1,
  *     "roleId": 3
  *   }
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
    * @apiErrorExample Project or role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project or the role doesn't exist."
  *     }
  *
  * @apiErrorExample Can't remove
  *   HTTP/1.1 403 Forbidden
  *   {
  *     "You can't remove the creator from Admin role"
  *   }
  *
  */
  public function delPersonRoleAction(Request $request)
  {
    $content = $request->getContent();
    $content = json_decode($content);

  	$user = $this->checkToken($content->_token);
  	if (!$content->projectId && !$content->userId && !$content->roleId)
		return $this->setBadRequest("Missing Parameters");
  	if (!$user)
  		return ($this->setBadTokenError());
  	if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
  		return $this->setNoRightsError();

    $em = $this->getDoctrine()->getManager();

    $project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);
    $role = $em->getRepository('GrappboxBundle:Role')->find($content->roleId);

    if ($project === null || $role === null)
    {
      throw new NotFoundHttpException("The project or the role doesn't exist.");
    }

    if ($project->getCreatorUser()->getId() == $content->userId && $role->getName() == "Admin")
    {
      return new JsonResponse('You can\'t remove the creator from Admin role', JsonResponse::HTTP_FORBIDDEN);
    }

    $repository = $em->getRepository('GrappboxBundle:ProjectUserRole');

    $qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
    ->setParameter('projectId', $content->projectId)->setParameter('userId', $content->userId)->setParameter('roleId', $content->roleId)->getQuery();
    $ProjectUserRoles = $qb->getResult();

    if (count($ProjectUserRoles) == 0)
    {
      throw new NotFoundHttpException("The project user role doesn't exist.");
    }

    foreach ($ProjectUserRoles as $role) {
      $em->remove($role);
    }

  	$em->flush();

  	return new JsonResponse("Remove project user role success.");
  }

  /**
  * @api {get} /V0.8/roles/getrolebyprojectanduser/:token/:projectId/:userId Get the roles id for a given user on a given project
  * @apiName getRoleByProjectAndUser
  * @apiGroup Roles
  * @apiVersion 0.8.0
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam [Number] userId Id of the user
  *
  * @apiSuccess {Object[]} Role Array of user roles
  * @apiSuccess {Number} Role.id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "Role 1":
  *     {
  *       "id": 10
  *     }
  *   }
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
  *       "The're no roles for the user X for the project Y"
  *     }
  *
  */

  /**
  * @api {get} /V0.8/roles/getrolebyprojectanduser/:token/:projectId/:userId Get the roles id for a given user on a given project
  * @apiName getRoleByProjectAndUser
  * @apiGroup Roles
  * @apiVersion 0.8.1
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam [Number] userId Id of the user
  *
  * @apiSuccess {Object[]} Role Array of user roles
  * @apiSuccess {Number} Role.id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "Role 1":
  *     {
  *       "id": 10
  *     }
  *   }
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
  *       "The're no roles for the user X for the project Y"
  *     }
  *
  */

  /**
  * @api {get} /V0.9/roles/getrolebyprojectanduser/:token/:projectId/:userId Get the roles id for a given user on a given project
  * @apiName getRoleByProjectAndUser
  * @apiGroup Roles
  * @apiVersion 0.9.0
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam [Number] userId Id of the user
  *
  * @apiSuccess {Object[]} Role Array of user roles
  * @apiSuccess {Number} Role.id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "Role 1":
  *     {
  *       "id": 10
  *     }
  *   }
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
  *       "The're no roles for the user X for the project Y"
  *     }
  *
  */

  /**
  * @api {get} /V0.10/roles/getrolebyprojectanduser/:token/:projectId/:userId Get the roles id for a given user on a given project
  * @apiName getRoleByProjectAndUser
  * @apiGroup Roles
  * @apiVersion 0.10.0
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam [Number] userId Id of the user
  *
  * @apiSuccess {Object[]} Role Array of user roles
  * @apiSuccess {Number} Role.id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "Role 1":
  *     {
  *       "id": 10
  *     }
  *   }
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
  *       "The're no roles for the user X for the project Y"
  *     }
  *
  */

  /**
  * @api {get} /V0.11/roles/getrolebyprojectanduser/:token/:projectId/:userId Get the roles id for a given user on a given project
  * @apiName getRoleByProjectAndUser
  * @apiGroup Roles
  * @apiVersion 0.11.0
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam [Number] userId Id of the user
  *
  * @apiSuccess {Object[]} Role Array of user roles
  * @apiSuccess {Number} Role.id Id of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "Role 1":
  *     {
  *       "id": 10
  *     }
  *   }
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
  *       "The're no roles for the user X for the project Y"
  *     }
  *
  */
  public function getRoleByProjectAndUserAction(Request $request, $token, $projectId, $userId)
  {
    $user = $this->checkToken($token);
    if (!$user)
      return ($this->setBadTokenError());
    if (!$this->checkRoles($user, $projectId, "projectSettings"))
      return $this->setNoRightsError();
    $em = $this->getDoctrine()->getManager();
    $repository = $em->getRepository('GrappboxBundle:ProjectUserRole');

    $qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId')->setParameter('projectId', $projectId)->setParameter('userId', $userId)->getQuery();
    $purs = $qb->getResult();

    if ($purs === null)
    {
      throw new NotFoundHttpException("The're no roles for the user ".$userId." for the project ".$projectId);
    }

    $arr = array();
    $i = 1;

    if (count($purs) == 0)
    {
      return new JsonResponse((Object)$arr);
    }

    foreach ($purs as $role) {
      $roleId = $role->getRoleId();
      $arr["Role ".$i] = array("id" => $roleId);
      $i++;
    }

    return new JsonResponse($arr);
  }

  /**
  * @api {get} /V0.11/roles/getusersforrole/:token/:roleId Get the users assigned and non assigned on the role
  * @apiName getUsersForRole
  * @apiGroup Roles
  * @apiVersion 0.11.0
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} roleId Id of the role
  *
  * @apiSuccess {Number} id Id of the role
  * @apiSuccess {String} name Name of the role
  * @apiSuccess {Object[]} users_assigned Array of users assigned to the role
  * @apiSuccess {Number} users_assigned.id Id of the user
  * @apiSuccess {String} users_assigned.firstname Firstname of the user
  * @apiSuccess {String} users_assigned.lastname Lastname of the user
  * @apiSuccess {Object[]} users_non_assigned Array of users non assigned to the role
  * @apiSuccess {Number} users_non_assigned.id Id of the user
  * @apiSuccess {String} users_non_assigned.firstname Firstname of the user
  * @apiSuccess {String} users_non_assigned.lastname Lastname of the user
  *
  * @apiSuccessExample Success-Response:
  *   {
  *     "id": 2,
  *     "name": "Admin",
  *     "users_assigned": [
  *       {
  *         "id": 1,
  *         "firstname": "john",
  *         "lastname": "doe"
  *       }
  *     ],
  *     "users-non_assigned": [
  *       {
  *         "id": 3,
  *         "firstname": "jean",
  *         "lastname": "neige"
  *       },
  *       {
  *         "id": 8,
  *         "firstname": "john",
  *         "lastname": "snow"
  *       }
  *     ]
  *   }
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
  * @apiErrorExample Role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The role with id X doesn't exist"
  *     }
  *
  */
  public function getUsersForRoleAction(Request $request, $token, $roleId)
  {
    $user = $this->checkToken($token);
    if (!$user)
      return ($this->setBadTokenError());

    $em = $this->getDoctrine()->getManager();
    $role = $em->getRepository('GrappboxBundle:Role')->find($roleId);
    if ($role === null)
    {
      throw new NotFoundHttpException("The role with id ".$roleId." doesn't exist.");
    }

    if (!$this->checkRoles($user, $role->getProjects()->getId(), "projectSettings"))
      return $this->setNoRightsError();

    $purRepository = $em->getRepository('GrappboxBundle:ProjectUserRole');
    $qb = $purRepository->createQueryBuilder('pur')->where('pur.roleId = :id')->setParameter('id', $role->getId())->getQuery();
    $purs = $qb->getResult();

    $usersAssigned = array();
    $usersNonAssigned = array();

    $users = $role->getProjects()->getUsers();
    print($role->getId());

    foreach ($users as $u) {
      $isAssigned = false;

      foreach ($purs as $p) {
        if ($p->getUserId() == $u->getId())
        {
          $usersAssigned[] = array("id" => $u->getId(), "firstname" => $u->getFirstname(), "lastname" => $u->getLastname());
          $isAssigned = true;
        }
      }
      if ($isAssigned == false)
        $usersNonAssigned[] = array("id" => $u->getId(), "firstname" => $u->getFirstname(), "lastname" => $u->getLastname());
    }

    return new JsonResponse(array("id" => $role->getId(),"name" => $role->getName(), "users_assigned" => $usersAssigned, "users_non_assigned" => $usersNonAssigned));
  }

  /**
  * @api {get} /V0.11/roles/getuserrolesinformations/:token Get the roles informations of the user connected
  * @apiName getUserConnectedRolesInformations
  * @apiGroup Roles
  * @apiVersion 0.11.0
  *
  * @apiParam {String} token Token of the person connected
  *
  * @apiSuccess {Object[]} user_role Array of user roles informations
  * @apiSuccess {Number} user_role.id Project user role id
  * @apiSuccess {Object[]} user_role.project Project informations
  * @apiSuccess {Number} user_role.project.id Id of the project
  * @apiSuccess {String} user_role.project.name Name of the project
  * @apiSuccess {Object[]} user_role.role Role informations
  * @apiSuccess {Number} user_role.role.id Id of the role
  * @apiSuccess {String} user_role.role.name Name of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *   "user_role": [
  *       "id": 10,
  *       "project": {
  *       "id": 2,
  *       "name": "Grappbox"
  *     },
  *       "role": {
  *       "id": 6,
  *       "name": "Admin"
  *     }
  *   ],
  *   [
  *       "id": 30,
  *       "project": {
  *       "id": 2,
  *       "name": "Grappbox"
  *     },
  *       "role": {
  *       "id": 6,
  *       "name": "Graphists"
  *     }
  *   ]
  *   }
  *
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample User roles not found
  *   HTTP/1.1 404 Not Found
  *   {
  *     "The user X don't have roles."
  *   }
  */
  public function getUserConnectedRolesInfosAction(Request $request, $token)
  {
    $user = $this->checkToken($token);
    if (!$user)
    return ($this->setBadTokenError());

    $em = $this->getDoctrine()->getManager();
  $userRoles = $em->getRepository('GrappboxBundle:ProjectUserRole')->findByuserId($user->getId());

  if ($userRoles === null)
  {
    throw new NotFoundHttpException("The user ".$user->getId()." don't have roles.");
  }

  $arr = array();

  foreach ($userRoles as $role) {
    $purId = $role->getId();

    $projectId = $role->getProjectId();
    $project = $em->getRepository('GrappboxBundle:Project')->find($projectId);
    $projectName = $project->getName();

    $roleId = $role->getRoleId();
    $role = $em->getRepository('GrappboxBundle:Role')->find($roleId);
    $roleName = $role->getName();

    $arr[] = array("id" => $purId, "project" => array("id" => $projectId, "name" => $projectName), "role" => array("id" => $roleId, "name" => $roleName));
  }

  return new JsonResponse(array("user_role" => $arr));
  }

  /**
  * @api {get} /V0.11/roles/getuserrolesinformations/:token/:id Get the roles informations of the given user
  * @apiName getUserRolesInformations
  * @apiGroup Roles
  * @apiVersion 0.11.0
  *
  * @apiParam {String} token Token of the person connected
  * @apiParam {Number} userId Id of the user you want the roles 
  *
  * @apiSuccess {Object[]} user_role Array of user roles informations
  * @apiSuccess {Number} user_role.id Project user role id
  * @apiSuccess {Object[]} user_role.project Project informations
  * @apiSuccess {Number} user_role.project.id Id of the project
  * @apiSuccess {String} user_role.project.name Name of the project
  * @apiSuccess {Object[]} user_role.role Role informations
  * @apiSuccess {Number} user_role.role.id Id of the role
  * @apiSuccess {String} user_role.role.name Name of the role
  *
  * @apiSuccessExample Success-Response:
  *   {
  *   "user_role": [
  *       "id": 10,
  *       "project": {
  *       "id": 2,
  *       "name": "Grappbox"
  *     },
  *       "role": {
  *       "id": 6,
  *       "name": "Admin"
  *     }
  *   ],
  *   [
  *       "id": 30,
  *       "project": {
  *       "id": 2,
  *       "name": "Grappbox"
  *     },
  *       "role": {
  *       "id": 6,
  *       "name": "Graphists"
  *     }
  *   ]
  *   }
  *
  * @apiErrorExample Bad Authentication Token
  *   HTTP/1.1 400 Bad Request
  *   {
  *     "Bad Authentication Token"
  *   }
  * @apiErrorExample User roles not found
  *   HTTP/1.1 404 Not Found
  *   {
  *     "The user X don't have roles."
  *   }
  */
  public function getUserRolesInfosAction(Request $request, $token, $userId)
  {
    $user = $this->checkToken($token);
    if (!$user)
    return ($this->setBadTokenError());

    $em = $this->getDoctrine()->getManager();
  $userRoles = $em->getRepository('GrappboxBundle:ProjectUserRole')->findByuserId($userId);

  if ($userRoles === null)
  {
    throw new NotFoundHttpException("The user ".$userId." don't have roles.");
  }

  $arr = array();

  foreach ($userRoles as $role) {
    $purId = $role->getId();

    $projectId = $role->getProjectId();
    $project = $em->getRepository('GrappboxBundle:Project')->find($projectId);
    $projectName = $project->getName();

    $roleId = $role->getRoleId();
    $role = $em->getRepository('GrappboxBundle:Role')->find($roleId);
    $roleName = $role->getName();

    $arr[] = array("id" => $purId, "project" => array("id" => $projectId, "name" => $projectName), "role" => array("id" => $roleId, "name" => $roleName));
  }

  return new JsonResponse(array("user_role" => $arr));
  }
}
