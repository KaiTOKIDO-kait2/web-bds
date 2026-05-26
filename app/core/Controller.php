<?php
class Controller
{
    public function model($model)
    {
        if (file_exists('../app/models/' . $model . '.php')) {
            require_once '../app/models/' . $model . '.php';
            return new $model();
        }
        return false;
    }

    public function view($view, $data = [])
    {
        if (file_exists('../app/views/' . $view . '.php')) {
            // Extract data array to variables so views can use $var and $data
            extract($data);
            require_once '../app/views/' . $view . '.php';
        } else {
            die("View " . $view . " does not exist.");
        }
    }
}
