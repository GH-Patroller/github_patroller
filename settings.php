

<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Campo para token_patroller
    $settings->add(new admin_setting_configtext(
        'pluginpatroller/token_patroller',
        get_string('tokenpatroller', 'pluginpatroller'),
        get_string('tokenpatroller_desc', 'pluginpatroller'),
        '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', // Valor por defecto
        PARAM_TEXT
    ));

    // Campo para owner_patroller
    $settings->add(new admin_setting_configtext(
        'pluginpatroller/owner_patroller',
        get_string('ownerpatroller', 'pluginpatroller'),
        get_string('ownerpatroller_desc', 'pluginpatroller'),
        '', // Valor por defecto
        PARAM_TEXT
    ));
}
?>