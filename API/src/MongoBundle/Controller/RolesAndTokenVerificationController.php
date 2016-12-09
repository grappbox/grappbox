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
		$auth = $em->getRepository('MongoBundle:Authentication')->findOneBy(array('token' => $token));

		if (!$auth)
			return $auth;

		$now = new DateTime('now');
		if ($auth->getToken() && $auth->getTokenValidity() && $auth->getTokenValidity() < $now)
		{
			$auth->setToken(null);

			$em->persist($auth);
			$em->flush();

			return null;
		}
		else if ($auth->getToken() && $auth->getTokenValidity())
		{
			$auth->setTokenValidity($now->add(new DateInterval("P1D")));

			$em->persist($auth);
			$em->flush();
		}

		return $auth->getUser();
	}

	// return 0 if user has no rigths on this role
	// return 1 if user has readOnly rights
	// return 2 if user has read and writte rights
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


	// public function addProjectRolesAction(Request $request)
	// {
	// 	$content = $request->getContent();
	// 	$content = json_decode($content);
	// 	$content = $content->data;
	//
	// 	if (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('name', $content) || !array_key_exists('teamTimeline', $content)
	// 		|| !array_key_exists('customerTimeline', $content) || !array_key_exists('gantt', $content) || !array_key_exists('whiteboard', $content) || !array_key_exists('bugtracker', $content)
	// 		|| !array_key_exists('event', $content) || !array_key_exists('task', $content) || !array_key_exists('projectSettings', $content) || !array_key_exists('cloud', $content))
	// 		return $this->setBadRequest("13.1.6", "Role", "addprojectroles", "Missing Parameter");
	//
	// 	$user = $this->checkToken($content->token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.1.3", "Role", "addprojectroles"));
	//
	// 	if ($content->name == "Admin")
	// 		return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: You can't create a role named Admin");
	//
	// 	if ($this->checkRoles($user, $content->projectId, "projectSettings"))
	// 		return $this->setNoRightsError("13.1.9", "Role", "addprojectroles");
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$role = new Role();
	//
	// 	$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
	// 	if ($project === null)
	// 		return $this->setBadRequest("13.1.4", "Role", "addprojectroles", "Bad Parameter: projectId");
	//
	// 	$role->setProjects($project);
	// 	$role->setName($content->name);
	// 	$role->setTeamTimeline($content->teamTimeline);
	// 	$role->setCustomerTimeline($content->customerTimeline);
	// 	$role->setGantt($content->gantt);
	// 	$role->setWhiteboard($content->whiteboard);
	// 	$role->setBugtracker($content->bugtracker);
	// 	$role->setEvent($content->event);
	// 	$role->setTask($content->task);
	// 	$role->setProjectSettings($content->projectSettings);
	// 	$role->setCloud($content->cloud);
	//
	// 	$em->persist($role);
	// 	$em->flush();
	//
	// 	return $this->setCreated("1.13.1", "Role", "addprojectroles", "Complete Success", array("id" => $role->getId()));
	// }

	// public function delProjectRolesAction(Request $request, $token, $id)
	// {
	// 	$user = $this->checkToken($content->token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.2.3", "Role", "delprojectroles"));
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$role = $em->getRepository('MongoBundle:Role')->find($content->id);
	//
	// 	if ($role === null)
	// 		return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: id");
	//
	// 	if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
	// 		return $this->setNoRightsError("13.2.9", "Role", "delprojectroles");
	//
	// 	if ($role->getName() == "Admin")
	// 		return $this->setBadRequest("13.2.4", "Role", "delprojectroles", "Bad Parameter: You can't remove the Admin role");
	//
	// 	$em->remove($role);
	// 	$em->flush();
	//
	// 	$response["info"]["return_code"] = "1.13.1";
	// 	$response["info"]["return_message"] = "Role - delprojectroles - Complete Success";
	// 	return new JsonResponse($response);
	// }

	// public function updateProjectRolesAction(Request $request)
	// {
	// 	$content = $request->getContent();
	// 	$content = json_decode($content);
	// 	$content = $content->data;
	//
	// 	if (!array_key_exists('roleId', $content) || !array_key_exists('token', $content))
	// 		return $this->setBadRequest("13.3.6", "Role", "putprojectroles", "Missing Parameter");
	//
	// 	$user = $this->checkToken($content->token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.3.3", "Role", "putprojectroles"));
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);
	//
	// 	if ($role === null)
	// 		return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: roleId");
	//
	// 	if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 2)
	// 		return $this->setNoRightsError("13.3.9", "Role", "putprojectroles");
	//
	// 	if ($role->getName() == "Admin")
	// 		return $this->setBadRequest("13.3.4", "Role", "putprojectroles", "Bad Parameter: You can't update the Admin role");
	//
	// 	if (array_key_exists('name', $content))
	// 		$role->setName($content->name);
	// 	if (array_key_exists('teamTimeline', $content))
	// 		$role->setTeamTimeline($content->teamTimeline);
	// 	if (array_key_exists('customerTimeline', $content))
	// 		$role->setCustomerTimeline($content->customerTimeline);
	// 	if (array_key_exists('gantt', $content))
	// 		$role->setGantt($content->gantt);
	// 	if (array_key_exists('whiteboard', $content))
	// 		$role->setWhiteboard($content->whiteboard);
	// 	if (array_key_exists('bugtracker', $content))
	// 		$role->setBugtracker($content->bugtracker);
	// 	if (array_key_exists('event', $content))
	// 		$role->setEvent($content->event);
	// 	if (array_key_exists('task', $content))
	// 		$role->setTask($content->task);
	// 	if (array_key_exists('projectSettings', $content))
	// 		$role->setProjectSettings($content->projectSettings);
	// 	if (array_key_exists('cloud', $content))
	// 		$role->setcloud($content->cloud);
	//
	// 	$em->flush();
	//
	// 	return $this->setSuccess("1.13.1", "Role", "putprojectroles", "Complete Success", array("id" => $role->getId()));
	// }

	// public function getProjectRolesAction(Request $request, $token, $projectId)
	// {
	// 	$user = $this->checkToken($token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.4.3", "Role", "getprojectroles"));
	//
	// 	if ($this->checkRoles($user, $projectId, "projectSettings") < 1)
	// 		return $this->setNoRightsError("13.4.9", "Role", "getprojectroles");
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$roles = $em->getRepository('MongoBundle:Role')->findByProjects($projectId);
	//
	// 	if ($roles === null)
	// 		return $this->setBadRequest("13.4.4", "Role", "getprojectroles", "Bad Parameter: projectId");
	//
	// 	$arr =array();
	//
	// 	if (count($roles) == 0)
	// 		return $this->setNoDataSuccess("1.13.3", "Role", "getprojectroles");
	//
	// 	foreach ($roles as $role) {
	// 		$roleId = $role->getId();
	// 		$roleName = $role->getName();
	// 		$teamTimeline = $role->getTeamTimeline();
	// 		$customerTimeline = $role->getCustomerTimeline();
	// 		$gantt = $role->getGantt();
	// 		$whiteboard = $role->getWhiteboard();
	// 		$bugtracker = $role->getBugtracker();
	// 		$event = $role->getEvent();
	// 		$task = $role->getTask();
	// 		$projectSettings = $role->getProjectSettings();
	// 		$cloud = $role->getCloud();
	//
	// 		$arr[] = array("id" => $roleId, "name" => $roleName, "team_timeline" => $teamTimeline, "customer_timeline" => $customerTimeline, "gantt" => $gantt,
	// 			"whiteboard" => $whiteboard, "bugtracker" => $bugtracker, "event" => $event, "task" => $task, "project_settings" => $projectSettings, "cloud" => $cloud);
	// 	}
	//
	// 	return $this->setSuccess("1.13.1", "Role", "getprojectroles", "Complete Success", array("array" => $arr));
	// }

	// public function assignPersonToRoleAction(Request $request)
	// {
	// 	$content = $request->getContent();
	// 	$content = json_decode($content);
	// 	$content = $content->data;
	//
	// 	if (!array_key_exists('roleId', $content) || !array_key_exists('userId', $content) || !array_key_exists('token', $content))
	// 		return $this->setBadRequest("13.5.6", "Role", "assignpersontorole", "Missing Parameter");
	//
	// 	$user = $this->checkToken($content->token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.5.3", "Role", "assignpersontorole"));
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);
	// 	$userToAdd = $em->getRepository('MongoBundle:User')->find($content->userId);
	//
	// 	if ($role === null)
	// 		return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: roleId");
	// 	if ($userToAdd === null)
	// 		return $this->setBadRequest("13.5.4", "Role", "assignpersontorole", "Bad Parameter: userId");
	//
	// 	$projectId = $role->getProjects()->getId();
	// 	if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
	// 		return $this->setNoRightsError("13.5.9", "Role", "assignpersontorole");
	//
	// 	$repository = $em->getRepository('MongoBundle:ProjectUserRole');
	// 	$qb = $repository->createQueryBuilder('p')->where('p.roleId = :roleId', 'p.userId = :userId')->setParameter('roleId', $content->roleId)->setParameter('userId', $content->userId)->getQuery();
	// 	$purs = $qb->execute();
	//
	// 	if (count($purs) == 0)
	// 	{
	// 		$ProjectUserRole = new ProjectUserRole();
	// 		$ProjectUserRole->setProjectId($projectId);
	// 		$ProjectUserRole->setUserId($content->userId);
	// 		$ProjectUserRole->setRoleId($content->roleId);
	//
	// 		$em->persist($ProjectUserRole);
	// 		$em->flush();
	//
	// 		return $this->setCreated("1.13.1", "Role", "assignpersontorole", "Complete Success", array("id" => $ProjectUserRole->getId()));
	// 	}
	// 	else
	// 		return $this->setBadRequest("13.5.7", "Role", "assignpersontorole", "Already In Database");
	// }

	// public function updatePersonRoleAction(Request $request)
	// {
	// 	$content = $request->getContent();
	// 	$content = json_decode($content);
	// 	$content = $content->data;
	//
	// 	if (!array_key_exists('roleId', $content) || !array_key_exists('userId', $content) || !array_key_exists('token', $content)
	// 		|| !array_key_exists('projectId', $content) || !array_key_exists('old_roleId', $content))
	// 		return $this->setBadRequest("13.6.6", "Role", "putpersonrole", "Missing Parameter");
	//
	// 	$user = $this->checkToken($content->token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.6.3", "Role", "putpersonrole"));
	//
	// 	if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
	// 		return $this->setNoRightsError("13.5.9", "Role", "putpersonrole");
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$repository = $em->getRepository('MongoBundle:ProjectUserRole');
	//
	// 	$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
	// 	->setParameter('projectId', $content->projectId)->setParameter('userId', $content->userId)->setParameter('roleId', $content->old_roleId)->getQuery();
	// 	$pur = $qb->getSingleResult();
	//
	// 	if ($pur === null)
	// 		return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter");
	//
	// 	$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);
	// 	if ($role === null)
	// 		return $this->setBadRequest("13.6.4", "Role", "putpersonrole", "Bad Parameter: roleId");
	//
	// 	$pur->setRoleId($content->roleId);
	//
	// 	$em->flush();
	//
	// 	return $this->setSuccess("1.13.1", "Role", "putpersonrole", "Complete Success", array("id" => $pur->getId()));
	// }

	// public function getUserRolesAction(Request $request, $token)
	// {
	// 	$user = $this->checkToken($token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.7.3", "Role", "getuserroles"));
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$userRoles = $em->getRepository('MongoBundle:ProjectUserRole')->findByUserId($user->getId());
	//
	// 	if (count($userRoles) == 0 || $userRoles === null)
	// 		return $this->setNoDataSuccess("1.13.3", "Role", "getuserroles");
	//
	// 	$arr = array();
	//
	// 	foreach ($userRoles as $role) {
	// 		$purId = $role->getId();
	// 		$projectId = $role->getProjectId();
	// 		$roleId = $role->getRoleId();
	//
	// 		$arr[] = array("id" => $purId, "project_id" => $projectId, "role_id" => $roleId);
	// 	}
	//
	// 	return $this->setSuccess("1.13.1", "Role", "getuserroles", "Complete Success", array("array" => $arr));
	// }

	// public function delPersonRoleAction(Request $request, $token, $projectId, $userId, $roleId)
	// {
	// 	$user = $this->checkToken($content->token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.8.3", "Role", "delpersonrole"));
	//
	// 	if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
	// 		return $this->setNoRightsError("13.8.9", "Role", "delpersonrole");
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
	// 	$role = $em->getRepository('MongoBundle:Role')->find($content->roleId);
	//
	// 	if ($project === null)
	// 		return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: projectId");
	// 	if ($role === null)
	// 		return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: roleId");
	//
	// 	if ($project->getCreatorUser()->getId() == $content->userId && $role->getName() == "Admin")
	// 		return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameter: You can't remove the creator from the Admin role");
	//
	// 	$repository = $em->getRepository('MongoBundle:ProjectUserRole');
	//
	// 	$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
	// 	->setParameter('projectId', $content->projectId)->setParameter('userId', $content->userId)->setParameter('roleId', $content->roleId)->getQuery();
	// 	$pur = $qb->getSingleResult();
	//
	// 	if ($pur == null)
	// 		return $this->setBadRequest("13.8.4", "Role", "delpersonrole", "Bad Parameters");
	//
	// 	$purId = $pur->getId();
	// 	$em->remove($pur);
	// 	$em->flush();
	//
	// 	$response["info"]["return_code"] = "1.13.1";
	// 	$response["info"]["return_message"] = "Role - delpersonrole - Complete Success";
	// 	return new JsonResponse($response);
	// }

	// public function getRoleByProjectAndUserAction(Request $request, $token, $projectId, $userId)
	// {
	// 	$user = $this->checkToken($token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.9.3", "Role", "getrolebyprojectanduser"));
	//
	// 	if ($this->checkRoles($user, $projectId, "projectSettings") < 1)
	// 		return $this->setNoRightsError("13.9.9", "Role", "getrolebyprojectanduser");
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$repository = $em->getRepository('MongoBundle:ProjectUserRole');
	// 	$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId')->setParameter('projectId', $projectId)->setParameter('userId', $userId)->getQuery();
	// 	$purs = $qb->execute();
	//
	// 	if ($purs === null)
	// 		return $this->setBadRequest("13.9.4", "Role", "getrolebyprojectanduser", "Bad Parameters");
	//
	// 	if (count($purs) == 0)
	// 		return $this->setNoDataSuccess("1.13.3", "Role", "getrolebyprojectanduser");
	//
	// 	$arr = array();
	//
	// 	foreach ($purs as $role) {
	// 		$roleId = $role->getRoleId();
	// 		$arr[] = array("id" => $roleId);
	// 	}
	//
	// 	return $this->setSuccess("1.13.1", "Role", "getrolebyprojectanduser", "Complete Success", array("array" => $arr));
	// }

	// public function getUsersForRoleAction(Request $request, $token, $roleId)
	// {
	// 	$user = $this->checkToken($token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.10.3", "Role", "getusersforrole"));
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$role = $em->getRepository('MongoBundle:Role')->find($roleId);
	// 	if ($role === null)
	// 		return $this->setBadRequest("13.10.4", "Role", "getusersforrole", "Bad Parameter: roleId");
	//
	// 	if ($this->checkRoles($user, $role->getProjects()->getId(), "projectSettings") < 1)
	// 		return $this->setNoRightsError("13.10.9", "Role", "getusersforrole");
	//
	// 	$purRepository = $em->getRepository('MongoBundle:ProjectUserRole');
	// 	$qb = $purRepository->createQueryBuilder('pur')->where('pur.roleId = :id')->setParameter('id', $role->getId())->getQuery();
	// 	$purs = $qb->execute();
	//
	// 	$usersAssigned = array();
	// 	$usersNonAssigned = array();
	//
	// 	$users = $role->getProjects()->getUsers();
	//
	// 	foreach ($users as $u) {
	// 		$isAssigned = false;
	//
	// 		foreach ($purs as $p) {
	// 			if ($p->getUserId() == $u->getId())
	// 			{
	// 				$usersAssigned[] = array("id" => $u->getId(), "firstname" => $u->getFirstname(), "lastname" => $u->getLastname());
	// 				$isAssigned = true;
	// 			}
	// 		}
	// 		if ($isAssigned == false)
	// 		$usersNonAssigned[] = array("id" => $u->getId(), "firstname" => $u->getFirstname(), "lastname" => $u->getLastname());
	// 	}
	//
	// 	return $this->setSuccess("1.13.1", "Role", "getusersforrole", "Complete Success",
	// 		array("id" => $role->getId(), "name" => $role->getName(), "users_assigned" => $usersAssigned, "users_non_assigned" => $usersNonAssigned));
	// }

	// public function getUserRoleForPartAction(Request $request, $token, $userId, $projectId, $part)
	// {
	// 	$user = $this->checkToken($token);
	// 	if (!$user)
	// 		return ($this->setBadTokenError("13.13.3", "Role", "getUserRoleForPart"));
	//
	// 	$em = $this->get('doctrine_mongodb')->getManager();
	// 	$roleId = $em->getRepository('MongoBundle:ProjectUserRole')->findOneBy(array("userId" => $userId, "projectId" => $projectId));
	// 	if (!($roleId instanceof ProjectUserRole))
	// 		return $this->setBadRequest("13.13.4", "Role", "getUserRoleForPart", "Bad Parameter: userId or projectId");
	//
	// 	$role = $em->getRepository('MongoBundle:Role')->find($roleId->getRoleId());
	//
	// 	if ($role->getPart($part) == -1)
	// 		return $this->setBadRequest("13.13.4", "Role", "getUserRoleForPart", "Bad Parameter: part");
	//
	// 	return $this->setSuccess("1.13.1", "Role", "getUserRoleForPart", "Complete Success", array("user_id" => $userId, "name" => $part, "value" => $role->getPart($part)));
	//
	// }

}
