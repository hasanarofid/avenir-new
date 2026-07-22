const fs = require('fs');
const html = fs.readFileSync('prd-testing/ownership/new/Avenir-OwnershipIntelligence-6-31.html', 'utf8');
const startMatch = "const DATA = {";
const startIndex = html.indexOf(startMatch);
if (startIndex !== -1) {
    let text = html.substring(startIndex + 13);
    const end = text.indexOf('\n/* ============ MANUAL OVERRIDES (Admin) ============ */');
    if (end !== -1) {
        text = text.substring(0, end);
        // Find the last semicolon
        const lastSemi = text.lastIndexOf(';');
        if (lastSemi !== -1) {
            text = text.substring(0, lastSemi);
        }
        text = text.trim();
        fs.writeFileSync('public/ownership_mock.json', text);
        console.log('Extracted to public/ownership_mock.json, length: ' + text.length);
    } else {
        console.log('Could not find end marker');
    }
} else {
    console.log('Could not find start marker');
}
