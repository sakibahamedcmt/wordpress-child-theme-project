/* ============================================================
   WooCommerce Custom Product Card — wpc-script.js
   Upload to: /wp-content/themes/your-child-theme/wpc/
   ============================================================ */

jQuery(function ($) {

    var ajaxUrl = wpcData.ajaxUrl;
    var nonce   = wpcData.nonce;

    /* ═══════════════════════════════════════════════
       WISHLIST  (localStorage)
    ═══════════════════════════════════════════════ */
    function getWishlist() {
        try { return JSON.parse(localStorage.getItem('wpc_wishlist') || '[]'); } catch (e) { return []; }
    }
    function saveWishlist(list) {
        localStorage.setItem('wpc_wishlist', JSON.stringify(list));
    }

    // Restore state on page load
    getWishlist().forEach(function (id) {
        $('.wpc-wishlist-btn[data-id="' + id + '"]').addClass('wishlisted').attr('data-tooltip', 'Remove Wishlist');
    });

    $(document).on('click', '.wpc-wishlist-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var id   = $btn.data('id').toString();
        var list = getWishlist();
        var idx  = list.indexOf(id);

        if (idx === -1) {
            list.push(id);
            $btn.addClass('wishlisted').attr('data-tooltip', 'Remove Wishlist');
        } else {
            list.splice(idx, 1);
            $btn.removeClass('wishlisted').attr('data-tooltip', 'Wishlist');
        }
        saveWishlist(list);
        $btn.css('transform', 'scale(1.35)');
        setTimeout(function () { $btn.css('transform', ''); }, 200);
    });


    /* ═══════════════════════════════════════════════
       COMPARE BAR
    ═══════════════════════════════════════════════ */
    var compareList = [];
    try { compareList = JSON.parse(localStorage.getItem('wpc_compare') || '[]'); } catch (e) {}

    function renderCompareBar() {
        if (!compareList.length) {
            $('.wpc-compare-bar').removeClass('open');
            return;
        }
        $('.wpc-compare-bar').addClass('open');
        var html = '';
        compareList.forEach(function (item) {
            html += '<span class="wpc-compare-item">' + item.name +
                    '<button class="wpc-remove-compare" data-id="' + item.id + '">&times;</button></span>';
        });
        $('.wpc-compare-items').html(html);
        $('.wpc-compare-btn').each(function () {
            var inList = compareList.some(function (c) { return c.id === $(this).data('id').toString(); }.bind(this));
            $(this).toggleClass('compared', inList).attr('data-tooltip', inList ? 'Remove Compare' : 'Compare');
        });
    }
    renderCompareBar();

    $(document).on('click', '.wpc-compare-btn', function (e) {
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
        localStorage.setItem('wpc_compare', JSON.stringify(compareList));
        renderCompareBar();
    });

    $(document).on('click', '.wpc-remove-compare', function () {
        var id = $(this).data('id').toString();
        compareList = compareList.filter(function (c) { return c.id !== id; });
        localStorage.setItem('wpc_compare', JSON.stringify(compareList));
        renderCompareBar();
    });

    $(document).on('click', '.wpc-compare-clear', function () {
        compareList = [];
        localStorage.removeItem('wpc_compare');
        renderCompareBar();
        $('.wpc-compare-btn').removeClass('compared').attr('data-tooltip', 'Compare');
    });

    $(document).on('click', '.wpc-compare-go', function () {
        if (compareList.length < 2) { alert('Select at least 2 products.'); return; }
        var ids   = compareList.map(function (c) { return c.id; });
        var query = ids.map(function (id) { return 'ids[]=' + id; }).join('&');
        window.location.href = wpcData.comparePageUrl + '?' + query;
    });


    /* ═══════════════════════════════════════════════
       QUICK VIEW MODAL
    ═══════════════════════════════════════════════ */
    $(document).on('click', '.wpc-quickview-btn', function (e) {
        e.preventDefault();
        var productId = $(this).data('id');
        $('#wpc-modal-body').html('<div class="wpc-modal-loading">Loading...</div>');
        $('#wpc-modal-overlay').addClass('open');
        $('body').css('overflow', 'hidden');

        $.post(ajaxUrl, { action: 'wpc_quick_view', nonce: nonce, product_id: productId }, function (res) {
            if (res.success) {
                $('#wpc-modal-body').html(res.data);
                wpcInitSlider();
                wpcInitQvVariants();
            } else {
                $('#wpc-modal-body').html('<p style="color:red;padding:20px">Failed to load.</p>');
            }
        });
    });

    $(document).on('click', '#wpc-modal-close, #wpc-modal-overlay', function (e) {
        if ($(e.target).is('#wpc-modal-overlay') || $(e.target).is('#wpc-modal-close')) {
            $('#wpc-modal-overlay').removeClass('open');
            $('body').css('overflow', '');
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            $('#wpc-modal-overlay').removeClass('open');
            $('body').css('overflow', '');
        }
    });


    /* ═══════════════════════════════════════════════
       IMAGE SLIDER (inside Quick View)
    ═══════════════════════════════════════════════ */
    function wpcInitSlider() {
        var $slider = $('#wpc-qv-slider');
        if (!$slider.length) return;

        var $track  = $slider.find('.wpc-slider-track');
        var $slides = $slider.find('.wpc-slide');
        var total   = $slides.length;
        var current = 0;

        function goTo(n) {
            if (n < 0) n = total - 1;
            if (n >= total) n = 0;
            current = n;
            $track.css('transform', 'translateX(-' + (current * 100) + '%)');
            $slides.removeClass('active').eq(current).addClass('active');
            $('#wpc-modal-body .wpc-thumb').removeClass('active').eq(current).addClass('active');
        }

        $slider.find('.wpc-slide-prev').off('click').on('click', function (e) { e.stopPropagation(); goTo(current - 1); });
        $slider.find('.wpc-slide-next').off('click').on('click', function (e) { e.stopPropagation(); goTo(current + 1); });

        $(document).off('click.wpc-thumb').on('click.wpc-thumb', '.wpc-thumb', function () {
            goTo($(this).data('index'));
        });
    }


    /* ═══════════════════════════════════════════════
       QUICK VIEW: Variation change
    ═══════════════════════════════════════════════ */
    function wpcInitQvVariants() {
        $(document).off('change.wpc-qv').on('change.wpc-qv', '.wpc-qv-variant', function () {
            var variationId = $(this).find('option:selected').data('variation-id');
            var $modal      = $('#wpc-modal-body');
            if (!variationId) return;

            var $track  = $modal.find('.wpc-slider-track');
            var $slides = $modal.find('.wpc-slide');
            $slides.eq(0).find('img').css('opacity', '0.4');

            $.post(ajaxUrl, { action: 'wpc_variation_image', nonce: nonce, variation_id: variationId }, function (res) {
                if (!res.success) { $slides.eq(0).find('img').css('opacity', '1'); return; }
                var d = res.data;

                if (d.image_url_large) {
                    $slides.eq(0).find('img').attr('src', d.image_url_large).css('opacity', '1');
                    $modal.find('.wpc-thumb').eq(0).find('img').attr('src', d.image_url_large);
                } else {
                    $slides.eq(0).find('img').css('opacity', '1');
                }

                // Reset slider to first slide
                $track.css('transform', 'translateX(0%)');
                $slides.removeClass('active').eq(0).addClass('active');
                $modal.find('.wpc-thumb').removeClass('active').eq(0).addClass('active');

                // Update price
                $modal.find('.wpc-qv-price-current').text(d.price);
                if (d.regular) { $modal.find('.wpc-qv-price-original').text(d.regular).show(); }
                else            { $modal.find('.wpc-qv-price-original').hide(); }

                // Update cart button
                $modal.find('.wpc-cart-btn.ajax_add_to_cart').attr('data-product_id', d.variation_id).data('product_id', d.variation_id);
            });
        });
    }


    /* ═══════════════════════════════════════════════
       CARD: Variation image + price swap
    ═══════════════════════════════════════════════ */
    $(document).on('change', '.wpc-card .wpc-variant', function () {
        var variationId = $(this).find('option:selected').data('variation-id');
        var $card       = $(this).closest('.wpc-card');
        var $img        = $card.find('.wpc-product-img');
        var $btn        = $card.find('.wpc-cart-btn');
        if (!variationId) return;

        $img.css('opacity', '0.4');
        $.post(ajaxUrl, { action: 'wpc_variation_image', nonce: nonce, variation_id: variationId }, function (res) {
            if (!res.success) { $img.css('opacity', '1'); return; }
            var d = res.data;
            if (d.image_url) $img.attr('src', d.image_url);
            $img.css('opacity', '1');
            if (d.price)   $card.find('.wpc-price').text(d.price);
            if (d.regular) { $card.find('.wpc-price-original').text(d.regular).show(); }
            else            { $card.find('.wpc-price-original').hide(); }
            $btn.attr('data-product_id', d.variation_id).data('product_id', d.variation_id);
        });
    });


    /* ═══════════════════════════════════════════════
       ADD TO CART — button states + Astra header update
    ═══════════════════════════════════════════════ */
    $(document).on('adding_to_cart', function (e, $btn) {
        if ($btn && $btn.hasClass('wpc-cart-btn')) {
            $btn.addClass('loading').find('.wpc-btn-text').text('Adding...');
        }
    });

    $(document).on('added_to_cart', function (e, fragments, cart_hash, $btn) {
        // Button state
        if ($btn && $btn.hasClass('wpc-cart-btn')) {
            $btn.removeClass('loading').addClass('added').find('.wpc-btn-text').text('✓ Added!');
            setTimeout(function () {
                $btn.removeClass('added').find('.wpc-btn-text').text('Add to Cart');
            }, 2500);
        }

        // Refresh Astra header cart count
        $(document.body).trigger('wc_fragment_refresh');

        // Toast — detect source
        var name = '', imgSrc = '', price = '';

        if ($btn) {
            // From product card
            var $card = $btn.closest('.wpc-card');
            if ($card.length) {
                name   = $card.find('.wpc-title a').text().trim();
                imgSrc = $card.find('.wpc-product-img').attr('src') || '';
                price  = $card.find('.wpc-price').text().trim();
            }
            // From Quick View modal
            if (!name && $('#wpc-modal-overlay').hasClass('open')) {
                var $m   = $('#wpc-modal-body');
                name     = $m.find('.wpc-qv-title').text().trim();
                var $ai  = $m.find('.wpc-slide.active img');
                if (!$ai.length) $ai = $m.find('.wpc-slide').first().find('img');
                imgSrc   = $ai.attr('src') || '';
                price    = $m.find('.wpc-qv-price-current').text().trim();
            }
            // From compare table
            var $ctRow = $btn.closest('td');
            if ($ctRow.length && !name) {
                var colIdx  = $ctRow.index();
                var $thCell = $btn.closest('table').find('thead tr th').eq(colIdx);
                name   = $thCell.find('.wpc-ct-name a').text().trim();
                imgSrc = $thCell.find('img').attr('src') || '';
            }
            // Native WC button anywhere on page
            if (!name) {
                var $area = $btn.closest('li, article, .product, .entry-summary');
                if ($area.length) {
                    name   = $area.find('.woocommerce-loop-product__title, .product_title, h1, h2, h3').first().text().trim();
                    imgSrc = $area.find('img').first().attr('src') || '';
                    price  = $area.find('.woocommerce-Price-amount').first().text().trim();
                }
            }
        }

        if (!name) name = 'Product';
        wpcShowToast(name, imgSrc, price ? 'Price: ' + price : 'Successfully added!');
    });


    /* ═══════════════════════════════════════════════
       TOAST NOTIFICATION
    ═══════════════════════════════════════════════ */
    $(document).on('click', '#wpc-toast-close', function () {
        $('#wpc-toast').removeClass('wpc-toast-show');
    });

    var wpcToastTimer = null;

    function wpcShowToast(name, imgSrc, sub) {
        var $t = $('#wpc-toast');
        $('#wpc-toast-name').text(name || 'Product');
        $('#wpc-toast-sub').text(sub || 'Successfully added!');
        $('#wpc-toast-progress').removeClass('wpc-toast-animate');

        if (imgSrc) { $('#wpc-toast-img').attr('src', imgSrc).show(); }
        else        { $('#wpc-toast-img').hide(); }

        $t.removeClass('wpc-toast-show');
        clearTimeout(wpcToastTimer);

        setTimeout(function () {
            $t.addClass('wpc-toast-show');
            setTimeout(function () { $('#wpc-toast-progress').addClass('wpc-toast-animate'); }, 10);
            wpcToastTimer = setTimeout(function () { $t.removeClass('wpc-toast-show'); }, 3400);
        }, 20);
    }

});
