<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function positiveId(string $id): int
    {
        $value = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($value === false, 404);

        return $value;
    }
}
