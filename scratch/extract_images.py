import os
import re
import urllib.parse
import base64

html_path = 'app/website/artikel.html'
output_dir = 'public/images/articles'

os.makedirs(output_dir, exist_ok=True)

with open(html_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Let's find all article cards or specifically image tags.
# An image tag looks like: <img alt="..." src="data:image/..." />
# Or it might be inside <div class="artikel-card" onclick="window.location.href='artikel-slug.html'"> ... <img src="..." />
# Let's find all card divs and extract the target slug and the image src.

# Regex to find each article card:
# <div class="artikel-card" onclick="window.location.href='(?P<link>[^']+)'">.*?<img[^>]+src="(?P<src>[^"]+)"
cards = re.finditer(r'class="artikel-card"\s+onclick="window.location.href=\'(?P<link>[^\']+)\'".*?<img[^>]+src="(?P<src>[^"]+)"', content, re.DOTALL)

count = 0
for match in cards:
    link = match.group('link')
    src = match.group('src')
    
    # Get slug from link, e.g. "artikel-paradoks-pdb-rupiah-2026.html" -> "paradoks-pdb-rupiah-2026"
    slug = link.replace('artikel-', '').replace('.html', '')
    
    # Check what kind of data URI it is
    if src.startswith('data:image/svg+xml'):
        # URL encoded SVG
        prefix = 'data:image/svg+xml;charset=UTF-8,'
        if src.startswith(prefix):
            svg_data = src[len(prefix):]
        else:
            # try to split by comma
            svg_data = src.split(',', 1)[1]
            
        svg_content = urllib.parse.unquote(svg_data)
        out_path = os.path.join(output_dir, f'{slug}.svg')
        with open(out_path, 'w', encoding='utf-8') as out_f:
            out_f.write(svg_content)
        print(f"Extracted SVG for {slug} -> {out_path}")
        count += 1
    elif src.startswith('data:image/'):
        # Base64 encoded image (jpeg, png, etc.)
        # Format: data:image/jpeg;base64,...
        parts = src.split(',', 1)
        mime = parts[0]
        data_base64 = parts[1]
        
        ext = 'png'
        if 'jpeg' in mime or 'jpg' in mime:
            ext = 'jpg'
        elif 'webp' in mime:
            ext = 'webp'
            
        img_data = base64.b64decode(data_base64)
        out_path = os.path.join(output_dir, f'{slug}.{ext}')
        with open(out_path, 'wb') as out_f:
            out_f.write(img_data)
        print(f"Extracted Base64 Image for {slug} -> {out_path}")
        count += 1
    else:
        print(f"Skipped src for {slug}: {src[:50]}...")

print(f"Done! Extracted {count} images.")
