<?php

namespace APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Util\SecureRandom;

use APIBundle\Entity\User;
use DateTime;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class AccountAdministrationController extends Controller
{

	public function loginFormAction()
    {
        return $this->render('APIBundle:AccountAdministrationController:login.html.twig', array(
                //
            ));
    }
	public function signInFormAction()
    {
        return $this->render('APIBundle:AccountAdministrationController:createUser.html.twig', array(
                //
            ));
    }

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="login to an account",
	 * views= { "accountAdministration" },
	 * requirements={
   *      {
   *          "name"="request",
   *          "dataType"="Request",
   *          "description"="The request object"
   *      }
   * },
	 * parameters={
	 * 		{"name"="login", "dataType"="email", "required"=true, "description"="user login (email)"},
	 * 		{"name"="password", "dataType"="password", "required"=true, "description"="user login (email)"}
   * }
   * )
	 *
	 */
	 public function loginAction(Request $request)
	 {
		 	$response = new JsonResponse();
		  $em = $this->getDoctrine()->getManager();
		  $user = $em->getRepository('APIBundle:User')->findOneBy(array('email' => $request->request->get('login')));
			if (!$user)
			{
				$response->setData(array('status' => 'error', 'data' => 'bad user'));
				return $response;
			}

			if ($this->container->get('security.password_encoder')->isPasswordValid($user, $request->request->get('password')))
			{
					$secureUtils = $this->get('security.secure_random');
					$tmpToken = $secureUtils->nextBytes(25);
					$token = md5($tmpToken);
					$user->setToken($token);
					$em->persist($user);
		      $em->flush();
					$response->setData(array('status' => 'success', 'data' => array('user' => $user->serialize())));
					return $response;
			}
			else
			{
				$response->setData(array('status' => 'error', 'data' => 'bad password'));
				return $response;
			}
	 }

	 /**
 	 *
 	 * @ApiDoc(
 	 * resource=true,
 	 * description="disconnect from an account",
 	 * views= { "accountAdministration" },
 	 * requirements={
 	 *      {
 	 *          "name"="request",
 	 *          "dataType"="Request",
 	 *          "description"="The request object"
 	 *      }
 	 * },
	 * parameters={
	 * 		{"name"="_token", "dataType"="varchar(255)", "required"=true, "description"="user authentication token"}
   * }
 	 * )
 	 *
 	 */
 	public function logoutAction(Request $request)
 	{
		$response = new JsonResponse();
		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('APIBundle:User')->findOneBy(array('token' => $request->request->get('_token')));
		if (!$user)
		{
			$response->setData(array('status' => 'error', 'data' => 'bad token'));
			return $response;
		}
		$user->setToken(null);
		$em->persist($user);
		$em->flush();
		$response->setData(array('status' => 'success', 'data' => 'success'));
		return $response;
 	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="create a new account",
	 * views= { "accountAdministration" },
	 * methods= {"POST"},
	 * requirements={
   *      {
   *          "name"="request",
   *          "dataType"="Request",
   *          "description"="The request object"
   *      }
   * },
	 * parameters={
   *      {"name"="firstname", "dataType"="varchar(255)", "required"=true, "description"="user firstname"},
	 *      {"name"="lastname", "dataType"="varchar(255)", "required"=true, "description"="user lastname"},
	 *      {"name"="birthday", "dataType"="dateTime", "required"=false, "description"="user birthday"},
	 *      {"name"="avatar", "dataType"="file", "required"=false, "description"="user avatar"},
	 *      {"name"="password", "dataType"="varchar(255)", "required"=true, "description"="user password"},
	 *      {"name"="email", "dataType"="varchar(255)", "required"=true, "description"="user email"},
	 *      {"name"="phone", "dataType"="varchar(255)", "required"=false, "description"="user phone number"},
	 *      {"name"="country", "dataType"="varchar(255)", "required"=false, "description"="user country"},
	 *      {"name"="linkedin", "dataType"="varchar(255)", "required"=false, "description"="user linkedin url"},
   *      {"name"="viadeo", "dataType"="varchar(255)", "required"=false, "description"="user viadeo url"},
	 *      {"name"="twitter", "dataType"="varchar(255)", "required"=false, "description"="user twitter url"}
	 *  }
   * )
	 *
	 */
	public function signInAction(Request $request)
	{
			$user = new User();
      $user->setFirstname($request->request->get('firstname'));
      $user->setLastname($request->request->get('lastname'));
			if ($request->request->get('birthday'))
				$user->setBirthday(new Datetime($request->request->get('birthday')));

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
      $encoded = $encoder->encodePassword($user, $request->request->get('password'));
      $user->setPassword($encoded);

			$user->setEmail($request->request->get('email'));
			if ($request->request->get('phone'))
      	$user->setPhone($request->request->get('phone'));
			if ($request->request->get('country'))
      	$user->setCountry($request->request->get('country'));
			if ($request->request->get('linkedin'))
      	$user->setLinkedin($request->request->get('linkedin'));
			if ($request->request->get('viadeo'))
      	$user->setViadeo($request->request->get('viadeo'));
			if ($request->request->get('twitter'))
      	$user->setTwitter($request->request->get('twitter'));

			$secureUtils = $this->get('security.secure_random');
			$tmpToken = $secureUtils->nextBytes(25);
			$token = md5($tmpToken);
			$user->setToken($token);

      $em = $this->getDoctrine()->getManager();
      $em->persist($user);
      $em->flush();


			$response = new JsonResponse();
			$response->setData(array('status' => 'success', 'data' => array('user' => $user->serialize())));
			return $response;
      // $providerKey = 'default'; // your firewall name
      // $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
      // $this->container->get('security.context')->setToken($token);

      // return $this->render('AppBundle:UserController:homeUser.html.twig',
      //     array(
      //         'avatar' => $user->getAvatar()
      //     ));
	}

}
