<?php

namespace Phire\Image\Model;

use Phire\Model\AbstractModel;
use Pop\File\Dir;
use Pop\File\Upload;

class Image extends AbstractModel
{

    public function process(array $post, $adapter, $history)
    {

        if (($post['lid'] == 'history') && !empty($post['history_origin_name'])) {
            $basename = basename($post['history_origin_name']);
            $orgMedia = \Phire\Media\Table\Media::findBy(['file' => $basename]);
            if (isset($orgMedia->id)) {
                $library = new \Phire\Media\Model\MediaLibrary();
                $library->getById($orgMedia->library_id);
                $post['save_as']  = $basename;
                $post['org_name'] = $basename;
            }
        } else {
            $library = new \Phire\Media\Model\MediaLibrary();
            $library->getById($post['lid']);
        }

        if (isset($library) && isset($library->id) && ($post['save_as'] == $post['org_name']) && ($history > 0)) {
            $historyFolder   = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history';
            $historyFileName = (new Upload($historyFolder))->checkFilename($post['org_name']);

            $historyList = $this->getHistory($post['org_name']);
            if ((count($historyList) > $history) && file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history/' . $historyList[0])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history/' . $historyList[0]);
            }
            copy(
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/' . $library->folder . '/' . $post['org_name'],
                $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history/' . $historyFileName
            );
        }

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

        if (!empty($post['image_flip_flop'])) {
            if ($post['image_flip_flop'] == 'flip') {
                $image->flip();
            } else {
                $image->flop();
            }
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
                    if (!empty($post['crop_resize_value'])) {
                        $image->resize((int)$post['crop_resize_value']);
                    }
                    break;
                case 'cropToThumb':
                    if (isset($post['crop_thumb_to_scale'])) {
                        $image->resizeToWidth((int)$post['scaled_w']);
                    }
                    $image->crop((int)$post['crop_thumb_value'], (int)$post['crop_thumb_value'], (int)$post['crop_x_value'], (int)$post['crop_y_value']);
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

    public function getHistory($filename)
    {
        $history = [];

        $dir = new Dir($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/image-history');

        foreach ($dir->getFiles() as $file) {
            if ($file == $filename) {
                $history[] = $file;
            } else if (strpos($file, '_') !== false) {
                $basename = str_replace(['-', '_'], ['\-', '\_'], substr($filename, 0, strrpos($filename, '.')));
                $ext      = substr($filename, (strrpos($filename, '.') + 1));
                $regex    = '/(' . $basename . ')\_+(\d.*)\.' . $ext . '/';
                if (preg_match($regex, $file)) {
                    $history[] = $file;
                }
            }
        }

        sort($history, SORT_NATURAL);
        return $history;
    }

}
