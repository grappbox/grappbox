<?php

namespace SQLBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use SQLBundle\Controller\RolesAndTokenVerificationController;
use SQLBundle\Entity\Project;
use SQLBundle\Entity\User;
use SQLBundle\Entity\Authentication;
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
	/**
	* @api {get} /0.3/accountadministration/login/:token Client login
	* @apiName clientlogin
	* @apiGroup AccountAdministration
	* @apiDescription log user with client token
	* @apiVersion 0.3.0
	*
	* @apiParam {token} client token access
	*
	* @apiSuccess {int} id whiteboard id
	* @apiSuccess {string} firstname user's firstname
	* @apiSuccess {string} lastname user's lastname
	* @apiSuccess {string} email user's email
	* @apiSuccess {string} token user's authentication token
	* @apiSuccess {String} avatar user's avatar last modification date
	* @apiSuccess {Boolean} is_client if the user is a client
	*
	* @apiSuccessExample {json} Success-Response:
 	* 	{
	*			"info": {
	*				"return_code": "1.14.1",
	*				"return_message": "AccountAdministration - clientlogin - Complete Success"
  	*			},
 	*			"data": {
	*				"id": 12,
	*				"firstname": "John",
	*				"lastname": "Doe",
	*				"email": "john.doe@gmail.com",
	*				"token": "fkE35dcDneOjF....",
	*				"avatar": "1945-06-18 06:00:00",
	*				"is_client": false
	*			}
 	* 	}
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "14.4.3",
	*			"return_message": "AccountAdministration - clientlogin - Bad id"
	*		}
	* 	}
	*
	*/
	/**
	* @api {get} /V0.2/accountadministration/login/:token Client login
	* @apiName clientlogin
	* @apiGroup AccountAdministration
	* @apiDescription log user with client token
	* @apiVersion 0.2.0
	*
	* @apiParam {token} client token access
	*
	* @apiSuccess {int} id whiteboard id
	* @apiSuccess {string} firstname user's firstname
	* @apiSuccess {string} lastname user's lastname
	* @apiSuccess {string} email user's email
	* @apiSuccess {string} token user's authentication token
	* @apiSuccess {Date} avatar user's avatar last modification date
	*
	* @apiSuccessExample {json} Success-Response:
 	* 	{
	*			"info": {
	*				"return_code": "1.14.1",
	*				"return_message": "AccountAdministration - clientlogin - Complete Success"
	*			},
 	*			"data": {
	*				"id": 12,
	*				"firstname": "John",
	*				"lastname": "Doe",
	*				"email": "john.doe@gmail.com",
	*				"token": "fkE35dcDneOjF....",
	*				"avatar": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
	*			}
 	* 	}
	*
	* @apiErrorExample Bad Id
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "14.4.3",
	*			"return_message": "AccountAdministration - clientlogin - Bad id"
	*		}
	* 	}
	*
	*/
	public function clientLoginAction(Request $request, $token)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('SQLBundle:User')->findOneBy(array('token' => $token));
		if (!$user || $user->getTokenValidity())
			return $this->setBadTokenError("14.4.3", "AccountAdministration", "clientLogin");

		return $this->setSuccess("1.14.1", "AccountAdministration", "clientLogin", "Complete Success", $user->objectToArray());
	}
 	/**
 	* @api {post} /0.3/accountadministration/login Login
 	* @apiName login
 	* @apiGroup AccountAdministration
	* @apiDescription Log user from his login and password
 	* @apiVersion 0.3.0
 	*
 	* @apiParam {string} login login (user's email)
 	* @apiParam {string} password password
	* @apiParam {string} mac MAC address of the device (equals null if flag = 'web')
	* @apiParam {string} flag device flag (web, and, ios, wph, desk)
	* @apiParam {string} device_name name of the device
 	*
	* @apiParamExample {json} Request-Example:
	*   {
	*		"data": {
	*   		"login": "john.doe@gmail.com",
	*   		"password": "ThisisAPassword",
	*   		"mac": "XXXXXXXXXXXXXX",
	*   		"flag": "and",
	*   		"device_name": "John Doe's phone"
	*		}
	*   }
	*
 	* @apiSuccess {int} id user's id
 	* @apiSuccess {string} firstname user's firstname
 	* @apiSuccess {string} lastname user's lastname
 	* @apiSuccess {string} email user's email
	* @apiSuccess {string} avatar user's avatar last modif date
	* @apiSuccess {Boolean} is_client if the user is a client
	* @apiSuccess {string} token user's authentication token
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
	*				"avatar": "1945-06-18 06:00:00",
	*				"is_client": false,
	*				"token": "fkE35dcDneOjF....",
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
	* @apiErrorExample Missing Parameter
	* 	HTTP/1.1 400 Bad Request
	* 	{
	*		"info": {
	*			"return_code": "14.1.6",
	*			"return_message": "AccountAdministration - login - Missing Parameter"
	*		}
	* 	}
 	*
 	*/
 	/**
 	* @api {post} /V0.2/accountadministration/login Login
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
	* @apiSuccess {date} avatar user's avatar last modif date
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
	*				"avatar": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
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
		$user = $em->getRepository('SQLBundle:User')->findOneBy(array('email' => $content->login));
		if (!$user)
			return $this->setBadRequest("14.1.4", "AccountAdministration", "login", "Bad Parameter: login");

		if (!($this->container->get('security.password_encoder')->isPasswordValid($user, $content->password)))
			return $this->setBadRequest("14.1.4", "AccountAdministration", "login", "Bad Parameter: password");

		$auth = $em->getRepository('SQLBundle:Authentication')->findOneBy(array('deviceFlag' => $content->flag, 'macAddr' => $content->mac));
		if ($auth instanceof Authentication && $content->flag != "web")
		{
			if ($content->device_name != $auth->getDeviceName()) {
				$auth->setDeviceName($content->device_name);
				$em->persist($auth);
				$em->flush();
			}
		}
		else {
			$auth = new Authentication();
			$auth->setuser($user);
			$auth->setMacAddr($content->mac);
			$auth->setDeviceFlag($content->flag);
			$auth->setDeviceName($content->device_name);
			$em->persist($auth);
			$em->flush();
		}

		$now = new DateTime('now');
		if ($auth->getToken() && $auth->getTokenValidity() > $now)
			{
				$auth->setTokenValidity($now->add(new DateInterval("P1D")));

				$em->persist($auth);
				$em->flush();

				$userObj = $user->objectToArray();
				$userObj['token'] = $auth->getToken();
				return $this->setSuccess("1.14.1", "AccountAdministration", "login", "Complete Success", $userObj);
			}

		$secureUtils = $this->get('security.secure_random');
		$tmpToken = $secureUtils->nextBytes(25);
		$token = md5($tmpToken);
		$auth->setToken($token);
		$auth->setTokenValidity($now->add(new DateInterval("P1D")));

		$em->persist($auth);
		$em->flush();

		$this->checkProjectsDeletedTime($user);

		$userObj = $user->objectToArray();
		$userObj['token'] = $auth->getToken();
		return $this->setSuccess("1.14.1", "AccountAdministration", "login", "Complete Success", $userObj);
	}

	private function checkProjectsDeletedTime($user)
	{
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SQLBundle:Project');

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
						$purs = $em->getRepository('SQLBundle:ProjectUserRole')->findByprojectId($project->getId());

						foreach ($purs as $pur) {
							$em->remove($pur);
						}
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
								$purs = $em->getRepository('SQLBundle:projectUserRole')->findByprojectId($project->getId());

								foreach ($purs as $pur) {
									$em->remove($pur);
								}
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
	* @api {get} /0.3/accountadministration/logout/:token Logout
	* @apiName logout
	* @apiGroup AccountAdministration
	* @apiDescription unvalid user's token
	* @apiVersion 0.3.0
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
	/**
	* @api {get} /V0.2/accountadministration/logout/:token Logout
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

		$em = $this->getDoctrine()->getManager();
		$auth = $em->getRepository('SQLBundle:Authentication')->findOneBy(array('user' => $user, 'token' => $token));
		if (!($auth instanceof Authentication))
			return ($this->setBadTokenError("14.2.3", "AccountAdministration", "logout"));

		$auth->setToken(null);

		$em->persist($auth);
		$em->flush();

		return $this->setSuccess("1.14.1", "AccountAdministration", "logout", "Complete Success", array("message" => "Successfully Logout"));
 	}

	/**
	* @api {post} /0.3/accountadministration/register Register
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiDescription Register a new user and log him
	* @apiVersion 0.3.0
	*
	* @apiParam {string} firstname user's firstname
	* @apiParam {string} password user's password
	* @apiParam {email} email user's email
	* @apiParam {string} lastname user's lastname
	* @apiParam {Date} [birthday] user's birthday
	* @apiParam {boolean} is_client If the user is a client
	* @apiParam {string} mac MAC address of the device (equals null if flag = 'web')
	* @apiParam {string} flag device flag (web, and, ios, wph, desk)
	* @apiParam {string} device_name name of the device
	*
	* @apiParamExample {json} Request-Example Minimum:
	*   {
	*   	"data": {
	*   		"firstname": "Janne",
	*   		"lastname": "Doe",
	*   		"email": "janne.doe@gmail.com",
	*   		"password": "ThisisAPassword",
	*				"is_client": false,
	*				"mac": "XXXXXXXXXXXXXXXXXXXXXX",
	*				"flag": "desk",
	*				"device_name": "John's Desktop"
	*   	}
	*   }
	* @apiParamExample {json} Request-Example Partial:
	*   {
	*   	"data": {
	*   		"firstname": "Janne",
	*   		"lastname": "Doe",
	*   		"email": "janne.doe@gmail.com",
	*   		"password": "ThisisAPassword",
	*   		"is_client": false,
	*   		"avatar": "100100111010011110100100.......",
	*   		"phone": "010-1658-9520",
	*   		"country": "New Caledonia",
	*				"mac": "XXXXXXXXXXXXXXXXXXXXXX",
	*				"flag": "desk",
	*				"device_name": "John's Desktop"
	*   	}
	*   }
	* @apiParamExample {json} Request-Example Full:
	*   {
	*   	"data": {
	*   		"firstname": "Janne",
	*   		"lastname": "Doe",
	*   		"email": "janne.doe@gmail.com",
	*   		"password": "ThisisAPassword",
	*   		"is_client": false,
	*   		"birthday": "1980-12-04",
	*   		"avatar": "100100111010011110100100.......",
	*   		"phone": "010-1658-9520",
	*   		"country": "New Caledonia",
	*   		"linkedin": "linkedin.com/janne.doe"
	*   		"viadeo": "viadeo.com/janne.doe",
	*   		"twitter": "twitter.com/janne.doe",
	*				"mac": "XXXXXXXXXXXXXXXXXXXXXX",
	*				"flag": "desk",
	*				"device_name": "John's Desktop"
	*   	}
	*   }
	*
	* @apiSuccess {int} id user's id
 	* @apiSuccess {string} firstname user's firstname
 	* @apiSuccess {string} lastname user's lastname
 	* @apiSuccess {string} email user's email
	* @apiSuccess {string} avatar user's avatar last modification date
	* @apiSuccess {Boolean} is_client if the user is a client
	* @apiSuccess {string} token user's authentication token
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
	*				"avatar": "1945-06-18 06:00:00",
	*				"is_client": false,
	*				"token": "fkE35dcDneOjF...."
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
	/**
	* @api {post} /V0.2/accountadministration/register Register
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
	*   		"password": "ThisisAPassword"
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
	*   		"password": "ThisisAPassword"
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
	* @apiSuccess {Date} avatar user's avatar last modification date
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
	*				"avatar": {"date": "1945-06-18 06:00:00", "timezone_type": 3, "timezone": "Europe\/Paris"}
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

		if (!array_key_exists('firstname', $content) || !array_key_exists('lastname', $content)
				|| !array_key_exists('password', $content) || !array_key_exists('email', $content)
				|| !array_key_exists('is_client', $content) || !array_key_exists('mac', $content)
				|| !array_key_exists('flag', $content) || !array_key_exists('device_name', $content))
			return $this->setBadRequest("14.3.6", "AccountAdministration", "register", "Missing Parameter");

		$em = $this->getDoctrine()->getManager();
		if ($em->getRepository('SQLBundle:User')->findOneBy(array('email' => $content->email)))
			return $this->setBadRequest("14.3.7", "AccountAdministration", "register", "Already in Database");

		$user = new User();
		$user->setFirstname($content->firstname);
		$user->setLastname($content->lastname);
		$user->setEmail($content->email);
		$user->setIsClient($content->is_client);

		$encoder = $this->container->get('security.password_encoder');
		$encoded = $encoder->encodePassword($user, $content->password);
		$user->setPassword($encoded);

		if (array_key_exists('birthday', $content))
			$user->setBirthday(date_create($content->birthday));

		$em->persist($user);
		$em->flush();

		$auth = new Authentication();
		$auth->setUser($user);
		$auth->setMacAddr($content->mac);
		$auth->setDeviceFlag($content->flag);
		$auth->setDeviceName($content->device_name);

		$now = new DateTime('now');

		$secureUtils = $this->get('security.secure_random');
		$tmpToken = $secureUtils->nextBytes(25);
		$token = md5($tmpToken);
		$auth->setToken($token);
		$auth->setTokenValidity($now->add(new DateInterval("P1D")));

		$em->persist($auth);
		$em->flush();

		$userObj = $user->objectToArray();
		$userObj['token'] = $auth->getToken();
		return $this->setCreated("1.14.1", "AccountAdministration", "register", "Complete Success", $userObj);
	}
}
