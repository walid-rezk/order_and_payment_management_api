<?php

namespace App\Enums;

enum OrderStatus: string
{
   case PENDING = 'pending';
   case CONFIRMED = 'confirmed';
   case CANCELLED = 'cancelled';

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
