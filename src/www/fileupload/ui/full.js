/**
 * @param id
 * @param deleteAction
 * @param renameAction
 * @param token
 * @constructor
 */
var UIFullRenderer = function(id, deleteAction, renameAction, token) {
	uiRenderer.call(this, id, deleteAction, renameAction, token);
	
	/**
	 * @type {Element}
	 */
	this.table = document.getElementById(id + "-table-tbody");
	
	/**
	 * @type {Element}
	 */
	this.progress = document.getElementById(id + "-progress");
	
	/**
	 * @param id
	 */
	this.generateFileProgressWrap = function(id) {
		var progress = this.generateFileProgress(id);
		
		var td = document.createElement("td");
		td.classList.add("file-progress");
		td.appendChild(progress);
		
		return td;
	};
	
	/**
	 * @param file
	 * @param id
	 */
	this.generateFileNameWrap = function(file, id) {
		var filename = this.getFileName(file, id);
		var td = document.createElement("td");
		td.classList.add("name");
		td.appendChild(filename);
		
		return td;
	};
	
	this.generateDeleteButtonWrap = function(id) {
		var button = this.generateDeleteButton(id);
		var td = document.createElement("td");
		td.classList.add("buttons");
		td.appendChild(button);
		
		return td;
	}
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
		tr.classList.add("zet-fileupload-file");
		tr.setAttribute("id", "file-" +this.token +"-"+ id);
		
		tr.appendChild(this.getFilePreview(file));
		tr.appendChild(this.generateFileNameWrap(file, id));
		tr.appendChild(this.generateFileProgressWrap(id));
		tr.appendChild(this.generateDeleteButtonWrap(id));
		
		this.table.appendChild(tr);
	},
	
	/**
	 * @param {String} msg
	 * @param {Number} id
	 */
	writeError: function(msg, id) {
		var fileTr = document.getElementById("file-" +this.token +"-"+ id);
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
		this.setFileProgress(data);
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
		this.enableActions(id);
	},
	
	/**
	 * @param {number} id
	 */
	stopFileProgress: function(id) {
		var progressBar = document.getElementById("file-" + this.token + "-" + id +"-progressbar");
		progressBar.classList.remove("active");
	}
};