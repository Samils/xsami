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
  use function strtolower as lower;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\XSami\DirMapper')) {
  /**
   * @trait DirMapper
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
  trait DirMapper {
    /**
     * dirMap
     * @param array $dirMap
     * @return array
     */
    private static function dirMap ($dirMap) {
      $re = '/\/(\*){2,}$/';
      /**
       * loop the directory list and get each
       * file that needs to be whatched
       */
      foreach ($dirMap as $dirPath => $dirMapObject) {
        $mapSubDirs = preg_match ($re, $dirPath);

        $dirPath = realpath (join ('/', [
          realpath (null),
          preg_replace ($re, '', $dirPath)
        ]));

        if (!$dirPath) {
          continue;
        }

        self::mapDirFiles ($dirPath, [
          'dirMapObject' => $dirMapObject,
          'mapSubDirectories' => $mapSubDirs
        ]);
      }
    }

    /**
     * @method void mapDirFiles
     */
    private static function mapDirFiles ($dirPath, array $options = []) {
      $defaultOptons = [
        'dirMapObject' => [],
        'mapSubDirectories' => false
      ];

      $options = array_merge ($defaultOptons, $options);

      if (!is_array ($options ['dirMapObject'])) {
        $options ['dirMapObject'] = [];
      }

      $dirFileList = self::readDir ($dirPath);

      foreach ($dirFileList as $dirFile) {
        if (is_file ($dirFile)) {
          self::registerAndExecuteFile ($dirFile, [
            'execMap' => $options ['dirMapObject'],
            'execMapExt' => array_keys ($options ['dirMapObject'])
          ]);
        } elseif ($options ['mapSubDirectories']) {
          self::mapDirFiles ($dirFile, $options);
        }
      }


    }

    protected static function readDir ($dir) {
      $files = [];

      if (is_dir ($dir)) {
        if ($dh = opendir ($dir)) {
          while (($file = readdir ($dh)) !== false) {
            if (!in_array ($file, ['.', '..'])) {
              array_push ($files, realpath ($dir . '/' . $file));
            }
          }

          closedir ($dh);
        }
      }

      return $files;
    }
  }}
}
