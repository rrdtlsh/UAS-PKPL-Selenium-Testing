from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.webdriver.remote.webdriver import WebDriver
from utils.config import EXPLICIT_WAIT, BASE_URL


class BasePage:
    """
    Parent class untuk semua Page Object.

    Args:
        driver: Instance WebDriver yang sudah dikonfigurasi oleh driver_factory.
    """

    def __init__(self, driver: WebDriver):
        self.driver = driver
        self.wait   = WebDriverWait(driver, EXPLICIT_WAIT)

    # ─── Navigation ───────────────────────────────────────────────────────

    def open(self, path: str = "") -> None:
        """
        Navigasi ke URL.

        Args:
            path: Path relatif dari BASE_URL. Misal: "/login", "/monitoring".
                  Kosong = buka BASE_URL.
        """
        url = f"{BASE_URL}{path}" if path else BASE_URL
        self.driver.get(url)

    # ─── Interaction ──────────────────────────────────────────────────────

    def click(self, by: By, locator: str) -> None:
        """Tunggu elemen clickable, lalu klik."""
        element = self.wait.until(EC.element_to_be_clickable((by, locator)))
        element.click()

    def type(self, by: By, locator: str, text: str) -> None:
        """Tunggu elemen visible, clear isinya, lalu ketik teks."""
        element = self.wait.until(EC.visibility_of_element_located((by, locator)))
        element.clear()
        element.send_keys(text)

    # ─── Query ────────────────────────────────────────────────────────────

    def get_text(self, by: By, locator: str) -> str:
        """Ambil teks dari elemen. Return string kosong jika tidak ditemukan."""
        try:
            element = self.wait.until(EC.visibility_of_element_located((by, locator)))
            return element.text
        except Exception:
            return ""

    def is_visible(self, by: By, locator: str) -> bool:
        """Return True jika elemen visible dalam waktu EXPLICIT_WAIT detik."""
        try:
            self.wait.until(EC.visibility_of_element_located((by, locator)))
            return True
        except Exception:
            return False

    def get_current_url(self) -> str:
        """Return URL halaman saat ini."""
        return self.driver.current_url

    def get_page_source(self) -> str:
        """Return HTML source halaman saat ini."""
        return self.driver.page_source