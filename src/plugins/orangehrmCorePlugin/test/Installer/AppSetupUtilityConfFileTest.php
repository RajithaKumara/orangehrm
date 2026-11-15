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

namespace OrangeHRM\Tests\Core\Installer;

use InvalidArgumentException;
use OrangeHRM\Authentication\Dto\UserCredential;
use OrangeHRM\Config\Config;
use OrangeHRM\Framework\Http\Session\MemorySessionStorage;
use OrangeHRM\Framework\Http\Session\Session;
use OrangeHRM\Framework\ServiceContainer;
use OrangeHRM\Framework\Services;
use OrangeHRM\Installer\Util\AppSetupUtility;
use OrangeHRM\Installer\Util\StateContainer;
use OrangeHRM\Tests\Util\TestCase;

/**
 * Database details written to `lib/confs/Conf.php` by the installer and upgrader.
 *
 * @group Core
 * @group Installer
 */
class AppSetupUtilityConfFileTest extends TestCase
{
    private const MARKER_FLAG = 'ohrmConfFileTestMarker';

    private ?string $confPath = null;
    private ?string $previousConfPath = null;
    /** @var Session|null */
    private $previousSession = null;

    /**
     * Every value below in every field, one field at a time.
     */
    public function fieldValueDataProvider(): array
    {
        $cases = [];
        foreach ($this->valueDataProvider() as $name => [$value]) {
            foreach (['dbHost', 'dbPort', 'dbName', 'dbUser', 'dbPass'] as $field) {
                $cases["$field: $name"] = [$field, $value];
            }
        }
        return $cases;
    }

    public function valueDataProvider(): array
    {
        $statement = "\$GLOBALS['" . self::MARKER_FLAG . "'] = true;";
        return [
            'quote closes the literal' => ["x';$statement//"],
            'escaped quote' => ["x\\';$statement//"],
            'trailing backslash' => ['secret\\'],
            'double backslash' => ['a\\\\b'],
            'backslash before quote' => ["a\\'b"],
            'only quotes and backslashes' => ["'\\'\\\\''"],
            'dollar and braces' => ['p$a{$s}${w}'],
            'double quotes' => ['say "hi"'],
            'placeholder of another field' => ['{{dbPass}}{{dbHost}}'],
            'closing tag' => ["?><?php $statement ?>"],
            'heredoc and comment markers' => ["*/ /* <<<EOT\nEOT; # //"],
            'newline, tab, carriage return' => ["line1\nline2\r\n\tend"],
            'NUL byte' => ["a\0b"],
            'unicode' => ['pässwörd-密码-🔑'],
            'invalid UTF-8' => ["\xC3\x28\xFF"],
            'empty' => [''],
            'ordinary password' => ['Ohrm@1423#Secure!'],
        ];
    }

    /**
     * @dataProvider fieldValueDataProvider
     */
    public function testValueIsWrittenAsData(string $field, string $value): void
    {
        $expected = [
            'dbHost' => 'localhost',
            'dbPort' => '3306',
            'dbName' => 'orangehrm',
            'dbUser' => 'ohrm_user',
            'dbPass' => 'ohrm_pass',
        ];
        $expected[$field] = $value;

        $conf = $this->writeAndLoadConf(
            $expected['dbHost'],
            $expected['dbPort'],
            $expected['dbName'],
            new UserCredential($expected['dbUser'], $expected['dbPass'])
        );

        $this->assertArrayNotHasKey(self::MARKER_FLAG, $GLOBALS, "$field was not written as a plain string");
        $this->assertSame($expected['dbHost'], $conf->getDbHost());
        $this->assertSame($expected['dbPort'], $conf->getDbPort());
        $this->assertSame($this->expectedDbName($expected['dbName']), $conf->getDbName());
        $this->assertSame($expected['dbUser'], $conf->getDbUser());
        $this->assertSame($expected['dbPass'], $conf->getDbPass());
    }

    /**
     * PHPUnit runs with ENVIRONMENT=test, so the production `dbName` line is checked as text.
     */
    public function testProductionDatabaseNameLineHoldsTheEscapedValue(): void
    {
        $dbName = "n';\$GLOBALS['" . self::MARKER_FLAG . "'] = true;//\\";
        $content = (new AppSetupUtility())->getConfFileContent(
            $this->getDbInfo('localhost', '3306', $dbName, 'root', 'pw')
        );

        $this->assertStringContainsString(
            "\$this->dbName = 'n\\';\$GLOBALS[\\'" . self::MARKER_FLAG . "\\'] = true;//\\\\';",
            $content
        );
    }

    public function testNonAsciiValuesAreWrittenAsAsciiOnly(): void
    {
        $content = (new AppSetupUtility())->getConfFileContent($this->getDbInfo(
            "h\x83\\",
            '3306',
            "\xC3\x28\xFF",
            'üser',
            'pässwörd-密码'
        ));

        $this->assertSame(0, preg_match('/[\x80-\xFF]/', $content));
        $this->assertStringContainsString(
            "\$this->dbPass = 'p' . hex2bin('c3a4') . 'ssw' . hex2bin('c3b6') . 'rd-'"
            . " . hex2bin('e5af86e7a081') . '';",
            $content
        );
        $this->assertStringContainsString("\$this->dbHost = 'h' . hex2bin('83') . '\\\\';", $content);
    }

    public function multibyteSourceEncodingDataProvider(): array
    {
        return [
            'SJIS' => ['SJIS', null],
            'SJIS, internal_encoding SJIS' => ['SJIS', 'SJIS'],
            'CP932, internal_encoding CP932' => ['CP932', 'CP932'],
            'EUC-JP' => ['EUC-JP', null],
            'BIG5, internal_encoding BIG5' => ['BIG5', 'BIG5'],
            'GBK (CP936), internal_encoding CP936' => ['CP936', 'CP936'],
        ];
    }

    /**
     * With zend.multibyte and e.g. a Shift_JIS source encoding, a lead byte followed by an inserted `\` would
     * read as one character, and raw high bytes may be converted. No byte >= 0x80 is written, so the values
     * still read back exactly and nothing runs. The file is loaded in a separate PHP process without
     * ENVIRONMENT, so the production `dbName` line is the one read there.
     *
     * @dataProvider multibyteSourceEncodingDataProvider
     */
    public function testValuesReadBackUnderMultibyteSourceEncoding(string $scriptEncoding, ?string $internal): void
    {
        $php = $this->getPhpCliWithMbstring();
        $values = [
            "x\x83';echo \"MARKER\";//\\",
            ';echo "MARKER";//',
            "n\x83';echo \"MARKER\";//\\",
            "u\x95';echo \"MARKER\";//",
            'pässwörd-密码',
        ];
        file_put_contents(
            $this->confPath,
            (new AppSetupUtility())->getConfFileContent($this->getDbInfo(...$values))
        );

        $script = 'include ' . var_export($this->confPath, true) . '; $c = new Conf(); echo PHP_EOL, "RESULT:",'
            . ' json_encode(array_map("bin2hex", [$c->getDbHost(), $c->getDbPort(), $c->getDbName(),'
            . ' $c->getDbUser(), $c->getDbPass()]));';
        $options = '-d zend.multibyte=1 -d ' . escapeshellarg("zend.script_encoding=$scriptEncoding")
            . ($internal ? ' -d ' . escapeshellarg("internal_encoding=$internal") : '');
        $errorPath = tempnam(sys_get_temp_dir(), 'ohrm_conf_err_');
        $this->assertNotFalse($errorPath);
        try {
            exec("$php $options -r " . escapeshellarg($script) . ' 2>' . escapeshellarg($errorPath), $output, $code);
            $errors = (string)file_get_contents($errorPath);
        } finally {
            @unlink($errorPath);
        }

        $this->assertSame(
            ['RESULT:' . json_encode(array_map('bin2hex', $values))],
            array_values(preg_grep('/^RESULT:/', $output)),
            "exit code $code\nstdout:\n" . implode("\n", $output) . "\nstderr:\n$errors"
        );
    }

    public function testEveryTemplatePlaceholderIsInsideASingleQuotedString(): void
    {
        $template = file_get_contents(
            realpath(__DIR__ . '/../../../../../installer/config/Conf.tpl.php')
        );
        $this->assertSame(6, preg_match_all('/\{\{db(Host|Port|Name|User|Pass)\}\}/', $template));
        foreach (token_get_all($template) as $token) {
            if (is_array($token) && strpos($token[1], '{{') !== false) {
                $this->assertSame(T_CONSTANT_ENCAPSED_STRING, $token[0], $token[1]);
                $this->assertSame("'", $token[1][0], $token[1]);
            }
        }
    }

    public function testOrdinaryValuesGiveTheSameFileAsBefore(): void
    {
        $dbInfo = $this->getDbInfo('db.example.com', '3307', 'orangehrm_5', 'ohrm-user', 'Ohrm@1423#Secure!$x');
        $template = file_get_contents(
            realpath(__DIR__ . '/../../../../../installer/config/Conf.tpl.php')
        );
        $previous = str_replace(
            ['{{dbHost}}', '{{dbPort}}', '{{dbName}}', '{{dbUser}}', '{{dbPass}}'],
            ['db.example.com', '3307', 'orangehrm_5', 'ohrm-user', 'Ohrm@1423#Secure!$x'],
            $template
        );

        $this->assertSame($previous, (new AppSetupUtility())->getConfFileContent($dbInfo));
    }

    public function testOrangehrmDatabaseUserIsWrittenWhenSet(): void
    {
        $conf = $this->writeAndLoadConf(
            'localhost',
            '3306',
            'orangehrm',
            new UserCredential('root', "root'pw"),
            new UserCredential("ohrm'user", "ohrm\\'pw")
        );

        $this->assertSame("ohrm'user", $conf->getDbUser());
        $this->assertSame("ohrm\\'pw", $conf->getDbPass());
    }

    public function testMissingPasswordIsWrittenAsEmptyString(): void
    {
        $conf = $this->writeAndLoadConf('localhost', '3306', 'orangehrm', new UserCredential('root', null));

        $this->assertSame('', $conf->getDbPass());
    }

    public function testIntegerPortIsWrittenAsString(): void
    {
        $dbInfo = $this->getDbInfo('localhost', 3306, 'orangehrm', 'root', 'pw');

        $conf = $this->loadConf((new AppSetupUtility())->getConfFileContent($dbInfo));

        $this->assertSame('3306', $conf->getDbPort());
    }

    public function testNonScalarValueIsRejected(): void
    {
        $dbInfo = $this->getDbInfo(['x'], '3306', 'orangehrm', 'root', 'pw');

        $this->expectException(InvalidArgumentException::class);
        (new AppSetupUtility())->getConfFileContent($dbInfo);
    }

    public function testWriteConfFileWritesToTheConfiguredPath(): void
    {
        $this->assertSame($this->confPath, Config::get(Config::CONF_FILE_PATH));
        $host = "x';\$GLOBALS['" . self::MARKER_FLAG . "'] = true;//";
        StateContainer::getInstance()->storeDbInfo($host, '3306', new UserCredential('root', 'pw'), 'orangehrm');

        (new AppSetupUtility())->writeConfFile();

        $this->assertStringContainsString(
            "\$this->dbHost = 'x\\';\$GLOBALS[\\'" . self::MARKER_FLAG . "\\'] = true;//';",
            file_get_contents($this->confPath)
        );
    }

    protected function setUp(): void
    {
        $container = ServiceContainer::getContainer();
        $this->previousSession = $container->has(Services::SESSION) ? $container->get(Services::SESSION) : null;
        $this->previousConfPath = Config::get(Config::CONF_FILE_PATH);
        $confPath = tempnam(sys_get_temp_dir(), 'ohrm_conf_');
        $this->assertNotFalse($confPath);
        $this->confPath = $confPath;

        $container->set(Services::SESSION, new Session(new MemorySessionStorage()));
        // Never write the instance's own lib/confs/Conf.php
        Config::set(Config::CONF_FILE_PATH, $this->confPath);
    }

    protected function tearDown(): void
    {
        Config::set(Config::CONF_FILE_PATH, $this->previousConfPath);
        ServiceContainer::getContainer()->set(Services::SESSION, $this->previousSession);
        @unlink($this->confPath);
        unset($GLOBALS[self::MARKER_FLAG]);
    }

    /**
     * Store the details the way the installer APIs do, write Conf.php and load it.
     */
    private function writeAndLoadConf(
        string $host,
        string $port,
        string $name,
        UserCredential $credential,
        ?UserCredential $ohrmCredential = null
    ): object {
        StateContainer::getInstance()->storeDbInfo($host, $port, $credential, $name, $ohrmCredential);
        $this->assertSame($this->confPath, Config::get(Config::CONF_FILE_PATH));
        (new AppSetupUtility())->writeConfFile();
        return $this->loadConf(file_get_contents($this->confPath));
    }

    /**
     * @param mixed $host
     * @param mixed $port
     * @param mixed $name
     * @param mixed $user
     * @param mixed $password
     * @return array
     */
    private function getDbInfo($host, $port, $name, $user, $password): array
    {
        return [
            StateContainer::DB_HOST => $host,
            StateContainer::DB_PORT => $port,
            StateContainer::DB_NAME => $name,
            StateContainer::DB_USER => $user,
            StateContainer::DB_PASSWORD => $password,
            StateContainer::ENABLE_DATA_ENCRYPTION => false,
        ];
    }

    /**
     * The PHP CLI binary running this test, shell-quoted. Skips the test, with the reason, when it cannot be
     * run with exec() or does not load mbstring (zend.multibyte needs it).
     */
    private function getPhpCliWithMbstring(): string
    {
        $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
        if (!function_exists('exec') || in_array('exec', $disabled, true)) {
            $this->markTestSkipped('exec() is disabled, so Conf.php cannot be loaded in a separate PHP process');
        }
        if (PHP_BINARY === '' || !is_executable(PHP_BINARY)) {
            $this->markTestSkipped('PHP_BINARY is not an executable PHP CLI: ' . var_export(PHP_BINARY, true));
        }
        $php = escapeshellarg(PHP_BINARY);
        exec("$php -r " . escapeshellarg('echo extension_loaded("mbstring") ? "yes" : "no";') . ' 2>&1', $out, $code);
        if ($code !== 0 || !in_array($out, [['yes'], ['no']], true)) {
            $this->markTestSkipped('Could not run ' . PHP_BINARY . " (exit code $code): " . implode("\n", $out));
        }
        if ($out === ['no']) {
            $this->markTestSkipped('mbstring is not loaded in ' . PHP_BINARY . ', and zend.multibyte needs it');
        }
        return $php;
    }

    private function expectedDbName(string $dbName): string
    {
        if (defined('ENVIRONMENT') && ENVIRONMENT == 'test') {
            return (defined('TEST_DB_PREFIX') ? TEST_DB_PREFIX : '') . 'test_' . $dbName;
        }
        return $dbName;
    }

    /**
     * Load generated Conf.php content under a unique class name (`Conf` is already loaded) and instantiate it.
     *
     * @param string $content
     * @return object
     */
    private function loadConf(string $content): object
    {
        static $count = 0;
        $className = 'OhrmConfFileTestConf' . getmypid() . '_' . (++$count);
        $renamed = preg_replace('/^class Conf$/m', "class $className", $content, -1, $replaced);
        $this->assertSame(1, $replaced, 'class declaration not found');

        $path = tempnam(sys_get_temp_dir(), 'ohrm_conf_');
        $this->assertNotFalse($path);
        try {
            file_put_contents($path, $renamed);
            require $path;
        } finally {
            @unlink($path);
        }
        return new $className();
    }
}
