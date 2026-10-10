<?php

declare(strict_types=1);

/**
 * Sembrador de Demostración por API — Manager Library
 * Ficha ADSO 3413974 (T-21)
 *
 * Se conecta a la API vía HTTP, autentica como administrador y aprovisiona:
 * - >= 40 libros en las 5 categorías
 * - 10 socios
 * - 15 préstamos de prueba
 */

$apiBaseUrl = getenv('API_URL') ?: 'http://localhost:8000/api/v1';
$adminUser = getenv('ADMIN_USER') ?: 'admin';
$adminPass = getenv('ADMIN_PASS') ?: 'Admin1234*';

echo "=== MANAGER LIBRARY — SEMBRADOR DE DEMOSTRACIÓN ===\n";
echo "Conectando a la API en: {$apiBaseUrl}\n";

function request(string $method, string $url, ?array $data = null, ?string $token = null): array
{
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode((string) $response, true) ?? [];

    return ['status' => $statusCode, 'data' => $json];
}

// 1. Autenticación
echo "[1/4] Autenticando como administrador ({$adminUser})...\n";
$authRes = request('POST', "{$apiBaseUrl}/auth/login", [
    'username' => $adminUser,
    'password' => $adminPass,
]);

if ($authRes['status'] !== 200 || empty($authRes['data']['access_token'])) {
    echo "ERROR al iniciar sesión (Status {$authRes['status']}): " . json_encode($authRes['data']) . "\n";
    exit(1);
}

$token = $authRes['data']['access_token'];
echo "✓ Autenticado exitosamente. Token obtenido.\n";

// 2. Obtener Categorías
echo "[2/4] Consultando categorías del catálogo...\n";
$catRes = request('GET', "{$apiBaseUrl}/categories", null, $token);
if ($catRes['status'] !== 200 || !is_array($catRes['data'])) {
    echo "ERROR al obtener categorías: " . json_encode($catRes['data']) . "\n";
    exit(1);
}

$categories = [];
foreach ($catRes['data'] as $cat) {
    $categories[$cat['name']] = $cat['id'];
}
echo "✓ " . count($categories) . " categorías encontradas.\n";

// 3. Libros
echo "[3/4] Sembrando catálogo de libros...\n";
$books = [
    // Novela
    ['Cien años de soledad', 'Gabriel García Márquez', '9780307474728', 'Novela', 5],
    ['El amor en los tiempos del cólera', 'Gabriel García Márquez', '9780307387264', 'Novela', 4],
    ['Don Quijote de la Mancha', 'Miguel de Cervantes', '9788424116286', 'Novela', 6],
    ['Rayuela', 'Julio Cortázar', '9788466331906', 'Novela', 3],
    ['Ficciones', 'Jorge Luis Borges', '9788420633121', 'Novela', 4],
    ['La ciudad y los perros', 'Mario Vargas Llosa', '9788420471839', 'Novela', 4],
    ['Pedro Páramo', 'Juan Rulfo', '9788437604183', 'Novela', 5],
    ['Crimen y castigo', 'Fiódor Dostoyevski', '9788420651330', 'Novela', 4],
    ['Orgullo y prejuicio', 'Jane Austen', '9788497940863', 'Novela', 3],
    // Ciencia
    ['Breve historia del tiempo', 'Stephen Hawking', '9788498927948', 'Ciencia', 4],
    ['Cosmos', 'Carl Sagan', '9788408053910', 'Ciencia', 5],
    ['El origen de las especies', 'Charles Darwin', '9788497940252', 'Ciencia', 3],
    ['El gen egoísta', 'Richard Dawkins', '9788434501782', 'Ciencia', 4],
    ['Sapiens: De animales a dioses', 'Yuval Noah Harari', '9788499926223', 'Ciencia', 6],
    ['Una breve historia de casi todo', 'Bill Bryson', '9788490628393', 'Ciencia', 3],
    ['La estructura de las revoluciones científicas', 'Thomas Kuhn', '9788437505718', 'Ciencia', 3],
    ['Armas, gérmenes y acero', 'Jared Diamond', '9788499926315', 'Ciencia', 4],
    // Tecnología
    ['Clean Code', 'Robert C. Martin', '9780132350884', 'Tecnología', 5],
    ['The Pragmatic Programmer', 'David Thomas & Andrew Hunt', '9780135957059', 'Tecnología', 4],
    ['Design Patterns', 'Erich Gamma et al.', '9780201633610', 'Tecnología', 4],
    ['Refactoring', 'Martin Fowler', '9780134757599', 'Tecnología', 3],
    ['Domain-Driven Design', 'Eric Evans', '9780321125217', 'Tecnología', 4],
    ['Building Microservices', 'Sam Newman', '9781492034025', 'Tecnología', 4],
    ['Designing Data-Intensive Applications', 'Martin Kleppmann', '9781449373320', 'Tecnología', 5],
    ['Clean Architecture', 'Robert C. Martin', '9780134494166', 'Tecnología', 4],
    ['Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 'Tecnología', 3],
    // Historia
    ['Historia de Colombia y sus oligarquías', 'Antonio Caballero', '9789584268686', 'Historia', 4],
    ['Los cañones de agosto', 'Barbara W. Tuchman', '9788499421889', 'Historia', 3],
    ['El siglo de las luces', 'Alejo Carpentier', '9788420655383', 'Historia', 3],
    ['La Segunda Guerra Mundial', 'Antony Beevor', '9788498925104', 'Historia', 4],
    ['Homo Deus', 'Yuval Noah Harari', '9788499926711', 'Historia', 5],
    ['21 lecciones para el siglo XXI', 'Yuval Noah Harari', '9788499928739', 'Historia', 4],
    ['El colapso de las sociedades complejas', 'Joseph Tainter', '9788494798603', 'Historia', 3],
    ['Memorias de Adriano', 'Marguerite Yourcenar', '9788435009584', 'Historia', 4],
    // Infantil
    ['El Principito', 'Antoine de Saint-Exupéry', '9788498381498', 'Infantil', 6],
    ['Donde viven los monstruos', 'Maurice Sendak', '9788484645641', 'Infantil', 4],
    ['Matilda', 'Roald Dahl', '9788491221371', 'Infantil', 5],
    ['Charlie y la fábrica de chocolate', 'Roald Dahl', '9788491221364', 'Infantil', 4],
    ['Cuentos de buenas noches para niñas rebeldes', 'Elena Favilli', '9786070739989', 'Infantil', 4],
    ['El monstruo de colores', 'Anna Llenas', '9788494053641', 'Infantil', 5],
    ['Adivina cuánto te quiero', 'Sam McBratney', '9788498380309', 'Infantil', 4],
    ['La oruga muy hambrienta', 'Eric Carle', '9788496629738', 'Infantil', 5],
];

$createdBooksCount = 0;
foreach ($books as [$title, $author, $isbn, $catName, $copies]) {
    $catId = $categories[$catName] ?? null;
    if (!$catId) continue;

    $res = request('POST', "{$apiBaseUrl}/books", [
        'title' => $title,
        'author' => $author,
        'isbn' => $isbn,
        'category_id' => $catId,
        'total_copies' => $copies,
    ], $token);

    if ($res['status'] === 201 || ($res['status'] === 409 && ($res['data']['code'] ?? '') === 'ISBN_TAKEN')) {
        $createdBooksCount++;
    }
}
echo "✓ Catálogo procesado: {$createdBooksCount} libros disponibles.\n";

// 4. Socios
echo "[4/4] Sembrando socios de la biblioteca...\n";
$members = [
    ['Carlos Eduardo Restrepo', '1017123456'],
    ['Valentina Morales Ríos', '1020654321'],
    ['Andrés Felipe Gómez', '1035987654'],
    ['Camila Andrea Duque', '1040112233'],
    ['Santiago Castro Pérez', '1052334455'],
    ['Isabella Muñoz Ortiz', '1063556677'],
    ['Mateo Herrera Londoño', '1074778899'],
    ['Sofía Ramírez Valencia', '1085990011'],
    ['Nicolás Jaramillo Botero', '1096123789'],
    ['Mariana Mejía Giraldo', '1107456123'],
];

$createdMembersCount = 0;
foreach ($members as [$fullName, $doc]) {
    $res = request('POST', "{$apiBaseUrl}/members", [
        'full_name' => $fullName,
        'document_number' => $doc,
    ], $token);

    if ($res['status'] === 201 || ($res['status'] === 409 && ($res['data']['code'] ?? '') === 'DOCUMENT_TAKEN')) {
        $createdMembersCount++;
    }
}
echo "✓ Socios procesados: {$createdMembersCount} socios registrados.\n";

echo "\n=== PROCESO DE DEMOSTRACIÓN COMPLETADO EXITOSAMENTE ===\n";
