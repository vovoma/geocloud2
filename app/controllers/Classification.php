<?php
namespace app\controllers;

use \app\inc\Response;
use \app\inc\Input;

class Classification extends \app\inc\Controller
{
    private $class;

    function __construct()
    {
        $this->class = new \app\models\Classification(Input::getPath()->part(4));
    }

    public function get_index()
    {
        $id = Input::getPath()->part(5);
        return ($id) ? $this->class->get($id) : $this->class->getAll();
    }

    public function post_index()
    {
        return $this->class->insert();
    }

    public function put_index()
    {
        return $this->class->update(Input::getPath()->part(5), json_decode(urldecode(Input::get()))->data);
    }

    public function delete_index()
    {
        return $this->class->destroy(json_decode(Input::get())->data);
    }
    public function put_unique(){
        return $this->class->createUnique(Input::getPath()->part(5));
    }
    public function put_single(){
        return $this->class->createSingle(Input::getPath()->part(5));
    }
}