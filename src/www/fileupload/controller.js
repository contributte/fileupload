var Renderer = function (token, components, inputHtmlId, removeLink) {

	/**
	 * @type {string}
	 */
	this.token = token;

	/**
	 * Seznam registrovaných komponent uploaderu společně s jejich html id.
	 * - container Kontejner ve kterém se nachází samotný uploaderu.
	 * - input File Input na kterém je registrovaný uploader.
	 * - globalProgress Element sloužící jako progress bar pro všechny nahrávané soubory.
	 * - globalProgressValue Element sloužící pro hodnotu progressbaru.
	 * - fileProgress Element sloužící jako progress bar pro aktuální soubor.
	 * - fileProgressValue Element sloužící pro hodnotu progressbaru.
	 * - imagePreview Element, ve kterém bude zobrazen náhled obrázku.
	 * - filePreview Element, ve kterém budou zobrazeny informace o souboru.
	 * - filename Element, ve kterém bude zobrazen název souboru.
	 * - delete Element sloužící jako tlačítko pro smazání.
	 * - errorMessage Element, ve kterém bude zobrazena chybová zpráva.
	 *
	 * @param { object.<string, string> }
	 */
	this.components = components;

	/**
	 * @type {string}
	 */
	this.inputHtmlId = inputHtmlId;

	/**
	 * @type {string}
	 */
	this.removeLink = removeLink;

	/**
	 * @type {string[]}
	 */
	this.imageExtension = [
		"jpg", "png", "jpeg", "gif"
	];

	this.getSelector = function (name) {
		return "[data-upload-component=" + name + "]";
	};

	this.getFileExtension = function (filename) {
		var filenameArray = filename.split(".");
		return filenameArray[filenameArray.length - 1];
	};

	this.isImage = function (filename) {
		return this.imageExtension.indexOf(this.getFileExtension(filename).toLowerCase()) !== -1;
	};

	this.setImagePreview = function (element, file) {
		var preview = "";

		if (file.preview) {
			preview = file.preview;
		} else {
			//noinspection JSUnresolvedVariable
			preview = URL.createObjectURL(file);
		}

		element.setAttribute("src", preview);
	};

	this.getFileContainer = function (id) {
		var container = document.querySelector("[data-upload-id='" + id.toString() + "'][for='" + this.inputHtmlId + "']");

		return container;
	};


	this.getTemplate = function (template) {
		var template = $.parseHTML(document.querySelector("." + template + "[for=" + this.inputHtmlId + "]").innerHTML);
		return template[1];
	};

	this.errorTemplate = function (file, message) {
		var template = this.getTemplate("upload-template-file-error");

		if (this.components.filename != null) {
			var filename = template.querySelector(this.getSelector(this.components.filename));
			if (filename != null) {
				filename.textContent = file.name;
			}
		}

		if (this.components.imagePreview != null && this.isImage(file.name)) {
			var imagePreview = template.querySelector(this.getSelector(this.components.imagePreview));
			if (imagePreview != null) {
				this.setImagePreview(imagePreview, file);
			}
		} else if (this.components.filePreview != null) {
			var filePreview = template.querySelector(this.getSelector(this.components.filePreview));
			if (filePreview != null) {
				filePreview.textContent = this.getFileExtension(file.name);
			}
		}

		template.querySelector(this.getSelector(this.components.errorMessage)).textContent = message;

		return template;
	}

};

Renderer.prototype = {

	/**
	 * @param { object.<int, object> } file
	 * @param { number } id
	 */
	add: function (file, id) {
		var template = this.getTemplate("upload-template-file-container");
		template.setAttribute("data-upload-id", id.toString());
		template.setAttribute("for", this.inputHtmlId);

		if (this.components.filename != null) {
			template.querySelector(this.getSelector(this.components.filename)).textContent = file.name;
		}

		if (this.isImage(file.name) && this.components.imagePreview != null) {
			var imagePreview = template.querySelector(this.getSelector(this.components.imagePreview));
			this.setImagePreview(imagePreview, file);
		} else if (this.components.filePreview != null) {
			template.querySelector(this.getSelector(this.components.filePreview)).textContent = this.getFileExtension(file.name);
		}

		document.querySelector(this.getSelector(this.components.container)).appendChild(template);
	},

	addDefaultFile: function (file, controller) {
		var template = this.getTemplate("upload-template-file-container");
		template.setAttribute("for", this.inputHtmlId);

		if (this.components.filename != null) {
			template.querySelector(this.getSelector(this.components.filename)).textContent = file.filename;
		}

		if (this.isImage(file.filename) && this.components.imagePreview != null) {
			var imagePreview = template.querySelector(this.getSelector(this.components.imagePreview));
			imagePreview.setAttribute("src", file.preview);
		} else if (this.components.filePreview != null) {
			template.querySelector(this.getSelector(this.components.filePreview)).textContent = this.getFileExtension(file.filename);
		}

		if (this.components.delete != null) {
			var deleteButton = template.querySelector(this.getSelector(this.components.delete));

			var self = this;
			deleteButton.addEventListener("click", function () {
				$.ajax({
					url: self.removeLink,
					data: {
						id: file.id,
						token: self.token,
						default: 1
					}
				}).done(function () {
					$(template).fadeOut(400, function () {
						$(this).remove();
						controller.uploaded--;
						controller.addedFiles--;
					});
				});
			});
		}

		document.querySelector(this.getSelector(this.components.container)).appendChild(template);
	},

	/**
	 * @param { object.<int, object> } file
	 * @param { number } id
	 * @param { string } message
	 */
	addError: function (file, id, message) {
		var template = this.errorTemplate(file, message);
		document.querySelector(this.getSelector(this.components.container)).appendChild(template);
	},

	/**
	 * @param { object } data
	 */
	updateFileProgress: function (data) {
		var container = this.getFileContainer(data.formData[0].value);
		var percents = parseInt(data.loaded / data.total * 100, 10);

		if (this.components.fileProgress != null) {
			var progress = container.querySelector(this.getSelector(this.components.fileProgress));
			if (progress.tagName.toLowerCase() == "progress") {
				progress.setAttribute("value", percents.toString());
			} else {
				progress.style.width = percents + "%";
			}
		}

		if (this.components.fileProgressValue != null) {
			var value = container.querySelector(this.getSelector(this.components.fileProgressValue));
			value.textContent = percents + "%";
		}
	},

	/**
	 * @param { object } data
	 */
	updateProgressAll: function (data) {
		var percents = parseInt(data.loaded / data.total * 100, 10);

		if (this.components.globalProgress != null) {
			var progress = document.querySelector(this.getSelector(this.components.globalProgress));
			if (progress.tagName.toLowerCase() == "progress") {
				progress.setAttribute("value", percents.toString());
			} else {
				progress.style.width = percents + "%";
			}
		}

		if (this.components.globalProgressValue != null) {
			var value = document.querySelector(this.getSelector(this.components.globalProgressValue));
			value.textContent = percents + "%";
		}
	},

	/**
	 *
	 */
	start: function () {
		if (this.components.globalProgress != null) {
			var progress = document.querySelector(this.getSelector(this.components.globalProgress));
			if (progress.tagName.toLowerCase() == "progress") {
				progress.setAttribute("value", "0");
			} else {
				progress.style.width = "0%";
			}
		}

		if (this.components.globalProgressValue != null) {
			var value = document.querySelector(this.getSelector(this.components.globalProgressValue));
			value.textContent = "0%";
		}
	},

	/**
	 * @param { Object } file
	 * @param { string } message
	 * @param { number } id
	 */
	fileError: function (file, message, id) {
		var template = this.errorTemplate(file, message);
		var fileContainer = this.getFileContainer(id);

		$(fileContainer).replaceWith(template);
	},

	/**
	 * @param { number } id
	 * @param { FileUploadController } controller
	 */
	fileDone: function (id, controller) {
		var fileContainer = this.getFileContainer(id);

		if (this.components.delete != null) {
			var deleteButton = fileContainer.querySelector(this.getSelector(this.components.delete));

			var self = this;
			deleteButton.addEventListener("click", function () {
				$.ajax({
					url: self.removeLink,
					data: {
						id: id,
						token: self.token
					}
				}).done(function () {
					$(fileContainer).fadeOut(400, function () {
						$(this).remove();
						controller.uploaded -= 1;
						controller.addedFiles -= 1;
					});
				});
			});
		}
	}
};

/**
 * @param {number} id
 * @param {string} token
 * @param {RendererDefinition} renderer
 * @param {object} config
 * @param {object} messages
 * @constructor
 */
var FileUploadController = function (id, token, renderer, config, messages) {

	/**
	 * @type {number}
	 */
	this.id = id;

	/**
	 * @type {string}
	 */
	this.token = token;

	/**
	 * @type {Renderer}
	 */
	this.renderer = renderer;

	/**
	 * @type {object}
	 */
	this.config = config;

	/**
	 * ID uploadovaného souboru.
	 * @type {number}
	 */
	this.fileId = 0;

	/**
	 * Počet nahraných souborů.
	 * @type {number}
	 */
	this.uploaded = 0;

	/**
	 * Počet přidaných souborů.
	 * @type {number}
	 */
	this.addedFiles = 0;

	/**
	 * Chybové hlášky.
	 * @type {object.<string, string>}
	 */
	this.messages = messages;
};

FileUploadController.prototype = {

	/**
	 * Přidání nového souboru k odeslání.
	 * @param {object.<int, object>} files
	 * @returns {boolean}
	 */
	add: function (files) {
		var readyToSend = false;
		var file = files[0];
		var message = "";

		if (!this.canUploadNextFile()) {
			message = this.messages.maxFiles.replace("{maxFiles}", this.config.maxFiles.toString());
			this.renderer.addError(file, this.fileId, message);
			this.fileId++;
		} else if (file["size"] > this.config.maxFileSize) {
			message = this.messages.maxSize.replace("{maxSize}", this.config.fileSizeString);
			this.renderer.addError(file, this.fileId, message);
			this.fileId++;
		} else {
			this.addedFiles++;
			this.renderer.add(file, this.fileId);
			readyToSend = true;
		}

		return readyToSend;
	},

	/**
	 * Aktualizace celkového postupu nahrávání.
	 * @param {Object} data
	 */
	updateProgressAll: function (data) {
		this.renderer.updateProgressAll(data);
	},

	/**
	 * Aktualizace postupu nahrávání jednoho souboru.
	 * @param {Object} data
	 */
	updateFileProgress: function (data) {
		this.renderer.updateFileProgress(data);
	},

	/**
	 * Spuštění uploadu.
	 */
	start: function () {
		this.renderer.start();
	},

	/**
	 * Dokončení uploadu.
	 * @param {object} data
	 */
	done: function (data) {
		var success = true;

		var result = data.result;
		var id = result.id;
		var error = result.error;

		if (error !== 0) {
			var msg = "";

			switch (error) {
				case 1:
				case 2:
					msg = this.messages.fileSize;
					break;
				case 3:
					msg = this.messages.partialUpload;
					break;
				case 4:
					msg = this.messages.noFile;
					break;
				case 6:
					msg = this.messages.tmpFolder;
					break;
				case 7:
					msg = this.messages.cannotWrite;
					break;
				case 8:
					msg = this.messages.stopped;
					break;
				case 99:
					//noinspection JSUnresolvedVariable
					msg = result.errorMessage;
					break;
				case 100:
					//noinspection JSUnresolvedVariable
					msg = this.messages.fileTypes.replace("{fileTypes}", result.errorMessage);
					break;
			}
			this.renderer.fileError(data.files[0], msg, id);
			success = false;
		} else {
			this.renderer.fileDone(id, this);
		}

		if (success) {
			this.uploaded++;
		} else {
			this.uploaded -= 1;
			this.addedFiles -= 1;
		}
	},

	/**
	 * ID dalšího souboru k odeslání.
	 * @returns {number}
	 */
	getFileId: function () {
		return this.fileId++;
	},

	/**
	 * Může uživatel nahrát další soubor?
	 * @returns {boolean}
	 */
	canUploadNextFile: function () {
		return this.uploaded < this.config.maxFiles && this.addedFiles < this.config.maxFiles;
	},

	/**
	 * @returns {Object.<string, string>}
	 */
	getMessages: function () {
		return this.messages;
	},

	/**
	 *
	 * @param defaultFiles
	 */
	addDefaultFiles: function (defaultFiles) {
		for (var i = 0; i < defaultFiles.length; i++) {
			this.uploaded++;
			this.addedFiles++;
			this.renderer.addDefaultFile(defaultFiles[i], this);
		}
	}
};
