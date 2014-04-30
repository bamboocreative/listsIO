$(document).ready(function(){

	document.addEventListener("touchstart", function(){}, true);

    var $list = $(".editable-list");

    var listID = $list.attr('data-id');

    var userID = $list.attr('data-user_id');

    var $saveIndicator = $('#save_indicator');
    
    var $item_template = $('#item-template');

    var list = document.getElementById('editable-list-'+listID);

    var saving_msg = "Saving...";
    var saved_msg = "Saved.";

    if (list) {
        var sortableList = new Sortable(list, {
            handle: ".number", // Restricts sort start click/touch to the specified element
            ghostClass: "dragging",
            onUpdate: function (evt){
                reorder_list_items();
            }
        });
    }

    $(document).ready(function(){
        $('textarea.description', '.editable-list').autosize();
    });
	
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
		}, 1200);

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

              var $template = $item_template.clone();
              var $item = $template.find('li.list-item');
              var $number = $item.find('.number');
              var $description = $item.find('.description');
              $description.autosize();
              $number.html(data.orderIndex);
              $item.attr('data-id', data.id)
              $item.attr('data-order_index', data.orderIndex);
              $('.editable-list').append($item);

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
	* Listen for stop in typing on title and subtitle and then call save_list()
	*
	*/
	$('.edit-list-head-wrapper').on('keyup', function(e) {

        var $this = $(this);

		var title = $this.find('.list-title').val();
		
		var subtitle = $this.find('.list-subtitle').val();
		
		var imgURL = $this.find('.list-img').val();
		
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
		
		show_save(saving_msg);

		$.ajax({

		  type: "POST",
		  url: '/list/save',
		  data: {
		  	'id': dataID,
		  	'title' : title,
		  	'subtitle' : subtitle,
		  	'imageURL' : imgURL
		  },
		  success: function(data) {

              show_save(saved_msg);
              hide_save();

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

        var $this = $(this);

		clearTimeout($.data(this, 'timer'));
		$(this).data('timer', setTimeout(function(){

            save_list_item(listID, $this);

        }, 300));
		
	});

    $('.list').on('keyup', '.description', function(e) {
        $(this).trigger('autosize.resize');
    } );
	
	
	/*
	*
	* Take list item and save
	*
	* Communicate saved
	*
	*/
	function save_list_item(listID, $item){

        var title = $item.find('.item').val();

        var dataID = $item.attr('data-id');

        var desc = $item.find('.description').val();

        var orderIndex = $item.attr('data-order_index');

		show_save(saving_msg);
		
		$.ajax({
		  type: "POST",
		  url: 'save_item/'+listID,
		  data: {
		  	'id': dataID,
		  	'title' : title,
		  	'description' : desc,
            'orderIndex' : orderIndex
		  },
		  success: function(data){
              show_save(saved_msg);
              hide_save();
		  }
		});	

	}

	/*
	*
	* Reorder the current list
	*
	*/
    function reorder_list_items()
    {
        $('.list-item', '.editable-list').each(function(index, object) {

            var $this = $(this);
            var newNum = index + 1;
            var oldNum = $this.attr('data-order_index');
            if (oldNum != newNum) {
                $this.find('.number').html(""+newNum);
                $this.attr('data-order_index', newNum);
                save_list_item(listID, $this);
            }

        });
    }
	
	
	/* 
	*
	* Delete a list item and remove it from the DOM
	*
	*/
		
	$('.list').on('click', '.delete-list-item', function(e){

        e.preventDefault();

        $container = $(this).parents('li.list-item');
        
        var itemID = $container.attr('data-id');
        var url = '/list/remove_item/'+itemID;
        
        var confirmation = confirm("Are you sure you want to delete this item?");

		if(confirmation == true){
		
			show_save("Deleting item...");
			
			$.ajax({
			  type: "POST",
			  url: url,
			  data : {},
			  success: function(data) {
				  $container.fadeOut();
                  $container.remove();
                  reorder_list_items();

                  show_save("Item deleted.");
                  hide_save();
			  }
			});
		} else {
			show_save("Good choice!");
			hide_save();
		}	
	});
	
	
	/* 
	*
	* Search
	*
	*/
	$peopleResults = $('#people-results ul');
	$listResults = $('#list-results ul');
	$search =  $('#search');
	
	/*
	*
	* Listen for stop in typing on search
	*
	*
	*/
	
	$search.on('keyup', function(e){

        var $this = $(this);
        var keyword = $search.val();
        
		if( !$search.val() ){
			$peopleResults.empty();
			$listResults.empty();
			return;
		}

		clearTimeout($.data(this, 'timer'));
		$(this).data('timer', setTimeout(function(){
			
			$peopleResults.empty();
			$listResults.empty();

	        $.ajax({
				url: 'http://localhost:8888/search/find?all=' + keyword,
				type: 'GET',
			}).done(function(response){
				console.log(response);
				var users = response.users;
				var lists = response.lists;
				
				if(!users.length && !lists.length){
					var html = "<li><h3>Nothing Found...</h3></li>";
					$listResults.append(html);
				} else {
									
					for(i = 0; i < users.length && i < 6; i++){
						var user = users[i];
						search_print_user(user, false);
					}
					
					for(i = 0; i < lists.length && i < 12; i++){
						var list = lists[i];
						search_print_list(list, false);
					}
				}
				
			})

        }, 300));
		
	});
	
	function search_print_user(user, empty){
		var html = "<li> <a href='/" + user.username + "'><img src='" + user.gravatarURL +"'/>  <h3>" + user.username + "</h3> </a> </li";
		$peopleResults.append(html);
	}
	
	function search_print_list(list){
		var html = "<li> <a href='/list/" + list.id + "'> <h3>" + list.title + "</h3> <p>" + list.subtitle + "</p> </a> </li>";
		$listResults.append(html);
	}
	
});