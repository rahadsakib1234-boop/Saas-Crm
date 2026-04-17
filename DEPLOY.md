# Fix Deploy Guide — Termux (Mobile)

## Files changed and where they go

| Fixed file | Destination in your repo |
|---|---|
| `LeadAutomationExecutionState.php` | `packages/Webkul/Lead/src/Models/` |
| `AutomationGuard.php` | `packages/Webkul/Lead/src/Services/` |
| `LeadActionExecutor.php` | `packages/Webkul/Lead/src/Services/` |
| `LeadTemperatureClassifier.php` | `packages/Webkul/Lead/src/Services/` |
| `LeadServiceProvider.php` | `packages/Webkul/Lead/src/Providers/` |
| `NotifyUserAction.php` | `packages/Webkul/Lead/src/Services/Actions/` |
| `CreateTaskAction.php` | `packages/Webkul/Lead/src/Services/Actions/` |
| `2026_04_18_000001_add_columns_to_execution_state_table.php` | `packages/Webkul/Lead/src/Database/Migrations/` |

---

## Step-by-step Termux commands

### 1. Install git if not already installed
```bash
pkg install git
```

### 2. Clone your repo (skip if already cloned)
```bash
git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
cd YOUR_REPO_NAME
```

### 3. Make sure you're on the right branch
```bash
git status
git branch
# If you need to switch: git checkout main
```

### 4. Replace each file — paste these one by one

> Replace each file with the fixed content. The easiest way on mobile
> is to use `cat > filename` then paste the content, then press Ctrl+D.
> Or use a text editor like `nano`.

```bash
# Install nano if needed
pkg install nano

# Edit each file (open, select all, paste fixed content, save with Ctrl+O, exit Ctrl+X)
nano packages/Webkul/Lead/src/Models/LeadAutomationExecutionState.php
nano packages/Webkul/Lead/src/Services/AutomationGuard.php
nano packages/Webkul/Lead/src/Services/LeadActionExecutor.php
nano packages/Webkul/Lead/src/Services/LeadTemperatureClassifier.php
nano packages/Webkul/Lead/src/Providers/LeadServiceProvider.php
nano packages/Webkul/Lead/src/Services/Actions/NotifyUserAction.php
nano packages/Webkul/Lead/src/Services/Actions/CreateTaskAction.php
```

### 5. Create the new migration file
```bash
nano packages/Webkul/Lead/src/Database/Migrations/2026_04_18_000001_add_columns_to_execution_state_table.php
# Paste the migration content, save with Ctrl+O, exit Ctrl+X
```

### 6. Stage all changed files
```bash
git add packages/Webkul/Lead/src/Models/LeadAutomationExecutionState.php
git add packages/Webkul/Lead/src/Services/AutomationGuard.php
git add packages/Webkul/Lead/src/Services/LeadActionExecutor.php
git add packages/Webkul/Lead/src/Services/LeadTemperatureClassifier.php
git add packages/Webkul/Lead/src/Providers/LeadServiceProvider.php
git add packages/Webkul/Lead/src/Services/Actions/NotifyUserAction.php
git add packages/Webkul/Lead/src/Services/Actions/CreateTaskAction.php
git add packages/Webkul/Lead/src/Database/Migrations/2026_04_18_000001_add_columns_to_execution_state_table.php
```

### 7. Commit and push
```bash
git commit -m "fix: automation guard methods, service bindings, action executor, activity columns"
git push origin main
```
> If your branch is not `main`, replace `main` with your branch name (e.g. `master` or `dev`).

### 8. On your server — pull and run migrations
If you have SSH access to your deployment server, run:
```bash
git pull origin main
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

---

## Summary of what each fix does

| Bug | Fix |
|---|---|
| `AutomationGuard` called 6 methods that didn't exist on `LeadAutomationExecutionState` | Added all 6 methods to the model + migration for 2 missing DB columns |
| `LeadTemperatureClassifier` called `->check()` and `->record()` but guard had `canExecute()`/`recordExecution()` | Added `check()` and `record()` as thin wrappers in `AutomationGuard` |
| `LeadServiceProvider` passed `AutomationGuard` into `LeadActionExecutor` which expects `LeadNotificationService` | Fixed the binding + registered `LeadTemperatureClassifier` (it was missing entirely) |
| `executeAll()` was called with 3 args but only accepts 2 | Removed the stray `$log` parameter from the call in `LeadTemperatureClassifier` |
| `NotifyUserAction` called `->notify()` but the method is `->send()` | Fixed method name |
| `CreateTaskAction` used `due_date` and `lead_id` columns that don't exist on Activity | Switched to `schedule_from`/`schedule_to` and `$activity->leads()->attach()` via pivot |
