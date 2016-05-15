/**
 * @author Zechy <email@zechy.cz>
 * @param string id
 * @param boolean productionMode
 * @param string token
 * @constructor
 */
var FileUploadController = function (id, productionMode, token) {

	/**
	 * HTML ID vygenerované nette.
	 * @type {String}
	 */
	this.id = id;

	/**
	 * Je prostředí provozní nebo vývojové?
	 * @type {bool}
	 */
	this.productionMode = productionMode;

	/**
	 * Identifikační token.
	 * @type {string}
	 */
	this.token = token;

	/**
	 * Odkaz pro vymazání souboru.
	 * @type {string}
	 */
	this.deleteLink = "#";

	/**
	 * Odkaz pro přejmenování souboru.
	 * @type {string}
	 */
	this.renameLink = "#";

	/**
	 * Tabulka se seznamem souborů.
	 * @type {Element}
	 */
	this.table = document.getElementById(id + "-table-tbody");

	/**
	 * Progress bar.
	 * @type {Element}
	 */
	this.progress = document.getElementById(id + "-progress");

	/**
	 * Čítač přidaných souborů.
	 * @type {number}
	 */
	this.idCounter = 0;

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
	 * Vybere text v elementu.
	 * @param {string} element
	 */
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
	 * Vrátí koncovku souboru.
	 * @param {String} filename
	 * @returns {String}
	 */
	this.getFileExtension = function (filename) {
		var filename = filename.split(".");
		return filename[filename.length - 1];
	};

	/**
	 * Zjistí, zda je soubor obrázek.
	 * @param filename
	 * @returns {Boolean}
	 */
	this.isImage = function (filename) {
		return this.imageExtension.indexOf(this.getFileExtension(filename).toLowerCase()) !== -1;
	};

	/**
	 * Vygeneruje náhled obrázku.
	 * @param {Object} file
	 * @returns {Element}
	 */
	this.generatePreview = function (file) {
		var td = document.createElement("td");
		td.classList.add("preview");
		if (this.isImage(file.name)) {
			var preview = "";

			if (file.preview) {
				preview = file.preview;
			} else {
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
	 * Vygeneruje tlačíka pro akce se souborem.
	 * @returns {Element}
	 */
	this.generateActionButtons = function () {
		var td = document.createElement("td");
		td.classList.add("buttons");

		var deleteButton = document.createElement("a");
		deleteButton.classList.add("btn", "btn-danger", "btn-sm", "zet-fileupload-delete", "disabled");
		deleteButton.setAttribute("data-file-id", this.idCounter.toString());
		deleteButton.setAttribute("id", "file-delete-"+this.idCounter.toString());

		var self = this;
		deleteButton.onclick = function () {
			if(!this.classList.contains("disabled")) {
				self.deleteFile(this);
			}
		};

		var deleteIcon = document.createElement("i");
		deleteIcon.classList.add("glyphicon", "glyphicon-remove");
		deleteButton.appendChild(deleteIcon);

		td.appendChild(deleteButton);

		return td;
	};

	/**
	 * Vygeneruje název souboru.
	 * @param filename
	 */
	this.generateFileName = function (filename) {
		var div = document.createElement("div");
		//div.setAttribute("contenteditable", "true");
		div.setAttribute("data-file-id", this.idCounter.toString());
		div.setAttribute("id", "file-rename-"+ this.idCounter.toString());
		div.classList.add("filename");
		div.textContent = filename;
		/*div.onclick = function () {
			document.execCommand("selectAll", false, null);
		};*/
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
	}

	/**
	 * Vygeneruje progress pro soubor.
	 */
	this.generateFileProgress = function () {
		var td = document.createElement("td");
		td.classList.add("file-progress");

		var progress = document.createElement("div");
		progress.classList.add("progress");
		progress.setAttribute("id", "file-" + this.idCounter + "-progress");

		var progressBar = document.createElement("div");
		progressBar.classList.add(
			"progress-bar", "progress-bar-success", "progress-bar-striped", "active", "zet-file-progress"
		);
		progressBar.style.width = "0%";
		progressBar.setAttribute("id", "file-" + this.idCounter + "-progressbar");

		var span = document.createElement("span");
		span.setAttribute("id", "file-" + (this.idCounter) + "-progressbar-value");
		span.textContent = "0%";
		progressBar.appendChild(span);
		progress.appendChild(progressBar);
		td.appendChild(progress);

		return td;
	};

	/**
	 * Zapíše error k souboru.
	 * @param id
	 * @param msg
	 */
	this.writeError = function (id, msg) {
		var fileTr = document.getElementById("file-" + id);
		fileTr.classList.add("bg-warning");
		var nameTd = fileTr.querySelector(".name");
		nameTd.innerHTML += msg;
	}
};

FileUploadController.prototype = {

	/**
	 * Nastaví odkaz pro smazání.
	 * @param {String} link
	 */
	setDeleteLink: function (link) {
		this.deleteLink = link;
	},

	/**
	 * Přejmenuje nahraný soubor.
	 * @param {String} link
	 */
	setRenameLink: function (link) {
		this.renameLink = link;
	},

	/**
	 * Vygeneruje nový řádek s přidaným souborem.
	 * @param {Object} file
	 */
	addRow: function (file) {
		var tr = document.createElement("tr");
		tr.appendChild(this.generatePreview(file));
		tr.setAttribute("id", "file-" + this.idCounter);

		var fileName = document.createElement("td");
		fileName.classList.add("name");
		fileName.appendChild(this.generateFileName(file.name));

		tr.appendChild(fileName);
		tr.appendChild(this.generateFileProgress());
		tr.appendChild(this.generateActionButtons());
		this.idCounter++;

		this.table.appendChild(tr);
	},

	/**
	 * Nastaví progress celé fronty souborů.
	 * @param {Object} data
	 */
	setProgressAll: function (data) {
		var percents = parseInt(data.loaded / data.total * 100, 10);
		var progressBar = this.progress.querySelector("#" + this.id + "-progressbar");
		var progressBarValue = this.progress.querySelector("#" + this.id + "-progressbar-value");

		progressBar.style.width = percents + "%";
		progressBarValue.textContent = percents;
	},

	/**
	 * Nastaví progress jednoho souboru.
	 * @param {Object} data
	 */
	setFileProgress: function (data) {
		var id = data.formData[0].value;
		var fileProgress = document.getElementById("file-" + id + "-progressbar");
		var fileProgressValue = document.getElementById("file-" + id + "-progressbar-value");
		var percents = parseInt(data.loaded / data.total * 100, 10);

		fileProgress.style.width = percents + "%";
		fileProgressValue.textContent = percents + "%";
	},

	/**
	 * Po dokončení uploadu ...
	 */
	stop: function () {
		var progressBar = this.progress.querySelector("#" + this.id + "-progressbar");
		progressBar.classList.remove("active");

		var fileProgresses = document.querySelectorAll(".zet-file-progress");
		for (var i = 0; i < fileProgresses.length; i++) {
			fileProgresses[i].classList.remove("active");
		}
	},

	/**
	 * Při zahájení uploadu ...
	 */
	start: function () {
		var progressBar = this.progress.querySelector("#" + this.id + "-progressbar");
		progressBar.classList.add("active");
		var progressBarValue = this.progress.querySelector("#" + this.id + "-progressbar-value");

		progressBar.style.width = "0%";
		progressBarValue.textContent = 0;
	},

	/**
	 * Po dokončení jednotlových uploadů.
	 * @param {Object} data
	 */
	uploadDone: function (data) {
		var result = data.result;
		var id = result.id;
		var error = result.error;

		if (error != 0) {
			var msg = "";
			switch (error) {
				case 1:
				case 2:
					msg = "Soubor je příliš veliký.";
					break;
				case 3:
					msg = "Soubor byl nahrán pouze částečně.";
					break;
				case 4:
					msg = "Nebyl nahrán žádný soubor.";
					break;
				case 6:
					msg = "Chybí dočasná složka.";
					break;
				case 7:
					msg = "Nepodařilo se zapsat soubor na disk.";
					break;
				case 8:
					msg = "Nahrávání souboru bylo přerušeno.";
					break;
				case 99:
					msg = result.errorMessage + ".";
					break;
				case 100:
					msg = "Povolené typy souborů jsou " + result.errorMessage + ".";
					break;
			}
			this.writeError(id, msg);
		} else {
			var divName = document.getElementById("file-rename-"+id);
			divName.setAttribute("contenteditable", "true");

			var deleteButton = document.getElementById("file-delete-"+id);
			deleteButton.classList.remove("disabled");
		}
	},

	/**
	 * Vymazání souboru.
	 * @param {Element} element
	 */
	deleteFile: function (element) {
		$.ajax({
			url: this.deleteLink,
			data: {
				id: element.getAttribute("data-file-id"),
				token: this.token
			}
		}).done(function () {
			$(element).parents("tr").fadeOut();
		});
	},

	/**
	 * Přejmenuje soubor.
	 * @param element
	 */
	renameFile: function (element) {
		$.ajax({
			url: this.renameLink,
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
	},

	/**
	 * Přidá řádek s chybovou hláškou.
	 * @param {object} file
	 * @param {string} msg
	 */
	addRowError: function(file, msg) {
		this.addRow(file);
		this.writeError(this.idCounter, msg);
	}
};