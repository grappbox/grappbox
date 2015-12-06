<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use APIBundle\Entity\Project;
use APIBundle\Entity\CustomerAccess;

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
 */
class ProjectController extends RolesAndTokenVerificationController
{
	/**
	* @api {post} /V0.8/projects/projectcreation/:token Create a project for the user connected
	* @apiName projectCreation
	* @apiGroup Project
	* @apiVersion 0.8.0
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
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "1dq354c3q4",
	*		"name": "Grappbox",
	*		"description": "grappbox est un projet de gestion de projets",
	*		"logo": "10001111001100110010101010"
	*		"company": "L'oie oppressée"
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"project_id" : 3
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	*
	*/
	public function projectCreationAction(Request $request, $token)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!array_key_exists('name', $content))
			return $this->setBadRequest("Missing Parameter");
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
		$em->flush();
		$id = $project->getId();

		return new JsonResponse(array("project_id" => $id));
	}

	/**
	* @api {put} /V0.8/projects/updateinformations Update a project informations
	* @apiName updateInformations
	* @apiGroup Project
	* @apiVersion 0.8.0
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
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "nfeq34efbfkqf54",
	*		"projetcId": 2,
	*		"creatorId": 18,
	*		"name": "Grappbox",
	*		"description": "grappbox est un projet de gestion de projets",
	*		"logo": "10001111001100110010101010"
	*		"company": "L'oie oppressée"
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Update project informations Success."
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	*
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with id X doesn't exist"
	* 	}
	*
	*/
	public function updateInformationsAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if (!array_key_exists('projectId', $content) && !array_key_exists('token', $content))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($content->projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		if (array_key_exists('creatorId', $content))
		{
			$creatorUser = $em->getRepository('APIBundle:User')->find($content->creatorId);

			if ($creatorUser === null)
			{
				throw new NotFoundHttpException("The user with id ".$content->creatorId." doesn't exist");
				
			}

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

		return new JsonResponse("Update project informations Success.");
	}

	/**
  	* @api {get} /V0.8/projects/getinformations/:token/:projectId Get a project basic informations
  	* @apiName getInformations
  	* @apiGroup Project
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} projectId Id of the project
  	*
  	* @apiSuccess {String} name Name of the project
  	* @apiSuccess {String} description Description of the project
  	* @apiSuccess {Text} logo Logo of the project
  	* @apiSuccess {String} phone Phone number of the project
  	* @apiSuccess {String} company Company name
  	* @apiSuccess {String} email for the project
  	* @apiSuccess {String} facebook Facebook for the project
  	* @apiSuccess {String} twitter Twitter for the project
  	* @apiSuccess {Datetime} creation_date Date of creation of the project
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"name": "Grappbox",
	*		"description": "Grappbox est un projet",
	*		"logo": "10100011000011001",
	*		"phone": "+89130 2145 8795",
	*		"company": "L'oie Oppressée",
	*		"contact_mail": "contact@grappbox.com",
	*		"facebook": "https://facebook.com/Grappbox",
	*		"twitter": "https://twitter.com/Grappbox",
	*		"creation_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
  	*
  	*/
	public function getInformationsAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$projectId." doesn't exist");
		}

		$name = $project->getName();
		$description = $project->getDescription();
		$logo = $project->getLogo();
		$phone = $project->getPhone();
		$company = $project->getCompany();
		$contactMail = $project->getContactEmail();
		$facebook = $project->getFacebook();
		$twitter = $project->getTwitter();
		$creation = $project->getCreatedAt();

		return new JsonResponse(array("name" => $name, "description" => $description, "logo" => $logo, "phone" => $phone, "company" => $company , "contact_mail" => $contactMail,
			"facebook" => $facebook, "twitter" => $twitter, "creation_date" => $creation));
	}

	/**
	* @api {delete} /V0.8/projects/delproject Delete a project 7 days after the call
	* @apiName delProject
	* @apiGroup Project
	* @apiVersion 0.8.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	*
	* @apiParamExample {json} Request-Example:
	*   {
	*   	"token": "aeqf231ced651qcd",
	*   	"projectId": 1
	*   }
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*	  "The project will be deleted at 2012-12-21 12:12:12"
	*	}
	*
	* @apiErrorExample Missing Parameter
	*   HTTP/1.1 400 Bad Request
	*   {
	*     "Missing Parameter"
	*   }
	* @apiErrorExample Bad Authentication Token
	*   HTTP/1.1 400 Bad Request
	*   {
	*     "Bad Authentication Token"
	*   }
	* @apiErrorExample Insufficient User Rights
	*   HTTP/1.1 403 Forbidden
	*   {
	*     "Insufficient User Rights"
	*   }
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Project not found
	*     HTTP/1.1 404 Not Found
	*     {
	*       "The project with id X doesn't exist."
	*     }
	*
	*/
	public function delProjectAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if (!array_key_exists('projectId', $content) && !array_key_exists('token', $content))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($content->projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		$delDate = new \DateTime;
		$delDate->add(new \DateInterval('P7D')); 
		$project->setDeletedAt($delDate);

		$em->flush();

		return new JsonResponse("The project will be deleted at ".$delDate->format('Y-m-d H:i:s'));
	}

	/**
  	* @api {get} /V0.8/projects/retrieveproject/:token/:projectId Retreive a project before the 7 days are passed, after delete
  	* @apiName retrieveProject
  	* @apiGroup Project
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} projectId Id of the project
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
	*		"Project X retreived."
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Insufficient User Rights
	*   HTTP/1.1 403 Forbidden
	*   {
	*     "Insufficient User Rights"
	*   }
  	*
  	*/
	public function retrieveProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $projectId, "projectSettings"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$projectId." doesn't exist");
		}

		$project->setDeletedAt(null);
		$em->flush();

		return new JsonResponse("Project ".$projectId." retreived.");
	}

	/**
	* @api {post} /V0.8/projects/generatecustomeraccess Generate or Regenerate a customer access for a project
	* @apiName generateCustomerAccess
	* @apiGroup Project
	* @apiVersion 0.8.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} projectId Id of the project
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "13cqs43c54vqd3",
	*		"projectId": 2
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"Customer access generated with id: X"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	*
	* @apiErrorExample Insufficient User Rights
	*   HTTP/1.1 403 Forbidden
	*   {
	*     "Insufficient User Rights"
	*   }
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	*/

	/**
	* @api {post} /V0.8/projects/generatecustomeraccess Generate or Regenerate a customer access for a project
	* @apiName generateCustomerAccess
	* @apiGroup Project
	* @apiVersion 0.8.1
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {String} projectId Id of the project
	* @apiParam {String} name Name of the customer access
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "13cqs43c54vqd3",
	*		"projectId": 2,
	*		"name": "access for Toyota"
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"id": 3
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	*
	* @apiErrorExample Insufficient User Rights
	*   HTTP/1.1 403 Forbidden
	*   {
	*     "Insufficient User Rights"
	*   }
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	*/
	public function generateCustomerAccessAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if (!array_key_exists('projectId', $content) && !array_key_exists('token', $content) && !array_key_exists('name', $content))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($content->projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		$repository = $em->getRepository('APIBundle:CustomerAccess');

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

		return new JsonResponse(array("id" => $customerAccess->getId()));
	}

	/**
  	* @api {get} /V0.8/projects/getcustomeraccessbyid/:token/:id Get a customer access by it's id
  	* @apiName getCustomerAccessById
  	* @apiGroup Project
  	* @apiVersion 0.8.0
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
  	* 	{
	*		"customer_token": "dizjflqfq41c645w",
	*		"project_id": 2,
	*		"creation_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No custromer access found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The custromer access with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
  	*
  	*/

  	/**
  	* @api {get} /V0.8/projects/getcustomeraccessbyid/:token/:id Get a customer access by it's id
  	* @apiName getCustomerAccessById
  	* @apiGroup Project
  	* @apiVersion 0.8.1
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
  	* 	{
	*		"customer_token": "dizjflqfq41c645w",
	*		"project_id": 2,
	*		"name": "access for X company",
	*		"creation_date":
	*		{
	*			"date":"2015-10-15 11:00:00",
	*			"timezone_type":3,
	*			"timezone":"Europe\/Paris"
	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No custromer access found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The custromer access with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
  	*
  	*/
	public function getCustomerAccessByIdAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$customerAccess = $em->getRepository('APIBundle:CustomerAccess')->find($id);

		if ($customerAccess === null)
		{
			throw new NotFoundHttpException("The custromer access with id ".$id." doesn't exist");
		}

		$name = $customerAccess->getName();
		$hash = $customerAccess->getHash();
		$createdAt = $customerAccess->getCreatedAt();
		$project = $customerAccess->getProjects()->getId();

		return new JsonResponse(array("name" => $name, "customer_token" => $hash, "creation_date" => $createdAt, "project_id" => $project));
	}

	/**
  	* @api {get} /V0.8/projects/getcustomeraccessbyproject/:token/:projectId Get a customer accesses by it's project
  	* @apiName getCustomerAccessByProject
  	* @apiGroup Project
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} projectId Id of the project
  	*
  	* @apiSuccess {Object[]} CustomerAccess Array of customer access
  	* @apiSuccess {Number} CustomerAccess.id Id of the customer access
  	* @apiSuccess {String} CustomerAccess.customer_token Customer access token
  	* @apiSuccess {Datetime} CustomerAccess.creation_date Date of creation of the customer access
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"CustomerAccess 1": 
  	*		{
	*			"customer_token": "dizjflqfq41c645w",
	*			"id": 2,
	*			"creation_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No custromer access found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The custromer access with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
  	*
  	*/

  	/**
  	* @api {get} /V0.8/projects/getcustomeraccessbyproject/:token/:projectId Get a customer accesses by it's project
  	* @apiName getCustomerAccessByProject
  	* @apiGroup Project
  	* @apiVersion 0.8.1
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} projectId Id of the project
  	*
  	* @apiSuccess {Object[]} CustomerAccess Array of customer access
  	* @apiSuccess {String} CustomerAccess.name Name of the customer access
  	* @apiSuccess {Number} CustomerAccess.id Id of the customer access
  	* @apiSuccess {String} CustomerAccess.customer_token Customer access token
  	* @apiSuccess {Datetime} CustomerAccess.creation_date Date of creation of the customer access
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"CustomerAccess 1": 
  	*		{
  	*			"name": "access for client X",
	*			"customer_token": "dizjflqfq41c645w",
	*			"id": 2,
	*			"creation_date":
	*			{
	*				"date":"2015-10-15 11:00:00",
	*				"timezone_type":3,
	*				"timezone":"Europe\/Paris"
	*			}
	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No custromer access found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The custromer access with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
  	*
  	*/
	public function getCustomerAccessByProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());
		$em = $this->getDoctrine()->getManager();
		$customerAccess = $em->getRepository('APIBundle:CustomerAccess')->findByprojects($projectId);

		if ($customerAccess === null)
		{
			throw new NotFoundHttpException("The're no custromer access for the project with id ".$projectId);
		}

		$arr = array();
		$i = 1;

		foreach ($customerAccess as $ca) {
			$id = $ca->getId();
			$name = $ca->getName();
			$hash = $ca->getHash();
			$createdAt = $ca->getCreatedAt();

			$arr["CustomerAccess ".$i] = array("id" => $id, "name" => $name, "customer_token" => $hash, "creation_date" => $createdAt);
			$i++;
		}

		return new JsonResponse($arr);
	}

	/**
	* @api {delete} /V0.8/projects/delcustomeraccess Delete a customer access
	* @apiName delCustomerAccess
	* @apiGroup Project
	* @apiVersion 0.8.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} customerAccessId Id of the customer access
	*
	* @apiParamExample {json} Request-Example:
	*   {
	*   	"token": "aeqf231ced651qcd",
	*   	"projectId": 1,
	*		"customerAccessId": 3
	*   }
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*	  "Customer access successfully remove!"
	*	}
	*
	* @apiErrorExample Missing Parameter
	*   HTTP/1.1 400 Bad Request
	*   {
	*     "Missing Parameter"
	*   }
	* @apiErrorExample Bad Authentication Token
	*   HTTP/1.1 400 Bad Request
	*   {
	*     "Bad Authentication Token"
	*   }
	* @apiErrorExample Insufficient User Rights
	*   HTTP/1.1 403 Forbidden
	*   {
	*     "Insufficient User Rights"
	*   }
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Customer access not found
	*     HTTP/1.1 404 Not Found
	*     {
	*       "The're no custromer access for the project with id X"
	*     }
	*
	*/
	public function delCustomerAccessAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if (!array_key_exists('projectId', $content) && !array_key_exists('token', $content) && !array_key_exists('customerAccessId', $content))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$customerAccess = $em->getRepository('APIBundle:CustomerAccess')->find($content->customerAccessId);

		if ($customerAccess === null)
		{
			throw new NotFoundHttpException("The customer access with id ".$content->customerAccessId." doesn't exist");
		}

		$em->remove($customerAccess);
		$em->flush();

		return new JsonResponse("Customer access successfully remove!");
	}

	/**
	* @api {put} /V0.8/projects/addusertoproject Add a user to a project
	* @apiName addUserToProject
	* @apiGroup Project
	* @apiVersion 0.8.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {String} userEmail Email of the user
	*
	* @apiParamExample {json} Request-Example:
	* 	{
	*		"token": "nfeq34efbfkqf54",
	*		"projetcId": 2,
	*		"userEmail": "toto@titi.com"
	* 	}
	*
	* @apiSuccessExample Success-Response
	*     HTTP/1.1 200 OK
	*	  {
	*		"User successfully add!"
	*	  }
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample Missing Parameters
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Missing Parameter"
	* 	}
	*
	* @apiErrorExample Insufficient User Rights
	* 	HTTP/1.1 400 Forbidden
	* 	{
	* 		"Insufficient User Rights"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample No user found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The user with with email X@Y.Z doesn't exist"
	* 	}
	*
	*/
	public function addUserToProjectAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if (!array_key_exists('projectId', $content) && !array_key_exists('token', $content) && !array_key_exists('userEmail', $content))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($content->projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		$userToAdd = $em->getRepository('APIBundle:User')->findOneByemail($content->userEmail);
		if ($userToAdd === null)
		{
			throw new NotFoundHttpException("The user with email ".$content->userEmail." doesn't exist");
		}

		$project->addUser($userToAdd);
		$em->flush();

		return new JsonResponse("User successfully add!");
	}

	/**
	* @api {delete} /V0.8/projects/removeusertoproject Remove a user from the project
	* @apiName removeUserToProject
	* @apiGroup Project
	* @apiVersion 0.8.0
	*
	* @apiParam {String} token Token of the person connected
	* @apiParam {Number} projectId Id of the project
	* @apiParam {Number} userId Id of the user
	*
	* @apiParamExample {json} Request-Example:
	*   {
	*   	"token": "aeqf231ced651qcd",
	*   	"projectId": 1,
	*		"userId": 3
	*   }
	*
	* @apiSuccessExample Success-Response
	*	HTTP/1.1 200 OK
	*	{
	*	  "User successfully removed!"
	*	}
	*
	* @apiErrorExample Missing Parameter
	*   HTTP/1.1 400 Bad Request
	*   {
	*     "Missing Parameter"
	*   }
	* @apiErrorExample Bad Authentication Token
	*   HTTP/1.1 400 Bad Request
	*   {
	*     "Bad Authentication Token"
	*   }
	* @apiErrorExample Insufficient User Rights
	*   HTTP/1.1 403 Forbidden
	*   {
	*     "Insufficient User Rights"
	*   }
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
	* @apiErrorExample Project not found
	*     HTTP/1.1 404 Not Found
	*     {
	*       "The project with id X doesn't exist."
	*     }
	*
	* @apiErrorExample User not found
	*     HTTP/1.1 404 Not Found
	*     {
	*       "The user with id X doesn't exist."
	*     }
	*
	*/
	public function removeUserToProjectAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if (!array_key_exists('projectId', $content) && !array_key_exists('token', $content) && !array_key_exists('userId', $content))
			return $this->setBadRequest("Missing Parameter");
		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $content->projectId, "projectSettings"))
			return ($this->setNoRightsError());
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($content->projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		$userToRemove = $em->getRepository('APIBundle:User')->find($content->userId);
		if ($userToRemove === null)
		{
			throw new NotFoundHttpException("The user with id ".$content->userId." doesn't exist");
		}

		$project->removeUser($userToRemove);
		$em->flush();

		return new JsonResponse("User successfully removed!");
	}

	/**
  	* @api {get} /V0.8/projects/getusertoproject/:token/:projectId Get all the user on a project
  	* @apiName getUserToProject
  	* @apiGroup Project
  	* @apiVersion 0.8.0
  	*
  	* @apiParam {String} token Token of the person connected
  	* @apiParam {Number} projectId Id of the project
  	*
  	* @apiSuccess {Object[]} User Array of users
  	* @apiSuccess {Number} id Id of the user
  	* @apiSuccess {String} first_name First name of the user
  	* @apiSuccess {String} last_name Last name of the user
  	*
  	* @apiSuccessExample Success-Response:
  	* 	{
  	*		"User 1":
  	*		{
	*			"id": 3,
	*			"first_name": "John",
	*			"last_name": "Doe"
  	*		}
  	* 	}
  	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	* @apiErrorExample No project found
	* 	HTTP/1.1 404 Not found
	* 	{
	* 		"The project with id X doesn't exist"
	* 	}
	*
	* @apiErrorExample Invalid Method Value
	*     HTTP/1.1 404 Not Found
	*     {
	*       "message": "404 not found."
	*     }
  	*
  	*/
	public function getUserToProjectAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('APIBundle:Project')->find($projectId);

		if ($project === null)
		{
			throw new NotFoundHttpException("The project with id ".$content->projectId." doesn't exist");
		}

		$arr = array();
		$i = 1;

		$users = $project->getUsers();
		foreach ($users as $user) {
			$id = $user->getId();
			$firstName = $user->getFirstname();
			$lastName = $user->getLastname();

			$arr["User ".$i] = array("id" => $id, "first_name" => $firstName, "last_name" => $lastName);
			$i++;
		}

		return new JsonResponse($arr);
	}
}