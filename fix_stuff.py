import re

# Remove footer from Index.vue
with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    content = f.read()

footer_regex = re.compile(r'<!-- FOOTER -->\s*<div class="foot">.*?</div>\s*</div>', re.DOTALL)
new_content, count = footer_regex.subn('', content)
print(f"Replaced footer {count} times.")

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(new_content)
