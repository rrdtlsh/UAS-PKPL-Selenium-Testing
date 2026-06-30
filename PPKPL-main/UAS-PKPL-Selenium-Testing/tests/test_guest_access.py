import pytest
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

from pages.login_page import LoginPage
from pages.monitoring_page import MonitoringPage
from utils.config import EXPLICIT_WAIT


class TestGuestAccess:
    """
    Test suite untuk akses guest (belum login) ke halaman terproteksi.

    Menggunakan fixture `driver` dari conftest.py:
    Chrome baru, belum login, session kosong — tidak perlu login sama sekali
    untuk skenario ini.
    """

    # ─────────────────────────────────────────────────────────────────────
    # TC-S03: Guest tidak boleh mengakses /monitoring secara langsung
    # Priority: Critical
    # ─────────────────────────────────────────────────────────────────────

    def test_TC_S03_guest_tidak_bisa_akses_monitoring(self, driver):
        """
        TC-S03: Verifikasi bahwa pengguna yang belum login tidak dapat
        mengakses /monitoring secara langsung melalui URL.

        Langkah:
          1. Buka /monitoring langsung tanpa login terlebih dahulu
          2. Tunggu redirect selesai

        Expected:
          - Sistem mengarahkan pengguna ke halaman /login
          - URL akhir mengandung /login, bukan /monitoring
          - Title browser sesuai halaman login
            ("Login | Sistem Monitoring & Alert")

        Referensi: routes/web.php — /monitoring dibungkus
        Route::middleware('auth'), tanpa override redirectTo di
        bootstrap/app.php, sehingga Laravel redirect ke route('login').
        """
        # Arrange
        monitoring_page = MonitoringPage(driver)
        login_page      = LoginPage(driver)

        # Act — coba akses /monitoring langsung tanpa login
        monitoring_page.open("/monitoring")

        # Act — tunggu redirect ke /login selesai
        # Menggunakan WebDriverWait eksplisit agar tidak flaky
        WebDriverWait(driver, EXPLICIT_WAIT).until(
            EC.url_contains("/login"),
            message=(
                "Guest berhasil mengakses /monitoring tanpa login. "
                "Redirect ke /login tidak terjadi. "
                f"URL saat ini: {driver.current_url}"
            )
        )

        # Assert 1 — URL akhir harus di halaman login
        assert login_page.is_on_login_page(), (
            "Guest yang belum login seharusnya diarahkan ke /login. "
            f"URL saat ini: {login_page.get_current_url()}"
        )

        # Assert 2 — URL tidak boleh mengandung /monitoring
        assert "/monitoring" not in login_page.get_current_url(), (
            "Guest tidak boleh tetap berada di /monitoring. "
            f"URL saat ini: {login_page.get_current_url()}"
        )

        # Assert 3 — title browser harus sesuai halaman login
        # Verifikasi tambahan bahwa redirect benar-benar mendarat di
        # halaman login, bukan halaman lain yang juga mengandung "/login"
        assert login_page.get_page_title() == login_page.EXPECTED_TITLE, (
            "Title halaman tidak sesuai dengan halaman login. "
            f"Diharapkan: '{login_page.EXPECTED_TITLE}' "
            f"Didapat: '{login_page.get_page_title()}'"
        )