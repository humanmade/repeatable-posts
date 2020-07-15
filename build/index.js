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
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/index.js":
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
/*! no exports provided */
/***/ (function(module, exports) {

throw new Error("Module build failed (from ./node_modules/babel-loader/lib/index.js):\nSyntaxError: C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\src\\index.js: Unexpected token, expected \",\" (12:2)\n\n  10 | \t\tvalue: \"\",\n  11 | \t\tlabel: \"No\"\n> 12 | \t\tdescription: \"\",\n     | \t\t^\n  13 | \t\tonSelect:\n  14 | \t\tchecked:\n  15 | \t\tonMetaFieldChange: value;\n    at Object._raise (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:757:17)\n    at Object.raiseWithData (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:750:17)\n    at Object.raise (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:744:17)\n    at Object.unexpected (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:8834:16)\n    at Object.expect (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:8820:28)\n    at Object.parseObj (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:10485:14)\n    at Object.parseExprAtom (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:10090:28)\n    at Object.parseExprAtom (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:4648:20)\n    at Object.parseExprSubscripts (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9688:23)\n    at Object.parseMaybeUnary (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9668:21)\n    at Object.parseExprOps (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9538:23)\n    at Object.parseMaybeConditional (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9511:23)\n    at Object.parseMaybeAssign (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9466:21)\n    at Object.parseObjectProperty (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:10636:101)\n    at Object.parseObjPropValue (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:10661:101)\n    at Object.parseObjectMember (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:10585:10)\n    at Object.parseObj (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:10494:25)\n    at Object.parseExprAtom (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:10090:28)\n    at Object.parseExprAtom (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:4648:20)\n    at Object.parseExprSubscripts (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9688:23)\n    at Object.parseMaybeUnary (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9668:21)\n    at Object.parseExprOps (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9538:23)\n    at Object.parseMaybeConditional (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9511:23)\n    at Object.parseMaybeAssign (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:9466:21)\n    at Object.parseVar (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:11869:26)\n    at Object.parseVarStatement (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:11678:10)\n    at Object.parseStatementContent (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:11277:21)\n    at Object.parseStatement (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:11210:17)\n    at Object.parseBlockOrModuleBlockBody (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:11785:25)\n    at Object.parseBlockBody (C:\\Users\\jwo\\srv\\icebreak.local\\app\\public\\wp-content\\plugins\\repeatable-posts\\node_modules\\@babel\\parser\\lib\\index.js:11771:10)");

/***/ })

/******/ });
//# sourceMappingURL=index.js.map