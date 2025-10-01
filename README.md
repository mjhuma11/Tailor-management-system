# The Stitch House - Tailor Shop Management System

A comprehensive web-based tailor shop management system built with HTML, CSS, Bootstrap 5, jQuery frontend and designed for PHP/MySQL backend integration.

## 🎯 Project Overview

The Stitch House is a modern, responsive website for a tailor shop that provides:
- Professional business presence
- Customer engagement platform  
- Online order management system
- Measurement submission system
- Gallery showcase
- Contact and booking system

## ✨ Features

### Frontend Features
- **Responsive Design**: Mobile-first approach with Bootstrap 5
- **Modern UI/UX**: Beautiful animations and interactive elements
- **Service Showcase**: Complete tailoring services display
- **Product Gallery**: Filterable product categories
- **Customer Portal**: Login/Register system
- **Measurement Guide**: Interactive measurement submission
- **Contact System**: Contact form with map integration
- **Social Integration**: Social media links and sharing

### Backend Ready Features (Database Structure)
- **User Management**: Admin, Manager, Staff roles
- **Customer Management**: Complete customer profiles
- **Order Management**: Order tracking and status updates
- **Measurement System**: Digital measurement storage
- **Inventory Management**: Staff and expense tracking
- **Communication**: Email and SMS system
- **Document Management**: File attachment system
- **Financial Tracking**: Income and expense management

## 🛠️ Technology Stack

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Custom styling with CSS variables
- **Bootstrap 5**: Responsive framework
- **jQuery**: Interactive functionality
- **Font Awesome**: Icons
- **Google Fonts**: Typography (Poppins, Playfair Display)

### Backend (Ready for Integration)
- **PHP**: Server-side scripting
- **MySQL**: Database management
- **Database Structure**: Complete schema provided

## 📁 Project Structure

```
The-stitch-house/
├── index.html              # Homepage
├── login.html              # Customer login
├── register.html           # Customer registration
├── assets/
│   ├── css/
│   │   └── style.css      # Custom styles
│   ├── js/
│   │   └── script.js      # jQuery functionality
│   └── images/
│       └── README.md      # Image requirements
├── DB/
│   └── stitch(1).sql      # Database schema
└── README.md              # This file
```

## 🚀 Setup Instructions

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.4 or higher (for backend)
- MySQL 5.7 or higher (for backend)
- Modern web browser

### Frontend Setup (Current)
1. **Clone/Download** the project files
2. **Place files** in your web server directory
3. **Add images** following the guide in `assets/images/README.md`
4. **Access** the website via your web server

### Backend Setup (Future)
1. **Import database** from `DB/stitch(1).sql`
2. **Configure database** connection
3. **Create PHP backend** files
4. **Implement authentication** system
5. **Add order management** functionality

## 🎨 Customization

### Colors
The website uses CSS custom properties for easy theming:
```css
:root {
    --primary-color: #2c3e50;
    --secondary-color: #e74c3c;
    --accent-color: #f39c12;
}
```

### Fonts
- **Headers**: Playfair Display (serif)
- **Body**: Poppins (sans-serif)

### Images
Add your own images following the specifications in `assets/images/README.md`

## 📱 Responsive Breakpoints

- **Mobile**: < 576px
- **Tablet**: 576px - 768px  
- **Desktop**: 768px - 992px
- **Large Desktop**: > 992px

## 🔗 Key Pages & Sections

### Homepage (`index.html`)
- Hero section with call-to-action
- About us with features
- Services showcase
- Product catalog with filtering
- Portfolio gallery
- Customer testimonials
- Measurement guide
- Contact form with map
- Newsletter subscription

### Authentication
- **Login**: `login.html`
- **Register**: `register.html`

## 🗄️ Database Schema

The project includes a complete MySQL database schema with:

### Core Tables
- `users` - System users (admin, staff)
- `customer` - Customer information
- `staff` - Staff management
- `order` - Order tracking
- `measurement` - Customer measurements

### Management Tables
- `expanse` & `exp_cat` - Expense tracking
- `income` & `inc_cat` - Income tracking
- `documents` - File management
- `email` & `sms` - Communication logs

## 🎯 Business Features

### For Customers
- Browse services and products
- Submit measurements online
- Create account and login
- Contact and booking
- View portfolio

### For Business (Backend Ready)
- Order management
- Customer database
- Staff management
- Financial tracking
- Communication system
- Document storage

## 🌐 Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 📞 Support & Contact

For questions about this project:
- Check the code comments
- Review the database schema
- Follow the setup instructions

## 📄 License

This project is created for The Stitch House business. Customize as needed for your tailoring business.

## 🚧 Development Roadmap

### Phase 1: Frontend ✅
- [x] Responsive homepage
- [x] Authentication pages
- [x] Interactive features
- [x] Modern design

### Phase 2: Backend (Next)
- [ ] PHP authentication system
- [ ] Database integration
- [ ] Order management
- [ ] Admin dashboard
- [ ] Customer portal

### Phase 3: Advanced Features
- [ ] Online payment integration
- [ ] Appointment booking
- [ ] Mobile app
- [ ] Advanced analytics

## 🎨 Design Credits

- **Icons**: Font Awesome
- **Fonts**: Google Fonts
- **Framework**: Bootstrap 5
- **Colors**: Custom color scheme
- **Layout**: Custom responsive design

---

**The Stitch House** - Perfect Fit, Perfect Style ✂️👔👗