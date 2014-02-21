$(document).ready(function(){

	document.addEventListener("touchstart", function(){}, true);
	
    var $title = $("#title");

    var listID = $title.attr('data-id');

    var userID = $title.attr('data-user_id');

    var $saveIndicator = $('#save_indicator');
    
    var $item_template = $('#item-template');
	
	/*
	*
	* Show Share
	*
	*/
	
	$('.share').on('mouseover', function(e){
	
		$(this).addClass('move-right');	
		$('.share-buttons ul li').addClass('move-left');
		
	});
		
	
	/*
	*
	* Show Save
	*
	*/
	function show_save(msg){

		$saveIndicator.fadeIn().text(msg);

	}
	
	/*
	*
	* Hide Save
	*
	*/
	function hide_save(){

		setTimeout(function(){
				$saveIndicator.fadeOut();
			}, 1200)	
	}
	
	
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
		  url: '/list/new_item/'+listID,
		  data : {},
		  success: function(data){

              var dataID = data.id;
              
              $item_template.find('.item').attr('data-id', dataID);

              $('ol', '.list').append($item_template.html());
              
              reorder_list_items()

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

        var $container = $this.parents('li');
        var listID = $container.attr('data-id');

        var url = '/list/remove/'+listID;
        
        var confirmation = confirm("Are you sure you want to delete this list?");

		if(confirmation == true){
					
			$.ajax({
			  type: "POST",
			  url: url,
			  data : {},
			  success: function(data){
				  $container.fadeOut();
                  $container.remove();
                  show_save('List deleted.');
	              hide_save()
			  }
	
			});
			
		} else {

			show_save("That was a close one!");
			hide_save();

		}
	});
	
	/*
	*
	* Listen for stop in typing on items and then call save_list_item()
	*
	*
	*/
	$(document).on('keydown', '.list-item, #title, #subtitle, #img', function(e){
		show_save('Saving...')
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
				$('.bg-wrapper').css('background-image', 'url(' + imgURL + ')');
			} else {
				$('.bg-wrapper').css('background-image', 'none');
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
		
		show_save('Saved.');
		
		$.ajax({
		  type: "POST",
		  url: '/list/save/'+userID,
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
		
		hide_save();	
		
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

		show_save('Saved.');
		
		$.ajax({
		  type: "POST",
		  url: 'save_item/'+listID,
		  data: {
		  	'id': dataID,
		  	'title' : item,
		  	'description' : desc
		  },
		  success: function(){
		  }
		});	
		
		hide_save();	
	}

	/*
	*
	* Reorder the current list
	*
	*/
    function reorder_list_items()
    {
        $('.list-item').each(function(index, object) {

            $(this).find('.number').html(""+(index + 1));

        });
    }
	
	
	/* 
	*
	* Delete a list item and remove it from the DOM
	*
	*/
		
	$('.list').on('click', '.delete-list-item', function(e){

        e.preventDefault();
        
        $this = $(this);
        
        var itemID = $this.siblings('input.item').attr('data-id');
        var url = '/list/remove_item/'+itemID;
        
        var confirmation = confirm("Are you sure you want to delete this item?");

		if(confirmation == true){
		
			show_save("Item deleted.");
			
			$.ajax({
			  type: "POST",
			  url: url,
			  data : {},
			  success: function(data) {
	              $container = $this.parents('li');
				  $container.fadeOut();
	              $container.remove();
	
	               hide_save();
	
	              reorder_list_items();
			  }
	
			});	
		} else {
			show_save("Good choice!");
			hide_save();
		}	
	});
	

	/*
	*
	* Convert URLs in descriptions
	*
	*/
    /*
	function convert_urls(){
		
		descriptions = $(document).find('.description').each(function(){
			
			$this = $(this);
			
			
			if($this.is(':input')){
				return false;
			} else {
				text = $this.text().replace(/\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/gi, function(url){
									
		                var full_url = url;
		                if (!full_url.match('^https?:\/\/')) {
		                    full_url = 'http://' + full_url;
		                }
		                return '<a href="' + full_url + '">' + url + '</a>';
		            }
		        );  
			$this.html(text);	
			}
		});		
	}	

	convert_urls();
	*/
	
});