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

	/**
	* @-api {get} mongo/accountadministration/login/:token Request login with client access
	* @apiName client login
	* @apiGroup AccountAdministration
	* @apiDescription log user with client token
	* @apiVersion 0.2.0
	*
	*/
	public function clientLoginAction(Request $request, $token)
	{
		$em = $this->get('doctrine_mongodb')->getManager();
		$user = $em->getRepository('MongoBundle:User')->findOneBy(array('token' => $token));
		if (!$user || $user->getTokenValidity())
			return $this->setBadTokenError();

		$response = new JsonResponse();
		$response->setData(array('user' => $user->objectToArray()));
		return $response;
	}

 	/**
 	* @api {post} mongo/accountadministration/login Login
 	* @apiName login
 	* @apiGroup AccountAdministration
	* @apiDescription Log user from his login and password
 	* @apiVersion 0.2.0
	*
 	*/
	public function loginAction(Request $request)
	{
			$content = $request->getContent();
			$content = json_decode($content);
			$content = $content->data;

		  $em = $this->get('doctrine_mongodb')->getManager();
		  $user = $em->getRepository('MongoBundle:User')->findOneBy(array('email' => $content->login));
			if (!$user)
				return $this->setBadRequest("14.1.4", "AccountAdministration", "login", "Bad Parameter: login");

			if (!($this->container->get('security.password_encoder')->isPasswordValid($user, $content->password)))
				return $this->setBadRequest("14.1.4", "AccountAdministration", "login", "Bad Parameter: password");

			$now = new DateTime('now');
			if ($user->getToken() && $user->getTokenValidity() > $now)
			{
				$user->setTokenValidity($now->add(new DateInterval("P1D")));

				$em->persist($user);
	      $em->flush();

				return $this->setSuccess("1.14.1", "AccountAdministration", "login", "Complete Success", $user->objectToArray());
			}

			$secureUtils = $this->get('security.secure_random');
			$tmpToken = $secureUtils->nextBytes(25);
			$token = md5($tmpToken);
			$user->setToken($token);
			$user->setTokenValidity($now->add(new DateInterval("P1D")));

			$em->persist($user);
			$em->flush();

      $this->checkProjectsDeletedTime($user);

			return $this->setSuccess("1.14.1", "AccountAdministration", "login", "Complete Success", $user->objectToArray());
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
						$purs = $em->getRepository('MongoBundle:ProjectUserRole')->findByprojectId($project->getId());

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
								$purs = $em->getRepository('MongoBundle:projectUserRole')->findByprojectId($project->getId());

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
 * @api {get} mongo/accountadministration/logout/:token Logout
 * @apiName logout
 * @apiGroup AccountAdministration
 * @apiDescription unvalid user's token
 * @apiVersion 0.2.0
 *
 *
 */
 	public function logoutAction(Request $request, $token)
 	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("14.2.3", "AccountAdministration", "logout"));

		$user->setToken(null);

		$em = $this->get('doctrine_mongodb')->getManager();
		$em->persist($user);
		$em->flush();

		return $this->setSuccess("1.14.1", "AccountAdministration", "logout", "Complete Success", array("message" => "Successfully Logout"));
 	}

	/**
	* @api {post} mongo/accountadministration/register Register
	* @apiName register
	* @apiGroup AccountAdministration
	* @apiDescription Register a new user and log him
	* @apiVersion 0.2.0
	*
	*
	*/
	public function registerAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('firstname', $content) || !array_key_exists('lastname', $content) || !array_key_exists('password', $content) || !array_key_exists('email', $content))
			return $this->setBadRequest("14.3.6", "AccountAdministration", "register", "Missing Parameter");

		$em = $this->get('doctrine_mongodb')->getManager();
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

		$now = new DateTime('now');

		$secureUtils = $this->get('security.secure_random');
		$tmpToken = $secureUtils->nextBytes(25);
		$token = md5($tmpToken);
		$user->setToken($token);
		$user->setTokenValidity($now->add(new DateInterval("P1D")));

    $em->persist($user);
    $em->flush();

		return $this->setCreated("1.14.1", "AccountAdministration", "register", "Complete Success", $user->objectToArray());
	}
}
