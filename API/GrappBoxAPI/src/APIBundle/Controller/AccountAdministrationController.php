<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class AccountAdministrationController extends Controller
{
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
		return new Response('login Success');
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
		return new Response('sign In Success');
	}
}