import fs from 'fs';
import vm from 'vm';
import assert from 'assert';

console.log('=== TEST 1: Print Blade JavaScript Stress Harness ===');

const filePath = 'resources/views/app/invoices/print.blade.php';
assert(fs.existsSync(filePath), `File not found: ${filePath}`);

const content = fs.readFileSync(filePath, 'utf8');

// Regex to extract all <script>...</script> blocks
const scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gi;
const scripts = [];
let match;
while ((match = scriptRegex.exec(content)) !== null) {
    scripts.push({
        raw: match[0],
        code: match[1].trim(),
        hasSrc: /src=["']/.test(match[0])
    });
}

console.log(`Found ${scripts.length} script tags in ${filePath}`);

// Verify syntax of all inline scripts
scripts.forEach((s, idx) => {
    if (s.hasSrc && !s.code) {
        console.log(`[PASS] Script ${idx}: External script with src (no inline code)`);
        return;
    }

    console.log(`Analyzing inline Script ${idx} (${s.code.length} chars)...`);

    // Replace Blade tokens with valid mock JS literals
    let processedCode = s.code
        // Handle Blade echo {{ ... }}
        .replace(/\{\{\s*route\([^)]+\)\s*\}\}/g, 'http://localhost/invoices/1/pdf')
        .replace(/\{\{\s*__\([^)]+\)\s*\}\}/g, 'Mocked Translated String')
        .replace(/\{\{\s*\$invoice->invoice_number\s*\}\}/g, 'INV-2026-001')
        .replace(/\{\{\s*asset\([^)]+\)\s*\}\}/g, '/vendor/html2pdf.bundle.min.js')
        .replace(/\{\{\s*.*?\s*\}\}/g, 'mock_blade_echo')
        // Handle unescaped echo {!! ... !!}
        .replace(/\{!!\s*.*?\s*!!\}/g, 'mock_unescaped')
        // Handle @json, @js
        .replace(/@json\(.*?\)/g, '{}')
        .replace(/@js\(.*?\)/g, '{}');

    // 1. AST / Syntax Check via vm.Script
    try {
        new vm.Script(processedCode);
        console.log(`[PASS] Script ${idx}: Syntax is 100% valid JavaScript`);
    } catch (err) {
        console.error(`[FAIL] Script ${idx} syntax error:`, err);
        process.exit(1);
    }
});

// Now let's stress test invocation of Script 3 (the main logic with downloadPDF and window load event)
console.log('\n--- Invocation & Behavior Stress Test for downloadPDF() & window.onload ---');

function createMockEnvironment(options = {}) {
    let printCalled = 0;
    let locationHref = 'http://localhost/invoices/1/print';
    let listeners = {};
    let timeouts = [];

    const mockBtn = {
        id: 'btnDownloadPdf',
        innerHTML: 'Download PDF Original',
        disabled: false,
        classList: {
            classes: new Set(),
            add(...cls) { cls.forEach(c => this.classes.add(c)); },
            remove(...cls) { cls.forEach(c => this.classes.delete(c)); },
            contains(c) { return this.classes.has(c); }
        }
    };

    const mockElement = {
        style: {
            padding: '20px'
        }
    };

    let html2pdfInstance = null;
    if (options.hasHtml2pdf) {
        html2pdfInstance = {
            set(opt) {
                this.options = opt;
                return this;
            },
            from(el) {
                this.element = el;
                return this;
            },
            save() {
                return new Promise((resolve, reject) => {
                    if (options.html2pdfShouldFail) {
                        reject(new Error('Simulated html2pdf failure'));
                    } else {
                        resolve();
                    }
                });
            }
        };
    }

    const documentMock = {
        writeCalled: [],
        write(str) { this.writeCalled.push(str); },
        getElementById(id) {
            if (id === 'btnDownloadPdf') return mockBtn;
            return null;
        },
        querySelector(sel) {
            if (sel === '.print-sheet') return mockElement;
            return null;
        }
    };

    const windowMock = {
        location: {
            href: locationHref,
            search: options.search || ''
        },
        print() {
            printCalled++;
        },
        addEventListener(event, fn) {
            if (!listeners[event]) listeners[event] = [];
            listeners[event].push(fn);
        },
        setTimeout(fn, delay) {
            timeouts.push({ fn, delay });
            return timeouts.length;
        }
    };

    const sandbox = {
        document: documentMock,
        window: windowMock,
        location: windowMock.location,
        URLSearchParams: URLSearchParams,
        console: console,
        setTimeout: windowMock.setTimeout,
        ...(options.hasHtml2pdf ? { html2pdf: () => html2pdfInstance } : {})
    };

    // If html2pdf is undefined in options, explicitly ensure it is undefined in sandbox
    if (!options.hasHtml2pdf) {
        delete sandbox.html2pdf;
    }

    return {
        sandbox,
        mockBtn,
        mockElement,
        windowMock,
        listeners,
        timeouts,
        getPrintCalled: () => printCalled
    };
}

// Extract main script code
const mainScript = scripts.find(s => s.code.includes('downloadPDF'));
assert(mainScript, 'Main script containing downloadPDF not found!');

let processedMainCode = mainScript.code
    .replace(/\{\{\s*route\([^)]+\)\s*\}\}/g, 'http://localhost/invoices/1/pdf')
    .replace(/\{\{\s*__\([^)]+\)\s*\}\}/g, 'Mocked Translated String')
    .replace(/\{\{\s*\$invoice->invoice_number\s*\}\}/g, 'INV-2026-001')
    .replace(/\{\{\s*.*?\s*\}\}/g, 'mock_token');

// Case 1: Fallback when html2pdf is undefined
{
    console.log('Testing Case 1: downloadPDF() when html2pdf is UNDEFINED (Fallback route redirect)');
    const env = createMockEnvironment({ hasHtml2pdf: false });
    vm.createContext(env.sandbox);
    vm.runInContext(processedMainCode, env.sandbox);

    // Call downloadPDF
    env.sandbox.downloadPDF();

    // Verify padding was temporarily changed then restored
    assert.strictEqual(env.mockElement.style.padding, '20px', 'Element padding should be restored to 20px');
    // Verify redirected to invoices.pdf
    assert.strictEqual(env.windowMock.location.href, 'http://localhost/invoices/1/pdf', 'Window location should redirect to PDF endpoint');
    console.log('[PASS] Case 1 passed: Fallback successfully navigated to server-side PDF generator.');
}

// Case 2: html2pdf is DEFINED and SUCCEEDS
{
    console.log('Testing Case 2: downloadPDF() when html2pdf is DEFINED and SUCCEEDS');
    const env = createMockEnvironment({ hasHtml2pdf: true, html2pdfShouldFail: false });
    vm.createContext(env.sandbox);
    vm.runInContext(processedMainCode, env.sandbox);

    // Call downloadPDF
    env.sandbox.downloadPDF();

    // Verify initial button state during PDF generation
    assert.strictEqual(env.mockBtn.disabled, true, 'Button should be disabled during generation');
    assert.strictEqual(env.mockBtn.classList.contains('cursor-wait'), true, 'Button should have cursor-wait');

    // Wait for the promise to resolve
    await new Promise(r => setTimeout(r, 10));

    // Verify button restored after generation
    assert.strictEqual(env.mockBtn.disabled, false, 'Button should be re-enabled after success');
    assert.strictEqual(env.mockBtn.classList.contains('cursor-wait'), false, 'cursor-wait should be removed');
    assert.strictEqual(env.mockElement.style.padding, '20px', 'Element padding should be restored');
    assert.ok(env.mockBtn.innerHTML.includes('Mocked Translated String'), 'Success message should be displayed');

    // Fast-forward timeout to restore original HTML
    assert.strictEqual(env.timeouts.length, 1, 'Should schedule timeout to restore original HTML');
    env.timeouts[0].fn();
    assert.strictEqual(env.mockBtn.innerHTML, 'Download PDF Original', 'Original button HTML should be restored');
    console.log('[PASS] Case 2 passed: html2pdf success flow handles states, padding, and UI restoration correctly.');
}

// Case 3: html2pdf is DEFINED and FAILS
{
    console.log('Testing Case 3: downloadPDF() when html2pdf FAILS (fallback to window.print)');
    const env = createMockEnvironment({ hasHtml2pdf: true, html2pdfShouldFail: true });
    vm.createContext(env.sandbox);
    vm.runInContext(processedMainCode, env.sandbox);

    // Call downloadPDF
    env.sandbox.downloadPDF();

    // Wait for promise rejection
    await new Promise(r => setTimeout(r, 10));

    // Verify fallback to window.print()
    assert.strictEqual(env.getPrintCalled(), 1, 'window.print() should be called upon html2pdf error');
    assert.strictEqual(env.mockBtn.disabled, false, 'Button should be re-enabled after error');
    assert.strictEqual(env.mockElement.style.padding, '20px', 'Element padding should be restored');
    assert.strictEqual(env.mockBtn.innerHTML, 'Download PDF Original', 'Original HTML should be restored');
    console.log('[PASS] Case 3 passed: html2pdf failure cleanly triggers window.print() fallback.');
}

// Case 4: window load event with ?download=1 or ?pdf=1
{
    console.log('Testing Case 4: window.onload with ?pdf=1 query parameter');
    const env = createMockEnvironment({ search: '?pdf=1' });
    vm.createContext(env.sandbox);
    vm.runInContext(processedMainCode, env.sandbox);

    assert.ok(env.listeners['load'], 'Should register window load listener');
    env.listeners['load'].forEach(fn => fn());

    assert.strictEqual(env.windowMock.location.href, 'http://localhost/invoices/1/pdf', 'Should automatically redirect to PDF');
    console.log('[PASS] Case 4 passed: Auto-download via query param redirects properly.');
}

// Case 5: window load event with ?print=1
{
    console.log('Testing Case 5: window.onload with ?print=1 query parameter');
    const env = createMockEnvironment({ search: '?print=1' });
    vm.createContext(env.sandbox);
    vm.runInContext(processedMainCode, env.sandbox);

    assert.ok(env.listeners['load'], 'Should register window load listener');
    env.listeners['load'].forEach(fn => fn());

    assert.strictEqual(env.timeouts.length, 1, 'Should trigger window.print with setTimeout');
    assert.strictEqual(env.timeouts[0].delay, 500, 'Print delay should be 500ms');
    env.timeouts[0].fn();
    assert.strictEqual(env.getPrintCalled(), 1, 'window.print() should be executed');
    console.log('[PASS] Case 5 passed: Auto-print query param triggers window.print() after 500ms delay.');
}

// Case 6: Script 2 (fallback loader for html2pdf)
{
    console.log('\nTesting Script 2 (CDN fallback document.write when html2pdf is missing)');
    const fallbackScript = scripts.find(s => s.code.includes('document.write'));
    assert(fallbackScript, 'Fallback loader script not found');
    
    // When html2pdf is defined: document.write should NOT be called
    {
        const env = createMockEnvironment({ hasHtml2pdf: true });
        vm.createContext(env.sandbox);
        vm.runInContext(fallbackScript.code, env.sandbox);
        assert.strictEqual(env.sandbox.document.writeCalled.length, 0, 'document.write should NOT be called when html2pdf is defined');
    }
    // When html2pdf is undefined: document.write SHOULD be called
    {
        const env = createMockEnvironment({ hasHtml2pdf: false });
        vm.createContext(env.sandbox);
        vm.runInContext(fallbackScript.code, env.sandbox);
        assert.strictEqual(env.sandbox.document.writeCalled.length, 1, 'document.write should be called once');
        assert.ok(env.sandbox.document.writeCalled[0].includes('cdnjs.cloudflare.com/ajax/libs/html2pdf.js'), 'Should inject CDN script tag');
        console.log('[PASS] Script 2 passed: Conditional CDN fallback injection behaves as expected.');
    }
}

console.log('\n>>> ALL PRINT.BLADE.PHP JAVASCRIPT TESTS PASSED EMPIRICALLY! <<<');
