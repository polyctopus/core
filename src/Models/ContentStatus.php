<?php

namespace Polysync\Core\Models;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}