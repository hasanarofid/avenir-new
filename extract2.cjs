const fs = require('fs');
const html = fs.readFileSync('prd-testing/ownership/new/Avenir-OwnershipIntelligence-6-31.html', 'utf8');
const startMatch = "const DATA = {";
const startIndex = html.indexOf(startMatch);
if (startIndex !== -1) {
    let text = html.substring(startIndex + 13);
    const end = text.indexOf('\nconst entityArr');
    if (end !== -1) {
        text = text.substring(0, end).trim();
        if (text.endsWith(';')) text = text.slice(0, -1);
        
        // Let's use eval or new Function to get the object safely
        try {
            const dataObj = new Function('return ' + text)();
            fs.writeFileSync('public/ownership_mock.json', JSON.stringify(dataObj));
            console.log('Extracted valid JSON to public/ownership_mock.json');
        } catch(e) {
            console.log('Error parsing extracted text:', e);
        }
    } else {
        console.log('Could not find end marker');
    }
} else {
    console.log('Could not find start marker');
}
