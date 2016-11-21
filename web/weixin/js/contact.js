jQuery(document).ready(function($) {
	function createNumber(data) {
		$('.numberList').html();
		var $html = '<tr  style="font-weight:bold"><td>#</td><td>number</td><td>password</td></tr>';
		var len = data.numbers.length;
		for (var i = 0; i < len; i++) {
			$html += '<tr><td>'+data.numbers[i].id+'</td><td>'+data.numbers[i].number+'</td><td>'+data.numbers[i].pwd+'</td></tr>';
		}
		$('#numberList').html($html);
	}
	
	var order = {
		init:function(){
			this.numberList();	
		},
		numberList:function(){
			$('#contactSubmitButton').on('click',function(){
				var data = {};
				data.number   = $('#exampleInputEmail').val();
				data.password = $('#exampleInputPassword').val();
				data._csrf = $('#_csrf').val();
				var url = "/collectiveWeiXin/ofo-bicycle/add";
				order.postAjax(url,data);
			})
		},

		/*getAjax:function(url,fn){
			$.ajax({
				url:url,
				dataType: 'json',
				type: 'get',
				success: function(data,status,xhr){
					fn(data,status,xhr);
				}
			})
		},*/

		postAjax:function(url,data){
			$.ajax({
				url:url,
				dataType: 'json',
				async : false,
				type: 'post',
				data:data,
				success: function(data,status,xhr){
					console.log(data);
					if (data.result == 'ok') {
						createNumber(data);
						// return location.href = "<?php echo U('home/index/info') ;?>";
					}
				}
			});
		},
	}
	window.onload = function() {
		order.init();
		$('#contactSubmitButton').trigger('click');
	}
});
