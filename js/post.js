
UIView.TabView = function(id){
	this.onClick = function(e){
		e.stop();
	};
	
	UIView.apply(this, arguments);
}
UIView.PostMenu = function(id){
	this.onClick = function(e){
		if(e.target.id === 'address'){
			SDDom.stop(e);
			this.open(e.target, {name: 'address_view'});
		}else if(e.target.id === 'photos_link'){
			
		}else{
			SDDom.stop(e);
		}
	};
	UIView.apply(this, arguments);
};
UIView.TextArea = function(id){
	UIView.apply(this, arguments);
	this.onResize = function(e){
		this.resize(SDDom.getHeight(e.target) - SDDom.getPosition(this.container).y - 80);
	};
	
	this.resize = function(height){
		SDDom.setStyles({height: height + 'px'}, this.container);
		SDDom.setStyles({height: (height - 30 ) + 'px'}, SDDom.byTag('iframe', this.container));
	};
	this.keypress = function(e){
		if((e.metaKey || e.ctrlKey) && String.fromCharCode(e.keyCode) === 's'){
			SDDom.stop(e);
			if(this.delegate && this.delegate.doSave){
				this.delegate.doSave.apply(this.delegate, [this, e]);
			}
		}
	};
	this.resize(SDDom.getHeight(window) - SDDom.getPosition(this.container).y - 80);
	this.eventResize = this.bind(this.onResize);
	SDDom.addEventListener(window, 'resize', this.eventResize);
	SDDom.addEventListener(this.container, 'keypress', this.bind(this.keypress));
};

UIView.Overlay = function(options){
	UIView.apply(this, [this.id, options]);
	var today = new Date();
	this.id = 'overlay_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDay());
	this.container = SDDom.create('div');
	this.container.id = '__overlay';
	SDDom.setStyles({"top":"0", "left":"0", "bottom":"0", "right":"0", "display": "none", "width": "100%", "height":"100%", "position":"absolute", "background": "#000", "opacity":".5"}, this.container);
	SDDom.setStyles({zIndex: 1}, this.container);
	SDDom.insertBefore(this.container, SDDom.byTag('body')[0]);
	
	this.toggle = function(){
		SDDom.toggle(this.container);
	}
};

UIView.Modal = function(id, options){
	var today = new Date();	
	this.div = document.createElement('div');
	this.div.id = 'content_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDay());
	if(!options.handle){
		throw new Exception("I need the DOM id of the element who's click event I'll handle to open the modal window.");
	}
	this.handle = null;
	try{
		this.handle = SDDom(options.handle);		
	}catch(e){
		throw new Exception("An exception occurred when I tried to get the DOM element by the id you gave me in options.handle: " + e);
	}
	this.overlay = new UIView.Overlay(null);
	this.onHandleClick = function(e){
		this.overlay.toggle();
		SDDom.setStyles({zIndex: 2, position: 'absolute', top: '50%', left: '50%', marginLeft: -1*SDDom.getWidth(this.container) / 2, marginTop: -1*SDDom.getHeight(this.container)/2}, this.container);
		this.toggle();
		if(this.didClickHandle){
			this.didClickHandle(e);
		}
		if(this.delegate && this.delegate.didClickHandle){
			this.delegate.didClickHandle.apply(this.delegate, [e]);
		}
		SDDom.stop(e);
	};
	this.onCloseClick = function(e){
		this.hide();
		this.overlay.toggle();
		if(this.didClickClose){
			this.didClickClose(e);
		}
		if(this.delegate && this.delegate.didClickCancel){
			this.delegate.didClickCancel.apply(this.delegate, [e]);
		}
	};
	this.onOkClick = function(e){
		this.hide();
		this.overlay.toggle();
		if(this.didClickOk){
			this.didClickOk.apply(this, e);
		}
		if(this.delegate && this.delegate.didClickOk){
			this.delegate.didClickOk.apply(this.delegate, [e]);
		}
	};
	this.onViewClick = function(e){		
		if(this.didClickView){
			this.didClickView(e);
		}
		if(this.delegate && this.delegate.didClickView){
			this.delegate.didClickView.apply(this.delegate, [e]);
		}
	};
	UIView.apply(this, [id, options]);
	this.setHtml = function(html){
		this.div.innerHTML = html;
	};
	SDDom.append(this.container, this.div);
	SDDom.addEventListener(this.handle, 'click', this.bind(this.onHandleClick));
	this.closeHandle = SDDom.create('button');
	var properties = {innerHTML: 'Clear All', value: 'Cancel', id: 'close_button_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDay())};
	for(prop in properties){
		this.closeHandle[prop] = properties[prop];
	}
	this.okHandle = SDDom.create('button');
	properties = {innerHTML: 'Ok', value: 'Ok', id: 'ok_button_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDay())};
	for(prop in properties){
		this.okHandle[prop] = properties[prop];
	}
	SDDom.append(this.container, this.closeHandle);
	SDDom.append(this.container, this.okHandle);

	SDDom.addEventListener(this.closeHandle, 'click', this.bind(this.onCloseClick));
	SDDom.addEventListener(this.okHandle, 'click', this.bind(this.onOkClick));
	SDDom.addEventListener(this.div, 'click', this.bind(this.onViewClick));
};

UIView.Modal.AddressBook = function(id, options){
	UIView.Modal.apply(this, [id, options]);	
	this.selectedGroup = null;
	this.selectedPerson = null;
	this.people = [];
	this.groups = [];
	this.didClickHandle = function(e){
		SDDom.setStyles({"width":"400px","height":"300px", "background":"#fff", "marginLeft":"-200px"}, this.container);	
	};
	this.clearPeople = function(){
		SDArray.each(SDDom.findAll('li', this.peopleContainer()), function(li){
			SDDom.remove(li);
		});
	};
	this.addPeople = function(people, selectedPeople){
		var peopleContainer = SDDom.findFirst('ul', this.peopleContainer());
		var first = SDDom.findFirst('input', this.groupContainer());
		var i = 0;
		var person = null;
		for(i = 0; i < people.length; i++){
			person = people[i];
			var li = SDDom.create('li');
			li.setAttribute('rel', person.id);
			li.setAttribute('class', person.is_owner ? 'owner' : '');
			var checkbox = SDDom.create('input');
			var a = SDDom.create('a');
			a.setAttribute('href', 'javascript:void(0);');
			checkbox.type = 'checkbox';
			checkbox.id = 'person_checkbox_' + person.id;
			checkbox.name = 'people';
			checkbox.value = encodeURIComponent(JSON.stringify(person));
			var span = SDDom.create('span');
			span.innerHTML = decodeURIComponent(person.name);
			span.setAttribute('rel', JSON.stringify(person));			
			if(first && first.checked){
				checkbox.checked = false;
				checkbox.disabled = true;
			}else{
				if(SDArray.collect(selectedPeople, function(p){return p.id === person.id;}).length > 0){
					checkbox.checked = true;
				}
			}
			SDDom.append(a, span);
			SDDom.append(li, checkbox);
			SDDom.append(li, a);
			SDDom.append(peopleContainer, li);
		}
	};
	this.groupContainer = function(){
		return SDDom('groups');
	};
	this.peopleContainer = function(){
		return SDDom('people');
	};
	this.disableAllCheckboxes = function(first){
		SDArray.each(SDDom.findAll('input', this.container), function(checkbox){
			if(checkbox !== first){
				checkbox.checked = false;
				checkbox.disabled = true;
			}
		});
	}
	
	this.enableAllCheckboxes = function(first){
		SDArray.each(SDDom.findAll('input', this.container), function(checkbox){
			if(checkbox !== first){
				checkbox.disabled = false;
			}
		});
	}
	
	this.didClickView = function(e){
		if(e.target && e.target.getAttribute){
			var section = SDDom.getParent('section', e.target);
			var nodeName = e.target.nodeName.toLowerCase();
			var target = (nodeName !== 'input' ? SDDom.getParent('li', e.target) : e.target);
			
			var rel = (nodeName === 'input' ? target.value : target.getAttribute('rel'));
			if(nodeName === 'input'){
				if(target === SDDom.findFirst('input', this.div)){
					if(this.delegate.allContactsWasSelected){
						if(target.checked){
							this.delegate.allContactsWasSelected.apply(this.delegate, [target]);
						}else{
							this.delegate.allContactsWasDeselected.apply(this.delegate, [target]);
						}
					}
				}				
			}
			if(section === SDDom.findFirst('section', this.div)){
				if(nodeName !== 'input' && this.delegate && this.delegate.didClickGroups){
					this.delegate.didClickGroups.apply(this.delegate, [e, rel]);
				}

				if(nodeName === 'input' && this.delegate && this.delegate.aGroupWasChecked){
					this.delegate.aGroupWasChecked.apply(this.delegate, [target]);
				}

				if(this.selectedGroup){
					SDDom.removeClass('selected', this.selectedGroup);
				}
				if(target !== null){
					this.selectedGroup = SDDom.getParent('li', target);
				}
				if(this.selectedGroup){
					SDDom.addClass('selected', this.selectedGroup);
				}
			}else if(section === SDDom.findAll('section', this.div)[1]){
				if(this.delegate && this.delegate.didClicPeople){
					this.delegate.didClicPeople.apply(this.delegate, [e, rel]);
				}
				if(this.delegate && this.delegate.aPersonWasChecked){
					this.delegate.aPersonWasChecked.apply(this.delegate, [e, target]);
				}

				if(this.selectedPerson){
					SDDom.removeClass('selected', this.selectedPerson);
				}
				if(this.selectedPerson){
					this.selectedPerson = SDDom.getParent('li', target);
				}
			}
		}		
	};
	
};

UIController.AddressBook = function(view){
	UIController.apply(this, arguments);
	this.view = view;
	this.view.delegate = this;
	
	this.allContactsWasDeselected = function(elem){
		this.view.enableAllCheckboxes(elem);
		this.view.groups = [];
		this.view.people = [];
	};
	this.didClickCancel = function(e){
		this.view.groups = [];
		this.view.people = [];
		if(this.delegate && this.delegate.didClickCancel){
			this.delegate.didClickCancel.apply(this.delegate, [e]);
		}
	};
	this.allContactsWasSelected = function(elem){
		var i = this.view.groups.length;
		var j = this.view.people.length;
		if(this.delegate && this.delegate.didRemoveGroup){
			while(group = this.view.groups[i--]){
				this.delegate.didRemoveGroup.apply(this.delegate, [group]);
			}
		}
		if(this.delegate && this.delegate.didRemovePerson){
			while(person = this.view.people[j--]){
				this.delegate.didRemovePerson.apply(this.delegate, [person]);
			}
		}		
		
		this.view.disableAllCheckboxes(elem);
		this.view.groups = [];
		this.view.people = [];
		this.view.groups.push(elem.getAttribute('value'));
	};
	this.onAddressbookAjaxDONE = function(request){		
		this.view.setHtml(request.responseText);		
		this.view.selectedGroup = SDDom.findFirst('li.selected');
		var i = 0;
		SDDom.remove(SDDom.findFirst('.owner'));
		// Remove the friend request group.
		SDArray.each(SDDom.findAll('li', this.view.groupContainer()), function(li){
			if(li.getAttribute('rel') === 'Friend Requests'){
				SDDom.remove(li);
			}
		});
		
		for(i = 0; i < this.view.groups.length; i++){
			checkbox = SDDom('group_checkbox_' + this.view.groups[i]);
			if(checkbox){
				checkbox.checked = true;
			}
		}
		
		for(i = 0; i < this.view.people.length; i++){
			checkbox = SDDom('person_checkbox_' + this.view.people[i].id);
			if(checkbox){
				checkbox.checked = true;
			}
		}
	};
	this.didClickHandle = function(e){
		var url = SDObject.rootUrl + 'addressbook.phtml';
		var ajax = new SDAjax({method: 'get', DONE: [this, this.onAddressbookAjaxDONE]});
		ajax.send(url);
	};
	this.onGroupAjaxDONE = function(request){
		var response = JSON.parse(request.responseText);
		this.view.clearPeople();
		if(response.people && response.people.length > 0){
			this.view.addPeople(response.people, this.view.people);					
		}
		SDDom.remove(SDDom.findFirst('li.owner'));
	}
	this.didClickGroups = function(e, text){
		var url = 'people/' + text + '.json';
		var ajax = new SDAjax({method: 'get', DONE: [this, this.onGroupAjaxDONE]});
		ajax.send(url);
	};
	
	this.aGroupWasChecked = function(elem){
		var group = elem.getAttribute('value');
		if(elem.checked){
			if(!SDArray.contains(group, this.view.groups)){
				this.view.groups.push(group);
			}
			if(this.delegate && this.delegate.didAddGroup){
				this.delegate.didAddGroup.apply(this.delegate, [group]);
			}
		}else{
			
			if(this.delegate && this.delegate.didRemoveGroup){
				this.delegate.didRemoveGroup.apply(this.delegate, [group]);
			}
			SDArray.remove(group, this.view.groups);			
		}
	};
	
	this.aPersonWasChecked = function(e, elem){
		if(elem.nodeName.toLowerCase() === 'input'){
			var value = decodeURIComponent(elem.value).replace(/\+/g, '');
			var person = JSON.parse(value);
			if(elem.checked){
				if(SDArray.collect(this.view.people, function(p){return p.id === person.id;}).length === 0){
					this.view.people.push(person);
				}
				if(this.delegate && this.delegate.didAddPerson){
					this.delegate.didAddPerson.apply(this.delegate, [person]);
				}
			}else{
				this.view.people = SDArray.collect(this.view.people, function(p){return p.id !== person.id;});
				if(this.delegate && this.delegate.didRemovePerson){
					this.delegate.didRemovePerson.apply(this.delegate, [person]);
				}
			}
		}else{
			SDDom.stop(e);
		}
	};
	
};

UIController.Post = function(){
	UIController.apply(this, arguments);
	this.form = SDDom('post_form');
	this.doSave = function(e){
		this.form.submit();
	};
	this.didAddGroup = function(group){
		var ul = SDDom.findFirst('ul', SDDom('send_to_list'));
		var li = SDDom.create('li');
		li.innerHTML = '<span>' + decodeURIComponent(group).replace('+', ' ') + '</span>';
		var input = SDDom.create('input');
		input.type = 'hidden';
		input.name = 'groups[]';
		input.id = 'groups_' + this.getUniqueId();
		input.value = group;
		SDDom.append(li, input);
		SDDom.append(ul, li);
	};
	this.didRemoveGroup = function(group){
		var fields = SDDom.findAll('input[type=hidden]', SDDom('send_to_list'));
		var i = fields.length;
		while(field = fields.item(--i)){
			if(field.value === group){
				break;
			}
		}
		
		if(field){
			SDDom.remove(SDDom.getParent('li', field));
		}
	};
	this.didAddPerson = function(person){
		var ul = SDDom.findFirst('ul', SDDom('send_to_list'));
		var li = li = SDDom.create('li');
		li.id = 'li_person_' + person.id;
		li.innerHTML = '<span>' + decodeURIComponent(person.name).replace('+', ' ') + '</span>';
		var input = SDDom.create('input');
		input.type = 'hidden';
		input.name = 'people[]';
		input.id = 'person_checkbox_' + person.id;
		input.value = person.id;
		SDDom.append(li, input);
		SDDom.append(ul, li);
	};
	this.didRemovePerson = function(person){
		SDDom.remove(SDDom('li_person_' + person.id));
	};
	this.didClickCancel = function(e){
		var lis = SDDom.findAll('ul li', SDDom('send_to_list'));
		var i = lis.length;
		while(i > 0){
			SDDom.remove(lis.item(--i));
		}
	};
	this.didClickMakeHome = function(e){
		var link = SDDom.getParent('a', e.target);		
		SDDom('make_home_page').checked = !SDDom('make_home_page').checked;
		if(SDDom('make_home_page').checked){
			SDDom.removeClass('home', link);
			SDDom.addClass('not_home', link);
			link.title = 'Make this a regular post.';
			SDDom.findFirst('span', link).innerHTML = 'Make Post';
		}else{
			SDDom.removeClass('not_home', link);
			SDDom.addClass('home', link);
			link.title = 'Make this post the home page.';
			SDDom.findFirst('span', link).innerHTML = 'Make Home';
		}
	};
	this.didClickReblog = function(e){
		SDDom('is_published').checked = true;
	};
	this.didClickMakePrivate = function(e){
		var link = SDDom.getParent('a', e.target);
		SDDom('is_published').checked = !SDDom('is_published').checked;
		if(SDDom('is_published').checked){
			SDDom.removeClass('private', link);
			SDDom.addClass('public', link);
			link.title = 'Make this post private. It will not show up on your public site.';
			SDDom.findFirst('span', link).innerHTML = 'Make Private';
		}else{
			SDDom.removeClass('public', link);
			SDDom.addClass('private', link);
			link.title = 'Make this post public. This will show up on your public site.';
			SDDom.findFirst('span', link).innerHTML = 'Make Public';
		}	
	};
	
	this.didChangePhoto = function(e){
		if(SDDom('photo_names[' + this.value + ']')){
			alert("you've already added that photo.");
			SDDom.stop(e);
		}else{
			$('media_form').submit();
		}	
	};
	
	this.didUploadPhoto = function(photo_name, file_name, photo_path, width){
		$('photo').set('value', null);
		var dd = new Element('dd', {text: photo_name});
		var items = $$('#photos dd');
		var count = 0;
		if(items && items.length > 0){
			count = items.length;
		}
		var hidden_field = new Element('input', {type: 'hidden', value: photo_name + '=' + file_name, id: 'photo_names[' + photo_name + ']', name: 'photo_names[]'});
		$$('#photos').adopt(dd);
		$$('#post_form fieldset')[0].adopt(hidden_field);
	};
	SDDom.addEventListener(SDDom('make_home_link'), 'click', this.bind(this.didClickMakeHome));
	SDDom.addEventListener(SDDom('publish_link'), 'click', this.bind(this.didClickMakePrivate));
	
	var reblog = SDDom('reblog');
	if(reblog){
		SDDom.addEventListener(reblog, 'click', this.bind(this.didClickReblog));
	}
}

var editor = null;
var postController;
SDDom.addEventListener(window, 'load', function(e){
	var postMenu = new UIView.PostMenu('post_menu');
	var textarea = new UIView.TextArea('body');
	var addressBookController = new UIController.AddressBook(new UIView.Modal.AddressBook('addressbook_modal', {handle: 'address'}));
	postController = new UIController.Post();
	textarea.delegate = postController;
	addressBookController.delegate = postController;
});

function photoWasUploaded(photo_name, file_name, photo_path, width){
	postController.didUploadPhoto(photo_name, file_name, photo_path, width);
}
function photoDidChange(e){
	if($('photo_names[' + this.value + ']')){
		alert("you've already added that photo.");
		e.stop();
	}else{
		$('media_form').submit();
	}
}

