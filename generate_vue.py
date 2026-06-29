import re

with open('resources/js/Pages/DeskBrief/Index_script.js', 'r') as f:
    script_content = f.read()

with open('resources/css/deskbrief.css', 'r') as f:
    css_content = f.read()

with open('resources/js/Pages/DeskBrief/Index_template.html', 'r') as f:
    template_content = f.read()

# Modify CSS
css_content = css_content.replace('body{', '.deskbrief-page{')
css_content = css_content.replace('width:1440px', 'width:100%;max-width:1440px;margin:0 auto;overflow-x:hidden;')

# Remove AppLayout from script
script_content = script_content.replace("import AppLayout from '@/Layouts/AppLayout.vue';", "")

# Wrap template
template_content = f"""<template>
  <div class="deskbrief-page">
{template_content}
  </div>
</template>"""

final_vue = f"""{script_content}

{template_content}

<style>
{css_content}

@media print {{
  .nav, .foot, .pbtns, .dropdown {{ display: none !important; }}
  .deskbrief-page {{ width: 100% !important; }}
  .wrap {{ padding: 0 !important; }}
}}
</style>
"""

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(final_vue)
