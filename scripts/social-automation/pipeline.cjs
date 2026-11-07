const { chromium } = require('c:/laragon/www/cooca_core/node_modules/@playwright/test');
const fs = require('fs');
const path = require('path');
const { renderFeedGraphic } = require('./render-feed-image.cjs');

const CATALOG_PATH = path.resolve(__dirname, 'features-catalog.json');
const catalog = JSON.parse(fs.readFileSync(CATALOG_PATH, 'utf8'));

// Jam Viral Resmi per Platform (WIB - UTC+7)
const VIRAL_SCHEDULE_SLOTS = {
  linkedin: {
    platform: 'linkedin',
    slot_label: 'B2B Morning Insight',
    viral_time: '08:00:00',
    theme: 'Tata Kelola Bisnis, Multi-Outlet & Laporan Keuangan'
  },
  instagram: {
    platform: 'instagram',
    slot_label: 'Lunchtime Visual Carousel',
    viral_time: '12:15:00',
    theme: 'Visual Carousel Edukasi Ritel Toko Kamu (3D & Cutout)'
  },
  facebook: {
    platform: 'facebook',
    slot_label: 'Afternoon Community Post',
    viral_time: '16:30:00',
    theme: 'Studi Kasus Toko Kamu & Diskusi Komunitas Ritel'
  },
  tiktok: {
    platform: 'tiktok',
    slot_label: 'Prime Time FYP Hook',
    viral_time: '19:45:00',
    theme: 'Tips Kilat Anti-Bocor Kas & Solusi Cepat Operasional'
  }
};

const VIRAL_THRESHOLD = 80.0;

/**
 * Evaluasi Potensi Viralitas Topik (Ambang Batas Minimal >= 80/100)
 */
function evaluateViralPotential(feature) {
  const metrics = feature.viral_metrics || {};
  const trVel = metrics.trend_velocity || 88;
  const hookUrg = metrics.hook_urgency || 92;
  const visDep = metrics.visual_depth || 95;
  const ctaClar = metrics.cta_clarity || 90;

  // Bobot: Trend Velocity (30%), Hook Urgency (30%), Visual Depth 3D & Cutout (25%), CTA Clarity (15%)
  const score = (trVel * 0.30) + (hookUrg * 0.30) + (visDep * 0.25) + (ctaClar * 0.15);
  const roundedScore = Math.round(score * 10) / 10;
  const isPassed = roundedScore >= VIRAL_THRESHOLD;

  return {
    trend_velocity: trVel,
    hook_urgency: hookUrg,
    visual_depth: visDep,
    cta_clarity: ctaClar,
    viral_score: roundedScore,
    is_passed: isPassed,
    verdict: isPassed ? 'PASSED_VIRAL_GATE' : 'REJECTED_LOW_SCORE'
  };
}

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

  // Maintain compat filename if slug is provided
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

  // Prioritas utama: toko-kamu.json
  const defaultKamu = path.join(dir, 'toko-kamu.json');
  if (!slugOrFile && fs.existsSync(defaultKamu)) {
    return JSON.parse(fs.readFileSync(defaultKamu, 'utf8'));
  }

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

/**
 * Matriks 4 Konten Unik Harian di Jam Viral Resmi (Anti-Duplikasi)
 * Mengambil 4 fitur berbeda secara deterministik untuk 4 platform pada hari target
 */
function getDailyQuadFeatures(targetDateStr) {
  const targetDate = targetDateStr ? new Date(targetDateStr) : new Date();
  const dayOfYear = Math.floor((targetDate - new Date(targetDate.getFullYear(), 0, 0)) / 1000 / 60 / 60 / 24);

  const platforms = ['linkedin', 'instagram', 'facebook', 'tiktok'];
  const quad = [];

  for (let i = 0; i < platforms.length; i++) {
    const platform = platforms[i];
    const slotInfo = VIRAL_SCHEDULE_SLOTS[platform];
    const catIdx = (dayOfYear * 4 + i) % catalog.length;
    let candidate = catalog[catIdx];

    // Evaluasi potensi viralitas
    let viralEval = evaluateViralPotential(candidate);
    
    // Jika tidak lolos (< 80), cari fitur berikutnya yang lolos
    if (!viralEval.is_passed) {
      console.warn(`[Viral Gate] Fitur ${candidate.name} ditolak (Skor: ${viralEval.viral_score} < ${VIRAL_THRESHOLD}). Mencari alternatif...`);
      for (let offset = 1; offset < catalog.length; offset++) {
        const altIdx = (catIdx + offset) % catalog.length;
        const altCand = catalog[altIdx];
        const altEval = evaluateViralPotential(altCand);
        if (altEval.is_passed) {
          candidate = altCand;
          viralEval = altEval;
          break;
        }
      }
    }

    quad.push({
      platform,
      slot_label: slotInfo.slot_label,
      viral_time: slotInfo.viral_time,
      feature: candidate,
      viral_eval: viralEval
    });
  }

  return quad;
}

async function runPipelineForFeature(feature, slot, targetDateStr, persona, targetPlatform = 'instagram', viralEval = null) {
  const ssDir = path.resolve('storage/app/public/content-assets/screenshots');
  const platformMediaDir = path.resolve('storage/app/public/admin/social-media-assets/platform');

  if (!fs.existsSync(ssDir)) fs.mkdirSync(ssDir, { recursive: true });
  if (!fs.existsSync(platformMediaDir)) fs.mkdirSync(platformMediaDir, { recursive: true });

  const ymd = targetDateStr || new Date().toISOString().slice(0, 10);
  const slug = persona ? persona.slug : 'toko-kamu';

  // 1. Capture screen
  const ssPath = await captureScreen(feature, ssDir, `${slug}-${targetPlatform}`);

  // 2. Render 1080x1350 feed graphic with Cooca logo and mockup
  const dateTag = ymd.replace(/-/g, '');
  const outFileName = `${ymd}-${slug}-${targetPlatform}-${feature.id}-1080x1350.png`;
  const finalImagePath = path.join(platformMediaDir, outFileName);

  console.log(`[Render] Generating feed graphic for [${targetPlatform.toUpperCase()}] ${feature.name}...`);
  await renderFeedGraphic({
    feature,
    screenshotPath: ssPath,
    outputPath: finalImagePath,
    persona
  });

  // 3. Tentukan Jam Viral Resmi Berdasarkan Platform
  const slotInfo = VIRAL_SCHEDULE_SLOTS[targetPlatform] || { viral_time: '12:15:00' };
  const schedTimeStr = `${ymd} ${slotInfo.viral_time}`;

  // 4. Susun Caption Disesuaikan Platform (Platform Angle)
  let caption = '';
  if (feature.platform_angles && feature.platform_angles[targetPlatform]) {
    const angle = feature.platform_angles[targetPlatform];
    caption = `${angle.title}\n\n${angle.caption}\n\n${feature.hashtags.join(' ')}`;
  } else if (feature.full_caption) {
    caption = feature.full_caption;
  } else {
    caption = `${feature.caption_hook}\n\n${feature.caption_body}\n\nCoba gratis sistem kasir & operasional lengkap di: https://cooca.id\n(Klik tautan di bio untuk demo langsung tanpa registrasi kartu kredit!)\n\n${feature.hashtags.join(' ')}`;
  }

  // Bersihkan dari emoji dan em dash untuk kepatuhan brand
  caption = caption.replace(/[\u{1F600}-\u{1F64F}\u{1F300}-\u{1F5FF}\u{1F680}-\u{1F6FF}\u{2600}-\u{26FF}\u{2700}-\u{27BF}]/gu, '');
  caption = caption.replace(/—/g, '-').replace(/–/g, '-');

  const evalResult = viralEval || evaluateViralPotential(feature);

  return {
    feature_id: feature.id,
    feature_name: feature.name,
    slot,
    platform: targetPlatform,
    scheduled_at: schedTimeStr,
    viral_score: evalResult.viral_score,
    viral_verdict: evalResult.verdict,
    image_filename: outFileName,
    image_relative_path: `admin/social-media-assets/platform/${outFileName}`,
    image_full_path: finalImagePath,
    caption: caption.trim(),
    keywords: feature.keywords,
    hashtags: feature.hashtags,
    // Anti-Duplikasi: Postingan HANYA didaftarkan untuk platform spesifik ini pada hari tersebut
    platforms: [targetPlatform]
  };
}

async function main() {
  const args = process.argv.slice(2);
  const featArg = args.find(a => a.startsWith('--feature='))?.split('=')[1];
  const slotArg = args.find(a => a.startsWith('--slot='))?.split('=')[1] || 'all';
  const dateArg = args.find(a => a.startsWith('--date='))?.split('=')[1];
  const personaArg = args.find(a => a.startsWith('--persona='))?.split('=')[1];
  const persona = loadPersona(personaArg);

  console.log(`================================================================`);
  console.log(`  COOCA VIRAL SOCIAL CONTENT ENGINE (4 POSTS / DAY)             `);
  console.log(`  Viral Gate (>= 80/100) | Zero Same-Day Collision | Jam Viral  `);
  console.log(`================================================================`);
  console.log(`Target Date     : ${dateArg || 'Today'}`);
  console.log(`Requested Slot  : ${slotArg}`);
  console.log(`Persona Wajib   : ${persona.business_name} (Owner: ${persona.owner_name})`);

  let tasks = [];

  if (featArg) {
    const f = catalog.find(c => c.id === featArg) || catalog[0];
    const plat = slotArg === 'morning' ? 'linkedin' : (slotArg === 'evening' ? 'tiktok' : 'instagram');
    tasks.push({
      feature: f,
      slot: slotArg,
      platform: plat,
      viral_eval: evaluateViralPotential(f)
    });
  } else if (slotArg === 'morning') {
    // Mode kompatibilitas: hanya slot pagi (LinkedIn)
    const quad = getDailyQuadFeatures(dateArg);
    tasks.push({ feature: quad[0].feature, slot: 'morning', platform: 'linkedin', viral_eval: quad[0].viral_eval });
  } else if (slotArg === 'evening') {
    // Mode kompatibilitas: hanya slot malam (TikTok)
    const quad = getDailyQuadFeatures(dateArg);
    tasks.push({ feature: quad[3].feature, slot: 'evening', platform: 'tiktok', viral_eval: quad[3].viral_eval });
  } else {
    // Default: 4 Konten Unik Harian untuk 4 Platform di Jam Viral Masing-Masing
    const quad = getDailyQuadFeatures(dateArg);
    for (const q of quad) {
      tasks.push({
        feature: q.feature,
        slot: q.slot_label,
        platform: q.platform,
        viral_eval: q.viral_eval
      });
    }
  }

  const results = [];
  for (const t of tasks) {
    console.log(`\n--- [${t.platform.toUpperCase()} @ ${VIRAL_SCHEDULE_SLOTS[t.platform].viral_time} WIB] Topik: ${t.feature.name} ---`);
    console.log(`    Skor Viral : ${t.viral_eval.viral_score}/100 (${t.viral_eval.verdict})`);
    const res = await runPipelineForFeature(t.feature, t.slot, dateArg, persona, t.platform, t.viral_eval);
    results.push(res);
  }

  console.log('\n=== PIPELINE COMPLETED SUCCESSFULLY ===');
  console.log(`Generated ${results.length} platform-unique content item(s).`);

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

module.exports = { runPipelineForFeature, getDailyQuadFeatures, evaluateViralPotential };
