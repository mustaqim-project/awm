import fs from 'fs';

const html = fs.readFileSync('scripts/error_page.html', 'utf-8');
const match = html.match(/"message":"([^"]+)"/);
if (match) {
    console.log('Error message:', match[1]);
}
const excMatch = html.match(/"class":"([^"]+)"/);
if (excMatch) {
    console.log('Exception class:', excMatch[1]);
}
const titleMatch = html.match(/<title>([^<]+)<\/title>/);
if (titleMatch) {
    console.log('Title:', titleMatch[1]);
}
