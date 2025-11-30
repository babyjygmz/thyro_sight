// Smooth scrolling for navigation links
document.addEventListener('DOMContentLoaded', function() {
    // Highlight current page in navigation
    highlightCurrentPage();
    
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = targetSection.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Smooth scrolling for footer links
    const footerLinks = document.querySelectorAll('.footer-section a[href^="#"]');
    
    footerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = targetSection.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Header background change on scroll
    const header = document.querySelector('.header');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.style.background = 'rgba(255, 255, 255, 0.95)';
            header.style.backdropFilter = 'blur(20px)';
        } else {
            header.style.background = 'linear-gradient(135deg, var(--white) 0%, var(--light-gray) 100%)';
            header.style.backdropFilter = 'blur(10px)';
        }
    });

    // Add active state to navigation links based on scroll position
    const sections = document.querySelectorAll('.section');
    
    window.addEventListener('scroll', function() {
        let current = '';
        const headerHeight = header.offsetHeight;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop - headerHeight - 100;
            const sectionHeight = section.offsetHeight;
            
            if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });

    // Add scroll-triggered animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all cards and team members for animation
    const animatedElements = document.querySelectorAll('.about-card, .team-member');
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });

    // Add parallax effect to floating shapes in header
    const floatingShapes = document.querySelectorAll('.shape');
    
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        
        floatingShapes.forEach((shape, index) => {
            const speed = 0.5 + (index * 0.1);
            shape.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });

    // Add interactive effects to buttons
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Add typing effect to the main heading
    const mainHeading = document.querySelector('.main-heading');
    if (mainHeading) {
        const text = mainHeading.textContent;
        mainHeading.textContent = '';
        
        let i = 0;
        const typeWriter = () => {
            if (i < text.length) {
                mainHeading.textContent += text.charAt(i);
                i++;
                setTimeout(typeWriter, 100);
            }
        };
        
        // Start typing effect after a short delay
        setTimeout(typeWriter, 500);
    }

    // Add pulse effect to thyroid gland on scroll
    const thyroidGland = document.querySelector('.thyroid-gland');
    
    if (thyroidGland) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            thyroidGland.style.transform = `translateX(-50%) translateY(${rate}px)`;
        });
    }

    // Add counter animation for tech stack
    const techItems = document.querySelectorAll('.tech-item');
    
    const animateCounters = () => {
        techItems.forEach(item => {
            const icon = item.querySelector('i');
            if (icon) {
                icon.style.animation = 'bounce 0.6s ease';
                setTimeout(() => {
                    icon.style.animation = '';
                }, 600);
            }
        });
    };

    // Trigger counter animation when developers section comes into view
    const developersSection = document.querySelector('#developers');
    if (developersSection) {
        const devObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(animateCounters, 500);
                    devObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        devObserver.observe(developersSection);
    }

    // Add CSS for active navigation state
    const style = document.createElement('style');
    style.textContent = `
        .nav-link.active {
            color: var(--primary-blue) !important;
        }
        
        .nav-link.active::after {
            width: 100% !important;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
    `;
    document.head.appendChild(style);

    // Add scroll progress indicator
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-blue), var(--light-green));
        z-index: 1001;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);

    window.addEventListener('scroll', function() {
        const scrolled = (window.pageYOffset / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        progressBar.style.width = scrolled + '%';
    });

    // Add floating action button for quick navigation
    const fab = document.createElement('div');
    fab.innerHTML = '<i class="fas fa-arrow-up"></i>';
    fab.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-blue), var(--light-green));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        z-index: 1000;
        opacity: 0;
        transform: translateY(100px);
    `;
    document.body.appendChild(fab);

    // Show/hide FAB based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            fab.style.opacity = '1';
            fab.style.transform = 'translateY(0)';
        } else {
            fab.style.opacity = '0';
            fab.style.transform = 'translateY(100px)';
        }
    });

    // FAB click event
    fab.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // FAB hover effects
    fab.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px) scale(1.1)';
        this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.4)';
    });

    fab.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
        this.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
    });
});

// Navigation highlighting function
function highlightCurrentPage() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const authButtons = document.querySelectorAll('.auth-buttons .btn');
    
    console.log('Current page:', currentPage);
    console.log('Found auth buttons:', authButtons.length);
    
    authButtons.forEach(button => {
        // Remove any existing active class
        button.classList.remove('active');
        
        // Check if this button links to the current page
        const href = button.getAttribute('href');
        console.log('Button href:', href, 'Current page:', currentPage);
        
        // For login page, highlight the Log In button
        if (currentPage === 'login.html' && href === 'login.html') {
            button.classList.add('active');
            console.log('Added active class to Log In button');
        }
        // For signup page, highlight the Sign Up button
        else if (currentPage === 'signup.html' && href === 'signup.html') {
            button.classList.add('active');
            console.log('Added active class to Sign Up button');
        }
        // For forgot-password page, highlight the Log In button
        else if (currentPage === 'forgot-password.html' && href === 'login.html') {
            button.classList.add('active');
            console.log('Added active class to Log In button');
        }
        // For signup-success page, highlight the Sign Up button
        else if (currentPage === 'signup-success.html' && href === 'signup.html') {
            button.classList.add('active');
            console.log('Added active class to Sign Up button');
        }
        // For landing page (index.html), no highlighting
        else if (currentPage === 'index.html') {
            // No buttons should be highlighted on landing page
            console.log('Landing page - no highlighting');
        }
    });
}
