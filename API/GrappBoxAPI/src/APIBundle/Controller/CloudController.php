<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class CloudController extends Controller
{
	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a file",
	 * views = { "cloud" },
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
	public function getFileAction(Request $request)
	{
		return new Response('get File Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="push a file",
	 * views = { "cloud" },
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
	public function pushFileAction(Request $request)
	{
		return new Response('push File Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="set the password of a file",
	 * views = { "cloud" },
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
	public function setFilePassAction(Request $request)
	{
		return new Response('Set File Pass Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="set the password of a directory",
	 * views = { "cloud" },
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
	public function setDirPassAction(Request $request)
	{
		return new Response('set Dir Pass Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a list of files",
	 * views = { "cloud" },
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
	public function getFileListAction(Request $request)
	{
		return new Response('get File List Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a file",
	 * views = { "cloud" },
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
	public function delFileAction(Request $request)
	{
		return new Response('del File Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get a list of directories",
	 * views = { "cloud" },
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
	public function getDirListAction(Request $request)
	{
		return new Response('get Dir List Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="delete a directory",
	 * views = { "cloud" },
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
	public function delDirAction(Request $request)
	{
		return new Response('del Dir Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the metadatas of a file",
	 * views = { "cloud" },
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
	public function getFileMetadataAction(Request $request)
	{
		return new Response('get File Metadata Success');
	}

	/**
	 *
	 * @ApiDoc(
	 * resource=true,
	 * description="get the metadata of a directory",
	 * views = { "cloud" },
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
	public function getDirMetadataAction(Request $request)
	{
		return new Response('get Dir Metadata Success');
	}
}