import{r as A,c as E,g as I}from"./vendor-DNxQnhHY.js";var z={exports:{}};/*!
 * typeahead.js 0.11.1
 * https://github.com/twitter/typeahead.js
 * Copyright 2013-2015 Twitter, Inc. and other contributors; Licensed MIT
 */(function(x,N){(function(d,c){x.exports=c(A())})(E,function(d){var c=function(){return{isMsie:function(){return/(msie|trident)/i.test(navigator.userAgent)?navigator.userAgent.match(/(msie |rv:)(\d+(.\d+)?)/i)[2]:!1},isBlankString:function(n){return!n||/^\s*$/.test(n)},escapeRegExChars:function(n){return n.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g,"\\$&")},isString:function(n){return typeof n=="string"},isNumber:function(n){return typeof n=="number"},isArray:d.isArray,isFunction:d.isFunction,isObject:d.isPlainObject,isUndefined:function(n){return typeof n>"u"},isElement:function(n){return!!(n&&n.nodeType===1)},isJQuery:function(n){return n instanceof d},toStr:function(o){return c.isUndefined(o)||o===null?"":o+""},bind:d.proxy,each:function(n,o){d.each(n,s);function s(i,a){return o(a,i)}},map:d.map,filter:d.grep,every:function(n,o){var s=!0;return n?(d.each(n,function(i,a){if(!(s=o.call(null,a,i,n)))return!1}),!!s):s},some:function(n,o){var s=!1;return n?(d.each(n,function(i,a){if(s=o.call(null,a,i,n))return!1}),!!s):s},mixin:d.extend,identity:function(n){return n},clone:function(n){return d.extend(!0,{},n)},getIdGenerator:function(){var n=0;return function(){return n++}},templatify:function(o){return d.isFunction(o)?o:s;function s(){return String(o)}},defer:function(n){setTimeout(n,0)},debounce:function(n,o,s){var i,a;return function(){var t=this,e=arguments,r,u;return r=function(){i=null,s||(a=n.apply(t,e))},u=s&&!i,clearTimeout(i),i=setTimeout(r,o),u&&(a=n.apply(t,e)),a}},throttle:function(n,o){var s,i,a,t,e,r;return e=0,r=function(){e=new Date,a=null,t=n.apply(s,i)},function(){var u=new Date,h=o-(u-e);return s=this,i=arguments,h<=0?(clearTimeout(a),a=null,e=u,t=n.apply(s,i)):a||(a=setTimeout(r,h)),t}},stringify:function(n){return c.isString(n)?n:JSON.stringify(n)},noop:function(){}}}(),T="0.11.1",q=function(){return{nonword:o,whitespace:n,obj:{nonword:s(o),whitespace:s(n)}};function n(i){return i=c.toStr(i),i?i.split(/\s+/):[]}function o(i){return i=c.toStr(i),i?i.split(/\W+/):[]}function s(i){return function(t){return t=c.isArray(t)?t:[].slice.call(arguments,0),function(r){var u=[];return c.each(t,function(h){u=u.concat(i(c.toStr(r[h])))}),u}}}}(),w=function(){function n(i){this.maxSize=c.isNumber(i)?i:100,this.reset(),this.maxSize<=0&&(this.set=this.get=d.noop)}c.mixin(n.prototype,{set:function(a,t){var e=this.list.tail,r;this.size>=this.maxSize&&(this.list.remove(e),delete this.hash[e.key],this.size--),(r=this.hash[a])?(r.val=t,this.list.moveToFront(r)):(r=new s(a,t),this.list.add(r),this.hash[a]=r,this.size++)},get:function(a){var t=this.hash[a];if(t)return this.list.moveToFront(t),t.val},reset:function(){this.size=0,this.hash={},this.list=new o}});function o(){this.head=this.tail=null}c.mixin(o.prototype,{add:function(a){this.head&&(a.next=this.head,this.head.prev=a),this.head=a,this.tail=this.tail||a},remove:function(a){a.prev?a.prev.next=a.next:this.head=a.next,a.next?a.next.prev=a.prev:this.tail=a.prev},moveToFront:function(i){this.remove(i),this.add(i)}});function s(i,a){this.key=i,this.val=a,this.prev=this.next=null}return n}(),R=function(){var n;try{n=window.localStorage,n.setItem("~~~","!"),n.removeItem("~~~")}catch{n=null}function o(e,r){this.prefix=["__",e,"__"].join(""),this.ttlKey="__ttl__",this.keyMatcher=new RegExp("^"+c.escapeRegExChars(this.prefix)),this.ls=r||n,!this.ls&&this._noop()}return c.mixin(o.prototype,{_prefix:function(e){return this.prefix+e},_ttlKey:function(e){return this._prefix(e)+this.ttlKey},_noop:function(){this.get=this.set=this.remove=this.clear=this.isExpired=c.noop},_safeSet:function(e,r){try{this.ls.setItem(e,r)}catch(u){u.name==="QuotaExceededError"&&(this.clear(),this._noop())}},get:function(e){return this.isExpired(e)&&this.remove(e),a(this.ls.getItem(this._prefix(e)))},set:function(e,r,u){return c.isNumber(u)?this._safeSet(this._ttlKey(e),i(s()+u)):this.ls.removeItem(this._ttlKey(e)),this._safeSet(this._prefix(e),i(r))},remove:function(e){return this.ls.removeItem(this._ttlKey(e)),this.ls.removeItem(this._prefix(e)),this},clear:function(){var e,r=t(this.keyMatcher);for(e=r.length;e--;)this.remove(r[e]);return this},isExpired:function(e){var r=a(this.ls.getItem(this._ttlKey(e)));return!!(c.isNumber(r)&&s()>r)}}),o;function s(){return new Date().getTime()}function i(e){return JSON.stringify(c.isUndefined(e)?null:e)}function a(e){return d.parseJSON(e)}function t(e){var r,u,h=[],l=n.length;for(r=0;r<l;r++)(u=n.key(r)).match(e)&&h.push(u.replace(e,""));return h}}(),_=function(){var n=0,o={},s=6,i=new w(10);function a(t){t=t||{},this.cancelled=!1,this.lastReq=null,this._send=t.transport,this._get=t.limiter?t.limiter(this._get):this._get,this._cache=t.cache===!1?new w(0):i}return a.setMaxPendingRequests=function(e){s=e},a.resetCache=function(){i.reset()},c.mixin(a.prototype,{_fingerprint:function(e){return e=e||{},e.url+e.type+d.param(e.data||{})},_get:function(t,e){var r=this,u,h;if(u=this._fingerprint(t),this.cancelled||u!==this.lastReq)return;(h=o[u])?h.done(l).fail(f):n<s?(n++,o[u]=this._send(t).done(l).fail(f).always(m)):this.onDeckRequestArgs=[].slice.call(arguments,0);function l(p){e(null,p),r._cache.set(u,p)}function f(){e(!0)}function m(){n--,delete o[u],r.onDeckRequestArgs&&(r._get.apply(r,r.onDeckRequestArgs),r.onDeckRequestArgs=null)}},get:function(t,e){var r,u;e=e||d.noop,t=c.isString(t)?{url:t}:t||{},u=this._fingerprint(t),this.cancelled=!1,this.lastReq=u,(r=this._cache.get(u))?e(null,r):this._get(t,e)},cancel:function(){this.cancelled=!0}}),a}(),k=window.SearchIndex=function(){var n="c",o="i";function s(r){r=r||{},(!r.datumTokenizer||!r.queryTokenizer)&&d.error("datumTokenizer and queryTokenizer are both required"),this.identify=r.identify||c.stringify,this.datumTokenizer=r.datumTokenizer,this.queryTokenizer=r.queryTokenizer,this.reset()}return c.mixin(s.prototype,{bootstrap:function(u){this.datums=u.datums,this.trie=u.trie},add:function(r){var u=this;r=c.isArray(r)?r:[r],c.each(r,function(h){var l,f;u.datums[l=u.identify(h)]=h,f=i(u.datumTokenizer(h)),c.each(f,function(m){var p,g,v;for(p=u.trie,g=m.split("");v=g.shift();)p=p[n][v]||(p[n][v]=a()),p[o].push(l)})})},get:function(u){var h=this;return c.map(u,function(l){return h.datums[l]})},search:function(u){var h=this,l,f;return l=i(this.queryTokenizer(u)),c.each(l,function(m){var p,g,v,y;if(f&&f.length===0)return!1;for(p=h.trie,g=m.split("");p&&(v=g.shift());)p=p[n][v];if(p&&g.length===0)y=p[o].slice(0),f=f?e(f,y):y;else return f=[],!1}),f?c.map(t(f),function(m){return h.datums[m]}):[]},all:function(){var u=[];for(var h in this.datums)u.push(this.datums[h]);return u},reset:function(){this.datums={},this.trie=a()},serialize:function(){return{datums:this.datums,trie:this.trie}}}),s;function i(r){return r=c.filter(r,function(u){return!!u}),r=c.map(r,function(u){return u.toLowerCase()}),r}function a(){var r={};return r[o]=[],r[n]={},r}function t(r){for(var u={},h=[],l=0,f=r.length;l<f;l++)u[r[l]]||(u[r[l]]=!0,h.push(r[l]));return h}function e(r,u){var h=0,l=0,f=[];r=r.sort(),u=u.sort();for(var m=r.length,p=u.length;h<m&&l<p;)r[h]<u[l]?h++:(r[h]>u[l]||(f.push(r[h]),h++),l++);return f}}(),S=function(){var n;n={data:"data",protocol:"protocol",thumbprint:"thumbprint"};function o(s){this.url=s.url,this.ttl=s.ttl,this.cache=s.cache,this.prepare=s.prepare,this.transform=s.transform,this.transport=s.transport,this.thumbprint=s.thumbprint,this.storage=new R(s.cacheKey)}return c.mixin(o.prototype,{_settings:function(){return{url:this.url,type:"GET",dataType:"json"}},store:function(i){this.cache&&(this.storage.set(n.data,i,this.ttl),this.storage.set(n.protocol,location.protocol,this.ttl),this.storage.set(n.thumbprint,this.thumbprint,this.ttl))},fromCache:function(){var i={},a;return this.cache?(i.data=this.storage.get(n.data),i.protocol=this.storage.get(n.protocol),i.thumbprint=this.storage.get(n.thumbprint),a=i.thumbprint!==this.thumbprint||i.protocol!==location.protocol,i.data&&!a?i.data:null):null},fromNetwork:function(s){var i=this,a;if(!s)return;a=this.prepare(this._settings()),this.transport(a).fail(t).done(e);function t(){s(!0)}function e(r){s(null,i.transform(r))}},clear:function(){return this.storage.clear(),this}}),o}(),b=function(){function n(o){this.url=o.url,this.prepare=o.prepare,this.transform=o.transform,this.transport=new _({cache:o.cache,limiter:o.limiter,transport:o.transport})}return c.mixin(n.prototype,{_settings:function(){return{url:this.url,type:"GET",dataType:"json"}},get:function(s,i){var a=this,t;if(!i)return;return s=s||"",t=this.prepare(s,this._settings()),this.transport.get(t,e);function e(r,u){i(r?[]:a.transform(u))}},cancelLastRequest:function(){this.transport.cancel()}}),n}(),C=function(){return function(e){var r,u;return r={initialize:!0,identify:c.stringify,datumTokenizer:null,queryTokenizer:null,sufficient:5,sorter:null,local:[],prefetch:null,remote:null},e=c.mixin(r,e||{}),!e.datumTokenizer&&d.error("datumTokenizer is required"),!e.queryTokenizer&&d.error("queryTokenizer is required"),u=e.sorter,e.sorter=u?function(h){return h.sort(u)}:c.identity,e.local=c.isFunction(e.local)?e.local():e.local,e.prefetch=n(e.prefetch),e.remote=o(e.remote),e};function n(t){var e;return t?(e={url:null,ttl:24*60*60*1e3,cache:!0,cacheKey:null,thumbprint:"",prepare:c.identity,transform:c.identity,transport:null},t=c.isString(t)?{url:t}:t,t=c.mixin(e,t),!t.url&&d.error("prefetch requires url to be set"),t.transform=t.filter||t.transform,t.cacheKey=t.cacheKey||t.url,t.thumbprint=T+t.thumbprint,t.transport=t.transport?a(t.transport):d.ajax,t):null}function o(t){var e;if(t)return e={url:null,cache:!0,prepare:null,replace:null,wildcard:null,limiter:null,rateLimitBy:"debounce",rateLimitWait:300,transform:c.identity,transport:null},t=c.isString(t)?{url:t}:t,t=c.mixin(e,t),!t.url&&d.error("remote requires url to be set"),t.transform=t.filter||t.transform,t.prepare=s(t),t.limiter=i(t),t.transport=t.transport?a(t.transport):d.ajax,delete t.replace,delete t.wildcard,delete t.rateLimitBy,delete t.rateLimitWait,t}function s(t){var e,r,u;if(e=t.prepare,r=t.replace,u=t.wildcard,e)return e;return r?e=h:t.wildcard?e=l:e=f,e;function h(m,p){return p.url=r(p.url,m),p}function l(m,p){return p.url=p.url.replace(u,encodeURIComponent(m)),p}function f(m,p){return p}}function i(t){var e,r,u;return e=t.limiter,r=t.rateLimitBy,u=t.rateLimitWait,e||(e=/^throttle$/i.test(r)?l(u):h(u)),e;function h(f){return function(p){return c.debounce(p,f)}}function l(f){return function(p){return c.throttle(p,f)}}}function a(t){return function(r){var u=d.Deferred();return t(r,h,l),u;function h(f){c.defer(function(){u.resolve(f)})}function l(f){c.defer(function(){u.reject(f)})}}}}(),P=function(){var n;n=window&&window.Bloodhound;function o(s){s=C(s),this.sorter=s.sorter,this.identify=s.identify,this.sufficient=s.sufficient,this.local=s.local,this.remote=s.remote?new b(s.remote):null,this.prefetch=s.prefetch?new S(s.prefetch):null,this.index=new k({identify:this.identify,datumTokenizer:s.datumTokenizer,queryTokenizer:s.queryTokenizer}),s.initialize!==!1&&this.initialize()}return o.noConflict=function(){return window&&(window.Bloodhound=n),o},o.tokenizers=q,c.mixin(o.prototype,{__ttAdapter:function(){var i=this;return this.remote?a:t;function a(e,r,u){return i.search(e,r,u)}function t(e,r){return i.search(e,r)}},_loadPrefetch:function(){var i=this,a,t;return a=d.Deferred(),this.prefetch?(t=this.prefetch.fromCache())?(this.index.bootstrap(t),a.resolve()):this.prefetch.fromNetwork(e):a.resolve(),a.promise();function e(r,u){if(r)return a.reject();i.add(u),i.prefetch.store(i.index.serialize()),a.resolve()}},_initialize:function(){var i=this;return this.clear(),(this.initPromise=this._loadPrefetch()).done(a),this.initPromise;function a(){i.add(i.local)}},initialize:function(i){return!this.initPromise||i?this._initialize():this.initPromise},add:function(i){return this.index.add(i),this},get:function(i){return i=c.isArray(i)?i:[].slice.call(arguments),this.index.get(i)},search:function(i,a,t){var e=this,r;return r=this.sorter(this.index.search(i)),a(this.remote?r.slice():r),this.remote&&r.length<this.sufficient?this.remote.get(i,u):this.remote&&this.remote.cancelLastRequest(),this;function u(h){var l=[];c.each(h,function(f){!c.some(r,function(m){return e.identify(f)===e.identify(m)})&&l.push(f)}),t&&t(l)}},all:function(){return this.index.all()},clear:function(){return this.index.reset(),this},clearPrefetchCache:function(){return this.prefetch&&this.prefetch.clear(),this},clearRemoteCache:function(){return _.resetCache(),this},ttAdapter:function(){return this.__ttAdapter()}}),o}();return P})})(z);var L=z.exports;const D=I(L);try{window.Bloodhound=D}catch{}