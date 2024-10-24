/**
CSS BROWSER DETECTOR 1.0.3
By Rodolfo Berrios <inbox@rodolfoberrios.com>

Highly inspired on the work of Rafael Lima http://rafael.adm.br/css_browser_selector/
Powered by this Quirks's guide http://www.quirksmode.org/js/detect.html

--

Usage: Simply include it on your HTML and once loaded, it will add something like "chrome chrome20 windows"
to the HTML html tag (document). So you can switch CSS styles using anidation like this:

.ie7 { *display: inline; }

It also blinds helpers like is_firefox() to detect firefox, or is_chrome(20) to detect if the browser is chrome 20.
You can also use something like if(is_ie() && get_browser_version() >= 9) to detect IE9 and above.

get_browser() returns the browser name
get_broser_version() returns the browser version
get_browser_os() return the operating system

**/
var BrowserDetect = {
	init: function(){
		this.browser = this.searchString(this.dataBrowser);
		this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion);
		this.shortversion = this.browser+this.version;
		this.OS = this.searchString(this.dataOS);
	},

	searchString: function(data){
		for (var i=0; i < data.length; i++)
		{
			var dataString = data[i].string;
			this.versionSearchString = data[i].subString;

			if(dataString.indexOf(data[i].subString) != -1){
				return data[i].identity;
			}
		}
	},

	searchVersion: function(dataString){
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},

	dataBrowser: [
		{
			string: navigator.userAgent,
			subString: "Chrome",
			identity: "chrome"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "ie"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "firefox"
		},
		{
			string: navigator.userAgent,
			subString: "Safari",
			identity: "safari"
		},
		{
			string: navigator.userAgent,
			subString: "Opera",
			identity: "opera"
		}
	],

	dataOS: [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "osx"
		},
		{
			string: navigator.userAgent,
			subString: "iPhone",
			identity: "ios"
		},
		{
			string: navigator.userAgent,
			subString: "iPad",
			identity: "ios"
		},
		{
			string: navigator.userAgent,
			subString: "iPod",
			identity: "ios"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "linux"
		}
	]

};
BrowserDetect.init();

document.documentElement.className += " " + BrowserDetect.browser + " " + BrowserDetect.shortversion + " " + BrowserDetect.OS;

function is_browser(agent, version){
	if(agent == BrowserDetect.browser){
		return typeof version !== "undefined" ? version == BrowserDetect.version : true;
	} else {
		return false;
	}
}

function get_browser(){
	return BrowserDetect.browser;
}

function get_browser_version(){
	return BrowserDetect.version;
}

function get_browser_os(){
	return BrowserDetect.OS;
}

// Generate is_browser() functions
for(var i=0; i<BrowserDetect.dataBrowser.length; i++){
	eval('function is_'+BrowserDetect.dataBrowser[i].identity+'(version) { return is_browser("'+BrowserDetect.dataBrowser[i].identity+'", version); }');
}
// Generate is_os() functions
for(var i=0; i<BrowserDetect.dataOS.length; i++){
	eval('function is_'+BrowserDetect.dataOS[i].identity+'() { return "'+BrowserDetect.dataOS[i].identity+'" == "'+BrowserDetect.OS+'"; }');
}

/*! jQuery v1.12.4 | (c) jQuery Foundation | jquery.org/license */
!function(a,b){"object"==typeof module&&"object"==typeof module.exports?module.exports=a.document?b(a,!0):function(a){if(!a.document)throw new Error("jQuery requires a window with a document");return b(a)}:b(a)}("undefined"!=typeof window?window:this,function(a,b){var c=[],d=a.document,e=c.slice,f=c.concat,g=c.push,h=c.indexOf,i={},j=i.toString,k=i.hasOwnProperty,l={},m="1.12.4",n=function(a,b){return new n.fn.init(a,b)},o=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,p=/^-ms-/,q=/-([\da-z])/gi,r=function(a,b){return b.toUpperCase()};n.fn=n.prototype={jquery:m,constructor:n,selector:"",length:0,toArray:function(){return e.call(this)},get:function(a){return null!=a?0>a?this[a+this.length]:this[a]:e.call(this)},pushStack:function(a){var b=n.merge(this.constructor(),a);return b.prevObject=this,b.context=this.context,b},each:function(a){return n.each(this,a)},map:function(a){return this.pushStack(n.map(this,function(b,c){return a.call(b,c,b)}))},slice:function(){return this.pushStack(e.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},eq:function(a){var b=this.length,c=+a+(0>a?b:0);return this.pushStack(c>=0&&b>c?[this[c]]:[])},end:function(){return this.prevObject||this.constructor()},push:g,sort:c.sort,splice:c.splice},n.extend=n.fn.extend=function(){var a,b,c,d,e,f,g=arguments[0]||{},h=1,i=arguments.length,j=!1;for("boolean"==typeof g&&(j=g,g=arguments[h]||{},h++),"object"==typeof g||n.isFunction(g)||(g={}),h===i&&(g=this,h--);i>h;h++)if(null!=(e=arguments[h]))for(d in e)a=g[d],c=e[d],g!==c&&(j&&c&&(n.isPlainObject(c)||(b=n.isArray(c)))?(b?(b=!1,f=a&&n.isArray(a)?a:[]):f=a&&n.isPlainObject(a)?a:{},g[d]=n.extend(j,f,c)):void 0!==c&&(g[d]=c));return g},n.extend({expando:"jQuery"+(m+Math.random()).replace(/\D/g,""),isReady:!0,error:function(a){throw new Error(a)},noop:function(){},isFunction:function(a){return"function"===n.type(a)},isArray:Array.isArray||function(a){return"array"===n.type(a)},isWindow:function(a){return null!=a&&a==a.window},isNumeric:function(a){var b=a&&a.toString();return!n.isArray(a)&&b-parseFloat(b)+1>=0},isEmptyObject:function(a){var b;for(b in a)return!1;return!0},isPlainObject:function(a){var b;if(!a||"object"!==n.type(a)||a.nodeType||n.isWindow(a))return!1;try{if(a.constructor&&!k.call(a,"constructor")&&!k.call(a.constructor.prototype,"isPrototypeOf"))return!1}catch(c){return!1}if(!l.ownFirst)for(b in a)return k.call(a,b);for(b in a);return void 0===b||k.call(a,b)},type:function(a){return null==a?a+"":"object"==typeof a||"function"==typeof a?i[j.call(a)]||"object":typeof a},globalEval:function(b){b&&n.trim(b)&&(a.execScript||function(b){a.eval.call(a,b)})(b)},camelCase:function(a){return a.replace(p,"ms-").replace(q,r)},nodeName:function(a,b){return a.nodeName&&a.nodeName.toLowerCase()===b.toLowerCase()},each:function(a,b){var c,d=0;if(s(a)){for(c=a.length;c>d;d++)if(b.call(a[d],d,a[d])===!1)break}else for(d in a)if(b.call(a[d],d,a[d])===!1)break;return a},trim:function(a){return null==a?"":(a+"").replace(o,"")},makeArray:function(a,b){var c=b||[];return null!=a&&(s(Object(a))?n.merge(c,"string"==typeof a?[a]:a):g.call(c,a)),c},inArray:function(a,b,c){var d;if(b){if(h)return h.call(b,a,c);for(d=b.length,c=c?0>c?Math.max(0,d+c):c:0;d>c;c++)if(c in b&&b[c]===a)return c}return-1},merge:function(a,b){var c=+b.length,d=0,e=a.length;while(c>d)a[e++]=b[d++];if(c!==c)while(void 0!==b[d])a[e++]=b[d++];return a.length=e,a},grep:function(a,b,c){for(var d,e=[],f=0,g=a.length,h=!c;g>f;f++)d=!b(a[f],f),d!==h&&e.push(a[f]);return e},map:function(a,b,c){var d,e,g=0,h=[];if(s(a))for(d=a.length;d>g;g++)e=b(a[g],g,c),null!=e&&h.push(e);else for(g in a)e=b(a[g],g,c),null!=e&&h.push(e);return f.apply([],h)},guid:1,proxy:function(a,b){var c,d,f;return"string"==typeof b&&(f=a[b],b=a,a=f),n.isFunction(a)?(c=e.call(arguments,2),d=function(){return a.apply(b||this,c.concat(e.call(arguments)))},d.guid=a.guid=a.guid||n.guid++,d):void 0},now:function(){return+new Date},support:l}),"function"==typeof Symbol&&(n.fn[Symbol.iterator]=c[Symbol.iterator]),n.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(a,b){i["[object "+b+"]"]=b.toLowerCase()});function s(a){var b=!!a&&"length"in a&&a.length,c=n.type(a);return"function"===c||n.isWindow(a)?!1:"array"===c||0===b||"number"==typeof b&&b>0&&b-1 in a}var t=function(a){var b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u="sizzle"+1*new Date,v=a.document,w=0,x=0,y=ga(),z=ga(),A=ga(),B=function(a,b){return a===b&&(l=!0),0},C=1<<31,D={}.hasOwnProperty,E=[],F=E.pop,G=E.push,H=E.push,I=E.slice,J=function(a,b){for(var c=0,d=a.length;d>c;c++)if(a[c]===b)return c;return-1},K="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",L="[\\x20\\t\\r\\n\\f]",M="(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",N="\\["+L+"*("+M+")(?:"+L+"*([*^$|!~]?=)"+L+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+M+"))|)"+L+"*\\]",O=":("+M+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+N+")*)|.*)\\)|)",P=new RegExp(L+"+","g"),Q=new RegExp("^"+L+"+|((?:^|[^\\\\])(?:\\\\.)*)"+L+"+$","g"),R=new RegExp("^"+L+"*,"+L+"*"),S=new RegExp("^"+L+"*([>+~]|"+L+")"+L+"*"),T=new RegExp("="+L+"*([^\\]'\"]*?)"+L+"*\\]","g"),U=new RegExp(O),V=new RegExp("^"+M+"$"),W={ID:new RegExp("^#("+M+")"),CLASS:new RegExp("^\\.("+M+")"),TAG:new RegExp("^("+M+"|[*])"),ATTR:new RegExp("^"+N),PSEUDO:new RegExp("^"+O),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+L+"*(even|odd|(([+-]|)(\\d*)n|)"+L+"*(?:([+-]|)"+L+"*(\\d+)|))"+L+"*\\)|)","i"),bool:new RegExp("^(?:"+K+")$","i"),needsContext:new RegExp("^"+L+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+L+"*((?:-\\d)?\\d*)"+L+"*\\)|)(?=[^-]|$)","i")},X=/^(?:input|select|textarea|button)$/i,Y=/^h\d$/i,Z=/^[^{]+\{\s*\[native \w/,$=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,_=/[+~]/,aa=/'|\\/g,ba=new RegExp("\\\\([\\da-f]{1,6}"+L+"?|("+L+")|.)","ig"),ca=function(a,b,c){var d="0x"+b-65536;return d!==d||c?b:0>d?String.fromCharCode(d+65536):String.fromCharCode(d>>10|55296,1023&d|56320)},da=function(){m()};try{H.apply(E=I.call(v.childNodes),v.childNodes),E[v.childNodes.length].nodeType}catch(ea){H={apply:E.length?function(a,b){G.apply(a,I.call(b))}:function(a,b){var c=a.length,d=0;while(a[c++]=b[d++]);a.length=c-1}}}function fa(a,b,d,e){var f,h,j,k,l,o,r,s,w=b&&b.ownerDocument,x=b?b.nodeType:9;if(d=d||[],"string"!=typeof a||!a||1!==x&&9!==x&&11!==x)return d;if(!e&&((b?b.ownerDocument||b:v)!==n&&m(b),b=b||n,p)){if(11!==x&&(o=$.exec(a)))if(f=o[1]){if(9===x){if(!(j=b.getElementById(f)))return d;if(j.id===f)return d.push(j),d}else if(w&&(j=w.getElementById(f))&&t(b,j)&&j.id===f)return d.push(j),d}else{if(o[2])return H.apply(d,b.getElementsByTagName(a)),d;if((f=o[3])&&c.getElementsByClassName&&b.getElementsByClassName)return H.apply(d,b.getElementsByClassName(f)),d}if(c.qsa&&!A[a+" "]&&(!q||!q.test(a))){if(1!==x)w=b,s=a;else if("object"!==b.nodeName.toLowerCase()){(k=b.getAttribute("id"))?k=k.replace(aa,"\\$&"):b.setAttribute("id",k=u),r=g(a),h=r.length,l=V.test(k)?"#"+k:"[id='"+k+"']";while(h--)r[h]=l+" "+qa(r[h]);s=r.join(","),w=_.test(a)&&oa(b.parentNode)||b}if(s)try{return H.apply(d,w.querySelectorAll(s)),d}catch(y){}finally{k===u&&b.removeAttribute("id")}}}return i(a.replace(Q,"$1"),b,d,e)}function ga(){var a=[];function b(c,e){return a.push(c+" ")>d.cacheLength&&delete b[a.shift()],b[c+" "]=e}return b}function ha(a){return a[u]=!0,a}function ia(a){var b=n.createElement("div");try{return!!a(b)}catch(c){return!1}finally{b.parentNode&&b.parentNode.removeChild(b),b=null}}function ja(a,b){var c=a.split("|"),e=c.length;while(e--)d.attrHandle[c[e]]=b}function ka(a,b){var c=b&&a,d=c&&1===a.nodeType&&1===b.nodeType&&(~b.sourceIndex||C)-(~a.sourceIndex||C);if(d)return d;if(c)while(c=c.nextSibling)if(c===b)return-1;return a?1:-1}function la(a){return function(b){var c=b.nodeName.toLowerCase();return"input"===c&&b.type===a}}function ma(a){return function(b){var c=b.nodeName.toLowerCase();return("input"===c||"button"===c)&&b.type===a}}function na(a){return ha(function(b){return b=+b,ha(function(c,d){var e,f=a([],c.length,b),g=f.length;while(g--)c[e=f[g]]&&(c[e]=!(d[e]=c[e]))})})}function oa(a){return a&&"undefined"!=typeof a.getElementsByTagName&&a}c=fa.support={},f=fa.isXML=function(a){var b=a&&(a.ownerDocument||a).documentElement;return b?"HTML"!==b.nodeName:!1},m=fa.setDocument=function(a){var b,e,g=a?a.ownerDocument||a:v;return g!==n&&9===g.nodeType&&g.documentElement?(n=g,o=n.documentElement,p=!f(n),(e=n.defaultView)&&e.top!==e&&(e.addEventListener?e.addEventListener("unload",da,!1):e.attachEvent&&e.attachEvent("onunload",da)),c.attributes=ia(function(a){return a.className="i",!a.getAttribute("className")}),c.getElementsByTagName=ia(function(a){return a.appendChild(n.createComment("")),!a.getElementsByTagName("*").length}),c.getElementsByClassName=Z.test(n.getElementsByClassName),c.getById=ia(function(a){return o.appendChild(a).id=u,!n.getElementsByName||!n.getElementsByName(u).length}),c.getById?(d.find.ID=function(a,b){if("undefined"!=typeof b.getElementById&&p){var c=b.getElementById(a);return c?[c]:[]}},d.filter.ID=function(a){var b=a.replace(ba,ca);return function(a){return a.getAttribute("id")===b}}):(delete d.find.ID,d.filter.ID=function(a){var b=a.replace(ba,ca);return function(a){var c="undefined"!=typeof a.getAttributeNode&&a.getAttributeNode("id");return c&&c.value===b}}),d.find.TAG=c.getElementsByTagName?function(a,b){return"undefined"!=typeof b.getElementsByTagName?b.getElementsByTagName(a):c.qsa?b.querySelectorAll(a):void 0}:function(a,b){var c,d=[],e=0,f=b.getElementsByTagName(a);if("*"===a){while(c=f[e++])1===c.nodeType&&d.push(c);return d}return f},d.find.CLASS=c.getElementsByClassName&&function(a,b){return"undefined"!=typeof b.getElementsByClassName&&p?b.getElementsByClassName(a):void 0},r=[],q=[],(c.qsa=Z.test(n.querySelectorAll))&&(ia(function(a){o.appendChild(a).innerHTML="<a id='"+u+"'></a><select id='"+u+"-\r\\' msallowcapture=''><option selected=''></option></select>",a.querySelectorAll("[msallowcapture^='']").length&&q.push("[*^$]="+L+"*(?:''|\"\")"),a.querySelectorAll("[selected]").length||q.push("\\["+L+"*(?:value|"+K+")"),a.querySelectorAll("[id~="+u+"-]").length||q.push("~="),a.querySelectorAll(":checked").length||q.push(":checked"),a.querySelectorAll("a#"+u+"+*").length||q.push(".#.+[+~]")}),ia(function(a){var b=n.createElement("input");b.setAttribute("type","hidden"),a.appendChild(b).setAttribute("name","D"),a.querySelectorAll("[name=d]").length&&q.push("name"+L+"*[*^$|!~]?="),a.querySelectorAll(":enabled").length||q.push(":enabled",":disabled"),a.querySelectorAll("*,:x"),q.push(",.*:")})),(c.matchesSelector=Z.test(s=o.matches||o.webkitMatchesSelector||o.mozMatchesSelector||o.oMatchesSelector||o.msMatchesSelector))&&ia(function(a){c.disconnectedMatch=s.call(a,"div"),s.call(a,"[s!='']:x"),r.push("!=",O)}),q=q.length&&new RegExp(q.join("|")),r=r.length&&new RegExp(r.join("|")),b=Z.test(o.compareDocumentPosition),t=b||Z.test(o.contains)?function(a,b){var c=9===a.nodeType?a.documentElement:a,d=b&&b.parentNode;return a===d||!(!d||1!==d.nodeType||!(c.contains?c.contains(d):a.compareDocumentPosition&&16&a.compareDocumentPosition(d)))}:function(a,b){if(b)while(b=b.parentNode)if(b===a)return!0;return!1},B=b?function(a,b){if(a===b)return l=!0,0;var d=!a.compareDocumentPosition-!b.compareDocumentPosition;return d?d:(d=(a.ownerDocument||a)===(b.ownerDocument||b)?a.compareDocumentPosition(b):1,1&d||!c.sortDetached&&b.compareDocumentPosition(a)===d?a===n||a.ownerDocument===v&&t(v,a)?-1:b===n||b.ownerDocument===v&&t(v,b)?1:k?J(k,a)-J(k,b):0:4&d?-1:1)}:function(a,b){if(a===b)return l=!0,0;var c,d=0,e=a.parentNode,f=b.parentNode,g=[a],h=[b];if(!e||!f)return a===n?-1:b===n?1:e?-1:f?1:k?J(k,a)-J(k,b):0;if(e===f)return ka(a,b);c=a;while(c=c.parentNode)g.unshift(c);c=b;while(c=c.parentNode)h.unshift(c);while(g[d]===h[d])d++;return d?ka(g[d],h[d]):g[d]===v?-1:h[d]===v?1:0},n):n},fa.matches=function(a,b){return fa(a,null,null,b)},fa.matchesSelector=function(a,b){if((a.ownerDocument||a)!==n&&m(a),b=b.replace(T,"='$1']"),c.matchesSelector&&p&&!A[b+" "]&&(!r||!r.test(b))&&(!q||!q.test(b)))try{var d=s.call(a,b);if(d||c.disconnectedMatch||a.document&&11!==a.document.nodeType)return d}catch(e){}return fa(b,n,null,[a]).length>0},fa.contains=function(a,b){return(a.ownerDocument||a)!==n&&m(a),t(a,b)},fa.attr=function(a,b){(a.ownerDocument||a)!==n&&m(a);var e=d.attrHandle[b.toLowerCase()],f=e&&D.call(d.attrHandle,b.toLowerCase())?e(a,b,!p):void 0;return void 0!==f?f:c.attributes||!p?a.getAttribute(b):(f=a.getAttributeNode(b))&&f.specified?f.value:null},fa.error=function(a){throw new Error("Syntax error, unrecognized expression: "+a)},fa.uniqueSort=function(a){var b,d=[],e=0,f=0;if(l=!c.detectDuplicates,k=!c.sortStable&&a.slice(0),a.sort(B),l){while(b=a[f++])b===a[f]&&(e=d.push(f));while(e--)a.splice(d[e],1)}return k=null,a},e=fa.getText=function(a){var b,c="",d=0,f=a.nodeType;if(f){if(1===f||9===f||11===f){if("string"==typeof a.textContent)return a.textContent;for(a=a.firstChild;a;a=a.nextSibling)c+=e(a)}else if(3===f||4===f)return a.nodeValue}else while(b=a[d++])c+=e(b);return c},d=fa.selectors={cacheLength:50,createPseudo:ha,match:W,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(a){return a[1]=a[1].replace(ba,ca),a[3]=(a[3]||a[4]||a[5]||"").replace(ba,ca),"~="===a[2]&&(a[3]=" "+a[3]+" "),a.slice(0,4)},CHILD:function(a){return a[1]=a[1].toLowerCase(),"nth"===a[1].slice(0,3)?(a[3]||fa.error(a[0]),a[4]=+(a[4]?a[5]+(a[6]||1):2*("even"===a[3]||"odd"===a[3])),a[5]=+(a[7]+a[8]||"odd"===a[3])):a[3]&&fa.error(a[0]),a},PSEUDO:function(a){var b,c=!a[6]&&a[2];return W.CHILD.test(a[0])?null:(a[3]?a[2]=a[4]||a[5]||"":c&&U.test(c)&&(b=g(c,!0))&&(b=c.indexOf(")",c.length-b)-c.length)&&(a[0]=a[0].slice(0,b),a[2]=c.slice(0,b)),a.slice(0,3))}},filter:{TAG:function(a){var b=a.replace(ba,ca).toLowerCase();return"*"===a?function(){return!0}:function(a){return a.nodeName&&a.nodeName.toLowerCase()===b}},CLASS:function(a){var b=y[a+" "];return b||(b=new RegExp("(^|"+L+")"+a+"("+L+"|$)"))&&y(a,function(a){return b.test("string"==typeof a.className&&a.className||"undefined"!=typeof a.getAttribute&&a.getAttribute("class")||"")})},ATTR:function(a,b,c){return function(d){var e=fa.attr(d,a);return null==e?"!="===b:b?(e+="","="===b?e===c:"!="===b?e!==c:"^="===b?c&&0===e.indexOf(c):"*="===b?c&&e.indexOf(c)>-1:"$="===b?c&&e.slice(-c.length)===c:"~="===b?(" "+e.replace(P," ")+" ").indexOf(c)>-1:"|="===b?e===c||e.slice(0,c.length+1)===c+"-":!1):!0}},CHILD:function(a,b,c,d,e){var f="nth"!==a.slice(0,3),g="last"!==a.slice(-4),h="of-type"===b;return 1===d&&0===e?function(a){return!!a.parentNode}:function(b,c,i){var j,k,l,m,n,o,p=f!==g?"nextSibling":"previousSibling",q=b.parentNode,r=h&&b.nodeName.toLowerCase(),s=!i&&!h,t=!1;if(q){if(f){while(p){m=b;while(m=m[p])if(h?m.nodeName.toLowerCase()===r:1===m.nodeType)return!1;o=p="only"===a&&!o&&"nextSibling"}return!0}if(o=[g?q.firstChild:q.lastChild],g&&s){m=q,l=m[u]||(m[u]={}),k=l[m.uniqueID]||(l[m.uniqueID]={}),j=k[a]||[],n=j[0]===w&&j[1],t=n&&j[2],m=n&&q.childNodes[n];while(m=++n&&m&&m[p]||(t=n=0)||o.pop())if(1===m.nodeType&&++t&&m===b){k[a]=[w,n,t];break}}else if(s&&(m=b,l=m[u]||(m[u]={}),k=l[m.uniqueID]||(l[m.uniqueID]={}),j=k[a]||[],n=j[0]===w&&j[1],t=n),t===!1)while(m=++n&&m&&m[p]||(t=n=0)||o.pop())if((h?m.nodeName.toLowerCase()===r:1===m.nodeType)&&++t&&(s&&(l=m[u]||(m[u]={}),k=l[m.uniqueID]||(l[m.uniqueID]={}),k[a]=[w,t]),m===b))break;return t-=e,t===d||t%d===0&&t/d>=0}}},PSEUDO:function(a,b){var c,e=d.pseudos[a]||d.setFilters[a.toLowerCase()]||fa.error("unsupported pseudo: "+a);return e[u]?e(b):e.length>1?(c=[a,a,"",b],d.setFilters.hasOwnProperty(a.toLowerCase())?ha(function(a,c){var d,f=e(a,b),g=f.length;while(g--)d=J(a,f[g]),a[d]=!(c[d]=f[g])}):function(a){return e(a,0,c)}):e}},pseudos:{not:ha(function(a){var b=[],c=[],d=h(a.replace(Q,"$1"));return d[u]?ha(function(a,b,c,e){var f,g=d(a,null,e,[]),h=a.length;while(h--)(f=g[h])&&(a[h]=!(b[h]=f))}):function(a,e,f){return b[0]=a,d(b,null,f,c),b[0]=null,!c.pop()}}),has:ha(function(a){return function(b){return fa(a,b).length>0}}),contains:ha(function(a){return a=a.replace(ba,ca),function(b){return(b.textContent||b.innerText||e(b)).indexOf(a)>-1}}),lang:ha(function(a){return V.test(a||"")||fa.error("unsupported lang: "+a),a=a.replace(ba,ca).toLowerCase(),function(b){var c;do if(c=p?b.lang:b.getAttribute("xml:lang")||b.getAttribute("lang"))return c=c.toLowerCase(),c===a||0===c.indexOf(a+"-");while((b=b.parentNode)&&1===b.nodeType);return!1}}),target:function(b){var c=a.location&&a.location.hash;return c&&c.slice(1)===b.id},root:function(a){return a===o},focus:function(a){return a===n.activeElement&&(!n.hasFocus||n.hasFocus())&&!!(a.type||a.href||~a.tabIndex)},enabled:function(a){return a.disabled===!1},disabled:function(a){return a.disabled===!0},checked:function(a){var b=a.nodeName.toLowerCase();return"input"===b&&!!a.checked||"option"===b&&!!a.selected},selected:function(a){return a.parentNode&&a.parentNode.selectedIndex,a.selected===!0},empty:function(a){for(a=a.firstChild;a;a=a.nextSibling)if(a.nodeType<6)return!1;return!0},parent:function(a){return!d.pseudos.empty(a)},header:function(a){return Y.test(a.nodeName)},input:function(a){return X.test(a.nodeName)},button:function(a){var b=a.nodeName.toLowerCase();return"input"===b&&"button"===a.type||"button"===b},text:function(a){var b;return"input"===a.nodeName.toLowerCase()&&"text"===a.type&&(null==(b=a.getAttribute("type"))||"text"===b.toLowerCase())},first:na(function(){return[0]}),last:na(function(a,b){return[b-1]}),eq:na(function(a,b,c){return[0>c?c+b:c]}),even:na(function(a,b){for(var c=0;b>c;c+=2)a.push(c);return a}),odd:na(function(a,b){for(var c=1;b>c;c+=2)a.push(c);return a}),lt:na(function(a,b,c){for(var d=0>c?c+b:c;--d>=0;)a.push(d);return a}),gt:na(function(a,b,c){for(var d=0>c?c+b:c;++d<b;)a.push(d);return a})}},d.pseudos.nth=d.pseudos.eq;for(b in{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})d.pseudos[b]=la(b);for(b in{submit:!0,reset:!0})d.pseudos[b]=ma(b);function pa(){}pa.prototype=d.filters=d.pseudos,d.setFilters=new pa,g=fa.tokenize=function(a,b){var c,e,f,g,h,i,j,k=z[a+" "];if(k)return b?0:k.slice(0);h=a,i=[],j=d.preFilter;while(h){c&&!(e=R.exec(h))||(e&&(h=h.slice(e[0].length)||h),i.push(f=[])),c=!1,(e=S.exec(h))&&(c=e.shift(),f.push({value:c,type:e[0].replace(Q," ")}),h=h.slice(c.length));for(g in d.filter)!(e=W[g].exec(h))||j[g]&&!(e=j[g](e))||(c=e.shift(),f.push({value:c,type:g,matches:e}),h=h.slice(c.length));if(!c)break}return b?h.length:h?fa.error(a):z(a,i).slice(0)};function qa(a){for(var b=0,c=a.length,d="";c>b;b++)d+=a[b].value;return d}function ra(a,b,c){var d=b.dir,e=c&&"parentNode"===d,f=x++;return b.first?function(b,c,f){while(b=b[d])if(1===b.nodeType||e)return a(b,c,f)}:function(b,c,g){var h,i,j,k=[w,f];if(g){while(b=b[d])if((1===b.nodeType||e)&&a(b,c,g))return!0}else while(b=b[d])if(1===b.nodeType||e){if(j=b[u]||(b[u]={}),i=j[b.uniqueID]||(j[b.uniqueID]={}),(h=i[d])&&h[0]===w&&h[1]===f)return k[2]=h[2];if(i[d]=k,k[2]=a(b,c,g))return!0}}}function sa(a){return a.length>1?function(b,c,d){var e=a.length;while(e--)if(!a[e](b,c,d))return!1;return!0}:a[0]}function ta(a,b,c){for(var d=0,e=b.length;e>d;d++)fa(a,b[d],c);return c}function ua(a,b,c,d,e){for(var f,g=[],h=0,i=a.length,j=null!=b;i>h;h++)(f=a[h])&&(c&&!c(f,d,e)||(g.push(f),j&&b.push(h)));return g}function va(a,b,c,d,e,f){return d&&!d[u]&&(d=va(d)),e&&!e[u]&&(e=va(e,f)),ha(function(f,g,h,i){var j,k,l,m=[],n=[],o=g.length,p=f||ta(b||"*",h.nodeType?[h]:h,[]),q=!a||!f&&b?p:ua(p,m,a,h,i),r=c?e||(f?a:o||d)?[]:g:q;if(c&&c(q,r,h,i),d){j=ua(r,n),d(j,[],h,i),k=j.length;while(k--)(l=j[k])&&(r[n[k]]=!(q[n[k]]=l))}if(f){if(e||a){if(e){j=[],k=r.length;while(k--)(l=r[k])&&j.push(q[k]=l);e(null,r=[],j,i)}k=r.length;while(k--)(l=r[k])&&(j=e?J(f,l):m[k])>-1&&(f[j]=!(g[j]=l))}}else r=ua(r===g?r.splice(o,r.length):r),e?e(null,g,r,i):H.apply(g,r)})}function wa(a){for(var b,c,e,f=a.length,g=d.relative[a[0].type],h=g||d.relative[" "],i=g?1:0,k=ra(function(a){return a===b},h,!0),l=ra(function(a){return J(b,a)>-1},h,!0),m=[function(a,c,d){var e=!g&&(d||c!==j)||((b=c).nodeType?k(a,c,d):l(a,c,d));return b=null,e}];f>i;i++)if(c=d.relative[a[i].type])m=[ra(sa(m),c)];else{if(c=d.filter[a[i].type].apply(null,a[i].matches),c[u]){for(e=++i;f>e;e++)if(d.relative[a[e].type])break;return va(i>1&&sa(m),i>1&&qa(a.slice(0,i-1).concat({value:" "===a[i-2].type?"*":""})).replace(Q,"$1"),c,e>i&&wa(a.slice(i,e)),f>e&&wa(a=a.slice(e)),f>e&&qa(a))}m.push(c)}return sa(m)}function xa(a,b){var c=b.length>0,e=a.length>0,f=function(f,g,h,i,k){var l,o,q,r=0,s="0",t=f&&[],u=[],v=j,x=f||e&&d.find.TAG("*",k),y=w+=null==v?1:Math.random()||.1,z=x.length;for(k&&(j=g===n||g||k);s!==z&&null!=(l=x[s]);s++){if(e&&l){o=0,g||l.ownerDocument===n||(m(l),h=!p);while(q=a[o++])if(q(l,g||n,h)){i.push(l);break}k&&(w=y)}c&&((l=!q&&l)&&r--,f&&t.push(l))}if(r+=s,c&&s!==r){o=0;while(q=b[o++])q(t,u,g,h);if(f){if(r>0)while(s--)t[s]||u[s]||(u[s]=F.call(i));u=ua(u)}H.apply(i,u),k&&!f&&u.length>0&&r+b.length>1&&fa.uniqueSort(i)}return k&&(w=y,j=v),t};return c?ha(f):f}return h=fa.compile=function(a,b){var c,d=[],e=[],f=A[a+" "];if(!f){b||(b=g(a)),c=b.length;while(c--)f=wa(b[c]),f[u]?d.push(f):e.push(f);f=A(a,xa(e,d)),f.selector=a}return f},i=fa.select=function(a,b,e,f){var i,j,k,l,m,n="function"==typeof a&&a,o=!f&&g(a=n.selector||a);if(e=e||[],1===o.length){if(j=o[0]=o[0].slice(0),j.length>2&&"ID"===(k=j[0]).type&&c.getById&&9===b.nodeType&&p&&d.relative[j[1].type]){if(b=(d.find.ID(k.matches[0].replace(ba,ca),b)||[])[0],!b)return e;n&&(b=b.parentNode),a=a.slice(j.shift().value.length)}i=W.needsContext.test(a)?0:j.length;while(i--){if(k=j[i],d.relative[l=k.type])break;if((m=d.find[l])&&(f=m(k.matches[0].replace(ba,ca),_.test(j[0].type)&&oa(b.parentNode)||b))){if(j.splice(i,1),a=f.length&&qa(j),!a)return H.apply(e,f),e;break}}}return(n||h(a,o))(f,b,!p,e,!b||_.test(a)&&oa(b.parentNode)||b),e},c.sortStable=u.split("").sort(B).join("")===u,c.detectDuplicates=!!l,m(),c.sortDetached=ia(function(a){return 1&a.compareDocumentPosition(n.createElement("div"))}),ia(function(a){return a.innerHTML="<a href='#'></a>","#"===a.firstChild.getAttribute("href")})||ja("type|href|height|width",function(a,b,c){return c?void 0:a.getAttribute(b,"type"===b.toLowerCase()?1:2)}),c.attributes&&ia(function(a){return a.innerHTML="<input/>",a.firstChild.setAttribute("value",""),""===a.firstChild.getAttribute("value")})||ja("value",function(a,b,c){return c||"input"!==a.nodeName.toLowerCase()?void 0:a.defaultValue}),ia(function(a){return null==a.getAttribute("disabled")})||ja(K,function(a,b,c){var d;return c?void 0:a[b]===!0?b.toLowerCase():(d=a.getAttributeNode(b))&&d.specified?d.value:null}),fa}(a);n.find=t,n.expr=t.selectors,n.expr[":"]=n.expr.pseudos,n.uniqueSort=n.unique=t.uniqueSort,n.text=t.getText,n.isXMLDoc=t.isXML,n.contains=t.contains;var u=function(a,b,c){var d=[],e=void 0!==c;while((a=a[b])&&9!==a.nodeType)if(1===a.nodeType){if(e&&n(a).is(c))break;d.push(a)}return d},v=function(a,b){for(var c=[];a;a=a.nextSibling)1===a.nodeType&&a!==b&&c.push(a);return c},w=n.expr.match.needsContext,x=/^<([\w-]+)\s*\/?>(?:<\/\1>|)$/,y=/^.[^:#\[\.,]*$/;function z(a,b,c){if(n.isFunction(b))return n.grep(a,function(a,d){return!!b.call(a,d,a)!==c});if(b.nodeType)return n.grep(a,function(a){return a===b!==c});if("string"==typeof b){if(y.test(b))return n.filter(b,a,c);b=n.filter(b,a)}return n.grep(a,function(a){return n.inArray(a,b)>-1!==c})}n.filter=function(a,b,c){var d=b[0];return c&&(a=":not("+a+")"),1===b.length&&1===d.nodeType?n.find.matchesSelector(d,a)?[d]:[]:n.find.matches(a,n.grep(b,function(a){return 1===a.nodeType}))},n.fn.extend({find:function(a){var b,c=[],d=this,e=d.length;if("string"!=typeof a)return this.pushStack(n(a).filter(function(){for(b=0;e>b;b++)if(n.contains(d[b],this))return!0}));for(b=0;e>b;b++)n.find(a,d[b],c);return c=this.pushStack(e>1?n.unique(c):c),c.selector=this.selector?this.selector+" "+a:a,c},filter:function(a){return this.pushStack(z(this,a||[],!1))},not:function(a){return this.pushStack(z(this,a||[],!0))},is:function(a){return!!z(this,"string"==typeof a&&w.test(a)?n(a):a||[],!1).length}});var A,B=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]*))$/,C=n.fn.init=function(a,b,c){var e,f;if(!a)return this;if(c=c||A,"string"==typeof a){if(e="<"===a.charAt(0)&&">"===a.charAt(a.length-1)&&a.length>=3?[null,a,null]:B.exec(a),!e||!e[1]&&b)return!b||b.jquery?(b||c).find(a):this.constructor(b).find(a);if(e[1]){if(b=b instanceof n?b[0]:b,n.merge(this,n.parseHTML(e[1],b&&b.nodeType?b.ownerDocument||b:d,!0)),x.test(e[1])&&n.isPlainObject(b))for(e in b)n.isFunction(this[e])?this[e](b[e]):this.attr(e,b[e]);return this}if(f=d.getElementById(e[2]),f&&f.parentNode){if(f.id!==e[2])return A.find(a);this.length=1,this[0]=f}return this.context=d,this.selector=a,this}return a.nodeType?(this.context=this[0]=a,this.length=1,this):n.isFunction(a)?"undefined"!=typeof c.ready?c.ready(a):a(n):(void 0!==a.selector&&(this.selector=a.selector,this.context=a.context),n.makeArray(a,this))};C.prototype=n.fn,A=n(d);var D=/^(?:parents|prev(?:Until|All))/,E={children:!0,contents:!0,next:!0,prev:!0};n.fn.extend({has:function(a){var b,c=n(a,this),d=c.length;return this.filter(function(){for(b=0;d>b;b++)if(n.contains(this,c[b]))return!0})},closest:function(a,b){for(var c,d=0,e=this.length,f=[],g=w.test(a)||"string"!=typeof a?n(a,b||this.context):0;e>d;d++)for(c=this[d];c&&c!==b;c=c.parentNode)if(c.nodeType<11&&(g?g.index(c)>-1:1===c.nodeType&&n.find.matchesSelector(c,a))){f.push(c);break}return this.pushStack(f.length>1?n.uniqueSort(f):f)},index:function(a){return a?"string"==typeof a?n.inArray(this[0],n(a)):n.inArray(a.jquery?a[0]:a,this):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(a,b){return this.pushStack(n.uniqueSort(n.merge(this.get(),n(a,b))))},addBack:function(a){return this.add(null==a?this.prevObject:this.prevObject.filter(a))}});function F(a,b){do a=a[b];while(a&&1!==a.nodeType);return a}n.each({parent:function(a){var b=a.parentNode;return b&&11!==b.nodeType?b:null},parents:function(a){return u(a,"parentNode")},parentsUntil:function(a,b,c){return u(a,"parentNode",c)},next:function(a){return F(a,"nextSibling")},prev:function(a){return F(a,"previousSibling")},nextAll:function(a){return u(a,"nextSibling")},prevAll:function(a){return u(a,"previousSibling")},nextUntil:function(a,b,c){return u(a,"nextSibling",c)},prevUntil:function(a,b,c){return u(a,"previousSibling",c)},siblings:function(a){return v((a.parentNode||{}).firstChild,a)},children:function(a){return v(a.firstChild)},contents:function(a){return n.nodeName(a,"iframe")?a.contentDocument||a.contentWindow.document:n.merge([],a.childNodes)}},function(a,b){n.fn[a]=function(c,d){var e=n.map(this,b,c);return"Until"!==a.slice(-5)&&(d=c),d&&"string"==typeof d&&(e=n.filter(d,e)),this.length>1&&(E[a]||(e=n.uniqueSort(e)),D.test(a)&&(e=e.reverse())),this.pushStack(e)}});var G=/\S+/g;function H(a){var b={};return n.each(a.match(G)||[],function(a,c){b[c]=!0}),b}n.Callbacks=function(a){a="string"==typeof a?H(a):n.extend({},a);var b,c,d,e,f=[],g=[],h=-1,i=function(){for(e=a.once,d=b=!0;g.length;h=-1){c=g.shift();while(++h<f.length)f[h].apply(c[0],c[1])===!1&&a.stopOnFalse&&(h=f.length,c=!1)}a.memory||(c=!1),b=!1,e&&(f=c?[]:"")},j={add:function(){return f&&(c&&!b&&(h=f.length-1,g.push(c)),function d(b){n.each(b,function(b,c){n.isFunction(c)?a.unique&&j.has(c)||f.push(c):c&&c.length&&"string"!==n.type(c)&&d(c)})}(arguments),c&&!b&&i()),this},remove:function(){return n.each(arguments,function(a,b){var c;while((c=n.inArray(b,f,c))>-1)f.splice(c,1),h>=c&&h--}),this},has:function(a){return a?n.inArray(a,f)>-1:f.length>0},empty:function(){return f&&(f=[]),this},disable:function(){return e=g=[],f=c="",this},disabled:function(){return!f},lock:function(){return e=!0,c||j.disable(),this},locked:function(){return!!e},fireWith:function(a,c){return e||(c=c||[],c=[a,c.slice?c.slice():c],g.push(c),b||i()),this},fire:function(){return j.fireWith(this,arguments),this},fired:function(){return!!d}};return j},n.extend({Deferred:function(a){var b=[["resolve","done",n.Callbacks("once memory"),"resolved"],["reject","fail",n.Callbacks("once memory"),"rejected"],["notify","progress",n.Callbacks("memory")]],c="pending",d={state:function(){return c},always:function(){return e.done(arguments).fail(arguments),this},then:function(){var a=arguments;return n.Deferred(function(c){n.each(b,function(b,f){var g=n.isFunction(a[b])&&a[b];e[f[1]](function(){var a=g&&g.apply(this,arguments);a&&n.isFunction(a.promise)?a.promise().progress(c.notify).done(c.resolve).fail(c.reject):c[f[0]+"With"](this===d?c.promise():this,g?[a]:arguments)})}),a=null}).promise()},promise:function(a){return null!=a?n.extend(a,d):d}},e={};return d.pipe=d.then,n.each(b,function(a,f){var g=f[2],h=f[3];d[f[1]]=g.add,h&&g.add(function(){c=h},b[1^a][2].disable,b[2][2].lock),e[f[0]]=function(){return e[f[0]+"With"](this===e?d:this,arguments),this},e[f[0]+"With"]=g.fireWith}),d.promise(e),a&&a.call(e,e),e},when:function(a){var b=0,c=e.call(arguments),d=c.length,f=1!==d||a&&n.isFunction(a.promise)?d:0,g=1===f?a:n.Deferred(),h=function(a,b,c){return function(d){b[a]=this,c[a]=arguments.length>1?e.call(arguments):d,c===i?g.notifyWith(b,c):--f||g.resolveWith(b,c)}},i,j,k;if(d>1)for(i=new Array(d),j=new Array(d),k=new Array(d);d>b;b++)c[b]&&n.isFunction(c[b].promise)?c[b].promise().progress(h(b,j,i)).done(h(b,k,c)).fail(g.reject):--f;return f||g.resolveWith(k,c),g.promise()}});var I;n.fn.ready=function(a){return n.ready.promise().done(a),this},n.extend({isReady:!1,readyWait:1,holdReady:function(a){a?n.readyWait++:n.ready(!0)},ready:function(a){(a===!0?--n.readyWait:n.isReady)||(n.isReady=!0,a!==!0&&--n.readyWait>0||(I.resolveWith(d,[n]),n.fn.triggerHandler&&(n(d).triggerHandler("ready"),n(d).off("ready"))))}});function J(){d.addEventListener?(d.removeEventListener("DOMContentLoaded",K),a.removeEventListener("load",K)):(d.detachEvent("onreadystatechange",K),a.detachEvent("onload",K))}function K(){(d.addEventListener||"load"===a.event.type||"complete"===d.readyState)&&(J(),n.ready())}n.ready.promise=function(b){if(!I)if(I=n.Deferred(),"complete"===d.readyState||"loading"!==d.readyState&&!d.documentElement.doScroll)a.setTimeout(n.ready);else if(d.addEventListener)d.addEventListener("DOMContentLoaded",K),a.addEventListener("load",K);else{d.attachEvent("onreadystatechange",K),a.attachEvent("onload",K);var c=!1;try{c=null==a.frameElement&&d.documentElement}catch(e){}c&&c.doScroll&&!function f(){if(!n.isReady){try{c.doScroll("left")}catch(b){return a.setTimeout(f,50)}J(),n.ready()}}()}return I.promise(b)},n.ready.promise();var L;for(L in n(l))break;l.ownFirst="0"===L,l.inlineBlockNeedsLayout=!1,n(function(){var a,b,c,e;c=d.getElementsByTagName("body")[0],c&&c.style&&(b=d.createElement("div"),e=d.createElement("div"),e.style.cssText="position:absolute;border:0;width:0;height:0;top:0;left:-9999px",c.appendChild(e).appendChild(b),"undefined"!=typeof b.style.zoom&&(b.style.cssText="display:inline;margin:0;border:0;padding:1px;width:1px;zoom:1",l.inlineBlockNeedsLayout=a=3===b.offsetWidth,a&&(c.style.zoom=1)),c.removeChild(e))}),function(){var a=d.createElement("div");l.deleteExpando=!0;try{delete a.test}catch(b){l.deleteExpando=!1}a=null}();var M=function(a){var b=n.noData[(a.nodeName+" ").toLowerCase()],c=+a.nodeType||1;return 1!==c&&9!==c?!1:!b||b!==!0&&a.getAttribute("classid")===b},N=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,O=/([A-Z])/g;function P(a,b,c){if(void 0===c&&1===a.nodeType){var d="data-"+b.replace(O,"-$1").toLowerCase();if(c=a.getAttribute(d),"string"==typeof c){try{c="true"===c?!0:"false"===c?!1:"null"===c?null:+c+""===c?+c:N.test(c)?n.parseJSON(c):c}catch(e){}n.data(a,b,c)}else c=void 0;
}return c}function Q(a){var b;for(b in a)if(("data"!==b||!n.isEmptyObject(a[b]))&&"toJSON"!==b)return!1;return!0}function R(a,b,d,e){if(M(a)){var f,g,h=n.expando,i=a.nodeType,j=i?n.cache:a,k=i?a[h]:a[h]&&h;if(k&&j[k]&&(e||j[k].data)||void 0!==d||"string"!=typeof b)return k||(k=i?a[h]=c.pop()||n.guid++:h),j[k]||(j[k]=i?{}:{toJSON:n.noop}),"object"!=typeof b&&"function"!=typeof b||(e?j[k]=n.extend(j[k],b):j[k].data=n.extend(j[k].data,b)),g=j[k],e||(g.data||(g.data={}),g=g.data),void 0!==d&&(g[n.camelCase(b)]=d),"string"==typeof b?(f=g[b],null==f&&(f=g[n.camelCase(b)])):f=g,f}}function S(a,b,c){if(M(a)){var d,e,f=a.nodeType,g=f?n.cache:a,h=f?a[n.expando]:n.expando;if(g[h]){if(b&&(d=c?g[h]:g[h].data)){n.isArray(b)?b=b.concat(n.map(b,n.camelCase)):b in d?b=[b]:(b=n.camelCase(b),b=b in d?[b]:b.split(" ")),e=b.length;while(e--)delete d[b[e]];if(c?!Q(d):!n.isEmptyObject(d))return}(c||(delete g[h].data,Q(g[h])))&&(f?n.cleanData([a],!0):l.deleteExpando||g!=g.window?delete g[h]:g[h]=void 0)}}}n.extend({cache:{},noData:{"applet ":!0,"embed ":!0,"object ":"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"},hasData:function(a){return a=a.nodeType?n.cache[a[n.expando]]:a[n.expando],!!a&&!Q(a)},data:function(a,b,c){return R(a,b,c)},removeData:function(a,b){return S(a,b)},_data:function(a,b,c){return R(a,b,c,!0)},_removeData:function(a,b){return S(a,b,!0)}}),n.fn.extend({data:function(a,b){var c,d,e,f=this[0],g=f&&f.attributes;if(void 0===a){if(this.length&&(e=n.data(f),1===f.nodeType&&!n._data(f,"parsedAttrs"))){c=g.length;while(c--)g[c]&&(d=g[c].name,0===d.indexOf("data-")&&(d=n.camelCase(d.slice(5)),P(f,d,e[d])));n._data(f,"parsedAttrs",!0)}return e}return"object"==typeof a?this.each(function(){n.data(this,a)}):arguments.length>1?this.each(function(){n.data(this,a,b)}):f?P(f,a,n.data(f,a)):void 0},removeData:function(a){return this.each(function(){n.removeData(this,a)})}}),n.extend({queue:function(a,b,c){var d;return a?(b=(b||"fx")+"queue",d=n._data(a,b),c&&(!d||n.isArray(c)?d=n._data(a,b,n.makeArray(c)):d.push(c)),d||[]):void 0},dequeue:function(a,b){b=b||"fx";var c=n.queue(a,b),d=c.length,e=c.shift(),f=n._queueHooks(a,b),g=function(){n.dequeue(a,b)};"inprogress"===e&&(e=c.shift(),d--),e&&("fx"===b&&c.unshift("inprogress"),delete f.stop,e.call(a,g,f)),!d&&f&&f.empty.fire()},_queueHooks:function(a,b){var c=b+"queueHooks";return n._data(a,c)||n._data(a,c,{empty:n.Callbacks("once memory").add(function(){n._removeData(a,b+"queue"),n._removeData(a,c)})})}}),n.fn.extend({queue:function(a,b){var c=2;return"string"!=typeof a&&(b=a,a="fx",c--),arguments.length<c?n.queue(this[0],a):void 0===b?this:this.each(function(){var c=n.queue(this,a,b);n._queueHooks(this,a),"fx"===a&&"inprogress"!==c[0]&&n.dequeue(this,a)})},dequeue:function(a){return this.each(function(){n.dequeue(this,a)})},clearQueue:function(a){return this.queue(a||"fx",[])},promise:function(a,b){var c,d=1,e=n.Deferred(),f=this,g=this.length,h=function(){--d||e.resolveWith(f,[f])};"string"!=typeof a&&(b=a,a=void 0),a=a||"fx";while(g--)c=n._data(f[g],a+"queueHooks"),c&&c.empty&&(d++,c.empty.add(h));return h(),e.promise(b)}}),function(){var a;l.shrinkWrapBlocks=function(){if(null!=a)return a;a=!1;var b,c,e;return c=d.getElementsByTagName("body")[0],c&&c.style?(b=d.createElement("div"),e=d.createElement("div"),e.style.cssText="position:absolute;border:0;width:0;height:0;top:0;left:-9999px",c.appendChild(e).appendChild(b),"undefined"!=typeof b.style.zoom&&(b.style.cssText="-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;display:block;margin:0;border:0;padding:1px;width:1px;zoom:1",b.appendChild(d.createElement("div")).style.width="5px",a=3!==b.offsetWidth),c.removeChild(e),a):void 0}}();var T=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,U=new RegExp("^(?:([+-])=|)("+T+")([a-z%]*)$","i"),V=["Top","Right","Bottom","Left"],W=function(a,b){return a=b||a,"none"===n.css(a,"display")||!n.contains(a.ownerDocument,a)};function X(a,b,c,d){var e,f=1,g=20,h=d?function(){return d.cur()}:function(){return n.css(a,b,"")},i=h(),j=c&&c[3]||(n.cssNumber[b]?"":"px"),k=(n.cssNumber[b]||"px"!==j&&+i)&&U.exec(n.css(a,b));if(k&&k[3]!==j){j=j||k[3],c=c||[],k=+i||1;do f=f||".5",k/=f,n.style(a,b,k+j);while(f!==(f=h()/i)&&1!==f&&--g)}return c&&(k=+k||+i||0,e=c[1]?k+(c[1]+1)*c[2]:+c[2],d&&(d.unit=j,d.start=k,d.end=e)),e}var Y=function(a,b,c,d,e,f,g){var h=0,i=a.length,j=null==c;if("object"===n.type(c)){e=!0;for(h in c)Y(a,b,h,c[h],!0,f,g)}else if(void 0!==d&&(e=!0,n.isFunction(d)||(g=!0),j&&(g?(b.call(a,d),b=null):(j=b,b=function(a,b,c){return j.call(n(a),c)})),b))for(;i>h;h++)b(a[h],c,g?d:d.call(a[h],h,b(a[h],c)));return e?a:j?b.call(a):i?b(a[0],c):f},Z=/^(?:checkbox|radio)$/i,$=/<([\w:-]+)/,_=/^$|\/(?:java|ecma)script/i,aa=/^\s+/,ba="abbr|article|aside|audio|bdi|canvas|data|datalist|details|dialog|figcaption|figure|footer|header|hgroup|main|mark|meter|nav|output|picture|progress|section|summary|template|time|video";function ca(a){var b=ba.split("|"),c=a.createDocumentFragment();if(c.createElement)while(b.length)c.createElement(b.pop());return c}!function(){var a=d.createElement("div"),b=d.createDocumentFragment(),c=d.createElement("input");a.innerHTML="  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>",l.leadingWhitespace=3===a.firstChild.nodeType,l.tbody=!a.getElementsByTagName("tbody").length,l.htmlSerialize=!!a.getElementsByTagName("link").length,l.html5Clone="<:nav></:nav>"!==d.createElement("nav").cloneNode(!0).outerHTML,c.type="checkbox",c.checked=!0,b.appendChild(c),l.appendChecked=c.checked,a.innerHTML="<textarea>x</textarea>",l.noCloneChecked=!!a.cloneNode(!0).lastChild.defaultValue,b.appendChild(a),c=d.createElement("input"),c.setAttribute("type","radio"),c.setAttribute("checked","checked"),c.setAttribute("name","t"),a.appendChild(c),l.checkClone=a.cloneNode(!0).cloneNode(!0).lastChild.checked,l.noCloneEvent=!!a.addEventListener,a[n.expando]=1,l.attributes=!a.getAttribute(n.expando)}();var da={option:[1,"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],area:[1,"<map>","</map>"],param:[1,"<object>","</object>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:l.htmlSerialize?[0,"",""]:[1,"X<div>","</div>"]};da.optgroup=da.option,da.tbody=da.tfoot=da.colgroup=da.caption=da.thead,da.th=da.td;function ea(a,b){var c,d,e=0,f="undefined"!=typeof a.getElementsByTagName?a.getElementsByTagName(b||"*"):"undefined"!=typeof a.querySelectorAll?a.querySelectorAll(b||"*"):void 0;if(!f)for(f=[],c=a.childNodes||a;null!=(d=c[e]);e++)!b||n.nodeName(d,b)?f.push(d):n.merge(f,ea(d,b));return void 0===b||b&&n.nodeName(a,b)?n.merge([a],f):f}function fa(a,b){for(var c,d=0;null!=(c=a[d]);d++)n._data(c,"globalEval",!b||n._data(b[d],"globalEval"))}var ga=/<|&#?\w+;/,ha=/<tbody/i;function ia(a){Z.test(a.type)&&(a.defaultChecked=a.checked)}function ja(a,b,c,d,e){for(var f,g,h,i,j,k,m,o=a.length,p=ca(b),q=[],r=0;o>r;r++)if(g=a[r],g||0===g)if("object"===n.type(g))n.merge(q,g.nodeType?[g]:g);else if(ga.test(g)){i=i||p.appendChild(b.createElement("div")),j=($.exec(g)||["",""])[1].toLowerCase(),m=da[j]||da._default,i.innerHTML=m[1]+n.htmlPrefilter(g)+m[2],f=m[0];while(f--)i=i.lastChild;if(!l.leadingWhitespace&&aa.test(g)&&q.push(b.createTextNode(aa.exec(g)[0])),!l.tbody){g="table"!==j||ha.test(g)?"<table>"!==m[1]||ha.test(g)?0:i:i.firstChild,f=g&&g.childNodes.length;while(f--)n.nodeName(k=g.childNodes[f],"tbody")&&!k.childNodes.length&&g.removeChild(k)}n.merge(q,i.childNodes),i.textContent="";while(i.firstChild)i.removeChild(i.firstChild);i=p.lastChild}else q.push(b.createTextNode(g));i&&p.removeChild(i),l.appendChecked||n.grep(ea(q,"input"),ia),r=0;while(g=q[r++])if(d&&n.inArray(g,d)>-1)e&&e.push(g);else if(h=n.contains(g.ownerDocument,g),i=ea(p.appendChild(g),"script"),h&&fa(i),c){f=0;while(g=i[f++])_.test(g.type||"")&&c.push(g)}return i=null,p}!function(){var b,c,e=d.createElement("div");for(b in{submit:!0,change:!0,focusin:!0})c="on"+b,(l[b]=c in a)||(e.setAttribute(c,"t"),l[b]=e.attributes[c].expando===!1);e=null}();var ka=/^(?:input|select|textarea)$/i,la=/^key/,ma=/^(?:mouse|pointer|contextmenu|drag|drop)|click/,na=/^(?:focusinfocus|focusoutblur)$/,oa=/^([^.]*)(?:\.(.+)|)/;function pa(){return!0}function qa(){return!1}function ra(){try{return d.activeElement}catch(a){}}function sa(a,b,c,d,e,f){var g,h;if("object"==typeof b){"string"!=typeof c&&(d=d||c,c=void 0);for(h in b)sa(a,h,c,d,b[h],f);return a}if(null==d&&null==e?(e=c,d=c=void 0):null==e&&("string"==typeof c?(e=d,d=void 0):(e=d,d=c,c=void 0)),e===!1)e=qa;else if(!e)return a;return 1===f&&(g=e,e=function(a){return n().off(a),g.apply(this,arguments)},e.guid=g.guid||(g.guid=n.guid++)),a.each(function(){n.event.add(this,b,e,d,c)})}n.event={global:{},add:function(a,b,c,d,e){var f,g,h,i,j,k,l,m,o,p,q,r=n._data(a);if(r){c.handler&&(i=c,c=i.handler,e=i.selector),c.guid||(c.guid=n.guid++),(g=r.events)||(g=r.events={}),(k=r.handle)||(k=r.handle=function(a){return"undefined"==typeof n||a&&n.event.triggered===a.type?void 0:n.event.dispatch.apply(k.elem,arguments)},k.elem=a),b=(b||"").match(G)||[""],h=b.length;while(h--)f=oa.exec(b[h])||[],o=q=f[1],p=(f[2]||"").split(".").sort(),o&&(j=n.event.special[o]||{},o=(e?j.delegateType:j.bindType)||o,j=n.event.special[o]||{},l=n.extend({type:o,origType:q,data:d,handler:c,guid:c.guid,selector:e,needsContext:e&&n.expr.match.needsContext.test(e),namespace:p.join(".")},i),(m=g[o])||(m=g[o]=[],m.delegateCount=0,j.setup&&j.setup.call(a,d,p,k)!==!1||(a.addEventListener?a.addEventListener(o,k,!1):a.attachEvent&&a.attachEvent("on"+o,k))),j.add&&(j.add.call(a,l),l.handler.guid||(l.handler.guid=c.guid)),e?m.splice(m.delegateCount++,0,l):m.push(l),n.event.global[o]=!0);a=null}},remove:function(a,b,c,d,e){var f,g,h,i,j,k,l,m,o,p,q,r=n.hasData(a)&&n._data(a);if(r&&(k=r.events)){b=(b||"").match(G)||[""],j=b.length;while(j--)if(h=oa.exec(b[j])||[],o=q=h[1],p=(h[2]||"").split(".").sort(),o){l=n.event.special[o]||{},o=(d?l.delegateType:l.bindType)||o,m=k[o]||[],h=h[2]&&new RegExp("(^|\\.)"+p.join("\\.(?:.*\\.|)")+"(\\.|$)"),i=f=m.length;while(f--)g=m[f],!e&&q!==g.origType||c&&c.guid!==g.guid||h&&!h.test(g.namespace)||d&&d!==g.selector&&("**"!==d||!g.selector)||(m.splice(f,1),g.selector&&m.delegateCount--,l.remove&&l.remove.call(a,g));i&&!m.length&&(l.teardown&&l.teardown.call(a,p,r.handle)!==!1||n.removeEvent(a,o,r.handle),delete k[o])}else for(o in k)n.event.remove(a,o+b[j],c,d,!0);n.isEmptyObject(k)&&(delete r.handle,n._removeData(a,"events"))}},trigger:function(b,c,e,f){var g,h,i,j,l,m,o,p=[e||d],q=k.call(b,"type")?b.type:b,r=k.call(b,"namespace")?b.namespace.split("."):[];if(i=m=e=e||d,3!==e.nodeType&&8!==e.nodeType&&!na.test(q+n.event.triggered)&&(q.indexOf(".")>-1&&(r=q.split("."),q=r.shift(),r.sort()),h=q.indexOf(":")<0&&"on"+q,b=b[n.expando]?b:new n.Event(q,"object"==typeof b&&b),b.isTrigger=f?2:3,b.namespace=r.join("."),b.rnamespace=b.namespace?new RegExp("(^|\\.)"+r.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,b.result=void 0,b.target||(b.target=e),c=null==c?[b]:n.makeArray(c,[b]),l=n.event.special[q]||{},f||!l.trigger||l.trigger.apply(e,c)!==!1)){if(!f&&!l.noBubble&&!n.isWindow(e)){for(j=l.delegateType||q,na.test(j+q)||(i=i.parentNode);i;i=i.parentNode)p.push(i),m=i;m===(e.ownerDocument||d)&&p.push(m.defaultView||m.parentWindow||a)}o=0;while((i=p[o++])&&!b.isPropagationStopped())b.type=o>1?j:l.bindType||q,g=(n._data(i,"events")||{})[b.type]&&n._data(i,"handle"),g&&g.apply(i,c),g=h&&i[h],g&&g.apply&&M(i)&&(b.result=g.apply(i,c),b.result===!1&&b.preventDefault());if(b.type=q,!f&&!b.isDefaultPrevented()&&(!l._default||l._default.apply(p.pop(),c)===!1)&&M(e)&&h&&e[q]&&!n.isWindow(e)){m=e[h],m&&(e[h]=null),n.event.triggered=q;try{e[q]()}catch(s){}n.event.triggered=void 0,m&&(e[h]=m)}return b.result}},dispatch:function(a){a=n.event.fix(a);var b,c,d,f,g,h=[],i=e.call(arguments),j=(n._data(this,"events")||{})[a.type]||[],k=n.event.special[a.type]||{};if(i[0]=a,a.delegateTarget=this,!k.preDispatch||k.preDispatch.call(this,a)!==!1){h=n.event.handlers.call(this,a,j),b=0;while((f=h[b++])&&!a.isPropagationStopped()){a.currentTarget=f.elem,c=0;while((g=f.handlers[c++])&&!a.isImmediatePropagationStopped())a.rnamespace&&!a.rnamespace.test(g.namespace)||(a.handleObj=g,a.data=g.data,d=((n.event.special[g.origType]||{}).handle||g.handler).apply(f.elem,i),void 0!==d&&(a.result=d)===!1&&(a.preventDefault(),a.stopPropagation()))}return k.postDispatch&&k.postDispatch.call(this,a),a.result}},handlers:function(a,b){var c,d,e,f,g=[],h=b.delegateCount,i=a.target;if(h&&i.nodeType&&("click"!==a.type||isNaN(a.button)||a.button<1))for(;i!=this;i=i.parentNode||this)if(1===i.nodeType&&(i.disabled!==!0||"click"!==a.type)){for(d=[],c=0;h>c;c++)f=b[c],e=f.selector+" ",void 0===d[e]&&(d[e]=f.needsContext?n(e,this).index(i)>-1:n.find(e,this,null,[i]).length),d[e]&&d.push(f);d.length&&g.push({elem:i,handlers:d})}return h<b.length&&g.push({elem:this,handlers:b.slice(h)}),g},fix:function(a){if(a[n.expando])return a;var b,c,e,f=a.type,g=a,h=this.fixHooks[f];h||(this.fixHooks[f]=h=ma.test(f)?this.mouseHooks:la.test(f)?this.keyHooks:{}),e=h.props?this.props.concat(h.props):this.props,a=new n.Event(g),b=e.length;while(b--)c=e[b],a[c]=g[c];return a.target||(a.target=g.srcElement||d),3===a.target.nodeType&&(a.target=a.target.parentNode),a.metaKey=!!a.metaKey,h.filter?h.filter(a,g):a},props:"altKey bubbles cancelable ctrlKey currentTarget detail eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),fixHooks:{},keyHooks:{props:"char charCode key keyCode".split(" "),filter:function(a,b){return null==a.which&&(a.which=null!=b.charCode?b.charCode:b.keyCode),a}},mouseHooks:{props:"button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),filter:function(a,b){var c,e,f,g=b.button,h=b.fromElement;return null==a.pageX&&null!=b.clientX&&(e=a.target.ownerDocument||d,f=e.documentElement,c=e.body,a.pageX=b.clientX+(f&&f.scrollLeft||c&&c.scrollLeft||0)-(f&&f.clientLeft||c&&c.clientLeft||0),a.pageY=b.clientY+(f&&f.scrollTop||c&&c.scrollTop||0)-(f&&f.clientTop||c&&c.clientTop||0)),!a.relatedTarget&&h&&(a.relatedTarget=h===a.target?b.toElement:h),a.which||void 0===g||(a.which=1&g?1:2&g?3:4&g?2:0),a}},special:{load:{noBubble:!0},focus:{trigger:function(){if(this!==ra()&&this.focus)try{return this.focus(),!1}catch(a){}},delegateType:"focusin"},blur:{trigger:function(){return this===ra()&&this.blur?(this.blur(),!1):void 0},delegateType:"focusout"},click:{trigger:function(){return n.nodeName(this,"input")&&"checkbox"===this.type&&this.click?(this.click(),!1):void 0},_default:function(a){return n.nodeName(a.target,"a")}},beforeunload:{postDispatch:function(a){void 0!==a.result&&a.originalEvent&&(a.originalEvent.returnValue=a.result)}}},simulate:function(a,b,c){var d=n.extend(new n.Event,c,{type:a,isSimulated:!0});n.event.trigger(d,null,b),d.isDefaultPrevented()&&c.preventDefault()}},n.removeEvent=d.removeEventListener?function(a,b,c){a.removeEventListener&&a.removeEventListener(b,c)}:function(a,b,c){var d="on"+b;a.detachEvent&&("undefined"==typeof a[d]&&(a[d]=null),a.detachEvent(d,c))},n.Event=function(a,b){return this instanceof n.Event?(a&&a.type?(this.originalEvent=a,this.type=a.type,this.isDefaultPrevented=a.defaultPrevented||void 0===a.defaultPrevented&&a.returnValue===!1?pa:qa):this.type=a,b&&n.extend(this,b),this.timeStamp=a&&a.timeStamp||n.now(),void(this[n.expando]=!0)):new n.Event(a,b)},n.Event.prototype={constructor:n.Event,isDefaultPrevented:qa,isPropagationStopped:qa,isImmediatePropagationStopped:qa,preventDefault:function(){var a=this.originalEvent;this.isDefaultPrevented=pa,a&&(a.preventDefault?a.preventDefault():a.returnValue=!1)},stopPropagation:function(){var a=this.originalEvent;this.isPropagationStopped=pa,a&&!this.isSimulated&&(a.stopPropagation&&a.stopPropagation(),a.cancelBubble=!0)},stopImmediatePropagation:function(){var a=this.originalEvent;this.isImmediatePropagationStopped=pa,a&&a.stopImmediatePropagation&&a.stopImmediatePropagation(),this.stopPropagation()}},n.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(a,b){n.event.special[a]={delegateType:b,bindType:b,handle:function(a){var c,d=this,e=a.relatedTarget,f=a.handleObj;return e&&(e===d||n.contains(d,e))||(a.type=f.origType,c=f.handler.apply(this,arguments),a.type=b),c}}}),l.submit||(n.event.special.submit={setup:function(){return n.nodeName(this,"form")?!1:void n.event.add(this,"click._submit keypress._submit",function(a){var b=a.target,c=n.nodeName(b,"input")||n.nodeName(b,"button")?n.prop(b,"form"):void 0;c&&!n._data(c,"submit")&&(n.event.add(c,"submit._submit",function(a){a._submitBubble=!0}),n._data(c,"submit",!0))})},postDispatch:function(a){a._submitBubble&&(delete a._submitBubble,this.parentNode&&!a.isTrigger&&n.event.simulate("submit",this.parentNode,a))},teardown:function(){return n.nodeName(this,"form")?!1:void n.event.remove(this,"._submit")}}),l.change||(n.event.special.change={setup:function(){return ka.test(this.nodeName)?("checkbox"!==this.type&&"radio"!==this.type||(n.event.add(this,"propertychange._change",function(a){"checked"===a.originalEvent.propertyName&&(this._justChanged=!0)}),n.event.add(this,"click._change",function(a){this._justChanged&&!a.isTrigger&&(this._justChanged=!1),n.event.simulate("change",this,a)})),!1):void n.event.add(this,"beforeactivate._change",function(a){var b=a.target;ka.test(b.nodeName)&&!n._data(b,"change")&&(n.event.add(b,"change._change",function(a){!this.parentNode||a.isSimulated||a.isTrigger||n.event.simulate("change",this.parentNode,a)}),n._data(b,"change",!0))})},handle:function(a){var b=a.target;return this!==b||a.isSimulated||a.isTrigger||"radio"!==b.type&&"checkbox"!==b.type?a.handleObj.handler.apply(this,arguments):void 0},teardown:function(){return n.event.remove(this,"._change"),!ka.test(this.nodeName)}}),l.focusin||n.each({focus:"focusin",blur:"focusout"},function(a,b){var c=function(a){n.event.simulate(b,a.target,n.event.fix(a))};n.event.special[b]={setup:function(){var d=this.ownerDocument||this,e=n._data(d,b);e||d.addEventListener(a,c,!0),n._data(d,b,(e||0)+1)},teardown:function(){var d=this.ownerDocument||this,e=n._data(d,b)-1;e?n._data(d,b,e):(d.removeEventListener(a,c,!0),n._removeData(d,b))}}}),n.fn.extend({on:function(a,b,c,d){return sa(this,a,b,c,d)},one:function(a,b,c,d){return sa(this,a,b,c,d,1)},off:function(a,b,c){var d,e;if(a&&a.preventDefault&&a.handleObj)return d=a.handleObj,n(a.delegateTarget).off(d.namespace?d.origType+"."+d.namespace:d.origType,d.selector,d.handler),this;if("object"==typeof a){for(e in a)this.off(e,b,a[e]);return this}return b!==!1&&"function"!=typeof b||(c=b,b=void 0),c===!1&&(c=qa),this.each(function(){n.event.remove(this,a,c,b)})},trigger:function(a,b){return this.each(function(){n.event.trigger(a,b,this)})},triggerHandler:function(a,b){var c=this[0];return c?n.event.trigger(a,b,c,!0):void 0}});var ta=/ jQuery\d+="(?:null|\d+)"/g,ua=new RegExp("<(?:"+ba+")[\\s/>]","i"),va=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:-]+)[^>]*)\/>/gi,wa=/<script|<style|<link/i,xa=/checked\s*(?:[^=]|=\s*.checked.)/i,ya=/^true\/(.*)/,za=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,Aa=ca(d),Ba=Aa.appendChild(d.createElement("div"));function Ca(a,b){return n.nodeName(a,"table")&&n.nodeName(11!==b.nodeType?b:b.firstChild,"tr")?a.getElementsByTagName("tbody")[0]||a.appendChild(a.ownerDocument.createElement("tbody")):a}function Da(a){return a.type=(null!==n.find.attr(a,"type"))+"/"+a.type,a}function Ea(a){var b=ya.exec(a.type);return b?a.type=b[1]:a.removeAttribute("type"),a}function Fa(a,b){if(1===b.nodeType&&n.hasData(a)){var c,d,e,f=n._data(a),g=n._data(b,f),h=f.events;if(h){delete g.handle,g.events={};for(c in h)for(d=0,e=h[c].length;e>d;d++)n.event.add(b,c,h[c][d])}g.data&&(g.data=n.extend({},g.data))}}function Ga(a,b){var c,d,e;if(1===b.nodeType){if(c=b.nodeName.toLowerCase(),!l.noCloneEvent&&b[n.expando]){e=n._data(b);for(d in e.events)n.removeEvent(b,d,e.handle);b.removeAttribute(n.expando)}"script"===c&&b.text!==a.text?(Da(b).text=a.text,Ea(b)):"object"===c?(b.parentNode&&(b.outerHTML=a.outerHTML),l.html5Clone&&a.innerHTML&&!n.trim(b.innerHTML)&&(b.innerHTML=a.innerHTML)):"input"===c&&Z.test(a.type)?(b.defaultChecked=b.checked=a.checked,b.value!==a.value&&(b.value=a.value)):"option"===c?b.defaultSelected=b.selected=a.defaultSelected:"input"!==c&&"textarea"!==c||(b.defaultValue=a.defaultValue)}}function Ha(a,b,c,d){b=f.apply([],b);var e,g,h,i,j,k,m=0,o=a.length,p=o-1,q=b[0],r=n.isFunction(q);if(r||o>1&&"string"==typeof q&&!l.checkClone&&xa.test(q))return a.each(function(e){var f=a.eq(e);r&&(b[0]=q.call(this,e,f.html())),Ha(f,b,c,d)});if(o&&(k=ja(b,a[0].ownerDocument,!1,a,d),e=k.firstChild,1===k.childNodes.length&&(k=e),e||d)){for(i=n.map(ea(k,"script"),Da),h=i.length;o>m;m++)g=k,m!==p&&(g=n.clone(g,!0,!0),h&&n.merge(i,ea(g,"script"))),c.call(a[m],g,m);if(h)for(j=i[i.length-1].ownerDocument,n.map(i,Ea),m=0;h>m;m++)g=i[m],_.test(g.type||"")&&!n._data(g,"globalEval")&&n.contains(j,g)&&(g.src?n._evalUrl&&n._evalUrl(g.src):n.globalEval((g.text||g.textContent||g.innerHTML||"").replace(za,"")));k=e=null}return a}function Ia(a,b,c){for(var d,e=b?n.filter(b,a):a,f=0;null!=(d=e[f]);f++)c||1!==d.nodeType||n.cleanData(ea(d)),d.parentNode&&(c&&n.contains(d.ownerDocument,d)&&fa(ea(d,"script")),d.parentNode.removeChild(d));return a}n.extend({htmlPrefilter:function(a){return a.replace(va,"<$1></$2>")},clone:function(a,b,c){var d,e,f,g,h,i=n.contains(a.ownerDocument,a);if(l.html5Clone||n.isXMLDoc(a)||!ua.test("<"+a.nodeName+">")?f=a.cloneNode(!0):(Ba.innerHTML=a.outerHTML,Ba.removeChild(f=Ba.firstChild)),!(l.noCloneEvent&&l.noCloneChecked||1!==a.nodeType&&11!==a.nodeType||n.isXMLDoc(a)))for(d=ea(f),h=ea(a),g=0;null!=(e=h[g]);++g)d[g]&&Ga(e,d[g]);if(b)if(c)for(h=h||ea(a),d=d||ea(f),g=0;null!=(e=h[g]);g++)Fa(e,d[g]);else Fa(a,f);return d=ea(f,"script"),d.length>0&&fa(d,!i&&ea(a,"script")),d=h=e=null,f},cleanData:function(a,b){for(var d,e,f,g,h=0,i=n.expando,j=n.cache,k=l.attributes,m=n.event.special;null!=(d=a[h]);h++)if((b||M(d))&&(f=d[i],g=f&&j[f])){if(g.events)for(e in g.events)m[e]?n.event.remove(d,e):n.removeEvent(d,e,g.handle);j[f]&&(delete j[f],k||"undefined"==typeof d.removeAttribute?d[i]=void 0:d.removeAttribute(i),c.push(f))}}}),n.fn.extend({domManip:Ha,detach:function(a){return Ia(this,a,!0)},remove:function(a){return Ia(this,a)},text:function(a){return Y(this,function(a){return void 0===a?n.text(this):this.empty().append((this[0]&&this[0].ownerDocument||d).createTextNode(a))},null,a,arguments.length)},append:function(){return Ha(this,arguments,function(a){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var b=Ca(this,a);b.appendChild(a)}})},prepend:function(){return Ha(this,arguments,function(a){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var b=Ca(this,a);b.insertBefore(a,b.firstChild)}})},before:function(){return Ha(this,arguments,function(a){this.parentNode&&this.parentNode.insertBefore(a,this)})},after:function(){return Ha(this,arguments,function(a){this.parentNode&&this.parentNode.insertBefore(a,this.nextSibling)})},empty:function(){for(var a,b=0;null!=(a=this[b]);b++){1===a.nodeType&&n.cleanData(ea(a,!1));while(a.firstChild)a.removeChild(a.firstChild);a.options&&n.nodeName(a,"select")&&(a.options.length=0)}return this},clone:function(a,b){return a=null==a?!1:a,b=null==b?a:b,this.map(function(){return n.clone(this,a,b)})},html:function(a){return Y(this,function(a){var b=this[0]||{},c=0,d=this.length;if(void 0===a)return 1===b.nodeType?b.innerHTML.replace(ta,""):void 0;if("string"==typeof a&&!wa.test(a)&&(l.htmlSerialize||!ua.test(a))&&(l.leadingWhitespace||!aa.test(a))&&!da[($.exec(a)||["",""])[1].toLowerCase()]){a=n.htmlPrefilter(a);try{for(;d>c;c++)b=this[c]||{},1===b.nodeType&&(n.cleanData(ea(b,!1)),b.innerHTML=a);b=0}catch(e){}}b&&this.empty().append(a)},null,a,arguments.length)},replaceWith:function(){var a=[];return Ha(this,arguments,function(b){var c=this.parentNode;n.inArray(this,a)<0&&(n.cleanData(ea(this)),c&&c.replaceChild(b,this))},a)}}),n.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(a,b){n.fn[a]=function(a){for(var c,d=0,e=[],f=n(a),h=f.length-1;h>=d;d++)c=d===h?this:this.clone(!0),n(f[d])[b](c),g.apply(e,c.get());return this.pushStack(e)}});var Ja,Ka={HTML:"block",BODY:"block"};function La(a,b){var c=n(b.createElement(a)).appendTo(b.body),d=n.css(c[0],"display");return c.detach(),d}function Ma(a){var b=d,c=Ka[a];return c||(c=La(a,b),"none"!==c&&c||(Ja=(Ja||n("<iframe frameborder='0' width='0' height='0'/>")).appendTo(b.documentElement),b=(Ja[0].contentWindow||Ja[0].contentDocument).document,b.write(),b.close(),c=La(a,b),Ja.detach()),Ka[a]=c),c}var Na=/^margin/,Oa=new RegExp("^("+T+")(?!px)[a-z%]+$","i"),Pa=function(a,b,c,d){var e,f,g={};for(f in b)g[f]=a.style[f],a.style[f]=b[f];e=c.apply(a,d||[]);for(f in b)a.style[f]=g[f];return e},Qa=d.documentElement;!function(){var b,c,e,f,g,h,i=d.createElement("div"),j=d.createElement("div");if(j.style){j.style.cssText="float:left;opacity:.5",l.opacity="0.5"===j.style.opacity,l.cssFloat=!!j.style.cssFloat,j.style.backgroundClip="content-box",j.cloneNode(!0).style.backgroundClip="",l.clearCloneStyle="content-box"===j.style.backgroundClip,i=d.createElement("div"),i.style.cssText="border:0;width:8px;height:0;top:0;left:-9999px;padding:0;margin-top:1px;position:absolute",j.innerHTML="",i.appendChild(j),l.boxSizing=""===j.style.boxSizing||""===j.style.MozBoxSizing||""===j.style.WebkitBoxSizing,n.extend(l,{reliableHiddenOffsets:function(){return null==b&&k(),f},boxSizingReliable:function(){return null==b&&k(),e},pixelMarginRight:function(){return null==b&&k(),c},pixelPosition:function(){return null==b&&k(),b},reliableMarginRight:function(){return null==b&&k(),g},reliableMarginLeft:function(){return null==b&&k(),h}});function k(){var k,l,m=d.documentElement;m.appendChild(i),j.style.cssText="-webkit-box-sizing:border-box;box-sizing:border-box;position:relative;display:block;margin:auto;border:1px;padding:1px;top:1%;width:50%",b=e=h=!1,c=g=!0,a.getComputedStyle&&(l=a.getComputedStyle(j),b="1%"!==(l||{}).top,h="2px"===(l||{}).marginLeft,e="4px"===(l||{width:"4px"}).width,j.style.marginRight="50%",c="4px"===(l||{marginRight:"4px"}).marginRight,k=j.appendChild(d.createElement("div")),k.style.cssText=j.style.cssText="-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;display:block;margin:0;border:0;padding:0",k.style.marginRight=k.style.width="0",j.style.width="1px",g=!parseFloat((a.getComputedStyle(k)||{}).marginRight),j.removeChild(k)),j.style.display="none",f=0===j.getClientRects().length,f&&(j.style.display="",j.innerHTML="<table><tr><td></td><td>t</td></tr></table>",j.childNodes[0].style.borderCollapse="separate",k=j.getElementsByTagName("td"),k[0].style.cssText="margin:0;border:0;padding:0;display:none",f=0===k[0].offsetHeight,f&&(k[0].style.display="",k[1].style.display="none",f=0===k[0].offsetHeight)),m.removeChild(i)}}}();var Ra,Sa,Ta=/^(top|right|bottom|left)$/;a.getComputedStyle?(Ra=function(b){var c=b.ownerDocument.defaultView;return c&&c.opener||(c=a),c.getComputedStyle(b)},Sa=function(a,b,c){var d,e,f,g,h=a.style;return c=c||Ra(a),g=c?c.getPropertyValue(b)||c[b]:void 0,""!==g&&void 0!==g||n.contains(a.ownerDocument,a)||(g=n.style(a,b)),c&&!l.pixelMarginRight()&&Oa.test(g)&&Na.test(b)&&(d=h.width,e=h.minWidth,f=h.maxWidth,h.minWidth=h.maxWidth=h.width=g,g=c.width,h.width=d,h.minWidth=e,h.maxWidth=f),void 0===g?g:g+""}):Qa.currentStyle&&(Ra=function(a){return a.currentStyle},Sa=function(a,b,c){var d,e,f,g,h=a.style;return c=c||Ra(a),g=c?c[b]:void 0,null==g&&h&&h[b]&&(g=h[b]),Oa.test(g)&&!Ta.test(b)&&(d=h.left,e=a.runtimeStyle,f=e&&e.left,f&&(e.left=a.currentStyle.left),h.left="fontSize"===b?"1em":g,g=h.pixelLeft+"px",h.left=d,f&&(e.left=f)),void 0===g?g:g+""||"auto"});function Ua(a,b){return{get:function(){return a()?void delete this.get:(this.get=b).apply(this,arguments)}}}var Va=/alpha\([^)]*\)/i,Wa=/opacity\s*=\s*([^)]*)/i,Xa=/^(none|table(?!-c[ea]).+)/,Ya=new RegExp("^("+T+")(.*)$","i"),Za={position:"absolute",visibility:"hidden",display:"block"},$a={letterSpacing:"0",fontWeight:"400"},_a=["Webkit","O","Moz","ms"],ab=d.createElement("div").style;function bb(a){if(a in ab)return a;var b=a.charAt(0).toUpperCase()+a.slice(1),c=_a.length;while(c--)if(a=_a[c]+b,a in ab)return a}function cb(a,b){for(var c,d,e,f=[],g=0,h=a.length;h>g;g++)d=a[g],d.style&&(f[g]=n._data(d,"olddisplay"),c=d.style.display,b?(f[g]||"none"!==c||(d.style.display=""),""===d.style.display&&W(d)&&(f[g]=n._data(d,"olddisplay",Ma(d.nodeName)))):(e=W(d),(c&&"none"!==c||!e)&&n._data(d,"olddisplay",e?c:n.css(d,"display"))));for(g=0;h>g;g++)d=a[g],d.style&&(b&&"none"!==d.style.display&&""!==d.style.display||(d.style.display=b?f[g]||"":"none"));return a}function db(a,b,c){var d=Ya.exec(b);return d?Math.max(0,d[1]-(c||0))+(d[2]||"px"):b}function eb(a,b,c,d,e){for(var f=c===(d?"border":"content")?4:"width"===b?1:0,g=0;4>f;f+=2)"margin"===c&&(g+=n.css(a,c+V[f],!0,e)),d?("content"===c&&(g-=n.css(a,"padding"+V[f],!0,e)),"margin"!==c&&(g-=n.css(a,"border"+V[f]+"Width",!0,e))):(g+=n.css(a,"padding"+V[f],!0,e),"padding"!==c&&(g+=n.css(a,"border"+V[f]+"Width",!0,e)));return g}function fb(a,b,c){var d=!0,e="width"===b?a.offsetWidth:a.offsetHeight,f=Ra(a),g=l.boxSizing&&"border-box"===n.css(a,"boxSizing",!1,f);if(0>=e||null==e){if(e=Sa(a,b,f),(0>e||null==e)&&(e=a.style[b]),Oa.test(e))return e;d=g&&(l.boxSizingReliable()||e===a.style[b]),e=parseFloat(e)||0}return e+eb(a,b,c||(g?"border":"content"),d,f)+"px"}n.extend({cssHooks:{opacity:{get:function(a,b){if(b){var c=Sa(a,"opacity");return""===c?"1":c}}}},cssNumber:{animationIterationCount:!0,columnCount:!0,fillOpacity:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{"float":l.cssFloat?"cssFloat":"styleFloat"},style:function(a,b,c,d){if(a&&3!==a.nodeType&&8!==a.nodeType&&a.style){var e,f,g,h=n.camelCase(b),i=a.style;if(b=n.cssProps[h]||(n.cssProps[h]=bb(h)||h),g=n.cssHooks[b]||n.cssHooks[h],void 0===c)return g&&"get"in g&&void 0!==(e=g.get(a,!1,d))?e:i[b];if(f=typeof c,"string"===f&&(e=U.exec(c))&&e[1]&&(c=X(a,b,e),f="number"),null!=c&&c===c&&("number"===f&&(c+=e&&e[3]||(n.cssNumber[h]?"":"px")),l.clearCloneStyle||""!==c||0!==b.indexOf("background")||(i[b]="inherit"),!(g&&"set"in g&&void 0===(c=g.set(a,c,d)))))try{i[b]=c}catch(j){}}},css:function(a,b,c,d){var e,f,g,h=n.camelCase(b);return b=n.cssProps[h]||(n.cssProps[h]=bb(h)||h),g=n.cssHooks[b]||n.cssHooks[h],g&&"get"in g&&(f=g.get(a,!0,c)),void 0===f&&(f=Sa(a,b,d)),"normal"===f&&b in $a&&(f=$a[b]),""===c||c?(e=parseFloat(f),c===!0||isFinite(e)?e||0:f):f}}),n.each(["height","width"],function(a,b){n.cssHooks[b]={get:function(a,c,d){return c?Xa.test(n.css(a,"display"))&&0===a.offsetWidth?Pa(a,Za,function(){return fb(a,b,d)}):fb(a,b,d):void 0},set:function(a,c,d){var e=d&&Ra(a);return db(a,c,d?eb(a,b,d,l.boxSizing&&"border-box"===n.css(a,"boxSizing",!1,e),e):0)}}}),l.opacity||(n.cssHooks.opacity={get:function(a,b){return Wa.test((b&&a.currentStyle?a.currentStyle.filter:a.style.filter)||"")?.01*parseFloat(RegExp.$1)+"":b?"1":""},set:function(a,b){var c=a.style,d=a.currentStyle,e=n.isNumeric(b)?"alpha(opacity="+100*b+")":"",f=d&&d.filter||c.filter||"";c.zoom=1,(b>=1||""===b)&&""===n.trim(f.replace(Va,""))&&c.removeAttribute&&(c.removeAttribute("filter"),""===b||d&&!d.filter)||(c.filter=Va.test(f)?f.replace(Va,e):f+" "+e)}}),n.cssHooks.marginRight=Ua(l.reliableMarginRight,function(a,b){return b?Pa(a,{display:"inline-block"},Sa,[a,"marginRight"]):void 0}),n.cssHooks.marginLeft=Ua(l.reliableMarginLeft,function(a,b){return b?(parseFloat(Sa(a,"marginLeft"))||(n.contains(a.ownerDocument,a)?a.getBoundingClientRect().left-Pa(a,{
marginLeft:0},function(){return a.getBoundingClientRect().left}):0))+"px":void 0}),n.each({margin:"",padding:"",border:"Width"},function(a,b){n.cssHooks[a+b]={expand:function(c){for(var d=0,e={},f="string"==typeof c?c.split(" "):[c];4>d;d++)e[a+V[d]+b]=f[d]||f[d-2]||f[0];return e}},Na.test(a)||(n.cssHooks[a+b].set=db)}),n.fn.extend({css:function(a,b){return Y(this,function(a,b,c){var d,e,f={},g=0;if(n.isArray(b)){for(d=Ra(a),e=b.length;e>g;g++)f[b[g]]=n.css(a,b[g],!1,d);return f}return void 0!==c?n.style(a,b,c):n.css(a,b)},a,b,arguments.length>1)},show:function(){return cb(this,!0)},hide:function(){return cb(this)},toggle:function(a){return"boolean"==typeof a?a?this.show():this.hide():this.each(function(){W(this)?n(this).show():n(this).hide()})}});function gb(a,b,c,d,e){return new gb.prototype.init(a,b,c,d,e)}n.Tween=gb,gb.prototype={constructor:gb,init:function(a,b,c,d,e,f){this.elem=a,this.prop=c,this.easing=e||n.easing._default,this.options=b,this.start=this.now=this.cur(),this.end=d,this.unit=f||(n.cssNumber[c]?"":"px")},cur:function(){var a=gb.propHooks[this.prop];return a&&a.get?a.get(this):gb.propHooks._default.get(this)},run:function(a){var b,c=gb.propHooks[this.prop];return this.options.duration?this.pos=b=n.easing[this.easing](a,this.options.duration*a,0,1,this.options.duration):this.pos=b=a,this.now=(this.end-this.start)*b+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),c&&c.set?c.set(this):gb.propHooks._default.set(this),this}},gb.prototype.init.prototype=gb.prototype,gb.propHooks={_default:{get:function(a){var b;return 1!==a.elem.nodeType||null!=a.elem[a.prop]&&null==a.elem.style[a.prop]?a.elem[a.prop]:(b=n.css(a.elem,a.prop,""),b&&"auto"!==b?b:0)},set:function(a){n.fx.step[a.prop]?n.fx.step[a.prop](a):1!==a.elem.nodeType||null==a.elem.style[n.cssProps[a.prop]]&&!n.cssHooks[a.prop]?a.elem[a.prop]=a.now:n.style(a.elem,a.prop,a.now+a.unit)}}},gb.propHooks.scrollTop=gb.propHooks.scrollLeft={set:function(a){a.elem.nodeType&&a.elem.parentNode&&(a.elem[a.prop]=a.now)}},n.easing={linear:function(a){return a},swing:function(a){return.5-Math.cos(a*Math.PI)/2},_default:"swing"},n.fx=gb.prototype.init,n.fx.step={};var hb,ib,jb=/^(?:toggle|show|hide)$/,kb=/queueHooks$/;function lb(){return a.setTimeout(function(){hb=void 0}),hb=n.now()}function mb(a,b){var c,d={height:a},e=0;for(b=b?1:0;4>e;e+=2-b)c=V[e],d["margin"+c]=d["padding"+c]=a;return b&&(d.opacity=d.width=a),d}function nb(a,b,c){for(var d,e=(qb.tweeners[b]||[]).concat(qb.tweeners["*"]),f=0,g=e.length;g>f;f++)if(d=e[f].call(c,b,a))return d}function ob(a,b,c){var d,e,f,g,h,i,j,k,m=this,o={},p=a.style,q=a.nodeType&&W(a),r=n._data(a,"fxshow");c.queue||(h=n._queueHooks(a,"fx"),null==h.unqueued&&(h.unqueued=0,i=h.empty.fire,h.empty.fire=function(){h.unqueued||i()}),h.unqueued++,m.always(function(){m.always(function(){h.unqueued--,n.queue(a,"fx").length||h.empty.fire()})})),1===a.nodeType&&("height"in b||"width"in b)&&(c.overflow=[p.overflow,p.overflowX,p.overflowY],j=n.css(a,"display"),k="none"===j?n._data(a,"olddisplay")||Ma(a.nodeName):j,"inline"===k&&"none"===n.css(a,"float")&&(l.inlineBlockNeedsLayout&&"inline"!==Ma(a.nodeName)?p.zoom=1:p.display="inline-block")),c.overflow&&(p.overflow="hidden",l.shrinkWrapBlocks()||m.always(function(){p.overflow=c.overflow[0],p.overflowX=c.overflow[1],p.overflowY=c.overflow[2]}));for(d in b)if(e=b[d],jb.exec(e)){if(delete b[d],f=f||"toggle"===e,e===(q?"hide":"show")){if("show"!==e||!r||void 0===r[d])continue;q=!0}o[d]=r&&r[d]||n.style(a,d)}else j=void 0;if(n.isEmptyObject(o))"inline"===("none"===j?Ma(a.nodeName):j)&&(p.display=j);else{r?"hidden"in r&&(q=r.hidden):r=n._data(a,"fxshow",{}),f&&(r.hidden=!q),q?n(a).show():m.done(function(){n(a).hide()}),m.done(function(){var b;n._removeData(a,"fxshow");for(b in o)n.style(a,b,o[b])});for(d in o)g=nb(q?r[d]:0,d,m),d in r||(r[d]=g.start,q&&(g.end=g.start,g.start="width"===d||"height"===d?1:0))}}function pb(a,b){var c,d,e,f,g;for(c in a)if(d=n.camelCase(c),e=b[d],f=a[c],n.isArray(f)&&(e=f[1],f=a[c]=f[0]),c!==d&&(a[d]=f,delete a[c]),g=n.cssHooks[d],g&&"expand"in g){f=g.expand(f),delete a[d];for(c in f)c in a||(a[c]=f[c],b[c]=e)}else b[d]=e}function qb(a,b,c){var d,e,f=0,g=qb.prefilters.length,h=n.Deferred().always(function(){delete i.elem}),i=function(){if(e)return!1;for(var b=hb||lb(),c=Math.max(0,j.startTime+j.duration-b),d=c/j.duration||0,f=1-d,g=0,i=j.tweens.length;i>g;g++)j.tweens[g].run(f);return h.notifyWith(a,[j,f,c]),1>f&&i?c:(h.resolveWith(a,[j]),!1)},j=h.promise({elem:a,props:n.extend({},b),opts:n.extend(!0,{specialEasing:{},easing:n.easing._default},c),originalProperties:b,originalOptions:c,startTime:hb||lb(),duration:c.duration,tweens:[],createTween:function(b,c){var d=n.Tween(a,j.opts,b,c,j.opts.specialEasing[b]||j.opts.easing);return j.tweens.push(d),d},stop:function(b){var c=0,d=b?j.tweens.length:0;if(e)return this;for(e=!0;d>c;c++)j.tweens[c].run(1);return b?(h.notifyWith(a,[j,1,0]),h.resolveWith(a,[j,b])):h.rejectWith(a,[j,b]),this}}),k=j.props;for(pb(k,j.opts.specialEasing);g>f;f++)if(d=qb.prefilters[f].call(j,a,k,j.opts))return n.isFunction(d.stop)&&(n._queueHooks(j.elem,j.opts.queue).stop=n.proxy(d.stop,d)),d;return n.map(k,nb,j),n.isFunction(j.opts.start)&&j.opts.start.call(a,j),n.fx.timer(n.extend(i,{elem:a,anim:j,queue:j.opts.queue})),j.progress(j.opts.progress).done(j.opts.done,j.opts.complete).fail(j.opts.fail).always(j.opts.always)}n.Animation=n.extend(qb,{tweeners:{"*":[function(a,b){var c=this.createTween(a,b);return X(c.elem,a,U.exec(b),c),c}]},tweener:function(a,b){n.isFunction(a)?(b=a,a=["*"]):a=a.match(G);for(var c,d=0,e=a.length;e>d;d++)c=a[d],qb.tweeners[c]=qb.tweeners[c]||[],qb.tweeners[c].unshift(b)},prefilters:[ob],prefilter:function(a,b){b?qb.prefilters.unshift(a):qb.prefilters.push(a)}}),n.speed=function(a,b,c){var d=a&&"object"==typeof a?n.extend({},a):{complete:c||!c&&b||n.isFunction(a)&&a,duration:a,easing:c&&b||b&&!n.isFunction(b)&&b};return d.duration=n.fx.off?0:"number"==typeof d.duration?d.duration:d.duration in n.fx.speeds?n.fx.speeds[d.duration]:n.fx.speeds._default,null!=d.queue&&d.queue!==!0||(d.queue="fx"),d.old=d.complete,d.complete=function(){n.isFunction(d.old)&&d.old.call(this),d.queue&&n.dequeue(this,d.queue)},d},n.fn.extend({fadeTo:function(a,b,c,d){return this.filter(W).css("opacity",0).show().end().animate({opacity:b},a,c,d)},animate:function(a,b,c,d){var e=n.isEmptyObject(a),f=n.speed(b,c,d),g=function(){var b=qb(this,n.extend({},a),f);(e||n._data(this,"finish"))&&b.stop(!0)};return g.finish=g,e||f.queue===!1?this.each(g):this.queue(f.queue,g)},stop:function(a,b,c){var d=function(a){var b=a.stop;delete a.stop,b(c)};return"string"!=typeof a&&(c=b,b=a,a=void 0),b&&a!==!1&&this.queue(a||"fx",[]),this.each(function(){var b=!0,e=null!=a&&a+"queueHooks",f=n.timers,g=n._data(this);if(e)g[e]&&g[e].stop&&d(g[e]);else for(e in g)g[e]&&g[e].stop&&kb.test(e)&&d(g[e]);for(e=f.length;e--;)f[e].elem!==this||null!=a&&f[e].queue!==a||(f[e].anim.stop(c),b=!1,f.splice(e,1));!b&&c||n.dequeue(this,a)})},finish:function(a){return a!==!1&&(a=a||"fx"),this.each(function(){var b,c=n._data(this),d=c[a+"queue"],e=c[a+"queueHooks"],f=n.timers,g=d?d.length:0;for(c.finish=!0,n.queue(this,a,[]),e&&e.stop&&e.stop.call(this,!0),b=f.length;b--;)f[b].elem===this&&f[b].queue===a&&(f[b].anim.stop(!0),f.splice(b,1));for(b=0;g>b;b++)d[b]&&d[b].finish&&d[b].finish.call(this);delete c.finish})}}),n.each(["toggle","show","hide"],function(a,b){var c=n.fn[b];n.fn[b]=function(a,d,e){return null==a||"boolean"==typeof a?c.apply(this,arguments):this.animate(mb(b,!0),a,d,e)}}),n.each({slideDown:mb("show"),slideUp:mb("hide"),slideToggle:mb("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(a,b){n.fn[a]=function(a,c,d){return this.animate(b,a,c,d)}}),n.timers=[],n.fx.tick=function(){var a,b=n.timers,c=0;for(hb=n.now();c<b.length;c++)a=b[c],a()||b[c]!==a||b.splice(c--,1);b.length||n.fx.stop(),hb=void 0},n.fx.timer=function(a){n.timers.push(a),a()?n.fx.start():n.timers.pop()},n.fx.interval=13,n.fx.start=function(){ib||(ib=a.setInterval(n.fx.tick,n.fx.interval))},n.fx.stop=function(){a.clearInterval(ib),ib=null},n.fx.speeds={slow:600,fast:200,_default:400},n.fn.delay=function(b,c){return b=n.fx?n.fx.speeds[b]||b:b,c=c||"fx",this.queue(c,function(c,d){var e=a.setTimeout(c,b);d.stop=function(){a.clearTimeout(e)}})},function(){var a,b=d.createElement("input"),c=d.createElement("div"),e=d.createElement("select"),f=e.appendChild(d.createElement("option"));c=d.createElement("div"),c.setAttribute("className","t"),c.innerHTML="  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>",a=c.getElementsByTagName("a")[0],b.setAttribute("type","checkbox"),c.appendChild(b),a=c.getElementsByTagName("a")[0],a.style.cssText="top:1px",l.getSetAttribute="t"!==c.className,l.style=/top/.test(a.getAttribute("style")),l.hrefNormalized="/a"===a.getAttribute("href"),l.checkOn=!!b.value,l.optSelected=f.selected,l.enctype=!!d.createElement("form").enctype,e.disabled=!0,l.optDisabled=!f.disabled,b=d.createElement("input"),b.setAttribute("value",""),l.input=""===b.getAttribute("value"),b.value="t",b.setAttribute("type","radio"),l.radioValue="t"===b.value}();var rb=/\r/g,sb=/[\x20\t\r\n\f]+/g;n.fn.extend({val:function(a){var b,c,d,e=this[0];{if(arguments.length)return d=n.isFunction(a),this.each(function(c){var e;1===this.nodeType&&(e=d?a.call(this,c,n(this).val()):a,null==e?e="":"number"==typeof e?e+="":n.isArray(e)&&(e=n.map(e,function(a){return null==a?"":a+""})),b=n.valHooks[this.type]||n.valHooks[this.nodeName.toLowerCase()],b&&"set"in b&&void 0!==b.set(this,e,"value")||(this.value=e))});if(e)return b=n.valHooks[e.type]||n.valHooks[e.nodeName.toLowerCase()],b&&"get"in b&&void 0!==(c=b.get(e,"value"))?c:(c=e.value,"string"==typeof c?c.replace(rb,""):null==c?"":c)}}}),n.extend({valHooks:{option:{get:function(a){var b=n.find.attr(a,"value");return null!=b?b:n.trim(n.text(a)).replace(sb," ")}},select:{get:function(a){for(var b,c,d=a.options,e=a.selectedIndex,f="select-one"===a.type||0>e,g=f?null:[],h=f?e+1:d.length,i=0>e?h:f?e:0;h>i;i++)if(c=d[i],(c.selected||i===e)&&(l.optDisabled?!c.disabled:null===c.getAttribute("disabled"))&&(!c.parentNode.disabled||!n.nodeName(c.parentNode,"optgroup"))){if(b=n(c).val(),f)return b;g.push(b)}return g},set:function(a,b){var c,d,e=a.options,f=n.makeArray(b),g=e.length;while(g--)if(d=e[g],n.inArray(n.valHooks.option.get(d),f)>-1)try{d.selected=c=!0}catch(h){d.scrollHeight}else d.selected=!1;return c||(a.selectedIndex=-1),e}}}}),n.each(["radio","checkbox"],function(){n.valHooks[this]={set:function(a,b){return n.isArray(b)?a.checked=n.inArray(n(a).val(),b)>-1:void 0}},l.checkOn||(n.valHooks[this].get=function(a){return null===a.getAttribute("value")?"on":a.value})});var tb,ub,vb=n.expr.attrHandle,wb=/^(?:checked|selected)$/i,xb=l.getSetAttribute,yb=l.input;n.fn.extend({attr:function(a,b){return Y(this,n.attr,a,b,arguments.length>1)},removeAttr:function(a){return this.each(function(){n.removeAttr(this,a)})}}),n.extend({attr:function(a,b,c){var d,e,f=a.nodeType;if(3!==f&&8!==f&&2!==f)return"undefined"==typeof a.getAttribute?n.prop(a,b,c):(1===f&&n.isXMLDoc(a)||(b=b.toLowerCase(),e=n.attrHooks[b]||(n.expr.match.bool.test(b)?ub:tb)),void 0!==c?null===c?void n.removeAttr(a,b):e&&"set"in e&&void 0!==(d=e.set(a,c,b))?d:(a.setAttribute(b,c+""),c):e&&"get"in e&&null!==(d=e.get(a,b))?d:(d=n.find.attr(a,b),null==d?void 0:d))},attrHooks:{type:{set:function(a,b){if(!l.radioValue&&"radio"===b&&n.nodeName(a,"input")){var c=a.value;return a.setAttribute("type",b),c&&(a.value=c),b}}}},removeAttr:function(a,b){var c,d,e=0,f=b&&b.match(G);if(f&&1===a.nodeType)while(c=f[e++])d=n.propFix[c]||c,n.expr.match.bool.test(c)?yb&&xb||!wb.test(c)?a[d]=!1:a[n.camelCase("default-"+c)]=a[d]=!1:n.attr(a,c,""),a.removeAttribute(xb?c:d)}}),ub={set:function(a,b,c){return b===!1?n.removeAttr(a,c):yb&&xb||!wb.test(c)?a.setAttribute(!xb&&n.propFix[c]||c,c):a[n.camelCase("default-"+c)]=a[c]=!0,c}},n.each(n.expr.match.bool.source.match(/\w+/g),function(a,b){var c=vb[b]||n.find.attr;yb&&xb||!wb.test(b)?vb[b]=function(a,b,d){var e,f;return d||(f=vb[b],vb[b]=e,e=null!=c(a,b,d)?b.toLowerCase():null,vb[b]=f),e}:vb[b]=function(a,b,c){return c?void 0:a[n.camelCase("default-"+b)]?b.toLowerCase():null}}),yb&&xb||(n.attrHooks.value={set:function(a,b,c){return n.nodeName(a,"input")?void(a.defaultValue=b):tb&&tb.set(a,b,c)}}),xb||(tb={set:function(a,b,c){var d=a.getAttributeNode(c);return d||a.setAttributeNode(d=a.ownerDocument.createAttribute(c)),d.value=b+="","value"===c||b===a.getAttribute(c)?b:void 0}},vb.id=vb.name=vb.coords=function(a,b,c){var d;return c?void 0:(d=a.getAttributeNode(b))&&""!==d.value?d.value:null},n.valHooks.button={get:function(a,b){var c=a.getAttributeNode(b);return c&&c.specified?c.value:void 0},set:tb.set},n.attrHooks.contenteditable={set:function(a,b,c){tb.set(a,""===b?!1:b,c)}},n.each(["width","height"],function(a,b){n.attrHooks[b]={set:function(a,c){return""===c?(a.setAttribute(b,"auto"),c):void 0}}})),l.style||(n.attrHooks.style={get:function(a){return a.style.cssText||void 0},set:function(a,b){return a.style.cssText=b+""}});var zb=/^(?:input|select|textarea|button|object)$/i,Ab=/^(?:a|area)$/i;n.fn.extend({prop:function(a,b){return Y(this,n.prop,a,b,arguments.length>1)},removeProp:function(a){return a=n.propFix[a]||a,this.each(function(){try{this[a]=void 0,delete this[a]}catch(b){}})}}),n.extend({prop:function(a,b,c){var d,e,f=a.nodeType;if(3!==f&&8!==f&&2!==f)return 1===f&&n.isXMLDoc(a)||(b=n.propFix[b]||b,e=n.propHooks[b]),void 0!==c?e&&"set"in e&&void 0!==(d=e.set(a,c,b))?d:a[b]=c:e&&"get"in e&&null!==(d=e.get(a,b))?d:a[b]},propHooks:{tabIndex:{get:function(a){var b=n.find.attr(a,"tabindex");return b?parseInt(b,10):zb.test(a.nodeName)||Ab.test(a.nodeName)&&a.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),l.hrefNormalized||n.each(["href","src"],function(a,b){n.propHooks[b]={get:function(a){return a.getAttribute(b,4)}}}),l.optSelected||(n.propHooks.selected={get:function(a){var b=a.parentNode;return b&&(b.selectedIndex,b.parentNode&&b.parentNode.selectedIndex),null},set:function(a){var b=a.parentNode;b&&(b.selectedIndex,b.parentNode&&b.parentNode.selectedIndex)}}),n.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){n.propFix[this.toLowerCase()]=this}),l.enctype||(n.propFix.enctype="encoding");var Bb=/[\t\r\n\f]/g;function Cb(a){return n.attr(a,"class")||""}n.fn.extend({addClass:function(a){var b,c,d,e,f,g,h,i=0;if(n.isFunction(a))return this.each(function(b){n(this).addClass(a.call(this,b,Cb(this)))});if("string"==typeof a&&a){b=a.match(G)||[];while(c=this[i++])if(e=Cb(c),d=1===c.nodeType&&(" "+e+" ").replace(Bb," ")){g=0;while(f=b[g++])d.indexOf(" "+f+" ")<0&&(d+=f+" ");h=n.trim(d),e!==h&&n.attr(c,"class",h)}}return this},removeClass:function(a){var b,c,d,e,f,g,h,i=0;if(n.isFunction(a))return this.each(function(b){n(this).removeClass(a.call(this,b,Cb(this)))});if(!arguments.length)return this.attr("class","");if("string"==typeof a&&a){b=a.match(G)||[];while(c=this[i++])if(e=Cb(c),d=1===c.nodeType&&(" "+e+" ").replace(Bb," ")){g=0;while(f=b[g++])while(d.indexOf(" "+f+" ")>-1)d=d.replace(" "+f+" "," ");h=n.trim(d),e!==h&&n.attr(c,"class",h)}}return this},toggleClass:function(a,b){var c=typeof a;return"boolean"==typeof b&&"string"===c?b?this.addClass(a):this.removeClass(a):n.isFunction(a)?this.each(function(c){n(this).toggleClass(a.call(this,c,Cb(this),b),b)}):this.each(function(){var b,d,e,f;if("string"===c){d=0,e=n(this),f=a.match(G)||[];while(b=f[d++])e.hasClass(b)?e.removeClass(b):e.addClass(b)}else void 0!==a&&"boolean"!==c||(b=Cb(this),b&&n._data(this,"__className__",b),n.attr(this,"class",b||a===!1?"":n._data(this,"__className__")||""))})},hasClass:function(a){var b,c,d=0;b=" "+a+" ";while(c=this[d++])if(1===c.nodeType&&(" "+Cb(c)+" ").replace(Bb," ").indexOf(b)>-1)return!0;return!1}}),n.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "),function(a,b){n.fn[b]=function(a,c){return arguments.length>0?this.on(b,null,a,c):this.trigger(b)}}),n.fn.extend({hover:function(a,b){return this.mouseenter(a).mouseleave(b||a)}});var Db=a.location,Eb=n.now(),Fb=/\?/,Gb=/(,)|(\[|{)|(}|])|"(?:[^"\\\r\n]|\\["\\\/bfnrt]|\\u[\da-fA-F]{4})*"\s*:?|true|false|null|-?(?!0\d)\d+(?:\.\d+|)(?:[eE][+-]?\d+|)/g;n.parseJSON=function(b){if(a.JSON&&a.JSON.parse)return a.JSON.parse(b+"");var c,d=null,e=n.trim(b+"");return e&&!n.trim(e.replace(Gb,function(a,b,e,f){return c&&b&&(d=0),0===d?a:(c=e||b,d+=!f-!e,"")}))?Function("return "+e)():n.error("Invalid JSON: "+b)},n.parseXML=function(b){var c,d;if(!b||"string"!=typeof b)return null;try{a.DOMParser?(d=new a.DOMParser,c=d.parseFromString(b,"text/xml")):(c=new a.ActiveXObject("Microsoft.XMLDOM"),c.async="false",c.loadXML(b))}catch(e){c=void 0}return c&&c.documentElement&&!c.getElementsByTagName("parsererror").length||n.error("Invalid XML: "+b),c};var Hb=/#.*$/,Ib=/([?&])_=[^&]*/,Jb=/^(.*?):[ \t]*([^\r\n]*)\r?$/gm,Kb=/^(?:about|app|app-storage|.+-extension|file|res|widget):$/,Lb=/^(?:GET|HEAD)$/,Mb=/^\/\//,Nb=/^([\w.+-]+:)(?:\/\/(?:[^\/?#]*@|)([^\/?#:]*)(?::(\d+)|)|)/,Ob={},Pb={},Qb="*/".concat("*"),Rb=Db.href,Sb=Nb.exec(Rb.toLowerCase())||[];function Tb(a){return function(b,c){"string"!=typeof b&&(c=b,b="*");var d,e=0,f=b.toLowerCase().match(G)||[];if(n.isFunction(c))while(d=f[e++])"+"===d.charAt(0)?(d=d.slice(1)||"*",(a[d]=a[d]||[]).unshift(c)):(a[d]=a[d]||[]).push(c)}}function Ub(a,b,c,d){var e={},f=a===Pb;function g(h){var i;return e[h]=!0,n.each(a[h]||[],function(a,h){var j=h(b,c,d);return"string"!=typeof j||f||e[j]?f?!(i=j):void 0:(b.dataTypes.unshift(j),g(j),!1)}),i}return g(b.dataTypes[0])||!e["*"]&&g("*")}function Vb(a,b){var c,d,e=n.ajaxSettings.flatOptions||{};for(d in b)void 0!==b[d]&&((e[d]?a:c||(c={}))[d]=b[d]);return c&&n.extend(!0,a,c),a}function Wb(a,b,c){var d,e,f,g,h=a.contents,i=a.dataTypes;while("*"===i[0])i.shift(),void 0===e&&(e=a.mimeType||b.getResponseHeader("Content-Type"));if(e)for(g in h)if(h[g]&&h[g].test(e)){i.unshift(g);break}if(i[0]in c)f=i[0];else{for(g in c){if(!i[0]||a.converters[g+" "+i[0]]){f=g;break}d||(d=g)}f=f||d}return f?(f!==i[0]&&i.unshift(f),c[f]):void 0}function Xb(a,b,c,d){var e,f,g,h,i,j={},k=a.dataTypes.slice();if(k[1])for(g in a.converters)j[g.toLowerCase()]=a.converters[g];f=k.shift();while(f)if(a.responseFields[f]&&(c[a.responseFields[f]]=b),!i&&d&&a.dataFilter&&(b=a.dataFilter(b,a.dataType)),i=f,f=k.shift())if("*"===f)f=i;else if("*"!==i&&i!==f){if(g=j[i+" "+f]||j["* "+f],!g)for(e in j)if(h=e.split(" "),h[1]===f&&(g=j[i+" "+h[0]]||j["* "+h[0]])){g===!0?g=j[e]:j[e]!==!0&&(f=h[0],k.unshift(h[1]));break}if(g!==!0)if(g&&a["throws"])b=g(b);else try{b=g(b)}catch(l){return{state:"parsererror",error:g?l:"No conversion from "+i+" to "+f}}}return{state:"success",data:b}}n.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Rb,type:"GET",isLocal:Kb.test(Sb[1]),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":Qb,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":n.parseJSON,"text xml":n.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(a,b){return b?Vb(Vb(a,n.ajaxSettings),b):Vb(n.ajaxSettings,a)},ajaxPrefilter:Tb(Ob),ajaxTransport:Tb(Pb),ajax:function(b,c){"object"==typeof b&&(c=b,b=void 0),c=c||{};var d,e,f,g,h,i,j,k,l=n.ajaxSetup({},c),m=l.context||l,o=l.context&&(m.nodeType||m.jquery)?n(m):n.event,p=n.Deferred(),q=n.Callbacks("once memory"),r=l.statusCode||{},s={},t={},u=0,v="canceled",w={readyState:0,getResponseHeader:function(a){var b;if(2===u){if(!k){k={};while(b=Jb.exec(g))k[b[1].toLowerCase()]=b[2]}b=k[a.toLowerCase()]}return null==b?null:b},getAllResponseHeaders:function(){return 2===u?g:null},setRequestHeader:function(a,b){var c=a.toLowerCase();return u||(a=t[c]=t[c]||a,s[a]=b),this},overrideMimeType:function(a){return u||(l.mimeType=a),this},statusCode:function(a){var b;if(a)if(2>u)for(b in a)r[b]=[r[b],a[b]];else w.always(a[w.status]);return this},abort:function(a){var b=a||v;return j&&j.abort(b),y(0,b),this}};if(p.promise(w).complete=q.add,w.success=w.done,w.error=w.fail,l.url=((b||l.url||Rb)+"").replace(Hb,"").replace(Mb,Sb[1]+"//"),l.type=c.method||c.type||l.method||l.type,l.dataTypes=n.trim(l.dataType||"*").toLowerCase().match(G)||[""],null==l.crossDomain&&(d=Nb.exec(l.url.toLowerCase()),l.crossDomain=!(!d||d[1]===Sb[1]&&d[2]===Sb[2]&&(d[3]||("http:"===d[1]?"80":"443"))===(Sb[3]||("http:"===Sb[1]?"80":"443")))),l.data&&l.processData&&"string"!=typeof l.data&&(l.data=n.param(l.data,l.traditional)),Ub(Ob,l,c,w),2===u)return w;i=n.event&&l.global,i&&0===n.active++&&n.event.trigger("ajaxStart"),l.type=l.type.toUpperCase(),l.hasContent=!Lb.test(l.type),f=l.url,l.hasContent||(l.data&&(f=l.url+=(Fb.test(f)?"&":"?")+l.data,delete l.data),l.cache===!1&&(l.url=Ib.test(f)?f.replace(Ib,"$1_="+Eb++):f+(Fb.test(f)?"&":"?")+"_="+Eb++)),l.ifModified&&(n.lastModified[f]&&w.setRequestHeader("If-Modified-Since",n.lastModified[f]),n.etag[f]&&w.setRequestHeader("If-None-Match",n.etag[f])),(l.data&&l.hasContent&&l.contentType!==!1||c.contentType)&&w.setRequestHeader("Content-Type",l.contentType),w.setRequestHeader("Accept",l.dataTypes[0]&&l.accepts[l.dataTypes[0]]?l.accepts[l.dataTypes[0]]+("*"!==l.dataTypes[0]?", "+Qb+"; q=0.01":""):l.accepts["*"]);for(e in l.headers)w.setRequestHeader(e,l.headers[e]);if(l.beforeSend&&(l.beforeSend.call(m,w,l)===!1||2===u))return w.abort();v="abort";for(e in{success:1,error:1,complete:1})w[e](l[e]);if(j=Ub(Pb,l,c,w)){if(w.readyState=1,i&&o.trigger("ajaxSend",[w,l]),2===u)return w;l.async&&l.timeout>0&&(h=a.setTimeout(function(){w.abort("timeout")},l.timeout));try{u=1,j.send(s,y)}catch(x){if(!(2>u))throw x;y(-1,x)}}else y(-1,"No Transport");function y(b,c,d,e){var k,s,t,v,x,y=c;2!==u&&(u=2,h&&a.clearTimeout(h),j=void 0,g=e||"",w.readyState=b>0?4:0,k=b>=200&&300>b||304===b,d&&(v=Wb(l,w,d)),v=Xb(l,v,w,k),k?(l.ifModified&&(x=w.getResponseHeader("Last-Modified"),x&&(n.lastModified[f]=x),x=w.getResponseHeader("etag"),x&&(n.etag[f]=x)),204===b||"HEAD"===l.type?y="nocontent":304===b?y="notmodified":(y=v.state,s=v.data,t=v.error,k=!t)):(t=y,!b&&y||(y="error",0>b&&(b=0))),w.status=b,w.statusText=(c||y)+"",k?p.resolveWith(m,[s,y,w]):p.rejectWith(m,[w,y,t]),w.statusCode(r),r=void 0,i&&o.trigger(k?"ajaxSuccess":"ajaxError",[w,l,k?s:t]),q.fireWith(m,[w,y]),i&&(o.trigger("ajaxComplete",[w,l]),--n.active||n.event.trigger("ajaxStop")))}return w},getJSON:function(a,b,c){return n.get(a,b,c,"json")},getScript:function(a,b){return n.get(a,void 0,b,"script")}}),n.each(["get","post"],function(a,b){n[b]=function(a,c,d,e){return n.isFunction(c)&&(e=e||d,d=c,c=void 0),n.ajax(n.extend({url:a,type:b,dataType:e,data:c,success:d},n.isPlainObject(a)&&a))}}),n._evalUrl=function(a){return n.ajax({url:a,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,"throws":!0})},n.fn.extend({wrapAll:function(a){if(n.isFunction(a))return this.each(function(b){n(this).wrapAll(a.call(this,b))});if(this[0]){var b=n(a,this[0].ownerDocument).eq(0).clone(!0);this[0].parentNode&&b.insertBefore(this[0]),b.map(function(){var a=this;while(a.firstChild&&1===a.firstChild.nodeType)a=a.firstChild;return a}).append(this)}return this},wrapInner:function(a){return n.isFunction(a)?this.each(function(b){n(this).wrapInner(a.call(this,b))}):this.each(function(){var b=n(this),c=b.contents();c.length?c.wrapAll(a):b.append(a)})},wrap:function(a){var b=n.isFunction(a);return this.each(function(c){n(this).wrapAll(b?a.call(this,c):a)})},unwrap:function(){return this.parent().each(function(){n.nodeName(this,"body")||n(this).replaceWith(this.childNodes)}).end()}});function Yb(a){return a.style&&a.style.display||n.css(a,"display")}function Zb(a){if(!n.contains(a.ownerDocument||d,a))return!0;while(a&&1===a.nodeType){if("none"===Yb(a)||"hidden"===a.type)return!0;a=a.parentNode}return!1}n.expr.filters.hidden=function(a){return l.reliableHiddenOffsets()?a.offsetWidth<=0&&a.offsetHeight<=0&&!a.getClientRects().length:Zb(a)},n.expr.filters.visible=function(a){return!n.expr.filters.hidden(a)};var $b=/%20/g,_b=/\[\]$/,ac=/\r?\n/g,bc=/^(?:submit|button|image|reset|file)$/i,cc=/^(?:input|select|textarea|keygen)/i;function dc(a,b,c,d){var e;if(n.isArray(b))n.each(b,function(b,e){c||_b.test(a)?d(a,e):dc(a+"["+("object"==typeof e&&null!=e?b:"")+"]",e,c,d)});else if(c||"object"!==n.type(b))d(a,b);else for(e in b)dc(a+"["+e+"]",b[e],c,d)}n.param=function(a,b){var c,d=[],e=function(a,b){b=n.isFunction(b)?b():null==b?"":b,d[d.length]=encodeURIComponent(a)+"="+encodeURIComponent(b)};if(void 0===b&&(b=n.ajaxSettings&&n.ajaxSettings.traditional),n.isArray(a)||a.jquery&&!n.isPlainObject(a))n.each(a,function(){e(this.name,this.value)});else for(c in a)dc(c,a[c],b,e);return d.join("&").replace($b,"+")},n.fn.extend({serialize:function(){return n.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var a=n.prop(this,"elements");return a?n.makeArray(a):this}).filter(function(){var a=this.type;return this.name&&!n(this).is(":disabled")&&cc.test(this.nodeName)&&!bc.test(a)&&(this.checked||!Z.test(a))}).map(function(a,b){var c=n(this).val();return null==c?null:n.isArray(c)?n.map(c,function(a){return{name:b.name,value:a.replace(ac,"\r\n")}}):{name:b.name,value:c.replace(ac,"\r\n")}}).get()}}),n.ajaxSettings.xhr=void 0!==a.ActiveXObject?function(){return this.isLocal?ic():d.documentMode>8?hc():/^(get|post|head|put|delete|options)$/i.test(this.type)&&hc()||ic()}:hc;var ec=0,fc={},gc=n.ajaxSettings.xhr();a.attachEvent&&a.attachEvent("onunload",function(){for(var a in fc)fc[a](void 0,!0)}),l.cors=!!gc&&"withCredentials"in gc,gc=l.ajax=!!gc,gc&&n.ajaxTransport(function(b){if(!b.crossDomain||l.cors){var c;return{send:function(d,e){var f,g=b.xhr(),h=++ec;if(g.open(b.type,b.url,b.async,b.username,b.password),b.xhrFields)for(f in b.xhrFields)g[f]=b.xhrFields[f];b.mimeType&&g.overrideMimeType&&g.overrideMimeType(b.mimeType),b.crossDomain||d["X-Requested-With"]||(d["X-Requested-With"]="XMLHttpRequest");for(f in d)void 0!==d[f]&&g.setRequestHeader(f,d[f]+"");g.send(b.hasContent&&b.data||null),c=function(a,d){var f,i,j;if(c&&(d||4===g.readyState))if(delete fc[h],c=void 0,g.onreadystatechange=n.noop,d)4!==g.readyState&&g.abort();else{j={},f=g.status,"string"==typeof g.responseText&&(j.text=g.responseText);try{i=g.statusText}catch(k){i=""}f||!b.isLocal||b.crossDomain?1223===f&&(f=204):f=j.text?200:404}j&&e(f,i,j,g.getAllResponseHeaders())},b.async?4===g.readyState?a.setTimeout(c):g.onreadystatechange=fc[h]=c:c()},abort:function(){c&&c(void 0,!0)}}}});function hc(){try{return new a.XMLHttpRequest}catch(b){}}function ic(){try{return new a.ActiveXObject("Microsoft.XMLHTTP")}catch(b){}}n.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(a){return n.globalEval(a),a}}}),n.ajaxPrefilter("script",function(a){void 0===a.cache&&(a.cache=!1),a.crossDomain&&(a.type="GET",a.global=!1)}),n.ajaxTransport("script",function(a){if(a.crossDomain){var b,c=d.head||n("head")[0]||d.documentElement;return{send:function(e,f){b=d.createElement("script"),b.async=!0,a.scriptCharset&&(b.charset=a.scriptCharset),b.src=a.url,b.onload=b.onreadystatechange=function(a,c){(c||!b.readyState||/loaded|complete/.test(b.readyState))&&(b.onload=b.onreadystatechange=null,b.parentNode&&b.parentNode.removeChild(b),b=null,c||f(200,"success"))},c.insertBefore(b,c.firstChild)},abort:function(){b&&b.onload(void 0,!0)}}}});var jc=[],kc=/(=)\?(?=&|$)|\?\?/;n.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var a=jc.pop()||n.expando+"_"+Eb++;return this[a]=!0,a}}),n.ajaxPrefilter("json jsonp",function(b,c,d){var e,f,g,h=b.jsonp!==!1&&(kc.test(b.url)?"url":"string"==typeof b.data&&0===(b.contentType||"").indexOf("application/x-www-form-urlencoded")&&kc.test(b.data)&&"data");return h||"jsonp"===b.dataTypes[0]?(e=b.jsonpCallback=n.isFunction(b.jsonpCallback)?b.jsonpCallback():b.jsonpCallback,h?b[h]=b[h].replace(kc,"$1"+e):b.jsonp!==!1&&(b.url+=(Fb.test(b.url)?"&":"?")+b.jsonp+"="+e),b.converters["script json"]=function(){return g||n.error(e+" was not called"),g[0]},b.dataTypes[0]="json",f=a[e],a[e]=function(){g=arguments},d.always(function(){void 0===f?n(a).removeProp(e):a[e]=f,b[e]&&(b.jsonpCallback=c.jsonpCallback,jc.push(e)),g&&n.isFunction(f)&&f(g[0]),g=f=void 0}),"script"):void 0}),n.parseHTML=function(a,b,c){if(!a||"string"!=typeof a)return null;"boolean"==typeof b&&(c=b,b=!1),b=b||d;var e=x.exec(a),f=!c&&[];return e?[b.createElement(e[1])]:(e=ja([a],b,f),f&&f.length&&n(f).remove(),n.merge([],e.childNodes))};var lc=n.fn.load;n.fn.load=function(a,b,c){if("string"!=typeof a&&lc)return lc.apply(this,arguments);var d,e,f,g=this,h=a.indexOf(" ");return h>-1&&(d=n.trim(a.slice(h,a.length)),a=a.slice(0,h)),n.isFunction(b)?(c=b,b=void 0):b&&"object"==typeof b&&(e="POST"),g.length>0&&n.ajax({url:a,type:e||"GET",dataType:"html",data:b}).done(function(a){f=arguments,g.html(d?n("<div>").append(n.parseHTML(a)).find(d):a)}).always(c&&function(a,b){g.each(function(){c.apply(this,f||[a.responseText,b,a])})}),this},n.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(a,b){n.fn[b]=function(a){return this.on(b,a)}}),n.expr.filters.animated=function(a){return n.grep(n.timers,function(b){return a===b.elem}).length};function mc(a){return n.isWindow(a)?a:9===a.nodeType?a.defaultView||a.parentWindow:!1}n.offset={setOffset:function(a,b,c){var d,e,f,g,h,i,j,k=n.css(a,"position"),l=n(a),m={};"static"===k&&(a.style.position="relative"),h=l.offset(),f=n.css(a,"top"),i=n.css(a,"left"),j=("absolute"===k||"fixed"===k)&&n.inArray("auto",[f,i])>-1,j?(d=l.position(),g=d.top,e=d.left):(g=parseFloat(f)||0,e=parseFloat(i)||0),n.isFunction(b)&&(b=b.call(a,c,n.extend({},h))),null!=b.top&&(m.top=b.top-h.top+g),null!=b.left&&(m.left=b.left-h.left+e),"using"in b?b.using.call(a,m):l.css(m)}},n.fn.extend({offset:function(a){if(arguments.length)return void 0===a?this:this.each(function(b){n.offset.setOffset(this,a,b)});var b,c,d={top:0,left:0},e=this[0],f=e&&e.ownerDocument;if(f)return b=f.documentElement,n.contains(b,e)?("undefined"!=typeof e.getBoundingClientRect&&(d=e.getBoundingClientRect()),c=mc(f),{top:d.top+(c.pageYOffset||b.scrollTop)-(b.clientTop||0),left:d.left+(c.pageXOffset||b.scrollLeft)-(b.clientLeft||0)}):d},position:function(){if(this[0]){var a,b,c={top:0,left:0},d=this[0];return"fixed"===n.css(d,"position")?b=d.getBoundingClientRect():(a=this.offsetParent(),b=this.offset(),n.nodeName(a[0],"html")||(c=a.offset()),c.top+=n.css(a[0],"borderTopWidth",!0),c.left+=n.css(a[0],"borderLeftWidth",!0)),{top:b.top-c.top-n.css(d,"marginTop",!0),left:b.left-c.left-n.css(d,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var a=this.offsetParent;while(a&&!n.nodeName(a,"html")&&"static"===n.css(a,"position"))a=a.offsetParent;return a||Qa})}}),n.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(a,b){var c=/Y/.test(b);n.fn[a]=function(d){return Y(this,function(a,d,e){var f=mc(a);return void 0===e?f?b in f?f[b]:f.document.documentElement[d]:a[d]:void(f?f.scrollTo(c?n(f).scrollLeft():e,c?e:n(f).scrollTop()):a[d]=e)},a,d,arguments.length,null)}}),n.each(["top","left"],function(a,b){n.cssHooks[b]=Ua(l.pixelPosition,function(a,c){return c?(c=Sa(a,b),Oa.test(c)?n(a).position()[b]+"px":c):void 0})}),n.each({Height:"height",Width:"width"},function(a,b){n.each({
padding:"inner"+a,content:b,"":"outer"+a},function(c,d){n.fn[d]=function(d,e){var f=arguments.length&&(c||"boolean"!=typeof d),g=c||(d===!0||e===!0?"margin":"border");return Y(this,function(b,c,d){var e;return n.isWindow(b)?b.document.documentElement["client"+a]:9===b.nodeType?(e=b.documentElement,Math.max(b.body["scroll"+a],e["scroll"+a],b.body["offset"+a],e["offset"+a],e["client"+a])):void 0===d?n.css(b,c,g):n.style(b,c,d,g)},b,f?d:void 0,f,null)}})}),n.fn.extend({bind:function(a,b,c){return this.on(a,null,b,c)},unbind:function(a,b){return this.off(a,null,b)},delegate:function(a,b,c,d){return this.on(b,a,c,d)},undelegate:function(a,b,c){return 1===arguments.length?this.off(a,"**"):this.off(b,a||"**",c)}}),n.fn.size=function(){return this.length},n.fn.andSelf=n.fn.addBack,"function"==typeof define&&define.amd&&define("jquery",[],function(){return n});var nc=a.jQuery,oc=a.$;return n.noConflict=function(b){return a.$===n&&(a.$=oc),b&&a.jQuery===n&&(a.jQuery=nc),n},b||(a.jQuery=a.$=n),n});

/*! jQuery UI - v1.10.3 - 2013-08-28
* http://jqueryui.com
* Includes: jquery.ui.core.js, jquery.ui.widget.js, jquery.ui.mouse.js, jquery.ui.position.js, jquery.ui.draggable.js, jquery.ui.droppable.js, jquery.ui.resizable.js, jquery.ui.selectable.js, jquery.ui.sortable.js, jquery.ui.autocomplete.js, jquery.ui.menu.js, jquery.ui.effect.js
* Copyright 2013 jQuery Foundation and other contributors Licensed MIT */

(function(e,t){function i(t,i){var a,n,r,o=t.nodeName.toLowerCase();return"area"===o?(a=t.parentNode,n=a.name,t.href&&n&&"map"===a.nodeName.toLowerCase()?(r=e("img[usemap=#"+n+"]")[0],!!r&&s(r)):!1):(/input|select|textarea|button|object/.test(o)?!t.disabled:"a"===o?t.href||i:i)&&s(t)}function s(t){return e.expr.filters.visible(t)&&!e(t).parents().addBack().filter(function(){return"hidden"===e.css(this,"visibility")}).length}var a=0,n=/^ui-id-\d+$/;e.ui=e.ui||{},e.extend(e.ui,{version:"1.10.3",keyCode:{BACKSPACE:8,COMMA:188,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,LEFT:37,NUMPAD_ADD:107,NUMPAD_DECIMAL:110,NUMPAD_DIVIDE:111,NUMPAD_ENTER:108,NUMPAD_MULTIPLY:106,NUMPAD_SUBTRACT:109,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SPACE:32,TAB:9,UP:38}}),e.fn.extend({focus:function(t){return function(i,s){return"number"==typeof i?this.each(function(){var t=this;setTimeout(function(){e(t).focus(),s&&s.call(t)},i)}):t.apply(this,arguments)}}(e.fn.focus),scrollParent:function(){var t;return t=e.ui.ie&&/(static|relative)/.test(this.css("position"))||/absolute/.test(this.css("position"))?this.parents().filter(function(){return/(relative|absolute|fixed)/.test(e.css(this,"position"))&&/(auto|scroll)/.test(e.css(this,"overflow")+e.css(this,"overflow-y")+e.css(this,"overflow-x"))}).eq(0):this.parents().filter(function(){return/(auto|scroll)/.test(e.css(this,"overflow")+e.css(this,"overflow-y")+e.css(this,"overflow-x"))}).eq(0),/fixed/.test(this.css("position"))||!t.length?e(document):t},zIndex:function(i){if(i!==t)return this.css("zIndex",i);if(this.length)for(var s,a,n=e(this[0]);n.length&&n[0]!==document;){if(s=n.css("position"),("absolute"===s||"relative"===s||"fixed"===s)&&(a=parseInt(n.css("zIndex"),10),!isNaN(a)&&0!==a))return a;n=n.parent()}return 0},uniqueId:function(){return this.each(function(){this.id||(this.id="ui-id-"+ ++a)})},removeUniqueId:function(){return this.each(function(){n.test(this.id)&&e(this).removeAttr("id")})}}),e.extend(e.expr[":"],{data:e.expr.createPseudo?e.expr.createPseudo(function(t){return function(i){return!!e.data(i,t)}}):function(t,i,s){return!!e.data(t,s[3])},focusable:function(t){return i(t,!isNaN(e.attr(t,"tabindex")))},tabbable:function(t){var s=e.attr(t,"tabindex"),a=isNaN(s);return(a||s>=0)&&i(t,!a)}}),e("<a>").outerWidth(1).jquery||e.each(["Width","Height"],function(i,s){function a(t,i,s,a){return e.each(n,function(){i-=parseFloat(e.css(t,"padding"+this))||0,s&&(i-=parseFloat(e.css(t,"border"+this+"Width"))||0),a&&(i-=parseFloat(e.css(t,"margin"+this))||0)}),i}var n="Width"===s?["Left","Right"]:["Top","Bottom"],r=s.toLowerCase(),o={innerWidth:e.fn.innerWidth,innerHeight:e.fn.innerHeight,outerWidth:e.fn.outerWidth,outerHeight:e.fn.outerHeight};e.fn["inner"+s]=function(i){return i===t?o["inner"+s].call(this):this.each(function(){e(this).css(r,a(this,i)+"px")})},e.fn["outer"+s]=function(t,i){return"number"!=typeof t?o["outer"+s].call(this,t):this.each(function(){e(this).css(r,a(this,t,!0,i)+"px")})}}),e.fn.addBack||(e.fn.addBack=function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}),e("<a>").data("a-b","a").removeData("a-b").data("a-b")&&(e.fn.removeData=function(t){return function(i){return arguments.length?t.call(this,e.camelCase(i)):t.call(this)}}(e.fn.removeData)),e.ui.ie=!!/msie [\w.]+/.exec(navigator.userAgent.toLowerCase()),e.support.selectstart="onselectstart"in document.createElement("div"),e.fn.extend({disableSelection:function(){return this.bind((e.support.selectstart?"selectstart":"mousedown")+".ui-disableSelection",function(e){e.preventDefault()})},enableSelection:function(){return this.unbind(".ui-disableSelection")}}),e.extend(e.ui,{plugin:{add:function(t,i,s){var a,n=e.ui[t].prototype;for(a in s)n.plugins[a]=n.plugins[a]||[],n.plugins[a].push([i,s[a]])},call:function(e,t,i){var s,a=e.plugins[t];if(a&&e.element[0].parentNode&&11!==e.element[0].parentNode.nodeType)for(s=0;a.length>s;s++)e.options[a[s][0]]&&a[s][1].apply(e.element,i)}},hasScroll:function(t,i){if("hidden"===e(t).css("overflow"))return!1;var s=i&&"left"===i?"scrollLeft":"scrollTop",a=!1;return t[s]>0?!0:(t[s]=1,a=t[s]>0,t[s]=0,a)}})})(jQuery);(function(e,t){var i=0,s=Array.prototype.slice,n=e.cleanData;e.cleanData=function(t){for(var i,s=0;null!=(i=t[s]);s++)try{e(i).triggerHandler("remove")}catch(a){}n(t)},e.widget=function(i,s,n){var a,r,o,h,l={},u=i.split(".")[0];i=i.split(".")[1],a=u+"-"+i,n||(n=s,s=e.Widget),e.expr[":"][a.toLowerCase()]=function(t){return!!e.data(t,a)},e[u]=e[u]||{},r=e[u][i],o=e[u][i]=function(e,i){return this._createWidget?(arguments.length&&this._createWidget(e,i),t):new o(e,i)},e.extend(o,r,{version:n.version,_proto:e.extend({},n),_childConstructors:[]}),h=new s,h.options=e.widget.extend({},h.options),e.each(n,function(i,n){return e.isFunction(n)?(l[i]=function(){var e=function(){return s.prototype[i].apply(this,arguments)},t=function(e){return s.prototype[i].apply(this,e)};return function(){var i,s=this._super,a=this._superApply;return this._super=e,this._superApply=t,i=n.apply(this,arguments),this._super=s,this._superApply=a,i}}(),t):(l[i]=n,t)}),o.prototype=e.widget.extend(h,{widgetEventPrefix:r?h.widgetEventPrefix:i},l,{constructor:o,namespace:u,widgetName:i,widgetFullName:a}),r?(e.each(r._childConstructors,function(t,i){var s=i.prototype;e.widget(s.namespace+"."+s.widgetName,o,i._proto)}),delete r._childConstructors):s._childConstructors.push(o),e.widget.bridge(i,o)},e.widget.extend=function(i){for(var n,a,r=s.call(arguments,1),o=0,h=r.length;h>o;o++)for(n in r[o])a=r[o][n],r[o].hasOwnProperty(n)&&a!==t&&(i[n]=e.isPlainObject(a)?e.isPlainObject(i[n])?e.widget.extend({},i[n],a):e.widget.extend({},a):a);return i},e.widget.bridge=function(i,n){var a=n.prototype.widgetFullName||i;e.fn[i]=function(r){var o="string"==typeof r,h=s.call(arguments,1),l=this;return r=!o&&h.length?e.widget.extend.apply(null,[r].concat(h)):r,o?this.each(function(){var s,n=e.data(this,a);return n?e.isFunction(n[r])&&"_"!==r.charAt(0)?(s=n[r].apply(n,h),s!==n&&s!==t?(l=s&&s.jquery?l.pushStack(s.get()):s,!1):t):e.error("no such method '"+r+"' for "+i+" widget instance"):e.error("cannot call methods on "+i+" prior to initialization; "+"attempted to call method '"+r+"'")}):this.each(function(){var t=e.data(this,a);t?t.option(r||{})._init():e.data(this,a,new n(r,this))}),l}},e.Widget=function(){},e.Widget._childConstructors=[],e.Widget.prototype={widgetName:"widget",widgetEventPrefix:"",defaultElement:"<div>",options:{disabled:!1,create:null},_createWidget:function(t,s){s=e(s||this.defaultElement||this)[0],this.element=e(s),this.uuid=i++,this.eventNamespace="."+this.widgetName+this.uuid,this.options=e.widget.extend({},this.options,this._getCreateOptions(),t),this.bindings=e(),this.hoverable=e(),this.focusable=e(),s!==this&&(e.data(s,this.widgetFullName,this),this._on(!0,this.element,{remove:function(e){e.target===s&&this.destroy()}}),this.document=e(s.style?s.ownerDocument:s.document||s),this.window=e(this.document[0].defaultView||this.document[0].parentWindow)),this._create(),this._trigger("create",null,this._getCreateEventData()),this._init()},_getCreateOptions:e.noop,_getCreateEventData:e.noop,_create:e.noop,_init:e.noop,destroy:function(){this._destroy(),this.element.unbind(this.eventNamespace).removeData(this.widgetName).removeData(this.widgetFullName).removeData(e.camelCase(this.widgetFullName)),this.widget().unbind(this.eventNamespace).removeAttr("aria-disabled").removeClass(this.widgetFullName+"-disabled "+"ui-state-disabled"),this.bindings.unbind(this.eventNamespace),this.hoverable.removeClass("ui-state-hover"),this.focusable.removeClass("ui-state-focus")},_destroy:e.noop,widget:function(){return this.element},option:function(i,s){var n,a,r,o=i;if(0===arguments.length)return e.widget.extend({},this.options);if("string"==typeof i)if(o={},n=i.split("."),i=n.shift(),n.length){for(a=o[i]=e.widget.extend({},this.options[i]),r=0;n.length-1>r;r++)a[n[r]]=a[n[r]]||{},a=a[n[r]];if(i=n.pop(),s===t)return a[i]===t?null:a[i];a[i]=s}else{if(s===t)return this.options[i]===t?null:this.options[i];o[i]=s}return this._setOptions(o),this},_setOptions:function(e){var t;for(t in e)this._setOption(t,e[t]);return this},_setOption:function(e,t){return this.options[e]=t,"disabled"===e&&(this.widget().toggleClass(this.widgetFullName+"-disabled ui-state-disabled",!!t).attr("aria-disabled",t),this.hoverable.removeClass("ui-state-hover"),this.focusable.removeClass("ui-state-focus")),this},enable:function(){return this._setOption("disabled",!1)},disable:function(){return this._setOption("disabled",!0)},_on:function(i,s,n){var a,r=this;"boolean"!=typeof i&&(n=s,s=i,i=!1),n?(s=a=e(s),this.bindings=this.bindings.add(s)):(n=s,s=this.element,a=this.widget()),e.each(n,function(n,o){function h(){return i||r.options.disabled!==!0&&!e(this).hasClass("ui-state-disabled")?("string"==typeof o?r[o]:o).apply(r,arguments):t}"string"!=typeof o&&(h.guid=o.guid=o.guid||h.guid||e.guid++);var l=n.match(/^(\w+)\s*(.*)$/),u=l[1]+r.eventNamespace,c=l[2];c?a.delegate(c,u,h):s.bind(u,h)})},_off:function(e,t){t=(t||"").split(" ").join(this.eventNamespace+" ")+this.eventNamespace,e.unbind(t).undelegate(t)},_delay:function(e,t){function i(){return("string"==typeof e?s[e]:e).apply(s,arguments)}var s=this;return setTimeout(i,t||0)},_hoverable:function(t){this.hoverable=this.hoverable.add(t),this._on(t,{mouseenter:function(t){e(t.currentTarget).addClass("ui-state-hover")},mouseleave:function(t){e(t.currentTarget).removeClass("ui-state-hover")}})},_focusable:function(t){this.focusable=this.focusable.add(t),this._on(t,{focusin:function(t){e(t.currentTarget).addClass("ui-state-focus")},focusout:function(t){e(t.currentTarget).removeClass("ui-state-focus")}})},_trigger:function(t,i,s){var n,a,r=this.options[t];if(s=s||{},i=e.Event(i),i.type=(t===this.widgetEventPrefix?t:this.widgetEventPrefix+t).toLowerCase(),i.target=this.element[0],a=i.originalEvent)for(n in a)n in i||(i[n]=a[n]);return this.element.trigger(i,s),!(e.isFunction(r)&&r.apply(this.element[0],[i].concat(s))===!1||i.isDefaultPrevented())}},e.each({show:"fadeIn",hide:"fadeOut"},function(t,i){e.Widget.prototype["_"+t]=function(s,n,a){"string"==typeof n&&(n={effect:n});var r,o=n?n===!0||"number"==typeof n?i:n.effect||i:t;n=n||{},"number"==typeof n&&(n={duration:n}),r=!e.isEmptyObject(n),n.complete=a,n.delay&&s.delay(n.delay),r&&e.effects&&e.effects.effect[o]?s[t](n):o!==t&&s[o]?s[o](n.duration,n.easing,a):s.queue(function(i){e(this)[t](),a&&a.call(s[0]),i()})}})})(jQuery);(function(e){var t=!1;e(document).mouseup(function(){t=!1}),e.widget("ui.mouse",{version:"1.10.3",options:{cancel:"input,textarea,button,select,option",distance:1,delay:0},_mouseInit:function(){var t=this;this.element.bind("mousedown."+this.widgetName,function(e){return t._mouseDown(e)}).bind("click."+this.widgetName,function(i){return!0===e.data(i.target,t.widgetName+".preventClickEvent")?(e.removeData(i.target,t.widgetName+".preventClickEvent"),i.stopImmediatePropagation(),!1):undefined}),this.started=!1},_mouseDestroy:function(){this.element.unbind("."+this.widgetName),this._mouseMoveDelegate&&e(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate)},_mouseDown:function(i){if(!t){this._mouseStarted&&this._mouseUp(i),this._mouseDownEvent=i;var s=this,n=1===i.which,a="string"==typeof this.options.cancel&&i.target.nodeName?e(i.target).closest(this.options.cancel).length:!1;return n&&!a&&this._mouseCapture(i)?(this.mouseDelayMet=!this.options.delay,this.mouseDelayMet||(this._mouseDelayTimer=setTimeout(function(){s.mouseDelayMet=!0},this.options.delay)),this._mouseDistanceMet(i)&&this._mouseDelayMet(i)&&(this._mouseStarted=this._mouseStart(i)!==!1,!this._mouseStarted)?(i.preventDefault(),!0):(!0===e.data(i.target,this.widgetName+".preventClickEvent")&&e.removeData(i.target,this.widgetName+".preventClickEvent"),this._mouseMoveDelegate=function(e){return s._mouseMove(e)},this._mouseUpDelegate=function(e){return s._mouseUp(e)},e(document).bind("mousemove."+this.widgetName,this._mouseMoveDelegate).bind("mouseup."+this.widgetName,this._mouseUpDelegate),i.preventDefault(),t=!0,!0)):!0}},_mouseMove:function(t){return e.ui.ie&&(!document.documentMode||9>document.documentMode)&&!t.button?this._mouseUp(t):this._mouseStarted?(this._mouseDrag(t),t.preventDefault()):(this._mouseDistanceMet(t)&&this._mouseDelayMet(t)&&(this._mouseStarted=this._mouseStart(this._mouseDownEvent,t)!==!1,this._mouseStarted?this._mouseDrag(t):this._mouseUp(t)),!this._mouseStarted)},_mouseUp:function(t){return e(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate),this._mouseStarted&&(this._mouseStarted=!1,t.target===this._mouseDownEvent.target&&e.data(t.target,this.widgetName+".preventClickEvent",!0),this._mouseStop(t)),!1},_mouseDistanceMet:function(e){return Math.max(Math.abs(this._mouseDownEvent.pageX-e.pageX),Math.abs(this._mouseDownEvent.pageY-e.pageY))>=this.options.distance},_mouseDelayMet:function(){return this.mouseDelayMet},_mouseStart:function(){},_mouseDrag:function(){},_mouseStop:function(){},_mouseCapture:function(){return!0}})})(jQuery);(function(t,e){function i(t,e,i){return[parseFloat(t[0])*(p.test(t[0])?e/100:1),parseFloat(t[1])*(p.test(t[1])?i/100:1)]}function s(e,i){return parseInt(t.css(e,i),10)||0}function n(e){var i=e[0];return 9===i.nodeType?{width:e.width(),height:e.height(),offset:{top:0,left:0}}:t.isWindow(i)?{width:e.width(),height:e.height(),offset:{top:e.scrollTop(),left:e.scrollLeft()}}:i.preventDefault?{width:0,height:0,offset:{top:i.pageY,left:i.pageX}}:{width:e.outerWidth(),height:e.outerHeight(),offset:e.offset()}}t.ui=t.ui||{};var a,o=Math.max,r=Math.abs,h=Math.round,l=/left|center|right/,c=/top|center|bottom/,u=/[\+\-]\d+(\.[\d]+)?%?/,d=/^\w+/,p=/%$/,f=t.fn.position;t.position={scrollbarWidth:function(){if(a!==e)return a;var i,s,n=t("<div style='display:block;width:50px;height:50px;overflow:hidden;'><div style='height:100px;width:auto;'></div></div>"),o=n.children()[0];return t("body").append(n),i=o.offsetWidth,n.css("overflow","scroll"),s=o.offsetWidth,i===s&&(s=n[0].clientWidth),n.remove(),a=i-s},getScrollInfo:function(e){var i=e.isWindow?"":e.element.css("overflow-x"),s=e.isWindow?"":e.element.css("overflow-y"),n="scroll"===i||"auto"===i&&e.width<e.element[0].scrollWidth,a="scroll"===s||"auto"===s&&e.height<e.element[0].scrollHeight;return{width:a?t.position.scrollbarWidth():0,height:n?t.position.scrollbarWidth():0}},getWithinInfo:function(e){var i=t(e||window),s=t.isWindow(i[0]);return{element:i,isWindow:s,offset:i.offset()||{left:0,top:0},scrollLeft:i.scrollLeft(),scrollTop:i.scrollTop(),width:s?i.width():i.outerWidth(),height:s?i.height():i.outerHeight()}}},t.fn.position=function(e){if(!e||!e.of)return f.apply(this,arguments);e=t.extend({},e);var a,p,m,g,v,b,_=t(e.of),y=t.position.getWithinInfo(e.within),w=t.position.getScrollInfo(y),x=(e.collision||"flip").split(" "),k={};return b=n(_),_[0].preventDefault&&(e.at="left top"),p=b.width,m=b.height,g=b.offset,v=t.extend({},g),t.each(["my","at"],function(){var t,i,s=(e[this]||"").split(" ");1===s.length&&(s=l.test(s[0])?s.concat(["center"]):c.test(s[0])?["center"].concat(s):["center","center"]),s[0]=l.test(s[0])?s[0]:"center",s[1]=c.test(s[1])?s[1]:"center",t=u.exec(s[0]),i=u.exec(s[1]),k[this]=[t?t[0]:0,i?i[0]:0],e[this]=[d.exec(s[0])[0],d.exec(s[1])[0]]}),1===x.length&&(x[1]=x[0]),"right"===e.at[0]?v.left+=p:"center"===e.at[0]&&(v.left+=p/2),"bottom"===e.at[1]?v.top+=m:"center"===e.at[1]&&(v.top+=m/2),a=i(k.at,p,m),v.left+=a[0],v.top+=a[1],this.each(function(){var n,l,c=t(this),u=c.outerWidth(),d=c.outerHeight(),f=s(this,"marginLeft"),b=s(this,"marginTop"),D=u+f+s(this,"marginRight")+w.width,T=d+b+s(this,"marginBottom")+w.height,C=t.extend({},v),M=i(k.my,c.outerWidth(),c.outerHeight());"right"===e.my[0]?C.left-=u:"center"===e.my[0]&&(C.left-=u/2),"bottom"===e.my[1]?C.top-=d:"center"===e.my[1]&&(C.top-=d/2),C.left+=M[0],C.top+=M[1],t.support.offsetFractions||(C.left=h(C.left),C.top=h(C.top)),n={marginLeft:f,marginTop:b},t.each(["left","top"],function(i,s){t.ui.position[x[i]]&&t.ui.position[x[i]][s](C,{targetWidth:p,targetHeight:m,elemWidth:u,elemHeight:d,collisionPosition:n,collisionWidth:D,collisionHeight:T,offset:[a[0]+M[0],a[1]+M[1]],my:e.my,at:e.at,within:y,elem:c})}),e.using&&(l=function(t){var i=g.left-C.left,s=i+p-u,n=g.top-C.top,a=n+m-d,h={target:{element:_,left:g.left,top:g.top,width:p,height:m},element:{element:c,left:C.left,top:C.top,width:u,height:d},horizontal:0>s?"left":i>0?"right":"center",vertical:0>a?"top":n>0?"bottom":"middle"};u>p&&p>r(i+s)&&(h.horizontal="center"),d>m&&m>r(n+a)&&(h.vertical="middle"),h.important=o(r(i),r(s))>o(r(n),r(a))?"horizontal":"vertical",e.using.call(this,t,h)}),c.offset(t.extend(C,{using:l}))})},t.ui.position={fit:{left:function(t,e){var i,s=e.within,n=s.isWindow?s.scrollLeft:s.offset.left,a=s.width,r=t.left-e.collisionPosition.marginLeft,h=n-r,l=r+e.collisionWidth-a-n;e.collisionWidth>a?h>0&&0>=l?(i=t.left+h+e.collisionWidth-a-n,t.left+=h-i):t.left=l>0&&0>=h?n:h>l?n+a-e.collisionWidth:n:h>0?t.left+=h:l>0?t.left-=l:t.left=o(t.left-r,t.left)},top:function(t,e){var i,s=e.within,n=s.isWindow?s.scrollTop:s.offset.top,a=e.within.height,r=t.top-e.collisionPosition.marginTop,h=n-r,l=r+e.collisionHeight-a-n;e.collisionHeight>a?h>0&&0>=l?(i=t.top+h+e.collisionHeight-a-n,t.top+=h-i):t.top=l>0&&0>=h?n:h>l?n+a-e.collisionHeight:n:h>0?t.top+=h:l>0?t.top-=l:t.top=o(t.top-r,t.top)}},flip:{left:function(t,e){var i,s,n=e.within,a=n.offset.left+n.scrollLeft,o=n.width,h=n.isWindow?n.scrollLeft:n.offset.left,l=t.left-e.collisionPosition.marginLeft,c=l-h,u=l+e.collisionWidth-o-h,d="left"===e.my[0]?-e.elemWidth:"right"===e.my[0]?e.elemWidth:0,p="left"===e.at[0]?e.targetWidth:"right"===e.at[0]?-e.targetWidth:0,f=-2*e.offset[0];0>c?(i=t.left+d+p+f+e.collisionWidth-o-a,(0>i||r(c)>i)&&(t.left+=d+p+f)):u>0&&(s=t.left-e.collisionPosition.marginLeft+d+p+f-h,(s>0||u>r(s))&&(t.left+=d+p+f))},top:function(t,e){var i,s,n=e.within,a=n.offset.top+n.scrollTop,o=n.height,h=n.isWindow?n.scrollTop:n.offset.top,l=t.top-e.collisionPosition.marginTop,c=l-h,u=l+e.collisionHeight-o-h,d="top"===e.my[1],p=d?-e.elemHeight:"bottom"===e.my[1]?e.elemHeight:0,f="top"===e.at[1]?e.targetHeight:"bottom"===e.at[1]?-e.targetHeight:0,m=-2*e.offset[1];0>c?(s=t.top+p+f+m+e.collisionHeight-o-a,t.top+p+f+m>c&&(0>s||r(c)>s)&&(t.top+=p+f+m)):u>0&&(i=t.top-e.collisionPosition.marginTop+p+f+m-h,t.top+p+f+m>u&&(i>0||u>r(i))&&(t.top+=p+f+m))}},flipfit:{left:function(){t.ui.position.flip.left.apply(this,arguments),t.ui.position.fit.left.apply(this,arguments)},top:function(){t.ui.position.flip.top.apply(this,arguments),t.ui.position.fit.top.apply(this,arguments)}}},function(){var e,i,s,n,a,o=document.getElementsByTagName("body")[0],r=document.createElement("div");e=document.createElement(o?"div":"body"),s={visibility:"hidden",width:0,height:0,border:0,margin:0,background:"none"},o&&t.extend(s,{position:"absolute",left:"-1000px",top:"-1000px"});for(a in s)e.style[a]=s[a];e.appendChild(r),i=o||document.documentElement,i.insertBefore(e,i.firstChild),r.style.cssText="position: absolute; left: 10.7432222px;",n=t(r).offset().left,t.support.offsetFractions=n>10&&11>n,e.innerHTML="",i.removeChild(e)}()})(jQuery);(function(e){e.widget("ui.draggable",e.ui.mouse,{version:"1.10.3",widgetEventPrefix:"drag",options:{addClasses:!0,appendTo:"parent",axis:!1,connectToSortable:!1,containment:!1,cursor:"auto",cursorAt:!1,grid:!1,handle:!1,helper:"original",iframeFix:!1,opacity:!1,refreshPositions:!1,revert:!1,revertDuration:500,scope:"default",scroll:!0,scrollSensitivity:20,scrollSpeed:20,snap:!1,snapMode:"both",snapTolerance:20,stack:!1,zIndex:!1,drag:null,start:null,stop:null},_create:function(){"original"!==this.options.helper||/^(?:r|a|f)/.test(this.element.css("position"))||(this.element[0].style.position="relative"),this.options.addClasses&&this.element.addClass("ui-draggable"),this.options.disabled&&this.element.addClass("ui-draggable-disabled"),this._mouseInit()},_destroy:function(){this.element.removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled"),this._mouseDestroy()},_mouseCapture:function(t){var i=this.options;return this.helper||i.disabled||e(t.target).closest(".ui-resizable-handle").length>0?!1:(this.handle=this._getHandle(t),this.handle?(e(i.iframeFix===!0?"iframe":i.iframeFix).each(function(){e("<div class='ui-draggable-iframeFix' style='background: #fff;'></div>").css({width:this.offsetWidth+"px",height:this.offsetHeight+"px",position:"absolute",opacity:"0.001",zIndex:1e3}).css(e(this).offset()).appendTo("body")}),!0):!1)},_mouseStart:function(t){var i=this.options;return this.helper=this._createHelper(t),this.helper.addClass("ui-draggable-dragging"),this._cacheHelperProportions(),e.ui.ddmanager&&(e.ui.ddmanager.current=this),this._cacheMargins(),this.cssPosition=this.helper.css("position"),this.scrollParent=this.helper.scrollParent(),this.offsetParent=this.helper.offsetParent(),this.offsetParentCssPosition=this.offsetParent.css("position"),this.offset=this.positionAbs=this.element.offset(),this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left},this.offset.scroll=!1,e.extend(this.offset,{click:{left:t.pageX-this.offset.left,top:t.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()}),this.originalPosition=this.position=this._generatePosition(t),this.originalPageX=t.pageX,this.originalPageY=t.pageY,i.cursorAt&&this._adjustOffsetFromHelper(i.cursorAt),this._setContainment(),this._trigger("start",t)===!1?(this._clear(),!1):(this._cacheHelperProportions(),e.ui.ddmanager&&!i.dropBehaviour&&e.ui.ddmanager.prepareOffsets(this,t),this._mouseDrag(t,!0),e.ui.ddmanager&&e.ui.ddmanager.dragStart(this,t),!0)},_mouseDrag:function(t,i){if("fixed"===this.offsetParentCssPosition&&(this.offset.parent=this._getParentOffset()),this.position=this._generatePosition(t),this.positionAbs=this._convertPositionTo("absolute"),!i){var s=this._uiHash();if(this._trigger("drag",t,s)===!1)return this._mouseUp({}),!1;this.position=s.position}return this.options.axis&&"y"===this.options.axis||(this.helper[0].style.left=this.position.left+"px"),this.options.axis&&"x"===this.options.axis||(this.helper[0].style.top=this.position.top+"px"),e.ui.ddmanager&&e.ui.ddmanager.drag(this,t),!1},_mouseStop:function(t){var i=this,s=!1;return e.ui.ddmanager&&!this.options.dropBehaviour&&(s=e.ui.ddmanager.drop(this,t)),this.dropped&&(s=this.dropped,this.dropped=!1),"original"!==this.options.helper||e.contains(this.element[0].ownerDocument,this.element[0])?("invalid"===this.options.revert&&!s||"valid"===this.options.revert&&s||this.options.revert===!0||e.isFunction(this.options.revert)&&this.options.revert.call(this.element,s)?e(this.helper).animate(this.originalPosition,parseInt(this.options.revertDuration,10),function(){i._trigger("stop",t)!==!1&&i._clear()}):this._trigger("stop",t)!==!1&&this._clear(),!1):!1},_mouseUp:function(t){return e("div.ui-draggable-iframeFix").each(function(){this.parentNode.removeChild(this)}),e.ui.ddmanager&&e.ui.ddmanager.dragStop(this,t),e.ui.mouse.prototype._mouseUp.call(this,t)},cancel:function(){return this.helper.is(".ui-draggable-dragging")?this._mouseUp({}):this._clear(),this},_getHandle:function(t){return this.options.handle?!!e(t.target).closest(this.element.find(this.options.handle)).length:!0},_createHelper:function(t){var i=this.options,s=e.isFunction(i.helper)?e(i.helper.apply(this.element[0],[t])):"clone"===i.helper?this.element.clone().removeAttr("id"):this.element;return s.parents("body").length||s.appendTo("parent"===i.appendTo?this.element[0].parentNode:i.appendTo),s[0]===this.element[0]||/(fixed|absolute)/.test(s.css("position"))||s.css("position","absolute"),s},_adjustOffsetFromHelper:function(t){"string"==typeof t&&(t=t.split(" ")),e.isArray(t)&&(t={left:+t[0],top:+t[1]||0}),"left"in t&&(this.offset.click.left=t.left+this.margins.left),"right"in t&&(this.offset.click.left=this.helperProportions.width-t.right+this.margins.left),"top"in t&&(this.offset.click.top=t.top+this.margins.top),"bottom"in t&&(this.offset.click.top=this.helperProportions.height-t.bottom+this.margins.top)},_getParentOffset:function(){var t=this.offsetParent.offset();return"absolute"===this.cssPosition&&this.scrollParent[0]!==document&&e.contains(this.scrollParent[0],this.offsetParent[0])&&(t.left+=this.scrollParent.scrollLeft(),t.top+=this.scrollParent.scrollTop()),(this.offsetParent[0]===document.body||this.offsetParent[0].tagName&&"html"===this.offsetParent[0].tagName.toLowerCase()&&e.ui.ie)&&(t={top:0,left:0}),{top:t.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:t.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if("relative"===this.cssPosition){var e=this.element.position();return{top:e.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:e.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}return{top:0,left:0}},_cacheMargins:function(){this.margins={left:parseInt(this.element.css("marginLeft"),10)||0,top:parseInt(this.element.css("marginTop"),10)||0,right:parseInt(this.element.css("marginRight"),10)||0,bottom:parseInt(this.element.css("marginBottom"),10)||0}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var t,i,s,n=this.options;return n.containment?"window"===n.containment?(this.containment=[e(window).scrollLeft()-this.offset.relative.left-this.offset.parent.left,e(window).scrollTop()-this.offset.relative.top-this.offset.parent.top,e(window).scrollLeft()+e(window).width()-this.helperProportions.width-this.margins.left,e(window).scrollTop()+(e(window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top],undefined):"document"===n.containment?(this.containment=[0,0,e(document).width()-this.helperProportions.width-this.margins.left,(e(document).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top],undefined):n.containment.constructor===Array?(this.containment=n.containment,undefined):("parent"===n.containment&&(n.containment=this.helper[0].parentNode),i=e(n.containment),s=i[0],s&&(t="hidden"!==i.css("overflow"),this.containment=[(parseInt(i.css("borderLeftWidth"),10)||0)+(parseInt(i.css("paddingLeft"),10)||0),(parseInt(i.css("borderTopWidth"),10)||0)+(parseInt(i.css("paddingTop"),10)||0),(t?Math.max(s.scrollWidth,s.offsetWidth):s.offsetWidth)-(parseInt(i.css("borderRightWidth"),10)||0)-(parseInt(i.css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left-this.margins.right,(t?Math.max(s.scrollHeight,s.offsetHeight):s.offsetHeight)-(parseInt(i.css("borderBottomWidth"),10)||0)-(parseInt(i.css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top-this.margins.bottom],this.relative_container=i),undefined):(this.containment=null,undefined)},_convertPositionTo:function(t,i){i||(i=this.position);var s="absolute"===t?1:-1,n="absolute"!==this.cssPosition||this.scrollParent[0]!==document&&e.contains(this.scrollParent[0],this.offsetParent[0])?this.scrollParent:this.offsetParent;return this.offset.scroll||(this.offset.scroll={top:n.scrollTop(),left:n.scrollLeft()}),{top:i.top+this.offset.relative.top*s+this.offset.parent.top*s-("fixed"===this.cssPosition?-this.scrollParent.scrollTop():this.offset.scroll.top)*s,left:i.left+this.offset.relative.left*s+this.offset.parent.left*s-("fixed"===this.cssPosition?-this.scrollParent.scrollLeft():this.offset.scroll.left)*s}},_generatePosition:function(t){var i,s,n,a,o=this.options,r="absolute"!==this.cssPosition||this.scrollParent[0]!==document&&e.contains(this.scrollParent[0],this.offsetParent[0])?this.scrollParent:this.offsetParent,h=t.pageX,l=t.pageY;return this.offset.scroll||(this.offset.scroll={top:r.scrollTop(),left:r.scrollLeft()}),this.originalPosition&&(this.containment&&(this.relative_container?(s=this.relative_container.offset(),i=[this.containment[0]+s.left,this.containment[1]+s.top,this.containment[2]+s.left,this.containment[3]+s.top]):i=this.containment,t.pageX-this.offset.click.left<i[0]&&(h=i[0]+this.offset.click.left),t.pageY-this.offset.click.top<i[1]&&(l=i[1]+this.offset.click.top),t.pageX-this.offset.click.left>i[2]&&(h=i[2]+this.offset.click.left),t.pageY-this.offset.click.top>i[3]&&(l=i[3]+this.offset.click.top)),o.grid&&(n=o.grid[1]?this.originalPageY+Math.round((l-this.originalPageY)/o.grid[1])*o.grid[1]:this.originalPageY,l=i?n-this.offset.click.top>=i[1]||n-this.offset.click.top>i[3]?n:n-this.offset.click.top>=i[1]?n-o.grid[1]:n+o.grid[1]:n,a=o.grid[0]?this.originalPageX+Math.round((h-this.originalPageX)/o.grid[0])*o.grid[0]:this.originalPageX,h=i?a-this.offset.click.left>=i[0]||a-this.offset.click.left>i[2]?a:a-this.offset.click.left>=i[0]?a-o.grid[0]:a+o.grid[0]:a)),{top:l-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+("fixed"===this.cssPosition?-this.scrollParent.scrollTop():this.offset.scroll.top),left:h-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+("fixed"===this.cssPosition?-this.scrollParent.scrollLeft():this.offset.scroll.left)}},_clear:function(){this.helper.removeClass("ui-draggable-dragging"),this.helper[0]===this.element[0]||this.cancelHelperRemoval||this.helper.remove(),this.helper=null,this.cancelHelperRemoval=!1},_trigger:function(t,i,s){return s=s||this._uiHash(),e.ui.plugin.call(this,t,[i,s]),"drag"===t&&(this.positionAbs=this._convertPositionTo("absolute")),e.Widget.prototype._trigger.call(this,t,i,s)},plugins:{},_uiHash:function(){return{helper:this.helper,position:this.position,originalPosition:this.originalPosition,offset:this.positionAbs}}}),e.ui.plugin.add("draggable","connectToSortable",{start:function(t,i){var s=e(this).data("ui-draggable"),n=s.options,a=e.extend({},i,{item:s.element});s.sortables=[],e(n.connectToSortable).each(function(){var i=e.data(this,"ui-sortable");i&&!i.options.disabled&&(s.sortables.push({instance:i,shouldRevert:i.options.revert}),i.refreshPositions(),i._trigger("activate",t,a))})},stop:function(t,i){var s=e(this).data("ui-draggable"),n=e.extend({},i,{item:s.element});e.each(s.sortables,function(){this.instance.isOver?(this.instance.isOver=0,s.cancelHelperRemoval=!0,this.instance.cancelHelperRemoval=!1,this.shouldRevert&&(this.instance.options.revert=this.shouldRevert),this.instance._mouseStop(t),this.instance.options.helper=this.instance.options._helper,"original"===s.options.helper&&this.instance.currentItem.css({top:"auto",left:"auto"})):(this.instance.cancelHelperRemoval=!1,this.instance._trigger("deactivate",t,n))})},drag:function(t,i){var s=e(this).data("ui-draggable"),n=this;e.each(s.sortables,function(){var a=!1,o=this;this.instance.positionAbs=s.positionAbs,this.instance.helperProportions=s.helperProportions,this.instance.offset.click=s.offset.click,this.instance._intersectsWith(this.instance.containerCache)&&(a=!0,e.each(s.sortables,function(){return this.instance.positionAbs=s.positionAbs,this.instance.helperProportions=s.helperProportions,this.instance.offset.click=s.offset.click,this!==o&&this.instance._intersectsWith(this.instance.containerCache)&&e.contains(o.instance.element[0],this.instance.element[0])&&(a=!1),a})),a?(this.instance.isOver||(this.instance.isOver=1,this.instance.currentItem=e(n).clone().removeAttr("id").appendTo(this.instance.element).data("ui-sortable-item",!0),this.instance.options._helper=this.instance.options.helper,this.instance.options.helper=function(){return i.helper[0]},t.target=this.instance.currentItem[0],this.instance._mouseCapture(t,!0),this.instance._mouseStart(t,!0,!0),this.instance.offset.click.top=s.offset.click.top,this.instance.offset.click.left=s.offset.click.left,this.instance.offset.parent.left-=s.offset.parent.left-this.instance.offset.parent.left,this.instance.offset.parent.top-=s.offset.parent.top-this.instance.offset.parent.top,s._trigger("toSortable",t),s.dropped=this.instance.element,s.currentItem=s.element,this.instance.fromOutside=s),this.instance.currentItem&&this.instance._mouseDrag(t)):this.instance.isOver&&(this.instance.isOver=0,this.instance.cancelHelperRemoval=!0,this.instance.options.revert=!1,this.instance._trigger("out",t,this.instance._uiHash(this.instance)),this.instance._mouseStop(t,!0),this.instance.options.helper=this.instance.options._helper,this.instance.currentItem.remove(),this.instance.placeholder&&this.instance.placeholder.remove(),s._trigger("fromSortable",t),s.dropped=!1)})}}),e.ui.plugin.add("draggable","cursor",{start:function(){var t=e("body"),i=e(this).data("ui-draggable").options;t.css("cursor")&&(i._cursor=t.css("cursor")),t.css("cursor",i.cursor)},stop:function(){var t=e(this).data("ui-draggable").options;t._cursor&&e("body").css("cursor",t._cursor)}}),e.ui.plugin.add("draggable","opacity",{start:function(t,i){var s=e(i.helper),n=e(this).data("ui-draggable").options;s.css("opacity")&&(n._opacity=s.css("opacity")),s.css("opacity",n.opacity)},stop:function(t,i){var s=e(this).data("ui-draggable").options;s._opacity&&e(i.helper).css("opacity",s._opacity)}}),e.ui.plugin.add("draggable","scroll",{start:function(){var t=e(this).data("ui-draggable");t.scrollParent[0]!==document&&"HTML"!==t.scrollParent[0].tagName&&(t.overflowOffset=t.scrollParent.offset())},drag:function(t){var i=e(this).data("ui-draggable"),s=i.options,n=!1;i.scrollParent[0]!==document&&"HTML"!==i.scrollParent[0].tagName?(s.axis&&"x"===s.axis||(i.overflowOffset.top+i.scrollParent[0].offsetHeight-t.pageY<s.scrollSensitivity?i.scrollParent[0].scrollTop=n=i.scrollParent[0].scrollTop+s.scrollSpeed:t.pageY-i.overflowOffset.top<s.scrollSensitivity&&(i.scrollParent[0].scrollTop=n=i.scrollParent[0].scrollTop-s.scrollSpeed)),s.axis&&"y"===s.axis||(i.overflowOffset.left+i.scrollParent[0].offsetWidth-t.pageX<s.scrollSensitivity?i.scrollParent[0].scrollLeft=n=i.scrollParent[0].scrollLeft+s.scrollSpeed:t.pageX-i.overflowOffset.left<s.scrollSensitivity&&(i.scrollParent[0].scrollLeft=n=i.scrollParent[0].scrollLeft-s.scrollSpeed))):(s.axis&&"x"===s.axis||(t.pageY-e(document).scrollTop()<s.scrollSensitivity?n=e(document).scrollTop(e(document).scrollTop()-s.scrollSpeed):e(window).height()-(t.pageY-e(document).scrollTop())<s.scrollSensitivity&&(n=e(document).scrollTop(e(document).scrollTop()+s.scrollSpeed))),s.axis&&"y"===s.axis||(t.pageX-e(document).scrollLeft()<s.scrollSensitivity?n=e(document).scrollLeft(e(document).scrollLeft()-s.scrollSpeed):e(window).width()-(t.pageX-e(document).scrollLeft())<s.scrollSensitivity&&(n=e(document).scrollLeft(e(document).scrollLeft()+s.scrollSpeed)))),n!==!1&&e.ui.ddmanager&&!s.dropBehaviour&&e.ui.ddmanager.prepareOffsets(i,t)}}),e.ui.plugin.add("draggable","snap",{start:function(){var t=e(this).data("ui-draggable"),i=t.options;t.snapElements=[],e(i.snap.constructor!==String?i.snap.items||":data(ui-draggable)":i.snap).each(function(){var i=e(this),s=i.offset();this!==t.element[0]&&t.snapElements.push({item:this,width:i.outerWidth(),height:i.outerHeight(),top:s.top,left:s.left})})},drag:function(t,i){var s,n,a,o,r,h,l,u,c,d,p=e(this).data("ui-draggable"),f=p.options,m=f.snapTolerance,g=i.offset.left,v=g+p.helperProportions.width,b=i.offset.top,y=b+p.helperProportions.height;for(c=p.snapElements.length-1;c>=0;c--)r=p.snapElements[c].left,h=r+p.snapElements[c].width,l=p.snapElements[c].top,u=l+p.snapElements[c].height,r-m>v||g>h+m||l-m>y||b>u+m||!e.contains(p.snapElements[c].item.ownerDocument,p.snapElements[c].item)?(p.snapElements[c].snapping&&p.options.snap.release&&p.options.snap.release.call(p.element,t,e.extend(p._uiHash(),{snapItem:p.snapElements[c].item})),p.snapElements[c].snapping=!1):("inner"!==f.snapMode&&(s=m>=Math.abs(l-y),n=m>=Math.abs(u-b),a=m>=Math.abs(r-v),o=m>=Math.abs(h-g),s&&(i.position.top=p._convertPositionTo("relative",{top:l-p.helperProportions.height,left:0}).top-p.margins.top),n&&(i.position.top=p._convertPositionTo("relative",{top:u,left:0}).top-p.margins.top),a&&(i.position.left=p._convertPositionTo("relative",{top:0,left:r-p.helperProportions.width}).left-p.margins.left),o&&(i.position.left=p._convertPositionTo("relative",{top:0,left:h}).left-p.margins.left)),d=s||n||a||o,"outer"!==f.snapMode&&(s=m>=Math.abs(l-b),n=m>=Math.abs(u-y),a=m>=Math.abs(r-g),o=m>=Math.abs(h-v),s&&(i.position.top=p._convertPositionTo("relative",{top:l,left:0}).top-p.margins.top),n&&(i.position.top=p._convertPositionTo("relative",{top:u-p.helperProportions.height,left:0}).top-p.margins.top),a&&(i.position.left=p._convertPositionTo("relative",{top:0,left:r}).left-p.margins.left),o&&(i.position.left=p._convertPositionTo("relative",{top:0,left:h-p.helperProportions.width}).left-p.margins.left)),!p.snapElements[c].snapping&&(s||n||a||o||d)&&p.options.snap.snap&&p.options.snap.snap.call(p.element,t,e.extend(p._uiHash(),{snapItem:p.snapElements[c].item})),p.snapElements[c].snapping=s||n||a||o||d)}}),e.ui.plugin.add("draggable","stack",{start:function(){var t,i=this.data("ui-draggable").options,s=e.makeArray(e(i.stack)).sort(function(t,i){return(parseInt(e(t).css("zIndex"),10)||0)-(parseInt(e(i).css("zIndex"),10)||0)});s.length&&(t=parseInt(e(s[0]).css("zIndex"),10)||0,e(s).each(function(i){e(this).css("zIndex",t+i)}),this.css("zIndex",t+s.length))}}),e.ui.plugin.add("draggable","zIndex",{start:function(t,i){var s=e(i.helper),n=e(this).data("ui-draggable").options;s.css("zIndex")&&(n._zIndex=s.css("zIndex")),s.css("zIndex",n.zIndex)},stop:function(t,i){var s=e(this).data("ui-draggable").options;s._zIndex&&e(i.helper).css("zIndex",s._zIndex)}})})(jQuery);(function(e){function t(e,t,i){return e>t&&t+i>e}e.widget("ui.droppable",{version:"1.10.3",widgetEventPrefix:"drop",options:{accept:"*",activeClass:!1,addClasses:!0,greedy:!1,hoverClass:!1,scope:"default",tolerance:"intersect",activate:null,deactivate:null,drop:null,out:null,over:null},_create:function(){var t=this.options,i=t.accept;this.isover=!1,this.isout=!0,this.accept=e.isFunction(i)?i:function(e){return e.is(i)},this.proportions={width:this.element[0].offsetWidth,height:this.element[0].offsetHeight},e.ui.ddmanager.droppables[t.scope]=e.ui.ddmanager.droppables[t.scope]||[],e.ui.ddmanager.droppables[t.scope].push(this),t.addClasses&&this.element.addClass("ui-droppable")},_destroy:function(){for(var t=0,i=e.ui.ddmanager.droppables[this.options.scope];i.length>t;t++)i[t]===this&&i.splice(t,1);this.element.removeClass("ui-droppable ui-droppable-disabled")},_setOption:function(t,i){"accept"===t&&(this.accept=e.isFunction(i)?i:function(e){return e.is(i)}),e.Widget.prototype._setOption.apply(this,arguments)},_activate:function(t){var i=e.ui.ddmanager.current;this.options.activeClass&&this.element.addClass(this.options.activeClass),i&&this._trigger("activate",t,this.ui(i))},_deactivate:function(t){var i=e.ui.ddmanager.current;this.options.activeClass&&this.element.removeClass(this.options.activeClass),i&&this._trigger("deactivate",t,this.ui(i))},_over:function(t){var i=e.ui.ddmanager.current;i&&(i.currentItem||i.element)[0]!==this.element[0]&&this.accept.call(this.element[0],i.currentItem||i.element)&&(this.options.hoverClass&&this.element.addClass(this.options.hoverClass),this._trigger("over",t,this.ui(i)))},_out:function(t){var i=e.ui.ddmanager.current;i&&(i.currentItem||i.element)[0]!==this.element[0]&&this.accept.call(this.element[0],i.currentItem||i.element)&&(this.options.hoverClass&&this.element.removeClass(this.options.hoverClass),this._trigger("out",t,this.ui(i)))},_drop:function(t,i){var s=i||e.ui.ddmanager.current,n=!1;return s&&(s.currentItem||s.element)[0]!==this.element[0]?(this.element.find(":data(ui-droppable)").not(".ui-draggable-dragging").each(function(){var t=e.data(this,"ui-droppable");return t.options.greedy&&!t.options.disabled&&t.options.scope===s.options.scope&&t.accept.call(t.element[0],s.currentItem||s.element)&&e.ui.intersect(s,e.extend(t,{offset:t.element.offset()}),t.options.tolerance)?(n=!0,!1):undefined}),n?!1:this.accept.call(this.element[0],s.currentItem||s.element)?(this.options.activeClass&&this.element.removeClass(this.options.activeClass),this.options.hoverClass&&this.element.removeClass(this.options.hoverClass),this._trigger("drop",t,this.ui(s)),this.element):!1):!1},ui:function(e){return{draggable:e.currentItem||e.element,helper:e.helper,position:e.position,offset:e.positionAbs}}}),e.ui.intersect=function(e,i,s){if(!i.offset)return!1;var n,a,o=(e.positionAbs||e.position.absolute).left,r=o+e.helperProportions.width,h=(e.positionAbs||e.position.absolute).top,l=h+e.helperProportions.height,u=i.offset.left,c=u+i.proportions.width,d=i.offset.top,p=d+i.proportions.height;switch(s){case"fit":return o>=u&&c>=r&&h>=d&&p>=l;case"intersect":return o+e.helperProportions.width/2>u&&c>r-e.helperProportions.width/2&&h+e.helperProportions.height/2>d&&p>l-e.helperProportions.height/2;case"pointer":return n=(e.positionAbs||e.position.absolute).left+(e.clickOffset||e.offset.click).left,a=(e.positionAbs||e.position.absolute).top+(e.clickOffset||e.offset.click).top,t(a,d,i.proportions.height)&&t(n,u,i.proportions.width);case"touch":return(h>=d&&p>=h||l>=d&&p>=l||d>h&&l>p)&&(o>=u&&c>=o||r>=u&&c>=r||u>o&&r>c);default:return!1}},e.ui.ddmanager={current:null,droppables:{"default":[]},prepareOffsets:function(t,i){var s,n,a=e.ui.ddmanager.droppables[t.options.scope]||[],o=i?i.type:null,r=(t.currentItem||t.element).find(":data(ui-droppable)").addBack();e:for(s=0;a.length>s;s++)if(!(a[s].options.disabled||t&&!a[s].accept.call(a[s].element[0],t.currentItem||t.element))){for(n=0;r.length>n;n++)if(r[n]===a[s].element[0]){a[s].proportions.height=0;continue e}a[s].visible="none"!==a[s].element.css("display"),a[s].visible&&("mousedown"===o&&a[s]._activate.call(a[s],i),a[s].offset=a[s].element.offset(),a[s].proportions={width:a[s].element[0].offsetWidth,height:a[s].element[0].offsetHeight})}},drop:function(t,i){var s=!1;return e.each((e.ui.ddmanager.droppables[t.options.scope]||[]).slice(),function(){this.options&&(!this.options.disabled&&this.visible&&e.ui.intersect(t,this,this.options.tolerance)&&(s=this._drop.call(this,i)||s),!this.options.disabled&&this.visible&&this.accept.call(this.element[0],t.currentItem||t.element)&&(this.isout=!0,this.isover=!1,this._deactivate.call(this,i)))}),s},dragStart:function(t,i){t.element.parentsUntil("body").bind("scroll.droppable",function(){t.options.refreshPositions||e.ui.ddmanager.prepareOffsets(t,i)})},drag:function(t,i){t.options.refreshPositions&&e.ui.ddmanager.prepareOffsets(t,i),e.each(e.ui.ddmanager.droppables[t.options.scope]||[],function(){if(!this.options.disabled&&!this.greedyChild&&this.visible){var s,n,a,o=e.ui.intersect(t,this,this.options.tolerance),r=!o&&this.isover?"isout":o&&!this.isover?"isover":null;r&&(this.options.greedy&&(n=this.options.scope,a=this.element.parents(":data(ui-droppable)").filter(function(){return e.data(this,"ui-droppable").options.scope===n}),a.length&&(s=e.data(a[0],"ui-droppable"),s.greedyChild="isover"===r)),s&&"isover"===r&&(s.isover=!1,s.isout=!0,s._out.call(s,i)),this[r]=!0,this["isout"===r?"isover":"isout"]=!1,this["isover"===r?"_over":"_out"].call(this,i),s&&"isout"===r&&(s.isout=!1,s.isover=!0,s._over.call(s,i)))}})},dragStop:function(t,i){t.element.parentsUntil("body").unbind("scroll.droppable"),t.options.refreshPositions||e.ui.ddmanager.prepareOffsets(t,i)}}})(jQuery);(function(e){function t(e){return parseInt(e,10)||0}function i(e){return!isNaN(parseInt(e,10))}e.widget("ui.resizable",e.ui.mouse,{version:"1.10.3",widgetEventPrefix:"resize",options:{alsoResize:!1,animate:!1,animateDuration:"slow",animateEasing:"swing",aspectRatio:!1,autoHide:!1,containment:!1,ghost:!1,grid:!1,handles:"e,s,se",helper:!1,maxHeight:null,maxWidth:null,minHeight:10,minWidth:10,zIndex:90,resize:null,start:null,stop:null},_create:function(){var t,i,s,n,a,o=this,r=this.options;if(this.element.addClass("ui-resizable"),e.extend(this,{_aspectRatio:!!r.aspectRatio,aspectRatio:r.aspectRatio,originalElement:this.element,_proportionallyResizeElements:[],_helper:r.helper||r.ghost||r.animate?r.helper||"ui-resizable-helper":null}),this.element[0].nodeName.match(/canvas|textarea|input|select|button|img/i)&&(this.element.wrap(e("<div class='ui-wrapper' style='overflow: hidden;'></div>").css({position:this.element.css("position"),width:this.element.outerWidth(),height:this.element.outerHeight(),top:this.element.css("top"),left:this.element.css("left")})),this.element=this.element.parent().data("ui-resizable",this.element.data("ui-resizable")),this.elementIsWrapper=!0,this.element.css({marginLeft:this.originalElement.css("marginLeft"),marginTop:this.originalElement.css("marginTop"),marginRight:this.originalElement.css("marginRight"),marginBottom:this.originalElement.css("marginBottom")}),this.originalElement.css({marginLeft:0,marginTop:0,marginRight:0,marginBottom:0}),this.originalResizeStyle=this.originalElement.css("resize"),this.originalElement.css("resize","none"),this._proportionallyResizeElements.push(this.originalElement.css({position:"static",zoom:1,display:"block"})),this.originalElement.css({margin:this.originalElement.css("margin")}),this._proportionallyResize()),this.handles=r.handles||(e(".ui-resizable-handle",this.element).length?{n:".ui-resizable-n",e:".ui-resizable-e",s:".ui-resizable-s",w:".ui-resizable-w",se:".ui-resizable-se",sw:".ui-resizable-sw",ne:".ui-resizable-ne",nw:".ui-resizable-nw"}:"e,s,se"),this.handles.constructor===String)for("all"===this.handles&&(this.handles="n,e,s,w,se,sw,ne,nw"),t=this.handles.split(","),this.handles={},i=0;t.length>i;i++)s=e.trim(t[i]),a="ui-resizable-"+s,n=e("<div class='ui-resizable-handle "+a+"'></div>"),n.css({zIndex:r.zIndex}),"se"===s&&n.addClass("ui-icon ui-icon-gripsmall-diagonal-se"),this.handles[s]=".ui-resizable-"+s,this.element.append(n);this._renderAxis=function(t){var i,s,n,a;t=t||this.element;for(i in this.handles)this.handles[i].constructor===String&&(this.handles[i]=e(this.handles[i],this.element).show()),this.elementIsWrapper&&this.originalElement[0].nodeName.match(/textarea|input|select|button/i)&&(s=e(this.handles[i],this.element),a=/sw|ne|nw|se|n|s/.test(i)?s.outerHeight():s.outerWidth(),n=["padding",/ne|nw|n/.test(i)?"Top":/se|sw|s/.test(i)?"Bottom":/^e$/.test(i)?"Right":"Left"].join(""),t.css(n,a),this._proportionallyResize()),e(this.handles[i]).length},this._renderAxis(this.element),this._handles=e(".ui-resizable-handle",this.element).disableSelection(),this._handles.mouseover(function(){o.resizing||(this.className&&(n=this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i)),o.axis=n&&n[1]?n[1]:"se")}),r.autoHide&&(this._handles.hide(),e(this.element).addClass("ui-resizable-autohide").mouseenter(function(){r.disabled||(e(this).removeClass("ui-resizable-autohide"),o._handles.show())}).mouseleave(function(){r.disabled||o.resizing||(e(this).addClass("ui-resizable-autohide"),o._handles.hide())})),this._mouseInit()},_destroy:function(){this._mouseDestroy();var t,i=function(t){e(t).removeClass("ui-resizable ui-resizable-disabled ui-resizable-resizing").removeData("resizable").removeData("ui-resizable").unbind(".resizable").find(".ui-resizable-handle").remove()};return this.elementIsWrapper&&(i(this.element),t=this.element,this.originalElement.css({position:t.css("position"),width:t.outerWidth(),height:t.outerHeight(),top:t.css("top"),left:t.css("left")}).insertAfter(t),t.remove()),this.originalElement.css("resize",this.originalResizeStyle),i(this.originalElement),this},_mouseCapture:function(t){var i,s,n=!1;for(i in this.handles)s=e(this.handles[i])[0],(s===t.target||e.contains(s,t.target))&&(n=!0);return!this.options.disabled&&n},_mouseStart:function(i){var s,n,a,o=this.options,r=this.element.position(),h=this.element;return this.resizing=!0,/absolute/.test(h.css("position"))?h.css({position:"absolute",top:h.css("top"),left:h.css("left")}):h.is(".ui-draggable")&&h.css({position:"absolute",top:r.top,left:r.left}),this._renderProxy(),s=t(this.helper.css("left")),n=t(this.helper.css("top")),o.containment&&(s+=e(o.containment).scrollLeft()||0,n+=e(o.containment).scrollTop()||0),this.offset=this.helper.offset(),this.position={left:s,top:n},this.size=this._helper?{width:h.outerWidth(),height:h.outerHeight()}:{width:h.width(),height:h.height()},this.originalSize=this._helper?{width:h.outerWidth(),height:h.outerHeight()}:{width:h.width(),height:h.height()},this.originalPosition={left:s,top:n},this.sizeDiff={width:h.outerWidth()-h.width(),height:h.outerHeight()-h.height()},this.originalMousePosition={left:i.pageX,top:i.pageY},this.aspectRatio="number"==typeof o.aspectRatio?o.aspectRatio:this.originalSize.width/this.originalSize.height||1,a=e(".ui-resizable-"+this.axis).css("cursor"),e("body").css("cursor","auto"===a?this.axis+"-resize":a),h.addClass("ui-resizable-resizing"),this._propagate("start",i),!0},_mouseDrag:function(t){var i,s=this.helper,n={},a=this.originalMousePosition,o=this.axis,r=this.position.top,h=this.position.left,l=this.size.width,u=this.size.height,c=t.pageX-a.left||0,d=t.pageY-a.top||0,p=this._change[o];return p?(i=p.apply(this,[t,c,d]),this._updateVirtualBoundaries(t.shiftKey),(this._aspectRatio||t.shiftKey)&&(i=this._updateRatio(i,t)),i=this._respectSize(i,t),this._updateCache(i),this._propagate("resize",t),this.position.top!==r&&(n.top=this.position.top+"px"),this.position.left!==h&&(n.left=this.position.left+"px"),this.size.width!==l&&(n.width=this.size.width+"px"),this.size.height!==u&&(n.height=this.size.height+"px"),s.css(n),!this._helper&&this._proportionallyResizeElements.length&&this._proportionallyResize(),e.isEmptyObject(n)||this._trigger("resize",t,this.ui()),!1):!1},_mouseStop:function(t){this.resizing=!1;var i,s,n,a,o,r,h,l=this.options,u=this;return this._helper&&(i=this._proportionallyResizeElements,s=i.length&&/textarea/i.test(i[0].nodeName),n=s&&e.ui.hasScroll(i[0],"left")?0:u.sizeDiff.height,a=s?0:u.sizeDiff.width,o={width:u.helper.width()-a,height:u.helper.height()-n},r=parseInt(u.element.css("left"),10)+(u.position.left-u.originalPosition.left)||null,h=parseInt(u.element.css("top"),10)+(u.position.top-u.originalPosition.top)||null,l.animate||this.element.css(e.extend(o,{top:h,left:r})),u.helper.height(u.size.height),u.helper.width(u.size.width),this._helper&&!l.animate&&this._proportionallyResize()),e("body").css("cursor","auto"),this.element.removeClass("ui-resizable-resizing"),this._propagate("stop",t),this._helper&&this.helper.remove(),!1},_updateVirtualBoundaries:function(e){var t,s,n,a,o,r=this.options;o={minWidth:i(r.minWidth)?r.minWidth:0,maxWidth:i(r.maxWidth)?r.maxWidth:1/0,minHeight:i(r.minHeight)?r.minHeight:0,maxHeight:i(r.maxHeight)?r.maxHeight:1/0},(this._aspectRatio||e)&&(t=o.minHeight*this.aspectRatio,n=o.minWidth/this.aspectRatio,s=o.maxHeight*this.aspectRatio,a=o.maxWidth/this.aspectRatio,t>o.minWidth&&(o.minWidth=t),n>o.minHeight&&(o.minHeight=n),o.maxWidth>s&&(o.maxWidth=s),o.maxHeight>a&&(o.maxHeight=a)),this._vBoundaries=o},_updateCache:function(e){this.offset=this.helper.offset(),i(e.left)&&(this.position.left=e.left),i(e.top)&&(this.position.top=e.top),i(e.height)&&(this.size.height=e.height),i(e.width)&&(this.size.width=e.width)},_updateRatio:function(e){var t=this.position,s=this.size,n=this.axis;return i(e.height)?e.width=e.height*this.aspectRatio:i(e.width)&&(e.height=e.width/this.aspectRatio),"sw"===n&&(e.left=t.left+(s.width-e.width),e.top=null),"nw"===n&&(e.top=t.top+(s.height-e.height),e.left=t.left+(s.width-e.width)),e},_respectSize:function(e){var t=this._vBoundaries,s=this.axis,n=i(e.width)&&t.maxWidth&&t.maxWidth<e.width,a=i(e.height)&&t.maxHeight&&t.maxHeight<e.height,o=i(e.width)&&t.minWidth&&t.minWidth>e.width,r=i(e.height)&&t.minHeight&&t.minHeight>e.height,h=this.originalPosition.left+this.originalSize.width,l=this.position.top+this.size.height,u=/sw|nw|w/.test(s),c=/nw|ne|n/.test(s);return o&&(e.width=t.minWidth),r&&(e.height=t.minHeight),n&&(e.width=t.maxWidth),a&&(e.height=t.maxHeight),o&&u&&(e.left=h-t.minWidth),n&&u&&(e.left=h-t.maxWidth),r&&c&&(e.top=l-t.minHeight),a&&c&&(e.top=l-t.maxHeight),e.width||e.height||e.left||!e.top?e.width||e.height||e.top||!e.left||(e.left=null):e.top=null,e},_proportionallyResize:function(){if(this._proportionallyResizeElements.length){var e,t,i,s,n,a=this.helper||this.element;for(e=0;this._proportionallyResizeElements.length>e;e++){if(n=this._proportionallyResizeElements[e],!this.borderDif)for(this.borderDif=[],i=[n.css("borderTopWidth"),n.css("borderRightWidth"),n.css("borderBottomWidth"),n.css("borderLeftWidth")],s=[n.css("paddingTop"),n.css("paddingRight"),n.css("paddingBottom"),n.css("paddingLeft")],t=0;i.length>t;t++)this.borderDif[t]=(parseInt(i[t],10)||0)+(parseInt(s[t],10)||0);n.css({height:a.height()-this.borderDif[0]-this.borderDif[2]||0,width:a.width()-this.borderDif[1]-this.borderDif[3]||0})}}},_renderProxy:function(){var t=this.element,i=this.options;this.elementOffset=t.offset(),this._helper?(this.helper=this.helper||e("<div style='overflow:hidden;'></div>"),this.helper.addClass(this._helper).css({width:this.element.outerWidth()-1,height:this.element.outerHeight()-1,position:"absolute",left:this.elementOffset.left+"px",top:this.elementOffset.top+"px",zIndex:++i.zIndex}),this.helper.appendTo("body").disableSelection()):this.helper=this.element},_change:{e:function(e,t){return{width:this.originalSize.width+t}},w:function(e,t){var i=this.originalSize,s=this.originalPosition;return{left:s.left+t,width:i.width-t}},n:function(e,t,i){var s=this.originalSize,n=this.originalPosition;return{top:n.top+i,height:s.height-i}},s:function(e,t,i){return{height:this.originalSize.height+i}},se:function(t,i,s){return e.extend(this._change.s.apply(this,arguments),this._change.e.apply(this,[t,i,s]))},sw:function(t,i,s){return e.extend(this._change.s.apply(this,arguments),this._change.w.apply(this,[t,i,s]))},ne:function(t,i,s){return e.extend(this._change.n.apply(this,arguments),this._change.e.apply(this,[t,i,s]))},nw:function(t,i,s){return e.extend(this._change.n.apply(this,arguments),this._change.w.apply(this,[t,i,s]))}},_propagate:function(t,i){e.ui.plugin.call(this,t,[i,this.ui()]),"resize"!==t&&this._trigger(t,i,this.ui())},plugins:{},ui:function(){return{originalElement:this.originalElement,element:this.element,helper:this.helper,position:this.position,size:this.size,originalSize:this.originalSize,originalPosition:this.originalPosition}}}),e.ui.plugin.add("resizable","animate",{stop:function(t){var i=e(this).data("ui-resizable"),s=i.options,n=i._proportionallyResizeElements,a=n.length&&/textarea/i.test(n[0].nodeName),o=a&&e.ui.hasScroll(n[0],"left")?0:i.sizeDiff.height,r=a?0:i.sizeDiff.width,h={width:i.size.width-r,height:i.size.height-o},l=parseInt(i.element.css("left"),10)+(i.position.left-i.originalPosition.left)||null,u=parseInt(i.element.css("top"),10)+(i.position.top-i.originalPosition.top)||null;i.element.animate(e.extend(h,u&&l?{top:u,left:l}:{}),{duration:s.animateDuration,easing:s.animateEasing,step:function(){var s={width:parseInt(i.element.css("width"),10),height:parseInt(i.element.css("height"),10),top:parseInt(i.element.css("top"),10),left:parseInt(i.element.css("left"),10)};n&&n.length&&e(n[0]).css({width:s.width,height:s.height}),i._updateCache(s),i._propagate("resize",t)}})}}),e.ui.plugin.add("resizable","containment",{start:function(){var i,s,n,a,o,r,h,l=e(this).data("ui-resizable"),u=l.options,c=l.element,d=u.containment,p=d instanceof e?d.get(0):/parent/.test(d)?c.parent().get(0):d;p&&(l.containerElement=e(p),/document/.test(d)||d===document?(l.containerOffset={left:0,top:0},l.containerPosition={left:0,top:0},l.parentData={element:e(document),left:0,top:0,width:e(document).width(),height:e(document).height()||document.body.parentNode.scrollHeight}):(i=e(p),s=[],e(["Top","Right","Left","Bottom"]).each(function(e,n){s[e]=t(i.css("padding"+n))}),l.containerOffset=i.offset(),l.containerPosition=i.position(),l.containerSize={height:i.innerHeight()-s[3],width:i.innerWidth()-s[1]},n=l.containerOffset,a=l.containerSize.height,o=l.containerSize.width,r=e.ui.hasScroll(p,"left")?p.scrollWidth:o,h=e.ui.hasScroll(p)?p.scrollHeight:a,l.parentData={element:p,left:n.left,top:n.top,width:r,height:h}))},resize:function(t){var i,s,n,a,o=e(this).data("ui-resizable"),r=o.options,h=o.containerOffset,l=o.position,u=o._aspectRatio||t.shiftKey,c={top:0,left:0},d=o.containerElement;d[0]!==document&&/static/.test(d.css("position"))&&(c=h),l.left<(o._helper?h.left:0)&&(o.size.width=o.size.width+(o._helper?o.position.left-h.left:o.position.left-c.left),u&&(o.size.height=o.size.width/o.aspectRatio),o.position.left=r.helper?h.left:0),l.top<(o._helper?h.top:0)&&(o.size.height=o.size.height+(o._helper?o.position.top-h.top:o.position.top),u&&(o.size.width=o.size.height*o.aspectRatio),o.position.top=o._helper?h.top:0),o.offset.left=o.parentData.left+o.position.left,o.offset.top=o.parentData.top+o.position.top,i=Math.abs((o._helper?o.offset.left-c.left:o.offset.left-c.left)+o.sizeDiff.width),s=Math.abs((o._helper?o.offset.top-c.top:o.offset.top-h.top)+o.sizeDiff.height),n=o.containerElement.get(0)===o.element.parent().get(0),a=/relative|absolute/.test(o.containerElement.css("position")),n&&a&&(i-=o.parentData.left),i+o.size.width>=o.parentData.width&&(o.size.width=o.parentData.width-i,u&&(o.size.height=o.size.width/o.aspectRatio)),s+o.size.height>=o.parentData.height&&(o.size.height=o.parentData.height-s,u&&(o.size.width=o.size.height*o.aspectRatio))},stop:function(){var t=e(this).data("ui-resizable"),i=t.options,s=t.containerOffset,n=t.containerPosition,a=t.containerElement,o=e(t.helper),r=o.offset(),h=o.outerWidth()-t.sizeDiff.width,l=o.outerHeight()-t.sizeDiff.height;t._helper&&!i.animate&&/relative/.test(a.css("position"))&&e(this).css({left:r.left-n.left-s.left,width:h,height:l}),t._helper&&!i.animate&&/static/.test(a.css("position"))&&e(this).css({left:r.left-n.left-s.left,width:h,height:l})}}),e.ui.plugin.add("resizable","alsoResize",{start:function(){var t=e(this).data("ui-resizable"),i=t.options,s=function(t){e(t).each(function(){var t=e(this);t.data("ui-resizable-alsoresize",{width:parseInt(t.width(),10),height:parseInt(t.height(),10),left:parseInt(t.css("left"),10),top:parseInt(t.css("top"),10)})})};"object"!=typeof i.alsoResize||i.alsoResize.parentNode?s(i.alsoResize):i.alsoResize.length?(i.alsoResize=i.alsoResize[0],s(i.alsoResize)):e.each(i.alsoResize,function(e){s(e)})},resize:function(t,i){var s=e(this).data("ui-resizable"),n=s.options,a=s.originalSize,o=s.originalPosition,r={height:s.size.height-a.height||0,width:s.size.width-a.width||0,top:s.position.top-o.top||0,left:s.position.left-o.left||0},h=function(t,s){e(t).each(function(){var t=e(this),n=e(this).data("ui-resizable-alsoresize"),a={},o=s&&s.length?s:t.parents(i.originalElement[0]).length?["width","height"]:["width","height","top","left"];e.each(o,function(e,t){var i=(n[t]||0)+(r[t]||0);i&&i>=0&&(a[t]=i||null)}),t.css(a)})};"object"!=typeof n.alsoResize||n.alsoResize.nodeType?h(n.alsoResize):e.each(n.alsoResize,function(e,t){h(e,t)})},stop:function(){e(this).removeData("resizable-alsoresize")}}),e.ui.plugin.add("resizable","ghost",{start:function(){var t=e(this).data("ui-resizable"),i=t.options,s=t.size;t.ghost=t.originalElement.clone(),t.ghost.css({opacity:.25,display:"block",position:"relative",height:s.height,width:s.width,margin:0,left:0,top:0}).addClass("ui-resizable-ghost").addClass("string"==typeof i.ghost?i.ghost:""),t.ghost.appendTo(t.helper)},resize:function(){var t=e(this).data("ui-resizable");t.ghost&&t.ghost.css({position:"relative",height:t.size.height,width:t.size.width})},stop:function(){var t=e(this).data("ui-resizable");t.ghost&&t.helper&&t.helper.get(0).removeChild(t.ghost.get(0))}}),e.ui.plugin.add("resizable","grid",{resize:function(){var t=e(this).data("ui-resizable"),i=t.options,s=t.size,n=t.originalSize,a=t.originalPosition,o=t.axis,r="number"==typeof i.grid?[i.grid,i.grid]:i.grid,h=r[0]||1,l=r[1]||1,u=Math.round((s.width-n.width)/h)*h,c=Math.round((s.height-n.height)/l)*l,d=n.width+u,p=n.height+c,f=i.maxWidth&&d>i.maxWidth,m=i.maxHeight&&p>i.maxHeight,g=i.minWidth&&i.minWidth>d,v=i.minHeight&&i.minHeight>p;i.grid=r,g&&(d+=h),v&&(p+=l),f&&(d-=h),m&&(p-=l),/^(se|s|e)$/.test(o)?(t.size.width=d,t.size.height=p):/^(ne)$/.test(o)?(t.size.width=d,t.size.height=p,t.position.top=a.top-c):/^(sw)$/.test(o)?(t.size.width=d,t.size.height=p,t.position.left=a.left-u):(t.size.width=d,t.size.height=p,t.position.top=a.top-c,t.position.left=a.left-u)}})})(jQuery);(function(e){e.widget("ui.selectable",e.ui.mouse,{version:"1.10.3",options:{appendTo:"body",autoRefresh:!0,distance:0,filter:"*",tolerance:"touch",selected:null,selecting:null,start:null,stop:null,unselected:null,unselecting:null},_create:function(){var t,i=this;this.element.addClass("ui-selectable"),this.dragged=!1,this.refresh=function(){t=e(i.options.filter,i.element[0]),t.addClass("ui-selectee"),t.each(function(){var t=e(this),i=t.offset();e.data(this,"selectable-item",{element:this,$element:t,left:i.left,top:i.top,right:i.left+t.outerWidth(),bottom:i.top+t.outerHeight(),startselected:!1,selected:t.hasClass("ui-selected"),selecting:t.hasClass("ui-selecting"),unselecting:t.hasClass("ui-unselecting")})})},this.refresh(),this.selectees=t.addClass("ui-selectee"),this._mouseInit(),this.helper=e("<div class='ui-selectable-helper'></div>")},_destroy:function(){this.selectees.removeClass("ui-selectee").removeData("selectable-item"),this.element.removeClass("ui-selectable ui-selectable-disabled"),this._mouseDestroy()},_mouseStart:function(t){var i=this,s=this.options;this.opos=[t.pageX,t.pageY],this.options.disabled||(this.selectees=e(s.filter,this.element[0]),this._trigger("start",t),e(s.appendTo).append(this.helper),this.helper.css({left:t.pageX,top:t.pageY,width:0,height:0}),s.autoRefresh&&this.refresh(),this.selectees.filter(".ui-selected").each(function(){var s=e.data(this,"selectable-item");s.startselected=!0,t.metaKey||t.ctrlKey||(s.$element.removeClass("ui-selected"),s.selected=!1,s.$element.addClass("ui-unselecting"),s.unselecting=!0,i._trigger("unselecting",t,{unselecting:s.element}))}),e(t.target).parents().addBack().each(function(){var s,n=e.data(this,"selectable-item");return n?(s=!t.metaKey&&!t.ctrlKey||!n.$element.hasClass("ui-selected"),n.$element.removeClass(s?"ui-unselecting":"ui-selected").addClass(s?"ui-selecting":"ui-unselecting"),n.unselecting=!s,n.selecting=s,n.selected=s,s?i._trigger("selecting",t,{selecting:n.element}):i._trigger("unselecting",t,{unselecting:n.element}),!1):undefined}))},_mouseDrag:function(t){if(this.dragged=!0,!this.options.disabled){var i,s=this,n=this.options,a=this.opos[0],o=this.opos[1],r=t.pageX,h=t.pageY;return a>r&&(i=r,r=a,a=i),o>h&&(i=h,h=o,o=i),this.helper.css({left:a,top:o,width:r-a,height:h-o}),this.selectees.each(function(){var i=e.data(this,"selectable-item"),l=!1;i&&i.element!==s.element[0]&&("touch"===n.tolerance?l=!(i.left>r||a>i.right||i.top>h||o>i.bottom):"fit"===n.tolerance&&(l=i.left>a&&r>i.right&&i.top>o&&h>i.bottom),l?(i.selected&&(i.$element.removeClass("ui-selected"),i.selected=!1),i.unselecting&&(i.$element.removeClass("ui-unselecting"),i.unselecting=!1),i.selecting||(i.$element.addClass("ui-selecting"),i.selecting=!0,s._trigger("selecting",t,{selecting:i.element}))):(i.selecting&&((t.metaKey||t.ctrlKey)&&i.startselected?(i.$element.removeClass("ui-selecting"),i.selecting=!1,i.$element.addClass("ui-selected"),i.selected=!0):(i.$element.removeClass("ui-selecting"),i.selecting=!1,i.startselected&&(i.$element.addClass("ui-unselecting"),i.unselecting=!0),s._trigger("unselecting",t,{unselecting:i.element}))),i.selected&&(t.metaKey||t.ctrlKey||i.startselected||(i.$element.removeClass("ui-selected"),i.selected=!1,i.$element.addClass("ui-unselecting"),i.unselecting=!0,s._trigger("unselecting",t,{unselecting:i.element})))))}),!1}},_mouseStop:function(t){var i=this;return this.dragged=!1,e(".ui-unselecting",this.element[0]).each(function(){var s=e.data(this,"selectable-item");s.$element.removeClass("ui-unselecting"),s.unselecting=!1,s.startselected=!1,i._trigger("unselected",t,{unselected:s.element})}),e(".ui-selecting",this.element[0]).each(function(){var s=e.data(this,"selectable-item");s.$element.removeClass("ui-selecting").addClass("ui-selected"),s.selecting=!1,s.selected=!0,s.startselected=!0,i._trigger("selected",t,{selected:s.element})}),this._trigger("stop",t),this.helper.remove(),!1}})})(jQuery);(function(t){function e(t,e,i){return t>e&&e+i>t}function i(t){return/left|right/.test(t.css("float"))||/inline|table-cell/.test(t.css("display"))}t.widget("ui.sortable",t.ui.mouse,{version:"1.10.3",widgetEventPrefix:"sort",ready:!1,options:{appendTo:"parent",axis:!1,connectWith:!1,containment:!1,cursor:"auto",cursorAt:!1,dropOnEmpty:!0,forcePlaceholderSize:!1,forceHelperSize:!1,grid:!1,handle:!1,helper:"original",items:"> *",opacity:!1,placeholder:!1,revert:!1,scroll:!0,scrollSensitivity:20,scrollSpeed:20,scope:"default",tolerance:"intersect",zIndex:1e3,activate:null,beforeStop:null,change:null,deactivate:null,out:null,over:null,receive:null,remove:null,sort:null,start:null,stop:null,update:null},_create:function(){var t=this.options;this.containerCache={},this.element.addClass("ui-sortable"),this.refresh(),this.floating=this.items.length?"x"===t.axis||i(this.items[0].item):!1,this.offset=this.element.offset(),this._mouseInit(),this.ready=!0},_destroy:function(){this.element.removeClass("ui-sortable ui-sortable-disabled"),this._mouseDestroy();for(var t=this.items.length-1;t>=0;t--)this.items[t].item.removeData(this.widgetName+"-item");return this},_setOption:function(e,i){"disabled"===e?(this.options[e]=i,this.widget().toggleClass("ui-sortable-disabled",!!i)):t.Widget.prototype._setOption.apply(this,arguments)},_mouseCapture:function(e,i){var s=null,n=!1,a=this;return this.reverting?!1:this.options.disabled||"static"===this.options.type?!1:(this._refreshItems(e),t(e.target).parents().each(function(){return t.data(this,a.widgetName+"-item")===a?(s=t(this),!1):undefined}),t.data(e.target,a.widgetName+"-item")===a&&(s=t(e.target)),s?!this.options.handle||i||(t(this.options.handle,s).find("*").addBack().each(function(){this===e.target&&(n=!0)}),n)?(this.currentItem=s,this._removeCurrentsFromItems(),!0):!1:!1)},_mouseStart:function(e,i,s){var n,a,o=this.options;if(this.currentContainer=this,this.refreshPositions(),this.helper=this._createHelper(e),this._cacheHelperProportions(),this._cacheMargins(),this.scrollParent=this.helper.scrollParent(),this.offset=this.currentItem.offset(),this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left},t.extend(this.offset,{click:{left:e.pageX-this.offset.left,top:e.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()}),this.helper.css("position","absolute"),this.cssPosition=this.helper.css("position"),this.originalPosition=this._generatePosition(e),this.originalPageX=e.pageX,this.originalPageY=e.pageY,o.cursorAt&&this._adjustOffsetFromHelper(o.cursorAt),this.domPosition={prev:this.currentItem.prev()[0],parent:this.currentItem.parent()[0]},this.helper[0]!==this.currentItem[0]&&this.currentItem.hide(),this._createPlaceholder(),o.containment&&this._setContainment(),o.cursor&&"auto"!==o.cursor&&(a=this.document.find("body"),this.storedCursor=a.css("cursor"),a.css("cursor",o.cursor),this.storedStylesheet=t("<style>*{ cursor: "+o.cursor+" !important; }</style>").appendTo(a)),o.opacity&&(this.helper.css("opacity")&&(this._storedOpacity=this.helper.css("opacity")),this.helper.css("opacity",o.opacity)),o.zIndex&&(this.helper.css("zIndex")&&(this._storedZIndex=this.helper.css("zIndex")),this.helper.css("zIndex",o.zIndex)),this.scrollParent[0]!==document&&"HTML"!==this.scrollParent[0].tagName&&(this.overflowOffset=this.scrollParent.offset()),this._trigger("start",e,this._uiHash()),this._preserveHelperProportions||this._cacheHelperProportions(),!s)for(n=this.containers.length-1;n>=0;n--)this.containers[n]._trigger("activate",e,this._uiHash(this));return t.ui.ddmanager&&(t.ui.ddmanager.current=this),t.ui.ddmanager&&!o.dropBehaviour&&t.ui.ddmanager.prepareOffsets(this,e),this.dragging=!0,this.helper.addClass("ui-sortable-helper"),this._mouseDrag(e),!0},_mouseDrag:function(e){var i,s,n,a,o=this.options,r=!1;for(this.position=this._generatePosition(e),this.positionAbs=this._convertPositionTo("absolute"),this.lastPositionAbs||(this.lastPositionAbs=this.positionAbs),this.options.scroll&&(this.scrollParent[0]!==document&&"HTML"!==this.scrollParent[0].tagName?(this.overflowOffset.top+this.scrollParent[0].offsetHeight-e.pageY<o.scrollSensitivity?this.scrollParent[0].scrollTop=r=this.scrollParent[0].scrollTop+o.scrollSpeed:e.pageY-this.overflowOffset.top<o.scrollSensitivity&&(this.scrollParent[0].scrollTop=r=this.scrollParent[0].scrollTop-o.scrollSpeed),this.overflowOffset.left+this.scrollParent[0].offsetWidth-e.pageX<o.scrollSensitivity?this.scrollParent[0].scrollLeft=r=this.scrollParent[0].scrollLeft+o.scrollSpeed:e.pageX-this.overflowOffset.left<o.scrollSensitivity&&(this.scrollParent[0].scrollLeft=r=this.scrollParent[0].scrollLeft-o.scrollSpeed)):(e.pageY-t(document).scrollTop()<o.scrollSensitivity?r=t(document).scrollTop(t(document).scrollTop()-o.scrollSpeed):t(window).height()-(e.pageY-t(document).scrollTop())<o.scrollSensitivity&&(r=t(document).scrollTop(t(document).scrollTop()+o.scrollSpeed)),e.pageX-t(document).scrollLeft()<o.scrollSensitivity?r=t(document).scrollLeft(t(document).scrollLeft()-o.scrollSpeed):t(window).width()-(e.pageX-t(document).scrollLeft())<o.scrollSensitivity&&(r=t(document).scrollLeft(t(document).scrollLeft()+o.scrollSpeed))),r!==!1&&t.ui.ddmanager&&!o.dropBehaviour&&t.ui.ddmanager.prepareOffsets(this,e)),this.positionAbs=this._convertPositionTo("absolute"),this.options.axis&&"y"===this.options.axis||(this.helper[0].style.left=this.position.left+"px"),this.options.axis&&"x"===this.options.axis||(this.helper[0].style.top=this.position.top+"px"),i=this.items.length-1;i>=0;i--)if(s=this.items[i],n=s.item[0],a=this._intersectsWithPointer(s),a&&s.instance===this.currentContainer&&n!==this.currentItem[0]&&this.placeholder[1===a?"next":"prev"]()[0]!==n&&!t.contains(this.placeholder[0],n)&&("semi-dynamic"===this.options.type?!t.contains(this.element[0],n):!0)){if(this.direction=1===a?"down":"up","pointer"!==this.options.tolerance&&!this._intersectsWithSides(s))break;this._rearrange(e,s),this._trigger("change",e,this._uiHash());break}return this._contactContainers(e),t.ui.ddmanager&&t.ui.ddmanager.drag(this,e),this._trigger("sort",e,this._uiHash()),this.lastPositionAbs=this.positionAbs,!1},_mouseStop:function(e,i){if(e){if(t.ui.ddmanager&&!this.options.dropBehaviour&&t.ui.ddmanager.drop(this,e),this.options.revert){var s=this,n=this.placeholder.offset(),a=this.options.axis,o={};a&&"x"!==a||(o.left=n.left-this.offset.parent.left-this.margins.left+(this.offsetParent[0]===document.body?0:this.offsetParent[0].scrollLeft)),a&&"y"!==a||(o.top=n.top-this.offset.parent.top-this.margins.top+(this.offsetParent[0]===document.body?0:this.offsetParent[0].scrollTop)),this.reverting=!0,t(this.helper).animate(o,parseInt(this.options.revert,10)||500,function(){s._clear(e)})}else this._clear(e,i);return!1}},cancel:function(){if(this.dragging){this._mouseUp({target:null}),"original"===this.options.helper?this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper"):this.currentItem.show();for(var e=this.containers.length-1;e>=0;e--)this.containers[e]._trigger("deactivate",null,this._uiHash(this)),this.containers[e].containerCache.over&&(this.containers[e]._trigger("out",null,this._uiHash(this)),this.containers[e].containerCache.over=0)}return this.placeholder&&(this.placeholder[0].parentNode&&this.placeholder[0].parentNode.removeChild(this.placeholder[0]),"original"!==this.options.helper&&this.helper&&this.helper[0].parentNode&&this.helper.remove(),t.extend(this,{helper:null,dragging:!1,reverting:!1,_noFinalSort:null}),this.domPosition.prev?t(this.domPosition.prev).after(this.currentItem):t(this.domPosition.parent).prepend(this.currentItem)),this},serialize:function(e){var i=this._getItemsAsjQuery(e&&e.connected),s=[];return e=e||{},t(i).each(function(){var i=(t(e.item||this).attr(e.attribute||"id")||"").match(e.expression||/(.+)[\-=_](.+)/);i&&s.push((e.key||i[1]+"[]")+"="+(e.key&&e.expression?i[1]:i[2]))}),!s.length&&e.key&&s.push(e.key+"="),s.join("&")},toArray:function(e){var i=this._getItemsAsjQuery(e&&e.connected),s=[];return e=e||{},i.each(function(){s.push(t(e.item||this).attr(e.attribute||"id")||"")}),s},_intersectsWith:function(t){var e=this.positionAbs.left,i=e+this.helperProportions.width,s=this.positionAbs.top,n=s+this.helperProportions.height,a=t.left,o=a+t.width,r=t.top,h=r+t.height,l=this.offset.click.top,c=this.offset.click.left,u="x"===this.options.axis||s+l>r&&h>s+l,d="y"===this.options.axis||e+c>a&&o>e+c,p=u&&d;return"pointer"===this.options.tolerance||this.options.forcePointerForContainers||"pointer"!==this.options.tolerance&&this.helperProportions[this.floating?"width":"height"]>t[this.floating?"width":"height"]?p:e+this.helperProportions.width/2>a&&o>i-this.helperProportions.width/2&&s+this.helperProportions.height/2>r&&h>n-this.helperProportions.height/2},_intersectsWithPointer:function(t){var i="x"===this.options.axis||e(this.positionAbs.top+this.offset.click.top,t.top,t.height),s="y"===this.options.axis||e(this.positionAbs.left+this.offset.click.left,t.left,t.width),n=i&&s,a=this._getDragVerticalDirection(),o=this._getDragHorizontalDirection();return n?this.floating?o&&"right"===o||"down"===a?2:1:a&&("down"===a?2:1):!1},_intersectsWithSides:function(t){var i=e(this.positionAbs.top+this.offset.click.top,t.top+t.height/2,t.height),s=e(this.positionAbs.left+this.offset.click.left,t.left+t.width/2,t.width),n=this._getDragVerticalDirection(),a=this._getDragHorizontalDirection();return this.floating&&a?"right"===a&&s||"left"===a&&!s:n&&("down"===n&&i||"up"===n&&!i)},_getDragVerticalDirection:function(){var t=this.positionAbs.top-this.lastPositionAbs.top;return 0!==t&&(t>0?"down":"up")},_getDragHorizontalDirection:function(){var t=this.positionAbs.left-this.lastPositionAbs.left;return 0!==t&&(t>0?"right":"left")},refresh:function(t){return this._refreshItems(t),this.refreshPositions(),this},_connectWith:function(){var t=this.options;return t.connectWith.constructor===String?[t.connectWith]:t.connectWith},_getItemsAsjQuery:function(e){var i,s,n,a,o=[],r=[],h=this._connectWith();if(h&&e)for(i=h.length-1;i>=0;i--)for(n=t(h[i]),s=n.length-1;s>=0;s--)a=t.data(n[s],this.widgetFullName),a&&a!==this&&!a.options.disabled&&r.push([t.isFunction(a.options.items)?a.options.items.call(a.element):t(a.options.items,a.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"),a]);for(r.push([t.isFunction(this.options.items)?this.options.items.call(this.element,null,{options:this.options,item:this.currentItem}):t(this.options.items,this.element).not(".ui-sortable-helper").not(".ui-sortable-placeholder"),this]),i=r.length-1;i>=0;i--)r[i][0].each(function(){o.push(this)});return t(o)},_removeCurrentsFromItems:function(){var e=this.currentItem.find(":data("+this.widgetName+"-item)");this.items=t.grep(this.items,function(t){for(var i=0;e.length>i;i++)if(e[i]===t.item[0])return!1;return!0})},_refreshItems:function(e){this.items=[],this.containers=[this];var i,s,n,a,o,r,h,l,c=this.items,u=[[t.isFunction(this.options.items)?this.options.items.call(this.element[0],e,{item:this.currentItem}):t(this.options.items,this.element),this]],d=this._connectWith();if(d&&this.ready)for(i=d.length-1;i>=0;i--)for(n=t(d[i]),s=n.length-1;s>=0;s--)a=t.data(n[s],this.widgetFullName),a&&a!==this&&!a.options.disabled&&(u.push([t.isFunction(a.options.items)?a.options.items.call(a.element[0],e,{item:this.currentItem}):t(a.options.items,a.element),a]),this.containers.push(a));for(i=u.length-1;i>=0;i--)for(o=u[i][1],r=u[i][0],s=0,l=r.length;l>s;s++)h=t(r[s]),h.data(this.widgetName+"-item",o),c.push({item:h,instance:o,width:0,height:0,left:0,top:0})},refreshPositions:function(e){this.offsetParent&&this.helper&&(this.offset.parent=this._getParentOffset());var i,s,n,a;for(i=this.items.length-1;i>=0;i--)s=this.items[i],s.instance!==this.currentContainer&&this.currentContainer&&s.item[0]!==this.currentItem[0]||(n=this.options.toleranceElement?t(this.options.toleranceElement,s.item):s.item,e||(s.width=n.outerWidth(),s.height=n.outerHeight()),a=n.offset(),s.left=a.left,s.top=a.top);if(this.options.custom&&this.options.custom.refreshContainers)this.options.custom.refreshContainers.call(this);else for(i=this.containers.length-1;i>=0;i--)a=this.containers[i].element.offset(),this.containers[i].containerCache.left=a.left,this.containers[i].containerCache.top=a.top,this.containers[i].containerCache.width=this.containers[i].element.outerWidth(),this.containers[i].containerCache.height=this.containers[i].element.outerHeight();return this},_createPlaceholder:function(e){e=e||this;var i,s=e.options;s.placeholder&&s.placeholder.constructor!==String||(i=s.placeholder,s.placeholder={element:function(){var s=e.currentItem[0].nodeName.toLowerCase(),n=t("<"+s+">",e.document[0]).addClass(i||e.currentItem[0].className+" ui-sortable-placeholder").removeClass("ui-sortable-helper");return"tr"===s?e.currentItem.children().each(function(){t("<td>&#160;</td>",e.document[0]).attr("colspan",t(this).attr("colspan")||1).appendTo(n)}):"img"===s&&n.attr("src",e.currentItem.attr("src")),i||n.css("visibility","hidden"),n},update:function(t,n){(!i||s.forcePlaceholderSize)&&(n.height()||n.height(e.currentItem.innerHeight()-parseInt(e.currentItem.css("paddingTop")||0,10)-parseInt(e.currentItem.css("paddingBottom")||0,10)),n.width()||n.width(e.currentItem.innerWidth()-parseInt(e.currentItem.css("paddingLeft")||0,10)-parseInt(e.currentItem.css("paddingRight")||0,10)))}}),e.placeholder=t(s.placeholder.element.call(e.element,e.currentItem)),e.currentItem.after(e.placeholder),s.placeholder.update(e,e.placeholder)},_contactContainers:function(s){var n,a,o,r,h,l,c,u,d,p,f=null,m=null;for(n=this.containers.length-1;n>=0;n--)if(!t.contains(this.currentItem[0],this.containers[n].element[0]))if(this._intersectsWith(this.containers[n].containerCache)){if(f&&t.contains(this.containers[n].element[0],f.element[0]))continue;f=this.containers[n],m=n}else this.containers[n].containerCache.over&&(this.containers[n]._trigger("out",s,this._uiHash(this)),this.containers[n].containerCache.over=0);if(f)if(1===this.containers.length)this.containers[m].containerCache.over||(this.containers[m]._trigger("over",s,this._uiHash(this)),this.containers[m].containerCache.over=1);else{for(o=1e4,r=null,p=f.floating||i(this.currentItem),h=p?"left":"top",l=p?"width":"height",c=this.positionAbs[h]+this.offset.click[h],a=this.items.length-1;a>=0;a--)t.contains(this.containers[m].element[0],this.items[a].item[0])&&this.items[a].item[0]!==this.currentItem[0]&&(!p||e(this.positionAbs.top+this.offset.click.top,this.items[a].top,this.items[a].height))&&(u=this.items[a].item.offset()[h],d=!1,Math.abs(u-c)>Math.abs(u+this.items[a][l]-c)&&(d=!0,u+=this.items[a][l]),o>Math.abs(u-c)&&(o=Math.abs(u-c),r=this.items[a],this.direction=d?"up":"down"));if(!r&&!this.options.dropOnEmpty)return;if(this.currentContainer===this.containers[m])return;r?this._rearrange(s,r,null,!0):this._rearrange(s,null,this.containers[m].element,!0),this._trigger("change",s,this._uiHash()),this.containers[m]._trigger("change",s,this._uiHash(this)),this.currentContainer=this.containers[m],this.options.placeholder.update(this.currentContainer,this.placeholder),this.containers[m]._trigger("over",s,this._uiHash(this)),this.containers[m].containerCache.over=1}},_createHelper:function(e){var i=this.options,s=t.isFunction(i.helper)?t(i.helper.apply(this.element[0],[e,this.currentItem])):"clone"===i.helper?this.currentItem.clone():this.currentItem;return s.parents("body").length||t("parent"!==i.appendTo?i.appendTo:this.currentItem[0].parentNode)[0].appendChild(s[0]),s[0]===this.currentItem[0]&&(this._storedCSS={width:this.currentItem[0].style.width,height:this.currentItem[0].style.height,position:this.currentItem.css("position"),top:this.currentItem.css("top"),left:this.currentItem.css("left")}),(!s[0].style.width||i.forceHelperSize)&&s.width(this.currentItem.width()),(!s[0].style.height||i.forceHelperSize)&&s.height(this.currentItem.height()),s},_adjustOffsetFromHelper:function(e){"string"==typeof e&&(e=e.split(" ")),t.isArray(e)&&(e={left:+e[0],top:+e[1]||0}),"left"in e&&(this.offset.click.left=e.left+this.margins.left),"right"in e&&(this.offset.click.left=this.helperProportions.width-e.right+this.margins.left),"top"in e&&(this.offset.click.top=e.top+this.margins.top),"bottom"in e&&(this.offset.click.top=this.helperProportions.height-e.bottom+this.margins.top)},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var e=this.offsetParent.offset();return"absolute"===this.cssPosition&&this.scrollParent[0]!==document&&t.contains(this.scrollParent[0],this.offsetParent[0])&&(e.left+=this.scrollParent.scrollLeft(),e.top+=this.scrollParent.scrollTop()),(this.offsetParent[0]===document.body||this.offsetParent[0].tagName&&"html"===this.offsetParent[0].tagName.toLowerCase()&&t.ui.ie)&&(e={top:0,left:0}),{top:e.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:e.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if("relative"===this.cssPosition){var t=this.currentItem.position();return{top:t.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:t.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}return{top:0,left:0}},_cacheMargins:function(){this.margins={left:parseInt(this.currentItem.css("marginLeft"),10)||0,top:parseInt(this.currentItem.css("marginTop"),10)||0}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var e,i,s,n=this.options;"parent"===n.containment&&(n.containment=this.helper[0].parentNode),("document"===n.containment||"window"===n.containment)&&(this.containment=[0-this.offset.relative.left-this.offset.parent.left,0-this.offset.relative.top-this.offset.parent.top,t("document"===n.containment?document:window).width()-this.helperProportions.width-this.margins.left,(t("document"===n.containment?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top]),/^(document|window|parent)$/.test(n.containment)||(e=t(n.containment)[0],i=t(n.containment).offset(),s="hidden"!==t(e).css("overflow"),this.containment=[i.left+(parseInt(t(e).css("borderLeftWidth"),10)||0)+(parseInt(t(e).css("paddingLeft"),10)||0)-this.margins.left,i.top+(parseInt(t(e).css("borderTopWidth"),10)||0)+(parseInt(t(e).css("paddingTop"),10)||0)-this.margins.top,i.left+(s?Math.max(e.scrollWidth,e.offsetWidth):e.offsetWidth)-(parseInt(t(e).css("borderLeftWidth"),10)||0)-(parseInt(t(e).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left,i.top+(s?Math.max(e.scrollHeight,e.offsetHeight):e.offsetHeight)-(parseInt(t(e).css("borderTopWidth"),10)||0)-(parseInt(t(e).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top])},_convertPositionTo:function(e,i){i||(i=this.position);var s="absolute"===e?1:-1,n="absolute"!==this.cssPosition||this.scrollParent[0]!==document&&t.contains(this.scrollParent[0],this.offsetParent[0])?this.scrollParent:this.offsetParent,a=/(html|body)/i.test(n[0].tagName);return{top:i.top+this.offset.relative.top*s+this.offset.parent.top*s-("fixed"===this.cssPosition?-this.scrollParent.scrollTop():a?0:n.scrollTop())*s,left:i.left+this.offset.relative.left*s+this.offset.parent.left*s-("fixed"===this.cssPosition?-this.scrollParent.scrollLeft():a?0:n.scrollLeft())*s}},_generatePosition:function(e){var i,s,n=this.options,a=e.pageX,o=e.pageY,r="absolute"!==this.cssPosition||this.scrollParent[0]!==document&&t.contains(this.scrollParent[0],this.offsetParent[0])?this.scrollParent:this.offsetParent,h=/(html|body)/i.test(r[0].tagName);return"relative"!==this.cssPosition||this.scrollParent[0]!==document&&this.scrollParent[0]!==this.offsetParent[0]||(this.offset.relative=this._getRelativeOffset()),this.originalPosition&&(this.containment&&(e.pageX-this.offset.click.left<this.containment[0]&&(a=this.containment[0]+this.offset.click.left),e.pageY-this.offset.click.top<this.containment[1]&&(o=this.containment[1]+this.offset.click.top),e.pageX-this.offset.click.left>this.containment[2]&&(a=this.containment[2]+this.offset.click.left),e.pageY-this.offset.click.top>this.containment[3]&&(o=this.containment[3]+this.offset.click.top)),n.grid&&(i=this.originalPageY+Math.round((o-this.originalPageY)/n.grid[1])*n.grid[1],o=this.containment?i-this.offset.click.top>=this.containment[1]&&i-this.offset.click.top<=this.containment[3]?i:i-this.offset.click.top>=this.containment[1]?i-n.grid[1]:i+n.grid[1]:i,s=this.originalPageX+Math.round((a-this.originalPageX)/n.grid[0])*n.grid[0],a=this.containment?s-this.offset.click.left>=this.containment[0]&&s-this.offset.click.left<=this.containment[2]?s:s-this.offset.click.left>=this.containment[0]?s-n.grid[0]:s+n.grid[0]:s)),{top:o-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+("fixed"===this.cssPosition?-this.scrollParent.scrollTop():h?0:r.scrollTop()),left:a-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+("fixed"===this.cssPosition?-this.scrollParent.scrollLeft():h?0:r.scrollLeft())}},_rearrange:function(t,e,i,s){i?i[0].appendChild(this.placeholder[0]):e.item[0].parentNode.insertBefore(this.placeholder[0],"down"===this.direction?e.item[0]:e.item[0].nextSibling),this.counter=this.counter?++this.counter:1;var n=this.counter;this._delay(function(){n===this.counter&&this.refreshPositions(!s)})},_clear:function(t,e){this.reverting=!1;var i,s=[];if(!this._noFinalSort&&this.currentItem.parent().length&&this.placeholder.before(this.currentItem),this._noFinalSort=null,this.helper[0]===this.currentItem[0]){for(i in this._storedCSS)("auto"===this._storedCSS[i]||"static"===this._storedCSS[i])&&(this._storedCSS[i]="");this.currentItem.css(this._storedCSS).removeClass("ui-sortable-helper")}else this.currentItem.show();for(this.fromOutside&&!e&&s.push(function(t){this._trigger("receive",t,this._uiHash(this.fromOutside))}),!this.fromOutside&&this.domPosition.prev===this.currentItem.prev().not(".ui-sortable-helper")[0]&&this.domPosition.parent===this.currentItem.parent()[0]||e||s.push(function(t){this._trigger("update",t,this._uiHash())}),this!==this.currentContainer&&(e||(s.push(function(t){this._trigger("remove",t,this._uiHash())}),s.push(function(t){return function(e){t._trigger("receive",e,this._uiHash(this))}}.call(this,this.currentContainer)),s.push(function(t){return function(e){t._trigger("update",e,this._uiHash(this))}}.call(this,this.currentContainer)))),i=this.containers.length-1;i>=0;i--)e||s.push(function(t){return function(e){t._trigger("deactivate",e,this._uiHash(this))}}.call(this,this.containers[i])),this.containers[i].containerCache.over&&(s.push(function(t){return function(e){t._trigger("out",e,this._uiHash(this))}}.call(this,this.containers[i])),this.containers[i].containerCache.over=0);if(this.storedCursor&&(this.document.find("body").css("cursor",this.storedCursor),this.storedStylesheet.remove()),this._storedOpacity&&this.helper.css("opacity",this._storedOpacity),this._storedZIndex&&this.helper.css("zIndex","auto"===this._storedZIndex?"":this._storedZIndex),this.dragging=!1,this.cancelHelperRemoval){if(!e){for(this._trigger("beforeStop",t,this._uiHash()),i=0;s.length>i;i++)s[i].call(this,t);this._trigger("stop",t,this._uiHash())}return this.fromOutside=!1,!1}if(e||this._trigger("beforeStop",t,this._uiHash()),this.placeholder[0].parentNode.removeChild(this.placeholder[0]),this.helper[0]!==this.currentItem[0]&&this.helper.remove(),this.helper=null,!e){for(i=0;s.length>i;i++)s[i].call(this,t);this._trigger("stop",t,this._uiHash())}return this.fromOutside=!1,!0},_trigger:function(){t.Widget.prototype._trigger.apply(this,arguments)===!1&&this.cancel()},_uiHash:function(e){var i=e||this;return{helper:i.helper,placeholder:i.placeholder||t([]),position:i.position,originalPosition:i.originalPosition,offset:i.positionAbs,item:i.currentItem,sender:e?e.element:null}}})})(jQuery);(function(t){var e=0;t.widget("ui.autocomplete",{version:"1.10.3",defaultElement:"<input>",options:{appendTo:null,autoFocus:!1,delay:300,minLength:1,position:{my:"left top",at:"left bottom",collision:"none"},source:null,change:null,close:null,focus:null,open:null,response:null,search:null,select:null},pending:0,_create:function(){var e,i,s,n=this.element[0].nodeName.toLowerCase(),a="textarea"===n,o="input"===n;this.isMultiLine=a?!0:o?!1:this.element.prop("isContentEditable"),this.valueMethod=this.element[a||o?"val":"text"],this.isNewMenu=!0,this.element.addClass("ui-autocomplete-input").attr("autocomplete","off"),this._on(this.element,{keydown:function(n){if(this.element.prop("readOnly"))return e=!0,s=!0,i=!0,undefined;e=!1,s=!1,i=!1;var a=t.ui.keyCode;switch(n.keyCode){case a.PAGE_UP:e=!0,this._move("previousPage",n);break;case a.PAGE_DOWN:e=!0,this._move("nextPage",n);break;case a.UP:e=!0,this._keyEvent("previous",n);break;case a.DOWN:e=!0,this._keyEvent("next",n);break;case a.ENTER:case a.NUMPAD_ENTER:this.menu.active&&(e=!0,n.preventDefault(),this.menu.select(n));break;case a.TAB:this.menu.active&&this.menu.select(n);break;case a.ESCAPE:this.menu.element.is(":visible")&&(this._value(this.term),this.close(n),n.preventDefault());break;default:i=!0,this._searchTimeout(n)}},keypress:function(s){if(e)return e=!1,(!this.isMultiLine||this.menu.element.is(":visible"))&&s.preventDefault(),undefined;if(!i){var n=t.ui.keyCode;switch(s.keyCode){case n.PAGE_UP:this._move("previousPage",s);break;case n.PAGE_DOWN:this._move("nextPage",s);break;case n.UP:this._keyEvent("previous",s);break;case n.DOWN:this._keyEvent("next",s)}}},input:function(t){return s?(s=!1,t.preventDefault(),undefined):(this._searchTimeout(t),undefined)},focus:function(){this.selectedItem=null,this.previous=this._value()},blur:function(t){return this.cancelBlur?(delete this.cancelBlur,undefined):(clearTimeout(this.searching),this.close(t),this._change(t),undefined)}}),this._initSource(),this.menu=t("<ul>").addClass("ui-autocomplete ui-front").appendTo(this._appendTo()).menu({role:null}).hide().data("ui-menu"),this._on(this.menu.element,{mousedown:function(e){e.preventDefault(),this.cancelBlur=!0,this._delay(function(){delete this.cancelBlur});var i=this.menu.element[0];t(e.target).closest(".ui-menu-item").length||this._delay(function(){var e=this;this.document.one("mousedown",function(s){s.target===e.element[0]||s.target===i||t.contains(i,s.target)||e.close()})})},menufocus:function(e,i){if(this.isNewMenu&&(this.isNewMenu=!1,e.originalEvent&&/^mouse/.test(e.originalEvent.type)))return this.menu.blur(),this.document.one("mousemove",function(){t(e.target).trigger(e.originalEvent)}),undefined;var s=i.item.data("ui-autocomplete-item");!1!==this._trigger("focus",e,{item:s})?e.originalEvent&&/^key/.test(e.originalEvent.type)&&this._value(s.value):this.liveRegion.text(s.value)},menuselect:function(t,e){var i=e.item.data("ui-autocomplete-item"),s=this.previous;this.element[0]!==this.document[0].activeElement&&(this.element.focus(),this.previous=s,this._delay(function(){this.previous=s,this.selectedItem=i})),!1!==this._trigger("select",t,{item:i})&&this._value(i.value),this.term=this._value(),this.close(t),this.selectedItem=i}}),this.liveRegion=t("<span>",{role:"status","aria-live":"polite"}).addClass("ui-helper-hidden-accessible").insertBefore(this.element),this._on(this.window,{beforeunload:function(){this.element.removeAttr("autocomplete")}})},_destroy:function(){clearTimeout(this.searching),this.element.removeClass("ui-autocomplete-input").removeAttr("autocomplete"),this.menu.element.remove(),this.liveRegion.remove()},_setOption:function(t,e){this._super(t,e),"source"===t&&this._initSource(),"appendTo"===t&&this.menu.element.appendTo(this._appendTo()),"disabled"===t&&e&&this.xhr&&this.xhr.abort()},_appendTo:function(){var e=this.options.appendTo;return e&&(e=e.jquery||e.nodeType?t(e):this.document.find(e).eq(0)),e||(e=this.element.closest(".ui-front")),e.length||(e=this.document[0].body),e},_initSource:function(){var e,i,s=this;t.isArray(this.options.source)?(e=this.options.source,this.source=function(i,s){s(t.ui.autocomplete.filter(e,i.term))}):"string"==typeof this.options.source?(i=this.options.source,this.source=function(e,n){s.xhr&&s.xhr.abort(),s.xhr=t.ajax({url:i,data:e,dataType:"json",success:function(t){n(t)},error:function(){n([])}})}):this.source=this.options.source},_searchTimeout:function(t){clearTimeout(this.searching),this.searching=this._delay(function(){this.term!==this._value()&&(this.selectedItem=null,this.search(null,t))},this.options.delay)},search:function(t,e){return t=null!=t?t:this._value(),this.term=this._value(),t.length<this.options.minLength?this.close(e):this._trigger("search",e)!==!1?this._search(t):undefined},_search:function(t){this.pending++,this.element.addClass("ui-autocomplete-loading"),this.cancelSearch=!1,this.source({term:t},this._response())},_response:function(){var t=this,i=++e;return function(s){i===e&&t.__response(s),t.pending--,t.pending||t.element.removeClass("ui-autocomplete-loading")}},__response:function(t){t&&(t=this._normalize(t)),this._trigger("response",null,{content:t}),!this.options.disabled&&t&&t.length&&!this.cancelSearch?(this._suggest(t),this._trigger("open")):this._close()},close:function(t){this.cancelSearch=!0,this._close(t)},_close:function(t){this.menu.element.is(":visible")&&(this.menu.element.hide(),this.menu.blur(),this.isNewMenu=!0,this._trigger("close",t))},_change:function(t){this.previous!==this._value()&&this._trigger("change",t,{item:this.selectedItem})},_normalize:function(e){return e.length&&e[0].label&&e[0].value?e:t.map(e,function(e){return"string"==typeof e?{label:e,value:e}:t.extend({label:e.label||e.value,value:e.value||e.label},e)})},_suggest:function(e){var i=this.menu.element.empty();this._renderMenu(i,e),this.isNewMenu=!0,this.menu.refresh(),i.show(),this._resizeMenu(),i.position(t.extend({of:this.element},this.options.position)),this.options.autoFocus&&this.menu.next()},_resizeMenu:function(){var t=this.menu.element;t.outerWidth(Math.max(t.width("").outerWidth()+1,this.element.outerWidth()))},_renderMenu:function(e,i){var s=this;t.each(i,function(t,i){s._renderItemData(e,i)})},_renderItemData:function(t,e){return this._renderItem(t,e).data("ui-autocomplete-item",e)},_renderItem:function(e,i){return t("<li>").append(t("<a>").text(i.label)).appendTo(e)},_move:function(t,e){return this.menu.element.is(":visible")?this.menu.isFirstItem()&&/^previous/.test(t)||this.menu.isLastItem()&&/^next/.test(t)?(this._value(this.term),this.menu.blur(),undefined):(this.menu[t](e),undefined):(this.search(null,e),undefined)},widget:function(){return this.menu.element},_value:function(){return this.valueMethod.apply(this.element,arguments)},_keyEvent:function(t,e){(!this.isMultiLine||this.menu.element.is(":visible"))&&(this._move(t,e),e.preventDefault())}}),t.extend(t.ui.autocomplete,{escapeRegex:function(t){return t.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g,"\\$&")},filter:function(e,i){var s=RegExp(t.ui.autocomplete.escapeRegex(i),"i");return t.grep(e,function(t){return s.test(t.label||t.value||t)})}}),t.widget("ui.autocomplete",t.ui.autocomplete,{options:{messages:{noResults:"No search results.",results:function(t){return t+(t>1?" results are":" result is")+" available, use up and down arrow keys to navigate."}}},__response:function(t){var e;this._superApply(arguments),this.options.disabled||this.cancelSearch||(e=t&&t.length?this.options.messages.results(t.length):this.options.messages.noResults,this.liveRegion.text(e))}})})(jQuery);(function(t){t.widget("ui.menu",{version:"1.10.3",defaultElement:"<ul>",delay:300,options:{icons:{submenu:"ui-icon-carat-1-e"},menus:"ul",position:{my:"left top",at:"right top"},role:"menu",blur:null,focus:null,select:null},_create:function(){this.activeMenu=this.element,this.mouseHandled=!1,this.element.uniqueId().addClass("ui-menu ui-widget ui-widget-content ui-corner-all").toggleClass("ui-menu-icons",!!this.element.find(".ui-icon").length).attr({role:this.options.role,tabIndex:0}).bind("click"+this.eventNamespace,t.proxy(function(t){this.options.disabled&&t.preventDefault()},this)),this.options.disabled&&this.element.addClass("ui-state-disabled").attr("aria-disabled","true"),this._on({"mousedown .ui-menu-item > a":function(t){t.preventDefault()},"click .ui-state-disabled > a":function(t){t.preventDefault()},"click .ui-menu-item:has(a)":function(e){var i=t(e.target).closest(".ui-menu-item");!this.mouseHandled&&i.not(".ui-state-disabled").length&&(this.mouseHandled=!0,this.select(e),i.has(".ui-menu").length?this.expand(e):this.element.is(":focus")||(this.element.trigger("focus",[!0]),this.active&&1===this.active.parents(".ui-menu").length&&clearTimeout(this.timer)))},"mouseenter .ui-menu-item":function(e){var i=t(e.currentTarget);i.siblings().children(".ui-state-active").removeClass("ui-state-active"),this.focus(e,i)},mouseleave:"collapseAll","mouseleave .ui-menu":"collapseAll",focus:function(t,e){var i=this.active||this.element.children(".ui-menu-item").eq(0);e||this.focus(t,i)},blur:function(e){this._delay(function(){t.contains(this.element[0],this.document[0].activeElement)||this.collapseAll(e)})},keydown:"_keydown"}),this.refresh(),this._on(this.document,{click:function(e){t(e.target).closest(".ui-menu").length||this.collapseAll(e),this.mouseHandled=!1}})},_destroy:function(){this.element.removeAttr("aria-activedescendant").find(".ui-menu").addBack().removeClass("ui-menu ui-widget ui-widget-content ui-corner-all ui-menu-icons").removeAttr("role").removeAttr("tabIndex").removeAttr("aria-labelledby").removeAttr("aria-expanded").removeAttr("aria-hidden").removeAttr("aria-disabled").removeUniqueId().show(),this.element.find(".ui-menu-item").removeClass("ui-menu-item").removeAttr("role").removeAttr("aria-disabled").children("a").removeUniqueId().removeClass("ui-corner-all ui-state-hover").removeAttr("tabIndex").removeAttr("role").removeAttr("aria-haspopup").children().each(function(){var e=t(this);e.data("ui-menu-submenu-carat")&&e.remove()}),this.element.find(".ui-menu-divider").removeClass("ui-menu-divider ui-widget-content")},_keydown:function(e){function i(t){return t.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g,"\\$&")}var s,n,a,o,r,h=!0;switch(e.keyCode){case t.ui.keyCode.PAGE_UP:this.previousPage(e);break;case t.ui.keyCode.PAGE_DOWN:this.nextPage(e);break;case t.ui.keyCode.HOME:this._move("first","first",e);break;case t.ui.keyCode.END:this._move("last","last",e);break;case t.ui.keyCode.UP:this.previous(e);break;case t.ui.keyCode.DOWN:this.next(e);break;case t.ui.keyCode.LEFT:this.collapse(e);break;case t.ui.keyCode.RIGHT:this.active&&!this.active.is(".ui-state-disabled")&&this.expand(e);break;case t.ui.keyCode.ENTER:case t.ui.keyCode.SPACE:this._activate(e);break;case t.ui.keyCode.ESCAPE:this.collapse(e);break;default:h=!1,n=this.previousFilter||"",a=String.fromCharCode(e.keyCode),o=!1,clearTimeout(this.filterTimer),a===n?o=!0:a=n+a,r=RegExp("^"+i(a),"i"),s=this.activeMenu.children(".ui-menu-item").filter(function(){return r.test(t(this).children("a").text())}),s=o&&-1!==s.index(this.active.next())?this.active.nextAll(".ui-menu-item"):s,s.length||(a=String.fromCharCode(e.keyCode),r=RegExp("^"+i(a),"i"),s=this.activeMenu.children(".ui-menu-item").filter(function(){return r.test(t(this).children("a").text())})),s.length?(this.focus(e,s),s.length>1?(this.previousFilter=a,this.filterTimer=this._delay(function(){delete this.previousFilter},1e3)):delete this.previousFilter):delete this.previousFilter}h&&e.preventDefault()},_activate:function(t){this.active.is(".ui-state-disabled")||(this.active.children("a[aria-haspopup='true']").length?this.expand(t):this.select(t))},refresh:function(){var e,i=this.options.icons.submenu,s=this.element.find(this.options.menus);s.filter(":not(.ui-menu)").addClass("ui-menu ui-widget ui-widget-content ui-corner-all").hide().attr({role:this.options.role,"aria-hidden":"true","aria-expanded":"false"}).each(function(){var e=t(this),s=e.prev("a"),n=t("<span>").addClass("ui-menu-icon ui-icon "+i).data("ui-menu-submenu-carat",!0);s.attr("aria-haspopup","true").prepend(n),e.attr("aria-labelledby",s.attr("id"))}),e=s.add(this.element),e.children(":not(.ui-menu-item):has(a)").addClass("ui-menu-item").attr("role","presentation").children("a").uniqueId().addClass("ui-corner-all").attr({tabIndex:-1,role:this._itemRole()}),e.children(":not(.ui-menu-item)").each(function(){var e=t(this);/[^\-\u2014\u2013\s]/.test(e.text())||e.addClass("ui-widget-content ui-menu-divider")}),e.children(".ui-state-disabled").attr("aria-disabled","true"),this.active&&!t.contains(this.element[0],this.active[0])&&this.blur()},_itemRole:function(){return{menu:"menuitem",listbox:"option"}[this.options.role]},_setOption:function(t,e){"icons"===t&&this.element.find(".ui-menu-icon").removeClass(this.options.icons.submenu).addClass(e.submenu),this._super(t,e)},focus:function(t,e){var i,s;this.blur(t,t&&"focus"===t.type),this._scrollIntoView(e),this.active=e.first(),s=this.active.children("a").addClass("ui-state-focus"),this.options.role&&this.element.attr("aria-activedescendant",s.attr("id")),this.active.parent().closest(".ui-menu-item").children("a:first").addClass("ui-state-active"),t&&"keydown"===t.type?this._close():this.timer=this._delay(function(){this._close()},this.delay),i=e.children(".ui-menu"),i.length&&/^mouse/.test(t.type)&&this._startOpening(i),this.activeMenu=e.parent(),this._trigger("focus",t,{item:e})},_scrollIntoView:function(e){var i,s,n,a,o,r;this._hasScroll()&&(i=parseFloat(t.css(this.activeMenu[0],"borderTopWidth"))||0,s=parseFloat(t.css(this.activeMenu[0],"paddingTop"))||0,n=e.offset().top-this.activeMenu.offset().top-i-s,a=this.activeMenu.scrollTop(),o=this.activeMenu.height(),r=e.height(),0>n?this.activeMenu.scrollTop(a+n):n+r>o&&this.activeMenu.scrollTop(a+n-o+r))},blur:function(t,e){e||clearTimeout(this.timer),this.active&&(this.active.children("a").removeClass("ui-state-focus"),this.active=null,this._trigger("blur",t,{item:this.active}))},_startOpening:function(t){clearTimeout(this.timer),"true"===t.attr("aria-hidden")&&(this.timer=this._delay(function(){this._close(),this._open(t)},this.delay))},_open:function(e){var i=t.extend({of:this.active},this.options.position);clearTimeout(this.timer),this.element.find(".ui-menu").not(e.parents(".ui-menu")).hide().attr("aria-hidden","true"),e.show().removeAttr("aria-hidden").attr("aria-expanded","true").position(i)},collapseAll:function(e,i){clearTimeout(this.timer),this.timer=this._delay(function(){var s=i?this.element:t(e&&e.target).closest(this.element.find(".ui-menu"));s.length||(s=this.element),this._close(s),this.blur(e),this.activeMenu=s},this.delay)},_close:function(t){t||(t=this.active?this.active.parent():this.element),t.find(".ui-menu").hide().attr("aria-hidden","true").attr("aria-expanded","false").end().find("a.ui-state-active").removeClass("ui-state-active")},collapse:function(t){var e=this.active&&this.active.parent().closest(".ui-menu-item",this.element);e&&e.length&&(this._close(),this.focus(t,e))},expand:function(t){var e=this.active&&this.active.children(".ui-menu ").children(".ui-menu-item").first();e&&e.length&&(this._open(e.parent()),this._delay(function(){this.focus(t,e)}))},next:function(t){this._move("next","first",t)},previous:function(t){this._move("prev","last",t)},isFirstItem:function(){return this.active&&!this.active.prevAll(".ui-menu-item").length},isLastItem:function(){return this.active&&!this.active.nextAll(".ui-menu-item").length},_move:function(t,e,i){var s;this.active&&(s="first"===t||"last"===t?this.active["first"===t?"prevAll":"nextAll"](".ui-menu-item").eq(-1):this.active[t+"All"](".ui-menu-item").eq(0)),s&&s.length&&this.active||(s=this.activeMenu.children(".ui-menu-item")[e]()),this.focus(i,s)},nextPage:function(e){var i,s,n;return this.active?(this.isLastItem()||(this._hasScroll()?(s=this.active.offset().top,n=this.element.height(),this.active.nextAll(".ui-menu-item").each(function(){return i=t(this),0>i.offset().top-s-n}),this.focus(e,i)):this.focus(e,this.activeMenu.children(".ui-menu-item")[this.active?"last":"first"]())),undefined):(this.next(e),undefined)},previousPage:function(e){var i,s,n;return this.active?(this.isFirstItem()||(this._hasScroll()?(s=this.active.offset().top,n=this.element.height(),this.active.prevAll(".ui-menu-item").each(function(){return i=t(this),i.offset().top-s+n>0}),this.focus(e,i)):this.focus(e,this.activeMenu.children(".ui-menu-item").first())),undefined):(this.next(e),undefined)},_hasScroll:function(){return this.element.outerHeight()<this.element.prop("scrollHeight")},select:function(e){this.active=this.active||t(e.target).closest(".ui-menu-item");var i={item:this.active};this.active.has(".ui-menu").length||this.collapseAll(e,!0),this._trigger("select",e,i)}})})(jQuery);(function(t,e){var i="ui-effects-";t.effects={effect:{}},function(t,e){function i(t,e,i){var s=u[e.type]||{};return null==t?i||!e.def?null:e.def:(t=s.floor?~~t:parseFloat(t),isNaN(t)?e.def:s.mod?(t+s.mod)%s.mod:0>t?0:t>s.max?s.max:t)}function s(i){var s=l(),n=s._rgba=[];return i=i.toLowerCase(),f(h,function(t,a){var o,r=a.re.exec(i),h=r&&a.parse(r),l=a.space||"rgba";return h?(o=s[l](h),s[c[l].cache]=o[c[l].cache],n=s._rgba=o._rgba,!1):e}),n.length?("0,0,0,0"===n.join()&&t.extend(n,a.transparent),s):a[i]}function n(t,e,i){return i=(i+1)%1,1>6*i?t+6*(e-t)*i:1>2*i?e:2>3*i?t+6*(e-t)*(2/3-i):t}var a,o="backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor",r=/^([\-+])=\s*(\d+\.?\d*)/,h=[{re:/rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,parse:function(t){return[t[1],t[2],t[3],t[4]]}},{re:/rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,parse:function(t){return[2.55*t[1],2.55*t[2],2.55*t[3],t[4]]}},{re:/#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/,parse:function(t){return[parseInt(t[1],16),parseInt(t[2],16),parseInt(t[3],16)]}},{re:/#([a-f0-9])([a-f0-9])([a-f0-9])/,parse:function(t){return[parseInt(t[1]+t[1],16),parseInt(t[2]+t[2],16),parseInt(t[3]+t[3],16)]}},{re:/hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,space:"hsla",parse:function(t){return[t[1],t[2]/100,t[3]/100,t[4]]}}],l=t.Color=function(e,i,s,n){return new t.Color.fn.parse(e,i,s,n)},c={rgba:{props:{red:{idx:0,type:"byte"},green:{idx:1,type:"byte"},blue:{idx:2,type:"byte"}}},hsla:{props:{hue:{idx:0,type:"degrees"},saturation:{idx:1,type:"percent"},lightness:{idx:2,type:"percent"}}}},u={"byte":{floor:!0,max:255},percent:{max:1},degrees:{mod:360,floor:!0}},d=l.support={},p=t("<p>")[0],f=t.each;p.style.cssText="background-color:rgba(1,1,1,.5)",d.rgba=p.style.backgroundColor.indexOf("rgba")>-1,f(c,function(t,e){e.cache="_"+t,e.props.alpha={idx:3,type:"percent",def:1}}),l.fn=t.extend(l.prototype,{parse:function(n,o,r,h){if(n===e)return this._rgba=[null,null,null,null],this;(n.jquery||n.nodeType)&&(n=t(n).css(o),o=e);var u=this,d=t.type(n),p=this._rgba=[];return o!==e&&(n=[n,o,r,h],d="array"),"string"===d?this.parse(s(n)||a._default):"array"===d?(f(c.rgba.props,function(t,e){p[e.idx]=i(n[e.idx],e)}),this):"object"===d?(n instanceof l?f(c,function(t,e){n[e.cache]&&(u[e.cache]=n[e.cache].slice())}):f(c,function(e,s){var a=s.cache;f(s.props,function(t,e){if(!u[a]&&s.to){if("alpha"===t||null==n[t])return;u[a]=s.to(u._rgba)}u[a][e.idx]=i(n[t],e,!0)}),u[a]&&0>t.inArray(null,u[a].slice(0,3))&&(u[a][3]=1,s.from&&(u._rgba=s.from(u[a])))}),this):e},is:function(t){var i=l(t),s=!0,n=this;return f(c,function(t,a){var o,r=i[a.cache];return r&&(o=n[a.cache]||a.to&&a.to(n._rgba)||[],f(a.props,function(t,i){return null!=r[i.idx]?s=r[i.idx]===o[i.idx]:e})),s}),s},_space:function(){var t=[],e=this;return f(c,function(i,s){e[s.cache]&&t.push(i)}),t.pop()},transition:function(t,e){var s=l(t),n=s._space(),a=c[n],o=0===this.alpha()?l("transparent"):this,r=o[a.cache]||a.to(o._rgba),h=r.slice();return s=s[a.cache],f(a.props,function(t,n){var a=n.idx,o=r[a],l=s[a],c=u[n.type]||{};null!==l&&(null===o?h[a]=l:(c.mod&&(l-o>c.mod/2?o+=c.mod:o-l>c.mod/2&&(o-=c.mod)),h[a]=i((l-o)*e+o,n)))}),this[n](h)},blend:function(e){if(1===this._rgba[3])return this;var i=this._rgba.slice(),s=i.pop(),n=l(e)._rgba;return l(t.map(i,function(t,e){return(1-s)*n[e]+s*t}))},toRgbaString:function(){var e="rgba(",i=t.map(this._rgba,function(t,e){return null==t?e>2?1:0:t});return 1===i[3]&&(i.pop(),e="rgb("),e+i.join()+")"},toHslaString:function(){var e="hsla(",i=t.map(this.hsla(),function(t,e){return null==t&&(t=e>2?1:0),e&&3>e&&(t=Math.round(100*t)+"%"),t});return 1===i[3]&&(i.pop(),e="hsl("),e+i.join()+")"},toHexString:function(e){var i=this._rgba.slice(),s=i.pop();return e&&i.push(~~(255*s)),"#"+t.map(i,function(t){return t=(t||0).toString(16),1===t.length?"0"+t:t}).join("")},toString:function(){return 0===this._rgba[3]?"transparent":this.toRgbaString()}}),l.fn.parse.prototype=l.fn,c.hsla.to=function(t){if(null==t[0]||null==t[1]||null==t[2])return[null,null,null,t[3]];var e,i,s=t[0]/255,n=t[1]/255,a=t[2]/255,o=t[3],r=Math.max(s,n,a),h=Math.min(s,n,a),l=r-h,c=r+h,u=.5*c;return e=h===r?0:s===r?60*(n-a)/l+360:n===r?60*(a-s)/l+120:60*(s-n)/l+240,i=0===l?0:.5>=u?l/c:l/(2-c),[Math.round(e)%360,i,u,null==o?1:o]},c.hsla.from=function(t){if(null==t[0]||null==t[1]||null==t[2])return[null,null,null,t[3]];var e=t[0]/360,i=t[1],s=t[2],a=t[3],o=.5>=s?s*(1+i):s+i-s*i,r=2*s-o;return[Math.round(255*n(r,o,e+1/3)),Math.round(255*n(r,o,e)),Math.round(255*n(r,o,e-1/3)),a]},f(c,function(s,n){var a=n.props,o=n.cache,h=n.to,c=n.from;l.fn[s]=function(s){if(h&&!this[o]&&(this[o]=h(this._rgba)),s===e)return this[o].slice();var n,r=t.type(s),u="array"===r||"object"===r?s:arguments,d=this[o].slice();return f(a,function(t,e){var s=u["object"===r?t:e.idx];null==s&&(s=d[e.idx]),d[e.idx]=i(s,e)}),c?(n=l(c(d)),n[o]=d,n):l(d)},f(a,function(e,i){l.fn[e]||(l.fn[e]=function(n){var a,o=t.type(n),h="alpha"===e?this._hsla?"hsla":"rgba":s,l=this[h](),c=l[i.idx];return"undefined"===o?c:("function"===o&&(n=n.call(this,c),o=t.type(n)),null==n&&i.empty?this:("string"===o&&(a=r.exec(n),a&&(n=c+parseFloat(a[2])*("+"===a[1]?1:-1))),l[i.idx]=n,this[h](l)))})})}),l.hook=function(e){var i=e.split(" ");f(i,function(e,i){t.cssHooks[i]={set:function(e,n){var a,o,r="";if("transparent"!==n&&("string"!==t.type(n)||(a=s(n)))){if(n=l(a||n),!d.rgba&&1!==n._rgba[3]){for(o="backgroundColor"===i?e.parentNode:e;(""===r||"transparent"===r)&&o&&o.style;)try{r=t.css(o,"backgroundColor"),o=o.parentNode}catch(h){}n=n.blend(r&&"transparent"!==r?r:"_default")}n=n.toRgbaString()}try{e.style[i]=n}catch(h){}}},t.fx.step[i]=function(e){e.colorInit||(e.start=l(e.elem,i),e.end=l(e.end),e.colorInit=!0),t.cssHooks[i].set(e.elem,e.start.transition(e.end,e.pos))}})},l.hook(o),t.cssHooks.borderColor={expand:function(t){var e={};return f(["Top","Right","Bottom","Left"],function(i,s){e["border"+s+"Color"]=t}),e}},a=t.Color.names={aqua:"#00ffff",black:"#000000",blue:"#0000ff",fuchsia:"#ff00ff",gray:"#808080",green:"#008000",lime:"#00ff00",maroon:"#800000",navy:"#000080",olive:"#808000",purple:"#800080",red:"#ff0000",silver:"#c0c0c0",teal:"#008080",white:"#ffffff",yellow:"#ffff00",transparent:[null,null,null,0],_default:"#ffffff"}}(jQuery),function(){function i(e){var i,s,n=e.ownerDocument.defaultView?e.ownerDocument.defaultView.getComputedStyle(e,null):e.currentStyle,a={};if(n&&n.length&&n[0]&&n[n[0]])for(s=n.length;s--;)i=n[s],"string"==typeof n[i]&&(a[t.camelCase(i)]=n[i]);else for(i in n)"string"==typeof n[i]&&(a[i]=n[i]);return a}function s(e,i){var s,n,o={};for(s in i)n=i[s],e[s]!==n&&(a[s]||(t.fx.step[s]||!isNaN(parseFloat(n)))&&(o[s]=n));return o}var n=["add","remove","toggle"],a={border:1,borderBottom:1,borderColor:1,borderLeft:1,borderRight:1,borderTop:1,borderWidth:1,margin:1,padding:1};t.each(["borderLeftStyle","borderRightStyle","borderBottomStyle","borderTopStyle"],function(e,i){t.fx.step[i]=function(t){("none"!==t.end&&!t.setAttr||1===t.pos&&!t.setAttr)&&(jQuery.style(t.elem,i,t.end),t.setAttr=!0)}}),t.fn.addBack||(t.fn.addBack=function(t){return this.add(null==t?this.prevObject:this.prevObject.filter(t))}),t.effects.animateClass=function(e,a,o,r){var h=t.speed(a,o,r);return this.queue(function(){var a,o=t(this),r=o.attr("class")||"",l=h.children?o.find("*").addBack():o;l=l.map(function(){var e=t(this);return{el:e,start:i(this)}}),a=function(){t.each(n,function(t,i){e[i]&&o[i+"Class"](e[i])})},a(),l=l.map(function(){return this.end=i(this.el[0]),this.diff=s(this.start,this.end),this}),o.attr("class",r),l=l.map(function(){var e=this,i=t.Deferred(),s=t.extend({},h,{queue:!1,complete:function(){i.resolve(e)}});return this.el.animate(this.diff,s),i.promise()}),t.when.apply(t,l.get()).done(function(){a(),t.each(arguments,function(){var e=this.el;t.each(this.diff,function(t){e.css(t,"")})}),h.complete.call(o[0])})})},t.fn.extend({addClass:function(e){return function(i,s,n,a){return s?t.effects.animateClass.call(this,{add:i},s,n,a):e.apply(this,arguments)}}(t.fn.addClass),removeClass:function(e){return function(i,s,n,a){return arguments.length>1?t.effects.animateClass.call(this,{remove:i},s,n,a):e.apply(this,arguments)}}(t.fn.removeClass),toggleClass:function(i){return function(s,n,a,o,r){return"boolean"==typeof n||n===e?a?t.effects.animateClass.call(this,n?{add:s}:{remove:s},a,o,r):i.apply(this,arguments):t.effects.animateClass.call(this,{toggle:s},n,a,o)}}(t.fn.toggleClass),switchClass:function(e,i,s,n,a){return t.effects.animateClass.call(this,{add:i,remove:e},s,n,a)}})}(),function(){function s(e,i,s,n){return t.isPlainObject(e)&&(i=e,e=e.effect),e={effect:e},null==i&&(i={}),t.isFunction(i)&&(n=i,s=null,i={}),("number"==typeof i||t.fx.speeds[i])&&(n=s,s=i,i={}),t.isFunction(s)&&(n=s,s=null),i&&t.extend(e,i),s=s||i.duration,e.duration=t.fx.off?0:"number"==typeof s?s:s in t.fx.speeds?t.fx.speeds[s]:t.fx.speeds._default,e.complete=n||i.complete,e}function n(e){return!e||"number"==typeof e||t.fx.speeds[e]?!0:"string"!=typeof e||t.effects.effect[e]?t.isFunction(e)?!0:"object"!=typeof e||e.effect?!1:!0:!0}t.extend(t.effects,{version:"1.10.3",save:function(t,e){for(var s=0;e.length>s;s++)null!==e[s]&&t.data(i+e[s],t[0].style[e[s]])},restore:function(t,s){var n,a;for(a=0;s.length>a;a++)null!==s[a]&&(n=t.data(i+s[a]),n===e&&(n=""),t.css(s[a],n))},setMode:function(t,e){return"toggle"===e&&(e=t.is(":hidden")?"show":"hide"),e},getBaseline:function(t,e){var i,s;switch(t[0]){case"top":i=0;break;case"middle":i=.5;break;case"bottom":i=1;break;default:i=t[0]/e.height}switch(t[1]){case"left":s=0;break;case"center":s=.5;break;case"right":s=1;break;default:s=t[1]/e.width}return{x:s,y:i}},createWrapper:function(e){if(e.parent().is(".ui-effects-wrapper"))return e.parent();var i={width:e.outerWidth(!0),height:e.outerHeight(!0),"float":e.css("float")},s=t("<div></div>").addClass("ui-effects-wrapper").css({fontSize:"100%",background:"transparent",border:"none",margin:0,padding:0}),n={width:e.width(),height:e.height()},a=document.activeElement;try{a.id}catch(o){a=document.body}return e.wrap(s),(e[0]===a||t.contains(e[0],a))&&t(a).focus(),s=e.parent(),"static"===e.css("position")?(s.css({position:"relative"}),e.css({position:"relative"})):(t.extend(i,{position:e.css("position"),zIndex:e.css("z-index")}),t.each(["top","left","bottom","right"],function(t,s){i[s]=e.css(s),isNaN(parseInt(i[s],10))&&(i[s]="auto")}),e.css({position:"relative",top:0,left:0,right:"auto",bottom:"auto"})),e.css(n),s.css(i).show()},removeWrapper:function(e){var i=document.activeElement;return e.parent().is(".ui-effects-wrapper")&&(e.parent().replaceWith(e),(e[0]===i||t.contains(e[0],i))&&t(i).focus()),e},setTransition:function(e,i,s,n){return n=n||{},t.each(i,function(t,i){var a=e.cssUnit(i);a[0]>0&&(n[i]=a[0]*s+a[1])}),n}}),t.fn.extend({effect:function(){function e(e){function s(){t.isFunction(a)&&a.call(n[0]),t.isFunction(e)&&e()}var n=t(this),a=i.complete,r=i.mode;(n.is(":hidden")?"hide"===r:"show"===r)?(n[r](),s()):o.call(n[0],i,s)}var i=s.apply(this,arguments),n=i.mode,a=i.queue,o=t.effects.effect[i.effect];return t.fx.off||!o?n?this[n](i.duration,i.complete):this.each(function(){i.complete&&i.complete.call(this)}):a===!1?this.each(e):this.queue(a||"fx",e)},show:function(t){return function(e){if(n(e))return t.apply(this,arguments);var i=s.apply(this,arguments);return i.mode="show",this.effect.call(this,i)}}(t.fn.show),hide:function(t){return function(e){if(n(e))return t.apply(this,arguments);var i=s.apply(this,arguments);return i.mode="hide",this.effect.call(this,i)}}(t.fn.hide),toggle:function(t){return function(e){if(n(e)||"boolean"==typeof e)return t.apply(this,arguments);var i=s.apply(this,arguments);return i.mode="toggle",this.effect.call(this,i)}}(t.fn.toggle),cssUnit:function(e){var i=this.css(e),s=[];return t.each(["em","px","%","pt"],function(t,e){i.indexOf(e)>0&&(s=[parseFloat(i),e])}),s}})}(),function(){var e={};t.each(["Quad","Cubic","Quart","Quint","Expo"],function(t,i){e[i]=function(e){return Math.pow(e,t+2)}}),t.extend(e,{Sine:function(t){return 1-Math.cos(t*Math.PI/2)},Circ:function(t){return 1-Math.sqrt(1-t*t)},Elastic:function(t){return 0===t||1===t?t:-Math.pow(2,8*(t-1))*Math.sin((80*(t-1)-7.5)*Math.PI/15)},Back:function(t){return t*t*(3*t-2)},Bounce:function(t){for(var e,i=4;((e=Math.pow(2,--i))-1)/11>t;);return 1/Math.pow(4,3-i)-7.5625*Math.pow((3*e-2)/22-t,2)}}),t.each(e,function(e,i){t.easing["easeIn"+e]=i,t.easing["easeOut"+e]=function(t){return 1-i(1-t)},t.easing["easeInOut"+e]=function(t){return.5>t?i(2*t)/2:1-i(-2*t+2)/2}})}()})(jQuery);
/*! Hammer.JS - v2.0.8 - 2016-04-23
 * http://hammerjs.github.io/
 *
 * Copyright (c) 2016 Jorik Tangelder;
 * Licensed under the MIT license */
!function(a,b,c,d){"use strict";function e(a,b,c){return setTimeout(j(a,c),b)}function f(a,b,c){return Array.isArray(a)?(g(a,c[b],c),!0):!1}function g(a,b,c){var e;if(a)if(a.forEach)a.forEach(b,c);else if(a.length!==d)for(e=0;e<a.length;)b.call(c,a[e],e,a),e++;else for(e in a)a.hasOwnProperty(e)&&b.call(c,a[e],e,a)}function h(b,c,d){var e="DEPRECATED METHOD: "+c+"\n"+d+" AT \n";return function(){var c=new Error("get-stack-trace"),d=c&&c.stack?c.stack.replace(/^[^\(]+?[\n$]/gm,"").replace(/^\s+at\s+/gm,"").replace(/^Object.<anonymous>\s*\(/gm,"{anonymous}()@"):"Unknown Stack Trace",f=a.console&&(a.console.warn||a.console.log);return f&&f.call(a.console,e,d),b.apply(this,arguments)}}function i(a,b,c){var d,e=b.prototype;d=a.prototype=Object.create(e),d.constructor=a,d._super=e,c&&la(d,c)}function j(a,b){return function(){return a.apply(b,arguments)}}function k(a,b){return typeof a==oa?a.apply(b?b[0]||d:d,b):a}function l(a,b){return a===d?b:a}function m(a,b,c){g(q(b),function(b){a.addEventListener(b,c,!1)})}function n(a,b,c){g(q(b),function(b){a.removeEventListener(b,c,!1)})}function o(a,b){for(;a;){if(a==b)return!0;a=a.parentNode}return!1}function p(a,b){return a.indexOf(b)>-1}function q(a){return a.trim().split(/\s+/g)}function r(a,b,c){if(a.indexOf&&!c)return a.indexOf(b);for(var d=0;d<a.length;){if(c&&a[d][c]==b||!c&&a[d]===b)return d;d++}return-1}function s(a){return Array.prototype.slice.call(a,0)}function t(a,b,c){for(var d=[],e=[],f=0;f<a.length;){var g=b?a[f][b]:a[f];r(e,g)<0&&d.push(a[f]),e[f]=g,f++}return c&&(d=b?d.sort(function(a,c){return a[b]>c[b]}):d.sort()),d}function u(a,b){for(var c,e,f=b[0].toUpperCase()+b.slice(1),g=0;g<ma.length;){if(c=ma[g],e=c?c+f:b,e in a)return e;g++}return d}function v(){return ua++}function w(b){var c=b.ownerDocument||b;return c.defaultView||c.parentWindow||a}function x(a,b){var c=this;this.manager=a,this.callback=b,this.element=a.element,this.target=a.options.inputTarget,this.domHandler=function(b){k(a.options.enable,[a])&&c.handler(b)},this.init()}function y(a){var b,c=a.options.inputClass;return new(b=c?c:xa?M:ya?P:wa?R:L)(a,z)}function z(a,b,c){var d=c.pointers.length,e=c.changedPointers.length,f=b&Ea&&d-e===0,g=b&(Ga|Ha)&&d-e===0;c.isFirst=!!f,c.isFinal=!!g,f&&(a.session={}),c.eventType=b,A(a,c),a.emit("hammer.input",c),a.recognize(c),a.session.prevInput=c}function A(a,b){var c=a.session,d=b.pointers,e=d.length;c.firstInput||(c.firstInput=D(b)),e>1&&!c.firstMultiple?c.firstMultiple=D(b):1===e&&(c.firstMultiple=!1);var f=c.firstInput,g=c.firstMultiple,h=g?g.center:f.center,i=b.center=E(d);b.timeStamp=ra(),b.deltaTime=b.timeStamp-f.timeStamp,b.angle=I(h,i),b.distance=H(h,i),B(c,b),b.offsetDirection=G(b.deltaX,b.deltaY);var j=F(b.deltaTime,b.deltaX,b.deltaY);b.overallVelocityX=j.x,b.overallVelocityY=j.y,b.overallVelocity=qa(j.x)>qa(j.y)?j.x:j.y,b.scale=g?K(g.pointers,d):1,b.rotation=g?J(g.pointers,d):0,b.maxPointers=c.prevInput?b.pointers.length>c.prevInput.maxPointers?b.pointers.length:c.prevInput.maxPointers:b.pointers.length,C(c,b);var k=a.element;o(b.srcEvent.target,k)&&(k=b.srcEvent.target),b.target=k}function B(a,b){var c=b.center,d=a.offsetDelta||{},e=a.prevDelta||{},f=a.prevInput||{};b.eventType!==Ea&&f.eventType!==Ga||(e=a.prevDelta={x:f.deltaX||0,y:f.deltaY||0},d=a.offsetDelta={x:c.x,y:c.y}),b.deltaX=e.x+(c.x-d.x),b.deltaY=e.y+(c.y-d.y)}function C(a,b){var c,e,f,g,h=a.lastInterval||b,i=b.timeStamp-h.timeStamp;if(b.eventType!=Ha&&(i>Da||h.velocity===d)){var j=b.deltaX-h.deltaX,k=b.deltaY-h.deltaY,l=F(i,j,k);e=l.x,f=l.y,c=qa(l.x)>qa(l.y)?l.x:l.y,g=G(j,k),a.lastInterval=b}else c=h.velocity,e=h.velocityX,f=h.velocityY,g=h.direction;b.velocity=c,b.velocityX=e,b.velocityY=f,b.direction=g}function D(a){for(var b=[],c=0;c<a.pointers.length;)b[c]={clientX:pa(a.pointers[c].clientX),clientY:pa(a.pointers[c].clientY)},c++;return{timeStamp:ra(),pointers:b,center:E(b),deltaX:a.deltaX,deltaY:a.deltaY}}function E(a){var b=a.length;if(1===b)return{x:pa(a[0].clientX),y:pa(a[0].clientY)};for(var c=0,d=0,e=0;b>e;)c+=a[e].clientX,d+=a[e].clientY,e++;return{x:pa(c/b),y:pa(d/b)}}function F(a,b,c){return{x:b/a||0,y:c/a||0}}function G(a,b){return a===b?Ia:qa(a)>=qa(b)?0>a?Ja:Ka:0>b?La:Ma}function H(a,b,c){c||(c=Qa);var d=b[c[0]]-a[c[0]],e=b[c[1]]-a[c[1]];return Math.sqrt(d*d+e*e)}function I(a,b,c){c||(c=Qa);var d=b[c[0]]-a[c[0]],e=b[c[1]]-a[c[1]];return 180*Math.atan2(e,d)/Math.PI}function J(a,b){return I(b[1],b[0],Ra)+I(a[1],a[0],Ra)}function K(a,b){return H(b[0],b[1],Ra)/H(a[0],a[1],Ra)}function L(){this.evEl=Ta,this.evWin=Ua,this.pressed=!1,x.apply(this,arguments)}function M(){this.evEl=Xa,this.evWin=Ya,x.apply(this,arguments),this.store=this.manager.session.pointerEvents=[]}function N(){this.evTarget=$a,this.evWin=_a,this.started=!1,x.apply(this,arguments)}function O(a,b){var c=s(a.touches),d=s(a.changedTouches);return b&(Ga|Ha)&&(c=t(c.concat(d),"identifier",!0)),[c,d]}function P(){this.evTarget=bb,this.targetIds={},x.apply(this,arguments)}function Q(a,b){var c=s(a.touches),d=this.targetIds;if(b&(Ea|Fa)&&1===c.length)return d[c[0].identifier]=!0,[c,c];var e,f,g=s(a.changedTouches),h=[],i=this.target;if(f=c.filter(function(a){return o(a.target,i)}),b===Ea)for(e=0;e<f.length;)d[f[e].identifier]=!0,e++;for(e=0;e<g.length;)d[g[e].identifier]&&h.push(g[e]),b&(Ga|Ha)&&delete d[g[e].identifier],e++;return h.length?[t(f.concat(h),"identifier",!0),h]:void 0}function R(){x.apply(this,arguments);var a=j(this.handler,this);this.touch=new P(this.manager,a),this.mouse=new L(this.manager,a),this.primaryTouch=null,this.lastTouches=[]}function S(a,b){a&Ea?(this.primaryTouch=b.changedPointers[0].identifier,T.call(this,b)):a&(Ga|Ha)&&T.call(this,b)}function T(a){var b=a.changedPointers[0];if(b.identifier===this.primaryTouch){var c={x:b.clientX,y:b.clientY};this.lastTouches.push(c);var d=this.lastTouches,e=function(){var a=d.indexOf(c);a>-1&&d.splice(a,1)};setTimeout(e,cb)}}function U(a){for(var b=a.srcEvent.clientX,c=a.srcEvent.clientY,d=0;d<this.lastTouches.length;d++){var e=this.lastTouches[d],f=Math.abs(b-e.x),g=Math.abs(c-e.y);if(db>=f&&db>=g)return!0}return!1}function V(a,b){this.manager=a,this.set(b)}function W(a){if(p(a,jb))return jb;var b=p(a,kb),c=p(a,lb);return b&&c?jb:b||c?b?kb:lb:p(a,ib)?ib:hb}function X(){if(!fb)return!1;var b={},c=a.CSS&&a.CSS.supports;return["auto","manipulation","pan-y","pan-x","pan-x pan-y","none"].forEach(function(d){b[d]=c?a.CSS.supports("touch-action",d):!0}),b}function Y(a){this.options=la({},this.defaults,a||{}),this.id=v(),this.manager=null,this.options.enable=l(this.options.enable,!0),this.state=nb,this.simultaneous={},this.requireFail=[]}function Z(a){return a&sb?"cancel":a&qb?"end":a&pb?"move":a&ob?"start":""}function $(a){return a==Ma?"down":a==La?"up":a==Ja?"left":a==Ka?"right":""}function _(a,b){var c=b.manager;return c?c.get(a):a}function aa(){Y.apply(this,arguments)}function ba(){aa.apply(this,arguments),this.pX=null,this.pY=null}function ca(){aa.apply(this,arguments)}function da(){Y.apply(this,arguments),this._timer=null,this._input=null}function ea(){aa.apply(this,arguments)}function fa(){aa.apply(this,arguments)}function ga(){Y.apply(this,arguments),this.pTime=!1,this.pCenter=!1,this._timer=null,this._input=null,this.count=0}function ha(a,b){return b=b||{},b.recognizers=l(b.recognizers,ha.defaults.preset),new ia(a,b)}function ia(a,b){this.options=la({},ha.defaults,b||{}),this.options.inputTarget=this.options.inputTarget||a,this.handlers={},this.session={},this.recognizers=[],this.oldCssProps={},this.element=a,this.input=y(this),this.touchAction=new V(this,this.options.touchAction),ja(this,!0),g(this.options.recognizers,function(a){var b=this.add(new a[0](a[1]));a[2]&&b.recognizeWith(a[2]),a[3]&&b.requireFailure(a[3])},this)}function ja(a,b){var c=a.element;if(c.style){var d;g(a.options.cssProps,function(e,f){d=u(c.style,f),b?(a.oldCssProps[d]=c.style[d],c.style[d]=e):c.style[d]=a.oldCssProps[d]||""}),b||(a.oldCssProps={})}}function ka(a,c){var d=b.createEvent("Event");d.initEvent(a,!0,!0),d.gesture=c,c.target.dispatchEvent(d)}var la,ma=["","webkit","Moz","MS","ms","o"],na=b.createElement("div"),oa="function",pa=Math.round,qa=Math.abs,ra=Date.now;la="function"!=typeof Object.assign?function(a){if(a===d||null===a)throw new TypeError("Cannot convert undefined or null to object");for(var b=Object(a),c=1;c<arguments.length;c++){var e=arguments[c];if(e!==d&&null!==e)for(var f in e)e.hasOwnProperty(f)&&(b[f]=e[f])}return b}:Object.assign;var sa=h(function(a,b,c){for(var e=Object.keys(b),f=0;f<e.length;)(!c||c&&a[e[f]]===d)&&(a[e[f]]=b[e[f]]),f++;return a},"extend","Use `assign`."),ta=h(function(a,b){return sa(a,b,!0)},"merge","Use `assign`."),ua=1,va=/mobile|tablet|ip(ad|hone|od)|android/i,wa="ontouchstart"in a,xa=u(a,"PointerEvent")!==d,ya=wa&&va.test(navigator.userAgent),za="touch",Aa="pen",Ba="mouse",Ca="kinect",Da=25,Ea=1,Fa=2,Ga=4,Ha=8,Ia=1,Ja=2,Ka=4,La=8,Ma=16,Na=Ja|Ka,Oa=La|Ma,Pa=Na|Oa,Qa=["x","y"],Ra=["clientX","clientY"];x.prototype={handler:function(){},init:function(){this.evEl&&m(this.element,this.evEl,this.domHandler),this.evTarget&&m(this.target,this.evTarget,this.domHandler),this.evWin&&m(w(this.element),this.evWin,this.domHandler)},destroy:function(){this.evEl&&n(this.element,this.evEl,this.domHandler),this.evTarget&&n(this.target,this.evTarget,this.domHandler),this.evWin&&n(w(this.element),this.evWin,this.domHandler)}};var Sa={mousedown:Ea,mousemove:Fa,mouseup:Ga},Ta="mousedown",Ua="mousemove mouseup";i(L,x,{handler:function(a){var b=Sa[a.type];b&Ea&&0===a.button&&(this.pressed=!0),b&Fa&&1!==a.which&&(b=Ga),this.pressed&&(b&Ga&&(this.pressed=!1),this.callback(this.manager,b,{pointers:[a],changedPointers:[a],pointerType:Ba,srcEvent:a}))}});var Va={pointerdown:Ea,pointermove:Fa,pointerup:Ga,pointercancel:Ha,pointerout:Ha},Wa={2:za,3:Aa,4:Ba,5:Ca},Xa="pointerdown",Ya="pointermove pointerup pointercancel";a.MSPointerEvent&&!a.PointerEvent&&(Xa="MSPointerDown",Ya="MSPointerMove MSPointerUp MSPointerCancel"),i(M,x,{handler:function(a){var b=this.store,c=!1,d=a.type.toLowerCase().replace("ms",""),e=Va[d],f=Wa[a.pointerType]||a.pointerType,g=f==za,h=r(b,a.pointerId,"pointerId");e&Ea&&(0===a.button||g)?0>h&&(b.push(a),h=b.length-1):e&(Ga|Ha)&&(c=!0),0>h||(b[h]=a,this.callback(this.manager,e,{pointers:b,changedPointers:[a],pointerType:f,srcEvent:a}),c&&b.splice(h,1))}});var Za={touchstart:Ea,touchmove:Fa,touchend:Ga,touchcancel:Ha},$a="touchstart",_a="touchstart touchmove touchend touchcancel";i(N,x,{handler:function(a){var b=Za[a.type];if(b===Ea&&(this.started=!0),this.started){var c=O.call(this,a,b);b&(Ga|Ha)&&c[0].length-c[1].length===0&&(this.started=!1),this.callback(this.manager,b,{pointers:c[0],changedPointers:c[1],pointerType:za,srcEvent:a})}}});var ab={touchstart:Ea,touchmove:Fa,touchend:Ga,touchcancel:Ha},bb="touchstart touchmove touchend touchcancel";i(P,x,{handler:function(a){var b=ab[a.type],c=Q.call(this,a,b);c&&this.callback(this.manager,b,{pointers:c[0],changedPointers:c[1],pointerType:za,srcEvent:a})}});var cb=2500,db=25;i(R,x,{handler:function(a,b,c){var d=c.pointerType==za,e=c.pointerType==Ba;if(!(e&&c.sourceCapabilities&&c.sourceCapabilities.firesTouchEvents)){if(d)S.call(this,b,c);else if(e&&U.call(this,c))return;this.callback(a,b,c)}},destroy:function(){this.touch.destroy(),this.mouse.destroy()}});var eb=u(na.style,"touchAction"),fb=eb!==d,gb="compute",hb="auto",ib="manipulation",jb="none",kb="pan-x",lb="pan-y",mb=X();V.prototype={set:function(a){a==gb&&(a=this.compute()),fb&&this.manager.element.style&&mb[a]&&(this.manager.element.style[eb]=a),this.actions=a.toLowerCase().trim()},update:function(){this.set(this.manager.options.touchAction)},compute:function(){var a=[];return g(this.manager.recognizers,function(b){k(b.options.enable,[b])&&(a=a.concat(b.getTouchAction()))}),W(a.join(" "))},preventDefaults:function(a){var b=a.srcEvent,c=a.offsetDirection;if(this.manager.session.prevented)return void b.preventDefault();var d=this.actions,e=p(d,jb)&&!mb[jb],f=p(d,lb)&&!mb[lb],g=p(d,kb)&&!mb[kb];if(e){var h=1===a.pointers.length,i=a.distance<2,j=a.deltaTime<250;if(h&&i&&j)return}return g&&f?void 0:e||f&&c&Na||g&&c&Oa?this.preventSrc(b):void 0},preventSrc:function(a){this.manager.session.prevented=!0,a.preventDefault()}};var nb=1,ob=2,pb=4,qb=8,rb=qb,sb=16,tb=32;Y.prototype={defaults:{},set:function(a){return la(this.options,a),this.manager&&this.manager.touchAction.update(),this},recognizeWith:function(a){if(f(a,"recognizeWith",this))return this;var b=this.simultaneous;return a=_(a,this),b[a.id]||(b[a.id]=a,a.recognizeWith(this)),this},dropRecognizeWith:function(a){return f(a,"dropRecognizeWith",this)?this:(a=_(a,this),delete this.simultaneous[a.id],this)},requireFailure:function(a){if(f(a,"requireFailure",this))return this;var b=this.requireFail;return a=_(a,this),-1===r(b,a)&&(b.push(a),a.requireFailure(this)),this},dropRequireFailure:function(a){if(f(a,"dropRequireFailure",this))return this;a=_(a,this);var b=r(this.requireFail,a);return b>-1&&this.requireFail.splice(b,1),this},hasRequireFailures:function(){return this.requireFail.length>0},canRecognizeWith:function(a){return!!this.simultaneous[a.id]},emit:function(a){function b(b){c.manager.emit(b,a)}var c=this,d=this.state;qb>d&&b(c.options.event+Z(d)),b(c.options.event),a.additionalEvent&&b(a.additionalEvent),d>=qb&&b(c.options.event+Z(d))},tryEmit:function(a){return this.canEmit()?this.emit(a):void(this.state=tb)},canEmit:function(){for(var a=0;a<this.requireFail.length;){if(!(this.requireFail[a].state&(tb|nb)))return!1;a++}return!0},recognize:function(a){var b=la({},a);return k(this.options.enable,[this,b])?(this.state&(rb|sb|tb)&&(this.state=nb),this.state=this.process(b),void(this.state&(ob|pb|qb|sb)&&this.tryEmit(b))):(this.reset(),void(this.state=tb))},process:function(a){},getTouchAction:function(){},reset:function(){}},i(aa,Y,{defaults:{pointers:1},attrTest:function(a){var b=this.options.pointers;return 0===b||a.pointers.length===b},process:function(a){var b=this.state,c=a.eventType,d=b&(ob|pb),e=this.attrTest(a);return d&&(c&Ha||!e)?b|sb:d||e?c&Ga?b|qb:b&ob?b|pb:ob:tb}}),i(ba,aa,{defaults:{event:"pan",threshold:10,pointers:1,direction:Pa},getTouchAction:function(){var a=this.options.direction,b=[];return a&Na&&b.push(lb),a&Oa&&b.push(kb),b},directionTest:function(a){var b=this.options,c=!0,d=a.distance,e=a.direction,f=a.deltaX,g=a.deltaY;return e&b.direction||(b.direction&Na?(e=0===f?Ia:0>f?Ja:Ka,c=f!=this.pX,d=Math.abs(a.deltaX)):(e=0===g?Ia:0>g?La:Ma,c=g!=this.pY,d=Math.abs(a.deltaY))),a.direction=e,c&&d>b.threshold&&e&b.direction},attrTest:function(a){return aa.prototype.attrTest.call(this,a)&&(this.state&ob||!(this.state&ob)&&this.directionTest(a))},emit:function(a){this.pX=a.deltaX,this.pY=a.deltaY;var b=$(a.direction);b&&(a.additionalEvent=this.options.event+b),this._super.emit.call(this,a)}}),i(ca,aa,{defaults:{event:"pinch",threshold:0,pointers:2},getTouchAction:function(){return[jb]},attrTest:function(a){return this._super.attrTest.call(this,a)&&(Math.abs(a.scale-1)>this.options.threshold||this.state&ob)},emit:function(a){if(1!==a.scale){var b=a.scale<1?"in":"out";a.additionalEvent=this.options.event+b}this._super.emit.call(this,a)}}),i(da,Y,{defaults:{event:"press",pointers:1,time:251,threshold:9},getTouchAction:function(){return[hb]},process:function(a){var b=this.options,c=a.pointers.length===b.pointers,d=a.distance<b.threshold,f=a.deltaTime>b.time;if(this._input=a,!d||!c||a.eventType&(Ga|Ha)&&!f)this.reset();else if(a.eventType&Ea)this.reset(),this._timer=e(function(){this.state=rb,this.tryEmit()},b.time,this);else if(a.eventType&Ga)return rb;return tb},reset:function(){clearTimeout(this._timer)},emit:function(a){this.state===rb&&(a&&a.eventType&Ga?this.manager.emit(this.options.event+"up",a):(this._input.timeStamp=ra(),this.manager.emit(this.options.event,this._input)))}}),i(ea,aa,{defaults:{event:"rotate",threshold:0,pointers:2},getTouchAction:function(){return[jb]},attrTest:function(a){return this._super.attrTest.call(this,a)&&(Math.abs(a.rotation)>this.options.threshold||this.state&ob)}}),i(fa,aa,{defaults:{event:"swipe",threshold:10,velocity:.3,direction:Na|Oa,pointers:1},getTouchAction:function(){return ba.prototype.getTouchAction.call(this)},attrTest:function(a){var b,c=this.options.direction;return c&(Na|Oa)?b=a.overallVelocity:c&Na?b=a.overallVelocityX:c&Oa&&(b=a.overallVelocityY),this._super.attrTest.call(this,a)&&c&a.offsetDirection&&a.distance>this.options.threshold&&a.maxPointers==this.options.pointers&&qa(b)>this.options.velocity&&a.eventType&Ga},emit:function(a){var b=$(a.offsetDirection);b&&this.manager.emit(this.options.event+b,a),this.manager.emit(this.options.event,a)}}),i(ga,Y,{defaults:{event:"tap",pointers:1,taps:1,interval:300,time:250,threshold:9,posThreshold:10},getTouchAction:function(){return[ib]},process:function(a){var b=this.options,c=a.pointers.length===b.pointers,d=a.distance<b.threshold,f=a.deltaTime<b.time;if(this.reset(),a.eventType&Ea&&0===this.count)return this.failTimeout();if(d&&f&&c){if(a.eventType!=Ga)return this.failTimeout();var g=this.pTime?a.timeStamp-this.pTime<b.interval:!0,h=!this.pCenter||H(this.pCenter,a.center)<b.posThreshold;this.pTime=a.timeStamp,this.pCenter=a.center,h&&g?this.count+=1:this.count=1,this._input=a;var i=this.count%b.taps;if(0===i)return this.hasRequireFailures()?(this._timer=e(function(){this.state=rb,this.tryEmit()},b.interval,this),ob):rb}return tb},failTimeout:function(){return this._timer=e(function(){this.state=tb},this.options.interval,this),tb},reset:function(){clearTimeout(this._timer)},emit:function(){this.state==rb&&(this._input.tapCount=this.count,this.manager.emit(this.options.event,this._input))}}),ha.VERSION="2.0.8",ha.defaults={domEvents:!1,touchAction:gb,enable:!0,inputTarget:null,inputClass:null,preset:[[ea,{enable:!1}],[ca,{enable:!1},["rotate"]],[fa,{direction:Na}],[ba,{direction:Na},["swipe"]],[ga],[ga,{event:"doubletap",taps:2},["tap"]],[da]],cssProps:{userSelect:"none",touchSelect:"none",touchCallout:"none",contentZooming:"none",userDrag:"none",tapHighlightColor:"rgba(0,0,0,0)"}};var ub=1,vb=2;ia.prototype={set:function(a){return la(this.options,a),a.touchAction&&this.touchAction.update(),a.inputTarget&&(this.input.destroy(),this.input.target=a.inputTarget,this.input.init()),this},stop:function(a){this.session.stopped=a?vb:ub},recognize:function(a){var b=this.session;if(!b.stopped){this.touchAction.preventDefaults(a);var c,d=this.recognizers,e=b.curRecognizer;(!e||e&&e.state&rb)&&(e=b.curRecognizer=null);for(var f=0;f<d.length;)c=d[f],b.stopped===vb||e&&c!=e&&!c.canRecognizeWith(e)?c.reset():c.recognize(a),!e&&c.state&(ob|pb|qb)&&(e=b.curRecognizer=c),f++}},get:function(a){if(a instanceof Y)return a;for(var b=this.recognizers,c=0;c<b.length;c++)if(b[c].options.event==a)return b[c];return null},add:function(a){if(f(a,"add",this))return this;var b=this.get(a.options.event);return b&&this.remove(b),this.recognizers.push(a),a.manager=this,this.touchAction.update(),a},remove:function(a){if(f(a,"remove",this))return this;if(a=this.get(a)){var b=this.recognizers,c=r(b,a);-1!==c&&(b.splice(c,1),this.touchAction.update())}return this},on:function(a,b){if(a!==d&&b!==d){var c=this.handlers;return g(q(a),function(a){c[a]=c[a]||[],c[a].push(b)}),this}},off:function(a,b){if(a!==d){var c=this.handlers;return g(q(a),function(a){b?c[a]&&c[a].splice(r(c[a],b),1):delete c[a]}),this}},emit:function(a,b){this.options.domEvents&&ka(a,b);var c=this.handlers[a]&&this.handlers[a].slice();if(c&&c.length){b.type=a,b.preventDefault=function(){b.srcEvent.preventDefault()};for(var d=0;d<c.length;)c[d](b),d++}},destroy:function(){this.element&&ja(this,!1),this.handlers={},this.session={},this.input.destroy(),this.element=null}},la(ha,{INPUT_START:Ea,INPUT_MOVE:Fa,INPUT_END:Ga,INPUT_CANCEL:Ha,STATE_POSSIBLE:nb,STATE_BEGAN:ob,STATE_CHANGED:pb,STATE_ENDED:qb,STATE_RECOGNIZED:rb,STATE_CANCELLED:sb,STATE_FAILED:tb,DIRECTION_NONE:Ia,DIRECTION_LEFT:Ja,DIRECTION_RIGHT:Ka,DIRECTION_UP:La,DIRECTION_DOWN:Ma,DIRECTION_HORIZONTAL:Na,DIRECTION_VERTICAL:Oa,DIRECTION_ALL:Pa,Manager:ia,Input:x,TouchAction:V,TouchInput:P,MouseInput:L,PointerEventInput:M,TouchMouseInput:R,SingleTouchInput:N,Recognizer:Y,AttrRecognizer:aa,Tap:ga,Pan:ba,Swipe:fa,Pinch:ca,Rotate:ea,Press:da,on:m,off:n,each:g,merge:ta,extend:sa,assign:la,inherit:i,bindFn:j,prefixed:u});var wb="undefined"!=typeof a?a:"undefined"!=typeof self?self:{};wb.Hammer=ha,"function"==typeof define&&define.amd?define(function(){return ha}):"undefined"!=typeof module&&module.exports?module.exports=ha:a[c]=ha}(window,document,"Hammer");

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function () {
    var ajaxSetup = {
        url: PF.obj.config.json_api,
        cache: false,
        dataType: "json",
        data: { auth_token: PF.obj.config.auth_token }
    };
    if (typeof PF.obj.config.session_id !== typeof undefined) {
        ajaxSetup.data.session_id = PF.obj.config.session_id;
    }
    $.ajaxSetup(ajaxSetup);

    /**
     * WINDOW LISTENERS
     * -------------------------------------------------------------------------------------------------
     */
    function beforeUnloadListener(event) {
        if (
            $("form", PF.obj.modal.selectors.root).data("beforeunload") == "continue"
        )
        return;
        if (
            $(PF.obj.modal.selectors.root).is(":visible") &&
            PF.fn.form_modal_has_changed()
        ) {
            event.preventDefault();
            return event.returnValue = '<i class="fas fa-exclamation-triangle"></i> ' + PF.fn._s(
                "All the changes that you have made will be lost if you continue."
            );
        }
    };
    window.addEventListener('beforeunload', beforeUnloadListener);

    if(("standalone" in window.navigator) && window.navigator.standalone) {
        $(document).on("click", "a", function(e) {
            var new_location = $(this).attr('href');
            if (new_location != undefined && new_location.substr(0, 1) != '#' && $(this).attr('data-method') == undefined) {
                e.preventDefault();
                new_location = new_location.replace(PF.obj.config.public_url, PF.obj.config.base_url);
                window.location = new_location;
                return false;
            }
        });
    }

    var previousScrollPosition = 0;
    const supportPageOffset = window.pageXOffset !== undefined;
    const isCSS1Compat = (document.compatMode || "") === "CSS1Compat";
    const isScrollingDown = function () {
        let scrolledPosition = supportPageOffset
            ? window.pageYOffset
            : isCSS1Compat
                ? document.documentElement.scrollTop
                : document.body.scrollTop;
        let isScrollDown;
        if (scrolledPosition > previousScrollPosition) {
            isScrollDown = true;
        } else {
            isScrollDown = false;
        }
        previousScrollPosition = scrolledPosition;
        return isScrollDown;
    };
    var scrollTimer;
    var ninjaScroll = function () {
        var down = isScrollingDown();
        var scrollUpClass = "scroll-up";
        var scrollDownClass = "scroll-down";
        var noStickyMediaClass = "no-sticky-media";
        var isAnimated = $(".top-bar").is(":animated");
        if(isAnimated
            || $("html").attr("data-scroll-lock") === "1"
        ) {
            scrollTimer = false;
            return;
        }
        if($(window).scrollTop() <= 0) {
            $("html").removeClass(scrollUpClass + " " + scrollDownClass);
            scrollTimer = false;
            return;
        }
        var addClass = scrollUpClass;
        var removeClass = scrollDownClass;
        // fix force down for Safari scroll bottom bounce
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight) {
            down = true;
          }
        if(down) {
            addClass = scrollDownClass;
            removeClass = scrollUpClass;
        }
        var mediaHeight = $('#image-viewer').outerHeight();
        var viewportHeight = $(window).height();
        if(mediaHeight/viewportHeight > 0.6) {
            addClass += " " + noStickyMediaClass;
        } else {
            removeClass += " " + noStickyMediaClass;
        }
        $("html")
            .addClass(addClass)
            .removeClass(removeClass);
        scrollTimer = false;
    };
    window.addEventListener("load", ninjaScroll());
    window.addEventListener("scroll", function () {
        if(!$("html").hasScrollbar().vertical) return;
        if(scrollTimer) return;
        scrollTimer = true;
        setTimeout(ninjaScroll(), 400);
    });

    // Blind the tipTips on load
    PF.fn.bindtipTip();

    var resizeTimeout = 0,
        resizeTimer,
        width = $(window).width();
    $(window).on("resize", function () {
        PF.fn.modal.styleAware();
        PF.fn.close_pops();
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            PF.fn.modal.fixScrollbars();
            var device = PF.fn.getDeviceName(),
                handled = ["phone", "phablet"],
                desktop = ["tablet", "laptop", "desktop"];
            var new_device = PF.fn.getDeviceName();
            if (
                (new_device !== device &&
                    ($.inArray(device, handled) >= 0 &&
                        $.inArray(new_device, handled) == -1)) ||
                ($.inArray(device, desktop) >= 0 && $.inArray(new_device, desktop) == -1)
            ) {
                PF.fn.close_pops();
            }

            $(".top-bar").css("top", "");
            $("body").css({ position: "", height: "" });

            $(".antiscroll")
                .removeClass("jsly")
                .data("antiscroll", ""); // Destroy for this?
            $(".antiscroll-inner").css({ height: "", width: "", maxheight: "" }); // .pop-box, .pop-box-inner ?

            PF.fn.list_fluid_width();

            if (width !== $(window).width()) {
                $(PF.obj.listing.selectors.list_item, PF.obj.listing.selectors.content_listing_visible).css("opacity", 0);
                if (
                    $("[data-action=top-bar-menu-full]", "#top-bar").hasClass("current")
                ) {
                    PF.fn.topMenu.hide(0);
                }
                PF.fn.listing.columnizer(true, 0, true);
                $(PF.obj.listing.selectors.list_item, PF.obj.listing.selectors.content_listing_visible).css("opacity", 1);
            }
            width = $(window).width();
        }, resizeTimeout);
    });

    // Close the opened pop-boxes on HTML click
    $(document).on("click", "html", function () {
        PF.fn.close_pops();
    });

    // Keydown numeric input (prevents non numeric keys)
    $(document).on("keydown", ".numeric-input", function (e) {
        e.keydown_numeric();
    });

    // The handly data-scrollto. IT will scroll the elements to the target
    $(document).on("click", "[data-scrollto]", function (e) {
        var target = $(this).data("scrollto"),
            $target = $(!target.match(/^\#|\./) ? "#" + target : target);

        if ($target.exists()) {
            PF.fn.scroll($target);
        } else {
            console.log("PF scrollto error: target doesn't exists", $target);
        }
    });

    $(document).on(
        "click focus",
        "[data-login-needed], [data-user-logged=must]",
        function (e) {
            if (!PF.fn.is_user_logged()) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = PF.obj.vars.urls.login;
                return false;
            }
        }
    );

    // The handly data-trigger. It will trigger click for elements with data-trigger
    $(document).on("click", "[data-trigger]", function (e) {
        if (e.isPropagationStopped()) {
            return false;
        }

        var trigger = $(this).data("trigger"),
            $target = $(!trigger.match(/^\#|\./) ? "#" + trigger : trigger);

        if ($target.exists()) {
            e.stopPropagation();
            e.preventDefault();
            if (!$target.closest(PF.obj.modal.selectors.root).length) {
                PF.fn.modal.close();
            }
            $target.trigger("click");
        } else {
            console.log("PF trigger error: target doesn't exists", $target);
        }
    });

    // Fix the auth_token inputs
    $("form[method=post]").each(function () {
        if (!$("input[name=auth_token]", this).exists()) {
            $(this).append(
                $("<input>", {
                    type: "hidden",
                    name: "auth_token",
                    value: PF.obj.config.auth_token
                })
            );
        }
    });

    // Clear form like magic
    $(document).on("click", ".clear-form", function () {
        $(this)
            .closest("form")[0]
            .reset();
    });

    $(document).on("submit", "form", function (e) {
        if(e.isPropagationStopped()) {
            return;
        }
        var type = $(this).data("type");
        var hasErrors = false;
        var $validate = $(this).find("[required], [data-validate]");
        var errorFn = function ($el) {
            if($el.is(":hidden")) {
                return;
            }
            $el.highlight();
            $el.closest(".input-label").find("label").shake();
            hasErrors = true;
        };
        $validate.each(function () {
            if(!$(this)[0].checkValidity()) {
                errorFn($(this));
            }
        });
        if (!hasErrors) {
            hasErrors = !$(this).get(0).checkValidity();
        }
        if(hasErrors) {
            $(this).get(0).reportValidity();
            return false;
        }
    });

    // Co-combo breaker
    $(document).on("change", "select[data-combo]", function () {
        var $combo = $("#" + $(this).data("combo"));

        if ($combo.exists()) {
            $combo.children(".switch-combo").hide();
        }

        var $combo_container = $(
            "#" +
            $(this)
                .closest("select")
                .data("combo")
        ),
            $combo_target = $(
                "[data-combo-value~=" + $("option:selected", this).attr("value") + "]",
                $combo_container
            );

        if ($combo_target.exists()) {
            $combo_target
                .show()
                .find("[data-required]")
                .each(function () {
                    $(this).attr("required", "required"); // re-enable any disabled required
                });
        }

        // Disable [required] in hidden combos
        $(".switch-combo", $combo_container).each(function () {
            if ($(this).is(":visible")) return;
            $("[required]", this)
                .attr("data-required", true)
                .removeAttr("required");
        });
    });

    $(document).on("keyup", function (e) {
        var $this = $(e.target);
        var event = e.originalEvent;
        if (event.key == "Escape") {
            if ($(PF.obj.modal.selectors.root).is(":visible")) {
                if(!$this.is(":input")) {
                    $(
                        "[data-action=cancel],[data-action=close-modal]",
                        PF.obj.modal.selectors.root
                    )
                        .first()
                        .trigger("click");
                } else {
                    $this.trigger("blur");
                }
                PF.fn.keyFeedback.spawn(e);
            }
        }
    });

    // Input events
    $(document).on("keyup", ":input", function (e) {
        $(".input-warning", $(this).closest(".input-label")).html("");
    });
    $(document).on("blur", ":input", function () {
        var this_val = $.trim($(this).prop("value"));
        $(this).prop("value", this_val);
    });

    $(document).on("click", "[data-focus=select-all],[data-click=select-all]", function () {
        if ($(this).is(":input")) {
            this.select();
        } else {
            var range = document.createRange();
            range.selectNodeContents(this);
            var sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        }
    });

    // Input password strength
    $(document).on("keyup change blur", ":input[type=password]", function () {
        var password = testPassword($(this).val()),
            $parent = $(this).closest("div");

        if ($(this).val() == "") {
            password.percent = 0;
            password.verdict = "";
        }

        $("[data-content=password-meter-bar]", $parent)
            .attr("data-veredict", password.verdict.replace(/ /g, "-"))
            .width(password.percent);
        $("[data-text=password-meter-message]", $parent)
            .removeClass("red-warning")
            .text(password.verdict !== "" ? PF.fn._s(password.verdict) : "");
    });

    // Popup links
    $(document).on("click", "[rel=popup-link], .popup-link", function (e) {
        e.preventDefault();
        var href = $(this)[
            typeof $(this).attr("href") !== "undefined" ? "attr" : "data"
        ]("href");
        if (typeof href == "undefined") {
            return;
        }
        if(PF.fn.isDevice(["phone", "phablet"])) {
            if (href.substring(0, 6) == "mailto") {
                window.location = href;
                return;
            }
            if (href.substring(0, 5) == "share") {
                if(navigator.canShare) {
                    navigator.share(
                        PF.fn.deparam(href.substring(6))
                    );
                }
                return;
            }

        }
        PF.fn.popup({ href: href });
    });

    /**
     * MODAL
     * -------------------------------------------------------------------------------------------------
     */

    // Call plain simple HTML modal
    $(document).on("click", "[data-modal=simple],[data-modal=html]", function () {
        var $target = $(
            "[data-modal=" + $(this).data("target") + "], #" + $(this).data("target")
        ).first();
        PF.fn.modal.call({ template: $target.html(), buttons: false });
    });

    // Prevent modal submit form since we only use the form in the modal to trigger HTML5 validation
    $(document).on("submit", PF.obj.modal.selectors.root + " form", function (e) {
        if ($(this).data("prevented")) return false; // Don't send the form if is prevented
        if (typeof $(this).attr("method") !== "undefined") return; // Don't bind anything extra if is normal form
        return false; // Prevent default form handling
    });

    // Form/editable/confirm modal
    $(document).on(
        "click",
        "[data-modal=edit],[data-modal=form],[data-confirm]",
        function (e) {
            e.preventDefault();

            var $this = $(this);
            var $target;

            if ($this.is("[data-confirm]")) {
                $target = $this;
                PF.obj.modal.type = "confirm";
            } else {
                $target = $(
                    "[data-modal=" + $this.data("target") + "], #" + $this.data("target")
                ).first();

                if ($target.length == 0) {
                    $target = $("[data-modal=form-modal], #form-modal").first();
                }

                if ($target.length == 0) {
                    console.log("PF Error: Modal target doesn't exists.");
                }

                PF.obj.modal.type = $this.data("modal");
            }

            var args = $this.data("args"),
                submit_function = window[$target.data("submit-fn")],
                cancel_function = window[$target.data("cancel-fn")],
                onload_function = window[$target.data("load-fn")],
                submit_done_msg = $target.data("submit-done"),
                ajax = {
                    url:
                        $target.data("ajax-url") ||
                        (typeof $target.data("is-xhr") !== typeof undefined
                            ? PF.obj.config.json_api
                            : null),
                    deferred: window[$target.data("ajax-deferred")]
                };

            if (typeof submit_function !== "function" && $target.data("submit-fn")) {
                var submit_fn_split = $target.data("submit-fn").split(".");
                submit_function = window;
                for (var i = 0; i < submit_fn_split.length; i++) {
                    submit_function = submit_function[submit_fn_split[i]];
                }
            }
            if (typeof cancel_function !== "function" && $target.data("cancel-fn")) {
                var cancel_fn_split = $target.data("cancel-fn").split(".");
                cancel_function = window;
                for (var i = 0; i < cancel_fn_split.length; i++) {
                    cancel_function = cancel_function[cancel_fn_split[i]];
                }
            }
            if (typeof load_function !== "function" && $target.data("load-fn")) {
                var load_fn_split = $target.data("load-fn").split(".");
                load_function = window;
                for (var i = 0; i < load_fn_split.length; i++) {
                    load_function = load_function[load_fn_split[i]];
                }
            }

            if (typeof ajax.deferred !== "object" && $target.data("ajax-deferred")) {
                var deferred_obj_split = $target.data("ajax-deferred").split(".");
                ajax.deferred = window;
                for (var i = 0; i < deferred_obj_split.length; i++) {
                    ajax.deferred = ajax.deferred[deferred_obj_split[i]];
                }
            }

            // Before fn
            var fn_before = window[$target.data("before-fn")];
            if (typeof fn_before !== "function" && $target.data("before-fn")) {
                var before_obj_split = $target.data("before-fn").split(".");
                fn_before = window;
                for (var i = 0; i < before_obj_split.length; i++) {
                    fn_before = fn_before[before_obj_split[i]];
                }
            }
            if (typeof fn_before == "function") {
                var before_result = fn_before(e);
                if(before_result === false) {
                    return false;
                }
            }

            var inline_options = $(this).data("options") || {};

            // Confirm modal
            if ($this.is("[data-confirm]")) {
                var default_options = {
                    message: $this.data("confirm"),
                    confirm:
                        typeof submit_function == "function" ? submit_function(args) : "",
                    cancel:
                        typeof cancel_function == "function" ? cancel_function(args) : "",
                    ajax: ajax
                };

                if ($this.attr("href") && default_options.confirm == "") {
                    default_options.confirm = function () {
                        return window.location.replace($this.attr("href"));
                    };
                }

                PF.fn.modal.confirm($.extend(default_options, inline_options));
            } else {
                // Form/editable
                var default_options = {
                    template: $target.html(),
                    button_submit: $(this).is("[data-modal=edit]")
                        ? PF.fn._s("Save changes")
                        : PF.fn._s("Submit"),
                    confirm: function () {
                        var form_modal_has_changed = PF.fn.form_modal_has_changed();

                        // Conventional form handling
                        var $form = $("form", PF.obj.modal.selectors.root);
                        if (typeof $form.attr("action") !== "undefined") {
                            $form.data("prevented", !form_modal_has_changed);
                            PF.fn.modal.close();
                            return;
                        }
                        $(":input[name]", $form).each(function () {
                            if (!$(this).is(":visible")) {
                                var input_attr = $(this).attr("required");
                                if (
                                    typeof input_attr !== typeof undefined &&
                                    input_attr !== false
                                ) {
                                    $(this)
                                        .prop("required", false)
                                        .attr("data-required", "required");
                                }
                            } else {
                                if ($(this).attr("data-required") == "required") {
                                    $(this).prop("required", true);
                                }
                            }
                        });
                        if (!PF.fn.form.validateForm($form)) {
                            return false;
                        }

                        // Run the full function only when the form changes
                        if (!form_modal_has_changed && !inline_options.forced) {
                            PF.fn.modal.close();
                            return;
                        }

                        if (typeof submit_function == "function")
                            submit_fn = submit_function(args);
                        if (typeof submit_fn !== "undefined" && submit_fn == false) {
                            return false;
                        }

                        $(":input", PF.obj.modal.selectors.root).each(function () {
                            $(this).val($.trim($(this).val()));
                        });

                        if ($this.is("[data-modal=edit]")) {
                            // Set the input values before cloning the html
                            $target.html(
                                $(
                                    PF.obj.modal.selectors.body,
                                    $(PF.obj.modal.selectors.root).bindFormData()
                                )
                                    .html()
                                    .replace(/rel=[\'"]tooltip[\'"]/g, 'rel="template-tooltip"')
                            );
                        }

                        if (typeof ajax.url !== "undefined") {
                            return true;
                        } else {
                            PF.fn.modal.close(function () {
                                if (typeof submit_done_msg !== "undefined" && submit_done_msg !== "") {
                                    PF.fn.growl.expirable(submit_done_msg);
                                }
                            });
                        }
                    },
                    cancel: function () {
                        if (typeof cancel_fn == "function") cancel_fn = cancel_fn();
                        if (typeof cancel_fn !== "undefined" && cancel_fn == false) {
                            return false;
                        }
                        // nota: falta template aca
                        if (
                            $target.data("prompt") != "skip" &&
                            PF.fn.form_modal_has_changed()
                        ) {
                            if ($(PF.obj.modal.selectors.changes_confirm).exists()) return;
                            $(PF.obj.modal.selectors.box, PF.obj.modal.selectors.root)
                                .css({ transition: "none" })
                                .hide();
                            $(PF.obj.modal.selectors.root).append(
                                '<div id="' +
                                PF.obj.modal.selectors.changes_confirm.replace("#", "") +
                                '"><div class="content-width"><h2>' +
                                '<i class="fas fa-exclamation-triangle"></i> ' +
                                PF.fn._s(
                                    "All the changes that you have made will be lost if you continue."
                                ) +
                                '</h2><div class="' +
                                PF.obj.modal.selectors.btn_container.replace(".", "") +
                                ' margin-bottom-0"><button class="btn btn-input default" data-action="cancel">' +
                                '<i class="fas fa-chevron-circle-left btn-icon"></i>'+
                                '<span class="btn-text">' +
                                PF.fn._s("Go back to form") +
                                '</span>' +
                                '</button> <span class="btn-alt">' +
                                PF.fn._s("or") +
                                ' <a data-action="submit"><i class="fas fa-check margin-right-5"></i>' +
                                PF.fn._s("continue anyway") +
                                "</a></span></div></div>"
                            );
                            $(PF.obj.modal.selectors.changes_confirm)
                                .css(
                                    "margin-top",
                                    -$(PF.obj.modal.selectors.changes_confirm).outerHeight(true) /
                                    2
                                )
                                .hide()
                                .fadeIn("fast");
                        } else {
                            PF.fn.modal.close();
                        }
                    },
                    load: function () {
                        if (typeof load_function == "function") load_function();
                    },
                    callback: function () { },
                    ajax: ajax
                };
                PF.fn.modal.call($.extend(default_options, inline_options));
            }
        }
    );

    if (!PF.fn.is_user_logged()) {
        $("[data-login-needed]:input, [data-user-logged=must]:input").each(
            function () {
                $(this).attr("readonly", true);
            }
        );
    }

    $(document).on("keydown", "html", function (e) {
        var $this = $(e.target),
            event = e.originalEvent;
        if (event.key === "Escape") {
            PF.fn.growl.close();
        }
        var submit = event.key === "Enter" && (event.ctrlKey || event.metaKey);
        if($this.is("textarea") && !submit) {
            e.stopPropagation();
            return;
        }
        if($this.is(":input.search") && event.key === "Escape") {
            if($this.val() == "") {
                $this.trigger("blur");
            }
            $this
                .closest(".input-search")
                .find("[data-action=clear-search]")
                .trigger("click");
            return;
        }

        var $inputEnabledEnter = $this.is(":input.search") || $this.closest(".input-with-button").exists();
        if(!$inputEnabledEnter && $this.is(":input, textarea") && event.key === 'Enter' && !submit) {
            e.stopPropagation();
            e.preventDefault();
            return;
        }
        var $form = $this.is(":input")
            ? $this.closest("form:not([data-js])")
            : $("form:not([data-js])", ".form-content:visible").first();
        if($(PF.obj.modal.selectors.root).exists()) {
            if(!submit
                && event.key === 'Enter'
                && $("[data-action=submit]", PF.obj.modal.selectors.root).exists()
                && !$this.is(".prevent-submit")
            ) {
                submit = true;
            }
            if(!submit) {
                return;
            }
            if(!$form.exists()) {
                e.stopPropagation();
                e.preventDefault();
                $("[data-action=submit]", PF.obj.modal.selectors.root).trigger("click");
            }
        }
        if(submit) {
            if($form.exists()) {
                e.stopPropagation();
                e.preventDefault();
                $form.trigger("submit");
            }
            PF.fn.keyFeedback.spawn(e);
        }
    });

    // function hashToAction() {
    //     $('[data-action="'+ window.location.hash.slice(1) +'"]')
    //         .first()
    //         .trigger("click");
    // }
    // if(window.location.hash) {
    //     hashToAction();
    // }

    // $(window).on("hashchange", function () {
    //     hashToAction();
    // });

    /**
     * MOBILE TOP BAR MENU
     * -------------------------------------------------------------------------------------------------
     */
    $(document).on("click", "#menu-fullscreen .fullscreen, [data-action=top-bar-menu-full]", function (e) {
        if($(e.target).is("#pop-box-mask")) {
            return;
        }
        var hasClass = $("[data-action=top-bar-menu-full]", "#top-bar").hasClass(
            "current"
        );
        PF.fn.topMenu[hasClass ? "hide" : "show"]();
        if(Boolean(window.navigator.vibrate)) {
            var pattern = !hasClass ? [15, 200, 25, 125, 15] : [15, 200, 15];
            window.navigator.vibrate(0);
            window.navigator.vibrate(pattern);
        }
    });

    /**
     * SEARCH INPUT
     * -------------------------------------------------------------------------------------------------
     */

    // Top-search feature
    $(document).on("click", "[data-action=top-bar-search]", function () {
        $("[data-action=top-bar-search-input]", ".top-bar")
            .removeClass("hidden");
        $("[data-action=top-bar-search-input]:visible input")
            .first()
            .focus();
        if (
            is_ios() &&
            !$(this)
                .closest(PF.fn.topMenu.vars.menu)
                .exists()
        ) {
            $(".top-bar").css("position", "absolute");
        }
        $("[data-action=top-bar-search]", ".top-bar").addClass("hidden");
    });

    $(document).on("click", ".input-search .icon--search", function (e) {
        $("input", e.currentTarget.offsetParent).focus();
    });

    $(document).on(
        "click",
        ".input-search .icon--close, .input-search [data-action=clear-search]",
        function (e) {
            var $input = $("input", e.currentTarget.offsetParent);
            if ($input.val() == "") {
                if (
                    $(this)
                        .closest("[data-action=top-bar-search-input]")
                        .exists()
                ) {
                    $("[data-action=top-bar-search-input]", ".top-bar").addClass("hidden");
                    $("[data-action=top-bar-search]", ".top-bar")
                        .removeClass("opened")
                        .removeClass("hidden");
                }
            } else {
                if (
                    !$(this)
                        .closest("[data-action=top-bar-search-input]")
                        .exists()
                ) {
                    $(this).addClass("hidden");
                }
                $input.val("").trigger("change");
            }
        }
    );

    // Input search clear search toggle
    $(document).on("keyup change", "input.search", function (e) {
        var $input = $(this),
            $div = $(this).closest(".input-search");
        if (
            !$(this)
                .closest("[data-action=top-bar-search-input]")
                .exists()
        ) {
            $(".icon--close, [data-action=clear-search]", $div)
                .toggleClass("hidden", $input.val() == "");
        }
    });

    /**
     * POP BOXES (MENUS)
     * -------------------------------------------------------------------------------------------------
     */
    $(document)
        .on("click mouseenter", ".pop-btn", function (e) {
            if (
                PF.fn.isDevice(["phone", "phablet"]) &&
                (e.type == "mouseenter" || $(this).hasClass("pop-btn-desktop"))
            ) {
                return;
            }

            var $this_click = $(e.target);
            var $pop_btn;
            var $pop_box;
            var devices = $.makeArray(["phone", "phablet"]);
            var $this = $(this);

            if (e.type == "mouseenter" && !$(this).hasClass("pop-btn-auto")) return;
            if (
                $(this).hasClass("disabled") ||
                ($this_click.closest(".current").exists() &&
                    !PF.fn.isDevice("phone") &&
                    !$this_click.closest(".pop-btn-show").exists())
            ) {
                return;
            }

            PF.fn.growl.close();

            e.stopPropagation();

            $pop_btn = $(this);
            $pop_box = $(".pop-box", $pop_btn);
            $pop_btn.addClass("opened");
            var marginBox = parseInt($pop_box.css("margin-right"));

            $(".pop-box-inner", $pop_box).css("max-height", "");

            if (PF.fn.isDevice(devices)) {
                var textButton = $(".pop-btn-text,.btn-text,.text", $pop_btn)
                    .first().text();
                var iconButton = $(".pop-btn-icon,.btn-icon,.icon", $pop_btn)[0].outerHTML;
                if (!$(".pop-box-header", $pop_box).exists()) {
                    $pop_box.prepend(
                        $("<div/>", {
                            class: "pop-box-header",
                            html: iconButton + ' ' + textButton + '<span class="btn-icon icon--close fas fa-times"></span></span>'
                        })
                    );
                }
            } else {
                $(".pop-box-header", $pop_box).remove();
                $pop_box.css({ bottom: "" });
            }
            if ($pop_box.hasClass("anchor-center")) {
                if (!PF.fn.isDevice(devices)) {
                    $pop_box.css("marginInlineStart", -($pop_box.outerWidth() / 2));
                } else {
                    $pop_box.css("marginInlineStart", "");
                }
            }

            // Pop button changer
            if ($this_click.is("[data-change]")) {
                $("li", $pop_box).removeClass("current");
                $this_click.closest("li").addClass("current");
                $("[data-text-change]", $pop_btn).text(
                    $("li.current a", $pop_box).text()
                );
                e.preventDefault();
            }

            if (!$pop_box.exists()) return;

            var $this = e.istriggered ? $(e.target) : $(this);
            if (
                $pop_box.is(":visible") &&
                $(e.target)
                    .closest(".pop-box-inner")
                    .exists() &&
                ($this.hasClass("pop-keep-click"))
            ) {
                return;
            }

            $(".pop-box:visible")
                .not($pop_box)
                .hide()
                .closest(".pop-btn")
                .removeClass("opened");

            var callback = function ($pop_box) {
                if (!$pop_box.is(":visible")) {
                    $pop_box
                        .css("marginInlineStart", "")
                        .removeAttr("data-guidstr")
                        .closest(".pop-btn")
                        .removeClass("opened");
                } else {
                    if (!PF.fn.isDevice(devices)) {
                        if($pop_box.is(".--auto-cols")) {
                            const max_cols = 5;
                            $pop_box.removeClass(function (i, c) {
                                return (c.match (/(^|\s)pbcols\S+/g) || []).join(' ');
                            });
                            for(let i = 1; i <= max_cols; i++) {
                                $pop_box.addClass("pbcols" + i);
                                $(".pop-box-inner", $pop_box)
                                    .toggleClass("pop-box-menucols", i > 1);
                                fixMargin();
                                if($pop_box.is_in_viewport() && $pop_box.height() < $(window).height()*.8) {
                                    break;
                                }
                                if(i !== max_cols) {
                                    $pop_box
                                        .css("marginInlineStart", "")
                                        .removeClass("pbcols" + i);
                                }
                            }
                        }
                        function fixMargin() {
                            var posMargin = $pop_box.css("marginInlineStart");
                            if (typeof posMargin !== typeof undefined) {
                                posMargin = parseFloat(posMargin);
                                $pop_box.css("marginInlineStart", "");
                            }
                            var cutoff = $pop_box.getWindowCutoff();
                            if (cutoff && cutoff.right && cutoff.right < posMargin) {
                                $pop_box
                                    .css("marginInlineStart", cutoff.right + "px");
                            } else {
                                $pop_box.css("marginInlineStart", posMargin + "px");
                                cutoff = $pop_box.getWindowCutoff();
                                if(cutoff && cutoff.left) {
                                    let marginFix = -(Math.abs(posMargin) + Math.abs(cutoff.left) + marginBox/2);
                                    $pop_box.css(
                                        "marginInlineStart",
                                        marginFix + "px"
                                    );
                                }
                            }
                        }
                        $(".antiscroll-wrap:not(.jsly):visible", $pop_box)
                            .addClass("jsly")
                            .antiscroll();
                    } else {
                        $(".antiscroll-inner", $pop_box).height("100%");
                    }
                }
            };

            if (PF.fn.isDevice(devices)) {
                if ($(this).is("[data-action=top-bar-notifications]")) {
                    $pop_box.css({ height: $(window).height() });
                }
                var pop_box_h = $pop_box.height() + "px";
                var menu_top =
                    parseInt($(".top-bar").outerHeight()) +
                    parseInt($(".top-bar").css("top")) +
                    parseInt($(".top-bar").css("margin-top")) +
                    parseInt($(".top-bar").css("margin-bottom")) +
                    "px";
                if ($pop_box.is(":visible")) {
                    $("#pop-box-mask").css({ opacity: 0 });
                    $pop_box.css({ transform: "none" });
                    if ($this.closest(PF.fn.topMenu.vars.menu).exists()) {
                        $(".top-bar").css({ transform: "none" });
                    }
                    setTimeout(function () {
                        $pop_box.hide().attr("style", "");
                        $("#pop-box-mask").remove();
                        callback($pop_box);
                        if ($this.closest(PF.fn.topMenu.vars.menu).exists()) {
                            $(PF.fn.topMenu.vars.menu).css({
                                height: ""
                            });
                            $(PF.fn.topMenu.vars.menu).animate(
                                { scrollTop: PF.fn.topMenu.vars.scrollTop },
                                PF.obj.config.animation.normal / 2
                            );
                        }
                        if (!$("body").data("hasOverflowHidden")) {
                            var removeClasses = "pop-box-show pop-box-show--top";
                            if(!$(PF.obj.modal.selectors.root).exists()) {
                                removeClasses += " overflow-hidden";
                            }
                            $("body,html").removeClass(removeClasses);
                        }
                        $pop_box.find(".pop-box-inner").css("height", "");
                    }, PF.obj.config.animation.normal);
                } else {
                    $("#pop-box-mask").remove();
                    $pop_box.parent().prepend(
                        $("<div/>", {
                            id: "pop-box-mask",
                            class: "fullscreen black"
                        }).css({
                            zIndex: 400,
                            display: "block"
                        })
                    );
                    PF.fn.topMenu.vars.scrollTop = $(PF.fn.topMenu.vars.menu).scrollTop();
                    setTimeout(function () {
                        $("#pop-box-mask").css({ opacity: 1 });
                        setTimeout(function () {
                            $pop_box.show().css({
                                bottom: "-" + pop_box_h,
                                maxHeight: "100%",
                                zIndex: 1000,
                                transform: "translate(0,0)"
                            });
                            setTimeout(function() {
                                $pop_box.find(".pop-box-inner").scrollTop(0)
                            }, 1)

                            setTimeout(function () {
                                $pop_box.css({ transform: "translate(0,-" + pop_box_h + ")" });
                            }, 1);

                            setTimeout(function () {
                                callback($pop_box);
                            }, PF.obj.config.animation.normal);

                            if ($("html").hasClass("overflow-hidden")) {
                                $("html").data("hasOverflowHidden", 1);
                            } else {
                                $("html").addClass("overflow-hidden");
                                $("body").addClass(
                                    ($this.closest('.top-bar').exists()
                                        ? 'pop-box-show--top'
                                        : 'pop-box-show')
                                );
                            }

                            $(".pop-box-inner", $pop_box).css(
                                "height",
                                $pop_box.height() -
                                $(".pop-box-header", $pop_box).outerHeight(true)
                            );
                        }, 1);
                    }, 1);
                }
            } else {
                $pop_box[$pop_box.is(":visible") ? "hide" : "show"](0, function () {
                    callback($pop_box);
                });
            }
        })
        .on("mouseleave", ".pop-btn", function () {
            if (!PF.fn.isDevice(["laptop", "desktop"])) {
                return;
            }
            var $pop_btn = $(this),
                $pop_box = $(".pop-box", $pop_btn);

            if (
                !$pop_btn.hasClass("pop-btn-auto") ||
                (PF.fn.isDevice(["phone", "phablet"]) &&
                    $pop_btn.hasClass("pop-btn-auto"))
            ) {
                return;
            }

            $pop_box
                .hide()
                .closest(".pop-btn")
                .removeClass("opened");
        });

    /**
     * TABS
     * -------------------------------------------------------------------------------------------------
     */

    var loadTabHash = function () {
        var hash = window.location.hash;
        var $hash_node = $('[href="' + hash + '"]');
        if($hash_node.length > 0) {
            $.each($hash_node[0].attributes, function() {
                PF.obj.tabs.hashdata[this.name] = this.value;
            });
            PF.obj.tabs.hashdata.pushed = "tabs";
            PF.fn.show_tab(PF.obj.tabs.hashdata['data-tab']);
        }
    }

    if (window.location.hash) {
        loadTabHash();
    }
    window.onhashchange = loadTabHash;

    $(document).on("click", "[data-action=tab-menu]", function () {
        var $tabs = $(this)
            .closest(".header")
            .find(".content-tabs"),
            visible = $tabs.is(":visible"),
            wrap = $tabs.closest('.content-tabs-wrap');
            $this = $(this);
        wrap.css("display", visible ? "" : "block");
        $this.toggleClass('--hide', visible);
        if (!visible) {
            $tabs.data("classes", $tabs.attr("class"));
            $tabs.removeClass(function (index, css) {
                return (css.match(/\b\w+-hide/g) || []).join(" ");
            });
            // $tabs.hide();
        }
        if (!visible) {
            $this.removeClass("current");
        }
        // $tabs[visible ? "hide" : "show"]();
        if (visible) {
            $tabs.css("display", "").addClass($tabs.data("classes"));
            $this.addClass("current");
        }
    });

    /**
     * LISTING
     * -------------------------------------------------------------------------------------------------
     */

    // Load more (listing +1 page)
    $(document).on("click", "[data-action=load-more]", function (e) {
        if (PF.obj.listing.lockClickMore) {
            return;
        }
        PF.obj.listing.lockClickMore = true;
        $(this)
            .closest(PF.obj.listing.selectors.content_listing_load_more)
            .hide();

        if (
            !PF.fn.is_listing() ||
            $(this)
                .closest(PF.obj.listing.selectors.content_listing)
                .is(":hidden") ||
            $(this)
                .closest("#content-listing-template")
                .exists() ||
            PF.obj.listing.calling
        )
            return;

        PF.fn.listing.queryString.stock_new();
        PF.obj.listing.query_string.seek = $(this).attr("data-seek");
        PF.obj.listing.query_string.page = $(
            PF.obj.listing.selectors.content_listing_visible
        ).data("page");
        PF.obj.listing.query_string.page++;

        PF.fn.listing.ajax();
        e.preventDefault();
        e.stopPropagation();
    });

    // List found on load html -> Do the columns!
    if ($(PF.obj.listing.selectors.pad_content).is(":visible")) {
        PF.fn.listing.show();
        // Bind the infinte scroll
        $(document).on("scroll", function (event) {
            PF.fn.listing.scrollLock = true;
            var $loadMore = $(
                PF.obj.listing.selectors.content_listing_load_more,
                PF.obj.listing.selectors.content_listing_visible
            ).find("button[data-action=load-more]");
            var toScroll = $(document).height() - $(window).height() - 1.5 * document.documentElement.clientHeight;
            if (
                $loadMore.length > 0 &&
                $(window).scrollTop() > toScroll &&
                PF.obj.listing.calling == false
            ) {
                event.preventDefault();
                $loadMore.trigger("click");
            }
        });
    } else {
        $(PF.obj.listing.selectors.content_listing + ".visible").addClass("jsly");
    }

    // Multi-selection tools
    $(document).on(
        "click",
        PF.obj.modal.selectors.root + " [data-switch]",
        function () {
            var $this_modal = $(this).closest(PF.obj.modal.selectors.root);
            $("[data-view=switchable]", $this_modal).hide();
            $("#" + $(this).attr("data-switch"), $this_modal).show();
        }
    );

    $(document).on("click", "[data-toggle]", function () {
        var $target = $("[data-content=" + $(this).data("toggle") + "]");
        var show = !$target.is(":visible");
        $(this).html($(this).data("html-" + (show ? "on" : "off")));
        $target.toggle();
    });

    // Cookie law thing
    $(document).on("click", "[data-action=cookie-law-close]", function () {
        var $cookie = $(this).closest("#cookie-law-banner");
        var cookieName =
            typeof $cookie.data("cookie") !== typeof undefined
                ? $cookie.data("cookie")
                : "PF_COOKIE_LAW_DISPLAY";
        Cookies.set(cookieName, 0, { expires: 365 });
        $cookie.remove();
    });

    Clipboard = new ClipboardJS("[data-action=copy]", {
        text: function (trigger) {
            var $target = $(trigger.getAttribute("data-action-target"));
            var text = $target.is(":input") ? $target.val() : $target.text();
            return text.trim();
        }
    });
    Clipboard.on("success", function (e) {
        var $target = $(e.trigger.getAttribute("data-action-target"));
        $target.highlight();
        e.clearSelection();
    });

    $(window).on("fullscreenchange", function () {
        $("html").toggleClass("--fullscreen", document.fullscreenElement !== null);
    });
});

/**
 * PEAFOWL OBJECT
 * -------------------------------------------------------------------------------------------------
 */
var PF = { fn: {}, str: {}, obj: {} };

/**
 * PEAFOWL CONFIG
 * -------------------------------------------------------------------------------------------------
 */
PF.obj.config = {
    base_url: "",
    json_api: "/json/",
    listing: {
        items_per_page: 24
    },
    animation: {
        easingFn: "ease",
        normal: 400,
        fast: 250
    }
};

/**
 * WINDOW VARS
 * -------------------------------------------------------------------------------------------------
 */

/**
 * LANGUAGE FUNCTIONS
 * -------------------------------------------------------------------------------------------------
 */
PF.obj.l10n = {};

/**
 * Get lang string by key
 * @argument string (lang key string)
 */
// pf: get_pf_lang
PF.fn._s = function (string, s) {
    var string;
    if (typeof string == "undefined") {
        return string;
    }
    if (
        typeof PF.obj.l10n !== "undefined" &&
        typeof PF.obj.l10n[string] !== "undefined"
    ) {
        string = PF.obj.l10n[string][0];
        if (typeof string == "undefined") {
            string = string;
        }
    } else {
        string = string;
    }
    string = string.toString();
    if (typeof s !== "undefined") {
        string = sprintf(string, s);
    }
    return string;
};

PF.fn._n = function (singular, plural, n) {
    var string;
    if (
        typeof PF.obj.l10n !== "undefined" &&
        typeof PF.obj.l10n[singular] !== "undefined"
    ) {
        string = PF.obj.l10n[singular][n == 1 ? 0 : 1];
    } else {
        string = n == 1 ? singular : plural;
    }
    string = typeof string == "undefined" ? singular : string.toString();
    if (typeof n !== "undefined") {
        string = sprintf(string, n);
    }
    return string;
};

/**
 * Extend Peafowl lang
 * Useful to add or replace strings
 * @argument strings obj
 */
// pf: extend_pf_lang
PF.fn.extend_lang = function (strings) {
    $.each(PF.obj.lang_strings, function (i, v) {
        if (typeof strings[i] !== "undefined") {
            $.extend(PF.obj.lang_strings[i], strings[i]);
        }
    });
};

/**
 * HELPER FUNCTIONS
 * -------------------------------------------------------------------------------------------------
 */

PF.fn.get_url_vars = function () {
    var match,
        pl = /\+/g, // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) {
            return decodeURIComponent(escape(s.replace(pl, " ")));
        },
        query = window.location.search.substring(1),
        urlParams = {};

    while ((match = search.exec(query))) {
        urlParams[decode(match[1])] = decode(match[2]);
    }

    return urlParams;
};

PF.fn.get_url_var = function (name) {
    return PF.fn.get_url_vars()[name];
};

PF.fn.is_user_logged = function () {
    return $("#top-bar-user").exists(); // nota: default version
    // It should use backend conditional
};

PF.fn.generate_random_string = function (len) {
    if (typeof len == "undefined") len = 5;
    var text = "";
    var possible =
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for (var i = 0; i < len; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
};

PF.fn.getDateTime = function () {
    var now = new Date();
    var year = now.getFullYear();
    var month = now.getMonth() + 1;
    var day = now.getDate();
    var hour = now.getHours();
    var minute = now.getMinutes();
    var second = now.getSeconds();
    if (month.toString().length == 1) {
        var month = "0" + month;
    }
    if (day.toString().length == 1) {
        var day = "0" + day;
    }
    if (hour.toString().length == 1) {
        var hour = "0" + hour;
    }
    if (minute.toString().length == 1) {
        var minute = "0" + minute;
    }
    if (second.toString().length == 1) {
        var second = "0" + second;
    }
    var dateTime =
        year + "-" + month + "-" + day + " " + hour + ":" + minute + ":" + second;
    return dateTime;
};

PF.fn.htmlEncode = function (value) {
    return $("<div/>")
        .text($.trim(value))
        .html();
};

PF.fn.nl2br = function (str) {
    var breakTag = "<br>";
    return (str + "").replace(
        /([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,
        "$1" + breakTag + "$2"
    );
};

// https://raw.githubusercontent.com/johndwells/phpjs/master/functions/info/version_compare.js
PF.fn.versionCompare = function (v1, v2, operator) {
    this.php_js = this.php_js || {};
    this.php_js.ENV = this.php_js.ENV || {};
    // END REDUNDANT
    // Important: compare must be initialized at 0.
    var i = 0,
        x = 0,
        compare = 0,
        // vm maps textual PHP versions to negatives so they're less than 0.
        // PHP currently defines these as CASE-SENSITIVE. It is important to
        // leave these as negatives so that they can come before numerical versions
        // and as if no letters were there to begin with.
        // (1alpha is < 1 and < 1.1 but > 1dev1)
        // If a non-numerical value can't be mapped to this table, it receives
        // -7 as its value.
        vm = {
            dev: -6,
            alpha: -5,
            a: -5,
            beta: -4,
            b: -4,
            RC: -3,
            rc: -3,
            "#": -2,
            p: 1,
            pl: 1
        },
        // This function will be called to prepare each version argument.
        // It replaces every _, -, and + with a dot.
        // It surrounds any nonsequence of numbers/dots with dots.
        // It replaces sequences of dots with a single dot.
        //    version_compare('4..0', '4.0') == 0
        // Important: A string of 0 length needs to be converted into a value
        // even less than an unexisting value in vm (-7), hence [-8].
        // It's also important to not strip spaces because of this.
        //   version_compare('', ' ') == 1
        prepVersion = function (v) {
            v = ("" + v).replace(/[_\-+]/g, ".");
            v = v.replace(/([^.\d]+)/g, ".$1.").replace(/\.{2,}/g, ".");
            return !v.length ? [-8] : v.split(".");
        };
    // This converts a version component to a number.
    // Empty component becomes 0.
    // Non-numerical component becomes a negative number.
    // Numerical component becomes itself as an integer.
    numVersion = function (v) {
        return !v ? 0 : isNaN(v) ? vm[v] || -7 : parseInt(v, 10);
    };
    v1 = prepVersion(v1);
    v2 = prepVersion(v2);
    x = Math.max(v1.length, v2.length);
    for (i = 0; i < x; i++) {
        if (v1[i] == v2[i]) {
            continue;
        }
        v1[i] = numVersion(v1[i]);
        v2[i] = numVersion(v2[i]);
        if (v1[i] < v2[i]) {
            compare = -1;
            break;
        } else if (v1[i] > v2[i]) {
            compare = 1;
            break;
        }
    }
    if (!operator) {
        return compare;
    }

    // Important: operator is CASE-SENSITIVE.
    // "No operator" seems to be treated as "<."
    // Any other values seem to make the function return null.
    switch (operator) {
        case ">":
        case "gt":
            return compare > 0;
        case ">=":
        case "ge":
            return compare >= 0;
        case "<=":
        case "le":
            return compare <= 0;
        case "==":
        case "=":
        case "eq":
            return compare === 0;
        case "<>":
        case "!=":
        case "ne":
            return compare !== 0;
        case "":
        case "<":
        case "lt":
            return compare < 0;
        default:
            return null;
    }
};

/**
 * Basename
 * http://stackoverflow.com/questions/3820381/need-a-basename-function-in-javascript
 */
PF.fn.baseName = function (str) {
    var base = new String(str).substring(str.lastIndexOf("/") + 1);
    if (base.lastIndexOf(".") != -1) {
        base = base.substring(0, base.lastIndexOf("."));
    }
    return base;
};

// https://stackoverflow.com/a/8809472
PF.fn.guid = function () {
    var d = new Date().getTime();
    if (
        typeof performance !== "undefined" &&
        typeof performance.now === "function"
    ) {
        d += performance.now(); //use high-precision timer if available
    }
    return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
        var r = (d + Math.random() * 16) % 16 | 0;
        d = Math.floor(d / 16);
        return (c === "x" ? r : (r & 0x3) | 0x8).toString(16);
    });
};

PF.fn.md5 = function (string) {
    return SparkMD5.hash(string);
};

/**
 * dataURI to BLOB
 * http://stackoverflow.com/questions/4998908/convert-data-uri-to-file-then-append-to-formdata
 */
PF.fn.dataURItoBlob = function (dataURI) {
    // convert base64/URLEncoded data component to raw binary data held in a string
    var byteString;
    if (dataURI.split(",")[0].indexOf("base64") >= 0) {
        byteString = atob(dataURI.split(",")[1]);
    } else {
        byteString = unescape(dataURI.split(",")[1]);
    }
    // separate out the mime component
    var mimeString = dataURI
        .split(",")[0]
        .split(":")[1]
        .split(";")[0];
    // write the bytes of the string to a typed array
    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new Blob([ia], { type: mimeString });
};

/**
 * Get the min and max value from 1D array
 */
Array.min = function (array) {
    return Math.min.apply(Math, array);
};
Array.max = function (array) {
    return Math.max.apply(Math, array);
};

/**
 * Return the sum of all the values in a 1D array
 */
Array.sum = function (array) {
    return array.reduce(function (pv, cv) {
        return cv + pv;
    });
};

/**
 * Return the size of an object
 */
Object.size = function (obj) {
    var size = 0,
        key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

/**
 * Flatten an object
 */
Object.flatten = function (obj, prefix) {
    if (typeof prefix == "undefined") {
        var prefix = "";
    }
    var result = {};
    $.each(obj, function (key, value) {
        if (value !== null && typeof value == "object") {
            result = $.extend({}, result, Object.flatten(value, prefix + key + "_"));
        } else {
            result[prefix + key] = value;
        }
    });

    return result;
};

/**
 * Tells if the string is a number or not
 */
String.prototype.isNumeric = function () {
    return !isNaN(parseFloat(this)) && isFinite(this);
};

/**
 * Repeats an string
 */
String.prototype.repeat = function (num) {
    return new Array(num + 1).join(this);
};

/**
 * Ucfirst
 */
String.prototype.capitalizeFirstLetter = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
};

/**
 * Replace all
 */
String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, "g"), replacement);
};

/**
 * Tells if the string is a email or not
 */
String.prototype.isEmail = function () {
    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(this);
};

// http://phpjs.org/functions/round/
String.prototype.getRounded = function (precision, mode) {
    var m, f, isHalf, sgn; // helper variables
    precision |= 0; // making sure precision is integer
    m = Math.pow(10, precision);
    value = this;
    value *= m;
    sgn = (value > 0) | -(value < 0); // sign of the number
    isHalf = value % 1 === 0.5 * sgn;
    f = Math.floor(value);

    if (isHalf) {
        switch (mode) {
            case "PHP_ROUND_HALF_DOWN":
                value = f + (sgn < 0); // rounds .5 toward zero
                break;
            case "PHP_ROUND_HALF_EVEN":
                value = f + (f % 2) * sgn; // rouds .5 towards the next even integer
                break;
            case "PHP_ROUND_HALF_ODD":
                value = f + !(f % 2); // rounds .5 towards the next odd integer
                break;
            default:
                value = f + (sgn > 0); // rounds .5 away from zero
        }
    }

    return (isHalf ? value : Math.round(value)) / m;
};

/**
 * Return bytes from Size + Suffix like "10 MB"
 */
String.prototype.getBytes = function () {
    var units = ["KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"],
        suffix = this.toUpperCase().substr(-2);
    if (units.indexOf(suffix) == -1) {
        return this;
    }
    var pow_factor = units.indexOf(suffix) + 1;
    return parseFloat(this) * Math.pow(1000, pow_factor);
};

/**
 * Return size formatted from size bytes
 */
String.prototype.formatBytes = function (round) {
    var bytes = parseInt(this),
        units = ["KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"];
    if (!$.isNumeric(this)) {
        return false;
    }
    if (bytes < 1000) return bytes + " B";
    if (typeof round == "undefined") var round = 2;
    for (var i = 0; i < units.length; i++) {
        var multiplier = Math.pow(1000, i + 1),
            threshold = multiplier * 1000;
        if (bytes < threshold) {
            var size = bytes / multiplier;
            return this.getRounded.call(size, round) + " " + units[i];
        }
    }
};

/**
 * Returns the image url.matches (multiple)
 */
String.prototype.match_image_urls = function () {
    return this.match(
        /\b(?:(http[s]?|ftp[s]):\/\/)?([^:\/\s]+)(:[0-9]+)?((?:\/\w+)*\/)([\w\-\.]+[^#?\s]+)([^#\s]*)?(#[\w\-]+)?\.(?:jpe?g|gif|png|bmp|webp)\b/gim
    );
};

String.prototype.match_urls = function () {
    return this.match(
        /\b(?:(http[s]?|ftp[s]):\/\/)?([^:\/\s]+)(:[0-9]+)?((?:\/\w+)*\/)([\w\-\.]+[^#?\s]+)([^#\s]*)?(#[\w\-]+)?\b/gim
    );
};

// Add ECMA262-5 Array methods if not supported natively
if (!("indexOf" in Array.prototype)) {
    Array.prototype.indexOf = function (find, i /*opt*/) {
        if (i === undefined) i = 0;
        if (i < 0) i += this.length;
        if (i < 0) i = 0;
        for (var n = this.length; i < n; i++) {
            if (i in this && this[i] === find) {
                return i;
            }
        }
        return -1;
    };
}

/**
 * Removes all the array duplicates without loosing the array order.
 */
Array.prototype.array_unique = function () {
    var result = [];
    $.each(this, function (i, e) {
        if ($.inArray(e, result) == -1) result.push(e);
    });
    return result;
};

PF.fn.parseQueryString = function (querystring) {
    var obj = {};
    if (typeof querystring == "undefined" || !querystring) {
        return obj
    }
    var pairs = querystring
            .replace(/^[\?|&]*/, "")
            .replace(/[&|\?]*$/, "")
            .split("&");
    for (var i = 0; i < pairs.length; i++) {
        var split = pairs[i].split("=");
        var key = decodeURIComponent(split[0]);
        var value = split[1] ? decodeURIComponent(split[1]) : null;
        if (obj.hasOwnProperty(key) && !value) {
            continue;
        }
        obj[key] = value;
    }
    return obj;
};

PF.fn.isHttpUrl = function (string) {
    let url;
    try {
        url = new URL(string);
    } catch (_) {
        return false;
    }
    return url.protocol === "http:" || url.protocol === "https:";
};

/**
 * @param string querystring_or_url
 * "?a=1&b=2"
 * "a=1&b=2"
 * "http(s)://example.com/?a=1&b=2"
 */
PF.fn.deparam = function (querystring_or_url) {
    if (typeof querystring_or_url == "undefined" || !querystring_or_url) return;
    var querystring = querystring_or_url.substring(querystring_or_url.indexOf("?") + 1);
    if(PF.fn.isHttpUrl(querystring_or_url) && querystring_or_url == querystring) {
        return {};
    }
    return PF.fn.parseQueryString(querystring);
};

// http://stackoverflow.com/a/1634841/1145912
String.prototype.removeURLParameter = function (key) {
    var deparam = PF.fn.deparam(this.toString());
    if (typeof deparam[key] !== "undefined") {
        delete deparam[key];
    }
    return decodeURIComponent($.param(deparam));
};

String.prototype.changeURLParameterValue = function (key, value) {
    var base = this.substring(0, this.indexOf("?"));
    if(base == "") {
        base = this;
    }
    var deparam = PF.fn.deparam(this.toString());
    deparam[key] = value;
    return base + "?" + decodeURIComponent($.param(deparam));
};

String.prototype.addURLParameterNoCache = function () {
    var url = this.toString();
    var params = PF.fn.deparam(url);
    if(Object.keys(params).length === 0) {
        var url = this.replace(/\/?$/, '/');
    }
    return url.changeURLParameterValue("nocache", new Date().getTime());
};

/**
 * Truncate the middle of the URL just like Firebug
 * From http://stackoverflow.com/questions/10903002/shorten-url-for-display-with-beginning-and-end-preserved-firebug-net-panel-st
 */
String.prototype.truncate_middle = function (l) {
    var l = typeof l != "undefined" ? l : 40,
        chunk_l = l / 2,
        url = this.replace(/https?:\/\//g, "");

    if (url.length <= l) {
        return url;
    }

    function shortString(s, l, reverse) {
        var stop_chars = [" ", "/", "&"],
            acceptable_shortness = l * 0.8, // When to start looking for stop characters
            reverse = typeof reverse != "undefined" ? reverse : false,
            s = reverse
                ? s
                    .split("")
                    .reverse()
                    .join("")
                : s,
            short_s = "";

        for (var i = 0; i < l - 1; i++) {
            short_s += s[i];
            if (i >= acceptable_shortness && stop_chars.indexOf(s[i]) >= 0) {
                break;
            }
        }
        if (reverse) {
            return short_s
                .split("")
                .reverse()
                .join("");
        }
        return short_s;
    }

    return (
        shortString(url, chunk_l, false) + "..." + shortString(url, chunk_l, true)
    );
};

/**
 * Compare 2 arrays/objects
 * http://stackoverflow.com/questions/1773069/using-jquery-to-compare-two-arrays
 */
jQuery.extend({
    compare: function (a, b) {
        var obj_str = "[object Object]",
            arr_str = "[object Array]",
            a_type = Object.prototype.toString.apply(a),
            b_type = Object.prototype.toString.apply(b);
        if (a_type !== b_type) {
            return false;
        } else if (a_type === obj_str) {
            return $.compareObject(a, b);
        } else if (a_type === arr_str) {
            return $.compareArray(a, b);
        }
        return a === b;
    },
    compareArray: function (arrayA, arrayB) {
        var a, b, i, a_type, b_type;
        if (arrayA === arrayB) {
            return true;
        }
        if (arrayA.length != arrayB.length) {
            return false;
        }
        a = jQuery.extend(true, [], arrayA);
        b = jQuery.extend(true, [], arrayB);
        a.sort();
        b.sort();
        for (i = 0, l = a.length; i < l; i += 1) {
            a_type = Object.prototype.toString.apply(a[i]);
            b_type = Object.prototype.toString.apply(b[i]);
            if (a_type !== b_type) {
                return false;
            }
            if ($.compare(a[i], b[i]) === false) {
                return false;
            }
        }
        return true;
    },
    compareObject: function (objA, objB) {
        var i, a_type, b_type;
        // Compare if they are references to each other
        if (objA === objB) {
            return true;
        }
        if (Object.keys(objA).length !== Object.keys(objB).length) {
            return false;
        }
        for (i in objA) {
            if (objA.hasOwnProperty(i)) {
                if (typeof objB[i] === "undefined") {
                    return false;
                } else {
                    a_type = Object.prototype.toString.apply(objA[i]);
                    b_type = Object.prototype.toString.apply(objB[i]);
                    if (a_type !== b_type) {
                        return false;
                    }
                }
            }
            if ($.compare(objA[i], objB[i]) === false) {
                return false;
            }
        }
        return true;
    }
});

/**
 * Tells if a selector exits in the dom
 */
jQuery.fn.exists = function () {
    return this.length > 0;
};

/**
 * Replace .svg for .png
 */
jQuery.fn.replace_svg = function () {
    if (!this.attr("src")) return;
    $(this).each(function () {
        $(this).attr(
            "src",
            $(this)
                .attr("src")
                .replace(".svg", ".png")
        );
    });
};

/**
 * Detect fluid layout
 * nota: deberia ir en PF
 */
jQuery.fn.is_fluid = function () {
    return true;
};

/**
 * jQueryfy the form data
 * Bind the attributes and values of form data to be manipulated by DOM fn
 */
jQuery.fn.bindFormData = function () {
    $(":input", this).each(function () {
        var safeVal = PF.fn.htmlEncode($(this).val());

        if ($(this).is("input")) {
            this.setAttribute("value", this.value);
            if (this.checked) {
                this.setAttribute("checked", "checked");
            } else {
                this.removeAttribute("checked");
            }
        }
        if ($(this).is("textarea")) {
            $(this).html(safeVal);
        }
        if ($(this).is("select")) {
            var index = this.selectedIndex,
                i = 0;
            $(this)
                .children("option")
                .each(function () {
                    if (i++ != index) {
                        this.removeAttribute("selected");
                    } else {
                        this.setAttribute("selected", "selected");
                    }
                });
        }
    });
    return this;
};

/** jQuery.formValues: get or set all of the name/value pairs from child input controls
 * @argument data {array} If included, will populate all child controls.
 * @returns element if data was provided, or array of values if not
 * http://stackoverflow.com/questions/1489486/jquery-plugin-to-serialize-a-form-and-also-restore-populate-the-form
 */
jQuery.fn.formValues = function (data) {
    var els = $(":input", this);
    if (typeof data != "object") {
        data = {};
        $.each(els, function () {
            if (
                this.name &&
                !this.disabled &&
                (this.checked ||
                    /select|textarea/i.test(this.nodeName) ||
                    /color|date|datetime|datetime-local|email|month|range|search|tel|time|url|week|text|number|hidden|password/i.test(
                        this.type
                    ))
            ) {
                if (this.name.match(/^.*\[\]$/) && this.checked) {
                    if (typeof data[this.name] == "undefined") {
                        data[this.name] = [];
                    }
                    data[this.name].push($(this).val());
                } else {
                    data[this.name] = $(this).val();
                }
            }
        });
        return data;
    } else {
        $.each(els, function () {
            if (this.name.match(/^.*\[\]$/) && typeof data[this.name] == "object") {
                $(this).prop("checked", data[this.name].indexOf($(this).val()) !== -1);
            } else {
                if (this.name && data[this.name]) {
                    if (/checkbox|radio/i.test(this.type)) {
                        $(this).prop("checked", data[this.name] == $(this).val());
                    } else {
                        $(this).val(data[this.name]);
                    }
                } else if (/checkbox|radio/i.test(this.type)) {
                    $(this).removeProp("checked");
                }
            }
        });
        return $(this);
    }
};

jQuery.fn.storeformData = function (dataname) {
    if (
        typeof dataname == "undefined" &&
        typeof $(this).attr("id") !== "undefined"
    ) {
        dataname = $(this).attr("id");
    }
    if (typeof dataname !== "undefined")
        $(this).data(dataname, $(this).formValues());
    return this;
};

/**
 * Compare the $.data values against the current DOM values
 * It relies in using $.data to store the previous value
 * Data must be stored using $.formValues()
 *
 * @argument dataname string name for the data key
 */
jQuery.fn.is_sameformData = function (dataname) {
    var $this = $(this);
    if (typeof dataname == "undefined") dataname = $this.attr("id");
    return jQuery.compare($this.formValues(), $this.data(dataname));
};

/**
 * Prevent non-numeric keydown
 * Allows only numeric keys to be entered on the target event
 */
jQuery.Event.prototype.keydown_numeric = function () {
    var e = this;

    if (e.shiftKey) {
        e.preventDefault();
        return false;
    }

    var key = e.charCode || e.keyCode,
        target = e.target,
        value = $(target).val() == "" ? 0 : parseInt($(target).val());

    if (key == 13) {
        // Allow enter key
        return true;
    }

    if (
        key == 46 ||
        key == 8 ||
        key == 9 ||
        key == 27 ||
        // Allow: Ctrl+A
        (key == 65 && e.ctrlKey === true) ||
        // Allow: home, end, left, right
        (key >= 35 && key <= 40)
    ) {
        // let it happen, don't do anything
        return true;
    } else {
        // Ensure that it is a number and stop the keypress
        if ((key < 48 || key > 57) && (key < 96 || key > 105)) {
            e.preventDefault();
        }
    }
};

/**
 * Detect canvas support
 */
PF.fn.is_canvas_supported = function () {
    var elem = document.createElement("canvas");
    return !!(elem.getContext && elem.getContext("2d"));
};

/**
 * Detect validity support
 */
PF.fn.is_validity_supported = function () {
    var i = document.createElement("input");
    return typeof i.validity === "object";
};

PF.fn.getScrollBarWidth = function () {
    var inner = document.createElement("p");
    inner.style.width = "100%";
    inner.style.height = "200px";

    var outer = document.createElement("div");
    outer.style.position = "absolute";
    outer.style.top = "0px";
    outer.style.left = "0px";
    outer.style.visibility = "hidden";
    outer.style.width = "200px";
    outer.style.height = "150px";
    outer.style.overflow = "hidden";
    outer.appendChild(inner);

    document.body.appendChild(outer);
    var w1 = inner.offsetWidth;
    outer.style.overflow = "scroll";
    var w2 = inner.offsetWidth;
    if (w1 == w2) w2 = outer.clientWidth;

    document.body.removeChild(outer);

    return w1 - w2;
};

PF.str.ScrollBarWidth = PF.fn.getScrollBarWidth();

/**
 * Updates the notifications button
 */
PF.fn.top_notifications_viewed = function () {
    var $top_bar_notifications = $("[data-action=top-bar-notifications]"),
        $notifications_lists = $(
            ".top-bar-notifications-list",
            $top_bar_notifications
        ),
        $notifications_count = $(".top-btn-number", $top_bar_notifications);

    if ($(".persistent", $top_bar_notifications).exists()) {
        $notifications_count
            .text($(".persistent", $top_bar_notifications).length)
            .addClass("on");
    } else {
        $notifications_count.removeClass("on");
    }
};

/**
 * bind tipTip for the $target with options
 * @argument $target selector or jQuery obj
 * @argument options obj
 */
PF.fn.bindtipTip = function ($target, options) {
    if (typeof $target == "undefined") $target = $("body");
    if ($target instanceof jQuery == false) $target = $($target);
    var bindtipTipoptions = {
        delay: 0,
        content: false,
        fadeIn: 0
    };
    if (typeof options !== "undefined") {
        if (typeof options.delay !== "undefined")
            bindtipTipoptions.delay = options.delay;
        if (typeof options.content !== "undefined")
            bindtipTipoptions.content = options.content;
        if (typeof options.content !== "undefined")
            bindtipTipoptions.fadeIn = options.fadeIn;
    }
    if ($target.attr("rel") !== "tooltip") $target = $("[rel=tooltip]", $target);

    $target.each(function () {
        if (
            (typeof $(this).attr("href") !== "undefined" ||
                typeof $(this).data("href") !== "undefined") &&
            PF.fn.isDevice(["phone", "phablet", "tablet"])
        ) {
            return true;
        }
        var position =
            typeof $(this).data("tiptip") == "undefined"
                ? "bottom"
                : $(this).data("tiptip");
        if (PF.fn.isDevice(["phone", "phablet"])) {
            position = "top";
        }
        $(this).tipTip({
            delay: bindtipTipoptions.delay,
            defaultPosition: position,
            content: bindtipTipoptions.content,
            fadeIn: bindtipTipoptions.fadeIn,
            fadeOut: 0
        });
    });
};

/**
 * form modal changed
 * Detects if the form modal (fullscreen) has changed or not
 * Note: It relies in that you save a serialized data to the
 */
PF.fn.form_modal_has_changed = function () {
    if ($(PF.obj.modal.selectors.root).is(":hidden")) return;
    if (typeof $("html").data("modal-form-values") == typeof undefined) return;
    var data_stored = $("html").data("modal-form-values");
    var data_modal = PF.fn.parseQueryString(
        $(":input:visible", PF.obj.modal.selectors.root).serialize()
    );
    var has_changed = false;
    var keys = $.extend({}, data_stored, data_modal);
    for (var k in keys) {
        if (data_stored[k] !== data_modal[k]) {
            has_changed = true;
            break;
        }
    }
    return has_changed;
};

/**
 * PEAFOWL CONDITIONALS
 * -------------------------------------------------------------------------------------------------
 */

PF.fn.is_listing = function () {
    return $(PF.obj.listing.selectors.content_listing).exists();
};

PF.fn.is_tabs = function () {
    return $(".content-tabs").exists();
};

/**
 * PEAFOWL EFFECTS
 * -------------------------------------------------------------------------------------------------
 */

/**
 * Shake effect
 * Shakes the element using CSS animations.
 * @argument callback fn
 */
jQuery.fn.shake = function (callback) {
    this.each(function (init) {
        var $this = $(this);
        if($this.data("shake") == 0) {
            return this;
        }
        $this.addClass("animate shake")
            .promise()
            .done(function () {
                setTimeout(function () {
                    $this.removeClass("shake")
                }, 820);
            });

    });
    if (typeof callback == "function") callback();
    return this;
};

/**
 * Highlight effect
 * Changes the background of the element to a highlight color and revert to original
 * @argument string (yellow|red|hex-color)
 */
jQuery.fn.highlight = function (color) {
    if (this.is(":animated") || !this.exists()) return this;
    if (typeof color == "undefined") color = "yellow";

    var fadecolor = color;
    var forecolor = "#333";
    switch (color) {
        case "yellow":
            fadecolor = "#FFFBA2";
            break;
        case "red":
            fadecolor = "#FF7F7F";
            break;
        default:
            fadecolor = color;
            break;
    }
    var base_background_color = $(this).css("background-color");
    var base_foreground_color = $(this).css("color");

    $(this)
        .css({
            color: forecolor,
            backgroundColor: fadecolor
        })
        .delay(100)
        .animate(
            { backgroundColor: base_background_color, color: base_foreground_color },
            150,
            function () {
                $(this).css({backgroundColor: base_background_color, color: base_foreground_color});
            }
        );
    return this;
};

/**
 * Peafowl slidedown effect
 * Bring the element using slideDown-type effect
 * @argument speed (fast|normal|slow|int)
 * @argument callback fn
 */
jQuery.fn.pf_slideDown = function (speed, callback) {
    var default_speed = "normal",
        this_length = $(this).length,
        css_prechanges,
        css_animation,
        animation_speed;

    if (typeof speed == "function") {
        callback = speed;
        speed = default_speed;
    }
    if (typeof speed == "undefined") {
        speed = default_speed;
    }

    $(this).each(function (index) {
        var this_css_top = parseInt($(this).css("top")),
            to_top = this_css_top > 0 ? this_css_top : 0;

        if (speed == 0) {
            (css_prechanges = { display: "block", opacity: 0 }),
                (css_animation = { opacity: 1 }),
                (animation_speed = jQuery.speed("fast").duration);
        } else {
            css_prechanges = {
                top: -$(this).outerHeight(true),
                opacity: 1,
                display: "block"
            };
            css_animation = { top: to_top };
            animation_speed = jQuery.speed(speed).duration;
        }

        $(this).data("originalTop", $(this).css("top"));
        $(this)
            .css(css_prechanges)
            .animate(css_animation, animation_speed, function () {
                if (index == this_length - 1) {
                    if (typeof callback == "function") {
                        callback();
                    }
                }
            });
    });

    return this;
};

jQuery.fn.is_in_viewport = function () {
    var rect = $(this)[0].getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <=
        (window.innerHeight ||
            document.documentElement.clientHeight) &&
        rect.right <=
        (window.innerWidth ||
            document.documentElement.clientWidth)
    );
};

jQuery.fn.is_within_viewport = function (height) {
    var rect = $(this)[0].getBoundingClientRect();
    if (typeof height == "undefined") {
        height = 0;
    }
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        (rect.top + height) <=
        (window.innerHeight ||
            document.documentElement.clientHeight) &&
        rect.right <=
        (window.innerWidth ||
            document.documentElement.clientWidth)
    );
};


/**
 * Visible on current window stuff
 */
jQuery.fn.getWindowCutoff = function () {
    var rect = {
        top: $(this).offset().top,
        left: $(this).offset().left,
        width: $(this).outerWidth(),
        height: $(this).outerHeight()
    };
    rect.right = rect.left + rect.width;
    rect.bottom = rect.top + rect.height;
    var detected = false;
    var cutoff = {
        top: rect.top > 0 ? 0 : rect.top,
        right: document.body.clientWidth - rect.right,
        bottom: document.body.clientHeight - rect.bottom,
        left: rect.left > 0 ? 0 : rect.left
    };
    for (var key in cutoff) {
        if (cutoff[key] < 0) {
            detected = true;
        } else {
            cutoff[key] = 0;
        }
    }
    if (!detected) return null;
    return cutoff;
};

/**
 * Scroll the window to the target.
 * @argument target selector
 * @argument callback fn
 */
PF.fn.scroll = function (target, callback) {
    if (typeof target == "function") {
        var callback = target,
            target = "";
    }

    var pxtop = parseInt($("body").css("margin-top"));
    if (pxtop == 0 && $(".top-bar-placeholder").exists()) {
        pxtop = $(".top-bar-placeholder").height();
    }

    if (!$(target).exists()) target = "html";
    $("body,html").animate(
        { scrollTop: $(target).offset().top - pxtop },
        "normal",
        function () {
            if (typeof callback == "function") callback();
        }
    );
};

PF.fn.close_pops = function (e) {
    $(".pop-box:visible").each(function() {
        $(this)
            .hide()
            .attr("style", "")
            .closest(".pop-btn")
            .removeClass("opened")
    });
    $("body").removeClass("pop-box-show pop-box-show--top");
    $("#pop-box-mask").remove();
};

/**
 * Bring up a nice growl-like alert
 */
PF.fn.growl = {
    selectors: {
        root: "#growl"
    },

    str: {
        timeout: null,
        timeoutcall: false
    },

    /**
     * Fires the growl
     * @argument options object
     */
    call: function (options) {
        if (typeof options == "undefined") return;
        if (typeof options == "string") {
            options = { message: options };
        }
        if (typeof options.message == "undefined") return;
        options.message = PF.fn.htmlEncode(options.message);
        var growl_options, $growl, growl_class, growl_color;
        growl_options = {
            message: options.message,
            insertTo: "body",
            where: "before",
            color: "default",
            css: {},
            classes: "",
            expires: 0,
            callback: function () { }
        };

        for (key in growl_options) {
            if (typeof options[key] !== "undefined") {
                if (key.match("/^(callback)$/")) {
                    if (typeof options[key] == "function") {
                        growl_options[key] = options[key];
                    }
                } else {
                    growl_options[key] = options[key];
                }
            }
        }

        if (!$(growl_options.insertTo).exists()) {
            growl_options.insertTo = "body";
        }

        if ($(PF.fn.growl.selectors.root).exists()) {
            if ($(PF.fn.growl.selectors.root).text() == growl_options.message) {
                $(PF.fn.growl.selectors.root).shake();
                return;
            }
            $(PF.fn.growl.selectors.root).remove();
        }

        $growl = $(
            '<div id="' +
            PF.fn.growl.selectors.root.replace("#", "") +
            '" class="growl animated">' +
            growl_options.message +
            '<span class="icon fas fa-times" data-action="close"></span></div>'
        )
            .css(growl_options.css)
            .addClass(growl_options.classes);

        growl_class = growl_options.insertTo !== "body" ? "static" : "";

        switch (growl_options.color) {
            case "dark":
                growl_color = "dark";
                break;
            default:
                growl_color = "";
                break;
        }

        $growl.addClass(growl_class + " " + growl_color);

        if (growl_options.where == "before") {
            $(growl_options.insertTo).prepend($growl.hide());
        } else {
            $(growl_options.insertTo).append($growl.hide());
        }

        $growl.pf_slideDown(growl_class == "static" ? 0 : 200, function () {
            if (typeof growl_options.callback == "function") {
                growl_options.callback();
            }
        });

        $(document).on("click", ".growl [data-action=close]", function (e) {
            PF.fn.growl.close(true);
        });

        if (growl_options.expires > 0) {
            if (typeof this.str.timeout == "number") {
                clearTimeout(this.str.timeout);
            }
            this.str.timeout = setTimeout(function () {
                PF.fn.growl.str.timeoutcall = true;
                PF.fn.growl.close();
            }, growl_options.expires);
        }
    },

    /**
     * Fires an expirable growl (will close after time)
     * @argument msg string
     * @argument time int (ms)
     */
    expirable: function (msg, time) {
        if (typeof msg == "undefined") return;
        if (typeof time == "undefined") time = 5000;
        PF.fn.growl.call({ message: msg, expires: time });
    },

    /**
     * Closes the growl
     * @argument callback fn
     */
    close: function (forced, callback) {
        var $growl = $(PF.fn.growl.selectors.root);

        if (forced) {
            this.str.timeout = null;
            this.str.timeoutcall = false;
            clearTimeout(this.str.timeout);
        }

        if (
            !$growl.exists() ||
            (typeof this.str.timeout == "number" && !this.str.timeoutcall)
        ) {
            return;
        }

        $growl.fadeOut("fast", function () {
            $(this).remove();
            if (typeof callback == "function") {
                callback();
            }
        });
    },
};

/**
 * Bring up a nice fullscreen modal
 */
PF.obj.modal = {
    type: "",
    selectors: {
        root: "#fullscreen-modal",
        box: "#fullscreen-modal-box",
        body: "#fullscreen-modal-body",
        login: "[data-modal=login]",
        changes_confirm: "#fullscreen-changes-confirm",
        btn_container: ".btn-container",
        close_buttons:
            ".close-modal,.cancel-modal,[data-action=cancel],[data-action-close]",
        submit_button: "[data-action=submit]",
        growl_placeholder: "#fullscreen-growl-placeholder"
    },
    ajax: {
        url: "",
        deferred: {}
    },
    locked: false,
    form_data: {},
    XHR: {},
    prevented: false
};
PF.obj.modal.$close_buttons = $(
    PF.obj.modal.selectors.close_buttons,
    PF.obj.modal.selectors.root
);

PF.fn.modal = {
    str: {
        transition: "all " + PF.obj.config.animation.fast + "ms " + PF.obj.config.animation.easingFn
    },

    /**
     * Fires the modal
     * @argument options object
     */
    call: function (options) {
        var modal_options, modal_base_template, modal_message;

        if (typeof options == "undefined") return;
        if (
            typeof options.template !== "undefined" &&
            typeof options.type == "undefined"
        )
            options.type = "html";
        if (
            (typeof options.title == "undefined" ||
                typeof options.message == "undefined") &&
            (options.type !== "login" && options.type !== "html")
        )
            return;

        PF.fn.growl.close(true);

        modal_options = {
            forced: false,
            type: "confirm",
            title: options.title,
            message: options.message,
            html: false,
            template: options.template,
            buttons: true,
            button_submit: PF.fn._s("Submit"),
            txt_or: PF.fn._s("or"),
            button_cancel: PF.fn._s("cancel"),
            ajax: { url: null, data: null, deferred: {} },
            confirm: function () { },
            cancel: function () {
                PF.fn.modal.close();
            },
            load: function () { },
            callback: function () { }
        };

        for (key in modal_options) {
            if (typeof options[key] !== "undefined") {
                if (/^cancel|confirm|callback$/.test(key)) {
                    if (typeof options[key] == "function") {
                        modal_options[key] = options[key];
                    }
                } else {
                    modal_options[key] = options[key];
                }
            }
        }

        if (
            typeof options.ajax !== "undefined" &&
            !options.ajax.url &&
            options.ajax.deferred
        ) {
            modal_options.ajax.url = PF.obj.config.json_api;
        }

        if (modal_options.type == "login") {
            modal_options.buttons = false;
        }

        if (modal_options.type == "confirm") {
            modal_options.button_submit = PF.fn._s("Confirm");
        }

        var overlay_background = "black";
        var modal_base_template = [
            '<div id="',
            PF.obj.modal.selectors.root.replace("#", ""),
            '"class="fullscreen ' + overlay_background + '"><div id="',
            PF.obj.modal.selectors.box.replace("#", ""),
            '"class="clickable"><div id="',
            PF.obj.modal.selectors.body.replace("#", ""),
            '">%MODAL_BODY%</div>%MODAL_BUTTONS%<span class="close-modal icon--close fas fa-times" data-action="close-modal" title="Esc"></span></div></div>'
        ].join("");

        var modal_buttons = modal_options.buttons
            ? [
                '<div class="',
                PF.obj.modal.selectors.btn_container.replace(".", ""),
                '"><button class="btn btn-input accent" data-action="submit" type="submit" title="Ctrl/Cmd + Enter">',
                '<span class="btn-icon fas fa-check-circle user-select-none"></span>',
                '<span class="btn-text  user-select-none">',
                modal_options.button_submit,
                '</span>',
                '</button></div>'
            ].join("")
            : "";

        if (modal_options.type == "login") {
            modal_options.template =
                typeof modal_options.template == "undefined"
                    ? $(PF.obj.modal.selectors.login).html()
                    : modal_options.template;
        }

        var modalBodyHTML;

        switch (modal_options.type) {
            case "html":
            case "login":
                modalBodyHTML = modal_options.template;
                break;
            case "confirm":
            default:
                modal_message = modal_options.message;
                if (!modal_options.html) {
                    modal_message = "<p>" + modal_message + "</p>";
                }
                modalBodyHTML = "<h1>" + modal_options.title + "</h1>" + modal_message;
                break;
        }

        if (typeof modalBodyHTML == "undefined") {
            console.log("PF Error: Modal content is empty");
            return;
        }

        modal_base_template = modal_base_template
            .replace("%MODAL_BODY%", modalBodyHTML)
            .replace("%MODAL_BUTTONS%", modal_buttons)
            .replace(/template-tooltip/g, "tooltip");

        $(PF.obj.modal.selectors.root).remove();

        $("body").data("hasOverflowHidden", $("body").hasClass("overflow-hidden") && !$("body").hasClass("pop-box-show"));
        $("body")
            .prepend(modal_base_template)
            .addClass("overflow-hidden");

        this.fixScrollbars();

        $("[rel=tooltip]", PF.obj.modal.selectors.root).each(function () {
            PF.fn.bindtipTip(this, { content: $(this).data("title") });
        });

        if (
            $(
                ":button, input[type=submit], input[type=reset]",
                PF.obj.modal.selectors.root
            ).length > 0
        ) {
            var $form = $("form", PF.obj.modal.selectors.root);
            if ($form.exists()) {
                $form.append(
                    $(
                        $(
                            PF.obj.modal.selectors.btn_container,
                            PF.obj.modal.selectors.root
                        ).html()
                    ).wrapInner(PF.obj.modal.selectors.btn_container.replace(".", ""))
                );
                $(
                    PF.obj.modal.selectors.btn_container,
                    PF.obj.modal.selectors.root
                ).each(function () {
                    if (
                        !$(this)
                            .closest("form")
                            .exists()
                    ) {
                        $(this).remove();
                    }
                });
            } else {
                $(PF.obj.modal.selectors.box, PF.obj.modal.selectors.root).wrapInner(
                    "<form data-js><form />"
                );
            }
        }

        modal_options.callback();

        $(PF.obj.modal.selectors.box).css({
            transform: "scale(0.7)",
            opacity: 0,
            transition: PF.fn.modal.str.transition
        });
        $(PF.obj.modal.selectors.root).addClass("--show");
        setTimeout(function () {
            $(PF.obj.modal.selectors.root).css({ opacity: 1 });
            $(PF.obj.modal.selectors.box).css({ transform: "scale(1)", opacity: 1 });
            if (typeof PFrecaptchaCallback !== typeof undefined) {
                PFrecaptchaCallback();
            }
            setTimeout(function () {
                $("html").data(
                    "modal-form-values",
                    PF.fn.parseQueryString(
                        $(":input:visible", PF.obj.modal.selectors.root).serialize()
                    )
                );
                if (typeof modal_options.load == "function") {
                    modal_options.load();
                }
                $(PF.obj.modal.selectors.box).css({ transform: ""});
            }, PF.obj.config.animation.fast);
            PF.fn.modal.styleAware();
        }, 10);

        $(PF.obj.modal.selectors.root).on("click", function (e) {
            var $this = $(e.target),
                _this = this;
            if (PF.obj.modal.locked || $this.is(PF.obj.modal.selectors.root)) {
                return;
            }
            var isCloseButton = $this.is(PF.obj.modal.selectors.close_buttons)
                || $this.closest(PF.obj.modal.selectors.close_buttons).exists();
            var isSubmitButton = $this.is(PF.obj.modal.selectors.submit_button)
                || $this.closest(PF.obj.modal.selectors.submit_button).exists();
            var isButton = isCloseButton || isSubmitButton;
            if (
                $this.closest(PF.obj.modal.selectors.changes_confirm).exists() &&
                isButton
            ) {
                $(PF.obj.modal.selectors.changes_confirm).remove();

                if (isCloseButton) {
                    $(PF.obj.modal.selectors.box, _this).fadeIn("fast", function () {
                        $(this).css("transition", PF.fn.modal.str.transition);
                    });
                } else {
                    PF.fn.modal.close();
                }
            } else {
                if (
                    !$this.closest(".clickable").exists() ||
                    isCloseButton
                ) {
                    PF.fn.growl.close();
                    modal_options.cancel();
                }

                if (isSubmitButton) {
                    if (modal_options.confirm() === false) {
                        return false;
                    }
                    var modal_submit_continue = true;
                    if (
                        $("input, textarea, select", PF.obj.modal.selectors.root).not(
                            ":input[type=button], :input[type=submit], :input[type=reset]"
                        ).length > 0 &&
                        !PF.fn.form_modal_has_changed() &&
                        !modal_options.forced
                    ) {
                        modal_submit_continue = false;
                    }

                    if (modal_submit_continue) {
                        if (modal_options.ajax.url) {
                            var $btn_container = $(
                                PF.obj.modal.selectors.btn_container,
                                PF.obj.modal.selectors.root
                            );
                            PF.obj.modal.locked = true;

                            $btn_container
                                .first()
                                .clone()
                                .height($btn_container.height())
                                .html("")
                                .addClass("loading")
                                .appendTo(PF.obj.modal.selectors.root + " form");
                            $btn_container.hide();

                            PF.obj.modal.$close_buttons.hide();

                            var modal_loading_msg;

                            switch (PF.obj.modal.type) {
                                case "edit":
                                    modal_loading_msg = PF.fn._s("Saving");
                                    break;
                                case "confirm":
                                case "form":
                                default:
                                    modal_loading_msg = PF.fn._s("Sending");
                                    break;
                            }

                            PF.fn.loading.inline(
                                $(
                                    PF.obj.modal.selectors.btn_container + ".loading",
                                    PF.obj.modal.selectors.root
                                ),
                                { size: "small", message: modal_loading_msg, valign: "center" }
                            );

                            $(PF.obj.modal.selectors.root).disableForm();

                            if (
                                !$.isEmptyObject(PF.obj.modal.form_data) ||
                                (typeof options.ajax !== "undefined" &&
                                    typeof options.ajax.data == "undefined")
                            ) {
                                modal_options.ajax.data = PF.obj.modal.form_data;
                            }
                            PF.obj.modal.XHR = $.ajax({
                                url: modal_options.ajax.url,
                                type: "POST",
                                data: modal_options.ajax.data //PF.obj.modal.form_data // $.param ?
                            }).complete(function (XHR) {
                                PF.obj.modal.locked = false;

                                if (XHR.status == 200) {
                                    var success_fn =
                                        typeof modal_options.ajax.deferred !== "undefined" &&
                                            typeof modal_options.ajax.deferred.success !== "undefined"
                                            ? modal_options.ajax.deferred.success
                                            : null;

                                    if (typeof success_fn == "function") {
                                        PF.fn.modal.close(function () {
                                            if (typeof success_fn == "function") {
                                                success_fn(XHR);
                                            }
                                        });
                                    } else if (typeof success_fn == "object") {
                                        if (typeof success_fn.before == "function") {
                                            success_fn.before(XHR);
                                        }
                                        if (typeof success_fn.done == "function") {
                                            success_fn.done(XHR);
                                        }
                                    }
                                } else {
                                    $(PF.obj.modal.selectors.root).enableForm();
                                    $(
                                        PF.obj.modal.selectors.btn_container + ".loading",
                                        PF.obj.modal.selectors.root
                                    ).remove();
                                    $btn_container.css("display", "");

                                    if (
                                        typeof modal_options.ajax.deferred !== "undefined" &&
                                        typeof modal_options.ajax.deferred.error == "function"
                                    ) {
                                        modal_options.ajax.deferred.error(XHR);
                                    } else {
                                        var message = PF.fn._s(
                                            "An error occurred. Please try again later."
                                        );
                                        if(XHR.responseJSON.error.message) {
                                            message = XHR.responseJSON.error.message;
                                        }
                                        PF.fn.growl.call(message);
                                    }
                                }
                            });
                        } else {
                            // No ajax behaviour
                            PF.fn.modal.close(modal_options.callback());
                        }
                    }
                }
            }
        });
    },

    styleAware: function () {
        if(!$(PF.obj.modal.selectors.root).exists()) {
            return;
        }
        $(PF.obj.modal.selectors.root)
            .toggleClass(
                "--has-scrollbar",
                $(PF.obj.modal.selectors.root).hasScrollbar().vertical
            );
    },

    /**
     * Fires a confirm modal
     * @argument options object
     */
    confirm: function (options) {
        options.type = "confirm";
        if (typeof options.title == "undefined") {
            options.title = PF.fn._s("Confirm action");
        }
        PF.fn.modal.call(options);
    },

    /**
     * Fires a simple info modal
     */
    simple: function (options) {
        if (typeof options == "string") options = { message: options };
        if (typeof options.buttons == "undefined") options.buttons = false;
        if (typeof options.title == "undefined")
            options.title = PF.fn._s("information");
        PF.fn.modal.call(options);
    },

    fixScrollbars: function () {
        if (!$(PF.obj.modal.selectors.root).exists()) {
            return;
        }
        var $targets = {
            padding: $(".fixed, .position-fixed"),
            margin: $("html")
        };
        var properties = {};
        if (
            PF.str.ScrollBarWidth > 0 &&
            $("html").hasScrollbar().vertical &&
            !$("body").data("hasOverflowHidden")
        ) {
            properties.padding = PF.str.ScrollBarWidth + "px";
            properties.margin = PF.str.ScrollBarWidth + "px";
        } else {
            properties.padding = "";
            properties.margin = "";
        }
        $targets.padding.css({ paddingRight: properties.padding });
        $targets.margin.css({ marginRight: properties.margin });
    },

    /**
     * Closes the modal
     * @argument callback fn
     */
    close: function (callback) {
        if (!$(PF.obj.modal.selectors.root).exists()) {
            return;
        }
        PF.fn.growl.close(true);
        $("[rel=tooltip]", PF.obj.modal.selectors.root).tipTip("hide");
        $(PF.obj.modal.selectors.box).css({ transform: "scale(0.5)", opacity: 0 });
        $(PF.obj.modal.selectors.root).css({ opacity: 0 });
        setTimeout(function () {
            if (PF.str.ScrollBarWidth > 0 && $("html").hasScrollbar().vertical) {
                $(".fixed, .position-fixed").css({ paddingRight: "" });
            }
            $("html").css({ marginRight: "" });
            if (!$("body").data("hasOverflowHidden")) {
                $("html,body").removeClass("overflow-hidden");
            }
            $("body").removeData("hasOverflowHidden");
            $(PF.obj.modal.selectors.root).remove();
            if (typeof callback == "function") callback();
        }, PF.obj.config.animation.normal);
    }
};

PF.fn.keyFeedback = {
    enabled: false,
    timeout: {
        spawn: null,
        remove: null,
    },
    selectors: {
        root: "#key-feedback",
    },
    translate: {
        "ArrowLeft": "◄",
        "ArrowRight": "►",
        "Delete": "Del",
        "Escape": "Esc",
    },
    spawn: function(e) {
        if(this.enabled == false || PF.fn.isDevice(["phone", "phablet"])) {
            return;
        }
        var $el = $(PF.fn.keyFeedback.selectors.root);
        if(!$el.exists()) {
            $('body').append($('<div></div>').attr({id: "key-feedback", class: "key-feedback"}));
            $el = $(PF.fn.keyFeedback.selectors.root)
        }
        var message = [];
        if((e.ctrlKey || e.metaKey) && e.originalEvent.code === 'KeyV') {
            e = {
                type: "keydown",
                key: PF.fn._s("Paste")
            };
        }
        if(e.type === "contextmenu" && e.ctrlKey) {
            e.type = "click";
        }
        if(e.type === "contextmenu") {
            message.push(PF.fn._s("Right click"));
        } else {
            if(e.ctrlKey) {
                message.push('Ctrl');
            }
            if(e.metaKey) {
                message.push('⌘');
            }
            if(e.hasOwnProperty("key")) {
                var key = e.key.length === 1
                    ? e.key.toUpperCase()
                    : e.key;
                if(key in this.translate) {
                    key = this.translate[key];
                }
                message.push(key);
            }
        }
        if(e.type === "click") {
            message.push("click");
        }
        $el.html(message.join(" + ", message)).css("opacity", 1);
        clearTimeout(PF.fn.keyFeedback.timeout.spawn);
        clearTimeout(PF.fn.keyFeedback.timeout.remove);
        PF.fn.keyFeedback.timeout.spawn = setTimeout(function() {
            $el.css("opacity", 0);
            PF.fn.keyFeedback.timeout.remove = setTimeout(function() {
                $el.remove();
            }, 500)
        }, 1500);
    },
};

PF.fn.popup = function (options) {
    var settings = {
        height: options.height || 500,
        width: options.width || 650,
        scrollTo: 0,
        resizable: 0,
        scrollbars: 0,
        location: 0
    };

    settings.top = screen.height / 2 - settings.height / 2;
    settings.left = screen.width / 2 - settings.width / 2;

    var settings_ = "";
    for (var key in settings) {
        settings_ += key + "=" + settings[key] + ",";
    }
    settings_ = settings_.slice(0, -1); // remove the last comma

    window.open(options.href, "Popup", settings_);
    return;
};

/**
 * PEAFOWL FLUID WIDTH FIXER
 * -------------------------------------------------------------------------------------------------
 */
PF.fn.list_fluid_width = function () {
    if (!$("body").is_fluid()) return;

    var $content_listing = $(PF.obj.listing.selectors.content_listing_visible),
        $pad_content_listing = $(
            PF.obj.listing.selectors.pad_content,
            $content_listing
        ),
        $list_item = $(PF.obj.listing.selectors.list_item, $content_listing),
        list_item_width = $list_item.outerWidth(true),
        list_item_gutter = $list_item.outerWidth(true) - $list_item.width();

    PF.obj.listing.content_listing_ratio = parseInt(
        ($content_listing.width() + list_item_gutter) / list_item_width
    );

    if ($list_item.length < PF.obj.listing.content_listing_ratio) {
        $pad_content_listing.css("width", "100%");
        return;
    }
};

/**
 * PEAFOWL TABS
 * -------------------------------------------------------------------------------------------------
 */

PF.obj.tabs = {
    hashdata: {}
};

PF.fn.show_tab = function (tab) {
    if (typeof tab == "undefined") {
        return;
    }
    var $link = $("a[data-tab=" + tab + "]", ".content-tabs");
    var $tab_menu = $("[data-action=tab-menu]", $link.closest(".header"));
    $tab_menu.find("[data-content=current-tab-label]").text($link.text());
    $tab_menu.find('[data-content="tab-icon"]').attr("class", "").addClass(
        $link.find(".btn-icon").attr("class")
    );
    if ($tab_menu.is(":visible")) {
        $tab_menu.trigger("click");
    }

    var $this = $("a[data-tab=" + tab + "]", ".content-tabs");

    $("li", $this.closest("ul")).removeClass("current");
    $this.closest("li").addClass("current");

    var $tab_content_group = $("#tabbed-content-group");
    $target = $("#" + $this.data("tab"));

    $(".tabbed-content", $tab_content_group)
        .removeClass("visible")
        .addClass("hidden");
    $($target, $tab_content_group)
        .addClass("visible")
        .removeClass("hidden");

    $("[data-content=list-selection]")
        .addClass("hidden");
    $("[data-content=list-selection][data-tab=" + $this.data("tab") + "]")
        .removeClass("hidden");

    if ($tab_content_group.exists()) {
        var $list_item_target = $(
            PF.obj.listing.selectors.list_item + ":not(.jsly)",
            $target
        );

        if (
            $target.data("load") == "ajax" &&
            $target.data("empty") !== "true" &&
            !$(PF.obj.listing.selectors.list_item, $target).exists()
        ) {
            PF.fn.listing.queryString.stock_load();
            $target.html(PF.obj.listing.template.fill);
            PF.fn.listing.queryString.stock_new();
            PF.fn.listing.ajax();
            $target.addClass("jsly");
        } else {
            PF.fn.listing.queryString.stock_current();
            PF.fn.listing.columnizer(false, 0, false);
            $list_item_target.show();
        }
    }

    PF.fn.listing.columnizerQueue();

    if (
        $(PF.obj.listing.selectors.content_listing_visible).data("queued") == true
    ) {
        PF.fn.listing.columnizer(true, 0);
    }
};

/**
 * PEAFOWL LISTINGS
 * -------------------------------------------------------------------------------------------------
 */
PF.obj.listing = {
    columns: "",
    columns_number: 1,
    current_column: "",
    current_column: "",
    XHR: {},
    query_string: PF.fn.get_url_vars(),
    calling: false,
    content_listing_ratio: 1,
    selectors: {
        sort: ".sort-listing .current [data-sort]",
        content_listing: ".content-listing",
        content_listing_visible: ".content-listing:visible",
        content_listing_loading: ".content-listing-loading",
        content_listing_load_more: ".content-listing-more",
        content_listing_pagination: ".content-listing-pagination",
        empty_icon: ".icon.fas.fa-inbox",
        pad_content: ".pad-content-listing",
        list_item: ".list-item"
    },
    template: {
        fill: $("[data-template=content-listing]").html(),
        empty: $("[data-template=content-listing-empty]").html(),
        loading: $("[data-template=content-listing-loading]").html()
    }
};

PF.fn.listing = {};

PF.fn.listing.show = function (response, callback) {
    $content_listing = $("#content-listing-tabs").exists()
        ? $(
            PF.obj.listing.selectors.content_listing_visible,
            "#content-listing-tabs"
        )
        : $(PF.obj.listing.selectors.content_listing);
    var list_content = $content_listing.data("list");
    var item_detect = list_content === "tags"
        ? ".tag-container"
        : PF.obj.listing.selectors.list_item;
    var $targets = $(
        item_detect,
        $content_listing
    );
    PF.fn.loading.inline(PF.obj.listing.selectors.content_listing_loading);
    if (
        (
            typeof response !== "undefined"
            && $(response.html).length < PF.obj.config.listing.items_per_page
        )
        || $targets.length < PF.obj.config.listing.items_per_page
    ) {
        PF.fn.listing.removeLoader($content_listing);
    }
    if (
        $(
            PF.obj.listing.selectors.content_listing_pagination,
            $content_listing
        ).is("[data-type=classic]") ||
        !$("[data-action=load-more]", $content_listing).exists()
    ) {
        $(
            PF.obj.listing.selectors.content_listing_loading,
            $content_listing
        ).remove();
    }

    if(list_content === "tags") {
        $content_listing.addClass("jsly");
    } else {
        PF.fn.listing.columnizer(false, 0);
        $targets.show();
        PF.fn.listing.columnizer(true, 0);
        $targets.addClass("--show");
    }

    PF.obj.listing.calling = false;

    var visible_loading =
        $(
            PF.obj.listing.selectors.content_listing_loading,
            $content_listing
        ).exists() &&
        $(
            PF.obj.listing.selectors.content_listing_loading,
            $content_listing
        ).is_in_viewport();

    PF.obj.listing.show_load_more = visible_loading;

    $(PF.obj.listing.selectors.content_listing_loading, $content_listing)[
        (visible_loading ? "add" : "remove") + "Class"
    ]("hidden");
    $(PF.obj.listing.selectors.content_listing_load_more, $content_listing)[
        PF.obj.listing.show_load_more ? "show" : "hide"
    ]();

    if (PF.obj.listing.lockClickMore) {
        PF.obj.listing.lockClickMore = false;
    }

    if (typeof callback == "function") {
        callback();
    }
};

PF.fn.listing.removeLoader = function (obj) {
    var remove = [
        PF.obj.listing.selectors.content_listing_load_more,
        PF.obj.listing.selectors.content_listing_loading
    ];

    if (
        $(PF.obj.listing.selectors.content_listing_pagination, $content_listing).is(
            "[data-type=endless]"
        )
    ) {
        remove.push(PF.obj.listing.selectors.content_listing_pagination);
    }

    $.each(remove, function (i, v) {
        $(v, obj).remove();
    });
};

PF.fn.listing.queryString = {
    // Stock the querystring values from initial load
    stock_load: function () {
        var $content_listing = $(PF.obj.listing.selectors.content_listing_visible),
            params = PF.fn.parseQueryString($content_listing.data("params"));

        PF.obj.listing.params_hidden =
            typeof $content_listing.data("params-hidden") !== "undefined"
                ? PF.fn.parseQueryString($content_listing.data("params-hidden"))
                : null;

        if (typeof PF.obj.listing.query_string.action == "undefined") {
            PF.obj.listing.query_string.action =
                $content_listing.data("action") || "list";
        }
        if (typeof PF.obj.listing.query_string.list == "undefined") {
            PF.obj.listing.query_string.list = $content_listing.data("list");
        }
        if (typeof PF.obj.listing.query_string.sort == "undefined") {
            if (typeof params !== "undefined" && typeof params.sort !== "undefined") {
                PF.obj.listing.query_string.sort = params.sort;
            } else {
                PF.obj.listing.query_string.sort = $(
                    ":visible" + PF.obj.listing.selectors.sort
                ).data("sort");
            }
        }
        if (typeof PF.obj.listing.query_string.page == "undefined") {
            PF.obj.listing.query_string.page = 1;
        }
        $content_listing.data("page", PF.obj.listing.query_string.page);

        // Stock the real ajaxed hrefs for ajax loads
        $(PF.obj.listing.selectors.content_listing + "[data-load=ajax]").each(
            function () {
                var $sortable_switch = $(
                    "[data-tab=" +
                    $(this).attr("id") +
                    "]" +
                    PF.obj.listing.selectors.sort
                );
                var dataParams = PF.fn.parseQueryString($(this).data("params")),
                    dataParamsHidden = PF.fn.parseQueryString($(this).data("params-hidden")),
                    params = {
                        q: dataParams && dataParams.q ? dataParams.q : null,
                        list: $(this).data("list"),
                        sort: $sortable_switch.exists()
                            ? $sortable_switch.data("sort")
                            : dataParams && dataParams.sort
                                ? dataParams.sort
                                : null,
                        page: dataParams && dataParams.page ? dataParams.page : 1
                    };

                if (dataParamsHidden && dataParamsHidden.list) {
                    delete params.list;
                }

                for (var k in params) {
                    if (!params[k]) delete params[k];
                }
            }
        );

        // The additional params setted in data-params=""
        for (var k in params) {
            if (/action|list|sort|page/.test(k) == false) {
                PF.obj.listing.query_string[k] = params[k];
            }
        }

        if (typeof PF.obj.listing.params_hidden !== typeof undefined) {
            // The additional params setted in data-hidden-params=""
            for (var k in PF.obj.listing.params_hidden) {
                if (/action|list|sort|page/.test(k) == false) {
                    PF.obj.listing.query_string[k] = PF.obj.listing.params_hidden[k];
                }
            }
            PF.obj.listing.query_string["params_hidden"] = PF.obj.listing.params_hidden;
            // Add this key for legacy, params_hidden v3.9.0 intro*
            // PF.obj.listing.params_hidden["params_hidden"] = null;
        }
    },
    stock_new: function () {
        var $content_listing = $(PF.obj.listing.selectors.content_listing_visible),
            params = PF.fn.parseQueryString($content_listing.data("params"));

        if ($content_listing.data("offset")) {
            PF.obj.listing.query_string.offset = $content_listing.data("offset");
        } else {
            delete PF.obj.listing.query_string.offset;
        }
        PF.obj.listing.query_string.seek = '';
        PF.obj.listing.query_string.action =
            $content_listing.data("action") || "list";
        PF.obj.listing.query_string.list = $content_listing.data("list");

        if (typeof params !== "undefined" && typeof params.sort !== "undefined") {
            PF.obj.listing.query_string.sort = params.sort;
        } else {
            PF.obj.listing.query_string.sort = $(
                ":visible" + PF.obj.listing.selectors.sort
            ).data("sort");
        }

        PF.obj.listing.query_string.page = 1;
    },

    // Stock querystring values for static tab change
    stock_current: function () {
        this.stock_new();
        PF.obj.listing.query_string.page = $(
            PF.obj.listing.selectors.content_listing_visible
        ).data("page");
    }
};

// Initial load -> Stock the current querystring
PF.fn.listing.queryString.stock_load();

PF.fn.listing.ajax = function () {
    if (PF.obj.listing.calling == true) {
        return;
    }

    PF.obj.listing.calling = true;

    var $content_listing = $(PF.obj.listing.selectors.content_listing_visible);
    var $pad_content_listing = $(
        PF.obj.listing.selectors.pad_content,
        $content_listing
    );
    var $content_listing_load_more = $(
        PF.obj.listing.selectors.content_listing_load_more,
        $content_listing
    );

    $content_listing_load_more.hide();
    $(PF.obj.listing.selectors.content_listing_loading, $content_listing)
        .removeClass("visibility-hidden")
        .show();

    PF.obj.listing.XHR = $.ajax({
        type: "POST",
        data: $.param(
            $.extend({}, PF.obj.listing.query_string, $.ajaxSettings.data)
        )
    }).complete(function (XHR) {
        var response = XHR.responseJSON;
        var removePagination = function () {
            $(
                PF.obj.listing.selectors.content_listing_loading +
                "," +
                PF.obj.listing.selectors.content_listing_pagination +
                ":not([data-visibility=visible])",
                $content_listing
            ).remove();
        },
            setEmptyTemplate = function () {
                $content_listing
                    .data("empty", "true")
                    .html(PF.obj.listing.template.empty);
                $(
                    "[data-content=list-selection][data-tab=" +
                    $content_listing.attr("id") +
                    "]"
                ).addClass("disabled");
            };

        if (XHR.readyState == 4 && typeof response !== "undefined") {
            $(
                "[data-content=list-selection][data-tab=" +
                $content_listing.attr("id") +
                "]"
            ).removeClass("disabled");

            if (XHR.status !== 200) {
                var response_output =
                    typeof response.error !== "undefined" &&
                        typeof response.error.message !== "undefined"
                        ? response.error.message
                        : "Bad request";
                PF.fn.growl.call("Error: " + response_output);
                $content_listing.data("load", "");
            }
            if (
                (typeof response.html == "undefined" || response.html == "") &&
                $(PF.obj.listing.selectors.list_item, $content_listing).length == 0
            ) {
                setEmptyTemplate();
            }
            if (typeof response.html == "undefined" || response.html == "") {
                removePagination();
                PF.obj.listing.calling = false;
                if (typeof PF.fn.listing_end == "function") {
                    PF.fn.listing_end();
                }
                return;
            }
            $content_listing.data({
                load: "",
                page: PF.obj.listing.query_string.page
            });

            var url_object = $.extend({}, PF.obj.listing.query_string);
            for (var k in PF.obj.listing.params_hidden) {
                if (typeof url_object[k] !== "undefined") {
                    delete url_object[k];
                }
            }

            delete url_object["action"];

            for (var k in url_object) {
                if (!url_object[k]) delete url_object[k];
            }

            $("a[data-tab=" + $content_listing.attr("id") + "]").attr(
                "href",
                document.URL
            );
            var $append_target = $pad_content_listing;
            var $content_tags = $append_target.find(".content-tags").first();
            if($content_tags.exists()) {
                $append_target = $content_tags;
            }
            $append_target.append(response.html);

            var $loadMore =  $(PF.obj.listing.selectors.content_listing_visible).find(
                "[data-action=load-more]"
            );
            if(response.seekEnd !== '') {
                $loadMore.attr("data-seek", response.seekEnd);
            } else {
                $loadMore.remove();
            }

            PF.fn.listing.show(response, function () {
                $(
                    PF.obj.listing.selectors.content_listing_loading,
                    $content_listing
                ).addClass("visibility-hidden");
            });
        } else {
            // Network error, abort or something similar
            PF.obj.listing.calling = false;
            $content_listing.data("load", "");
            removePagination();
            if ($(PF.obj.listing.selectors.list_item, $content_listing).length == 0) {
                setEmptyTemplate();
            }
            if (XHR.readyState !== 0) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
            }
        }

        if (typeof PF.fn.listing.ajax.callback == "function") {
            PF.fn.listing.ajax.callback(XHR);
        }
    });
};

PF.fn.listing.columnizerQueue = function () {
    $(PF.obj.listing.selectors.content_listing + ":hidden").data("queued", true);
};

PF.fn.listing.refresh = function (animation_time) {
    PF.fn.listing.columnizer(true, animation_time, false);
};

var width = $(window).width();
PF.fn.listing.columnizer = function (forced, animation_time, hard_forced) {
    var device_to_columns = {
        // default
        phone: 1,
        phablet: 3,
        tablet: 4,
        laptop: 5,
        desktop: 6,
        largescreen: 7
    };

    if (typeof forced !== "boolean") var forced = false;
    if (typeof PF.obj.listing.mode == "undefined") forced = true;
    if (typeof hard_forced !== "boolean") {
        var hard_forced = false,
            default_hard_forced = true;
    } else {
        var default_hard_forced = false;
    }
    if (!hard_forced && default_hard_forced) {
        if (width !== $(window).width() || forced) {
            hard_forced = true;
        }
    }

    if (typeof animation_time == typeof undefined)
        var animation_time = PF.obj.config.animation.normal;

    var $container = $("#content-listing-tabs").exists()
        ? $(
            PF.obj.listing.selectors.content_listing_visible,
            "#content-listing-tabs"
        )
        : $(PF.obj.listing.selectors.content_listing),
        $pad_content_listing = $(PF.obj.listing.selectors.pad_content, $container),
        list_mode = "responsive",
        $list_item = $(
            forced || hard_forced
                ? PF.obj.listing.selectors.list_item
                : PF.obj.listing.selectors.list_item + ":not(.jsly)",
            $container
        );

    if (typeof PF.obj.config.listing.device_to_columns !== "undefined") {
        device_to_columns = $.extend(
            {},
            device_to_columns,
            PF.obj.config.listing.device_to_columns
        );
    }

    if ($container.data("device-columns")) {
        device_to_columns = $.extend(
            {},
            device_to_columns,
            $container.data("device-columns")
        );
    }

    PF.obj.listing.mode = list_mode;
    PF.obj.listing.device = PF.fn.getDeviceName();

    if (!$list_item.exists()) return;

    if (
        typeof $container.data("columns") !== "undefined" &&
        !forced &&
        !hard_forced
    ) {
        PF.obj.listing.columns = $container.data("columns");
        PF.obj.listing.columns_number = $container.data("columns").length - 1;
        PF.obj.listing.current_column = $container.data("current_column");
    } else {
        var $list_item_1st = $list_item.first();
        $list_item_1st.css("width", "");
        PF.obj.listing.columns = new Array();
        PF.obj.listing.columns_number = device_to_columns[PF.fn.getDeviceName()];
        for (i = 0; i < PF.obj.listing.columns_number; i++) {
            PF.obj.listing.columns[i + 1] = 0;
        }
        PF.obj.listing.current_column = 1;
    }

    $container
        .removeClass("small-cols")
        .addClass(PF.obj.listing.columns_number > 6 ? "small-cols" : "");

    $pad_content_listing.css("width", "100%");

    var delay = 0;

    $list_item.each(function (index) {
        $(this).addClass("jsly");

        var $list_item_img = $(".list-item-image", this),
            $list_item_src = $(".list-item-image img", this),
            $list_item_thumbs = $(".list-item-thumbs", this),
            isJslyLoaded = $list_item_src.hasClass("jsly-loaded");

        $list_item_src.show();

        if (hard_forced) {
            $(this).css({ top: "", left: "", height: "", position: "" });
            $list_item_img.css({ maxHeight: "", height: "" });
            $list_item_src
                .removeClass("jsly")
                .css({ width: "", height: "" })
                .parent()
                .css({
                    marginLeft: "",
                    marginTop: ""
                });
            $("li", $list_item_thumbs).css({ width: "", height: "" });
        }

        var width_responsive =
            PF.obj.listing.columns_number == 1
                ? "100%"
                : parseFloat(
                    (1 / PF.obj.listing.columns_number) *
                    $container.width() +
                    "px"
                );
        $(this).css("width", width_responsive);

        if (PF.obj.listing.current_column > PF.obj.listing.columns_number) {
            PF.obj.listing.current_column = 1;
        }

        $(this).attr("data-col", PF.obj.listing.current_column);

        if (!$list_item_src.exists()) {
            var empty = true;
            $list_item_src = $(".image-container .empty", this);
        }

        var already_shown = $(this).is(":visible");
        $list_item.show();

        var isFixed = $list_item_img.hasClass("fixed-size");

        var image = {
            w: parseFloat($list_item_src.attr("width")),
            h: parseFloat($list_item_src.attr("height"))
        };
        image.ratio = image.w / image.h;

        if (
            empty ||
            ($list_item_img.css("min-height") && !$list_item_src.hasClass("jsly"))
        ) {
            var col = {
                    w: $(this).width(),
                    h: isFixed ? $(this).width() : null
                },
                magicWidth = Math.min(image.w, image.w < col.w ? image.w : col.w);

            if (isFixed) {
                $list_item_img.css({ height: col.w }); // Sets the item container height
                if (image.ratio <= 3 && (image.ratio > 1 || image.ratio == 1)) {
                    // Landscape or square
                    image.h = Math.min(image.h, image.w < col.w ? image.w : col.w);
                    image.w = image.h * image.ratio;
                } else {
                    // Portrait
                    image.w = magicWidth;
                    image.h = image.w / image.ratio;
                }
                $list_item_img.css("min-height", 0);
            } else {
                // Fluid height
                image.w = magicWidth;
                if (image.ratio >= 3 || image.ratio < 1 || image.ratio == 1) {
                    // Portrait or square
                    image.h = image.w / image.ratio;
                } else {
                    // Landscape
                    image.h = Math.min(image.h, image.w);
                    image.w = image.h * image.ratio;
                }
                if (empty) {
                    image.h = col.w;
                }
                $list_item_img.css({ height: image.h }); // Fill some gaps
            }

            if ($list_item_src.width() == 0) {
                $list_item_src.css({
                    width: magicWidth,
                    height: magicWidth / image.ratio
                });
            }

            if ($(".image-container", this).is(".list-item-avatar-cover")) {
                $list_item_src.css(
                    isFixed
                        ? { width: "auto", height: "100%" }
                        : { width: "100%", height: "auto" }
                );
            }

            var list_item_src_pitfall_x = Math.max(
                $list_item_src.position().left * 2,
                0
            ),
                list_item_src_pitfall_y = Math.max(
                    $list_item_src.position().top * 2,
                    0
                );

            var pitfall_ratio_x = list_item_src_pitfall_x / $list_item_img.width(),
                pitfall_ratio_y = list_item_src_pitfall_y / $list_item_img.height();
            if (
                (list_item_src_pitfall_x > 0 || list_item_src_pitfall_y > 0)
                && (pitfall_ratio_x <= 0.25 || pitfall_ratio_y <= 0.25)
            ) {
                $list_item_img.addClass("--fit");
            }
            if ($list_item_thumbs.exists()) {
                $("li", $list_item_thumbs)
                    .css({ width: 100 / $("li", $list_item_thumbs).length + "%" })
                    .css({ height: $("li", $list_item_thumbs).width() });
            }

            if (!already_shown) {
                $list_item.hide();
            }
        }

        if (!$list_item_src.hasClass("jsly") && $(this).is(":hidden")) {
            $(this).css("top", "100%");
        }

        PF.obj.listing.columns[PF.obj.listing.current_column] += $(
            this
        ).outerHeight(true);

        if ($(this).is(":animated")) {
            animation_time = 0;
        }
        $(this).addClass("position-absolute");

        var new_left =
            $(this).outerWidth(true) * (PF.obj.listing.current_column - 1);
        var must_change_left = parseFloat($(this).css("left")) != new_left;
        if (must_change_left) {
            animate_grid = true;
            $(this).animate(
                {
                    left: new_left
                },
                animation_time
            );
        }

        var new_top =
            PF.obj.listing.columns[PF.obj.listing.current_column] -
            $(this).outerHeight(true);
        if (parseFloat($(this).css("top")) != new_top) {
            animate_grid = true;
            $(this).animate(
                {
                    top: new_top
                },
                animation_time
            );
            if (must_change_left) {
                delay = 1;
            }
        }

        if (already_shown) {
            $list_item.show();
        }

        PF.obj.listing.current_column++;
    });

    $container.data({
        columns: PF.obj.listing.columns,
        current_column: PF.obj.listing.current_column
    }).attr('data-columns', PF.obj.listing.columns_number);

    var content_listing_height = 0;
    $.each(PF.obj.listing.columns, function (i, v) {
        if (v > content_listing_height) {
            content_listing_height = v;
        }
    });

    PF.obj.listing.width = $container.width();

    if (typeof PF.obj.listing.height !== typeof undefined) {
        var old_listing_height = PF.obj.listing.height;
    }
    PF.obj.listing.height = content_listing_height;

    var do_listing_h_resize =
        typeof old_listing_height !== typeof undefined &&
        old_listing_height !== PF.obj.listing.height;

    if (!do_listing_h_resize) {
        $pad_content_listing.height(content_listing_height);
        PF.fn.list_fluid_width();
    }

    if (do_listing_h_resize) {
        $pad_content_listing.height(old_listing_height);
        setTimeout(function () {
            $pad_content_listing.animate(
                { height: content_listing_height },
                animation_time,
                function () {
                    PF.fn.list_fluid_width();
                }
            );
        }, animation_time * delay);
    }

    $container.data("list-mode", PF.obj.listing.mode);
    $(PF.obj.listing.selectors.content_listing_visible).data("queued", false);

    $container.addClass("jsly");
};

/**
 * PEAFOWL LOADERS
 * -------------------------------------------------------------------------------------------------
 */
PF.fn.loading = {
    spin: {
        small: {
            lines: 11,
            length: 0,
            width: 3,
            radius: 7,
            speed: 1,
            trail: 45,
            blocksize: 20
        }, // 20x20
        normal: {
            lines: 11,
            length: 0,
            width: 5,
            radius: 10,
            speed: 1,
            trail: 45,
            blocksize: 30
        }, // 30x30
        big: {
            lines: 11,
            length: 0,
            width: 7,
            radius: 13,
            speed: 1,
            trail: 45,
            blocksize: 40
        }, // 40x40
        huge: {
            lines: 11,
            length: 0,
            width: 9,
            radius: 16,
            speed: 1,
            trail: 45,
            blocksize: 50
        } // 50x50
    },
    inline: function ($target, options) {
        if (typeof $target == "undefined") return;

        if ($target instanceof jQuery == false) {
            var $target = $($target);
        }

        var defaultoptions = {
            size: "normal",
            color: $("body").css("color"),
            center: false,
            position: "absolute",
            shadow: false,
            valign: "top"
        };

        if (typeof options == "undefined") {
            options = defaultoptions;
        } else {
            for (var k in defaultoptions) {
                if (typeof options[k] == "undefined") {
                    options[k] = defaultoptions[k];
                }
            }
        }

        var size = PF.fn.loading.spin[options.size];

        PF.fn.loading.spin[options.size].color = options.color;
        PF.fn.loading.spin[options.size].shadow = options.shadow;

        $target
            .html(
                '<span class="loading-indicator"></span>' +
                (typeof options.message !== "undefined"
                    ? '<span class="loading-text">' + options.message + "</span>"
                    : "")
            )
            .css({
                "line-height": PF.fn.loading.spin[options.size].blocksize + "px"
            });

        $(".loading-indicator", $target)
            .css({
                width: PF.fn.loading.spin[options.size].blocksize,
                height: PF.fn.loading.spin[options.size].blocksize
            })
            .spin(PF.fn.loading.spin[options.size]);

        if (options.center) {
            $(".loading-indicator", $target.css("textAlign", "center")).css({
                position: options.position,
                top: "50%",
                insetInlineStart: "50%",
                marginTop: -(PF.fn.loading.spin[options.size].blocksize / 2),
                marginInlineStart: -(PF.fn.loading.spin[options.size].blocksize / 2)
            });
        }
        if (options.valign == "center") {
            $(".loading-indicator,.loading-text", $target).css(
                "marginTop",
                ($target.height() - PF.fn.loading.spin[options.size].blocksize) / 2 +
                "px"
            );
        }

        $(".spinner", $target).css({
            top: PF.fn.loading.spin[options.size].blocksize / 2 + "px",
            insetInlineStart: PF.fn.loading.spin[options.size].blocksize / 2 + "px"
        });
    },
    fullscreen: function () {
        $("body").append(
            '<div class="fullscreen" id="pf-fullscreen-loader"><div class="fullscreen-loader black-bkg"><span class="loading-txt">' +
            PF.fn._s("loading") +
            "</span></div></div>"
        );
        $(".fullscreen-loader", "#pf-fullscreen-loader").spin(
            PF.fn.loading.spin.huge
        );
        $("#pf-fullscreen-loader").css("opacity", 1);
    },
    destroy: function ($target) {
        var $loader_fs = $("#pf-fullscreen-loader"),
            $loader_os = $("#pf-onscreen-loader");

        if ($target == "fullscreen") $target = $loader_fs;
        if ($target == "onscreen") $target = $loader_os;

        if (typeof $target !== "undefined") {
            $target.remove();
        } else {
            $loader_fs.remove();
            $loader_os.remove();
        }
    }
};

/**
 * PEAFOWL FORM HELPERS
 * -------------------------------------------------------------------------------------------------
 */
jQuery.fn.disableForm = function () {
    $(this).data("disabled", true);
    $(":input", this).each(function () {
        $(this).attr("disabled", true);
    });
    return this;
};
jQuery.fn.enableForm = function () {
    $(this).data("disabled", false);
    $(":input", this).removeAttr("disabled");
    return this;
};

PF.fn.isDevice = function (device) {
    if (typeof device == "object") {
        var device = "." + device.join(",.");
    } else {
        var device = "." + device;
    }
    return $("html").is(device);
};

PF.fn.getDeviceName = function () {
    var current_device;
    $.each(PF.obj.devices, function (i, v) {
        if (PF.fn.isDevice(v)) {
            current_device = v;
            return true;
        }
    });
    return current_device;
};

PF.fn.topMenu = {
    vars: {
        $button: $("[data-action=top-bar-menu-full]", "#top-bar"),
        menu: "#menu-fullscreen",
        speed: PF.obj.config.animation.fast,
        menu_top:
            parseInt($("#top-bar").outerHeight()) +
            parseInt($("#top-bar").css("top")) +
            parseInt($("#top-bar").css("margin-top")) +
            parseInt($("#top-bar").css("margin-bottom")) -
            parseInt($("#top-bar").css("border-bottom-width")) +
            "px"
    },
    show: function (speed) {
        if ($("body").is(":animated")) return;

        if (typeof speed == "undefined") {
            var speed = this.vars.speed;
        }

        this.vars.$button.addClass("current");
        $("html").addClass("menu-fullscreen-visible");
        $("#top-bar")
            .append(
                $("<div/>", {
                    id: "menu-fullscreen",
                    class: "touch-scroll",
                    html: $("<div/>", {
                            class: "fullscreen black",
                        })
                })
                .css({
                    left: "-100%"
                })
                .append(
                    $("<ul/>", {
                        html: $(".top-bar-left").html() + $(".top-bar-right").html()
                    })
                )
            );

        var $menu = $(this.vars.menu);

        $(
            "li.phone-hide, li > .top-btn-text, li > .top-btn-text > span, li > a > .top-btn-text > span",
            $menu
        ).each(function () {
            $(this).removeClass("phone-hide");
        });
        $("[data-action=top-bar-menu-full]", $menu).remove();
        $(
            ".btn.black, .btn.default, .btn.blue, .btn.green, .btn.orange, .btn.red, .btn.transparent",
            $menu
        ).removeClass("btn black default blue green orange red transparent");

        setTimeout(function () {
            $menu.css({ transform: "translateX(100%)" });
            $(".fullscreen").css("opacity", 1);
        }, 1);
        setTimeout(function () {
            $menu.css({ transition: "none", transform: "", left: "" });
            $("html").css({ backgroundColor: "" });
        }, speed);
    },
    hide: function (speed) {
        if ($("body").is(":animated")) return;

        if (!$(this.vars.menu).is(":visible")) return;
        var $menu = $(this.vars.menu);
        if (typeof speed == "undefined") {
            var speed = this.vars.speed;
        }
        $menu.css({transition: ""});
        setTimeout(function () {
            $menu.css({
                transform: "translateX(-100%)"
            });
        }, 1);
        $("#top-bar").css("position", "");
        this.vars.$button.removeClass("current");
        $("html").removeClass("menu-fullscreen-visible");
        setTimeout(function () {
            $menu.remove();
        }, speed);
    }
};

PF.fn.form = {
    validateInput: function ($input) {
        if($input[0].checkValidity()) {
            return true;
        }
        $input.highlight();
        $("label", $input.closest(".input-label")).shake();
        return false;
    },
    validateForm: function ($form) {
        let validate = true;
        let _this = this;
        $(":input[name]:visible", $form).each(function () {
            validate = _this.validateInput($(this)) && validate;
        });
        if(!validate) {
            $form[0].reportValidity();
        }

        return validate;
    }
};

/**
 * JQUERY PLUGINS (strictly needed plugins)
 * -------------------------------------------------------------------------------------------------
 */

// http://phpjs.org/functions/sprintf/
function sprintf() {
    var e = /%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuideEfFgG])/g;
    var t = arguments;
    var n = 0;
    var r = t[n++];
    var i = function (e, t, n, r) {
        if (!n) {
            n = " ";
        }
        var i = e.length >= t ? "" : new Array((1 + t - e.length) >>> 0).join(n);
        return r ? e + i : i + e;
    };
    var s = function (e, t, n, r, s, o) {
        var u = r - e.length;
        if (u > 0) {
            if (n || !s) {
                e = i(e, r, o, n);
            } else {
                e = e.slice(0, t.length) + i("", u, "0", true) + e.slice(t.length);
            }
        }
        return e;
    };
    var o = function (e, t, n, r, o, u, a) {
        var f = e >>> 0;
        n = (n && f && { 2: "0b", 8: "0", 16: "0x" }[t]) || "";
        e = n + i(f.toString(t), u || 0, "0", false);
        return s(e, n, r, o, a);
    };
    var u = function (e, t, n, r, i, o) {
        if (r != null) {
            e = e.slice(0, r);
        }
        return s(e, "", t, n, i, o);
    };
    var a = function (e, r, a, f, l, c, h) {
        var p, d, v, m, g;
        if (e === "%%") {
            return "%";
        }
        var y = false;
        var b = "";
        var w = false;
        var E = false;
        var S = " ";
        var x = a.length;
        for (var T = 0; a && T < x; T++) {
            switch (a.charAt(T)) {
                case " ":
                    b = " ";
                    break;
                case "+":
                    b = "+";
                    break;
                case "-":
                    y = true;
                    break;
                case "'":
                    S = a.charAt(T + 1);
                    break;
                case "0":
                    w = true;
                    S = "0";
                    break;
                case "#":
                    E = true;
                    break;
            }
        }
        if (!f) {
            f = 0;
        } else if (f === "*") {
            f = +t[n++];
        } else if (f.charAt(0) == "*") {
            f = +t[f.slice(1, -1)];
        } else {
            f = +f;
        }
        if (f < 0) {
            f = -f;
            y = true;
        }
        if (!isFinite(f)) {
            throw new Error("sprintf: (minimum-)width must be finite");
        }
        if (!c) {
            c = "fFeE".indexOf(h) > -1 ? 6 : h === "d" ? 0 : undefined;
        } else if (c === "*") {
            c = +t[n++];
        } else if (c.charAt(0) == "*") {
            c = +t[c.slice(1, -1)];
        } else {
            c = +c;
        }
        g = r ? t[r.slice(0, -1)] : t[n++];
        switch (h) {
            case "s":
                return u(String(g), y, f, c, w, S);
            case "c":
                return u(String.fromCharCode(+g), y, f, c, w);
            case "b":
                return o(g, 2, E, y, f, c, w);
            case "o":
                return o(g, 8, E, y, f, c, w);
            case "x":
                return o(g, 16, E, y, f, c, w);
            case "X":
                return o(g, 16, E, y, f, c, w).toUpperCase();
            case "u":
                return o(g, 10, E, y, f, c, w);
            case "i":
            case "d":
                p = +g || 0;
                p = Math.round(p - (p % 1));
                d = p < 0 ? "-" : b;
                g = d + i(String(Math.abs(p)), c, "0", false);
                return s(g, d, y, f, w);
            case "e":
            case "E":
            case "f":
            case "F":
            case "g":
            case "G":
                p = +g;
                d = p < 0 ? "-" : b;
                v = ["toExponential", "toFixed", "toPrecision"][
                    "efg".indexOf(h.toLowerCase())
                ];
                m = ["toString", "toUpperCase"]["eEfFgG".indexOf(h) % 2];
                g = d + Math.abs(p)[v](c);
                return s(g, d, y, f, w)[m]();
            default:
                return e;
        }
    };
    return r.replace(e, a);
}

/**
 * TipTip
 * Copyright 2010 Drew Wilson
 * code.drewwilson.com/entry/tiptip-jquery-plugin
 *
 * Version 1.3(modified) - Updated: Jun. 23, 2011
 * http://drew.tenderapp.com/discussions/tiptip/70-updated-tiptip-with-new-features
 *
 * This TipTip jQuery plug-in is dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
(function ($) {
    $.fn.tipTip = function (options) {
        var defaults = {
            activation: "hover",
            keepAlive: false,
            maxWidth: "200px",
            edgeOffset: 6,
            defaultPosition: "bottom",
            delay: 400,
            fadeIn: 200,
            fadeOut: 200,
            attribute: "title",
            content: false,
            enter: function () { },
            afterEnter: function () { },
            exit: function () { },
            afterExit: function () { },
            cssClass: ""
        };
        if ($("#tiptip_holder").length <= 0) {
            var tiptip_holder = $('<div id="tiptip_holder"></div>');
            var tiptip_content = $('<div id="tiptip_content"></div>');
            var tiptip_arrow = $('<div id="tiptip_arrow"></div>');
            $("body").append(
                tiptip_holder
                    .html(tiptip_content)
                    .prepend(tiptip_arrow.html('<div id="tiptip_arrow_inner"></div>'))
            );
        } else {
            var tiptip_holder = $("#tiptip_holder");
            var tiptip_content = $("#tiptip_content");
            var tiptip_arrow = $("#tiptip_arrow");
        }
        return this.each(function () {
            var org_elem = $(this),
                data = org_elem.data("tipTip"),
                opts = (data && data.options) || $.extend(defaults, options),
                callback_data = {
                    holder: tiptip_holder,
                    content: tiptip_content,
                    arrow: tiptip_arrow,
                    options: opts
                };
            if (data) {
                switch (options) {
                    case "show":
                        active_tiptip();
                        break;
                    case "hide":
                        deactive_tiptip();
                        break;
                    case "destroy":
                        org_elem.unbind(".tipTip").removeData("tipTip");
                        break;
                }
            } else {
                var timeout = false;
                org_elem.data("tipTip", { options: opts });
                if (opts.activation == "hover") {
                    org_elem
                        .bind("mouseenter.tipTip", function () {
                            active_tiptip();
                        })
                        .bind("mouseleave.tipTip", function () {
                            if (!opts.keepAlive) {
                                deactive_tiptip();
                            } else {
                                tiptip_holder.one("mouseleave.tipTip", function () {
                                    deactive_tiptip();
                                });
                            }
                        });
                } else {
                    if (opts.activation == "focus") {
                        org_elem
                            .bind("focus.tipTip", function () {
                                active_tiptip();
                            })
                            .bind("blur.tipTip", function () {
                                deactive_tiptip();
                            });
                    } else {
                        if (opts.activation == "click") {
                            org_elem
                                .bind("click.tipTip", function (e) {
                                    e.preventDefault();
                                    active_tiptip();
                                    return false;
                                })
                                .bind("mouseleave.tipTip", function () {
                                    if (!opts.keepAlive) {
                                        deactive_tiptip();
                                    } else {
                                        tiptip_holder.one("mouseleave.tipTip", function () {
                                            deactive_tiptip();
                                        });
                                    }
                                });
                        } else {
                            if (opts.activation == "manual") {
                            }
                        }
                    }
                }
            }
            function active_tiptip() {
                if (opts.enter.call(org_elem, callback_data) === false) {
                    return;
                }
                var org_title;
                if (opts.content) {
                    org_title = $.isFunction(opts.content)
                        ? opts.content.call(org_elem, callback_data)
                        : opts.content;
                } else {
                    org_title = opts.content = org_elem.attr(opts.attribute);
                    org_elem.removeAttr(opts.attribute);
                }
                if (!org_title) {
                    return;
                }
                tiptip_content.html(org_title);
                tiptip_holder
                    .hide()
                    .removeAttr("class")
                    .css({ margin: "0px", "max-width": opts.maxWidth });
                if (opts.cssClass) {
                    tiptip_holder.addClass(opts.cssClass);
                }
                tiptip_arrow.removeAttr("style");
                var top = parseInt(org_elem.offset()["top"]),
                    left = parseInt(org_elem.offset()["left"]),
                    org_width = parseInt(org_elem.outerWidth()),
                    org_height = parseInt(org_elem.outerHeight()),
                    tip_w = tiptip_holder.outerWidth(),
                    tip_h = tiptip_holder.outerHeight(),
                    w_compare = Math.round((org_width - tip_w) / 2),
                    h_compare = Math.round((org_height - tip_h) / 2),
                    marg_left = Math.round(left + w_compare),
                    marg_top = Math.round(top + org_height + opts.edgeOffset),
                    t_class = "",
                    arrow_top = "",
                    arrow_left = Math.round(tip_w - 12) / 2;
                if (opts.defaultPosition == "bottom") {
                    t_class = "_bottom";
                } else {
                    if (opts.defaultPosition == "top") {
                        t_class = "_top";
                    } else {
                        if (opts.defaultPosition == "left") {
                            t_class = "_left";
                        } else {
                            if (opts.defaultPosition == "right") {
                                t_class = "_right";
                            }
                        }
                    }
                }
                var right_compare = w_compare + left < parseInt($(window).scrollLeft()),
                    left_compare = tip_w + left > parseInt($(window).width());
                if (
                    (right_compare && w_compare < 0) ||
                    (t_class == "_right" && !left_compare) ||
                    (t_class == "_left" && left < tip_w + opts.edgeOffset + 5)
                ) {
                    t_class = "_right";
                    arrow_top = Math.round(tip_h - 13) / 2;
                    arrow_left = -12;
                    marg_left = Math.round(left + org_width + opts.edgeOffset);
                    marg_top = Math.round(top + h_compare);
                } else {
                    if (
                        (left_compare && w_compare < 0) ||
                        (t_class == "_left" && !right_compare)
                    ) {
                        t_class = "_left";
                        arrow_top = Math.round(tip_h - 13) / 2;
                        arrow_left = Math.round(tip_w);
                        marg_left = Math.round(left - (tip_w + opts.edgeOffset + 5));
                        marg_top = Math.round(top + h_compare);
                    }
                }
                var top_compare =
                    top + org_height + opts.edgeOffset + tip_h + 8 >
                    parseInt($(window).height() + $(window).scrollTop()),
                    bottom_compare = top + org_height - (opts.edgeOffset + tip_h + 8) < 0;
                if (
                    top_compare ||
                    (t_class == "_bottom" && top_compare) ||
                    (t_class == "_top" && !bottom_compare)
                ) {
                    if (t_class == "_top" || t_class == "_bottom") {
                        t_class = "_top";
                    } else {
                        t_class = t_class + "_top";
                    }
                    arrow_top = tip_h;
                    marg_top = Math.round(top - (tip_h + 5 + opts.edgeOffset));
                } else {
                    if (
                        bottom_compare | (t_class == "_top" && bottom_compare) ||
                        (t_class == "_bottom" && !top_compare)
                    ) {
                        if (t_class == "_top" || t_class == "_bottom") {
                            t_class = "_bottom";
                        } else {
                            t_class = t_class + "_bottom";
                        }
                        arrow_top = -12;
                        marg_top = Math.round(top + org_height + opts.edgeOffset);
                    }
                }
                if (t_class == "_right_top" || t_class == "_left_top") {
                    marg_top = marg_top + 5;
                } else {
                    if (t_class == "_right_bottom" || t_class == "_left_bottom") {
                        marg_top = marg_top - 5;
                    }
                }
                if (t_class == "_left_top" || t_class == "_left_bottom") {
                    marg_left = marg_left + 5;
                }
                tiptip_arrow.css({
                    "margin-left": arrow_left + "px",
                    "margin-top": arrow_top + "px"
                });
                tiptip_holder
                    .css({
                        "margin-left": marg_left + "px",
                        "margin-top": marg_top + "px"
                    })
                    .addClass("tip" + t_class);
                if (timeout) {
                    clearTimeout(timeout);
                }
                timeout = setTimeout(function () {
                    tiptip_holder.stop(true, true).fadeIn(opts.fadeIn);
                }, opts.delay);
                opts.afterEnter.call(org_elem, callback_data);
            }
            function deactive_tiptip() {
                if (opts.exit.call(org_elem, callback_data) === false) {
                    return;
                }
                if (timeout) {
                    clearTimeout(timeout);
                }
                tiptip_holder.fadeOut(opts.fadeOut);
                opts.afterExit.call(org_elem, callback_data);
            }
        });
    };
})(jQuery);

/**
 * jQuery UI Touch Punch 0.2.2
 * Copyright 2011, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * Depends: jquery.ui.widget jquery.ui.mouse
 */
(function (b) {
    b.support.touch = "ontouchend" in document;
    if (!b.support.touch) {
        return;
    }
    var c = b.ui.mouse.prototype,
        e = c._mouseInit,
        a;
    function d(g, h) {
        if (g.originalEvent.touches.length > 1) {
            return;
        }
        g.preventDefault();
        var i = g.originalEvent.changedTouches[0],
            f = document.createEvent("MouseEvents");
        f.initMouseEvent(
            h,
            true,
            true,
            window,
            1,
            i.screenX,
            i.screenY,
            i.clientX,
            i.clientY,
            false,
            false,
            false,
            false,
            0,
            null
        );
        g.target.dispatchEvent(f);
    }
    c._touchStart = function (g) {
        var f = this;
        if (a || !f._mouseCapture(g.originalEvent.changedTouches[0])) {
            return;
        }
        a = true;
        f._touchMoved = false;
        d(g, "mouseover");
        d(g, "mousemove");
        d(g, "mousedown");
    };
    c._touchMove = function (f) {
        if (!a) {
            return;
        }
        this._touchMoved = true;
        d(f, "mousemove");
    };
    c._touchEnd = function (f) {
        if (!a) {
            return;
        }
        d(f, "mouseup");
        d(f, "mouseout");
        if (!this._touchMoved) {
            d(f, "click");
        }
        a = false;
    };
    c._mouseInit = function () {
        var f = this;
        f.element
            .bind("touchstart", b.proxy(f, "_touchStart"))
            .bind("touchmove", b.proxy(f, "_touchMove"))
            .bind("touchend", b.proxy(f, "_touchEnd"));
        e.call(f);
    };
})(jQuery);

/**
 * fileOverview TouchSwipe - jQuery Plugin
 * version 1.6.5
 */
(function (a) {
    if (typeof define === "function" && define.amd && define.amd.jQuery) {
        define(["jquery"], a);
    } else {
        a(jQuery);
    }
})(function (e) {
    var o = "left",
        n = "right",
        d = "up",
        v = "down",
        c = "in",
        w = "out",
        l = "none",
        r = "auto",
        k = "swipe",
        s = "pinch",
        x = "tap",
        i = "doubletap",
        b = "longtap",
        A = "horizontal",
        t = "vertical",
        h = "all",
        q = 10,
        f = "start",
        j = "move",
        g = "end",
        p = "cancel",
        a = "ontouchstart" in window,
        y = "TouchSwipe";
    var m = {
        fingers: 1,
        threshold: 75,
        cancelThreshold: null,
        pinchThreshold: 20,
        maxTimeThreshold: null,
        fingerReleaseThreshold: 250,
        longTapThreshold: 500,
        doubleTapThreshold: 200,
        swipe: null,
        swipeLeft: null,
        swipeRight: null,
        swipeUp: null,
        swipeDown: null,
        swipeStatus: null,
        pinchIn: null,
        pinchOut: null,
        pinchStatus: null,
        click: null,
        tap: null,
        doubleTap: null,
        longTap: null,
        triggerOnTouchEnd: true,
        triggerOnTouchLeave: false,
        allowPageScroll: "auto",
        fallbackToMouseEvents: true,
        excludedElements: "label, button, input, select, textarea, a, .noSwipe"
    };
    e.fn.swipe = function (D) {
        var C = e(this),
            B = C.data(y);
        if (B && typeof D === "string") {
            if (B[D]) {
                return B[D].apply(this, Array.prototype.slice.call(arguments, 1));
            } else {
                e.error("Method " + D + " does not exist on jQuery.swipe");
            }
        } else {
            if (!B && (typeof D === "object" || !D)) {
                return u.apply(this, arguments);
            }
        }
        return C;
    };
    e.fn.swipe.defaults = m;
    e.fn.swipe.phases = {
        PHASE_START: f,
        PHASE_MOVE: j,
        PHASE_END: g,
        PHASE_CANCEL: p
    };
    e.fn.swipe.directions = { LEFT: o, RIGHT: n, UP: d, DOWN: v, IN: c, OUT: w };
    e.fn.swipe.pageScroll = { NONE: l, HORIZONTAL: A, VERTICAL: t, AUTO: r };
    e.fn.swipe.fingers = { ONE: 1, TWO: 2, THREE: 3, ALL: h };
    function u(B) {
        if (
            B &&
            (B.allowPageScroll === undefined &&
                (B.swipe !== undefined || B.swipeStatus !== undefined))
        ) {
            B.allowPageScroll = l;
        }
        if (B.click !== undefined && B.tap === undefined) {
            B.tap = B.click;
        }
        if (!B) {
            B = {};
        }
        B = e.extend({}, e.fn.swipe.defaults, B);
        return this.each(function () {
            var D = e(this);
            var C = D.data(y);
            if (!C) {
                C = new z(this, B);
                D.data(y, C);
            }
        });
    }
    function z(a0, aq) {
        var av = a || !aq.fallbackToMouseEvents,
            G = av ? "touchstart" : "mousedown",
            au = av ? "touchmove" : "mousemove",
            R = av ? "touchend" : "mouseup",
            P = av ? null : "mouseleave",
            az = "touchcancel";
        var ac = 0,
            aL = null,
            Y = 0,
            aX = 0,
            aV = 0,
            D = 1,
            am = 0,
            aF = 0,
            J = null;
        var aN = e(a0);
        var W = "start";
        var T = 0;
        var aM = null;
        var Q = 0,
            aY = 0,
            a1 = 0,
            aa = 0,
            K = 0;
        var aS = null;
        try {
            aN.bind(G, aJ);
            aN.bind(az, a5);
        } catch (ag) {
            e.error("events not supported " + G + "," + az + " on jQuery.swipe");
        }
        this.enable = function () {
            aN.bind(G, aJ);
            aN.bind(az, a5);
            return aN;
        };
        this.disable = function () {
            aG();
            return aN;
        };
        this.destroy = function () {
            aG();
            aN.data(y, null);
            return aN;
        };
        this.option = function (a8, a7) {
            if (aq[a8] !== undefined) {
                if (a7 === undefined) {
                    return aq[a8];
                } else {
                    aq[a8] = a7;
                }
            } else {
                e.error("Option " + a8 + " does not exist on jQuery.swipe.options");
            }
            return null;
        };
        function aJ(a9) {
            if (ax()) {
                return;
            }
            if (e(a9.target).closest(aq.excludedElements, aN).length > 0) {
                return;
            }
            var ba = a9.originalEvent ? a9.originalEvent : a9;
            var a8,
                a7 = a ? ba.touches[0] : ba;
            W = f;
            if (a) {
                T = ba.touches.length;
            } else {
                a9.preventDefault();
            }
            ac = 0;
            aL = null;
            aF = null;
            Y = 0;
            aX = 0;
            aV = 0;
            D = 1;
            am = 0;
            aM = af();
            J = X();
            O();
            if (!a || (T === aq.fingers || aq.fingers === h) || aT()) {
                ae(0, a7);
                Q = ao();
                if (T == 2) {
                    ae(1, ba.touches[1]);
                    aX = aV = ap(aM[0].start, aM[1].start);
                }
                if (aq.swipeStatus || aq.pinchStatus) {
                    a8 = L(ba, W);
                }
            } else {
                a8 = false;
            }
            if (a8 === false) {
                W = p;
                L(ba, W);
                return a8;
            } else {
                ak(true);
            }
            return null;
        }
        function aZ(ba) {
            var bd = ba.originalEvent ? ba.originalEvent : ba;
            if (W === g || W === p || ai()) {
                return;
            }
            var a9,
                a8 = a ? bd.touches[0] : bd;
            var bb = aD(a8);
            aY = ao();
            if (a) {
                T = bd.touches.length;
            }
            W = j;
            if (T == 2) {
                if (aX == 0) {
                    ae(1, bd.touches[1]);
                    aX = aV = ap(aM[0].start, aM[1].start);
                } else {
                    aD(bd.touches[1]);
                    aV = ap(aM[0].end, aM[1].end);
                    aF = an(aM[0].end, aM[1].end);
                }
                D = a3(aX, aV);
                am = Math.abs(aX - aV);
            }
            if (T === aq.fingers || aq.fingers === h || !a || aT()) {
                aL = aH(bb.start, bb.end);
                ah(ba, aL);
                ac = aO(bb.start, bb.end);
                Y = aI();
                aE(aL, ac);
                if (aq.swipeStatus || aq.pinchStatus) {
                    a9 = L(bd, W);
                }
                if (!aq.triggerOnTouchEnd || aq.triggerOnTouchLeave) {
                    var a7 = true;
                    if (aq.triggerOnTouchLeave) {
                        var bc = aU(this);
                        a7 = B(bb.end, bc);
                    }
                    if (!aq.triggerOnTouchEnd && a7) {
                        W = ay(j);
                    } else {
                        if (aq.triggerOnTouchLeave && !a7) {
                            W = ay(g);
                        }
                    }
                    if (W == p || W == g) {
                        L(bd, W);
                    }
                }
            } else {
                W = p;
                L(bd, W);
            }
            if (a9 === false) {
                W = p;
                L(bd, W);
            }
        }
        function I(a7) {
            var a8 = a7.originalEvent;
            if (a) {
                if (a8.touches.length > 0) {
                    C();
                    return true;
                }
            }
            if (ai()) {
                T = aa;
            }
            a7.preventDefault();
            aY = ao();
            Y = aI();
            if (a6()) {
                W = p;
                L(a8, W);
            } else {
                if (
                    aq.triggerOnTouchEnd ||
                    (aq.triggerOnTouchEnd == false && W === j)
                ) {
                    W = g;
                    L(a8, W);
                } else {
                    if (!aq.triggerOnTouchEnd && a2()) {
                        W = g;
                        aB(a8, W, x);
                    } else {
                        if (W === j) {
                            W = p;
                            L(a8, W);
                        }
                    }
                }
            }
            ak(false);
            return null;
        }
        function a5() {
            T = 0;
            aY = 0;
            Q = 0;
            aX = 0;
            aV = 0;
            D = 1;
            O();
            ak(false);
        }
        function H(a7) {
            var a8 = a7.originalEvent;
            if (aq.triggerOnTouchLeave) {
                W = ay(g);
                L(a8, W);
            }
        }
        function aG() {
            aN.unbind(G, aJ);
            aN.unbind(az, a5);
            aN.unbind(au, aZ);
            aN.unbind(R, I);
            if (P) {
                aN.unbind(P, H);
            }
            ak(false);
        }
        function ay(bb) {
            var ba = bb;
            var a9 = aw();
            var a8 = aj();
            var a7 = a6();
            if (!a9 || a7) {
                ba = p;
            } else {
                if (
                    a8 &&
                    bb == j &&
                    (!aq.triggerOnTouchEnd || aq.triggerOnTouchLeave)
                ) {
                    ba = g;
                } else {
                    if (!a8 && bb == g && aq.triggerOnTouchLeave) {
                        ba = p;
                    }
                }
            }
            return ba;
        }
        function L(a9, a7) {
            var a8 = undefined;
            if (F() || S()) {
                a8 = aB(a9, a7, k);
            } else {
                if ((M() || aT()) && a8 !== false) {
                    a8 = aB(a9, a7, s);
                }
            }
            if (aC() && a8 !== false) {
                a8 = aB(a9, a7, i);
            } else {
                if (al() && a8 !== false) {
                    a8 = aB(a9, a7, b);
                } else {
                    if (ad() && a8 !== false) {
                        a8 = aB(a9, a7, x);
                    }
                }
            }
            if (a7 === p) {
                a5(a9);
            }
            if (a7 === g) {
                if (a) {
                    if (a9.touches.length == 0) {
                        a5(a9);
                    }
                } else {
                    a5(a9);
                }
            }
            return a8;
        }
        function aB(ba, a7, a9) {
            var a8 = undefined;
            if (a9 == k) {
                aN.trigger("swipeStatus", [a7, aL || null, ac || 0, Y || 0, T]);
                if (aq.swipeStatus) {
                    a8 = aq.swipeStatus.call(aN, ba, a7, aL || null, ac || 0, Y || 0, T);
                    if (a8 === false) {
                        return false;
                    }
                }
                if (a7 == g && aR()) {
                    aN.trigger("swipe", [aL, ac, Y, T]);
                    if (aq.swipe) {
                        a8 = aq.swipe.call(aN, ba, aL, ac, Y, T);
                        if (a8 === false) {
                            return false;
                        }
                    }
                    switch (aL) {
                        case o:
                            aN.trigger("swipeLeft", [aL, ac, Y, T]);
                            if (aq.swipeLeft) {
                                a8 = aq.swipeLeft.call(aN, ba, aL, ac, Y, T);
                            }
                            break;
                        case n:
                            aN.trigger("swipeRight", [aL, ac, Y, T]);
                            if (aq.swipeRight) {
                                a8 = aq.swipeRight.call(aN, ba, aL, ac, Y, T);
                            }
                            break;
                        case d:
                            aN.trigger("swipeUp", [aL, ac, Y, T]);
                            if (aq.swipeUp) {
                                a8 = aq.swipeUp.call(aN, ba, aL, ac, Y, T);
                            }
                            break;
                        case v:
                            aN.trigger("swipeDown", [aL, ac, Y, T]);
                            if (aq.swipeDown) {
                                a8 = aq.swipeDown.call(aN, ba, aL, ac, Y, T);
                            }
                            break;
                    }
                }
            }
            if (a9 == s) {
                aN.trigger("pinchStatus", [a7, aF || null, am || 0, Y || 0, T, D]);
                if (aq.pinchStatus) {
                    a8 = aq.pinchStatus.call(
                        aN,
                        ba,
                        a7,
                        aF || null,
                        am || 0,
                        Y || 0,
                        T,
                        D
                    );
                    if (a8 === false) {
                        return false;
                    }
                }
                if (a7 == g && a4()) {
                    switch (aF) {
                        case c:
                            aN.trigger("pinchIn", [aF || null, am || 0, Y || 0, T, D]);
                            if (aq.pinchIn) {
                                a8 = aq.pinchIn.call(aN, ba, aF || null, am || 0, Y || 0, T, D);
                            }
                            break;
                        case w:
                            aN.trigger("pinchOut", [aF || null, am || 0, Y || 0, T, D]);
                            if (aq.pinchOut) {
                                a8 = aq.pinchOut.call(
                                    aN,
                                    ba,
                                    aF || null,
                                    am || 0,
                                    Y || 0,
                                    T,
                                    D
                                );
                            }
                            break;
                    }
                }
            }
            if (a9 == x) {
                if (a7 === p || a7 === g) {
                    clearTimeout(aS);
                    if (V() && !E()) {
                        K = ao();
                        aS = setTimeout(
                            e.proxy(function () {
                                K = null;
                                aN.trigger("tap", [ba.target]);
                                if (aq.tap) {
                                    a8 = aq.tap.call(aN, ba, ba.target);
                                }
                            }, this),
                            aq.doubleTapThreshold
                        );
                    } else {
                        K = null;
                        aN.trigger("tap", [ba.target]);
                        if (aq.tap) {
                            a8 = aq.tap.call(aN, ba, ba.target);
                        }
                    }
                }
            } else {
                if (a9 == i) {
                    if (a7 === p || a7 === g) {
                        clearTimeout(aS);
                        K = null;
                        aN.trigger("doubletap", [ba.target]);
                        if (aq.doubleTap) {
                            a8 = aq.doubleTap.call(aN, ba, ba.target);
                        }
                    }
                } else {
                    if (a9 == b) {
                        if (a7 === p || a7 === g) {
                            clearTimeout(aS);
                            K = null;
                            aN.trigger("longtap", [ba.target]);
                            if (aq.longTap) {
                                a8 = aq.longTap.call(aN, ba, ba.target);
                            }
                        }
                    }
                }
            }
            return a8;
        }
        function aj() {
            var a7 = true;
            if (aq.threshold !== null) {
                a7 = ac >= aq.threshold;
            }
            return a7;
        }
        function a6() {
            var a7 = false;
            if (aq.cancelThreshold !== null && aL !== null) {
                a7 = aP(aL) - ac >= aq.cancelThreshold;
            }
            return a7;
        }
        function ab() {
            if (aq.pinchThreshold !== null) {
                return am >= aq.pinchThreshold;
            }
            return true;
        }
        function aw() {
            var a7;
            if (aq.maxTimeThreshold) {
                if (Y >= aq.maxTimeThreshold) {
                    a7 = false;
                } else {
                    a7 = true;
                }
            } else {
                a7 = true;
            }
            return a7;
        }
        function ah(a7, a8) {
            if (aq.allowPageScroll === l || aT()) {
                a7.preventDefault();
            } else {
                var a9 = aq.allowPageScroll === r;
                switch (a8) {
                    case o:
                        if ((aq.swipeLeft && a9) || (!a9 && aq.allowPageScroll != A)) {
                            a7.preventDefault();
                        }
                        break;
                    case n:
                        if ((aq.swipeRight && a9) || (!a9 && aq.allowPageScroll != A)) {
                            a7.preventDefault();
                        }
                        break;
                    case d:
                        if ((aq.swipeUp && a9) || (!a9 && aq.allowPageScroll != t)) {
                            a7.preventDefault();
                        }
                        break;
                    case v:
                        if ((aq.swipeDown && a9) || (!a9 && aq.allowPageScroll != t)) {
                            a7.preventDefault();
                        }
                        break;
                }
            }
        }
        function a4() {
            var a8 = aK();
            var a7 = U();
            var a9 = ab();
            return a8 && a7 && a9;
        }
        function aT() {
            return !!(aq.pinchStatus || aq.pinchIn || aq.pinchOut);
        }
        function M() {
            return !!(a4() && aT());
        }
        function aR() {
            var ba = aw();
            var bc = aj();
            var a9 = aK();
            var a7 = U();
            var a8 = a6();
            var bb = !a8 && a7 && a9 && bc && ba;
            return bb;
        }
        function S() {
            return !!(
                aq.swipe ||
                aq.swipeStatus ||
                aq.swipeLeft ||
                aq.swipeRight ||
                aq.swipeUp ||
                aq.swipeDown
            );
        }
        function F() {
            return !!(aR() && S());
        }
        function aK() {
            return T === aq.fingers || aq.fingers === h || !a;
        }
        function U() {
            return aM[0].end.x !== 0;
        }
        function a2() {
            return !!aq.tap;
        }
        function V() {
            return !!aq.doubleTap;
        }
        function aQ() {
            return !!aq.longTap;
        }
        function N() {
            if (K == null) {
                return false;
            }
            var a7 = ao();
            return V() && a7 - K <= aq.doubleTapThreshold;
        }
        function E() {
            return N();
        }
        function at() {
            return (T === 1 || !a) && (isNaN(ac) || ac === 0);
        }
        function aW() {
            return Y > aq.longTapThreshold && ac < q;
        }
        function ad() {
            return !!(at() && a2());
        }
        function aC() {
            return !!(N() && V());
        }
        function al() {
            return !!(aW() && aQ());
        }
        function C() {
            a1 = ao();
            aa = event.touches.length + 1;
        }
        function O() {
            a1 = 0;
            aa = 0;
        }
        function ai() {
            var a7 = false;
            if (a1) {
                var a8 = ao() - a1;
                if (a8 <= aq.fingerReleaseThreshold) {
                    a7 = true;
                }
            }
            return a7;
        }
        function ax() {
            return !!(aN.data(y + "_intouch") === true);
        }
        function ak(a7) {
            if (a7 === true) {
                aN.bind(au, aZ);
                aN.bind(R, I);
                if (P) {
                    aN.bind(P, H);
                }
            } else {
                aN.unbind(au, aZ, false);
                aN.unbind(R, I, false);
                if (P) {
                    aN.unbind(P, H, false);
                }
            }
            aN.data(y + "_intouch", a7 === true);
        }
        function ae(a8, a7) {
            var a9 = a7.identifier !== undefined ? a7.identifier : 0;
            aM[a8].identifier = a9;
            aM[a8].start.x = aM[a8].end.x = a7.pageX || a7.clientX;
            aM[a8].start.y = aM[a8].end.y = a7.pageY || a7.clientY;
            return aM[a8];
        }
        function aD(a7) {
            var a9 = a7.identifier !== undefined ? a7.identifier : 0;
            var a8 = Z(a9);
            a8.end.x = a7.pageX || a7.clientX;
            a8.end.y = a7.pageY || a7.clientY;
            return a8;
        }
        function Z(a8) {
            for (var a7 = 0; a7 < aM.length; a7++) {
                if (aM[a7].identifier == a8) {
                    return aM[a7];
                }
            }
        }
        function af() {
            var a7 = [];
            for (var a8 = 0; a8 <= 5; a8++) {
                a7.push({ start: { x: 0, y: 0 }, end: { x: 0, y: 0 }, identifier: 0 });
            }
            return a7;
        }
        function aE(a7, a8) {
            a8 = Math.max(a8, aP(a7));
            J[a7].distance = a8;
        }
        function aP(a7) {
            if (J[a7]) {
                return J[a7].distance;
            }
            return undefined;
        }
        function X() {
            var a7 = {};
            a7[o] = ar(o);
            a7[n] = ar(n);
            a7[d] = ar(d);
            a7[v] = ar(v);
            return a7;
        }
        function ar(a7) {
            return { direction: a7, distance: 0 };
        }
        function aI() {
            return aY - Q;
        }
        function ap(ba, a9) {
            var a8 = Math.abs(ba.x - a9.x);
            var a7 = Math.abs(ba.y - a9.y);
            return Math.round(Math.sqrt(a8 * a8 + a7 * a7));
        }
        function a3(a7, a8) {
            var a9 = (a8 / a7) * 1;
            return a9.toFixed(2);
        }
        function an() {
            if (D < 1) {
                return w;
            } else {
                return c;
            }
        }
        function aO(a8, a7) {
            return Math.round(
                Math.sqrt(Math.pow(a7.x - a8.x, 2) + Math.pow(a7.y - a8.y, 2))
            );
        }
        function aA(ba, a8) {
            var a7 = ba.x - a8.x;
            var bc = a8.y - ba.y;
            var a9 = Math.atan2(bc, a7);
            var bb = Math.round((a9 * 180) / Math.PI);
            if (bb < 0) {
                bb = 360 - Math.abs(bb);
            }
            return bb;
        }
        function aH(a8, a7) {
            var a9 = aA(a8, a7);
            if (a9 <= 45 && a9 >= 0) {
                return o;
            } else {
                if (a9 <= 360 && a9 >= 315) {
                    return o;
                } else {
                    if (a9 >= 135 && a9 <= 225) {
                        return n;
                    } else {
                        if (a9 > 45 && a9 < 135) {
                            return v;
                        } else {
                            return d;
                        }
                    }
                }
            }
        }
        function ao() {
            var a7 = new Date();
            return a7.getTime();
        }
        function aU(a7) {
            a7 = e(a7);
            var a9 = a7.offset();
            var a8 = {
                left: a9.left,
                right: a9.left + a7.outerWidth(),
                top: a9.top,
                bottom: a9.top + a7.outerHeight()
            };
            return a8;
        }
        function B(a7, a8) {
            return (
                a7.x > a8.left && a7.x < a8.right && a7.y > a8.top && a7.y < a8.bottom
            );
        }
    }
});

/**
 * Copyright (c) 2011-2013 Felix Gnass
 * Licensed under the MIT license
 */
//fgnass.github.com/spin.js#v1.3.2
(function (root, factory) {
    if (typeof exports == "object") {
        module.exports = factory();
    } else {
        if (typeof define == "function" && define.amd) {
            define(factory);
        } else {
            root.Spinner = factory();
        }
    }
})(this, function () {
    var prefixes = ["webkit", "Moz", "ms", "O"],
        animations = {},
        useCssAnimations;
    function createEl(tag, prop) {
        var el = document.createElement(tag || "div"),
            n;
        for (n in prop) {
            el[n] = prop[n];
        }
        return el;
    }
    function ins(parent) {
        for (var i = 1, n = arguments.length; i < n; i++) {
            parent.appendChild(arguments[i]);
        }
        return parent;
    }
    var sheet = (function () {
        var el = createEl("style", { type: "text/css" });
        ins(document.getElementsByTagName("head")[0], el);
        return el.sheet || el.styleSheet;
    })();
    function addAnimation(alpha, trail, i, lines) {
        var name = ["opacity", trail, ~~(alpha * 100), i, lines].join("-"),
            start = 0.01 + (i / lines) * 100,
            z = Math.max(1 - ((1 - alpha) / trail) * (100 - start), alpha),
            prefix = useCssAnimations
                .substring(0, useCssAnimations.indexOf("Animation"))
                .toLowerCase(),
            pre = (prefix && "-" + prefix + "-") || "";
        if (!animations[name]) {
            sheet.insertRule(
                "@" +
                pre +
                "keyframes " +
                name +
                "{0%{opacity:" +
                z +
                "}" +
                start +
                "%{opacity:" +
                alpha +
                "}" +
                (start + 0.01) +
                "%{opacity:1}" +
                ((start + trail) % 100) +
                "%{opacity:" +
                alpha +
                "}100%{opacity:" +
                z +
                "}}",
                sheet.cssRules.length
            );
            animations[name] = 1;
        }
        return name;
    }
    function vendor(el, prop) {
        var s = el.style,
            pp,
            i;
        prop = prop.charAt(0).toUpperCase() + prop.slice(1);
        for (i = 0; i < prefixes.length; i++) {
            pp = prefixes[i] + prop;
            if (s[pp] !== undefined) {
                return pp;
            }
        }
        if (s[prop] !== undefined) {
            return prop;
        }
    }
    function css(el, prop) {
        for (var n in prop) {
            el.style[vendor(el, n) || n] = prop[n];
        }
        return el;
    }
    function merge(obj) {
        for (var i = 1; i < arguments.length; i++) {
            var def = arguments[i];
            for (var n in def) {
                if (obj[n] === undefined) {
                    obj[n] = def[n];
                }
            }
        }
        return obj;
    }
    function pos(el) {
        var o = { x: el.offsetLeft, y: el.offsetTop };
        while ((el = el.offsetParent)) {
            (o.x += el.offsetLeft), (o.y += el.offsetTop);
        }
        return o;
    }
    function getColor(color, idx) {
        return typeof color == "string" ? color : color[idx % color.length];
    }
    var defaults = {
        lines: 12,
        length: 7,
        width: 5,
        radius: 10,
        rotate: 0,
        corners: 1,
        color: "#000",
        direction: 1,
        speed: 1,
        trail: 100,
        opacity: 1 / 4,
        fps: 20,
        zIndex: "auto",
        className: "spinner",
        top: "auto",
        left: "auto",
        position: "relative"
    };
    function Spinner(o) {
        if (typeof this == "undefined") {
            return new Spinner(o);
        }
        this.opts = merge(o || {}, Spinner.defaults, defaults);
    }
    Spinner.defaults = {};
    merge(Spinner.prototype, {
        spin: function (target) {
            this.stop();
            var self = this,
                o = self.opts,
                el = (self.el = css(createEl(0, { className: o.className }), {
                    position: o.position,
                    width: 0,
                    zIndex: o.zIndex
                })),
                mid = o.radius + o.length + o.width,
                ep,
                tp;
            if (target) {
                target.insertBefore(el, target.firstChild || null);
                tp = pos(target);
                ep = pos(el);
                css(el, {
                    left:
                        (o.left == "auto"
                            ? tp.x - ep.x + (target.offsetWidth >> 1)
                            : parseInt(o.left, 10) + mid) + "px",
                    top:
                        (o.top == "auto"
                            ? tp.y - ep.y + (target.offsetHeight >> 1)
                            : parseInt(o.top, 10) + mid) + "px"
                });
            }
            el.setAttribute("role", "progressbar");
            self.lines(el, self.opts);
            if (!useCssAnimations) {
                var i = 0,
                    start = ((o.lines - 1) * (1 - o.direction)) / 2,
                    alpha,
                    fps = o.fps,
                    f = fps / o.speed,
                    ostep = (1 - o.opacity) / ((f * o.trail) / 100),
                    astep = f / o.lines;
                (function anim() {
                    i++;
                    for (var j = 0; j < o.lines; j++) {
                        alpha = Math.max(
                            1 - ((i + (o.lines - j) * astep) % f) * ostep,
                            o.opacity
                        );
                        self.opacity(el, j * o.direction + start, alpha, o);
                    }
                    self.timeout = self.el && setTimeout(anim, ~~(1000 / fps));
                })();
            }
            return self;
        },
        stop: function () {
            var el = this.el;
            if (el) {
                clearTimeout(this.timeout);
                if (el.parentNode) {
                    el.parentNode.removeChild(el);
                }
                this.el = undefined;
            }
            return this;
        },
        lines: function (el, o) {
            var i = 0,
                start = ((o.lines - 1) * (1 - o.direction)) / 2,
                seg;
            function fill(color, shadow) {
                return css(createEl(), {
                    position: "absolute",
                    width: o.length + o.width + "px",
                    height: o.width + "px",
                    background: color,
                    boxShadow: shadow,
                    transformOrigin: "left",
                    transform:
                        "rotate(" +
                        ~~((360 / o.lines) * i + o.rotate) +
                        "deg) translate(" +
                        o.radius +
                        "px,0)",
                    borderRadius: ((o.corners * o.width) >> 1) + "px"
                });
            }
            for (; i < o.lines; i++) {
                seg = css(createEl(), {
                    position: "absolute",
                    top: 1 + ~(o.width / 2) + "px",
                    transform: o.hwaccel ? "translate3d(0,0,0)" : "",
                    opacity: o.opacity,
                    animation:
                        useCssAnimations &&
                        addAnimation(o.opacity, o.trail, start + i * o.direction, o.lines) +
                        " " +
                        1 / o.speed +
                        "s linear infinite"
                });
                if (o.shadow) {
                    ins(
                        seg,
                        css(fill("rgba(0,0,0,.25)", "0 0 4px rgba(0,0,0,.5)"), {
                            top: 2 + "px"
                        })
                    );
                }
                ins(el, ins(seg, fill(getColor(o.color, i), "0 0 1px rgba(0,0,0,.1)")));
            }
            return el;
        },
        opacity: function (el, i, val) {
            if (i < el.childNodes.length) {
                el.childNodes[i].style.opacity = val;
            }
        }
    });
    function initVML() {
        function vml(tag, attr) {
            return createEl(
                "<" + tag + ' xmlns="urn:schemas-microsoft.com:vml" class="spin-vml">',
                attr
            );
        }
        sheet.addRule(".spin-vml", "behavior:url(#default#VML)");
        Spinner.prototype.lines = function (el, o) {
            var r = o.length + o.width,
                s = 2 * r;
            function grp() {
                return css(
                    vml("group", { coordsize: s + " " + s, coordorigin: -r + " " + -r }),
                    { width: s, height: s }
                );
            }
            var margin = -(o.width + o.length) * 2 + "px",
                g = css(grp(), { position: "absolute", top: margin, left: margin }),
                i;
            function seg(i, dx, filter) {
                ins(
                    g,
                    ins(
                        css(grp(), { rotation: (360 / o.lines) * i + "deg", left: ~~dx }),
                        ins(
                            css(vml("roundrect", { arcsize: o.corners }), {
                                width: r,
                                height: o.width,
                                left: o.radius,
                                top: -o.width >> 1,
                                filter: filter
                            }),
                            vml("fill", { color: getColor(o.color, i), opacity: o.opacity }),
                            vml("stroke", { opacity: 0 })
                        )
                    )
                );
            }
            if (o.shadow) {
                for (i = 1; i <= o.lines; i++) {
                    seg(
                        i,
                        -2,
                        "progid:DXImageTransform.Microsoft.Blur(pixelradius=2,makeshadow=1,shadowopacity=.3)"
                    );
                }
            }
            for (i = 1; i <= o.lines; i++) {
                seg(i);
            }
            return ins(el, g);
        };
        Spinner.prototype.opacity = function (el, i, val, o) {
            var c = el.firstChild;
            o = (o.shadow && o.lines) || 0;
            if (c && i + o < c.childNodes.length) {
                c = c.childNodes[i + o];
                c = c && c.firstChild;
                c = c && c.firstChild;
                if (c) {
                    c.opacity = val;
                }
            }
        };
    }
    var probe = css(createEl("group"), { behavior: "url(#default#VML)" });
    if (!vendor(probe, "transform") && probe.adj) {
        initVML();
    } else {
        useCssAnimations = vendor(probe, "animation");
    }
    return Spinner;
});
(function (e) {
    if (typeof exports == "object") {
        e(require("jquery"), require("spin"));
    } else if (typeof define == "function" && define.amd) {
        define(["jquery", "spin"], e);
    } else {
        if (!window.Spinner) throw new Error("Spin.js not present");
        e(window.jQuery, window.Spinner);
    }
})(function (e, t) {
    e.fn.spin = function (n, r) {
        return this.each(function () {
            var i = e(this),
                s = i.data();
            if (s.spinner) {
                s.spinner.stop();
                delete s.spinner;
            }
            if (n !== false) {
                n = e.extend({ color: r || i.css("color") }, e.fn.spin.presets[n] || n);
                s.spinner = new t(n).spin(this);
            }
        });
    };
    e.fn.spin.presets = {
        tiny: { lines: 8, length: 2, width: 2, radius: 3 },
        small: { lines: 8, length: 4, width: 3, radius: 5 },
        large: { lines: 10, length: 8, width: 4, radius: 8 }
    };
});

// http://stackoverflow.com/a/21422049
(function (e) {
    e.fn.hasScrollbar = function () {
        var e = {},
            t = this.get(0);
        e.vertical = t.scrollHeight > t.clientHeight ? true : false;
        e.horizontal = t.scrollWidth > t.clientWidth ? true : false;
        return e;
    };
})(jQuery);

/**
 * Antiscroll
 * https://github.com/LearnBoost/antiscroll
 */
(function ($) {
    $.fn.antiscroll = function (options) {
        return this.each(function () {
            if ($(this).data("antiscroll"))
                $(this)
                    .data("antiscroll")
                    .destroy();
            $(this).data("antiscroll", new $.Antiscroll(this, options));
        });
    };
    $.Antiscroll = Antiscroll;
    function Antiscroll(el, opts) {
        this.el = $(el);
        this.options = opts || {};
        this.x = false !== this.options.x || this.options.forceHorizontal;
        this.y = false !== this.options.y || this.options.forceVertical;
        this.autoHide = false !== this.options.autoHide;
        this.padding = undefined == this.options.padding ? 2 : this.options.padding;
        this.inner = this.el.find(".antiscroll-inner");
        this.inner.css({
            width: "+=" + (this.y ? scrollbarSize() : 0),
            height: "+=" + (this.x ? scrollbarSize() : 0)
        });
        this.refresh();
    }
    Antiscroll.prototype.refresh = function () {
        var needHScroll =
            this.inner.get(0).scrollWidth >
            this.el.width() + (this.y ? scrollbarSize() : 0),
            needVScroll =
                this.inner.get(0).scrollHeight >
                this.el.height() + (this.x ? scrollbarSize() : 0);
        if (this.x)
            if (!this.horizontal && needHScroll)
                this.horizontal = new Scrollbar.Horizontal(this);
            else if (this.horizontal && !needHScroll) {
                this.horizontal.destroy();
                this.horizontal = null;
            } else if (this.horizontal) this.horizontal.update();
        if (this.y)
            if (!this.vertical && needVScroll)
                this.vertical = new Scrollbar.Vertical(this);
            else if (this.vertical && !needVScroll) {
                this.vertical.destroy();
                this.vertical = null;
            } else if (this.vertical) this.vertical.update();
    };
    Antiscroll.prototype.destroy = function () {
        if (this.horizontal) {
            this.horizontal.destroy();
            this.horizontal = null;
        }
        if (this.vertical) {
            this.vertical.destroy();
            this.vertical = null;
        }
        return this;
    };
    Antiscroll.prototype.rebuild = function () {
        this.destroy();
        this.inner.attr("style", "");
        Antiscroll.call(this, this.el, this.options);
        return this;
    };
    function Scrollbar(pane) {
        this.pane = pane;
        this.pane.el.append(this.el);
        this.innerEl = this.pane.inner.get(0);
        this.dragging = false;
        this.enter = false;
        this.shown = false;
        this.pane.el.mouseenter($.proxy(this, "mouseenter"));
        this.pane.el.mouseleave($.proxy(this, "mouseleave"));
        this.el.mousedown($.proxy(this, "mousedown"));
        this.innerPaneScrollListener = $.proxy(this, "scroll");
        this.pane.inner.scroll(this.innerPaneScrollListener);
        this.innerPaneMouseWheelListener = $.proxy(this, "mousewheel");
        this.pane.inner.bind("mousewheel", this.innerPaneMouseWheelListener);
        var initialDisplay = this.pane.options.initialDisplay;
        if (initialDisplay !== false) {
            this.show();
            if (this.pane.autoHide)
                this.hiding = setTimeout(
                    $.proxy(this, "hide"),
                    parseInt(initialDisplay, 10) || 3e3
                );
        }
    }
    Scrollbar.prototype.destroy = function () {
        this.el.remove();
        this.pane.inner.unbind("scroll", this.innerPaneScrollListener);
        this.pane.inner.unbind("mousewheel", this.innerPaneMouseWheelListener);
        return this;
    };
    Scrollbar.prototype.mouseenter = function () {
        this.enter = true;
        this.show();
    };
    Scrollbar.prototype.mouseleave = function () {
        this.enter = false;
        if (!this.dragging) if (this.pane.autoHide) this.hide();
    };
    Scrollbar.prototype.scroll = function () {
        if (!this.shown) {
            this.show();
            if (!this.enter && !this.dragging)
                if (this.pane.autoHide)
                    this.hiding = setTimeout($.proxy(this, "hide"), 1500);
        }
        this.update();
    };
    Scrollbar.prototype.mousedown = function (ev) {
        ev.preventDefault();
        this.dragging = true;
        this.startPageY = ev.pageY - parseInt(this.el.css("top"), 10);
        this.startPageX = ev.pageX - parseInt(this.el.css("left"), 10);
        this.el[0].ownerDocument.onselectstart = function () {
            return false;
        };
        var pane = this.pane,
            move = $.proxy(this, "mousemove"),
            self = this;
        $(this.el[0].ownerDocument)
            .mousemove(move)
            .mouseup(function () {
                self.dragging = false;
                this.onselectstart = null;
                $(this).unbind("mousemove", move);
                if (!self.enter) self.hide();
            });
    };
    Scrollbar.prototype.show = function (duration) {
        if (!this.shown && this.update()) {
            this.el.addClass("antiscroll-scrollbar-shown");
            if (this.hiding) {
                clearTimeout(this.hiding);
                this.hiding = null;
            }
            this.shown = true;
        }
    };
    Scrollbar.prototype.hide = function () {
        if (this.pane.autoHide !== false && this.shown) {
            this.el.removeClass("antiscroll-scrollbar-shown");
            this.shown = false;
        }
    };
    Scrollbar.Horizontal = function (pane) {
        this.el = $(
            '<div class="antiscroll-scrollbar antiscroll-scrollbar-horizontal">',
            pane.el
        );
        Scrollbar.call(this, pane);
    };
    inherits(Scrollbar.Horizontal, Scrollbar);
    Scrollbar.Horizontal.prototype.update = function () {
        var paneWidth = this.pane.el.width(),
            trackWidth = paneWidth - this.pane.padding * 2,
            innerEl = this.pane.inner.get(0);
        this.el
            .css("width", (trackWidth * paneWidth) / innerEl.scrollWidth)
            .css("left", (trackWidth * innerEl.scrollLeft) / innerEl.scrollWidth);
        return paneWidth < innerEl.scrollWidth;
    };
    Scrollbar.Horizontal.prototype.mousemove = function (ev) {
        var trackWidth = this.pane.el.width() - this.pane.padding * 2,
            pos = ev.pageX - this.startPageX,
            barWidth = this.el.width(),
            innerEl = this.pane.inner.get(0);
        var y = Math.min(Math.max(pos, 0), trackWidth - barWidth);
        innerEl.scrollLeft =
            ((innerEl.scrollWidth - this.pane.el.width()) * y) /
            (trackWidth - barWidth);
    };
    Scrollbar.Horizontal.prototype.mousewheel = function (ev, delta, x, y) {
        if (
            (x < 0 && 0 == this.pane.inner.get(0).scrollLeft) ||
            (x > 0 &&
                this.innerEl.scrollLeft + Math.ceil(this.pane.el.width()) ==
                this.innerEl.scrollWidth)
        ) {
            ev.preventDefault();
            return false;
        }
    };
    Scrollbar.Vertical = function (pane) {
        this.el = $(
            '<div class="antiscroll-scrollbar antiscroll-scrollbar-vertical">',
            pane.el
        );
        Scrollbar.call(this, pane);
    };
    inherits(Scrollbar.Vertical, Scrollbar);
    Scrollbar.Vertical.prototype.update = function () {
        var paneHeight = this.pane.el.height(),
            trackHeight = paneHeight - this.pane.padding * 2,
            innerEl = this.innerEl;
        var scrollbarHeight = (trackHeight * paneHeight) / innerEl.scrollHeight;
        scrollbarHeight = scrollbarHeight < 20 ? 20 : scrollbarHeight;
        var topPos = (trackHeight * innerEl.scrollTop) / innerEl.scrollHeight;
        if (topPos + scrollbarHeight > trackHeight) {
            var diff = topPos + scrollbarHeight - trackHeight;
            topPos = topPos - diff - 3;
        }
        this.el.css("height", scrollbarHeight).css("top", topPos);
        return paneHeight < innerEl.scrollHeight;
    };
    Scrollbar.Vertical.prototype.mousemove = function (ev) {
        var paneHeight = this.pane.el.height(),
            trackHeight = paneHeight - this.pane.padding * 2,
            pos = ev.pageY - this.startPageY,
            barHeight = this.el.height(),
            innerEl = this.innerEl;
        var y = Math.min(Math.max(pos, 0), trackHeight - barHeight);
        innerEl.scrollTop =
            ((innerEl.scrollHeight - paneHeight) * y) / (trackHeight - barHeight);
    };
    Scrollbar.Vertical.prototype.mousewheel = function (ev, delta, x, y) {
        if (
            (y > 0 && 0 == this.innerEl.scrollTop) ||
            (y < 0 &&
                this.innerEl.scrollTop + Math.ceil(this.pane.el.height()) ==
                this.innerEl.scrollHeight)
        ) {
            ev.preventDefault();
            return false;
        }
    };
    function inherits(ctorA, ctorB) {
        function f() { }
        f.prototype = ctorB.prototype;
        ctorA.prototype = new f();
    }
    var size;
    function scrollbarSize() {
        if (size === undefined) {
            var div = $(
                '<div class="antiscroll-inner" style="width:50px;height:50px;overflow-y:scroll;' +
                'position:absolute;top:-200px;left:-200px;"><div style="height:100px;width:100%">' +
                "</div>"
            );
            $("body").append(div);
            var w1 = $(div).innerWidth();
            var w2 = $("div", div).innerWidth();
            $(div).remove();
            size = w1 - w2;
        }
        return size;
    }
})(jQuery);

/**
 * jQuery Mousewheel
 * ! Copyright (c) 2013 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).
 *
 * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.
 * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.
 * Thanks to: Seamus Leahy for adding deltaX and deltaY
 *
 * Version: 3.1.3
 *
 * Requires: 1.2.2+
 */
(function (factory) {
    if (typeof define === "function" && define.amd) define(["jquery"], factory);
    else if (typeof exports === "object") module.exports = factory;
    else factory(jQuery);
})(function ($) {
    var toFix = ["wheel", "mousewheel", "DOMMouseScroll", "MozMousePixelScroll"];
    var toBind =
        "onwheel" in document || document.documentMode >= 9
            ? ["wheel"]
            : ["mousewheel", "DomMouseScroll", "MozMousePixelScroll"];
    var lowestDelta, lowestDeltaXY;
    if ($.event.fixHooks)
        for (var i = toFix.length; i;)
            $.event.fixHooks[toFix[--i]] = $.event.mouseHooks;
    $.event.special.mousewheel = {
        setup: function () {
            if (this.addEventListener)
                for (var i = toBind.length; i;)
                    this.addEventListener(toBind[--i], handler, false);
            else this.onmousewheel = handler;
        },
        teardown: function () {
            if (this.removeEventListener)
                for (var i = toBind.length; i;)
                    this.removeEventListener(toBind[--i], handler, false);
            else this.onmousewheel = null;
        }
    };
    $.fn.extend({
        mousewheel: function (fn) {
            return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel");
        },
        unmousewheel: function (fn) {
            return this.unbind("mousewheel", fn);
        }
    });
    function handler(event) {
        var orgEvent = event || window.event,
            args = [].slice.call(arguments, 1),
            delta = 0,
            deltaX = 0,
            deltaY = 0,
            absDelta = 0,
            absDeltaXY = 0,
            fn;
        event = $.event.fix(orgEvent);
        event.type = "mousewheel";
        if (orgEvent.wheelDelta) delta = orgEvent.wheelDelta;
        if (orgEvent.detail) delta = orgEvent.detail * -1;
        if (orgEvent.deltaY) {
            deltaY = orgEvent.deltaY * -1;
            delta = deltaY;
        }
        if (orgEvent.deltaX) {
            deltaX = orgEvent.deltaX;
            delta = deltaX * -1;
        }
        if (orgEvent.wheelDeltaY !== undefined) deltaY = orgEvent.wheelDeltaY;
        if (orgEvent.wheelDeltaX !== undefined) deltaX = orgEvent.wheelDeltaX * -1;
        absDelta = Math.abs(delta);
        if (!lowestDelta || absDelta < lowestDelta) lowestDelta = absDelta;
        absDeltaXY = Math.max(Math.abs(deltaY), Math.abs(deltaX));
        if (!lowestDeltaXY || absDeltaXY < lowestDeltaXY)
            lowestDeltaXY = absDeltaXY;
        fn = delta > 0 ? "floor" : "ceil";
        delta = Math[fn](delta / lowestDelta);
        deltaX = Math[fn](deltaX / lowestDeltaXY);
        deltaY = Math[fn](deltaY / lowestDeltaXY);
        args.unshift(event, delta, deltaX, deltaY);
        return ($.event.dispatch || $.event.handle).apply(this, args);
    }
});

/**
Created: 20060120
Author:  Steve Moitozo <god at zilla dot us> -- geekwisdom.com
License: MIT License (see below)
Copyright (c) 2006 Steve Moitozo <god at zilla dot us>

Slightly modified for Peafowl

*/
function testPassword(e) {
    var t = 0,
        n = "weak",
        r = "",
        i = 0;
    if (e.length < 5) {
        t = t + 3;
        r = r + "3 points for length (" + e.length + ")\n";
    } else if (e.length > 4 && e.length < 8) {
        t = t + 6;
        r = r + "6 points for length (" + e.length + ")\n";
    } else if (e.length > 7 && e.length < 16) {
        t = t + 12;
        r = r + "12 points for length (" + e.length + ")\n";
    } else if (e.length > 15) {
        t = t + 18;
        r = r + "18 point for length (" + e.length + ")\n";
    }
    if (e.match(/[a-z]/)) {
        t = t + 1;
        r = r + "1 point for at least one lower case char\n";
    }
    if (e.match(/[A-Z]/)) {
        t = t + 5;
        r = r + "5 points for at least one upper case char\n";
    }
    if (e.match(/\d+/)) {
        t = t + 5;
        r = r + "5 points for at least one number\n";
    }
    if (e.match(/(.*[0-9].*[0-9].*[0-9])/)) {
        t = t + 5;
        r = r + "5 points for at least three numbers\n";
    }
    if (e.match(/.[!,@,#,$,%,^,&,*,?,_,~]/)) {
        t = t + 5;
        r = r + "5 points for at least one special char\n";
    }
    if (e.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)) {
        t = t + 5;
        r = r + "5 points for at least two special chars\n";
    }
    if (e.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) {
        t = t + 2;
        r = r + "2 combo points for upper and lower letters\n";
    }
    if (e.match(/([a-zA-Z])/) && e.match(/([0-9])/)) {
        t = t + 2;
        r = r + "2 combo points for letters and numbers\n";
    }
    if (
        e.match(
            /([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/
        )
    ) {
        t = t + 2;
        r = r + "2 combo points for letters, numbers and special chars\n";
    }
    if (e.length == 0) {
        t = 0;
    }
    if (t < 16) {
        n = "very weak";
    } else if (t > 15 && t < 25) {
        n = "weak";
    } else if (t > 24 && t < 35) {
        n = "average";
    } else if (t > 34 && t < 45) {
        n = "strong";
    } else {
        n = "stronger";
    }
    i = Math.round(Math.min(100, (100 * t) / 45)) / 100;
    return { score: t, ratio: i, percent: i * 100 + "%", verdict: n, log: r };
}

// SparkMD5
(function (factory) {
    if (typeof exports === "object") {
        module.exports = factory();
    } else if (typeof define === "function" && define.amd) {
        define(factory);
    } else {
        var glob;
        try {
            glob = window;
        } catch (e) {
            glob = self;
        }
        glob.SparkMD5 = factory();
    }
})(function (undefined) {
    "use strict";
    var add32 = function (a, b) {
        return (a + b) & 4294967295;
    },
        hex_chr = [
            "0",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "a",
            "b",
            "c",
            "d",
            "e",
            "f"
        ];
    function cmn(q, a, b, x, s, t) {
        a = add32(add32(a, q), add32(x, t));
        return add32((a << s) | (a >>> (32 - s)), b);
    }
    function ff(a, b, c, d, x, s, t) {
        return cmn((b & c) | (~b & d), a, b, x, s, t);
    }
    function gg(a, b, c, d, x, s, t) {
        return cmn((b & d) | (c & ~d), a, b, x, s, t);
    }
    function hh(a, b, c, d, x, s, t) {
        return cmn(b ^ c ^ d, a, b, x, s, t);
    }
    function ii(a, b, c, d, x, s, t) {
        return cmn(c ^ (b | ~d), a, b, x, s, t);
    }
    function md5cycle(x, k) {
        var a = x[0],
            b = x[1],
            c = x[2],
            d = x[3];
        a = ff(a, b, c, d, k[0], 7, -680876936);
        d = ff(d, a, b, c, k[1], 12, -389564586);
        c = ff(c, d, a, b, k[2], 17, 606105819);
        b = ff(b, c, d, a, k[3], 22, -1044525330);
        a = ff(a, b, c, d, k[4], 7, -176418897);
        d = ff(d, a, b, c, k[5], 12, 1200080426);
        c = ff(c, d, a, b, k[6], 17, -1473231341);
        b = ff(b, c, d, a, k[7], 22, -45705983);
        a = ff(a, b, c, d, k[8], 7, 1770035416);
        d = ff(d, a, b, c, k[9], 12, -1958414417);
        c = ff(c, d, a, b, k[10], 17, -42063);
        b = ff(b, c, d, a, k[11], 22, -1990404162);
        a = ff(a, b, c, d, k[12], 7, 1804603682);
        d = ff(d, a, b, c, k[13], 12, -40341101);
        c = ff(c, d, a, b, k[14], 17, -1502002290);
        b = ff(b, c, d, a, k[15], 22, 1236535329);
        a = gg(a, b, c, d, k[1], 5, -165796510);
        d = gg(d, a, b, c, k[6], 9, -1069501632);
        c = gg(c, d, a, b, k[11], 14, 643717713);
        b = gg(b, c, d, a, k[0], 20, -373897302);
        a = gg(a, b, c, d, k[5], 5, -701558691);
        d = gg(d, a, b, c, k[10], 9, 38016083);
        c = gg(c, d, a, b, k[15], 14, -660478335);
        b = gg(b, c, d, a, k[4], 20, -405537848);
        a = gg(a, b, c, d, k[9], 5, 568446438);
        d = gg(d, a, b, c, k[14], 9, -1019803690);
        c = gg(c, d, a, b, k[3], 14, -187363961);
        b = gg(b, c, d, a, k[8], 20, 1163531501);
        a = gg(a, b, c, d, k[13], 5, -1444681467);
        d = gg(d, a, b, c, k[2], 9, -51403784);
        c = gg(c, d, a, b, k[7], 14, 1735328473);
        b = gg(b, c, d, a, k[12], 20, -1926607734);
        a = hh(a, b, c, d, k[5], 4, -378558);
        d = hh(d, a, b, c, k[8], 11, -2022574463);
        c = hh(c, d, a, b, k[11], 16, 1839030562);
        b = hh(b, c, d, a, k[14], 23, -35309556);
        a = hh(a, b, c, d, k[1], 4, -1530992060);
        d = hh(d, a, b, c, k[4], 11, 1272893353);
        c = hh(c, d, a, b, k[7], 16, -155497632);
        b = hh(b, c, d, a, k[10], 23, -1094730640);
        a = hh(a, b, c, d, k[13], 4, 681279174);
        d = hh(d, a, b, c, k[0], 11, -358537222);
        c = hh(c, d, a, b, k[3], 16, -722521979);
        b = hh(b, c, d, a, k[6], 23, 76029189);
        a = hh(a, b, c, d, k[9], 4, -640364487);
        d = hh(d, a, b, c, k[12], 11, -421815835);
        c = hh(c, d, a, b, k[15], 16, 530742520);
        b = hh(b, c, d, a, k[2], 23, -995338651);
        a = ii(a, b, c, d, k[0], 6, -198630844);
        d = ii(d, a, b, c, k[7], 10, 1126891415);
        c = ii(c, d, a, b, k[14], 15, -1416354905);
        b = ii(b, c, d, a, k[5], 21, -57434055);
        a = ii(a, b, c, d, k[12], 6, 1700485571);
        d = ii(d, a, b, c, k[3], 10, -1894986606);
        c = ii(c, d, a, b, k[10], 15, -1051523);
        b = ii(b, c, d, a, k[1], 21, -2054922799);
        a = ii(a, b, c, d, k[8], 6, 1873313359);
        d = ii(d, a, b, c, k[15], 10, -30611744);
        c = ii(c, d, a, b, k[6], 15, -1560198380);
        b = ii(b, c, d, a, k[13], 21, 1309151649);
        a = ii(a, b, c, d, k[4], 6, -145523070);
        d = ii(d, a, b, c, k[11], 10, -1120210379);
        c = ii(c, d, a, b, k[2], 15, 718787259);
        b = ii(b, c, d, a, k[9], 21, -343485551);
        x[0] = add32(a, x[0]);
        x[1] = add32(b, x[1]);
        x[2] = add32(c, x[2]);
        x[3] = add32(d, x[3]);
    }
    function md5blk(s) {
        var md5blks = [],
            i;
        for (i = 0; i < 64; i += 4) {
            md5blks[i >> 2] =
                s.charCodeAt(i) +
                (s.charCodeAt(i + 1) << 8) +
                (s.charCodeAt(i + 2) << 16) +
                (s.charCodeAt(i + 3) << 24);
        }
        return md5blks;
    }
    function md5blk_array(a) {
        var md5blks = [],
            i;
        for (i = 0; i < 64; i += 4) {
            md5blks[i >> 2] =
                a[i] + (a[i + 1] << 8) + (a[i + 2] << 16) + (a[i + 3] << 24);
        }
        return md5blks;
    }
    function md51(s) {
        var n = s.length,
            state = [1732584193, -271733879, -1732584194, 271733878],
            i,
            length,
            tail,
            tmp,
            lo,
            hi;
        for (i = 64; i <= n; i += 64) {
            md5cycle(state, md5blk(s.substring(i - 64, i)));
        }
        s = s.substring(i - 64);
        length = s.length;
        tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        for (i = 0; i < length; i += 1) {
            tail[i >> 2] |= s.charCodeAt(i) << (i % 4 << 3);
        }
        tail[i >> 2] |= 128 << (i % 4 << 3);
        if (i > 55) {
            md5cycle(state, tail);
            for (i = 0; i < 16; i += 1) {
                tail[i] = 0;
            }
        }
        tmp = n * 8;
        tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
        lo = parseInt(tmp[2], 16);
        hi = parseInt(tmp[1], 16) || 0;
        tail[14] = lo;
        tail[15] = hi;
        md5cycle(state, tail);
        return state;
    }
    function md51_array(a) {
        var n = a.length,
            state = [1732584193, -271733879, -1732584194, 271733878],
            i,
            length,
            tail,
            tmp,
            lo,
            hi;
        for (i = 64; i <= n; i += 64) {
            md5cycle(state, md5blk_array(a.subarray(i - 64, i)));
        }
        a = i - 64 < n ? a.subarray(i - 64) : new Uint8Array(0);
        length = a.length;
        tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        for (i = 0; i < length; i += 1) {
            tail[i >> 2] |= a[i] << (i % 4 << 3);
        }
        tail[i >> 2] |= 128 << (i % 4 << 3);
        if (i > 55) {
            md5cycle(state, tail);
            for (i = 0; i < 16; i += 1) {
                tail[i] = 0;
            }
        }
        tmp = n * 8;
        tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
        lo = parseInt(tmp[2], 16);
        hi = parseInt(tmp[1], 16) || 0;
        tail[14] = lo;
        tail[15] = hi;
        md5cycle(state, tail);
        return state;
    }
    function rhex(n) {
        var s = "",
            j;
        for (j = 0; j < 4; j += 1) {
            s += hex_chr[(n >> (j * 8 + 4)) & 15] + hex_chr[(n >> (j * 8)) & 15];
        }
        return s;
    }
    function hex(x) {
        var i;
        for (i = 0; i < x.length; i += 1) {
            x[i] = rhex(x[i]);
        }
        return x.join("");
    }
    if (hex(md51("hello")) !== "5d41402abc4b2a76b9719d911017c592") {
        add32 = function (x, y) {
            var lsw = (x & 65535) + (y & 65535),
                msw = (x >> 16) + (y >> 16) + (lsw >> 16);
            return (msw << 16) | (lsw & 65535);
        };
    }
    function toUtf8(str) {
        if (/[\u0080-\uFFFF]/.test(str)) {
            str = unescape(encodeURIComponent(str));
        }
        return str;
    }
    function utf8Str2ArrayBuffer(str, returnUInt8Array) {
        var length = str.length,
            buff = new ArrayBuffer(length),
            arr = new Uint8Array(buff),
            i;
        for (i = 0; i < length; i++) {
            arr[i] = str.charCodeAt(i);
        }
        return returnUInt8Array ? arr : buff;
    }
    function arrayBuffer2Utf8Str(buff) {
        return String.fromCharCode.apply(null, new Uint8Array(buff));
    }
    function concatenateArrayBuffers(first, second, returnUInt8Array) {
        var result = new Uint8Array(first.byteLength + second.byteLength);
        result.set(new Uint8Array(first));
        result.set(new Uint8Array(second), first.byteLength);
        return returnUInt8Array ? result : result.buffer;
    }
    function SparkMD5() {
        this.reset();
    }
    SparkMD5.prototype.append = function (str) {
        this.appendBinary(toUtf8(str));
        return this;
    };
    SparkMD5.prototype.appendBinary = function (contents) {
        this._buff += contents;
        this._length += contents.length;
        var length = this._buff.length,
            i;
        for (i = 64; i <= length; i += 64) {
            md5cycle(this._hash, md5blk(this._buff.substring(i - 64, i)));
        }
        this._buff = this._buff.substring(i - 64);
        return this;
    };
    SparkMD5.prototype.end = function (raw) {
        var buff = this._buff,
            length = buff.length,
            i,
            tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            ret;
        for (i = 0; i < length; i += 1) {
            tail[i >> 2] |= buff.charCodeAt(i) << (i % 4 << 3);
        }
        this._finish(tail, length);
        ret = !!raw ? this._hash : hex(this._hash);
        this.reset();
        return ret;
    };
    SparkMD5.prototype.reset = function () {
        this._buff = "";
        this._length = 0;
        this._hash = [1732584193, -271733879, -1732584194, 271733878];
        return this;
    };
    SparkMD5.prototype.getState = function () {
        return { buff: this._buff, length: this._length, hash: this._hash };
    };
    SparkMD5.prototype.setState = function (state) {
        this._buff = state.buff;
        this._length = state.length;
        this._hash = state.hash;
        return this;
    };
    SparkMD5.prototype.destroy = function () {
        delete this._hash;
        delete this._buff;
        delete this._length;
    };
    SparkMD5.prototype._finish = function (tail, length) {
        var i = length,
            tmp,
            lo,
            hi;
        tail[i >> 2] |= 128 << (i % 4 << 3);
        if (i > 55) {
            md5cycle(this._hash, tail);
            for (i = 0; i < 16; i += 1) {
                tail[i] = 0;
            }
        }
        tmp = this._length * 8;
        tmp = tmp.toString(16).match(/(.*?)(.{0,8})$/);
        lo = parseInt(tmp[2], 16);
        hi = parseInt(tmp[1], 16) || 0;
        tail[14] = lo;
        tail[15] = hi;
        md5cycle(this._hash, tail);
    };
    SparkMD5.hash = function (str, raw) {
        return SparkMD5.hashBinary(toUtf8(str), raw);
    };
    SparkMD5.hashBinary = function (content, raw) {
        var hash = md51(content);
        return !!raw ? hash : hex(hash);
    };
    SparkMD5.ArrayBuffer = function () {
        this.reset();
    };
    SparkMD5.ArrayBuffer.prototype.append = function (arr) {
        var buff = concatenateArrayBuffers(this._buff.buffer, arr, true),
            length = buff.length,
            i;
        this._length += arr.byteLength;
        for (i = 64; i <= length; i += 64) {
            md5cycle(this._hash, md5blk_array(buff.subarray(i - 64, i)));
        }
        this._buff = i - 64 < length ? buff.subarray(i - 64) : new Uint8Array(0);
        return this;
    };
    SparkMD5.ArrayBuffer.prototype.end = function (raw) {
        var buff = this._buff,
            length = buff.length,
            tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            i,
            ret;
        for (i = 0; i < length; i += 1) {
            tail[i >> 2] |= buff[i] << (i % 4 << 3);
        }
        this._finish(tail, length);
        ret = !!raw ? this._hash : hex(this._hash);
        this.reset();
        return ret;
    };
    SparkMD5.ArrayBuffer.prototype.reset = function () {
        this._buff = new Uint8Array(0);
        this._length = 0;
        this._hash = [1732584193, -271733879, -1732584194, 271733878];
        return this;
    };
    SparkMD5.ArrayBuffer.prototype.getState = function () {
        var state = SparkMD5.prototype.getState.call(this);
        state.buff = arrayBuffer2Utf8Str(state.buff);
        return state;
    };
    SparkMD5.ArrayBuffer.prototype.setState = function (state) {
        state.buff = utf8Str2ArrayBuffer(state.buff, true);
        return SparkMD5.prototype.setState.call(this, state);
    };
    SparkMD5.ArrayBuffer.prototype.destroy = SparkMD5.prototype.destroy;
    SparkMD5.ArrayBuffer.prototype._finish = SparkMD5.prototype._finish;
    SparkMD5.ArrayBuffer.hash = function (arr, raw) {
        var hash = md51_array(new Uint8Array(arr));
        return !!raw ? hash : hex(hash);
    };
    return SparkMD5;
});

/*!
 * jQuery Color Animations v3.0.0
 * https://github.com/jquery/jquery-color
 *
 * Copyright OpenJS Foundation and other contributors
 * Released under the MIT license.
 * https://jquery.org/license
 *
 * Date: Wed May 15 16:49:44 2024 +0200
 */

( function( root, factory ) {
	"use strict";

	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "jquery" ], factory );
	} else if ( typeof exports === "object" ) {
		module.exports = factory( require( "jquery" ) );
	} else {
		factory( root.jQuery );
	}
} )( this, function( jQuery, undefined ) {
	"use strict";

	var stepHooks = "backgroundColor borderBottomColor borderLeftColor borderRightColor " +
		"borderTopColor color columnRuleColor outlineColor textDecorationColor textEmphasisColor",

	class2type = {},
	toString = class2type.toString,

	// plusequals test for += 100 -= 100
	rplusequals = /^([\-+])=\s*(\d+\.?\d*)/,

	// a set of RE's that can match strings and generate color tuples.
	stringParsers = [ {
			re: /rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
			parse: function( execResult ) {
				return [
					execResult[ 1 ],
					execResult[ 2 ],
					execResult[ 3 ],
					execResult[ 4 ]
				];
			}
		}, {
			re: /rgba?\(\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
			parse: function( execResult ) {
				return [
					execResult[ 1 ] * 2.55,
					execResult[ 2 ] * 2.55,
					execResult[ 3 ] * 2.55,
					execResult[ 4 ]
				];
			}
		}, {

			// this regex ignores A-F because it's compared against an already lowercased string
			re: /#([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})?/,
			parse: function( execResult ) {
				return [
					parseInt( execResult[ 1 ], 16 ),
					parseInt( execResult[ 2 ], 16 ),
					parseInt( execResult[ 3 ], 16 ),
					execResult[ 4 ] ?
						( parseInt( execResult[ 4 ], 16 ) / 255 ).toFixed( 2 ) :
						1
				];
			}
		}, {

			// this regex ignores A-F because it's compared against an already lowercased string
			re: /#([a-f0-9])([a-f0-9])([a-f0-9])([a-f0-9])?/,
			parse: function( execResult ) {
				return [
					parseInt( execResult[ 1 ] + execResult[ 1 ], 16 ),
					parseInt( execResult[ 2 ] + execResult[ 2 ], 16 ),
					parseInt( execResult[ 3 ] + execResult[ 3 ], 16 ),
					execResult[ 4 ] ?
						( parseInt( execResult[ 4 ] + execResult[ 4 ], 16 ) / 255 )
							.toFixed( 2 ) :
						1
				];
			}
		}, {
			re: /hsla?\(\s*(\d+(?:\.\d+)?)\s*,\s*(\d+(?:\.\d+)?)\%\s*,\s*(\d+(?:\.\d+)?)\%\s*(?:,\s*(\d?(?:\.\d+)?)\s*)?\)/,
			space: "hsla",
			parse: function( execResult ) {
				return [
					execResult[ 1 ],
					execResult[ 2 ] / 100,
					execResult[ 3 ] / 100,
					execResult[ 4 ]
				];
			}
		} ],

	// jQuery.Color( )
	color = jQuery.Color = function( color, green, blue, alpha ) {
		return new jQuery.Color.fn.parse( color, green, blue, alpha );
	},
	spaces = {
		rgba: {
			props: {
				red: {
					idx: 0,
					type: "byte"
				},
				green: {
					idx: 1,
					type: "byte"
				},
				blue: {
					idx: 2,
					type: "byte"
				}
			}
		},

		hsla: {
			props: {
				hue: {
					idx: 0,
					type: "degrees"
				},
				saturation: {
					idx: 1,
					type: "percent"
				},
				lightness: {
					idx: 2,
					type: "percent"
				}
			}
		}
	},
	propTypes = {
		"byte": {
			floor: true,
			max: 255
		},
		"percent": {
			max: 1
		},
		"degrees": {
			mod: 360,
			floor: true
		}
	},

	// colors = jQuery.Color.names
	colors,

	// local aliases of functions called often
	each = jQuery.each;

// define cache name and alpha properties
// for rgba and hsla spaces
each( spaces, function( spaceName, space ) {
	space.cache = "_" + spaceName;
	space.props.alpha = {
		idx: 3,
		type: "percent",
		def: 1
	};
} );

// Populate the class2type map
jQuery.each( "Boolean Number String Function Array Date RegExp Object Error Symbol".split( " " ),
	function( _i, name ) {
		class2type[ "[object " + name + "]" ] = name.toLowerCase();
	} );

function getType( obj ) {
	if ( obj == null ) {
		return obj + "";
	}

	return typeof obj === "object" ?
		class2type[ toString.call( obj ) ] || "object" :
		typeof obj;
}

function clamp( value, prop, allowEmpty ) {
	var type = propTypes[ prop.type ] || {};

	if ( value == null ) {
		return ( allowEmpty || !prop.def ) ? null : prop.def;
	}

	// ~~ is an short way of doing floor for positive numbers
	value = type.floor ? ~~value : parseFloat( value );

	if ( type.mod ) {

		// we add mod before modding to make sure that negatives values
		// get converted properly: -10 -> 350
		return ( value + type.mod ) % type.mod;
	}

	// for now all property types without mod have min and max
	return Math.min( type.max, Math.max( 0, value ) );
}

function stringParse( string ) {
	var inst = color(),
		rgba = inst._rgba = [];

	string = string.toLowerCase();

	each( stringParsers, function( _i, parser ) {
		var parsed,
			match = parser.re.exec( string ),
			values = match && parser.parse( match ),
			spaceName = parser.space || "rgba";

		if ( values ) {
			parsed = inst[ spaceName ]( values );

			// if this was an rgba parse the assignment might happen twice
			// oh well....
			inst[ spaces[ spaceName ].cache ] = parsed[ spaces[ spaceName ].cache ];
			rgba = inst._rgba = parsed._rgba;

			// exit each( stringParsers ) here because we matched
			return false;
		}
	} );

	// Found a stringParser that handled it
	if ( rgba.length ) {

		// if this came from a parsed string, force "transparent" when alpha is 0
		// chrome, (and maybe others) return "transparent" as rgba(0,0,0,0)
		if ( rgba.join() === "0,0,0,0" ) {
			jQuery.extend( rgba, colors.transparent );
		}
		return inst;
	}

	return colors[ string ];
}

color.fn = jQuery.extend( color.prototype, {
	parse: function( red, green, blue, alpha ) {
		if ( red === undefined ) {
			this._rgba = [ null, null, null, null ];
			return this;
		}
		if ( red.jquery || red.nodeType ) {
			red = jQuery( red ).css( green );
			green = undefined;
		}

		var inst = this,
			type = getType( red ),
			rgba = this._rgba = [];

		// more than 1 argument specified - assume ( red, green, blue, alpha )
		if ( green !== undefined ) {
			red = [ red, green, blue, alpha ];
			type = "array";
		}

		if ( type === "string" ) {
			return this.parse( stringParse( red ) || colors._default );
		}

		if ( type === "array" ) {
			each( spaces.rgba.props, function( _key, prop ) {
				rgba[ prop.idx ] = clamp( red[ prop.idx ], prop );
			} );
			return this;
		}

		if ( type === "object" ) {
			if ( red instanceof color ) {
				each( spaces, function( _spaceName, space ) {
					if ( red[ space.cache ] ) {
						inst[ space.cache ] = red[ space.cache ].slice();
					}
				} );
			} else {
				each( spaces, function( _spaceName, space ) {
					var cache = space.cache;
					each( space.props, function( key, prop ) {

						// if the cache doesn't exist, and we know how to convert
						if ( !inst[ cache ] && space.to ) {

							// if the value was null, we don't need to copy it
							// if the key was alpha, we don't need to copy it either
							if ( key === "alpha" || red[ key ] == null ) {
								return;
							}
							inst[ cache ] = space.to( inst._rgba );
						}

						// this is the only case where we allow nulls for ALL properties.
						// call clamp with alwaysAllowEmpty
						inst[ cache ][ prop.idx ] = clamp( red[ key ], prop, true );
					} );

					// everything defined but alpha?
					if ( inst[ cache ] && jQuery.inArray(
						null,
						inst[ cache ].slice( 0, 3 )
					) < 0 ) {

						// use the default of 1
						if ( inst[ cache ][ 3 ] == null ) {
							inst[ cache ][ 3 ] = 1;
						}

						if ( space.from ) {
							inst._rgba = space.from( inst[ cache ] );
						}
					}
				} );
			}
			return this;
		}
	},
	is: function( compare ) {
		var is = color( compare ),
			same = true,
			inst = this;

		each( spaces, function( _, space ) {
			var localCache,
				isCache = is[ space.cache ];
			if ( isCache ) {
				localCache = inst[ space.cache ] || space.to && space.to( inst._rgba ) || [];
				each( space.props, function( _, prop ) {
					if ( isCache[ prop.idx ] != null ) {
						same = ( isCache[ prop.idx ] === localCache[ prop.idx ] );
						return same;
					}
				} );
			}
			return same;
		} );
		return same;
	},
	_space: function() {
		var used = [],
			inst = this;
		each( spaces, function( spaceName, space ) {
			if ( inst[ space.cache ] ) {
				used.push( spaceName );
			}
		} );
		return used.pop();
	},
	transition: function( other, distance ) {
		var end = color( other ),
			spaceName = end._space(),
			space = spaces[ spaceName ],
			startColor = this.alpha() === 0 ? color( "transparent" ) : this,
			start = startColor[ space.cache ] || space.to( startColor._rgba ),
			result = start.slice();

		end = end[ space.cache ];
		each( space.props, function( _key, prop ) {
			var index = prop.idx,
				startValue = start[ index ],
				endValue = end[ index ],
				type = propTypes[ prop.type ] || {};

			// if null, don't override start value
			if ( endValue === null ) {
				return;
			}

			// if null - use end
			if ( startValue === null ) {
				result[ index ] = endValue;
			} else {
				if ( type.mod ) {
					if ( endValue - startValue > type.mod / 2 ) {
						startValue += type.mod;
					} else if ( startValue - endValue > type.mod / 2 ) {
						startValue -= type.mod;
					}
				}
				result[ index ] = clamp( ( endValue - startValue ) * distance + startValue, prop );
			}
		} );
		return this[ spaceName ]( result );
	},
	blend: function( opaque ) {

		// if we are already opaque - return ourself
		if ( this._rgba[ 3 ] === 1 ) {
			return this;
		}

		var rgb = this._rgba.slice(),
			a = rgb.pop(),
			blend = color( opaque )._rgba;

		return color( jQuery.map( rgb, function( v, i ) {
			return ( 1 - a ) * blend[ i ] + a * v;
		} ) );
	},
	toRgbaString: function() {
		var prefix = "rgba(",
			rgba = jQuery.map( this._rgba, function( v, i ) {
				if ( v != null ) {
					return v;
				}
				return i > 2 ? 1 : 0;
			} );

		if ( rgba[ 3 ] === 1 ) {
			rgba.pop();
			prefix = "rgb(";
		}

		return prefix + rgba.join( ", " ) + ")";
	},
	toHslaString: function() {
		var prefix = "hsla(",
			hsla = jQuery.map( this.hsla(), function( v, i ) {
				if ( v == null ) {
					v = i > 2 ? 1 : 0;
				}

				// catch 1 and 2
				if ( i && i < 3 ) {
					v = Math.round( v * 100 ) + "%";
				}
				return v;
			} );

		if ( hsla[ 3 ] === 1 ) {
			hsla.pop();
			prefix = "hsl(";
		}
		return prefix + hsla.join( ", " ) + ")";
	},
	toHexString: function( includeAlpha ) {
		var rgba = this._rgba.slice(),
			alpha = rgba.pop();

		if ( includeAlpha ) {
			rgba.push( ~~( alpha * 255 ) );
		}

		return "#" + jQuery.map( rgba, function( v ) {

			// default to 0 when nulls exist
			return ( "0" + ( v || 0 ).toString( 16 ) ).substr( -2 );
		} ).join( "" );
	},
	toString: function() {
		return this.toRgbaString();
	}
} );
color.fn.parse.prototype = color.fn;

// hsla conversions adapted from:
// https://code.google.com/p/maashaack/source/browse/packages/graphics/trunk/src/graphics/colors/HUE2RGB.as?r=5021

function hue2rgb( p, q, h ) {
	h = ( h + 1 ) % 1;
	if ( h * 6 < 1 ) {
		return p + ( q - p ) * h * 6;
	}
	if ( h * 2 < 1 ) {
		return q;
	}
	if ( h * 3 < 2 ) {
		return p + ( q - p ) * ( ( 2 / 3 ) - h ) * 6;
	}
	return p;
}

spaces.hsla.to = function( rgba ) {
	if ( rgba[ 0 ] == null || rgba[ 1 ] == null || rgba[ 2 ] == null ) {
		return [ null, null, null, rgba[ 3 ] ];
	}
	var r = rgba[ 0 ] / 255,
		g = rgba[ 1 ] / 255,
		b = rgba[ 2 ] / 255,
		a = rgba[ 3 ],
		max = Math.max( r, g, b ),
		min = Math.min( r, g, b ),
		diff = max - min,
		add = max + min,
		l = add * 0.5,
		h, s;

	if ( min === max ) {
		h = 0;
	} else if ( r === max ) {
		h = ( 60 * ( g - b ) / diff ) + 360;
	} else if ( g === max ) {
		h = ( 60 * ( b - r ) / diff ) + 120;
	} else {
		h = ( 60 * ( r - g ) / diff ) + 240;
	}

	// chroma (diff) == 0 means greyscale which, by definition, saturation = 0%
	// otherwise, saturation is based on the ratio of chroma (diff) to lightness (add)
	if ( diff === 0 ) {
		s = 0;
	} else if ( l <= 0.5 ) {
		s = diff / add;
	} else {
		s = diff / ( 2 - add );
	}
	return [ Math.round( h ) % 360, s, l, a == null ? 1 : a ];
};

spaces.hsla.from = function( hsla ) {
	if ( hsla[ 0 ] == null || hsla[ 1 ] == null || hsla[ 2 ] == null ) {
		return [ null, null, null, hsla[ 3 ] ];
	}
	var h = hsla[ 0 ] / 360,
		s = hsla[ 1 ],
		l = hsla[ 2 ],
		a = hsla[ 3 ],
		q = l <= 0.5 ? l * ( 1 + s ) : l + s - l * s,
		p = 2 * l - q;

	return [
		Math.round( hue2rgb( p, q, h + ( 1 / 3 ) ) * 255 ),
		Math.round( hue2rgb( p, q, h ) * 255 ),
		Math.round( hue2rgb( p, q, h - ( 1 / 3 ) ) * 255 ),
		a
	];
};


each( spaces, function( spaceName, space ) {
	var props = space.props,
		cache = space.cache,
		to = space.to,
		from = space.from;

	// makes rgba() and hsla()
	color.fn[ spaceName ] = function( value ) {

		// generate a cache for this space if it doesn't exist
		if ( to && !this[ cache ] ) {
			this[ cache ] = to( this._rgba );
		}
		if ( value === undefined ) {
			return this[ cache ].slice();
		}

		var ret,
			type = getType( value ),
			arr = ( type === "array" || type === "object" ) ? value : arguments,
			local = this[ cache ].slice();

		each( props, function( key, prop ) {
			var val = arr[ type === "object" ? key : prop.idx ];
			if ( val == null ) {
				val = local[ prop.idx ];
			}
			local[ prop.idx ] = clamp( val, prop );
		} );

		if ( from ) {
			ret = color( from( local ) );
			ret[ cache ] = local;
			return ret;
		} else {
			return color( local );
		}
	};

	// makes red() green() blue() alpha() hue() saturation() lightness()
	each( props, function( key, prop ) {

		// alpha is included in more than one space
		if ( color.fn[ key ] ) {
			return;
		}
		color.fn[ key ] = function( value ) {
			var local, cur, match, fn,
				vtype = getType( value );

			if ( key === "alpha" ) {
				fn = this._hsla ? "hsla" : "rgba";
			} else {
				fn = spaceName;
			}
			local = this[ fn ]();
			cur = local[ prop.idx ];

			if ( vtype === "undefined" ) {
				return cur;
			}

			if ( vtype === "function" ) {
				value = value.call( this, cur );
				vtype = getType( value );
			}
			if ( value == null && prop.empty ) {
				return this;
			}
			if ( vtype === "string" ) {
				match = rplusequals.exec( value );
				if ( match ) {
					value = cur + parseFloat( match[ 2 ] ) * ( match[ 1 ] === "+" ? 1 : -1 );
				}
			}
			local[ prop.idx ] = value;
			return this[ fn ]( local );
		};
	} );
} );

// add cssHook and .fx.step function for each named hook.
// accept a space separated string of properties
color.hook = function( hook ) {
	var hooks = hook.split( " " );
	each( hooks, function( _i, hook ) {
		jQuery.cssHooks[ hook ] = {
			set: function( elem, value ) {
				var parsed;

				if ( value !== "transparent" &&
					( getType( value ) !== "string" ||
						( parsed = stringParse( value ) ) ) ) {
					value = color( parsed || value );
					value = value.toRgbaString();
				}
				elem.style[ hook ] = value;
			}
		};
		jQuery.fx.step[ hook ] = function( fx ) {
			if ( !fx.colorInit ) {
				fx.start = color( fx.elem, hook );
				fx.end = color( fx.end );
				fx.colorInit = true;
			}
			jQuery.cssHooks[ hook ].set( fx.elem, fx.start.transition( fx.end, fx.pos ) );
		};
	} );

};

color.hook( stepHooks );

jQuery.cssHooks.borderColor = {
	expand: function( value ) {
		var expanded = {};

		each( [ "Top", "Right", "Bottom", "Left" ], function( _i, part ) {
			expanded[ "border" + part + "Color" ] = value;
		} );
		return expanded;
	}
};

// Basic color names only.
// Usage of any of the other color names requires adding yourself or including
// jquery.color.svg-names.js.
colors = jQuery.Color.names = {

	// 4.1. Basic color keywords
	aqua: "#00ffff",
	black: "#000000",
	blue: "#0000ff",
	fuchsia: "#ff00ff",
	gray: "#808080",
	green: "#008000",
	lime: "#00ff00",
	maroon: "#800000",
	navy: "#000080",
	olive: "#808000",
	purple: "#800080",
	red: "#ff0000",
	silver: "#c0c0c0",
	teal: "#008080",
	white: "#ffffff",
	yellow: "#ffff00",

	// 4.2.3. "transparent" color keyword
	transparent: [ null, null, null, 0 ],

	_default: "#ffffff"
};

} );

/*!
 * imagesLoaded PACKAGED v4.1.4
 * JavaScript is all like "You images are done yet or what?"
 * MIT License
 */

/**
 * EvEmitter v1.1.0
 * Lil' event emitter
 * MIT License
 */

/* jshint unused: true, undef: true, strict: true */

( function( global, factory ) {
    // universal module definition
    /* jshint strict: false */ /* globals define, module, window */
    if ( typeof define == 'function' && define.amd ) {
      // AMD - RequireJS
      define( 'ev-emitter/ev-emitter',factory );
    } else if ( typeof module == 'object' && module.exports ) {
      // CommonJS - Browserify, Webpack
      module.exports = factory();
    } else {
      // Browser globals
      global.EvEmitter = factory();
    }

  }( typeof window != 'undefined' ? window : this, function() {



  function EvEmitter() {}

  var proto = EvEmitter.prototype;

  proto.on = function( eventName, listener ) {
    if ( !eventName || !listener ) {
      return;
    }
    // set events hash
    var events = this._events = this._events || {};
    // set listeners array
    var listeners = events[ eventName ] = events[ eventName ] || [];
    // only add once
    if ( listeners.indexOf( listener ) == -1 ) {
      listeners.push( listener );
    }

    return this;
  };

  proto.once = function( eventName, listener ) {
    if ( !eventName || !listener ) {
      return;
    }
    // add event
    this.on( eventName, listener );
    // set once flag
    // set onceEvents hash
    var onceEvents = this._onceEvents = this._onceEvents || {};
    // set onceListeners object
    var onceListeners = onceEvents[ eventName ] = onceEvents[ eventName ] || {};
    // set flag
    onceListeners[ listener ] = true;

    return this;
  };

  proto.off = function( eventName, listener ) {
    var listeners = this._events && this._events[ eventName ];
    if ( !listeners || !listeners.length ) {
      return;
    }
    var index = listeners.indexOf( listener );
    if ( index != -1 ) {
      listeners.splice( index, 1 );
    }

    return this;
  };

  proto.emitEvent = function( eventName, args ) {
    var listeners = this._events && this._events[ eventName ];
    if ( !listeners || !listeners.length ) {
      return;
    }
    // copy over to avoid interference if .off() in listener
    listeners = listeners.slice(0);
    args = args || [];
    // once stuff
    var onceListeners = this._onceEvents && this._onceEvents[ eventName ];

    for ( var i=0; i < listeners.length; i++ ) {
      var listener = listeners[i]
      var isOnce = onceListeners && onceListeners[ listener ];
      if ( isOnce ) {
        // remove listener
        // remove before trigger to prevent recursion
        this.off( eventName, listener );
        // unset once flag
        delete onceListeners[ listener ];
      }
      // trigger listener
      listener.apply( this, args );
    }

    return this;
  };

  proto.allOff = function() {
    delete this._events;
    delete this._onceEvents;
  };

  return EvEmitter;

  }));

  /*!
   * imagesLoaded v4.1.4
   * JavaScript is all like "You images are done yet or what?"
   * MIT License
   */

  ( function( window, factory ) { 'use strict';
    // universal module definition

    /*global define: false, module: false, require: false */

    if ( typeof define == 'function' && define.amd ) {
      // AMD
      define( [
        'ev-emitter/ev-emitter'
      ], function( EvEmitter ) {
        return factory( window, EvEmitter );
      });
    } else if ( typeof module == 'object' && module.exports ) {
      // CommonJS
      module.exports = factory(
        window,
        require('ev-emitter')
      );
    } else {
      // browser global
      window.imagesLoaded = factory(
        window,
        window.EvEmitter
      );
    }

  })( typeof window !== 'undefined' ? window : this,

  // --------------------------  factory -------------------------- //

  function factory( window, EvEmitter ) {



  var $ = window.jQuery;
  var console = window.console;

  // -------------------------- helpers -------------------------- //

  // extend objects
  function extend( a, b ) {
    for ( var prop in b ) {
      a[ prop ] = b[ prop ];
    }
    return a;
  }

  var arraySlice = Array.prototype.slice;

  // turn element or nodeList into an array
  function makeArray( obj ) {
    if ( Array.isArray( obj ) ) {
      // use object if already an array
      return obj;
    }

    var isArrayLike = typeof obj == 'object' && typeof obj.length == 'number';
    if ( isArrayLike ) {
      // convert nodeList to array
      return arraySlice.call( obj );
    }

    // array of single index
    return [ obj ];
  }

  // -------------------------- imagesLoaded -------------------------- //

  /**
   * @param {Array, Element, NodeList, String} elem
   * @param {Object or Function} options - if function, use as callback
   * @param {Function} onAlways - callback function
   */
  function ImagesLoaded( elem, options, onAlways ) {
    // coerce ImagesLoaded() without new, to be new ImagesLoaded()
    if ( !( this instanceof ImagesLoaded ) ) {
      return new ImagesLoaded( elem, options, onAlways );
    }
    // use elem as selector string
    var queryElem = elem;
    if ( typeof elem == 'string' ) {
      queryElem = document.querySelectorAll( elem );
    }
    // bail if bad element
    if ( !queryElem ) {
      console.error( 'Bad element for imagesLoaded ' + ( queryElem || elem ) );
      return;
    }

    this.elements = makeArray( queryElem );
    this.options = extend( {}, this.options );
    // shift arguments if no options set
    if ( typeof options == 'function' ) {
      onAlways = options;
    } else {
      extend( this.options, options );
    }

    if ( onAlways ) {
      this.on( 'always', onAlways );
    }

    this.getImages();

    if ( $ ) {
      // add jQuery Deferred object
      this.jqDeferred = new $.Deferred();
    }

    // HACK check async to allow time to bind listeners
    setTimeout( this.check.bind( this ) );
  }

  ImagesLoaded.prototype = Object.create( EvEmitter.prototype );

  ImagesLoaded.prototype.options = {};

  ImagesLoaded.prototype.getImages = function() {
    this.images = [];

    // filter & find items if we have an item selector
    this.elements.forEach( this.addElementImages, this );
  };

  /**
   * @param {Node} element
   */
  ImagesLoaded.prototype.addElementImages = function( elem ) {
    // filter siblings
    if ( elem.nodeName == 'IMG' ) {
      this.addImage( elem );
    }
    // get background image on element
    if ( this.options.background === true ) {
      this.addElementBackgroundImages( elem );
    }

    // find children
    // no non-element nodes, #143
    var nodeType = elem.nodeType;
    if ( !nodeType || !elementNodeTypes[ nodeType ] ) {
      return;
    }
    var childImgs = elem.querySelectorAll('img');
    // concat childElems to filterFound array
    for ( var i=0; i < childImgs.length; i++ ) {
      var img = childImgs[i];
      this.addImage( img );
    }

    // get child background images
    if ( typeof this.options.background == 'string' ) {
      var children = elem.querySelectorAll( this.options.background );
      for ( i=0; i < children.length; i++ ) {
        var child = children[i];
        this.addElementBackgroundImages( child );
      }
    }
  };

  var elementNodeTypes = {
    1: true,
    9: true,
    11: true
  };

  ImagesLoaded.prototype.addElementBackgroundImages = function( elem ) {
    var style = getComputedStyle( elem );
    if ( !style ) {
      // Firefox returns null if in a hidden iframe https://bugzil.la/548397
      return;
    }
    // get url inside url("...")
    var reURL = /url\((['"])?(.*?)\1\)/gi;
    var matches = reURL.exec( style.backgroundImage );
    while ( matches !== null ) {
      var url = matches && matches[2];
      if ( url ) {
        this.addBackground( url, elem );
      }
      matches = reURL.exec( style.backgroundImage );
    }
  };

  /**
   * @param {Image} img
   */
  ImagesLoaded.prototype.addImage = function( img ) {
    var loadingImage = new LoadingImage( img );
    this.images.push( loadingImage );
  };

  ImagesLoaded.prototype.addBackground = function( url, elem ) {
    var background = new Background( url, elem );
    this.images.push( background );
  };

  ImagesLoaded.prototype.check = function() {
    var _this = this;
    this.progressedCount = 0;
    this.hasAnyBroken = false;
    // complete if no images
    if ( !this.images.length ) {
      this.complete();
      return;
    }

    function onProgress( image, elem, message ) {
      // HACK - Chrome triggers event before object properties have changed. #83
      setTimeout( function() {
        _this.progress( image, elem, message );
      });
    }

    this.images.forEach( function( loadingImage ) {
      loadingImage.once( 'progress', onProgress );
      loadingImage.check();
    });
  };

  ImagesLoaded.prototype.progress = function( image, elem, message ) {
    this.progressedCount++;
    this.hasAnyBroken = this.hasAnyBroken || !image.isLoaded;
    // progress event
    this.emitEvent( 'progress', [ this, image, elem ] );
    if ( this.jqDeferred && this.jqDeferred.notify ) {
      this.jqDeferred.notify( this, image );
    }
    // check if completed
    if ( this.progressedCount == this.images.length ) {
      this.complete();
    }

    if ( this.options.debug && console ) {
      console.log( 'progress: ' + message, image, elem );
    }
  };

  ImagesLoaded.prototype.complete = function() {
    var eventName = this.hasAnyBroken ? 'fail' : 'done';
    this.isComplete = true;
    this.emitEvent( eventName, [ this ] );
    this.emitEvent( 'always', [ this ] );
    if ( this.jqDeferred ) {
      var jqMethod = this.hasAnyBroken ? 'reject' : 'resolve';
      this.jqDeferred[ jqMethod ]( this );
    }
  };

  function LoadingImage( img ) {
    this.img = img;
  }

  LoadingImage.prototype = Object.create( EvEmitter.prototype );

  LoadingImage.prototype.check = function() {
    // If complete is true and browser supports natural sizes,
    // try to check for image status manually.
    var isComplete = this.getIsImageComplete();
    if ( isComplete ) {
      // report based on naturalWidth
      this.confirm( this.img.naturalWidth !== 0, 'naturalWidth' );
      return;
    }

    // If none of the checks above matched, simulate loading on detached element.
    this.proxyImage = new Image();
    this.proxyImage.addEventListener( 'load', this );
    this.proxyImage.addEventListener( 'error', this );
    // bind to image as well for Firefox. #191
    this.img.addEventListener( 'load', this );
    this.img.addEventListener( 'error', this );
    this.proxyImage.src = this.img.src;
  };

  LoadingImage.prototype.getIsImageComplete = function() {
    // check for non-zero, non-undefined naturalWidth
    // fixes Safari+InfiniteScroll+Masonry bug infinite-scroll#671
    return this.img.complete && this.img.naturalWidth;
  };

  LoadingImage.prototype.confirm = function( isLoaded, message ) {
    this.isLoaded = isLoaded;
    this.emitEvent( 'progress', [ this, this.img, message ] );
  };

  // ----- events ----- //

  // trigger specified handler for event type
  LoadingImage.prototype.handleEvent = function( event ) {
    var method = 'on' + event.type;
    if ( this[ method ] ) {
      this[ method ]( event );
    }
  };

  LoadingImage.prototype.onload = function() {
    this.confirm( true, 'onload' );
    this.unbindEvents();
  };

  LoadingImage.prototype.onerror = function() {
    this.confirm( false, 'onerror' );
    this.unbindEvents();
  };

  LoadingImage.prototype.unbindEvents = function() {
    this.proxyImage.removeEventListener( 'load', this );
    this.proxyImage.removeEventListener( 'error', this );
    this.img.removeEventListener( 'load', this );
    this.img.removeEventListener( 'error', this );
  };

  // -------------------------- Background -------------------------- //

  function Background( url, element ) {
    this.url = url;
    this.element = element;
    this.img = new Image();
  }

  // inherit LoadingImage prototype
  Background.prototype = Object.create( LoadingImage.prototype );

  Background.prototype.check = function() {
    this.img.addEventListener( 'load', this );
    this.img.addEventListener( 'error', this );
    this.img.src = this.url;
    // check if image is already complete
    var isComplete = this.getIsImageComplete();
    if ( isComplete ) {
      this.confirm( this.img.naturalWidth !== 0, 'naturalWidth' );
      this.unbindEvents();
    }
  };

  Background.prototype.unbindEvents = function() {
    this.img.removeEventListener( 'load', this );
    this.img.removeEventListener( 'error', this );
  };

  Background.prototype.confirm = function( isLoaded, message ) {
    this.isLoaded = isLoaded;
    this.emitEvent( 'progress', [ this, this.element, message ] );
  };

  // -------------------------- jQuery -------------------------- //

  ImagesLoaded.makeJQueryPlugin = function( jQuery ) {
    jQuery = jQuery || window.jQuery;
    if ( !jQuery ) {
      return;
    }
    // set local variable
    $ = jQuery;
    // $().imagesLoaded()
    $.fn.imagesLoaded = function( options, callback ) {
      var instance = new ImagesLoaded( this, options, callback );
      return instance.jqDeferred.promise( $(this) );
    };
  };
  // try making plugin
  ImagesLoaded.makeJQueryPlugin();

  // --------------------------  -------------------------- //

  return ImagesLoaded;

  });

/*
 * JavaScript Load Image
 * https://github.com/blueimp/JavaScript-Load-Image
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, module, Promise */

;(function ($) {
    'use strict'

    var urlAPI = $.URL || $.webkitURL

    /**
     * Creates an object URL for a given File object.
     *
     * @param {Blob} blob Blob object
     * @returns {string|boolean} Returns object URL if API exists, else false.
     */
    function createObjectURL(blob) {
      return urlAPI ? urlAPI.createObjectURL(blob) : false
    }

    /**
     * Revokes a given object URL.
     *
     * @param {string} url Blob object URL
     * @returns {undefined|boolean} Returns undefined if API exists, else false.
     */
    function revokeObjectURL(url) {
      return urlAPI ? urlAPI.revokeObjectURL(url) : false
    }

    /**
     * Helper function to revoke an object URL
     *
     * @param {string} url Blob Object URL
     * @param {object} [options] Options object
     */
    function revokeHelper(url, options) {
      if (url && url.slice(0, 5) === 'blob:' && !(options && options.noRevoke)) {
        revokeObjectURL(url)
      }
    }

    /**
     * Loads a given File object via FileReader interface.
     *
     * @param {Blob} file Blob object
     * @param {Function} onload Load event callback
     * @param {Function} [onerror] Error/Abort event callback
     * @param {string} [method=readAsDataURL] FileReader method
     * @returns {FileReader|boolean} Returns FileReader if API exists, else false.
     */
    function readFile(file, onload, onerror, method) {
      if (!$.FileReader) return false
      var reader = new FileReader()
      reader.onload = function () {
        onload.call(reader, this.result)
      }
      if (onerror) {
        reader.onabort = reader.onerror = function () {
          onerror.call(reader, this.error)
        }
      }
      var readerMethod = reader[method || 'readAsDataURL']
      if (readerMethod) {
        readerMethod.call(reader, file)
        return reader
      }
    }

    /**
     * Cross-frame instanceof check.
     *
     * @param {string} type Instance type
     * @param {object} obj Object instance
     * @returns {boolean} Returns true if the object is of the given instance.
     */
    function isInstanceOf(type, obj) {
      // Cross-frame instanceof check
      return Object.prototype.toString.call(obj) === '[object ' + type + ']'
    }

    /**
     * @typedef { HTMLImageElement|HTMLCanvasElement } Result
     */

    /**
     * Loads an image for a given File object.
     *
     * @param {Blob|string} file Blob object or image URL
     * @param {Function|object} [callback] Image load event callback or options
     * @param {object} [options] Options object
     * @returns {HTMLImageElement|FileReader|Promise<Result>} Object
     */
    function loadImage(file, callback, options) {
      /**
       * Promise executor
       *
       * @param {Function} resolve Resolution function
       * @param {Function} reject Rejection function
       * @returns {HTMLImageElement|FileReader} Object
       */
      function executor(resolve, reject) {
        var img = document.createElement('img')
        var url
        /**
         * Callback for the fetchBlob call.
         *
         * @param {HTMLImageElement|HTMLCanvasElement} img Error object
         * @param {object} data Data object
         * @returns {undefined} Undefined
         */
        function resolveWrapper(img, data) {
          if (resolve === reject) {
            // Not using Promises
            if (resolve) resolve(img, data)
            return
          } else if (img instanceof Error) {
            reject(img)
            return
          }
          data = data || {} // eslint-disable-line no-param-reassign
          data.image = img
          resolve(data)
        }
        /**
         * Callback for the fetchBlob call.
         *
         * @param {Blob} blob Blob object
         * @param {Error} err Error object
         */
        function fetchBlobCallback(blob, err) {
          if (err && $.console) console.log(err) // eslint-disable-line no-console
          if (blob && isInstanceOf('Blob', blob)) {
            file = blob // eslint-disable-line no-param-reassign
            url = createObjectURL(file)
          } else {
            url = file
            if (options && options.crossOrigin) {
              img.crossOrigin = options.crossOrigin
            }
          }
          img.src = url
        }
        img.onerror = function (event) {
          revokeHelper(url, options)
          if (reject) reject.call(img, event)
        }
        img.onload = function () {
          revokeHelper(url, options)
          var data = {
            originalWidth: img.naturalWidth || img.width,
            originalHeight: img.naturalHeight || img.height
          }
          try {
            loadImage.transform(img, options, resolveWrapper, file, data)
          } catch (error) {
            if (reject) reject(error)
          }
        }
        if (typeof file === 'string') {
          if (loadImage.requiresMetaData(options)) {
            loadImage.fetchBlob(file, fetchBlobCallback, options)
          } else {
            fetchBlobCallback()
          }
          return img
        } else if (isInstanceOf('Blob', file) || isInstanceOf('File', file)) {
          url = createObjectURL(file)
          if (url) {
            img.src = url
            return img
          }
          return readFile(
            file,
            function (url) {
              img.src = url
            },
            reject
          )
        }
      }
      if ($.Promise && typeof callback !== 'function') {
        options = callback // eslint-disable-line no-param-reassign
        return new Promise(executor)
      }
      return executor(callback, callback)
    }

    // Determines if metadata should be loaded automatically.
    // Requires the load image meta extension to load metadata.
    loadImage.requiresMetaData = function (options) {
      return options && options.meta
    }

    // If the callback given to this function returns a blob, it is used as image
    // source instead of the original url and overrides the file argument used in
    // the onload and onerror event callbacks:
    loadImage.fetchBlob = function (url, callback) {
      callback()
    }

    loadImage.transform = function (img, options, callback, file, data) {
      callback(img, data)
    }

    loadImage.global = $
    loadImage.readFile = readFile
    loadImage.isInstanceOf = isInstanceOf
    loadImage.createObjectURL = createObjectURL
    loadImage.revokeObjectURL = revokeObjectURL

    if (typeof define === 'function' && define.amd) {
      define(function () {
        return loadImage
      })
    } else if (typeof module === 'object' && module.exports) {
      module.exports = loadImage
    } else {
      $.loadImage = loadImage
    }
  })((typeof window !== 'undefined' && window) || this);

/*
 * JavaScript Load Image Meta
 * https://github.com/blueimp/JavaScript-Load-Image
 *
 * Copyright 2013, Sebastian Tschan
 * https://blueimp.net
 *
 * Image metadata handling implementation
 * based on the help and contribution of
 * Achim Stöhr.
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, module, require, Promise, DataView, Uint8Array, ArrayBuffer */

;(function (factory) {
    'use strict'
    if (typeof define === 'function' && define.amd) {
      // Register as an anonymous AMD module:
      define(['./load-image'], factory)
    } else if (typeof module === 'object' && module.exports) {
      factory(require('./load-image'))
    } else {
      // Browser globals:
      factory(window.loadImage)
    }
  })(function (loadImage) {
    'use strict'

    var global = loadImage.global
    var originalTransform = loadImage.transform

    var blobSlice =
      global.Blob &&
      (Blob.prototype.slice ||
        Blob.prototype.webkitSlice ||
        Blob.prototype.mozSlice)

    var bufferSlice =
      (global.ArrayBuffer && ArrayBuffer.prototype.slice) ||
      function (begin, end) {
        // Polyfill for IE10, which does not support ArrayBuffer.slice
        // eslint-disable-next-line no-param-reassign
        end = end || this.byteLength - begin
        var arr1 = new Uint8Array(this, begin, end)
        var arr2 = new Uint8Array(end)
        arr2.set(arr1)
        return arr2.buffer
      }

    var metaDataParsers = {
      jpeg: {
        0xffe1: [], // APP1 marker
        0xffed: [] // APP13 marker
      }
    }

    /**
     * Parses image metadata and calls the callback with an object argument
     * with the following property:
     * - imageHead: The complete image head as ArrayBuffer
     * The options argument accepts an object and supports the following
     * properties:
     * - maxMetaDataSize: Defines the maximum number of bytes to parse.
     * - disableImageHead: Disables creating the imageHead property.
     *
     * @param {Blob} file Blob object
     * @param {Function} [callback] Callback function
     * @param {object} [options] Parsing options
     * @param {object} [data] Result data object
     * @returns {Promise<object>|undefined} Returns Promise if no callback given.
     */
    function parseMetaData(file, callback, options, data) {
      var that = this
      /**
       * Promise executor
       *
       * @param {Function} resolve Resolution function
       * @param {Function} reject Rejection function
       * @returns {undefined} Undefined
       */
      function executor(resolve, reject) {
        if (
          !(
            global.DataView &&
            blobSlice &&
            file &&
            file.size >= 12 &&
            file.type === 'image/jpeg'
          )
        ) {
          // Nothing to parse
          return resolve(data)
        }
        // 256 KiB should contain all EXIF/ICC/IPTC segments:
        var maxMetaDataSize = options.maxMetaDataSize || 262144
        if (
          !loadImage.readFile(
            blobSlice.call(file, 0, maxMetaDataSize),
            function (buffer) {
              // Note on endianness:
              // Since the marker and length bytes in JPEG files are always
              // stored in big endian order, we can leave the endian parameter
              // of the DataView methods undefined, defaulting to big endian.
              var dataView = new DataView(buffer)
              // Check for the JPEG marker (0xffd8):
              if (dataView.getUint16(0) !== 0xffd8) {
                return reject(
                  new Error('Invalid JPEG file: Missing JPEG marker.')
                )
              }
              var offset = 2
              var maxOffset = dataView.byteLength - 4
              var headLength = offset
              var markerBytes
              var markerLength
              var parsers
              var i
              while (offset < maxOffset) {
                markerBytes = dataView.getUint16(offset)
                // Search for APPn (0xffeN) and COM (0xfffe) markers,
                // which contain application-specific metadata like
                // Exif, ICC and IPTC data and text comments:
                if (
                  (markerBytes >= 0xffe0 && markerBytes <= 0xffef) ||
                  markerBytes === 0xfffe
                ) {
                  // The marker bytes (2) are always followed by
                  // the length bytes (2), indicating the length of the
                  // marker segment, which includes the length bytes,
                  // but not the marker bytes, so we add 2:
                  markerLength = dataView.getUint16(offset + 2) + 2
                  if (offset + markerLength > dataView.byteLength) {
                    // eslint-disable-next-line no-console
                    console.log('Invalid JPEG metadata: Invalid segment size.')
                    break
                  }
                  parsers = metaDataParsers.jpeg[markerBytes]
                  if (parsers && !options.disableMetaDataParsers) {
                    for (i = 0; i < parsers.length; i += 1) {
                      parsers[i].call(
                        that,
                        dataView,
                        offset,
                        markerLength,
                        data,
                        options
                      )
                    }
                  }
                  offset += markerLength
                  headLength = offset
                } else {
                  // Not an APPn or COM marker, probably safe to
                  // assume that this is the end of the metadata
                  break
                }
              }
              // Meta length must be longer than JPEG marker (2)
              // plus APPn marker (2), followed by length bytes (2):
              if (!options.disableImageHead && headLength > 6) {
                data.imageHead = bufferSlice.call(buffer, 0, headLength)
              }
              resolve(data)
            },
            reject,
            'readAsArrayBuffer'
          )
        ) {
          // No support for the FileReader interface, nothing to parse
          resolve(data)
        }
      }
      options = options || {} // eslint-disable-line no-param-reassign
      if (global.Promise && typeof callback !== 'function') {
        options = callback || {} // eslint-disable-line no-param-reassign
        data = options // eslint-disable-line no-param-reassign
        return new Promise(executor)
      }
      data = data || {} // eslint-disable-line no-param-reassign
      return executor(callback, callback)
    }

    /**
     * Replaces the head of a JPEG Blob
     *
     * @param {Blob} blob Blob object
     * @param {ArrayBuffer} oldHead Old JPEG head
     * @param {ArrayBuffer} newHead New JPEG head
     * @returns {Blob} Combined Blob
     */
    function replaceJPEGHead(blob, oldHead, newHead) {
      if (!blob || !oldHead || !newHead) return null
      return new Blob([newHead, blobSlice.call(blob, oldHead.byteLength)], {
        type: 'image/jpeg'
      })
    }

    /**
     * Replaces the image head of a JPEG blob with the given one.
     * Returns a Promise or calls the callback with the new Blob.
     *
     * @param {Blob} blob Blob object
     * @param {ArrayBuffer} head New JPEG head
     * @param {Function} [callback] Callback function
     * @returns {Promise<Blob|null>|undefined} Combined Blob
     */
    function replaceHead(blob, head, callback) {
      var options = { maxMetaDataSize: 1024, disableMetaDataParsers: true }
      if (!callback && global.Promise) {
        return parseMetaData(blob, options).then(function (data) {
          return replaceJPEGHead(blob, data.imageHead, head)
        })
      }
      parseMetaData(
        blob,
        function (data) {
          callback(replaceJPEGHead(blob, data.imageHead, head))
        },
        options
      )
    }

    loadImage.transform = function (img, options, callback, file, data) {
      if (loadImage.requiresMetaData(options)) {
        data = data || {} // eslint-disable-line no-param-reassign
        parseMetaData(
          file,
          function (result) {
            if (result !== data) {
              // eslint-disable-next-line no-console
              if (global.console) console.log(result)
              result = data // eslint-disable-line no-param-reassign
            }
            originalTransform.call(
              loadImage,
              img,
              options,
              callback,
              file,
              result
            )
          },
          options,
          data
        )
      } else {
        originalTransform.apply(loadImage, arguments)
      }
    }

    loadImage.blobSlice = blobSlice
    loadImage.bufferSlice = bufferSlice
    loadImage.replaceHead = replaceHead
    loadImage.parseMetaData = parseMetaData
    loadImage.metaDataParsers = metaDataParsers
  });

/*
 * JavaScript Load Image Scaling
 * https://github.com/blueimp/JavaScript-Load-Image
 *
 * Copyright 2011, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * https://opensource.org/licenses/MIT
 */

/* global define, module, require */

;(function (factory) {
    'use strict'
    if (typeof define === 'function' && define.amd) {
      // Register as an anonymous AMD module:
      define(['./load-image'], factory)
    } else if (typeof module === 'object' && module.exports) {
      factory(require('./load-image'))
    } else {
      // Browser globals:
      factory(window.loadImage)
    }
  })(function (loadImage) {
    'use strict'

    var originalTransform = loadImage.transform

    loadImage.createCanvas = function (width, height, offscreen) {
      if (offscreen && loadImage.global.OffscreenCanvas) {
        return new OffscreenCanvas(width, height)
      }
      var canvas = document.createElement('canvas')
      canvas.width = width
      canvas.height = height
      return canvas
    }

    loadImage.transform = function (img, options, callback, file, data) {
      originalTransform.call(
        loadImage,
        loadImage.scale(img, options, data),
        options,
        callback,
        file,
        data
      )
    }

    // Transform image coordinates, allows to override e.g.
    // the canvas orientation based on the orientation option,
    // gets canvas, options and data passed as arguments:
    loadImage.transformCoordinates = function () {}

    // Returns transformed options, allows to override e.g.
    // maxWidth, maxHeight and crop options based on the aspectRatio.
    // gets img, options, data passed as arguments:
    loadImage.getTransformedOptions = function (img, options) {
      var aspectRatio = options.aspectRatio
      var newOptions
      var i
      var width
      var height
      if (!aspectRatio) {
        return options
      }
      newOptions = {}
      for (i in options) {
        if (Object.prototype.hasOwnProperty.call(options, i)) {
          newOptions[i] = options[i]
        }
      }
      newOptions.crop = true
      width = img.naturalWidth || img.width
      height = img.naturalHeight || img.height
      if (width / height > aspectRatio) {
        newOptions.maxWidth = height * aspectRatio
        newOptions.maxHeight = height
      } else {
        newOptions.maxWidth = width
        newOptions.maxHeight = width / aspectRatio
      }
      return newOptions
    }

    // Canvas render method, allows to implement a different rendering algorithm:
    loadImage.drawImage = function (
      img,
      canvas,
      sourceX,
      sourceY,
      sourceWidth,
      sourceHeight,
      destWidth,
      destHeight,
      options
    ) {
      var ctx = canvas.getContext('2d')
      if (options.imageSmoothingEnabled === false) {
        ctx.msImageSmoothingEnabled = false
        ctx.imageSmoothingEnabled = false
      } else if (options.imageSmoothingQuality) {
        ctx.imageSmoothingQuality = options.imageSmoothingQuality
      }
      ctx.drawImage(
        img,
        sourceX,
        sourceY,
        sourceWidth,
        sourceHeight,
        0,
        0,
        destWidth,
        destHeight
      )
      return ctx
    }

    // Determines if the target image should be a canvas element:
    loadImage.requiresCanvas = function (options) {
      return options.canvas || options.crop || !!options.aspectRatio
    }

    // Scales and/or crops the given image (img or canvas HTML element)
    // using the given options:
    loadImage.scale = function (img, options, data) {
      // eslint-disable-next-line no-param-reassign
      options = options || {}
      // eslint-disable-next-line no-param-reassign
      data = data || {}
      var useCanvas =
        img.getContext ||
        (loadImage.requiresCanvas(options) &&
          !!loadImage.global.HTMLCanvasElement)
      var width = img.naturalWidth || img.width
      var height = img.naturalHeight || img.height
      var destWidth = width
      var destHeight = height
      var maxWidth
      var maxHeight
      var minWidth
      var minHeight
      var sourceWidth
      var sourceHeight
      var sourceX
      var sourceY
      var pixelRatio
      var downsamplingRatio
      var tmp
      var canvas
      /**
       * Scales up image dimensions
       */
      function scaleUp() {
        var scale = Math.max(
          (minWidth || destWidth) / destWidth,
          (minHeight || destHeight) / destHeight
        )
        if (scale > 1) {
          destWidth *= scale
          destHeight *= scale
        }
      }
      /**
       * Scales down image dimensions
       */
      function scaleDown() {
        var scale = Math.min(
          (maxWidth || destWidth) / destWidth,
          (maxHeight || destHeight) / destHeight
        )
        if (scale < 1) {
          destWidth *= scale
          destHeight *= scale
        }
      }
      if (useCanvas) {
        // eslint-disable-next-line no-param-reassign
        options = loadImage.getTransformedOptions(img, options, data)
        sourceX = options.left || 0
        sourceY = options.top || 0
        if (options.sourceWidth) {
          sourceWidth = options.sourceWidth
          if (options.right !== undefined && options.left === undefined) {
            sourceX = width - sourceWidth - options.right
          }
        } else {
          sourceWidth = width - sourceX - (options.right || 0)
        }
        if (options.sourceHeight) {
          sourceHeight = options.sourceHeight
          if (options.bottom !== undefined && options.top === undefined) {
            sourceY = height - sourceHeight - options.bottom
          }
        } else {
          sourceHeight = height - sourceY - (options.bottom || 0)
        }
        destWidth = sourceWidth
        destHeight = sourceHeight
      }
      maxWidth = options.maxWidth
      maxHeight = options.maxHeight
      minWidth = options.minWidth
      minHeight = options.minHeight
      if (useCanvas && maxWidth && maxHeight && options.crop) {
        destWidth = maxWidth
        destHeight = maxHeight
        tmp = sourceWidth / sourceHeight - maxWidth / maxHeight
        if (tmp < 0) {
          sourceHeight = (maxHeight * sourceWidth) / maxWidth
          if (options.top === undefined && options.bottom === undefined) {
            sourceY = (height - sourceHeight) / 2
          }
        } else if (tmp > 0) {
          sourceWidth = (maxWidth * sourceHeight) / maxHeight
          if (options.left === undefined && options.right === undefined) {
            sourceX = (width - sourceWidth) / 2
          }
        }
      } else {
        if (options.contain || options.cover) {
          minWidth = maxWidth = maxWidth || minWidth
          minHeight = maxHeight = maxHeight || minHeight
        }
        if (options.cover) {
          scaleDown()
          scaleUp()
        } else {
          scaleUp()
          scaleDown()
        }
      }
      if (useCanvas) {
        pixelRatio = options.pixelRatio
        if (
          pixelRatio > 1 &&
          // Check if the image has not yet had the device pixel ratio applied:
          !(
            img.style.width &&
            Math.floor(parseFloat(img.style.width, 10)) ===
              Math.floor(width / pixelRatio)
          )
        ) {
          destWidth *= pixelRatio
          destHeight *= pixelRatio
        }
        // Check if workaround for Chromium orientation crop bug is required:
        // https://bugs.chromium.org/p/chromium/issues/detail?id=1074354
        if (
          loadImage.orientationCropBug &&
          !img.getContext &&
          (sourceX || sourceY || sourceWidth !== width || sourceHeight !== height)
        ) {
          // Write the complete source image to an intermediate canvas first:
          tmp = img
          // eslint-disable-next-line no-param-reassign
          img = loadImage.createCanvas(width, height, true)
          loadImage.drawImage(
            tmp,
            img,
            0,
            0,
            width,
            height,
            width,
            height,
            options
          )
        }
        downsamplingRatio = options.downsamplingRatio
        if (
          downsamplingRatio > 0 &&
          downsamplingRatio < 1 &&
          destWidth < sourceWidth &&
          destHeight < sourceHeight
        ) {
          while (sourceWidth * downsamplingRatio > destWidth) {
            canvas = loadImage.createCanvas(
              sourceWidth * downsamplingRatio,
              sourceHeight * downsamplingRatio,
              true
            )
            loadImage.drawImage(
              img,
              canvas,
              sourceX,
              sourceY,
              sourceWidth,
              sourceHeight,
              canvas.width,
              canvas.height,
              options
            )
            sourceX = 0
            sourceY = 0
            sourceWidth = canvas.width
            sourceHeight = canvas.height
            // eslint-disable-next-line no-param-reassign
            img = canvas
          }
        }
        canvas = loadImage.createCanvas(destWidth, destHeight)
        loadImage.transformCoordinates(canvas, options, data)
        if (pixelRatio > 1) {
          canvas.style.width = canvas.width / pixelRatio + 'px'
        }
        loadImage
          .drawImage(
            img,
            canvas,
            sourceX,
            sourceY,
            sourceWidth,
            sourceHeight,
            destWidth,
            destHeight,
            options
          )
          .setTransform(1, 0, 0, 1, 0, 0) // reset to the identity matrix
        return canvas
      }
      img.width = destWidth
      img.height = destHeight
      return img
    }
  });

/*!
 * clipboard.js v2.0.11
 * https://clipboardjs.com/
 *
 * Licensed MIT © Zeno Rocha
 */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ClipboardJS"] = factory();
	else
		root["ClipboardJS"] = factory();
})(this, function() {
return /******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 686:
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": function() { return /* binding */ clipboard; }
});

// EXTERNAL MODULE: ./node_modules/tiny-emitter/index.js
var tiny_emitter = __webpack_require__(279);
var tiny_emitter_default = /*#__PURE__*/__webpack_require__.n(tiny_emitter);
// EXTERNAL MODULE: ./node_modules/good-listener/src/listen.js
var listen = __webpack_require__(370);
var listen_default = /*#__PURE__*/__webpack_require__.n(listen);
// EXTERNAL MODULE: ./node_modules/select/src/select.js
var src_select = __webpack_require__(817);
var select_default = /*#__PURE__*/__webpack_require__.n(src_select);
;// CONCATENATED MODULE: ./src/common/command.js
/**
 * Executes a given operation type.
 * @param {String} type
 * @return {Boolean}
 */
function command(type) {
  try {
    return document.execCommand(type);
  } catch (err) {
    return false;
  }
}
;// CONCATENATED MODULE: ./src/actions/cut.js


/**
 * Cut action wrapper.
 * @param {String|HTMLElement} target
 * @return {String}
 */

var ClipboardActionCut = function ClipboardActionCut(target) {
  var selectedText = select_default()(target);
  command('cut');
  return selectedText;
};

/* harmony default export */ var actions_cut = (ClipboardActionCut);
;// CONCATENATED MODULE: ./src/common/create-fake-element.js
/**
 * Creates a fake textarea element with a value.
 * @param {String} value
 * @return {HTMLElement}
 */
function createFakeElement(value) {
  var isRTL = document.documentElement.getAttribute('dir') === 'rtl';
  var fakeElement = document.createElement('textarea'); // Prevent zooming on iOS

  fakeElement.style.fontSize = '12pt'; // Reset box model

  fakeElement.style.border = '0';
  fakeElement.style.padding = '0';
  fakeElement.style.margin = '0'; // Move element out of screen horizontally

  fakeElement.style.position = 'absolute';
  fakeElement.style[isRTL ? 'right' : 'left'] = '-9999px'; // Move element to the same position vertically

  var yPosition = window.pageYOffset || document.documentElement.scrollTop;
  fakeElement.style.top = "".concat(yPosition, "px");
  fakeElement.setAttribute('readonly', '');
  fakeElement.value = value;
  return fakeElement;
}
;// CONCATENATED MODULE: ./src/actions/copy.js



/**
 * Create fake copy action wrapper using a fake element.
 * @param {String} target
 * @param {Object} options
 * @return {String}
 */

var fakeCopyAction = function fakeCopyAction(value, options) {
  var fakeElement = createFakeElement(value);
  options.container.appendChild(fakeElement);
  var selectedText = select_default()(fakeElement);
  command('copy');
  fakeElement.remove();
  return selectedText;
};
/**
 * Copy action wrapper.
 * @param {String|HTMLElement} target
 * @param {Object} options
 * @return {String}
 */


var ClipboardActionCopy = function ClipboardActionCopy(target) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
    container: document.body
  };
  var selectedText = '';

  if (typeof target === 'string') {
    selectedText = fakeCopyAction(target, options);
  } else if (target instanceof HTMLInputElement && !['text', 'search', 'url', 'tel', 'password'].includes(target === null || target === void 0 ? void 0 : target.type)) {
    // If input type doesn't support `setSelectionRange`. Simulate it. https://developer.mozilla.org/en-US/docs/Web/API/HTMLInputElement/setSelectionRange
    selectedText = fakeCopyAction(target.value, options);
  } else {
    selectedText = select_default()(target);
    command('copy');
  }

  return selectedText;
};

/* harmony default export */ var actions_copy = (ClipboardActionCopy);
;// CONCATENATED MODULE: ./src/actions/default.js
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }



/**
 * Inner function which performs selection from either `text` or `target`
 * properties and then executes copy or cut operations.
 * @param {Object} options
 */

var ClipboardActionDefault = function ClipboardActionDefault() {
  var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  // Defines base properties passed from constructor.
  var _options$action = options.action,
      action = _options$action === void 0 ? 'copy' : _options$action,
      container = options.container,
      target = options.target,
      text = options.text; // Sets the `action` to be performed which can be either 'copy' or 'cut'.

  if (action !== 'copy' && action !== 'cut') {
    throw new Error('Invalid "action" value, use either "copy" or "cut"');
  } // Sets the `target` property using an element that will be have its content copied.


  if (target !== undefined) {
    if (target && _typeof(target) === 'object' && target.nodeType === 1) {
      if (action === 'copy' && target.hasAttribute('disabled')) {
        throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');
      }

      if (action === 'cut' && (target.hasAttribute('readonly') || target.hasAttribute('disabled'))) {
        throw new Error('Invalid "target" attribute. You can\'t cut text from elements with "readonly" or "disabled" attributes');
      }
    } else {
      throw new Error('Invalid "target" value, use a valid Element');
    }
  } // Define selection strategy based on `text` property.


  if (text) {
    return actions_copy(text, {
      container: container
    });
  } // Defines which selection strategy based on `target` property.


  if (target) {
    return action === 'cut' ? actions_cut(target) : actions_copy(target, {
      container: container
    });
  }
};

/* harmony default export */ var actions_default = (ClipboardActionDefault);
;// CONCATENATED MODULE: ./src/clipboard.js
function clipboard_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { clipboard_typeof = function _typeof(obj) { return typeof obj; }; } else { clipboard_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return clipboard_typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (clipboard_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }






/**
 * Helper function to retrieve attribute value.
 * @param {String} suffix
 * @param {Element} element
 */

function getAttributeValue(suffix, element) {
  var attribute = "data-clipboard-".concat(suffix);

  if (!element.hasAttribute(attribute)) {
    return;
  }

  return element.getAttribute(attribute);
}
/**
 * Base class which takes one or more elements, adds event listeners to them,
 * and instantiates a new `ClipboardAction` on each click.
 */


var Clipboard = /*#__PURE__*/function (_Emitter) {
  _inherits(Clipboard, _Emitter);

  var _super = _createSuper(Clipboard);

  /**
   * @param {String|HTMLElement|HTMLCollection|NodeList} trigger
   * @param {Object} options
   */
  function Clipboard(trigger, options) {
    var _this;

    _classCallCheck(this, Clipboard);

    _this = _super.call(this);

    _this.resolveOptions(options);

    _this.listenClick(trigger);

    return _this;
  }
  /**
   * Defines if attributes would be resolved using internal setter functions
   * or custom functions that were passed in the constructor.
   * @param {Object} options
   */


  _createClass(Clipboard, [{
    key: "resolveOptions",
    value: function resolveOptions() {
      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      this.action = typeof options.action === 'function' ? options.action : this.defaultAction;
      this.target = typeof options.target === 'function' ? options.target : this.defaultTarget;
      this.text = typeof options.text === 'function' ? options.text : this.defaultText;
      this.container = clipboard_typeof(options.container) === 'object' ? options.container : document.body;
    }
    /**
     * Adds a click event listener to the passed trigger.
     * @param {String|HTMLElement|HTMLCollection|NodeList} trigger
     */

  }, {
    key: "listenClick",
    value: function listenClick(trigger) {
      var _this2 = this;

      this.listener = listen_default()(trigger, 'click', function (e) {
        return _this2.onClick(e);
      });
    }
    /**
     * Defines a new `ClipboardAction` on each click event.
     * @param {Event} e
     */

  }, {
    key: "onClick",
    value: function onClick(e) {
      var trigger = e.delegateTarget || e.currentTarget;
      var action = this.action(trigger) || 'copy';
      var text = actions_default({
        action: action,
        container: this.container,
        target: this.target(trigger),
        text: this.text(trigger)
      }); // Fires an event based on the copy operation result.

      this.emit(text ? 'success' : 'error', {
        action: action,
        text: text,
        trigger: trigger,
        clearSelection: function clearSelection() {
          if (trigger) {
            trigger.focus();
          }

          window.getSelection().removeAllRanges();
        }
      });
    }
    /**
     * Default `action` lookup function.
     * @param {Element} trigger
     */

  }, {
    key: "defaultAction",
    value: function defaultAction(trigger) {
      return getAttributeValue('action', trigger);
    }
    /**
     * Default `target` lookup function.
     * @param {Element} trigger
     */

  }, {
    key: "defaultTarget",
    value: function defaultTarget(trigger) {
      var selector = getAttributeValue('target', trigger);

      if (selector) {
        return document.querySelector(selector);
      }
    }
    /**
     * Allow fire programmatically a copy action
     * @param {String|HTMLElement} target
     * @param {Object} options
     * @returns Text copied.
     */

  }, {
    key: "defaultText",

    /**
     * Default `text` lookup function.
     * @param {Element} trigger
     */
    value: function defaultText(trigger) {
      return getAttributeValue('text', trigger);
    }
    /**
     * Destroy lifecycle.
     */

  }, {
    key: "destroy",
    value: function destroy() {
      this.listener.destroy();
    }
  }], [{
    key: "copy",
    value: function copy(target) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
        container: document.body
      };
      return actions_copy(target, options);
    }
    /**
     * Allow fire programmatically a cut action
     * @param {String|HTMLElement} target
     * @returns Text cutted.
     */

  }, {
    key: "cut",
    value: function cut(target) {
      return actions_cut(target);
    }
    /**
     * Returns the support of the given action, or all actions if no action is
     * given.
     * @param {String} [action]
     */

  }, {
    key: "isSupported",
    value: function isSupported() {
      var action = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : ['copy', 'cut'];
      var actions = typeof action === 'string' ? [action] : action;
      var support = !!document.queryCommandSupported;
      actions.forEach(function (action) {
        support = support && !!document.queryCommandSupported(action);
      });
      return support;
    }
  }]);

  return Clipboard;
}((tiny_emitter_default()));

/* harmony default export */ var clipboard = (Clipboard);

/***/ }),

/***/ 828:
/***/ (function(module) {

var DOCUMENT_NODE_TYPE = 9;

/**
 * A polyfill for Element.matches()
 */
if (typeof Element !== 'undefined' && !Element.prototype.matches) {
    var proto = Element.prototype;

    proto.matches = proto.matchesSelector ||
                    proto.mozMatchesSelector ||
                    proto.msMatchesSelector ||
                    proto.oMatchesSelector ||
                    proto.webkitMatchesSelector;
}

/**
 * Finds the closest parent that matches a selector.
 *
 * @param {Element} element
 * @param {String} selector
 * @return {Function}
 */
function closest (element, selector) {
    while (element && element.nodeType !== DOCUMENT_NODE_TYPE) {
        if (typeof element.matches === 'function' &&
            element.matches(selector)) {
          return element;
        }
        element = element.parentNode;
    }
}

module.exports = closest;


/***/ }),

/***/ 438:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var closest = __webpack_require__(828);

/**
 * Delegates event to a selector.
 *
 * @param {Element} element
 * @param {String} selector
 * @param {String} type
 * @param {Function} callback
 * @param {Boolean} useCapture
 * @return {Object}
 */
function _delegate(element, selector, type, callback, useCapture) {
    var listenerFn = listener.apply(this, arguments);

    element.addEventListener(type, listenerFn, useCapture);

    return {
        destroy: function() {
            element.removeEventListener(type, listenerFn, useCapture);
        }
    }
}

/**
 * Delegates event to a selector.
 *
 * @param {Element|String|Array} [elements]
 * @param {String} selector
 * @param {String} type
 * @param {Function} callback
 * @param {Boolean} useCapture
 * @return {Object}
 */
function delegate(elements, selector, type, callback, useCapture) {
    // Handle the regular Element usage
    if (typeof elements.addEventListener === 'function') {
        return _delegate.apply(null, arguments);
    }

    // Handle Element-less usage, it defaults to global delegation
    if (typeof type === 'function') {
        // Use `document` as the first parameter, then apply arguments
        // This is a short way to .unshift `arguments` without running into deoptimizations
        return _delegate.bind(null, document).apply(null, arguments);
    }

    // Handle Selector-based usage
    if (typeof elements === 'string') {
        elements = document.querySelectorAll(elements);
    }

    // Handle Array-like based usage
    return Array.prototype.map.call(elements, function (element) {
        return _delegate(element, selector, type, callback, useCapture);
    });
}

/**
 * Finds closest match and invokes callback.
 *
 * @param {Element} element
 * @param {String} selector
 * @param {String} type
 * @param {Function} callback
 * @return {Function}
 */
function listener(element, selector, type, callback) {
    return function(e) {
        e.delegateTarget = closest(e.target, selector);

        if (e.delegateTarget) {
            callback.call(element, e);
        }
    }
}

module.exports = delegate;


/***/ }),

/***/ 879:
/***/ (function(__unused_webpack_module, exports) {

/**
 * Check if argument is a HTML element.
 *
 * @param {Object} value
 * @return {Boolean}
 */
exports.node = function(value) {
    return value !== undefined
        && value instanceof HTMLElement
        && value.nodeType === 1;
};

/**
 * Check if argument is a list of HTML elements.
 *
 * @param {Object} value
 * @return {Boolean}
 */
exports.nodeList = function(value) {
    var type = Object.prototype.toString.call(value);

    return value !== undefined
        && (type === '[object NodeList]' || type === '[object HTMLCollection]')
        && ('length' in value)
        && (value.length === 0 || exports.node(value[0]));
};

/**
 * Check if argument is a string.
 *
 * @param {Object} value
 * @return {Boolean}
 */
exports.string = function(value) {
    return typeof value === 'string'
        || value instanceof String;
};

/**
 * Check if argument is a function.
 *
 * @param {Object} value
 * @return {Boolean}
 */
exports.fn = function(value) {
    var type = Object.prototype.toString.call(value);

    return type === '[object Function]';
};


/***/ }),

/***/ 370:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var is = __webpack_require__(879);
var delegate = __webpack_require__(438);

/**
 * Validates all params and calls the right
 * listener function based on its target type.
 *
 * @param {String|HTMLElement|HTMLCollection|NodeList} target
 * @param {String} type
 * @param {Function} callback
 * @return {Object}
 */
function listen(target, type, callback) {
    if (!target && !type && !callback) {
        throw new Error('Missing required arguments');
    }

    if (!is.string(type)) {
        throw new TypeError('Second argument must be a String');
    }

    if (!is.fn(callback)) {
        throw new TypeError('Third argument must be a Function');
    }

    if (is.node(target)) {
        return listenNode(target, type, callback);
    }
    else if (is.nodeList(target)) {
        return listenNodeList(target, type, callback);
    }
    else if (is.string(target)) {
        return listenSelector(target, type, callback);
    }
    else {
        throw new TypeError('First argument must be a String, HTMLElement, HTMLCollection, or NodeList');
    }
}

/**
 * Adds an event listener to a HTML element
 * and returns a remove listener function.
 *
 * @param {HTMLElement} node
 * @param {String} type
 * @param {Function} callback
 * @return {Object}
 */
function listenNode(node, type, callback) {
    node.addEventListener(type, callback);

    return {
        destroy: function() {
            node.removeEventListener(type, callback);
        }
    }
}

/**
 * Add an event listener to a list of HTML elements
 * and returns a remove listener function.
 *
 * @param {NodeList|HTMLCollection} nodeList
 * @param {String} type
 * @param {Function} callback
 * @return {Object}
 */
function listenNodeList(nodeList, type, callback) {
    Array.prototype.forEach.call(nodeList, function(node) {
        node.addEventListener(type, callback);
    });

    return {
        destroy: function() {
            Array.prototype.forEach.call(nodeList, function(node) {
                node.removeEventListener(type, callback);
            });
        }
    }
}

/**
 * Add an event listener to a selector
 * and returns a remove listener function.
 *
 * @param {String} selector
 * @param {String} type
 * @param {Function} callback
 * @return {Object}
 */
function listenSelector(selector, type, callback) {
    return delegate(document.body, selector, type, callback);
}

module.exports = listen;


/***/ }),

/***/ 817:
/***/ (function(module) {

function select(element) {
    var selectedText;

    if (element.nodeName === 'SELECT') {
        element.focus();

        selectedText = element.value;
    }
    else if (element.nodeName === 'INPUT' || element.nodeName === 'TEXTAREA') {
        var isReadOnly = element.hasAttribute('readonly');

        if (!isReadOnly) {
            element.setAttribute('readonly', '');
        }

        element.select();
        element.setSelectionRange(0, element.value.length);

        if (!isReadOnly) {
            element.removeAttribute('readonly');
        }

        selectedText = element.value;
    }
    else {
        if (element.hasAttribute('contenteditable')) {
            element.focus();
        }

        var selection = window.getSelection();
        var range = document.createRange();

        range.selectNodeContents(element);
        selection.removeAllRanges();
        selection.addRange(range);

        selectedText = selection.toString();
    }

    return selectedText;
}

module.exports = select;


/***/ }),

/***/ 279:
/***/ (function(module) {

function E () {
  // Keep this empty so it's easier to inherit from
  // (via https://github.com/lipsmack from https://github.com/scottcorgan/tiny-emitter/issues/3)
}

E.prototype = {
  on: function (name, callback, ctx) {
    var e = this.e || (this.e = {});

    (e[name] || (e[name] = [])).push({
      fn: callback,
      ctx: ctx
    });

    return this;
  },

  once: function (name, callback, ctx) {
    var self = this;
    function listener () {
      self.off(name, listener);
      callback.apply(ctx, arguments);
    };

    listener._ = callback
    return this.on(name, listener, ctx);
  },

  emit: function (name) {
    var data = [].slice.call(arguments, 1);
    var evtArr = ((this.e || (this.e = {}))[name] || []).slice();
    var i = 0;
    var len = evtArr.length;

    for (i; i < len; i++) {
      evtArr[i].fn.apply(evtArr[i].ctx, data);
    }

    return this;
  },

  off: function (name, callback) {
    var e = this.e || (this.e = {});
    var evts = e[name];
    var liveEvents = [];

    if (evts && callback) {
      for (var i = 0, len = evts.length; i < len; i++) {
        if (evts[i].fn !== callback && evts[i].fn._ !== callback)
          liveEvents.push(evts[i]);
      }
    }

    // Remove event from queue to prevent memory leak
    // Suggested by https://github.com/lazd
    // Ref: https://github.com/scottcorgan/tiny-emitter/commit/c6ebfaa9bc973b33d110a84a307742b7cf94c953#commitcomment-5024910

    (liveEvents.length)
      ? e[name] = liveEvents
      : delete e[name];

    return this;
  }
};

module.exports = E;
module.exports.TinyEmitter = E;


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		if(__webpack_module_cache__[moduleId]) {
/******/ 			return __webpack_module_cache__[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/
/************************************************************************/
/******/ 	// module exports must be returned from runtime so entry inlining is disabled
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(686);
/******/ })()
.default;
});

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

CHV = {
    obj: {},
    fn: {},
    str: {},
};

if (window.opener) {
    CHV.obj.opener = {
        uploadPlugin: {},
    }
}

CHV.fn.ctaButtons = {
    selectors: {
        container: "[data-contains=cta-album]",
    },
    render: function(html="") {
        $(this.selectors.container).each(function() {
            $(this).html(html);
        });
    }
}
CHV.fn.ctaForm = {
    enable: 0,
    array: [],
    selectors: {
        root: "#cta-form",
        rows: "#cta-rows",
        enable: "#cta-enable",
        template: "#cta-row-template",
        combo: "#cta-combo",
        row: ".cta-row"
    },
    update: function($atElement) {
        let pos = this.getPos($atElement);
        let key = $atElement.attr("name").match(/cta-(.*)?_\d+/)[1]
        this.array[pos-1][key] = $atElement.val();
    },
    add: function(label="", icon="", href="") {
        this.array.push(this.getRowObject(label, icon, href));
        this.render();
    },
    insert: function($atElement) {
        let pos = this.getPos($atElement);
        this.array.splice(pos, 0, this.getRowObject());
        this.render();
    },
    remove: function($atElement) {
        let pos = this.getPos($atElement);
        this.array.splice(pos-1, 1);
        this.render();
    },
    getRowObject: function(label="", icon="", href="") {
        return {
            "label": label,
            "icon": icon,
            "href": href
        }
    },
    getIconClass: function(icon) {
        if(!/\s/g.test(icon)) {
            return "fa-solid fa-" + icon;
        }

        return icon;
    },
    getRow: function($element) {
        return $element.closest(this.selectors.row);
    },
    getPos: function($element) {
        return this.getRow($element).data("pos");
    },
    getTemplateHtml: function() {
        return $(this.selectors.template).html();
    },
    getRowHtml: function(pos, data) {
        return this.getTemplateHtml()
            .replaceAll(/%pos%/g, pos)
            .replaceAll(/%label%/g, data.label)
            .replaceAll(/%href%/g, data.href)
            .replaceAll(/%icon%/g, data.icon)
            .replaceAll(
                /%iconClass%/g,
                this.getIconClass(data.icon)
            );
    },
    render: function() {
        let $ctaForm = $(this.selectors.root);
        let $ctaRows = $ctaForm.find(this.selectors.rows);
        let $this = this;
        this.destroy();
        $.each(this.array, function(index, data) {
            $ctaRows.append($this.getRowHtml(index+1, data));
        });
        this.setEnable(this.enable);
        $ctaRows.sortable({
            cursor: "grabbing",
            axis: "y",
            update: function() {
                let array = [];
                $(this).find($this.selectors.row).each(function() {
                    let pos = $this.getPos($(this));
                    array.push($this.array[pos-1]);
                });
                $this.array = array;
                $this.render();
            }
        });
    },
    setEnable: function(integer) {
        let $ctaRows = $(this.selectors.rows, this.selectors.root);
        this.enable = integer;
        let enable = this.enable === 1;
        $('input[data-required]', $ctaRows).each(function() {
            $(this).attr("required", enable);
        });
    },
    destroy: function() {
        let $ctaForm = $(this.selectors.root);
        let $ctaRows = $ctaForm.find(this.selectors.rows);
        try {
            $ctaRows.sortable("destroy");
        } catch(e) {}
        $ctaRows.empty();
    }
}

CHV.fn.album = {
    showEmbedCodes: function () {
        var $loading = $(".content-listing-loading", "#tab-embeds");
        if (!$loading.exists()) {
            return;
        }
        var $embed_codes = $("#embed-codes");
        $.ajax({
            url: PF.obj.config.json_api,
            type: "POST",
            dataType: "json",
            data: {
                action: "get-album-contents",
                albumid: CHV.obj.resource.id,
                auth_token: PF.obj.config.auth_token
            },
            cache: false,
        }).always(function (XHR) {
            PF.fn.loading.destroy($loading);
            if (XHR.status_code == 200) {
                CHV.fn.fillEmbedCodes(XHR.contents, "#tab-embeds");
                $("#tab-embeds").addClass("visible");
                $embed_codes.removeClass("soft-hidden");
            }
        });
    },
}

CHV.fn.modal = {
    getTemplateWithPreview: function (selector, $target) {
        var template = $(selector).html();
        var div = $("<div/>");
        var html = '';
        var src = $target.find('.image-container img').attr('src');
        if (typeof src !== typeof undefined) {
            html += '<a href="'
                + $target.attr('data-url-short')
                + '" target="_blank"><img class="canvas checkered-background" src='
                + src
                + ' /></a>';
        }
        div.html(template).find('.image-preview').html(html);

        return div.html();
    },
    getTemplateWithPreviews: function (selector, $targets, limit = 50) {
        var template = $(selector).html();
        var div = $("<div/>");
        var html = '';
        var counter = 0;
        $targets.each(function () {
            if (counter >= limit) {
                return false;
            }
            html += '<a class="image-preview-container checkered-background" href="' + $(this).attr('data-url-short') + '" target="_blank">';
            var src = $(this).find('.image-container img');
            if (src.exists()) {
                html += '<canvas width="160" height="160" class="thumb" style="background-image: url(' + src.attr("src") + ');" />';
            } else {
                html += '<canvas width="160" height="160" class="thumb" />';
                html += '<span class="empty icon fas fa-inbox"></span>';
            }
            html += '</a>';
            counter++;
        });
        div.html(template).find('.image-preview').html(html);

        return div.html();
    }
}

CHV.fn.listingViewer = {
    selectors: {
        bodyShown: ".--viewer-shown",
        content: ".viewer-content",
        template: "#viewer-template",
        root: ".viewer",
        rootShow: ".viewer--show",
        rootHide: ".viewer--hide",
        rootZero: ".viewer--zero",
        rootNavPrev: ".viewer--nav-prev",
        rootNavNext: ".viewer--nav-next",
        src: ".viewer-src",
        tools: ".viewer-tools",
        loader: ".viewer-loader",
        owner: ".viewer-owner",
        ownerGuest: ".viewer-owner--guest",
        ownerUser: ".viewer-owner--user",
        inputMap: ".viewer-kb-input",
    },
    keys: {
        "ArrowLeft": "prev",
        "ArrowRight": "next",
        "Delete": "delete",
        "Escape": "close",
        "KeyA": "create-album",
        "KeyE": "edit",
        "KeyF": "flag",
        "KeyL": "like",
        "KeyM": "move",
        "KeyO": "approve",
        "KeyS": "share",
        "KeyW": "zoom",
        "Period": "select",
    },
    keymap: {
        "create-album": ["A", PF.fn._s("Create album")],
        approve: ["O", PF.fn._s("Approve")],
        close: ["Esc", PF.fn._s("Close")],
        delete: ["Del", PF.fn._s("Delete")],
        edit: ["E", PF.fn._s("Edit")],
        flag: ["F", PF.fn._s("Toggle flag")],
        like: ["L", PF.fn._s("Like")],
        move: ["M", PF.fn._n("Move")],
        next: ["►", PF.fn._s("Next")],
        prev: ["◄", PF.fn._s("Previous")],
        select: [".", PF.fn._s("Toggle select")],
        share: ["S", PF.fn._s("Share")],
        zoom: ["W", PF.fn._s("Zoom")],
    },
    loading: null,
    idleTimer: 0,
    $item: null,
    show: function () {
        var paramsHidden = PF.fn.parseQueryString(this.$item.closest(PF.obj.listing.selectors.content_listing_visible).data("params-hidden"));
        this.getEl("root")
            .removeClass(this.selectors.rootHide.substring(1))
            .addClass(this.selectors.rootShow.substring(1));
        $("body").addClass(this.selectors.bodyShown.substring(1));
        var hammertime = new Hammer($(CHV.fn.listingViewer.selectors.root).get(0), {
            direction: Hammer.DIRECTION_VERTICAL,
        });
        hammertime.on("swipeleft swiperight", function (e) {
            // left -> next, right -> prev
            var swipe = e.type.substring("swipe".length) == "left" ? "next" : "prev";
            CHV.fn.listingViewer[swipe]();
        });
        this.getEl("root")[
            (PF.fn.isDevice(["phone", "phablet"]) ?
                "add" :
                "remove"
            ) + "Class"]("--over");
    },
    getItem: function () {
        return this.$item;
    },
    getEl: function (sel) {
        var context =
            sel.startsWith("template") || sel.startsWith("root") ?
                false :
                this.selectors.root;
        return context ? $(this.selectors[sel], context) : $(this.selectors[sel]);
    },
    getObject: function (fresh) {
        if (fresh || typeof this.object == typeof undefined) {
            var json = decodeURIComponent(this.getItem().attr("data-object"));
            this.object = (JSON && JSON.parse(json)) || $.parseJSON(json);
        }
        return this.object;
    },
    placeholderSizing: function () {
        if (!this.getEl("root").exists()) return;
        var vW = Math.max(
            document.documentElement.clientWidth,
            window.innerWidth || 0
        );
        var vH = Math.max(
            document.documentElement.clientHeight,
            window.innerHeight || 0
        );
        var vR = vW / vH;
        var eSrc = this.getEl("src")[0];
        var eW = eSrc.getAttribute("width");
        var eH = eSrc.getAttribute("height");
        var eR = eW / eH;
        var c = vR < eR;
        eSrc.classList.remove("--width-auto", "--height-auto");
        eSrc.classList.add("--" + (c ? "height" : "width") + "-auto");
    },
    filler: function (isOpened) {
        var _this = this;
        var $viewer = this.getEl("root");
        if (isOpened) {
            var $parsed = this.getParsedTemplate();
            $viewer.html($parsed.html());
        }
        $viewer[(this.getItem().hasClass("selected") ? "add" : "remove") + "Class"](
            "selected"
        );
        var navActions = ["prev", "next"];
        $.each(navActions, function (i, v) {
            var navSelector =
                _this.selectors[
                "rootNav" + (v.charAt(0).toUpperCase() + v.slice(1).toLowerCase())
                ];
            var action =
                $(PF.obj.listing.selectors.content_listing_pagination + ":visible")
                    .length > 0 ?
                    "add" :
                    _this.getItem()[v]().exists() ?
                        "add" :
                        "remove";
            $viewer[action + "Class"](navSelector.substring(1));
        });
        $.each(this.getItem().get(0).attributes, function (i, attr) {
            if (!attr.name.startsWith("data-")) return true;
            $viewer.attr(attr.name, attr.value);
        });
        $viewer.attr("data-has-owner", typeof this.object.user == typeof undefined ? "0" : "1");
        var $tools = this.getItem().find(".list-item-image-tools[data-action='list-tools']");
        this.getEl("tools").append($tools.html());
        let $this = this;
        this.getEl("tools").find(".list-tool[data-action]").each(function() {
            $(this).attr("title", $(this).attr("title") + " ("+$this.keymap[$(this).attr("data-action")][0]+")");
        });
        this.placeholderSizing();
        this.trickyLoad();
    },
    zoom: function () {
        this.getEl("root").attr("data-cover", this.getEl("root").attr("data-cover") == "1" ? "0" : "1");
    },
    remove: function () {
        this.getEl("root").remove();
    },
    getParsedTemplate: function () {
        var object = this.getObject(true);
        var template = this.getEl("template").html();
        var matches = template.match(/%(\S+)%/g);
        if (matches) {
            $.each(matches, function (i, v) {
                var handle = v.slice(1, -1).split(".");
                var value = false;
                handle.map(function (k) {
                    var aux = value === false ? object : value;
                    if (typeof aux === "object" && k in aux) {
                        value = aux[k];
                    }
                });
                var regex = new RegExp(v, "g");
                value = typeof value == typeof undefined ? "" : value;
                template = template.replace(regex, value);
            });
        }
        var $template = $(template);
        var $userMeta = $template.find('.viewer-owner--user');
        var $find = $userMeta.find('.user-image:not(.default-user-image)');
        if($find.attr('src') !== '') {
            $find = $userMeta.find('.user-image.default-user-image');
        }
        $find.remove();
        return $template;
    },
    insertEl: function () {
        var $html = this.getParsedTemplate();
        this.getEl("rootZero").remove();
        $html.appendTo("body");
    },
    toggleIdle: function (idle, refresh) {
        var _this = this;
        var refresh = typeof refresh == typeof undefined ? true : refresh;
        $("html")[(idle ? "add" : "remove") + "Class"]("--idle");
        if (!idle) {
            clearTimeout(_this.idleTimer);
            if (refresh) {
                _this.idleTimer = setTimeout(function () {
                    var $fs = $(".fullscreen");
                    var $el = _this.getEl("root");
                    _this.toggleIdle($el.length > 0 && $fs.length == 0);
                }, 5000);
            }
        }
    },
    open: function ($item) {
        if (!$item.exists()) {
            this.getEl("rootZero").remove();
            return;
        }
        this.setItem($item);
        this.insertEl();
        this.filler();
        this.show();
        this.toggleIdle(false); // init idler
        var _this = this;
        this.getEl("root").on("mousemove mouseout", function () {
            _this.toggleIdle(false);
        });
    },
    setItem: function ($item) {
        this.$item = $item;
    },
    trickyLoad: function () {
        if (this.object.image.url == this.object.display_url) {
            return;
        }
        var srcHtml = this.getEl("src").parent().html();
        var $src = $(srcHtml).attr("src", this.object.image.url);
        $src.insertBefore(this.getEl("src"));
        var mediaTarget = $src.eq(0);
        var isVideo = mediaTarget.attr("data-media") == "video";
        if(isVideo) {
            mediaTarget.replaceWith(
                '<video draggable="false" class="viewer-src no-select animate" playsinline autoplay controls src="'+this.object.image.url+'" poster="'+this.object.display_url+'"></video>'
            );
            $src.next().css("opacity", 0);
            setTimeout(function() {
                $src.next().remove();
            }, 200);
        } else {
            mediaTarget.attr("src", this.object.image.url);
        }
        $src.imagesLoaded(function () {
            if(!isVideo) {
                $src.next().remove();
            }
        });
    },
    close: function () {
        var _this = this;
        $(this.selectors.root)
            .removeClass(this.selectors.rootShow.substring(1))
            .addClass(this.selectors.rootHide.substring(1));
        $("body").removeClass(this.selectors.bodyShown.substring(1));
        this.toggleIdle(false, false);
        if (this.getItem() !== null) {
            $(window).scrollTop(this.getItem().offset().top);
        }
        $("html").attr("data-scroll-lock", "1");
        setTimeout(function () {
            _this.remove();
        }, 250);
        setTimeout(function () {
            $("html").removeAttr("data-scroll-lock");
        }, 300);
    },
    browse: function (direction) {
        var $item = this.getItem()[direction]();
        if (!$item.exists()) {
            var $pagination = $(
                "[data-pagination=" + direction + "]",
                PF.obj.listing.selectors.content_listing_pagination + ":visible"
            );
            var href = $pagination.attr("href");
            if (!href) return;
            window.location.href = href + "&viewer=" + direction;
            return;
        }
        this.setItem($item);
        this.filler(true);
        var $loadMore = $(PF.obj.listing.selectors.content_listing_visible).find(
            "[data-action=load-more]"
        );
        var padding = $item[direction + "All"]().length;
        if (
            $loadMore.length > 0 &&
            padding <= 5 &&
            !PF.obj.listing.calling &&
            direction == "next"
        ) {
            $loadMore.trigger("click");
        }
    },
    prev: function () {
        this.browse("prev");
    },
    next: function () {
        this.browse("next");
    },
};

CHV.obj.image_viewer = {
    selector: "#image-viewer",
    container: "#image-viewer",
    navigation: ".image-viewer-navigation",
    loading: "#image-viewer-loading",
    loader: "#image-viewer-loader",
};
CHV.obj.image_viewer.$container = $(CHV.obj.image_viewer.container);
CHV.obj.image_viewer.$navigation = $(CHV.obj.image_viewer.navigation);
CHV.obj.image_viewer.$loading = $(CHV.obj.image_viewer.loading);

CHV.fn.system = {
    checkUpdates: function (callback) {
        $.ajax({
            url: CHEVERETO.api.get.info + "/",
            data: { id: CHEVERETO.id },
            cache: false,
        }).always(function (data, status, XHR) {
            if (typeof callback == "function") {
                callback(XHR);
            }
        });
    },
};
if((navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 0) || navigator.platform === 'iPad') {
    $("html").removeClass("device-nonmobile");
}
CHV.fn.bindSelectableItems = function () {
    var el = "content-listing-wrapper";
    var sel = "#" + el;
    if (!$(sel).exists()) {
        $("#content-listing-tabs")
            .wrap("<div id='" + el + "' />");
    } else if ($(sel).hasClass("ui-selectable")) {
        $(sel).selectable("destroy");
    }
    if (!$("[data-content=list-selection]").exists()) {
        return;
    }
    $("html.device-nonmobile " + sel).selectable({
        delay: 150,
        filter: PF.obj.listing.selectors.list_item,
        cancel: ".content-empty, .header, #tab-share, #tab-info, .viewer-title, .header-link, .top-bar, .content-listing-pagination *, #fullscreen-modal, #top-user, #background-cover, .list-item-desc, .list-item-image-tools, [data-action=load-image], #tab-embeds",
        classes: {
            "ui-selected": "selected",
        },
        selected: function (event, ui) {
            var $this = $(ui.selected);
            CHV.fn.list_editor.selectItem($this);
        },
        unselecting: function (event, ui) {
            var $this = $(ui.unselecting);
            CHV.fn.list_editor.unselectItem($this);
        },
    });
};

CHV.fn.isCachedImage = function (src) {
    var image = new Image();
    image.src = src;
    return image.complete || image.width + image.height > 0;
};

CHV.fn.viewerLoadImage = function () {
    if (CHV.obj.image_viewer.$loading.exists()) {
        CHV.obj.image_viewer.$loading.removeClass("soft-hidden").css({
            zIndex: 2,
        });
        PF.fn.loading.inline(CHV.obj.image_viewer.$loading, {
            color: "white",
            size: "small",
            center: true,
            valign: true,
        });
        CHV.obj.image_viewer.$loading.hide().fadeIn("slow");
    }
    $(CHV.obj.image_viewer.loader).remove();
    if (CHV.obj.image_viewer.image.is_360) {
        PF.fn.loading.destroy(CHV.obj.image_viewer.$loading);
        pannellum.viewer('image-viewer-360', {
            autoLoad: true,
            type: "equirectangular",
            panorama: CHV.obj.image_viewer.image.url,
            preview: CHV.obj.image_viewer.$container.find(".media").eq(0).attr("src"),
            pitch: 2.3,
            yaw: -135.4,
            hfov: 120
        });
        $("#image-viewer-360").removeClass("soft-hidden");
        CHV.obj.image_viewer.$container.find(".media").eq(0).remove();
        return;
    }
    CHV.obj.image_viewer.image.html = CHV.obj.image_viewer.$container.html();
    CHV.obj.image_viewer.$container
        .css("height", CHV.obj.image_viewer.$container.height())
        .prepend(
            $(CHV.obj.image_viewer.image.html).css({
                top: 0,
                zIndex: 0,
                opacity: 0,
                position: "absolute"
            })
        );
    CHV.obj.image_viewer.$container.find(".media").eq(0).css("zIndex", 1);
    var mediaTarget = CHV.obj.image_viewer.$container.find(".media").eq(1);
    var width = mediaTarget.css("width");
    var height = mediaTarget.css("height");
    if(mediaTarget.attr('data-media') === 'video') {
        mediaTarget.replaceWith(
            '<video class="media animate" controls autoplay playsinline width="'+width+'" height="'+height+'" src="'+CHV.obj.image_viewer.image.url+'" poster="'+CHV.obj.image_viewer.image.display_url+'" style="opacity: 0;"></video>'
        );
        mediaTarget.src = CHV.obj.image_viewer.image.url;
    } else {
        mediaTarget.attr("src", CHV.obj.image_viewer.image.url);
    }
    mediaTarget
        .imagesLoaded(function () {
            CHV.obj.image_viewer.$container.find(".media").eq(1).css({ position: "", display: "", opacity: 1});
            CHV.obj.image_viewer.$container.find(".media").eq(0).remove();
            $(CHV.obj.image_viewer.container).css('height', '');
            PF.fn.loading.destroy(CHV.obj.image_viewer.$loading);
        });
};

CHV.obj.embed_share_tpl = {};
CHV.obj.embed_upload_tpl = {};

CHV.obj.topBar = {
    transparencyScrollToggle: function () {
        var Y = $(window).scrollTop();
        $("#top-bar")[(Y > 0 ? "remove" : "add") + "Class"]("transparent");
    },
};

CHV.obj.uploaderReset = {
    isUploading: false,
    canAdd: true,
    queueStatus: "ready",
    uploadThreads: 0,
    uploadParsedIds: [],
    uploadProcessedIds: [],
    files: {},
    results: {
        success: {},
        error: {},
    },
    toggleWorking: 0,
    filesAddId: 0,
    clipboardImages: [],
};

CHV.fn.uploader = {
    files: {},
    selectors: {
        root: "#anywhere-upload",
        show: ".upload-box--show",
        queue: "#anywhere-upload-queue",
        queue_complete: ".queue-complete",
        queue_item: ".queue-item",
        close_cancel: "[data-button=close-cancel]",
        file: "#anywhere-upload-input",
        camera: "#anywhere-upload-input-camera",
        upload_item_template: "#anywhere-upload-item-template",
        item_progress_bar: "[data-content=progress-bar]",
        failed_result: "[data-content=failed-upload-result]",
        fullscreen_mask: "#fullscreen-uploader-mask",
        dropzone: "#uploader-dropzone",
        paste: "#anywhere-upload-paste",
        input: "[data-action=anywhere-upload-input]",
    },
    toggle: function (options, args) {
        this.queueSize();

        var $switch = $("[data-action=top-bar-upload]", ".top-bar");
        var show = !$(CHV.fn.uploader.selectors.root).data("shown");
        var options = $.extend({
            callback: null,
            reset: true,
        },
            options
        );

        if (typeof options.show !== typeof undefined && options.show) {
            show = true;
        }

        PF.fn.growl.close(true);
        PF.fn.close_pops();

        if (
            this.toggleWorking == 1 ||
            $(CHV.fn.uploader.selectors.root).is(":animated") ||
            CHV.fn.uploader.isUploading ||
            ($switch.data("login-needed") && !PF.fn.is_user_logged())
        )
            return;

        this.toggleWorking = 1;

        var animation = {
            time: 500,
            easing: null,
        };
        var callbacks = function () {
            if (!show && options.reset) {
                CHV.fn.uploader.reset();
            }
            PF.fn.topMenu.hide();
            if (typeof options.callback == "function") {
                options.callback(args);
            }
            CHV.fn.uploader.boxSizer();
            CHV.fn.uploader.toggleWorking = 0;
        };

        $(CHV.fn.uploader.selectors.root)[(show ? "add" : "remove") + "Class"](
            this.selectors.show.substring(1)
        );

        if (show) {
            $("html")
                .data({
                    "followed-scroll": $("html").hasClass("followed-scroll"),
                    "top-bar-box-shadow-prevent": true,
                })
                .removeClass("followed-scroll")
                .addClass("overflow-hidden top-bar-box-shadow-none");
            $("#top-bar")
                .data({
                    stock_classes: $("#top-bar").attr("class"),
                })
                .addClass("scroll-up");
            $(".current[data-nav]", ".top-bar").each(function () {
                if ($(this).is("[data-action=top-bar-menu-full]")) return;
                $(this).removeClass("current").attr("data-current", 1);
            });
            if (PF.fn.isDevice("mobile")) {
                var $upload_heading = $(
                    ".upload-box-heading",
                    $(CHV.fn.uploader.selectors.root)
                );
                $upload_heading.css({
                    position: "relative",
                    top: 0.5 * ($(window).height() - $upload_heading.height()) + "px",
                });
            }
            CHV.fn.uploader.focus(function () {
                setTimeout(function () {
                    callbacks();
                }, animation.time);
            });
        } else {
            $("#top-bar")[0].className = $("#top-bar").data('stock_classes');
            $("[data-nav][data-current=1]", ".top-bar").each(function () {
                $(this).addClass("current");
            });
            setTimeout(function () {
                $(CHV.fn.uploader.selectors.fullscreen_mask).css({
                    opacity: 0,
                });
            }, 0.1 * animation.time);
            setTimeout(function () {
                $(CHV.fn.uploader.selectors.fullscreen_mask).remove();
            }, animation.time);

            var _uploadBoxHeight = $(CHV.fn.uploader.selectors.root).outerHeight();
            var _uploadBoxPush =
                _uploadBoxHeight -
                parseInt($(CHV.fn.uploader.selectors.root).data("initial-height")) +
                "px";
            $(CHV.fn.uploader.selectors.root).css({
                transform: "translate(0,-" + _uploadBoxPush + ")",
            });

            setTimeout(function () {
                $(CHV.fn.uploader.selectors.root).css({
                    top: "",
                });
                callbacks();
                $("html,body").removeClass("overflow-hidden").data({
                    "top-bar-box-shadow-prevent": false,
                });
                $("#top-bar *").trigger("blur");
            }, animation.time);
        }

        $(CHV.fn.uploader.selectors.root).data("shown", show);

        $switch.toggleClass("current").removeClass("opened");
    },

    reset: function () {
        $.extend(this, $.extend(true, {}, CHV.obj.uploaderReset));

        $("li", this.selectors.queue).remove();
        $(this.selectors.root).height("").css({
            "overflow-y": "",
            "overflow-x": "",
        });

        $(this.selectors.queue)
            .addClass("queueEmpty")
            .removeClass(this.selectors.queue_complete.substring(1));

        $(this.selectors.input, this.selectors.root).each(function () {
            $(this).prop("value", null);
        });
        $("[data-group=upload-result] textarea", this.selectors.root).prop(
            "value",
            ""
        );
        $.each(
            [
                "upload-queue-ready",
                "uploading",
                "upload-result",
                "upload-queue-ready",
                "upload-queue",
            ],
            function (i, v) {
                $("[data-group=" + v + "]").hide();
            }
        );
        $("[data-group=upload]", this.selectors.root).show();
        // Force HTML album selection (used for upload to current album)
        $("[name=upload-album-id]", this.selectors.root).prop("value", function () {
            var $selected = $("option[selected]", this);
            if ($selected.exists()) {
                return $selected.attr("value");
            }
        });

        $(this.selectors.root)
            .removeClass("queueCompleted queueReady queueHasResults")
            .addClass("queueEmpty")
            .attr("data-queue-size", 0);

        // Always ask for category
        $("[name=upload-category-id]", this.selectors.root).prop("value", "");
        $("[name=upload-nsfw]", this.selectors.root).prop(
            "checked",
            this.defaultChecked
        );

        this.boxSizer(true);
    },

    focus: function (callback) {
        if ($(this.selectors.fullscreen_mask).exists()) return;
        if (!$("body").is("#upload")) {
            $("body").append(
                $("<div/>", {
                    id: this.selectors.fullscreen_mask.replace("#", ""),
                    class: "fullscreen black",
                }).css({
                    top: PF.fn.isDevice("phone") ?
                        0 : $(CHV.fn.uploader.selectors.root).data("top"),
                })
            );
        }
        setTimeout(function () {
            if (!$("body").is("#upload")) {
                $(CHV.fn.uploader.selectors.fullscreen_mask).css({
                    opacity: 1,
                });
            }
            setTimeout(
                function () {
                    if (typeof callback == "function") {
                        callback();
                    }
                },
                PF.fn.isDevice(["phone", "phablet"]) ? 0 : 250
            );
        }, 1);
    },

    boxSizer: function (forced) {
        var shown = $(this.selectors.root).is(this.selectors.show);
        var doit = shown || forced;
        if (shown) {
            $("html").addClass("overflow-hidden");
        }
        if (!doit) return;
        $(this.selectors.root).height("");
        if (!$("body").is("#upload") &&
            $(this.selectors.root).height() > $(window).height()
        ) {
            $(this.selectors.root).height(
                $(window).height() - $("#top-bar").height()
            ).css({
                "overflow-y": "scroll",
                "overflow-x": "auto",
            });
            $("html").addClass("overflow-hidden");
        } else {
            $(this.selectors.root).css("overflow-y", "");
        }
    },

    pasteURL: function () {
        var textarea = $("[name=urls]", PF.obj.modal.selectors.root);
        var value = textarea.val();
        if (value) {
            CHV.fn.uploader.toggle({ show: true });
            CHV.fn.uploader.add({}, value);
        }
    },

    pasteImageHandler: function (e) {
        // Leave the inputs alone
        if ($(e.target).is(":input")) {
            return;
        }
        // Get the items from the clipboard
        if (typeof e.clipboardData !== typeof undefined && e.clipboardData.items) {
            var items = e.clipboardData.items;
        } else {
            setTimeout(function () {
                // Hack to get the items after paste
                e.clipboardData = {};
                e.clipboardData.items = [];
                $.each($("img", CHV.fn.uploader.$pasteCatcher), function (i, v) {
                    e.clipboardData.items.push(PF.fn.dataURItoBlob($(this).attr("src")));
                });
                $(CHV.fn.uploader.selectors.paste).html("");
                return CHV.fn.uploader.pasteImageHandler(e);
            }, 1);
        }
        if (items) {
            const files = new Array();
            const urls = new Array();
            const regex = new RegExp("^(image|video)/", "i");
            let uploaderIsVisible = $(CHV.fn.uploader.selectors.root).data("shown");
            for (var i = 0; i < items.length; i++) {
                if (regex.test(items[i].type)) {
                    let file = items[i].getAsFile();
                    files.push(file);
                } else if (items[i].kind == 'string') {
                    if (!CHV.obj.config.upload.url) {
                        continue;
                    }
                    items[i].getAsString(function (s) {
                        CHV.fn.uploader.add({}, s);
                    })
                    urls.push(i);
                }
            }
            if (files.length == 0 && urls.length == 0) {
                return;
            }

            var pushEvent = {
                originalEvent: {
                    dataTransfer: {
                        files: [...files]
                    },
                    preventDefault: function () { },
                    stopPropagation: function () { },
                }
            }
            if (!uploaderIsVisible) {
                CHV.fn.uploader.toggle({
                    callback: function () {
                        CHV.fn.uploader.add(pushEvent);
                    },
                });
            } else {
                CHV.fn.uploader.add(pushEvent);
            }
        }
    },

    add: function (e, urls) {
        var md5;

        // Prevent add items ?
        if (!this.canAdd) {
            var e = e.originalEvent;
            e.preventDefault();
            e.stopPropagation();
            return false;
        }

        var $file_input = $(this.selectors.file);
        $file_input.replaceWith(($file_input = $file_input.clone(true)));
        var item_queue_template = $(this.selectors.upload_item_template).html();
        let files = [];
        let directories = [];

        function addDirectoryItem(item, files, directories, isLast) {
            if (item.isDirectory) {
                var directoryReader = item.createReader();
                directoryReader.readEntries(function (entries) {
                    var size = entries.length;
                    var i = 0;
                    entries.forEach(function (entry) {
                        i++;
                        if (entry.name === '.DS_Store') {
                            return;
                        }
                        addDirectoryItem(entry, files, directories, size === i);
                    });
                });
                directories.push(item.name)
            } else {
                item.file(function (file) {
                    files.push(file);
                    if (isLast) {
                        CHV.fn.uploader.add({
                            originalEvent: {
                                preventDefault: function () { },
                                stopPropagation: function () { },
                                dataTransfer: {
                                    parsedItems: true,
                                    files: [...files]
                                }
                            }
                        })
                    }
                });
            }
        }

        if (typeof urls == typeof undefined) {
            var e = e.originalEvent;
            e.preventDefault();
            e.stopPropagation();
            var data = e.dataTransfer || e.target;

            if ("items" in data) {
                var items = data.items;
                for (var i = 0; i < items.length; i++) {
                    var item = items[i].webkitGetAsEntry();
                    if (item) {
                        addDirectoryItem(item, files, directories, false);
                    }
                }
            }
            if ("files" in data) {
                files = Array.isArray(data.files)
                    ? data.files.slice()
                    : $.makeArray(data.files);

                files = files.filter(function (o) {
                    return (directories.indexOf(o.name) < 0);
                });
            }

            // Keep a map for the clipboard images
            // if (e.clipboard) {
            //     md5 = PF.fn.md5(e.dataURL);
            //     if ($.inArray(md5, this.clipboardImages) != -1) {
            //         return null;
            //     }
            //     this.clipboardImages.push(md5);
            // }

            // Filter non-images
            var failed_files = [];
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (directories.includes(file.name)) {
                    continue;
                }
                var image_type_str;
                if (typeof file.type == "undefined" || file.type == "") {
                    // Some browsers (Android) don't set the correct file.type
                    image_type_str = file.name
                        .substr(file.name.lastIndexOf(".") + 1)
                        .toLowerCase();
                } else {
                    image_type_str = file.type
                        .replace("image/", "")
                        .replace("video/", "");
                }
                // Size filter
                if (file.size > CHV.obj.config.image.max_filesize.getBytes()) {
                    failed_files.push({
                        uid: i,
                        name: file.name.truncate_middle() + " - " + PF.fn._s("File too big."),
                        error: 'MEDIA_ERR_FILE_SIZE',
                    });
                    continue;
                }
                // Android can output something like image:10 as the full file name so ignore this filter
                if (
                    CHV.obj.config.upload.image_types.indexOf(image_type_str) == -1 &&
                    /android/i.test(navigator.userAgent) == false
                ) {
                    failed_files.push({
                        uid: i,
                        name: file.name.truncate_middle() +
                            " - " +
                            PF.fn._s("Invalid or unsupported file format."),
                        error: 'MEDIA_ERR_FILETYPE',
                    });
                    continue;
                }
                if (md5) {
                    file.md5 = md5;
                }
                file.fromClipboard = e.clipboard == true;
                file.uid = i;
            }
            for (var i = 0; i < failed_files.length; i++) {
                var failed_file = failed_files[i];
                files.splice(failed_file.id, 1);
            }
            if (failed_files.length > 0 && files.length == 0) {
                var failed_message = "";
                for (var i = 0; i < failed_files.length; i++) {
                    failed_message +=
                        "<li>" + PF.fn.htmlEncode(failed_files[i].name) + "</li>";
                }
                PF.fn.modal.simple({
                    title: PF.fn._s("Some files couldn't be added"),
                    message: "<ul>" + "<li>" + failed_message + "</ul>",
                });
                return;
            }

            if (files.length == 0) {
                return;
            }
        } else {
            // Remote files
            // Strip HTML + BBCode
            urls = urls.replace(/(<([^>]+)>)/g, "").replace(/(\[([^\]]+)\])/g, "");
            files = urls.match_urls();
            if (!files) return;
            files = files.array_unique();
            files = $.map(files, function (file, i) {
                return {
                    uid: i,
                    name: file,
                    url: file,
                };
            });
        }

        // Empty current files object?
        if ($.isEmptyObject(this.files)) {
            for (var i = 0; i < files.length; i++) {
                this.files[files[i].uid] = files[i];
                this.filesAddId++;
            }
        } else {
            /**
             * Check duplicates by file name (local and remote)
             * This is basic but is the quickest way to do it
             * Note: it doesn't work on iOS for local files http://stackoverflow.com/questions/18412774/get-real-file-name-in-ios-6-x-filereader
             */
            var current_files = [];
            for (var key in this.files) {
                if (
                    typeof this.files[key] == "undefined" ||
                    typeof this.files[key] == "function"
                )
                    continue;
                current_files.push(encodeURI(this.files[key].name));
            }
            files = $.map(files, function (file, i) {
                if ($.inArray(encodeURI(file.name), current_files) != -1) {
                    return null;
                }
                file.uid = CHV.fn.uploader.filesAddId;
                CHV.fn.uploader.filesAddId++;
                return file;
            });
            for (var i = 0; i < files.length; i++) {
                this.files[files[i].uid] = files[i];
            }
        }

        $(this.selectors.queue, this.selectors.root).append(
            item_queue_template.repeat(files.length)
        );

        $(
            this.selectors.queue +
            " " +
            this.selectors.queue_item +
            ":not([data-id])",
            this.selectors.root
        ).hide(); // hide the stock items

        var failed_before = failed_files,
            failed_files = [],
            j = 0,
            default_options = {
                canvas: true,
                maxWidth: 610,
            };

        function CHVLoadImage(i) {
            if (typeof i == typeof undefined) {
                var i = 0;
            }
            if (!(i in files)) {
                PF.fn.loading.destroy("fullscreen");
                return;
            }
            var file = files[i];
            if (directories.includes(file.name)) {
                return;
            }
            $(
                CHV.fn.uploader.selectors.queue_item + ":not([data-id]) .load-url",
                CHV.fn.uploader.selectors.queue
            )[typeof file.url !== "undefined" ? "show" : "remove"]();

            loadImage.parseMetaData(file.url ? file.url : file, function (data) {
                // Set the queue item placeholder ids
                $(
                    CHV.fn.uploader.selectors.queue_item +
                    ":not([data-id]) .preview:empty",
                    CHV.fn.uploader.selectors.queue
                )
                    .first()
                    .closest("li")
                    .attr("data-id", file.uid);

                function getQueueItem(uid) {
                    return $(
                        CHV.fn.uploader.selectors.queue_item +
                        "[data-id=" + uid +"]",
                        CHV.fn.uploader.selectors.queue
                    );
                }

                function displayQueueIfNotVisible() {
                    if (!$(
                        "[data-group=upload-queue]",
                        CHV.fn.uploader.selectors.root
                    ).is(":visible")) {
                        $(
                            "[data-group=upload-queue]",
                            CHV.fn.uploader.selectors.root
                        ).css("display", "block");
                    }
                }

                function getTitle(file) {
                    var title = null;
                    if (typeof file.name !== typeof undefined) {
                        var basename = PF.fn.baseName(file.name);
                        title = $.trim(
                            basename
                                .substring(0, 100)
                                .capitalizeFirstLetter()
                        );
                    }
                    return title;
                }

                function loadVideo(url, callback) {
                    const video = document.createElement("video");
                    video.onerror = (e) => {
                        const videoError = {
                            1: "MEDIA_ERR_ABORTED",
                            2: "MEDIA_ERR_NETWORK",
                            3: "MEDIA_ERR_DECODE",
                            4: "MEDIA_ERR_SRC_NOT_SUPPORTED",
                        }
                        var error = videoError[video.error.code];
                        callback({ type: "error", error: error })
                        console.error("Error loading video", error)
                    }
                    video.addEventListener("loadedmetadata", function () {
                        const seek = parseInt(video.duration / 4);
                        setTimeout(() => {
                            video.currentTime = seek;
                            video.pause();
                        }, 200);
                        video.addEventListener("seeked", () => {
                            const canvas = document.createElement("canvas");
                            canvas.width = video.videoWidth;
                            canvas.height = video.videoHeight;
                            const ctx = canvas.getContext("2d");
                            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                            ctx.canvas.toBlob(
                                blob => {
                                    callback(video, canvas)
                                },
                                "image/jpeg",
                                0.90
                            );
                        }, false);
                    });
                    if (/iPad|iPhone|iPod|Safari/.test(navigator.userAgent)) {
                        video.autoplay = true;
                        video.playsInline = true;
                        video.muted = true;
                    }
                    video.preload = "metadata";
                    video.src = url;
                }

                function setQueueReady($queue_item, img) {
                    $queue_item.show();
                    $(CHV.fn.uploader.selectors.root)
                        .addClass("queueReady")
                        .removeClass("queueEmpty");
                    $("[data-group=upload-queue-ready]", CHV.fn.uploader.selectors.root).show();
                    $("[data-group=upload]", CHV.fn.uploader.selectors.root).hide();
                    $queue_item.find(".load-url").remove();
                    $queue_item
                        .find(".preview")
                        .removeClass("soft-hidden")
                        .show()
                        .append(img);
                    $img = $queue_item.find(".preview").find("img,canvas");
                    $img.attr("class", "canvas");
                    queue_item_h = $queue_item.height();
                    queue_item_w = $queue_item.width();
                    var img_w = parseInt($img.attr("width")) || $img.width();
                    var img_h = parseInt($img.attr("height")) || $img.height();
                    var img_r = img_w / img_h;
                    $img.hide();
                    if (img_w > img_h || img_w == img_h) {
                        // Landscape
                        var queue_img_h = img_h < queue_item_h ? img_h : queue_item_h;
                        if (img_w > img_h) {
                            $img.height(queue_img_h).width(queue_img_h * img_r);
                        }
                    }
                    if (img_w < img_h || img_w == img_h) {
                        // Portrait
                        var queue_img_w = img_w < queue_item_w ? img_w : queue_item_w;
                        if (img_w < img_h) {
                            $img.width(queue_img_w).height(queue_img_w / img_r);
                        }
                    }
                    if (img_w == img_h) {
                        $img.height(queue_img_h).width(queue_img_w);
                    }
                    $img
                        .css({
                            marginTop: -$img.height() / 2,
                            marginLeft: -$img.width() / 2,
                        })
                        .show();
                    displayQueueIfNotVisible();
                    CHV.fn.uploader.boxSizer();
                }

                function someFilesFailed(j, files, failed_files) {
                    if (j !== files.length) {
                        return;
                    }
                    if (typeof failed_before !== "undefined") {
                        failed_files = failed_files.concat(failed_before);
                    }
                    PF.fn.loading.destroy("fullscreen");
                    if (failed_files.length > 0) {
                        var failed_message = "";
                        for (var i = 0; i < failed_files.length; i++) {
                            failed_message +=
                                "<li>" +
                                PF.fn.htmlEncode(failed_files[i].name) +
                                " - " +
                                PF.fn.htmlEncode(failed_files[i].error) +
                                "</li>";
                            delete CHV.fn.uploader.files[failed_files[i].uid];
                            $(
                                "li[data-id=" + failed_files[i].uid + "]",
                                CHV.fn.uploader.selectors.queue
                            )
                                .find("[data-action=cancel]")
                                .click();
                        }
                        PF.fn.modal.simple({
                            title: PF.fn._s("Some files couldn't be loaded"),
                            message: "<ul>" + failed_message + "</ul>",
                        });
                    } else {
                        CHV.fn.uploader.focus();
                    }
                    CHV.fn.uploader.boxSizer();
                }

                // Load the image (async)
                if(typeof file.type !== "undefined" && file.type.startsWith('video/')) {
                    var $queue_item = getQueueItem(file.uid);
                    var title = getTitle(file);
                    var videoUrl = URL.createObjectURL(file);
                    loadVideo(
                        videoUrl,
                        function(video, canvas) {
                            ++j;
                            // var $queue_item = getQueueItem(file.uid);
                            if (video.type === "error") {
                                failed_files.push({
                                    uid: file.uid,
                                    name: file.name.truncate_middle(),
                                    error: video.error
                                });
                            } else {
                                CHV.fn.uploader.files[file.uid].parsedMeta = {
                                    title: title,
                                    width: video.videoWidth,
                                    height: video.videoHeight,
                                    mimetype: file.type,
                                };
                                setQueueReady($queue_item, canvas);
                            }
                            someFilesFailed(j, files, failed_files);
                        }
                    )
                } else {
                    loadImage(
                        file.url ? file.url : file,
                        function (img, imgData) {
                            ++j;
                            var $queue_item = getQueueItem(file.uid);
                            if (img.type === "error") {
                                failed_files.push({
                                    uid: file.uid,
                                    name: file.name.truncate_middle(),
                                    error: 'MEDIA_ERR_SRC_FORMAT'
                                });
                            } else {
                                displayQueueIfNotVisible();
                                var mimetype = "image/jpeg"; // default for URL uploads
                                if(file.hasOwnProperty("type")) {
                                    mimetype = file.type;
                                } else {
                                    file.type = mimetype;
                                }
                                if (typeof data.buffer !== typeof undefined) {
                                    var buffer = new Uint8Array(data.buffer).subarray(0, 4);
                                    var header = "";
                                    for (var i = 0; i < buffer.length; i++) {
                                        header += buffer[i].toString(16);
                                    }
                                    var header_to_mime = {
                                        "89504e47": "image/png",
                                        "47494638": "image/gif",
                                        "ffd8ffe0": "image/jpeg",
                                        "ffd8ffe1": "image/jpeg",
                                        "ffd8ffe2": "image/jpeg",
                                        "ffd8ffe3": "image/jpeg",
                                        "ffd8ffe8": "image/jpeg"
                                    };
                                    if (typeof header_to_mime[header] !== typeof undefined) {
                                        mimetype = header_to_mime[header];
                                    }
                                }
                                var title = getTitle(file);
                                CHV.fn.uploader.files[file.uid].parsedMeta = {
                                    title: title,
                                    width: imgData.originalWidth,
                                    height: imgData.originalHeight,
                                    mimetype: mimetype,
                                };

                                setQueueReady($queue_item, img);
                            }
                            someFilesFailed(j, files, failed_files);
                        },
                        $.extend({}, default_options, {
                            orientation: data.exif ? data.exif.get("Orientation") : 1,
                        })
                    );
                }

                // Next one
                setTimeout(function () {
                    CHVLoadImage(i + 1);
                }, 25);
            });
        }

        PF.fn.loading.fullscreen();
        CHVLoadImage();
        this.queueSize();
    },

    queueSize: function () {
        $(this.selectors.root).attr("data-queue-size", Object.size(this.files));
        $("[data-text=queue-objects]", this.selectors.root).text(
            PF.fn._n("file", "files", Object.size(this.files))
        );
        $("[data-text=queue-size]", this.selectors.root).text(
            Object.size(this.files)
        );
    },

    queueProgress: function (e, id) {
        var queue_size = Object.size(this.files);
        this.files[id].progress = e.loaded / e.total;
        var progress = 0;
        for (var i = 0; i < queue_size; i++) {
            if (
                typeof this.files[i] == typeof undefined ||
                !("progress" in this.files[i])
            )
                continue;
            progress += this.files[i].progress;
        }
        $("[data-text=queue-progress]", this.selectors.root).text(
            parseInt((100 * progress) / queue_size)
        );
    },

    upload: function ($queue_item) {
        var id = $queue_item.data("id");
        var nextId = $queue_item.next().exists() ?
            $queue_item.next().data("id") :
            false;

        // Already working on this?
        if ($.inArray(id, this.uploadParsedIds) !== -1) {
            if ($queue_item.next().exists()) {
                this.upload($queue_item.next());
            }
            return;
        }

        var self = this;

        this.uploadParsedIds.push(id);

        var f = this.files[id];
        if (typeof f == typeof undefined) {
            return;
        }
        var queue_is_url = typeof f.url !== typeof undefined;
        var source = queue_is_url ? f.url : f;
        var hasForm = typeof f.formValues !== typeof undefined;

        if (typeof f == typeof undefined) {
            if ($queue_item.next().exists()) {
                this.upload($queue_item.next());
            }
            return;
        }

        this.uploadThreads += 1;

        if (this.uploadThreads < CHV.obj.config.upload.threads && nextId) {
            this.upload($queue_item.next());
        }

        this.isUploading = true;

        var form = new FormData();
        var formData = {
            source: null,
            type: queue_is_url ? "url" : "file",
            action: "upload",
            privacy: $("[data-privacy]", this.selectors.root).first().data("privacy"),
            timestamp: this.timestamp,
            auth_token: PF.obj.config.auth_token,
            expiration: $("[name=upload-expiration]", this.selectors.root).val() || '',
            category_id: $("[name=upload-category-id]", this.selectors.root).val() || null,
            nsfw: $("[name=upload-nsfw]", this.selectors.root).prop("checked") ?
                1 : 0,
            album_id: $("[name=upload-album-id]", this.selectors.root).val() || null,
            mimetype: f.type
        };

        // Append URL BLOB source
        if (queue_is_url) {
            formData.source = source;
        } else {
            form.append("source", source, f.name); // Stupid 3rd argument for file
        }
        if (hasForm) {
            // Merge with each queue item form data
            $.each(f.formValues, function (i, v) {
                formData[i.replace(/image_/g, "")] = v;
            });
        }

        $.each(formData, function (i, v) {
            if (v === null) return true;
            form.append(i, v);
        });

        this.files[id].xhr = new XMLHttpRequest();

        $queue_item.removeClass("waiting");
        $(".block.edit, .queue-item-button.edit", $queue_item).remove();

        if (!queue_is_url) {
            this.files[id].xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    CHV.fn.uploader.queueProgress(e, id);
                    percentComplete = parseInt((e.loaded / e.total) * 100);
                    $(CHV.fn.uploader.selectors.item_progress_bar, $queue_item).width(
                        100 - percentComplete + "%"
                    );

                    if (percentComplete == 100) {
                        CHV.fn.uploader.itemLoading($queue_item);
                    }
                }
            };
        } else {
            this.queueSize();
            this.queueProgress({
                loaded: 1,
                total: 1,
            },
                id
            );
            this.itemLoading($queue_item);
        }

        this.files[id].xhr.onreadystatechange = function () {
            var is_error = false;

            if (
                this.readyState == 4 &&
                typeof CHV.fn.uploader.files[id].xhr !== "undefined" &&
                CHV.fn.uploader.files[id].xhr.status !== 0
            ) {
                self.uploadProcessedIds.push(id);
                self.uploadThreads -= 1;

                $(".loading-indicator", $queue_item).remove();
                $queue_item.removeClass("waiting uploading");

                try {
                    var JSONresponse =
                        this.responseType !== "json" ?
                            JSON.parse(this.response) :
                            this.response;
                    if (typeof JSONresponse !== "undefined" && this.status == 200) {
                        $("[data-group=image-link]", $queue_item).attr(
                            "href",
                            JSONresponse.image.path_viewer
                        );
                    } else {
                        if (JSONresponse.error.context == "PDOException") {
                            JSONresponse.error.message = "Database error";
                        }
                        JSONresponse.error.message =
                            PF.fn.htmlEncode(CHV.fn.uploader.files[id].name.truncate_middle()) +
                            " - " +
                            JSONresponse.error.message;
                    }

                    // Save the server response (keeping indexing for results)
                    CHV.fn.uploader.results[this.status == 200 ? "success" : "error"][
                        id
                    ] = JSONresponse;

                    if (this.status !== 200) is_error = true;
                } catch (err) {
                    is_error = true;

                    var err_handle;

                    if (typeof JSONresponse == typeof undefined) {
                        // Server epic error
                        err_handle = {
                            status: 500,
                            statusText: "Internal server error",
                        };
                    } else {
                        err_handle = {
                            status: 400,
                            statusText: JSONresponse.error.message,
                        };
                    }

                    JSONresponse = {
                        status_code: err_handle.status,
                        error: {
                            message: PF.fn.htmlEncode(CHV.fn.uploader.files[id].name.truncate_middle()) +
                                " - Server error (" +
                                err_handle.statusText +
                                ")",
                            code: err_handle.status,
                            context: "XMLHttpRequest",
                        },
                        status_txt: err_handle.statusText,
                    };

                    var error_key = Object.size(CHV.fn.uploader.results.error) + 1;

                    CHV.fn.uploader.results.error[error_key] = JSONresponse;
                }

                $queue_item.addClass(!is_error ? "completed" : "failed");

                if (
                    typeof JSONresponse.error !== "undefined" &&
                    typeof JSONresponse.error.message !== "undefined"
                ) {
                    $queue_item
                        .attr("rel", "tooltip")
                        .data("tiptip", "top")
                        .attr("title", JSONresponse.error.message);
                    PF.fn.bindtipTip($queue_item);
                }

                if (self.uploadThreads < CHV.obj.config.upload.threads && nextId) {
                    CHV.fn.uploader.upload($queue_item.next());
                    $(CHV.fn.uploader.selectors.root).addClass("queueHasResults");
                }

                if (self.uploadProcessedIds.length == Object.size(self.files)) {
                    CHV.fn.uploader.displayResults();
                }

                $(".done", $queue_item).fadeOut();
            }
        };

        this.files[id].xhr.open("POST", PF.obj.config.json_api, true);
        this.files[id].xhr.setRequestHeader("Accept", "application/json");
        this.files[id].xhr.send(form);
    },

    itemLoading: function ($queue_item) {
        PF.fn.loading.inline($(".progress", $queue_item), {
            color: "#FFF",
            size: "normal",
            center: true,
            position: "absolute",
            shadow: true,
        });
        $("[data-action=cancel], [data-action=edit]", $queue_item).hide();
    },

    displayResults: function () {
        CHV.fn.uploader.isUploading = false;

        var group_result = "[data-group=upload-result][data-result=%RESULT%]",
            result_types = ["error", "mixed", "success"],
            results = {};

        for (var i = 0; i < result_types.length; i++) {
            results[result_types[i]] = group_result.replace(
                "%RESULT%",
                result_types[i]
            );
        }

        if (Object.size(this.results.error) > 0) {
            var error_files = [];
            for (var i in this.results.error) {
                if (typeof this.results.error[i] !== "object") continue;
                error_files[i] = this.results.error[i].error.message;
            }
            if (error_files.length > 0) {
                $(this.selectors.failed_result).html(
                    "<li>" + error_files.join("</li><li>") + "</li>"
                );
            }
        } else {
            $(results.error, this.selectors.root).hide();
        }
        if (!window.opener &&
            CHV.obj.config.upload.moderation == 0 &&
            CHV.obj.config.upload.redirect_single_upload &&
            Object.size(this.results.success) == 1 &&
            Object.size(this.results.error) == 0
        ) {
            window.location.href = this.results.success[Object.keys(this.results.success)[0]]
                .image.path_viewer;
            return false;
        }

        $("[data-text=queue-progress]", this.selectors.root).text(100);
        $("[data-group=uploading]", this.selectors.root).hide();

        $(this.selectors.root)
            .removeClass("queueUploading queueHasResults")
            .addClass("queueCompleted");

        $(this.selectors.queue).addClass(
            this.selectors.queue_complete.substring(1)
        );

        // Append the embed codes
        if (
            Object.size(this.results.success) > 0 &&
            $("[data-group=upload-result] textarea", this.selectors.root).exists()
        ) {
            CHV.fn.fillEmbedCodes(
                this.results.success,
                CHV.fn.uploader.selectors.root,
                "val"
            );
        }

        if (
            Object.size(this.results.success) > 0 &&
            Object.size(this.results.error) > 0
        ) {
            $(results.mixed + ", " + results.success, this.selectors.root).show();
        } else if (Object.size(this.results.success) > 0) {
            $(results.success, this.selectors.root).show();
        } else if (Object.size(this.results.error) > 0) {
            $(results.error, this.selectors.root).show();
        }

        if ($(results.success, this.selectors.root).is(":visible")) {
            $(results.success, this.selectors.root)
                .find("[data-group^=user], [data-group=guest]")
                .hide();
            $(results.success, this.selectors.root)
                .find(
                    "[data-group=" + (PF.fn.is_user_logged() ? "user" : "guest") + "]"
                )
                .show();
            var firstKey = Object.keys(this.results.success)[0];
            if (typeof this.results.success[firstKey].image.album !== "undefined") {
                var albums = [];
                for (var key in this.results.success) {
                    var image = this.results.success[key].image;
                    if (
                        image.album &&
                        !!image.album.id_encoded &&
                        albums.indexOf(image.album.id_encoded) == -1
                    ) {
                        albums.push(image.album.id_encoded);
                    }
                }
                var targetAlbum = {
                    link: null,
                    text: null,
                };

                if (albums.length <= 1) {
                    targetAlbum.link = this.results.success[firstKey].image.album.url;
                    targetAlbum.text = this.results.success[firstKey].image.album.name;
                } else {
                    targetAlbum.link = this.results.success[
                        firstKey
                    ].image.user.url_albums;
                    targetAlbum.text = PF.fn._s(
                        "%s's Albums",
                        this.results.success[firstKey].image.user.name_short_html
                    );
                }

                $("[data-text=upload-target]", this.selectors.root).text(
                    targetAlbum.text
                );
                $("[data-link=upload-target]", this.selectors.root).attr(
                    "href",
                    targetAlbum.link
                );

                if (PF.fn.is_user_logged()) {
                    var show_user_stuff = albums.length > 0 ? "album" : "stream";
                    $(
                        "[data-group=user-" + show_user_stuff + "]",
                        this.selectors.root
                    ).show();
                }
            }
        }

        this.boxSizer();
        this.queueStatus = "done";

        if (
            window.opener &&
            typeof CHV.obj.opener.uploadPlugin[window.name] !== typeof undefined
        ) {
            if (CHV.obj.opener.uploadPlugin[window.name].autoInsert) {
                var targetSel = CHV.obj.opener.uploadPlugin[window.name].autoInsert;
                var $target = $(
                    ':input[name="'
                    + targetSel
                    + '"]'
                    ,
                    CHV.fn.uploader.selectors.root
                );
                var value = $target.val();
                if (value) {
                    window.opener.postMessage({
                        id: window.name,
                        message: value,
                    },
                        "*"
                    );
                }
            }
            if(CHV.obj.opener.uploadPlugin[window.name].autoClose) {
                window.close();
            }
        } else {
            $('[data-action="openerPostMessage"]', this.selectors.root).remove();
        }
    },
};

$.extend(CHV.fn.uploader, $.extend(true, {}, CHV.obj.uploaderReset));

CHV.fn.fillEmbedCodes = function (elements, parent, fn) {
    if (typeof fn == "undefined") {
        fn = "val";
    }
    var embed_tpl = CHV.fn.uploader.selectors.root == parent ? "embed_upload_tpl" : "embed_share_tpl";
    var hasFrame = false;
    var hasMedium = false;
    var hasThumb = false;
    $.each(elements, function (key, value) {
        if (typeof value == typeof undefined) return;
        var image = "id_encoded" in value ? value : value.image;
        var flatten_image = Object.flatten(image);
        let itemHasFrame = image.url_frame !== "";
        let itemHasMedium = image.medium.url !== null;
        let itemHasThumb = image.thumb.url !== null;
        if(itemHasFrame) {
            hasFrame = true;
        }
        if(itemHasMedium) {
            hasMedium = true;
        }
        if(itemHasThumb) {
            hasThumb = true;
        }
        $.each(CHV.obj[embed_tpl], function (key, value) {
            $.each(value.options, function (k, v) {
                if(!itemHasFrame && k.startsWith('frame-')) {
                    return;
                }
                if(!itemHasMedium && k.startsWith('medium-')) {
                    return;
                }
                if(!itemHasThumb && k.startsWith('thumb-')) {
                    return;
                }
                var $embed = $("textarea[name=" + k + "]", parent);
                var template = v.template;
                if(typeof template === 'object' && template.hasOwnProperty(flatten_image["type"])
                ) {
                    template = template[flatten_image["type"]]
                }
                if(flatten_image["type"] !== "video") {
                    template = template.replaceAll("%URL_FRAME%", "");
                }
                for (var i in flatten_image) {
                    if (!flatten_image.hasOwnProperty(i)) {
                        continue;
                    }
                    template = template.replace(
                        new RegExp("%" + i.toUpperCase() + "%", "g"),
                        PF.fn.htmlEncode(PF.fn.htmlEncode(flatten_image[i]))
                    );
                }
                let useWhitespace = $embed.data("size") == "thumb" && k !== "thumb-links";
                $embed[fn](
                    $embed.val() +
                    template +
                    (useWhitespace ? " " : "\n")
                );
            });
        });
    });
    $("option[value^=frame]", parent).prop("disabled", !hasFrame);
    $("option[value^=medium-]", parent).prop("disabled", !hasMedium);
    $("option[value^=thumb-]", parent).prop("disabled", !hasThumb);
    $.each(CHV.obj[embed_tpl], function (key, value) {
        $.each(value.options, function (k, v) {
            var $embed = $("textarea[name=" + k + "]", parent);
            $embed[fn]($.trim($embed.val()));
        });
    });
};

CHV.fn.resource_privacy_toggle = function (privacy) {
    CHV.obj.resource.privacy = privacy;
    if (!privacy) privacy = "public";
    $("[data-content=privacy-private]").hide();
    if (privacy !== "public") {
        $("[data-content=privacy-private]").show();
    }
};

CHV.fn.submit_create_album = function () {
    var $modal = $(PF.obj.modal.selectors.root);
    if ($("[name=form-album-name]", $modal).val() == "") {
        PF.fn.growl.call(
            PF.fn._s("You must enter the album name.")
        );
        $("[name=form-album-name]", $modal).highlight();
        return false;
    }
    PF.obj.modal.form_data = {
        action: "create-album",
        type: "album",
        owner: CHV.obj.resource.user.id,
        album: {
            parent_id: $("[name=form-album-parent-id]", $modal).val(),
            name: $("[name=form-album-name]", $modal).val(),
            description: $("[name=form-album-description]", $modal).val(),
            privacy: $("[name=form-privacy]", $modal).val(),
            password: $("[name=form-privacy]", $modal).val() == "password" ?
                $("[name=form-album-password]", $modal).val() : null,
            new: true,
        },
    };
    return true;
};
CHV.fn.complete_create_album = {
    success: function (XHR) {
        var response = XHR.responseJSON.album;
        window.location = response.url;
    },
    error: function (XHR) {
        var response = XHR.responseJSON;
        PF.fn.growl.call(PF.fn._s(response.error.message));
    },
};

CHV.fn.submit_upload_edit = function () {
    var $modal = $(PF.obj.modal.selectors.root),
        new_album = false;

    if (
        $("[data-content=form-new-album]", $modal).is(":visible") &&
        $("[name=form-album-name]", $modal).val() == ""
    ) {
        PF.fn.growl.call(
            PF.fn._s("You must enter the album name.")
        );
        $("[name=form-album-name]", $modal).highlight();
        return false;
    }

    if ($("[data-content=form-new-album]", $modal).is(":visible")) {
        new_album = true;
    }

    PF.obj.modal.form_data = {
        action: new_album ? "create-album" : "move",
        type: "images",
        album: {
            ids: $.map(CHV.fn.uploader.results.success, function (v) {
                return v.image.id_encoded;
            }),
            new: new_album,
        },
    };

    if (new_album) {
        PF.obj.modal.form_data.album.name = $(
            "[name=form-album-name]",
            $modal
        ).val();
        PF.obj.modal.form_data.album.description = $(
            "[name=form-album-description]",
            $modal
        ).val();
        PF.obj.modal.form_data.album.privacy = $(
            "[name=form-privacy]",
            $modal
        ).val();
        if (PF.obj.modal.form_data.album.privacy == "password") {
            PF.obj.modal.form_data.album.password = $(
                "[name=form-album-password]",
                $modal
            ).val();
        }
    } else {
        PF.obj.modal.form_data.album.id = $("[name=form-album-id]", $modal).val();
    }

    return true;
};
CHV.fn.complete_upload_edit = {
    success: function (XHR) {
        var response = XHR.responseJSON.album;
        window.location = response.url;
    },
    error: function (XHR) {
        var response = XHR.responseJSON;
        PF.fn.growl.call(PF.fn._s(response.error.message));
    },
};

CHV.fn.before_image_edit = function () {
    var $modal = $("[data-ajax-deferred='CHV.fn.complete_image_edit']");
    $("[data-content=form-new-album]", $modal).hide();
    $("#move-existing-album", $modal).show();
};
CHV.fn.submit_image_edit = function () {
    var $modal = $(PF.obj.modal.selectors.root),
        new_album = false;

    if (
        $("[data-content=form-new-album]", $modal).is(":visible") &&
        $("[name=form-album-name]", $modal).val() == ""
    ) {
        PF.fn.growl.call(
            PF.fn._s("You must enter the album name.")
        );
        $("[name=form-album-name]", $modal).highlight();
        return false;
    }

    if ($("[data-content=form-new-album]", $modal).is(":visible")) {
        new_album = true;
    }

    PF.obj.modal.form_data = {
        action: "edit",
        edit: "image",
        editing: {
            id: CHV.obj.resource.id,
            tags: $("[name=form-image-tags]", $modal).val() || "",
            category_id: $("[name=form-category-id]", $modal).val() || null,
            title: $("[name=form-image-title]", $modal).val() || null,
            description: $("[name=form-image-description]", $modal).val() || null,
            nsfw: $("[name=form-nsfw]", $modal).prop("checked") ? 1 : 0,
            new_album: new_album,
        },
    };

    if (new_album) {
        PF.obj.modal.form_data.editing.album_privacy = $(
            "[name=form-privacy]",
            $modal
        ).val();
        if (PF.obj.modal.form_data.editing.album_privacy == "password") {
            PF.obj.modal.form_data.editing.album_password = $(
                "[name=form-album-password]",
                $modal
            ).val();
        }
        PF.obj.modal.form_data.editing.album_name = $(
            "[name=form-album-name]",
            $modal
        ).val();
        PF.obj.modal.form_data.editing.album_description = $(
            "[name=form-album-description]",
            $modal
        ).val();
    } else {
        PF.obj.modal.form_data.editing.album_id = $(
            "[name=form-album-id]",
            $modal
        ).val();
    }

    return true;
};
CHV.fn.complete_image_edit = {
    success: function (XHR) {
        var response = XHR.responseJSON.image;

        if (!response.album.id_encoded) response.album.id_encoded = "";

        // Detect album change
        if (CHV.obj.image_viewer.album.id_encoded !== response.album.id_encoded) {
            CHV.obj.image_viewer.album.id_encoded = response.album.id_encoded;
            var slice = {
                html: response.album.slice && response.album.slice.html ?
                    response.album.slice.html : null,
                prev: response.album.slice && response.album.slice.prev ?
                    response.album.slice.prev : null,
                next: response.album.slice && response.album.slice.next ?
                    response.album.slice.next : null,
            };
            $("[data-content=album-slice]").html(slice.html);
            $("[data-content=album-panel-title]")[slice.html ? "show" : "hide"]();
            $("a[data-action=prev]").attr("href", slice.prev);
            $("a[data-action=next]").attr("href", slice.next);
            $("a[data-action]", ".image-viewer-navigation").each(function () {
                $(this)[
                    typeof $(this).attr("href") == "undefined" ?
                        "addClass" :
                        "removeClass"
                ]("hidden");
            });
        }

        CHV.fn.resource_privacy_toggle(response.album.privacy);

        $.each(["description", "title"], function (i, v) {
            var $obj = $("[data-text=image-" + v + "]");
            $obj.html(PF.fn.nl2br(PF.fn.htmlEncode(response[v])));
            if ($obj.html() !== "") {
                $obj.show();
            }
        });

        var tagTemplate = $('[data-template=tag]').html();
        var tags = response.tags;
        var tagsHtml = "";
        $.each(tags, function (i, v) {
            tagsHtml += tagTemplate
                .replaceAll("%url", v.url)
                .replaceAll("%tag", v.name_safe_html);
        });
        $("[data-content=tags]")
            .attr("data-count", tags.length)
            .find('.tag').remove();
        $("[data-content=tags]").append(tagsHtml);

        CHV.fn.common.updateDoctitle(response.title);
        CHV.fn.list_editor.addAlbumtoModals(response.album);
        var $modal = $("[data-submit-fn='CHV.fn.submit_image_edit']");
        $.each(["description", "name", "password"], function (i, v) {
            var $input = $("[name=form-album-" + v + "]", $modal);
            if ($input.is("textarea")) {
                $input.val("").html("");
            } else {
                $input.val("").attr("value", "");
            }
        });
        $("[name=form-privacy] option", $modal).each(function () {
            $(this).removeAttr("selected");
        });
        $("[data-combo-value=password]", $modal).hide();

        // Select the album
        $("[name=form-album-id]", $modal).find("option").removeAttr("selected");
        $("[name=form-album-id]", $modal)
            .find('[value="' + response.album.id_encoded + '"]')
            .attr("selected", true);
    },
};

CHV.fn.albumEdit = {
    before: function () {
        var modal_source = "[data-before-fn='CHV.fn.albumEdit.before']";
        $("[data-action=album-switch]", modal_source).remove();
        var $enableCta = $(CHV.fn.ctaForm.selectors.enable, modal_source);
        CHV.fn.ctaForm.destroy();
        if(CHV.fn.ctaForm.enable) {
            $enableCta.prop("checked", true).trigger("change");
        }
    },
    load: function() {
        var $enableCta = $(CHV.fn.ctaForm.selectors.enable, PF.obj.modal.selectors.root);
        if($enableCta.is(":checked")) {
            $enableCta.prop("checked", true).trigger("change");
        }
    },
    submit: function() {
        var $modal = $(PF.obj.modal.selectors.root);
        if (!$("[name=form-album-name]", $modal).val()) {
            PF.fn.growl.call(
                PF.fn._s("You must enter the album name.")
            );
            $("[name=form-album-name]", $modal).highlight();
            return false;
        }
        PF.obj.modal.form_data = {
            action: "edit",
            edit: "album",
            editing: {
                id: CHV.obj.resource.id,
                name: $("[name=form-album-name]", $modal).val(),
                privacy: $("[name=form-privacy]", $modal).val(),
                description: $("[name=form-album-description]", $modal).val(),
                cta_enable: + CHV.fn.ctaForm.enable,
                cta: JSON.stringify(CHV.fn.ctaForm.array),
            },
        };
        if (PF.obj.modal.form_data.editing.privacy == "password") {
            PF.obj.modal.form_data.editing.password = $(
                "[name=form-album-password]",
                $modal
            ).val();
        }

        return true;
    },
    complete: {
        success: function (XHR) {
            var album = XHR.responseJSON.album;
            $("[data-text=album-name]").html(PF.fn.htmlEncode(album.name));
            $("[data-text=album-description]").html(
                PF.fn.htmlEncode(album.description)
            );
            CHV.fn.resource_privacy_toggle(album.privacy);
            var stock = CHV.obj.resource.type;
            CHV.obj.resource.type = null;
            CHV.fn.list_editor.updateItem($(PF.obj.listing.selectors.list_item, PF.obj.listing.selectors.content_listing_visible), XHR.responseJSON);
            CHV.obj.resource.type = stock;
            $("[data-modal]").each(function () {
                $('option[value="' + album.id_encoded + '"]', this).text(
                    album.name +
                    (album.privacy !== "public" ? " (" + PF.fn._s("private") + ")" : "")
                );
            });
            CHV.fn.common.updateDoctitle(album.name);
            CHV.fn.ctaButtons.render(album.cta_html);
        },
    },
};

CHV.fn.category = {
    formFields: ["id", "name", "url_key", "description"],
    validateForm: function (id) {
        var modal = PF.obj.modal.selectors.root,
            submit = true,
            used_url_key = false;

        if (!CHV.fn.common.validateForm(modal)) {
            return false;
        }

        if (Object.size(CHV.obj.categories) > 0) {
            $.each(CHV.obj.categories, function (i, v) {
                if (typeof id !== "undefined" && v.id == id) return true;
                if (v.url_key == $("[name=form-category-url_key]", modal).val()) {
                    used_url_key = true;
                    return false;
                }
            });
        }
        if (used_url_key) {
            PF.fn.growl.call(PF.fn._s("Category URL key already being used."));
            $("[name=form-category-url_key]", modal).highlight();
            return false;
        }

        return true;
    },
    edit: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("category-id"),
                category = CHV.obj.categories[id],
                modal_source = "[data-modal=" + $this.data("target") + "]";
            $.each(CHV.fn.category.formFields, function (i, v) {
                var i = "form-category-" + v,
                    v = category[v],
                    $input = $("[name=" + i + "]", modal_source);
                if ($input.is("textarea")) {
                    $input.html(PF.fn.htmlEncode(v));
                } else {
                    $input.attr("value", v);
                }
            });
        },
        submit: function () {
            var modal = PF.obj.modal.selectors.root,
                id = $("[name=form-category-id]", modal).val();

            if (!CHV.fn.category.validateForm(id)) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "edit",
                edit: "category",
                editing: {},
            };
            $.each(CHV.fn.category.formFields, function (i, v) {
                PF.obj.modal.form_data.editing[v] = $(
                    "[name=form-category-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var category = XHR.responseJSON.category,
                    parent = "[data-content=category]",
                    parentId = "[data-category-id=" + category.id + "]";
                var parent = parent + parentId;
                $.each(category, function (i, v) {
                    $("[data-content=category-" + i + "]", parent).html(
                        PF.fn.htmlEncode(v)
                    );
                });
                $("[data-link=category-url]", parent).each(function(){
                    $(this).attr("href", category.url)
                });
                CHV.obj.categories[category.id] = category;
            },
        },
    },
    delete: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("category-id"),
                category = CHV.obj.categories[id];
            $this.attr(
                "data-confirm",
                $this.attr("data-confirm").replace("%s", '"' + PF.fn.htmlEncode(category.name) + '"')
            );
        },
        submit: function (id) {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "category",
                deleting: {
                    id: id,
                },
            };
            return true;
        },
        complete: {
            success: function (XHR) {
                var id = XHR.responseJSON.request.deleting.id;
                $("[data-content=category][data-category-id=" + id + "]").remove();
                delete CHV.obj.categories[id];
                PF.fn.growl.call(PF.fn._s("The %s has been deleted.", PF.fn._s("category")));
            },
        },
    },
    add: {
        before: function(e) {
            if(
                CHV.obj.service_limits.CHEVERETO_MAX_CATEGORIES !== 0
                && (Object.size(CHV.obj.categories) + 1) > CHV.obj.service_limits.CHEVERETO_MAX_CATEGORIES
            ) {
                PF.fn.growl.call(
                    "Maximum number of %t% reached (limit %s%)."
                    .replace("%t%", PF.fn._s('Categories'))
                    .replace("%s%", CHV.obj.service_limits.CHEVERETO_MAX_CATEGORIES)
                );

                return false;
            }
        },
        submit: function () {
            var modal = PF.obj.modal.selectors.root;

            if (!CHV.fn.category.validateForm()) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "add-category",
                category: {},
            };
            $.each(CHV.fn.category.formFields, function (i, v) {
                if (v == "id") return;
                PF.obj.modal.form_data.category[v] = $(
                    "[name=form-category-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var category = XHR.responseJSON.category,
                    list = "[data-content=dashboard-categories-list]",
                    html = $("[data-content=category-dashboard-template]").html(),
                    replaces = {};

                $.each(category, function (i, v) {
                    html = html.replace(
                        new RegExp("%" + i.toUpperCase() + "%", "g"),
                        v ? v : ""
                    );
                });

                $(list).append(html);

                if (Object.size(CHV.obj.categories) == 0) {
                    CHV.obj.categories = {};
                }
                CHV.obj.categories[category.id] = category;

                PF.fn.growl.call(
                    PF.fn._s("Category %s added.", '"' + category.name + '"')
                );
            },
        },
    },
};

CHV.fn.tag = {
    formFields: ["id", "name", "description"],
    validateForm: function (id) {
        var modal = PF.obj.modal.selectors.root,
            submit = true,
            used_url_key = false;

        if (!CHV.fn.common.validateForm(modal)) {
            return false;
        }

        return true;
    },
    edit: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("tag-id") || $this.closest("[data-tag-id]").data("tag-id"),
                tag = CHV.obj.tags[id],
                modal_source = "[data-modal=" + $this.data("target") + "]";
            $.each(CHV.fn.tag.formFields, function (i, v) {
                var i = "form-tag-" + v,
                    v = tag[v],
                    $input = $("[name=" + i + "]", modal_source);
                if ($input.is("textarea")) {
                    $input.html(PF.fn.htmlEncode(v));
                } else {
                    $input.attr("value", v);
                }
            });
        },
        submit: function () {
            var modal = PF.obj.modal.selectors.root,
                id = $("[name=form-tag-id]", modal).val();

            if (!CHV.fn.tag.validateForm(id)) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "edit",
                edit: "tag",
                editing: {},
            };
            $.each(CHV.fn.category.formFields, function (i, v) {
                PF.obj.modal.form_data.editing[v] = $(
                    "[name=form-tag-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var tag = XHR.responseJSON.tag,
                    parent = "[data-content=tag]",
                    parentId = "[data-tag-id=" + tag.id + "]";
                var parent = parent + parentId;
                $.each(tag, function (i, v) {
                    $("[data-content=tag-" + i + "]", parent).html(
                        PF.fn.htmlEncode(v)
                    );
                });
                $("[data-link=tag-url]", parent).each(function(){
                    $(this).attr("href", tag.url)
                });
            },
        },
    },
    delete: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("tag-id") || $this.closest("[data-tag-id]").data("tag-id"),
                tag = CHV.obj.tags[id];
            $this.attr(
                "data-confirm",
                $this.attr("data-confirm").replace("%s", '"' + PF.fn.htmlEncode(tag.name) + '"')
            );
        },
        submit: function (id) {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "tag",
                deleting: {
                    id: id,
                },
            };
            return true;
        },
        complete: {
            success: function (XHR) {
                var id = XHR.responseJSON.request.deleting.id;
                delete CHV.obj.tags[id];
                PF.fn.growl.call(PF.fn._s("The %s has been deleted.", PF.fn._s("tag")));
            },
        },
    },
};

CHV.fn.ip_ban = {
    formFields: ["id", "ip", "expires", "message"],
    validateForm: function (id) {
        var modal = PF.obj.modal.selectors.root,
            submit = true,
            already_banned = false,
            ip = $("[name=form-ip_ban-ip]", modal).val();

        if (!CHV.fn.common.validateForm(modal)) {
            return false;
        }

        if (
            $("[name=form-ip_ban-expires]", modal).val() !== "" &&
            /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(
                $("[name=form-ip_ban-expires]", modal).val()
            ) == false
        ) {
            PF.fn.growl.call(PF.fn._s("Invalid expiration date."));
            $("[name=form-ip_ban-expires]", modal).highlight();
            return false;
        }

        if (Object.size(CHV.obj.ip_bans) > 0) {
            $.each(CHV.obj.ip_bans, function (i, v) {
                if (typeof id !== "undefined" && v.id == id) return true;
                if (v.ip == ip) {
                    already_banned = true;
                    return false;
                }
            });
        }
        if (already_banned) {
            PF.fn.growl.call(PF.fn._s("IP %s already banned.", ip));
            $("[name=form-ip_ban-ip]", modal).highlight();
            return false;
        }

        return true;
    },

    add: {
        submit: function () {
            var modal = PF.obj.modal.selectors.root;

            if (!CHV.fn.ip_ban.validateForm()) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "add-ip_ban",
                ip_ban: {},
            };
            $.each(CHV.fn.ip_ban.formFields, function (i, v) {
                if (v == "id") return;
                PF.obj.modal.form_data.ip_ban[v] = $(
                    "[name=form-ip_ban-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var ip_ban = XHR.responseJSON.ip_ban,
                    list = "[data-content=dashboard-ip_bans-list]",
                    html = $("[data-content=ip_ban-dashboard-template]").html(),
                    replaces = {};

                if (typeof html !== "undefined") {
                    $.each(ip_ban, function (i, v) {
                        html = html.replace(
                            new RegExp("%" + i.toUpperCase() + "%", "g"),
                            v ? v : ""
                        );
                    });
                    $(list).append(html);
                }
                if (Object.size(CHV.obj.ip_bans) == 0) {
                    CHV.obj.ip_bans = {};
                }
                CHV.obj.ip_bans[ip_ban.id] = ip_ban;
                $("[data-content=ban_ip]").addClass("hidden");
                $("[data-content=banned_ip]").removeClass("hidden");
                PF.fn.growl.call(PF.fn._s("IP %s banned.", ip_ban.ip));
            },
            error: function (XHR) {
                // experimental
                var error = XHR.responseJSON.error;
                PF.fn.growl.call(PF.fn._s(error.message));
            },
        },
    },

    edit: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("ip_ban-id"),
                target = CHV.obj.ip_bans[id],
                modal_source = "[data-modal=" + $this.data("target") + "]";
            $.each(CHV.fn.ip_ban.formFields, function (i, v) {
                var i = "form-ip_ban-" + v,
                    v = target[v],
                    $input = $("[name=" + i + "]", modal_source);
                if ($input.is("textarea")) {
                    $input.html(PF.fn.htmlEncode(v));
                } else {
                    $input.attr("value", v);
                }
            });
        },
        submit: function () {
            var modal = PF.obj.modal.selectors.root,
                id = $("[name=form-ip_ban-id]", modal).val();

            if (!CHV.fn.ip_ban.validateForm(id)) {
                return false;
            }

            PF.obj.modal.form_data = {
                action: "edit",
                edit: "ip_ban",
                editing: {},
            };
            $.each(CHV.fn.ip_ban.formFields, function (i, v) {
                PF.obj.modal.form_data.editing[v] = $(
                    "[name=form-ip_ban-" + v + "]",
                    modal
                ).val();
            });

            return true;
        },
        complete: {
            success: function (XHR) {
                var ip_ban = XHR.responseJSON.ip_ban,
                    parent = "[data-content=ip_ban][data-ip_ban-id=" + ip_ban.id + "]";

                $.each(ip_ban, function (i, v) {
                    $("[data-content=ip_ban-" + i + "]", parent).html(
                        PF.fn.htmlEncode(v)
                    );
                });
                CHV.obj.ip_bans[ip_ban.id] = ip_ban;
            },
            error: function (XHR) {
                var error = XHR.responseJSON.error;
                PF.fn.growl.call(PF.fn._s(error.message));
            },
        },
    },

    delete: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("ip_ban-id"),
                ip_ban = CHV.obj.ip_bans[id];
            $this.attr(
                "data-confirm",
                $this.attr("data-confirm").replace("%s", ip_ban.ip)
            );
        },
        submit: function (id) {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "ip_ban",
                deleting: {
                    id: id,
                },
            };
            return true;
        },
        complete: {
            success: function (XHR) {
                var id = XHR.responseJSON.request.deleting.id;
                $("[data-content=ip_ban][data-ip_ban-id=" + id + "]").remove();
                delete CHV.obj.ip_bans[id];
            },
        },
    },
};

CHV.fn.storage = {
    formFields: [
        "id",
        "name",
        "api_id",
        "bucket",
        "server",
        "service",
        "capacity",
        "region",
        "key",
        "secret",
        "url",
        "account_id",
        "account_name",
        "type_chain",
        "use_path_style_endpoint"
    ],
    chain: [
        "other",
        "document",
        "audio",
        "video",
        "image",
    ],
    calling: false,
    validateForm: function (parent) {
        var id = $("[name=form-storage-id]", parent).val(),
            submit = true;

        $.each($(":input", parent), function (i, v) {
            if ($(this).is(":hidden")) {
                if ($(this).attr("required")) {
                    $(this).removeAttr("required").attr("data-required", 1);
                }
            } else {
                if ($(this).attr("data-required") == 1) {
                    $(this).attr("required", "required");
                }
            }
            if (
                $(this).is(":visible") &&
                $(this).val() == "" &&
                $(this).attr("required")
            ) {
                $(this).highlight();
                $("label", $(this).closest(".input-label")).shake();
                submit = false;
            }
        });

        if (!submit) {
            return false;
        }

        // Validate storage capacity
        var $storage_capacity = $("[name=form-storage-capacity]", parent),
            storage_capacity = $storage_capacity.val(),
            capacity_error_msg;

        if ($storage_capacity.is(":visible") && storage_capacity !== "") {
            if (
                /^[\d\.]+\s*[A-Za-z]{2}$/.test(storage_capacity) == false ||
                typeof storage_capacity.getBytes() == "undefined"
            ) {
                capacity_error_msg = PF.fn._s(
                    "Invalid storage capacity value. Make sure to use a valid format."
                );
            } else if (
                typeof CHV.obj.storages[id] !== "undefined" &&
                storage_capacity.getBytes() < CHV.obj.storages[id].space_used
            ) {
                capacity_error_msg = PF.fn._s(
                    "Storage capacity can't be lower than its current usage (%s).",
                    CHV.obj.storages[id].space_used.formatBytes()
                );
            }
            if (capacity_error_msg) {
                PF.fn.growl.call(capacity_error_msg);
                $storage_capacity.highlight();
                return false;
            }
        }
        if (
            /^https?:\/\/.+$/.test($("[name=form-storage-url]", parent).val()) == false
        ) {
            PF.fn.growl.call(PF.fn._s("Invalid URL."));
            $("[name=form-storage-url]", parent).highlight();
            return false;
        }
        return true;
    },
    toggleHttps: function (id) {
        this.toggleBool(id, "https");
    },
    toggleActive: function (id) {
        this.toggleBool(id, "active");
    },
    toggleBool: function (id, string) {
        if (this.calling) return;

        this.calling = true;

        var $root = $("[data-storage-id=" + id + "]"),
            $parent = $("[data-content=storage-" + string + "]", $root),
            $el = $("[data-checkbox]", $parent),
            checked = CHV.obj.storages[id]["is_" + string],
            toggle = checked == 0 ? 1 : 0,
            data = {
                action: "edit",
                edit: "storage",
                editing: {
                    id: id,
                },
            };
        data.editing["is_" + string] = toggle;
        if (string == "https") {
            data.editing.url = CHV.obj.storages[id].url;
        }

        PF.fn.loading.fullscreen();

        $.ajax({
            type: "POST",
            data: data,
        }).always(function (data, status, XHR) {
            CHV.fn.storage.calling = false;
            PF.fn.loading.destroy("fullscreen");
            if (typeof data.storage == "undefined") {
                PF.fn.growl.call(data.responseJSON.error.message);
                return;
            }
            var storage = data.storage;
            CHV.obj.storages[storage.id] = storage;
            switch (string) {
                case "https":
                    $("[data-content=storage-url]", $root).html(storage.url);
                    break;
            }
            CHV.fn.storage.toggleBoolDisplay($el, toggle);
        });
    },
    edit: {
        before: function (e) {
            if(typeof e === "object") {
                var $this = $(e.target),
                    id = $this.data("storage-id");
            } else {
                var id = e;
            }
            var storage = CHV.obj.storages[id];
            var combo = "[data-combo-value~=" + storage["api_id"] + "]";
            $.each(CHV.fn.storage.formFields, function (i, v) {
                var i = "form-storage-" + v,
                    v = storage[v],
                    $combo_input = $(combo + " [name=" + i + "]"),
                    $global_input = $("[name=" + i + "]"),
                    $input = $combo_input.exists() ? $combo_input : $global_input;
                $input.each(function() {
                    if ($(this).is("textarea")) {
                        $(this).html(PF.fn.htmlEncode(v));
                    } else if ($(this).is("input:checkbox")) {
                        $(this).prop("checked", v == 1);
                        $(this).attr("checked", v == 1);
                    } else if ($(this).is("select")) {
                        $("option", $(this)).removeAttr("selected");
                        $("option", $(this)).each(function () {
                            if ($(this).attr("value") == v) {
                                $(this).attr("selected", "selected");
                                return false;
                            }
                        });
                    } else {
                        if (
                            $(this).is("[name=form-storage-capacity]") &&
                            typeof v !== "undefined" &&
                            v > 0
                        ) {
                            v = String(v).formatBytes(2);
                        }
                        $(this).attr("value", v);
                    }
                    if(i === "form-storage-type_chain") {
                        let chain = (parseInt(v) >>> 0)
                            .toString(2)
                            .paddingLeft(
                                "0".repeat(CHV.fn.storage.chain.length)
                            )
                            .split("");
                        CHV.fn.storage.chain.forEach(function(key, i) {
                            $('#storage_type_enable_'+key)
                                .removeAttr("checked")
                                .attr("checked", chain[i] == 1);
                        });
                    }
                });

            });
            $("[data-combo-value]").addClass("soft-hidden");
            $(combo).removeClass("soft-hidden");
            CHV.fn.storage.prepareForm(storage["api_id"], true);
        },
        submit: function () {
            var modal = PF.obj.modal.selectors.root,
                id = $("[name=form-storage-id]", modal).val(),
                used_url_key = false;

            if (!CHV.fn.storage.validateForm(PF.obj.modal.selectors.root)) {
                return false;
            }
            PF.obj.modal.form_data = {
                action: "edit",
                edit: "storage",
                editing: {},
            };
            $.each(CHV.fn.storage.formFields, function (i, v) {
                let sel, val;
                sel = "[name=form-storage-" + v + "]";
                if ($(sel, modal).attr("type") !== "hidden") {
                    sel += ":visible";
                }
                val = $(sel, modal).val();
                if($(sel, modal).is("input:checkbox")) {
                    val = $(sel, modal).prop("checked") ? '1' : '0';
                }
                PF.obj.modal.form_data.editing[v] = val;
            });
            let chain = CHV.fn.storage.chain.map(function(key) {
                return $('#storage_type_enable_'+key, modal).prop("checked") ? 1 : 0;
            });
            PF.obj.modal.form_data.editing.type_chain = parseInt(chain.join(""), 2);

            return true;
        },
        complete: {
            success: function (XHR) {
                var storage = XHR.responseJSON.storage,
                    parent = "[data-content=storage][data-storage-id=" + storage.id + "]",
                    $el = $("[data-action=toggle-storage-https]", parent);
                $.each(storage, function (i, v) {
                    $("[data-content=storage-" + i + "]", parent).html(
                        PF.fn.htmlEncode(v)
                    );
                });
                CHV.obj.storages[storage.id] = storage;
                CHV.fn.storage.toggleBoolDisplay($el, storage["is_https"] == 1);
            },
            error: function (XHR) {
                var response = XHR.responseJSON,
                    message = response.error.message;
                PF.fn.growl.call({
                    message: message,
                    insertTo: "#fullscreen-modal-box #growl-placeholder",
                });
            },
        },
    },
    add: {
        before: function(e) {
            if(CHV.obj.service_limits.CHEVERETO_MAX_STORAGES !== 0
                && (Object.size(CHV.obj.storages) + 1) > CHV.obj.service_limits.CHEVERETO_MAX_STORAGES
            ) {
                PF.fn.growl.call(
                    "Maximum number of %t% reached (limit %s%)."
                    .replace("%t%", PF.fn._s('External storage'))
                    .replace("%s%", CHV.obj.service_limits.CHEVERETO_MAX_STORAGES)
                );

                return false;
            }
            var api_id = $("select[name=form-storage-api_id]").prop("value");
            $.each(CHV.fn.storage.formFields, function (i, v) {
                if(v === 'api_id') {
                    return;
                }
                var i = "form-storage-" + v;
                var $input = $("[name=" + i + "]");
                $input.each(function() {
                     if($(this).is(":checkbox")) {
                        let checked = $(this).is('[data-checked="1"]');
                        if(checked) {
                            $(this).attr("checked", "checked");
                        } else {
                            $(this).removeAttr("checked");
                        }
                        $(this).prop("checked", checked);
                     } else {
                        $(this)
                            .val("")
                            .prop("value", "")
                            .attr("value", "");
                     }
                })
            });
            CHV.fn.storage.prepareForm(api_id, true);
        },
        submit: function () {
            if (!CHV.fn.storage.validateForm(PF.obj.modal.selectors.root)) {
                return false;
            }
            var modal = PF.obj.modal.selectors.root;

            PF.obj.modal.form_data = {
                action: "add-storage",
                storage: {},
            };
            $.each(CHV.fn.storage.formFields, function (i, v) {
                let sel, val;
                sel = "[name=form-storage-" + v + "]";
                if ($(sel, modal).attr("type") !== "hidden") {
                    sel += ":visible";
                }
                val = $(sel, modal).val();
                if($(sel, modal).is("input:checkbox")) {
                    val = $(sel, modal).prop("checked") ? '1' : '0';
                }
                PF.obj.modal.form_data.storage[v] = val;
            });
            let chain = CHV.fn.storage.chain.map(function(key) {
                return $('#storage_type_enable_'+key, modal).prop("checked") ? 1 : 0;
            });
            PF.obj.modal.form_data.storage.type_chain = parseInt(chain.join(""), 2);

            return true;
        },
        complete: {
            success: function (XHR) {
                var storage = XHR.responseJSON.storage,
                    list = "[data-content=dashboard-storages-list]",
                    html = $("[data-content=storage-dashboard-template]").html(),
                    replaces = {};

                $.each(storage, function (i, v) {
                    var upper = i.toUpperCase();
                    if (i == "is_https" || i == "is_active") {
                        var v = CHV.obj.storageTemplate.icon
                            .replace("%TITLE%", CHV.obj.storageTemplate.messages[i])
                            .replace("%ICON%", CHV.obj.storageTemplate.checkboxes[v])
                            .replace("%PROP%", i.replace("is_", ""));
                    }
                    html = html.replace(new RegExp("%" + upper + "%", "g"), v ? v : "");
                });

                $(list).append(html);

                PF.fn.bindtipTip($("[data-storage-id=" + storage.id + "]"));

                if (CHV.obj.storages.length == 0) {
                    CHV.obj.storages = {};
                }
                CHV.obj.storages[storage.id] = storage;
            },
            error: function (XHR) {
                var response = XHR.responseJSON,
                    message = response.error.message;
                PF.fn.growl.call({
                    message: message,
                    insertTo: "#fullscreen-modal-box #growl-placeholder",
                });
            },
        },
    },
    delete: {
        before: function (e) {
            var $this = $(e.target),
                id = $this.data("storage-id"),
                storage = CHV.obj.storages[id];
            $this.attr(
                "data-confirm",
                $this.attr("data-confirm").replace("%s", '"' + storage.name + '"' + " (ID " + storage.id + ")" )
            );
        },
        submit: function (id) {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "storage",
                deleting: {
                    id: id,
                },
            };
            return true;
        },
        complete: {
            success: function (XHR) {
                var id = XHR.responseJSON.request.deleting.id;
                $("[data-content=storage][data-storage-id=" + id + "]").remove();
                delete CHV.obj.storages[id];
            },
            error: function (XHR) {
                PF.fn.growl.call(
                    XHR.responseJSON.error.message
                );
            }
        }
    },
    toggleBoolDisplay: function ($el, toggle) {
        var icons = {
            0: $el.data("unchecked-icon"),
            1: $el.data("checked-icon"),
        };
        $el.removeClass(icons[0] + " " + icons[1]).addClass(icons[toggle ? 1 : 0]);
    },
    prepareForm: function(api_id, trigger) {
        var combo = "[data-combo-value~=" + api_id + "]";
        var trigger = typeof trigger !== "undefined" ? trigger : false;
        $(":input", "[data-combo-value]:hidden").each(function() {
            if($(this).attr("disabled") && !$(this).is("[data-hide-disabled]")) {
                return;
            }
            $(this).prop("disabled", true);
            $(this).attr("data-hide-disabled", 1);
        });
        $(":input", combo).each(function() {
            if(!$(this).is("[data-hide-disabled]")) {
                return;
            }
            $(this).prop("disabled", false);
            $(this).removeAttr("data-hide-disabled");
        });
        if(trigger) {
            setTimeout(function() {
                $("#form-storage-api_id").trigger("change");
            }, 1);
        }
    }
};

CHV.fn.common = {
    validateForm: function (modal) {
        if (typeof modal == "undefined") {
            var modal = PF.obj.modal.selectors.root;
        }
        var submit = true;
        $.each($(":input:visible", modal), function (i, v) {
            if ($(this).val() == "" && $(this).attr("required")) {
                $(this).highlight();
                submit = false;
            }
        });
        if (!submit) {
            return false;
        }

        return true;
    },
    updateDoctitle: function (pre_doctitle) {
        if (typeof CHV.obj.page_info !== typeof undefined) {
            CHV.obj.page_info.pre_doctitle = pre_doctitle;
            CHV.obj.page_info.doctitle =
                CHV.obj.page_info.pre_doctitle + CHV.obj.page_info.pos_doctitle;
            document.title = CHV.obj.page_info.doctitle;
        }
    },
};

CHV.fn.user = {
    add: {
        before: function() {
            if(CHV.obj.service_limits.CHEVERETO_MAX_USERS !== 0
                && (CHV.obj.stat_totals.users + 1) > CHV.obj.service_limits.CHEVERETO_MAX_USERS
            ) {
                PF.fn.growl.call(
                    "Maximum number of %t% reached (limit %s%)."
                    .replace("%t%", PF.fn._s('Users'))
                    .replace("%s%", CHV.obj.service_limits.CHEVERETO_MAX_USERS)
                );

                return false;
            }
        },
        submit: function () {
            var $modal = $(PF.obj.modal.selectors.root),
                submit = true;
            $.each($(":input", $modal), function (i, v) {
                if ($(this).val() == "" && $(this).attr("required")) {
                    $(this).highlight();
                    submit = false;
                }
            });
            if (!submit) {
                return false;
            }
            PF.obj.modal.form_data = {
                action: "add-user",
                user: {
                    username: $("[name=form-username]", $modal).val(),
                    email: $("[name=form-email]", $modal).val(),
                    password: $("[name=form-password]", $modal).val(),
                    role: $("[name=form-role]", $modal).val(),
                },
            };

            return true;
        },
        complete: {
            success: function (XHR) {
                var response = XHR.responseJSON;
            },
            error: function (XHR) {
                var response = XHR.responseJSON;
                PF.fn.growl.call(
                    PF.fn._s(response.error.message)
                );
            },
        },
    },
    delete: {
        submit: function () {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "user",
                owner: CHV.obj.resource.user.id,
                deleting: CHV.obj.resource.user,
            };
            return true;
        },
    },
    ban: {
        submit: function () {
            PF.obj.modal.form_data = {
                action: "ban",
                ban: "user",
                banning: CHV.obj.resource.user.id,
            };
            return true;
        },
        success: function () {

        }
    }
};

CHV.fn.submit_resource_approve = function () {
    PF.obj.modal.form_data = {
        action: "approve",
        approve: CHV.obj.resource.type,
        from: "resource",
        owner: typeof CHV.obj.resource.user !== "undefined" ?
            CHV.obj.resource.user.id : null,
        approving: CHV.obj.resource,
    };
    return true;
};
CHV.fn.complete_resource_approve = {
    success: function (XHR) {
        $("body").fadeOut("normal", function () {
            document.location.replace(CHV.obj.resource.url.addURLParameterNoCache());
        });
    },
};

CHV.fn.submit_resource_delete = function () {
    PF.obj.modal.form_data = {
        action: "delete",
        delete: CHV.obj.resource.type,
        from: "resource",
        owner: typeof CHV.obj.resource.user !== "undefined" ?
            CHV.obj.resource.user.id : null,
        deleting: CHV.obj.resource,
    };
    return true;
};
CHV.fn.complete_resource_delete = {
    success: function (XHR) {
        $("body").fadeOut("normal", function () {
            document.location.replace(CHV.obj.resource.url.addURLParameterNoCache());
        });
    },
};

CHV.fn.list_editor = {
    blink: function ($target) {
        $target.addClass('ui-selecting');
        setTimeout(function () {
            $target.removeClass('ui-selecting');
        }, 200);
    },
    selectionCount: function () {
        var $content_listing = $(PF.obj.listing.selectors.content_listing);
        $content_listing.each(function () {
            var all_count = $(PF.obj.listing.selectors.list_item, this).length;
            var listingId = $(this).attr("id");
            var $list_selection = $("[data-content=list-selection][data-tab=" + listingId + "]");
            var $listing_options = $(
                "[data-content=pop-selection]",
                $list_selection
            );
            var selection_count = $(
                PF.obj.listing.selectors.list_item + ".selected",
                this
            ).length;
            $list_selection.attr('data-selected-count', selection_count);
            $listing_options.toggleClass("disabled", all_count === 0 || selection_count  === 0);
            $("[data-text=selection-count]", $listing_options).text(
                selection_count > 0
                    ? selection_count
                    : ""
            );
            if ($content_listing.data("list") == "images" && selection_count > 0) {
                var has_sfw =
                    $(
                        PF.obj.listing.selectors.list_item + ".selected[data-flag=safe]",
                        this
                    ).length > 0,
                    has_nsfw =
                        $(
                            PF.obj.listing.selectors.list_item +
                            ".selected[data-flag=unsafe]",
                            this
                        ).length > 0;
                $("[data-action=flag-safe]", $listing_options).parent()[
                    (has_nsfw ? "remove" : "add") + "Class"
                ]("hidden");
                $("[data-action=flag-unsafe]", $listing_options).parent()[
                    (has_sfw ? "remove" : "add") + "Class"
                ]("hidden");
            }
            if ($(this).is(":visible")) {
                $("body").toggleClass('--has-selection', selection_count > 0);
                CHV.fn.list_editor.listMassActionSet(
                    all_count == selection_count ? "clear" : "select"
                );
            }
        });
    },

    removeFromList: function ($target, msg) {
        if (typeof $target == "undefined") return;

        var $target = $target instanceof jQuery == false ? $($target) : $target,
            $content_listing = $(PF.obj.listing.selectors.content_listing_visible),
            target_size = $target.length;

        $target.fadeOut("fast"); // Promise

        // Update counts
        var type = $target.first().data("type"),
            new_count =
                parseInt($("[data-text=" + type + "-count]").text()) - target_size;

        CHV.fn.list_editor.updateUserCounters(
            $target.first().data("type"),
            target_size,
            "-"
        );

        $target.promise().done(function () {
            $(document).removeClass(
                CHV.fn.listingViewer.selectors.bodyShown.substr(1)
            );

            // Get count related to each list
            var affected_content_lists = {};
            $target.each(function () {
                $("[data-id=" + $(this).data("id") + "]").each(function () {
                    var list_id = $(this)
                        .closest(PF.obj.listing.selectors.content_listing)
                        .attr("id");

                    if (!affected_content_lists[list_id]) {
                        affected_content_lists[list_id] = 0;
                    }
                    affected_content_lists[list_id] += 1;
                });
            });

            if (target_size == 1) {
                $("[data-id=" + $(this).data("id") + "]").remove();
            } else {
                $target.each(function () {
                    $("[data-id=" + $(this).data("id") + "]").remove();
                });
            }

            PF.fn.listing.columnizerQueue();
            PF.fn.listing.refresh();

            CHV.fn.list_editor.selectionCount();

            if (typeof msg !== "undefined" && typeof msg == "string") {
                PF.fn.growl.call(msg);
            }
            if (!$(
                PF.obj.listing.selectors.content_listing_pagination,
                $content_listing
            ).exists() &&
                $(PF.obj.listing.selectors.list_item, $content_listing).length == 0
            ) {
                new_count = 0;
            }

            // On zero add the empty template
            if (new_count == 0) {
                $content_listing.html(PF.obj.listing.template.empty);
                // Reset ajaxed status of all
                $(
                    PF.obj.listing.selectors.content_listing +
                    ":not(" +
                    PF.obj.listing.selectors.content_listing_visible +
                    ")"
                ).data({
                    empty: null,
                    load: "ajax",
                });
                $(
                    "[data-content=list-selection][data-tab=" +
                    $content_listing.attr("id") +
                    "]"
                ).addClass("disabled");
            } else {
                // Count isn't zero.. But the view?
                if (
                    $(PF.obj.listing.selectors.list_item, $content_listing).length == 0
                ) {
                    $(PF.obj.listing.selectors.pad_content).height(0);
                    if ($("[data-action=load-more]", $content_listing).exists()) {
                        $(PF.obj.listing.selectors.content_listing_visible).data("page", 0);
                        $("[data-action=load-more]", $content_listing).trigger("click");
                        PF.obj.listing.recolumnize = true;
                        return;
                    }
                    var $pagNext = $("[data-pagination=next]", $content_listing);
                    if ($pagNext.exists()) {
                        var hrefNext = $pagNext.attr("href");
                        var params = PF.fn.deparam(hrefNext);
                        if ("page" in params && params.page > 1) {
                            hrefNext = hrefNext.changeURLParameterValue(
                                "page",
                                params.page - 1
                            );
                        }
                        window.location = hrefNext;
                        return;
                    }
                }
            }
        });
    },

    deleteFromList: function ($target) {
        if (typeof growl == "undefined") {
            var growl = true;
        }
        var $target = $target instanceof jQuery == false ? $($target) : $target;
        this.removeFromList($target);
    },

    moveFromList: function ($target, growl) {
        if (typeof growl == "undefined") {
            var growl = true;
        }
        var $target = $target instanceof jQuery == false ? $($target) : $target;
        this.removeFromList($target);
    },

    toggleSelectItem: function ($list_item, select) {
        if (typeof select !== "boolean") {
            var select = !$list_item.hasClass('selected');
        }
        var $target = $(".viewer").is(":visible")
            ? $("[data-type=image][data-id=" + $list_item.attr("data-id") + "]")
            : $list_item;
        var $icon = $("[data-action=select] .btn-icon", $target);
        var add_class, remove_class, label_text;
        if ($target.hasClass('unselect')) {
            return;
        }
        $target.addClass("unselect");
        if (!select) {
            $target.removeClass("selected ui-selected");
            add_class = $icon.data("icon-unselected");
            remove_class = $icon.data("icon-selected");
            label_text = PF.fn._s("Select");
        } else {
            if(Boolean(window.navigator.vibrate)) {
                window.navigator.vibrate([15, 125, 25]);
            }
            $target.addClass("selected");
            add_class = $icon.data("icon-selected");
            remove_class = $icon.data("icon-unselected");
            label_text = PF.fn._s("Unselect");
        }
        $icon.removeClass(remove_class).addClass(add_class);
        $target.removeClass("unselect")
        $("[data-action=select] .label", $target).text(label_text);
        CHV.fn.list_editor.selectionCount();
    },
    selectItem: function ($list_item) {
        this.toggleSelectItem($list_item, true);
    },
    unselectItem: function ($list_item) {
        this.toggleSelectItem($list_item, false);
    },
    selectAll: function (e) {
        var $targets = $(PF.obj.listing.selectors.list_item + ":visible:not(.selected)");
        this.selectItem($targets);
        this.listMassActionSet("clear");
        e.stopPropagation();
    },
    clearSelected: function () {
        var $targets = $(
            PF.obj.listing.selectors.list_item + ".selected",
            PF.obj.listing.selectors.content_listing_visible
        );
        this.unselectItem($targets);
        this.listMassActionSet("select");
    },

    listMassActionSet: function (action) {
        var current = action == "select" ? "clear" : "select";
        var $target = $("[data-text-select-all][data-action=list-" + current + "-all]:visible");
        var text = $target.data("text-" + action + "-all");
        $target.text(text)
            .attr("data-action", "list-" + action + "-all");
    },

    updateItem: function ($target, response, action, growl) {
        console.log('que mierda')
        var album_name;
        if ($target instanceof jQuery == false) {
            var $target = $($target);
        }
        var dealing_with = $target.data("type"),
            album = dealing_with == "image" ? response.album : response;
        this.addAlbumtoModals(album);
        $('option[value="' + album.id_encoded + '"]', "[name=form-album-id]").html(
            PF.fn.htmlEncode(album.name_with_privacy_readable_html)
        );
        if (typeof action == "undefined") {
            var action = "edit";
        }
        if (action == "edit" || action == "move") {
            if (action == "move" && CHV.obj.resource.type == "album") {
                console.log("Moving from album", $target, growl);
                CHV.fn.list_editor.moveFromList($target, growl);
                return;
            }
            $target.attr("data-description", response.description);
            if (dealing_with == "image") {
                if (typeof response.title !== typeof undefined) {
                    $target.attr("data-title", response.title);
                    $target.find("[title]").attr("title", response.title);
                    $("[data-text=image-title]", $target).text(
                        PF.fn.htmlEncode(response.title)
                    );
                }
                if (typeof response.title_truncated !== typeof undefined) {
                    $("[data-text=image-title-truncated]", $target).html(
                        PF.fn.htmlEncode(response.title_truncated)
                    );
                }
                if (typeof response.category_id !== typeof undefined) {
                    $target.attr("data-category-id", response.category_id);
                }
                $target.attr({
                    "data-tags": response.tags_string,
                    "data-album-id": album.id_encoded,
                    "data-flag": response.nsfw == 1 ? "unsafe" : "safe",
                });
                $("[data-content=album-link]", $target).attr("href", album.url);
            } else {
                album_name = PF.fn.htmlEncode(album.name);
                $target.attr({
                    "data-description": album.description,
                    "data-password": album.password,
                    "data-name": album_name,
                });
            }
            $target.attr("data-privacy", album.privacy);
            $("[data-text=album-name]", $target).html(album_name);
        }
    },

    addAlbumtoModals: function (album) {
        var added = false;
        $("[name=form-album-id]", "[data-modal]").each(function () {
            if (
                album.id_encoded &&
                !$('option[value="' + album.id_encoded + '"]', this).exists()
            ) {
                $(this).append(
                    '<option value="' +
                    album.id_encoded +
                    '">' +
                    album.name_with_privacy_readable_html +
                    "</option>"
                );
                added = true;
            }
        });
        if (added) {
            CHV.fn.list_editor.updateUserCounters("album", 1, "+");
        }
    },

    updateAlbum: function (album) {
        $("[data-id=" + album.id_encoded + "]").each(function () {
            if (album.html !== "") {
                $(this).after(album.html);
                $(this).remove();
            }
        });
    },

    updateUserCounters: function (counter, number, operation) {
        if (typeof operation == "undefined") {
            var operation = "+";
        }

        // Current resource counter
        var $count = $("[data-text=" + counter + "-count]"),
            $count_label = $("[data-text=" + counter + "-label]"),
            number = parseInt(number),
            old_count = parseInt($count.html()),
            new_count,
            delta;

        switch (operation) {
            case "+":
                new_count = old_count + number;
                break;
            case "-":
                new_count = old_count - number;
                break;
            case "=":
                new_count = number;
                break;
        }

        delta = new_count - old_count;

        // Total counter
        var $total_count = $("[data-text=total-" + $count.data("text") + "]"),
            $total_count_label = $(
                "[data-text=" + $total_count.data("text") + "-label]"
            ),
            old_total_count = parseInt($total_count.html()),
            new_total_count = old_total_count + delta;

        $count.text(new_count);
        $total_count.text(new_total_count);
        $count_label.text(
            $count_label.data(new_count == 1 ? "label-single" : "label-plural")
        );
        $total_count_label.text(
            $count_label.data(new_total_count == 1 ? "label-single" : "label-plural")
        );
    },

    updateMoveItemLists: function (response, dealing_with, $targets) {
        CHV.fn.list_editor.clearSelected();
        if (/image/.test(dealing_with)) {
            if (dealing_with == "image") {
                // single
                CHV.fn.list_editor.updateItem(
                    "[data-type=image][data-id=" + $targets.data("id") + "]",
                    response.image,
                    "move"
                );
            } else {
                $targets.each(function () {
                    CHV.fn.list_editor.updateItem(
                        "[data-type=image][data-id=" + $(this).data("id") + "]",
                        response,
                        "move",
                        false
                    );
                });
            }
        } else {
            CHV.fn.list_editor.moveFromList($targets, false);
            if (response.album) {
                if (
                    typeof response.albums_old !== "undefined" ?
                        response.request.album.new == "true" :
                        response.request.editing.new_album == "true"
                ) {
                    // Add option select to modals
                    CHV.fn.list_editor.addAlbumtoModals(response.album);

                    var old_count = parseInt($("[data-text=album-count]").text()) - 1;

                    $(PF.obj.listing.selectors.pad_content).each(function () {
                        var list_count = $(this).find(PF.obj.listing.selectors.list_item)
                            .length;

                        if (list_count == 0) {
                            return;
                        }

                        var params = PF.fn.parseQueryString(
                            $(this)
                                .closest(PF.obj.listing.selectors.content_listing)
                                .data("params")
                        );

                        if (params.sort == "date_desc" || old_count == list_count) {
                            $(this)[params.sort == "date_desc" ? "prepend" : "append"](
                                response.album.html
                            );
                        }
                    });
                } else {
                    CHV.fn.list_editor.updateAlbum(response.album);
                }
            }

            PF.fn.listing.columnizerQueue();
            PF.fn.listing.refresh(0);
        }
    },
};

CHV.fn.import = {
    errorHandler: function (response) {
        PF.fn.growl.call(response.error.message);
    },
    reset: function (id) {
        var id = parseInt(id);
        CHV.obj.import.working[id].stats = $.ajax({
            type: "POST",
            data: {
                action: "importReset",
                id: id,
            },
        });
        CHV.obj.import.working[id].stats.complete(function (XHR) {
            var response = XHR.responseJSON;
            if (response) {
                var $html = CHV.fn.import.parseTemplate(response.import);
                $(
                    "[data-id=" + response.import.id + "]",
                    CHV.obj.import.sel.root
                ).replaceWith($html);
                if (response.import.status != "working") {
                    clearInterval(CHV.obj.import.working[id].interval);
                }
            }
        });
    },
    updateStats: function (id) {
        var id = parseInt(id);
        if (
            "readyState" in CHV.obj.import.working[id].stats &&
            CHV.obj.import.working[id].stats.readyState != 4
        ) {
            console.error(
                "Aborting stats timeout call (previous call is still not ready)"
            );
            return;
        }
        CHV.obj.import.working[id].stats = $.ajax({
            type: "POST",
            data: {
                action: "importStats",
                id: id,
            },
        });
        CHV.obj.import.working[id].stats.complete(function (XHR) {
            var response = XHR.responseJSON;
            if (response) {
                var $html = CHV.fn.import.parseTemplate(response.import);
                $(
                    "[data-id=" + response.import.id + "]",
                    CHV.obj.import.sel.root
                ).replaceWith($html);
                if (response.import.status != "working") {
                    clearInterval(CHV.obj.import.working[id].interval);
                }
            }
        });
    },
    delete: {
        submit: function (id) {
            PF.obj.modal.form_data = {
                action: "importDelete",
                id: id,
            };
            return true;
        },
        deferred: {
            success: function (XHR) {
                var response = XHR.responseJSON;
                PF.fn.growl.call(PF.fn._s("Import ID %s removed", response.import.id));
                $(
                    "[data-id=" + response.import.id + "]",
                    CHV.obj.import.sel.root
                ).remove();
                if ($("li", CHV.obj.import.sel.root).size() == 1) {
                    $(CHV.obj.import.sel.root).addClass("hidden");
                }
            },
            error: function (XHR) {
                CHV.fn.import.errorHandler(XHR.responseJSON);
            },
        },
    },
    parseTemplate: function (dataset, $el) {
        var tpl = CHV.obj.import.rowTpl;
        for (var key in CHV.obj.import.importTr) {
            if (typeof dataset[key] != typeof undefined) {
                tpl = tpl.replaceAll("%" + key + "%", dataset[key]);
            }
        }
        tpl = tpl.replaceAll("%parse%", dataset.options.root);
        tpl = tpl.replaceAll("%shortParse%", dataset.options.root.charAt(0));
        tpl = tpl.replaceAll(
            "%displayStatus%",
            CHV.obj.import.statusesDisplay[dataset.status]
        );
        var $html = $($.parseHTML(tpl)).attr(
            "data-object",
            JSON.stringify(dataset)
        );
        return $html;
    },
};

CHV.fn.Palettes = {
    timeout: {},
    get: function () {
        return ($("html").get(0).className.match(/(^|\s)palette-\S+/g) || []).join(' ');
    },
    set: function (palette) {
        $("html")
            .attr("data-palette", palette)
            .removeClass(this.get())
            .addClass("palette-" + palette);
    },
    preview: function (palette) {
        $("html")
            .removeClass(this.get())
            .addClass("palette-" + palette);
    },
    save: function () {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(function () {
            $.ajax({
                type: "POST",
                data: {
                    action: "paletteSet",
                    palette_id: CHV.obj.config.palettesId[$("html").attr("data-palette")],
                },
                cache: false,
            });
        }, 400);
    }
}

CHV.fn.license = {
    set: {
        submit: function () {
            var $modal = $(PF.obj.modal.selectors.root),
                submit = true;
            $.each($(":input", $modal), function (i, v) {
                if ($(this).val() == "" && $(this).attr("required")) {
                    $(this).highlight();
                    submit = false;
                }
            });
            if (!submit) {
                return false;
            }
            PF.obj.modal.form_data = {
                action: "set-license-key",
                key: $("[name=chevereto-license-key]", $modal).val(),
            };
            return true;
        },
        complete: {
            success: function (XHR) {
                let response = XHR.responseJSON;
                let $trigger = $("[data-action=upgrade]");
                if(CHV.obj.system_info.edition === 'free') {
                    $trigger.removeClass("hidden");
                    $trigger.trigger("click");
                    return;
                }
                PF.fn.growl.call(PF.fn._s(response.success.message));
            },
            error: function (XHR) {
                var response = XHR.responseJSON;
                PF.fn.growl.call(PF.fn._s(response.error.message));
            },
        },
    },
};

CHV.fn.user_background = {
    delete: {
        submit: function () {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "background",
                owner: CHV.obj.resource.user.id,
            };
            return true;
        },
        deferred: {
            success: {
                before: function (XHR) {
                    $("[data-content=user-background-cover-src]").css(
                        "background-image",
                        "none"
                    );
                    $("[data-content=user-background-cover], .top-user")
                        .addClass("no-background");
                    $("[data-content=user-background-cover]").height("");
                    $("[data-content=user-upload-background]")
                        .removeClass("hidden")
                        .show();
                    $("[data-content=user-change-background]").hide();
                },
                done: function (XHR) {
                    PF.fn.modal.close();
                },
            },
            error: function (XHR) {
                PF.fn.growl.call(
                    PF.fn._s("Error deleting profile background image.")
                );
            },
        },
    },
};

CHV.fn.user_api = {
    delete: {
        submit: function () {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "api_key",
                owner: CHV.obj.resource.user.id,
            };
            return true;
        },
        deferred: {
            success: {
                before: function (XHR) {
                },
                done: function (XHR) {
                    PF.fn.modal.close(function () {
                        location.reload();
                    });
                },
            },
            error: function (XHR) {
                PF.fn.growl.call(
                    XHR.responseJSON.error.message
                );
            },
        },
    },
};

CHV.fn.user_two_factor = {
    delete: {
        submit: function () {
            PF.obj.modal.form_data = {
                action: "delete",
                delete: "two_factor",
                owner: CHV.obj.resource.user.id,
            };
            return true;
        },
        deferred: {
            success: {
                before: function (XHR) {
                },
                done: function (XHR) {
                    PF.fn.modal.close(function () {
                        location.reload();
                    });
                },
            },
            error: function (XHR) {
                PF.fn.growl.call(
                    XHR.responseJSON.error.message
                );
            },
        },
    },
};

CHV.str.mainform = "[data-content=main-form]";
CHV.obj.timezone = {
    selector: "[data-content=timezone]",
    input: "#timezone-region",
};


$(function () {
    var resizedFinished;
    $(window).resize(function (e) {
        clearTimeout(resizedFinished);
        resizedFinished = setTimeout(function () {
            CHV.fn.uploader.boxSizer();
            CHV.fn.bindSelectableItems();
            CHV.fn.listingViewer.placeholderSizing();
            prevWidth = $(window).width();
            prevHeight = $(window).height();
        }, 10);
    });
    if (window.opener) {
        $(window).on("load", function (e) {
            window.opener.postMessage({
                id: window.name,
                requestAction: "postSettings",
            },
                "*"
            );
            CHV.obj.opener.uploadPlugin[window.name] = {
                autoInsert: false,
                autoClose: false,
            };
        });
        $(window).on("message", function (e) {
            var data = e.originalEvent.data;
            var type = typeof data.type === typeof undefined
                ? "upload-plugin"
                : data.type;
            if(type === "upload-plugin") {
                if (typeof data.id == typeof undefined ||
                    typeof data.settings == typeof undefined
                ) {
                    return;
                }
                if (window.name !== data.id) {
                    return;
                }
                if("autoInsert" in data.settings) {
                    if(! data.settings.autoInsert || data.settings.autoInsert == '0') {
                        data.settings.autoInsert = false;
                    }
                }
                if("autoClose" in data.settings) {
                    data.settings.autoClose = data.settings.autoClose && data.settings.autoClose != '0';
                }
                CHV.obj.opener.uploadPlugin[data.id] = {...CHV.obj.opener.uploadPlugin[data.id], ...data.settings};
            }
        });
    }
    if ($("#home-cover, #maintenance-wrapper, #login").exists()) {
        var landing_src = $("#maintenance-wrapper").exists() ?
            $("#maintenance-wrapper")
                .css("background-image")
                .slice(4, -1)
                .replace(/^\"|\"$/g, "") :
            $(".home-cover-img", "#home-cover-slideshow").first().attr("data-src");

        function showHomeCover() {
            $("body").addClass("load");
            if (!$("#maintenance-wrapper").exists()) {
                $(".home-cover-img", "#home-cover-slideshow")
                    .first()
                    .css("background-image", "url(" + landing_src + ")")
                    .addClass("animate-in--alt")
                    .removeAttr("data-src");
            }
            setTimeout(function () {
                setTimeout(function () {
                    $("body").addClass("loaded");
                }, 400 * 3);

                setTimeout(function () {
                    showHomeSlideshow();
                }, 7000);
            }, 400 * 1.5);
        }

        var showHomeSlideshowInterval = function () {
            setTimeout(function () {
                showHomeSlideshow();
            }, 8000);
        };

        function showHomeSlideshow() {
            var $image = $(
                ".home-cover-img[data-src]",
                "#home-cover-slideshow"
            ).first();
            var $images = $(".home-cover-img", "#home-cover-slideshow");
            if ($image.length == 0) {
                if ($images.length == 1) return;
                $images.first().removeClass("animate-in");
                $("#home-cover-slideshow").append($images.first());
                setTimeout(function () {
                    $(".home-cover-img:last", "#home-cover-slideshow").addClass(
                        "animate-in"
                    );
                }, 20);
                setTimeout(function () {
                    $(".home-cover-img:not(:last)", "#home-cover-slideshow").removeClass(
                        "animate-in"
                    );
                }, 4000);
                showHomeSlideshowInterval();
            } else {
                var src = $image.attr("data-src");
                $("<img/>")
                    .attr("src", src)
                    .on("load error", function () {
                        $(this).remove();
                        $image
                            .css("background-image", "url(" + src + ")")
                            .addClass("animate-in")
                            .removeAttr("data-src");
                        setTimeout(function () {
                            $(
                                ".home-cover-img:not(:last)",
                                "#home-cover-slideshow"
                            ).removeClass("animate-end animate-in--alt");
                        }, 2000);
                        showHomeSlideshowInterval();
                    });
            }
        }

        if (landing_src) {
            $("<img/>")
                .attr("src", landing_src)
                .on("load error", function () {
                    $(this).remove();
                    showHomeCover();
                });
        } else {
            showHomeCover();
        }
    }

    var anywhere_upload = CHV.fn.uploader.selectors.root;
    var anywhere_upload_queue = CHV.fn.uploader.selectors.queue;
    var $anywhere_upload = $(anywhere_upload);
    var $anywhere_upload_queue = $(anywhere_upload_queue);

    $(document).on("click", "[data-action=top-bar-upload]", function (e) {
        if (!$("body").is("#upload") && $(this).data("link") === 'js') {
            CHV.fn.uploader.toggle({ reset: false });
        }
        if ($(this).data("link") !== 'page') {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    var timeoutPalette;
    $(document).on("click mouseover mouseout", "[data-action=palette]", function (e) {
        clearTimeout(timeoutPalette);
        e.preventDefault();
        var palette = $(this).data("palette");
        switch (e.type) {
            case "mouseover":
                timeoutPalette = setTimeout(function () {
                    CHV.fn.Palettes.preview(palette);
                }, 1000);
                break;
            case "mouseout":
                palette = $("html").attr("data-palette") || '';
                CHV.fn.Palettes.set(palette);
                break;
            case "click":
                e.stopPropagation();
                $("[data-action=palette]", "[data-content=palettes]").removeClass("current");
                $(this).addClass("current");
                CHV.fn.Palettes.set(palette);
                CHV.fn.Palettes.save();
                break;
        }
    });
    $(document).on("change", "#palettes", function (e) {
        CHV.fn.Palettes.set(this.value);
        CHV.fn.Palettes.save();
    });

    $("[data-action=close-upload]", $anywhere_upload).click(function () {
        if ($anywhere_upload.is(":animated")) {
            return;
        }
        $("[data-action=top-bar-upload]", "#top-bar").trigger("click");
    });

    $("[data-action=reset-upload]", $anywhere_upload).click(function () {
        if (CHV.fn.uploader.isUploading) {
            $(
                "[data-action=cancel-upload-remaining], [data-action=cancel-upload]",
                $anywhere_upload
            ).trigger("click");
        }
        CHV.fn.uploader.reset();
    });

    $(
        "[data-action=cancel-upload-remaining], [data-action=cancel-upload]",
        $anywhere_upload
    ).click(function () {
        CHV.fn.uploader.isUploading = false;
        $("[data-action=cancel]", $anywhere_upload_queue).click();
        if (Object.size(CHV.fn.uploader.results.success) > 0) {
            CHV.fn.uploader.displayResults();
            return;
        } else {
            CHV.fn.uploader.reset();
        }
    });

    $(document).on(
        "click",
        "[data-action=upload-privacy]:not(disabled)",
        function (e) {
            if (e.isDefaultPrevented()) return;
            current_privacy = $(this).data("privacy");
            target_privacy = current_privacy == "public" ? "private" : "public";
            this_lock = $(".icon", this).data("lock");
            this_unlock = $(".icon", this).data("unlock");
            $(".icon", this)
                .removeClass(this_lock + " " + this_unlock)
                .addClass(current_privacy == "public" ? this_lock : this_unlock);
            $(this).data("privacy", target_privacy);

            $("[data-action=upload-privacy-copy]").html(
                $("[data-action=upload-privacy]").html()
            );

            $upload_button = $("[data-action=upload]", $anywhere_upload);
            $upload_button.text($upload_button.data(target_privacy));

            $(this).tipTip("hide");
        }
    );

    $(CHV.fn.uploader.selectors.file + ", " + CHV.fn.uploader.selectors.camera)
        .on("change", function (e) {
            if (!$(CHV.fn.uploader.selectors.root).data("shown")) {
                CHV.fn.uploader.toggle({
                    callback: function (e) {
                        CHV.fn.uploader.add(e);
                    },
                },
                    e
                );
            } else {
                CHV.fn.uploader.add(e);
            }
        })
        .on("click", function (e) {
            if ($(this).data("login-needed") && !PF.fn.is_user_logged()) {
                return;
            }
        });

    function isFileTransfer(e) {
        var e = e.originalEvent,
            isFileTransfer = false;
        if (e.dataTransfer.types) {
            for (var i = 0; i < e.dataTransfer.types.length; i++) {
                if (e.dataTransfer.types[i] == "Files") {
                    isFileTransfer = true;
                    break;
                }
            }
        }
        return isFileTransfer;
    }

    if ($(CHV.fn.uploader.selectors.root).exists()) {
        $("body").on({
            dragenter: function (e) {
                e.preventDefault();
                if (!isFileTransfer(e)) {
                    return false;
                }
                if (!$(CHV.fn.uploader.selectors.dropzone).exists()) {
                    $("body").append(
                        $(
                            '<div id="' +
                            CHV.fn.uploader.selectors.dropzone.replace("#", "") +
                            '"/>'
                        ).css({
                            width: "100%",
                            height: "100%",
                            position: "fixed",
                            /* opacity: 0.5, background: "red",*/
                            zIndex: 1000,
                            left: 0,
                            top: 0,
                        })
                    );
                }
            },
        });
        $(document).on({
            dragover: function (e) {
                e.preventDefault();
                if (!isFileTransfer(e)) {
                    return false;
                }
                if (!$(CHV.fn.uploader.selectors.root).data("shown")) {
                    CHV.fn.uploader.toggle({
                        reset: false,
                    });
                }
            },
            dragleave: function (e) {
                $(CHV.fn.uploader.selectors.dropzone).remove();
                if ($.isEmptyObject(CHV.fn.uploader.files)) {
                    CHV.fn.uploader.toggle();
                }
            },
            drop: function (e) {
                e.preventDefault();
                CHV.fn.uploader.add(e);
                $(CHV.fn.uploader.selectors.dropzone).remove();
            },
        },
            CHV.fn.uploader.selectors.dropzone
        );
    }
    $(document).on("keyup change", "[data-action=resize-combo-input]", function (
        e
    ) {
        var $parent = $(this).closest("[data-action=resize-combo-input]");
        var $input_width = $("[name=form-width]", $parent);
        var $input_height = $("[name=form-height]", $parent);
        var ratio = $input_width.data("initial") / $input_height.data("initial");
        var image = {
            width: Math.round($input_width.prop("value") / ratio),
            height: Math.round($input_height.prop("value") * ratio),
        };
        if ($(e.target).is($input_width)) {
            $input_height.prop("value", Math.round(image.width));
        } else {
            $input_width.prop("value", Math.round(image.height));
        }
    });

    $(document).on(
        "click",
        anywhere_upload_queue + " [data-action=edit]",
        function () {
            var $item = $(this).closest("li");
            var id = $item.data("id");
            var file = CHV.fn.uploader.files[id];
            var media = file.type.substring(0, file.type.indexOf("/"));
            var modal = PF.obj.modal.selectors.root;
            var queueObject = $.extend({}, file.formValues || file.parsedMeta);
            var injectKeys = ["album_id", "category_id", "nsfw"];
            for (var i = 0; i < injectKeys.length; i++) {
                var key = injectKeys[i];
                if (typeof queueObject[key] == typeof undefined) {
                    var $object = $(
                        "[name=upload-" + key.replace("_", "-") + "]",
                        CHV.fn.uploader.selectors.root
                    );
                    var value = $object.prop(
                        $object.is(":checkbox") ? "checked" : "value"
                    );
                    queueObject[key] = $object.is(":checkbox") ?
                        value ?
                            "1" :
                            null :
                        value;
                }
            }
            PF.fn.modal.call({
                type: "html",
                template: $("#anywhere-upload-edit-item").html(),
                callback: function () {
                    $("[data-content=icon]", modal).addClass('fa-file-' + media);
                    var imageMaxCfg = {
                        width: CHV.obj.config.image.max_width != 0 ?
                            CHV.obj.config.image.max_width : queueObject.width,
                        height: CHV.obj.config.image.max_height != 0 ?
                            CHV.obj.config.image.max_height : queueObject.height,
                    };

                    var imageMax = $.extend({}, imageMaxCfg);
                    var ratio = queueObject.width / queueObject.height;

                    imageMax.width = Math.round(imageMaxCfg.height * ratio);
                    imageMax.height = Math.round(imageMaxCfg.width / ratio);

                    if (imageMax.height > imageMaxCfg.height) {
                        imageMax.height = imageMaxCfg.height;
                        imageMax.width = Math.round(imageMax.height * ratio);
                    }

                    if (imageMax.width > imageMaxCfg.width) {
                        imageMax.width = imageMaxCfg.width;
                        imageMax.height = Math.round(imageMax.width / ratio);
                    }

                    $.each(queueObject, function (i, v) {
                        var name = "[name=form-" + i.replace(/_/g, "-") + "]";
                        var $input = $(name, modal);

                        if (!$input.exists()) {
                            return true;
                        }

                        if ($input.is(":checkbox")) {
                            $input.prop("checked", $input.attr("value") == v);
                        } else if ($input.is("select")) {
                            var $option = $input.find('[value="' + v + '"]');
                            if (!$option.exists()) {
                                $option = $input.find("option:first");
                            }
                            $option.prop("selected", true);
                        } else {
                            $input.prop("value", v);
                        }

                        if (i == "width" || i == "height") {
                            var max = imageMax[i];
                            var value = file.parsedMeta[i] > max ? max : file.parsedMeta[i];
                            $input
                                .prop("max", value)
                                .data("initial", file.parsedMeta[i])
                                .prop("value", value);
                            if(media !== "image") {
                                $input
                                    .prop("disabled", true)
                                    .closest("[data-action=resize-combo-input]").hide();
                            }
                        }
                    });

                    if (file.parsedMeta.mimetype !== "image/gif") {
                        $("[ data-content=animated-gif-warning]", modal).remove();
                    }

                    $(".image-preview", modal).append(
                        $("<canvas/>", {
                            class: "canvas checkered-background",
                        })
                    );
                    var source_canvas = $(".queue-item[data-id=" + id + "] .preview .canvas")[0];
                    var target_canvas = $(".image-preview .canvas", modal)[0];
                    target_canvas.width = source_canvas.width;
                    target_canvas.height = source_canvas.height;
                    var target_canvas_ctx = target_canvas.getContext("2d");
                    target_canvas_ctx.drawImage(source_canvas, 0, 0);
                },
                confirm: function () {
                    if (!PF.fn.form_modal_has_changed()) {
                        PF.fn.modal.close();
                        return;
                    }

                    // Validations (just in case)
                    var errors = false;
                    $.each(["width", "height"], function (i, v) {
                        var $input = $("[name=form-" + v + "]", modal);
                        var input_val = parseInt($input.val());
                        var min_val = parseInt($input.attr("min"));
                        var max_val = parseInt($input.attr("max"));
                        if (input_val > max_val || input_val < min_val) {
                            $input.highlight();
                            errors = true;
                            return true;
                        }
                    });

                    if (errors) {
                        return false;
                    }

                    if (typeof file.formValues == typeof undefined) {
                        file.formValues = {
                            title: null,
                            tags: null,
                            category_id: null,
                            width: null,
                            height: null,
                            nsfw: null,
                            expiration: null,
                            description: null,
                            album_id: null,
                        };
                    }

                    $(":input[name]", modal).each(function (i, v) {
                        var key = $(this)
                            .attr("name")
                            .replace("form-", "")
                            .replace(/-/g, "_");
                        if (typeof file.formValues[key] == typeof undefined) return true;
                        file.formValues[key] = $(this).is(":checkbox") ?
                            $(this).is(":checked") ?
                                $(this).prop("value") :
                                null :
                            $(this).prop("value");
                    });

                    CHV.fn.uploader.files[id].formValues = file.formValues;

                    return true;
                },
            });
        }
    );

    $(document).on(
        "click",
        anywhere_upload_queue + " [data-action=cancel]",
        function () {
            var $item = $(this).closest("li"),
                $queue = $item.closest("ul"),
                id = $item.data("id"),
                queue_height = $queue.height(),
                item_xhr_cancel = false;

            if ($item.hasClass("completed") || $item.hasClass("failed")) {
                return;
            }

            $("#tiptip_holder").hide();

            $item.tipTip("destroy").remove();

            if (queue_height !== $queue.height()) {
                CHV.fn.uploader.boxSizer();
            }
            if (!$("li", $anywhere_upload_queue).exists()) {
                $(
                    "[data-group=upload-queue-ready], [data-group=upload-queue], [data-group=upload-queue-ready]",
                    $anywhere_upload
                ).css("display", "");
            }

            if (
                CHV.fn.uploader.files[id] &&
                typeof CHV.fn.uploader.files[id].xhr !== "undefined"
            ) {
                CHV.fn.uploader.files[id].xhr.abort();
                item_xhr_cancel = true;
            }

            if (
                typeof CHV.fn.uploader.files[id] !== typeof undefined &&
                typeof CHV.fn.uploader.files[id].fromClipboard !== typeof undefined
            ) {
                var c_md5 = CHV.fn.uploader.files[id].md5;
                var c_index = CHV.fn.uploader.clipboardImages.indexOf(c_md5);
                if (c_index > -1) {
                    CHV.fn.uploader.clipboardImages.splice(c_index, 1);
                }
            }

            delete CHV.fn.uploader.files[id];

            CHV.fn.uploader.queueSize();

            if (Object.size(CHV.fn.uploader.files) == 0) {
                // No queue left
                // Null result ?
                if (!("success" in CHV.fn.uploader) ||
                    !("results" in CHV.fn.uploader) ||
                    (Object.size(CHV.fn.uploader.results.success) == 0 &&
                        Object.size(CHV.fn.uploader.results.error) == 0)
                ) {
                    CHV.fn.uploader.reset();
                }
            } else {
                // Do we need to process the next item?
                if (item_xhr_cancel && $("li.waiting", $queue).first().length !== 0) {
                    CHV.fn.uploader.upload($("li.waiting", $queue).first());
                }
            }
        }
    );

    $(document).on("click", "[data-action=upload]", function () {
        if (typeof CHV.obj.logged_user === "undefined" && $('#upload-tos').prop('checked') === false) {
            $('#upload-tos').prop("required", true)[0].reportValidity();
            return false;
        }
        $(
            "[data-group=upload], [data-group=upload-queue-ready]",
            $anywhere_upload
        ).hide();
        $anywhere_upload
            .removeClass("queueReady")
            .addClass("queueUploading")
            .find("[data-group=uploading]")
            .show();
        CHV.fn.uploader.queueSize();
        CHV.fn.uploader.canAdd = false;
        $queue_items = $("li", $anywhere_upload_queue);
        $queue_items.addClass("uploading waiting");
        CHV.fn.uploader.timestamp = new Date().getTime();
        CHV.fn.uploader.upload($queue_items.first("li"));
    });

    if ($("#top-bar-shade").exists() && $("#top-bar-shade").css("opacity")) {
        $("#top-bar-shade").data(
            "initial-opacity",
            Number($("#top-bar-shade").css("opacity"))
        );
    }

    CHV.fn.bindSelectableItems();

    if ($("body#image").exists()) {
        if ($(CHV.obj.image_viewer.selector + " [data-load=full]").length > 0) {
            $(document).on("click", CHV.obj.image_viewer.loader, function (e) {
                CHV.fn.viewerLoadImage();
            });
            if (
                $(CHV.obj.image_viewer.loader).data("size") >
                CHV.obj.config.image.load_max_filesize.getBytes()
            ) {
                $(CHV.obj.image_viewer.loader).css("display", "block");
            } else {
                CHV.fn.viewerLoadImage();
            }
        }
        new MutationObserver(() => {
            if (
                $("html").height() > $(window).innerHeight() &&
                !$("html").hasClass("scrollbar-y")
            ) {
                $("html").addClass("scrollbar-y");
                $(document).data({
                    width: $(this).width(),
                    height: $(this).height(),
                });
            }
        }).observe(document, { childList: true });
        $(document).on("keyup", function (e) {
            var $this = $(e.target),
                event = e.originalEvent;
            if ($this.is(":input")) {
                return;
            } else {
                if (
                    CHV.obj.image_viewer.$navigation.exists() &&
                    (event.key == "ArrowLeft" || event.key == "ArrowRight")
                ) {
                    var navigation_jump_url = $(
                        "[data-action=" + (event.key == "ArrowLeft" ? "prev" : "next") + "]",
                        CHV.obj.image_viewer.$navigation
                    ).attr("href");
                    if (
                        typeof navigation_jump_url !== "undefined" &&
                        navigation_jump_url !== ""
                    ) {
                        window.location = $(
                            "[data-action=" + (event.key == "ArrowLeft" ? "prev" : "next") + "]",
                            CHV.obj.image_viewer.$navigation
                        ).attr("href");
                    }
                }
            }
        });
    }

    $(document).on("click", CHV.obj.image_viewer.container + " img", function (e) {
            if($(CHV.obj.image_viewer.loader).exists()) {
                $(CHV.obj.image_viewer.loader).trigger("click");
                return;
            }
            $(this).toggleClass("zoom-natural");
        })
        .on("contextmenu", CHV.obj.image_viewer.container, function (e) {
            if (!CHV.obj.config.image.right_click) {
                e.preventDefault();
                return false;
            }
        });

    $(document).on(
        "contextmenu",
        "html.device-mobile a.image-container",
        function (e) {
            e.preventDefault();
            e.stopPropagation();
        }
    );

    $(document).on("keyup", "input[data-dashboard-tool]", function (e) {
        if (e.keyCode == 13) {
            var $button = $("[data-action=" + $(this).data("dashboard-tool") + "]");
            $button.click();
        }
    });

    $(document).on("click", "[data-action=dashboardTool]", function (e) {
        e.preventDefault();
        var tool = $(this).data("tool");
        var dataSet = $(this).data("data");
        var data = $.extend({}, dataSet);
        var inputs = {};
        for (var key in data) {
            var val = $(data[key]).val();
            if ($(data[key]).prop("disabled") || !val) {
                return;
            }
            inputs[key] = $(data[key]);
            data[key] = val;
        }
        data.action = tool;
        var ajaxObj = {
            type: "GET", // !
            cache: false,
        };
        ajaxObj.data = data;
        var $parent = $(this).closest(".input-label");
        var validate = true;
        var message;

        if (validate == false) {
            PF.fn.growl.call(message);
            return;
        }
        PF.fn.loading.inline($(".loading", $parent), {
            size: "small",
            valign: "middle",
        });
        $parent.find(".btn .text").hide();
        $.ajax(ajaxObj).complete(function (XHR) {
            var response = XHR.responseJSON;
            $(".loading", $parent).empty();
            $parent.find(".btn .text").show();
            if (
                response.status_code == 200 &&
                typeof response.success.redirURL !== typeof undefined
            ) {
                window.location.href = response.success.redirURL;
                return;
            }
            PF.fn.growl.call(
                response[response.status_code == 200 ? "success" : "error"].message
            );
        });
    });

    $(document).on("click", "[data-action=openerPostMessage]", function (e) {
        if (!window.opener) return;
        e.preventDefault();
        var target_attr = "data-action-target";
        var $target = $(
            $(this).is("[" + target_attr + "]") ? $(this).attr(target_attr) : this
        );
        var val = $target[$target.is(":input") ? "val" : "html"]();
        window.opener.postMessage({
            id: window.name,
            message: val,
        },
            "*"
        );
    });

    /**
     * USER SIDE LISTING EDITOR
     * -------------------------------------------------------------------------------------------------
     */

    $(document).on("click", "[data-action=list-tools] [data-action]", function (
        e
    ) {
        var $this = $(e.target),
            $list_item = $this.closest("[data-id]");
        if (
            $list_item &&
            $list_item.find("[data-action=select]").exists() &&
            (e.ctrlKey || e.metaKey) &&
            e.altKey
        ) {
            CHV.fn.list_editor.toggleSelectItem(
                $list_item, !$list_item.hasClass("selected")
            );
            e.preventDefault();
            e.stopPropagation();
        }
    });

    PF.fn.listing.ajax.callback = function (XHR) {
        if (XHR.status !== 200) return;
        CHV.fn.list_editor.listMassActionSet("select");
    };

    $(document).on("click", "[data-action=list-select-all]", function (e) {
        if ($(this).closest('.disabled').exists()) {
            return false;
        }
        CHV.fn.list_editor.selectAll(e);
    });

    $(document).on("click", "[data-action=list-clear-all]", function (e) {
        if ($(this).closest('.disabled').exists()) {
            return false;
        }
        CHV.fn.list_editor.clearSelected();
    });

    $(document).on("click", "[data-action=share]", function (e) {
        if($(PF.obj.modal.selectors.box).exists()) {
            return;
        }
        var $list_item;
        if ($('.viewer:visible').exists()) {
            $list_item = $(PF.obj.listing.selectors.list_item + '[data-id=' + $('.viewer').attr('data-id') + ']', '.content-listing').first();
        } else {
            $list_item = $(this).closest(PF.obj.listing.selectors.list_item).first();
        }
        var url;
        var image;
        var title;
        var link;
        var modal_tpl;
        var modal_sel = "#modal-share";
        if ($list_item.exists()) {
            modal_tpl = CHV.fn.modal.getTemplateWithPreview(modal_sel, $list_item);
            if (typeof $list_item.attr("data-type") === "undefined") {
                console.error("Error: data-type not defined");
                return;
            }
            link = $list_item.find('.list-item-desc-title-link').first();
            image = $list_item.find('.image-container img').first().attr('src');
            url = $list_item.attr('data-url-short');
        } else {
            modal_tpl = $(modal_sel).html();
            dealing_with = CHV.obj.resource.type;
            url = CHV.obj.resource.url_short;
            image = 'img_viewer' in CHV.obj
                ? CHV.obj.image_viewer.image.display_url
                : null;
            link = $(".header > h1 > a");
        }
        title = encodeURIComponent(link.text());
        var privacy = $list_item.attr("data-privacy") || CHV.obj.resource.privacy;
        var privacy_notes = '';
        switch (privacy) {
            case 'private_but_link':
                privacy_notes = PF.fn._s('Note: This content is private but anyone with the link will be able to see this.');
                break;
            case 'password':
                privacy_notes = PF.fn._s('Note: This content is password protected. Remember to pass the content password to share.');
                break;
            case 'private':
                privacy_notes = PF.fn._s('Note: This content is private. Change privacy to "public" to share.');
                break;
        }
        modal_tpl = modal_tpl
            .replaceAll('__url__', url)
            .replaceAll('__image__', image)
            .replaceAll('__title__', title)
            .replaceAll('__privacy__', privacy)
            .replaceAll('__privacy_notes__', privacy_notes);
        PF.fn.modal.call({
            type: "html",
            buttons: false,
            template: modal_tpl,
        });
    });

    $(document).on("click", "[data-action=list-tools] [data-action]", function (
        e
    ) {
        if (e.isPropagationStopped()) return false;

        var $list_item;
        if ($('.viewer:visible').exists()) {
            $list_item = $(PF.obj.listing.selectors.list_item + '[data-id=' + $('.viewer').attr('data-id') + ']', '.content-listing').first();
        } else {
            $list_item = $(this).closest(PF.obj.listing.selectors.list_item).first();
        }
        var id = $list_item.attr("data-id");

        if (typeof $list_item.attr("data-type") !== "undefined") {
            dealing_with = $list_item.attr("data-type");
        } else {
            console.error("Error: data-type not defined");
            return;
        }

        var $targets = $("[data-type=" + dealing_with + "][data-id=" + id + "]");
        var dealing_with;

        switch ($(this).data("action")) {
            case "select":
                CHV.fn.list_editor.toggleSelectItem(
                    $list_item, !$list_item.hasClass("selected")
                );
                break;

            case "edit":
                var modal_source = "[data-modal=form-edit-single]";
                switch (dealing_with) {
                    case "image":
                        if(!$list_item.attr("data-tags")) {
                            var image = JSON.parse(
                                decodeURIComponent($list_item.attr("data-object"))
                            );
                            $list_item.attr("data-tags", image.tags_string);
                        }
                        $("[name=form-image-title]", modal_source).attr({
                            value: $list_item.attr("data-title"),
                            autocomplete: "off"
                        });
                        $("[name=form-image-tags]", modal_source).attr({
                            value: $list_item.attr("data-tags"),
                            autocomplete: "off"
                        });
                        $("[name=form-image-description]", modal_source).html(
                            PF.fn.htmlEncode($list_item.attr("data-description"))
                        );
                        $("[name=form-album-id]", modal_source)
                            .find("option")
                            .removeAttr("selected");
                        $("[name=form-album-id]", modal_source)
                            .find(
                                '[value="' +
                                $list_item
                                    .data(dealing_with == "image" ? "album-id" : "id") +
                                '"]'
                            )
                            .attr("selected", true);

                        $("[name=form-category-id]", modal_source)
                            .find("option")
                            .removeAttr("selected");
                        $("[name=form-category-id]", modal_source)
                            .find('[value="' + $list_item.attr("data-category-id") + '"]')
                            .attr("selected", true);

                        $("[name=form-nsfw]", modal_source).attr(
                            "checked",
                            $list_item.attr("data-flag") == "unsafe"
                        );

                        // Just in case...
                        $("[name=form-album-name]", modal_source).attr({ value: "", autocomplete: "off" });
                        $("[name=form-album-description]", modal_source).html("");
                        $("[name=form-privacy]", modal_source)
                            .find("option")
                            .removeAttr("selected");

                        break;
                    case "album":
                        $("[data-action=album-switch]", modal_source).remove();
                        $("[name=form-album-name]", modal_source).attr({
                            value: $list_item.attr("data-name"),
                            autocomplete: "off"
                        });
                        $("[name=form-album-description]", modal_source).html(
                            PF.fn.htmlEncode($list_item.attr("data-description"))
                        );
                        $("[name=form-privacy]", modal_source)
                            .find("option")
                            .removeAttr("selected");
                        $("[name=form-privacy]", modal_source)
                            .find('[value="' + $list_item.attr("data-privacy") + '"]')
                            .attr("selected", true);
                        if ($list_item.attr("data-privacy") == "password") {
                            $("[data-combo-value=password]").show();
                            $("[name=form-album-password]", modal_source).attr(
                                "value",
                                $list_item.attr("data-password")
                            );
                        } else {
                            $("[data-combo-value=password]").hide();
                            $("[name=form-album-password]", modal_source).attr("value", "");
                        }
                        break;
                }

                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreview(modal_source, $list_item),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                console.log('UPDATE ITEM')
                                CHV.fn.list_editor.updateItem(
                                    "[data-type=" + dealing_with + "][data-id=" + id + "]",
                                    XHR.responseJSON[dealing_with],
                                    "edit"
                                );
                            },
                        },
                    },
                    confirm: function () {
                        var $modal = $(PF.obj.modal.selectors.root);
                        var $form = $modal.find("form");
                        if(!PF.fn.form.validateForm($form)) {
                            return false;
                        }
                        if (!PF.fn.form_modal_has_changed()) {
                            PF.fn.modal.close();
                            return;
                        }

                        PF.obj.modal.form_data = {
                            action: "edit", // use the same method applied in viewer
                            edit: $list_item.attr("data-type"),
                            single: true,
                            owner: CHV.obj.resource.user.id,
                            editing: {
                                id: id,
                                description: $(
                                    "[name=form-" + dealing_with + "-description]",
                                    $modal
                                ).val(),
                            },
                        };

                        switch (dealing_with) {
                            case "image":
                                PF.obj.modal.form_data.editing.title = $(
                                    "[name=form-image-title]",
                                    $modal
                                ).val();
                                PF.obj.modal.form_data.editing.tags = $(
                                    "[name=form-image-tags]",
                                    $modal
                                ).val();
                                PF.obj.modal.form_data.editing.category_id =
                                    $("[name=form-category-id]", $modal).val() || null;
                                PF.obj.modal.form_data.editing.nsfw = $(
                                    "[name=form-nsfw]",
                                    $modal
                                ).prop("checked") ?
                                    1 :
                                    0;
                                break;
                            case "album":
                                PF.obj.modal.form_data.editing.name = $(
                                    "[name=form-album-name]",
                                    $modal
                                ).val();
                                PF.obj.modal.form_data.editing.privacy = $(
                                    "[name=form-privacy]",
                                    $modal
                                ).val();
                                if (PF.obj.modal.form_data.editing.privacy == "password") {
                                    PF.obj.modal.form_data.editing.password = $(
                                        "[name=form-album-password]",
                                        $modal
                                    ).val();
                                }
                                break;
                        }

                        PF.obj.modal.form_data.editing.new_album = $(
                            "[data-content=form-new-album]",
                            $modal
                        ).is(":visible");

                        if (PF.obj.modal.form_data.editing.new_album) {
                            PF.obj.modal.form_data.editing.album_name = $(
                                "[name=form-album-name]",
                                $modal
                            ).val();
                            PF.obj.modal.form_data.editing.album_privacy = $(
                                "[name=form-privacy]",
                                $modal
                            ).val();
                            if (PF.obj.modal.form_data.editing.album_privacy == "password") {
                                PF.obj.modal.form_data.editing.album_password = $(
                                    "[name=form-album-password]",
                                    $modal
                                ).val();
                            }
                            PF.obj.modal.form_data.editing.album_description = $(
                                "[name=form-album-description]",
                                $modal
                            ).val();
                        } else {
                            PF.obj.modal.form_data.editing.album_id = $(
                                "[name=form-album-id]",
                                $modal
                            ).val();
                        }

                        return true;
                    },
                });
                break;

            case "create-album":
            case "move": // Move or create album
                var template = $(this).data("action") == "move" ?
                    "form-move-single" :
                    "form-create-album",
                    modal_source = "[data-modal=" + template + "]";
                $("[name=form-album-id]", modal_source)
                    .find("option")
                    .removeAttr("selected");
                $("[name=form-album-id]", modal_source)
                    .find(
                        '[value="' +
                        $list_item.attr(dealing_with == "image" ? "data-album-id" : "data-id") +
                        '"]'
                    )
                    .attr("selected", true);
                $("[name=form-album-name]", modal_source).attr({ value: "", autocomplete: "off" });
                $("[name=form-album-description]", modal_source).html("");
                $("[name=form-privacy]", modal_source)
                    .find("option")
                    .removeAttr("selected");

                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreview(modal_source, $targets),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.updateMoveItemLists(
                                    XHR.responseJSON,
                                    dealing_with,
                                    $targets
                                );
                                if(template === "form-create-album") {
                                    window.location = XHR.responseJSON.image.album.url;
                                }
                            },
                        },
                    },
                    load: function () {
                        //$("[name=form-album-id]", PF.obj.modal.selectors.root).focus();
                    },
                    confirm: function () {
                        var $modal = $(PF.obj.modal.selectors.root);
                        var $form = $modal.find("form");
                        if(!PF.fn.form.validateForm($form)) {
                            return false;
                        }

                        if (!PF.fn.form_modal_has_changed()) {
                            PF.fn.modal.close();
                            return;
                        }

                        PF.obj.modal.form_data = {
                            action: "edit", // use the same method applied in viewer
                            edit: $list_item.attr("data-type"),
                            single: true,
                            owner: CHV.obj.resource.user.id,
                            editing: {
                                id: id,
                            },
                        };

                        PF.obj.modal.form_data.editing.new_album = $(
                            "[data-content=form-new-album]",
                            $modal
                        ).is(":visible");

                        if (PF.obj.modal.form_data.editing.new_album) {
                            PF.obj.modal.form_data.editing.album_name = $(
                                "[name=form-album-name]",
                                $modal
                            ).val();
                            PF.obj.modal.form_data.editing.album_privacy = $(
                                "[name=form-privacy]",
                                $modal
                            ).val();
                            if (PF.obj.modal.form_data.editing.album_privacy == "password") {
                                PF.obj.modal.form_data.editing.album_password = $(
                                    "[name=form-album-password]",
                                    $modal
                                ).val();
                            }
                            PF.obj.modal.form_data.editing.album_description = $(
                                "[name=form-album-description]",
                                $modal
                            ).val();
                        } else {
                            PF.obj.modal.form_data.editing.album_id = $(
                                "[name=form-album-id]",
                                $modal
                            ).val();
                        }

                        return true;
                    },
                });

                break;

            case "approve":
                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreview("[data-modal=form-approve-single]", $list_item),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.removeFromList(
                                    $list_item,
                                    PF.fn._s("The content has been approved.")
                                );
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: "approve",
                            single: true,
                            approve: $list_item.attr("data-type"),
                            approving: {
                                id: id,
                            },
                        };
                        return true;
                    },
                });
                break;
            case "delete":
                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreview("[data-modal=form-delete-single]", $list_item),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                if (dealing_with == "album") {
                                    $("[name=form-album-id]", "[data-modal]")
                                        .find('[value="' + id + '"]')
                                        .remove();
                                    CHV.fn.list_editor.updateUserCounters(
                                        "image",
                                        XHR.responseJSON.success.affected,
                                        "-"
                                    );
                                }
                                CHV.fn.list_editor.deleteFromList($list_item);
                                CHV.fn.listingViewer.close();
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: "delete",
                            single: true,
                            delete: $list_item.attr("data-type"),
                            deleting: {
                                id: id,
                            },
                        };
                        return true;
                    },
                });

                break;

            case "flag":
                $.ajax({
                    type: "POST",
                    data: {
                        action: "edit",
                        edit: "image",
                        single: true,
                        editing: {
                            id: id,
                            nsfw: $list_item.attr("data-flag") == "unsafe" ? 0 : 1,
                        },
                    },
                }).complete(function (XHR) {
                    var response = XHR.responseJSON;
                    if (response.status_code == 200) {
                        var flag = response.image.nsfw == 1 ? "unsafe" : "safe";
                        $targets.attr("data-flag", flag).data("flag", flag);
                    } else {
                        PF.fn.growl.call(response.error.message);
                    }
                    CHV.fn.list_editor.selectionCount();
                });
                break;
        }
    });

    $(".pop-box-menu a", "[data-content=list-selection]").click(function (e) {
        var $content_listing = $(PF.obj.listing.selectors.content_listing_visible);

        if (typeof $content_listing.data("list") !== "undefined") {
            dealing_with = $content_listing.data("list");
        } else {
            console.error("Error: data-list not defined");
            return;
        }

        var $targets = $(
            PF.obj.listing.selectors.list_item + ".selected",
            $content_listing
        ),
            ids = $.map($targets, function (e, i) {
                return $(e).data("id");
            });

        PF.fn.close_pops();
        if ($(this).data("action") !== 'list-select-all') {
            e.stopPropagation();
        }

        switch ($(this).data("action")) {
            case "get-embed-codes":
                var template = "[data-modal=form-embed-codes]";
                var objects = [];
                $("textarea", template).html("");
                $targets.each(function () {
                    var aux = {
                        image: JSON.parse(decodeURIComponent($(this).data("object"))),
                    };
                    if ("url" in aux.image) {
                        objects.push(aux);
                    }
                });
                CHV.fn.fillEmbedCodes(objects, template, "html");
                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreviews(template, $targets),
                    buttons: false,
                });


                break;

            case "clear":
                e.stopPropagation();
                CHV.fn.list_editor.clearSelected();
                break;

            case "list-select-all":
                CHV.fn.list_editor.selectAll(e);
                break;

            case "move":
            case "create-album":
                var template = $(this).data("action") == "move" ?
                    "form-move-multiple" :
                    "form-create-album",
                    modal_source = "[data-modal=" + template + "]",
                    dealing_id_data = /image/.test(dealing_with) ? "album-id" : "id";

                $("[name=form-album-id]", modal_source).find('[value="null"]').remove();

                $("[name=form-album-id]", modal_source)
                    .find("option")
                    .removeAttr("selected");

                $("[name=form-album-name]", modal_source).attr({ value: "", autocomplete: "off" });
                $("[name=form-album-description]", modal_source).html("");
                $("[name=form-privacy]", modal_source)
                    .find("option")
                    .removeAttr("selected");

                var album_id = $targets.first().data(dealing_id_data),
                    same_album = true;

                $targets.each(function () {
                    if ($(this).data(dealing_id_data) !== album_id) {
                        same_album = false;
                        return false;
                    }
                });

                if (!same_album) {
                    $("[name=form-album-id]", modal_source).prepend(
                        '<option value="null">' +
                        PF.fn._s("Select existing album") +
                        "</option>"
                    );
                }

                $("[name=form-album-id]", modal_source)
                    .find(
                        '[value="' +
                        (same_album ? $targets.first().data(dealing_id_data) : "null") +
                        '"]'
                    )
                    .attr("selected", true);

                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreviews(modal_source, $targets),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.updateMoveItemLists(
                                    XHR.responseJSON,
                                    dealing_with,
                                    $targets
                                );
                                if(template === "form-create-album") {
                                    window.location = XHR.responseJSON.album.url;
                                }
                            },
                        },
                    },
                    load: function () {
                        if (template == "form-move-multiple") {
                            //$("[name=form-album-id]", PF.obj.modal.selectors.root).focus();
                        }
                    },
                    confirm: function () {
                        var $modal = $(PF.obj.modal.selectors.root);
                        var new_album = false;
                        var $form = $modal.find("form");
                        if(!PF.fn.form.validateForm($form)) {
                            return false;
                        }
                        if ($("[data-content=form-new-album]", $modal).is(":visible")) {
                            new_album = true;
                        }
                        if (!PF.fn.form_modal_has_changed()) {
                            PF.fn.modal.close();
                            return;
                        }

                        PF.obj.modal.form_data = {
                            action: new_album ? "create-album" : "move",
                            type: dealing_with,
                            owner: CHV.obj.resource.user.id,
                            multiple: true,
                            album: {
                                ids: ids,
                                new: new_album,
                            },
                        };

                        if (new_album) {
                            PF.obj.modal.form_data.album.name = $(
                                "[name=form-album-name]",
                                $modal
                            ).val();
                            PF.obj.modal.form_data.album.privacy = $(
                                "[name=form-privacy]",
                                $modal
                            ).val();
                            if (PF.obj.modal.form_data.album.privacy == "password") {
                                PF.obj.modal.form_data.album.password = $(
                                    "[name=form-album-password]",
                                    $modal
                                ).val();
                            }
                            PF.obj.modal.form_data.album.description = $(
                                "[name=form-album-description]",
                                $modal
                            ).val();
                        } else {
                            PF.obj.modal.form_data.album.id = $(
                                "[name=form-album-id]",
                                $modal
                            ).val();
                        }

                        return true;
                    },
                });

                break;

            case "approve":
                PF.fn.modal.call({
                    template: CHV.fn.modal.getTemplateWithPreviews("[data-modal=form-approve-multiple]", $targets),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                CHV.fn.list_editor.removeFromList(
                                    $targets,
                                    PF.fn._s("The content has been approved.")
                                );
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: "approve",
                            from: "list",
                            approve: dealing_with,
                            multiple: true,
                            approving: {
                                ids: ids,
                            },
                        };

                        return true;
                    },
                });

                break;

            case "delete":
                PF.fn.modal.call({
                    template: CHV.fn.modal.getTemplateWithPreviews("[data-modal=form-delete-multiple]", $targets),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                // unificar
                                if (dealing_with == "albums") {
                                    $targets.each(function () {
                                        $("[name=form-album-id]", "[data-modal]")
                                            .find('[value="' + $(this).data("id") + '"]')
                                            .remove();
                                    });
                                    CHV.fn.list_editor.updateUserCounters(
                                        "image",
                                        XHR.responseJSON.success.affected,
                                        "-"
                                    );
                                }
                                CHV.fn.list_editor.deleteFromList($targets);
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: "delete",
                            from: "list",
                            delete: dealing_with,
                            multiple: true,
                            deleting: {
                                ids: ids,
                            },
                        };

                        return true;
                    },
                });

                break;

            case "assign-category":
                var category_id = $targets.first().data("category-id"),
                    same_category = true;

                $targets.each(function () {
                    if ($(this).data("category-id") !== category_id) {
                        same_category = false;
                        return false;
                    }
                });

                PF.fn.modal.call({
                    type: "html",
                    template: CHV.fn.modal.getTemplateWithPreviews("[data-modal=form-assign-category]", $targets),
                    forced: true,
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                $targets.each(function () {
                                    var response = XHR.responseJSON;
                                    $(this).data("category-id", response.category_id);
                                });
                                CHV.fn.list_editor.clearSelected();
                            },
                        },
                    },
                    confirm: function () {
                        var $modal = $(PF.obj.modal.selectors.root),
                            form_category =
                                $("[name=form-category-id]", $modal).val() || null;

                        if (same_category && category_id == form_category) {
                            PF.fn.modal.close(function () {
                                CHV.fn.list_editor.clearSelected();
                            });
                            return false;
                        }

                        PF.obj.modal.form_data = {
                            action: "edit-category",
                            from: "list",
                            multiple: true,
                            editing: {
                                ids: ids,
                                category_id: form_category,
                            },
                        };
                        return true;
                    },
                });
                break;

            case "flag-safe":
            case "flag-unsafe":
                var action = $(this).data("action"),
                    flag = action == "flag-safe" ? "safe" : "unsafe";

                PF.fn.modal.call({
                    template: CHV.fn.modal.getTemplateWithPreviews("[data-modal=form-" + action + "]", $targets),
                    button_submit: PF.fn._s("Confirm"),
                    ajax: {
                        url: PF.obj.config.json_api,
                        deferred: {
                            success: function (XHR) {
                                $targets.each(function () {
                                    $(this)
                                        .removeClass("safe unsafe")
                                        .addClass(flag)
                                        .removeAttr("data-flag")
                                        .attr("data-flag", flag)
                                        .data("flag", flag);
                                });
                                CHV.fn.list_editor.clearSelected();
                            },
                        },
                    },
                    confirm: function () {
                        PF.obj.modal.form_data = {
                            action: action,
                            from: "list",
                            multiple: true,
                            editing: {
                                ids: ids,
                                nsfw: action == "flag-safe" ? 0 : 1,
                            },
                        };

                        return true;
                    },
                });

                break;
        }

        if (PF.fn.isDevice(["phone", "phablet"])) {
            return false;
        }
    });

    $(document).on("click", "[data-action=disconnect]", function () {
        var $this = $(this),
            connection = $this.data("connection");

        PF.fn.modal.confirm({
            message: $this.data("confirm-message"),
            ajax: {
                data: {
                    action: "disconnect",
                    disconnect: connection,
                    user_id: CHV.obj.resource.user.id,
                },
                deferred: {
                    success: function (XHR) {
                        var response = XHR.responseJSON;
                        $("[data-connection=" + connection + "]").fadeOut(function () {
                            $($("[data-connect=" + connection + "]")).fadeIn();
                            $(this).remove();
                            if ($("[data-connection]").length == 0) {
                                $("[data-content=empty-message]").show();
                            }
                            // PF.fn.growl.expirable(response.success.message);
                        });
                        if (response.success.redirect !== '') {
                            window.location.href = response.success.redirect;
                        }
                    },
                    error: function (XHR) {
                        var response = XHR.responseJSON;
                        PF.fn.growl.call(response.error.message);
                    },
                },
            },
        });
    });

    $(document).on("click", "[data-action=delete-avatar]", function () {
        var $parent = $(".user-settings-avatar"),
            $loading = $(".loading-placeholder", $parent),
            $top = $("#top-bar");

        $loading.removeClass("hidden");

        PF.fn.loading.inline($loading, {
            center: true,
        });

        $.ajax({
            type: "POST",
            data: {
                action: "delete",
                delete: "avatar",
                owner: CHV.obj.resource.user.id,
            },
        }).complete(function (XHR) {
            $loading.addClass("hidden").empty();
            if (XHR.status == 200) {
                if (CHV.obj.logged_user.id == CHV.obj.resource.user.id) {
                    $("img.user-image", $top).hide();
                    $(".default-user-image", $top).removeClass("hidden");
                }
                $(".default-user-image", $parent).removeClass("hidden").css({
                    opacity: 0,
                });
                $('[data-action="delete-avatar"]', $parent).parent().addClass("soft-hidden");
                $("img.user-image", $parent).fadeOut(function () {
                    $(".default-user-image", $parent).animate({
                        opacity: 1,
                    });
                });
            } else {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
            }
        });
    });

    $(document).on("change", "[data-content=user-avatar-upload-input]", function (
        e
    ) {
        e.preventDefault();
        e.stopPropagation();
        var $this = $(this),
            $parent = $(".user-settings-avatar"),
            $loading = $(".loading-placeholder", ".user-settings-avatar"),
            $top = $("#top-bar"),
            user_avatar_file = $(this)[0].files[0];

        if ($this.data("uploading")) {
            return;
        }
        if (/^image\/.*$/.test(user_avatar_file.type) == false) {
            PF.fn.growl.call(PF.fn._s("Please select a valid image file type."));
            return;
        }
        if (
            user_avatar_file.size > CHV.obj.config.user.avatar_max_filesize.getBytes()
        ) {
            PF.fn.growl.call(
                PF.fn._s(
                    "Please select a picture of at most %s size.",
                    CHV.obj.config.user.avatar_max_filesize
                )
            );
            return;
        }
        var deleteAvatar = $('[data-action="delete-avatar"]');
        $loading.removeClass("hidden");
        PF.fn.loading.inline($loading, {
            center: true,
        });
        $this.data("uploading", true);
        var user_avatar_fd = new FormData();
        user_avatar_fd.append("source", user_avatar_file);
        user_avatar_fd.append("action", "upload");
        user_avatar_fd.append("type", "file");
        user_avatar_fd.append("what", "avatar");
        user_avatar_fd.append("owner", CHV.obj.resource.user.id);
        user_avatar_fd.append("auth_token", PF.obj.config.auth_token);
        avatarXHR = new XMLHttpRequest();
        avatarXHR.open("POST", PF.obj.config.json_api, true);
        avatarXHR.send(user_avatar_fd);
        avatarXHR.onreadystatechange = function () {
            if (this.readyState == 4) {
                var response =
                    this.responseType !== "json" ?
                        JSON.parse(this.response) :
                        this.response,
                    image = response.success.image;

                $loading.addClass("hidden").empty();
                if (this.status == 200) {
                    change_avatar = function (parent) {
                        deleteAvatar.parent().removeClass("soft-hidden");
                        $("img.user-image", parent)
                            .attr("src", image.url)
                            .removeClass("hidden")
                            .show();
                    };
                    hide_default = function (parent) {
                        $(".default-user-image", parent).addClass("hidden");
                    };
                    hide_default($parent);
                    $(".btn-alt", $parent).closest("div").show();
                    change_avatar($parent);
                    if (CHV.obj.logged_user.id == CHV.obj.resource.user.id) {
                        change_avatar($top);
                        hide_default($top);
                    }
                } else {
                    PF.fn.growl.call(
                        PF.fn._s("An error occurred. Please try again later.")
                    );
                }

                $this.data("uploading", false);
            }
        };
    });

    $(document).on(
        "change",
        "[data-content=user-background-upload-input]",
        function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $this = $(this),
                $parent = $("[data-content=user-background-cover]"),
                $src = $("[data-content=user-background-cover-src]"),
                $loading = $(".loading-placeholder", $parent),
                $top = $("#top-bar"),
                user_file = $(this)[0].files[0];

            if ($this.data("uploading")) {
                return;
            }

            if (/^image\/.*$/.test(user_file.type) == false) {
                PF.fn.growl.call(PF.fn._s("Please select a valid image file type."));
                return;
            }

            if (
                user_file.size > CHV.obj.config.user.background_max_filesize.getBytes()
            ) {
                PF.fn.growl.call(
                    PF.fn._s(
                        "Please select a picture of at most %s size.",
                        CHV.obj.config.user.background_max_filesize
                    )
                );
                return;
            }

            $loading.removeClass("hidden");

            PF.fn.loading.inline($loading, {
                center: true,
                size: "big",
                color: "#FFF",
            });

            $this.data("uploading", true);

            var user_picture_fd = new FormData();
            user_picture_fd.append("source", user_file);
            user_picture_fd.append("action", "upload");
            user_picture_fd.append("type", "file");
            user_picture_fd.append("what", "background");
            user_picture_fd.append("owner", CHV.obj.resource.user.id);
            user_picture_fd.append("auth_token", PF.obj.config.auth_token);
            avatarXHR = new XMLHttpRequest();
            avatarXHR.open("POST", PF.obj.config.json_api, true);
            avatarXHR.send(user_picture_fd);
            avatarXHR.onreadystatechange = function () {
                if (this.readyState == 4) {
                    var response =
                        this.responseType !== "json" ?
                            JSON.parse(this.response) :
                            this.response,
                        image = response.success.image;

                    if (this.status == 200) {
                        var $img = $("<img/>");
                        $img.attr("src", image.url).imagesLoaded(function () {
                            $loading.addClass("hidden").empty();
                            $src
                                .css("background-image", "url(" + image.url + ")")
                                .hide()
                                .fadeIn();
                            $("[data-content=user-change-background]", $parent).removeClass(
                                "hidden"
                            );
                            $($parent).removeClass("no-background");
                            $(".top-user").removeClass("no-background");
                            $("[data-content=user-upload-background]").hide();
                            $("[data-content=user-change-background]").show();
                            $img.remove();
                        });
                    } else {
                        $loading.addClass("hidden").empty();
                        PF.fn.growl.call(
                            PF.fn._s("An error occurred. Please try again later.")
                        );
                    }

                    $this.data("uploading", false);
                }
            };
        }
    );

    $(document).on("keyup change", CHV.str.mainform + " :input", function () {
        if ($(this).is("[name=username]")) {
            $("[data-text=username]").text($(this).val());
        }
    });

    $(document).on("change", CHV.obj.timezone.input, function () {
        var value = $(this).val(),
            $timezone_combo = $("#timezone-combo-" + value);
        $timezone_combo.find("option:first").prop("selected", true);
        $(CHV.obj.timezone.selector).val($timezone_combo.val()).change();
    });
    $(document).on("change", "[id^=timezone-combo-]", function () {
        var value = $(this).val();
        $(CHV.obj.timezone.selector).val(value).change();
    });

    $(document).on("keyup change blur", "[name^=new-password]", function () {
        var $new_password = $("[name=new-password]"),
            $new_password_confirm = $("[name=new-password-confirm]"),
            hide = $new_password.val() == $new_password_confirm.val(),
            $warning = $new_password_confirm
                .closest(".input-password")
                .find(".input-warning");
        if ($warning.exists() == false) {
            $warning = $("[data-message=new-password-confirm]");
        }

        if ($(this).is($new_password_confirm)) {
            $new_password_confirm.data("touched", true);
        }

        if ($new_password_confirm.data("touched")) {
            $warning
                .text(!hide ? $warning.data("text") : "")[!hide ? "removeClass" : "addClass"]("hidden-visibility");
        }
    });

    $(document).on("submit", CHV.obj.mainform, function () {
        switch ($(this).data("type")) {
            case "password":
                var $p1 = $("[name=new-password]", this),
                    $p2 = $("[name=new-password-confirm]", this);
                if ($p1.val() !== "" || $p2.val() !== "") {
                    if ($p1.val() !== $p2.val()) {
                        $p1.highlight();
                        $p2.highlight();
                        PF.fn.growl.call(
                            PF.fn._s("Passwords don't match")
                        );
                        return false;
                    }
                }
                break;
        }
    });

    $(document).on("click", "[data-action=check-for-updates]", function () {
        PF.fn.loading.fullscreen();
        CHV.fn.system.checkUpdates(function (XHR) {
            PF.fn.loading.destroy("fullscreen");
            if (XHR.status !== 200) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            var data = XHR.responseJSON.software;
            if (
                PF.fn.versionCompare(
                    CHV.obj.system_info.version,
                    data.current_version
                ) == -1
            ) {
                let splitVersion = CHV.obj.system_info.version.split(".");
                let majorVersion = splitVersion[0];
                let minorVersion = majorVersion + '.' + splitVersion[1];
                let upgradeButtonTarget = "_self";
                let upgradeButtonSrc = PF.obj.config.base_url + 'dashboard/upgrade/?auth_token=' + PF.obj.config.auth_token;
                let upgradeButtonText = PF.fn._s("Upgrade");
                let upgradeButtonIcon = "fas fa-download";
                if(CHV.obj.system_info.servicing === 'docker') {
                    upgradeButtonTarget = "_blank";
                    upgradeButtonSrc = 'https://v4-docs.chevereto.com/guides/docker/#upgrading';
                    upgradeButtonText = PF.fn._s("Instructions");
                    upgradeButtonIcon = "fa-brands fa-docker";
                }
                PF.fn.modal.simple({
                    title: '<i class="fas fa-arrow-alt-circle-up"></i> ' + PF.fn._s("Chevereto v%s available", data.current_version),
                    message: "<p>" +
                        PF.fn._s("There is a new Chevereto version available with the following release notes.") +
                        ' ' +
                        PF.fn._s("Check %s for a complete changelog since you last upgrade.", '<a href="https://releases.chevereto.com/' + majorVersion + '.X/' + minorVersion + '/' + CHV.obj.system_info.version + '" target="_blank">' + CHV.obj.system_info.version + '<span class="btn-icon fas fas fa-code-branch"></span></a>') +
                        '</p>' +
                        '<textarea class="r4 resize-vertical">' +
                        data.release_notes.trim() +
                        "</textarea>" +
                        '<p>' +
                        PF.fn._s("Check the %s for alternative update methods.", '<a href="https://chevereto.com/go/v4update" target="_blank">' + PF.fn._s('documentation') + '</a>') +
                        '</p>' +
                        '<div class="btn-container margin-bottom-0">' +
                        '<a href="' +
                        upgradeButtonSrc +
                        '" class="btn btn-input accent" target="' +
                        upgradeButtonTarget +
                        '">' +
                        '<span class="btn-icon ' +
                        upgradeButtonIcon +
                        ' user-select-none"></span>' +
                        '<span class="btn-text user-select-none">' +
                        upgradeButtonText +
                        '</span>' +
                        '</a> ' +
                        '</div>',
                    html: true,
                });
            } else {
                PF.fn.growl.call(
                    PF.fn._s(
                        "This website is running latest %s version",
                        CHEVERETO.edition
                    )
                );
            }
        });
    });

    if (typeof PF.fn.get_url_var("checkUpdates") !== typeof undefined) {
        $("[data-action=check-for-updates]").trigger("click");
    }
    if (typeof PF.fn.get_url_var("upgrade") !== typeof undefined) {
        $("[data-action=upgrade]").trigger("click");
    }
    if (typeof PF.fn.get_url_var("license") !== typeof undefined) {
        $("[data-action='license']").trigger("click");
    }
    if (typeof PF.fn.get_url_var("welcome") !== typeof undefined) {
        PF.fn.modal.call({
            template: $("[data-modal=welcome]").html(),
            buttons: false,
        });
    }
    if (typeof PF.fn.get_url_var("installed") !== typeof undefined) {
        PF.fn.modal.simple({
            title: '<i class="fas fa-code-branch"></i> ' + PF.fn._s("Chevereto v%s installed", CHV.obj.system_info.version),
            message: "<p>" +
                PF.fn._s('Usage of Chevereto Software must be in compliance with the software license terms known as "The Chevereto License".') +
                '</p>' +
                '<div class="btn-container margin-bottom-0">' +
                '<a href="https://chevereto.com/license" target="_blank" class="btn btn-input accent">' +
                '<span class="btn-icon fas fa-file-contract user-select-none"></span>' +
                '<span class="btn-text user-select-none">' +
                PF.fn._s("License agreement") +
                '</span>' +
                '</a> ' +
                '</div>',
            html: true,
        });
    }
    $(document).on("click", "[data-action=system-update]", function (e) {
        if (!$("input#system-update").prop("checked")) {
            PF.fn.growl.call(
                PF.fn._s('Please review the system requirements before proceeding')
            );
            e.preventDefault();
            return;
        }
    });
    $(document).on("click", "[data-action=toggle-storage-https]", function () {
        CHV.fn.storage.toggleHttps(
            $(this).closest("[data-content=storage]").data("storage-id")
        );
    });
    $(document).on("click", "[data-action=toggle-storage-active]", function () {
        CHV.fn.storage.toggleActive(
            $(this).closest("[data-content=storage]").data("storage-id")
        );
    });

    if ($(CHV.fn.uploader.selectors.root).exists()) {
        CHV.fn.uploader.$pasteCatcher = $("<div />", {
            contenteditable: "true",
            id: CHV.fn.uploader.selectors.paste.replace(/#/, ""),
        });
        $("body").append(CHV.fn.uploader.$pasteCatcher);

        // Hack Ctrl/Cmd+V to focus pasteCatcher
        $(document).on("keydown", function (e) {
            if ((e.ctrlKey || e.metaKey) && e.originalEvent.code == 'KeyV' && !$(e.target).is(":input")) {
                PF.fn.keyFeedback.spawn(e);
                CHV.fn.uploader.$pasteCatcher.focus(e);
            }
        });
        document.addEventListener("dragover", function (e) {
            e.preventDefault();
        });
        document.addEventListener("drop", function (e) {
            if (!CHV.obj.config.upload.url) {
                return;
            }
            e.preventDefault();
            var imageUrl = e.dataTransfer.getData('text/html');
            var rex = /src="?([^"\s]+)"?\s*/;
            var url, res;
            url = rex.exec(imageUrl);
            if (url) {
                CHV.fn.uploader.toggle({ show: true });
                CHV.fn.uploader.add({}, url[1]);
            }
        });
        window.addEventListener("paste", CHV.fn.uploader.pasteImageHandler);
    }

    $(document).on("click", "[data-action=like]", function () {
        if (!PF.fn.is_user_logged()) {
            window.location.href = CHV.obj.vars.urls.login;
            return;
        }
        var $this = $(this);
        if ($this.data("XHR")) return;
        $this.data("XHR", true);
        var $object = $(this).is("[data-liked]") ?
            $(this) :
            $(this).closest("[data-liked]");
        var isSingle = !$object.closest("[data-list], .viewer").exists() &&
            typeof CHV.obj.resource !== typeof undefined;
        var liked = $object.is("[data-liked=1]");
        var action = !liked ? "like" : "dislike";
        var content = {
            id: isSingle ?
                CHV.obj.resource.id : $(this).closest("[data-id]").attr("data-id"),
            type: isSingle ?
                CHV.obj.resource.type : $(this).closest("[data-type]").attr("data-type"),
        };
        var $targets = isSingle ?
            $this :
            $("[data-type=" + content.type + "][data-id=" + content.id + "]");
        var ajax = {
            type: "POST",
            data: {
                action: action,
            },
            cache: false,
        };
        ajax.data[action] = {
            object: content.type,
            id: content.id,
        };
        $.ajax(ajax).complete(function (XHR) {
            var response = XHR.responseJSON;
            $this.data("XHR", false);
            if (response.status_code !== 200) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            if (isSingle && typeof response.content !== typeof undefined) {
                $("[data-text=likes-count]").html(response.content.likes);
            }
            $targets.closest("[data-liked]").attr("data-liked", liked ? 0 : 1);
        });
    });

    $(document).on("click", "[data-action=album-cover]", function () {
        var $this = $(this);
        if ($this.data("XHR")) return;
        $this.data("XHR", true);
        var $object = $(this).is("[data-cover]") ?
            $(this) :
            $(this).closest("[data-cover]");
        var covered = $object.is("[data-cover=1]");
        var action = !covered ? "album-cover-set" : "album-cover-unset";
        var content = {
            id: CHV.obj.resource.id,
            type: 'image',
        };

        var $targets = $this.closest("[data-cover]");
        var ajax = {
            type: "POST",
            data: {
                action: action,
            },
            cache: false,
        };
        ajax.data[action] = {
            "album_id": $targets.data("album-id"),
            "image_id": $targets.data("id"),
        };
        $.ajax(ajax).complete(function (XHR) {
            var response = XHR.responseJSON;
            $this.data("XHR", false);
            if (response.status_code !== 200) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            $targets.attr("data-cover", covered ? 0 : 1);
        });
    });

    $(document).on("click", "[data-action=follow]", function () {
        if (!PF.fn.is_user_logged()) {
            PF.fn.modal.call({
                type: "login",
            });
            return;
        }

        var $this = $(this);
        if ($this.data("XHR")) return;
        $this.data("XHR", true);

        var $object = $(this).is("[data-followed]") ?
            $(this) :
            $(this).closest("[data-followed]");
        var isSingle = typeof CHV.obj.resource !== typeof undefined;
        var followed = $object.is("[data-followed=1]");
        var action = !followed ? "follow" : "unfollow";
        var content = {
            id: isSingle ?
                CHV.obj.resource.id : $(this).closest("[data-id]").data("id"),
            type: isSingle ?
                CHV.obj.resource.type : $(this).closest("[data-type]").data("type"),
        };
        var ajax = {
            type: "POST",
            data: {
                action: action,
            },
            cache: false,
        };
        ajax.data[action] = {
            object: content.type,
            id: content.id,
        };
        $.ajax(ajax).complete(function (XHR) {
            var response = XHR.responseJSON;
            $this.data("XHR", false);
            if (response.status_code !== 200) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            if (isSingle) {
                if (typeof response.user_followed !== typeof undefined) {
                    var $followersLabel = $("[data-text=followers-label]");
                    var label = {
                        single: $followersLabel.data("label-single"),
                        plural: $followersLabel.data("label-plural"),
                    };
                    $("[data-text=followers-count]").html(
                        response.user_followed.followers
                    );
                    $followersLabel.html(
                        PF.fn._n(
                            label.single,
                            label.plural,
                            response.user_followed.followers
                        )
                    );
                }
            }
            $object.attr("data-followed", followed ? 0 : 1); // Toggle indicator
        });
    });

    $(document).on("click", "[data-action=user_ban],[data-action=user_unban]", function () {
        var $this = $(this);
        if ($this.data("XHR")) return;
        $this.data("XHR", true);
        var $object = $(this).closest("[data-banned]");
        var isSingle = true;
        var banned = $object.is("[data-banned=1]");
        var action = $this.attr("data-action");
        var content = {
            id: isSingle ?
                CHV.obj.resource.id : $(this).closest("[data-id]").data("id"),
            type: isSingle ?
                CHV.obj.resource.type : $(this).closest("[data-type]").data("type"),
        };
        var ajax = {
            type: "POST",
            data: {
                action: action,
            },
            cache: false,
        };
        ajax.data[action] = {
            user_id: content.id,
        };
        $.ajax(ajax).complete(function (XHR) {
            var response = XHR.responseJSON;
            $this.data("XHR", false);
            if (response.status_code !== 200) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                return;
            }
            $object.attr("data-banned", banned ? 0 : 1);
        });
    });

    function notifications_scroll() {
        if (PF.fn.isDevice(["phone", "phablet"])) return;
        var $visible_list = $(".top-bar-notifications-list ul", ".top-bar:visible");
        var height;
        var height_auto;
        $visible_list.css("height", ""); // Reset any change
        height = $visible_list.height();
        $visible_list.data("height", height).css("height", "auto");
        height_auto = $visible_list.height();
        if (height_auto > height) {
            $visible_list.height(height);
            $visible_list.closest(".antiscroll-wrap").antiscroll();
        }
    }

    $(document).on("click", "[data-action=top-bar-notifications]", function (e) {
        var _this = this;
        var $this = $(this);
        var $container = $(".top-bar-notifications-container", $this);
        var $list = $(".top-bar-notifications-list", $this);
        var $ul = $("ul", $list);
        var $loading = $(".loading", $container);
        if ($this.data("XHR")) {
            return;
        } else {
            $loading.removeClass("hidden");
            PF.fn.loading.inline($loading, {
                size: "small",
                message: PF.fn._s("loading"),
            });
        }
        $.ajax({
            type: "POST",
            data: {
                action: "notifications",
            },
            cache: false,
        }).complete(function (XHR) {
            var response = XHR.responseJSON;
            if (response.status_code !== 200) {
                PF.fn.growl.call(
                    PF.fn._s("An error occurred. Please try again later.")
                );
                $this.data("XHR", false);
                $loading.addClass("hidden").html("");
                return;
            }
            $this.data("XHR", true);
            $loading.remove();
            if (!response.html) {
                $(".empty", $container).removeClass("hidden");
                return;
            }
            $list.removeClass("hidden");
            $ul.html(response.html);
            notifications_scroll();
            var $li = $("li.new", $ul);
            $li.addClass("transition");
            setTimeout(function () {
                $li.removeClass("new");
                $("[data-content=notifications-counter]", _this)
                    .removeClass("on")
                    .html("0");
                setTimeout(function () {
                    $li.removeClass("transition");
                }, 150);
            }, 1500);
        });
    });

    if (
        $("#g-recaptcha").is(":empty") &&
        CHV.obj.config.captcha.enabled &&
        CHV.obj.config.captcha.sitekey
    ) {
        if(CHV.obj.config.captcha.version == '3' || !CHV.obj.config.captcha.isNeeded) {
            $('label[for="recaptcha_response_field"]').remove();
        }
    }

    $(document).on("click", PF.obj.listing.selectors.list_item + " a.image-container", function (e) {
        var $parent = $(this).closest(PF.obj.listing.selectors.list_item);
        var $loadBtn = $parent.find("[data-action=load-image]");
        if ($loadBtn.length > 0) {
            loadImageListing($loadBtn);
            e.preventDefault();
        }
        return;
    });

    $(document).on("click", PF.obj.listing.selectors.list_item + " [data-action=load-image]", function (e) {
        loadImageListing($(this));
        e.preventDefault();
        e.stopPropagation();
        return;
    });

    function loadImageListing($this) {
        $this.addClass("list-item-play-gif--loading");
        var $parent = $this.closest(PF.obj.listing.selectors.list_item);
        var $imageContainer = $(".image-container", $parent);
        var $image = $("img", $imageContainer);
        var imageSrc = $image.attr("src");
        // alert(imageSrc)
        var md = ".md";
        var mdIndex = imageSrc.lastIndexOf(md);
        if (mdIndex == -1) {
            var md = ".th";
            var mdIndex = imageSrc.lastIndexOf(md);
        }
        var loadSrc =
            imageSrc.substr(0, mdIndex) +
            imageSrc.substr(mdIndex + md.length, imageSrc.length);

        $imageContainer.append($imageContainer.html());
        $load = $parent
            .find(".image-container img")
            .eq(1)
            .attr("src", loadSrc)
            .addClass("hidden");
        $load.imagesLoaded(function () {
            $this.remove();
            $image.remove();
            $("img", $imageContainer).show();
            $(this.elements).removeClass("hidden");
        });
    }

    $(document).on("click", "#album [data-tab=tab-embeds]", function (e) {
        e.preventDefault;
        CHV.fn.album.showEmbedCodes();
    });

    if ($("body").is("#upload")) {
        CHV.fn.uploader.toggle({
            show: true,
        });
    }

    $(document).on("keyup", function (e) {
        if ($(e.target).is(":input") || (e.ctrlKey || e.metaKey || e.altKey)) {
            return;
        }
        var isModalVisible = $("#fullscreen-modal:visible").exists();
        var $viewer = $(".viewer");
        var $listSelection = $(".list-selection:visible");
        var $listTools = $listSelection.find("[data-content=pop-selection]:visible:not(.disabled)");
        var viewerShown = $("body").hasClass("--viewer-shown");
        var uploaderShown = $(CHV.fn.uploader.selectors.root + CHV.fn.uploader.selectors.show).exists();
        var keyCode = e.originalEvent.code;
        if (e.originalEvent.code === 'Escape') {
            if (isModalVisible) {
                return;
            }
            if (uploaderShown) {
                CHV.fn.uploader.toggle({ reset: false });
            }
        }
        if ($viewer.exists() && viewerShown) {
            if (keyCode in CHV.fn.listingViewer.keys) {
                var direct = ["KeyW", "Escape", "ArrowLeft", "ArrowRight"];
                var action = CHV.fn.listingViewer.keys[keyCode];
                if (direct.indexOf(keyCode) == -1) {
                    $("[data-action=" + action + "]", CHV.fn.listingViewer.selectors.root).click();
                } else {
                    if (action in CHV.fn.listingViewer) {
                        CHV.fn.listingViewer[action]();
                    }
                }
                PF.fn.keyFeedback.spawn(e);
            }
            return;
        }
        var $button;
        var keyMapListing = {
            'Period': 'list-select-all',
            'KeyK': 'get-embed-codes',
            'KeyZ': 'clear',
            'KeyA': 'create-album',
            'KeyM': 'move',
            'KeyO': 'approve',
            'Delete': 'delete',
            'Backspace': 'delete',
            'KeyC': 'assign-category',
            'KeyV': 'flag-safe',
            'KeyF': 'flag-unsafe',
            'KeyH': 'album-cover',
        }
        var keyMapResource = {
            'KeyE': 'edit',
            'KeyL': 'like',
            'KeyS': 'share',
            'KeyJ': 'sub-album',
            'KeyP': 'upload-to-album',
        }
        var action = keyMapListing[keyCode] || keyMapResource[keyCode];
        if (typeof action == typeof undefined) {
            return;
        }
        if ($listSelection.exists()) {
            if (!viewerShown && !isModalVisible) {
                if (parseInt($('[data-text=selection-count]:visible', $listTools).text()) > 0) {
                    $button = $("[data-action=" + action + "]", $listSelection.closest(".list-selection"));
                }
            }
        }
        if (typeof $button === typeof undefined) {
            $button = $("[data-action=" + action + "]:visible").not("#content-listing-tabs *");
        }
        if ($button instanceof jQuery && $button.length > 0) {
            $button.first().trigger("click");
            PF.fn.keyFeedback.spawn(e);
        }
    });

    $(document).on(
        "click",
        CHV.fn.listingViewer.selectors.root + " [data-action^=viewer-]",
        function () {
            var action = $(this).data("action").substring("viewer-".length);
            if (action in CHV.fn.listingViewer) {
                CHV.fn.listingViewer[action]();
            }
        }
    );

    $(document).on(
        "click",
        "a[data-href]:not([rel=popup-link]):not(.popup-link)",
        function () {
            var data = $(this).attr("data-href");
            var href = $(this).attr("href");
            if (!data && !href) return;
            location.href = href ? href : data;
        }
    );
    function toggleListSelect(that, e) {
        var $item = $(that).closest(PF.obj.listing.selectors.list_item);
        CHV.fn.list_editor.blink($item);
        CHV.fn.list_editor.toggleSelectItem($item);
        PF.fn.keyFeedback.spawn(e);
        e.preventDefault();
        e.stopPropagation();
    }
    var selectableItemSelector = PF.obj.listing.selectors.list_item + ", .image-container";
    $(document).on("contextmenu click", selectableItemSelector, function (e) {
        if (!$(".list-selection:visible").exists()
            || $(e.target).closest(".list-item-desc").exists()
            || $(this).closest(CHV.fn.listingViewer.selectors.root).exists()
            || (e.type == "click" && !(e.ctrlKey || e.metaKey))
        ) {
            return;
        }
        toggleListSelect(this, e);
    });
    if(navigator.userAgent.match(/(iPad|iPhone|iPod)/i)) {
        var pressTimer;
        $(document)
            .on("mouseup mousemove", selectableItemSelector, function(e) {
                clearTimeout(pressTimer);
                return false;
            })
            .on("mousedown", selectableItemSelector, function(e) {
                var that = this;
                var event = e;
                pressTimer = window.setTimeout(function() {
                    if (!$(".list-selection:visible").exists()
                        || $(that).closest(CHV.fn.listingViewer.selectors.root).exists()) {
                        return;
                    }
                    toggleListSelect(that, event);
                }, 500);
                return false;
            });
    }

    if (typeof CHV.obj.config !== typeof undefined &&
        CHV.obj.config.listing.viewer
    ) {
        $(document).on(
            "click",
            PF.obj.listing.selectors.list_item + "[data-type=image] .image-container",
            function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (e.clientX === 0 && e.clientY === 0) {
                    PF.fn.keyFeedback.spawn(e);
                    return;
                }
                var $item = $(this).closest(PF.obj.listing.selectors.list_item);
                if (!$item.exists()) return;
                if (e.ctrlKey || e.metaKey) {
                    return;
                }
                CHV.fn.listingViewer.open($item);
            }
        );
    }

    $(document).on(
        "contextmenu",
        CHV.fn.listingViewer.selectors.root,
        function (e) {
            e.preventDefault();
            CHV.fn.listingViewer.zoom();
            PF.fn.keyFeedback.spawn(e);
            return false;
        }
    );

    var UrlParams = PF.fn.deparam(window.location.search);
    if (UrlParams && "viewer" in UrlParams) {
        var $parent = $(PF.obj.listing.selectors.content_listing_visible);
        if ($parent.data("list") == "images") {
            var $item = $(PF.obj.listing.selectors.list_item, $parent)[
                UrlParams.viewer == "next" ? "first" : "last"
            ]();
            CHV.fn.listingViewer.open($item);
        }
    }
    var resizeTimer;
    var loadListingFn = function () {
        $(PF.obj.listing.selectors.list_item + ":visible").each(function () {
            var loadBtn = $(this).find('[data-action="load-image"]').first();
            var paramsHidden = PF.fn.parseQueryString(
                $(PF.obj.listing.selectors.list_item + '[data-id=' + $(this).attr("data-id") + ']').closest(".content-listing").data("params-hidden")
            );
            var autoLoad = paramsHidden && "is_animated" in paramsHidden ?
                paramsHidden.is_animated :
                $(this).data("size") <= CHV.obj.config.image.load_max_filesize.getBytes();
            if (loadBtn.exists() && autoLoad && $(this).is_within_viewport(50)) {
                loadImageListing(loadBtn);
            }
        });
    };
    $(window).on("DOMContentLoaded load", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(loadListingFn, 500);
    });
    $(window).on("resize scroll", function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(loadListingFn, 1000);
    });
    $(document).on("click", "[data-action=logout]", function () {
        let $form = $("form#form-logout");
        $form.submit();
    });

    if(Boolean(window.navigator.vibrate)) {
        $(document).on("click",
            "button, .btn, .pop-btn, .top-btn-el, [data-action], .content-tabs a, .top-bar-logo a, .login-provider-button, .panel-share-networks li a, #image-viewer-loader",
            function(e) {
                if($(this).is("[data-action=top-bar-menu-full]")
                    || e.isPropagationStopped
                    || e.isDefaultPrevented
                ) {
                    return;
                }
                window.navigator.vibrate([0, 15]);
            }
        );
    }

    $(document).on("change keyup", CHV.fn.ctaForm.selectors.rows + " input[name^='cta-']", function() {
        CHV.fn.ctaForm.update($(this));
    });

    $(document).on("click", CHV.fn.ctaForm.selectors.rows + " [data-action=cta-add]", function () {
        CHV.fn.ctaForm.insert($(this));
    });

    $(document).on("click", CHV.fn.ctaForm.selectors.rows + " [data-action=cta-remove]", function () {
        CHV.fn.ctaForm.remove($(this));
        if(CHV.fn.ctaForm.array.length == 0) {
            $(CHV.fn.ctaForm.selectors.root + " " + CHV.fn.ctaForm.selectors.enable).prop("checked", false).trigger("change");
        }

    });
    $(document).on("change", CHV.fn.ctaForm.selectors.root + " " + CHV.fn.ctaForm.selectors.enable, function() {
        let $combo = $(CHV.fn.ctaForm.selectors.combo, CHV.fn.ctaForm.selectors.root);
        let checked = $(this).is(":checked");
        $combo.toggleClass("soft-hidden", !checked);
        if(checked) {
            if(CHV.fn.ctaForm.array.length == 0) {
                CHV.fn.ctaForm.add();
            }
            CHV.fn.ctaForm.render();
        }
        CHV.fn.ctaForm.setEnable(checked ? 1 : 0);
    });

    $(document).on("change keyup", CHV.fn.ctaForm.selectors.root + " input[name^='cta-icon_']", function() {
        let $row = CHV.fn.ctaForm.getRow($(this));
        let $icon = $row.find("label[for^='cta-icon_'] [data-content=icon]");
        $icon.removeClass();
        let iconClass = CHV.fn.ctaForm.getIconClass($(this).val());
        $icon.addClass(iconClass);
    });

    $(document).on("click", "[href^='https://chevereto.com/']", function(e) {
        let hasBadge = $(this).find(".badge--paid").exists();
        if(!hasBadge) {
            return;
        }
        let href = $(this).attr("href");
        let buyFrom = PF.fn._s('Get a license at %s to unlock all features and support.', '<a href="'+href+'" target="_blank">chevereto.com</a>');
        let instructions = PF.fn._s('You can enter your license key in the dashboard panel.');
        let buttonHref = PF.obj.config.base_url + 'dashboard/?license';
        let buttonTarget = "_self";
        let buttonIcon = "fas fa-key";
        let buttonText = PF.fn._s("Enter license");
        if(CHV.obj.system_info.servicing === 'docker') {
            instructions = PF.fn._s('You can upgrade by following the instructions in the documentation.');
            buttonHref = 'https://v4-docs.chevereto.com/guides/docker/#upgrading';
            buttonTarget = "_blank";
            buttonIcon = "fa-brands fa-docker";
            buttonText = PF.fn._s("Instructions");
        }
        e.preventDefault();
        e.stopPropagation();
        PF.fn.modal.simple({
            html: true,
            title: '<i class="fa-solid fa-boxes-packing"></i> Upgrade Chevereto',
            message: "<p>" + buyFrom +
            " " + instructions +  "</p>" +
            '<div class="btn-container margin-bottom-0">' +
            '<a href="' + buttonHref + '" target="' + buttonTarget + '" class="btn btn-input accent">' +
            '<span class="btn-icon ' + buttonIcon + ' user-select-none"></span>' +
            '<span class="btn-text user-select-none">' + buttonText + '</span>' +
            '</a> ' +
            '</div>',
        });
    });
    $(document).on("focus", "input[name='form-album-password']", function() {
        $(this).get(0).type = "text";
    });
    $(document).on("blur", "input[name='form-album-password']", function() {
        $(this).get(0).type = "password";
    });

    $("button[type=submit]", "form").on("click", function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).closest("form").trigger("submit");
    });

    var tagAutocompleteItemTpl = $("template#tags-autocomplete-item").html();
    var tagAutocompleteCache = {};
    function autocompleteItems(items, template, $target) {
        var $input = $('input[data-target="#' + $target.attr("id") + '"]');
        var tags = $input.val().split(",").map(s => s.trim());
        $target.empty();
        $.each(items, function(i, item) {
            if(tags.includes(item)) {
                return;
            }
            var itemHtml = template.replace(/%name%/g, PF.fn.htmlEncode(item));
            $target.append($(itemHtml));
        });
    }
    $(document).on("keyup", "input[data-autocomplete=tags]", function(e) {
        var value = $(this).val();
        var tryHandle = /[^\,]*$/.exec(value)[0];
        var $target = $($(this).data("target"));
        tryHandle = tryHandle.trim();
        if(tryHandle.length < 1 || e.key === 'Escape') {
            $target.empty();
            return;
        }
        if(tryHandle in tagAutocompleteCache) {
            autocompleteItems(tagAutocompleteCache[tryHandle], tagAutocompleteItemTpl, $target);
        } else {
            $.ajax({
                url: PF.obj.config.base_url + "tag-autocomplete/",
                data: { q: tryHandle },
                type: "GET",
                dataType: "json",
                cache: true,
            }).always(function (data) {
                if (data.status_code == 200) {
                    tagAutocompleteCache[tryHandle] = data.items;
                    autocompleteItems(data.items, tagAutocompleteItemTpl, $target);
                }
            });
        }
    });
    $(document).on("click", ".content-tags-autocomplete li", function() {
        var value = $(this).text().trim();
        var $parent = $(this).closest('.content-tags-autocomplete');
        var targetId = $parent.attr("id");
        var $target = $('input[data-target="#' + targetId + '"]');
        var targetValue = $target.val();
        var tags = targetValue.split(",").map(s => s.trim());
        $parent.empty();
        if(tags.includes(value)) {
            return;
        }
        tags.pop();
        tags.push(value);
        $target.val(tags.join(", "));
    });
    $(document).on("blur", "input[data-autocomplete=tags]", function() {
        var $target = $($(this).data("target"));
        setTimeout(function() {
            if($target.find(":active").length === 0) {
                $target.empty();
            }
        }, 150);
    });
});

