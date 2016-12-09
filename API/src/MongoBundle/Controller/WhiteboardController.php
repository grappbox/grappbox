<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MongoBundle\Controller\RolesAndTokenVerificationController;
use MongoBundle\Document\Whiteboard;
use MongoBundle\Document\WhiteboardObject;
use SQLBundle\Entity\WhiteboardPerson;
use DateTime;

/**
*  @IgnoreAnnotation("apiName")
*  @IgnoreAnnotation("apiGroup")
*  @IgnoreAnnotation("apiVersion")
*  @IgnoreAnnotation("apiDescription")
*  @IgnoreAnnotation("apiSuccess")
*  @IgnoreAnnotation("apiSuccessExample")
*  @IgnoreAnnotation("apiError")
*  @IgnoreAnnotation("apiErrorExample")
*  @IgnoreAnnotation("apiParam")
*  @IgnoreAnnotation("apiParamExample")
*/
class WhiteboardController extends RolesAndTokenVerificationController
{
	private function serializeInArray($objects)
	{
		$content = array();
		foreach ($objects as $key => $value) {
			$content[] = $value->objectToArray();
		}
		return $content;
	}

	/**
	* @-api {get} /0.3/whiteboards/:projectId List whiteboards
	* @apiName listWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Get the list of whiteboards for the given project
	* @apiVersion 0.3.0
	*
	*/
	public function listWhiteboardAction(Request $request, $projectId)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("10.1.3", "Whiteboard", "list"));
		if (!$this->checkRoles($user, $projectId, "whiteboard"))
			return ($this->setNoRightsError("10.1.9", "Whiteboard", "list"));
		$em = $this->getDoctrine()->getManager();
		$project = $em->getRepository('MongoBundle:Project')->find($projectId);
		$whiteboards = $project->getWhiteboards();
		$whiteboardsList = array();
		foreach ($whiteboards as $key => $whiteboard) {
			if(!$whiteboard->getDeletedAt())
				$whiteboardsList[] = $whiteboard;
		}
		if (count($whiteboardsList) <= 0)
			return $this->setNoDataSuccess("1.10.3", "Whiteboard", "list");
		return $this->setSuccess("1.10.1", "Whiteboard", "list", "Complete Success", array("array" => $this->serializeInArray($whiteboardsList)));
	}

	/**
	* @-api {post} /0.3/whiteboard Create a new Whiteboard
	* @apiName createWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Create a new whiteboard
	* @apiVersion 0.3.0
	*
	*/
	public function newWhiteboardAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('whiteboardName', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("10.2.6", "Whiteboard", "new", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("10.2.3", "Whiteboard", "new"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
		if ($project instanceof Project)
			$this->setBadRequest("10.2.4", "Whiteboard", "new", "Bad Parameter: projectId");

		if ($this->checkRoles($user, $content->projectId, "whiteboard") < 2)
			return ($this->setNoRightsError("10.2.9", "Whiteboard", "new"));

		$whiteboard = new Whiteboard();
		$whiteboard->setProjects($project);
		$whiteboard->setCreatorUser($user);
		$whiteboard->setUpdatorUser($user);
		$whiteboard->setName($content->whiteboardName);
		$whiteboard->setCreatedAt(new DateTime('now'));
		$whiteboard->setUpdatedAt(new DateTime('now'));

		$em->persist($whiteboard);
		$em->flush();

		//notifs
		$mdata['mtitle'] = "new whiteboard";
		$mdata['mdesc'] = json_encode($whiteboard->objectToArray());
		$wdata['type'] = "new whiteboard";
		$wdata['targetId'] = $whiteboard->getId();
		$wdata['message'] = json_encode($whiteboard->objectToArray());
		$userNotif = array();
		foreach ($project->getUsers() as $key => $value) {
			$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setCreated("1.10.1", "Whiteboard", "new", "Complete Success", $whiteboard->objectToArray());
	}

	/**
	* @-api {get} /0.3/whiteboard/:id Open a whiteboard
	* @apiName openWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Open the given whiteboard
	* @apiVersion 0.3.0
	*
	*/
	public function openWhiteboardAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("10.3.3", "Whiteboard", "open"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
	 	if (!$whiteboard)
			return $this->setBadRequest("10.3.4", "Whiteboard", "open", "Bad Parameter: id");

 		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 1)
	 			return ($this->setNoRightsError("10.3.9", "Whiteboard", "open"));

		if ($whiteboard->getDeletedAt())
			return $this->setBadRequest("10.3.4", "Whiteboard", "open", "Bad Parameter: Whiteboard Deleted");

		$arr = $whiteboard->objectToArray();
		$arr["content"] = array();
		foreach ($whiteboard->getObjects() as $key => $obj) {
			if ($obj->getDeletedAt() == null)
			{
				$object = $obj->objectToArray();
				$arr["content"][] = $object;
			}
		}
		$arr["users"] = array();
		$userNotif = array();
		foreach ($whiteboard->getPersons() as $key => $value) {
			$arr["users"][] = $value->getUser()->objectToArray();
			if ($value->getUser()->getId() != $user->getId())
				$userNotif[] = $value->getUser()->getId();
		}

		//notifs
		$mdata['mtitle'] = "login whiteboard";
		$mdata['mdesc'] = json_encode(array("id" => $whiteboard->getId(), "projectId" => $whiteboard->getProjects()->getId(), "user" => array("id" => $user->getId(), "firstname" => $user->getFirstname(), "lastname" => $user->getLastname())));
		$wdata['type'] = "login whiteboard";
		$wdata['targetId'] = $whiteboard->getId();
		$wdata['message'] = json_encode(array("id" => $whiteboard->getId(), "projectId" => $whiteboard->getProjects()->getId(), "user" => array("id" => $user->getId(), "firstname" => $user->getFirstname(), "lastname" => $user->getLastname())));
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		$exist = $em->getRepository('MongoBundle:WhiteboardPerson')->findBy(array("user" => $user->getId(), "whiteboard" => $whiteboard->getId()));
		if (count($exist) == 0) {
			$newPerson = new WhiteboardPerson();
			$newPerson->setWhiteboard($whiteboard);
			$newPerson->setUser($user);
			$em->persist($newPerson);
			$em->flush();
		}

		return $this->setSuccess("1.10.1", "Whiteboard", "open", "Complete Success", $arr);
	}

	/**
	* @-api {put} /0.3/whiteboard/:id Close a whiteboard
	* @apiName closeWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Close the given whiteboard
	* @apiVersion 0.3.0
	*/
	public function closeWhiteboardAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("10.3.3", "Whiteboard", "close"));
		$em = $this->getDoctrine()->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
			return $this->setBadRequest("10.3.4", "Whiteboard", "close", "Bad Parameter: id");
		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 1)
			return ($this->setNoRightsError("10.3.9", "Whiteboard", "close"));
		if ($whiteboard->getDeletedAt())
			return $this->setBadRequest("10.3.4", "Whiteboard", "close", "Bad Parameter: Whiteboard Deleted");
		$users = array();
		$userNotif = array();
		foreach ($whiteboard->getPersons() as $key => $value) {
			$users[] = $value->getUser()->objectToArray();
			$userNotif[] = $value->getUser()->getId();
		}
		$userConnect = $em->getRepository('MongoBundle:WhiteboardPerson')->findBy(array("user" => $user->getId(), "whiteboard" => $whiteboard->getId()));
		if (count($userConnect) < 1)
			return $this->setBadRequest("10.3.4", "Whiteboard", "close", "Bad Parameter: Not connected on the whiteboard");

		//notifs
		$mdata['mtitle'] = "logout whiteboard";
		$mdata['mdesc'] = json_encode(array("id" => $whiteboard->getId(), "projectId" => $whiteboard->getProjects()->getId(), "user" => array("id" => $user->getId(), "firstname" => $user->getFirstname(), "lastname" => $user->getLastname())));
		$wdata['type'] = "logout whiteboard";
		$wdata['targetId'] = $whiteboard->getId();
		$wdata['message'] = json_encode(array("id" => $whiteboard->getId(), "projectId" => $whiteboard->getProjects()->getId(), "user" => array("id" => $user->getId(), "firstname" => $user->getFirstname(), "lastname" => $user->getLastname())));
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		foreach ($userConnect as $key => $value) {
			$em->remove($value);
		}
		$em->flush();

		return $this->setSuccess("1.10.1", "Whiteboard", "close", "Complete Success", array());
	}

	/**
	* @-api {put} /0.3/whiteboard/draw/:id Push a whiteboard modification
	* @apiName pushDrawOnWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Push a whiteboard modification
	* @apiVersion 0.3.0
	*
	*/
	public function pushDrawAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('object', $content))
		 	return $this->setBadRequest("10.4.6", "Whiteboard", "push", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("10.4.3", "Whiteboard", "push"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.4.4", "Whiteboard", "push", "Bad Parameter: id");

		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 2)
			return ($this->setNoRightsError("10.4.9", "Whiteboard", "push"));

		$object = new WhiteboardObject();
		$object->setWhiteboardId($id);
		$object->setWhiteboard($whiteboard);
		$object->setObject(json_encode($content->object));
		$object->setCreatedAt(new DateTime('now'));
		$em->persist($object);
		$em->flush();

		$objectArray = $object->objectToArray();
		$objectArray['projectId'] = $object->getWhiteboard()->getProjects()->getId();

		//notifs
		$mdata['mtitle'] = "new object";
		$mdata['mdesc'] = json_encode($objectArray);
		$wdata['type'] = "new object";
		$wdata['targetId'] = $object->getId();
		$wdata['message'] = json_encode($objectArray);
		$userNotif = array();
		foreach ($whiteboard->getProjects()->getUsers() as $key => $value) {
			if ($this->checkRoles($value, $whiteboard->getProjects()->getId(), "whiteboard") > 0)
				$userNotif[] = $value->getId();
		}
		if (count($userNotif) > 0)
			$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);

		return $this->setSuccess("1.10.1", "Whiteboard", "push", "Complete Success", $object->objectToArray());
	}

	/**
	* @-api {post} /0.3/whiteboard/draw/:id Pull whiteboard modifications
	* @apiName pullDrawOnWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Pull whiteboard modifications
	* @apiVersion 0.3.0
	*
	*/
	public function pullDrawAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('lastUpdate', $content))
 			return $this->setBadRequest("10.5.6", "Whiteboard", "pull", "Missing Parameter");

		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("10.5.3", "Whiteboard", "pull"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.5.4", "Whiteboard", "pull", "Bad Parameter: id");

		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 1)
			 return ($this->setNoRightsError("10.5.9", "Whiteboard", "pull"));

		$date = new \DateTime($content->lastUpdate);

		// $toAddQuery = $em->createQuery('SELECT objects FROM SQLBundle\Entity\WhiteboardObject objects
		// 								WHERE objects.whiteboardId = :id AND objects.createdAt > :date AND objects.deletedAt IS NULL')
		// 								->setParameters(array('date' => $date, 'id' => $id));
		$qb = $em->getRepository('MongoBundle:WhiteboardObject')->createQueryBuilder('w')
						->field('whiteboardId')->equals($id)
						->field('createdAt')->gt($date);
		$to_add = $qb->getQuery()->execute();

		$toAdd = array();
		foreach ($to_add as $key => $value) {
			$toAdd[] = $value->objectToArray();
		}
		// $toDelQuery = $em->createQuery('SELECT objects FROM SQLBundle\Entity\WhiteboardObject objects
		// 							    WHERE objects.whiteboardId = :id AND objects.deletedAt > :date AND objects.deletedAt IS NOT NULL')
		// 								->setParameters(array('date' => $date, 'id' => $id));
		// $to_del = $toDelQuery->getResult();

		$qb = $em->getRepository('MongoBundle:WhiteboardObject')->createQueryBuilder('w')
						->field('whiteboardId')->equals($id)
						->field('deletedAt')-gt($date);
		$to_del = $qb->getQuery()->execute();

		$toDel = array();
		foreach ($to_del as $key => $value) {
			$toDel[] = $value->objectToArray();
		}

		$users = array();
		foreach ($whiteboard->getPersons() as $key => $value) {
			$users[] = $value->getUser()->objectToArray();
		}

		return $this->setSuccess("1.10.1", "Whiteboard", "pull", "Complete Success", array('add' => $toAdd, 'delete' => $toDel, 'users' => $users));
	}

	/**
	* @-api {delete} /0.3/whiteboard/:id Delete a Whiteboard
	* @apiName deleteWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Delete a whiteboard
	* @apiVersion 0.3.0
	*
	*/
	public function delWhiteboardAction(Request $request, $id)
	{
		$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("10.6.3", "Whiteboard", "delete"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.6.4", "Whiteboard", "delete", "Bad Parameter: id");

		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 2)
			 return ($this->setNoRightsError("10.6.9", "Whiteboard", "delete"));

		if ($whiteboard)
		{
			$whiteboard->setDeletedAt(new DateTime('now'));
			$em->persist($whiteboard);
			$em->flush();

			//notifs
			$mdata['mtitle'] = "delete whiteboard";
			$mdata['mdesc'] = json_encode($whiteboard->objectToArray());
			$wdata['type'] = "delete whiteboard";
			$wdata['targetId'] = $whiteboard->getId();
			$wdata['message'] = json_encode($whiteboard->objectToArray());
			$userNotif = array();
			foreach ($whiteboard->getProjects()->getUsers() as $key => $value) {
				$userNotif[] = $value->getId();
			}
			if (count($userNotif) > 0)
				$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);
		}

		$response["info"]["return_code"] = "1.10.1";
		$response["info"]["return_message"] = "Whiteboard - delete - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @-api {delete} /0.3/whiteboard/object/:id Delete object
	* @apiName deleteObject
	* @apiGroup Whiteboard
	* @apiDescription Get the last object created to delete from rubber position and radius
	* @apiVersion 0.3.00
	*
	*/
	public function deleteObjectAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('center', $content) || !array_key_exists('radius', $content))
			return $this->setBadRequest("10.7.6", "Whiteboard", "deleteObject", "Missing Parameter");

			$user = $this->checkToken($request->headers->get('Authorization'));
		if (!$user)
			return ($this->setBadTokenError("10.7.3", "Whiteboard", "deleteObject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
			return $this->setBadRequest("10.7.4", "Whiteboard", "deleteObject", "Bad Parameter: whiteboardId");

		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 2)
			 return ($this->setNoRightsError("10.7.9", "Whiteboard", "deleteObject"));

		$objects =  $em->getRepository('MongoBundle:WhiteboardObject')->findBy(array("whiteboardId" => $whiteboard->getId(), "deletedAt" => NULL), array("createdAt" => 'DESC'));

		$toDel = $this->checkDeletion($objects, $content->center, $content->radius);

		$data = array();
		if ($toDel != null)
		{
			$value = $toDel;
			$value->setDeletedAt(new DateTime("now"));
			$em->persist($value);
			$em->flush();
			$data = $value->objectToArray();

			$objectArray = $data;
			$objectArray['projectId'] = $object->getWhiteboard()->getProjects()->getId();

			//notifs
			$mdata['mtitle'] = "delete object";
			$mdata['mdesc'] = json_encode($objectArray);
			$wdata['type'] = "delete object";
			$wdata['targetId'] = $value->getId();
			$wdata['message'] = json_encode($objectArray);
			$userNotif = array();
			foreach ($whiteboard->getProjects()->getUsers() as $key => $value) {
				if ($this->checkRoles($value, $whiteboard->getProjects()->getId(), "whiteboard") > 0)
					$userNotif[] = $value->getId();
			}
			if (count($userNotif) > 0)
				$this->get('service_notifs')->notifs($userNotif, $mdata, $wdata, $em);
		}

		if (count($data) <= 0)
			return $this->setNoDataSuccess("1.10.3", "Whiteboard", "deleteObject");

		return $this->setSuccess("1.10.1", "Whiteboard", "deleteObject", "Complete Success", $data);
	}

	private function checkDeletion($objects, $center, $radius)
	{
		foreach ($objects as $key => $object) {
			$obj = json_decode($object->getObject());
			switch ($obj->type) {
				case 'LINE':
					if ($this->intersectionWithLine($center, $radius, array("x" => $obj->positionStart->x, "y" => $obj->positionStart->y), array("x" => $obj->positionEnd->x, "y" => $obj->positionEnd->y)))
						return $object;
					break;
				case 'HANDWRITE':
					if ($this->intersectionWithHandwrite($center, $radius, $obj))
						return $object;
					break;
				case 'RECTANGLE':
					$square = $this->determineMinimalSquare($obj);
					if ($this->intersectionWithSquare($center, $radius, $obj, $square))
						return $object;
					break;
				case 'TEXT':
					$square = $this->determineMinimalSquare($obj);
					if ($this->intersectionWithText($center, $radius, $obj, $square))
						return $object;
					break;
				case 'DIAMOND':
					$square = $this->determineMinimalSquare($obj);
					$diamond = $this->determineDiamond($square);
					if ($this->intersectionWithDiamond($center, $radius, $obj, $diamond))
						return $object;
					break;
				case 'ELLIPSE':
					if ($this->intersectionWithEllipse($center, $radius, $obj))
						return $object;
					break;
				default:
					$square = $this->determineMinimalSquare($obj);
					if ($this->intersectionWithSquare($center, $radius, $obj, $square))
						return $object;
					break;
			}
		}
	}

	private function determineMinimalSquare($object)
	{
		$pointA = array("x" => $object->positionStart->x, "y" => $object->positionStart->y);
		$pointB = array("x" => $object->positionStart->x, "y" => $object->positionEnd->y);
		$pointC = array("x" => $object->positionEnd->x, "y" => $object->positionEnd->y);
		$pointD = array("x" => $object->positionEnd->x, "y" => $object->positionStart->y);
		return array("A" => $pointA, "B" => $pointB, "C" => $pointC, "D" => $pointD);
	}

	private function determineDiamond($square)
	{
		$pointA = array("x" => (($square["A"]["x"] + $square["B"]["x"]) / 2), "y" => (($square["A"]["y"] + $square["B"]["y"]) / 2));
		$pointB = array("x" => (($square["B"]["x"] + $square["C"]["x"]) / 2), "y" => (($square["B"]["y"] + $square["C"]["y"]) / 2));
		$pointC = array("x" => (($square["C"]["x"] + $square["D"]["x"]) / 2), "y" => (($square["C"]["y"] + $square["D"]["y"]) / 2));
		$pointD = array("x" => (($square["D"]["x"] + $square["A"]["x"]) / 2), "y" => (($square["D"]["y"] + $square["A"]["y"]) / 2));
		return array("A" => $pointA, "B" => $pointB, "C" => $pointC, "D" => $pointD);
	}

	private function intersectionWithLine($center, $radius, $pointA, $pointB)
	{
		if ($pointA["x"] == $pointB["x"])
		{
			$x = $pointA["x"];
			if ($pointA["y"] > $pointB["y"])
				$dif = -0.1;
			else
				$dif = 0.1;
			for ($y = $pointA["y"]; ($y >= $pointA["y"] && $y <= $pointB["y"]) || ($y <= $pointA["y"] && $y >= $pointB["y"]); $y += $dif)
			{
				if ((pow(($x-$center->x), 2) + pow(($y-$center->y), 2)) <= pow($radius, 2))
					return true;
			}
			return false;
		}
		// determine m and p
		$m = ($pointB["y"] - $pointA["y"]) / ($pointB["x"] - $pointA["x"]);
		$p = $pointA["y"] - ($m * $pointA["x"]);
		// determine line direction
		if ($pointA["x"] > $pointB["x"])
			$dif = -0.1;
		else
			$dif = 0.1;
		//determine if has intersection
		for ($x = $pointA["x"]; ($x >= $pointA["x"] && $x <= $pointB["x"]) || ($x <= $pointA["x"] && $x >= $pointB["x"]); $x += $dif)
		{
			$y = ($m * $x) + $p;
			if ((pow(($x-$center->x), 2) + pow(($y-$center->y), 2)) <= pow($radius, 2))
				return true;
		}
		return false;
	}

	private function intersectionWithHandwrite($center, $radius, $obj)
	{
		$prev = null;
		foreach ($obj->points as $point) {
			if (!$prev)
				$prev = $point;
			else {
				if ($this->intersectionWithLine($center, $radius, array("x" => $prev->x, "y" => $prev->y), array("x" => $point->x, "y" => $point->y)))
					return true;
				$prev = $point;
			}
		}
		return false;
	}

	private function intersectionWithText($center, $radius, $obj, $square)
	{
		if ($this->intersectionWithLine($center, $radius, $square["A"], $square["B"]) || $this->intersectionWithLine($center, $radius, $square["B"], $square["C"])
			|| $this->intersectionWithLine($center, $radius, $square["C"], $square["D"]) || $this->intersectionWithLine($center, $radius, $square["D"], $square["A"]))
			return true;
		$xStart = $obj->positionStart->x;
		$yStart = $obj->positionStart->y;

		$xEnd = $obj->positionEnd->x;
		$yEnd = $obj->positionEnd->y;
		if ($xStart > $xEnd)
		{
			$x = $xStart;
			$xStart = $xEnd;
			$xEnd = $x;
		}
		if ($yStart > $yEnd)
		{
			$y = $yStart;
			$yStart = $yEnd;
			$yEnd = $y;
		}
		if ($center->x >= $xStart && $center->x <= $xEnd)
		{
			if ($center->y >= $yStart && $center->y <= $yEnd)
				return true;
		}
		return false;
	}

	private function intersectionWithSquare($center, $radius, $obj, $square)
	{
		if (($obj->background == "" || $obj->background == null) && $obj->type != "TEXT")
		{
			if ($this->intersectionWithLine($center, $radius, $square["A"], $square["B"]) || $this->intersectionWithLine($center, $radius, $square["B"], $square["C"])
				|| $this->intersectionWithLine($center, $radius, $square["C"], $square["D"]) || $this->intersectionWithLine($center, $radius, $square["D"], $square["A"]))
				return true;
		}
		else
		{
			if ($this->intersectionWithLine($center, $radius, $square["A"], $square["B"]) || $this->intersectionWithLine($center, $radius, $square["B"], $square["C"])
				|| $this->intersectionWithLine($center, $radius, $square["C"], $square["D"]) || $this->intersectionWithLine($center, $radius, $square["D"], $square["A"]))
				return true;
			$xStart = $obj->positionStart->x;
			$yStart = $obj->positionStart->y;

			$xEnd = $obj->positionEnd->x;
			$yEnd = $obj->positionEnd->y;
			if ($xStart > $xEnd)
			{
				$x = $xStart;
				$xStart = $xEnd;
				$xEnd = $x;
			}
			if ($yStart > $yEnd)
			{
				$y = $yStart;
				$yStart = $yEnd;
				$yEnd = $y;
			}
			if ($center->x >= $xStart && $center->x <= $xEnd)
			{
				if ($center->y >= $yStart && $center->y <= $yEnd)
					return true;
			}
		}
		return false;
	}

	private function checkRight($center, $pointA, $pointB)
	{
		$Dx = $pointB["x"] - $pointA["x"];
			$Dy = $pointB["y"] - $pointA["y"];
			$Tx = $center->x - $pointA["x"];
			$Ty = $center->y - $pointA["y"];
			$d = $Dx*$Ty - $Dy*$Tx;
			if ($d<0)
					return true;
				return false;
	}

	private function intersectionWithDiamond($center, $radius, $obj, $square)
	{
		if ($obj->background == "" || $obj->background == null)
		{
			if ($this->intersectionWithLine($center, $radius, $square["A"], $square["B"]) || $this->intersectionWithLine($center, $radius, $square["B"], $square["C"])
				|| $this->intersectionWithLine($center, $radius, $square["C"], $square["D"]) || $this->intersectionWithLine($center, $radius, $square["D"], $square["A"]))
				return true;
		}
		else
		{
			if ($this->intersectionWithLine($center, $radius, $square["A"], $square["B"]) || $this->intersectionWithLine($center, $radius, $square["B"], $square["C"])
				|| $this->intersectionWithLine($center, $radius, $square["C"], $square["D"]) || $this->intersectionWithLine($center, $radius, $square["D"], $square["A"]))
				return true;
			$a = $this->checkRight($center, $square["A"], $square["B"]);
			$b = $this->checkRight($center, $square["B"], $square["C"]);
			$c = $this->checkRight($center, $square["C"], $square["D"]);
			$d = $this->checkRight($center, $square["D"], $square["A"]);
			if ($a == true && $b == true && $c == true && $d == true)
				return true;
			if ($a == false && $b == false && $c == false && $d == false)
			{
				$a = $this->checkRight($center, $square["A"], $square["D"]);
				$b = $this->checkRight($center, $square["D"], $square["C"]);
				$c = $this->checkRight($center, $square["C"], $square["B"]);
				$d = $this->checkRight($center, $square["B"], $square["A"]);
				if ($a == true && $b == true && $c == true && $d == true)
					return true;
			}
		}
		return false;
	}

	private function intersectionWithEllipse($center, $radius, $obj)
	{
		$objCenter = array("x" => (($obj->positionStart->x + $obj->positionEnd->x) / 2), "y" => (($obj->positionStart->y + $obj->positionEnd->y) / 2));
		if ($center->x == $objCenter["x"])
		{
			$x = $center->x;
			if ($center->y > $objCenter["y"])
				$dif = -0.1;
			else
				$dif = 0.1;
			for ($y = $center->y; ($y >= $center->y && $y <= $objCenter["y"]) || ($y <= $center->y && $y >= $objCenter["y"]); $y += $dif)
			{
				if (((pow(($x-$center->x), 2) + pow(($y-$center->y), 2)) <= pow($radius, 2))
					&& ((pow(($x - $objCenter["x"]), 2) / pow($obj->radius->x, 2)) + (pow(($y - $objCenter["y"]), 2) / pow($obj->radius->y, 2)) <= 1))
					return true;
			}
			return false;
		}
		// determine m and p
		$m = ($objCenter["y"] - $center->y) / ($objCenter["x"] - $center->x);
		$p = $center->y - ($m * $center->x);
		// determine line direction
		if ($center->x > $objCenter["x"])
			$dif = -0.1;
		else
			$dif = 0.1;
		//determine if has intersection
		for ($x = $center->x; ($x >= $center->x && $x <= $objCenter["x"]) || ($x <= $center->x && $x >= $objCenter["x"]); $x += $dif)
		{
			$y = ($m * $x) + $p;
			if (((pow(($x-$center->x), 2) + pow(($y-$center->y), 2)) <= pow($radius, 2))
				&& ((pow(($x - $objCenter["x"]), 2) / pow($obj->radius->x, 2)) + (pow(($y - $objCenter["y"]), 2) / pow($obj->radius->y, 2)) <= 1))
				return true;
		}
		return false;
	}

}
