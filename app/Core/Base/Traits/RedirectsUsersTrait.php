<?php

namespace App\Core\Base\Traits;

/**
 * Trait RedirectsUsersTrait
 *
 * @package Illuminate\Foundation\Auth
 */
trait RedirectsUsersTrait
{
    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath(): string
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }
}
