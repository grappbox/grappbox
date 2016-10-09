<?php

namespace SQLBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use \DateTime;

use SQLBundle\Entity\CloudTransfer;
use SQLBundle\Entity\CloudSecuredFileMetadata;

use Sabre\DAV\Client;
use League\Flysystem\WebDAV\WebDAVAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\FileNotFoundException;

class CurlRequest {
	protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
	protected $_url;
	protected $_timeout;
	protected $_cookieFileLocation = './cookie.txt';
	protected $_post;
	protected $_postFields;
	protected $_referer ="http://cloud.grappbox.com/";

	protected $_session;
	protected $_webpage;
	protected $_status;
	public    $authentication = 1;
	public    $auth_name      = 'grappbox';
	public    $auth_pass      = 'GolfBravo$$';

	public function __construct($timeOut = 30)
	{
		$this->_post = false;
		$this->_timeout = $timeOut;
		$this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt';
	}

	public function setReferer($referer)
	{
		$this->_referer = $referer;
	}

	public function setPost($postFields)
	{
		$this->_post = true;
		$this->_postFields = $postFields;
	}

	public function setUserAgent($userAgent)
	{
		$this->_useragent = $userAgent;
	}

	public function createCurl($url = 'nul')
	{
		if($url != 'nul'){
			$this->_url = $url;
		}

		$curlRequest = curl_init();

		curl_setopt($curlRequest,CURLOPT_URL,$this->_url);
		curl_setopt($curlRequest,CURLOPT_TIMEOUT,$this->_timeout);
		curl_setopt($curlRequest,CURLOPT_RETURNTRANSFER,true);

		if($this->authentication == 1){
		curl_setopt($curlRequest, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass);
		}
		if($this->_post)
		{
			curl_setopt($curlRequest,CURLOPT_POST,true);
			curl_setopt($curlRequest,CURLOPT_POSTFIELDS,$this->_postFields);
			curl_setopt($curlRequest, CURLOPT_HTTPHEADER, array("Content-Type" => "multipart/form-data"));
		}
		else {
			curl_setopt($curlRequest,CURLOPT_HTTPGET,true);
		}

		$this->_webpage = curl_exec($curlRequest);
		$this->_status = curl_getinfo($curlRequest,CURLINFO_HTTP_CODE);
		curl_close($curlRequest);
		return $this->_webpage;
	}

	public function getHttpStatus()
	{
		return $this->_status;
	}

	public function __tostring(){
		return $this->_webpage;
	}
}

/**
*  @IgnoreAnnotation("apiName")
*  @IgnoreAnnotation("apiDescription")
*  @IgnoreAnnotation("apiGroup")
*  @IgnoreAnnotation("apiVersion")
*  @IgnoreAnnotation("apiSuccess")
*  @IgnoreAnnotation("apiSuccessExample")
*  @IgnoreAnnotation("apiError")
*  @IgnoreAnnotation("apiErrorExample")
*  @IgnoreAnnotation("apiParam")
*  @IgnoreAnnotation("apiParamExample")
*/

class CloudController extends Controller
{
	private static $settingsDAV = null;

	public function __construct()
	{
			if (is_null(self::$settingsDAV))
				self::$settingsDAV = array(
				'baseUri' => 'http://cloud.grappbox.com/remote.php/webdav/',
				'userName' => 'grappbox',
				'password' => 'GolfBravo$$'
				);
	}

	private function getUserId($token)
	{
		// $userRepository = $this->getDoctrine()->getRepository("SQLBundle:User");
		// $user = $userRepository->findOneByToken($token);
		// return (is_null($user) ? -1 : $user->getId());
		$authRepository = $this->getDoctrine()->getRepository("SQLBundle:Authentication");
		$user = $authRepository->findOneByToken($token);
		return (is_null($user) ? -1 : $user->getUser()->getId());
	}

	private function checkUserCloudAuthorization($userId, $idProject)
	{
		$db = $this->getDoctrine();
		$roles = $db->getRepository("SQLBundle:ProjectUserRole")->findBy(array("projectId" => $idProject, "userId" => $userId));
		foreach($roles as $role)
		{
			if (is_null($role))
				continue;
			$roleTable = $db->getRepository("SQLBundle:Role")->findOneById($role->getRoleId());
			if (!is_null($roleTable) && $roleTable->getCloud() > 0)
				return $roleTable->getCloud();
		}
		return (-1);
	}

	private function grappSha1($str) // note : PLEASE DON'T REMOVE THAT FUNCTION! GOD DAMN IT!
	{
		return $str; //TODO : code the Grappbox sha-1 algorithm when assigned people ready
		// TODO : copy code in the corresponding function in ProjectController (before updateInformation method)
	}

	/**
	*
	* @api {post} /0.3/cloud/stream/:project_id/[:safe_password] Open a new stream in order to upload file
	* @apiVersion 0.3.0
	* @apiDescription This method is here to create an upload process between API and Cloud.
	* @apiGroup Cloud
	* @apiName Stream opening
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {string} [session_infos.safe_password] The password of the project safe. Use it only if the future file will be in the safe.
	* @apiParam {Object[]} data All informations about the core request have to be here
	* @apiParam {string} data.filename The filename of the future file with extension
	* @apiParam {string} data.path The path where the future file will be uploaded
	* @apiParam {string} [data.password] The password to protect the file. Use it only if password protected required.
	* @apiParamExample {json} Request Example:
	*	{
	*		"data": {
	*			"path" : "/LabEIP/Golum",
	*			"password" : "My Precious!"
	*			"filename" : "The ring.worldDomination"
	*		}
	* }
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	* @apiSuccess (200) {Object} data All informations about response will be here.
	* @apiSuccess (200) {Number} data.stream_id The id of the stream newly created.
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*				"return_code" : "1.3.1",
	*				"return_message" : "Cloud - openStreamAction - Complete success"
	*		},
	*		"data" : {
	*			"stream_id" : 1
	*		}
	*	}
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	* HTTP/1.1 206 Partial Content
	* {
	*		"info" : {
	*				"return_code" : "3.1.9",
	*				"return_message" : "Cloud - openStreamAction - Insufficient Right"
	*		}
	* }
	*/
	/**
	*
	* @api {post} /V0.2/cloud/stream/:token/:project_id/[:safe_password] Open a new stream in order to upload file
	* @apiVersion 0.2.0
	* @apiDescription This method is here to create an upload process between API and Cloud.
	* @apiGroup Cloud
	* @apiName Stream opening
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {string} [session_infos.safe_password] The password of the project safe. Use it only if the future file will be in the safe.
	* @apiParam {Object[]} data All informations about the core request have to be here
	* @apiParam {string} data.filename The filename of the future file with extension
	* @apiParam {string} data.path The path where the future file will be uploaded
	* @apiParam {string} [data.password] The password to protect the file. Use it only if password protected required.
	* @apiParamExample {json} Request Example:
	*	{
	*		"data": {
	*			"path" : "/LabEIP/Golum",
	*			"password" : "My Precious!"
	*			"filename" : "The ring.worldDomination"
	*		}
	* }
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	* @apiSuccess (200) {Object} data All informations about response will be here.
	* @apiSuccess (200) {Number} data.stream_id The id of the stream newly created.
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*				"return_code" : "1.3.1",
	*				"return_message" : "Cloud - openStreamAction - Complete success"
	*		},
	*		"data" : {
	*			"stream_id" : 1
	*		}
	*	}
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	* HTTP/1.1 206 Partial Content
	* {
	*		"info" : {
	*				"return_code" : "3.1.9",
	*				"return_message" : "Cloud - openStreamAction - Insufficient Right"
	*		}
	* }
	*/
	public function openStreamAction($idProject, $safePassword, Request $request){
		$token = $request->headers->get('Authorization');
		$dbManager = $this->getDoctrine()->getManager();
		$json = json_decode($request->getContent(), true);
		$receivedData = $json["data"];
		$userId = $this->getUserId($token);

		$isSafe = preg_match("/Safe/", $receivedData["path"]);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("SQLBundle:Project")->findOneById($idProject);
			$passwordEncrypted = ($safePassword ? $this->grappSha1($safePassword) : NULL);
		}
		else {
			$project = null;
			$passwordEncrypted = null;
		}
		if (($this->checkUserCloudAuthorization($userId, $idProject) < 2) || ($isSafe && $passwordEncrypted != $project->getSafePassword()))
		{
			$response["info"]["return_code"] = "3.1.9";
			$response["info"]["return_message"] = "Cloud - openStreamAction - Insufficient Right";
			return new JsonResponse($response, 206);
		}
		$receivedData["filename"] = str_replace(" ", "|", $receivedData["filename"]);
		$receivedData["path"] = str_replace(" ", "|", $receivedData["path"]);

		if ($receivedData["path"][0] != "/")
		{
			$response["info"]["return_code"] = "3.1.4";
			$response["info"]["return_message"] = "Cloud - openStreamAction - Bad Parameter";
			return new JsonResponse($response, 206);
		}
		$em = $this->getDoctrine()->getManager();
		$stream = new CloudTransfer();
		$receivedData["filename"] = str_replace(',', '', $receivedData["filename"]);
		if (substr($receivedData["path"], -1) == '/')
			$receivedData["path"] = substr($receivedData["path"], 0, -1);
		$stream->setCreatorId($userId)
					 ->setFilename(str_replace(',', '', $receivedData["filename"]))
					 ->setPath('/GrappBox|Projects/'.(string)$idProject.$receivedData["path"])
					 ->setPassword(isset($receivedData["password"]) && !empty($receivedData["password"]) ? $receivedData["password"] : null)
					 ->setCreationDate(new DateTime("now"))
					 ->setDeletionDate(null);
		$em->persist($stream);
		$em->flush();

		$this->get('service_stat')->updateCloudStat($idProject, $token, $request);

		$response["info"]["return_code"] = "1.3.1";
		$response["info"]["return_message"] = "Cloud - openStreamAction - Complete Success";
		$response["data"]["stream_id"] = $stream->getId();
		return new JsonResponse($response);
	}

	/**
	*
	* @api {delete} /0.3/cloud/stream/:projectId/:streamId Close a stream in order to complete an upload
	* @apiVersion 0.3.0
	* @apiDescription This method is here to finalize an upload and make the file downloadable.
	* @apiGroup Cloud
	* @apiName Stream closing
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} project_id The project id to execute the command.
	* @apiParam {Number} stream_id The id of the stream to close.
	* @apiParamExample {curl} Request Example:
	*	curl -X DELETE http://api.grappbox.com/0.3/cloud/stream/myToken/42/1
	*
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.2.1,
	*			"return_message" : "Cloud - closeStreamAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.2.9,
	*			"return_message" : "Cloud - closeStreamAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {delete} /V0.2/cloud/stream/:token/:projectId/:streamId Close a stream in order to complete an upload
	* @apiVersion 0.2.0
	* @apiDescription This method is here to finalize an upload and make the file downloadable.
	* @apiGroup Cloud
	* @apiName Stream closing
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} project_id The project id to execute the command.
	* @apiParam {Number} stream_id The id of the stream to close.
	* @apiParamExample {curl} Request Example:
	*	curl -X DELETE http://api.grappbox.com/api_dev.php/V0.2/cloud/stream/myToken/42/1
	*
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.2.1,
	*			"return_message" : "Cloud - closeStreamAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.2.9,
	*			"return_message" : "Cloud - closeStreamAction - Insufficient Right"
	*		}
	*	}
	*/
	public function closeStreamAction($projectId, $streamId, Request $request){
		$dbManager = $this->getDoctrine()->getManager();
		$cloudTransferRepository = $this->getDoctrine()->getRepository("SQLBundle:CloudTransfer");
		$em = $this->getDoctrine()->getManager();
		$stream = $cloudTransferRepository->find($streamId);
		$user_id = $this->getUserId($request->headers->get('Authorization'));
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
		{
			$response["info"]["return_code"] = "3.2.9";
			$response["info"]["return_message"] = "Cloud - closeStreamAction - Insufficient Right";
			return new JsonResponse($response, 206);
		}

		//Here the user have the authorization to close this stream
		if (!is_null($stream->getPassword()))
		{
			//Here add the CloudSecuredFileMetadata infos
			$meta = new CloudSecuredFileMetadata();
			$meta->setFilename($stream->getFilename())
					 ->setPassword($stream->getPassword())
					 ->setCreationDate(new DateTime("now"))
					 ->setCloudPath($stream->getPath());
			$em->persist($meta);
		}

		$stream->setDeletionDate(new DateTime("now"));
		$shareRequest = new CurlRequest();
		$shareRequest->setPost(array(
			"path" => (string)($stream->getPath()."/".$stream->getFilename()),
			"shareType" => (int)3,
			"publicUpload" => (bool)false,
			"permissions" => (int)1
		));
		$shareRequest->createCurl("http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/api/v1/shares");
		$em->persist($stream);
		$em->flush();
		$response["info"]["return_code"] = "1.3.1";
		$response["info"]["return_message"] = "Cloud - closeStreamAction - Complete Success";
		return new JsonResponse($response);
	}

	/**
	*
	* @api {put} /0.3/cloud/file send a file chunk.
	* @apiVersion 0.3.0
	* @apiDescription This method is there to upload a file in the given project cloud. You have to open a stream before.
	* @apiGroup Cloud
	* @apiName Send file
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Object[]} data All informations about the core request have to be here
	* @apiParam {Number} data.project_id The project id to execute the command.
	* @apiParam {Number} data.stream_id The stream id which contains the uploaded file metadata (use POST stream action route to open one)
	* @apiParam {Number} data.chunk_numbers The numbers of chunk you will upload for this file.
	* @apiParam {Number} data.current_chunk The index of current chunk. This start to 0 and end to (chunk_numbers - 1)
	* @apiParam {string} data.file_chunk The file chunk encoded in base64
	* @apiParamExample {json} Request Example:
	*	{
	*		"data": {
	*			"stream_id" : 21,
	*			"project_id": 42,
	*			"chunk_numbers" : 2,
	*			"current_chunk" : 1,
	*			"file_chunk" : "Here put your chunk encoded in base 64"
	*		}
	*	}
	*
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.3.1,
	*			"return_message" : "Cloud - closeStreamAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.3.9,
	*			"return_message" : "Cloud - sendFileAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {put} /V0.2/cloud/file send a file chunk.
	* @apiVersion 0.2.0
	* @apiDescription This method is there to upload a file in the given project cloud. You have to open a stream before.
	* @apiGroup Cloud
	* @apiName Send file
	* @apiParam {Object[]} data All informations about the core request have to be here
	* @apiParam {string} data.token The token of authenticated user.
	* @apiParam {Number} data.project_id The project id to execute the command.
	* @apiParam {Number} data.stream_id The stream id which contains the uploaded file metadata (use POST stream action route to open one)
	* @apiParam {Number} data.chunk_numbers The numbers of chunk you will upload for this file.
	* @apiParam {Number} data.current_chunk The index of current chunk. This start to 0 and end to (chunk_numbers - 1)
	* @apiParam {string} data.file_chunk The file chunk encoded in base64
	* @apiParamExample {json} Request Example:
	*	{
	*		"data": {
	*			"token": "48q98d"
	*			"stream_id" : 21,
	*			"project_id": 42,
	*			"chunk_numbers" : 2,
	*			"current_chunk" : 1,
	*			"file_chunk" : "Here put your chunk encoded in base 64"
	*		}
	*	}
	*
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.3.1,
	*			"return_message" : "Cloud - closeStreamAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.3.9,
	*			"return_message" : "Cloud - sendFileAction - Insufficient Right"
	*		}
	*	}
	*/
	public function sendFileAction(Request $request){
		$cloudTransferRepository = $this->getDoctrine()->getRepository("SQLBundle:CloudTransfer");
		$json = json_decode($request->getContent(), true);
		$token = $request->headers->get('Authorization');
		$receivedData = $json["data"];
		$user_id = $this->getUserId($token);
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
		{
			$response["info"]["return_code"] = "3.3.9";
			$response["info"]["return_message"] = "Cloud - sendFileAction - Insufficient Right";
			return new JsonResponse($response, 206);
		}

		//Here the user have the right authorization, so upload the file's chunk
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$flysystem->put($stream->getPath() ."/".$stream->getFilename().'-chunking-'.(string)$receivedData["stream_id"].'-'.$receivedData["chunk_numbers"].'-'.$receivedData["current_chunk"], (string)base64_decode($receivedData["file_chunk"]));
		$response["info"]["return_code"] = "1.3.1";
		$response["info"]["return_message"] = "Cloud - sendFileAction - Complete Success";
		return new JsonResponse($response);
	}

	/**
	*
	* @api {get} /0.3/cloud/list/:idProject/:path[/:passwordSafe] Cloud LS
	* @apiVersion 0.3.0
	* @apiDescription Get the list of a given directory.
	* @apiGroup Cloud
	* @apiName List directory
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} path The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the user want the safe content
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/0.3/cloud/list/minus5percent/1/,Sauron/satan
	*
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	* @apiSuccess (200) {Object} data All informations about response will be here.
	* @apiSuccess (200) {Object[]} data.array List of all entity found by owncloud
	* @apiSuccess (200) {string} data.array.type The entity's type
	* @apiSuccess (200) {string} data.array.filename The entity's name in owncloud
	* @apiSuccess (200) {boolean} data.array.is_secured True if the entity is a password protected file.
	* @apiSuccess (200) {string} [data.array.size] (Only on files) The size of the entity in bytes
	* @apiSuccess (200) {string} [data.array.mimetype] (Only on files) The mimetype of the file.
	* @apiSuccess (200) {Number} [data.array.timestamp] (Only on files) The timestamp of the last modification.
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.4.1,
	*			"return_message" : "Cloud - closeStreamAction - Complete Success"
	*		},
	*		"data" : {
	*			"array" : [{
	*				"type" : "file",
	*				"filename" : "Doulan.txt",
	*				"is_secured" : false,
	*				"size" : "18",
	*				"mimetype" : "text/plain",
	*				"last_modified" : {"date" : "2016-01-31 21:56:00.000000", "timezone_type" : 3, "timezone" : "Europe/Paris" }
	*			},
	*			{
	*				"type" : "dir",
	*				"filename" : "Safe",
	*				is_secured : true,
	*			}]
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.4.9,
	*			"return_message" : "Cloud - sendFileAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {get} /V0.2/cloud/list/:token/:idProject/:path[/:passwordSafe] Cloud LS
	* @apiVersion 0.2.0
	* @apiDescription Get the list of a given directory.
	* @apiGroup Cloud
	* @apiName List directory
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} path The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the user want the safe content
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.2/cloud/list/minus5percent/1/,Sauron/satan
	*
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	* @apiSuccess (200) {Object} data All informations about response will be here.
	* @apiSuccess (200) {Object[]} data.array List of all entity found by owncloud
	* @apiSuccess (200) {string} data.array.type The entity's type
	* @apiSuccess (200) {string} data.array.filename The entity's name in owncloud
	* @apiSuccess (200) {boolean} data.array.is_secured True if the entity is a password protected file.
	* @apiSuccess (200) {string} [data.array.size] (Only on files) The size of the entity in bytes
	* @apiSuccess (200) {string} [data.array.mimetype] (Only on files) The mimetype of the file.
	* @apiSuccess (200) {Number} [data.array.timestamp] (Only on files) The timestamp of the last modification.
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.4.1,
	*			"return_message" : "Cloud - closeStreamAction - Complete Success"
	*		},
	*		"data" : {
	*			"array" : [{
	*				"type" : "file",
	*				"filename" : "Doulan.txt",
	*				"is_secured" : false,
	*				"size" : "18",
	*				"mimetype" : "text/plain",
	*				"last_modified" : {"date" : "2016-01-31 21:56:00.000000", "timezone_type" : 3, "timezone" : "Europe/Paris" }
	*			},
	*			{
	*				"type" : "dir",
	*				"filename" : "Safe",
	*				is_secured : true,
	*			}]
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.4.9,
	*			"return_message" : "Cloud - sendFileAction - Insufficient Right"
	*		}
	*	}
	*/
	public function getListAction($idProject, $path, $password, Request $request)
	{
		$token = $request->headers->get('Authorization');
		$userId = $this->getUserId($token);
		$isSafe = preg_match("/Safe/", $path);
		if ($isSafe){
			$project = $this->getDoctrine()->getRepository("SQLBundle:Project")->findOneById($idProject);
			$passwordEncrypted = $password; // TODO : SHA-1 512 Hashing when algo created!
		}
		else{
			$project = null;
			$passwordEncrypted = null;
		}
		if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) < 1 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
		{
			$response["info"]["return_code"] = "3.4.9";
			$response["info"]["return_message"] = "Cloud - getListAction - Insufficient Right";
			return new JsonResponse($response, 206);
		}

		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$prepath = str_replace(" ", "|", str_replace(",", "/", $path));
		$rpath = "/GrappBox|Projects/".(string)($idProject).$prepath;
		$securedFileRepository = $this->getDoctrine()->getRepository("SQLBundle:CloudSecuredFileMetadata");

		$content = str_replace("|", " ", $adapter->listContents($rpath));
		if (substr($rpath, -1) == '/')
			$rpath = substr($rpath, 0, -1);
		foreach ($content as $i => $row)
		{
			$content[$i]["path"] = str_replace("remote.php/webdav/GrappBox%7cProjects/".(string)$idProject.$prepath.($prepath == "/" ? "": "/"), "", $content[$i]["path"]);
			$filename = split('/', $content[$i]["path"]);
			$filename = $filename[count($filename) - 1];
			$filename = urldecode($filename);
			$content[$i]["is_secured"] = (!($securedFileRepository->findOneBy(array("filename" => $filename, "cloudPath" => $rpath)) == null) || $filename == "Safe");
			$filename = str_replace('|', ' ', $filename);
			$content[$i]["filename"] = $filename;
			unset($content[$i]["path"]);
			if ($content[$i]["type"] == "file")
			{
					$content[$i]["last_modified"] = new DateTime();
					$content[$i]["last_modified"]->setTimestamp($content[$i]["timestamp"]);
					unset($content[$i]["timestamp"]);
			}


		}
		$response["info"]["return_code"] = "1.3.1";
		$response["info"]["return_message"] = "Cloud - getListAction - Complete Success";
		$response["data"]["array"] = $content;
		return new JsonResponse($response);
	}

	/**
	*
	* @api {get} /0.3/cloud/file/:cloudPath/:idProject/[:passwordSafe] Download a file
	* @apiVersion 0.3.0
	* @apiDescription This method is there to start a download.
	* @apiGroup Cloud
	* @apiName Download file
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {string} CloudPath The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the file is in the safe
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/cloud/getfile/,Sauron/minus5percent/1/mustache/satan
	* @apiSuccess (203) {string} Header HTTP/1.1 203 Redirect (You will be redirected on the file URL in GET method (for download))
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.5.9,
	*			"return_message" : "Cloud - sendFileAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {get} /V0.2/cloud/file/:cloudPath/:token/:idProject/[:passwordSafe] Download a file
	* @apiVersion 0.2.0
	* @apiDescription This method is there to start a download.
	* @apiGroup Cloud
	* @apiName Download file
	* @apiParam {string} CloudPath The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the file is in the safe
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/cloud/getfile/,Sauron/minus5percent/1/mustache/satan
	* @apiSuccess (203) {string} Header HTTP/1.1 203 Redirect (You will be redirected on the file URL in GET method (for download))
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.5.9,
	*			"return_message" : "Cloud - sendFileAction - Insufficient Right"
	*		}
	*	}
	*/
	public function getFileAction($cloudPath, $idProject, $passwordSafe, Request $request){
		$token = $request->headers->get('Authorization');
		$userId = $this->getUserId($token);
		$cloudPath = str_replace(",", "/", $cloudPath);
		$cloudPath = str_replace(" ", "|", $cloudPath);
		$cloudPathArray = explode('/', $cloudPath);
		$filename = $cloudPathArray[count($cloudPathArray) - 1];
		unset($cloudPathArray[count($cloudPathArray) - 1]);
		$cloudBasePath = implode('/', $cloudPathArray);
		if ($cloudBasePath == "" || $cloudBasePath[0] != "/")
			$cloudBasePath = "/" + $cloudBasePath;
		if ($cloudBasePath === 0)
		   $cloudBasePath = "/";
		$filePassword = $this->getDoctrine()->getRepository("SQLBundle:CloudSecuredFileMetadata")->findOneBy(array("cloudPath" => "/GrappBox|Projects/".(string)$idProject.$cloudBasePath, "filename" => $filename));
		$isSafe = preg_match("/Safe/", $cloudPath);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("SQLBundle:Project")->findOneById($idProject);
			$passwordEncrypted = $this->grappSha1($passwordSafe);
		}
		else {
			$project = NULL;
			$passwordEncrypted = NULL;
		}
		if (!is_null($filePassword) || $userId < 0 || (!is_null($filePassword) && $filePassword->getPassword() != $passwordEncrypted) || $this->checkUserCloudAuthorization($userId, $idProject) < 1 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
		{
			$response["info"]["return_code"] = "3.5.9";
			$response["info"]["return_message"] = "Cloud - getFileAction - Insufficient Right";
			return new JsonResponse($response, 206);
		}

		//Here we have authorization to get the encrypted file, Client have to decrypt it after reception, if it's a secured file
		$path = "http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/api/v1/shares?path=".urlencode("/GrappBox|Projects/".(string)($idProject).$cloudPath);
		$searchRequest = new CurlRequest();
		$searchResult = simplexml_load_string($searchRequest->createCurl($path));
		if ($searchResult->meta->statuscode != 100 ||
			$searchResult->data->element->share_type != "3")
			{
				$response["info"]["return_code"] = "3.5.10";
				$response["info"]["return_message"] = "Cloud - getFileAction - Target file not found";
				return new JsonResponse($response, 206);
			}
	 	header("Location: http://cloud.grappbox.com/index.php/s/".(string)($searchResult->data->element->token)."/download"), true, 204);
		return new Response();
	}

	/**
	*
	* @api {get} /0.3/cloud/filesecured/:cloudPath/:idProject/[:password]/[:passwordSafe] Download a secured file
	* @apiVersion 0.3.0
	* @apiDescription This method is there to start a download.
	* @apiGroup Cloud
	* @apiName Download secured file
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {string} CloudPath The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} [password] The password hashed in a clear way. Use only if file is password protected.
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the file is in the safe
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/0.3/cloud/getfile/,Sauron/minus5percent/1/mustache/satan
	* @apiSuccess (203) {string} Header HTTP/1.1 203 Redirect (You will be redirected on the file URL in GET method (for download))
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.5.9,
	*			"return_message" : "Cloud - sendFileAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {get} /V0.2/cloud/filesecured/:cloudPath/:token/:idProject/[:password]/[:passwordSafe] Download a secured file
	* @apiVersion 0.2.0
	* @apiDescription This method is there to start a download.
	* @apiGroup Cloud
	* @apiName Download secured file
	* @apiParam {string} CloudPath The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} [password] The password hashed in a clear way. Use only if file is password protected.
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the file is in the safe
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.2/cloud/getfile/,Sauron/minus5percent/1/mustache/satan
	* @apiSuccess (203) {string} Header HTTP/1.1 203 Redirect (You will be redirected on the file URL in GET method (for download))
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.5.9,
	*			"return_message" : "Cloud - sendFileAction - Insufficient Right"
	*		}
	*	}
	*/
	public function getFileSecuredAction($cloudPath, $idProject, $password, $passwordSafe = null, Request $request){
		$token = $request->headers->get('Authorization');
		$userId = $this->getUserId($token);
		$passwordFileEncrypted = $this->grappSha1($password);
		$cloudPathArray = explode(',', $cloudPath);
		$filename = $cloudPathArray[count($cloudPathArray) - 1];
		unset($cloudPathArray[count($cloudPathArray) - 1]);
		$cloudBasePath = implode('/', $cloudPathArray);
		if ($cloudBasePath == "")
			$cloudBasePath = "/";
		if (substr($cloudBasePath, -1) == "/")
			$cloudBasePath = substr($cloudBasePath, 0, -1);
		$cloudBasePath = preg_replace("/\/\//", "/", $cloudBasePath);
		$filePassword = $this->getDoctrine()->getRepository("SQLBundle:CloudSecuredFileMetadata")->findOneBy(array("cloudPath" => "/GrappBox|Projects/".(string)$idProject.$cloudBasePath, "filename" => $filename));

		$isSafe = preg_match("/Safe/", $cloudPath);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("SQLBundle:Project")->findOneById($idProject);
			$passwordEncrypted = $this->grappSha1($passwordSafe);
		}
		else {
			$project = NULL;
			$passwordEncrypted = NULL;
		}
		if ($userId < 0 || (!is_null($filePassword) && $filePassword->getPassword() != $passwordFileEncrypted) || $this->checkUserCloudAuthorization($userId, $idProject) < 1 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
		{
			$response["info"]["return_code"] = "3.5.9";
			$response["info"]["return_message"] = "Cloud - getFileSecuredAction - Insufficient Right";
			return new JsonResponse($response, 206);
		}

		//Here we have authorization to get the encrypted file, Client have to decrypt it after reception, if it's a secured file
		$cloudPath = str_replace(',', '/', $cloudPath);
		$path = "http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/api/v1/shares?path=".urlencode("/GrappBox|Projects/".(string)($idProject).$cloudPath);
		$searchRequest = new CurlRequest();
		$searchResult = simplexml_load_string($searchRequest->createCurl($path));
		if ($searchResult->meta->statuscode != 100 ||
			$searchResult->data->element->share_type != "3")
			{
				$response["info"]["return_code"] = "3.5.10";
				$response["info"]["return_message"] = "Cloud - getFileSecuredAction - Target file not found";
				return new JsonResponse($response, 206);
			}
		return $this->redirect("http://cloud.grappbox.com/index.php/s/".(string)($searchResult->data->element->token)."/download");
	}

	/**
	*
	* @api {put} /0.3/cloud/safepass Set the safe password
	* @apiVersion 0.3.0
	* @apiDescription This method is there to change the safe password for a given project.
	* @apiGroup Cloud
	* @apiName Set Safe Password
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Object[]} data All informations about the core request have to be here
	* @apiParam {Number} data.project_id The project id to execute the command.
	* @apiParam {string} data.password The new password hashed in SHA-1 512
	* @apiParam {string} data.oldPassword The actual password
	* @apiParamExample {json} Request Example:
	*	{
	*		"data": {
	*			"project_id": 42,
	*			"password": "6q8d4zq68d",
	*			"oldPassword": "MyPassword"
	*		}
	*	}
	*
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	* @apiSuccess (200) {Object} data All informations about response will be here.
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.4.1,
	*			"return_message" : "Cloud - setSafePassAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.6.9,
	*			"return_message" : "Cloud - setSafePassAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {put} /v0.2/cloud/safepass Set the safe password
	* @apiVersion 0.2.0
	* @apiDescription This method is there to change the safe password for a given project.
	* @apiGroup Cloud
	* @apiName Set Safe Password
	* @apiParam {Object[]} data All informations about the core request have to be here
	* @apiParam {string} data.token The token of authenticated user.
	* @apiParam {Number} data.project_id The project id to execute the command.
	* @apiParam {string} data.password The new password hashed in SHA-1 512
	* @apiParam {string} data.oldPassword The actual password
	* @apiParamExample {json} Request Example:
	*	{
	*		"data": {
	*			"token": "48q98d"
	*			"project_id": 42,
	*			"password": "6q8d4zq68d",
	*			"oldPassword": "MyPassword"
	*		}
	*	}
	*
	* @apiSuccess (200) {Object} info Informations about the request
	* @apiSuccess (200) {string} info.return_code Request end state code
	* @apiSuccess (200) {string} info.return_message Request end state message (text formated return_code)
	* @apiSuccess (200) {Object} data All informations about response will be here.
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.4.1,
	*			"return_message" : "Cloud - setSafePassAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.6.9,
	*			"return_message" : "Cloud - setSafePassAction - Insufficient Right"
	*		}
	*	}
	*/
	public function setSafePassAction(Request $request)
	{
		$dbManager = $this->getDoctrine()->getManager();
		$json = json_decode($request->getContent(), true);
		$token = $request->headers->get('Authorization');
		$userId = $this->getUserId($token);
		$idProject = (int)$json["data"]["project_id"];
		$project = $this->getDoctrine()->getRepository("SQLBundle:Project")->findOneById($idProject);
		if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) < 2 || is_null($project))
		{
			$response["info"]["return_code"] = "3.6.9";
			$response["info"]["return_message"] = "Cloud - setSafePassAction - Insufficient Success";
			return new JsonResponse($response, 206);
		}

		$passwordEncrypted = ($json["data"]["oldPassword"] ? $this->grappSha1($json["data"]["oldPassword"]) : NULL);
		if ($passwordEncrypted != $project->getSafePassword())
		{
			$response["info"]["return_code"] = "3.6.9";
			$response["info"]["return_message"] = "Cloud - setSafePassAction - Insufficient Right";
			return new JsonResponse($response, 206);
		}

		$project->setSafePassword($json["data"]["password"]);
		$dbManager->persist($project);
		$dbManager->flush();
		$response["info"]["return_code"] = "1.3.1";
		$response["info"]["return_message"] = "Cloud - setSafePassAction - Complete Success";
		return new JsonResponse($response);
	}

	/**
	*
	* @api {delete} /0.3/cloud/file/:project_id/:path/:password Delete a file or directory
	* @apiVersion 0.3.0
	* @apiDescription This method is there to delete something in the cloud
	* @apiGroup Cloud
	* @apiName Delete
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} project_id The project id to execute the command.
	* @apiParam {string} path The path of the file/directory in the cloud (absolute path from the root of the project's cloud)
	* @apiParam {string} [passwordSafe] The project's safe password, in order to delete a file or a directory into the safe. Use only if file or directory into the safe. You can't delete the safe itself!
	* @apiParamExample {curl} Request Example:
	* curl -X DELETE http://api.grappbox.com/0.3/cloud/del/MyToken/1/,Doulan.txt/satan
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.3.1,
	*			"return_message" : "Cloud - delAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.7.9,
	*			"return_message" : "Cloud - delAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {delete} /V0.2/cloud/file/:token/:project_id/:path/:password Delete a file or directory
	* @apiVersion 0.2.0
	* @apiDescription This method is there to delete something in the cloud
	* @apiGroup Cloud
	* @apiName Delete
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} project_id The project id to execute the command.
	* @apiParam {string} path The path of the file/directory in the cloud (absolute path from the root of the project's cloud)
	* @apiParam {string} [passwordSafe] The project's safe password, in order to delete a file or a directory into the safe. Use only if file or directory into the safe. You can't delete the safe itself!
	* @apiParamExample {curl} Request Example:
	* curl -X DELETE http://api.grappbox.com/app_dev.php/V0.2/cloud/del/MyToken/1/,Doulan.txt/satan
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.3.1,
	*			"return_message" : "Cloud - delAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.7.9,
	*			"return_message" : "Cloud - delAction - Insufficient Right"
	*		}
	*	}
	*/
	public function delAction($projectId, $path, $password, Request $request)
	{
		$token = $request->headers->get('Authorization');
		$path = str_replace(',', '/', $path);
		$userId = $this->getUserId($token);
		$apath = explode('/', $path);
		$filename = $apath[count($apath) - 1];
		$apath = array_splice($apath, count($apath) - 1);
		$apath = join('/', $apath);
		if (count($apath) < 2)
			$apath = "/";
		$apath = "/GrappBox|Projects/" . $projectId . $apath;

		$file = $this->getDoctrine()->getRepository("SQLBundle:CloudSecuredFileMetadata")->findOneBy(array("filename" => $filename, "cloudPath" => $apath));
		$isSafe = preg_match("/Safe/", $path);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("SQLBundle:Project")->findOneById($projectId);
			$passwordEncrypted = $this->grappSha1($password);
		}
		else {
			$project = NULL;
			$passwordEncrypted = NULL;
		}
		if ($path == "" || $path == "/" || !is_null($file) || $userId < 0 || $this->checkUserCloudAuthorization($userId, $projectId) < 2 || preg_match("/Safe$/", $path) || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
			{
				$response["info"]["return_code"] = "3.7.9";
				$response["info"]["return_message"] = "Cloud - delAction - Insufficient Right Access";
				return new JsonResponse($response, 206);
			}

		//Now we can delete the file or the directory
		$path = "/GrappBox|Projects/".(string)($projectId).str_replace(' ', '|', $path);
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		try{
				$flysystem->delete($path);
		} catch (FileNotFoundException $e)
		{
			$response["info"]["return_code"] = "3.7.10";
			$response["info"]["return_message"] = "Cloud - delAction - File not found";
			return new JsonResponse($response);
		}

		$this->get('service_stat')->updateCloudStat($projectId, $token, $request);

		$response["info"]["return_code"] = "1.3.1";
		$response["info"]["return_message"] = "Cloud - delAction - Complete Success";
		return new JsonResponse($response);
	}

	/**
	*
	* @api {delete} /0.3/cloud/filesecured/:project_id/:path/:password/:safe_password Delete a secured file or directory
	* @apiVersion 0.3.0
	* @apiDescription This method is there to delete something in the cloud
	* @apiGroup Cloud
	* @apiName Delete secured
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Number} project_id The project id to execute the command.
	* @apiParam {string} path The path of the file/directory in the cloud (absolute path from the root of the project's cloud)
	* @apiParam {string} password the password of the file you want to delete
	* @apiParam {string} [passwordSafe] The project's safe password, in order to delete a file or a directory into the safe. Use only if file or directory into the safe. You can't delete the safe itself!
	* @apiParamExample {curl} Request Example:
	* curl -X DELETE http://api.grappbox.com/0.3/cloud/del/MyToken/1/,Doulan.txt/satan
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.3.1,
	*			"return_message" : "Cloud - delAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.7.9,
	*			"return_message" : "Cloud - delAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {delete} /V0.2/cloud/filesecured/:token/:project_id/:path/:password/:safe_password Delete a secured file or directory
	* @apiVersion 0.2.0
	* @apiDescription This method is there to delete something in the cloud
	* @apiGroup Cloud
	* @apiName Delete secured
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} project_id The project id to execute the command.
	* @apiParam {string} path The path of the file/directory in the cloud (absolute path from the root of the project's cloud)
	* @apiParam {string} password the password of the file you want to delete
	* @apiParam {string} [passwordSafe] The project's safe password, in order to delete a file or a directory into the safe. Use only if file or directory into the safe. You can't delete the safe itself!
	* @apiParamExample {curl} Request Example:
	* curl -X DELETE http://api.grappbox.com/app_dev.php/V0.2/cloud/del/MyToken/1/,Doulan.txt/satan
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.3.1,
	*			"return_message" : "Cloud - delAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.7.9,
	*			"return_message" : "Cloud - delAction - Insufficient Right"
	*		}
	*	}
	*/
	public function delSecuredAction($projectId, $path, $password, $safe_password, Request $request)
	{
		$token = $request->headers->get('Authorization');
		$path = str_replace(',', '/', $path);
		$path = str_replace(' ', '|', $path);
		$userId = $this->getUserId($token);
		$apath = explode('/', $path);
		$filename = $apath[count($apath) - 1];
		unset($apath[count($apath) - 1]);
		$apath = join('/', $apath);

		if (isset($apath[0]) && $apath[0] != "/")
			$apath = "/" + $apath;
		else if (!isset($apath[0])) {
			$apath = "/";
		}

		$apath = "/GrappBox|Projects/" . $projectId . $apath;
		$apath = preg_replace("/\/\//", "/", $apath);
		if (substr($apath, -1) == "/")
			$apath = substr($apath, 0, -1);
		$file = $this->getDoctrine()->getRepository("SQLBundle:CloudSecuredFileMetadata")->findOneBy(array("filename" => $filename, "cloudPath" => $apath));
		$isSafe = preg_match("/Safe/", $path);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("SQLBundle:Project")->findOneById($projectId);
			$passwordEncrypted = $this->grappSha1($safe_password);
		}
		else {
			$project = NULL;
			$passwordEncrypted = NULL;
		}
		if ($path == "/" || $path == "" || is_null($file) || (!is_null($file) && $this->grappSha1($password) != $file->getPassword()) || $userId < 0 || $this->checkUserCloudAuthorization($userId, $projectId) < 2 || preg_match("/Safe$/", $path) || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
			{
				$response["info"]["return_code"] = "3.9.9";
				$response["info"]["return_message"] = "Cloud - delSafeAction - Insufficient Right Access";
				return new JsonResponse($response, 206);
			}

		//Now we can delete the file or the directory
		$path = "/GrappBox|Projects/".(string)($projectId).str_replace(' ', '|', $path);
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$flysystem->delete($path);
		$this->getDoctrine()->getManager()->remove($file);
		$this->getDoctrine()->getManager()->flush();

		$this->get('service_stat')->updateCloudStat($projectId, $token, $request);

		$response["info"]["return_code"] = "1.3.1";
		$response["info"]["return_message"] = "Cloud - delAction - Complete Success";
		return new JsonResponse($response);
	}

	/**
	*
	* @api {post} /0.3/cloud/createdir create a directory
	* @apiVersion 0.3.0
	* @apiDescription This method is there to create a directory in the cloud
	* @apiGroup Cloud
	* @apiName Create Directory
	*
	* @apiHeader {string} Authorization user's authentication token
	* @apiHeaderExample Request-Example:
	*	{
	*		"Authorization": "6e281d062afee65fb9338d38b25828b3"
	*	}
	*
	* @apiParam {Object[]} data All informations about the core request have to be here
	* @apiParam {Number} data.project_id The project id to execute the command.
	* @apiParam {string} data.path The path of the directory in the cloud where the new directory have to be created (absolute path from the root of the project's cloud)
	* @apiParam {string} data.dir_name The new directory's name.
	* @apiParam {string} [data.passwordSafe] The project's safe password, in order to create the directory into the safe. Use only if directory have to be into the safe.
	* @apiParamExample {json} Request Example:
	* 	{
	*		"data": {
	*			"project_id": 42,
	*			"path": "/Gandalf le gris"
	*			"dir_name" : "Beard"
	*		}
	*	}
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.3.1,
	*			"return_message" : "Cloud - createDirAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.8.9,
	*			"return_message" : "Cloud - createDirAction - Insufficient Right"
	*		}
	*	}
	*/
	/**
	*
	* @api {post} /V0.2/cloud/createdir create a directory
	* @apiVersion 0.2.0
	* @apiDescription This method is there to create a directory in the cloud
	* @apiGroup Cloud
	* @apiName Create Directory
	* @apiParam {Object[]} data All informations about the core request have to be here
	* @apiParam {string} data.token The token of authenticated user.
	* @apiParam {Number} data.project_id The project id to execute the command.
	* @apiParam {string} data.path The path of the directory in the cloud where the new directory have to be created (absolute path from the root of the project's cloud)
	* @apiParam {string} data.dir_name The new directory's name.
	* @apiParam {string} [data.passwordSafe] The project's safe password, in order to create the directory into the safe. Use only if directory have to be into the safe.
	* @apiParamExample {json} Request Example:
	* 	{
	*		"data": {
	*			"token": "48q98d"
	*			"project_id": 42,
	*			"path": "/Gandalf le gris"
	*			"dir_name" : "Beard"
	*		}
	*	}
	*
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"info" : {
	*			"return_code" : 1.3.1,
	*			"return_message" : "Cloud - createDirAction - Complete Success"
	*		}
	*	}
	*
	* @apiError (206) {Object} info Informations about the request
	* @apiError (206) {string} info.return_code Request end state code
	* @apiError (206) {string} info.return_message Request end state message (text formated return_code)
	*
	* @apiErrorExample {json} Error Response:
	*	HTTP/1.1 206 Partial Content
	*	{
	*		"info" : {
	*			"return_code" : 3.8.9,
	*			"return_message" : "Cloud - createDirAction - Insufficient Right"
	*		}
	*	}
	*/
	public function createDirAction(Request $request)
	{
		$json = json_decode($request->getContent(), true);
		if (!isset($json["data"]["password"]) && isset($json["data"]["passwordSafe"]))
			$json["data"]["password"] = $json["data"]["passwordSafe"];
		$token = $request->headers->get('Authorization');
		$userId = $this->getUserId($token);
		$idProject = $json["data"]["project_id"];
		$json["data"]["path"] = str_replace(" ", "|", urldecode($json["data"]["path"]));

		$isSafe = preg_match("/Safe/", $json["data"]["path"]);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("SQLBundle:Project")->findOneById($idProject);
			$passwordEncrypted = $this->grappSha1($json["data"]["password"]);
		}
		else {
			$project = NULL;
			$passwordEncrypted = NULL;
		}
		if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) < 2 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
		{
			$response["info"]["return_code"] = "3.8.9";
			$response["info"]["return_message"] = "Cloud - createDirAction - Insufficient Success";
			return new JsonResponse($response, 206);
		}

		//Now we can create the directory at the proper place
		$path = $json["data"]["path"];
		$dirName = $json["data"]["dir_name"];
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$dirName = str_replace(' ', '|', $dirName);
		$rpath = "/GrappBox|Projects/".(string)($idProject).(string)($path)."/".$dirName;
		//HERE Create the dir in the cloud
		$flysystem->createDir($rpath);

		$this->get('service_stat')->updateCloudStat($idProject, $token, $request);

		$response["info"]["return_code"] = "1.3.1";
		$response["info"]["return_message"] = "Cloud - createDirAction - Complete Success";
		return new JsonResponse($response);
	}

	// WARNING : ONLY FOR OTHER API CONTROLLERS!
	public function createCloudAction(Request $request, $projectId)
	{
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$rpathSafe = "GrappBox|Projects/".(string)($projectId)."/Safe";
		$rpath = "GrappBox|Projects/".(string)($projectId);
		//HERE Create the dir in the cloud
		$flysystem->createDir($rpath);
		$flysystem->createDir($rpathSafe);
		return new JsonResponse(Array("infos" => "OK"));
	}
}
