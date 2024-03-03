<?php

namespace Zet\FileUpload\Controller;

interface IUploadController
{

	public function handleUpload();

	public function handleRemove();

	public function handleRename();

}
