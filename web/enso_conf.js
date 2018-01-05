var ensoConf = {
	version: "3.0.0",
	defaultApp: "login",
	viewsPath: "views/",
	afterViewCallBacks: [],

	navigateToPage: function () {
		if (ensoConf.getCurrentPage() != "") {
			pageUrl = ensoConf.viewsPath + ensoConf.getCurrentPage() + ".html";

			$.ajax({
				type: "GET",
				dataType: "html",
				cache: false,
				url: pageUrl,
				success: function (response) {
					$("#main-content").empty().append(response);
					$.each(ensoConf.afterViewCallBacks, function (index, callBack) {
						callBack();
					});
				},
				error: function (response) {
					ensoConf.switchApp(ensoConf.defaultApp);
				}
			});
		}
	},

	goToPage: function (nextPage, args = {}) {
		newHash = "!" + nextPage;

		if (Object.keys(args).length > 0) {
			newHash += '?';
			$.each(args, function (key, val) {
				newHash += key + '=' + val + '&';
			});
		}

		window.location.hash = newHash;
	},

	parseParams: function () {
		var params = {};
		// Meter o url todo em minúsculas para ser case insensitive
		var url_lower = window.location.hash.toLowerCase();
		var paramArray = url_lower.split('?');

		// se for verdade, tem parâmetros depois de '?'
		if (paramArray.length > 1) {
			// começa na posição 1 para excluir a parte que está antes do '?'
			for (var i = 1; i < paramArray.length; i++) {

				var varArray = paramArray[i].split('&');

				for (var j = 0; j < varArray.length; j++) {

					var pair = varArray[j].split("=");
					var k = decodeURIComponent(pair[0]);
					var v = decodeURIComponent(pair[1]);

					params[k] = v;
				}
			}
		}
		return params;
	},

	switchApp: function (appToLoad, params, newTab) {
		var url = appToLoad + ".html";

		if (params != null)
			url += "#?" + params;

		if (newTab != null && newTab)
			window.open(url, "_blank");
		else
			window.location = url;
	},

	loadFirstView: function (pageToLoad) {
		$(document).ready(function () {
			if (window.location.hash == "")
				ensoConf.goToPage(pageToLoad);
			else
				ensoConf.navigateToPage();
		});
	},

	getUrlArgs: function () {
		var hash = window.location.hash;
		var indexView = hash.indexOf("?");
		if (indexView < 0)
			return null;

		var subStr = hash.substring(indexView + 1, hash.length);
		var args = subStr.split('!')[0];
		var argsArray = args.split("&");
		var myArgs = {};
		for (var index = 0; index < argsArray.length; index++) {
			var arg = argsArray[index].split('=');
			myArgs[arg[0]] = arg[1];
		}
		return myArgs;
	},

	getCurrentPage: function () {
		var hash = window.location.hash;
		var indexView = hash.indexOf("!");
		if (indexView < 0)
			return "";

		var subStr = hash.substring(indexView + 1, hash.length);
		var str = subStr.split('?')[0];
		return str;
	},

	ensoLoadScripts: function (scripts, callback) {
		if (scripts.length > 0)
			ensoConf.ensoLoadScriptsRecursive(scripts, 0, callback);
	},

	ensoLoadScriptsRecursive: function (scripts, index, callback) {

		if (index < scripts.length) {
			var path = scripts[index];
			$.getScript(path, function () {
				index++;
				if (index < scripts.length)
					ensoConf.ensoLoadScriptsRecursive(scripts, index, callback);
				else
					if (callback != null)
						callback();
			});
		}
		else {
			if (callback != null)
				callback();
		}
	},

	addAfterViewCallback: function (arg) {
		ensoConf.afterViewCallBacks.push(arg);
	},

	removeAfterViewCallback: function (arg) {
		index = ensoConf.afterViewCallBacks.indexOf(arg);

		if (index > -1)
			ensoConf.afterViewCallBacks.splice(index, 1);
	},

	init: function () {
		$(window).on("hashchange", function () {
			ensoConf.navigateToPage();
		});
	}
};

ensoConf.init();