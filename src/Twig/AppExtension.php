<?php

namespace App\Twig;

use App\Util\PvTrans;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('pv_trans', [$this, 'translate']),
        ];
    }

    public function translate($object, string $field, string $locale = 'de')
    {
        return PvTrans::translate($object, $field, $locale);
    }
}