import re

# Fix Index.vue ring class
with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    content = f.read()

# The class might be "ring" exactly
new_content = content.replace('<div class="ring" style="align-self:center">', '<div class="regime-ring" style="align-self:center">')

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(new_content)

# Fix deskbrief.css
with open('resources/css/deskbrief.css', 'r') as f:
    css_content = f.read()

css_content = css_content.replace('.ring{', '.regime-ring{')
css_content = css_content.replace('.ring ', '.regime-ring ')
# just in case
css_content = css_content.replace('.rings', '.regime-rings') # wait, rings is plural

with open('resources/css/deskbrief.css', 'w') as f:
    f.write(css_content)

# Fix Footer max width
with open('resources/js/Components/Footer.vue', 'r') as f:
    footer_content = f.read()

footer_content = footer_content.replace('max-w-[1200px]', 'max-w-[1440px]')

with open('resources/js/Components/Footer.vue', 'w') as f:
    f.write(footer_content)

print("UI fixes applied.")
