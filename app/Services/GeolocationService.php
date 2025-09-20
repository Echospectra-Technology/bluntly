<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeolocationService
{
    private const CACHE_PREFIX = 'geo_location_';
    private const CACHE_TTL = 86400; // 24 hours

    public function getLocationFromIP(string $ipAddress): array
    {
        // Hash IP for privacy
        $ipHash = hash('sha256', $ipAddress);
        $cacheKey = self::CACHE_PREFIX . $ipHash;

        // Check cache first
        $cachedLocation = Cache::get($cacheKey);
        if ($cachedLocation) {
            return $cachedLocation;
        }

        // Don't geolocate private/local IPs
        if ($this->isPrivateIP($ipAddress)) {
            $location = $this->getDefaultLocation();
            Cache::put($cacheKey, $location, self::CACHE_TTL);
            return $location;
        }

        try {
            $location = $this->fetchLocationFromAPI($ipAddress);
            
            // Cache the result
            Cache::put($cacheKey, $location, self::CACHE_TTL);
            
            return $location;
        } catch (\Exception $e) {
            Log::warning('Geolocation API failed', [
                'ip_hash' => $ipHash,
                'error' => $e->getMessage()
            ]);
            
            // Return default location on failure
            $location = $this->getDefaultLocation();
            Cache::put($cacheKey, $location, 3600); // Cache for 1 hour on error
            return $location;
        }
    }

    private function fetchLocationFromAPI(string $ipAddress): array
    {
        // Using ip-api.com (free service, good for development)
        // In production, consider using MaxMind or similar paid service
        $response = Http::timeout(5)->get("http://ip-api.com/json/{$ipAddress}", [
            'fields' => 'status,country,countryCode,region,regionName,city,lat,lon'
        ]);

        if (!$response->successful()) {
            throw new \Exception('Geolocation API request failed');
        }

        $data = $response->json();

        if ($data['status'] !== 'success') {
            throw new \Exception('Geolocation API returned error: ' . ($data['message'] ?? 'Unknown error'));
        }

        return [
            'country_code' => $data['countryCode'] ?? null,
            'country_name' => $data['country'] ?? null,
            'state_code' => $data['region'] ?? null,
            'state_name' => $data['regionName'] ?? null,
            'city' => $data['city'] ?? null,
            'latitude' => $data['lat'] ?? null,
            'longitude' => $data['lon'] ?? null,
            'region' => $this->buildRegionIdentifier($data),
            'ip_hash' => hash('sha256', $ipAddress),
        ];
    }

    private function buildRegionIdentifier(array $geoData): ?string
    {
        $countryCode = $geoData['countryCode'] ?? null;
        $stateCode = $geoData['region'] ?? null;
        $city = $geoData['city'] ?? null;

        if (!$countryCode) {
            return null;
        }

        // Build hierarchical region identifier
        $region = $countryCode;
        
        if ($stateCode) {
            $region .= '-' . $stateCode;
        }
        
        if ($city) {
            $region .= '-' . str_replace(' ', '', $city);
        }

        return $region;
    }

    private function isPrivateIP(string $ip): bool
    {
        return !filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    private function getDefaultLocation(): array
    {
        return [
            'country_code' => null,
            'country_name' => null,
            'state_code' => null,
            'state_name' => null,
            'city' => null,
            'latitude' => null,
            'longitude' => null,
            'region' => 'global',
            'ip_hash' => null,
        ];
    }

    public function getLocationHierarchy(array $location): array
    {
        $hierarchy = ['global'];

        if ($location['country_code']) {
            $hierarchy[] = $location['country_code'];
            
            if ($location['state_code']) {
                $hierarchy[] = $location['country_code'] . '-' . $location['state_code'];
                
                if ($location['city']) {
                    $hierarchy[] = $location['country_code'] . '-' . $location['state_code'] . '-' . str_replace(' ', '', $location['city']);
                }
            }
        }

        return $hierarchy;
    }

    public function calculateRegionScore(string $userRegion, string $postRegion): float
    {
        if ($userRegion === $postRegion) {
            return 1.0; // Perfect match
        }

        // Check if regions share common parts (hierarchical matching)
        $userParts = explode('-', $userRegion);
        $postParts = explode('-', $postRegion);

        $commonParts = 0;
        $maxParts = max(count($userParts), count($postParts));

        for ($i = 0; $i < min(count($userParts), count($postParts)); $i++) {
            if ($userParts[$i] === $postParts[$i]) {
                $commonParts++;
            } else {
                break; // Stop at first mismatch
            }
        }

        if ($commonParts === 0) {
            return 0.0; // No match
        }

        // Score based on hierarchy depth
        // Country match: 0.3, State match: 0.6, City match: 1.0
        switch ($commonParts) {
            case 1: return 0.3; // Country only
            case 2: return 0.6; // Country + State
            case 3: return 0.8; // Country + State + City
            default: return 0.0;
        }
    }
}