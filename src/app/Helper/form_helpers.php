<?php

if (! function_exists('oldSessionOrModel')) {

  function oldSessionOrModel(
    string $key,
    ?array $session = null,
    ?object $model = null,
    mixed $default = null)
  {
    if (! is_null(old($key))) {
      return old($key);
    }

    if (isset($session[$key])) {
      return $session[$key];
    }

    if ($model && isset($model->{$key})) {
      return $model->{$key};
    }

    return $default;
  }

}