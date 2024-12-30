import os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# Create the output directory if it doesn't exist
cur_dir = os.path.dirname(__file__)
output_dir = cur_dir + "/exportraw_bewerbe"
os.makedirs(output_dir, exist_ok=True)

# Initialize WebDriver (e.g., for Chrome)
options = webdriver.ChromeOptions()
#headless
headless = False
if headless:
    options.add_argument("--headless")
    options.add_argument("--disable-gpu")
    # options.add_argument("--window-size=1920,1200")
driver = webdriver.Chrome(options=options)
done = []

try:
    # Open the website
    base_url = "http://www.ff-grosshartmannsdorf.at/cms/index.php/aktuelle-berichte/bewerbswesen?start=100"
    driver.get(base_url)

    # Wait for the page to load
    wait = WebDriverWait(driver, 10)

    while True:
        # Locate all "readmore" links and save their hrefs in a list
        readmore_links = driver.find_elements(By.XPATH, "//p[@class='readmore']/a[@class='btn']")
        hrefs = [link.get_attribute("href") for link in readmore_links]

        if all([h in done for h in hrefs]):
            # Check if there is a "Weiter" button
            weiter_button = driver.find_elements(By.XPATH, "//a[contains(text(), 'Weiter')]")
            if weiter_button:
                weiter_button[0].click()
                try:
                    wait.until(EC.presence_of_all_elements_located((By.XPATH, "//p[@class='readmore']/a[@class='btn']")))
                except: 
                    print("No readmore articles found go next")
                continue
            else:
                print("No more links to process and no 'Weiter' button found.")
                break

        # Iterate over the saved hrefs
        for index, href in enumerate(hrefs):
            try:
                # Open the link
                driver.get(href)

                # Wait for the page to load
                wait.until(EC.presence_of_element_located((By.TAG_NAME, "body")))

                print(f"Loaded page {index + 1}: {driver.title}")

                # Optionally, get the page title and HTML source
                title = driver.title.replace(" ", "_").replace("/", "-")
                html_source = driver.page_source

                # Save the HTML to the output folder
                file_path = os.path.join(output_dir, f"{title}.html")
                with open(file_path, "w", encoding="utf-8") as file:
                    file.write(html_source)
                done.append(href)
                print(f"Saved: {file_path}")

                # Navigate back to the main page
                driver.get(base_url)
                wait.until(EC.presence_of_all_elements_located((By.XPATH, "//p[@class='readmore']/a[@class='btn']")))

            except Exception as e:
                print(f"Error processing link {index + 1}: {e}")

finally:
    driver.quit()
