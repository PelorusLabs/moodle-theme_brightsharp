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
 * Specialised workflow styling regressions.
 *
 * @package    theme_brightsharp
 * @copyright  Pelorus Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_pre_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_extra_scss')]
final class workflows_test extends \advanced_testcase {
    /**
     * Find all declaration blocks for an exact selector.
     *
     * @param string $css Compiled CSS.
     * @param string $selector Selector.
     * @return array
     */
    private function rules(string $css, string $selector): array {
        $css = preg_replace('~/\*.*?\*/~s', '', $css);
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $matches, PREG_SET_ORDER);
        $rules = [];
        foreach ($matches as $match) {
            if (in_array($selector, array_map('trim', explode(',', trim($match[1]))), true)) {
                $rules[] = $match[2];
            }
        }
        return $rules;
    }

    /**
     * Compile integrated CSS and check each workflow's targeted declarations.
     */
    public function test_workflow_styles(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        set_config('shadowsizelarge', 9, 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $css = $compiler->to_css();
        foreach (
            [
            '.path-mod-assign [data-region="grade-panel"] .popout' =>
                'box-shadow: 9px 9px 0 #345678;',
            'table.question-bank-table' => 'border: 3px solid #345678;',
            '.block_myoverview .progress' => 'outline: 1px solid #345678;',
            '#page-user-profile .node_category' => 'background-color: #fffdf5;',
            '.pagelayout-login .login-container' => 'box-shadow: 9px 9px 0 #345678;',
            '.path-mod-book .book_toc a:focus-visible' => 'outline: 3px solid #1746a2;',
            '.path-mod-lesson .slideshow' => 'border: 2px solid #345678;',
            '.path-mod-workshop .submission-full' => 'border: 2px solid #345678;',
            '.path-mod-data .defaulttemplate-listentry' => 'border: 2px solid #345678;',
            '.path-mod-glossary .glossarypost:not(.continuous)' => 'border: 2px solid #345678;',
            '.path-mod-h5pactivity iframe.h5p-player' => 'border-radius: 0;',
            '#course-category-listings #category-listing > .category-listing' => 'border: 3px solid #345678;',
            '#course-category-listings #course-listing > .course-listing' => 'background-color: #fffdf5;',
            '#course-category-listings #course-detail > .course-detail' => 'border-radius: 0;',
            '#course-category-listings #course-listing > .course-listing:not(.card)' => 'padding: 1rem;',
            '#course-category-listings .list-group' => '--bs-list-group-border-color: #345678;',
            '#course-category-listings .listitem.list-group-item' => 'box-shadow: none;',
            '#course-category-listings .listitem.list-group-item[data-selected="1"]:not(.highlight)' =>
                'background-color: #fff7da;',
            '#course-category-listings .bulk-actions' => 'border-top: 2px solid #345678;',
            '#course-category-listings .bulk-actions .pair-value > div' => 'flex-wrap: wrap;',
            '#course-category-listings .pagination' => 'row-gap: 0.25rem;',
            '#course-category-listings .listitem a:focus-visible' => 'outline-offset: -3px;',
            '.path-backup .mform .grouped_settings.section_level' => 'border: 3px solid #345678;',
            '.path-backup .mform .grouped_settings.subsection_level' => 'background-color: #fffdf5;',
            '.path-backup .backup-section' => 'border-radius: 0;',
            '.path-backup .backup_progress' => 'padding: 1rem;',
            '.path-backup .backup_progress .backup_stage_current' => 'border-bottom: 3px solid #ffd23f;',
            '.path-backup .backup_progress a.backup_stage:focus-visible' => 'outline: 3px solid #1746a2;',
            '.path-backup .backup-section > .header' => 'padding-block-end: 0.5rem;',
            '.path-backup .mform .grouped_settings.activity_level:nth-of-type(odd)' => 'background-color: #f5f5ef;',
            '.path-backup .mform .grouped_settings.activity_level:nth-of-type(even)' => 'background-color: #fffdf5;',
            '.path-backup .detail-pair-value' => 'overflow-wrap: anywhere;',
            '.path-backup .backup_log_contents' => 'overflow-x: auto;',
            '.path-backup .wibbler' => 'max-inline-size: 100%;',
            '.path-backup .progress' => 'max-inline-size: 100%;',
            ] as $selector => $declaration
        ) {
            $rules = $this->rules($css, $selector);
            $this->assertNotEmpty($rules, $selector);
            $this->assertStringContainsString($declaration, implode("\n", $rules), $selector);
        }
        $loginrules = $this->rules($css, '.pagelayout-login .login-container');
        $this->assertStringContainsString('padding: 1rem;', implode("\n", $loginrules));
        $this->assertStringContainsString('padding: 2rem;', implode("\n", $loginrules));
        foreach (
            [
            '.login-form-username > .form-control',
            '.login-form-password > .form-control',
            '.login-form-password > .toggle-sensitive-wrapper',
            ] as $input
        ) {
            $rules = $this->rules($css, '.pagelayout-login .login-container .login-form ' . $input);
            $this->assertNotEmpty($rules);
            $this->assertStringContainsString('width: 100%;', implode("\n", $rules));
            $this->assertStringContainsString('max-width: 100%;', implode("\n", $rules));
        }
        foreach (
            [
            'grading', 'questions', 'dashboard', 'users', 'login', 'activityextras', 'coursemanagement', 'backuprestore', 'editors',
            ] as $module
        ) {
            $scss = file_get_contents($CFG->dirroot . '/theme/brightsharp/scss/' . $module . '.scss');
            $this->assertStringContainsString($scss, $theme->get_extra_scss_code());
            foreach (['display:', 'position:', 'height:', 'width:', 'opacity:', 'transform:'] as $property) {
                if ($module === 'login' && $property === 'width:') {
                    continue;
                }
                $this->assertDoesNotMatchRegularExpression('/(?:^|[;{}])\s*' . preg_quote($property, '/') . '/', $scss, $module);
            }
        }
    }

    /**
     * Specialised overrides leave core progress, correctness and hidden-state rules unchanged.
     */
    public function test_semantic_states_preserved(): void {
        global $CFG;

        $this->resetAfterTest();
        $theme = \theme_config::load('brightsharp');
        $compiled = [];
        foreach ([false, true] as $overrides) {
            $compiler = new \core_scss();
            $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
            $compiler->prepend_raw_scss($theme->get_pre_scss_code());
            $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
            if ($overrides) {
                $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/brightsharp/scss/tokens.scss'));
                foreach (
                    [
                    'grading', 'questions', 'dashboard', 'users', 'login', 'activityextras', 'coursemanagement', 'backuprestore',
                    'editors',
                    ] as $module
                ) {
                    $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/brightsharp/scss/' . $module . '.scss'));
                }
            }
            $compiled[] = $compiler->to_css();
        }
        foreach (
            [
            '.progress-bar', '.hidden', '.que .formulation', '.que .outcome', '.userpicture',
            '#course-category-listings .listitem[data-selected="1"]',
            '#course-category-listings .listitem[data-visible="0"]',
            '#course-category-listings .listitem[data-visible="0"] > div .item-actions .action-show',
            '#course-category-listings .listitem[data-visible="0"] > div .item-actions .action-hide',
            '#course-category-listings #category-listing .listitem.collapsed > ul.ml',
            '#course-category-listings #course-listing .listitem .drag-handle',
            '.jsenabled #course-category-listings #course-listing .listitem .drag-handle',
            '#course-category-listings .listitem.highlight > div',
            '.course-being-dragged',
            '.path-backup .notification.dependencies_enforced',
            '.path-backup .backup_progress .backup_stage',
            '.path-backup .backup_progress .backup_stage.backup_stage_current',
            '.path-backup .backup_progress span.backup_stage.backup_stage_complete',
            '.path-backup .mform .include_setting',
            '.path-backup .mform .normal_setting',
            '.path-backup .wibbler .wibble',
            '.path-backup .wibbler .state0',
            '.path-backup .wibbler .state12',
            '#page-backup-restore .filealiasesfailures',
            ] as $selector
        ) {
            $baseline = $this->rules($compiled[0], $selector);
            $this->assertNotEmpty($baseline, $selector);
            $this->assertSame($baseline, $this->rules($compiled[1], $selector), $selector);
        }
    }
}
