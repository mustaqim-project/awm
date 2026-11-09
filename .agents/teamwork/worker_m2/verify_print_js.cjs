const fs = require('fs');
const content = fs.readFileSync('resources/views/app/invoices/print.blade.php', 'utf8');
const scripts = content.match(/<script[\s\S]*?<\/script>/gi);

if (!scripts) {
    console.log('No scripts found in print.blade.php');
    process.exit(0);
}

scripts.forEach((s, idx) => {
    try {
        let code = s.replace(/<\/?script[^>]*>/gi, '')
                    .replace(/@json\(.*?\)/g, '{}')
                    .replace(/@js\(.*?\)/g, '{}')
                    .replace(/\{\{.*?\}\}/g, 'token')
                    .replace(/\{!!.*?!!\}/g, 'token');
        new Function(code);
        console.log(`Script ${idx} syntax OK`);
    } catch(e) {
        console.error(`Script ${idx} ERROR:`, e.message);
        process.exit(1);
    }
});

console.log('All JS scripts in print.blade.php validated successfully!');
