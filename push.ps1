git add includes/ admin/includes/
git commit -m "refactor(core): extract reusable header and footer templates for DRY architecture"
git add config/db.php
git commit -m "feat(db): implement global exception handler and secure PDO configuration"
git add config/auth.php login.php register.php
git commit -m "feat(security): implement robust CSRF token generation and validation for auth forms"
git add index.php api/ script.js
git commit -m "refactor(public): upgrade public pages and API to use templates, secure PDO, and CSRF validation"
git add admin/
git commit -m "refactor(admin): overhaul admin dashboard to use templates, strict CSRF validation, and parameterized queries"
git add .gitignore
git commit -m "chore(config): add standard .gitignore for environment and build files"
git add .
git commit -m "chore: commit remaining unversioned files"
git push origin main
