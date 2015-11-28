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
}