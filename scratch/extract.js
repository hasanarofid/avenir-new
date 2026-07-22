const fs = require('fs');
const html = fs.readFileSync('prd-testing/ownership/new/Avenir-OwnershipIntelligence-6-31.html', 'utf8');
const startMatch = "const DATA = {";
const startIndex = html.indexOf(startMatch);
if (startIndex !== -1) {
    // Find the end of the JSON object.
    // It's followed by "const entityArr ="
    const endIndex = html.indexOf(';', startIndex + startMatch.length - 1);
    // Actually, in the HTML, the JSON ends at `},"affiliations":{}};` wait, let's just find `};\nconst entityArr` or similar.
    let text = html.substring(startIndex + 13);
    const end = text.indexOf('\nconst entityArr');
    if (end !== -1) {
        text = text.substring(0, end);
        // remove trailing semicolon if exists
        text = text.trim();
        if (text.endsWith(';')) text = text.slice(0, -1);
        fs.writeFileSync('public/ownership_mock.json', text);
        console.log('Extracted to public/ownership_mock.json, length: ' + text.length);
    } else {
        console.log('Could not find end marker');
    }
} else {
    console.log('Could not find start marker');
}
