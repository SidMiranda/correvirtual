(function () {
  'use strict';

  // ---- Menu mobile ----
  var toggle = document.getElementById('cv-nav-toggle');
  var links = document.getElementById('cv-nav-links');

  if (toggle && links) {
    toggle.addEventListener('click', function () {
      var isOpen = links.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    links.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        links.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  // ---- Banner rotativo ----
  var track = document.getElementById('cv-banner-track');
  if (!track) {
    return;
  }

  var slides = Array.prototype.slice.call(track.querySelectorAll('.cv-banner__slide'));
  var dots = Array.prototype.slice.call(document.querySelectorAll('.cv-banner__dot'));
  var prevBtn = document.getElementById('cv-banner-prev');
  var nextBtn = document.getElementById('cv-banner-next');

  if (slides.length < 2) {
    return;
  }

  var current = 0;
  var intervalMs = 6000;
  var timer = null;

  function goTo(index) {
    slides[current].classList.remove('is-active');
    dots[current] && dots[current].classList.remove('is-active');

    current = (index + slides.length) % slides.length;

    slides[current].classList.add('is-active');
    dots[current] && dots[current].classList.add('is-active');
  }

  function next() {
    goTo(current + 1);
  }

  function prev() {
    goTo(current - 1);
  }

  function start() {
    stop();
    timer = window.setInterval(next, intervalMs);
  }

  function stop() {
    if (timer) {
      window.clearInterval(timer);
      timer = null;
    }
  }

  nextBtn && nextBtn.addEventListener('click', function () {
    next();
    start();
  });

  prevBtn && prevBtn.addEventListener('click', function () {
    prev();
    start();
  });

  dots.forEach(function (dot) {
    dot.addEventListener('click', function () {
      goTo(parseInt(dot.getAttribute('data-slide-goto'), 10));
      start();
    });
  });

  track.addEventListener('mouseenter', stop);
  track.addEventListener('mouseleave', start);

  start();
})();
