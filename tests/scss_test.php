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

namespace theme_brightsharp;

/**
 * Tests for Brightsharp's colour settings and compiled styles.
 *
 * @package    theme_brightsharp
 * @copyright  Pelorus Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_pre_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_extra_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_shadow_sizes')]
final class scss_test extends \advanced_testcase {
    /**
     * Button colours do not change brand or secondary colours.
     */
    public function test_primary_button_colour(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('brandcolor', '#123456', 'theme_brightsharp');
        set_config('accentcolor', '#654321', 'theme_brightsharp');
        set_config('primarybuttoncolor', '#abcdef', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');

        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $compiler->append_raw_scss(':root { --test-accent: #{$brightsharp-accent-color}; }');
        $css = $compiler->to_css();

        $this->assertStringContainsString('--bs-primary: #123456;', $css);
        $this->assertStringNotContainsString('--bs-secondary: #654321;', $css);
        $this->assertStringContainsString('--test-accent: #654321;', $css);
        preg_match_all('/\.btn-primary\s*\{([^}]+)\}/', $css, $filled);
        $this->assertNotEmpty($filled[1]);
        $this->assertStringContainsString('--bs-btn-bg: #abcdef;', end($filled[1]));
        $this->assertStringContainsString('--bs-btn-hover-bg:', end($filled[1]));
        $this->assertStringContainsString('--bs-btn-active-bg:', end($filled[1]));
        $this->assertStringContainsString('--bs-btn-disabled-bg: #abcdef;', end($filled[1]));

        preg_match_all('/\.btn-outline-primary\s*\{([^}]+)\}/', $css, $outlined);
        $this->assertNotEmpty($outlined[1]);
        $this->assertStringContainsString('--bs-btn-color: #abcdef;', end($outlined[1]));
        $this->assertStringContainsString('--bs-btn-hover-bg: #abcdef;', end($outlined[1]));
        $this->assertStringContainsString('.btn:not(.btn-link):not(.btn-icon):focus-visible', $css);
        $this->assertStringContainsString('--bs-btn-border-width: 3px;', $css);
        $this->assertStringContainsString('outline-offset: 3px;', $css);
    }

    /**
     * An unset button colour uses the brand colour with neubrutalist styling.
     */
    public function test_unset_primary_button_colour(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('brandcolor', '#123456', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');

        $this->assertStringContainsString('$primary: #123456;', $theme->get_pre_scss_code());
        $this->assertStringNotContainsString('$primary-button-color:', $theme->get_pre_scss_code());
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();
        preg_match_all('/\.btn-primary\s*\{([^}]+)\}/', $css, $filled);
        $this->assertNotEmpty($filled[1]);
        $this->assertStringContainsString('--bs-btn-bg: #123456;', end($filled[1]));
        $this->assertStringContainsString('border-radius: 0;', $css);
        $this->assertStringContainsString('--brightsharp-button-shadow-offset: 3px;', $css);
    }

    /**
     * Custom outlines and surfaces are used without changing semantic colours.
     */
    public function test_button_palette_and_states(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('outlinecolor', '#234567', 'theme_brightsharp');
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('primarybuttoncolor', '#ffd23f', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match_all('/\.btn-outline-primary\s*\{([^}]+)\}/', $css, $outlined);
        $this->assertNotEmpty($outlined[1]);
        $this->assertStringContainsString('--bs-btn-bg: #fffdf5;', end($outlined[1]));
        $this->assertStringContainsString('--bs-btn-color: #000000;', end($outlined[1]));
        $this->assertStringContainsString('border-color: #234567;', $css);
        $this->assertStringContainsString('calc(var(--brightsharp-button-shadow-offset) * .2)', $css);
        $this->assertStringContainsString('.btn-check:checked + .btn:not(.btn-link):not(.btn-icon)', $css);
        $this->assertStringContainsString('.btn-check:focus-visible + .btn:not(.btn-link):not(.btn-icon)', $css);
        $this->assertStringContainsString('fieldset:disabled .btn:not(.btn-link):not(.btn-icon)', $css);
        $this->assertStringContainsString('@media (forced-colors: active)', $css);
        $this->assertStringContainsString('.btn-group > .btn:not(.btn-link):not(.btn-icon)', $css);
        preg_match_all('/\.btn-danger\s*\{([^}]+)\}/', $css, $danger);
        $this->assertNotEmpty($danger[1]);
        $this->assertStringNotContainsString('--bs-btn-bg: #ffd23f;', end($danger[1]));
        preg_match(
            '/\.tertiary-navigation \.tertiary-navigation-selector \.btn\.dropdown-toggle\s*\{([^}]+)\}/',
            $css,
            $selectorbutton
        );
        $this->assertNotEmpty($selectorbutton);
        $this->assertStringContainsString(
            'padding: var(--bs-btn-padding-y) var(--bs-btn-padding-x);',
            $selectorbutton[1]
        );
        $this->assertStringNotContainsString('font-size:', $selectorbutton[1]);
        preg_match(
            '/\.initials-selector \.initialswidget\.btn\.dropdown-toggle\s*\{([^}]+)\}/',
            $css,
            $initialsbutton
        );
        $this->assertNotEmpty($initialsbutton);
        $this->assertStringContainsString(
            'padding: var(--bs-btn-padding-y) var(--bs-btn-padding-x) !important;',
            $initialsbutton[1]
        );
        preg_match(
            '/\.navitem \.grade-search > \.gradesearchwidget\.btn\.dropdown-toggle,\s*'
                . '\.navitem \.extrafilters > \.btn\.dropdown-toggle\[data-for="dropdowndialog_button"\]\s*\{([^}]+)\}/',
            $css,
            $advancedbutton
        );
        $this->assertNotEmpty($advancedbutton);
        $this->assertStringContainsString(
            'padding: var(--bs-btn-padding-y) var(--bs-btn-padding-x) !important;',
            $advancedbutton[1]
        );
        preg_match(
            '/\.content-bank-container \.cb-toolbar-container > \.btn\[data-action="viewgrid"\]'
                . ' \+ \.btn\[data-action="viewlist"\]\s*\{([^}]+)\}/',
            $css,
            $viewbuttons
        );
        $this->assertNotEmpty($viewbuttons);
        $this->assertStringContainsString(
            'margin-inline-start: calc(0.5rem + 3px);',
            $viewbuttons[1]
        );
        $this->assertStringNotContainsString('box-shadow:', $viewbuttons[1]);
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $blocks, PREG_SET_ORDER);
        $states = [];
        foreach ($blocks as $block) {
            foreach (explode(',', trim($block[1])) as $compiledselector) {
                $states[trim($compiledselector)][] = $block[2];
            }
        }
        foreach (
            [
            '.content-bank-container .cb-toolbar-container > .btn[data-action="viewgrid"]',
            '.content-bank-container .cb-toolbar-container > .btn[data-action="viewlist"]',
            '.content-bank-container .cb-toolbar-container > .btn-group > .btn[data-action="Add-content"]',
            ] as $selector
        ) {
            $this->assertStringContainsString('--brightsharp-contentbank-shadow-factor: 1;', implode('', $states[$selector]));
            $this->assertStringContainsString(
                'var(--brightsharp-contentbank-shadow-factor)',
                implode('', $states[$selector])
            );
            $this->assertStringContainsString(
                '--brightsharp-contentbank-shadow-factor: .6;',
                implode('', $states[$selector . ':hover'])
            );
            foreach ([':active', '.show'] as $state) {
                $this->assertStringContainsString(
                    '--brightsharp-contentbank-shadow-factor: .2;',
                    implode('', $states[$selector . $state])
                );
            }
            foreach ([':focus-visible', '.focus', ':disabled', '.disabled'] as $state) {
                $this->assertStringContainsString('box-shadow: none;', implode('', $states[$selector . $state]));
            }
            $this->assertStringContainsString('box-shadow: none;', implode('', $states['fieldset:disabled ' . $selector]));
        }
        $this->assertMatchesRegularExpression(
            '/@media \(forced-colors: active\)\s*\{[^{}]*'
                . '\.content-bank-container \.cb-toolbar-container > \.btn-group > \.btn\[data-action="Add-content"\]'
                . '\s*\{\s*box-shadow: none;/',
            $css
        );
    }

    /**
     * Reserved palette settings expose independent variables without changing Boost roles.
     */
    public function test_semantic_palette_variables(): void {
        $this->resetAfterTest();
        $colours = [
            'accentcolor' => 'brightsharp-accent-color',
            'pagebackgroundcolor' => 'brightsharp-page-background-color',
            'surfacecolor' => 'brightsharp-surface-color',
            'textcolor' => 'brightsharp-text-color',
            'outlinecolor' => 'brightsharp-outline-color',
            'linkcolor' => 'brightsharp-link-color',
        ];
        foreach ($colours as $setting => $variable) {
            set_config($setting, '#123456', 'theme_brightsharp');
        }
        $scss = theme_brightsharp_get_pre_scss(\theme_config::load('brightsharp'));
        foreach ($colours as $variable) {
            $this->assertStringContainsString('$' . $variable . ": #123456;\n", $scss);
        }
        $this->assertStringNotContainsString('$secondary:', $scss);
        $this->assertStringNotContainsString('$primary:', $scss);

        foreach ($colours as $setting => $variable) {
            set_config($setting, '', 'theme_brightsharp');
        }
        $emptyscss = theme_brightsharp_get_pre_scss(\theme_config::load('brightsharp'));
        foreach ($colours as $variable) {
            $this->assertStringNotContainsString('$' . $variable . ": #123456;\n", $emptyscss);
        }
    }

    /**
     * Input styling compiles with custom colours and preserves control states.
     */
    public function test_input_palette_and_states(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match_all('/\.form-control,\s*\.form-select\s*\{([^}]+)\}/', $css, $inputs);
        $this->assertNotEmpty($inputs[1]);
        $styles = end($inputs[1]);
        $this->assertStringContainsString('color: #234567;', $styles);
        $this->assertStringContainsString('background-color: #fffdf5;', $styles);
        $this->assertStringContainsString('border: 3px solid #345678;', $styles);
        $this->assertStringContainsString('border-radius: 0;', $styles);
        $this->assertStringContainsString('box-shadow: 2px 2px 0 #345678;', $styles);

        $forms = new \core_scss();
        $forms->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $forms->prepend_raw_scss($theme->get_pre_scss_code());
        // Import definitions without base component styles to inspect only Brightsharp's rules.
        $forms->append_raw_scss(
            '@import "bootstrap/functions"; @import "bootstrap/variables"; @import "bootstrap/mixins";'
                . ' $navbar-height: 60px; $moremenu-height: 60px;'
        );
        $forms->append_raw_scss($theme->get_extra_scss_code());
        $customcss = $forms->to_css();
        $this->assertStringContainsString('.form-select:focus', $customcss);
        $this->assertStringContainsString('outline: 3px solid #1746a2;', $customcss);
        $this->assertStringContainsString('.form-control[readonly]', $customcss);
        $this->assertStringContainsString('.form-select:disabled', $customcss);
        $this->assertStringContainsString('.form-control::file-selector-button', $customcss);
        $this->assertStringContainsString('.input-group > .form-floating > .form-control', $customcss);
        preg_match('/\.input-group > \.form-control\.rounded-start\s*\{([^}]+)\}/', $css, $squaredinput);
        $this->assertNotEmpty($squaredinput);
        $this->assertStringContainsString('border-radius: 0 !important;', $squaredinput[1]);
        $this->assertStringContainsString('.form-check-input[type="radio"]', $customcss);
        $this->assertStringContainsString('border-radius: 50%;', $customcss);
        $this->assertStringContainsString('.form-check-input[type="checkbox"]:indeterminate', $customcss);
        $this->assertStringContainsString('.form-switch .form-check-input:checked:focus', $customcss);
        $this->assertStringContainsString('.was-validated .form-control:invalid:focus', $customcss);
        $this->assertStringContainsString('.form-select.is-valid:focus', $customcss);
        $this->assertStringContainsString('.form-control[aria-invalid="true"]', $customcss);
        $this->assertStringContainsString('.form-range::-webkit-slider-thumb', $customcss);
        $this->assertStringContainsString('.form-range::-moz-range-thumb', $customcss);
        $this->assertStringContainsString('appearance: auto;', $customcss);
        $this->assertStringContainsString('outline-color: Highlight;', $customcss);
    }

    /**
     * Joined inputs use shared elevation, including when a hidden label intervenes.
     */
    public function test_input_group_shadow(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('shadowsizesmall', 6, 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(
            '@import "bootstrap/functions"; @import "bootstrap/variables"; @import "bootstrap/mixins";'
                . ' $navbar-height: 60px; $moremenu-height: 60px;'
        );
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match_all(
            '/\.input-group:has\(> \.form-control, > \.form-select, > \.form-floating\):not\(\.has-validation\)\s*\{([^}]+)\}/',
            $css,
            $groups
        );
        $this->assertNotEmpty($groups[1]);
        $this->assertStringContainsString('box-shadow: 6px 6px 0 #171717;', $groups[1][0]);
        $this->assertStringContainsString('box-shadow: none;', end($groups[1]));
        $this->assertStringContainsString(
            '.input-group:has(> .form-control, > .form-select, > .form-floating):not(.has-validation):focus-within',
            $css
        );
        $this->assertStringNotContainsString('.input-group:not(.has-validation)', $css);
        $this->assertStringNotContainsString('.input-group > :not(:first-child)', $css);
        $this->assertStringContainsString('.input-group > .btn:not(:first-child)', $css);
        $this->assertStringContainsString('.input-group > label[for^="searchinput-"]', $css);
        $this->assertStringContainsString('margin-left: -3px;', $css);
        preg_match(
            '/\.input-group > \.btn:not\(\.btn-link\):not\(\.btn-icon\):hover[^{}]*\{([^}]+)\}/',
            $css,
            $buttons
        );
        $this->assertNotEmpty($buttons);
        $this->assertStringContainsString('box-shadow: none;', $buttons[1]);
        $this->assertStringContainsString('.input-group > .btn:not(.btn-link):not(.btn-icon):active', $css);
    }

    /**
     * Search inputs override Boost's more specific rounded left-corner rules.
     */
    public function test_search_input_corners(): void {
        global $CFG;

        $this->resetAfterTest();
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match_all('/\.simplesearchform \.input-group input\.form-control\s*\{([^}]+)\}/', $css, $search);
        $this->assertGreaterThanOrEqual(2, count($search[1]));
        $this->assertStringContainsString('border-top-left-radius:', $search[1][0]);
        $this->assertStringContainsString('border-radius: 0;', end($search[1]));
        preg_match(
            '/\.simplesearchform \.input-group input\.form-control\.rounded\s*\{([^}]+)\}/',
            $css,
            $autosearch
        );
        $this->assertNotEmpty($autosearch);
        $this->assertStringContainsString('border-radius: 0 !important;', $autosearch[1]);
    }

    /**
     * Navbar buttons suppress decorative borders and shadows while retaining focus.
     */
    public function test_navbar_button_decoration(): void {
        global $CFG;

        $this->resetAfterTest();
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match(
            '/\.navbar \.btn:not\(\.btn-link\):not\(\.btn-icon\):hover[^{}]*\{([^}]+)\}/',
            $css,
            $decoration
        );
        $this->assertNotEmpty($decoration);
        $this->assertStringContainsString('border-width: 0;', $decoration[1]);
        $this->assertStringContainsString('box-shadow: none;', $decoration[1]);
        preg_match(
            '/\.navbar \.btn:not\(\.btn-link\):not\(\.btn-icon\):focus-visible[^{}]*\{([^}]+)\}/',
            $css,
            $focus
        );
        $this->assertNotEmpty($focus);
        $this->assertStringContainsString('outline: 3px solid #1746a2;', $focus[1]);
        $this->assertStringContainsString('box-shadow: none;', $focus[1]);
    }

    /**
     * Content icon buttons are square and flat without changing navbar targets.
     */
    public function test_icon_button_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match('/#page \.btn\.btn-icon\s*\{([^}]+)\}/', $css, $icon);
        $this->assertNotEmpty($icon);
        $this->assertStringContainsString('border: 2px solid transparent;', $icon[1]);
        $this->assertStringContainsString('border-radius: 0;', $icon[1]);
        $this->assertStringContainsString('box-shadow: none;', $icon[1]);
        $this->assertStringNotContainsString('height:', $icon[1]);
        $this->assertStringNotContainsString("\n  width:", $icon[1]);
        preg_match('/#page \.btn\.btn-icon:hover,[^{]+\{([^}]+)\}/', $css, $hover);
        $this->assertNotEmpty($hover);
        $this->assertStringContainsString('border-color: #345678;', $hover[1]);
        $this->assertStringContainsString('[aria-expanded="true"]', $hover[0]);
        preg_match('/#page \.btn\.btn-icon:focus-visible,[^{]+\{([^}]+)\}/', $css, $focus);
        $this->assertNotEmpty($focus);
        $this->assertStringContainsString('outline: 3px solid #1746a2;', $focus[1]);
        $this->assertStringContainsString('box-shadow: none;', $focus[1]);
        preg_match('/#page \.btn\.btn-icon:disabled,[^{]+\{([^}]+)\}/', $css, $disabled);
        $this->assertNotEmpty($disabled);
        $this->assertStringContainsString('border-color: transparent;', $disabled[1]);
        $this->assertStringContainsString('#page fieldset:disabled .btn.btn-icon', $css);
        preg_match('/#page \.btn\.btn-icon:not\(\.btn-link\)[^{]+\{([^}]+)\}/', $css, $neutral);
        $this->assertNotEmpty($neutral);
        $this->assertStringContainsString('--bs-btn-color: #234567;', $neutral[1]);
        $this->assertStringContainsString('--bs-btn-bg: transparent;', $neutral[1]);
        $this->assertStringContainsString(':not(.btn-danger)', $neutral[0]);
        $this->assertStringContainsString(':not(.btn-outline-danger)', $neutral[0]);
        $this->assertStringContainsString(':not(.icons-collapse-expand)', $neutral[0]);
    }

    /**
     * Shared section markup uses square panels regardless of the course format.
     */
    public function test_course_section_panels(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('outlinecolor', '#234567', 'theme_brightsharp');
        set_config('textcolor', '#345678', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match(
            '/\.course-content \.section-list > \.course-section > \.section-item\s*\{([^}]+)\}/',
            $css,
            $panel
        );
        $this->assertNotEmpty($panel);
        $this->assertStringContainsString('background-color: #fffdf5;', $panel[1]);
        $this->assertStringContainsString('border: 3px solid #234567;', $panel[1]);
        $this->assertStringContainsString('border-radius: 0;', $panel[1]);
        $this->assertStringContainsString('box-shadow: none;', $panel[1]);
        $this->assertStringContainsString(
            '.course-content .section-list > .course-section > .section-item > .course-section-header',
            $css
        );
        $this->assertStringContainsString('border-bottom: 2px solid #234567;', $css);
        $this->assertStringContainsString('.section-list > .course-section.hidden > .section-item', $css);
        $this->assertStringContainsString('.section-list > .course-section.orphaned > .section-item', $css);
        $this->assertStringNotContainsString('.topics.section-list', $css);
        preg_match(
            '/\.course-content \.section-list > \.course-section\.current > \.section-item::before\s*\{([^}]+)\}/',
            $css,
            $marker
        );
        $this->assertNotEmpty($marker);
        $this->assertStringContainsString('background-color: #ffd23f;', $marker[1]);
        $this->assertStringContainsString('inset-inline-start: -8px;', $marker[1]);
        $this->assertStringContainsString('inset-inline-end: auto;', $marker[1]);
    }

    /**
     * Body and link colours are independent of the primary brand fill.
     */
    public function test_text_colours(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        foreach ([['#234567', '#345678'], ['', '']] as [$text, $link]) {
            set_config('textcolor', $text, 'theme_brightsharp');
            set_config('linkcolor', $link, 'theme_brightsharp');
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();

            $this->assertStringContainsString('--bs-body-color: ' . ($text ?: '#171717') . ';', $css);
            $this->assertStringContainsString('--bs-link-color: ' . ($link ?: '#1746a2') . ';', $css);
            $this->assertStringContainsString('--bs-primary: #ffd23f;', $css);
            $this->assertStringNotContainsString('--bs-link-color: #ffd23f;', $css);
        }
    }

    /**
     * The main content canvas follows the page background, independently of cards and the dark navbar.
     */
    public function test_page_background_colour(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#ffffff', 'theme_brightsharp');
        foreach (['#abcdef', ''] as $background) {
            set_config('pagebackgroundcolor', $background, 'theme_brightsharp');
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();
            $this->assertStringContainsString('--bs-body-bg: ' . ($background ?: '#fffdf5') . ';', $css);
            preg_match('/(?:^|\n)html\s*\{([^}]+)\}/', $css, $root);
            $this->assertNotEmpty($root);
            $this->assertStringContainsString('background-color: ' . ($background ?: '#fffdf5') . ';', $root[1]);
            preg_match('/\.path-admin \.block_settings\.card\s*\{([^}]+)\}/', $css, $admincard);
            $this->assertNotEmpty($admincard);
            $this->assertStringContainsString('--bs-card-bg: ' . ($background ?: '#fffdf5') . ';', $admincard[1]);
            preg_match_all('/#page\.drawers \.main-inner\s*\{([^}]+)\}/', $css, $panels);
            $this->assertNotEmpty($panels[1]);
            $this->assertStringContainsString('background-color: ' . ($background ?: '#fffdf5') . ';', end($panels[1]));
            preg_match(
                '/@media\s*\(min-width:\s*992px\)\s*\{\s*'
                    . '\.path-admin-reportbuilder\.pagelayout-popup \.dynamictabs \.nav-tabs\s*\{([^}]+)\}/',
                $css,
                $reporttabs
            );
            $this->assertNotEmpty($reporttabs);
            $this->assertStringContainsString('background-color: ' . ($background ?: '#fffdf5') . ';', $reporttabs[1]);
            $this->assertStringContainsString('box-shadow: 0 1.25rem 0 ' . ($background ?: '#fffdf5') . ';', $reporttabs[1]);
            $this->assertStringNotContainsString('position:', $reporttabs[1]);
            $this->assertStringNotContainsString('padding:', $reporttabs[1]);
            preg_match_all(
                '/\.path-admin-reportbuilder\.pagelayout-popup \.dynamictabs \.tab-content\s*\{([^}]+)\}/',
                $css,
                $reportcontent
            );
            $this->assertNotEmpty($reportcontent[1]);
            $this->assertStringContainsString('padding-top: 90px;', end($reportcontent[1]));
            $this->assertStringContainsString('max-height: calc(100vh - 170px - 52px);', $css);
            $this->assertStringContainsString('max-height: calc(100vh - 170px);', $css);
            $this->assertStringContainsString('--bs-body-bg: #171717;', $css);
        }
    }

    /**
     * Navigation compiles with shared colours, fixed geometry, and accessible states.
     */
    public function test_navbar_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('outlinecolor', '#234567', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        set_config('shadowsizemedium', 8, 'theme_brightsharp');
        set_config('shadowsizelarge', 12.8, 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        $this->assertStringContainsString('--bs-body-bg: #234567;', $css);
        $this->assertStringContainsString('border-bottom: 3px solid #234567;', $css);
        preg_match(
            '/\.navbar\.fixed-top \.primary-navigation \.navigation\s*\{([^}]+)\}/',
            $css,
            $height
        );
        $this->assertNotEmpty($height);
        $this->assertStringContainsString('height: 58px;', $height[1]);
        preg_match(
            '/\.navbar\.fixed-top \.primary-navigation \.nav-link\.active,[^{}]+\{([^}]+)\}/',
            $css,
            $active
        );
        $this->assertNotEmpty($active);
        $this->assertStringContainsString('background-color: #ffd23f;', $active[1]);
        $this->assertStringContainsString('color: #000000;', $active[1]);
        $this->assertStringContainsString('border-color: #000;', $active[1]);
        $this->assertStringContainsString('box-shadow: 8px 8px 0 #fff;', $active[1]);
        $this->assertStringContainsString('.navbar.fixed-top .usermenu .dropdown-menu', $css);
        $this->assertStringContainsString('box-shadow: 12.8px 12.8px 0 #234567;', $css);
        $this->assertStringContainsString('.navbar.fixed-top .primary-navigation .nav-link:focus-visible', $css);
        $this->assertStringContainsString('.drawer-primary .drawercontent .list-group-item.active', $css);
        preg_match('/\.drawer-primary \.drawerheader\s*\{([^}]+)\}/', $css, $drawerheader);
        $this->assertNotEmpty($drawerheader);
        $this->assertStringContainsString('padding-inline-start: 1rem;', $drawerheader[1]);
        $this->assertStringContainsString('border-inline-end: 3px solid #234567;', $css);
        $this->assertStringContainsString('outline-offset: -3px;', $css);
    }

    /**
     * Local fonts resolve through Moodle and compile into the correct roles.
     */
    public function test_theme_typography(): void {
        global $CFG;

        $this->resetAfterTest();
        $theme = \theme_config::load('brightsharp');
        foreach (['inter-variable.woff2', 'space-grotesk-variable.woff2'] as $font) {
            $this->assertSame($CFG->dirroot . '/theme/brightsharp/fonts/' . $font, $theme->resolve_font_location($font, 'theme'));
        }
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        $this->assertStringContainsString('[[font:theme|inter-variable.woff2]]', $css);
        $this->assertStringContainsString('[[font:theme|space-grotesk-variable.woff2]]', $css);
        $this->assertStringContainsString('font-weight: 100 900;', $css);
        $this->assertStringContainsString('font-weight: 300 700;', $css);
        $this->assertStringContainsString('font-display: swap;', $css);
        preg_match('/body\s*\{([^}]+)\}/', $css, $body);
        $this->assertNotEmpty($body);
        $this->assertStringContainsString('font-family: var(--bs-body-font-family);', $body[1]);
        $this->assertStringContainsString('--bs-body-font-family: var(--bs-font-sans-serif);', $css);
        $this->assertMatchesRegularExpression('/--bs-font-sans-serif:\s*["\']?Inter["\']?, system-ui, sans-serif;/', $css);
        preg_match('/h1,\s*\.h1,[^{}]+\{([^}]+)\}/', $css, $headings);
        $this->assertNotEmpty($headings);
        $this->assertStringContainsString('Space Grotesk', $headings[1]);
        preg_match('/\.card-title,\s*\.sectionname\s*\{([^}]+)\}/', $css, $titles);
        $this->assertNotEmpty($titles);
        $this->assertStringContainsString('Space Grotesk', $titles[1]);
    }

    /**
     * Card defaults use tokens rather than overriding semantic utility colours.
     */
    public function test_card_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('outlinecolor', '#234567', 'theme_brightsharp');
        set_config('shadowsizemedium', 8, 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match_all('/(?<![a-z-])\.card\s*\{([^}]+)\}/', $css, $cards);
        $this->assertNotEmpty($cards[1]);
        $defaults = array_values(array_filter($cards[1], fn($rule) => str_contains($rule, '--bs-card-cap-bg: transparent;')));
        $this->assertCount(1, $defaults);
        $this->assertStringContainsString('--bs-card-border-width: 3px;', $defaults[0]);
        $this->assertStringContainsString('--bs-card-border-color: #234567;', $defaults[0]);
        $this->assertStringContainsString('--bs-card-bg: #fffdf5;', $defaults[0]);
        $this->assertStringContainsString('--bs-card-inner-border-radius: 0;', $defaults[0]);
        $this->assertStringContainsString('box-shadow: none;', $defaults[0]);
        $this->assertStringNotContainsString("\n  color:", $defaults[0]);
        $this->assertStringNotContainsString("\n  border-color:", $defaults[0]);
        $this->assertStringNotContainsString("\n  background-color:", $defaults[0]);
        $this->assertStringContainsString('border-color: inherit;', $css);
        $this->assertStringContainsString('box-shadow: 8px 8px 0 #234567 !important;', $css);
        $this->assertStringContainsString('.card.brightsharp-card-raised', $css);
        $this->assertStringContainsString('.border-danger', $css);
        $this->assertStringContainsString('.text-bg-success', $css);
        $this->assertStringContainsString('box-shadow: none !important;', $css);
    }

    /**
     * Accent strips are opt-in and leave panel text and surface colours unchanged.
     */
    public function test_accent_panel_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        foreach (['#123456', '', '#000000'] as $accent) {
            set_config('accentcolor', $accent, 'theme_brightsharp');
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();

            preg_match('/(?:^|\n)\.brightsharp-accent-panel\s*\{([^}]+)\}/', $css, $panel);
            $this->assertNotEmpty($panel);
            $expected = $accent === '' ? '#ff90e8' : $accent;
            $this->assertStringContainsString("border-inline-start: 0.5rem solid {$expected};", $panel[1]);
            $this->assertStringContainsString('border-radius: 0;', $panel[1]);
            $this->assertStringNotContainsString('background', $panel[1]);
            $this->assertStringNotContainsString("\n  color:", $panel[1]);
            $this->assertStringNotContainsString('box-shadow:', $panel[1]);
            preg_match_all('/\.brightsharp-accent-panel\s*\{([^}]+)\}/', $css, $panels);
            $this->assertStringContainsString('border-inline-start-color: CanvasText;', end($panels[1]));
        }
    }

    /**
     * Breadcrumbs use independent text colours without changing responsive markup.
     */
    public function test_breadcrumb_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        set_config('linkcolor', '#123456', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match('/#page-navbar \.breadcrumb\s*\{([^}]+)\}/', $css, $breadcrumb);
        $this->assertNotEmpty($breadcrumb);
        $this->assertStringContainsString('--bs-breadcrumb-bg: transparent;', $breadcrumb[1]);
        $this->assertStringContainsString('--bs-breadcrumb-border-radius: 0;', $breadcrumb[1]);
        $this->assertStringContainsString('--bs-breadcrumb-divider: "/";', $breadcrumb[1]);
        $this->assertStringContainsString('--bs-breadcrumb-divider-color: #345678;', $breadcrumb[1]);
        $this->assertStringContainsString('--bs-breadcrumb-item-padding-x: 0.75rem;', $breadcrumb[1]);
        $this->assertStringContainsString('border: 0;', $breadcrumb[1]);
        $this->assertStringContainsString('box-shadow: none;', $breadcrumb[1]);
        preg_match('/#page-navbar \.breadcrumb \.breadcrumb-item:not\(\.dimmed_text\) a\s*\{([^}]+)\}/', $css, $links);
        $this->assertNotEmpty($links);
        $this->assertStringContainsString('color: #123456;', $links[1]);
        preg_match('/#page-navbar \.breadcrumb \.breadcrumb-item a\s*\{([^}]+)\}/', $css, $decoration);
        $this->assertNotEmpty($decoration);
        $this->assertStringContainsString('text-decoration: underline;', $decoration[1]);
        preg_match('/#page-navbar \.breadcrumb \.breadcrumb-item:not\(\.dimmed_text\) span,[^{]+\{([^}]+)\}/', $css, $current);
        $this->assertNotEmpty($current);
        $this->assertStringContainsString('color: #234567;', $current[1]);
        $this->assertStringContainsString('font-weight: 700;', $current[1]);
        $this->assertStringContainsString('a[aria-current="page"]', $current[0]);
        preg_match('/#page-navbar \.breadcrumb \.breadcrumb-item a:focus-visible\s*\{([^}]+)\}/', $css, $focus);
        $this->assertNotEmpty($focus);
        $this->assertStringContainsString('outline: 3px solid #1746a2;', $focus[1]);
        $this->assertStringContainsString('outline-offset: -3px;', $focus[1]);
        $this->assertStringContainsString('outline-color: Highlight;', $css);
        $this->assertStringContainsString('.breadcrumb:not(:empty)', $css);
        $this->assertStringContainsString('text-overflow: ellipsis;', $css);
    }

    /**
     * Secondary tabs retain fixed-height overflow and independent inactive colours.
     */
    public function test_secondary_navigation_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        foreach ([5, 0] as $size) {
            set_config('shadowsizesmall', $size, 'theme_brightsharp');
            set_config('shadowsizelarge', $size, 'theme_brightsharp');
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();

            preg_match_all('/\.secondary-navigation \.navigation\s*\{([^}]+)\}/', $css, $navigation);
            $this->assertNotEmpty($navigation[1]);
            $defaults = array_values(array_filter(
                $navigation[1],
                fn($rule) => str_contains($rule, 'border-bottom: 3px solid #345678;'),
            ));
            $this->assertCount(1, $defaults);
            $this->assertStringContainsString('background-color: transparent;', $defaults[0]);
            $selector = '\.secondary-navigation \.navigation \.nav-tabs > \.nav-item > \.nav-link';
            preg_match('/' . $selector . '\s*\{([^}]+)\}/', $css, $links);
            $this->assertNotEmpty($links);
            $this->assertStringContainsString('color: #234567;', $links[1]);
            $this->assertStringContainsString('height: calc(57px - 0.5rem);', $links[1]);
            $this->assertStringContainsString('border: 2px solid transparent;', $links[1]);
            $this->assertStringContainsString('border-radius: 0;', $links[1]);
            $this->assertStringContainsString('box-shadow: none;', $links[1]);
            preg_match('/' . $selector . '\.active,[^{]+\{([^}]+)\}/', $css, $active);
            $this->assertNotEmpty($active);
            $this->assertStringContainsString('background-color: #ffd23f;', $active[1]);
            $this->assertStringContainsString('color: #000000;', $active[1]);
            $offset = $size;
            $this->assertStringContainsString("box-shadow: {$offset}px {$offset}px 0 #345678;", $active[1]);
            preg_match('/' . $selector . ':focus-visible,[^{]+\{([^}]+)\}/', $css, $focus);
            $this->assertNotEmpty($focus);
            $this->assertStringContainsString('outline: 3px solid #1746a2;', $focus[1]);
            $this->assertStringContainsString('box-shadow: none;', $focus[1]);
            preg_match('/\.secondary-navigation \.navigation \.dropdown-menu\s*\{([^}]+)\}/', $css, $menu);
            $this->assertNotEmpty($menu);
            $this->assertStringContainsString('--bs-dropdown-bg: #fffdf5;', $menu[1]);
            $largeoffset = $size;
            $this->assertStringContainsString("box-shadow: {$largeoffset}px {$largeoffset}px 0 #345678;", $menu[1]);
            $this->assertStringContainsString(
                '.secondary-navigation .navigation .dropdownmoremenu .dropdown-menu .dropdown-menu',
                $css
            );
            $this->assertStringContainsString('.moremenu .dropdown-item[aria-current="true"]:before', $css);
        }
    }

    /**
     * Action menus share navigation panels without overriding disabled row states.
     */
    public function test_action_menu_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        foreach ([8, 0] as $size) {
            set_config('shadowsizelarge', $size, 'theme_brightsharp');
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();

            preg_match('/\.action-menu \.dropdown-menu\s*\{([^}]+)\}/', $css, $panel);
            $this->assertNotEmpty($panel);
            $this->assertStringContainsString('--bs-dropdown-bg: #fffdf5;', $panel[1]);
            $this->assertStringContainsString('--bs-dropdown-link-color: #234567;', $panel[1]);
            $this->assertStringContainsString('border: 3px solid #345678;', $panel[1]);
            $this->assertStringContainsString('border-radius: 0;', $panel[1]);
            $largeoffset = $size;
            $this->assertStringContainsString("box-shadow: {$largeoffset}px {$largeoffset}px 0 #345678;", $panel[1]);
            preg_match('/\.dropdown-menu:not\(\.dropdown-menu-dark\)\s*\{([^}]+)\}/', $css, $plainpanel);
            $this->assertNotEmpty($plainpanel);
            $this->assertSame($panel[1], $plainpanel[1]);
            preg_match_all('/(?:^|\n)\.dropdown-menu \.dropdown-item\s*\{([^}]+)\}/', $css, $plainrows);
            $this->assertNotEmpty($plainrows[1]);
            $plainrow = end($plainrows[1]);
            $this->assertStringContainsString('white-space: normal;', $plainrow);
            $this->assertStringNotContainsString('display:', $plainrow);
            foreach (
                [
                '.initialsdropdown.dropdown-menu .initialbargroups' => ['min-width: 0;', 'max-width: 100%;'],
                '.initialsdropdown.dropdown-menu .initialbargroups .pagination' =>
                    ['flex-wrap: wrap;', 'max-width: 100%;', 'row-gap: 0.25rem;'],
                '.initialsdropdown.dropdown-menu .initialbargroups .page-item' => ['flex-shrink: 0;'],
                '.initialsdropdown.dropdown-menu .initialbargroups .pagination.pagination-sm' => ['margin-left: 0;'],
                ] as $selector => $declarations
            ) {
                preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]+)\}/', $css, $initialsrule);
                $this->assertNotEmpty($initialsrule, $selector);
                foreach ($declarations as $declaration) {
                    $this->assertStringContainsString($declaration, $initialsrule[1]);
                }
            }
            preg_match('/\.action-menu \.dropdown-menu \.dropdown-item\s*\{([^}]+)\}/', $css, $row);
            $this->assertNotEmpty($row);
            $this->assertStringContainsString('white-space: normal;', $row[1]);
            $this->assertStringContainsString('overflow-wrap: anywhere;', $row[1]);
            $this->assertStringNotContainsString('background-color:', $row[1]);
            $this->assertStringNotContainsString("\n  color:", $row[1]);
            preg_match('/\.action-menu \.dropdown-menu \.dropdown-item:focus-visible,[^{]+\{([^}]+)\}/', $css, $focus);
            $this->assertNotEmpty($focus);
            $this->assertStringContainsString('outline: 3px solid #1746a2;', $focus[1]);
            $this->assertStringContainsString('outline-offset: -3px;', $focus[1]);
            $this->assertStringContainsString('.dropdown-item-outline:focus-within', $focus[0]);
            preg_match_all('/\.action-menu \.dropdown-menu\s*\{([^}]+)\}/', $css, $panels);
            $forcedcolours = end($panels[1]);
            $this->assertStringContainsString('border-color: CanvasText;', $forcedcolours);
            $this->assertStringContainsString('box-shadow: none;', $forcedcolours);
        }
    }

    /**
     * Notifications change geometry without replacing semantic alert or toast colours.
     */
    public function test_notification_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        foreach ([5, 0] as $size) {
            set_config('shadowsizesmall', $size, 'theme_brightsharp');
            set_config('shadowsizelarge', $size, 'theme_brightsharp');
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();

            preg_match_all('/(?:^|\n)\.alert\s*\{([^}]+)\}/', $css, $alerts);
            $customalerts = array_values(array_filter($alerts[1], fn($rule) => str_contains($rule, 'box-shadow: none;')));
            $this->assertCount(1, $customalerts);
            $this->assertStringContainsString('--bs-alert-border: 3px solid var(--bs-alert-border-color);', $customalerts[0]);
            $this->assertStringContainsString('border-radius: 0;', $customalerts[0]);
            $this->assertStringNotContainsString('--bs-alert-color:', $customalerts[0]);
            $this->assertStringNotContainsString('--bs-alert-bg:', $customalerts[0]);
            $this->assertStringNotContainsString('--bs-alert-border-color:', $customalerts[0]);
            $this->assertStringContainsString('.alert a:not(.btn)', $css);
            preg_match_all('/(?:^|\n)\.toast\s*\{([^}]+)\}/', $css, $toasts);
            $customtoasts = array_values(array_filter($toasts[1], fn($rule) => str_contains($rule, '--bs-toast-bg: #fffdf5;')));
            $this->assertCount(1, $customtoasts);
            $this->assertStringContainsString('--bs-toast-border-width: 3px;', $customtoasts[0]);
            $offset = $size;
            $this->assertStringContainsString("--bs-toast-box-shadow: {$offset}px {$offset}px 0 #345678;", $customtoasts[0]);
            $this->assertStringNotContainsString("\n  background-color:", $customtoasts[0]);
            $this->assertStringNotContainsString("\n  color:", $customtoasts[0]);
            foreach (['success', 'danger', 'warning', 'info'] as $status) {
                $this->assertStringContainsString(".toast.toast-{$status} .toast-body:before", $css);
                $this->assertStringContainsString(".alert-{$status}", $css);
            }
            preg_match('/\.popover-region-notifications \.popover-region-container\s*\{([^}]+)\}/', $css, $panel);
            $this->assertNotEmpty($panel);
            $this->assertStringContainsString('color: #234567;', $panel[1]);
            $this->assertStringContainsString('background-color: #fffdf5;', $panel[1]);
            $this->assertStringContainsString('border: 3px solid #345678;', $panel[1]);
            $largeoffset = $size;
            $this->assertStringContainsString("box-shadow: {$largeoffset}px {$largeoffset}px 0 #345678;", $panel[1]);
            $this->assertStringNotContainsString("\n  height:", $panel[1]);
            $this->assertStringNotContainsString("\n  width:", $panel[1]);
            $this->assertStringContainsString('.content-item-container.unread', $css);
            $this->assertStringContainsString('.alert .btn-close:focus-visible', $css);
            $this->assertStringContainsString('.toast .btn-close:focus-visible', $css);
            $this->assertStringContainsString('--bs-toast-box-shadow: none;', $css);
        }
    }

    /**
     * Content drawers preserve navigation states while using flat light surfaces.
     */
    public function test_content_drawer_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        foreach ([5, 0] as $size) {
            set_config('shadowsizesmall', $size, 'theme_brightsharp');
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();

            preg_match('/#theme_boost-drawers-courseindex,\s*#theme_boost-drawers-blocks\s*\{([^}]+)\}/', $css, $drawers);
            $this->assertNotEmpty($drawers);
            $this->assertStringContainsString('background-color: #fffdf5;', $drawers[1]);
            $this->assertStringContainsString('box-shadow: none;', $drawers[1]);
            $this->assertStringNotContainsString('width:', $drawers[1]);
            $this->assertStringNotContainsString('height:', $drawers[1]);
            $this->assertStringNotContainsString('overflow:', $drawers[1]);
            $this->assertStringContainsString('border-inline-end: 3px solid #345678;', $css);
            $this->assertStringContainsString('border-inline-start: 3px solid #345678;', $css);
            preg_match(
                '/#theme_boost-drawers-courseindex \.courseindex-item\.pageitem:not\(\.dragging\),[^{]+\{([^}]+)\}/',
                $css,
                $current
            );
            $this->assertNotEmpty($current);
            $this->assertStringContainsString('background-color: #ffd23f;', $current[1]);
            $this->assertStringContainsString('color: #000000;', $current[1]);
            $this->assertStringContainsString('border-inline-start: 3px solid #345678;', $current[1]);
            $this->assertStringContainsString('.courseindex .courseindex-item .completioninfo.completion_complete', $css);
            $this->assertStringContainsString('.courseindex .courseindex-item .completioninfo.completion_fail', $css);
            $this->assertStringContainsString('.courseindex .courseindex-item.dragging', $css);
            $this->assertStringContainsString('#theme_boost-drawers-courseindex .courseindex-item.dimmed', $css);
            $this->assertStringContainsString('#theme_boost-drawers-courseindex .courseindex-section-title', $css);
            $this->assertStringContainsString('#theme_boost-drawers-blocks .btn.btn-icon:focus-visible', $css);
            foreach (
                [
                '#theme_boost-drawers-blocks .block_navigation .block_tree' => ['padding: 0;'],
                '#theme_boost-drawers-blocks .block_navigation .block_tree ul[role="group"]' =>
                    ['margin: 0;', 'padding: 0;', 'padding-inline-start: 0.75rem;'],
                '#theme_boost-drawers-blocks .block_navigation .block_tree .tree_item.branch' => ['margin: 3px 0;'],
                ] as $selector => $declarations
            ) {
                preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]+)\}/', $css, $treerule);
                $this->assertNotEmpty($treerule, $selector);
                foreach ($declarations as $declaration) {
                    $this->assertStringContainsString($declaration, $treerule[1]);
                }
                $this->assertStringNotContainsString('display:', $treerule[1]);
                $this->assertStringNotContainsString('overflow:', $treerule[1]);
            }
            $offset = $size;
            $this->assertStringContainsString("box-shadow: {$offset}px 0 0 #345678;", $css);
            $this->assertStringContainsString('.dir-rtl #theme_boost-drawers-blocks.show', $css);
            preg_match('/#page \.drawer-toggler \.btn\.btn-icon\s*\{([^}]+)\}/', $css, $toggle);
            $this->assertNotEmpty($toggle);
            $this->assertStringContainsString('border: 2px solid #345678;', $toggle[1]);
            $this->assertStringContainsString('box-shadow: none;', $toggle[1]);
        }
    }

    /**
     * Tables and forum posts use structural styling without replacing state variants.
     */
    public function test_tables_and_forum_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        $this->assertStringContainsString('--bs-table-color: #234567;', $css);
        $this->assertStringContainsString('--bs-table-bg: #fffdf5;', $css);
        preg_match('/(?:^|\n)\.table,\s*\.generaltable\s*\{([^}]+)\}/', $css, $table);
        $this->assertNotEmpty($table);
        $this->assertStringContainsString('border: 3px solid var(--bs-table-border-color,', $table[1]);
        $this->assertStringContainsString('border-radius: 0;', $table[1]);
        $this->assertStringNotContainsString('box-shadow:', $table[1]);
        preg_match_all('/(?:^|\n)\.table-borderless\s*\{([^}]+)\}/', $css, $borderless);
        $this->assertNotEmpty($borderless[1]);
        $this->assertStringContainsString('border: 0;', end($borderless[1]));
        preg_match('/(?:^|\n)\.table-danger\s*\{([^}]+)\}/', $css, $danger);
        $this->assertNotEmpty($danger);
        $this->assertStringNotContainsString('--bs-table-bg: #fffdf5;', $danger[1]);
        $this->assertStringContainsString('.table-striped > tbody > tr:nth-of-type(odd)', $css);
        $this->assertStringContainsString('.table-hover > tbody > tr:hover', $css);
        $this->assertStringContainsString('.table-responsive', $css);
        preg_match('/\.course-overview\.table-responsive\.rounded\s*\{([^}]+)\}/', $css, $overview);
        $this->assertNotEmpty($overview);
        $this->assertStringContainsString('border-radius: 0 !important;', $overview[1]);
        $this->assertStringNotContainsString('overflow:', $overview[1]);
        preg_match(
            '/#course-overview-page \.course-overview\.table-responsive\s*\{([^}]+)\}/',
            $css,
            $overviewframe
        );
        $this->assertNotEmpty($overviewframe);
        $this->assertStringContainsString('border: 3px solid #b8c3c9 !important;', $overviewframe[1]);
        $this->assertStringContainsString('padding-top: 0 !important;', $overviewframe[1]);
        $this->assertStringNotContainsString('overflow:', $overviewframe[1]);
        preg_match(
            '/#course-overview-page \.course-overview\.table-responsive > \.course-overview-table\.table\s*\{([^}]+)\}/',
            $css,
            $overviewtable
        );
        $this->assertNotEmpty($overviewtable);
        $this->assertStringContainsString('border: 0;', $overviewtable[1]);
        $this->assertStringContainsString('border-color: CanvasText !important;', $css);
        preg_match_all('/(?:^|\n)\.forumpost\s*\{([^}]+)\}/', $css, $posts);
        $customposts = array_values(array_filter($posts[1], fn($rule) => str_contains($rule, '--bs-border-width: 3px;')));
        $this->assertCount(1, $customposts);
        $this->assertStringContainsString('--bs-border-color: #345678;', $customposts[0]);
        $this->assertStringContainsString('background-color: #fffdf5;', $customposts[0]);
        $this->assertStringContainsString('box-shadow: none;', $customposts[0]);
        $this->assertStringContainsString('border-inline-start-width: 6px;', $css);
        $this->assertStringContainsString('.firstpost[data-content="forum-post"]', $css);
        preg_match(
            '/\.path-mod-forum \.nested-v2-display-mode \.replies-container \.forum-post-container,[^{]+\{([^}]+)\}/',
            $css,
            $replies
        );
        $this->assertNotEmpty($replies);
        $this->assertStringNotContainsString('border:', $replies[1]);
        $this->assertStringNotContainsString('padding:', $replies[1]);
        $this->assertStringContainsString('box-shadow: none;', $replies[1]);
        $this->assertStringContainsString('.path-mod-forum .forum-post-container:focus-visible', $css);
    }

    /**
     * Supporting components share geometry without replacing behaviour or status colours.
     */
    public function test_supporting_component_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        foreach ([5, 0] as $size) {
            set_config('shadowsizelarge', $size, 'theme_brightsharp');
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();

            foreach (['modal', 'pagination', 'accordion', 'popover', 'tooltip'] as $component) {
                preg_match_all('/(?:^|\n)\.' . $component . '\s*\{([^}]+)\}/', $css, $rules);
                $custom = array_values(array_filter(
                    $rules[1],
                    fn($rule) => str_contains($rule, "--bs-{$component}-border-radius: 0;"),
                ));
                $this->assertCount(1, $custom, $component);
                $this->assertStringNotContainsString("\n  width:", $custom[0]);
                $this->assertStringNotContainsString("\n  height:", $custom[0]);
                $this->assertStringNotContainsString("\n  display:", $custom[0]);
            }
            $largeoffset = $size;
            $this->assertStringContainsString("--bs-modal-box-shadow: {$largeoffset}px {$largeoffset}px 0 #345678;", $css);
            $this->assertStringContainsString('box-shadow: var(--bs-modal-box-shadow);', $css);
            $this->assertStringContainsString('box-shadow: var(--bs-popover-box-shadow);', $css);
            $offset = $size;
            $this->assertStringContainsString("--bs-popover-box-shadow: {$offset}px {$offset}px 0 #345678;", $css);
            $this->assertStringContainsString('--bs-modal-bg: #fffdf5;', $css);
            $this->assertStringContainsString('.modal-fullscreen .modal-content', $css);
            $this->assertStringContainsString('.modal-dialog-scrollable', $css);
            $this->assertStringContainsString('.modal-backdrop', $css);
            $this->assertStringContainsString('--bs-pagination-active-color: #000000;', $css);
            $this->assertStringContainsString('--bs-pagination-active-bg: #ffd23f;', $css);
            $this->assertStringContainsString('margin-inline-start: -2px;', $css);
            $this->assertStringContainsString('.pagination .page-link:focus-visible', $css);
            $this->assertStringContainsString('.nav-tabs:not(.more-nav)', $css);
            $this->assertStringContainsString('.nav-pills:not(.more-nav)', $css);
            $this->assertStringContainsString('--bs-nav-pills-link-active-bg: #ffd23f;', $css);
            $this->assertStringContainsString('--bs-accordion-bg: #fffdf5;', $css);
            $this->assertStringContainsString('.accordion-flush > .accordion-item', $css);
            $this->assertStringContainsString('.accordion .accordion-button:focus-visible', $css);
            $this->assertStringContainsString('.collapsibleregioncaption a:focus-visible', $css);
            preg_match('/\.badge:not\(\.count-container\)\s*\{([^}]+)\}/', $css, $badge);
            $this->assertNotEmpty($badge);
            $this->assertStringContainsString('border-radius: 0;', $badge[1]);
            $this->assertStringNotContainsString("\n  color:", $badge[1]);
            $this->assertStringNotContainsString('background', $badge[1]);
            $this->assertStringContainsString('.badge.rounded-pill:not(.count-container)', $css);
            $this->assertStringContainsString('--bs-tooltip-bg: #345678;', $css);
            $this->assertStringContainsString('--bs-popover-body-color: #234567;', $css);
            $this->assertStringContainsString('--bs-modal-box-shadow: none;', $css);
            $this->assertStringContainsString('--bs-popover-box-shadow: none;', $css);
            $this->assertStringContainsString('border-inline-end: 2px solid ButtonText;', $css);
        }
    }

    /**
     * File tools and gradebook structure retain state colours and specialised layouts.
     */
    public function test_files_and_gradebook_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();

        preg_match('/\.path-grade-report-grader \.gradeparent table\s*\{([^}]+)\}/', $css, $grade);
        $this->assertNotEmpty($grade);
        $this->assertStringContainsString('border-color: #345678;', $grade[1]);
        $this->assertStringNotContainsString('background', $grade[1]);
        $this->assertStringNotContainsString('position:', $grade[1]);
        $this->assertStringNotContainsString('width:', $grade[1]);
        $this->assertStringContainsString('.path-grade-report-grader .gradepass', $css);
        $this->assertStringContainsString('.path-grade-report-grader .gradefail', $css);
        preg_match_all('/\.filepicker-filelist,\s*\.filemanager-container\s*\{([^}]+)\}/', $css, $containers);
        $custom = array_values(array_filter($containers[1], fn($rule) => str_contains($rule, 'border: 3px solid #345678;')));
        $this->assertCount(1, $custom);
        $this->assertStringContainsString('border-radius: 0;', $custom[0]);
        $this->assertStringNotContainsString('overflow:', $custom[0]);
        $this->assertStringContainsString('.file-picker .fp-list .fp-repo.active a', $css);
        $this->assertStringContainsString('.filemanager .fp-viewbar:not(.disabled) a.checked', $css);
        $this->assertStringContainsString('.filepicker.moodle-dialogue-fullscreen .moodle-dialogue-wrap', $css);
        $this->assertStringContainsString('.filemanager-container.dndupload-over .dndupload-target', $css);
        $this->assertStringContainsString('.fitem.disabled .filemanager-container', $css);
    }

    /**
     * All three shadow tiers support configured, zero, and unset values.
     */
    public function test_shadow_size(): void {
        global $CFG;

        $this->resetAfterTest();
        foreach ([null, 10, 0] as $size) {
            foreach (['small', 'medium', 'large'] as $tier) {
                if ($size === null) {
                    unset_config('shadowsize' . $tier, 'theme_brightsharp');
                } else {
                    set_config('shadowsize' . $tier, $size, 'theme_brightsharp');
                }
            }
            $theme = \theme_config::load('brightsharp');
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(
                '@import "bootstrap/functions"; @import "bootstrap/variables"; @import "bootstrap/mixins";'
                    . ' $navbar-height: 60px; $moremenu-height: 60px;'
            );
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $css = $compiler->to_css();
            $offset = $size ?? 3;
            $smalloffset = $size ?? 2;
            $this->assertStringContainsString("--brightsharp-button-shadow-offset: {$offset}px;", $css);
            $this->assertStringContainsString("--brightsharp-button-shadow-offset: {$smalloffset}px;", $css);
            $this->assertStringContainsString("box-shadow: {$smalloffset}px {$smalloffset}px 0", $css);
            $largeoffset = $size ?? 4;
            foreach (
                [
                '.card.shadow-sm' => $smalloffset,
                '.card.shadow' => $offset,
                '.card.shadow-lg' => $largeoffset,
                '.card.brightsharp-card-raised' => $offset,
                ] as $selector => $tieroffset
            ) {
                preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]+)\}/', $css, $tier);
                $this->assertNotEmpty($tier, $selector);
                $this->assertStringContainsString("box-shadow: {$tieroffset}px {$tieroffset}px 0 #171717", $tier[1]);
            }
            $this->assertStringContainsString("--bs-modal-box-shadow: {$largeoffset}px {$largeoffset}px 0 #171717;", $css);
            $this->assertStringContainsString("--bs-popover-box-shadow: {$largeoffset}px {$largeoffset}px 0 #171717;", $css);
            $this->assertStringContainsString("max-width: calc(100vw - 2 * 1rem - {$largeoffset}px);", $css);
            $this->assertStringContainsString('calc(var(--brightsharp-button-shadow-offset) * .6)', $css);
            $this->assertStringContainsString('calc(var(--brightsharp-button-shadow-offset) * .2)', $css);
        }

        require_once($CFG->libdir . '/adminlib.php');
        $ADMIN = (object) ['fulltree' => true];
        include($CFG->dirroot . '/theme/brightsharp/settings.php');
        $shadowsettings = array_filter(
            (array) $settings->settings,
            fn($setting) => str_starts_with($setting->name, 'shadowsize')
        );
        $this->assertCount(3, $shadowsettings);
        foreach ($shadowsettings as $setting) {
            foreach (['0', '5', '10', '1.5'] as $value) {
                $this->assertTrue($setting->validate($value));
            }
            foreach (['', '-1', '5px', 'abc'] as $value) {
                $this->assertNotSame(true, $setting->validate($value));
            }
        }
    }

    /**
     * Each tier is independent, including zero, fractional sizes, and ignored legacy values.
     */
    public function test_independent_shadow_sizes(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('shadowsize', 10, 'theme_brightsharp');
        set_config('shadowsizesmall', 0, 'theme_brightsharp');
        set_config('shadowsizemedium', 7, 'theme_brightsharp');
        set_config('shadowsizelarge', 9.5, 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $this->assertSame(
            ['small' => 0.0, 'medium' => 7.0, 'large' => 9.5],
            theme_brightsharp_get_shadow_sizes($theme->settings)
        );
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(
            '@import "bootstrap/functions"; @import "bootstrap/variables"; @import "bootstrap/mixins";'
                . ' $navbar-height: 60px; $moremenu-height: 60px;'
        );
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();
        foreach (
            [
            '.card.shadow-sm' => 0,
            '.card.shadow' => 7,
            '.card.shadow-lg' => 9.5,
            ] as $selector => $offset
        ) {
            preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]+)\}/', $css, $rule);
            $this->assertNotEmpty($rule);
            $this->assertStringContainsString("box-shadow: {$offset}px {$offset}px 0 #171717", $rule[1]);
        }
        $this->assertStringContainsString('--brightsharp-button-shadow-offset: 7px;', $css);
        $this->assertStringContainsString('--brightsharp-button-shadow-offset: 0px;', $css);
        $this->assertStringContainsString('--bs-modal-box-shadow: 9.5px 9.5px 0 #171717;', $css);
        $this->assertSame(
            ['small' => 2, 'medium' => 3, 'large' => 4],
            theme_brightsharp_get_shadow_sizes(new \stdClass())
        );
        $this->assertSame(
            ['small' => 0.0, 'medium' => 3, 'large' => 4],
            theme_brightsharp_get_shadow_sizes((object) ['shadowsizesmall' => 0, 'shadowsize' => 10])
        );
    }
}
