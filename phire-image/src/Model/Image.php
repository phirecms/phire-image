<?php

namespace Phire\Image\Model;

use Phire\Model\AbstractModel;

class Image extends AbstractModel
{

    public function process(array $post, $adapter)
    {
        if (strtolower($adapter) == 'gmagick') {
            $image = new \Pop\Image\Gmagick($_SERVER['DOCUMENT_ROOT'] . $post['current_image']);
        } else if (strtolower($adapter) == 'imagick') {
            $image = new \Pop\Image\Imagick($_SERVER['DOCUMENT_ROOT'] . $post['current_image']);
        } else {
            $image = new \Pop\Image\Gd($_SERVER['DOCUMENT_ROOT'] . $post['current_image']);
        }


        if (!empty($post['rotate_value'])) {
            $color = [255, 255, 255];
            if (!empty($post['rotate_bg_color'])) {
                if (strpos($post['rotate_bg_color'], ',') !== false) {
                    $color = explode(',', $post['rotate_bg_color']);
                    foreach ($color as $key => $value) {
                        $color[$key] = trim($value);
                    }
                } else if ((strlen($post['rotate_bg_color']) == 7) && (substr($post['rotate_bg_color'], 0, 1) == '#')) {
                    $hex = substr($post['rotate_bg_color'], 1);
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                    $color = [$r, $g, $b];
                }
            }
            $image->rotate((int)$post['rotate_value'], $color);
        }

        if (!empty($post['resize_action'])) {
            switch ($post['resize_action']) {
                case 'resizeToWidth':
                    $image->resizeToWidth((int)$post['resize_to_width_value']);
                    break;
                case 'resizeToHeight':
                    $image->resizeToHeight((int)$post['resize_to_height_value']);
                    break;
                case 'resize':
                    $image->resize((int)$post['resize_value']);
                    break;
                case 'scale':
                    $image->scale($post['scale_value']);
                    break;
                case 'crop':
                    if (isset($post['crop_to_scale'])) {
                        $image->resizeToWidth((int)$post['scaled_w']);
                    }
                    $image->crop((int)$post['crop_w_value'], (int)$post['crop_h_value'], (int)$post['crop_x_value'], (int)$post['crop_y_value']);
                    break;
                case 'cropToThumb':
                    if (isset($post['crop_thumb_to_scale'])) {
                        $image->resizeToWidth((int)$post['scaled_w']);
                    }
                    $image->cropThumb((int)$post['crop_thumb_value'], (int)$post['crop_x_value'], (int)$post['crop_y_value']);
                    if (!empty($post['crop_thumb_resize_value'])) {
                        $image->resize((int)$post['crop_thumb_resize_value']);
                    }
                    break;
            }
        }

        if (!empty($post['brightness_value'])) {
            $image->adjust->brightness($post['brightness_value']);
        }

        if (!empty($post['contrast_value'])) {
            $image->adjust->contrast($post['contrast_value']);
        }

        if (isset($post['desaturate'])) {
            $image->adjust->desaturate();
        }

        if (!empty($post['sharpen_value'])) {
            $image->filter->sharpen($post['sharpen_value']);
        }

        if (!empty($post['blur_value'])) {
            $image->filter->blur($post['blur_value']);
        }

        if (!empty($post['pixelate_value'])) {
            $image->filter->pixelate($post['pixelate_value']);
        }

        if (!empty($post['border_value'])) {
            $color = [0, 0, 0];
            if (!empty($post['border_color'])) {
                if (strpos($post['border_color'], ',') !== false) {
                    $color = explode(',', $post['border_color']);
                    foreach ($color as $key => $value) {
                        $color[$key] = trim($value);
                    }
                } else if ((strlen($post['border_color']) == 7) && (substr($post['border_color'], 0, 1) == '#')) {
                    $hex = substr($post['border_color'], 1);
                    $r = hexdec(substr($hex, 0, 2));
                    $g = hexdec(substr($hex, 2, 2));
                    $b = hexdec(substr($hex, 4, 2));
                    $color = [$r, $g, $b];
                }
            }
            $image->effect->border($color, (int)$post['border_value']);
        }

        if (!empty($post['negate'])) {
            $image->filter->negate();
        }

        if (!empty($post['quality'])) {
            $image->setQuality($post['quality']);
        }

        if (!empty($post['overlay_value']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $post['overlay_value'])) {
            $image->layer->overlay($_SERVER['DOCUMENT_ROOT'] . $post['overlay_value'], (int)$post['overlay_x_value'], (int)$post['overlay_y_value']);
        }

        $library = new \Phire\Media\Model\MediaLibrary();
        $library->getById($post['lid']);

        if (isset($library->id)) {
            $fileName = (!empty($post['save_as'])) ? $post['save_as'] : $image->getBasename();
            $media    = \Phire\Media\Table\Media::findBy(['file' => $fileName]);

            $image->save($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/' . $library->folder .'/' . $fileName);

            if (!isset($media->id)) {
                $media = new \Phire\Media\Table\Media([
                    'library_id' => $post['lid'],
                    'title'      => ucwords(str_replace(['_', '-'], [' ', ' '], substr($fileName, 0, strrpos($fileName, '.')))),
                    'file'       => $fileName,
                    'size'       => filesize(
                        $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR .
                        $library->folder . DIRECTORY_SEPARATOR . $fileName
                    ),
                    'uploaded'   => date('Y-m-d H:i:s'),
                    'order'      => 0
                ]);
                $media->save();
            } else {
                $media->size = filesize(
                    $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . DIRECTORY_SEPARATOR .
                    $library->folder . DIRECTORY_SEPARATOR . $fileName
                );
                $media->uploaded = date('Y-m-d H:i:s');
                $media->save();
            }


            $m = new \Phire\Media\Model\Media();
            $m->processImage($fileName, $library);

            $this->data['image_id'] = $media->id;
        }
    }

}
