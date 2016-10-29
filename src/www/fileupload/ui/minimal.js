/**
 * @param id
 * @param deleteAction
 * @param renameAction
 * @param token
 * @constructor
 */
var UIMininalRenderer = function(id, deleteAction, renameAction, token) {
	uiRenderer.call(this, id, deleteAction, renameAction, token);
	
	/**
	 * @type {string}
	 */
	this.container = document.getElementById(this.id + "-container");
	
	/**
	 * @param file
	 * @param id
	 * @returns {Element}
	 */
	this.getFileNameWrap = function(file, id) {
		var filenameDiv = this.getFileName(file, id);
		filenameDiv.classList.add("pull-left");
		
		return filenameDiv;
	};
	
	/**
	 * @param id
	 */
	this.generateFileProgressWrap = function(id) {
		var progress = this.generateFileProgress(id);
		progress.classList.add("progress-sm");
		var p = document.createElement("p");
		p.appendChild(progress);
		p.classList.add("progress");
		
		return p;
	};
	
	/**
	 * @param id
	 */
	this.generateDeleteButtonWrap = function(id) {
		var button = this.generateDeleteButton(id);
		button.classList.add("btn-xs");
		var div = document.createElement("div");
		div.classList.add("btn-group", "pull-right");
		div.appendChild(button);
		
		return div;
	};
};
extendsClass(UIMininalRenderer, uiRenderer);

UIMininalRenderer.prototype = {
	
	/**
	 * @param file
	 * @param id
	 * @param message
	 */
	addRowError: function(file, id, message) {
		this.addRow(file, id);
		this.writeError(message, id);
	},
	
	/**
	 * @param file
	 * @param id
	 */
	addRow: function(file, id) {
		var div = document.createElement("div");
		div.classList.add("well", "well-sm", "zet-fileupload-file");
		div.setAttribute("id", "file-" +this.token +"-"+ id);
		
		var row = document.createElement("div");
		row.classList.add("clearfix");
		row.appendChild(this.getFileNameWrap(file, id));
		row.appendChild(this.generateDeleteButtonWrap(id));
		
		div.appendChild(row);
		div.appendChild(this.generateFileProgressWrap(id));
		
		this.container.appendChild(div);
	},
	
	/**
	 * @param msg
	 * @param id
	 */
	writeError: function(msg, id) {
		var fileContainer = document.getElementById("file-" +this.token +"-"+ id);
		var nameP = fileContainer.querySelector(".filename");
		nameP.classList.add("alert", "alert-warning");
		nameP.textContent = msg;
		
		var progress = fileContainer.querySelector(".progress");
		progress.classList.add("hidden");
	},
	
	/**
	 * @param data
	 */
	updateProgressAll: function(data) {
		// By pass ...
	},
	
	/**
	 * @param data
	 */
	updateFileProgress: function(data) {
		this.setFileProgress(data);
	},
	
	/**
	 *
	 */
	stop: function() {
		// By pass ...
	},
	
	/**
	 *
	 */
	start: function() {
		// By pass ...
	},
	
	/**
	 * @param id
	 */
	fileDone: function(id) {
		var deleteButton = document.getElementById("file-delete-"+this.token +"-"+id);
		deleteButton.classList.remove("disabled");
	},
	
	/**
	 * @param id
	 */
	stopFileProgress: function(id) {
		var progressBar = document.getElementById("file-" + this.token + "-" + id +"-progressbar");
		progressBar.classList.remove("active");
	}
};