const fs = require('fs');
const content = fs.readFileSync('resources/views/app/invoices/print.blade.php', 'utf8');
const scripts = content.match(/<script[\s\S]*?<\/script>/gi);

if (!scripts) {
    console.error('No script tags found!');
    process.exit(1);
}

console.log(`Found ${scripts.length} script blocks in resources/views/app/invoices/print.blade.php.`);

scripts.forEach((scriptTag, idx) => {
    // When Blade compiles {{ $var }}, it replaces it with string content inside quotes.
    // e.g. "http://example.com" or 'Faktur-123.pdf'
    let jsCode = scriptTag.replace(/<\/?script[^>]*>/gi, '')
                          .replace(/@json\(.*?\)/g, '{}')
                          .replace(/@js\(.*?\)/g, '{}')
                          .replace(/\{\{.*?\}\}/g, 'token')
                          .replace(/\{!!.*?!!\}/g, 'token');
    try {
        new Function(jsCode);
        console.log(`Script block [${idx}] parsed successfully without syntax errors.`);
    } catch (err) {
        console.error(`Script block [${idx}] SYNTAX ERROR: ${err.message}`);
        process.exit(1);
    }
});

console.log('ALL JS SCRIPT BLOCKS CLEAN AND VALID!');
