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
  use FileSystem\File as FileObject;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\XSami\File')) {
  /**
   * @trait File
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
  trait File {
    /**
     * @var FileSystem\File file object
     */
    protected $file;

    /**
     * @var array file props
     */
    private $props = [
      /**
       * @var array on change handler list
       */
      'onchangehandlerlist' => []
    ];

    /**
     * @method void constructor
     */
    public function __construct (FileObject $file = null) {
      $this->file = $file;

      if ($file) {
        $this->path = $file->abs;
      }
    }

    /**
     * @method mixed file property getter
     */
    public function __get (string $prop) {
      $prop = $this->formatPropName ($prop);

      if (isset ($this->$prop)) {
        return $this->props [$prop];
      }
    }

    /**
     * @method mixed file property setter
     */
    public function __set (string $prop, $value = null) {
      $prop = $this->formatPropName ($prop);

      $this->props [$prop] = $value;
    }

    /**
     * @method boolean file property is set
     */
    public function __isset (string $prop) {
      $prop = $this->formatPropName ($prop);

      return isset ($this->props [$prop]);
    }

    /**
     * @method mixed file method call fallback
     */
    public function __call ($methodName, $argments) {
      $propGetterRe = '/^((g|s)et|add)(.+)/i';

      if (preg_match ($propGetterRe, $methodName, $match)) {

        $propName = $this->formatPropName ($match [3]);
        $value = isset ($argments [0]) ? $argments [0] : null;

        switch (strtolower ($match [1])) {
          case 'get':
            return $this->__get ($match [3]);
            break;

          case 'set':
            return $this->__set ($match [3], $value);
            break;

          case 'add':
            if (!(isset ($this->$propName) && is_array ($this->$propName))) {
              $this->$propName = [];
            }

            array_push ($this->props [$propName], $value);
            break;

          default:
            # code...
            break;
        }

        #return $this->__get ($match [3]);
      }
    }

    /**
     * @method void on change
     */
    public function onChange ($handler) {
      if (!($handler instanceof \Closure)) {
        return;
      }

      array_push ($this->props ['onchangehandlerlist'], $handler);
    }

    /**
     * @method string format property name
     */
    protected function formatPropName ($propName) {
      return strtolower ($propName);
    }
  }}
}
