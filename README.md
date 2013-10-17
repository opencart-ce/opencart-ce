opencart-ce
===========

OpenCart Community Edition is an unofficial fork dedicated to backporting all the latest OpenCart bug fixes so users can maintain a stable and secure store without having to wait extended periods between major releases or upgrade to an OpenCart version which may be incompatible with their extensions or themes.

## Roadmap
OpenCart Community Edition has published its first release candidate.  Release candidates are the final step before a full release.  They're tested stable but may have unknown issues that are found in wider testing.

This release candidate is best suited for developers, advanced users, or anyone wanting to test Community Edition.  An official release will follow a period of user testing and feedback.

The repo is set to initially include OpenCart 1.5.5.x and 1.5.4.x with possible expansion to earlier 1.5.x versions at a later date.  To reduce file size and help code alignment in GitHub, inconsistencies in whitespace (a space mixed in with the tabs and vice versa) will be fixed on the initial push.  Please make sure you're using this whitespace standardized version when submitting a pull request and not an OpenCart default version.

## Contribution Guidelines

 1. The OpenCart Community Edition GitHub repository is for submitting bug reports and bug fixes.  If you need assistance troubleshooting an issue with OpenCart please use the official community support forums at [forum.opencart.com](http://forum.opencart.com).
 2. When submitting a bug report please include your OpenCart version, relevant environment info, and a step-by-step process on how to create the error or the relevant line of code which causes it.  If you are unsure if an issue is a bug please post in the community support forum for assistance.
 3. When submitting a pull request leave a description of the changes so the repository maintainers can more efficiently review the code.  A pull request should only cover one issue, preferably with a single commit.
 4. Follow the existing syntax and styling used in OpenCart for all pull requests.  This helps keep code clean and readable.  You may be asked to resubmit a pull request if standard styling is not followed.  More info on OpenCart styling can be found on the [unofficial OpenCart wiki](http://wiki.opencarthelp.com/doku.php?id=style_guide).
 5. Attempt to keep changes as minimal as possible.  Significant changes to code risk causing compatibility issues with third-party mods.
 6. Significant code changes which may impact third-party mods or themes should be discussed before inclusion.
 7. If a bug is fixed in the official OpenCart repository with minimal changes that method is preferred in Community Edition.
 8. When backporting an official OpenCart bugfix include a link to their commit in the description for reference.
 9. If applicable submit a bug report or fix to the [opencart/opencart core](https://github.com/opencart/opencart).
 10. Only submit code that you have the rights to or that is released under GPL.  Code from commercial mods or mods not explicitly released under GPL is prohibited.
 11. Pull requests and bug reports that are closed without action will include a statement why.  Feel free to advocate for re-opening the issue but please note the decision may not be reversed.  Remember, closing an issue is not meant as a personal attack against you.  It's about what's best for the project.
