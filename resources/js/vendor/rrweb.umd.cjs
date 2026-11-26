(function (g, f) {
    if ("object" == typeof exports && "object" == typeof module) {
      module.exports = f();
    } else if ("function" == typeof define && define.amd) {
      define("rrweb", [], f);
    } else if ("object" == typeof exports) {
      exports["rrweb"] = f();
    } else {
      g["rrweb"] = f();
    }
  }(this, () => {
var exports = {};
var module = { exports };
var __defProp = Object.defineProperty;
var __defProps = Object.defineProperties;
var __getOwnPropDescs = Object.getOwnPropertyDescriptors;
var __getOwnPropSymbols = Object.getOwnPropertySymbols;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __propIsEnum = Object.prototype.propertyIsEnumerable;
var __defNormalProp = (obj, key, value) => key in obj ? __defProp(obj, key, { enumerable: true, configurable: true, writable: true, value }) : obj[key] = value;
var __spreadValues = (a2, b) => {
  for (var prop in b || (b = {}))
    if (__hasOwnProp.call(b, prop))
      __defNormalProp(a2, prop, b[prop]);
  if (__getOwnPropSymbols)
    for (var prop of __getOwnPropSymbols(b)) {
      if (__propIsEnum.call(b, prop))
        __defNormalProp(a2, prop, b[prop]);
    }
  return a2;
};
var __spreadProps = (a2, b) => __defProps(a2, __getOwnPropDescs(b));
var __objRest = (source, exclude) => {
  var target = {};
  for (var prop in source)
    if (__hasOwnProp.call(source, prop) && exclude.indexOf(prop) < 0)
      target[prop] = source[prop];
  if (source != null && __getOwnPropSymbols)
    for (var prop of __getOwnPropSymbols(source)) {
      if (exclude.indexOf(prop) < 0 && __propIsEnum.call(source, prop))
        target[prop] = source[prop];
    }
  return target;
};
var __publicField = (obj, key, value) => __defNormalProp(obj, typeof key !== "symbol" ? key + "" : key, value);

// dist/rrweb.cjs
Object.defineProperty(exports, Symbol.toStringTag, { value: "Module" });
var __defProp$2 = Object.defineProperty;
var __exportAll = (all, no_symbols) => {
  let target = {};
  for (var name in all) __defProp$2(target, name, {
    get: all[name],
    enumerable: true
  });
  if (!no_symbols) __defProp$2(target, Symbol.toStringTag, { value: "Module" });
  return target;
};
var __create$1 = Object.create;
var __defProp$1 = Object.defineProperty;
var __getOwnPropDesc$1 = Object.getOwnPropertyDescriptor;
var __getOwnPropNames$1 = Object.getOwnPropertyNames;
var __getProtoOf$1 = Object.getPrototypeOf;
var __hasOwnProp$1 = Object.prototype.hasOwnProperty;
var __commonJSMin$1 = (cb, mod) => () => (mod || (cb((mod = { exports: {} }).exports, mod, mod.exports), cb = null), mod.exports);
var __copyProps$1 = (to, from, except, desc) => {
  if (from && typeof from === "object" || typeof from === "function") for (var keys = __getOwnPropNames$1(from), i2 = 0, n2 = keys.length, key; i2 < n2; i2++) {
    key = keys[i2];
    if (!__hasOwnProp$1.call(to, key) && key !== except) __defProp$1(to, key, {
      get: ((k) => from[k]).bind(null, key),
      enumerable: !(desc = __getOwnPropDesc$1(from, key)) || desc.enumerable
    });
  }
  return to;
};
var __toESM$1 = (mod, isNodeMode, target) => (target = mod != null ? __create$1(__getProtoOf$1(mod)) : {}, __copyProps$1(isNodeMode || !mod || !mod.__esModule || !__hasOwnProp$1.call(mod, "default") ? __defProp$1(target, "default", {
  value: mod,
  enumerable: true
}) : target, mod));
var NodeType$3 = /* @__PURE__ */ function(NodeType2) {
  NodeType2[NodeType2["Document"] = 0] = "Document";
  NodeType2[NodeType2["DocumentType"] = 1] = "DocumentType";
  NodeType2[NodeType2["Element"] = 2] = "Element";
  NodeType2[NodeType2["Text"] = 3] = "Text";
  NodeType2[NodeType2["CDATA"] = 4] = "CDATA";
  NodeType2[NodeType2["Comment"] = 5] = "Comment";
  return NodeType2;
}({});
var testableAccessors$2 = {
  Node: [
    "childNodes",
    "parentNode",
    "parentElement",
    "textContent",
    "ownerDocument",
    "firstChild",
    "lastChild",
    "nextSibling",
    "previousSibling"
  ],
  ShadowRoot: ["host", "styleSheets"],
  Element: [
    "shadowRoot",
    "querySelector",
    "querySelectorAll"
  ],
  MutationObserver: []
};
var testableMethods$2 = {
  Node: ["contains", "getRootNode"],
  ShadowRoot: ["getSelection"],
  Element: [],
  MutationObserver: ["constructor"]
};
var untaintedBasePrototype$2 = {};
var untaintedBaseIframeCleanup$2 = {};
var mutationObserverUsers$2 = 0;
function angularZoneUnpatchedAlternative$2(key) {
  var _a2, _b;
  const angularUnpatchedVersionSymbol = (_b = (_a2 = globalThis == null ? void 0 : globalThis.Zone) == null ? void 0 : _a2.__symbol__) == null ? void 0 : _b.call(_a2, key);
  if (angularUnpatchedVersionSymbol && globalThis[angularUnpatchedVersionSymbol]) return globalThis[angularUnpatchedVersionSymbol];
  else return;
}
function getUntaintedPrototype$2(key) {
  if (untaintedBasePrototype$2[key]) return untaintedBasePrototype$2[key];
  const defaultObj = angularZoneUnpatchedAlternative$2(key) || globalThis[key];
  const defaultPrototype = defaultObj.prototype;
  const accessorNames = key in testableAccessors$2 ? testableAccessors$2[key] : void 0;
  const isUntaintedAccessors = Boolean(accessorNames && accessorNames.every((accessor) => {
    var _a2, _b;
    return Boolean((_b = (_a2 = Object.getOwnPropertyDescriptor(defaultPrototype, accessor)) == null ? void 0 : _a2.get) == null ? void 0 : _b.toString().includes("[native code]"));
  }));
  const methodNames = key in testableMethods$2 ? testableMethods$2[key] : void 0;
  const isUntaintedMethods = Boolean(methodNames && methodNames.every((method) => {
    var _a2;
    return typeof defaultPrototype[method] === "function" && ((_a2 = defaultPrototype[method]) == null ? void 0 : _a2.toString().includes("[native code]"));
  }));
  if (isUntaintedAccessors && isUntaintedMethods) {
    untaintedBasePrototype$2[key] = defaultObj.prototype;
    return defaultObj.prototype;
  }
  try {
    const iframeEl = document.createElement("iframe");
    iframeEl.style.display = "none";
    document.body.appendChild(iframeEl);
    const win = iframeEl.contentWindow;
    if (!win) return defaultObj.prototype;
    const untaintedObject = win[key].prototype;
    if (!untaintedObject) {
      iframeEl.remove();
      return defaultPrototype;
    }
    const ua = navigator.userAgent;
    if (ua.includes("Safari") && !ua.includes("Chrome")) {
      iframeEl.classList.add("rr-block");
      iframeEl.setAttribute("__rrwebUntaintedMutationObserver", "");
      untaintedBaseIframeCleanup$2[key] = () => iframeEl.remove();
    } else iframeEl.remove();
    return untaintedBasePrototype$2[key] = untaintedObject;
  } catch (e2) {
    return defaultPrototype;
  }
}
var untaintedAccessorCache$2 = {};
function getUntaintedAccessor$2(key, instance, accessor) {
  var _a2, _b;
  const cached = (_a2 = untaintedAccessorCache$2[key]) == null ? void 0 : _a2[accessor];
  if (cached) return cached.call(instance);
  const untaintedPrototype = getUntaintedPrototype$2(key);
  const untaintedAccessor = (_b = Object.getOwnPropertyDescriptor(untaintedPrototype, accessor)) == null ? void 0 : _b.get;
  if (!untaintedAccessor) return instance[accessor];
  (untaintedAccessorCache$2[key] || (untaintedAccessorCache$2[key] = {}))[accessor] = untaintedAccessor;
  return untaintedAccessor.call(instance);
}
var untaintedMethodCache$2 = {};
function getUntaintedMethod$2(key, instance, method) {
  const cacheKey = `${key}.${String(method)}`;
  if (untaintedMethodCache$2[cacheKey]) return untaintedMethodCache$2[cacheKey].bind(instance);
  const untaintedMethod = getUntaintedPrototype$2(key)[method];
  if (typeof untaintedMethod !== "function") return instance[method];
  untaintedMethodCache$2[cacheKey] = untaintedMethod;
  return untaintedMethod.bind(instance);
}
function ownerDocument$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "ownerDocument");
}
function childNodes$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "childNodes");
}
function parentNode$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "parentNode");
}
function parentElement$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "parentElement");
}
function textContent$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "textContent");
}
function firstChild$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "firstChild");
}
function lastChild$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "lastChild");
}
function nextSibling$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "nextSibling");
}
function previousSibling$2(n2) {
  return getUntaintedAccessor$2("Node", n2, "previousSibling");
}
function contains$2(n2, other) {
  return getUntaintedMethod$2("Node", n2, "contains")(other);
}
function getRootNode$2(n2) {
  return getUntaintedMethod$2("Node", n2, "getRootNode")();
}
function host$2(n2) {
  if (!n2 || !("host" in n2)) return null;
  return getUntaintedAccessor$2("ShadowRoot", n2, "host");
}
function styleSheets$2(n2) {
  return n2.styleSheets;
}
function shadowRoot$2(n2) {
  if (!n2 || !("shadowRoot" in n2)) return null;
  return getUntaintedAccessor$2("Element", n2, "shadowRoot");
}
function querySelector$2(n2, selectors) {
  return getUntaintedAccessor$2("Element", n2, "querySelector")(selectors);
}
function querySelectorAll$2(n2, selectors) {
  return getUntaintedAccessor$2("Element", n2, "querySelectorAll")(selectors);
}
function mutationObserverCtor$2() {
  const prototype = getUntaintedPrototype$2("MutationObserver");
  mutationObserverUsers$2++;
  let stopCalled = false;
  return [prototype.constructor, () => {
    if (stopCalled) return;
    stopCalled = true;
    if (--mutationObserverUsers$2 !== 0) return;
    const cleanup = untaintedBaseIframeCleanup$2["MutationObserver"];
    if (!cleanup) return;
    try {
      cleanup();
    } finally {
      delete untaintedBasePrototype$2["MutationObserver"];
      delete untaintedBaseIframeCleanup$2["MutationObserver"];
    }
  }];
}
var nowTimestamp$2 = Date.now;
if (!/* @__PURE__ */ /[1-9][0-9]{12}/.test(Date.now().toString())) nowTimestamp$2 = () => (/* @__PURE__ */ new Date()).getTime();
function patch$2(source, name, replacement) {
  try {
    if (!(name in source)) return () => {
    };
    const original = source[name];
    const wrapped = replacement(original);
    if (typeof wrapped === "function") {
      wrapped.prototype = wrapped.prototype || {};
      Object.defineProperties(wrapped, { __rrweb_original__: {
        enumerable: false,
        value: original
      } });
    }
    source[name] = wrapped;
    return () => {
      source[name] = original;
    };
  } catch (e2) {
    return () => {
    };
  }
}
var src_default$2 = {
  ownerDocument: ownerDocument$2,
  childNodes: childNodes$2,
  parentNode: parentNode$2,
  parentElement: parentElement$2,
  textContent: textContent$2,
  firstChild: firstChild$2,
  lastChild: lastChild$2,
  nextSibling: nextSibling$2,
  previousSibling: previousSibling$2,
  contains: contains$2,
  getRootNode: getRootNode$2,
  host: host$2,
  styleSheets: styleSheets$2,
  shadowRoot: shadowRoot$2,
  querySelector: querySelector$2,
  querySelectorAll: querySelectorAll$2,
  nowTimestamp: nowTimestamp$2,
  mutationObserverCtor: mutationObserverCtor$2,
  patch: patch$2
};
function isElement(n2) {
  return n2.nodeType === n2.ELEMENT_NODE;
}
function isShadowRoot(n2) {
  const hostEl = n2 && "host" in n2 && "mode" in n2 && src_default$2.host(n2) || null;
  return Boolean(hostEl && "shadowRoot" in hostEl && src_default$2.shadowRoot(hostEl) === n2);
}
function isNativeShadowDom(shadowRoot2) {
  return Object.prototype.toString.call(shadowRoot2) === "[object ShadowRoot]";
}
function fixBrowserCompatibilityIssuesInCSS(cssText) {
  if (cssText.includes(" background-clip: text;") && !cssText.includes(" -webkit-background-clip: text;")) cssText = cssText.replace(/\sbackground-clip:\s*text;/g, " -webkit-background-clip: text; background-clip: text;");
  return cssText;
}
function escapeImportStatement(rule) {
  const { cssText } = rule;
  if (cssText.split('"').length < 3) return cssText;
  const statement = ["@import", `url(${JSON.stringify(rule.href)})`];
  if (rule.layerName === "") statement.push(`layer`);
  else if (rule.layerName) statement.push(`layer(${rule.layerName})`);
  if (rule.supportsText) statement.push(`supports(${rule.supportsText})`);
  if (rule.media.length) statement.push(rule.media.mediaText);
  return statement.join(" ") + ";";
}
function stringifyStylesheet(s2) {
  try {
    const rules2 = s2.rules || s2.cssRules;
    if (!rules2) return null;
    let sheetHref = s2.href;
    if (!sheetHref && s2.ownerNode) sheetHref = s2.ownerNode.baseURI;
    return fixBrowserCompatibilityIssuesInCSS(Array.from(rules2, (rule) => stringifyRule(rule, sheetHref)).join(""));
  } catch (error) {
    return null;
  }
}
function stringifyRule(rule, sheetHref) {
  if (isCSSImportRule(rule)) {
    let importStringified;
    try {
      importStringified = stringifyStylesheet(rule.styleSheet) || escapeImportStatement(rule);
    } catch (error) {
      importStringified = rule.cssText;
    }
    if (rule.styleSheet.href) return absolutifyURLs(importStringified, rule.styleSheet.href);
    return importStringified;
  } else {
    let ruleStringified = rule.cssText;
    if (isCSSStyleRule(rule) && rule.selectorText.includes(":")) ruleStringified = fixSafariColons(ruleStringified);
    if (sheetHref) return absolutifyURLs(ruleStringified, sheetHref);
    return ruleStringified;
  }
}
function fixSafariColons(cssStringified) {
  return cssStringified.replace(/(\[(?:[\w-]+)[^\\])(:(?:[\w-]+)\])/gm, "$1\\$2");
}
function isCSSImportRule(rule) {
  return "styleSheet" in rule;
}
function isCSSStyleRule(rule) {
  return "selectorText" in rule;
}
var Mirror$2 = class {
  constructor() {
    __publicField(this, "idNodeMap", /* @__PURE__ */ new Map());
    __publicField(this, "nodeMetaMap", /* @__PURE__ */ new WeakMap());
  }
  getId(n2) {
    var _a2, _b;
    if (!n2) return -1;
    return (_b = (_a2 = this.getMeta(n2)) == null ? void 0 : _a2.id) != null ? _b : -1;
  }
  getNode(id) {
    return this.idNodeMap.get(id) || null;
  }
  getIds() {
    return Array.from(this.idNodeMap.keys());
  }
  getMeta(n2) {
    return this.nodeMetaMap.get(n2) || null;
  }
  removeNodeFromMap(n2) {
    const id = this.getId(n2);
    this.idNodeMap.delete(id);
    if (n2.childNodes) n2.childNodes.forEach((childNode) => this.removeNodeFromMap(childNode));
    const shadowRoot2 = src_default$2.shadowRoot(n2);
    if (shadowRoot2) this.removeNodeFromMap(shadowRoot2);
  }
  has(id) {
    return this.idNodeMap.has(id);
  }
  hasNode(node) {
    return this.nodeMetaMap.has(node);
  }
  add(n2, meta) {
    const id = meta.id;
    this.idNodeMap.set(id, n2);
    this.nodeMetaMap.set(n2, meta);
  }
  replace(id, n2) {
    const oldNode = this.getNode(id);
    if (oldNode) {
      const meta = this.nodeMetaMap.get(oldNode);
      if (meta) this.nodeMetaMap.set(n2, meta);
    }
    this.idNodeMap.set(id, n2);
  }
  reset() {
    this.idNodeMap = /* @__PURE__ */ new Map();
    this.nodeMetaMap = /* @__PURE__ */ new WeakMap();
  }
};
function createMirror$2() {
  return new Mirror$2();
}
function maskInputValue({ element, maskInputOptions, tagName, type, value, maskInputFn }) {
  let text = value || "";
  const actualType = type && toLowerCase(type);
  if (maskInputOptions[tagName.toLowerCase()] || actualType && maskInputOptions[actualType]) {
    if (maskInputFn) text = maskInputFn(text, element);
    else text = "*".repeat(text.length);
  }
  return text;
}
function toLowerCase(str) {
  return str.toLowerCase();
}
var ORIGINAL_ATTRIBUTE_NAME = "__rrweb_original__";
function is2DCanvasBlank(canvas) {
  const ctx = canvas.getContext("2d");
  if (!ctx) return true;
  const chunkSize = 50;
  for (let x = 0; x < canvas.width; x += chunkSize) for (let y = 0; y < canvas.height; y += chunkSize) {
    const getImageData = ctx.getImageData;
    const originalGetImageData = ORIGINAL_ATTRIBUTE_NAME in getImageData ? getImageData[ORIGINAL_ATTRIBUTE_NAME] : getImageData;
    if (new Uint32Array(originalGetImageData.call(ctx, x, y, Math.min(chunkSize, canvas.width - x), Math.min(chunkSize, canvas.height - y)).data.buffer).some((pixel) => pixel !== 0)) return false;
  }
  return true;
}
function isNodeMetaEqual(a2, b) {
  if (!a2 || !b || a2.type !== b.type) return false;
  if (a2.type === NodeType$3.Document) return a2.compatMode === b.compatMode;
  else if (a2.type === NodeType$3.DocumentType) return a2.name === b.name && a2.publicId === b.publicId && a2.systemId === b.systemId;
  else if (a2.type === NodeType$3.Comment || a2.type === NodeType$3.Text || a2.type === NodeType$3.CDATA) return a2.textContent === b.textContent;
  else if (a2.type === NodeType$3.Element) return a2.tagName === b.tagName && JSON.stringify(a2.attributes) === JSON.stringify(b.attributes) && a2.isSVG === b.isSVG && a2.needBlock === b.needBlock;
  return false;
}
function getInputType(element) {
  const type = element.type;
  return element.hasAttribute("data-rr-is-password") ? "password" : type ? toLowerCase(type) : null;
}
function extractFileExtension(path, baseURL) {
  var _a2, _b;
  let url;
  try {
    url = new URL(path, baseURL != null ? baseURL : window.location.href);
  } catch (err) {
    return null;
  }
  return (_b = (_a2 = url.pathname.match(/\.([0-9a-z]+)(?:$)/i)) == null ? void 0 : _a2[1]) != null ? _b : null;
}
function extractOrigin(url) {
  let origin = "";
  if (url.indexOf("//") > -1) origin = url.split("/").slice(0, 3).join("/");
  else origin = url.split("/")[0];
  origin = origin.split("?")[0];
  return origin;
}
var URL_IN_CSS_REF = /url\((?:(')([^']*)'|(")(.*?)"|([^)]*))\)/gm;
var URL_PROTOCOL_MATCH = /^(?:[a-z+]+:)?\/\//i;
var URL_WWW_MATCH = /^www\..*/i;
var DATA_URI = /^(data:)([^,]*),(.*)/i;
var BLOB_URI = /^blob:/i;
function absolutifyURLs(cssText, href) {
  return (cssText || "").replace(URL_IN_CSS_REF, (origin, quote1, path1, quote2, path2, path3) => {
    const filePath = path1 || path2 || path3;
    const maybeQuote = quote1 || quote2 || "";
    if (!filePath) return origin;
    if (URL_PROTOCOL_MATCH.test(filePath) || URL_WWW_MATCH.test(filePath)) return `url(${maybeQuote}${filePath}${maybeQuote})`;
    if (DATA_URI.test(filePath) || BLOB_URI.test(filePath)) return `url(${maybeQuote}${filePath}${maybeQuote})`;
    if (filePath[0] === "#") return `url(${maybeQuote}${filePath}${maybeQuote})`;
    if (filePath[0] === "/") return `url(${maybeQuote}${extractOrigin(href) + filePath}${maybeQuote})`;
    const filePathNoHash = filePath.split("#")[0];
    const maybeHash = filePath.substring(filePathNoHash.length);
    const stack = href.split("#")[0].split("/");
    const parts = filePathNoHash.split("/");
    stack.pop();
    for (const part of parts) if (part === ".") continue;
    else if (part === "..") stack.pop();
    else stack.push(part);
    return `url(${maybeQuote}${stack.join("/")}${maybeHash}${maybeQuote})`;
  });
}
var cssNormalizeBase$1 = "(?:/\\*[^*]*\\*/)|[\\s;]";
var cssNormalizeRe = new RegExp(`${cssNormalizeBase$1}|\\b(0)px?`, "g");
var cssNormalizeReNoPx = new RegExp(cssNormalizeBase$1, "g");
function normalizeCssString(cssText, _testNoPxNorm = false) {
  return _testNoPxNorm ? cssText.replace(cssNormalizeReNoPx, "") : cssText.replace(cssNormalizeRe, "$1");
}
function splitCssText(cssText, style, _testNoPxNorm = false) {
  const childNodes2 = Array.from(style.childNodes);
  const splits = [];
  let iterCount = 0;
  if (childNodes2.length > 1 && cssText && typeof cssText === "string") {
    let cssTextNorm = normalizeCssString(cssText, _testNoPxNorm);
    const normFactor = cssTextNorm.length / cssText.length;
    for (let i2 = 1; i2 < childNodes2.length; i2++) if (childNodes2[i2].textContent && typeof childNodes2[i2].textContent === "string") {
      const textContentNorm = normalizeCssString(childNodes2[i2].textContent, _testNoPxNorm);
      const jLimit = 100;
      let j = 3;
      for (; j < textContentNorm.length; j++) {
        if (textContentNorm[j].match(/[a-zA-Z0-9]/) || textContentNorm.indexOf(textContentNorm.substring(0, j), 1) !== -1) continue;
        break;
      }
      for (; j < textContentNorm.length; j++) {
        let startSubstring = textContentNorm.substring(0, j);
        let cssNormSplits = cssTextNorm.split(startSubstring);
        let splitNorm = -1;
        if (cssNormSplits.length === 2) splitNorm = cssNormSplits[0].length;
        else if (cssNormSplits.length > 2 && cssNormSplits[0] === "" && childNodes2[i2 - 1].textContent !== "") splitNorm = cssTextNorm.indexOf(startSubstring, 1);
        else if (cssNormSplits.length === 1) {
          startSubstring = startSubstring.substring(0, startSubstring.length - 1);
          cssNormSplits = cssTextNorm.split(startSubstring);
          if (cssNormSplits.length <= 1) {
            splits.push(cssText);
            return splits;
          }
          j = 101;
        } else if (j === textContentNorm.length - 1) splitNorm = cssTextNorm.indexOf(startSubstring);
        if (cssNormSplits.length >= 2 && j > jLimit) {
          const prevTextContent = childNodes2[i2 - 1].textContent;
          if (prevTextContent && typeof prevTextContent === "string") {
            const prevMinLength = normalizeCssString(prevTextContent).length;
            splitNorm = cssTextNorm.indexOf(startSubstring, prevMinLength);
          }
          if (splitNorm === -1) splitNorm = cssNormSplits[0].length;
        }
        if (splitNorm !== -1) {
          let k = Math.floor(splitNorm / normFactor);
          for (; k > 0 && k < cssText.length; ) {
            iterCount += 1;
            if (iterCount > 50 * childNodes2.length) {
              splits.push(cssText);
              return splits;
            }
            const normPart = normalizeCssString(cssText.substring(0, k), _testNoPxNorm);
            if (normPart.length === splitNorm) {
              splits.push(cssText.substring(0, k));
              cssText = cssText.substring(k);
              cssTextNorm = cssTextNorm.substring(splitNorm);
              break;
            } else if (normPart.length < splitNorm) k += Math.max(1, Math.floor((splitNorm - normPart.length) / normFactor));
            else k -= Math.max(1, Math.floor((normPart.length - splitNorm) * normFactor));
          }
          break;
        }
      }
    }
  }
  splits.push(cssText);
  return splits;
}
function markCssSplits(cssText, style) {
  return splitCssText(cssText, style).join("/* rr_split */");
}
var _id = 1;
var tagNameRegex = /* @__PURE__ */ new RegExp("[^a-z0-9-_:]");
function genId() {
  return _id++;
}
function getValidTagName$1(element) {
  if (element instanceof HTMLFormElement) return "form";
  const processedTagName = toLowerCase(element.tagName);
  if (tagNameRegex.test(processedTagName)) return "div";
  return processedTagName;
}
var canvasService;
var canvasCtx;
var SRCSET_NOT_SPACES = /^[^ \t\n\r\u000c]+/;
var SRCSET_COMMAS_OR_SPACES = /^[, \t\n\r\u000c]+/;
function getAbsoluteSrcsetString(doc, attributeValue) {
  if (attributeValue.trim() === "") return attributeValue;
  let pos = 0;
  function collectCharacters(regEx) {
    let chars2;
    const match = regEx.exec(attributeValue.substring(pos));
    if (match) {
      chars2 = match[0];
      pos += chars2.length;
      return chars2;
    }
    return "";
  }
  const output = [];
  while (true) {
    collectCharacters(SRCSET_COMMAS_OR_SPACES);
    if (pos >= attributeValue.length) break;
    let url = collectCharacters(SRCSET_NOT_SPACES);
    if (url.slice(-1) === ",") {
      url = absoluteToDoc(doc, url.substring(0, url.length - 1));
      output.push(url);
    } else {
      let descriptorsStr = "";
      url = absoluteToDoc(doc, url);
      let inParens = false;
      while (true) {
        const c2 = attributeValue.charAt(pos);
        if (c2 === "") {
          output.push((url + descriptorsStr).trim());
          break;
        } else if (!inParens) {
          if (c2 === ",") {
            pos += 1;
            output.push((url + descriptorsStr).trim());
            break;
          } else if (c2 === "(") inParens = true;
        } else if (c2 === ")") inParens = false;
        descriptorsStr += c2;
        pos += 1;
      }
    }
  }
  return output.join(", ");
}
var cachedDocument = /* @__PURE__ */ new WeakMap();
function absoluteToDoc(doc, attributeValue) {
  if (!attributeValue || attributeValue.trim() === "") return attributeValue;
  return getHref(doc, attributeValue);
}
function isSVGElement(el) {
  return Boolean(el.tagName === "svg" || el.ownerSVGElement);
}
function getHref(doc, customHref) {
  let a2 = cachedDocument.get(doc);
  if (!a2) {
    a2 = doc.createElement("a");
    cachedDocument.set(doc, a2);
  }
  if (!customHref) customHref = "";
  else if (customHref.startsWith("blob:") || customHref.startsWith("data:")) return customHref;
  a2.setAttribute("href", customHref);
  return a2.href;
}
function transformAttribute(doc, tagName, name, value) {
  if (!value) return value;
  if (name === "src" || name === "href" && !(tagName === "use" && value[0] === "#")) return absoluteToDoc(doc, value);
  else if (name === "xlink:href" && value[0] !== "#") return absoluteToDoc(doc, value);
  else if (name === "background" && [
    "table",
    "td",
    "th"
  ].includes(tagName)) return absoluteToDoc(doc, value);
  else if (name === "srcset") return getAbsoluteSrcsetString(doc, value);
  else if (name === "style") return absolutifyURLs(value, getHref(doc));
  else if (tagName === "object" && name === "data") return absoluteToDoc(doc, value);
  return value;
}
function ignoreAttribute(tagName, name, _value) {
  return ["video", "audio"].includes(tagName) && toLowerCase(name) === "autoplay";
}
function _isBlockedElement(element, blockClass, blockSelector) {
  try {
    if (typeof blockClass === "string") {
      if (element.classList.contains(blockClass)) return true;
    } else for (let eIndex = element.classList.length; eIndex--; ) {
      const className = element.classList[eIndex];
      if (blockClass.test(className)) return true;
    }
    if (blockSelector) return element.matches(blockSelector);
  } catch (e2) {
  }
  return false;
}
function classMatchesRegex(node, regex, checkAncestors) {
  if (!node) return false;
  if (node.nodeType !== node.ELEMENT_NODE) {
    if (!checkAncestors) return false;
    return classMatchesRegex(src_default$2.parentNode(node), regex, checkAncestors);
  }
  for (let eIndex = node.classList.length; eIndex--; ) {
    const className = node.classList[eIndex];
    if (regex.test(className)) return true;
  }
  if (!checkAncestors) return false;
  return classMatchesRegex(src_default$2.parentNode(node), regex, checkAncestors);
}
function needMaskingText(node, maskTextClass, maskTextSelector, checkAncestors) {
  let el;
  if (isElement(node)) {
    el = node;
    if (!src_default$2.childNodes(el).length) return false;
  } else if (src_default$2.parentElement(node) === null) return false;
  else el = src_default$2.parentElement(node);
  try {
    if (typeof maskTextClass === "string") {
      if (checkAncestors) {
        if (el.closest(`.${maskTextClass}`)) return true;
      } else if (el.classList.contains(maskTextClass)) return true;
    } else if (classMatchesRegex(el, maskTextClass, checkAncestors)) return true;
    if (maskTextSelector) {
      if (checkAncestors) {
        if (el.closest(maskTextSelector)) return true;
      } else if (el.matches(maskTextSelector)) return true;
    }
  } catch (e2) {
  }
  return false;
}
function onceIframeLoaded(iframeEl, listener, iframeLoadTimeout) {
  const win = iframeEl.contentWindow;
  if (!win) return;
  let fired = false;
  let readyState;
  try {
    readyState = win.document.readyState;
  } catch (error) {
    return;
  }
  if (readyState !== "complete") {
    const timer = setTimeout(() => {
      if (!fired) {
        listener();
        fired = true;
      }
    }, iframeLoadTimeout);
    iframeEl.addEventListener("load", () => {
      clearTimeout(timer);
      fired = true;
      listener();
    });
    return;
  }
  const blankUrl = "about:blank";
  if (win.location.href !== blankUrl || iframeEl.src === blankUrl || iframeEl.src === "") {
    setTimeout(listener, 0);
    return iframeEl.addEventListener("load", listener);
  }
  iframeEl.addEventListener("load", listener);
}
function onceStylesheetLoaded(link, listener, styleSheetLoadTimeout) {
  let fired = false;
  let styleSheetLoaded;
  try {
    styleSheetLoaded = link.sheet;
  } catch (error) {
    return;
  }
  if (styleSheetLoaded) return;
  const timer = setTimeout(() => {
    if (!fired) {
      listener();
      fired = true;
    }
  }, styleSheetLoadTimeout);
  link.addEventListener("load", () => {
    clearTimeout(timer);
    fired = true;
    listener();
  });
}
function serializeNode(n2, options) {
  const { doc, mirror: mirror2, blockClass, blockSelector, needsMask, inlineStylesheet, maskInputOptions = {}, maskTextFn, maskInputFn, dataURLOptions = {}, inlineImages, recordCanvas, keepIframeSrcFn, newlyAddedElement = false, cssCaptured = false } = options;
  const rootId = getRootId(doc, mirror2);
  switch (n2.nodeType) {
    case n2.DOCUMENT_NODE:
      if (n2.compatMode !== "CSS1Compat") return {
        type: NodeType$3.Document,
        childNodes: [],
        compatMode: n2.compatMode
      };
      else return {
        type: NodeType$3.Document,
        childNodes: []
      };
    case n2.DOCUMENT_TYPE_NODE:
      return {
        type: NodeType$3.DocumentType,
        name: n2.name,
        publicId: n2.publicId,
        systemId: n2.systemId,
        rootId
      };
    case n2.ELEMENT_NODE:
      return serializeElementNode(n2, {
        doc,
        blockClass,
        blockSelector,
        inlineStylesheet,
        maskInputOptions,
        maskInputFn,
        dataURLOptions,
        inlineImages,
        recordCanvas,
        keepIframeSrcFn,
        newlyAddedElement,
        rootId
      });
    case n2.TEXT_NODE:
      return serializeTextNode(n2, {
        doc,
        needsMask,
        maskTextFn,
        rootId,
        cssCaptured
      });
    case n2.CDATA_SECTION_NODE:
      return {
        type: NodeType$3.CDATA,
        textContent: "",
        rootId
      };
    case n2.COMMENT_NODE:
      return {
        type: NodeType$3.Comment,
        textContent: src_default$2.textContent(n2) || "",
        rootId
      };
    default:
      return false;
  }
}
function getRootId(doc, mirror2) {
  if (!mirror2.hasNode(doc)) return void 0;
  const docId = mirror2.getId(doc);
  return docId === 1 ? void 0 : docId;
}
function serializeTextNode(n2, options) {
  const { needsMask, maskTextFn, rootId, cssCaptured } = options;
  const parent = src_default$2.parentNode(n2);
  const parentTagName = parent && parent.tagName;
  let textContent2 = "";
  const isStyle = parentTagName === "STYLE" ? true : void 0;
  const isScript = parentTagName === "SCRIPT" ? true : void 0;
  if (isScript) textContent2 = "SCRIPT_PLACEHOLDER";
  else if (!cssCaptured) {
    textContent2 = src_default$2.textContent(n2);
    if (isStyle && textContent2) textContent2 = absolutifyURLs(textContent2, getHref(options.doc));
  }
  if (!isStyle && !isScript && textContent2 && needsMask) textContent2 = maskTextFn ? maskTextFn(textContent2, src_default$2.parentElement(n2)) : textContent2.replace(/[\S]/g, "*");
  return {
    type: NodeType$3.Text,
    textContent: textContent2 || "",
    rootId
  };
}
function serializeElementNode(n2, options) {
  const { doc, blockClass, blockSelector, inlineStylesheet, maskInputOptions = {}, maskInputFn, dataURLOptions = {}, inlineImages, recordCanvas, keepIframeSrcFn, newlyAddedElement = false, rootId } = options;
  const needBlock = _isBlockedElement(n2, blockClass, blockSelector);
  const tagName = getValidTagName$1(n2);
  let attributes = {};
  const len = n2.attributes.length;
  for (let i2 = 0; i2 < len; i2++) {
    const attr = n2.attributes[i2];
    if (!ignoreAttribute(tagName, attr.name, attr.value)) attributes[attr.name] = transformAttribute(doc, tagName, toLowerCase(attr.name), attr.value);
  }
  if (tagName === "link" && inlineStylesheet) {
    const stylesheet = Array.from(doc.styleSheets).find((s2) => {
      return s2.href === n2.href;
    });
    let cssText = null;
    if (stylesheet) cssText = stringifyStylesheet(stylesheet);
    if (cssText) {
      delete attributes.rel;
      delete attributes.href;
      attributes._cssText = cssText;
    }
  }
  if (tagName === "style" && n2.sheet) {
    let cssText = stringifyStylesheet(n2.sheet);
    if (cssText) {
      if (n2.childNodes.length > 1) cssText = markCssSplits(cssText, n2);
      attributes._cssText = cssText;
    }
  }
  if ([
    "input",
    "textarea",
    "select"
  ].includes(tagName)) {
    const value = n2.value;
    const checked = n2.checked;
    if (attributes.type !== "radio" && attributes.type !== "checkbox" && attributes.type !== "submit" && attributes.type !== "button" && value) attributes.value = maskInputValue({
      element: n2,
      type: getInputType(n2),
      tagName,
      value,
      maskInputOptions,
      maskInputFn
    });
    else if (checked) attributes.checked = checked;
  }
  if (tagName === "option") {
    if (n2.selected && !maskInputOptions["select"]) attributes.selected = true;
    else delete attributes.selected;
  }
  if (tagName === "dialog" && n2.open) attributes.rr_open_mode = n2.matches("dialog:modal") ? "modal" : "non-modal";
  if (tagName === "canvas" && recordCanvas) {
    if (n2.__context === "2d") {
      if (!is2DCanvasBlank(n2)) attributes.rr_dataURL = n2.toDataURL(dataURLOptions.type, dataURLOptions.quality);
    } else if (!("__context" in n2)) {
      const canvasDataURL = n2.toDataURL(dataURLOptions.type, dataURLOptions.quality);
      const blankCanvas = doc.createElement("canvas");
      blankCanvas.width = n2.width;
      blankCanvas.height = n2.height;
      if (canvasDataURL !== blankCanvas.toDataURL(dataURLOptions.type, dataURLOptions.quality)) attributes.rr_dataURL = canvasDataURL;
    }
  }
  if (tagName === "img" && inlineImages) {
    if (!canvasService) {
      canvasService = doc.createElement("canvas");
      canvasCtx = canvasService.getContext("2d");
    }
    const image = n2;
    const imageSrc = image.currentSrc || image.getAttribute("src") || "<unknown-src>";
    const priorCrossOrigin = image.crossOrigin;
    const recordInlineImage = () => {
      image.removeEventListener("load", recordInlineImage);
      try {
        canvasService.width = image.naturalWidth;
        canvasService.height = image.naturalHeight;
        canvasCtx.drawImage(image, 0, 0);
        attributes.rr_dataURL = canvasService.toDataURL(dataURLOptions.type, dataURLOptions.quality);
      } catch (err) {
        if (image.crossOrigin !== "anonymous") {
          image.crossOrigin = "anonymous";
          if (image.complete && image.naturalWidth !== 0) recordInlineImage();
          else image.addEventListener("load", recordInlineImage);
          return;
        } else console.warn(`Cannot inline img src=${imageSrc}! Error: ${err}`);
      }
      if (image.crossOrigin === "anonymous") priorCrossOrigin ? attributes.crossOrigin = priorCrossOrigin : image.removeAttribute("crossorigin");
    };
    if (image.complete && image.naturalWidth !== 0) recordInlineImage();
    else image.addEventListener("load", recordInlineImage);
  }
  if (["audio", "video"].includes(tagName)) {
    const mediaAttributes = attributes;
    mediaAttributes.rr_mediaState = n2.paused ? "paused" : "played";
    mediaAttributes.rr_mediaCurrentTime = n2.currentTime;
    mediaAttributes.rr_mediaPlaybackRate = n2.playbackRate;
    mediaAttributes.rr_mediaMuted = n2.muted;
    mediaAttributes.rr_mediaLoop = n2.loop;
    mediaAttributes.rr_mediaVolume = n2.volume;
  }
  if (!newlyAddedElement) {
    if (n2.scrollLeft) attributes.rr_scrollLeft = n2.scrollLeft;
    if (n2.scrollTop) attributes.rr_scrollTop = n2.scrollTop;
  }
  if (needBlock) {
    const { width, height } = n2.getBoundingClientRect();
    attributes = {
      class: attributes.class,
      rr_width: `${width}px`,
      rr_height: `${height}px`
    };
  }
  if (tagName === "iframe" && !keepIframeSrcFn(attributes.src)) {
    if (!n2.contentDocument) attributes.rr_src = attributes.src;
    delete attributes.src;
  }
  let isCustomElement;
  try {
    if (customElements.get(tagName)) isCustomElement = true;
  } catch (e2) {
  }
  return {
    type: NodeType$3.Element,
    tagName,
    attributes,
    childNodes: [],
    isSVG: isSVGElement(n2) || void 0,
    needBlock,
    rootId,
    isCustom: isCustomElement
  };
}
function lowerIfExists(maybeAttr) {
  if (maybeAttr === void 0 || maybeAttr === null) return "";
  else return maybeAttr.toLowerCase();
}
function slimDOMDefaults(_slimDOMOptions) {
  if (_slimDOMOptions === true || _slimDOMOptions === "all") return {
    script: true,
    comment: true,
    headFavicon: true,
    headWhitespace: true,
    headMetaSocial: true,
    headMetaRobots: true,
    headMetaHttpEquiv: true,
    headMetaVerification: true,
    headMetaAuthorship: _slimDOMOptions === "all",
    headMetaDescKeywords: _slimDOMOptions === "all",
    headTitleMutations: _slimDOMOptions === "all"
  };
  else if (_slimDOMOptions) return _slimDOMOptions;
  return {};
}
function slimDOMExcluded(sn, slimDOMOptions) {
  if (slimDOMOptions.comment && sn.type === NodeType$3.Comment) return true;
  else if (sn.type === NodeType$3.Element) {
    if (slimDOMOptions.script && (sn.tagName === "script" || sn.tagName === "link" && (sn.attributes.rel === "preload" && sn.attributes.as === "script" || sn.attributes.rel === "modulepreload") || sn.tagName === "link" && sn.attributes.rel === "prefetch" && typeof sn.attributes.href === "string" && extractFileExtension(sn.attributes.href) === "js")) return true;
    else if (slimDOMOptions.headFavicon && (sn.tagName === "link" && sn.attributes.rel === "shortcut icon" || sn.tagName === "meta" && (lowerIfExists(sn.attributes.name).match(/^msapplication-tile(image|color)$/) || lowerIfExists(sn.attributes.name) === "application-name" || lowerIfExists(sn.attributes.rel) === "icon" || lowerIfExists(sn.attributes.rel) === "apple-touch-icon" || lowerIfExists(sn.attributes.rel) === "shortcut icon"))) return true;
    else if (sn.tagName === "meta") {
      if (slimDOMOptions.headMetaDescKeywords && lowerIfExists(sn.attributes.name).match(/^description|keywords$/)) return true;
      else if (slimDOMOptions.headMetaSocial && (lowerIfExists(sn.attributes.property).match(/^(og|twitter|fb):/) || lowerIfExists(sn.attributes.name).match(/^(og|twitter):/) || lowerIfExists(sn.attributes.name) === "pinterest")) return true;
      else if (slimDOMOptions.headMetaRobots && (lowerIfExists(sn.attributes.name) === "robots" || lowerIfExists(sn.attributes.name) === "googlebot" || lowerIfExists(sn.attributes.name) === "bingbot")) return true;
      else if (slimDOMOptions.headMetaHttpEquiv && sn.attributes["http-equiv"] !== void 0) return true;
      else if (slimDOMOptions.headMetaAuthorship && (lowerIfExists(sn.attributes.name) === "author" || lowerIfExists(sn.attributes.name) === "generator" || lowerIfExists(sn.attributes.name) === "framework" || lowerIfExists(sn.attributes.name) === "publisher" || lowerIfExists(sn.attributes.name) === "progid" || lowerIfExists(sn.attributes.property).match(/^article:/) || lowerIfExists(sn.attributes.property).match(/^product:/))) return true;
      else if (slimDOMOptions.headMetaVerification && (lowerIfExists(sn.attributes.name) === "google-site-verification" || lowerIfExists(sn.attributes.name) === "yandex-verification" || lowerIfExists(sn.attributes.name) === "csrf-token" || lowerIfExists(sn.attributes.name) === "p:domain_verify" || lowerIfExists(sn.attributes.name) === "verify-v1" || lowerIfExists(sn.attributes.name) === "verification" || lowerIfExists(sn.attributes.name) === "shopify-checkout-api-token")) return true;
    }
  }
  return false;
}
function serializeNodeWithId(n2, options) {
  const { doc, mirror: mirror2, blockClass, blockSelector, maskTextClass, maskTextSelector, skipChild = false, inlineStylesheet = true, maskInputOptions = {}, maskTextFn, maskInputFn, slimDOMOptions, dataURLOptions = {}, inlineImages = false, recordCanvas = false, onSerialize, onIframeLoad, iframeLoadTimeout = 5e3, onStylesheetLoad, stylesheetLoadTimeout = 5e3, keepIframeSrcFn = () => false, newlyAddedElement = false, cssCaptured = false } = options;
  let { needsMask } = options;
  let { preserveWhiteSpace = true } = options;
  if (!needsMask) needsMask = needMaskingText(n2, maskTextClass, maskTextSelector, needsMask === void 0);
  const _serializedNode = serializeNode(n2, {
    doc,
    mirror: mirror2,
    blockClass,
    blockSelector,
    needsMask,
    inlineStylesheet,
    maskInputOptions,
    maskTextFn,
    maskInputFn,
    dataURLOptions,
    inlineImages,
    recordCanvas,
    keepIframeSrcFn,
    newlyAddedElement,
    cssCaptured
  });
  if (!_serializedNode) {
    console.warn(n2, "not serialized");
    return null;
  }
  let id;
  if (mirror2.hasNode(n2)) id = mirror2.getId(n2);
  else if (slimDOMExcluded(_serializedNode, slimDOMOptions) || !preserveWhiteSpace && _serializedNode.type === NodeType$3.Text && !_serializedNode.textContent.replace(/^\s+|\s+$/gm, "").length) id = -2;
  else id = genId();
  const serializedNode = Object.assign(_serializedNode, { id });
  mirror2.add(n2, serializedNode);
  if (id === -2) return null;
  if (onSerialize) onSerialize(n2);
  let recordChild = !skipChild;
  if (serializedNode.type === NodeType$3.Element) {
    recordChild = recordChild && !serializedNode.needBlock;
    delete serializedNode.needBlock;
    const shadowRootEl = src_default$2.shadowRoot(n2);
    if (shadowRootEl && isNativeShadowDom(shadowRootEl)) serializedNode.isShadowHost = true;
  }
  if ((serializedNode.type === NodeType$3.Document || serializedNode.type === NodeType$3.Element) && recordChild) {
    if (slimDOMOptions.headWhitespace && serializedNode.type === NodeType$3.Element && serializedNode.tagName === "head") preserveWhiteSpace = false;
    const bypassOptions = {
      doc,
      mirror: mirror2,
      blockClass,
      blockSelector,
      needsMask,
      maskTextClass,
      maskTextSelector,
      skipChild,
      inlineStylesheet,
      maskInputOptions,
      maskTextFn,
      maskInputFn,
      slimDOMOptions,
      dataURLOptions,
      inlineImages,
      recordCanvas,
      preserveWhiteSpace,
      onSerialize,
      onIframeLoad,
      iframeLoadTimeout,
      onStylesheetLoad,
      stylesheetLoadTimeout,
      keepIframeSrcFn,
      cssCaptured: false
    };
    if (serializedNode.type === NodeType$3.Element && serializedNode.tagName === "textarea" && serializedNode.attributes.value !== void 0) {
    } else {
      if (serializedNode.type === NodeType$3.Element && serializedNode.attributes._cssText !== void 0 && typeof serializedNode.attributes._cssText === "string") bypassOptions.cssCaptured = true;
      for (const childN of Array.from(src_default$2.childNodes(n2))) {
        const serializedChildNode = serializeNodeWithId(childN, bypassOptions);
        if (serializedChildNode) serializedNode.childNodes.push(serializedChildNode);
      }
    }
    let shadowRootEl = null;
    if (isElement(n2) && (shadowRootEl = src_default$2.shadowRoot(n2))) for (const childN of Array.from(src_default$2.childNodes(shadowRootEl))) {
      const serializedChildNode = serializeNodeWithId(childN, bypassOptions);
      if (serializedChildNode) {
        isNativeShadowDom(shadowRootEl) && (serializedChildNode.isShadow = true);
        serializedNode.childNodes.push(serializedChildNode);
      }
    }
  }
  const parent = src_default$2.parentNode(n2);
  if (parent && isShadowRoot(parent) && isNativeShadowDom(parent)) serializedNode.isShadow = true;
  if (serializedNode.type === NodeType$3.Element && serializedNode.tagName === "iframe" && !_isBlockedElement(n2, blockClass, blockSelector)) onceIframeLoaded(n2, () => {
    const iframeDoc = n2.contentDocument;
    if (iframeDoc && onIframeLoad) {
      const serializedIframeNode = serializeNodeWithId(iframeDoc, {
        doc: iframeDoc,
        mirror: mirror2,
        blockClass,
        blockSelector,
        needsMask,
        maskTextClass,
        maskTextSelector,
        skipChild: false,
        inlineStylesheet,
        maskInputOptions,
        maskTextFn,
        maskInputFn,
        slimDOMOptions,
        dataURLOptions,
        inlineImages,
        recordCanvas,
        preserveWhiteSpace,
        onSerialize,
        onIframeLoad,
        iframeLoadTimeout,
        onStylesheetLoad,
        stylesheetLoadTimeout,
        keepIframeSrcFn
      });
      if (serializedIframeNode) onIframeLoad(n2, serializedIframeNode);
    }
  }, iframeLoadTimeout);
  if (serializedNode.type === NodeType$3.Element && serializedNode.tagName === "link" && typeof serializedNode.attributes.rel === "string" && (serializedNode.attributes.rel === "stylesheet" || serializedNode.attributes.rel === "preload" && typeof serializedNode.attributes.href === "string" && extractFileExtension(serializedNode.attributes.href) === "css")) onceStylesheetLoaded(n2, () => {
    if (onStylesheetLoad) {
      const serializedLinkNode = serializeNodeWithId(n2, {
        doc,
        mirror: mirror2,
        blockClass,
        blockSelector,
        needsMask,
        maskTextClass,
        maskTextSelector,
        skipChild: false,
        inlineStylesheet,
        maskInputOptions,
        maskTextFn,
        maskInputFn,
        slimDOMOptions,
        dataURLOptions,
        inlineImages,
        recordCanvas,
        preserveWhiteSpace,
        onSerialize,
        onIframeLoad,
        iframeLoadTimeout,
        onStylesheetLoad,
        stylesheetLoadTimeout,
        keepIframeSrcFn
      });
      if (serializedLinkNode) onStylesheetLoad(n2, serializedLinkNode);
    }
  }, stylesheetLoadTimeout);
  return serializedNode;
}
function snapshot(n2, options) {
  const { mirror: mirror2 = new Mirror$2(), blockClass = "rr-block", blockSelector = null, maskTextClass = "rr-mask", maskTextSelector = null, inlineStylesheet = true, inlineImages = false, recordCanvas = false, maskAllInputs = false, maskTextFn, maskInputFn, slimDOM = false, dataURLOptions, preserveWhiteSpace, onSerialize, onIframeLoad, iframeLoadTimeout, onStylesheetLoad, stylesheetLoadTimeout, keepIframeSrcFn = () => false } = options || {};
  return serializeNodeWithId(n2, {
    doc: n2,
    mirror: mirror2,
    blockClass,
    blockSelector,
    maskTextClass,
    maskTextSelector,
    skipChild: false,
    inlineStylesheet,
    maskInputOptions: maskAllInputs === true ? {
      color: true,
      date: true,
      "datetime-local": true,
      email: true,
      month: true,
      number: true,
      range: true,
      search: true,
      tel: true,
      text: true,
      time: true,
      url: true,
      week: true,
      textarea: true,
      select: true,
      password: true
    } : maskAllInputs === false ? { password: true } : maskAllInputs,
    maskTextFn,
    maskInputFn,
    slimDOMOptions: slimDOMDefaults(slimDOM),
    dataURLOptions,
    inlineImages,
    recordCanvas,
    preserveWhiteSpace,
    onSerialize,
    onIframeLoad,
    iframeLoadTimeout,
    onStylesheetLoad,
    stylesheetLoadTimeout,
    keepIframeSrcFn,
    newlyAddedElement: false
  });
}
var MEDIA_SELECTOR_GLOBAL = new RegExp(/(max|min)-device-(width|height)/.source, "g");
var mediaSelectorPlugin = {
  postcssPlugin: "postcss-custom-selectors",
  prepare() {
    return {
      postcssPlugin: "postcss-custom-selectors",
      AtRule: function(atrule) {
        if (atrule.params.match(MEDIA_SELECTOR_GLOBAL)) atrule.params = atrule.params.replace(MEDIA_SELECTOR_GLOBAL, "$1-$2");
      }
    };
  }
};
var pseudoClassPlugin = {
  postcssPlugin: "postcss-hover-classes",
  prepare: function() {
    const fixed = [];
    return { Rule: function(rule) {
      if (fixed.indexOf(rule) !== -1) return;
      fixed.push(rule);
      rule.selectors.forEach(function(selector) {
        if (selector.includes(":hover")) rule.selector += ",\n" + selector.replace(/:hover/g, ".\\:hover");
      });
    } };
  }
};
var require_picocolors_browser$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var x = String;
  var create = function() {
    return {
      isColorSupported: false,
      reset: x,
      bold: x,
      dim: x,
      italic: x,
      underline: x,
      inverse: x,
      hidden: x,
      strikethrough: x,
      black: x,
      red: x,
      green: x,
      yellow: x,
      blue: x,
      magenta: x,
      cyan: x,
      white: x,
      gray: x,
      bgBlack: x,
      bgRed: x,
      bgGreen: x,
      bgYellow: x,
      bgBlue: x,
      bgMagenta: x,
      bgCyan: x,
      bgWhite: x,
      blackBright: x,
      redBright: x,
      greenBright: x,
      yellowBright: x,
      blueBright: x,
      magentaBright: x,
      cyanBright: x,
      whiteBright: x,
      bgBlackBright: x,
      bgRedBright: x,
      bgGreenBright: x,
      bgYellowBright: x,
      bgBlueBright: x,
      bgMagentaBright: x,
      bgCyanBright: x,
      bgWhiteBright: x
    };
  };
  module2.exports = create();
  module2.exports.createColors = create;
});
var require___vite_browser_external$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  module2.exports = {};
});
var require_css_syntax_error$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var pico = require_picocolors_browser$1();
  var terminalHighlight = require___vite_browser_external$1();
  var CssSyntaxError = class CssSyntaxError2 extends Error {
    constructor(message, line, column, source, file, plugin) {
      super(message);
      this.name = "CssSyntaxError";
      this.reason = message;
      if (file) this.file = file;
      if (source) this.source = source;
      if (plugin) this.plugin = plugin;
      if (typeof line !== "undefined" && typeof column !== "undefined") {
        if (typeof line === "number") {
          this.line = line;
          this.column = column;
        } else {
          this.line = line.line;
          this.column = line.column;
          this.endLine = column.line;
          this.endColumn = column.column;
        }
      }
      this.setMessage();
      if (Error.captureStackTrace) Error.captureStackTrace(this, CssSyntaxError2);
    }
    setMessage() {
      this.message = this.plugin ? this.plugin + ": " : "";
      this.message += this.file ? this.file : "<css input>";
      if (typeof this.line !== "undefined") this.message += ":" + this.line + ":" + this.column;
      this.message += ": " + this.reason;
    }
    showSourceCode(color) {
      if (!this.source) return "";
      let css = this.source;
      if (color == null) color = pico.isColorSupported;
      let aside = (text) => text;
      let mark = (text) => text;
      let highlight = (text) => text;
      if (color) {
        let { bold, gray, red } = pico.createColors(true);
        mark = (text) => bold(red(text));
        aside = (text) => gray(text);
        if (terminalHighlight) highlight = (text) => terminalHighlight(text);
      }
      let lines = css.split(/\r?\n/);
      let start = Math.max(this.line - 3, 0);
      let end = Math.min(this.line + 2, lines.length);
      let maxWidth = String(end).length;
      return lines.slice(start, end).map((line, index) => {
        let number = start + 1 + index;
        let gutter = " " + (" " + number).slice(-maxWidth) + " | ";
        if (number === this.line) {
          if (line.length > 160) {
            let padding = 20;
            let subLineStart = Math.max(0, this.column - padding);
            let subLineEnd = Math.max(this.column + padding, this.endColumn + padding);
            let subLine = line.slice(subLineStart, subLineEnd);
            let spacing2 = aside(gutter.replace(/\d/g, " ")) + line.slice(0, Math.min(this.column - 1, 19)).replace(/[^\t]/g, " ");
            return mark(">") + aside(gutter) + highlight(subLine) + "\n " + spacing2 + mark("^");
          }
          let spacing = aside(gutter.replace(/\d/g, " ")) + line.slice(0, this.column - 1).replace(/[^\t]/g, " ");
          return mark(">") + aside(gutter) + highlight(line) + "\n " + spacing + mark("^");
        }
        return " " + aside(gutter) + highlight(line);
      }).join("\n");
    }
    toString() {
      let code = this.showSourceCode();
      if (code) code = "\n\n" + code + "\n";
      return this.name + ": " + this.message + code;
    }
  };
  module2.exports = CssSyntaxError;
  CssSyntaxError.default = CssSyntaxError;
});
var require_stringifier$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var AT_NAME_END = /[\t\n\f\r "#'()/;[\\\]{}]/;
  var DEFAULT_RAW = {
    after: "\n",
    beforeClose: "\n",
    beforeComment: "\n",
    beforeDecl: "\n",
    beforeOpen: " ",
    beforeRule: "\n",
    colon: ": ",
    commentLeft: " ",
    commentRight: " ",
    emptyBody: "",
    indent: "    ",
    semicolon: false
  };
  function capitalize(str) {
    return str[0].toUpperCase() + str.slice(1);
  }
  function atruleStart(str, node) {
    let name = "@" + node.name;
    let params = node.params ? str.rawValue(node, "params") : "";
    let afterName = node.raws.afterName;
    if (typeof afterName === "undefined") afterName = params ? " " : "";
    else if (afterName === "" && params && !AT_NAME_END.test(params[0])) afterName = " ";
    return name + afterName + params;
  }
  function isCustomProperty(node) {
    if (!node.prop.startsWith("--")) return false;
    let before = node.raws.before;
    return typeof before === "undefined" || !/[^\s;]$/.test(before);
  }
  function pushBody(str, stack, node) {
    let nodes = node.nodes;
    let last = nodes.length - 1;
    while (last > 0) {
      if (nodes[last].type !== "comment") break;
      last -= 1;
    }
    let semicolon = str.raw(node, "semicolon");
    let isDocument = node.type === "document";
    for (let i2 = nodes.length - 1; i2 >= 0; i2--) {
      let child = nodes[i2];
      let childSemicolon = last !== i2 || semicolon;
      if (!childSemicolon && i2 < nodes.length - 1 && (child.type === "atrule" && !child.nodes || child.type === "decl" && isCustomProperty(child))) childSemicolon = true;
      stack.push({
        document: isDocument,
        node: child,
        semicolon: childSemicolon
      });
    }
  }
  function pushBlock(str, stack, node, start) {
    let between = str.raw(node, "between", "beforeOpen");
    str.builder(start + between + "{", node, "start");
    let hasNodes = node.nodes && node.nodes.length;
    let close = () => {
      let after = hasNodes ? str.raw(node, "after") : str.raw(node, "after", "emptyBody");
      if (after) str.builder(after);
      str.builder("}", node, "end");
      if (node.type === "rule" && node.raws.ownSemicolon) str.builder(node.raws.ownSemicolon, node, "end");
    };
    if (hasNodes) {
      stack.push(close);
      pushBody(str, stack, node);
    } else close();
  }
  var Stringifier = class Stringifier2 {
    constructor(builder) {
      this.builder = builder;
    }
    atrule(node, semicolon) {
      let start = atruleStart(this, node);
      if (node.nodes) this.block(node, start);
      else {
        let end = (node.raws.between || "") + (semicolon ? ";" : "");
        this.builder(start + end, node);
      }
    }
    beforeAfter(node, detect) {
      let value;
      if (node.type === "decl") value = this.raw(node, null, "beforeDecl");
      else if (node.type === "comment") value = this.raw(node, null, "beforeComment");
      else if (detect === "before") value = this.raw(node, null, "beforeRule");
      else value = this.raw(node, null, "beforeClose");
      let buf = node.parent;
      let depth = 0;
      while (buf && buf.type !== "root") {
        depth += 1;
        buf = buf.parent;
      }
      if (value.includes("\n")) {
        let indent = this.raw(node, null, "indent");
        if (indent.length) for (let step = 0; step < depth; step++) value += indent;
      }
      return value;
    }
    block(node, start) {
      let between = this.raw(node, "between", "beforeOpen");
      this.builder(start + between + "{", node, "start");
      let after;
      if (node.nodes && node.nodes.length) {
        this.body(node);
        after = this.raw(node, "after");
      } else after = this.raw(node, "after", "emptyBody");
      if (after) this.builder(after);
      this.builder("}", node, "end");
    }
    body(node) {
      let proto = Stringifier2.prototype;
      let expandable = [
        "atrule",
        "block",
        "body",
        "rule",
        "stringify"
      ].every((method) => this[method] === proto[method]);
      let stack = [];
      pushBody(this, stack, node);
      while (stack.length > 0) {
        let entry = stack.pop();
        if (typeof entry === "function") {
          entry();
          continue;
        }
        let child = entry.node;
        let before = this.raw(child, "before");
        if (before) this.builder(before);
        if (expandable && child.type === "rule") pushBlock(this, stack, child, this.rawValue(child, "selector"));
        else if (expandable && child.type === "atrule" && child.nodes) pushBlock(this, stack, child, atruleStart(this, child));
        else this.stringify(child, entry.semicolon);
      }
    }
    comment(node) {
      let left = this.raw(node, "left", "commentLeft");
      let right = this.raw(node, "right", "commentRight");
      this.builder("/*" + left + node.text + right + "*/", node);
    }
    decl(node, semicolon) {
      let raws = node.raws;
      let between = this.raw(node, "between", "colon");
      let string = node.prop + between + this.rawValue(node, "value");
      if (node.important) string += raws.important || " !important";
      if (semicolon) string += ";";
      this.builder(string, node);
    }
    document(node) {
      this.body(node);
    }
    raw(node, own, detect) {
      let value;
      if (!detect) detect = own;
      if (own) {
        value = node.raws[own];
        if (typeof value !== "undefined") return value;
      }
      let parent = node.parent;
      if (detect === "before") {
        if (!parent || parent.type === "root" && parent.first === node) return "";
        if (parent && parent.type === "document") return "";
      }
      if (!parent) return DEFAULT_RAW[detect];
      let root = node.root();
      let cache = root.rawCache || (root.rawCache = {});
      if (typeof cache[detect] !== "undefined") return cache[detect];
      if (detect === "before" || detect === "after") return this.beforeAfter(node, detect);
      else {
        let method = "raw" + capitalize(detect);
        if (this[method]) value = this[method](root, node);
        else root.walk((i2) => {
          value = i2.raws[own];
          if (typeof value !== "undefined") return false;
        });
      }
      if (typeof value === "undefined") value = DEFAULT_RAW[detect];
      cache[detect] = value;
      return value;
    }
    rawBeforeClose(root) {
      let value;
      root.walk((i2) => {
        if (i2.nodes && i2.nodes.length > 0) {
          if (typeof i2.raws.after !== "undefined") {
            value = i2.raws.after;
            if (value.includes("\n")) value = value.replace(/[^\n]+$/, "");
            return false;
          }
        }
      });
      if (value) value = value.replace(/\S/g, "");
      return value;
    }
    rawBeforeComment(root, node) {
      let value;
      root.walkComments((i2) => {
        if (typeof i2.raws.before !== "undefined") {
          value = i2.raws.before;
          if (value.includes("\n")) value = value.replace(/[^\n]+$/, "");
          return false;
        }
      });
      if (typeof value === "undefined") value = this.raw(node, null, "beforeDecl");
      else if (value) value = value.replace(/\S/g, "");
      return value;
    }
    rawBeforeDecl(root, node) {
      let value;
      root.walkDecls((i2) => {
        if (typeof i2.raws.before !== "undefined") {
          value = i2.raws.before;
          if (value.includes("\n")) value = value.replace(/[^\n]+$/, "");
          return false;
        }
      });
      if (typeof value === "undefined") value = this.raw(node, null, "beforeRule");
      else if (value) value = value.replace(/\S/g, "");
      return value;
    }
    rawBeforeOpen(root) {
      let value;
      root.walk((i2) => {
        if (i2.type !== "decl") {
          value = i2.raws.between;
          if (typeof value !== "undefined") return false;
        }
      });
      return value;
    }
    rawBeforeRule(root) {
      let value;
      root.walk((i2) => {
        if (i2.nodes && (i2.parent !== root || root.first !== i2)) {
          if (typeof i2.raws.before !== "undefined") {
            value = i2.raws.before;
            if (value.includes("\n")) value = value.replace(/[^\n]+$/, "");
            return false;
          }
        }
      });
      if (value) value = value.replace(/\S/g, "");
      return value;
    }
    rawColon(root) {
      let value;
      root.walkDecls((i2) => {
        if (typeof i2.raws.between !== "undefined") {
          value = i2.raws.between.replace(/[^\s:]/g, "");
          return false;
        }
      });
      return value;
    }
    rawEmptyBody(root) {
      let value;
      root.walk((i2) => {
        if (i2.nodes && i2.nodes.length === 0) {
          value = i2.raws.after;
          if (typeof value !== "undefined") return false;
        }
      });
      return value;
    }
    rawIndent(root) {
      if (root.raws.indent) return root.raws.indent;
      let value;
      root.walk((i2) => {
        let p = i2.parent;
        if (p && p !== root && p.parent && p.parent === root) {
          if (typeof i2.raws.before !== "undefined") {
            let parts = i2.raws.before.split("\n");
            value = parts[parts.length - 1];
            value = value.replace(/\S/g, "");
            return false;
          }
        }
      });
      return value;
    }
    rawSemicolon(root) {
      let value;
      root.walk((i2) => {
        if (i2.nodes && i2.nodes.length && i2.last.type === "decl") {
          value = i2.raws.semicolon;
          if (typeof value !== "undefined") return false;
        }
      });
      return value;
    }
    rawValue(node, prop) {
      let value = node[prop];
      let raw = node.raws[prop];
      if (raw && raw.value === value) return raw.raw;
      return value;
    }
    root(node) {
      if (!(node.parent && node.parent.type === "document") && node.source && node.source.input.hasBOM) this.builder("\uFEFF", node, "start");
      this.body(node);
      if (node.raws.after) this.builder(node.raws.after);
    }
    rule(node) {
      this.block(node, this.rawValue(node, "selector"));
      if (node.raws.ownSemicolon) this.builder(node.raws.ownSemicolon, node, "end");
    }
    stringify(node, semicolon) {
      if (!this[node.type]) throw new Error("Unknown AST node type " + node.type + ". Maybe you need to change PostCSS stringifier.");
      this[node.type](node, semicolon);
    }
  };
  module2.exports = Stringifier;
  Stringifier.default = Stringifier;
});
var require_stringify$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Stringifier = require_stringifier$1();
  var STYLE_TAG = /(<)(\/?style\b)/gi;
  var COMMENT_OPEN = /(<)(!--)/g;
  function escapeHTMLInCSS(str) {
    if (!str.includes("<")) return str;
    return str.replace(STYLE_TAG, "\\3c $2").replace(COMMENT_OPEN, "\\3c $2");
  }
  function cutEscaped(chunks, css, escaped) {
    let parts = [];
    let at = 0;
    let into = 0;
    for (let i2 = 0; i2 < chunks.length; i2 += 3) {
      let from = into;
      let end = at + chunks[i2].length;
      while (at < end) if (css[at] === escaped[into]) {
        at += 1;
        into += 1;
      } else {
        at += 1;
        into += 4;
      }
      parts.push(escaped.slice(from, into));
    }
    return parts;
  }
  var SafeStringifier = class extends Stringifier {
    escapeHTML(print) {
      let builder = this.builder;
      let chunks, css;
      this.builder = (str, child, type) => {
        if (chunks) {
          css += str;
          chunks.push(str, child, type);
        } else if (str.includes("<")) {
          css = str;
          chunks = [
            str,
            child,
            type
          ];
        } else builder(str, child, type);
      };
      print();
      this.builder = builder;
      if (!chunks) return;
      let escaped = escapeHTMLInCSS(css);
      if (escaped === css) for (let i2 = 0; i2 < chunks.length; i2 += 3) builder(chunks[i2], chunks[i2 + 1], chunks[i2 + 2]);
      else {
        let parts = cutEscaped(chunks, css, escaped);
        for (let i2 = 0; i2 < chunks.length; i2 += 3) builder(parts[i2 / 3], chunks[i2 + 1], chunks[i2 + 2]);
      }
    }
    root(node) {
      if (node.parent && node.parent.type === "document") {
        this.escapeHTML(() => this.body(node));
        if (node.raws.after) this.builder(node.raws.after);
      } else this.escapeHTML(() => super.root(node));
    }
  };
  function stringify(node, builder) {
    let str = new SafeStringifier(builder);
    if (node.type === "root" || node.type === "document") str.stringify(node);
    else str.escapeHTML(() => str.stringify(node));
  }
  module2.exports = stringify;
  stringify.default = stringify;
});
var require_symbols$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  module2.exports.isClean = Symbol("isClean");
  module2.exports.my = Symbol("my");
});
var require_node$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var CssSyntaxError = require_css_syntax_error$1();
  var Stringifier = require_stringifier$1();
  var stringify = require_stringify$1();
  var { isClean, my } = require_symbols$1();
  function cloneNode(obj, parent) {
    let cloned = new obj.constructor();
    let stack = [[
      obj,
      cloned,
      parent
    ]];
    while (stack.length > 0) {
      let [source, target, targetParent] = stack.pop();
      for (let i2 in source) {
        if (!Object.prototype.hasOwnProperty.call(source, i2))
          continue;
        if (i2 === "proxyCache") continue;
        let value = source[i2];
        let type = typeof value;
        if (i2 === "parent" && type === "object") {
          if (targetParent) target[i2] = targetParent;
        } else if (i2 === "source") target[i2] = value;
        else if (Array.isArray(value)) {
          let children = [];
          target[i2] = children;
          for (let j of value) {
            let childClone = new j.constructor();
            children.push(childClone);
            stack.push([
              j,
              childClone,
              target
            ]);
          }
        } else {
          if (type === "object" && value !== null) {
            let valueClone = new value.constructor();
            stack.push([
              value,
              valueClone,
              void 0
            ]);
            value = valueClone;
          }
          target[i2] = value;
        }
      }
    }
    return cloned;
  }
  function sourceOffset(inputCSS, position) {
    if (position && typeof position.offset !== "undefined") return position.offset;
    let column = 1;
    let line = 1;
    let offset = 0;
    for (let i2 = 0; i2 < inputCSS.length; i2++) {
      if (line === position.line && column === position.column) {
        offset = i2;
        break;
      }
      if (inputCSS[i2] === "\n") {
        column = 1;
        line += 1;
      } else column += 1;
    }
    return offset;
  }
  var Node2 = class Node3 {
    get proxyOf() {
      return this;
    }
    constructor(defaults = {}) {
      this.raws = {};
      this[isClean] = false;
      this[my] = true;
      for (let name of Object.keys(defaults)) {
        if (name === "__proto__") continue;
        if (name === "nodes") {
          this.nodes = [];
          for (let node of defaults[name]) if (typeof node.clone === "function" && node.parent) this.append(node.clone());
          else this.append(node);
        } else this[name] = defaults[name];
      }
    }
    addToError(error) {
      error.postcssNode = this;
      if (error.stack && this.source && /\n\s{4}at /.test(error.stack)) {
        let s2 = this.source;
        error.stack = error.stack.replace(/\n\s{4}at /, `$&${s2.input.from}:${s2.start.line}:${s2.start.column}$&`);
      }
      return error;
    }
    after(add) {
      this.parent.insertAfter(this, add);
      return this;
    }
    assign(overrides = {}) {
      for (let name in overrides) this[name] = overrides[name];
      return this;
    }
    before(add) {
      this.parent.insertBefore(this, add);
      return this;
    }
    cleanRaws(keepBetween) {
      delete this.raws.before;
      delete this.raws.after;
      if (!keepBetween) delete this.raws.between;
    }
    clone(overrides = {}) {
      let cloned = cloneNode(this);
      for (let name in overrides) cloned[name] = overrides[name];
      return cloned;
    }
    cloneAfter(overrides = {}) {
      let cloned = this.clone(overrides);
      this.parent.insertAfter(this, cloned);
      return cloned;
    }
    cloneBefore(overrides = {}) {
      let cloned = this.clone(overrides);
      this.parent.insertBefore(this, cloned);
      return cloned;
    }
    error(message, opts = {}) {
      if (this.source) {
        let { end, start } = this.rangeBy(opts);
        return this.source.input.error(message, {
          column: start.column,
          line: start.line
        }, {
          column: end.column,
          line: end.line
        }, opts);
      }
      return new CssSyntaxError(message);
    }
    getProxyProcessor() {
      return {
        get(node, prop) {
          if (prop === "proxyOf") return node;
          else if (prop === "root") return () => node.root().toProxy();
          else return node[prop];
        },
        set(node, prop, value) {
          if (node[prop] === value) return true;
          node[prop] = value;
          if (prop === "prop" || prop === "value" || prop === "name" || prop === "params" || prop === "important" || /* c8 ignore next */
          prop === "text") node.markDirty();
          return true;
        }
      };
    }
    /* c8 ignore next 3 */
    markClean() {
      this[isClean] = true;
    }
    markDirty() {
      if (this[isClean]) {
        this[isClean] = false;
        let next = this;
        while (next = next.parent) next[isClean] = false;
      }
    }
    next() {
      if (!this.parent) return void 0;
      let index = this.parent.index(this);
      return this.parent.nodes[index + 1];
    }
    positionBy(opts = {}) {
      let inputString = "document" in this.source.input ? this.source.input.document : this.source.input.css;
      let pos = {
        column: this.source.start.column,
        line: this.source.start.line,
        offset: sourceOffset(inputString, this.source.start)
      };
      if (opts.index) pos = this.positionInside(opts.index);
      else if (opts.word) {
        let index = inputString.slice(sourceOffset(inputString, this.source.start), this.rangeBy().end.offset).indexOf(opts.word);
        if (index !== -1) pos = this.positionInside(index);
      }
      return pos;
    }
    positionInside(index) {
      let column = this.source.start.column;
      let line = this.source.start.line;
      let inputString = "document" in this.source.input ? this.source.input.document : this.source.input.css;
      let offset = sourceOffset(inputString, this.source.start);
      let end = offset + index;
      for (let i2 = offset; i2 < end; i2++) if (inputString[i2] === "\n") {
        column = 1;
        line += 1;
      } else column += 1;
      return {
        column,
        line,
        offset: end
      };
    }
    prev() {
      if (!this.parent) return void 0;
      let index = this.parent.index(this);
      return this.parent.nodes[index - 1];
    }
    rangeBy(opts = {}) {
      let inputString = "document" in this.source.input ? this.source.input.document : this.source.input.css;
      let start = {
        column: this.source.start.column,
        line: this.source.start.line,
        offset: sourceOffset(inputString, this.source.start)
      };
      let end = this.source.end ? {
        column: this.source.end.column + 1,
        line: this.source.end.line,
        offset: typeof this.source.end.offset === "number" ? this.source.end.offset : sourceOffset(inputString, this.source.end) + 1
      } : {
        column: start.column + 1,
        line: start.line,
        offset: start.offset + 1
      };
      if (opts.word) {
        let index = inputString.slice(sourceOffset(inputString, this.source.start), end.offset).indexOf(opts.word);
        if (index !== -1) {
          start = this.positionInside(index);
          end = this.positionInside(index + opts.word.length);
        }
      } else {
        if (opts.start) start = {
          column: opts.start.column,
          line: opts.start.line,
          offset: sourceOffset(inputString, opts.start)
        };
        else if (typeof opts.index === "number") start = this.positionInside(opts.index);
        if (opts.end) end = {
          column: opts.end.column,
          line: opts.end.line,
          offset: sourceOffset(inputString, opts.end)
        };
        else if (typeof opts.endIndex === "number") end = this.positionInside(opts.endIndex);
        else if (typeof opts.index === "number") end = this.positionInside(opts.index + 1);
      }
      if (end.line < start.line || end.line === start.line && end.column <= start.column) end = {
        column: start.column + 1,
        line: start.line,
        offset: start.offset + 1
      };
      return {
        end,
        start
      };
    }
    raw(prop, defaultType) {
      return new Stringifier().raw(this, prop, defaultType);
    }
    remove() {
      if (this.parent) this.parent.removeChild(this);
      this.parent = void 0;
      return this;
    }
    replaceWith(...nodes) {
      if (this.parent) {
        let bookmark = this;
        let foundSelf = false;
        for (let node of nodes) if (node === this) foundSelf = true;
        else if (foundSelf) {
          this.parent.insertAfter(bookmark, node);
          bookmark = node;
        } else this.parent.insertBefore(bookmark, node);
        if (!foundSelf) this.remove();
      }
      return this;
    }
    root() {
      let result = this;
      while (result.parent && result.parent.type !== "document") result = result.parent;
      return result;
    }
    toJSON(_, inputs) {
      let emitInputs = inputs == null;
      inputs = inputs || /* @__PURE__ */ new Map();
      let holderOfRoot = [];
      let queue = [[
        this,
        holderOfRoot,
        0
      ]];
      for (let step = 0; step < queue.length; step++) {
        let [node, holder, key] = queue[step];
        let fixed2 = {};
        holder[key] = fixed2;
        for (let name in node) {
          if (!Object.prototype.hasOwnProperty.call(node, name))
            continue;
          if (name === "parent" || name === "proxyCache") continue;
          let value = node[name];
          if (Array.isArray(value)) {
            let fixedArray = [];
            fixed2[name] = fixedArray;
            for (let i2 = 0; i2 < value.length; i2++) {
              let item = value[i2];
              if (typeof item === "object" && item.toJSON) {
                if (item.toJSON === Node3.prototype.toJSON) queue.push([
                  item,
                  fixedArray,
                  i2
                ]);
                else fixedArray[i2] = item.toJSON(null, inputs);
              } else fixedArray[i2] = item;
            }
          } else if (typeof value === "object" && value.toJSON) {
            if (value.toJSON === Node3.prototype.toJSON) queue.push([
              value,
              fixed2,
              name
            ]);
            else fixed2[name] = value.toJSON(null, inputs);
          } else if (name === "source") {
            if (value == null) continue;
            let inputId = inputs.get(value.input);
            if (inputId == null) {
              inputId = inputs.size;
              inputs.set(value.input, inputId);
            }
            fixed2[name] = {
              end: value.end,
              inputId,
              start: value.start
            };
          } else fixed2[name] = value;
        }
      }
      let fixed = holderOfRoot[0];
      if (emitInputs) fixed.inputs = [...inputs.keys()].map((input) => input.toJSON());
      return fixed;
    }
    toProxy() {
      if (!this.proxyCache) this.proxyCache = new Proxy(this, this.getProxyProcessor());
      return this.proxyCache;
    }
    toString(stringifier = stringify) {
      if (stringifier.stringify) stringifier = stringifier.stringify;
      let result = "";
      stringifier(this, (i2) => {
        result += i2;
      });
      return result;
    }
    warn(result, text, opts = {}) {
      let data = { node: this };
      for (let i2 in opts) data[i2] = opts[i2];
      return result.warn(text, data);
    }
  };
  module2.exports = Node2;
  Node2.default = Node2;
});
var require_comment$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Node2 = require_node$1();
  var Comment = class extends Node2 {
    constructor(defaults) {
      super(defaults);
      this.type = "comment";
    }
  };
  module2.exports = Comment;
  Comment.default = Comment;
});
var require_declaration$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Node2 = require_node$1();
  var Declaration = class extends Node2 {
    get variable() {
      return this.prop.startsWith("--") || this.prop[0] === "$";
    }
    constructor(defaults) {
      if (defaults && typeof defaults.value !== "undefined" && typeof defaults.value !== "string") defaults = __spreadProps(__spreadValues({}, defaults), {
        value: String(defaults.value)
      });
      super(defaults);
      this.type = "decl";
    }
  };
  module2.exports = Declaration;
  Declaration.default = Declaration;
});
var require_container$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Comment = require_comment$1();
  var Declaration = require_declaration$1();
  var Node2 = require_node$1();
  var { isClean, my } = require_symbols$1();
  var AtRule;
  var parse;
  var Root;
  var Rule;
  function cleanSource(nodes) {
    let stack = nodes.slice();
    while (stack.length > 0) {
      let node = stack.pop();
      delete node.source;
      if (node.nodes) {
        node.nodes = node.nodes.slice();
        for (let i2 of node.nodes) stack.push(i2);
      }
    }
    return nodes.slice();
  }
  function markTreeDirty(node) {
    let stack = [node];
    while (stack.length > 0) {
      let next = stack.pop();
      next[isClean] = false;
      if (next.proxyOf.nodes) for (let i2 of next.proxyOf.nodes) stack.push(i2);
    }
  }
  var Container = class Container2 extends Node2 {
    get first() {
      if (!this.proxyOf.nodes) return void 0;
      return this.proxyOf.nodes[0];
    }
    get last() {
      if (!this.proxyOf.nodes) return void 0;
      return this.proxyOf.nodes[this.proxyOf.nodes.length - 1];
    }
    append(...children) {
      for (let child of children) {
        let nodes = this.normalize(child, this.last);
        for (let node of nodes) this.proxyOf.nodes.push(node);
      }
      this.markDirty();
      return this;
    }
    cleanRaws(keepBetween) {
      let stack = [this];
      while (stack.length > 0) {
        let node = stack.pop();
        if (node !== this && node.cleanRaws !== Container2.prototype.cleanRaws) {
          node.cleanRaws(keepBetween);
          continue;
        }
        Node2.prototype.cleanRaws.call(node, keepBetween);
        if (node.nodes) for (let child of node.nodes) stack.push(child);
      }
    }
    each(callback) {
      if (!this.proxyOf.nodes) return void 0;
      let iterator = this.getIterator();
      let index, result;
      while (this.indexes[iterator] < this.proxyOf.nodes.length) {
        index = this.indexes[iterator];
        result = callback(this.proxyOf.nodes[index], index);
        if (result === false) break;
        this.indexes[iterator] += 1;
      }
      delete this.indexes[iterator];
      return result;
    }
    every(condition) {
      return this.nodes.every(condition);
    }
    getIterator() {
      if (!this.lastEach) this.lastEach = 0;
      if (!this.indexes) this.indexes = {};
      this.lastEach += 1;
      let iterator = this.lastEach;
      this.indexes[iterator] = 0;
      return iterator;
    }
    getProxyProcessor() {
      return {
        get(node, prop) {
          if (prop === "proxyOf") return node;
          else if (!node[prop]) return node[prop];
          else if (prop === "each" || typeof prop === "string" && prop.startsWith("walk")) return (...args) => {
            return node[prop](...args.map((i2) => {
              if (typeof i2 === "function") return (child, index) => i2(child.toProxy(), index);
              else return i2;
            }));
          };
          else if (prop === "every" || prop === "some") return (cb) => {
            return node[prop]((child, ...other) => cb(child.toProxy(), ...other));
          };
          else if (prop === "root") return () => node.root().toProxy();
          else if (prop === "nodes") return node.nodes.map((i2) => i2.toProxy());
          else if (prop === "first" || prop === "last") return node[prop].toProxy();
          else return node[prop];
        },
        set(node, prop, value) {
          if (node[prop] === value) return true;
          node[prop] = value;
          if (prop === "name" || prop === "params" || prop === "selector") node.markDirty();
          return true;
        }
      };
    }
    index(child) {
      if (typeof child === "number") return child;
      if (child.proxyOf) child = child.proxyOf;
      return this.proxyOf.nodes.indexOf(child);
    }
    insertAfter(exist, add) {
      let existIndex = this.index(exist);
      let nodes = this.normalize(add, this.proxyOf.nodes[existIndex]).reverse();
      existIndex = this.index(exist);
      for (let node of nodes) this.proxyOf.nodes.splice(existIndex + 1, 0, node);
      let index;
      for (let id in this.indexes) {
        index = this.indexes[id];
        if (existIndex < index) this.indexes[id] = index + nodes.length;
      }
      this.markDirty();
      return this;
    }
    insertBefore(exist, add) {
      let existIndex = this.index(exist);
      let type = existIndex === 0 ? "prepend" : false;
      let nodes = this.normalize(add, this.proxyOf.nodes[existIndex], type).reverse();
      existIndex = this.index(exist);
      for (let node of nodes) this.proxyOf.nodes.splice(existIndex, 0, node);
      let index;
      for (let id in this.indexes) {
        index = this.indexes[id];
        if (existIndex <= index) this.indexes[id] = index + nodes.length;
      }
      this.markDirty();
      return this;
    }
    normalize(nodes, sample) {
      if (typeof nodes === "string") nodes = cleanSource(parse(nodes).nodes);
      else if (typeof nodes === "undefined") nodes = [];
      else if (Array.isArray(nodes)) {
        nodes = nodes.slice(0);
        for (let i2 of nodes) if (i2.parent) i2.parent.removeChild(i2, "ignore");
      } else if (nodes.type === "root" && this.type !== "document") {
        nodes = nodes.nodes.slice(0);
        for (let i2 of nodes) if (i2.parent) i2.parent.removeChild(i2, "ignore");
      } else if (nodes.type) nodes = [nodes];
      else if (nodes.prop) {
        if (typeof nodes.value === "undefined") throw new Error("Value field is missed in node creation");
        else if (typeof nodes.value !== "string") nodes.value = String(nodes.value);
        nodes = [new Declaration(nodes)];
      } else if (nodes.selector || nodes.selectors) nodes = [new Rule(nodes)];
      else if (nodes.name) nodes = [new AtRule(nodes)];
      else if (nodes.text) nodes = [new Comment(nodes)];
      else throw new Error("Unknown node type in node creation");
      return nodes.map((i2) => {
        if (!i2[my]) Container2.rebuild(i2);
        i2 = i2.proxyOf;
        if (i2.parent) i2.parent.removeChild(i2);
        if (i2[isClean]) markTreeDirty(i2);
        if (!i2.raws) i2.raws = {};
        if (typeof i2.raws.before === "undefined") {
          if (sample && typeof sample.raws.before !== "undefined") i2.raws.before = sample.raws.before.replace(/\S/g, "");
        }
        i2.parent = this.proxyOf;
        return i2;
      });
    }
    prepend(...children) {
      children = children.reverse();
      for (let child of children) {
        let nodes = this.normalize(child, this.first, "prepend").reverse();
        for (let node of nodes) this.proxyOf.nodes.unshift(node);
        for (let id in this.indexes) this.indexes[id] = this.indexes[id] + nodes.length;
      }
      this.markDirty();
      return this;
    }
    push(child) {
      child.parent = this;
      this.proxyOf.nodes.push(child);
      return this;
    }
    removeAll() {
      for (let node of this.proxyOf.nodes) node.parent = void 0;
      this.proxyOf.nodes = [];
      this.markDirty();
      return this;
    }
    removeChild(child) {
      child = this.index(child);
      this.proxyOf.nodes[child].parent = void 0;
      this.proxyOf.nodes.splice(child, 1);
      let index;
      for (let id in this.indexes) {
        index = this.indexes[id];
        if (index >= child) this.indexes[id] = index - 1;
      }
      this.markDirty();
      return this;
    }
    replaceValues(pattern, opts, callback) {
      if (!callback) {
        callback = opts;
        opts = {};
      }
      this.walkDecls((decl) => {
        if (opts.props && !opts.props.includes(decl.prop)) return;
        if (opts.fast && !decl.value.includes(opts.fast)) return;
        decl.value = decl.value.replace(pattern, callback);
      });
      this.markDirty();
      return this;
    }
    some(condition) {
      return this.nodes.some(condition);
    }
    walk(callback) {
      if (!this.proxyOf.nodes) return void 0;
      let stack = [{
        iterator: this.getIterator(),
        node: this.proxyOf
      }];
      while (stack.length > 0) {
        let { iterator, node } = stack[stack.length - 1];
        let index = node.indexes[iterator];
        if (index >= node.proxyOf.nodes.length) {
          delete node.indexes[iterator];
          stack.pop();
          let parent = stack[stack.length - 1];
          if (parent) parent.node.indexes[parent.iterator] += 1;
          continue;
        }
        let child = node.proxyOf.nodes[index];
        let result;
        try {
          result = callback(child, index);
        } catch (e2) {
          throw child.addToError(e2);
        }
        if (result === false) {
          for (let opened of stack) delete opened.node.indexes[opened.iterator];
          return false;
        }
        if (child.walk && child.proxyOf.nodes) stack.push({
          iterator: child.getIterator(),
          node: child
        });
        else node.indexes[iterator] += 1;
      }
    }
    walkAtRules(name, callback) {
      if (!callback) {
        callback = name;
        return this.walk((child, i2) => {
          if (child.type === "atrule") return callback(child, i2);
        });
      }
      if (name instanceof RegExp) return this.walk((child, i2) => {
        if (child.type === "atrule" && name.test(child.name)) return callback(child, i2);
      });
      return this.walk((child, i2) => {
        if (child.type === "atrule" && child.name === name) return callback(child, i2);
      });
    }
    walkComments(callback) {
      return this.walk((child, i2) => {
        if (child.type === "comment") return callback(child, i2);
      });
    }
    walkDecls(prop, callback) {
      if (!callback) {
        callback = prop;
        return this.walk((child, i2) => {
          if (child.type === "decl") return callback(child, i2);
        });
      }
      if (prop instanceof RegExp) return this.walk((child, i2) => {
        if (child.type === "decl" && prop.test(child.prop)) return callback(child, i2);
      });
      return this.walk((child, i2) => {
        if (child.type === "decl" && child.prop === prop) return callback(child, i2);
      });
    }
    walkRules(selector, callback) {
      if (!callback) {
        callback = selector;
        return this.walk((child, i2) => {
          if (child.type === "rule") return callback(child, i2);
        });
      }
      if (selector instanceof RegExp) return this.walk((child, i2) => {
        if (child.type === "rule" && selector.test(child.selector)) return callback(child, i2);
      });
      return this.walk((child, i2) => {
        if (child.type === "rule" && child.selector === selector) return callback(child, i2);
      });
    }
  };
  Container.registerParse = (dependant) => {
    parse = dependant;
  };
  Container.registerRule = (dependant) => {
    Rule = dependant;
  };
  Container.registerAtRule = (dependant) => {
    AtRule = dependant;
  };
  Container.registerRoot = (dependant) => {
    Root = dependant;
  };
  module2.exports = Container;
  Container.default = Container;
  Container.rebuild = (node) => {
    let stack = [node];
    while (stack.length > 0) {
      let next = stack.pop();
      if (next.type === "atrule") Object.setPrototypeOf(next, AtRule.prototype);
      else if (next.type === "rule") Object.setPrototypeOf(next, Rule.prototype);
      else if (next.type === "decl") Object.setPrototypeOf(next, Declaration.prototype);
      else if (next.type === "comment") Object.setPrototypeOf(next, Comment.prototype);
      else if (next.type === "root") Object.setPrototypeOf(next, Root.prototype);
      next[my] = true;
      if (next.nodes) for (let child of next.nodes) stack.push(child);
    }
  };
});
var require_at_rule$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Container = require_container$1();
  var AtRule = class extends Container {
    constructor(defaults) {
      super(defaults);
      this.type = "atrule";
    }
    append(...children) {
      if (!this.proxyOf.nodes) this.nodes = [];
      return super.append(...children);
    }
    prepend(...children) {
      if (!this.proxyOf.nodes) this.nodes = [];
      return super.prepend(...children);
    }
  };
  module2.exports = AtRule;
  AtRule.default = AtRule;
  Container.registerAtRule(AtRule);
});
var require_document$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Container = require_container$1();
  var LazyResult;
  var Processor;
  var Document2 = class extends Container {
    constructor(defaults) {
      super(__spreadValues({
        type: "document"
      }, defaults));
      if (!this.nodes) this.nodes = [];
    }
    toResult(opts = {}) {
      return new LazyResult(new Processor(), this, opts).stringify();
    }
  };
  Document2.registerLazyResult = (dependant) => {
    LazyResult = dependant;
  };
  Document2.registerProcessor = (dependant) => {
    Processor = dependant;
  };
  module2.exports = Document2;
  Document2.default = Document2;
});
var require_non_secure$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var urlAlphabet = "useandom-26T198340PX75pxJACKVERYMINDBUSHWOLF_GQZbfghjklqvwyzrict";
  var customAlphabet = (alphabet, defaultSize = 21) => {
    return (size = defaultSize) => {
      let id = "";
      let i2 = size | 0;
      while (i2-- > 0) id += alphabet[Math.random() * alphabet.length | 0];
      return id;
    };
  };
  var nanoid = (size = 21) => {
    let id = "";
    let i2 = size | 0;
    while (i2-- > 0) id += urlAlphabet[Math.random() * 64 | 0];
    return id;
  };
  module2.exports = {
    nanoid,
    customAlphabet
  };
});
var require_previous_map$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var { existsSync, readFileSync, realpathSync } = require___vite_browser_external$1();
  var { dirname, isAbsolute, join, relative, sep } = require___vite_browser_external$1();
  var { SourceMapConsumer, SourceMapGenerator } = require___vite_browser_external$1();
  function realPath(path) {
    try {
      return realpathSync(path);
    } catch (e2) {
      return path;
    }
  }
  function fromBase64(str) {
    if (Buffer) return Buffer.from(str, "base64").toString();
    else
      return window.atob(str);
  }
  var PreviousMap = class {
    constructor(css, opts) {
      if (opts.map === false) return;
      if (opts.unsafeMap) this.unsafeMap = true;
      this.loadAnnotation(css);
      this.inline = this.startWith(this.annotation, "data:");
      let prev = opts.map ? opts.map.prev : void 0;
      let text = this.loadMap(opts.from, prev);
      if (!this.mapFile && opts.from) this.mapFile = opts.from;
      if (this.mapFile) this.root = dirname(this.mapFile);
      if (text) this.text = text;
    }
    consumer() {
      if (!this.consumerCache) this.consumerCache = new SourceMapConsumer(this.json || this.text);
      return this.consumerCache;
    }
    decodeInline(text) {
      let baseCharsetUri = /^data:application\/json;charset=utf-?8;base64,/;
      let baseUri = /^data:application\/json;base64,/;
      let uriMatch = text.match(/^data:application\/json;charset=utf-?8,/) || text.match(/^data:application\/json,/);
      if (uriMatch) return decodeURIComponent(text.substr(uriMatch[0].length));
      let baseUriMatch = text.match(baseCharsetUri) || text.match(baseUri);
      if (baseUriMatch) return fromBase64(text.substr(baseUriMatch[0].length));
      let encoding = text.slice(22);
      encoding = encoding.slice(0, encoding.indexOf(","));
      throw new Error("Unsupported source map encoding " + encoding);
    }
    getAnnotationURL(sourceMapString) {
      return sourceMapString.replace(/^\/\*\s*# sourceMappingURL=/, "").trim();
    }
    isMap(map) {
      if (typeof map !== "object") return false;
      return typeof map.mappings === "string" || typeof map._mappings === "string" || Array.isArray(map.sections);
    }
    loadAnnotation(css) {
      let comments = css.match(/\/\*\s*# sourceMappingURL=/g);
      if (!comments) return;
      let start = css.lastIndexOf(comments.pop());
      let end = css.indexOf("*/", start);
      if (start > -1 && end > -1) this.annotation = this.getAnnotationURL(css.substring(start, end));
    }
    loadFile(path, cssFile, trusted) {
      if (!trusted && !this.unsafeMap) {
        if (!/\.map$/i.test(path)) return void 0;
        if (!cssFile) return void 0;
        let rel = relative(realPath(dirname(cssFile)), realPath(path));
        if (rel === ".." || rel.startsWith(".." + sep) || isAbsolute(rel)) return;
      }
      this.root = dirname(path);
      if (existsSync(path)) {
        this.mapFile = path;
        return readFileSync(path, "utf-8").toString().trim();
      }
    }
    loadMap(file, prev) {
      if (prev === false) return false;
      if (prev) {
        if (typeof prev === "string") return prev;
        else if (typeof prev === "function") {
          let prevPath = prev(file);
          if (prevPath) {
            let map = this.loadFile(prevPath, file, true);
            if (!map) throw new Error("Unable to load previous source map: " + prevPath.toString());
            return map;
          }
        } else if (prev instanceof SourceMapConsumer) return SourceMapGenerator.fromSourceMap(prev).toString();
        else if (prev instanceof SourceMapGenerator) return prev.toString();
        else if (this.isMap(prev)) return JSON.stringify(prev);
        else throw new Error("Unsupported previous source map format: " + prev.toString());
      } else if (this.inline) return this.decodeInline(this.annotation);
      else if (this.annotation) {
        let map = this.annotation;
        if (file) map = join(dirname(file), map);
        let unknown = this.loadFile(map, file, false);
        if (unknown) try {
          this.json = JSON.parse(unknown.replace(/^\)]}'[^\n]*\n/, ""));
        } catch (e2) {
          return;
        }
        return unknown;
      }
    }
    startWith(string, start) {
      if (!string) return false;
      return string.substr(0, start.length) === start;
    }
    withContent() {
      return !!(this.consumer().sourcesContent && this.consumer().sourcesContent.length > 0);
    }
  };
  module2.exports = PreviousMap;
  PreviousMap.default = PreviousMap;
});
var require_input$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var { nanoid } = require_non_secure$1();
  var { isAbsolute, resolve } = require___vite_browser_external$1();
  var { SourceMapConsumer, SourceMapGenerator } = require___vite_browser_external$1();
  var { fileURLToPath, pathToFileURL } = require___vite_browser_external$1();
  var CssSyntaxError = require_css_syntax_error$1();
  var PreviousMap = require_previous_map$1();
  var terminalHighlight = require___vite_browser_external$1();
  var lineToIndexCache = Symbol("lineToIndexCache");
  var sourceMapAvailable = Boolean(SourceMapConsumer && SourceMapGenerator);
  var pathAvailable = Boolean(resolve && isAbsolute);
  function getLineToIndex(input) {
    if (input[lineToIndexCache]) return input[lineToIndexCache];
    let lines = input.css.split("\n");
    let lineToIndex = new Array(lines.length);
    let prevIndex = 0;
    for (let i2 = 0, l2 = lines.length; i2 < l2; i2++) {
      lineToIndex[i2] = prevIndex;
      prevIndex += lines[i2].length + 1;
    }
    input[lineToIndexCache] = lineToIndex;
    return lineToIndex;
  }
  var Input = class {
    get from() {
      return this.file || this.id;
    }
    constructor(css, opts = {}) {
      if (css === null || typeof css === "undefined" || typeof css === "object" && !css.toString) throw new Error(`PostCSS received ${css} instead of CSS string`);
      this.css = css.toString();
      if (this.css[0] === "\uFEFF" || this.css[0] === "\uFFFE") {
        this.hasBOM = true;
        this.css = this.css.slice(1);
      } else this.hasBOM = false;
      this.document = this.css;
      if (opts.document) this.document = opts.document.toString();
      if (opts.from) {
        if (!pathAvailable || /^\w+:\/\//.test(opts.from) || isAbsolute(opts.from)) this.file = opts.from;
        else this.file = resolve(opts.from);
      }
      if (pathAvailable && sourceMapAvailable) {
        let map = new PreviousMap(this.css, opts);
        if (map.text) {
          this.map = map;
          let file = map.consumer().file;
          if (!this.file && file) this.file = this.mapResolve(file);
        }
      }
      if (!this.file) this.id = "<input css " + nanoid(6) + ">";
      if (this.map) this.map.file = this.from;
    }
    error(message, line, column, opts = {}) {
      let endColumn, endLine, endOffset, offset, result;
      if (line && typeof line === "object") {
        let start = line;
        let end = column;
        if (typeof start.offset === "number") {
          offset = start.offset;
          let pos = this.fromOffset(offset);
          line = pos.line;
          column = pos.col;
        } else {
          line = start.line;
          column = start.column;
          offset = this.fromLineAndColumn(line, column);
        }
        if (typeof end.offset === "number") {
          endOffset = end.offset;
          let pos = this.fromOffset(endOffset);
          endLine = pos.line;
          endColumn = pos.col;
        } else {
          endLine = end.line;
          endColumn = end.column;
          endOffset = this.fromLineAndColumn(end.line, end.column);
        }
      } else if (!column) {
        offset = line;
        let pos = this.fromOffset(offset);
        line = pos.line;
        column = pos.col;
      } else offset = this.fromLineAndColumn(line, column);
      let origin = this.origin(line, column, endLine, endColumn);
      if (origin) result = new CssSyntaxError(message, origin.endLine === void 0 ? origin.line : {
        column: origin.column,
        line: origin.line
      }, origin.endLine === void 0 ? origin.column : {
        column: origin.endColumn,
        line: origin.endLine
      }, origin.source, origin.file, opts.plugin);
      else result = new CssSyntaxError(message, endLine === void 0 ? line : {
        column,
        line
      }, endLine === void 0 ? column : {
        column: endColumn,
        line: endLine
      }, this.css, this.file, opts.plugin);
      result.input = {
        column,
        endColumn,
        endLine,
        endOffset,
        line,
        offset,
        source: this.css
      };
      if (this.file) {
        if (pathToFileURL) result.input.url = pathToFileURL(this.file).toString();
        result.input.file = this.file;
      }
      return result;
    }
    fromLineAndColumn(line, column) {
      return getLineToIndex(this)[line - 1] + column - 1;
    }
    fromOffset(offset) {
      let lineToIndex = getLineToIndex(this);
      let lastLine = lineToIndex[lineToIndex.length - 1];
      let min = 0;
      if (offset >= lastLine) min = lineToIndex.length - 1;
      else {
        let max = lineToIndex.length - 2;
        let mid;
        while (min < max) {
          mid = min + (max - min >> 1);
          if (offset < lineToIndex[mid]) max = mid - 1;
          else if (offset >= lineToIndex[mid + 1]) min = mid + 1;
          else {
            min = mid;
            break;
          }
        }
      }
      return {
        col: offset - lineToIndex[min] + 1,
        line: min + 1
      };
    }
    mapResolve(file) {
      if (/^\w+:\/\//.test(file)) return file;
      return resolve(this.map.consumer().sourceRoot || this.map.root || ".", file);
    }
    origin(line, column, endLine, endColumn) {
      if (!this.map) return false;
      let consumer = this.map.consumer();
      let from = consumer.originalPositionFor({
        column: column - 1,
        line
      });
      if (!from.source) return false;
      let to;
      if (typeof endLine === "number") {
        let toPosition = consumer.originalPositionFor({
          column: endColumn - 1,
          line: endLine
        });
        if (toPosition.source) to = toPosition;
      }
      let fromUrl;
      if (isAbsolute(from.source)) fromUrl = pathToFileURL(from.source);
      else fromUrl = new URL(from.source, pathToFileURL(this.map.mapFile || resolve(".") + "/"));
      let result = {
        column: from.column + 1,
        endColumn: to && to.column + 1,
        endLine: to && to.line,
        line: from.line,
        url: fromUrl.toString()
      };
      if (fromUrl.protocol === "file:") {
        if (fileURLToPath) result.file = fileURLToPath(fromUrl);
        else
          throw new Error(`file: protocol is not available in this PostCSS build`);
      }
      let source = consumer.sourceContentFor(from.source);
      if (source) result.source = source;
      return result;
    }
    toJSON() {
      let json = {};
      for (let name of [
        "hasBOM",
        "css",
        "file",
        "id"
      ]) if (this[name] != null) json[name] = this[name];
      if (this.map) {
        json.map = __spreadValues({}, this.map);
        if (json.map.consumerCache) json.map.consumerCache = void 0;
      }
      return json;
    }
  };
  module2.exports = Input;
  Input.default = Input;
  if (terminalHighlight && terminalHighlight.registerInput) terminalHighlight.registerInput(Input);
});
var require_root$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Container = require_container$1();
  var LazyResult;
  var Processor;
  var Root = class extends Container {
    constructor(defaults) {
      super(defaults);
      this.type = "root";
      if (!this.nodes) this.nodes = [];
    }
    normalize(child, sample, type) {
      let keepBefore = /* @__PURE__ */ new Set();
      for (let node of Array.isArray(child) ? child : [child]) if (node && typeof node === "object" && !node.parent && node.raws && typeof node.raws.before !== "undefined") keepBefore.add(node.raws);
      let nodes = super.normalize(child);
      if (sample) {
        if (type === "prepend") {
          if (this.nodes.length > 1) sample.raws.before = this.nodes[1].raws.before;
          else delete sample.raws.before;
        } else if (this.first !== sample) {
          for (let node of nodes) if (!keepBefore.has(node.raws)) node.raws.before = sample.raws.before;
        }
      }
      return nodes;
    }
    removeChild(child, ignore) {
      let index = this.index(child);
      if (!ignore && index === 0 && this.nodes.length > 1) this.nodes[1].raws.before = this.nodes[index].raws.before;
      return super.removeChild(child);
    }
    toResult(opts = {}) {
      return new LazyResult(new Processor(), this, opts).stringify();
    }
  };
  Root.registerLazyResult = (dependant) => {
    LazyResult = dependant;
  };
  Root.registerProcessor = (dependant) => {
    Processor = dependant;
  };
  module2.exports = Root;
  Root.default = Root;
  Container.registerRoot(Root);
});
var require_list$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var list = {
    comma(string) {
      return list.split(string, [","], true);
    },
    space(string) {
      return list.split(string, [
        " ",
        "\n",
        "	"
      ]);
    },
    split(string, separators, last) {
      if (typeof string !== "string") return [];
      let array = [];
      let current = "";
      let split = false;
      let func = 0;
      let inQuote = false;
      let prevQuote = "";
      let escape = false;
      let inComment = false;
      for (let i2 = 0; i2 < string.length; i2++) {
        let letter = string[i2];
        if (inComment) {
          current += letter;
          if (letter === "*" && string[i2 + 1] === "/") {
            current += "/";
            i2 += 1;
            inComment = false;
          }
          continue;
        }
        if (escape) escape = false;
        else if (letter === "\\") escape = true;
        else if (inQuote) {
          if (letter === prevQuote) inQuote = false;
        } else if (letter === '"' || letter === "'") {
          inQuote = true;
          prevQuote = letter;
        } else if (letter === "/" && string[i2 + 1] === "*") {
          current += "/*";
          i2 += 1;
          inComment = true;
          continue;
        } else if (letter === "(") func += 1;
        else if (letter === ")") {
          if (func > 0) func -= 1;
        } else if (func === 0) {
          if (separators.includes(letter)) split = true;
        }
        if (split) {
          let value2 = current.trim();
          if (last || value2 !== "") array.push(value2);
          current = "";
          split = false;
        } else current += letter;
      }
      let value = current.trim();
      if (last || value !== "") array.push(value);
      return array;
    }
  };
  module2.exports = list;
  list.default = list;
});
var require_rule$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Container = require_container$1();
  var list = require_list$1();
  var Rule = class extends Container {
    get selectors() {
      return list.comma(this.selector);
    }
    set selectors(values) {
      let match = this.selector ? this.selector.match(/,\s*/) : null;
      let sep = match ? match[0] : "," + this.raw("between", "beforeOpen");
      this.selector = values.join(sep);
    }
    constructor(defaults) {
      super(defaults);
      this.type = "rule";
      if (!this.nodes) this.nodes = [];
    }
  };
  module2.exports = Rule;
  Rule.default = Rule;
  Container.registerRule(Rule);
});
var require_fromJSON$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var AtRule = require_at_rule$1();
  var Comment = require_comment$1();
  var Declaration = require_declaration$1();
  var Input = require_input$1();
  var PreviousMap = require_previous_map$1();
  var Root = require_root$1();
  var Rule = require_rule$1();
  function hydrateInputs(json, inputs) {
    if (!json.inputs) return inputs;
    return json.inputs.map((input) => {
      let inputHydrated = __spreadProps(__spreadValues({}, input), {
        __proto__: Input.prototype
      });
      if (inputHydrated.map) inputHydrated.map = __spreadProps(__spreadValues({}, inputHydrated.map), {
        __proto__: PreviousMap.prototype
      });
      return inputHydrated;
    });
  }
  function constructNode(json, inputs, children) {
    let defaults = __spreadValues({}, json);
    delete defaults.inputs;
    delete defaults.nodes;
    if (defaults.source) {
      let _a2 = defaults.source, { inputId } = _a2, source = __objRest(_a2, ["inputId"]);
      defaults.source = source;
      if (inputId != null) defaults.source.input = inputs[inputId];
    }
    let node;
    if (defaults.type === "root") node = new Root(defaults);
    else if (defaults.type === "decl") node = new Declaration(defaults);
    else if (defaults.type === "rule") node = new Rule(defaults);
    else if (defaults.type === "comment") node = new Comment(defaults);
    else if (defaults.type === "atrule") node = new AtRule(defaults);
    else throw new Error("Unknown node type: " + json.type);
    if (children) {
      node.nodes = children;
      for (let child of children) child.parent = node;
    }
    return node;
  }
  function fromJSON(json, inputs) {
    if (Array.isArray(json)) return json.map((n2) => fromJSON(n2));
    let result;
    let stack = [{
      childIndex: 0,
      children: [],
      inputs: hydrateInputs(json, inputs),
      json
    }];
    while (stack.length > 0) {
      let frame = stack[stack.length - 1];
      let jsonNodes = frame.json.nodes;
      if (jsonNodes && frame.childIndex < jsonNodes.length) {
        let childJson = jsonNodes[frame.childIndex];
        frame.childIndex += 1;
        stack.push({
          childIndex: 0,
          children: [],
          inputs: hydrateInputs(childJson, frame.inputs),
          json: childJson
        });
        continue;
      }
      stack.pop();
      let node = constructNode(frame.json, frame.inputs, jsonNodes ? frame.children : void 0);
      if (stack.length > 0) stack[stack.length - 1].children.push(node);
      else result = node;
    }
    return result;
  }
  module2.exports = fromJSON;
  fromJSON.default = fromJSON;
});
var require_map_generator$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var { dirname, relative, resolve, sep } = require___vite_browser_external$1();
  var { SourceMapConsumer, SourceMapGenerator } = require___vite_browser_external$1();
  var { pathToFileURL } = require___vite_browser_external$1();
  var Input = require_input$1();
  var sourceMapAvailable = Boolean(SourceMapConsumer && SourceMapGenerator);
  var pathAvailable = Boolean(dirname && resolve && relative && sep);
  var MapGenerator = class {
    constructor(stringify, root, opts, cssString) {
      this.stringify = stringify;
      this.mapOpts = opts.map || {};
      this.root = root;
      this.opts = opts;
      this.css = cssString;
      this.originalCSS = cssString;
      this.usesFileUrls = !this.mapOpts.from && this.mapOpts.absolute;
      this.memoizedFileURLs = /* @__PURE__ */ new Map();
      this.memoizedPaths = /* @__PURE__ */ new Map();
      this.memoizedURLs = /* @__PURE__ */ new Map();
    }
    addAnnotation() {
      let content;
      if (this.isInline()) content = "data:application/json;base64," + this.toBase64(this.map.toString());
      else if (typeof this.mapOpts.annotation === "string") content = this.mapOpts.annotation;
      else if (typeof this.mapOpts.annotation === "function") content = this.mapOpts.annotation(this.opts.to, this.root);
      else content = this.outputFile() + ".map";
      let eol = "\n";
      if (this.css.includes("\r\n")) eol = "\r\n";
      this.css += eol + "/*# sourceMappingURL=" + content + " */";
    }
    applyPrevMaps() {
      for (let prev of this.previous()) {
        let from = this.toUrl(this.path(prev.file));
        let root = prev.root || dirname(prev.file);
        let map;
        if (this.mapOpts.sourcesContent === false) {
          map = new SourceMapConsumer(prev.text);
          if (map.sourcesContent) map.sourcesContent = null;
        } else map = prev.consumer();
        this.map.applySourceMap(map, from, this.toUrl(this.path(root)));
      }
    }
    clearAnnotation() {
      if (this.mapOpts.annotation === false) return;
      if (this.root) {
        let node;
        for (let i2 = this.root.nodes.length - 1; i2 >= 0; i2--) {
          node = this.root.nodes[i2];
          if (node.type !== "comment") continue;
          if (node.text.startsWith("# sourceMappingURL=")) this.root.removeChild(i2);
        }
      } else if (this.css) {
        let annotation = "/*# sourceMappingURL=";
        let css = "";
        let cursor = 0;
        let startIndex;
        while ((startIndex = this.css.indexOf(annotation, cursor)) !== -1) {
          let endIndex = this.css.indexOf("*/", startIndex + 21);
          if (endIndex === -1) break;
          while (startIndex > cursor && this.css[startIndex - 1] === "\n") startIndex--;
          css += this.css.slice(cursor, startIndex);
          cursor = endIndex + 2;
        }
        if (cursor > 0) this.css = css + this.css.slice(cursor);
      }
    }
    generate() {
      this.clearAnnotation();
      if (pathAvailable && sourceMapAvailable && this.isMap()) return this.generateMap();
      else {
        let result = "";
        this.stringify(this.root, (i2) => {
          result += i2;
        });
        return [result];
      }
    }
    generateMap() {
      if (this.root) this.generateString();
      else if (this.previous().length === 1) {
        let prev = this.previous()[0].consumer();
        prev.file = this.outputFile();
        this.map = SourceMapGenerator.fromSourceMap(prev, { ignoreInvalidMapping: true });
      } else {
        this.map = new SourceMapGenerator({
          file: this.outputFile(),
          ignoreInvalidMapping: true
        });
        this.map.addMapping({
          generated: {
            column: 0,
            line: 1
          },
          original: {
            column: 0,
            line: 1
          },
          source: this.opts.from ? this.toUrl(this.path(this.opts.from)) : "<no source>"
        });
      }
      if (this.isSourcesContent()) this.setSourcesContent();
      if (this.root && this.previous().length > 0) this.applyPrevMaps();
      if (this.isAnnotation()) this.addAnnotation();
      if (this.isInline()) return [this.css];
      else return [this.css, this.map];
    }
    generateString() {
      this.css = "";
      this.map = new SourceMapGenerator({
        file: this.outputFile(),
        ignoreInvalidMapping: true
      });
      let line = 1;
      let column = 1;
      let noSource = "<no source>";
      let mapping = {
        generated: {
          column: 0,
          line: 0
        },
        original: {
          column: 0,
          line: 0
        },
        source: ""
      };
      let last, lines;
      this.stringify(this.root, (str, node, type) => {
        this.css += str;
        if (node && type !== "end") {
          mapping.generated.line = line;
          mapping.generated.column = column - 1;
          if (node.source && node.source.start) {
            mapping.source = this.sourcePath(node);
            mapping.original.line = node.source.start.line;
            mapping.original.column = node.source.start.column - 1;
            this.map.addMapping(mapping);
          } else {
            mapping.source = noSource;
            mapping.original.line = 1;
            mapping.original.column = 0;
            this.map.addMapping(mapping);
          }
        }
        lines = str.match(/\n/g);
        if (lines) {
          line += lines.length;
          last = str.lastIndexOf("\n");
          column = str.length - last;
        } else column += str.length;
        if (node && type !== "start") {
          let p = node.parent || { raws: {} };
          if (!(node.type === "decl" || node.type === "atrule" && !node.nodes) || node !== p.last || p.raws.semicolon) {
            if (node.source && node.source.end) {
              mapping.source = this.sourcePath(node);
              mapping.original.line = node.source.end.line;
              mapping.original.column = node.source.end.column - 1;
              mapping.generated.line = line;
              mapping.generated.column = column - 2;
              this.map.addMapping(mapping);
            } else {
              mapping.source = noSource;
              mapping.original.line = 1;
              mapping.original.column = 0;
              mapping.generated.line = line;
              mapping.generated.column = column - 1;
              this.map.addMapping(mapping);
            }
          }
        }
      });
    }
    isAnnotation() {
      if (this.isInline()) return true;
      if (typeof this.mapOpts.annotation !== "undefined") return this.mapOpts.annotation;
      if (this.previous().length) return this.previous().some((i2) => i2.annotation);
      return true;
    }
    isInline() {
      if (typeof this.mapOpts.inline !== "undefined") return this.mapOpts.inline;
      let annotation = this.mapOpts.annotation;
      if (typeof annotation !== "undefined" && annotation !== true) return false;
      if (this.previous().length) return this.previous().some((i2) => i2.inline);
      return true;
    }
    isMap() {
      if (typeof this.opts.map !== "undefined") return !!this.opts.map;
      return this.previous().length > 0;
    }
    isSourcesContent() {
      if (typeof this.mapOpts.sourcesContent !== "undefined") return this.mapOpts.sourcesContent;
      if (this.previous().length) return this.previous().some((i2) => i2.withContent());
      return true;
    }
    outputFile() {
      if (this.opts.to) return this.path(this.opts.to);
      else if (this.opts.from) return this.path(this.opts.from);
      else return "to.css";
    }
    path(file) {
      if (this.mapOpts.absolute) return file;
      if (file.charCodeAt(0) === 60) return file;
      if (/^\w+:\/\//.test(file)) return file;
      let cached = this.memoizedPaths.get(file);
      if (cached) return cached;
      let from = this.opts.to ? dirname(this.opts.to) : ".";
      if (typeof this.mapOpts.annotation === "string") from = dirname(resolve(from, this.mapOpts.annotation));
      let path = relative(from, file);
      this.memoizedPaths.set(file, path);
      return path;
    }
    previous() {
      if (!this.previousMaps) {
        this.previousMaps = [];
        if (this.root) this.root.walk((node) => {
          if (node.source && node.source.input.map) {
            let map = node.source.input.map;
            if (!this.previousMaps.includes(map)) this.previousMaps.push(map);
          }
        });
        else {
          let input = new Input(this.originalCSS, this.opts);
          if (input.map) this.previousMaps.push(input.map);
        }
      }
      return this.previousMaps;
    }
    setSourcesContent() {
      let already = {};
      if (this.root) this.root.walk((node) => {
        if (node.source) {
          let from = node.source.input.from;
          if (from && !already[from]) {
            already[from] = true;
            let fromUrl = this.usesFileUrls ? this.toFileUrl(from) : this.toUrl(this.path(from));
            this.map.setSourceContent(fromUrl, node.source.input.css);
          }
        }
      });
      else if (this.css) {
        let from = this.opts.from ? this.toUrl(this.path(this.opts.from)) : "<no source>";
        this.map.setSourceContent(from, this.css);
      }
    }
    sourcePath(node) {
      if (this.mapOpts.from) return this.toUrl(this.mapOpts.from);
      else if (this.usesFileUrls) return this.toFileUrl(node.source.input.from);
      else return this.toUrl(this.path(node.source.input.from));
    }
    toBase64(str) {
      if (Buffer) return Buffer.from(str).toString("base64");
      else return window.btoa(unescape(encodeURIComponent(str)));
    }
    toFileUrl(path) {
      let cached = this.memoizedFileURLs.get(path);
      if (cached) return cached;
      if (pathToFileURL) {
        let fileURL = pathToFileURL(path).toString();
        this.memoizedFileURLs.set(path, fileURL);
        return fileURL;
      } else throw new Error("`map.absolute` option is not available in this PostCSS build");
    }
    toUrl(path) {
      let cached = this.memoizedURLs.get(path);
      if (cached) return cached;
      if (sep === "\\") path = path.replace(/\\/g, "/");
      let url = encodeURI(path).replace(/[#?]/g, encodeURIComponent);
      this.memoizedURLs.set(path, url);
      return url;
    }
  };
  module2.exports = MapGenerator;
});
var require_tokenize$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var SINGLE_QUOTE = "'".charCodeAt(0);
  var DOUBLE_QUOTE = '"'.charCodeAt(0);
  var BACKSLASH = "\\".charCodeAt(0);
  var SLASH = "/".charCodeAt(0);
  var NEWLINE = "\n".charCodeAt(0);
  var SPACE = " ".charCodeAt(0);
  var FEED = "\f".charCodeAt(0);
  var TAB = "	".charCodeAt(0);
  var CR = "\r".charCodeAt(0);
  var OPEN_SQUARE = "[".charCodeAt(0);
  var CLOSE_SQUARE = "]".charCodeAt(0);
  var OPEN_PARENTHESES = "(".charCodeAt(0);
  var CLOSE_PARENTHESES = ")".charCodeAt(0);
  var OPEN_CURLY = "{".charCodeAt(0);
  var CLOSE_CURLY = "}".charCodeAt(0);
  var SEMICOLON = ";".charCodeAt(0);
  var ASTERISK = "*".charCodeAt(0);
  var COLON = ":".charCodeAt(0);
  var AT = "@".charCodeAt(0);
  var RE_AT_END = /[\t\n\f\r "#'()/;[\\\]{}]/g;
  var RE_WORD_END = /[\t\n\f\r !"#'():;@[\\\]{}]|\/(?=\*)/g;
  var RE_BAD_BRACKET = /.[\r\n"'(/\\]/;
  var RE_HEX_ESCAPE = /[\da-f]/i;
  module2.exports = function tokenizer(input, options = {}) {
    let css = input.css.valueOf();
    let ignore = options.ignoreErrors;
    let code, content, escape, next, quote;
    let currentToken, escaped, escapePos, n2, prev;
    let length = css.length;
    let pos = 0;
    let buffer = [];
    let returned = [];
    let lastBadParen = -1;
    function position() {
      return pos;
    }
    function unclosed(what) {
      throw input.error("Unclosed " + what, pos);
    }
    function endOfFile() {
      return returned.length === 0 && pos >= length;
    }
    function nextToken(opts) {
      if (returned.length) return returned.pop();
      if (pos >= length) return;
      let ignoreUnclosed = opts ? opts.ignoreUnclosed : false;
      code = css.charCodeAt(pos);
      switch (code) {
        case NEWLINE:
        case SPACE:
        case TAB:
        case CR:
        case FEED:
          next = pos;
          do {
            next += 1;
            code = css.charCodeAt(next);
          } while (code === SPACE || code === NEWLINE || code === TAB || code === CR || code === FEED);
          currentToken = ["space", css.slice(pos, next)];
          pos = next - 1;
          break;
        case OPEN_SQUARE:
        case CLOSE_SQUARE:
        case OPEN_CURLY:
        case CLOSE_CURLY:
        case COLON:
        case SEMICOLON:
        case CLOSE_PARENTHESES: {
          let controlChar = String.fromCharCode(code);
          currentToken = [
            controlChar,
            controlChar,
            pos
          ];
          break;
        }
        case OPEN_PARENTHESES:
          prev = buffer.length ? buffer.pop()[1] : "";
          n2 = css.charCodeAt(pos + 1);
          if (prev === "url" && n2 !== SINGLE_QUOTE && n2 !== DOUBLE_QUOTE && n2 !== SPACE && n2 !== NEWLINE && n2 !== TAB && n2 !== FEED && n2 !== CR) {
            next = pos;
            do {
              escaped = false;
              next = css.indexOf(")", next + 1);
              if (next === -1) {
                if (ignore || ignoreUnclosed) {
                  next = pos;
                  break;
                } else unclosed("bracket");
              }
              escapePos = next;
              while (css.charCodeAt(escapePos - 1) === BACKSLASH) {
                escapePos -= 1;
                escaped = !escaped;
              }
            } while (escaped);
            currentToken = [
              "brackets",
              css.slice(pos, next + 1),
              pos,
              next
            ];
            pos = next;
          } else if (pos <= lastBadParen) currentToken = [
            "(",
            "(",
            pos
          ];
          else {
            next = css.indexOf(")", pos + 1);
            content = css.slice(pos, next + 1);
            if (next === -1 || RE_BAD_BRACKET.test(content)) {
              lastBadParen = next === -1 ? length : next;
              currentToken = [
                "(",
                "(",
                pos
              ];
            } else {
              currentToken = [
                "brackets",
                content,
                pos,
                next
              ];
              pos = next;
            }
          }
          break;
        case SINGLE_QUOTE:
        case DOUBLE_QUOTE:
          quote = code === SINGLE_QUOTE ? "'" : '"';
          next = pos;
          do {
            escaped = false;
            next = css.indexOf(quote, next + 1);
            if (next === -1) {
              if (ignore || ignoreUnclosed) {
                next = pos + 1;
                break;
              } else unclosed("string");
            }
            escapePos = next;
            while (css.charCodeAt(escapePos - 1) === BACKSLASH) {
              escapePos -= 1;
              escaped = !escaped;
            }
          } while (escaped);
          currentToken = [
            "string",
            css.slice(pos, next + 1),
            pos,
            next
          ];
          pos = next;
          break;
        case AT:
          RE_AT_END.lastIndex = pos + 1;
          RE_AT_END.test(css);
          if (RE_AT_END.lastIndex === 0) next = css.length - 1;
          else next = RE_AT_END.lastIndex - 2;
          currentToken = [
            "at-word",
            css.slice(pos, next + 1),
            pos,
            next
          ];
          pos = next;
          break;
        case BACKSLASH:
          next = pos;
          escape = true;
          while (css.charCodeAt(next + 1) === BACKSLASH) {
            next += 1;
            escape = !escape;
          }
          code = css.charCodeAt(next + 1);
          if (escape && code !== SLASH && code !== SPACE && code !== NEWLINE && code !== TAB && code !== CR && code !== FEED) {
            next += 1;
            if (RE_HEX_ESCAPE.test(css.charAt(next))) {
              while (RE_HEX_ESCAPE.test(css.charAt(next + 1))) next += 1;
              if (css.charCodeAt(next + 1) === SPACE) next += 1;
            }
          }
          currentToken = [
            "word",
            css.slice(pos, next + 1),
            pos,
            next
          ];
          pos = next;
          break;
        default:
          if (code === SLASH && css.charCodeAt(pos + 1) === ASTERISK) {
            next = css.indexOf("*/", pos + 2) + 1;
            if (next === 0) {
              if (ignore || ignoreUnclosed) next = css.length;
              else unclosed("comment");
            }
            currentToken = [
              "comment",
              css.slice(pos, next + 1),
              pos,
              next
            ];
            pos = next;
          } else {
            RE_WORD_END.lastIndex = pos + 1;
            RE_WORD_END.test(css);
            if (RE_WORD_END.lastIndex === 0) next = css.length - 1;
            else next = RE_WORD_END.lastIndex - 2;
            currentToken = [
              "word",
              css.slice(pos, next + 1),
              pos,
              next
            ];
            buffer.push(currentToken);
            pos = next;
          }
      }
      pos++;
      return currentToken;
    }
    function back(token) {
      returned.push(token);
    }
    return {
      back,
      endOfFile,
      nextToken,
      position
    };
  };
});
var require_parser$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var AtRule = require_at_rule$1();
  var Comment = require_comment$1();
  var Declaration = require_declaration$1();
  var Root = require_root$1();
  var Rule = require_rule$1();
  var tokenizer = require_tokenize$1();
  var SAFE_COMMENT_NEIGHBOR = {
    empty: true,
    space: true
  };
  function findLastWithPosition(tokens) {
    for (let i2 = tokens.length - 1; i2 >= 0; i2--) {
      let token = tokens[i2];
      let pos = token[3] || token[2];
      if (pos) return pos;
    }
  }
  function tokensToString(tokens, from, to) {
    let result = "";
    for (let i2 = from; i2 < to; i2++) result += tokens[i2][1];
    return result;
  }
  var Parser = class {
    constructor(input) {
      this.input = input;
      this.root = new Root();
      this.current = this.root;
      this.spaces = "";
      this.semicolon = false;
      this.createTokenizer();
      this.root.source = {
        input,
        start: {
          column: 1,
          line: 1,
          offset: 0
        }
      };
    }
    atrule(token) {
      let node = new AtRule();
      node.name = token[1].slice(1);
      if (node.name === "") this.unnamedAtrule(node, token);
      this.init(node, token[2]);
      let type;
      let prev;
      let shift;
      let last = false;
      let open = false;
      let params = [];
      let brackets = [];
      while (!this.tokenizer.endOfFile()) {
        token = this.tokenizer.nextToken();
        type = token[0];
        if (type === "(" || type === "[") brackets.push(type === "(" ? ")" : "]");
        else if (type === "{" && brackets.length > 0) brackets.push("}");
        else if (type === brackets[brackets.length - 1]) brackets.pop();
        if (brackets.length === 0) {
          if (type === ";") {
            node.source.end = this.getPosition(token[2]);
            node.source.end.offset++;
            this.semicolon = true;
            break;
          } else if (type === "{") {
            open = true;
            break;
          } else if (type === "}") {
            if (params.length > 0) {
              shift = params.length - 1;
              prev = params[shift];
              while (prev && prev[0] === "space") prev = params[--shift];
              if (prev) {
                node.source.end = this.getPosition(prev[3] || prev[2]);
                node.source.end.offset++;
              }
            }
            this.end(token);
            break;
          } else params.push(token);
        } else params.push(token);
        if (this.tokenizer.endOfFile()) {
          last = true;
          break;
        }
      }
      node.raws.between = this.spacesAndCommentsFromEnd(params);
      if (params.length) {
        node.raws.afterName = this.spacesAndCommentsFromStart(params);
        this.raw(node, "params", params);
        if (last) {
          token = params[params.length - 1];
          node.source.end = this.getPosition(token[3] || token[2]);
          node.source.end.offset++;
          this.spaces = node.raws.between;
          node.raws.between = "";
        }
      } else {
        node.raws.afterName = "";
        node.params = "";
      }
      if (open) {
        node.nodes = [];
        this.current = node;
      }
    }
    checkMissedSemicolon(tokens) {
      let colon = this.colon(tokens);
      if (colon === false) return;
      let founded = 0;
      let token;
      for (let j = colon - 1; j >= 0; j--) {
        token = tokens[j];
        if (token[0] !== "space") {
          founded += 1;
          if (founded === 2) break;
        }
      }
      throw this.input.error("Missed semicolon", token[0] === "word" ? token[3] + 1 : token[2]);
    }
    colon(tokens) {
      let brackets = 0;
      let prev, token, type;
      for (let [i2, element] of tokens.entries()) {
        token = element;
        type = token[0];
        if (type === "(") brackets += 1;
        if (type === ")") brackets -= 1;
        if (brackets === 0 && type === ":") {
          if (!prev) this.doubleColon(token);
          else if (prev[0] === "word" && prev[1] === "progid") continue;
          else return i2;
        }
        prev = token;
      }
      return false;
    }
    comment(token) {
      let node = new Comment();
      this.init(node, token[2]);
      node.source.end = this.getPosition(token[3] || token[2]);
      node.source.end.offset++;
      let text = token[1].slice(2, -2);
      if (!text.trim()) {
        node.text = "";
        node.raws.left = text;
        node.raws.right = "";
      } else {
        let match = text.match(/^(\s*)([^]*\S)(\s*)$/);
        node.text = match[2];
        node.raws.left = match[1];
        node.raws.right = match[3];
      }
    }
    createTokenizer() {
      this.tokenizer = tokenizer(this.input);
    }
    decl(tokens, customProperty) {
      let node = new Declaration();
      this.init(node, tokens[0][2]);
      let last = tokens[tokens.length - 1];
      if (last[0] === ";") {
        this.semicolon = true;
        tokens.pop();
      }
      node.source.end = this.getPosition(last[3] || last[2] || findLastWithPosition(tokens));
      node.source.end.offset++;
      let start = 0;
      while (tokens[start][0] !== "word") {
        if (start === tokens.length - 1) this.unknownWord([tokens[start]]);
        start++;
      }
      node.raws.before += tokensToString(tokens, 0, start);
      node.source.start = this.getPosition(tokens[start][2]);
      let propStart = start;
      while (start < tokens.length) {
        let type = tokens[start][0];
        if (type === ":" || type === "space" || type === "comment") break;
        start++;
      }
      node.prop = tokensToString(tokens, propStart, start);
      let betweenStart = start;
      let token;
      while (start < tokens.length) {
        token = tokens[start];
        start++;
        if (token[0] === ":") break;
        if (token[0] === "word" && /\w/.test(token[1])) this.unknownWord([token]);
      }
      node.raws.between = tokensToString(tokens, betweenStart, start);
      if (node.prop[0] === "_" || node.prop[0] === "*") {
        node.raws.before += node.prop[0];
        node.prop = node.prop.slice(1);
      }
      let firstSpacesStart = start;
      while (start < tokens.length) {
        let next = tokens[start][0];
        if (next !== "space" && next !== "comment") break;
        start++;
      }
      let firstSpaces = tokens.slice(firstSpacesStart, start);
      tokens = tokens.slice(start);
      this.precheckMissedSemicolon(tokens);
      for (let i2 = tokens.length - 1; i2 >= 0; i2--) {
        token = tokens[i2];
        if (token[1].toLowerCase() === "!important") {
          node.important = true;
          let string = this.stringFrom(tokens, i2);
          string = this.spacesFromEnd(tokens) + string;
          if (string !== " !important") node.raws.important = string;
          break;
        } else if (token[1].toLowerCase() === "important") {
          let cache = tokens.slice(0);
          let str = "";
          for (let j = i2; j > 0; j--) {
            let type = cache[j][0];
            if (str.trim().startsWith("!") && type !== "space") break;
            str = cache.pop()[1] + str;
          }
          if (str.trim().startsWith("!")) {
            node.important = true;
            node.raws.important = str;
            tokens = cache;
          }
        }
        if (token[0] !== "space" && token[0] !== "comment") break;
      }
      if (tokens.some((i2) => i2[0] !== "space" && i2[0] !== "comment")) {
        node.raws.between += firstSpaces.map((i2) => i2[1]).join("");
        firstSpaces = [];
      }
      this.raw(node, "value", firstSpaces.concat(tokens), customProperty);
      if (node.value.includes(":") && !customProperty) this.checkMissedSemicolon(tokens);
    }
    doubleColon(token) {
      throw this.input.error("Double colon", { offset: token[2] }, { offset: token[2] + token[1].length });
    }
    emptyRule(token) {
      let node = new Rule();
      this.init(node, token[2]);
      node.selector = "";
      node.raws.between = "";
      this.current = node;
    }
    end(token) {
      if (this.current.nodes && this.current.nodes.length) this.current.raws.semicolon = this.semicolon;
      this.semicolon = false;
      this.current.raws.after = (this.current.raws.after || "") + this.spaces;
      this.spaces = "";
      if (this.current.parent) {
        this.current.source.end = this.getPosition(token[2]);
        this.current.source.end.offset++;
        this.current = this.current.parent;
      } else this.unexpectedClose(token);
    }
    endFile() {
      if (this.current.parent) this.unclosedBlock();
      if (this.current.nodes && this.current.nodes.length) this.current.raws.semicolon = this.semicolon;
      this.current.raws.after = (this.current.raws.after || "") + this.spaces;
      this.root.source.end = this.getPosition(this.tokenizer.position());
    }
    freeSemicolon(token) {
      this.spaces += token[1];
      if (this.current.nodes) {
        let prev = this.current.nodes[this.current.nodes.length - 1];
        if (prev && prev.type === "rule" && !prev.raws.ownSemicolon) {
          prev.raws.ownSemicolon = this.spaces;
          this.spaces = "";
          prev.source.end = this.getPosition(token[2]);
          prev.source.end.offset++;
        }
      }
    }
    getPosition(offset) {
      let pos = this.input.fromOffset(offset);
      return {
        column: pos.col,
        line: pos.line,
        offset
      };
    }
    init(node, offset) {
      this.current.push(node);
      node.source = {
        input: this.input,
        start: this.getPosition(offset)
      };
      node.raws.before = this.spaces;
      this.spaces = "";
      if (node.type !== "comment") this.semicolon = false;
    }
    other(start) {
      let end = false;
      let type = null;
      let colon = false;
      let bracket = null;
      let brackets = [];
      let customProperty = start[1].startsWith("--");
      let tokens = [];
      let token = start;
      while (token) {
        type = token[0];
        tokens.push(token);
        if (type === "(" || type === "[") {
          if (!bracket) bracket = token;
          brackets.push(type === "(" ? ")" : "]");
        } else if (customProperty && colon && type === "{") {
          if (!bracket) bracket = token;
          brackets.push("}");
        } else if (brackets.length === 0) {
          if (type === ";") {
            if (colon) {
              this.decl(tokens, customProperty);
              return;
            } else break;
          } else if (type === "{") {
            this.rule(tokens);
            return;
          } else if (type === "}") {
            this.tokenizer.back(tokens.pop());
            end = true;
            break;
          } else if (type === ":") colon = true;
        } else if (type === brackets[brackets.length - 1]) {
          brackets.pop();
          if (brackets.length === 0) bracket = null;
        }
        token = this.tokenizer.nextToken();
      }
      if (this.tokenizer.endOfFile()) end = true;
      if (brackets.length > 0) this.unclosedBracket(bracket);
      if (end && colon) {
        if (!customProperty) while (tokens.length) {
          token = tokens[tokens.length - 1][0];
          if (token !== "space" && token !== "comment") break;
          this.tokenizer.back(tokens.pop());
        }
        this.decl(tokens, customProperty);
      } else this.unknownWord(tokens);
    }
    parse() {
      let token;
      while (!this.tokenizer.endOfFile()) {
        token = this.tokenizer.nextToken();
        switch (token[0]) {
          case "space":
            this.spaces += token[1];
            break;
          case ";":
            this.freeSemicolon(token);
            break;
          case "}":
            this.end(token);
            break;
          case "comment":
            this.comment(token);
            break;
          case "at-word":
            this.atrule(token);
            break;
          case "{":
            this.emptyRule(token);
            break;
          default:
            this.other(token);
        }
      }
      this.endFile();
    }
    precheckMissedSemicolon() {
    }
    raw(node, prop, tokens, customProperty) {
      let token, type;
      let length = tokens.length;
      let value = "";
      let last = "";
      let clean = true;
      let next, prev;
      for (let i2 = 0; i2 < length; i2 += 1) {
        token = tokens[i2];
        type = token[0];
        if (type === "space" && i2 === length - 1 && !customProperty) clean = false;
        else if (type === "comment") {
          prev = tokens[i2 - 1] ? tokens[i2 - 1][0] : "empty";
          next = tokens[i2 + 1] ? tokens[i2 + 1][0] : "empty";
          if (!SAFE_COMMENT_NEIGHBOR[prev] && !SAFE_COMMENT_NEIGHBOR[next]) {
            if (last === ",") clean = false;
            else {
              value += token[1];
              last = token[1].slice(-1);
            }
          } else clean = false;
        } else {
          value += token[1];
          last = token[1].slice(-1);
        }
      }
      if (!clean) {
        let raw = tokens.reduce((all, i2) => all + i2[1], "");
        node.raws[prop] = {
          raw,
          value
        };
      }
      node[prop] = value;
    }
    rule(tokens) {
      tokens.pop();
      let node = new Rule();
      this.init(node, tokens[0][2]);
      node.raws.between = this.spacesAndCommentsFromEnd(tokens);
      this.raw(node, "selector", tokens);
      this.current = node;
    }
    spacesAndCommentsFromEnd(tokens) {
      let lastTokenType;
      let spaces = "";
      while (tokens.length) {
        lastTokenType = tokens[tokens.length - 1][0];
        if (lastTokenType !== "space" && lastTokenType !== "comment") break;
        spaces = tokens.pop()[1] + spaces;
      }
      return spaces;
    }
    spacesAndCommentsFromStart(tokens) {
      let next;
      let spaces = "";
      while (tokens.length) {
        next = tokens[0][0];
        if (next !== "space" && next !== "comment") break;
        spaces += tokens.shift()[1];
      }
      return spaces;
    }
    spacesFromEnd(tokens) {
      let lastTokenType;
      let spaces = "";
      while (tokens.length) {
        lastTokenType = tokens[tokens.length - 1][0];
        if (lastTokenType !== "space") break;
        spaces = tokens.pop()[1] + spaces;
      }
      return spaces;
    }
    stringFrom(tokens, from) {
      let result = "";
      for (let i2 = from; i2 < tokens.length; i2++) result += tokens[i2][1];
      tokens.splice(from, tokens.length - from);
      return result;
    }
    unclosedBlock() {
      let pos = this.current.source.start;
      throw this.input.error("Unclosed block", pos.line, pos.column);
    }
    unclosedBracket(bracket) {
      throw this.input.error("Unclosed bracket", { offset: bracket[2] }, { offset: bracket[2] + 1 });
    }
    unexpectedClose(token) {
      throw this.input.error("Unexpected }", { offset: token[2] }, { offset: token[2] + 1 });
    }
    unknownWord(tokens) {
      throw this.input.error("Unknown word " + tokens[0][1], { offset: tokens[0][2] }, { offset: tokens[0][2] + tokens[0][1].length });
    }
    unnamedAtrule(node, token) {
      throw this.input.error("At-rule without name", { offset: token[2] }, { offset: token[2] + token[1].length });
    }
  };
  module2.exports = Parser;
});
var require_parse$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Container = require_container$1();
  var Input = require_input$1();
  var Parser = require_parser$1();
  function parse(css, opts) {
    let parser = new Parser(new Input(css, opts));
    try {
      parser.parse();
    } catch (e2) {
      if (true) {
        if (e2.name === "CssSyntaxError" && opts && opts.from) {
          if (/\.scss$/i.test(opts.from)) e2.message += "\nYou tried to parse SCSS with the standard CSS parser; try again with the postcss-scss parser";
          else if (/\.sass/i.test(opts.from)) e2.message += "\nYou tried to parse Sass with the standard CSS parser; try again with the postcss-sass parser";
          else if (/\.less$/i.test(opts.from)) e2.message += "\nYou tried to parse Less with the standard CSS parser; try again with the postcss-less parser";
        }
      }
      throw e2;
    }
    return parser.root;
  }
  module2.exports = parse;
  parse.default = parse;
  Container.registerParse(parse);
});
var require_warning$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Container = require_container$1();
  var { my } = require_symbols$1();
  var Warning = class {
    constructor(text, opts = {}) {
      this.type = "warning";
      this.text = text;
      if (opts.node && opts.node.source) {
        if (!opts.node[my]) Container.rebuild(opts.node);
        let range = opts.node.rangeBy(opts);
        this.line = range.start.line;
        this.column = range.start.column;
        this.endLine = range.end.line;
        this.endColumn = range.end.column;
      }
      for (let opt in opts) this[opt] = opts[opt];
    }
    toString() {
      if (this.node) return this.node.error(this.text, {
        index: this.index,
        plugin: this.plugin,
        start: this.start,
        word: this.word
      }).message;
      if (this.plugin) return this.plugin + ": " + this.text;
      return this.text;
    }
  };
  module2.exports = Warning;
  Warning.default = Warning;
});
var require_result$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Warning = require_warning$1();
  var Result = class {
    get content() {
      return this.css;
    }
    constructor(processor, root, opts) {
      this.processor = processor;
      this.messages = [];
      this.root = root;
      this.opts = opts;
      this.css = "";
      this.map = void 0;
    }
    toString() {
      return this.css;
    }
    warn(text, opts = {}) {
      if (!opts.plugin) {
        if (this.lastPlugin && this.lastPlugin.postcssPlugin) opts.plugin = this.lastPlugin.postcssPlugin;
      }
      let warning = new Warning(text, opts);
      this.messages.push(warning);
      return warning;
    }
    warnings() {
      return this.messages.filter((i2) => i2.type === "warning");
    }
  };
  module2.exports = Result;
  Result.default = Result;
});
var require_warn_once$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var printed = {};
  module2.exports = function warnOnce(message) {
    if (printed[message]) return;
    printed[message] = true;
    if (typeof console !== "undefined" && console.warn) console.warn(message);
  };
});
var require_lazy_result$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Container = require_container$1();
  var Document2 = require_document$1();
  var MapGenerator = require_map_generator$1();
  var parse = require_parse$1();
  var Result = require_result$1();
  var Root = require_root$1();
  var stringify = require_stringify$1();
  var { isClean, my } = require_symbols$1();
  var warnOnce = require_warn_once$1();
  var TYPE_TO_CLASS_NAME = {
    atrule: "AtRule",
    comment: "Comment",
    decl: "Declaration",
    document: "Document",
    root: "Root",
    rule: "Rule"
  };
  var PLUGIN_PROPS = {
    AtRule: true,
    AtRuleExit: true,
    Comment: true,
    CommentExit: true,
    Declaration: true,
    DeclarationExit: true,
    Document: true,
    DocumentExit: true,
    Once: true,
    OnceExit: true,
    postcssPlugin: true,
    prepare: true,
    Root: true,
    RootExit: true,
    Rule: true,
    RuleExit: true
  };
  var NOT_VISITORS = {
    Once: true,
    postcssPlugin: true,
    prepare: true
  };
  var CHILDREN = 0;
  function isPromise(obj) {
    return typeof obj === "object" && typeof obj.then === "function";
  }
  function getEvents(node) {
    let key = false;
    let type = TYPE_TO_CLASS_NAME[node.type];
    if (node.type === "decl") key = node.prop.toLowerCase();
    else if (node.type === "atrule") key = node.name.toLowerCase();
    if (key && node.append) return [
      type,
      type + "-" + key,
      CHILDREN,
      type + "Exit",
      type + "Exit-" + key
    ];
    else if (key) return [
      type,
      type + "-" + key,
      type + "Exit",
      type + "Exit-" + key
    ];
    else if (node.append) return [
      type,
      CHILDREN,
      type + "Exit"
    ];
    else return [type, type + "Exit"];
  }
  function toStack(node) {
    let events;
    if (node.type === "document") events = [
      "Document",
      CHILDREN,
      "DocumentExit"
    ];
    else if (node.type === "root") events = [
      "Root",
      CHILDREN,
      "RootExit"
    ];
    else events = getEvents(node);
    return {
      eventIndex: 0,
      events,
      iterator: 0,
      node,
      visitorIndex: 0,
      visitors: []
    };
  }
  function cleanMarks(node) {
    let stack = [node];
    while (stack.length > 0) {
      let next = stack.pop();
      next[isClean] = false;
      if (next.nodes) for (let i2 of next.nodes) stack.push(i2);
    }
    return node;
  }
  var postcss = {};
  var LazyResult = class LazyResult2 {
    get content() {
      return this.stringify().content;
    }
    get css() {
      return this.stringify().css;
    }
    get map() {
      return this.stringify().map;
    }
    get messages() {
      return this.sync().messages;
    }
    get opts() {
      return this.result.opts;
    }
    get processor() {
      return this.result.processor;
    }
    get root() {
      return this.sync().root;
    }
    get [Symbol.toStringTag]() {
      return "LazyResult";
    }
    constructor(processor, css, opts) {
      this.stringified = false;
      this.processed = false;
      let root;
      if (typeof css === "object" && css !== null && (css.type === "root" || css.type === "document")) root = cleanMarks(css);
      else if (css instanceof LazyResult2 || css instanceof Result) {
        root = cleanMarks(css.root);
        if (css.map) {
          if (typeof opts.map === "undefined") opts.map = {};
          if (!opts.map.inline) opts.map.inline = false;
          opts.map.prev = css.map;
        }
      } else {
        let parser = parse;
        if (opts.syntax) parser = opts.syntax.parse;
        if (opts.parser) parser = opts.parser;
        if (parser.parse) parser = parser.parse;
        try {
          root = parser(css, opts);
        } catch (error) {
          this.processed = true;
          this.error = error;
        }
        if (root && !root[my])
          Container.rebuild(root);
      }
      this.result = new Result(processor, root, opts);
      this.helpers = __spreadProps(__spreadValues({}, postcss), {
        postcss,
        result: this.result
      });
      this.plugins = this.processor.plugins.map((plugin) => {
        if (typeof plugin === "object" && plugin.prepare) return __spreadValues(__spreadValues({}, plugin), plugin.prepare(this.result));
        else return plugin;
      });
    }
    async() {
      if (this.error) return Promise.reject(this.error);
      if (this.processed) return Promise.resolve(this.result);
      if (!this.processing) this.processing = this.runAsync();
      return this.processing;
    }
    catch(onRejected) {
      return this.async().catch(onRejected);
    }
    finally(onFinally) {
      return this.async().finally(onFinally);
    }
    getAsyncError() {
      throw new Error("Use process(css).then(cb) to work with async plugins");
    }
    handleError(error, node) {
      let plugin = this.result.lastPlugin;
      try {
        if (node) node.addToError(error);
        this.error = error;
        if (error.name === "CssSyntaxError" && !error.plugin) {
          error.plugin = plugin.postcssPlugin;
          error.setMessage();
        } else if (plugin.postcssVersion) {
          if (true) {
            let pluginName = plugin.postcssPlugin;
            let pluginVer = plugin.postcssVersion;
            let runtimeVer = this.result.processor.version;
            let a2 = pluginVer.split(".");
            let b = runtimeVer.split(".");
            if (a2[0] !== b[0] || parseInt(a2[1]) > parseInt(b[1])) console.error("Unknown error from PostCSS plugin. Your current PostCSS version is " + runtimeVer + ", but " + pluginName + " uses " + pluginVer + ". Perhaps this is the source of the error below.");
          }
        }
      } catch (err) {
        if (console && console.error) console.error(err);
      }
      return error;
    }
    prepareVisitors() {
      this.listeners = {};
      let add = (plugin, type, cb) => {
        if (!this.listeners[type]) this.listeners[type] = [];
        this.listeners[type].push([plugin, cb]);
      };
      for (let plugin of this.plugins) if (typeof plugin === "object") for (let event in plugin) {
        if (!PLUGIN_PROPS[event] && /^[A-Z]/.test(event)) throw new Error(`Unknown event ${event} in ${plugin.postcssPlugin}. Try to update PostCSS (${this.processor.version} now).`);
        if (!NOT_VISITORS[event]) {
          if (typeof plugin[event] === "object") for (let filter in plugin[event]) if (filter === "*") add(plugin, event, plugin[event][filter]);
          else add(plugin, event + "-" + filter.toLowerCase(), plugin[event][filter]);
          else if (typeof plugin[event] === "function") add(plugin, event, plugin[event]);
        }
      }
      this.hasListener = Object.keys(this.listeners).length > 0;
    }
    async runAsync() {
      this.plugin = 0;
      for (let i2 = 0; i2 < this.plugins.length; i2++) {
        let plugin = this.plugins[i2];
        let promise = this.runOnRoot(plugin);
        if (isPromise(promise)) try {
          await promise;
        } catch (error) {
          throw this.handleError(error);
        }
      }
      this.prepareVisitors();
      if (this.hasListener) {
        let root = this.result.root;
        while (!root[isClean]) {
          root[isClean] = true;
          let stack = [toStack(root)];
          while (stack.length > 0) {
            let promise = this.visitTick(stack);
            if (isPromise(promise)) try {
              await promise;
            } catch (e2) {
              let node = stack[stack.length - 1].node;
              throw this.handleError(e2, node);
            }
          }
        }
        if (this.listeners.OnceExit) for (let [plugin, visitor] of this.listeners.OnceExit) {
          this.result.lastPlugin = plugin;
          try {
            if (root.type === "document") {
              let roots = root.nodes.map((subRoot) => visitor(subRoot, this.helpers));
              await Promise.all(roots);
            } else await visitor(root, this.helpers);
          } catch (e2) {
            throw this.handleError(e2);
          }
        }
      }
      this.processed = true;
      return this.stringify();
    }
    runOnRoot(plugin) {
      this.result.lastPlugin = plugin;
      try {
        if (typeof plugin === "object" && plugin.Once) {
          if (this.result.root.type === "document") {
            let roots = this.result.root.nodes.map((root) => plugin.Once(root, this.helpers));
            if (isPromise(roots[0])) return Promise.all(roots);
            return roots;
          }
          return plugin.Once(this.result.root, this.helpers);
        } else if (typeof plugin === "function") return plugin(this.result.root, this.result);
      } catch (error) {
        throw this.handleError(error);
      }
    }
    stringify() {
      if (this.error) throw this.error;
      if (this.stringified) return this.result;
      this.stringified = true;
      this.sync();
      let opts = this.result.opts;
      let str = stringify;
      if (opts.syntax) str = opts.syntax.stringify;
      if (opts.stringifier) str = opts.stringifier;
      if (str.stringify) str = str.stringify;
      let rootSource = this.result.root.source;
      if (opts.map === void 0 && !(rootSource && rootSource.input && rootSource.input.map)) {
        let result = "";
        str(this.result.root, (i2) => {
          result += i2;
        });
        this.result.css = result;
        return this.result;
      }
      let data = new MapGenerator(str, this.result.root, this.result.opts).generate();
      this.result.css = data[0];
      this.result.map = data[1];
      return this.result;
    }
    sync() {
      if (this.error) throw this.error;
      if (this.processed) return this.result;
      this.processed = true;
      if (this.processing) throw this.getAsyncError();
      for (let plugin of this.plugins) if (isPromise(this.runOnRoot(plugin))) throw this.getAsyncError();
      this.prepareVisitors();
      if (this.hasListener) {
        let root = this.result.root;
        while (!root[isClean]) {
          root[isClean] = true;
          this.walkSync(root);
        }
        if (this.listeners.OnceExit) {
          if (root.type === "document") for (let subRoot of root.nodes) this.visitSync(this.listeners.OnceExit, subRoot);
          else this.visitSync(this.listeners.OnceExit, root);
        }
      }
      return this.result;
    }
    then(onFulfilled, onRejected) {
      if (true) {
        if (!("from" in this.opts)) warnOnce("Without `from` option PostCSS could generate wrong source map and will not find Browserslist config. Set it to CSS file path or to `undefined` to prevent this warning.");
      }
      return this.async().then(onFulfilled, onRejected);
    }
    toString() {
      return this.css;
    }
    visitSync(visitors, node) {
      for (let [plugin, visitor] of visitors) {
        this.result.lastPlugin = plugin;
        let promise;
        try {
          promise = visitor(node, this.helpers);
        } catch (e2) {
          throw this.handleError(e2, node.proxyOf);
        }
        if (node.type !== "root" && node.type !== "document" && !node.parent) return true;
        if (isPromise(promise)) throw this.getAsyncError();
      }
    }
    visitTick(stack) {
      let visit2 = stack[stack.length - 1];
      let { node, visitors } = visit2;
      if (node.type !== "root" && node.type !== "document" && !node.parent) {
        stack.pop();
        return;
      }
      if (visitors.length > 0 && visit2.visitorIndex < visitors.length) {
        let [plugin, visitor] = visitors[visit2.visitorIndex];
        visit2.visitorIndex += 1;
        if (visit2.visitorIndex === visitors.length) {
          visit2.visitors = [];
          visit2.visitorIndex = 0;
        }
        this.result.lastPlugin = plugin;
        try {
          return visitor(node.toProxy(), this.helpers);
        } catch (e2) {
          throw this.handleError(e2, node);
        }
      }
      if (visit2.iterator !== 0) {
        let iterator = visit2.iterator;
        if (visit2.descending) {
          visit2.descending = false;
          node.indexes[iterator] += 1;
        }
        let child;
        while (child = node.nodes[node.indexes[iterator]]) {
          if (!child[isClean]) {
            child[isClean] = true;
            visit2.descending = true;
            stack.push(toStack(child));
            return;
          }
          node.indexes[iterator] += 1;
        }
        visit2.iterator = 0;
        delete node.indexes[iterator];
      }
      let events = visit2.events;
      while (visit2.eventIndex < events.length) {
        let event = events[visit2.eventIndex];
        visit2.eventIndex += 1;
        if (event === CHILDREN) {
          if (node.nodes && node.nodes.length) {
            node[isClean] = true;
            visit2.iterator = node.getIterator();
          }
          return;
        } else if (this.listeners[event]) {
          visit2.visitors = this.listeners[event];
          return;
        }
      }
      stack.pop();
    }
    walkSync(node) {
      node[isClean] = true;
      let stack = [{
        eventIndex: 0,
        events: getEvents(node),
        iterator: 0,
        node
      }];
      while (stack.length > 0) {
        let visit2 = stack[stack.length - 1];
        let visitNode = visit2.node;
        if (visit2.iterator !== 0) {
          let iterator = visit2.iterator;
          if (visit2.descending) {
            visit2.descending = false;
            visitNode.indexes[iterator] += 1;
          }
          let child;
          let descended = false;
          while (child = visitNode.nodes[visitNode.indexes[iterator]]) {
            if (!child[isClean]) {
              child[isClean] = true;
              visit2.descending = true;
              stack.push({
                eventIndex: 0,
                events: getEvents(child),
                iterator: 0,
                node: child
              });
              descended = true;
              break;
            }
            visitNode.indexes[iterator] += 1;
          }
          if (descended) continue;
          visit2.iterator = 0;
          delete visitNode.indexes[iterator];
        }
        if (visit2.eventIndex < visit2.events.length) {
          let event = visit2.events[visit2.eventIndex];
          visit2.eventIndex += 1;
          if (event === CHILDREN) {
            if (visitNode.nodes && visitNode.nodes.length) visit2.iterator = visitNode.getIterator();
          } else {
            let visitors = this.listeners[event];
            if (visitors) {
              if (this.visitSync(visitors, visitNode.toProxy())) stack.pop();
            }
          }
          continue;
        }
        stack.pop();
      }
    }
    warnings() {
      return this.sync().warnings();
    }
  };
  LazyResult.registerPostcss = (dependant) => {
    postcss = dependant;
  };
  module2.exports = LazyResult;
  LazyResult.default = LazyResult;
  Root.registerLazyResult(LazyResult);
  Document2.registerLazyResult(LazyResult);
});
var require_no_work_result$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var MapGenerator = require_map_generator$1();
  var parse = require_parse$1();
  var Result = require_result$1();
  var stringify = require_stringify$1();
  var warnOnce = require_warn_once$1();
  var NoWorkResult = class {
    get content() {
      return this.result.css;
    }
    get css() {
      return this.result.css;
    }
    get map() {
      return this.result.map;
    }
    get messages() {
      return [];
    }
    get opts() {
      return this.result.opts;
    }
    get processor() {
      return this.result.processor;
    }
    get root() {
      if (this._root) return this._root;
      let root;
      let parser = parse;
      try {
        root = parser(this._css, this._opts);
      } catch (error) {
        this.error = error;
      }
      if (this.error) throw this.error;
      else {
        this._root = root;
        return root;
      }
    }
    get [Symbol.toStringTag]() {
      return "NoWorkResult";
    }
    constructor(processor, css, opts) {
      css = css.toString();
      this.stringified = false;
      this._processor = processor;
      this._css = css;
      this._opts = opts;
      this._map = void 0;
      let str = stringify;
      this.result = new Result(this._processor, void 0, this._opts);
      this.result.css = css;
      let self2 = this;
      Object.defineProperty(this.result, "root", { get() {
        return self2.root;
      } });
      let map = new MapGenerator(str, void 0, this._opts, css);
      if (map.isMap()) {
        let [generatedCSS, generatedMap] = map.generate();
        if (generatedCSS) this.result.css = generatedCSS;
        if (generatedMap) this.result.map = generatedMap;
      } else {
        map.clearAnnotation();
        this.result.css = map.css;
      }
    }
    async() {
      if (this.error) return Promise.reject(this.error);
      return Promise.resolve(this.result);
    }
    catch(onRejected) {
      return this.async().catch(onRejected);
    }
    finally(onFinally) {
      return this.async().finally(onFinally);
    }
    sync() {
      if (this.error) throw this.error;
      return this.result;
    }
    then(onFulfilled, onRejected) {
      if (true) {
        if (!("from" in this._opts)) warnOnce("Without `from` option PostCSS could generate wrong source map and will not find Browserslist config. Set it to CSS file path or to `undefined` to prevent this warning.");
      }
      return this.async().then(onFulfilled, onRejected);
    }
    toString() {
      return this._css;
    }
    warnings() {
      return [];
    }
  };
  module2.exports = NoWorkResult;
  NoWorkResult.default = NoWorkResult;
});
var require_processor$1 = /* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var Document2 = require_document$1();
  var LazyResult = require_lazy_result$1();
  var NoWorkResult = require_no_work_result$1();
  var Root = require_root$1();
  var Processor = class {
    constructor(plugins = []) {
      this.version = "8.5.32";
      this.plugins = this.normalize(plugins);
    }
    normalize(plugins) {
      let normalized = [];
      for (let i2 of plugins) {
        if (i2.postcss === true) i2 = i2();
        else if (i2.postcss) i2 = i2.postcss;
        if (typeof i2 === "object" && Array.isArray(i2.plugins)) normalized = normalized.concat(i2.plugins);
        else if (typeof i2 === "object" && i2.postcssPlugin) normalized.push(i2);
        else if (typeof i2 === "function") normalized.push(i2);
        else if (typeof i2 === "object" && (i2.parse || i2.stringify)) {
          if (true) throw new Error("PostCSS syntaxes cannot be used as plugins. Instead, please use one of the syntax/parser/stringifier options as outlined in your PostCSS runner documentation.");
        } else throw new Error(i2 + " is not a PostCSS plugin");
      }
      return normalized;
    }
    process(css, opts = {}) {
      if (!this.plugins.length && !opts.parser && !opts.stringifier && !opts.syntax) return new NoWorkResult(this, css, opts);
      else return new LazyResult(this, css, opts);
    }
    use(plugin) {
      this.plugins = this.plugins.concat(this.normalize([plugin]));
      return this;
    }
  };
  module2.exports = Processor;
  Processor.default = Processor;
  Root.registerProcessor(Processor);
  Document2.registerProcessor(Processor);
});
var import_postcss$1 = /* @__PURE__ */ __toESM$1((/* @__PURE__ */ __commonJSMin$1((exports2, module2) => {
  var AtRule = require_at_rule$1();
  var Comment = require_comment$1();
  var Container = require_container$1();
  var CssSyntaxError = require_css_syntax_error$1();
  var Declaration = require_declaration$1();
  var Document2 = require_document$1();
  var fromJSON = require_fromJSON$1();
  var Input = require_input$1();
  var LazyResult = require_lazy_result$1();
  var list = require_list$1();
  var Node2 = require_node$1();
  var parse = require_parse$1();
  var Processor = require_processor$1();
  var Result = require_result$1();
  var Root = require_root$1();
  var Rule = require_rule$1();
  var stringify = require_stringify$1();
  var Warning = require_warning$1();
  function postcss(...plugins) {
    if (plugins.length === 1 && Array.isArray(plugins[0])) plugins = plugins[0];
    return new Processor(plugins);
  }
  postcss.plugin = function plugin(name, initializer) {
    let warningPrinted = false;
    function creator(...args) {
      if (console && console.warn && !warningPrinted) {
        warningPrinted = true;
        console.warn(name + ": postcss.plugin was deprecated. Migration guide:\nhttps://evilmartians.com/chronicles/postcss-8-plugin-migration");
        if (process.env.LANG && process.env.LANG.startsWith("zh"))
          console.warn(name + ": \u91CC\u9762 postcss.plugin \u88AB\u5F03\u7528. \u8FC1\u79FB\u6307\u5357:\nhttps://www.w3ctech.com/topic/2226");
      }
      let transformer = initializer(...args);
      transformer.postcssPlugin = name;
      transformer.postcssVersion = new Processor().version;
      return transformer;
    }
    let cache;
    Object.defineProperty(creator, "postcss", { get() {
      if (!cache) cache = creator();
      return cache;
    } });
    creator.process = function(css, processOpts, pluginOpts) {
      return postcss([creator(pluginOpts)]).process(css, processOpts);
    };
    return creator;
  };
  postcss.stringify = stringify;
  postcss.parse = parse;
  postcss.fromJSON = fromJSON;
  postcss.list = list;
  postcss.comment = (defaults) => new Comment(defaults);
  postcss.atRule = (defaults) => new AtRule(defaults);
  postcss.decl = (defaults) => new Declaration(defaults);
  postcss.rule = (defaults) => new Rule(defaults);
  postcss.root = (defaults) => new Root(defaults);
  postcss.document = (defaults) => new Document2(defaults);
  postcss.CssSyntaxError = CssSyntaxError;
  postcss.Declaration = Declaration;
  postcss.Container = Container;
  postcss.Processor = Processor;
  postcss.Document = Document2;
  postcss.Comment = Comment;
  postcss.Warning = Warning;
  postcss.AtRule = AtRule;
  postcss.Result = Result;
  postcss.Input = Input;
  postcss.Rule = Rule;
  postcss.Root = Root;
  postcss.Node = Node2;
  LazyResult.registerPostcss(postcss);
  module2.exports = postcss;
  postcss.default = postcss;
}))(), 1);
var postcss_default = import_postcss$1.default;
import_postcss$1.default.stringify;
import_postcss$1.default.fromJSON;
import_postcss$1.default.plugin;
import_postcss$1.default.parse;
import_postcss$1.default.list;
import_postcss$1.default.document;
import_postcss$1.default.comment;
import_postcss$1.default.atRule;
import_postcss$1.default.rule;
import_postcss$1.default.decl;
import_postcss$1.default.root;
import_postcss$1.default.CssSyntaxError;
import_postcss$1.default.Declaration;
import_postcss$1.default.Container;
import_postcss$1.default.Processor;
import_postcss$1.default.Document;
import_postcss$1.default.Comment;
import_postcss$1.default.Warning;
import_postcss$1.default.AtRule;
import_postcss$1.default.Result;
import_postcss$1.default.Input;
import_postcss$1.default.Rule;
import_postcss$1.default.Root;
import_postcss$1.default.Node;
var tagMap = {
  script: "noscript",
  altglyph: "altGlyph",
  altglyphdef: "altGlyphDef",
  altglyphitem: "altGlyphItem",
  animatecolor: "animateColor",
  animatemotion: "animateMotion",
  animatetransform: "animateTransform",
  clippath: "clipPath",
  feblend: "feBlend",
  fecolormatrix: "feColorMatrix",
  fecomponenttransfer: "feComponentTransfer",
  fecomposite: "feComposite",
  feconvolvematrix: "feConvolveMatrix",
  fediffuselighting: "feDiffuseLighting",
  fedisplacementmap: "feDisplacementMap",
  fedistantlight: "feDistantLight",
  fedropshadow: "feDropShadow",
  feflood: "feFlood",
  fefunca: "feFuncA",
  fefuncb: "feFuncB",
  fefuncg: "feFuncG",
  fefuncr: "feFuncR",
  fegaussianblur: "feGaussianBlur",
  feimage: "feImage",
  femerge: "feMerge",
  femergenode: "feMergeNode",
  femorphology: "feMorphology",
  feoffset: "feOffset",
  fepointlight: "fePointLight",
  fespecularlighting: "feSpecularLighting",
  fespotlight: "feSpotLight",
  fetile: "feTile",
  feturbulence: "feTurbulence",
  foreignobject: "foreignObject",
  glyphref: "glyphRef",
  lineargradient: "linearGradient",
  radialgradient: "radialGradient"
};
function getTagName(n2) {
  let tagName = tagMap[n2.tagName] ? tagMap[n2.tagName] : n2.tagName;
  if (tagName === "link" && n2.attributes._cssText) tagName = "style";
  return tagName;
}
function adaptCssForReplay(cssText, cache) {
  const cachedStyle = cache == null ? void 0 : cache.stylesWithHoverClass.get(cssText);
  if (cachedStyle) return cachedStyle;
  let result = cssText;
  try {
    result = postcss_default([mediaSelectorPlugin, pseudoClassPlugin]).process(cssText).css;
  } catch (error) {
    console.warn("Failed to adapt css for replay", error);
  }
  cache == null ? void 0 : cache.stylesWithHoverClass.set(cssText, result);
  return result;
}
function createCache() {
  return { stylesWithHoverClass: /* @__PURE__ */ new Map() };
}
var REBUILD_TARGET_ERROR = "rrweb-snapshot.rebuild() cannot rebuild into an unprotected browser document. Use rebuildIntoSandboxedIframe() or set UNSAFE_allowUnprotectedRebuild: true only when you accept the script-execution risk.";
var SANDBOXED_IFRAME_ROOT_ERROR = "rrweb-snapshot.createSandboxedIframe() requires root to be connected to a document before creating a sandboxed iframe.";
var sandboxedRebuildDocuments = /* @__PURE__ */ new WeakSet();
function isSupportedSandboxedIframe(frameElement) {
  if (!frameElement || frameElement.tagName !== "IFRAME") return false;
  if (!("sandbox" in frameElement)) return false;
  const sandboxTokens = Array.from(frameElement.sandbox);
  return sandboxTokens.length === 1 && sandboxTokens[0] === "allow-same-origin";
}
function assertRebuildTargetAllowed(options) {
  if (options.UNSAFE_allowUnprotectedRebuild) return;
  const win = options.doc.defaultView;
  if (!win) return;
  if (sandboxedRebuildDocuments.has(options.doc) && isSupportedSandboxedIframe(win.frameElement)) return;
  throw new Error(REBUILD_TARGET_ERROR);
}
function applyCssSplits(n2, cssText, hackCss, cache) {
  const childTextNodes = [];
  for (const scn of n2.childNodes) if (scn.type === NodeType$3.Text) childTextNodes.push(scn);
  const cssTextSplits = cssText.split("/* rr_split */");
  while (cssTextSplits.length > 1 && cssTextSplits.length > childTextNodes.length) cssTextSplits.splice(-2, 2, cssTextSplits.slice(-2).join(""));
  let adaptedCss = "";
  if (hackCss) adaptedCss = adaptCssForReplay(cssTextSplits.join(""), cache);
  let startIndex = 0;
  let errorOffset = -1;
  for (let i2 = 0; i2 < childTextNodes.length; i2++) {
    if (i2 === cssTextSplits.length) break;
    const childTextNode = childTextNodes[i2];
    let cssTextSection = cssTextSplits[i2];
    const nextTextSection = i2 < cssTextSplits.length - 1 ? cssTextSplits[i2 + 1] : null;
    if (hackCss && nextTextSection) {
      let endIndex = startIndex;
      let endSearch = nextTextSection.length;
      endSearch = Math.min(endSearch, 30);
      let found = false;
      for (; endSearch > 2; endSearch--) {
        const searchBit = nextTextSection.substring(0, endSearch);
        const searchIndex = adaptedCss.substring(startIndex).indexOf(searchBit);
        found = searchIndex !== -1;
        if (found) {
          endIndex += searchIndex;
          break;
        }
      }
      if (!found) endIndex += cssTextSection.length;
      cssTextSection = adaptedCss.substring(startIndex, endIndex);
      startIndex = endIndex;
    } else if (hackCss) cssTextSection = adaptedCss.substring(startIndex);
    if (errorOffset !== -1) cssTextSection = cssTextSection.substring(errorOffset);
    if (nextTextSection && (errorOffset !== -1 || cssTextSection.endsWith("0p") && nextTextSection.startsWith("x"))) {
      errorOffset = nextTextSection.indexOf("}");
      if (errorOffset !== -1) {
        errorOffset += 1;
        cssTextSection += nextTextSection.substring(0, errorOffset);
      }
    }
    childTextNode.textContent = cssTextSection;
  }
}
function buildStyleNode(n2, styleEl, cssText, options) {
  const { doc, hackCss, cache } = options;
  if (n2.childNodes.length) applyCssSplits(n2, cssText, hackCss, cache);
  else {
    if (hackCss) cssText = adaptCssForReplay(cssText, cache);
    styleEl.appendChild(doc.createTextNode(cssText));
  }
}
function buildNode(n2, options) {
  var _a2;
  const { doc, hackCss, cache } = options;
  switch (n2.type) {
    case NodeType$3.Document:
      return doc.implementation.createDocument(null, "", null);
    case NodeType$3.DocumentType:
      return doc.implementation.createDocumentType(n2.name || "html", n2.publicId, n2.systemId);
    case NodeType$3.Element: {
      const tagName = getTagName(n2);
      let node;
      if (n2.isSVG) node = doc.createElementNS("http://www.w3.org/2000/svg", tagName);
      else {
        if (n2.isCustom && ((_a2 = doc.defaultView) == null ? void 0 : _a2.customElements) && !doc.defaultView.customElements.get(n2.tagName)) doc.defaultView.customElements.define(n2.tagName, class extends doc.defaultView.HTMLElement {
        });
        node = doc.createElement(tagName);
      }
      const specialAttributes = {};
      for (const name in n2.attributes) {
        if (!Object.prototype.hasOwnProperty.call(n2.attributes, name)) continue;
        let value = n2.attributes[name];
        if (tagName === "option" && name === "selected" && value === false) continue;
        if (value === null) continue;
        if (value === true) value = "";
        if (name.startsWith("rr_")) {
          specialAttributes[name] = value;
          continue;
        }
        if (typeof value !== "string") {
        } else if (tagName === "style" && name === "_cssText") {
          buildStyleNode(n2, node, value, options);
          continue;
        } else if (tagName === "textarea" && name === "value") {
          node.appendChild(doc.createTextNode(value));
          n2.childNodes = [];
          continue;
        }
        try {
          if (n2.isSVG && name === "xlink:href") node.setAttributeNS("http://www.w3.org/1999/xlink", name, value.toString());
          else if (name === "onload" || name === "onclick" || name.substring(0, 7) === "onmouse") node.setAttribute("_" + name, value.toString());
          else if (tagName === "meta" && n2.attributes["http-equiv"] === "Content-Security-Policy" && name === "content") {
            node.setAttribute("csp-content", value.toString());
            continue;
          } else if (tagName === "link" && (n2.attributes.rel === "preload" && n2.attributes.as === "script" || n2.attributes.rel === "modulepreload")) {
          } else if (tagName === "link" && n2.attributes.rel === "prefetch" && typeof n2.attributes.href === "string" && extractFileExtension(n2.attributes.href) === "js") {
          } else if (tagName === "img" && n2.attributes.srcset && n2.attributes.rr_dataURL) node.setAttribute("rrweb-original-srcset", n2.attributes.srcset);
          else node.setAttribute(name, value.toString());
        } catch (error) {
        }
      }
      for (const name in specialAttributes) {
        const value = specialAttributes[name];
        if (tagName === "canvas" && name === "rr_dataURL") {
          const image = doc.createElement("img");
          image.onload = () => {
            const ctx = node.getContext("2d");
            if (ctx) ctx.drawImage(image, 0, 0, image.width, image.height);
          };
          image.src = value.toString();
          if (node.RRNodeType) node.rr_dataURL = value.toString();
        } else if (tagName === "img" && name === "rr_dataURL") {
          const image = node;
          if (!image.currentSrc.startsWith("data:")) {
            image.setAttribute("rrweb-original-src", n2.attributes.src);
            image.src = value.toString();
          }
        }
        if (name === "rr_width") node.style.setProperty("width", value.toString());
        else if (name === "rr_height") node.style.setProperty("height", value.toString());
        else if (name === "rr_mediaCurrentTime" && typeof value === "number") node.currentTime = value;
        else if (name === "rr_mediaState") switch (value) {
          case "played":
            node.play().catch((e2) => console.warn("media playback error", e2));
            break;
          case "paused":
            node.pause();
        }
        else if (name === "rr_mediaPlaybackRate" && typeof value === "number") node.playbackRate = value;
        else if (name === "rr_mediaMuted" && typeof value === "boolean") node.muted = value;
        else if (name === "rr_mediaLoop" && typeof value === "boolean") node.loop = value;
        else if (name === "rr_mediaVolume" && typeof value === "number") node.volume = value;
        else if (name === "rr_open_mode") node.setAttribute("rr_open_mode", value);
      }
      if (n2.isShadowHost) {
        if (!node.shadowRoot) node.attachShadow({ mode: "open" });
        else while (node.shadowRoot.firstChild) node.shadowRoot.removeChild(node.shadowRoot.firstChild);
      }
      if (tagName === "input" || tagName === "textarea") node.setAttribute("autocomplete", "off");
      return node;
    }
    case NodeType$3.Text:
      if (n2.isStyle && hackCss) return doc.createTextNode(adaptCssForReplay(n2.textContent, cache));
      return doc.createTextNode(n2.textContent);
    case NodeType$3.CDATA:
      return doc.createCDATASection(n2.textContent);
    case NodeType$3.Comment:
      return doc.createComment(n2.textContent);
    default:
      return null;
  }
}
function buildNodeWithSN(n2, options) {
  const { doc, mirror: mirror2, skipChild = false, hackCss = true, afterAppend, cache } = options;
  if (mirror2.has(n2.id)) {
    const nodeInMirror = mirror2.getNode(n2.id);
    if (isNodeMetaEqual(mirror2.getMeta(nodeInMirror), n2)) return mirror2.getNode(n2.id);
  }
  let node = buildNode(n2, {
    doc,
    hackCss,
    cache
  });
  if (!node) return null;
  if (n2.rootId && mirror2.getNode(n2.rootId) !== doc) mirror2.replace(n2.rootId, doc);
  if (n2.type === NodeType$3.Document) {
    doc.close();
    doc.open();
    if (n2.compatMode === "BackCompat" && n2.childNodes && n2.childNodes[0].type !== NodeType$3.DocumentType) {
      if (n2.childNodes[0].type === NodeType$3.Element && "xmlns" in n2.childNodes[0].attributes && n2.childNodes[0].attributes.xmlns === "http://www.w3.org/1999/xhtml") doc.write('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "">');
      else doc.write('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "">');
    }
    node = doc;
  }
  mirror2.add(node, n2);
  if ((n2.type === NodeType$3.Document || n2.type === NodeType$3.Element) && !skipChild) for (const childN of n2.childNodes) {
    const childNode = buildNodeWithSN(childN, {
      doc,
      mirror: mirror2,
      skipChild: false,
      hackCss,
      afterAppend,
      cache
    });
    if (!childNode) {
      console.warn("Failed to rebuild", childN);
      continue;
    }
    if (childN.isShadow && isElement(node) && node.shadowRoot) node.shadowRoot.appendChild(childNode);
    else if (n2.type === NodeType$3.Document && childN.type == NodeType$3.Element) {
      const htmlElement = childNode;
      let body = null;
      htmlElement.childNodes.forEach((child) => {
        if (child.nodeName === "BODY") body = child;
      });
      if (body) {
        htmlElement.removeChild(body);
        node.appendChild(childNode);
        htmlElement.appendChild(body);
      } else node.appendChild(childNode);
    } else node.appendChild(childNode);
    if (afterAppend) afterAppend(childNode, childN.id);
  }
  return node;
}
function visit(mirror2, onVisit) {
  function walk(node) {
    onVisit(node);
  }
  for (const id of mirror2.getIds()) if (mirror2.has(id)) walk(mirror2.getNode(id));
}
function handleScroll(node, mirror2) {
  const n2 = mirror2.getMeta(node);
  if ((n2 == null ? void 0 : n2.type) !== NodeType$3.Element) return;
  const el = node;
  for (const name in n2.attributes) {
    if (!(Object.prototype.hasOwnProperty.call(n2.attributes, name) && name.startsWith("rr_"))) continue;
    const value = n2.attributes[name];
    if (name === "rr_scrollLeft") el.scrollLeft = value;
    if (name === "rr_scrollTop") el.scrollTop = value;
  }
}
function rebuild(n2, options) {
  assertRebuildTargetAllowed(options);
  const { doc, onVisit, hackCss = true, afterAppend, cache, mirror: mirror2 = new Mirror$2() } = options;
  const node = buildNodeWithSN(n2, {
    doc,
    mirror: mirror2,
    skipChild: false,
    hackCss,
    afterAppend,
    cache
  });
  visit(mirror2, (visitedNode) => {
    if (onVisit) onVisit(visitedNode);
    handleScroll(visitedNode, mirror2);
  });
  return node;
}
function createSandboxedIframe(options) {
  var _a2;
  if (!options.root.isConnected) throw new Error(SANDBOXED_IFRAME_ROOT_ERROR);
  const iframe = options.root.ownerDocument.createElement("iframe");
  for (const [name, value] of Object.entries(options.iframeAttributes || {})) {
    if (name === "sandbox") continue;
    iframe.setAttribute(name, value);
  }
  iframe.setAttribute("sandbox", "allow-same-origin");
  options.root.appendChild(iframe);
  const doc = iframe.contentDocument;
  if (!doc || !iframe.contentWindow) {
    (_a2 = iframe.parentNode) == null ? void 0 : _a2.removeChild(iframe);
    throw new Error(SANDBOXED_IFRAME_ROOT_ERROR);
  }
  sandboxedRebuildDocuments.add(doc);
  return iframe;
}
var __create = Object.create;
var __defProp2 = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __getProtoOf = Object.getPrototypeOf;
var __hasOwnProp2 = Object.prototype.hasOwnProperty;
var __commonJSMin = (cb, mod) => () => (mod || (cb((mod = { exports: {} }).exports, mod, mod.exports), cb = null), mod.exports);
var __copyProps = (to, from, except, desc) => {
  if (from && typeof from === "object" || typeof from === "function") for (var keys = __getOwnPropNames(from), i2 = 0, n2 = keys.length, key; i2 < n2; i2++) {
    key = keys[i2];
    if (!__hasOwnProp2.call(to, key) && key !== except) __defProp2(to, key, {
      get: ((k) => from[k]).bind(null, key),
      enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable
    });
  }
  return to;
};
var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(isNodeMode || !mod || !mod.__esModule || !__hasOwnProp2.call(mod, "default") ? __defProp2(target, "default", {
  value: mod,
  enumerable: true
}) : target, mod));
var testableAccessors$1 = {
  Node: [
    "childNodes",
    "parentNode",
    "parentElement",
    "textContent",
    "ownerDocument",
    "firstChild",
    "lastChild",
    "nextSibling",
    "previousSibling"
  ],
  ShadowRoot: ["host", "styleSheets"],
  Element: [
    "shadowRoot",
    "querySelector",
    "querySelectorAll"
  ],
  MutationObserver: []
};
var testableMethods$1 = {
  Node: ["contains", "getRootNode"],
  ShadowRoot: ["getSelection"],
  Element: [],
  MutationObserver: ["constructor"]
};
var untaintedBasePrototype$1 = {};
var untaintedBaseIframeCleanup$1 = {};
var mutationObserverUsers$1 = 0;
function angularZoneUnpatchedAlternative$1(key) {
  var _a2, _b;
  const angularUnpatchedVersionSymbol = (_b = (_a2 = globalThis == null ? void 0 : globalThis.Zone) == null ? void 0 : _a2.__symbol__) == null ? void 0 : _b.call(_a2, key);
  if (angularUnpatchedVersionSymbol && globalThis[angularUnpatchedVersionSymbol]) return globalThis[angularUnpatchedVersionSymbol];
  else return;
}
function getUntaintedPrototype$1(key) {
  if (untaintedBasePrototype$1[key]) return untaintedBasePrototype$1[key];
  const defaultObj = angularZoneUnpatchedAlternative$1(key) || globalThis[key];
  const defaultPrototype = defaultObj.prototype;
  const accessorNames = key in testableAccessors$1 ? testableAccessors$1[key] : void 0;
  const isUntaintedAccessors = Boolean(accessorNames && accessorNames.every((accessor) => {
    var _a2, _b;
    return Boolean((_b = (_a2 = Object.getOwnPropertyDescriptor(defaultPrototype, accessor)) == null ? void 0 : _a2.get) == null ? void 0 : _b.toString().includes("[native code]"));
  }));
  const methodNames = key in testableMethods$1 ? testableMethods$1[key] : void 0;
  const isUntaintedMethods = Boolean(methodNames && methodNames.every((method) => {
    var _a2;
    return typeof defaultPrototype[method] === "function" && ((_a2 = defaultPrototype[method]) == null ? void 0 : _a2.toString().includes("[native code]"));
  }));
  if (isUntaintedAccessors && isUntaintedMethods) {
    untaintedBasePrototype$1[key] = defaultObj.prototype;
    return defaultObj.prototype;
  }
  try {
    const iframeEl = document.createElement("iframe");
    iframeEl.style.display = "none";
    document.body.appendChild(iframeEl);
    const win = iframeEl.contentWindow;
    if (!win) return defaultObj.prototype;
    const untaintedObject = win[key].prototype;
    if (!untaintedObject) {
      iframeEl.remove();
      return defaultPrototype;
    }
    const ua = navigator.userAgent;
    if (ua.includes("Safari") && !ua.includes("Chrome")) {
      iframeEl.classList.add("rr-block");
      iframeEl.setAttribute("__rrwebUntaintedMutationObserver", "");
      untaintedBaseIframeCleanup$1[key] = () => iframeEl.remove();
    } else iframeEl.remove();
    return untaintedBasePrototype$1[key] = untaintedObject;
  } catch (e2) {
    return defaultPrototype;
  }
}
var untaintedAccessorCache$1 = {};
function getUntaintedAccessor$1(key, instance, accessor) {
  var _a2, _b;
  const cached = (_a2 = untaintedAccessorCache$1[key]) == null ? void 0 : _a2[accessor];
  if (cached) return cached.call(instance);
  const untaintedPrototype = getUntaintedPrototype$1(key);
  const untaintedAccessor = (_b = Object.getOwnPropertyDescriptor(untaintedPrototype, accessor)) == null ? void 0 : _b.get;
  if (!untaintedAccessor) return instance[accessor];
  (untaintedAccessorCache$1[key] || (untaintedAccessorCache$1[key] = {}))[accessor] = untaintedAccessor;
  return untaintedAccessor.call(instance);
}
var untaintedMethodCache$1 = {};
function getUntaintedMethod$1(key, instance, method) {
  const cacheKey = `${key}.${String(method)}`;
  if (untaintedMethodCache$1[cacheKey]) return untaintedMethodCache$1[cacheKey].bind(instance);
  const untaintedMethod = getUntaintedPrototype$1(key)[method];
  if (typeof untaintedMethod !== "function") return instance[method];
  untaintedMethodCache$1[cacheKey] = untaintedMethod;
  return untaintedMethod.bind(instance);
}
function ownerDocument$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "ownerDocument");
}
function childNodes$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "childNodes");
}
function parentNode$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "parentNode");
}
function parentElement$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "parentElement");
}
function textContent$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "textContent");
}
function firstChild$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "firstChild");
}
function lastChild$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "lastChild");
}
function nextSibling$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "nextSibling");
}
function previousSibling$1(n2) {
  return getUntaintedAccessor$1("Node", n2, "previousSibling");
}
function contains$1(n2, other) {
  return getUntaintedMethod$1("Node", n2, "contains")(other);
}
function getRootNode$1(n2) {
  return getUntaintedMethod$1("Node", n2, "getRootNode")();
}
function host$1(n2) {
  if (!n2 || !("host" in n2)) return null;
  return getUntaintedAccessor$1("ShadowRoot", n2, "host");
}
function styleSheets$1(n2) {
  return n2.styleSheets;
}
function shadowRoot$1(n2) {
  if (!n2 || !("shadowRoot" in n2)) return null;
  return getUntaintedAccessor$1("Element", n2, "shadowRoot");
}
function querySelector$1(n2, selectors) {
  return getUntaintedAccessor$1("Element", n2, "querySelector")(selectors);
}
function querySelectorAll$1(n2, selectors) {
  return getUntaintedAccessor$1("Element", n2, "querySelectorAll")(selectors);
}
function mutationObserverCtor$1() {
  const prototype = getUntaintedPrototype$1("MutationObserver");
  mutationObserverUsers$1++;
  let stopCalled = false;
  return [prototype.constructor, () => {
    if (stopCalled) return;
    stopCalled = true;
    if (--mutationObserverUsers$1 !== 0) return;
    const cleanup = untaintedBaseIframeCleanup$1["MutationObserver"];
    if (!cleanup) return;
    try {
      cleanup();
    } finally {
      delete untaintedBasePrototype$1["MutationObserver"];
      delete untaintedBaseIframeCleanup$1["MutationObserver"];
    }
  }];
}
var nowTimestamp$1 = Date.now;
if (!/* @__PURE__ */ /[1-9][0-9]{12}/.test(Date.now().toString())) nowTimestamp$1 = () => (/* @__PURE__ */ new Date()).getTime();
function patch$1(source, name, replacement) {
  try {
    if (!(name in source)) return () => {
    };
    const original = source[name];
    const wrapped = replacement(original);
    if (typeof wrapped === "function") {
      wrapped.prototype = wrapped.prototype || {};
      Object.defineProperties(wrapped, { __rrweb_original__: {
        enumerable: false,
        value: original
      } });
    }
    source[name] = wrapped;
    return () => {
      source[name] = original;
    };
  } catch (e2) {
    return () => {
    };
  }
}
var src_default$1 = {
  ownerDocument: ownerDocument$1,
  childNodes: childNodes$1,
  parentNode: parentNode$1,
  parentElement: parentElement$1,
  textContent: textContent$1,
  firstChild: firstChild$1,
  lastChild: lastChild$1,
  nextSibling: nextSibling$1,
  previousSibling: previousSibling$1,
  contains: contains$1,
  getRootNode: getRootNode$1,
  host: host$1,
  styleSheets: styleSheets$1,
  shadowRoot: shadowRoot$1,
  querySelector: querySelector$1,
  querySelectorAll: querySelectorAll$1,
  nowTimestamp: nowTimestamp$1,
  mutationObserverCtor: mutationObserverCtor$1,
  patch: patch$1
};
var Mirror$1 = class {
  constructor() {
    __publicField(this, "idNodeMap", /* @__PURE__ */ new Map());
    __publicField(this, "nodeMetaMap", /* @__PURE__ */ new WeakMap());
  }
  getId(n2) {
    var _a2, _b;
    if (!n2) return -1;
    return (_b = (_a2 = this.getMeta(n2)) == null ? void 0 : _a2.id) != null ? _b : -1;
  }
  getNode(id) {
    return this.idNodeMap.get(id) || null;
  }
  getIds() {
    return Array.from(this.idNodeMap.keys());
  }
  getMeta(n2) {
    return this.nodeMetaMap.get(n2) || null;
  }
  removeNodeFromMap(n2) {
    const id = this.getId(n2);
    this.idNodeMap.delete(id);
    if (n2.childNodes) n2.childNodes.forEach((childNode) => this.removeNodeFromMap(childNode));
    const shadowRoot2 = src_default$1.shadowRoot(n2);
    if (shadowRoot2) this.removeNodeFromMap(shadowRoot2);
  }
  has(id) {
    return this.idNodeMap.has(id);
  }
  hasNode(node) {
    return this.nodeMetaMap.has(node);
  }
  add(n2, meta) {
    const id = meta.id;
    this.idNodeMap.set(id, n2);
    this.nodeMetaMap.set(n2, meta);
  }
  replace(id, n2) {
    const oldNode = this.getNode(id);
    if (oldNode) {
      const meta = this.nodeMetaMap.get(oldNode);
      if (meta) this.nodeMetaMap.set(n2, meta);
    }
    this.idNodeMap.set(id, n2);
  }
  reset() {
    this.idNodeMap = /* @__PURE__ */ new Map();
    this.nodeMetaMap = /* @__PURE__ */ new WeakMap();
  }
};
function createMirror$1() {
  return new Mirror$1();
}
var cssNormalizeBase = "(?:/\\*[^*]*\\*/)|[\\s;]";
new RegExp(`${cssNormalizeBase}|\\b(0)px?`, "g");
new RegExp(cssNormalizeBase, "g");
new RegExp(/(max|min)-device-(width|height)/.source, "g");
var require_picocolors_browser = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var x = String;
  var create = function() {
    return {
      isColorSupported: false,
      reset: x,
      bold: x,
      dim: x,
      italic: x,
      underline: x,
      inverse: x,
      hidden: x,
      strikethrough: x,
      black: x,
      red: x,
      green: x,
      yellow: x,
      blue: x,
      magenta: x,
      cyan: x,
      white: x,
      gray: x,
      bgBlack: x,
      bgRed: x,
      bgGreen: x,
      bgYellow: x,
      bgBlue: x,
      bgMagenta: x,
      bgCyan: x,
      bgWhite: x,
      blackBright: x,
      redBright: x,
      greenBright: x,
      yellowBright: x,
      blueBright: x,
      magentaBright: x,
      cyanBright: x,
      whiteBright: x,
      bgBlackBright: x,
      bgRedBright: x,
      bgGreenBright: x,
      bgYellowBright: x,
      bgBlueBright: x,
      bgMagentaBright: x,
      bgCyanBright: x,
      bgWhiteBright: x
    };
  };
  module2.exports = create();
  module2.exports.createColors = create;
});
var require___vite_browser_external = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  module2.exports = {};
});
var require_css_syntax_error = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var pico = require_picocolors_browser();
  var terminalHighlight = require___vite_browser_external();
  var CssSyntaxError = class CssSyntaxError2 extends Error {
    constructor(message, line, column, source, file, plugin) {
      super(message);
      this.name = "CssSyntaxError";
      this.reason = message;
      if (file) this.file = file;
      if (source) this.source = source;
      if (plugin) this.plugin = plugin;
      if (typeof line !== "undefined" && typeof column !== "undefined") {
        if (typeof line === "number") {
          this.line = line;
          this.column = column;
        } else {
          this.line = line.line;
          this.column = line.column;
          this.endLine = column.line;
          this.endColumn = column.column;
        }
      }
      this.setMessage();
      if (Error.captureStackTrace) Error.captureStackTrace(this, CssSyntaxError2);
    }
    setMessage() {
      this.message = this.plugin ? this.plugin + ": " : "";
      this.message += this.file ? this.file : "<css input>";
      if (typeof this.line !== "undefined") this.message += ":" + this.line + ":" + this.column;
      this.message += ": " + this.reason;
    }
    showSourceCode(color) {
      if (!this.source) return "";
      let css = this.source;
      if (color == null) color = pico.isColorSupported;
      let aside = (text) => text;
      let mark = (text) => text;
      let highlight = (text) => text;
      if (color) {
        let { bold, gray, red } = pico.createColors(true);
        mark = (text) => bold(red(text));
        aside = (text) => gray(text);
        if (terminalHighlight) highlight = (text) => terminalHighlight(text);
      }
      let lines = css.split(/\r?\n/);
      let start = Math.max(this.line - 3, 0);
      let end = Math.min(this.line + 2, lines.length);
      let maxWidth = String(end).length;
      return lines.slice(start, end).map((line, index) => {
        let number = start + 1 + index;
        let gutter = " " + (" " + number).slice(-maxWidth) + " | ";
        if (number === this.line) {
          if (line.length > 160) {
            let padding = 20;
            let subLineStart = Math.max(0, this.column - padding);
            let subLineEnd = Math.max(this.column + padding, this.endColumn + padding);
            let subLine = line.slice(subLineStart, subLineEnd);
            let spacing2 = aside(gutter.replace(/\d/g, " ")) + line.slice(0, Math.min(this.column - 1, 19)).replace(/[^\t]/g, " ");
            return mark(">") + aside(gutter) + highlight(subLine) + "\n " + spacing2 + mark("^");
          }
          let spacing = aside(gutter.replace(/\d/g, " ")) + line.slice(0, this.column - 1).replace(/[^\t]/g, " ");
          return mark(">") + aside(gutter) + highlight(line) + "\n " + spacing + mark("^");
        }
        return " " + aside(gutter) + highlight(line);
      }).join("\n");
    }
    toString() {
      let code = this.showSourceCode();
      if (code) code = "\n\n" + code + "\n";
      return this.name + ": " + this.message + code;
    }
  };
  module2.exports = CssSyntaxError;
  CssSyntaxError.default = CssSyntaxError;
});
var require_stringifier = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var AT_NAME_END = /[\t\n\f\r "#'()/;[\\\]{}]/;
  var DEFAULT_RAW = {
    after: "\n",
    beforeClose: "\n",
    beforeComment: "\n",
    beforeDecl: "\n",
    beforeOpen: " ",
    beforeRule: "\n",
    colon: ": ",
    commentLeft: " ",
    commentRight: " ",
    emptyBody: "",
    indent: "    ",
    semicolon: false
  };
  function capitalize(str) {
    return str[0].toUpperCase() + str.slice(1);
  }
  function atruleStart(str, node) {
    let name = "@" + node.name;
    let params = node.params ? str.rawValue(node, "params") : "";
    let afterName = node.raws.afterName;
    if (typeof afterName === "undefined") afterName = params ? " " : "";
    else if (afterName === "" && params && !AT_NAME_END.test(params[0])) afterName = " ";
    return name + afterName + params;
  }
  function isCustomProperty(node) {
    if (!node.prop.startsWith("--")) return false;
    let before = node.raws.before;
    return typeof before === "undefined" || !/[^\s;]$/.test(before);
  }
  function pushBody(str, stack, node) {
    let nodes = node.nodes;
    let last = nodes.length - 1;
    while (last > 0) {
      if (nodes[last].type !== "comment") break;
      last -= 1;
    }
    let semicolon = str.raw(node, "semicolon");
    let isDocument = node.type === "document";
    for (let i2 = nodes.length - 1; i2 >= 0; i2--) {
      let child = nodes[i2];
      let childSemicolon = last !== i2 || semicolon;
      if (!childSemicolon && i2 < nodes.length - 1 && (child.type === "atrule" && !child.nodes || child.type === "decl" && isCustomProperty(child))) childSemicolon = true;
      stack.push({
        document: isDocument,
        node: child,
        semicolon: childSemicolon
      });
    }
  }
  function pushBlock(str, stack, node, start) {
    let between = str.raw(node, "between", "beforeOpen");
    str.builder(start + between + "{", node, "start");
    let hasNodes = node.nodes && node.nodes.length;
    let close = () => {
      let after = hasNodes ? str.raw(node, "after") : str.raw(node, "after", "emptyBody");
      if (after) str.builder(after);
      str.builder("}", node, "end");
      if (node.type === "rule" && node.raws.ownSemicolon) str.builder(node.raws.ownSemicolon, node, "end");
    };
    if (hasNodes) {
      stack.push(close);
      pushBody(str, stack, node);
    } else close();
  }
  var Stringifier = class Stringifier2 {
    constructor(builder) {
      this.builder = builder;
    }
    atrule(node, semicolon) {
      let start = atruleStart(this, node);
      if (node.nodes) this.block(node, start);
      else {
        let end = (node.raws.between || "") + (semicolon ? ";" : "");
        this.builder(start + end, node);
      }
    }
    beforeAfter(node, detect) {
      let value;
      if (node.type === "decl") value = this.raw(node, null, "beforeDecl");
      else if (node.type === "comment") value = this.raw(node, null, "beforeComment");
      else if (detect === "before") value = this.raw(node, null, "beforeRule");
      else value = this.raw(node, null, "beforeClose");
      let buf = node.parent;
      let depth = 0;
      while (buf && buf.type !== "root") {
        depth += 1;
        buf = buf.parent;
      }
      if (value.includes("\n")) {
        let indent = this.raw(node, null, "indent");
        if (indent.length) for (let step = 0; step < depth; step++) value += indent;
      }
      return value;
    }
    block(node, start) {
      let between = this.raw(node, "between", "beforeOpen");
      this.builder(start + between + "{", node, "start");
      let after;
      if (node.nodes && node.nodes.length) {
        this.body(node);
        after = this.raw(node, "after");
      } else after = this.raw(node, "after", "emptyBody");
      if (after) this.builder(after);
      this.builder("}", node, "end");
    }
    body(node) {
      let proto = Stringifier2.prototype;
      let expandable = [
        "atrule",
        "block",
        "body",
        "rule",
        "stringify"
      ].every((method) => this[method] === proto[method]);
      let stack = [];
      pushBody(this, stack, node);
      while (stack.length > 0) {
        let entry = stack.pop();
        if (typeof entry === "function") {
          entry();
          continue;
        }
        let child = entry.node;
        let before = this.raw(child, "before");
        if (before) this.builder(before);
        if (expandable && child.type === "rule") pushBlock(this, stack, child, this.rawValue(child, "selector"));
        else if (expandable && child.type === "atrule" && child.nodes) pushBlock(this, stack, child, atruleStart(this, child));
        else this.stringify(child, entry.semicolon);
      }
    }
    comment(node) {
      let left = this.raw(node, "left", "commentLeft");
      let right = this.raw(node, "right", "commentRight");
      this.builder("/*" + left + node.text + right + "*/", node);
    }
    decl(node, semicolon) {
      let raws = node.raws;
      let between = this.raw(node, "between", "colon");
      let string = node.prop + between + this.rawValue(node, "value");
      if (node.important) string += raws.important || " !important";
      if (semicolon) string += ";";
      this.builder(string, node);
    }
    document(node) {
      this.body(node);
    }
    raw(node, own, detect) {
      let value;
      if (!detect) detect = own;
      if (own) {
        value = node.raws[own];
        if (typeof value !== "undefined") return value;
      }
      let parent = node.parent;
      if (detect === "before") {
        if (!parent || parent.type === "root" && parent.first === node) return "";
        if (parent && parent.type === "document") return "";
      }
      if (!parent) return DEFAULT_RAW[detect];
      let root = node.root();
      let cache = root.rawCache || (root.rawCache = {});
      if (typeof cache[detect] !== "undefined") return cache[detect];
      if (detect === "before" || detect === "after") return this.beforeAfter(node, detect);
      else {
        let method = "raw" + capitalize(detect);
        if (this[method]) value = this[method](root, node);
        else root.walk((i2) => {
          value = i2.raws[own];
          if (typeof value !== "undefined") return false;
        });
      }
      if (typeof value === "undefined") value = DEFAULT_RAW[detect];
      cache[detect] = value;
      return value;
    }
    rawBeforeClose(root) {
      let value;
      root.walk((i2) => {
        if (i2.nodes && i2.nodes.length > 0) {
          if (typeof i2.raws.after !== "undefined") {
            value = i2.raws.after;
            if (value.includes("\n")) value = value.replace(/[^\n]+$/, "");
            return false;
          }
        }
      });
      if (value) value = value.replace(/\S/g, "");
      return value;
    }
    rawBeforeComment(root, node) {
      let value;
      root.walkComments((i2) => {
        if (typeof i2.raws.before !== "undefined") {
          value = i2.raws.before;
          if (value.includes("\n")) value = value.replace(/[^\n]+$/, "");
          return false;
        }
      });
      if (typeof value === "undefined") value = this.raw(node, null, "beforeDecl");
      else if (value) value = value.replace(/\S/g, "");
      return value;
    }
    rawBeforeDecl(root, node) {
      let value;
      root.walkDecls((i2) => {
        if (typeof i2.raws.before !== "undefined") {
          value = i2.raws.before;
          if (value.includes("\n")) value = value.replace(/[^\n]+$/, "");
          return false;
        }
      });
      if (typeof value === "undefined") value = this.raw(node, null, "beforeRule");
      else if (value) value = value.replace(/\S/g, "");
      return value;
    }
    rawBeforeOpen(root) {
      let value;
      root.walk((i2) => {
        if (i2.type !== "decl") {
          value = i2.raws.between;
          if (typeof value !== "undefined") return false;
        }
      });
      return value;
    }
    rawBeforeRule(root) {
      let value;
      root.walk((i2) => {
        if (i2.nodes && (i2.parent !== root || root.first !== i2)) {
          if (typeof i2.raws.before !== "undefined") {
            value = i2.raws.before;
            if (value.includes("\n")) value = value.replace(/[^\n]+$/, "");
            return false;
          }
        }
      });
      if (value) value = value.replace(/\S/g, "");
      return value;
    }
    rawColon(root) {
      let value;
      root.walkDecls((i2) => {
        if (typeof i2.raws.between !== "undefined") {
          value = i2.raws.between.replace(/[^\s:]/g, "");
          return false;
        }
      });
      return value;
    }
    rawEmptyBody(root) {
      let value;
      root.walk((i2) => {
        if (i2.nodes && i2.nodes.length === 0) {
          value = i2.raws.after;
          if (typeof value !== "undefined") return false;
        }
      });
      return value;
    }
    rawIndent(root) {
      if (root.raws.indent) return root.raws.indent;
      let value;
      root.walk((i2) => {
        let p = i2.parent;
        if (p && p !== root && p.parent && p.parent === root) {
          if (typeof i2.raws.before !== "undefined") {
            let parts = i2.raws.before.split("\n");
            value = parts[parts.length - 1];
            value = value.replace(/\S/g, "");
            return false;
          }
        }
      });
      return value;
    }
    rawSemicolon(root) {
      let value;
      root.walk((i2) => {
        if (i2.nodes && i2.nodes.length && i2.last.type === "decl") {
          value = i2.raws.semicolon;
          if (typeof value !== "undefined") return false;
        }
      });
      return value;
    }
    rawValue(node, prop) {
      let value = node[prop];
      let raw = node.raws[prop];
      if (raw && raw.value === value) return raw.raw;
      return value;
    }
    root(node) {
      if (!(node.parent && node.parent.type === "document") && node.source && node.source.input.hasBOM) this.builder("\uFEFF", node, "start");
      this.body(node);
      if (node.raws.after) this.builder(node.raws.after);
    }
    rule(node) {
      this.block(node, this.rawValue(node, "selector"));
      if (node.raws.ownSemicolon) this.builder(node.raws.ownSemicolon, node, "end");
    }
    stringify(node, semicolon) {
      if (!this[node.type]) throw new Error("Unknown AST node type " + node.type + ". Maybe you need to change PostCSS stringifier.");
      this[node.type](node, semicolon);
    }
  };
  module2.exports = Stringifier;
  Stringifier.default = Stringifier;
});
var require_stringify = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Stringifier = require_stringifier();
  var STYLE_TAG = /(<)(\/?style\b)/gi;
  var COMMENT_OPEN = /(<)(!--)/g;
  function escapeHTMLInCSS(str) {
    if (!str.includes("<")) return str;
    return str.replace(STYLE_TAG, "\\3c $2").replace(COMMENT_OPEN, "\\3c $2");
  }
  function cutEscaped(chunks, css, escaped) {
    let parts = [];
    let at = 0;
    let into = 0;
    for (let i2 = 0; i2 < chunks.length; i2 += 3) {
      let from = into;
      let end = at + chunks[i2].length;
      while (at < end) if (css[at] === escaped[into]) {
        at += 1;
        into += 1;
      } else {
        at += 1;
        into += 4;
      }
      parts.push(escaped.slice(from, into));
    }
    return parts;
  }
  var SafeStringifier = class extends Stringifier {
    escapeHTML(print) {
      let builder = this.builder;
      let chunks, css;
      this.builder = (str, child, type) => {
        if (chunks) {
          css += str;
          chunks.push(str, child, type);
        } else if (str.includes("<")) {
          css = str;
          chunks = [
            str,
            child,
            type
          ];
        } else builder(str, child, type);
      };
      print();
      this.builder = builder;
      if (!chunks) return;
      let escaped = escapeHTMLInCSS(css);
      if (escaped === css) for (let i2 = 0; i2 < chunks.length; i2 += 3) builder(chunks[i2], chunks[i2 + 1], chunks[i2 + 2]);
      else {
        let parts = cutEscaped(chunks, css, escaped);
        for (let i2 = 0; i2 < chunks.length; i2 += 3) builder(parts[i2 / 3], chunks[i2 + 1], chunks[i2 + 2]);
      }
    }
    root(node) {
      if (node.parent && node.parent.type === "document") {
        this.escapeHTML(() => this.body(node));
        if (node.raws.after) this.builder(node.raws.after);
      } else this.escapeHTML(() => super.root(node));
    }
  };
  function stringify(node, builder) {
    let str = new SafeStringifier(builder);
    if (node.type === "root" || node.type === "document") str.stringify(node);
    else str.escapeHTML(() => str.stringify(node));
  }
  module2.exports = stringify;
  stringify.default = stringify;
});
var require_symbols = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  module2.exports.isClean = Symbol("isClean");
  module2.exports.my = Symbol("my");
});
var require_node = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var CssSyntaxError = require_css_syntax_error();
  var Stringifier = require_stringifier();
  var stringify = require_stringify();
  var { isClean, my } = require_symbols();
  function cloneNode(obj, parent) {
    let cloned = new obj.constructor();
    let stack = [[
      obj,
      cloned,
      parent
    ]];
    while (stack.length > 0) {
      let [source, target, targetParent] = stack.pop();
      for (let i2 in source) {
        if (!Object.prototype.hasOwnProperty.call(source, i2))
          continue;
        if (i2 === "proxyCache") continue;
        let value = source[i2];
        let type = typeof value;
        if (i2 === "parent" && type === "object") {
          if (targetParent) target[i2] = targetParent;
        } else if (i2 === "source") target[i2] = value;
        else if (Array.isArray(value)) {
          let children = [];
          target[i2] = children;
          for (let j of value) {
            let childClone = new j.constructor();
            children.push(childClone);
            stack.push([
              j,
              childClone,
              target
            ]);
          }
        } else {
          if (type === "object" && value !== null) {
            let valueClone = new value.constructor();
            stack.push([
              value,
              valueClone,
              void 0
            ]);
            value = valueClone;
          }
          target[i2] = value;
        }
      }
    }
    return cloned;
  }
  function sourceOffset(inputCSS, position) {
    if (position && typeof position.offset !== "undefined") return position.offset;
    let column = 1;
    let line = 1;
    let offset = 0;
    for (let i2 = 0; i2 < inputCSS.length; i2++) {
      if (line === position.line && column === position.column) {
        offset = i2;
        break;
      }
      if (inputCSS[i2] === "\n") {
        column = 1;
        line += 1;
      } else column += 1;
    }
    return offset;
  }
  var Node2 = class Node3 {
    get proxyOf() {
      return this;
    }
    constructor(defaults = {}) {
      this.raws = {};
      this[isClean] = false;
      this[my] = true;
      for (let name of Object.keys(defaults)) {
        if (name === "__proto__") continue;
        if (name === "nodes") {
          this.nodes = [];
          for (let node of defaults[name]) if (typeof node.clone === "function" && node.parent) this.append(node.clone());
          else this.append(node);
        } else this[name] = defaults[name];
      }
    }
    addToError(error) {
      error.postcssNode = this;
      if (error.stack && this.source && /\n\s{4}at /.test(error.stack)) {
        let s2 = this.source;
        error.stack = error.stack.replace(/\n\s{4}at /, `$&${s2.input.from}:${s2.start.line}:${s2.start.column}$&`);
      }
      return error;
    }
    after(add) {
      this.parent.insertAfter(this, add);
      return this;
    }
    assign(overrides = {}) {
      for (let name in overrides) this[name] = overrides[name];
      return this;
    }
    before(add) {
      this.parent.insertBefore(this, add);
      return this;
    }
    cleanRaws(keepBetween) {
      delete this.raws.before;
      delete this.raws.after;
      if (!keepBetween) delete this.raws.between;
    }
    clone(overrides = {}) {
      let cloned = cloneNode(this);
      for (let name in overrides) cloned[name] = overrides[name];
      return cloned;
    }
    cloneAfter(overrides = {}) {
      let cloned = this.clone(overrides);
      this.parent.insertAfter(this, cloned);
      return cloned;
    }
    cloneBefore(overrides = {}) {
      let cloned = this.clone(overrides);
      this.parent.insertBefore(this, cloned);
      return cloned;
    }
    error(message, opts = {}) {
      if (this.source) {
        let { end, start } = this.rangeBy(opts);
        return this.source.input.error(message, {
          column: start.column,
          line: start.line
        }, {
          column: end.column,
          line: end.line
        }, opts);
      }
      return new CssSyntaxError(message);
    }
    getProxyProcessor() {
      return {
        get(node, prop) {
          if (prop === "proxyOf") return node;
          else if (prop === "root") return () => node.root().toProxy();
          else return node[prop];
        },
        set(node, prop, value) {
          if (node[prop] === value) return true;
          node[prop] = value;
          if (prop === "prop" || prop === "value" || prop === "name" || prop === "params" || prop === "important" || /* c8 ignore next */
          prop === "text") node.markDirty();
          return true;
        }
      };
    }
    /* c8 ignore next 3 */
    markClean() {
      this[isClean] = true;
    }
    markDirty() {
      if (this[isClean]) {
        this[isClean] = false;
        let next = this;
        while (next = next.parent) next[isClean] = false;
      }
    }
    next() {
      if (!this.parent) return void 0;
      let index = this.parent.index(this);
      return this.parent.nodes[index + 1];
    }
    positionBy(opts = {}) {
      let inputString = "document" in this.source.input ? this.source.input.document : this.source.input.css;
      let pos = {
        column: this.source.start.column,
        line: this.source.start.line,
        offset: sourceOffset(inputString, this.source.start)
      };
      if (opts.index) pos = this.positionInside(opts.index);
      else if (opts.word) {
        let index = inputString.slice(sourceOffset(inputString, this.source.start), this.rangeBy().end.offset).indexOf(opts.word);
        if (index !== -1) pos = this.positionInside(index);
      }
      return pos;
    }
    positionInside(index) {
      let column = this.source.start.column;
      let line = this.source.start.line;
      let inputString = "document" in this.source.input ? this.source.input.document : this.source.input.css;
      let offset = sourceOffset(inputString, this.source.start);
      let end = offset + index;
      for (let i2 = offset; i2 < end; i2++) if (inputString[i2] === "\n") {
        column = 1;
        line += 1;
      } else column += 1;
      return {
        column,
        line,
        offset: end
      };
    }
    prev() {
      if (!this.parent) return void 0;
      let index = this.parent.index(this);
      return this.parent.nodes[index - 1];
    }
    rangeBy(opts = {}) {
      let inputString = "document" in this.source.input ? this.source.input.document : this.source.input.css;
      let start = {
        column: this.source.start.column,
        line: this.source.start.line,
        offset: sourceOffset(inputString, this.source.start)
      };
      let end = this.source.end ? {
        column: this.source.end.column + 1,
        line: this.source.end.line,
        offset: typeof this.source.end.offset === "number" ? this.source.end.offset : sourceOffset(inputString, this.source.end) + 1
      } : {
        column: start.column + 1,
        line: start.line,
        offset: start.offset + 1
      };
      if (opts.word) {
        let index = inputString.slice(sourceOffset(inputString, this.source.start), end.offset).indexOf(opts.word);
        if (index !== -1) {
          start = this.positionInside(index);
          end = this.positionInside(index + opts.word.length);
        }
      } else {
        if (opts.start) start = {
          column: opts.start.column,
          line: opts.start.line,
          offset: sourceOffset(inputString, opts.start)
        };
        else if (typeof opts.index === "number") start = this.positionInside(opts.index);
        if (opts.end) end = {
          column: opts.end.column,
          line: opts.end.line,
          offset: sourceOffset(inputString, opts.end)
        };
        else if (typeof opts.endIndex === "number") end = this.positionInside(opts.endIndex);
        else if (typeof opts.index === "number") end = this.positionInside(opts.index + 1);
      }
      if (end.line < start.line || end.line === start.line && end.column <= start.column) end = {
        column: start.column + 1,
        line: start.line,
        offset: start.offset + 1
      };
      return {
        end,
        start
      };
    }
    raw(prop, defaultType) {
      return new Stringifier().raw(this, prop, defaultType);
    }
    remove() {
      if (this.parent) this.parent.removeChild(this);
      this.parent = void 0;
      return this;
    }
    replaceWith(...nodes) {
      if (this.parent) {
        let bookmark = this;
        let foundSelf = false;
        for (let node of nodes) if (node === this) foundSelf = true;
        else if (foundSelf) {
          this.parent.insertAfter(bookmark, node);
          bookmark = node;
        } else this.parent.insertBefore(bookmark, node);
        if (!foundSelf) this.remove();
      }
      return this;
    }
    root() {
      let result = this;
      while (result.parent && result.parent.type !== "document") result = result.parent;
      return result;
    }
    toJSON(_, inputs) {
      let emitInputs = inputs == null;
      inputs = inputs || /* @__PURE__ */ new Map();
      let holderOfRoot = [];
      let queue = [[
        this,
        holderOfRoot,
        0
      ]];
      for (let step = 0; step < queue.length; step++) {
        let [node, holder, key] = queue[step];
        let fixed2 = {};
        holder[key] = fixed2;
        for (let name in node) {
          if (!Object.prototype.hasOwnProperty.call(node, name))
            continue;
          if (name === "parent" || name === "proxyCache") continue;
          let value = node[name];
          if (Array.isArray(value)) {
            let fixedArray = [];
            fixed2[name] = fixedArray;
            for (let i2 = 0; i2 < value.length; i2++) {
              let item = value[i2];
              if (typeof item === "object" && item.toJSON) {
                if (item.toJSON === Node3.prototype.toJSON) queue.push([
                  item,
                  fixedArray,
                  i2
                ]);
                else fixedArray[i2] = item.toJSON(null, inputs);
              } else fixedArray[i2] = item;
            }
          } else if (typeof value === "object" && value.toJSON) {
            if (value.toJSON === Node3.prototype.toJSON) queue.push([
              value,
              fixed2,
              name
            ]);
            else fixed2[name] = value.toJSON(null, inputs);
          } else if (name === "source") {
            if (value == null) continue;
            let inputId = inputs.get(value.input);
            if (inputId == null) {
              inputId = inputs.size;
              inputs.set(value.input, inputId);
            }
            fixed2[name] = {
              end: value.end,
              inputId,
              start: value.start
            };
          } else fixed2[name] = value;
        }
      }
      let fixed = holderOfRoot[0];
      if (emitInputs) fixed.inputs = [...inputs.keys()].map((input) => input.toJSON());
      return fixed;
    }
    toProxy() {
      if (!this.proxyCache) this.proxyCache = new Proxy(this, this.getProxyProcessor());
      return this.proxyCache;
    }
    toString(stringifier = stringify) {
      if (stringifier.stringify) stringifier = stringifier.stringify;
      let result = "";
      stringifier(this, (i2) => {
        result += i2;
      });
      return result;
    }
    warn(result, text, opts = {}) {
      let data = { node: this };
      for (let i2 in opts) data[i2] = opts[i2];
      return result.warn(text, data);
    }
  };
  module2.exports = Node2;
  Node2.default = Node2;
});
var require_comment = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Node2 = require_node();
  var Comment = class extends Node2 {
    constructor(defaults) {
      super(defaults);
      this.type = "comment";
    }
  };
  module2.exports = Comment;
  Comment.default = Comment;
});
var require_declaration = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Node2 = require_node();
  var Declaration = class extends Node2 {
    get variable() {
      return this.prop.startsWith("--") || this.prop[0] === "$";
    }
    constructor(defaults) {
      if (defaults && typeof defaults.value !== "undefined" && typeof defaults.value !== "string") defaults = __spreadProps(__spreadValues({}, defaults), {
        value: String(defaults.value)
      });
      super(defaults);
      this.type = "decl";
    }
  };
  module2.exports = Declaration;
  Declaration.default = Declaration;
});
var require_container = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Comment = require_comment();
  var Declaration = require_declaration();
  var Node2 = require_node();
  var { isClean, my } = require_symbols();
  var AtRule;
  var parse;
  var Root;
  var Rule;
  function cleanSource(nodes) {
    let stack = nodes.slice();
    while (stack.length > 0) {
      let node = stack.pop();
      delete node.source;
      if (node.nodes) {
        node.nodes = node.nodes.slice();
        for (let i2 of node.nodes) stack.push(i2);
      }
    }
    return nodes.slice();
  }
  function markTreeDirty(node) {
    let stack = [node];
    while (stack.length > 0) {
      let next = stack.pop();
      next[isClean] = false;
      if (next.proxyOf.nodes) for (let i2 of next.proxyOf.nodes) stack.push(i2);
    }
  }
  var Container = class Container2 extends Node2 {
    get first() {
      if (!this.proxyOf.nodes) return void 0;
      return this.proxyOf.nodes[0];
    }
    get last() {
      if (!this.proxyOf.nodes) return void 0;
      return this.proxyOf.nodes[this.proxyOf.nodes.length - 1];
    }
    append(...children) {
      for (let child of children) {
        let nodes = this.normalize(child, this.last);
        for (let node of nodes) this.proxyOf.nodes.push(node);
      }
      this.markDirty();
      return this;
    }
    cleanRaws(keepBetween) {
      let stack = [this];
      while (stack.length > 0) {
        let node = stack.pop();
        if (node !== this && node.cleanRaws !== Container2.prototype.cleanRaws) {
          node.cleanRaws(keepBetween);
          continue;
        }
        Node2.prototype.cleanRaws.call(node, keepBetween);
        if (node.nodes) for (let child of node.nodes) stack.push(child);
      }
    }
    each(callback) {
      if (!this.proxyOf.nodes) return void 0;
      let iterator = this.getIterator();
      let index, result;
      while (this.indexes[iterator] < this.proxyOf.nodes.length) {
        index = this.indexes[iterator];
        result = callback(this.proxyOf.nodes[index], index);
        if (result === false) break;
        this.indexes[iterator] += 1;
      }
      delete this.indexes[iterator];
      return result;
    }
    every(condition) {
      return this.nodes.every(condition);
    }
    getIterator() {
      if (!this.lastEach) this.lastEach = 0;
      if (!this.indexes) this.indexes = {};
      this.lastEach += 1;
      let iterator = this.lastEach;
      this.indexes[iterator] = 0;
      return iterator;
    }
    getProxyProcessor() {
      return {
        get(node, prop) {
          if (prop === "proxyOf") return node;
          else if (!node[prop]) return node[prop];
          else if (prop === "each" || typeof prop === "string" && prop.startsWith("walk")) return (...args) => {
            return node[prop](...args.map((i2) => {
              if (typeof i2 === "function") return (child, index) => i2(child.toProxy(), index);
              else return i2;
            }));
          };
          else if (prop === "every" || prop === "some") return (cb) => {
            return node[prop]((child, ...other) => cb(child.toProxy(), ...other));
          };
          else if (prop === "root") return () => node.root().toProxy();
          else if (prop === "nodes") return node.nodes.map((i2) => i2.toProxy());
          else if (prop === "first" || prop === "last") return node[prop].toProxy();
          else return node[prop];
        },
        set(node, prop, value) {
          if (node[prop] === value) return true;
          node[prop] = value;
          if (prop === "name" || prop === "params" || prop === "selector") node.markDirty();
          return true;
        }
      };
    }
    index(child) {
      if (typeof child === "number") return child;
      if (child.proxyOf) child = child.proxyOf;
      return this.proxyOf.nodes.indexOf(child);
    }
    insertAfter(exist, add) {
      let existIndex = this.index(exist);
      let nodes = this.normalize(add, this.proxyOf.nodes[existIndex]).reverse();
      existIndex = this.index(exist);
      for (let node of nodes) this.proxyOf.nodes.splice(existIndex + 1, 0, node);
      let index;
      for (let id in this.indexes) {
        index = this.indexes[id];
        if (existIndex < index) this.indexes[id] = index + nodes.length;
      }
      this.markDirty();
      return this;
    }
    insertBefore(exist, add) {
      let existIndex = this.index(exist);
      let type = existIndex === 0 ? "prepend" : false;
      let nodes = this.normalize(add, this.proxyOf.nodes[existIndex], type).reverse();
      existIndex = this.index(exist);
      for (let node of nodes) this.proxyOf.nodes.splice(existIndex, 0, node);
      let index;
      for (let id in this.indexes) {
        index = this.indexes[id];
        if (existIndex <= index) this.indexes[id] = index + nodes.length;
      }
      this.markDirty();
      return this;
    }
    normalize(nodes, sample) {
      if (typeof nodes === "string") nodes = cleanSource(parse(nodes).nodes);
      else if (typeof nodes === "undefined") nodes = [];
      else if (Array.isArray(nodes)) {
        nodes = nodes.slice(0);
        for (let i2 of nodes) if (i2.parent) i2.parent.removeChild(i2, "ignore");
      } else if (nodes.type === "root" && this.type !== "document") {
        nodes = nodes.nodes.slice(0);
        for (let i2 of nodes) if (i2.parent) i2.parent.removeChild(i2, "ignore");
      } else if (nodes.type) nodes = [nodes];
      else if (nodes.prop) {
        if (typeof nodes.value === "undefined") throw new Error("Value field is missed in node creation");
        else if (typeof nodes.value !== "string") nodes.value = String(nodes.value);
        nodes = [new Declaration(nodes)];
      } else if (nodes.selector || nodes.selectors) nodes = [new Rule(nodes)];
      else if (nodes.name) nodes = [new AtRule(nodes)];
      else if (nodes.text) nodes = [new Comment(nodes)];
      else throw new Error("Unknown node type in node creation");
      return nodes.map((i2) => {
        if (!i2[my]) Container2.rebuild(i2);
        i2 = i2.proxyOf;
        if (i2.parent) i2.parent.removeChild(i2);
        if (i2[isClean]) markTreeDirty(i2);
        if (!i2.raws) i2.raws = {};
        if (typeof i2.raws.before === "undefined") {
          if (sample && typeof sample.raws.before !== "undefined") i2.raws.before = sample.raws.before.replace(/\S/g, "");
        }
        i2.parent = this.proxyOf;
        return i2;
      });
    }
    prepend(...children) {
      children = children.reverse();
      for (let child of children) {
        let nodes = this.normalize(child, this.first, "prepend").reverse();
        for (let node of nodes) this.proxyOf.nodes.unshift(node);
        for (let id in this.indexes) this.indexes[id] = this.indexes[id] + nodes.length;
      }
      this.markDirty();
      return this;
    }
    push(child) {
      child.parent = this;
      this.proxyOf.nodes.push(child);
      return this;
    }
    removeAll() {
      for (let node of this.proxyOf.nodes) node.parent = void 0;
      this.proxyOf.nodes = [];
      this.markDirty();
      return this;
    }
    removeChild(child) {
      child = this.index(child);
      this.proxyOf.nodes[child].parent = void 0;
      this.proxyOf.nodes.splice(child, 1);
      let index;
      for (let id in this.indexes) {
        index = this.indexes[id];
        if (index >= child) this.indexes[id] = index - 1;
      }
      this.markDirty();
      return this;
    }
    replaceValues(pattern, opts, callback) {
      if (!callback) {
        callback = opts;
        opts = {};
      }
      this.walkDecls((decl) => {
        if (opts.props && !opts.props.includes(decl.prop)) return;
        if (opts.fast && !decl.value.includes(opts.fast)) return;
        decl.value = decl.value.replace(pattern, callback);
      });
      this.markDirty();
      return this;
    }
    some(condition) {
      return this.nodes.some(condition);
    }
    walk(callback) {
      if (!this.proxyOf.nodes) return void 0;
      let stack = [{
        iterator: this.getIterator(),
        node: this.proxyOf
      }];
      while (stack.length > 0) {
        let { iterator, node } = stack[stack.length - 1];
        let index = node.indexes[iterator];
        if (index >= node.proxyOf.nodes.length) {
          delete node.indexes[iterator];
          stack.pop();
          let parent = stack[stack.length - 1];
          if (parent) parent.node.indexes[parent.iterator] += 1;
          continue;
        }
        let child = node.proxyOf.nodes[index];
        let result;
        try {
          result = callback(child, index);
        } catch (e2) {
          throw child.addToError(e2);
        }
        if (result === false) {
          for (let opened of stack) delete opened.node.indexes[opened.iterator];
          return false;
        }
        if (child.walk && child.proxyOf.nodes) stack.push({
          iterator: child.getIterator(),
          node: child
        });
        else node.indexes[iterator] += 1;
      }
    }
    walkAtRules(name, callback) {
      if (!callback) {
        callback = name;
        return this.walk((child, i2) => {
          if (child.type === "atrule") return callback(child, i2);
        });
      }
      if (name instanceof RegExp) return this.walk((child, i2) => {
        if (child.type === "atrule" && name.test(child.name)) return callback(child, i2);
      });
      return this.walk((child, i2) => {
        if (child.type === "atrule" && child.name === name) return callback(child, i2);
      });
    }
    walkComments(callback) {
      return this.walk((child, i2) => {
        if (child.type === "comment") return callback(child, i2);
      });
    }
    walkDecls(prop, callback) {
      if (!callback) {
        callback = prop;
        return this.walk((child, i2) => {
          if (child.type === "decl") return callback(child, i2);
        });
      }
      if (prop instanceof RegExp) return this.walk((child, i2) => {
        if (child.type === "decl" && prop.test(child.prop)) return callback(child, i2);
      });
      return this.walk((child, i2) => {
        if (child.type === "decl" && child.prop === prop) return callback(child, i2);
      });
    }
    walkRules(selector, callback) {
      if (!callback) {
        callback = selector;
        return this.walk((child, i2) => {
          if (child.type === "rule") return callback(child, i2);
        });
      }
      if (selector instanceof RegExp) return this.walk((child, i2) => {
        if (child.type === "rule" && selector.test(child.selector)) return callback(child, i2);
      });
      return this.walk((child, i2) => {
        if (child.type === "rule" && child.selector === selector) return callback(child, i2);
      });
    }
  };
  Container.registerParse = (dependant) => {
    parse = dependant;
  };
  Container.registerRule = (dependant) => {
    Rule = dependant;
  };
  Container.registerAtRule = (dependant) => {
    AtRule = dependant;
  };
  Container.registerRoot = (dependant) => {
    Root = dependant;
  };
  module2.exports = Container;
  Container.default = Container;
  Container.rebuild = (node) => {
    let stack = [node];
    while (stack.length > 0) {
      let next = stack.pop();
      if (next.type === "atrule") Object.setPrototypeOf(next, AtRule.prototype);
      else if (next.type === "rule") Object.setPrototypeOf(next, Rule.prototype);
      else if (next.type === "decl") Object.setPrototypeOf(next, Declaration.prototype);
      else if (next.type === "comment") Object.setPrototypeOf(next, Comment.prototype);
      else if (next.type === "root") Object.setPrototypeOf(next, Root.prototype);
      next[my] = true;
      if (next.nodes) for (let child of next.nodes) stack.push(child);
    }
  };
});
var require_at_rule = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Container = require_container();
  var AtRule = class extends Container {
    constructor(defaults) {
      super(defaults);
      this.type = "atrule";
    }
    append(...children) {
      if (!this.proxyOf.nodes) this.nodes = [];
      return super.append(...children);
    }
    prepend(...children) {
      if (!this.proxyOf.nodes) this.nodes = [];
      return super.prepend(...children);
    }
  };
  module2.exports = AtRule;
  AtRule.default = AtRule;
  Container.registerAtRule(AtRule);
});
var require_document = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Container = require_container();
  var LazyResult;
  var Processor;
  var Document2 = class extends Container {
    constructor(defaults) {
      super(__spreadValues({
        type: "document"
      }, defaults));
      if (!this.nodes) this.nodes = [];
    }
    toResult(opts = {}) {
      return new LazyResult(new Processor(), this, opts).stringify();
    }
  };
  Document2.registerLazyResult = (dependant) => {
    LazyResult = dependant;
  };
  Document2.registerProcessor = (dependant) => {
    Processor = dependant;
  };
  module2.exports = Document2;
  Document2.default = Document2;
});
var require_non_secure = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var urlAlphabet = "useandom-26T198340PX75pxJACKVERYMINDBUSHWOLF_GQZbfghjklqvwyzrict";
  var customAlphabet = (alphabet, defaultSize = 21) => {
    return (size = defaultSize) => {
      let id = "";
      let i2 = size | 0;
      while (i2-- > 0) id += alphabet[Math.random() * alphabet.length | 0];
      return id;
    };
  };
  var nanoid = (size = 21) => {
    let id = "";
    let i2 = size | 0;
    while (i2-- > 0) id += urlAlphabet[Math.random() * 64 | 0];
    return id;
  };
  module2.exports = {
    nanoid,
    customAlphabet
  };
});
var require_previous_map = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var { existsSync, readFileSync, realpathSync } = require___vite_browser_external();
  var { dirname, isAbsolute, join, relative, sep } = require___vite_browser_external();
  var { SourceMapConsumer, SourceMapGenerator } = require___vite_browser_external();
  function realPath(path) {
    try {
      return realpathSync(path);
    } catch (e2) {
      return path;
    }
  }
  function fromBase64(str) {
    if (Buffer) return Buffer.from(str, "base64").toString();
    else
      return window.atob(str);
  }
  var PreviousMap = class {
    constructor(css, opts) {
      if (opts.map === false) return;
      if (opts.unsafeMap) this.unsafeMap = true;
      this.loadAnnotation(css);
      this.inline = this.startWith(this.annotation, "data:");
      let prev = opts.map ? opts.map.prev : void 0;
      let text = this.loadMap(opts.from, prev);
      if (!this.mapFile && opts.from) this.mapFile = opts.from;
      if (this.mapFile) this.root = dirname(this.mapFile);
      if (text) this.text = text;
    }
    consumer() {
      if (!this.consumerCache) this.consumerCache = new SourceMapConsumer(this.json || this.text);
      return this.consumerCache;
    }
    decodeInline(text) {
      let baseCharsetUri = /^data:application\/json;charset=utf-?8;base64,/;
      let baseUri = /^data:application\/json;base64,/;
      let uriMatch = text.match(/^data:application\/json;charset=utf-?8,/) || text.match(/^data:application\/json,/);
      if (uriMatch) return decodeURIComponent(text.substr(uriMatch[0].length));
      let baseUriMatch = text.match(baseCharsetUri) || text.match(baseUri);
      if (baseUriMatch) return fromBase64(text.substr(baseUriMatch[0].length));
      let encoding = text.slice(22);
      encoding = encoding.slice(0, encoding.indexOf(","));
      throw new Error("Unsupported source map encoding " + encoding);
    }
    getAnnotationURL(sourceMapString) {
      return sourceMapString.replace(/^\/\*\s*# sourceMappingURL=/, "").trim();
    }
    isMap(map) {
      if (typeof map !== "object") return false;
      return typeof map.mappings === "string" || typeof map._mappings === "string" || Array.isArray(map.sections);
    }
    loadAnnotation(css) {
      let comments = css.match(/\/\*\s*# sourceMappingURL=/g);
      if (!comments) return;
      let start = css.lastIndexOf(comments.pop());
      let end = css.indexOf("*/", start);
      if (start > -1 && end > -1) this.annotation = this.getAnnotationURL(css.substring(start, end));
    }
    loadFile(path, cssFile, trusted) {
      if (!trusted && !this.unsafeMap) {
        if (!/\.map$/i.test(path)) return void 0;
        if (!cssFile) return void 0;
        let rel = relative(realPath(dirname(cssFile)), realPath(path));
        if (rel === ".." || rel.startsWith(".." + sep) || isAbsolute(rel)) return;
      }
      this.root = dirname(path);
      if (existsSync(path)) {
        this.mapFile = path;
        return readFileSync(path, "utf-8").toString().trim();
      }
    }
    loadMap(file, prev) {
      if (prev === false) return false;
      if (prev) {
        if (typeof prev === "string") return prev;
        else if (typeof prev === "function") {
          let prevPath = prev(file);
          if (prevPath) {
            let map = this.loadFile(prevPath, file, true);
            if (!map) throw new Error("Unable to load previous source map: " + prevPath.toString());
            return map;
          }
        } else if (prev instanceof SourceMapConsumer) return SourceMapGenerator.fromSourceMap(prev).toString();
        else if (prev instanceof SourceMapGenerator) return prev.toString();
        else if (this.isMap(prev)) return JSON.stringify(prev);
        else throw new Error("Unsupported previous source map format: " + prev.toString());
      } else if (this.inline) return this.decodeInline(this.annotation);
      else if (this.annotation) {
        let map = this.annotation;
        if (file) map = join(dirname(file), map);
        let unknown = this.loadFile(map, file, false);
        if (unknown) try {
          this.json = JSON.parse(unknown.replace(/^\)]}'[^\n]*\n/, ""));
        } catch (e2) {
          return;
        }
        return unknown;
      }
    }
    startWith(string, start) {
      if (!string) return false;
      return string.substr(0, start.length) === start;
    }
    withContent() {
      return !!(this.consumer().sourcesContent && this.consumer().sourcesContent.length > 0);
    }
  };
  module2.exports = PreviousMap;
  PreviousMap.default = PreviousMap;
});
var require_input = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var { nanoid } = require_non_secure();
  var { isAbsolute, resolve } = require___vite_browser_external();
  var { SourceMapConsumer, SourceMapGenerator } = require___vite_browser_external();
  var { fileURLToPath, pathToFileURL } = require___vite_browser_external();
  var CssSyntaxError = require_css_syntax_error();
  var PreviousMap = require_previous_map();
  var terminalHighlight = require___vite_browser_external();
  var lineToIndexCache = Symbol("lineToIndexCache");
  var sourceMapAvailable = Boolean(SourceMapConsumer && SourceMapGenerator);
  var pathAvailable = Boolean(resolve && isAbsolute);
  function getLineToIndex(input) {
    if (input[lineToIndexCache]) return input[lineToIndexCache];
    let lines = input.css.split("\n");
    let lineToIndex = new Array(lines.length);
    let prevIndex = 0;
    for (let i2 = 0, l2 = lines.length; i2 < l2; i2++) {
      lineToIndex[i2] = prevIndex;
      prevIndex += lines[i2].length + 1;
    }
    input[lineToIndexCache] = lineToIndex;
    return lineToIndex;
  }
  var Input = class {
    get from() {
      return this.file || this.id;
    }
    constructor(css, opts = {}) {
      if (css === null || typeof css === "undefined" || typeof css === "object" && !css.toString) throw new Error(`PostCSS received ${css} instead of CSS string`);
      this.css = css.toString();
      if (this.css[0] === "\uFEFF" || this.css[0] === "\uFFFE") {
        this.hasBOM = true;
        this.css = this.css.slice(1);
      } else this.hasBOM = false;
      this.document = this.css;
      if (opts.document) this.document = opts.document.toString();
      if (opts.from) {
        if (!pathAvailable || /^\w+:\/\//.test(opts.from) || isAbsolute(opts.from)) this.file = opts.from;
        else this.file = resolve(opts.from);
      }
      if (pathAvailable && sourceMapAvailable) {
        let map = new PreviousMap(this.css, opts);
        if (map.text) {
          this.map = map;
          let file = map.consumer().file;
          if (!this.file && file) this.file = this.mapResolve(file);
        }
      }
      if (!this.file) this.id = "<input css " + nanoid(6) + ">";
      if (this.map) this.map.file = this.from;
    }
    error(message, line, column, opts = {}) {
      let endColumn, endLine, endOffset, offset, result;
      if (line && typeof line === "object") {
        let start = line;
        let end = column;
        if (typeof start.offset === "number") {
          offset = start.offset;
          let pos = this.fromOffset(offset);
          line = pos.line;
          column = pos.col;
        } else {
          line = start.line;
          column = start.column;
          offset = this.fromLineAndColumn(line, column);
        }
        if (typeof end.offset === "number") {
          endOffset = end.offset;
          let pos = this.fromOffset(endOffset);
          endLine = pos.line;
          endColumn = pos.col;
        } else {
          endLine = end.line;
          endColumn = end.column;
          endOffset = this.fromLineAndColumn(end.line, end.column);
        }
      } else if (!column) {
        offset = line;
        let pos = this.fromOffset(offset);
        line = pos.line;
        column = pos.col;
      } else offset = this.fromLineAndColumn(line, column);
      let origin = this.origin(line, column, endLine, endColumn);
      if (origin) result = new CssSyntaxError(message, origin.endLine === void 0 ? origin.line : {
        column: origin.column,
        line: origin.line
      }, origin.endLine === void 0 ? origin.column : {
        column: origin.endColumn,
        line: origin.endLine
      }, origin.source, origin.file, opts.plugin);
      else result = new CssSyntaxError(message, endLine === void 0 ? line : {
        column,
        line
      }, endLine === void 0 ? column : {
        column: endColumn,
        line: endLine
      }, this.css, this.file, opts.plugin);
      result.input = {
        column,
        endColumn,
        endLine,
        endOffset,
        line,
        offset,
        source: this.css
      };
      if (this.file) {
        if (pathToFileURL) result.input.url = pathToFileURL(this.file).toString();
        result.input.file = this.file;
      }
      return result;
    }
    fromLineAndColumn(line, column) {
      return getLineToIndex(this)[line - 1] + column - 1;
    }
    fromOffset(offset) {
      let lineToIndex = getLineToIndex(this);
      let lastLine = lineToIndex[lineToIndex.length - 1];
      let min = 0;
      if (offset >= lastLine) min = lineToIndex.length - 1;
      else {
        let max = lineToIndex.length - 2;
        let mid;
        while (min < max) {
          mid = min + (max - min >> 1);
          if (offset < lineToIndex[mid]) max = mid - 1;
          else if (offset >= lineToIndex[mid + 1]) min = mid + 1;
          else {
            min = mid;
            break;
          }
        }
      }
      return {
        col: offset - lineToIndex[min] + 1,
        line: min + 1
      };
    }
    mapResolve(file) {
      if (/^\w+:\/\//.test(file)) return file;
      return resolve(this.map.consumer().sourceRoot || this.map.root || ".", file);
    }
    origin(line, column, endLine, endColumn) {
      if (!this.map) return false;
      let consumer = this.map.consumer();
      let from = consumer.originalPositionFor({
        column: column - 1,
        line
      });
      if (!from.source) return false;
      let to;
      if (typeof endLine === "number") {
        let toPosition = consumer.originalPositionFor({
          column: endColumn - 1,
          line: endLine
        });
        if (toPosition.source) to = toPosition;
      }
      let fromUrl;
      if (isAbsolute(from.source)) fromUrl = pathToFileURL(from.source);
      else fromUrl = new URL(from.source, pathToFileURL(this.map.mapFile || resolve(".") + "/"));
      let result = {
        column: from.column + 1,
        endColumn: to && to.column + 1,
        endLine: to && to.line,
        line: from.line,
        url: fromUrl.toString()
      };
      if (fromUrl.protocol === "file:") {
        if (fileURLToPath) result.file = fileURLToPath(fromUrl);
        else
          throw new Error(`file: protocol is not available in this PostCSS build`);
      }
      let source = consumer.sourceContentFor(from.source);
      if (source) result.source = source;
      return result;
    }
    toJSON() {
      let json = {};
      for (let name of [
        "hasBOM",
        "css",
        "file",
        "id"
      ]) if (this[name] != null) json[name] = this[name];
      if (this.map) {
        json.map = __spreadValues({}, this.map);
        if (json.map.consumerCache) json.map.consumerCache = void 0;
      }
      return json;
    }
  };
  module2.exports = Input;
  Input.default = Input;
  if (terminalHighlight && terminalHighlight.registerInput) terminalHighlight.registerInput(Input);
});
var require_root = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Container = require_container();
  var LazyResult;
  var Processor;
  var Root = class extends Container {
    constructor(defaults) {
      super(defaults);
      this.type = "root";
      if (!this.nodes) this.nodes = [];
    }
    normalize(child, sample, type) {
      let keepBefore = /* @__PURE__ */ new Set();
      for (let node of Array.isArray(child) ? child : [child]) if (node && typeof node === "object" && !node.parent && node.raws && typeof node.raws.before !== "undefined") keepBefore.add(node.raws);
      let nodes = super.normalize(child);
      if (sample) {
        if (type === "prepend") {
          if (this.nodes.length > 1) sample.raws.before = this.nodes[1].raws.before;
          else delete sample.raws.before;
        } else if (this.first !== sample) {
          for (let node of nodes) if (!keepBefore.has(node.raws)) node.raws.before = sample.raws.before;
        }
      }
      return nodes;
    }
    removeChild(child, ignore) {
      let index = this.index(child);
      if (!ignore && index === 0 && this.nodes.length > 1) this.nodes[1].raws.before = this.nodes[index].raws.before;
      return super.removeChild(child);
    }
    toResult(opts = {}) {
      return new LazyResult(new Processor(), this, opts).stringify();
    }
  };
  Root.registerLazyResult = (dependant) => {
    LazyResult = dependant;
  };
  Root.registerProcessor = (dependant) => {
    Processor = dependant;
  };
  module2.exports = Root;
  Root.default = Root;
  Container.registerRoot(Root);
});
var require_list = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var list = {
    comma(string) {
      return list.split(string, [","], true);
    },
    space(string) {
      return list.split(string, [
        " ",
        "\n",
        "	"
      ]);
    },
    split(string, separators, last) {
      if (typeof string !== "string") return [];
      let array = [];
      let current = "";
      let split = false;
      let func = 0;
      let inQuote = false;
      let prevQuote = "";
      let escape = false;
      let inComment = false;
      for (let i2 = 0; i2 < string.length; i2++) {
        let letter = string[i2];
        if (inComment) {
          current += letter;
          if (letter === "*" && string[i2 + 1] === "/") {
            current += "/";
            i2 += 1;
            inComment = false;
          }
          continue;
        }
        if (escape) escape = false;
        else if (letter === "\\") escape = true;
        else if (inQuote) {
          if (letter === prevQuote) inQuote = false;
        } else if (letter === '"' || letter === "'") {
          inQuote = true;
          prevQuote = letter;
        } else if (letter === "/" && string[i2 + 1] === "*") {
          current += "/*";
          i2 += 1;
          inComment = true;
          continue;
        } else if (letter === "(") func += 1;
        else if (letter === ")") {
          if (func > 0) func -= 1;
        } else if (func === 0) {
          if (separators.includes(letter)) split = true;
        }
        if (split) {
          let value2 = current.trim();
          if (last || value2 !== "") array.push(value2);
          current = "";
          split = false;
        } else current += letter;
      }
      let value = current.trim();
      if (last || value !== "") array.push(value);
      return array;
    }
  };
  module2.exports = list;
  list.default = list;
});
var require_rule = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Container = require_container();
  var list = require_list();
  var Rule = class extends Container {
    get selectors() {
      return list.comma(this.selector);
    }
    set selectors(values) {
      let match = this.selector ? this.selector.match(/,\s*/) : null;
      let sep = match ? match[0] : "," + this.raw("between", "beforeOpen");
      this.selector = values.join(sep);
    }
    constructor(defaults) {
      super(defaults);
      this.type = "rule";
      if (!this.nodes) this.nodes = [];
    }
  };
  module2.exports = Rule;
  Rule.default = Rule;
  Container.registerRule(Rule);
});
var require_fromJSON = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var AtRule = require_at_rule();
  var Comment = require_comment();
  var Declaration = require_declaration();
  var Input = require_input();
  var PreviousMap = require_previous_map();
  var Root = require_root();
  var Rule = require_rule();
  function hydrateInputs(json, inputs) {
    if (!json.inputs) return inputs;
    return json.inputs.map((input) => {
      let inputHydrated = __spreadProps(__spreadValues({}, input), {
        __proto__: Input.prototype
      });
      if (inputHydrated.map) inputHydrated.map = __spreadProps(__spreadValues({}, inputHydrated.map), {
        __proto__: PreviousMap.prototype
      });
      return inputHydrated;
    });
  }
  function constructNode(json, inputs, children) {
    let defaults = __spreadValues({}, json);
    delete defaults.inputs;
    delete defaults.nodes;
    if (defaults.source) {
      let _a2 = defaults.source, { inputId } = _a2, source = __objRest(_a2, ["inputId"]);
      defaults.source = source;
      if (inputId != null) defaults.source.input = inputs[inputId];
    }
    let node;
    if (defaults.type === "root") node = new Root(defaults);
    else if (defaults.type === "decl") node = new Declaration(defaults);
    else if (defaults.type === "rule") node = new Rule(defaults);
    else if (defaults.type === "comment") node = new Comment(defaults);
    else if (defaults.type === "atrule") node = new AtRule(defaults);
    else throw new Error("Unknown node type: " + json.type);
    if (children) {
      node.nodes = children;
      for (let child of children) child.parent = node;
    }
    return node;
  }
  function fromJSON(json, inputs) {
    if (Array.isArray(json)) return json.map((n2) => fromJSON(n2));
    let result;
    let stack = [{
      childIndex: 0,
      children: [],
      inputs: hydrateInputs(json, inputs),
      json
    }];
    while (stack.length > 0) {
      let frame = stack[stack.length - 1];
      let jsonNodes = frame.json.nodes;
      if (jsonNodes && frame.childIndex < jsonNodes.length) {
        let childJson = jsonNodes[frame.childIndex];
        frame.childIndex += 1;
        stack.push({
          childIndex: 0,
          children: [],
          inputs: hydrateInputs(childJson, frame.inputs),
          json: childJson
        });
        continue;
      }
      stack.pop();
      let node = constructNode(frame.json, frame.inputs, jsonNodes ? frame.children : void 0);
      if (stack.length > 0) stack[stack.length - 1].children.push(node);
      else result = node;
    }
    return result;
  }
  module2.exports = fromJSON;
  fromJSON.default = fromJSON;
});
var require_map_generator = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var { dirname, relative, resolve, sep } = require___vite_browser_external();
  var { SourceMapConsumer, SourceMapGenerator } = require___vite_browser_external();
  var { pathToFileURL } = require___vite_browser_external();
  var Input = require_input();
  var sourceMapAvailable = Boolean(SourceMapConsumer && SourceMapGenerator);
  var pathAvailable = Boolean(dirname && resolve && relative && sep);
  var MapGenerator = class {
    constructor(stringify, root, opts, cssString) {
      this.stringify = stringify;
      this.mapOpts = opts.map || {};
      this.root = root;
      this.opts = opts;
      this.css = cssString;
      this.originalCSS = cssString;
      this.usesFileUrls = !this.mapOpts.from && this.mapOpts.absolute;
      this.memoizedFileURLs = /* @__PURE__ */ new Map();
      this.memoizedPaths = /* @__PURE__ */ new Map();
      this.memoizedURLs = /* @__PURE__ */ new Map();
    }
    addAnnotation() {
      let content;
      if (this.isInline()) content = "data:application/json;base64," + this.toBase64(this.map.toString());
      else if (typeof this.mapOpts.annotation === "string") content = this.mapOpts.annotation;
      else if (typeof this.mapOpts.annotation === "function") content = this.mapOpts.annotation(this.opts.to, this.root);
      else content = this.outputFile() + ".map";
      let eol = "\n";
      if (this.css.includes("\r\n")) eol = "\r\n";
      this.css += eol + "/*# sourceMappingURL=" + content + " */";
    }
    applyPrevMaps() {
      for (let prev of this.previous()) {
        let from = this.toUrl(this.path(prev.file));
        let root = prev.root || dirname(prev.file);
        let map;
        if (this.mapOpts.sourcesContent === false) {
          map = new SourceMapConsumer(prev.text);
          if (map.sourcesContent) map.sourcesContent = null;
        } else map = prev.consumer();
        this.map.applySourceMap(map, from, this.toUrl(this.path(root)));
      }
    }
    clearAnnotation() {
      if (this.mapOpts.annotation === false) return;
      if (this.root) {
        let node;
        for (let i2 = this.root.nodes.length - 1; i2 >= 0; i2--) {
          node = this.root.nodes[i2];
          if (node.type !== "comment") continue;
          if (node.text.startsWith("# sourceMappingURL=")) this.root.removeChild(i2);
        }
      } else if (this.css) {
        let annotation = "/*# sourceMappingURL=";
        let css = "";
        let cursor = 0;
        let startIndex;
        while ((startIndex = this.css.indexOf(annotation, cursor)) !== -1) {
          let endIndex = this.css.indexOf("*/", startIndex + 21);
          if (endIndex === -1) break;
          while (startIndex > cursor && this.css[startIndex - 1] === "\n") startIndex--;
          css += this.css.slice(cursor, startIndex);
          cursor = endIndex + 2;
        }
        if (cursor > 0) this.css = css + this.css.slice(cursor);
      }
    }
    generate() {
      this.clearAnnotation();
      if (pathAvailable && sourceMapAvailable && this.isMap()) return this.generateMap();
      else {
        let result = "";
        this.stringify(this.root, (i2) => {
          result += i2;
        });
        return [result];
      }
    }
    generateMap() {
      if (this.root) this.generateString();
      else if (this.previous().length === 1) {
        let prev = this.previous()[0].consumer();
        prev.file = this.outputFile();
        this.map = SourceMapGenerator.fromSourceMap(prev, { ignoreInvalidMapping: true });
      } else {
        this.map = new SourceMapGenerator({
          file: this.outputFile(),
          ignoreInvalidMapping: true
        });
        this.map.addMapping({
          generated: {
            column: 0,
            line: 1
          },
          original: {
            column: 0,
            line: 1
          },
          source: this.opts.from ? this.toUrl(this.path(this.opts.from)) : "<no source>"
        });
      }
      if (this.isSourcesContent()) this.setSourcesContent();
      if (this.root && this.previous().length > 0) this.applyPrevMaps();
      if (this.isAnnotation()) this.addAnnotation();
      if (this.isInline()) return [this.css];
      else return [this.css, this.map];
    }
    generateString() {
      this.css = "";
      this.map = new SourceMapGenerator({
        file: this.outputFile(),
        ignoreInvalidMapping: true
      });
      let line = 1;
      let column = 1;
      let noSource = "<no source>";
      let mapping = {
        generated: {
          column: 0,
          line: 0
        },
        original: {
          column: 0,
          line: 0
        },
        source: ""
      };
      let last, lines;
      this.stringify(this.root, (str, node, type) => {
        this.css += str;
        if (node && type !== "end") {
          mapping.generated.line = line;
          mapping.generated.column = column - 1;
          if (node.source && node.source.start) {
            mapping.source = this.sourcePath(node);
            mapping.original.line = node.source.start.line;
            mapping.original.column = node.source.start.column - 1;
            this.map.addMapping(mapping);
          } else {
            mapping.source = noSource;
            mapping.original.line = 1;
            mapping.original.column = 0;
            this.map.addMapping(mapping);
          }
        }
        lines = str.match(/\n/g);
        if (lines) {
          line += lines.length;
          last = str.lastIndexOf("\n");
          column = str.length - last;
        } else column += str.length;
        if (node && type !== "start") {
          let p = node.parent || { raws: {} };
          if (!(node.type === "decl" || node.type === "atrule" && !node.nodes) || node !== p.last || p.raws.semicolon) {
            if (node.source && node.source.end) {
              mapping.source = this.sourcePath(node);
              mapping.original.line = node.source.end.line;
              mapping.original.column = node.source.end.column - 1;
              mapping.generated.line = line;
              mapping.generated.column = column - 2;
              this.map.addMapping(mapping);
            } else {
              mapping.source = noSource;
              mapping.original.line = 1;
              mapping.original.column = 0;
              mapping.generated.line = line;
              mapping.generated.column = column - 1;
              this.map.addMapping(mapping);
            }
          }
        }
      });
    }
    isAnnotation() {
      if (this.isInline()) return true;
      if (typeof this.mapOpts.annotation !== "undefined") return this.mapOpts.annotation;
      if (this.previous().length) return this.previous().some((i2) => i2.annotation);
      return true;
    }
    isInline() {
      if (typeof this.mapOpts.inline !== "undefined") return this.mapOpts.inline;
      let annotation = this.mapOpts.annotation;
      if (typeof annotation !== "undefined" && annotation !== true) return false;
      if (this.previous().length) return this.previous().some((i2) => i2.inline);
      return true;
    }
    isMap() {
      if (typeof this.opts.map !== "undefined") return !!this.opts.map;
      return this.previous().length > 0;
    }
    isSourcesContent() {
      if (typeof this.mapOpts.sourcesContent !== "undefined") return this.mapOpts.sourcesContent;
      if (this.previous().length) return this.previous().some((i2) => i2.withContent());
      return true;
    }
    outputFile() {
      if (this.opts.to) return this.path(this.opts.to);
      else if (this.opts.from) return this.path(this.opts.from);
      else return "to.css";
    }
    path(file) {
      if (this.mapOpts.absolute) return file;
      if (file.charCodeAt(0) === 60) return file;
      if (/^\w+:\/\//.test(file)) return file;
      let cached = this.memoizedPaths.get(file);
      if (cached) return cached;
      let from = this.opts.to ? dirname(this.opts.to) : ".";
      if (typeof this.mapOpts.annotation === "string") from = dirname(resolve(from, this.mapOpts.annotation));
      let path = relative(from, file);
      this.memoizedPaths.set(file, path);
      return path;
    }
    previous() {
      if (!this.previousMaps) {
        this.previousMaps = [];
        if (this.root) this.root.walk((node) => {
          if (node.source && node.source.input.map) {
            let map = node.source.input.map;
            if (!this.previousMaps.includes(map)) this.previousMaps.push(map);
          }
        });
        else {
          let input = new Input(this.originalCSS, this.opts);
          if (input.map) this.previousMaps.push(input.map);
        }
      }
      return this.previousMaps;
    }
    setSourcesContent() {
      let already = {};
      if (this.root) this.root.walk((node) => {
        if (node.source) {
          let from = node.source.input.from;
          if (from && !already[from]) {
            already[from] = true;
            let fromUrl = this.usesFileUrls ? this.toFileUrl(from) : this.toUrl(this.path(from));
            this.map.setSourceContent(fromUrl, node.source.input.css);
          }
        }
      });
      else if (this.css) {
        let from = this.opts.from ? this.toUrl(this.path(this.opts.from)) : "<no source>";
        this.map.setSourceContent(from, this.css);
      }
    }
    sourcePath(node) {
      if (this.mapOpts.from) return this.toUrl(this.mapOpts.from);
      else if (this.usesFileUrls) return this.toFileUrl(node.source.input.from);
      else return this.toUrl(this.path(node.source.input.from));
    }
    toBase64(str) {
      if (Buffer) return Buffer.from(str).toString("base64");
      else return window.btoa(unescape(encodeURIComponent(str)));
    }
    toFileUrl(path) {
      let cached = this.memoizedFileURLs.get(path);
      if (cached) return cached;
      if (pathToFileURL) {
        let fileURL = pathToFileURL(path).toString();
        this.memoizedFileURLs.set(path, fileURL);
        return fileURL;
      } else throw new Error("`map.absolute` option is not available in this PostCSS build");
    }
    toUrl(path) {
      let cached = this.memoizedURLs.get(path);
      if (cached) return cached;
      if (sep === "\\") path = path.replace(/\\/g, "/");
      let url = encodeURI(path).replace(/[#?]/g, encodeURIComponent);
      this.memoizedURLs.set(path, url);
      return url;
    }
  };
  module2.exports = MapGenerator;
});
var require_tokenize = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var SINGLE_QUOTE = "'".charCodeAt(0);
  var DOUBLE_QUOTE = '"'.charCodeAt(0);
  var BACKSLASH = "\\".charCodeAt(0);
  var SLASH = "/".charCodeAt(0);
  var NEWLINE = "\n".charCodeAt(0);
  var SPACE = " ".charCodeAt(0);
  var FEED = "\f".charCodeAt(0);
  var TAB = "	".charCodeAt(0);
  var CR = "\r".charCodeAt(0);
  var OPEN_SQUARE = "[".charCodeAt(0);
  var CLOSE_SQUARE = "]".charCodeAt(0);
  var OPEN_PARENTHESES = "(".charCodeAt(0);
  var CLOSE_PARENTHESES = ")".charCodeAt(0);
  var OPEN_CURLY = "{".charCodeAt(0);
  var CLOSE_CURLY = "}".charCodeAt(0);
  var SEMICOLON = ";".charCodeAt(0);
  var ASTERISK = "*".charCodeAt(0);
  var COLON = ":".charCodeAt(0);
  var AT = "@".charCodeAt(0);
  var RE_AT_END = /[\t\n\f\r "#'()/;[\\\]{}]/g;
  var RE_WORD_END = /[\t\n\f\r !"#'():;@[\\\]{}]|\/(?=\*)/g;
  var RE_BAD_BRACKET = /.[\r\n"'(/\\]/;
  var RE_HEX_ESCAPE = /[\da-f]/i;
  module2.exports = function tokenizer(input, options = {}) {
    let css = input.css.valueOf();
    let ignore = options.ignoreErrors;
    let code, content, escape, next, quote;
    let currentToken, escaped, escapePos, n2, prev;
    let length = css.length;
    let pos = 0;
    let buffer = [];
    let returned = [];
    let lastBadParen = -1;
    function position() {
      return pos;
    }
    function unclosed(what) {
      throw input.error("Unclosed " + what, pos);
    }
    function endOfFile() {
      return returned.length === 0 && pos >= length;
    }
    function nextToken(opts) {
      if (returned.length) return returned.pop();
      if (pos >= length) return;
      let ignoreUnclosed = opts ? opts.ignoreUnclosed : false;
      code = css.charCodeAt(pos);
      switch (code) {
        case NEWLINE:
        case SPACE:
        case TAB:
        case CR:
        case FEED:
          next = pos;
          do {
            next += 1;
            code = css.charCodeAt(next);
          } while (code === SPACE || code === NEWLINE || code === TAB || code === CR || code === FEED);
          currentToken = ["space", css.slice(pos, next)];
          pos = next - 1;
          break;
        case OPEN_SQUARE:
        case CLOSE_SQUARE:
        case OPEN_CURLY:
        case CLOSE_CURLY:
        case COLON:
        case SEMICOLON:
        case CLOSE_PARENTHESES: {
          let controlChar = String.fromCharCode(code);
          currentToken = [
            controlChar,
            controlChar,
            pos
          ];
          break;
        }
        case OPEN_PARENTHESES:
          prev = buffer.length ? buffer.pop()[1] : "";
          n2 = css.charCodeAt(pos + 1);
          if (prev === "url" && n2 !== SINGLE_QUOTE && n2 !== DOUBLE_QUOTE && n2 !== SPACE && n2 !== NEWLINE && n2 !== TAB && n2 !== FEED && n2 !== CR) {
            next = pos;
            do {
              escaped = false;
              next = css.indexOf(")", next + 1);
              if (next === -1) {
                if (ignore || ignoreUnclosed) {
                  next = pos;
                  break;
                } else unclosed("bracket");
              }
              escapePos = next;
              while (css.charCodeAt(escapePos - 1) === BACKSLASH) {
                escapePos -= 1;
                escaped = !escaped;
              }
            } while (escaped);
            currentToken = [
              "brackets",
              css.slice(pos, next + 1),
              pos,
              next
            ];
            pos = next;
          } else if (pos <= lastBadParen) currentToken = [
            "(",
            "(",
            pos
          ];
          else {
            next = css.indexOf(")", pos + 1);
            content = css.slice(pos, next + 1);
            if (next === -1 || RE_BAD_BRACKET.test(content)) {
              lastBadParen = next === -1 ? length : next;
              currentToken = [
                "(",
                "(",
                pos
              ];
            } else {
              currentToken = [
                "brackets",
                content,
                pos,
                next
              ];
              pos = next;
            }
          }
          break;
        case SINGLE_QUOTE:
        case DOUBLE_QUOTE:
          quote = code === SINGLE_QUOTE ? "'" : '"';
          next = pos;
          do {
            escaped = false;
            next = css.indexOf(quote, next + 1);
            if (next === -1) {
              if (ignore || ignoreUnclosed) {
                next = pos + 1;
                break;
              } else unclosed("string");
            }
            escapePos = next;
            while (css.charCodeAt(escapePos - 1) === BACKSLASH) {
              escapePos -= 1;
              escaped = !escaped;
            }
          } while (escaped);
          currentToken = [
            "string",
            css.slice(pos, next + 1),
            pos,
            next
          ];
          pos = next;
          break;
        case AT:
          RE_AT_END.lastIndex = pos + 1;
          RE_AT_END.test(css);
          if (RE_AT_END.lastIndex === 0) next = css.length - 1;
          else next = RE_AT_END.lastIndex - 2;
          currentToken = [
            "at-word",
            css.slice(pos, next + 1),
            pos,
            next
          ];
          pos = next;
          break;
        case BACKSLASH:
          next = pos;
          escape = true;
          while (css.charCodeAt(next + 1) === BACKSLASH) {
            next += 1;
            escape = !escape;
          }
          code = css.charCodeAt(next + 1);
          if (escape && code !== SLASH && code !== SPACE && code !== NEWLINE && code !== TAB && code !== CR && code !== FEED) {
            next += 1;
            if (RE_HEX_ESCAPE.test(css.charAt(next))) {
              while (RE_HEX_ESCAPE.test(css.charAt(next + 1))) next += 1;
              if (css.charCodeAt(next + 1) === SPACE) next += 1;
            }
          }
          currentToken = [
            "word",
            css.slice(pos, next + 1),
            pos,
            next
          ];
          pos = next;
          break;
        default:
          if (code === SLASH && css.charCodeAt(pos + 1) === ASTERISK) {
            next = css.indexOf("*/", pos + 2) + 1;
            if (next === 0) {
              if (ignore || ignoreUnclosed) next = css.length;
              else unclosed("comment");
            }
            currentToken = [
              "comment",
              css.slice(pos, next + 1),
              pos,
              next
            ];
            pos = next;
          } else {
            RE_WORD_END.lastIndex = pos + 1;
            RE_WORD_END.test(css);
            if (RE_WORD_END.lastIndex === 0) next = css.length - 1;
            else next = RE_WORD_END.lastIndex - 2;
            currentToken = [
              "word",
              css.slice(pos, next + 1),
              pos,
              next
            ];
            buffer.push(currentToken);
            pos = next;
          }
      }
      pos++;
      return currentToken;
    }
    function back(token) {
      returned.push(token);
    }
    return {
      back,
      endOfFile,
      nextToken,
      position
    };
  };
});
var require_parser = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var AtRule = require_at_rule();
  var Comment = require_comment();
  var Declaration = require_declaration();
  var Root = require_root();
  var Rule = require_rule();
  var tokenizer = require_tokenize();
  var SAFE_COMMENT_NEIGHBOR = {
    empty: true,
    space: true
  };
  function findLastWithPosition(tokens) {
    for (let i2 = tokens.length - 1; i2 >= 0; i2--) {
      let token = tokens[i2];
      let pos = token[3] || token[2];
      if (pos) return pos;
    }
  }
  function tokensToString(tokens, from, to) {
    let result = "";
    for (let i2 = from; i2 < to; i2++) result += tokens[i2][1];
    return result;
  }
  var Parser = class {
    constructor(input) {
      this.input = input;
      this.root = new Root();
      this.current = this.root;
      this.spaces = "";
      this.semicolon = false;
      this.createTokenizer();
      this.root.source = {
        input,
        start: {
          column: 1,
          line: 1,
          offset: 0
        }
      };
    }
    atrule(token) {
      let node = new AtRule();
      node.name = token[1].slice(1);
      if (node.name === "") this.unnamedAtrule(node, token);
      this.init(node, token[2]);
      let type;
      let prev;
      let shift;
      let last = false;
      let open = false;
      let params = [];
      let brackets = [];
      while (!this.tokenizer.endOfFile()) {
        token = this.tokenizer.nextToken();
        type = token[0];
        if (type === "(" || type === "[") brackets.push(type === "(" ? ")" : "]");
        else if (type === "{" && brackets.length > 0) brackets.push("}");
        else if (type === brackets[brackets.length - 1]) brackets.pop();
        if (brackets.length === 0) {
          if (type === ";") {
            node.source.end = this.getPosition(token[2]);
            node.source.end.offset++;
            this.semicolon = true;
            break;
          } else if (type === "{") {
            open = true;
            break;
          } else if (type === "}") {
            if (params.length > 0) {
              shift = params.length - 1;
              prev = params[shift];
              while (prev && prev[0] === "space") prev = params[--shift];
              if (prev) {
                node.source.end = this.getPosition(prev[3] || prev[2]);
                node.source.end.offset++;
              }
            }
            this.end(token);
            break;
          } else params.push(token);
        } else params.push(token);
        if (this.tokenizer.endOfFile()) {
          last = true;
          break;
        }
      }
      node.raws.between = this.spacesAndCommentsFromEnd(params);
      if (params.length) {
        node.raws.afterName = this.spacesAndCommentsFromStart(params);
        this.raw(node, "params", params);
        if (last) {
          token = params[params.length - 1];
          node.source.end = this.getPosition(token[3] || token[2]);
          node.source.end.offset++;
          this.spaces = node.raws.between;
          node.raws.between = "";
        }
      } else {
        node.raws.afterName = "";
        node.params = "";
      }
      if (open) {
        node.nodes = [];
        this.current = node;
      }
    }
    checkMissedSemicolon(tokens) {
      let colon = this.colon(tokens);
      if (colon === false) return;
      let founded = 0;
      let token;
      for (let j = colon - 1; j >= 0; j--) {
        token = tokens[j];
        if (token[0] !== "space") {
          founded += 1;
          if (founded === 2) break;
        }
      }
      throw this.input.error("Missed semicolon", token[0] === "word" ? token[3] + 1 : token[2]);
    }
    colon(tokens) {
      let brackets = 0;
      let prev, token, type;
      for (let [i2, element] of tokens.entries()) {
        token = element;
        type = token[0];
        if (type === "(") brackets += 1;
        if (type === ")") brackets -= 1;
        if (brackets === 0 && type === ":") {
          if (!prev) this.doubleColon(token);
          else if (prev[0] === "word" && prev[1] === "progid") continue;
          else return i2;
        }
        prev = token;
      }
      return false;
    }
    comment(token) {
      let node = new Comment();
      this.init(node, token[2]);
      node.source.end = this.getPosition(token[3] || token[2]);
      node.source.end.offset++;
      let text = token[1].slice(2, -2);
      if (!text.trim()) {
        node.text = "";
        node.raws.left = text;
        node.raws.right = "";
      } else {
        let match = text.match(/^(\s*)([^]*\S)(\s*)$/);
        node.text = match[2];
        node.raws.left = match[1];
        node.raws.right = match[3];
      }
    }
    createTokenizer() {
      this.tokenizer = tokenizer(this.input);
    }
    decl(tokens, customProperty) {
      let node = new Declaration();
      this.init(node, tokens[0][2]);
      let last = tokens[tokens.length - 1];
      if (last[0] === ";") {
        this.semicolon = true;
        tokens.pop();
      }
      node.source.end = this.getPosition(last[3] || last[2] || findLastWithPosition(tokens));
      node.source.end.offset++;
      let start = 0;
      while (tokens[start][0] !== "word") {
        if (start === tokens.length - 1) this.unknownWord([tokens[start]]);
        start++;
      }
      node.raws.before += tokensToString(tokens, 0, start);
      node.source.start = this.getPosition(tokens[start][2]);
      let propStart = start;
      while (start < tokens.length) {
        let type = tokens[start][0];
        if (type === ":" || type === "space" || type === "comment") break;
        start++;
      }
      node.prop = tokensToString(tokens, propStart, start);
      let betweenStart = start;
      let token;
      while (start < tokens.length) {
        token = tokens[start];
        start++;
        if (token[0] === ":") break;
        if (token[0] === "word" && /\w/.test(token[1])) this.unknownWord([token]);
      }
      node.raws.between = tokensToString(tokens, betweenStart, start);
      if (node.prop[0] === "_" || node.prop[0] === "*") {
        node.raws.before += node.prop[0];
        node.prop = node.prop.slice(1);
      }
      let firstSpacesStart = start;
      while (start < tokens.length) {
        let next = tokens[start][0];
        if (next !== "space" && next !== "comment") break;
        start++;
      }
      let firstSpaces = tokens.slice(firstSpacesStart, start);
      tokens = tokens.slice(start);
      this.precheckMissedSemicolon(tokens);
      for (let i2 = tokens.length - 1; i2 >= 0; i2--) {
        token = tokens[i2];
        if (token[1].toLowerCase() === "!important") {
          node.important = true;
          let string = this.stringFrom(tokens, i2);
          string = this.spacesFromEnd(tokens) + string;
          if (string !== " !important") node.raws.important = string;
          break;
        } else if (token[1].toLowerCase() === "important") {
          let cache = tokens.slice(0);
          let str = "";
          for (let j = i2; j > 0; j--) {
            let type = cache[j][0];
            if (str.trim().startsWith("!") && type !== "space") break;
            str = cache.pop()[1] + str;
          }
          if (str.trim().startsWith("!")) {
            node.important = true;
            node.raws.important = str;
            tokens = cache;
          }
        }
        if (token[0] !== "space" && token[0] !== "comment") break;
      }
      if (tokens.some((i2) => i2[0] !== "space" && i2[0] !== "comment")) {
        node.raws.between += firstSpaces.map((i2) => i2[1]).join("");
        firstSpaces = [];
      }
      this.raw(node, "value", firstSpaces.concat(tokens), customProperty);
      if (node.value.includes(":") && !customProperty) this.checkMissedSemicolon(tokens);
    }
    doubleColon(token) {
      throw this.input.error("Double colon", { offset: token[2] }, { offset: token[2] + token[1].length });
    }
    emptyRule(token) {
      let node = new Rule();
      this.init(node, token[2]);
      node.selector = "";
      node.raws.between = "";
      this.current = node;
    }
    end(token) {
      if (this.current.nodes && this.current.nodes.length) this.current.raws.semicolon = this.semicolon;
      this.semicolon = false;
      this.current.raws.after = (this.current.raws.after || "") + this.spaces;
      this.spaces = "";
      if (this.current.parent) {
        this.current.source.end = this.getPosition(token[2]);
        this.current.source.end.offset++;
        this.current = this.current.parent;
      } else this.unexpectedClose(token);
    }
    endFile() {
      if (this.current.parent) this.unclosedBlock();
      if (this.current.nodes && this.current.nodes.length) this.current.raws.semicolon = this.semicolon;
      this.current.raws.after = (this.current.raws.after || "") + this.spaces;
      this.root.source.end = this.getPosition(this.tokenizer.position());
    }
    freeSemicolon(token) {
      this.spaces += token[1];
      if (this.current.nodes) {
        let prev = this.current.nodes[this.current.nodes.length - 1];
        if (prev && prev.type === "rule" && !prev.raws.ownSemicolon) {
          prev.raws.ownSemicolon = this.spaces;
          this.spaces = "";
          prev.source.end = this.getPosition(token[2]);
          prev.source.end.offset++;
        }
      }
    }
    getPosition(offset) {
      let pos = this.input.fromOffset(offset);
      return {
        column: pos.col,
        line: pos.line,
        offset
      };
    }
    init(node, offset) {
      this.current.push(node);
      node.source = {
        input: this.input,
        start: this.getPosition(offset)
      };
      node.raws.before = this.spaces;
      this.spaces = "";
      if (node.type !== "comment") this.semicolon = false;
    }
    other(start) {
      let end = false;
      let type = null;
      let colon = false;
      let bracket = null;
      let brackets = [];
      let customProperty = start[1].startsWith("--");
      let tokens = [];
      let token = start;
      while (token) {
        type = token[0];
        tokens.push(token);
        if (type === "(" || type === "[") {
          if (!bracket) bracket = token;
          brackets.push(type === "(" ? ")" : "]");
        } else if (customProperty && colon && type === "{") {
          if (!bracket) bracket = token;
          brackets.push("}");
        } else if (brackets.length === 0) {
          if (type === ";") {
            if (colon) {
              this.decl(tokens, customProperty);
              return;
            } else break;
          } else if (type === "{") {
            this.rule(tokens);
            return;
          } else if (type === "}") {
            this.tokenizer.back(tokens.pop());
            end = true;
            break;
          } else if (type === ":") colon = true;
        } else if (type === brackets[brackets.length - 1]) {
          brackets.pop();
          if (brackets.length === 0) bracket = null;
        }
        token = this.tokenizer.nextToken();
      }
      if (this.tokenizer.endOfFile()) end = true;
      if (brackets.length > 0) this.unclosedBracket(bracket);
      if (end && colon) {
        if (!customProperty) while (tokens.length) {
          token = tokens[tokens.length - 1][0];
          if (token !== "space" && token !== "comment") break;
          this.tokenizer.back(tokens.pop());
        }
        this.decl(tokens, customProperty);
      } else this.unknownWord(tokens);
    }
    parse() {
      let token;
      while (!this.tokenizer.endOfFile()) {
        token = this.tokenizer.nextToken();
        switch (token[0]) {
          case "space":
            this.spaces += token[1];
            break;
          case ";":
            this.freeSemicolon(token);
            break;
          case "}":
            this.end(token);
            break;
          case "comment":
            this.comment(token);
            break;
          case "at-word":
            this.atrule(token);
            break;
          case "{":
            this.emptyRule(token);
            break;
          default:
            this.other(token);
        }
      }
      this.endFile();
    }
    precheckMissedSemicolon() {
    }
    raw(node, prop, tokens, customProperty) {
      let token, type;
      let length = tokens.length;
      let value = "";
      let last = "";
      let clean = true;
      let next, prev;
      for (let i2 = 0; i2 < length; i2 += 1) {
        token = tokens[i2];
        type = token[0];
        if (type === "space" && i2 === length - 1 && !customProperty) clean = false;
        else if (type === "comment") {
          prev = tokens[i2 - 1] ? tokens[i2 - 1][0] : "empty";
          next = tokens[i2 + 1] ? tokens[i2 + 1][0] : "empty";
          if (!SAFE_COMMENT_NEIGHBOR[prev] && !SAFE_COMMENT_NEIGHBOR[next]) {
            if (last === ",") clean = false;
            else {
              value += token[1];
              last = token[1].slice(-1);
            }
          } else clean = false;
        } else {
          value += token[1];
          last = token[1].slice(-1);
        }
      }
      if (!clean) {
        let raw = tokens.reduce((all, i2) => all + i2[1], "");
        node.raws[prop] = {
          raw,
          value
        };
      }
      node[prop] = value;
    }
    rule(tokens) {
      tokens.pop();
      let node = new Rule();
      this.init(node, tokens[0][2]);
      node.raws.between = this.spacesAndCommentsFromEnd(tokens);
      this.raw(node, "selector", tokens);
      this.current = node;
    }
    spacesAndCommentsFromEnd(tokens) {
      let lastTokenType;
      let spaces = "";
      while (tokens.length) {
        lastTokenType = tokens[tokens.length - 1][0];
        if (lastTokenType !== "space" && lastTokenType !== "comment") break;
        spaces = tokens.pop()[1] + spaces;
      }
      return spaces;
    }
    spacesAndCommentsFromStart(tokens) {
      let next;
      let spaces = "";
      while (tokens.length) {
        next = tokens[0][0];
        if (next !== "space" && next !== "comment") break;
        spaces += tokens.shift()[1];
      }
      return spaces;
    }
    spacesFromEnd(tokens) {
      let lastTokenType;
      let spaces = "";
      while (tokens.length) {
        lastTokenType = tokens[tokens.length - 1][0];
        if (lastTokenType !== "space") break;
        spaces = tokens.pop()[1] + spaces;
      }
      return spaces;
    }
    stringFrom(tokens, from) {
      let result = "";
      for (let i2 = from; i2 < tokens.length; i2++) result += tokens[i2][1];
      tokens.splice(from, tokens.length - from);
      return result;
    }
    unclosedBlock() {
      let pos = this.current.source.start;
      throw this.input.error("Unclosed block", pos.line, pos.column);
    }
    unclosedBracket(bracket) {
      throw this.input.error("Unclosed bracket", { offset: bracket[2] }, { offset: bracket[2] + 1 });
    }
    unexpectedClose(token) {
      throw this.input.error("Unexpected }", { offset: token[2] }, { offset: token[2] + 1 });
    }
    unknownWord(tokens) {
      throw this.input.error("Unknown word " + tokens[0][1], { offset: tokens[0][2] }, { offset: tokens[0][2] + tokens[0][1].length });
    }
    unnamedAtrule(node, token) {
      throw this.input.error("At-rule without name", { offset: token[2] }, { offset: token[2] + token[1].length });
    }
  };
  module2.exports = Parser;
});
var require_parse = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Container = require_container();
  var Input = require_input();
  var Parser = require_parser();
  function parse(css, opts) {
    let parser = new Parser(new Input(css, opts));
    try {
      parser.parse();
    } catch (e2) {
      if (true) {
        if (e2.name === "CssSyntaxError" && opts && opts.from) {
          if (/\.scss$/i.test(opts.from)) e2.message += "\nYou tried to parse SCSS with the standard CSS parser; try again with the postcss-scss parser";
          else if (/\.sass/i.test(opts.from)) e2.message += "\nYou tried to parse Sass with the standard CSS parser; try again with the postcss-sass parser";
          else if (/\.less$/i.test(opts.from)) e2.message += "\nYou tried to parse Less with the standard CSS parser; try again with the postcss-less parser";
        }
      }
      throw e2;
    }
    return parser.root;
  }
  module2.exports = parse;
  parse.default = parse;
  Container.registerParse(parse);
});
var require_warning = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Container = require_container();
  var { my } = require_symbols();
  var Warning = class {
    constructor(text, opts = {}) {
      this.type = "warning";
      this.text = text;
      if (opts.node && opts.node.source) {
        if (!opts.node[my]) Container.rebuild(opts.node);
        let range = opts.node.rangeBy(opts);
        this.line = range.start.line;
        this.column = range.start.column;
        this.endLine = range.end.line;
        this.endColumn = range.end.column;
      }
      for (let opt in opts) this[opt] = opts[opt];
    }
    toString() {
      if (this.node) return this.node.error(this.text, {
        index: this.index,
        plugin: this.plugin,
        start: this.start,
        word: this.word
      }).message;
      if (this.plugin) return this.plugin + ": " + this.text;
      return this.text;
    }
  };
  module2.exports = Warning;
  Warning.default = Warning;
});
var require_result = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Warning = require_warning();
  var Result = class {
    get content() {
      return this.css;
    }
    constructor(processor, root, opts) {
      this.processor = processor;
      this.messages = [];
      this.root = root;
      this.opts = opts;
      this.css = "";
      this.map = void 0;
    }
    toString() {
      return this.css;
    }
    warn(text, opts = {}) {
      if (!opts.plugin) {
        if (this.lastPlugin && this.lastPlugin.postcssPlugin) opts.plugin = this.lastPlugin.postcssPlugin;
      }
      let warning = new Warning(text, opts);
      this.messages.push(warning);
      return warning;
    }
    warnings() {
      return this.messages.filter((i2) => i2.type === "warning");
    }
  };
  module2.exports = Result;
  Result.default = Result;
});
var require_warn_once = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var printed = {};
  module2.exports = function warnOnce(message) {
    if (printed[message]) return;
    printed[message] = true;
    if (typeof console !== "undefined" && console.warn) console.warn(message);
  };
});
var require_lazy_result = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Container = require_container();
  var Document2 = require_document();
  var MapGenerator = require_map_generator();
  var parse = require_parse();
  var Result = require_result();
  var Root = require_root();
  var stringify = require_stringify();
  var { isClean, my } = require_symbols();
  var warnOnce = require_warn_once();
  var TYPE_TO_CLASS_NAME = {
    atrule: "AtRule",
    comment: "Comment",
    decl: "Declaration",
    document: "Document",
    root: "Root",
    rule: "Rule"
  };
  var PLUGIN_PROPS = {
    AtRule: true,
    AtRuleExit: true,
    Comment: true,
    CommentExit: true,
    Declaration: true,
    DeclarationExit: true,
    Document: true,
    DocumentExit: true,
    Once: true,
    OnceExit: true,
    postcssPlugin: true,
    prepare: true,
    Root: true,
    RootExit: true,
    Rule: true,
    RuleExit: true
  };
  var NOT_VISITORS = {
    Once: true,
    postcssPlugin: true,
    prepare: true
  };
  var CHILDREN = 0;
  function isPromise(obj) {
    return typeof obj === "object" && typeof obj.then === "function";
  }
  function getEvents(node) {
    let key = false;
    let type = TYPE_TO_CLASS_NAME[node.type];
    if (node.type === "decl") key = node.prop.toLowerCase();
    else if (node.type === "atrule") key = node.name.toLowerCase();
    if (key && node.append) return [
      type,
      type + "-" + key,
      CHILDREN,
      type + "Exit",
      type + "Exit-" + key
    ];
    else if (key) return [
      type,
      type + "-" + key,
      type + "Exit",
      type + "Exit-" + key
    ];
    else if (node.append) return [
      type,
      CHILDREN,
      type + "Exit"
    ];
    else return [type, type + "Exit"];
  }
  function toStack(node) {
    let events;
    if (node.type === "document") events = [
      "Document",
      CHILDREN,
      "DocumentExit"
    ];
    else if (node.type === "root") events = [
      "Root",
      CHILDREN,
      "RootExit"
    ];
    else events = getEvents(node);
    return {
      eventIndex: 0,
      events,
      iterator: 0,
      node,
      visitorIndex: 0,
      visitors: []
    };
  }
  function cleanMarks(node) {
    let stack = [node];
    while (stack.length > 0) {
      let next = stack.pop();
      next[isClean] = false;
      if (next.nodes) for (let i2 of next.nodes) stack.push(i2);
    }
    return node;
  }
  var postcss = {};
  var LazyResult = class LazyResult2 {
    get content() {
      return this.stringify().content;
    }
    get css() {
      return this.stringify().css;
    }
    get map() {
      return this.stringify().map;
    }
    get messages() {
      return this.sync().messages;
    }
    get opts() {
      return this.result.opts;
    }
    get processor() {
      return this.result.processor;
    }
    get root() {
      return this.sync().root;
    }
    get [Symbol.toStringTag]() {
      return "LazyResult";
    }
    constructor(processor, css, opts) {
      this.stringified = false;
      this.processed = false;
      let root;
      if (typeof css === "object" && css !== null && (css.type === "root" || css.type === "document")) root = cleanMarks(css);
      else if (css instanceof LazyResult2 || css instanceof Result) {
        root = cleanMarks(css.root);
        if (css.map) {
          if (typeof opts.map === "undefined") opts.map = {};
          if (!opts.map.inline) opts.map.inline = false;
          opts.map.prev = css.map;
        }
      } else {
        let parser = parse;
        if (opts.syntax) parser = opts.syntax.parse;
        if (opts.parser) parser = opts.parser;
        if (parser.parse) parser = parser.parse;
        try {
          root = parser(css, opts);
        } catch (error) {
          this.processed = true;
          this.error = error;
        }
        if (root && !root[my])
          Container.rebuild(root);
      }
      this.result = new Result(processor, root, opts);
      this.helpers = __spreadProps(__spreadValues({}, postcss), {
        postcss,
        result: this.result
      });
      this.plugins = this.processor.plugins.map((plugin) => {
        if (typeof plugin === "object" && plugin.prepare) return __spreadValues(__spreadValues({}, plugin), plugin.prepare(this.result));
        else return plugin;
      });
    }
    async() {
      if (this.error) return Promise.reject(this.error);
      if (this.processed) return Promise.resolve(this.result);
      if (!this.processing) this.processing = this.runAsync();
      return this.processing;
    }
    catch(onRejected) {
      return this.async().catch(onRejected);
    }
    finally(onFinally) {
      return this.async().finally(onFinally);
    }
    getAsyncError() {
      throw new Error("Use process(css).then(cb) to work with async plugins");
    }
    handleError(error, node) {
      let plugin = this.result.lastPlugin;
      try {
        if (node) node.addToError(error);
        this.error = error;
        if (error.name === "CssSyntaxError" && !error.plugin) {
          error.plugin = plugin.postcssPlugin;
          error.setMessage();
        } else if (plugin.postcssVersion) {
          if (true) {
            let pluginName = plugin.postcssPlugin;
            let pluginVer = plugin.postcssVersion;
            let runtimeVer = this.result.processor.version;
            let a2 = pluginVer.split(".");
            let b = runtimeVer.split(".");
            if (a2[0] !== b[0] || parseInt(a2[1]) > parseInt(b[1])) console.error("Unknown error from PostCSS plugin. Your current PostCSS version is " + runtimeVer + ", but " + pluginName + " uses " + pluginVer + ". Perhaps this is the source of the error below.");
          }
        }
      } catch (err) {
        if (console && console.error) console.error(err);
      }
      return error;
    }
    prepareVisitors() {
      this.listeners = {};
      let add = (plugin, type, cb) => {
        if (!this.listeners[type]) this.listeners[type] = [];
        this.listeners[type].push([plugin, cb]);
      };
      for (let plugin of this.plugins) if (typeof plugin === "object") for (let event in plugin) {
        if (!PLUGIN_PROPS[event] && /^[A-Z]/.test(event)) throw new Error(`Unknown event ${event} in ${plugin.postcssPlugin}. Try to update PostCSS (${this.processor.version} now).`);
        if (!NOT_VISITORS[event]) {
          if (typeof plugin[event] === "object") for (let filter in plugin[event]) if (filter === "*") add(plugin, event, plugin[event][filter]);
          else add(plugin, event + "-" + filter.toLowerCase(), plugin[event][filter]);
          else if (typeof plugin[event] === "function") add(plugin, event, plugin[event]);
        }
      }
      this.hasListener = Object.keys(this.listeners).length > 0;
    }
    async runAsync() {
      this.plugin = 0;
      for (let i2 = 0; i2 < this.plugins.length; i2++) {
        let plugin = this.plugins[i2];
        let promise = this.runOnRoot(plugin);
        if (isPromise(promise)) try {
          await promise;
        } catch (error) {
          throw this.handleError(error);
        }
      }
      this.prepareVisitors();
      if (this.hasListener) {
        let root = this.result.root;
        while (!root[isClean]) {
          root[isClean] = true;
          let stack = [toStack(root)];
          while (stack.length > 0) {
            let promise = this.visitTick(stack);
            if (isPromise(promise)) try {
              await promise;
            } catch (e2) {
              let node = stack[stack.length - 1].node;
              throw this.handleError(e2, node);
            }
          }
        }
        if (this.listeners.OnceExit) for (let [plugin, visitor] of this.listeners.OnceExit) {
          this.result.lastPlugin = plugin;
          try {
            if (root.type === "document") {
              let roots = root.nodes.map((subRoot) => visitor(subRoot, this.helpers));
              await Promise.all(roots);
            } else await visitor(root, this.helpers);
          } catch (e2) {
            throw this.handleError(e2);
          }
        }
      }
      this.processed = true;
      return this.stringify();
    }
    runOnRoot(plugin) {
      this.result.lastPlugin = plugin;
      try {
        if (typeof plugin === "object" && plugin.Once) {
          if (this.result.root.type === "document") {
            let roots = this.result.root.nodes.map((root) => plugin.Once(root, this.helpers));
            if (isPromise(roots[0])) return Promise.all(roots);
            return roots;
          }
          return plugin.Once(this.result.root, this.helpers);
        } else if (typeof plugin === "function") return plugin(this.result.root, this.result);
      } catch (error) {
        throw this.handleError(error);
      }
    }
    stringify() {
      if (this.error) throw this.error;
      if (this.stringified) return this.result;
      this.stringified = true;
      this.sync();
      let opts = this.result.opts;
      let str = stringify;
      if (opts.syntax) str = opts.syntax.stringify;
      if (opts.stringifier) str = opts.stringifier;
      if (str.stringify) str = str.stringify;
      let rootSource = this.result.root.source;
      if (opts.map === void 0 && !(rootSource && rootSource.input && rootSource.input.map)) {
        let result = "";
        str(this.result.root, (i2) => {
          result += i2;
        });
        this.result.css = result;
        return this.result;
      }
      let data = new MapGenerator(str, this.result.root, this.result.opts).generate();
      this.result.css = data[0];
      this.result.map = data[1];
      return this.result;
    }
    sync() {
      if (this.error) throw this.error;
      if (this.processed) return this.result;
      this.processed = true;
      if (this.processing) throw this.getAsyncError();
      for (let plugin of this.plugins) if (isPromise(this.runOnRoot(plugin))) throw this.getAsyncError();
      this.prepareVisitors();
      if (this.hasListener) {
        let root = this.result.root;
        while (!root[isClean]) {
          root[isClean] = true;
          this.walkSync(root);
        }
        if (this.listeners.OnceExit) {
          if (root.type === "document") for (let subRoot of root.nodes) this.visitSync(this.listeners.OnceExit, subRoot);
          else this.visitSync(this.listeners.OnceExit, root);
        }
      }
      return this.result;
    }
    then(onFulfilled, onRejected) {
      if (true) {
        if (!("from" in this.opts)) warnOnce("Without `from` option PostCSS could generate wrong source map and will not find Browserslist config. Set it to CSS file path or to `undefined` to prevent this warning.");
      }
      return this.async().then(onFulfilled, onRejected);
    }
    toString() {
      return this.css;
    }
    visitSync(visitors, node) {
      for (let [plugin, visitor] of visitors) {
        this.result.lastPlugin = plugin;
        let promise;
        try {
          promise = visitor(node, this.helpers);
        } catch (e2) {
          throw this.handleError(e2, node.proxyOf);
        }
        if (node.type !== "root" && node.type !== "document" && !node.parent) return true;
        if (isPromise(promise)) throw this.getAsyncError();
      }
    }
    visitTick(stack) {
      let visit2 = stack[stack.length - 1];
      let { node, visitors } = visit2;
      if (node.type !== "root" && node.type !== "document" && !node.parent) {
        stack.pop();
        return;
      }
      if (visitors.length > 0 && visit2.visitorIndex < visitors.length) {
        let [plugin, visitor] = visitors[visit2.visitorIndex];
        visit2.visitorIndex += 1;
        if (visit2.visitorIndex === visitors.length) {
          visit2.visitors = [];
          visit2.visitorIndex = 0;
        }
        this.result.lastPlugin = plugin;
        try {
          return visitor(node.toProxy(), this.helpers);
        } catch (e2) {
          throw this.handleError(e2, node);
        }
      }
      if (visit2.iterator !== 0) {
        let iterator = visit2.iterator;
        if (visit2.descending) {
          visit2.descending = false;
          node.indexes[iterator] += 1;
        }
        let child;
        while (child = node.nodes[node.indexes[iterator]]) {
          if (!child[isClean]) {
            child[isClean] = true;
            visit2.descending = true;
            stack.push(toStack(child));
            return;
          }
          node.indexes[iterator] += 1;
        }
        visit2.iterator = 0;
        delete node.indexes[iterator];
      }
      let events = visit2.events;
      while (visit2.eventIndex < events.length) {
        let event = events[visit2.eventIndex];
        visit2.eventIndex += 1;
        if (event === CHILDREN) {
          if (node.nodes && node.nodes.length) {
            node[isClean] = true;
            visit2.iterator = node.getIterator();
          }
          return;
        } else if (this.listeners[event]) {
          visit2.visitors = this.listeners[event];
          return;
        }
      }
      stack.pop();
    }
    walkSync(node) {
      node[isClean] = true;
      let stack = [{
        eventIndex: 0,
        events: getEvents(node),
        iterator: 0,
        node
      }];
      while (stack.length > 0) {
        let visit2 = stack[stack.length - 1];
        let visitNode = visit2.node;
        if (visit2.iterator !== 0) {
          let iterator = visit2.iterator;
          if (visit2.descending) {
            visit2.descending = false;
            visitNode.indexes[iterator] += 1;
          }
          let child;
          let descended = false;
          while (child = visitNode.nodes[visitNode.indexes[iterator]]) {
            if (!child[isClean]) {
              child[isClean] = true;
              visit2.descending = true;
              stack.push({
                eventIndex: 0,
                events: getEvents(child),
                iterator: 0,
                node: child
              });
              descended = true;
              break;
            }
            visitNode.indexes[iterator] += 1;
          }
          if (descended) continue;
          visit2.iterator = 0;
          delete visitNode.indexes[iterator];
        }
        if (visit2.eventIndex < visit2.events.length) {
          let event = visit2.events[visit2.eventIndex];
          visit2.eventIndex += 1;
          if (event === CHILDREN) {
            if (visitNode.nodes && visitNode.nodes.length) visit2.iterator = visitNode.getIterator();
          } else {
            let visitors = this.listeners[event];
            if (visitors) {
              if (this.visitSync(visitors, visitNode.toProxy())) stack.pop();
            }
          }
          continue;
        }
        stack.pop();
      }
    }
    warnings() {
      return this.sync().warnings();
    }
  };
  LazyResult.registerPostcss = (dependant) => {
    postcss = dependant;
  };
  module2.exports = LazyResult;
  LazyResult.default = LazyResult;
  Root.registerLazyResult(LazyResult);
  Document2.registerLazyResult(LazyResult);
});
var require_no_work_result = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var MapGenerator = require_map_generator();
  var parse = require_parse();
  var Result = require_result();
  var stringify = require_stringify();
  var warnOnce = require_warn_once();
  var NoWorkResult = class {
    get content() {
      return this.result.css;
    }
    get css() {
      return this.result.css;
    }
    get map() {
      return this.result.map;
    }
    get messages() {
      return [];
    }
    get opts() {
      return this.result.opts;
    }
    get processor() {
      return this.result.processor;
    }
    get root() {
      if (this._root) return this._root;
      let root;
      let parser = parse;
      try {
        root = parser(this._css, this._opts);
      } catch (error) {
        this.error = error;
      }
      if (this.error) throw this.error;
      else {
        this._root = root;
        return root;
      }
    }
    get [Symbol.toStringTag]() {
      return "NoWorkResult";
    }
    constructor(processor, css, opts) {
      css = css.toString();
      this.stringified = false;
      this._processor = processor;
      this._css = css;
      this._opts = opts;
      this._map = void 0;
      let str = stringify;
      this.result = new Result(this._processor, void 0, this._opts);
      this.result.css = css;
      let self2 = this;
      Object.defineProperty(this.result, "root", { get() {
        return self2.root;
      } });
      let map = new MapGenerator(str, void 0, this._opts, css);
      if (map.isMap()) {
        let [generatedCSS, generatedMap] = map.generate();
        if (generatedCSS) this.result.css = generatedCSS;
        if (generatedMap) this.result.map = generatedMap;
      } else {
        map.clearAnnotation();
        this.result.css = map.css;
      }
    }
    async() {
      if (this.error) return Promise.reject(this.error);
      return Promise.resolve(this.result);
    }
    catch(onRejected) {
      return this.async().catch(onRejected);
    }
    finally(onFinally) {
      return this.async().finally(onFinally);
    }
    sync() {
      if (this.error) throw this.error;
      return this.result;
    }
    then(onFulfilled, onRejected) {
      if (true) {
        if (!("from" in this._opts)) warnOnce("Without `from` option PostCSS could generate wrong source map and will not find Browserslist config. Set it to CSS file path or to `undefined` to prevent this warning.");
      }
      return this.async().then(onFulfilled, onRejected);
    }
    toString() {
      return this._css;
    }
    warnings() {
      return [];
    }
  };
  module2.exports = NoWorkResult;
  NoWorkResult.default = NoWorkResult;
});
var require_processor = /* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var Document2 = require_document();
  var LazyResult = require_lazy_result();
  var NoWorkResult = require_no_work_result();
  var Root = require_root();
  var Processor = class {
    constructor(plugins = []) {
      this.version = "8.5.32";
      this.plugins = this.normalize(plugins);
    }
    normalize(plugins) {
      let normalized = [];
      for (let i2 of plugins) {
        if (i2.postcss === true) i2 = i2();
        else if (i2.postcss) i2 = i2.postcss;
        if (typeof i2 === "object" && Array.isArray(i2.plugins)) normalized = normalized.concat(i2.plugins);
        else if (typeof i2 === "object" && i2.postcssPlugin) normalized.push(i2);
        else if (typeof i2 === "function") normalized.push(i2);
        else if (typeof i2 === "object" && (i2.parse || i2.stringify)) {
          if (true) throw new Error("PostCSS syntaxes cannot be used as plugins. Instead, please use one of the syntax/parser/stringifier options as outlined in your PostCSS runner documentation.");
        } else throw new Error(i2 + " is not a PostCSS plugin");
      }
      return normalized;
    }
    process(css, opts = {}) {
      if (!this.plugins.length && !opts.parser && !opts.stringifier && !opts.syntax) return new NoWorkResult(this, css, opts);
      else return new LazyResult(this, css, opts);
    }
    use(plugin) {
      this.plugins = this.plugins.concat(this.normalize([plugin]));
      return this;
    }
  };
  module2.exports = Processor;
  Processor.default = Processor;
  Root.registerProcessor(Processor);
  Document2.registerProcessor(Processor);
});
var import_postcss = /* @__PURE__ */ __toESM((/* @__PURE__ */ __commonJSMin((exports2, module2) => {
  var AtRule = require_at_rule();
  var Comment = require_comment();
  var Container = require_container();
  var CssSyntaxError = require_css_syntax_error();
  var Declaration = require_declaration();
  var Document2 = require_document();
  var fromJSON = require_fromJSON();
  var Input = require_input();
  var LazyResult = require_lazy_result();
  var list = require_list();
  var Node2 = require_node();
  var parse = require_parse();
  var Processor = require_processor();
  var Result = require_result();
  var Root = require_root();
  var Rule = require_rule();
  var stringify = require_stringify();
  var Warning = require_warning();
  function postcss(...plugins) {
    if (plugins.length === 1 && Array.isArray(plugins[0])) plugins = plugins[0];
    return new Processor(plugins);
  }
  postcss.plugin = function plugin(name, initializer) {
    let warningPrinted = false;
    function creator(...args) {
      if (console && console.warn && !warningPrinted) {
        warningPrinted = true;
        console.warn(name + ": postcss.plugin was deprecated. Migration guide:\nhttps://evilmartians.com/chronicles/postcss-8-plugin-migration");
        if (process.env.LANG && process.env.LANG.startsWith("zh"))
          console.warn(name + ": \u91CC\u9762 postcss.plugin \u88AB\u5F03\u7528. \u8FC1\u79FB\u6307\u5357:\nhttps://www.w3ctech.com/topic/2226");
      }
      let transformer = initializer(...args);
      transformer.postcssPlugin = name;
      transformer.postcssVersion = new Processor().version;
      return transformer;
    }
    let cache;
    Object.defineProperty(creator, "postcss", { get() {
      if (!cache) cache = creator();
      return cache;
    } });
    creator.process = function(css, processOpts, pluginOpts) {
      return postcss([creator(pluginOpts)]).process(css, processOpts);
    };
    return creator;
  };
  postcss.stringify = stringify;
  postcss.parse = parse;
  postcss.fromJSON = fromJSON;
  postcss.list = list;
  postcss.comment = (defaults) => new Comment(defaults);
  postcss.atRule = (defaults) => new AtRule(defaults);
  postcss.decl = (defaults) => new Declaration(defaults);
  postcss.rule = (defaults) => new Rule(defaults);
  postcss.root = (defaults) => new Root(defaults);
  postcss.document = (defaults) => new Document2(defaults);
  postcss.CssSyntaxError = CssSyntaxError;
  postcss.Declaration = Declaration;
  postcss.Container = Container;
  postcss.Processor = Processor;
  postcss.Document = Document2;
  postcss.Comment = Comment;
  postcss.Warning = Warning;
  postcss.AtRule = AtRule;
  postcss.Result = Result;
  postcss.Input = Input;
  postcss.Rule = Rule;
  postcss.Root = Root;
  postcss.Node = Node2;
  LazyResult.registerPostcss(postcss);
  module2.exports = postcss;
  postcss.default = postcss;
}))(), 1);
import_postcss.default;
import_postcss.default.stringify;
import_postcss.default.fromJSON;
import_postcss.default.plugin;
import_postcss.default.parse;
import_postcss.default.list;
import_postcss.default.document;
import_postcss.default.comment;
import_postcss.default.atRule;
import_postcss.default.rule;
import_postcss.default.decl;
import_postcss.default.root;
import_postcss.default.CssSyntaxError;
import_postcss.default.Declaration;
import_postcss.default.Container;
import_postcss.default.Processor;
import_postcss.default.Document;
import_postcss.default.Comment;
import_postcss.default.Warning;
import_postcss.default.AtRule;
import_postcss.default.Result;
import_postcss.default.Input;
import_postcss.default.Rule;
import_postcss.default.Root;
import_postcss.default.Node;
var NodeType$1 = /* @__PURE__ */ function(NodeType2) {
  NodeType2[NodeType2["Document"] = 0] = "Document";
  NodeType2[NodeType2["DocumentType"] = 1] = "DocumentType";
  NodeType2[NodeType2["Element"] = 2] = "Element";
  NodeType2[NodeType2["Text"] = 3] = "Text";
  NodeType2[NodeType2["CDATA"] = 4] = "CDATA";
  NodeType2[NodeType2["Comment"] = 5] = "Comment";
  return NodeType2;
}({});
function parseCSSText(cssText) {
  const res = {};
  const listDelimiter = /;(?![^(]*\))/g;
  const propertyDelimiter = /:(.+)/;
  cssText.replace(/\/\*.*?\*\//g, "").split(listDelimiter).forEach(function(item) {
    if (item) {
      const tmp = item.split(propertyDelimiter);
      tmp.length > 1 && (res[camelize(tmp[0].trim())] = tmp[1].trim());
    }
  });
  return res;
}
function toCSSText(style) {
  const properties = [];
  for (const name in style) {
    const value = style[name];
    if (typeof value !== "string") continue;
    const normalizedName = hyphenate(name);
    properties.push(`${normalizedName}: ${value};`);
  }
  return properties.join(" ");
}
var camelizeRE = /-([a-z])/g;
var CUSTOM_PROPERTY_REGEX = /^--[a-zA-Z0-9-]+$/;
var camelize = (str) => {
  if (CUSTOM_PROPERTY_REGEX.test(str)) return str;
  return str.replace(camelizeRE, (_, c2) => c2 ? c2.toUpperCase() : "");
};
var hyphenateRE = /\B([A-Z])/g;
var hyphenate = (str) => {
  return str.replace(hyphenateRE, "-$1").toLowerCase();
};
var BaseRRNode = class BaseRRNode2 {
  constructor(..._args) {
    __publicField(this, "parentElement", null);
    __publicField(this, "parentNode", null);
    __publicField(this, "ownerDocument");
    __publicField(this, "firstChild", null);
    __publicField(this, "lastChild", null);
    __publicField(this, "previousSibling", null);
    __publicField(this, "nextSibling", null);
    __publicField(this, "ELEMENT_NODE", 1);
    __publicField(this, "TEXT_NODE", 3);
    __publicField(this, "nodeType");
    __publicField(this, "nodeName");
    __publicField(this, "RRNodeType");
  }
  get childNodes() {
    const childNodes2 = [];
    let childIterator = this.firstChild;
    while (childIterator) {
      childNodes2.push(childIterator);
      childIterator = childIterator.nextSibling;
    }
    return childNodes2;
  }
  contains(node) {
    if (!(node instanceof BaseRRNode2)) return false;
    else if (node.ownerDocument !== this.ownerDocument) return false;
    else if (node === this) return true;
    while (node.parentNode) {
      if (node.parentNode === this) return true;
      node = node.parentNode;
    }
    return false;
  }
  appendChild(_newChild) {
    throw new Error(`RRDomException: Failed to execute 'appendChild' on 'RRNode': This RRNode type does not support this method.`);
  }
  insertBefore(_newChild, _refChild) {
    throw new Error(`RRDomException: Failed to execute 'insertBefore' on 'RRNode': This RRNode type does not support this method.`);
  }
  removeChild(_node) {
    throw new Error(`RRDomException: Failed to execute 'removeChild' on 'RRNode': This RRNode type does not support this method.`);
  }
  toString() {
    return "RRNode";
  }
};
var BaseRRDocument = class BaseRRDocument2 extends BaseRRNode {
  constructor(...args) {
    super(args);
    __publicField(this, "nodeType", 9);
    __publicField(this, "nodeName", "#document");
    __publicField(this, "compatMode", "CSS1Compat");
    __publicField(this, "RRNodeType", NodeType$1.Document);
    __publicField(this, "textContent", null);
    this.ownerDocument = this;
  }
  get documentElement() {
    return this.childNodes.find((node) => node.RRNodeType === NodeType$1.Element && node.tagName === "HTML") || null;
  }
  get body() {
    var _a2;
    return ((_a2 = this.documentElement) == null ? void 0 : _a2.childNodes.find((node) => node.RRNodeType === NodeType$1.Element && node.tagName === "BODY")) || null;
  }
  get head() {
    var _a2;
    return ((_a2 = this.documentElement) == null ? void 0 : _a2.childNodes.find((node) => node.RRNodeType === NodeType$1.Element && node.tagName === "HEAD")) || null;
  }
  get implementation() {
    return this;
  }
  get firstElementChild() {
    return this.documentElement;
  }
  appendChild(newChild) {
    const nodeType = newChild.RRNodeType;
    if (nodeType === NodeType$1.Element || nodeType === NodeType$1.DocumentType) {
      if (this.childNodes.some((s2) => s2.RRNodeType === nodeType)) throw new Error(`RRDomException: Failed to execute 'appendChild' on 'RRNode': Only one ${nodeType === NodeType$1.Element ? "RRElement" : "RRDoctype"} on RRDocument allowed.`);
    }
    const child = appendChild(this, newChild);
    child.parentElement = null;
    return child;
  }
  insertBefore(newChild, refChild) {
    const nodeType = newChild.RRNodeType;
    if (nodeType === NodeType$1.Element || nodeType === NodeType$1.DocumentType) {
      if (this.childNodes.some((s2) => s2.RRNodeType === nodeType)) throw new Error(`RRDomException: Failed to execute 'insertBefore' on 'RRNode': Only one ${nodeType === NodeType$1.Element ? "RRElement" : "RRDoctype"} on RRDocument allowed.`);
    }
    const child = insertBefore(this, newChild, refChild);
    child.parentElement = null;
    return child;
  }
  removeChild(node) {
    return removeChild(this, node);
  }
  open() {
    this.firstChild = null;
    this.lastChild = null;
  }
  close() {
  }
  /**
  * Adhoc implementation for setting xhtml namespace in rebuilt.ts (rrweb-snapshot).
  * There are two lines used this function:
  * 1. doc.write('\<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ""\>')
  * 2. doc.write('\<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" ""\>')
  */
  write(content) {
    let publicId;
    if (content === '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "">') publicId = "-//W3C//DTD XHTML 1.0 Transitional//EN";
    else if (content === '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "">') publicId = "-//W3C//DTD HTML 4.0 Transitional//EN";
    if (publicId) {
      const doctype = this.createDocumentType("html", publicId, "");
      this.open();
      this.appendChild(doctype);
    }
  }
  createDocument(_namespace, _qualifiedName, _doctype) {
    return new BaseRRDocument2();
  }
  createDocumentType(qualifiedName, publicId, systemId) {
    const doctype = new BaseRRDocumentType(qualifiedName, publicId, systemId);
    doctype.ownerDocument = this;
    return doctype;
  }
  createElement(tagName) {
    const element = new BaseRRElement(tagName);
    element.ownerDocument = this;
    return element;
  }
  createElementNS(_namespaceURI, qualifiedName) {
    return this.createElement(qualifiedName);
  }
  createTextNode(data) {
    const text = new BaseRRText(data);
    text.ownerDocument = this;
    return text;
  }
  createComment(data) {
    const comment = new BaseRRComment(data);
    comment.ownerDocument = this;
    return comment;
  }
  createCDATASection(data) {
    const CDATASection = new BaseRRCDATASection(data);
    CDATASection.ownerDocument = this;
    return CDATASection;
  }
  toString() {
    return "RRDocument";
  }
};
var BaseRRDocumentType = class extends BaseRRNode {
  constructor(qualifiedName, publicId, systemId) {
    super();
    __publicField(this, "nodeType", 10);
    __publicField(this, "RRNodeType", NodeType$1.DocumentType);
    __publicField(this, "name");
    __publicField(this, "publicId");
    __publicField(this, "systemId");
    __publicField(this, "textContent", null);
    this.name = qualifiedName;
    this.publicId = publicId;
    this.systemId = systemId;
    this.nodeName = qualifiedName;
  }
  toString() {
    return "RRDocumentType";
  }
};
var BaseRRElement = class extends BaseRRNode {
  constructor(tagName) {
    super();
    __publicField(this, "nodeType", 1);
    __publicField(this, "RRNodeType", NodeType$1.Element);
    __publicField(this, "tagName");
    __publicField(this, "attributes", {});
    __publicField(this, "shadowRoot", null);
    __publicField(this, "scrollLeft");
    __publicField(this, "scrollTop");
    this.tagName = tagName.toUpperCase();
    this.nodeName = tagName.toUpperCase();
  }
  get textContent() {
    let result = "";
    this.childNodes.forEach((node) => result += node.textContent);
    return result;
  }
  set textContent(textContent2) {
    this.firstChild = null;
    this.lastChild = null;
    this.appendChild(this.ownerDocument.createTextNode(textContent2));
  }
  get classList() {
    return new ClassList(this.attributes.class, (newClassName) => {
      this.attributes.class = newClassName;
    });
  }
  get id() {
    return this.attributes.id || "";
  }
  get className() {
    return this.attributes.class || "";
  }
  get style() {
    const style = this.attributes.style ? parseCSSText(this.attributes.style) : {};
    const hyphenateRE2 = /\B([A-Z])/g;
    style.setProperty = (name, value, priority) => {
      if (hyphenateRE2.test(name)) return;
      const normalizedName = camelize(name);
      if (!value) delete style[normalizedName];
      else style[normalizedName] = value;
      if (priority === "important") style[normalizedName] += " !important";
      this.attributes.style = toCSSText(style);
    };
    style.removeProperty = (name) => {
      if (hyphenateRE2.test(name)) return "";
      const normalizedName = camelize(name);
      const value = style[normalizedName] || "";
      delete style[normalizedName];
      this.attributes.style = toCSSText(style);
      return value;
    };
    return style;
  }
  getAttribute(name) {
    if (this.attributes[name] === void 0) return null;
    return this.attributes[name];
  }
  setAttribute(name, attribute) {
    this.attributes[name] = attribute;
  }
  setAttributeNS(_namespace, qualifiedName, value) {
    this.setAttribute(qualifiedName, value);
  }
  removeAttribute(name) {
    delete this.attributes[name];
  }
  appendChild(newChild) {
    return appendChild(this, newChild);
  }
  insertBefore(newChild, refChild) {
    return insertBefore(this, newChild, refChild);
  }
  removeChild(node) {
    return removeChild(this, node);
  }
  attachShadow(_init) {
    const shadowRoot2 = this.ownerDocument.createElement("SHADOWROOT");
    this.shadowRoot = shadowRoot2;
    return shadowRoot2;
  }
  dispatchEvent(_event) {
    return true;
  }
  toString() {
    let attributeString = "";
    for (const attribute in this.attributes) attributeString += `${attribute}="${this.attributes[attribute]}" `;
    return `${this.tagName} ${attributeString}`;
  }
};
var BaseRRMediaElement = class extends BaseRRElement {
  constructor() {
    super(...arguments);
    __publicField(this, "currentTime");
    __publicField(this, "volume");
    __publicField(this, "paused");
    __publicField(this, "muted");
    __publicField(this, "playbackRate");
    __publicField(this, "loop");
  }
  attachShadow(_init) {
    throw new Error(`RRDomException: Failed to execute 'attachShadow' on 'RRElement': This RRElement does not support attachShadow`);
  }
  play() {
    this.paused = false;
  }
  pause() {
    this.paused = true;
  }
};
var BaseRRDialogElement = class extends BaseRRElement {
  constructor() {
    super(...arguments);
    __publicField(this, "tagName", "DIALOG");
    __publicField(this, "nodeName", "DIALOG");
  }
  get isModal() {
    return this.getAttribute("rr_open_mode") === "modal";
  }
  get open() {
    return this.getAttribute("open") !== null;
  }
  close() {
    this.removeAttribute("open");
    this.removeAttribute("rr_open_mode");
  }
  show() {
    this.setAttribute("open", "");
    this.setAttribute("rr_open_mode", "non-modal");
  }
  showModal() {
    this.setAttribute("open", "");
    this.setAttribute("rr_open_mode", "modal");
  }
};
var BaseRRText = class extends BaseRRNode {
  constructor(data) {
    super();
    __publicField(this, "nodeType", 3);
    __publicField(this, "nodeName", "#text");
    __publicField(this, "RRNodeType", NodeType$1.Text);
    __publicField(this, "data");
    this.data = data;
  }
  get textContent() {
    return this.data;
  }
  set textContent(textContent2) {
    this.data = textContent2;
  }
  toString() {
    return `RRText text=${JSON.stringify(this.data)}`;
  }
};
var BaseRRComment = class extends BaseRRNode {
  constructor(data) {
    super();
    __publicField(this, "nodeType", 8);
    __publicField(this, "nodeName", "#comment");
    __publicField(this, "RRNodeType", NodeType$1.Comment);
    __publicField(this, "data");
    this.data = data;
  }
  get textContent() {
    return this.data;
  }
  set textContent(textContent2) {
    this.data = textContent2;
  }
  toString() {
    return `RRComment text=${JSON.stringify(this.data)}`;
  }
};
var BaseRRCDATASection = class extends BaseRRNode {
  constructor(data) {
    super();
    __publicField(this, "nodeName", "#cdata-section");
    __publicField(this, "nodeType", 4);
    __publicField(this, "RRNodeType", NodeType$1.CDATA);
    __publicField(this, "data");
    this.data = data;
  }
  get textContent() {
    return this.data;
  }
  set textContent(textContent2) {
    this.data = textContent2;
  }
  toString() {
    return `RRCDATASection data=${JSON.stringify(this.data)}`;
  }
};
var ClassList = class {
  constructor(classText, onChange) {
    __publicField(this, "onChange");
    __publicField(this, "classes", []);
    __publicField(this, "add", (...classNames) => {
      for (const item of classNames) {
        const className = String(item);
        if (this.classes.indexOf(className) >= 0) continue;
        this.classes.push(className);
      }
      this.onChange && this.onChange(this.classes.join(" "));
    });
    __publicField(this, "remove", (...classNames) => {
      this.classes = this.classes.filter((item) => classNames.indexOf(item) === -1);
      this.onChange && this.onChange(this.classes.join(" "));
    });
    if (classText) {
      const classes = classText.trim().split(/\s+/);
      this.classes.push(...classes);
    }
    this.onChange = onChange;
  }
};
function appendChild(parent, newChild) {
  if (newChild.parentNode) newChild.parentNode.removeChild(newChild);
  if (parent.lastChild) {
    parent.lastChild.nextSibling = newChild;
    newChild.previousSibling = parent.lastChild;
  } else {
    parent.firstChild = newChild;
    newChild.previousSibling = null;
  }
  parent.lastChild = newChild;
  newChild.nextSibling = null;
  newChild.parentNode = parent;
  newChild.parentElement = parent;
  newChild.ownerDocument = parent.ownerDocument;
  return newChild;
}
function insertBefore(parent, newChild, refChild) {
  if (!refChild) return appendChild(parent, newChild);
  if (refChild.parentNode !== parent) throw new Error("Failed to execute 'insertBefore' on 'RRNode': The RRNode before which the new node is to be inserted is not a child of this RRNode.");
  if (newChild === refChild) return newChild;
  if (newChild.parentNode) newChild.parentNode.removeChild(newChild);
  newChild.previousSibling = refChild.previousSibling;
  refChild.previousSibling = newChild;
  newChild.nextSibling = refChild;
  if (newChild.previousSibling) newChild.previousSibling.nextSibling = newChild;
  else parent.firstChild = newChild;
  newChild.parentElement = parent;
  newChild.parentNode = parent;
  newChild.ownerDocument = parent.ownerDocument;
  return newChild;
}
function removeChild(parent, child) {
  if (child.parentNode !== parent) throw new Error("Failed to execute 'removeChild' on 'RRNode': The RRNode to be removed is not a child of this RRNode.");
  if (child.previousSibling) child.previousSibling.nextSibling = child.nextSibling;
  else parent.firstChild = child.nextSibling;
  if (child.nextSibling) child.nextSibling.previousSibling = child.previousSibling;
  else parent.lastChild = child.previousSibling;
  child.previousSibling = null;
  child.nextSibling = null;
  child.parentElement = null;
  child.parentNode = null;
  return child;
}
var NodeType$2 = /* @__PURE__ */ function(NodeType2) {
  NodeType2[NodeType2["PLACEHOLDER"] = 0] = "PLACEHOLDER";
  NodeType2[NodeType2["ELEMENT_NODE"] = 1] = "ELEMENT_NODE";
  NodeType2[NodeType2["ATTRIBUTE_NODE"] = 2] = "ATTRIBUTE_NODE";
  NodeType2[NodeType2["TEXT_NODE"] = 3] = "TEXT_NODE";
  NodeType2[NodeType2["CDATA_SECTION_NODE"] = 4] = "CDATA_SECTION_NODE";
  NodeType2[NodeType2["ENTITY_REFERENCE_NODE"] = 5] = "ENTITY_REFERENCE_NODE";
  NodeType2[NodeType2["ENTITY_NODE"] = 6] = "ENTITY_NODE";
  NodeType2[NodeType2["PROCESSING_INSTRUCTION_NODE"] = 7] = "PROCESSING_INSTRUCTION_NODE";
  NodeType2[NodeType2["COMMENT_NODE"] = 8] = "COMMENT_NODE";
  NodeType2[NodeType2["DOCUMENT_NODE"] = 9] = "DOCUMENT_NODE";
  NodeType2[NodeType2["DOCUMENT_TYPE_NODE"] = 10] = "DOCUMENT_TYPE_NODE";
  NodeType2[NodeType2["DOCUMENT_FRAGMENT_NODE"] = 11] = "DOCUMENT_FRAGMENT_NODE";
  return NodeType2;
}({});
var NAMESPACES = {
  svg: "http://www.w3.org/2000/svg",
  "xlink:href": "http://www.w3.org/1999/xlink",
  xmlns: "http://www.w3.org/2000/xmlns/"
};
var SVGTagMap = {
  altglyph: "altGlyph",
  altglyphdef: "altGlyphDef",
  altglyphitem: "altGlyphItem",
  animatecolor: "animateColor",
  animatemotion: "animateMotion",
  animatetransform: "animateTransform",
  clippath: "clipPath",
  feblend: "feBlend",
  fecolormatrix: "feColorMatrix",
  fecomponenttransfer: "feComponentTransfer",
  fecomposite: "feComposite",
  feconvolvematrix: "feConvolveMatrix",
  fediffuselighting: "feDiffuseLighting",
  fedisplacementmap: "feDisplacementMap",
  fedistantlight: "feDistantLight",
  fedropshadow: "feDropShadow",
  feflood: "feFlood",
  fefunca: "feFuncA",
  fefuncb: "feFuncB",
  fefuncg: "feFuncG",
  fefuncr: "feFuncR",
  fegaussianblur: "feGaussianBlur",
  feimage: "feImage",
  femerge: "feMerge",
  femergenode: "feMergeNode",
  femorphology: "feMorphology",
  feoffset: "feOffset",
  fepointlight: "fePointLight",
  fespecularlighting: "feSpecularLighting",
  fespotlight: "feSpotLight",
  fetile: "feTile",
  feturbulence: "feTurbulence",
  foreignobject: "foreignObject",
  glyphref: "glyphRef",
  lineargradient: "linearGradient",
  radialgradient: "radialGradient"
};
var createdNodeSet = null;
function diff(oldTree, newTree, replayer, rrnodeMirror = newTree.mirror || newTree.ownerDocument.mirror) {
  oldTree = diffBeforeUpdatingChildren(oldTree, newTree, replayer, rrnodeMirror);
  diffChildren(oldTree, newTree, replayer, rrnodeMirror);
  diffAfterUpdatingChildren(oldTree, newTree, replayer);
}
function diffBeforeUpdatingChildren(oldTree, newTree, replayer, rrnodeMirror) {
  var _a2;
  if (replayer.afterAppend && !createdNodeSet) {
    createdNodeSet = /* @__PURE__ */ new WeakSet();
    setTimeout(() => {
      createdNodeSet = null;
    }, 0);
  }
  if (!sameNodeType(oldTree, newTree)) {
    const calibratedOldTree = createOrGetNode(newTree, replayer.mirror, rrnodeMirror);
    (_a2 = oldTree.parentNode) == null ? void 0 : _a2.replaceChild(calibratedOldTree, oldTree);
    oldTree = calibratedOldTree;
  }
  switch (newTree.RRNodeType) {
    case NodeType$1.Document:
      if (!nodeMatching(oldTree, newTree, replayer.mirror, rrnodeMirror)) {
        const newMeta = rrnodeMirror.getMeta(newTree);
        if (newMeta) {
          replayer.mirror.removeNodeFromMap(oldTree);
          oldTree.close();
          oldTree.open();
          replayer.mirror.add(oldTree, newMeta);
          createdNodeSet == null ? void 0 : createdNodeSet.add(oldTree);
        }
      }
      break;
    case NodeType$1.Element: {
      const oldElement = oldTree;
      const newRRElement = newTree;
      switch (newRRElement.tagName) {
        case "IFRAME": {
          const oldContentDocument = oldTree.contentDocument;
          if (!oldContentDocument) break;
          diff(oldContentDocument, newTree.contentDocument, replayer, rrnodeMirror);
          break;
        }
      }
      if (newRRElement.shadowRoot) {
        if (!oldElement.shadowRoot) oldElement.attachShadow({ mode: "open" });
        diffChildren(oldElement.shadowRoot, newRRElement.shadowRoot, replayer, rrnodeMirror);
      }
      diffProps(oldElement, newRRElement, rrnodeMirror);
      break;
    }
  }
  return oldTree;
}
function diffAfterUpdatingChildren(oldTree, newTree, replayer) {
  var _a2;
  switch (newTree.RRNodeType) {
    case NodeType$1.Document: {
      const scrollData = newTree.scrollData;
      scrollData && replayer.applyScroll(scrollData, true);
      break;
    }
    case NodeType$1.Element: {
      const oldElement = oldTree;
      const newRRElement = newTree;
      newRRElement.scrollData && replayer.applyScroll(newRRElement.scrollData, true);
      newRRElement.inputData && replayer.applyInput(newRRElement.inputData);
      switch (newRRElement.tagName) {
        case "AUDIO":
        case "VIDEO": {
          const oldMediaElement = oldTree;
          const newMediaRRElement = newRRElement;
          if (newMediaRRElement.paused !== void 0) newMediaRRElement.paused ? oldMediaElement.pause() : oldMediaElement.play();
          if (newMediaRRElement.muted !== void 0) oldMediaElement.muted = newMediaRRElement.muted;
          if (newMediaRRElement.volume !== void 0) oldMediaElement.volume = newMediaRRElement.volume;
          if (newMediaRRElement.currentTime !== void 0) oldMediaElement.currentTime = newMediaRRElement.currentTime;
          if (newMediaRRElement.playbackRate !== void 0) oldMediaElement.playbackRate = newMediaRRElement.playbackRate;
          if (newMediaRRElement.loop !== void 0) oldMediaElement.loop = newMediaRRElement.loop;
          break;
        }
        case "CANVAS": {
          const rrCanvasElement = newTree;
          if (rrCanvasElement.rr_dataURL !== null) {
            const image = document.createElement("img");
            image.onload = () => {
              const ctx = oldElement.getContext("2d");
              if (ctx) ctx.drawImage(image, 0, 0, image.width, image.height);
            };
            image.src = rrCanvasElement.rr_dataURL;
          }
          rrCanvasElement.canvasMutations.forEach((canvasMutation2) => replayer.applyCanvas(canvasMutation2.event, canvasMutation2.mutation, oldTree));
          break;
        }
        case "STYLE": {
          const styleSheet = oldElement.sheet;
          styleSheet && newTree.rules.forEach((data) => replayer.applyStyleSheetMutation(data, styleSheet));
          break;
        }
        case "DIALOG": {
          const dialog = oldElement;
          const rrDialog = newRRElement;
          const wasOpen = dialog.open;
          const wasModal = dialog.matches("dialog:modal");
          const shouldBeOpen = rrDialog.open;
          const shouldBeModal = rrDialog.isModal;
          const modalChanged = wasModal !== shouldBeModal;
          const openChanged = wasOpen !== shouldBeOpen;
          if (modalChanged || wasOpen && openChanged) dialog.close();
          if (shouldBeOpen && (openChanged || modalChanged)) try {
            if (shouldBeModal) dialog.showModal();
            else dialog.show();
          } catch (e2) {
            console.warn(e2);
          }
          break;
        }
      }
      break;
    }
    case NodeType$1.Text:
    case NodeType$1.Comment:
    case NodeType$1.CDATA:
      if (oldTree.textContent !== newTree.data) oldTree.textContent = newTree.data;
  }
  if (createdNodeSet == null ? void 0 : createdNodeSet.has(oldTree)) {
    createdNodeSet.delete(oldTree);
    (_a2 = replayer.afterAppend) == null ? void 0 : _a2.call(replayer, oldTree, replayer.mirror.getId(oldTree));
  }
}
function diffProps(oldTree, newTree, rrnodeMirror) {
  var _a2;
  const oldAttributes = oldTree.attributes;
  const newAttributes = newTree.attributes;
  for (const name in newAttributes) {
    const newValue = newAttributes[name];
    if (((_a2 = rrnodeMirror.getMeta(newTree)) == null ? void 0 : _a2.isSVG) && NAMESPACES[name]) oldTree.setAttributeNS(NAMESPACES[name], name, newValue);
    else if (newTree.tagName === "CANVAS" && name === "rr_dataURL") {
      const image = document.createElement("img");
      image.src = newValue;
      image.onload = () => {
        const ctx = oldTree.getContext("2d");
        if (ctx) ctx.drawImage(image, 0, 0, image.width, image.height);
      };
    } else if (newTree.tagName === "IFRAME" && name === "srcdoc") continue;
    else try {
      oldTree.setAttribute(name, newValue);
    } catch (err) {
      console.warn(err);
    }
  }
  for (const { name } of Array.from(oldAttributes)) if (!(name in newAttributes)) oldTree.removeAttribute(name);
  newTree.scrollLeft && (oldTree.scrollLeft = newTree.scrollLeft);
  newTree.scrollTop && (oldTree.scrollTop = newTree.scrollTop);
}
function diffChildren(oldTree, newTree, replayer, rrnodeMirror) {
  const oldChildren = Array.from(oldTree.childNodes);
  const newChildren = newTree.childNodes;
  if (oldChildren.length === 0 && newChildren.length === 0) return;
  let oldStartIndex = 0, oldEndIndex = oldChildren.length - 1, newStartIndex = 0, newEndIndex = newChildren.length - 1;
  let oldStartNode = oldChildren[oldStartIndex], oldEndNode = oldChildren[oldEndIndex], newStartNode = newChildren[newStartIndex], newEndNode = newChildren[newEndIndex];
  let oldIdToIndex = void 0, indexInOld = void 0;
  while (oldStartIndex <= oldEndIndex && newStartIndex <= newEndIndex) if (oldStartNode === void 0) oldStartNode = oldChildren[++oldStartIndex];
  else if (oldEndNode === void 0) oldEndNode = oldChildren[--oldEndIndex];
  else if (nodeMatching(oldStartNode, newStartNode, replayer.mirror, rrnodeMirror)) {
    oldStartNode = oldChildren[++oldStartIndex];
    newStartNode = newChildren[++newStartIndex];
  } else if (nodeMatching(oldEndNode, newEndNode, replayer.mirror, rrnodeMirror)) {
    oldEndNode = oldChildren[--oldEndIndex];
    newEndNode = newChildren[--newEndIndex];
  } else if (nodeMatching(oldStartNode, newEndNode, replayer.mirror, rrnodeMirror)) {
    try {
      oldTree.insertBefore(oldStartNode, oldEndNode.nextSibling);
    } catch (e2) {
      console.warn(e2);
    }
    oldStartNode = oldChildren[++oldStartIndex];
    newEndNode = newChildren[--newEndIndex];
  } else if (nodeMatching(oldEndNode, newStartNode, replayer.mirror, rrnodeMirror)) {
    try {
      oldTree.insertBefore(oldEndNode, oldStartNode);
    } catch (e2) {
      console.warn(e2);
    }
    oldEndNode = oldChildren[--oldEndIndex];
    newStartNode = newChildren[++newStartIndex];
  } else {
    if (!oldIdToIndex) {
      oldIdToIndex = {};
      for (let i2 = oldStartIndex; i2 <= oldEndIndex; i2++) {
        const oldChild2 = oldChildren[i2];
        if (oldChild2 && replayer.mirror.hasNode(oldChild2)) oldIdToIndex[replayer.mirror.getId(oldChild2)] = i2;
      }
    }
    indexInOld = oldIdToIndex[rrnodeMirror.getId(newStartNode)];
    const nodeToMove = oldChildren[indexInOld];
    if (indexInOld !== void 0 && nodeToMove && nodeMatching(nodeToMove, newStartNode, replayer.mirror, rrnodeMirror)) {
      try {
        oldTree.insertBefore(nodeToMove, oldStartNode);
      } catch (e2) {
        console.warn(e2);
      }
      oldChildren[indexInOld] = void 0;
    } else {
      const newNode = createOrGetNode(newStartNode, replayer.mirror, rrnodeMirror);
      if (oldTree.nodeName === "#document" && oldStartNode && (newNode.nodeType === newNode.DOCUMENT_TYPE_NODE && oldStartNode.nodeType === oldStartNode.DOCUMENT_TYPE_NODE || newNode.nodeType === newNode.ELEMENT_NODE && oldStartNode.nodeType === oldStartNode.ELEMENT_NODE)) {
        oldTree.removeChild(oldStartNode);
        replayer.mirror.removeNodeFromMap(oldStartNode);
        oldStartNode = oldChildren[++oldStartIndex];
      }
      try {
        oldTree.insertBefore(newNode, oldStartNode || null);
      } catch (e2) {
        console.warn(e2);
      }
    }
    newStartNode = newChildren[++newStartIndex];
  }
  if (oldStartIndex > oldEndIndex) {
    const referenceRRNode = newChildren[newEndIndex + 1];
    let referenceNode = null;
    if (referenceRRNode) referenceNode = replayer.mirror.getNode(rrnodeMirror.getId(referenceRRNode));
    for (; newStartIndex <= newEndIndex; ++newStartIndex) {
      const newNode = createOrGetNode(newChildren[newStartIndex], replayer.mirror, rrnodeMirror);
      try {
        oldTree.insertBefore(newNode, referenceNode);
      } catch (e2) {
        console.warn(e2);
      }
    }
  } else if (newStartIndex > newEndIndex) for (; oldStartIndex <= oldEndIndex; oldStartIndex++) {
    const node = oldChildren[oldStartIndex];
    if (!node || node.parentNode !== oldTree) continue;
    try {
      oldTree.removeChild(node);
      replayer.mirror.removeNodeFromMap(node);
    } catch (e2) {
      console.warn(e2);
    }
  }
  let oldChild = oldTree.firstChild;
  let newChild = newTree.firstChild;
  while (oldChild !== null && newChild !== null) {
    diff(oldChild, newChild, replayer, rrnodeMirror);
    oldChild = oldChild.nextSibling;
    newChild = newChild.nextSibling;
  }
}
function createOrGetNode(rrNode, domMirror, rrnodeMirror) {
  const nodeId = rrnodeMirror.getId(rrNode);
  const sn = rrnodeMirror.getMeta(rrNode);
  let node = null;
  if (nodeId > -1) node = domMirror.getNode(nodeId);
  if (node !== null && sameNodeType(node, rrNode)) return node;
  switch (rrNode.RRNodeType) {
    case NodeType$1.Document:
      node = new Document();
      break;
    case NodeType$1.DocumentType:
      node = document.implementation.createDocumentType(rrNode.name, rrNode.publicId, rrNode.systemId);
      break;
    case NodeType$1.Element: {
      let tagName = rrNode.tagName.toLowerCase();
      tagName = SVGTagMap[tagName] || tagName;
      if (sn && "isSVG" in sn && (sn == null ? void 0 : sn.isSVG)) node = document.createElementNS(NAMESPACES["svg"], tagName);
      else node = document.createElement(rrNode.tagName);
      break;
    }
    case NodeType$1.Text:
      node = document.createTextNode(rrNode.data);
      break;
    case NodeType$1.Comment:
      node = document.createComment(rrNode.data);
      break;
    case NodeType$1.CDATA:
      node = document.createCDATASection(rrNode.data);
  }
  if (sn) domMirror.add(node, __spreadValues({}, sn));
  try {
    createdNodeSet == null ? void 0 : createdNodeSet.add(node);
  } catch (e2) {
  }
  return node;
}
function sameNodeType(node1, node2) {
  if (node1.nodeType !== node2.nodeType) return false;
  return node1.nodeType !== node1.ELEMENT_NODE || node1.tagName.toUpperCase() === node2.tagName;
}
function nodeMatching(node1, node2, domMirror, rrdomMirror) {
  const node1Id = domMirror.getId(node1);
  const node2Id = rrdomMirror.getId(node2);
  if (node1Id === -1 || node1Id !== node2Id) return false;
  return sameNodeType(node1, node2);
}
var RRDocument = class RRDocument2 extends BaseRRDocument {
  constructor(mirror2) {
    super();
    __publicField(this, "UNSERIALIZED_STARTING_ID", -2);
    __publicField(this, "_unserializedId", this.UNSERIALIZED_STARTING_ID);
    __publicField(this, "mirror", createMirror());
    __publicField(this, "scrollData", null);
    if (mirror2) this.mirror = mirror2;
  }
  /**
  * Every time the id is used, it will minus 1 automatically to avoid collisions.
  */
  get unserializedId() {
    return this._unserializedId--;
  }
  createDocument(_namespace, _qualifiedName, _doctype) {
    return new RRDocument2();
  }
  createDocumentType(qualifiedName, publicId, systemId) {
    const documentTypeNode = new RRDocumentType(qualifiedName, publicId, systemId);
    documentTypeNode.ownerDocument = this;
    return documentTypeNode;
  }
  createElement(tagName) {
    const upperTagName = tagName.toUpperCase();
    let element;
    switch (upperTagName) {
      case "AUDIO":
      case "VIDEO":
        element = new RRMediaElement(upperTagName);
        break;
      case "IFRAME":
        element = new RRIFrameElement(upperTagName, this.mirror);
        break;
      case "CANVAS":
        element = new RRCanvasElement(upperTagName);
        break;
      case "STYLE":
        element = new RRStyleElement(upperTagName);
        break;
      case "DIALOG":
        element = new RRDialogElement(upperTagName);
        break;
      default:
        element = new RRElement(upperTagName);
    }
    element.ownerDocument = this;
    return element;
  }
  createComment(data) {
    const commentNode = new RRComment(data);
    commentNode.ownerDocument = this;
    return commentNode;
  }
  createCDATASection(data) {
    const sectionNode = new RRCDATASection(data);
    sectionNode.ownerDocument = this;
    return sectionNode;
  }
  createTextNode(data) {
    const textNode = new RRText(data);
    textNode.ownerDocument = this;
    return textNode;
  }
  destroyTree() {
    this.firstChild = null;
    this.lastChild = null;
    this.mirror.reset();
  }
  open() {
    super.open();
    this._unserializedId = this.UNSERIALIZED_STARTING_ID;
  }
};
var RRDocumentType = BaseRRDocumentType;
var RRElement = class extends BaseRRElement {
  constructor() {
    super(...arguments);
    __publicField(this, "inputData", null);
    __publicField(this, "scrollData", null);
  }
};
var RRMediaElement = class extends BaseRRMediaElement {
};
var RRDialogElement = class extends BaseRRDialogElement {
};
var RRCanvasElement = class extends RRElement {
  constructor() {
    super(...arguments);
    __publicField(this, "rr_dataURL", null);
    __publicField(this, "canvasMutations", []);
  }
  /**
  * This is a dummy implementation to distinguish RRCanvasElement from real HTMLCanvasElement.
  */
  getContext() {
    return null;
  }
};
var RRStyleElement = class extends RRElement {
  constructor() {
    super(...arguments);
    __publicField(this, "rules", []);
  }
};
var RRIFrameElement = class extends RRElement {
  constructor(upperTagName, mirror2) {
    super(upperTagName);
    __publicField(this, "contentDocument", new RRDocument());
    this.contentDocument.mirror = mirror2;
  }
};
var RRText = BaseRRText;
var RRComment = BaseRRComment;
var RRCDATASection = BaseRRCDATASection;
function getValidTagName(element) {
  if (element instanceof HTMLFormElement) return "FORM";
  return element.tagName.toUpperCase();
}
function buildFromNode(node, rrdom, domMirror, parentRRNode) {
  let rrNode;
  switch (node.nodeType) {
    case NodeType$2.DOCUMENT_NODE:
      if (parentRRNode && parentRRNode.nodeName === "IFRAME") rrNode = parentRRNode.contentDocument;
      else {
        rrNode = rrdom;
        rrNode.compatMode = node.compatMode;
      }
      break;
    case NodeType$2.DOCUMENT_TYPE_NODE: {
      const documentType = node;
      rrNode = rrdom.createDocumentType(documentType.name, documentType.publicId, documentType.systemId);
      break;
    }
    case NodeType$2.ELEMENT_NODE: {
      const elementNode = node;
      const tagName = getValidTagName(elementNode);
      rrNode = rrdom.createElement(tagName);
      const rrElement = rrNode;
      for (const { name, value } of Array.from(elementNode.attributes)) rrElement.attributes[name] = value;
      elementNode.scrollLeft && (rrElement.scrollLeft = elementNode.scrollLeft);
      elementNode.scrollTop && (rrElement.scrollTop = elementNode.scrollTop);
      break;
    }
    case NodeType$2.TEXT_NODE:
      rrNode = rrdom.createTextNode(node.textContent || "");
      break;
    case NodeType$2.CDATA_SECTION_NODE:
      rrNode = rrdom.createCDATASection(node.data);
      break;
    case NodeType$2.COMMENT_NODE:
      rrNode = rrdom.createComment(node.textContent || "");
      break;
    case NodeType$2.DOCUMENT_FRAGMENT_NODE:
      rrNode = parentRRNode.attachShadow({ mode: "open" });
      break;
    default:
      return null;
  }
  let sn = domMirror.getMeta(node);
  if (rrdom instanceof RRDocument) {
    if (!sn) {
      sn = getDefaultSN(rrNode, rrdom.unserializedId);
      domMirror.add(node, sn);
    }
    rrdom.mirror.add(rrNode, __spreadValues({}, sn));
  }
  return rrNode;
}
function buildFromDom(dom, domMirror = createMirror$1(), rrdom = new RRDocument()) {
  function walk(node, parentRRNode) {
    const rrNode = buildFromNode(node, rrdom, domMirror, parentRRNode);
    if (rrNode === null) return;
    if ((parentRRNode == null ? void 0 : parentRRNode.nodeName) !== "IFRAME" && node.nodeType !== NodeType$2.DOCUMENT_FRAGMENT_NODE) {
      parentRRNode == null ? void 0 : parentRRNode.appendChild(rrNode);
      rrNode.parentNode = parentRRNode;
      rrNode.parentElement = parentRRNode;
    }
    if (node.nodeName === "IFRAME") {
      const iframeDoc = node.contentDocument;
      iframeDoc && walk(iframeDoc, rrNode);
    } else if (node.nodeType === NodeType$2.DOCUMENT_NODE || node.nodeType === NodeType$2.ELEMENT_NODE || node.nodeType === NodeType$2.DOCUMENT_FRAGMENT_NODE) {
      if (node.nodeType === NodeType$2.ELEMENT_NODE && node.shadowRoot) walk(node.shadowRoot, rrNode);
      node.childNodes.forEach((childNode) => walk(childNode, rrNode));
    }
  }
  walk(dom, null);
  return rrdom;
}
function createMirror() {
  return new Mirror();
}
var Mirror = class {
  constructor() {
    __publicField(this, "idNodeMap", /* @__PURE__ */ new Map());
    __publicField(this, "nodeMetaMap", /* @__PURE__ */ new WeakMap());
  }
  getId(n2) {
    var _a2, _b;
    if (!n2) return -1;
    return (_b = (_a2 = this.getMeta(n2)) == null ? void 0 : _a2.id) != null ? _b : -1;
  }
  getNode(id) {
    return this.idNodeMap.get(id) || null;
  }
  getIds() {
    return Array.from(this.idNodeMap.keys());
  }
  getMeta(n2) {
    return this.nodeMetaMap.get(n2) || null;
  }
  removeNodeFromMap(n2) {
    const id = this.getId(n2);
    this.idNodeMap.delete(id);
    if (n2.childNodes) n2.childNodes.forEach((childNode) => this.removeNodeFromMap(childNode));
  }
  has(id) {
    return this.idNodeMap.has(id);
  }
  hasNode(node) {
    return this.nodeMetaMap.has(node);
  }
  add(n2, meta) {
    const id = meta.id;
    this.idNodeMap.set(id, n2);
    this.nodeMetaMap.set(n2, meta);
  }
  replace(id, n2) {
    const oldNode = this.getNode(id);
    if (oldNode) {
      const meta = this.nodeMetaMap.get(oldNode);
      if (meta) this.nodeMetaMap.set(n2, meta);
    }
    this.idNodeMap.set(id, n2);
  }
  reset() {
    this.idNodeMap = /* @__PURE__ */ new Map();
    this.nodeMetaMap = /* @__PURE__ */ new WeakMap();
  }
};
function getDefaultSN(node, id) {
  switch (node.RRNodeType) {
    case NodeType$1.Document:
      return {
        id,
        type: node.RRNodeType,
        childNodes: []
      };
    case NodeType$1.DocumentType: {
      const doctype = node;
      return {
        id,
        type: node.RRNodeType,
        name: doctype.name,
        publicId: doctype.publicId,
        systemId: doctype.systemId
      };
    }
    case NodeType$1.Element:
      return {
        id,
        type: node.RRNodeType,
        tagName: node.tagName.toLowerCase(),
        attributes: {},
        childNodes: []
      };
    case NodeType$1.Text:
      return {
        id,
        type: node.RRNodeType,
        textContent: node.textContent || ""
      };
    case NodeType$1.Comment:
      return {
        id,
        type: node.RRNodeType,
        textContent: node.textContent || ""
      };
    case NodeType$1.CDATA:
      return {
        id,
        type: node.RRNodeType,
        textContent: ""
      };
  }
}
var testableAccessors = {
  Node: [
    "childNodes",
    "parentNode",
    "parentElement",
    "textContent",
    "ownerDocument",
    "firstChild",
    "lastChild",
    "nextSibling",
    "previousSibling"
  ],
  ShadowRoot: ["host", "styleSheets"],
  Element: [
    "shadowRoot",
    "querySelector",
    "querySelectorAll"
  ],
  MutationObserver: []
};
var testableMethods = {
  Node: ["contains", "getRootNode"],
  ShadowRoot: ["getSelection"],
  Element: [],
  MutationObserver: ["constructor"]
};
var untaintedBasePrototype = {};
var untaintedBaseIframeCleanup = {};
var mutationObserverUsers = 0;
function angularZoneUnpatchedAlternative(key) {
  var _a2, _b;
  const angularUnpatchedVersionSymbol = (_b = (_a2 = globalThis == null ? void 0 : globalThis.Zone) == null ? void 0 : _a2.__symbol__) == null ? void 0 : _b.call(_a2, key);
  if (angularUnpatchedVersionSymbol && globalThis[angularUnpatchedVersionSymbol]) return globalThis[angularUnpatchedVersionSymbol];
  else return;
}
function getUntaintedPrototype(key) {
  if (untaintedBasePrototype[key]) return untaintedBasePrototype[key];
  const defaultObj = angularZoneUnpatchedAlternative(key) || globalThis[key];
  const defaultPrototype = defaultObj.prototype;
  const accessorNames = key in testableAccessors ? testableAccessors[key] : void 0;
  const isUntaintedAccessors = Boolean(accessorNames && accessorNames.every((accessor) => {
    var _a2, _b;
    return Boolean((_b = (_a2 = Object.getOwnPropertyDescriptor(defaultPrototype, accessor)) == null ? void 0 : _a2.get) == null ? void 0 : _b.toString().includes("[native code]"));
  }));
  const methodNames = key in testableMethods ? testableMethods[key] : void 0;
  const isUntaintedMethods = Boolean(methodNames && methodNames.every((method) => {
    var _a2;
    return typeof defaultPrototype[method] === "function" && ((_a2 = defaultPrototype[method]) == null ? void 0 : _a2.toString().includes("[native code]"));
  }));
  if (isUntaintedAccessors && isUntaintedMethods) {
    untaintedBasePrototype[key] = defaultObj.prototype;
    return defaultObj.prototype;
  }
  try {
    const iframeEl = document.createElement("iframe");
    iframeEl.style.display = "none";
    document.body.appendChild(iframeEl);
    const win = iframeEl.contentWindow;
    if (!win) return defaultObj.prototype;
    const untaintedObject = win[key].prototype;
    if (!untaintedObject) {
      iframeEl.remove();
      return defaultPrototype;
    }
    const ua = navigator.userAgent;
    if (ua.includes("Safari") && !ua.includes("Chrome")) {
      iframeEl.classList.add("rr-block");
      iframeEl.setAttribute("__rrwebUntaintedMutationObserver", "");
      untaintedBaseIframeCleanup[key] = () => iframeEl.remove();
    } else iframeEl.remove();
    return untaintedBasePrototype[key] = untaintedObject;
  } catch (e2) {
    return defaultPrototype;
  }
}
var untaintedAccessorCache = {};
function getUntaintedAccessor(key, instance, accessor) {
  var _a2, _b;
  const cached = (_a2 = untaintedAccessorCache[key]) == null ? void 0 : _a2[accessor];
  if (cached) return cached.call(instance);
  const untaintedPrototype = getUntaintedPrototype(key);
  const untaintedAccessor = (_b = Object.getOwnPropertyDescriptor(untaintedPrototype, accessor)) == null ? void 0 : _b.get;
  if (!untaintedAccessor) return instance[accessor];
  (untaintedAccessorCache[key] || (untaintedAccessorCache[key] = {}))[accessor] = untaintedAccessor;
  return untaintedAccessor.call(instance);
}
var untaintedMethodCache = {};
function getUntaintedMethod(key, instance, method) {
  const cacheKey = `${key}.${String(method)}`;
  if (untaintedMethodCache[cacheKey]) return untaintedMethodCache[cacheKey].bind(instance);
  const untaintedMethod = getUntaintedPrototype(key)[method];
  if (typeof untaintedMethod !== "function") return instance[method];
  untaintedMethodCache[cacheKey] = untaintedMethod;
  return untaintedMethod.bind(instance);
}
function ownerDocument(n2) {
  return getUntaintedAccessor("Node", n2, "ownerDocument");
}
function childNodes(n2) {
  return getUntaintedAccessor("Node", n2, "childNodes");
}
function parentNode(n2) {
  return getUntaintedAccessor("Node", n2, "parentNode");
}
function parentElement(n2) {
  return getUntaintedAccessor("Node", n2, "parentElement");
}
function textContent(n2) {
  return getUntaintedAccessor("Node", n2, "textContent");
}
function firstChild(n2) {
  return getUntaintedAccessor("Node", n2, "firstChild");
}
function lastChild(n2) {
  return getUntaintedAccessor("Node", n2, "lastChild");
}
function nextSibling(n2) {
  return getUntaintedAccessor("Node", n2, "nextSibling");
}
function previousSibling(n2) {
  return getUntaintedAccessor("Node", n2, "previousSibling");
}
function contains(n2, other) {
  return getUntaintedMethod("Node", n2, "contains")(other);
}
function getRootNode(n2) {
  return getUntaintedMethod("Node", n2, "getRootNode")();
}
function host(n2) {
  if (!n2 || !("host" in n2)) return null;
  return getUntaintedAccessor("ShadowRoot", n2, "host");
}
function styleSheets(n2) {
  return n2.styleSheets;
}
function shadowRoot(n2) {
  if (!n2 || !("shadowRoot" in n2)) return null;
  return getUntaintedAccessor("Element", n2, "shadowRoot");
}
function querySelector(n2, selectors) {
  return getUntaintedAccessor("Element", n2, "querySelector")(selectors);
}
function querySelectorAll(n2, selectors) {
  return getUntaintedAccessor("Element", n2, "querySelectorAll")(selectors);
}
function mutationObserverCtor() {
  const prototype = getUntaintedPrototype("MutationObserver");
  mutationObserverUsers++;
  let stopCalled = false;
  return [prototype.constructor, () => {
    if (stopCalled) return;
    stopCalled = true;
    if (--mutationObserverUsers !== 0) return;
    const cleanup = untaintedBaseIframeCleanup["MutationObserver"];
    if (!cleanup) return;
    try {
      cleanup();
    } finally {
      delete untaintedBasePrototype["MutationObserver"];
      delete untaintedBaseIframeCleanup["MutationObserver"];
    }
  }];
}
var nowTimestamp = Date.now;
if (!/* @__PURE__ */ /[1-9][0-9]{12}/.test(Date.now().toString())) nowTimestamp = () => (/* @__PURE__ */ new Date()).getTime();
function patch(source, name, replacement) {
  try {
    if (!(name in source)) return () => {
    };
    const original = source[name];
    const wrapped = replacement(original);
    if (typeof wrapped === "function") {
      wrapped.prototype = wrapped.prototype || {};
      Object.defineProperties(wrapped, { __rrweb_original__: {
        enumerable: false,
        value: original
      } });
    }
    source[name] = wrapped;
    return () => {
      source[name] = original;
    };
  } catch (e2) {
    return () => {
    };
  }
}
var src_default = {
  ownerDocument,
  childNodes,
  parentNode,
  parentElement,
  textContent,
  firstChild,
  lastChild,
  nextSibling,
  previousSibling,
  contains,
  getRootNode,
  host,
  styleSheets,
  shadowRoot,
  querySelector,
  querySelectorAll,
  nowTimestamp,
  mutationObserverCtor,
  patch
};
var utils_exports = /* @__PURE__ */ __exportAll({
  StyleSheetMirror: () => StyleSheetMirror,
  _mirror: () => _mirror,
  closestElementOfNode: () => closestElementOfNode,
  getBaseDimension: () => getBaseDimension,
  getNestedRule: () => getNestedRule,
  getPositionsAndIndex: () => getPositionsAndIndex,
  getRootShadowHost: () => getRootShadowHost,
  getShadowHost: () => getShadowHost,
  getWindowHeight: () => getWindowHeight,
  getWindowScroll: () => getWindowScroll,
  getWindowWidth: () => getWindowWidth,
  hasShadowRoot: () => hasShadowRoot,
  hookSetter: () => hookSetter,
  inDom: () => inDom,
  isAncestorRemoved: () => isAncestorRemoved,
  isBlocked: () => isBlocked,
  isIgnored: () => isIgnored,
  isSerialized: () => isSerialized,
  isSerializedIframe: () => isSerializedIframe,
  isSerializedStylesheet: () => isSerializedStylesheet,
  iterateResolveTree: () => iterateResolveTree,
  legacy_isTouchEvent: () => legacy_isTouchEvent,
  nowTimestamp: () => nowTimestamp,
  on: () => on,
  polyfill: () => polyfill$1,
  queueToResolveTrees: () => queueToResolveTrees,
  shadowHostInDom: () => shadowHostInDom,
  throttle: () => throttle,
  uniqueTextMutations: () => uniqueTextMutations
});
function on(type, fn, target = document) {
  const options = {
    capture: true,
    passive: true
  };
  target.addEventListener(type, fn, options);
  return () => target.removeEventListener(type, fn, options);
}
var DEPARTED_MIRROR_ACCESS_WARNING = "Please stop import mirror directly. Instead of that,\r\nnow you can use replayer.getMirror() to access the mirror instance of a replayer,\r\nor you can use record.mirror to access the mirror instance during recording.";
var _mirror = {
  map: {},
  getId() {
    console.error(DEPARTED_MIRROR_ACCESS_WARNING);
    return -1;
  },
  getNode() {
    console.error(DEPARTED_MIRROR_ACCESS_WARNING);
    return null;
  },
  removeNodeFromMap() {
    console.error(DEPARTED_MIRROR_ACCESS_WARNING);
  },
  has() {
    console.error(DEPARTED_MIRROR_ACCESS_WARNING);
    return false;
  },
  reset() {
    console.error(DEPARTED_MIRROR_ACCESS_WARNING);
  }
};
if (typeof window !== "undefined" && window.Proxy && window.Reflect) _mirror = new Proxy(_mirror, { get(target, prop, receiver) {
  if (prop === "map") console.error(DEPARTED_MIRROR_ACCESS_WARNING);
  return Reflect.get(target, prop, receiver);
} });
function throttle(func, wait, options = {}) {
  let timeout = null;
  let previous = 0;
  return function(...args) {
    const now = Date.now();
    if (!previous && options.leading === false) previous = now;
    const remaining = wait - (now - previous);
    const context = this;
    if (remaining <= 0 || remaining > wait) {
      if (timeout) {
        clearTimeout(timeout);
        timeout = null;
      }
      previous = now;
      func.apply(context, args);
    } else if (!timeout && options.trailing !== false) timeout = setTimeout(() => {
      previous = options.leading === false ? 0 : Date.now();
      timeout = null;
      func.apply(context, args);
    }, remaining);
  };
}
function hookSetter(target, key, d, isRevoked, win = window) {
  const original = win.Object.getOwnPropertyDescriptor(target, key);
  win.Object.defineProperty(target, key, isRevoked ? d : { set(value) {
    setTimeout(() => {
      d.set.call(this, value);
    }, 0);
    if (original && original.set) original.set.call(this, value);
  } });
  return () => hookSetter(target, key, original || {}, true);
}
function getWindowScroll(win) {
  var _a2, _b, _c, _d;
  const doc = win.document;
  return {
    left: doc.scrollingElement ? doc.scrollingElement.scrollLeft : win.pageXOffset !== void 0 ? win.pageXOffset : doc.documentElement.scrollLeft || (doc == null ? void 0 : doc.body) && ((_a2 = src_default.parentElement(doc.body)) == null ? void 0 : _a2.scrollLeft) || ((_b = doc == null ? void 0 : doc.body) == null ? void 0 : _b.scrollLeft) || 0,
    top: doc.scrollingElement ? doc.scrollingElement.scrollTop : win.pageYOffset !== void 0 ? win.pageYOffset : (doc == null ? void 0 : doc.documentElement.scrollTop) || (doc == null ? void 0 : doc.body) && ((_c = src_default.parentElement(doc.body)) == null ? void 0 : _c.scrollTop) || ((_d = doc == null ? void 0 : doc.body) == null ? void 0 : _d.scrollTop) || 0
  };
}
function getWindowHeight() {
  return window.innerHeight || document.documentElement && document.documentElement.clientHeight || document.body && document.body.clientHeight;
}
function getWindowWidth() {
  return window.innerWidth || document.documentElement && document.documentElement.clientWidth || document.body && document.body.clientWidth;
}
function closestElementOfNode(node) {
  if (!node) return null;
  return node.nodeType === node.ELEMENT_NODE ? node : src_default.parentElement(node);
}
function isBlocked(node, blockClass, blockSelector, checkAncestors) {
  if (!node) return false;
  const el = closestElementOfNode(node);
  if (!el) return false;
  try {
    if (typeof blockClass === "string") {
      if (el.classList.contains(blockClass)) return true;
      if (checkAncestors && el.closest("." + blockClass) !== null) return true;
    } else if (classMatchesRegex(el, blockClass, checkAncestors)) return true;
  } catch (e2) {
  }
  if (blockSelector) {
    if (el.matches(blockSelector)) return true;
    if (checkAncestors && el.closest(blockSelector) !== null) return true;
  }
  return false;
}
function isSerialized(n2, mirror2) {
  return mirror2.getId(n2) !== -1;
}
function isIgnored(n2, mirror2, slimDOMOptions) {
  if (n2.tagName === "TITLE" && slimDOMOptions.headTitleMutations) return true;
  return mirror2.getId(n2) === -2;
}
function isAncestorRemoved(target, mirror2) {
  if (isShadowRoot(target)) return false;
  const id = mirror2.getId(target);
  if (!mirror2.has(id)) return true;
  const parent = src_default.parentNode(target);
  if (parent && parent.nodeType === target.DOCUMENT_NODE) return false;
  if (!parent) return true;
  return isAncestorRemoved(parent, mirror2);
}
function legacy_isTouchEvent(event) {
  return Boolean(event.changedTouches);
}
function polyfill$1(win = window) {
  if ("NodeList" in win && !win.NodeList.prototype.forEach) win.NodeList.prototype.forEach = Array.prototype.forEach;
  if ("DOMTokenList" in win && !win.DOMTokenList.prototype.forEach) win.DOMTokenList.prototype.forEach = Array.prototype.forEach;
}
function queueToResolveTrees(queue) {
  const queueNodeMap = {};
  const putIntoMap = (m, parent) => {
    const nodeInTree = {
      value: m,
      parent,
      children: []
    };
    queueNodeMap[m.node.id] = nodeInTree;
    return nodeInTree;
  };
  const queueNodeTrees = [];
  for (const mutation of queue) {
    const { nextId, parentId } = mutation;
    if (nextId && nextId in queueNodeMap) {
      const nextInTree = queueNodeMap[nextId];
      if (nextInTree.parent) {
        const idx = nextInTree.parent.children.indexOf(nextInTree);
        nextInTree.parent.children.splice(idx, 0, putIntoMap(mutation, nextInTree.parent));
      } else {
        const idx = queueNodeTrees.indexOf(nextInTree);
        queueNodeTrees.splice(idx, 0, putIntoMap(mutation, null));
      }
      continue;
    }
    if (parentId in queueNodeMap) {
      const parentInTree = queueNodeMap[parentId];
      parentInTree.children.push(putIntoMap(mutation, parentInTree));
      continue;
    }
    queueNodeTrees.push(putIntoMap(mutation, null));
  }
  return queueNodeTrees;
}
function iterateResolveTree(tree, cb) {
  cb(tree.value);
  for (let i2 = tree.children.length - 1; i2 >= 0; i2--) iterateResolveTree(tree.children[i2], cb);
}
function isSerializedIframe(n2, mirror2) {
  return Boolean(n2.nodeName === "IFRAME" && mirror2.getMeta(n2));
}
function isSerializedStylesheet(n2, mirror2) {
  return Boolean(n2.nodeName === "LINK" && n2.nodeType === n2.ELEMENT_NODE && n2.getAttribute && n2.getAttribute("rel") === "stylesheet" && mirror2.getMeta(n2));
}
function getBaseDimension(node, rootIframe) {
  var _a2, _b;
  const frameElement = (_b = (_a2 = node.ownerDocument) == null ? void 0 : _a2.defaultView) == null ? void 0 : _b.frameElement;
  if (!frameElement || frameElement === rootIframe) return {
    x: 0,
    y: 0,
    relativeScale: 1,
    absoluteScale: 1
  };
  const frameDimension = frameElement.getBoundingClientRect();
  const frameBaseDimension = getBaseDimension(frameElement, rootIframe);
  const relativeScale = frameDimension.height / frameElement.clientHeight;
  return {
    x: frameDimension.x * frameBaseDimension.relativeScale + frameBaseDimension.x,
    y: frameDimension.y * frameBaseDimension.relativeScale + frameBaseDimension.y,
    relativeScale,
    absoluteScale: frameBaseDimension.absoluteScale * relativeScale
  };
}
function hasShadowRoot(n2) {
  if (!n2) return false;
  if (n2 instanceof BaseRRNode && "shadowRoot" in n2) return Boolean(n2.shadowRoot);
  return Boolean(src_default.shadowRoot(n2));
}
function getNestedRule(rules2, position) {
  const rule = rules2 == null ? void 0 : rules2[position[0]];
  if (!rule) return null;
  if (position.length === 1) return rule;
  else return getNestedRule(rule.cssRules, position.slice(1));
}
function getPositionsAndIndex(nestedIndex) {
  const positions = [...nestedIndex];
  return {
    positions,
    index: positions.pop()
  };
}
function uniqueTextMutations(mutations) {
  const idSet = /* @__PURE__ */ new Set();
  const uniqueMutations = [];
  for (let i2 = mutations.length; i2--; ) {
    const mutation = mutations[i2];
    if (!idSet.has(mutation.id)) {
      uniqueMutations.push(mutation);
      idSet.add(mutation.id);
    }
  }
  return uniqueMutations;
}
var StyleSheetMirror = class {
  constructor() {
    __publicField(this, "id", 1);
    __publicField(this, "styleIDMap", /* @__PURE__ */ new WeakMap());
    __publicField(this, "idStyleMap", /* @__PURE__ */ new Map());
  }
  getId(stylesheet) {
    var _a2;
    return (_a2 = this.styleIDMap.get(stylesheet)) != null ? _a2 : -1;
  }
  has(stylesheet) {
    return this.styleIDMap.has(stylesheet);
  }
  /**
  * @returns If the stylesheet is in the mirror, returns the id of the stylesheet. If not, return the new assigned id.
  */
  add(stylesheet, id) {
    if (this.has(stylesheet)) return this.getId(stylesheet);
    let newId;
    if (id === void 0) newId = this.id++;
    else newId = id;
    this.styleIDMap.set(stylesheet, newId);
    this.idStyleMap.set(newId, stylesheet);
    return newId;
  }
  getStyle(id) {
    return this.idStyleMap.get(id) || null;
  }
  reset() {
    this.styleIDMap = /* @__PURE__ */ new WeakMap();
    this.idStyleMap = /* @__PURE__ */ new Map();
    this.id = 1;
  }
  generateId() {
    return this.id++;
  }
};
function getShadowHost(n2) {
  var _a2;
  let shadowHost = null;
  if ("getRootNode" in n2 && ((_a2 = src_default.getRootNode(n2)) == null ? void 0 : _a2.nodeType) === Node.DOCUMENT_FRAGMENT_NODE && src_default.host(src_default.getRootNode(n2))) shadowHost = src_default.host(src_default.getRootNode(n2));
  return shadowHost;
}
function getRootShadowHost(n2) {
  let rootShadowHost = n2;
  let shadowHost;
  while (shadowHost = getShadowHost(rootShadowHost)) rootShadowHost = shadowHost;
  return rootShadowHost;
}
function shadowHostInDom(n2) {
  const doc = src_default.ownerDocument(n2);
  if (!doc) return false;
  const shadowHost = getRootShadowHost(n2);
  return src_default.contains(doc, shadowHost);
}
function inDom(n2) {
  const doc = src_default.ownerDocument(n2);
  if (!doc) return false;
  return src_default.contains(doc, n2) || shadowHostInDom(n2);
}
var EventType = /* @__PURE__ */ function(EventType2) {
  EventType2[EventType2["DomContentLoaded"] = 0] = "DomContentLoaded";
  EventType2[EventType2["Load"] = 1] = "Load";
  EventType2[EventType2["FullSnapshot"] = 2] = "FullSnapshot";
  EventType2[EventType2["IncrementalSnapshot"] = 3] = "IncrementalSnapshot";
  EventType2[EventType2["Meta"] = 4] = "Meta";
  EventType2[EventType2["Custom"] = 5] = "Custom";
  EventType2[EventType2["Plugin"] = 6] = "Plugin";
  EventType2[EventType2["Asset"] = 7] = "Asset";
  EventType2[EventType2["Annotation"] = 8] = "Annotation";
  return EventType2;
}({});
var IncrementalSource = /* @__PURE__ */ function(IncrementalSource2) {
  IncrementalSource2[IncrementalSource2["Mutation"] = 0] = "Mutation";
  IncrementalSource2[IncrementalSource2["MouseMove"] = 1] = "MouseMove";
  IncrementalSource2[IncrementalSource2["MouseInteraction"] = 2] = "MouseInteraction";
  IncrementalSource2[IncrementalSource2["Scroll"] = 3] = "Scroll";
  IncrementalSource2[IncrementalSource2["ViewportResize"] = 4] = "ViewportResize";
  IncrementalSource2[IncrementalSource2["Input"] = 5] = "Input";
  IncrementalSource2[IncrementalSource2["TouchMove"] = 6] = "TouchMove";
  IncrementalSource2[IncrementalSource2["MediaInteraction"] = 7] = "MediaInteraction";
  IncrementalSource2[IncrementalSource2["StyleSheetRule"] = 8] = "StyleSheetRule";
  IncrementalSource2[IncrementalSource2["CanvasMutation"] = 9] = "CanvasMutation";
  IncrementalSource2[IncrementalSource2["Font"] = 10] = "Font";
  IncrementalSource2[IncrementalSource2["Log"] = 11] = "Log";
  IncrementalSource2[IncrementalSource2["Drag"] = 12] = "Drag";
  IncrementalSource2[IncrementalSource2["StyleDeclaration"] = 13] = "StyleDeclaration";
  IncrementalSource2[IncrementalSource2["Selection"] = 14] = "Selection";
  IncrementalSource2[IncrementalSource2["AdoptedStyleSheet"] = 15] = "AdoptedStyleSheet";
  IncrementalSource2[IncrementalSource2["CustomElement"] = 16] = "CustomElement";
  return IncrementalSource2;
}({});
var MouseInteractions = /* @__PURE__ */ function(MouseInteractions2) {
  MouseInteractions2[MouseInteractions2["MouseUp"] = 0] = "MouseUp";
  MouseInteractions2[MouseInteractions2["MouseDown"] = 1] = "MouseDown";
  MouseInteractions2[MouseInteractions2["Click"] = 2] = "Click";
  MouseInteractions2[MouseInteractions2["ContextMenu"] = 3] = "ContextMenu";
  MouseInteractions2[MouseInteractions2["DblClick"] = 4] = "DblClick";
  MouseInteractions2[MouseInteractions2["Focus"] = 5] = "Focus";
  MouseInteractions2[MouseInteractions2["Blur"] = 6] = "Blur";
  MouseInteractions2[MouseInteractions2["TouchStart"] = 7] = "TouchStart";
  MouseInteractions2[MouseInteractions2["TouchMove_Departed"] = 8] = "TouchMove_Departed";
  MouseInteractions2[MouseInteractions2["TouchEnd"] = 9] = "TouchEnd";
  MouseInteractions2[MouseInteractions2["TouchCancel"] = 10] = "TouchCancel";
  return MouseInteractions2;
}({});
var PointerTypes = /* @__PURE__ */ function(PointerTypes2) {
  PointerTypes2[PointerTypes2["Mouse"] = 0] = "Mouse";
  PointerTypes2[PointerTypes2["Pen"] = 1] = "Pen";
  PointerTypes2[PointerTypes2["Touch"] = 2] = "Touch";
  return PointerTypes2;
}({});
var CanvasContext = /* @__PURE__ */ function(CanvasContext2) {
  CanvasContext2[CanvasContext2["2D"] = 0] = "2D";
  CanvasContext2[CanvasContext2["WebGL"] = 1] = "WebGL";
  CanvasContext2[CanvasContext2["WebGL2"] = 2] = "WebGL2";
  return CanvasContext2;
}({});
var MediaInteractions = /* @__PURE__ */ function(MediaInteractions2) {
  MediaInteractions2[MediaInteractions2["Play"] = 0] = "Play";
  MediaInteractions2[MediaInteractions2["Pause"] = 1] = "Pause";
  MediaInteractions2[MediaInteractions2["Seeked"] = 2] = "Seeked";
  MediaInteractions2[MediaInteractions2["VolumeChange"] = 3] = "VolumeChange";
  MediaInteractions2[MediaInteractions2["RateChange"] = 4] = "RateChange";
  return MediaInteractions2;
}({});
var ReplayerEvents = /* @__PURE__ */ function(ReplayerEvents2) {
  ReplayerEvents2["Start"] = "start";
  ReplayerEvents2["Pause"] = "pause";
  ReplayerEvents2["Resume"] = "resume";
  ReplayerEvents2["Resize"] = "resize";
  ReplayerEvents2["Finish"] = "finish";
  ReplayerEvents2["FullsnapshotRebuilded"] = "fullsnapshot-rebuilded";
  ReplayerEvents2["LoadStylesheetStart"] = "load-stylesheet-start";
  ReplayerEvents2["LoadStylesheetEnd"] = "load-stylesheet-end";
  ReplayerEvents2["SkipStart"] = "skip-start";
  ReplayerEvents2["SkipEnd"] = "skip-end";
  ReplayerEvents2["MouseInteraction"] = "mouse-interaction";
  ReplayerEvents2["EventCast"] = "event-cast";
  ReplayerEvents2["CustomEvent"] = "custom-event";
  ReplayerEvents2["Annotation"] = "annotation";
  ReplayerEvents2["Flush"] = "flush";
  ReplayerEvents2["StateChange"] = "state-change";
  ReplayerEvents2["PlayBack"] = "play-back";
  ReplayerEvents2["Destroy"] = "destroy";
  return ReplayerEvents2;
}({});
var NodeType = /* @__PURE__ */ function(NodeType2) {
  NodeType2[NodeType2["Document"] = 0] = "Document";
  NodeType2[NodeType2["DocumentType"] = 1] = "DocumentType";
  NodeType2[NodeType2["Element"] = 2] = "Element";
  NodeType2[NodeType2["Text"] = 3] = "Text";
  NodeType2[NodeType2["CDATA"] = 4] = "CDATA";
  NodeType2[NodeType2["Comment"] = 5] = "Comment";
  return NodeType2;
}({});
var moveKey = (id, parentId) => `${id}@${parentId}`;
var MutationBuffer = class {
  constructor() {
    __publicField(this, "frozen", false);
    __publicField(this, "locked", false);
    __publicField(this, "texts", []);
    __publicField(this, "attributes", []);
    __publicField(this, "attributeMap", /* @__PURE__ */ new WeakMap());
    __publicField(this, "removes", []);
    __publicField(this, "mapRemoves", []);
    __publicField(this, "movedMap", {});
    /**
    * the browser MutationObserver emits multiple mutations after
    * a delay for performance reasons, making tracing added nodes hard
    * in our `processMutations` callback function.
    * For example, if we append an element el_1 into body, and then append
    * another element el_2 into el_1, these two mutations may be passed to the
    * callback function together when the two operations were done.
    * Generally we need to trace child nodes of newly added nodes, but in this
    * case if we count el_2 as el_1's child node in the first mutation record,
    * then we will count el_2 again in the second mutation record which was
    * duplicated.
    * To avoid of duplicate counting added nodes, we use a Set to store
    * added nodes and its child nodes during iterate mutation records. Then
    * collect added nodes from the Set which have no duplicate copy. But
    * this also causes newly added nodes will not be serialized with id ASAP,
    * which means all the id related calculation should be lazy too.
    */
    __publicField(this, "addedSet", /* @__PURE__ */ new Set());
    __publicField(this, "movedSet", /* @__PURE__ */ new Set());
    __publicField(this, "droppedSet", /* @__PURE__ */ new Set());
    __publicField(this, "removesSubTreeCache", /* @__PURE__ */ new Set());
    __publicField(this, "mutationCb");
    __publicField(this, "blockClass");
    __publicField(this, "blockSelector");
    __publicField(this, "maskTextClass");
    __publicField(this, "maskTextSelector");
    __publicField(this, "inlineStylesheet");
    __publicField(this, "maskInputOptions");
    __publicField(this, "maskTextFn");
    __publicField(this, "maskInputFn");
    __publicField(this, "keepIframeSrcFn");
    __publicField(this, "recordCanvas");
    __publicField(this, "inlineImages");
    __publicField(this, "slimDOMOptions");
    __publicField(this, "dataURLOptions");
    __publicField(this, "doc");
    __publicField(this, "mirror");
    __publicField(this, "iframeManager");
    __publicField(this, "stylesheetManager");
    __publicField(this, "shadowDomManager");
    __publicField(this, "processedNodeManager");
    __publicField(this, "unattachedDoc");
    __publicField(this, "processMutations", (mutations) => {
      mutations.forEach(this.processMutation);
      this.emit();
    });
    __publicField(this, "emit", () => {
      if (this.frozen || this.locked) return;
      const adds = [];
      const addedIds = /* @__PURE__ */ new Set();
      while (this.mapRemoves.length) this.mirror.removeNodeFromMap(this.mapRemoves.shift());
      for (const n3 of this.movedSet) {
        const parentNode3 = src_default.parentNode(n3);
        if (this.removesSubTreeCache.has(parentNode3) && !this.movedSet.has(parentNode3)) continue;
        this.addedSet.add(n3);
      }
      let n2 = null;
      let parentNode2 = null;
      let parentId = -1;
      let nextSibling2 = null;
      let ancestorBad = false;
      const missingParents = /* @__PURE__ */ new Set();
      const iter = this.addedSet.values();
      let curr = iter.next();
      while (this.addedSet.size) {
        if (n2 !== null && this.addedSet.has(src_default.previousSibling(n2))) {
          nextSibling2 = n2;
          n2 = src_default.previousSibling(n2);
        } else {
          if (!this.addedSet.has(curr.value)) curr = iter.next();
          n2 = curr.value;
          while (true) {
            parentNode2 = src_default.parentNode(n2);
            if (this.addedSet.has(parentNode2)) {
              n2 = parentNode2;
              continue;
            }
            break;
          }
          if (missingParents.has(parentNode2)) parentNode2 = null;
          else if (parentNode2) {
            if (!inDom(parentNode2)) ancestorBad = true;
            else {
              ancestorBad = isSelfOrAncestorInSet(this.droppedSet, parentNode2) || this.removesSubTreeCache.has(parentNode2);
              if (ancestorBad && isSelfOrAncestorInSet(this.movedSet, n2)) ancestorBad = false;
            }
            if (this.addedSet.has(src_default.lastChild(parentNode2))) {
              n2 = src_default.lastChild(parentNode2);
              nextSibling2 = null;
            } else while (true) {
              nextSibling2 = src_default.nextSibling(n2);
              if (this.addedSet.has(nextSibling2)) {
                n2 = nextSibling2;
                continue;
              }
              break;
            }
            parentId = isShadowRoot(parentNode2) ? this.mirror.getId(getShadowHost(n2)) : this.mirror.getId(parentNode2);
            if (parentId === -1 && parentNode2.nodeType === Node.DOCUMENT_FRAGMENT_NODE) {
              const shadowHost = src_default.host(parentNode2);
              parentId = this.mirror.getId(shadowHost);
            }
          }
        }
        this.addedSet.delete(n2);
        if (!parentNode2 || parentId === -1) {
          missingParents.add(n2);
          continue;
        } else if (ancestorBad) {
          this.droppedSet.add(n2);
          continue;
        }
        let cssCaptured = false;
        if (n2.nodeType === Node.TEXT_NODE) {
          const parentTag = parentNode2.tagName;
          if (parentTag === "TEXTAREA") continue;
          else if (parentTag === "STYLE" && addedIds.has(parentId)) cssCaptured = true;
        }
        let nextId = nextSibling2 ? this.mirror.getId(nextSibling2) : null;
        while (nextId === -2) {
          nextSibling2 = nextSibling2 && src_default.nextSibling(nextSibling2);
          nextId = nextSibling2 && this.mirror.getId(nextSibling2);
        }
        if (nextId === -1) {
          console.warn("Couldn't record new node. Couldn't find mirror id for nextSibling:", nextSibling2);
          n2 = null;
          continue;
        }
        const sn = serializeNodeWithId(n2, {
          doc: this.doc,
          mirror: this.mirror,
          blockClass: this.blockClass,
          blockSelector: this.blockSelector,
          maskTextClass: this.maskTextClass,
          maskTextSelector: this.maskTextSelector,
          skipChild: true,
          newlyAddedElement: true,
          inlineStylesheet: this.inlineStylesheet,
          maskInputOptions: this.maskInputOptions,
          maskTextFn: this.maskTextFn,
          maskInputFn: this.maskInputFn,
          slimDOMOptions: this.slimDOMOptions,
          dataURLOptions: this.dataURLOptions,
          recordCanvas: this.recordCanvas,
          inlineImages: this.inlineImages,
          onSerialize: (currentN) => {
            if (isSerializedIframe(currentN, this.mirror)) this.iframeManager.addIframe(currentN);
            if (isSerializedStylesheet(currentN, this.mirror)) this.stylesheetManager.trackLinkElement(currentN);
            if (hasShadowRoot(n2)) this.shadowDomManager.addShadowRoot(src_default.shadowRoot(n2), this.doc);
          },
          onIframeLoad: (iframe, childSn) => {
            this.iframeManager.attachIframe(iframe, childSn);
            this.shadowDomManager.observeAttachShadow(iframe);
          },
          onStylesheetLoad: (link, childSn) => {
            this.stylesheetManager.attachLinkElement(link, childSn);
          },
          cssCaptured
        });
        if (sn) {
          adds.push({
            parentId,
            nextId,
            node: sn
          });
          addedIds.add(sn.id);
        }
      }
      const payload = {
        texts: this.texts.map((text) => {
          const n3 = text.node;
          const parent = src_default.parentNode(n3);
          if (parent && parent.tagName === "TEXTAREA") this.genTextAreaValueMutation(parent);
          return {
            id: this.mirror.getId(n3),
            value: text.value
          };
        }).filter((text) => !addedIds.has(text.id)).filter((text) => this.mirror.has(text.id)),
        attributes: this.attributes.map((attribute) => {
          const { attributes } = attribute;
          if (typeof attributes.style === "string") {
            const diffAsStr = JSON.stringify(attribute.styleDiff);
            const unchangedAsStr = JSON.stringify(attribute._unchangedStyles);
            if (diffAsStr.length < attributes.style.length) {
              if ((diffAsStr + unchangedAsStr).split("var(").length === attributes.style.split("var(").length) attributes.style = attribute.styleDiff;
            }
          }
          return {
            id: this.mirror.getId(attribute.node),
            attributes
          };
        }).filter((attribute) => !addedIds.has(attribute.id)).filter((attribute) => this.mirror.has(attribute.id)),
        removes: this.removes,
        adds
      };
      if (!payload.texts.length && !payload.attributes.length && !payload.removes.length && !payload.adds.length) return;
      this.texts = [];
      this.attributes = [];
      this.attributeMap = /* @__PURE__ */ new WeakMap();
      this.removes = [];
      this.addedSet = /* @__PURE__ */ new Set();
      this.movedSet = /* @__PURE__ */ new Set();
      this.droppedSet = /* @__PURE__ */ new Set();
      this.removesSubTreeCache = /* @__PURE__ */ new Set();
      this.movedMap = {};
      this.mutationCb(payload);
    });
    __publicField(this, "genTextAreaValueMutation", (textarea) => {
      let item = this.attributeMap.get(textarea);
      if (!item) {
        item = {
          node: textarea,
          attributes: {},
          styleDiff: {},
          _unchangedStyles: {}
        };
        this.attributes.push(item);
        this.attributeMap.set(textarea, item);
      }
      const value = Array.from(src_default.childNodes(textarea), (cn) => src_default.textContent(cn) || "").join("");
      item.attributes.value = maskInputValue({
        element: textarea,
        maskInputOptions: this.maskInputOptions,
        tagName: textarea.tagName,
        type: getInputType(textarea),
        value,
        maskInputFn: this.maskInputFn
      });
    });
    __publicField(this, "processMutation", (m) => {
      if (isIgnored(m.target, this.mirror, this.slimDOMOptions)) return;
      switch (m.type) {
        case "characterData": {
          const value = src_default.textContent(m.target);
          if (!isBlocked(m.target, this.blockClass, this.blockSelector, false) && value !== m.oldValue) this.texts.push({
            value: needMaskingText(m.target, this.maskTextClass, this.maskTextSelector, true) && value ? this.maskTextFn ? this.maskTextFn(value, closestElementOfNode(m.target)) : value.replace(/[\S]/g, "*") : value,
            node: m.target
          });
          break;
        }
        case "attributes": {
          const target = m.target;
          const tagNameLower = toLowerCase(target.tagName);
          let attributeName = m.attributeName;
          let value = m.target.getAttribute(attributeName);
          if (attributeName === "value") {
            const type = getInputType(target);
            value = maskInputValue({
              element: target,
              maskInputOptions: this.maskInputOptions,
              tagName: target.tagName,
              type,
              value,
              maskInputFn: this.maskInputFn
            });
          }
          if (isBlocked(m.target, this.blockClass, this.blockSelector, false) || value === m.oldValue) return;
          let item = this.attributeMap.get(m.target);
          if (tagNameLower === "iframe" && attributeName === "src" && !this.keepIframeSrcFn(value)) {
            if (!target.contentDocument) attributeName = "rr_src";
            else return;
          }
          if (!item) {
            item = {
              node: m.target,
              attributes: {},
              styleDiff: {},
              _unchangedStyles: {}
            };
            this.attributes.push(item);
            this.attributeMap.set(m.target, item);
          }
          if (attributeName === "type" && tagNameLower === "input" && (m.oldValue || "").toLowerCase() === "password") target.setAttribute("data-rr-is-password", "true");
          if (!ignoreAttribute(tagNameLower, attributeName, value)) {
            item.attributes[attributeName] = transformAttribute(this.doc, tagNameLower, toLowerCase(attributeName), value);
            if (attributeName === "style") {
              if (!this.unattachedDoc) try {
                this.unattachedDoc = document.implementation.createHTMLDocument();
              } catch (e2) {
                this.unattachedDoc = this.doc;
              }
              const old = this.unattachedDoc.createElement("span");
              if (m.oldValue) old.setAttribute("style", m.oldValue);
              for (const pname of Array.from(target.style)) {
                const newValue = target.style.getPropertyValue(pname);
                const newPriority = target.style.getPropertyPriority(pname);
                if (newValue !== old.style.getPropertyValue(pname) || newPriority !== old.style.getPropertyPriority(pname)) {
                  if (newPriority === "") item.styleDiff[pname] = newValue;
                  else item.styleDiff[pname] = [newValue, newPriority];
                } else item._unchangedStyles[pname] = [newValue, newPriority];
              }
              for (const pname of Array.from(old.style)) if (target.style.getPropertyValue(pname) === "") item.styleDiff[pname] = false;
            } else if (attributeName === "open" && target.tagName === "DIALOG") {
              if (target.matches("dialog:modal")) item.attributes["rr_open_mode"] = "modal";
              else item.attributes["rr_open_mode"] = "non-modal";
            }
          }
          break;
        }
        case "childList":
          if (isBlocked(m.target, this.blockClass, this.blockSelector, true)) return;
          if (m.target.tagName === "TEXTAREA") {
            this.genTextAreaValueMutation(m.target);
            return;
          }
          m.addedNodes.forEach((n2) => this.genAdds(n2, m.target));
          m.removedNodes.forEach((n2) => {
            if (!inDom(n2)) this.shadowDomManager.removeShadowRootsIn(n2);
            const nodeId = this.mirror.getId(n2);
            const parentId = isShadowRoot(m.target) ? this.mirror.getId(src_default.host(m.target)) : this.mirror.getId(m.target);
            if (isBlocked(m.target, this.blockClass, this.blockSelector, false) || isIgnored(n2, this.mirror, this.slimDOMOptions) || !isSerialized(n2, this.mirror)) return;
            if (this.addedSet.has(n2)) {
              deepDelete(this.addedSet, n2);
              this.droppedSet.add(n2);
            } else if (this.addedSet.has(m.target) && nodeId === -1) {
            } else if (isAncestorRemoved(m.target, this.mirror)) {
            } else if (this.movedSet.has(n2) && this.movedMap[moveKey(nodeId, parentId)]) deepDelete(this.movedSet, n2);
            else {
              this.removes.push({
                parentId,
                id: nodeId,
                isShadow: isShadowRoot(m.target) && isNativeShadowDom(m.target) ? true : void 0
              });
              processRemoves(n2, this.removesSubTreeCache);
            }
            this.mapRemoves.push(n2);
          });
      }
    });
    /**
    * Make sure you check if `n`'s parent is blocked before calling this function
    * */
    __publicField(this, "genAdds", (n2, target) => {
      if (this.processedNodeManager.inOtherBuffer(n2, this)) return;
      if (this.addedSet.has(n2) || this.movedSet.has(n2)) return;
      if (this.mirror.hasNode(n2)) {
        if (isIgnored(n2, this.mirror, this.slimDOMOptions)) return;
        this.movedSet.add(n2);
        let targetId = null;
        if (target && this.mirror.hasNode(target)) targetId = this.mirror.getId(target);
        if (targetId && targetId !== -1) this.movedMap[moveKey(this.mirror.getId(n2), targetId)] = true;
      } else {
        this.addedSet.add(n2);
        this.droppedSet.delete(n2);
      }
      if (!isBlocked(n2, this.blockClass, this.blockSelector, false)) {
        src_default.childNodes(n2).forEach((childN) => this.genAdds(childN));
        if (hasShadowRoot(n2)) src_default.childNodes(src_default.shadowRoot(n2)).forEach((childN) => {
          this.processedNodeManager.add(childN, this);
          this.genAdds(childN, n2);
        });
      }
    });
  }
  init(options) {
    [
      "mutationCb",
      "blockClass",
      "blockSelector",
      "maskTextClass",
      "maskTextSelector",
      "inlineStylesheet",
      "maskInputOptions",
      "maskTextFn",
      "maskInputFn",
      "keepIframeSrcFn",
      "recordCanvas",
      "inlineImages",
      "slimDOMOptions",
      "dataURLOptions",
      "doc",
      "mirror",
      "iframeManager",
      "stylesheetManager",
      "shadowDomManager",
      "processedNodeManager"
    ].forEach((key) => {
      this[key] = options[key];
    });
  }
  freeze() {
    this.frozen = true;
  }
  unfreeze() {
    this.frozen = false;
    this.emit();
  }
  isFrozen() {
    return this.frozen;
  }
  lock() {
    this.locked = true;
  }
  unlock() {
    this.locked = false;
    this.emit();
  }
};
function deepDelete(addsSet, n2) {
  addsSet.delete(n2);
  src_default.childNodes(n2).forEach((childN) => deepDelete(addsSet, childN));
}
function processRemoves(n2, cache) {
  const queue = [n2];
  while (queue.length) {
    const next = queue.pop();
    if (cache.has(next)) continue;
    cache.add(next);
    src_default.childNodes(next).forEach((n3) => queue.push(n3));
  }
}
function isSelfOrAncestorInSet(set, n2) {
  if (set.size === 0) return false;
  return _isSelfOrAncestorInSet(set, n2);
}
function _isSelfOrAncestorInSet(set, n2) {
  if (set.has(n2)) return true;
  const parent = src_default.parentNode(n2);
  if (!parent) return false;
  return _isSelfOrAncestorInSet(set, parent);
}
var errorHandler;
function registerErrorHandler(handler) {
  errorHandler = handler;
}
function unregisterErrorHandler() {
  errorHandler = void 0;
}
var callbackWrapper = (cb) => {
  if (!errorHandler) return cb;
  const rrwebWrapped = (...rest) => {
    try {
      return cb(...rest);
    } catch (error) {
      if (errorHandler && errorHandler(error) === true) return;
      throw error;
    }
  };
  return rrwebWrapped;
};
var mutationBuffers = /* @__PURE__ */ new Set();
function getEventTarget(event) {
  try {
    if ("composedPath" in event) {
      const path = event.composedPath();
      if (path.length) return path[0];
    } else if ("path" in event && event.path.length) return event.path[0];
  } catch (e2) {
  }
  return event && event.target;
}
function initMutationObserver(options, rootEl) {
  const mutationBuffer = new MutationBuffer();
  mutationBuffers.add(mutationBuffer);
  mutationBuffer.init(options);
  const [ObserverCtor, releaseObserverConstructor] = mutationObserverCtor();
  const observer = new ObserverCtor(callbackWrapper(mutationBuffer.processMutations.bind(mutationBuffer)));
  observer.observe(rootEl, {
    attributes: true,
    attributeOldValue: true,
    characterData: true,
    characterDataOldValue: true,
    childList: true,
    subtree: true
  });
  const stop = () => {
    observer.disconnect();
    mutationBuffers.delete(mutationBuffer);
    releaseObserverConstructor();
  };
  return stop;
}
function initMoveObserver({ mousemoveCb, sampling, doc, mirror: mirror2 }) {
  if (sampling.mousemove === false) return () => {
  };
  const threshold = typeof sampling.mousemove === "number" ? sampling.mousemove : 50;
  const callbackThreshold = typeof sampling.mousemoveCallback === "number" ? sampling.mousemoveCallback : 500;
  let positions = [];
  let timeBaseline;
  const wrappedCb = throttle(callbackWrapper((source) => {
    const totalOffset = Date.now() - timeBaseline;
    mousemoveCb(positions.map((p) => {
      p.timeOffset -= totalOffset;
      return p;
    }), source);
    positions = [];
    timeBaseline = null;
  }), callbackThreshold);
  const updatePosition = callbackWrapper(throttle(callbackWrapper((evt) => {
    const target = getEventTarget(evt);
    const { clientX, clientY } = legacy_isTouchEvent(evt) ? evt.changedTouches[0] : evt;
    if (!timeBaseline) timeBaseline = nowTimestamp();
    positions.push({
      x: clientX,
      y: clientY,
      id: mirror2.getId(target),
      timeOffset: nowTimestamp() - timeBaseline
    });
    wrappedCb(typeof DragEvent !== "undefined" && evt instanceof DragEvent ? IncrementalSource.Drag : evt instanceof MouseEvent ? IncrementalSource.MouseMove : IncrementalSource.TouchMove);
  }), threshold, { trailing: false }));
  const handlers = [
    on("mousemove", updatePosition, doc),
    on("touchmove", updatePosition, doc),
    on("drag", updatePosition, doc)
  ];
  return callbackWrapper(() => {
    handlers.forEach((h) => h());
  });
}
function initMouseInteractionObserver({ mouseInteractionCb, doc, mirror: mirror2, blockClass, blockSelector, sampling }) {
  if (sampling.mouseInteraction === false) return () => {
  };
  const disableMap = sampling.mouseInteraction === true || sampling.mouseInteraction === void 0 ? {} : sampling.mouseInteraction;
  const handlers = [];
  let currentPointerType = null;
  const getHandler = (eventKey) => {
    return (event) => {
      const target = getEventTarget(event);
      if (isBlocked(target, blockClass, blockSelector, true)) return;
      let pointerType = null;
      let thisEventKey = eventKey;
      if ("pointerType" in event) {
        switch (event.pointerType) {
          case "mouse":
            pointerType = PointerTypes.Mouse;
            break;
          case "touch":
            pointerType = PointerTypes.Touch;
            break;
          case "pen":
            pointerType = PointerTypes.Pen;
        }
        if (pointerType === PointerTypes.Touch) {
          if (MouseInteractions[eventKey] === MouseInteractions.MouseDown) thisEventKey = "TouchStart";
          else if (MouseInteractions[eventKey] === MouseInteractions.MouseUp) thisEventKey = "TouchEnd";
        } else if (pointerType === PointerTypes.Pen) {
        }
      } else if (legacy_isTouchEvent(event)) pointerType = PointerTypes.Touch;
      if (pointerType !== null) {
        currentPointerType = pointerType;
        if (thisEventKey.startsWith("Touch") && pointerType === PointerTypes.Touch || thisEventKey.startsWith("Mouse") && pointerType === PointerTypes.Mouse) pointerType = null;
      } else if (MouseInteractions[eventKey] === MouseInteractions.Click) {
        pointerType = currentPointerType;
        currentPointerType = null;
      }
      const e2 = legacy_isTouchEvent(event) ? event.changedTouches[0] : event;
      if (!e2) return;
      const id = mirror2.getId(target);
      const { clientX, clientY } = e2;
      callbackWrapper(mouseInteractionCb)(__spreadValues({
        type: MouseInteractions[thisEventKey],
        id,
        x: clientX,
        y: clientY
      }, pointerType !== null && { pointerType }));
    };
  };
  Object.keys(MouseInteractions).filter((key) => Number.isNaN(Number(key)) && !key.endsWith("_Departed") && disableMap[key] !== false).forEach((eventKey) => {
    let eventName = toLowerCase(eventKey);
    const handler = getHandler(eventKey);
    if (window.PointerEvent) switch (MouseInteractions[eventKey]) {
      case MouseInteractions.MouseDown:
      case MouseInteractions.MouseUp:
        eventName = eventName.replace("mouse", "pointer");
        break;
      case MouseInteractions.TouchStart:
      case MouseInteractions.TouchEnd:
        return;
    }
    handlers.push(on(eventName, handler, doc));
  });
  return callbackWrapper(() => {
    handlers.forEach((h) => h());
  });
}
function initScrollObserver({ scrollCb, doc, mirror: mirror2, blockClass, blockSelector, sampling }) {
  return on("scroll", callbackWrapper(throttle(callbackWrapper((evt) => {
    const target = getEventTarget(evt);
    if (!target || isBlocked(target, blockClass, blockSelector, true)) return;
    const id = mirror2.getId(target);
    if (target === doc && doc.defaultView) {
      const scrollLeftTop = getWindowScroll(doc.defaultView);
      scrollCb({
        id,
        x: scrollLeftTop.left,
        y: scrollLeftTop.top
      });
    } else scrollCb({
      id,
      x: target.scrollLeft,
      y: target.scrollTop
    });
  }), sampling.scroll || 100)), doc);
}
function initViewportResizeObserver({ viewportResizeCb }, { win }) {
  let lastH = -1;
  let lastW = -1;
  return on("resize", callbackWrapper(throttle(callbackWrapper(() => {
    const height = getWindowHeight();
    const width = getWindowWidth();
    if (lastH !== height || lastW !== width) {
      viewportResizeCb({
        width: Number(width),
        height: Number(height)
      });
      lastH = height;
      lastW = width;
    }
  }), 200)), win);
}
var INPUT_TAGS = [
  "INPUT",
  "TEXTAREA",
  "SELECT"
];
var lastInputValueMap = /* @__PURE__ */ new WeakMap();
function initInputObserver({ inputCb, doc, mirror: mirror2, blockClass, blockSelector, ignoreClass, ignoreSelector, maskInputOptions, maskInputFn, sampling, userTriggeredOnInput }) {
  function eventHandler(event) {
    let target = getEventTarget(event);
    const userTriggered = event.isTrusted;
    const tagName = target && target.tagName;
    if (target && tagName === "OPTION") target = src_default.parentElement(target);
    if (!target || !tagName || INPUT_TAGS.indexOf(tagName) < 0 || isBlocked(target, blockClass, blockSelector, true)) return;
    if (target.classList.contains(ignoreClass) || ignoreSelector && target.matches(ignoreSelector)) return;
    let text = target.value;
    let isChecked = false;
    const type = getInputType(target) || "";
    if (type === "radio" || type === "checkbox") isChecked = target.checked;
    else if (maskInputOptions[tagName.toLowerCase()] || maskInputOptions[type]) text = maskInputValue({
      element: target,
      maskInputOptions,
      tagName,
      type,
      value: text,
      maskInputFn
    });
    cbWithDedup(target, userTriggeredOnInput ? {
      text,
      isChecked,
      userTriggered
    } : {
      text,
      isChecked
    });
    const name = target.name;
    if (type === "radio" && name && isChecked) doc.querySelectorAll(`input[type="radio"][name="${name}"]`).forEach((el) => {
      if (el !== target) {
        const text2 = el.value;
        cbWithDedup(el, userTriggeredOnInput ? {
          text: text2,
          isChecked: !isChecked,
          userTriggered: false
        } : {
          text: text2,
          isChecked: !isChecked
        });
      }
    });
  }
  function cbWithDedup(target, v2) {
    const lastInputValue = lastInputValueMap.get(target);
    if (!lastInputValue || lastInputValue.text !== v2.text || lastInputValue.isChecked !== v2.isChecked) {
      lastInputValueMap.set(target, v2);
      const id = mirror2.getId(target);
      callbackWrapper(inputCb)(__spreadProps(__spreadValues({}, v2), {
        id
      }));
    }
  }
  const handlers = (sampling.input === "last" ? ["change"] : ["input", "change"]).map((eventName) => on(eventName, callbackWrapper(eventHandler), doc));
  const currentWindow = doc.defaultView;
  if (!currentWindow) return () => {
    handlers.forEach((h) => h());
  };
  const propertyDescriptor = currentWindow.Object.getOwnPropertyDescriptor(currentWindow.HTMLInputElement.prototype, "value");
  const hookProperties = [
    [currentWindow.HTMLInputElement.prototype, "value"],
    [currentWindow.HTMLInputElement.prototype, "checked"],
    [currentWindow.HTMLSelectElement.prototype, "value"],
    [currentWindow.HTMLTextAreaElement.prototype, "value"],
    [currentWindow.HTMLSelectElement.prototype, "selectedIndex"],
    [currentWindow.HTMLOptionElement.prototype, "selected"]
  ];
  if (propertyDescriptor && propertyDescriptor.set) handlers.push(...hookProperties.map((p) => hookSetter(p[0], p[1], { set() {
    callbackWrapper(eventHandler)({
      target: this,
      isTrusted: false
    });
  } }, false, currentWindow)));
  return callbackWrapper(() => {
    handlers.forEach((h) => h());
  });
}
function getNestedCSSRulePositions(rule) {
  const positions = [];
  function recurse(childRule, pos) {
    if (hasNestedCSSRule("CSSGroupingRule") && childRule.parentRule instanceof CSSGroupingRule || hasNestedCSSRule("CSSMediaRule") && childRule.parentRule instanceof CSSMediaRule || hasNestedCSSRule("CSSSupportsRule") && childRule.parentRule instanceof CSSSupportsRule || hasNestedCSSRule("CSSConditionRule") && childRule.parentRule instanceof CSSConditionRule) {
      const index = Array.from(childRule.parentRule.cssRules).indexOf(childRule);
      pos.unshift(index);
      return recurse(childRule.parentRule, pos);
    } else if (childRule.parentStyleSheet) {
      const index = Array.from(childRule.parentStyleSheet.cssRules).indexOf(childRule);
      pos.unshift(index);
    }
    return pos;
  }
  return recurse(rule, positions);
}
function getIdAndStyleId(sheet, mirror2, styleMirror) {
  let id, styleId;
  if (!sheet) return {};
  if (sheet.ownerNode) id = mirror2.getId(sheet.ownerNode);
  else styleId = styleMirror.getId(sheet);
  return {
    styleId,
    id
  };
}
function initStyleSheetObserver({ styleSheetRuleCb, mirror: mirror2, stylesheetManager }, { win }) {
  if (!win.CSSStyleSheet || !win.CSSStyleSheet.prototype) return () => {
  };
  const insertRule = win.CSSStyleSheet.prototype.insertRule;
  win.CSSStyleSheet.prototype.insertRule = new Proxy(insertRule, { apply: callbackWrapper((target, thisArg, argumentsList) => {
    const [rule, index] = argumentsList;
    const { id, styleId } = getIdAndStyleId(thisArg, mirror2, stylesheetManager.styleMirror);
    if (id && id !== -1 || styleId && styleId !== -1) styleSheetRuleCb({
      id,
      styleId,
      adds: [{
        rule,
        index
      }]
    });
    return target.apply(thisArg, argumentsList);
  }) });
  win.CSSStyleSheet.prototype.addRule = function(selector, styleBlock, index = this.cssRules.length) {
    const rule = `${selector} { ${styleBlock} }`;
    return win.CSSStyleSheet.prototype.insertRule.apply(this, [rule, index]);
  };
  const deleteRule = win.CSSStyleSheet.prototype.deleteRule;
  win.CSSStyleSheet.prototype.deleteRule = new Proxy(deleteRule, { apply: callbackWrapper((target, thisArg, argumentsList) => {
    const [index] = argumentsList;
    const { id, styleId } = getIdAndStyleId(thisArg, mirror2, stylesheetManager.styleMirror);
    if (id && id !== -1 || styleId && styleId !== -1) styleSheetRuleCb({
      id,
      styleId,
      removes: [{ index }]
    });
    return target.apply(thisArg, argumentsList);
  }) });
  win.CSSStyleSheet.prototype.removeRule = function(index) {
    return win.CSSStyleSheet.prototype.deleteRule.apply(this, [index]);
  };
  let replace;
  if (win.CSSStyleSheet.prototype.replace) {
    replace = win.CSSStyleSheet.prototype.replace;
    win.CSSStyleSheet.prototype.replace = new Proxy(replace, { apply: callbackWrapper((target, thisArg, argumentsList) => {
      const [text] = argumentsList;
      const { id, styleId } = getIdAndStyleId(thisArg, mirror2, stylesheetManager.styleMirror);
      if (id && id !== -1 || styleId && styleId !== -1) styleSheetRuleCb({
        id,
        styleId,
        replace: text
      });
      return target.apply(thisArg, argumentsList);
    }) });
  }
  let replaceSync;
  if (win.CSSStyleSheet.prototype.replaceSync) {
    replaceSync = win.CSSStyleSheet.prototype.replaceSync;
    win.CSSStyleSheet.prototype.replaceSync = new Proxy(replaceSync, { apply: callbackWrapper((target, thisArg, argumentsList) => {
      const [text] = argumentsList;
      const { id, styleId } = getIdAndStyleId(thisArg, mirror2, stylesheetManager.styleMirror);
      if (id && id !== -1 || styleId && styleId !== -1) styleSheetRuleCb({
        id,
        styleId,
        replaceSync: text
      });
      return target.apply(thisArg, argumentsList);
    }) });
  }
  const supportedNestedCSSRuleTypes = {};
  if (canMonkeyPatchNestedCSSRule("CSSGroupingRule")) supportedNestedCSSRuleTypes.CSSGroupingRule = win.CSSGroupingRule;
  else {
    if (canMonkeyPatchNestedCSSRule("CSSMediaRule")) supportedNestedCSSRuleTypes.CSSMediaRule = win.CSSMediaRule;
    if (canMonkeyPatchNestedCSSRule("CSSConditionRule")) supportedNestedCSSRuleTypes.CSSConditionRule = win.CSSConditionRule;
    if (canMonkeyPatchNestedCSSRule("CSSSupportsRule")) supportedNestedCSSRuleTypes.CSSSupportsRule = win.CSSSupportsRule;
  }
  const unmodifiedFunctions = {};
  Object.entries(supportedNestedCSSRuleTypes).forEach(([typeKey, type]) => {
    unmodifiedFunctions[typeKey] = {
      insertRule: type.prototype.insertRule,
      deleteRule: type.prototype.deleteRule
    };
    type.prototype.insertRule = new Proxy(unmodifiedFunctions[typeKey].insertRule, { apply: callbackWrapper((target, thisArg, argumentsList) => {
      const [rule, index] = argumentsList;
      const { id, styleId } = getIdAndStyleId(thisArg.parentStyleSheet, mirror2, stylesheetManager.styleMirror);
      if (id && id !== -1 || styleId && styleId !== -1) styleSheetRuleCb({
        id,
        styleId,
        adds: [{
          rule,
          index: [...getNestedCSSRulePositions(thisArg), index || 0]
        }]
      });
      return target.apply(thisArg, argumentsList);
    }) });
    type.prototype.deleteRule = new Proxy(unmodifiedFunctions[typeKey].deleteRule, { apply: callbackWrapper((target, thisArg, argumentsList) => {
      const [index] = argumentsList;
      const { id, styleId } = getIdAndStyleId(thisArg.parentStyleSheet, mirror2, stylesheetManager.styleMirror);
      if (id && id !== -1 || styleId && styleId !== -1) styleSheetRuleCb({
        id,
        styleId,
        removes: [{ index: [...getNestedCSSRulePositions(thisArg), index] }]
      });
      return target.apply(thisArg, argumentsList);
    }) });
  });
  return callbackWrapper(() => {
    win.CSSStyleSheet.prototype.insertRule = insertRule;
    win.CSSStyleSheet.prototype.deleteRule = deleteRule;
    replace && (win.CSSStyleSheet.prototype.replace = replace);
    replaceSync && (win.CSSStyleSheet.prototype.replaceSync = replaceSync);
    Object.entries(supportedNestedCSSRuleTypes).forEach(([typeKey, type]) => {
      type.prototype.insertRule = unmodifiedFunctions[typeKey].insertRule;
      type.prototype.deleteRule = unmodifiedFunctions[typeKey].deleteRule;
    });
  });
}
function initAdoptedStyleSheetObserver({ mirror: mirror2, stylesheetManager }, host2) {
  var _a2, _b, _c;
  let hostId = null;
  if (host2.nodeName === "#document") hostId = mirror2.getId(host2);
  else hostId = mirror2.getId(src_default.host(host2));
  const patchTarget = host2.nodeName === "#document" ? (_a2 = host2.defaultView) == null ? void 0 : _a2.Document : (_c = (_b = host2.ownerDocument) == null ? void 0 : _b.defaultView) == null ? void 0 : _c.ShadowRoot;
  const originalPropertyDescriptor = (patchTarget == null ? void 0 : patchTarget.prototype) ? Object.getOwnPropertyDescriptor(patchTarget == null ? void 0 : patchTarget.prototype, "adoptedStyleSheets") : void 0;
  if (hostId === null || hostId === -1 || !patchTarget || !originalPropertyDescriptor) return () => {
  };
  Object.defineProperty(host2, "adoptedStyleSheets", {
    configurable: originalPropertyDescriptor.configurable,
    enumerable: originalPropertyDescriptor.enumerable,
    get() {
      var _a3;
      return (_a3 = originalPropertyDescriptor.get) == null ? void 0 : _a3.call(this);
    },
    set(sheets) {
      var _a3;
      const result = (_a3 = originalPropertyDescriptor.set) == null ? void 0 : _a3.call(this, sheets);
      if (hostId !== null && hostId !== -1) try {
        stylesheetManager.adoptStyleSheets(sheets, hostId);
      } catch (e2) {
      }
      return result;
    }
  });
  return callbackWrapper(() => {
    Object.defineProperty(host2, "adoptedStyleSheets", {
      configurable: originalPropertyDescriptor.configurable,
      enumerable: originalPropertyDescriptor.enumerable,
      get: originalPropertyDescriptor.get,
      set: originalPropertyDescriptor.set
    });
  });
}
function initStyleDeclarationObserver({ styleDeclarationCb, mirror: mirror2, ignoreCSSAttributes, stylesheetManager }, { win }) {
  const setProperty = win.CSSStyleDeclaration.prototype.setProperty;
  win.CSSStyleDeclaration.prototype.setProperty = new Proxy(setProperty, { apply: callbackWrapper((target, thisArg, argumentsList) => {
    var _a2;
    const [property, value, priority] = argumentsList;
    if (ignoreCSSAttributes.has(property)) return setProperty.apply(thisArg, [
      property,
      value,
      priority
    ]);
    const { id, styleId } = getIdAndStyleId((_a2 = thisArg.parentRule) == null ? void 0 : _a2.parentStyleSheet, mirror2, stylesheetManager.styleMirror);
    if (id && id !== -1 || styleId && styleId !== -1) styleDeclarationCb({
      id,
      styleId,
      set: {
        property,
        value,
        priority
      },
      index: getNestedCSSRulePositions(thisArg.parentRule)
    });
    return target.apply(thisArg, argumentsList);
  }) });
  const removeProperty = win.CSSStyleDeclaration.prototype.removeProperty;
  win.CSSStyleDeclaration.prototype.removeProperty = new Proxy(removeProperty, { apply: callbackWrapper((target, thisArg, argumentsList) => {
    var _a2;
    const [property] = argumentsList;
    if (ignoreCSSAttributes.has(property)) return removeProperty.apply(thisArg, [property]);
    const { id, styleId } = getIdAndStyleId((_a2 = thisArg.parentRule) == null ? void 0 : _a2.parentStyleSheet, mirror2, stylesheetManager.styleMirror);
    if (id && id !== -1 || styleId && styleId !== -1) styleDeclarationCb({
      id,
      styleId,
      remove: { property },
      index: getNestedCSSRulePositions(thisArg.parentRule)
    });
    return target.apply(thisArg, argumentsList);
  }) });
  return callbackWrapper(() => {
    win.CSSStyleDeclaration.prototype.setProperty = setProperty;
    win.CSSStyleDeclaration.prototype.removeProperty = removeProperty;
  });
}
function initMediaInteractionObserver({ mediaInteractionCb, blockClass, blockSelector, mirror: mirror2, sampling, doc }) {
  const handler = callbackWrapper((type) => throttle(callbackWrapper((event) => {
    const target = getEventTarget(event);
    if (!target || !(target instanceof HTMLMediaElement) || isBlocked(target, blockClass, blockSelector, true)) return;
    const { currentTime, volume, muted, playbackRate, loop } = target;
    mediaInteractionCb({
      type,
      id: mirror2.getId(target),
      currentTime,
      volume,
      muted,
      playbackRate,
      loop
    });
  }), sampling.media || 500));
  const handlers = [
    on("play", handler(MediaInteractions.Play), doc),
    on("pause", handler(MediaInteractions.Pause), doc),
    on("seeked", handler(MediaInteractions.Seeked), doc),
    on("volumechange", handler(MediaInteractions.VolumeChange), doc),
    on("ratechange", handler(MediaInteractions.RateChange), doc)
  ];
  return callbackWrapper(() => {
    handlers.forEach((h) => h());
  });
}
function initFontObserver({ fontCb, doc }) {
  const win = doc.defaultView;
  if (!win) return () => {
  };
  const handlers = [];
  const fontMap = /* @__PURE__ */ new WeakMap();
  const originalFontFace = win.FontFace;
  win.FontFace = function FontFace2(family, source, descriptors) {
    const fontFace = new originalFontFace(family, source, descriptors);
    fontMap.set(fontFace, {
      family,
      buffer: typeof source !== "string",
      descriptors,
      fontSource: typeof source === "string" ? source : JSON.stringify(Array.from(new Uint8Array(source)))
    });
    return fontFace;
  };
  const restoreHandler = patch(doc.fonts, "add", function(original) {
    return function(fontFace) {
      setTimeout(callbackWrapper(() => {
        const p = fontMap.get(fontFace);
        if (p) {
          fontCb(p);
          fontMap.delete(fontFace);
        }
      }), 0);
      return original.apply(this, [fontFace]);
    };
  });
  handlers.push(() => {
    win.FontFace = originalFontFace;
  });
  handlers.push(restoreHandler);
  return callbackWrapper(() => {
    handlers.forEach((h) => h());
  });
}
function initSelectionObserver(param) {
  const { doc, mirror: mirror2, blockClass, blockSelector, selectionCb } = param;
  let collapsed = true;
  const updateSelection = callbackWrapper(() => {
    const selection = doc.getSelection();
    if (!selection || collapsed && (selection == null ? void 0 : selection.isCollapsed)) return;
    collapsed = selection.isCollapsed || false;
    const ranges = [];
    const count = selection.rangeCount || 0;
    for (let i2 = 0; i2 < count; i2++) {
      const { startContainer, startOffset, endContainer, endOffset } = selection.getRangeAt(i2);
      if (isBlocked(startContainer, blockClass, blockSelector, true) || isBlocked(endContainer, blockClass, blockSelector, true)) continue;
      ranges.push({
        start: mirror2.getId(startContainer),
        startOffset,
        end: mirror2.getId(endContainer),
        endOffset
      });
    }
    selectionCb({ ranges });
  });
  updateSelection();
  return on("selectionchange", updateSelection);
}
function initCustomElementObserver({ doc, customElementCb }) {
  const win = doc.defaultView;
  if (!win || !win.customElements) return () => {
  };
  return patch(win.customElements, "define", function(original) {
    return function(name, constructor, options) {
      try {
        customElementCb({ define: { name } });
      } catch (e2) {
        console.warn(`Custom element callback failed for ${name}`);
      }
      return original.apply(this, [
        name,
        constructor,
        options
      ]);
    };
  });
}
function mergeHooks(o2, hooks) {
  const { mutationCb, mousemoveCb, mouseInteractionCb, scrollCb, viewportResizeCb, inputCb, mediaInteractionCb, styleSheetRuleCb, styleDeclarationCb, canvasMutationCb, fontCb, selectionCb, customElementCb } = o2;
  o2.mutationCb = (...p) => {
    if (hooks.mutation) hooks.mutation(...p);
    mutationCb(...p);
  };
  o2.mousemoveCb = (...p) => {
    if (hooks.mousemove) hooks.mousemove(...p);
    mousemoveCb(...p);
  };
  o2.mouseInteractionCb = (...p) => {
    if (hooks.mouseInteraction) hooks.mouseInteraction(...p);
    mouseInteractionCb(...p);
  };
  o2.scrollCb = (...p) => {
    if (hooks.scroll) hooks.scroll(...p);
    scrollCb(...p);
  };
  o2.viewportResizeCb = (...p) => {
    if (hooks.viewportResize) hooks.viewportResize(...p);
    viewportResizeCb(...p);
  };
  o2.inputCb = (...p) => {
    if (hooks.input) hooks.input(...p);
    inputCb(...p);
  };
  o2.mediaInteractionCb = (...p) => {
    if (hooks.mediaInteaction) hooks.mediaInteaction(...p);
    mediaInteractionCb(...p);
  };
  o2.styleSheetRuleCb = (...p) => {
    if (hooks.styleSheetRule) hooks.styleSheetRule(...p);
    styleSheetRuleCb(...p);
  };
  o2.styleDeclarationCb = (...p) => {
    if (hooks.styleDeclaration) hooks.styleDeclaration(...p);
    styleDeclarationCb(...p);
  };
  o2.canvasMutationCb = (...p) => {
    if (hooks.canvasMutation) hooks.canvasMutation(...p);
    canvasMutationCb(...p);
  };
  o2.fontCb = (...p) => {
    if (hooks.font) hooks.font(...p);
    fontCb(...p);
  };
  o2.selectionCb = (...p) => {
    if (hooks.selection) hooks.selection(...p);
    selectionCb(...p);
  };
  o2.customElementCb = (...c2) => {
    if (hooks.customElement) hooks.customElement(...c2);
    customElementCb(...c2);
  };
}
function initObservers(o2, hooks = {}) {
  const currentWindow = o2.doc.defaultView;
  if (!currentWindow) return () => {
  };
  mergeHooks(o2, hooks);
  let stopMutationObserver;
  if (o2.recordDOM) stopMutationObserver = initMutationObserver(o2, o2.doc);
  const mousemoveHandler = initMoveObserver(o2);
  const mouseInteractionHandler = initMouseInteractionObserver(o2);
  const scrollHandler = initScrollObserver(o2);
  const viewportResizeHandler = initViewportResizeObserver(o2, { win: currentWindow });
  const inputHandler = initInputObserver(o2);
  const mediaInteractionHandler = initMediaInteractionObserver(o2);
  let styleSheetObserver = () => {
  };
  let adoptedStyleSheetObserver = () => {
  };
  let styleDeclarationObserver = () => {
  };
  let fontObserver = () => {
  };
  if (o2.recordDOM) {
    styleSheetObserver = initStyleSheetObserver(o2, { win: currentWindow });
    adoptedStyleSheetObserver = initAdoptedStyleSheetObserver(o2, o2.doc);
    styleDeclarationObserver = initStyleDeclarationObserver(o2, { win: currentWindow });
    if (o2.collectFonts) fontObserver = initFontObserver(o2);
  }
  const selectionObserver = initSelectionObserver(o2);
  const customElementObserver = initCustomElementObserver(o2);
  const pluginHandlers = [];
  for (const plugin of o2.plugins) pluginHandlers.push(plugin.observer(plugin.callback, currentWindow, plugin.options));
  return callbackWrapper(() => {
    stopMutationObserver == null ? void 0 : stopMutationObserver();
    mousemoveHandler();
    mouseInteractionHandler();
    scrollHandler();
    viewportResizeHandler();
    inputHandler();
    mediaInteractionHandler();
    styleSheetObserver();
    adoptedStyleSheetObserver();
    styleDeclarationObserver();
    fontObserver();
    selectionObserver();
    customElementObserver();
    pluginHandlers.forEach((h) => h());
  });
}
function hasNestedCSSRule(prop) {
  return typeof window[prop] !== "undefined";
}
function canMonkeyPatchNestedCSSRule(prop) {
  return Boolean(typeof window[prop] !== "undefined" && window[prop].prototype && "insertRule" in window[prop].prototype && "deleteRule" in window[prop].prototype);
}
var CrossOriginIframeMirror = class {
  constructor(generateIdFn) {
    __publicField(this, "generateIdFn");
    __publicField(this, "iframeIdToRemoteIdMap", /* @__PURE__ */ new WeakMap());
    __publicField(this, "iframeRemoteIdToIdMap", /* @__PURE__ */ new WeakMap());
    this.generateIdFn = generateIdFn;
  }
  getId(iframe, remoteId, idToRemoteMap, remoteToIdMap) {
    const idToRemoteIdMap = idToRemoteMap || this.getIdToRemoteIdMap(iframe);
    const remoteIdToIdMap = remoteToIdMap || this.getRemoteIdToIdMap(iframe);
    let id = idToRemoteIdMap.get(remoteId);
    if (!id) {
      id = this.generateIdFn();
      idToRemoteIdMap.set(remoteId, id);
      remoteIdToIdMap.set(id, remoteId);
    }
    return id;
  }
  getIds(iframe, remoteId) {
    const idToRemoteIdMap = this.getIdToRemoteIdMap(iframe);
    const remoteIdToIdMap = this.getRemoteIdToIdMap(iframe);
    return remoteId.map((id) => this.getId(iframe, id, idToRemoteIdMap, remoteIdToIdMap));
  }
  getRemoteId(iframe, id, map) {
    const remoteIdToIdMap = map || this.getRemoteIdToIdMap(iframe);
    if (typeof id !== "number") return id;
    const remoteId = remoteIdToIdMap.get(id);
    if (!remoteId) return -1;
    return remoteId;
  }
  getRemoteIds(iframe, ids) {
    const remoteIdToIdMap = this.getRemoteIdToIdMap(iframe);
    return ids.map((id) => this.getRemoteId(iframe, id, remoteIdToIdMap));
  }
  reset(iframe) {
    if (!iframe) {
      this.iframeIdToRemoteIdMap = /* @__PURE__ */ new WeakMap();
      this.iframeRemoteIdToIdMap = /* @__PURE__ */ new WeakMap();
      return;
    }
    this.iframeIdToRemoteIdMap.delete(iframe);
    this.iframeRemoteIdToIdMap.delete(iframe);
  }
  getIdToRemoteIdMap(iframe) {
    let idToRemoteIdMap = this.iframeIdToRemoteIdMap.get(iframe);
    if (!idToRemoteIdMap) {
      idToRemoteIdMap = /* @__PURE__ */ new Map();
      this.iframeIdToRemoteIdMap.set(iframe, idToRemoteIdMap);
    }
    return idToRemoteIdMap;
  }
  getRemoteIdToIdMap(iframe) {
    let remoteIdToIdMap = this.iframeRemoteIdToIdMap.get(iframe);
    if (!remoteIdToIdMap) {
      remoteIdToIdMap = /* @__PURE__ */ new Map();
      this.iframeRemoteIdToIdMap.set(iframe, remoteIdToIdMap);
    }
    return remoteIdToIdMap;
  }
};
var IframeManager = class {
  constructor(options) {
    __publicField(this, "iframes", /* @__PURE__ */ new WeakMap());
    __publicField(this, "crossOriginIframeMap", /* @__PURE__ */ new WeakMap());
    __publicField(this, "crossOriginIframeMirror", new CrossOriginIframeMirror(genId));
    __publicField(this, "crossOriginIframeStyleMirror");
    __publicField(this, "crossOriginIframeRootIdMap", /* @__PURE__ */ new WeakMap());
    __publicField(this, "mirror");
    __publicField(this, "mutationCb");
    __publicField(this, "wrappedEmit");
    __publicField(this, "loadListener");
    __publicField(this, "unloadListener");
    __publicField(this, "attachedWindows", /* @__PURE__ */ new Map());
    __publicField(this, "messageHandler", (message) => this.handleMessage(message));
    __publicField(this, "stylesheetManager");
    __publicField(this, "recordCrossOriginIframes");
    this.mutationCb = options.mutationCb;
    this.wrappedEmit = options.wrappedEmit;
    this.stylesheetManager = options.stylesheetManager;
    this.recordCrossOriginIframes = options.recordCrossOriginIframes;
    this.crossOriginIframeStyleMirror = new CrossOriginIframeMirror(this.stylesheetManager.styleMirror.generateId.bind(this.stylesheetManager.styleMirror));
    this.mirror = options.mirror;
    if (this.recordCrossOriginIframes) window.addEventListener("message", this.messageHandler);
  }
  addIframe(iframeEl) {
    this.iframes.set(iframeEl, true);
    if (iframeEl.contentWindow) this.crossOriginIframeMap.set(iframeEl.contentWindow, iframeEl);
  }
  addLoadListener(cb) {
    this.loadListener = cb;
  }
  addUnloadListener(cb) {
    this.unloadListener = cb;
  }
  attachIframe(iframeEl, childSn) {
    var _a2;
    this.mutationCb({
      adds: [{
        parentId: this.mirror.getId(iframeEl),
        nextId: null,
        node: childSn
      }],
      removes: [],
      texts: [],
      attributes: [],
      isAttachIframe: true
    });
    this.watchIframeWindow(iframeEl);
    (_a2 = this.loadListener) == null ? void 0 : _a2.call(this, iframeEl);
    if (iframeEl.contentDocument && iframeEl.contentDocument.adoptedStyleSheets && iframeEl.contentDocument.adoptedStyleSheets.length > 0) this.stylesheetManager.adoptStyleSheets(iframeEl.contentDocument.adoptedStyleSheets, this.mirror.getId(iframeEl.contentDocument));
  }
  destroy() {
    window.removeEventListener("message", this.messageHandler);
    this.attachedWindows.forEach((stop) => stop());
  }
  watchIframeWindow(iframeEl) {
    const win = iframeEl.contentWindow;
    if (!win || this.attachedWindows.has(win)) return;
    const handlers = [];
    const stop = () => {
      this.attachedWindows.delete(win);
      handlers.forEach((handler) => {
        try {
          handler();
        } catch (e2) {
        }
      });
    };
    this.attachedWindows.set(win, stop);
    if (this.recordCrossOriginIframes) handlers.push(on("message", this.messageHandler, win));
    handlers.push(on("pagehide", (event) => {
      var _a2;
      if (event.persisted) return;
      const doc = win.document;
      stop();
      (_a2 = this.unloadListener) == null ? void 0 : _a2.call(this, doc);
    }, win));
  }
  handleMessage(message) {
    const crossOriginMessageEvent = message;
    if (crossOriginMessageEvent.data.type !== "rrweb" || crossOriginMessageEvent.origin !== crossOriginMessageEvent.data.origin) return;
    if (!message.source) return;
    const iframeEl = this.crossOriginIframeMap.get(message.source);
    if (!iframeEl) return;
    const transformedEvent = this.transformCrossOriginEvent(iframeEl, crossOriginMessageEvent.data.event);
    if (transformedEvent) this.wrappedEmit(transformedEvent, crossOriginMessageEvent.data.isCheckout);
  }
  transformCrossOriginEvent(iframeEl, e2) {
    var _a2;
    switch (e2.type) {
      case EventType.FullSnapshot: {
        this.crossOriginIframeMirror.reset(iframeEl);
        this.crossOriginIframeStyleMirror.reset(iframeEl);
        this.replaceIdOnNode(e2.data.node, iframeEl);
        const rootId = e2.data.node.id;
        this.crossOriginIframeRootIdMap.set(iframeEl, rootId);
        this.patchRootIdOnNode(e2.data.node, rootId);
        return {
          timestamp: e2.timestamp,
          type: EventType.IncrementalSnapshot,
          data: {
            source: IncrementalSource.Mutation,
            adds: [{
              parentId: this.mirror.getId(iframeEl),
              nextId: null,
              node: e2.data.node
            }],
            removes: [],
            texts: [],
            attributes: [],
            isAttachIframe: true
          }
        };
      }
      case EventType.Meta:
      case EventType.Load:
      case EventType.DomContentLoaded:
        return false;
      case EventType.Annotation:
      case EventType.Plugin:
        return e2;
      case EventType.Custom:
        this.replaceIds(e2.data.payload, iframeEl, [
          "id",
          "parentId",
          "previousId",
          "nextId"
        ]);
        return e2;
      case EventType.IncrementalSnapshot:
        switch (e2.data.source) {
          case IncrementalSource.Mutation:
            e2.data.adds.forEach((n2) => {
              this.replaceIds(n2, iframeEl, [
                "parentId",
                "nextId",
                "previousId"
              ]);
              this.replaceIdOnNode(n2.node, iframeEl);
              const rootId = this.crossOriginIframeRootIdMap.get(iframeEl);
              rootId && this.patchRootIdOnNode(n2.node, rootId);
            });
            e2.data.removes.forEach((n2) => {
              this.replaceIds(n2, iframeEl, ["parentId", "id"]);
            });
            e2.data.attributes.forEach((n2) => {
              this.replaceIds(n2, iframeEl, ["id"]);
            });
            e2.data.texts.forEach((n2) => {
              this.replaceIds(n2, iframeEl, ["id"]);
            });
            return e2;
          case IncrementalSource.Drag:
          case IncrementalSource.TouchMove:
          case IncrementalSource.MouseMove:
            e2.data.positions.forEach((p) => {
              this.replaceIds(p, iframeEl, ["id"]);
            });
            return e2;
          case IncrementalSource.ViewportResize:
            return false;
          case IncrementalSource.MediaInteraction:
          case IncrementalSource.MouseInteraction:
          case IncrementalSource.Scroll:
          case IncrementalSource.CanvasMutation:
          case IncrementalSource.Input:
            this.replaceIds(e2.data, iframeEl, ["id"]);
            return e2;
          case IncrementalSource.StyleSheetRule:
          case IncrementalSource.StyleDeclaration:
            this.replaceIds(e2.data, iframeEl, ["id"]);
            this.replaceStyleIds(e2.data, iframeEl, ["styleId"]);
            return e2;
          case IncrementalSource.Font:
            return e2;
          case IncrementalSource.Selection:
            e2.data.ranges.forEach((range) => {
              this.replaceIds(range, iframeEl, ["start", "end"]);
            });
            return e2;
          case IncrementalSource.AdoptedStyleSheet:
            this.replaceIds(e2.data, iframeEl, ["id"]);
            this.replaceStyleIds(e2.data, iframeEl, ["styleIds"]);
            (_a2 = e2.data.styles) == null ? void 0 : _a2.forEach((style) => {
              this.replaceStyleIds(style, iframeEl, ["styleId"]);
            });
            return e2;
        }
    }
    return false;
  }
  replace(iframeMirror, obj, iframeEl, keys) {
    for (const key of keys) {
      if (!Array.isArray(obj[key]) && typeof obj[key] !== "number") continue;
      if (Array.isArray(obj[key])) obj[key] = iframeMirror.getIds(iframeEl, obj[key]);
      else obj[key] = iframeMirror.getId(iframeEl, obj[key]);
    }
    return obj;
  }
  replaceIds(obj, iframeEl, keys) {
    return this.replace(this.crossOriginIframeMirror, obj, iframeEl, keys);
  }
  replaceStyleIds(obj, iframeEl, keys) {
    return this.replace(this.crossOriginIframeStyleMirror, obj, iframeEl, keys);
  }
  replaceIdOnNode(node, iframeEl) {
    this.replaceIds(node, iframeEl, ["id", "rootId"]);
    if ("childNodes" in node) node.childNodes.forEach((child) => {
      this.replaceIdOnNode(child, iframeEl);
    });
  }
  patchRootIdOnNode(node, rootId) {
    if (node.type !== NodeType.Document && !node.rootId) node.rootId = rootId;
    if ("childNodes" in node) node.childNodes.forEach((child) => {
      this.patchRootIdOnNode(child, rootId);
    });
  }
};
var ShadowDomManager = class {
  constructor(options) {
    /** Observed shadow roots and the handlers that stop observing them. */
    __publicField(this, "shadowDoms", /* @__PURE__ */ new Map());
    __publicField(this, "mutationCb");
    __publicField(this, "scrollCb");
    __publicField(this, "bypassOptions");
    __publicField(this, "mirror");
    __publicField(this, "restoreHandlers", []);
    this.mutationCb = options.mutationCb;
    this.scrollCb = options.scrollCb;
    this.bypassOptions = options.bypassOptions;
    this.mirror = options.mirror;
    this.init();
  }
  init() {
    this.reset();
    this.patchAttachShadow(Element, document);
  }
  addShadowRoot(shadowRoot2, doc) {
    if (!isNativeShadowDom(shadowRoot2)) return;
    if (this.shadowDoms.has(shadowRoot2)) return;
    const handlers = [];
    this.shadowDoms.set(shadowRoot2, handlers);
    const stopMutationObserver = initMutationObserver(__spreadProps(__spreadValues({}, this.bypassOptions), {
      doc,
      mutationCb: this.mutationCb,
      mirror: this.mirror,
      shadowDomManager: this
    }), shadowRoot2);
    handlers.push(stopMutationObserver);
    handlers.push(initScrollObserver(__spreadProps(__spreadValues({}, this.bypassOptions), {
      scrollCb: this.scrollCb,
      doc: shadowRoot2,
      mirror: this.mirror
    })));
    setTimeout(() => {
      if (this.shadowDoms.get(shadowRoot2) !== handlers) return;
      if (shadowRoot2.adoptedStyleSheets && shadowRoot2.adoptedStyleSheets.length > 0) this.bypassOptions.stylesheetManager.adoptStyleSheets(shadowRoot2.adoptedStyleSheets, this.mirror.getId(src_default.host(shadowRoot2)));
      handlers.push(initAdoptedStyleSheetObserver({
        mirror: this.mirror,
        stylesheetManager: this.bypassOptions.stylesheetManager
      }, shadowRoot2));
    }, 0);
  }
  removeShadowRootsIn(n2) {
    if (this.shadowDoms.size === 0) return;
    const shadowRoot2 = src_default.shadowRoot(n2);
    if (shadowRoot2) {
      if (this.shadowDoms.has(shadowRoot2)) this.removeShadowRoot(shadowRoot2);
      this.removeShadowRootsIn(shadowRoot2);
    }
    src_default.childNodes(n2).forEach((child) => this.removeShadowRootsIn(child));
  }
  removeShadowRoot(shadowRoot2) {
    const handlers = this.shadowDoms.get(shadowRoot2);
    this.shadowDoms.delete(shadowRoot2);
    handlers == null ? void 0 : handlers.forEach(runHandler);
  }
  /**
  * Monkey patch 'attachShadow' of an IFrameElement to observe newly added shadow doms.
  */
  observeAttachShadow(iframeElement) {
    if (!iframeElement.contentWindow || !iframeElement.contentDocument) return;
    this.patchAttachShadow(iframeElement.contentWindow.Element, iframeElement.contentDocument);
  }
  /**
  * Patch 'attachShadow' to observe newly added shadow doms.
  */
  patchAttachShadow(element, doc) {
    const manager = this;
    this.restoreHandlers.push(patch(element.prototype, "attachShadow", function(original) {
      return function(option) {
        const sRoot = original.call(this, option);
        const shadowRootEl = src_default.shadowRoot(this);
        if (shadowRootEl && inDom(this)) manager.addShadowRoot(shadowRootEl, doc);
        return sRoot;
      };
    }));
  }
  reset() {
    this.restoreHandlers.forEach(runHandler);
    this.restoreHandlers = [];
    const shadowDoms = this.shadowDoms;
    this.shadowDoms = /* @__PURE__ */ new Map();
    shadowDoms.forEach((handlers) => handlers.forEach(runHandler));
  }
};
function runHandler(handler) {
  try {
    handler();
  } catch (e2) {
  }
}
var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
var lookup = typeof Uint8Array === "undefined" ? [] : /* @__PURE__ */ new Uint8Array(256);
for (i$1 = 0; i$1 < chars.length; i$1++) lookup[chars.charCodeAt(i$1)] = i$1;
var i$1;
var encode = function(arraybuffer) {
  var bytes = new Uint8Array(arraybuffer), i2, len = bytes.length, base64 = "";
  for (i2 = 0; i2 < len; i2 += 3) {
    base64 += chars[bytes[i2] >> 2];
    base64 += chars[(bytes[i2] & 3) << 4 | bytes[i2 + 1] >> 4];
    base64 += chars[(bytes[i2 + 1] & 15) << 2 | bytes[i2 + 2] >> 6];
    base64 += chars[bytes[i2 + 2] & 63];
  }
  if (len % 3 === 2) base64 = base64.substring(0, base64.length - 1) + "=";
  else if (len % 3 === 1) base64 = base64.substring(0, base64.length - 2) + "==";
  return base64;
};
var decode = function(base64) {
  var bufferLength = base64.length * 0.75, len = base64.length, i2, p = 0, encoded1, encoded2, encoded3, encoded4;
  if (base64[base64.length - 1] === "=") {
    bufferLength--;
    if (base64[base64.length - 2] === "=") bufferLength--;
  }
  var arraybuffer = new ArrayBuffer(bufferLength), bytes = new Uint8Array(arraybuffer);
  for (i2 = 0; i2 < len; i2 += 4) {
    encoded1 = lookup[base64.charCodeAt(i2)];
    encoded2 = lookup[base64.charCodeAt(i2 + 1)];
    encoded3 = lookup[base64.charCodeAt(i2 + 2)];
    encoded4 = lookup[base64.charCodeAt(i2 + 3)];
    bytes[p++] = encoded1 << 2 | encoded2 >> 4;
    bytes[p++] = (encoded2 & 15) << 4 | encoded3 >> 2;
    bytes[p++] = (encoded3 & 3) << 6 | encoded4 & 63;
  }
  return arraybuffer;
};
var canvasVarMap = /* @__PURE__ */ new Map();
function variableListFor$1(ctx, ctor) {
  let contextMap = canvasVarMap.get(ctx);
  if (!contextMap) {
    contextMap = /* @__PURE__ */ new Map();
    canvasVarMap.set(ctx, contextMap);
  }
  if (!contextMap.has(ctor)) contextMap.set(ctor, []);
  return contextMap.get(ctor);
}
var saveWebGLVar = (value, win, ctx) => {
  if (!value || !(isInstanceOfWebGLObject(value, win) || typeof value === "object")) return;
  const name = value.constructor.name;
  const list = variableListFor$1(ctx, name);
  let index = list.indexOf(value);
  if (index === -1) {
    index = list.length;
    list.push(value);
  }
  return index;
};
function serializeArg(value, win, ctx) {
  if (value instanceof Array) return value.map((arg) => serializeArg(arg, win, ctx));
  else if (value === null) return value;
  else if (value instanceof Float32Array || value instanceof Float64Array || value instanceof Int32Array || value instanceof Uint32Array || value instanceof Uint8Array || value instanceof Uint16Array || value instanceof Int16Array || value instanceof Int8Array || value instanceof Uint8ClampedArray) return {
    rr_type: value.constructor.name,
    args: [Object.values(value)]
  };
  else if (value instanceof ArrayBuffer) return {
    rr_type: value.constructor.name,
    base64: encode(value)
  };
  else if (value instanceof DataView) return {
    rr_type: value.constructor.name,
    args: [
      serializeArg(value.buffer, win, ctx),
      value.byteOffset,
      value.byteLength
    ]
  };
  else if (value instanceof HTMLImageElement) {
    const name = value.constructor.name;
    const { src } = value;
    return {
      rr_type: name,
      src
    };
  } else if (value instanceof HTMLCanvasElement) return {
    rr_type: "HTMLImageElement",
    src: value.toDataURL()
  };
  else if (value instanceof ImageData) return {
    rr_type: value.constructor.name,
    args: [
      serializeArg(value.data, win, ctx),
      value.width,
      value.height
    ]
  };
  else if (isInstanceOfWebGLObject(value, win) || typeof value === "object") return {
    rr_type: value.constructor.name,
    index: saveWebGLVar(value, win, ctx)
  };
  return value;
}
var serializeArgs = (args, win, ctx) => {
  return args.map((arg) => serializeArg(arg, win, ctx));
};
var isInstanceOfWebGLObject = (value, win) => {
  const supportedWebGLConstructorNames = [
    "WebGLActiveInfo",
    "WebGLBuffer",
    "WebGLFramebuffer",
    "WebGLProgram",
    "WebGLRenderbuffer",
    "WebGLShader",
    "WebGLShaderPrecisionFormat",
    "WebGLTexture",
    "WebGLUniformLocation",
    "WebGLVertexArrayObject",
    "WebGLVertexArrayObjectOES"
  ].filter((name) => typeof win[name] === "function");
  return Boolean(supportedWebGLConstructorNames.find((name) => value instanceof win[name]));
};
function initCanvas2DMutationObserver(cb, win, blockClass, blockSelector) {
  const handlers = [];
  const props2D = Object.getOwnPropertyNames(win.CanvasRenderingContext2D.prototype);
  for (const prop of props2D) try {
    if (typeof win.CanvasRenderingContext2D.prototype[prop] !== "function") continue;
    const restoreHandler = patch(win.CanvasRenderingContext2D.prototype, prop, function(original) {
      return function(...args) {
        if (!isBlocked(this.canvas, blockClass, blockSelector, true)) setTimeout(() => {
          const recordArgs = serializeArgs(args, win, this);
          cb(this.canvas, {
            type: CanvasContext["2D"],
            property: prop,
            args: recordArgs
          });
        }, 0);
        return original.apply(this, args);
      };
    });
    handlers.push(restoreHandler);
  } catch (e2) {
    const hookHandler = hookSetter(win.CanvasRenderingContext2D.prototype, prop, { set(v2) {
      cb(this.canvas, {
        type: CanvasContext["2D"],
        property: prop,
        args: [v2],
        setter: true
      });
    } });
    handlers.push(hookHandler);
  }
  return () => {
    handlers.forEach((h) => h());
  };
}
function getNormalizedContextName(contextType) {
  return contextType === "experimental-webgl" ? "webgl" : contextType;
}
function initCanvasContextObserver(win, blockClass, blockSelector, setPreserveDrawingBufferToTrue) {
  const handlers = [];
  try {
    const restoreHandler = patch(win.HTMLCanvasElement.prototype, "getContext", function(original) {
      return function(contextType, ...args) {
        if (!isBlocked(this, blockClass, blockSelector, true)) {
          const ctxName = getNormalizedContextName(contextType);
          if (!("__context" in this)) this.__context = ctxName;
          if (setPreserveDrawingBufferToTrue && ["webgl", "webgl2"].includes(ctxName)) {
            if (args[0] && typeof args[0] === "object") {
              const contextAttributes = args[0];
              if (!contextAttributes.preserveDrawingBuffer) contextAttributes.preserveDrawingBuffer = true;
            } else args.splice(0, 1, { preserveDrawingBuffer: true });
          }
        }
        return original.apply(this, [contextType, ...args]);
      };
    });
    handlers.push(restoreHandler);
  } catch (e2) {
    console.error("failed to patch HTMLCanvasElement.prototype.getContext");
  }
  return () => {
    handlers.forEach((h) => h());
  };
}
function patchGLPrototype(prototype, type, cb, blockClass, blockSelector, win) {
  const handlers = [];
  const props = Object.getOwnPropertyNames(prototype);
  for (const prop of props) {
    if ([
      "isContextLost",
      "canvas",
      "drawingBufferWidth",
      "drawingBufferHeight"
    ].includes(prop)) continue;
    try {
      if (typeof prototype[prop] !== "function") continue;
      const restoreHandler = patch(prototype, prop, function(original) {
        return function(...args) {
          const result = original.apply(this, args);
          saveWebGLVar(result, win, this);
          if ("tagName" in this.canvas && !isBlocked(this.canvas, blockClass, blockSelector, true)) {
            const recordArgs = serializeArgs(args, win, this);
            const mutation = {
              type,
              property: prop,
              args: recordArgs
            };
            cb(this.canvas, mutation);
          }
          return result;
        };
      });
      handlers.push(restoreHandler);
    } catch (e2) {
      const hookHandler = hookSetter(prototype, prop, { set(v2) {
        cb(this.canvas, {
          type,
          property: prop,
          args: [v2],
          setter: true
        });
      } });
      handlers.push(hookHandler);
    }
  }
  return handlers;
}
function initCanvasWebGLMutationObserver(cb, win, blockClass, blockSelector) {
  const handlers = [];
  if (typeof win.WebGLRenderingContext !== "undefined") handlers.push(...patchGLPrototype(win.WebGLRenderingContext.prototype, CanvasContext.WebGL, cb, blockClass, blockSelector, win));
  if (typeof win.WebGL2RenderingContext !== "undefined") handlers.push(...patchGLPrototype(win.WebGL2RenderingContext.prototype, CanvasContext.WebGL2, cb, blockClass, blockSelector, win));
  return () => {
    handlers.forEach((h) => h());
  };
}
var jsContent = '(function() {\n	// ../../node_modules/base64-arraybuffer/dist/base64-arraybuffer.es5.js\n	var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";\n	var lookup = typeof Uint8Array === "undefined" ? [] : /* @__PURE__ */ new Uint8Array(256);\n	for (var i = 0; i < chars.length; i++) lookup[chars.charCodeAt(i)] = i;\n	var encode = function(arraybuffer) {\n		var bytes = new Uint8Array(arraybuffer), i, len = bytes.length, base64 = "";\n		for (i = 0; i < len; i += 3) {\n			base64 += chars[bytes[i] >> 2];\n			base64 += chars[(bytes[i] & 3) << 4 | bytes[i + 1] >> 4];\n			base64 += chars[(bytes[i + 1] & 15) << 2 | bytes[i + 2] >> 6];\n			base64 += chars[bytes[i + 2] & 63];\n		}\n		if (len % 3 === 2) base64 = base64.substring(0, base64.length - 1) + "=";\n		else if (len % 3 === 1) base64 = base64.substring(0, base64.length - 2) + "==";\n		return base64;\n	};\n	// src/record/workers/image-bitmap-data-url-worker.ts\n	const lastBlobMap = /* @__PURE__ */ new Map();\n	const transparentBlobMap = /* @__PURE__ */ new Map();\n	async function getTransparentBlobFor(width, height, dataURLOptions) {\n		const id = `${width}-${height}`;\n		if ("OffscreenCanvas" in globalThis) {\n			if (transparentBlobMap.has(id)) return transparentBlobMap.get(id);\n			const offscreen = new OffscreenCanvas(width, height);\n			offscreen.getContext("2d");\n			const base64 = encode(await (await offscreen.convertToBlob(dataURLOptions)).arrayBuffer());\n			transparentBlobMap.set(id, base64);\n			return base64;\n		} else return "";\n	}\n	const worker = self;\n	worker.onmessage = async function(e) {\n		if ("OffscreenCanvas" in globalThis) {\n			const { id, bitmap, width, height, dataURLOptions } = e.data;\n			const transparentBase64 = getTransparentBlobFor(width, height, dataURLOptions);\n			const offscreen = new OffscreenCanvas(width, height);\n			offscreen.getContext("2d").drawImage(bitmap, 0, 0);\n			bitmap.close();\n			const blob = await offscreen.convertToBlob(dataURLOptions);\n			const type = blob.type;\n			const base64 = encode(await blob.arrayBuffer());\n			if (!lastBlobMap.has(id) && await transparentBase64 === base64) {\n				lastBlobMap.set(id, base64);\n				return worker.postMessage({ id });\n			}\n			if (lastBlobMap.get(id) === base64) return worker.postMessage({ id });\n			worker.postMessage({\n				id,\n				type,\n				base64,\n				width,\n				height\n			});\n			lastBlobMap.set(id, base64);\n		} else return worker.postMessage({ id: e.data.id });\n	};\n})();\n\n//# sourceMappingURL=image-bitmap-data-url-worker-NVDCVKXO.js.map';
var blob = typeof self !== "undefined" && self.Blob && new Blob(["(self.URL || self.webkitURL).revokeObjectURL(self.location.href);", jsContent], { type: "text/javascript;charset=utf-8" });
function WorkerWrapper(options) {
  let objURL;
  try {
    objURL = blob && (self.URL || self.webkitURL).createObjectURL(blob);
    if (!objURL) throw "";
    const worker = new Worker(objURL, { name: options == null ? void 0 : options.name });
    worker.addEventListener("error", () => {
      (self.URL || self.webkitURL).revokeObjectURL(objURL);
    });
    return worker;
  } catch (e2) {
    return new Worker("data:text/javascript;charset=utf-8," + encodeURIComponent(jsContent), { name: options == null ? void 0 : options.name });
  }
}
var CanvasManager = class {
  constructor(options) {
    __publicField(this, "pendingCanvasMutations", /* @__PURE__ */ new Map());
    __publicField(this, "rafStamps", {
      latestId: 0,
      invokeId: null
    });
    __publicField(this, "mirror");
    __publicField(this, "mutationCb");
    __publicField(this, "resetObservers");
    __publicField(this, "frozen", false);
    __publicField(this, "locked", false);
    __publicField(this, "processMutation", (target, mutation) => {
      if (this.rafStamps.invokeId && this.rafStamps.latestId !== this.rafStamps.invokeId || !this.rafStamps.invokeId) this.rafStamps.invokeId = this.rafStamps.latestId;
      if (!this.pendingCanvasMutations.has(target)) this.pendingCanvasMutations.set(target, []);
      this.pendingCanvasMutations.get(target).push(mutation);
    });
    const { sampling = "all", win, blockClass, blockSelector, recordCanvas, dataURLOptions } = options;
    this.mutationCb = options.mutationCb;
    this.mirror = options.mirror;
    if (recordCanvas && sampling === "all") this.initCanvasMutationObserver(win, blockClass, blockSelector);
    if (recordCanvas && typeof sampling === "number") this.initCanvasFPSObserver(sampling, win, blockClass, blockSelector, { dataURLOptions });
  }
  reset() {
    this.pendingCanvasMutations.clear();
    this.resetObservers && this.resetObservers();
  }
  freeze() {
    this.frozen = true;
  }
  unfreeze() {
    this.frozen = false;
  }
  lock() {
    this.locked = true;
  }
  unlock() {
    this.locked = false;
  }
  initCanvasFPSObserver(fps, win, blockClass, blockSelector, options) {
    const canvasContextReset = initCanvasContextObserver(win, blockClass, blockSelector, true);
    const snapshotInProgressMap = /* @__PURE__ */ new Map();
    const worker = new WorkerWrapper();
    worker.onmessage = (e2) => {
      const { id } = e2.data;
      snapshotInProgressMap.set(id, false);
      if (!("base64" in e2.data)) return;
      const { base64, type, width, height } = e2.data;
      this.mutationCb({
        id,
        type: CanvasContext["2D"],
        commands: [{
          property: "clearRect",
          args: [
            0,
            0,
            width,
            height
          ]
        }, {
          property: "drawImage",
          args: [
            {
              rr_type: "ImageBitmap",
              args: [{
                rr_type: "Blob",
                data: [{
                  rr_type: "ArrayBuffer",
                  base64
                }],
                type
              }]
            },
            0,
            0
          ]
        }]
      });
    };
    const timeBetweenSnapshots = 1e3 / fps;
    let lastSnapshotTime = 0;
    let rafId;
    const getCanvas = () => {
      const matchedCanvas = [];
      win.document.querySelectorAll("canvas").forEach((canvas) => {
        if (!isBlocked(canvas, blockClass, blockSelector, true)) matchedCanvas.push(canvas);
      });
      return matchedCanvas;
    };
    const takeCanvasSnapshots = (timestamp) => {
      if (lastSnapshotTime && timestamp - lastSnapshotTime < timeBetweenSnapshots) {
        rafId = requestAnimationFrame(takeCanvasSnapshots);
        return;
      }
      lastSnapshotTime = timestamp;
      getCanvas().forEach(async (canvas) => {
        var _a2;
        const id = this.mirror.getId(canvas);
        if (snapshotInProgressMap.get(id)) return;
        if (canvas.width === 0 || canvas.height === 0) return;
        snapshotInProgressMap.set(id, true);
        if (["webgl", "webgl2"].includes(canvas.__context)) {
          const context = canvas.getContext(canvas.__context);
          if (((_a2 = context == null ? void 0 : context.getContextAttributes()) == null ? void 0 : _a2.preserveDrawingBuffer) === false) context.clear(context.COLOR_BUFFER_BIT);
        }
        const bitmap = await createImageBitmap(canvas);
        worker.postMessage({
          id,
          bitmap,
          width: canvas.width,
          height: canvas.height,
          dataURLOptions: options.dataURLOptions
        }, [bitmap]);
      });
      rafId = requestAnimationFrame(takeCanvasSnapshots);
    };
    rafId = requestAnimationFrame(takeCanvasSnapshots);
    this.resetObservers = () => {
      canvasContextReset();
      cancelAnimationFrame(rafId);
    };
  }
  initCanvasMutationObserver(win, blockClass, blockSelector) {
    this.startRAFTimestamping();
    this.startPendingCanvasMutationFlusher();
    const canvasContextReset = initCanvasContextObserver(win, blockClass, blockSelector, false);
    const canvas2DReset = initCanvas2DMutationObserver(this.processMutation.bind(this), win, blockClass, blockSelector);
    const canvasWebGL1and2Reset = initCanvasWebGLMutationObserver(this.processMutation.bind(this), win, blockClass, blockSelector);
    this.resetObservers = () => {
      canvasContextReset();
      canvas2DReset();
      canvasWebGL1and2Reset();
    };
  }
  startPendingCanvasMutationFlusher() {
    requestAnimationFrame(() => this.flushPendingCanvasMutations());
  }
  startRAFTimestamping() {
    const setLatestRAFTimestamp = (timestamp) => {
      this.rafStamps.latestId = timestamp;
      requestAnimationFrame(setLatestRAFTimestamp);
    };
    requestAnimationFrame(setLatestRAFTimestamp);
  }
  flushPendingCanvasMutations() {
    this.pendingCanvasMutations.forEach((_values, canvas) => {
      const id = this.mirror.getId(canvas);
      this.flushPendingCanvasMutationFor(canvas, id);
    });
    requestAnimationFrame(() => this.flushPendingCanvasMutations());
  }
  flushPendingCanvasMutationFor(canvas, id) {
    if (this.frozen || this.locked) return;
    const valuesWithType = this.pendingCanvasMutations.get(canvas);
    if (!valuesWithType || id === -1) return;
    const values = valuesWithType.map((value) => {
      const _a2 = value, { type: type2 } = _a2, rest = __objRest(_a2, ["type"]);
      return rest;
    });
    const { type } = valuesWithType[0];
    this.mutationCb({
      id,
      type,
      commands: values
    });
    this.pendingCanvasMutations.delete(canvas);
  }
};
var StylesheetManager = class {
  constructor(options) {
    __publicField(this, "trackedLinkElements", /* @__PURE__ */ new WeakSet());
    __publicField(this, "mutationCb");
    __publicField(this, "adoptedStyleSheetCb");
    __publicField(this, "styleMirror", new StyleSheetMirror());
    this.mutationCb = options.mutationCb;
    this.adoptedStyleSheetCb = options.adoptedStyleSheetCb;
  }
  attachLinkElement(linkEl, childSn) {
    if ("_cssText" in childSn.attributes) this.mutationCb({
      adds: [],
      removes: [],
      texts: [],
      attributes: [{
        id: childSn.id,
        attributes: childSn.attributes
      }]
    });
    this.trackLinkElement(linkEl);
  }
  trackLinkElement(linkEl) {
    if (this.trackedLinkElements.has(linkEl)) return;
    this.trackedLinkElements.add(linkEl);
    this.trackStylesheetInLinkElement(linkEl);
  }
  adoptStyleSheets(sheets, hostId) {
    if (sheets.length === 0) return;
    const adoptedStyleSheetData = {
      id: hostId,
      styleIds: []
    };
    const styles = [];
    for (const sheet of sheets) {
      let styleId;
      if (!this.styleMirror.has(sheet)) {
        styleId = this.styleMirror.add(sheet);
        styles.push({
          styleId,
          rules: Array.from(sheet.rules || CSSRule, (r2, index) => ({
            rule: stringifyRule(r2, sheet.href),
            index
          }))
        });
      } else styleId = this.styleMirror.getId(sheet);
      adoptedStyleSheetData.styleIds.push(styleId);
    }
    if (styles.length > 0) adoptedStyleSheetData.styles = styles;
    this.adoptedStyleSheetCb(adoptedStyleSheetData);
  }
  reset() {
    this.styleMirror.reset();
    this.trackedLinkElements = /* @__PURE__ */ new WeakSet();
  }
  trackStylesheetInLinkElement(_linkEl) {
  }
};
var ProcessedNodeManager = class {
  constructor() {
    __publicField(this, "nodeMap", /* @__PURE__ */ new WeakMap());
    __publicField(this, "active", false);
  }
  inOtherBuffer(node, thisBuffer) {
    const buffers = this.nodeMap.get(node);
    return buffers && Array.from(buffers).some((buffer) => buffer !== thisBuffer);
  }
  add(node, buffer) {
    if (!this.active) {
      this.active = true;
      requestAnimationFrame(() => {
        this.nodeMap = /* @__PURE__ */ new WeakMap();
        this.active = false;
      });
    }
    this.nodeMap.set(node, (this.nodeMap.get(node) || /* @__PURE__ */ new Set()).add(buffer));
  }
  destroy() {
  }
};
var wrappedEmit;
var takeFullSnapshot$1;
var canvasManager;
var recording = false;
var _a;
try {
  if (Array.from([1], (x) => x * 2)[0] !== 2) {
    const cleanFrame = document.createElement("iframe");
    document.body.appendChild(cleanFrame);
    Array.from = ((_a = cleanFrame.contentWindow) == null ? void 0 : _a.Array.from) || Array.from;
    document.body.removeChild(cleanFrame);
  }
} catch (err) {
  console.debug("Unable to override Array.from", err);
}
var mirror = createMirror$2();
function record(options = {}) {
  const { emit, checkoutEveryNms, checkoutEveryNth, blockClass = "rr-block", blockSelector = null, ignoreClass = "rr-ignore", ignoreSelector = null, maskTextClass = "rr-mask", maskTextSelector = null, inlineStylesheet = true, maskAllInputs, maskInputOptions: _maskInputOptions, slimDOMOptions: _slimDOMOptions, maskInputFn, maskTextFn, hooks, packFn, sampling = {}, dataURLOptions = {}, mousemoveWait, recordDOM = true, recordCanvas = false, recordCrossOriginIframes = false, recordAfter = options.recordAfter === "DOMContentLoaded" ? options.recordAfter : "load", userTriggeredOnInput = false, collectFonts = false, inlineImages = false, plugins, keepIframeSrcFn = () => false, ignoreCSSAttributes = /* @__PURE__ */ new Set([]), errorHandler: errorHandler2 } = options;
  registerErrorHandler(errorHandler2);
  const inEmittingFrame = recordCrossOriginIframes ? window.parent === window : true;
  let passEmitsToParent = false;
  if (!inEmittingFrame) try {
    if (window.parent.document) passEmitsToParent = false;
  } catch (e2) {
    passEmitsToParent = true;
  }
  if (inEmittingFrame && !emit) throw new Error("emit function is required");
  if (!inEmittingFrame && !passEmitsToParent) return () => {
  };
  if (mousemoveWait !== void 0 && sampling.mousemove === void 0) sampling.mousemove = mousemoveWait;
  mirror.reset();
  const maskInputOptions = maskAllInputs === true ? {
    color: true,
    date: true,
    "datetime-local": true,
    email: true,
    month: true,
    number: true,
    range: true,
    search: true,
    tel: true,
    text: true,
    time: true,
    url: true,
    week: true,
    textarea: true,
    select: true,
    password: true
  } : _maskInputOptions !== void 0 ? _maskInputOptions : { password: true };
  const slimDOMOptions = slimDOMDefaults(_slimDOMOptions);
  polyfill$1();
  let lastFullSnapshotEvent;
  let incrementalSnapshotCount = 0;
  const eventProcessor = (e2) => {
    for (const plugin of plugins || []) if (plugin.eventProcessor) e2 = plugin.eventProcessor(e2);
    if (packFn && !passEmitsToParent) e2 = packFn(e2);
    return e2;
  };
  wrappedEmit = (r2, isCheckout) => {
    const e2 = r2;
    e2.timestamp = nowTimestamp();
    if (e2.type !== EventType.FullSnapshot && !(e2.type === EventType.IncrementalSnapshot && e2.data.source === IncrementalSource.Mutation)) {
      canvasManager.unfreeze();
      for (const buf of mutationBuffers) if (buf.isFrozen()) buf.unfreeze();
      else break;
    }
    if (inEmittingFrame) emit == null ? void 0 : emit(eventProcessor(e2), isCheckout);
    else if (passEmitsToParent) {
      const message = {
        type: "rrweb",
        event: eventProcessor(e2),
        origin: window.location.origin,
        isCheckout
      };
      window.parent.postMessage(message, "*");
    }
    if (e2.type === EventType.FullSnapshot) {
      lastFullSnapshotEvent = e2;
      incrementalSnapshotCount = 0;
    } else if (e2.type === EventType.IncrementalSnapshot) {
      if (e2.data.source === IncrementalSource.Mutation && e2.data.isAttachIframe) return;
      incrementalSnapshotCount++;
      const exceedCount = checkoutEveryNth && incrementalSnapshotCount >= checkoutEveryNth;
      const exceedTime = checkoutEveryNms && e2.timestamp - lastFullSnapshotEvent.timestamp > checkoutEveryNms;
      if (exceedCount || exceedTime) takeFullSnapshot$1(true);
    }
  };
  const wrappedMutationEmit = (m) => {
    wrappedEmit({
      type: EventType.IncrementalSnapshot,
      data: __spreadValues({
        source: IncrementalSource.Mutation
      }, m)
    });
  };
  const wrappedScrollEmit = (p) => wrappedEmit({
    type: EventType.IncrementalSnapshot,
    data: __spreadValues({
      source: IncrementalSource.Scroll
    }, p)
  });
  const wrappedCanvasMutationEmit = (p) => wrappedEmit({
    type: EventType.IncrementalSnapshot,
    data: __spreadValues({
      source: IncrementalSource.CanvasMutation
    }, p)
  });
  const wrappedAdoptedStyleSheetEmit = (a2) => wrappedEmit({
    type: EventType.IncrementalSnapshot,
    data: __spreadValues({
      source: IncrementalSource.AdoptedStyleSheet
    }, a2)
  });
  const stylesheetManager = new StylesheetManager({
    mutationCb: wrappedMutationEmit,
    adoptedStyleSheetCb: wrappedAdoptedStyleSheetEmit
  });
  const iframeManager = new IframeManager({
    mirror,
    mutationCb: wrappedMutationEmit,
    stylesheetManager,
    recordCrossOriginIframes,
    wrappedEmit
  });
  for (const plugin of plugins || []) if (plugin.getMirror) plugin.getMirror({
    nodeMirror: mirror,
    crossOriginIframeMirror: iframeManager.crossOriginIframeMirror,
    crossOriginIframeStyleMirror: iframeManager.crossOriginIframeStyleMirror
  });
  const processedNodeManager = new ProcessedNodeManager();
  canvasManager = new CanvasManager({
    recordCanvas,
    mutationCb: wrappedCanvasMutationEmit,
    win: window,
    blockClass,
    blockSelector,
    mirror,
    sampling: sampling.canvas,
    dataURLOptions
  });
  const shadowDomManager = new ShadowDomManager({
    mutationCb: wrappedMutationEmit,
    scrollCb: wrappedScrollEmit,
    bypassOptions: {
      blockClass,
      blockSelector,
      maskTextClass,
      maskTextSelector,
      inlineStylesheet,
      maskInputOptions,
      dataURLOptions,
      maskTextFn,
      maskInputFn,
      recordCanvas,
      inlineImages,
      sampling,
      slimDOMOptions,
      iframeManager,
      stylesheetManager,
      canvasManager,
      keepIframeSrcFn,
      processedNodeManager
    },
    mirror
  });
  takeFullSnapshot$1 = (isCheckout = false) => {
    if (!recordDOM) return;
    wrappedEmit({
      type: EventType.Meta,
      data: {
        href: window.location.href,
        width: getWindowWidth(),
        height: getWindowHeight()
      }
    }, isCheckout);
    stylesheetManager.reset();
    shadowDomManager.init();
    canvasManager.lock();
    mutationBuffers.forEach((buf) => buf.lock());
    const node = snapshot(document, {
      mirror,
      blockClass,
      blockSelector,
      maskTextClass,
      maskTextSelector,
      inlineStylesheet,
      maskAllInputs: maskInputOptions,
      maskTextFn,
      maskInputFn,
      slimDOM: slimDOMOptions,
      dataURLOptions,
      recordCanvas,
      inlineImages,
      onSerialize: (n2) => {
        if (isSerializedIframe(n2, mirror)) iframeManager.addIframe(n2);
        if (isSerializedStylesheet(n2, mirror)) stylesheetManager.trackLinkElement(n2);
        if (hasShadowRoot(n2)) shadowDomManager.addShadowRoot(src_default.shadowRoot(n2), document);
      },
      onIframeLoad: (iframe, childSn) => {
        iframeManager.attachIframe(iframe, childSn);
        shadowDomManager.observeAttachShadow(iframe);
      },
      onStylesheetLoad: (linkEl, childSn) => {
        stylesheetManager.attachLinkElement(linkEl, childSn);
      },
      keepIframeSrcFn
    });
    if (!node) return console.warn("Failed to snapshot the document");
    wrappedEmit({
      type: EventType.FullSnapshot,
      data: {
        node,
        initialOffset: getWindowScroll(window)
      }
    }, isCheckout);
    canvasManager.unlock();
    mutationBuffers.forEach((buf) => buf.unlock());
    if (document.adoptedStyleSheets && document.adoptedStyleSheets.length > 0) stylesheetManager.adoptStyleSheets(document.adoptedStyleSheets, mirror.getId(document));
  };
  try {
    const handlers = [];
    const observe = (doc) => {
      var _a2;
      return callbackWrapper(initObservers)({
        mutationCb: wrappedMutationEmit,
        mousemoveCb: (positions, source) => wrappedEmit({
          type: EventType.IncrementalSnapshot,
          data: {
            source,
            positions
          }
        }),
        mouseInteractionCb: (d) => wrappedEmit({
          type: EventType.IncrementalSnapshot,
          data: __spreadValues({
            source: IncrementalSource.MouseInteraction
          }, d)
        }),
        scrollCb: wrappedScrollEmit,
        viewportResizeCb: (d) => wrappedEmit({
          type: EventType.IncrementalSnapshot,
          data: __spreadValues({
            source: IncrementalSource.ViewportResize
          }, d)
        }),
        inputCb: (v2) => wrappedEmit({
          type: EventType.IncrementalSnapshot,
          data: __spreadValues({
            source: IncrementalSource.Input
          }, v2)
        }),
        mediaInteractionCb: (p) => wrappedEmit({
          type: EventType.IncrementalSnapshot,
          data: __spreadValues({
            source: IncrementalSource.MediaInteraction
          }, p)
        }),
        styleSheetRuleCb: (r2) => wrappedEmit({
          type: EventType.IncrementalSnapshot,
          data: __spreadValues({
            source: IncrementalSource.StyleSheetRule
          }, r2)
        }),
        styleDeclarationCb: (r2) => wrappedEmit({
          type: EventType.IncrementalSnapshot,
          data: __spreadValues({
            source: IncrementalSource.StyleDeclaration
          }, r2)
        }),
        canvasMutationCb: wrappedCanvasMutationEmit,
        fontCb: (p) => wrappedEmit({
          type: EventType.IncrementalSnapshot,
          data: __spreadValues({
            source: IncrementalSource.Font
          }, p)
        }),
        selectionCb: (p) => {
          wrappedEmit({
            type: EventType.IncrementalSnapshot,
            data: __spreadValues({
              source: IncrementalSource.Selection
            }, p)
          });
        },
        customElementCb: (c2) => {
          wrappedEmit({
            type: EventType.IncrementalSnapshot,
            data: __spreadValues({
              source: IncrementalSource.CustomElement
            }, c2)
          });
        },
        blockClass,
        ignoreClass,
        ignoreSelector,
        maskTextClass,
        maskTextSelector,
        maskInputOptions,
        inlineStylesheet,
        sampling,
        recordDOM,
        recordCanvas,
        inlineImages,
        userTriggeredOnInput,
        collectFonts,
        doc,
        maskInputFn,
        maskTextFn,
        keepIframeSrcFn,
        blockSelector,
        slimDOMOptions,
        dataURLOptions,
        mirror,
        iframeManager,
        stylesheetManager,
        shadowDomManager,
        processedNodeManager,
        canvasManager,
        ignoreCSSAttributes,
        plugins: ((_a2 = plugins == null ? void 0 : plugins.filter((p) => p.observer)) == null ? void 0 : _a2.map((p) => ({
          observer: p.observer,
          options: p.options,
          callback: (payload) => wrappedEmit({
            type: EventType.Plugin,
            data: {
              plugin: p.name,
              payload
            }
          })
        }))) || []
      }, hooks);
    };
    const iframeObservers = /* @__PURE__ */ new Map();
    iframeManager.addLoadListener((iframeEl) => {
      try {
        const doc = iframeEl.contentDocument;
        if (iframeObservers.has(doc)) return;
        iframeObservers.set(doc, observe(doc));
      } catch (error) {
        console.warn(error);
      }
    });
    iframeManager.addUnloadListener((doc) => {
      const stopObserving = iframeObservers.get(doc);
      if (!stopObserving) return;
      iframeObservers.delete(doc);
      stopObserving();
      shadowDomManager.removeShadowRootsIn(doc);
      mirror.removeNodeFromMap(doc);
    });
    const init = () => {
      takeFullSnapshot$1();
      handlers.push(observe(document));
      recording = true;
    };
    if (["interactive", "complete"].includes(document.readyState)) init();
    else {
      handlers.push(on("DOMContentLoaded", () => {
        wrappedEmit({
          type: EventType.DomContentLoaded,
          data: {}
        });
        if (recordAfter === "DOMContentLoaded") init();
      }));
      handlers.push(on("load", () => {
        wrappedEmit({
          type: EventType.Load,
          data: {}
        });
        if (recordAfter === "load") init();
      }, window));
    }
    return () => {
      [...handlers, ...iframeObservers.values()].forEach((handler) => {
        try {
          handler();
        } catch (error) {
          if (!String(error).toLowerCase().includes("cross-origin")) console.warn(error);
        }
      });
      iframeObservers.clear();
      iframeManager.destroy();
      processedNodeManager.destroy();
      shadowDomManager.reset();
      canvasManager.reset();
      recording = false;
      unregisterErrorHandler();
    };
  } catch (error) {
    console.warn(error);
  }
}
record.addCustomEvent = (tag, payload) => {
  if (!recording) throw new Error("please add custom event after start recording");
  wrappedEmit({
    type: EventType.Custom,
    data: {
      tag,
      payload
    }
  });
};
record.addAnnotation = (annotation) => {
  if (!recording) throw new Error("please add annotation after recording started");
  wrappedEmit({
    type: EventType.Annotation,
    data: annotation
  });
};
record.freezePage = () => {
  canvasManager.freeze();
  mutationBuffers.forEach((buf) => buf.freeze());
};
record.takeFullSnapshot = (isCheckout) => {
  if (!recording) throw new Error("please take full snapshot after start recording");
  takeFullSnapshot$1(isCheckout);
};
record.mirror = mirror;
var mitt_exports = /* @__PURE__ */ __exportAll({ default: () => mitt_default });
function mitt_default(n2) {
  return {
    all: n2 = n2 || /* @__PURE__ */ new Map(),
    on: function(t2, e2) {
      var i2 = n2.get(t2);
      i2 ? i2.push(e2) : n2.set(t2, [e2]);
    },
    off: function(t2, e2) {
      var i2 = n2.get(t2);
      i2 && (e2 ? i2.splice(i2.indexOf(e2) >>> 0, 1) : n2.set(t2, []));
    },
    emit: function(t2, e2) {
      var i2 = n2.get(t2);
      i2 && i2.slice().map(function(n3) {
        n3(e2);
      }), (i2 = n2.get("*")) && i2.slice().map(function(n3) {
        n3(t2, e2);
      });
    }
  };
}
function polyfill(w = window, d = document) {
  if ("scrollBehavior" in d.documentElement.style && w.__forceSmoothScrollPolyfill__ !== true) return;
  const Element2 = w.HTMLElement || w.Element;
  const SCROLL_TIME = 468;
  const original = {
    scroll: w.scroll || w.scrollTo,
    scrollBy: w.scrollBy,
    elementScroll: Element2.prototype.scroll || scrollElement,
    scrollIntoView: Element2.prototype.scrollIntoView
  };
  const now = w.performance && w.performance.now ? w.performance.now.bind(w.performance) : Date.now;
  function isMicrosoftBrowser(userAgent) {
    return new RegExp([
      "MSIE ",
      "Trident/",
      "Edge/"
    ].join("|")).test(userAgent);
  }
  const ROUNDING_TOLERANCE = isMicrosoftBrowser(w.navigator.userAgent) ? 1 : 0;
  function scrollElement(x, y) {
    this.scrollLeft = x;
    this.scrollTop = y;
  }
  function ease(k) {
    return 0.5 * (1 - Math.cos(Math.PI * k));
  }
  function shouldBailOut(firstArg) {
    if (firstArg === null || typeof firstArg !== "object" || firstArg.behavior === void 0 || firstArg.behavior === "auto" || firstArg.behavior === "instant") return true;
    if (typeof firstArg === "object" && firstArg.behavior === "smooth") return false;
    throw new TypeError("behavior member of ScrollOptions " + firstArg.behavior + " is not a valid value for enumeration ScrollBehavior.");
  }
  function hasScrollableSpace(el, axis) {
    if (axis === "Y") return el.clientHeight + ROUNDING_TOLERANCE < el.scrollHeight;
    if (axis === "X") return el.clientWidth + ROUNDING_TOLERANCE < el.scrollWidth;
  }
  function canOverflow(el, axis) {
    const overflowValue = w.getComputedStyle(el, null)["overflow" + axis];
    return overflowValue === "auto" || overflowValue === "scroll";
  }
  function isScrollable(el) {
    const isScrollableY = hasScrollableSpace(el, "Y") && canOverflow(el, "Y");
    const isScrollableX = hasScrollableSpace(el, "X") && canOverflow(el, "X");
    return isScrollableY || isScrollableX;
  }
  function findScrollableParent(el) {
    while (el !== d.body && isScrollable(el) === false) el = el.parentNode || el.host;
    return el;
  }
  function step(context) {
    const time = now();
    let value;
    let currentX;
    let currentY;
    let elapsed = (time - context.startTime) / SCROLL_TIME;
    elapsed = elapsed > 1 ? 1 : elapsed;
    value = ease(elapsed);
    currentX = context.startX + (context.x - context.startX) * value;
    currentY = context.startY + (context.y - context.startY) * value;
    context.method.call(context.scrollable, currentX, currentY);
    if (currentX !== context.x || currentY !== context.y) w.requestAnimationFrame(step.bind(w, context));
  }
  function smoothScroll(el, x, y) {
    let scrollable;
    let startX;
    let startY;
    let method;
    const startTime = now();
    if (el === d.body) {
      scrollable = w;
      startX = w.scrollX || w.pageXOffset;
      startY = w.scrollY || w.pageYOffset;
      method = original.scroll;
    } else {
      scrollable = el;
      startX = el.scrollLeft;
      startY = el.scrollTop;
      method = scrollElement;
    }
    step({
      scrollable,
      method,
      startTime,
      startX,
      startY,
      x,
      y
    });
  }
  w.scroll = w.scrollTo = function() {
    if (arguments[0] === void 0) return;
    if (shouldBailOut(arguments[0]) === true) {
      original.scroll.call(w, arguments[0].left !== void 0 ? arguments[0].left : typeof arguments[0] !== "object" ? arguments[0] : w.scrollX || w.pageXOffset, arguments[0].top !== void 0 ? arguments[0].top : arguments[1] !== void 0 ? arguments[1] : w.scrollY || w.pageYOffset);
      return;
    }
    smoothScroll.call(w, d.body, arguments[0].left !== void 0 ? ~~arguments[0].left : w.scrollX || w.pageXOffset, arguments[0].top !== void 0 ? ~~arguments[0].top : w.scrollY || w.pageYOffset);
  };
  w.scrollBy = function() {
    if (arguments[0] === void 0) return;
    if (shouldBailOut(arguments[0])) {
      original.scrollBy.call(w, arguments[0].left !== void 0 ? arguments[0].left : typeof arguments[0] !== "object" ? arguments[0] : 0, arguments[0].top !== void 0 ? arguments[0].top : arguments[1] !== void 0 ? arguments[1] : 0);
      return;
    }
    smoothScroll.call(w, d.body, ~~arguments[0].left + (w.scrollX || w.pageXOffset), ~~arguments[0].top + (w.scrollY || w.pageYOffset));
  };
  Element2.prototype.scroll = Element2.prototype.scrollTo = function() {
    if (arguments[0] === void 0) return;
    if (shouldBailOut(arguments[0]) === true) {
      if (typeof arguments[0] === "number" && arguments[1] === void 0) throw new SyntaxError("Value could not be converted");
      original.elementScroll.call(this, arguments[0].left !== void 0 ? ~~arguments[0].left : typeof arguments[0] !== "object" ? ~~arguments[0] : this.scrollLeft, arguments[0].top !== void 0 ? ~~arguments[0].top : arguments[1] !== void 0 ? ~~arguments[1] : this.scrollTop);
      return;
    }
    const left = arguments[0].left;
    const top = arguments[0].top;
    smoothScroll.call(this, this, typeof left === "undefined" ? this.scrollLeft : ~~left, typeof top === "undefined" ? this.scrollTop : ~~top);
  };
  Element2.prototype.scrollBy = function() {
    if (arguments[0] === void 0) return;
    if (shouldBailOut(arguments[0]) === true) {
      original.elementScroll.call(this, arguments[0].left !== void 0 ? ~~arguments[0].left + this.scrollLeft : ~~arguments[0] + this.scrollLeft, arguments[0].top !== void 0 ? ~~arguments[0].top + this.scrollTop : ~~arguments[1] + this.scrollTop);
      return;
    }
    this.scroll({
      left: ~~arguments[0].left + this.scrollLeft,
      top: ~~arguments[0].top + this.scrollTop,
      behavior: arguments[0].behavior
    });
  };
  Element2.prototype.scrollIntoView = function() {
    if (shouldBailOut(arguments[0]) === true) {
      original.scrollIntoView.call(this, arguments[0] === void 0 ? true : arguments[0]);
      return;
    }
    const scrollableParent = findScrollableParent(this);
    const parentRects = scrollableParent.getBoundingClientRect();
    const clientRects = this.getBoundingClientRect();
    if (scrollableParent !== d.body) {
      smoothScroll.call(this, scrollableParent, scrollableParent.scrollLeft + clientRects.left - parentRects.left, scrollableParent.scrollTop + clientRects.top - parentRects.top);
      if (w.getComputedStyle(scrollableParent).position !== "fixed") w.scrollBy({
        left: parentRects.left,
        top: parentRects.top,
        behavior: "smooth"
      });
    } else w.scrollBy({
      left: clientRects.left,
      top: clientRects.top,
      behavior: "smooth"
    });
  };
}
var Timer = class {
  constructor(actions = [], config) {
    __publicField(this, "timeOffset", 0);
    __publicField(this, "speed");
    __publicField(this, "actions");
    __publicField(this, "raf", null);
    __publicField(this, "lastTimestamp");
    this.actions = actions;
    this.speed = config.speed;
  }
  /**
  * Add an action, possibly after the timer starts.
  */
  addAction(action) {
    const rafWasActive = this.raf === true;
    if (!this.actions.length || this.actions[this.actions.length - 1].delay <= action.delay) this.actions.push(action);
    else {
      const index = this.findActionIndex(action);
      this.actions.splice(index, 0, action);
    }
    if (rafWasActive) this.raf = requestAnimationFrame(this.rafCheck.bind(this));
  }
  start() {
    this.timeOffset = 0;
    this.lastTimestamp = performance.now();
    this.raf = requestAnimationFrame(this.rafCheck.bind(this));
  }
  updateLiveTime() {
    if (this.raf === true) this.rafCheck();
  }
  rafCheck() {
    const time = performance.now();
    this.timeOffset += (time - this.lastTimestamp) * this.speed;
    this.lastTimestamp = time;
    while (this.actions.length) {
      const action = this.actions[0];
      if (this.timeOffset >= action.delay) {
        this.actions.shift();
        action.doAction();
      } else break;
    }
    if (this.actions.length > 0) this.raf = requestAnimationFrame(this.rafCheck.bind(this));
    else this.raf = true;
  }
  clear() {
    if (this.raf) {
      if (this.raf !== true) cancelAnimationFrame(this.raf);
      this.raf = null;
    }
    this.actions.length = 0;
  }
  setSpeed(speed) {
    this.speed = speed;
  }
  isActive() {
    return this.raf !== null;
  }
  findActionIndex(action) {
    let start = 0;
    let end = this.actions.length - 1;
    while (start <= end) {
      const mid = Math.floor((start + end) / 2);
      if (this.actions[mid].delay < action.delay) start = mid + 1;
      else if (this.actions[mid].delay > action.delay) end = mid - 1;
      else return mid + 1;
    }
    return start;
  }
};
function addDelay(event, baselineTime) {
  if (event.type === EventType.IncrementalSnapshot && event.data.source === IncrementalSource.MouseMove && event.data.positions && event.data.positions.length) {
    const firstOffset = event.data.positions[0].timeOffset;
    const firstTimestamp = event.timestamp + firstOffset;
    event.delay = firstTimestamp - baselineTime;
    return firstTimestamp - baselineTime;
  }
  event.delay = event.timestamp - baselineTime;
  return event.delay;
}
function t(t2, n2) {
  var e2 = "function" == typeof Symbol && t2[Symbol.iterator];
  if (!e2) return t2;
  var r2, o2, i2 = e2.call(t2), a2 = [];
  try {
    for (; (void 0 === n2 || n2-- > 0) && !(r2 = i2.next()).done; ) a2.push(r2.value);
  } catch (t3) {
    o2 = { error: t3 };
  } finally {
    try {
      r2 && !r2.done && (e2 = i2.return) && e2.call(i2);
    } finally {
      if (o2) throw o2.error;
    }
  }
  return a2;
}
var n;
(function(t2) {
  t2[t2.NotStarted = 0] = "NotStarted", t2[t2.Running = 1] = "Running", t2[t2.Stopped = 2] = "Stopped";
})(n || (n = {}));
var e = { type: "xstate.init" };
function r(t2) {
  return void 0 === t2 ? [] : [].concat(t2);
}
function o(t2) {
  return {
    type: "xstate.assign",
    assignment: t2
  };
}
function i(t2, n2) {
  return "string" == typeof (t2 = "string" == typeof t2 && n2 && n2[t2] ? n2[t2] : t2) ? { type: t2 } : "function" == typeof t2 ? {
    type: t2.name,
    exec: t2
  } : t2;
}
function a(t2) {
  return function(n2) {
    return t2 === n2;
  };
}
function u(t2) {
  return "string" == typeof t2 ? { type: t2 } : t2;
}
function c(t2, n2) {
  return {
    value: t2,
    context: n2,
    actions: [],
    changed: false,
    matches: a(t2)
  };
}
function f(t2, n2, e2) {
  var r2 = n2, o2 = false;
  return [
    t2.filter(function(t3) {
      if ("xstate.assign" === t3.type) {
        o2 = true;
        var n3 = Object.assign({}, r2);
        return "function" == typeof t3.assignment ? n3 = t3.assignment(r2, e2) : Object.keys(t3.assignment).forEach(function(o3) {
          n3[o3] = "function" == typeof t3.assignment[o3] ? t3.assignment[o3](r2, e2) : t3.assignment[o3];
        }), r2 = n3, false;
      }
      return true;
    }),
    r2,
    o2
  ];
}
function s(n2, o2) {
  void 0 === o2 && (o2 = {});
  var s2 = t(f(r(n2.states[n2.initial].entry).map(function(t2) {
    return i(t2, o2.actions);
  }), n2.context, e), 2), l2 = s2[0], v2 = s2[1], y = {
    config: n2,
    _options: o2,
    initialState: {
      value: n2.initial,
      actions: l2,
      context: v2,
      matches: a(n2.initial)
    },
    transition: function(e2, o3) {
      var s3, l3, v3 = "string" == typeof e2 ? {
        value: e2,
        context: n2.context
      } : e2, p = v3.value, g = v3.context, d = u(o3), x = n2.states[p];
      if (x.on) {
        var m = r(x.on[d.type]);
        try {
          for (var h = function(t2) {
            var n3 = "function" == typeof Symbol && Symbol.iterator, e3 = n3 && t2[n3], r2 = 0;
            if (e3) return e3.call(t2);
            if (t2 && "number" == typeof t2.length) return { next: function() {
              return t2 && r2 >= t2.length && (t2 = void 0), {
                value: t2 && t2[r2++],
                done: !t2
              };
            } };
            throw new TypeError(n3 ? "Object is not iterable." : "Symbol.iterator is not defined.");
          }(m), b = h.next(); !b.done; b = h.next()) {
            var S = b.value;
            if (void 0 === S) return c(p, g);
            var w = "string" == typeof S ? { target: S } : S, j = w.target, E = w.actions, R = void 0 === E ? [] : E, N = w.cond, O = void 0 === N ? function() {
              return true;
            } : N, _ = void 0 === j, k = null != j ? j : p, T = n2.states[k];
            if (O(g, d)) {
              var q = t(f((_ ? r(R) : [].concat(x.exit, R, T.entry).filter(function(t2) {
                return t2;
              })).map(function(t2) {
                return i(t2, y._options.actions);
              }), g, d), 3), z = q[0], A = q[1], B = q[2], C = null != j ? j : p;
              return {
                value: C,
                context: A,
                actions: z,
                changed: j !== p || z.length > 0 || B,
                matches: a(C)
              };
            }
          }
        } catch (t2) {
          s3 = { error: t2 };
        } finally {
          try {
            b && !b.done && (l3 = h.return) && l3.call(h);
          } finally {
            if (s3) throw s3.error;
          }
        }
      }
      return c(p, g);
    }
  };
  return y;
}
var l = function(t2, n2) {
  return t2.actions.forEach(function(e2) {
    var r2 = e2.exec;
    return r2 && r2(t2.context, n2);
  });
};
function v(t2) {
  var r2 = t2.initialState, o2 = n.NotStarted, i2 = /* @__PURE__ */ new Set(), c2 = {
    _machine: t2,
    send: function(e2) {
      o2 === n.Running && (r2 = t2.transition(r2, e2), l(r2, u(e2)), i2.forEach(function(t3) {
        return t3(r2);
      }));
    },
    subscribe: function(t3) {
      return i2.add(t3), t3(r2), { unsubscribe: function() {
        return i2.delete(t3);
      } };
    },
    start: function(i3) {
      if (i3) {
        var u2 = "object" == typeof i3 ? i3 : {
          context: t2.config.context,
          value: i3
        };
        r2 = {
          value: u2.value,
          actions: [],
          context: u2.context,
          matches: a(u2.value)
        };
      }
      return o2 = n.Running, l(r2, e), c2;
    },
    stop: function() {
      return o2 = n.Stopped, i2.clear(), c2;
    },
    get state() {
      return r2;
    },
    get status() {
      return o2;
    }
  };
  return c2;
}
function discardPriorSnapshots(events, baselineTime) {
  for (let idx = events.length - 1; idx >= 0; idx--) {
    const event = events[idx];
    if (event.type === EventType.Meta) {
      if (event.timestamp <= baselineTime) return events.slice(idx);
    }
  }
  return events;
}
function createPlayerService(context, { getCastFn, applyEventsSynchronously, emitter }) {
  return v(s({
    id: "player",
    context,
    initial: "paused",
    states: {
      playing: { on: {
        PAUSE: {
          target: "paused",
          actions: ["pause"]
        },
        CAST_EVENT: {
          target: "playing",
          actions: "castEvent"
        },
        END: {
          target: "paused",
          actions: ["resetLastPlayedEvent", "pause"]
        },
        ADD_EVENT: {
          target: "playing",
          actions: ["addEvent"]
        }
      } },
      paused: { on: {
        PLAY: {
          target: "playing",
          actions: ["recordTimeOffset", "play"]
        },
        CAST_EVENT: {
          target: "paused",
          actions: "castEvent"
        },
        TO_LIVE: {
          target: "live",
          actions: ["startLive"]
        },
        ADD_EVENT: {
          target: "paused",
          actions: ["addEvent"]
        }
      } },
      live: { on: {
        ADD_EVENT: {
          target: "live",
          actions: ["addEvent"]
        },
        CAST_EVENT: {
          target: "live",
          actions: ["castEvent"]
        }
      } }
    }
  }, { actions: {
    castEvent: o({ lastPlayedEvent: (ctx, event) => {
      if (event.type === "CAST_EVENT") return event.payload.event;
      return ctx.lastPlayedEvent;
    } }),
    recordTimeOffset: o((ctx, event) => {
      let timeOffset = ctx.timeOffset;
      if ("payload" in event && "timeOffset" in event.payload) timeOffset = event.payload.timeOffset;
      return __spreadProps(__spreadValues({}, ctx), {
        timeOffset,
        baselineTime: ctx.events[0].timestamp + timeOffset
      });
    }),
    play(ctx) {
      var _a2;
      const { timer, events, baselineTime, lastPlayedEvent } = ctx;
      timer.clear();
      for (const event of events) addDelay(event, baselineTime);
      const neededEvents = discardPriorSnapshots(events, baselineTime);
      let lastPlayedTimestamp = lastPlayedEvent == null ? void 0 : lastPlayedEvent.timestamp;
      if ((lastPlayedEvent == null ? void 0 : lastPlayedEvent.type) === EventType.IncrementalSnapshot && lastPlayedEvent.data.source === IncrementalSource.MouseMove) lastPlayedTimestamp = lastPlayedEvent.timestamp + ((_a2 = lastPlayedEvent.data.positions[0]) == null ? void 0 : _a2.timeOffset);
      if (baselineTime < (lastPlayedTimestamp || 0)) emitter.emit(ReplayerEvents.PlayBack);
      const syncEvents = new Array();
      for (const event of neededEvents) {
        if (lastPlayedTimestamp && lastPlayedTimestamp < baselineTime && (event.timestamp <= lastPlayedTimestamp || event === lastPlayedEvent)) continue;
        if (event.timestamp < baselineTime) syncEvents.push(event);
        else {
          const castFn = getCastFn(event, false);
          timer.addAction({
            doAction: () => {
              castFn();
            },
            delay: event.delay
          });
        }
      }
      applyEventsSynchronously(syncEvents);
      emitter.emit(ReplayerEvents.Flush);
      timer.start();
    },
    pause(ctx) {
      ctx.timer.clear();
    },
    resetLastPlayedEvent: o((ctx) => {
      return __spreadProps(__spreadValues({}, ctx), {
        lastPlayedEvent: null
      });
    }),
    startLive: o({ baselineTime: (ctx, event) => {
      ctx.timer.start();
      if (event.type === "TO_LIVE" && event.payload.baselineTime) return event.payload.baselineTime;
      return Date.now();
    } }),
    addEvent: o((ctx, machineEvent) => {
      const { baselineTime, timer, events } = ctx;
      if (machineEvent.type === "ADD_EVENT") {
        const { event } = machineEvent.payload;
        addDelay(event, baselineTime);
        let end = events.length - 1;
        if (!events[end] || events[end].timestamp <= event.timestamp) events.push(event);
        else {
          let insertionIndex = -1;
          let start = 0;
          while (start <= end) {
            const mid = Math.floor((start + end) / 2);
            if (events[mid].timestamp <= event.timestamp) start = mid + 1;
            else end = mid - 1;
          }
          if (insertionIndex === -1) insertionIndex = start;
          events.splice(insertionIndex, 0, event);
        }
        const isSync = event.timestamp < baselineTime;
        const castFn = getCastFn(event, isSync);
        if (isSync) castFn();
        else if (timer.isActive()) timer.addAction({
          doAction: () => {
            castFn();
          },
          delay: event.delay
        });
      }
      return __spreadProps(__spreadValues({}, ctx), {
        events
      });
    })
  } }));
}
function createSpeedService(context) {
  return v(s({
    id: "speed",
    context,
    initial: "normal",
    states: {
      normal: { on: {
        FAST_FORWARD: {
          target: "skipping",
          actions: ["recordSpeed", "setSpeed"]
        },
        SET_SPEED: {
          target: "normal",
          actions: ["setSpeed"]
        }
      } },
      skipping: { on: {
        BACK_TO_NORMAL: {
          target: "normal",
          actions: ["restoreSpeed"]
        },
        SET_SPEED: {
          target: "normal",
          actions: ["setSpeed"]
        }
      } }
    }
  }, { actions: {
    setSpeed: (ctx, event) => {
      if ("payload" in event) ctx.timer.setSpeed(event.payload.speed);
    },
    recordSpeed: o({ normalSpeed: (ctx) => ctx.timer.speed }),
    restoreSpeed: (ctx) => {
      ctx.timer.setSpeed(ctx.normalSpeed);
    }
  } }));
}
var rules = (blockClass) => [`.${blockClass} { background: currentColor }`, "noscript { display: none !important; }"];
var webGLVarMap = /* @__PURE__ */ new Map();
function variableListFor(ctx, ctor) {
  let contextMap = webGLVarMap.get(ctx);
  if (!contextMap) {
    contextMap = /* @__PURE__ */ new Map();
    webGLVarMap.set(ctx, contextMap);
  }
  if (!contextMap.has(ctor)) contextMap.set(ctor, []);
  return contextMap.get(ctor);
}
function deserializeArg(imageMap, ctx, preload) {
  return async (arg) => {
    if (arg && typeof arg === "object" && "rr_type" in arg) {
      if (preload) preload.isUnchanged = false;
      if (arg.rr_type === "ImageBitmap" && "args" in arg) {
        const args = await deserializeArg(imageMap, ctx, preload)(arg.args);
        return await createImageBitmap.apply(null, args);
      } else if ("index" in arg) {
        if (preload || ctx === null) return arg;
        const { rr_type: name, index } = arg;
        return variableListFor(ctx, name)[index];
      } else if ("args" in arg) {
        const { rr_type: name, args } = arg;
        const ctor = window[name];
        return new ctor(...await Promise.all(args.map(deserializeArg(imageMap, ctx, preload))));
      } else if ("base64" in arg) return decode(arg.base64);
      else if ("src" in arg) {
        const image = imageMap.get(arg.src);
        if (image) return image;
        else {
          const image2 = new Image();
          image2.src = arg.src;
          imageMap.set(arg.src, image2);
          return image2;
        }
      } else if ("data" in arg && arg.rr_type === "Blob") {
        const blobContents = await Promise.all(arg.data.map(deserializeArg(imageMap, ctx, preload)));
        return new Blob(blobContents, { type: arg.type });
      }
    } else if (Array.isArray(arg)) return await Promise.all(arg.map(deserializeArg(imageMap, ctx, preload)));
    return arg;
  };
}
function getContext(target, type) {
  try {
    if (type === CanvasContext.WebGL) return target.getContext("webgl") || target.getContext("experimental-webgl");
    return target.getContext("webgl2");
  } catch (e2) {
    return null;
  }
}
var WebGLVariableConstructorsNames = [
  "WebGLActiveInfo",
  "WebGLBuffer",
  "WebGLFramebuffer",
  "WebGLProgram",
  "WebGLRenderbuffer",
  "WebGLShader",
  "WebGLShaderPrecisionFormat",
  "WebGLTexture",
  "WebGLUniformLocation",
  "WebGLVertexArrayObject"
];
function saveToWebGLVarMap(ctx, result) {
  if (!(result == null ? void 0 : result.constructor)) return;
  const { name } = result.constructor;
  if (!WebGLVariableConstructorsNames.includes(name)) return;
  const variables = variableListFor(ctx, name);
  if (!variables.includes(result)) variables.push(result);
}
async function webglMutation({ mutation, target, type, imageMap, errorHandler: errorHandler2 }) {
  try {
    const ctx = getContext(target, type);
    if (!ctx) return;
    if (mutation.setter) {
      ctx[mutation.property] = mutation.args[0];
      return;
    }
    const original = ctx[mutation.property];
    const args = await Promise.all(mutation.args.map(deserializeArg(imageMap, ctx)));
    saveToWebGLVarMap(ctx, original.apply(ctx, args));
  } catch (error) {
    errorHandler2(mutation, error);
  }
}
async function canvasMutation$1({ event, mutations, target, imageMap, errorHandler: errorHandler2 }) {
  const ctx = target.getContext("2d");
  if (!ctx) {
    errorHandler2(mutations[0], /* @__PURE__ */ new Error("Canvas context is null"));
    return;
  }
  const mutationArgsPromises = mutations.map(async (mutation) => {
    return Promise.all(mutation.args.map(deserializeArg(imageMap, ctx)));
  });
  (await Promise.all(mutationArgsPromises)).forEach((args, index) => {
    const mutation = mutations[index];
    try {
      if (mutation.setter) {
        ctx[mutation.property] = mutation.args[0];
        return;
      }
      const original = ctx[mutation.property];
      if (mutation.property === "drawImage" && typeof mutation.args[0] === "string") {
        imageMap.get(event);
        original.apply(ctx, mutation.args);
      } else original.apply(ctx, args);
    } catch (error) {
      errorHandler2(mutation, error);
    }
  });
}
async function canvasMutation({ event, mutation, target, imageMap, canvasEventMap, errorHandler: errorHandler2 }) {
  try {
    const precomputedMutation = canvasEventMap.get(event) || mutation;
    const commands = "commands" in precomputedMutation ? precomputedMutation.commands : [precomputedMutation];
    if ([CanvasContext.WebGL, CanvasContext.WebGL2].includes(mutation.type)) {
      for (let i2 = 0; i2 < commands.length; i2++) {
        const command = commands[i2];
        await webglMutation({
          mutation: command,
          type: mutation.type,
          target,
          imageMap,
          errorHandler: errorHandler2
        });
      }
      return;
    }
    await canvasMutation$1({
      event,
      mutations: commands,
      target,
      imageMap,
      errorHandler: errorHandler2
    });
  } catch (error) {
    errorHandler2(mutation, error);
  }
}
var MediaManager = class {
  constructor(options) {
    __publicField(this, "mediaMap", /* @__PURE__ */ new Map());
    __publicField(this, "warn");
    __publicField(this, "service");
    __publicField(this, "speedService");
    __publicField(this, "emitter");
    __publicField(this, "getCurrentTime");
    __publicField(this, "metadataCallbackMap", /* @__PURE__ */ new Map());
    this.warn = options.warn;
    this.service = options.service;
    this.speedService = options.speedService;
    this.emitter = options.emitter;
    this.getCurrentTime = options.getCurrentTime;
    this.emitter.on(ReplayerEvents.Start, this.start.bind(this));
    this.emitter.on(ReplayerEvents.SkipStart, this.start.bind(this));
    this.emitter.on(ReplayerEvents.Pause, this.pause.bind(this));
    this.emitter.on(ReplayerEvents.Finish, this.pause.bind(this));
    this.speedService.subscribe(() => {
      this.syncAllMediaElements();
    });
  }
  syncAllMediaElements(options = { pause: false }) {
    this.mediaMap.forEach((_mediaState, target) => {
      this.syncTargetWithState(target);
      if (options.pause) try {
        target.pause();
      } catch (error) {
        this.warn(`Failed to sync media element: ${error.message || error}`);
      }
    });
  }
  start() {
    this.syncAllMediaElements();
  }
  pause() {
    this.syncAllMediaElements({ pause: true });
  }
  seekTo({ time, target, mediaState }) {
    if (mediaState.isPlaying) {
      const mediaPlaybackOffset = (time - mediaState.lastInteractionTimeOffset) / 1e3 * mediaState.playbackRate;
      const duration = "duration" in target && target.duration;
      if (Number.isNaN(duration)) {
        this.waitForMetadata(target);
        return;
      }
      let seekToTime = mediaState.currentTimeAtLastInteraction + mediaPlaybackOffset;
      if (target.loop && duration !== false) seekToTime = seekToTime % duration;
      target.currentTime = seekToTime;
    } else {
      try {
        target.pause();
      } catch (error) {
        this.warn(`Failed to pause during seek: ${error.message || error}`);
      }
      target.currentTime = mediaState.currentTimeAtLastInteraction;
    }
  }
  waitForMetadata(target) {
    if (this.metadataCallbackMap.has(target)) return;
    if (!("addEventListener" in target)) return;
    const onLoadedMetadata = () => {
      this.metadataCallbackMap.delete(target);
      const mediaState = this.mediaMap.get(target);
      if (!mediaState) return;
      this.seekTo({
        time: this.getCurrentTime(),
        target,
        mediaState
      });
    };
    this.metadataCallbackMap.set(target, onLoadedMetadata);
    target.addEventListener("loadedmetadata", onLoadedMetadata, { once: true });
  }
  getMediaStateFromMutation({ target, timeOffset, mutation }) {
    var _a2, _b, _c, _d, _e;
    const lastState = this.mediaMap.get(target);
    const { type, playbackRate, currentTime, muted, volume, loop } = mutation;
    return {
      isPlaying: type === MediaInteractions.Play || type !== MediaInteractions.Pause && ((lastState == null ? void 0 : lastState.isPlaying) || target.getAttribute("autoplay") !== null),
      currentTimeAtLastInteraction: (_a2 = currentTime != null ? currentTime : lastState == null ? void 0 : lastState.currentTimeAtLastInteraction) != null ? _a2 : 0,
      lastInteractionTimeOffset: timeOffset,
      playbackRate: (_b = playbackRate != null ? playbackRate : lastState == null ? void 0 : lastState.playbackRate) != null ? _b : 1,
      volume: (_c = volume != null ? volume : lastState == null ? void 0 : lastState.volume) != null ? _c : 1,
      muted: (_d = muted != null ? muted : lastState == null ? void 0 : lastState.muted) != null ? _d : target.getAttribute("muted") === null,
      loop: (_e = loop != null ? loop : lastState == null ? void 0 : lastState.loop) != null ? _e : target.getAttribute("loop") === null
    };
  }
  syncTargetWithState(target) {
    const mediaState = this.mediaMap.get(target);
    if (!mediaState) return;
    const { muted, loop, volume, isPlaying } = mediaState;
    const playerIsPaused = this.service.state.matches("paused");
    const playbackRate = mediaState.playbackRate * this.speedService.state.context.timer.speed;
    try {
      this.seekTo({
        time: this.getCurrentTime(),
        target,
        mediaState
      });
      if (target.volume !== volume) target.volume = volume;
      target.muted = muted;
      target.loop = loop;
      if (target.playbackRate !== playbackRate) target.playbackRate = playbackRate;
      if (isPlaying && !playerIsPaused) target.play();
      else target.pause();
    } catch (error) {
      this.warn(`Failed to replay media interactions: ${error.message || error}`);
    }
  }
  addMediaElements(node, timeOffset, mirror2) {
    if (!["AUDIO", "VIDEO"].includes(node.nodeName)) return;
    const target = node;
    const serializedNode = mirror2.getMeta(target);
    if (!serializedNode || !("attributes" in serializedNode)) return;
    const playerIsPaused = this.service.state.matches("paused");
    const mediaAttributes = serializedNode.attributes;
    let isPlaying = false;
    if (mediaAttributes.rr_mediaState) isPlaying = mediaAttributes.rr_mediaState === "played";
    else isPlaying = target.getAttribute("autoplay") !== null;
    if (isPlaying && playerIsPaused) target.pause();
    let playbackRate = 1;
    if (typeof mediaAttributes.rr_mediaPlaybackRate === "number") playbackRate = mediaAttributes.rr_mediaPlaybackRate;
    let muted = false;
    if (typeof mediaAttributes.rr_mediaMuted === "boolean") muted = mediaAttributes.rr_mediaMuted;
    else muted = target.getAttribute("muted") !== null;
    let loop = false;
    if (typeof mediaAttributes.rr_mediaLoop === "boolean") loop = mediaAttributes.rr_mediaLoop;
    else loop = target.getAttribute("loop") !== null;
    let volume = 1;
    if (typeof mediaAttributes.rr_mediaVolume === "number") volume = mediaAttributes.rr_mediaVolume;
    let currentTimeAtLastInteraction = 0;
    if (typeof mediaAttributes.rr_mediaCurrentTime === "number") currentTimeAtLastInteraction = mediaAttributes.rr_mediaCurrentTime;
    this.mediaMap.set(target, {
      isPlaying,
      currentTimeAtLastInteraction,
      lastInteractionTimeOffset: timeOffset,
      playbackRate,
      volume,
      muted,
      loop
    });
    this.syncTargetWithState(target);
  }
  mediaMutation({ target, timeOffset, mutation }) {
    this.mediaMap.set(target, this.getMediaStateFromMutation({
      target,
      timeOffset,
      mutation
    }));
    this.syncTargetWithState(target);
  }
  isSupportedMediaElement(node) {
    return ["AUDIO", "VIDEO"].includes(node.nodeName);
  }
  reset() {
    this.mediaMap.clear();
  }
};
function applyDialogToTopLevel(node, attributeMutation) {
  if (node.nodeName !== "DIALOG" || node instanceof BaseRRNode) return;
  const dialog = node;
  const oldIsOpen = dialog.open;
  const oldIsModalState = oldIsOpen && dialog.matches("dialog:modal");
  const rrOpenMode = dialog.getAttribute("rr_open_mode");
  const newIsOpen = typeof (attributeMutation == null ? void 0 : attributeMutation.attributes.open) === "string" || typeof dialog.getAttribute("open") === "string";
  const newIsModalState = rrOpenMode === "modal";
  if (oldIsOpen && !(oldIsModalState && rrOpenMode === "non-modal" || !oldIsModalState && newIsModalState)) return;
  if (!dialog.isConnected) {
    console.warn("dialog is not attached to the dom", dialog);
    return;
  }
  if (oldIsOpen) dialog.close();
  if (!newIsOpen) return;
  if (newIsModalState) dialog.showModal();
  else dialog.show();
}
function removeDialogFromTopLevel(node, attributeMutation) {
  if (node.nodeName !== "DIALOG" || node instanceof BaseRRNode) return;
  const dialog = node;
  if (!dialog.isConnected) {
    console.warn("dialog is not attached to the dom", dialog);
    return;
  }
  if (attributeMutation.attributes.open === null) {
    dialog.removeAttribute("open");
    dialog.removeAttribute("rr_open_mode");
  }
}
var SKIP_TIME_INTERVAL = 5e3;
var mitt = mitt_default || mitt_exports;
var REPLAY_CONSOLE_PREFIX = "[replayer]";
var defaultMouseTailConfig = {
  duration: 500,
  lineCap: "round",
  lineWidth: 3,
  strokeStyle: "red"
};
function indicatesTouchDevice(e2) {
  return e2.type == EventType.IncrementalSnapshot && (e2.data.source == IncrementalSource.TouchMove || e2.data.source == IncrementalSource.MouseInteraction && e2.data.type == MouseInteractions.TouchStart);
}
var Replayer = class {
  constructor(events, config) {
    __publicField(this, "wrapper");
    __publicField(this, "iframe");
    __publicField(this, "UNSAFE_replayCanvas", false);
    __publicField(this, "service");
    __publicField(this, "speedService");
    __publicField(this, "config");
    __publicField(this, "usingVirtualDom", false);
    __publicField(this, "virtualDom", new RRDocument());
    __publicField(this, "mouse");
    __publicField(this, "mouseTail", null);
    __publicField(this, "tailPositions", []);
    __publicField(this, "emitter", mitt());
    __publicField(this, "nextUserInteractionEvent");
    __publicField(this, "legacy_missingNodeRetryMap", {});
    __publicField(this, "cache", createCache());
    __publicField(this, "imageMap", /* @__PURE__ */ new Map());
    __publicField(this, "canvasEventMap", /* @__PURE__ */ new Map());
    __publicField(this, "mirror", createMirror$2());
    __publicField(this, "styleMirror", new StyleSheetMirror());
    __publicField(this, "mediaManager");
    __publicField(this, "firstFullSnapshot", null);
    __publicField(this, "newDocumentQueue", []);
    __publicField(this, "mousePos", null);
    __publicField(this, "touchActive", null);
    __publicField(this, "lastMouseDownEvent", null);
    __publicField(this, "lastHoveredRootNode");
    __publicField(this, "lastSelectionData", null);
    __publicField(this, "constructedStyleMutations", []);
    __publicField(this, "adoptedStyleSheets", []);
    __publicField(this, "handleResize", (dimension) => {
      this.iframe.style.display = "inherit";
      for (const el of [this.mouseTail, this.iframe]) {
        if (!el) continue;
        el.setAttribute("width", String(dimension.width));
        el.setAttribute("height", String(dimension.height));
      }
    });
    __publicField(this, "applyEventsSynchronously", (events) => {
      for (const event of events) {
        switch (event.type) {
          case EventType.DomContentLoaded:
          case EventType.Load:
            continue;
          case EventType.FullSnapshot:
          case EventType.Meta:
          case EventType.Plugin:
          case EventType.IncrementalSnapshot:
        }
        this.getCastFn(event, true)();
      }
    });
    __publicField(this, "getCastFn", (event, isSync = false) => {
      let castFn;
      switch (event.type) {
        case EventType.DomContentLoaded:
        case EventType.Load:
          break;
        case EventType.Annotation:
          castFn = () => this.emitter.emit(ReplayerEvents.Annotation, event);
          break;
        case EventType.Custom:
          castFn = () => {
            this.emitter.emit(ReplayerEvents.CustomEvent, event);
          };
          break;
        case EventType.Meta:
          castFn = () => this.emitter.emit(ReplayerEvents.Resize, {
            width: event.data.width,
            height: event.data.height
          });
          break;
        case EventType.FullSnapshot:
          castFn = () => {
            var _a2;
            if (this.firstFullSnapshot) {
              if (this.firstFullSnapshot === event) {
                this.firstFullSnapshot = true;
                return;
              }
            } else this.firstFullSnapshot = true;
            this.mediaManager.reset();
            this.styleMirror.reset();
            this.rebuildFullSnapshot(event, isSync);
            (_a2 = this.iframe.contentWindow) == null ? void 0 : _a2.scrollTo(event.data.initialOffset);
          };
          break;
        case EventType.IncrementalSnapshot:
          castFn = () => {
            this.applyIncremental(event, isSync);
            if (isSync) return;
            if (event === this.nextUserInteractionEvent) {
              this.nextUserInteractionEvent = null;
              this.backToNormal();
            }
            if (this.config.skipInactive && !this.nextUserInteractionEvent) {
              for (const _event of this.service.state.context.events) {
                if (_event.timestamp <= event.timestamp) continue;
                if (this.isUserInteraction(_event)) {
                  if (_event.delay - event.delay > this.config.inactivePeriodThreshold * this.speedService.state.context.timer.speed) this.nextUserInteractionEvent = _event;
                  break;
                }
              }
              if (this.nextUserInteractionEvent) {
                const skipTime = this.nextUserInteractionEvent.delay - event.delay;
                const payload = { speed: Math.min(Math.round(skipTime / SKIP_TIME_INTERVAL), this.config.maxSpeed) };
                this.speedService.send({
                  type: "FAST_FORWARD",
                  payload
                });
                this.emitter.emit(ReplayerEvents.SkipStart, payload);
              }
            }
          };
      }
      const wrappedCastFn = () => {
        if (castFn) castFn();
        for (const plugin of this.config.plugins || []) if (plugin.handler) plugin.handler(event, isSync, { replayer: this });
        this.service.send({
          type: "CAST_EVENT",
          payload: { event }
        });
        const lastIndex = this.service.state.context.events.length - 1;
        if (!this.config.liveMode && event === this.service.state.context.events[lastIndex]) {
          const finish = () => {
            if (lastIndex < this.service.state.context.events.length - 1) return;
            this.backToNormal();
            this.service.send("END");
            this.emitter.emit(ReplayerEvents.Finish);
          };
          let finishBuffer = 50;
          if (event.type === EventType.IncrementalSnapshot && event.data.source === IncrementalSource.MouseMove && event.data.positions.length) finishBuffer += Math.max(0, -event.data.positions[0].timeOffset);
          setTimeout(finish, finishBuffer);
        }
        this.emitter.emit(ReplayerEvents.EventCast, event);
      };
      return wrappedCastFn;
    });
    if (!(config == null ? void 0 : config.liveMode) && events.length < 2) throw new Error("Replayer need at least 2 events.");
    const defaultConfig = {
      speed: 1,
      maxSpeed: 360,
      root: document.body,
      loadTimeout: 0,
      skipInactive: false,
      inactivePeriodThreshold: 1e4,
      showWarning: true,
      showDebug: false,
      blockClass: "rr-block",
      liveMode: false,
      insertStyleRules: [],
      triggerFocus: true,
      UNSAFE_replayCanvas: false,
      pauseAnimation: true,
      mouseTail: defaultMouseTailConfig,
      useVirtualDom: true,
      logger: console
    };
    this.config = Object.assign({}, defaultConfig, config);
    this.handleResize = this.handleResize.bind(this);
    this.getCastFn = this.getCastFn.bind(this);
    this.applyEventsSynchronously = this.applyEventsSynchronously.bind(this);
    this.emitter.on(ReplayerEvents.Resize, this.handleResize);
    this.setupDom();
    for (const plugin of this.config.plugins || []) if (plugin.getMirror) plugin.getMirror({ nodeMirror: this.mirror });
    this.emitter.on(ReplayerEvents.Flush, () => {
      if (this.usingVirtualDom) {
        const replayerHandler = {
          mirror: this.mirror,
          applyCanvas: (canvasEvent, canvasMutationData, target) => {
            canvasMutation({
              event: canvasEvent,
              mutation: canvasMutationData,
              target,
              imageMap: this.imageMap,
              canvasEventMap: this.canvasEventMap,
              errorHandler: this.warnCanvasMutationFailed.bind(this)
            });
          },
          applyInput: this.applyInput.bind(this),
          applyScroll: this.applyScroll.bind(this),
          applyStyleSheetMutation: (data, styleSheet) => {
            if (data.source === IncrementalSource.StyleSheetRule) this.applyStyleSheetRule(data, styleSheet);
            else if (data.source === IncrementalSource.StyleDeclaration) this.applyStyleDeclaration(data, styleSheet);
          },
          afterAppend: (node, id) => {
            for (const plugin of this.config.plugins || []) if (plugin.onBuild) plugin.onBuild(node, {
              id,
              replayer: this
            });
          }
        };
        if (this.iframe.contentDocument) try {
          diff(this.iframe.contentDocument, this.virtualDom, replayerHandler, this.virtualDom.mirror);
        } catch (e2) {
          this.warn(e2);
        }
        this.virtualDom.destroyTree();
        this.usingVirtualDom = false;
        if (Object.keys(this.legacy_missingNodeRetryMap).length) for (const key in this.legacy_missingNodeRetryMap) try {
          const value = this.legacy_missingNodeRetryMap[key];
          const realNode = createOrGetNode(value.node, this.mirror, this.virtualDom.mirror);
          diff(realNode, value.node, replayerHandler, this.virtualDom.mirror);
          value.node = realNode;
        } catch (error) {
          this.warn(error);
        }
        this.constructedStyleMutations.forEach((data) => {
          this.applyStyleSheetMutation(data);
        });
        this.constructedStyleMutations = [];
        this.adoptedStyleSheets.forEach((data) => {
          this.applyAdoptedStyleSheet(data);
        });
        this.adoptedStyleSheets = [];
      }
      if (this.mousePos) {
        this.moveAndHover(this.mousePos.x, this.mousePos.y, this.mousePos.id, true, this.mousePos.debugData);
        this.mousePos = null;
      }
      if (this.touchActive === true) this.mouse.classList.add("touch-active");
      else if (this.touchActive === false) this.mouse.classList.remove("touch-active");
      this.touchActive = null;
      if (this.lastMouseDownEvent) {
        const [target, event] = this.lastMouseDownEvent;
        target.dispatchEvent(event);
      }
      this.lastMouseDownEvent = null;
      if (this.lastSelectionData) {
        this.applySelection(this.lastSelectionData);
        this.lastSelectionData = null;
      }
    });
    this.emitter.on(ReplayerEvents.PlayBack, () => {
      this.firstFullSnapshot = null;
      this.mirror.reset();
      this.styleMirror.reset();
      this.mediaManager.reset();
    });
    const timer = new Timer([], { speed: this.config.speed });
    this.service = createPlayerService({
      events: events.map((e2) => {
        if (config && config.unpackFn) return config.unpackFn(e2);
        return e2;
      }).sort((a1, a2) => a1.timestamp - a2.timestamp),
      timer,
      timeOffset: 0,
      baselineTime: 0,
      lastPlayedEvent: null
    }, {
      getCastFn: this.getCastFn,
      applyEventsSynchronously: this.applyEventsSynchronously,
      emitter: this.emitter
    });
    this.service.start();
    this.service.subscribe((state) => {
      this.emitter.emit(ReplayerEvents.StateChange, { player: state });
    });
    this.speedService = createSpeedService({
      normalSpeed: -1,
      timer
    });
    this.speedService.start();
    this.speedService.subscribe((state) => {
      this.emitter.emit(ReplayerEvents.StateChange, { speed: state });
    });
    this.mediaManager = new MediaManager({
      warn: this.warn.bind(this),
      service: this.service,
      speedService: this.speedService,
      emitter: this.emitter,
      getCurrentTime: this.getCurrentTime.bind(this)
    });
    const firstMeta = this.service.state.context.events.find((e2) => e2.type === EventType.Meta);
    const firstFullsnapshot = this.service.state.context.events.find((e2) => e2.type === EventType.FullSnapshot);
    if (firstMeta) {
      const { width, height } = firstMeta.data;
      setTimeout(() => {
        this.emitter.emit(ReplayerEvents.Resize, {
          width,
          height
        });
      }, 0);
    }
    if (firstFullsnapshot) setTimeout(() => {
      var _a2;
      if (this.firstFullSnapshot) return;
      this.firstFullSnapshot = firstFullsnapshot;
      this.rebuildFullSnapshot(firstFullsnapshot);
      (_a2 = this.iframe.contentWindow) == null ? void 0 : _a2.scrollTo(firstFullsnapshot.data.initialOffset);
    }, 1);
    if (this.service.state.context.events.find(indicatesTouchDevice)) this.mouse.classList.add("touch-device");
  }
  get timer() {
    return this.service.state.context.timer;
  }
  on(event, handler) {
    this.emitter.on(event, handler);
    return this;
  }
  off(event, handler) {
    this.emitter.off(event, handler);
    return this;
  }
  setConfig(config) {
    Object.keys(config).forEach((key) => {
      config[key];
      this.config[key] = config[key];
    });
    if (!this.config.skipInactive) this.backToNormal();
    if (typeof config.speed !== "undefined") this.speedService.send({
      type: "SET_SPEED",
      payload: { speed: config.speed }
    });
    if (typeof config.mouseTail !== "undefined") {
      if (config.mouseTail === false) {
        if (this.mouseTail) this.mouseTail.style.display = "none";
      } else {
        if (!this.mouseTail) {
          this.mouseTail = document.createElement("canvas");
          this.mouseTail.width = Number.parseFloat(this.iframe.width);
          this.mouseTail.height = Number.parseFloat(this.iframe.height);
          this.mouseTail.classList.add("replayer-mouse-tail");
          this.wrapper.insertBefore(this.mouseTail, this.iframe);
        }
        this.mouseTail.style.display = "inherit";
      }
    }
  }
  getMetaData() {
    const firstEvent = this.service.state.context.events[0];
    const lastEvent = this.service.state.context.events[this.service.state.context.events.length - 1];
    return {
      startTime: firstEvent.timestamp,
      endTime: lastEvent.timestamp,
      totalTime: lastEvent.timestamp - firstEvent.timestamp
    };
  }
  /**
  * Get the actual time offset the player is at now compared to the first event.
  */
  getCurrentTime() {
    if (this.config.liveMode) this.timer.updateLiveTime();
    return this.timer.timeOffset + this.getTimeOffset();
  }
  /**
  * Get the time offset the player is at now compared to the first event, but without regard for the timer.
  */
  getTimeOffset() {
    const { baselineTime, events } = this.service.state.context;
    return baselineTime - events[0].timestamp;
  }
  getMirror() {
    return this.mirror;
  }
  /**
  * This API was designed to be used as play at any time offset.
  * Since we minimized the data collected from recorder, we do not
  * have the ability of undo an event.
  * So the implementation of play at any time offset will always iterate
  * all of the events, cast event before the offset synchronously
  * and cast event after the offset asynchronously with timer.
  * @param timeOffset - number
  */
  play(timeOffset = 0) {
    var _a2, _b;
    if (this.service.state.matches("paused")) this.service.send({
      type: "PLAY",
      payload: { timeOffset }
    });
    else {
      this.service.send({ type: "PAUSE" });
      this.service.send({
        type: "PLAY",
        payload: { timeOffset }
      });
    }
    (_b = (_a2 = this.iframe.contentDocument) == null ? void 0 : _a2.getElementsByTagName("html")[0]) == null ? void 0 : _b.classList.remove("rrweb-paused");
    this.emitter.emit(ReplayerEvents.Start);
  }
  pause(timeOffset) {
    var _a2, _b;
    if (timeOffset === void 0 && this.service.state.matches("playing")) this.service.send({ type: "PAUSE" });
    if (typeof timeOffset === "number") {
      this.play(timeOffset);
      this.service.send({ type: "PAUSE" });
    }
    (_b = (_a2 = this.iframe.contentDocument) == null ? void 0 : _a2.getElementsByTagName("html")[0]) == null ? void 0 : _b.classList.add("rrweb-paused");
    this.emitter.emit(ReplayerEvents.Pause);
  }
  resume(timeOffset = 0) {
    this.warn(`The 'resume' was deprecated in 1.0. Please use 'play' method which has the same interface.`);
    this.play(timeOffset);
    this.emitter.emit(ReplayerEvents.Resume);
  }
  /**
  * Totally destroy this replayer and please be careful that this operation is irreversible.
  * Memory occupation can be released by removing all references to this replayer.
  */
  destroy() {
    this.pause();
    this.mirror.reset();
    this.styleMirror.reset();
    this.mediaManager.reset();
    this.config.root.removeChild(this.wrapper);
    this.emitter.emit(ReplayerEvents.Destroy);
  }
  startLive(baselineTime) {
    this.service.send({
      type: "TO_LIVE",
      payload: { baselineTime }
    });
  }
  addEvent(rawEvent) {
    const event = this.config.unpackFn ? this.config.unpackFn(rawEvent) : rawEvent;
    if (indicatesTouchDevice(event)) this.mouse.classList.add("touch-device");
    Promise.resolve().then(() => this.service.send({
      type: "ADD_EVENT",
      payload: { event }
    }));
  }
  enableInteract() {
    this.iframe.setAttribute("scrolling", "auto");
    this.iframe.style.pointerEvents = "auto";
  }
  disableInteract() {
    this.iframe.setAttribute("scrolling", "no");
    this.iframe.style.pointerEvents = "none";
  }
  /**
  * Empties the replayer's cache and reclaims memory.
  * The replayer will use this cache to speed up the playback.
  */
  resetCache() {
    this.cache = createCache();
  }
  setupDom() {
    this.wrapper = document.createElement("div");
    this.wrapper.classList.add("replayer-wrapper");
    this.config.root.appendChild(this.wrapper);
    this.mouse = document.createElement("div");
    this.mouse.classList.add("replayer-mouse");
    this.wrapper.appendChild(this.mouse);
    if (this.config.mouseTail !== false) {
      this.mouseTail = document.createElement("canvas");
      this.mouseTail.classList.add("replayer-mouse-tail");
      this.mouseTail.style.display = "inherit";
      this.wrapper.appendChild(this.mouseTail);
    }
    if (this.config.UNSAFE_replayCanvas) {
      this.iframe = document.createElement("iframe");
      this.iframe.setAttribute("sandbox", "allow-same-origin allow-scripts");
      this.wrapper.appendChild(this.iframe);
      this.UNSAFE_replayCanvas = true;
    } else {
      this.iframe = createSandboxedIframe({ root: this.wrapper });
      this.UNSAFE_replayCanvas = false;
    }
    this.iframe.style.display = "none";
    this.disableInteract();
    if (this.iframe.contentWindow && this.iframe.contentDocument) {
      polyfill(this.iframe.contentWindow, this.iframe.contentDocument);
      polyfill$1(this.iframe.contentWindow);
    }
  }
  rebuildFullSnapshot(event, isSync = false) {
    if (!this.iframe.contentDocument) return this.warn("Looks like your replayer has been destroyed.");
    if (Object.keys(this.legacy_missingNodeRetryMap).length) this.warn("Found unresolved missing node map", this.legacy_missingNodeRetryMap);
    this.legacy_missingNodeRetryMap = {};
    const collectedIframes = [];
    const collectedDialogs = /* @__PURE__ */ new Set();
    const afterAppend = (builtNode, id) => {
      if (builtNode.nodeName === "DIALOG") collectedDialogs.add(builtNode);
      this.collectIframeAndAttachDocument(collectedIframes, builtNode);
      if (this.mediaManager.isSupportedMediaElement(builtNode)) {
        const { events } = this.service.state.context;
        this.mediaManager.addMediaElements(builtNode, event.timestamp - events[0].timestamp, this.mirror);
      }
      for (const plugin of this.config.plugins || []) if (plugin.onBuild) plugin.onBuild(builtNode, {
        id,
        replayer: this
      });
    };
    if (this.usingVirtualDom) {
      this.virtualDom.destroyTree();
      this.usingVirtualDom = false;
    }
    this.mirror.reset();
    rebuild(event.data.node, {
      doc: this.iframe.contentDocument,
      afterAppend,
      cache: this.cache,
      mirror: this.mirror,
      UNSAFE_allowUnprotectedRebuild: this.UNSAFE_replayCanvas
    });
    afterAppend(this.iframe.contentDocument, event.data.node.id);
    for (const { mutationInQueue, builtNode } of collectedIframes) {
      this.attachDocumentToIframe(mutationInQueue, builtNode);
      this.newDocumentQueue = this.newDocumentQueue.filter((m) => m !== mutationInQueue);
    }
    const { documentElement, head } = this.iframe.contentDocument;
    this.insertStyleRules(documentElement, head);
    collectedDialogs.forEach((d) => applyDialogToTopLevel(d));
    if (!this.service.state.matches("playing")) this.iframe.contentDocument.getElementsByTagName("html")[0].classList.add("rrweb-paused");
    this.emitter.emit(ReplayerEvents.FullsnapshotRebuilded, event);
    if (!isSync) this.waitForStylesheetLoad();
    if (this.config.UNSAFE_replayCanvas) this.preloadAllImages();
  }
  insertStyleRules(documentElement, head) {
    var _a2;
    const injectStylesRules = rules(this.config.blockClass).concat(this.config.insertStyleRules);
    if (this.config.pauseAnimation) injectStylesRules.push("html.rrweb-paused *, html.rrweb-paused *:before, html.rrweb-paused *:after { animation-play-state: paused !important; }");
    if (!injectStylesRules.length) return;
    if (this.usingVirtualDom) {
      const styleEl = this.virtualDom.createElement("style");
      this.virtualDom.mirror.add(styleEl, getDefaultSN(styleEl, this.virtualDom.unserializedId));
      documentElement.insertBefore(styleEl, head);
      styleEl.rules.push({
        source: IncrementalSource.StyleSheetRule,
        adds: injectStylesRules.map((cssText, index) => ({
          rule: cssText,
          index
        }))
      });
    } else {
      const styleEl = document.createElement("style");
      documentElement.insertBefore(styleEl, head);
      for (let idx = 0; idx < injectStylesRules.length; idx++) (_a2 = styleEl.sheet) == null ? void 0 : _a2.insertRule(injectStylesRules[idx], idx);
    }
  }
  attachDocumentToIframe(mutation, iframeEl) {
    const mirror2 = this.usingVirtualDom ? this.virtualDom.mirror : this.mirror;
    const collectedIframes = [];
    const collectedDialogs = /* @__PURE__ */ new Set();
    const afterAppend = (builtNode, id) => {
      if (builtNode.nodeName === "DIALOG") collectedDialogs.add(builtNode);
      this.collectIframeAndAttachDocument(collectedIframes, builtNode);
      const sn = mirror2.getMeta(builtNode);
      if ((sn == null ? void 0 : sn.type) === NodeType.Element && (sn == null ? void 0 : sn.tagName.toUpperCase()) === "HTML") {
        const { documentElement, head } = iframeEl.contentDocument;
        this.insertStyleRules(documentElement, head);
      }
      if (this.usingVirtualDom) return;
      for (const plugin of this.config.plugins || []) if (plugin.onBuild) plugin.onBuild(builtNode, {
        id,
        replayer: this
      });
    };
    buildNodeWithSN(mutation.node, {
      doc: iframeEl.contentDocument,
      mirror: mirror2,
      hackCss: true,
      skipChild: false,
      afterAppend,
      cache: this.cache
    });
    afterAppend(iframeEl.contentDocument, mutation.node.id);
    for (const { mutationInQueue, builtNode } of collectedIframes) {
      this.attachDocumentToIframe(mutationInQueue, builtNode);
      this.newDocumentQueue = this.newDocumentQueue.filter((m) => m !== mutationInQueue);
    }
    collectedDialogs.forEach((d) => applyDialogToTopLevel(d));
  }
  collectIframeAndAttachDocument(collected, builtNode) {
    if (isSerializedIframe(builtNode, this.mirror)) {
      const mutationInQueue = this.newDocumentQueue.find((m) => m.parentId === this.mirror.getId(builtNode));
      if (mutationInQueue) collected.push({
        mutationInQueue,
        builtNode
      });
    }
  }
  /**
  * pause when loading style sheet, resume when loaded all timeout exceed
  */
  waitForStylesheetLoad() {
    var _a2;
    const head = (_a2 = this.iframe.contentDocument) == null ? void 0 : _a2.head;
    if (head) {
      const unloadSheets = /* @__PURE__ */ new Set();
      let timer;
      let beforeLoadState = this.service.state;
      const stateHandler = () => {
        beforeLoadState = this.service.state;
      };
      this.emitter.on(ReplayerEvents.Start, stateHandler);
      this.emitter.on(ReplayerEvents.Pause, stateHandler);
      const unsubscribe = () => {
        this.emitter.off(ReplayerEvents.Start, stateHandler);
        this.emitter.off(ReplayerEvents.Pause, stateHandler);
      };
      head.querySelectorAll('link[rel="stylesheet"]').forEach((css) => {
        if (!css.sheet) {
          unloadSheets.add(css);
          css.addEventListener("load", () => {
            unloadSheets.delete(css);
            if (unloadSheets.size === 0 && timer !== -1) {
              if (beforeLoadState.matches("playing")) this.play(this.getCurrentTime());
              this.emitter.emit(ReplayerEvents.LoadStylesheetEnd);
              if (timer) clearTimeout(timer);
              unsubscribe();
            }
          });
        }
      });
      if (unloadSheets.size > 0) {
        this.service.send({ type: "PAUSE" });
        this.emitter.emit(ReplayerEvents.LoadStylesheetStart);
        timer = setTimeout(() => {
          if (beforeLoadState.matches("playing")) this.play(this.getCurrentTime());
          timer = -1;
          unsubscribe();
        }, this.config.loadTimeout);
      }
    }
  }
  /**
  * pause when there are some canvas drawImage args need to be loaded
  */
  async preloadAllImages() {
    const promises = [];
    for (const event of this.service.state.context.events) if (event.type === EventType.IncrementalSnapshot && event.data.source === IncrementalSource.CanvasMutation) {
      promises.push(this.deserializeAndPreloadCanvasEvents(event.data, event));
      ("commands" in event.data ? event.data.commands : [event.data]).forEach((c2) => {
        this.preloadImages(c2, event);
      });
    }
    return Promise.all(promises);
  }
  preloadImages(data, event) {
    if (data.property === "drawImage" && typeof data.args[0] === "string" && !this.imageMap.has(event)) {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");
      const imgd = ctx == null ? void 0 : ctx.createImageData(canvas.width, canvas.height);
      ctx == null ? void 0 : ctx.putImageData(imgd, 0, 0);
    }
  }
  async deserializeAndPreloadCanvasEvents(data, event) {
    if (!this.canvasEventMap.has(event)) {
      const status = { isUnchanged: true };
      if ("commands" in data) {
        const commands = await Promise.all(data.commands.map(async (c2) => {
          const args = await Promise.all(c2.args.map(deserializeArg(this.imageMap, null, status)));
          return __spreadProps(__spreadValues({}, c2), {
            args
          });
        }));
        if (status.isUnchanged === false) this.canvasEventMap.set(event, __spreadProps(__spreadValues({}, data), {
          commands
        }));
      } else {
        const args = await Promise.all(data.args.map(deserializeArg(this.imageMap, null, status)));
        if (status.isUnchanged === false) this.canvasEventMap.set(event, __spreadProps(__spreadValues({}, data), {
          args
        }));
      }
    }
  }
  applyIncremental(e2, isSync) {
    var _a2, _b, _c;
    const { data: d } = e2;
    switch (d.source) {
      case IncrementalSource.Mutation:
        try {
          this.applyMutation(d, isSync);
        } catch (error) {
          this.warn(`Exception in mutation ${error.message || error}`, d);
        }
        break;
      case IncrementalSource.Drag:
      case IncrementalSource.TouchMove:
      case IncrementalSource.MouseMove:
        if (isSync) {
          const lastPosition = d.positions[d.positions.length - 1];
          this.mousePos = {
            x: lastPosition.x,
            y: lastPosition.y,
            id: lastPosition.id,
            debugData: d
          };
        } else {
          d.positions.forEach((p) => {
            const action = {
              doAction: () => {
                this.moveAndHover(p.x, p.y, p.id, isSync, d);
              },
              delay: p.timeOffset + e2.timestamp - this.service.state.context.baselineTime
            };
            this.timer.addAction(action);
          });
          this.timer.addAction({
            doAction() {
            },
            delay: e2.delay - ((_a2 = d.positions[0]) == null ? void 0 : _a2.timeOffset)
          });
        }
        break;
      case IncrementalSource.MouseInteraction: {
        if (d.id === -1) break;
        const event = new Event(toLowerCase(MouseInteractions[d.type]));
        const target = this.mirror.getNode(d.id);
        if (!target) return this.debugNodeNotFound(d, d.id);
        this.emitter.emit(ReplayerEvents.MouseInteraction, {
          type: d.type,
          target
        });
        const { triggerFocus } = this.config;
        switch (d.type) {
          case MouseInteractions.Blur:
            if ("blur" in target) target.blur();
            break;
          case MouseInteractions.Focus:
            if (triggerFocus && target.focus) target.focus({ preventScroll: true });
            break;
          case MouseInteractions.Click:
          case MouseInteractions.TouchStart:
          case MouseInteractions.TouchEnd:
          case MouseInteractions.MouseDown:
          case MouseInteractions.MouseUp:
            if (isSync) {
              if (d.type === MouseInteractions.TouchStart) this.touchActive = true;
              else if (d.type === MouseInteractions.TouchEnd) this.touchActive = false;
              if (d.type === MouseInteractions.MouseDown) this.lastMouseDownEvent = [target, event];
              else if (d.type === MouseInteractions.MouseUp) this.lastMouseDownEvent = null;
              this.mousePos = {
                x: d.x || 0,
                y: d.y || 0,
                id: d.id,
                debugData: d
              };
            } else {
              if (d.type === MouseInteractions.TouchStart) this.tailPositions.length = 0;
              this.moveAndHover(d.x || 0, d.y || 0, d.id, isSync, d);
              if (d.type === MouseInteractions.Click) {
                this.mouse.classList.remove("active");
                this.mouse.offsetWidth;
                this.mouse.classList.add("active");
              } else if (d.type === MouseInteractions.TouchStart) {
                this.mouse.offsetWidth;
                this.mouse.classList.add("touch-active");
              } else if (d.type === MouseInteractions.TouchEnd) this.mouse.classList.remove("touch-active");
              else target.dispatchEvent(event);
            }
            break;
          case MouseInteractions.TouchCancel:
            if (isSync) this.touchActive = false;
            else this.mouse.classList.remove("touch-active");
            break;
          default:
            target.dispatchEvent(event);
        }
        break;
      }
      case IncrementalSource.Scroll:
        if (d.id === -1) break;
        if (this.usingVirtualDom) {
          const target = this.virtualDom.mirror.getNode(d.id);
          if (!target) return this.debugNodeNotFound(d, d.id);
          target.scrollData = d;
          break;
        }
        this.applyScroll(d, isSync);
        break;
      case IncrementalSource.ViewportResize:
        this.emitter.emit(ReplayerEvents.Resize, {
          width: d.width,
          height: d.height
        });
        break;
      case IncrementalSource.Input:
        if (d.id === -1) break;
        if (this.usingVirtualDom) {
          const target = this.virtualDom.mirror.getNode(d.id);
          if (!target) return this.debugNodeNotFound(d, d.id);
          target.inputData = d;
          break;
        }
        this.applyInput(d);
        break;
      case IncrementalSource.MediaInteraction: {
        const target = this.usingVirtualDom ? this.virtualDom.mirror.getNode(d.id) : this.mirror.getNode(d.id);
        if (!target) return this.debugNodeNotFound(d, d.id);
        const mediaEl = target;
        const { events } = this.service.state.context;
        this.mediaManager.mediaMutation({
          target: mediaEl,
          timeOffset: e2.timestamp - events[0].timestamp,
          mutation: d
        });
        break;
      }
      case IncrementalSource.StyleSheetRule:
      case IncrementalSource.StyleDeclaration:
        if (this.usingVirtualDom) {
          if (d.styleId) this.constructedStyleMutations.push(d);
          else if (d.id) (_b = this.virtualDom.mirror.getNode(d.id)) == null ? void 0 : _b.rules.push(d);
        } else this.applyStyleSheetMutation(d);
        break;
      case IncrementalSource.CanvasMutation:
        if (!this.config.UNSAFE_replayCanvas) return;
        if (this.usingVirtualDom) {
          const target = this.virtualDom.mirror.getNode(d.id);
          if (!target) return this.debugNodeNotFound(d, d.id);
          target.canvasMutations.push({
            event: e2,
            mutation: d
          });
        } else {
          const target = this.mirror.getNode(d.id);
          if (!target) return this.debugNodeNotFound(d, d.id);
          canvasMutation({
            event: e2,
            mutation: d,
            target,
            imageMap: this.imageMap,
            canvasEventMap: this.canvasEventMap,
            errorHandler: this.warnCanvasMutationFailed.bind(this)
          });
        }
        break;
      case IncrementalSource.Font:
        try {
          const fontFace = new FontFace(d.family, d.buffer ? new Uint8Array(JSON.parse(d.fontSource)) : d.fontSource, d.descriptors);
          (_c = this.iframe.contentDocument) == null ? void 0 : _c.fonts.add(fontFace);
        } catch (error) {
          this.warn(error);
        }
        break;
      case IncrementalSource.Selection:
        if (isSync) {
          this.lastSelectionData = d;
          break;
        }
        this.applySelection(d);
        break;
      case IncrementalSource.AdoptedStyleSheet:
        if (this.usingVirtualDom) this.adoptedStyleSheets.push(d);
        else this.applyAdoptedStyleSheet(d);
    }
  }
  /**
  * Apply the mutation to the virtual dom or the real dom.
  * @param d - The mutation data.
  * @param isSync - Whether the mutation should be applied synchronously (while fast-forwarding).
  */
  applyMutation(d, isSync) {
    if (this.config.useVirtualDom && !this.usingVirtualDom && isSync) {
      this.usingVirtualDom = true;
      buildFromDom(this.iframe.contentDocument, this.mirror, this.virtualDom);
      if (Object.keys(this.legacy_missingNodeRetryMap).length) for (const key in this.legacy_missingNodeRetryMap) try {
        const value = this.legacy_missingNodeRetryMap[key];
        const virtualNode = buildFromNode(value.node, this.virtualDom, this.mirror);
        if (virtualNode) value.node = virtualNode;
      } catch (error) {
        this.warn(error);
      }
    }
    const mirror2 = this.usingVirtualDom ? this.virtualDom.mirror : this.mirror;
    d.removes = d.removes.filter((mutation) => {
      if (!mirror2.getNode(mutation.id)) {
        this.warnNodeNotFound(d, mutation.id);
        return false;
      }
      return true;
    });
    d.removes.forEach((mutation) => {
      var _a2;
      const target = mirror2.getNode(mutation.id);
      if (!target) return;
      let parent = mirror2.getNode(mutation.parentId);
      if (!parent) return this.warnNodeNotFound(d, mutation.parentId);
      if (mutation.isShadow && hasShadowRoot(parent)) parent = parent.shadowRoot;
      mirror2.removeNodeFromMap(target);
      if (parent) try {
        parent.removeChild(target);
        if (this.usingVirtualDom && target.nodeName === "#text" && parent.nodeName === "STYLE" && ((_a2 = parent.rules) == null ? void 0 : _a2.length) > 0) parent.rules = [];
      } catch (error) {
        if (error instanceof DOMException) this.warn("parent could not remove child in mutation", parent, target, d);
        else throw error;
      }
    });
    const legacy_missingNodeMap = __spreadValues({}, this.legacy_missingNodeRetryMap);
    const legacy_queue = [];
    const nextNotInDOM = (mutation) => {
      let next = null;
      if (mutation.nextId) next = mirror2.getNode(mutation.nextId);
      if (mutation.nextId !== null && mutation.nextId !== void 0 && mutation.nextId !== -1 && !next) return true;
      return false;
    };
    const appendNode = (mutation) => {
      var _a2, _b;
      if (!this.iframe.contentDocument) return this.warn("Looks like your replayer has been destroyed.");
      let parent = mirror2.getNode(mutation.parentId);
      if (!parent) {
        if (mutation.node.type === NodeType.Document) return this.newDocumentQueue.push(mutation);
        return legacy_queue.push(mutation);
      }
      if (mutation.node.isShadow) {
        if (!hasShadowRoot(parent)) {
          parent.attachShadow({ mode: "open" });
          parent = parent.shadowRoot;
        } else parent = parent.shadowRoot;
      }
      let previous = null;
      let next = null;
      if (mutation.previousId) previous = mirror2.getNode(mutation.previousId);
      if (mutation.nextId) next = mirror2.getNode(mutation.nextId);
      if (nextNotInDOM(mutation)) return legacy_queue.push(mutation);
      if (mutation.node.rootId && !mirror2.getNode(mutation.node.rootId)) return;
      const targetDoc = mutation.node.rootId ? mirror2.getNode(mutation.node.rootId) : this.usingVirtualDom ? this.virtualDom : this.iframe.contentDocument;
      if (isSerializedIframe(parent, mirror2)) {
        this.attachDocumentToIframe(mutation, parent);
        return;
      }
      const afterAppend = (node, id) => {
        if (this.usingVirtualDom) return;
        applyDialogToTopLevel(node);
        for (const plugin of this.config.plugins || []) if (plugin.onBuild) plugin.onBuild(node, {
          id,
          replayer: this
        });
      };
      const target = buildNodeWithSN(mutation.node, {
        doc: targetDoc,
        mirror: mirror2,
        skipChild: true,
        hackCss: true,
        cache: this.cache,
        /**
        * caveat: `afterAppend` only gets called on child nodes of target
        * we have to call it again below when this target was added to the DOM
        */
        afterAppend
      });
      if (mutation.previousId === -1 || mutation.nextId === -1) {
        legacy_missingNodeMap[mutation.node.id] = {
          node: target,
          mutation
        };
        return;
      }
      const parentSn = mirror2.getMeta(parent);
      if (parentSn && parentSn.type === NodeType.Element && mutation.node.type === NodeType.Text) {
        const prospectiveSiblings = Array.isArray(parent.childNodes) ? parent.childNodes : Array.from(parent.childNodes);
        if (parentSn.tagName === "textarea") {
          for (const c2 of prospectiveSiblings) if (c2.nodeType === parent.TEXT_NODE) parent.removeChild(c2);
        } else if (parentSn.tagName === "style" && prospectiveSiblings.length === 1) {
          for (const cssText of prospectiveSiblings) if (cssText.nodeType === parent.TEXT_NODE && !mirror2.hasNode(cssText)) {
            target.textContent = cssText.textContent;
            parent.removeChild(cssText);
          }
        }
      } else if ((parentSn == null ? void 0 : parentSn.type) === NodeType.Document) {
        const parentDoc = parent;
        if (mutation.node.type === NodeType.DocumentType && ((_a2 = parentDoc.childNodes[0]) == null ? void 0 : _a2.nodeType) === Node.DOCUMENT_TYPE_NODE) parentDoc.removeChild(parentDoc.childNodes[0]);
        if (target.nodeName === "HTML" && parentDoc.documentElement) parentDoc.removeChild(parentDoc.documentElement);
      }
      if (previous && previous.nextSibling && previous.nextSibling.parentNode) parent.insertBefore(target, previous.nextSibling);
      else if (next && next.parentNode) parent.contains(next) ? parent.insertBefore(target, next) : parent.insertBefore(target, null);
      else parent.appendChild(target);
      afterAppend(target, mutation.node.id);
      if (this.usingVirtualDom && target.nodeName === "#text" && parent.nodeName === "STYLE" && ((_b = parent.rules) == null ? void 0 : _b.length) > 0) parent.rules = [];
      if (isSerializedIframe(target, this.mirror)) {
        const targetId = this.mirror.getId(target);
        const mutationInQueue = this.newDocumentQueue.find((m) => m.parentId === targetId);
        if (mutationInQueue) {
          this.attachDocumentToIframe(mutationInQueue, target);
          this.newDocumentQueue = this.newDocumentQueue.filter((m) => m !== mutationInQueue);
        }
      }
      if (mutation.previousId || mutation.nextId) this.legacy_resolveMissingNode(legacy_missingNodeMap, parent, target, mutation);
    };
    d.adds.forEach((mutation) => {
      appendNode(mutation);
    });
    const startTime = Date.now();
    while (legacy_queue.length) {
      const resolveTrees = queueToResolveTrees(legacy_queue);
      legacy_queue.length = 0;
      if (Date.now() - startTime > 500) {
        this.warn("Timeout in the loop, please check the resolve tree data:", resolveTrees);
        break;
      }
      for (const tree of resolveTrees) if (!mirror2.getNode(tree.value.parentId)) this.debug("Drop resolve tree since there is no parent for the root node.", tree);
      else iterateResolveTree(tree, (mutation) => {
        appendNode(mutation);
      });
    }
    if (Object.keys(legacy_missingNodeMap).length) Object.assign(this.legacy_missingNodeRetryMap, legacy_missingNodeMap);
    uniqueTextMutations(d.texts).forEach((mutation) => {
      var _a2;
      const target = mirror2.getNode(mutation.id);
      if (!target) {
        if (d.removes.find((r2) => r2.id === mutation.id)) return;
        return this.warnNodeNotFound(d, mutation.id);
      }
      const parentEl = target.parentElement;
      if (mutation.value && parentEl && parentEl.tagName === "STYLE") target.textContent = adaptCssForReplay(mutation.value, this.cache);
      else target.textContent = mutation.value;
      if (this.usingVirtualDom) {
        const parent = target.parentNode;
        if (((_a2 = parent == null ? void 0 : parent.rules) == null ? void 0 : _a2.length) > 0) parent.rules = [];
      }
    });
    d.attributes.forEach((mutation) => {
      var _a2;
      const target = mirror2.getNode(mutation.id);
      if (!target) {
        if (d.removes.find((r2) => r2.id === mutation.id)) return;
        return this.warnNodeNotFound(d, mutation.id);
      }
      for (const attributeName in mutation.attributes) if (typeof attributeName === "string") {
        const value = mutation.attributes[attributeName];
        if (value === null) {
          target.removeAttribute(attributeName);
          if (attributeName === "open") removeDialogFromTopLevel(target, mutation);
        } else if (typeof value === "string") try {
          if (attributeName === "_cssText" && (target.nodeName === "LINK" || target.nodeName === "STYLE")) try {
            const newSn = mirror2.getMeta(target);
            const newNode = buildNodeWithSN(__spreadProps(__spreadValues({}, newSn), {
              attributes: __spreadValues(__spreadValues({}, newSn.attributes), mutation.attributes)
            }), {
              doc: target.ownerDocument,
              mirror: mirror2,
              skipChild: true,
              hackCss: true,
              cache: this.cache
            });
            Object.assign(newSn.attributes, mutation.attributes);
            const siblingNode = target.nextSibling;
            const parentNode2 = target.parentNode;
            if (newNode && parentNode2) {
              parentNode2.removeChild(target);
              parentNode2.insertBefore(newNode, siblingNode);
              mirror2.replace(mutation.id, newNode);
              break;
            }
          } catch (e2) {
          }
          if (attributeName === "value" && target.nodeName === "TEXTAREA") {
            const textarea = target;
            textarea.childNodes.forEach((c2) => textarea.removeChild(c2));
            const tn = (_a2 = target.ownerDocument) == null ? void 0 : _a2.createTextNode(value);
            if (tn) textarea.appendChild(tn);
          } else target.setAttribute(attributeName, value);
          if (attributeName === "rr_open_mode" && target.nodeName === "DIALOG") applyDialogToTopLevel(target, mutation);
        } catch (error) {
          this.warn("An error occurred may due to the checkout feature.", error);
        }
        else if (attributeName === "style") {
          const styleValues = value;
          const targetEl = target;
          for (const s2 in styleValues) if (styleValues[s2] === false) targetEl.style.removeProperty(s2);
          else if (styleValues[s2] instanceof Array) {
            const svp = styleValues[s2];
            targetEl.style.setProperty(s2, svp[0], svp[1]);
          } else {
            const svs = styleValues[s2];
            targetEl.style.setProperty(s2, svs);
          }
        }
      }
    });
  }
  /**
  * Apply the scroll data on real elements.
  * If the replayer is in sync mode, smooth scroll behavior should be disabled.
  * @param d - the scroll data
  * @param isSync - whether the replayer is in sync mode(fast-forward)
  */
  applyScroll(d, isSync) {
    var _a2, _b;
    const target = this.mirror.getNode(d.id);
    if (!target) return this.debugNodeNotFound(d, d.id);
    const sn = this.mirror.getMeta(target);
    if (target === this.iframe.contentDocument) (_a2 = this.iframe.contentWindow) == null ? void 0 : _a2.scrollTo({
      top: d.y,
      left: d.x,
      behavior: isSync ? "auto" : "smooth"
    });
    else if ((sn == null ? void 0 : sn.type) === NodeType.Document) (_b = target.defaultView) == null ? void 0 : _b.scrollTo({
      top: d.y,
      left: d.x,
      behavior: isSync ? "auto" : "smooth"
    });
    else try {
      target.scrollTo({
        top: d.y,
        left: d.x,
        behavior: isSync ? "auto" : "smooth"
      });
    } catch (error) {
    }
  }
  applyInput(d) {
    const target = this.mirror.getNode(d.id);
    if (!target) return this.debugNodeNotFound(d, d.id);
    try {
      target.checked = d.isChecked;
      target.value = d.text;
    } catch (error) {
    }
  }
  applySelection(d) {
    try {
      const selectionSet = /* @__PURE__ */ new Set();
      const ranges = d.ranges.map(({ start, startOffset, end, endOffset }) => {
        var _a2;
        const startContainer = this.mirror.getNode(start);
        const endContainer = this.mirror.getNode(end);
        if (!startContainer || !endContainer) return;
        const result = new Range();
        result.setStart(startContainer, startOffset);
        result.setEnd(endContainer, endOffset);
        const selection = (_a2 = startContainer.ownerDocument) == null ? void 0 : _a2.getSelection();
        selection && selectionSet.add(selection);
        return {
          range: result,
          selection
        };
      });
      selectionSet.forEach((s2) => s2.removeAllRanges());
      ranges.forEach((r2) => {
        var _a2;
        return r2 && ((_a2 = r2.selection) == null ? void 0 : _a2.addRange(r2.range));
      });
    } catch (error) {
    }
  }
  applyStyleSheetMutation(data) {
    var _a2;
    let styleSheet = null;
    if (data.styleId) styleSheet = this.styleMirror.getStyle(data.styleId);
    else if (data.id) styleSheet = ((_a2 = this.mirror.getNode(data.id)) == null ? void 0 : _a2.sheet) || null;
    if (!styleSheet) return;
    if (data.source === IncrementalSource.StyleSheetRule) this.applyStyleSheetRule(data, styleSheet);
    else if (data.source === IncrementalSource.StyleDeclaration) this.applyStyleDeclaration(data, styleSheet);
  }
  applyStyleSheetRule(data, styleSheet) {
    var _a2, _b, _c, _d;
    (_a2 = data.adds) == null ? void 0 : _a2.forEach(({ rule, index: nestedIndex }) => {
      var _a3;
      try {
        if (Array.isArray(nestedIndex)) {
          const { positions, index } = getPositionsAndIndex(nestedIndex);
          (_a3 = getNestedRule(styleSheet.cssRules, positions)) == null ? void 0 : _a3.insertRule(rule, index);
        } else {
          const index = nestedIndex === void 0 ? void 0 : Math.min(nestedIndex, styleSheet.cssRules.length);
          styleSheet == null ? void 0 : styleSheet.insertRule(rule, index);
        }
      } catch (e2) {
      }
    });
    (_b = data.removes) == null ? void 0 : _b.forEach(({ index: nestedIndex }) => {
      var _a3;
      try {
        if (Array.isArray(nestedIndex)) {
          const { positions, index } = getPositionsAndIndex(nestedIndex);
          (_a3 = getNestedRule(styleSheet.cssRules, positions)) == null ? void 0 : _a3.deleteRule(index || 0);
        } else styleSheet == null ? void 0 : styleSheet.deleteRule(nestedIndex);
      } catch (e2) {
      }
    });
    if (typeof data.replace === "string") try {
      (_c = styleSheet.replace) == null ? void 0 : _c.call(styleSheet, data.replace);
    } catch (e2) {
    }
    if (typeof data.replaceSync === "string") try {
      (_d = styleSheet.replaceSync) == null ? void 0 : _d.call(styleSheet, data.replaceSync);
    } catch (e2) {
    }
  }
  /**
  * Apply a StyleDeclaration event (setProperty/removeProperty) to a stylesheet.
  *
  * Uses defensive null checks because the rule may not exist:
  * - Timing issues: The rule was added by a previous StyleSheetRule event
  *   that hasn't been processed yet
  * - Dynamic stylesheets: Constructed stylesheets or adopted stylesheets
  *   may not be fully synchronized
  * - Nested rules: Rules inside @media/@supports require the parent rule
  *   to exist first
  */
  applyStyleDeclaration(data, styleSheet) {
    if (data.set) {
      const rule = getNestedRule(styleSheet.rules, data.index);
      if (rule == null ? void 0 : rule.style) rule.style.setProperty(data.set.property, data.set.value, data.set.priority);
    }
    if (data.remove) {
      const rule = getNestedRule(styleSheet.rules, data.index);
      if (rule == null ? void 0 : rule.style) rule.style.removeProperty(data.remove.property);
    }
  }
  applyAdoptedStyleSheet(data) {
    var _a2;
    const targetHost = this.mirror.getNode(data.id);
    if (!targetHost) return;
    (_a2 = data.styles) == null ? void 0 : _a2.forEach((style) => {
      var _a3;
      let newStyleSheet = null;
      let hostWindow = null;
      if (hasShadowRoot(targetHost)) hostWindow = ((_a3 = targetHost.ownerDocument) == null ? void 0 : _a3.defaultView) || null;
      else if (targetHost.nodeName === "#document") hostWindow = targetHost.defaultView;
      if (!hostWindow) return;
      try {
        newStyleSheet = new hostWindow.CSSStyleSheet();
        this.styleMirror.add(newStyleSheet, style.styleId);
        this.applyStyleSheetRule({
          source: IncrementalSource.StyleSheetRule,
          adds: style.rules
        }, newStyleSheet);
      } catch (e2) {
      }
    });
    const MAX_RETRY_TIME = 10;
    let count = 0;
    const adoptStyleSheets = (targetHost2, styleIds) => {
      const stylesToAdopt = styleIds.map((styleId) => this.styleMirror.getStyle(styleId)).filter((style) => style !== null);
      if (hasShadowRoot(targetHost2)) targetHost2.shadowRoot.adoptedStyleSheets = stylesToAdopt;
      else if (targetHost2.nodeName === "#document") targetHost2.adoptedStyleSheets = stylesToAdopt;
      if (stylesToAdopt.length !== styleIds.length && count < MAX_RETRY_TIME) {
        setTimeout(() => adoptStyleSheets(targetHost2, styleIds), 0 + 100 * count);
        count++;
      }
    };
    adoptStyleSheets(targetHost, data.styleIds);
  }
  legacy_resolveMissingNode(map, parent, target, targetMutation) {
    const { previousId, nextId } = targetMutation;
    const previousInMap = previousId && map[previousId];
    const nextInMap = nextId && map[nextId];
    if (previousInMap) {
      const { node, mutation } = previousInMap;
      parent.insertBefore(node, target);
      delete map[mutation.node.id];
      delete this.legacy_missingNodeRetryMap[mutation.node.id];
      if (mutation.previousId || mutation.nextId) this.legacy_resolveMissingNode(map, parent, node, mutation);
    }
    if (nextInMap) {
      const { node, mutation } = nextInMap;
      parent.insertBefore(node, target.nextSibling);
      delete map[mutation.node.id];
      delete this.legacy_missingNodeRetryMap[mutation.node.id];
      if (mutation.previousId || mutation.nextId) this.legacy_resolveMissingNode(map, parent, node, mutation);
    }
  }
  moveAndHover(x, y, id, isSync, debugData) {
    const target = this.mirror.getNode(id);
    if (!target) return this.debugNodeNotFound(debugData, id);
    const base = getBaseDimension(target, this.iframe);
    const _x = x * base.absoluteScale + base.x;
    const _y = y * base.absoluteScale + base.y;
    this.mouse.style.left = `${_x}px`;
    this.mouse.style.top = `${_y}px`;
    if (!isSync) this.drawMouseTail({
      x: _x,
      y: _y
    });
    this.hoverElements(target);
  }
  drawMouseTail(position) {
    if (!this.mouseTail) return;
    const { lineCap, lineWidth, strokeStyle, duration } = this.config.mouseTail === true ? defaultMouseTailConfig : Object.assign({}, defaultMouseTailConfig, this.config.mouseTail);
    const draw = () => {
      if (!this.mouseTail) return;
      const ctx = this.mouseTail.getContext("2d");
      if (!ctx || !this.tailPositions.length) return;
      ctx.clearRect(0, 0, this.mouseTail.width, this.mouseTail.height);
      ctx.beginPath();
      ctx.lineWidth = lineWidth;
      ctx.lineCap = lineCap;
      ctx.strokeStyle = strokeStyle;
      ctx.moveTo(this.tailPositions[0].x, this.tailPositions[0].y);
      this.tailPositions.forEach((p) => ctx.lineTo(p.x, p.y));
      ctx.stroke();
    };
    this.tailPositions.push(position);
    draw();
    setTimeout(() => {
      this.tailPositions = this.tailPositions.filter((p) => p !== position);
      draw();
    }, duration / this.speedService.state.context.timer.speed);
  }
  hoverElements(el) {
    var _a2;
    (_a2 = this.lastHoveredRootNode || this.iframe.contentDocument) == null ? void 0 : _a2.querySelectorAll(".\\:hover").forEach((hoveredEl) => {
      hoveredEl.classList.remove(":hover");
    });
    this.lastHoveredRootNode = el.getRootNode();
    let currentEl = el;
    while (currentEl) {
      if (currentEl.classList) currentEl.classList.add(":hover");
      currentEl = currentEl.parentElement;
    }
  }
  isUserInteraction(event) {
    if (event.type !== EventType.IncrementalSnapshot) return false;
    return event.data.source > IncrementalSource.Mutation && event.data.source <= IncrementalSource.Input;
  }
  backToNormal() {
    this.nextUserInteractionEvent = null;
    if (this.speedService.state.matches("normal")) return;
    this.speedService.send({ type: "BACK_TO_NORMAL" });
    this.emitter.emit(ReplayerEvents.SkipEnd, { speed: this.speedService.state.context.normalSpeed });
  }
  warnNodeNotFound(d, id) {
    this.warn(`Node with id '${id}' not found. `, d);
  }
  warnCanvasMutationFailed(d, error) {
    this.warn(`Has error on canvas update`, error, "canvas mutation:", d);
  }
  debugNodeNotFound(d, id) {
    this.debug(`Node with id '${id}' not found. `, d);
  }
  warn(...args) {
    if (!this.config.showWarning) return;
    this.config.logger.warn(REPLAY_CONSOLE_PREFIX, ...args);
  }
  debug(...args) {
    if (!this.config.showDebug) return;
    this.config.logger.log(REPLAY_CONSOLE_PREFIX, ...args);
  }
};
var { addCustomEvent, addAnnotation } = record;
var { freezePage } = record;
var { takeFullSnapshot } = record;
exports.EventType = EventType;
exports.IncrementalSource = IncrementalSource;
exports.MouseInteractions = MouseInteractions;
exports.Replayer = Replayer;
exports.ReplayerEvents = ReplayerEvents;
exports.addAnnotation = addAnnotation;
exports.addCustomEvent = addCustomEvent;
exports.canvasMutation = canvasMutation;
exports.freezePage = freezePage;
Object.defineProperty(exports, "mirror", {
  enumerable: true,
  get: function() {
    return _mirror;
  }
});
exports.record = record;
exports.takeFullSnapshot = takeFullSnapshot;
Object.defineProperty(exports, "utils", {
  enumerable: true,
  get: function() {
    return utils_exports;
  }
});
/*! *****************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */
if (typeof module.exports == "object" && typeof exports == "object") {
  var __cp = (to, from, except, desc) => {
    if ((from && typeof from === "object") || typeof from === "function") {
      for (let key of Object.getOwnPropertyNames(from)) {
        if (!Object.prototype.hasOwnProperty.call(to, key) && key !== except)
        Object.defineProperty(to, key, {
          get: () => from[key],
          enumerable: !(desc = Object.getOwnPropertyDescriptor(from, key)) || desc.enumerable,
        });
      }
    }
    return to;
  };
  module.exports = __cp(module.exports, exports);
}
return module.exports;
}))
//# sourceMappingURL=rrweb.umd.cjs.map
