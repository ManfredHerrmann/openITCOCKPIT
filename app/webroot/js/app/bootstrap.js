/**
 * Make our Types available directly in the App namespace
 */
if(typeof appData.Types != 'object') {
	appData.Types = {};
}
App.Types = appData.Types;
App.Helpers = {};
App.ModuleController = {};

function __(msg) {
	if(typeof appData.jsonData.localeStrings == 'object' && typeof appData.jsonData.localeStrings[ msg ] != 'undefined') {
		return appData.jsonData.localeStrings[ msg ];
	} else {
		return msg;
	}
}

function debug(){
	return console.log.apply(console, Array.prototype.slice.call(arguments));
}


$(document).ready(function(){
	//Fix left-panel height
	$('#left-panel').css('height', parseInt($(document).innerHeight())+'px');

	//Fix drop down issue in mobile tables
	//$('.dropdown-toggle').off();

	/*
	 * Set an id for all the drop-down menus
	 */
	$('.dropdown-menu').each(function(key, object){
		$(object).attr('id',(Math.floor(Math.random() * (100000000 - 1)) + 1));
	});

	$('table .dropdown-toggle').click(function (){
		//This is hacky shit and need to get frefactored ASAP!!

		if($('#uglyDropdownMenuHack').html() != ''){
			//Avoid that the menu distry it self if the user press twice on the 'open menu' arrow
			return false;
		}

		var $ul = $(this).next('ul');
		//$ul.hide();
		var offset = $(this).offset(),
			right = $('body').width() - 26 - parseInt(offset.left);

		$('#uglyDropdownMenuHack').attr('sourceId', $ul.attr('id'));
		$('#uglyDropdownMenuHack').html($ul.clone().attr('id', 'foobarclonezilla'));

		//Remove orginal element for postLinks (duplicate form is bad)
		$ul.html('');

		if($ul.hasClass( "pull-right" )){
			$('#uglyDropdownMenuHack')
				.children('ul')
					.addClass('animated flipInX')
					.show()
					.css({
						'position': 'absolute',
						'top': parseInt(offset.top + 20)+'px',
						'left': 'auto',
						'right': right,
						'animation-duration': '0.4s'
					});
		}else{
			$('#uglyDropdownMenuHack')
			.children('ul')
				.addClass('animated flipInX')
				.show()
				.css({
					'position': 'absolute',
					'top': parseInt(offset.top + 20)+'px',
					'left': parseInt(offset.left - 20)+'px',
					'animation-duration': '0.4s'
				});
		}
	});


	$(document).on('hidden.bs.dropdown', function(){
		//Restore orginal menu content
		$('#'+$('#uglyDropdownMenuHack').attr('sourceId')).html($('#foobarclonezilla').html());
		$('#uglyDropdownMenuHack').html('');
	});

	jQuery.ajax({
		url: "https://project.it-novum.com/s/2527aba8089321056587b3d39dfb83e1-T/en_US-kerfqg/64026/9/1.4.27/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector-embededjs/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector-embededjs.js?locale=en-US&collectorId=c71ccc45",
		type: "get",
		cache: true,
		dataType: "script"
	});

});
