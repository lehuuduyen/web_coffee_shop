var header = {
	init: function() {
		header.methods.boxHeaderMenu();
	},
	methods: {
		boxHeaderMenu: function() {
			jQuery("#btnHeaderMenu, .header-main-menu").hover(function() {
				jQuery(".header-overlay").addClass("active");
			}, function() {
				jQuery(".header-overlay").removeClass("active");
			});
		},
		hideMenuCate: function() {
			var boxHeaderMenu = jQuery("#logo>#btnHeaderMenu");
			var heightHeader = jQuery(".header-main").height();
			var boxSlidingMenu = jQuery("#content .menu-top .menu-main-menu-container");

			if (boxSlidingMenu.length) {
				var top = boxSlidingMenu.offset().top;
				var height = boxSlidingMenu.height();
				var offsettop = (top + height) - heightHeader;
				console.log(top,height,offsettop)

				jQuery(window).on('scroll', function() {
					if (jQuery(window).scrollTop() < offsettop) {
						boxHeaderMenu.css({ 'pointer-events': 'none' });
					} else {
						boxHeaderMenu.css({ 'pointer-events': 'unset' });
					}
				});
			}
		}
	}
}

jQuery(document).ready(function() {
	header.init();
	jQuery("body.home #logo>#btnHeaderMenu").css({ 'pointer-events': 'none' });
	setTimeout(() => {
		header.methods.hideMenuCate();
	}, 2000);
	
	
	jQuery(".menu-mobile-section ul.sub-menu").removeClass("nav-dropdown nav-dropdown-defaul");
	jQuery("ul.nav-menu-mobile .icon-angle-down").remove();
	
	jQuery("ul.nav-menu-mobile > li:nth-child(1)").addClass("active");
	jQuery('ul.nav-menu-mobile > li').click(function(){
		jQuery(this).addClass("active").siblings().removeClass("active");
	});

	jQuery(".footer-menu-mobile ul.menu li.open-menu-mobile").click(function(){
		jQuery(".menu-mobile-section").toggleClass("active");
		jQuery(".footer-wrapper").removeClass("active");
	});
	jQuery(".load-more-info").click(function(){
		jQuery(".load-more-info").addClass("active");
		jQuery(".woocommerce-Tabs-panel--description").removeClass("active");
	});
	jQuery(".footer-menu-mobile ul.menu li:last-child").click(function(){
		jQuery(".footer-wrapper").toggleClass("active");
		jQuery(".menu-mobile-section").removeClass("active");
	});
	
	jQuery(".open-menu-mobile a").click(function(){
		jQuery(".header-wrapper").addClass("active");
	});
	jQuery(".load-more-info").click(function(){
		jQuery(".load-more-info").addClass("active");
		jQuery(".woocommerce-Tabs-panel--description").removeClass("active");
	});
	jQuery(".load-more-specifications").click(function(){
		jQuery(".load-more-specifications").addClass("active");
		jQuery(".product-footer-right").addClass("active");
	});
	jQuery(".send-review").click(function(){
		jQuery("#review_form_wrapper").addClass("open");
	});
	jQuery("#item-list-video").click(function(){
		jQuery(".list-video-reviews").addClass("active");
	});
	jQuery(".close-video-review").click(function(){
		jQuery(".list-video-reviews").removeClass("active");
	});
	jQuery('.list-video-reviews .close-video-review').click(function(e) {
		e.preventDefault();
		jQuery('.list-video-reviews .main-video-review .swiper-slide').children('iframe').attr('src', '');
	});
	jQuery('#item-list-video').click(function(e) {
		e.preventDefault();
		jQuery('.list-video-reviews .main-video-review .swiper-slide').children('iframe').attr('src', src);
	});
	// Finds all iframes from youtubes and gives them a unique class
	jQuery('iframe[src*="https://www.youtube.com/embed/"]').addClass("youtube-iframe");
	jQuery(".list-video-reviews .swiper-button-prev").click(function() {
		jQuery('.youtube-iframe').each(function(index) {
			jQuery(this).attr('src', jQuery(this).attr('src'));
			return false;
		});
	});
	
	
	
	jQuery(".product-reviews div.review_vote").remove();
	
	
	
});

jQuery(window).scroll(function() {    
	var scroll = jQuery(window).scrollTop();
	if (scroll >= 100) {
		jQuery("#header").addClass("active");
		jQuery(".header-wrapper").addClass("stuck");
	}
	if (scroll < 100) {
		jQuery("#header").removeClass("active");
		jQuery(".header-wrapper").removeClass("stuck");
	}
});