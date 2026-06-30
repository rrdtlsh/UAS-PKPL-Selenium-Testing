import pytest
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

from pages.admin_page import AdminPage
from utils.config import EXPLICIT_WAIT


class TestAdminAccess:
    """
    Test suite untuk akses halaman /admin oleh user dengan role admin.

    Menggunakan fixture `logged_in_admin` dari conftest.py:
    Chrome yang sudah login sebagai admin@lab.com via LoginPage,
    sehingga test ini tidak perlu mengulang logika login.
    """

    # ─────────────────────────────────────────────────────────────────────
    # TC-S04: Admin dapat mengakses halaman /admin
    # Priority: Critical
    # ─────────────────────────────────────────────────────────────────────

    def test_TC_S04_admin_bisa_akses_halaman_admin(self, logged_in_admin):
        """
        TC-S04: Verifikasi bahwa user dengan role admin dapat mengakses
        halaman /admin dan melihat Panel Administrator.

        Langkah:
          1. Login sebagai admin@lab.com (sudah dilakukan oleh fixture
             logged_in_admin)
          2. Navigasi ke /admin
          3. Tunggu heading halaman tampil

        Expected:
          - URL mengandung /admin
          - Heading h1 berisi "Panel Administrator"
          - Badge role menampilkan "Administrator"
          - Halaman bukan halaman 403 (akses tidak ditolak)

        Referensi: routes/web.php — /admin dibungkus
        Route::middleware('role:admin') di dalam grup auth, sehingga
        hanya role 'admin' yang lolos RoleMiddleware.
        """
        # Arrange
        driver     = logged_in_admin
        admin_page = AdminPage(driver)

        # Act — navigasi ke /admin
        admin_page.open()

        # Act — tunggu heading halaman admin tampil
        # Menggunakan WebDriverWait eksplisit agar tidak flaky
        WebDriverWait(driver, EXPLICIT_WAIT).until(
            EC.visibility_of_element_located(admin_page.PAGE_HEADING),
            message=(
                "Heading halaman /admin tidak muncul setelah navigasi. "
                f"URL saat ini: {driver.current_url}"
            )
        )

        # Assert 1 — URL harus berada di /admin
        assert admin_page.is_on_admin_page(), (
            "Admin seharusnya tetap berada di /admin setelah navigasi. "
            f"URL saat ini: {admin_page.get_current_url()}"
        )

        # Assert 2 — halaman Panel Administrator harus berhasil dimuat
        assert admin_page.is_loaded(), (
            "Halaman /admin tidak menampilkan 'Panel Administrator' "
            "untuk user dengan role admin. "
            f"URL: {admin_page.get_current_url()}"
        )

        # Assert 3 — badge role harus menampilkan "Administrator"
        badge_text = admin_page.get_text(*admin_page.ROLE_BADGE)
        assert admin_page.ADMIN_BADGE_TEXT in badge_text, (
            "Badge role tidak menampilkan teks yang sesuai. "
            f"Diharapkan mengandung: '{admin_page.ADMIN_BADGE_TEXT}' "
            f"Didapat: '{badge_text}'"
        )

        # Assert 4 — halaman tidak boleh menampilkan error 403
        # Sanity check: admin tidak boleh ditolak akses ke halamannya sendiri
        assert not admin_page.is_403_page(), (
            "Admin seharusnya tidak mendapatkan halaman 403 saat "
            "mengakses /admin. RoleMiddleware mungkin salah konfigurasi."
        )