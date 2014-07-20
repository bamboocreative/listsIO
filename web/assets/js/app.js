$(document).ready(function () {

  document.addEventListener("touchstart", function () {
  }, true);

  var $body = $('body');

  var $siteWrapper = $('#site_wrapper');

  var $loader = $('.loader');

  var $list = $(".editable-list, .non-editable-list");

  var listID = $list.attr('data-id');

  var userID = $('body').attr('data-user_id');

  var $saveIndicator = $('#save_indicator');

  var $statusIndicator = $('#status_indicator');

  var $item_template = $('#item-template');

  var list = document.getElementById('editable-list-' + listID);

  var $sidebar = $('.sidebar');

  var $sidebarbtn = $('.logo');

  var $share = $('.share');

  var $shareBtns = $('.share-buttons ul li');

  var saving_msg = "Saving...";
  var saved_msg = "Saved.";
  var error_msg = "Please try again later.";
  var login_to_like_msg = 'Please <a href="/register">sign up</a> or <a href="/login">login</a> to like.';
  var login_to_follow_msg = 'Please <a href="/register">sign up</a> or <a href="/login">login</a> to follow.';

  /** GEOLOCATION VARS **/
  var locationApiKey = 'AnLymyMeoSzSTiPis2W3kL0fW95ewe-LlrNXANkqd_TLorg8tTIYMdtV3v66h3xl';
  var position = false;
  var locString = false;
  var $geolocationBtn = $('button.geolocation');
  var $location = $('.edit-list-location');
  var NORMAL_STATUS = 'normal';
  var IMPORTANT_STATUS = 'important';
  var ERROR_STATUS = 'error';
  // Attach nearby lists callback.
  $('.nearby-lists-tab').data('async_load_func', loadNearbyLists);


  /** AUTOSIZE LIST ITEM DESCRIPTIONS **/
  $('textarea.description', '.editable-list').autosize();

  /** SORTABLE LIST ITEMS **/
  if (list) {
    new Sortable(list, {
      handle: ".number", // Restricts sort start click/touch to the specified element
      ghostClass: "dragging",
      onUpdate: function (evt) {
        reorder_list_items();
      }
    });
  }

  /** FOLLOW **/
  $('.follow-button').on('click', function(e) {
    var $this = $(this);
    $this.addClass('loading');
    var followedId = $this.attr('data-followed_id');
    if ($this.hasClass('following')) {
      unfollow($this, followedId);
    } else {
      follow($this, followedId);
    }
  });

  function follow($element, followedId)
  {
    $.ajax({
      url: '/follow',
      type: 'POST',
      data: {followedId: followedId},
      success: function(data, textStatus, jqXHR) {
        // 200 is a redirect to login.
        if (jqXHR.status == 200) {
          show_status(login_to_follow_msg, IMPORTANT_STATUS);
          hide_status();
        } else if (jqXHR.status == 201) {
          $element.html("Unfollow").addClass('following');
          show_status("Followed.");
          hide_status();
        }
        $element.removeClass('loading');
      },
      error: function(jqXHR, textStatus, errorThrown) {
        show_status("Error following. Please try again later.");
        $element.removeClass('loading');
      }
    });
  }

  function unfollow($element, followedId)
  {
    $.ajax({
      url: '/follow',
      type: 'DELETE',
      data: {followedId: followedId},
      success: function(data, textStatus, jqXHR) {
        // 200 is a redirect to login.
        if (jqXHR.status == 200) {
          show_status(login_to_follow_msg, IMPORTANT_STATUS);
          hide_status();
        } else if (jqXHR.status == 204) {
          $element.html("Follow");
          show_status("Unollowed.");
          hide_status();
          $element.removeClass('loading').removeClass('following');
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        if (jqXHR.status == 403) {
          show_status(login_to_follow_msg, ERROR_STATUS);
          hide_status();
        } else {
          show_status("Error following. Please try again later.");
          $element.removeClass('loading');
        }
      }
    });
  }

  /** LOCATION AUTOCOMPLETE **/
  var locationAutocompleteData = false;
  $location.autocomplete({
    source: locationAutocompleteCallback,
    select: function(event, ui) {
      $location.val(ui.item.value);
      $location.attr('data-lat', false);
      $location.attr('data-long', false);
    }
  });

  $location.on('keyup', function(e) {
    var code = e.keyCode || e.which;
    // Remove lat, long on backspace and 0 - z
    if (code == 8 || (code >= 48 && code <= 90)) {
      $location.removeAttr('data-lat');
      $location.removeAttr('data-long');
    }
  });

  function locationAutocompleteCallback(request, callback)
  {
    if (! locationAutocompleteData) {
      locationAutocompleteCallout(request.term, callback);
    } else {
      var numLocs = locationAutocompleteData.length;
      var location;
      var stillMatches = false;
      for(var i = 0; i < numLocs; i++) {
        location = locationAutocompleteData[i ];
        if (location.locString.indexOf(request.term) > 0) {
          stillMatches = true;
        }
      }
      if ( stillMatches) {
        locationAutocompleteHandler(callback,locationAutocompleteData);
      } else {
        locationAutocompleteCallout(request.term, callback);
      }
    }
  }

  function locationAutocompleteCallout(term, callback)
  {
    $.ajax({
      type: 'GET',
      url: '/location_autocomplete',
      data: {
        term: term
      },
      success: function (data, textStatus, jqXHR) {
        locationAutocompleteData = data;
        locationAutocompleteResponseHandler(callback, locationAutocompleteData);
      },
    });
  }

  function locationAutocompleteResponseHandler(callback, data)
  {
    var numLocs = data.length;
    var results = [];
    var result;
    for (var i = 0; i < numLocs; i++) {
      result = {
        label: data[i].locString,
        value: data[i].locString,
      };
      results[i] = result;
    }
    if (numLocs > 0) {
      callback(results);
    } else {
      callback();
    }
  }

  /** GEOLOCATION **/
  // Get user location for relevant pages (edit list and user profile, for nearby tab).
  if ($location.length + $('.nearby-lists-tab').length > 0) {
    getPosition(function() {});
  }
  function geolocationError(message) {
    show_status(message ? message : 'Geolocation disabled.', ERROR_STATUS);
    hide_status(5000);
  }
  function getPosition(callback) {

    function getCurrentPositionSuccessCallback(pos) {
      position = pos;
      callback(position);
    }

    function getCurrentPositionErrorCallback(posError) {
      var message = posError.code == posError.PERMISSION_DENIED ? "Dang, geolocation is disabled." : false;
      geolocationError(message);
      callback();
    }

    if (position) {
      callback(position);
    } else {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(getCurrentPositionSuccessCallback, getCurrentPositionErrorCallback);
      } else {
        geolocationError();
        callback();
      }
    }
  }

  function getLocString(pos, callback)
  {

    // MS Maps API requires valid callback function for JSONP.
    function reverseGeocodeCallback(data) {}

    if ( ! pos) {
      callback();
      return;
    }
    var lat = pos.coords.latitude;
    var long = pos.coords.longitude;
    if (locString) {
      callback(lat, long, locString);
    } else {
      var point = lat + "," + long;
      $.ajax({
        type: 'GET',
        url: 'http://dev.virtualearth.net/REST/v1/Locations/' + point,
        cache: true,
        data: {
          includeEntityTypes: 'Neighborhood, PopulatedPlace, AdminDivision1, AdminDivision2, CountryRegion',
          includeNeighborhood: 1,
          // ciso2 option returns country code
          include: 'ciso2',
          key: locationApiKey,
        },
        dataType: 'jsonp',
        jsonp: 'jsonp',
        jsonpCallback: 'reverseGeocodeCallback',
        success: function(data) {
          var address = data.resourceSets[0].resources[0].address;

          var locString = address.locality ? address.locality + ', ' : '';

          // Use Locality, ST for US, Locality, Country Name for others.
          if (address.countryRegionIso2 == "US") {
            if (address.adminDistrict) {
              locString += address.adminDistrict;
            }
          } else if (address.countryRegion) {
              locString += address.countryRegion;
          }

          locString = locString.length ? locString : false;

          callback(lat, long, locString);
        }
      });
    }
  }

  /** GEOLCATION BUTTON **/
  $geolocationBtn.on('click', function(e) {
    e.preventDefault();
    var $this = $(this);
    $this.addClass('loading');
    getPosition(function(pos) {
      getLocString(pos, function (lat, long, locString) {
        if ( lat && long && locString) {
          $location.val(locString);
          $location.attr('data-lat', lat);
          $location.attr('data-long', long);
          $geolocationBtn.removeClass('loading');
          save_list();
        } else {
          $this.removeClass('loading');
        }
      });
    });
  });

  /*
   *
   * Sidebar
   *
   */
  bodyClick = function (e) {

    var $target = $(e.target);

    //Check if the sidebar is open and if so hide it
    if (!$target.is($sidebar) && !$target.parents().is($sidebar)) {
      $body.removeClass('sidebar-transition-open').addClass('sidebar-transition-close');
      $siteWrapper.removeClass('sidebar-open');

      //Remove the bodyClick listener
      $siteWrapper.off('click', bodyClick);

      setTimeout(function () {
        $this.fadeIn();
      }, 500)
    }
    ;
  };

  $sidebarbtn.on('click', function (e) {

    $this = $(this);
    $this.hide();
    $body.addClass('sidebar-transition-open');
    $siteWrapper.addClass('sidebar-open');

    //Attach a listener to close this
    $siteWrapper.on('click', bodyClick);

  });


  /*
   *
   * Show Share
   *
   */

  $share.on('click', function (e) {

    $share.removeClass('move-left').addClass('move-right');
    $shareBtns.removeClass('move-right').addClass('move-left');

    //Attach a listener to close this
    $siteWrapper.on('click', function (e) {
      ;

      var $target = $(e.target);

      //Check if we are showing share buttons and if so hide them
      if (!$target.is($shareBtns) && !$target.parents().is('.share-wrapper')) {
        $share.removeClass('move-right').addClass('move-left');
        $shareBtns.removeClass('move-left').addClass('move-right');

        //Remove the bodyClick listener
        $siteWrapper.off('click', bodyClick);
      }
      ;
    })

  });

  $('.like-btn').on('click', function (e) {
    e.preventDefault();
    var $this = $(this);

    show_save('Liking...');
    $.ajax({
      type: "POST",
      url: '/list_like',
      data: {
        listId: listID
      },
      success: function (data, textStatus, jqXHR) {
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
      error: function (jqXHR, textStatus, errorThrown) {
        hide_save();
        // 409 indicates like already exists.
        if (jqXHR.status == 409) {
          $this.addClass('liked');
          show_status("Already liked.");
        } else {
          show_status(error_msg);
        }
        hide_status();
      }
    });
  });

  /*
   *
   * Show Save
   *
   */
  function show_save(msg) {
    $saveIndicator.fadeIn().html(msg);

  }

  var statusClasses = {
    NORMAL_STATUS: 'normal',
    IMPORTANT_STATUS: 'important',
    ERROR_STATUS: 'error'
  };
  function show_status(msg, status) {
    for (var statusClass in statusClasses) {
      $statusIndicator.removeClass(statusClass);
    }
    if (status) {
      $statusIndicator.addClass(status);
    }
    $statusIndicator.fadeIn().html(msg);
  }

  /*
   *
   * Hide Save
   *
   */
  function hide_save(delay) {
    setTimeout(function () {
      $saveIndicator.fadeOut();
    }, (delay ? delay : 1200));
  }

  function hide_status(delay) {
    setTimeout(function () {
      $statusIndicator.fadeOut();
    }, delay ? delay : 3000);
  }

  /*
   *
   * Add a list item and save it to the DB
   *
   */
  var listCount = $('.list-item').length;

  $('#add').on('click', function (e) {

    e.preventDefault();

    listCount++;

    $.ajax({
      type: "POST",
      url: '/list_item',
      data: {
        'listId': listID
      },
      success: function (data, textStatus, jqXHR) {
        if (jqXHR.status == 201) {
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
        } else {
          show_save("Error adding list item. Refresh and try again.");
          hide_save();
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        show_save("Error adding list item.");
        hide_save();
      }


    });
  });

  /*
   *
   * Delete a list and remove it from the DOM
   *
   */

  $('.delete-list').on('click', function (e) {

    e.preventDefault();

    $this = $(this);

    var $container = $this.parents('li');
    var listID = $container.attr('data-id');

    var url = '/list/' + listID;

    var confirmation = confirm("Are you sure you want to delete this list?");

    if (confirmation == true) {

      $.ajax({
        type: "DELETE",
        url: url,
        data: {},
        success: function (data, textStatus, jqXHR) {
          if (jqXHR.status == 204) {
            $container.fadeOut();
            $container.remove();
            show_save('List deleted.');
            hide_save()
          } else {
            show_save('Error deleting list. Refresh and try again.');
            hide_save();
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          show_save("Error deleting list.");
          hide_save();
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
  $('.edit-list-head-wrapper').on('keyup', function (e) {

    var $this = $(this);

    var imgURL = $('.list-img').val();

    if (imgURL) {
      $('.bg-wrapper').css('background-image', 'url(' + imgURL + ')');
      $('.mobile-background').css('background-image', 'url(' + imgURL + ')');
    } else {
      $('.bg-wrapper').css('background-image', 'none');
    }

    clearTimeout($.data(this, 'timer'));

    $(this).data('timer', setTimeout(function () {

      save_list();

    }, 300));


  });


  /*
   *
   * Take list title and save
   *
   * Communicate saved
   *
   */
  function save_list() {

    var title = $('.list-title').val();

    var subtitle = $('.list-subtitle').val();

    var imgURL = $('.list-img').val();

    var locString = $location.val();

    var lat = $location.attr('data-lat');

    var long = $location.attr('data-long');

    show_save(saving_msg);

    var data = {
      title: title,
      subtitle: subtitle,
      imageURL: imgURL,
      locString: locString
    };

    // Only set lat/long if locString is set (for case when user erases locString).
    data.locString = locString;
    if (locString) {
      if (lat) {
        data.lat = lat;
      }
      if (long) {
        data.lon = long;
      }
    } else {
      data.lat = '';
      data.lon = '';
    }

    $.ajax({

      type: "PUT",
      url: '/list/' + listID,
      data: data,
      success: function (data, textStatus, jqXHR) {
        show_save(saved_msg);
        hide_save();
      },
      error: function (jqXHR, textStatus, errorThrown) {
        show_save("Error saving list.");
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
  $('.list').on('keyup', '.list-item', function (e) {

    var $this = $(this);

    clearTimeout($.data(this, 'timer'));
    $(this).data('timer', setTimeout(function () {

      save_list_item(listID, $this);

    }, 300));

  });

  $('.list').on('keyup', '.description', function (e) {
    $(this).trigger('autosize.resize');
  });


  /*
   *
   * Take list item and save
   *
   * Communicate saved
   *
   */
  function save_list_item(listID, $item) {

    var title = $item.find('.item').val();

    var dataID = $item.attr('data-id');

    var desc = $item.find('.description').val();

    var orderIndex = $item.attr('data-order_index');

    show_save(saving_msg);

    $.ajax({
      type: "PUT",
      url: '/list_item/' + dataID,
      data: {
        'listId': listID,
        'title': title,
        'description': desc,
        'orderIndex': orderIndex
      },
      success: function (data) {
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
  function reorder_list_items() {
    $('.list-item', '.editable-list').each(function (index, object) {

      var $this = $(this);
      var newNum = index + 1;
      var oldNum = $this.attr('data-order_index');
      if (oldNum != newNum) {
        $this.find('.number').html("" + newNum);
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

  $('.list').on('click', '.delete-list-item', function (e) {

    e.preventDefault();

    $container = $(this).parents('li.list-item');

    var itemID = $container.attr('data-id');
    var url = '/list_item/' + itemID;

    var confirmation = confirm("Are you sure you want to delete this item?");

    if (confirmation == true) {

      show_save("Deleting item...");

      $.ajax({
        type: "DELETE",
        url: url,
        data: {},
        success: function (data) {
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
  $search = $('#search');


  /*
   *
   * Listen for stop in typing on search and then search
   *
   *
   */
  $search.on('keyup', function (e) {

    var $this = $(this);
    var keyword = $search.val();

    if (!$search.val()) {
      $userResults.empty();
      $listResults.empty();
      return;
    }

    clearTimeout($.data(this, 'timer'));
    $(this).data('timer', setTimeout(function () {

      $userResults.empty();
      $listResults.empty();

      $.ajax({
        url: '/search/query?all=' + keyword,
        type: 'GET',
      }).done(function (response) {

        var users = response.users;
        var lists = response.lists;

        var usersEmpty = false;
        var listsEmpty = false;

        if (!users.length && !lists.length) {
          var html = "<li><h3 class='nothing-found'>Nothing Found...</h3></li>";
          $listResults.append(html);
          return;
        }

        if (!users.length) {
          var usersEmpty = true;
          search_print_user(user, usersEmpty);

        } else {
          for (i = 0; i < users.length && i < 6; i++) {
            var user = users[i];
            search_print_user(user, usersEmpty);
          }
        }


        if (!lists.length) {
          var listsEmpty = true;
          search_print_list(list, listsEmpty);
        } else {
          for (i = 0; i < lists.length && i < 8; i++) {
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
  function search_print_user(user, empty) {
    if (empty) {
      var html = "<li><p class='nothing-found'>No user found...</p></li>";
    } else {
      var html = "<li> <a href='/" + user.username + "'><div class='search-user'><img src='" + user.profilePicURL + "'/>  <span class='search-user-username'>" + user.username + "</span> </div> </a> </li";
    }
    $userResults.append(html);
  }


  /*
   *
   *
   * Take the list and print it to the search results. If it is empty show nothing found message
   *
   */
  function search_print_list(list, empty) {
    if (empty) {
      var html = "<li><p class='nothing-found'>No lists found...</p></li>";
    } else {
      var html = "<li> <a href='/list/" + list.id + "'> <div class='search-list'> <h3 class='search-title'>" + list.title + "</h3> <p class='search-subtitle'>" + list.subtitle + "</p></a> </div> <a href=/" + list.user.username + "><div class='search-list-profile'><img src='" + list.user.profilePicURL + "' /><span class='search-list-username'>by " + list.user.username + "</span> </div> </a></li>";
    }

    $listResults.append(html);
  }


  /*
   *
   * Feed and Home Feed
   *
   */
  var feedLoading = false;
  var $feedInsert = $('#feed-insert');
  var timer;

  if ($('.feed').length) {
    if ($('.home-header').length) {
      initScrollListenerForHome();
    } else {
      var $feedInsert = $('#feed-insert');
      initScrollListenerForFeed();
    }
  }

  function initScrollListenerForHome() {
    $(document).on('scroll', function (e) {

      var $logo = $('.sidebar .logo');

      manageHover();

      var height = $(document).height();
      var scrollBottom = windowBottom();

      if (scrollBottom >= height && !feedLoading) {
        $logo.addClass("wiggling");
        setTimeout(function () {
          $logo.removeClass('wiggling');
          show_save("&larr; Pssst, over here...");
          feedLoading = true;
          setTimeout(function () {
            $saveIndicator.fadeOut(function () {
              feedLoading = false;
            });
          }, 3000);
        }, 800);
      }
    });
  }

  /*
   *
   *
   * Listen to the feed scroll and if we hit the bottom lets load more feed
   *
   */
  function initScrollListenerForFeed() {

    $(document).on('scroll', function (e) {

      manageHover();

      var height = $(document).height();
      var scrollBottom = windowBottom();

      if (feedLoading == false && scrollBottom == height) {

        feedloading = true;

        $loader.fadeIn(200);

        var last = $('.feed-item-count').last().attr('data-id');

        $.ajax({
          url: '/feed/next',
          type: 'GET',
          data: {
            'cursor': last
          }
        }).done(function (feedLists) {

          if (feedLists == false) {

            feedLoading = true;

            $('#feed-cta').show()

            setTimeout(function () {
              $loader.fadeOut(200);
            }, 800);

          } else {

            showFeedNext(feedLists, function () {
              setTimeout(function () {
                $loader.fadeOut(200);
              }, 800);
              feedLoading = false;
            });
          }

        })
      }
    });
  }

  function manageHover() {
    clearTimeout(timer);

    if (!$body.hasClass('disable-hover')) {
      $body.addClass('disable-hover')
    }

    timer = setTimeout(function () {
      $body.removeClass('disable-hover')
    }, 200);
  }

  function windowBottom() {
    return $(window).scrollTop() + $(window).height();
  }

  /*
   *
   *
   * Print out the next 21 items returned from the feed.
   *
   */
  function showFeedNext(feedLists, callback) {

    for (i = 0; i < feedLists.length; i++) {

      print(feedLists[i]);

    }

    callback();

    function print(feedList) {
      var html = '<div data-id="' + feedList.id + '" onclick="location.href=\'/list/' + feedList.id + '\'" class="col-xs-12 col-sm-12 col-md-12 col-lg-12 feed-item-wrapper feed-item-count" style="background-image: url(' + feedList.imageURL + ')"> <div class="feed-item-overlay"> <div class="feed-item"> <h3 class="feed-item-title">' + feedList.title + '</h3> <a href="/' + feedList.user.username + '"> <div class="feed-item-profile"> <img src="' + feedList.user.profilePicURL + '" /> <span class="feed-item-username">' + feedList.user.username + '</span> </div> </a> </div> </div> </div>';

      $feedInsert.append(html);
    }


  }

  /*
   *
   *
   * Toggle the profile page
   *
   */
  $('.profile-toggle li').on('click', function (e) {
    $this = $(this);
    if ($this.hasClass('active')) {
      return false;
    } else {
      if($this.data('async_load_func')) {
        $this.addClass('loading');
        var $container = $(getActiveTabSelector($this)).find('.col-md-6');
        // Only async load if container is empty.
        if ($container.children().length <= 0) {
          // Call async callback (set/attached at top of script).
          $this.data('async_load_func').apply(this, [$container]);
        } else {
          toggleContent($this);
        }
      } else {
        toggleContent($this);
      }
    }

  });

  function toggleContent($element) {
    if ($element.hasClass('sub-toggle')) {
      $('.sub-toggle-wrapper').hide();
      $element.parents('.sub-profile-toggle').find('li.active').removeClass('active');
    } else {
      $('.profile-lists-wrapper, .profile-users-wrapper').not('sub-toggle-wrapper').hide();
      var $parent = $element.parents('.profile-toggle')
      $parent.not('sub-profile-toggle').find('li.active').removeClass('active');
      var $subToggle = $(getActiveTabSelector($element)).find('.sub-profile-toggle');
      console.log($subToggle);
      if ($subToggle.length > 0) {
        var $activeSubToggle = $subToggle.find('li.active');
        console.log(getActiveTabSelector($activeSubToggle));
        $(getActiveTabSelector($activeSubToggle)).show();
      }

    }



    $element.addClass('active');
    $element.removeClass('loading');
    $(getActiveTabSelector($element)).fadeIn(800);
    $(getActiveTabSelector($element)).show();
  }

  function getActiveTabSelector($element) {
    return '.profile-' + $element.attr('data-toggle') + '-toggle';
  }

  function loadNearbyLists($container)
  {
    var $this = $(this);
    var user = $('body').hasClass('logged-in') ? $('.profile-wrapper').attr('data-user_id') : null;
    getPosition(function(pos) {
      getLocString(pos, function(lat, long, locString) {
        if (locString) {
          $.ajax({
            type: 'GET',
            url: '/lists/nearby',
            data: {
              locString: locString,
              profileUserId: user
            },
            success: function(data, textStatus, jqXHR) {
              $container.html(data);
              toggleContent($this);
            }
          });
        } else {
          show_status("We can't find you on our map! Sorry!", ERROR_STATUS);
          hide_status(5000);
          $this.removeClass('loading');
        }
      });
    });



  }

});
