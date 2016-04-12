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

    load: function (image, lid, noHistory) {
        phire.imageEditor.image = image;
        var basename = image.substring(image.lastIndexOf('/') + 1);
        jax('#image-name').val(image);
        jax('#current_image').val(image);
        jax('#save_as').val(basename);
        jax('#org_name').val(basename);
        jax('#lid').val(lid);

        if ((jax.cookie.load('phire') != '') && !(noHistory)) {
            var phireCookie = jax.cookie.load('phire');
            var json = jax.get(phireCookie.base_path + phireCookie.app_uri + '/image/json?image=' + basename);
            if (json.history.length > 0) {
                for (var i = 0; i < json.history.length; i++) {
                    jax('#image-history').append('a', {
                        "href" : phireCookie.base_path + phireCookie.content_path + "/image-history/" + json.history[i],
                        "onclick" : "phire.imageEditor.load(this.href.substring(window.location.origin.length), 'history', true); return false;",
                        "class" : "small-link"
                    }, json.history[i]);
                }
                jax('#image-history').append('a', {
                    "href" : jax('#current_image').val(),
                    "onclick" : "phire.imageEditor.load(this.href, '" + jax('#lid').val() +"', true); return false;",
                    "class" : "small-link"
                }, 'Current');
                jax('#history_origin_name').val(jax('#current_image').val());
                jax('#image-history').show();
            } else {
                jax('#history_origin_name').val('');
                jax('#image-history').val('');
                jax('#image-history').hide();
            }
        }

        var img = new Image();
        img.onload = function () {
            phire.imageEditor.sizeOrg.w = this.width;
            phire.imageEditor.sizeOrg.h = this.height;

            jax('#image-width').val(this.width);
            jax('#image-height').val(this.height);

            if ((this.width > jax('#image-editor').width()) || (this.height > jax('#image-editor').height())) {
                jax('#image-editor').css('background-size', 'contain');
                jax('#scaled').show('inline');
            } else {
                jax('#image-editor').css('background-size', 'auto');
                jax('#scaled').hide();
            }

            jax('#image-editor').css('background-image', 'url(' + this.src + ')');

            var width = jax('#image-editor').width();
            var height = jax('#image-editor').height();

            var scale = jax('#image-editor').height() / this.height;
            var w = Math.round(this.width * scale);
            var h = jax('#image-editor').height();

            if (w > jax('#image-editor').width()) {
                scale = jax('#image-editor').width() / this.width;
                w = jax('#image-editor').width();
                h = Math.round(this.height * scale);
            }

            phire.imageEditor.scaleOrg.w = w;
            phire.imageEditor.scaleOrg.h = h;
            jax('#image-scaled-width').val(w);
            jax('#image-scaled-height').val(h);
            jax('#scaled_w').val(w);
            jax('#scaled_h').val(h);
            jax('#image-size').show();

            if (w < jax('#image-editor').width()) {
                phire.imageEditor.gutterX = (jax('#image-editor').width() - w) / 2;
            }
            if (h < jax('#image-editor').height()) {
                phire.imageEditor.gutterY = (jax('#image-editor').height() - h) / 2;
            }
        };

        img.src = phire.imageEditor.image;

        jax('#crop').drag({
            startDrag : function() {
                if (phire.imageEditor.cropOrg.top == '') {
                    phire.imageEditor.cropOrg.top = jax('#crop').css('top');
                    phire.imageEditor.cropOrg.left = jax('#crop').css('left');
                    phire.imageEditor.resizeOrg.top = jax('#resize').css('top');
                    phire.imageEditor.resizeOrg.left = jax('#resize').css('left');
                }
            },
            onDrag : function() {
                var x = jax('#crop').css('left');
                var y = jax('#crop').css('top');
                if ((!jax('#crop_to_scale')[0].checked) && (!jax('#crop_thumb_to_scale')[0].checked)) {
                    x = Math.round(((x / phire.imageEditor.scaleOrg.w) * phire.imageEditor.sizeOrg.w));
                    y = Math.round(((y / phire.imageEditor.scaleOrg.h) * phire.imageEditor.sizeOrg.h));
                    x = x - Math.round(((phire.imageEditor.gutterX / phire.imageEditor.scaleOrg.w) * phire.imageEditor.sizeOrg.w));
                    y = y - Math.round(((phire.imageEditor.gutterY / phire.imageEditor.scaleOrg.h) * phire.imageEditor.sizeOrg.h));
                } else {
                    x = Math.round(x - phire.imageEditor.gutterX);
                    y = Math.round(y - phire.imageEditor.gutterY);
                }
                jax('#crop_x_value').val(x);
                jax('#crop_y_value').val(y);
                jax('#resize').css('top', (jax('#crop').css('top') + jax('#crop').css('height') - 2) + 'px');
                jax('#resize').css('left', (jax('#crop').css('left') + jax('#crop').css('width') - 2) + 'px');
            }
        });
        jax('#resize').drag({
            onDrag : function() {
                var width = (jax('#resize').css('left') - jax('#crop').css('left') + 2);
                var height = (jax('#resize_action').val() != 'cropToThumb') ? (jax('#resize').css('top') - jax('#crop').css('top') + 2) : width;
                if (width > 10) {
                    jax('#crop').css('width', width + 'px');
                }
                if (height > 10) {
                    jax('#crop').css('height', height + 'px');
                }
                if (jax('#resize_action').val() != 'cropToThumb') {
                    if (!jax('#crop_to_scale')[0].checked) {
                        width = Math.round((width / phire.imageEditor.scaleOrg.w) * phire.imageEditor.sizeOrg.w);
                        height = Math.round((height / phire.imageEditor.scaleOrg.h) * phire.imageEditor.sizeOrg.h);
                    }
                    jax('#crop_w_value').val(width);
                    jax('#crop_h_value').val(height);
                } else {
                    if (!jax('#crop_thumb_to_scale')[0].checked) {
                        width = Math.round((width / phire.imageEditor.scaleOrg.w) * phire.imageEditor.sizeOrg.w);
                    }
                    jax('#crop_thumb_value').val(width);
                }
            },
            stopDrag : function () {
                jax('#resize').css('top', (jax('#crop').css('top') + jax('#crop').css('height') - 2) + 'px');
                jax('#resize').css('left', (jax('#crop').css('left') + jax('#crop').css('width') - 2) + 'px');
            }
        });

        jax('#resize_action').change(function() {
            var action = jax('#resize_action').val();
            jax('#resize-value-field').hide();
            jax('#resize-to-width-value-field').hide();
            jax('#resize-to-height-value-field').hide();
            jax('#crop-value-field').hide();
            jax('#crop-to-thumb-value-field').hide();
            jax('#scale-value-field').hide();

            if (jax('#resize_action').val().indexOf('crop') != -1) {
                jax('#crop').show();
                jax('#resize').show();
                if (action == 'cropToThumb') {
                    jax('#crop_thumb_value').val('');
                    jax('#crop_thumb_resize_value').val('');
                    jax('#crop-to-thumb-value-field').show('inline-block');
                } else {
                    jax('#crop_w_value').val('');
                    jax('#crop_h_value').val('');
                    jax('#crop_resize_value').val('');
                    jax('#crop-value-field').show('inline-block');
                }
            } else {
                jax('#crop').hide();
                jax('#resize').hide();
                jax('#crop').css('top', phire.imageEditor.cropOrg.top + 'px');
                jax('#crop').css('left', phire.imageEditor.cropOrg.left + 'px');
                jax('#crop').css('width', '100px');
                jax('#crop').css('height', '100px');

                jax('#resize').css('top', phire.imageEditor.resizeOrg.top + 'px');
                jax('#resize').css('left', phire.imageEditor.resizeOrg.left + 'px');

                switch (action) {
                    case 'resize':
                        jax('#resize-value-field').show('inline-block');
                        break;
                    case 'resizeToWidth':
                        jax('#resize-to-width-value-field').show('inline-block');
                        break;
                    case 'resizeToHeight':
                        jax('#resize-to-height-value-field').show('inline-block');
                        break;
                    case 'scale':
                        jax('#scale-value-field').show('inline-block');
                        break;
                }
            }
        });

        jax('#actions').show();
        jax('#image-nav > a:first-child').attrib('class', 'nav-on');
    },
    selectImage : function(sel, url) {
        if (jax(sel).val() != '----') {
            jax.browser.open(url + jax(sel).val() + '?editor=phire-image&type=image', 'phireImage', {width: 960, height: 720});
        }
    },
    changeNav : function (i, tab) {
        jax('#image-nav > a').attrib('class', 'nav-off');
        jax('#image-nav > a:nth-child(' + i + ')').attrib('class', 'nav-on');

        jax('#actions').hide();
        jax('#adjustments').hide();
        jax('#filters').hide();
        jax('#rotate').hide();
        jax('#layers').hide();

        jax('#' + tab).show();
    }
};