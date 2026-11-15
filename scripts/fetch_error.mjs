import fs from 'fs';

async function check() {
    const res = await fetch('http://127.0.0.1:8000/');
    const text = await res.text();
    fs.writeFileSync('scripts/error_page.html', text);
    console.log('Saved error_page.html, length:', text.length);
}

check().catch(console.error);
