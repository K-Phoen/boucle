const Masonry = require('masonry-layout');
const imagesLoaded = require('imagesloaded');

require('lightgallery.js');
require('lg-hash.js');
require('lg-fullscreen.js');

function initGrid() {
    new Masonry('.grid', {
        itemSelector: '.grid-item',
        columnWidth: 300,
    });
}

function initGallery() {
    lightGallery(document.getElementById('grid'), {
        selector: '.grid-item',
    });
}

imagesLoaded('.grid', function () {
    initGrid();
    initGallery();
});
