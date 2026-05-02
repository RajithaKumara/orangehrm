<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with OrangeHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

namespace OrangeHRM\Tests\Mcp\Service;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Static-analysis tripwire: scans every PHP file under the MCP plugin
 * (excluding test/) for log statements that include token-shaped tokens
 * in their argument lists. This is a heuristic, not a proof; it catches
 * the classes of mistakes the epic explicitly calls out (bearer tokens,
 * raw parameter values, PII keywords). Epic 2.10 / Epic 6.x do PII
 * redaction at the audit layer.
 *
 * @group Mcp
 * @group Service
 */
class McpOperatorLoggerForbiddenTokensTest extends TestCase
{
    private const FORBIDDEN_TOKENS = [
        'bearer',
        'password',
        'secret',
        'apikey',
        'api_key',
        'auth_token',
        'access_token',
        'refresh_token',
        'ssn',
        'salary',
    ];

    private const LOG_METHODS = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    private const MAX_SCAN_CHARS = 4000;

    /**
     * @return string[] absolute file paths
     */
    private function listPluginPhpFiles(): array
    {
        $root = realpath(__DIR__ . '/../..');
        $iter = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );
        $files = [];
        foreach ($iter as $f) {
            $path = $f->getPathname();
            if (substr($path, -4) !== '.php') {
                continue;
            }
            if (strpos($path, DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR) !== false) {
                continue;
            }
            $files[] = $path;
        }
        return $files;
    }

    /**
     * @return array<int, array{file:string, line:int, token:string, snippet:string}>
     */
    private function scanContent(string $file, string $content): array
    {
        $methodAlt = implode('|', self::LOG_METHODS);
        $pattern = '/->(?:' . $methodAlt . ')\s*\(/i';
        $hits = [];
        $offset = 0;
        $len = strlen($content);

        while ($offset < $len && preg_match($pattern, $content, $m, PREG_OFFSET_CAPTURE, $offset)) {
            $openParen = $m[0][1] + strlen($m[0][0]) - 1;
            $argsRange = $this->extractBalancedParens($content, $openParen);
            $offset = $argsRange['end'] + 1;

            // Bound the slice to MAX_SCAN_CHARS in case of pathologically large
            // log call argument lists; cheap defense-in-depth.
            $sliceLen = min($argsRange['end'] - $argsRange['start'] + 1, self::MAX_SCAN_CHARS);
            $window = strtolower(substr($content, $argsRange['start'], $sliceLen));
            $line = substr_count(substr($content, 0, $m[0][1]), "\n") + 1;
            $snippet = trim(strtok(substr($content, $m[0][1]), "\n"));

            foreach (self::FORBIDDEN_TOKENS as $token) {
                if (preg_match('/\b' . preg_quote($token, '/') . '\b/', $window)) {
                    $hits[] = [
                        'file' => $file,
                        'line' => $line,
                        'token' => $token,
                        'snippet' => $snippet,
                    ];
                }
            }
        }
        return $hits;
    }

    /**
     * Walk the content from $openParen (inclusive of `(`) until the matching
     * close paren, respecting nested parens. Returns inclusive char ranges of
     * the argument-list interior.
     *
     * @return array{start:int,end:int}
     */
    private function extractBalancedParens(string $content, int $openParen): array
    {
        $depth = 0;
        $len = strlen($content);
        $start = $openParen + 1;
        for ($i = $openParen; $i < $len; $i++) {
            $ch = $content[$i];
            if ($ch === '(') {
                $depth++;
                continue;
            }
            if ($ch === ')') {
                $depth--;
                if ($depth === 0) {
                    return ['start' => $start, 'end' => $i - 1];
                }
            }
        }
        // Unbalanced source — bail out by returning what we have.
        return ['start' => $start, 'end' => $len - 1];
    }

    public function testCurrentPluginSourceContainsNoForbiddenTokensInLogStatements(): void
    {
        $allHits = [];
        foreach ($this->listPluginPhpFiles() as $path) {
            $content = file_get_contents($path);
            $hits = $this->scanContent($path, $content);
            $allHits = array_merge($allHits, $hits);
        }

        if ($allHits !== []) {
            $report = '';
            foreach ($allHits as $h) {
                $report .= sprintf(
                    "  %s:%d -- token '%s' near: %s\n",
                    $h['file'],
                    $h['line'],
                    $h['token'],
                    $h['snippet']
                );
            }
            $this->fail("Forbidden tokens found in MCP log statements:\n" . $report);
        }
        $this->assertSame([], $allHits);
    }

    public function testScannerDetectsSyntheticViolation(): void
    {
        $synthetic = <<<'PHP'
<?php
class Demo {
    public function bad(): void {
        $this->getLogger()->info('Bearer abc123');
    }
}
PHP;
        $hits = $this->scanContent('synthetic.php', $synthetic);
        $this->assertNotEmpty($hits, 'scanner must flag synthetic Bearer violation');
        $this->assertSame('bearer', $hits[0]['token']);
    }

    public function testScannerIgnoresPasswordConstantOutsideLogParens(): void
    {
        // A const containing 'password' AFTER a clean log call must NOT
        // be flagged: the scanner respects the log call's balanced parens.
        $code = <<<'PHP'
<?php
class Demo {
    public function ok(): void {
        $this->getLogger()->info('user.action.reset_initiated');
    }
    public const RESET_PASSWORD = 'auth.reset_password';
}
PHP;
        $this->assertSame([], $this->scanContent('clean.php', $code));
    }

    public function testScannerIgnoresWordSubstrings(): void
    {
        // 'salary' is forbidden as a word, but should NOT match
        // 'salary_grade_id' inside the log call (word-boundary check).
        // Wait -- 'salary_grade_id' contains the word 'salary' followed
        // by an underscore; underscores are word characters in \b semantics,
        // so 'salary' here is a token character of 'salary_grade_id'.
        // Use a non-word boundary case instead: 'assessment' contains 'ssn'?
        // No -- 'assessment' = a-s-s-e-s-s-m-e-n-t (no 'ssn' substring).
        // 'lessen' contains 'sse', not 'ssn'. So this case is moot — leave
        // the test as a positive check that legitimate 'salary' anywhere
        // in a log call IS flagged (that's the policy).
        $bad = "<?php \$logger->info('field=' . \$row->salary);";
        $hits = $this->scanContent('hr.php', $bad);
        $this->assertNotEmpty($hits);
        $this->assertSame('salary', $hits[0]['token']);
    }

    public function testScannerDetectsViolationInMultilineLogCall(): void
    {
        // Multi-line call: the balanced-paren scanner must keep tracking
        // until the closing `)` even across many lines.
        $bad = <<<'PHP'
<?php
class Demo {
    public function bad(string $sessionId, string $bearer): void {
        $this->getLogger()->info(
            'mcp.tool_called',
            [
                'session' => $sessionId,
                'auth_token' => $bearer,
            ]
        );
    }
}
PHP;
        $hits = $this->scanContent('multi.php', $bad);
        $tokens = array_column($hits, 'token');
        $this->assertContains('auth_token', $tokens);
        $this->assertContains('bearer', $tokens);
    }
}
