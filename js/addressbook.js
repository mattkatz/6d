UIView.List = function(id, options){
	UIView.apply(this, [id, options]);
	var grabbed_item = null;
	var current_position = {x:0, y: 0};
	var mouse_elem_delta = {x: 0, y: 0};
	var view_position = {x:0,y:0};
	function makeHot(elem){
		deselect(elem);
		elem.addClass('hot');
	}
	
	function makeSelected(elem){
		deselect(elem);
		elem.addClass('selected');
	}
	
	function makeNotHot(elem){
		deselect(elem);
		elem.removeClass('hot');
	}
	function makeInactive(elem){
		deselect(elem);
		elem.addClass('inactive');
	}
	function makeDeleted(elem){
		elem.addClass('deleted');
	}
	function deselect(elem){
		elem.removeClass('selected');
		elem.removeClass('inactive');
	}
	
	this.onDoubleClick = function(e){
		var elem = (e.target.nodeName.toLowerCase() === 'li' ? e.target : e.target.getParent('li'));
		if(elem && e.target.nodeName.toLowerCase() !== 'input'){
			if(this.delegate && this.delegate.itemWasClicked && elem){			
				this.delegate.itemWasDoubleClicked(this, elem, e);
			}
		}
	};
	this.onClick = function(e){
		this.is_active_view = true;
		var elem = (e.target.nodeName.toLowerCase() === 'li' ? e.target : e.target.getParent('li'));
		if(elem && e.target.nodeName.toLowerCase() !== 'input'){
			if(!e.shift){
				this.container.getElements('li').each(function(li){
					deselect(li);
				});
			}
			this.set('selected_item', elem);
			makeSelected(elem);
			if(this.delegate && this.delegate.itemWasClicked && elem){			
				this.delegate.itemWasClicked(this, elem, e);
			}
		}
	};
	this.onMouseUp = function(e){
		UIView.List.prototype.mouse_is_down = false;
	};
	this.isWithin = function(position){
		return view_position.x < current_position.x && view_position.y;
	};
	
	this.onMouseOver = function(e){
		if(UIView.List.prototype.mouse_is_down){
			var elem = (e.target.nodeName.toLowerCase() === 'li' ? e.target : e.target.getParent('li'));
			if(elem && this.is_droppable){
				if(!elem.hasClass('selected') && !elem.hasClass('inactive')){
					makeHot(elem);
				}
			}
		}
	};
	
	this.onMouseOut = function(e){
		if(UIView.List.prototype.mouse_is_down){
			var elem = (e.target.nodeName.toLowerCase() === 'li' ? e.target : e.target.getParent('li'));
			if(elem){
				makeNotHot(elem);
			}
		}
	};
	
	this.onMouseDown = function(e){
		UIView.List.prototype.mouse_is_down = true;
		var elem = $(e.target);
		var li = (elem.nodeName.toLowerCase() === 'li' ? elem : elem.getParent('li'));
		if(li && e.target.nodeName.toLowerCase() !== 'input'){
			this.pickUpAndAttach(li, e);
		}
	};
	this.mouseMoveOnDocument = function(e){
		if(grabbed_item){
			if(e.page.x !== current_position.x && e.page.y !== current_position.y){
				grabbed_item.setStyles({display:'block'});
			}
			grabbed_item.setPosition({x: e.page.x+mouse_elem_delta.x, y:e.page.y+mouse_elem_delta.y});
		}
	};
	this.getDroppedOnElement = function(e){
		var elem = e.target;
		var dropped_on_element = null;
		elem = (elem.hasClass('hot') ? elem : elem.getParent('.hot'));
		if(elem){
			makeNotHot(elem);
			dropped_on_element = elem;
		}
		return dropped_on_element;
	};
	
	this.mouseUpOnDocument = function(e){
		$(document).removeEvent('mousemove', this.mouseMoveOnDocument);
		$(document).removeEvent('mouseup', this.mouseUpOnDocument);
		var dropped_on_item = this.getDroppedOnElement(e);
		if(dropped_on_item !== null && this.delegate && this.delegate.itemWasDropped){
			this.delegate.itemWasDropped(dropped_on_item, grabbed_item, e);
		}
		this.dropBack();
		grabbed_item.dispose();
//		e.stop();
	};
	
	this.dropBack = function(){
		grabbed_item.setStyles({opacity: 1});
	};
	
	this.pickUpAndAttach = function(elem, e){
		grabbed_item = new Element('div', {text: '+', rel: elem.get('rel')});
		grabbed_item.setStyles({display: 'none', top: e.page.y+10, left: e.page.x, textAlign: 'center', width: '20px', height: '20px', position: 'absolute', border: 'solid 1px red'});
		current_position = grabbed_item.getPosition(window);
		mouse_elem_delta.x = 0;
		mouse_elem_delta.y = 10;
		$(document).addEvent('mousemove', this.mouseMoveOnDocument.bind(this));
		$(document).addEvent('mouseup', this.mouseUpOnDocument.bind(this));
		grabbed_item.inject($$('body')[0], 'bottom');
		e.stop();
	};
	
	this.onMouseMove = function(e){
		
	};
	
	this.addView = function(view){
		this.views.push(view);
	};
	this.removeView = function(view){
		
	};
	this.onBlur = function(e){
		this.is_active_view = false;
		if(this.get('selected_item')){
			makeInactive(this.get('selected_item'));
		}
	};
	this.addItem = function(text, id){
		var list = this.container.getElements('li');
		var item = null;
		var top_list = list.filter(function(li){
			return li.get('rel').toLowerCase() < text.toLowerCase();
		}.bind(this));
		item = top_list.pop();
		if(item){
			var new_item = new Element('li', {rel: id}).grab(
				new Element('span', {text: text})
			);
			
			item.grab(new_item, 'after');
			this.set('selected_item', new_item);
		}
		return new_item;
	};
	this.addNewItem = function(view, elem){
		var ul = this.container.getElement('ul');
		var temp_id = this.container.id + '_new_item';//'_new_' + ul.getChildren('li').length;
		var existing_elem = $(temp_id);
		var new_field = null;
		var new_form = null;
		if(!existing_elem){
			ul.grab(
				new Element('li', {
					id: temp_id
				}).grab(
					new Element('form', {
						id: this.container.id + '_form_' + temp_id
					}).grab(
						new Element('input', {
							type: 'text'
							, id: this.container.id + '_field_' + temp_id
							, name: this.get('field_name')//'group[text]'
							, value: this.get('field_default_value')
						})
					)
				)
			);
			new_field = $(this.container.id + '_field_' + temp_id);
			new_field.focus();
			new_field.select();
			new_form = $(this.container.id + '_form_' + temp_id);
			new_form.addEvent('submit', this.eventAddFormSubmit);
			this.set('selected_item', $(temp_id));
		}
	};
	this.addFormSubmit = function(e){
		if(this.delegate && this.delegate.onSubmit){
			this.delegate.onSubmit(this, e);
		}
		e.target.getParent('li').destroy();
		e.target.destroy();
	};
	
	this.onKeyPress = function(e){
		if(this.is_active_view){
			if(e.key === 'backspace'){
				var selected_elements = this.container.getElements('li.selected');
				if(selected_elements){
					selected_elements.each(function(li){
						makeDeleted(li);
					});
				}
				if(selected_elements && this.delegate && this.delegate.onDeleteKeyPressed){
					this.delegate.onDeleteKeyPressed(this, selected_elements, e);
					e.stop();
				}
			}
		}
	};
	this.removeDeletedItems = function(){
		var deleted_items = this.container.getElements('li.deleted');
		var item = null;
		while(item = deleted_items.pop()){
			item.destroy();
		}
	};	
	this.is_active_view = false;
	this.eventAddFormSubmit = this.addFormSubmit.bind(this);
	this.list = [];
	this.is_draggable = true;
	this.is_droppable = true;
	this.container.addEvent('click', this.onClick.bind(this));
	this.container.addEvent('dblclick', this.onDoubleClick.bind(this));	
	NSObject.apply(this, [options]);
	if(this.is_draggable){
		this.container.addEvent('mousedown', this.onMouseDown.bind(this));
		this.container.addEvent('mousemove', this.onMouseMove.bind(this));
	}
	this.container.addEvent('mouseup', this.onMouseUp.bind(this));
	this.container.addEvent('mouseover', this.onMouseOver.bind(this));
	this.container.addEvent('mouseout', this.onMouseOut.bind(this));
	this.set('selected_item', this.container.getElement('li.selected'));	
	view_position = this.container.getPosition(window);
	document.addEvent('keypress', this.onKeyPress.bind(this));
}
UIView.List.prototype.mouse_is_down = false;

UIView.ToolBar = function(id, view, options){
	UIView.apply(this, [id, options]);
	this.delegate = null;
	this.view = view;
	
	this.onClick = function(e){
		var elem = (e.target.nodeName.toLowerCase() === 'a' ? e.target : e.target.getParent('a'));
		if(elem){
			if(this.delegate && this.delegate.itemWasClicked && elem){	
				this.delegate.itemWasClicked(this, elem, e);
			}
			this.view.addNewItem(this, elem);
			e.stop();
		}
	};
	
	this.container.addEvent('click', this.onClick.bind(this));
	NSObject.apply(this, arguments);
}
UIController.AddressBook = function(views){
	UIController.apply(this, arguments);
	var path = window.location.pathname.split('/');
	this.url_field = null;
	this.friend_request_button = null;
	this.url_field_observer = null;
	
	path.pop();
	this.views = views;
	this.selectedGroup = null;
	this.eventPersonWasClicked = null;
	this.itemWasDoubleClicked = function(view, elem, e){
		switch(view.id){
			case('groups'):
				if(window.opener){
					window.opener.groupWasClicked(elem.get('rel'));
				};
				break;
			case('people'):
				if(window.opener){
					window.opener.personWasClicked(elem.get('rel'));
				};
				break;
		}
	};
	this.itemWasClicked = function(view, elem, e){
		switch(view.id){
			case('groups'):
				this.getView('people').onBlur();
				this.selectGroupItem(elem.get('rel'));
				break;
			case('people'):
				this.getView('groups').onBlur();
				this.selectPersonItem(elem.get('rel'));
				if(e.target.nodeName !== 'BUTTON'){
					e.stop();					
				}
				break;
		}
	};
	this.selectPersonItem = function(id){
		var text = this.selectedGroup.getProperty('rel');
		var url = 'person/' + id + '.phtml';
		if(text === 'Friend Requests'){
			url = 'follower/' + id + '.phtml';
		}
		if(this.url_field_observer !== null){
			clearInterval(this.url_field_observer);
		}
		if($('friendrequest_form')){
			$('friendrequest_form').removeEvents();
		}
		if($('person_form')){
			$('person_form').removeEvents();
		}
		var request = new Request({url: NSObject.rootUrl + url
			, method: 'get'
			, onSuccess: function(text, xml){
				$('detail').set('html', text);
				if($('friendrequest_form')){
					$('friendrequest_form').addEvent('submit', this.eventFollowFormDidSubmit);
				}
				if($('person_form')){
					$('person_form').addEvent('submit', this.eventPersonFormDidSubmit);
				}
				this.url_field = $('url');
				this.friend_request_button = $('friend_request_button');
				this.url_field_observer = setInterval(this.observeUrlField.bind(this), 250);
			}.bind(this)
		}).send();
	};
	this.selectGroupItem = function(text){
		var text = this.selectedGroup.getProperty('rel');
		var form = this.selectedGroup.getParent('form');
		if(form !== null){
			new Request({url: form.action
				, method: form.method
				, onSuccess: function(text, xml){
					alert(text);
				}
			}).send(form.toQueryString());
		}
		var url = 'people/' + text + '.phtml';
		if(text === 'Friend Requests'){
			url = 'followers.phtml';
		}
		var request = new Request({url: NSObject.rootUrl + url
			, method: 'get'
			, onSuccess: function(text, xml){
				$('detail').set('html', null);
				var ul = $('people').getFirst('ul');
				text = text.replace('<ul>', '');
				text = text.replace('</ul>', '');
				ul.set('html', text);
			}
		}).send();
	};
	this.itemWasDropped = function(dropped_on_item, grabbed_item, e){
		var person_id = grabbed_item.get('rel');
		var group_text = dropped_on_item.get('rel');
		var request = new Request({url: NSObject.rootUrl + 'group.json'
			, method: 'post'
			, onSuccess: function(text, xml){
				var response = JSON.decode(text);
			}
		}).send('group[text]=' + group_text + '&group[parent_id]=' + person_id);
	};
	this.personFormDidSubmit = function(e){
		$(e.target).set('send', {url: e.target.action + '.json', onSuccess: function(text, xml){
			var user_message = $$('.user_message')[0];
			var response = JSON.decode(text);
			user_message.set('text', response.message);
			user_message.show();
			$$('#person_form legend')[0].set('text', response.person.name);
		}});
		$(e.target).send();
		e.stop();
	};
	this.followFormDidSubmit = function(e){
		$(e.target).set('send', {url: e.target.action + '.json', onSuccess: function(text, xml){
			var user_message = $$('.user_message')[0];
			user_message.set('text', JSON.decode(text).message);
			user_message.show();
		}});
		$(e.target).send();
		e.stop();
	};
	this.onSubmit = function(view, e){
		e.stop();
		if(view.id === 'groups'){
			var form = $(e.target);
			form.method = 'post';
			form.action = NSObject.rootUrl + 'group.json';
			new Request({url: form.action
				, method: form.method
				, onSuccess: function(text, xml){
					var response = JSON.decode(text, false);
					var new_item = this.getView('groups').addItem(response.group.text, response.group.text);
				}.bind(this)
				, onFailure: function(req){
					alert(req.responseText);
				}
			}).send(form.toQueryString());
		}else if(view.id === 'people'){
			var form = $(e.target);
			form.method = 'post';
			form.action = NSObject.rootUrl + 'person.json';
			new Request({url: form.action
				, method: form.method
				, onSuccess: function(text, xml){
					var response = JSON.decode(text, false);
					var new_item = this.getView('people').addItem(response.person.name, response.person.id);
					var span = new_item.getChildren('span');
					span.set('rel', response.person.id);
					(new Element('a', {href:NSObject.rootUrl + 'person/' + response.person.id
						, title: 'edit ' + response.person.name})).grab(span);
				}.bind(this)
				, onFailure: function(req){
					alert(req.responseText);
				}
			}).send(form.toQueryString());
		}
	};
	this.onDeleteKeyPressed = function(view, selected_elements, e){
		if(view.id === 'groups'){
			var groups = selected_elements.map(function(elem){
				return elem.get('rel');
			}).join(',');
			
			if(confirm('Just to make sure, did you want to delete the "' + groups + '" group(s)? If so, click Ok. Otherwise, click Cancel.')){
				new Request({url: NSObject.rootUrl + 'groups.json'
					, method: 'delete'
					, onSuccess: function(text, xml){
						var response = JSON.decode(text, false);
						this.getView('groups').removeDeletedItems();
					}.bind(this)
					, onFailure: function(req){
						alert(req.responseText);
					}
				}).send('groups[]=' + groups);
			}
		}else if(view.id === 'people'){
			var selected_group = this.getView('groups').get('selected_item');
			var people = selected_elements.map(function(elem){
				return elem.get('rel');
			}).join(',');
			if(selected_group.get('rel') === 'All Contacts'){
				if(confirm('Just to make sure, did you want to delete the selected person(s)? If so, click Ok. Otherwise, click Cancel.')){
					new Request({url: NSObject.rootUrl + 'people.json'
						, method: 'post'
						, onSuccess: function(text, xml){
							var response = JSON.decode(text, false);
							this.getView('people').removeDeletedItems();
						}.bind(this)
						, onFailure: function(req){
							alert(req.responseText);
						}
					}).send('_method=delete&ids=' + people);
				}
			}else{
				new Request({url: NSObject.rootUrl + 'groups.json'
					, method: 'post'
					, onSuccess: function(text, xml){
						var response = JSON.decode(text, false);
						this.getView('groups').removeDeletedItems();
					}.bind(this)
					, onFailure: function(req){
						alert(req.responseText);
					}
				}).send('_method=delete&people[]=' + people);				
			}
		}
	};
	this.getView = function(id){
		var found = this.views.filter(function(view){
			return view.id === id;
		});
		if(found && found.length > 0){
			return found[0];
		}else{
			return null;
		}
	};
	this.addView = function(view){
		if(view){
			view.delegate = this;
			this.views.push(view);
		}
	};
	
	this.removeView = function(view){
		
	};
	
	this.observerKeyValueSet = function(key, value){
		if(key === 'selected_item'){
			this.selectedGroup = value;
		}
	};
	this.observeUrlField = function(){
		if(this.friend_request_button){
			if(this.url_field.get('value').length === 0){
				this.friend_request_button.set('disabled', true);
			}else{
				this.friend_request_button.set('disabled', false);
			}
		}
	};
	if(this.views && this.views.length > 0){
		this.views.each(function(view){
			view.delegate = this;
			if(view.id === 'groups'){
				this.selectedGroup = view.container.getElement('.selected');
				view.addObserver(this, 'selected_item');
			}
		}, this);
	}
	this.eventAddFormSubmit = this.onSubmit.bind(this);
	this.eventFollowFormDidSubmit = this.followFormDidSubmit.bind(this);
	this.eventPersonFormDidSubmit = this.personFormDidSubmit.bind(this);
}

window.addEvent('domready', function(){
	try{
		var groupListView = new UIView.List($('groups'), {field_name: 'group[text]', field_default_value:'new group', is_draggable: false});
		var personListView = new UIView.List($('people'), {is_droppable: false, field_name: 'person[name]', field_default_value: 'new person'});
		var groupToolBarView = new UIView.ToolBar($$('#groups footer nav')[0], groupListView, null);	
		var peopleToolBarView = new UIView.ToolBar($$('#people footer nav')[0], personListView, null);	

		var controller = new UIController.AddressBook([groupListView, personListView, groupToolBarView, peopleToolBarView]);
	}catch(e){
		alert('Oh snap!' + e);
	}
});
