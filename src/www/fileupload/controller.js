/**
 * @param id
 * @param productionMode
 * @param token
 * @param config
 * @constructor
 */
var FileUploadController = function(id, productionMode, token, config) {
	
	/**
	 * Nette HTML ID
	 * @var {string}
	 */
	this.id = id;
	
	/**
	 * Provoz nebo vývoj?
	 * @var {boolean}
	 */
	this.productionMode = productionMode;
	
	/**
	 * Identifikační token upload inputu.
	 * @var {string}
	 */
	this.token = token;
	
	/**
	 * Konfigurace uploaderu
	 * @var {Object.<String, Object>}
	 */
	this.config = config;
	
	/**
	 * Kolik bylo nahráno souborů?
	 * @type {number}
	 */
	this.uploaded = 0;
	
	/**
	 * ID nově přidaného souboru.
	 * @type {number}
	 */
	this.fileId = 1;
	
	/**
	 * @type {number}
	 */
	this.addedFiles = 0;
	
	/**
	 * @var {UIFullRenderer}
	 */
	this.renderer = null;
	
	/**
	 * @type {string[]}
	 */
	this.messages = [
		"Maximální počet souborů je %maxFiles%",
		"Maximální velikost souboru je %maxFileSize%"
	];
	
	/**
	 *
	 */
	this.createInstance = function() {
		switch(this.config.uiMode) {
			case 1:
				this.renderer = new UIFullRenderer(this.id, this.config.deleteAction, this.config.renameAction, this.token);
				break;
			case 2:
				this.renderer = new UIMininalRenderer(this.id, this.config.deleteAction, this.config.renameAction, this.token);
				break;
		}
		
		var self = this;
		this.renderer.onDelete = function() {
			self.uploaded -= 1;
			self.addedFiles -= 1;
		};
	};
	
	this.createInstance();
};

FileUploadController.prototype = {
	
	/**
	 * @param {Object.<int, Object>} files
	 * @return {boolean}
	 */
	add: function(files) {
		var readyToSend = false;
		
		var file = files[0];
		var message = "";
		
		if(this.uploaded >= this.config.maxFiles || this.addedFiles >= this.config.maxFiles) {
			message = this.messages[0].replace("%maxFiles%", this.config.maxFiles.toString());
			this.renderer.addRowError(file, this.fileId, message);
			this.fileId++;
		} else if(file["size"] > this.config.maxFileSize) {
			message = this.messages[1].replace("%maxFileSize%", this.config.fileSizeString);
			this.renderer.addRowError(file, this.fileId, message);
			this.fileId++;
		} else {
			this.addedFiles++;
			this.renderer.addRow(file, this.fileId);
			readyToSend = true;
		}
		
		return readyToSend;
	},
	
	/**
	 * @param {Object} data
	 */
	updateProgressAll: function(data) {
		this.renderer.updateProgressAll(data);
	},
	
	/**
	 * @param {Object} data
	 */
	updateFileProgress: function(data) {
		this.renderer.updateFileProgress(data);
	},
	
	/**
	 *
	 */
	stop: function() {
		this.renderer.stop();
	},
	
	/**
	 *
	 */
	start: function() {
		this.renderer.start();
	},
	
	/**
	 * @param {Object} data
	 */
	done: function(data) {
		var success = true;
		
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
					//noinspection JSUnresolvedVariable
					msg = result.errorMessage + ".";
					break;
				case 100:
					//noinspection JSUnresolvedVariable
					msg = "Povolené typy souborů jsou " + result.errorMessage + ".";
					break;
			}
			this.renderer.writeError(msg, id);
			success = false;
		} else {
			this.renderer.fileDone(id);
		}
		
		this.renderer.stopFileProgress(id);
		
		if(success) {
			this.uploaded++;
		} else {
			this.addedFiles -= 1;
		}
	},
	
	/**
	 * @returns {number}
	 */
	getFileId: function() {
		return this.fileId++;
	}
};