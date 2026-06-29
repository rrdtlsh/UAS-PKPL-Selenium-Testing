from selenium import webdriver
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from webdriver_manager.chrome import ChromeDriverManager
from utils.config import IMPLICIT_WAIT


def create_driver(headless: bool = False) -> webdriver.Chrome:
    """
    Membuat dan mengembalikan instance Chrome WebDriver.

    Args:
        headless: True = tanpa tampilan browser. Default False.

    Returns:
        webdriver.Chrome: Driver siap pakai.
    """
    options = Options()

    if headless:
        options.add_argument("--headless=new")
        options.add_argument("--window-size=1920,1080")
        options.add_argument("--disable-gpu")
        options.add_argument("--no-sandbox")
        options.add_argument("--disable-dev-shm-usage")

    # Nonaktifkan notifikasi browser yang bisa mengganggu test
    options.add_argument("--disable-notifications")
    options.add_experimental_option("excludeSwitches", ["enable-logging"])

    service = Service(ChromeDriverManager().install())
    driver  = webdriver.Chrome(service=service, options=options)

    driver.maximize_window()
    driver.implicitly_wait(IMPLICIT_WAIT)

    return driver