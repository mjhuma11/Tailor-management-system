<?php
/**
 * Homepage with Session-based Navigation
 * The Stitch House - Main Website
 */

require_once 'backend/config.php';

// Get current user info if logged in
$currentUser = null;
$currentCustomer = null;
$isLoggedIn = isUserLoggedIn();

if ($isLoggedIn) {
    $currentUser = getCurrentUser($pdo);
    if ($currentUser && $currentUser['role'] === 'customer') {
        $currentCustomer = getCurrentCustomer($pdo);
    }
}

// Get flash message
$flashMessage = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Stitch House - Perfect Fit, Perfect Style</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header / Navbar -->
    <header class="navbar-section">
        <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-white shadow-sm">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand" href="#home">
                    <img src="assets/images/logo.png" height="50" class="d-none d-sm-inline">
                    <span class="brand-text ms-2">The Stitch House</span>
                </a>

                <!-- Mobile Toggle Button -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navigation Menu -->
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto me-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#about">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#services">Services</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#products">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#gallery">Gallery</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#contact">Contact</a>
                        </li>
                    </ul>
                    
                    <!-- Dynamic Navigation based on login status -->
                    <div class="d-flex">
                        <?php if ($isLoggedIn): ?>
                            <!-- Logged in user menu -->
                            <div class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle btn btn-outline-primary" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle"></i> <?= htmlspecialchars($currentUser['name']) ?>
                                </a>
                                <ul class="dropdown-menu">
                                    <?php if (in_array($currentUser['role'], ['admin', 'manager'])): ?>
                                        <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a></li>
                                    <?php endif; ?>
                                    
                                    <?php if ($currentUser['role'] === 'customer'): ?>
                                        <li><a class="dropdown-item" href="customer-dashboard.php"><i class="fas fa-user"></i> My Dashboard</a></li>
                                        <li><a class="dropdown-item" href="my-orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                                        <li><a class="dropdown-item" href="my-profile.php"><i class="fas fa-edit"></i> My Profile</a></li>
                                    <?php endif; ?>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="backend/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <!-- Not logged in - show login/register buttons -->
                            <a href="login.html" class="btn btn-outline-primary me-2">Login</a>
                            <a href="register.html" class="btn btn-primary">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Flash Message -->
    <?php if ($flashMessage): ?>
        <div class="alert alert-<?= $flashMessage['type'] === 'error' ? 'danger' : $flashMessage['type'] ?> alert-dismissible fade show" style="margin-top: 80px;">
            <div class="container">
                <?= htmlspecialchars($flashMessage['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-background">
            <div class="container">
                <div class="row align-items-center min-vh-100">
                    <div class="col-lg-6">
                        <div class="hero-content">
                            <?php if ($isLoggedIn): ?>
                                <h1 class="hero-title mb-4">Welcome Back, <?= htmlspecialchars($currentUser['name']) ?>!</h1>
                                <p class="hero-subtitle mb-4">
                                    Ready for your next custom tailoring project? We're here to create the perfect fit for you.
                                </p>
                            <?php else: ?>
                                <h1 class="hero-title mb-4">Perfect Fit, Perfect Style</h1>
                                <p class="hero-subtitle mb-4">
                                    Experience premium tailoring services with over 20 years of expertise. 
                                    From custom designs to alterations, we bring your fashion dreams to life.
                                </p>
                            <?php endif; ?>
                            <div class="hero-buttons">
                                <a href="#contact" class="btn btn-primary btn-lg me-3">Order Now</a>
                                <a href="#services" class="btn btn-outline-light btn-lg">Book Appointment</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image">
                            <img src="assets/images/hero-banner.jpg" alt="Tailoring Services" class="img-fluid rounded-3 shadow-lg">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Down Indicator -->
        <div class="scroll-indicator">
            <a href="#about" class="scroll-down">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>   
 <!-- About Us Section -->
    <section id="about" class="about-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">About The Stitch House</h2>
                    <p class="section-subtitle">Crafting Excellence Since 2000</p>
                </div>
            </div>
            
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4">
                    <div class="about-content">
                        <h3 class="mb-3">Your Trusted Tailoring Partner</h3>
                        <p class="mb-4">
                            For over two decades, The Stitch House has been synonymous with quality, precision, and style. 
                            Our master tailors combine traditional craftsmanship with modern techniques to create garments 
                            that not only fit perfectly but also reflect your unique personality.
                        </p>
                        
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <div class="feature-box">
                                    <i class="fas fa-cut feature-icon"></i>
                                    <h5>Custom Design</h5>
                                    <p>Personalized designs tailored to your preferences</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="feature-box">
                                    <i class="fas fa-ring feature-icon"></i>
                                    <h5>Bridal Wear</h5>
                                    <p>Exquisite wedding dresses and gowns</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="feature-box">
                                    <i class="fas fa-user-tie feature-icon"></i>
                                    <h5>Men's Tailoring</h5>
                                    <p>Professional suits and formal wear</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="feature-box">
                                    <i class="fas fa-clock feature-icon"></i>
                                    <h5>Quick Service</h5>
                                    <p>Fast turnaround without compromising quality</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="about-images">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <img src="assets/images/about-1.jpg" alt="Tailoring Process" class="img-fluid rounded shadow">
                            </div>
                            <div class="col-6 mb-3">
                                <img src="assets/images/about-2.jpg" alt="Master Tailor" class="img-fluid rounded shadow">
                            </div>
                            <div class="col-12">
                                <img src="assets/images/about-3.jpg" alt="Shop Interior" class="img-fluid rounded shadow">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Our Services</h2>
                    <p class="section-subtitle">Complete Tailoring Solutions</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="service-card h-100">
                        <div class="service-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <h4>Custom Design Tailoring</h4>
                        <p>Create unique garments from scratch based on your vision and preferences. Our designers work closely with you to bring your ideas to life.</p>
                        <a href="#contact" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="service-card h-100">
                        <div class="service-icon">
                            <i class="fas fa-cut"></i>
                        </div>
                        <h4>Alteration & Repair</h4>
                        <p>Professional alterations to ensure perfect fit. From simple hemming to complex restructuring, we handle all types of modifications.</p>
                        <a href="#contact" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="service-card h-100">
                        <div class="service-icon">
                            <i class="fas fa-ring"></i>
                        </div>
                        <h4>Wedding Collection</h4>
                        <p>Stunning bridal wear, gowns, suits, and traditional wedding attire. Make your special day even more memorable with our designs.</p>
                        <a href="#contact" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="service-card h-100">
                        <div class="service-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h4>Ready-Made Collection</h4>
                        <p>Browse our curated collection of ready-to-wear garments for immediate purchase. Quality fabrics and modern designs.</p>
                        <a href="#contact" class="btn btn-outline-primary">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="products-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Our Products</h2>
                    <p class="section-subtitle">Explore Our Fashion Categories</p>
                </div>
            </div>
            
            <!-- Product Categories -->
            <div class="row mb-4">
                <div class="col-lg-12">
                    <div class="product-filter text-center">
                        <button class="btn btn-outline-primary active" data-filter="all">All</button>
                        <button class="btn btn-outline-primary" data-filter="women">Women</button>
                        <button class="btn btn-outline-primary" data-filter="men">Men</button>
                        <button class="btn btn-outline-primary" data-filter="bridal">Bridal</button>
                        <button class="btn btn-outline-primary" data-filter="traditional">Traditional</button>
                    </div>
                </div>
            </div>
            
            <!-- Product Grid -->
            <div class="row product-grid">
                <div class="col-lg-4 col-md-6 mb-4 product-item women">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/product-1.jpg" alt="Women's Dress" class="img-fluid">
                            <div class="product-overlay">
                                <a href="#" class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#productModal">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="#contact" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-cart"></i> Order
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h5>Elegant Evening Dress</h5>
                            <p class="text-muted">Custom fitted evening dress with intricate detailing</p>
                            <span class="price">From $150</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4 product-item men">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/product-2.jpg" alt="Men's Suit" class="img-fluid">
                            <div class="product-overlay">
                                <a href="#" class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#productModal">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="#contact" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-cart"></i> Order
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h5>Premium Business Suit</h5>
                            <p class="text-muted">Tailored business suit for professional look</p>
                            <span class="price">From $250</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 mb-4 product-item bridal">
                    <div class="product-card">
                        <div class="product-image">
                            <img src="assets/images/product-3.jpg" alt="Wedding Dress" class="img-fluid">
                            <div class="product-overlay">
                                <a href="#" class="btn btn-light btn-sm me-2" data-bs-toggle="modal" data-bs-target="#productModal">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="#contact" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-cart"></i> Order
                                </a>
                            </div>
                        </div>
                        <div class="product-info">
                            <h5>Designer Wedding Gown</h5>
                            <p class="text-muted">Luxurious bridal gown with custom embellishments</p>
                            <span class="price">From $500</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="section-title">Contact Us</h2>
                    <p class="section-subtitle">Get in Touch for Your Custom Orders</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="contact-info">
                        <h4 class="mb-4">Get in Touch</h4>
                        
                        <div class="contact-item mb-3">
                            <i class="fas fa-map-marker-alt contact-icon"></i>
                            <div class="contact-details">
                                <h6>Address</h6>
                                <p>123 Fashion Street<br>Downtown, City 12345</p>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <i class="fas fa-phone contact-icon"></i>
                            <div class="contact-details">
                                <h6>Phone</h6>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <i class="fab fa-whatsapp contact-icon"></i>
                            <div class="contact-details">
                                <h6>WhatsApp</h6>
                                <p>+1 (555) 987-6543</p>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-3">
                            <i class="fas fa-envelope contact-icon"></i>
                            <div class="contact-details">
                                <h6>Email</h6>
                                <p>info@thestitchhouse.com</p>
                            </div>
                        </div>
                        
                        <div class="social-links mt-4">
                            <a href="#" class="social-link me-3"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="social-link me-3"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link me-3"><i class="fab fa-tiktok"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8 mb-4">
                    <div class="contact-form">
                        <h4 class="mb-4">Send Message</h4>
                        
                        <form id="contactForm" method="POST" action="backend/contact.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" class="form-control" id="contactName" name="name" placeholder="Your Name" 
                                           value="<?= $isLoggedIn ? htmlspecialchars($currentUser['name']) : '' ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="email" class="form-control" id="contactEmail" name="email" placeholder="Your Email" 
                                           value="<?= $isLoggedIn ? htmlspecialchars($currentUser['email']) : '' ?>" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input type="tel" class="form-control" id="contactPhone" name="phone" placeholder="Your Phone" 
                                           value="<?= ($isLoggedIn && $currentCustomer) ? htmlspecialchars($currentCustomer['phone']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <select class="form-select" id="contactService" name="service" required>
                                        <option value="">Select Service</option>
                                        <option value="custom_design">Custom Design</option>
                                        <option value="alteration">Alteration & Repair</option>
                                        <option value="wedding">Wedding Collection</option>
                                        <option value="ready_made">Ready-Made</option>
                                        <option value="consultation">Consultation</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" id="contactMessage" name="message" rows="5" placeholder="Your Message" required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2024 The Stitch House. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#contact" class="text-decoration-none me-3">Contact</a>
                    <a href="#about" class="text-decoration-none me-3">About</a>
                    <a href="#services" class="text-decoration-none">Services</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>