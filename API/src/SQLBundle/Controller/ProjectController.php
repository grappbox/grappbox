<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use SQLBundle\Entity\Project;
use SQLBundle\Entity\CustomerAccess;
use SQLBundle\Entity\Role;
use SQLBundle\Entity\ProjectUserRole;
use SQLBundle\Entity\Tag;
use SQLBundle\Entity\BugtrackerTag;
use SQLBundle\Entity\Timeline;
use SQLBundle\Entity\Color;

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
*  @IgnoreAnnotation("apiIgnore")
*  @IgnoreAnnotation("apiDescription")
*  @IgnoreAnnotation("apiHeader")
*  @IgnoreAnnotation("apiHeaderExample")
*/
class ProjectController extends RolesAndTokenVerificationController
{
	/**
	* @api {post} /0.3/project Create a project for the user connected
	* @apiName projectCreation
	* @apiGroup Project
	* @apiDescription Create a project for the user connected
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {String} name Name of the project
	* @apiParam {String} [description] Description of the project
	* @apiParam {Text} [logo] Logo of the project
	* @apiParam {String} [phone] Phone for the project
	* @apiParam {String} [company] Company of the project
	* @apiParam {String} [email] Email for the project
	* @apiParam {String} [facebook] Facebook of the project
	* @apiParam {String} [twitter] Twitter of the person
	* @apiParam {String} password Safe password for the project hashed in SHA-1 512
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
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
	*			"name": "Grappbox"
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
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
	* @apiSuccess {string} name Name of the project
	* @apiSuccess {string} description Description of the project
	* @apiSuccess {Object[]} creator Creator infos
	* @apiSuccess {int} creator.id Id of the creator
	* @apiSuccess {string} creator.firstname Firstname of the creator
	* @apiSuccess {string} creator.lastname Lastname of the creator
	* @apiSuccess {string} logo Date of last modification of the logo
	* @apiSuccess {string} phone Phone of the company
	* @apiSuccess {string} company Company of the project
	* @apiSuccess {string} contact_mail Mail of contact of the project
	* @apiSuccess {string} facebook Facebook of the project
	* @apiSuccess {string} twitter Twitter of the project
	* @apiSuccess {string} color Color of the project
	* @apiSuccess {string} created_at Date of creation of the project
	* @apiSuccess {string} deleted_at Date of deletion of the project
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
	*			"id": 1,
	*			"name": "Grappbox",
	*			"description": "grappbox est un projet de gestion de projets",
	*			"creator": {
	*				"id": 13,
	*				"firstname": "John",
	*				"lastname": "Doe"
	*			},
	*			"logo": "2016-10-04 18:00:00",
	*			"phone": "+335 65 23 45 94",
	*			"company": "L'oie oppressée",
	*			"contact_mail": "contact@oieoppresee.com",
	*			"facebook": "www.facebook.com/OieOpp",
	*			"twitter": "www.twitter.com/OieOpp",
	*			"color": "#333333",
	*			"created_at": "2016-10-04 18:00:00",
	*			"deleted_at": null
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "new project",
	*			"body": {
	*				"id": 1,
	*				"name": "Grappbox",
	*				"description": "grappbox est un projet de gestion de projets",
	*				"creator": {
	*					"id": 13,
	*					"firstname": "John",
	*					"lastname": "Doe"
	*				},
	*				"logo": "2016-10-04 18:00:00",
	*				"phone": "+335 65 23 45 94",
	*				"company": "L'oie oppressée",
	*				"contact_mail": "contact@oieoppresee.com",
	*				"facebook": "www.facebook.com/OieOpp",
	*				"twitter": "www.twitter.com/OieOpp",
	*				"color": "#333333",
	*				"created_at": "2016-10-04 18:00:00",
	*				"deleted_at": null
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.1.3",
	*			"return_message": "Project - projectcreation - Bad Token"
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
	/**
	* @api {post} /V0.2/projects/projectcreation Create a project for the user connected
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
	* @apiParam {String} password Safe password for the project hashed in SHA-1 512
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

		if (!array_key_exists('name', $content) || !array_key_exists('password', $content))
			return $this->setBadRequest("6.1.6", "Project", "projectcreation", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return $this->setBadTokenError("6.1.3", "Project", "projectcreation");

		$em = $this->getDoctrine()->getManager();

		//Create the project
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
			$dir="/web/resources/projects/";
			$filename = 'project-'+$project->getId()+"-"+md5($this->get('security.secure_random')->nextBytes(10));

			$content->logo->getData()->move($dir, $filename);
			$project->setLogo($dir + $filename);
			$project->setLogoDate(new \DateTime);

			// $generator = $this->get('security.secure_random');
			// $random = $generator->nextBytes(10);
			// $fileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/avatars';
			// $fileName= md5($random).'.'.$request->files->get('avatar')->guessExtension();
			// $avatar = $request->files->get('avatar')->move($fileDir, $fileName);
			// $user->setAvatar($fileDir.'/'.$fileName);
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
		$qb = $em->getRepository('SQLBundle:Tag')->createQueryBuilder('t')->getQuery();
		$tags = $qb->getResult();
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

		//Create the default tags and bugtracker tags
		$qb = $em->getRepository('SQLBundle:BugtrackerTag')->createQueryBuilder('t')->getQuery();
		$btags = $qb->getResult();
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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$this->get('service_stat')->initiateStatistics($project, $request->headers->get('Authorization'), $request);

		return $this->setCreated("1.6.1", "Project", "projectcreation", "Complete Success", $project->objectToArray($em, $user));
	}

	private function grappSha1($str) // note : PLEASE DON'T REMOVE THAT FUNCTION! GOD DAMN IT!
	{
		return $str; //TODO : code the Grappbox sha-1 algorithm when assigned people ready
	}

	/**
	* @api {put} /0.3/project/:id Update a project informations
	* @apiName updateInformations
	* @apiGroup Project
	* @apiDescription Update the given project informations
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the project
	* @apiParam {Number} [creatorId] Id of the new creator
	* @apiParam {String} [name] name of the project
	* @apiParam {String} [description] Description of the project
	* @apiParam {Text} [logo] Logo of the project
	* @apiParam {String} [phone] Phone for the project
	* @apiParam {String} [company] Company of the project
	* @apiParam {String} [email] Email for the project
	* @apiParam {String} [facebook] Facebook of the project
	* @apiParam {String} [twitter] Twitter of the person
	* @apiParam {String} [password] New safe password for the project
	* @apiParam {String} [oldPassword] Old safe password for the project
	*
	* @apiParamExample {json} Request-Full-Example:
	*	{
	*		"data": {
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
	*		}
	*	}
	*
	* @apiParamExample {json} Request-Partial-Example:
	*	{
	*		"data": {
	*			"projectId": 2,
	*			"description": "grappbox est un projet de gestion de projets",
	*			"logo": "10001111001100110010101010",
	*			"phone": "+335 65 23 45 94",
	*			"twitter": "www.twitter.com/OieOpp"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the project created
	* @apiSuccess {string} name Name of the project
	* @apiSuccess {string} description Description of the project
	* @apiSuccess {Object[]} creator Creator infos
	* @apiSuccess {int} creator.id Id of the creator
	* @apiSuccess {string} creator.firstname Firstname of the creator
	* @apiSuccess {string} creator.lastname Lastname of the creator
	* @apiSuccess {string} logo Date of last modification of the logo
	* @apiSuccess {string} phone Phone of the company
	* @apiSuccess {string} company Company of the project
	* @apiSuccess {string} contact_mail Mail of contact of the project
	* @apiSuccess {string} facebook Facebook of the project
	* @apiSuccess {string} twitter Twitter of the project
	* @apiSuccess {string} color Color of the project
	* @apiSuccess {string} created_at Date of creation of the project
	* @apiSuccess {string} deleted_at Date of deletion of the project
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
	*			"id": 1,
	*			"name": "Grappbox",
	*			"description": "grappbox est un projet de gestion de projets",
	*			"creator": {
	*				"id": 13,
	*				"firstname": "John",
	*				"lastname": "Doe"
	*			},
	*			"logo": "2016-10-04 18:00:00",
	*			"phone": "+335 65 23 45 94",
	*			"company": "L'oie oppressée",
	*			"contact_mail": "contact@oieoppresee.com",
	*			"facebook": "www.facebook.com/OieOpp",
	*			"twitter": "www.twitter.com/OieOpp",
	*			"color": "#333333",
	*			"created_at": "2016-10-04 18:00:00",
	*			"deleted_at": null
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "update project",
	*			"body": {
	*				"id": 1,
	*				"name": "Grappbox",
	*				"description": "grappbox est un projet de gestion de projets",
	*				"creator": {
	*					"id": 13,
	*					"firstname": "John",
	*					"lastname": "Doe"
	*				},
	*				"logo": "2016-10-04 18:00:00",
	*				"phone": "+335 65 23 45 94",
	*				"company": "L'oie oppressée",
	*				"contact_mail": "contact@oieoppresee.com",
	*				"facebook": "www.facebook.com/OieOpp",
	*				"twitter": "www.twitter.com/OieOpp",
	*				"color": "#333333",
	*				"created_at": "2016-10-04 18:00:00",
	*				"deleted_at": null
	*			}
	*		}
	*	}
	* @apiSuccessExample {json} Notifications Logo
	*	{
	*		"data": {
	*			"title": "logo project",
	*			"body": {
	*				"id": 1,
	*				"name": "Grappbox",
	*				"description": "grappbox est un projet de gestion de projets",
	*				"creator": {
	*					"id": 13,
	*					"firstname": "John",
	*					"lastname": "Doe"
	*				},
	*				"logo": "2016-10-04 18:00:00",
	*				"phone": "+335 65 23 45 94",
	*				"company": "L'oie oppressée",
	*				"contact_mail": "contact@oieoppresee.com",
	*				"facebook": "www.facebook.com/OieOpp",
	*				"twitter": "www.twitter.com/OieOpp",
	*				"color": "#333333",
	*				"created_at": "2016-10-04 18:00:00",
	*				"deleted_at": null
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.2.3",
	*			"return_message": "Project - updateinformations - Bad Token"
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
	* @apiErrorExample Bad Parameter: oldPassword
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.2.4",
	*			"return_message": "Project - updateinformations - Bad Parameter: oldPassword"
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
	/**
	* @api {put} /V0.2/projects/updateinformations Update a project informations
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
	* @apiParam {String} [password] New safe password for the project hashed in SHA-1 512
	* @apiParam {String} [oldPassword] Old safe password for the project hashed in SHA-1 512
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
	public function updateInformationsAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.2.3", "Project", "updateinformations"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.2.4", "Project", "updateinformations", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $id, "projectSettings") < 2)
			return ($this->setNoRightsError("6.2.9", "Project", "updateinformations"));

		if (array_key_exists('creatorId', $content))
		{
			$creatorUser = $em->getRepository('SQLBundle:User')->find($content->creatorId);

			if ($creatorUser === null)
				return $this->setBadRequest("6.2.4", "Project", "updateinformations", "Bad Parameter: creatorId");

			$repository = $em->getRepository('SQLBundle:Role');

			$qb = $repository->createQueryBuilder('r')->join('r.projects', 'p')->where('r.name = :name', 'p.id = :id')->setParameter('name', "Admin")->setParameter('id', $id)->getQuery();
			$role = $qb->getResult();

			if (count($role) == 0)
				return $this->setBadRequest("6.2.1", "Project", "updateinformations", "Reading Error: role");
			else
				$role = $role[0];

			$repository = $em->getRepository('SQLBundle:ProjectUserRole');
			$creatorUserId = $project->getCreatorUser()->getId();
			$roleId = $role->getId();

			$qb = $repository->createQueryBuilder('r')->where('r.projectId = :projectId', 'r.userId = :userId', 'r.roleId = :roleId')
			->setParameter('projectId', $id)->setParameter('userId', $creatorUserId)->setParameter('roleId', $roleId)->getQuery();
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
		{

			// $dir = "/var/www/static/app/project/";
			// print_r('https://static.grappbox.com/project/'+$filename);

			// $filepath = $this->container->getParameter('kernel.root_dir')."/../web/resources/user/".$id;
			$filepath = "/var/www/static/app/project/".$id;

			$file = base64_decode($content->logo);
			if ($file == false)
				print_r('invalid data');

			$image = imagecreatefromstring($file);
			if ($image == false)
				print_r('invalid data');

			if (imagejpeg($image, $filepath, 50))
				print_r('invalid data');

			$fileurl = 'https://static.grappbox.com/project/'.$id;

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
				$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);
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
				return $this->setBadRequest("6.2.6", "Project", "updateinformations", "Bad Parameter: oldPassword");
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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.6.1", "Project", "updateinformations", "Complete Success", $project->objectToArray($em, $user));
	}

	/**
	* @api {get} /0.3/project/:id Get a project basic informations
	* @apiName getInformations
	* @apiGroup Project
	* @apiDescription Get the given project basic informations
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {String} name Name of the project
	* @apiSuccess {String} description Description of the project
	* @apiSuccess {Object[]} creator Creator infos
	* @apiSuccess {int} creator.id Id of the creator
	* @apiSuccess {string} creator.firstname Firstname of the creator
	* @apiSuccess {string} creator.lastname Lastname of the creator
	* @apiSuccess {string} logo Logo last modif date of the project
	* @apiSuccess {String} phone Phone number of the project
	* @apiSuccess {String} company Company name
	* @apiSuccess {String} contact_mail for the project
	* @apiSuccess {String} facebook Facebook for the project
	* @apiSuccess {String} twitter Twitter for the project
	* @apiSuccess {String} color Color of the project
	* @apiSuccess {string} creation_date Date of creation of the project
	* @apiSuccess {string} deleted_at Date when the project will be deleted
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
	*			"creator": {
	*				"id": 13,
	*				"firstname": "John",
	*				"lastname": "Doe"
	*			},
	*			"logo": "1945-06-18 06:00:00",
	*			"phone": "+89130 2145 8795",
	*			"company": "L'oie Oppressée",
	*			"contact_mail": "contact@grappbox.com",
	*			"facebook": "https://facebook.com/Grappbox",
	*			"twitter": "https://twitter.com/Grappbox",
	*			"color": "12ff52",
	*			"creation_date": "2015-10-15 11:00:00",
	*			"deleted_at": null
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.3.3",
	*			"return_message": "Project - getinformations - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.3.4",
	*			"return_message": "Project - getinformations - Bad Parameter: id"
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
	/**
	* @api {get} /V0.2/projects/getinformations/:token/:projectId Get a project basic informations
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
	* @apiSuccess {date} logo Logo last modif date of the project
	* @apiSuccess {String} phone Phone number of the project
	* @apiSuccess {String} company Company name
	* @apiSuccess {String} contact_mail for the project
	* @apiSuccess {String} facebook Facebook for the project
	* @apiSuccess {String} twitter Twitter for the project
	* @apiSuccess {String} color Color of the project
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
	*			"logo": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"},
	*			"phone": "+89130 2145 8795",
	*			"company": "L'oie Oppressée",
	*			"contact_mail": "contact@grappbox.com",
	*			"facebook": "https://facebook.com/Grappbox",
	*			"twitter": "https://twitter.com/Grappbox",
	*			"color": "12ff52",
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
	public function getInformationsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return $this->setBadTokenError("6.3.3", "Project", "getinformations");

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.3.4", "Project", "getinformations", "Bad Parameter: id");

		return $this->setSuccess("1.6.1", "Project", "getinformations", "Complete Success", $project->objectToArray($em, $user));
	}

	/**
	* @api {delete} /0.3/project/:id Delete a project 7 days after the call
	* @apiName delProject
	* @apiGroup Project
	* @apiDescription Set the deleted at of the given project to 7 days after the call of the function
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - delproject - Complete Success"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "delete project",
	*			"body": {
	*				"id": 1,
	*				"name": "Grappbox",
	*				"description": "grappbox est un projet de gestion de projets",
	*				"creator": {
	*					"id": 13,
	*					"firstname": "John",
	*					"lastname": "Doe"
	*				},
	*				"logo": "2016-10-04 18:00:00",
	*				"phone": "+335 65 23 45 94",
	*				"company": "L'oie oppressée",
	*				"contact_mail": "contact@oieoppresee.com",
	*				"facebook": "www.facebook.com/OieOpp",
	*				"twitter": "www.twitter.com/OieOpp",
	*				"color": "#333333",
	*				"created_at": "2016-10-04 18:00:00",
	*				"deleted_at": null
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.4.3",
	*			"return_message": "Project - delproject - Bad Token"
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
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.4.4",
	*			"return_message": "Project - delproject - Bad Parameter: id"
	*		}
	*	}
	*/
	/**
	* @api {delete} /V0.2/projects/delproject/:token/:projectId Delete a project 7 days after the call
	* @apiName delProject
	* @apiGroup Project
	* @apiDescription Set the deleted at of the given project to 7 days after the call of the function
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - delproject - Complete Success"
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
	public function delProjectAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.4.3", "Project", "delproject"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.4.4", "Project", "delproject", "Bad Parameter: id");

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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - delproject - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /0.3/project/retrieve/:projectId Retreive a project before the 7 days are passed, after delete
	* @apiName retrieveProject
	* @apiGroup Project
	* @apiDescription Retreive a project set to be deleted, but have to be called before the 7 days are passed
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
	* @apiSuccess {Number} id Id of the project retrieve
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - retrieveproject - Complete Success"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "retrieve project",
	*			"body": {
	*				"id": 1,
	*				"name": "Grappbox",
	*				"description": "grappbox est un projet de gestion de projets",
	*				"creator": {
	*					"id": 13,
	*					"firstname": "John",
	*					"lastname": "Doe"
	*				},
	*				"logo": "2016-10-04 18:00:00",
	*				"phone": "+335 65 23 45 94",
	*				"company": "L'oie oppressée",
	*				"contact_mail": "contact@oieoppresee.com",
	*				"facebook": "www.facebook.com/OieOpp",
	*				"twitter": "www.twitter.com/OieOpp",
	*				"color": "#333333",
	*				"created_at": "2016-10-04 18:00:00",
	*				"deleted_at": null
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.5.3",
	*			"return_message": "Project - retrieveproject - Bad Token"
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
	/**
	* @api {get} /V0.2/projects/retrieveproject/:token/:projectId Retreive a project before the 7 days are passed, after delete
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
	public function retrieveProjectAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.5.3", "Project", "retrieveproject"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);
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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - retrieveproject - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {post} /0.3/project/customeraccess Generate or Regenerate a customer access for a project
	* @apiName generateCustomerAccess
	* @apiGroup Project
	* @apiDescription Generate or regenerate a customer access for the given project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {String} projectId Id of the project
	* @apiParam {String} name Name of the customer access
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"projectId": 2,
	*			"name": "access for Toyota"
	*		}
	*	}
	*
	* @apiSuccess {Number} id Id of the customer access
	* @apiSuccess {string} name Nom du customer access
	* @apiSuccess {string} token Customer access token
	* @apiSuccess {int} project_id Id of the project
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
	*			"id": 1,
	*			"name": "access for Toyota",
	*			"token": "246107b11d98a9546978a2c20632bb2d",
	*			"project_id": 2
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "new customeraccess",
	*			"body": {
	*				"id": 1,
	*				"name": "access for Toyota",
	*				"token": "246107b11d98a9546978a2c20632bb2d",
	*				"project_id": 2
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.6.3",
	*			"return_message": "Project - generatecustomeraccess - Bad Token"
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
	/**
	* @api {post} /V0.2/projects/generatecustomeraccess Generate or Regenerate a customer access for a project
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

		if (!array_key_exists('projectId', $content) || !array_key_exists('name', $content))
			return $this->setBadRequest("6.6.6", "Project", "generatecustomeraccess", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.6.3", "Project", "generatecustomeraccess"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($content->projectId);
		if ($project === null)
			return $this->setBadRequest("6.6.4", "Project", "generatecustomeraccess", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.6.9", "Project", "generatecustomeraccess"));

		$repository = $em->getRepository('SQLBundle:CustomerAccess');

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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.6.1", "Project", "generatecustomeraccess", "Complete Success", $customerAccess->objectToArray());
	}

	/**
	* @api {get} /0.3/project/customeraccesses/:projectId Get a customer accesses by it's project
	* @apiName getCustomerAccessByProject
	* @apiGroup Project
	* @apiDescription Get a customer access by it's poject id
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
	* @apiSuccess {Object[]} array Array of customer access
	* @apiSuccess {String} array.name Name of the customer access
	* @apiSuccess {String} array.customer_token Customer access token
	* @apiSuccess {Number} array.id Id of the customer access
	* @apiSuccess {int} array.project_id Id of the project
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
	*					"project_id": 3
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.8.3",
	*			"return_message": "Project - getcustomeraccessbyproject - Bad Token"
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
	/**
	* @api {get} /V0.2/projects/getcustomeraccessbyproject/:token/:projectId Get a customer accesses by it's project
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
	public function getCustomerAccessByProjectAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.8.3", "Project", "getcustomeraccessbyproject"));

		$em = $this->getDoctrine()->getManager();
		$customerAccess = $em->getRepository('SQLBundle:CustomerAccess')->findByprojects($projectId);
		if ($customerAccess === null)
			return $this->setBadRequest("6.8.4", "Project", "getcustomeraccessbyproject", "Bad Parameter: projectId");

		$arr = array();

		if (count($customerAccess) == 0)
			return $this->setNoDataSuccess("1.6.3", "Project", "getcustomeraccessbyproject");

		foreach ($customerAccess as $ca) {
			$arr[] = $ca->objectToArray();
		}

		return $this->setSuccess("1.6.1", "Project", "getcustomeraccessbyproject", "Complete Success", array("array" => $arr));
	}

	/**
	* @api {delete} /0.3/project/customeraccess/:projectId/:customerAccessId Delete a customer access
	* @apiName delCustomerAccess
	* @apiGroup Project
	* @apiDescription Delete the given customer access
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} customerAccessId Id of the customer access
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - delcustomeraccess - Complete Success"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "delete customeraccess",
	*			"body": {
	*				"id": 1,
	*				"name": "access for Toyota",
	*				"token": "246107b11d98a9546978a2c20632bb2d",
	*				"project_id": 2
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.9.3",
	*			"return_message": "Project - delcustomeraccess - Bad Token"
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
	/**
	* @api {delete} /V0.2/projects/delcustomeraccess/:token/:projectId/:customerAccessId Delete a customer access
	* @apiName delCustomerAccess
	* @apiGroup Project
	* @apiDescription Delete the given customer access
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} customerAccessId Id of the customer access
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - delcustomeraccess - Complete Success"
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
	public function delCustomerAccessAction(Request $request, $projectId, $customerAccessId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.9.3", "Project", "delcustomeraccess"));

		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.9.9", "Project", "delcustomeraccess"));

		$em = $this->getDoctrine()->getManager();
		$customerAccess = $em->getRepository('SQLBundle:CustomerAccess')->find($customerAccessId);

		if ($customerAccess === null)
			return $this->setBadRequest("6.9.4", "Project", "delcustomeraccess", "Bad Parameter: customerAccessId");

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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$em->remove($customerAccess);
		$em->flush();

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - delcustomeraccess - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {post} /0.3/project/user Add a user to a project
	* @apiName addUserToProject
	* @apiGroup Project
	* @apiDescription Add a given user to the project wanted
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the project
	* @apiParam {String} email Email of the user
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"id": 2,
	*			"email": "toto@titi.com"
	*		}
	*	}
	*
	* @apiSuccess id Id of the user add
	* @apiSuccess firstname First name of the user add
	* @apiSuccess lastname Last name of the user add
	* @apiSuccess avatar Date Avatar of the user add
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
	*			"avatar": "2016-11-09"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "user assign project",
	*			"body": {
	*				"id": 1,
	*				"user":{
	*					"id": 1
	*					"firstname": "john",
	*					"lastname": "doe",
	*					"avatar": "2016-11-09"
	*				}
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.10.3",
	*			"return_message": "Project - addusertoproject - Bad Token"
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
	/**
	* @api {post} /V0.2/projects/addusertoproject Add a user to a project
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

		if (!array_key_exists('id', $content) || !array_key_exists('email', $content))
			return $this->setBadRequest("6.10.6", "Project", "addusertoproject", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.10.3", "Project", "addusertoproject"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($content->id);
		if ($project === null)
			return $this->setBadRequest("6.10.4", "Project", "addusertoproject", "Bad Parameter: id");

		if ($this->checkRoles($user, $content->id, "projectSettings") < 2)
			return ($this->setNoRightsError("6.10.9", "Project", "addusertoproject"));

		$userToAdd = $em->getRepository('SQLBundle:User')->findOneByemail($content->email);
		if ($userToAdd === null)
			return $this->setBadRequest("6.10.4", "Project", "addusertoproject", "Bad Parameter: email");

		$users = $project->getUsers();
		foreach ($users as $user) {
			if ($user === $userToAdd)
				return $this->setBadRequest("6.10.7", "Project", "addusertoproject", "Already In Database");
		}

		$project->addUser($userToAdd);
		$em->flush();

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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.6.1", "Project", "addusertoproject", "Complete Success",
			array("id" => $userToAdd->getId(), "firstname" => $userToAdd->getFirstname(), "lastname" => $userToAdd->getLastname(), "avatar" => $userToAdd->getAvatarDate()));
	}

	/**
	* @api {delete} /V0.3/project/userconnected/:projectId Remove the user connected from the project
	* @apiName removeUserConnected
	* @apiGroup Project
	* @apiDescription Remove the user connected from the project
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
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - removeuserconnected - Complete Success"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "user unassign project",
	*			"body": {
	*				"id": 1,
	*				"user":{
	*					"id": 1
	*					"firstname": "john",
	*					"lastname": "doe",
	*					"avatar": "2016-11-09"
	*				}
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.11.3",
	*			"return_message": "Project - removeuserconnected - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.11.4",
	*			"return_message": "Project - removeuserconnected - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You are not on the project
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.11.4",
	*			"return_message": "Project - removeuserconnected - Bad Parameter: You are not on the project"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You are the project creator, you can't be removed
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.11.4",
	*			"return_message": "Project - removeuserconnected - Bad Parameter: You are the project creator, you can't be removed"
	*		}
	*	}
	*/
	public function removeUserConnectedAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.11.3", "Project", "removeuserconnected"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);
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

		$userRoleLink = $em->getRepository('SQLBundle:ProjectUserRole')->findBy(array('projectId'=> $project->getId(), 'userId' => $userId));
		foreach ($userRoleLink as $key => $userRole) {
			$em->remove($userRole);
			$em->flush();
		}

		$bugs = $em->getRepository('SQLBundle:Bug')->findBy(array('projects'=> $project->getId()));
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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - removeuserconnected - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {delete} /0.3/project/user/:projectId/:userId Remove a user from the project
	* @apiName removeUserToProject
	* @apiGroup Project
	* @apiDescription Remove a given user to the project wanted
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} userId Id of the user
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - removeusertoproject - Complete Success"
	*		}
	*	}
	* @apiSuccessExample {json} Notifications
	*	{
	*		"data": {
	*			"title": "user unassign project",
	*			"body": {
	*				"id": 1,
	*				"user":{
	*					"id": 1
	*					"firstname": "john",
	*					"lastname": "doe",
	*					"avatar": "2016-11-09"
	*				}
	*			}
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.12.3",
	*			"return_message": "Project - removeusertoproject - Bad Token"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.12.9",
	*			"return_message": "Project - removeusertoproject - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.12.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.12.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: userId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You can't remove the project creator
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.12.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: You can't remove the project creator"
	*		}
	*	}
	*/
	/**
	* @api {delete} /V0.2/projects/removeusertoproject/:token/:projectId/:userId Remove a user from the project
	* @apiName removeUserToProject
	* @apiGroup Project
	* @apiDescription Remove a given user to the project wanted
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} userId Id of the user
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - removeusertoproject - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.12.3",
	*			"return_message": "Project - removeusertoproject - Bad ID"
	*		}
	*	}
	* @apiErrorExample Insufficient Rights
	*	HTTP/1.1 403 Forbidden
	*	{
	*		"info": {
	*			"return_code": "6.12.9",
	*			"return_message": "Project - removeusertoproject - Insufficient Rights"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.12.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: userId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.12.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: userId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: You can't remove the project creator
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.12.4",
	*			"return_message": "Project - removeusertoproject - Bad Parameter: You can't remove the project creator"
	*		}
	*	}
	*/
	public function removeUserToProjectAction(Request $request, $projectId, $userId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.12.3", "Project", "removeusertoproject"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($projectId);
		if ($project === null)
			return $this->setBadRequest("6.12.4", "Project", "removeusertoproject", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $projectId, "projectSettings") < 2)
			return ($this->setNoRightsError("6.12.9", "Project", "removeusertoproject"));

		$userToRemove = $em->getRepository('SQLBundle:User')->find($userId);
		if ($userToRemove === null)
			return $this->setBadRequest("6.12.4", "Project", "removeusertoproject", "Bad Parameter: userId");

		if ($userToRemove === $project->getCreatorUser())
			return $this->setBadRequest("6.12.4", "Project", "removeusertoproject", "Bad Parameter: You can't remove the project creator");

		$users = $project->getUsers();
		$isOnProject = false;
		foreach ($users as $u) {
			if ($u == $userToRemove)
				$isOnProject = true;
		}
		if ($isOnProject == false)
			return $this->setBadRequest("6.12.4", "Project", "removeusertoproject", "Bad Parameter: userId");

		$userRoleLink = $em->getRepository('SQLBundle:ProjectUserRole')->findBy(array('projectId'=> $project->getId(), 'userId' => $userId));
		foreach ($userRoleLink as $key => $userRole) {
			$em->remove($userRole);
			$em->flush();
		}

		$bugs = $em->getRepository('SQLBundle:Bug')->findBy(array('projects'=> $project->getId()));
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
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - removeusertoproject - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /0.3/project/users/:id Get all the users on a project
	* @apiName getUserToProject
	* @apiGroup Project
	* @apiDescription Get all the users on the given project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the project
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
	*					"firstname": "John",
	*					"lastname": "Doe"
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
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.13.3",
	*			"return_message": "Project - getusertoproject - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.13.4",
	*			"return_message": "Project - getusertoproject - Bad Parameter: id"
	*		}
	*	}
	*/
	/**
	* @api {get} /V0.2/projects/getusertoproject/:token/:projectId Get all the users on a project
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
	*					"firstname": "John",
	*					"lastname": "Doe"
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
	*			"return_code": "6.13.3",
	*			"return_message": "Project - getusertoproject - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.13.4",
	*			"return_message": "Project - getusertoproject - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function getUserToProjectAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.13.3", "Project", "getusertoproject"));

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.13.4", "Project", "getusertoproject", "Bad Parameter: id");

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
	* @api {put} /0.3/project/color/:id Change the color of a project
	* @apiName changeProjectColor
	* @apiGroup Project
	* @apiDescription Change the color of a project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the project
	* @apiParam {String} color Color of the project, in hexadecimal
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"color": "bd2487"
	*		}
	*	}
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - changeprojectcolor - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.14.3",
	*			"return_message": "Project - changeprojectcolor - Bad Token"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.14.6",
	*			"return_message": "Project - changeprojectcolor - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.14.4",
	*			"return_message": "Project - changeprojectcolor - Bad Parameter: id"
	*		}
	*	}
	*/
	/**
	* @api {put} /V0.2/projects/changeprojectcolor Change the color of a project
	* @apiName changeProjectColor
	* @apiGroup Project
	* @apiDescription Change the color of a project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} color Color of the project, in hexadecimal
	*
	* @apiParamExample {json} Request-Example:
	*	{
	*		"data": {
	*			"token": "nfeq34efbfkqf54",
	*			"projectId": 2,
	*			"color": "bd2487"
	*		}
	*	}
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - changeprojectcolor - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.14.3",
	*			"return_message": "Project - changeprojectcolor - Bad ID"
	*		}
	*	}
	* @apiErrorExample Missing Parameters
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.14.6",
	*			"return_message": "Project - changeprojectcolor - Missing Parameter"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.14.4",
	*			"return_message": "Project - changeprojectcolor - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function changeProjectColorAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('color', $content))
			return $this->setBadRequest("6.14.6", "Project", "changeprojectcolor", "Missing Parameter");

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.14.4", "Project", "changeprojectcolor", "Bad Parameter: id");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.14.3", "Project", "changeprojectcolor"));

		$color = $em->getRepository('SQLBundle:Color')->findOneBy(array("project" => $project, "user" => $user));
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
	* @api {delete} /0.3/project/color/:id Reset the color of the project
	* @apiName resetProjectColor
	* @apiGroup Project
	* @apiDescription Reset the color of the given project to the default one
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - resetprojectcolor - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.15.3",
	*			"return_message": "Project - resetprojectcolor - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.15.4",
	*			"return_message": "Project - resetprojectcolor - Bad Parameter: id"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: No color for the user
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.15.4",
	*			"return_message": "Project - resetprojectcolor - Bad Parameter: No color for the user"
	*		}
	*	}
	*/
	/**
	* @api {delete} /V0.2/projects/resetprojectcolor/:token/:projectId Reset the color of the project
	* @apiName resetProjectColor
	* @apiGroup Project
	* @apiDescription Reset the color of the given project to the default one
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - resetprojectcolor - Complete Success"
	*		}
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.15.3",
	*			"return_message": "Project - resetprojectcolor - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.15.4",
	*			"return_message": "Project - resetprojectcolor - Bad Parameter: projectId"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: No color for the user
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.15.4",
	*			"return_message": "Project - resetprojectcolor - Bad Parameter: No color for the user"
	*		}
	*	}
	*/
	public function resetProjectColorAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.15.4", "Project", "resetprojectcolor", "Bad Parameter: id");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.15.3", "Project", "resetprojectcolor"));

		$color = $em->getRepository('SQLBundle:Color')->findOneBy(array("project" => $project, "user" => $user));
		if ($color === null)
			return $this->setBadRequest("6.15.4", "Project", "resetprojectcolor", "Bad Parameter: No color for the user");

		$em->remove($color);
		$em->flush();

		$response["info"]["return_code"] = "1.6.1";
		$response["info"]["return_message"] = "Project - resetprojectcolor - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {get} /0.3/project/logo/:id Get project logo
	* @apiName getProjectLogo
	* @apiGroup Project
	* @apiDescription Get the logo of the given project
	* @apiVersion 0.3.0
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} id Id of the project
	*
	* @apiSuccess {string} logo Logo of the project
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - getProjectLogo - Complete Success"
	*		},
	*		"data": {
	*			"logo": "/web/resources/projects/project-12-az5fse4sdf6e5s3d5fsd.png"
	*		},
	*	}
	*
	* @apiErrorExample Bad Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.16.3",
	*			"return_message": "Project - getProjectLogo - Bad Token"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: id
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.16.4",
	*			"return_message": "Project - getProjectLogo - Bad Parameter: id"
	*		}
	*	}
	*/
	/**
	* @api {get} /V0.2/projects/getprojectlogo/:token/:projectId Get project logo
	* @apiName getProjectLogo
	* @apiGroup Project
	* @apiDescription Get the logo of the given project
	* @apiVersion 0.2.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiSuccess {Text} logo Logo of the project
	*
	* @apiSuccessExample Success-Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info": {
	*			"return_code": "1.6.1",
	*			"return_message": "Project - getProjectLogo - Complete Success"
	*		},
	*		"data": {
	*			"logo": "10100011000011001"
	*		},
	*	}
	*
	* @apiErrorExample Bad Authentication Token
	*	HTTP/1.1 401 Unauthorized
	*	{
	*		"info": {
	*			"return_code": "6.16.3",
	*			"return_message": "Project - getProjectLogo - Bad ID"
	*		}
	*	}
	* @apiErrorExample Bad Parameter: projectId
	*	HTTP/1.1 400 Bad Request
	*	{
	*		"info": {
	*			"return_code": "6.16.4",
	*			"return_message": "Project - getProjectLogo - Bad Parameter: projectId"
	*		}
	*	}
	*/
	public function getProjectLogoAction(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('SQLBundle:Project')->find($id);
		if ($project === null)
			return $this->setBadRequest("6.16.4", "Project", "getProjectLogo", "Bad Parameter: id");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("6.16.3", "Project", "getProjectLogo"));

		return $this->setSuccess("1.6.1", "Project", "getProjectLogo", "Complete Success", array("logo" => $project->getLogo()));
	}
}
