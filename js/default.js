function SDObject(options){
	var observers = [];
	var me = this;
	this.delegate = null;
	this.bind = function(fn) {
		return function() {
			var args = new Array();
			if(window.event){
				args.push(window.event);
			}
			if(arguments && arguments.length > 0){
				args.concat(arguments);
			}
			return fn.apply(me, args);
		}
	};

	function notifySetObserversFor(key, value){
        var ubounds = observers.length;
        var indexer = 0;
        for(indexer; indexer < ubounds; indexer++){
            if(observers[indexer].observer.observerKeyValueSet !== undefined){
				if(observers[indexer].key === null || observers[indexer].key === key){
	                observers[indexer].observer.observerKeyValueSet(key, value);
				}
            }
        }
    }

    this.addObserver = function(observer, key){
        observers.push({observer: observer, key: key});
    };

    this.removeObserver = function(observer){
        var ubounds = observers.length;
        var indexer = 0;
        for(indexer; indexer < ubounds - 1; indexer++){
            if(observer === observers[indexer]){
                observers.splice(indexer, 1);
                break;
            }
        }
    };
	this.getUniqueId = function(){
		var today = new Date();
		return Date.UTC(today.getFullYear(), today.getMonth(), today.getDay(), today.getHours(), today.getMinutes(), today.getSeconds(), today.getMilliseconds());
	};
    this.set = function(key, value){
        notifySetObserversFor(key, value);
        me[key] = value;
    };

    this.get = function(key){
        return me[key];
    };
    if(options != null && options.nodeName === undefined){
        for(prop in options){
            this.set(prop, options[prop]);
        }
    }
}
SDObject.capitalize = function(text){
	var words = text.toLowerCase().split('_');
	for(key in words){
		if(words[key].slice){
			words[key] = words[key].slice(0, 1).toUpperCase() + words[key].slice(1, words[key].length);
		}
	}	
	return words.join(' ');
};

SDObject.stringify = function(obj){
	var msg = [];
	for(prop in obj){
		if(obj[prop] !== null && typeof obj[prop] === 'object'){
			msg.push(SDObject.stringify(obj[prop]));
		}else{
			msg.push(prop + '=' + obj[prop]);				
		}
	}
	return msg;
};

function SDArray(ary){}
SDArray.collect = function(ary, delegate){
	var i = 0;
	var ubounds = ary.length;
	var collection = [];
	var val = null;
	var item = null;
	for(i = 0; i < ubounds; i++){
		item = ary.item ? ary.item(i) : ary[i];
		if(delegate(item)){
			collection.push(item);
		}
	}
	return collection;
};
SDArray.pluck = function(ary, delegate){
	var i = ary.length;
	var counter = 0;
	var temp = [];
	while(item = ary[--i]){
		temp.push(delegate(item, counter));
		counter++;
	}
	return temp;
};
SDArray.each = function(ary, delegate){
	var i = 0;
	var ubounds = ary.length;
	var item = null;
	for(i = 0; i < ubounds; i++){
		item = ary.item ? ary.item(i) : ary[i];
		delegate(ary[i], i);
	}
	return ary;
};
SDArray.contains = function(needle, ary){
	var i = ary.length;
	var item = null;
	var is_node_list = (i > 0 && ary.item);
	while(i--){
		item = is_node_list ? ary.item(i) : ary[i];
		if(item === needle){
			return true;
		}
	}
	return false;
};
SDArray.remove = function(item, ary){
	var i = ary.length;
	while(i--){
		if(ary[i] === item){
			ary.splice(i, 1);
			return ary;
		}
	}
	return ary;
};
SDArray.find = function(ary, delegate){
	var i = ary.length;
	while(item = ary[--i]){
		if(delegate(item)){
			return item;
		}
	}
	return null;
};

function SDDom(id){
	return document.getElementById(id);
}
// again, from prototype.js, thanks guys.
SDDom.keys = {
	BACKSPACE: 8
	, TAB: 9
	, RETURN: 13
	, ESC: 27
	, LEFT: 37
	, UP: 38
	, RIGHT: 39
	, DOWN: 40
	, DELETE: 46
	, HOME: 36
	, END: 35
	, PAGEUP: 33
	, PAGEDOWN: 34
	, INSERT: 45
};
SDDom.observers = [];
SDDom.remove = function(elem){
	if(elem){
		if(elem.item){
			if(elem.length > 0){
				var e = elem.item(0);
				var parent = e.parentNode;
				do{
					parent.removeChild(e);
				}while(e = elem.item(elem.length));
			}

		}else{
			if(elem && elem.parentNode){
				elem.parentNode.removeChild(elem);
			}
		}
	}
	return elem;
};
SDDom.show = function(elem){
	elem.style.display = 'block';
	elem.style.visibility = 'visible';
};

SDDom.hide = function(elem){
	elem.style.display = 'none';
	elem.style.visibility = 'hidden';
};
SDDom.toggle = function(elem){
	if(SDDom.isVisible(elem)){
		SDDom.hide(elem);
	}else{
		SDDom.show(elem);
	}
}
SDDom.isVisible = function(elem){
	var display = elem.style.display.length > 0 ? elem.style.display : 'block';
	var visibility = elem.style.visibility.length > 0 ? elem.style.visibility : 'visible';
	return display == 'block' && visibility == 'visible';
}
SDDom.byTag = function(tag, elem){
	var nodes = [];
	if(elem && elem !== document){
		var elems = document.getElementsByTagName(elem.nodeName);
		nodes = SDArray.collect(elems, function(elem){
			return SDArray.collect(elem.childNodes, function(node){
				if(node.nodeName.toLowerCase() === tag){
					return node;
				}
			});
		});
	}else{
		nodes = document.getElementsByTagName(tag);
	}
	return nodes.length > 0 ? nodes : null;
};
SDDom.findAll = function(css_selector, elem){
	if(!elem){
		elem = document;
	}
	return elem.querySelectorAll(css_selector);
};
SDDom.findFirst = function(css_selector, elem){
	if(!elem){
		elem = document;
	}
	return elem.querySelector(css_selector);
};
SDDom.getParent = function(tag, elem){
	var node_name = elem.nodeName.toLowerCase();
	function findParent(tag, elem){
		if(elem === document){
			return null;
		}
		if(elem.parentNode && elem.parentNode.parentNode){
			if(elem.parentNode.nodeName.toLowerCase() == tag){
				return elem.parentNode;
			}else{
				return findParent(tag, elem.parentNode);
			}
		}else{
			return null;
		}
	}
	
	return findParent(tag, elem);
	
};
SDDom.stop = function(e){
	e.cancelBubble = true;
	e.returnValue = false;
};
SDDom.removeClass = function(class_name, elem){
	var names = elem.className.split(' ');
	var new_names = SDArray.collect(names, function(name){return name.length > 0 && name !== class_name;});
	elem.className = new_names.join(' ');
	return elem;
};
SDDom.addClass = function(class_name, elem){
	var names = elem.className.split(' ');
	var i = 0;
	var ubounds = names.length;
	var new_names = [];
	for(i = 0; i < ubounds; i++){
		if(names[i] !== class_name && names[i].length > 0){
			new_names.push(names[i]);
		}
	}
	new_names.push(class_name);
	elem.className = new_names.join(' ');
	return elem;
};
SDDom.hasClass = function(class_name, elem){
	var names = elem.className.split(' ');
	var i = names.length;
	while(name = names[--i]){
		if(name === class_name){
			return true;
		}
	}
	return false;
};
SDDom.addEventListener = function(elem, name, fn){
	// IE doesn't fire an onload event when a script element loads, it implements onreadystatechange like XMLHTTpRequest.
	// So I'm coding for that scenario here.
	SDDom.observers.push([elem, name, fn]);
	if(elem.nodeName && name === 'load' && elem.nodeName.toLowerCase() === 'script' && elem.attachEvent){
		elem.onreadystatechange = function(){
			if(this.readyState === 'loaded' || this.readyState === 'complete'){
				fn();
			}
		};
	}
	if (elem.addEventListener){
		elem.addEventListener(name, fn, false);
	}else{
		elem.attachEvent('on' + name, fn);
	}
};
SDDom.removeAllEventListeners = function(elem){
	var i = SDDom.observers.length;
	while(observer = SDDom.observers[--i]){
		if(observer[0] === elem){
			SDDom.removeEventListener(observer[0], observer[1], observer[2]);
		}
	}
};
SDDom.removeEventListener = function(elem, name, fn){
	if(elem.removeEventListener){
		elem.removeEventListener(name, fn, false);
	}else{
		elem.detachEvent('on' + name, fn);
	}	
};

// Remove all event listeners on unload. TODO: Test this for order issues if someone is listening for the unload event too.
SDDom.addEventListener(window, 'unload', function(e){
	var i = SDDom.observers.length;
	while(observer = SDDom.observers[--i]){
		SDDom.removeEventListener(observer[0], observer[1], observer[2]);
	}
});

SDDom.getHeight = function(elem){
	return elem.clientHeight;
};
SDDom.getWidth = function(elem){
	return elem.clientWidth;
};
// From prototype.js
//http://prototypejs.org/
SDDom.getPosition = function(elem, from){
	var top = 0, left = 0;
	do {
		top += elem.offsetTop  || 0;
		left += elem.offsetLeft || 0;
		elem = elem.offsetParent;
		if (elem) {
			if (elem.tagName.toLowerCase() == 'body') break;
			var p = elem.style.position;
			if (p !== 'static') break;
		}
	} while (elem);    
	return {x:top, y:left};
};
SDDom.setStyles = function(styles, elem){
	if(elem){
		if(!elem.style){
			elem.style = [];
		}
		for(style in styles){
			elem.style[style] = styles[style];
		}		
	}
	return elem;
};
SDDom.create = function(tag, properties){
	var elem = document.createElement(tag);
	for(prop in properties){
		elem[prop] = properties[prop];
	}
	return elem;
};
SDDom.insertBefore = function(elem, parent){
	parent.insertBefore(elem, parent.firstChild);
	return elem;
};
SDDom.insertAfter = function(elem, parent){
	if(parent.parentNode){
		parent.parentNode.appendChild(elem)
	}else{
		document.appendChild(elem);
	}
	return elem;
};
SDDom.append = function(parent, elem){
	parent.appendChild(elem);
	return elem;
};
SDDom.toQueryString = function(form){
	var qs = [];
	var fields = SDDom.findAll('input,select,textarea', form);
	fields = SDArray.collect(fields, function(field){
		return !(!field.name || field.disabled || field.type == 'submit' || field.type == 'reset' || field.tpe == 'file');
	});
	var values = SDArray.pluck(fields, function(field){
		if(field.tagName.toLowerCase() === 'select'){
			return field.name + '=' + field.selectedIndex > 0 ? field.options[field.selectedIndex-1].value : null;
		}else if(field.type && ((field.type === 'radio' || field.type === 'checkbox') && field.checked)){			
			return field.name + '=' + field.value;
		}else{
			return field.name + '=' + field.value;
		}
	});
	return values.join('&');
};
SDDom.pageX = function(e){
	return e.pageX;
};
SDDom.pageY = function(e){
	return e.pageY;
};
SDObject.extend = function(dest, src){
	for(prop in src){
		dest[prop] = src[prop];
	}
	return dest;
};
function SDAjax(options){
	SDObject.apply(this, [options]);
	this.options = {
		method: 'post'
		, asynchronous: true
		, contentType: 'application/x-www-form-urlencoded'
		, encoding: 'UTF-8'
		, parameters: ''
		, evalJSON: true
		, evalJS: true
	};
	var events = ['UNSENT', 'OPENED', 'HEADERS_RECEIVED', 'LOADING', 'DONE'];
	SDObject.extend(this.options, options || {});
	if(!request){
		var request = createTransport();
	}
	function createTransport(){
		if(XMLHttpRequest)return new XMLHttpRequest();
		if(ActiveXObject && ActiveXObject('Msxml2.XMLHTTP')) return new ActiveXObject('Msxml2.XMLHTTP');
		if(ActiveXObject && ActiveXObject('Microsoft.XMLHTTP')) return new ActiveXObject('Microsoft.XMLHTTP');
		return null;
	}
	function didStateChange(){
		var state = events[request.readyState];
		if(this.options[state]){
			this.options[state][1].apply(this.options[state][0], [request]);
		}
		if(state === 'DONE'){
			request = null;
		}
	}
	function getHeaders(method, params){
		var header = {"X-Requested-With":"XMLHttpRequest", "Accept":"text/javascript, text/html, application/xml, text/xml, */*"};
		if(method === 'post'){
			header["Content-type"] = 'application/x-www-form-urlencoded; charset=UTF-8';
		}
		return header;
	}
	this.send = function(url){
		if(request == null) return;
		
		if(this.options.parameters){
			if(this.options.method == 'get'){
				url += (url.test(/\?/) ? '&' : '?') + this.options.parameters;
			}else if(/Konqueror|Safari|KHTML/.test(navigator.userAgent)){
				this.options.parameters += '&_=';
			}
		}
		if(!SDArray.contains(this.options.method, ['get', 'post'])){
			this.options.parameters += '&_method=delete';
			this.options.method = 'post';
		}
		request.open(this.options.method.toUpperCase(), url, this.options.asynchronous);		
		SDDom.addEventListener(request, 'readystatechange', this.bind(didStateChange));		
		var headers = getHeaders(this.options.method, this.options.parameters);
		for(name in headers){
			request.setRequestHeader(name, headers[name]);
		}
		request.send(this.options.method === 'post' ? this.options.parameters : null);
	};
	
};
function UIResponder(){
	SDObject.apply(this, arguments);
	var is_first_responder = false;
	this.nextResponder = function(){
		
	};
	this.isFirstResponder = function(){
		return is_first_responder;
	};
	this.canBecomeFirstResponder = function(){
		
	};
	this.becomeFirstResponder = function(){
		
	};
	this.canResignFirstResponder = function(){
		
	};
	this.resignFirstResponder = function(){
		
	};
	
	this.touchesBeganWithEvent = function(e){
		
	};
	this.touchesMovedWithEvent = function(e){
		
	};
	this.toucesEndedWithEvent = function(e){
		
	};
	this.touchesCancelledWithEvent = function(e){
		
	};
	this.motionBeganWithEvent = function(e){
		
	};
	this.motionEndedWithEvent = function(e){
		
	};
	this.motionCancelledWithEvent = function(e){
		
	};
	
	this.canPerformActionWithSender = function(action, sender){
		
	};
	this.undoManager = null;	
}

function UIWindow(){
	SDObject.apply(this, arguments);
	var keyWindow = false;
	var windowLevel = null;
	this.keyWindow = function(){
		return keyWindow;
	}
	this.makeKeyAndVisible = function(){
		
	};
	this.becomeKeyWindw = function(){
		
	};
	this.makeKeyWindow = function(){
		
	};
	this.resignKeyWindow = function(){
		
	};
	this.sendEvents = function(){
		
	};
}

function UIView(id){
	UIResponder.apply(this, arguments);
	this.container = null;
	this.activeView = null;
	if(id){
		this.container = SDDom(id);
	}
	if(this.container && !this.container.id){
		this.container.id = this.getUniqueId();
	}
	this.id = id;
	if(this.container && this.onClick){
		SDDom.addEventListener(this.container, 'click', this.bind(this.onClick));		
	}
	this.setHtml = function(html){
		this.container.innerHTML = html;
	};
	this.isVisible = function(){
		var display = this.container.style.display.length > 0 ? this.container.style.display : 'block';
		var visibility = this.container.style.visibility.length > 0 ? this.container.style.visibility : 'visible';
		return display == 'block' || visibility == 'visible';
	};
	this.show = function(){
		SDDom.show(this.container);
	};
	this.hide = function(){
		SDDom.hide(this.container);
	};
	this.toggle = function(){
		SDDom.toggle(this.container);
	};
	this.open = function(url, options){
		this.activeView = window.open(url, (options.name ? options.name : this.id + '_view_' + (new Date()).UTC)
			, (options.options ? options.options : 'dependent=yes,directories=no,height=600,location=no,menubar=no,resizable=yes,outerHeight=600,outerWidth=600,scrollbars=yes,status=no,titlebar=no,toolbar=no, width=600'));
		
	};
	this.viewDidClose = function(e){
		this.activeView = null;
	};
	this.eventViewDidClose = this.bind(this.viewDidClose);	
}

function UIController(views){
	SDObject.apply(this, arguments);
	this.views = views;
}

UIView.Panel = function(id){
	UIView.apply(this, arguments);
}

UIView.Button = function(id){
	UIView.apply(this, arguments);
}

UIView.ContactPanel = function(id){
	UIView.Panel.apply(this, arguments);
	this.onClick = function(e){
		alert(e);
		SDDom.stopPropagation(e);
	}
}

UIView.ContactLink = function(id){
	this.onClick = function(e){
		SDDom.stopPropagation(e);
	};
	UIView.Button.apply(this, arguments);
}

UIView.AdminMenu = function(id){
	this.onClick = function(e){
		if(e.target.id === 'new_post_link'){
			SDDom.stopPropagation(e);
			this.open(e.target, 'admin_menu');
		}
	};
	UIView.apply(this, arguments);
}
SDDom.addEventListener(window, 'load', function(){
	var scripts = SDDom.byTag('script');
	SDObject.rootUrl = SDArray.collect(scripts, function(script){return script.src.indexOf('default') > -1;})[0].src.replace('js/default.js', '');
	if(window.location.href.indexOf('index.php') > -1){
		SDObject.rootUrl += 'index.php/';
	}
});
