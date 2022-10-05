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
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\XSami\Runner')) {
  /**
   * @trait Runner
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
  trait Runner {
    /**
     * @var Sammy\Packs\XSami\Application $dafeultApp
     *
     * Default XSami Application for handling map
     * ?(iterations)
     *
     */
    private static $defaultApp = null;

    /**
     * @var boolean
     *
     * ?is xsami app running
     */
    private static $running = false;

    /**
     * @var Sammy\Packs\XSami\Application $app
     *
     * XSami Application for handling map
     * ?(iterations)
     *
     */
    private static $app = null;
    /**
     * [Run description]
     */
    public static function Run (Application $app = null, array $runOptions) {
      $config = self::getConfig ();

      if ( !(is_array ($config) && $config) ) {
        return null;
      }

      $map = !isset ($config ['map']) ? [] : (
        $config ['map']
      );

      $dirConfigArray = self::issetAsArray ('dir');
      $fileConfigArray = self::issetAsArray ('file');
      $directoryConfigArray = self::issetAsArray ('directory');

      $dir = array_merge ($dirConfigArray, $directoryConfigArray);

      $dirMap = self::issetAsArray ('map', $dir);
      $fileWatcherConfig = self::issetAsArray ('watcher', $fileConfigArray);

      $fileWatcherConfig = self::object2array ($fileWatcherConfig);

      foreach ($fileWatcherConfig as $watcherObject) {
        if (!is_array ($watcherObject)) {
          $watcherObject = [
            'resolve' => $watcherObject
          ];
        }

        if (isset ($watcherObject ['resolve'])) {
          $watcherObjectModule = requires ($watcherObject ['resolve']);

          if (is_callable ($watcherObjectModule)) {
            self::$fileWatcherMap[] = [
              'resolve' => $watcherObjectModule,
              'options' => self::issetAsArray ('options', $watcherObject)
            ];
          }
        }
      }

      # print_r (self::$fileWatcherMap);
      # $data = (array)('Le');

      $dirs = !isset ($config ['watch']) ? [] : (
        $config ['watch']
      );

      $exclude = [];

      $issetExludingList = ( boolean ) (
        isset ($config ['watch']) &&
        is_array ($config ['watch']) &&
        isset ($config ['watch']['exclude']) &&
        is_array ($config ['watch']['exclude'])
      );

      if ($issetExludingList) {
        $exclude = $config ['watch']['exclude'];
      }

      self::map ( $map, $exclude );

      self::start ();
      self::welcome ();

      while (self::running ()) {
        self::dirMap ($dirMap);
        self::watchAll ($dirs);

        if (!(is_bool ($runOptions ['watch']) && $runOptions ['watch'])) {
          self::stop ();
        }
      }

      exit (0);
    }

    /**
     * @method boolean
     *
     * verify if the xsami app is running
     */
    public static function running () {
      return self::$running;
    }

    /**
     * @method void
     *
     * stop the xsami application
     */
    private static function stop () {
      self::$running = false;
    }

    /**
     * @method void
     *
     * start the xsami application
     */
    private static function start () {
      self::$running = !false;
    }

    /**
     * @method void runApp
     *
     * Run XSami Development server given an application
     * which should be a set configurations for running
     * it a specific way different than the default.
     *
     * @param Sammy\Packs\XSami\Application $app
     *
     * The XSami Application for running with.
     *
     */
    public function runApp (Application $app = null, array $runOptions = []) {
      self::$app = $app;
      self::Run ($app, $runOptions);
    }
  }}
}
