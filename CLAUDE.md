# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a proof-of-concept PHP application that demonstrates:
1. **Git LFS Integration**: Tracking large files (PDFs) with Git LFS and testing with GitHub
2. **Cloudflare R2 Storage**: Uploading, downloading, and managing files in R2 as a distribution CDN
3. **Signed URLs**: Generating temporary access URLs for controlled file distribution

The project provides working code that serves as a foundation for a real application using R2 for file hosting.

## Understanding Git LFS

**What is Git LFS?**

Git LFS (Large File Storage) is an extension to Git that handles large files efficiently. Instead of storing the actual file content in your Git repository, Git LFS stores a small "pointer" file (128 bytes) that references the actual file content stored separately.

**Why use Git LFS?**

- **Repository Performance**: Keeps repository size small (your `.git` directory stays manageable)
- **Cloning Speed**: Cloning is fast because you're downloading pointers, not 100MB PDFs
- **Version Control**: Track changes to large files with full Git history
- **Bandwidth**: Only download the specific versions you need

**How it works:**

1. You tell Git which file types to track with LFS (via `.gitattributes`)
2. When you add a large file, Git LFS stores it separately and commits a pointer
3. When you push, LFS files go to LFS storage (not your regular Git remote)
4. When you clone, Git LFS automatically fetches the actual files

**Key file: `.gitattributes`**

This file defines which file types Git LFS should track:
```
*.pdf filter=lfs diff=lfs merge=lfs -text
*.zip filter=lfs diff=lfs merge=lfs -text
```

Every developer must have `.gitattributes` committed to git (not ignored). It's how Git knows to use LFS.

## 5-Minute Quick Start

**Prerequisites:** PHP 8.1+, Composer, Git LFS installed (`brew install git-lfs`)

```bash
# 1. Set up project (2 minutes)
composer install
cp .env.example .env
# Edit .env with your Cloudflare credentials (see R2 Setup above)

# 2. Initialize Git LFS (1 minute)
git lfs install
git lfs track "*.pdf"
git add .gitattributes
git commit -m "Initialize Git LFS"

# 3. Test R2 connection (1 minute)
php scripts/list-r2.php
# Should show: "No files found." or list existing files

# 4. Test locally (1 minute)
composer test
```

If all commands succeed, your PoC is ready!

## Common Commands

### Initial Setup

```bash
# Install dependencies
composer install

# Copy environment configuration
cp .env.example .env
# Then edit .env with your Cloudflare R2 credentials

# Initialize Git LFS (one-time)
git lfs install
git lfs track "*.pdf"
git add .gitattributes
git commit -m "Initialize Git LFS"
```

### Development Commands

```bash
# Run tests
composer test

# Lint PHP syntax
composer lint

# Upload a file to R2
php scripts/upload-to-r2.php path/to/file.pdf

# Download a file from R2
php scripts/download-from-r2.php documents/filename.pdf path/to/save.pdf

# List files in R2
php scripts/list-r2.php documents/
```

## Git LFS Testing Workflow

### Local Testing

1. **Create a test PDF** (or use an existing one):
   ```bash
   # Create a dummy PDF for testing
   echo "%PDF-1.4" > documents/test.pdf
   echo "Test content" >> documents/test.pdf
   ```

2. **Add file with Git LFS**:
   ```bash
   git add documents/test.pdf
   git commit -m "Add test PDF with Git LFS"
   ```

3. **Verify LFS tracking locally**:
   ```bash
   # View Git LFS status
   git lfs ls-files

   # Should show: documents/test.pdf (<size>) <hash>
   ```

### GitHub Testing

1. **Push to GitHub** (assuming remote is configured):
   ```bash
   git push origin main
   ```

2. **Verify on GitHub**:
   - Open the file in GitHub's web interface
   - In the raw view, you should see the LFS pointer format (not the actual file):
     ```
     version https://git-lfs.github.com/spec/v1
     oid sha256:...
     size ...
     ```

3. **Clone and verify LFS download**:
   ```bash
   cd /tmp
   git clone https://github.com/username/repo.git test-clone
   cd test-clone
   ls -lh documents/test.pdf  # Should show actual file size, not pointer
   ```

### Setting Up GitHub Remote

1. **Create repository on GitHub**:
   - Go to [github.com/new](https://github.com/new)
   - Repository name: `git-lfs-poc` (or your preferred name)
   - Description: "Git LFS + Cloudflare R2 Proof of Concept"
   - Choose public or private (private recommended for PoC)
   - Do NOT initialize with README/gitignore (we already have them)
   - Click **Create Repository**

2. **Connect local repository to GitHub** (from your project directory):
   ```bash
   # Add GitHub as remote (replace username/repo)
   git remote add origin https://github.com/username/git-lfs-poc.git

   # Verify remote is set correctly
   git remote -v
   # Should show:
   # origin  https://github.com/username/git-lfs-poc.git (fetch)
   # origin  https://github.com/username/git-lfs-poc.git (push)
   ```

3. **Push to GitHub**:
   ```bash
   # If you haven't initialized a commit yet:
   git add .
   git commit -m "Initial commit: Git LFS + R2 PoC"

   # Push to GitHub
   git branch -M main
   git push -u origin main
   # -u sets this branch to track the remote (future pushes are easier)
   ```

4. **Verify on GitHub**:
   - Go to your repository on GitHub
   - You should see all files (except those in `.gitignore`)
   - Check that `.gitattributes` is present (critical for LFS)
   - Any PDFs should show "Stored with Git LFS" badge

5. **Enable Git LFS on GitHub** (one-time per repository):
   - Go to repository **Settings** → **Data services**
   - Under "Git LFS" section, verify it says "Git LFS is enabled"
   - GitHub automatically enables LFS support for public repos with LFS files

**Note on LFS Bandwidth:**
- GitHub provides 1GB free LFS bandwidth/month
- Each account gets additional 50GB free LFS storage
- If you exceed limits during testing, delete old test files and rebalance

## Cloudflare R2 Setup & Testing

### Step-by-Step Configuration

**Step 1: Get Your Cloudflare Account ID**
1. Log into [Cloudflare Dashboard](https://dash.cloudflare.com/)
2. Go to **Account Settings** (bottom left)
3. Look for **Account ID** in the right sidebar
4. Copy it (format: `abc123def456`)

**Step 2: Create R2 API Token**
1. In Cloudflare Dashboard, go to **Account Settings** → **API Tokens**
2. Click **Create Token**
3. Select template: **Edit Cloudflare Workers** (or create custom)
4. Under **Permissions**, select:
   - `Account` > `R2` > `Edit`
5. Click **Continue to Summary** → **Create Token**
6. You'll see one-time display of:
   - **Access Key ID** (copy this)
   - **Secret Access Key** (copy this - only shown once!)
7. Save both securely

**Step 3: Create R2 Bucket**
1. In Cloudflare Dashboard, go to **Storage** → **R2**
2. Click **Create Bucket**
3. Enter name: `git-lfs-poc` (or your chosen name)
4. Choose region (recommend: auto-select or us-west-2)
5. Click **Create Bucket**
6. You now have `R2_BUCKET_NAME=git-lfs-poc`

**Step 4: Set Up Public Access (Optional, for CDN)**

If you want to serve files via public URL (recommended for CDN testing):

1. In R2 bucket, go to **Settings** → **CORS**
2. Add CORS rule (allows downloads from any origin):
   ```json
   {
     "AllowedOrigins": ["*"],
     "AllowedMethods": ["GET"],
     "AllowedHeaders": ["*"]
   }
   ```
3. Go to **Settings** → **Public Access**
4. Click **Allow access** under "Public access to buckets"
5. Your public endpoint: `https://git-lfs-poc.r2.dev` (or link a custom domain)

**Step 5: Configure `.env` File**
```bash
# Copy template
cp .env.example .env

# Edit with your values
nano .env
```

Fill in these values (example):
```
R2_ACCOUNT_ID=1234567890abcdef1234567890abcdef
R2_ACCESS_KEY_ID=1234567890abcdef1234567890abcdef
R2_SECRET_ACCESS_KEY=secret_key_1234567890abcdef1234567890abcdef
R2_BUCKET_NAME=git-lfs-poc
R2_ENDPOINT=https://1234567890abcdef1234567890abcdef.r2.cloudflarestorage.com
R2_PUBLIC_ENDPOINT=https://git-lfs-poc.r2.dev
R2_REGION=auto
```

**Step 6: Test Configuration**
```bash
php scripts/list-r2.php
# Should show: "No files found." (bucket is empty)
# If it shows an error, check your credentials in .env
```

### R2 Operations

```bash
# Upload test PDF to R2
php scripts/upload-to-r2.php documents/test.pdf

# List files in R2 bucket
php scripts/list-r2.php

# Download file from R2
php scripts/download-from-r2.php documents/test.pdf /tmp/downloaded.pdf

# For scripting, check the response format in src/R2Client.php
```

## R2Client API Reference

### Main Integration Class: `src/R2Client.php`

#### `uploadFile(string $filePath, string $key): array`

Upload a file from your server to R2.

**Parameters:**
- `$filePath`: Local file path (e.g., `documents/sample.pdf`)
- `$key`: Path in R2 bucket (e.g., `documents/sample.pdf`)

**Returns:** Array with upload result
```php
[
    'success' => true,
    'key' => 'documents/sample.pdf',
    'url' => 'https://git-lfs-poc.r2.dev/documents/sample.pdf',
    'etag' => '"abc123xyz789"'  // For integrity verification
]
```

**Example:**
```php
$result = $r2Client->uploadFile('/tmp/sample.pdf', 'documents/sample.pdf');
if ($result['success']) {
    echo "Download at: " . $result['url'];
} else {
    echo "Error: " . $result['error'];
}
```

---

#### `downloadFile(string $key, string $savePath): bool`

Download a file from R2 to your server.

**Parameters:**
- `$key`: Path in R2 bucket (e.g., `documents/sample.pdf`)
- `$savePath`: Where to save file locally (e.g., `/tmp/downloaded.pdf`)

**Returns:** `true` on success, `false` on failure

**Example:**
```php
if ($r2Client->downloadFile('documents/sample.pdf', '/tmp/sample.pdf')) {
    echo "Downloaded successfully";
} else {
    echo "Download failed";
}
```

---

#### `generateSignedUrl(string $key, int $expirationSeconds = 3600): string`

Create a temporary access URL that expires after a set time. Useful for controlling access without making files public.

**Parameters:**
- `$key`: Path in R2 bucket (e.g., `documents/sample.pdf`)
- `$expirationSeconds`: How long URL is valid (default: 3600 = 1 hour)

**Returns:** URL string that works for the specified duration

**Example:**
```php
// URL valid for 30 minutes (1800 seconds)
$tempUrl = $r2Client->generateSignedUrl('documents/sample.pdf', 1800);
echo "Link valid for 30 min: " . $tempUrl;

// URL valid for 1 week
$weekUrl = $r2Client->generateSignedUrl('documents/sample.pdf', 7 * 24 * 3600);
```

---

#### `getPublicUrl(string $key): string`

Get the permanent public URL for a file (only works if bucket is public).

**Parameters:**
- `$key`: Path in R2 bucket (e.g., `documents/sample.pdf`)

**Returns:** Public URL string

**Example:**
```php
$url = $r2Client->getPublicUrl('documents/sample.pdf');
// Returns: https://git-lfs-poc.r2.dev/documents/sample.pdf
```

---

#### `listFiles(string $prefix = ''): array`

List all files in R2 bucket (optionally filtered by prefix).

**Parameters:**
- `$prefix`: Filter by folder prefix (e.g., `documents/` shows only files in that folder)

**Returns:** Array of file information
```php
[
    [
        'key' => 'documents/sample.pdf',
        'size' => 1048576,  // bytes
        'modified' => '2025-02-02 14:30:00',
        'url' => 'https://git-lfs-poc.r2.dev/documents/sample.pdf'
    ],
    // ... more files
]
```

**Example:**
```php
$files = $r2Client->listFiles('documents/');
foreach ($files as $file) {
    echo $file['key'] . " (" . $file['size'] . " bytes)\n";
}
```

---

#### `deleteFile(string $key): bool`

Remove a file from R2.

**Parameters:**
- `$key`: Path in R2 bucket to delete

**Returns:** `true` on success, `false` on failure

**Example:**
```php
if ($r2Client->deleteFile('documents/old-file.pdf')) {
    echo "File deleted";
} else {
    echo "Delete failed";
}
```

---

### Core Components

- **src/R2Client.php**: Main R2 integration class (documented above)
- **src/Config.php**: Environment configuration loader

- **src/Config.php**: Environment variable loader
  - Reads from `.env` file
  - Provides typed access to configuration values

- **scripts/**: CLI tools for testing
  - `upload-to-r2.php`: Command-line upload utility
  - `download-from-r2.php`: Command-line download utility
  - `list-r2.php`: List bucket contents

### Configuration

- **.gitattributes**: Defines which files Git LFS should track (PDFs, ZIPs, etc.)
- **.env**: Runtime configuration (not in git, use `.env.example` as template)
- **composer.json**: PHP dependencies (AWS SDK for R2 API access)

### File Structure

```
.
├── src/                    # PHP source code
│   ├── R2Client.php       # R2 client implementation
│   └── Config.php         # Configuration loader
├── scripts/               # CLI tools
│   ├── upload-to-r2.php
│   ├── download-from-r2.php
│   └── list-r2.php
├── documents/             # Directory for test files (tracked by LFS)
├── tests/                 # PHPUnit tests
├── .gitattributes         # Git LFS configuration
├── .env.example           # Environment template
├── composer.json          # PHP dependencies
└── README.md              # User documentation
```

## Testing & Validation

### Unit Tests

```bash
composer test
```

Tests verify configuration loading and environment variable handling.

### Manual Integration Testing

1. **Test local Git LFS workflow**:
   - Add PDF to repo
   - Verify `git lfs ls-files` shows it
   - Push and clone to confirm LFS works

2. **Test R2 integration**:
   - Upload a file: `php scripts/upload-to-r2.php documents/test.pdf`
   - Verify file appears in R2 bucket
   - Download it back: `php scripts/download-from-r2.php documents/test.pdf /tmp/test.pdf`
   - Verify downloaded file matches original

3. **Test signed URLs** (use in production code):
   - See example in R2Client.php `generateSignedUrl()` method
   - URLs expire after 1 hour by default (configurable)

## Key Implementation Details

### AWS SDK Configuration for R2

The project uses AWS SDK for PHP configured for R2:
- **Endpoint**: Points to R2 API (not AWS S3)
- **Region**: Always "auto" for R2
- **Credentials**: Uses R2 API token (not AWS credentials)
- **S3-compatible API**: Works with standard S3 operations

### File Upload Process

1. Detect file MIME type
2. Upload to R2 with metadata
3. Return public URL and file key
4. ETag for integrity verification

### Security Considerations

- R2 credentials stored in `.env` (excluded from git)
- Signed URLs use time-based expiration (default 1 hour)
- Public URLs can be restricted via bucket policies
- File deletion requires explicit authorization (API call only)

## Common Development Tasks

### Adding a new CLI command

1. Create script in `scripts/` (e.g., `scripts/new-command.php`)
2. Add executable shebang: `#!/usr/bin/env php`
3. Load config and R2Client same as existing scripts
4. Add entry to composer.json `scripts` section if needed

### Testing with real PDFs

1. Place actual PDF in `documents/` directory
2. PDF will be tracked by Git LFS automatically (due to `.gitattributes`)
3. Upload to R2 using: `php scripts/upload-to-r2.php documents/yourfile.pdf`
4. Retrieve public URL from script output

### Extending R2Client

- Add new methods following existing pattern (try/catch for AwsException)
- Return consistent array format: `['success' => bool, ...]`
- Document parameters and return values for future use

## Deployment to Test Server

Deploy the PoC to your test server at `https://test.elanregistry.org/lfs-test` for remote testing and validation.

### Prerequisites

- SSH access to test.elanregistry.org
- PHP 8.1+ installed on server
- Composer installed on server
- Git and Git LFS installed on server
- Web server (Apache/Nginx) configured for the domain

### Deployment Steps

**Step 1: SSH into Test Server**

```bash
ssh user@test.elanregistry.org
cd /path/to/web/root
# Navigate to where lfs-test should be deployed
# (Adjust path based on your server structure)
```

**Step 2: Clone Repository**

```bash
# Clone from GitHub
git clone https://github.com/username/git-lfs-poc.git lfs-test
cd lfs-test

# Ensure Git LFS is installed and tracking is enabled
git lfs install
git lfs pull  # Download LFS files
```

**Step 3: Set Up Environment**

```bash
# Copy environment template
cp .env.example .env

# Edit with server-specific configuration
nano .env
```

Update `.env` with:
- **R2 credentials** (same as local, or use test account)
- **R2_PUBLIC_ENDPOINT**: Ensure it points to publicly accessible endpoint
- Any server-specific paths or settings

**Step 4: Install Dependencies**

```bash
composer install --no-dev
# --no-dev excludes dev dependencies like PHPUnit to reduce size
```

**Step 5: Set File Permissions**

```bash
# Make scripts executable
chmod +x scripts/*.php

# Ensure documents directory is writable (for uploads/downloads)
chmod 755 documents/
chmod 755 /tmp  # or wherever temporary files are stored
```

**Step 6: Configure Web Server Access**

For Apache:
```bash
# Create .htaccess to allow script execution (if needed)
cat > .htaccess << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On
    # Allow direct access to files
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]
</IfModule>

# Deny access to sensitive files
<Files .env>
    Order allow,deny
    Deny from all
</Files>

<Files composer.json>
    Order allow,deny
    Deny from all
</Files>
EOF
```

For Nginx:
```bash
# Configure in your nginx server block:
# location ~ /\.env {
#     deny all;
# }
# location ~ /composer.json {
#     deny all;
# }
```

**Step 7: Verify Deployment**

```bash
# Test R2 connectivity
php scripts/list-r2.php
# Should show existing files or "No files found."

# Run tests (optional, if dev dependencies installed)
composer test
```

### Accessing the Deployment

Once deployed, you can access the PoC at:
- **Base URL**: `https://test.elanregistry.org/lfs-test/`
- **Scripts**: Run directly via SSH: `php scripts/upload-to-r2.php path/to/file.pdf`

### Testing Remote Operations

**Upload files to R2 from test server:**
```bash
cd /path/to/lfs-test
php scripts/upload-to-r2.php documents/test.pdf
```

**Test Git LFS pull on fresh clone:**
```bash
# On your local machine
git clone https://github.com/username/git-lfs-poc.git test-clone-remote
cd test-clone-remote
ls -lh documents/
# Should download LFS files automatically
```

### Updating Deployment

To push updates to the test server:

**From local machine:**
```bash
# Make changes locally
git add .
git commit -m "Update: describe changes"
git push origin main
```

**On test server:**
```bash
cd /path/to/lfs-test
git pull origin main
git lfs pull  # Download any new/updated LFS files
composer install --no-dev
# Restart web server if needed
```

### Deployment Troubleshooting

**"Git LFS not installed" error:**
```bash
# Install Git LFS on server
sudo apt-get install git-lfs  # Ubuntu/Debian
# or
brew install git-lfs  # macOS
```

**"Permission denied" for scripts:**
```bash
# Make scripts executable
chmod +x scripts/*.php
# Or run via PHP directly
php scripts/list-r2.php
```

**"Cannot write to documents directory":**
```bash
# Fix permissions
chmod 755 documents/
chmod 755 $(dirname "$TMPDIR")  # Temp directory for uploads
```

**R2 operations fail on server but work locally:**
- Verify `.env` file exists and has correct credentials
- Verify `.env` is NOT in git (check `.gitignore`)
- Test connectivity: `curl https://your-account-id.r2.cloudflarestorage.com`
- Check server firewall/security groups allow outbound HTTPS

**LFS files not downloading on server:**
```bash
# Manually trigger LFS pull
git lfs pull
git lfs ls-files  # Verify tracking
```

### Monitoring Deployment

**Check server logs:**
```bash
# PHP error logs
tail -f /var/log/php-fpm.log

# Web server logs
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

**Monitor R2 activity:**
- Log into Cloudflare Dashboard
- Go to **R2** → your bucket
- View upload/download statistics and costs

## Troubleshooting

### "Bucket not found" error
- Verify R2_BUCKET_NAME matches actual bucket name in Cloudflare
- Check R2_ENDPOINT has correct account ID

### "Access denied" error
- Verify R2 API token credentials in `.env`
- Check token has R2 read/write permissions in Cloudflare

### Git LFS not tracking files
- Run `git lfs install` in repository
- Verify `.gitattributes` exists and is committed
- Add file again after `.gitattributes` is in git

### Large file not downloading from GitHub
- Check GitHub shows "LFS Pointer" in web view, not actual content
- Run `git lfs pull` to fetch actual file content
- Verify `.gitattributes` is in main branch

## Dependencies

- **aws/aws-sdk-php**: ^3.0 - AWS SDK for PHP (used with R2)
- **phpunit/phpunit**: ^10.0 - Testing framework (dev only)
- **php**: ^8.1 - Runtime requirement
