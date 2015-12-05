<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use \DateTime;

use APIBundle\Entity\CloudTransfer;
use APIBundle\Entity\CloudSecuredFileMetadata;

use Sabre\DAV\Client;
use League\Flysystem\WebDAV\WebDAVAdapter;
use League\Flysystem\Filesystem;

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
	public    $auth_name      = 'GrappBot';
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
	//Return -1 if the use don't have authorization for Cloud
	//Else return the user_id

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
		$userRepository = $this->getDoctrine()->getRepository("APIBundle:User");
		$user = $userRepository->findOneByToken($token);
		return (is_null($user) ? -1 : $user->getId());
	}

	private function checkUserCloudAuthorization($userId, $idProject)
	{
		$db = $this->getDoctrine();
		$role = $db->getRepository("APIBundle:ProjectUserRole")->findOneBy(array("projectId" => $idProject, "userId" => $userId));
		if (is_null($role))
			return (-1);
		$roleTable = $db->getRepository("APIBundle:Role")->findOneById($role->getId());
		return (is_null($roleTable) ? -1 : $roleTable->getCloud());
	}

	public function streamAction(Request $request){
		$method = $request->getMethod();
		$dbManager = $this->getDoctrine()->getManager();
		$json = json_decode($request->getContent(), true);
		$token = $json["session_infos"]["token"];
		$userId = $this->getUserId($token);
		$receivedData = $json["stream_infos"];
		$isSafe = ($method == "DELETE" ? false : preg_match("/Safe/", $receivedData["path"]));
		if ($isSafe){
			$project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
			$passwordEncrypted = (isset($json["session_infos"]["safe_password"]) ? $json["session_infos"]["safe_password"] : NULL); // TODO : SHA-1 512 Hashing when algo created!
		}
		else{
			$project = null;
			$passwordEncrypted = null;
		}
		if ($method == "POST")
		{
			$receivedData["filename"] = str_replace(" ", "|", $receivedData["filename"]);
			$receivedData["path"] = str_replace(" ", "|", $receivedData["path"]);
			$idProject = $receivedData["project_id"];
		}
		if (($method == "POST" && $this->checkUserCloudAuthorization($userId, $idProject) <= 0) || ($isSafe && $passwordEncrypted != $project->getSafePassword()))
			throw $this->createAccessDeniedException();
		return ($method == "POST"
							? $this->openStream($receivedData, $userId, $idProject)
							: $this->closeStream($receivedData, $token));
	}

	/**
	*
	* @api {post} /V0.6/cloud/stream Open a new stream in order to upload file
	* @apiDescription This method is here to create an upload process between API and Cloud.
	* @apiGroup Cloud
	* @apiName Stream opening
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {string} [session_infos.safe_password] The password of the project safe. Use it only if the future file will be in the safe.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {string} stream_infos.filename The filename of the future file with extension
	* @apiParam {string} stream_infos.path The path where the future file will be uploaded
	* @apiParam {string} [stream_infos.password] The password to protect the file. Use it only if password protected required.
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d",
	*			"safe_password" : "satan"
	*		},
	*		"stream_infos": {
	*			"project_id": 42,
	*			"path" : "/LabEIP/Golum",
	*			"password" : "My Precious!"
	*			"filename" : "The ring.worldDomination"
	*		}
	* 	}
	* @apiSuccess (200) {string} stream_id The id of the stream newly created.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"stream_id" : 1
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {post} /V0.7/cloud/stream Open a new stream in order to upload file
	* @apiDescription This method is here to create an upload process between API and Cloud.
	* @apiGroup Cloud
	* @apiName Stream opening
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {string} [session_infos.safe_password] The password of the project safe. Use it only if the future file will be in the safe.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {string} stream_infos.filename The filename of the future file with extension
	* @apiParam {string} stream_infos.path The path where the future file will be uploaded
	* @apiParam {string} [stream_infos.password] The password to protect the file. Use it only if password protected required.
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d",
	*			"safe_password" : "satan"
	*		},
	*		"stream_infos": {
	*			"project_id": 42,
	*			"path" : "/LabEIP/Golum",
	*			"password" : "My Precious!"
	*			"filename" : "The ring.worldDomination"
	*		}
	* 	}
	* @apiSuccess (200) {string} stream_id The id of the stream newly created.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"stream_id" : 1
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {post} /V0.8/cloud/stream Open a new stream in order to upload file
	* @apiDescription This method is here to create an upload process between API and Cloud.
	* @apiGroup Cloud
	* @apiName Stream opening
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {string} [session_infos.safe_password] The password of the project safe. Use it only if the future file will be in the safe.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {string} stream_infos.filename The filename of the future file with extension
	* @apiParam {string} stream_infos.path The path where the future file will be uploaded
	* @apiParam {string} [stream_infos.password] The password to protect the file. Use it only if password protected required.
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d",
	*			"safe_password" : "satan"
	*		},
	*		"stream_infos": {
	*			"project_id": 42,
	*			"path" : "/LabEIP/Golum",
	*			"password" : "My Precious!"
	*			"filename" : "The ring.worldDomination"
	*		}
	* 	}
	* @apiSuccess (200) {string} stream_id The id of the stream newly created.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"stream_id" : 1
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.8.0
	*/
	private function openStream($receivedData, $userId, $idProject){
		if ($receivedData["path"][0] != "/")
			return header("HTTP1.0 400 Bad Request", True, 400);
		$em = $this->getDoctrine()->getManager();
		$stream = new CloudTransfer();
		$stream->setCreatorId($userId)
					 ->setFilename($receivedData["filename"])
					 ->setPath('/GrappBox|Projects/'.(string)$idProject.$receivedData["path"])
					 ->setPassword($receivedData["password"])
					 ->setCreationDate(new DateTime("now"))
					 ->setDeletionDate(null);
		$em->persist($stream);
		$em->flush();
		return new JsonResponse(array("stream_id" => $stream->getId()));
	}

	/**
	*
	* @api {delete} /V0.6/cloud/stream Close a stream in order to complete an upload
	* @apiDescription This method is here to finalize an upload and make the file downloadable.
	* @apiGroup Cloud
	* @apiName Stream closing
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {string} [session_infos.safe_password] The password of the project safe. Use it only if the future file will be in the safe.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {Number} stream_infos.stream_id The id of the stream to close.
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d",
	*			"safe_password" : "satan"
	*		},
	*		"stream_infos": {
	*			"project_id": 42,
	*			"stream_id" : 1
	*		}
	*	}
	* @apiSuccess (200) {string} infos. This will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {delete} /V0.7/cloud/stream Close a stream in order to complete an upload
	* @apiDescription This method is here to finalize an upload and make the file downloadable.
	* @apiGroup Cloud
	* @apiName Stream closing
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {string} [session_infos.safe_password] The password of the project safe. Use it only if the future file will be in the safe.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {Number} stream_infos.stream_id The id of the stream to close.
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d",
	*			"safe_password" : "satan"
	*		},
	*		"stream_infos": {
	*			"project_id": 42,
	*			"stream_id" : 1
	*		}
	*	}
	* @apiSuccess (200) {string} infos. This will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {delete} /V0.8/cloud/stream Close a stream in order to complete an upload
	* @apiDescription This method is here to finalize an upload and make the file downloadable.
	* @apiGroup Cloud
	* @apiName Stream closing
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {string} [session_infos.safe_password] The password of the project safe. Use it only if the future file will be in the safe.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {Number} stream_infos.stream_id The id of the stream to close.
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d",
	*			"safe_password" : "satan"
	*		},
	*		"stream_infos": {
	*			"project_id": 42,
	*			"stream_id" : 1
	*		}
	*	}
	* @apiSuccess (200) {string} infos. This will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.8.0
	*/
	private function closeStream($receivedData, $token){
		$cloudTransferRepository = $this->getDoctrine()->getRepository("APIBundle:CloudTransfer");
		$em = $this->getDoctrine()->getManager();
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
		$user_id = $this->getUserId($token);
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
			throw $this->createAccessDeniedException();

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
		var_dump($shareRequest->createCurl("http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/api/v1/shares"));
		$em->persist($stream);
		$em->flush();
		return new JsonResponse(Array("infos" => "OK"));
	}

	/**
	*
	* @api {put} /V0.6/cloud/sendfile send a file chunk.
	* @apiDescription This method is there to upload a file in the given project cloud. You have to open a stream before.
	* @apiGroup Cloud
	* @apiName Send file
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {Number} stream_infos.stream_id The stream id which contains the uploaded file metadata (use POST stream action route to open one)
	* @apiParam {Number} stream_infos.chunk_numbers The numbers of chunk you will upload for this file.
	* @apiParam {Number} stream_infos.current_chunk The index of current chunk. This start to 0 and end to (chunk_numbers - 1)
	* @apiParam {string} stream_infos.file_chunk The file chunk encoded in base64
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"stream_infos": {
	*			"stream_id" : 21,
	*			"project_id": 42,
	*			"chunk_numbers" : 2,
	*			"current_chunk" : 1,
	*			"file_chunk" : "Here put your chunk encoded in base 64"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {put} /V0.7/cloud/sendfile send a file chunk.
	* @apiDescription This method is there to upload a file in the given project cloud. You have to open a stream before.
	* @apiGroup Cloud
	* @apiName Send file
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {Number} stream_infos.stream_id The stream id which contains the uploaded file metadata (use POST stream action route to open one)
	* @apiParam {Number} stream_infos.chunk_numbers The numbers of chunk you will upload for this file.
	* @apiParam {Number} stream_infos.current_chunk The index of current chunk. This start to 0 and end to (chunk_numbers - 1)
	* @apiParam {string} stream_infos.file_chunk The file chunk encoded in base64
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"stream_infos": {
	*			"stream_id" : 21,
	*			"project_id": 42,
	*			"chunk_numbers" : 2,
	*			"current_chunk" : 1,
	*			"file_chunk" : "Here put your chunk encoded in base 64"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {put} /V0.8/cloud/sendfile send a file chunk.
	* @apiDescription This method is there to upload a file in the given project cloud. You have to open a stream before.
	* @apiGroup Cloud
	* @apiName Send file
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} stream_infos All informations about the core request have to be here
	* @apiParam {Number} stream_infos.project_id The project id to execute the command.
	* @apiParam {Number} stream_infos.stream_id The stream id which contains the uploaded file metadata (use POST stream action route to open one)
	* @apiParam {Number} stream_infos.chunk_numbers The numbers of chunk you will upload for this file.
	* @apiParam {Number} stream_infos.current_chunk The index of current chunk. This start to 0 and end to (chunk_numbers - 1)
	* @apiParam {string} stream_infos.file_chunk The file chunk encoded in base64
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"stream_infos": {
	*			"stream_id" : 21,
	*			"project_id": 42,
	*			"chunk_numbers" : 2,
	*			"current_chunk" : 1,
	*			"file_chunk" : "Here put your chunk encoded in base 64"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.8.0
	*/
	public function sendFileAction(Request $request){
		$cloudTransferRepository = $this->getDoctrine()->getRepository("APIBundle:CloudTransfer");
		$json = json_decode($request->getContent(), true);
		$token = $json["session_infos"]["token"];
		$receivedData = $json["stream_infos"];
		$user_id = $this->getUserId($token);
		$stream = $cloudTransferRepository->find($receivedData["stream_id"]);
		if ($user_id < 0 || $user_id != $stream->getCreatorId())
			throw $this->createAccessDeniedException();

		//Here the user have the right authorization, so upload the file's chunk

		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$flysystem->put('/GrappBox|Projects/'.(string)$receivedData["project_id"]."/".$stream->getFilename().'-chunking-'.(string)$receivedData["stream_id"].'-'.$receivedData["chunk_numbers"].'-'.$receivedData["current_chunk"], (string)base64_decode($receivedData["file_chunk"]));
		return new JsonResponse(Array("infos" => "OK"));
	}

	/**
	*
	* @api {get} /V0.6/cloud/getlist/:token/:idProject/:path/[:passwordSafe] Cloud LS
	* @apiDescription Get the list of a given directory.
	* @apiGroup Cloud
	* @apiName List directory
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} path The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the user want the safe content
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/cloud/getlist/minus5percent/1/,Sauron/satan
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {get} /V0.7/cloud/getlist/:token/:idProject/:path/[:passwordSafe] Cloud LS
	* @apiDescription Get the list of a given directory.
	* @apiGroup Cloud
	* @apiName List directory
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} path The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the user want the safe content
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/cloud/getlist/minus5percent/1/,Sauron/satan
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {get} /V0.8/cloud/getlist/:token/:idProject/:path/[:passwordSafe] Cloud LS
	* @apiDescription Get the list of a given directory.
	* @apiGroup Cloud
	* @apiName List directory
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} path The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the user want the safe content
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/cloud/getlist/minus5percent/1/,Sauron/satan
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.8.0
	*/
	public function getListAction($token, $idProject, $path, $password, Request $request)
	{
		$userId = $this->getUserId($token);
		$isSafe = preg_match("/Safe/", $path);
		if ($isSafe){
			$project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
			$passwordEncrypted = $password; // TODO : SHA-1 512 Hashing when algo created!
		}
		else{
			$project = null;
			$passwordEncrypted = null;
		}
		if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
			throw $this->createAccessDeniedException();

		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$prepath = str_replace(" ", "|", str_replace(",", "/", $path));
		$rpath = "/GrappBox|Projects/".(string)($idProject).$prepath;
		$securedFileRepository = $this->getDoctrine()->getRepository("APIBundle:CloudSecuredFileMetadata");

		$content = str_replace("|", " ", $adapter->listContents($rpath));
		foreach ($content as $i => $row)
		{
			$content[$i]["path"] = str_replace("remote.php/webdav/GrappBox%7cProjects/".(string)$idProject.$prepath.($prepath == "/" ? "": "/"), "", $content[$i]["path"]);
			$filename = split('/', $content[$i]["path"]);
			$filename = $filename[count($filename) - 1];
			$content[$i]["filename"] = $filename;
			unset($content[$i]["path"]);
			$content[$i]["isSecured"] = !($securedFileRepository->findOneBy(array("filename" => $filename, "cloudPath" => $rpath)) == null);
		}
		return new JsonResponse(array("data" => $content));
	}

	/**
	*
	* @api {get} /V0.6/cloud/getfile/:cloudPath/:token/:idProject/[:password]/[:passwordSafe] Download a file
	* @apiDescription This method is there to start a download.
	* @apiGroup Cloud
	* @apiName Download file
	* @apiParam {string} CloudPath The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} [password] The password hashed in a clear way. Use only if file is password protected.
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the file is in the safe
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/cloud/getfile/,Sauron/minus5percent/1/mustache/satan
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {get} /V0.7/cloud/getfile/:cloudPath/:token/:idProject/[:password]/[:passwordSafe] Download a file
	* @apiDescription This method is there to start a download.
	* @apiGroup Cloud
	* @apiName Download file
	* @apiParam {string} CloudPath The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} [password] The password hashed in a clear way. Use only if file is password protected.
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the file is in the safe
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/cloud/getfile/,Sauron/minus5percent/1/mustache/satan
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {get} /V0.8/cloud/getfile/:cloudPath/:token/:idProject/[:password]/[:passwordSafe] Download a file
	* @apiDescription This method is there to start a download.
	* @apiGroup Cloud
	* @apiName Download file
	* @apiParam {string} CloudPath The path to the file with coma instead of slash. This have to start with a coma
	* @apiParam {string} token The token of authenticated user.
	* @apiParam {Number} idProject The project id to execute the command.
	* @apiParam {string} [password] The password hashed in a clear way. Use only if file is password protected.
	* @apiParam {string} [passwordSafe] The project safe password. Use it only if the file is in the safe
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/cloud/getfile/,Sauron/minus5percent/1/mustache/satan
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.8.0
	*/
	public function getFileAction($cloudPath, $token, $idProject, $password = null, $passwordSafe = null, Request $request){
		$userId = $this->getUserId($token);
		$passwordEncrypted = $password; //TODO : sha-1 512 hashing Here in password
		$cloudPathArray = explode(',', $cloudPath);
		$filename = $cloudPathArray[count($cloudPathArray) - 1];
		unset($cloudPathArray[count($cloudPathArray) - 1]);
		$cloudBasePath = implode('/', $cloudPathArray);
		if ($cloudBasePath == "")
			$cloudBasePath = "/";
		$filePassword = $this->getDoctrine()->getRepository("APIBundle:CloudSecuredFileMetadata")->findOneBy(array("cloudPath" => "/GrappBox|Projects/".(string)$idProject.$cloudBasePath, "filename" => $filename));

		$isSafe = preg_match("/Safe/", $cloudPath);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
			$passwordEncrypted = $password; // TODO : SHA-1 Hashing
		}
		else {
			$project == NULL;
			$passwordEncrypted = NULL;
		}
		if ($userId < 0 || (!is_null($filePassword) && $filePassword->getPassword() != $passwordEncrypted) || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
			throw $this->createAccessDeniedException();

		//Here we have authorization to get the encrypted file, Client have to decrypt it after reception, if it's a secured file
		$cloudPath = str_replace(',', '/', $cloudPath);
		$path = "http://cloud.grappbox.com/ocs/v1.php/apps/files_sharing/api/v1/shares?path=".urlencode("/GrappBox|Projects/".(string)($idProject).$cloudPath);
		$searchRequest = new CurlRequest();
		$searchResult = simplexml_load_string($searchRequest->createCurl($path));
		if ($searchResult->meta->statuscode != 100 ||
			$searchResult->data->element->share_type != "3")
			throw $this->createNotFoundException('file not found');
		return $this->redirect("http://cloud.grappbox.com/index.php/s/".(string)($searchResult->data->element->token)."/download");
	}

	/**
	*
	* @api {post} /v0.6/cloud/setsafepass Set the safe password
	* @apiDescription This method is there to change the safe password for a given project.
	* @apiGroup Cloud
	* @apiName Set Safe Password
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} safe_infos All informations about the core request have to be here
	* @apiParam {Number} safe_infos.project_id The project id to execute the command.
	* @apiParam {string} safe_infos.password The password hashed in SHA-1 512
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"safe_infos": {
	*			"project_id": 42,
	*			"password": "6q8d4zq68d"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {post} /v0.7/cloud/setsafepass Set the safe password
	* @apiDescription This method is there to change the safe password for a given project.
	* @apiGroup Cloud
	* @apiName Set Safe Password
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} safe_infos All informations about the core request have to be here
	* @apiParam {Number} safe_infos.project_id The project id to execute the command.
	* @apiParam {string} safe_infos.password The password hashed in SHA-1 512
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"safe_infos": {
	*			"project_id": 42,
	*			"password": "6q8d4zq68d"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {post} /v0.8/cloud/setsafepass Set the safe password
	* @apiDescription This method is there to change the safe password for a given project.
	* @apiGroup Cloud
	* @apiName Set Safe Password
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} safe_infos All informations about the core request have to be here
	* @apiParam {Number} safe_infos.project_id The project id to execute the command.
	* @apiParam {string} safe_infos.password The password hashed in SHA-1 512
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"safe_infos": {
	*			"project_id": 42,
	*			"password": "6q8d4zq68d"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role problem or a token problem.
	* @apiVersion 0.8.0
	*/
	public function setSafePassAction(Request $request)
	{
		$dbManager = $this->getDoctrine()->getManager();
		$json = json_decode($request->getContent(), true);
		$token = $json["session_infos"]["token"];
		$userId = $this->getUserId($token);
		$idProject = (int)$json["safe_infos"]["project_id"];
		$project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
		if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || is_null($project))
			throw $this->createAccessDeniedException();

		$project->setSafePassword($json["safe_infos"]["password"]);
		$dbManager->persist($project);
		$dbManager->flush();
		return new JsonResponse(Array("infos" => "OK"));
	}

	/**
	*
	* @api {get} /V0.6/cloud/createcloud/:projectId Create the cloud for a given project
	* @apiDescription This method have to be used only for test or between symfony controllers. Clients don't have to call it.
	* @apiGroup Cloud
	* @apiName Create cloud
	* @apiParam {Number} projectId The project id in which the cloud have to be created.
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/Cloud/createCloud/1
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. This method can't fail.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {get} /V0.7/cloud/createcloud/:projectId Create the cloud for a given project
	* @apiDescription This method have to be used only for test or between symfony controllers. Clients don't have to call it.
	* @apiGroup Cloud
	* @apiName Create cloud
	* @apiParam {Number} projectId The project id in which the cloud have to be created.
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/Cloud/createCloud/1
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. This method can't fail.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {get} /V0.8/cloud/createcloud/:projectId Create the cloud for a given project
	* @apiDescription This method have to be used only for test or between symfony controllers. Clients don't have to call it.
	* @apiGroup Cloud
	* @apiName Create cloud
	* @apiParam {Number} projectId The project id in which the cloud have to be created.
	* @apiParamExample {curl} Request Example:
	*	curl http://api.grappbox.com/V0.6/Cloud/createCloud/1
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. This method can't fail.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiVersion 0.8.0
	*/
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

	/**
	*
	* @api {delete} /V0.6/cloud/del Delete a file or a directory
	* @apiDescription This method is there to delete something in the cloud
	* @apiGroup Cloud
	* @apiName Delete
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} deletion_infos All informations about the core request have to be here
	* @apiParam {Number} deletion_infos.project_id The project id to execute the command.
	* @apiParam {string} deletion_infos.path The path of the file/directory in the cloud (absolute path from the root of the project's cloud)
	* @apiParam {string} [deletion_infos.password] The project's safe password, in order to delete a file or a directory into the safe. Use only if file or directory into the safe. You can't delete the safe itself!
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"deletion_infos": {
	*			"project_id": 42,
	*			"path": "/Gandalf le gris"
	*			"password" : "Ajax"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role, password or token problem.
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {delete} /V0.7/cloud/del Delete a file or a directory
	* @apiDescription This method is there to delete something in the cloud
	* @apiGroup Cloud
	* @apiName Delete
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} deletion_infos All informations about the core request have to be here
	* @apiParam {Number} deletion_infos.project_id The project id to execute the command.
	* @apiParam {string} deletion_infos.path The path of the file/directory in the cloud (absolute path from the root of the project's cloud)
	* @apiParam {string} [deletion_infos.password] The project's safe password, in order to delete a file or a directory into the safe. Use only if file or directory into the safe. You can't delete the safe itself!
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"deletion_infos": {
	*			"project_id": 42,
	*			"path": "/Gandalf le gris"
	*			"password" : "Ajax"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role, password or token problem.
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {delete} /V0.8/cloud/del Delete a file or a directory
	* @apiDescription This method is there to delete something in the cloud
	* @apiGroup Cloud
	* @apiName Delete
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} deletion_infos All informations about the core request have to be here
	* @apiParam {Number} deletion_infos.project_id The project id to execute the command.
	* @apiParam {string} deletion_infos.path The path of the file/directory in the cloud (absolute path from the root of the project's cloud)
	* @apiParam {string} [deletion_infos.password] The project's safe password, in order to delete a file or a directory into the safe. Use only if file or directory into the safe. You can't delete the safe itself!
	* @apiParamExample {json} Request Example:
	*	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"deletion_infos": {
	*			"project_id": 42,
	*			"path": "/Gandalf le gris"
	*			"password" : "Ajax"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role, password or token problem.
	* @apiVersion 0.8.0
	*/
	public function delAction(Request $request)
	{
		$json = json_decode($request->getContent(), true);
		$token = $json["session_infos"]["token"];
		$userId = $this->getUserId($token);
		$idProject = $json["deletion_infos"]["project_id"];
		$isSafe = preg_match("/Safe/", $json["deletion_infos"]["path"]);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
			$passwordEncrypted = $json["deletion_infos"]["password"]; // TODO : SHA-1 Hashing
		}
		else {
			$project == NULL;
			$passwordEncrypted = NULL;
		}
		if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || preg_match("/Safe$/", $json["deletion_infos"]["path"]) || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
			throw $this->createAccessDeniedException();

		//Now we can delete the file or the directory
		$path = "/GrappBox|Projects/".(string)($idProject).str_replace(' ', '|', $json["deletion_infos"]["path"]);
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$flysystem->delete($path);
		return new JsonResponse(Array("infos" => "OK"));
	}

	/**
	*
	* @api {post} /V0.6/cloud/createdir create a directory
	* @apiDescription This method is there to create a directory in the cloud
	* @apiGroup Cloud
	* @apiName Create Directory
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} creation_infos All informations about the core request have to be here
	* @apiParam {Number} creation_infos.project_id The project id to execute the command.
	* @apiParam {string} creation_infos.path The path of the directory in the cloud where the new directory have to be created (absolute path from the root of the project's cloud)
	* @apiParam {string} creation_infos.dir_name The new directory's name.
	* @apiParam {string} [creation_infos.password] The project's safe password, in order to create the directory into the safe. Use only if directory have to be into the safe.
	* @apiParamExample {json} Request Example:
	* 	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"creation_infos": {
	*			"project_id": 42,
	*			"path": "/Gandalf le gris"
	*			"dir_name" : "Beard"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role, password or token problem.
	* @apiVersion 0.6.0
	*/

	/**
	*
	* @api {post} /V0.7/cloud/createdir create a directory
	* @apiDescription This method is there to create a directory in the cloud
	* @apiGroup Cloud
	* @apiName Create Directory
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} creation_infos All informations about the core request have to be here
	* @apiParam {Number} creation_infos.project_id The project id to execute the command.
	* @apiParam {string} creation_infos.path The path of the directory in the cloud where the new directory have to be created (absolute path from the root of the project's cloud)
	* @apiParam {string} creation_infos.dir_name The new directory's name.
	* @apiParam {string} [creation_infos.password] The project's safe password, in order to create the directory into the safe. Use only if directory have to be into the safe.
	* @apiParamExample {json} Request Example:
	* 	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"creation_infos": {
	*			"project_id": 42,
	*			"path": "/Gandalf le gris"
	*			"dir_name" : "Beard"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role, password or token problem.
	* @apiVersion 0.7.0
	*/

	/**
	*
	* @api {post} /V0.8/cloud/createdir create a directory
	* @apiDescription This method is there to create a directory in the cloud
	* @apiGroup Cloud
	* @apiName Create Directory
	* @apiParam {Object[]} session_infos All informations about the session have to be here
	* @apiParam {string} session_infos.token The token of authenticated user.
	* @apiParam {Object[]} creation_infos All informations about the core request have to be here
	* @apiParam {Number} creation_infos.project_id The project id to execute the command.
	* @apiParam {string} creation_infos.path The path of the directory in the cloud where the new directory have to be created (absolute path from the root of the project's cloud)
	* @apiParam {string} creation_infos.dir_name The new directory's name.
	* @apiParam {string} [creation_infos.password] The project's safe password, in order to create the directory into the safe. Use only if directory have to be into the safe.
	* @apiParamExample {json} Request Example:
	* 	{
	*		"session_infos": {
	*			"token": "48q98d"
	*		},
	*		"creation_infos": {
	*			"project_id": 42,
	*			"path": "/Gandalf le gris"
	*			"dir_name" : "Beard"
	*		}
	*	}
	* @apiSuccess (200) {string} infos The state of the request, will always be OK. Check the HTTP status code instead.
	* @apiSuccessExample {json} Success Response:
	*	HTTP/1.1 200 OK
	*	{
	*		"infos" : "OK"
	*	}
	* @apiError (403) AccessDenied You don't have the permission to access the request. That can be a role, password or token problem.
	* @apiVersion 0.8.0
	*/
	public function createDirAction(Request $request)
	{
		$json = json_decode($request->getContent(), true);
		$token = $json["session_infos"]["token"];
		$userId = $this->getUserId($token);
		$idProject = $json["creation_infos"]["project_id"];

		$isSafe = preg_match("/Safe/", $json["creation_infos"]["path"]);
		if ($isSafe)
		{
			$project = $this->getDoctrine()->getRepository("APIBundle:Project")->findOneById($idProject);
			$passwordEncrypted = $json["creation_infos"]["password"]; // TODO : SHA-1 Hashing
		}
		else {
			$project == NULL;
			$passwordEncrypted = NULL;
		}
		if ($userId < 0 || $this->checkUserCloudAuthorization($userId, $idProject) <= 0 || ($isSafe && (is_null($project) || is_null($passwordEncrypted) || $passwordEncrypted != $project->getSafePassword())))
			return  $this->createAccessDeniedException();

		//Now we can create the directory at the proper place
		$path = $json["creation_infos"]["path"];
		$dirName = $json["creation_infos"]["dir_name"];
		$client = new Client(self::$settingsDAV);
		$adapter = new WebDAVAdapter($client);
		$flysystem = new Filesystem($adapter);
		$dirName = str_replace(' ', '|', $dirName);
		$rpath = "/GrappBox|Projects/".(string)($idProject).(string)($path)."/".$dirName;
		//HERE Create the dir in the cloud
		$flysystem->createDir($rpath);
		return new JsonResponse(Array("infos" => "OK"));
	}
}