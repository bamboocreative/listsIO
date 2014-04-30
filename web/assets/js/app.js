$(document).ready(function(){

	document.addEventListener("touchstart", function(){}, true);

    var $list = $(".editable-list, .non-editable-list");

    var listID = $list.attr('data-id');

    var userID = $('body').attr('data-user_id');

    var $saveIndicator = $('#save_indicator');
    
    var $item_template = $('#item-template');

    var list = document.getElementById('editable-list-'+listID);

    var saving_msg = "Saving...";
    var saved_msg = "Saved.";
    var error_msg = "Please try again later.";

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

    $('.like-btn').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);
        if ($this.hasClass('liked')) {
            return false;
        }
        show_save('Liking...');
        $.ajax({
            type: "POST",
            url: '/list/like',
            data : {
                listId: listID
            },
            success: function(data){
                console.log(data);
                $this.removeClass('not-liked').addClass('liked');
                show_save("Liked.");
                hide_save();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                if (jqXHR.status == 403) {
                    show_save('Please <a href="/register">Sign up</a> or <a href="/login">login</a> to like.');
                } else {
                    show_save(error_msg);
                }
                hide_save();
            }
        });
    });
		
	
	/*
	*
	* Show Save
	*
	*/
	function show_save(msg){

		$saveIndicator.fadeIn().html(msg);

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
	
});