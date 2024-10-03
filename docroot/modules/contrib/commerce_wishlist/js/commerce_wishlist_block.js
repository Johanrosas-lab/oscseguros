/**
 * @file
 * Defines Javascript behaviors for the commerce wishlist block module.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.commerceWishlistBlock = {
    attach: function (context) {
      var $wishlist = $(context).find('.block-commerce-wishlist-block');
      var $wishlistButton = $wishlist.find('.wishlist-block--link__expand');
      var $wishlistContents = $wishlist.find('.wishlist-block--contents');

      if ($wishlistContents.length > 0) {
        // Expand the block when the link is clicked.
        $wishlistButton.on('click', function (e) {
          // Prevent it from going to the cart.
          e.preventDefault();
          // Get the shopping cart width + the offset to the left.
          var windowWidth = $(window).width();
          var wishlistWidth = $wishlistContents.width() + $wishlist.offset().left;
          // If the cart goes out of the viewport we should align it right.
          if (wishlistWidth > windowWidth) {
            $wishlistContents.addClass('is-outside-horizontal');
          }
          // Toggle the expanded class.
          $wishlistContents
            .toggleClass('wishlist-block--contents__expanded')
            .slideToggle();
        });
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
