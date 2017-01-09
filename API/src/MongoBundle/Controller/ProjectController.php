<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use MongoBundle\Document\Project;
use MongoBundle\Document\CustomerAccess;
use MongoBundle\Document\Role;
use MongoBundle\Document\ProjectUserRole;
use MongoBundle\Document\Tag;
use MongoBundle\Document\Timeline;
use MongoBundle\Document\Color;

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
*	@IgnoreAnnotation("apiIgnore")
*	@IgnoreAnnotation("apiDescription")
*/
class ProjectController extends RolesAndTokenVerificationController
{
	/**
	* @-api {post} /0.3/project Create a project for the user connected
	* @apiName projectCreation
	* @apiGroup Project
	* @apiDescription Create a project for the user connected
	* @apiVersion 0.3.0
	*
	*/
	public function projectCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('name', $content) || !array_key_exists('password', $content))
			return $this->setBadRequest("6.1.6", "Project", "projectcreation", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return $this->setBadTokenError("6.1.3", "Project", "projectcreation");

		$em = $this->get('doctrine_mongodb')->getManager();

		$project = new Project();
		$project->setName($content->name);
		$project->setCreatedAt(new \DateTime);
		$project->setCreatorUser($user);
		$project->setColor(dechex(rand(0x000000, 0xFFFFFF)));
		$project->setSafePassword($content->password);
		if (array_key_exists('description', $content))
			$project->setDescription($content->description);

		if (array_key_exists('phone', $content))
			$project->setPhone($content->phone);
		if (array_key_exists('company', $content))
			$project->setCompany($content->company);
		if (array_key_exists('email', $content))
			$project->setContactEmail($content->email);
		if (array_key_exists('facebook', $content))
			$project->setFacebook($content->facebook);
		if (array_key_exists('twitter', $content))
			$project->setTwitter($content->twitter);

		$em->persist($project);
		$project->addUser($user);

		if (array_key_exists('logo', $content)) {
			$filepath = "/var/www/static/app/project/".$id;

			$file = base64_decode($content->logo);
			if ($file == false)
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Bad Parameter: logo");

			$image = imagecreatefromstring($file);
			if ($image == false)
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Bad Parameter: logo");

			if (!imagejpeg($image, $filepath, 80))
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Bad Parameter: logo");

			imagedestroy($image);

			$fileurl = 'https://static.grappbox.com/app/project/'.$id;

			$project->setLogo($fileurl);
			$project->setLogoDate(new \DateTime);
		}

		//Create admin role
		$role = new Role();
		$role->setName("Admin");
		$role->setTeamTimeline(2);
		$role->setCustomerTimeline(2);
		$role->setGantt(2);
		$role->setWhiteboard(2);
		$role->setBugtracker(2);
		$role->setEvent(2);
		$role->setTask(2);
		$role->setProjectSettings(2);
		$role->setCloud(2);
		$role->setProjects($project);
		$em->persist($role);

		//create customer role
		$client_role = new Role();
		$client_role->setName("Customer");
		$client_role->setTeamTimeline(0);
		$client_role->setCustomerTimeline(2);
		$client_role->setGantt(0);
		$client_role->setWhiteboard(2);
		$client_role->setBugtracker(0);
		$client_role->setEvent(0);
		$client_role->setTask(0);
		$client_role->setProjectSettings(0);
		$client_role->setCloud(0);
		$client_role->setProjects($project);
		$em->persist($client_role);

		$em->flush();

		//Assign the creator to the admin role
		$pur = new ProjectUserRole();
		$pur->setProjectId($project->getId());
		$pur->setUserId($user->getId());
		$pur->setRoleId($role->getId());
		$em->persist($pur);

		//Create the default tags
		$qb = $em->getRepository('MongoBundle:Tag')->createQueryBuilder('t')->getQuery();
		$tags = $qb->execute();
		foreach ($tags as $t) {
			if ($t->getProject() === null)
			{
				$newTag = new Tag();
				$newTag->setName($t->getName());
				$newTag->setProject($project);
				$newTag->setColor($t->getColor());
				$em->persist($newTag);
			}
		}

		//Create the default bugtracker tags
		$qb = $em->getRepository('MongoBundle:BugtrackerTag')->createQueryBuilder('t')->getQuery();
		$btags = $qb->execute();
		foreach ($btags as $t) {
			if ($t->getProject() === null)
			{
				$newTag = new BugtrackerTag();
				$newTag->setName($t->getName());
				$newTag->setProject($project);
				$newTag->setColor($t->getColor());
				$em->persist($newTag);
			}
		}

		$em->flush();
		$id = $project->getId();

		//Init the cloud for the project
		$cloudClass = new CloudController();
		$cloudClass->createCloudAction($request, $id);

		//Create team timeline
		$teamTimeline = new Timeline();
		$teamTimeline->setTypeId(2);
		$teamTimeline->setProjects($project);
		$teamTimeline->setProjectId($project->getId());
		$teamTimeline->setName("TeamTimeline - ".$project->getName());
		$em->persist($teamTimeline);

		//Create customer timeline
		$customerTimeline = new Timeline();
		$customerTimeline->setTypeId(1);
		$customerTimeline->setProjects($project);
		$customerTimeline->setProjectId($project->getId());
		$customerTimeline->setName("CustomerTimeline - ".$project->getName());
		$em->persist($customerTimeline);
		$em->flush();

		//notifs
		$mdata['mtitle'] = "new project";
		$mdata['mdesc'] = json_encode($project->objectToArray($em, $user));
		$wdata['type'] = "new project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = json_encode($project->objectToArray($em, $user));
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('mongo_service_stat')->initiateStatistics($project, $request->headers->get('Authorization'), $request);

		return $this->setCreated("1.6.1", "Project", "projectcreation", "Complete Success", $project->objectToArray($em, $user));
	}

	private function grappSha1($str) // note : PLEASE DON'T REMOVE THAT FUNCTION! GOD DAMN IT!
	{
		return $str; //TODO : code the Grappbox sha-1 algorithm when assigned people ready
	}

	/**
	* @-api {put} /0.3/project/:id Update a project informations
	* @apiName updateInformations
	* @apiGroup Project
	* @apiDescription Update the given project informations
	* @apiVersion 0.3.0
	*
	*/
	public function updateInformationsAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.2.3", "Project", "updateinformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.2.4", "Project", "updateinformations", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $id, "projectSettings") < 2)
			return ($this->setNoRightsError("6.2.9", "Project", "updateinformations"));

		if (array_key_exists('creatorId', $content))
		{
			$creatorUser = $em->getRepository('MongoBundle:User')->find($content->creatorId);

			if ($creatorUser === null)
				return $this->setBadRequest("6.2.4", "Project", "updateinformations", "Bad Parameter: creatorId");

			$repository = $em->getRepository('MongoBundle:Role');

			$qb = $repository->createQueryBuilder()->field('name')->equals('Admin')->filed('projects.id')->equals($id);
			$role = $qb->getQuery()->execute();

			if (count($role) == 0)
				return $this->setBadRequest("6.2.1", "Project", "updateinformations", "Reading Error: role");
			else
				$role = $role[0];

			$repository = $em->getRepository('MongoBundle:ProjectUserRole');
			$creatorUserId = $project->getCreatorUser()->getId();
			$roleId = $role->getId();

			$qb = $repository->createQueryBuilder()->field('projectId')->equals($id)->field('userId')->equals($creatorUserId)->field('roleId')->equals($roleId);
			$ProjectUserRoles = $qb->getQuery()->execute();

			if (count($ProjectUserRoles) == 0)
				return $this->setBadRequest("6.2.1", "Project", "updateinformations", "Reading Error: project user role");
			else
				$ProjectUserRoles = $ProjectUserRoles[0];

			$ProjectUserRoles->setUserId($creatorUser->getId());

			$project->setCreatorUser($creatorUser);
		}
		if (array_key_exists('name', $content))
			$project->setName($content->name);
		if (array_key_exists('description', $content))
			$project->setDescription($content->description);
		if (array_key_exists('logo', $content))
		{
			$filepath = "/var/www/static/app/project/".$id;

			$file = base64_decode($content->logo);
			if ($file == false)
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Bad Parameter: logo");

			$image = imagecreatefromstring($file);
			if ($image == false)
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Bad Parameter: logo");

			if (!imagejpeg($image, $filepath, 80))
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Bad Parameter: logo");

			imagedestroy($image);

			$fileurl = 'https://static.grappbox.com/app/project/'.$id;

			$project->setLogo($fileurl);
			$project->setLogoDate(new \DateTime);

			$mdata['mtitle'] = "logo project";
			$mdata['mdesc'] = json_encode($project->objectToArray($em, $user));
			$wdata['type'] = "logo project";
			$wdata['targetId'] = $project->getId();
			$wdata['message'] = json_encode($project->objectToArray($em, $user));
			$userNotif = array();
			foreach ($project->getUsers() as $key => $value) {
				$userNotif[] = $value->getId();
			}
			if (count($userNotif) > 0)
				$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);
		}
		if (array_key_exists('phone', $content))
			$project->setPhone($content->phone);
		if (array_key_exists('company', $content))
			$project->setCompany($content->company);
		if (array_key_exists('email', $content))
			$project->setContactEmail($content->email);
		if (array_key_exists('facebook', $content))
			$project->setFacebook($content->facebook);
		if (array_key_exists('twitter', $content))
			$project->setTwitter($content->twitter);
		if (array_key_exists('password', $content))
		{
			if (!array_key_exists('oldPassword', $content))
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Missing Parameter");
			$passwordEncrypted = ($content->oldPassword ? $this->grappSha1($content->oldPassword) : NULL);
			if ($passwordEncrypted != $project->getSafePassword())
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Missing Parameter");
			$project->setSafePassword($content->password);
		}
		$em->flush();

		$mdata['mtitle'] = "update project";
		$mdata['mdesc'] = json_encode($project->objectToArray($em, $user));
		$wdata['type'] = "update project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = json_encode($project->objectToArray($em, $user));
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.6.1", "Project", "updateinformations", "Complete Success", $project->objectToArray($em, $user));
	}

	/**
	* @-api {get} /0.3/project/:id Get a project basic informations
	* @apiName getInformations
	* @apiGroup Project
	* @apiDescription Get the given project basic informations
	* @apiVersion 0.3.0
	*
	*/
	public function getInformationsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return $this->setBadTokenError("6.3.3", "Project", "getinformations");

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.3.4", "Project", "getinformations", "Bad Parameter: projectId");

		return $this->setSuccess("1.6.1", "Project", "getinformations", "Complete Success", $project->objectToArray($em, $user));
	}

	/**
	* @-api {delete} /0.3/project/:id Delete a project 7 days after the call
	* @apiName delProject
	* @apiGroup Project
	* @apiDescription Set the deleted at of the given project to 7 days after the call of the function
	* @apiVersion 0.3.0
	*
	*/
	public function delProjectAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.4.3", "Project", "delproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.4.4", "Project", "delproject", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $id, "projectSettings") < 2)
			return ($this->setNoRightsError("6.4.9", "Project", "delproject"));

		$delDate = new \DateTime;
		$delDate->add(new \DateInterval('P7D'));
		$project->setDeletedAt($delDate);

		$em->flush();

		$mdata['mtitle'] = "delete project";
		$mdata['mdesc'] = json_encode($project->objectToArray($em, $user));
		$wdata['type'] = "delete project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = json_encode($project->objectToArray($em, $user));
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - delproject - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {get} /0.3/project/retrieve/:projectId Retreive a project before the 7 days are passed, after delete
	* @apiName retrieveProject
	* @apiGroup Project
	* @apiDescription Retreive a project set to be deleted, but have to be called before the 7 days are passed
	* @apiVersion 0.3.0
	*
	*/
	public function retrieveProjectAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.5.3", "Project", "retrieveproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("6.5.4", "Project", "retrieveproject", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.5.9", "Project", "retrieveproject"));

		$project->setDeletedAt(null);
		$em->flush();

		$mdata['mtitle'] = "retrieve project";
		$mdata['mdesc'] = json_encode($project->objectToArray($em, $user));
		$wdata['type'] = "retrieve project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = json_encode($project->objectToArray($em, $user));
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - retrieveproject - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {post} /0.3/project/customeraccess Generate or Regenerate a customer access for a project
	* @apiName generateCustomerAccess
	* @apiGroup Project
	* @apiDescription Generate or regenerate a customer access for the given project
	* @apiVersion 0.3.0
	*
	*/
	public function generateCustomerAccessAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('name', $content))
			return $this->setBadRequest("6.6.6", "Project", "generatecustomeraccess", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.6.3", "Project", "generatecustomeraccess"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("6.6.4", "Project", "generatecustomeraccess", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.6.9", "Project", "generatecustomeraccess"));

		$repository = $em->getRepository('MongoBundle:CustomerAccess');

		$qb = $repository->createQueryBuilder()->field('name')->equals($content->name)->field('projects.id')->equals($content->projectId);
		$customerAccess = $qb->getQuery()->execute();

		if (count($customerAccess) == 0)
		{
			$customerAccess = new CustomerAccess();
			$customerAccess->setProjects($project);
		}
		else
		{
			$customerAccess = $customerAccess->getSingleResult();
			//$customerAccess = $customerAccess[0];
		}

		$customerAccess->setCreatedAt(new \DateTime);
		$customerAccess->setName($content->name);

		$tmpToken = random_bytes(25);
		$token = md5($tmpToken);

		$customerAccess->setHash($token);
		$em->persist($customerAccess);

		$project->addCustomersAccess($customerAccess);
		$em->flush();

		$mdata['mtitle'] = "new customeraccess";
		$mdata['mdesc'] = json_encode($customerAccess->objectToArray($em, $user));
		$wdata['type'] = "new customeraccess";
		$wdata['targetId'] = $customerAccess->getId();
		$wdata['message'] = json_encode($customerAccess->objectToArray($em, $user));
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.6.1", "Project", "generatecustomeraccess", "Complete Success", $customerAccess->objectToArray());
	}

	/**
	* @-api {get} /0.3/project/customeraccesses/:projectId Get a customer accesses by it's project
	* @apiName getCustomerAccessByProject
	* @apiGroup Project
	* @apiDescription Get a customer access by it's poject id
	* @apiVersion 0.3.0
	*
	*/
	public function getCustomerAccessByProjectAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.8.3", "Project", "getcustomeraccessbyproject"));

		$em = $this->get('doctrine_mongodb')->getManager();

		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("6.8.4", "Project", "getcustomeraccessbyproject", "Bad Parameter: projectId");

		$customerAccess = $project->getCustomersAccess();

		$arr = array();

		if (count($customerAccess) == 0)
			return $this->setNoDataSuccess("1.6.3", "Project", "getcustomeraccessbyproject");

		foreach ($customerAccess as $ca) {
			$arr[] = $ca->objectToArray();
		}

		return $this->setSuccess("1.6.1", "Project", "getcustomeraccessbyproject", "Complete Success", array("array" => $arr));
	}

	/**
	* @-api {delete} /0.3/project/customeraccess/:projectId/:customerAccessId Delete a customer access
	* @apiName delCustomerAccess
	* @apiGroup Project
	* @apiDescription Delete the given customer access
	* @apiVersion 0.3.0
	*
	*/
	public function delCustomerAccessAction(Request $request, $projectId, $customerAccessId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.9.3", "Project", "delcustomeraccess"));

		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.9.9", "Project", "delcustomeraccess"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$customerAccess = $em->getRepository('MongoBundle:CustomerAccess')->find($customerAccessId);

		if ($customerAccess === null)
			return $this->setBadRequest("6.9.4", "Project", "delcustomeraccess", "Bad Parameter: customerAccessId");

		$em->remove($customerAccess);
		$em->flush();

		$mdata['mtitle'] = "delete customeraccess";
		$mdata['mdesc'] = json_encode($customerAccess->objectToArray($em, $user));
		$wdata['type'] = "delete customeraccess";
		$wdata['targetId'] = $customerAccess->getId();
		$wdata['message'] = json_encode($customerAccess->objectToArray($em, $user));
		$userNotif = array();
		foreach ($customerAccess->getProjects()->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - delcustomeraccess - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {post} /0.3/project/user Add a user to a project
	* @apiName addUserToProject
	* @apiGroup Project
	* @apiDescription Add a given user to the project wanted
	* @apiVersion 0.3.0
	*
	*/
	public function addUserToProjectAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('id', $content) || !array_key_exists('email', $content))
			return $this->setBadRequest("6.10.6", "Project", "addusertoproject", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.10.3", "Project", "addusertoproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->id);
		if ($project === null)
			return $this->setBadRequest("6.10.4", "Project", "addusertoproject", "Bad Parameter: id");

		if ($this->checkRoles($user, $content->id, "projectSettings") < 2)
			return ($this->setNoRightsError("6.10.9", "Project", "addusertoproject"));

		$userToAdd = $em->getRepository('MongoBundle:User')->findOneByemail($content->email);
		if ($userToAdd === null)
			return $this->setBadRequest("6.10.4", "Project", "addusertoproject", "Bad Parameter: email");

		$users = $project->getUsers();
		foreach ($users as $user) {
			if ($user === $userToAdd)
				return $this->setBadRequest("6.10.7", "Project", "addusertoproject", "Already In Database");
		}

		$project->addUser($userToAdd);
		$em->flush();

		// Notifications
		$mdata['mtitle'] = "user assign project";
		$mdata['mdesc'] = json_encode(array("id" => $project->getId(), "user" => array("id" => $userToAdd->getId(), "firstname" => $userToAdd->getFirstname(), "lastname" => $userToAdd->getLastname(), "avatar" => $userToAdd->getAvatarDate())));
		$wdata['type'] = "user assign project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = json_encode(array("id" => $project->getId(), "user" => array("id" => $userToAdd->getId(), "firstname" => $userToAdd->getFirstname(), "lastname" => $userToAdd->getLastname())));
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.6.1", "Project", "addusertoproject", "Complete Success",
			array("id" => $userToAdd->getId(), "firstname" => $userToAdd->getFirstname(), "lastname" => $userToAdd->getLastname(), "avatar" => $userToAdd->getAvatarDate()));
	}

	/**
	* @-api {delete} /V0.3/project/userconnected/:projectId Remove the user connected from the project
	* @apiName removeUserConnected
	* @apiGroup Project
	* @apiDescription Remove the user connected from the project
	* @apiVersion 0.3.0
	*
	*/
	public function removeUserConnectedAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.11.3", "Project", "removeuserconnected"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("6.11.4", "Project", "removeuserconnected", "Bad Parameter: projectId");

		if ($user === $project->getCreatorUser())
			return $this->setBadRequest("6.11.4", "Project", "removeuserconnected", "Bad Parameter: You are the project creator, you can't be removed");

		$users = $project->getUsers();
		$isOnProject = false;
		foreach ($users as $u) {
			if ($u == $user)
				$isOnProject = true;
		}
		if ($isOnProject == false)
			return $this->setBadRequest("6.11.4", "Project", "removeuserconnected", "Bad Parameter: You are not on the project");

		$userRoleLink = $em->getRepository('MongoBundle:ProjectUserRole')->findBy(array('projectId'=> $project->getId(), 'userId' => $userId));
		foreach ($userRoleLink as $key => $userRole) {
			$em->remove($userRole);
			$em->flush();
		}

		$bugs = $em->getRepository('MongoBundle:Bug')->findBy(array('projects.id'=> $project->getId()));
		foreach ($bugs as $key => $bug) {
			$bug->removeUser($user);
		}

		$project->removeUser($user);
		$em->flush();

		// Notifications
		$mdata['mtitle'] = "user unassign project";
		$mdata['mdesc'] = json_encode(array("id" => $project->getId(), "user" => array("id" => $user->getId(), "firstname" => $user->getFirstname(), "lastname" => $user->getLastname(), "avatar" => $user->getAvatarDate())));
		$wdata['type'] = "user unassign project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = json_encode(array("id" => $project->getId(), "user" => array("id" => $user->getId(), "firstname" => $user->getFirstname(), "lastname" => $user->getLastname(), "avatar" => $user->getAvatarDate())));
		$userNotif = array();
		$userNotif[] = $user->getId();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - removeuserconnected - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {delete} /0.3/project/user/:projectId/:userId Remove a user from the project
	* @apiName removeUserToProject
	* @apiGroup Project
	* @apiDescription Remove a given user to the project wanted
	* @apiVersion 0.3.0
	*/
	public function removeUserToProjectAction(Request $request, $projectId, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.11.3", "Project", "removeusertoproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("6.11.4", "Project", "removeusertoproject", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.11.9", "Project", "removeusertoproject"));

		$userToRemove = $em->getRepository('MongoBundle:User')->find($userId);
		if ($userToRemove === null)
			return $this->setBadRequest("6.11.4", "Project", "removeusertoproject", "Bad Parameter: userId");

		if ($userToRemove === $project->getCreatorUser())
			return $this->setBadRequest("6.11.4", "Project", "removeusertoproject", "Bad Parameter: You can't remove the project creator");

		$users = $project->getUsers();
		$isOnProject = false;
		foreach ($users as $u) {
			if ($u == $userToRemove)
				$isOnProject = true;
		}
		if ($isOnProject == false)
			return $this->setBadRequest("6.11.4", "Project", "removeusertoproject", "Bad Parameter: userId");

		$userRoleLink = $em->getRepository('MongoBundle:ProjectUserRole')->findBy(array('projectId'=> $project->getId(), 'userId' => $userId));
		foreach ($userRoleLink as $key => $userRole) {
			$em->remove($userRole);
			$em->flush();
		}

		$bugs = $em->getRepository('MongoBundle:Bug')->findBy(array('projects'=> $project->getId()));
		foreach ($bugs as $key => $bug) {
			$bug->removeUser($userToRemove);
		}

		$project->removeUser($userToRemove);
		$em->flush();

		// Notifications
		$mdata['mtitle'] = "user unassign project";
		$mdata['mdesc'] = json_encode(array("id" => $project->getId(), "user" => array("id" => $userToRemove->getId(), "firstname" => $userToRemove->getFirstname(), "lastname" => $userToRemove->getLastname(), "avatar" => $userToRemove->getAvatarDate())));
		$wdata['type'] = "user unassign project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = json_encode(array("id" => $project->getId(), "user" => array("id" => $userToRemove->getId(), "firstname" => $userToRemove->getFirstname(), "lastname" => $userToRemove->getLastname(), "avatar" => $userToRemove->getAvatarDate())));
		$userNotif = array();
		$userNotif[] = $userToRemove->getId();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('mongo_service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - removeusertoproject - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {get} /0.3/project/users/:id Get all the users on a project
	* @apiName getUserToProject
	* @apiGroup Project
	* @apiDescription Get all the users on the given project
	* @apiVersion 0.3.0
	*
	*/
	public function getUserToProjectAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.12.3", "Project", "getusertoproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.12.4", "Project", "getusertoproject", "Bad Parameter: projectId");

		$arr = array();

		$users = $project->getUsers();

		if (count($users) == 0)
			return $this->setNoDataSuccess("1.6.3", "Project", "getusertoproject");

		foreach ($users as $user) {
			$id = $user->getId();
			$firstName = $user->getFirstname();
			$lastName = $user->getLastname();

			$arr[] = array("id" => $id, "firstname" => $firstName, "lastname" => $lastName);
		}

		return $this->setSuccess("1.6.1", "Project", "getusertoproject", "Complete Success", array("array" => $arr));
	}

	/**
	* @-api {put} /0.3/project/color/:id Change the color of a project
	* @apiName changeProjectColor
	* @apiGroup Project
	* @apiDescription Change the color of a project
	* @apiVersion 0.3.0
	*
	*/
	public function changeProjectColorAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('color', $content))
			return $this->setBadRequest("6.13.6", "Project", "changeprojectcolor", "Missing Parameter");

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.13.4", "Project", "changeprojectcolor", "Bad Parameter: projectId");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.13.3", "Project", "changeprojectcolor"));

		$color = $em->getRepository('MongoBundle:Color')->findOneBy(array("project.id" => $project->getId(), "user.id" => $user->getId()));
		if ($color === null)
		{
			$color = new Color();
			$color->setUser($user);
			$color->setProject($project);
			$em->persist($color);
		}

		$color->setColor($content->color);
		$em->flush();

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - changeprojectcolor - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {delete} /0.3/project/color/:id Reset the color of the project
	* @apiName resetProjectColor
	* @apiGroup Project
	* @apiDescription Reset the color of the given project to the default one
	* @apiVersion 0.3.0
	*
	*/
	public function resetProjectColorAction(Request $request, $projectId)
	{
		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("6.10.4", "Project", "resetprojectcolor", "Bad Parameter: projectId");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.10.3", "Project", "resetprojectcolor"));

		$color = $em->getRepository('MongoBundle:Color')->findOneBy(array("project.id" => $project->getId(), "user.id" => $user->getId()));
		if ($color === null)
			return $this->setBadRequest("6.10.4", "Project", "resetprojectcolor", "Bad Parameter: No color for the user");

		$em->remove($color);
		$em->flush();

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - resetprojectcolor - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {get} /0.3/project/logo/:id Get project logo
	* @apiName getProjectLogo
	* @apiGroup Project
	* @apiDescription Get the logo of the given project
	* @apiVersion 0.3.0
	*
	*/
	public function getProjectLogoAction(Request $request, $id)
	{
		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.15.4", "Project", "getProjectLogo", "Bad Parameter: projectId");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.15.3", "Project", "getProjectLogo"));

		return $this->setSuccess("1.6.1", "Project", "getProjectLogo", "Complete Success", array("logo" => $project->getLogo()));
	}
}
