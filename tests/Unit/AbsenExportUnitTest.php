<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Http\Controllers\AbsenController;
use ReflectionMethod;

class AbsenExportUnitTest extends TestCase
{
    private function getClassSortKey(AbsenController $controller, ?string $kelas): string
    {
        $reflection = new ReflectionMethod(AbsenController::class, 'getClassSortKey');
        return $reflection->invoke($controller, $kelas);
    }

    public function test_class_sorting_key_order()
    {
        $controller = new AbsenController();

        $classes = [
            'XII-RPL 10',
            'X-RPL 1',
            'XI-RPL 2',
            'X-RPL 2',
            'X-RPL 10',
            'XI-RPL 1',
        ];

        usort($classes, function ($a, $b) use ($controller) {
            return strcmp($this->getClassSortKey($controller, $a), $this->getClassSortKey($controller, $b));
        });

        $expected = [
            'X-RPL 1',
            'X-RPL 2',
            'X-RPL 10',
            'XI-RPL 1',
            'XI-RPL 2',
            'XII-RPL 10',
        ];

        $this->assertEquals($expected, array_values($classes));
    }
}
