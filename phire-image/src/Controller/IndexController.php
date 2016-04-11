<?php

namespace Phire\Image\Controller;

use Phire\Image\Model;
use Phire\Controller\AbstractController;

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
            $image->process($this->request->getPost(), $this->application->module('phire-image')['adapter']);
            $this->sess->setRequestValue('saved', true);
            $this->redirect(BASE_PATH . APP_URI . '/image/' . $image->image_id);
        } else {
            $this->view->title = 'Image Editor';
        }

        $this->send();
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
