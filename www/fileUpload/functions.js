/**
 * @author Zechy <email@zechy.cz>
 * @param string id
 * @constructor
 */
var FileUploadController = function (id) {

	/**
	 * HTML ID vygenerované nette.
	 * @type {String}
	 */
	this.id = id;

	/**
	 * Tabulka se seznamem souborů.
	 * @type {Element}
	 */
	this.table = document.getElementById(id + "-table-tbody");

	/**
	 * Čítač přidaných souborů.
	 * @type {number}
	 */
	this.idCounter = 0;

	/**
	 * Vygeneruje náhled obrázku.
	 * @param {Object} file
	 * @returns {Element}
	 */
	this.generatePreview = function (file) {
		var td = document.createElement("td");
		td.classList.add("preview");
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
		deleteButton.classList.add("btn", "btn-danger");
		var deleteIcon = document.createElement("i");
		deleteIcon.classList.add("glyphicon", "glyphicon-remove");
		deleteButton.appendChild(deleteIcon);
		td.appendChild(deleteButton);

		return td;
	};

	/**
	 * Vygeneruje progress pro soubor.
	 */
	this.generateFileProgress = function() {
		var td = document.createElement("td");
		td.classList.add("file-progress");
		td.setAttribute("id", "file-"+ this.idCounter);

		var progress = document.createElement("div");
		progress.classList.add("progress");
		var progressBar = document.createElement("div");
		progressBar.classList.add("progress-bar", "progress-bar-success", "progress-bar-striped", "active");
		progressBar.style.width = "0%";
		var span = document.createElement("span");
		span.setAttribute("id", "file-"+ (this.idCounter++) +"-progress");
		span.textContent = "0%";
		progressBar.appendChild(span);
		progress.appendChild(progressBar);
		td.appendChild(progress);

		return td;
	};
};

FileUploadController.prototype = {

	/**
	 * Vygeneruje nový řádek s přidaným souborem.
	 * @param {Object} file
	 */
	addRow: function (file) {
		var tr = document.createElement("tr");
		tr.appendChild(this.generatePreview(file));

		var fileName = document.createElement("td");
		fileName.textContent = file.name;
		tr.appendChild(fileName);
		tr.appendChild(this.generateFileProgress());
		tr.appendChild(this.generateActionButtons());

		console.log("Add Row");
		this.table.appendChild(tr);
	}
};