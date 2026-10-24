<?php
namespace App\Model\Enum;

// Valid: no suffix needed when in Enum namespace
enum Status: string {
    case Draft = 'draft';
}

// Also valid: suffix is still allowed
enum StatusEnum: string {
    case Published = 'published';
}
