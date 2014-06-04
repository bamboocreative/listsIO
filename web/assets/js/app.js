$(document).ready(function(){

	document.addEventListener("touchstart", function(){}, true);
	
	var $body = $('body');
	
	var $siteWrapper = $('#site_wrapper');
	
	var $loader = $('.loader');

    var $list = $(".editable-list, .non-editable-list");

    var listID = $list.attr('data-id');

    var userID = $('body').attr('data-user_id');

    var $saveIndicator = $('#save_indicator');

    var $statusIndicator = $('#status_indicator');
    
    var $item_template = $('#item-template');

    var list = document.getElementById('editable-list-'+listID);
    
    var $sidebar = $('.sidebar');
	
	var $sidebarbtn = $('.logo');
	
	var $share = $('.share');
	
	var $shareBtns = $('.share-buttons ul li');

    var saving_msg = "Saving...";
    var saved_msg = "Saved.";
    var error_msg = "Please try again later.";
    var login_to_like_msg = 'Please <a href="/register">sign up</a> or <a href="/login">login</a> to like.';

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
	* Toggle Register Form
	*
	*/
	$('#register-form-button').on('click', function(e){
		
		e.preventDefault();
		
		$button = $(this);
		
		$button.fadeOut();
		
		$form = $('#register-form');
		
		$form.slideDown();
		
		$( document ).on( 'click', function(e){
			
			var target = $(e.target)
			
			if( !target.parents('#register-form').length && target.attr('id') != 'register-form-button' ){
			
					$form.slideUp();
					$button.fadeIn();
			}
		});
	});
	
	 /*
	*
	* Sidebar
	*
	*/
	bodyClick = function(e){
		
		var $target = $( e.target );
				
		//Check if the sidebar is open and if so hide it		
		if ( ! $target.is( $sidebar ) && ! $target.parents().is( $sidebar )) {
			$body.removeClass('sidebar-transition-open').addClass('sidebar-transition-close');
			$siteWrapper.removeClass('sidebar-open');
			
			//Remove the bodyClick listener
			$siteWrapper.off( 'click', bodyClick);
			
			setTimeout(function(){
				$this.fadeIn();
			}, 500)
		};
	};
	 
	$sidebarbtn.on('click', function(e){
		
		$this = $(this);
		$this.hide();
		$body.addClass('sidebar-transition-open');
		$siteWrapper.addClass('sidebar-open');
		
		//Attach a listener to close this
		$siteWrapper.on( 'click', bodyClick);
		
	});
    
	
	/*
	*
	* Show Share
	*
	*/
	
	$share.on('click', function(e){
					
		$share.removeClass('move-left').addClass('move-right');	
		$shareBtns.removeClass('move-right').addClass('move-left');
		
		//Attach a listener to close this
		$siteWrapper.on( 'click' , function(e){;
			
			var $target = $( e.target );
			
			//Check if we are showing share buttons and if so hide them
			if ( ! $target.is( $shareBtns ) && ! $target.parents().is( '.share-wrapper' ) ){
				$share.removeClass('move-right').addClass('move-left');
				$shareBtns.removeClass('move-left').addClass('move-right');
				
				//Remove the bodyClick listener
				$siteWrapper.off( 'click', bodyClick);
			};
		})
		
	});

    $('.like-btn').on('click', function(e) {
        e.preventDefault();
        var $this = $(this);

        show_save('Liking...');
        $.ajax({
            type: "POST",
            url: '/list/like',
            data : {
                listId: listID
            },
            success: function(data, textStatus, jqXHR){
                // 201 indicates new like created.
                // Otherwise = redirect to login (user is not logged in).
                if (jqXHR.status == 201) {
                    $this.removeClass('not-liked').addClass('liked');
                    show_save("Liked.");
                    hide_save();
                } else {
                    hide_save();
                    show_status(login_to_like_msg);
                    hide_status();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // 409 indicates like already exists.
                if (jqXHR.status == 409) {
                    $this.addClass('liked');
                    show_status("Already liked.");
                } else {
                    show_status(error_msg);
                }
                hide_save();
                hide_status();
            }
        });
    });
	
	/*
	*
	* Show Save
	*
	*/
	function show_save(msg)
    {
		$saveIndicator.fadeIn().html(msg);

	}

    function show_status(msg)
    {
        $statusIndicator.fadeIn().html(msg);
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

    function hide_status()
    {
        setTimeout(function(){
            $statusIndicator.fadeOut();
        }, 3000);
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
              console.log(data);
              var $template = $item_template.clone();
              var $item = $template.find('li.list-item');
              var $number = $item.find('.number');
              var $description = $item.find('.description');
              $description.autosize();
              $number.html(data.order_index);
              $item.attr('data-id', data.id)
              $item.attr('data-order_index', data.order_index);
              $('.editable-list').append($item);
              $item.find('.item').focus();
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
            $('.mobile-background').css('background-image', 'url(' + imgURL + ')');
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
	$userResults = $('#user-results ul');
	$listResults = $('#list-results ul');
	$search =  $('#search');
	
	
	
	
	/*
	*
	* Listen for stop in typing on search and then search
	*
	*
	*/
	$search.on('keyup', function(e){

        var $this = $(this);
        var keyword = $search.val();
        
		if( !$search.val() ){
			$userResults.empty();
			$listResults.empty();
			return;
		}

		clearTimeout($.data(this, 'timer'));
		$(this).data('timer', setTimeout(function(){
			
			$userResults.empty();
			$listResults.empty();

	        $.ajax({
				url: '/search/query?all=' + keyword,
				type: 'GET',
			}).done(function(response){

				var users = response.users;
				var lists = response.lists;
				
				var usersEmpty = false;
				var listsEmpty = false;
				
				if(!users.length && !lists.length){
					var html = "<li><h3 class='nothing-found'>Nothing Found...</h3></li>";
					$listResults.append(html);
					return;
				} 
				
				if (!users.length){
					var usersEmpty = true;
					search_print_user(user, usersEmpty);
					
				} else {
					for(i = 0; i < users.length && i < 6; i++){
						var user = users[i];
						search_print_user(user, usersEmpty);
					}
				}
				
				
				if (!lists.length){
					var listsEmpty = true;
					search_print_list(list, listsEmpty);
				} else {
					for(i = 0; i < lists.length && i < 8; i++){
						var list = lists[i];
						search_print_list(list, listsEmpty);
					}
				}
																	
			})

        }, 300));
		
	});
	
	
	/*
	*
	*
	* Take the user and print it to the search results. If it is empty show nothing found message
	*
	*/
	function search_print_user(user, empty){
		if(empty){
			var html = "<li><p class='nothing-found'>No user found...</p></li>";
		} else {
			var html = "<li> <a href='/" + user.username + "'><div class='search-user'><img src='" + user.gravatarURL +"'/>  <span class='search-user-username'>" + user.username + "</span> </div> </a> </li";
		}
		$userResults.append(html);
	}
	
	
	/*
	*
	*
	* Take the list and print it to the search results. If it is empty show nothing found message
	*
	*/
	function search_print_list(list, empty){
		if(empty){
			var html = "<li><p class='nothing-found'>No lists found...</p></li>";
		} else {
			var html = "<li> <a href='/list/" + list.id + "'> <div class='search-list'> <h3 class='search-title'>" + list.title + "</h3> <p class='search-subtitle'>" + list.subtitle + "</p></a> </div> <a href=/" + list.user.username + "><div class='search-list-profile'><img src='" + list.user.gravatarURL + "' /><span class='search-list-username'>by " + list.user.username + "</span> </div> </a></li>";
		}
		
		$listResults.append(html);
	}
	
	
	/* 
	*
	* Feed
	*
	*/
	var $feedInsert = $('#feed-insert');
	var feedLoading = false;	
	
	/*
	*
	*
	* Listen to the feed scroll and if we hit the bottom lets load more feed
	*
	*/
	var timer;
	
	$( document ).on('scroll', function(e){
		
		clearTimeout(timer);
		
		if(!$body.hasClass('disable-hover')) {
			$body.addClass('disable-hover')
		}
		
		timer = setTimeout(function(){
			$body.removeClass('disable-hover')
		},200);
		
		var height = $(document).height();
		var scrollBottom = $(window).scrollTop() + $(window).height();
		
		if(feedLoading == false && scrollBottom == height ){
			
			feedloading = true;
			
			$loader.fadeIn(200);
			
			var last = $('.feed-item-count').last().attr('data-id');
					
			$.ajax({
				url: '/feed/next',
				type: 'GET',
				data: {
					'cursor' : last
				}
			}).done(function(response){
								
				var feedLists = response.lists;
				
				if (feedLists == false){
				
					feedLoading = true;
					
					$('#feed-cta').show()
					
					setTimeout(function(){
						$loader.fadeOut(200);
					},800);
									
				} else{
				
					showFeedNext(feedLists, function(){
						console.log('he')
						setTimeout(function(){
							$loader.fadeOut(200);
						},800);
						feedLoading = false;
					});
				}
				
			})
		}	
	});
	
	/*
	*
	*
	* Print out the next 21 items returned from the feed.
	*
	*/
	function showFeedNext(feedLists, callback) {
	
		for (i = 0; i < feedLists.length; i++){
					
			print(feedLists[i]);
			
		}
		
		callback();
		
		function print(feedList){
			var html = '<div data-id="'+feedList.id+'" onclick="location.href=\'/list/'+feedList.id+'\'" class="col-xs-12 col-sm-12 col-md-12 col-lg-12 feed-item-wrapper feed-item-count" style="background-image: url('+feedList.imageURL+')"> <div class="feed-item-overlay"> <div class="feed-item"> <h3 class="feed-item-title">'+feedList.title+'</h3> <a href="/'+feedList.user.username+'"> <div class="feed-item-profile"> <img src="'+feedList.user.gravatarURL+'" /> <span class="feed-item-username">'+feedList.user.username+'</span> </div> </a> </div> </div> </div>';
		
			$feedInsert.append(html);
		}
		
		
	}
	
});
