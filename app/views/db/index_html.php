<div class="horizontal slider container">
	<h1>Databases on <?php echo $host;?></h1>
	<ul class="horizontal">
	<?php foreach($databases as $db):?>
		<li class="closed"><a href="javascript:void(0);" class="database"><span><?php echo $db->Database;?></span></a></li>
	<?php endforeach;?>
	</ul>
</div>
<div style="clear: both"></div>
<div class="container">
	<h1>Tables in <span id="db_name"></span>
	<ul id="tables" class="horizontal" style="display: none;"></ul>
</div>
<div style="clear: both"></div>
<textarea id="query" cols="100" rows="20"></textarea>
<a href="javascript:void(0);" id="execute_link">execute!</a>
<div id="query_results"></div>


<script type="text/javascript">
	var links = [];
	var db_name = '';
	function onDbWasClicked(request){
		SDDom('tables').innerHTML = request.responseText;
		tablesViewWillLoad(null);
	}
	function dbDidClick(e){
		db_name = this.text;
		(new SDAjax({method: 'get', parameters: 'db_name=' + db_name
			, DONE: [window, onDbWasClicked]})).send('<?php echo FrontController::urlFor('tables');?>');
		
		SDArray.each(SDDom.findAll('ul.horizontal li'), function(li){
			li.className = 'closed';
		});
		e.target.parentNode.className = 'opened';
	}
	function tablesViewWillLoad(responseTree, responseElements, responseHTML, responseJavaScript){
		SDDom.setStyles({"display":"block"}, SDDom('tables'));
		SDDom('db_name').innerHTML = db_name;
		tablesViewDidLoad(null);
	}
	function tablesViewDidLoad(elem){
		addObserverToTableLinks();
	}
	function addObserverToTableLinks(){
		SDArray.each(SDDom.findAll('a.delete'), function(link){
			SDDom.addEventListener(link, 'click', deleteTableWasClicked);
		});
	}
	function removeObserverFromTableLinks(){
		SDArray.each(SDDom.findAll('a.delete'), function(link){
			SDDom.removeEventListener(link, 'click', deleteTableWasClicked);
		});
	}
	function onDeleteTable(request){
		SDDom('tables').innerHTML = request.responseText;
		tablesViewDidLoad(null);
	}
	function deleteTableWasClicked(e){
		removeObserverFromTableLinks();
		var table_name = e.target.getAttribute('table');
		var form_to_submit = e.target.parentNode;
		if(confirm('Are you sure you want to delete ' + table_name + '?')){
			
			(new SDAjax({method: 'post', parameters: SDDom.toQueryString(form_to_submit)
				, DONE: [window, onDeleteTable]})).send('<?php echo FrontController::urlFor('table');?>');

		}
		return false;
	}
	function databasesViewDidLoad(){
		SDDom('navigation_controller_header').innerHTML = 'Databases';
	}
	function onQueryDidExecute(request){
		SDDom('query_results').innerHTML = request.responseText;
	}
	function executeLinkWasClicked(e){
		var query = SDDom('query').value;
		(new SDAjax({method: 'post', parameters: 'db_name=' + db_name + '&query=' + query
			, DONE: [window, onQueryDidExecute]})).send('<?php echo FrontController::urlFor('query');?>');
		
		return false;
	}
	SDDom.addEventListener(window, 'load', function(){
		var original = {};
		var extended = {};
		links = SDDom.findAll('a.database');
		SDArray.each(links, function(a){
			SDDom.addEventListener(a, 'click', dbDidClick);
		});
		SDDom.addEventListener(SDDom('execute_link'), 'click', executeLinkWasClicked);
	});
</script>