# Guess the sorting

## Live exercise

- everyone gets a PostIt and a pen
- take ten minutes to write down the output of the method
- let's group the results on the wall
- discuss how the different versions came up

- reveal the output (run it or show the results below)
- you may also try to test all PHP versions on https://3v4l.org/
- discuss again

## Outcome of the first session

- 10 minutes are not enough to understand it, executing and var_dump'ing it would help a lot
- certain "side effects" are not stable throughout the versions
- keeping PHP versions in sync on vagrant, staging, live and CI
- unit test would help to understand and prevent issues when upgrading


## Results

Output for hhvm-3.9.1 - 3.12.0, 7.0.0 - 7.1.0beta3
    
    2 x Abc
    2 x Incubator
    2 x Pocket
    1 x Berlin
    1 x Internet
    1 x Startup

Output for 4.3.0 - 5.6.25

    2 x Pocket
    2 x Abc
    2 x Incubator
    1 x Startup
    1 x Berlin
    1 x Internet


## Explanations

> ## Sorting Arrays
>  PHP has several functions that deal with sorting arrays, and this document exists to help sort it all out.
>  The main differences are:
>  - ...
>  - If any of these sort functions evaluates two members as equal then **the order is undefined** (the sorting is not stable).
http://php.net/manual/en/array.sorting.php


**the order is undefined** -> You must not rely on the given order as it might change from one PHP version to another.

See also:
- http://stackoverflow.com/questions/37941808/php-7-usort-adds-equal-items-to-end-of-array-where-in-php-5-it-adds-to-the-begin

