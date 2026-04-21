/* ============================================================
   WooCommerce Tabbed Product Grid — wpt-script.js
   Upload to: /wp-content/themes/your-child-theme/wpt/
   Requires: wptData.ajaxUrl, wptData.nonce, wptData.comparePageUrl
   ============================================================ */

jQuery(function ($) {

    var ajaxUrl = wptData.ajaxUrl;
    var nonce   = wptData.nonce;

    /* ═══════════════════════════════════════════════
       TAB SWITCHING
    ═══════════════════════════════════════════════ */
    $(document).on('click', '.wpt-tab', function () {
        var $tab     = $(this);
        var slug     = $tab.data('slug');
        var uid      = $tab.data('uid');
        var $section = $('#' + uid);

        $section.find('.wpt-tab').removeClass('active').attr('aria-selected', 'false');
        $tab.addClass('active').attr('aria-selected', 'true');

        $section.find('.wpt-panel').removeClass('active');
        $section.find('.wpt-panel[data-slug="' + slug + '"]').addClass('active');
    });

    /* ═══════════════════════════════════════════════
       WISHLIST  (localStorage)
    ═══════════════════════════════════════════════ */
    function getWishlist() {
        try { return JSON.parse(localStorage.getItem('wpt_wishlist') || '[]'); } catch (e) { return []; }
    }

    getWishlist().forEach(function (id) {
        $('.wpt-wishlist-btn[data-id="' + id + '"]').addClass('wishlisted');
    });

    $(document).on('click', '.wpt-wishlist-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var id   = $btn.data('id').toString();
        var list = getWishlist();
        var idx  = list.indexOf(id);
        if (idx === -1) { list.push(id); $btn.addClass('wishlisted'); }
        else            { list.splice(idx, 1); $btn.removeClass('wishlisted'); }
        localStorage.setItem('wpt_wishlist', JSON.stringify(list));
        $btn.css('transform', 'scale(1.4)');
        setTimeout(function () { $btn.css('transform', ''); }, 200);
    });

    /* ═══════════════════════════════════════════════
       COMPARE BAR
    ═══════════════════════════════════════════════ */
    var compareList = [];
    try { compareList = JSON.parse(localStorage.getItem('wpt_compare') || '[]'); } catch (e) {}

    function renderCompareBar() {
        if (!compareList.length) { $('#wpt-compare-bar').removeClass('open'); return; }
        $('#wpt-compare-bar').addClass('open');
        var html = '';
        compareList.forEach(function (item) {
            html += '<span class="wpt-compare-item">' + item.name +
                    '<button class="wpt-rm-compare" data-id="' + item.id + '">&times;</button></span>';
        });
        $('#wpt-compare-items').html(html);
        $('.wpt-compare-btn').each(function () {
            var inList = compareList.some(function (c) { return c.id === $(this).data('id').toString(); }.bind(this));
            $(this).toggleClass('compared', inList);
        });
    }
    renderCompareBar();

    $(document).on('click', '.wpt-compare-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var id   = $btn.data('id').toString();
        var name = $btn.data('name');
        var idx  = compareList.findIndex(function (c) { return c.id === id; });
        if (idx === -1) {
            if (compareList.length >= 4) { alert('Max 4 products.'); return; }
            compareList.push({ id: id, name: name });
        } else {
            compareList.splice(idx, 1);
        }
        localStorage.setItem('wpt_compare', JSON.stringify(compareList));
        renderCompareBar();
    });

    $(document).on('click', '.wpt-rm-compare', function () {
        var id = $(this).data('id').toString();
        compareList = compareList.filter(function (c) { return c.id !== id; });
        localStorage.setItem('wpt_compare', JSON.stringify(compareList));
        renderCompareBar();
    });

    $(document).on('click', '.wpt-compare-clear', function () {
        compareList = [];
        localStorage.removeItem('wpt_compare');
        renderCompareBar();
        $('.wpt-compare-btn').removeClass('compared');
    });

    $(document).on('click', '.wpt-compare-go', function () {
        if (compareList.length < 2) { alert('Select at least 2 products.'); return; }
        var ids   = compareList.map(function (c) { return c.id; });
        var query = ids.map(function (id) { return 'ids[]=' + id; }).join('&');
        window.location.href = wptData.comparePageUrl + '?' + query;
    });

    /* ═══════════════════════════════════════════════
       QUICK VIEW MODAL
    ═══════════════════════════════════════════════ */
    $(document).on('click', '.wpt-quickview-btn', function (e) {
        e.preventDefault();
        var pid = $(this).data('id');
        $('#wpt-modal-body').html('<div class="wpt-modal-loading">Loading...</div>');
        $('#wpt-modal-overlay').addClass('open');
        $('body').css('overflow', 'hidden');

        $.post(ajaxUrl, { action: 'wpt_quick_view', nonce: nonce, product_id: pid }, function (res) {
            if (res.success) {
                $('#wpt-modal-body').html(res.data);
                wptInitSlider();
                wptInitQvVariants();
            } else {
                $('#wpt-modal-body').html('<p style="color:red;padding:20px">Failed to load.</p>');
            }
        });
    });

    $(document).on('click', '#wpt-modal-close, #wpt-modal-overlay', function (e) {
        if ($(e.target).is('#wpt-modal-overlay') || $(e.target).is('#wpt-modal-close')) {
            $('#wpt-modal-overlay').removeClass('open');
            $('body').css('overflow', '');
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') { $('#wpt-modal-overlay').removeClass('open'); $('body').css('overflow', ''); }
    });

    /* ═══════════════════════════════════════════════
       IMAGE SLIDER (inside Quick View)
    ═══════════════════════════════════════════════ */
    function wptInitSlider() {
        var $slider = $('#wpt-qv-slider');
        if (!$slider.length) return;

        var $track  = $slider.find('.wpt-slider-track');
        var $slides = $slider.find('.wpt-slide');
        var total   = $slides.length;
        var current = 0;

        function goTo(n) {
            if (n < 0) n = total - 1;
            if (n >= total) n = 0;
            current = n;
            $track.css('transform', 'translateX(-' + (current * 100) + '%)');
            $slides.removeClass('active').eq(current).addClass('active');
            $('#wpt-modal-body .wpt-thumb').removeClass('active').eq(current).addClass('active');
        }

        $slider.find('.wpt-slide-prev').off('click').on('click', function (e) { e.stopPropagation(); goTo(current - 1); });
        $slider.find('.wpt-slide-next').off('click').on('click', function (e) { e.stopPropagation(); goTo(current + 1); });

        $(document).off('click.wpt-thumb').on('click.wpt-thumb', '.wpt-thumb', function () {
            goTo($(this).data('index'));
        });
    }

    /* ═══════════════════════════════════════════════
       QUICK VIEW: Variation change
    ═══════════════════════════════════════════════ */
    function wptInitQvVariants() {
        $(document).off('change.wpt-qv').on('change.wpt-qv', '.wpt-qv-variant', function () {
            var variationId = $(this).find('option:selected').data('variation-id');
            var $modal      = $('#wpt-modal-body');
            if (!variationId) return;

            var $track  = $modal.find('.wpt-slider-track');
            var $slides = $modal.find('.wpt-slide');
            $slides.eq(0).find('img').css('opacity', '0.4');

            $.post(ajaxUrl, { action: 'wpt_variation_image', nonce: nonce, variation_id: variationId }, function (res) {
                if (!res.success) { $slides.eq(0).find('img').css('opacity', '1'); return; }
                var d = res.data;
                if (d.image_url) {
                    $slides.eq(0).find('img').attr('src', d.image_url).css('opacity', '1');
                    $modal.find('.wpt-thumb').eq(0).find('img').attr('src', d.image_url);
                } else {
                    $slides.eq(0).find('img').css('opacity', '1');
                }
                $track.css('transform', 'translateX(0%)');
                $slides.removeClass('active').eq(0).addClass('active');
                $modal.find('.wpt-thumb').removeClass('active').eq(0).addClass('active');
                $modal.find('.wpt-qv-price-current').text(d.price);
                if (d.regular) { $modal.find('.wpt-qv-price-original').text(d.regular).show(); }
                else            { $modal.find('.wpt-qv-price-original').hide(); }
                $modal.find('.wpt-cart-btn.ajax_add_to_cart').attr('data-product_id', d.variation_id).data('product_id', d.variation_id);
            });
        });
    }

    /* ═══════════════════════════════════════════════
       CARD: Variation image + price swap
    ═══════════════════════════════════════════════ */
    $(document).on('change', '.wpt-card .wpt-variant', function () {
        var variationId = $(this).find('option:selected').data('variation-id');
        var $card       = $(this).closest('.wpt-card');
        var $img        = $card.find('.wpt-product-img');
        var $btn        = $card.find('.wpt-cart-btn');
        if (!variationId) return;

        $img.css('opacity', '0.4');
        $.post(ajaxUrl, { action: 'wpt_variation_image', nonce: nonce, variation_id: variationId }, function (res) {
            if (!res.success) { $img.css('opacity', '1'); return; }
            var d = res.data;
            if (d.image_url) $img.attr('src', d.image_url);
            $img.css('opacity', '1');
            if (d.price)   $card.find('.wpt-price').text(d.price);
            if (d.regular) { $card.find('.wpt-price-original').text(d.regular).show(); }
            else            { $card.find('.wpt-price-original').hide(); }
            $btn.attr('data-product_id', d.variation_id).data('product_id', d.variation_id);
        });
    });

    /* ═══════════════════════════════════════════════
       ADD TO CART — button states + Astra update
    ═══════════════════════════════════════════════ */
    $(document).on('adding_to_cart', function (e, $btn) {
        if ($btn && $btn.hasClass('wpt-cart-btn')) {
            $btn.addClass('loading').find('.wpt-btn-text').text('Adding...');
        }
    });

    $(document).on('added_to_cart', function (e, fragments, cart_hash, $btn) {
        if ($btn && $btn.hasClass('wpt-cart-btn')) {
            $btn.removeClass('loading').addClass('added').find('.wpt-btn-text').text('✓ Added!');
            setTimeout(function () {
                $btn.removeClass('added').find('.wpt-btn-text').text('Add to Cart');
            }, 2500);
        }
        $(document.body).trigger('wc_fragment_refresh');

        // Toast — detect source
        var name = '', imgSrc = '', price = '';
        if ($btn) {
            var $card = $btn.closest('.wpt-card');
            if ($card.length) {
                name   = $card.find('.wpt-title a').text().trim();
                imgSrc = $card.find('.wpt-product-img').attr('src') || '';
                price  = $card.find('.wpt-price').text().trim();
            }
            if (!name && $('#wpt-modal-overlay').hasClass('open')) {
                var $m  = $('#wpt-modal-body');
                name    = $m.find('.wpt-qv-title').text().trim();
                var $ai = $m.find('.wpt-slide.active img');
                if (!$ai.length) $ai = $m.find('.wpt-slide').first().find('img');
                imgSrc  = $ai.attr('src') || '';
                price   = $m.find('.wpt-qv-price-current').text().trim();
            }
        }
        if (!name) name = 'Product';
        wptShowToast(name, imgSrc, price ? 'Price: ' + price : 'Successfully added!');
    });

    /* ═══════════════════════════════════════════════
       TOAST NOTIFICATION
    ═══════════════════════════════════════════════ */
    $(document).on('click', '#wpt-toast-close', function () {
        $('#wpt-toast').removeClass('wpt-toast-show');
    });

    var wptToastTimer = null;

    function wptShowToast(name, imgSrc, sub) {
        var $t = $('#wpt-toast');
        $('#wpt-toast-name').text(name || 'Product');
        $('#wpt-toast-sub').text(sub || 'Successfully added!');
        $('#wpt-toast-progress').removeClass('wpt-animate');
        if (imgSrc) { $('#wpt-toast-img').attr('src', imgSrc).show(); }
        else        { $('#wpt-toast-img').hide(); }
        $t.removeClass('wpt-toast-show');
        clearTimeout(wptToastTimer);
        setTimeout(function () {
            $t.addClass('wpt-toast-show');
            setTimeout(function () { $('#wpt-toast-progress').addClass('wpt-animate'); }, 10);
            wptToastTimer = setTimeout(function () { $t.removeClass('wpt-toast-show'); }, 3400);
        }, 20);
    }

});
