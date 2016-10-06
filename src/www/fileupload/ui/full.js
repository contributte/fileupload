/**
 * @param id
 * @param deleteAction
 * @param renameAction
 * @param token
 * @constructor
 */
var UIFullRenderer = function(id, deleteAction, renameAction, token) {
	
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
	 * Základní koncovky obrázků.
	 * @type {string[]}
	 */
	this.imageExtension = [
		"jpg", "png", "jpeg", "gif"
	];
	
	/**
	 * @type {Element}
	 */
	this.table = document.getElementById(id + "-table-tbody");
	
	/**
	 * @type {Element}
	 */
	this.progress = document.getElementById(id + "-progress");
	
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
			$(element).parents("tr").fadeOut();
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
		
		var td = document.createElement("td");
		td.classList.add("name");
		td.appendChild(div);
		
		return td;
	};
	
	/**
	 * @param {Number} id
	 * @returns {Element}
	 */
	this.generateFileProgress = function (id) {
		var td = document.createElement("td");
		td.classList.add("file-progress");
		
		var progress = document.createElement("div");
		progress.classList.add("progress");
		progress.setAttribute("id", "file-" + id + "-progress");
		
		var progressBar = document.createElement("div");
		progressBar.classList.add(
			"progress-bar", "progress-bar-success", "progress-bar-striped", "active", "zet-file-progress"
		);
		progressBar.style.width = "0%";
		progressBar.setAttribute("id", "file-" + id + "-progressbar");
		
		var span = document.createElement("span");
		span.setAttribute("id", "file-" + (id) + "-progressbar-value");
		span.textContent = "0%";
		progressBar.appendChild(span);
		progress.appendChild(progressBar);
		td.appendChild(progress);
		
		return td;
	};
	
	/**
	 * @param {Number} id
	 * @returns {Element}
	 */
	this.generateActionButtons = function(id) {
		var td = document.createElement("td");
		td.classList.add("buttons");
		
		var deleteButton = document.createElement("a");
		deleteButton.classList.add("btn", "btn-danger", "btn-sm", "zet-fileupload-delete", "disabled");
		deleteButton.setAttribute("data-file-id", id.toString());
		deleteButton.setAttribute("id", "file-delete-"+id.toString());
		
		var self = this;
		deleteButton.onclick = function () {
			if(!this.classList.contains("disabled")) {
				//noinspection JSCheckFunctionSignatures
				self.deleteFile(this);
			}
		};
		
		var deleteIcon = document.createElement("i");
		deleteIcon.classList.add("glyphicon", "glyphicon-remove");
		deleteButton.appendChild(deleteIcon);
		
		td.appendChild(deleteButton);
		
		return td;
	};
};

UIFullRenderer.prototype = {
	
	/**
	 * @param {Object} file
	 * @param {Number} id
	 * @param {String} message
	 */
	addRowError: function(file, id, message) {
		this.addRow(file, id);
		this.writeError(message, id);
	},
	
	/**
	 * @param {Object} file
	 * @param {Number} id
	 */
	addRow: function(file, id) {
		var tr = document.createElement("tr");
		tr.setAttribute("id", "file-" + id);
		
		tr.appendChild(this.getFilePreview(file));
		tr.appendChild(this.getFileName(file, id));
		tr.appendChild(this.generateFileProgress(id));
		tr.appendChild(this.generateActionButtons(id));
		
		this.table.appendChild(tr);
	},
	
	/**
	 * @param {String} msg
	 * @param {Number} id
	 */
	writeError: function(msg, id) {
		var fileTr = document.getElementById("file-" + id);
		fileTr.classList.add("bg-warning");
		
		var nameTd = fileTr.querySelector(".name");
		nameTd.innerHTML += msg;
	},
	
	/**
	 * @param {Object} data
	 */
	updateProgressAll: function(data) {
		var percents = parseInt(data.loaded / data.total * 100, 10);
		var progressBar = this.progress.querySelector("#" + this.id + "-progressbar");
		var progressBarValue = this.progress.querySelector("#" + this.id + "-progressbar-value");
		
		progressBar.style.width = percents + "%";
		progressBarValue.textContent = percents;
	},
	
	/**
	 * @param {Object} data
	 */
	updateFileProgress: function(data) {
		var id = data.formData[0].value;
		var fileProgress = document.getElementById("file-" + id + "-progressbar");
		var fileProgressValue = document.getElementById("file-" + id + "-progressbar-value");
		var percents = parseInt(data.loaded / data.total * 100, 10);
		
		fileProgress.style.width = percents + "%";
		fileProgressValue.textContent = percents + "%";
	},
	
	/**
	 *
	 */
	stop: function() {
		var progressBar = this.progress.querySelector("#" + this.id + "-progressbar");
		progressBar.classList.remove("active");
	},
	
	/**
	 *
	 */
	start: function() {
		var progressBar = this.progress.querySelector("#" + this.id + "-progressbar");
		progressBar.classList.add("active");
		var progressBarValue = this.progress.querySelector("#" + this.id + "-progressbar-value");
		
		progressBar.style.width = "0%";
		progressBarValue.textContent = 0;
	},
	
	/**
	 * @param {number} id
	 */
	fileDone: function(id) {
		var divName = document.getElementById("file-rename-"+id);
		divName.setAttribute("contenteditable", "true");
		
		var deleteButton = document.getElementById("file-delete-"+id);
		deleteButton.classList.remove("disabled");
	},
	
	/**
	 * @param {number} id
	 */
	stopFileProgress: function(id) {
		var progressBar = document.getElementById("file-"+ id +"-progressbar");
		progressBar.classList.remove("active");
	}
};