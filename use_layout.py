import re

with open('resources/js/Pages/DeskBrief/Index.vue', 'r') as f:
    content = f.read()

# Add AppLayout import
import_stmt = "import AppLayout from '@/Layouts/AppLayout.vue';\n"
if "import AppLayout from '@/Layouts/AppLayout.vue';" not in content:
    content = content.replace("import { Head, Link } from '@inertiajs/vue3';", "import { Head, Link } from '@inertiajs/vue3';\n" + import_stmt)

# Remove old nav and wrap template
nav_regex = re.compile(r'<!-- NAV -->\s*<div class="nav">.*?</script>\s*<template>\s*<div class="deskbrief-page">', re.DOTALL)
# wait, the nav is inside <template> <div class="deskbrief-page">
nav_regex2 = re.compile(r'(<template>\s*<div class="deskbrief-page">)\s*<!-- NAV -->\s*<div class="nav">.*?</div>\s*</div>\s*<!-- PAGE HEADER -->', re.DOTALL)

replacement = """<template>
  <AppLayout>
    <Head title="Desk Brief - Avenir" />
    <div class="deskbrief-page">

<!-- PAGE HEADER -->"""

new_content, count = nav_regex2.subn(replacement, content)
print(f"Replaced nav {count} times.")

# Add closing </AppLayout> at the end of file
if count > 0:
    new_content = new_content.replace('</template>', '  </AppLayout>\n</template>')

with open('resources/js/Pages/DeskBrief/Index.vue', 'w') as f:
    f.write(new_content)
