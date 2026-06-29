from selenium.webdriver.common.by import By
from selenium.webdriver.remote.webdriver import WebDriver
from pages.base_page import BasePage


class LoginPage(BasePage):
    """Page Object untuk /login."""

    # ─── Locators ─────────────────────────────────────────────────────────
    # Diverifikasi dari login.blade.php — jangan ubah tanpa cek blade dulu

    # <input type="email" name="email" class="form-control ...">
    EMAIL_INPUT = (By.CSS_SELECTOR, "input[name='email']")

    # <input type="password" name="password" class="form-control">
    PASSWORD_INPUT = (By.CSS_SELECTOR, "input[name='password']")

    # <button type="submit" class="btn btn-primary w-100">Login</button>
    SUBMIT_BUTTON = (By.CSS_SELECTOR, "button[type='submit']")

    # <div class="alert alert-danger">{{ $errors->first() }}</div>
    # Hanya muncul saat login gagal (conditional @if $errors->any())
    ERROR_ALERT = (By.CSS_SELECTOR, "div.alert.alert-danger")

    # ─── Title yang diharapkan ────────────────────────────────────────────
    # Dari <title>Login | Sistem Monitoring & Alert</title>
    EXPECTED_TITLE = "Login | Sistem Monitoring & Alert"

    def __init__(self, driver: WebDriver):
        super().__init__(driver)

    # ─── Actions ──────────────────────────────────────────────────────────

    def open(self) -> None:
        """Navigasi ke /login."""
        super().open("/login")

    def enter_email(self, email: str) -> None:
        """Isi field email."""
        self.type(*self.EMAIL_INPUT, email)

    def enter_password(self, password: str) -> None:
        """Isi field password."""
        self.type(*self.PASSWORD_INPUT, password)

    def click_submit(self) -> None:
        """Klik tombol Login."""
        self.click(*self.SUBMIT_BUTTON)

    def login(self, email: str, password: str) -> None:
        """
        Shortcut: isi email + password + submit dalam satu panggilan.
        Digunakan oleh fixture conftest dan langsung dari test.
        """
        self.enter_email(email)
        self.enter_password(password)
        self.click_submit()

    # ─── Queries ──────────────────────────────────────────────────────────

    def get_error_message(self) -> str:
        """
        Return teks pesan error login.
        Dari AuthController: 'Email atau password tidak valid.'
        Return string kosong jika tidak ada error.
        """
        return self.get_text(*self.ERROR_ALERT)

    def is_error_visible(self) -> bool:
        """Return True jika div alert-danger tampil di halaman."""
        return self.is_visible(*self.ERROR_ALERT)

    def is_on_login_page(self) -> bool:
        """Return True jika URL saat ini mengandung /login."""
        return "/login" in self.get_current_url()

    def get_page_title(self) -> str:
        """Return title tab browser halaman login."""
        return self.driver.title