document.addEventListener("DOMContentLoaded", function () {
  var HomeSlider = new Swiper(".home-offer-slider .slider", {
    speed: 1000,
    loop: true,
    autoplay:false,
    // autoplay: {
    //   delay: 3000,
    //   waitForTransition: true,
    //   disableOnInteraction: true,
    // },

    navigation: {
      nextEl: ".slider-fullscreen-button-next",
      prevEl: ".slider-fullscreen-button-prev",
    },

    pagination: {
      el: ".swiper-pagination-custom",
      clickable: true,
    },

    breakpoints: {
      0: {
        slidesPerView: 1,
        spaceBetween: 10,
      },
      575: {
        slidesPerView: 1,
        spaceBetween: 10,
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 10,
      },
      1024: {
        slidesPerView: 3,
        spaceBetween: 10,
      },
      1200: {
        slidesPerView: 3,
        spaceBetween: 15,
      }
    }

  });
  var homeOfferSlider = document.querySelector(".home-offer-slider");
  if (homeOfferSlider) {
    homeOfferSlider.addEventListener('mouseenter', function() {
      HomeSlider.autoplay.stop();
    });
    homeOfferSlider.addEventListener('mouseleave', function() {
      HomeSlider.autoplay.start();
    });
  }
  
  var HomeSlider = new Swiper(".home-blog-slider .slider", {
    speed: 1000,
    loop: true,
    autoplay: {
      delay: 3000,
      waitForTransition: true,
      disableOnInteraction: true,
    },
    flipEffect: {
      rotate: 30,
      slideShadows: false,
    },
   navigation: {
    nextEl: ".slider-fullscreen-button-next",
    prevEl: ".slider-fullscreen-button-prev",
  },
  pagination: {
    el: '.swiper-pagination-blog',
    clickable: true,
    },
    breakpoints: {
      0: {
        slidesPerView: 1,
        spaceBetween: 10,
        clickable: true,
      },
      575: {
        slidesPerView: 1,
        spaceBetween: 10,
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 10,
      },
      1024: {
        slidesPerView: 3,
        spaceBetween: 10,
      },
      1200: {
        slidesPerView: 4,
        spaceBetween: 15,
      }
    }
  });

  var homeOfferSlider = document.querySelector(".home-blog-slider");
  if (homeOfferSlider) {
    homeOfferSlider.addEventListener('mouseenter', function() {
      HomeSlider.autoplay.stop();
    });
    
    homeOfferSlider.addEventListener('mouseleave', function() {
      HomeSlider.autoplay.start();
    });
  }


window.addEventListener("scroll", function () {

  /* run only on home page */
  if (!document.body.classList.contains("cms-home")) return;

  const stickyBar = document.querySelector(".foote-tyre-finder");
  if (!stickyBar) return;

  var scrolledToBottom =
    (window.innerHeight + window.scrollY) >=
    (document.documentElement.scrollHeight - 100);

  if (scrolledToBottom || window.scrollY <= 760) {
    stickyBar.classList.remove("active");
  } else {
    stickyBar.classList.add("active");
  }
});

window.addEventListener("scroll", function () {

  /* run only on listing page and product page */
  var body = document.body;
  if (!body.classList.contains("catalog-category-view") && !body.classList.contains("catalog-product-view")) return;

  const stickyBar = document.querySelector(".foote-tyre-finder");
  if (!stickyBar) return;

  var scrolledToBottom =
    (window.innerHeight + window.scrollY) >=
    (document.documentElement.scrollHeight - 100);

  if (scrolledToBottom) {
    stickyBar.classList.remove("active");
  } else {
    stickyBar.classList.add("active");
  }

});


});



