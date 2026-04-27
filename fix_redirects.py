import os
import glob
import re

php_files = glob.glob("/home/mohamed/wedding-project/public/*.php") + glob.glob("/home/mohamed/wedding-project/includes/*.php")

for filepath in php_files:
    with open(filepath, "r") as f:
        content = f.read()
    
    # Replace redirect("/") with redirect("index.php")
    content = re.sub(r'redirect\([\'"]\/[\'"]\)', 'redirect(\'index.php\')', content)
    
    # Replace redirect("/...") with redirect("...")
    content = re.sub(r'redirect\([\'"]\/([^\'"]+)[\'"]\)', r"redirect('\1')", content)
    
    with open(filepath, "w") as f:
        f.write(content)
