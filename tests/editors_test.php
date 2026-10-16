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
 * Editor chrome styling regressions.
 *
 * @package    theme_brightsharp
 * @copyright  Pelorus Labs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_pre_scss')]
#[\PHPUnit\Framework\Attributes\CoversFunction('theme_brightsharp_get_extra_scss')]
final class editors_test extends \advanced_testcase {
    /**
     * Find declaration blocks for an exact selector, including inside media queries.
     *
     * @param string $css CSS.
     * @param string $selector Selector.
     * @param bool|null $forcedcolours Null includes every media context; otherwise selects the colour mode.
     * @return array
     */
    private function rules(string $css, string $selector, ?bool $forcedcolours = null): array {
        $css = preg_replace('~/\*.*?\*/~s', '', $css);
        if ($forcedcolours !== null) {
            $css = $this->active_css($css, $forcedcolours);
        }
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
     * Select unconditional and forced-colour rules, excluding viewport-specific branches.
     *
     * @param string $css CSS.
     * @param bool $forcedcolours Whether forced colours are active.
     * @return string
     */
    private function active_css(string $css, bool $forcedcolours): string {
        $result = '';
        $offset = 0;
        while (($open = strpos($css, '{', $offset)) !== false) {
            $header = trim(substr($css, $offset, $open - $offset));
            $depth = 1;
            $end = $open + 1;
            for (; $depth > 0 && $end < strlen($css); $end++) {
                if ($css[$end] === '{') {
                    $depth++;
                } else if ($css[$end] === '}') {
                    $depth--;
                }
            }
            $body = substr($css, $open + 1, $end - $open - 2);
            if (str_starts_with($header, '@media')) {
                if ($forcedcolours && $header === '@media (forced-colors: active)') {
                    $result .= $this->active_css($body, $forcedcolours);
                }
            } else if (!str_starts_with($header, '@')) {
                $result .= $header . '{' . $body . '}';
            }
            $offset = $end;
        }
        return $result;
    }

    /**
     * Resolve a property among the selectors matching a particular test fixture.
     *
     * @param string $css CSS from both stylesheets.
     * @param array $selectors Applicable selectors, including ancestors and interaction states.
     * @param string $property Property to resolve.
     * @param bool $forcedcolours Whether forced colours are active.
     * @return string|null Winning value.
     */
    private function cascaded_value(string $css, array $selectors, string $property, bool $forcedcolours): ?string {
        $css = $this->active_css(preg_replace('~/\*.*?\*/~s', '', $css), $forcedcolours);
        preg_match_all('/([^{}]+)\{([^{}]*)\}/', $css, $blocks, PREG_SET_ORDER);
        $parser = new \Symfony\Component\CssSelector\Parser\Parser();
        $winner = null;
        $priority = [-1, -1];
        foreach ($blocks as $block) {
            foreach (array_map('trim', explode(',', $block[1])) as $selector) {
                if (!in_array($selector, $selectors, true)) {
                    continue;
                }
                $specificity = $parser->parse($selector)[0]->getSpecificity()->getValue();
                foreach (explode(';', $block[2]) as $declaration) {
                    $pair = explode(':', $declaration, 2);
                    if (count($pair) !== 2 || trim($pair[0]) !== $property) {
                        continue;
                    }
                    $important = str_contains($pair[1], '!important') ? 1 : 0;
                    if ([$important, $specificity] >= $priority) {
                        $priority = [$important, $specificity];
                        $winner = trim(str_replace('!important', '', $pair[1]));
                    }
                }
            }
        }
        return $winner;
    }

    /**
     * Real skin declarations cannot replace palette or shadow rules in either load order.
     */
    public function test_skin_cascade(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#202020', 'theme_brightsharp');
        set_config('textcolor', '#f5f5f5', 'theme_brightsharp');
        $theme = \theme_config::load('brightsharp');
        $compiler = new \core_scss();
        $compiler->setImportPaths([$CFG->dirroot . '/theme/boost/scss']);
        $compiler->prepend_raw_scss($theme->get_pre_scss_code());
        $compiler->append_raw_scss(file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss'));
        $compiler->append_raw_scss($theme->get_extra_scss_code());
        $themecss = $compiler->to_css();
        $skin = file_get_contents($CFG->dirroot . '/lib/editor/tiny/js/tinymce/skins/ui/oxide/skin.css');
        $cases = [
            [
                [
                    '.tox:not(.tox-tinymce-inline) .tox-editor-header',
                    '.tox:not(.tox-tinymce-inline).tox-tinymce--toolbar-bottom .tox-editor-header',
                    '.tox:not(.tox-tinymce-inline).tox-tinymce--toolbar-sticky-on .tox-editor-header',
                    '.tox:not(.tox-tinymce-inline).tox-tinymce--toolbar-sticky-on.tox-tinymce--toolbar-bottom .tox-editor-header',
                    '.tox.tox.tox-tinymce .tox-editor-header:not(.tox-editor-header--empty)',
                    '.tox.tox.tox-tinymce.tox-tinymce--toolbar-sticky-on .tox-editor-header:not(.tox-editor-header--empty)',
                    '.tox.tox.tox-tinymce.tox-tinymce--toolbar-bottom .tox-editor-header:not(.tox-editor-header--empty)',
                ],
                'box-shadow', 'none', 'none',
            ],
            [
                ['.tox .tox-label', '.tox.tox .tox-label'],
                'color', '#f5f5f5', '#f5f5f5',
            ],
            [
                ['.tox .tox-toolbar-label', '.tox.tox .tox-toolbar-label'],
                'color', '#f5f5f5', '#f5f5f5',
            ],
            [
                ['.tox .tox-menu', '.tox.tox .tox-menu'],
                'box-shadow', '4px 4px 0 #171717', 'none',
            ],
            [
                ['.tox .tox-tbtn:focus', '.tox.tox .tox-tbtn:focus'],
                'outline-color', null, 'Highlight',
            ],
        ];
        foreach (['a', '.tox-statusbar__path-item', '.tox-statusbar__wordcount'] as $control) {
            $skincontrol = $control === 'a' ? '.tox-statusbar a' : $control;
            foreach (['', ':hover', ':focus'] as $state) {
                $selectors = [
                    '.tox ' . $skincontrol,
                    '.tox.tox .tox-statusbar ' . $control
                        . ':not([aria-disabled="true"]):not(:disabled)',
                ];
                if ($state !== '') {
                    $selectors[] = '.tox ' . $skincontrol . $state . ':not(:disabled):not([aria-disabled=true])';
                    $selectors[] = '.tox.tox .tox-statusbar ' . $control
                        . ':not([aria-disabled="true"]):not(:disabled)' . $state;
                }
                $cases[] = [$selectors, 'color', '#f5f5f5', '#f5f5f5'];
            }
        }
        foreach ([$skin . $themecss, $themecss . $skin] as $css) {
            foreach ($cases as [$selectors, $property, $normal, $forced]) {
                $this->assertSame($normal, $this->cascaded_value($css, $selectors, $property, false), implode(', ', $selectors));
                $this->assertSame($forced, $this->cascaded_value($css, $selectors, $property, true), implode(', ', $selectors));
            }
            foreach (
                [
                    '.tox .tox-form__group--error',
                    '.tox .tox-notification--error',
                    '.tox .tox-notification--warning',
                ] as $selector
            ) {
                $this->assertNotEmpty($this->rules($skin, $selector, false));
                $this->assertSame($this->rules($skin, $selector, false), $this->rules($css, $selector, false));
            }
        }
    }

    /**
     * Compile the theme and check editor palette, elevation and focus states.
     */
    public function test_editor_chrome(): void {
        global $CFG;

        $this->resetAfterTest();
        set_config('surfacecolor', '#fffdf5', 'theme_brightsharp');
        set_config('outlinecolor', '#345678', 'theme_brightsharp');
        set_config('brandcolor', '#ffd23f', 'theme_brightsharp');
        set_config('primarybuttoncolor', '#ff90e8', 'theme_brightsharp');
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
            '.tox.tox.tox-tinymce:not(.tox-tinymce-inline)' => ['border: 3px solid #345678;', 'border-radius: 0;'],
            '.tox.tox.tox-tinymce .tox-editor-header:not(.tox-editor-header--empty)' =>
                ['background-color: #fffdf5;', 'box-shadow: none;'],
            '.tox.tox.tox-tinymce.tox-tinymce--toolbar-bottom .tox-editor-header:not(.tox-editor-header--empty)' =>
                ['border-top: 2px solid #345678;', 'border-bottom: 0;'],
            '.tox.tox .tox-statusbar' => ['border-top-color: #345678;'],
            '.tox.tox .tox-tbtn:not(.tox-tbtn--disabled):not(:disabled).tox-tbtn--enabled' =>
                ['background-color: #ffd23f;', 'color: #000000;'],
            '.tox.tox .tox-tbtn:focus' => ['outline: 3px solid #1746a2;', 'outline-offset: -3px;'],
            '.tox.tox .tox-menu' => ['border: 3px solid #345678;', 'box-shadow: 9px 9px 0 #345678;'],
            '.tox.tox .tox-pop__dialog' => ['border-radius: 0;', 'box-shadow: 9px 9px 0 #345678;'],
            '.tox.tox .tox-dialog' => ['border: 3px solid #345678;', 'box-shadow: 9px 9px 0 #345678;'],
            '.tox.tox .tox-dialog__header' => ['padding-bottom: 0.5rem;'],
            '.tox.tox .tox-dialog__footer' => ['border-top: 2px solid #345678;'],
            '.tox.tox .tox-dialog__body-nav-item--active' => ['background-color: #ffd23f;'],
            '.tox.tox .tox-textfield:not(.tox-invalid)' => ['border: 2px solid #345678;', 'border-radius: 0;'],
            '.tox.tox .tox-textfield:not(.tox-invalid):focus' => ['outline: 3px solid #1746a2;'],
            '.tox.tox .tox-button:not(.tox-button--naked):not(.tox-button--icon)' => ['background-color: #ff90e8;'],
            '.tox.tox .tox-button:not(.tox-button--naked):not(.tox-button--icon).tox-button--secondary' =>
                ['background-color: #fffdf5;'],
            'div.editor_atto_toolbar' => ['border: 3px solid #345678;', 'border-radius: 0;'],
            'div.editor_atto_toolbar div.atto_group' => ['border-color: #345678;', 'border-radius: 0;'],
            'div.editor_atto_toolbar button.highlight:not(:disabled)' => ['background-image: none;', 'background-color: #ffd23f;'],
            'div.editor_atto_toolbar button:disabled' => ['background-image: none;'],
            ] as $selector => $declarations
        ) {
            $rules = $this->rules($css, $selector, false);
            $this->assertNotEmpty($rules, $selector);
            foreach ($declarations as $declaration) {
                $this->assertStringContainsString($declaration, implode("\n", $rules), $selector);
            }
            foreach (
                [
                '.tox.tox .tox-tbtn:focus' => 'outline-color: Highlight;',
                '.tox.tox .tox-textfield:not(.tox-invalid):focus' => 'outline-color: Highlight;',
                '.tox.tox .tox-menu' => 'box-shadow: none;',
                '.tox.tox .tox-dialog__body-nav-item--active' => 'border-bottom-color: Highlight;',
                ] as $selector => $declaration
            ) {
                $this->assertStringNotContainsString($declaration, implode("\n", $this->rules($css, $selector, false)));
                $this->assertStringContainsString($declaration, implode("\n", $this->rules($css, $selector, true)));
            }
        }
        $source = file_get_contents($CFG->dirroot . '/theme/brightsharp/scss/editors.scss');
        $this->assertStringContainsString($source, $theme->get_extra_scss_code());
        foreach (['display', 'position', 'height', 'width', 'opacity', 'transform', 'z-index'] as $property) {
            $this->assertDoesNotMatchRegularExpression('/(?:^|[;{}])\s*' . $property . '\s*:/', $source);
        }
        $this->assertStringNotContainsString('.editor_atto_content', $source);
        $this->assertStringNotContainsString('.mce-content-body', $source);
        $this->assertStringNotContainsString('.tox-edit-area', $source);
    }
}
