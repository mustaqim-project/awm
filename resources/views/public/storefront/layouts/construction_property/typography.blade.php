{{--
  Typography - Konstruksi & Properti (construction_property)
  Heading  : Rajdhani (condensed / teknis)
  Body     : Plus Jakarta Sans
  Karakter : Teknis sempit, ukuran +12% agar setara
  Mode font: dinamis (ikut $activeTheme, default di bawah)
  Skala    : H1 34 -> 54px | H2 27 -> 38px | H3 22 -> 27px | H4 20 -> 22px | Hero 40 -> 67px (375px -> 1280px)
  Dipasang : di-include dari app.blade.php tema ini, SETELAH partial typography global.
  Aturan   : selector sengaja berspesifisitas rendah (html body h1), jadi utility Tailwind pada
             elemen (text-2xl, font-extrabold, tracking-wide, leading-tight) tetap menang.
             Hanya heading/teks TANPA utility yang mengikuti skala tema ini.
  Mengubah : cukup edit variabel --ts-* di blok :root, tanpa menyentuh view.
--}}
@php
    $tsFontHeading = $activeTheme['font_heading'] ?? 'Rajdhani';
    $tsFontBody = $activeTheme['font_body'] ?? 'Plus Jakarta Sans';
@endphp
<style id="ts-typography-construction_property">
    :root {
        /* Font. File font dimuat oleh app.blade.php; di sini hanya stack + fallback yang cocok. */
        --font-heading: '{{ $tsFontHeading }}', "Arial Narrow", "Roboto Condensed", ui-sans-serif, system-ui, sans-serif;
        --font-body: '{{ $tsFontBody }}', ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;

        /* Skala fluid (viewport 375px -> 1280px) */
        --ts-display: clamp(2.520rem, 1.824rem + 2.97vw, 4.200rem);
        --ts-h1: clamp(2.100rem, 1.578rem + 2.23vw, 3.360rem);
        --ts-h2: clamp(1.680rem, 1.390rem + 1.24vw, 2.380rem);
        --ts-h3: clamp(1.400rem, 1.284rem + 0.50vw, 1.680rem);
        --ts-h4: clamp(1.260rem, 1.202rem + 0.25vw, 1.400rem);
        --ts-h5: clamp(1.000rem, 0.948rem + 0.22vw, 1.125rem);
        --ts-h6: clamp(0.875rem, 0.823rem + 0.22vw, 1.000rem);
        --ts-lead: clamp(1.062rem, 0.985rem + 0.33vw, 1.250rem);
        --ts-body: 1rem;
        --ts-small: 0.875rem;
        --ts-caption: 0.75rem;

        /* Karakter heading */
        --ts-heading-weight: 700;
        --ts-subheading-weight: 600;
        --ts-heading-tracking: 0.01em;
        --ts-heading-leading: 1.1;
        --ts-subheading-leading: 1.2;

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
