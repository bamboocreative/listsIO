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
		$('.share-buttons ul li').addClass('move-left');
	})
	
	
	/* 
	*
	* Add a list item and save it to the DB
	*
	*/
	
	var listCount = $('.list-item').length;
	
	$('#add').on('click', function(e){

        e.preventDefault();

		listCount++;
					
		$.ajax({
		  type: "POST",
		  url: '/app_dev.php/list/new_item/'+listID,
		  data : {},
		  success: function(data){

              var dataID = data.id;

			  //move out to twig template
              var $new_item = $("<li class='list-item'><span class='number'>"+listCount+".</span><input type='text' placeholder='List item title' data-id='" + dataID + "' class='item' /><textarea placeholder='(Optional) A short description of this list item.' class='description'></textarea></li>");

              $('ol', '.list').append($new_item);

		  }

		});
	});
	
	/* 
	*
	* Delete a list and remove it from the DOM
	*
	*/
		
	$('.delete-list').on('click', function(e){

        e.preventDefault();
        
        $this = $(this);
        
        var listID = $this.parent().attr('data-id');
        
        //Add an alert "are you sure you want to delete
        $('#save_indicator').fadeIn().text("Saved.");
					
		$.ajax({
		  type: "POST",
		  url: '/app_dev.php/list/remove/'+listID,
		  data : {},
		  success: function(data){
			  
			  $this.parent().parent().fadeOut();
              //remove from DOM
              
               $('#save_indicator').fadeOut();
		  }

		});
	});
	
	/*
	*
	* Listen for stop in typing on items and then call save_list_item()
	*
	*
	*/
	$(document).on('keydown', '.list-item, #title, #subtitle, #img', function(e){
	
		$('#save_indicator').fadeIn().text("Saving...");
		
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
			} else {
				$('body').css('background-image', 'none');
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
	*/
	function save_list(userID, dataID, title, subtitle, imgURL){
		
		$('#save_indicator').text("Saved.");
		
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
		
		setTimeout(function(){
			$('#save_indicator').fadeOut();
		}, 1200)	
		
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
	*/
	function save_list_item(listID, dataID, item, desc){

		$('#save_indicator').text("Saved.");
		
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
		
		setTimeout(function(){
			$('#save_indicator').fadeOut();
		}, 1200)	
	}
	
	
	/* 
	*
	* Delete a list item and remove it from the DOM
	*
	*/
		
	$('.list-item').on('click', '.delete-list-item', function(e){

        e.preventDefault();
        
        $this = $(this);
        
        var itemID = $this.parent().find('.item').attr('data-id');
        
        //Add an alert "are you sure you want to delete
        $('#save_indicator').fadeIn().text("Saved.");
					
		$.ajax({
		  type: "POST",
		  url: '/app_dev.php/list/remove_item/'+itemID,
		  data : {},
		  success: function(data){
			  
			  $this.parent().fadeOut();
              //remove from DOM and reshuffle list-item numbers
              
               $('#save_indicator').fadeOut();
		  }
		  

		});
	});
	
});