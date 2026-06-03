# 🎰 Spin to Win – GCash Prize Game

A modern PHP spin-the-wheel game with MySQL database, leaderboard, and GCash payout system.

---

## 📁 File Structure

```
spinwheel/
├── index.php           ← Main game page
├── api.php             ← Game API (spin, register, payout)
├── database.sql        ← Database setup (run this first!)
├── includes/
│   └── config.php      ← Database & game settings
└── admin/
    └── index.php       ← Admin panel (manage payouts)
```

---

## 🚀 Setup Instructions

### Step 1 – Import the Database
1. Open **phpMyAdmin** (or MySQL Workbench)
2. Click **Import** → Choose `database.sql`
3. Click **Go** / **Execute**

### Step 2 – Configure the App
Open `includes/config.php` and edit:
```php
define('DB_HOST', 'localhost');       // Your MySQL host
define('DB_USER', 'root');            // Your MySQL username
define('DB_PASS', '');                // Your MySQL password
define('DB_NAME', 'spinwheel_db');    // Leave as is

define('MAX_SPINS_PER_DAY', 3);       // Spins per player per day
define('MIN_PAYOUT_AMOUNT', 50);      // Minimum ₱ to cash out
define('GCASH_ADMIN_NUMBER', '09XX-XXX-XXXX');  // ← PUT YOUR GCASH NUMBER HERE
```

### Step 3 – Upload to Server
Upload the entire `spinwheel/` folder to your web server:
- **XAMPP**: Put in `C:/xampp/htdocs/spinwheel/`
- **cPanel hosting**: Upload to `public_html/spinwheel/`

### Step 4 – Change Admin Password
The default admin login is:
- Username: `admin`
- Password: `password`

To change it, run this PHP snippet once to generate a new hash:
```php
<?php echo password_hash('your_new_password', PASSWORD_BCRYPT); ?>
```
Then update it in the database:
```sql
UPDATE admin_users SET password = 'YOUR_HASH_HERE' WHERE username = 'admin';
```

---

## 🎮 How to Play

1. Go to `http://yoursite.com/spinwheel/`
2. Enter your name and GCash number
3. Click **SPIN!** (up to 3 spins per day)
4. Win ₱5 to ₱200 per spin!
5. Reach ₱50+ to request a GCash payout

---

## 💸 Payout Flow

1. Player accumulates score from winning spins
2. Player clicks **"Request GCash Payout"** — score is deducted and request is saved
3. **Admin** logs into `/spinwheel/admin/` → Payout Requests tab
4. Admin sees player name, GCash number, and amount
5. Admin manually sends GCash, then clicks **Approve** and adds a note
6. If rejected (e.g. fake number), score is refunded to player

---

## 🎡 Prize Segments

| Prize   | Odds  |
|---------|-------|
| ₱5      | 20%   |
| ₱10     | 18%   |
| ₱20     | 15%   |
| ₱50     | 10%   |
| ₱100    | 5%    |
| ₱200    | 2%    |
| Try Again | 30% |

You can adjust the `$prizes` array in `api.php` to change prizes and odds.

---

## ⚠️ Notes

- This is a **demo/fun game** – not a real gambling platform
- Real GCash payouts are sent **manually** by admin
- Always comply with Philippine laws regarding prize games
- Secure your admin panel with a strong password before going live
