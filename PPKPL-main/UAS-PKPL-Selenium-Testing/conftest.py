import os
import logging
import pytest
from datetime import datetime

from utils.driver_factory import create_driver
from utils.config import (
    ADMIN_EMAIL, ADMIN_PASSWORD,
    PETUGAS_EMAIL, PETUGAS_PASSWORD,
)

# ─── Logger ─────────────────────────────────────────────────────────────────
# Memanfaatkan log_cli=true & log_cli_level=INFO yang sudah ada di pytest.ini.
# Tidak perlu logging.basicConfig() — pytest sudah memasang handler-nya
# sendiri di root logger, sehingga semua logger.info() di project ini
# otomatis tampil di terminal DAN masuk ke section "Captured log call"
# pada laporan HTML pytest-html.
logger = logging.getLogger(__name__)


# ─── CLI Option ───────────────────────────────────────────────────────────────

def pytest_addoption(parser):
    """Tambahkan opsi --headless ke CLI pytest."""
    parser.addoption(
        "--headless",
        action="store_true",
        default=False,
        help="Jalankan Chrome dalam mode headless (tanpa tampilan browser).",
    )


# ─── Hook: penanda awal test ──────────────────────────────────────────────────

def pytest_runtest_logstart(nodeid, location):
    """
    Hook bawaan pytest, dipanggil tepat sebelum sebuah test dijalankan
    (sebelum fixture setup). Dipakai untuk menandai awal setiap test
    pada log, agar output CLI dan laporan HTML mudah ditelusuri saat
    presentasi UAS.
    """
    logger.info("===== START TEST: %s =====", nodeid)


# ─── Hook: tangkap status pass/fail untuk screenshot + log hasil ────────────

@pytest.hookimpl(tryfirst=True, hookwrapper=True)
def pytest_runtest_makereport(item, call):
    """
    Hook pytest untuk menyimpan hasil test ke node item.
    Digunakan oleh fixture driver untuk cek apakah test PASS atau FAIL,
    dan untuk mencatat status akhir tersebut ke log.
    """
    outcome = yield
    rep = outcome.get_result()
    setattr(item, f"rep_{rep.when}", rep)

    # Hanya catat status pada fase "call" (eksekusi test, bukan fixture setup)
    if rep.when == "call":
        if rep.passed:
            logger.info("TEST PASSED: %s", item.nodeid)
        elif rep.failed:
            logger.error("TEST FAILED: %s", item.nodeid)


# ─── Fixtures ─────────────────────────────────────────────────────────────────

@pytest.fixture
def driver(request):
    """
    Fixture dasar: Chrome baru, session kosong, belum login.

    Digunakan oleh:
      - test_guest_access.py (langsung, tanpa login)
      - Sebagai base untuk fixture logged_in_* di bawah

    Teardown:
      - Simpan screenshot ke screenshots/ untuk SETIAP test, baik
        PASS (prefix "PASS_") maupun FAIL (prefix "FAIL_")
      - Tutup browser
    """
    headless = request.config.getoption("--headless")
    drv = create_driver(headless=headless)
    logger.info("Chrome driver dibuat (headless=%s)", headless)

    yield drv

    # Teardown: screenshot sesuai hasil akhir test (PASS maupun FAIL)
    if hasattr(request.node, "rep_call"):
        if request.node.rep_call.failed:
            _save_screenshot(drv, request.node.name, status="FAIL")
        elif request.node.rep_call.passed:
            _save_screenshot(drv, request.node.name, status="PASS")

    drv.quit()
    logger.info("Chrome driver ditutup")


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

def _save_screenshot(driver, test_name: str, status: str) -> None:
    """
    Simpan screenshot ke folder screenshots/ dengan prefix status dan
    timestamp.

    Args:
        driver   : WebDriver yang sedang aktif.
        test_name: Nama function test (request.node.name).
        status   : "PASS" atau "FAIL" — menjadi prefix nama file,
                   mis. PASS_TC_S01_login_berhasil_..._20260630_120000.png
    """
    os.makedirs("screenshots", exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")

    # Hilangkan prefix "test_" agar nama file lebih ringkas dan
    # langsung menonjolkan ID test case (TC-Sxx)
    clean_name = test_name[5:] if test_name.startswith("test_") else test_name
    # Bersihkan karakter yang tidak aman untuk nama file
    safe_name = "".join(c if c.isalnum() or c in "-_" else "_" for c in clean_name)

    filepath = f"screenshots/{status}_{safe_name}_{timestamp}.png"
    try:
        driver.save_screenshot(filepath)
        logger.info("Screenshot %s disimpan: %s", status, filepath)
    except Exception as e:
        logger.warning("Gagal menyimpan screenshot %s: %s", status, e)