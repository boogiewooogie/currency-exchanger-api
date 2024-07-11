<?php

namespace App\Response;

enum StatusThesaurus: string
{
    case Success = 'success';

    case Error = 'error';
}