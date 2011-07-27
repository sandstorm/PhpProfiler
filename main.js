$(document).ready(function(){
	$('td').click(function() {
		$('table').addClass('highlightmode');
		$('td.input').removeClass('highlight');
		if ($(this).hasClass('input')) {
			var tdClassName = $(this).attr('class');
			tdClassName = tdClassName.replace('number input ', '');
			$('td.' + tdClassName).addClass('highlight');
		}
		var trClassName = $(this).parent().attr('class');
		$('tr').removeClass('highlight');
		$('tr.' + trClassName).addClass('highlight');
	});
	$('table').dblclick(function() {
		$('table').removeClass('highlightmode');
	});
});