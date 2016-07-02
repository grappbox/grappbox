<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use MongoBundle\Document\Role;
use MongoBundle\Document\ProjectUserRole;
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
		$em = $this->get('doctrine_mongodb')->getManager();
		$user = $em->getRepository('MongoBundle:User')->findOneBy(array('token' => $token));

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

			$em = $this->get('doctrine_mongodb')->getManager();
			$em->persist($user);
			$em->flush();
		}

		return $user;
	}

	// return 0 if user has no rigths on this role
	// return 1 if user has rights
	protected function checkRoles($user, $projectId, $role)
	{
		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Role');

		$qb = $em->getRepository("MongoBundle:ProjectUserRole")->findOneBy(array("userId" => $user->getId(), "projectId" => $projectId));

		$result = $em->getRepository("MongoBundle:Role")->find($qb->getRoleId());

		$res = $result->objectToArray();
		return $res[$role];
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
	* @api {post} /mongo/roles/addprojectroles Add a project role
	* @apiName addProjectRoles
	* @apiGroup Roles
	* @apiDescription Add a project role, 0: NONE, 1: READ ONLY, 2: READ & WRITE
	* @apiVersion 0.2.0
	*
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

		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return $this->setNoRightsError("13.1.9", "Role", "addprojectroles");

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = new Role();

		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
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
	* @api {delete} /mongo/roles/delprojectroles/:token/:id Delete a project role
	* @apiName delProjectRoles
	* @apiGroup Roles
	* @apiDescription Delete the given role of the project wanted
	* @apiVersion 0.2.0
	*
	*/
	public function delProjectRolesAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("13.2.3", "Role", "delprojectroles"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = $em->getRepository('MongoBundle:Role')->find($content->id);

		if ($role === null)
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: id");

		if (!$this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
			return $this->setNoRightsError("13.2.9", "Role", "delprojectroles");

		if ($role->getName() == "Admin")
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: You can't remove the Admin role");

		$em->remove($role);
		$em->flush();

		$response["info"]["return_code"] = "1.13.1";
		$response["info"]["return_message"] = "Role - delprojectroles - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {put} /mongo/roles/putprojectroles Update a project role
	* @apiName updateProjectRoles
	* @apiGroup Roles
	* @apiDescription Update a given project role, 0: NONE, 1: READ ONLY, 2: READ & WRITE
	* @apiVersion 0.2.0
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);

		if ($role === null)
			return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: roleId");

		if (!$this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
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
	* @api {get} /mongo/roles/getprojectroles/:token/:projectId Get all project roles
	* @apiName GetProjectRoles
	* @apiGroup Roles
	* @apiDescription Get all the roles for the wanted project
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectRolesAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.4.3", "Role", "getprojectroles"));

		if (!$this->checkRoles($user, $projectId, "projectSettings") < 1)
			return $this->setNoRightsError("13.4.9", "Role", "getprojectroles");

		$em = $this->get('doctrine_mongodb')->getManager();
		$roles = $em->getRepository('MongoBundle:Role')->findByprojects($projectId);

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
	* @api {post} /mongo/roles/assignpersontorole Assign a person to a role
	* @apiName assignPersonToRole
	* @apiGroup Roles
	* @apiDescription Assign the given user to the role wanted
	* @apiVersion 0.2.0
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);
		$userToAdd = $em->getRepository('MongoBundle:User')->find($content->userId);

		if ($role === null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: roleId");
		if ($userToAdd === null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: userId");

		$projectId = $role->getProjects()->getId();
		if (!$this->checkRoles($user, $projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.5.9", "Role", "assignpersontorole");

		$repository = $em->getRepository('MongoBundle:ProjectUserRole');
		$qb = $repository->createQueryBuilder('p')->where('p.roleId = :roleId', 'p.userId = :userId')->setParameter('roleId', $content->roleId)->setParameter('userId', $content->userId)->getQuery();
		$purs = $qb->execute();

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
	* @api {put} /mongo/roles/putpersonrole Update a person role
	* @apiName updatePersonRole
	* @apiGroup Roles
	* @apiDescription Update a person role
	* @apiVersion 0.2.0
	*
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

		if (!$this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.5.9", "Role", "putpersonrole");

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:ProjectUserRole');

		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
		->setParameter('projectId', $content->projectId)->setParameter('userId', $content->userId)->setParameter('roleId', $content->old_roleId)->getQuery();
		$pur = $qb->getSingleResult();

		if ($pur === null)
			return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter");

		$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);
		if ($role === null)
			return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter: roleId");

		$pur->setRoleId($content->roleId);

		$em->flush();

		return $this->setSuccess("1.13.1", "Role", "putpersonrole", "Complete Success", array("id" => $pur->getId()));
	}

	/**
	* @api {get} /mongo/roles/getuserroles/:token Get the roles of the user connected
	* @apiName updatePersonRole
	* @apiGroup Roles
	* @apiDescription Get the all the roles of all the projects of the user connected
	* @apiVersion 0.2.0
	*
	*/
	public function getUserRolesAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.7.3", "Role", "getuserroles"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$userRoles = $em->getRepository('MongoBundle:ProjectUserRole')->findByuserId($user->getId());

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
	* @api {delete} /mongo/roles/delpersonrole/:token/:projectId/:userId/:roleId Delete a person role
	* @apiName delPersonRole
	* @apiGroup Roles
	* @apiDescription Delete a person role
	* @apiVersion 0.2.0
	*
	*/
	public function delPersonRoleAction(Request $request, $token, $projectId, $userId, $roleId)
	{
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("13.8.3", "Role", "delpersonrole"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.8.9", "Role", "delpersonrole");

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
		$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);

		if ($project === null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: projectId");
		if ($role === null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: roleId");

		if ($project->getCreatorUser()->getId() == $content->userId && $role->getName() == "Admin")
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: You can't remove the creator from the Admin role");

		$repository = $em->getRepository('MongoBundle:ProjectUserRole');

		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
		->setParameter('projectId', $content->projectId)->setParameter('userId', $content->userId)->setParameter('roleId', $content->roleId)->getQuery();
		$pur = $qb->getSingleResult();

		if ($pur == null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameters");

		$purId = $pur->getId();
		$em->remove($pur);
		$em->flush();

		$response["info"]["return_code"] = "1.13.1";
		$response["info"]["return_message"] = "Role - delpersonrole - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /mongo/roles/getrolebyprojectanduser/:token/:projectId/:userId Get the roles id for a given user on a given project
	* @apiName getRoleByProjectAndUser
	* @apiGroup Roles
	* @apiDescription Get the roles id for a given user on a given project
	* @apiVersion 0.2.0
	*
	*/
	public function getRoleByProjectAndUserAction(Request $request, $token, $projectId, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.9.3", "Role", "getrolebyprojectanduser"));

		if (!$this->checkRoles($user, $projectId, "projectSettings") < 1)
			return $this->setNoRightsError("13.9.9", "Role", "getrolebyprojectanduser");

		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:ProjectUserRole');
		$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId')->setParameter('projectId', $projectId)->setParameter('userId', $userId)->getQuery();
		$purs = $qb->execute();

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
	* @api {get} /mongo/roles/getusersforrole/:token/:roleId Get the users assigned and non assigned on the role
	* @apiName getUsersForRole
	* @apiGroup Roles
	* @apiDescription Get the users assigned and non assigned on the given role with their basic informations
	* @apiVersion 0.2.0
	*
	*/
	public function getUsersForRoleAction(Request $request, $token, $roleId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.10.3", "Role", "getusersforrole"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = $em->getRepository('MongoBundle:Role')->find($roleId);
		if ($role === null)
			return $this->setBadRequest("13.10.4", "Role", "getusersforrole", "Bad Parameter: roleId");

		if (!$this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 1)
			return $this->setNoRightsError("13.10.9", "Role", "getusersforrole");

		$purRepository = $em->getRepository('MongoBundle:ProjectUserRole');
		$qb = $purRepository->createQueryBuilder('pur')->where('pur.roleId = :id')->setParameter('id', $role->getId())->getQuery();
		$purs = $qb->execute();

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
	* @api {get} /mongo/roles/getuserrolesinformations/:token Get the roles informations of the user connected
	* @apiName getUserConnectedRolesInformations
	* @apiGroup Roles
	* @apiDescription Get the roles informations for the user connected
	* @apiVersion 0.2.0
	*
	*/
	public function getUserConnectedRolesInfosAction(Request $request, $token)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.11.3", "Role", "getuserconnectedrolesinformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$userRoles = $em->getRepository('MongoBundle:ProjectUserRole')->findByuserId($user->getId());

		if (count($userRoles) == 0 || $userRoles === null)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserconnectedrolesinformations");

		$arr = array();

		foreach ($userRoles as $role) {
			$purId = $role->getId();

			$projectId = $role->getProjectId();
			$project = $em->getRepository('MongoBundle:Project')->find($projectId);

			$roleId = $role->getRoleId();
			$role = $em->getRepository('MongoBundle:Role')->find($roleId);

			if (($project != null && $role != null) && $this->checkRoles($user, $project->getId(), "projectSettings") < 1)
			{
				$roleName = $role->getName();
				$roleValues = array("teamTimeline" => $role->getTeamTimeline(),
        										"customerTimeline" => $role->getCustomerTimeline(),
										        "gantt" => $role->getGantt(),
										        "whiteboard" => $role->getWhiteboard(),
										        "bugtracker" => $role->getBugtracker(),
										        "event" => $role->getEvent(),
										        "task" => $role->getTask(),
										        "projectSettings" => $role->getProjectSettings(),
										        "cloud" => $role->getCloud());
				$projectName = $project->getName();

				$arr[] = array("id" => $purId, "project" => array("id" => $projectId, "name" => $projectName), "role" => array("id" => $roleId, "name" => $roleName, "values" => $roleValues));
			}
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserconnectedrolesinformations");

		return $this->setSuccess("1.13.1", "Role", "getuserconnectedrolesinformations", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /mongo/roles/getuserrolesinformations/:token/:id Get the roles informations of the given user
	* @apiName getUserRolesInformations
	* @apiGroup Roles
	* @apiDescription Get the roles informations for the given user
	* @apiVersion 0.2.0
	*
	*/
	public function getUserRolesInfosAction(Request $request, $token, $userId)
	{
		$user = $this->checkToken($token);
		if (!$user)
		return ($this->setBadTokenError("13.12.3", "Role", "getuserrolesinformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$userConnectedProjects = $user->getProjects();

		$repository = $em->getRepository('MongoBundle:ProjectUserRole');

		$arr = array();

		foreach ($userConnectedProjects as $p) {
			if ($this->checkRoles($user, $p->getId(), "projectSettings") < 1)
			{
				$pId = $p->getId();
				$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId')->setParameter('projectId', $pId)->setParameter('userId', $userId)->getQuery();
				$userRoles = $qb->execute();

				foreach ($userRoles as $role) {
					$purId = $role->getId();

					$projectId = $role->getProjectId();
					$project = $em->getRepository('MongoBundle:Project')->find($projectId);

					$roleId = $role->getRoleId();
					$role = $em->getRepository('MongoBundle:Role')->find($roleId);

					if ($project != null && $role != null)
					{
						$projectName = $project->getName();
						$roleName = $role->getName();
						$roleValues = array("teamTimeline" => $role->getTeamTimeline(),
																"customerTimeline" => $role->getCustomerTimeline(),
																"gantt" => $role->getGantt(),
																"whiteboard" => $role->getWhiteboard(),
																"bugtracker" => $role->getBugtracker(),
																"event" => $role->getEvent(),
																"task" => $role->getTask(),
																"projectSettings" => $role->getProjectSettings(),
																"cloud" => $role->getCloud());

						$arr[] = array("id" => $purId, "project" => array("id" => $projectId, "name" => $projectName), "role" => array("id" => $roleId, "name" => $roleName, "values" => $roleValues));
					}
				}
			}
		}

		if (count($arr) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserrolesinformations");

		return $this->setSuccess("1.13.1", "Role", "getuserrolesinformations", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {get} /mongo/roles/getuserroleforpart/:token/:userId/:projectId/:part Get user's rights for a specific part
	* @apiName getUserRoleForPArt
	* @apiGroup Roles
	* @apiDescription Get user's rights (0: none, 1: readonly, 2:read& write) for a specific part (timeline, bugtracker, ...)
	* @apiVersion 0.2.0
	*
	*/
	public function getUserRoleForPartAction(Request $request, $token, $userId, $projectId, $part)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("13.13.3", "Role", "getUserRoleForPart"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$roleId = $em->getRepository('MongoBundle:ProjectUserRole')->findOneBy(array("userId" => $userId, "projectId" => $projectId));
		if (!($roleId instanceof ProjectUserRole))
			return $this->setBadRequest("13.13.4", "Role", "getUserRoleForPart", "Bad Parameter: userId or projectId");

		$role = $em->getRepository('MongoBundle:Role')->find($roleId->getRoleId());

		if ($role->getPart($part) == -1)
			return $this->setBadRequest("13.13.4", "Role", "getUserRoleForPart", "Bad Parameter: part");

		return $this->setSuccess("1.13.1", "Role", "getUserRoleForPart", "Complete Success", array("user_id" => $userId, "name" => $part, "value" => $role->getPart($part)));

	}

}
