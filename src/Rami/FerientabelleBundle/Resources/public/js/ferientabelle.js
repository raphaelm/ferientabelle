$(function(){
	var nowTemp = new Date();
	var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
	 
	var checkin = $('.dpfrom').datepicker({
		'format':'dd.mm.yyyy',
		'weekStart':1,
	  onRender: function(date) {
		return date.valueOf() < now.valueOf() ? 'disabled' : '';
	  }
	}).on('changeDate', function(ev) {
	  if (ev.date.valueOf() > checkout.date.valueOf()) {
		var newDate = new Date(ev.date)
		newDate.setDate(newDate.getDate());
		checkout.setValue(newDate);
	  }
	  checkin.hide();
	  $('.dpto')[0].focus();
	}).data('datepicker');
	var checkout = $('.dpto').datepicker({
		'format':'dd.mm.yyyy',
		'weekStart':1,
	  onRender: function(date) {
		return date.valueOf() < checkin.date.valueOf() ? 'disabled' : '';
	  }
	}).on('changeDate', function(ev) {
	  checkout.hide();
	}).data('datepicker');
	
	
	$('[data-toggle="tooltip"]').tooltip({'container':'.therow'});


	$('.tabbing a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	  location.href = $(this).attr("href");
	});
	$('a.gotoframes').click(function() {
		$('.nav-tabs a[href="#frames"]').tab('show');
	});
	if(location.hash.length > 0 && $('.nav-tabs a[href="'+location.hash+'"]').length > 0){
		$('.nav-tabs a[href="'+location.hash+'"]').tab('show');
	} else {
		$('.nav-tabs a:first').tab('show');
	}
	
	$(".confirmdeletefriendship").click(function(){
		return confirm("Diese Freundschaft wirklich entfernen?");
	});
});