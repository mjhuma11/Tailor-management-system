// The Stitch House - JavaScript Functions
$(document).ready(function() {
    
    // Navbar scroll effect
    $(window).scroll(function() {
        if ($(this).scrollTop() > 50) {
            $('.navbar').addClass('scrolled');
            $('.scroll-to-top').fadeIn();
        } else {
            $('.navbar').removeClass('scrolled');
            $('.scroll-to-top').fadeOut();
        }
    });

    // Smooth scrolling for navigation links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 80
            }, 1000);
        }
    });

    // Update active nav link on scroll
    $(window).scroll(function() {
        var scrollPos = $(document).scrollTop() + 100;
        
        $('.navbar-nav a').each(function() {
            var currLink = $(this);
            var refElement = $(currLink.attr('href'));
            
            if (refElement.length && refElement.position().top <= scrollPos && refElement.position().top + refElement.height() > scrollPos) {
                $('.navbar-nav a').removeClass('active');
                currLink.addClass('active');
            }
        });
    });

    // Product filtering
    $('.product-filter .btn').on('click', function() {
        var filterValue = $(this).attr('data-filter');
        
        // Update active filter button
        $('.product-filter .btn').removeClass('active');
        $(this).addClass('active');
        
        // Filter products
        if (filterValue === 'all') {
            $('.product-item').fadeIn();
        } else {
            $('.product-item').hide();
            $('.product-item.' + filterValue).fadeIn();
        }
    });

    // Gallery lightbox effect (simple modal)
    $('.gallery-item').on('click', function() {
        var imageSrc = $(this).find('img').attr('src');
        var title = $(this).find('.gallery-overlay h5').text();
        var description = $(this).find('.gallery-overlay p').text();
        
        // Create modal content
        var modalContent = `
            <div class="modal fade" id="galleryModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="${imageSrc}" class="img-fluid rounded" alt="${title}">
                            <p class="mt-3">${description}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal and add new one
        $('#galleryModal').remove();
        $('body').append(modalContent);
        $('#galleryModal').modal('show');
    });

    // Product detail modal
    $('.product-card .btn[data-bs-toggle="modal"]').on('click', function(e) {
        e.preventDefault();
        var productCard = $(this).closest('.product-card');
        var productImage = productCard.find('.product-image img').attr('src');
        var productTitle = productCard.find('.product-info h5').text();
        var productDescription = productCard.find('.product-info p').text();
        var productPrice = productCard.find('.price').text();
        
        var modalContent = `
            <div class="modal fade" id="productModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${productTitle}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <img src="${productImage}" class="img-fluid rounded" alt="${productTitle}">
                                </div>
                                <div class="col-md-6">
                                    <h4>${productTitle}</h4>
                                    <p class="text-muted">${productDescription}</p>
                                    <h5 class="text-primary mb-4">${productPrice}</h5>
                                    <div class="mb-3">
                                        <label class="form-label">Size:</label>
                                        <select class="form-select">
                                            <option>Select Size</option>
                                            <option>Small</option>
                                            <option>Medium</option>
                                            <option>Large</option>
                                            <option>X-Large</option>
                                            <option>Custom Size</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Fabric:</label>
                                        <select class="form-select">
                                            <option>Select Fabric</option>
                                            <option>Cotton</option>
                                            <option>Silk</option>
                                            <option>Linen</option>
                                            <option>Wool</option>
                                            <option>Synthetic</option>
                                        </select>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-primary">Add to Cart</button>
                                        <button class="btn btn-outline-primary">Customize Order</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#productModal').remove();
        $('body').append(modalContent);
        $('#productModal').modal('show');
    });

    // Contact form submission (placeholder)
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        var formData = {
            name: $('#contactName').val(),
            email: $('#contactEmail').val(),
            phone: $('#contactPhone').val(),
            service: $('#contactService').val(),
            message: $('#contactMessage').val()
        };
        
        // Simple validation
        if (!formData.name || !formData.email || !formData.message) {
            alert('Please fill in all required fields.');
            return;
        }
        
        // Show success message (in real app, send to backend)
        alert('Thank you for your message! We will contact you soon.');
        this.reset();
    });

    // Measurement form submission
    $('#measurementForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get measurement data
        var measurements = {
            chest: $('#chest').val(),
            waist: $('#waist').val(),
            hips: $('#hips').val(),
            shoulder: $('#shoulder').val(),
            sleeve: $('#sleeve').val(),
            neck: $('#neck').val(),
            inseam: $('#inseam').val(),
            height: $('#height').val()
        };
        
        // Validate measurements
        var isValid = true;
        Object.keys(measurements).forEach(function(key) {
            if (!measurements[key] || isNaN(measurements[key])) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            alert('Please enter valid measurements for all fields.');
            return;
        }
        
        // Show success message
        alert('Measurements submitted successfully! We will use these for your custom orders.');
        $('#measurementModal').modal('hide');
    });

    // Scroll to top button
    $('.scroll-to-top').on('click', function() {
        $('html, body').animate({
            scrollTop: 0
        }, 800);
    });

    // Add scroll to top button to page
    $('body').append('<button class="scroll-to-top"><i class="fas fa-chevron-up"></i></button>');

    // Animate elements on scroll
    function animateOnScroll() {
        $('.loading').each(function() {
            var elementTop = $(this).offset().top;
            var windowBottom = $(window).scrollTop() + $(window).height();
            
            if (elementTop < windowBottom - 50) {
                $(this).addClass('loaded');
            }
        });
    }

    // Add loading class to animated elements
    $('.service-card, .product-card, .gallery-item, .testimonial-card, .feature-box').addClass('loading');
    
    // Trigger animations on scroll
    $(window).scroll(animateOnScroll);
    animateOnScroll(); // Initial check

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Phone number formatting
    $('#contactPhone, #measurementPhone').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        var formattedValue = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        $(this).val(formattedValue);
    });

    // Auto-resize textareas
    $('textarea').on('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });

    // Sticky header effect
    var headerHeight = $('.navbar').outerHeight();
    $('body').css('padding-top', headerHeight + 'px');

    // Lazy loading for images
    $('img').each(function() {
        var $img = $(this);
        var src = $img.attr('src');
        
        $img.on('load', function() {
            $img.fadeIn();
        }).on('error', function() {
            // Fallback image
            $img.attr('src', 'assets/images/placeholder.jpg');
        });
        
        // Initially hide images
        if (src && src !== '') {
            $img.hide();
        }
    });

    // Search functionality (for future implementation)
    $('#searchInput').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $('.product-item').each(function() {
            var productText = $(this).find('.product-info').text().toLowerCase();
            
            if (productText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Newsletter subscription
    $('#newsletterForm').on('submit', function(e) {
        e.preventDefault();
        var email = $('#newsletterEmail').val();
        
        if (!email || !isValidEmail(email)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        alert('Thank you for subscribing to our newsletter!');
        $('#newsletterEmail').val('');
    });

    // Email validation helper
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Mobile menu close on link click
    $('.navbar-nav a').on('click', function() {
        if ($(window).width() < 992) {
            $('.navbar-collapse').collapse('hide');
        }
    });

    // Preloader (if needed)
    $(window).on('load', function() {
        $('#preloader').fadeOut();
    });

    // Print functionality for orders
    $('.print-order').on('click', function() {
        window.print();
    });

    // Social media sharing
    $('.share-btn').on('click', function(e) {
        e.preventDefault();
        var platform = $(this).data('platform');
        var url = encodeURIComponent(window.location.href);
        var title = encodeURIComponent(document.title);
        
        var shareUrl = '';
        
        switch(platform) {
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'whatsapp':
                shareUrl = `https://wa.me/?text=${title} ${url}`;
                break;
        }
        
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    });

    // Initialize all components
    console.log('The Stitch House website loaded successfully!');
});