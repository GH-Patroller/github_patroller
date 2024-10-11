
<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Add a setting for the GitHub owner field
    $settings->add(new admin_setting_configtext(
        'pluginpatroller/github_token',
        get_string('githubtoken', 'pluginpatroller'),
        get_string('githubtoken_desc', 'pluginpatroller'),
        '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', // Default value
        PARAM_TEXT
    ));

}
?>
