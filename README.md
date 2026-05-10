# Siemola

Siemola is a smart locker monitoring system built for campus item borrowing. It combines a Laravel dashboard, staff/admin workflows, RFID identity checks, and an ESP-based locker API so locker activity can be tracked from the physical device into the web interface.

This repository is presented as a project showcase only.

## Showcase

### Smart Locker Dashboard

Siemola provides a monitoring-focused dashboard for checking locker availability, active borrowing activity, late returns, student totals, staff totals, and the latest ESP synchronization state. The interface is designed for operators who need a quick operational view rather than a marketing page.

### RFID and ESP Flow

The locker flow separates access authorization from the actual borrowing record:

1. A student taps an RFID card.
2. The ESP sends the card UID and device ID to the Laravel API.
3. Siemola validates the card, student status, locker device, and active borrowing state.
4. Borrowing is recorded when the switch reports that the item was taken.
5. Return is recorded when the switch reports that the item was placed back.

This keeps an abandoned tap from being treated as a completed borrowing event.

### Role-Based Operations

The web dashboard supports separated admin and staff responsibilities:

- Admin users manage staff accounts, student records, and locker records.
- Staff users review borrowing history and export filtered reports.
- Public dashboard access can show a student-friendly monitoring view.

### Locker Model

The current demo setup keeps the real hardware structure visible:

- `L1` represents the real ESP locker.
- `L2` to `L12` are dummy locker entries for dashboard and demo coverage.
- Locker states are kept operationally simple: available, borrowed, or late.

### History and Reporting

Borrowing history includes searchable and filterable records by date range, student data, study program, locker, and RFID UID. The export view follows the same filter state so reports match the operator's current screen.

### ESP Simulator

The repository includes a simulator script for demonstrating the ESP API contract without flashing hardware during every test run.

```bash
php tools\simulate-esp.php check
php tools\simulate-esp.php tap
php tools\simulate-esp.php borrow
php tools\simulate-esp.php return
php tools\simulate-esp.php cycle
```

## Built With

- Laravel
- Laravel Breeze authentication
- Laravel Sanctum API authentication
- Blade
- Tailwind CSS
- Vite
- MySQL-compatible database
- ESP/RFID hardware API integration

## Project Status

Siemola is a private academic/project showcase. The codebase is not published as a reusable package, starter kit, or open-source template.

## License

All rights reserved.

This source code is proprietary and is provided for showcase and review purposes only. You may not copy, reuse, redistribute, modify, publish, sublicense, or deploy this project or any part of it without explicit written permission from the owner.
