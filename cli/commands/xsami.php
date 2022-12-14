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
  use Clinter\Console;
  use Sammy\Packs\Path;
  use Sammy\Packs\XSami;
  use Sammy\Packs\Sami\CommandLineInterface\Options;
  use Sammy\Packs\Sami\CommandLineInterface\Parameters;

  /**
   * Make sure the module base internal class is not
   * declared in the php global scope before creating
   * it.
   * It ensures that the script flux is not interrupted
   * when trying to run the current command by the cli
   * API.
   */
  $module->exports = [
    'name' => 'xsami',
    'description' => 'Xsami CLI',

    'handler' => function (Parameters $parameters, Options $options) {
      $xsami = new XSami;

      $app = $xsami ();

      $path = new Path;

      $runOptions = $options->only (['watch']);

      $xsamiConfigFilePath = $path->join ('~', 'xsami.config.php');

      if (is_file ($xsamiConfigFilePath)) {
        $xsamiConfig = requires ($xsamiConfigFilePath);

        $app->config ($xsamiConfig);
      }

      $app->handler = $app->defaultAppHandler;

      $xsami->runApp ($app, $runOptions);
    }
  ];
}
