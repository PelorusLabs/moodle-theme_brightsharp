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
 * Compiled calendar and messaging drawer regression tests.
 *
 * @package    theme_brightsharp
 * @copyright  Pelorus Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_pre_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_extra_scss')]
final class calendar_messaging_test extends \advanced_testcase {
    /**
     * Compile the full theme or just these components with Bootstrap definitions.
     *
     * @param bool $componentonly Whether to isolate the component overrides.
     * @param bool $includetheme Whether to include Brightsharp overrides.
     * @param array $components Components to include when isolated.
     * @return string Compiled CSS.
     */
    private function compile_css(
        bool $componentonly = false,
        bool $includetheme = true,
        array $components = ['calendar', 'messaging']
    ): string {
        global $CFG;

        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        if ($componentonly) {
            $compiler->append_raw_scss(
                '@import "bootstrap/functions"; @import "bootstrap/variables"; @import "bootstrap/mixins";'
            );
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/brightsharp/scss/tokens.scss'));
        } else {
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            if ($includetheme) {
                $compiler->append_raw_scss($theme->get_extra_scss_code());
            }
        }
        if ($includetheme) {
            foreach ($components as $component) {
                $scss = file_get_contents($CFG->dirroot . '/theme/brightsharp/scss/' . $component . '.scss');
                // Exercise the new components even before the theme callback has been wired.
                if ($componentonly || !str_contains($theme->get_extra_scss_code(), $scss)) {
                    $compiler->append_raw_scss($scss);
                }
            }
        }
        return $compiler->to_css();
    }

    /**
     * Extract declaration blocks for an exact selector, including media-query rules.
     *
     * @param string $css Compiled CSS.
     * @param string $selector Selector to find.
     * @return array Declaration blocks in source order.
     */
    private function rules(string $css, string $selector): array {
        $css = preg_replace('~/\*.*?\*/~s', '', $css);
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $matches, PREG_SET_ORDER);
        $rules = [];
        foreach ($matches as $match) {
            $selectors = array_map('trim', explode(',', trim($match[1])));
            if (in_array($selector, $selectors, true)) {
                $rules[] = trim($match[2]);
            }
        }
        return $rules;
    }

    /**
     * Assert a declaration occurs in a rule for the exact selector.
     *
     * @param string $css Compiled CSS.
     * @param string $selector Selector to find.
     * @param string $declaration Expected declaration.
     */
    private function assert_declaration(string $css, string $selector, string $declaration): void {
        $rules = $this->rules($css, $selector);
        $this->assertNotEmpty($rules, 'Missing selector: ' . $selector);
        $this->assertStringContainsString($declaration, implode("\n", $rules), $selector);
    }

    /**
     * Custom neutral surfaces and square markers compile in the complete theme.
     */
    public function test_calendar_surfaces_and_focus(): void {
        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        $css = $this->compile_css();

        foreach (['.maincalendar .calendarmonth', '.block .minicalendar'] as $selector) {
            $this->assert_declaration($css, $selector, 'background-color: #fffdf5;');
            $this->assert_declaration($css, $selector, 'color: #234567;');
            $this->assert_declaration($css, $selector, 'border: 3px solid #345678;');
            $this->assert_declaration($css, $selector, 'box-shadow: none;');
        }
        $this->assert_declaration($css, '.maincalendar .calendarmonth td.today .day-number-circle', 'border-radius: 0;');
        $this->assert_declaration(
            $css,
            '.maincalendar .calendarmonth td.today .day-number-circle',
            'background-color: #ffd23f;'
        );
        $this->assert_declaration($css, '.maincalendar .calendarmonth td.today .day-number-circle', 'color: #000000;');
        $this->assert_declaration($css, '.block .minicalendar td.today', 'outline: 2px solid #345678;');
        $this->assert_declaration($css, '.maincalendar .event > .card', 'border-radius: 0 !important;');
        $this->assert_declaration($css, '.maincalendar .event > .card > .card-header', 'border-color: #345678;');
        $this->assert_declaration($css, '.block_calendar_upcoming [data-region="event-item"]', 'box-shadow: none;');
        foreach (
            [
            '.maincalendar .calendarmonth td a.day:focus',
            '.maincalendar a:not(.btn):focus-visible',
            '.block_calendar_month a:not(.btn):focus-visible',
            '.block_calendar_upcoming a:not(.btn):focus-visible',
            ] as $selector
        ) {
            $this->assert_declaration($css, $selector, 'outline: 3px solid #1746a2;');
            $this->assert_declaration($css, $selector, 'outline-offset: -3px;');
            $this->assert_declaration($css, $selector, 'outline-color: Highlight;');
        }
    }

    /**
     * Calendar categories, duration colours, overdue labels and responsive behaviour survive.
     */
    public function test_calendar_semantics_and_responsiveness(): void {
        $this->resetAfterTest();
        $baseline = $this->compile_css(false, false);
        $css = $this->compile_css();
        foreach (['category', 'course', 'site', 'group', 'user', 'other'] as $type) {
            foreach (
                [
                '.calendar_event_' . $type,
                '.maincalendar .calendarmonth ul li .calendar-circle.calendar_event_' . $type,
                ] as $selector
            ) {
                $this->assertNotEmpty($this->rules($baseline, $selector), $selector);
                $this->assertSame($this->rules($baseline, $selector), $this->rules($css, $selector), $selector);
            }
            $daycell = '#page-calendar-view #region-main .maincalendar .calendarwrapper .calendarmonth td > div';
            $this->assertSame(1, preg_match(
                '/@media\s*\(min-width:\s*769px\)\s*\{\s*(' . preg_quote($daycell, '/') . '\s*\{[^}]*\})/',
                $css,
                $compactrules
            ));
            $this->assert_declaration($compactrules[1], $daycell, 'height: 7em;');
            $this->assert_declaration($compactrules[1], $daycell, 'overflow-y: auto;');
        }
        foreach (['global', 'category', 'course', 'group', 'user', 'other'] as $type) {
            $selector = '.block .minicalendar td.duration_' . $type . '.duration_finish';
            $this->assertNotEmpty($this->rules($baseline, $selector), $selector);
            $this->assertSame($this->rules($baseline, $selector), $this->rules($css, $selector), $selector);
        }
        foreach (
            [
            '.text-danger',
            '.maincalendar .calendartable.calendarmonth td.hasevent [data-region="day-content"]',
            '.maincalendar .calendarmonth ul li[data-event-folded="true"]',
            '#region-main .maincalendar .calendarwrapper td > div',
            ] as $selector
        ) {
            $this->assertNotEmpty($this->rules($baseline, $selector), $selector);
            $this->assertSame($this->rules($baseline, $selector), $this->rules($css, $selector), $selector);
        }
    }

    /**
     * Flat bubbles retain direction distinction, selection and keyboard focus.
     */
    public function test_messaging_surfaces_and_states(): void {
        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('textcolor', '#234567', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        $css = $this->compile_css();
        $drawer = '.message-app[data-region="message-drawer"]';
        $this->assert_declaration($css, $drawer, 'background-color: #fffdf5;');
        $this->assert_declaration($css, $drawer . ' .message', 'border-radius: 0 !important;');
        $this->assert_declaration($css, $drawer . ' .message', 'box-shadow: none;');
        $this->assert_declaration($css, $drawer . ' .message.received', 'background-color: #fffdf5;');
        foreach (['send', 'received'] as $direction) {
            $this->assert_declaration($css, $drawer . ' .message.' . $direction, 'color: #234567;');
        }
        $this->assertNotSame(
            $this->rules($css, $drawer . ' .message.send'),
            $this->rules($css, $drawer . ' .message.received')
        );
        $this->assert_declaration($css, $drawer . ' .message .tail', 'display: none;');
        $this->assert_declaration($css, $drawer . ' .clickable:hover', 'filter: none;');
        $this->assert_declaration($css, $drawer . ' .list-group .list-group-item.active', 'background-color: #ffd23f;');
        $this->assert_declaration($css, $drawer . ' .list-group .list-group-item.active', 'color: #000000;');
        $this->assert_declaration($css, $drawer . ' .message[aria-checked="true"]', 'outline: 2px solid #ffd23f;');
        $this->assert_declaration($css, $drawer . ' .message[aria-checked="true"]', 'border-color: Highlight;');
        foreach (['a', 'button', '.message', '.overview-section-toggle'] as $control) {
            $selector = $drawer . ' ' . $control . ':focus-visible';
            $this->assert_declaration($css, $selector, 'outline: 3px solid #1746a2;');
            $this->assert_declaration($css, $selector, 'outline-offset: -3px;');
            $this->assert_declaration($css, $selector, 'outline-color: Highlight;');
        }
    }

    /**
     * Drawer geometry, unread/online indicators and JS visibility remain Boost-owned.
     */
    public function test_messaging_semantics_and_scope(): void {
        $this->resetAfterTest();
        $baseline = $this->compile_css(false, false);
        $css = $this->compile_css();
        foreach (
            [
            '[data-region=right-hand-drawer].drawer',
            '[data-region=right-hand-drawer].hidden',
            '.message-app .contact-status.online .icon',
            '.message-app .body-container > *',
            '.message-app .overview-section-toggle.collapsed .collapsed-icon-container',
            '.message-app .emoji-auto-complete-container.hidden',
            '.message-app .message.send .tail',
            '.message-app .message.received .tail',
            '.bg-primary',
            '.text-white',
            '.hidden',
            ] as $selector
        ) {
            $this->assertNotEmpty($this->rules($baseline, $selector), $selector);
            $this->assertSame($this->rules($baseline, $selector), $this->rules($css, $selector), $selector);
        }
        $componentcss = $this->compile_css(true, true, ['messaging']);
        $this->assertStringNotContainsString('.message-app.main', $componentcss);
        $this->assertStringNotContainsString('.contact-status', $componentcss);
        $this->assertStringNotContainsString('unread-count', $componentcss);
        $this->assertStringNotContainsString('.hidden', $componentcss);
        $this->assertStringNotContainsString('width:', $componentcss);
        $this->assertStringNotContainsString('height:', $componentcss);
        $this->assertStringNotContainsString('direction:', $componentcss);
        $this->assertStringNotContainsString('transform:', $componentcss);
        $this->assertStringContainsString('@media (forced-colors: active)', $componentcss);
        $this->assert_declaration(
            $componentcss,
            '[data-region="right-hand-drawer"]:has(> [data-region="message-drawer"])',
            'border-inline-start: 3px solid #171717;'
        );
    }
}
