<?php

namespace App\Enums;

enum PaymentStatus: string
{
   case PENDING = 'pending';
   case SUCCESSFUL = 'successful';
   case FAILED = 'failed';

   /**
    * Get all enum values as an array.
    */
   public static function values(): array
   {
      return array_column(self::cases(), 'value');
   }

   /**
    * Get a comma-separated list of values for readable error messages.
    */
   public static function commaSeparated(): string
   {
      return implode(', ', self::values());
   }
}
