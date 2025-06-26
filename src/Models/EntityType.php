<?php

namespace Polyctopus\Core\Models;

enum EntityType: string
{
    case Content = 'content';
    case Variant = 'variant';
}