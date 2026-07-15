import re
import os
import sys

def main():
    html_file = sys.argv[1]
    vue_file = sys.argv[2]
    
    with open(html_file, 'r', encoding='utf-8') as f:
        content = f.read()
        
    # Extract style
    style_match = re.search(r'<style>(.*?)</style>', content, re.DOTALL)
    style_content = style_match.group(1) if style_match else ""
    
    # Remove the massive DATA variable from script
    # It starts with `const DATA = {` and ends with `};`
    # Since it's huge, we use regex to remove it
    script_match = re.search(r'<script>(.*?)</script>', content, re.DOTALL)
    script_content = script_match.group(1) if script_match else ""
    
    # We will just replace `const DATA = {...};` with nothing.
    script_content = re.sub(r'const DATA = \{.*?\};\n', 'let DATA = null;\n', script_content, flags=re.DOTALL)
    
    # Extract body content (inside <body> tag)
    body_match = re.search(r'<body[^>]*>(.*?)<script>', content, re.DOTALL)
    body_content = body_match.group(1) if body_match else ""
    
    # Build Vue component
    vue_template = f"""
<template>
  <div>
    {body_content}
  </div>
</template>

<script setup>
import {{ onMounted }} from 'vue';
import axios from 'axios';

// --- Extracted Script from HTML ---
{script_content}

// --- Vue Lifecycle ---
onMounted(async () => {{
    try {{
        const response = await axios.get('/api/admin/desk-brief/ownership/data');
        if(response.data.status === 'success') {{
            DATA = response.data;
            if (DATA.brokerDict) {{
                // Initialize BROKER_DICT
                window.BROKER_DICT = DATA.brokerDict;
            }}
            if (DATA.insiderData) {{
                localStorage.setItem('avenir_insider_snapshots_v1', JSON.stringify(DATA.insiderData));
            }}
            if (DATA.monthlyData) {{
                localStorage.setItem('avenir_monthly_data_v1', JSON.stringify(DATA.monthlyData));
            }}
            // Trigger rendering functions if they exist in the extracted script
            if(typeof initData === 'function') initData();
            if(typeof renderAll === 'function') renderAll();
        }}
    }} catch(e) {{
        console.error('Failed to fetch data', e);
    }}
}});
</script>

<style scoped>
{style_content}
</style>
"""

    with open(vue_file, 'w', encoding='utf-8') as f:
        f.write(vue_template)
        
    print(f"Successfully wrote Vue component to {vue_file}")

if __name__ == '__main__':
    main()
