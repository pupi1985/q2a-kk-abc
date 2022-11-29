<?php

/*
	Question2Answer (c) Gideon Greenspan

	https://www.question2answer.org/

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: https://www.question2answer.org/license.php
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../');
    exit;
}

class KK_ABC_Captcha
{
    const INPUT_NAME = 'kk_abc_form-div';

    function option_default($option)
    {
        if ($option == 'antibotcaptcha_count') {
            return 4;
        }
        if ($option == 'antibotcaptcha_charset') {
            return '23456789';
        }

        return null;
    }

    function admin_form()
    {
        $saved = false;

        if (qa_clicked('antibotcaptcha_save_button')) {
            qa_opt('antibotcaptcha_count', qa_post_text('antibotcaptcha_count_field'));
            qa_opt('antibotcaptcha_charset', qa_post_text('antibotcaptcha_charset_field'));

            $saved = true;
        }

        $form = array(
            'ok' => $saved ? 'AntiBot Captcha settings saved' : null,

            'fields' => array(
                'count' => array(
                    'label' => 'Symbol count:',
                    'value' => qa_opt('antibotcaptcha_count'),
                    'tags' => 'NAME="antibotcaptcha_count_field"',
                    'type' => 'number',
                ),

                'charset' => array(
                    'label' => 'Character set:',
                    'value' => qa_opt('antibotcaptcha_charset'),
                    'tags' => 'NAME="antibotcaptcha_charset_field"',
                ),

            ),

            'buttons' => array(
                array(
                    'label' => 'Save Changes',
                    'tags' => 'NAME="antibotcaptcha_save_button"',
                ),
            ),
        );

        return $form;
    }

    function allow_captcha()
    {
        return true;
    }

    public function form_html(&$qa_content, $error)
    {
        $inputName = qa_random_alphanum(10);
        $_SESSION[self::INPUT_NAME] = $inputName;

        $imagePage = qa_path_html('kk_abc_page');
        $charCount = (int)qa_opt('antibotcaptcha_count') + 2;

        return
            sprintf('<div style="vertical-align:middle;" id="%s">', $inputName) .
            '<p>' .
            sprintf('<input type="text" class="qa-form-tall-data" name="%s" id="%s" size="%d" autocomplete="off" />', $inputName, $inputName, $charCount) .
            sprintf('<label for="%s"> <img src="%s" alt="%s" /></label>', $inputName, $imagePage, qa_lang_html('misc/captcha_error')) .
            '</p>' .
            '</div>';
    }

    public function validate_post(&$error)
    {
        if ($this->allow_captcha()) {
            $captchaError = $this->captcha_check_answer();

            if (is_null($captchaError)) {
                return true;
            }

            $error = $captchaError;
        }

        return false;
    }

    private function captcha_check_answer()
    {
        $inputName = $_SESSION[self::INPUT_NAME] ?? null;

        $securitycode = qa_post_text($inputName);
        if ($securitycode == '') {
            return 'ERROR: Input code from image';
        } else if ($_SESSION['IMAGE_CODE'] != $securitycode) {
            return 'Invalid code. Return back and try input code again.';
        } else {
            unset($_SESSION['IMAGE_CODE']);

            return null;
        }
    }
}
