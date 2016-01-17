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
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} name Name of the project
	* @apiParam {String} [description] Description of the project
	* @apiParam {Text} [logo] Logo of the project
	* @apiParam {String} [phone] Phone for the project
	* @apiParam {String} [company] Company of the project
	* @apiParam {String} [email] Email for the project
	* @apiParam {String} [facebook] Facebook of the project
	* @apiParam {String} [twitter] Twitter of the person
	* @apiParam {String} [password] Safe password for the project
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"name": "Grappbox",
	*			"description": "grappbox est un projet de gestion de projets",
	*			"logo": "10001111001100110010101010",
	*			"phone": "+335 65 23 45 94",
	*			"company": "L'oie oppressée",
	*			"email": "contact@oieoppresee.com",
	*			"facebook": "www.facebook.com/OieOpp",
	*			"twitter": "www.twitter.com/OieOpp",
	*			"password": "yolo42"
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"name": "Grappbox"
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"name": "Grappbox",
	*			"description": "grappbox est un projet de gestion de projets",
	*			"phone": "+335 65 23 45 94",
	*			"company": "L'oie oppressée",
	*			"email": "contact@oieoppresee.com",
	*			"password": "yolo42"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the project created
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 201 Created
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - projectcreation - Complete Success"
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
	*			"return_code": "6.1.3",
	*			"return_message": "Project - projectcreation - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.1.6",
	*			"return_message": "Project - projectcreation - Missing Parameter"
	*		}
	*	}
	*/
	public function projectCreationAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('name', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("6.1.6", "Project", "projectcreation", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return $this->setBadTokenError("6.1.3", "Project", "projectcreation");

		$em = $this->getDoctrine()->getManager();

		$project = new Project();
		$project->setName($content->name);
		$project->setCreatedAt(new \DateTime);
		$project->setCreatorUser($user);
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
			$encoder = $this->container->get('security.password_encoder');
			$encoded = $encoder->encodePassword($user, $content->password);
			$project->setSafePassword($encoded);
		}

		$em->persist($project);

		$project->addUser($user);

		$role = new Role();
		$role->setName("Admin");
		$role->setTeamTimeline(1);
		$role->setCustomerTimeline(1);
		$role->setGantt(1);
		$role->setWhiteboard(1);
		$role->setBugtracker(1);
		$role->setEvent(1);
		$role->setTask(1);
		$role->setProjectSettings(1);
		$role->setCloud(1);
		$role->setProjects($project);

		$em->persist($role);
		$em->flush();

		$pur = new ProjectUserRole();
		$pur->setProjectId($project->getId());
		$pur->setUserId($user->getId());
		$pur->setRoleId($role->getId());

		$em->persist($pur);

		$qb = $em->getRepository('MongoBundle:Tag')->createQueryBuilder('t')->getQuery();
		$tags = $qb->getResult();

		foreach ($tags as $t) {
			if ($t->getProject() === null)
			{
				$newTag = new Tag();
				$newTag->setName($t->getName());
				$newTag->setProject($project);

				$em->persist($newTag);
			}
		}

		$em->flush();
		$id = $project->getId();

		$cloudClass = new CloudController();
		$cloudClass->createCloudAction($request, $id);

		$teamTimeline = new Timeline();
		$teamTimeline->setTypeId(2);
		$teamTimeline->setProjects($project);
		$teamTimeline->setProjectId($project->getId());
		$teamTimeline->setName("TeamTimeline - ".$project->getName());
		$em->persist($teamTimeline);

		$customerTimeline = new Timeline();
		$customerTimeline->setTypeId(1);
		$customerTimeline->setProjects($project);
		$customerTimeline->setProjectId($project->getId());
		$customerTimeline->setName("CustomerTimeline - ".$project->getName());
		$em->persist($customerTimeline);
		$em->flush();

		return $this->setCreated("1.6.1", "Project", "projectcreation", "Complete Success", array("id" => $id));
	}

	/**
	* @api {put} /mongo/projects/updateinformations Update a project informations
	* @apiName updateInformations
	* @apiGroup Project
	* @apiDescription Update the given project informations
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} [creatorId] Id of the new creator
	* @apiParam {String} [name] name of the project
	* @apiParam {String} [description] Description of the project
	* @apiParam {Text} [logo] Logo of the project
	* @apiParam {String} [phone] Phone for the project
	* @apiParam {String} [company] Company of the project
	* @apiParam {String} [email] Email for the project
	* @apiParam {String} [facebook] Facebook of the project
	* @apiParam {String} [twitter] Twitter of the person
	* @apiParam {String} [password] Safe password for the project
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"projectId": 2,
	*			"creatorId": 18,
	*			"name": "Grappbox",
	*			"description": "grappbox est un projet de gestion de projets",
	*			"logo": "10001111001100110010101010",
	*			"phone": "+335 65 23 45 94",
	*			"company": "L'oie oppressée",
	*			"email": "contact@oieoppresee.com",
	*			"facebook": "www.facebook.com/OieOpp",
	*			"twitter": "www.twitter.com/OieOpp",
	*			"password": "yolo42"
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Minimum-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"projectId": 2
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"token": "13135",
	*			"projectId": 2,
	*			"description": "grappbox est un projet de gestion de projets",
	*			"logo": "10001111001100110010101010",
	*			"phone": "+335 65 23 45 94",
	*			"twitter": "www.twitter.com/OieOpp"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the project updated
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - updateinformations - Complete Success"
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
	*			"return_code": "6.2.3",
	*			"return_message": "Project - updateinformations - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.2.6",
	*			"return_message": "Project - updateinformations - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.2.9",
	*			"return_message": "Project - updateinformations - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.2.4",
	*			"return_message": "Project - updateinformations - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Reading Error: role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.2.1",
	*			"return_message": "Project - updateinformations - Reading Error: role"
	*		}
	*	}
	* @apiErrorExample Reading Error: project user role
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.2.1",
	*			"return_message": "Project - updateinformations - Reading Error: project user role"
	*		}
	*	}
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

		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError("6.2.9", "Project", "updateinformations"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
			return $this->setBadRequest("6.2.4", "Project", "updateinformations", "Bad Parameter: projectId");

		if (array_key_exists('creatorId', $content))
		{
			$creatorUser = $em->getRepository('MongoBundle:User')->find($content->creatorId);

			if ($creatorUser === null)
				return $this->setBadRequest("6.2.4", "Project", "updateinformations", "Bad Parameter: creatorId");

			$repository = $em->getRepository('MongoBundle:Role');

			$qb = $repository->createQueryBuilder('r')->join('r.projects', 'p')->where('r.name = :name', 'p.id = :id')->setParameter('name', "Admin")->setParameter('id', $content->projectId)->getQuery();
			$role = $qb->getResult();

			if (count($role) == 0)
				return $this->setBadRequest("6.2.1", "Project", "updateinformations", "Reading Error: role");
			else
				$role = $role[0];

			$repository = $em->getRepository('MongoBundle:ProjectUserRole');
			$creatorUserId = $project->getCreatorUser()->getId();
			$roleId = $role->getId();

			$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
			->setParameter('projectId', $content->projectId)->setParameter('userId', $creatorUserId)->setParameter('roleId', $roleId)->getQuery();
			$ProjectUserRoles = $qb->getResult();

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
			$encoder = $this->container->get('security.password_encoder');
			$encoded = $encoder->encodePassword($user, $content->password);
			$project->setSafePassword($encoded);
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
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {String} name Name of the project
	* @apiSuccess {String} description Description of the project
	* @apiSuccess {Text} logo Logo of the project
	* @apiSuccess {String} phone Phone number of the project
	* @apiSuccess {String} company Company name
	* @apiSuccess {String} contact_mail for the project
	* @apiSuccess {String} facebook Facebook for the project
	* @apiSuccess {String} twitter Twitter for the project
	* @apiSuccess {Datetime} creation_date Date of creation of the project
	* @apiSuccess {Datetime} deleted_at Date when the project will be deleted
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - getinformations - Complete Success"
	*		},
	*		"data":
	*		{
	*			"name": "Grappbox",
	*			"description": "Grappbox est un projet",
	*			"logo": "10100011000011001",
	*			"phone": "+89130 2145 8795",
	*			"company": "L'oie Oppressée",
	*			"contact_mail": "contact@grappbox.com",
	*			"facebook": "https://facebook.com/Grappbox",
	*			"twitter": "https://twitter.com/Grappbox",
	*			"creation_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			},
	*			"deleted_at": null
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.3.3",
	*			"return_message": "Project - getinformations - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.3.4",
	*			"return_message": "Project - getinformations - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.3.9",
	*			"return_message": "Project - getinformations - Insufficient Rights"
	*		}
	*	}
	*/
	public function getInformationsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return $this->setBadTokenError("6.3.3", "Project", "getinformations");

		if (!$this->checkRoles($user, $projectId, "projectSettings"))
			return ($this->setNoRightsError("6.3.9", "Project", "getinformations"));

		$em = $this->getDoctrine()->getManager();
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
		$creation = $project->getCreatedAt();
		$deletedAt = $project->getDeletedAt();

		return $this->setSuccess("1.6.1", "Project", "getinformations", "Complete Success", array("name" => $name, "description" => $description, "logo" => $logo, "phone" => $phone,
			"company" => $company , "contact_mail" => $contactMail, "facebook" => $facebook, "twitter" => $twitter, "creation_date" => $creation, "deleted_at" => $deletedAt));
	}

	/**
	* @api {delete} /mongo/projects/delproject Delete a project 7 days after the call
	* @apiName delProject
	* @apiGroup Project
	* @apiDescription Set the deleted at of the given project to 7 days after the call of the function
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"projectId": 1
	*		}
	*	}
	*
	* @apiSuccess {DateTime} deletion_date Date of deletion of the project
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - delproject - Complete Success"
	*		},
	*		"data":
	*		{
	*			"deletion_date": {
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.4.3",
	*			"return_message": "Project - delproject - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.4.6",
	*			"return_message": "Project - delproject - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.4.9",
	*			"return_message": "Project - delproject - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.4.4",
	*			"return_message": "Project - delproject - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function delProjectAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("6.4.6", "Project", "delproject", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.4.3", "Project", "delproject"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError("6.4.9", "Project", "delproject"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
			return $this->setBadRequest("6.4.4", "Project", "delproject", "Bad Parameter: projectId");

		$delDate = new \DateTime;
		$delDate->add(new \DateInterval('P7D'));
		$project->setDeletedAt($delDate);

		$em->flush();

		return $this->setSuccess("1.6.1", "Project", "delproject", "Complete Success", array("deletion_date" => $project->getDeletedAt()));
	}

	/**
	* @api {get} /mongo/projects/retrieveproject/:token/:projectId Retreive a project before the 7 days are passed, after delete
	* @apiName retrieveProject
	* @apiGroup Project
	* @apiDescription Retreive a project set to be deleted, but have to be called before the 7 days are passed
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Number} id Id of the project retrieve
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - retrieveproject - Complete Success"
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
	*			"return_code": "6.5.3",
	*			"return_message": "Project - retrieveproject - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.5.9",
	*			"return_message": "Project - retrieveproject - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.5.4",
	*			"return_message": "Project - retrieveproject - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function retrieveProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.5.3", "Project", "retrieveproject"));

		if (!$this->checkRoles($user, $projectId, "projectSettings"))
			return ($this->setNoRightsError("6.5.9", "Project", "retrieveproject"));

		$em = $this->getDoctrine()->getManager();
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
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} projectId Id of the project
	* @apiParam {String} name Name of the customer access
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "13cqs43c54vqd3",
	*			"projectId": 2,
	*			"name": "access for Toyota"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the customer access
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - generatecustomeraccess - Complete Success"
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
	*			"return_code": "6.6.3",
	*			"return_message": "Project - generatecustomeraccess - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.6.6",
	*			"return_message": "Project - generatecustomeraccess - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.6.9",
	*			"return_message": "Project - generatecustomeraccess - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.6.4",
	*			"return_message": "Project - generatecustomeraccess - Bad Parameter: projectId"
	*		}
	*	}
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

		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError("6.6.9", "Project", "generatecustomeraccess"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($content->projectId);

		if ($project === null)
			return $this->setBadRequest("6.6.4", "Project", "generatecustomeraccess", "Bad Parameter: projectId");

		$repository = $em->getRepository('MongoBundle:CustomerAccess');

		$qb = $repository->createQueryBuilder('ca')->join('ca.projects', 'p')->where('ca.name = :name', 'p.id = :id')->setParameter('name', $content->name)->setParameter('id', $content->projectId)->getQuery();
		$customerAccess = $qb->getResult();

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
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the customer access
	*
	* @apiSuccess {String} customer_token Customer access token
	* @apiSuccess {Number} project_id Id of the project
	* @apiSuccess {String} name Name of the customer access
	* @apiSuccess {Datetime} creation_date Date of creation of the customer access
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - getcustomeraccessbyid - Complete Success"
	*		},
	*		"data": {
	*			"customer_token": "dizjflqfq41c645w",
	*			"project_id": 2,
	*			"name": "access for X company",
	*			"creation_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.7.3",
	*			"return_message": "Project - getcustomeraccessbyid - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.7.4",
	*			"return_message": "Project - getcustomeraccessbyid - Bad Parameter: id"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.7.9",
	*			"return_message": "Project - getcustomeraccessbyid - Insufficient Rights"
	*		}
	*	}
	*/
	public function getCustomerAccessByIdAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.7.3", "Project", "getcustomeraccessbyid"));

		$em = $this->getDoctrine()->getManager();
		$customerAccess = $em->getRepository('MongoBundle:CustomerAccess')->find($id);

		if ($customerAccess === null)
			return $this->setBadRequest("6.7.4", "Project", "getcustomeraccessbyid", "Bad Parameter: id");

		if (!$this->checkRoles($user, $customerAccess->getProjects()->getId(), "projectSettings"))
			return ($this->setNoRightsError("6.7.9", "Project", "getcustomeraccessbyid"));

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
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} array Array of customer access
	* @apiSuccess {String} array.name Name of the customer access
	* @apiSuccess {String} array.customer_token Customer access token
	* @apiSuccess {Number} array.id Id of the customer access
	* @apiSuccess {Datetime} array.creation_date Date of creation of the customer access
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - getcustomeraccessbyproject - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"name": "access for client X",
	*					"customer_token": "dizjflqfq41c645w",
	*					"id": 2,
	*					"creation_date": {
	*						"date":"2015-10-15 11:00:00",
	*						"timezone_type":3,
	*						"timezone":"Europe\/Paris"
	*					}
	*				}
	*			]
	*		}
	*	}
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.6.3",
	*			"return_message": "Project - getcustomeraccessbyproject - No Data Success"
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
	*			"return_code": "6.8.3",
	*			"return_message": "Project - getcustomeraccessbyproject - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.8.4",
	*			"return_message": "Project - getcustomeraccessbyproject - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.8.9",
	*			"return_message": "Project - getcustomeraccessbyproject - Insufficient Rights"
	*		}
	*	}
	*/
	public function getCustomerAccessByProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.8.3", "Project", "getcustomeraccessbyproject"));

		if (!$this->checkRoles($user, $projectId, "projectSettings"))
			return ($this->setNoRightsError("6.8.9", "Project", "getcustomeraccessbyproject"));

		$em = $this->getDoctrine()->getManager();
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
	* @api {delete} /mongo/projects/delcustomeraccess Delete a customer access
	* @apiName delCustomerAccess
	* @apiGroup Project
	* @apiDescription Delete the given customer access
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} customerAccessId Id of the customer access
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"projectId": 1,
	*			"customerAccessId": 3
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the customer access deleted
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - delcustomeraccess - Complete Success"
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
	*			"return_code": "6.9.3",
	*			"return_message": "Project - delcustomeraccess - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.9.6",
	*			"return_message": "Project - delcustomeraccess - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.9.9",
	*			"return_message": "Project - delcustomeraccess - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: customerAccessId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.9.4",
	*			"return_message": "Project - delcustomeraccess - Bad Parameter: customerAccessId"
	*		}
	*	}
	*/
	public function delCustomerAccessAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('customerAccessId', $content))
			return $this->setBadRequest("6.9.6", "Project", "delcustomeraccess", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.9.3", "Project", "delcustomeraccess"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError("6.9.9", "Project", "delcustomeraccess"));

		$em = $this->getDoctrine()->getManager();
		$customerAccess = $em->getRepository('MongoBundle:CustomerAccess')->find($content->customerAccessId);

		if ($customerAccess === null)
			return $this->setBadRequest("6.9.4", "Project", "delcustomeraccess", "Bad Parameter: customerAccessId");

		$em->remove($customerAccess);
		$em->flush();

		return $this->setSuccess("1.6.1", "Project", "delcustomeraccess", "Complete Success", array("id" => $content->customerAccessId));
	}

	/**
	* @api {post} /mongo/projects/addusertoproject Add a user to a project
	* @apiName addUserToProject
	* @apiGroup Project
	* @apiDescription Add a given user to the project wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} id Id of the project
	* @apiParam {String} email Email of the user
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "nfeq34efbfkqf54",
	*			"id": 2,
	*			"email": "toto@titi.com"
	*		}
	*	}
	*
	* @apiSuccess id Id of the user add
	* @apiSuccess firstname First name of the user add
	* @apiSuccess lastname Last name of the user add
	* @apiSuccess avatar Avatar of the user add
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - addusertoproject - Complete Success"
	*		},
	*		"data":
	*		{
	*			"id": 1
	*			"firstname": "john",
	*			"lastname": "doe",
	*			"avatar": "DATA"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.10.3",
	*			"return_message": "Project - addusertoproject - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.10.6",
	*			"return_message": "Project - addusertoproject - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.10.9",
	*			"return_message": "Project - addusertoproject - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.10.4",
	*			"return_message": "Project - addusertoproject - Bad Parameter: id"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: email
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.10.4",
	*			"return_message": "Project - addusertoproject - Bad Parameter: email"
	*		}
	*	}
	* @apiErrorExample Already In Database
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.10.7",
	*			"return_message": "Project - addusertoproject - Already In Database"
	*		}
	*	}
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

		if (!$this->checkRoles($user, $content->id, "projectSettings"))
			return ($this->setNoRightsError("6.10.9", "Project", "addusertoproject"));

		$em = $this->getDoctrine()->getManager();
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
	* @api {delete} /mongo/projects/removeusertoproject Remove a user from the project
	* @apiName removeUserToProject
	* @apiGroup Project
	* @apiDescription Remove a given user to the project wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} userId Id of the user
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "aeqf231ced651qcd",
	*			"projectId": 1,
	*			"userId": 3
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the user removed
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - removeusertoproject - Complete Success"
	*		},
	*		"data": {
	*			"id": 18
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.11.3",
	*			"return_message": "Project - removeusertoproject - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.11.6",
	*			"return_message": "Project - removeusertoproject - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.11.9",
	*			"return_message": "Project - removeusertoproject - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.11.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.11.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: userId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You can't remove the project creator
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.11.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: You can't remove the project creator"
	*		}
	*	}
	*/
	public function removeUserToProjectAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('token', $content) || !array_key_exists('userId', $content))
			return $this->setBadRequest("6.11.6", "Project", "removeusertoproject", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("6.11.3", "Project", "removeusertoproject"));

		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError("6.11.9", "Project", "removeusertoproject"));

		$em = $this->getDoctrine()->getManager();
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

		return $this->setSuccess("1.6.1", "Project", "removeusertoproject", "Complete Success", array("id" => $userToRemove->getId()));
	}

	/**
	* @api {get} /mongo/projects/getusertoproject/:token/:projectId Get all the users on a project
	* @apiName getUserToProject
	* @apiGroup Project
	* @apiDescription Get all the users on the given project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Object[]} array Array of users
	* @apiSuccess {Number} id Id of the user
	* @apiSuccess {String} firstname First name of the user
	* @apiSuccess {String} lastname Last name of the user
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - getusertoproject - Complete Success"
	*		},
	*		"data": {
	*			"array": [
	*				{
	*					"id": 3,
	*					"first_name": "John",
	*					"last_name": "Doe"
	*				}
	*			]
	*		}
	*	}
	* @apiSuccessExample Success-No Data
	*	HTTP/1.1 201 Partial Content
	*	{
	*		"info": {
	*			"return_code": "1.6.3",
	*			"return_message": "Project - getusertoproject - No Data Success"
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
	*			"return_code": "6.12.3",
	*			"return_message": "Project - getusertoproject - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.12.4",
	*			"return_message": "Project - getusertoproject - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.12.9",
	*			"return_message": "Project - getusertoproject - Insufficient Rights"
	*		}
	*	}
	*/
	public function getUserToProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("6.12.3", "Project", "getusertoproject"));

		if (!$this->checkRoles($user, $projectId, "projectSettings"))
			return ($this->setNoRightsError("6.12.9", "Project", "getusertoproject"));

		$em = $this->getDoctrine()->getManager();
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
}
