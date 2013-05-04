<?php
/**
 * V8
 *
 * @author      Jorg Thuijls <jorg.thuijls@gmail.com>
 * @copyright   2013 Jorg Thuijls
 * @license     MIT License
 * @version     0.1
 * @package     Core
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Core {

    /**
     * Class loader
     * 
     * This class loads all classes automatically, after the following principle:
     * \Name\Space\ClassName stored in file Name\Space\ClassName.php, prefixed
     * by the include path.  
     */
    class ClassLoader {

        /**
         * file extension of the file to load. This would usually be .php
         * @var type string
         */
        private $_fileExtension = '.php';
        
        /**
         * The root path of the web app. 
         * @var type string
         */
        private $_includePath;

        /**
         * Create a new class loader
         * 
         * @param type $includePath the root of the web app
         */
        public function __construct($includePath = null) {
            $this->_includePath = $includePath;
        }

        /**
         * Sets the base include path for all class files in the namespace of this class loader.
         * 
         * @param string $includePath
         */
        public function setIncludePath($includePath) {
            $this->_includePath = $includePath;
        }

        /**
         * Gets the base include path for all class files in the namespace of this class loader.
         *
         * @return string $includePath
         */
        public function getIncludePath() {
            return $this->_includePath;
        }

        /**
         * Sets the file extension of class files in the namespace of this class loader.
         * 
         * @param string $fileExtension
         */
        public function setFileExtension($fileExtension) {
            $this->_fileExtension = $fileExtension;
        }

        /**
         * Gets the file extension of class files in the namespace of this class loader.
         *
         * @return string $fileExtension
         */
        public function getFileExtension() {
            return $this->_fileExtension;
        }

        /**
         * Installs this class loader on the SPL autoload stack.
         */
        public function register() {
            spl_autoload_register(array($this, 'loadClass'));
        }

        /**
         * Uninstalls this class loader from the SPL autoloader stack.
         */
        public function unregister() {
            spl_autoload_unregister(array($this, 'loadClass'));
        }

        /**
         * Loads the given class or interface.
         *
         * @param string $className The name of the class to load.
         * @return void
         */
        public function loadClass($class) {
            debug('fetching class: ' . $class, 3);
            $file = $this->_includePath . DIRECTORY_SEPARATOR .
                    implode(DIRECTORY_SEPARATOR, explode('\\', $class))
                    . '.php';
 
            if (file_exists($file)) {
                require_once $file;
            }
        }

    }

}