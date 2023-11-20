# 360VUZ Sheets

## Project Overview

This project utilizes a Laravel Artisan command to set an average moving value in a Google Spreadsheet. The command has the following signature:

```bash
php artisan app:set-average-moving {--spreadsheetId=} {--sheetName=}
```

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/your-username/your-project.git
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Copy the example environment file:

   ```bash
   cp .env.example .env
   ```

4. Configure your `.env` file with the necessary information, including the Google Spreadsheet ID and sheet name.

   ```env
   SPREADSHEET_ID=your-spreadsheet-id
   SHEET_NAME=your-sheet-name
   CREDENTIALS_PATH=credentials.json
   ```

   Make sure to replace `your-spreadsheet-id` and `your-sheet-name` with your actual Google Spreadsheet ID and sheet name.

## Google Credentials

In order to interact with Google Sheets, you need to provide the `credentials.json` file. Follow these steps:

1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project or select an existing project.
3. Enable the Google Sheets API.
4. In the left navigation menu, go to "APIs & Services" > "Credentials."
5. Click on "Create Credentials" and select "Service account key."
6. Choose or create a service account, set the role to "Project" > "Editor," and choose JSON as the key type.
7. Click "Create" to download the JSON file. Save this file as `credentials.json`.
8. Place the `credentials.json` file in the root of your Laravel project.

## Usage

Now that your environment is set up, you can use the Artisan command to set the average moving value in your Google Spreadsheet. Run the following command:

```bash
php artisan app:set-average-moving
```

You can also provide the Spreadsheet ID and sheet name as options:

```bash
php artisan app:set-average-moving --spreadsheetId=your-spreadsheet-id --sheetName=your-sheet-name
```

Make sure to replace `your-spreadsheet-id` and `your-sheet-name` with the appropriate values and set spreadsheet which is shared with GCP account.

## Configuration

You can modify the configuration in the `config/average-moving.php` file to change default values:

```php
return [
    'spreadsheet_id' => env('SPREADSHEET_ID', '1ib8BwpDYYXSJ2_DK8S3o4geVivHTAzxtRx4a2bXoqwA'),
    'sheet_name' => env('SHEET_NAME', 'Sheet1'),
    'credentials_path' => env('CREDENTIALS_PATH', 'credentials.json'),
];
```
