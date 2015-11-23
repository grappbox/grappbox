<?php

namespace APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use APIBundle\Controller\RolesAndTokenVerificationController;
use APIBundle\Entity\User;
use DateTime;

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
	public function loginAction(Request $request)
	{
			$content = $request->getContent();
			$content = json_decode($content);

		  $em = $this->getDoctrine()->getManager();
		  $user = $em->getRepository('APIBundle:User')->findOneBy(array('email' => $content->login));
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
					$em->persist($user);
		      $em->flush();

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
	public function registerAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		if (!array_key_exists('firstname', $content) || !array_key_exists('lastname', $content) || !array_key_exists('password', $content) || !array_key_exists('email', $content))
			return $this->setBadRequest("Missing Parameter");
		$em = $this->getDoctrine()->getManager();
		if ($em->getRepository('APIBundle:User')->findOneBy(array('email' => $content->email)))
			return $this->setBadRequest("Email already in DB");
		$user = new User();
    $user->setFirstname($content->firstname);
    $user->setLastname($content->lastname);

		if (array_key_exists('birthday', $content))
			$user->setBirthday(new Datetime($content->birthday));

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
