<?php
/**
 * Phire Image Module
 *
 * @link       https://github.com/phirecms/phire-image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Image\Controller;

use Phire\Image\Model;
use Phire\Controller\AbstractController;

/**
 * Image Index Controller class
 *
 * @category   Phire\Image
 * @package    Phire\Image
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    1.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index($id = null)
    {
        $this->prepareView('image/index.phtml');

        $libraries = [];
        if (class_exists('Phire\Media\Table\MediaLibraries')) {
            $libs = \Phire\Media\Table\MediaLibraries::findAll(['order' => 'order ASC']);
            foreach ($libs->rows() as $lib) {
                $libraries[$lib->id] = $lib->name;
            }
        }

        $this->view->editor_height = $this->application->module('phire-image')['editor_height'];
        $this->view->libraries     = $libraries;

        if (null !== $id) {
            $media = new \Phire\Media\Model\Media();
            $media->getById($id);

            $this->view->image_id       = $id;
            $this->view->image_file     = $media->file;
            $this->view->library_id     = $media->library_id;
            $this->view->library_folder = $media->library_folder;
        }

        if ($this->request->isPost()) {
            $image = new Model\Image();
            $image->process(
                $this->request->getPost(),
                $this->application->module('phire-image')['adapter'],
                $this->application->module('phire-image')['history']
            );
            $this->sess->setRequestValue('saved', true);
            $this->redirect(BASE_PATH . APP_URI . '/image/' . $image->image_id);
        } else {
            $this->view->title = 'Image Editor';
        }

        $this->send();
    }

    /**
     * JSON action method
     *
     * @return void
     */
    public function json()
    {
        $json = [
            'history' => []
        ];

        if (null !== $this->request->getQuery('image')) {
            $json['history'] = (new Model\Image())->getHistory($this->request->getQuery('image'));
        }

        $this->response->setBody(json_encode($json, JSON_PRETTY_PRINT));
        $this->send(200, ['Content-Type' => 'application/json']);
    }

    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->viewPath = __DIR__ . '/../../view';
        parent::prepareView($template);
    }

}
