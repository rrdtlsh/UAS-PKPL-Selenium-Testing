from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.remote.webdriver import WebDriver
from pages.base_page import BasePage


class MonitoringPage(BasePage):
    """Page Object untuk /monitoring."""

    # ─── Locators ─────────────────────────────────────────────────────────
    # Diverifikasi dari monitoring.blade.php

    # <button type="submit" class="logout-btn">Logout</button> — baris 464
    LOGOUT_BUTTON = (By.CSS_SELECTOR, "button.logout-btn")

    # ─── Expected values ──────────────────────────────────────────────────
    # Dari <title>Monitoring Kondisi Lab | PPKPL Kelompok 5</title> — baris 7
    EXPECTED_TITLE_FRAGMENT = "Monitoring Kondisi Lab"

    def __init__(self, driver: WebDriver):
        super().__init__(driver)

    # ─── Actions ──────────────────────────────────────────────────────────

    def click_logout(self) -> None:
        """Klik tombol Logout di sidebar."""
        self.click(*self.LOGOUT_BUTTON)

    # ─── Queries ──────────────────────────────────────────────────────────

    def is_loaded(self) -> bool:
        """
        Return True jika halaman monitoring berhasil dimuat.

        Tiga kondisi harus terpenuhi sekaligus:
          1. URL mengandung /monitoring
          2. Title browser mengandung "Monitoring Kondisi Lab"
          3. Tombol Logout visible di sidebar
        """
        url_ok    = "/monitoring" in self.get_current_url()
        title_ok  = self.EXPECTED_TITLE_FRAGMENT in self.driver.title
        logout_ok = self.is_visible(*self.LOGOUT_BUTTON)
        return url_ok and title_ok and logout_ok

    def is_on_monitoring_page(self) -> bool:
        """Return True jika URL saat ini mengandung /monitoring."""
        return "/monitoring" in self.get_current_url()

    def wait_until_loaded(self) -> None:
        """
        Tunggu sampai halaman monitoring benar-benar siap.
        Digunakan setelah klik Login untuk memberi waktu redirect selesai.
        """
        self.wait.until(
            EC.url_contains("/monitoring")
        )