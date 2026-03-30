# AC Medical - Student & Financial Management System

A comprehensive web-based management system built with PHP and MySQL to handle student records, track financial transactions, and manage tuition fees for academic institutions (supporting both Bachelor and Postgraduate programs).

## Features

- **Student Management:** Manage profiles for both Bachelor ("بكلوريوس") and Postgraduate ("عليا") students.
- **Financial Dashboard:** Real-time overview of total revenues, daily transactions, monthly/yearly collections, and outstanding debts.
- **Payment Tracking:** Record tuition fee installments, track remaining balances, and upload receipt images for verification.
- **Recent Activity:** Maintain a history of the latest captured payments directly on the dashboard.
- **User Authentication:** Secure login system with session management.
- **Arabic Interface:** Fully localized dashboard and UI for Arabic-speaking users.

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript (jQuery, Bootstrap)
- **Styling:** Custom Medical Theme CSS

## Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/ali-falah/ac-medical.git
   cd ac-medical
   ```

2. **Server Environment:**
   Place the project directory in your local web server's document root (e.g., `htdocs` for XAMPP or `/var/www/html` for LAMP).

3. **Database Configuration:**
   - Create a MySQL database for the project.
   - Import the database architecture to set up tables such as `student`, `student_high`, `payment`, and `payment_high`.
   - Set up the database connection strings in `php_action/db_connect.php`.

4. **Directory Permissions:**
   Make sure the server has write access to the following directories so that uploaded receipts can be saved:
   - `php_action/uploads/`
   - `php_action/uploadsHigh/`

5. **Access Application:**
   Open your browser and navigate to `http://localhost/ac-medical/` (or your domain).
