/**
 * @author Zechy <email@zechy.cz>
 * @param string id
 * @constructor
 */
var FileUploadController = function (id, productionMode) {

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
	 * Odkaz pro vymazání souboru.
	 * @type {string}
	 */
	this.deleteLink = "#";

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
		return this.imageExtension.indexOf(this.getFileExtension(filename)) !== -1;
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
		}

		return td;
	};

	/**
	 * Vygeneruje tlačíka pro akce se souborem.
	 * @returns {Element}
	 */
	this.generateActionButtons = function () {
		var td = document.createElement("td");
		td.classList.add("buttons");

		var deleteButton = document.createElement("a");
		deleteButton.classList.add("btn", "btn-danger", "btn-sm", "zet-fileupload-delete");
		deleteButton.setAttribute("data-file-id", this.idCounter.toString());
		var self = this;
		deleteButton.onclick = function () {
			self.deleteFile(this);
		};

		var deleteIcon = document.createElement("i");
		deleteIcon.classList.add("glyphicon", "glyphicon-remove");
		deleteButton.appendChild(deleteIcon);

		td.appendChild(deleteButton);

		return td;
	};

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
		if (!this.productionMode) console.error("File ID: " + id + ", Err msg: " + msg);
		var fileTr = document.getElementById("file-" + id);
		var nameTd = fileTr.querySelector(".name");
		nameTd.textContent = "Při nahrávání souboru došlo k chybě.";
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
	 * Vygeneruje nový řádek s přidaným souborem.
	 * @param {Object} file
	 */
	addRow: function (file) {
		var tr = document.createElement("tr");
		tr.appendChild(this.generatePreview(file));
		tr.setAttribute("id", "file-" + this.idCounter);

		var fileName = document.createElement("td");
		fileName.classList.add("name");
		fileName.textContent = file.name;
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
					msg = result.errorMessage;
					break;
			}
			this.writeError(id, msg);
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
				id: element.getAttribute("data-file-id")
			}
		}).done(function () {
			$(element).parents("tr").fadeOut();
		});
	}
};