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
 * Tests for activity chooser, assignment submission, and quiz attempt styles.
 *
 * @package    theme_brightsharp
 * @copyright  Pelorus Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_pre_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_extra_scss')]
final class activities_test extends \advanced_testcase {
    /**
     * Activity-specific styles compile and leave workflow colours to Moodle.
     */
    public function test_activity_styles_compile_and_preserve_states(): void {
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

        $this->assertStringContainsString('.modchoosercontent .optionscontainer .option', $css);
        $this->assertStringContainsString('border: 2px solid #345678;', $css);
        $this->assertStringContainsString('background-color: #fffdf5;', $css);
        $this->assertStringContainsString('.path-mod-assign .submissionstatusdraft', $css);
        $this->assertStringContainsString('.path-mod-assign .submissionstatussubmitted', $css);
        $this->assertStringContainsString('#page-mod-quiz-attempt .qnbutton', $css);
        $this->assertStringContainsString('#page-mod-quiz-attempt .que .correctness', $css);
        $this->assertStringContainsString('outline: 3px solid #1746a2;', $css);
        $this->assertStringContainsString('@media (forced-colors: active)', $css);

        preg_match(
            '/\.path-mod-assign \.submissionstatus,[^{]*\{([^}]+)\}/s',
            $css,
            $submissionstatus
        );
        $this->assertNotEmpty($submissionstatus);
        $this->assertStringNotContainsString('background-color:', $submissionstatus[1]);
        $this->assertStringNotContainsString('color:', $submissionstatus[1]);

        preg_match('/#page-mod-quiz-attempt \.qnbutton\s*\{([^}]+)\}/', $css, $navigation);
        $this->assertNotEmpty($navigation);
        $this->assertStringNotContainsString('background-color:', $navigation[1]);

        preg_match(
            '/#page-mod-quiz-attempt \.que \.correctness\s*\{([^}]+)\}/',
            $css,
            $correctness
        );
        $this->assertNotEmpty($correctness);
        $this->assertStringNotContainsString('background-color:', $correctness[1]);
        $this->assertStringNotContainsString('color:', $correctness[1]);
    }
}
