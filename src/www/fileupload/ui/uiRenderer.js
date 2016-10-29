/**
 * @param id
 * @param deleteAction
 * @param renameAction
 * @param token
 */
var uiRenderer = function(id, deleteAction, renameAction, token) {
	
	/**
	 * Nette HTML ID.
	 * @var {string}
	 */
	this.id = id;
	
	/**
	 * @var {string}
	 */
	this.deleteAction = deleteAction;
	
	/**
	 * @var {string}
	 */
	this.renameAction = renameAction;
	
	/**
	 * @var {string}
	 */
	this.token = token;
	
	/**
	 * @type {null}
	 */
	this.onDelete = null;
	
	/**
	 * Základní koncovky obrázků.
	 * @type {string[]}
	 */
	this.imageExtension = [
		"jpg", "png", "jpeg", "gif"
	];
	
	/**
	 * Proměnná pro uložení timeoutu.
	 */
	this.timeout = [];
	
	/**
	 * Vrátí koncovku souboru.
	 * @param {String} filename
	 * @returns {String}
	 */
	this.getFileExtension = function (filename) {
		var filenameArray = filename.split(".");
		return filenameArray[filenameArray.length - 1];
	};
	
	/**
	 * @param {string} filename
	 */
	this.isImage = function(filename) {
		return this.imageExtension.indexOf(this.getFileExtension(filename).toLowerCase()) !== -1;
	};
	
	/**
	 * Vygeneruje popisek s typem souboru
	 * @param {string} filename
	 */
	this.generateExtInfo = function (filename) {
		var span = document.createElement("span");
		span.classList.add("label", "label-info");
		if(filename.indexOf(".") != -1) {
			span.textContent = "." + this.getFileExtension(filename);
		} else {
			span.textContent = "file";
		}
		return span;
	};
	
	/**
	 * Vymazání souboru.
	 * @param {Element} element
	 */
	this.deleteFile = function (element) {
		$.ajax({
			url: this.deleteAction,
			data: {
				id: element.getAttribute("data-file-id"),
				token: this.token
			}
		}).done(function () {
			$(element).parents(".zet-fileupload-file").fadeOut();
		});
	};
	
	/**
	 * Přejmenuje soubor.
	 * @param element
	 */
	this.renameFile = function (element) {
		$.ajax({
			url: this.renameAction,
			data: {
				id: element.getAttribute("data-file-id"),
				newName: element.textContent,
				token: this.token
			}
		}).done(function () {
			element.style.fontStyle = "normal";
			$(element).fadeOut("400", function () {
				$(element).fadeIn();
				element.blur();
			});
		});
	};
	
	this.selectText = function(element) {
		var doc = document
			, text = doc.getElementById(element)
			, range, selection
			;
		if (doc.body.createTextRange) {
			range = document.body.createTextRange();
			range.moveToElementText(text);
			range.select();
		} else if (window.getSelection) {
			selection = window.getSelection();
			range = document.createRange();
			range.selectNodeContents(text);
			selection.removeAllRanges();
			selection.addRange(range);
		}
	};
	
	/**
	 * @param {Object} file
	 * @returns {Element}
	 */
	this.getFilePreview = function(file) {
		var td = document.createElement("td");
		td.classList.add("preview");
		if(this.isImage(file.name)) {
			var preview = "";
			
			if(file.preview) {
				preview = file.preview;
			} else {
				//noinspection JSUnresolvedVariable
				preview = URL.createObjectURL(file);
			}
			
			var img = document.createElement("img");
			img.setAttribute("src", preview);
			img.classList.add("img-responsive");
			td.appendChild(img);
		} else {
			td.appendChild(this.generateExtInfo(file.name));
		}
		
		return td;
	};
	
	/**
	 * @param {Object} file
	 * @param {Number} id
	 * @returns {Element}
	 */
	this.getFileName = function(file, id) {
		var div = document.createElement("div");
		div.setAttribute("data-file-id", id.toString());
		div.setAttribute("id", "file-rename-"+ id.toString());
		div.classList.add("filename");
		div.textContent = file.name;
		var self = this;
		
		div.onfocus = function () {
			self.selectText(div.getAttribute("id"));
		};
		div.onkeyup = function (event) {
			div.style.fontStyle = "italic";
			
			if (event.code != "Tab") {
				clearTimeout(self.timeout[this.getAttribute("data-file-id")]);
				
				self.timeout[this.getAttribute("data-file-id")] = setTimeout(function () {
					self.renameFile(div);
				}, 750);
			}
		};
		
		return div;
	};
	
	/**
	 * @param {Number} id
	 * @returns {Element}
	 */
	this.generateDeleteButton = function(id) {
		var deleteButton = document.createElement("a");
		deleteButton.classList.add("btn", "btn-danger", "btn-sm", "zet-fileupload-delete", "disabled");
		deleteButton.setAttribute("data-file-id", id.toString());
		deleteButton.setAttribute("id", "file-delete-"+this.token +"-"+id.toString());
		deleteButton.setAttribute("title", "Smazat");
		deleteButton.setAttribute("data-toggle", "tooltip");
		
		var self = this;
		deleteButton.onclick = function () {
			if(!this.classList.contains("disabled")) {
				//noinspection JSCheckFunctionSignatures
				self.deleteFile(this);
				self.onDelete();
			}
		};
		
		var deleteIcon = document.createElement("i");
		deleteIcon.classList.add("glyphicon", "glyphicon-remove");
		deleteButton.appendChild(deleteIcon);
		
		return deleteButton;
	};
	
	/**
	 * @param {Number} id
	 * @returns {Element}
	 */
	this.generateFileProgress = function (id) {
		var progress = document.createElement("div");
		progress.classList.add("progress");
		progress.setAttribute("id", "file-" +this.token +"-"+ id + "-progress");
		
		var progressBar = document.createElement("div");
		progressBar.classList.add(
			"progress-bar", "progress-bar-success", "progress-bar-striped", "active", "zet-file-progress"
		);
		progressBar.style.width = "0%";
		progressBar.setAttribute("id", "file-" +this.token +"-"+ id + "-progressbar");
		
		var span = document.createElement("span");
		span.setAttribute("id", "file-" +this.token +"-"+ (id) + "-progressbar-value");
		span.textContent = "0%";
		progressBar.appendChild(span);
		progress.appendChild(progressBar);
		
		return progress;
	};
	
	/**
	 * @param data
	 */
	this.setFileProgress = function(data) {
		var id = data.formData[0].value;
		var fileProgress = document.getElementById("file-" +this.token +"-"+ id + "-progressbar");
		var fileProgressValue = document.getElementById("file-" +this.token +"-"+ id + "-progressbar-value");
		var percents = parseInt(data.loaded / data.total * 100, 10);
		
		fileProgress.style.width = percents + "%";
		fileProgressValue.textContent = percents + "%";
	};
	
	/**
	 * @param id
	 */
	this.enableActions = function(id) {
		var divName = document.getElementById("file-rename-"+id);
		divName.setAttribute("contenteditable", "true");
		
		var deleteButton = document.getElementById("file-delete-"+this.token +"-"+id);
		deleteButton.classList.remove("disabled");
	}
};

uiRenderer.prototype = {
	
	/**
	 * @param callback
	 */
	setOnDelete: function(callback) {
		this.onDelete = callback;
	}
};