$(document).ready(function(){
	
	
	/*
	*
	* Toggle Login and Signup
	*
	*/
	
	
	
	
	
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
	
		listCount++
			
		var dataID = 1234;
		  	
		  	var new_item = "<li class='list-item'><span class='number'>"+listCount+".</span><input type='text' placeholder='item' data-id='" + dataID + "'class='item' /><textarea placeholder='description' class='description'></textarea></li>";
		
		  	$('.list').append(new_item);
					
		/*$.ajax({
		  type: "POST",
		  url: 'list/new_item/1',
		  //data nothing
		  success: function(){
		  
		  	var dataID = data.id;
		  	
		  	var new_item = "<input type='text' class='item' data-id='" + dataID + "' placeholder='item'/>";
		
		  	$('.list').append(new_item);

		  },
		});*/
		
	});
	
	
	
	
	/*
	*
	* Listen for a key down and communicate saving
	*
	*/
	
	$(document).on('keydown', function(e){
			
			console.log("Saving....")
			//$('.saveIndicator').text("Saving...");
		
	})
	
	
	/*
	*
	* Listen for stop in typing on title and subtitle and then call save_list()
	*
	*/
	$('#title, #subtitle, #img').on('keyup', function(e){
		
		var dataID = $('#title').attr('data-id');
		
		var title = $('#title').val();
		
		var subtitle = $('#subtitle').val(); 
		
		var imgURL = $('#img').val();
		
			if(imgURL){
				$('.background').css('background-image', 'url(' + imgURL + ')');
			} else {
				$('.background').css('background-image', 'none');
			}
		
		clearTimeout($.data(this, 'timer'));
		
		$(this).data('timer', setTimeout(function(){
		
			save_list(dataID, title, subtitle, imgURL);
		
		}, 300));
		
		
	});
	

	
	/*
	*
	* Take list title and save
	*
	* Communicate saved
	*/
	function save_list(dataID, title, subtitle, imgURL){
		
		console.log("Saved! " + title + " with id " + dataID + " " + subtitle + " and image " + imgURL);
		//$('.saveIndicator').text("Saved.");
		
		
		$.ajax({
		  type: "POST",
		  url: 'save/1',
		  data: {
		  	'id': dataID,
		  	'title' : title,
		  	'subtitle' : subtitle,
		  	'imgageURL' : imgURL,
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
	
		var dataID = $this.find('.item').attr('data-id');
	
		var item = $this.find('.item').val();
		
		var desc = $this.find('.description').val();
				
		clearTimeout($.data(this, 'timer'));
		
		$(this).data('timer', setTimeout(function(){
		
			save_list_item(dataID, item, desc);
		
		}, 300));
		
	});
	
	
	/*
	*
	* Take list item and save
	*
	* Communicate saved
	*/
	function save_list_item(dataID, item, desc){
		
		console.log("Saved! " + item + " with id " + dataID + " " + desc);
		//$('.saveIndicator').text("Saved.");
		
		$.ajax({
		  type: "POST",
		  url: 'save_item/1',
		  data: {
		  	'id': dataID,
		  	'title' : item,
		  	'description' : desc,
		  },
		  success: function(){
		  	console.log(resp);
		  },
		});		
	}
	
	
	
})