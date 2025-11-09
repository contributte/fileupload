# Contributte / Fileupload

jQuery-FileUpload is component which extends UploadControl in Nette form using [blueimp/jQuery-File-Upload](https://github.com/blueimp/jQuery-File-Upload).

## Content

## Setup

```
extensions:
    fileUpload: Contributte\FileUpload\FileUploadExtension
```

### Configuration of DI extension options

```
fileUpload:
	maxFiles: 10
	maxFileSize: 2M
	fileFilter: Contributte\FileUpload\Filter\ImageFilter
	uploadModel: App\Model\MyUploadModel
	uiMode: # full nebo minimal
```

## CSS and JS

### Copy files

This component needs for its precise functionality some third party scripts and styles. Copy all required assets from the assets folder to the www root of the project.

### Loading CSS and JS in layout.latte

```
{\Contributte\FileUpload\FileUploadControl::getHead($basePath)}
{\Contributte\FileUpload\FileUploadControl::getScripts($basePath)}
``` 

## Upload model

Component can use custom processing file upload through `UploadModel` which does not save files on server directly.

### Interface

Custom `UploadModel` must implement interface **\Contributte\FileUpload\Model\IUploadModel**.

```php
namespace Contributte\FileUpload\Model;

/**
 * Interface IUploadController
 * @author Zechy <email@zechy.cz>
 * @package Contributte\FileUpload\Model
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

The interface has three primary methods - save, rename and remove.

The method `save` is called when uploading the file to the server, this method is expected to return any return value, which will then be returned when retrieving the field value when processing the form. If custom parameters for uploaded files have been set with the **FileUploadControl::setParams()** method, they are passed as fields to the **$params** parameter.

The method `remove` is to remove a file after upload, its parameter is the value that was returned by the save function.

The method `rename` is to rename the uploaded file. Its first parameter is the value returned by the save function and the new file name. Rename should return the same value as save.

### Registering custom model

```
fileUpload:
    uploadModel: App\Model\MyUploadModel
```

## Filtering of files

Uploaded files can be filtered using their mimetype or suffix, so it is possible to limit the upload of files to images only. There are filter classes for this purpose.

All filters are instances of the abstract class **\Contributte\FileUpload\Filter\BaseFilter**, which implements the basic methodology for determining the correctness of a file using a mimetype, or according to the file extension. This basic filter implements the **\Contributte\FileUpload\Filter\IMimeTypeFilter** interface.

## Basic filters

```php
FileUploadControl::FILTER_IMAGES; // Allows uploading of images png, jpeg, jpg, gif only.
FileUploadControl::FILTER_DOCUMENTS; // Allows uploading of documents txt, doc, docx, xls, xlsx, ppt, pptx, pdf  only.
FileUploadControl::FILTER_ARCHIVE; // Allows uploading of files zip, tar, rar, 7z only.
FileUploadControl::FILTER_AUDIO; // Allows uploading of files mp3, ogg, aiff only.

FileUploadControl::setFileFilter("Constant or custom class"); // Sets the class to determine if the file can be uploaded
```

The setFileFilter method accepts as a parameter a string in which the class name is written, eg **Contributte\FileUpload\Filter\ImageFilter**.

## Custom filter

### Using BaseFilter

To use the basic methodology for determining the correctness of a file type, you can create your own class, which will be a child of **\Contributte\FileUpload\Filter\BaseFilter**.

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

If you want to create your own way of determining the file type, you can create your own class, but it must implement the **\Contributte\FileUpload\Filter\IMimeTypeFilter** interface.

```php
/**
 * Interface IMimeTypeFilters
 * Interface for checking the mimetype of file
 * @author Zechy <email@zechy.cz>
 * @package Contributte\FileUpload\Filter
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
FileUploadControl::setMaxFiles(25); // Setter for maximum of files.
FileUploadControl::setMaxFileSize("2M"); // Setter for maximum of file size.
FileUploadControl::setUploadModel('\Model\File\UploadModel'); // Setter for custom upload model.
FileUploadControl::setFileFilter('\Contributte\FileUpload\Filter\ImageFilter'); // Setter for restrictions on uploading files to specified types. You can string a custom class or use the FileUploadControl constants.
FileUploadControl::setUiTemplate(FileUploadControl::UI_FULL, __DIR__ . "/path/to/my/template.latte");
FileUploadControl::setParams(["productId" => 10]); // Setter for custom values for the uploaded file
```

## Catching exceptions

### Server-Side Controller

On the controller side, any exceptions can be thrown from the UploadModel. Exceptions are caught during processing and a JSON response with error information is sent to the uploader.

### Client-Side

If an error is returned from the server while uploading a file, its name will be replaced in the file list by an error message. If the application is not in run mode, the information with the error message from the exception is written as an error to the console.

## Upgrading from 1.2 tp 2.0

### Registration

* UiMode was removed from registration. Newly replaced by Renderer settings.
* UI Modes are replaced by custom renderer class.

### Configuration

New options have been added to the configuration:

#### renderer

Replacing uiMode, it takes the name of the class that will render the FileUploader. Can be used [predefined or custom](#rendering).

#### translator

Name of class (not required) which implements translator. If the is no one, upúloader tries to load **\Nette\Localization\ITranslator**.interface.

#### autoTranslate

Default value is `false`. Automatically translates entered error messages.

#### messages

The error messages:
* maxFiles - The maximum of files.
* maxSize - The maximum size of file.
* fileTypes - Allowed file MIME types.
* fileSize - PHP error - the file is too big.
* partialUpload - PHP error - The file was partially uploaded.
* noFile - PHP error. No file uploaded.
* tmpFolder - PHP error. Temporary folder is missing.
* cannotWrite - PHP error. Cannot write the file.
* stopped - PHP error. File upload was interrupted.

#### uploadSettings

List of custom configuration values for the uploader. This item is used to customize the blueimp uploader, allows you to specify all the configuration properties that the uploader offers.

## Rendering

### Default renderers

Fileuploader comes with three predefined renderers:
* [Html5Renderer](https://github.com/JZechy/jQuery-FileUpload/blob/master/src/Template/Renderer/Html5Renderer.php)
* [Bootstrap3Renderer](https://github.com/JZechy/jQuery-FileUpload/blob/master/src/Template/Renderer/Bootstrap3Renderer.php)
* [Bootstrap4Renderer](https://github.com/JZechy/jQuery-FileUpload/blob/master/src/Template/Renderer/Bootstrap4Renderer.php)

By default, Html5Renderer is selected.

### Custom renderer

It is also possible to create custom class for rendering the uploader. Everything is done with the help of the class **\Nette\Utils\Html**. There are several basic elements / components available to render the uploader which you can modify as you wish.

* **container**: Element that wraps the entire uploader.
* **input**: File input to which uploader events are linked.
* **globalProgress**: An element used for showing progress bar for the entire file queue.
* **globalProgressValue**: Element used to showing list the percentage of uploaded of the current queue.
* **fileProgress**: Element that used for showing progress bar for the file itself.
* **fileProgressValue**: Element used for showing list percentage of upload of the current file.
* **imagePreview**: Element which is for showing uploaded image preview.
* **filePreview**: Element which is for showing type of uploaded file is listed.
* **filename**: Element in which shows the file name.
* **delete**: Element that is used for a button / link to delete a file.
* **errorMessage**: Element into which error messages are written.

All elements are set by default as `div`, except of the following:

* **input** is input type = "file"
* **delete** is button type = "button"
* **imagePreview** is img

Progress bars can be HTML5 tags &lt;progress&gt; or &lt;div&gt;.

#### BaseRenderer

The custom renderer must inherit from the **\Contributte\FileUpload\Template\Renderer\BaseRenderer** class. This class has the attribute **$elements** in which are stored the Html Prototypes of all components in the field on indexes.

BaseRenderer contains three abstract methods that need to be implemented.

```php
/**
 * Class BaseRenderer
 *
 * @author  Zechy <email@zechy.cz>
 * @package Contributte\FileUpload\Template\Renderer
 */
abstract class BaseRenderer extends Object implements IUploadRenderer {

	/**
	 * Sestavení výchozí šablony uploaderu.
	 *
	 * @return \Nette\Utils\Html
	 */
	abstract public function buildDefaultTemplate();
	
	/**
	 * Sestavení šablony pro vkládání nových souborů.
	 *
	 * @return \Nette\Utils\Html
	 */
	abstract public function buildFileContainerTemplate();
	
	/**
	 * Sestavení šablony pro soubor, u kterého vznikla chyba.
	 *
	 * @return \Nette\Utils\Html
	 */
	abstract public function buildFileError();
}
```

The **buildDefaultTemplate()** method builds the base renderer template, which is what is visible when the page is loaded. Each additional file added then uses a template that is built using **buuildFileContainerTemplate()**. If an error occurs while uploading the file, the template from the **buildFileError()** method is used.

If it is not necessary to use a component in the template, it is possible to overwrite its HTML prototype with the value null, then no attempt will be made to fill / use it during rendering.

### Default files

Since version 2.0.0-beta2, default files can be entered into the uploader. When loaded these files are displayed as already uploaded files that the user can delete.

### DefaultFile

Use the container **\Contributte\FileUpload\Model\DefaultFile** to add a default file. 

```php
class DefaultFile extends Object {
	
	/**
	 * Callback pro smazání výchozího souboru s parametry (mixed $identifier).
	 *
	 * @var array
	 */
	public $onDelete = [];
	
	/**
	 * Odkaz na náhled obrázku.
	 *
	 * @var string
	 */
	private $preview;
	
	/**
	 * Název souboru.
	 *
	 * @var string
	 */
	private $filename;
	
	/**
	 * Identifikátor souboru sloužící pro jeho smazání.
	 *
	 * @var mixed
	 */
	private $identifier;
}
```

The container offers the ability to specify a PHP callback to a function that will eventually delete the default file. It is also possible to set a link to display a preview of the image, the file name and its identifier which is used for callback when deleting a file.

The default file can be added to the uploader using `setDefaultFiles()`, which accepts an array of files, or `addDefaultFiles()`, which adds a single file to an array.
