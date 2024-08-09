<?php

function utf8d(? string $string): ?string
{
    if($string)
        return utf8_decode($string);
    else
        return null;
}
