/**
 * Image Editor Module Scripts for Phire CMS 2
 */

phire.imageEditor = {
    image     : null,
    sizeOrg   : {w: '', h: ''},
    scaleOrg  : {w: '', h: ''},
    cropOrg   : {top: '', left: ''},
    resizeOrg : {top: '', left: ''},
    gutterX   : 0,
    gutterY   : 0,

    load: function (image, lid) {
        phire.imageEditor.image = image;
        $('#image-name').val(image);
        $('#current_image').val(image);
        $('#lid').val(lid);

        var img = new Image();
        img.onload = function () {
            phire.imageEditor.sizeOrg.w = this.width;
            phire.imageEditor.sizeOrg.h = this.height;

            $('#image-width').val(this.width);
            $('#image-height').val(this.height);

            if ((this.width > $('#image-editor').width()) || (this.height > $('#image-editor').height())) {
                $('#image-editor').css('background-size', 'contain');
                $('#scaled').show('inline');
            } else {
                $('#image-editor').css('background-size', 'auto');
                $('#scaled').hide();
            }

            $('#image-editor').css('background-image', 'url(' + this.src + ')');

            var width = $('#image-editor').width();
            var height = $('#image-editor').height();

            var scale = $('#image-editor').height() / this.height;
            var w = Math.round(this.width * scale);
            var h = $('#image-editor').height();

            if (w > $('#image-editor').width()) {
                scale = $('#image-editor').width() / this.width;
                w = $('#image-editor').width();
                h = Math.round(this.height * scale);
            }

            phire.imageEditor.scaleOrg.w = w;
            phire.imageEditor.scaleOrg.h = h;
            $('#image-scaled-width').val(w);
            $('#image-scaled-height').val(h);
            $('#scaled_w').val(w);
            $('#scaled_h').val(h);
            $('#image-size').show();

            if (w < $('#image-editor').width()) {
                phire.imageEditor.gutterX = ($('#image-editor').width() - w) / 2;
            }
            if (h < $('#image-editor').height()) {
                phire.imageEditor.gutterY = ($('#image-editor').height() - h) / 2;
            }
        };

        img.src = phire.imageEditor.image;

        $('#crop').drag({
            startDrag : function() {
                if (phire.imageEditor.cropOrg.top == '') {
                    phire.imageEditor.cropOrg.top = $('#crop').css('top');
                    phire.imageEditor.cropOrg.left = $('#crop').css('left');
                    phire.imageEditor.resizeOrg.top = $('#resize').css('top');
                    phire.imageEditor.resizeOrg.left = $('#resize').css('left');
                }
            },
            onDrag : function() {
                var x = $('#crop').css('left');
                var y = $('#crop').css('top');
                if ((!$('#crop_to_scale')[0].checked) && (!$('#crop_thumb_to_scale')[0].checked)) {
                    x = Math.round(((x / phire.imageEditor.scaleOrg.w) * phire.imageEditor.sizeOrg.w));
                    y = Math.round(((y / phire.imageEditor.scaleOrg.h) * phire.imageEditor.sizeOrg.h));
                    x = x - Math.round(((phire.imageEditor.gutterX / phire.imageEditor.scaleOrg.w) * phire.imageEditor.sizeOrg.w));
                    y = y - Math.round(((phire.imageEditor.gutterY / phire.imageEditor.scaleOrg.h) * phire.imageEditor.sizeOrg.h));
                } else {
                    x = Math.round(x - phire.imageEditor.gutterX);
                    y = Math.round(y - phire.imageEditor.gutterY);
                }
                $('#crop_x_value').val(x);
                $('#crop_y_value').val(y);
                $('#resize').css('top', ($('#crop').css('top') + $('#crop').css('height') - 2) + 'px');
                $('#resize').css('left', ($('#crop').css('left') + $('#crop').css('width') - 2) + 'px');
            }
        });
        $('#resize').drag({
            onDrag : function() {
                var width = ($('#resize').css('left') - $('#crop').css('left') + 2);
                var height = ($('#resize_action').val() != 'cropToThumb') ? ($('#resize').css('top') - $('#crop').css('top') + 2) : width;
                if (width > 10) {
                    $('#crop').css('width', width + 'px');
                }
                if (height > 10) {
                    $('#crop').css('height', height + 'px');
                }
                if ($('#resize_action').val() != 'cropToThumb') {
                    if (!$('#crop_to_scale')[0].checked) {
                        width = Math.round((width / phire.imageEditor.scaleOrg.w) * phire.imageEditor.sizeOrg.w);
                        height = Math.round((height / phire.imageEditor.scaleOrg.h) * phire.imageEditor.sizeOrg.h);
                    }
                    $('#crop_w_value').val(width);
                    $('#crop_h_value').val(height);
                } else {
                    if (!$('#crop_thumb_to_scale')[0].checked) {
                        width = Math.round((width / phire.imageEditor.scaleOrg.w) * phire.imageEditor.sizeOrg.w);
                    }
                    $('#crop_thumb_value').val(width);
                }
            },
            stopDrag : function () {
                $('#resize').css('top', ($('#crop').css('top') + $('#crop').css('height') - 2) + 'px');
                $('#resize').css('left', ($('#crop').css('left') + $('#crop').css('width') - 2) + 'px');
            }
        });

        $('#resize_action').change(function() {
            var action = $('#resize_action').val();
            $('#resize-value-field').hide();
            $('#resize-to-width-value-field').hide();
            $('#resize-to-height-value-field').hide();
            $('#crop-value-field').hide();
            $('#crop-to-thumb-value-field').hide();
            $('#scale-value-field').hide();

            if ($('#resize_action').val().indexOf('crop') != -1) {
                $('#crop').show();
                $('#resize').show();
                if (action == 'cropToThumb') {
                    $('#crop_thumb_value').val('');
                    $('#crop_thumb_resize_value').val('');
                    $('#crop-to-thumb-value-field').show('inline-block');
                } else {
                    $('#crop_w_value').val('');
                    $('#crop_h_value').val('');
                    $('#crop_resize_value').val('');
                    $('#crop-value-field').show('inline-block');
                }
            } else {
                $('#crop').hide();
                $('#resize').hide();
                $('#crop').css('top', phire.imageEditor.cropOrg.top + 'px');
                $('#crop').css('left', phire.imageEditor.cropOrg.left + 'px');
                $('#crop').css('width', '100px');
                $('#crop').css('height', '100px');

                $('#resize').css('top', phire.imageEditor.resizeOrg.top + 'px');
                $('#resize').css('left', phire.imageEditor.resizeOrg.left + 'px');

                switch (action) {
                    case 'resize':
                        $('#resize-value-field').show('inline-block');
                        break;
                    case 'resizeToWidth':
                        $('#resize-to-width-value-field').show('inline-block');
                        break;
                    case 'resizeToHeight':
                        $('#resize-to-height-value-field').show('inline-block');
                        break;
                    case 'scale':
                        $('#scale-value-field').show('inline-block');
                        break;
                }
            }
        });

        $('#actions').show();
        $('#image-nav > a:first-child').attrib('class', 'nav-on');
    },
    selectImage : function(sel, url) {
        if ($(sel).val() != '----') {
            $.browser.open(url + $(sel).val() + '?editor=phire-image&type=image', 'phireImage', {width: 960, height: 720});
        }
    },
    changeNav : function (i, tab) {
        $('#image-nav > a').attrib('class', 'nav-off');
        $('#image-nav > a:nth-child(' + i + ')').attrib('class', 'nav-on');

        $('#actions').hide();
        $('#adjustments').hide();
        $('#filters').hide();
        $('#rotate').hide();
        $('#layers').hide();

        $('#' + tab).show();
    }
};