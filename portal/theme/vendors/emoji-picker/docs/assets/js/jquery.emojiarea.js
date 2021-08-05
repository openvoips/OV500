var EmojiArea =
/******/ (function(modules) { // webpackBootstrap
/******/ 	function hotDisposeChunk(chunkId) {
/******/ 		delete installedChunks[chunkId];
/******/ 	}
/******/ 	var parentHotUpdateCallback = this["webpackHotUpdateEmojiArea"];
/******/ 	this["webpackHotUpdateEmojiArea"] = 
/******/ 	function webpackHotUpdateCallback(chunkId, moreModules) { // eslint-disable-line no-unused-vars
/******/ 		hotAddUpdateChunk(chunkId, moreModules);
/******/ 		if(parentHotUpdateCallback) parentHotUpdateCallback(chunkId, moreModules);
/******/ 	} ;
/******/ 	
/******/ 	function hotDownloadUpdateChunk(chunkId) { // eslint-disable-line no-unused-vars
/******/ 		var head = document.getElementsByTagName("head")[0];
/******/ 		var script = document.createElement("script");
/******/ 		script.type = "text/javascript";
/******/ 		script.charset = "utf-8";
/******/ 		script.src = __webpack_require__.p + "" + chunkId + "." + hotCurrentHash + ".hot-update.js";
/******/ 		;
/******/ 		head.appendChild(script);
/******/ 	}
/******/ 	
/******/ 	function hotDownloadManifest(requestTimeout) { // eslint-disable-line no-unused-vars
/******/ 		requestTimeout = requestTimeout || 10000;
/******/ 		return new Promise(function(resolve, reject) {
/******/ 			if(typeof XMLHttpRequest === "undefined")
/******/ 				return reject(new Error("No browser support"));
/******/ 			try {
/******/ 				var request = new XMLHttpRequest();
/******/ 				var requestPath = __webpack_require__.p + "" + hotCurrentHash + ".hot-update.json";
/******/ 				request.open("GET", requestPath, true);
/******/ 				request.timeout = requestTimeout;
/******/ 				request.send(null);
/******/ 			} catch(err) {
/******/ 				return reject(err);
/******/ 			}
/******/ 			request.onreadystatechange = function() {
/******/ 				if(request.readyState !== 4) return;
/******/ 				if(request.status === 0) {
/******/ 					// timeout
/******/ 					reject(new Error("Manifest request to " + requestPath + " timed out."));
/******/ 				} else if(request.status === 404) {
/******/ 					// no update available
/******/ 					resolve();
/******/ 				} else if(request.status !== 200 && request.status !== 304) {
/******/ 					// other failure
/******/ 					reject(new Error("Manifest request to " + requestPath + " failed."));
/******/ 				} else {
/******/ 					// success
/******/ 					try {
/******/ 						var update = JSON.parse(request.responseText);
/******/ 					} catch(e) {
/******/ 						reject(e);
/******/ 						return;
/******/ 					}
/******/ 					resolve(update);
/******/ 				}
/******/ 			};
/******/ 		});
/******/ 	}
/******/
/******/ 	
/******/ 	
/******/ 	var hotApplyOnUpdate = true;
/******/ 	var hotCurrentHash = "6d46dee1dfa855451ac9"; // eslint-disable-line no-unused-vars
/******/ 	var hotRequestTimeout = 10000;
/******/ 	var hotCurrentModuleData = {};
/******/ 	var hotCurrentChildModule; // eslint-disable-line no-unused-vars
/******/ 	var hotCurrentParents = []; // eslint-disable-line no-unused-vars
/******/ 	var hotCurrentParentsTemp = []; // eslint-disable-line no-unused-vars
/******/ 	
/******/ 	function hotCreateRequire(moduleId) { // eslint-disable-line no-unused-vars
/******/ 		var me = installedModules[moduleId];
/******/ 		if(!me) return __webpack_require__;
/******/ 		var fn = function(request) {
/******/ 			if(me.hot.active) {
/******/ 				if(installedModules[request]) {
/******/ 					if(installedModules[request].parents.indexOf(moduleId) < 0)
/******/ 						installedModules[request].parents.push(moduleId);
/******/ 				} else {
/******/ 					hotCurrentParents = [moduleId];
/******/ 					hotCurrentChildModule = request;
/******/ 				}
/******/ 				if(me.children.indexOf(request) < 0)
/******/ 					me.children.push(request);
/******/ 			} else {
/******/ 				console.warn("[HMR] unexpected require(" + request + ") from disposed module " + moduleId);
/******/ 				hotCurrentParents = [];
/******/ 			}
/******/ 			return __webpack_require__(request);
/******/ 		};
/******/ 		var ObjectFactory = function ObjectFactory(name) {
/******/ 			return {
/******/ 				configurable: true,
/******/ 				enumerable: true,
/******/ 				get: function() {
/******/ 					return __webpack_require__[name];
/******/ 				},
/******/ 				set: function(value) {
/******/ 					__webpack_require__[name] = value;
/******/ 				}
/******/ 			};
/******/ 		};
/******/ 		for(var name in __webpack_require__) {
/******/ 			if(Object.prototype.hasOwnProperty.call(__webpack_require__, name) && name !== "e") {
/******/ 				Object.defineProperty(fn, name, ObjectFactory(name));
/******/ 			}
/******/ 		}
/******/ 		fn.e = function(chunkId) {
/******/ 			if(hotStatus === "ready")
/******/ 				hotSetStatus("prepare");
/******/ 			hotChunksLoading++;
/******/ 			return __webpack_require__.e(chunkId).then(finishChunkLoading, function(err) {
/******/ 				finishChunkLoading();
/******/ 				throw err;
/******/ 			});
/******/ 	
/******/ 			function finishChunkLoading() {
/******/ 				hotChunksLoading--;
/******/ 				if(hotStatus === "prepare") {
/******/ 					if(!hotWaitingFilesMap[chunkId]) {
/******/ 						hotEnsureUpdateChunk(chunkId);
/******/ 					}
/******/ 					if(hotChunksLoading === 0 && hotWaitingFiles === 0) {
/******/ 						hotUpdateDownloaded();
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 		return fn;
/******/ 	}
/******/ 	
/******/ 	function hotCreateModule(moduleId) { // eslint-disable-line no-unused-vars
/******/ 		var hot = {
/******/ 			// private stuff
/******/ 			_acceptedDependencies: {},
/******/ 			_declinedDependencies: {},
/******/ 			_selfAccepted: false,
/******/ 			_selfDeclined: false,
/******/ 			_disposeHandlers: [],
/******/ 			_main: hotCurrentChildModule !== moduleId,
/******/ 	
/******/ 			// Module API
/******/ 			active: true,
/******/ 			accept: function(dep, callback) {
/******/ 				if(typeof dep === "undefined")
/******/ 					hot._selfAccepted = true;
/******/ 				else if(typeof dep === "function")
/******/ 					hot._selfAccepted = dep;
/******/ 				else if(typeof dep === "object")
/******/ 					for(var i = 0; i < dep.length; i++)
/******/ 						hot._acceptedDependencies[dep[i]] = callback || function() {};
/******/ 				else
/******/ 					hot._acceptedDependencies[dep] = callback || function() {};
/******/ 			},
/******/ 			decline: function(dep) {
/******/ 				if(typeof dep === "undefined")
/******/ 					hot._selfDeclined = true;
/******/ 				else if(typeof dep === "object")
/******/ 					for(var i = 0; i < dep.length; i++)
/******/ 						hot._declinedDependencies[dep[i]] = true;
/******/ 				else
/******/ 					hot._declinedDependencies[dep] = true;
/******/ 			},
/******/ 			dispose: function(callback) {
/******/ 				hot._disposeHandlers.push(callback);
/******/ 			},
/******/ 			addDisposeHandler: function(callback) {
/******/ 				hot._disposeHandlers.push(callback);
/******/ 			},
/******/ 			removeDisposeHandler: function(callback) {
/******/ 				var idx = hot._disposeHandlers.indexOf(callback);
/******/ 				if(idx >= 0) hot._disposeHandlers.splice(idx, 1);
/******/ 			},
/******/ 	
/******/ 			// Management API
/******/ 			check: hotCheck,
/******/ 			apply: hotApply,
/******/ 			status: function(l) {
/******/ 				if(!l) return hotStatus;
/******/ 				hotStatusHandlers.push(l);
/******/ 			},
/******/ 			addStatusHandler: function(l) {
/******/ 				hotStatusHandlers.push(l);
/******/ 			},
/******/ 			removeStatusHandler: function(l) {
/******/ 				var idx = hotStatusHandlers.indexOf(l);
/******/ 				if(idx >= 0) hotStatusHandlers.splice(idx, 1);
/******/ 			},
/******/ 	
/******/ 			//inherit from previous dispose call
/******/ 			data: hotCurrentModuleData[moduleId]
/******/ 		};
/******/ 		hotCurrentChildModule = undefined;
/******/ 		return hot;
/******/ 	}
/******/ 	
/******/ 	var hotStatusHandlers = [];
/******/ 	var hotStatus = "idle";
/******/ 	
/******/ 	function hotSetStatus(newStatus) {
/******/ 		hotStatus = newStatus;
/******/ 		for(var i = 0; i < hotStatusHandlers.length; i++)
/******/ 			hotStatusHandlers[i].call(null, newStatus);
/******/ 	}
/******/ 	
/******/ 	// while downloading
/******/ 	var hotWaitingFiles = 0;
/******/ 	var hotChunksLoading = 0;
/******/ 	var hotWaitingFilesMap = {};
/******/ 	var hotRequestedFilesMap = {};
/******/ 	var hotAvailableFilesMap = {};
/******/ 	var hotDeferred;
/******/ 	
/******/ 	// The update info
/******/ 	var hotUpdate, hotUpdateNewHash;
/******/ 	
/******/ 	function toModuleId(id) {
/******/ 		var isNumber = (+id) + "" === id;
/******/ 		return isNumber ? +id : id;
/******/ 	}
/******/ 	
/******/ 	function hotCheck(apply) {
/******/ 		if(hotStatus !== "idle") throw new Error("check() is only allowed in idle status");
/******/ 		hotApplyOnUpdate = apply;
/******/ 		hotSetStatus("check");
/******/ 		return hotDownloadManifest(hotRequestTimeout).then(function(update) {
/******/ 			if(!update) {
/******/ 				hotSetStatus("idle");
/******/ 				return null;
/******/ 			}
/******/ 			hotRequestedFilesMap = {};
/******/ 			hotWaitingFilesMap = {};
/******/ 			hotAvailableFilesMap = update.c;
/******/ 			hotUpdateNewHash = update.h;
/******/ 	
/******/ 			hotSetStatus("prepare");
/******/ 			var promise = new Promise(function(resolve, reject) {
/******/ 				hotDeferred = {
/******/ 					resolve: resolve,
/******/ 					reject: reject
/******/ 				};
/******/ 			});
/******/ 			hotUpdate = {};
/******/ 			var chunkId = 0;
/******/ 			{ // eslint-disable-line no-lone-blocks
/******/ 				/*globals chunkId */
/******/ 				hotEnsureUpdateChunk(chunkId);
/******/ 			}
/******/ 			if(hotStatus === "prepare" && hotChunksLoading === 0 && hotWaitingFiles === 0) {
/******/ 				hotUpdateDownloaded();
/******/ 			}
/******/ 			return promise;
/******/ 		});
/******/ 	}
/******/ 	
/******/ 	function hotAddUpdateChunk(chunkId, moreModules) { // eslint-disable-line no-unused-vars
/******/ 		if(!hotAvailableFilesMap[chunkId] || !hotRequestedFilesMap[chunkId])
/******/ 			return;
/******/ 		hotRequestedFilesMap[chunkId] = false;
/******/ 		for(var moduleId in moreModules) {
/******/ 			if(Object.prototype.hasOwnProperty.call(moreModules, moduleId)) {
/******/ 				hotUpdate[moduleId] = moreModules[moduleId];
/******/ 			}
/******/ 		}
/******/ 		if(--hotWaitingFiles === 0 && hotChunksLoading === 0) {
/******/ 			hotUpdateDownloaded();
/******/ 		}
/******/ 	}
/******/ 	
/******/ 	function hotEnsureUpdateChunk(chunkId) {
/******/ 		if(!hotAvailableFilesMap[chunkId]) {
/******/ 			hotWaitingFilesMap[chunkId] = true;
/******/ 		} else {
/******/ 			hotRequestedFilesMap[chunkId] = true;
/******/ 			hotWaitingFiles++;
/******/ 			hotDownloadUpdateChunk(chunkId);
/******/ 		}
/******/ 	}
/******/ 	
/******/ 	function hotUpdateDownloaded() {
/******/ 		hotSetStatus("ready");
/******/ 		var deferred = hotDeferred;
/******/ 		hotDeferred = null;
/******/ 		if(!deferred) return;
/******/ 		if(hotApplyOnUpdate) {
/******/ 			// Wrap deferred object in Promise to mark it as a well-handled Promise to
/******/ 			// avoid triggering uncaught exception warning in Chrome.
/******/ 			// See https://bugs.chromium.org/p/chromium/issues/detail?id=465666
/******/ 			Promise.resolve().then(function() {
/******/ 				return hotApply(hotApplyOnUpdate);
/******/ 			}).then(
/******/ 				function(result) {
/******/ 					deferred.resolve(result);
/******/ 				},
/******/ 				function(err) {
/******/ 					deferred.reject(err);
/******/ 				}
/******/ 			);
/******/ 		} else {
/******/ 			var outdatedModules = [];
/******/ 			for(var id in hotUpdate) {
/******/ 				if(Object.prototype.hasOwnProperty.call(hotUpdate, id)) {
/******/ 					outdatedModules.push(toModuleId(id));
/******/ 				}
/******/ 			}
/******/ 			deferred.resolve(outdatedModules);
/******/ 		}
/******/ 	}
/******/ 	
/******/ 	function hotApply(options) {
/******/ 		if(hotStatus !== "ready") throw new Error("apply() is only allowed in ready status");
/******/ 		options = options || {};
/******/ 	
/******/ 		var cb;
/******/ 		var i;
/******/ 		var j;
/******/ 		var module;
/******/ 		var moduleId;
/******/ 	
/******/ 		function getAffectedStuff(updateModuleId) {
/******/ 			var outdatedModules = [updateModuleId];
/******/ 			var outdatedDependencies = {};
/******/ 	
/******/ 			var queue = outdatedModules.slice().map(function(id) {
/******/ 				return {
/******/ 					chain: [id],
/******/ 					id: id
/******/ 				};
/******/ 			});
/******/ 			while(queue.length > 0) {
/******/ 				var queueItem = queue.pop();
/******/ 				var moduleId = queueItem.id;
/******/ 				var chain = queueItem.chain;
/******/ 				module = installedModules[moduleId];
/******/ 				if(!module || module.hot._selfAccepted)
/******/ 					continue;
/******/ 				if(module.hot._selfDeclined) {
/******/ 					return {
/******/ 						type: "self-declined",
/******/ 						chain: chain,
/******/ 						moduleId: moduleId
/******/ 					};
/******/ 				}
/******/ 				if(module.hot._main) {
/******/ 					return {
/******/ 						type: "unaccepted",
/******/ 						chain: chain,
/******/ 						moduleId: moduleId
/******/ 					};
/******/ 				}
/******/ 				for(var i = 0; i < module.parents.length; i++) {
/******/ 					var parentId = module.parents[i];
/******/ 					var parent = installedModules[parentId];
/******/ 					if(!parent) continue;
/******/ 					if(parent.hot._declinedDependencies[moduleId]) {
/******/ 						return {
/******/ 							type: "declined",
/******/ 							chain: chain.concat([parentId]),
/******/ 							moduleId: moduleId,
/******/ 							parentId: parentId
/******/ 						};
/******/ 					}
/******/ 					if(outdatedModules.indexOf(parentId) >= 0) continue;
/******/ 					if(parent.hot._acceptedDependencies[moduleId]) {
/******/ 						if(!outdatedDependencies[parentId])
/******/ 							outdatedDependencies[parentId] = [];
/******/ 						addAllToSet(outdatedDependencies[parentId], [moduleId]);
/******/ 						continue;
/******/ 					}
/******/ 					delete outdatedDependencies[parentId];
/******/ 					outdatedModules.push(parentId);
/******/ 					queue.push({
/******/ 						chain: chain.concat([parentId]),
/******/ 						id: parentId
/******/ 					});
/******/ 				}
/******/ 			}
/******/ 	
/******/ 			return {
/******/ 				type: "accepted",
/******/ 				moduleId: updateModuleId,
/******/ 				outdatedModules: outdatedModules,
/******/ 				outdatedDependencies: outdatedDependencies
/******/ 			};
/******/ 		}
/******/ 	
/******/ 		function addAllToSet(a, b) {
/******/ 			for(var i = 0; i < b.length; i++) {
/******/ 				var item = b[i];
/******/ 				if(a.indexOf(item) < 0)
/******/ 					a.push(item);
/******/ 			}
/******/ 		}
/******/ 	
/******/ 		// at begin all updates modules are outdated
/******/ 		// the "outdated" status can propagate to parents if they don't accept the children
/******/ 		var outdatedDependencies = {};
/******/ 		var outdatedModules = [];
/******/ 		var appliedUpdate = {};
/******/ 	
/******/ 		var warnUnexpectedRequire = function warnUnexpectedRequire() {
/******/ 			console.warn("[HMR] unexpected require(" + result.moduleId + ") to disposed module");
/******/ 		};
/******/ 	
/******/ 		for(var id in hotUpdate) {
/******/ 			if(Object.prototype.hasOwnProperty.call(hotUpdate, id)) {
/******/ 				moduleId = toModuleId(id);
/******/ 				var result;
/******/ 				if(hotUpdate[id]) {
/******/ 					result = getAffectedStuff(moduleId);
/******/ 				} else {
/******/ 					result = {
/******/ 						type: "disposed",
/******/ 						moduleId: id
/******/ 					};
/******/ 				}
/******/ 				var abortError = false;
/******/ 				var doApply = false;
/******/ 				var doDispose = false;
/******/ 				var chainInfo = "";
/******/ 				if(result.chain) {
/******/ 					chainInfo = "\nUpdate propagation: " + result.chain.join(" -> ");
/******/ 				}
/******/ 				switch(result.type) {
/******/ 					case "self-declined":
/******/ 						if(options.onDeclined)
/******/ 							options.onDeclined(result);
/******/ 						if(!options.ignoreDeclined)
/******/ 							abortError = new Error("Aborted because of self decline: " + result.moduleId + chainInfo);
/******/ 						break;
/******/ 					case "declined":
/******/ 						if(options.onDeclined)
/******/ 							options.onDeclined(result);
/******/ 						if(!options.ignoreDeclined)
/******/ 							abortError = new Error("Aborted because of declined dependency: " + result.moduleId + " in " + result.parentId + chainInfo);
/******/ 						break;
/******/ 					case "unaccepted":
/******/ 						if(options.onUnaccepted)
/******/ 							options.onUnaccepted(result);
/******/ 						if(!options.ignoreUnaccepted)
/******/ 							abortError = new Error("Aborted because " + moduleId + " is not accepted" + chainInfo);
/******/ 						break;
/******/ 					case "accepted":
/******/ 						if(options.onAccepted)
/******/ 							options.onAccepted(result);
/******/ 						doApply = true;
/******/ 						break;
/******/ 					case "disposed":
/******/ 						if(options.onDisposed)
/******/ 							options.onDisposed(result);
/******/ 						doDispose = true;
/******/ 						break;
/******/ 					default:
/******/ 						throw new Error("Unexception type " + result.type);
/******/ 				}
/******/ 				if(abortError) {
/******/ 					hotSetStatus("abort");
/******/ 					return Promise.reject(abortError);
/******/ 				}
/******/ 				if(doApply) {
/******/ 					appliedUpdate[moduleId] = hotUpdate[moduleId];
/******/ 					addAllToSet(outdatedModules, result.outdatedModules);
/******/ 					for(moduleId in result.outdatedDependencies) {
/******/ 						if(Object.prototype.hasOwnProperty.call(result.outdatedDependencies, moduleId)) {
/******/ 							if(!outdatedDependencies[moduleId])
/******/ 								outdatedDependencies[moduleId] = [];
/******/ 							addAllToSet(outdatedDependencies[moduleId], result.outdatedDependencies[moduleId]);
/******/ 						}
/******/ 					}
/******/ 				}
/******/ 				if(doDispose) {
/******/ 					addAllToSet(outdatedModules, [result.moduleId]);
/******/ 					appliedUpdate[moduleId] = warnUnexpectedRequire;
/******/ 				}
/******/ 			}
/******/ 		}
/******/ 	
/******/ 		// Store self accepted outdated modules to require them later by the module system
/******/ 		var outdatedSelfAcceptedModules = [];
/******/ 		for(i = 0; i < outdatedModules.length; i++) {
/******/ 			moduleId = outdatedModules[i];
/******/ 			if(installedModules[moduleId] && installedModules[moduleId].hot._selfAccepted)
/******/ 				outdatedSelfAcceptedModules.push({
/******/ 					module: moduleId,
/******/ 					errorHandler: installedModules[moduleId].hot._selfAccepted
/******/ 				});
/******/ 		}
/******/ 	
/******/ 		// Now in "dispose" phase
/******/ 		hotSetStatus("dispose");
/******/ 		Object.keys(hotAvailableFilesMap).forEach(function(chunkId) {
/******/ 			if(hotAvailableFilesMap[chunkId] === false) {
/******/ 				hotDisposeChunk(chunkId);
/******/ 			}
/******/ 		});
/******/ 	
/******/ 		var idx;
/******/ 		var queue = outdatedModules.slice();
/******/ 		while(queue.length > 0) {
/******/ 			moduleId = queue.pop();
/******/ 			module = installedModules[moduleId];
/******/ 			if(!module) continue;
/******/ 	
/******/ 			var data = {};
/******/ 	
/******/ 			// Call dispose handlers
/******/ 			var disposeHandlers = module.hot._disposeHandlers;
/******/ 			for(j = 0; j < disposeHandlers.length; j++) {
/******/ 				cb = disposeHandlers[j];
/******/ 				cb(data);
/******/ 			}
/******/ 			hotCurrentModuleData[moduleId] = data;
/******/ 	
/******/ 			// disable module (this disables requires from this module)
/******/ 			module.hot.active = false;
/******/ 	
/******/ 			// remove module from cache
/******/ 			delete installedModules[moduleId];
/******/ 	
/******/ 			// when disposing there is no need to call dispose handler
/******/ 			delete outdatedDependencies[moduleId];
/******/ 	
/******/ 			// remove "parents" references from all children
/******/ 			for(j = 0; j < module.children.length; j++) {
/******/ 				var child = installedModules[module.children[j]];
/******/ 				if(!child) continue;
/******/ 				idx = child.parents.indexOf(moduleId);
/******/ 				if(idx >= 0) {
/******/ 					child.parents.splice(idx, 1);
/******/ 				}
/******/ 			}
/******/ 		}
/******/ 	
/******/ 		// remove outdated dependency from module children
/******/ 		var dependency;
/******/ 		var moduleOutdatedDependencies;
/******/ 		for(moduleId in outdatedDependencies) {
/******/ 			if(Object.prototype.hasOwnProperty.call(outdatedDependencies, moduleId)) {
/******/ 				module = installedModules[moduleId];
/******/ 				if(module) {
/******/ 					moduleOutdatedDependencies = outdatedDependencies[moduleId];
/******/ 					for(j = 0; j < moduleOutdatedDependencies.length; j++) {
/******/ 						dependency = moduleOutdatedDependencies[j];
/******/ 						idx = module.children.indexOf(dependency);
/******/ 						if(idx >= 0) module.children.splice(idx, 1);
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		}
/******/ 	
/******/ 		// Not in "apply" phase
/******/ 		hotSetStatus("apply");
/******/ 	
/******/ 		hotCurrentHash = hotUpdateNewHash;
/******/ 	
/******/ 		// insert new code
/******/ 		for(moduleId in appliedUpdate) {
/******/ 			if(Object.prototype.hasOwnProperty.call(appliedUpdate, moduleId)) {
/******/ 				modules[moduleId] = appliedUpdate[moduleId];
/******/ 			}
/******/ 		}
/******/ 	
/******/ 		// call accept handlers
/******/ 		var error = null;
/******/ 		for(moduleId in outdatedDependencies) {
/******/ 			if(Object.prototype.hasOwnProperty.call(outdatedDependencies, moduleId)) {
/******/ 				module = installedModules[moduleId];
/******/ 				if(module) {
/******/ 					moduleOutdatedDependencies = outdatedDependencies[moduleId];
/******/ 					var callbacks = [];
/******/ 					for(i = 0; i < moduleOutdatedDependencies.length; i++) {
/******/ 						dependency = moduleOutdatedDependencies[i];
/******/ 						cb = module.hot._acceptedDependencies[dependency];
/******/ 						if(cb) {
/******/ 							if(callbacks.indexOf(cb) >= 0) continue;
/******/ 							callbacks.push(cb);
/******/ 						}
/******/ 					}
/******/ 					for(i = 0; i < callbacks.length; i++) {
/******/ 						cb = callbacks[i];
/******/ 						try {
/******/ 							cb(moduleOutdatedDependencies);
/******/ 						} catch(err) {
/******/ 							if(options.onErrored) {
/******/ 								options.onErrored({
/******/ 									type: "accept-errored",
/******/ 									moduleId: moduleId,
/******/ 									dependencyId: moduleOutdatedDependencies[i],
/******/ 									error: err
/******/ 								});
/******/ 							}
/******/ 							if(!options.ignoreErrored) {
/******/ 								if(!error)
/******/ 									error = err;
/******/ 							}
/******/ 						}
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		}
/******/ 	
/******/ 		// Load self accepted modules
/******/ 		for(i = 0; i < outdatedSelfAcceptedModules.length; i++) {
/******/ 			var item = outdatedSelfAcceptedModules[i];
/******/ 			moduleId = item.module;
/******/ 			hotCurrentParents = [moduleId];
/******/ 			try {
/******/ 				__webpack_require__(moduleId);
/******/ 			} catch(err) {
/******/ 				if(typeof item.errorHandler === "function") {
/******/ 					try {
/******/ 						item.errorHandler(err);
/******/ 					} catch(err2) {
/******/ 						if(options.onErrored) {
/******/ 							options.onErrored({
/******/ 								type: "self-accept-error-handler-errored",
/******/ 								moduleId: moduleId,
/******/ 								error: err2,
/******/ 								orginalError: err, // TODO remove in webpack 4
/******/ 								originalError: err
/******/ 							});
/******/ 						}
/******/ 						if(!options.ignoreErrored) {
/******/ 							if(!error)
/******/ 								error = err2;
/******/ 						}
/******/ 						if(!error)
/******/ 							error = err;
/******/ 					}
/******/ 				} else {
/******/ 					if(options.onErrored) {
/******/ 						options.onErrored({
/******/ 							type: "self-accept-errored",
/******/ 							moduleId: moduleId,
/******/ 							error: err
/******/ 						});
/******/ 					}
/******/ 					if(!options.ignoreErrored) {
/******/ 						if(!error)
/******/ 							error = err;
/******/ 					}
/******/ 				}
/******/ 			}
/******/ 		}
/******/ 	
/******/ 		// handle errors in accept handlers and self accepted module load
/******/ 		if(error) {
/******/ 			hotSetStatus("fail");
/******/ 			return Promise.reject(error);
/******/ 		}
/******/ 	
/******/ 		hotSetStatus("idle");
/******/ 		return new Promise(function(resolve) {
/******/ 			resolve(outdatedModules);
/******/ 		});
/******/ 	}
/******/
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {},
/******/ 			hot: hotCreateModule(moduleId),
/******/ 			parents: (hotCurrentParentsTemp = hotCurrentParents, hotCurrentParents = [], hotCurrentParentsTemp),
/******/ 			children: []
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, hotCreateRequire(moduleId));
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// __webpack_hash__
/******/ 	__webpack_require__.h = function() { return hotCurrentHash; };
/******/
/******/ 	// Load entry module and return exports
/******/ 	return hotCreateRequire(3)(__webpack_require__.s = 3);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function($) {

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /**
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      * The EmojiUtil contains all functionality for handling emoji data and groups, but no html specific stuff.
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      *
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      * @author Wolfgang Stöttinger
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      */

var _EmojiData = __webpack_require__(6);

var _EmojiData2 = _interopRequireDefault(_EmojiData);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var EmojiUtil = function () {
  function EmojiUtil() {
    _classCallCheck(this, EmojiUtil);
  }

  /**
   *
   */


  _createClass(EmojiUtil, null, [{
    key: 'initialize',
    value: function initialize() {
      EmojiUtil.aliases = {};
      EmojiUtil.unicodes = {};

      var dataKeys = Object.keys(EmojiUtil.data);
      for (var e = 0; e < dataKeys.length; e++) {
        var key = dataKeys[e];
        var emojiData = EmojiUtil.data[key];
        if (emojiData) {
          var code = emojiData[EmojiUtil.EMOJI_UNICODE][0];

          EmojiUtil.aliases[emojiData[EmojiUtil.EMOJI_ALIASES]] = key;
          EmojiUtil.unicodes[code] = key;
        }
      }
    }
  }, {
    key: 'checkAlias',
    value: function checkAlias(alias) {
      return EmojiUtil.aliases.hasOwnProperty(alias);
    }
  }, {
    key: 'checkUnicode',
    value: function checkUnicode(alias) {
      return EmojiUtil.unicodes.hasOwnProperty(alias);
    }
  }, {
    key: 'checkAscii',
    value: function checkAscii(ascii) {
      return EmojiUtil.ascii.hasOwnProperty(ascii);
    }

    /**
     * @param alias
     * @param groupData if true returns an array including groupId, Col# and Row# of the Emoji
     * @returns {*}
     */

  }, {
    key: 'dataFromAlias',
    value: function dataFromAlias(alias) {
      var groupData = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

      var key = EmojiUtil.aliases[alias];
      var data = EmojiUtil.data[key];
      if (!groupData || data[2]) // if group is set
        return data;

      for (var g = 0; g < EmojiUtil.groups.length; g++) {
        var group = EmojiUtil.groups[g];
        var d = group.dimensions;
        var pos = $.inArray(key, group.items);
        if (pos >= 0) {
          data[2] = g; // group
          data[3] = pos % d[0]; // column
          data[4] = pos / d[0] | 0; // row
          data[5] = d; // sprite dimensions
          return data;
        }
      }
      return data;
    }

    /**
     *
     * @param alias
     * @returns {*}
     */

  }, {
    key: 'unicodeFromAlias',
    value: function unicodeFromAlias(alias) {
      if (alias) {
        var key = EmojiUtil.aliases[alias];
        var emojiData = EmojiUtil.data[key];
        if (emojiData && emojiData[EmojiUtil.EMOJI_UNICODE]) return emojiData[EmojiUtil.EMOJI_UNICODE][0];
      }
      return null;
    }
  }, {
    key: 'unicodeFromAscii',
    value: function unicodeFromAscii(ascii) {
      return EmojiUtil.unicodeFromAlias(EmojiUtil.aliasFromAscii(ascii));
    }
  }, {
    key: 'aliasFromUnicode',
    value: function aliasFromUnicode(unicode) {
      if (unicode) {
        var key = EmojiUtil.unicodes[unicode];
        var emojiData = EmojiUtil.data[key];
        if (emojiData && emojiData[EmojiUtil.EMOJI_ALIASES]) return emojiData[EmojiUtil.EMOJI_ALIASES];
      }
      return null;
    }
  }, {
    key: 'aliasFromAscii',
    value: function aliasFromAscii(ascii) {
      return EmojiUtil.ascii[ascii] || null;
    }
  }]);

  return EmojiUtil;
}();

exports.default = EmojiUtil;


EmojiUtil.data = _EmojiData2.default.data;
EmojiUtil.groups = _EmojiData2.default.groups;
EmojiUtil.ascii = _EmojiData2.default.ascii;

EmojiUtil.EMOJI_UNICODE = 0;
EmojiUtil.EMOJI_ALIASES = 1;

EmojiUtil.initialize();
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(0)))

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /**
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      * This EmojiArea is rewritten from ground up an based on the code from Brian Reavis <brian@diy.org>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      *
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      * @author Wolfgang Stöttinger
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      */


var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _EmojiPicker = __webpack_require__(7);

var _EmojiPicker2 = _interopRequireDefault(_EmojiPicker);

var _EmojiUtil = __webpack_require__(1);

var _EmojiUtil2 = _interopRequireDefault(_EmojiUtil);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var EmojiArea = function () {
  function EmojiArea(emojiArea, options) {
    _classCallCheck(this, EmojiArea);

    this.o = _jquery2.default.extend({}, EmojiArea.DEFAULTS, options);
    this.$ea = (0, _jquery2.default)(emojiArea);
    this.$ti = this.$ea.find(options.inputSelector);
    this.$b = this.$ea.find(options.buttonSelector).on('click', this.togglePicker.bind(this));

    if (options.type !== 'unicode') {
      this.$ti.hide();
      this.$e = (0, _jquery2.default)('<div>').addClass('emoji-editor').attr('tabIndex', 0).attr('contentEditable', true).text(this.$ti.text()).on(options.inputEvent, this.onInput.bind(this)).on(options.keyEvent, this.onKey.bind(this)).on('copy', options.textClipboard ? this.clipboardCopy.bind(this) : function () {
        return true;
      }).on('paste', options.textClipboard ? this.clipboardPaste.bind(this) : function () {
        return true;
      }).appendTo(this.$ea);

      this.processContent();

      this.htmlSel = document.createRange();
      this.htmlSel.setStartBefore(this.$e[0].lastChild);
      this.htmlSel.collapse(true);
    } else {
      this.$e = this.$ti;
      this.$ti.on(options.inputEvent, this.processTextContent.bind(this));

      this.processTextContent();

      var v = this.$ti.val();
      this.$ti[0].setSelectionRange(v.length, v.length);
      this.textSel = { start: v.length, end: v.length };
    }

    this.$e.focusout(this.saveSelection.bind(this)).focus(this.restoreSelection.bind(this));
    // $(document.body).on('mousedown', this.saveSelection.bind(this));
  }

  //
  // Clipboard handling
  //

  // noinspection JSMethodCanBeStatic


  _createClass(EmojiArea, [{
    key: 'clipboardCopy',
    value: function clipboardCopy(e) {
      // only allow plain text copy:
      var cbd = e.originalEvent.clipboardData || window.clipboardData;
      var content = window.getSelection().toString();
      window.clipboardData ? cbd.setData('text', content) : cbd.setData('text/plain', content);
      e.preventDefault();
    }
  }, {
    key: 'clipboardPaste',
    value: function clipboardPaste(e) {
      // only allow to paste plain text
      var cbd = e.originalEvent.clipboardData || window.clipboardData;
      var content = window.clipboardData ? cbd.getData('text') : cbd.getData('text/plain');

      if (!document.execCommand('insertText', false, content)) {
        this.saveSelection();
        var range = this.htmlSel;
        var insert = document.createTextNode(content);
        range.deleteContents();
        range.insertNode(insert);
        range.setStartAfter(insert);
        range.setEndAfter(insert);
        setTimeout(this.onInput.bind(this), 0);
      }
      e.preventDefault();
    }

    //
    // Selection handling
    //

  }, {
    key: 'saveSelection',
    value: function saveSelection() {
      var e = this.$e[0];
      // for unicode mode, the textarea itself:
      if (this.$e === this.$ti && e.selectionStart && e.selectionEnd) {
        this.textSel = { start: e.selectionStart, end: e.selectionEnd };
      } else {
        var sel = window.getSelection();
        if (sel.focusNode && (sel.focusNode === e || sel.focusNode.parentNode === e)) {
          this.htmlSel = sel.getRangeAt(0);
        }
      }
    }
  }, {
    key: 'restoreSelection',
    value: function restoreSelection(event) {
      var hSel = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.htmlSel;
      var tSel = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : this.textSel;

      if (hSel) {
        var s = window.getSelection();
        s.removeAllRanges();
        s.addRange(hSel);
      } else if (tSel) {
        if (!event || event.type !== 'focus') {
          this.$ti[0].focus();
        }
        this.$ti[0].setSelectionRange(tSel.start, tSel.end);
      }
    }
  }, {
    key: 'replaceSelection',
    value: function replaceSelection(content) {
      var hSel = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.htmlSel;
      var tSel = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : this.textSel;

      this.restoreSelection(null, hSel, tSel);
      if (hSel) {
        if (!document.execCommand('insertHTML', false, content)) {
          // let insert = $.parseHTML(content)[0];
          // insert = document.importNode(insert, true); // this is necessary for IE
          hSel.deleteContents();
          var insert = hSel.createContextualFragment(content);
          hSel.insertNode(insert);
          hSel.collapse(false);
          // hSel.setStartAfter(insert.lastChild);
          // hSel.setEndAfter(insert.lastChild);
          return insert;
        }
        return true;
      } else if (tSel) {
        if (!document.execCommand('insertText', false, content)) {
          var val = this.$e.val();
          this.$e.val(val.slice(0, tSel.start) + content + val.slice(tSel.end));
          tSel.start = tSel.end = tSel.start + content.length;
          this.$ti[0].setSelectionRange(tSel.start, tSel.end);
        }
        return true;
      }
      return false;
    }
  }, {
    key: 'onInput',
    value: function onInput(event) {
      if (!event || event.originalEvent && event.originalEvent.inputType !== 'historyUndo') {
        this.processContent();
        this.updateInput();
      }
    }
  }, {
    key: 'onKey',
    value: function onKey(e) {
      if (e.originalEvent.keyCode === 13) {
        // catch enter and just insert <br>
        this.saveSelection();
        this.replaceSelection('<br>');

        if (this.$e[0].lastChild.nodeName !== 'BR') {
          this.$e.append('<br>'); // this is necessary to render correctly.
        }

        e.stopPropagation();
        return false;
      }
    }
  }, {
    key: 'updateInput',
    value: function updateInput() {
      this.$ti.val(this.$e[0].innerText || this.$e[0].textContent);
      this.$ti.trigger(this.o.inputEvent);
    }
  }, {
    key: 'processTextContent',
    value: function processTextContent(event) {
      if (!event || event.originalEvent && event.originalEvent.inputType !== 'historyUndo') {
        var val = this.$ti.val();
        var parsed = this.replaceAscii(val);
        parsed = this.replaceAliases(parsed);
        if (parsed !== val) {
          var sel = parsed.length - (val.length - this.$ti[0].selectionEnd);
          this.$ti.val(parsed);
          this.$ti[0].setSelectionRange(sel, sel);
          this.textSel = { start: sel, end: sel };
          this.$ti.focus().trigger(this.o.inputEvent);
        }
      }
    }
  }, {
    key: 'processContent',
    value: function processContent() {
      this.saveSelection();
      this._processElement(this.$e);
      if (this.$e[0].lastChild.nodeName !== 'BR') {
        this.$e.append('<br>'); // this is necessary to render correctly.
      }
    }
  }, {
    key: '_processElement',
    value: function _processElement() {
      var _this = this;

      var element = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.$e;

      // this is a bit more complex becaue
      //  a) only text nodes should be replaced
      //  b) the cursor position should be kept after an alias is replaced

      element.contents().each(function (i, e) {
        if (e.nodeType === 1 || e.nodeType === 11) {
          // element or document fragment
          var $e = (0, _jquery2.default)(e);
          if (!$e.is('.emoji')) // skip emojis
            {
              _this._processElement($e);
            }
        } else if (e.nodeType === 3) {
          // text node
          // replace unicodes
          var parsed = e.nodeValue;

          if (_this.o.type !== 'unicode') {
            //convert existing unicodes
            parsed = _this.replaceUnicodes(parsed);
          }

          parsed = _this.replaceAscii(parsed);
          parsed = _this.replaceAliases(parsed);

          if (parsed !== e.nodeValue) {
            var isSelected = _this.htmlSel && _this.htmlSel.endContainer === e;
            var range = isSelected ? _this.htmlSel : document.createRange();
            var carret = _this.htmlSel ? e.nodeValue.length - _this.htmlSel.endOffset : 0;
            var next = e.nextSibling;
            range.selectNode(e);
            _this.replaceSelection(parsed, range, null);
            if (isSelected) {
              if (next.previousSibling) {
                var inserted = next.previousSibling;
                range.setStart(inserted, inserted.length - carret);
                range.setEnd(inserted, inserted.length - carret);
                //this.htmlSel.setStartAfter(content[content.length - 1]);
                //this.htmlSel.collapse(false);
              } else {
                range.setStartBefore(_this.$e[0].lastChild);
                range.setEndBefore(_this.$e[0].lastChild);
              }
            }
          }
        }
      });
    }
  }, {
    key: 'replaceUnicodes',
    value: function replaceUnicodes(text) {
      var _this2 = this;

      return text.replace(this.o.unicodeRegex, function (match, unicode) {
        return _EmojiUtil2.default.checkUnicode(unicode) ? EmojiArea.createEmoji(null, _this2.o, unicode) : unicode;
      });
    }
  }, {
    key: 'replaceAscii',
    value: function replaceAscii(text) {
      var _this3 = this;

      return text.replace(this.o.asciiRegex, function (match, ascii) {
        if (_EmojiUtil2.default.checkAscii(ascii)) {
          var alias = _EmojiUtil2.default.aliasFromAscii(ascii);
          if (alias) {
            return EmojiArea.createEmoji(alias, _this3.o);
          }
        }
        return ascii + ' ';
      });
    }
  }, {
    key: 'replaceAliases',
    value: function replaceAliases(text) {
      var _this4 = this;

      return text.replace(this.o.aliasRegex, function (match, alias) {
        return _EmojiUtil2.default.checkAlias(alias) ? EmojiArea.createEmoji(alias, _this4.o) : ':' + alias + ':';
      });
    }
  }, {
    key: 'togglePicker',
    value: function togglePicker() {
      var delegate = this.picker || _EmojiPicker2.default;
      if (!delegate.isVisible()) {
        this.picker = delegate.show(this.insert.bind(this), this.$b, this.o);
      } else {
        delegate.hide();
      }
      return false;
    }
  }, {
    key: 'insert',
    value: function insert(alias) {
      var content = EmojiArea.createEmoji(alias, this.o);
      if (!this.replaceSelection(content)) {
        this.$e.append(content).focus().trigger(this.o.inputEvent);
      }
    }
  }], [{
    key: 'createEmoji',
    value: function createEmoji(alias) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : EmojiArea.DEFAULTS;
      var unicode = arguments[2];

      if (!alias && !unicode) {
        return;
      }
      alias = alias || _EmojiUtil2.default.aliasFromUnicode(unicode);
      unicode = unicode || _EmojiUtil2.default.unicodeFromAlias(alias);
      return unicode ? options.type === 'unicode' ? unicode : options.type === 'css' ? EmojiArea.generateEmojiTag(unicode, alias) : EmojiArea.generateEmojiImg(unicode, alias, options) : alias;
    }
  }, {
    key: 'generateEmojiTag',
    value: function generateEmojiTag(unicode, alias) {
      return '<i class="emoji emoji-' + alias + '" contenteditable="false">' + unicode + '</i>';
    }
  }, {
    key: 'generateEmojiImg',
    value: function generateEmojiImg(unicode, alias) {
      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : EmojiArea.DEFAULTS;

      var data = _EmojiUtil2.default.dataFromAlias(alias, true);
      var group = _EmojiUtil2.default.groups[data[2]];
      var dimensions = data[5];
      var iconSize = options.iconSize || 25;

      var style = 'background: url(\'' + options.assetPath + '/' + group.sprite + '\') ' + -iconSize * data[3] + 'px ' // data[3] = column
      + -iconSize * data[4] + 'px no-repeat; ' // data[4] = row
      + 'background-size: ' + dimensions[0] * iconSize + 'px ' + dimensions[1] * iconSize + 'px;'; // position

      return '<i class="emoji emoji-' + alias + ' emoji-image" contenteditable="false"><img src="' + options.assetPath + '/blank.gif" style="' + style + '" alt="' + unicode + '" contenteditable="false"/>' + unicode + '</i>';
    }
  }]);

  return EmojiArea;
}();

exports.default = EmojiArea;


EmojiArea.DEFAULTS = {
  aliasRegex: /:([a-z0-9_]+?):/g,
  asciiRegex: /([\/<:;=8>(][()D3opPy*>\/\\|-]+) /g,
  unicodeRegex: /((?:[\xA9\xAE\u2122\u23E9-\u23EF\u23F3\u23F8-\u23FA\u24C2\u25B6\u2600-\u27BF\u2934\u2935\u2B05-\u2B07\u2B1B\u2B1C\u2B50\u2B55\u3030\u303D\u3297\u3299]|\uD83C[\uDC04\uDCCF\uDD70\uDD71\uDD7E\uDD7F\uDD8E\uDD91-\uDE51\uDF00-\uDFFF]|\uD83D[\uDC00-\uDE4F\uDE80-\uDEFF]|\uD83E[\uDD00-\uDDFF]))/g,
  inputSelector: 'input:text, textarea',
  buttonSelector: '>.emoji-button',
  inputEvent: /Trident/.test(navigator.userAgent) ? 'textinput' : 'input',
  keyEvent: 'keypress',
  anchorAlignment: 'left', // can be left|right
  anchorOffsetX: -5,
  anchorOffsetY: 5,
  type: 'unicode', // can be one of (unicode|css|image)
  iconSize: 25, // only for css or image mode
  assetPath: '../images', // only for css or image mode
  textClipboard: true,
  globalPicker: true
};

EmojiArea.AUTOINIT = true;
EmojiArea.INJECT_STYLES = true; // only makes sense when EmojiArea.type != 'unicode'

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _generatePlugin = __webpack_require__(4);

var _generatePlugin2 = _interopRequireDefault(_generatePlugin);

var _EmojiStyleGenerator = __webpack_require__(5);

var _EmojiStyleGenerator2 = _interopRequireDefault(_EmojiStyleGenerator);

var _EmojiArea = __webpack_require__(2);

var _EmojiArea2 = _interopRequireDefault(_EmojiArea);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * This is the entry point for the library
 *
 * @author Wolfgang Stöttinger
 */

(0, _generatePlugin2.default)('emojiarea', _EmojiArea2.default);

/**
 * call auto initialization.
 */
(0, _jquery2.default)(function () {
  (0, _jquery2.default)('[data-emoji-inject-style]').each(function (i, e) {
    _EmojiStyleGenerator2.default.injectImageStyles(e);
  });
  (0, _jquery2.default)('[data-emojiarea]').emojiarea();
});

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

exports.default = generatePlugin;

var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

/**
 * Generate a jQuery plugin
 * @param pluginName [string] Plugin name
 * @param className [object] Class of the plugin
 * @param shortHand [bool] Generate a shorthand as $.pluginName
 *
 * @example
 * import plugin from 'plugin';
 *
 * class MyPlugin {
 *     constructor(element, options) {
 *         // ...
 *     }
 * }
 *
 * MyPlugin.DEFAULTS = {};
 *
 * plugin('myPlugin', MyPlugin');
 */
function generatePlugin(pluginName, className) {
  var shortHand = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

  var instanceName = '__' + pluginName;
  var old = _jquery2.default.fn[pluginName];

  _jquery2.default.fn[pluginName] = function (option) {
    return this.each(function () {
      var $this = (0, _jquery2.default)(this);
      var instance = $this.data(instanceName);

      if (!instance && option !== 'destroy') {
        var _options = _jquery2.default.extend({}, className.DEFAULTS, $this.data(), (typeof option === 'undefined' ? 'undefined' : _typeof(option)) === 'object' && option);
        $this.data(instanceName, instance = new className(this, _options));
      } else if (typeof instance.configure === 'function') {
        instance.configure(options);
      }

      if (typeof option === 'string') {
        if (option === 'destroy') {
          instance.destroy();
          $this.data(instanceName, false);
        } else {
          instance[option]();
        }
      }
    });
  };

  // - Short hand
  if (shortHand) {
    _jquery2.default[pluginName] = function (options) {
      return (0, _jquery2.default)({})[pluginName](options);
    };
  }

  // - No conflict
  _jquery2.default.fn[pluginName].noConflict = function () {
    return _jquery2.default.fn[pluginName] = old;
  };
}

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
/**
 * This class generated css style which can automatically be injected into a given element.
 * This is not needed in unicode or image mode, only in css mode.
 *
 * @author Wolfgang Stöttinger
 */


var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _EmojiUtil = __webpack_require__(1);

var _EmojiUtil2 = _interopRequireDefault(_EmojiUtil);

var _EmojiArea = __webpack_require__(2);

var _EmojiArea2 = _interopRequireDefault(_EmojiArea);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var EmojiStyleGenerator = function () {
  function EmojiStyleGenerator() {
    _classCallCheck(this, EmojiStyleGenerator);
  }

  _createClass(EmojiStyleGenerator, null, [{
    key: 'createImageStyles',
    value: function createImageStyles() {
      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

      options = _jquery2.default.extend({}, _EmojiArea2.default.DEFAULTS, (typeof options === 'undefined' ? 'undefined' : _typeof(options)) === 'object' && options);

      var iconSize = options.iconSize;
      var assetPath = options.assetPath;

      var style = '';
      // with before pseudo doesn't work with selection
      // style += '.emoji { font-size: 0; }.emoji::before{display: inline-block;content: \'\';width: ' + iconSize + 'px;height: ' + iconSize + 'px;}';
      // style += '.emoji{color: transparent;}.emoji::selection{color: transparent; background-color:highlight}';

      for (var g = 0; g < _EmojiUtil2.default.groups.length; g++) {
        var group = _EmojiUtil2.default.groups[g];
        var d = group.dimensions;

        for (var e = 0; e < group.items.length; e++) {
          var key = group.items[e];
          var emojiData = _EmojiUtil2.default.data[key];
          if (!emojiData) continue;
          var alias = emojiData[_EmojiUtil2.default.EMOJI_ALIASES];
          if (alias) {
            var row = e / d[0] | 0;
            var col = e % d[0];
            style += '.emoji-' + alias + '{' + 'background: url(\'' + assetPath + '/' + group.sprite + '\') ' + -iconSize * col + 'px ' + -iconSize * row + 'px no-repeat;' + 'background-size: ' + d[0] * iconSize + 'px ' + d[1] * iconSize + 'px;' + '}';
          }
        }
      }

      return style;
    }
  }, {
    key: 'injectImageStyles',
    value: function injectImageStyles(element, options) {
      element = element || 'head';
      (0, _jquery2.default)('<style type="text/css">' + EmojiStyleGenerator.createImageStyles(options) + '</style>').appendTo(element);
    }
  }]);

  return EmojiStyleGenerator;
}();

exports.default = EmojiStyleGenerator;

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
var data = {
  '00a9': [['\xA9', '\xA9\uFE0E'], 'copyright'],
  '00ae': [['\xAE', '\xAE\uFE0E'], 'registered'],
  '203c': [['\u203C\uFE0F', '\u203C'], 'bangbang'],
  '2049': [['\u2049\uFE0F', '\u2049'], 'interrobang'],
  '2122': [['\u2122', '\u2122\uFE0E'], 'tm'],
  '2139': [['\u2139\uFE0F', '\u2139'], 'information_source'],
  '2194': [['\u2194\uFE0F', '\u2194'], 'left_right_arrow'],
  '2195': [['\u2195\uFE0F', '\u2195'], 'arrow_up_down'],
  '2196': [['\u2196\uFE0F', '\u2196'], 'arrow_upper_left'],
  '2197': [['\u2197\uFE0F', '\u2197'], 'arrow_upper_right'],
  '2198': [['\u2198\uFE0F', '\u2198'], 'arrow_lower_right'],
  '2199': [['\u2199\uFE0F', '\u2199'], 'arrow_lower_left'],
  '21a9': [['\u21A9\uFE0F', '\u21A9'], 'leftwards_arrow_with_hook'],
  '21aa': [['\u21AA\uFE0F', '\u21AA'], 'arrow_right_hook'],
  '231a': [['\u231A\uFE0F', '\u231A'], 'watch'],
  '231b': [['\u231B\uFE0F', '\u231B'], 'hourglass'],
  '23e9': [['\u23E9', '\u23E9\uFE0E'], 'fast_forward'],
  '23ea': [['\u23EA', '\u23EA\uFE0E'], 'rewind'],
  '23eb': [['\u23EB', '\u23EB\uFE0E'], 'arrow_double_up'],
  '23ec': [['\u23EC', '\u23EC\uFE0E'], 'arrow_double_down'],
  '23f0': [['\u23F0', '\u23F0\uFE0E'], 'alarm_clock'],
  '23f3': [['\u23F3', '\u23F3\uFE0E'], 'hourglass_flowing_sand'],
  '24c2': [['\u24C2\uFE0F', '\u24C2'], 'm'],
  '25aa': [['\u25AA\uFE0F', '\u25AA'], 'black_small_square'],
  '25ab': [['\u25AB\uFE0F', '\u25AB'], 'white_small_square'],
  '25b6': [['\u25B6\uFE0F', '\u25B6'], 'arrow_forward'],
  '25c0': [['\u25C0\uFE0F', '\u25C0'], 'arrow_backward'],
  '25fb': [['\u25FB\uFE0F', '\u25FB'], 'white_medium_square'],
  '25fc': [['\u25FC\uFE0F', '\u25FC'], 'black_medium_square'],
  '25fd': [['\u25FD\uFE0F', '\u25FD'], 'white_medium_small_square'],
  '25fe': [['\u25FE\uFE0F', '\u25FE'], 'black_medium_small_square'],
  '2600': [['\u2600\uFE0F', '\u2600'], 'sunny'],
  '2601': [['\u2601\uFE0F', '\u2601'], 'cloud'],
  '260e': [['\u260E\uFE0F', '\u260E'], 'phone'],
  '2611': [['\u2611\uFE0F', '\u2611'], 'ballot_box_with_check'],
  '2614': [['\u2614\uFE0F', '\u2614'], 'umbrella'],
  '2615': [['\u2615\uFE0F', '\u2615'], 'coffee'],
  '261d': [['\u261D\uFE0F', '\u261D'], 'point_up'],
  '263a': [['\u263A\uFE0F', '\u263A'], 'relaxed'],
  '2648': [['\u2648\uFE0F', '\u2648'], 'aries'],
  '2649': [['\u2649\uFE0F', '\u2649'], 'taurus'],
  '264a': [['\u264A\uFE0F', '\u264A'], 'gemini'],
  '264b': [['\u264B\uFE0F', '\u264B'], 'cancer'],
  '264c': [['\u264C\uFE0F', '\u264C'], 'leo'],
  '264d': [['\u264D\uFE0F', '\u264D'], 'virgo'],
  '264e': [['\u264E\uFE0F', '\u264E'], 'libra'],
  '264f': [['\u264F\uFE0F', '\u264F'], 'scorpius'],
  '2650': [['\u2650\uFE0F', '\u2650'], 'sagittarius'],
  '2651': [['\u2651\uFE0F', '\u2651'], 'capricorn'],
  '2652': [['\u2652\uFE0F', '\u2652'], 'aquarius'],
  '2653': [['\u2653\uFE0F', '\u2653'], 'pisces'],
  '2660': [['\u2660\uFE0F', '\u2660'], 'spades'],
  '2663': [['\u2663\uFE0F', '\u2663'], 'clubs'],
  '2665': [['\u2665\uFE0F', '\u2665'], 'hearts'],
  '2666': [['\u2666\uFE0F', '\u2666'], 'diamonds'],
  '2668': [['\u2668\uFE0F', '\u2668'], 'hotsprings'],
  '267b': [['\u267B\uFE0F', '\u267B'], 'recycle'],
  '267f': [['\u267F\uFE0F', '\u267F'], 'wheelchair'],
  '2693': [['\u2693\uFE0F', '\u2693'], 'anchor'],
  '26a0': [['\u26A0\uFE0F', '\u26A0'], 'warning'],
  '26a1': [['\u26A1\uFE0F', '\u26A1'], 'zap'],
  '26aa': [['\u26AA\uFE0F', '\u26AA'], 'white_circle'],
  '26ab': [['\u26AB\uFE0F', '\u26AB'], 'black_circle'],
  '26bd': [['\u26BD\uFE0F', '\u26BD'], 'soccer'],
  '26be': [['\u26BE\uFE0F', '\u26BE'], 'baseball'],
  '26c4': [['\u26C4\uFE0F', '\u26C4'], 'snowman'],
  '26c5': [['\u26C5\uFE0F', '\u26C5'], 'partly_sunny'],
  '26ce': [['\u26CE', '\u26CE\uFE0E'], 'ophiuchus'],
  '26d4': [['\u26D4\uFE0F', '\u26D4'], 'no_entry'],
  '26ea': [['\u26EA\uFE0F', '\u26EA'], 'church'],
  '26f2': [['\u26F2\uFE0F', '\u26F2'], 'fountain'],
  '26f3': [['\u26F3\uFE0F', '\u26F3'], 'golf'],
  '26f5': [['\u26F5\uFE0F', '\u26F5'], 'boat'],
  '26fa': [['\u26FA\uFE0F', '\u26FA'], 'tent'],
  '26fd': [['\u26FD\uFE0F', '\u26FD'], 'fuelpump'],
  '2702': [['\u2702\uFE0F', '\u2702'], 'scissors'],
  '2705': [['\u2705', '\u2705\uFE0E'], 'white_check_mark'],
  '2708': [['\u2708\uFE0F', '\u2708'], 'airplane'],
  '2709': [['\u2709\uFE0F', '\u2709'], 'email'],
  '270a': [['\u270A', '\u270A\uFE0E'], 'fist'],
  '270b': [['\u270B', '\u270B\uFE0E'], 'hand'],
  '270c': [['\u270C\uFE0F', '\u270C'], 'v'],
  '270f': [['\u270F\uFE0F', '\u270F'], 'pencil2'],
  '2712': [['\u2712\uFE0F', '\u2712'], 'black_nib'],
  '2714': [['\u2714\uFE0F', '\u2714'], 'heavy_check_mark'],
  '2716': [['\u2716\uFE0F', '\u2716'], 'heavy_multiplication_x'],
  '2728': [['\u2728', '\u2728\uFE0E'], 'sparkles'],
  '2733': [['\u2733\uFE0F', '\u2733'], 'eight_spoked_asterisk'],
  '2734': [['\u2734\uFE0F', '\u2734'], 'eight_pointed_black_star'],
  '2744': [['\u2744\uFE0F', '\u2744'], 'snowflake'],
  '2747': [['\u2747\uFE0F', '\u2747'], 'sparkle'],
  '274c': [['\u274C', '\u274C\uFE0E'], 'x'],
  '274e': [['\u274E', '\u274E\uFE0E'], 'negative_squared_cross_mark'],
  '2753': [['\u2753', '\u2753\uFE0E'], 'question'],
  '2754': [['\u2754', '\u2754\uFE0E'], 'grey_question'],
  '2755': [['\u2755', '\u2755\uFE0E'], 'grey_exclamation'],
  '2757': [['\u2757\uFE0F', '\u2757'], 'exclamation'],
  '2764': [['\u2764\uFE0F', '\u2764'], 'heart'],
  '2795': [['\u2795', '\u2795\uFE0E'], 'heavy_plus_sign'],
  '2796': [['\u2796', '\u2796\uFE0E'], 'heavy_minus_sign'],
  '2797': [['\u2797', '\u2797\uFE0E'], 'heavy_division_sign'],
  '27a1': [['\u27A1\uFE0F', '\u27A1'], 'arrow_right'],
  '27b0': [['\u27B0', '\u27B0\uFE0E'], 'curly_loop'],
  '27bf': [['\u27BF', '\u27BF\uFE0E'], 'loop'],
  '2934': [['\u2934\uFE0F', '\u2934'], 'arrow_heading_up'],
  '2935': [['\u2935\uFE0F', '\u2935'], 'arrow_heading_down'],
  '2b05': [['\u2B05\uFE0F', '\u2B05'], 'arrow_left'],
  '2b06': [['\u2B06\uFE0F', '\u2B06'], 'arrow_up'],
  '2b07': [['\u2B07\uFE0F', '\u2B07'], 'arrow_down'],
  '2b1b': [['\u2B1B\uFE0F', '\u2B1B'], 'black_large_square'],
  '2b1c': [['\u2B1C\uFE0F', '\u2B1C'], 'white_large_square'],
  '2b50': [['\u2B50\uFE0F', '\u2B50'], 'star'],
  '2b55': [['\u2B55\uFE0F', '\u2B55'], 'o'],
  '3030': [['\u3030', '\u3030\uFE0E'], 'wavy_dash'],
  '303d': [['\u303D\uFE0F', '\u303D'], 'part_alternation_mark'],
  '3297': [['\u3297\uFE0F', '\u3297'], 'congratulations'],
  '3299': [['\u3299\uFE0F', '\u3299'], 'secret'],
  '1f004': [['\uD83C\uDC04\uFE0F'], 'mahjong'],
  '1f0cf': [['\uD83C\uDCCF'], 'black_joker'],
  '1f170': [['\uD83C\uDD70'], 'a'],
  '1f171': [['\uD83C\uDD71'], 'b'],
  '1f17e': [['\uD83C\uDD7E'], 'o2'],
  '1f17f': [['\uD83C\uDD7F\uFE0F'], 'parking'],
  '1f18e': [['\uD83C\uDD8E'], 'ab'],
  '1f191': [['\uD83C\uDD91'], 'cl'],
  '1f192': [['\uD83C\uDD92'], 'cool'],
  '1f193': [['\uD83C\uDD93'], 'free'],
  '1f194': [['\uD83C\uDD94'], 'id'],
  '1f195': [['\uD83C\uDD95'], 'new'],
  '1f196': [['\uD83C\uDD96'], 'ng'],
  '1f197': [['\uD83C\uDD97'], 'ok'],
  '1f198': [['\uD83C\uDD98'], 'sos'],
  '1f199': [['\uD83C\uDD99'], 'up'],
  '1f19a': [['\uD83C\uDD9A'], 'vs'],
  '1f201': [['\uD83C\uDE01'], 'koko'],
  '1f202': [['\uD83C\uDE02'], 'sa'],
  '1f21a': [['\uD83C\uDE1A\uFE0F'], 'u7121'],
  '1f22f': [['\uD83C\uDE2F\uFE0F'], 'u6307'],
  '1f232': [['\uD83C\uDE32'], 'u7981'],
  '1f233': [['\uD83C\uDE33'], 'u7a7a'],
  '1f234': [['\uD83C\uDE34'], 'u5408'],
  '1f235': [['\uD83C\uDE35'], 'u6e80'],
  '1f236': [['\uD83C\uDE36'], 'u6709'],
  '1f237': [['\uD83C\uDE37'], 'u6708'],
  '1f238': [['\uD83C\uDE38'], 'u7533'],
  '1f239': [['\uD83C\uDE39'], 'u5272'],
  '1f23a': [['\uD83C\uDE3A'], 'u55b6'],
  '1f250': [['\uD83C\uDE50'], 'ideograph_advantage'],
  '1f251': [['\uD83C\uDE51'], 'accept'],
  '1f300': [['\uD83C\uDF00'], 'cyclone'],
  '1f301': [['\uD83C\uDF01'], 'foggy'],
  '1f302': [['\uD83C\uDF02'], 'closed_umbrella'],
  '1f303': [['\uD83C\uDF03'], 'night_with_stars'],
  '1f304': [['\uD83C\uDF04'], 'sunrise_over_mountains'],
  '1f305': [['\uD83C\uDF05'], 'sunrise'],
  '1f306': [['\uD83C\uDF06'], 'city_sunset'],
  '1f307': [['\uD83C\uDF07'], 'city_sunrise'],
  '1f308': [['\uD83C\uDF08'], 'rainbow'],
  '1f309': [['\uD83C\uDF09'], 'bridge_at_night'],
  '1f30a': [['\uD83C\uDF0A'], 'ocean'],
  '1f30b': [['\uD83C\uDF0B'], 'volcano'],
  '1f30c': [['\uD83C\uDF0C'], 'milky_way'],
  '1f30d': [['\uD83C\uDF0D'], 'earth_africa'],
  '1f30e': [['\uD83C\uDF0E'], 'earth_americas'],
  '1f30f': [['\uD83C\uDF0F'], 'earth_asia'],
  '1f310': [['\uD83C\uDF10'], 'globe_with_meridians'],
  '1f311': [['\uD83C\uDF11'], 'new_moon'],
  '1f312': [['\uD83C\uDF12'], 'waxing_crescent_moon'],
  '1f313': [['\uD83C\uDF13'], 'first_quarter_moon'],
  '1f314': [['\uD83C\uDF14'], 'moon'],
  '1f315': [['\uD83C\uDF15'], 'full_moon'],
  '1f316': [['\uD83C\uDF16'], 'waning_gibbous_moon'],
  '1f317': [['\uD83C\uDF17'], 'last_quarter_moon'],
  '1f318': [['\uD83C\uDF18'], 'waning_crescent_moon'],
  '1f319': [['\uD83C\uDF19'], 'crescent_moon'],
  '1f31a': [['\uD83C\uDF1A'], 'new_moon_with_face'],
  '1f31b': [['\uD83C\uDF1B'], 'first_quarter_moon_with_face'],
  '1f31c': [['\uD83C\uDF1C'], 'last_quarter_moon_with_face'],
  '1f31d': [['\uD83C\uDF1D'], 'full_moon_with_face'],
  '1f31e': [['\uD83C\uDF1E'], 'sun_with_face'],
  '1f31f': [['\uD83C\uDF1F'], 'star2'],
  '1f320': [['\uD83C\uDF20'], 'stars'],
  '1f330': [['\uD83C\uDF30'], 'chestnut'],
  '1f331': [['\uD83C\uDF31'], 'seedling'],
  '1f332': [['\uD83C\uDF32'], 'evergreen_tree'],
  '1f333': [['\uD83C\uDF33'], 'deciduous_tree'],
  '1f334': [['\uD83C\uDF34'], 'palm_tree'],
  '1f335': [['\uD83C\uDF35'], 'cactus'],
  '1f337': [['\uD83C\uDF37'], 'tulip'],
  '1f338': [['\uD83C\uDF38'], 'cherry_blossom'],
  '1f339': [['\uD83C\uDF39'], 'rose'],
  '1f33a': [['\uD83C\uDF3A'], 'hibiscus'],
  '1f33b': [['\uD83C\uDF3B'], 'sunflower'],
  '1f33c': [['\uD83C\uDF3C'], 'blossom'],
  '1f33d': [['\uD83C\uDF3D'], 'corn'],
  '1f33e': [['\uD83C\uDF3E'], 'ear_of_rice'],
  '1f33f': [['\uD83C\uDF3F'], 'herb'],
  '1f340': [['\uD83C\uDF40'], 'four_leaf_clover'],
  '1f341': [['\uD83C\uDF41'], 'maple_leaf'],
  '1f342': [['\uD83C\uDF42'], 'fallen_leaf'],
  '1f343': [['\uD83C\uDF43'], 'leaves'],
  '1f344': [['\uD83C\uDF44'], 'mushroom'],
  '1f345': [['\uD83C\uDF45'], 'tomato'],
  '1f346': [['\uD83C\uDF46'], 'eggplant'],
  '1f347': [['\uD83C\uDF47'], 'grapes'],
  '1f348': [['\uD83C\uDF48'], 'melon'],
  '1f349': [['\uD83C\uDF49'], 'watermelon'],
  '1f34a': [['\uD83C\uDF4A'], 'tangerine'],
  '1f34b': [['\uD83C\uDF4B'], 'lemon'],
  '1f34c': [['\uD83C\uDF4C'], 'banana'],
  '1f34d': [['\uD83C\uDF4D'], 'pineapple'],
  '1f34e': [['\uD83C\uDF4E'], 'apple'],
  '1f34f': [['\uD83C\uDF4F'], 'green_apple'],
  '1f350': [['\uD83C\uDF50'], 'pear'],
  '1f351': [['\uD83C\uDF51'], 'peach'],
  '1f352': [['\uD83C\uDF52'], 'cherries'],
  '1f353': [['\uD83C\uDF53'], 'strawberry'],
  '1f354': [['\uD83C\uDF54'], 'hamburger'],
  '1f355': [['\uD83C\uDF55'], 'pizza'],
  '1f356': [['\uD83C\uDF56'], 'meat_on_bone'],
  '1f357': [['\uD83C\uDF57'], 'poultry_leg'],
  '1f358': [['\uD83C\uDF58'], 'rice_cracker'],
  '1f359': [['\uD83C\uDF59'], 'rice_ball'],
  '1f35a': [['\uD83C\uDF5A'], 'rice'],
  '1f35b': [['\uD83C\uDF5B'], 'curry'],
  '1f35c': [['\uD83C\uDF5C'], 'ramen'],
  '1f35d': [['\uD83C\uDF5D'], 'spaghetti'],
  '1f35e': [['\uD83C\uDF5E'], 'bread'],
  '1f35f': [['\uD83C\uDF5F'], 'fries'],
  '1f360': [['\uD83C\uDF60'], 'sweet_potato'],
  '1f361': [['\uD83C\uDF61'], 'dango'],
  '1f362': [['\uD83C\uDF62'], 'oden'],
  '1f363': [['\uD83C\uDF63'], 'sushi'],
  '1f364': [['\uD83C\uDF64'], 'fried_shrimp'],
  '1f365': [['\uD83C\uDF65'], 'fish_cake'],
  '1f366': [['\uD83C\uDF66'], 'icecream'],
  '1f367': [['\uD83C\uDF67'], 'shaved_ice'],
  '1f368': [['\uD83C\uDF68'], 'ice_cream'],
  '1f369': [['\uD83C\uDF69'], 'doughnut'],
  '1f36a': [['\uD83C\uDF6A'], 'cookie'],
  '1f36b': [['\uD83C\uDF6B'], 'chocolate_bar'],
  '1f36c': [['\uD83C\uDF6C'], 'candy'],
  '1f36d': [['\uD83C\uDF6D'], 'lollipop'],
  '1f36e': [['\uD83C\uDF6E'], 'custard'],
  '1f36f': [['\uD83C\uDF6F'], 'honey_pot'],
  '1f370': [['\uD83C\uDF70'], 'cake'],
  '1f371': [['\uD83C\uDF71'], 'bento'],
  '1f372': [['\uD83C\uDF72'], 'stew'],
  '1f373': [['\uD83C\uDF73'], 'egg'],
  '1f374': [['\uD83C\uDF74'], 'fork_and_knife'],
  '1f375': [['\uD83C\uDF75'], 'tea'],
  '1f376': [['\uD83C\uDF76'], 'sake'],
  '1f377': [['\uD83C\uDF77'], 'wine_glass'],
  '1f378': [['\uD83C\uDF78'], 'cocktail'],
  '1f379': [['\uD83C\uDF79'], 'tropical_drink'],
  '1f37a': [['\uD83C\uDF7A'], 'beer'],
  '1f37b': [['\uD83C\uDF7B'], 'beers'],
  '1f37c': [['\uD83C\uDF7C'], 'baby_bottle'],
  '1f380': [['\uD83C\uDF80'], 'ribbon'],
  '1f381': [['\uD83C\uDF81'], 'gift'],
  '1f382': [['\uD83C\uDF82'], 'birthday'],
  '1f383': [['\uD83C\uDF83'], 'jack_o_lantern'],
  '1f384': [['\uD83C\uDF84'], 'christmas_tree'],
  '1f385': [['\uD83C\uDF85'], 'santa'],
  '1f386': [['\uD83C\uDF86'], 'fireworks'],
  '1f387': [['\uD83C\uDF87'], 'sparkler'],
  '1f388': [['\uD83C\uDF88'], 'balloon'],
  '1f389': [['\uD83C\uDF89'], 'tada'],
  '1f38a': [['\uD83C\uDF8A'], 'confetti_ball'],
  '1f38b': [['\uD83C\uDF8B'], 'tanabata_tree'],
  '1f38c': [['\uD83C\uDF8C'], 'crossed_flags'],
  '1f38d': [['\uD83C\uDF8D'], 'bamboo'],
  '1f38e': [['\uD83C\uDF8E'], 'dolls'],
  '1f38f': [['\uD83C\uDF8F'], 'flags'],
  '1f390': [['\uD83C\uDF90'], 'wind_chime'],
  '1f391': [['\uD83C\uDF91'], 'rice_scene'],
  '1f392': [['\uD83C\uDF92'], 'school_satchel'],
  '1f393': [['\uD83C\uDF93'], 'mortar_board'],
  '1f3a0': [['\uD83C\uDFA0'], 'carousel_horse'],
  '1f3a1': [['\uD83C\uDFA1'], 'ferris_wheel'],
  '1f3a2': [['\uD83C\uDFA2'], 'roller_coaster'],
  '1f3a3': [['\uD83C\uDFA3'], 'fishing_pole_and_fish'],
  '1f3a4': [['\uD83C\uDFA4'], 'microphone'],
  '1f3a5': [['\uD83C\uDFA5'], 'movie_camera'],
  '1f3a6': [['\uD83C\uDFA6'], 'cinema'],
  '1f3a7': [['\uD83C\uDFA7'], 'headphones'],
  '1f3a8': [['\uD83C\uDFA8'], 'art'],
  '1f3a9': [['\uD83C\uDFA9'], 'tophat'],
  '1f3aa': [['\uD83C\uDFAA'], 'circus_tent'],
  '1f3ab': [['\uD83C\uDFAB'], 'ticket'],
  '1f3ac': [['\uD83C\uDFAC'], 'clapper'],
  '1f3ad': [['\uD83C\uDFAD'], 'performing_arts'],
  '1f3ae': [['\uD83C\uDFAE'], 'video_game'],
  '1f3af': [['\uD83C\uDFAF'], 'dart'],
  '1f3b0': [['\uD83C\uDFB0'], 'slot_machine'],
  '1f3b1': [['\uD83C\uDFB1'], '8ball'],
  '1f3b2': [['\uD83C\uDFB2'], 'game_die'],
  '1f3b3': [['\uD83C\uDFB3'], 'bowling'],
  '1f3b4': [['\uD83C\uDFB4'], 'flower_playing_cards'],
  '1f3b5': [['\uD83C\uDFB5'], 'musical_note'],
  '1f3b6': [['\uD83C\uDFB6'], 'notes'],
  '1f3b7': [['\uD83C\uDFB7'], 'saxophone'],
  '1f3b8': [['\uD83C\uDFB8'], 'guitar'],
  '1f3b9': [['\uD83C\uDFB9'], 'musical_keyboard'],
  '1f3ba': [['\uD83C\uDFBA'], 'trumpet'],
  '1f3bb': [['\uD83C\uDFBB'], 'violin'],
  '1f3bc': [['\uD83C\uDFBC'], 'musical_score'],
  '1f3bd': [['\uD83C\uDFBD'], 'running_shirt_with_sash'],
  '1f3be': [['\uD83C\uDFBE'], 'tennis'],
  '1f3bf': [['\uD83C\uDFBF'], 'ski'],
  '1f3c0': [['\uD83C\uDFC0'], 'basketball'],
  '1f3c1': [['\uD83C\uDFC1'], 'checkered_flag'],
  '1f3c2': [['\uD83C\uDFC2'], 'snowboarder'],
  '1f3c3': [['\uD83C\uDFC3'], 'runner'],
  '1f3c4': [['\uD83C\uDFC4'], 'surfer'],
  '1f3c6': [['\uD83C\uDFC6'], 'trophy'],
  '1f3c7': [['\uD83C\uDFC7'], 'horse_racing'],
  '1f3c8': [['\uD83C\uDFC8'], 'football'],
  '1f3c9': [['\uD83C\uDFC9'], 'rugby_football'],
  '1f3ca': [['\uD83C\uDFCA'], 'swimmer'],
  '1f3e0': [['\uD83C\uDFE0'], 'house'],
  '1f3e1': [['\uD83C\uDFE1'], 'house_with_garden'],
  '1f3e2': [['\uD83C\uDFE2'], 'office'],
  '1f3e3': [['\uD83C\uDFE3'], 'post_office'],
  '1f3e4': [['\uD83C\uDFE4'], 'european_post_office'],
  '1f3e5': [['\uD83C\uDFE5'], 'hospital'],
  '1f3e6': [['\uD83C\uDFE6'], 'bank'],
  '1f3e7': [['\uD83C\uDFE7'], 'atm'],
  '1f3e8': [['\uD83C\uDFE8'], 'hotel'],
  '1f3e9': [['\uD83C\uDFE9'], 'love_hotel'],
  '1f3ea': [['\uD83C\uDFEA'], 'convenience_store'],
  '1f3eb': [['\uD83C\uDFEB'], 'school'],
  '1f3ec': [['\uD83C\uDFEC'], 'department_store'],
  '1f3ed': [['\uD83C\uDFED'], 'factory'],
  '1f3ee': [['\uD83C\uDFEE'], 'izakaya_lantern'],
  '1f3ef': [['\uD83C\uDFEF'], 'japanese_castle'],
  '1f3f0': [['\uD83C\uDFF0'], 'european_castle'],
  '1f400': [['\uD83D\uDC00'], 'rat'],
  '1f401': [['\uD83D\uDC01'], 'mouse2'],
  '1f402': [['\uD83D\uDC02'], 'ox'],
  '1f403': [['\uD83D\uDC03'], 'water_buffalo'],
  '1f404': [['\uD83D\uDC04'], 'cow2'],
  '1f405': [['\uD83D\uDC05'], 'tiger2'],
  '1f406': [['\uD83D\uDC06'], 'leopard'],
  '1f407': [['\uD83D\uDC07'], 'rabbit2'],
  '1f408': [['\uD83D\uDC08'], 'cat2'],
  '1f409': [['\uD83D\uDC09'], 'dragon'],
  '1f40a': [['\uD83D\uDC0A'], 'crocodile'],
  '1f40b': [['\uD83D\uDC0B'], 'whale2'],
  '1f40c': [['\uD83D\uDC0C'], 'snail'],
  '1f40d': [['\uD83D\uDC0D'], 'snake'],
  '1f40e': [['\uD83D\uDC0E'], 'racehorse'],
  '1f40f': [['\uD83D\uDC0F'], 'ram'],
  '1f410': [['\uD83D\uDC10'], 'goat'],
  '1f411': [['\uD83D\uDC11'], 'sheep'],
  '1f412': [['\uD83D\uDC12'], 'monkey'],
  '1f413': [['\uD83D\uDC13'], 'rooster'],
  '1f414': [['\uD83D\uDC14'], 'chicken'],
  '1f415': [['\uD83D\uDC15'], 'dog2'],
  '1f416': [['\uD83D\uDC16'], 'pig2'],
  '1f417': [['\uD83D\uDC17'], 'boar'],
  '1f418': [['\uD83D\uDC18'], 'elephant'],
  '1f419': [['\uD83D\uDC19'], 'octopus'],
  '1f41a': [['\uD83D\uDC1A'], 'shell'],
  '1f41b': [['\uD83D\uDC1B'], 'bug'],
  '1f41c': [['\uD83D\uDC1C'], 'ant'],
  '1f41d': [['\uD83D\uDC1D'], 'bee'],
  '1f41e': [['\uD83D\uDC1E'], 'beetle'],
  '1f41f': [['\uD83D\uDC1F'], 'fish'],
  '1f420': [['\uD83D\uDC20'], 'tropical_fish'],
  '1f421': [['\uD83D\uDC21'], 'blowfish'],
  '1f422': [['\uD83D\uDC22'], 'turtle'],
  '1f423': [['\uD83D\uDC23'], 'hatching_chick'],
  '1f424': [['\uD83D\uDC24'], 'baby_chick'],
  '1f425': [['\uD83D\uDC25'], 'hatched_chick'],
  '1f426': [['\uD83D\uDC26'], 'bird'],
  '1f427': [['\uD83D\uDC27'], 'penguin'],
  '1f428': [['\uD83D\uDC28'], 'koala'],
  '1f429': [['\uD83D\uDC29'], 'poodle'],
  '1f42a': [['\uD83D\uDC2A'], 'dromedary_camel'],
  '1f42b': [['\uD83D\uDC2B'], 'camel'],
  '1f42c': [['\uD83D\uDC2C'], 'dolphin'],
  '1f42d': [['\uD83D\uDC2D'], 'mouse'],
  '1f42e': [['\uD83D\uDC2E'], 'cow'],
  '1f42f': [['\uD83D\uDC2F'], 'tiger'],
  '1f430': [['\uD83D\uDC30'], 'rabbit'],
  '1f431': [['\uD83D\uDC31'], 'cat'],
  '1f432': [['\uD83D\uDC32'], 'dragon_face'],
  '1f433': [['\uD83D\uDC33'], 'whale'],
  '1f434': [['\uD83D\uDC34'], 'horse'],
  '1f435': [['\uD83D\uDC35'], 'monkey_face'],
  '1f436': [['\uD83D\uDC36'], 'dog'],
  '1f437': [['\uD83D\uDC37'], 'pig'],
  '1f438': [['\uD83D\uDC38'], 'frog'],
  '1f439': [['\uD83D\uDC39'], 'hamster'],
  '1f43a': [['\uD83D\uDC3A'], 'wolf'],
  '1f43b': [['\uD83D\uDC3B'], 'bear'],
  '1f43c': [['\uD83D\uDC3C'], 'panda_face'],
  '1f43d': [['\uD83D\uDC3D'], 'pig_nose'],
  '1f43e': [['\uD83D\uDC3E'], 'feet'],
  '1f440': [['\uD83D\uDC40'], 'eyes'],
  '1f442': [['\uD83D\uDC42'], 'ear'],
  '1f443': [['\uD83D\uDC43'], 'nose'],
  '1f444': [['\uD83D\uDC44'], 'lips'],
  '1f445': [['\uD83D\uDC45'], 'tongue'],
  '1f446': [['\uD83D\uDC46'], 'point_up_2'],
  '1f447': [['\uD83D\uDC47'], 'point_down'],
  '1f448': [['\uD83D\uDC48'], 'point_left'],
  '1f449': [['\uD83D\uDC49'], 'point_right'],
  '1f44a': [['\uD83D\uDC4A'], 'facepunch'],
  '1f44b': [['\uD83D\uDC4B'], 'wave'],
  '1f44c': [['\uD83D\uDC4C'], 'ok_hand'],
  '1f44d': [['\uD83D\uDC4D'], 'thumb_up'],
  '1f44e': [['\uD83D\uDC4E'], 'thumb_down'],
  '1f44f': [['\uD83D\uDC4F'], 'clap'],
  '1f450': [['\uD83D\uDC50'], 'open_hands'],
  '1f451': [['\uD83D\uDC51'], 'crown'],
  '1f452': [['\uD83D\uDC52'], 'womans_hat'],
  '1f453': [['\uD83D\uDC53'], 'eyeglasses'],
  '1f454': [['\uD83D\uDC54'], 'necktie'],
  '1f455': [['\uD83D\uDC55'], 'shirt'],
  '1f456': [['\uD83D\uDC56'], 'jeans'],
  '1f457': [['\uD83D\uDC57'], 'dress'],
  '1f458': [['\uD83D\uDC58'], 'kimono'],
  '1f459': [['\uD83D\uDC59'], 'bikini'],
  '1f45a': [['\uD83D\uDC5A'], 'womans_clothes'],
  '1f45b': [['\uD83D\uDC5B'], 'purse'],
  '1f45c': [['\uD83D\uDC5C'], 'handbag'],
  '1f45d': [['\uD83D\uDC5D'], 'pouch'],
  '1f45e': [['\uD83D\uDC5E'], 'mans_shoe'],
  '1f45f': [['\uD83D\uDC5F'], 'athletic_shoe'],
  '1f460': [['\uD83D\uDC60'], 'high_heel'],
  '1f461': [['\uD83D\uDC61'], 'sandal'],
  '1f462': [['\uD83D\uDC62'], 'boot'],
  '1f463': [['\uD83D\uDC63'], 'footprints'],
  '1f464': [['\uD83D\uDC64'], 'bust_in_silhouette'],
  '1f465': [['\uD83D\uDC65'], 'busts_in_silhouette'],
  '1f466': [['\uD83D\uDC66'], 'boy'],
  '1f467': [['\uD83D\uDC67'], 'girl'],
  '1f468': [['\uD83D\uDC68'], 'man'],
  '1f469': [['\uD83D\uDC69'], 'woman'],
  '1f46a': [['\uD83D\uDC6A'], 'family'],
  '1f46b': [['\uD83D\uDC6B'], 'couple'],
  '1f46c': [['\uD83D\uDC6C'], 'two_men_holding_hands'],
  '1f46d': [['\uD83D\uDC6D'], 'two_women_holding_hands'],
  '1f46e': [['\uD83D\uDC6E'], 'cop'],
  '1f46f': [['\uD83D\uDC6F'], 'dancers'],
  '1f470': [['\uD83D\uDC70'], 'bride_with_veil'],
  '1f471': [['\uD83D\uDC71'], 'person_with_blond_hair'],
  '1f472': [['\uD83D\uDC72'], 'man_with_gua_pi_mao'],
  '1f473': [['\uD83D\uDC73'], 'man_with_turban'],
  '1f474': [['\uD83D\uDC74'], 'older_man'],
  '1f475': [['\uD83D\uDC75'], 'older_woman'],
  '1f476': [['\uD83D\uDC76'], 'baby'],
  '1f477': [['\uD83D\uDC77'], 'construction_worker'],
  '1f478': [['\uD83D\uDC78'], 'princess'],
  '1f479': [['\uD83D\uDC79'], 'japanese_ogre'],
  '1f47a': [['\uD83D\uDC7A'], 'japanese_goblin'],
  '1f47b': [['\uD83D\uDC7B'], 'ghost'],
  '1f47c': [['\uD83D\uDC7C'], 'angel'],
  '1f47d': [['\uD83D\uDC7D'], 'alien'],
  '1f47e': [['\uD83D\uDC7E'], 'space_invader'],
  '1f47f': [['\uD83D\uDC7F'], 'imp'],
  '1f480': [['\uD83D\uDC80'], 'skull'],
  '1f481': [['\uD83D\uDC81'], 'information_desk_person'],
  '1f482': [['\uD83D\uDC82'], 'guardsman'],
  '1f483': [['\uD83D\uDC83'], 'dancer'],
  '1f484': [['\uD83D\uDC84'], 'lipstick'],
  '1f485': [['\uD83D\uDC85'], 'nail_care'],
  '1f486': [['\uD83D\uDC86'], 'massage'],
  '1f487': [['\uD83D\uDC87'], 'haircut'],
  '1f488': [['\uD83D\uDC88'], 'barber'],
  '1f489': [['\uD83D\uDC89'], 'syringe'],
  '1f48a': [['\uD83D\uDC8A'], 'pill'],
  '1f48b': [['\uD83D\uDC8B'], 'kiss'],
  '1f48c': [['\uD83D\uDC8C'], 'love_letter'],
  '1f48d': [['\uD83D\uDC8D'], 'ring'],
  '1f48e': [['\uD83D\uDC8E'], 'gem'],
  '1f48f': [['\uD83D\uDC8F'], 'couplekiss'],
  '1f490': [['\uD83D\uDC90'], 'bouquet'],
  '1f491': [['\uD83D\uDC91'], 'couple_with_heart'],
  '1f492': [['\uD83D\uDC92'], 'wedding'],
  '1f493': [['\uD83D\uDC93'], 'heartbeat'],
  '1f494': [['\uD83D\uDC94'], 'broken_heart'],
  '1f495': [['\uD83D\uDC95'], 'two_hearts'],
  '1f496': [['\uD83D\uDC96'], 'sparkling_heart'],
  '1f497': [['\uD83D\uDC97'], 'heartpulse'],
  '1f498': [['\uD83D\uDC98'], 'cupid'],
  '1f499': [['\uD83D\uDC99'], 'blue_heart'],
  '1f49a': [['\uD83D\uDC9A'], 'green_heart'],
  '1f49b': [['\uD83D\uDC9B'], 'yellow_heart'],
  '1f49c': [['\uD83D\uDC9C'], 'purple_heart'],
  '1f49d': [['\uD83D\uDC9D'], 'gift_heart'],
  '1f49e': [['\uD83D\uDC9E'], 'revolving_hearts'],
  '1f49f': [['\uD83D\uDC9F'], 'heart_decoration'],
  '1f4a0': [['\uD83D\uDCA0'], 'diamond_shape_with_a_dot_inside'],
  '1f4a1': [['\uD83D\uDCA1'], 'bulb'],
  '1f4a2': [['\uD83D\uDCA2'], 'anger'],
  '1f4a3': [['\uD83D\uDCA3'], 'bomb'],
  '1f4a4': [['\uD83D\uDCA4'], 'zzz'],
  '1f4a5': [['\uD83D\uDCA5'], 'boom'],
  '1f4a6': [['\uD83D\uDCA6'], 'sweat_drops'],
  '1f4a7': [['\uD83D\uDCA7'], 'droplet'],
  '1f4a8': [['\uD83D\uDCA8'], 'dash'],
  '1f4a9': [['\uD83D\uDCA9'], 'hankey'],
  '1f4aa': [['\uD83D\uDCAA'], 'muscle'],
  '1f4ab': [['\uD83D\uDCAB'], 'dizzy'],
  '1f4ac': [['\uD83D\uDCAC'], 'speech_balloon'],
  '1f4ad': [['\uD83D\uDCAD'], 'thought_balloon'],
  '1f4ae': [['\uD83D\uDCAE'], 'white_flower'],
  '1f4af': [['\uD83D\uDCAF'], '100'],
  '1f4b0': [['\uD83D\uDCB0'], 'moneybag'],
  '1f4b1': [['\uD83D\uDCB1'], 'currency_exchange'],
  '1f4b2': [['\uD83D\uDCB2'], 'heavy_dollar_sign'],
  '1f4b3': [['\uD83D\uDCB3'], 'credit_card'],
  '1f4b4': [['\uD83D\uDCB4'], 'yen'],
  '1f4b5': [['\uD83D\uDCB5'], 'dollar'],
  '1f4b6': [['\uD83D\uDCB6'], 'euro'],
  '1f4b7': [['\uD83D\uDCB7'], 'pound'],
  '1f4b8': [['\uD83D\uDCB8'], 'money_with_wings'],
  '1f4b9': [['\uD83D\uDCB9'], 'chart'],
  '1f4ba': [['\uD83D\uDCBA'], 'seat'],
  '1f4bb': [['\uD83D\uDCBB'], 'computer'],
  '1f4bc': [['\uD83D\uDCBC'], 'briefcase'],
  '1f4bd': [['\uD83D\uDCBD'], 'minidisc'],
  '1f4be': [['\uD83D\uDCBE'], 'floppy_disk'],
  '1f4bf': [['\uD83D\uDCBF'], 'cd'],
  '1f4c0': [['\uD83D\uDCC0'], 'dvd'],
  '1f4c1': [['\uD83D\uDCC1'], 'file_folder'],
  '1f4c2': [['\uD83D\uDCC2'], 'open_file_folder'],
  '1f4c3': [['\uD83D\uDCC3'], 'page_with_curl'],
  '1f4c4': [['\uD83D\uDCC4'], 'page_facing_up'],
  '1f4c5': [['\uD83D\uDCC5'], 'date'],
  '1f4c6': [['\uD83D\uDCC6'], 'calendar'],
  '1f4c7': [['\uD83D\uDCC7'], 'card_index'],
  '1f4c8': [['\uD83D\uDCC8'], 'chart_with_upwards_trend'],
  '1f4c9': [['\uD83D\uDCC9'], 'chart_with_downwards_trend'],
  '1f4ca': [['\uD83D\uDCCA'], 'bar_chart'],
  '1f4cb': [['\uD83D\uDCCB'], 'clipboard'],
  '1f4cc': [['\uD83D\uDCCC'], 'pushpin'],
  '1f4cd': [['\uD83D\uDCCD'], 'round_pushpin'],
  '1f4ce': [['\uD83D\uDCCE'], 'paperclip'],
  '1f4cf': [['\uD83D\uDCCF'], 'straight_ruler'],
  '1f4d0': [['\uD83D\uDCD0'], 'triangular_ruler'],
  '1f4d1': [['\uD83D\uDCD1'], 'bookmark_tabs'],
  '1f4d2': [['\uD83D\uDCD2'], 'ledger'],
  '1f4d3': [['\uD83D\uDCD3'], 'notebook'],
  '1f4d4': [['\uD83D\uDCD4'], 'notebook_with_decorative_cover'],
  '1f4d5': [['\uD83D\uDCD5'], 'closed_book'],
  '1f4d6': [['\uD83D\uDCD6'], 'book'],
  '1f4d7': [['\uD83D\uDCD7'], 'green_book'],
  '1f4d8': [['\uD83D\uDCD8'], 'blue_book'],
  '1f4d9': [['\uD83D\uDCD9'], 'orange_book'],
  '1f4da': [['\uD83D\uDCDA'], 'books'],
  '1f4db': [['\uD83D\uDCDB'], 'name_badge'],
  '1f4dc': [['\uD83D\uDCDC'], 'scroll'],
  '1f4dd': [['\uD83D\uDCDD'], 'memo'],
  '1f4de': [['\uD83D\uDCDE'], 'telephone_receiver'],
  '1f4df': [['\uD83D\uDCDF'], 'pager'],
  '1f4e0': [['\uD83D\uDCE0'], 'fax'],
  '1f4e1': [['\uD83D\uDCE1'], 'satellite'],
  '1f4e2': [['\uD83D\uDCE2'], 'loudspeaker'],
  '1f4e3': [['\uD83D\uDCE3'], 'mega'],
  '1f4e4': [['\uD83D\uDCE4'], 'outbox_tray'],
  '1f4e5': [['\uD83D\uDCE5'], 'inbox_tray'],
  '1f4e6': [['\uD83D\uDCE6'], 'package'],
  '1f4e7': [['\uD83D\uDCE7'], 'e-mail'],
  '1f4e8': [['\uD83D\uDCE8'], 'incoming_envelope'],
  '1f4e9': [['\uD83D\uDCE9'], 'envelope_with_arrow'],
  '1f4ea': [['\uD83D\uDCEA'], 'mailbox_closed'],
  '1f4eb': [['\uD83D\uDCEB'], 'mailbox'],
  '1f4ec': [['\uD83D\uDCEC'], 'mailbox_with_mail'],
  '1f4ed': [['\uD83D\uDCED'], 'mailbox_with_no_mail'],
  '1f4ee': [['\uD83D\uDCEE'], 'postbox'],
  '1f4ef': [['\uD83D\uDCEF'], 'postal_horn'],
  '1f4f0': [['\uD83D\uDCF0'], 'newspaper'],
  '1f4f1': [['\uD83D\uDCF1'], 'iphone'],
  '1f4f2': [['\uD83D\uDCF2'], 'calling'],
  '1f4f3': [['\uD83D\uDCF3'], 'vibration_mode'],
  '1f4f4': [['\uD83D\uDCF4'], 'mobile_phone_off'],
  '1f4f5': [['\uD83D\uDCF5'], 'no_mobile_phones'],
  '1f4f6': [['\uD83D\uDCF6'], 'signal_strength'],
  '1f4f7': [['\uD83D\uDCF7'], 'camera'],
  '1f4f9': [['\uD83D\uDCF9'], 'video_camera'],
  '1f4fa': [['\uD83D\uDCFA'], 'tv'],
  '1f4fb': [['\uD83D\uDCFB'], 'radio'],
  '1f4fc': [['\uD83D\uDCFC'], 'vhs'],
  '1f500': [['\uD83D\uDD00'], 'twisted_rightwards_arrows'],
  '1f501': [['\uD83D\uDD01'], 'repeat'],
  '1f502': [['\uD83D\uDD02'], 'repeat_one'],
  '1f503': [['\uD83D\uDD03'], 'arrows_clockwise'],
  '1f504': [['\uD83D\uDD04'], 'arrows_counterclockwise'],
  '1f505': [['\uD83D\uDD05'], 'low_brightness'],
  '1f506': [['\uD83D\uDD06'], 'high_brightness'],
  '1f507': [['\uD83D\uDD07'], 'mute'],
  '1f508': [['\uD83D\uDD08'], 'speaker'],
  '1f509': [['\uD83D\uDD09'], 'sound'],
  '1f50a': [['\uD83D\uDD0A'], 'loud_sound'],
  '1f50b': [['\uD83D\uDD0B'], 'battery'],
  '1f50c': [['\uD83D\uDD0C'], 'electric_plug'],
  '1f50d': [['\uD83D\uDD0D'], 'mag'],
  '1f50e': [['\uD83D\uDD0E'], 'mag_right'],
  '1f50f': [['\uD83D\uDD0F'], 'lock_with_ink_pen'],
  '1f510': [['\uD83D\uDD10'], 'closed_lock_with_key'],
  '1f511': [['\uD83D\uDD11'], 'key'],
  '1f512': [['\uD83D\uDD12'], 'lock'],
  '1f513': [['\uD83D\uDD13'], 'unlock'],
  '1f514': [['\uD83D\uDD14'], 'bell'],
  '1f515': [['\uD83D\uDD15'], 'no_bell'],
  '1f516': [['\uD83D\uDD16'], 'bookmark'],
  '1f517': [['\uD83D\uDD17'], 'link'],
  '1f518': [['\uD83D\uDD18'], 'radio_button'],
  '1f519': [['\uD83D\uDD19'], 'back'],
  '1f51a': [['\uD83D\uDD1A'], 'end'],
  '1f51b': [['\uD83D\uDD1B'], 'on'],
  '1f51c': [['\uD83D\uDD1C'], 'soon'],
  '1f51d': [['\uD83D\uDD1D'], 'top'],
  '1f51e': [['\uD83D\uDD1E'], 'underage'],
  '1f51f': [['\uD83D\uDD1F'], 'keycap_ten'],
  '1f520': [['\uD83D\uDD20'], 'capital_abcd'],
  '1f521': [['\uD83D\uDD21'], 'abcd'],
  '1f522': [['\uD83D\uDD22'], '1234'],
  '1f523': [['\uD83D\uDD23'], 'symbols'],
  '1f524': [['\uD83D\uDD24'], 'abc'],
  '1f525': [['\uD83D\uDD25'], 'fire'],
  '1f526': [['\uD83D\uDD26'], 'flashlight'],
  '1f527': [['\uD83D\uDD27'], 'wrench'],
  '1f528': [['\uD83D\uDD28'], 'hammer'],
  '1f529': [['\uD83D\uDD29'], 'nut_and_bolt'],
  '1f52a': [['\uD83D\uDD2A'], 'hocho'],
  '1f52b': [['\uD83D\uDD2B'], 'gun'],
  '1f52c': [['\uD83D\uDD2C'], 'microscope'],
  '1f52d': [['\uD83D\uDD2D'], 'telescope'],
  '1f52e': [['\uD83D\uDD2E'], 'crystal_ball'],
  '1f52f': [['\uD83D\uDD2F'], 'six_pointed_star'],
  '1f530': [['\uD83D\uDD30'], 'beginner'],
  '1f531': [['\uD83D\uDD31'], 'trident'],
  '1f532': [['\uD83D\uDD32'], 'black_square_button'],
  '1f533': [['\uD83D\uDD33'], 'white_square_button'],
  '1f534': [['\uD83D\uDD34'], 'red_circle'],
  '1f535': [['\uD83D\uDD35'], 'large_blue_circle'],
  '1f536': [['\uD83D\uDD36'], 'large_orange_diamond'],
  '1f537': [['\uD83D\uDD37'], 'large_blue_diamond'],
  '1f538': [['\uD83D\uDD38'], 'small_orange_diamond'],
  '1f539': [['\uD83D\uDD39'], 'small_blue_diamond'],
  '1f53a': [['\uD83D\uDD3A'], 'small_red_triangle'],
  '1f53b': [['\uD83D\uDD3B'], 'small_red_triangle_down'],
  '1f53c': [['\uD83D\uDD3C'], 'arrow_up_small'],
  '1f53d': [['\uD83D\uDD3D'], 'arrow_down_small'],
  '1f550': [['\uD83D\uDD50'], 'clock1'],
  '1f551': [['\uD83D\uDD51'], 'clock2'],
  '1f552': [['\uD83D\uDD52'], 'clock3'],
  '1f553': [['\uD83D\uDD53'], 'clock4'],
  '1f554': [['\uD83D\uDD54'], 'clock5'],
  '1f555': [['\uD83D\uDD55'], 'clock6'],
  '1f556': [['\uD83D\uDD56'], 'clock7'],
  '1f557': [['\uD83D\uDD57'], 'clock8'],
  '1f558': [['\uD83D\uDD58'], 'clock9'],
  '1f559': [['\uD83D\uDD59'], 'clock10'],
  '1f55a': [['\uD83D\uDD5A'], 'clock11'],
  '1f55b': [['\uD83D\uDD5B'], 'clock12'],
  '1f55c': [['\uD83D\uDD5C'], 'clock130'],
  '1f55d': [['\uD83D\uDD5D'], 'clock230'],
  '1f55e': [['\uD83D\uDD5E'], 'clock330'],
  '1f55f': [['\uD83D\uDD5F'], 'clock430'],
  '1f560': [['\uD83D\uDD60'], 'clock530'],
  '1f561': [['\uD83D\uDD61'], 'clock630'],
  '1f562': [['\uD83D\uDD62'], 'clock730'],
  '1f563': [['\uD83D\uDD63'], 'clock830'],
  '1f564': [['\uD83D\uDD64'], 'clock930'],
  '1f565': [['\uD83D\uDD65'], 'clock1030'],
  '1f566': [['\uD83D\uDD66'], 'clock1130'],
  '1f567': [['\uD83D\uDD67'], 'clock1230'],
  '1f5fb': [['\uD83D\uDDFB'], 'mount_fuji'],
  '1f5fc': [['\uD83D\uDDFC'], 'tokyo_tower'],
  '1f5fd': [['\uD83D\uDDFD'], 'statue_of_liberty'],
  '1f5fe': [['\uD83D\uDDFE'], 'japan'],
  '1f5ff': [['\uD83D\uDDFF'], 'moyai'],
  '1f600': [['\uD83D\uDE00'], 'grinning'],
  '1f601': [['\uD83D\uDE01'], 'grin'],
  '1f602': [['\uD83D\uDE02'], 'joy'],
  '1f603': [['\uD83D\uDE03'], 'smiley'],
  '1f604': [['\uD83D\uDE04'], 'smile'],
  '1f605': [['\uD83D\uDE05'], 'sweat_smile'],
  '1f606': [['\uD83D\uDE06'], 'satisfied'],
  '1f607': [['\uD83D\uDE07'], 'innocent'],
  '1f608': [['\uD83D\uDE08'], 'smiling_imp'],
  '1f609': [['\uD83D\uDE09'], 'wink'],
  '1f60a': [['\uD83D\uDE0A'], 'blush'],
  '1f60b': [['\uD83D\uDE0B'], 'yum'],
  '1f60c': [['\uD83D\uDE0C'], 'relieved'],
  '1f60d': [['\uD83D\uDE0D'], 'heart_eyes'],
  '1f60e': [['\uD83D\uDE0E'], 'sunglasses'],
  '1f60f': [['\uD83D\uDE0F'], 'smirk'],
  '1f610': [['\uD83D\uDE10'], 'neutral_face'],
  '1f611': [['\uD83D\uDE11'], 'expressionless'],
  '1f612': [['\uD83D\uDE12'], 'unamused'],
  '1f613': [['\uD83D\uDE13'], 'sweat'],
  '1f614': [['\uD83D\uDE14'], 'pensive'],
  '1f615': [['\uD83D\uDE15'], 'confused'],
  '1f616': [['\uD83D\uDE16'], 'confounded'],
  '1f617': [['\uD83D\uDE17'], 'kissing'],
  '1f618': [['\uD83D\uDE18'], 'kissing_heart'],
  '1f619': [['\uD83D\uDE19'], 'kissing_smiling_eyes'],
  '1f61a': [['\uD83D\uDE1A'], 'kissing_closed_eyes'],
  '1f61b': [['\uD83D\uDE1B'], 'stuck_out_tongue'],
  '1f61c': [['\uD83D\uDE1C'], 'stuck_out_tongue_winking_eye'],
  '1f61d': [['\uD83D\uDE1D'], 'stuck_out_tongue_closed_eyes'],
  '1f61e': [['\uD83D\uDE1E'], 'disappointed'],
  '1f61f': [['\uD83D\uDE1F'], 'worried'],
  '1f620': [['\uD83D\uDE20'], 'angry'],
  '1f621': [['\uD83D\uDE21'], 'rage'],
  '1f622': [['\uD83D\uDE22'], 'cry'],
  '1f623': [['\uD83D\uDE23'], 'persevere'],
  '1f624': [['\uD83D\uDE24'], 'triumph'],
  '1f625': [['\uD83D\uDE25'], 'disappointed_relieved'],
  '1f626': [['\uD83D\uDE26'], 'frowning'],
  '1f627': [['\uD83D\uDE27'], 'anguished'],
  '1f628': [['\uD83D\uDE28'], 'fearful'],
  '1f629': [['\uD83D\uDE29'], 'weary'],
  '1f62a': [['\uD83D\uDE2A'], 'sleepy'],
  '1f62b': [['\uD83D\uDE2B'], 'tired_face'],
  '1f62c': [['\uD83D\uDE2C'], 'grimacing'],
  '1f62d': [['\uD83D\uDE2D'], 'sob'],
  '1f62e': [['\uD83D\uDE2E'], 'open_mouth'],
  '1f62f': [['\uD83D\uDE2F'], 'hushed'],
  '1f630': [['\uD83D\uDE30'], 'cold_sweat'],
  '1f631': [['\uD83D\uDE31'], 'scream'],
  '1f632': [['\uD83D\uDE32'], 'astonished'],
  '1f633': [['\uD83D\uDE33'], 'flushed'],
  '1f634': [['\uD83D\uDE34'], 'sleeping'],
  '1f635': [['\uD83D\uDE35'], 'dizzy_face'],
  '1f636': [['\uD83D\uDE36'], 'no_mouth'],
  '1f637': [['\uD83D\uDE37'], 'mask'],
  '1f638': [['\uD83D\uDE38'], 'smile_cat'],
  '1f639': [['\uD83D\uDE39'], 'joy_cat'],
  '1f63a': [['\uD83D\uDE3A'], 'smiley_cat'],
  '1f63b': [['\uD83D\uDE3B'], 'heart_eyes_cat'],
  '1f63c': [['\uD83D\uDE3C'], 'smirk_cat'],
  '1f63d': [['\uD83D\uDE3D'], 'kissing_cat'],
  '1f63e': [['\uD83D\uDE3E'], 'pouting_cat'],
  '1f63f': [['\uD83D\uDE3F'], 'crying_cat_face'],
  '1f640': [['\uD83D\uDE40'], 'scream_cat'],
  '1f645': [['\uD83D\uDE45'], 'no_good'],
  '1f646': [['\uD83D\uDE46'], 'ok_woman'],
  '1f647': [['\uD83D\uDE47'], 'bow'],
  '1f648': [['\uD83D\uDE48'], 'see_no_evil'],
  '1f649': [['\uD83D\uDE49'], 'hear_no_evil'],
  '1f64a': [['\uD83D\uDE4A'], 'speak_no_evil'],
  '1f64b': [['\uD83D\uDE4B'], 'raising_hand'],
  '1f64c': [['\uD83D\uDE4C'], 'raised_hands'],
  '1f64d': [['\uD83D\uDE4D'], 'person_frowning'],
  '1f64e': [['\uD83D\uDE4E'], 'person_with_pouting_face'],
  '1f64f': [['\uD83D\uDE4F'], 'pray'],
  '1f680': [['\uD83D\uDE80'], 'rocket'],
  '1f681': [['\uD83D\uDE81'], 'helicopter'],
  '1f682': [['\uD83D\uDE82'], 'steam_locomotive'],
  '1f683': [['\uD83D\uDE83'], 'railway_car'],
  '1f684': [['\uD83D\uDE84'], 'bullettrain_side'],
  '1f685': [['\uD83D\uDE85'], 'bullettrain_front'],
  '1f686': [['\uD83D\uDE86'], 'train2'],
  '1f687': [['\uD83D\uDE87'], 'metro'],
  '1f688': [['\uD83D\uDE88'], 'light_rail'],
  '1f689': [['\uD83D\uDE89'], 'station'],
  '1f68a': [['\uD83D\uDE8A'], 'tram'],
  '1f68b': [['\uD83D\uDE8B'], 'train'],
  '1f68c': [['\uD83D\uDE8C'], 'bus'],
  '1f68d': [['\uD83D\uDE8D'], 'oncoming_bus'],
  '1f68e': [['\uD83D\uDE8E'], 'trolleybus'],
  '1f68f': [['\uD83D\uDE8F'], 'busstop'],
  '1f690': [['\uD83D\uDE90'], 'minibus'],
  '1f691': [['\uD83D\uDE91'], 'ambulance'],
  '1f692': [['\uD83D\uDE92'], 'fire_engine'],
  '1f693': [['\uD83D\uDE93'], 'police_car'],
  '1f694': [['\uD83D\uDE94'], 'oncoming_police_car'],
  '1f695': [['\uD83D\uDE95'], 'taxi'],
  '1f696': [['\uD83D\uDE96'], 'oncoming_taxi'],
  '1f697': [['\uD83D\uDE97'], 'car'],
  '1f698': [['\uD83D\uDE98'], 'oncoming_automobile'],
  '1f699': [['\uD83D\uDE99'], 'blue_car'],
  '1f69a': [['\uD83D\uDE9A'], 'truck'],
  '1f69b': [['\uD83D\uDE9B'], 'articulated_lorry'],
  '1f69c': [['\uD83D\uDE9C'], 'tractor'],
  '1f69d': [['\uD83D\uDE9D'], 'monorail'],
  '1f69e': [['\uD83D\uDE9E'], 'mountain_railway'],
  '1f69f': [['\uD83D\uDE9F'], 'suspension_railway'],
  '1f6a0': [['\uD83D\uDEA0'], 'mountain_cableway'],
  '1f6a1': [['\uD83D\uDEA1'], 'aerial_tramway'],
  '1f6a2': [['\uD83D\uDEA2'], 'ship'],
  '1f6a3': [['\uD83D\uDEA3'], 'rowboat'],
  '1f6a4': [['\uD83D\uDEA4'], 'speedboat'],
  '1f6a5': [['\uD83D\uDEA5'], 'traffic_light'],
  '1f6a6': [['\uD83D\uDEA6'], 'vertical_traffic_light'],
  '1f6a7': [['\uD83D\uDEA7'], 'construction'],
  '1f6a8': [['\uD83D\uDEA8'], 'rotating_light'],
  '1f6a9': [['\uD83D\uDEA9'], 'triangular_flag_on_post'],
  '1f6aa': [['\uD83D\uDEAA'], 'door'],
  '1f6ab': [['\uD83D\uDEAB'], 'no_entry_sign'],
  '1f6ac': [['\uD83D\uDEAC'], 'smoking'],
  '1f6ad': [['\uD83D\uDEAD'], 'no_smoking'],
  '1f6ae': [['\uD83D\uDEAE'], 'put_litter_in_its_place'],
  '1f6af': [['\uD83D\uDEAF'], 'do_not_litter'],
  '1f6b0': [['\uD83D\uDEB0'], 'potable_water'],
  '1f6b1': [['\uD83D\uDEB1'], 'non-potable_water'],
  '1f6b2': [['\uD83D\uDEB2'], 'bike'],
  '1f6b3': [['\uD83D\uDEB3'], 'no_bicycles'],
  '1f6b4': [['\uD83D\uDEB4'], 'bicyclist'],
  '1f6b5': [['\uD83D\uDEB5'], 'mountain_bicyclist'],
  '1f6b6': [['\uD83D\uDEB6'], 'walking'],
  '1f6b7': [['\uD83D\uDEB7'], 'no_pedestrians'],
  '1f6b8': [['\uD83D\uDEB8'], 'children_crossing'],
  '1f6b9': [['\uD83D\uDEB9'], 'mens'],
  '1f6ba': [['\uD83D\uDEBA'], 'womens'],
  '1f6bb': [['\uD83D\uDEBB'], 'restroom'],
  '1f6bc': [['\uD83D\uDEBC'], 'baby_symbol'],
  '1f6bd': [['\uD83D\uDEBD'], 'toilet'],
  '1f6be': [['\uD83D\uDEBE'], 'wc'],
  '1f6bf': [['\uD83D\uDEBF'], 'shower'],
  '1f6c0': [['\uD83D\uDEC0'], 'bath'],
  '1f6c1': [['\uD83D\uDEC1'], 'bathtub'],
  '1f6c2': [['\uD83D\uDEC2'], 'passport_control'],
  '1f6c3': [['\uD83D\uDEC3'], 'customs'],
  '1f6c4': [['\uD83D\uDEC4'], 'baggage_claim'],
  '1f6c5': [['\uD83D\uDEC5'], 'left_luggage'],
  '0023-20e3': [['#\uFE0F\u20E3', '#\u20E3'], 'hash'],
  '0030-20e3': [['0\uFE0F\u20E3', '0\u20E3'], 'zero'],
  '0031-20e3': [['1\uFE0F\u20E3', '1\u20E3'], 'one'],
  '0032-20e3': [['2\uFE0F\u20E3', '2\u20E3'], 'two'],
  '0033-20e3': [['3\uFE0F\u20E3', '3\u20E3'], 'three'],
  '0034-20e3': [['4\uFE0F\u20E3', '4\u20E3'], 'four'],
  '0035-20e3': [['5\uFE0F\u20E3', '5\u20E3'], 'five'],
  '0036-20e3': [['6\uFE0F\u20E3', '6\u20E3'], 'six'],
  '0037-20e3': [['7\uFE0F\u20E3', '7\u20E3'], 'seven'],
  '0038-20e3': [['8\uFE0F\u20E3', '8\u20E3'], 'eight'],
  '0039-20e3': [['9\uFE0F\u20E3', '9\u20E3'], 'nine'],
  '1f1e8-1f1f3': [['\uD83C\uDDE8\uD83C\uDDF3'], 'cn'],
  '1f1e9-1f1ea': [['\uD83C\uDDE9\uD83C\uDDEA'], 'de'],
  '1f1ea-1f1f8': [['\uD83C\uDDEA\uD83C\uDDF8'], 'es'],
  '1f1eb-1f1f7': [['\uD83C\uDDEB\uD83C\uDDF7'], 'fr'],
  '1f1ec-1f1e7': [['\uD83C\uDDEC\uD83C\uDDE7'], 'gb'],
  '1f1ee-1f1f9': [['\uD83C\uDDEE\uD83C\uDDF9'], 'it'],
  '1f1ef-1f1f5': [['\uD83C\uDDEF\uD83C\uDDF5'], 'jp'],
  '1f1f0-1f1f7': [['\uD83C\uDDF0\uD83C\uDDF7'], 'kr'],
  '1f1f7-1f1fa': [['\uD83C\uDDF7\uD83C\uDDFA'], 'ru'],
  '1f1fa-1f1f8': [['\uD83C\uDDFA\uD83C\uDDF8'], 'us']
};

var groups = [{
  name: 'smile',
  items: ['1f604', '1f603', '1f600', '1f60a', '263a', '1f609', '1f60d', '1f618', '1f61a', '1f617', '1f619', '1f61c', '1f61d', '1f61b', '1f633', '1f601', '1f614', '1f60c', '1f612', '1f61e', '1f623', '1f622', '1f602', '1f62d', '1f62a', '1f625', '1f630', '1f605', '1f613', '1f629', '1f62b', '1f628', '1f631', '1f620', '1f621', '1f624', '1f616', '1f606', '1f60b', '1f637', '1f60e', '1f634', '1f635', '1f632', '1f61f', '1f626', '1f627', '1f608', '1f47f', '1f62e', '1f62c', '1f610', '1f615', '1f62f', '1f636', '1f607', '1f60f', '1f611', '1f472', '1f473', '1f46e', '1f477', '1f482', '1f476', '1f466', '1f467', '1f468', '1f469', '1f474', '1f475', '1f471', '1f47c', '1f478', '1f63a', '1f638', '1f63b', '1f63d', '1f63c', '1f640', '1f63f', '1f639', '1f63e', '1f479', '1f47a', '1f648', '1f649', '1f64a', '1f480', '1f47d', '1f4a9', '1f525', '2728', '1f31f', '1f4ab', '1f4a5', '1f4a2', '1f4a6', '1f4a7', '1f4a4', '1f4a8', '1f442', '1f440', '1f443', '1f445', '1f444', '1f44d', '1f44e', '1f44c', '1f44a', '270a', '270c', '1f44b', '270b', '1f450', '1f446', '1f447', '1f449', '1f448', '1f64c', '1f64f', '261d', '1f44f', '1f4aa', '1f6b6', '1f3c3', '1f483', '1f46b', '1f46a', '1f46c', '1f46d', '1f48f', '1f491', '1f46f', '1f646', '1f645', '1f481', '1f64b', '1f486', '1f487', '1f485', '1f470', '1f64e', '1f64d', '1f647', '1f3a9', '1f451', '1f452', '1f45f', '1f45e', '1f461', '1f460', '1f462', '1f455', '1f454', '1f45a', '1f457', '1f3bd', '1f456', '1f458', '1f459', '1f4bc', '1f45c', '1f45d', '1f45b', '1f453', '1f380', '1f302', '1f484', '1f49b', '1f499', '1f49c', '1f49a', '2764', '1f494', '1f497', '1f493', '1f495', '1f496', '1f49e', '1f498', '1f48c', '1f48b', '1f48d', '1f48e', '1f464', '1f465', '1f4ac', '1f463', '1f4ad'],
  sprite: 'emoji_spritesheet_0.png',
  dimensions: [27, 7]
}, {
  name: 'dog',
  items: ['1f436', '1f43a', '1f431', '1f42d', '1f439', '1f430', '1f438', '1f42f', '1f428', '1f43b', '1f437', '1f43d', '1f42e', '1f417', '1f435', '1f412', '1f434', '1f411', '1f418', '1f43c', '1f427', '1f426', '1f424', '1f425', '1f423', '1f414', '1f40d', '1f422', '1f41b', '1f41d', '1f41c', '1f41e', '1f40c', '1f419', '1f41a', '1f420', '1f41f', '1f42c', '1f433', '1f40b', '1f404', '1f40f', '1f400', '1f403', '1f405', '1f407', '1f409', '1f40e', '1f410', '1f413', '1f415', '1f416', '1f401', '1f402', '1f432', '1f421', '1f40a', '1f42b', '1f42a', '1f406', '1f408', '1f429', '1f43e', '1f490', '1f338', '1f337', '1f340', '1f339', '1f33b', '1f33a', '1f341', '1f343', '1f342', '1f33f', '1f33e', '1f344', '1f335', '1f334', '1f332', '1f333', '1f330', '1f331', '1f33c', '1f310', '1f31e', '1f31d', '1f31a', '1f311', '1f312', '1f313', '1f314', '1f315', '1f316', '1f317', '1f318', '1f31c', '1f31b', '1f319', '1f30d', '1f30e', '1f30f', '1f30b', '1f30c', '1f320', '2b50', '2600', '26c5', '2601', '26a1', '2614', '2744', '26c4', '1f300', '1f301', '1f308', '1f30a'],
  sprite: 'emoji_spritesheet_1.png',
  dimensions: [29, 4]
}, {
  name: 'bell',
  items: ['1f38d', '1f49d', '1f38e', '1f392', '1f393', '1f38f', '1f386', '1f387', '1f390', '1f391', '1f383', '1f47b', '1f385', '1f384', '1f381', '1f38b', '1f389', '1f38a', '1f388', '1f38c', '1f52e', '1f3a5', '1f4f7', '1f4f9', '1f4fc', '1f4bf', '1f4c0', '1f4bd', '1f4be', '1f4bb', '1f4f1', '260e', '1f4de', '1f4df', '1f4e0', '1f4e1', '1f4fa', '1f4fb', '1f50a', '1f509', '1f508', '1f507', '1f514', '1f515', '1f4e3', '1f4e2', '23f3', '231b', '23f0', '231a', '1f513', '1f512', '1f50f', '1f510', '1f511', '1f50e', '1f4a1', '1f526', '1f506', '1f505', '1f50c', '1f50b', '1f50d', '1f6c0', '1f6c1', '1f6bf', '1f6bd', '1f527', '1f529', '1f528', '1f6aa', '1f6ac', '1f4a3', '1f52b', '1f52a', '1f48a', '1f489', '1f4b0', '1f4b4', '1f4b5', '1f4b7', '1f4b6', '1f4b3', '1f4b8', '1f4f2', '1f4e7', '1f4e5', '1f4e4', '2709', '1f4e9', '1f4e8', '1f4ef', '1f4eb', '1f4ea', '1f4ec', '1f4ed', '1f4ee', '1f4e6', '1f4dd', '1f4c4', '1f4c3', '1f4d1', '1f4ca', '1f4c8', '1f4c9', '1f4dc', '1f4cb', '1f4c5', '1f4c6', '1f4c7', '1f4c1', '1f4c2', '2702', '1f4cc', '1f4ce', '2712', '270f', '1f4cf', '1f4d0', '1f4d5', '1f4d7', '1f4d8', '1f4d9', '1f4d3', '1f4d4', '1f4d2', '1f4da', '1f4d6', '1f516', '1f4db', '1f52c', '1f52d', '1f4f0', '1f3a8', '1f3ac', '1f3a4', '1f3a7', '1f3bc', '1f3b5', '1f3b6', '1f3b9', '1f3bb', '1f3ba', '1f3b7', '1f3b8', '1f47e', '1f3ae', '1f0cf', '1f3b4', '1f004', '1f3b2', '1f3af', '1f3c8', '1f3c0', '26bd', '26be', '1f3be', '1f3b1', '1f3c9', '1f3b3', '26f3', '1f6b5', '1f6b4', '1f3c1', '1f3c7', '1f3c6', '1f3bf', '1f3c2', '1f3ca', '1f3c4', '1f3a3', '2615', '1f375', '1f376', '1f37c', '1f37a', '1f37b', '1f378', '1f379', '1f377', '1f374', '1f355', '1f354', '1f35f', '1f357', '1f356', '1f35d', '1f35b', '1f364', '1f371', '1f363', '1f365', '1f359', '1f358', '1f35a', '1f35c', '1f372', '1f362', '1f361', '1f373', '1f35e', '1f369', '1f36e', '1f366', '1f368', '1f367', '1f382', '1f370', '1f36a', '1f36b', '1f36c', '1f36d', '1f36f', '1f34e', '1f34f', '1f34a', '1f34b', '1f352', '1f347', '1f349', '1f353', '1f351', '1f348', '1f34c', '1f350', '1f34d', '1f360', '1f346', '1f345', '1f33d'],
  sprite: 'emoji_spritesheet_2.png',
  dimensions: [33, 7]
}, {
  name: 'car',
  items: ['1f3e0', '1f3e1', '1f3eb', '1f3e2', '1f3e3', '1f3e5', '1f3e6', '1f3ea', '1f3e9', '1f3e8', '1f492', '26ea', '1f3ec', '1f3e4', '1f307', '1f306', '1f3ef', '1f3f0', '26fa', '1f3ed', '1f5fc', '1f5fe', '1f5fb', '1f304', '1f305', '1f303', '1f5fd', '1f309', '1f3a0', '1f3a1', '26f2', '1f3a2', '1f6a2', '26f5', '1f6a4', '1f6a3', '2693', '1f680', '2708', '1f4ba', '1f681', '1f682', '1f68a', '1f689', '1f69e', '1f686', '1f684', '1f685', '1f688', '1f687', '1f69d', '1f683', '1f68b', '1f68e', '1f68c', '1f68d', '1f699', '1f698', '1f697', '1f695', '1f696', '1f69b', '1f69a', '1f6a8', '1f693', '1f694', '1f692', '1f691', '1f690', '1f6b2', '1f6a1', '1f69f', '1f6a0', '1f69c', '1f488', '1f68f', '1f3ab', '1f6a6', '1f6a5', '26a0', '1f6a7', '1f530', '26fd', '1f3ee', '1f3b0', '2668', '1f5ff', '1f3aa', '1f3ad', '1f4cd', '1f6a9', '1f1ef-1f1f5', '1f1f0-1f1f7', '1f1e9-1f1ea', '1f1e8-1f1f3', '1f1fa-1f1f8', '1f1eb-1f1f7', '1f1ea-1f1f8', '1f1ee-1f1f9', '1f1f7-1f1fa', '1f1ec-1f1e7'],
  sprite: 'emoji_spritesheet_3.png',
  dimensions: [34, 3]
}, {
  name: 'clock2',
  items: ['0031', '0032', '0033', '0034', '0035', '0036', '0037', '0038', '0039', '0030', '1f51f', '1f522', '0023', '1f523', '2b06', '2b07', '2b05', '27a1', '1f520', '1f521', '1f524', '2197', '2196', '2198', '2199', '2194', '2195', '1f504', '25c0', '25b6', '1f53c', '1f53d', '21a9', '21aa', '2139', '23ea', '23e9', '23eb', '23ec', '2935', '2934', '1f197', '1f500', '1f501', '1f502', '1f195', '1f199', '1f192', '1f193', '1f196', '1f4f6', '1f3a6', '1f201', '1f22f', '1f233', '1f235', '1f234', '1f232', '1f250', '1f239', '1f23a', '1f236', '1f21a', '1f6bb', '1f6b9', '1f6ba', '1f6bc', '1f6be', '1f6b0', '1f6ae', '1f17f', '267f', '1f6ad', '1f237', '1f238', '1f202', '24c2', '1f6c2', '1f6c4', '1f6c5', '1f6c3', '1f251', '3299', '3297', '1f191', '1f198', '1f194', '1f6ab', '1f51e', '1f4f5', '1f6af', '1f6b1', '1f6b3', '1f6b7', '1f6b8', '26d4', '2733', '2747', '274e', '2705', '2734', '1f49f', '1f19a', '1f4f3', '1f4f4', '1f170', '1f171', '1f18e', '1f17e', '1f4a0', '27bf', '267b', '2648', '2649', '264a', '264b', '264c', '264d', '264e', '264f', '2650', '2651', '2652', '2653', '26ce', '1f52f', '1f3e7', '1f4b9', '1f4b2', '1f4b1', '00a9', '00ae', '2122', '274c', '203c', '2049', '2757', '2753', '2755', '2754', '2b55', '1f51d', '1f51a', '1f519', '1f51b', '1f51c', '1f503', '1f55b', '1f567', '1f550', '1f55c', '1f551', '1f55d', '1f552', '1f55e', '1f553', '1f55f', '1f554', '1f560', '1f555', '1f556', '1f557', '1f558', '1f559', '1f55a', '1f561', '1f562', '1f563', '1f564', '1f565', '1f566', '2716', '2795', '2796', '2797', '2660', '2665', '2663', '2666', '1f4ae', '1f4af', '2714', '2611', '1f518', '1f517', '27b0', '3030', '303d', '1f531', '25fc', '25fb', '25fe', '25fd', '25aa', '25ab', '1f53a', '1f532', '1f533', '26ab', '26aa', '1f534', '1f535', '1f53b', '2b1c', '2b1b', '1f536', '1f537', '1f538', '1f539'],
  sprite: 'emoji_spritesheet_4.png',
  dimensions: [34, 7]
}];

var ascii = {
  '<3': 'heart',
  '<\/3': 'broken_heart',
  ':)': 'blush',
  ':-)': 'blush',
  ':D': 'smile',
  ':-D': 'smile',
  ';)': 'wink',
  ';-)': 'wink',
  ':(': 'disappointed',
  ':-(': 'disappointed',
  ':\'(': 'cry',
  '=)': 'smiley',
  '=-)': 'smiley',
  ':*': 'kiss',
  ':-*': 'kiss',
  ':>': 'laughing',
  ':->': 'laughing',
  '8)': 'sunglasses',
  ':\\\\': 'confused',
  ':-\\\\': 'confused',
  ':\/': 'confused',
  ':-\/': 'confused',
  ':|': 'neutral_face',
  ':-|': 'neutral_face',
  ':o': 'open_mouth',
  ':-o': 'open_mouth',
  '>:(': 'angry',
  '>:-(': 'angry',
  ':p': 'stuck_out_tongue',
  ':P': 'stuck_out_tongue',
  ':-p': 'stuck_out_tongue',
  ':-P': 'stuck_out_tongue',
  ';p': 'stuck_out_tongue_winking_eye',
  ';P': 'stuck_out_tongue_winking_eye',
  ';-p': 'stuck_out_tongue_winking_eye',
  ';-P': 'stuck_out_tongue_winking_eye',
  ':o)': 'monkey_face',
  '(y)': 'thumb_up'
};

exports.default = { data: data, groups: groups, ascii: ascii };

/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }(); /**
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      * Emoji Picker (Dropdown) can work as global singleton (one dropdown for all inputs on the page)
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      * or with separate instances (and settings) for each input.
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      *
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      * @author Wolfgang Stöttinger
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      */


var _jquery = __webpack_require__(0);

var _jquery2 = _interopRequireDefault(_jquery);

var _EmojiArea = __webpack_require__(2);

var _EmojiArea2 = _interopRequireDefault(_EmojiArea);

var _EmojiUtil = __webpack_require__(1);

var _EmojiUtil2 = _interopRequireDefault(_EmojiUtil);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var EmojiPicker = function () {
  function EmojiPicker() {
    var _this = this;

    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : _EmojiArea2.default.DEFAULTS;

    _classCallCheck(this, EmojiPicker);

    this.o = options;
    var $body = (0, _jquery2.default)(document.body);
    $body.on('keydown', function (e) {
      if (e.keyCode === KEY_ESC || e.keyCode === KEY_TAB) _this.hide();
    });
    $body.on('click', function () {
      _this.hide();
    });
    (0, _jquery2.default)(window).on('resize', function () {
      if (_this.$p.is(':visible')) {
        _this.reposition();
      }
    });

    this.$p = (0, _jquery2.default)('<div>').addClass('emoji-picker').attr('data-picker-type', options.type) // $.data() here not possible, doesn't change dom
    .on('mouseup click', function (e) {
      return e.stopPropagation() && false;
    }).hide().appendTo($body);

    var tabs = this.loadPicker();
    setTimeout(this.loadEmojis.bind(this, tabs), 100);
  }

  _createClass(EmojiPicker, [{
    key: 'loadPicker',
    value: function loadPicker() {
      var _this2 = this;

      var ul = (0, _jquery2.default)('<ul>').addClass('emoji-selector nav nav-tabs');
      var tabs = (0, _jquery2.default)('<div>').addClass('tab-content');

      var _loop = function _loop(g) {
        var group = _EmojiUtil2.default.groups[g];
        var id = 'group_' + group.name;
        var gid = '#' + id;

        var a = (0, _jquery2.default)('<a>').html(_EmojiArea2.default.createEmoji(group.name, _this2.o)).data('toggle', 'tab').attr('href', gid);

        ul.append((0, _jquery2.default)('<li>').append(a));

        var tab = (0, _jquery2.default)('<div>').attr('id', id).addClass('emoji-group tab-pane').data('group', group.name);

        a.on('click', function (e) {
          (0, _jquery2.default)('.tab-pane').not(tab).hide().removeClass('active');
          tab.addClass('active').show();
          e.preventDefault();
        });
        tabs.append(tab);
      };

      for (var g = 0; g < _EmojiUtil2.default.groups.length; g++) {
        _loop(g);
      }

      tabs.find('.tab-pane').not(':first-child').hide().removeClass('active');

      this.$p.append(ul).append(tabs);
      return tabs.children();
    }
  }, {
    key: 'loadEmojis',
    value: function loadEmojis(tabs) {
      var _this3 = this;

      for (var g = 0; g < _EmojiUtil2.default.groups.length; g++) {
        var group = _EmojiUtil2.default.groups[g];
        var _tab = tabs[g];
        for (var e = 0; e < group.items.length; e++) {
          var emojiId = group.items[e];
          if (_EmojiUtil2.default.data.hasOwnProperty(emojiId)) {
            (function () {
              var word = _EmojiUtil2.default.data[emojiId][_EmojiUtil2.default.EMOJI_ALIASES] || '';
              var emojiElem = (0, _jquery2.default)('<a>').data('emoji', word).html(_EmojiArea2.default.createEmoji(word, _this3.o)).on('click', function () {
                _this3.insertEmoji(word);
              });
              (0, _jquery2.default)(_tab).append(emojiElem);
            })();
          }
        }
      }
    }
  }, {
    key: 'insertEmoji',
    value: function insertEmoji(emoji) {
      if (typeof this.cb === 'function') this.cb(emoji, this.o);
      this.hide();
    }
  }, {
    key: 'reposition',
    value: function reposition(anchor, options) {
      if (!anchor || anchor.length === 0) return;

      var $anchor = (0, _jquery2.default)(anchor);
      var anchorOffset = $anchor.offset();
      anchorOffset.right = anchorOffset.left + anchor.outerWidth() - this.$p.outerWidth();
      this.$p.css({
        top: anchorOffset.top + anchor.outerHeight() + (options.anchorOffsetY || 0),
        left: anchorOffset[options.anchorAlignment] + (options.anchorOffsetX || 0)
      });
    }
  }, {
    key: 'show',
    value: function show(insertCallback, anchor, options) {
      this.cb = insertCallback;
      this.reposition(anchor, options);
      this.$p.attr('data-picker-type', options.type); // $.data() here not possible, doesn't change dom
      this.$p.show();
      return this;
    }
  }, {
    key: 'hide',
    value: function hide() {
      this.$p.hide();
    }
  }, {
    key: 'isVisible',
    value: function isVisible() {
      return this.$p.is(':visible');
    }
  }]);

  return EmojiPicker;
}();

exports.default = EmojiPicker;


EmojiPicker.globalPicker = null;

EmojiPicker.show = function (insertCallback, anchor) {
  var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : _EmojiArea2.default.DEFAULTS;

  var picker = EmojiPicker.globalPicker;
  if (!options.globalPicker) picker = new EmojiPicker(options);
  if (!picker) picker = EmojiPicker.globalPicker = new EmojiPicker(options);
  picker.show(insertCallback, anchor, options);
  return picker;
};

EmojiPicker.isVisible = function () {
  return EmojiPicker.globalPicker && EmojiPicker.globalPicker.isVisible();
};

EmojiPicker.hide = function () {
  !EmojiPicker.globalPicker || EmojiPicker.globalPicker.hide();
};

var KEY_ESC = 27;
var KEY_TAB = 9;

/***/ })
/******/ ]);
//# sourceMappingURL=jquery.emojiarea.js.map