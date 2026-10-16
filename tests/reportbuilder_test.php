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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace theme_brightsharp;

/**
 * Report builder presentation regressions.
 *
 * @package    theme_brightsharp
 * @copyright  Pelorus Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_pre_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_extra_scss')]
final class reportbuilder_test extends \advanced_testcase {
    /**
     * Compile baseline or themed CSS.
     *
     * @param bool $overrides Whether to include theme overrides.
     * @return string
     */
    private function compile_css(bool $overrides): string {
        global $CFG;

        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        if ($overrides) {
            $compiler->append_raw_scss($theme->get_extra_scss_code());
            $module = file_get_contents($CFG->dirroot . '/theme/brightsharp/scss/reportbuilder.scss');
            if (!str_contains($theme->get_extra_scss_code(), $module)) {
                $compiler->append_raw_scss($module);
            }
        }
        return $compiler->to_css();
    }

    /**
     * Extract an exact selector's declarations.
     *
     * @param string $css CSS.
     * @param string $selector Selector.
     * @return array
     */
    private function rules(string $css, string $selector): array {
        $css = preg_replace('~/\*.*?\*/~s', '', $css);
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $matches, PREG_SET_ORDER);
        $rules = [];
        foreach ($matches as $match) {
            if (in_array($selector, array_map('trim', explode(',', trim($match[1]))), true)) {
                $rules[] = trim($match[2]);
            }
        }
        return $rules;
    }

    /**
     * Custom colours and keyboard focus reach specialised editor controls.
     */
    public function test_editor_surfaces(): void {
        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        $css = $this->compile_css(true);
        foreach (
            [
            '.reportbuilder-toggle-card .card-body' => 'border-top: 1px solid #345678;',
            '.reportbuilder-sidebar-settings .list-group' => '--bs-list-group-bg: #fffdf5;',
            '.reportbuilder-sidebar-menu .list-group' => '--bs-list-group-color: #234567;',
            '.reportbuilder-sidebar-menu > .input-group > label[for="reportbuilder-sidebar-search"]' =>
                'position: absolute;',
            '.reportbuilder-sidebar-menu > .input-group > .input-group-text.bg-white' =>
                'background-color: #fffdf5 !important;',
            '.reportbuilder-sidebar-menu a[data-region="sidebar-item"]:focus-visible' =>
                'outline: 3px solid #1746a2;',
            '.reportbuilder-report table.reportbuilder-table tr' => 'border-radius: 0;',
            ] as $selector => $declaration
        ) {
            $rules = $this->rules($css, $selector);
            $this->assertNotEmpty($rules, $selector);
            $this->assertStringContainsString($declaration, implode("\n", $rules), $selector);
        }
    }

    /**
     * Drag feedback, collapsible icons, scrolling and card-view visibility remain core-owned.
     */
    public function test_editor_behaviour_preserved(): void {
        $this->resetAfterTest();
        $baseline = $this->compile_css(false);
        $css = $this->compile_css(true);
        foreach (
            [
            '.reportbuilder-sidebar-menu-cards',
            '.reportbuilder-toggle-card .toggle-card-button.collapsed .collapsed-icon-container',
            '.reportbuilder-sortable-list li.sortable-list-current-position',
            '.reportbuilder-sortable-list li.sortable-list-is-dragged',
            '.reportbuilder-wrapper .filters-dropdown',
            ] as $selector
        ) {
            $rules = $this->rules($baseline, $selector);
            $this->assertNotEmpty($rules, $selector);
            $this->assertSame($rules, $this->rules($css, $selector), $selector);
        }
        $this->assertStringContainsString('td[data-cardviewhidden]', $css);
        $this->assertStringContainsString('@container', $css);
    }
}
