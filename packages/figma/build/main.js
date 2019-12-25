/******/ (function(modules) { // webpackBootstrap
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
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
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
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/code.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/code.js":
/*!*********************!*\
  !*** ./src/code.js ***!
  \*********************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _utils_types__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/utils/types */ "./src/utils/types.js");


const storageKey            = 'presentator_storage';
const defaultWidth          = 450;
const defaultHeight         = 390;
const defaultExportSettings = {
    format: 'PNG',
    contentsOnly: true,
    constraint: {
        type: 'SCALE',
        value: 1,
    },
};

// loads storage data and initializes the ui
async function initUI() {
    let storageData;
    try {
        storageData = await figma.clientStorage.getAsync(storageKey);
    } catch (e) {
        console.log('Storage init error:', e);
    }

    figma.showUI(__html__, { width: defaultWidth, height: defaultHeight });

    figma.ui.postMessage({
        type: _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_INIT_APP,
        data: storageData || {},
    });
}

// returns visible frame nodes
function getFrames(fromSelection = false) {
    let result = [];
    let nodes  = (fromSelection ? figma.currentPage.selection : figma.currentPage.children) || [];

    for (let i = 0; i < nodes.length; i++) {
        if (nodes[i].type === 'FRAME' && nodes[i].visible) {
            result.push({
                node:   nodes[i],
                id:     nodes[i].id,
                name:   nodes[i].name,
                width:  nodes[i].width,
                height: nodes[i].height,
            });
        }
    }

    return result;
}

// returns single frame by its node id
function getFrameById(id) {
    const frames = getFrames();

    for (let i = frames.length - 1; i >= 0; i--) {
        if (frames[i].id == id) {
            return frames[i];
        }
    }

    return null;
}

// exports frame node data
async function exportFrame(id, additionalSettings) {
    try {
        const frame = getFrameById(id);

        if (frame && frame.node) {
            return await frame.node.exportAsync(Object.assign({}, defaultExportSettings, additionalSettings || {}));
        }
    } catch (e) {
        console.log('Export frame error:', e);
    }

    return null;
}

initUI();

figma.ui.onmessage = async (message) => {
    if (typeof message !== 'object' || message === null || !message.type) {
        return;
    }

    if (message.type === _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_SAVE_STORAGE) {
        figma.clientStorage.setAsync(storageKey, message.data || {});
    } else if (message.type === _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_CLOSE) {
        figma.closePlugin();
    } else if (message.type === _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_NOTIFY) {
        figma.notify(message.data.message, {
            timeout: (message.data.timeout || 4000) << 0,
        });
    } else if (message.type === _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_RESIZE_UI) {
        figma.ui.resize(
            (message.data.width || defaultWidth) << 0,
            (message.data.height || defaultHeight) << 0,
        );
    } else if (message.type === _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_GET_FRAMES) {
        let frames = getFrames(message.data.onlySelected);

        // send the result to the ui
        figma.ui.postMessage({
            state: message.state,
            type:  _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_GET_FRAMES_RESPONSE,
            data:  frames,
        });
    } else if (message.type === _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_EXPORT_FRAME) {
        let data = await exportFrame(message.data.id);

        // send the result to the ui
        figma.ui.postMessage({
            state: message.state,
            type:  _utils_types__WEBPACK_IMPORTED_MODULE_0__["default"].MESSAGE_EXPORT_FRAME_RESPONSE,
            data:  data,
        });
    }
}


/***/ }),

/***/ "./src/utils/types.js":
/*!****************************!*\
  !*** ./src/utils/types.js ***!
  \****************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// list with plugin messages types:
/* harmony default export */ __webpack_exports__["default"] = ({
    MESSAGE_INIT_APP:              'init_app',
    MESSAGE_NOTIFY:                'notify',
    MESSAGE_CLOSE:                 'close',
    MESSAGE_RESIZE_UI:             'resize_ui',
    MESSAGE_GET_FRAMES:            'get_frames',
    MESSAGE_GET_FRAMES_RESPONSE:   'get_frames_response',
    MESSAGE_EXPORT_FRAME:          'export_frame',
    MESSAGE_EXPORT_FRAME_RESPONSE: 'export_frame_response',
    MESSAGE_SAVE_STORAGE:          'save_storage',
    MESSAGE_SAVE_STORAGE:          'save_storage',
});


/***/ })

/******/ });
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vLy4vc3JjL2NvZGUuanMiLCJ3ZWJwYWNrOi8vLy4vc3JjL3V0aWxzL3R5cGVzLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiI7UUFBQTtRQUNBOztRQUVBO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7O1FBRUE7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTs7O1FBR0E7UUFDQTs7UUFFQTtRQUNBOztRQUVBO1FBQ0E7UUFDQTtRQUNBLDBDQUEwQyxnQ0FBZ0M7UUFDMUU7UUFDQTs7UUFFQTtRQUNBO1FBQ0E7UUFDQSx3REFBd0Qsa0JBQWtCO1FBQzFFO1FBQ0EsaURBQWlELGNBQWM7UUFDL0Q7O1FBRUE7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBO1FBQ0E7UUFDQTtRQUNBLHlDQUF5QyxpQ0FBaUM7UUFDMUUsZ0hBQWdILG1CQUFtQixFQUFFO1FBQ3JJO1FBQ0E7O1FBRUE7UUFDQTtRQUNBO1FBQ0EsMkJBQTJCLDBCQUEwQixFQUFFO1FBQ3ZELGlDQUFpQyxlQUFlO1FBQ2hEO1FBQ0E7UUFDQTs7UUFFQTtRQUNBLHNEQUFzRCwrREFBK0Q7O1FBRXJIO1FBQ0E7OztRQUdBO1FBQ0E7Ozs7Ozs7Ozs7Ozs7QUNsRkE7QUFBQTtBQUFrQzs7QUFFbEM7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsS0FBSztBQUNMOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSxLQUFLO0FBQ0w7QUFDQTs7QUFFQSw0QkFBNEIsNkNBQTZDOztBQUV6RTtBQUNBLGNBQWMsb0RBQUs7QUFDbkIsK0JBQStCO0FBQy9CLEtBQUs7QUFDTDs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQSxtQkFBbUIsa0JBQWtCO0FBQ3JDO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsYUFBYTtBQUNiO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7O0FBRUEsbUNBQW1DLFFBQVE7QUFDM0M7QUFDQTtBQUNBO0FBQ0E7O0FBRUE7QUFDQTs7QUFFQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBLGdFQUFnRSxpREFBaUQ7QUFDakg7QUFDQSxLQUFLO0FBQ0w7QUFDQTs7QUFFQTtBQUNBOztBQUVBOztBQUVBO0FBQ0E7QUFDQTtBQUNBOztBQUVBLHlCQUF5QixvREFBSztBQUM5QixtRUFBbUU7QUFDbkUsS0FBSywyQkFBMkIsb0RBQUs7QUFDckM7QUFDQSxLQUFLLDJCQUEyQixvREFBSztBQUNyQztBQUNBO0FBQ0EsU0FBUztBQUNULEtBQUssMkJBQTJCLG9EQUFLO0FBQ3JDO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsS0FBSywyQkFBMkIsb0RBQUs7QUFDckM7O0FBRUE7QUFDQTtBQUNBO0FBQ0EsbUJBQW1CLG9EQUFLO0FBQ3hCO0FBQ0EsU0FBUztBQUNULEtBQUssMkJBQTJCLG9EQUFLO0FBQ3JDOztBQUVBO0FBQ0E7QUFDQTtBQUNBLG1CQUFtQixvREFBSztBQUN4QjtBQUNBLFNBQVM7QUFDVDtBQUNBOzs7Ozs7Ozs7Ozs7O0FDdEhBO0FBQUE7QUFDZTtBQUNmO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EsQ0FBQyIsImZpbGUiOiJtYWluLmpzIiwic291cmNlc0NvbnRlbnQiOlsiIFx0Ly8gVGhlIG1vZHVsZSBjYWNoZVxuIFx0dmFyIGluc3RhbGxlZE1vZHVsZXMgPSB7fTtcblxuIFx0Ly8gVGhlIHJlcXVpcmUgZnVuY3Rpb25cbiBcdGZ1bmN0aW9uIF9fd2VicGFja19yZXF1aXJlX18obW9kdWxlSWQpIHtcblxuIFx0XHQvLyBDaGVjayBpZiBtb2R1bGUgaXMgaW4gY2FjaGVcbiBcdFx0aWYoaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0pIHtcbiBcdFx0XHRyZXR1cm4gaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0uZXhwb3J0cztcbiBcdFx0fVxuIFx0XHQvLyBDcmVhdGUgYSBuZXcgbW9kdWxlIChhbmQgcHV0IGl0IGludG8gdGhlIGNhY2hlKVxuIFx0XHR2YXIgbW9kdWxlID0gaW5zdGFsbGVkTW9kdWxlc1ttb2R1bGVJZF0gPSB7XG4gXHRcdFx0aTogbW9kdWxlSWQsXG4gXHRcdFx0bDogZmFsc2UsXG4gXHRcdFx0ZXhwb3J0czoge31cbiBcdFx0fTtcblxuIFx0XHQvLyBFeGVjdXRlIHRoZSBtb2R1bGUgZnVuY3Rpb25cbiBcdFx0bW9kdWxlc1ttb2R1bGVJZF0uY2FsbChtb2R1bGUuZXhwb3J0cywgbW9kdWxlLCBtb2R1bGUuZXhwb3J0cywgX193ZWJwYWNrX3JlcXVpcmVfXyk7XG5cbiBcdFx0Ly8gRmxhZyB0aGUgbW9kdWxlIGFzIGxvYWRlZFxuIFx0XHRtb2R1bGUubCA9IHRydWU7XG5cbiBcdFx0Ly8gUmV0dXJuIHRoZSBleHBvcnRzIG9mIHRoZSBtb2R1bGVcbiBcdFx0cmV0dXJuIG1vZHVsZS5leHBvcnRzO1xuIFx0fVxuXG5cbiBcdC8vIGV4cG9zZSB0aGUgbW9kdWxlcyBvYmplY3QgKF9fd2VicGFja19tb2R1bGVzX18pXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLm0gPSBtb2R1bGVzO1xuXG4gXHQvLyBleHBvc2UgdGhlIG1vZHVsZSBjYWNoZVxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5jID0gaW5zdGFsbGVkTW9kdWxlcztcblxuIFx0Ly8gZGVmaW5lIGdldHRlciBmdW5jdGlvbiBmb3IgaGFybW9ueSBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLmQgPSBmdW5jdGlvbihleHBvcnRzLCBuYW1lLCBnZXR0ZXIpIHtcbiBcdFx0aWYoIV9fd2VicGFja19yZXF1aXJlX18ubyhleHBvcnRzLCBuYW1lKSkge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBuYW1lLCB7IGVudW1lcmFibGU6IHRydWUsIGdldDogZ2V0dGVyIH0pO1xuIFx0XHR9XG4gXHR9O1xuXG4gXHQvLyBkZWZpbmUgX19lc01vZHVsZSBvbiBleHBvcnRzXG4gXHRfX3dlYnBhY2tfcmVxdWlyZV9fLnIgPSBmdW5jdGlvbihleHBvcnRzKSB7XG4gXHRcdGlmKHR5cGVvZiBTeW1ib2wgIT09ICd1bmRlZmluZWQnICYmIFN5bWJvbC50b1N0cmluZ1RhZykge1xuIFx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBTeW1ib2wudG9TdHJpbmdUYWcsIHsgdmFsdWU6ICdNb2R1bGUnIH0pO1xuIFx0XHR9XG4gXHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCAnX19lc01vZHVsZScsIHsgdmFsdWU6IHRydWUgfSk7XG4gXHR9O1xuXG4gXHQvLyBjcmVhdGUgYSBmYWtlIG5hbWVzcGFjZSBvYmplY3RcbiBcdC8vIG1vZGUgJiAxOiB2YWx1ZSBpcyBhIG1vZHVsZSBpZCwgcmVxdWlyZSBpdFxuIFx0Ly8gbW9kZSAmIDI6IG1lcmdlIGFsbCBwcm9wZXJ0aWVzIG9mIHZhbHVlIGludG8gdGhlIG5zXG4gXHQvLyBtb2RlICYgNDogcmV0dXJuIHZhbHVlIHdoZW4gYWxyZWFkeSBucyBvYmplY3RcbiBcdC8vIG1vZGUgJiA4fDE6IGJlaGF2ZSBsaWtlIHJlcXVpcmVcbiBcdF9fd2VicGFja19yZXF1aXJlX18udCA9IGZ1bmN0aW9uKHZhbHVlLCBtb2RlKSB7XG4gXHRcdGlmKG1vZGUgJiAxKSB2YWx1ZSA9IF9fd2VicGFja19yZXF1aXJlX18odmFsdWUpO1xuIFx0XHRpZihtb2RlICYgOCkgcmV0dXJuIHZhbHVlO1xuIFx0XHRpZigobW9kZSAmIDQpICYmIHR5cGVvZiB2YWx1ZSA9PT0gJ29iamVjdCcgJiYgdmFsdWUgJiYgdmFsdWUuX19lc01vZHVsZSkgcmV0dXJuIHZhbHVlO1xuIFx0XHR2YXIgbnMgPSBPYmplY3QuY3JlYXRlKG51bGwpO1xuIFx0XHRfX3dlYnBhY2tfcmVxdWlyZV9fLnIobnMpO1xuIFx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkobnMsICdkZWZhdWx0JywgeyBlbnVtZXJhYmxlOiB0cnVlLCB2YWx1ZTogdmFsdWUgfSk7XG4gXHRcdGlmKG1vZGUgJiAyICYmIHR5cGVvZiB2YWx1ZSAhPSAnc3RyaW5nJykgZm9yKHZhciBrZXkgaW4gdmFsdWUpIF9fd2VicGFja19yZXF1aXJlX18uZChucywga2V5LCBmdW5jdGlvbihrZXkpIHsgcmV0dXJuIHZhbHVlW2tleV07IH0uYmluZChudWxsLCBrZXkpKTtcbiBcdFx0cmV0dXJuIG5zO1xuIFx0fTtcblxuIFx0Ly8gZ2V0RGVmYXVsdEV4cG9ydCBmdW5jdGlvbiBmb3IgY29tcGF0aWJpbGl0eSB3aXRoIG5vbi1oYXJtb255IG1vZHVsZXNcbiBcdF9fd2VicGFja19yZXF1aXJlX18ubiA9IGZ1bmN0aW9uKG1vZHVsZSkge1xuIFx0XHR2YXIgZ2V0dGVyID0gbW9kdWxlICYmIG1vZHVsZS5fX2VzTW9kdWxlID9cbiBcdFx0XHRmdW5jdGlvbiBnZXREZWZhdWx0KCkgeyByZXR1cm4gbW9kdWxlWydkZWZhdWx0J107IH0gOlxuIFx0XHRcdGZ1bmN0aW9uIGdldE1vZHVsZUV4cG9ydHMoKSB7IHJldHVybiBtb2R1bGU7IH07XG4gXHRcdF9fd2VicGFja19yZXF1aXJlX18uZChnZXR0ZXIsICdhJywgZ2V0dGVyKTtcbiBcdFx0cmV0dXJuIGdldHRlcjtcbiBcdH07XG5cbiBcdC8vIE9iamVjdC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkuY2FsbFxuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5vID0gZnVuY3Rpb24ob2JqZWN0LCBwcm9wZXJ0eSkgeyByZXR1cm4gT2JqZWN0LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eS5jYWxsKG9iamVjdCwgcHJvcGVydHkpOyB9O1xuXG4gXHQvLyBfX3dlYnBhY2tfcHVibGljX3BhdGhfX1xuIFx0X193ZWJwYWNrX3JlcXVpcmVfXy5wID0gXCJcIjtcblxuXG4gXHQvLyBMb2FkIGVudHJ5IG1vZHVsZSBhbmQgcmV0dXJuIGV4cG9ydHNcbiBcdHJldHVybiBfX3dlYnBhY2tfcmVxdWlyZV9fKF9fd2VicGFja19yZXF1aXJlX18ucyA9IFwiLi9zcmMvY29kZS5qc1wiKTtcbiIsImltcG9ydCB0eXBlcyBmcm9tICdAL3V0aWxzL3R5cGVzJztcclxuXHJcbmNvbnN0IHN0b3JhZ2VLZXkgICAgICAgICAgICA9ICdwcmVzZW50YXRvcl9zdG9yYWdlJztcclxuY29uc3QgZGVmYXVsdFdpZHRoICAgICAgICAgID0gNDUwO1xyXG5jb25zdCBkZWZhdWx0SGVpZ2h0ICAgICAgICAgPSAzOTA7XHJcbmNvbnN0IGRlZmF1bHRFeHBvcnRTZXR0aW5ncyA9IHtcclxuICAgIGZvcm1hdDogJ1BORycsXHJcbiAgICBjb250ZW50c09ubHk6IHRydWUsXHJcbiAgICBjb25zdHJhaW50OiB7XHJcbiAgICAgICAgdHlwZTogJ1NDQUxFJyxcclxuICAgICAgICB2YWx1ZTogMSxcclxuICAgIH0sXHJcbn07XHJcblxyXG4vLyBsb2FkcyBzdG9yYWdlIGRhdGEgYW5kIGluaXRpYWxpemVzIHRoZSB1aVxyXG5hc3luYyBmdW5jdGlvbiBpbml0VUkoKSB7XHJcbiAgICBsZXQgc3RvcmFnZURhdGE7XHJcbiAgICB0cnkge1xyXG4gICAgICAgIHN0b3JhZ2VEYXRhID0gYXdhaXQgZmlnbWEuY2xpZW50U3RvcmFnZS5nZXRBc3luYyhzdG9yYWdlS2V5KTtcclxuICAgIH0gY2F0Y2ggKGUpIHtcclxuICAgICAgICBjb25zb2xlLmxvZygnU3RvcmFnZSBpbml0IGVycm9yOicsIGUpO1xyXG4gICAgfVxyXG5cclxuICAgIGZpZ21hLnNob3dVSShfX2h0bWxfXywgeyB3aWR0aDogZGVmYXVsdFdpZHRoLCBoZWlnaHQ6IGRlZmF1bHRIZWlnaHQgfSk7XHJcblxyXG4gICAgZmlnbWEudWkucG9zdE1lc3NhZ2Uoe1xyXG4gICAgICAgIHR5cGU6IHR5cGVzLk1FU1NBR0VfSU5JVF9BUFAsXHJcbiAgICAgICAgZGF0YTogc3RvcmFnZURhdGEgfHwge30sXHJcbiAgICB9KTtcclxufVxyXG5cclxuLy8gcmV0dXJucyB2aXNpYmxlIGZyYW1lIG5vZGVzXHJcbmZ1bmN0aW9uIGdldEZyYW1lcyhmcm9tU2VsZWN0aW9uID0gZmFsc2UpIHtcclxuICAgIGxldCByZXN1bHQgPSBbXTtcclxuICAgIGxldCBub2RlcyAgPSAoZnJvbVNlbGVjdGlvbiA/IGZpZ21hLmN1cnJlbnRQYWdlLnNlbGVjdGlvbiA6IGZpZ21hLmN1cnJlbnRQYWdlLmNoaWxkcmVuKSB8fCBbXTtcclxuXHJcbiAgICBmb3IgKGxldCBpID0gMDsgaSA8IG5vZGVzLmxlbmd0aDsgaSsrKSB7XHJcbiAgICAgICAgaWYgKG5vZGVzW2ldLnR5cGUgPT09ICdGUkFNRScgJiYgbm9kZXNbaV0udmlzaWJsZSkge1xyXG4gICAgICAgICAgICByZXN1bHQucHVzaCh7XHJcbiAgICAgICAgICAgICAgICBub2RlOiAgIG5vZGVzW2ldLFxyXG4gICAgICAgICAgICAgICAgaWQ6ICAgICBub2Rlc1tpXS5pZCxcclxuICAgICAgICAgICAgICAgIG5hbWU6ICAgbm9kZXNbaV0ubmFtZSxcclxuICAgICAgICAgICAgICAgIHdpZHRoOiAgbm9kZXNbaV0ud2lkdGgsXHJcbiAgICAgICAgICAgICAgICBoZWlnaHQ6IG5vZGVzW2ldLmhlaWdodCxcclxuICAgICAgICAgICAgfSk7XHJcbiAgICAgICAgfVxyXG4gICAgfVxyXG5cclxuICAgIHJldHVybiByZXN1bHQ7XHJcbn1cclxuXHJcbi8vIHJldHVybnMgc2luZ2xlIGZyYW1lIGJ5IGl0cyBub2RlIGlkXHJcbmZ1bmN0aW9uIGdldEZyYW1lQnlJZChpZCkge1xyXG4gICAgY29uc3QgZnJhbWVzID0gZ2V0RnJhbWVzKCk7XHJcblxyXG4gICAgZm9yIChsZXQgaSA9IGZyYW1lcy5sZW5ndGggLSAxOyBpID49IDA7IGktLSkge1xyXG4gICAgICAgIGlmIChmcmFtZXNbaV0uaWQgPT0gaWQpIHtcclxuICAgICAgICAgICAgcmV0dXJuIGZyYW1lc1tpXTtcclxuICAgICAgICB9XHJcbiAgICB9XHJcblxyXG4gICAgcmV0dXJuIG51bGw7XHJcbn1cclxuXHJcbi8vIGV4cG9ydHMgZnJhbWUgbm9kZSBkYXRhXHJcbmFzeW5jIGZ1bmN0aW9uIGV4cG9ydEZyYW1lKGlkLCBhZGRpdGlvbmFsU2V0dGluZ3MpIHtcclxuICAgIHRyeSB7XHJcbiAgICAgICAgY29uc3QgZnJhbWUgPSBnZXRGcmFtZUJ5SWQoaWQpO1xyXG5cclxuICAgICAgICBpZiAoZnJhbWUgJiYgZnJhbWUubm9kZSkge1xyXG4gICAgICAgICAgICByZXR1cm4gYXdhaXQgZnJhbWUubm9kZS5leHBvcnRBc3luYyhPYmplY3QuYXNzaWduKHt9LCBkZWZhdWx0RXhwb3J0U2V0dGluZ3MsIGFkZGl0aW9uYWxTZXR0aW5ncyB8fCB7fSkpO1xyXG4gICAgICAgIH1cclxuICAgIH0gY2F0Y2ggKGUpIHtcclxuICAgICAgICBjb25zb2xlLmxvZygnRXhwb3J0IGZyYW1lIGVycm9yOicsIGUpO1xyXG4gICAgfVxyXG5cclxuICAgIHJldHVybiBudWxsO1xyXG59XHJcblxyXG5pbml0VUkoKTtcclxuXHJcbmZpZ21hLnVpLm9ubWVzc2FnZSA9IGFzeW5jIChtZXNzYWdlKSA9PiB7XHJcbiAgICBpZiAodHlwZW9mIG1lc3NhZ2UgIT09ICdvYmplY3QnIHx8IG1lc3NhZ2UgPT09IG51bGwgfHwgIW1lc3NhZ2UudHlwZSkge1xyXG4gICAgICAgIHJldHVybjtcclxuICAgIH1cclxuXHJcbiAgICBpZiAobWVzc2FnZS50eXBlID09PSB0eXBlcy5NRVNTQUdFX1NBVkVfU1RPUkFHRSkge1xyXG4gICAgICAgIGZpZ21hLmNsaWVudFN0b3JhZ2Uuc2V0QXN5bmMoc3RvcmFnZUtleSwgbWVzc2FnZS5kYXRhIHx8IHt9KTtcclxuICAgIH0gZWxzZSBpZiAobWVzc2FnZS50eXBlID09PSB0eXBlcy5NRVNTQUdFX0NMT1NFKSB7XHJcbiAgICAgICAgZmlnbWEuY2xvc2VQbHVnaW4oKTtcclxuICAgIH0gZWxzZSBpZiAobWVzc2FnZS50eXBlID09PSB0eXBlcy5NRVNTQUdFX05PVElGWSkge1xyXG4gICAgICAgIGZpZ21hLm5vdGlmeShtZXNzYWdlLmRhdGEubWVzc2FnZSwge1xyXG4gICAgICAgICAgICB0aW1lb3V0OiAobWVzc2FnZS5kYXRhLnRpbWVvdXQgfHwgNDAwMCkgPDwgMCxcclxuICAgICAgICB9KTtcclxuICAgIH0gZWxzZSBpZiAobWVzc2FnZS50eXBlID09PSB0eXBlcy5NRVNTQUdFX1JFU0laRV9VSSkge1xyXG4gICAgICAgIGZpZ21hLnVpLnJlc2l6ZShcclxuICAgICAgICAgICAgKG1lc3NhZ2UuZGF0YS53aWR0aCB8fCBkZWZhdWx0V2lkdGgpIDw8IDAsXHJcbiAgICAgICAgICAgIChtZXNzYWdlLmRhdGEuaGVpZ2h0IHx8IGRlZmF1bHRIZWlnaHQpIDw8IDAsXHJcbiAgICAgICAgKTtcclxuICAgIH0gZWxzZSBpZiAobWVzc2FnZS50eXBlID09PSB0eXBlcy5NRVNTQUdFX0dFVF9GUkFNRVMpIHtcclxuICAgICAgICBsZXQgZnJhbWVzID0gZ2V0RnJhbWVzKG1lc3NhZ2UuZGF0YS5vbmx5U2VsZWN0ZWQpO1xyXG5cclxuICAgICAgICAvLyBzZW5kIHRoZSByZXN1bHQgdG8gdGhlIHVpXHJcbiAgICAgICAgZmlnbWEudWkucG9zdE1lc3NhZ2Uoe1xyXG4gICAgICAgICAgICBzdGF0ZTogbWVzc2FnZS5zdGF0ZSxcclxuICAgICAgICAgICAgdHlwZTogIHR5cGVzLk1FU1NBR0VfR0VUX0ZSQU1FU19SRVNQT05TRSxcclxuICAgICAgICAgICAgZGF0YTogIGZyYW1lcyxcclxuICAgICAgICB9KTtcclxuICAgIH0gZWxzZSBpZiAobWVzc2FnZS50eXBlID09PSB0eXBlcy5NRVNTQUdFX0VYUE9SVF9GUkFNRSkge1xyXG4gICAgICAgIGxldCBkYXRhID0gYXdhaXQgZXhwb3J0RnJhbWUobWVzc2FnZS5kYXRhLmlkKTtcclxuXHJcbiAgICAgICAgLy8gc2VuZCB0aGUgcmVzdWx0IHRvIHRoZSB1aVxyXG4gICAgICAgIGZpZ21hLnVpLnBvc3RNZXNzYWdlKHtcclxuICAgICAgICAgICAgc3RhdGU6IG1lc3NhZ2Uuc3RhdGUsXHJcbiAgICAgICAgICAgIHR5cGU6ICB0eXBlcy5NRVNTQUdFX0VYUE9SVF9GUkFNRV9SRVNQT05TRSxcclxuICAgICAgICAgICAgZGF0YTogIGRhdGEsXHJcbiAgICAgICAgfSk7XHJcbiAgICB9XHJcbn1cclxuIiwiLy8gbGlzdCB3aXRoIHBsdWdpbiBtZXNzYWdlcyB0eXBlczpcbmV4cG9ydCBkZWZhdWx0IHtcbiAgICBNRVNTQUdFX0lOSVRfQVBQOiAgICAgICAgICAgICAgJ2luaXRfYXBwJyxcbiAgICBNRVNTQUdFX05PVElGWTogICAgICAgICAgICAgICAgJ25vdGlmeScsXG4gICAgTUVTU0FHRV9DTE9TRTogICAgICAgICAgICAgICAgICdjbG9zZScsXG4gICAgTUVTU0FHRV9SRVNJWkVfVUk6ICAgICAgICAgICAgICdyZXNpemVfdWknLFxuICAgIE1FU1NBR0VfR0VUX0ZSQU1FUzogICAgICAgICAgICAnZ2V0X2ZyYW1lcycsXG4gICAgTUVTU0FHRV9HRVRfRlJBTUVTX1JFU1BPTlNFOiAgICdnZXRfZnJhbWVzX3Jlc3BvbnNlJyxcbiAgICBNRVNTQUdFX0VYUE9SVF9GUkFNRTogICAgICAgICAgJ2V4cG9ydF9mcmFtZScsXG4gICAgTUVTU0FHRV9FWFBPUlRfRlJBTUVfUkVTUE9OU0U6ICdleHBvcnRfZnJhbWVfcmVzcG9uc2UnLFxuICAgIE1FU1NBR0VfU0FWRV9TVE9SQUdFOiAgICAgICAgICAnc2F2ZV9zdG9yYWdlJyxcbiAgICBNRVNTQUdFX1NBVkVfU1RPUkFHRTogICAgICAgICAgJ3NhdmVfc3RvcmFnZScsXG59XG4iXSwic291cmNlUm9vdCI6IiJ9