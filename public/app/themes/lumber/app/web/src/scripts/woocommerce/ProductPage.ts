import $ from "jquery";
import GLightbox from 'glightbox';

$(document).ready(() => {
    if ($(".woocommerce-page.single-product").length) {
        const lightbox = GLightbox({ 
            selector: ".woocommerce-product-gallery__wrapper a"
        });
    }
});