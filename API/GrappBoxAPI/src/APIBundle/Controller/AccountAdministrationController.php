<?php

namespace APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use APIBundle\Controller\RolesAndTokenVerificationController;
use GrappboxBundle\Entity\Project;
use GrappboxBundle\Entity\User;
use DateTime;
use DateInterval;

//use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
class AccountAdministrationController extends RolesAndTokenVerificationController
{
	/**
	* @api {get} V0.6/accountadministration/login/:token Request login with client access
	* @apiName client login
	* @apiGroup AccountAdministration
	* @apiVersion 0.6.0
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

	/**
	* @api {get} V0.7/accountadministration/login/:token Request login with client access
	* @apiName client login
	* @apiGroup AccountAdministration
	* @apiVersion 0.7.0
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

	/**
	* @api {get} V0.8/accountadministration/login/:token Request login with client access
	* @apiName client login
	* @apiGroup AccountAdministration
	* @apiVersion 0.8.0
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

	/**
	* @api {get} V0.9/accountadministration/login/:token Request login with client access
	* @apiName client login
	* @apiGroup AccountAdministration
	* @apiVersion 0.9.0
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

	/**
	* @api {get} V0.10/accountadministration/login/:token Request login with client access
	* @apiName client login
	* @apiGroup AccountAdministration
	* @apiVersion 0.10.0
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

	/**
	* @api {get} V0.11/accountadministration/login/:token Request login with client access
	* @apiName client login
	* @apiGroup AccountAdministration
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
		$user = $em->getRepository('GrappboxBundle:User')->findOneBy(array('token' => $token));
		if (!$user || $user->getTokenValidity())
			return $this->setBadTokenError();

		$response = new JsonResponse();
		$response->setData(array('user' => $user->objectToArray()));
		return $response;
	}

	/**
 	* @api {post} V0.6/accountadministration/login Request login
 	* @apiName login
 	* @apiGroup AccountAdministration
 	* @apiVersion 0.6.0
 	*
 	* @apiParam {email} login login (user's email)
 	* @apiParam {string} password password
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
 	* @apiErrorExample Bad Login
 	* 	HTTP/1.1 400 Bad Request
 	* 	{
 	* 		"Bad Login"
 	* 	}
	* @apiErrorExample Bad Password
 	*		HTTP/1.1 400 Bad Request
  * 	{
  *   	"Bad Password"
  * 	}
 	*
 	*/

 	/**
 	* @api {post} V0.7/accountadministration/login Request login
 	* @apiName login
 	* @apiGroup AccountAdministration
 	* @apiVersion 0.7.0
 	*
 	* @apiParam {email} login login (user's email)
 	* @apiParam {string} password password
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
 	* @apiErrorExample Bad Login
 	* 	HTTP/1.1 400 Bad Request
 	* 	{
 	* 		"Bad Login"
 	* 	}
	* @apiErrorExample Bad Password
 	*		HTTP/1.1 400 Bad Request
  * 	{
  *   	"Bad Password"
  * 	}
 	*
 	*/

 	/**
 	* @api {post} V0.8/accountadministration/login Request login
 	* @apiName login
 	* @apiGroup AccountAdministration
 	* @apiVersion 0.8.0
 	*
 	* @apiParam {email} login login (user's email)
 	* @apiParam {string} password password
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
 	* @apiErrorExample Bad Login
 	* 	HTTP/1.1 400 Bad Request
 	* 	{
 	* 		"Bad Login"
 	* 	}
	* @apiErrorExample Bad Password
 	*		HTTP/1.1 400 Bad Request
  * 	{
  *   	"Bad Password"
  * 	}
 	*
 	*/

 	/**
 	* @api {post} V0.9/accountadministration/login Request login
 	* @apiName login
 	* @apiGroup AccountAdministration
 	* @apiVersion 0.9.0
 	*
 	* @apiParam {email} login login (user's email)
 	* @apiParam {string} password password
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
 	* @apiErrorExample Bad Login
 	* 	HTTP/1.1 400 Bad Request
 	* 	{
 	* 		"Bad Login"
 	* 	}
	* @apiErrorExample Bad Password
 	*		HTTP/1.1 400 Bad Request
  * 	{
  *   	"Bad Password"
  * 	}
 	*
 	*/

 	/**
 	* @api {post} V0.10/accountadministration/login Request login
 	* @apiName login
 	* @apiGroup AccountAdministration
 	* @apiVersion 0.10.0
 	*
 	* @apiParam {email} login login (user's email)
 	* @apiParam {string} password password
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
 	* @apiErrorExample Bad Login
 	* 	HTTP/1.1 400 Bad Request
 	* 	{
 	* 		"Bad Login"
 	* 	}
	* @apiErrorExample Bad Password
 	*		HTTP/1.1 400 Bad Request
  * 	{
  *   	"Bad Password"
  * 	}
 	*
 	*/

 	/**
 	* @api {post} V0.11/accountadministration/login Request login
 	* @apiName login
 	* @apiGroup AccountAdministration
 	* @apiVersion 0.11.0
 	*
 	* @apiParam {email} login login (user's email)
 	* @apiParam {string} password password
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
	*				"token": "fkE35dcDneOjF....",
	*				"avatar" : "XXXXXXXXXXXXXXXXX"
	*			}
 	* 	}
 	*
 	* @apiErrorExample Bad Login
 	* 	HTTP/1.1 400 Bad Request
 	* 	{
 	* 		"Bad Login"
 	* 	}
	* @apiErrorExample Bad Password
 	*		HTTP/1.1 400 Bad Request
  * 	{
  *   	"Bad Password"
  * 	}
 	*
 	*/
	public function loginAction(Request $request)
	{
			$content = $request->getContent();
			$content = json_decode($content);

		  $em = $this->getDoctrine()->getManager();
		  $user = $em->getRepository('GrappboxBundle:User')->findOneBy(array('email' => $content->login));
			if (!$user)
			{
				$response = new JsonResponse('Bad Login', JsonResponse::HTTP_BAD_REQUEST);
				return $response;
			}

			if ($this->container->get('security.password_encoder')->isPasswordValid($user, $content->password))
			{
					$secureUtils = $this->get('security.secure_random');
					$tmpToken = $secureUtils->nextBytes(25);
					$token = md5($tmpToken);
					$user->setToken($token);

					$now = new DateTime('now');
					$user->setTokenValidity($now->add(new DateInterval("P1D")));

					$em->persist($user);
		      $em->flush();

		      $this->checkProjectsDeletedTime($user);

					$response = new JsonResponse();
					$response->setData(array('user' => $user->objectToArray()));
					return $response;
			}
			else
			{
				$response = new JsonResponse('Bad Password', JsonResponse::HTTP_BAD_REQUEST);
				return $response;
			}
	}

	private function checkProjectsDeletedTime($user)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('GrappboxBundle:Project');

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
 * @api {get} V0.6/accountadministration/logout/:token Request logout
 * @apiName logout
 * @apiGroup AccountAdministration
 * @apiVersion 0.6.0
 *
 * @apiParam {string} token user's authentication token
 *
 * @apiSuccess {string} data	success message
 *
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"Logout Successfully"
 * 	}
 *
 * @apiErrorExample Bad Authentication Token
 * 	HTTP/1.1 400 Bad Request
 * 	{
 * 		"Bad Authentication Token"
 * 	}
 *
 */

	/**
 * @api {get} V0.7/accountadministration/logout/:token Request logout
 * @apiName logout
 * @apiGroup AccountAdministration
 * @apiVersion 0.7.0
 *
 * @apiParam {string} token user's authentication token
 *
 * @apiSuccess {string} data	success message
 *
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"Logout Successfully"
 * 	}
 *
 * @apiErrorExample Bad Authentication Token
 * 	HTTP/1.1 400 Bad Request
 * 	{
 * 		"Bad Authentication Token"
 * 	}
 *
 */

	/**
 * @api {get} V0.8/accountadministration/logout/:token Request logout
 * @apiName logout
 * @apiGroup AccountAdministration
 * @apiVersion 0.8.0
 *
 * @apiParam {string} token user's authentication token
 *
 * @apiSuccess {string} data	success message
 *
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"Logout Successfully"
 * 	}
 *
 * @apiErrorExample Bad Authentication Token
 * 	HTTP/1.1 400 Bad Request
 * 	{
 * 		"Bad Authentication Token"
 * 	}
 *
 */

	/**
 * @api {get} V0.9/accountadministration/logout/:token Request logout
 * @apiName logout
 * @apiGroup AccountAdministration
 * @apiVersion 0.9.0
 *
 * @apiParam {string} token user's authentication token
 *
 * @apiSuccess {string} data	success message
 *
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"Logout Successfully"
 * 	}
 *
 * @apiErrorExample Bad Authentication Token
 * 	HTTP/1.1 400 Bad Request
 * 	{
 * 		"Bad Authentication Token"
 * 	}
 *
 */

	/**
 * @api {get} V0.10/accountadministration/logout/:token Request logout
 * @apiName logout
 * @apiGroup AccountAdministration
 * @apiVersion 0.10.0
 *
 * @apiParam {string} token user's authentication token
 *
 * @apiSuccess {string} data	success message
 *
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"Logout Successfully"
 * 	}
 *
 * @apiErrorExample Bad Authentication Token
 * 	HTTP/1.1 400 Bad Request
 * 	{
 * 		"Bad Authentication Token"
 * 	}
 *
 */

	/**
 * @api {get} V0.11/accountadministration/logout/:token Request logout
 * @apiName logout
 * @apiGroup AccountAdministration
 * @apiVersion 0.11.0
 *
 * @apiParam {string} token user's authentication token
 *
 * @apiSuccess {string} data	success message
 *
 * @apiSuccessExample {json} Success-Response:
 * 	HTTP/1.1 200 OK
 * 	{
 * 		"Logout Successfully"
 * 	}
 *
 * @apiErrorExample Bad Authentication Token
 * 	HTTP/1.1 400 Bad Request
 * 	{
 * 		"Bad Authentication Token"
 * 	}
 *
 */
 	public function logoutAction(Request $request, $token)
 	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError());

		$user->setToken(null);

		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

		$response = new JsonResponse();
		$response->setData('Logout Successfully');
		return $response;
 	}

	/**
		* @api {post} V0.6/accountadministration/register Request user creation and login
		* @apiName register
		* @apiGroup AccountAdministration
		* @apiVersion 0.6.0
		*
		* @apiParam {string} firstname user's firstname
		* @apiParam {string} lastname user's lastname
		* @apiParam {DateTime} [birthday] user's birthday
		* @apiParam {file} [avatar] user's avatar
		* @apiParam {string} password user's password
		* @apiParam {email} email user's email
		* @apiParam {string} [phone] user's phone
		* @apiParam {string} [country] user's country
		* @apiParam {url} [linkedin] user's linkedin
		* @apiParam {url} [viadeo] user's viadeo
		* @apiParam {url} [twitter] user's twitter
		*
		* @apiSuccess {Object} user user's informations
		* @apiSuccess {int} user.id whiteboard id
		* @apiSuccess {string} user.firstname user's firstname
		* @apiSuccess {string} user.lastname user's lastname
		* @apiSuccess {string} user.email user's email
		* @apiSuccess {string} user.token user's authentication token
		*
		* @apiSuccessExample {json} Success-Response:
		* 	{
		*		"user": {
		*			"id": 12,
		*			"firstname": "John",
		*			"lastname": "Doe",
		*			"email": "john.doe@gmail.com",
		*			"token": "fkE35dcDneOjF...."
		*		}
		* 	}
		*
		* @apiErrorExample Missing Parameter
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Email Already Used
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Email already in DB"
	  * 	}
		*
		*/

	/**
	* @api {post} V0.7/accountadministration/register Request user creation and login
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiVersion 0.7.0
	*
	* @apiParam {string} firstname user's firstname
	* @apiParam {string} lastname user's lastname
	* @apiParam {DateTime} [birthday] user's birthday
	* @apiParam {file} [avatar] user's avatar
	* @apiParam {string} password user's password
	* @apiParam {email} email user's email
	* @apiParam {string} [phone] user's phone
	* @apiParam {string} [country] user's country
	* @apiParam {url} [linkedin] user's linkedin
	* @apiParam {url} [viadeo] user's viadeo
	* @apiParam {url} [twitter] user's twitter
	*
	* @apiSuccess {Object} user user's informations
	* @apiSuccess {int} user.id whiteboard id
	* @apiSuccess {string} user.firstname user's firstname
	* @apiSuccess {string} user.lastname user's lastname
	* @apiSuccess {string} user.email user's email
	* @apiSuccess {string} user.token user's authentication token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"user": {
	*			"id": 12,
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"email": "john.doe@gmail.com",
	*			"token": "fkE35dcDneOjF...."
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Email Already Used
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Email already in DB"
	  * 	}
		*
		*/

	/**
	* @api {post} V0.8/accountadministration/register Request user creation and login
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiVersion 0.8.0
	*
	* @apiParam {string} firstname user's firstname
	* @apiParam {string} lastname user's lastname
	* @apiParam {DateTime} [birthday] user's birthday
	* @apiParam {file} [avatar] user's avatar
	* @apiParam {string} password user's password
	* @apiParam {email} email user's email
	* @apiParam {string} [phone] user's phone
	* @apiParam {string} [country] user's country
	* @apiParam {url} [linkedin] user's linkedin
	* @apiParam {url} [viadeo] user's viadeo
	* @apiParam {url} [twitter] user's twitter
	*
	* @apiSuccess {Object} user user's informations
	* @apiSuccess {int} user.id whiteboard id
	* @apiSuccess {string} user.firstname user's firstname
	* @apiSuccess {string} user.lastname user's lastname
	* @apiSuccess {string} user.email user's email
	* @apiSuccess {string} user.token user's authentication token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"user": {
	*			"id": 12,
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"email": "john.doe@gmail.com",
	*			"token": "fkE35dcDneOjF...."
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Email Already Used
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Email already in DB"
	  * 	}
		*
		*/

	/**
	* @api {post} V0.9/accountadministration/register Request user creation and login
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiVersion 0.9.0
	*
	* @apiParam {string} firstname user's firstname
	* @apiParam {string} lastname user's lastname
	* @apiParam {DateTime} [birthday] user's birthday
	* @apiParam {file} [avatar] user's avatar
	* @apiParam {string} password user's password
	* @apiParam {email} email user's email
	* @apiParam {string} [phone] user's phone
	* @apiParam {string} [country] user's country
	* @apiParam {url} [linkedin] user's linkedin
	* @apiParam {url} [viadeo] user's viadeo
	* @apiParam {url} [twitter] user's twitter
	*
	* @apiSuccess {Object} user user's informations
	* @apiSuccess {int} user.id whiteboard id
	* @apiSuccess {string} user.firstname user's firstname
	* @apiSuccess {string} user.lastname user's lastname
	* @apiSuccess {string} user.email user's email
	* @apiSuccess {string} user.token user's authentication token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"user": {
	*			"id": 12,
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"email": "john.doe@gmail.com",
	*			"token": "fkE35dcDneOjF...."
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Email Already Used
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Email already in DB"
	  * 	}
		*
		*/

	/**
	* @api {post} V0.10/accountadministration/register Request user creation and login
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiVersion 0.10.0
	*
	* @apiParam {string} firstname user's firstname
	* @apiParam {string} lastname user's lastname
	* @apiParam {DateTime} [birthday] user's birthday
	* @apiParam {file} [avatar] user's avatar
	* @apiParam {string} password user's password
	* @apiParam {email} email user's email
	* @apiParam {string} [phone] user's phone
	* @apiParam {string} [country] user's country
	* @apiParam {url} [linkedin] user's linkedin
	* @apiParam {url} [viadeo] user's viadeo
	* @apiParam {url} [twitter] user's twitter
	*
	* @apiSuccess {Object} user user's informations
	* @apiSuccess {int} user.id whiteboard id
	* @apiSuccess {string} user.firstname user's firstname
	* @apiSuccess {string} user.lastname user's lastname
	* @apiSuccess {string} user.email user's email
	* @apiSuccess {string} user.token user's authentication token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"user": {
	*			"id": 12,
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"email": "john.doe@gmail.com",
	*			"token": "fkE35dcDneOjF...."
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Email Already Used
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Email already in DB"
	  * 	}
		*
		*/

	/**
	* @api {post} V0.11/accountadministration/register Request user creation and login
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiVersion 0.11.0
	*
	* @apiParam {string} firstname user's firstname
	* @apiParam {string} lastname user's lastname
	* @apiParam {DateTime} [birthday] user's birthday
	* @apiParam {file} [avatar] user's avatar
	* @apiParam {string} password user's password
	* @apiParam {email} email user's email
	* @apiParam {string} [phone] user's phone
	* @apiParam {string} [country] user's country
	* @apiParam {url} [linkedin] user's linkedin
	* @apiParam {url} [viadeo] user's viadeo
	* @apiParam {url} [twitter] user's twitter
	*
	* @apiSuccess {Object} user user's informations
	* @apiSuccess {int} user.id whiteboard id
	* @apiSuccess {string} user.firstname user's firstname
	* @apiSuccess {string} user.lastname user's lastname
	* @apiSuccess {string} user.email user's email
	* @apiSuccess {string} user.token user's authentication token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"user": {
	*			"id": 12,
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"email": "john.doe@gmail.com",
	*			"token": "fkE35dcDneOjF...."
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Email Already Used
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Email already in DB"
	  * 	}
		*
		*/

	/**
	* @api {post} V0.11/accountadministration/register Request user creation and login
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiVersion 0.11.1
	*
	* @apiParam {string} firstname user's firstname
	* @apiParam {string} lastname user's lastname
	* @apiParam {Date} [birthday] user's birthday
	* @apiParam {file} [avatar] user's avatar
	* @apiParam {string} password user's password
	* @apiParam {email} email user's email
	* @apiParam {string} [phone] user's phone
	* @apiParam {string} [country] user's country
	* @apiParam {url} [linkedin] user's linkedin
	* @apiParam {url} [viadeo] user's viadeo
	* @apiParam {url} [twitter] user's twitter
	*
	* @apiSuccess {Object} user user's informations
	* @apiSuccess {int} user.id whiteboard id
	* @apiSuccess {string} user.firstname user's firstname
	* @apiSuccess {string} user.lastname user's lastname
	* @apiSuccess {string} user.email user's email
	* @apiSuccess {string} user.token user's authentication token
	*
	* @apiSuccessExample {json} Success-Response:
	* 	{
	*		"user": {
	*			"id": 12,
	*			"firstname": "John",
	*			"lastname": "Doe",
	*			"email": "john.doe@gmail.com",
	*			"token": "fkE35dcDneOjF...."
	*		}
	* 	}
	*
	* @apiErrorExample Missing Parameter
 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Missing Parameter"
	  * 	}
		* @apiErrorExample Email Already Used
	 	* 	HTTP/1.1 400 Bad Request
	  * 	{
	  * 		"Email already in DB"
	  * 	}
		*
		*/
	public function registerAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if (!array_key_exists('firstname', $content) || !array_key_exists('lastname', $content) || !array_key_exists('password', $content) || !array_key_exists('email', $content))
			return $this->setBadRequest("Missing Parameter");
		$em = $this->getDoctrine()->getManager();
		if ($em->getRepository('GrappboxBundle:User')->findOneBy(array('email' => $content->email)))
			return $this->setBadRequest("Email already in DB");
		$user = new User();
    $user->setFirstname($content->firstname);
    $user->setLastname($content->lastname);

		if (array_key_exists('birthday', $content))
			$user->setBirthday(date_create($content->birthday));

		if ($request->files->get('avatar'))
		{
			$generator = $this->get('security.secure_random');
	    $random = $generator->nextBytes(10);
	    $fileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/avatars';
	    $fileName= md5($random).'.'.$request->files->get('avatar')->guessExtension();
	    $avatar = $request->files->get('avatar')->move($fileDir, $fileName);

	    $user->setAvatar($fileDir.'/'.$fileName);
		}

    $encoder = $this->container->get('security.password_encoder');
    $encoded = $encoder->encodePassword($user, $content->password);
    $user->setPassword($encoded);

		$user->setEmail($content->email);
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

		$response = new JsonResponse();
		$response->setData(array('user' => $user->objectToArray()));
		return $response;
	}
}
