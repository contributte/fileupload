/**
 * @param id
 * @param deleteAction
 * @param renameAction
 * @param token
 * @constructor
 */
var UIFullRenderer = function(id, deleteAction, renameAction, token) {
	uiRenderer.call(this, id, deleteAction, renameAction);
	
	/**
	 * @var {string}
	 */
	this.token = token;
	
	/**
	 * @type {Element}
	 */
	this.table = document.getElementById(id + "-table-tbody");
	
	/**
	 * @type {Element}
	 */
	this.progress = document.getElementById(id + "-progress");
};
extendsClass(UIFullRenderer, uiRenderer);

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