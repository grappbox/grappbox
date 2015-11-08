<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class PersonnalSettingsController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="edit a profile photo",
	 * views = { "personnalsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the photo you want to edit"
     *      }
     *  }
	 * )
	 *
	 */
	public function editPhotoAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('edit Photo '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="edit personal informations",
	 * views = { "personnalsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person you want to edit the infos"
     *      }
     *  }
	 * )
	 *
	 */
	public function editPersonalInfosAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('edit Personal Infos '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="change the password",
	 * views = { "personnalsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person you want to change the password"
     *      }
     *  }
	 * )
	 *
	 */
	public function changePasswordAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('change Password '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="edit the preferences",
	 * views = { "personnalsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the profile you want to edit the preferencies"
     *      }
     *  }
	 * )
	 *
	 */
	public function editPreferencesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('edit Preferences '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the role of the person",
	 * views = { "personnalsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person you want to get the role"
     *      }
     *  }
	 * )
	 *
	 */
	public function getRoleAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
			
		return new Response('get Role '.$id.' Success');
	}
}
