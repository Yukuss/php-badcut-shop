<div align="center">
Выберите язык / Choose prefered language
  
[Русский](README.ru.md) | <b>English</b>

</div>
<hr>

# Bad Cut - Demo Online Store for Caps

Educational project of an online store built with pure PHP and MySQL.

## Technologies

- Backend: PHP 7.4+
- Database: MySQL
- Extension: MySQLi
- Frontend: HTML5, CSS3, JavaScript
- Security: Prepared Statements, sessions, password hashing

## Architectural Solutions
- Modular structure - separation into reusable components (header, footer)
- MySQLi with Prepared Statements - protection against SQL injections
- PHP Sessions - user state management
- Tree-based menu and comments - multi-level systems via `parent_id`
- Server-side validation - checking all input data
- Error handling - displaying error messages to users

<hr>

## Implemented Features
### Authentication System
- Registration with data validation (login, email, phone, password)
- Duplicate checking - cannot register with existing login/email/phone
- Secure password storage - hashing via MD5
- Sessions - storing user data between pages
- Authentication with credential verification
- Logout functionality

### Product Catalog
- Dynamic catalog generation from database
- Filter by brands - select specific manufacturer
- Filter by categories - group products by type
- Sorting options:
  - Price: low to high
  - Price: high to low
  - Alphabetically (A-Z, Z-A)
- Reset filters with one button

### Product Page
- Detailed product information (name, description, price, image)
- Comment system:
  - Adding reviews from authenticated users
  - Replies to comments (tree structure)
  - Deleting own comments
- Product image gallery
