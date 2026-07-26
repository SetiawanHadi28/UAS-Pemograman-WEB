document.addEventListener('DOMContentLoaded', () => {

    // ============================================================
    // 1. MATRIX DIGITAL RAIN BACKGROUND
    // ============================================================
    const canvas = document.getElementById('bg-canvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let W = canvas.width = window.innerWidth;
        let H = canvas.height = window.innerHeight;

        // Characters — binary + katakana + special chars
        const CHARS = '01アイウエオカキクケコサシスセソタチツテトナニヌネノ<>{}[]()ABCDEFabcdef01234789!@#$%^&*';
        const FONT_SIZE = 14;
        const cols = Math.floor(W / FONT_SIZE);
        const drops = new Array(cols).fill(1).map(() => Math.random() * -50);
        const speeds = new Array(cols).fill(0).map(() => 0.3 + Math.random() * 0.7);

        // Color palette — neon green primary, occasional blue/purple
        const COLORS = [
            '#00ff88', '#00ff88', '#00ff88', // mostly green
            '#00c8ff', // occasional blue
            '#bf5fff', // rare purple
        ];
        const colColors = drops.map(() => COLORS[Math.floor(Math.random() * COLORS.length)]);

        function drawMatrix() {
            // Semi-transparent black overlay — creates trail effect
            ctx.fillStyle = 'rgba(8, 11, 20, 0.06)';
            ctx.fillRect(0, 0, W, H);

            for (let i = 0; i < cols; i++) {
                const char = CHARS[Math.floor(Math.random() * CHARS.length)];
                const x = i * FONT_SIZE;
                const y = drops[i] * FONT_SIZE;

                // Bright head character
                ctx.font = `bold ${FONT_SIZE}px 'JetBrains Mono', monospace`;
                ctx.fillStyle = '#ffffff';
                ctx.globalAlpha = 0.9;
                ctx.fillText(char, x, y);

                // Main colored body
                ctx.fillStyle = colColors[i];
                ctx.globalAlpha = 0.6;
                ctx.font = `${FONT_SIZE}px 'JetBrains Mono', monospace`;
                ctx.fillText(CHARS[Math.floor(Math.random() * CHARS.length)], x, y + FONT_SIZE);

                // Faded trail
                ctx.fillStyle = colColors[i];
                ctx.globalAlpha = 0.2;
                ctx.fillText(CHARS[Math.floor(Math.random() * CHARS.length)], x, y + FONT_SIZE * 2);

                ctx.globalAlpha = 1;

                // Reset drop randomly
                if (y > H && Math.random() > 0.975) {
                    drops[i] = 0;
                    colColors[i] = COLORS[Math.floor(Math.random() * COLORS.length)];
                }
                drops[i] += speeds[i];
            }
        }

        let matrixInterval = setInterval(drawMatrix, 50);

        window.addEventListener('resize', () => {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
            clearInterval(matrixInterval);
            matrixInterval = setInterval(drawMatrix, 50);
        });
    }

    // ============================================================
    // 2. TYPING ANIMATION FOR HERO LABEL
    // ============================================================
    const label = document.querySelector('.top-card-info .label');
    if (label) {
        const originalText = label.textContent.trim();
        label.textContent = '';
        label.style.borderRight = '2px solid #00ff88';

        let i = 0;
        const typeTimer = setInterval(() => {
            label.textContent += originalText[i];
            i++;
            if (i >= originalText.length) {
                clearInterval(typeTimer);
                label.style.borderRight = 'none';
            }
        }, 60);
    }

    // ============================================================
    // 3. INTERSECTION OBSERVER — FADE IN ELEMENTS
    // ============================================================
    const fadeElements = document.querySelectorAll('.fade-in');
    const fadeObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -40px 0px', threshold: 0.08 });

    fadeElements.forEach(el => fadeObserver.observe(el));

    // ============================================================
    // 4. COUNTER ANIMATION (STAT NUMBERS)
    // ============================================================
    const statNumbers = document.querySelectorAll('.stat-number');
    let statsAnimated = false;

    const statObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !statsAnimated) {
                statsAnimated = true;
                statNumbers.forEach(stat => {
                    const target = parseFloat(stat.getAttribute('data-target'));
                    const isDecimal = target % 1 !== 0;
                    const duration = 2000;
                    const frameDuration = 1000 / 60;
                    const totalFrames = Math.round(duration / frameDuration);
                    let frame = 0;

                    // Add neon glow pulse on count
                    stat.style.filter = 'drop-shadow(0 0 20px rgba(0,255,136,0.6))';

                    const counter = setInterval(() => {
                        frame++;
                        const progress = frame / totalFrames;
                        const eased = 1 - Math.pow(1 - progress, 3);
                        const current = target * eased;
                        stat.textContent = isDecimal ? current.toFixed(2) : Math.floor(current);
                        if (frame >= totalFrames) {
                            clearInterval(counter);
                            stat.textContent = isDecimal ? target.toFixed(2) : target;
                            stat.style.filter = 'drop-shadow(0 0 8px rgba(0,255,136,0.25))';
                        }
                    }, frameDuration);
                });
            }
        });
    }, { threshold: 0.5 });

    const statsSection = document.querySelector('.stats-row');
    if (statsSection) statObserver.observe(statsSection);

    // ============================================================
    // 5. SMOOTH SCROLL FOR ANCHOR LINKS
    // ============================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offset = 100;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top, behavior: 'smooth' });
            }
        });
    });

    // ============================================================
    // 6. ACTIVE LINK HIGHLIGHTING ON SCROLL
    // ============================================================
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.sidebar-menu a[href^="#"]');

    const sectionObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                navLinks.forEach(link => link.classList.remove('active'));
                const activeLink = document.querySelector(`.sidebar-menu a[href="#${entry.target.id}"]`);
                if (activeLink) activeLink.classList.add('active');
            }
        });
    }, { rootMargin: '-30% 0px -60% 0px', threshold: 0 });

    sections.forEach(section => sectionObserver.observe(section));

    // ============================================================
    // 7. NAVBAR SCROLL EFFECT
    // ============================================================
    const navTop = document.querySelector('.nav-top');
    if (navTop) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                navTop.style.background = 'rgba(8, 11, 20, 0.98)';
                navTop.style.boxShadow = '0 4px 30px rgba(0,0,0,0.5), 0 0 20px rgba(0,255,136,0.04)';
            } else {
                navTop.style.background = 'rgba(8, 11, 20, 0.94)';
                navTop.style.boxShadow = 'none';
            }
        }, { passive: true });
    }

    // ============================================================
    // 8. SKILL TAGS STAGGER ANIMATION
    // ============================================================
    const skillTags = document.querySelectorAll('.skill-tag');
    skillTags.forEach((tag, i) => {
        tag.style.transitionDelay = `${i * 0.045}s`;
        tag.style.opacity = '0';
        tag.style.transform = 'translateY(8px) scale(0.95)';
    });

    const skillsSection = document.querySelector('.skills-container');
    if (skillsSection) {
        const skillsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    skillTags.forEach(tag => {
                        tag.style.opacity = '1';
                        tag.style.transform = 'translateY(0) scale(1)';
                        tag.style.transition = 'opacity 0.4s ease, transform 0.4s ease, box-shadow 0.25s ease, border-color 0.25s ease';
                    });
                    skillsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });
        skillsObserver.observe(skillsSection);
    }

    // ============================================================
    // 9. TIMELINE ITEMS — STAGGER SLIDE IN
    // ============================================================
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach((item, i) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-15px)';
        item.style.transition = `opacity 0.5s ease ${i * 0.1}s, transform 0.5s ease ${i * 0.1}s`;
    });

    const timelineObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateX(0)';
            }
        });
    }, { threshold: 0.1 });

    timelineItems.forEach(item => timelineObserver.observe(item));

    // ============================================================
    // 10. MOUSE PARALLAX ON HERO CARD
    // ============================================================
    const heroCard = document.querySelector('.top-card');
    if (heroCard) {
        heroCard.addEventListener('mousemove', (e) => {
            const rect = heroCard.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;
            const dx = (e.clientX - cx) / rect.width;
            const dy = (e.clientY - cy) / rect.height;
            heroCard.style.transform = `perspective(1000px) rotateY(${dx * 3}deg) rotateX(${-dy * 2}deg)`;
        });

        heroCard.addEventListener('mouseleave', () => {
            heroCard.style.transform = 'perspective(1000px) rotateY(0) rotateX(0)';
            heroCard.style.transition = 'transform 0.5s ease';
        });
    }

    // ============================================================
    // 11. CONSOLE EASTER EGG
    // ============================================================
    console.log('%c⚡ CV SYSTEM INITIALIZED', 'color: #00ff88; font-size: 16px; font-family: monospace; font-weight: bold;');
    console.log('%c> Welcome to the Matrix, developer!', 'color: #00c8ff; font-family: monospace;');
    console.log('%c  Built with ❤️  using pure HTML/CSS/PHP', 'color: #bf5fff; font-family: monospace;');

});

