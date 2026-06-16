<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QR Code Generator
    |--------------------------------------------------------------------------
    | GD backend — no Imagick required
    */
    'backend' => \BaconQrCode\Renderer\Image\GDLibImageBackEnd::class,
];
