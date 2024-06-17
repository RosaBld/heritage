import Glide from '@glidejs/glide';

export default function Carousel() {

  let glide = new Glide('.glide', {
    type: 'slider',
    // autoplay: 4000,
    animationDuration: 1000,
    focusAt: 'center',
    perView: 1
  });

  glide.mount();

  let discover = new Glide('.discover', {
    type: 'carousel',
    animationDuration: 1000,
    // autoplay: 3000,
    perView: 1,
    peek: {
      before: 70,
      after: 70
    },
    gap: 20,
    breakpoints: {
      768: {
        peek: {
          before: 200,
          after: 200
        }
      },
      650: {
        peek: {
          before: 50,
          after: 50
        }
      }
    }
  });

  discover.mount();
};

document.getElementById('b-burger').addEventListener('click', function() {
  document.querySelector('.header-burger nav ul').classList.toggle('show');
});


$(document).ready(function(){
  $("nav ul li a, .scrollDown a").on('click', function(event) {
    if (this.hash !== "") {
      event.preventDefault();
      var hash = this.hash;
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 800, function(){
        window.location.hash = hash;
      });
    }
  });
});
