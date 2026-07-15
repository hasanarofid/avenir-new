const fs = require('fs');

let vueContent = fs.readFileSync('resources/js/Pages/OwnershipIntelligence/Index.vue', 'utf8');

// Ensure props define manualInputs
if (!vueContent.includes('manualInputs: Object')) {
    vueContent = vueContent.replace(
        'dataUrl: String',
        'dataUrl: String,\n    manualInputs: Object'
    );
}

if (!vueContent.includes('window.manualInputs = props.manualInputs;')) {
    vueContent = vueContent.replace(
        'const DATA = await response.json();',
        'const DATA = await response.json();\n        window.manualInputs = props.manualInputs || {};'
    );
}

// Now we need to inject the logic into the proxy pane rendering.
// Since the proxy pane in the vanilla script might be rendered by a function like `renderProxyExplorer` or similar.
// Wait, the `Avenir-OwnershipIntelligence-logic.html` script doesn't dynamically render the UBO image. We have to hook into it.
// Let's find where the panes are updated when a ticker is selected.
// Usually there's a `renderAll()` function or `setSelected(k)`.
// We can append some script at the bottom of initVanillaLogic to patch renderAll.

const patchScript = `
    const originalSetSelected = window.setSelected || (typeof setSelected === 'function' ? setSelected : null);
    if (originalSetSelected) {
        window.setSelected = function(k) {
            originalSetSelected(k);
            
            // Look for proxyPane or auditPane and inject our manual images!
            const proxyBody = document.getElementById('upstreamPaths');
            const manualInputs = window.manualInputs || {};
            
            const ent = DATA.entities && DATA.entities[k] ? DATA.entities[k] : { ticker: '' };
            const ticker = ent.ticker || k.replace('E:', '');
            
            if (proxyBody) {
                // Check if there is manual image
                const manual = manualInputs[ticker];
                let manualHtml = '';
                
                if (manual && manual.ubo_image_path) {
                    manualHtml += \`<div class="panel" style="box-shadow:none; margin-top:16px;">
                        <div class="panelHead">
                            <h3 class="text-amber-500">Official UBO Structure (Annual Report)</h3>
                        </div>
                        <div class="panelBody">
                            <img src="/storage/\${manual.ubo_image_path}" alt="UBO Structure" style="max-width:100%; border-radius: 8px; border: 1px solid #2a3a33;" />
                        </div>
                    </div>\`;
                }
                
                if (manual && manual.shareholder_image_path) {
                    manualHtml += \`<div class="panel" style="box-shadow:none; margin-top:16px;">
                        <div class="panelHead">
                            <h3 class="text-amber-500">Official Shareholder Composition (Annual Report)</h3>
                        </div>
                        <div class="panelBody">
                            <img src="/storage/\${manual.shareholder_image_path}" alt="Shareholders" style="max-width:100%; border-radius: 8px; border: 1px solid #2a3a33;" />
                        </div>
                    </div>\`;
                }
                
                // If there's an existing manual container, update it, else append
                let container = document.getElementById('manual-ubo-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'manual-ubo-container';
                    // Let's insert it before the upstreamPaths or inside proxyPane
                    const proxyPane = document.getElementById('proxyPane');
                    if(proxyPane) {
                        const pb = proxyPane.querySelector('.panelBody');
                        if (pb) pb.appendChild(container);
                    }
                }
                container.innerHTML = manualHtml;
            }
        };
        
        // Trigger for initial selection
        if (selectedKey) {
            window.setSelected(selectedKey);
        }
    }
`;

if (!vueContent.includes('manual-ubo-container')) {
    vueContent = vueContent.replace(
        'switchTab(\'networkPane\');',
        `switchTab('networkPane');\n\n${patchScript}`
    );
}

fs.writeFileSync('resources/js/Pages/OwnershipIntelligence/Index.vue', vueContent);

