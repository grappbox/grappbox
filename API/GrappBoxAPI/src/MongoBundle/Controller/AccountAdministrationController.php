<?php

namespace MongoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use MongoBundle\Controller\RolesAndTokenVerificationController;
use MongoBundle\Document\Project;
use MongoBundle\Document\User;
use DateTime;
use DateInterval;

/**
 *  @IgnoreAnnotation("apiName")
 *  @IgnoreAnnotation("apiGroup")
 *	@IgnoreAnnotation("apiDescription")
 *  @IgnoreAnnotation("apiVersion")
 *  @IgnoreAnnotation("apiSuccess")
 *  @IgnoreAnnotation("apiSuccessExample")
 *  @IgnoreAnnotation("apiError")
 *  @IgnoreAnnotation("apiErrorExample")
 *  @IgnoreAnnotation("apiParam")
 *  @IgnoreAnnotation("apiParamExample")
 */
class AccountAdministrationController extends RolesAndTokenVerificationController
{
	// TODO is it used ? can it be deleted ?

	/**
	* @-api {get} mongo/accountadministration/login/:token Request login with client access
	* @apiName client login
	* @apiGroup AccountAdministration
	* @apiDescription log user with client token
	* @apiVersion 0.11.0
	*
	* @apiParam {token} client token access
	*
	* @apiSuccess {Object} user user's information
	* @apiSuccess {int} user.id whiteboard id
	* @apiSuccess {string} user.firstname user's firstname
	* @apiSuccess {string} user.lastname user's lastname
	* @apiSuccess {string} user.email user's email
	* @apiSuccess {string} user.token user's authentication token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*			"user": {
	*				"id": 12,
	*				"firstname": "John",
	*				"lastname": "Doe",
	*				"email": "john.doe@gmail.com",
	*				"token": "fkE35dcDneOjF...."
	*			}
	* 	}
	*
	* @apiErrorExample Bad Authentication Token
	* 	HTTP/1.1 400 Bad Request
	* 	{
	* 		"Bad Authentication Token"
	* 	}
	*
	*/
	public function clientLoginAction(Request $request, $token)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('MongoBundle:User')->findOneBy(array('token' => $token));
		if (!$user || $user->getTokenValidity())
			return $this->setBadTokenError();

		$response = new JsonResponse();
		$response->setData(array('user' => $user->objectToArray()));
		return $response;
	}

 	/**
 	* @api {post} V0.2/accountadministration/login Login
 	* @apiName login
 	* @apiGroup AccountAdministration
	* @apiDescription Log user from his login and password
 	* @apiVersion 0.2.0
 	*
 	* @apiParam {string} login login (user's email)
 	* @apiParam {string} password password
 	*
	* @apiParamExample {json} Request-Example:
	*   {
	*		"data": {
	*   		"login": "john.doe@gmail.com",
	*   		"password": "ThisisAPassword"
	*		}
	*   }
	*
 	* @apiSuccess {int} id user's id
 	* @apiSuccess {string} firstname user's firstname
 	* @apiSuccess {string} lastname user's lastname
 	* @apiSuccess {string} email user's email
	* @apiSuccess {string} token user's authentication token
	* @apiSuccess {String} avatar user's avatar
 	*
 	* @apiSuccessExample {json} Success-Response:
 	* 	{
	*			"info": {
	*				"return_code": "1.14.1",
	*				"return_message": "AccountAdministration - login - Complete Success"
  *			},
 	*			"data": {
	*				"id": 12,
	*				"firstname": "John",
	*				"lastname": "Doe",
	*				"email": "john.doe@gmail.com",
	*				"token": "fkE35dcDneOjF....",
	*				"avatar": "01101000101101000111...."
	*			}
 	* 	}
 	*
	* @apiErrorExample Bad Parameter: login
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "14.1.4",
	*			"return_message": "AccountAdministration - login - Bad Parameter: login"
  *		}
	* 	}
	* @apiErrorExample Bad Parameter: password
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "14.1.4",
	*			"return_message": "AccountAdministration - login - Bad Parameter: password"
  *		}
	* 	}
 	*
 	*/
	public function loginAction(Request $request)
	{
			$content = $request->getContent();
			$content = json_decode($content);
			$content = $content->data;

		  $em = $this->getDoctrine()->getManager();
		  $user = $em->getRepository('MongoBundle:User')->findOneBy(array('email' => $content->login));
			if (!$user)
				return $this->setBadRequest("14.1.4", "AccountAdministration", "login", "Bad Parameter: login");

			if (!($this->container->get('security.password_encoder')->isPasswordValid($user, $content->password)))
				return $this->setBadRequest("14.1.4", "AccountAdministration", "login", "Bad Parameter: password");

			$secureUtils = $this->get('security.secure_random');
			$tmpToken = $secureUtils->nextBytes(25);
			$token = md5($tmpToken);
			$user->setToken($token);

			$now = new DateTime('now');
			$user->setTokenValidity($now->add(new DateInterval("P1D")));

			$em->persist($user);
      $em->flush();

      $this->checkProjectsDeletedTime($user);

			return $this->setSuccess("1.14.1", "AccountAdministration", "login", "Complete Success", $user->objectToArray());
	}

	private function checkProjectsDeletedTime($user)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('MongoBundle:Project');

		$qb = $repository->createQueryBuilder('p');

		$projects = $qb->getQuery()->getResult();
		$nullDate = date_create("0000-00-00 00:00:00");
		$defDate = new \DateTime;

		if ($projects === null)
			return;

		foreach ($projects as $project) {
			$creatorId = $project->getCreatorUser()->getId();

			if ($creatorId == $user->getId())
			{
				if ($project->getDeletedAt() != $nullDate && $project->getDeletedAt() != null)
				{
					if ($project->getDeletedAt() < $defDate)
					{
						$em->remove($project);
					}
				}
			}
			else
			{
				$projectUsers = $project->getUsers();

				foreach ($projectUsers as $projectUser) {
					$userId = $projectUser->getId();

					if ($userId == $user->getId())
					{
						if ($project->getDeletedAt() != $nullDate && $project->getDeletedAt() != null)
						{
							if ($project->getDeletedAt() < $defDate)
							{
								$em->remove($project);
							}
						}
					}
				}
			}
		}

		$em->flush();
	}

	/**
 * @api {get} V0.2/accountadministration/logout/:token Logout
 * @apiName logout
 * @apiGroup AccountAdministration
 * @apiDescription unvalid user's token
 * @apiVersion 0.2.0
 *
 * @apiParam {string} token user's authentication token
 *
 * @apiSuccess {string} message	success message
 *
 * @apiSuccessExample {json} Success-Response:
 * 	{
 *			"info": {
 *				"return_code": "1.14.1",
 *				"return_message": "AccountAdministration - login - Complete Success"
 *			},
 *			"data": {
 *				"message": "Successfully logout"
 *			}
 * 	}
 *
 * @apiErrorExample Bad Id
 * 	HTTP/1.1 400 Bad Request
 * 	{
 *		"info": {
 *			"return_code": "14.2.3",
 *			"return_message": "AccountAdministration - logout - Bad id"
 *		}
 * 	}
 *
 */
 	public function logoutAction(Request $request, $token)
 	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("14.2.3", "AccountAdministration", "logout"));

		$user->setToken(null);

		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

		return $this->setSuccess("1.14.1", "AccountAdministration", "logout", array("message" => "Successfully Logout"));
 	}

	/**
	* @api {post} V0.2/accountadministration/register Register
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiDescription Register a new user and log him
	* @apiVersion 0.2.0
	*
	* @apiParam {string} firstname user's firstname
	* @apiParam {string} password user's password
	* @apiParam {email} email user's email
	* @apiParam {string} lastname user's lastname
	* @apiParam {Date} [birthday] user's birthday
	* @apiParam {file} [avatar] user's avatar
	* @apiParam {string} [phone] user's phone
	* @apiParam {string} [country] user's country
	* @apiParam {url} [linkedin] user's linkedin
	* @apiParam {url} [viadeo] user's viadeo
	* @apiParam {url} [twitter] user's twitter
	*
	* @apiParamExample {json} Request-Example Minimum:
	*   {
	*   	"data": {
	*   		"firstname": "Janne",
	*   		"lastname": "Doe",
	*   		"email": "janne.doe@gmail.com",
	*   		"password": "ThisisAPassword"
	*   	}
	*   }
	* @apiParamExample {json} Request-Example Partial:
	*   {
	*   	"data": {
	*   		"firstname": "Janne",
	*   		"lastname": "Doe",
	*   		"email": "janne.doe@gmail.com",
	*   		"password": "ThisisAPassword",
	*   		"avatar": "100100111010011110100100.......",
	*   		"phone": "010-1658-9520",
	*   		"country": "New Caledonia"
	*   	}
	*   }
	* @apiParamExample {json} Request-Example Full:
	*   {
	*   	"data": {
	*   		"firstname": "Janne",
	*   		"lastname": "Doe",
	*   		"email": "janne.doe@gmail.com",
	*   		"password": "ThisisAPassword",
	*   		"birthday": "1980-12-04",
	*   		"avatar": "100100111010011110100100.......",
	*   		"phone": "010-1658-9520",
	*   		"country": "New Caledonia",
	*   		"linkedin": "linkedin.com/janne.doe"
	*   		"viadeo": "viadeo.com/janne.doe",
	*   		"twitter": "twitter.com/janne.doe"
	*   	}
	*   }
	*
	* @apiSuccess {int} id user's id
 	* @apiSuccess {string} firstname user's firstname
 	* @apiSuccess {string} lastname user's lastname
 	* @apiSuccess {string} email user's email
	* @apiSuccess {string} token user's authentication token
	* @apiSuccess {String} avatar user's avatar
	*
	* @apiSuccessExample {json} Success-Response:
	* 	HTTP/1.1 201 Created
	* 	{
	*			"info": {
	*				"return_code": "1.14.1",
	*				"return_message": "AccountAdministration - register - Complete Success"
  *			},
 	*			"data": {
	*				"id": 12,
	*				"firstname": "Janne",
	*				"lastname": "Doe",
	*				"email": "janne.doe@gmail.com",
	*				"token": "fkE35dcDneOjF....",
	*				"avatar": "01101000101101000111...."
	*			}
	* 	}
	*
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "14.3.6",
	*			"return_message": "AccountAdministration - register - Missing Parameter"
  *		}
	* 	}
	* @apiErrorExample Already in DB
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "14.3.7",
	*			"return_message": "AccountAdministration - register - Already in Database"
  *		}
	* 	}
	*
	*/
	public function registerAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('firstname', $content) || !array_key_exists('lastname', $content) || !array_key_exists('password', $content) || !array_key_exists('email', $content))
			return $this->setBadRequest("14.3.6", "AccountAdministration", "register", "Missing Parameter");

		$em = $this->getDoctrine()->getManager();
		if ($em->getRepository('MongoBundle:User')->findOneBy(array('email' => $content->email)))
			return $this->setBadRequest("14.3.7", "AccountAdministration", "register", "Already in Database");

		$user = new User();
    $user->setFirstname($content->firstname);
    $user->setLastname($content->lastname);
		$user->setEmail($content->email);

		$encoder = $this->container->get('security.password_encoder');
    $encoded = $encoder->encodePassword($user, $content->password);
    $user->setPassword($encoded);

		if (array_key_exists('avatar', $content))
			$user->setAvatar(date_create($content->avatar));
		if (array_key_exists('birthday', $content))
			$user->setBirthday(date_create($content->birthday));
		if (array_key_exists('phone', $content))
    	$user->setPhone($content->phone);
		if (array_key_exists('country', $content))
      $user->setCountry($content->country);
		if (array_key_exists('linkedin', $content))
      $user->setLinkedin($content->linkedin);
		if (array_key_exists('viadeo', $content))
      $user->setViadeo($content->viadeo);
		if (array_key_exists('twitter', $content))
      $user->setTwitter($content->twitter);

		$secureUtils = $this->get('security.secure_random');
		$tmpToken = $secureUtils->nextBytes(25);
		$token = md5($tmpToken);
		$user->setToken($token);

    $em->persist($user);
    $em->flush();

		return $this->setCreated("1.14.1", "AccountAdministration", "register", "Complete Success", $user->objectToArray());
	}
}
