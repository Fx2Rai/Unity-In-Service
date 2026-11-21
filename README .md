# Unity in Service (UIS)

Unity in Service (UIS) is a web application for disaster reporting and volunteer coordination. The project combines PHP/MySQL pages for registration, dashboards, disaster alerts, volunteer/requirement management and messaging with a separate Python Flask image-analysis prototype.


## 1. Project components

- **Web application:** PHP, HTML, CSS and JavaScript
- **Database:** MySQL (`disaster_volunteering`)
- **Image-analysis prototype:** Python, Flask, OpenCV, NumPy and Matplotlib
- **Additional local data store:** SQLite (`disaster_data.db`, used by `app.py`)


## 2. Prerequisites

Install the following on Windows:

- XAMPP (Apache and MySQL)
- Python 3 (for the optional Flask image-analysis service)
- A browser


## 3. Install the PHP website

1. Extract the project folder.
2. Copy the `UIS` folder into XAMPP's web root, usually:
   `C:\xampp\htdocs\UIS`
3. Open **XAMPP Control Panel**.
4. Start **Apache** and **MySQL**.
5. Open phpMyAdmin at `http://localhost/phpmyadmin`.
6. Create/import the MySQL database named `disaster_volunteering`.
7. Review `db_connect.php` and update the connection values if your MySQL setup differs.
8. Open the site at `http://localhost/UIS/`.

### Database configuration

The supplied `db_connect.php` is configured for:


```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "disaster_volunteering";
```

These are local-development defaults. Change them to match your MySQL account. **The ZIP does not include a SQL schema/dump, so the database tables may need to be recreated or restored from your original database backup.** The PHP pages expect tables for volunteers, administrators, disaster alerts, requirements, uploaded images, analysis records and chat/messages; exact schema details should be verified from the source queries before creating a database manually.


## 4. Website entry points and features

| Page/file | Purpose |
|---|---|
| `index.html` | Main public landing page and requirements display |
| `login.php` / `login.html` | Login flow |
| `volunteer_registration.php` | Volunteer registration |
| `userdisplay.html` | Volunteer-facing display/dashboard entry |
| `volunteer_dashboard.php` | Volunteer dashboard |
| `volunteer_profile.php` | Volunteer profile management |
| `volunteer_management.php` | Admin volunteer management |
| `admin_dashboard.php` | Admin dashboard and management views |
| `disaster_alert.html` | Disaster alert/report form with map/location selection |
| `alert_disaster.php` | Processes disaster alert submissions |
| `fetch_alerts.php` | Retrieves alert records from MySQL |
| `fetch_requirements.php` | Retrieves disaster requirements |
| `add_requirements.php` | Adds requirements (admin-side flow) |
| `update_requirements.php` / `update_collected.php` | Updates requirement/collection status |
| `image_analyzer.php` | PHP-based uploaded-image analysis and severity result page |
| `volunteer_image_upload.php` / `upload_image.php` | Image upload flows |
| `chat.php` / `chatadmin.php` | Messaging pages for volunteer/admin workflows |

Feature availability depends on the MySQL database being correctly set up and any relevant PHP pages being accessible.


## 5. Typical volunteer workflow

1. Open `http://localhost/UIS/`.
2. Select **Register** and complete volunteer registration.
3. Return to the login page and sign in.
4. Open the volunteer dashboard/profile.
5. View requirements and relevant disaster information.
6. Use the image-upload/analyzer feature if needed.
7. Use the messaging feature where available.


## 6. Typical administrator workflow

1. Sign in with an administrator account that exists in the database.
2. Open the admin dashboard.
3. Review disaster reports/alerts.
4. Manage volunteers.
5. Add or update disaster requirements and collection status.
6. Review image-analysis information.
7. Use the administrator messaging page.


## 7. Optional Flask image-analysis service

The ZIP contains `app.py`, a separate Flask service. It accepts an image upload and uses OpenCV edge detection with simple threshold rules to assign **Low**, **Moderate**, or **High** severity. This is a basic image-processing heuristic, not a trained disaster-detection model, and its output should not be used as a real-world emergency assessment.


### Install Python dependencies

From a terminal opened in the project directory:


```bash
python -m pip install flask flask-cors opencv-python numpy matplotlib
```

### Start the service

```bash
python app.py
```

The service is configured to listen on port `5000` (the Flask development server). Leave that terminal open while using the service.


### Flask endpoints

| Method | Endpoint | Purpose |
|---|---|---|
| `POST` | `/upload` | Upload an image using multipart form field `image`; returns the severity result |
| `GET` | `/get_severity_data` | Returns counts grouped by Low, Moderate and High |
| `GET` | `/plot_severity` | Creates and returns a severity bar chart image |


### Flask data and uploads

- Uploads are saved in the service's `uploads/` folder.
- The Flask service uses a SQLite database file named `disaster_data.db` in its current working directory.
- The service creates its `image_analysis` table when it starts.
- Run the service from the UIS project directory so its relative paths resolve predictably.


## 8. Troubleshooting

### Apache or MySQL will not start

- Check whether another application is already using the required ports.
- Review XAMPP's Apache/MySQL logs for the specific error.

### PHP pages show a database connection error

- Confirm MySQL is running in XAMPP.
- Confirm `disaster_volunteering` exists.
- Confirm the username/password in `db_connect.php` match your local MySQL setup.
- Restore or create the required tables. The project ZIP does not contain a SQL export.

### The site opens but some pages fail

- Confirm the folder is named `UIS` and is under `htdocs`.
- Use the local URL `http://localhost/UIS/` rather than opening PHP files directly from disk.
- Check the PHP error log and browser console.
- Missing database tables or columns can cause feature-specific errors.

### Flask service does not start

- Check Python is installed: `python --version`
- Install the dependencies listed above.
- Run `python app.py` from the UIS folder.
- If port 5000 is occupied, stop the other service or change the port in `app.py` and update any frontend/API configuration that depends on it.

### Image analysis behaves unexpectedly

The Flask service uses grayscale conversion and Canny edge density thresholds. It does not understand disaster types semantically and can produce incorrect severity results. Treat results as demonstrations only.


## 9. Stop the project

1. Close the Flask terminal with `Ctrl+C` if it is running.
2. In XAMPP Control Panel, click **Stop** for Apache and MySQL.


## 10. Important project notes

- The ZIP contains source files and sample uploads, but no database SQL export was found in the archive listing.
- The PHP application and Flask service are separate components; the PHP site can have features that do not require Flask, and the Flask API is not necessarily wired into every PHP image workflow.
- Some dependencies are loaded from external CDN URLs (for example, icons, fonts, maps/chart libraries), so internet access may be needed for those interface assets.
- This project is a prototype/academic project. Do not use its heuristic severity scores or alert records as a substitute for official emergency systems.


## 11. Quick-start commands

**PHP website:** Start Apache and MySQL in XAMPP, then visit:

`http://localhost/UIS/`

**Optional Flask service:**

```bash
cd C:\xampp\htdocs\UIS
python -m pip install flask flask-cors opencv-python numpy matplotlib
python app.py
```

Flask URL: `http://localhost:5000`
