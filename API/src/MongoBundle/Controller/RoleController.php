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
*  @IgnoreAnnotation("apiHeader")
*  @IgnoreAnnotation("apiHeaderExample")
*/
class RoleController extends RolesAndTokenVerificationController
{
	/**
	* @-api {post} /0.3/role Create a role
	* @apiName addProjectRoles
	* @apiGroup Roles
	* @apiDescription Create a role for a project, 0: NONE, 1: READ ONLY, 2: READ & WRITE
	* @apiVersion 0.3.0
	*
	*/
	public function addProjectRolesAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('name', $content) || !array_key_exists('teamTimeline', $content)
			|| !array_key_exists('customerTimeline', $content) || !array_key_exists('gantt', $content)
			|| !array_key_exists('whiteboard', $content) || !array_key_exists('bugtracker', $content)
			|| !array_key_exists('event', $content) || !array_key_exists('task', $content) || !array_key_exists('projectSettings', $content)
			|| !array_key_exists('cloud', $content))
			return $this->setBadRequest("13.1.6", "Role", "addprojectroles", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.1.3", "Role", "addprojectroles"));

		if ($content->name == "Admin" || $content->name == "Customer")
			return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: Can't create a role named Admin or Customer");

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.1.9", "Role", "addprojectroles");
		$role = new Role();

		$roles = $em->getRepository("MongoBundle:Role")->findBy(array('projects.id'=> $project->getId(), 'name' => $content->name));
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.13.1", "Role", "addprojectroles", "Complete Success", $role->objectToArray());
	}

	/**
	* @-api {delete} /0.3/role/:id Delete role
	* @apiName delProjectRoles
	* @apiGroup Roles
	* @apiDescription Delete the given role of the project wanted
	* @apiVersion 0.3.0
	*
	*/
	public function delProjectRolesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.2.3", "Role", "delprojectroles"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = $em->getRepository('MongoBundle:Role')->find($id);

		if ($role === null)
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: id");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
			return $this->setNoRightsError("13.2.9", "Role", "delprojectroles");

		if ($role->getName() == "Admin" || $role->getName() == "Customer")
			return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: Can't remove the Admin or Customer role");

		$users = $em->getRepository("MongoBundle:ProjectUserRole")->findBy(array('roleId'=> $id));
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($role);
		$em->flush();

		$response["info"]["return_code"] = "1.13.1";
		$response["info"]["return_message"] = "Role - delprojectroles - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {put} /0.3/role/:id Update role
	* @apiName updateProjectRoles
	* @apiGroup Roles
	* @apiDescription Update role caracteristics, 0: NONE, 1: READ ONLY, 2: READ & WRITE
	* @apiVersion 0.3.0
	*
	*/
	public function updateProjectRolesAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.3.3", "Role", "putprojectroles"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = $em->getRepository('MongoBundle:Role')->find($id);

		if ($role === null)
			return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: id");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
			return $this->setNoRightsError("13.3.9", "Role", "putprojectroles");

		if ($role->getName() == "Admin")
			return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: Can't update the Admin role");

		if ($role->getName() == "Customer" && array_key_exists('name', $content))
			return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: Can't update the Customer role name");

		$roles = $em->getRepository("MongoBundle:Role")->findBy(array('projects.id'=> $role->getProjects()->getId(), 'name' => $content->name));
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
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.13.1", "Role", "putprojectroles", "Complete Success", $role->objectToArray());
	}

	/**
	* @-api {get} /0.3/roles/:projectId Get roles by project
	* @apiName GetProjectRoles
	* @apiGroup Roles
	* @apiDescription Get all the roles of the given project
	* @apiVersion 0.3.0
	*
	*/
	public function getProjectRolesAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.4.3", "Role", "getprojectroles"));

		$em = $this->get('doctrine_mongodb')->getManager();
		// $roles = $em->getRepository('MongoBundle:Role')->findByprojects($projectId);
		$roles = $em->getRepository('MongoBundle:Role')->findBy(array('projects.id' => $projectId));
		if ($roles === null)
			return $this->setBadRequest("13.4.4", "Role", "getprojectroles", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "projectSettings") < 1)
			return $this->setNoRightsError("13.4.9", "Role", "getprojectroles");

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

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);
		$userToAdd = $em->getRepository('MongoBundle:User')->find($content->userId);

		if ($role === null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: roleId");
		if ($role->getProjects()->getCreatorUser()->getId() == $content->userId && $role->getName() != "Admin")
			return $this->setBadRequest("13.8.4", "Role", "assignpersontorole", "Bad Parameter: You can't add the creator to another role than Admin role");
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

		$pur = $em->getRepository('MongoBundle:ProjectUserRole')->findBy(array('projectId'=> $projectId, 'userId'=> $content->userId));
		if($pur != null)
			return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: User already have a role");


		// $repository = $em->getRepository('MongoBundle:ProjectUserRole');
		// $qb = $repository->createQueryBuilder('p')
		// 	->where('p.roleId = :roleId', 'p.userId = :userId')->setParameter('roleId', $content->roleId)->setParameter('userId', $content->userId)->getQuery();
		// $purs = $qb->getResult();
		$purs = $em->getRepository('MongoBundle:ProjectUserRole')->findBy(array('roleId' => $content->roleId, 'userId' => $content->userId));

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
			$mdata['mdesc'] = json_encode(array("user_id" => $content->userId, "role_id" => $content->roleId, "project_id" => $projectId));
			$wdata['type'] = "assign user role";
			$wdata['targetId'] = $role->getId();
			$wdata['message'] = json_encode(array("user_id" => $content->userId, "role_id" => $content->roleId, "project_id" => $projectId));
			$userNotif = array();
			foreach ($role->getProjects()->getUsers() as $key => $value) {
				$userNotif[] = $value->getId();
			}
			if (count($userNotif) > 0)
				$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

			return $this->setCreated("1.13.1", "Role", "assignpersontorole", "Complete Success", array("id" => $ProjectUserRole->getId()));
		}
		else
			return $this->setBadRequest("13.5.7", "Role", "assignpersontorole", "Already In Database");
	}

	/**
	* @-api {put} /0.3/role/user/:userId Change user role
	* @apiName updatePersonRole
	* @apiGroup Roles
	* @apiDescription Change the role of a user ofr the realted project
	* @apiVersion 0.3.0
	*
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

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("13.5.4", "Role", "putpersonrole", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.5.9", "Role", "putpersonrole");

		$repository = $em->getRepository('MongoBundle:ProjectUserRole');
    $pur = $repository->findOneBy(array('projectId' => $content->projectId, 'userId' => $userId, 'roleId' => $content->old_roleId));

		// $qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
		// ->setParameter('projectId', $content->projectId)->setParameter('userId', $userId)->setParameter('roleId', $content->old_roleId)->getQuery();
		// $pur = $qb->setMaxResults(1)->getOneOrNullResult();

		if ($pur === null)
			return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter");

		$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);
		if ($role === null)
			return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter: roleId");

		if ($role->getProjects()->getCreatorUser()->getId() == $userId && $role->getName() == "Admin")
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: You can't remove the creator from the Admin role");

		$pur->setRoleId($content->roleId);
		$em->flush();

		//notifs
		$mdata['mtitle'] = "update user role";
		$mdata['mdesc'] = json_encode(array("user_id" => $userId, "role_id" => $content->roleId, "project_id" => $content->projectId));
		$wdata['type'] = "update user role";
		$wdata['targetId'] = $role->getId();
		$wdata['message'] = json_encode(array("user_id" => $userId, "role_id" => $content->roleId, "project_id" => $content->projectId));
		$userNotif = array();
		foreach ($role->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.13.1", "Role", "putpersonrole", "Complete Success", array("id" => $pur->getId()));
	}

	/**
	* @-api {get} /0.3/roles/user Get connected user roles
	* @apiName getuserroles
	* @apiGroup Roles
	* @apiDescription Get the all roles linked to the connected user
	* @apiVersion 0.3.0
	*
	*/
	public function getUserRolesAction(Request $request)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.7.3", "Role", "getuserroles"));

		$em = $this->get('doctrine_mongodb')->getManager();
		//$userRoles = $em->getRepository('MongoBundle:ProjectUserRole')->findByuserId($user->getId());
		$userRoles = $em->getRepository('MongoBundle:ProjectUserRole')->findBy(array('userId' => $user->getId()));

		if (count($userRoles) == 0 || $userRoles === null)
			return $this->setNoDataSuccess("1.13.3", "Role", "getuserroles");

		$arr = array();

		foreach ($userRoles as $pur) {
			$role = $em->getRepository('MongoBundle:Role')->find($pur->getRoleId());
			$arr[] = $role->objectToArray();
		}

		return $this->setSuccess("1.13.1", "Role", "getuserroles", "Complete Success", array("array" => $arr));
	}

	/**
	* @-api {delete} /0.3/role/user/:projectId/:userId/:roleId Unassign user to role
	* @apiName delPersonRole
	* @apiGroup Roles
	* @apiDescription Unlink given user and role
	* @apiVersion 0.3.0
  *
	*/
	public function delPersonRoleAction(Request $request, $projectId, $userId, $roleId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.8.3", "Role", "delpersonrole"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return $this->setNoRightsError("13.8.9", "Role", "delpersonrole");
		$role = $em->getRepository('MongoBundle:Role')->find($roleId);

		if ($role === null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: roleId");

		if ($project->getCreatorUser()->getId() == $userId && $role->getName() == "Admin")
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: You can't remove the creator from the Admin role");

		//$repository = $em->getRepository('MongoBundle:ProjectUserRole');
		// $qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
		// ->setParameter('projectId', $projectId)->setParameter('userId', $userId)->setParameter('roleId', $roleId)->getQuery();
		// $pur = $qb->setMaxResults(1)->getOneOrNullResult();
		$pur = $em->getRepository('MongoBundle:ProjectUserRole')->findOneBy(array("projectId" => $projectId, "userId" => $userId, "roleId" => $roleId));

		if ($pur == null)
			return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameters");

		//notifs
		$mdata['mtitle'] = "delete user role";
		$mdata['mdesc'] = json_encode(array("user_id" => $userId, "role_id" => $roleId, "project_id" => $projectId));
		$wdata['type'] = "delete user role";
		$wdata['targetId'] = $role->getId();
		$wdata['message'] = json_encode(array("user_id" => $userId, "role_id" => $roleId, "project_id" => $projectId));
		$userNotif = array();
		foreach ($role->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$purId = $pur->getId();
		$em->remove($pur);
		$em->flush();

		$response["info"]["return_code"] = "1.13.1";
		$response["info"]["return_message"] = "Role - delpersonrole - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {get} /0.3/roles/project/user/:projectId/[:userId] Get user role by project
	* @apiName getRoleByProjectAndUser
	* @apiGroup Roles
	* @apiDescription Get user role for a given project, if userId not specified assumed reference user is the connected user
	* @apiVersion 0.3.0
	*
	*/
	public function getRoleByProjectAndUserAction(Request $request, $projectId, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.9.3", "Role", "getrolebyprojectanduser"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("13.9.4", "Role", "getrolebyprojectanduser", "Bad Parameter: projectId");

		$u = $em->getRepository('MongoBundle:User')->find($userId);
		if ($u === null)
			return $this->setBadRequest("13.9.4", "Role", "getrolebyprojectanduser", "Bad Parameter: userId");

		if ($this->checkRoles($user, $projectId, "projectSettings") < 1)
			return $this->setNoRightsError("13.9.9", "Role", "getrolebyprojectanduser");

		if ($userId == 0)
			$userId = $user->getId();

		$repository = $em->getRepository('MongoBundle:ProjectUserRole');
    $purs = $repository->findBy(array("projectId" => $projectId, "userId" => $userId));
		//$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId')->setParameter('projectId', $projectId)->setParameter('userId', $userId)->getQuery();
		//$purs = $qb->getResult();

		if ($purs === null)
			return $this->setBadRequest("13.9.4", "Role", "getrolebyprojectanduser", "Bad Parameters");

		if (count($purs) == 0)
			return $this->setNoDataSuccess("1.13.3", "Role", "getrolebyprojectanduser");

		$role = $em->getRepository('MongoBundle:Role')->find($purs[0]->getRoleId());

		return $this->setSuccess("1.13.1", "Role", "getrolebyprojectanduser", "Complete Success", $role->objectToArray());
	}

	/**
	* @-api {get} /0.3/role/users/:roleId Get (un)assigned users by role
	* @apiName getUsersForRole
	* @apiGroup Roles
	* @apiDescription Get the users assigned and non assigned on the given role with their basic informations
	* @apiVersion 0.3.0
	*
	*/
	public function getUsersForRoleAction(Request $request, $roleId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.10.3", "Role", "getusersforrole"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$role = $em->getRepository('MongoBundle:Role')->find($roleId);
		if ($role === null)
			return $this->setBadRequest("13.10.4", "Role", "getusersforrole", "Bad Parameter: roleId");

		if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 1)
			return $this->setNoRightsError("13.10.9", "Role", "getusersforrole");

		$purRepository = $em->getRepository('MongoBundle:ProjectUserRole');
    $purs = $purRepository->findBy(array("roleId" => $roleId));

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
	* @-api {get} /V0.3/role/user/part/:userId/:projectId/:part Get user's rights for a specific part
	* @apiName getUserRoleForPArt
	* @apiGroup Roles
	* @apiDescription Get user's rights (0: none, 1: readonly, 2:read& write) for a specific part (customer_timeline, bugtracker, ...)
	* @apiVersion 0.3.0
	*/
	public function getUserRoleForPartAction(Request $request, $userId, $projectId, $part)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("13.11.3", "Role", "getUserRoleForPart"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$roleId = $em->getRepository('MongoBundle:ProjectUserRole')->findOneBy(array("userId" => $userId, "projectId" => $projectId));
		if (!($roleId instanceof ProjectUserRole))
			return $this->setBadRequest("13.11.4", "Role", "getUserRoleForPart", "Bad Parameter: userId or projectId");

		$role = $em->getRepository('MongoBundle:Role')->find($roleId->getRoleId());

		if ($role->getPart($part) == -1)
			return $this->setBadRequest("13.11.4", "Role", "getUserRoleForPart", "Bad Parameter: part");

		return $this->setSuccess("1.13.1", "Role", "getUserRoleForPart", "Complete Success", array("user_id" => $userId, "name" => $part, "value" => $role->getPart($part)));

	}

	/**
	* @-api {get} - Get roles info of connected user
	* @apiName getUserConnectedRolesInformations
	* @apiGroup Roles
	* @apiDescription This request no longer exists. See [getUserRoles](0.3/#api-Roles-getuserroles) or [getUserRoleByProject](0.3/#api-Roles-getRoleByProjectAndUser)
	* @apiVersion 0.3.0
	*
	*/

	/**
	* @-api {get} - Get roles info of given user
	* @apiName getUserRolesInformations
	* @apiGroup Roles
	* @apiDescription This request no longer exists. See [getUserRoleByProject](0.3/#api-Roles-getRoleByProjectAndUser)
	* @apiVersion 0.3.0
	*/

}
