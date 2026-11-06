const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');
const { renderFeedGraphic } = require('./render-feed-image.cjs');

const CATALOG_PATH = path.resolve(__dirname, 'features-catalog.json');
const catalog = JSON.parse(fs.readFileSync(CATALOG_PATH, 'utf8'));

async function captureScreen(feature, outDir, slug) {
  const browser = await chromium.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true
  });

  const authPath = path.resolve('tests/e2e/.auth/user.json');
  const context = await browser.newContext({
    storageState: fs.existsSync(authPath) ? authPath : undefined,
    viewport: feature.viewport || { width: 1440, height: 900 },
    ignoreHTTPSErrors: true
  });

  const page = await context.newPage();
  const targetUrl = 'http://cooca_core.test' + (feature.route || '/dashboard');
  
  console.log(`[Playwright] Navigating to ${targetUrl}...`);
  try {
    await page.goto(targetUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForTimeout(1500); // allow charts/animations to settle
  } catch (err) {
    console.warn(`[Playwright] Warning on navigation: ${err.message}. Retrying simple load...`);
    await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });
  }

  const primaryName = slug ? `${slug}-cooca-ui.png` : `${feature.id}-desktop.png`;
  const ssPath = path.join(outDir, primaryName);
  await page.screenshot({ path: ssPath, fullPage: false });

  // Also maintain compat filename if slug is provided
  if (slug) {
    const compatPath = path.join(outDir, `${feature.id}-desktop.png`);
    fs.copyFileSync(ssPath, compatPath);
  }

  await browser.close();

  console.log(`[Playwright] Screen captured: ${ssPath}`);
  return ssPath;
}

function loadPersona(slugOrFile) {
  const dir = path.resolve('database/data/social_content/business-personas');
  if (!fs.existsSync(dir)) return null;

  if (slugOrFile) {
    const candidate1 = path.join(dir, `${slugOrFile}.json`);
    const candidate2 = path.join(dir, slugOrFile);
    if (fs.existsSync(candidate1)) return JSON.parse(fs.readFileSync(candidate1, 'utf8'));
    if (fs.existsSync(candidate2)) return JSON.parse(fs.readFileSync(candidate2, 'utf8'));
  }

  const files = fs.readdirSync(dir).filter(f => f.endsWith('.json'));
  if (files.length > 0) {
    return JSON.parse(fs.readFileSync(path.join(dir, files[0]), 'utf8'));
  }
  return null;
}

function getDailyFeatures(targetDateStr) {
  const targetDate = targetDateStr ? new Date(targetDateStr) : new Date();
  const dayOfYear = Math.floor((targetDate - new Date(targetDate.getFullYear(), 0, 0)) / 1000 / 60 / 60 / 24);

  // Pick 2 features alternating through catalog
  const idx1 = (dayOfYear * 2) % catalog.length;
  const idx2 = (dayOfYear * 2 + 1) % catalog.length;

  return {
    morning: { ...catalog[idx1], slot: 'morning' },
    evening: { ...catalog[idx2], slot: 'evening' }
  };
}

async function runPipelineForFeature(feature, slot, targetDateStr, persona) {
  const ssDir = path.resolve('storage/app/public/content-assets/screenshots');
  const platformMediaDir = path.resolve('storage/app/public/admin/social-media-assets/platform');

  if (!fs.existsSync(ssDir)) fs.mkdirSync(ssDir, { recursive: true });
  if (!fs.existsSync(platformMediaDir)) fs.mkdirSync(platformMediaDir, { recursive: true });

  const ymd = targetDateStr || new Date().toISOString().slice(0, 10);
  const slug = persona ? persona.slug : feature.id;

  // 1. Capture screen
  const ssPath = await captureScreen(feature, ssDir, slug);

  // 2. Render 1080x1350 feed graphic with Cooca logo and mockup
  const dateTag = ymd.replace(/-/g, '');
  const outFileName = slug ? `${ymd}-${slug}-1080x1350.png` : `feed-${feature.id}-${slot}-${dateTag}.png`;
  const finalImagePath = path.join(platformMediaDir, outFileName);

  console.log(`[Render] Generating feed graphic for ${feature.name}...`);
  await renderFeedGraphic({
    feature,
    screenshotPath: ssPath,
    outputPath: finalImagePath,
    persona
  });

  // 3. Prepare copy & schedule time
  const schedTimeStr = slot === 'morning' ? `${ymd} 09:30:00` : `${ymd} 19:00:00`;

  const fullCaption = feature.full_caption
    ? feature.full_caption
    : `${feature.caption_hook} 💡\n\n${feature.caption_body}\n\n👉 Coba gratis sistem kasir & operasional lengkap di: https://cooca.id\n(Klik tautan di bio untuk demo langsung tanpa registrasi kartu kredit!)\n\n${feature.hashtags.join(' ')}`;

  return {
    feature_id: feature.id,
    feature_name: feature.name,
    slot,
    scheduled_at: schedTimeStr,
    image_filename: outFileName,
    image_relative_path: `admin/social-media-assets/platform/${outFileName}`,
    image_full_path: finalImagePath,
    caption: fullCaption,
    keywords: feature.keywords,
    hashtags: feature.hashtags,
    platforms: ['instagram', 'facebook', 'linkedin', 'tiktok']
  };
}

async function main() {
  const args = process.argv.slice(2);
  const featArg = args.find(a => a.startsWith('--feature='))?.split('=')[1];
  const slotArg = args.find(a => a.startsWith('--slot='))?.split('=')[1] || 'both';
  const dateArg = args.find(a => a.startsWith('--date='))?.split('=')[1];
  const personaArg = args.find(a => a.startsWith('--persona='))?.split('=')[1];
  const persona = loadPersona(personaArg);

  console.log(`=== COOCA AUTOMATED SOCIAL CONTENT GENERATOR ===`);
  console.log(`Target Date: ${dateArg || 'Today'}`);
  console.log(`Requested Slot: ${slotArg}, Feature Override: ${featArg || 'Auto Rotation'}`);
  if (persona) {
    console.log(`Loaded Persona: ${persona.business_name} (${persona.owner_name}) [${persona.slug}]`);
  }

  let tasks = [];

  if (featArg) {
    const f = catalog.find(c => c.id === featArg) || catalog[0];
    tasks.push({ feature: f, slot: slotArg === 'both' ? 'evening' : slotArg });
  } else {
    // Check if keyword research for this target date specified a winning topic
    const kwPath = path.resolve(`database/data/social_content/keyword-research/${dateArg || new Date().toISOString().slice(0, 10)}.json`);
    let kwFeature = null;
    if (fs.existsSync(kwPath)) {
      try {
        const kwData = JSON.parse(fs.readFileSync(kwPath, 'utf8'));
        if (kwData.selected_winning_topic && kwData.selected_winning_topic.primary_keyword.includes('stok')) {
          kwFeature = catalog.find(c => c.id === 'inventory');
        }
      } catch (e) {}
    }

    const daily = getDailyFeatures(dateArg);
    if (slotArg === 'morning' || slotArg === 'both') {
      tasks.push({ feature: daily.morning, slot: 'morning' });
    }
    if (slotArg === 'evening' || slotArg === 'both') {
      tasks.push({ feature: kwFeature || daily.evening, slot: 'evening' });
    }
  }

  const results = [];
  for (const t of tasks) {
    console.log(`\n--- Processing Slot [${t.slot.toUpperCase()}]: ${t.feature.name} ---`);
    const res = await runPipelineForFeature(t.feature, t.slot, dateArg, persona);
    results.push(res);
  }

  console.log('\n=== PIPELINE COMPLETED SUCCESSFULLY ===');
  console.log(JSON.stringify(results, null, 2));

  // Write output json for Laravel Artisan command to consume
  const outJsonPath = path.resolve('storage/app/public/content-assets/last-generated.json');
  fs.writeFileSync(outJsonPath, JSON.stringify(results, null, 2), 'utf8');
}

if (require.main === module) {
  main().catch(err => {
    console.error('[Error] Pipeline failed:', err);
    process.exit(1);
  });
}

module.exports = { runPipelineForFeature, getDailyFeatures };
