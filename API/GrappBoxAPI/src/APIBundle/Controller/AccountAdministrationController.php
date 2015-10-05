<?php

namespace APIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function loginAction(Request $request)
	{
		$authenticationUtils = $this->get('security.authentication_utils');

    // get the login error if there is one
    $error = $authenticationUtils->getLastAuthenticationError();
    // last username entered by the user
   	// $lastUsername = $authenticationUtils->getLastUsername();

		if ($error)
			{
			 $message = "Error: ".$error->getMessage();
			}
		else
		{
		 $token = $this->container->get('security.context')->getToken();
		 $message = "Success: you are now login, token: ".$token;
		}

		return new Response($message);
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="create a new account",
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      }
     * }
     * )
	 *
	 */
	public function signInAction(Request $request)
	{
			$user = new User();
      $user->setFirstname($request->request->get('firstname'));
      $user->setLastname($request->request->get('lastname'));
      $user->setBirthday(new Datetime($request->request->get('birthday')));

			$generator = $this->get('security.secure_random');
      $random = $generator->nextBytes(10);
      $fileDir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/avatars';
      $fileName= md5($random).'.'.$request->files->get('avatar')->guessExtension();
      $avatar = $request->files->get('avatar')->move($fileDir, $fileName);

      $user->setAvatar($fileDir.'/'.$fileName);

      $encoder = $this->container->get('security.password_encoder');
      $encoded = $encoder->encodePassword($user, $request->request->get('password'));
      $user->setPassword($encoded);

      $user->setEmail($request->request->get('email'));
      $user->setPhone($request->request->get('phone'));
      $user->setCountry($request->request->get('country'));
      $user->setLinkedin($request->request->get('linkedin'));
      $user->setViadeo($request->request->get('viadeo'));
      $user->setTwitter($request->request->get('twitter'));

      $em = $this->getDoctrine()->getManager();
      $em->persist($user);
      $em->flush();

      $providerKey = 'main';
      $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
      $this->container->get('security.context')->setToken($token);

			return new Response('Success: token: '.$token);
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="disconnect from an account",
		 * requirements={
		 *      {
		 *          "name"="request",
		 *          "dataType"="Request",
		 *          "description"="The request object"
		 *      }
		 * }
		 * )
	 *
	 */
	public function disconnectAction(Request $request)
	{
		return new Response('disconnect Success');
	}

}
