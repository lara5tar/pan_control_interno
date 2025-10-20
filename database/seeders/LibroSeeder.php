<?php

namespace Database\Seeders;

use App\Models\Libro;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LibroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $libros = [
            [
                'nombre' => 'Cien años de soledad',
                'codigo_barras' => '9780307474728',
                'precio' => 299.99,
                'stock' => 15,
            ],
            [
                'nombre' => 'Don Quijote de la Mancha',
                'codigo_barras' => '9788424116163',
                'precio' => 349.00,
                'stock' => 8,
            ],
            [
                'nombre' => 'El principito',
                'codigo_barras' => '9788478887194',
                'precio' => 189.50,
                'stock' => 25,
            ],
            [
                'nombre' => '1984',
                'codigo_barras' => '9780451524935',
                'precio' => 259.99,
                'stock' => 12,
            ],
            [
                'nombre' => 'Rayuela',
                'codigo_barras' => '9788420471358',
                'precio' => 279.00,
                'stock' => 5,
            ],
            [
                'nombre' => 'El amor en los tiempos del cólera',
                'codigo_barras' => '9780307387387',
                'precio' => 319.99,
                'stock' => 20,
            ],
            [
                'nombre' => 'Crónica de una muerte anunciada',
                'codigo_barras' => '9780307475572',
                'precio' => 249.00,
                'stock' => 18,
            ],
            [
                'nombre' => 'Pedro Páramo',
                'codigo_barras' => '9788493440428',
                'precio' => 199.99,
                'stock' => 10,
            ],
        ];

        foreach ($libros as $libro) {
            Libro::create($libro);
        }
    }
}
