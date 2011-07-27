$(document).ready(function(){
	$('td').click(function() {
		if ($(this).hasClass('input')) {
			var tdClassName = $(this).attr('class');
			tdClassName = tdClassName.replace('number input ', '');
			$('td.input').removeClass('highlight');
			$('td.' + tdClassName).addClass('highlight');
		}
		var trClassName = $(this).parent().attr('class');
		$('tr').removeClass('highlight');
		$('tr.' + trClassName).addClass('highlight');
	});
	$('table').mouseover(function() {
		$('table').addClass('highlightmode');
	});
	$('table').mouseout(function() {
		$('table').removeClass('highlightmode');
	});
});