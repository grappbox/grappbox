<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use APIBundle\Entity\Project;

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
	* @apiIgnore Not finished Method
	* @api {post} /V0.7/projects/projectcreation/:token Create a project for the user connected
	* @apiName projectCreation
	* @apiGroup Project
	* @apiVersion 0.7.0
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
			// $encoder = $this->container->get('security.password_encoder');
			// $encoded = $encoder->encodePassword($project, $content->password);
			$project->setSafePassword($content->password);
		}

		$em->persist($project);
		$em->flush();
		$id = $project->getId();

		return new JsonResponse(array("project_id" => $id));
	}

	/**
	* @api {put} /V0.7/projects/updateinformations Update a project informations
	* @apiName updateInformations
	* @apiGroup Project
	* @apiVersion 0.7.0
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
			// $encoder = $this->container->get('security.password_encoder');
			// $encoded = $encoder->encodePassword($project, $content->password);
			$project->setSafePassword($content->password);
		}

		$em->flush();

		return new JsonResponse("Update project informations Success.");
	}

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

		return new JsonResponse("The project will be deleted at ".$delDate->format('Y-m-d-H-i-s'));
	}
}