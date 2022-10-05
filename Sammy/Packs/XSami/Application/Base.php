<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\XSami\Application
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
namespace Sammy\Packs\XSami\Application {
  use Closure;
  use Sammy\Packs\XSami\Application;
  use Sammy\Packs\Sami\CommandLineInterface;
  /**
   * Make sure the module base internal trait is not
   * declared in the php global scope defore creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  if (!trait_exists ('Sammy\Packs\XSami\Application\Base')) {
  /**
   * @trait Base
   * Base internal trait for the
   * XSami\Application module.
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
     * @method void
     *
     * setter
     */
    public function __set (string $prop, $value = null) {
      $propSetter = join ('', ['set', ucfirst ($prop)]);

      if (method_exists ($this, $propSetter)) {
        $this->props [strtolower ($prop)] = call_user_func_array ([$this, $propSetter], [$value]);
      } else {
        $this->props [strtolower ($prop)] = $value;
      }
    }

    /**
     * @method void
     *
     * handler setter
     */
    private function setHandler ($handler) {
      if ($handler instanceof Closure) {
        return $handler;
      } elseif ($handler instanceof CommandLineInterface) {
        $handlerApp = new Application;
        $handlerApp->customHandler = $handler;

        return Closure::bind (function ($args) {
          if ($this->customHandler instanceof CommandLineInterface) {
            $this->customHandler->runRaw ($args);
          }
        }, $handlerApp, get_class ($handlerApp));
      }
    }

    /**
     * @method mixed
     *
     * getter
     */
    public function __get (string $prop) {
      $prop = strtolower ($prop);

      if (isset ($this->props [$prop])) {
        return $this->props [$prop];
      }
    }

    /**
     * @method boolean
     *
     * isset
     */
    public function __isset (string $prop) {
      return isset ($this->props [strtolower ($prop)]);
    }
  }}
}
