# Contributte / Fileupload

jQuery-FileUpload is component which extends UploadControl in Nette form using [blueimp/jQuery-File-Upload](https://github.com/blueimp/jQuery-File-Upload).

## Content

## Setup

```
extensions:
    fileUpload: Zet\FileUpload\FileUploadExtension
```

### Configuration of DI extension options

```
fileUpload:
	maxFiles: 10
	maxFileSize: 2M
	fileFilter: Zet\FileUpload\Filter\ImageFilter
	uploadModel: App\Model\MyUploadModel
	uiMode: # full nebo minimal
```

## CSS and JS

### Copy files

This component needs for its precise functionality some third party scripts and styles. Copy all required assets from aassets folder to www root of project.

### Loading CSS and JS in layout.latte

```
{\Zet\FileUpload\FileUploadControl::getHead($basePath)}
{\Zet\FileUpload\FileUploadControl::getScripts($basePath)}
``` 

## Upload model

Component can use custom processing file upload through `UploadModel` which does not save files on server directly.

### Interface

Custom `UploadModel` must implement interface **\Zet\FileUpload\Model\IUploadModel**.

```php
namespace Zet\FileUpload\Model;

/**
 * Interface IUploadController
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Model
 */
interface IUploadModel {

	/**
	 * Save uploaded file 
	 * @param \Nette\Http\FileUpload $file
	 * @param array $params array of custom parameters
	 * @return mixed orn returned value.
	 */
	public function save(\Nette\Http\FileUpload $file, array $params = []);

	/**
	 * Processing renaming file
	 * @param $upload value of returned by method save
	 * @param $newName Nové jméno souboru.
	 * @return mixed Vlastní návratová hodnota.
	 */
	public function rename($upload, $newName);

	/**
	 * Processing request and deleting file 
	 * @param $uploaded value of returned method save
 	 */
	public function remove($uploaded);

}
```

Interface has three primary method - save, rename and remove.

The method `save` is called when uploading the file to the server, this method is expected to return any return value, which will then be returned when retrieving the field value when processing the form. If custom parameters for uploaded files have been set with the **FileUploadControl::setParams()** method, they are passed as fields to the **$params** parameter.

The method `remove` is to remove a file after upload, its parameter is the value that was returned by the save function.

The method `rename` to rename the uploaded file. Its first parameter is the value returned by the save function and the new file name. Rename should return the same value as save.

### Registering custom moder

```
fileUpload:
    uploadModel: App\Model\MyUploadModel
```

## Filtering of files

Uploaded files can be filtered using their mimetype or suffix, so it is possible to limit the upload of files to images only. There are filter classes for this purpose.

All filters are instances of the abstract class **\Zet\FileUpload\Filter\BaseFilter**, which implements the basic methodology for determining the correctness of a file using a mimetype, or according to the file extension. This basic filter implements the **\Zet\FileUpload\Filter\IMimeTypeFilter** interface.

## Basic filters

```php
FileUploadControl::FILTER_IMAGES; // Allows uploading of images png, jpeg, jpg, gif only.
FileUploadControl::FILTER_DOCUMENTS; // Allows uploading of documents txt, doc, docx, xls, xlsx, ppt, pptx, pdf  only.
FileUploadControl::FILTER_ARCHIVE; // Allows uploading of files zip, tar, rar, 7z only.
FileUploadControl::FILTER_AUDIO; // Allows uploading of files mp3, ogg, aiff only.

FileUploadControl::setFileFilter("Constant or custom class"); // Sets the class to determine if the file can be uploaded
```

The setFileFilter method accepts as a parameter a string in which the class name is written, eg **Zet\FileUpload\Filter\ImageFilter**.

## Custom filter

### Using BaseFilter

To use the basic methodology for determining the correctness of a file type, you can create your own class, which will be a child of **\Zet\FileUpload\Filter\BaseFilter**.

The class created in this way must have its own implementation of the `getMimeTypes()` method, which returns an array of file mimetypes and their extensions, e.g.:

```php
/**
 * Returns a list of allowed file types with their typical extension.
 * @example array("text/plain" => "txt")
 * @return string[]
 */
protected function getMimeTypes() {
	return array(
		"image/png" => "png",
		"image/pjpeg" => "jpeg",
		"image/jpeg" => "jpg",
		"image/gif" => "gif",
	);
}
```

### Own filtering methodology

If you want to create your own way of determining the file type, you can create your own class, but it must implement the **\Zet\FileUpload\Filter\IMimeTypeFilter** interface.

```php
/**
 * Interface IMimeTypeFilters
 * Interface for checking the mimetype of file
 * @author Zechy <email@zechy.cz>
 * @package Zet\FileUpload\Filter
 */
interface IMimeTypeFilter {

	/**
	 * Validates the mimetype of the uploaded file.
	 * @param \Nette\Http\FileUpload $file Uploaded file for validating.
	 * @return bool
	 */
	public function checkType(\Nette\Http\FileUpload $file);

	/**
	 * Returns a list of allowed file types.
	 * @return string
	 */
	public function getAllowedTypes();
}
```

## Usage

Registered component can be used by calling method *addFileUpload()*.

```php
protected function createComponentMyForm() {
	$form = new \Nette\Application\UI\Form;
	$form->addFileUpload("uploader");

	return $form;
}
```

The *addFileUpload()* method has one required parameter, the name. However, you can also use two other optional parameters - *maximum number of files* and *maximum file size*.

Maximum file sizes can be passed in **3M** or **500K** format. If the maximum size is not set, the maximum upload file size from **php.ini** will be loaded.

### UI Mode

As of version 1.2.0, the appearance of the uploader can be changed using the FileUploadControl::setUIMode() function, the function accepts the UI_FULL and UI_MINIMAL constants as a parameter.

```php
FileUploadControl::setUIMode(FileUploadControl::UI_FULL);
FileUploadControl::setUIMode(FileUploadControl::UI_MINIMAL);
```

### Custom parameters
As of version 1.2.1, an array of custom parameters can be passed to the FileUploader, using the method **FileUplaodControl::setParams()**.

```php
FileUploadControl::setParams([
  "productId" => 23,
  "userId" => 10
]);
```

The parameters in this way are then automatically passed to the UploadModel via the parameter **$params**.

### Processing

Uploaded files can be obtained by processing the form from the field **$values**. The element returns an array of exactly the same values as the **save()** method of the UploadModel.

### Setters

```php
FileUploadControl::setMaxFiles(25); // Sets maxium of files.
FileUploadControl::setMaxFileSize("2M"); // Setsa of maximum of file size.
FileUploadControl::setUploadModel('\Model\File\UploadModel'); // Sets custom upload model.
FileUploadControl::setFileFilter('\Zet\FileUpload\Filter\ImageFilter'); // Sets restrictions on uploading files to specified types. You can string a custom class or use the FileUploadControl constants.
FileUploadControl::setUiTemplate(FileUploadControl::UI_FULL, __DIR__ . "/path/to/my/template.latte");
FileUploadControl::setParams(["productId" => 10]); // Sets custom values for the uploaded file
```

## Catching exceptions

### Server-Side Controller

On the controller side, any exceptions can be thrown from the UploadModel. Exceptions are caught during processing and a JSON response with error information is sent to the uploader.

### Client-Side

If an error is returned from the server while uploading a file, its name will be replaced in the file list by an error message. If the application is not in run mode, the information with the error message from the exception is written as an error to the console.

