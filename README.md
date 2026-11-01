# BrightSharp

BrightSharp is a Boost-based Moodle theme aiming for a **[neubrutalist](https://neubrutalism.com) design language**.

The goal is a bold, bright, sharply defined interface that remains practical for
learning.

## Supported versions

BrightSharp supports **Moodle 5.2 and later** and inherits from Moodle's Boost theme.

## Installation

1. Download BrightSharp from the [GitHub repository](https://github.com/PelorusLabs/moodle-theme_brightsharp).
2. Extract the theme into your Moodle installation's `theme/brightsharp` directory
   (`public/theme/brightsharp` in Moodle 5.2 and later). The directory should contain
   the theme's `version.php` file, not another nested directory.
3. Sign in as a site administrator and visit **Site administration > Notifications**
   to complete installation.
4. Go to **Site administration > Appearance > Themes > Theme selector** and select
   **BrightSharp**.
5. Configure the theme under **Site administration > Appearance > Themes > BrightSharp**.

## Third-party fonts

BrightSharp bundles and self-hosts the following fonts:

- [Inter](https://github.com/rsms/inter), version 4.001 (git-9221beed3),
  for body text and controls. Copyright 2016 The Inter Project Authors.
  See [the bundled copyright notice and licence](fonts/inter-OFL.txt).
- [Space Grotesk](https://github.com/floriankarsten/space-grotesk), version 2.000,
  for headings. Copyright 2020 The Space Grotesk Project Authors.
  See [the bundled copyright notice and licence](fonts/space-grotesk-OFL.txt).

Both fonts are distributed under the **SIL Open Font License 1.1** and retain
that licence independently of the theme's GPL licence. Their metadata is
recorded in [thirdpartylibs.xml](thirdpartylibs.xml).

## Support and issue reporting

For support, bug reports, and feature requests, use the
[GitHub issue tracker](https://github.com/PelorusLabs/moodle-theme_brightsharp/issues).
Check existing issues before opening a new one.

When reporting a problem, include your Moodle and BrightSharp versions, browser
and device details, steps to reproduce, and the expected and actual behaviour.
Include screenshots or relevant error messages where helpful, but remove passwords,
tokens, personal information, and other sensitive data.
