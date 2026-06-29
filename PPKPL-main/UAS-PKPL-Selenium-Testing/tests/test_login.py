import pytest
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

from pages.login_page import LoginPage
from pages.monitoring_page import MonitoringPage
from utils.config import (
    ADMIN_EMAIL,
    ADMIN_PASSWORD,
    INVALID_EMAIL,
    INVALID_PASSWORD,
    MONITORING_URL,
    EXPLICIT_WAIT,
)


class TestLogin:
    """
    Test suite untuk fitur login.

    Menggunakan fixture `driver` dari conftest.py:
    Chrome baru, belum login, session kosong.
    """

    # ─────────────────────────────────────────────────────────────────────
    # TC-S01: Login berhasil sebagai admin
    # Priority: Critical
    # ─────────────────────────────────────────────────────────────────────

    def test_TC_S01_login_berhasil_sebagai_admin(self, driver):
        """
        TC-S01: Verifikasi bahwa admin dapat login dengan kredensial valid
        dan diarahkan ke halaman /monitoring.

        Langkah:
          1. Buka halaman /login
          2. Isi email dengan admin@lab.com
          3. Isi password dengan password123
          4. Klik tombol Login
          5. Tunggu redirect selesai
          6. Verifikasi halaman monitoring berhasil dimuat

        Expected:
          - URL mengandung /monitoring
          - Title browser mengandung "Monitoring Kondisi Lab"
          - Tombol Logout visible di sidebar
        """
        # Arrange
        login_page      = LoginPage(driver)
        monitoring_page = MonitoringPage(driver)

        # Act — buka halaman login
        login_page.open()

        # Assert — halaman login berhasil dimuat sebelum aksi
        assert login_page.is_on_login_page(), (
            "Gagal membuka halaman login. "
            f"URL saat ini: {login_page.get_current_url()}"
        )

        # Act — isi form dan submit
        login_page.login(ADMIN_EMAIL, ADMIN_PASSWORD)

        # Act — tunggu redirect ke /monitoring selesai
        # Menggunakan WebDriverWait eksplisit agar tidak flaky
        WebDriverWait(driver, EXPLICIT_WAIT).until(
            EC.url_contains("/monitoring"),
            message=(
                "Redirect ke /monitoring tidak terjadi setelah login. "
                f"URL saat ini: {driver.current_url}"
            )
        )

        # Assert — verifikasi tiga kondisi halaman monitoring
        assert monitoring_page.is_loaded(), (
            "Halaman monitoring tidak berhasil dimuat setelah login admin. "
            f"URL: {monitoring_page.get_current_url()} | "
            f"Title: {driver.title}"
        )

        # Assert — URL tidak boleh mengandung /login lagi
        assert "/login" not in monitoring_page.get_current_url(), (
            "Setelah login berhasil, URL masih berada di halaman login."
        )

    # ─────────────────────────────────────────────────────────────────────
    # TC-S02: Login gagal menggunakan password yang salah
    # Priority: High
    # ─────────────────────────────────────────────────────────────────────

    def test_TC_S02_login_gagal_password_salah(self, driver):
        """
        TC-S02: Verifikasi bahwa sistem menolak kredensial tidak valid
        dan menampilkan pesan error yang sesuai.

        Langkah:
          1. Buka halaman /login
          2. Isi email dengan admin@lab.com
          3. Isi password dengan passwordSALAH (password salah)
          4. Klik tombol Login
          5. Tunggu halaman merespons

        Expected:
          - URL tetap di /login (tidak berpindah ke /monitoring)
          - Muncul elemen div.alert.alert-danger
          - Pesan error mengandung "Email atau password tidak valid."
        """
        # Arrange
        login_page = LoginPage(driver)

        # Act — buka halaman login
        login_page.open()

        # Assert — konfirmasi berada di login page sebelum aksi
        assert login_page.is_on_login_page(), (
            "Gagal membuka halaman login. "
            f"URL saat ini: {login_page.get_current_url()}"
        )

        # Act — isi form dengan kredensial yang salah dan submit
        login_page.enter_email(INVALID_EMAIL)
        login_page.enter_password(INVALID_PASSWORD)
        login_page.click_submit()

        # Assert 1 — URL harus tetap di /login (tidak redirect ke /monitoring)
        # WebDriverWait menunggu hingga kondisi terpenuhi atau timeout
        WebDriverWait(driver, EXPLICIT_WAIT).until(
            EC.url_contains("/login"),
            message=(
                "Setelah login gagal, URL seharusnya tetap /login. "
                f"URL saat ini: {driver.current_url}"
            )
        )

        assert login_page.is_on_login_page(), (
            "Setelah login dengan kredensial salah, "
            "user tidak boleh meninggalkan halaman login."
        )

        # Assert 2 — alert error harus muncul
        assert login_page.is_error_visible(), (
            "Pesan error tidak muncul setelah login gagal. "
            "Elemen div.alert.alert-danger tidak ditemukan."
        )

        # Assert 3 — teks error harus sesuai dengan AuthController
        # Dari: ->withErrors(['email' => 'Email atau password tidak valid.'])
        error_message = login_page.get_error_message()
        assert "Email atau password tidak valid." in error_message, (
            f"Pesan error tidak sesuai. "
            f"Diharapkan: 'Email atau password tidak valid.' "
            f"Didapat: '{error_message}'"
        )

        # Assert 4 — halaman monitoring tidak boleh bisa diakses
        assert "/monitoring" not in login_page.get_current_url(), (
            "Sistem secara tidak sengaja mengizinkan login dengan "
            "kredensial yang salah."
        )