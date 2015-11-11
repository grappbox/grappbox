<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use APIBundle\Entity\Role;
use APIBundle\Entity\ProjectUserRole;

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
  * @api {post} /V1/API/Roles/addProjectRoles Add a project role
  * @apiName addProjectRoles
  * @apiGroup Roles
  * @apiVersion 0.0.1
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
  *		"_token": "aeqf231ced651qcd",
  *		"projectId": 1,
  *		"name": "Admin",
  *		"teamTimeline": 1,
  *		"customerTimeline": 1,
  *		"gantt": 1,
  *		"whiteboard": 1,
  *		"bugtracker": 1,
  *		"event": 1,
  *		"task": 1,
  *		"projectSettings": 1,
  *		"cloud": 1
  * 	}
  *
  * @apiSuccess {Number} roleId Id of the role created
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"roleId":1
  * 	}
  *
  * @apiError message Missing parameter
  * @apiError message Bad authentication token
  * @apiError message Insufficient user rights
  * @apiErrorExample Invalid Method Value
  *     HTTP/1.1 404 Not Found
  *     {
  *       "message": "404 not found."
  *     }
  *
  */
  public function addProjectRolesAction(Request $request)
  {
  	$user = $this->checkToken($request->request->get('_token'));
  	if (!$request->request->get('projectId'))
		return $this->setBadRequest("Missing Parameter");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $request->request->get('projectId'), "role"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();
	$role = new Role();

	$role->setProjectId($request->request->get('projectId'));
	$role->setName($request->request->get('name'));
	$role->setTeamTimeline($request->request->get('teamTimeline'));
	$role->setCustomerTimeline($request->request->get('customerTimeline'));
	$role->setGantt($request->request->get('gantt'));
	$role->setWhiteboard($request->request->get('whiteboard'));
	$role->setBugtracker($request->request->get('bugtracker'));
	$role->setEvent($request->request->get('event'));
	$role->setTask($request->request->get('task'));
	$role->setProjectSettings($request->request->get('projectSettings'));
	$role->setCloud($request->request->get('cloud'));

	$em->persist($role);
	$em->flush();

	return new JsonResponse(array("roleId" => $role->getId()));
  }

  /**
	* @api {delete} /V1/API/Roles/delProjectRoles Delete a project role
	* @apiName delProjectRoles
	* @apiGroup Roles
	* @apiVersion 0.0.1
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
	* @apiError message Missing parameters
	* @apiError message Bad authentication token
	* @apiError message Insufficient user rights
	* @apiErrorExample Role not found
	*     HTTP/1.1 404 Not Found
	*     {
	*       "The role with id 3 doesn't exist."
	*     }
	*
	*/
  public function delProjectRolesAction(Request $request)
  {
  	$user = $this->checkToken($request->request->get('_token'));
  	if (!$request->request->get('projectId') && !$request->request->get('roleId'))
		return $this->setBadRequest("Missing Parameters");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $request->request->get('projectId'), "role"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();

	$role = $em->getRepository('APIBundle:Role')->find($request->request->get('roleId'));
	
	if ($role === null)
	{
		throw new NotFoundHttpException("The role with id ".$request->request->get('roleId')." doesn't exist.");
	}

	$em->remove($role);
	$em->flush();

	return new JsonResponse("Remove role success.");
  }

  	/**
	* @api {put} /V1/API/Roles/updateProjectRoles Update a project role
	* @apiName updateProjectRoles
	* @apiGroup Roles
	* @apiVersion 0.0.1
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
	*		"_token": "aeqf231ced651qcd",
	*		"roleId": 2,
	*		"projectId": 1,
	*		"name": "Admin",
	*		"customerTimeline": 0,
	*		"cloud": 0
	* 	}
	*
	* @apiSuccess message Update role success.
	*
	* @apiSuccessExample Success-Response:
	* 	{
	*		"Update role success."
	* 	}
	*
	* @apiError message Missing parameter
	* @apiError message Bad authentication token
	* @apiError message Insufficient user rights
	* @apiErrorExample Role not found
	*     HTTP/1.1 404 Not Found
	*     {
	*       "The role with id 2 doesn't exist."
	*     }
	*
	*/
  public function updateProjectRolesAction(Request $request)
  {
  	$user = $this->checkToken($request->request->get('_token'));
  	if (!$request->request->get('projectId') && !$request->request->get('roleId'))
		return $this->setBadRequest("Missing Parameters");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $request->request->get('projectId'), "role"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();

	$role = $em->getRepository('APIBundle:Role')->find($request->request->get('roleId'));
	
	if ($role === null)
	{
		throw new NotFoundHttpException("The role with id ".$request->request->get('roleId')." doesn't exist.");
	}

	$req = $request->request->all();

	foreach ($req as $key => $value) {
		switch ($key) {
			case 'name':
				$role->setName($request->request->get('name'));
				break;
			case 'teamTimeline':
				$role->setTeamTimeline($request->request->get('teamTimeline'));
				break;
			case 'customerTimeline':
				$role->setCustomerTimeline($request->request->get('customerTimeline'));
				break;
			case 'gantt':
				$role->setGantt($request->request->get('gantt'));
				break;
			case 'whiteboard':
				$role->setWhiteboard($request->request->get('whiteboard'));
				break;
			case 'bugtracker':
				$role->setBugtracker($request->request->get('bugtracker'));
				break;
			case 'event':
				$role->setEvent($request->request->get('event'));
				break;
			case 'task':
				$role->setTask($request->request->get('task'));
				break;
			case 'projectSettings':
				$role->setProjectSettings($request->request->get('projectSettings'));
				break;
			case 'cloud':
				$role->setCloud($request->request->get('cloud'));
				break;
			default:
				break;
		}
	}

	$em->flush();

	return new JsonResponse("Update role success.");
  }

	/**
	* @api {post} /V1/API/Roles/assignPersonToRole Assign a person to a role
	* @apiName assignPersonToRole
	* @apiGroup Roles
	* @apiVersion 0.0.1
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
	*		"purId":1
	* 	}
	*
	* @apiError message Missing parameter
	* @apiError message Bad authentication token
	* @apiError message Insufficient user rights
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	*/
  public function assignPersonToRoleAction(Request $request)
  {
  	$user = $this->checkToken($request->request->get('_token'));
  	if (!$request->request->get('projectId') && !$request->request->get('roleId') && !$request->request->get('userId'))
		return $this->setBadRequest("Missing Parameters");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $request->request->get('projectId'), "role"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();
	$ProjectUserRole = new ProjectUserRole();

	$ProjectUserRole->setProjectId($request->request->get('projectId'));
	$ProjectUserRole->setUserId($request->request->get('userId'));
	$ProjectUserRole->setRoleId($request->request->get('roleId'));

	$em->persist($ProjectUserRole);
	$em->flush();

	return new JsonResponse(array("purId" => $ProjectUserRole->getId()));
  }

  /**
  * @api {put} /V1/API/Roles/updatePersonRole Update a person role
  * @apiName updatePersonRole
  * @apiGroup Roles
  * @apiVersion 0.0.1
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} roleId Id of the role
  * @apiParam {Number} purId Id of the Project user role to update
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *		"_token": "aeqf231ced651qcd",
  *		"projectId": 1,
  *		"roleId": 2,
  *		"purId": 1
  * 	}
  *
  * @apiSuccess message Update of the project user role success.
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"Update of the project user role success."
  * 	}
  *
  * @apiError message Missing parameter
  * @apiError message Bad authentication token
  * @apiError message Insufficient user rights
  * @apiErrorExample Project user role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project user role 1 doesn't exist."
  *     }
  *
  */
  public function updatePersonRoleAction(Request $request)
  {
  	$user = $this->checkToken($request->request->get('_token'));
  	if (!$request->request->get('projectId') && !$request->request->get('roleId') && !$request->request->get('purId'))
		return $this->setBadRequest("Missing Parameters");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $request->request->get('projectId'), "role"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();
	$ProjectUserRole = $em->getRepository('APIBundle:ProjectUserRole')->find($request->request->get('purId'));

	if ($ProjectUserRole === null)
	{
		throw new NotFoundHttpException("The project user role ".$request->request->get('purID')." doesn't exist.");
	}

	$ProjectUserRole->setRoleId($request->request->get('roleId'));
	$em->flush();

	return new JsonResponse("Update of the project user role success.");
  }

  /**
  * @api {delete} /V1/API/Roles/delPersonRole Delete a person role
  * @apiName delPersonRole
  * @apiGroup Roles
  * @apiVersion 0.0.1
  *
  * @apiParam {String} _token Token of the person connected
  * @apiParam {Number} projectId Id of the project
  * @apiParam {Number} purId Id of the Project user role to update
  *
  * @apiParamExample {json} Request-Example:
  * 	{
  *		"_token": "aeqf231ced651qcd",
  *		"projectId": 1,
  *		"purId": 1
  * 	}
  *
  * @apiSuccess message Update of the project user role success.
  *
  * @apiSuccessExample Success-Response:
  * 	{
  *			"Remove project user role success."
  * 	}
  *
  * @apiError message Missing parameter
  * @apiError message Bad authentication token
  * @apiError message Insufficient user rights
  * @apiErrorExample Project user role not found
  *     HTTP/1.1 404 Not Found
  *     {
  *       "The project user role with id 1 doesn't exist."
  *     }
  *
  */
  public function delPersonRoleAction(Request $request)
  {
  	$user = $this->checkToken($request->request->get('_token'));
  	if (!$request->request->get('projectId') && !$request->request->get('purId'))
		return $this->setBadRequest("Missing Parameters");
	if (!$user)
		return ($this->setBadTokenError());
	if (!$this->checkRoles($user, $request->request->get('projectId'), "role"))
		return $this->setNoRightsError();

	$em = $this->getDoctrine()->getManager();

	$role = $em->getRepository('APIBundle:ProjectUserRole')->find($request->request->get('purId'));
	
	if ($role === null)
	{
		throw new NotFoundHttpException("The project user role with id ".$request->request->get('purId')." doesn't exist.");
	}

	$em->remove($role);
	$em->flush();

	return new JsonResponse("Remove project user role success.");
  }
}
