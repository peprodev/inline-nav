(function($) {
  $(document).ready(function() {
    $.each(peproInlineNavigation.data, function(i, x) {
      $(".pepro-inline-navigation-container").append(`<li data-href='${x.anchor}' id='${x.uniqid}'>${x.title}</li>`);
    });

    $(document).on("click tap", ".pepro-inline-navigation-container>li", function(e) {
      e.preventDefault();
      var id = $(this).attr("data-href");
      scroll($(`${id}`));
      $(".pepro-inline-navigation-container.responsive").removeClass("hover")
    });

    $(document).on("click tap", ".pepro-inline-navigation-container.responsive", function(e) {
      e.preventDefault();
      var me = $(this);
      me.removeClass("hover").addClass("hover");
    });

    $(document).mouseup(function(e) {
      var container = $(".pepro-inline-navigation-container.responsive.hover");
      if (!container.is(e.target) && container.has(e.target).length === 0) {
        container.removeClass("hover");
      }
    });

    if ($(window).width() < 1603) {
      $(".pepro-inline-navigation-container").addClass("responsive");
    } else {
      $(".pepro-inline-navigation-container").removeClass("responsive");
    }

    $(window).resize(function() {
      if ($(window).width() < 1603) {
        $(".pepro-inline-navigation-container").addClass("responsive");
      } else {
        $(".pepro-inline-navigation-container").removeClass("responsive");
      }
    });

    function scroll(e) {
      $('html, body').animate({
        scrollTop: e.offset().top - parseInt(peproInlineNavigation.offset || 0)
      }, 500);
      setTimeout(function () {
        $(".pepro-inline-navigation-container.responsive").removeClass("hover");
      }, 500);
    }

  });
})(jQuery);
