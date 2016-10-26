<?php
/**
 * @copyright Copyright (c) 2016 mogita (http://mogita.com)
 * @license https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @author      mogita
 * @created_by  PhpStorm
 * @created_at  10/25/16 14:04
 */

namespace Mogita\DocBuilder;

use Symfony\Component\Yaml\Yaml;

class MkDocs {
    private $rootPath; // should point to the folder that contains mkdocs.yml

    private $mkDocsConfig = [
        'site_name' => '',
        'site_url' => '',
        'repo_url' => '',
        'docs_dir' => '',
        'site_dir' => '',
        'theme' => '',
        'extra' => [],
        'markdown_extensions' => [
            'codehilite',
            'admonition',
            ['toc' =>
                ['permalink' => '#']
            ]
        ],
        'pages' => []
    ];

    /**
     * MkDocs constructor.
     * @param string $rootPath - Documentation root path for mkdocs, which will contain the mkdocs.yml file
     * @param array $config - Instantiate with configs for mkdocs.yml. You can always make changes to the configuration later through the setter methods, before calling the build() method.
     */
    function __construct($rootPath = '', $config = []) {
        $this->mkDocsConfig = array_merge($this->mkDocsConfig, $config);
        $this->rootPath = $rootPath;
    }

    /**
     * @param bool $configOnly - write to mkdocs.yml file without running mkdocs "build" command
     * @return bool(true) - on success, or a string describing the failure
     */
    public function build($configOnly = false) {
        // make sure folders exist
        if (!$this->checkDir()) return 'failed to make dir';

        // make a full mkdocs config
        if (!$this->checkConfig()) return 'failed to build config';

        // overwrite mkdocs.yaml file
        if (!$this->writeConfig()) return 'failed to write config file';

        // execute mkdocs build --clean
        if ($configOnly) return true;
        return $this->exec();
    }

    public function setDocsDir($str = '') {
        if (strlen($str) > 0) {
            $this->mkDocsConfig['docs_dir'] = $str;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['docs_dir'];
    }

    public function setSiteDir($str = '') {
        if (strlen($str) > 0) {
            $this->mkDocsConfig['site_dir'] = $str;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['site_dir'];
    }

    public function addPage($title = '', $content = '', $parent = '') {
        if (strlen($parent) > 0) {
            foreach($this->mkDocsConfig['pages'] as $key => $item) {
                if (array_key_exists($parent, $item)) {
                    array_push($this->mkDocsConfig['pages'][$key][$parent], [$title => $content]);
                }
            }
        }
        else {
            array_push($this->mkDocsConfig['pages'], [$title => $content]);
        }

        return $this->mkDocsConfig['pages'];
    }

    public function setSiteName($siteName = '') {
        if (strlen($siteName) > 0) {
            $this->mkDocsConfig['site_name'] = $siteName;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['site_name'];
    }

    public function setSiteUrl($siteUrl = '') {
        if (strlen($siteUrl) > 0) {
            $this->mkDocsConfig['site_url'] = $siteUrl;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['site_url'];
    }

    public function setHeaderLink($link = '') {
        if (strlen($link) > 0) {
            $this->mkDocsConfig['repo_url'] = $link;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['repo_url'];
    }

    public function setPrev($str = '') {
        if (strlen($str) > 0) {
            $this->mkDocsConfig['extra']['i18n']['prev'] = $str;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['extra']['i18n']['prev'];
    }

    public function setNext($str = '') {
        if (strlen($str) > 0) {
            $this->mkDocsConfig['extra']['i18n']['next'] = $str;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['extra']['i18n']['next'];
    }

    public function setPrimaryColor($str = '') {
        if (strlen($str) > 0) {
            $this->mkDocsConfig['extra']['palette']['primary'] = $str;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['extra']['palette']['primary'];
    }

    public function setAccentColor($str = '') {
        if (strlen($str) > 0) {
            $this->mkDocsConfig['extra']['palette']['accent'] = $str;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['extra']['palette']['accent'];
    }

    public function setTheme($str = '') {
        if (strlen($str) > 0) {
            $this->mkDocsConfig['theme'] = $str;
        }
        else {
            return false;
        }

        return $this->mkDocsConfig['theme'];
    }

    private function deleteProperty($node = '') {
        if (array_key_exists($node, $this->mkDocsConfig)) unset($this->mkDocsConfig[$node]);
    }

    private function checkDir() {
        if (!is_dir($this->rootPath)) mkdir($this->rootPath, 0755);

        $srcDir = $this->rootPath . $this->mkDocsConfig['docs_dir'];

        if (!is_dir($srcDir)) {
            // create docs directory
            return mkdir($srcDir, 0755);
        }
        else {
            return true;
        }
    }

    private function checkConfig() {
        // basic setups
        if (!isset($this->mkDocsConfig['site_name']) || strlen($this->mkDocsConfig['site_name']) === 0) $this->setSiteName('Documents');
        if (!isset($this->mkDocsConfig['docs_dir']) || strlen($this->mkDocsConfig['docs_dir']) === 0) $this->setDocsDir('docs');
        if (!isset($this->mkDocsConfig['site_dir']) || strlen($this->mkDocsConfig['site_dir']) === 0) $this->setSiteDir('site');
        if (!isset($this->mkDocsConfig['site_url']) || strlen($this->mkDocsConfig['site_url']) === 0) $this->deleteProperty('site_url');
        if (!isset($this->mkDocsConfig['repo_url']) || strlen($this->mkDocsConfig['repo_url']) === 0) $this->deleteProperty('repo_url');
        if (!isset($this->mkDocsConfig['theme']) || strlen($this->mkDocsConfig['theme']) === 0) $this->deleteProperty('theme');

        // set previous and next page localization
        if (!isset($this->mkDocsConfig['extra']['i18n']['prev']) || strlen($this->mkDocsConfig['extra']['i18n']['prev']) === 0) $this->setPrev('Previous');
        if (!isset($this->mkDocsConfig['extra']['i18n']['next']) || strlen($this->mkDocsConfig['extra']['i18n']['next']) === 0) $this->setNext('Next');

        // set site color palette
        // all available colors can be found here https://www.materialui.co/colors
        if (!isset($this->mkDocsConfig['extra']['palette']['primary']) || strlen($this->mkDocsConfig['extra']['palette']['primary']) === 0) $this->setPrimaryColor('teal');
        if (!isset($this->mkDocsConfig['extra']['palette']['accent']) || strlen($this->mkDocsConfig['extra']['palette']['accent']) === 0) $this->setAccentColor('pink');

        // pages must not be empty
        if (isset($this->mkDocsConfig['pages']) && count($this->mkDocsConfig['pages']) === 0) $this->deleteProperty('pages');

        return true;
    }

    private function writeConfig() {
        $yaml = Yaml::dump($this->mkDocsConfig, 2, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        return file_put_contents($this->rootPath . '/mkdocs.yml', $yaml);
    }

    private function exec() {
        $result = [];
        $retVal = 0;
        chdir($this->rootPath);

        // TODO: more compatibilities
        $mkdocs = '/usr/local/bin/mkdocs';

        // 2>&1 will make exec able to print errors
        exec($mkdocs . ' build --clean 2>&1', $result, $retVal);

        if ($retVal === 0) {
            return true;
        }
        else {
            return $result;
        }
    }
}