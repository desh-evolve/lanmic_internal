<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

class Location
{
    /**
     * Get all locations from JSON.
     */
    public static function all()
    {
        if (Storage::exists('locations.json')) {
            $json = Storage::get('locations.json');
            $data = json_decode($json, true);
            return collect($data);
        }
        return collect([]);
    }

    /**
     * Get location by ID.
     */
    public static function find($id)
    {
        return self::all()->firstWhere('id', $id);
    }

    /**
     * Get locations by type.
     */
    public static function byType($type)
    {
        return self::all()->where('type', $type);
    }

    /**
     * Get used locations.
     */
    public static function used()
    {
        return self::byType('used');
    }

    /**
     * Get all except used locations.
     */
    public static function exceptUsed()
    {
        return self::all()->where('type', '!=', 'used');
    }
}