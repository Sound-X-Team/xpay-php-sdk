<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Linting;

use PHPUnit\Framework\TestCase;

/**
 * Tests to verify linting and static analysis configuration
 */
final class LintingTest extends TestCase
{
    public function testPhpStanConfigurationExists(): void
    {
        $configPath = dirname(__DIR__, 3) . '/phpstan.neon';
        $this->assertFileExists($configPath, 'PHPStan configuration file should exist');
        
        $content = file_get_contents($configPath);
        $this->assertStringContainsString('level: 8', $content, 'PHPStan should be configured for level 8');
        $this->assertStringContainsString('src', $content, 'PHPStan should analyze src directory');
    }

    public function testPhpCsFixerConfigurationExists(): void
    {
        $configPath = dirname(__DIR__, 3) . '/.php-cs-fixer.php';
        $this->assertFileExists($configPath, 'PHP CS Fixer configuration file should exist');
        
        $content = file_get_contents($configPath);
        $this->assertStringContainsString('PSR12', $content, 'Should enforce PSR-12 standards');
        $this->assertStringContainsString('src', $content, 'Should analyze src directory');
    }

    public function testMakefileExists(): void
    {
        $makefilePath = dirname(__DIR__, 3) . '/Makefile';
        $this->assertFileExists($makefilePath, 'Makefile should exist for development commands');
        
        $content = file_get_contents($makefilePath);
        $this->assertStringContainsString('lint:', $content, 'Makefile should have lint target');
        $this->assertStringContainsString('test:', $content, 'Makefile should have test target');
        $this->assertStringContainsString('phpstan:', $content, 'Makefile should have phpstan target');
    }

    public function testIdeHelperExists(): void
    {
        $helperPath = dirname(__DIR__, 3) . '/ide-helper.php';
        $this->assertFileExists($helperPath, 'IDE helper file should exist');
        
        $content = file_get_contents($helperPath);
        $this->assertStringContainsString('ClientInterface', $content, 'Should provide PSR HTTP client stubs');
        $this->assertStringContainsString('class Money', $content, 'Should provide Money library stubs');
        $this->assertStringContainsString('Illuminate\\', $content, 'Should provide Laravel stubs');
    }

    public function testComposerScriptsAreConfigured(): void
    {
        $composerPath = dirname(__DIR__, 3) . '/composer.json';
        $this->assertFileExists($composerPath);
        
        $composer = json_decode(file_get_contents($composerPath), true);
        $this->assertArrayHasKey('scripts', $composer);
        
        $scripts = $composer['scripts'];
        $this->assertArrayHasKey('phpstan', $scripts, 'Should have phpstan script');
        $this->assertArrayHasKey('cs-check', $scripts, 'Should have cs-check script');
        $this->assertArrayHasKey('cs-fix', $scripts, 'Should have cs-fix script');
        $this->assertArrayHasKey('lint', $scripts, 'Should have lint script');
        $this->assertArrayHasKey('test', $scripts, 'Should have test script');
    }

    public function testRequiredDevDependenciesArePresent(): void
    {
        $composerPath = dirname(__DIR__, 3) . '/composer.json';
        $composer = json_decode(file_get_contents($composerPath), true);
        
        $devDeps = $composer['require-dev'];
        $this->assertArrayHasKey('phpstan/phpstan', $devDeps, 'PHPStan should be a dev dependency');
        $this->assertArrayHasKey('friendsofphp/php-cs-fixer', $devDeps, 'PHP CS Fixer should be a dev dependency');
        $this->assertArrayHasKey('phpunit/phpunit', $devDeps, 'PHPUnit should be a dev dependency');
    }

    public function testClassesAreProperlyNamespaced(): void
    {
        // Test that our main classes follow PSR-4 autoloading
        $this->assertTrue(class_exists(\XPay\XPay::class), 'Main XPay class should be autoloadable');
        $this->assertTrue(class_exists(\XPay\Http\Client::class), 'HTTP Client should be autoloadable');
        $this->assertTrue(class_exists(\XPay\Types\XPayConfig::class), 'XPayConfig should be autoloadable');
        $this->assertTrue(class_exists(\XPay\Exceptions\XPayException::class), 'XPayException should be autoloadable');
    }

    public function testExceptionHierarchyIsCorrect(): void
    {
        $reflection = new \ReflectionClass(\XPay\Exceptions\ValidationException::class);
        $this->assertTrue(
            $reflection->isSubclassOf(\XPay\Exceptions\XPayException::class),
            'ValidationException should extend XPayException'
        );
        
        $this->assertTrue(
            $reflection->isSubclassOf(\Exception::class),
            'ValidationException should ultimately extend Exception'
        );
    }
}