# Real-Time Socket Messenger

**Technical Assessment:** A real-time messaging system built for performance and scalability.

---

## 🚀 Technologies
- **Backend:** Laravel 12 (PHP 8.5)
- **Frontend:** Vue 3 (Inertia.js)
- **Real-time:** Laravel Reverb (WebSockets)
- **Infrastructure:** Docker (Laravel Sail)
- **Queue/Cache:** Redis

---

## 🛠 Installation & Setup

The project can be fully deployed using a single installation script:

1. `chmod +x install.sh && ./install.sh`

Required background services. To ensure real-time functionality and event processing, run the following commands:

2. `./vendor/bin/sail artisan queue:work`
3. `./vendor/bin/sail artisan reverb:start`

---

## ✅ Features & Implementation Checklist

### 💬 Core Messaging
- [x] **Real-time communication:** Instant message delivery using WebSockets (Laravel Reverb).
- [x] **Private Channels:** Secure messaging implementation where users only receive data intended for them.
- [x] **Message Persistence:** All conversations are stored in MySQL with optimized indexing.
- [x] **Read/Unread Status:** Automatic marking of messages as read when a chat is opened.

### 👥 User Management
- [x] **Authentication:** Full registration and login flow powered by Laravel Breeze (Inertia/Vue).
- [x] **Infinite Scroll:** The user list supports dynamic loading (pagination) to ensure high performance with large datasets.
- [x] **Unread Counters:** Visual indicators showing the number of missed messages for each contact, which persist even after logout/login.

### 🏗 Architecture & Performance
- [x] **Service Layer Pattern:** Decoupled business logic from controllers into `ChatService` and `ListService`.
- [x] **N+1 Query Prevention:** Optimized database calls using `withCount` and subqueries to fetch users and their unread message counts in a single efficient query.
- [x] **Form Requests:** Strict server-side validation for all incoming data.

### 🐳 DevOps
- [x] **Dockerized Environment:** Fully pre-configured Laravel Sail setup (PHP 8.5, MySQL 8.4, Redis, Reverb).
- [x] **Redis Integration:** Used for efficient event broadcasting and queue management.
- [x] **Automated Deployment:** Custom `install.sh` script that handles dependency installation, environment setup, and waits for database readiness before running migrations.
