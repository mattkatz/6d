UIView.List = function(id, options){
	UIView.apply(this, [id, options]);
	var grabbed_item = null;
	var current_position = {x:0, y: 0};
	var mouse_elem_delta = {x: 0, y: 0};
	var view_position = {x:0,y:0};
	function makeHot(elem){
		deselect(elem);
		SDDom.addClass('hot', elem);
	}
	
	function makeSelected(elem){
		deselect(elem);
		SDDom.addClass('selected', elem);
	}
	
	function makeNotHot(elem){
		deselect(elem);
		SDDom.removeClass('hot', elem);
	}
	function makeInactive(elem){
		deselect(elem);
		SDDom.addClass('inactive', elem);
	}
	function makeDeleted(elem){
		SDDom.addClass('deleted', elem);
	}
	function deselect(elem){				
		SDDom.removeClass('selected', elem);
		SDDom.removeClass('inactive', elem);
	}
	
	this.onDoubleClick = function(e){
		var elem = (e.target.nodeName.toLowerCase() === 'li' ? e.target : SDDom.getParent('li', e.target));
		if(elem && e.target.nodeName.toLowerCase() !== 'input'){
			if(this.delegate && this.delegate.itemWasClicked && elem){			
				this.delegate.itemWasDoubleClicked.apply(this.delegate, [this, elem, e]);
			}
		}
	};
	this.onClick = function(e){
		this.is_active_view = true;
		var elem = (e.target.nodeName.toLowerCase() === 'li' ? e.target : SDDom.getParent('li', e.target));
		if(elem && e.target.nodeName.toLowerCase() !== 'input'){
			if(!e.shift){
				SDArray.each(SDDom.findAll('li', this.container), function(li){
					deselect(li);
				});
			}			
			this.set('selected_item', elem);
			makeSelected(elem);
			if(this.delegate && this.delegate.itemWasClicked && elem){			
				this.delegate.itemWasClicked.apply(this.delegate, [this, elem, e]);
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
			var elem = (e.target.nodeName.toLowerCase() === 'li' ? e.target : SDDom.getParent('li', e.target));
			if(elem && this.is_droppable){
				if(!SDDom.hasClass('selected', elem) && !SDDom.hasClass('inactive', elem)){
					makeHot(elem);
				}
			}
		}
	};
	
	this.onMouseOut = function(e){
		if(UIView.List.prototype.mouse_is_down){
			var elem = (e.target.nodeName.toLowerCase() === 'li' ? e.target : SDDom.getParent('li', e.target));
			if(elem){
				makeNotHot(elem);
			}
		}
	};
	
	this.onMouseDown = function(e){		
		UIView.List.prototype.mouse_is_down = true;
		var elem = e.target;
		var li = (elem.nodeName.toLowerCase() === 'li' ? elem : SDDom.getParent('li', elem));
		if(li && e.target.nodeName.toLowerCase() !== 'input'){			
			this.pickUpAndAttach(li, e);
		}
	};
	this.mouseMoveOnDocument = function(e){
		if(grabbed_item){
			if(SDDom.pageX(e) !== current_position.x && SDDom.pageY(e) !== current_position.y){
				SDDom.setStyles({display:'block'}, grabbed_item);
			}
			SDDom.setStyles({left: (SDDom.pageX(e) + mouse_elem_delta.x) + 'px'
				, top:(SDDom.pageY(e) + mouse_elem_delta.y) + 'px'}, grabbed_item);
		}
	};
	this.getDroppedOnElement = function(e){
		var elem = e.target;
		var dropped_on_element = null;
		elem = (SDDom.hasClass('hot', elem) ? elem : SDDom.getParent('li', elem));
		if(elem){
			makeNotHot(elem);
			dropped_on_element = elem;
		}
		return dropped_on_element;
	};
	
	this.mouseUpOnDocument = function(e){
		SDDom.removeEventListener(document, 'mousemove', mouseMoveEvent);
		SDDom.removeEventListener(document, 'mouseup', mouseUpEvent);
		var dropped_on_item = this.getDroppedOnElement(e);
		if(dropped_on_item !== null && this.delegate && this.delegate.itemWasDropped){
			this.delegate.itemWasDropped.apply(this.delegate, [dropped_on_item, grabbed_item, e]);
		}
		this.drop();
	};
	
	this.drop = function(){
		SDDom.setStyles({opacity: 1}, grabbed_item);
		SDDom.remove(grabbed_item);
	};
	
	this.pickUpAndAttach = function(elem, e){
		grabbed_item = SDDom.create('div');
		grabbed_item.innerHTML = '+';
		grabbed_item.setAttribute('rel', elem.getAttribute('rel'));
		SDDom.setStyles({display: 'none', top: (SDDom.pageY(e)+10) + 'px', left: SDDom.pageX(e) + 'px', textAlign: 'center', width: '20px', height: '20px', position: 'absolute', border: 'solid 1px red'}, grabbed_item);
		current_position = SDDom.getPosition(grabbed_item, window);
		mouse_elem_delta.x = 0;
		mouse_elem_delta.y = 10;
		SDDom.append(SDDom.byTag('body')[0], grabbed_item);		
		SDDom.addEventListener(document, 'mousemove', mouseMoveEvent);
		SDDom.addEventListener(document, 'mouseup', mouseUpEvent);
		SDDom.stop(e);
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
		var list = SDDom.findAll('li', this.container);
		var item = null;
		var top_list = SDArray.collect(list, function(li){
			return li.getAttribute('rel').toLowerCase() < text.toLowerCase();
		});
		item = top_list.pop();
		if(item){
			var new_item = SDDom.create('li');
			new_item.innerHTML = '<span>' + text + '</span><input type="checkbox" id="group_' + id + '" name="groups" value="' + text + '" /><form action="" method="post" class="delete"><input type="hidden" value="' + text + '" name="text" /><input type="hidden" value="delete" name="_method" /><button><span>Delete</span></button></form>';
			new_item.setAttribute('rel', id);
			SDDom.insertAfter(new_item, item);
			this.set('selected_item', new_item);
		}
		return new_item;
	};
	this.addNewItem = function(view, elem){
		var ul = SDDom.findFirst('ul', this.container);
		var temp_id = this.container.id + '_new_item';
		var existing_elem = SDDom(temp_id);
		var new_field = null;
		var new_form = null;
		if(!existing_elem){
			var input = SDDom.create('input', {type: 'text', id: this.container.id + '_field_' + temp_id, name: this.get('field_name'), value: this.get('field_default_value')});
			
			var form = SDDom.create('form', {id: this.container.id + '_form_' + temp_id});
			var li = SDDom.create('li', {id: temp_id});
			SDDom.append(form, input);
			SDDom.append(li, form);
			SDDom.append(ul, li);
			
			new_field = SDDom(this.container.id + '_field_' + temp_id);
			new_field.focus();
			new_field.select();
			new_form = SDDom(this.container.id + '_form_' + temp_id);
			SDDom.addEventListener(new_form, 'submit', this.eventAddFormSubmit);
			this.set('selected_item', SDDom(temp_id));
		}
	};
	this.addFormSubmit = function(e){
		if(this.delegate && this.delegate.onSubmit){
			this.delegate.onSubmit.apply(this.delegate, [this, e]);
		}
		SDDom.remove(SDDom.getParent('li', e.target));
	};
	
	this.onKeyPress = function(e){
		if(this.is_active_view){
			if(e.keyCode === SDDom.keys.BACKSPACE){
				var selected_elements = SDDom.findAll('li.selected', this.container);
				if(selected_elements){
					SDArray.each(selected_elements, function(li){
						makeDeleted(li);
					});
				}
				if(selected_elements && this.delegate && this.delegate.onDeleteKeyPressed){
					this.delegate.onDeleteKeyPressed.apply(this.delegate, [this, selected_elements, e]);
					SDDom.stop(e);
				}
			}
		}
	};
	this.removeDeletedItems = function(){
		var deleted_items = SDDom.findAll('li.deleted', this.container);
		SDDom.remove(deleted_items);
	};
	
	// The code needs reference to the event delegate so it can be removed. I've created private variables to 
	// store the delegate reference so I can remove them after the person moves a person to a group. A new group was
	// being created after moving a contact into a group and clicking on a group because the mouseup delegate was
	// still firing since it wasn't getting removed after the contact was dropped onto a group.
	var mouseMoveEvent = this.bind(this.mouseMoveOnDocument);
	var mouseUpEvent = this.bind(this.mouseUpOnDocument);
	
	this.is_active_view = false;
	this.eventAddFormSubmit = this.bind(this.addFormSubmit);
	this.list = [];
	this.is_draggable = true;	
	this.is_droppable = true;
	SDDom.addEventListener(this.container, 'click', this.bind(this.onClick));		
	SDDom.addEventListener(this.container, 'dblclick', this.bind(this.onDoubleClick));	
	SDObject.apply(this, [options]);
	if(this.is_draggable){
		SDDom.addEventListener(this.container, 'mousedown', this.bind(this.onMouseDown));
		SDDom.addEventListener(this.container, 'mousemove', this.bind(this.onMouseMove));
	}
	
	SDDom.addEventListener(this.container, 'mouseup', this.bind(this.onMouseUp));
	SDDom.addEventListener(this.container, 'mouseover', this.bind(this.onMouseOver));
	SDDom.addEventListener(this.container, 'mouseout', this.bind(this.onMouseOut));
	this.set('selected_item', SDDom.findFirst('li.selected', this.container));	
	view_position = SDDom.getPosition(this.container);
	SDDom.addEventListener(document, 'keypress', this.bind(this.onKeyPress));
}
UIView.List.prototype.mouse_is_down = false;

UIView.ToolBar = function(container, view, options){
	UIView.apply(this, [container.id, options]);
	this.view = view;
	this.container = container;
	this.onClick = function(e){
		var elem = (e.target.nodeName.toLowerCase() === 'a' ? e.target : SDDom.getParent('a', e.target));
		if(elem){
			if(this.delegate && this.delegate.itemWasClicked && elem){	
				this.delegate.itemWasClicked.apply(this.delegate, [this, elem, e]);
			}
			this.view.addNewItem(this, elem);
			SDDom.stop(e);
		}
	};
	
	SDDom.addEventListener(this.container, 'click', this.bind(this.onClick));
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
				this.selectGroupItem(elem.getAttribute('rel'));
				break;
			case('people'):
				this.getView('groups').onBlur();
				this.selectPersonItem(elem.getAttribute('rel'));
				if(e.target.nodeName !== 'BUTTON'){
					SDDom.stop(e);
				}
				break;
		}
	};
	this.selectPersonItem = function(id){
		var text = this.selectedGroup.getAttribute('rel');
		var url = 'person/' + id + '.phtml';
		if(text === 'Friend Requests'){
			url = 'follower/' + id + '.phtml';
		}
		if(this.url_field_observer !== null){
			clearInterval(this.url_field_observer);
		}
		if(SDDom('friendrequest_form')){
			SDDom.removeAllEventListeners(SDDom('friendrequest_form'));
		}
		if(SDDom('person_form')){
			SDDom.removeAllEventListeners(SDDom('person_form'));
		}
		var url = SDObject.rootUrl + url;
		var ajax = new SDAjax({method: 'get', DONE: [this, this.onPersonSelectedDONE]});
		ajax.send(url);
	};
	this.onPersonSelectedDONE = function(request){
		SDDom('detail').innerHTML = request.responseText;
		var form = SDDom('friendrequest_form');
		if(form){
			SDDom.addEventListener(form, 'submit', this.eventFollowFormDidSubmit);
		}
		var person_form = SDDom('person_form');
		if(person_form){
			SDDom.addEventListener(person_form, 'submit', this.eventPersonFormDidSubmit);
		}
		this.url_field = SDDom('url');
		this.friend_request_button = SDDom('friend_request_button');
		this.url_field_observer = setInterval(this.bind(this.observeUrlField), 250);
	};
	this.onSelectGroupItemDONE = function(request){
		alert(request.responseText);
	};
	this.onGetPeopleDONE = function(request){
		SDDom('detail').innerHTML = null;
		var ul = SDDom.findFirst('ul', SDDom('people'));
		var text = request.responseText;
		text = text.replace('<ul>', '');
		text = text.replace('</ul>', '');
		ul.innerHTML = text;
	};
	this.selectGroupItem = function(text){
		var text = this.selectedGroup.getAttribute('rel');		
		/*var form = SDDom.getParent('form', this.selectedGroup);
		if(form !== null){
			console.log(form);
			var url = SDObject.rootUrl + url;
			(new SDAjax({method: form.method
				, parameters: SDDom.toQueryString(form)
				, DONE: [this, this.onSelectGroupItemDONE]})).send(url);
			
		}*/
		var url = 'people/' + text + '.phtml';
		if(text === 'Friend Requests'){
			url = 'followers.phtml';
		}
		(new SDAjax({method: 'get'
			, DONE: [this, this.onGetPeopleDONE]})).send(SDObject.rootUrl + url);
				
	};
	this.onItemWasDroppedDONE = function(request){
		var response = JSON.parse(request.responseText);
	};
	this.itemWasDropped = function(dropped_on_item, grabbed_item, e){		
		if(dropped_on_item && SDArray.contains(dropped_on_item, SDDom.findAll('li', this.getView('groups').container))){
			var person_id = grabbed_item.getAttribute('rel');
			var group_text = dropped_on_item.getAttribute('rel');
			(new SDAjax({parameters: 'group[text]=' + group_text + '&group[parent_id]=' + person_id
				, DONE: [this, this.onItemWasDroppedDONE]})).send(SDObject.rootUrl + 'group.json');
		}
	};
	this.onPersonWasSubmittedDONE = function(request){
		var user_message = SDDom.findFirst('.user_message');
		var response = JSON.parse(request.responseText);
		user_message.innerHTML = response.message;
		SDDom.show(user_message);
		SDDom.findFirst('legend', SDDom('person_form')).innerHTML = response.person.name;
	};
	this.personFormDidSubmit = function(e){
		(new SDAjax({method: e.target.method
			, parameters: SDDom.toQueryString(e.target)
			, DONE: [this, this.onPersonWasSubmittedDONE]})).send(e.target.action + '.json');

		SDDom.stop(e);
	};
	this.onFollowWasSubmittedDONE = function(request){
		var user_message = $$('.user_message')[0];
		user_message.set('text', JSON.parse(text).message);
		user_message.show();
	};
	this.followFormDidSubmit = function(e){
		(new SDAjax({method: e.target.method
			, parameters: SDDom.toQueryString(e.target)
			, DONE: [this, this.onFollowWasSubmittedDONE]})).send(e.target.action + '.json');

		SDDom.stop(e);
	};
	this.onNewGroupSubmitDONE = function(request){
		var response = JSON.parse(request.responseText, false);
		var new_item = this.getView('groups').addItem(response.group.text, response.group.text);
	};
	
	this.onNewPersonSubmitDONE = function(request){
		var response = JSON.parse(request.responseText, false);
		var new_item = this.getView('people').addItem(response.person.name, response.person.id);
		var span = SDDom.findAll('span', new_item);
		span.setAttribute('rel', response.person.id);
		var a = SDDom.create('a', {href:SDObject.rootUrl + 'person/' + response.person.id
			, title: 'edit ' + response.person.name});
		SDDom.append(span, a);
		
	};
	this.onSubmit = function(view, e){
		SDDom.stop(e);
		if(view.id === 'groups'){
			var form = e.target;
			form.method = 'post';
			form.action = SDObject.rootUrl + 'group.json';
			(new SDAjax({method: form.method
				, parameters: SDDom.toQueryString(form)
				, DONE: [this, this.onNewGroupSubmitDONE]})).send(form.action);
			
		}else if(view.id === 'people'){
			var form = e.target;
			form.method = 'post';
			form.action = SDObject.rootUrl + 'person.json';
			(new SDAjax({method: form.method
				, parameters: SDDom.toQueryString(form)
				, DONE: [this, this.onNewPersonSubmitDONE]})).send(form.action);
			
		}
	};
	
	this.onDeleteDONE = function(request){
		var response = JSON.parse(request.responseText, false);
		this.getView('groups').removeDeletedItems();
	};
	this.onPersonDeleteDONE = function(request){		
		var response = JSON.parse(request.responseText, false);
		this.getView('people').removeDeletedItems();	
	};
	this.onGroupDeleteDONE = function(request){
		var response = JSON.parse(request.responseText, false);
		this.getView('groups').removeDeletedItems();
	};
	this.onDeleteKeyPressed = function(view, selected_elements, e){
		if(view.id === 'groups'){
			var i = selected_elements.length;
			var groups = SDArray.pluck(selected_elements, function(elem){return elem.getAttribute('rel');}).join(',');
			
			if(confirm('Just to make sure, did you want to delete the "' + groups + '" group(s)? If so, click Ok. Otherwise, click Cancel.')){
				(new SDAjax({method: 'delete'
					, parameters: 'groups[]=' + groups
					, DONE: [this, this.onDeleteDONE]})).send(SDObject.rootUrl + 'groups.json');
				
			}
		}else if(view.id === 'people'){			
			var selected_group = this.getView('groups').get('selected_item');
			var people = SDArray.pluck(selected_elements, function(elem){return elem.getAttribute('rel');}).join(',');
			var group = selected_group.getAttribute('rel');
			if(group === 'All Contacts'){
				if(confirm('Just to make sure, did you want to delete the selected person(s)? If so, click Ok. Otherwise, click Cancel.')){
					
					(new SDAjax({method: 'delete', parameters: 'ids=' + people
						, DONE: [this, this.onPersonDeleteDONE]})).send(SDObject.rootUrl + 'people.json');
					
				}
			}else if(confirm('Do you want to remove ' + people + ' from the ' + group + ' group?')){
				(new SDAjax({method: 'delete', parameters: 'ids[]=' + people + '&group[text]=' + group
					, DONE: [this, this.onPersonDeleteDONE]})).send(SDObject.rootUrl + 'groups.json');
				
			}
		}
	};
	this.getView = function(id){
		var found = SDArray.find(this.views, function(view){
			return view.id === id;
		});
		return found;
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
			if(this.url_field.value.length === 0){
				this.friend_request_button.setAttribute('disabled', true);
			}else{
				this.friend_request_button.removeAttribute('disabled');
			}
		}
	};

	if(this.views && this.views.length > 0){
		var i = this.views.length;
		while(view = this.views[--i]){
			view.delegate = this;
			if(view.id === 'groups'){
				this.selectedGroup = SDDom.findFirst('.selected', view.container);
				view.addObserver(this, 'selected_item');
			}
		}
	}
	this.eventAddFormSubmit = this.bind(this.onSubmit);
	this.eventFollowFormDidSubmit = this.bind(this.followFormDidSubmit);
	this.eventPersonFormDidSubmit = this.bind(this.personFormDidSubmit);
}

SDDom.addEventListener(window, 'load', function(){
	try{
		var groupListView = new UIView.List('groups', {field_name: 'group[text]', field_default_value:'new group', is_draggable: false});
		var personListView = new UIView.List('people', {is_droppable: false, field_name: 'person[name]', field_default_value: 'new person'});
		var groupToolBarView = new UIView.ToolBar(SDDom.findFirst('#groups footer nav'), groupListView, null);	
		var peopleToolBarView = new UIView.ToolBar(SDDom.findFirst('#people footer nav'), personListView, null);	

		var controller = new UIController.AddressBook([groupListView, personListView, groupToolBarView, peopleToolBarView]);
	}catch(e){
		alert('Oh snap!' + e);
	}
});
