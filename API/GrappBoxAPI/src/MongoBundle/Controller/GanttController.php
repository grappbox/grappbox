<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GanttController extends Controller
{

	public function addTaskAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!array_key_exists('projectId', $content))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $content->projectId, "gantt"))
			return ($this->setNoRightsError());

		return new Response('add Task '.$id.' Success');
	}


	public function assignTaskAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!array_key_exists('projectId', $content))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $content->projectId, "gantt"))
			return ($this->setNoRightsError());

		return new Response('assignTask '.$id.' Success');
	}


	public function editTaskAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!array_key_exists('projectId', $content))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $content->projectId, "gantt"))
			return ($this->setNoRightsError());

		return new Response('edit Task '.$id.' Success');
	}


	public function delTaskAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!array_key_exists('projectId', $content))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $content->projectId, "gantt"))
			return ($this->setNoRightsError());

		return new Response('del Task '.$id.' Success');
	}

	
	public function setTaskPropertiesAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!array_key_exists('projectId', $content))
			return $this->setBadRequest("Missing Parameter");
		if (!$this->checkRoles($user, $content->projectId, "gantt"))
			return ($this->setNoRightsError());

		return new Response('set Task Properties '.$id.' Success');
	}
}
