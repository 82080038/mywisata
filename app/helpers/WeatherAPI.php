<?php
/**
 * MyWisata Application - Weather API Helper
 * 
 * Handles weather forecast API integration using OpenWeatherMap.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-16
 */

class WeatherAPI {
    
    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5';
    
    public function __construct() {
        $this->apiKey = getenv('OPENWEATHER_API_KEY') ?: '';
    }
    
    /**
     * Get current weather for a city
     * 
     * @param string $city City name
     * @param string $countryCode Country code (optional)
     * @return array|false
     */
    public function getCurrentWeather($city, $countryCode = 'ID') {
        if (empty($this->apiKey)) {
            return $this->getMockWeather();
        }
        
        $location = $countryCode ? "{$city},{$countryCode}" : $city;
        $url = "{$this->baseUrl}/weather?q=" . urlencode($location) . "&appid={$this->apiKey}&units=metric";
        
        $response = $this->makeRequest($url);
        
        if ($response) {
            return $this->formatCurrentWeather($response);
        }
        
        return $this->getMockWeather();
    }
    
    /**
     * Get weather forecast for a city
     * 
     * @param string $city City name
     * @param string $countryCode Country code (optional)
     * @param int $days Number of days (max 5)
     * @return array|false
     */
    public function getForecast($city, $countryCode = 'ID', $days = 5) {
        if (empty($this->apiKey)) {
            return $this->getMockForecast($days);
        }
        
        $location = $countryCode ? "{$city},{$countryCode}" : $city;
        $url = "{$this->baseUrl}/forecast?q=" . urlencode($location) . "&appid={$this->apiKey}&units=metric&cnt=" . ($days * 8);
        
        $response = $this->makeRequest($url);
        
        if ($response) {
            return $this->formatForecast($response, $days);
        }
        
        return $this->getMockForecast($days);
    }
    
    /**
     * Get weather by coordinates
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return array|false
     */
    public function getWeatherByCoords($lat, $lon) {
        if (empty($this->apiKey)) {
            return $this->getMockWeather();
        }
        
        $url = "{$this->baseUrl}/weather?lat={$lat}&lon={$lon}&appid={$this->apiKey}&units=metric";
        
        $response = $this->makeRequest($url);
        
        if ($response) {
            return $this->formatCurrentWeather($response);
        }
        
        return $this->getMockWeather();
    }
    
    /**
     * Make HTTP request to API
     * 
     * @param string $url API URL
     * @return array|false
     */
    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }
        
        return false;
    }
    
    /**
     * Format current weather response
     * 
     * @param array $response API response
     * @return array
     */
    private function formatCurrentWeather($response) {
        return [
            'city' => $response['name'],
            'country' => $response['sys']['country'],
            'temperature' => round($response['main']['temp']),
            'feels_like' => round($response['main']['feels_like']),
            'humidity' => $response['main']['humidity'],
            'pressure' => $response['main']['pressure'],
            'wind_speed' => round($response['wind']['speed'] * 3.6), // Convert m/s to km/h
            'description' => $response['weather'][0]['description'],
            'icon' => $response['weather'][0]['icon'],
            'condition' => $this->mapCondition($response['weather'][0]['main']),
            'timestamp' => $response['dt']
        ];
    }
    
    /**
     * Format forecast response
     * 
     * @param array $response API response
     * @param int $days Number of days
     * @return array
     */
    private function formatForecast($response, $days) {
        $forecast = [];
        $dailyData = [];
        
        // Group by date
        foreach ($response['list'] as $item) {
            $date = date('Y-m-d', $item['dt']);
            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [];
            }
            $dailyData[$date][] = $item;
        }
        
        // Get daily forecast (midday data)
        $count = 0;
        foreach ($dailyData as $date => $items) {
            if ($count >= $days) break;
            
            // Get midday data (around 12:00)
            $midday = $items[0];
            foreach ($items as $item) {
                $hour = date('H', $item['dt']);
                if (abs($hour - 12) < abs(date('H', $midday['dt']) - 12)) {
                    $midday = $item;
                }
            }
            
            $forecast[] = [
                'date' => $date,
                'day_name' => date('l', strtotime($date)),
                'temperature' => round($midday['main']['temp']),
                'min_temp' => round(min(array_column($items, 'main'))['temp']),
                'max_temp' => round(max(array_column($items, 'main'))['temp']),
                'humidity' => $midday['main']['humidity'],
                'description' => $midday['weather'][0]['description'],
                'icon' => $midday['weather'][0]['icon'],
                'condition' => $this->mapCondition($midday['weather'][0]['main'])
            ];
            
            $count++;
        }
        
        return $forecast;
    }
    
    /**
     * Map weather condition to user-friendly text
     * 
     * @param string $condition API condition
     * @return string
     */
    private function mapCondition($condition) {
        $map = [
            'Clear' => 'Cerah',
            'Clouds' => 'Berawan',
            'Rain' => 'Hujan',
            'Drizzle' => 'Gerimis',
            'Thunderstorm' => 'Badai Petir',
            'Snow' => 'Salju',
            'Mist' => 'Kabut',
            'Fog' => 'Kabut Tebal',
            'Haze' => 'Kabut Asap'
        ];
        
        return $map[$condition] ?? $condition;
    }
    
    /**
     * Get mock weather data (for testing when API key not available)
     * 
     * @return array
     */
    private function getMockWeather() {
        return [
            'city' => 'Jakarta',
            'country' => 'ID',
            'temperature' => 28,
            'feels_like' => 30,
            'humidity' => 75,
            'pressure' => 1013,
            'wind_speed' => 10,
            'description' => 'partly cloudy',
            'icon' => '03d',
            'condition' => 'Berawan',
            'timestamp' => time()
        ];
    }
    
    /**
     * Get mock forecast data
     * 
     * @param int $days Number of days
     * @return array
     */
    private function getMockForecast($days) {
        $forecast = [];
        $conditions = ['Cerah', 'Berawan', 'Hujan', 'Gerimis'];
        
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            $forecast[] = [
                'date' => $date,
                'day_name' => date('l', strtotime($date)),
                'temperature' => rand(25, 32),
                'min_temp' => rand(22, 26),
                'max_temp' => rand(30, 35),
                'humidity' => rand(60, 85),
                'description' => strtolower($conditions[array_rand($conditions)]),
                'icon' => '03d',
                'condition' => $conditions[array_rand($conditions)]
            ];
        }
        
        return $forecast;
    }
    
    /**
     * Get weather recommendation based on conditions
     * 
     * @param array $weather Weather data
     * @return array
     */
    public function getRecommendation($weather) {
        $recommendations = [];
        
        if ($weather['temperature'] > 30) {
            $recommendations[] = 'Gunakan sunscreen dan topi';
            $recommendations[] = 'Bawa air minum yang cukup';
        }
        
        if ($weather['humidity'] > 80) {
            $recommendations[] = 'Kondisi lembab, gunakan pakaian yang menyerap keringat';
        }
        
        if (in_array($weather['condition'], ['Hujan', 'Badai Petir', 'Gerimis'])) {
            $recommendations[] = 'Bawa payung atau jas hujan';
            $recommendations[] = 'Gunakan sepatu anti-slip';
        }
        
        if ($weather['wind_speed'] > 20) {
            $recommendations[] = 'Angin kencang, hati-hati saat aktivitas outdoor';
        }
        
        if (empty($recommendations)) {
            $recommendations[] = 'Cuaca cerah, cocok untuk aktivitas outdoor';
        }
        
        return $recommendations;
    }
}
