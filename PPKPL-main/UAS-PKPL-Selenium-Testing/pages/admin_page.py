from selenium.webdriver.common.by import By
from selenium.webdriver.remote.webdriver import WebDriver
from pages.base_page import BasePage
from utils.config import ADMIN_URL


class AdminPage(BasePage):
    """Page Object untuk /admin."""

    # ─── Locators ────────────────────────────────────────────────────────────
    PAGE_HEADING = (By.TAG_NAME, "h1")
    ROLE_BADGE   = (By.CSS_SELECTOR, "div.badge")

    # String identifier dari admin/dashboard.blade.php
    ADMIN_HEADING_TEXT = "Panel Administrator"
    ADMIN_BADGE_TEXT   = "Administrator"

    def __init__(self, driver: WebDriver):
        super().__init__(driver)

    # ─── Actions ─────────────────────────────────────────────────────────────

    def open(self) -> None:
        """Navigasi langsung ke /admin."""
        super().open("/admin")

    # ─── Queries ─────────────────────────────────────────────────────────────

    def is_loaded(self) -> bool:
        """
        Return True jika halaman admin berhasil dimuat (hanya untuk admin).

        Verifikasi menggunakan:
          1. Teks h1 == "Panel Administrator"
          2. Badge "Administrator" visible
        """
        heading_text = self.get_text(*self.PAGE_HEADING)
        return self.ADMIN_HEADING_TEXT in heading_text

    def is_403_page(self) -> bool:
        """
        Return True jika halaman menampilkan error 403 (untuk petugas).

        Laravel abort(403) menampilkan halaman error default dengan
        teks "403" dan pesan "Akses ditolak..." di page source.
        """
        source = self.get_page_source()
        panel_admin_absent = self.ADMIN_HEADING_TEXT not in source
        is_forbidden = (
            "403" in source or
            "Forbidden" in source or
            "Akses ditolak" in source
        )
        return panel_admin_absent and is_forbidden

    def is_on_admin_page(self) -> bool:
        """Return True jika URL saat ini adalah /admin."""
        return "/admin" in self.get_current_url()