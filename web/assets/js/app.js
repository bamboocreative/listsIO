$(document).ready(function(){


    var $title = $("#title");

    var listID = $title.attr('data-id');

    var userID = $title.attr('data-user_id');
	
	/*
	*
	* Show Share
	*
	*/
	
	$('.share').on('click', function(){
	
		console.log("hello");
		$(this).addClass('move-right');	
		$('.share-buttons ul li').addClass('move-left')	
	})
	
	
	/* 
	*
	* Add a list item and save it to the DB
	*
	*/
	
	var listCount = 1;
	
	$('#add').on('click', function(e){
	
		listCount++;
					
		$.ajax({
		  type: "POST",
		  url: '/app_dev.php/list/new_item/'+listID,
		  data : {},
		  success: function(data){
		  
		  	var dataID = data.id;
		  	
		  	var $new_item = $("<li class='list-item'><span class='number'>"+listCount+".</span><input type='text' placeholder='item' data-id='" + dataID + "'class='item' /><textarea placeholder='description' class='description'></textarea></li>");
		
		  	$('.list').append($new_item);

		  }

		});
	});
	
	
	
	
	/*
	*
	* Listen for a key down and communicate saving
	*
	*/
	
	$(document).on('keydown', function(e){
			
			console.log("Saving....");
			//$('.saveIndicator').text("Saving...");
		
	});
	
	
	/*
	*
	* Listen for stop in typing on title and subtitle and then call save_list()
	*
	*/
	$('#title, #subtitle, #img').on('keyup', function(e) {
		
		var title = $title.val();
		
		var subtitle = $('#subtitle').val(); 
		
		var imgURL = $('#img').val();
		
			if(imgURL){
				$('body').css('background-image', 'url(' + imgURL + ')');
			}
		
		clearTimeout($.data(this, 'timer'));
		
		$(this).data('timer', setTimeout(function(){
		
			save_list(userID, listID, title, subtitle, imgURL);
		
		}, 300));
		
		
	});
	

	
	/*
	*
	* Take list title and save
	*
	* Communicate saved
	*
	* Using 1 as a test id should grab 1 from dataID
	*/
	function save_list(userID, dataID, title, subtitle, imgURL){
		
		console.log("Saved! " + title + " with id " + dataID + " " + subtitle + " and image " + imgURL);
		//$('.saveIndicator').text("Saved.");
		
		
		$.ajax({
		  type: "POST",
		  url: '/app_dev.php/list/save/'+userID,
		  data: {
		  	'id': dataID,
		  	'title' : title,
		  	'subtitle' : subtitle,
		  	'imageURL' : imgURL
		  },
		  success: function(){
		  	console.log(resp);
		  }
		});
	}
	
	
	
	
	/*
	*
	* Listen for stop in typing on items and then call save_list_item()
	*
	*
	*/
	$('.list').on('keyup', '.list-item', function(e){
	
		$this = $(this);

        $item = $this.find('.item');
	
		var dataID = $item.attr('data-id');
	
		var item = $item.val();
		
		var desc = $this.find('.description').val();
				
		clearTimeout($.data(this, 'timer'));
		
		$(this).data('timer', setTimeout(function(){
		
			save_list_item(listID, dataID, item, desc);
		
		}, 300));
		
	});
	
	
	/*
	*
	* Take list item and save
	*
	* Communicate saved
	*
	* Using 1 as a test id should grab 1 from dataID
	*/
	function save_list_item(listID, dataID, item, desc){
		
		console.log("Saved! " + item + " with id " + dataID + " " + desc);
		//$('.saveIndicator').text("Saved.");
		
		$.ajax({
		  type: "POST",
		  url: 'save_item/'+listID,
		  data: {
		  	'id': dataID,
		  	'title' : item,
		  	'description' : desc
		  },
		  success: function(){
		  	console.log(resp);
		  }
		});		
	}
	
	
	
});