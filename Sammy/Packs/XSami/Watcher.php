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
  use php\module;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\XSami\Watcher')) {
  /**
   * @trait Watcher
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
  trait Watcher {
    /**
     * [watchAll description]
     * @param  array $directories
     * @return null
     */
    private static function watchAll ($directories = []) {
      if (!(is_array ($directories) && $directories)) {
        return null;
      }

      foreach ( $directories as $i => $directory ) {
        if (!in_array ( path ($directory), self::$exclude)) {
          self::watch ( $directory );
        }
      }
    }

    /**
     * [watch description]
     * @param  string $directories
     * @return null
     */
    private static function watch ($directory = '') {
      $ds = DIRECTORY_SEPARATOR;
      /**
       * [$dir description]
       * @var string
       */
      $dir = preg_replace ('/(\\\|\/)+/', $ds,
        path ( $directory )
      );

      if (in_array ( $directory, self::$exclude)) {
        return;
      }

      $dir = join ('', [
        preg_replace ('/(\\\|\/)+$/', '' , $dir),
        DIRECTORY_SEPARATOR, '*'
      ]);

      $dirFiles = glob ($dir);

      if ( !(is_array ($dirFiles) && $dirFiles) ) {
        return;
      }

      foreach ( $dirFiles as $dirFile ) {
        if ( is_dir ($dirFile) ) {
          self::watch ($dirFile);
          continue;
        }

        self::registerAndExecuteFile ($dirFile);
      }
    }

    /**
     * @method mixed execute file command
     */
    private static function execFile ($filePath, array $options = []) {
      $fs = requires ('fs');

      $file = $fs->useFile ($filePath);

      $defaultOptions = [
        'execMap' => self::$execMap
      ];

      $options = array_merge ($defaultOptions, $options);

      if (!is_array ($options ['execMap'])) {
        $options ['execMap'] = $defaultOptions ['execMap'];
      }

      if (!isset ($options ['execMap'][$file->extension])) {
        return;
      }

      self::handleFileWatchers ($file);

      /**
       * [$commandStrSlices description]
       * @var array
       */
      $commandStrSlices = preg_split (
        '/\s+/',
        $options ['execMap'][$file->extension]
      );

      #echo self::$execMap [ $file->extension ], "\n";

      #$commandArgs = [$commandStrSlices[0], $dirFile];

      $commandArgs = array_merge (
        $commandStrSlices, [$filePath]
      );

      self::$files [$filePath]['lastModify'] = $file->lastModify;

      $handler = self::getAppHandler ();

      if (is_callable ($handler)) {
        call_user_func_array ($handler, [$commandArgs]);
      } else {
        $cmdArgs = array_merge (['php'], $commandArgs);
        @system (join (' ', $cmdArgs));
      }


      # echo "\nFile Pah => ", $file->abs, "\n";

      $fileObject = self::getStaticFile ($file);

      $fileObject->runOptions = $options;

      $fileOnChangeHandlerList = $fileObject->getOnChangeHandlerList ();

      if (is_array ($fileOnChangeHandlerList) && count ($fileOnChangeHandlerList) >= 1) {
        foreach ($fileOnChangeHandlerList as $onChangeHandler) {
          $onChangeHandler = \Closure::bind ($onChangeHandler, new static, static::class);

          call_user_func_array ($onChangeHandler, [
            $fileObject
          ]);
        }
      }

    }

    /**
     * @method void registerAndExecuteFile
     */
    private static function registerAndExecuteFile ($dirFile, array $options = []) {
      $fs = requires ('fs');

      $defaultOptions = [
        'execMapExt' => []
      ];

      $options = array_merge ($defaultOptions, $options);

      if (!(is_array ($options ['execMapExt']) && $options ['execMapExt'])) {
        $options ['execMapExt'] = self::$execMapExt;
      }

      $file = $fs->useFile ($dirFile);

      if (in_array ($file->extension, $options ['execMapExt'])) {

        if (!isset (self::$files [$dirFile])) {
          /**
           * Register the current file path
           */
          self::$files [ $dirFile ] = self::factory (
            $file
          );
        } elseif (is_array (self::$files [$dirFile])) {
          $fileDatas = self::$files [ $dirFile ];

          if ($fileDatas ['lastModify'] === $file->lastModify) {
            return;
          }
        }

        return self::execFile ($dirFile, $options);
      }
    }

    /**
     * @method void handleFileWatchers
     */
    private static function  handleFileWatchers (File $file) {
      foreach (self::$fileWatcherMap as $fileWatcher) {
        $fileWatcherHandler = $fileWatcher ['resolve'];

        $fileWatcherExtensions = self::issetAsArray ('extensions', $fileWatcher ['options']);

        if (!in_array ($file->extension, $fileWatcherExtensions)) {
          continue;
        }

        $fileObject = self::getStaticFile ($file) /*new static ($file)*/;

        $defaultFileProps = self::issetAsArray ('defaultFileProps', $fileWatcher ['options']);

        foreach ($defaultFileProps as $prop => $value) {
          if (!isset ($fileObject->$prop)) {
            $fileObject->$prop = $value;
          }
        }

        call_user_func_array ($fileWatcherHandler, [
          $fileObject,
          $fileWatcher ['options']
        ]);
      }
    }

    /**
     * @method mixed get static file object
     */
    public static function getStaticFile (File $file) {
      static $fileList = [];

      $fileAbsolutePath = $file->abs;

      if (isset ($fileList [$fileAbsolutePath])) {
        return $fileList [$fileAbsolutePath];
      }

      return ($fileList [$fileAbsolutePath] = new static ($file));
    }
  }}
}
