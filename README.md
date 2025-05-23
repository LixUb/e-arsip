# Document Archiving Application

## Overview
This Document Archiving Application allows users to upload, download, and manage documents efficiently. It utilizes PHP for server-side scripting and MySQL for database management, providing a simple interface for document handling.

## Features
- Upload documents with validation.
- List all uploaded documents with options to download or delete.
- Secure file handling and storage.
- Database integration for storing document metadata.

## Project Structure
```
document-archiving-app
├── src
│   ├── config
│   │   └── database.php
│   ├── uploads
│   ├── index.php
│   ├── upload.php
│   ├── download.php
│   ├── delete.php
│   └── styles
│       └── style.css
├── .env
├── README.md
└── sql
    └── schema.sql
```

## Installation

1. **Clone the repository:**
   ```
   git clone <repository-url>
   cd document-archiving-app
   ```

2. **Set up the database:**
   - Create a MySQL database and import the schema from `sql/schema.sql`.
   - Update the `.env` file with your database credentials.

3. **Configure the uploads directory:**
   - Ensure the `src/uploads` directory exists and has the appropriate permissions for file uploads.

4. **Run the application:**
   - Start a local server (e.g., using XAMPP, MAMP, or built-in PHP server).
   - Access the application via your web browser at `http://localhost/document-archiving-app/src/index.php`.

## Usage
- **Upload Documents:** Use the upload form on the main page to select and upload documents.
- **View Documents:** After uploading, all documents will be listed with options to download or delete them.
- **Download Documents:** Click the download link next to any document to download it to your local machine.
- **Delete Documents:** Click the delete link next to any document to remove it from the server.

## Contributing
Contributions are welcome! Please fork the repository and submit a pull request for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for details.