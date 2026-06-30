import pytest
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

from pages.login_page import LoginPage
from pages.monitoring_page import MonitoringPage
from utils.config import EXPLICIT_WAIT


class TestLogout:
    """
    Test suite untuk fitur logout.

    Menggunakan fixture `logged_in_admin` dari conftest.py:
    Chrome yang sudah login sebagai admin@lab.com via LoginPage.
    """

    # ─────────────────────────────────────────────────────────────────────
    # TC-S06: Logout berhasil dan session benar-benar terhapus
    # Priority: Critical
    # ─────────────────────────────────────────────────────────────────────

    def test_TC_S06_logout_berhasil_dan_session_terhapus(self, logged_in_admin):
        """
        TC-S06: Verifikasi bahwa logout mengarahkan pengguna ke /login
        dan session benar-benar dihapus (bukan hanya redirect kosmetik).

        Langkah:
          1. Login sebagai admin@lab.com (sudah dilakukan oleh fixture
             logged_in_admin)
          2. Tunggu halaman /monitoring termuat
          3. Klik tombol Logout
          4. Tunggu redirect ke /login
          5. Coba akses /monitoring langsung lagi via URL

        Expected:
          - Setelah klik Logout, URL mengarah ke /login
          - Title halaman sesuai halaman login
          - Setelah logout, akses ulang /monitoring tetap diarahkan
            ke /login (membuktikan session benar-benar invalidate,
            bukan hanya UI yang berubah)

        Referensi: AuthController::logout() — Auth::logout(),
        $request->session()->invalidate(), regenerateToken(),
        redirect()->route('login').
        """
        # Arrange
        driver          = logged_in_admin
        monitoring_page = MonitoringPage(driver)
        login_page      = LoginPage(driver)

        # Act — pastikan halaman monitoring benar-benar termuat
        # sebelum mengklik logout (fixture tidak menunggu redirect login)
        monitoring_page.wait_until_loaded()

        assert monitoring_page.is_loaded(), (
            "Halaman monitoring belum termuat dengan benar sebelum "
            "logout dilakukan. "
            f"URL saat ini: {monitoring_page.get_current_url()}"
        )

        # Act — klik tombol Logout
        monitoring_page.click_logout()

        # Act — tunggu redirect ke /login selesai
        WebDriverWait(driver, EXPLICIT_WAIT).until(
            EC.url_contains("/login"),
            message=(
                "Redirect ke /login tidak terjadi setelah klik Logout. "
                f"URL saat ini: {driver.current_url}"
            )
        )

        # Assert 1 — berada di halaman login setelah logout
        assert login_page.is_on_login_page(), (
            "Setelah logout, pengguna seharusnya diarahkan ke /login. "
            f"URL saat ini: {login_page.get_current_url()}"
        )

        # Assert 2 — title browser harus sesuai halaman login
        assert login_page.get_page_title() == login_page.EXPECTED_TITLE, (
            "Title halaman tidak sesuai dengan halaman login setelah "
            "logout. "
            f"Diharapkan: '{login_page.EXPECTED_TITLE}' "
            f"Didapat: '{login_page.get_page_title()}'"
        )

        # Act — coba akses /monitoring langsung lagi setelah logout
        # Ini membuktikan session benar-benar dihapus, bukan hanya
        # redirect kosmetik yang masih meninggalkan session aktif
        monitoring_page.open("/monitoring")

        WebDriverWait(driver, EXPLICIT_WAIT).until(
            EC.url_contains("/login"),
            message=(
                "Setelah logout, /monitoring masih bisa diakses tanpa "
                "login ulang. Session mungkin belum benar-benar "
                "dihapus. "
                f"URL saat ini: {driver.current_url}"
            )
        )

        # Assert 3 — session benar-benar invalid, /monitoring tetap
        # tidak bisa diakses tanpa login ulang
        assert login_page.is_on_login_page(), (
            "Setelah logout, akses langsung ke /monitoring seharusnya "
            "tetap diarahkan ke /login. "
            f"URL saat ini: {login_page.get_current_url()}"
        )

        assert "/monitoring" not in login_page.get_current_url(), (
            "Session masih aktif setelah logout — /monitoring berhasil "
            "diakses kembali tanpa login ulang."
        )