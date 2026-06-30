import pytest
from selenium.webdriver.support.ui import WebDriverWait

from pages.admin_page import AdminPage
from utils.config import EXPLICIT_WAIT


class TestPetugasAccess:
    """
    Test suite untuk akses halaman /admin oleh user dengan role petugas.

    Menggunakan fixture `logged_in_petugas` dari conftest.py:
    Chrome yang sudah login sebagai petugas@lab.com via LoginPage,
    sehingga test ini tidak perlu mengulang logika login.
    """

    # ─────────────────────────────────────────────────────────────────────
    # TC-S05: Petugas tidak boleh mengakses halaman Administrator
    # Priority: Critical
    # ─────────────────────────────────────────────────────────────────────

    def test_TC_S05_petugas_tidak_bisa_akses_halaman_admin(self, logged_in_petugas):
        """
        TC-S05: Verifikasi bahwa user dengan role petugas ditolak akses
        ke halaman /admin.

        Langkah:
          1. Login sebagai petugas@lab.com (sudah dilakukan oleh fixture
             logged_in_petugas)
          2. Navigasi ke /admin
          3. Tunggu halaman menampilkan indikator 403

        Expected:
          - URL tetap di /admin (RoleMiddleware abort(403), bukan redirect)
          - Page source mengandung indikator 403 ("403"/"Forbidden"/
            "Akses ditolak")
          - Panel Administrator TIDAK ditampilkan

        Referensi: routes/web.php baris 169 — komentar pada route
        eksplisit menyebut "TC-S05: Petugas tidak boleh mengakses
        halaman Admin". RoleMiddleware.php memanggil abort(403) untuk
        role di luar daftar yang diizinkan, yang TIDAK melakukan
        redirect — karena itu wait di sini tidak bisa memakai
        EC.url_contains().
        """
        # Arrange
        driver     = logged_in_petugas
        admin_page = AdminPage(driver)

        # Act — coba akses /admin sebagai petugas
        admin_page.open()

        # Act — tunggu page source menampilkan indikator 403
        # abort(403) tidak mengubah URL, jadi tidak bisa pakai
        # EC.url_contains(). Logika ini mencerminkan
        # AdminPage.is_403_page() agar konsisten.
        WebDriverWait(driver, EXPLICIT_WAIT).until(
            lambda d: (
                "403" in d.page_source
                or "Forbidden" in d.page_source
                or "Akses ditolak" in d.page_source
            ),
            message=(
                "Halaman /admin tidak menampilkan indikator 403 "
                "untuk user dengan role petugas. "
                f"URL saat ini: {driver.current_url}"
            )
        )

        # Assert 1 — URL tetap di /admin (abort 403, bukan redirect)
        assert admin_page.is_on_admin_page(), (
            "URL seharusnya tetap di /admin meskipun akses ditolak "
            "(abort 403 tidak melakukan redirect). "
            f"URL saat ini: {admin_page.get_current_url()}"
        )

        # Assert 2 — halaman harus berupa halaman 403
        assert admin_page.is_403_page(), (
            "Petugas seharusnya mendapatkan halaman 403 saat mengakses "
            "/admin, tetapi indikator 403 tidak ditemukan di page source."
        )

        # Assert 3 — Panel Administrator TIDAK boleh ditampilkan
        assert not admin_page.is_loaded(), (
            "Petugas berhasil melihat 'Panel Administrator'. "
            "RoleMiddleware seharusnya menolak akses untuk role ini."
        )