# ─── URL Aplikasi ──────────────────────────────────────────────────────────
BASE_URL = "http://localhost:8000"

# ─── Endpoint ─────────────────────────────────────────────────────────────
LOGIN_URL      = f"{BASE_URL}/login"
MONITORING_URL = f"{BASE_URL}/monitoring"
ADMIN_URL      = f"{BASE_URL}/admin"

# ─── Kredensial (dari DatabaseSeeder.php) ─────────────────────────────────
ADMIN_EMAIL      = "admin@lab.com"
ADMIN_PASSWORD   = "password123"

PETUGAS_EMAIL    = "petugas@lab.com"
PETUGAS_PASSWORD = "password123"

INVALID_EMAIL    = "tidak_ada@lab.com"
INVALID_PASSWORD = "passwordSALAH"

# ─── Timeout ──────────────────────────────────────────────────────────────
IMPLICIT_WAIT = 5    # detik — fallback pencarian elemen
EXPLICIT_WAIT = 10   # detik — WebDriverWait di BasePage