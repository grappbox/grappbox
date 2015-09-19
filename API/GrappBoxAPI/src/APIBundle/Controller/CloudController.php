<?php

namespace APIBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CloudController extends Controller
{
	public function getFileAction(Request $request)
	{
		return new Response('get File Success');
	}

	public function pushFileAction(Request $request)
	{
		return new Response('push File Success');
	}

	public function setFilePassAction(Request $request)
	{
		return new Response('Set File Pass Success');
	}

	public function setDirPassAction(Request $request)
	{
		return new Response('set Dir Pass Success');
	}

	public function getFileListAction(Request $request)
	{
		return new Response('get File List Success');
	}

	public function delFileAction(Request $request)
	{
		return new Response('del File Success');
	}

	public function getDirListAction(Request $request)
	{
		return new Response('get Dir List Success');
	}

	public function delDirAction(Request $request)
	{
		return new Response('del Dir Success');
	}

	public function getFileMetadataAction(Request $request)
	{
		return new Response('get File Metadata Success');
	}

	public function getDirMetadataAction(Request $request)
	{
		return new Response('get Dir Metadata Success');
	}
}