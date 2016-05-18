# Discover

## Violation of the DRY principle

Check the history of
[AmenitiesBuilder](AmenitiesBuilder.php)
and find out why
\CleanCode\DRY\AmenitiesBuilder::versionThreeMerged is broken.

- [ede0066](https://github.com/tobias-reuter-rocket/clean-code-session/commit/ede0066) DRY violation example Amenities - initial version
- [57071ba](https://github.com/tobias-reuter-rocket/clean-code-session/commit/57071ba) DRY violation example Amenities - branching
- [e88d22b](https://github.com/tobias-reuter-rocket/clean-code-session/commit/e88d22b) DRY violation example Amenities - simple improvements
- [72da651](https://github.com/tobias-reuter-rocket/clean-code-session/commit/72da651) DRY violation example Amenities - extension in parallel
- [cd7bd50](https://github.com/tobias-reuter-rocket/clean-code-session/commit/cd7bd50) DRY violation example Amenities - merged and broken version

You can also see everything in one file: [AmenitiesBuilderFlat](AmenitiesBuilderFlat.php)


## Solve DRY violation through refactoring

Check the details of the pull request
https://github.com/tobias-reuter-rocket/clean-code-session/pull/1/commits/ab47125b3ca12a3708ca8f23bc1da04fe5e1775d

Use the "next" button in the upper right area to browse through the changes.

You will see the following steps as commits:

- [ab47125](https://github.com/tobias-reuter-rocket/clean-code-session/commit/ab47125) DRY Amenities / refactoring solution branch - extract method refactoring
- [a6691a3](https://github.com/tobias-reuter-rocket/clean-code-session/commit/a6691a3) DRY Amenities / refactoring solution branch - extract parameter refactoring
- [b4ae671](https://github.com/tobias-reuter-rocket/clean-code-session/commit/b4ae671) DRY Amenities / refactoring solution branch - remove duplicated code
- [5a35e4d](https://github.com/tobias-reuter-rocket/clean-code-session/commit/5a35e4d) DRY Amenities / refactoring solution branch - add more amenities
- [3ca6275](https://github.com/tobias-reuter-rocket/clean-code-session/commit/3ca6275) DRY Amenities / refactoring solution branch - apply improvement


# Theoretical background

## Red grade
- http://clean-code-developer.de/die-grade/roter-grad/
- red grade can be practiced without precondition in every environment
- Principles
    - **DRY**
    - KISS
    - Favor composition over inheritance
    - premature optimisation
- Practices
    - boy scout rule
    - root cause analysis
    - use a VCS
    - **simple refactoring**
    - reflect daily

## DRY - Don't Repeat Yourself
- https://en.wikipedia.org/wiki/Don%27t_repeat_yourself
- php documentation link
  - http://php.net/manual/en/function.htmlspecialchars.php
- see code example

## Simple and save refactoring
- extract method
- extract parameter
