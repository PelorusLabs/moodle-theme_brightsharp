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
 * Brightsharp theme callbacks.
 *
 * @package    theme_brightsharp
 * @copyright  Pelorus Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Resolve independent shadow sizes, using defaults for unset tiers.
 *
 * @param stdClass $settings Theme settings.
 * @return array Shadow offsets in pixels keyed by tier.
 */
function theme_brightsharp_get_shadow_sizes(stdClass $settings): array {
    $sizes = ['small' => 2, 'medium' => 3, 'large' => 4];
    foreach (array_keys($sizes) as $tier) {
        $name = 'shadowsize' . $tier;
        if (isset($settings->{$name})) {
            $sizes[$tier] = (float) $settings->{$name};
        }
    }
    return $sizes;
}

/**
 * Returns SCSS variables for Brightsharp's typography, colour, and shadow settings.
 *
 * The primary colour is handled by Boost's pre-SCSS callback through the
 * Brightsharp brandcolor setting.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_brightsharp_get_pre_scss($theme) {
    $scss = file_get_contents(__DIR__ . '/scss/typography-pre.scss') . "\n";
    $colours = [
        'primarybuttoncolor' => 'primary-button-color',
        'accentcolor' => 'brightsharp-accent-color',
        'pagebackgroundcolor' => 'brightsharp-page-background-color',
        'surfacecolor' => 'brightsharp-surface-color',
        'textcolor' => 'brightsharp-text-color',
        'outlinecolor' => 'brightsharp-outline-color',
        'linkcolor' => 'brightsharp-link-color',
    ];
    foreach ($colours as $setting => $variable) {
        if (!empty($theme->settings->{$setting})) {
            $scss .= '$' . $variable . ': ' . $theme->settings->{$setting} . ";\n";
        }
    }
    foreach (theme_brightsharp_get_shadow_sizes($theme->settings) as $tier => $size) {
        $scss .= '$brightsharp-shadow-' . $tier . ': ' . $size . "px;\n";
    }
    $scss .= file_get_contents(__DIR__ . '/scss/colours-pre.scss') . "\n";

    return $scss;
}

/**
 * Returns neubrutalist styles after Boost's styles.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_brightsharp_get_extra_scss($theme) {
    return file_get_contents(__DIR__ . '/scss/tokens.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/typography.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/buttons.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/forms.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/course.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/cards.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/tables.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/forum.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/dropdowns.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/modals.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/navigationcomponents.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/panels.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/badges.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/overlays.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/navbar.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/secondarynavigation.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/actionmenus.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/notifications.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/breadcrumbs.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/drawers.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/gradebook.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/files.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/activities.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/calendar.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/messaging.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/reportbuilder.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/grading.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/questions.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/dashboard.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/users.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/login.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/activityextras.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/coursemanagement.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/backuprestore.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/editors.scss') . "\n"
        . file_get_contents(__DIR__ . '/scss/layout.scss');
}
