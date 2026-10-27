@extends('layouts.app', [
    'title' => __('marketplace.products_page_title') . ' - ' . $business->name,
    'headerTitle' => __('marketplace.products_page_title'),
    'headerSubtitle' => __('marketplace.products_page_subtitle'),
])

@section('content')
@php
    $productsMap = $products->getCollection()->keyBy('id')->map(function ($p) {
        $primaryImgUrl = $p->image_url ?? $p->image ?? null;
        $gallery = [];
        if ($p->relationLoaded('images') && $p->images) {
            foreach ($p->images as $img) {
                $gallery[] = [
                    'id' => (string) $img->id,
                    'url' => $img->image_url,
                    'caption' => $img->caption,
                    'is_primary' => (bool) $img->is_primary,
                ];
            }
        }
        if (empty($gallery) && $primaryImgUrl) {
            $gallery[] = [
                'id' => 'primary',
                'url' => $primaryImgUrl,
                'caption' => 'Foto Utama',
                'is_primary' => true,
            ];
        }

        return [
            'id' => (string) $p->id,
            'name' => $p->name,
            'code' => $p->code ?? '',
            'selling_price' => (float) $p->selling_price,
            'base_cost' => (float) $p->base_cost,
            'stock' => (int) ($p->stock ?? 0),
            'unit' => $p->unit ?? 'pcs',
            'weight' => (float) ($p->weight ?: 200),
            'length' => (float) ($p->length ?: 10),
            'width' => (float) ($p->width ?: 10),
            'height' => (float) ($p->height ?: 10),
            'description' => $p->description ?? '',
            'category' => $p->category ? ['name' => $p->category->name] : null,
            'gallery_images' => $gallery,
            'image_url' => $primaryImgUrl,
            'marketplace_mappings' => $p->marketplaceMappings->map(function ($m) use ($p) {
                return [
                    'id' => (string) $m->id,
                    'channel' => $m->channel,
                    'marketplace_item_id' => $m->marketplace_item_id ?: $m->external_product_id,
                    'marketplace_sku' => $m->marketplace_sku ?: $m->external_sku_code,
                    'channel_price' => $m->channel_price,
                    'sync_price_auto' => (bool) $m->sync_price_auto,
                    'price_multiplier' => (float) ($m->price_multiplier ?? 1.0),
                    'effective_price' => (float) $m->getEffectivePrice(),
                    'sync_stock_auto' => (bool) $m->sync_stock_auto,
                    'stock_buffer' => (int) ($m->stock_buffer ?? 0),
                    'custom_stock' => $m->custom_stock,
                    'effective_stock' => (int) $m->getEffectiveStock((int) ($p->stock ?? 0)),
                    'is_below_cost' => (bool) $m->isBelowCost(),
                    'is_margin_negative' => (bool) $m->isMarginNegativeAfterFees(),
                    'cost_deficit' => (float) $m->getCostDeficit(),
                    'is_active' => (bool) $m->is_active,
                    'raw_metadata' => $m->raw_metadata,
                ];
            })->values()->all(),
        ];
    })->all();
@endphp

<script>
window.__MARKETPLACE_CATEGORIES__ = @json($marketplaceCategories ?? []);
window.__MARKETPLACE_PRODUCTS__ = @json($productsMap ?? []);

function marketplaceProductManager(config = {}) {
    return {
        mappingModal: false,
        syncModal: false,
        submitting: false,
        publishing: false,
        togglingStatus: false,
        pullingProducts: false,
        syncingAll: false,
        activeMenuId: null,
        toggleMenu(id) {
            const strId = String(id);
            this.activeMenuId = (this.activeMenuId === strId ? null : strId);
        },
        closeMenu() {
            this.activeMenuId = null;
        },
        pollTimer: null,
        init() {
            // Auto-refresh product listing silently every 25s without interrupting user interactions
            this.pollTimer = setInterval(() => {
                if (!this.mappingModal && !this.submitting && !this.publishing && !this.togglingStatus && !this.pullingProducts && !this.syncingAll && !this.activeMenuId) {
                    this.refreshProductsTable();
                }
            }, 25000);
        },
        destroy() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
            }
        },
        async refreshProductsTable() {
            try {
                const res = await fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                if (res.ok) {
                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTbody = doc.getElementById('marketplace-products-tbody');
                    const currentTbody = document.getElementById('marketplace-products-tbody');
                    if (newTbody && currentTbody) {
                        currentTbody.innerHTML = newTbody.innerHTML;
                        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(currentTbody);
                        }
                        if (window.lucide && typeof window.lucide.createIcons === 'function') {
                            window.lucide.createIcons();
                        }
                    }
                }
            } catch (e) {
                console.warn('Silent product refresh error:', e);
            }
        },
        async pullProductsAjax() {
            let confirmed = true;
            if (window.AppAlert && typeof window.AppAlert['confirm'] === 'function') {
                confirmed = await window.AppAlert['confirm'](
                    @json(__('marketplace.confirm_pull_title')),
                    @json(__('marketplace.confirm_pull_desc'))
                );
            }
            if (!confirmed) return;

            this.pullingProducts = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('{{ route('marketplace-hub.products.pull') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({ channel: 'all' })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || data.error || 'Gagal menarik katalog produk dari marketplace.');
                }
                if (window.AppAlert) {
                    AppAlert.toast(data.message, 'success');
                }
                await this.refreshProductsTable();
            } catch (err) {
                if (window.AppAlert) {
                    AppAlert.toast(err.message, 'error');
                } else {
                    alert(err.message);
                }
            } finally {
                this.pullingProducts = false;
            }
        },
        async syncAllAjax() {
            let confirmed = true;
            if (window.AppAlert && typeof window.AppAlert['confirm'] === 'function') {
                confirmed = await window.AppAlert['confirm'](
                    @json(__('marketplace.confirm_sync_title')),
                    @json(__('marketplace.confirm_sync_desc'))
                );
            }
            if (!confirmed) return;

            this.syncingAll = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('{{ route('marketplace-hub.products.sync-all') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    }
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || data.error || 'Gagal sinkronisasi produk massal.');
                }
                if (window.AppAlert) {
                    AppAlert.toast(data.message, 'success');
                }
                await this.refreshProductsTable();
            } catch (err) {
                if (window.AppAlert) {
                    AppAlert.toast(err.message, 'error');
                } else {
                    alert(err.message);
                }
            } finally {
                this.syncingAll = false;
            }
        },
        selectedProduct: null,
        categories: (config && config.categories && config.categories.length)
            ? config.categories
            : (window.__MARKETPLACE_CATEGORIES__ || []),
        productsMap: (() => {
            const map = {};
            const src = (config && config.products && Object.keys(config.products).length)
                ? config.products
                : (window.__MARKETPLACE_PRODUCTS__ || {});
            if (Array.isArray(src)) {
                src.forEach(p => {
                    if (p && p.id !== undefined) {
                        map[String(p.id)] = p;
                    }
                });
            } else if (typeof src === 'object' && src !== null) {
                Object.keys(src).forEach(k => {
                    const p = src[k];
                    if (p && p.id !== undefined) {
                        map[String(p.id)] = p;
                    }
                    map[k] = p;
                });
            }
            return map;
        })(),
        categorySearch: '',
        categoryDropdownOpen: false,
        pricingMode: 'nominal',
        markupPercent: 0,
        stockMode: 'auto',
        form: {
            product_id: '',
            channel: 'shopee',
            category_id: '',
            category_name: '',
            marketplace_item_id: '',
            marketplace_sku: '',
            channel_price: '',
            sync_price_auto: false,
            price_multiplier: 1.0,
            sync_stock_auto: true,
            stock_buffer: 0,
            custom_stock: '',
            is_active: true,
            allow_below_cost: false
        },
        openMapping(productOrId, channel) {
            this.activeMenuId = null;
            let product = (typeof productOrId === 'object' && productOrId !== null)
                ? productOrId
                : (this.productsMap[productOrId] || this.productsMap[String(productOrId)] || null);

            if (!product && this.productsMap) {
                const list = Array.isArray(this.productsMap) ? this.productsMap : Object.values(this.productsMap);
                product = list.find(p => p && (String(p.id) === String(productOrId)));
            }

            if (!product) {
                console.warn('openMapping: Produk tidak ditemukan di data lokal', productOrId);
                return;
            }
            this.selectedProduct = product;
            this.form.product_id = product.id;
            this.form.allow_below_cost = false;
            this.categorySearch = '';
            this.categoryDropdownOpen = false;
            this.switchChannel(channel || 'shopee');
            this.submitting = false;
            this.publishing = false;
            this.mappingModal = true;
        },
        switchChannel(channel) {
            this.form.channel = channel;
            this.form.allow_below_cost = false;
            if (!this.selectedProduct) return;
            const existing = (this.selectedProduct.marketplace_mappings || []).find(m => m.channel === channel);
            if (existing) {
                this.form.marketplace_item_id = existing.marketplace_item_id || existing.external_product_id || '';
                this.form.marketplace_sku = existing.marketplace_sku || existing.external_sku_code || '';
                this.form.category_id = (existing.raw_metadata && existing.raw_metadata.category_id) ? existing.raw_metadata.category_id : '';
                this.form.category_name = (existing.raw_metadata && existing.raw_metadata.category_name) ? existing.raw_metadata.category_name : '';
                if (!this.form.category_id) {
                    this.autoDetectCategory(this.selectedProduct);
                }
                this.form.channel_price = existing.channel_price || '';
                this.form.sync_price_auto = Boolean(existing.sync_price_auto);
                this.form.price_multiplier = existing.price_multiplier || 1.0;
                this.pricingMode = this.form.sync_price_auto ? 'percentage' : 'nominal';
                this.markupPercent = Math.round(((parseFloat(this.form.price_multiplier) || 1.0) - 1.0) * 100);
                this.form.sync_stock_auto = Boolean(existing.sync_stock_auto);
                this.stockMode = this.form.sync_stock_auto ? 'auto' : 'manual';
                this.form.stock_buffer = existing.stock_buffer || 0;
                this.form.custom_stock = existing.custom_stock !== null ? existing.custom_stock : '';
                this.form.is_active = Boolean(existing.is_active);
            } else {
                this.form.marketplace_item_id = '';
                this.form.marketplace_sku = this.selectedProduct.code || '';
                this.autoDetectCategory(this.selectedProduct);
                this.form.channel_price = this.selectedProduct.selling_price || '';
                this.form.sync_price_auto = false;
                this.form.price_multiplier = 1.0;
                this.pricingMode = 'nominal';
                this.markupPercent = 0;
                this.form.sync_stock_auto = true;
                this.stockMode = 'auto';
                this.form.stock_buffer = 0;
                this.form.custom_stock = '';
                this.form.is_active = true;
            }
        },
        autoDetectCategory(product) {
            if (!product || !this.categories || !this.categories.length) return;
            const text = ((product.name || '') + ' ' + (product.category?.name || '') + ' ' + (product.description || '')).toLowerCase();
            let bestMatch = null;
            let maxScore = 0;
            for (const cat of this.categories) {
                let score = 0;
                if (text.includes(cat.name.toLowerCase())) {
                    score += 10;
                }
                if (cat.keywords && Array.isArray(cat.keywords)) {
                    for (const kw of cat.keywords) {
                        if (text.includes(kw.toLowerCase())) {
                            score += 3;
                        }
                    }
                }
                if (score > maxScore) {
                    maxScore = score;
                    bestMatch = cat;
                }
            }
            if (bestMatch && maxScore > 0) {
                this.form.category_id = bestMatch.id;
                this.form.category_name = bestMatch.name;
            } else {
                this.form.category_id = this.categories[0]?.id || '';
                this.form.category_name = this.categories[0]?.name || '';
            }
        },
        get filteredCategories() {
            if (!this.categorySearch || !this.categorySearch.trim()) return this.categories;
            const q = this.categorySearch.toLowerCase().trim();
            return this.categories.filter(c => 
                c.name.toLowerCase().includes(q) || 
                (c.id && c.id.includes(q)) || 
                (c.description && c.description.toLowerCase().includes(q)) ||
                (c.keywords && c.keywords.some(k => k.toLowerCase().includes(q)))
            );
        },
        get selectedCategoryObj() {
            return this.categories.find(c => c.id === this.form.category_id) || null;
        },
        selectCategory(cat) {
            this.form.category_id = cat.id;
            this.form.category_name = cat.name;
            this.categoryDropdownOpen = false;
            this.categorySearch = '';
        },
        setPricingMode(mode) {
            this.pricingMode = mode;
            if (mode === 'percentage') {
                this.form.sync_price_auto = true;
                this.updateMultiplierFromPercent();
            } else {
                this.form.sync_price_auto = false;
                if (!this.form.channel_price || Number(this.form.channel_price) === 0) {
                    this.form.channel_price = this.computedEffectivePrice || this.selectedProduct?.selling_price || 0;
                }
            }
        },
        updateMultiplierFromPercent() {
            const p = parseFloat(this.markupPercent) || 0;
            this.form.price_multiplier = Math.round((1 + (p / 100)) * 1000) / 1000;
        },
        setMarkupPercent(p) {
            this.markupPercent = p;
            this.setPricingMode('percentage');
            this.updateMultiplierFromPercent();
        },
        setNominalPrice(price) {
            this.form.channel_price = Math.max(0, Math.round(price));
            this.setPricingMode('nominal');
        },
        setStockMode(mode) {
            this.stockMode = mode;
            this.form.sync_stock_auto = (mode === 'auto');
        },
        async handleFormSubmit(e) {
            e.preventDefault();
            if (this.isZeroPrice) {
                if (window.AppAlert) {
                    AppAlert.toast(@json(__('marketplace.toast_zero_price')), 'warning');
                } else {
                    alert(@json(__('marketplace.toast_zero_price')));
                }
                return false;
            }
            if (this.isBelowCost && !this.form.allow_below_cost) {
                if (window.AppAlert) {
                    AppAlert.toast(@json(__('marketplace.toast_unlock_risk')), 'warning');
                } else {
                    alert(@json(__('marketplace.toast_unlock_risk')));
                }
                return false;
            }

            this.submitting = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const formEl = e.target;
                const formData = new FormData(formEl);

                const res = await fetch(formEl.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: formData
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || data.error || 'Gagal menyimpan pemetaan SKU.');
                }

                if (window.AppAlert) {
                    AppAlert.toast(data.message || 'Pengaturan SKU & Saluran berhasil disimpan!', 'success');
                }

                // Update in-memory product mapping
                if (this.selectedProduct) {
                    if (!this.selectedProduct.marketplace_mappings) {
                        this.selectedProduct.marketplace_mappings = [];
                    }
                    const existingIdx = this.selectedProduct.marketplace_mappings.findIndex(m => m.channel === this.form.channel);
                    const updatedMapping = {
                        channel: this.form.channel,
                        marketplace_item_id: this.form.marketplace_item_id,
                        channel_price: this.form.channel_price,
                        is_active: this.form.is_active,
                    };
                    if (existingIdx >= 0) {
                        this.selectedProduct.marketplace_mappings[existingIdx] = Object.assign(this.selectedProduct.marketplace_mappings[existingIdx], updatedMapping);
                    } else {
                        this.selectedProduct.marketplace_mappings.push(updatedMapping);
                    }
                }

                this.mappingModal = false;
                await this.refreshProductsTable();
            } catch (err) {
                if (window.AppAlert) {
                    AppAlert.toast(err.message, 'error');
                } else {
                    alert(err.message);
                }
            } finally {
                this.submitting = false;
            }
        },
        async publishToMarketplace() {
            if (!this.selectedProduct) return;
            if (this.isZeroPrice) {
                if (window.AppAlert) {
                    AppAlert.toast(@json(__('marketplace.toast_zero_price_short')), 'warning');
                } else {
                    alert(@json(__('marketplace.toast_zero_price_short')));
                }
                return;
            }
            if (this.isBelowCost && !this.form.allow_below_cost) {
                if (window.AppAlert) {
                    AppAlert.toast(@json(__('marketplace.toast_unlock_risk')), 'warning');
                } else {
                    alert(@json(__('marketplace.toast_unlock_risk')));
                }
                return;
            }

            // Shopee Completeness Validation
            if (this.form.channel === 'shopee') {
                const missing = [];
                if (this.galleryPhotos.length < 2) {
                    missing.push('• Foto Produk: Shopee mewajibkan minimal 2 foto produk (saat ini baru ' + this.galleryPhotos.length + ' foto). Silakan tambahkan foto di galeri produk.');
                }
                const weight = parseFloat(this.selectedProduct.weight || 0);
                if (weight <= 0) {
                    missing.push('• Berat Produk: Wajib diisi (minimal 100 gram / 0.1 kg) untuk ongkir kurir Shopee.');
                }
                if (missing.length > 0) {
                    const msg = 'Data upload ke Shopee belum lengkap:\n' + missing.join('\n');
                    if (window.AppAlert) {
                        AppAlert.toast(msg, 'warning');
                    } else {
                        alert(msg);
                    }
                    return;
                }
            }

            const channelName = this.form.channel === 'tiktok_shop' ? 'TikTok Shop' : (this.form.channel === 'tokopedia' ? 'Tokopedia' : 'Shopee');
            const publishTitleTpl = @json(__('marketplace.confirm_publish_title'));
            const publishDescTpl = @json(__('marketplace.confirm_publish_desc'));
            
            if (window.AppAlert && typeof window.AppAlert['confirm'] === 'function') {
                const confirmed = await window.AppAlert['confirm'](
                    publishTitleTpl.replace(':channel', channelName),
                    publishDescTpl.replace(':channel', channelName).replace(':name', this.selectedProduct.name).replace(':count', this.galleryPhotos.length)
                );
                if (!confirmed) return;
            }

            this.publishing = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const url = `/marketplace-hub/products/${this.selectedProduct.id}/publish`;
                
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        channel: this.form.channel,
                        category_id: this.form.category_id,
                        category_name: this.form.category_name,
                        channel_price: this.form.channel_price,
                        sync_price_auto: this.form.sync_price_auto,
                        price_multiplier: this.form.price_multiplier,
                        custom_stock: this.form.custom_stock,
                        sync_stock_auto: this.form.sync_stock_auto,
                        stock_buffer: this.form.stock_buffer,
                        allow_below_cost: this.form.allow_below_cost
                    })
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.error || data.message || @json(__('marketplace.publish_failed')));
                }

                this.form.marketplace_item_id = data.external_product_id || this.form.marketplace_item_id;
                
                const publishSuccessTpl = @json(__('marketplace.publish_success'));
                if (window.AppAlert) {
                    AppAlert.toast(data.message || publishSuccessTpl.replace(':channel', channelName), 'success');
                }

                // Close modal sheet reactively and update in-memory product mapping
                this.mappingModal = false;
                if (this.selectedProduct) {
                    if (!this.selectedProduct.marketplace_mappings) {
                        this.selectedProduct.marketplace_mappings = [];
                    }
                    const existingIdx = this.selectedProduct.marketplace_mappings.findIndex(m => m.channel === this.form.channel);
                    const updatedMapping = {
                        channel: this.form.channel,
                        marketplace_item_id: this.form.marketplace_item_id,
                        channel_price: this.form.channel_price,
                        is_active: this.form.is_active,
                    };
                    if (existingIdx >= 0) {
                        this.selectedProduct.marketplace_mappings[existingIdx] = Object.assign(this.selectedProduct.marketplace_mappings[existingIdx], updatedMapping);
                    } else {
                        this.selectedProduct.marketplace_mappings.push(updatedMapping);
                    }
                }

                await this.refreshProductsTable();
            } catch (err) {
                if (window.AppAlert) {
                    AppAlert.toast(err.message, 'error');
                } else {
                    alert(err.message);
                }
            } finally {
                this.publishing = false;
            }
        },
        async toggleChannelProductStatus(action) {
            if (!this.selectedProduct || !this.form.marketplace_item_id) return;
            const isDeactivate = action === 'deactivate';
            const actionBadge = isDeactivate ? @json(__('marketplace.btn_delist_modal')) : @json(__('marketplace.activate'));
            const actionText = isDeactivate ? @json(__('marketplace.action_deactivate_text')) : @json(__('marketplace.action_activate_text'));
            const channelName = this.form.channel === 'tiktok_shop' ? 'TikTok Shop' : (this.form.channel === 'tokopedia' ? 'Tokopedia' : 'Shopee');
            const confirmToggleTitleTpl = @json(__('marketplace.confirm_toggle_title'));
            const confirmToggleDescTpl = @json(__('marketplace.confirm_toggle_desc'));
            
            if (window.AppAlert && typeof window.AppAlert['confirm'] === 'function') {
                const confirmed = await window.AppAlert['confirm'](
                    confirmToggleTitleTpl.replace(':action', actionBadge),
                    confirmToggleDescTpl.replace(':action', actionText).replace(':name', this.selectedProduct.name).replace(':channel', channelName)
                );
                if (!confirmed) return;
            }

            this.togglingStatus = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const endpoint = isDeactivate ? 'deactivate' : 'activate';
                const url = `/marketplace-hub/products/${this.selectedProduct.id}/${endpoint}`;

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({
                        channel: this.form.channel
                    })
                });

                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || data.error || @json(__('marketplace.toggle_status_failed')));
                }

                this.form.is_active = !isDeactivate;
                if (this.selectedProduct && this.selectedProduct.marketplace_mappings) {
                    const m = this.selectedProduct.marketplace_mappings.find(x => x.channel === this.form.channel);
                    if (m) {
                        m.is_active = !isDeactivate;
                    }
                }

                if (window.AppAlert) {
                    AppAlert.toast(data.message || @json(__('marketplace.status_updated_success')), 'success');
                }

                await this.refreshProductsTable();
            } catch (err) {
                if (window.AppAlert) {
                    AppAlert.toast(err.message, 'error');
                } else {
                    alert(err.message);
                }
            } finally {
                this.togglingStatus = false;
            }
        },
        async toggleChannelProductDirect(productId, channel, action) {
            this.activeMenuId = null;
            const product = this.productsMap[productId] || this.productsMap[String(productId)] || null;
            const isDeactivate = action === 'deactivate';
            const actionTitle = isDeactivate ? @json(__('marketplace.btn_delist_modal')) : @json(__('marketplace.activate'));
            const actionVerb = isDeactivate ? @json(__('marketplace.action_deactivate_text')) : @json(__('marketplace.action_activate_text'));
            const channelLabel = channel === 'all' 
                ? @json(__('marketplace.channel_all_label'))
                : (channel === 'tiktok_shop' ? 'TikTok Shop' : (channel === 'tokopedia' ? 'Tokopedia' : 'Shopee'));
            const productName = product ? product.name : @json(__('marketplace.th_product'));
            const confirmToggleTitleTpl = @json(__('marketplace.confirm_toggle_title'));
            const confirmToggleDescTpl = @json(__('marketplace.confirm_toggle_desc'));
            const promptTitle = confirmToggleTitleTpl.replace(':action', actionTitle);
            const promptMsg = confirmToggleDescTpl.replace(':action', actionVerb).replace(':name', productName).replace(':channel', channelLabel);

            let confirmed = true;
            if (window.AppAlert && typeof window.AppAlert['confirm'] === 'function') {
                confirmed = await window.AppAlert['confirm'](promptTitle, promptMsg);
            } else {
                confirmed = confirm(promptMsg);
            }

            if (!confirmed) return;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const endpoint = isDeactivate ? 'deactivate' : 'activate';
            const url = `/marketplace-hub/products/${productId}/${endpoint}`;
            const procWord = isDeactivate ? @json(__('marketplace.action_deactivation')) : @json(__('marketplace.action_activation'));

            try {
                const toastProcTpl = @json(__('marketplace.toggle_processing_toast'));
                if (window.AppAlert) {
                    AppAlert.toast(toastProcTpl.replace(':action', procWord).replace(':channel', channelLabel), 'info');
                }

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify({ channel: channel })
                });

                const data = await res.json();
                const toggleFailedTpl = @json(__('marketplace.toggle_failed'));
                if (!res.ok || !data.success) {
                    throw new Error(data.message || data.error || toggleFailedTpl.replace(':action', procWord));
                }

                if (window.AppAlert) {
                    AppAlert.toast(data.message || @json(__('marketplace.listing_status_updated')), 'success');
                }

                await this.refreshProductsTable();
            } catch (err) {
                if (window.AppAlert) {
                    AppAlert.toast(err.message, 'error');
                } else {
                    alert(err.message);
                }
            }
        },
        get categoryDisplayName() {
            return this.selectedCategoryObj ? this.selectedCategoryObj.name : '{{ addslashes(__('marketplace.modal_category_placeholder')) }}';
        },
        get categoryDisplayDesc() {
            return this.selectedCategoryObj ? this.selectedCategoryObj.description : '{{ addslashes(__('marketplace.modal_category_hint')) }}';
        },
        get categoryToggleLabel() {
            return this.categoryDropdownOpen ? '{{ addslashes(__('marketplace.close')) }}' : '{{ addslashes(__('marketplace.modal_change')) }}';
        },
        get pricingModeBadgeText() {
            return this.pricingMode === 'nominal' ? '{{ addslashes(__('marketplace.badge_fixed_price')) }}' : '{{ addslashes(__('marketplace.badge_percentage')) }}';
        },
        get stockModeBadgeText() {
            return this.stockMode === 'auto' ? '{{ addslashes(__('marketplace.badge_auto_warehouse')) }}' : '{{ addslashes(__('marketplace.badge_manual_quota')) }}';
        },
        get listingStatusLabel() {
            return this.form.is_active ? '{{ addslashes(__('marketplace.filter_status_active')) }}' : '{{ addslashes(__('marketplace.delisted_from_channel')) }}';
        },
        get photosCountLabel() {
            return this.galleryPhotos.length + ' {{ addslashes(__('marketplace.photos_unit')) }}';
        },
        get delistButtonText() {
            return this.togglingStatus ? '{{ addslashes(__('marketplace.processing')) }}' : '{{ addslashes(__('marketplace.btn_delist_modal')) }}';
        },
        get relistButtonText() {
            return this.togglingStatus ? '{{ addslashes(__('marketplace.processing')) }}' : '{{ addslashes(__('marketplace.btn_relist_modal')) }}';
        },
        get publishButtonText() {
            if (this.publishing) return '{{ addslashes(__('marketplace.btn_syncing')) }}';
            if (this.isZeroPrice) return '{{ addslashes(__('marketplace.btn_enter_price_first')) }}';
            const prefix = this.form.marketplace_item_id ? '{{ addslashes(__('marketplace.btn_update_listing_at')) }}' : '{{ addslashes(__('marketplace.btn_publish_listing_to')) }}';
            const ch = this.form.channel === 'tiktok_shop' ? 'TikTok Shop' : (this.form.channel === 'tokopedia' ? 'Tokopedia' : 'Shopee');
            return prefix + ch;
        },
        get submitButtonText() {
            if (this.submitting) return '{{ addslashes(__('marketplace.btn_saving_settings')) }}';
            if (this.isZeroPrice) return '{{ addslashes(__('marketplace.btn_enter_channel_price')) }}';
            if (this.isBelowCost && !this.form.allow_below_cost) return '{{ addslashes(__('marketplace.btn_unlock_risk_to_save')) }}';
            return '{{ addslashes(__('marketplace.btn_save_channel_settings')) }}';
        },
        get galleryPhotos() {
            if (!this.selectedProduct) return [];
            const photos = [];
            if (this.selectedProduct.image) {
                photos.push({
                    url: this.selectedProduct.image.startsWith('http') ? this.selectedProduct.image : ('/storage/' + this.selectedProduct.image),
                    caption: @json(__('marketplace.photo_main'))
                });
            }
            if (Array.isArray(this.selectedProduct.gallery_images)) {
                for (const img of this.selectedProduct.gallery_images) {
                    const u = (typeof img === 'string') ? img : (img.image_url || img.path || '');
                    if (u) {
                        photos.push({
                            url: u.startsWith('http') ? u : ('/storage/' + u),
                            caption: img.caption || @json(__('marketplace.photo_gallery'))
                        });
                    }
                }
            } else if (Array.isArray(this.selectedProduct.images)) {
                for (const img of this.selectedProduct.images) {
                    const u = (typeof img === 'string') ? img : (img.image_url || img.path || '');
                    if (u) {
                        photos.push({
                            url: u.startsWith('http') ? u : ('/storage/' + u),
                            caption: img.caption || @json(__('marketplace.photo_gallery'))
                        });
                    }
                }
            }
            return photos;
        },
        get effectiveChannelStock() {
            if (!this.selectedProduct) return 0;
            if (this.stockMode === 'manual') {
                return Math.max(0, parseInt(this.form.custom_stock) || 0);
            }
            const physical = parseInt(this.selectedProduct.stock) || 0;
            const buffer = parseInt(this.form.stock_buffer) || 0;
            return Math.max(0, physical - buffer);
        },
        get isBufferExceedingStock() {
            if (!this.selectedProduct || this.stockMode !== 'auto') return false;
            const physical = parseInt(this.selectedProduct.stock) || 0;
            const buffer = parseInt(this.form.stock_buffer) || 0;
            return buffer > 0 && buffer >= physical;
        },
        get isZeroPrice() {
            return !this.computedEffectivePrice || this.computedEffectivePrice <= 0;
        },
        get nominalDiffVsStore() {
            if (!this.selectedProduct) return { amount: 0, rawDiff: 0, percent: 0, isHigher: true };
            const storePrice = Number(this.selectedProduct.selling_price || 0);
            const channelPrice = Number(this.form.channel_price || 0);
            const diff = channelPrice - storePrice;
            const percent = storePrice > 0 ? Math.round((diff / storePrice) * 100) : 0;
            return {
                amount: Math.abs(diff),
                rawDiff: diff,
                percent: Math.abs(percent),
                isHigher: diff >= 0
            };
        },
        get computedEffectivePrice() {
            if (!this.selectedProduct) return 0;
            if (this.pricingMode === 'percentage' || this.form.sync_price_auto) {
                const mult = parseFloat(this.form.price_multiplier) || 1.0;
                return Math.round(Number(this.selectedProduct.selling_price || 0) * mult);
            }
            return Math.round(parseFloat(this.form.channel_price) || 0);
        },
        get computedAdminFee() {
            return Math.round(this.computedEffectivePrice * 0.08);
        },
        get computedNetReceived() {
            return this.computedEffectivePrice - this.computedAdminFee;
        },
        get computedBaseCost() {
            return Number(this.selectedProduct?.base_cost || 0);
        },
        get computedNetMargin() {
            return this.computedNetReceived - this.computedBaseCost;
        },
        get computedMarginPercent() {
            if (this.computedEffectivePrice <= 0) return 0;
            return Math.round((this.computedNetMargin / this.computedEffectivePrice) * 100);
        },
        get isBelowCost() {
            return this.computedBaseCost > 0 && this.computedEffectivePrice < this.computedBaseCost;
        },
        get costDeficit() {
            return Math.max(0, this.computedBaseCost - this.computedEffectivePrice);
        },
        formatRupiah(val) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
        }
    };
}

// Assign to window for direct Alpine component instantiation
window.marketplaceProductManager = marketplaceProductManager;

// Register with Alpine.data if Alpine is already loaded or on alpine:init
if (typeof Alpine !== 'undefined' && Alpine.data) {
    Alpine.data('marketplaceProductManager', marketplaceProductManager);
}
document.addEventListener('alpine:init', () => {
    if (typeof Alpine !== 'undefined' && Alpine.data) {
        Alpine.data('marketplaceProductManager', marketplaceProductManager);
    }
});
</script>

<div class="max-w-[1360px] mx-auto space-y-6 pb-28 sm:pb-32 lg:pb-12"
    x-data="marketplaceProductManager()">

    {{-- MODULE HEADER & PERSISTENT MARKETPLACE TABS --}}
    <x-module-header
        module="marketplace"
        :title="__('marketplace.products_module_title')"
        :subtitle="__('marketplace.products_module_subtitle')">
        <x-slot:actions>
            {{-- Live Feed Status Indicator --}}
            <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-semibold bg-[#34C759]/10 text-[#34C759] border border-[#34C759]/20 shadow-sm" title="Katalog produk tersinkronisasi live dengan saluran Shopee, TikTok Shop & Tokopedia">
                <span class="w-2 h-2 rounded-full bg-[#34C759] animate-pulse"></span>
                <span>Auto-sync Aktif</span>
            </div>

            {{-- 1-Click Pull / Import Products from Marketplace (AJAX) --}}
            <button type="button" @click="pullProductsAjax()" :disabled="pullingProducts || syncingAll"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#34C759] hover:bg-[#30B753] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer disabled:opacity-60"
                title="{{ __('marketplace.btn_pull_products_title') }}">
                <svg x-show="pullingProducts" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <i x-show="!pullingProducts" data-lucide="download" class="w-4 h-4"></i>
                <span x-text="pullingProducts ? 'Menarik...' : '{{ __('marketplace.btn_pull_products') }}'"></span>
            </button>

            {{-- 1-Click Push / Sync All Price & Stock to Marketplace (AJAX) --}}
            <button type="button" @click="syncAllAjax()" :disabled="syncingAll || pullingProducts"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-semibold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.97] transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer disabled:opacity-60"
                title="{{ __('marketplace.btn_sync_all_title') }}">
                <svg x-show="syncingAll" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <i x-show="!syncingAll" data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span x-text="syncingAll ? 'Sinkronisasi...' : '{{ __('marketplace.btn_sync_all') }}'"></span>
            </button>

            <a href="{{ route('import.index') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-3.5 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer"
                title="{{ __('marketplace.btn_import_excel_title') }}">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span class="hidden md:inline">{{ __('marketplace.btn_import_excel') }}</span>
            </a>

            <a href="{{ route('marketplace-hub.index') }}"
                class="min-h-[44px] sm:min-h-0 sm:h-9 px-4 rounded-[10px] text-[13px] font-medium text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] flex items-center justify-center gap-1.5 cursor-pointer">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>{{ __('marketplace.back') }}</span>
            </a>
        </x-slot:actions>
    </x-module-header>

    <x-module-tabs module="marketplace" />

    <!-- ===================================================== -->
    <!-- 1.1 SECTOR CONTEXT & COMPLIANCE BANNERS               -->
    <!-- ===================================================== -->
    @if($isPharmacy)
        <div class="rounded-[20px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 p-4 sm:p-5 flex items-start gap-3.5 text-[#FF3B30]">
            <div class="w-10 h-10 rounded-[12px] bg-[#FF3B30]/15 flex items-center justify-center shrink-0">
                <i data-lucide="shield-alert" class="w-5 h-5 text-[#FF3B30]"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-[14px] font-bold text-black dark:text-white flex items-center gap-2 flex-wrap">
                    <span>{{ __('marketplace.guardrail_bpom_title') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FF3B30] text-white">{{ __('marketplace.guardrail_bpom_badge') }}</span>
                </h4>
                <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    {!! __('marketplace.guardrail_bpom_desc') !!}
                </p>
            </div>
        </div>
    @endif

    @if($isServiceSector)
        <div class="rounded-[20px] bg-[#007AFF]/10 border border-[#007AFF]/20 p-4 sm:p-5 flex items-start gap-3.5 text-[#007AFF]">
            <div class="w-10 h-10 rounded-[12px] bg-[#007AFF]/15 flex items-center justify-center shrink-0">
                <i data-lucide="wrench" class="w-5 h-5 text-[#007AFF]"></i>
            </div>
            <div class="space-y-1">
                <h4 class="text-[14px] font-bold text-black dark:text-white flex items-center gap-2 flex-wrap">
                    <span>{{ __('marketplace.guardrail_service_title') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#007AFF] text-white">{{ __('marketplace.guardrail_service_badge') }}</span>
                </h4>
                <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed">
                    {!! __('marketplace.guardrail_service_desc') !!}
                </p>
            </div>
        </div>
    @endif

    {{-- SELLER CENTER ONBOARDING & REVERSE SYNC BANNER --}}
    <div x-data="{ expanded: false }" class="rounded-[20px] bg-gradient-to-r from-[#007AFF]/5 via-[#34C759]/5 to-transparent border border-black/5 dark:border-white/10 p-4 sm:p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-[12px] bg-[#34C759]/15 flex items-center justify-center shrink-0">
                    <i data-lucide="store" class="w-5 h-5 text-[#34C759]"></i>
                </div>
                <div>
                    <h4 class="text-[14px] font-bold text-black dark:text-white flex items-center gap-2 flex-wrap">
                        <span>{{ __('marketplace.reverse_sync_title') }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#34C759] text-white">{{ __('marketplace.reverse_sync_badge') }}</span>
                    </h4>
                    <p class="text-[12.5px] text-black/70 dark:text-white/70 leading-relaxed mt-0.5">
                        {{ __('marketplace.reverse_sync_desc') }}
                    </p>
                </div>
            </div>
            <button @click="expanded = !expanded" type="button" class="text-[12px] font-semibold text-[#007AFF] hover:underline shrink-0 flex items-center gap-1 cursor-pointer">
                <span x-text="expanded ? '{{ __('marketplace.btn_close_pull_guide') }}' : '{{ __('marketplace.btn_view_pull_guide') }}'"></span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 transition-transform" :class="expanded ? 'rotate-180' : ''"></i>
            </button>
        </div>

        <div x-show="expanded" x-collapse class="mt-4 pt-4 border-t border-black/5 dark:border-white/10 grid grid-cols-1 md:grid-cols-2 gap-4 text-[12.5px]">
            <div class="p-3.5 rounded-[14px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-black dark:text-white">
                    <span class="w-5 h-5 rounded-full bg-[#34C759] text-white text-[11px] flex items-center justify-center">1</span>
                    <span>{{ __('marketplace.pull_guide_option_1_title') }}</span>
                </div>
                <p class="text-black/60 dark:text-white/60">
                    {!! __('marketplace.pull_guide_option_1_desc') !!}
                </p>
            </div>
            <div class="p-3.5 rounded-[14px] bg-white/60 dark:bg-black/20 border border-black/5 dark:border-white/5 space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-black dark:text-white">
                    <span class="w-5 h-5 rounded-full bg-[#007AFF] text-white text-[11px] flex items-center justify-center">2</span>
                    <span>{{ __('marketplace.pull_guide_option_2_title') }}</span>
                </div>
                <p class="text-black/60 dark:text-white/60">
                    {!! __('marketplace.pull_guide_option_2_desc', ['url' => route('import.index')]) !!}
                </p>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 2. SEARCH & MULTI-CHANNEL FILTER BAR                  -->
    <!-- ===================================================== -->
    <div class="rounded-[20px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/5 dark:border-white/10 p-3.5 sm:p-4 backdrop-blur-md space-y-3 shadow-xs">
        <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <form method="GET" action="{{ route('marketplace-hub.products') }}" class="flex-1 flex items-center gap-2">
                <input type="hidden" name="type" value="{{ request('type', 'all') }}">
                <input type="hidden" name="channel" value="{{ request('channel', 'all') }}">
                <input type="hidden" name="risk" value="{{ request('risk', 'all') }}">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                <div class="relative flex-1">
                    <i data-lucide="search" class="w-4 h-4 text-black/40 dark:text-white/40 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="{{ __('marketplace.search_products_placeholder') }}"
                        class="w-full h-10 pl-9 pr-4 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[13px] text-black dark:text-white placeholder:text-black/40 dark:placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                </div>
                <button type="submit"
                    class="h-10 px-4 rounded-[12px] text-[13px] font-bold text-black dark:text-white bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] cursor-pointer transition-colors">
                    {{ __('marketplace.search') }}
                </button>
                @if(request()->filled('q') || request('channel', 'all') !== 'all' || request('type', 'all') !== 'all' || request('risk', 'all') !== 'all' || request('status', 'all') !== 'all')
                    <a href="{{ route('marketplace-hub.products') }}" class="h-10 px-3 rounded-[12px] text-[12.5px] font-medium text-black/50 hover:text-black dark:hover:text-white flex items-center transition-colors">
                        {{ __('marketplace.reset_filter') }}
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2 self-start md:self-auto text-[12px] text-black/60 dark:text-white/60">
                <span class="font-medium">{!! __('marketplace.total_products_count', ['count' => $products->total()]) !!}</span>
            </div>
        </div>

        <!-- Filter Segmented Tabs (Channel + Status + Type + Risk) -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 pt-2 border-t border-black/[0.04] dark:border-white/[0.06]">
            <!-- 1. Channel Filter Pills -->
            <div class="flex items-center gap-1 p-1 rounded-[13px] bg-black/[0.04] dark:bg-white/[0.06] flex-wrap">
                <span class="text-[11px] font-bold uppercase tracking-wider text-black/40 dark:text-white/40 px-2">{{ __('marketplace.filter_channel_label') }}</span>
                <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['channel' => 'all', 'page' => 1])) }}"
                    class="px-3 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all {{ request('channel', 'all') === 'all' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-black dark:text-white font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    {{ __('marketplace.filter_channel_all') }}
                </a>
                <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['channel' => 'tiktok_shop', 'page' => 1])) }}"
                    class="px-3 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all flex items-center gap-1.5 {{ request('channel') === 'tiktok_shop' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-black dark:text-white font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-black dark:bg-white"></span>
                    <span>TikTok Shop</span>
                </a>
                <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['channel' => 'shopee', 'page' => 1])) }}"
                    class="px-3 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all flex items-center gap-1.5 {{ request('channel') === 'shopee' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-[#EE4D2D] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#EE4D2D]"></span>
                    <span>Shopee</span>
                </a>
                <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['channel' => 'tokopedia', 'page' => 1])) }}"
                    class="px-3 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all flex items-center gap-1.5 {{ request('channel') === 'tokopedia' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-[#00AA5B] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#00AA5B]"></span>
                    <span>Tokopedia</span>
                </a>
                <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['channel' => 'unmapped', 'page' => 1])) }}"
                    class="px-3 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all {{ request('channel') === 'unmapped' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-[#007AFF] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                    {{ __('marketplace.filter_channel_unmapped') }}
                </a>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <!-- 2. Status Filter Pills (Aktif vs Nonaktif di Etalase) -->
                <div class="flex items-center gap-1 p-1 rounded-[13px] bg-black/[0.04] dark:bg-white/[0.06]">
                    <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['status' => 'all', 'page' => 1])) }}"
                        class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all {{ request('status', 'all') === 'all' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-black dark:text-white font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('marketplace.filter_status_all') }}
                    </a>
                    <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['status' => 'active', 'page' => 1])) }}"
                        class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all flex items-center gap-1 {{ request('status') === 'active' ? 'bg-[#34C759] text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request('status') === 'active' ? 'bg-white' : 'bg-[#34C759]' }}"></span>
                        <span>{{ __('marketplace.filter_status_active') }}</span>
                    </a>
                    <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['status' => 'inactive', 'page' => 1])) }}"
                        class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all flex items-center gap-1 {{ request('status') === 'inactive' ? 'bg-[#FF9500] text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        <i data-lucide="eye-off" class="w-3 h-3"></i>
                        <span>{{ __('marketplace.filter_status_inactive') }}</span>
                    </a>
                </div>

                <!-- 3. Type Filter Pills -->
                <div class="flex items-center gap-1 p-1 rounded-[13px] bg-black/[0.04] dark:bg-white/[0.06]">
                    <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['type' => 'all', 'page' => 1])) }}"
                        class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all {{ request('type', 'all') === 'all' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-black dark:text-white font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('marketplace.filter_type_all') }}
                    </a>
                    <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['type' => 'goods', 'page' => 1])) }}"
                        class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all {{ request('type', 'all') === 'goods' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-[#007AFF] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('marketplace.filter_type_goods') }}
                    </a>
                    <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['type' => 'service', 'page' => 1])) }}"
                        class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all {{ request('type', 'all') === 'service' ? 'bg-white dark:bg-[#2C2C2E] shadow-xs text-[#FF9500] font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}">
                        {{ __('marketplace.filter_type_service') }}
                    </a>
                </div>

                <!-- 4. Risk Alert Pill -->
                <div class="flex items-center gap-1 p-1 rounded-[13px] bg-black/[0.04] dark:bg-white/[0.06]">
                    <a href="{{ route('marketplace-hub.products', array_merge(request()->query(), ['risk' => request('risk') === 'out_of_stock' ? 'all' : 'out_of_stock', 'page' => 1])) }}"
                        class="px-2.5 py-1 rounded-[8px] text-[11.5px] font-semibold transition-all flex items-center gap-1 {{ request('risk') === 'out_of_stock' ? 'bg-[#FF3B30] text-white shadow-xs font-bold' : 'text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white' }}"
                        title="{{ __('marketplace.filter_risk_out_of_stock_title') }}">
                        <i data-lucide="package-x" class="w-3.5 h-3.5"></i>
                        <span>{{ __('marketplace.filter_risk_out_of_stock') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================== -->
    <!-- 3. BENTO PRODUCTS & MULTI-PRICING TABLE               -->
    <!-- ===================================================== -->
    <div class="rounded-[24px] bg-white/80 dark:bg-[#1C1C1E]/80 border border-black/[0.06] dark:border-white/[0.08] backdrop-blur-md overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-[13px] border-collapse min-w-[920px]">
                <thead>
                    <tr class="border-b border-black/[0.06] dark:border-white/[0.08] bg-black/[0.02] dark:bg-white/[0.02] text-black/60 dark:text-white/60 font-semibold text-[11px] uppercase tracking-wider">
                        <th class="py-3 px-4 sm:px-6 w-[36%]">{{ __('marketplace.th_product') }}</th>
                        <th class="py-3 px-3 text-right w-[13%]">{{ __('marketplace.th_store_price') }}</th>
                        <th class="py-3 px-3 text-center w-[14%]">
                            <span class="inline-flex items-center gap-1.5 text-[#EE4D2D]">
                                <span class="w-2 h-2 rounded-full bg-[#EE4D2D]"></span>
                                <span>Shopee</span>
                            </span>
                        </th>
                        <th class="py-3 px-3 text-center w-[14%]">
                            <span class="inline-flex items-center gap-1.5 text-black dark:text-white">
                                <span class="w-2 h-2 rounded-full bg-black dark:bg-white"></span>
                                <span>TikTok Shop</span>
                            </span>
                        </th>
                        <th class="py-3 px-3 text-center w-[14%]">
                            <span class="inline-flex items-center gap-1.5 text-[#00AA5B]">
                                <span class="w-2 h-2 rounded-full bg-[#00AA5B]"></span>
                                <span>Tokopedia</span>
                            </span>
                        </th>
                        <th class="py-3 px-4 sm:px-6 text-right whitespace-nowrap sticky right-0 bg-[#F9F9FA] dark:bg-[#1E1E20] z-10 border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)]">{{ __('marketplace.th_action') }}</th>
                    </tr>
                </thead>
                <tbody id="marketplace-products-tbody" class="divide-y divide-black/[0.04] dark:divide-white/[0.06]">
                    @forelse($products as $product)
                        @php
                            $shopeeMapping = $product->marketplaceMappings->firstWhere('channel', 'shopee');
                            $tiktokMapping = $product->marketplaceMappings->firstWhere('channel', 'tiktok_shop');
                            $tokpedMapping = $product->marketplaceMappings->firstWhere('channel', 'tokopedia');

                            $hasAnyMapping = ($shopeeMapping && ! empty($shopeeMapping->external_product_id))
                                || ($tiktokMapping && ! empty($tiktokMapping->external_product_id))
                                || ($tokpedMapping && ! empty($tokpedMapping->external_product_id));

                            $hasActiveMapping = ($shopeeMapping && ! empty($shopeeMapping->external_product_id) && $shopeeMapping->is_active)
                                || ($tiktokMapping && ! empty($tiktokMapping->external_product_id) && $tiktokMapping->is_active)
                                || ($tokpedMapping && ! empty($tokpedMapping->external_product_id) && $tokpedMapping->is_active);

                            $allMappingsInactive = $hasAnyMapping && ! $hasActiveMapping;

                            $isRestrictedDrug = $isPharmacy && $product->isRestrictedPharmacyProduct();
                            $isServiceItem    = $product->isService();
                            $thumbUrl         = $product->image_url ?? $product->images->first()?->image_url;
                        @endphp
                        <tr class="hover:bg-black/[0.015] dark:hover:bg-white/[0.015] transition-colors group">
                            <!-- Column 1: Product Name, Thumbnail, SKU & Stock -->
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <!-- Thumbnail -->
                                    <div class="w-10 h-10 rounded-[10px] shrink-0 bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] overflow-hidden flex items-center justify-center">
                                        @if($thumbUrl)
                                            <img src="{{ $thumbUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover" loading="lazy">
                                        @else
                                            <i data-lucide="package" class="w-4 h-4 text-black/30 dark:text-white/30"></i>
                                        @endif
                                    </div>

                                    <!-- Details -->
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <button type="button" @click="openMapping('{{ $product->id }}', 'shopee')"
                                                class="font-semibold text-black dark:text-white text-[13px] hover:text-[#007AFF] transition-colors text-left truncate max-w-[260px] sm:max-w-[340px]"
                                                title="{{ $product->name }}">
                                                {{ $product->name }}
                                            </button>

                                            @if(! $product->is_active)
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-[#8E8E93]/15 text-[#8E8E93]" title="{{ __('marketplace.badge_inactive_cooca_title') }}">
                                                    <i data-lucide="pause-circle" class="w-2.5 h-2.5"></i>
                                                    <span>{{ __('marketplace.badge_inactive_cooca') }}</span>
                                                </span>
                                            @endif
                                            @if($isRestrictedDrug)
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-[#FF3B30]/15 text-[#FF3B30]" title="{{ __('marketplace.badge_bpom_title') }}">
                                                    <i data-lucide="shield-alert" class="w-2.5 h-2.5"></i>
                                                    <span>{{ __('marketplace.badge_bpom') }}</span>
                                                </span>
                                            @elseif($isServiceItem)
                                                <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-[#FF9500]/15 text-[#FF9500]">
                                                    <span>{{ __('marketplace.badge_service') }}</span>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-1.5 text-[11px] text-black/45 dark:text-white/45 mt-0.5 flex-wrap">
                                            <span class="font-mono bg-black/[0.03] dark:bg-white/[0.06] px-1.5 py-0.2 rounded text-[10.5px] text-black/60 dark:text-white/60">
                                                {{ $product->code ?? 'NO-SKU' }}
                                            </span>
                                            <span>•</span>
                                            @if($isServiceItem)
                                                <span class="text-[#FF9500]">{{ __('marketplace.badge_service_cashier') }}</span>
                                            @else
                                                @if(($product->stock ?? 0) <= 0)
                                                    <span class="inline-flex items-center gap-0.5 text-[#FF3B30] font-semibold">
                                                        <i data-lucide="alert-circle" class="w-3 h-3"></i>
                                                        <span>{{ __('marketplace.badge_out_of_stock') }}</span>
                                                    </span>
                                                @else
                                                    <span>{{ __('marketplace.stock_label') }} <strong class="text-black/80 dark:text-white/80 font-mono">{{ $product->stock ?? 0 }}</strong> {{ $product->unit ?? 'pcs' }}</span>
                                                @endif
                                            @endif
                                            @if($product->category)
                                                <span>•</span>
                                                <span class="truncate max-w-[120px]">{{ $product->category->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Column 2: COOCA Base Price -->
                            <td class="py-3.5 px-3 text-right">
                                <div class="font-bold font-mono text-black dark:text-white text-[13px] tracking-tight">
                                    Rp {{ number_format((float)$product->selling_price, 0, ',', '.') }}
                                </div>
                                @if($product->base_cost > 0)
                                    <div class="text-[10.5px] font-mono text-black/40 dark:text-white/40 mt-0.5" title="Harga Pokok Penjualan (HPP)">
                                        {{ __('marketplace.base_cost_hpp', ['cost' => number_format((float)$product->base_cost, 0, ',', '.')]) }}
                                    </div>
                                @else
                                    <div class="text-[10px] text-black/30 dark:text-white/30 mt-0.5">{{ __('marketplace.main_store') }}</div>
                                @endif
                            </td>

                            <!-- Column 3: Shopee Price & Config -->
                            <td class="py-3.5 px-3 text-center">
                                @if($isRestrictedDrug)
                                    <div class="flex justify-center">
                                        <span class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 inline-flex items-center gap-1 cursor-not-allowed" title="{{ __('marketplace.restricted_drug_warning') }}">
                                            <i data-lucide="lock" class="w-3 h-3"></i>
                                            <span>{{ __('marketplace.status_locked') }}</span>
                                        </span>
                                    </div>
                                @elseif($isServiceItem)
                                    <div class="flex justify-center">
                                        <span class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-black/40 dark:text-white/40 bg-black/[0.04] dark:bg-white/[0.06] inline-flex items-center gap-1 cursor-not-allowed">
                                            <i data-lucide="ban" class="w-3 h-3"></i>
                                            <span>{{ __('marketplace.offline_service') }}</span>
                                        </span>
                                    </div>
                                @elseif($shopeeMapping && ! empty($shopeeMapping->external_product_id))
                                    @if($shopeeMapping->is_active)
                                        <div class="flex flex-col items-center">
                                            <button type="button" @click="openMapping('{{ $product->id }}', 'shopee')"
                                                class="font-bold font-mono text-[12.5px] text-[#EE4D2D] hover:underline cursor-pointer tracking-tight"
                                                title="Klik untuk ubah harga & aturan saluran">
                                                Rp {{ number_format((float)$shopeeMapping->getEffectivePrice(), 0, ',', '.') }}
                                            </button>
                                            <div class="flex items-center gap-1 text-[10.5px] text-black/45 dark:text-white/45 mt-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span class="font-mono">{{ __('marketplace.stock_label') }} {{ $shopeeMapping->getEffectiveStock($product->stock ?? 0) }}</span>
                                                @if($shopeeMapping->isBelowCost())
                                                    <span class="text-[#FF3B30]" title="Harga di bawah HPP!"><i data-lucide="alert-triangle" class="w-3 h-3"></i></span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <button type="button" @click="openMapping('{{ $product->id }}', 'shopee')"
                                                    class="text-[10px] text-black/40 dark:text-white/40 hover:text-[#007AFF] transition-colors cursor-pointer">
                                                    {{ __('marketplace.edit') }}
                                                </button>
                                                <span class="text-black/15 dark:text-white/15 text-[8px]">•</span>
                                                <button type="button" @click="toggleChannelProductDirect('{{ $product->id }}', 'shopee', 'deactivate')"
                                                    class="text-[10px] text-black/35 dark:text-white/35 hover:text-[#FF3B30] transition-colors cursor-pointer"
                                                    title="Tarik produk dari etalase Shopee">
                                                    {{ __('marketplace.deactivate') }}
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <div class="text-[11.5px] font-mono text-black/35 dark:text-white/35 line-through">
                                                Rp {{ number_format((float)$shopeeMapping->getEffectivePrice(), 0, ',', '.') }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <button type="button" @click="toggleChannelProductDirect('{{ $product->id }}', 'shopee', 'activate')"
                                                    class="h-5 px-1.5 rounded-[5px] text-[10px] font-bold text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20 transition-all cursor-pointer flex items-center gap-0.5"
                                                    title="Aktifkan kembali ke etalase Shopee">
                                                    <i data-lucide="eye" class="w-2.5 h-2.5"></i>
                                                    <span>{{ __('marketplace.activate') }}</span>
                                                </button>
                                                <button type="button" @click="openMapping('{{ $product->id }}', 'shopee')"
                                                    class="text-[10px] text-black/40 dark:text-white/40 hover:text-[#007AFF] cursor-pointer">
                                                    {{ __('marketplace.edit') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="flex justify-center">
                                        <button type="button" @click="openMapping('{{ $product->id }}', 'shopee')"
                                            class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-black/45 dark:text-white/45 bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] hover:text-black dark:hover:text-white transition-all inline-flex items-center gap-1 cursor-pointer"
                                            title="Hubungkan atau Terbitkan ke Shopee">
                                            <i data-lucide="plus" class="w-3 h-3 opacity-60"></i>
                                            <span>{{ __('marketplace.connect') }}</span>
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <!-- Column 4: TikTok Shop Price & Config -->
                            <td class="py-3.5 px-3 text-center">
                                @if($isRestrictedDrug)
                                    <div class="flex justify-center">
                                        <span class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 inline-flex items-center gap-1 cursor-not-allowed" title="{{ __('marketplace.restricted_drug_warning') }}">
                                            <i data-lucide="lock" class="w-3 h-3"></i>
                                            <span>{{ __('marketplace.status_locked') }}</span>
                                        </span>
                                    </div>
                                @elseif($isServiceItem)
                                    <div class="flex justify-center">
                                        <span class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-black/40 dark:text-white/40 bg-black/[0.04] dark:bg-white/[0.06] inline-flex items-center gap-1 cursor-not-allowed">
                                            <i data-lucide="ban" class="w-3 h-3"></i>
                                            <span>{{ __('marketplace.offline_service') }}</span>
                                        </span>
                                    </div>
                                @elseif($tiktokMapping && ! empty($tiktokMapping->external_product_id))
                                    @if($tiktokMapping->is_active)
                                        <div class="flex flex-col items-center">
                                            <button type="button" @click="openMapping('{{ $product->id }}', 'tiktok_shop')"
                                                class="font-bold font-mono text-[12.5px] text-black dark:text-white hover:underline cursor-pointer tracking-tight"
                                                title="Klik untuk ubah harga & aturan saluran">
                                                Rp {{ number_format((float)$tiktokMapping->getEffectivePrice(), 0, ',', '.') }}
                                            </button>
                                            <div class="flex items-center gap-1 text-[10.5px] text-black/45 dark:text-white/45 mt-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span class="font-mono">{{ __('marketplace.stock_label') }} {{ $tiktokMapping->getEffectiveStock($product->stock ?? 0) }}</span>
                                                @if($tiktokMapping->isBelowCost())
                                                    <span class="text-[#FF3B30]" title="Harga di bawah HPP!"><i data-lucide="alert-triangle" class="w-3 h-3"></i></span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <button type="button" @click="openMapping('{{ $product->id }}', 'tiktok_shop')"
                                                    class="text-[10px] text-black/40 dark:text-white/40 hover:text-[#007AFF] transition-colors cursor-pointer">
                                                    {{ __('marketplace.edit') }}
                                                </button>
                                                <span class="text-black/15 dark:text-white/15 text-[8px]">•</span>
                                                <button type="button" @click="toggleChannelProductDirect('{{ $product->id }}', 'tiktok_shop', 'deactivate')"
                                                    class="text-[10px] text-black/35 dark:text-white/35 hover:text-[#FF3B30] transition-colors cursor-pointer"
                                                    title="Tarik produk dari etalase TikTok Shop">
                                                    {{ __('marketplace.deactivate') }}
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <div class="text-[11.5px] font-mono text-black/35 dark:text-white/35 line-through">
                                                Rp {{ number_format((float)$tiktokMapping->getEffectivePrice(), 0, ',', '.') }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <button type="button" @click="toggleChannelProductDirect('{{ $product->id }}', 'tiktok_shop', 'activate')"
                                                    class="h-5 px-1.5 rounded-[5px] text-[10px] font-bold text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20 transition-all cursor-pointer flex items-center gap-0.5"
                                                    title="Aktifkan kembali ke etalase TikTok Shop">
                                                    <i data-lucide="eye" class="w-2.5 h-2.5"></i>
                                                    <span>{{ __('marketplace.activate') }}</span>
                                                </button>
                                                <button type="button" @click="openMapping('{{ $product->id }}', 'tiktok_shop')"
                                                    class="text-[10px] text-black/40 dark:text-white/40 hover:text-[#007AFF] cursor-pointer">
                                                    {{ __('marketplace.edit') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="flex justify-center">
                                        <button type="button" @click="openMapping('{{ $product->id }}', 'tiktok_shop')"
                                            class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-black/45 dark:text-white/45 bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] hover:text-black dark:hover:text-white transition-all inline-flex items-center gap-1 cursor-pointer"
                                            title="Hubungkan atau Terbitkan ke TikTok Shop">
                                            <i data-lucide="plus" class="w-3 h-3 opacity-60"></i>
                                            <span>{{ __('marketplace.connect') }}</span>
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <!-- Column 5: Tokopedia Price & Config -->
                            <td class="py-3.5 px-3 text-center">
                                @if($isRestrictedDrug)
                                    <div class="flex justify-center">
                                        <span class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-[#FF3B30] bg-[#FF3B30]/10 inline-flex items-center gap-1 cursor-not-allowed" title="{{ __('marketplace.restricted_drug_warning') }}">
                                            <i data-lucide="lock" class="w-3 h-3"></i>
                                            <span>{{ __('marketplace.status_locked') }}</span>
                                        </span>
                                    </div>
                                @elseif($isServiceItem)
                                    <div class="flex justify-center">
                                        <span class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-black/40 dark:text-white/40 bg-black/[0.04] dark:bg-white/[0.06] inline-flex items-center gap-1 cursor-not-allowed">
                                            <i data-lucide="ban" class="w-3 h-3"></i>
                                            <span>{{ __('marketplace.offline_service') }}</span>
                                        </span>
                                    </div>
                                @elseif($tokpedMapping && ! empty($tokpedMapping->external_product_id))
                                    @if($tokpedMapping->is_active)
                                        <div class="flex flex-col items-center">
                                            <button type="button" @click="openMapping('{{ $product->id }}', 'tokopedia')"
                                                class="font-bold font-mono text-[12.5px] text-[#00AA5B] hover:underline cursor-pointer tracking-tight"
                                                title="Klik untuk ubah harga & aturan saluran">
                                                Rp {{ number_format((float)$tokpedMapping->getEffectivePrice(), 0, ',', '.') }}
                                            </button>
                                            <div class="flex items-center gap-1 text-[10.5px] text-black/45 dark:text-white/45 mt-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#34C759]"></span>
                                                <span class="font-mono">{{ __('marketplace.stock_label') }} {{ $tokpedMapping->getEffectiveStock($product->stock ?? 0) }}</span>
                                                @if($tokpedMapping->isBelowCost())
                                                    <span class="text-[#FF3B30]" title="Harga di bawah HPP!"><i data-lucide="alert-triangle" class="w-3 h-3"></i></span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <button type="button" @click="openMapping('{{ $product->id }}', 'tokopedia')"
                                                    class="text-[10px] text-black/40 dark:text-white/40 hover:text-[#007AFF] transition-colors cursor-pointer">
                                                    {{ __('marketplace.edit') }}
                                                </button>
                                                <span class="text-black/15 dark:text-white/15 text-[8px]">•</span>
                                                <button type="button" @click="toggleChannelProductDirect('{{ $product->id }}', 'tokopedia', 'deactivate')"
                                                    class="text-[10px] text-black/35 dark:text-white/35 hover:text-[#FF3B30] transition-colors cursor-pointer"
                                                    title="Tarik produk dari etalase Tokopedia">
                                                    {{ __('marketplace.deactivate') }}
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center">
                                            <div class="text-[11.5px] font-mono text-black/35 dark:text-white/35 line-through">
                                                Rp {{ number_format((float)$tokpedMapping->getEffectivePrice(), 0, ',', '.') }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-1">
                                                <button type="button" @click="toggleChannelProductDirect('{{ $product->id }}', 'tokopedia', 'activate')"
                                                    class="h-5 px-1.5 rounded-[5px] text-[10px] font-bold text-[#34C759] bg-[#34C759]/10 hover:bg-[#34C759]/20 transition-all cursor-pointer flex items-center gap-0.5"
                                                    title="Aktifkan kembali ke etalase Tokopedia">
                                                    <i data-lucide="eye" class="w-2.5 h-2.5"></i>
                                                    <span>{{ __('marketplace.activate') }}</span>
                                                </button>
                                                <button type="button" @click="openMapping('{{ $product->id }}', 'tokopedia')"
                                                    class="text-[10px] text-black/40 dark:text-white/40 hover:text-[#007AFF] cursor-pointer">
                                                    {{ __('marketplace.edit') }}
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="flex justify-center">
                                        <button type="button" @click="openMapping('{{ $product->id }}', 'tokopedia')"
                                            class="h-6 px-2 rounded-[6px] text-[10.5px] font-medium text-black/45 dark:text-white/45 bg-black/[0.03] dark:bg-white/[0.05] hover:bg-black/[0.06] hover:text-black dark:hover:text-white transition-all inline-flex items-center gap-1 cursor-pointer"
                                            title="Hubungkan atau Terbitkan ke Tokopedia">
                                            <i data-lucide="plus" class="w-3 h-3 opacity-60"></i>
                                            <span>{{ __('marketplace.connect') }}</span>
                                        </button>
                                    </div>
                                @endif
                            </td>

                            <!-- Column 6: Sync & Management Actions -->
                            <td class="py-3.5 px-4 sm:px-6 text-right whitespace-nowrap sticky right-0 bg-white dark:bg-[#1C1C1E] group-hover:bg-[#F9F9FB] dark:group-hover:bg-[#252528] border-l border-black/[0.04] dark:border-white/[0.06] shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.05)] transition-all"
                                :class="activeMenuId === '{{ $product->id }}' ? '!z-30' : 'z-10'">
                                @if($isRestrictedDrug || $isServiceItem)
                                    <span class="text-[11px] text-black/35 dark:text-white/35 italic font-medium">
                                        {{ $isRestrictedDrug ? __('marketplace.status_restricted') : __('marketplace.offline_service') }}
                                    </span>
                                @else
                                    <div class="flex items-center justify-end gap-1 relative">
                                        <button type="button" @click="openMapping('{{ $product->id }}', 'shopee')"
                                            class="w-8 h-8 rounded-[8px] flex items-center justify-center text-[#007AFF] hover:bg-[#007AFF]/10 dark:hover:bg-[#007AFF]/20 transition-all cursor-pointer shrink-0"
                                            title="{{ __('marketplace.manage') }}"
                                            aria-label="{{ __('marketplace.manage') }}">
                                            <i data-lucide="sliders" class="w-4 h-4"></i>
                                        </button>

                                        @if($hasAnyMapping)
                                            <button type="button" @click.stop="toggleMenu('{{ $product->id }}')"
                                                class="w-8 h-8 rounded-[8px] flex items-center justify-center text-black/50 dark:text-white/50 hover:bg-black/[0.06] dark:hover:bg-white/[0.08] transition-colors cursor-pointer shrink-0"
                                                title="{{ __('marketplace.menu_additional_actions') }}"
                                                aria-label="{{ __('marketplace.menu_additional_actions') }}">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Dropdown Menu (Clean, no overlap, full label visibility) -->
                                            <div x-show="activeMenuId === '{{ $product->id }}'" x-cloak
                                                @click.outside="if (activeMenuId === '{{ $product->id }}') activeMenuId = null"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                x-transition:leave="transition ease-in duration-75"
                                                x-transition:leave-start="transform opacity-100 scale-100"
                                                x-transition:leave-end="transform opacity-0 scale-95"
                                                class="absolute right-0 {{ $loop->index >= (count($products) - 3) && count($products) > 3 ? 'bottom-full mb-1.5' : 'top-full mt-1.5' }} z-50 min-w-[220px] whitespace-nowrap rounded-[14px] bg-white dark:bg-[#2C2C2E] border border-black/[0.1] dark:border-white/[0.12] shadow-2xl p-1 text-[12px] divide-y divide-black/[0.04] dark:divide-white/[0.06] text-left">
                                                <div class="py-1">
                                                    <form method="POST" action="{{ route('marketplace-hub.products.sync-price', $product->id) }}">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] rounded-[8px] flex items-center gap-2.5 text-black/80 dark:text-white/80 transition-colors cursor-pointer">
                                                            <i data-lucide="dollar-sign" class="w-3.5 h-3.5 text-[#007AFF] shrink-0"></i>
                                                            <span>{{ __('marketplace.push_price_to_store') }}</span>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('marketplace-hub.products.sync-stock', $product->id) }}">
                                                        @csrf
                                                        <button type="submit" class="w-full text-left px-3.5 py-2 hover:bg-black/[0.04] dark:hover:bg-white/[0.06] rounded-[8px] flex items-center gap-2.5 text-black/80 dark:text-white/80 transition-colors cursor-pointer">
                                                            <i data-lucide="box" class="w-3.5 h-3.5 text-[#34C759] shrink-0"></i>
                                                            <span>{{ __('marketplace.push_stock_to_store') }}</span>
                                                        </button>
                                                    </form>
                                                </div>

                                                <div class="py-1">
                                                    @if($hasActiveMapping)
                                                        <button type="button" @click="activeMenuId = null; toggleChannelProductDirect('{{ $product->id }}', 'all', 'deactivate')"
                                                            class="w-full text-left px-3.5 py-2 hover:bg-[#FF3B30]/10 rounded-[8px] flex items-center gap-2.5 text-[#FF3B30] transition-colors cursor-pointer">
                                                            <i data-lucide="eye-off" class="w-3.5 h-3.5 shrink-0"></i>
                                                            <span>{{ __('marketplace.delist_all_stores') }}</span>
                                                        </button>
                                                    @elseif($allMappingsInactive)
                                                        <button type="button" @click="activeMenuId = null; toggleChannelProductDirect('{{ $product->id }}', 'all', 'activate')"
                                                            class="w-full text-left px-3.5 py-2 hover:bg-[#34C759]/10 rounded-[8px] flex items-center gap-2.5 text-[#34C759] transition-colors cursor-pointer">
                                                            <i data-lucide="eye" class="w-3.5 h-3.5 shrink-0"></i>
                                                            <span>{{ __('marketplace.activate_all_stores') }}</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-black/50 dark:text-white/50">
                                <div class="w-12 h-12 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06] flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="inbox" class="w-6 h-6 text-black/40 dark:text-white/40"></i>
                                </div>
                                <div class="font-bold text-[14px]">{{ __('marketplace.empty_products_initial_title') }}</div>
                                <p class="text-[12px] mt-0.5">{{ __('marketplace.empty_products_initial_desc') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- ===================================================== -->
    <!-- 4. MODAL PEMETAAN & ATUR HARGA PER CHANNEL (BENTO XXL)-->
    <!-- ===================================================== -->
    <template x-teleport="body">
        <div x-show="mappingModal" x-cloak
            class="fixed inset-0 z-[200] overflow-y-auto flex items-center justify-center p-3 sm:p-4"
            @keydown.escape.window="mappingModal = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="marketplace-mapping-modal-title">

            <!-- Backdrop -->
            <div x-show="mappingModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="mappingModal = false"
                class="fixed inset-0 bg-black/60 dark:bg-black/75 backdrop-blur-md"></div>

            <div x-show="mappingModal"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                class="relative w-full max-w-5xl xl:max-w-6xl rounded-[28px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 p-6 sm:p-8 space-y-6 shadow-2xl max-h-[92vh] overflow-y-auto z-10"
                @click.outside="mappingModal = false">

                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-[16px] bg-[#007AFF]/15 text-[#007AFF] flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h3 id="marketplace-mapping-modal-title" class="text-[18px] font-bold text-black dark:text-white tracking-tight">{{ __('marketplace.modal_mapping_title') }}</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#007AFF]/10 text-[#007AFF] border border-[#007AFF]/20">
                                    Bento XXL v2.0
                                </span>
                            </div>
                            <p class="text-[12.5px] text-black/50 dark:text-white/50" x-text="selectedProduct ? (selectedProduct.name + ' • SKU: ' + (selectedProduct.code || '-')) : ''"></p>
                        </div>
                    </div>
                    <button type="button" @click="mappingModal = false" class="min-w-[44px] min-h-[44px] rounded-full bg-black/5 dark:bg-white/10 flex items-center justify-center text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white hover:bg-black/10 transition-all cursor-pointer active:scale-95" aria-label="{{ __('marketplace.close_modal') }}">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

            <!-- Form -->
            <form method="POST" action="{{ route('marketplace-hub.products.map') }}" @submit="handleFormSubmit($event)" class="space-y-6">
                @csrf
                <input type="hidden" name="product_id" :value="form.product_id">
                <input type="hidden" name="channel" :value="form.channel">
                <input type="hidden" name="category_id" :value="form.category_id">
                <input type="hidden" name="category_name" :value="form.category_name">
                <input type="hidden" name="sync_price_auto" :value="form.sync_price_auto ? '1' : '0'">
                <input type="hidden" name="sync_stock_auto" :value="form.sync_stock_auto ? '1' : '0'">
                <input type="hidden" name="allow_below_cost" :value="form.allow_below_cost ? '1' : '0'">
                <input type="hidden" name="is_active" :value="form.is_active ? '1' : '0'">

                <!-- 2-Column Bento Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- ================= LEFT COLUMN: INPUTS & SETTINGS (7 COLS) ================= -->
                    <div class="lg:col-span-7 space-y-5">
                        
                        <!-- 1. Channel Selector Segmented Tabs -->
                        <div>
                            <label class="block text-[12px] font-bold uppercase tracking-wider text-black/50 dark:text-white/50 mb-2">
                                {{ __('marketplace.modal_channel_selector') }}
                            </label>
                            <div class="grid grid-cols-3 gap-2 p-1.5 rounded-[16px] bg-black/[0.04] dark:bg-white/[0.06]">
                                <button type="button" @click="switchChannel('shopee')"
                                    :class="form.channel === 'shopee' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#EE4D2D]' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[12px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full" :class="form.channel === 'shopee' ? 'bg-[#EE4D2D]' : 'bg-transparent'"></span>
                                    <span>Shopee</span>
                                </button>
                                <button type="button" @click="switchChannel('tiktok_shop')"
                                    :class="form.channel === 'tiktok_shop' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-black dark:text-white' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[12px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full" :class="form.channel === 'tiktok_shop' ? 'bg-black dark:bg-white' : 'bg-transparent'"></span>
                                    <span>TikTok Shop</span>
                                </button>
                                <button type="button" @click="switchChannel('tokopedia')"
                                    :class="form.channel === 'tokopedia' ? 'bg-white dark:bg-[#2C2C2E] shadow-sm font-bold text-[#00AA5B]' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[12px] transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                    <span class="w-2 h-2 rounded-full" :class="form.channel === 'tokopedia' ? 'bg-[#00AA5B]' : 'bg-transparent'"></span>
                                    <span>Tokopedia</span>
                                </button>
                            </div>
                        </div>

                        <!-- 1.5 Official Marketplace Category Selector Bento Card -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-3.5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="block text-[13px] font-bold text-black dark:text-white">
                                        {{ __('marketplace.modal_category_title') }}
                                    </label>
                                    <p class="text-[11.5px] text-black/50 dark:text-white/50">
                                        {{ __('marketplace.modal_category_sub') }}
                                    </p>
                                </div>
                                <template x-if="selectedCategoryObj">
                                    <span class="text-[11px] font-mono font-bold px-2.5 py-1 rounded-full bg-[#007AFF]/10 text-[#007AFF]">
                                        ID: <span x-text="form.category_id"></span>
                                    </span>
                                </template>
                            </div>

                            <!-- Active Selection Card / Trigger Button -->
                            <div class="relative">
                                <button type="button" @click="categoryDropdownOpen = !categoryDropdownOpen"
                                    class="w-full p-3.5 rounded-[14px] bg-white dark:bg-[#1C1C1E] border border-black/[0.1] dark:border-white/[0.12] hover:border-[#007AFF] transition-all flex items-center justify-between text-left gap-3 shadow-2xs cursor-pointer">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-[10px] bg-[#007AFF]/10 text-[#007AFF] flex items-center justify-center shrink-0">
                                            <i data-lucide="layers" class="w-5 h-5"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-[13px] font-bold text-black dark:text-white truncate" x-text="categoryDisplayName"></div>
                                            <div class="text-[11px] text-black/50 dark:text-white/50 truncate max-w-[280px] sm:max-w-md" x-text="categoryDisplayDesc"></div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0 text-black/40 dark:text-white/40">
                                        <span class="text-[11.5px] font-medium hidden sm:inline" x-text="categoryToggleLabel"></span>
                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="categoryDropdownOpen ? 'rotate-180' : ''"></i>
                                    </div>
                                </button>

                                <!-- Searchable Dropdown Overlay Card (Bento Sheet) -->
                                <div x-show="categoryDropdownOpen" @click.away="categoryDropdownOpen = false" x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                    class="absolute z-50 left-0 right-0 top-full mt-2 p-3 rounded-[18px] bg-white dark:bg-[#1C1C1E] border border-black/10 dark:border-white/15 shadow-xl max-h-[340px] flex flex-col space-y-2.5">
                                    
                                    <!-- Search bar -->
                                    <div class="relative">
                                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40"></i>
                                        <input type="text" x-model="categorySearch" placeholder="{{ __('marketplace.modal_category_search_placeholder') }}"
                                            class="w-full h-9 pl-9 pr-3 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.08] dark:border-white/[0.1] text-[12.5px] text-black dark:text-white placeholder:text-black/35 dark:placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                                    </div>

                                    <!-- Categories List Scrollable -->
                                    <div class="overflow-y-auto space-y-1 pr-1 flex-1 max-h-[240px] custom-scrollbar">
                                        <template x-for="cat in filteredCategories" :key="cat.id">
                                            <button type="button" @click="selectCategory(cat)"
                                                :class="form.category_id === cat.id ? 'bg-[#007AFF]/10 border-[#007AFF]/30 text-[#007AFF]' : 'hover:bg-black/[0.03] dark:hover:bg-white/[0.05] border-transparent text-black dark:text-white'"
                                                class="w-full p-2.5 rounded-[12px] border text-left flex items-center justify-between gap-2.5 transition-colors cursor-pointer group">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-[12.5px] font-semibold group-hover:text-[#007AFF] transition-colors" x-text="cat.name"></span>
                                                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-black/5 dark:bg-white/10 text-black/60 dark:text-white/60" x-text="cat.id"></span>
                                                    </div>
                                                    <p class="text-[11px] text-black/50 dark:text-white/50 truncate mt-0.5" x-text="cat.description"></p>
                                                </div>
                                                <template x-if="form.category_id === cat.id">
                                                    <i data-lucide="check" class="w-4 h-4 text-[#007AFF] shrink-0"></i>
                                                </template>
                                            </button>
                                        </template>
                                        <div x-show="filteredCategories.length === 0" class="py-6 text-center text-[12px] text-black/40 dark:text-white/40">
                                            {{ __('marketplace.modal_category_empty') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Marketplace Listing Status & Item ID/SKU -->
                        <div class="space-y-3">
                            <div class="p-3.5 rounded-[16px] border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 text-[12.5px]"
                                :class="form.marketplace_item_id ? (form.is_active ? 'bg-[#34C759]/10 border-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/10 border-[#FF9500]/20 text-[#FF9500]') : 'bg-[#007AFF]/10 border-[#007AFF]/20 text-[#007AFF]'">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-4 h-4 shrink-0 flex items-center justify-center">
                                        <i x-show="form.marketplace_item_id && form.is_active" data-lucide="check-circle-2" class="w-4 h-4"></i>
                                        <i x-show="form.marketplace_item_id && !form.is_active" data-lucide="pause-circle" class="w-4 h-4"></i>
                                        <i x-show="!form.marketplace_item_id" data-lucide="sparkles" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <template x-if="form.marketplace_item_id">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span>Item ID: <strong class="font-mono" x-text="form.marketplace_item_id"></strong></span>
                                                <span class="font-bold px-2 py-0.5 rounded text-[10.5px]" :class="form.is_active ? 'bg-[#34C759]/20 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/20 text-[#FF9500]'" x-text="listingStatusLabel"></span>
                                            </div>
                                        </template>
                                        <template x-if="!form.marketplace_item_id">
                                            <span>{!! __('marketplace.modal_unregistered_hint') !!}</span>
                                        </template>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <template x-if="form.marketplace_item_id && form.channel === 'tiktok_shop'">
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" x-show="form.is_active" @click="toggleChannelProductStatus('deactivate')" :disabled="togglingStatus"
                                                class="px-2.5 py-1 rounded-[10px] text-[11px] font-bold bg-[#FF3B30]/15 text-[#FF3B30] hover:bg-[#FF3B30]/25 transition-all flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="eye-off" class="w-3.5 h-3.5"></i>
                                                <span x-text="delistButtonText"></span>
                                            </button>
                                            <button type="button" x-show="!form.is_active" @click="toggleChannelProductStatus('activate')" :disabled="togglingStatus"
                                                class="px-2.5 py-1 rounded-[10px] text-[11px] font-bold bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158] hover:bg-[#34C759]/25 transition-all flex items-center gap-1 cursor-pointer">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                                <span x-text="relistButtonText"></span>
                                            </button>
                                        </div>
                                    </template>
                                    <div x-show="selectedProduct" class="shrink-0 text-[11px] font-semibold opacity-90 px-2.5 py-0.5 rounded-full bg-white/60 dark:bg-black/20 border border-current/20">
                                        <span x-text="photosCountLabel"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div>
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                        {{ __('marketplace.modal_item_id_manual') }}
                                    </label>
                                    <input type="text" name="marketplace_item_id" x-model="form.marketplace_item_id"
                                        placeholder="{{ __('marketplace.placeholder_example_id') }}"
                                        class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                                </div>
                                <div>
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                        {{ __('marketplace.modal_sku_manual') }}
                                    </label>
                                    <input type="text" name="marketplace_sku" x-model="form.marketplace_sku"
                                        placeholder="{{ __('marketplace.placeholder_example_sku') }}"
                                        class="w-full h-10 px-3.5 rounded-[12px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.08] dark:border-white/[0.1] text-[13px] text-black dark:text-white font-mono placeholder:text-black/30 dark:placeholder:text-white/30 focus:outline-none focus:ring-2 focus:ring-[#007AFF]/30">
                                </div>
                            </div>
                        </div>

                        <!-- 3. Pricing Bento Card -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[13.5px] font-bold text-black dark:text-white">{{ __('marketplace.modal_pricing_scheme') }}</span>
                                    <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('marketplace.modal_pricing_scheme_sub') }}</p>
                                </div>
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full" 
                                    :class="pricingMode === 'nominal' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#007AFF]/10 text-[#007AFF]'">
                                    <span x-text="pricingModeBadgeText"></span>
                                </span>
                            </div>

                            <!-- Segmented Control Tabs (Harga Langsung vs Persentase Markup) -->
                            <div class="grid grid-cols-2 gap-1.5 p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06]">
                                <button type="button" @click="setPricingMode('nominal')"
                                    :class="pricingMode === 'nominal' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[10px] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="tag" class="w-4 h-4 text-[#34C759]"></i>
                                    <span>{{ __('marketplace.modal_pricing_nominal_tab') }}</span>
                                </button>
                                <button type="button" @click="setPricingMode('percentage')"
                                    :class="pricingMode === 'percentage' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[10px] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="percent" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span>{{ __('marketplace.modal_pricing_percent_tab') }}</span>
                                </button>
                            </div>

                            <!-- MODE 1: HARGA LANGSUNG / NOMINAL RP -->
                            <div x-show="pricingMode === 'nominal'" class="space-y-3 pt-1">
                                <div>
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                        {{ __('marketplace.modal_pricing_nominal_label') }}
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-[13px]">Rp</span>
                                        <input type="number" step="100" min="0" name="channel_price" x-model="form.channel_price"
                                            placeholder="{{ __('marketplace.placeholder_example_price') }}"
                                            class="w-full h-11 pl-10 pr-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#34C759]/40">
                                    </div>
                                    
                                    <!-- Live comparison info with COOCA Store Price -->
                                    <div class="mt-2 text-[12px] flex items-center gap-1.5" x-show="form.channel_price && selectedProduct">
                                        <template x-if="nominalDiffVsStore.rawDiff > 0">
                                            <span class="text-[#34C759] font-medium flex items-center gap-1">
                                                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                                                <span>+<strong x-text="formatRupiah(nominalDiffVsStore.amount)"></strong> (+<span x-text="nominalDiffVsStore.percent"></span>%) {{ __('marketplace.compared_to_cooca_store') }} (<span x-text="formatRupiah(selectedProduct.selling_price)"></span>)</span>
                                            </span>
                                        </template>
                                        <template x-if="nominalDiffVsStore.rawDiff < 0">
                                            <span class="text-[#FF9500] font-medium flex items-center gap-1">
                                                <i data-lucide="trending-down" class="w-3.5 h-3.5"></i>
                                                <span>-<strong x-text="formatRupiah(nominalDiffVsStore.amount)"></strong> (-<span x-text="nominalDiffVsStore.percent"></span>%) {{ __('marketplace.compared_to_cooca_store') }} (<span x-text="formatRupiah(selectedProduct.selling_price)"></span>)</span>
                                            </span>
                                        </template>
                                        <template x-if="nominalDiffVsStore.rawDiff === 0">
                                            <span class="text-black/50 dark:text-white/50 font-medium">
                                                {{ __('marketplace.same_as_cooca_store') }} (<span x-text="formatRupiah(selectedProduct?.selling_price || 0)"></span>)
                                            </span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Quick nominal presets -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-0.5" x-show="selectedProduct">
                                    <span class="text-[11px] text-black/45 dark:text-white/45">{{ __('marketplace.modal_quick_presets') }}</span>
                                    <button type="button" @click="setNominalPrice(Number(selectedProduct.selling_price || 0))"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#34C759] transition-colors cursor-pointer">
                                        {{ __('marketplace.modal_preset_same_price') }}
                                    </button>
                                    <button type="button" @click="setNominalPrice(Number(selectedProduct.selling_price || 0) + 5000)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#34C759] transition-colors cursor-pointer">
                                        +Rp 5.000
                                    </button>
                                    <button type="button" @click="setNominalPrice(Number(selectedProduct.selling_price || 0) + 10000)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#34C759] transition-colors cursor-pointer">
                                        +Rp 10.000
                                    </button>
                                    <button type="button" @click="setNominalPrice(Number(selectedProduct.selling_price || 0) + 20000)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#34C759] transition-colors cursor-pointer">
                                        +Rp 20.000
                                    </button>
                                </div>
                            </div>

                            <!-- MODE 2: PERSENTASE MARKUP (%) -->
                            <div x-show="pricingMode === 'percentage'" class="space-y-3 pt-1">
                                <div>
                                    <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70 mb-1.5">
                                        {{ __('marketplace.modal_pricing_percent_label') }}
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <div class="relative w-36">
                                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-[13px]">+</span>
                                            <input type="number" step="0.5" min="-50" max="200" x-model="markupPercent" @input="updateMultiplierFromPercent()"
                                                placeholder="{{ __('marketplace.placeholder_example_percent') }}"
                                                class="w-full h-11 pl-8 pr-7 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-black/40 dark:text-white/40 font-bold text-[13px]">%</span>
                                        </div>
                                        <input type="hidden" name="price_multiplier" :value="form.price_multiplier">
                                        <div class="text-[13px] text-black/70 dark:text-white/70">
                                            = <strong class="text-black dark:text-white font-mono text-[14px]" x-text="formatRupiah(computedEffectivePrice)"></strong> {{ __('marketplace.in_channel') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick percentage presets -->
                                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                    <span class="text-[11px] text-black/45 dark:text-white/45">{{ __('marketplace.modal_quick_presets_percent') }}</span>
                                    <button type="button" @click="setMarkupPercent(0)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 0 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        {{ __('marketplace.preset_same_percent') }}
                                    </button>
                                    <button type="button" @click="setMarkupPercent(5)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 5 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        +5%
                                    </button>
                                    <button type="button" @click="setMarkupPercent(8)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 8 ? 'border-[#007AFF] text-[#007AFF] ring-1 ring-[#007AFF]' : ''">
                                        {{ __('marketplace.modal_preset_percent_fee') }}
                                    </button>
                                    <button type="button" @click="setMarkupPercent(10)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 10 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        +10%
                                    </button>
                                    <button type="button" @click="setMarkupPercent(15)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 15 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        +15%
                                    </button>
                                    <button type="button" @click="setMarkupPercent(20)"
                                        class="px-2.5 py-1 rounded-[8px] text-[11px] font-semibold bg-white dark:bg-[#2C2C2E] border border-black/10 dark:border-white/10 hover:border-[#007AFF] transition-colors cursor-pointer"
                                        :class="markupPercent == 20 ? 'border-[#007AFF] text-[#007AFF]' : ''">
                                        +20%
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Stock Bento Card -->
                        <div class="p-4 sm:p-5 rounded-[20px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-[13.5px] font-bold text-black dark:text-white">{{ __('marketplace.modal_stock_scheme') }}</span>
                                    <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('marketplace.modal_stock_scheme_sub') }}</p>
                                </div>
                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full" :class="stockMode === 'auto' ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-black/10 text-black/60 dark:text-white/60'">
                                    <span x-text="stockModeBadgeText"></span>
                                </span>
                            </div>

                            <!-- Segmented Control Tabs (Stok Otomatis vs Kuota Manual) -->
                            <div class="grid grid-cols-2 gap-1.5 p-1 rounded-[14px] bg-black/[0.04] dark:bg-white/[0.06]">
                                <button type="button" @click="setStockMode('auto')"
                                    :class="stockMode === 'auto' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[10px] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="refresh-cw" class="w-4 h-4 text-[#34C759]"></i>
                                    <span>{{ __('marketplace.modal_stock_auto_tab') }}</span>
                                </button>
                                <button type="button" @click="setStockMode('manual')"
                                    :class="stockMode === 'manual' ? 'bg-white dark:bg-[#2C2C2E] text-black dark:text-white shadow-sm font-bold' : 'text-black/60 dark:text-white/60 font-medium hover:text-black dark:hover:text-white'"
                                    class="py-2.5 text-[12.5px] rounded-[10px] transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i data-lucide="layers" class="w-4 h-4 text-[#007AFF]"></i>
                                    <span>{{ __('marketplace.modal_stock_manual_tab') }}</span>
                                </button>
                            </div>

                            <!-- If Auto: Stock Buffer -->
                            <div x-show="stockMode === 'auto'" class="space-y-2 pt-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                    {{ __('marketplace.modal_safety_buffer_label') }}
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="number" min="0" name="stock_buffer" x-model="form.stock_buffer"
                                        class="w-32 h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white font-mono font-bold">
                                    <div class="text-[12px] text-black/60 dark:text-white/60 leading-relaxed">
                                        {{ __('marketplace.modal_safety_buffer_desc') }}
                                    </div>
                                </div>

                                <!-- Live anti-overselling stock calculation -->
                                <div class="p-2.5 rounded-[12px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.06] dark:border-white/[0.08] text-[11.5px] text-black/70 dark:text-white/70 flex items-center justify-between gap-2" x-show="selectedProduct">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="shield-check" class="w-4 h-4 text-[#34C759]"></i>
                                        <span>{{ __('marketplace.modal_simulated_allocation') }}</span>
                                    </div>
                                    <div class="font-mono font-bold text-black dark:text-white">
                                        <span x-text="selectedProduct ? selectedProduct.stock : 0"></span> {{ __('marketplace.warehouse_parenthesis') }} - <span x-text="form.stock_buffer || 0"></span> {{ __('marketplace.buffer_parenthesis') }} = <span class="text-[#007AFF] text-[13px]" x-text="effectiveChannelStock"></span> {{ __('marketplace.unit') }}
                                    </div>
                                </div>
                                <div x-show="isBufferExceedingStock" class="p-2.5 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#FF9500] text-[11.5px] flex items-center gap-2">
                                    <i data-lucide="shield-alert" class="w-4 h-4 shrink-0"></i>
                                    <span>{{ __('marketplace.modal_buffer_exceeds_warning') }}</span>
                                </div>
                            </div>

                            <!-- If Manual: Custom Stock -->
                            <div x-show="stockMode === 'manual'" class="space-y-2 pt-1">
                                <label class="block text-[12px] font-semibold text-black/70 dark:text-white/70">
                                    {{ __('marketplace.modal_custom_stock_label') }}
                                </label>
                                <div class="relative">
                                    <input type="number" min="0" name="custom_stock" x-model="form.custom_stock"
                                        placeholder="{{ __('marketplace.placeholder_example_stock') }}"
                                        class="w-full h-11 px-3.5 rounded-[12px] bg-white dark:bg-[#1C1C1E] border border-black/[0.08] dark:border-white/[0.1] text-[14px] text-black dark:text-white font-mono font-bold focus:outline-none focus:ring-2 focus:ring-[#007AFF]/40">
                                </div>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('marketplace.modal_custom_stock_desc') }}</p>
                            </div>
                        </div>

                        <!-- 5. Active Toggle -->
                        <div class="flex items-center justify-between p-4 rounded-[18px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08]">
                            <div>
                                <span class="text-[13px] font-bold text-black dark:text-white">{{ __('marketplace.modal_listing_status_label') }}</span>
                                <p class="text-[11.5px] text-black/50 dark:text-white/50">{{ __('marketplace.modal_listing_status_desc') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full transition-colors"
                                    :class="form.is_active ? 'bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]' : 'bg-[#FF9500]/15 text-[#FF9500]'">
                                    <span x-text="listingStatusLabel"></span>
                                </span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="form.is_active" class="sr-only peer">
                                    <div class="w-11 h-6 bg-black/20 peer-focus:outline-none rounded-full peer dark:bg-white/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#34C759]"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- ================= RIGHT COLUMN: PRE-FLIGHT INSPECTION, FINANCIAL CALCULATOR & TIPS (5 COLS) ================= -->
                    <div class="lg:col-span-5 space-y-4">
                        
                        <!-- Pre-flight Listing Readiness Inspection (Apple HIG Bento) -->
                        <div class="rounded-[22px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-3.5 shadow-xs" x-show="selectedProduct">
                            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="clipboard-check" class="w-4 h-4 text-[#34C759]"></i>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">{{ __('marketplace.inspection_title') }}</h4>
                                </div>
                                <template x-if="galleryPhotos.length >= 5">
                                    <span class="text-[10.5px] font-bold px-2.5 py-0.5 rounded-full bg-[#34C759]/15 text-[#248A3D] dark:text-[#30D158]">
                                        {{ __('marketplace.inspection_optimal') }}
                                    </span>
                                </template>
                                <template x-if="galleryPhotos.length > 0 && galleryPhotos.length < 5">
                                    <span class="text-[10.5px] font-bold px-2.5 py-0.5 rounded-full bg-[#007AFF]/10 text-[#007AFF]">
                                        <span x-text="photosCountLabel"></span>
                                    </span>
                                </template>
                                <template x-if="galleryPhotos.length === 0">
                                    <span class="text-[10.5px] font-bold px-2.5 py-0.5 rounded-full bg-[#FF3B30]/15 text-[#FF3B30]">
                                        {{ __('marketplace.inspection_no_photos') }}
                                    </span>
                                </template>
                            </div>

                            <!-- Gallery thumbnails preview strip -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-[11px] text-black/50 dark:text-white/50">
                                    <span>{{ __('marketplace.inspection_gallery_prefix') }} (<span x-text="galleryPhotos.length"></span> {{ __('marketplace.inspection_min_photos_hint') }}):</span>
                                    <span x-show="galleryPhotos.length >= 5" class="text-[#34C759] font-medium">{{ __('marketplace.inspection_meets_standard') }}</span>
                                    <span x-show="galleryPhotos.length > 0 && galleryPhotos.length < 5" class="text-black/40 dark:text-white/40">{{ __('marketplace.inspection_min_photos_hint') }}</span>
                                </div>
                                <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
                                    <template x-for="(photo, pidx) in galleryPhotos" :key="pidx">
                                        <div class="relative w-12 h-12 rounded-[10px] overflow-hidden border border-black/10 dark:border-white/10 shrink-0 group bg-black/5 dark:bg-white/5">
                                            <img :src="photo.url" class="w-full h-full object-cover" :alt="photo.caption || '{{ addslashes(__('marketplace.th_product')) }}'">
                                            <span class="absolute bottom-0 inset-x-0 bg-black/70 text-white text-[8px] font-bold text-center py-0.5 truncate" x-text="pidx === 0 ? '{{ addslashes(__('marketplace.photo_main_badge')) }}' : '#' + (pidx + 1)"></span>
                                        </div>
                                    </template>
                                    <div x-show="galleryPhotos.length === 0" class="w-full py-3 text-center text-[11.5px] text-[#FF3B30] bg-[#FF3B30]/10 rounded-[10px] border border-[#FF3B30]/20 font-medium">
                                        {{ __('marketplace.inspection_no_photo_alert') }}
                                    </div>
                                </div>

                                <!-- Shopee Specific Requirement Notice -->
                                <div x-show="form.channel === 'shopee' && galleryPhotos.length < 2" class="p-2.5 rounded-[12px] bg-[#FF9500]/10 border border-[#FF9500]/25 text-[#D97706] dark:text-[#FBBF24] text-[11px] space-y-1">
                                    <div class="font-bold flex items-center gap-1.5">
                                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>Syarat Foto Shopee Belum Lengkap</span>
                                    </div>
                                    <p class="text-[10.5px] leading-relaxed opacity-95">
                                        Shopee mewajibkan <strong>minimal 2 foto produk</strong>. Saat ini produk baru memiliki <span class="font-bold font-mono" x-text="galleryPhotos.length"></span> foto. Silakan tambahkan minimal 1 foto lagi di Master Data Produk sebelum menerbitkan.
                                    </p>
                                </div>
                            </div>

                            <!-- Logistics Specs (Weight & Dimensions) -->
                            <div class="grid grid-cols-2 gap-2 text-[11.5px] pt-1">
                                <div class="p-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.04] dark:border-white/[0.06]">
                                    <span class="text-black/50 dark:text-white/50 block text-[10.5px]">{{ __('marketplace.spec_package_weight') }}</span>
                                    <span class="font-bold text-black dark:text-white font-mono" x-text="(selectedProduct?.weight || 200) + ' gram'"></span>
                                </div>
                                <div class="p-2 rounded-[10px] bg-black/[0.03] dark:bg-white/[0.05] border border-black/[0.04] dark:border-white/[0.06]">
                                    <span class="text-black/50 dark:text-white/50 block text-[10.5px]">{{ __('marketplace.spec_package_dimensions') }}</span>
                                    <span class="font-bold text-black dark:text-white font-mono" x-text="(selectedProduct?.length || 10) + '×' + (selectedProduct?.width || 10) + '×' + (selectedProduct?.height || 10) + ' cm'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Summary Card (Apple HIG Bento) -->
                        <div class="rounded-[22px] bg-gradient-to-br from-black/[0.02] to-black/[0.05] dark:from-white/[0.03] dark:to-white/[0.06] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-4 shadow-xs">
                            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.08] pb-3">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="calculator" class="w-4 h-4 text-[#007AFF]"></i>
                                    <h4 class="text-[13px] font-bold text-black dark:text-white">{{ __('marketplace.calc_margin_live') }}</h4>
                                </div>
                                <span class="text-[11px] font-mono text-black/50 dark:text-white/50 uppercase" x-text="form.channel"></span>
                            </div>

                            <div class="space-y-2.5 text-[12.5px]">
                                <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                    <span>{{ __('marketplace.calc_base_cost') }}</span>
                                    <span class="font-mono font-semibold text-black dark:text-white" x-text="formatRupiah(computedBaseCost)"></span>
                                </div>
                                <div class="flex items-center justify-between text-black/60 dark:text-white/60">
                                    <span>{{ __('marketplace.calc_store_price') }}</span>
                                    <span class="font-mono font-semibold text-black dark:text-white" x-text="formatRupiah(selectedProduct ? selectedProduct.selling_price : 0)"></span>
                                </div>
                                <div class="flex items-center justify-between text-black dark:text-white font-medium">
                                    <span>{{ __('marketplace.calc_effective_channel_price') }}</span>
                                    <span class="font-mono font-bold text-[13.5px] text-[#007AFF]" x-text="formatRupiah(computedEffectivePrice)"></span>
                                </div>
                                <div class="flex items-center justify-between text-[#FF3B30] text-[12px]">
                                    <span>{{ __('marketplace.calc_admin_fee_estimate') }}</span>
                                    <span class="font-mono font-medium" x-text="'- ' + formatRupiah(computedAdminFee)"></span>
                                </div>

                                <div class="pt-2 border-t border-black/[0.06] dark:border-white/[0.08]">
                                    <div class="flex items-center justify-between text-[12.5px] text-black/70 dark:text-white/70">
                                        <span>{{ __('marketplace.calc_net_payout') }}</span>
                                        <span class="font-mono font-bold text-black dark:text-white" x-text="formatRupiah(computedNetReceived)"></span>
                                    </div>
                                    <div class="flex items-center justify-between text-[13px] pt-1">
                                        <span class="font-bold text-black dark:text-white">{{ __('marketplace.calc_net_margin') }}</span>
                                        <div class="text-right">
                                            <span class="font-mono font-bold text-[14px]" :class="computedNetMargin < 0 ? 'text-[#FF3B30]' : 'text-[#34C759]'" x-text="formatRupiah(computedNetMargin)"></span>
                                            <span class="text-[11px] block font-mono font-semibold" :class="computedNetMargin < 0 ? 'text-[#FF3B30]' : 'text-[#34C759]'" x-text="'(' + computedMarginPercent + '%)'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Zero-Price Warning (Accidental Free Product Guard) -->
                            <div x-show="isZeroPrice" class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/30 space-y-1 text-[#FF3B30]">
                                <div class="flex items-center gap-2 text-[12px] font-bold">
                                    <i data-lucide="alert-octagon" class="w-4 h-4 shrink-0"></i>
                                    <span>{{ __('marketplace.warning_zero_price_title') }}</span>
                                </div>
                                <p class="text-[11.5px] text-black/80 dark:text-white/80 leading-tight">
                                    {{ __('marketplace.warning_zero_price_desc') }}
                                </p>
                            </div>

                            <!-- Anti-Margin Bleed Alert & Risk Consent (Guardrail 3) -->
                            <div x-show="!isZeroPrice && isBelowCost" class="p-3.5 rounded-[16px] bg-[#FF3B30]/10 border border-[#FF3B30]/30 space-y-2.5 text-[#FF3B30]">
                                <div class="flex items-start gap-2.5">
                                    <i data-lucide="shield-alert" class="w-4 h-4 shrink-0 mt-0.5 text-[#FF3B30]"></i>
                                    <div class="space-y-1">
                                        <div class="text-[12px] font-bold text-[#FF3B30]">
                                            {{ __('marketplace.warning_margin_bleed_title') }}
                                        </div>
                                        <div class="text-[11.5px] leading-tight text-black/80 dark:text-white/80">
                                            {!! __('marketplace.warning_margin_bleed_desc_html') !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-[#FF3B30]/20 flex items-start gap-2">
                                    <input type="checkbox" id="allow_below_cost" x-model="form.allow_below_cost"
                                        class="mt-0.5 rounded-[4px] border-[#FF3B30] text-[#FF3B30] focus:ring-[#FF3B30] cursor-pointer">
                                    <label for="allow_below_cost" class="text-[11.5px] font-semibold text-black dark:text-white cursor-pointer select-none leading-tight">
                                        {{ __('marketplace.consent_below_cost') }}
                                    </label>
                                </div>
                            </div>

                            <!-- Bleed Warning if Margin is Negative but above Base Cost -->
                            <div x-show="!isZeroPrice && !isBelowCost && computedNetMargin < 0" class="p-3 rounded-[12px] bg-[#FF3B30]/10 border border-[#FF3B30]/20 flex items-start gap-2.5 text-[#FF3B30]">
                                <i data-lucide="alert-octagon" class="w-4 h-4 shrink-0 mt-0.5"></i>
                                <div class="text-[11.5px] leading-tight font-medium">
                                    {!! __('marketplace.warning_loss_potential') !!}
                                </div>
                            </div>
                        </div>

                        <!-- Channel Guardrails & Rules Card -->
                        <div class="rounded-[22px] bg-black/[0.02] dark:bg-white/[0.03] border border-black/[0.06] dark:border-white/[0.08] p-5 space-y-3">
                            <div class="flex items-center gap-2">
                                <i data-lucide="info" class="w-4 h-4 text-black/50 dark:text-white/50"></i>
                                <h4 class="text-[12.5px] font-bold text-black dark:text-white">{{ __('marketplace.guidelines_title') }}</h4>
                            </div>
                            <ul class="text-[11.5px] text-black/60 dark:text-white/60 space-y-2 leading-relaxed">
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                                    <span>{!! __('marketplace.guidelines_1') !!}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-[#34C759] shrink-0 mt-0.5"></i>
                                    <span>{!! __('marketplace.guidelines_2') !!}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#007AFF] shrink-0 mt-0.5"></i>
                                    <span>{!! __('marketplace.guidelines_3') !!}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- Modal Actions -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-4 border-t border-black/[0.06] dark:border-white/[0.08]">
                    <div class="text-[12px] text-black/50 dark:text-white/50">
                        <span x-show="form.marketplace_item_id">{{ __('marketplace.item_id_connected') }} <strong class="font-mono text-black dark:text-white" x-text="form.marketplace_item_id"></strong></span>
                        <span x-show="!form.marketplace_item_id" class="inline-flex items-center gap-1 text-[#FF9500] font-semibold">
                            <i data-lucide="info" class="w-3.5 h-3.5"></i>
                            <span>{{ __('marketplace.listing_not_published') }}</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 flex-wrap">
                        <button type="button" @click="mappingModal = false" :disabled="submitting || publishing || togglingStatus"
                            class="h-11 px-4 rounded-[14px] text-[13px] font-semibold text-black/70 dark:text-white/70 bg-black/[0.05] dark:bg-white/[0.08] hover:bg-black/[0.08] transition-all cursor-pointer">
                            {{ __('marketplace.cancel') }}
                        </button>

                        {{-- 1-Click Direct Delist / Activate from Marketplace Etalase --}}
                        <template x-if="form.marketplace_item_id && form.is_active">
                            <button type="button" @click="toggleChannelProductStatus('deactivate')" :disabled="togglingStatus || publishing || submitting"
                                class="h-11 px-4 rounded-[14px] text-[13px] font-semibold text-[#FF3B30] bg-[#FF3B30]/10 hover:bg-[#FF3B30]/20 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                title="{{ __('marketplace.title_delist_direct') }}">
                                <i data-lucide="eye-off" class="w-4 h-4"></i>
                                <span x-text="delistButtonText"></span>
                            </button>
                        </template>
                        <template x-if="form.marketplace_item_id && !form.is_active">
                            <button type="button" @click="toggleChannelProductStatus('activate')" :disabled="togglingStatus || publishing || submitting"
                                class="h-11 px-4 rounded-[14px] text-[13px] font-semibold text-[#34C759] bg-[#34C759]/15 hover:bg-[#34C759]/25 active:scale-[0.98] transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                                title="{{ __('marketplace.title_relist_direct') }}">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                <span x-text="relistButtonText"></span>
                            </button>
                        </template>

                        <button type="button" @click="publishToMarketplace()" :disabled="publishing || submitting || togglingStatus || isZeroPrice || (isBelowCost && !form.allow_below_cost)"
                            class="h-11 px-5 rounded-[14px] text-[13px] font-bold text-white bg-[#34C759] hover:bg-[#28A745] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer"
                            title="{{ __('marketplace.title_upload_listing') }}">
                            <svg x-show="publishing" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <i x-show="!publishing" data-lucide="cloud-upload" class="w-4 h-4"></i>
                            <span x-text="publishButtonText"></span>
                        </button>
                        <button type="submit" :disabled="submitting || publishing || togglingStatus || isZeroPrice || (isBelowCost && !form.allow_below_cost)"
                            class="h-11 px-5 rounded-[14px] text-[13px] font-bold text-white bg-[#007AFF] hover:bg-[#0071E3] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                            <svg x-show="submitting" class="animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitButtonText"></span>
                        </button>

                    </div>
                </div>

            </form>
        </div>
    </div>
</template>

</div>
@endsection

