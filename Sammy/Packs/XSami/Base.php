<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\XSami
 * - Autoload, application dependencies
 *
 * MIT License
 *
 * Copyright (c) 2020 Ysare
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace Sammy\Packs\XSami {
  use FileSystem\File;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\XSami\Base')) {
  /**
   * @trait Base
   * Base internal trait for the
   * XSami module.
   * -
   * This is (in the ils environment)
   * an instance of the php module,
   * wich should contain the module
   * core functionalities that should
   * be extended.
   * -
   * For extending the module, just create
   * an 'exts' directory in the module directory
   * and boot it by using the ils directory boot.
   * -
   */
  trait Base {
    /**
     * @var array config datas
     */
    private static $config;
    /**
     * @var array $dirs2watch
     *
     * A list of directory absolute paths
     * to whatch while running xsami development
     * server.
     *
     */
    private static $dirs2watch = array ();
    /**
     * @var array execMap
     *
     * ...
     *
     */
    private static $execMap = array ();
    /**
     * @var array execMapExt
     *
     * ...
     *
     */
    private static $execMapExt = array ();
    /**
     * @var array exclude
     *
     * ...
     *
     */
    private static $exclude = array ();
    /**
     * @var array files
     *
     * ...
     *
     */
    private static $files = array ();

    /**
     * @var array file watcher map
     */
    private static $fileWatcherMap = [];

    /**
     * @method array factory
     */
    private static function factory (File $file) {
      return array (
        'file' => $file,
        'lastModify' => $file->lastModify
      );
    }

    private static function issetAsArray ($configProp, $config = null) {
      $config = is_array ($config) ? $config : self::$config;

      return (isset ($config [$configProp]) && is_array ($config [$configProp])) ? $config [$configProp] : [];
    }

    private static function object2array ($object) {
      if (is_object ($object)) {
        $object = (array)($object);
      }

      if (is_array ($object)) {
        foreach ($object as $key => $value) {
          $object [$key] = self::object2array ($value);
        }
      }

      return $object;
    }

    public function __invoke () {
      return new Application;
    }
  }}
}
