<?php

namespace MongoBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use MongoBundle\Controller\RolesAndTokenVerificationController;
use MongoBundle\Document\Whiteboard;
use MongoBundle\Document\WhiteboardObject;
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
	* @api {get} /mongo/whiteboard/list/:token/:projectId List whiteboards
	* @apiName listWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Get the list of whiteboards for the given project
	* @apiVersion 0.2.0
	*
	*/
	public function listWhiteboardAction(Request $request, $token, $projectId)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("10.1.3", "Whiteboard", "list"));

		if (!$this->checkRoles($user, $projectId, "whiteboard"))
			return ($this->setNoRightsError("10.1.9", "Whiteboard", "list"));

		$em = $this->get('doctrine_mongodb')->getManager();
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
	* @api {post} /mongo/whiteboard/new Create a new Whiteboard
	* @apiName createWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Create a new whiteboard
	* @apiVersion 0.2.0
	*
	*/
	public function newWhiteboardAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('projectId', $content) || !array_key_exists('whiteboardName', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("10.2.6", "Whiteboard", "new", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("10.2.3", "Whiteboard", "new"));

		if ($this->checkRoles($user, $content->projectId, "whiteboard") < 2)
			return ($this->setNoRightsError("10.2.9", "Whiteboard", "new"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$project = $em->getRepository("MongoBundle:Project")->find($content->projectId);
		if ($project instanceof Project)
			$this->setBadRequest("10.2.4", "Whiteboard", "new", "Bad Parameter: projectId");

		$whiteboard = new Whiteboard();
		$whiteboard->setProjects($project);
		$whiteboard->setUserId($user->getId());
		$whiteboard->setUpdatorId($user->getId());
		$whiteboard->setName($content->whiteboardName);
		$whiteboard->setCreatedAt(new DateTime('now'));
		$whiteboard->setUpdatedAt(new DateTime('now'));

		$em->persist($whiteboard);
		$em->flush();

		return $this->setCreated("1.10.1", "Whiteboard", "new", "Complete Success", $whiteboard->objectToArray());
	}

	/**
	* @api {get} /mongo/whiteboard/open/:token/:id Open a whiteboard
	* @apiName openWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Open the given whiteboard
	* @apiVersion 0.2.0
	*
	*/
	public function openWhiteboardAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
		if (!$user)
			return ($this->setBadTokenError("10.3.3", "Whiteboard", "open"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			 return $this->setBadRequest("Bad Whiteboard Id");

			 if (!$whiteboard)
	  			return $this->setBadRequest("10.3.4", "Whiteboard", "open", "Bad Parameter: id");

	 		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 1)
	 			return ($this->setNoRightsError("10.3.9", "Whiteboard", "open"));

	 		if ($whiteboard->getDeletedAt())
	 			return $this->setBadRequest("Whiteboard Deleted");

	 		$arr = $whiteboard->objectToArray();

	 		$arr["content"] =  $this->serializeInArray($whiteboard->getObjects());

	 		return $this->setSuccess("1.10.1", "Whiteboard", "open", "Complete Success", $arr);
	 	}

	/**
	* @api {put} mongo/whiteboard/pushdraw/:id Push a whiteboard modification
	* @apiName pushDrawOnWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Push a whiteboard modification
	* @apiVersion 0.2.0
	*
	*/
	public function pushDrawAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('modification', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("10.4.6", "Whiteboard", "push", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("10.4.3", "Whiteboard", "push"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.4.4", "Whiteboard", "push", "Bad Parameter: id");

		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 2)
			return ($this->setNoRightsError("10.4.9", "Whiteboard", "push"));

		if ($content->modification == "add")
		{
			if (!array_key_exists('object', $content))
	 			return $this->setBadRequest("10.4.6", "Whiteboard", "push", "Missing Parameter");
			$object = new WhiteboardObject();
			$object->setWhiteboardId($id);
			$object->setWhiteboard($whiteboard);
			$object->setObject(json_encode($content->object));
			$object->setCreatedAt(new DateTime('now'));
		}
		else {
			if (!array_key_exists('objectId', $content))
	 			return $this->setBadRequest("10.4.6", "Whiteboard", "push", "Missing Parameter");
			$object = $em->getRepository('MongoBundle:WhiteboardObject')->find($content->objectId);
			$object->setDeletedAt(new DateTime('now'));
		}

		$em->persist($object);
		$em->flush();

		return $this->setSuccess("1.10.1", "Whiteboard", "push", "Complete Success", $object->objectToArray());
	}

	/**
	* @api {post} /mongo/whiteboard/pulldraw/:id Pull a whiteboard modification
	* @apiName pullDrawOnWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Pull whiteboard modifications
	* @apiVersion 0.2.0
	*
	*/
	public function pullDrawAction(Request $request, $id)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('lastUpdate', $content) || !array_key_exists('token', $content))
			return $this->setBadRequest("10.5.6", "Whiteboard", "pull", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("10.5.3", "Whiteboard", "pull"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($id);
		if (!$whiteboard)
 			return $this->setBadRequest("10.5.4", "Whiteboard", "pull", "Bad Parameter: id");

		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 1)
			 return ($this->setNoRightsError("10.5.9", "Whiteboard", "pull"));

		$date = new \DateTime($content->lastUpdate);

		// $toAddQuery = $em->createQuery(
		// 							    'SELECT objects
		// 							    FROM MongoBundle\Document\WhiteboardObject objects
		// 							    WHERE objects.whiteboardId = :id AND objects.createdAt > :date AND objects.deletedAt IS NULL')
		// 									->setParameters(array('date' => $date, 'id' => $id));
		$toAddQuery = $em->getRepository('MongBundle:WhiteboardObject')->createQueryBuilder('obj')
											->where("obj.whiteboardId == :id")
											->andWhere("obj.createdAt > :date")
											->andWhere("obj.deletedAt IS NULL")
											->setParameters(array('date' => $date, 'id' => $id));
		$to_add = $toAddQuery->getQuery()->getResult();;
		$toAdd = array();
		foreach ($to_add as $key => $value) {
			$toAdd[] = $value->objectToArray();
		}
		// $toDelQuery = $em->createQuery(
		// 							    'SELECT objects
		// 							    FROM MongoBundle\Document\WhiteboardObject objects
		// 							    WHERE objects.whiteboardId = :id AND objects.deletedAt > :date AND objects.deletedAt IS NOT NULL')
		// 									->setParameters(array('date' => $date, 'id' => $id));
		$toAddQuery = $em->getRepository('MongBundle:WhiteboardObject')->createQueryBuilder('obj')
											->where("obj.whiteboardId == :id")
											->andWhere("obj.deletedAt > :date")
											->andWhere("obj.deletedAt IS NOT NULL")
											->setParameters(array('date' => $date, 'id' => $id));
		$to_del = $toDelQuery->getResult();
		$toDel = array();
		foreach ($to_del as $key => $value) {
			$toDel[] = $value->objectToArray();
		}

		return $this->setSuccess("1.10.1", "Whiteboard", "push", "Complete Success", array('add' => $toAdd, 'delete' => $toDel));
	}

	/**
	* @api {delete} /mongo/whiteboard/delete/:token/:id Delete a Whiteboard
	* @apiName deleteWhiteboard
	* @apiGroup Whiteboard
	* @apiDescription Delete a whiteboard
	* @apiVersion 0.2.0
	*
	*/
	public function delWhiteboardAction(Request $request, $token, $id)
	{
		$user = $this->checkToken($token);
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
				// $em->remove($whiteboard);
				$em->flush();
		}

		$response["info"]["return_code"] = "1.10.1";
		$response["info"]["return_message"] = "Whiteboard - delete - Complete Success";
		return new JsonResponse($response);
	}

	/**
	* @api {put} /mongo/whiteboard/deleteObject Delete object
	* @apiName deleteObject
	* @apiGroup Whiteboard
	* @apiDescription Determiner object(s) to delete from rubber position and radius
	* @apiVersion 0.2.0
	*
	*/
	public function deleteObjectAction(Request $request)
	{
		$content = $request->getContent();
		$content = json_decode($content);
		$content = $content->data;

		if (!array_key_exists('token', $content) || !array_key_exists('center', $content) || !array_key_exists('radius', $content) || !array_key_exists('whiteboardId', $content))
			return $this->setBadRequest("10.7.6", "Whiteboard", "deleteObject", "Missing Parameter");

		$user = $this->checkToken($content->token);
		if (!$user)
			return ($this->setBadTokenError("10.7.3", "Whiteboard", "deleteObject"));

		$em = $this->get('doctrine_mongodb')->getManager();
		$whiteboard =  $em->getRepository('MongoBundle:Whiteboard')->find($content->whiteboardId);
		if (!$whiteboard)
			return $this->setBadRequest("10.7.4", "Whiteboard", "deleteObject", "Bad Parameter: whiteboardId");

		if ($this->checkRoles($user, $whiteboard->getProjects()->getId(), "whiteboard") < 2)
			 return ($this->setNoRightsError("10.7.9", "Whiteboard", "deleteObject"));

		$objects =  $em->getRepository('MongoBundle:WhiteboardObject')->findBy(array("whiteboardId" => $whiteboard->getId(), "deletedAt" => NULL));

		$toDel = $this->checkDeletion($objects, $content->center, $content->radius);

		$data = array();
		foreach ($toDel as $key => $value) {
			$value->setDeletedAt(new DateTime("now"));
			$em->persist($value);
			$em->flush();

			$data[] = $value->objectToArray();
		}
		return $this->setSuccess("1.10.1", "Whiteboard", "deleteObject", "Complete Success", array("array" => $data));
	}

	private function checkDeletion($objects, $center, $radius)
	{
		$toDel = array();
		foreach ($objects as $key => $object) {
			$obj = json_decode($object->getObject());
			switch ($obj->type) {
				case 'LINE':
					if ($this->intersectionWithLine($center, $radius, array("x" => $obj->positionStart->x, "y" => $obj->positionStart->y), array("x" => $obj->positionEnd->x, "y" => $obj->positionEnd->y)))
						$toDel[] = $object;
					break;
				case 'HANDWRITE':
					if ($this->intersectionWithHandwrite($center, $radius, array("x" => $obj->positionStart->x, "y" => $obj->positionStart->y), array("x" => $obj->positionEnd->x, "y" => $obj->positionEnd->y)))
						$toDel[] = $object;
					break;
				case 'RECTANGLE':
					$square = $this->determineMinimalSquare($obj);
					if ($this->intersectionWithSquare($center, $radius, $square))
						$toDel[] = $object;
					break;
				case 'TEXT':
				$square = $this->determineMinimalSquare($obj);
					if ($this->intersectionWithSquare($center, $radius, $square))
						$toDel[] = $object;
					break;
				case 'DIAMOND':
					$square = $this->determineMinimalSquare($obj);
					$diamond = $this->determineDiamond($square);
					if ($this->intersectionWithSquare($center, $radius, $diamond))
						$toDel[] = $object;
					break;
				case 'ELLIPSE':
					if ($this->intersectionWithEllipse($center, $radius, $obj))
						$toDel[] = $object;
					break;
				default:
					$square = $this->determineMinimalSquare($obj);
					if ($this->intersectionWithSquare($center, $radius, $square))
						$toDel[] = $object;
					break;
			}
		}
		return $toDel;
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
			foreach ($obj->points as $key => $point) {
				if (!$prev)
					$prev = $point;
				else {
					if ($this->intersectionWithLine($center, $radius, $prev, $point))
						return true;
					$prev = $point;
				}
			}
		return false;
	}

	private function intersectionWithSquare($center, $radius, $square)
	{
		if ($this->intersectionWithLine($center, $radius, $square["A"], $square["B"]) || $this->intersectionWithLine($center, $radius, $square["B"], $square["C"])
			|| $this->intersectionWithLine($center, $radius, $square["C"], $square["D"]) || $this->intersectionWithLine($center, $radius, $square["D"], $square["A"]))
			return true;
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
