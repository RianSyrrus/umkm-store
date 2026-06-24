<?php

namespace App\Enums;

enum SaleMode: string
{
    case ReadyStock = 'ready_stock';
    case Preorder = 'preorder';
    case Both = 'both';
}
