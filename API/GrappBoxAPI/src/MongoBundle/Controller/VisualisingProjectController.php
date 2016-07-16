<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class VisualisingProjectController extends Controller
{

	public function addActionAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());

		return new Response('add Action '.$id.' Success');
	}


	public function addActorAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "projectSettings"))
			return ($this->setNoRightsError());

		return new Response('add actor '.$id.' Success');
	}


	public function addLinkAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		if (!$this->checkRoles($user, $id, "projectSettings"))
			return ($this->setNoRightsError());

		return new Response('add Link '.$id.' Success');
	}


	public function newDiagramAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		// if (!$this->checkRoles($user, $id, "projectSettings"))
		// 	return ($this->setNoRightsError());

		return new Response('new Diagram '.$id.' Success');
	}


	public function openDiagramAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		// if (!$this->checkRoles($user, $id, "projectSettings"))
		// 	return ($this->setNoRightsError());

		return new Response('open Diagram '.$id.' Success');
	}

	
	public function invitePersonAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError());
		// if (!$this->checkRoles($user, $id, "projectSettings"))
		// 	return ($this->setNoRightsError());

		return new Response('invite Person '.$id.' Success');
	}
}
