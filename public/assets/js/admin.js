(function () {
    'use strict';

    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var message = form.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    var input = document.querySelector('[data-image-input]');
    var preview = document.querySelector('.media-field__preview');

    if (input && preview) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file || !/^image\//.test(file.type)) {
                return;
            }

            var image = preview.querySelector('img');
            if (!image) {
                image = document.createElement('img');
                image.width = 240;
                image.height = 180;
                image.alt = '';
                preview.innerHTML = '';
                preview.appendChild(image);
            }

            image.src = URL.createObjectURL(file);
        });
    }
}());
