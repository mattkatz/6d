
UIView.TabView = function(id){
	this.onClick = function(e){
		e.stop();
	};
	
	UIView.apply(this, arguments);
}
UIView.PostMenu = function(id){
	this.onClick = function(e){
		if(e.target.id === 'address'){
			e.stop();
			this.open(e.target, {name: 'address_view'});
		}else if(e.target.id === 'photos_link'){
			
		}else{
			e.stop();
		}
	};
	UIView.apply(this, arguments);
};
UIView.TextArea = function(id){
	UIView.apply(this, arguments);
	this.onResize = function(e){
		this.resize(e.target.getHeight() - this.container.getPosition().y - 80);
	};
	
	this.resize = function(height){
		this.container.setStyles({height: height + 'px'});
		this.container.getElement('iframe').setStyles({height: (height - 30 ) + 'px'});
	};
	this.resize(window.getHeight() - this.container.getPosition().y - 80);
	this.eventResize = this.onResize.bind(this);
	window.addListener('resize', this.eventResize);
};

UIView.Panel = function(id, options){
	var options_hide_link = $(options.options_hide_link);
	options_hide_link.className = 'window_button';
	this.onHideClick = function(e){
		options_hide_link.set('text', options_hide_link.get('text') === 'Hide' ? 'Show' : 'Hide');
		var mySlide = new Fx.Slide(this.container, {duration: 'short'}).toggle();
	};
	UIView.apply(this, [id, options]);
	options_hide_link.addEvent('click', this.onHideClick.bind(this));
};

UIView.Overlay = function(options){
	var today = new Date();
	this.id = 'overlay_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDay());
	$$('body')[0].grab(new Element('div', {id: this.id, style: 'top:0;left:0;bottom:0;right:0;display: none;width: 100%;height:100%;position:absolute;background: #000;opacity:.5;'}), 'top');
	UIView.apply(this, [this.id, options]);
};

UIView.Modal = function(id, options){
	var today = new Date();	
	this.div = new Element('div', {id: 'content_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDay())});
	if(!options.handle){
		throw new Exception("I need the DOM id of the element who's click event I'll handle to open the modal window.");
	}
	this.handle = null;
	try{
		this.handle = $(options.handle);		
	}catch(e){
		throw new Exception("An exception occurred when I tried to get the DOM element by the id you gave me in options.handle: " + e);
	}
	this.overlay = new UIView.Overlay(null);
	this.onHandleClick = function(e){
		this.container.setStyles({zIndex: 2, position: 'absolute', top: '50%', left: '50%', marginLeft: -1*this.container.getDimensions().x / 2, marginTop: -1*this.container.getDimensions().y/2});
		this.overlay.container.setStyles({zIndex: 1});
		this.overlay.toggle();
		this.toggle();
		if(this.delegate && this.delegate.didClickHandle){
			this.delegate.didClickHandle(e);
		}
		e.stop();
	};
	this.onCloseClick = function(e){
		this.hide();
		this.overlay.toggle();
		if(this.delegate && this.delegate.didClickCancel){
			this.delegate.didClickCancel(e);
		}
	};
	this.onOkClick = function(e){
		this.hide();
		this.overlay.toggle();
		if(this.delegate && this.delegate.didClickOk){
			this.delegate.didClickOk(e);
		}
	};
	this.onViewClick = function(e){
		if(this.delegate && this.delegate.didClickView){
			this.delegate.didClickView(e);
		}
	};
	UIView.apply(this, [id, options]);
	this.setHtml = function(html){
		this.div.set('html', html);
	};
	this.container.grab(this.div);
	this.handle.addEvent('click', this.onHandleClick.bind(this));
	this.closeHandle = new Element('button', {html: 'Clear All', value: 'Cancel', id: 'close_button_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDay())});
	this.okHandle = new Element('button', {html: 'Ok', value: 'Ok', id: 'ok_button_' + Date.UTC(today.getFullYear(), today.getMonth(), today.getDay())});
	this.container.grab(this.closeHandle);
	this.container.grab(this.okHandle);

	this.closeHandle.addEvent('click', function(e){
		this.onCloseClick(e);
	}.bind(this));

	this.okHandle.addEvent('click', function(e){
		this.onOkClick(e);
	}.bind(this));

	this.div.addEvent('click', function(e){
		this.onViewClick(e);
	}.bind(this));
};

UIView.Modal.AddressBook = function(id, options){
	UIView.Modal.apply(this, [id, options]);
	this.selectedGroup = null;
	this.selectedPerson = null;
	this.people = [];
	this.groups = [];
	var parent = {onViewClick: this.onViewClick.bind(this)
		, onHandleClick: this.onHandleClick.bind(this)
		, onOkClick: this.onOkClick.bind(this)
		, onCloseClick: this.onCloseClick.bind(this)
	};

	this.clearPeople = function(){
		this.peopleContainer().getChildren('li').each(function(li){
			li.destroy();
		});
	};
	this.addPeople = function(people, selectedPeople){
		var peopleContainer = this.peopleContainer();
		var first = this.div.getElement('input');
		var i = 0;
		var person = null;
		for(i = 0; i < people.length; i++){
			person = people[i];
			var li = new Element('li', null);
			var checkbox = new Element('input', {type: 'checkbox', id: 'person_' + person.id, name: 'people', value: JSON.encode(person)});
			var span = new Element('span', {text: decodeURIComponent(person.name), rel: JSON.encode(person)});
			if(first && first.get('checked')){
				checkbox.set('checked', false);
				checkbox.set('disabled', true);
			}else{
				if(selectedPeople.filter(function(p){return p.id === person.id;}, this).length > 0){
					checkbox.set('checked', true);
				}
			}
			li.grab(checkbox);
			li.grab(span);
			peopleContainer.grab(li);
		}
	};
	this.onHandleClick = function(e){
		parent.onHandleClick(e);
	};
	this.groupContainer = function(){
		return this.div.getElement('section ul');
	};
	this.peopleContainer = function(){
		var pc = this.div.getElements('section ul');
		if(pc && pc.length > 0){
			pc = pc[1];
		}else{
			pc = new Element('ul', {id: 'people_' + Date.UTC(new Date())});
			this.div.getElements('section')[1].grab(pc);
		}
		return pc;
	};
	this.onCloseClick = function(e){
		parent.onCloseClick(e);
	};
	this.onOkClick = function(e){
		parent.onOkClick(e);
	};
	this.disableAllCheckboxes = function(first){
		this.container.getElements('input').each(function(checkbox){
			if(checkbox !== first){
				checkbox.set('checked', false);
				checkbox.set('disabled', true);
			}
		});
	}
	
	this.enableAllCheckboxes = function(first){
		this.container.getElements('input').each(function(checkbox){
			if(checkbox !== first){
				checkbox.set('disabled', false);
			}
		});
	}
	
	this.onViewClick = function(e){
		if(e.target){
			var section = $(e.target).getParent('section');
			var rel = null;
			var target = $(e.target);
			var nodeName = target.nodeName.toLowerCase();
			if(nodeName === 'input'){
				if(target === this.div.getElement('input')){
					if(this.delegate.allContactsWasSelected){
						if(target.checked){
							this.delegate.allContactsWasSelected(target);
						}else{
							this.delegate.allContactsWasDeselected(target);
						}
					}				
				}
			}
			if(section === this.div.getElement('section')){
				if(this.delegate && this.delegate.didClickGroups){
					if(target.nodeName.toLowerCase() !== 'input'){
						target = target.getPrevious('input');
					}
					if(target){
						rel = target.get('value');
						if(rel === null){
							rel = target.get('rel');
						}
					}
					this.delegate.didClickGroups(e, rel);
				}

				if(nodeName === 'input' && this.delegate && this.delegate.aGroupWasChecked){
					this.delegate.aGroupWasChecked(target);
				}

				if(this.selectedGroup){
					this.selectedGroup.removeClass('selected');
				}
				if(target !== null){
					this.selectedGroup = target.getParent('li');					
				}
				if(this.selectedGroup){
					this.selectedGroup.addClass('selected');
				}
			}else if(section === this.div.getElements('section')[1]){
				if(this.delegate && this.delegate.didClicPeople){
					if(nodeName !== 'input'){
						target = target.getPrevious('input');
					}
					rel = target.get('value');
					this.delegate.didClicPeople(e, rel);				
				}
				if(this.delegate && this.delegate.aPersonWasChecked){
					this.delegate.aPersonWasChecked(e, target);
				}

				if(this.selectedPerson){
					this.selectedPerson.removeClass('selected');
				}
				if(this.selectedPerson){
					this.selectedPerson = target.getParent('li');
				}
			}
		}
		parent.onViewClick(e);
	};
};

UIController.AddressBook = function(view){
	this.view = view;
	view = null;
	
	this.allContactsWasDeselected = function(elem){
		this.view.enableAllCheckboxes(elem);
		this.view.groups = [];
		this.view.people = [];
	};
	this.didClickCancel = function(e){
		this.view.groups = [];
		this.view.people = [];
		if(this.delegate && this.delegate.didClickCancel){
			this.delegate.didClickCancel(e);
		}
	};
	this.allContactsWasSelected = function(elem){
		if(this.delegate && this.delegate.didRemoveGroup){
			this.view.groups.each(function(group){
				this.delegate.didRemoveGroup(group);
			}.bind(this));
		}
		if(this.delegate && this.delegate.didRemovePerson){
			this.view.people.each(function(person){
				this.delegate.didRemovePerson(person);
			}.bind(this));
		}		
		
		this.view.disableAllCheckboxes(elem);
		this.view.groups = [];
		this.view.people = [];
		this.view.groups.push(elem.get('value'));
	};
	
	this.didClickHandle = function(e){
		var url = 'addressbook.phtml';
		var request = new Request.HTML({data: 'mini=true', url: NSObject.rootUrl + url, update: this.view.div
			, onSuccess: function(tree, elements, html, js){
				this.view.setHtml(html);
				this.view.selectedGroup = $$('li.selected')[0];
				var i = 0;
				this.view.groupContainer().getElements('li').each(function(li){
					if(li.get('rel') === 'Friend+Requests'){
						li.destroy();
					}
				});
				for(i = 0; i < this.view.groups.length; i++){
					this.view.groupContainer().getElements('input[type=checkbox]').each(function(checkbox){
						if(this.view.groups[i] === checkbox.get('value')){
							checkbox.set('checked', true);
						}
					}.bind(this));
				}

				for(i = 0; i < this.view.people.length; i++){
					this.view.peopleContainer().getElements('input[type=checkbox]').each(function(checkbox){
						if(this.view.people[i].id === JSON.decode(checkbox.get('value')).id){
							checkbox.set('checked', true);
						}
					}.bind(this));
				}
				
			}.bind(this)
			, onFailure: function(xhr){alert(xhr.responseText);}
		}).get();
	};
	this.didClickGroups = function(e, text){
		var url = 'people/' + text + '.json';
		var request = new Request({url: NSObject.rootUrl + url
			, method: 'get'
			, onSuccess: function(data, xml){
				var response = JSON.decode(data);
				this.view.clearPeople();
				if(response.people && response.people.length > 0){
					this.view.addPeople(response.people, this.view.people);					
				}
			}.bind(this)
		}).send();
	};
	
	this.aGroupWasChecked = function(elem){
		var group = elem.get('value');
		if(elem.get('checked')){
			if(!this.view.groups.contains(group)){
				this.view.groups.push(group);
			}
			if(this.delegate && this.delegate.didAddGroup){
				this.delegate.didAddGroup(group);
			}
		}else{
			if(this.delegate && this.delegate.didRemoveGroup){
				this.delegate.didRemoveGroup(group);
			}
			this.view.groups.erase(group);			
		}
	};
	
	this.aPersonWasChecked = function(e, elem){
		if(elem.nodeName === 'INPUT'){
			var person = JSON.decode(decodeURIComponent(elem.get('value')));
			if(elem.get('checked')){
				if(this.view.people.filter(function(p){return p.id === person.id;}, this).length === 0){
					this.view.people.push(person);
				}
				if(this.delegate && this.delegate.didAddPerson){
					this.delegate.didAddPerson(person);
				}
			}else{
				this.view.people = this.view.people.filter(function(p){return p.id !== person.id;}, this);
				if(this.delegate && this.delegate.didRemovePerson){
					this.delegate.didRemovePerson(person);
				}
			}
		}else{
			e.stop();
		}
	};
	
	this.view.delegate = this;
};

UIController.Post = function(){
	UIController.apply(this, arguments);
	this.didAddGroup = function(group){
		var ul = $('send_to_list').getElement('ul');
		var li = li = new Element('li', {text: decodeURIComponent(group.replace('+', ' '))});
		var input = new Element('input', {type: 'hidden', name: 'groups[]', id: 'groups_' + this.getUniqueId(), value: group});		
		li.grab(input);
		ul.grab(li);
	};
	this.didRemoveGroup = function(group){
		var fields = $('send_to_list').getElements('input[type=hidden]');
		while(field = fields.pop()){
			if(field.value == group){
				break;
			}
		}
		if(field){
			field.getParent('li').destroy();
		}
	};
	this.didAddPerson = function(person){
		var ul = $('send_to_list').getElement('ul');
		var li = li = new Element('li', {text: decodeURIComponent(person.name).replace('+', ' ')});
		var input = new Element('input', {type: 'hidden', name: 'people[]', id: 'send_to_list_' + this.getUniqueId(), value: person.id});
		li.grab(input);
		ul.grab(li);
	};
	this.didRemovePerson = function(person){
		var field = $('send_to_list').getElements('ul input[value=' + person.id + ']');
		field.getParent('li').destroy();
	};
	this.didClickCancel = function(e){
		var lis = $('send_to_list').getElements('ul li');
		lis.each(function(li){
			li.destroy();
		});
	};
	this.didClickMakeHome = function(e){
		$('make_home_page').checked = !$('make_home_page').checked;
		if($('make_home_page').checked){
			this.removeClass('home');
			this.addClass('not_home');
			this.set('title', 'Make this a regular post.');
			this.getElement('span').set('text', 'Make post');
		}else{
			this.removeClass('not_home');
			this.addClass('home');
			this.set('title', 'Make this post the home page.');
			this.getElement('span').set('text', 'Make home');
		}
	};
	this.didClickReblog = function(e){
		$('is_published').checked = true;
	};
	this.didClickMakePrivate = function(e){
		$('is_published').checked = !$('is_published').checked;
		if($('is_published').checked){
			this.removeClass('private');
			this.addClass('public');
			this.set('title', 'Make this post private. It will not show up on your public site.');
			this.getElement('span').set('text', 'Make private');
		}else{
			this.removeClass('public');
			this.addClass('private');
			this.set('title', 'Make this post public. This will show up on your public site.');
			this.getElement('span').set('text', 'Make public');
		}	
	};
	
	this.didChangePhoto = function(e){
		if($('photo_names[' + this.value + ']')){
			alert("you've already added that photo.");
			e.stop();
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
	$('make_home_link').addEvent('click', this.didClickMakeHome);
	$('publish_link').addEvent('click', this.didClickMakePrivate);
	if($('reblog')){
		$('reblog').addEvent('click', this.didClickReblog);
	}
}

var editor = null;
var postController;
window.addEvent('load', function(e){
	editor = $('body').mooEditable();
	var postMenu = new UIView.PostMenu('post_menu');
	var textarea = new UIView.TextArea('body-mooeditable-container');
	var optionsPanel = new UIView.Panel('options', {options_hide_link: 'options_hide_link'});
	var addressBookController = new UIController.AddressBook(new UIView.Modal.AddressBook('addressbook', {handle: 'address'}));
	postController = new UIController.Post();
	addressBookController.delegate = postController;
	
});

function photoWasUploaded(photo_name, file_name, photo_path, width){
	postController.didUploadPhoto(photo_name, file_name, photo_path, width);
}

UIController.Panel = function(){
	
};

function photoDidChange(e){
	if($('photo_names[' + this.value + ']')){
		alert("you've already added that photo.");
		e.stop();
	}else{
		$('media_form').submit();
	}
}

