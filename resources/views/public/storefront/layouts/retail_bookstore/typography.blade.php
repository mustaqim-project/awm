{{--
  Typography - Buku, Alat Tulis, Musik & Perlengkapan Sekolah (retail_bookstore)
  Heading  : Merriweather (serif display)
  Body     : Inter
  Karakter : Serif buku, nyaman dibaca
  Mode font: tetap (sama dengan font yang dimuat app.blade.php tema ini)
  Skala    : H1 27 -> 43px | H2 22 -> 31px | H3 18 -> 22px | H4 16 -> 18px | Hero 32 -> 54px (375px -> 1280px)
  Dipasang : di-include dari app.blade.php tema ini, SETELAH partial typography global.
  Aturan   : selector sengaja berspesifisitas rendah (html body h1), jadi utility Tailwind pada
             elemen (text-2xl, font-extrabold, tracking-wide, leading-tight) tetap menang.
             Hanya heading/teks TANPA utility yang mengikuti skala tema ini.
  Mengubah : cukup edit variabel --ts-* di blok :root, tanpa menyentuh view.
--}}
<style id="ts-typography-retail_bookstore">
    :root {
        /* Font. File font dimuat oleh app.blade.php; di sini hanya stack + fallback yang cocok. */
        --font-heading: 'Merriweather', ui-serif, Georgia, "Times New Roman", serif;
        --font-body: 'Inter', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;

        /* Skala fluid (viewport 375px -> 1280px) */
        --ts-display: clamp(2.025rem, 1.466rem + 2.39vw, 3.375rem);
        --ts-h1: clamp(1.688rem, 1.268rem + 1.79vw, 2.700rem);
        --ts-h2: clamp(1.350rem, 1.117rem + 0.99vw, 1.913rem);
        --ts-h3: clamp(1.125rem, 1.032rem + 0.40vw, 1.350rem);
        --ts-h4: clamp(1.012rem, 0.966rem + 0.20vw, 1.125rem);
        --ts-h5: clamp(1.000rem, 0.948rem + 0.22vw, 1.125rem);
        --ts-h6: clamp(0.875rem, 0.823rem + 0.22vw, 1.000rem);
        --ts-lead: clamp(1.062rem, 0.985rem + 0.33vw, 1.250rem);
        --ts-body: 1rem;
        --ts-small: 0.875rem;
        --ts-caption: 0.75rem;

        /* Karakter heading */
        --ts-heading-weight: 700;
        --ts-subheading-weight: 700;
        --ts-heading-tracking: -0.01em;
        --ts-heading-leading: 1.25;
        --ts-subheading-leading: 1.35;

        /* Karakter body */
        --ts-body-weight: 400;
        --ts-body-strong: 600;
        --ts-body-leading: 1.6;
        --ts-body-tracking: 0em;
        --ts-measure: 65ch;
    }

    /* Teks terang di latar gelap tampak lebih tebal dan padat: longgarkan sedikit. */
    .dark {
        --ts-body-leading: 1.65;
        --ts-body-tracking: 0.005em;
    }

    /* ---------- Dasar ---------- */
    html body {
        font-size: var(--ts-body);
        font-weight: var(--ts-body-weight);
        line-height: var(--ts-body-leading);
        letter-spacing: var(--ts-body-tracking);
        font-kerning: normal;
    }

    html body :is(strong, b) {
        font-weight: var(--ts-body-strong);
    }

    html body p {
        overflow-wrap: break-word;
        text-wrap: pretty;
    }

    html body small {
        font-size: var(--ts-small);
    }

    /* ---------- Heading ---------- */
    html body :is(h1, h2, h3, h4, h5, h6) {
        font-family: var(--font-heading);
        font-weight: var(--ts-subheading-weight);
        line-height: var(--ts-subheading-leading);
        letter-spacing: calc(var(--ts-heading-tracking) / 2);
        font-optical-sizing: auto;
        font-variant-numeric: lining-nums;
        overflow-wrap: break-word; /* teks Indonesia panjang tidak memicu scroll horizontal di 375px */
        text-wrap: balance;
    }

    html body h1 {
        font-size: var(--ts-h1);
        font-weight: var(--ts-heading-weight);
        line-height: var(--ts-heading-leading);
        letter-spacing: var(--ts-heading-tracking);
    }

    html body h2 {
        font-size: var(--ts-h2);
        font-weight: var(--ts-heading-weight);
        line-height: var(--ts-heading-leading);
        letter-spacing: var(--ts-heading-tracking);
    }

    html body h3 { font-size: var(--ts-h3); }
    html body h4 { font-size: var(--ts-h4); }
    html body h5 { font-size: var(--ts-h5); }
    html body h6 { font-size: var(--ts-h6); }

    /* Elemen non-heading yang memakai kelas font-heading ikut font tema (stack + fallback benar). */
    .cooca-storefront .font-heading {
        font-family: var(--font-heading);
        font-variant-numeric: lining-nums;
    }

    /* ---------- Kelas opsional (opt-in, tidak mengubah view yang sudah ada) ---------- */
    .ts-display {
        font-family: var(--font-heading);
        font-size: var(--ts-display);
        font-weight: var(--ts-heading-weight);
        line-height: calc(var(--ts-heading-leading) - 0.05);
        letter-spacing: var(--ts-heading-tracking);
        text-wrap: balance;
        overflow-wrap: break-word;
    }

    .ts-lead {
        font-size: var(--ts-lead);
        line-height: 1.55;
        max-width: var(--ts-measure);
    }

    .ts-measure {
        max-width: var(--ts-measure);
    }

    .ts-eyebrow {
        font-family: var(--font-body);
        font-size: var(--ts-caption);
        font-weight: var(--ts-body-strong);
        line-height: 1.4;
        letter-spacing: 0.08em;
        text-transform: uppercase; /* hanya label pendek, bukan kalimat */
    }

    .ts-caption {
        font-size: var(--ts-caption);
        line-height: 1.4;
    }

    .ts-price {
        font-family: var(--font-body);
        font-weight: 700;
        font-variant-numeric: tabular-nums lining-nums;
        letter-spacing: -0.01em;
    }
</style>
