$(document).ready(function() {
	// make rel="external" links open in a new window
	$("a[rel='external']").attr('target', '_blank');
	$(".main").css('min-height', $("nav").height() - 34);
	$(".dboarditem:last").css('min-height', $(".dboarditem:first").height());
	$(".dboarditem:first").css('min-height', $(".dboarditem:last").height());

	// set focus on username-field if on loginpage
	if ($(".loginpage").length != 0) {
		$("#loginname").focus();
	}

	// Auto-select next field in configfile - wizard
	$('#config_distribution').change(function (){
		window.location.href=window.location.href + '&distribution=' + this.options[ this.selectedIndex ].value;
	});
	$('#config_service').change(function (){
		window.location.href=window.location.href + '&service=' + this.options[ this.selectedIndex ].value;
	});
	$('#config_daemon').change(function (){
		window.location.href=window.location.href + '&daemon=' + this.options[ this.selectedIndex ].value;
	});

	// Back-button
	$('#yesnobutton').click(function() {
		history.back();
	});
	$(".tablesorter").tablesorter({widgets: ['zebra']})
	.tablesorterPager({container: $("#pager"), positionFixed: false})
	.tablesorterFilter({
		filterContainer: $("#filter-box"),
		filterClearContainer: $("#filter-clear-button"),
		filterCaseSensitive: false,
	});
});
