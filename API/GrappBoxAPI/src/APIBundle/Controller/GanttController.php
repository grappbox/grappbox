<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class GanttController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="add a task on an already existing gantt",
	 * views = { "gantt" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the gantt you want to add a task"
     *      }
     *  }
	 * )
	 *
	 */
	public function addTaskAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "gantt"))
			return ($this->setNoRightsError());

		return new Response('add Task '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="assign a task on a person",
	 * views = { "gantt" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the person you want to assign to a task"
     *      }
     *  }
	 * )
	 *
	 */
	public function assignTaskAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "gantt"))
			return ($this->setNoRightsError());

		return new Response('assignTask '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="edit a task on an already existing gantt",
	 * views = { "gantt" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the task you want to modify"
     *      }
     *  }
	 * )
	 *
	 */
	public function editTaskAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "gantt"))
			return ($this->setNoRightsError());

		return new Response('edit Task '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a task on an already existing gantt",
	 * views = { "gantt" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the task you want to delete"
     *      }
     *  }
	 * )
	 *
	 */
	public function delTaskAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "gantt"))
			return ($this->setNoRightsError());

		return new Response('del Task '.$id.' Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="set the properties of a task",
	 * views = { "gantt" },
  	 * requirements={
     *      {
     *          "name"="request",
     *          "dataType"="Request",
     *          "description"="The request object"
     *      },
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="The id corresponding to the task you want to set the properties"
     *      }
     *  }
	 * )
	 *
	 */
	public function setTaskPropertiesAction(Request $request, $id)
	{
		$user = $this->checkToken($request->request->get('_token'));
		if (!$user)
			return ($this->setBadTokenError());
		if (!$request->request->get('projectId'))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $request->request->get('projectId'), "gantt"))
			return ($this->setNoRightsError());

		return new Response('set Task Properties '.$id.' Success');
	}
}
