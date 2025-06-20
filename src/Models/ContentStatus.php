<?php

namespace Polyctopus\Core\Models;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}