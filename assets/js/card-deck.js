/**
 * N.A Fresh Fruits & Coconuts - 3D Swipeable Card Deck Engine
 * Physical Tinder-style card deck interaction using HTML5, CSS3, Vanilla JavaScript, and GSAP.
 * 
 * Features:
 * - Responsive 3D visual hierarchy (Front, 2nd, 3rd, 4th cards behind)
 * - Proportional desktop & mobile stack offsets
 * - Physical touchmove / mousemove follow with dynamic rotation and scale
 * - Smooth progressive reveal of card underneath as top card is pulled away
 * - Velocity + distance swipe threshold detection
 * - Elastic spring-back if below threshold
 * - Physical throw-away outside screen if above threshold
 * - Fully accessible circular arrow navigation and pagination dots
 */

class CardDeck {
  constructor(deckContainerSelector, options = {}) {
    this.container = document.querySelector(deckContainerSelector);
    if (!this.container) return;

    this.cards = Array.from(this.container.querySelectorAll('.deck-card'));
    this.totalCards = this.cards.length;
    if (this.totalCards === 0) return;

    this.currentIndex = 0;
    this.isDragging = false;
    this.hasMoved = false;
    this.startX = 0;
    this.startY = 0;
    this.currentX = 0;
    this.currentY = 0;
    this.startTime = 0;
    this.isAnimating = false;

    this.dotsContainer = options.dotsContainer ? document.querySelector(options.dotsContainer) : null;
    this.prevBtn = options.prevBtn ? document.querySelector(options.prevBtn) : null;
    this.nextBtn = options.nextBtn ? document.querySelector(options.nextBtn) : null;
    this.accentColor = options.accentColor || '#15803d';

    this.init();
  }

  init() {
    this.setupStack(false);
    this.bindEvents();
    this.createOrUpdateDots();

    // Adjust stack on window resize (mobile <-> desktop)
    window.addEventListener('resize', () => {
      if (!this.isDragging && !this.isAnimating) {
        this.setupStack(false);
      }
    });
  }

  getStepY() {
    if (window.innerWidth >= 1440) return 36;
    if (window.innerWidth >= 1280) return 30;
    if (window.innerWidth >= 1024) return 24;
    return 14;
  }

  /**
   * Arranges cards in 3D stack based on distance from current front card.
   */
  setupStack(animate = true) {
    const stepY = this.getStepY();

    this.cards.forEach((card, i) => {
      // relative offset from front card (0 = active front, 1 = behind, 2 = further behind, etc.)
      const offset = (i - this.currentIndex + this.totalCards) % this.totalCards;

      let scale = 1;
      let y = 0;
      let zIndex = 40;
      let opacity = 1;
      let pointerEvents = 'none';

      if (offset === 0) {
        // 1st (Active Front Card)
        scale = 1;
        y = 0;
        zIndex = 40;
        opacity = 1;
        pointerEvents = 'auto';
      } else if (offset === 1) {
        // 2nd (Slightly behind, peeking with reduced scale)
        scale = 0.94;
        y = stepY;
        zIndex = 30;
        opacity = 0.96;
      } else if (offset === 2) {
        // 3rd (Further behind)
        scale = 0.88;
        y = stepY * 2;
        zIndex = 20;
        opacity = 0.86;
      } else if (offset === 3) {
        // 4th (Base of visible deck)
        scale = 0.82;
        y = stepY * 3;
        zIndex = 10;
        opacity = 0.65;
      } else {
        // Deeper cards hidden
        scale = 0.76;
        y = stepY * 4;
        zIndex = 0;
        opacity = 0;
      }

      const animProps = {
        x: 0,
        y: y,
        scale: scale,
        rotation: 0,
        zIndex: zIndex,
        opacity: opacity,
        transformOrigin: '50% 100%',
        duration: animate ? 0.45 : 0,
        ease: 'power3.out',
        overwrite: 'auto'
      };

      if (typeof gsap !== 'undefined') {
        gsap.to(card, animProps);
      } else {
        card.style.transform = `translate3d(0, ${y}px, 0) scale(${scale})`;
        card.style.zIndex = zIndex;
        card.style.opacity = opacity;
      }

      card.style.pointerEvents = pointerEvents;
    });

    this.createOrUpdateDots();
  }

  getFrontCard() {
    return this.cards[this.currentIndex];
  }

  getNextCard() {
    return this.cards[(this.currentIndex + 1) % this.totalCards];
  }

  getThirdCard() {
    return this.cards[(this.currentIndex + 2) % this.totalCards];
  }

  bindEvents() {
    const stage = this.container;

    // Mouse drag events
    stage.addEventListener('mousedown', (e) => this.handleDragStart(e));
    window.addEventListener('mousemove', (e) => this.handleDragMove(e));
    window.addEventListener('mouseup', (e) => this.handleDragEnd(e));

    // Touch drag events
    stage.addEventListener('touchstart', (e) => this.handleDragStart(e), { passive: true });
    window.addEventListener('touchmove', (e) => this.handleDragMove(e), { passive: false });
    window.addEventListener('touchend', (e) => this.handleDragEnd(e));
    window.addEventListener('touchcancel', (e) => this.handleDragEnd(e));

    // Arrow button controls
    if (this.prevBtn) {
      this.prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        this.swipeCard(-1);
      });
    }
    if (this.nextBtn) {
      this.nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        this.swipeCard(1);
      });
    }

    // Intercept clicks on cards when dragging has occurred
    this.cards.forEach(card => {
      card.addEventListener('click', (e) => {
        if (this.hasMoved) {
          e.preventDefault();
          e.stopPropagation();
        }
      }, true);
    });
  }

  handleDragStart(e) {
    if (this.isAnimating) return;

    // If user clicked directly on interactive form controls/buttons, allow natural click
    const target = e.target;
    if (target.closest('button, a, input, select')) {
      // Allow button click to proceed
    }

    const point = e.touches ? e.touches[0] : e;
    this.startX = point.clientX;
    this.startY = point.clientY;
    this.currentX = point.clientX;
    this.currentY = point.clientY;
    this.startTime = Date.now();
    this.isDragging = true;
    this.hasMoved = false;

    const frontCard = this.getFrontCard();
    if (frontCard) {
      frontCard.style.cursor = 'grabbing';
    }
  }

  handleDragMove(e) {
    if (!this.isDragging || this.isAnimating) return;

    const point = e.touches ? e.touches[0] : e;
    this.currentX = point.clientX;
    this.currentY = point.clientY;

    const deltaX = this.currentX - this.startX;
    const deltaY = this.currentY - this.startY;

    if (Math.hypot(deltaX, deltaY) > 6) {
      this.hasMoved = true;
      if (e.cancelable && e.touches) {
        e.preventDefault(); // lock vertical page scrolling while actively swiping 3D card
      }
    }

    const frontCard = this.getFrontCard();
    if (!frontCard) return;

    const isDesktop = window.innerWidth >= 1024;
    const stepY = this.getStepY();

    // Smooth rotation dynamically responding to swipe distance
    const rotation = deltaX * 0.055; // gentle, natural tilt
    const liftY = deltaY * 0.2;
    const scale = Math.max(0.96, 1 - Math.abs(deltaX) / 3200);

    if (typeof gsap !== 'undefined') {
      gsap.set(frontCard, {
        x: deltaX,
        y: liftY,
        rotation: rotation,
        scale: scale,
        transformOrigin: '50% 100%'
      });

      // Smoothly lift the card underneath forward in 3D depth
      const maxDist = isDesktop ? 220 : 160;
      const progress = Math.min(1, Math.abs(deltaX) / maxDist);
      const nextCard = this.getNextCard();
      if (nextCard) {
        gsap.set(nextCard, {
          scale: 0.94 + 0.06 * progress,
          y: stepY - stepY * progress,
          opacity: 0.96 + 0.04 * progress,
          transformOrigin: '50% 100%'
        });
      }

      const thirdCard = this.getThirdCard();
      if (thirdCard) {
        gsap.set(thirdCard, {
          scale: 0.88 + 0.06 * progress,
          y: (stepY * 2) - stepY * progress,
          opacity: 0.86 + 0.10 * progress,
          transformOrigin: '50% 100%'
        });
      }
    }
  }

  handleDragEnd(e) {
    if (!this.isDragging) return;
    this.isDragging = false;

    const frontCard = this.getFrontCard();
    if (frontCard) {
      frontCard.style.cursor = 'grab';
    }

    const deltaX = this.currentX - this.startX;
    const elapsed = Math.max(1, Date.now() - this.startTime);
    const velocity = deltaX / elapsed;

    const isDesktop = window.innerWidth >= 1024;
    const swipeThreshold = isDesktop ? 120 : 80;

    // Threshold: moved > threshold OR fast flick velocity > 0.42
    if (Math.abs(deltaX) > swipeThreshold || Math.abs(velocity) > 0.42) {
      const direction = deltaX > 0 ? 1 : -1;
      this.completeSwipe(direction);
    } else {
      // Spring back smoothly
      this.cancelSwipe();
    }
  }

  completeSwipe(direction) {
    this.isAnimating = true;
    const frontCard = this.getFrontCard();
    const throwDistance = (window.innerWidth || 1200) + 300;
    const exitX = direction * throwDistance;
    const exitRotation = direction * 28;

    if (typeof gsap !== 'undefined') {
      // 1. Physically throw front card outside visible stack
      gsap.to(frontCard, {
        x: exitX,
        rotation: exitRotation,
        scale: 0.92,
        opacity: 0,
        duration: 0.38,
        ease: 'power2.out',
        onComplete: () => {
          // Advance index and cycle deck
          this.currentIndex = (this.currentIndex + 1) % this.totalCards;
          this.isAnimating = false;
          this.setupStack(true);
        }
      });

      // 2. Animate next card smoothly forward into the front position
      const nextCard = this.getNextCard();
      if (nextCard) {
        gsap.to(nextCard, {
          scale: 1,
          y: 0,
          opacity: 1,
          duration: 0.38,
          ease: 'power2.out'
        });
      }

      const thirdCard = this.getThirdCard();
      if (thirdCard) {
        const stepY = this.getStepY();
        gsap.to(thirdCard, {
          scale: 0.94,
          y: stepY,
          opacity: 0.96,
          duration: 0.38,
          ease: 'power2.out'
        });
      }
    } else {
      this.currentIndex = (this.currentIndex + 1) % this.totalCards;
      this.isAnimating = false;
      this.setupStack(false);
    }
  }

  cancelSwipe() {
    this.isAnimating = true;
    const frontCard = this.getFrontCard();

    if (typeof gsap !== 'undefined') {
      gsap.to(frontCard, {
        x: 0,
        y: 0,
        rotation: 0,
        scale: 1,
        duration: 0.48,
        ease: 'elastic.out(1, 0.75)',
        onComplete: () => {
          this.isAnimating = false;
          this.setupStack(false);
        }
      });

      const stepY = this.getStepY();
      const nextCard = this.getNextCard();
      if (nextCard) {
        gsap.to(nextCard, { scale: 0.94, y: stepY, opacity: 0.96, duration: 0.35, ease: 'power2.out' });
      }

      const thirdCard = this.getThirdCard();
      if (thirdCard) {
        gsap.to(thirdCard, { scale: 0.88, y: stepY * 2, opacity: 0.86, duration: 0.35, ease: 'power2.out' });
      }
    } else {
      this.isAnimating = false;
      this.setupStack(false);
    }
  }

  swipeCard(direction) {
    if (this.isAnimating) return;
    this.completeSwipe(direction);
  }

  goToIndex(index) {
    if (this.isAnimating || index === this.currentIndex) return;
    this.currentIndex = index % this.totalCards;
    this.setupStack(true);
  }

  createOrUpdateDots() {
    if (!this.dotsContainer) return;

    if (this.dotsContainer.children.length === 0) {
      for (let i = 0; i < this.totalCards; i++) {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'deck-dot transition-all duration-300 rounded-full focus:outline-none';
        dot.style.width = '10px';
        dot.style.height = '10px';
        dot.style.backgroundColor = i === 0 ? this.accentColor : '#d1fae5';
        dot.setAttribute('aria-label', `Go to card ${i + 1}`);
        dot.addEventListener('click', () => this.goToIndex(i));
        this.dotsContainer.appendChild(dot);
      }
    } else {
      const dots = this.dotsContainer.querySelectorAll('.deck-dot');
      dots.forEach((dot, idx) => {
        if (idx === this.currentIndex) {
          dot.style.backgroundColor = this.accentColor;
          dot.style.width = '14px';
          dot.style.height = '14px';
        } else {
          dot.style.backgroundColor = '#d1fae5';
          dot.style.width = '10px';
          dot.style.height = '10px';
        }
      });
    }
  }
}

// Global factory initializer
window.initCardDeck = function(containerSelector, options) {
  return new CardDeck(containerSelector, options);
};
