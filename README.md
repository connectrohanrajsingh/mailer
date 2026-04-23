<p align="center">
  <a href="#"><img src="https://img.shields.io/badge/build-passing-brightgreen" alt="Build Status"></a>
  <a href="#"><img src="https://img.shields.io/badge/version-1.0-blue" alt="Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/license-MIT-lightgrey" alt="License"></a>
</p>

## About Mailer

Mailer is a lightweight and efficient mail management application designed to simplify email handling through flexible configuration and a clean interface. It provides seamless integration with standard mail protocols, making it easy to manage both incoming and outgoing emails.

The application supports both IMAP and SMTP configurations, allowing users to fetch and send emails reliably across different mail servers.

### Features

* IMAP support for fetching emails from configured mailboxes
* SMTP support for sending emails securely
* Easy configuration of mail servers via environment variables (`.env`)
* Compose and send emails directly from the interface
* Organized inbox and outbox handling
* Clean and minimal user experience for fast workflows

This approach ensures flexibility and security while keeping your credentials outside the codebase.

## Usage

* Configure your mail servers in the `.env` file
* Access the application dashboard
* View incoming emails via IMAP
* Compose and send emails using SMTP
* Manage your inbox and outbox efficiently

## IMAP Configuration

The application allows flexible IMAP configuration for fetching emails from your mail server. All settings are controlled via environment variables.

### Available Options

```env
IMAP_HOST=
IMAP_PORT=
IMAP_PROTOCOL=
IMAP_ENCRYPTION=
IMAP_VALIDATE_CERT=
IMAP_USERNAME=
IMAP_PASSWORD=
IMAP_FOLDER=
IMAP_START_DATE=
IMAP_MAILS_PER_FETCH=
```

### Description

* **IMAP_HOST** – Mail server host (e.g., `imap.gmail.com`)

* **IMAP_PORT** – Port number (e.g., `993`)

* **IMAP_PROTOCOL** – Protocol type (`imap`)

* **IMAP_ENCRYPTION** – Encryption method (`ssl`, `tls`, or `null`)

* **IMAP_VALIDATE_CERT** – Enable/disable certificate validation (`true/false`)

* **IMAP_USERNAME** – Email address or username

* **IMAP_PASSWORD** – Mail account password or app password

* **IMAP_FOLDER** – Mailbox folder to fetch from (e.g., `INBOX`)

* **IMAP_START_DATE** –
  Defines the starting point for fetching emails.

  * If provided → emails will be fetched from this date onward
  * If not provided → fetching starts from the current date

* **IMAP_MAILS_PER_FETCH** –
  Controls how many emails are fetched per connection.

  * Helps optimize performance and avoid overload
  * Maximum allowed value: **150**
  * Recommended to keep it within a reasonable range for stability

### Behavior

* On each connection, the system fetches emails based on:

  * Defined date range (via `IMAP_START_DATE`)
  * Fetch limit (via `IMAP_MAILS_PER_FETCH`)
* Ensures controlled and efficient email synchronization
* Prevents excessive load by enforcing fetch limits

### Example

```env
IMAP_HOST=imap.gmail.com
IMAP_PORT=993
IMAP_PROTOCOL=imap
IMAP_ENCRYPTION=ssl
IMAP_VALIDATE_CERT=true
IMAP_USERNAME=example@gmail.com
IMAP_PASSWORD=yourpassword
IMAP_FOLDER=INBOX
IMAP_START_DATE=2026-01-01
IMAP_MAILS_PER_FETCH=100
```

## SMTP Configuration

The application provides flexible SMTP configuration for sending emails through your preferred mail server. All settings are managed via environment variables.

### Available Options

```env id="smtp9x2"
SMTP_HOST=
SMTP_PORT=
SMTP_ENCRYPTION=
SMTP_USERNAME=
SMTP_PASSWORD=
SMTP_FROM_ADDRESS=
SMTP_FROM_NAME=
SMTP_TIMEOUT=
SMTP_AUTH_MODE=
```

### Description

* **SMTP_HOST** – Mail server host (e.g., `smtp.gmail.com`)

* **SMTP_PORT** – Port number (e.g., `587` for TLS, `465` for SSL)

* **SMTP_ENCRYPTION** – Encryption type (`tls`, `ssl`, or `null`)

* **SMTP_USERNAME** – Email address or username

* **SMTP_PASSWORD** – Mail account password or app password

* **SMTP_FROM_ADDRESS** –
  Default sender email address used while sending mails

* **SMTP_FROM_NAME** –
  Display name for outgoing emails (e.g., App or User name)

* **SMTP_TIMEOUT** –
  Connection timeout in seconds (optional, improves reliability)

* **SMTP_AUTH_MODE** –
  Authentication mode if required by the server (`login`, `plain`, etc.)

### Behavior

* Used for sending emails from the application
* Works seamlessly with the compose mail feature
* Supports secure connections via SSL/TLS
* Ensures reliable delivery with configurable timeout and authentication

### Example

```env id="smtpEx1"
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=example@gmail.com
SMTP_PASSWORD=yourpassword
SMTP_FROM_ADDRESS=example@gmail.com
SMTP_FROM_NAME=Mailer App
SMTP_TIMEOUT=30
SMTP_AUTH_MODE=login
```

## Scheduled Jobs

The application uses Laravel’s scheduler to efficiently manage email synchronization in two stages: quick overview fetching and detailed mail retrieval.

### Configuration

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('emails:overview')->everyTwoMinutes()->withoutOverlapping();
Schedule::command('emails:fetch')->everyTenMinutes()->withoutOverlapping();
```

### How It Works

* **emails:overview**

  * Runs every **2 minutes**
  * Performs a **fast, lightweight fetch**
  * Retrieves basic metadata (e.g., subject, sender, timestamps)
  * Helps keep the inbox quickly updated with minimal load

* **emails:fetch**

  * Runs every **10 minutes**
  * Performs a **detailed fetch of emails**
  * Completes and fills in full email content (body, attachments, etc.)
  * Ensures all messages are fully synchronized
 
  
* **Verify Scheduler**

You can manually test the scheduler using:
```php
 php artisan schedule:run
```

### Behavior

* The system follows a **two-phase sync strategy**:

  1. Quick overview for instant visibility
  2. Background detailed fetch for completeness

* `withoutOverlapping()` ensures:

  * No duplicate executions
  * Prevents race conditions and server overload

* Optimized for:

  * Performance
  * Scalability
  * Smooth user experience without delays

This approach ensures users can see new emails almost instantly, while full data is populated seamlessly in the background.


## Queue Worker (Email Sending)

The application uses a background queue worker to handle outgoing emails efficiently. Instead of sending emails instantly during user actions, they are pushed to a queue and processed asynchronously.

### How It Works

* When a user composes and sends an email:

  * The email is added to a **queue**
  * The request returns immediately without delay

* A **queue worker** processes the jobs in the background:

  * Picks queued emails
  * Sends them via SMTP configuration
  * Updates status (sent/failed) accordingly

### Benefits

* Faster user experience (no waiting for mail sending)
* Reliable delivery with retry mechanisms
* Scalable for high-volume email sending
* Prevents request timeouts

### Running the Worker

Start the queue worker using:

```bash id="wrk12x"
php artisan queue:work
```

For continuous production usage, it is recommended to use a process manager like Supervisor:

```bash id="wrk22y"
php artisan queue:work --tries=3 --timeout=90
```

### Behavior

* Emails are sent in the background via the configured SMTP server
* Failed jobs can be retried automatically based on queue settings
* Ensures smooth and non-blocking email delivery

This architecture keeps the system responsive while ensuring reliable email dispatching.


## Contributing

Contributions are welcome. If you'd like to improve the project, feel free to fork the repository and submit a pull request.

## Security

If you discover any security issues, please report them responsibly so they can be addressed promptly.

## 📄 License

This project is distributed under the **MIT License**, which permits free use, modification, and distribution of the code.

### 🎨 Template Credit

The UI template used in this project is designed by **Xiaoying Riley**.  
You should provide proper credit by including attribution in your project.

🔗 [Template Repository](https://github.com/xriley/portal-theme-bs5)
