# 📸 Kislap - Photography Booking Platform

<div align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript">
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5">
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3">
</div>

<div align="center">
  <h3>🌟 Connect Clients with Professional Photographers 🌟</h3>
  <p>A modern, full-featured photography booking platform that bridges the gap between talented photographers and clients seeking professional photography services.</p>
</div>

---

## 🚀 Complete Feature List

### 👥 **Multi-User System**
- **Customers/Clients**: Browse, book, and manage photography sessions
- **Photographers/Workers**: Showcase portfolios, manage bookings, grow business
- **Administrators**: Platform management, user oversight, content moderation

### � **vAuthentication & Security**
- **Password Recovery**: Forgot password with email OTP
- **Input Validation**: Proper name capitalization enforcement
- **Session Management**: Secure login/logout with session protection

### 💬 **Real-time Communication**
- **Live Chat System**: Instant messaging between users and photographers
- **AI Chatbot**: Assists with initial booking requirements
- **File Sharing**: Send images, documents, and attachments
- **Message History**: Complete conversation records

### 📅 **Booking Management**
- **Complete Booking Lifecycle**: From inquiry to completion
- **Status Tracking**: Pending → Negotiating → Confirmed → Completed
- **Proposal System**: Photographers can propose prices and dates
- **Calendar Integration**: Availability management
- **Booking History**: Track all past and current bookings
- **Cancellation System**: Handle booking cancellations with reasons

### 💳 **Payment System**
- **Split Payments**: 50% deposit + 50% final payment
- **Payment Modals**: Detailed booking information before payment
- **Payment History**: Track all transactions
- **Automatic Calculations**: Deposit amounts calculated automatically

### 🔍 **Advanced Search & Discovery**
- **Multi-criteria Search**: Name, location, specialty, price range
- **Smart Filtering**: Category-based photographer discovery
- **Sorting Options**: Rating, reviews, price (low/high), newest
- **Portfolio Browsing**: Gallery view with lightbox
- **Photographer Profiles**: Detailed information and work samples

### ⭐ **Rating & Review System**
- **5-Star Rating**: Comprehensive rating system
- **Written Reviews**: Detailed feedback from clients
- **Average Ratings**: Calculated photographer ratings
- **Review History**: Track all ratings and feedback
- **Reputation Building**: Help photographers build credibility

### 📊 **Analytics & Dashboard**
- **Photographer Dashboard**: Earnings, bookings, performance metrics
- **Admin Analytics**: Platform usage, user statistics
- **Booking Statistics**: Track booking trends and success rates
- **Revenue Tracking**: Monitor platform and photographer earnings

### 🎨 **Portfolio Management**
- **Image Galleries**: Showcase photographer work
- **Portfolio Upload**: High-quality image management
- **Work Categorization**: Organize by photography type
- **Portfolio Validation**: Quality control for uploaded work

### 📦 **Package System**
- **Service Packages**: Photographers can create service offerings
- **Pricing Management**: Flexible pricing structures
- **Package Selection**: Clients can choose from available packages
- **Custom Bookings**: Non-package custom arrangements

### 👨‍💼 **Admin Management**
- **User Management**: View, edit, suspend, ban users
- **Application Review**: Approve/reject photographer applications
- **Suspension System**: Temporary restrictions with duration
- **Ban Management**: Permanent restrictions with reasoning

### 📱 **User Experience**
- **Responsive Design**: Works on desktop, tablet, mobile
- **Modern Dark Theme**: Professional, sleek interface
- **Fast Performance**: Optimized loading and interactions
- **Intuitive Navigation**: Easy-to-use interface design
- **Accessibility**: Screen reader friendly and keyboard navigation

### 🔧 **Technical Features**
- **MVC Architecture**: Clean, maintainable code structure
- **Repository Pattern**: Organized data access layer
- **Real-time Updates**: AJAX-powered dynamic content
- **Database Optimization**: Efficient queries and indexing

### 📧 **Communication Features**
- **Email Notifications**: Booking updates and alerts
- **Support System**: Customer support ticket system
- **OTP Delivery**: Secure one-time password delivery
- **Email Templates**: Professional email formatting
- **SMTP Integration**: Reliable email delivery

### 🎯 **Business Features**
- **Photographer Applications**: Professional onboarding process
- **Revenue Sharing**: Platform commission system
- **Performance Tracking**: Business metrics and KPIs

---

## 🛠️ Technology Stack

### **Backend**
- **PHP 8.0+** - Server-side logic and API endpoints
- **MySQL** - Relational database for data persistence
- **PDO** - Database abstraction layer for security

### **Frontend**
- **HTML5** - Semantic markup structure
- **CSS3** - Modern styling with Flexbox/Grid
- **JavaScript (ES6+)** - Interactive functionality and AJAX
- **Font Awesome** - Professional icon library

### **Architecture**
- **MVC Pattern** - Clean separation of concerns
- **Repository Pattern** - Data access abstraction
- **RESTful Design** - Consistent API structure

---

## ⚙️ Installation & Setup

### **Prerequisites**
- **XAMPP/WAMP/MAMP** (includes Apache, MySQL, PHP)
- **PHP 8.0+** 
- **MySQL 5.7+**
- **Modern Web Browser** (Chrome, Firefox, Safari, Edge)

### **Step-by-Step Installation**

#### 1. **Download & Setup XAMPP**
```bash
# Download XAMPP from https://www.apachefriends.org/
# Install and start Apache + MySQL services
```

#### 2. **Database Setup**
```sql
-- Open phpMyAdmin (http://localhost/phpmyadmin)
-- Create new database named 'kislap'
CREATE DATABASE kislap;

-- Import the database schema
-- Go to Import tab and select: DB/database_schema.sql
```

#### 3. **Configure Database Connection**
The project uses these default settings (already configured):
```php
Host: localhost
Database: kislap  
Username: root
Password: (empty)
```
#### 6. **Access the Project**
```
Open browser and go to: http://localhost/Kislap
```

### **Quick Start with Test Data**

The database comes pre-loaded with test accounts. See the **Test Credentials** section below for complete login details.

### **Development Setup**

#### **Database Management**
```bash
# Access phpMyAdmin: http://localhost/phpmyadmin
# Backup database: Export → SQL format
# Reset database: Drop tables → Re-import schema
```

## 🚀 How to Run the Project

### **Initial Setup**
1. **Start XAMPP** - Make sure Apache and MySQL are running
2. **Access the Project** - Visit `http://localhost/Kislap`
3. **First Visit** - You'll be redirected to a landing page (loads only once, can be skipped)
4. **Create Account** - Click the profile icon and choose:
   - "Sign Up as Customer" 
   - "Sign Up as Photographer"
   
   💡 **Tip**: Use your actual email address for full functionality

---

## 🎯 Complete User Guide

### **👤 For Customers/Clients**

#### **Account Management**
- **Registration**: Create account with personal details (proper name capitalization required)
- **Login**: Use your credentials to access the platform
- **Forgot Password**: Click "Forgot Password" → Enter email → Check spam folder for OTP
- **Profile Management**: 
  - Click profile icon → "Edit Profile"
  - Update personal information (email cannot be changed for security)
  - Contact customer support via email
  - Change password securely

#### **Finding & Booking Photographers**
1. **Browse Photographers**:
   - Click "Search" in navbar OR "Explore Now" button
   - Use search bar to filter by name, location, specialty
   - Sort by rating, reviews, price (low to high/high to low), newest

2. **View Photographer Profiles**:
   - Click "View Profile" for detailed photographer information
   - Browse portfolio galleries with lightbox view
   - Check ratings and reviews from other clients
   - View available packages and pricing

3. **Booking Process**:
   - Click "Book Now" (changes to "Book Again" for returning clients)
   - If active conversation exists, shows "Continue Booking"
   - Redirects to real-time chat with photographer
   - AI chatbot assists with initial booking requirements
   - Discuss details directly with photographer

#### **Booking Management**
- **My Bookings**: Access via navbar to view all bookings
- **Booking Statuses**:
  - 🕐 **Pending**: Waiting for photographer response
  - 🤝 **Negotiating**: Price/date discussions ongoing  
  - ✅ **Confirmed**: Booking accepted, ready for deposit
  - ⭐ **Completed**: Service finished, ready for rating
  - ❌ **Cancelled**: Booking cancelled by either party

#### **Payment System**
- **Deposit Payment**: 50% upfront when booking is confirmed
- **Final Payment**: Remaining 50% after service completion
- **Payment Modal**: Shows complete booking details before payment
- **Secure Processing**: All payments processed securely

#### **Communication Features**
- **Real-time Messaging**: Instant chat with photographers
- **File Sharing**: Send images and documents
- **Message History**: Complete conversation records
- **Notifications**: Stay updated on booking status

#### **Rating & Review System**
- **Rate Experience**: 1-5 star rating system after completion
- **Write Reviews**: Share detailed feedback for other clients
- **View Ratings**: Check photographer ratings before booking

---

### **📸 For Photographers/Freelancers**

#### **Application Process**
1. **Apply to Join**: Submit application with required documents
2. **Portfolio Upload**: Minimum 2 high-quality work samples
3. **Resume/CV**: Upload professional background
4. **Admin Review**: Wait for application approval
5. **Account Activation**: Receive notification when approved

#### **Profile Management**
- **Complete Profile**: Add bio, specialties, experience
- **Portfolio Management**: Upload, organize, and update work samples
- **Package Creation**: Set up service packages with pricing
- **Availability Settings**: Manage calendar and booking slots
- **Profile Photo**: Professional headshot recommended

#### **Booking Management Dashboard**
- **Incoming Requests**: View and respond to booking inquiries
- **Active Bookings**: Manage confirmed sessions
- **Booking History**: Track completed work
- **Earnings Tracker**: Monitor income and statistics
- **Calendar Integration**: Manage availability and schedules

#### **Client Communication**
- **Real-time Chat**: Instant messaging with clients
- **Proposal System**: Send price and date proposals
- **File Sharing**: Share contracts, mood boards, sample images
- **Booking Confirmation**: Accept or negotiate booking terms

#### **Business Features**
- **Analytics Dashboard**: Track performance metrics
- **Rating Management**: Build reputation through client reviews
- **Portfolio Showcase**: Display work in organized galleries
- **Package Management**: Create and modify service offerings
- **Pricing Tools**: Set competitive rates and special offers

---

### **👨‍💼 For Administrators**

#### **User Management**
- **User Accounts**: View, edit, suspend, or ban users
- **Worker Accounts**: Manage photographer profiles and status
- **Account Verification**: Verify photographer information
- **Suspension System**: Temporary account restrictions with duration
- **Ban Management**: Permanent account restrictions with reasoning

#### **Application Review**
- **Pending Applications**: Review photographer applications
- **Document Verification**: Check resumes and portfolio quality
- **Approval Process**: Accept or reject applications
- **Rejection Reasoning**: Provide detailed feedback for rejections

#### **Platform Management**
- **Booking Oversight**: Monitor all platform bookings
- **Dispute Resolution**: Handle conflicts between users and photographers
- **Content Moderation**: Review and moderate user-generated content
- **System Analytics**: Platform usage and performance metrics
- **Revenue Tracking**: Monitor platform earnings and transactions

#### **Search & Filtering**
- **Advanced Search**: Find users/photographers by multiple criteria
- **Sorting Options**: Highest to lowest ratings, earnings, activity
- **Status Filtering**: Filter by account status, booking activity
- **Bulk Actions**: Perform actions on multiple accounts

---

## 💡 Pro Tips

### **For Best Experience**
- Use actual email addresses for full functionality
- Upload high-quality portfolio images (photographers)
- Complete all profile information for better visibility
- Communicate clearly in chat for smooth bookings
- Leave honest reviews to help the community

### **Troubleshooting**
- Check spam folder for OTP emails
- Clear browser cache if experiencing issues
- Ensure JavaScript is enabled for real-time features
- Contact support through profile settings if needed

### **Testing the Platform**
- To test completed bookings: Edit database dates to 2024
- Use provided test credentials for quick access
- Admin panel available for platform management
- Sample conversations and bookings included

---

## 🔧 Key Features Deep Dive

### **🔐 Authentication System**
- Secure password hashing with bcrypt
- Session management with CSRF protection
- Email and phone number validation
- Proper name capitalization enforcement

### **💬 Real-time Messaging**
- AJAX-powered chat interface
- File and image sharing capabilities
- Message status indicators
- Conversation history management

### **📊 Booking Management**
- Multi-stage booking process
- Deposit and final payment tracking
- Status updates and notifications
- Booking history and analytics

### **🎨 Portfolio System**
- Image upload and optimization
- Gallery display with lightbox
- Portfolio categorization
- Work samples validation

---

## 📄 License

- TBD
---

<div align="center">
  <h3>⭐ If you found this project helpful, please give it a star! ⭐</h3>
  <p>Made with ❤️ for the photography community</p>
  
  <img src="https://img.shields.io/github/stars/yourusername/kislap?style=social" alt="GitHub stars">
</div>

---

<div align="center">
  <sub>Built with passion by the Kislap team 📸</sub>
</div>

---

## 🔑 Test Credentials

*We recommend creating your own accounts for both Users and Photographers to fully test our project.*

### **📧 Helpdesk / Email Configuration**
```
Email: kislaphelpdesk@gmail.com
Password: kislap@gmail
App Password: vbvp uokz yyfa hfnf
```

### **👨‍💼 Admin Accounts**
```
Username: admin
Password: admin123

Username: cjcabubas
Password: 12345678
```

### **👥 Test User Accounts**

#### **Gabriel Lopez Caguiat**
```
Phone: 09232333244
Email: gabriel@gmail.com
Address: 231 Fiesta Communities, Mexico City, Pampanga
Password: gab123
```

#### **Gian Lavandero Mendiguarin**
```
Phone: 09318400461
Email: gila.mendiguarin.up@phinmaed.com
Address: 475 Naguilayan Highway, Binmaley, Pangasinan
Password: 123pogi
```

#### **Crystal James Fernandez Martinez**
```
Phone: 09442223434
Email: crystal@gmail.com
Password: 123123
```

#### **Mikaella Gonzales**
```
Phone: 09224569081
Email: mikaella@gmail.com
Password: 123456
```

#### **Christian Joseph Caguiat Cabubas**
```
Phone: 09193695376
Email: cjplaysgames83@gmail.com
Password: gender123
```

#### **Lester Ari**
```
Phone: 09991234343
Email: LesterAri@outlook.com
Password: 12345678
```

#### **Mildred Torio Balonzo**
```
Phone: 09291113534
Email: Balonzomildred@gmail.com
Password: balonzo123
```

#### **Antonio Villanueva Morales**
```
Phone: 09371234567
Email: antonio.morales@live.com
Address: 7816 Batangas St., Barangay San Isidro, Batangas City, Batangas
Password: antonioBatangas2023
```

#### **Clara Cruz Vargas**
```
Phone: 09241234567
Email: clara.vargas@rocketmail.com
Address: 4532 Pine Ave., Barangay Langtang, Tarlac City, Tarlac
Password: clara1234
```

#### **Roberto Castillo Delos Santos**
```
Phone: 09152345678
Email: roberto.delossantos@icloud.com
Address: 8901 Pineapple St., Barangay Gubat, Legazpi City, Albay
Password: robAlbay2023
```

#### **Edgar Reyes Fernandez**
```
Phone: 09161123456
Email: edgar.fernandez@zoho.com
Address: 2347 Mangga St., Barangay Poblacion, Davao City, Davao del Sur
Password: fernedgar777
```

#### **Julia Solis Esteban**
```
Phone: 09251823456
Email: julia.esteban@gmail.com
Address: 5432 Sampaguita Rd., Barangay Kauswagan, Cagayan de Oro, Misamis Oriental
Password: juliaCagayan32
```

#### **Raul Perez Bautista**
```
Phone: 09231123456
Email: raul.bautista@hotmail.com
Address: 1134 Rosas Ave., Barangay Panacan, Davao City, Davao del Sur
Password: raul!Davao2023
```

#### **Hazel Martinez Alonzo**
```
Phone: 09191456789
Email: hazel.alonzo@aol.com
Address: 8973 Zinnia St., Barangay Banilad, Cebu City, Cebu
Password: hazelCebu321
```

#### **Isabel Del Rosario Cruz**
```
Phone: 09361122334
Email: isabel.cruz@gmail.com
Address: 6785 Jasmine St., Barangay Talamban, Cebu City, Cebu
Password: cruzisabel!
```

#### **Marlon Gomez Cruz**
```
Phone: 09181234585
Email: marlon.cruz@gmail.com
Address: 123 Juniper St., Brgy. Malanday, Valenzuela City, Metro Manila
Password: M@rlonC#52
```

---

### **📸 Approved Photographer Accounts**

#### **Kim Gerick Tuazon Alano** - Portrait
```
Email: kitu.alano.up@phinmaed.com
Password: kim123
Specialty: Portrait Photography
```

#### **Miguel Santos Navarro** - Portrait
```
Phone: 09171234567
Email: miguel.navarro@gmail.com
Address: 12 Rizal St., Brgy. San Jose, Lucena City, Quezon
Password: Mgnr#8721
Specialty: Portrait Photography
```

#### **Anna Flores** - Photobooth
```
Phone: 09281234678
Email: anna.flores@yahoo.com
Address: 45 Mabini Ave., Brgy. Poblacion, Iloilo City, Iloilo
Password: AFl0res!43
Specialty: Photobooth Photography
```

#### **Diego Ramos Cruz** - Product
```
Phone: 09391234789
Email: diego.cruz@outlook.com
Address: 78 Magsaysay Rd., Brgy. Centro, Tagbilaran, Bohol
Password: D!e90g0
Specialty: Product Photography
```

#### **Beatrice Gomez Mercado** - Lifestyle
```
Phone: 09181234890
Email: bea.mercado@mail.com
Address: 101 San Miguel St., Brgy. Burgos, Batangas City, Batangas
Password: BeaM#2207
Specialty: Lifestyle Photography
```

#### **Rafael Torres Ilagan** - Creative/Conceptual
```
Phone: 09271234901
Email: rafael.ilagan@protonmail.com
Address: 9 Laurel Lane, Brgy. San Isidro, Cagayan de Oro, Misamis Oriental
Password: Raf_T0rr3s!
Specialty: Creative/Conceptual Photography
```

#### **Camille Valdez** - Event
```
Phone: 09371234560
Email: camille.valdez@gmail.com
Address: 222 Mango St., Brgy. Pasonanca, Zamboanga City, Zamboanga del Sur
Password: Cv!d3z2024
Specialty: Event Photography
```

#### **Arnel Bautista Pineda** - Portrait
```
Phone: 09191234561
Email: arnel.pineda@yahoo.com
Address: 33 Acacia Dr., Brgy. San Roque, Naga City, Camarines Sur
Password: Arn#NB77
Specialty: Portrait Photography
```

#### **Liza dela Cruz Aquino** - Photobooth
```
Phone: 09291234562
Email: liza.aquino@icloud.com
Address: 7 Coconut Rd., Brgy. San Antonio, Laoag City, Ilocos Norte
Password: L!zAq1985
Specialty: Photobooth Photography
```

#### **Marco Perez Santos** - Product
```
Phone: 09391234563
Email: marco.santos@ymail.com
Address: 150 Bamboo St., Brgy. San Vicente, Puerto Princesa, Palawan
Password: M4rc0$Ppz
Specialty: Product Photography
```

#### **Nelia Ramos Ortega** - Lifestyle
```
Phone: 09171234564
Email: nelia.ortega@zoho.com
Address: 56 Sampaguita Ln., Brgy. Divisoria, Tarlac City, Tarlac
Password: N0rte@55
Specialty: Lifestyle Photography
```

#### **Carlo Alonzo Reyes** - Conceptual/Creative
```
Phone: 09281234565
Email: carlo.reyes@gmail.com
Address: 88 Bago Blvd., Brgy. Banilad, Cebu City, Cebu
Password: CrR_8842!
Specialty: Conceptual/Creative Photography
```

#### **Melinda Santos Gonzaga** - Event
```
Phone: 09371234566
Email: melinda.gonzaga@mail.com
Address: 14 Lapu-Lapu St., Brgy. Durano, Mandaue City, Cebu
Password: MinDa#7x
Specialty: Event Photography
```

#### **Jason Villanueva Herrera** - Event
```
Phone: 09191234567
Email: j.herrera@outlook.com
Address: 300 Pine Rd., Brgy. Malvar, Angeles City, Pampanga
Password: JHerr!900
Specialty: Event Photography
```

#### **Elaine Yu Tan** - Product
```
Phone: 09291234568
Email: elaine.tan@gmail.com
Address: 19 Orchid Way, Brgy. Bagong Silang, Baguio City, Benguet
Password: E!Tan#334
Specialty: Product Photography
```

#### **Victor Navarro De Guzman** - Conceptual/Creative
```
Phone: 09391234569
Email: victor.dguzman@protonmail.com
Address: 77 Sampaloc St., Brgy. Divisoria, Tarlac City, Tarlac
Password: VDGz#12x
Specialty: Conceptual/Creative Photography
```

#### **Rica Santos Manalo** - Portrait
```
Phone: 09171234570
Email: rica.manalo@yahoo.com
Address: 5 Palm Grove, Brgy. Poblacion, Roxas City, Capiz
Password: Ric@M200
Specialty: Portrait Photography
```

#### **Jonah Cruz Fabillar** - Event
```
Phone: 09281234571
Email: jonah.fabillar@gmail.com
Address: 402 Pearl St., Brgy. Baybay, Legazpi City, Albay
Password: J0n4h_F!b
Specialty: Event Photography
```

#### **Maribel Aquino Catindig** - Lifestyle
```
Phone: 09371234572
Email: maribel.catindig@aol.com
Address: 21 Tulip Ln., Brgy. San Jose, Lucena City, Quezon
Password: MCat!982
Specialty: Lifestyle Photography
```

#### **Edwin Ramos Lozano** - Product
```
Phone: 09191234573
Email: edwin.lozano@mail.com
Address: 66 Mango Grove, Brgy. San Isidro, Iloilo City, Iloilo
Password: EdL0z#44
Specialty: Product Photography
```

#### **Faye Navarro Salazar** - Photobooth
```
Phone: 09291234574
Email: faye.salazar@outlook.com
Address: 9 Camia St., Brgy. San Miguel, Dumaguete City, Negros Oriental
Password: F@yeS_77
Specialty: Photobooth Photography
```

---

### **⏳ Pending Photographer Applications**

#### **Ruben Morales Pascual**
```
Phone: 09391234575
Email: ruben.pascual@gmail.com
Address: 150 Maharlika Rd., Brgy. Poblacion, Butuan City, Agusan del Norte
Password: RbPasc#05
```

#### **Sonia dela Cruz Almeda**
```
Phone: 09171234576
Email: sonia.almeda@yahoo.com
Address: 27 Sampaloc Ave., Brgy. Mabini, Calapan, Oriental Mindoro
Password: SAlm!d0
```

#### **Kelvin Lacerna Quiroz**
```
Phone: 09281234577
Email: kelvin.quiroz@gmail.com
Address: 310 Baybayon St., Brgy. Poblacion, Legazpi City, Albay
Password: KQ#2025!
```

#### **Ma Teresa Salonga Borja**
```
Phone: 09371234578
Email: teresa.borja@zoho.com
Address: 4 Sampaguita Rd., Brgy. Centro, Tuguegarao City, Cagayan
Password: MTb#889
```

#### **Arlene Quinto Lomboy**
```
Phone: 09191234579
Email: arlene.lomboy@icloud.com
Address: 88 Coconut Ln., Brgy. Baybay, Calbayog City, Samar
Password: ALQ#2023@
```

#### **Luis Mendoza Dela Cruz**
```
Phone: 09281234580
Email: luis.delacruz@gmail.com
Address: 234 Palmera St., Brgy. Victoria, San Fernando, Pampanga
Password: LDM!323
```

#### **Hannah Perez Torres**
```
Phone: 09371234581
Email: hannah.torres@outlook.com
Address: 57 Mango Blvd., Brgy. Panghulo, Malolos City, Bulacan
Password: H@nn@H456
```

#### **Jerome Reyes Sison**
```
Phone: 09171234582
Email: jerome.sison@aol.com
Address: 400 Maharlika Rd., Brgy. San Juan, Laoag City, Ilocos Norte
Password: JSi$on123
```

#### **Rafael Salcedo Aguilar**
```
Phone: 09291234583
Email: rafael.aguilar@gmail.com
Address: 182 Laurel St., Brgy. San Isidro, Batangas City, Batangas
Password: R@fa_1990
```

#### **Cynthia Aquino Delos Reyes**
```
Phone: 09391234584
Email: cynthia.delosreyes@zoho.com
Address: 118 Palm St., Brgy. Bagumbayan, Davao City, Davao del Sur
Password: CDR@995
```

---

### **🔧 Testing Notes**
- All accounts are pre-configured and ready for testing
- Use these credentials to explore different user roles and features
- Photographers are categorized by specialty for comprehensive testing
- Pending applications can be approved/rejected through admin panel
- Email functionality requires proper SMTP configuration with provided credentials
