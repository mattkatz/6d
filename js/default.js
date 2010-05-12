function NSObject(options){
	var observers = [];
	var me = this;
	this.delegate = null;
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

function UIResponder(){
	NSObject.apply(this, arguments);
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
	NSObject.apply(this, arguments);
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
		this.container = $(id);			
	}
	if(!this.container.id){
		this.container.id = this.getUniqueId();
	}
	this.id = this.container.id;
	if(this.container && this.onClick){
		this.container.addEvent('click', this.onClick.bind(this));
	}
	this.setHtml = function(html){
		this.container.set('html', html);
	};
	this.isVisible = function(){
		visible = false;
		if(this.container){
			visible = (this.container.getStyle('display') === 'none' && this.container.getStyle('visibility') === 'visible');
		}
		return visible;	
	};
	this.show = function(){
		this.container.show();
	};
	this.hide = function(){
		this.container.hide();
	};
	this.toggle = function(){
		if(this.isVisible()){
			this.show();
		}else{
			this.hide();
		}
	};
	this.open = function(url, options){
		this.activeView = window.open(url, (options.name ? options.name : this.id + '_view_' + (new Date()).UTC)
			, (options.options ? options.options : 'dependent=yes,directories=no,height=600,location=no,menubar=no,resizable=yes,outerHeight=600,outerWidth=600,scrollbars=yes,status=no,titlebar=no,toolbar=no, width=600'));
		
	};
	this.viewDidClose = function(e){
		this.activeView = null;
	};
	this.eventViewDidClose = this.viewDidClose.bind(this);
	
}

function UIController(views){
	NSObject.apply(this, arguments);
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
		e.stop();
	}
}

UIView.ContactLink = function(id){
	this.onClick = function(e){
		e.stop();
	};
	UIView.Button.apply(this, arguments);
}

UIView.AdminMenu = function(id){
	this.onClick = function(e){
		if(e.target.id === 'new_post_link'){
			e.stop();
			this.open(e.target, 'admin_menu');
		}
	};
	UIView.apply(this, arguments);
}
window.addEvent('domready', function(){
	NSObject.rootUrl = $$('script[src*=default]')[0].src.replace('js/default.js', '');
	if(window.location.href.indexOf('index.php') > -1){
		NSObject.rootUrl += 'index.php/';
	}
});
