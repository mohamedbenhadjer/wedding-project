import os
import glob
import re

for filepath in glob.glob("/home/mohamed/wedding-project/public/*.php"):
    with open(filepath, "r") as f:
        content = f.read()
    
    # Replace href="/" with href="index.php"
    content = re.sub(r'href="/"', 'href="index.php"', content)
    
    # Replace href="/..." with href="..."
    content = re.sub(r'href="/([^"]+)"', r'href="\1"', content)
    
    # Replace src="/..." with src="..."
    content = re.sub(r'src="/([^"]+)"', r'src="\1"', content)
    
    with open(filepath, "w") as f:
        f.write(content)
