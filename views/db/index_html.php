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
<div id="query" class="query" contentEditable="true"></div>
<a href="javascript:void(0);" id="execute_link">execute!</a>
<div id="query_results"></div>


<script type="text/javascript">
	var links = [];
	var db_name = '';
	function dbDidClick(e){
		db_name = this.text;
		new Request.HTML({update:'tables', url:'<?php echo FrontController::urlFor('tables');?>', onSuccess:tablesViewWillLoad}).get({db_name:this.text});
		$$('ul.horizontal li').each(function(li){
			li.className = 'closed';
		});
		this.getParent().className = 'opened';
	}
	function tablesViewWillLoad(responseTree, responseElements, responseHTML, responseJavaScript){
		$('tables').setStyle('display', 'block');
		$('db_name').set('html', db_name);
		tablesViewDidLoad(null);
	}
	function tablesViewDidLoad(elem){
		addObserverToTableLinks();
	}
	function addObserverToTableLinks(){
		$$('a.delete').each(function(link){
			link.addEvent('click', deleteTableWasClicked);
		});
	}
	function removeObserverFromTableLinks(){
		$$('a.delete').each(function(link){
			link.removeEvent('click', deleteTableWasClicked);
		});
	}
	function deleteTableWasClicked(e){
		removeObserverFromTableLinks();
		var table_name = $(e.target).get('table');
		var form_to_submit = e.target.parentNode;
		if(confirm('Are you sure you want to delete ' + table_name + '?')){
			var request = new Request.HTML({url:'<?php echo FrontController::urlFor('table');?>', update:'tables', onSuccess: tablesViewDidLoad}).post($(form_to_submit));
		}
		return false;
	}
	function databasesViewDidLoad(){
		$('navigation_controller_header').set('html', 'Databases');
	}
	function backWasClicked(e){
		new Fx.Tween('databases_column', {onComplete: databasesViewDidLoad}).start('margin-left', -320, 0);
		return false;
	}
	function executeLinkWasClicked(e){
		var query = $('query').get('html');
		var request = new Request.HTML({url:'<?php echo FrontController::urlFor('query');?>', update: 'query_results', data:'db_name=' + db_name + '&query=' + query});
		request.send();
		return false;
	}
	window.addEvent('domready', function(){
		var original = {};
		var extended = {};
		var test = $extend(original, extended);
		links = $$('a.database');
		links.each(function(a){
			a.addEvent('click', dbDidClick);
		});
		
		$('execute_link').addEvent('click', executeLinkWasClicked);
	});
</script>