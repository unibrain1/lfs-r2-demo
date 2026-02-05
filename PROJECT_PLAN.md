# Git LFS + Cloudflare R2 Proof-of-Concept
## Project Plan

**Project Type:** Technical Proof-of-Concept
**Target Environment:** Local development → GitHub → test.elanregistry.org
**Primary Goal:** Automated deployment of Git LFS-tracked files to Cloudflare R2 storage

---

## Project Overview

### Success Definition
A working end-to-end system where:
1. Large files (PDFs) are tracked in Git via LFS, not stored directly in repository
2. These files are automatically deployed to Cloudflare R2 storage when code is pushed to GitHub
3. Files are accessible via R2 signed URLs or CDN for production use
4. Complete documentation enables team replication and production rollout

### Scope Boundaries
**In Scope:**
- Git LFS fundamentals and workflow
- GitHub repository with LFS tracking
- Cloudflare R2 storage setup and testing
- Automated deployment via GitHub webhooks
- PHP/AWS SDK integration patterns
- Documentation and code examples

**Out of Scope:**
- Production deployment (this is POC only)
- User authentication/authorization for R2 files
- Performance optimization or load testing
- Cost analysis beyond basic understanding
- Integration with existing application code
- Multi-environment deployment (dev/staging/prod)

### Critical Dependencies
- GitHub account with LFS enabled (free tier supports 1GB storage, 1GB bandwidth/month)
- Cloudflare account with R2 enabled (free tier: 10GB storage, no egress fees)
- Access to test.elanregistry.org server with PHP/Composer
- Git LFS client installed locally
- AWS SDK for PHP (for R2 S3-compatible API)

---

## Phase 1: Git LFS Learning & Showcase

**Phase Goal:** Demonstrate complete Git LFS workflow with real files on GitHub

**Phase Duration Estimate:** Small (1-2 focused work sessions)

**Prerequisites:**
- Git installed locally (version 2.x+)
- GitHub account created
- Basic Git knowledge (commit, push, pull, clone)

### Task 1.1: Git LFS Installation & Configuration
**Objective:** Install Git LFS client and understand core concepts

**Subtasks:**
- [ ] Install Git LFS client on local machine
  - macOS: `brew install git-lfs`
  - Verification: `git lfs version`
- [ ] Initialize Git LFS globally: `git lfs install`
- [ ] Document what Git LFS is and why it exists

**Acceptance Criteria:**
- `git lfs version` returns valid version number
- `git lfs install` completes without errors
- Documentation section created explaining:
  - Problem: Large binary files bloat Git repositories
  - Solution: LFS stores pointers in Git, actual files in separate storage
  - Benefit: Fast clones, smaller repo size, version control for large files

**Validation Steps:**
1. Run `git lfs version` - should show version 3.x or higher
2. Run `git config --global --list | grep lfs` - should show LFS configuration
3. Review documentation - confirm clear problem/solution explanation

**Known Challenges:**
- **Challenge:** Git LFS not available via package manager
  - **Mitigation:** Download from official GitHub releases page
- **Challenge:** Confusion between Git LFS and Git itself
  - **Mitigation:** Document that LFS is an extension, not a replacement

**Estimated Effort:** Trivial (15-30 minutes)

---

### Task 1.2: Create GitHub Repository with LFS Tracking
**Objective:** Set up new repository with LFS enabled for PDF files

**Subtasks:**
- [ ] Create new GitHub repository: `git-lfs-poc` (public or private)
- [ ] Clone repository locally
- [ ] Configure LFS to track PDF files: `git lfs track "*.pdf"`
- [ ] Verify `.gitattributes` file created correctly
- [ ] Commit `.gitattributes` to repository

**Acceptance Criteria:**
- Repository created on GitHub (URL: `github.com/[username]/git-lfs-poc`)
- `.gitattributes` file exists with content: `*.pdf filter=lfs diff=lfs merge=lfs -text`
- `.gitattributes` committed and pushed to GitHub
- Running `git lfs track` shows `*.pdf` pattern tracked

**Validation Steps:**
1. Navigate to GitHub repository in browser
2. Verify `.gitattributes` file exists in repository root
3. Click file and verify it contains PDF tracking pattern
4. Run `git lfs track` locally - should list `*.pdf`

**Known Challenges:**
- **Challenge:** `.gitattributes` not committed, only exists locally
  - **Mitigation:** Explicitly commit with `git add .gitattributes && git commit -m "Configure LFS tracking for PDFs"`
- **Challenge:** Tracking pattern too broad (e.g., `*` tracks everything)
  - **Mitigation:** Use specific pattern `*.pdf` as specified in scope

**Estimated Effort:** Trivial (15-30 minutes)

---

### Task 1.3: Add Test PDF Files with LFS
**Objective:** Commit test PDFs and verify they're tracked by LFS, not stored in Git directly

**Subtasks:**
- [ ] Create or obtain 1-2 small test PDF files (50-100KB each)
  - Option A: Generate simple PDFs with sample content
  - Option B: Download public domain PDFs
- [ ] Add PDFs to repository: `git add test-file-1.pdf test-file-2.pdf`
- [ ] Verify files will be tracked by LFS: `git lfs status`
- [ ] Commit files: `git commit -m "Add test PDF files via LFS"`
- [ ] Push to GitHub: `git push origin main`

**Acceptance Criteria:**
- 1-2 PDF files exist in repository (under 200KB total size)
- `git lfs status` before commit shows files in "Git LFS objects to be committed"
- Commit completes successfully
- Push to GitHub shows LFS upload progress
- GitHub shows files exist in repository web interface

**Validation Steps:**
1. Run `git lfs ls-files` - should list both PDF files
2. Check `.git/lfs/objects` directory - should contain file objects
3. View raw file on GitHub - should show LFS pointer (starts with "version https://git-lfs.github.com/spec/v1")
4. View file normally on GitHub - should show PDF preview or download option

**Known Challenges:**
- **Challenge:** Files committed before LFS tracking configured
  - **Mitigation:** If files show up in `git diff --stat` with large size changes, they're not LFS-tracked. Remove and re-add after configuring tracking.
- **Challenge:** LFS quota exceeded on GitHub free tier
  - **Mitigation:** Use small test files (50-100KB each), well under 1GB limit
- **Challenge:** Authentication issues when pushing LFS files
  - **Mitigation:** Use personal access token with `repo` scope, not password

**Estimated Effort:** Small (30-60 minutes including file preparation)

---

### Task 1.4: Test Full LFS Workflow (Clone & Verify)
**Objective:** Prove LFS files download automatically when repository is cloned

**Subtasks:**
- [ ] Clone repository to different location (simulate different machine)
  - Example: `git clone https://github.com/[username]/git-lfs-poc.git ~/Desktop/lfs-test-clone`
- [ ] Verify LFS files downloaded automatically during clone
- [ ] Check file integrity (files are actual PDFs, not pointers)
- [ ] Document complete workflow from commit to clone

**Acceptance Criteria:**
- Repository cloned successfully to new location
- PDF files exist in working directory and are valid (can be opened)
- `git lfs ls-files` in cloned repo shows PDF files tracked
- Clone output shows LFS download progress (e.g., "Downloading LFS objects: 100% (2/2)")
- Workflow documentation covers: track → add → commit → push → clone → verify

**Validation Steps:**
1. Navigate to cloned directory
2. Run `ls -lh *.pdf` - should show full file sizes (50-100KB), not tiny pointer files
3. Open one PDF file - should render correctly, not show pointer text
4. Run `git lfs ls-files` - should show same files as original repo
5. Check `.git/lfs/objects` - should contain downloaded LFS objects

**Known Challenges:**
- **Challenge:** LFS files not downloaded automatically, only pointers present
  - **Mitigation:** Run `git lfs pull` manually. If this is needed, LFS isn't installed on clone machine.
- **Challenge:** Large files take long time to download
  - **Mitigation:** Use small test files as specified (50-100KB each)
- **Challenge:** Network issues during LFS download
  - **Mitigation:** LFS downloads can be resumed with `git lfs pull`

**Estimated Effort:** Small (30-45 minutes)

---

### Task 1.5: Create Phase 1 Documentation
**Objective:** Document Git LFS concepts, workflow, and proof-of-concept results

**Subtasks:**
- [ ] Create `docs/phase1-git-lfs.md` documentation file
- [ ] Document Git LFS concepts (what, why, how)
- [ ] Document complete workflow with commands
- [ ] Include screenshots or examples of LFS pointers vs. actual files
- [ ] Document benefits and limitations discovered
- [ ] Include troubleshooting section for common issues

**Acceptance Criteria:**
- Documentation file exists in repository
- Concepts section explains:
  - What Git LFS is (extension to Git for large file storage)
  - Why it's needed (repo bloat, clone speed, version control for binaries)
  - How it works (pointers in Git, actual files in LFS storage)
- Workflow section includes all commands with explanations
- Benefits listed: smaller repo size, faster clones, version control for large files
- Limitations listed: quota limits, requires LFS client, additional setup
- Troubleshooting covers: LFS not installed, files not tracked, authentication issues

**Validation Steps:**
1. Read documentation as if you're a new team member
2. Verify all commands are correct and complete (can be copy-pasted)
3. Check that benefits and limitations are realistic and accurate
4. Confirm troubleshooting section addresses challenges encountered

**Known Challenges:**
- **Challenge:** Documentation too technical or too basic
  - **Mitigation:** Target audience: developers with Git experience but no LFS knowledge
- **Challenge:** Missing critical workflow steps
  - **Mitigation:** Review actual commands used in Tasks 1.1-1.4

**Estimated Effort:** Small (1-2 hours)

---

### Phase 1: Go/No-Go Decision Point

**Required Outcomes:**
- [ ] Git LFS installed and working locally
- [ ] GitHub repository with LFS-tracked PDF files
- [ ] Successful clone test proving automatic LFS download
- [ ] Documentation explaining Git LFS concepts and workflow
- [ ] All validation steps passed

**Decision Criteria:**
- **GO to Phase 2 if:** All tasks completed, workflow proven end-to-end, documentation clear
- **NO-GO if:** Cannot clone LFS files automatically, GitHub quota concerns, fundamental LFS issues

**Stakeholder Questions Before Phase 2:**
1. Does the Git LFS workflow meet expectations for simplicity?
2. Are there concerns about GitHub LFS storage limits (1GB free)?
3. Is the documentation sufficient for team understanding?
4. Should we proceed with Cloudflare R2 integration?

**Phase 1 Deliverables:**
- Working GitHub repository: `git-lfs-poc`
- 1-2 test PDF files tracked by LFS
- Documentation: `docs/phase1-git-lfs.md`
- Validated end-to-end workflow

---

## Phase 2: Cloudflare R2 Understanding

**Phase Goal:** Hands-on understanding of R2 storage with PHP integration patterns

**Phase Duration Estimate:** Medium (2-4 focused work sessions)

**Prerequisites:**
- Phase 1 completed successfully
- Cloudflare account created
- R2 enabled in Cloudflare account (may require credit card even for free tier)
- PHP 8.0+ installed locally
- Composer installed locally

### Task 2.1: R2 Account Setup & Bucket Creation
**Objective:** Configure Cloudflare R2 and create storage bucket for testing

**Subtasks:**
- [ ] Log in to Cloudflare dashboard
- [ ] Navigate to R2 section
- [ ] Enable R2 (accept terms, add payment method if required)
- [ ] Create new R2 bucket: `lfs-poc-bucket`
- [ ] Configure bucket settings (public access: off for now)
- [ ] Generate R2 API token with read/write permissions
- [ ] Document API token details (keep secret, store in `.env.example` format)

**Acceptance Criteria:**
- R2 enabled in Cloudflare account
- Bucket created with name `lfs-poc-bucket`
- Bucket visible in R2 dashboard
- API token generated with permissions: Object Read & Write for `lfs-poc-bucket`
- Token details documented securely (account ID, access key ID, secret access key)
- `.env.example` file created with token placeholder format

**Validation Steps:**
1. Navigate to R2 dashboard - should show `lfs-poc-bucket`
2. Click bucket - should show empty bucket (0 objects, 0 bytes)
3. Navigate to API Tokens - should show newly created token
4. Verify token permissions - should include Object Read & Write
5. Copy account ID, access key ID, secret access key to secure location

**Known Challenges:**
- **Challenge:** R2 requires payment method even for free tier
  - **Mitigation:** Cloudflare policy, must add credit card (won't charge within free tier limits)
- **Challenge:** API token permissions unclear
  - **Mitigation:** Use "Object Read & Write" template, scope to specific bucket
- **Challenge:** Confusion between R2 API token and Cloudflare API token
  - **Mitigation:** Use R2-specific token (under R2 settings, not general API tokens)

**Estimated Effort:** Small (30-60 minutes including account setup)

---

### Task 2.2: Understand R2 Concepts & Pricing
**Objective:** Document R2 fundamentals, use cases, and cost structure

**Subtasks:**
- [ ] Research R2 architecture and how it differs from S3, B2, etc.
- [ ] Document R2 pricing model (storage, operations, egress)
- [ ] Understand R2 free tier limits
- [ ] Research R2 integration with Cloudflare CDN
- [ ] Document when to use R2 vs. alternatives (S3, local storage, etc.)
- [ ] Create comparison table for decision-making

**Acceptance Criteria:**
- Documentation section created: `docs/phase2-r2-concepts.md`
- R2 architecture explained:
  - S3-compatible API (works with AWS SDK)
  - Global distribution via Cloudflare network
  - No egress fees (major differentiator from S3)
- Pricing model documented:
  - Storage: $0.015/GB/month (free tier: 10GB)
  - Class A operations (write, list): $4.50/million (free tier: 1M/month)
  - Class B operations (read): $0.36/million (free tier: 10M/month)
  - Egress: $0/GB (unlimited free)
- Use case comparison table included:
  - When to use R2: Large files, high bandwidth, cost optimization
  - When not to use R2: Small files with rare access, need for specific S3 features

**Validation Steps:**
1. Review documentation for accuracy against official Cloudflare R2 docs
2. Verify pricing information is current (as of 2026)
3. Check that comparison table addresses project-specific use cases
4. Confirm free tier limits are clearly stated

**Known Challenges:**
- **Challenge:** Pricing information may be outdated
  - **Mitigation:** Reference official Cloudflare R2 pricing page, include documentation date
- **Challenge:** Comparison may be biased toward R2
  - **Mitigation:** Include honest limitations (e.g., fewer features than S3, newer service)

**Estimated Effort:** Medium (2-3 hours including research)

---

### Task 2.3: Set Up PHP/AWS SDK Integration
**Objective:** Configure local PHP environment to interact with R2 via S3-compatible API

**Subtasks:**
- [ ] Create new PHP project directory: `r2-integration-test/`
- [ ] Initialize Composer: `composer init`
- [ ] Install AWS SDK for PHP: `composer require aws/aws-sdk-php`
- [ ] Create `.env` file with R2 credentials (add to `.gitignore`)
- [ ] Create `config.php` to load credentials and configure SDK
- [ ] Test SDK connection to R2 (list buckets)

**Acceptance Criteria:**
- PHP project directory created with Composer initialized
- `composer.json` includes `aws/aws-sdk-php` dependency
- `.env` file exists with required variables:
  ```
  R2_ACCOUNT_ID=your_account_id
  R2_ACCESS_KEY_ID=your_access_key_id
  R2_SECRET_ACCESS_KEY=your_secret_access_key
  R2_BUCKET_NAME=lfs-poc-bucket
  ```
- `.gitignore` includes `.env` to prevent credential exposure
- `config.php` exists and loads credentials from `.env`
- Test script successfully connects to R2 and lists buckets

**Validation Steps:**
1. Run `composer install` - should download AWS SDK without errors
2. Run `php -r "require 'vendor/autoload.php'; echo 'SDK loaded';"` - should output "SDK loaded"
3. Run test script - should output bucket list including `lfs-poc-bucket`
4. Verify `.env` is in `.gitignore` - run `git status`, should not show `.env` as untracked

**Known Challenges:**
- **Challenge:** S3 SDK endpoint configuration for R2
  - **Mitigation:** Use endpoint format: `https://{accountId}.r2.cloudflarestorage.com`
- **Challenge:** Credentials loaded incorrectly from `.env`
  - **Mitigation:** Use library like `vlucas/phpdotenv` or simple `parse_ini_file()`
- **Challenge:** SSL/TLS certificate verification issues
  - **Mitigation:** Ensure PHP has up-to-date CA certificates bundle

**Estimated Effort:** Medium (1-2 hours)

---

### Task 2.4: Test R2 File Operations (Upload/Download)
**Objective:** Implement and test basic file operations with R2 using PHP

**Subtasks:**
- [ ] Create `upload.php` script to upload test PDF to R2
- [ ] Test upload with one of the Phase 1 test PDFs
- [ ] Verify file appears in R2 dashboard
- [ ] Create `download.php` script to download file from R2
- [ ] Test download and verify file integrity (compare checksums)
- [ ] Create `list.php` script to list all objects in bucket
- [ ] Document all operations with code examples

**Acceptance Criteria:**
- `upload.php` script exists and successfully uploads file to R2
- File visible in R2 dashboard with correct size
- `download.php` script exists and successfully downloads file from R2
- Downloaded file matches original (MD5 checksum identical)
- `list.php` script exists and displays all bucket objects
- All scripts include error handling and status messages
- Code examples documented in `docs/phase2-r2-php-examples.md`

**Validation Steps:**
1. Run `php upload.php test-file-1.pdf` - should output "Upload successful"
2. Check R2 dashboard - should show `test-file-1.pdf` in bucket
3. Run `php download.php test-file-1.pdf downloaded-file.pdf` - should output "Download successful"
4. Run `md5 test-file-1.pdf downloaded-file.pdf` - checksums should match
5. Run `php list.php` - should output list including `test-file-1.pdf`

**Known Challenges:**
- **Challenge:** Large file uploads timing out
  - **Mitigation:** Test files are small (50-100KB), but document multipart upload for larger files
- **Challenge:** File permissions issues on downloaded files
  - **Mitigation:** Set appropriate permissions after download: `chmod 644`
- **Challenge:** Memory issues with file operations
  - **Mitigation:** Use streaming operations, not loading entire file into memory

**Estimated Effort:** Medium (2-3 hours including testing)

---

### Task 2.5: Test R2 Signed URLs
**Objective:** Generate and test pre-signed URLs for secure, temporary file access

**Subtasks:**
- [ ] Create `generate-signed-url.php` script
- [ ] Generate signed URL for uploaded test PDF (1-hour expiration)
- [ ] Test signed URL in browser (should download/display file)
- [ ] Test URL expiration (wait or manipulate timestamp)
- [ ] Test URL without signature (should fail)
- [ ] Document signed URL use cases and security considerations

**Acceptance Criteria:**
- `generate-signed-url.php` script exists and generates valid signed URLs
- Signed URL successfully downloads file when accessed in browser
- Signed URL includes expiration timestamp (default: 1 hour)
- Expired URL returns 403 Forbidden error
- URL without signature returns 403 Forbidden error
- Documentation explains signed URL use cases:
  - Temporary file access without authentication
  - Secure downloads without exposing credentials
  - Time-limited sharing of private files

**Validation Steps:**
1. Run `php generate-signed-url.php test-file-1.pdf` - should output signed URL
2. Copy URL and paste in browser - should download/display PDF
3. Remove signature parameter from URL and try again - should return 403 error
4. Generate URL with short expiration (1 minute), wait, then try - should return 403 error
5. Review documentation - confirms security best practices

**Known Challenges:**
- **Challenge:** Signed URL generation complex with AWS SDK
  - **Mitigation:** Use `Aws\S3\S3Client::createPresignedRequest()` method, well-documented
- **Challenge:** Clock skew between local machine and R2 servers
  - **Mitigation:** Set expiration buffer (e.g., +5 minutes from now, expires in 1 hour)
- **Challenge:** Testing expiration requires waiting
  - **Mitigation:** Generate URL with very short expiration (1-2 minutes) for testing

**Estimated Effort:** Small (1-2 hours)

---

### Task 2.6: Test R2 with Cloudflare CDN (Optional)
**Objective:** Understand how to serve R2 files via Cloudflare CDN for improved performance

**Subtasks:**
- [ ] Research R2 public bucket configuration
- [ ] Configure R2 bucket for public access (if appropriate for use case)
- [ ] Set up custom domain or R2.dev subdomain for bucket
- [ ] Test file access via CDN URL vs. direct R2 URL
- [ ] Document CDN integration benefits and configuration steps

**Acceptance Criteria:**
- Documentation explains R2 + CDN integration:
  - R2.dev subdomain (automatic, free)
  - Custom domain (requires Cloudflare DNS)
  - Benefits: caching, DDoS protection, global edge network
- If tested: Files accessible via CDN URL
- If not tested: Documentation explains why deferred (e.g., not needed for POC)

**Validation Steps:**
1. Review R2 dashboard for public access settings
2. If enabled: Access file via CDN URL, verify faster response than direct R2
3. If not enabled: Documentation explains decision and future considerations

**Known Challenges:**
- **Challenge:** Public access may not be appropriate for all use cases
  - **Mitigation:** Mark as optional, document that signed URLs work without CDN
- **Challenge:** Custom domain requires DNS configuration
  - **Mitigation:** Use R2.dev subdomain for testing if custom domain not available

**Estimated Effort:** Small (1-2 hours) - Optional

---

### Task 2.7: Create Phase 2 Documentation
**Objective:** Comprehensive documentation of R2 concepts, PHP integration, and code examples

**Subtasks:**
- [ ] Consolidate all R2 concept notes into `docs/phase2-r2-concepts.md`
- [ ] Create `docs/phase2-r2-php-examples.md` with all code examples
- [ ] Document best practices for R2 integration
- [ ] Include troubleshooting section for common R2/PHP issues
- [ ] Create decision matrix for when to use R2 vs. alternatives

**Acceptance Criteria:**
- Two documentation files created with clear structure
- Concepts documentation covers:
  - What R2 is (S3-compatible object storage)
  - Why use R2 (no egress fees, Cloudflare network, cost optimization)
  - R2 vs. S3, R2 vs. local storage comparisons
  - Pricing and free tier limits
- PHP examples documentation includes:
  - SDK setup and configuration
  - Upload, download, list operations with full code
  - Signed URL generation with examples
  - Error handling patterns
- Best practices section includes:
  - Credential management (never commit `.env`)
  - Error handling and retries
  - File validation before upload
  - Logging and monitoring considerations
- Troubleshooting covers common issues encountered during Phase 2

**Validation Steps:**
1. Read documentation as if implementing R2 for first time
2. Verify all code examples are complete and runnable
3. Check that troubleshooting addresses actual issues encountered
4. Confirm decision matrix is practical and actionable

**Known Challenges:**
- **Challenge:** Code examples may not be production-ready
  - **Mitigation:** Clearly label as POC/examples, document what's needed for production
- **Challenge:** Documentation becomes outdated as R2 evolves
  - **Mitigation:** Include documentation date and link to official R2 docs

**Estimated Effort:** Medium (2-3 hours)

---

### Phase 2: Go/No-Go Decision Point

**Required Outcomes:**
- [ ] R2 bucket created and accessible
- [ ] PHP scripts successfully upload, download, list files on R2
- [ ] Signed URLs generated and tested
- [ ] Documentation explaining R2 concepts and integration
- [ ] All validation steps passed

**Decision Criteria:**
- **GO to Phase 3 if:** R2 operations working reliably, PHP integration understood, ready to automate
- **NO-GO if:** R2 reliability concerns, PHP integration too complex, unclear deployment path

**Stakeholder Questions Before Phase 3:**
1. Is R2 the right storage solution for this use case (vs. S3, local storage, etc.)?
2. Are there concerns about R2 pricing or free tier limits?
3. Is the PHP integration approach appropriate for production?
4. Should we proceed with automated deployment to R2?
5. Do we need CDN integration before proceeding?

**Phase 2 Deliverables:**
- R2 bucket: `lfs-poc-bucket`
- PHP project: `r2-integration-test/` with working scripts
- Documentation: `docs/phase2-r2-concepts.md` and `docs/phase2-r2-php-examples.md`
- Test files uploaded to R2
- Validated R2 operations (upload, download, signed URLs)

---

## Phase 3: LFS → R2 Automated Deployment

**Phase Goal:** Automated deployment system that syncs LFS files from GitHub to R2 via webhook

**Phase Duration Estimate:** Large (5-8 focused work sessions)

**Prerequisites:**
- Phase 1 and Phase 2 completed successfully
- Access to test.elanregistry.org server (SSH, file system, web server)
- Server requirements:
  - Git with LFS installed
  - PHP 8.0+ with Composer
  - Web server (Apache/Nginx) to receive webhooks
  - HTTPS endpoint for webhook security
  - Sufficient disk space for temporary LFS file storage

### Task 3.1: Server Environment Preparation
**Objective:** Ensure test server has all required tools and access for automated deployment

**Subtasks:**
- [ ] SSH into test.elanregistry.org
- [ ] Verify Git installed: `git --version`
- [ ] Verify Git LFS installed: `git lfs version`
- [ ] If not installed: Install Git LFS on server
- [ ] Verify PHP version: `php -v` (must be 8.0+)
- [ ] Verify Composer installed: `composer --version`
- [ ] Create deployment directory: `/var/www/lfs-test/`
- [ ] Set appropriate permissions for web server user
- [ ] Test GitHub SSH access from server: `ssh -T git@github.com`
- [ ] If needed: Generate SSH key and add to GitHub account

**Acceptance Criteria:**
- Git LFS installed and working on server: `git lfs version` returns valid version
- PHP 8.0+ available: `php -v` shows 8.0 or higher
- Composer installed: `composer --version` returns valid version
- Deployment directory created: `/var/www/lfs-test/` exists with proper permissions
- Server can authenticate to GitHub via SSH (no password prompt)
- Test clone successful: `git clone git@github.com:[username]/git-lfs-poc.git /tmp/test-clone`

**Validation Steps:**
1. SSH to server: `ssh user@test.elanregistry.org`
2. Run all verification commands listed above
3. Attempt git clone with LFS files - should complete without password prompt
4. Check cloned files - LFS files should download automatically
5. Clean up test clone: `rm -rf /tmp/test-clone`

**Known Challenges:**
- **Challenge:** Git LFS not available in default package repositories
  - **Mitigation:** Install from official packagecloud.io repository or download binary
- **Challenge:** Server user doesn't have permission to install software
  - **Mitigation:** Work with system administrator or use `sudo` if available
- **Challenge:** GitHub SSH authentication fails from server
  - **Mitigation:** Generate SSH key on server, add to GitHub account deploy keys or user keys
- **Challenge:** LFS files don't download automatically on server
  - **Mitigation:** Verify `git lfs install` run on server, check `.gitconfig` for LFS filter

**Estimated Effort:** Medium (1-2 hours depending on server access)

---

### Task 3.2: Create Deployment Script
**Objective:** Shell script that pulls latest code from GitHub and syncs LFS files to R2

**Subtasks:**
- [ ] Create `deploy.sh` script in deployment directory
- [ ] Implement Git pull logic:
  - Change to repository directory
  - Pull latest changes from GitHub
  - Update LFS files: `git lfs pull`
- [ ] Implement R2 sync logic:
  - Find all LFS-tracked files in repository
  - For each file, upload to R2 (using PHP script or AWS CLI)
  - Log successful uploads
- [ ] Add error handling and logging
- [ ] Test script manually before webhook integration
- [ ] Document script usage and configuration

**Acceptance Criteria:**
- `deploy.sh` script exists and is executable (`chmod +x deploy.sh`)
- Script pulls latest code from GitHub successfully
- Script updates LFS files using `git lfs pull`
- Script uploads LFS files to R2 bucket
- Script logs all actions to `/var/www/lfs-test/logs/deploy.log`
- Script exits with appropriate status codes (0 = success, non-zero = error)
- Manual execution: `./deploy.sh` completes without errors
- R2 bucket contains all LFS files after script execution

**Validation Steps:**
1. Run script manually: `cd /var/www/lfs-test && ./deploy.sh`
2. Check log file: `cat logs/deploy.log` - should show successful git pull and R2 uploads
3. Check R2 dashboard - should show all PDF files from repository
4. Modify a PDF in local repo, push to GitHub, run script again
5. Verify updated file appears in R2 with new timestamp/checksum

**Known Challenges:**
- **Challenge:** Script fails if repository not already cloned
  - **Mitigation:** Add initial clone logic if repo directory doesn't exist
- **Challenge:** Determining which files are LFS-tracked
  - **Mitigation:** Use `git lfs ls-files` to get list of LFS files
- **Challenge:** R2 credentials not available to script
  - **Mitigation:** Store credentials in `/var/www/lfs-test/.env`, load in script
- **Challenge:** Concurrent executions of script (webhook spam)
  - **Mitigation:** Add lock file mechanism to prevent concurrent runs

**Estimated Effort:** Large (3-4 hours including testing)

**Example Script Structure:**
```bash
#!/bin/bash
set -e  # Exit on error

# Configuration
REPO_DIR="/var/www/lfs-test/repo"
LOG_FILE="/var/www/lfs-test/logs/deploy.log"
LOCK_FILE="/var/www/lfs-test/deploy.lock"

# Lock mechanism
if [ -f "$LOCK_FILE" ]; then
    echo "Deployment already in progress" | tee -a "$LOG_FILE"
    exit 1
fi
touch "$LOCK_FILE"
trap "rm -f $LOCK_FILE" EXIT

# Log start
echo "[$(date)] Deployment started" >> "$LOG_FILE"

# Git pull
cd "$REPO_DIR"
git pull origin main >> "$LOG_FILE" 2>&1
git lfs pull >> "$LOG_FILE" 2>&1

# Sync LFS files to R2
git lfs ls-files | while read line; do
    file=$(echo "$line" | awk '{print $3}')
    echo "[$(date)] Uploading $file to R2" >> "$LOG_FILE"
    php /var/www/lfs-test/upload-to-r2.php "$REPO_DIR/$file" >> "$LOG_FILE" 2>&1
done

echo "[$(date)] Deployment completed" >> "$LOG_FILE"
```

---

### Task 3.3: Create R2 Upload PHP Script (Server Version)
**Objective:** PHP script on server to upload files to R2, called by deployment script

**Subtasks:**
- [ ] Copy R2 integration code from Phase 2 to server
- [ ] Create `upload-to-r2.php` CLI script
- [ ] Accept file path as command-line argument
- [ ] Upload file to R2 with appropriate naming/organization
- [ ] Return exit code 0 on success, non-zero on failure
- [ ] Add logging to track uploads
- [ ] Configure R2 credentials from server `.env` file

**Acceptance Criteria:**
- `upload-to-r2.php` script exists on server
- Script can be called from command line: `php upload-to-r2.php /path/to/file.pdf`
- Script uploads file to R2 bucket successfully
- Script maintains file name or uses configurable naming scheme
- Script logs upload status (success/failure) with timestamp
- Script returns exit code 0 on success, 1 on failure
- R2 credentials loaded from `/var/www/lfs-test/.env`

**Validation Steps:**
1. Create test file: `echo "test" > /tmp/test.txt`
2. Run upload script: `php /var/www/lfs-test/upload-to-r2.php /tmp/test.txt`
3. Check exit code: `echo $?` - should be 0
4. Check R2 dashboard - should show `test.txt` or organized path
5. Test failure case: `php upload-to-r2.php /nonexistent/file.pdf` - should return non-zero exit code

**Known Challenges:**
- **Challenge:** PHP memory limit exceeded for large files
  - **Mitigation:** Use streaming uploads, not loading entire file into memory
- **Challenge:** AWS SDK not installed on server
  - **Mitigation:** Run `composer install` in deployment directory
- **Challenge:** R2 credentials not accessible from CLI script
  - **Mitigation:** Load `.env` file explicitly in script, ensure proper file permissions

**Estimated Effort:** Medium (1-2 hours)

---

### Task 3.4: Configure GitHub Webhook
**Objective:** Set up GitHub webhook to notify server on each push

**Subtasks:**
- [ ] Create webhook receiver endpoint on server: `webhook.php`
- [ ] Implement webhook receiver:
  - Verify webhook signature (GitHub secret)
  - Parse webhook payload
  - Trigger deployment script
  - Return 200 OK response
- [ ] Make webhook endpoint publicly accessible: `https://test.elanregistry.org/lfs-test/webhook.php`
- [ ] Create webhook secret for security
- [ ] Configure webhook in GitHub repository:
  - URL: `https://test.elanregistry.org/lfs-test/webhook.php`
  - Content type: `application/json`
  - Secret: [generated secret]
  - Events: Just push events
- [ ] Test webhook delivery from GitHub

**Acceptance Criteria:**
- `webhook.php` endpoint exists and is publicly accessible
- Endpoint verifies GitHub webhook signature before processing
- Endpoint triggers deployment script: `./deploy.sh`
- Endpoint responds with 200 OK status
- Webhook configured in GitHub repository settings
- Webhook secret stored securely on server (in `.env`)
- Test push to repository triggers webhook successfully
- GitHub webhook delivery log shows successful delivery (green checkmark)

**Validation Steps:**
1. Access webhook URL in browser: `https://test.elanregistry.org/lfs-test/webhook.php`
   - Should return 405 Method Not Allowed or similar (GET not supported)
2. Go to GitHub repo settings → Webhooks
3. Find configured webhook, click "Recent Deliveries"
4. Find test delivery, check response code (should be 200)
5. Check server deployment log - should show deployment triggered by webhook
6. Make a commit and push to GitHub
7. Within seconds, webhook should trigger, deployment should run
8. Check R2 - any new/updated LFS files should appear

**Known Challenges:**
- **Challenge:** Webhook endpoint not accessible from internet
  - **Mitigation:** Ensure server firewall allows HTTPS traffic, web server configured correctly
- **Challenge:** Signature verification fails
  - **Mitigation:** Use `hash_hmac('sha256', $payload, $secret)` to verify GitHub signature
- **Challenge:** Deployment script hangs, webhook times out
  - **Mitigation:** Run deployment script in background: `./deploy.sh > /dev/null 2>&1 &`
- **Challenge:** Multiple rapid pushes trigger concurrent deployments
  - **Mitigation:** Lock file mechanism in deployment script (already addressed in Task 3.2)

**Estimated Effort:** Medium (2-3 hours including testing)

**Example Webhook Receiver Structure:**
```php
<?php
// webhook.php

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Load webhook secret
$secret = getenv('GITHUB_WEBHOOK_SECRET') ?: file_get_contents(__DIR__ . '/.webhook-secret');

// Verify signature
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');
$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    exit('Invalid signature');
}

// Parse payload
$data = json_decode($payload, true);

// Log webhook receipt
file_put_contents(__DIR__ . '/logs/webhook.log',
    "[" . date('Y-m-d H:i:s') . "] Webhook received from {$data['pusher']['name']}\n",
    FILE_APPEND
);

// Trigger deployment in background
exec(__DIR__ . '/deploy.sh > /dev/null 2>&1 &');

// Respond immediately
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Deployment triggered']);
```

---

### Task 3.5: End-to-End Integration Testing
**Objective:** Test complete automated deployment workflow from git push to R2 sync

**Subtasks:**
- [ ] Add new test PDF file to local repository
- [ ] Commit and push to GitHub
- [ ] Monitor webhook delivery in GitHub
- [ ] Monitor deployment log on server
- [ ] Verify new file appears in R2 bucket
- [ ] Test update scenario: modify existing PDF, push, verify update in R2
- [ ] Test deletion scenario (optional): remove PDF from repo, push, verify handling
- [ ] Document observed timing (push → webhook → deployment → R2 sync)

**Acceptance Criteria:**
- New file added locally, committed, and pushed to GitHub
- GitHub webhook triggers within 5 seconds of push
- Deployment script executes successfully (check log)
- New file appears in R2 bucket within 2 minutes of push
- File in R2 matches file in repository (checksum verification)
- Update scenario: Modified file reflected in R2 within 2 minutes
- Complete workflow documented with timing metrics

**Validation Steps:**
1. Local: Add `test-file-3.pdf` to repository
2. Local: `git add test-file-3.pdf && git commit -m "Add test file 3" && git push`
3. GitHub: Check webhook deliveries - should show new delivery within seconds
4. Server: `tail -f /var/www/lfs-test/logs/deploy.log` - should show deployment in progress
5. Server: Wait for deployment to complete (watch log)
6. R2: Check dashboard - should show `test-file-3.pdf`
7. Local: Modify `test-file-1.pdf`, commit, push
8. R2: Verify `test-file-1.pdf` updated (check timestamp or re-download and compare)

**Known Challenges:**
- **Challenge:** Webhook not triggering consistently
  - **Mitigation:** Check GitHub webhook delivery log for errors, verify endpoint accessible
- **Challenge:** Deployment takes too long (>5 minutes)
  - **Mitigation:** Investigate bottleneck (git pull? LFS download? R2 upload?)
- **Challenge:** Files not updating in R2 (old version remains)
  - **Mitigation:** Ensure upload script overwrites existing files, not appending/versioning
- **Challenge:** Race condition with rapid pushes
  - **Mitigation:** Lock file mechanism should prevent, test with multiple rapid pushes

**Estimated Effort:** Medium (2-3 hours including multiple test scenarios)

---

### Task 3.6: Error Handling & Monitoring
**Objective:** Add robust error handling, alerting, and monitoring to deployment system

**Subtasks:**
- [ ] Enhance deployment script error handling:
  - Catch git pull failures
  - Catch LFS pull failures
  - Catch R2 upload failures
  - Continue processing other files if one fails
- [ ] Implement logging strategy:
  - Separate logs for webhook, deployment, uploads
  - Log rotation (prevent log files growing unbounded)
  - Include timestamps, status codes, error messages
- [ ] Add basic monitoring:
  - Track deployment success/failure rate
  - Track last successful deployment timestamp
  - Optional: Email or Slack notification on failure
- [ ] Create troubleshooting guide for common failures
- [ ] Document log locations and how to interpret them

**Acceptance Criteria:**
- Deployment script handles errors gracefully (doesn't crash on first failure)
- Failed file upload doesn't prevent other files from uploading
- All operations logged with timestamps and status
- Log files include enough detail to diagnose issues
- Log rotation configured (e.g., daily rotation, keep 7 days)
- Monitoring dashboard or status file shows last deployment status
- Troubleshooting guide covers common scenarios:
  - Webhook not triggering
  - Git authentication failure
  - LFS download failure
  - R2 upload failure
  - Permissions issues

**Validation Steps:**
1. Test failure scenario: Temporarily break R2 credentials
2. Push to GitHub, trigger deployment
3. Check logs - should show clear error message about R2 authentication
4. Fix credentials, push again
5. Check logs - should show successful recovery
6. Review troubleshooting guide - should cover this scenario

**Known Challenges:**
- **Challenge:** Too much logging (disk space issues)
  - **Mitigation:** Implement log rotation, configurable log levels
- **Challenge:** No visibility into deployment status without SSH access
  - **Mitigation:** Create simple status page or file accessible via web
- **Challenge:** Alert fatigue if monitoring too sensitive
  - **Mitigation:** Only alert on repeated failures, not single transient errors

**Estimated Effort:** Medium (2-3 hours)

---

### Task 3.7: Create Phase 3 Documentation
**Objective:** Complete documentation of automated deployment system architecture and setup

**Subtasks:**
- [ ] Create `docs/phase3-automated-deployment.md`
- [ ] Document system architecture with diagram:
  - GitHub repository with LFS files
  - GitHub webhook
  - Test server webhook receiver
  - Deployment script
  - R2 bucket
- [ ] Document setup instructions (for replicating to other servers)
- [ ] Document operational procedures:
  - How to trigger manual deployment
  - How to check deployment status
  - How to troubleshoot failures
- [ ] Document security considerations:
  - Webhook signature verification
  - Credential storage
  - Server permissions
- [ ] Include code examples for all key components
- [ ] Create runbook for common operational tasks

**Acceptance Criteria:**
- Documentation file created with comprehensive coverage
- Architecture diagram included (can be ASCII art or image)
- Setup instructions are step-by-step and complete
- Operational procedures cover:
  - Manual deployment: `./deploy.sh`
  - Check logs: `tail -f logs/deploy.log`
  - Test webhook: GitHub settings → Webhooks → Redeliver
- Security section covers:
  - Webhook secret generation and storage
  - `.env` file permissions (600 or 640)
  - R2 credentials access control
  - HTTPS requirement for webhook endpoint
- Runbook includes:
  - Deploy new LFS file
  - Update existing LFS file
  - Troubleshoot failed deployment
  - Verify R2 sync
  - Rotate webhook secret

**Validation Steps:**
1. Have someone unfamiliar with system read documentation
2. Ask them to identify what they'd need to replicate setup
3. Verify all code examples are complete and correct
4. Check that troubleshooting covers issues encountered during Phase 3

**Known Challenges:**
- **Challenge:** Documentation becomes outdated as system evolves
  - **Mitigation:** Include last updated date, mark POC status clearly
- **Challenge:** Too much detail makes documentation hard to navigate
  - **Mitigation:** Use clear sections, table of contents, focus on essential information

**Estimated Effort:** Large (3-4 hours)

---

### Task 3.8: Security Review & Hardening
**Objective:** Review deployment system for security vulnerabilities and harden as needed

**Subtasks:**
- [ ] Review webhook signature verification implementation
- [ ] Review R2 credential storage and access
- [ ] Review file permissions on deployment directory
- [ ] Review deployment script for command injection vulnerabilities
- [ ] Consider rate limiting webhook endpoint
- [ ] Document security assumptions and limitations
- [ ] Create security checklist for production deployment

**Acceptance Criteria:**
- Webhook signature verified before processing (already in Task 3.4)
- R2 credentials stored in `.env` with restrictive permissions (600)
- `.env` not accessible via web browser (outside web root or `.htaccess` deny)
- Deployment script sanitizes inputs (file names, etc.)
- Webhook endpoint has basic rate limiting (e.g., max 10 requests/minute)
- Security documentation includes:
  - Threat model (what attacks are we defending against?)
  - Mitigations implemented
  - Known limitations (e.g., no deploy approval workflow)
  - Recommendations for production (e.g., separate deploy key, audit logging)

**Validation Steps:**
1. Attempt to access `.env` via web browser - should return 403 Forbidden
2. Send webhook request without signature - should return 403 Forbidden
3. Send webhook request with invalid signature - should return 403 Forbidden
4. Check `.env` permissions: `ls -l .env` - should show `-rw-------` or `-rw-r-----`
5. Review deployment script for user input - ensure all inputs validated/sanitized
6. Review security documentation - covers realistic threats and mitigations

**Known Challenges:**
- **Challenge:** Webhook endpoint could be DDoS target
  - **Mitigation:** Rate limiting, Cloudflare DDoS protection (if available)
- **Challenge:** Deployment script runs with elevated privileges
  - **Mitigation:** Run as dedicated user with minimal permissions, not root
- **Challenge:** No deploy approval or rollback mechanism
  - **Mitigation:** Document as POC limitation, recommend for production

**Estimated Effort:** Medium (2-3 hours)

---

### Phase 3: Go/No-Go Decision Point

**Required Outcomes:**
- [ ] Server configured with Git LFS and PHP/Composer
- [ ] Deployment script working (manual execution successful)
- [ ] GitHub webhook configured and triggering deployments
- [ ] End-to-end test successful (push → webhook → deploy → R2 sync)
- [ ] Error handling and monitoring in place
- [ ] Documentation complete
- [ ] Security review completed

**Decision Criteria:**
- **GO to Production Planning if:** All tasks completed, workflow reliable, stakeholders satisfied
- **NO-GO if:** Reliability concerns, security issues unresolved, workflow too complex

**Stakeholder Questions for Production Decision:**
1. Does the automated deployment workflow meet requirements?
2. Are there concerns about system reliability or error handling?
3. Are security measures sufficient for production use?
4. What additional requirements exist for production deployment?
5. Should we proceed with rollout beyond POC, or is POC sufficient?

**Phase 3 Deliverables:**
- Deployment script: `/var/www/lfs-test/deploy.sh`
- Webhook receiver: `/var/www/lfs-test/webhook.php`
- R2 upload script: `/var/www/lfs-test/upload-to-r2.php`
- Documentation: `docs/phase3-automated-deployment.md`
- Working automated deployment system on test.elanregistry.org
- Test files synced from GitHub LFS to Cloudflare R2

---

## Post-POC: Production Considerations

**Not in Scope for POC, but Document for Future:**

### Production Hardening
- Separate deployment user with minimal permissions
- Deploy key (read-only) instead of full GitHub SSH access
- Audit logging for all deployments
- Rollback mechanism for failed deployments
- Health checks and uptime monitoring
- Automated testing before deployment
- Blue-green or canary deployment strategy

### Scalability Considerations
- Multiple environments (dev, staging, production)
- Large file handling (files >100MB)
- High-frequency deployments (multiple per hour)
- Multiple repositories/buckets
- CDN integration for file delivery

### Operational Readiness
- On-call runbook
- Incident response procedures
- Backup and disaster recovery
- Cost monitoring and alerts
- Performance monitoring

### Integration with Existing Systems
- CI/CD pipeline integration
- Application code to serve files from R2
- User authentication/authorization for file access
- Analytics and usage tracking

---

## Success Metrics

### Phase 1 Success Metrics
- Time to clone repository with LFS files: <30 seconds
- LFS file download success rate: 100%
- Documentation clarity: Team member can replicate without questions

### Phase 2 Success Metrics
- R2 upload success rate: 100%
- R2 upload time for 100KB file: <2 seconds
- Signed URL generation time: <1 second
- Documentation clarity: Team member can implement R2 integration without questions

### Phase 3 Success Metrics
- Webhook trigger time after push: <10 seconds
- End-to-end deployment time (push to R2 sync): <5 minutes
- Deployment success rate: >95%
- Failed deployment recovery time: <15 minutes (manual intervention)
- Documentation clarity: Team member can troubleshoot deployment without questions

---

## Risk Register

### High-Risk Items (Address Immediately if Encountered)

**Risk 1: GitHub LFS Quota Exceeded**
- **Impact:** Cannot push LFS files, workflow blocked
- **Likelihood:** Low (using small test files)
- **Mitigation:** Monitor LFS usage, stay well under 1GB free tier
- **Contingency:** Purchase additional LFS bandwidth or reduce file sizes

**Risk 2: R2 Credentials Exposed**
- **Impact:** Security breach, potential data loss or costs
- **Likelihood:** Medium (credential management error)
- **Mitigation:** Never commit `.env`, use restrictive permissions, review before git push
- **Contingency:** Rotate credentials immediately, audit R2 access logs

**Risk 3: Deployment Script Infinite Loop or Fork Bomb**
- **Impact:** Server resource exhaustion, downtime
- **Likelihood:** Low (careful script development)
- **Mitigation:** Lock file mechanism, test thoroughly before webhook integration
- **Contingency:** Kill process, fix script, restart services

### Medium-Risk Items (Monitor and Mitigate)

**Risk 4: Webhook Secret Compromised**
- **Impact:** Unauthorized deployments, potential malicious code execution
- **Likelihood:** Low (secret stored securely)
- **Mitigation:** Use strong random secret, verify signature, restrict endpoint access
- **Contingency:** Rotate secret, audit deployment logs for suspicious activity

**Risk 5: Network Connectivity Issues (Server to GitHub/R2)**
- **Impact:** Deployment failures, file sync failures
- **Likelihood:** Medium (external dependencies)
- **Mitigation:** Error handling, retries, monitoring
- **Contingency:** Manual deployment, investigate network issues, failover if persistent

**Risk 6: Large File Handling Issues**
- **Impact:** Timeouts, memory errors, failed uploads
- **Likelihood:** Low for POC (small files), High for production
- **Mitigation:** Test with appropriately sized files, use streaming uploads
- **Contingency:** Implement chunked/multipart uploads for large files

### Low-Risk Items (Document Only)

**Risk 7: Documentation Becomes Outdated**
- **Impact:** Team confusion, setup difficulties
- **Likelihood:** High (over time)
- **Mitigation:** Include documentation date, link to official sources
- **Contingency:** Regular documentation reviews and updates

**Risk 8: Test Server Downtime**
- **Impact:** Cannot test automated deployment
- **Likelihood:** Low
- **Mitigation:** Test locally before server deployment
- **Contingency:** Wait for server restoration, or provision temporary server

---

## Appendix: Tools & Resources

### Required Tools
- **Git:** Version control system (2.x+)
- **Git LFS:** Large file storage extension (3.x+)
- **PHP:** Programming language (8.0+)
- **Composer:** PHP dependency manager
- **AWS SDK for PHP:** S3-compatible client for R2

### Installation Commands
```bash
# macOS
brew install git-lfs
git lfs install

# Ubuntu/Debian
curl -s https://packagecloud.io/install/repositories/github/git-lfs/script.deb.sh | sudo bash
sudo apt-get install git-lfs
git lfs install

# Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

# AWS SDK for PHP
composer require aws/aws-sdk-php
```

### Official Documentation Links
- Git LFS: https://git-lfs.github.com/
- Cloudflare R2: https://developers.cloudflare.com/r2/
- AWS SDK for PHP: https://docs.aws.amazon.com/sdk-for-php/
- GitHub Webhooks: https://docs.github.com/en/webhooks

### Useful Commands Reference
```bash
# Git LFS
git lfs install                    # Initialize LFS
git lfs track "*.pdf"              # Track PDFs with LFS
git lfs ls-files                   # List LFS-tracked files
git lfs status                     # Show LFS file status
git lfs pull                       # Download LFS files
git lfs migrate import --include="*.pdf"  # Migrate existing files to LFS

# Debugging
git lfs logs last                  # Show last LFS operation log
git lfs env                        # Show LFS environment
git config --list | grep lfs       # Show LFS config

# R2 with AWS CLI (optional, for testing)
aws s3 ls s3://bucket-name --endpoint-url https://[account-id].r2.cloudflarestorage.com
aws s3 cp file.pdf s3://bucket-name/ --endpoint-url https://[account-id].r2.cloudflarestorage.com
```

---

## GitHub Issues Template

For tracking this project in GitHub Issues, create issues with the following structure:

### Issue Template: Phase 1 Tasks
```
Title: [Phase 1.X] Task Name

Labels: phase-1, documentation|setup|testing

Description:
**Objective:** [What this task achieves]

**Acceptance Criteria:**
- [ ] Criterion 1
- [ ] Criterion 2

**Validation Steps:**
1. Step 1
2. Step 2

**Estimated Effort:** Trivial|Small|Medium|Large

**Dependencies:** None | Blocked by #X

**Documentation:** Link to PROJECT_PLAN.md task section
```

### Suggested Issue Breakdown
- **Issue #1:** Phase 1.1 - Git LFS Installation & Configuration
- **Issue #2:** Phase 1.2 - Create GitHub Repository with LFS Tracking
- **Issue #3:** Phase 1.3 - Add Test PDF Files with LFS
- **Issue #4:** Phase 1.4 - Test Full LFS Workflow
- **Issue #5:** Phase 1.5 - Create Phase 1 Documentation
- **Issue #6:** Phase 1 Go/No-Go Decision
- **Issue #7:** Phase 2.1 - R2 Account Setup & Bucket Creation
- **Issue #8:** Phase 2.2 - Understand R2 Concepts & Pricing
- ... [continue for all tasks]

---

## Project Tracking Checklist

Use this high-level checklist to track overall progress:

### Phase 1: Git LFS Learning & Showcase
- [ ] Git LFS installed and configured
- [ ] GitHub repository created with LFS tracking
- [ ] Test PDFs committed via LFS
- [ ] Clone test successful
- [ ] Phase 1 documentation complete
- [ ] Phase 1 Go/No-Go decision: **GO / NO-GO**

### Phase 2: Cloudflare R2 Understanding
- [ ] R2 account and bucket set up
- [ ] R2 concepts documented
- [ ] PHP/AWS SDK integration working
- [ ] File upload/download tested
- [ ] Signed URLs tested
- [ ] Phase 2 documentation complete
- [ ] Phase 2 Go/No-Go decision: **GO / NO-GO**

### Phase 3: LFS → R2 Automated Deployment
- [ ] Server environment prepared
- [ ] Deployment script created and tested
- [ ] R2 upload script working on server
- [ ] GitHub webhook configured
- [ ] End-to-end integration test successful
- [ ] Error handling and monitoring in place
- [ ] Phase 3 documentation complete
- [ ] Security review complete
- [ ] Phase 3 Go/No-Go decision: **GO / NO-GO**

### Post-POC
- [ ] Production considerations documented
- [ ] Stakeholder demo completed
- [ ] Production deployment decision: **GO / NO-GO**

---

**Document Version:** 1.0
**Last Updated:** 2026-02-02
**Status:** Draft - Awaiting Phase 1 Kickoff
**Next Review:** After each phase completion
