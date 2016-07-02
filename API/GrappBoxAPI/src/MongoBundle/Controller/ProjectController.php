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
	* @api {post} /mongo/projects/projectcreation Create a project for the user connected
	* @apiName projectCreation
	* @apiGroup Project
	* @apiDescription Create a project for the user connected
	* @apiVersion 0.2.0
	*
	*/
	public function projectCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('name', $content) || !array_key_exists('token', $content) || !array_key_exists('password', $content))
			return $this->setBadRequest("6.1.6", "Project", "projectcreation", "Missing Parameter");

		$user = $this->checkToken($content->token);
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
		if (array_key_exists('logo', $content))
			$project->setLogo($content->logo);
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
		//$em->flush();

		$project->addUser($user);
		//$em->flush();

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
		//$em->flush();

		$pur = new ProjectUserRole();
		$pur->setProjectId($project->getId());
		$pur->setUserId($user->getId());
		$pur->setRoleId($role->getId());

		$em->persist($pur);
		//$em->flush();

		$qb = $em->getRepository('MongoBundle:Tag')->createQueryBuilder();
		$tags = $qb->getQuery()->execute();

		foreach ($tags as $t) {
			if ($t->getProject() === null)
			{
				$newTag = new Tag();
				$newTag->setName($t->getName());
				$newTag->setProject($project);

				$em->persist($newTag);
			}
		}

		//$em->flush();
		$id = $project->getId();

		$cloudClass = new CloudController();
		$cloudClass->createCloudAction($request, $id);

		$teamTimeline = new Timeline();
		$teamTimeline->setTypeId(2);
		$teamTimeline->setProjects($project);
		$teamTimeline->setProjectId($project->getId());
		$teamTimeline->setName("TeamTimeline - ".$project->getName());
		$em->persist($teamTimeline);
		//$em->flush();

		$customerTimeline = new Timeline();
		$customerTimeline->setTypeId(1);
		$customerTimeline->setProjects($project);
		$customerTimeline->setProjectId($project->getId());
		$customerTimeline->setName("CustomerTimeline - ".$project->getName());
		$em->persist($customerTimeline);
		$em->flush();

		//$this->get('service_stat')->initiateStatistics($project);

		return $this->setCreated("1.6.1", "Project", "projectcreation", "Complete Success", array("id" => $id));
	}

	private function grappSha1($str) // note : PLEASE DON'T REMOVE THAT FUNCTION! GOD DAMN IT!
	{
		return $str; //TODO : code the Grappbox sha-1 algorithm when assigned people ready
	}

	/**
	* @api {put} /mongo/projects/updateinformations Update a project informations
	* @apiName updateInformations
	* @apiGroup Project
	* @apiDescription Update the given project informations
	* @apiVersion 0.2.0
	*
	*/
	public function updateInformationsAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.2.3", "Project", "updateinformations"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.2.9", "Project", "updateinformations"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
			return $this->setBadRequest("6.2.4", "Project", "updateinformations", "Bad Parameter: projectId");

		if (array_key_exists('creatorId', $content))
		{
			$creatorUser = $em->getRepository('MongoBundle:User')->find($content->creatorId);

			if ($creatorUser === null)
				return $this->setBadRequest("6.2.4", "Project", "updateinformations", "Bad Parameter: creatorId");

			$repository = $em->getRepository('MongoBundle:Role');

			$qb = $repository->createQueryBuilder()->field('name')->equals('Admin')->filed('projects.id')->equals($content->projectId);
			$role = $qb->getQuery()->execute();

			if (count($role) == 0)
				return $this->setBadRequest("6.2.1", "Project", "updateinformations", "Reading Error: role");
			else
				$role = $role[0];

			$repository = $em->getRepository('MongoBundle:ProjectUserRole');
			$creatorUserId = $project->getCreatorUser()->getId();
			$roleId = $role->getId();

			$qb = $repository->createQueryBuilder()->field('projectId')->equals($content->projectId)->field('userId')->equals($creatorUserId)->field('roleId')->equals($roleId);
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
			$project->setLogo($content->logo);
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

		return $this->setSuccess("1.6.1", "Project", "updateinformations", "Complete Success", array("id" => $project->getId()));
	}

	/**
	* @api {get} /mongo/projects/getinformations/:token/:projectId Get a project basic informations
	* @apiName getInformations
	* @apiGroup Project
	* @apiDescription Get the given project basic informations
	* @apiVersion 0.2.0
	*
	*/
	public function getInformationsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return $this->setBadTokenError("6.3.3", "Project", "getinformations");

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);

		if ($project === null)
			return $this->setBadRequest("6.3.4", "Project", "getinformations", "Bad Parameter: projectId");

		$name = $project->getName();
		$description = $project->getDescription();
		$logo = $project->getLogo();
		$phone = $project->getPhone();
		$company = $project->getCompany();
		$contactMail = $project->getContactEmail();
		$facebook = $project->getFacebook();
		$twitter = $project->getTwitter();
		$color = $em->getRepository('MongoBundle:Color')->findOneBy(array("project.id" => $project->getId(), "user.id" => $user->getId()));
		if ($color === null)
			$color = $project->getColor();
		else
			$color = $color->getColor();
		$creation = $project->getCreatedAt();
		$deletedAt = $project->getDeletedAt();

		return $this->setSuccess("1.6.1", "Project", "getinformations", "Complete Success", array("name" => $name, "description" => $description, "logo" => $logo, "phone" => $phone,
			"company" => $company , "contact_mail" => $contactMail, "facebook" => $facebook, "twitter" => $twitter, "color" => $color, "creation_date" => $creation, "deleted_at" => $deletedAt));
	}

	/**
	* @api {delete} /mongo/projects/delproject/:token/:projectId Delete a project 7 days after the call
	* @apiName delProject
	* @apiGroup Project
	* @apiDescription Set the deleted at of the given project to 7 days after the call of the function
	* @apiVersion 0.2.0
	*
	*/
	public function delProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.4.3", "Project", "delproject"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.4.9", "Project", "delproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
			return $this->setBadRequest("6.4.4", "Project", "delproject", "Bad Parameter: projectId");

		$delDate = new \DateTime;
		$delDate->add(new \DateInterval('P7D'));
		$project->setDeletedAt($delDate);

		$em->flush();

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - delproject - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /mongo/projects/retrieveproject/:token/:projectId Retreive a project before the 7 days are passed, after delete
	* @apiName retrieveProject
	* @apiGroup Project
	* @apiDescription Retreive a project set to be deleted, but have to be called before the 7 days are passed
	* @apiVersion 0.2.0
	*
	*/
	public function retrieveProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.5.3", "Project", "retrieveproject"));

		if (!$this->checkRoles($user, $projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.5.9", "Project", "retrieveproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);

		if ($project === null)
			return $this->setBadRequest("6.5.4", "Project", "retrieveproject", "Bad Parameter: projectId");

		$project->setDeletedAt(null);
		$em->flush();

		return $this->setSuccess("1.6.1", "Project", "retrieveproject", "Complete Success", array("id" => $projectId));
	}

	/**
	* @api {post} /mongo/projects/generatecustomeraccess Generate or Regenerate a customer access for a project
	* @apiName generateCustomerAccess
	* @apiGroup Project
	* @apiDescription Generate or regenerate a customer access for the given project
	* @apiVersion 0.2.0
	*
	*/
	public function generateCustomerAccessAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('name', $content))
			return $this->setBadRequest("6.6.6", "Project", "generatecustomeraccess", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.6.3", "Project", "generatecustomeraccess"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.6.9", "Project", "generatecustomeraccess"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
			return $this->setBadRequest("6.6.4", "Project", "generatecustomeraccess", "Bad Parameter: projectId");

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
			$customerAccess = $customerAccess[0];
		}

		$customerAccess->setCreatedAt(new \DateTime);
		$customerAccess->setName($content->name);

		$secureUtils = $this->get('security.secure_random');
		$tmpToken = $secureUtils->nextBytes(25);
		$token = md5($tmpToken);

		$customerAccess->setHash($token);
		$em->persist($customerAccess);

		$project->addCustomersAccess($customerAccess);
		$em->flush();

		return $this->setSuccess("1.6.1", "Project", "generatecustomeraccess", "Complete Success", array("id" => $customerAccess->getId()));
	}

	/**
	* @api {get} /mongo/projects/getcustomeraccessbyid/:token/:id Get a customer access by it's id
	* @apiName getCustomerAccessById
	* @apiGroup Project
	* @apiDescription Get a customer access by it's id
	* @apiVersion 0.2.0
	*
	*/
	public function getCustomerAccessByIdAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.7.3", "Project", "getcustomeraccessbyid"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$customerAccess = $em->getRepository('MongoBundle:CustomerAccess')->find($id);

		if ($customerAccess === null)
			return $this->setBadRequest("6.7.4", "Project", "getcustomeraccessbyid", "Bad Parameter: id");

		$name = $customerAccess->getName();
		$hash = $customerAccess->getHash();
		$createdAt = $customerAccess->getCreatedAt();
		$project = $customerAccess->getProjects()->getId();

		return $this->setSuccess("1.6.1", "Project", "getcustomeraccessbyid", "Complete Success", array("customer_token" => $hash, "project_id" => $project, "name" => $name, "creation_date" => $createdAt));
	}

	/**
	* @api {get} /mongo/projects/getcustomeraccessbyproject/:token/:projectId Get a customer accesses by it's project
	* @apiName getCustomerAccessByProject
	* @apiGroup Project
	* @apiDescription Get a customer access by it's poject id
	* @apiVersion 0.2.0
	*
	*/
	public function getCustomerAccessByProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.8.3", "Project", "getcustomeraccessbyproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$customerAccess = $em->getRepository('MongoBundle:CustomerAccess')->findByprojects($projectId);
		if ($customerAccess === null)
			return $this->setBadRequest("6.8.4", "Project", "getcustomeraccessbyproject", "Bad Parameter: projectId");

		$arr = array();

		if (count($customerAccess) == 0)
			return $this->setNoDataSuccess("1.6.3", "Project", "getcustomeraccessbyproject");

		foreach ($customerAccess as $ca) {
			$id = $ca->getId();
			$name = $ca->getName();
			$hash = $ca->getHash();
			$createdAt = $ca->getCreatedAt();

			$arr[] = array("name" => $name, "customer_token" => $hash, "id" => $id, "creation_date" => $createdAt);
		}

		return $this->setSuccess("1.6.1", "Project", "getcustomeraccessbyproject", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {delete} /mongo/projects/delcustomeraccess/:token/:projectId/:customerAccessId Delete a customer access
	* @apiName delCustomerAccess
	* @apiGroup Project
	* @apiDescription Delete the given customer access
	* @apiVersion 0.2.0
	*
	*/
	public function delCustomerAccessAction(Request $request, $token, $projectId, $customerAccessId)
	{
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.9.3", "Project", "delcustomeraccess"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.9.9", "Project", "delcustomeraccess"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$customerAccess = $em->getRepository('MongoBundle:CustomerAccess')->find($content->customerAccessId);

		if ($customerAccess === null)
			return $this->setBadRequest("6.9.4", "Project", "delcustomeraccess", "Bad Parameter: customerAccessId");

		$em->remove($customerAccess);
		$em->flush();

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - delcustomeraccess - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {post} /mongo/projects/addusertoproject Add a user to a project
	* @apiName addUserToProject
	* @apiGroup Project
	* @apiDescription Add a given user to the project wanted
	* @apiVersion 0.2.0
	*
	*/
	public function addUserToProjectAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('id', $content) || !array_key_exists('token', $content) || !array_key_exists('email', $content))
			return $this->setBadRequest("6.10.6", "Project", "addusertoproject", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.10.3", "Project", "addusertoproject"));

		if (!$this->checkRoles($user, $content->id, "projectSettings") < 2)
			return ($this->setNoRightsError("6.10.9", "Project", "addusertoproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->id);

		if ($project === null)
			return $this->setBadRequest("6.10.4", "Project", "addusertoproject", "Bad Parameter: id");

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
		$class = new NotificationController();

		$mdata['mtitle'] = "Project - Add";
		$mdata['mdesc'] = "You have been added on the project ".$project->getName();

		$wdata['type'] = "Project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = "You have been added on the project ".$project->getName();

		$userNotif[] = $userToAdd->getId();

		$class->pushNotification($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.6.1", "Project", "addusertoproject", "Complete Success",
			array("id" => $userToAdd->getId(), "firstname" => $userToAdd->getFirstname(), "lastname" => $userToAdd->getLastname(), "avatar" => $userToAdd->getAvatar()));
	}

	/**
	* @api {delete} /mongo/projects/removeusertoproject/:token/:projectId/:userId Remove a user from the project
	* @apiName removeUserToProject
	* @apiGroup Project
	* @apiDescription Remove a given user to the project wanted
	* @apiVersion 0.2.0
	*
	*/
	public function removeUserToProjectAction(Request $request, $token, $projectId, $userId)
	{
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.11.3", "Project", "removeusertoproject"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.11.9", "Project", "removeusertoproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
			return $this->setBadRequest("6.11.4", "Project", "removeusertoproject", "Bad Parameter: projectId");

		$userToRemove = $em->getRepository('MongoBundle:User')->find($content->userId);
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

		$project->removeUser($userToRemove);
		$em->flush();

		// Notifications
		$class = new NotificationController();

		$mdata['mtitle'] = "Project - Remove";
		$mdata['mdesc'] = "You have been removed from the project ".$project->getName();

		$wdata['type'] = "Project";
		$wdata['targetId'] = $project->getId();
		$wdata['message'] = "You have been removed from the project ".$project->getName();

		$userNotif[] = $userToRemove->getId();

		$class->pushNotification($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - removeusertoproject - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /mongo/projects/getusertoproject/:token/:projectId Get all the users on a project
	* @apiName getUserToProject
	* @apiGroup Project
	* @apiDescription Get all the users on the given project
	* @apiVersion 0.2.0
	*
	*/
	public function getUserToProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.12.3", "Project", "getusertoproject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);

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
	* @api {put} /mongo/projects/changeprojectcolor Change the color of a project
	* @apiName changeProjectColor
	* @apiGroup Project
	* @apiDescription Change the color of a project
	* @apiVersion 0.2.0
	*
	*/
	public function changeProjectColorAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('color', $content))
			return $this->setBadRequest("6.13.6", "Project", "changeprojectcolor", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.13.3", "Project", "changeprojectcolor"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
			return $this->setBadRequest("6.13.4", "Project", "changeprojectcolor", "Bad Parameter: projectId");

		$color = $em->getRepository('MongoBundle:Color')->findOneBy(array("project" => $project, "user" => $user));
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
	* @api {delete} /mongo/projects/resetprojectcolor/:token/:projectId Reset the color of the project
	* @apiName resetProjectColor
	* @apiGroup Project
	* @apiDescription Reset the color of the given project to the default one
	* @apiVersion 0.2.0
	*
	*/
	public function resetProjectColorAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.10.3", "Project", "resetprojectcolor"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);

		if ($project === null)
			return $this->setBadRequest("6.10.4", "Project", "resetprojectcolor", "Bad Parameter: projectId");

		$color = $em->getRepository('MongoBundle:Color')->findOneBy(array("project" => $project, "user" => $user));
		if ($color === null)
			return $this->setBadRequest("6.10.4", "Project", "resetprojectcolor", "Bad Parameter: No color for the user");

		$em->remove($color);
		$em->flush();

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - resetprojectcolor - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /mongo/projects/getprojectlogo/:token/:projectId Get project logo
	* @apiName getProjectLogo
	* @apiGroup Project
	* @apiDescription Get the logo of the given project
	* @apiVersion 0.2.0
	*
	*/
	public function getProjectLogoAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.15.3", "Project", "getProjectLogo"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);

		if ($project === null)
			return $this->setBadRequest("6.15.4", "Project", "getProjectLogo", "Bad Parameter: projectId");

		return $this->setSuccess("1.6.1", "Project", "getProjectLogo", "Complete Success", array("logo" => $project->getLogo()));
	}
}
