$(document).ready(function() {
	
    new WOW().init();

    $(".faculty-slick").slick({
        dots: true,
        infinite: true,
        slidesToShow: 3,
        slidesToScroll: 1,
		autoplay: true,
		autoplaySpeed: 5000,
		pauseOnHover: true,
        responsive: [{
                breakpoint: 992,
                settings: {
                    arrows: false,
                    slidesToShow: 2,
                }
            },
            {
                breakpoint: 767,
                settings: {
                    arrows: false,
                    slidesToShow: 1,
                }
            }
        ]
    });

    /* function for header fixed */


    $(window).scroll(function() {
	   $(window).scrollTop() >= 500 ? $(".footer-fixed-bar").slideDown(300) : $(".footer-fixed-bar").slideUp(300);         
        var scroll = $(window).scrollTop();

        if (scroll >= 100) {
            $("header").addClass("scroll");
        } else {
            $("header").removeClass("scroll");
        }
    });




    // The typewriter element
    var typeWriterElement = document.getElementById('typewriter');

    // The TextArray: 
    var textArray = ["Global Learning at your pace & your place", "Advance in your career with futuristic online degree"];

    // You can also do this by transfering it through a data-attribute
    // var textArray = typeWriterElement.getAttribute('data-array');


    // function to generate the backspace effect 
    /* function delWriter(text, i, cb) {
        if (i >= 0) {
            typeWriterElement.innerHTML = text.substring(0, i--);
            // generate a random Number to emulate backspace hitting.
            var rndBack = 10 + Math.random() * 100;
            setTimeout(function() {
                delWriter(text, i, cb);
            }, rndBack);
        } else if (typeof cb == 'function') {
            setTimeout(cb, 1000);
        }
    }; */

    // function to generate the keyhitting effect
  /*   function typeWriter(text, i, cb) {
        if (i < text.length + 1) {
            typeWriterElement.innerHTML = text.substring(0, i++);
            // generate a random Number to emulate Typing on the Keyboard.
            var rndTyping = 250 - Math.random() * 100;
            setTimeout(function() {
                typeWriter(text, i++, cb)
            }, rndTyping);
        } else if (i === text.length + 1) {
            setTimeout(function() {
                delWriter(text, i, cb)
            }, 1000);
        }
    }; */

    // the main writer function
  /*   function StartWriter(i) {
        if (typeof textArray[i] == "undefined") {
            setTimeout(function() {
                StartWriter(0)
            }, 1000);
        } else if (i < textArray[i].length + 1) {
            typeWriter(textArray[i], 0, function() {
                StartWriter(i + 1);
            });
        }
    }; */
    // wait one second then start the typewriter
    /* setTimeout(function() {
        StartWriter(0);
    }, 100); */



 /* function for counter */

 var a = 0;
 $(window).scroll(function() {

   var oTop = $('#counter').offset().top - window.innerHeight;
   if (a == 0 && $(window).scrollTop() > oTop) {
     $('.count').each(function() {
       var $this = $(this),
         countTo = $this.attr('data-count');
       $({
         countNum: $this.text()
       }).animate({
           countNum: countTo
         },

         {

           duration: 2000,
           easing: 'swing',
           step: function() {
             $this.text(Math.floor(this.countNum));
           },
           complete: function() {
             $this.text(this.countNum);
             //alert('finished');
           }

         });
     });
     a = 1;
   }

 }); 
      $('.news-slider').slick({
         infinite: true,
         dots: true,
         arrows: false,
         autoplay: true,
         autoplaySpeed: 3000,
         slidesToShow: 1,
         slidesToScroll: 1,
         responsive: [{
            breakpoint: 480,
            settings: {
               slidesToShow: 1,
               slidesToScroll: 1
            }
         }]
      });
      $('.course-list-slider').slick({
         infinite: true,
         dots: true,
         arrows: true,
         autoplay: false,
         autoplaySpeed: 10000,
         slidesToShow: 1,
         slidesToScroll: 1,
         responsive: [{
            breakpoint: 480,
            settings: {
               slidesToShow: 1,
               slidesToScroll: 1
            }
         }]
      });
      $('.industry-slider').slick({
         infinite: true,
         dots: true,
         arrows: true,
         autoplay: true,
         autoplaySpeed: 10000,
         slidesToShow: 3,
         slidesToScroll: 1,
         responsive: [{
            breakpoint: 1023,
            settings: {
               slidesToShow: 2,
               slidesToScroll: 1
            }
         },
         {
            breakpoint: 992,
            settings: {
               slidesToShow: 1,
               slidesToScroll: 1
            }
         },
         {
            breakpoint: 767,
            settings: {
               slidesToShow: 1,
               slidesToScroll: 1
            }
         }]
      });
      $('.partner-slider').slick({
        infinite: true,
        dots: true,
        arrows: true,
        autoplay: true,
        autoplaySpeed: 10000,
        slidesToShow: 5,
        slidesToScroll: 1,
        responsive: [{
           breakpoint: 1023,
           settings: {
              slidesToShow: 4,
              slidesToScroll: 1
           }
        },
        {
           breakpoint: 992,
           settings: {
              slidesToShow: 3,
              slidesToScroll: 1
           }
        },
        {
           breakpoint: 767,
           settings: {
              slidesToShow: 2,
              slidesToScroll: 1
           }
        }]
     });
      $('.homepage-slider').slick({
         infinite: true,
         dots: true,
         arrows: true,
         autoplay: true,
         autoplaySpeed: 10000,
         slidesToShow: 1,
         slidesToScroll: 1,
         responsive: [{
            breakpoint: 1023,
            settings: {
               slidesToShow: 1,
               slidesToScroll: 1
            }
         },
         {
            breakpoint: 767,
            settings: {
               slidesToShow: 1,
               slidesToScroll: 1
            }
         }]
      });

      $('.rankingsBlock__slider').slick({
         infinite: true,
         dots: false,
         arrows: false,
         autoplay: true,
         autoplaySpeed: 2000,
         slidesToShow: 1,
         slidesToScroll: 1,
         customPaging: function (slick, index) {
            var targetImage = slick.$slides.eq(index).find('img.slide-logo').attr('src');
            return '<img src=" ' + targetImage + ' "/>';
         },
         responsive: [
            {
               breakpoint: 480,
               settings: {
                  slidesToShow: 1,
                  slidesToScroll: 1
               }
            }
         ]
      });

 
    
    // $(".accordion-button").click( function() {
        // $(window).scrollTop(2500);
    // });  
    // $(".course-tabs-details.red .accordion-button").click( function() {
        // $(window).scrollTop(3200);
    // }); 

    
	/* accordian panel move offset */
    $('.collapse').on('shown.bs.collapse', function(e) {
        var $card = $(this).closest('.accordion-item');
        var $open = $($(this).data('parent')).find('.collapse.show');
        var additionalOffset = 220;
        if($card.prevAll().filter($open.closest('.accordion-item')).length !== 0)
        {
            additionalOffset =  $open.height();
        }
        $('html,body').animate({
            scrollTop: $card.offset().top - additionalOffset
        }, 500);
    });
	

	
	
//	$(".readMore-link").hover(
//	  function () {
//		$(".faculty-card").addClass("result_hover");
//	  },
//	  function () {
//		$(".faculty-card").removeClass("result_hover");
//	  }
//	);

$(".bell-icon").click(function() {
    $(".notifi-listing").toggleClass('close-swipe');
});

});



