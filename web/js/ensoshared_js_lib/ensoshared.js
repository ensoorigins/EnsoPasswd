var EnsoShared = {
		VERSION: "4.0.0",
		
		ENSO_REST_OK: 200,
		ENSO_REST_CREATED: 201,
		ENSO_REST_UPDATE_DELETED: 202,
		ENSO_REST_BAD_REQUEST: 400,
		ENSO_REST_NOT_AUTHORIZED: 401,
		ENSO_REST_FORBIDDEN: 403,
		ENSO_REST_NOT_FOUND: 404,
		ENSO_REST_NOT_ACCEPTABLE: 406,
		ENSO_REST_INTERNAL_SERVER_ERROR: 500,
		
		BLOCK_SIZE: 16,
		HASH_TYPE: "SHA-512",
		NETWORK_ENCODE_SAFE_CHAR: " ",
		
		/**
		 * Função de unpadding, na versão atual da lib são retirados os bytes que foram
		 * acrescentados no final dos dados até preencher o blocksize com informação
		 * (caracter que representa o número de padding necessário)
		 * 
		 * @param string
		 *            data dados a encriptar
		 * @param string
		 *            key chave de encriptação
		 */
		EnsoUnpad: function (text) {
			return text.substring(0, text.length - text.charCodeAt(text.length - 1));
		},
		
		/**
		 * Função de padding, na versão atual da lib são acrescentados bytes no final
		 * dos dado até preencher o blocksize com informação (caracter que representa o
		 * número de padding necessário)
		 * 
		 * @param string
		 *            text dados a encriptar
		 */
		EnsoPad: function (text) {
			var pad_size = this.BLOCK_SIZE - (text.length % this.BLOCK_SIZE);
			for (var i = 0; i < pad_size; i++)
				text += String.fromCharCode(pad_size);
			return text;
		},
		
		toHex : function(str) {
			var hex = '';
			for(var i=0;i<str.length;i++) {
				hex += ''+str.charCodeAt(i).toString(16);
			}
			return hex;
		},
		
		/**
		 * Função de encriptação, na versão atual da lib é estabelecido a utilização de
		 * AES 128
		 * 
		 * @param string
		 *            data dados a encriptar
		 * @param string
		 *            key chave de encriptação
		 */
		encrypt : function(data, key) {
			var keyHex = CryptoJS.enc.Hex.parse(this.toHex(key));
			return this.netUrlEncode( CryptoJS.AES.encrypt(
					this.EnsoPad(data), 
					keyHex, 
					{
						mode : CryptoJS.mode.ECB,
						padding : CryptoJS.pad.NoPadding
					}
				).toString());
		},
		
		/**
		 * Função de desencriptação, na versão atual da lib é estabelecido a utilização
		 * de AES 128
		 * 
		 * @param string
		 *            data dados a desencriptar
		 * @param string
		 *            key chave de encriptação
		 */
		decrypt : function(data, key) {
			var keyHex = CryptoJS.enc.Hex.parse(this.toHex(key));
			var decrypted = CryptoJS.AES.decrypt(
					this.netUrlDecode(data), 
					keyHex, 
					{
						mode : CryptoJS.mode.ECB,
						padding : CryptoJS.pad.NoPadding
					}
				);
			return this.EnsoUnpad(decrypted.toString(CryptoJS.enc.Utf8));
		},

		/**
		 *	Função que retorna o tempo actual em segundos UNIX
		 *
		 *	@return retorna tempo actual em segundos UNIX
		 */
		 now : function() {
		 	return Math.floor( new Date().getTime()/1000);
		 },
		 
		 /**
		 *	Função que retorna o tempo actual em segundos UNIX
		 *
		 *	@return retorna tempo actual em segundos UNIX
		 */
		 nowMillis : function() {
		 	return new Date().getTime();
		 },

		
		/**
		 * Função de codificação para transferência em rede, na versão atual da lib é
		 * estabelecido como recurso a utilização de base64
		 * 
		 * @param string
		 *            data dados a codificar
		 */
		networkEncode : function(data) {
			if(data == null){
				return this.netUrlEncode(btoa(utf8.encode("")));
			}
			else
				return this.netUrlEncode(btoa(utf8.encode(data)));
		},
		
		/**
		 * Função de descodificação de dados codificados para transferência em rede, na
		 * versão atual da lib é estabelecido como recurso a utilização de base64
		 * 
		 * @param string
		 *            data dados a descodificar
		 */
		networkDecode : function(data) {
			return atob(this.netUrlDecode(data));
		},
		
		/**
		 * Funcção que substitui os caracteres que não podem ser tranmitidos no url
		 * e que fazem parte do dicionario do Base64
		 * 
		 * @param String na qual será feito o UrlEncode
		 * @return string com o resultado do UrlEncode
		 */
		netUrlEncode : function(data){
			var find ='\\+';
			var re = new RegExp(find, 'g');
			data = data.replace(re, '-');
			
			var find2 ='/';
			var re2 = new RegExp(find2, 'g');
			data = data.replace(re2, '_');
			
			var find3 ='=';
			var re3 = new RegExp(find3, 'g');
			data = data.replace(re3, ':');
			
			return data;
		},
		
		/**
		 * Funcção que repõe os caracteres que não podem ser tranmitidos no url
		 * e que fazem parte do dicionario do Base64
		 * 
		 * @param string na qual será feito o UrlDecode
		 * @return string com o resultado do UrlDecode
		 */
		netUrlDecode : function(data){
			var find ='-';
			var re = new RegExp(find, 'g');
			data = data.replace(re, '+');
			
			var find2 ='_';
			var re2 = new RegExp(find2, 'g');
			data = data.replace(re2, '/');
			
			var find3 =':';
			var re3 = new RegExp(find3, 'g');
			data = data.replace(re3, '=');
			
			return data;
		},
		
		
		/**
		 * Função de criação de hash a partir de uma string
		 * 
		 * @param string
		 *            data dados a codificar
		 */
		hash : function(data) {
			var shaObj = new jsSHA(data, "ASCII");
			var hash = shaObj.getHash(this.HASH_TYPE, "HEX");
			return hash.toLowerCase();
		},
		

		/**
		 * Função que normaliza uma key com base no block_size utilizado na encriptação/desencriptação
		 * 
		 * @param String a ser normalizada
		 * @return string normalizada ou null caso haja algo incorrecto com a string de input
		 */
		normalizeKey: function(initHash) {
			if (initHash == "") {
				return null;
			} else if (initHash.length == this.BLOCK_SIZE) {
				return initHash;
			} else if (initHash.length < this.BLOCK_SIZE) {
				while (initHash.length < this.BLOCK_SIZE) {
					initHash += initHash;
				}
				return initHash.substring(0, this.BLOCK_SIZE);
			} else if (initHash.length > this.BLOCK_SIZE) {
				return initHash.substring(0, this.BLOCK_SIZE);
			}
			return null;
		},
		
		init : function(){
			
			var files = ["aes.js", "mode-ecb.js", "pad-nopadding.js", "sha.js", "utf8.js"];
			$.each(files, function(index, val){
				
				$.ajax({
					  url: "js/ensoshared_js_lib/" + val,
					  dataType: "script",
					  async: false,
				});
			});
		}
}
	
EnsoShared.init();