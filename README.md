# FYTT Fitness - MMA & Performance Center Website

## Project Overview
FYTT Fitness is a responsive web application designed for a modern mixed martial arts and strength conditioning facility. The project serves as a digital storefront for the gym, providing potential clients with detailed information regarding training disciplines, class schedules, and membership pricing tiers.

Beyond a static informational site, this application implements a simulated front-end authentication system. It features a client-side "Member Portal" that demonstrates state management, allowing users to access a personalized dashboard containing a digital membership ID, real-time session tracking, and activity history.

## Features

### User Interface & Experience
* **Responsive Design:** Utilizes a mobile-first approach ensuring full compatibility across desktop, tablet, and mobile devices.
* **Modern Aesthetic:** Implements a high-contrast visual hierarchy (Red/Black/White) to align with the brand identity.
* **Parallax Effects:** CSS-driven background scrolling for enhanced visual engagement.

### Functional Components
* **Dynamic Navigation:** A sticky navigation bar with active state handling and mobile responsiveness.
* **Pricing Tables:** Structured layout comparing standard memberships versus session-based training packages.
* **Class Timetable:** A semantic HTML table layout for weekly training schedules.

### Simulated Member Portal (JavaScript Logic)
* **Authentication Flow:** A modal-based login system that simulates server-side validation.
* **State Persistence:** JavaScript logic manages the user's session state without page reloads.
* **Dashboard Analytics:**
    * **Digital Identification:** Integration with a QR code API for member verification.
    * **Session Management:** Dynamic progress bars visualizing remaining sessions and membership expiration.
    * **Activity Logging:** A data table rendering recent check-in history.

## Technology Stack

* **Markup:** HTML5
* **Styling:** Tailwind CSS (via CDN)
* **Scripting:** Vanilla JavaScript (ES6+)
* **Typography:** Google Fonts (Teko, Inter)
* **External Assets:** FontAwesome (Icons), Unsplash (Imagery)

## Future Development

The current release represents the Minimum Viable Product (MVP) focused on frontend architecture. The following enhancements are planned for the full-stack iteration:

1.  **Backend Integration:** Implementation of a Node.js and Express server environment.
2.  **Data Persistence:** Migration from hardcoded JavaScript objects to a MongoDB database.
3.  **Content Management System (CMS):** Development of an admin interface for schedule and pricing updates.
4.  **Payment Gateway:** Integration with Stripe API for online membership purchasing.

## Contact

**[Carlos Miguel A. Cariño]**
Full Stack Developer
