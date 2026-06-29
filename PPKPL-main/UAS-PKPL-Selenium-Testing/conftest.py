import os
import pytest
from datetime import datetime

from utils.driver_factory import create_driver
from utils.config import (
    ADMIN_EMAIL, ADMIN_PASSWORD,
    PETUGAS_EMAIL, PETUGAS_PASSWORD,
)


# ─── CLI Option ───────────────────────────────────────────────────────────────

def pytest_addoption(parser):
    """Tambahkan opsi --headless ke CLI pytest."""
    parser.addoption(
        "--headless",
        action="store_true",
        default=False,
        help="Jalankan Chrome dalam mode headless (tanpa tampilan browser).",
    )


# ─── Hook: tangkap status pass/fail untuk screenshot ─────────────────────────

@pytest.hookimpl(tryfirst=True, hookwrapper=True)
def pytest_runtest_makereport(item, call):
    """
    Hook pytest untuk menyimpan hasil test ke node item.
    Digunakan oleh fixture driver untuk cek apakah test gagal.
    """
    outcome = yield
    rep = outcome.get_result()
    setattr(item, f"rep_{rep.when}", rep)


# ─── Fixtures ─────────────────────────────────────────────────────────────────

@pytest.fixture
def driver(request):
    """
    Fixture dasar: Chrome baru, session kosong, belum login.

    Digunakan oleh:
      - test_guest_access.py (langsung, tanpa login)
      - Sebagai base untuk fixture logged_in_* di bawah

    Teardown:
      - Simpan screenshot ke screenshots/ jika test gagal
      - Tutup browser
    """
    headless = request.config.getoption("--headless")
    drv = create_driver(headless=headless)

    yield drv

    # Teardown: screenshot jika test gagal
    if hasattr(request.node, "rep_call") and request.node.rep_call.failed:
        _save_failure_screenshot(drv, request.node.name)

    drv.quit()


@pytest.fixture
def logged_in_admin(driver):
    """
    Fixture: Chrome yang sudah login sebagai admin@lab.com.

    Digunakan oleh:
      - test_logout.py
      - test_admin_access.py

    Proses login dilakukan via LoginPage agar konsisten dengan POM.
    """
    from pages.login_page import LoginPage
    login_page = LoginPage(driver)
    login_page.open()
    login_page.login(ADMIN_EMAIL, ADMIN_PASSWORD)
    yield driver


@pytest.fixture
def logged_in_petugas(driver):
    """
    Fixture: Chrome yang sudah login sebagai petugas@lab.com.

    Digunakan oleh:
      - test_petugas_access.py
    """
    from pages.login_page import LoginPage
    login_page = LoginPage(driver)
    login_page.open()
    login_page.login(PETUGAS_EMAIL, PETUGAS_PASSWORD)
    yield driver


# ─── Helper Internal ──────────────────────────────────────────────────────────

def _save_failure_screenshot(driver, test_name: str) -> None:
    """
    Simpan screenshot ke folder screenshots/ dengan timestamp.

    Args:
        driver   : WebDriver yang sedang aktif.
        test_name: Nama function test yang gagal.
    """
    os.makedirs("screenshots", exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    # Bersihkan karakter yang tidak aman untuk nama file
    safe_name = "".join(c if c.isalnum() or c in "-_" else "_" for c in test_name)
    filepath  = f"screenshots/FAIL_{safe_name}_{timestamp}.png"
    try:
        driver.save_screenshot(filepath)
        print(f"\n[Screenshot] Disimpan: {filepath}")
    except Exception as e:
        print(f"\n[Screenshot] Gagal menyimpan: {e}")