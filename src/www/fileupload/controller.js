var RendererDefinition = function() {

};

RendererDefinition.prototype = {
	
	/**
	 * @param {object.<int, object>} file
	 * @param {number} id
	 */
	add: function(file, id) {},
	
	/**
	 * @param {object.<int, object>} file
	 * @param {number} id
	 * @param {string} message
	 */
	addError: function(file, id, message) {},
	
	/**
	 * @param {object} data
	 */
	updateFileProgress: function(data) {},
	
	/**
	 * @param {object} data
	 */
	updateProgressAll: function(data) {},
	
	/**
	 *
	 */
	stop: function() {},
	
	/**
	 *
	 */
	start: function() {},
	
	/**
	 * @param {string} message
	 * @param {number} id
	 */
	fileError: function(message, id) {},
	
	/**
	 * @param {number} id
	 */
	fileDone: function(id) {},
	
	/**
	 * @param {number} id
	 */
	stopFileProgress: function(id) {}
};

/**
 * @param {number} id
 * @param {string} token
 * @param {RendererDefinition} renderer
 * @param {object} config
 * @constructor
 */
var FileUploadController = function(id, token, renderer, config) {
	
	/**
	 * @type {number}
	 */
	this.id = id;
	
	/**
	 * @type {string}
	 */
	this.token = token;
	
	/**
	 * @type {RendererDefinition}
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
	this.messages = {
		maxFiles: "Maximální počet souborů je %maxFiles%.",
		maxSize: "Maximální velikost souboru je %maxSize%.",
		fileTypes: "Povolené typy souborů jsou %fileTypes%.",
		
		// PHP Errors
		fileSize: "Soubor je příliš veliký.",
		partialUpload: "Soubor byl nahrán pouze částěčně.",
		noFile: "Nebyl nahrán žádný soubor.",
		tmpFolder: "Chybí dočasná složka.",
		cannotWrite: "Nepodařilo se zapsat soubor na disk.",
		stopped: "Nahrávání souboru bylo přerušeno."
	};
};

FileUploadController.prototype = {
	
	/**
	 * Přidání nového souboru k odeslání.
	 * @param {object.<int, object>} files
	 * @returns {boolean}
	 */
	add: function(files) {
		var readyToSend = false;
		var file = files[0];
		var message = "";
		
		if(!this.canUploadNextFile()) {
			message = this.messages.maxFiles.replace("%maxFiles%", this.config.maxFiles.toString());
			this.renderer.addError(file, this.fileId, message);
			this.fileId++;
		} else if(file["size"] > this.config.maxFileSize) {
			message = this.messages.maxSize.replace("%maxSize%", this.config.maxFileSize.toString());
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
	updateProgressAll: function(data) {
		this.renderer.updateProgressAll(data);
	},
	
	/**
	 * Aktualizace postupu nahrávání jednoho souboru.
	 * @param {Object} data
	 */
	updateFileProgress: function(data) {
		this.renderer.updateFileProgress(data);
	},
	
	/**
	 * Spuštění uploadu.
	 */
	start: function() {
		this.renderer.start();
	},
	
	/**
	 * Dokončení uploadu.
	 * @param {object} data
	 */
	done: function(data) {
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
					msg = result.errorMessage + ".";
					break;
				case 100:
					//noinspection JSUnresolvedVariable
					msg = this.messages.fileTypes.replace("%fileTypes%", result.errorMessage);
					break;
			}
			this.renderer.fileError(data.files[0], msg, id);
			success = false;
		} else {
			this.renderer.fileDone(id);
		}
		
		if(success) {
			this.uploaded++;
		} else {
			this.addedFiles -= 1;
		}
	},
	
	/**
	 * ID dalšího souboru k odeslání.
	 * @returns {number}
	 */
	getFileId: function() {
		return this.fileId++;
	},
	
	/**
	 * Může uživatel nahrát další soubor?
	 * @returns {boolean}
	 */
	canUploadNextFile: function() {
		return this.uploaded < this.config.maxFiles || this.addedFiles < this.config.maxFiles;
	},
	
	/**
	 * @returns {Object.<string, string>}
	 */
	getMessages: function() {
		return this.messages;
	}
};
