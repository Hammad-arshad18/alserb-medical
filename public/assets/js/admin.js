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

    if (!input) {
        return;
    }

    var maxBytes = parseInt(input.getAttribute('data-max-bytes'), 10) || 4 * 1024 * 1024;
    var maxDimension = parseInt(input.getAttribute('data-max-dimension'), 10) || 1800;
    var form = input.form;
    var submit = form ? form.querySelector('button[type="submit"]') : null;
    var help = input.parentNode ? input.parentNode.querySelector('.field__help') : null;
    var helpText = help ? help.textContent : '';
    var busy = false;

    function setBusy(state) {
        busy = state;
        if (submit) {
            submit.disabled = state;
        }
        if (help) {
            help.textContent = state ? 'Preparing image…' : helpText;
        }
    }

    function formatSize(bytes) {
        if (bytes >= 1024 * 1024) {
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        return Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    function showPreview(file) {
        if (!preview) {
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

        var url = URL.createObjectURL(file);
        image.onload = function () {
            URL.revokeObjectURL(url);
        };
        image.src = url;
    }

    function preparedFile(blob, originalName) {
        var base = originalName.replace(/\.[^.]+$/, '') || 'image';

        return new File([blob], base + '.jpg', {
            type: 'image/jpeg',
            lastModified: Date.now()
        });
    }

    /**
     * Shrinks oversized photos in the browser so uploads stay under both the
     * application limit and PHP's upload_max_filesize.
     */
    function prepare(file) {
        return new Promise(function (resolve) {
            var url = URL.createObjectURL(file);
            var image = new Image();

            image.onload = function () {
                URL.revokeObjectURL(url);

                var widest = Math.max(image.width, image.height);
                var scale = Math.min(1, maxDimension / widest);

                if (scale === 1 && file.size <= maxBytes) {
                    resolve(file);
                    return;
                }

                var canvas = document.createElement('canvas');
                var context = canvas.getContext('2d');

                function encode(width, height, quality, attempt) {
                    canvas.width = width;
                    canvas.height = height;
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, width, height);
                    context.drawImage(image, 0, 0, width, height);

                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            resolve(file);
                            return;
                        }

                        if (blob.size <= maxBytes || attempt >= 4) {
                            resolve(preparedFile(blob, file.name));
                            return;
                        }

                        if (quality > 0.6) {
                            encode(width, height, quality - 0.12, attempt + 1);
                            return;
                        }

                        encode(
                            Math.max(640, Math.round(width * 0.8)),
                            Math.max(640, Math.round(height * 0.8)),
                            0.82,
                            attempt + 1
                        );
                    }, 'image/jpeg', quality);
                }

                encode(
                    Math.max(1, Math.round(image.width * scale)),
                    Math.max(1, Math.round(image.height * scale)),
                    0.86,
                    0
                );
            };

            image.onerror = function () {
                URL.revokeObjectURL(url);
                resolve(file);
            };

            image.src = url;
        });
    }

    input.addEventListener('change', function () {
        var file = input.files && input.files[0];

        if (!file || !/^image\//.test(file.type)) {
            return;
        }

        showPreview(file);

        if (!/^image\/(jpeg|png|webp)$/.test(file.type) || typeof DataTransfer === 'undefined') {
            return;
        }

        setBusy(true);

        prepare(file).then(function (prepared) {
            if (prepared !== file) {
                var transfer = new DataTransfer();
                transfer.items.add(prepared);
                input.files = transfer.files;
                showPreview(prepared);
            }

            setBusy(false);

            if (help) {
                help.textContent = 'Ready to upload (' + formatSize(prepared.size) + ').';
            }
        });
    });

    if (form) {
        form.addEventListener('submit', function (event) {
            if (busy) {
                event.preventDefault();
            }
        });
    }
}());
