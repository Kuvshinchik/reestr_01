<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tablodata>
 */
class TablodataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Генерируем случайные годы
        $birthYear = $this->faker->numberBetween(1950, 2005);
        $startWorkYear = $birthYear + $this->faker->numberBetween(18, 30);
        
        return [
            'ip' => $this->faker->ipv4(), // Случайный IPv4 адрес
            'height' => $this->faker->numberBetween(150, 210), // Рост от 150 до 210 см
            'width' => $this->faker->numberBetween(50, 150), // Ширина/вес от 50 до 150 кг
            'type' => $this->faker->randomElement(['admin', 'user', 'guest', 'moderator', 'editor']),
            'yearbirthday' => $birthYear,
            'yearbeginworking' => $startWorkYear,
            'foto' => $this->faker->imageUrl(640, 480, 'people', true), // Ссылка на случайное фото
            'qrcode' => $this->faker->regexify('[A-Z0-9]{20}'), // Случайная строка для QR-кода
        ];
    }
    
    /**
     * Дополнительные состояния для фабрики (опционально)
     */
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Tablodata $tablodata) {
            // Действия после создания записи (опционально)
            // Например, логирование или дополнительные операции
        });
    }
    
    /**
     * Состояние для администраторов (опционально)
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'admin',
            'height' => $this->faker->numberBetween(170, 190),
        ]);
    }
    
    /**
     * Состояние для пользователей с определенным ростом (опционально)
     */
    public function tall(): static
    {
        return $this->state(fn (array $attributes) => [
            'height' => $this->faker->numberBetween(190, 210),
        ]);
    }
    
    /**
     * Состояние для определенного года рождения (опционально)
     */
    public function bornInYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'yearbirthday' => $year,
            'yearbeginworking' => $year + $this->faker->numberBetween(18, 25),
        ]);
    }
}