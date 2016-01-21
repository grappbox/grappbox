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
*  @IgnoreAnnotation("apiDescription")
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
		$ret["info"] = array("return_code" => $code, "return_message" => $part." - ".$function." - "."No Data Success");
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
	* @api {post} /V0.2/roles/addprojectroles Add a project role
	* @apiName addProjectRoles
	* @apiGroup Roles
	* @apiDescription Add a project role, 0: NONE, 1: READ ONLY, 2: READ & WRITE
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
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
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"projectId": 1,
	*			"name": "Intern",
	*			"teamTimeline": 2,
	*			"customerTimeline": 0,
	*			"gantt": 0,
	*			"whiteboard": 2,
	*			"bugtracker": 1,
	*			"event": 1,
	*			"task": 1,
	*			"projectSettings": 1,
	*			"cloud": 1
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the role created
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - addprojectroles - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.1.3",
	*			"return_message": "Role - addprojectroles - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.1.6",
	*			"return_message": "Role - addprojectroles - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.1.9",
	*			"return_message": "Role - addprojectroles - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.1.4",
	*			"return_message": "Role - addprojectroles - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You can't create a role named Admin
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.2.4",
	*			"return_message": "Role - addprojectroles - Bad Parameter: You can't create a role named Admin"
	*		}
	*	}
	*/
	public function addProjectRolesAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('name', $content) || !array_key_exists('teamTimeline', $content)
			|| !array_key_exists('customerTimeline', $content) || !array_key_exists('gantt', $content) || !array_key_exists('whiteboard', $content) || !array_key_exists('bugtracker', $content)
			|| !array_key_exists('event', $content) || !array_key_exists('task', $content) || !array_key_exists('projectSettings', $content) || !array_key_exists('cloud', $content))
			return $this->setBadRequest("13.1.6", "Role", "addprojectroles", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("13.1.3", "Role", "addprojectroles"));

		if ($content->name == "Admin")
			return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: You can't create a role named Admin");

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.1.9", "Role", "addprojectroles");

		$em = $this->getDoctrine()->getManager();
		$role = new Role();

		$project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: projectId");

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

		return $this->setCreated("1.13.1", "Role", "addprojectroles", "Complete Success", array("id" => $role->getId()));
	}

	/**
	* @api {delete} /V0.2/roles/delprojectroles Delete a project role
	* @apiName delProjectRoles
	* @apiGroup Roles
	* @apiDescription Delete the given role of the project wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the role
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"id": 1
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the role deleted
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - delprojectroles - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.2.3",
	*			"return_message": "Role - delprojectroles - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.2.6",
	*			"return_message": "Role - delprojectroles - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.2.9",
	*			"return_message": "Role - delprojectroles - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.2.4",
	*			"return_message": "Role - delprojectroles - Bad Parameter: id"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You can't remove the Admin role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.2.4",
	*			"return_message": "Role - delprojectroles - Bad Parameter: You can't remove the Admin role"
	*		}
	*	}
	*/
	public function delProjectRolesAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('id', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("13.2.6", "Role", "delprojectroles", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("13.2.3", "Role", "delprojectroles"));

		$em = $this->getDoctrine()->getManager();
		$role = $em->getRepository('GrappboxBundle:Role')->find($content->id);

		if ($role === null)
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: id");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
			return $this->setNoRightsError("13.2.9", "Role", "delprojectroles");

		if ($role->getName() == "Admin")
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: You can't remove the Admin role");

		$em->remove($role);
		$em->flush();

		return $this->setSuccess("1.13.1", "Role", "delprojectroles", "Complete Success", array("id" => $content->id));
	}

	/**
	* @api {put} /V0.2/roles/putprojectroles Update a project role
	* @apiName updateProjectRoles
	* @apiGroup Roles
	* @apiDescription Update a given project role, 0: NONE, 1: READ ONLY, 2: READ & WRITE
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} roleId Id of the role
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
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"roleId": 2,
	*			"name": "Graphists",
	*			"teamTimeline": 2,
	*			"customerTimeline": 0,
	*			"gantt": 0,
	*			"whiteboard": 1,
	*			"bugtracker": 0,
	*			"event": 1,
	*			"task": 1,
	*			"projectSettings": 1,
	*			"cloud": 2
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"roleId": 2
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"roleId": 2,
	*			"teamTimeline": 1,
	*			"customerTimeline": 0,
	*			"whiteboard": 2,
	*			"event": 1,
	*			"task": 1
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the role updated
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - putprojectroles - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.3.3",
	*			"return_message": "Role - putprojectroles - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.3.6",
	*			"return_message": "Role - putprojectroles - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.3.9",
	*			"return_message": "Role - putprojectroles - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.3.4",
	*			"return_message": "Role - putprojectroles - Bad Parameter: id"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You can't update the Admin role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.3.4",
	*			"return_message": "Role - putprojectroles - Bad Parameter: You can't update the Admin role"
	*		}
	*	}
	*/
	public function updateProjectRolesAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('roleId', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("13.3.6", "Role", "putprojectroles", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("13.3.3", "Role", "putprojectroles"));

		$em = $this->getDoctrine()->getManager();
		$role = $em->getRepository('GrappboxBundle:Role')->find($content->roleId);

		if ($role === null)
			return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: roleId");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
			return $this->setNoRightsError("13.3.9", "Role", "putprojectroles");

		if ($role->getName() == "Admin")
			return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: You can't update the Admin role");

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

		return $this->setSuccess("1.13.1", "Role", "putprojectroles", "Complete Success", array("id" => $role->getId()));
	}

	/**
	* @api {get} /V0.2/roles/getprojectroles/:token/:projectId Get all project roles
	* @apiName GetProjectRoles
	* @apiGroup Roles
	* @apiDescription Get all the roles for the wanted project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} array Array of roles
	* @apiSuccess {Number} array.id Role id
	* @apiSuccess {String} array.name Role name
	* @apiSuccess {Number} array.team_timeline Team timeline role
	* @apiSuccess {Number} array.customer_timeline Customer timeline role
	* @apiSuccess {Number} array.gantt Gantt role
	* @apiSuccess {Number} array.whiteboard Whiteboard role
	* @apiSuccess {Number} array.bugtracker Bugtracker role
	* @apiSuccess {Number} array.event Event role
	* @apiSuccess {Number} array.task Task role
	* @apiSuccess {Number} array.project_settings Project settings role
	* @apiSuccess {Number} array.cloud Cloud role
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getprojectroles - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 10,
	*					"name": "Intern roles",
	*					"team_timeline": 1,
	*					"customer_timeline": 0,
	*					"gantt": 0,
	*					"whiteboard": 0,
	*					"bugtracker": 1,
	*					"event": 0,
	*					"task": 0,
	*					"project_settings": 0,
	*					"cloud": 1
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.13.3",
	*			"return_message": "Role - getprojectroles - No Data Success"
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
	*			"return_code": "13.4.3",
	*			"return_message": "Role - getprojectroles - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.4.9",
	*			"return_message": "Role - getprojectroles - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.4.4",
	*			"return_message": "Role - getprojectroles - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function getProjectRolesAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.4.3", "Role", "getprojectroles"));

		if ($this->checkRoles($user, $projectId, "projectSettings") < 1)
			return $this->setNoRightsError("13.4.9", "Role", "getprojectroles");

		$em = $this->getDoctrine()->getManager();
		$roles = $em->getRepository('GrappboxBundle:Role')->findByprojects($projectId);

		if ($roles === null)
			return $this->setBadRequest("13.4.4", "Role", "getprojectroles", "Bad Parameter: projectId");

		$arr =array();

		if (count($roles) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getprojectroles");

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

			$arr[] = array("id" => $roleId, "name" => $roleName, "team_timeline" => $teamTimeline, "customer_timeline" => $customerTimeline, "gantt" => $gantt,
				"whiteboard" => $whiteboard, "bugtracker" => $bugtracker, "event" => $event, "task" => $task, "project_settings" => $projectSettings, "cloud" => $cloud);
		}

		return $this->setSuccess("1.13.1", "Role", "getprojectroles", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {post} /V0.2/roles/assignpersontorole Assign a person to a role
	* @apiName assignPersonToRole
	* @apiGroup Roles
	* @apiDescription Assign the given user to the role wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} userId Id of the user
	* @apiParam {Number} roleId Id of the role
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"userId": 6,
	*			"roleId": 2
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the project user role created
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - assignpersontorole - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.5.3",
	*			"return_message": "Role - assignpersontorole - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.5.9",
	*			"return_message": "Role - assignpersontorole - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.5.6",
	*			"return_message": "Role - assignpersontorole - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.5.4",
	*			"return_message": "Role - assignpersontorole - Bad Parameter: userId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: roleId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.5.4",
	*			"return_message": "Role - assignpersontorole - Bad Parameter: roleId"
	*		}
	*	}
	* @apiErrorExample Already In Database
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.5.7",
	*			"return_message": "Role - assignpersontorole - Already In Database"
	*		}
	*	}
	*/
	public function assignPersonToRoleAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('roleId', $content) || !array_key_exists('userId', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("13.5.6", "Role", "assignpersontorole", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("13.5.3", "Role", "assignpersontorole"));

		$em = $this->getDoctrine()->getManager();
		$role = $em->getRepository('GrappboxBundle:Role')->find($content->roleId);
		$userToAdd = $em->getRepository('GrappboxBundle:User')->find($content->userId);

		if ($role === null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: roleId");
		if ($userToAdd === null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: userId");

		$projectId = $role->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.5.9", "Role", "assignpersontorole");

		$repository = $em->getRepository('GrappboxBundle:ProjectUserRole');
		$qb = $repository->createQueryBuilder('p')->where('p.roleId = :roleId', 'p.userId = :userId')->setParameter('roleId', $content->roleId)->setParameter('userId', $content->userId)->getQuery();
		$purs = $qb->getResult();

		if (count($purs) == 0)
		{
			$ProjectUserRole = new ProjectUserRole();
			$ProjectUserRole->setProjectId($projectId);
			$ProjectUserRole->setUserId($content->userId);
			$ProjectUserRole->setRoleId($content->roleId);

			$em->persist($ProjectUserRole);
			$em->flush();

			return $this->setCreated("1.13.1", "Role", "assignpersontorole", "Complete Success", array("id" => $ProjectUserRole->getId()));
		}
		else
			return $this->setBadRequest("13.5.7", "Role", "assignpersontorole", "Already In Database");
	}

	/**
	* @api {put} /V0.2/roles/putpersonrole Update a person role
	* @apiName updatePersonRole
	* @apiGroup Roles
	* @apiDescription Update a person role
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project for searching
	* @apiParam {Number} userId Id of the user for searching
	* @apiParam {Number} old_roleId Old id of the role for searching
	* @apiParam {Number} roleId new role id
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"projectId": 1,
	*			"userId": 1,
	*			"old_roleId": 2,
	*			"roleId": 3
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the project user role
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - putpersonrole - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.6.3",
	*			"return_message": "Role - putpersonrole - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.6.9",
	*			"return_message": "Role - putpersonrole - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Missing Parameter
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.6.6",
	*			"return_message": "Role - putpersonrole - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Bad Parameter
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.6.4",
	*			"return_message": "Role - putpersonrole - Bad Parameter"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: roleId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.6.4",
	*			"return_message": "Role - putpersonrole - Bad Parameter: roleId"
	*		}
	*	}
	*/
	public function updatePersonRoleAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('roleId', $content) || !array_key_exists('userId', $content) || !array_key_exists('token', $content)
			|| !array_key_exists('projectId', $content) || !array_key_exists('old_roleId', $content))
			return $this->setBadRequest("13.6.6", "Role", "putpersonrole", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("13.6.3", "Role", "putpersonrole"));

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.5.9", "Role", "putpersonrole");

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:ProjectUserRole');

		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
		->setParameter('projectId', $content->projectId)->setParameter('userId', $content->userId)->setParameter('roleId', $content->old_roleId)->getQuery();
		$pur = $qb->setMaxResults(1)->getOneOrNullResult();

		if ($pur === null)
			return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter");

		$role = $em->getRepository('GrappboxBundle:Role')->find($content->roleId);
		if ($role === null)
			return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter: roleId");

		$pur->setRoleId($content->roleId);

		$em->flush();

		return $this->setSuccess("1.13.1", "Role", "putpersonrole", "Complete Success", array("id" => $pur->getId()));
	}

	/**
	* @api {get} /V0.2/roles/getuserroles/:token Get the roles of the user connected
	* @apiName updatePersonRole
	* @apiGroup Roles
	* @apiDescription Get the all the roles of all the projects of the user connected
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {Object[]} array Array of user roles
	* @apiSuccess {Number} array.id Project user role id
	* @apiSuccess {Number} array.project_id Id of the project
	* @apiSuccess {Number} array.role_id Id of the role
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getuserroles - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 10,
	*					"project_id": 5,
	*					"role_id": 1
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.13.3",
	*			"return_message": "Role - getuserroles - No Data Success"
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
	*			"return_code": "13.7.3",
	*			"return_message": "Role - getuserroles - Bad ID"
	*		}
	*	}
	*/
	public function getUserRolesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.7.3", "Role", "getuserroles"));

		$em = $this->getDoctrine()->getManager();
		$userRoles = $em->getRepository('GrappboxBundle:ProjectUserRole')->findByuserId($user->getId());

		if (count($userRoles) == 0 || $userRoles === null)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserroles");

		$arr = array();

		foreach ($userRoles as $role) {
			$purId = $role->getId();
			$projectId = $role->getProjectId();
			$roleId = $role->getRoleId();

			$arr[] = array("id" => $purId, "project_id" => $projectId, "role_id" => $roleId);
		}

		return $this->setSuccess("1.13.1", "Role", "getuserroles", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {delete} /V0.2/roles/delpersonrole Delete a person role
	* @apiName delPersonRole
	* @apiGroup Roles
	* @apiDescription Delete a person role
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} userd Id of the user
	* @apiParam {Number} roleId Id of the role
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"projectId": 5,
	*			"userId": 1,
	*			"roleId": 3
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the project user role
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - delpersonrole - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.8.3",
	*			"return_message": "Role - delpersonrole - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.8.9",
	*			"return_message": "Role - delpersonrole - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Missing Parameter
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.8.6",
	*			"return_message": "Role - delpersonrole - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: roleId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.8.4",
	*			"return_message": "Role - delpersonrole - Bad Parameter: roleId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.8.4",
	*			"return_message": "Role - delpersonrole - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.8.4",
	*			"return_message": "Role - delpersonrole - Bad Parameters"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You can't remove the creator from the Admin role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.8.4",
	*			"return_message": "Role - delpersonrole - Bad Parameter: You can't remove the creator from the Admin role"
	*		}
	*	}
	*/
	public function delPersonRoleAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!$content->projectId && !$content->userId && !$content->roleId)
			return $this->setBadRequest("13.8.6", "Role", "delpersonrole", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("13.8.3", "Role", "delpersonrole"));

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.8.9", "Role", "delpersonrole");

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('GrappboxBundle:Project')->find($content->projectId);
		$role = $em->getRepository('GrappboxBundle:Role')->find($content->roleId);

		if ($project === null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: projectId");
		if ($role === null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: roleId");

		if ($project->getCreatorUser()->getId() == $content->userId && $role->getName() == "Admin")
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: You can't remove the creator from the Admin role");

		$repository = $em->getRepository('GrappboxBundle:ProjectUserRole');

		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
		->setParameter('projectId', $content->projectId)->setParameter('userId', $content->userId)->setParameter('roleId', $content->roleId)->getQuery();
		$pur = $qb->setMaxResults(1)->getOneOrNullResult();

		if ($pur == null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameters");

		$purId = $pur->getId();
		$em->remove($pur);
		$em->flush();

		return $this->setSuccess("1.13.1", "Role", "delpersonrole", "Complete Success", array("id" => $purId));
	}

	/**
	* @api {get} /V0.2/roles/getrolebyprojectanduser/:token/:projectId/:userId Get the roles id for a given user on a given project
	* @apiName getRoleByProjectAndUser
	* @apiGroup Roles
	* @apiDescription Get the roles id for a given user on a given project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam [Number] userId Id of the user
	*
	* @apiSuccess {Object[]} array Array of user roles
	* @apiSuccess {Number} array.id Id of the role
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getrolebyprojectanduser - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 10
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.13.3",
	*			"return_message": "Role - getrolebyprojectanduser - No Data Success"
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
	*			"return_code": "13.9.3",
	*			"return_message": "Role - getrolebyprojectanduser - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.9.9",
	*			"return_message": "Role - getrolebyprojectanduser - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.9.4",
	*			"return_message": "Role - getrolebyprojectanduser - Bad Parameters"
	*		}
	*	}
	*/
	public function getRoleByProjectAndUserAction(Request $request, $token, $projectId, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.9.3", "Role", "getrolebyprojectanduser"));

		if ($this->checkRoles($user, $projectId, "projectSettings") < 1)
			return $this->setNoRightsError("13.9.9", "Role", "getrolebyprojectanduser");

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:ProjectUserRole');
		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId')->setParameter('projectId', $projectId)->setParameter('userId', $userId)->getQuery();
		$purs = $qb->getResult();

		if ($purs === null)
			return $this->setBadRequest("13.9.4", "Role", "getrolebyprojectanduser", "Bad Parameters");

		if (count($purs) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getrolebyprojectanduser");

		$arr = array();

		foreach ($purs as $role) {
			$roleId = $role->getRoleId();
			$arr[] = array("id" => $roleId);
		}

		return $this->setSuccess("1.13.1", "Role", "getrolebyprojectanduser", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /V0.2/roles/getusersforrole/:token/:roleId Get the users assigned and non assigned on the role
	* @apiName getUsersForRole
	* @apiGroup Roles
	* @apiDescription Get the users assigned and non assigned on the given role with their basic informations
	* @apiVersion 0.2.0
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
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getusersforrole - Complete Success"
	*		},
	*		"data": {
	*			"id": 2,
	*			"name": "Admin",
	*			"users_assigned": [],
	*			"users-non_assigned": [
	*				{
	*					"id": 3,
	*					"firstname": "jean",
	*					"lastname": "neige"
	*				},
	*				{
	*					"id": 8,
	*					"firstname": "john",
	*					"lastname": "snow"
	*				}
	*			]
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.10.3",
	*			"return_message": "Role - getusersforrole - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "13.10.9",
	*			"return_message": "Role - getusersforrole - Insufficient Rights"
	*		}
	*	}
		* @apiErrorExample Bad Parameter: roleId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.10.4",
	*			"return_message": "Role - getusersforrole - Bad Parameter: roleId"
	*		}
	*	}
	*/
	public function getUsersForRoleAction(Request $request, $token, $roleId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.10.3", "Role", "getusersforrole"));

		$em = $this->getDoctrine()->getManager();
		$role = $em->getRepository('GrappboxBundle:Role')->find($roleId);
		if ($role === null)
			return $this->setBadRequest("13.10.4", "Role", "getusersforrole", "Bad Parameter: roleId");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 1)
			return $this->setNoRightsError("13.10.9", "Role", "getusersforrole");

		$purRepository = $em->getRepository('GrappboxBundle:ProjectUserRole');
		$qb = $purRepository->createQueryBuilder('pur')->where('pur.roleId = :id')->setParameter('id', $role->getId())->getQuery();
		$purs = $qb->getResult();

		$usersAssigned = array();
		$usersNonAssigned = array();

		$users = $role->getProjects()->getUsers();

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

		return $this->setSuccess("1.13.1", "Role", "getusersforrole", "Complete Success",
			array("id" => $role->getId(), "name" => $role->getName(), "users_assigned" => $usersAssigned, "users_non_assigned" => $usersNonAssigned));
	}

	/**
	* @api {get} /V0.2/roles/getuserrolesinformations/:token Get the roles informations of the user connected
	* @apiName getUserConnectedRolesInformations
	* @apiGroup Roles
	* @apiDescription Get the roles informations for the user connected
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {Object[]} array Array of user roles informations
	* @apiSuccess {Number} array.id Project user role id
	* @apiSuccess {Object[]} array.project Project informations
	* @apiSuccess {Number} array.project.id Id of the project
	* @apiSuccess {String} array.project.name Name of the project
	* @apiSuccess {Object[]} array.role Role informations
	* @apiSuccess {Number} array.role.id Id of the role
	* @apiSuccess {String} array.role.name Name of the role
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getuserconnectedrolesinformations - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 10,
	*					"project": {
	*						"id": 2,
	*						"name": "Grappbox"
	*					},
	*					"role": {
	*						"id": 6,
	*						"name": "Admin"
	*					}
	*				},
	*				{
	*					"id": 30,
	*					"project": {
	*						"id": 2,
	*						"name": "Grappbox"
	*					},
	*					"role": {
	*						"id": 6,
	*						"name": "Graphists"
	*					}
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.13.3",
	*			"return_message": "Role - getuserconnectedrolesinformations - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.11.3",
	*			"return_message": "Role - getuserconnectedrolesinformations - Bad ID"
	*		}
	*	}
	*/
	public function getUserConnectedRolesInfosAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.11.3", "Role", "getuserconnectedrolesinformations"));

		$em = $this->getDoctrine()->getManager();
		$userRoles = $em->getRepository('GrappboxBundle:ProjectUserRole')->findByuserId($user->getId());

		if (count($userRoles) == 0 || $userRoles === null)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserconnectedrolesinformations");

		$arr = array();

		foreach ($userRoles as $role) {
			$purId = $role->getId();

			$projectId = $role->getProjectId();
			$project = $em->getRepository('GrappboxBundle:Project')->find($projectId);

			$roleId = $role->getRoleId();
			$role = $em->getRepository('GrappboxBundle:Role')->find($roleId);

			if (($project != null && $role != null) && $this->checkRoles($user, $project->getId(), "projectSettings") > 1)
			{
				$roleName = $role->getName();
				$projectName = $project->getName();

				$arr[] = array("id" => $purId, "project" => array("id" => $projectId, "name" => $projectName), "role" => array("id" => $roleId, "name" => $roleName));
			}
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserconnectedrolesinformations");

		return $this->setSuccess("1.13.1", "Role", "getuserconnectedrolesinformations", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /V0.2/roles/getuserrolesinformations/:token/:id Get the roles informations of the given user
	* @apiName getUserRolesInformations
	* @apiGroup Roles
	* @apiDescription Get the roles informations for the given user
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} userId Id of the user you want the roles
	*
	* @apiSuccess {Object[]} array Array of user roles informations
	* @apiSuccess {Number} array.id Project user role id
	* @apiSuccess {Object[]} array.project Project informations
	* @apiSuccess {Number} array.project.id Id of the project
	* @apiSuccess {String} array.project.name Name of the project
	* @apiSuccess {Object[]} array.role Role informations
	* @apiSuccess {Number} array.role.id Id of the role
	* @apiSuccess {String} array.role.name Name of the role
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getuserrolesinformations - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 10,
	*					"project": {
	*						"id": 2,
	*						"name": "Grappbox"
	*					},
	*					"role": {
	*						"id": 6,
	*						"name": "Admin"
	*					}
	*				},
	*				{
	*					"id": 30,
	*					"project": {
	*						"id": 2,
	*						"name": "Grappbox"
	*					},
	*					"role": {
	*						"id": 6,
	*						"name": "Graphists"
	*					}
	*				}
	*			]
	*		}
	*	}
	*
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.13.3",
	*			"return_message": "Role - getuserrolesinformations - No Data Success"
	*		},
	*		"data": {
	*			"array": []
	*		}
	*	}
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.12.3",
	*			"return_message": "Role - getuserrolesinformations - Bad ID"
	*		}
	*	}
	*/
	public function getUserRolesInfosAction(Request $request, $token, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
		return ($this->setBadTokenError("13.12.3", "Role", "getuserrolesinformations"));

		$em = $this->getDoctrine()->getManager();
		$userConnectedProjects = $user->getProjects();

		$repository = $em->getRepository('GrappboxBundle:ProjectUserRole');

		$arr = array();

		foreach ($userConnectedProjects as $p) {
			if ($this->checkRoles($user, $p->getId(), "projectSettings") > 1)
			{
				$pId = $p->getId();
				$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId')->setParameter('projectId', $pId)->setParameter('userId', $userId)->getQuery();
				$userRoles = $qb->getResult();

				foreach ($userRoles as $role) {
					$purId = $role->getId();

					$projectId = $role->getProjectId();
					$project = $em->getRepository('GrappboxBundle:Project')->find($projectId);

					$roleId = $role->getRoleId();
					$role = $em->getRepository('GrappboxBundle:Role')->find($roleId);

					if ($project != null && $role != null)
					{
						$projectName = $project->getName();
						$roleName = $role->getName();

						$arr[] = array("id" => $purId, "project" => array("id" => $projectId, "name" => $projectName), "role" => array("id" => $roleId, "name" => $roleName));
					}
				}
			}
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserrolesinformations");

		return $this->setSuccess("1.13.1", "Role", "getuserrolesinformations", "Complete Success", array("array" => $arr));
	}
}
