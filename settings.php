<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Brightsharp theme settings.
 *
 * @package    theme_brightsharp
 * @copyright  Pelorus Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once(__DIR__ . '/lib.php');
    $settings = new admin_settingpage('themesettingbrightsharp', get_string('pluginname', 'theme_brightsharp'));

    $setting = new admin_setting_configcolourpicker(
        'theme_brightsharp/brandcolor',
        get_string('primarycolor', 'theme_brightsharp'),
        get_string('primarycolor_desc', 'theme_brightsharp'),
        '#FFD23F'
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $setting = new admin_setting_configcolourpicker(
        'theme_brightsharp/primarybuttoncolor',
        get_string('primarybuttoncolor', 'theme_brightsharp'),
        get_string('primarybuttoncolor_desc', 'theme_brightsharp'),
        ''
    );
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    $colours = [
        'accentcolor' => '#FF90E8',
        'pagebackgroundcolor' => '#FFFDF5',
        'surfacecolor' => '#FFFFFF',
        'textcolor' => '#171717',
        'outlinecolor' => '#171717',
        'linkcolor' => '#1746A2',
    ];
    foreach ($colours as $name => $default) {
        $setting = new admin_setting_configcolourpicker(
            'theme_brightsharp/' . $name,
            get_string($name, 'theme_brightsharp'),
            get_string($name . '_desc', 'theme_brightsharp'),
            $default
        );
        $setting->set_updatedcallback('theme_reset_all_caches');
        $settings->add($setting);
    }

    $shadowdefaults = theme_brightsharp_get_shadow_sizes((object) get_config('theme_brightsharp'));
    foreach ($shadowdefaults as $tier => $default) {
        $name = 'shadowsize' . $tier;
        $setting = new admin_setting_configtext(
            'theme_brightsharp/' . $name,
            get_string($name, 'theme_brightsharp'),
            get_string($name . '_desc', 'theme_brightsharp'),
            $default,
            '/^[0-9]+(?:\.[0-9]+)?$/'
        );
        $setting->set_updatedcallback('theme_reset_all_caches');
        $settings->add($setting);
    }
}
