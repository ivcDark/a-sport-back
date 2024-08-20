<?php

namespace App\Contracts;

interface IParseApiFootball
{
    public function start();
    public function json();
    public function toArray();
    public function toObject();
}
