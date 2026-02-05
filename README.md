# Git LFS + Cloudflare R2 Proof of Concept

A PHP proof-of-concept demonstrating Git LFS integration with GitHub and Cloudflare R2 for file distribution.

## Features

- **Git LFS Integration**: Track large PDF files with Git LFS
- **Cloudflare R2 Storage**: Upload and manage files in R2
- **Signed URLs**: Generate temporary access URLs for files
- **File Management**: List, download, and delete files from R2

## Setup

### Prerequisites

- PHP 8.1+
- Composer
- Git LFS (`brew install git-lfs` on macOS)
- Cloudflare R2 account with bucket configured

### Installation

1. Clone the repository
2. Copy `.env.example` to `.env` and fill in your Cloudflare credentials:
   ```bash
   cp .env.example .env
   ```

3. Install dependencies:
   ```bash
   composer install
   ```

4. Initialize Git LFS:
   ```bash
   git lfs install
   git lfs track "*.pdf"
   ```

## Usage

### Upload a file to R2

```bash
php scripts/upload-to-r2.php path/to/file.pdf
```

### Download a file from R2

```bash
php scripts/download-from-r2.php documents/file.pdf path/to/save.pdf
```

### List files in R2

```bash
php scripts/list-r2.php documents/
```

### Run tests

```bash
composer test
```

## Git LFS Testing

1. Add a PDF file to the repository:
   ```bash
   cp sample.pdf documents/
   git add documents/sample.pdf
   git commit -m "Add sample PDF"
   git push
   ```

2. Verify LFS tracking on GitHub (file will show "LFS Pointer" in raw view)

3. Clone repository in a new directory to test LFS download:
   ```bash
   git clone https://github.com/user/repo.git test-clone
   cd test-clone
   ls -lh documents/  # Should show actual file, not pointer
   ```

## Architecture

- **src/R2Client.php**: Main client for R2 operations
- **src/Config.php**: Environment configuration loader
- **scripts/**: CLI tools for uploading, downloading, and listing files
- **tests/**: Unit tests

## Configuration

See `.env.example` for all available configuration options:

- `R2_ACCOUNT_ID`: Your Cloudflare account ID
- `R2_ACCESS_KEY_ID`: R2 API token access key
- `R2_SECRET_ACCESS_KEY`: R2 API token secret key
- `R2_BUCKET_NAME`: Name of your R2 bucket
- `R2_ENDPOINT`: R2 API endpoint (https://{account-id}.r2.cloudflarestorage.com)
- `R2_PUBLIC_ENDPOINT`: Public URL for file access
- `R2_REGION`: Always "auto" for R2
