<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use SQLBundle\Entity\Role;
use SQLBundle\Entity\ProjectUserRole;
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
*  @IgnoreAnnotation("apiHeader")
*  @IgnoreAnnotation("apiHeaderExample")
*/
class RoleController extends RolesAndTokenVerificationController
{
	/**
	* @api {post} /0.3/role Create a role
	* @apiName addProjectRoles
	* @apiGroup Roles
	* @apiDescription Create a role for a project, 0: NONE, 1: READ ONLY, 2: READ & WRITE
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
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
	* @apiSuccess {Number} projectId Id of the project
	* @apiSuccess {Number} roleId Id of the role
	* @apiSuccess {String} name Name of the role
	* @apiSuccess {Number} teamTimeline Access rights on the project's team timeline
	* @apiSuccess {Number} customerTimeline Access rights on the project's customer timeline
	* @apiSuccess {Number} gantt Access rights on the project's gantt
	* @apiSuccess {Number} whiteboard Access rights on the project's whiteboard
	* @apiSuccess {Number} bugtracker Access rights on the project's bugracker
	* @apiSuccess {Number} event Access rights on the project's meetings
	* @apiSuccess {Number} task Access rights on the project's tasks
	* @apiSuccess {Number} projectSettings Access rights on the project's settings
	* @apiSuccess {Number} cloud Access rights on the project's cloud
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
	*			"projectId": 1,
	*			"roleId": 1,
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.1.3",
	*			"return_message": "Role - addprojectroles - Bad Token"
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
	* @apiErrorExample Bad Parameter: Can't create a role named Admin
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.1.4",
	*			"return_message": "Role - addprojectroles - Bad Parameter: Can't create a role named Admin"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: Role name already register for this project
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.1.4",
	*			"return_message": "Role - addprojectroles - Bad Parameter: Role name already register for this project"
	*		}
	*	}
	*/
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

		if (!array_key_exists('projectId', $content) || !array_key_exists('name', $content) || !array_key_exists('teamTimeline', $content)
			|| !array_key_exists('customerTimeline', $content) || !array_key_exists('gantt', $content) || !array_key_exists('whiteboard', $content) || !array_key_exists('bugtracker', $content)
			|| !array_key_exists('event', $content) || !array_key_exists('task', $content) || !array_key_exists('projectSettings', $content) || !array_key_exists('cloud', $content))
			return $this->setBadRequest("13.1.6", "Role", "addprojectroles", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.1.3", "Role", "addprojectroles"));

		if ($content->name == "Admin")
			return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: Can't create a role named Admin");

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.1.9", "Role", "addprojectroles");
		$role = new Role();

		$roles = $em->getRepository("SQLBundle:Role")->findBy(array('projects'=> $project, 'name' => $content->name));
		if ($roles != null)
			return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: Role name already register for this project");

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

		//notifs
		$mdata['mtitle'] = "new role";
		$mdata['mdesc'] = json_encode($role->objectToArray());
		$wdata['type'] = "new role";
		$wdata['targetId'] = $role->getId();
		$wdata['message'] = json_encode($role->objectToArray());
		$userNotif = array();
		foreach ($role->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.13.1", "Role", "addprojectroles", "Complete Success", $role->objectToArray());
	}

	/**
	* @api {delete} /0.3/role/:id Delete role
	* @apiName delProjectRoles
	* @apiGroup Roles
	* @apiDescription Delete the given role of the project wanted
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the role
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - delprojectroles - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.2.3",
	*			"return_message": "Role - delprojectroles - Bad Token"
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
	* @apiErrorExample Bad Parameter: Can't remove the Admin role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.2.4",
	*			"return_message": "Role - delprojectroles - Bad Parameter: Can't remove the Admin role"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: Can't delete role, there is still users linked to it
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.2.4",
	*			"return_message": "Role - delprojectroles - Bad Parameter: Can't delete role, there is still users linked to it"
	*		}
	*	}
	*/
	/**
	* @api {delete} /V0.2/roles/delprojectroles/:token/:id Delete a project role
	* @apiName delProjectRoles
	* @apiGroup Roles
	* @apiDescription Delete the given role of the project wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the role
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - delprojectroles - Complete Success"
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
	public function delProjectRolesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.2.3", "Role", "delprojectroles"));

		$em = $this->getDoctrine()->getManager();
		$role = $em->getRepository('SQLBundle:Role')->find($id);

		if ($role === null)
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: id");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
			return $this->setNoRightsError("13.2.9", "Role", "delprojectroles");

		if ($role->getName() == "Admin")
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: Can't remove the Admin role");

		$users = $em->getRepository("SQLBundle:ProjectUserRole")->findBy(array('roleId'=> $id));
		if ($users != null)
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: Can't delete role, there is still users linked to it");

		//notifs
		$mdata['mtitle'] = "delete role";
		$mdata['mdesc'] = json_encode($role->objectToArray());
		$wdata['type'] = "delete role";
		$wdata['targetId'] = $role->getId();
		$wdata['message'] = json_encode($role->objectToArray());
		$userNotif = array();
		foreach ($role->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($role);
		$em->flush();

		$response["info"]["return_code"] = "1.13.1";
		$response["info"]["return_message"] = "Role - delprojectroles - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {put} /0.3/role/:id Update role
	* @apiName updateProjectRoles
	* @apiGroup Roles
	* @apiDescription Update role caracteristics, 0: NONE, 1: READ ONLY, 2: READ & WRITE
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the role
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
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"teamTimeline": 1,
	*			"customerTimeline": 0,
	*			"whiteboard": 2,
	*			"event": 1,
	*			"task": 1
	*		}
	*	}
	*
	* @apiSuccess {Number} projectId Id of the project
	* @apiSuccess {Number} roleId Id of the role
	* @apiSuccess {String} name Name of the role
	* @apiSuccess {Number} teamTimeline Access rights on the project's team timeline
	* @apiSuccess {Number} customerTimeline Access rights on the project's customer timeline
	* @apiSuccess {Number} gantt Access rights on the project's gantt
	* @apiSuccess {Number} whiteboard Access rights on the project's whiteboard
	* @apiSuccess {Number} bugtracker Access rights on the project's bugracker
	* @apiSuccess {Number} event Access rights on the project's meetings
	* @apiSuccess {Number} task Access rights on the project's tasks
	* @apiSuccess {Number} projectSettings Access rights on the project's settings
	* @apiSuccess {Number} cloud Access rights on the project's cloud
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
	*			"projectId": 2,
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
	* @apiErrorExample Bad Parameter: Can't update the Admin role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.3.4",
	*			"return_message": "Role - putprojectroles - Bad Parameter: Can't update the Admin role"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: Role name already register for this project
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.3.4",
	*			"return_message": "Role - putprojectroles - Bad Parameter: Role name already register for this project"
	*		}
	*	}
	*/
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
	public function updateProjectRolesAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.3.3", "Role", "putprojectroles"));

		$em = $this->getDoctrine()->getManager();
		$role = $em->getRepository('SQLBundle:Role')->find($id);

		if ($role === null)
			return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: id");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
			return $this->setNoRightsError("13.3.9", "Role", "putprojectroles");

		if ($role->getName() == "Admin")
			return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: Can't update the Admin role");

		$roles = $em->getRepository("SQLBundle:Role")->findBy(array('projects'=> $role->getProjects(), 'name' => $content->name));
		if ($roles != null) {
			$isSame = false;
			foreach ($roles as $r) {
				if ($r->getId() == $id)
					$isSame = true;
			}
			if ($isSame == false)
				return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: Role name already register for this project");
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

		//notifs
		$mdata['mtitle'] = "update role";
		$mdata['mdesc'] = json_encode($role->objectToArray());
		$wdata['type'] = "update role";
		$wdata['targetId'] = $role->getId();
		$wdata['message'] = json_encode($role->objectToArray());
		$userNotif = array();
		foreach ($role->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.13.1", "Role", "putprojectroles", "Complete Success", $role->objectToArray());
	}

	/**
	* @api {get} /0.3/roles/:projectId Get roles by project
	* @apiName GetProjectRoles
	* @apiGroup Roles
	* @apiDescription Get all the roles of the given project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} array Array of roles
	* @apiSuccess {Number} array.projectId project id
	* @apiSuccess {Number} array.roleId Role id
	* @apiSuccess {String} array.name Role name
	* @apiSuccess {Number} array.teamTimeline Team timeline role
	* @apiSuccess {Number} array.customerTimeline Customer timeline role
	* @apiSuccess {Number} array.gantt Gantt role
	* @apiSuccess {Number} array.whiteboard Whiteboard role
	* @apiSuccess {Number} array.bugtracker Bugtracker role
	* @apiSuccess {Number} array.event Event role
	* @apiSuccess {Number} array.task Task role
	* @apiSuccess {Number} array.projectSettings Project settings role
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
	*					"projectId": 1,
	*					"roleId": 10,
	*					"name": "Intern roles",
	*					"teamTimeline": 1,
	*					"customerTimeline": 0,
	*					"gantt": 0,
	*					"whiteboard": 0,
	*					"bugtracker": 1,
	*					"event": 0,
	*					"task": 0,
	*					"projectSettings": 0,
	*					"cloud": 1
	*				},
	*				...
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.4.3",
	*			"return_message": "Role - getprojectroles - Bad Token"
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
	* @apiSuccess {Number} array.teamTimeline Team timeline role
	* @apiSuccess {Number} array.customerTimeline Customer timeline role
	* @apiSuccess {Number} array.gantt Gantt role
	* @apiSuccess {Number} array.whiteboard Whiteboard role
	* @apiSuccess {Number} array.bugtracker Bugtracker role
	* @apiSuccess {Number} array.event Event role
	* @apiSuccess {Number} array.task Task role
	* @apiSuccess {Number} array.projectSettings Project settings role
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
	*					"teamTimeline": 1,
	*					"customerTimeline": 0,
	*					"gantt": 0,
	*					"whiteboard": 0,
	*					"bugtracker": 1,
	*					"event": 0,
	*					"task": 0,
	*					"projectSettings": 0,
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
	public function getProjectRolesAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.4.3", "Role", "getprojectroles"));

		if ($this->checkRoles($user, $projectId, "projectSettings") < 1)
			return $this->setNoRightsError("13.4.9", "Role", "getprojectroles");

		$em = $this->getDoctrine()->getManager();
		$roles = $em->getRepository('SQLBundle:Role')->findByprojects($projectId);

		if ($roles === null)
			return $this->setBadRequest("13.4.4", "Role", "getprojectroles", "Bad Parameter: projectId");

		if (count($roles) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getprojectroles");

		$arr = array();
		foreach ($roles as $role) {
			$arr[] = $role->objectToArray();
		}

		return $this->setSuccess("1.13.1", "Role", "getprojectroles", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {post} /0.3/role/user Assign user to role
	* @apiName assignPersonToRole
	* @apiGroup Roles
	* @apiDescription Assign the given user to the role for the related project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} userId Id of the user
	* @apiParam {Number} roleId Id of the role
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"userId": 6,
	*			"roleId": 2
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the project_user_role created
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.5.3",
	*			"return_message": "Role - assignpersontorole - Bad Token"
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
	* @apiErrorExample Bad Parameter: User already have a role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.5.4",
	*			"return_message": "Role - assignpersontorole - Bad Parameter: User already have a role"
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
	* @apiErrorExample Bad Parameter: You can't add the creator to another role than Admin role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.5.7",
	*			"return_message": "Role - assignpersontorole - Bad Parameter: You can't add the creator to another role than Admin role"
	*		}
	*	}
	*/
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

		if (!array_key_exists('roleId', $content) || !array_key_exists('userId', $content))
			return $this->setBadRequest("13.5.6", "Role", "assignpersontorole", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.5.3", "Role", "assignpersontorole"));

		$em = $this->getDoctrine()->getManager();
		$role = $em->getRepository('SQLBundle:Role')->find($content->roleId);
		$userToAdd = $em->getRepository('SQLBundle:User')->find($content->userId);

		if ($role === null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: roleId");
		if ($role->getProjects()->getCreatorUser()->getId() == $userId && $role->getName() != "Admin")
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: You can't add the creator to another role than Admin role");
		if ($userToAdd === null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: userId");

		$projectId = $role->getProjects()->getId();
		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.5.9", "Role", "assignpersontorole");

		$projectUsers = $role->getProjects()->getUsers();
		$isInProject = false;
		foreach ($projectUsers as $u) {
			if ($u === $userToAdd)
				$isInProject = true;
		}
		if (!$isInProject)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: userId");

		$pur = $em->getRepository('SQLBundle:ProjectUserRole')->findBy(array('projectId'=> $projectId, 'userId'=> $content->userId));
		if($pur != null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: User already have a role");


		$repository = $em->getRepository('SQLBundle:ProjectUserRole');
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

			//notifs
			$mdata['mtitle'] = "assign user role";
			$mdata['mdesc'] = json_encode(array("user_id" => $content->userId, "role_id" => $content->roleId));
			$wdata['type'] = "assign user role";
			$wdata['targetId'] = $role->getId();
			$wdata['message'] = json_encode(array("user_id" => $content->userId, "role_id" => $content->roleId));
			$userNotif = array();
			foreach ($role->getProjects()->getUsers() as $key => $value) {
				$userNotif[] = $value->getId();
			}
			if (count($userNotif) > 0)
				$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

			return $this->setCreated("1.13.1", "Role", "assignpersontorole", "Complete Success", array("id" => $ProjectUserRole->getId()));
		}
		else
			return $this->setBadRequest("13.5.7", "Role", "assignpersontorole", "Already In Database");
	}

	/**
	* @api {put} /0.3/role/user/:userId Change user role
	* @apiName updatePersonRole
	* @apiGroup Roles
	* @apiDescription Change the role of a user ofr the realted project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} userId Id of the user for searching
	* @apiParam {Number} projectId Id of the project for searching
	* @apiParam {Number} old_roleId Old id of the role for searching
	* @apiParam {Number} roleId new role id
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"projectId": 1,
	*			"old_roleId": 2,
	*			"roleId": 3
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the project_user_role
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.6.3",
	*			"return_message": "Role - putpersonrole - Bad Token"
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
	* @apiErrorExample Bad Parameter: You can't remove the creator from the Admin role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.6.4",
	*			"return_message": "Role - putpersonrole - Bad Parameter: You can't remove the creator from the Admin role"
	*		}
	*	}
	*/
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
	public function updatePersonRoleAction(Request $request, $userId)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('roleId', $content) || !array_key_exists('projectId', $content) || !array_key_exists('old_roleId', $content))
			return $this->setBadRequest("13.6.6", "Role", "putpersonrole", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.6.3", "Role", "putpersonrole"));

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.5.9", "Role", "putpersonrole");

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:ProjectUserRole');

		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
		->setParameter('projectId', $content->projectId)->setParameter('userId', $userId)->setParameter('roleId', $content->old_roleId)->getQuery();
		$pur = $qb->setMaxResults(1)->getOneOrNullResult();

		if ($pur === null)
			return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter");

		$role = $em->getRepository('SQLBundle:Role')->find($content->roleId);
		if ($role === null)
			return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter: roleId");
		
		if ($role->getProjects()->getCreatorUser()->getId() == $userId && $role->getName() == "Admin")
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: You can't remove the creator from the Admin role");

		$pur->setRoleId($content->roleId);
		$em->flush();

		//notifs
		$mdata['mtitle'] = "update user role";
		$mdata['mdesc'] = json_encode(array("user_id" => $userId, "role_id" => $content->roleId));
		$wdata['type'] = "update user role";
		$wdata['targetId'] = $role->getId();
		$wdata['message'] = json_encode(array("user_id" => $userId, "role_id" => $content->roleId));
		$userNotif = array();
		foreach ($role->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.13.1", "Role", "putpersonrole", "Complete Success", array("id" => $pur->getId()));
	}

	/**
	* @api {get} /0.3/roles/user Get connected user roles
	* @apiName getuserroles
	* @apiGroup Roles
	* @apiDescription Get the all roles linked to the connected user
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiSuccess {Object[]} array Array of user roles
	* @apiSuccess {Number} array.projectId project id
	* @apiSuccess {Number} array.roleId Role id
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
	*			"return_message": "Role - getuserroles - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"projectId": 1,
	*					"roleId": 10,
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
	*				},
	*				...
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.7.3",
	*			"return_message": "Role - getuserroles - Bad Token"
	*		}
	*	}
	*/
	/**
	* @api {get} /V0.2/roles/getuserroles/:token Get the roles of the user connected
	* @apiName getuserroles
	* @apiGroup Roles
	* @apiDescription Get the all the roles of all the projects of the user connected
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	*
	* @apiSuccess {Object[]} array Array of user roles
	* @apiSuccess {Number} array.id Project user role id
	* @apiSuccess {Number} array.project_id Id of the project
	* @apiSuccess {Number} array.role_id Id of the rol
	* @apiSuccess {String} array.name Role name
	* @apiSuccess {Number} array.teamTimeline Team timeline role
	* @apiSuccess {Number} array.customerTimeline Customer timeline role
	* @apiSuccess {Number} array.gantt Gantt role
	* @apiSuccess {Number} array.whiteboard Whiteboard role
	* @apiSuccess {Number} array.bugtracker Bugtracker role
	* @apiSuccess {Number} array.event Event role
	* @apiSuccess {Number} array.task Task role
	* @apiSuccess {Number} array.projectSettings Project settings role
	* @apiSuccess {Number} array.cloud Cloud role
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
	*					"projectId": 1,
	*					"roleId": 10,
	*					"name": "Intern roles",
	*					"teamTimeline": 1,
	*					"customerTimeline": 0,
	*					"gantt": 0,
	*					"whiteboard": 0,
	*					"bugtracker": 1,
	*					"event": 0,
	*					"task": 0,
	*					"projectSettings": 0,
	*					"cloud": 1
	*				},
	*				...
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
	public function getUserRolesAction(Request $request)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.7.3", "Role", "getuserroles"));

		$em = $this->getDoctrine()->getManager();
		$userRoles = $em->getRepository('SQLBundle:ProjectUserRole')->findByuserId($user->getId());

		if (count($userRoles) == 0 || $userRoles === null)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserroles");

		$arr = array();

		foreach ($userRoles as $pur) {
			$role = $em->getRepository('SQLBundle:Role')->find($pur->getRoleId());
			$arr[] = $role->objectToArray();
		}

		return $this->setSuccess("1.13.1", "Role", "getuserroles", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {delete} /0.3/role/user/:projectId/:userId/:roleId Unassign user to role
	* @apiName delPersonRole
	* @apiGroup Roles
	* @apiDescription Unlink given user and role
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} userd Id of the user
	* @apiParam {Number} roleId Id of the role
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - delpersonrole - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.8.3",
	*			"return_message": "Role - delpersonrole - Bad Token"
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
	/**
	* @api {delete} /V0.2/roles/delpersonrole/:token/:projectId/:userId/:roleId Delete a person role
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
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - delpersonrole - Complete Success"
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
	public function delPersonRoleAction(Request $request, $projectId, $userId, $roleId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.8.3", "Role", "delpersonrole"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.8.9", "Role", "delpersonrole");
		$role = $em->getRepository('SQLBundle:Role')->find($roleId);

		if ($role === null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: roleId");

		if ($project->getCreatorUser()->getId() == $userId && $role->getName() == "Admin")
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: You can't remove the creator from the Admin role");

		$repository = $em->getRepository('SQLBundle:ProjectUserRole');

		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
		->setParameter('projectId', $projectId)->setParameter('userId', $userId)->setParameter('roleId', $roleId)->getQuery();
		$pur = $qb->setMaxResults(1)->getOneOrNullResult();

		if ($pur == null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameters");

		//notifs
		$mdata['mtitle'] = "delete user role";
		$mdata['mdesc'] = json_encode(array("user_id" => $content->userId, "role_id" => $content->roleId));
		$wdata['type'] = "delete user role";
		$wdata['targetId'] = $role->getId();
		$wdata['message'] = json_encode(array("user_id" => $content->userId, "role_id" => $content->roleId));
		$userNotif = array();
		foreach ($role->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$purId = $pur->getId();
		$em->remove($pur);
		$em->flush();

		$response["info"]["return_code"] = "1.13.1";
		$response["info"]["return_message"] = "Role - delpersonrole - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /0.3/roles/project/user/:projectId/[:userId] Get user role by project
	* @apiName getRoleByProjectAndUser
	* @apiGroup Roles
	* @apiDescription Get user role for a given project, if userId not specified assumed reference user is the connected user
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} [userId] Id of the user
	*
	* @apiSuccess {Number} projectId Id of the project
	* @apiSuccess {Number} roleId Id of the role
	* @apiSuccess {String} name Name of the role
	* @apiSuccess {Number} teamTimeline Access rights on the project's team timeline
	* @apiSuccess {Number} customerTimeline Access rights on the project's customer timeline
	* @apiSuccess {Number} gantt Access rights on the project's gantt
	* @apiSuccess {Number} whiteboard Access rights on the project's whiteboard
	* @apiSuccess {Number} bugtracker Access rights on the project's bugracker
	* @apiSuccess {Number} event Access rights on the project's meetings
	* @apiSuccess {Number} task Access rights on the project's tasks
	* @apiSuccess {Number} projectSettings Access rights on the project's settings
	* @apiSuccess {Number} cloud Access rights on the project's cloud
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getrolebyprojectanduser - Complete Success"
	*		},
	*		"data":
	*		{
	*			"projectId": 1,
	*			"roleId": 1,
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.9.3",
	*			"return_message": "Role - getrolebyprojectanduser - Bad Token"
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
	/**
	* @api {get} /V0.2/roles/getrolebyprojectanduser/:token/:projectId/[:userId] Get the roles id for a given user on a given project
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
	public function getRoleByProjectAndUserAction(Request $request, $projectId, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.9.3", "Role", "getrolebyprojectanduser"));

		if ($this->checkRoles($user, $projectId, "projectSettings") < 1)
			return $this->setNoRightsError("13.9.9", "Role", "getrolebyprojectanduser");

		if ($userId == 0)
			$userId = $user->getId();

		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:ProjectUserRole');
		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId')->setParameter('projectId', $projectId)->setParameter('userId', $userId)->getQuery();
		$purs = $qb->getResult();

		if ($purs === null)
			return $this->setBadRequest("13.9.4", "Role", "getrolebyprojectanduser", "Bad Parameters");

		if (count($purs) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getrolebyprojectanduser");

		$role = $em->getRepository('SQLBundle:Role')->find($purs[0]->getRoleId());

		return $this->setSuccess("1.13.1", "Role", "getrolebyprojectanduser", "Complete Success", $role->objectToArray());
	}

	/**
	* @api {get} /0.3/role/users/:roleId Get (un)assigned users by role
	* @apiName getUsersForRole
	* @apiGroup Roles
	* @apiDescription Get the users assigned and non assigned on the given role with their basic informations
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.10.3",
	*			"return_message": "Role - getusersforrole - Bad Token"
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
	public function getUsersForRoleAction(Request $request, $roleId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.10.3", "Role", "getusersforrole"));

		$em = $this->getDoctrine()->getManager();
		$role = $em->getRepository('SQLBundle:Role')->find($roleId);
		if ($role === null)
			return $this->setBadRequest("13.10.4", "Role", "getusersforrole", "Bad Parameter: roleId");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 1)
			return $this->setNoRightsError("13.10.9", "Role", "getusersforrole");

		$purRepository = $em->getRepository('SQLBundle:ProjectUserRole');
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
	* @api {get} /0.3/role/user/part/:userId/:projectId/:part Get user's rights by named part
	* @apiName getUserRoleForPArt
	* @apiGroup Roles
	* @apiDescription Get user's rights (0: none, 1: readonly, 2:read& write) for a specific part (timeline, bugtracker, ...)
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} userId Id of the user you want the roles
	* @apiParam {Number} projectId Id of the projectId concerned
	* @apiParam {string} part name of the part ['team_timeline', 'customer_timeline', 'gantt', 'whiteboard', 'bugtracker', 'event', 'task', 'project_settings', 'cloud']
	*
	* @apiSuccess {int} userId user id
	* @apiSuccess {int} name part name
	* @apiSuccess {int} value part rights
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getUserRoleForPart - Complete Success"
	*		},
	*		"data": {
	*					"userId": 10,
	*					"name": "bugtracker",
	*					"value": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.11.3",
	*			"return_message": "Role - getUserRoleForPart - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId or projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.11.4",
	*			"return_message": "Role - getUserRoleForPart - Bad Parameter: userId or projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: part
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.11.4",
	*			"return_message": "Role - getUserRoleForPart - Bad Parameter: part"
	*		}
	*	}
	*/
	/**
	* @api {get} /V0.2/roles/getuserroleforpart/:token/:userId/:projectId/:part Get user's rights for a specific part
	* @apiName getUserRoleForPArt
	* @apiGroup Roles
	* @apiDescription Get user's rights (0: none, 1: readonly, 2:read& write) for a specific part (timeline, bugtracker, ...)
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} userId Id of the user you want the roles
	* @apiParam {Number} projectId Id of the projectId concerned
	* @apiParam {string} part name of the part ['team_timeline', 'customer_timeline', 'gantt', 'whiteboard', 'bugtracker', 'event', 'task', 'project_settings', 'cloud']
	*
	* @apiSuccess {int} userId user id
	* @apiSuccess {int} name part name
	* @apiSuccess {int} value part rights
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.13.1",
	*			"return_message": "Role - getUserRoleForPart - Complete Success"
	*		},
	*		"data": {
	*					"userId": 10,
	*					"name": "bugtracker",
	*					"value": 1
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "13.13.3",
	*			"return_message": "Role - getUserRoleForPart - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId or projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.13.4",
	*			"return_message": "Role - getUserRoleForPart - Bad Parameter: userId or projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: part
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "13.13.4",
	*			"return_message": "Role - getUserRoleForPart - Bad Parameter: part"
	*		}
	*	}
	*/
	public function getUserRoleForPartAction(Request $request, $userId, $projectId, $part)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.11.3", "Role", "getUserRoleForPart"));

		$em = $this->getDoctrine()->getManager();
		$roleId = $em->getRepository('SQLBundle:ProjectUserRole')->findOneBy(array("userId" => $userId, "projectId" => $projectId));
		if (!($roleId instanceof ProjectUserRole))
			return $this->setBadRequest("13.11.4", "Role", "getUserRoleForPart", "Bad Parameter: userId or projectId");

		$role = $em->getRepository('SQLBundle:Role')->find($roleId->getRoleId());

		if ($role->getPart($part) == -1)
			return $this->setBadRequest("13.11.4", "Role", "getUserRoleForPart", "Bad Parameter: part");

		return $this->setSuccess("1.13.1", "Role", "getUserRoleForPart", "Complete Success", array("user_id" => $userId, "name" => $part, "value" => $role->getPart($part)));

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
	* @apiSuccess {object} array.role.values values of each section of the role
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
	*						"name": "Admin",
	*						"values": {
	*							"teamTimeline": 2,
	*							"customerTimeline": 2,
	*							"gantt": 2,
	*							"whiteboard": 2,
	*							"bugtracker": 2,
	*							"event": 2,
	*							"task": 2,
	*							"projectSettings": 2,
	*							"cloud": 2
	*							}
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
	*						"name": "Graphists",
	*						"values": {
	*							"teamTimeline": 2,
	*							"customerTimeline": 2,
	*							"gantt": 1,
	*							"whiteboard": 2,
	*							"bugtracker": 1,
	*							"event": 1,
	*							"task": 1,
	*							"projectSettings": 0,
	*							"cloud": 2
	*							}
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
	/**
	* @api {get} - Get roles info of connected user
	* @apiName getUserConnectedRolesInformations
	* @apiGroup Roles
	* @apiDescription This request no longer exists. See [getUserRoles](0.3/#api-Roles-getuserroles) or [getUserRoleByProject](0.3/#api-Roles-getRoleByProjectAndUser)
	* @apiVersion 0.3.0
	*
	*/


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
	* @apiSuccess {object} array.role.values values of each section of the role
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
	*						"name": "Admin",
	*						"values": {
	*							"teamTimeline": 2,
	*							"customerTimeline": 2,
	*							"gantt": 2,
	*							"whiteboard": 2,
	*							"bugtracker": 2,
	*							"event": 2,
	*							"task": 2,
	*							"projectSettings": 2,
	*							"cloud": 2
	*							}
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
	*						"name": "Graphists",
	*						"values": {
	*							"teamTimeline": 2,
	*							"customerTimeline": 2,
	*							"gantt": 2,
	*							"whiteboard": 2,
	*							"bugtracker": 2,
	*							"event": 2,
	*							"task": 2,
	*							"projectSettings": 2,
	*							"cloud": 2
	*							}
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
	/**
	* @api {get} - Get roles info of given user
	* @apiName getUserRolesInformations
	* @apiGroup Roles
	* @apiDescription This request no longer exists. See [getUserRoleByProject](0.3/#api-Roles-getRoleByProjectAndUser)
	* @apiVersion 0.3.0
	*/

}
