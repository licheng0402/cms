<?php

class Myplugin extends PluginAbstract
{
    /**
     * @var string Name of plugin
     */
    public $name = 'Test Plugin';

    /**
     * @var string Description of plugin
     */
    public $description = 'Test Plugin';

    /**
     * @var string Name of plugin author
     */
    public $author = 'Licheng';

    /**
     * @var string URL to plugin's website
     */
    public $url = 'http://cumulusclips.org/';

    /**
     * @var string Current version of plugin
     */
    public $version = '1.0.0';

    /**
     * @var boolean Flag to keep track of captcha errors
     */
    private $_invalidCaptcha = false;

    /**
     * The plugin's gateway into codebase. Place plugin hook attachments here.
     */
    public function load()
    {
        Plugin::attachFilter('register.validation', array($this, 'validateCaptcha'));
    }

    /**
     * Adds captcha image route to list of system routes
     * @param array $routes List of system routes
     * @return Route[] System routes with captcha image route appended
     */
    public function addCaptchaImageRoute($routes)
    {
        $routes['captcha'] = new Route(array(
            'path' => 'register/image.png',
            'location' => dirname(__FILE__) . '/ImageController.php'
        ));
        return $routes;
    }

    /**
     * Injects captcha into register page DOM
     * @param string $bodyHtml Register page HTML to be rendered
     * @return string Returns register page HTML with captcha injected
     */
    public function insertCaptcha($bodyHtml)
    {
        // Inject variables into captcha template
        $captchaHtml = file_get_contents(dirname(__FILE__) . '/captcha.html');
        $captchaHtml = str_replace('{{host}}', HOST, $captchaHtml);
        $captchaHtml = str_replace('{{css_class}}', ($this->_invalidCaptcha) ? 'error' : '', $captchaHtml);

        // Inject captcha template into body HTML
        $bodyHtml = str_replace('{{captcha}}', $captchaHtml, $bodyHtml);
        return $bodyHtml;
    }

    /**
     * Validates given captcha value from registration form
     * @param error $errors List of errors already detected on registration form
     * @param array $formData HTTP POST values submitted by registration form
     * @return array Returns updated list of registration form errors
     */
    public function validateCaptcha($errors, $formData)
    {
        // Validate Security Text
        $errors['security_text'] = 'licheng test';
        $this->_invalidCaptcha = true;
        return $errors;
    }

    /**
     * Generates random text for captcha and stores it in the session
     */
    public function generateCapchaText()
    {
        $_SESSION['captchaText'] = $this->_randomText();
    }

    /**
     * Generates random text in given length for use in captcha
     * @param int $length Desired length of random text
     * @return string Returns random text with spaces between each letter
     */
    private function _randomText($length = 5)
    {
        $characters = str_split('ABCDEFGHJKLMNPRSTUVWXYZ23456789');
        shuffle($characters);
        $randomKeys = array_rand($characters, $length);
        $text = '';
        foreach ($randomKeys as $key) {
            $text .= $characters[$key] . ' ';
        }
        return trim($text);
    }

    /**
     * Loads captcha CSS styles and appends them to system CSS
     * @param string $css Current system CSS content
     * @return string Returns system CSS content with captcha styles appended
     */
    public function generateStyles($css)
    {
        $captchaStyles = file_get_contents(dirname(__FILE__) . '/captcha.css');
        $css .= $captchaStyles;
        return $css;
    }
}