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
use MongoBundle\Document\Authentication;
use MongoBundle\Document\Newsletter;
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
 *  @IgnoreAnnotation("apiHeader")
 *  @IgnoreAnnotation("apiHeaderExample")
 */
class AccountAdministrationController extends RolesAndTokenVerificationController
{
	/**
	* @-api {post} /0.3/account/preorder Preorder newsletter
 	* @apiName preorder
 	* @apiGroup AccountAdministration
	* @apiDescription Set a mail adress for the newsletter
 	* @apiVersion 0.3.0
	*/
	public function preorderAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;
		$em = $this->get('doctrine_mongodb')->getManager();

		if (!array_key_exists("email", $content) || !array_key_exists("firstname", $content) || !array_key_exists("lastname", $content))
				return $this->setBadRequest("14.1.6", "AccountAdministration", "preorder", "Missing Parameter");

		if ($em->getRepository('MongoBundle:Newsletter')->findOneBy(array('email' => $content->email)))
			return $this->setBadRequest("14.1.7", "AccountAdministration", "preorder", "Already in Database");

		$mail = new Newsletter();
		$mail->setEmail($content->email);
		$mail->setFirstname($content->firstname);
		$mail->setLastname($content->lastname);

		$em->persist($mail);
		$em->flush();

		return $this->setSuccess("1.14.1", "AccountAdministration", "preorder", "Complete Success", null);
	}

	/**
	* @-api {get} /0.3/account/login Client login
	* @apiName clientlogin
	* @apiGroup AccountAdministration
	* @apiDescription log user with client token
	* @apiVersion 0.3.0
	*
	*/
	public function clientLoginAction(Request $request)
	{
		$em = $this->get('doctrine_mongodb')->getManager();
		$user = $em->getRepository('MongoBundle:User')->findOneBy(array('token' => $request->headers->get('Authorization')));
		if (!$user || $user->getTokenValidity())
			return $this->setBadTokenError("14.4.3", "AccountAdministration", "clientLogin");

		return $this->setSuccess("1.14.1", "AccountAdministration", "clientLogin", "Complete Success", $user->objectToArray());
	}

 	/**
	* @-api {post} /0.3/account/login Login
 	* @apiName login
 	* @apiGroup AccountAdministration
	* @apiDescription Log user from his login and password
 	* @apiVersion 0.3.0
	*
 	*/
	public function loginAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('login', $content) || !array_key_exists('password', $content)
				|| !array_key_exists('flag', $content) || !array_key_exists('mac', $content)
				|| !array_key_exists('device_name', $content))
			return $this->setBadRequest("14.1.6", "AccountAdministration", "register", "Missing Parameter");

		if ($content->flag == "" || $content->device_name == "")
      return $this->setBadRequest("14.1.4", "AccountAdministration", "register", "Bad Parameter: flag or device_name");

	  $em = $this->get('doctrine_mongodb')->getManager();
	  $user = $em->getRepository('MongoBundle:User')->findOneBy(array('email' => $content->login));
		if (!$user)
			return $this->setBadRequest("14.1.4", "AccountAdministration", "login", "Bad Parameter: login");

		if (!($this->container->get('security.password_encoder')->isPasswordValid($user, $content->password)))
			return $this->setBadRequest("14.1.4", "AccountAdministration", "login", "Bad Parameter: password");

		$auth = $em->getRepository('MongoBundle:Authentication')->findOneBy(array('user' => $user->getId(), 'deviceFlag' => $content->flag, 'macAddr' => $content->mac));
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

			$em->persist($user);
      $em->flush();

			$userObj = $user->objectToArray();
			$userObj['token'] = $auth->getToken();
			return $this->setSuccess("1.14.1", "AccountAdministration", "login", "Complete Success", $userObj);
		}

		$tmpToken = random_bytes(25);
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
		$em = $this->get('doctrine_mongodb')->getManager();
		$repository = $em->getRepository('MongoBundle:Project');

		$qb = $repository->createQueryBuilder('p');

		$projects = $qb->getQuery()->execute();
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
						$purs = $em->getRepository('MongoBundle:ProjectUserRole')->findByProjectId($project->getId());

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
								$purs = $em->getRepository('MongoBundle:projectUserRole')->findByProjectId($project->getId());

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
	* @-api {get} /0.3/account/logout Logout
	* @apiName logout
	* @apiGroup AccountAdministration
	* @apiDescription Log out user
	* @apiVersion 0.3.0
  *
  */
 	public function logoutAction(Request $request)
 	{
		$token = $request->headers->get('Authorization');
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("14.2.3", "AccountAdministration", "logout"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$auth = $em->getRepository('MongoBundle:Authentication')->findOneBy(array('user' => $user, 'token' => $token));
		if (!($auth instanceof Authentication))
			return ($this->setBadTokenError("14.2.3", "AccountAdministration", "logout"));

		$em->remove($auth);
		$em->flush();

		return $this->setSuccess("1.14.1", "AccountAdministration", "logout", "Complete Success", array("message" => "Successfully Logout"));
 	}

	/**
	* @-api {post} /0.3/account/register Register
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiDescription Register a new user and log him
	* @apiVersion 0.3.0
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

    if ($content->flag == "" || $content->device_name == "")
      return $this->setBadRequest("14.3.6", "AccountAdministration", "register", "Bad Parameter");

		$em = $this->get('doctrine_mongodb')->getManager();
		if ($em->getRepository('MongoBundle:User')->findOneBy(array('email' => $content->email)))
			return $this->setBadRequest("14.3.4", "AccountAdministration", "register", "Already in Database");

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

		$tmpToken = random_bytes(25);
		$token = md5($tmpToken);
		$auth->setToken($token);
		$auth->setTokenValidity($now->add(new DateInterval("P1D")));

    $em->persist($user);
    $em->flush();

		$userObj = $user->objectToArray();
		$userObj['token'] = $auth->getToken();
		return $this->setCreated("1.14.1", "AccountAdministration", "register", "Complete Success", $userObj);
		}
}
