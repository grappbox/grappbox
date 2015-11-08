<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class ProjectSettingsController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="add a member to the project",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to add a person"
     *      }
     *  }
	 * )
	 *
	 */
	public function addTeamMemberAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('add Team '.$id.' Member Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="generate a client access to the project",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to generate the access"
     *      }
     *  }
	 * )
	 *
	 */
	public function generateClientAccessAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('genreate Client Access '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a member to the project",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to delete a person"
     *      }
     *  }
	 * )
	 *
	 */
	public function delTeamMemberAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('del Team Member '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="assign a role to a member of the project",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person you want to assign a role"
     *      }
     *  }
	 * )
	 *
	 */
	public function assignRoleAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('assign Role '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="edit a role in the project",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the role you want to edit"
     *      }
     *  }
	 * )
	 *
	 */
	public function editRoleAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('edit Role '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get all the roles of the project",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to get the roles"
     *      }
     *  }
	 * )
	 *
	 */
	public function getRolesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('getRoles '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="check the permissions of the project",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to check the permisssions"
     *      }
     *  }
	 * )
	 *
	 */
	public function checkPermissionsAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('check Permissions '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="create a new project",
	 * views = { "projectsettings" },
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
	public function createProjectAction(Request $request)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('create Project Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="archive a project",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to archive"
     *      }
     *  }
	 * )
	 *
	 */
	public function archiveProjectAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('archive Project '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="set the project to finished",
	 * views = { "projectsettings" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the project you want to set as finished"
     *      }
     *  }
	 * )
	 *
	 */
	public function setProjectFinishedAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
			
		return new Response('set Project Finished '.$id.' Success');
	}
}
