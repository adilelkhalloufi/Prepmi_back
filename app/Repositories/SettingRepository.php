<?php

namespace App\Repositories;

use App\Models\Setting;

class SettingRepository
{
    public function create(array $data): Setting
    {
        return Setting::create($data);
    }

    public function find($id): ?Setting
    {
        return Setting::find($id);
    }

    public function findByKey(string $key): ?Setting
    {
        return Setting::where('key', $key)->first();
    }

    public function all()
    {
        return Setting::all();
    }

    public function update(Setting $setting, array $data): bool
    {
        return $setting->update($data);
    }

    public function delete(Setting $setting): bool
    {
        return $setting->delete();
    }

    public function getValue(string $key, $default = null)
    {
        $setting = $this->findByKey($key);
        return $setting ? $setting->value : $default;
    }

    public function setValue(string $key, $value, string $type = 'string', string $description = null): Setting
    {
        $setting = $this->findByKey($key);
        if ($setting) {
            $this->update($setting, [
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]);
            return $setting->fresh();
        } else {
            return $this->create([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'description' => $description,
            ]);
        }
    }
}
