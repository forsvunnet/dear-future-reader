# PHP preferences

declare(strict_types=1) always.

Don't use final.

Don't use class constants. We don't want extra indirection or hidden coupling.

Each feature outside the core app goes in its own domain. A domain knows only itself and the app. The app does not know any domain. Domains talk to each other through interfaces in the app. You can remove or replace a domain, and the app still works.

Composition over inheritance.

Use an interface when there are two or more implementations, when a second one is likely, or in front of an outside system.

Classes serve one named purpose. A class should never go beyond what it's name suggest. Some classes orchestrate and put things together. Other have a more singular more specified purpose.

Classes that do work have one main method, `__invoke`, and maybe a few helpers. A helper can be public, for example so a test can call it. When another class uses a helper, move the helper to its own class. Classes that hold data, like value objects and models, can have many methods. Each method must still fit the class name.

Organisation over brevity. Classes (files) are cheap to make. Make a new one instead of cramming more features into an existing one.

Arrays with named keys that leave a method should be DTOs. This includes JSON columns.

DTOs should usually not have methods. They carry data only. Transformations of data belong in other classes.

Prefer immutability.

Enums are good.

A general utility, like currency conversion, can be a global helper function. Specific work goes in a class.

Use a result object for work that can fail in expected ways, like parsing. Return the data and the error messages together. Throw exceptions only for unexpected failures. Returning `null` is acceptable, but a result object is better.

# Laravel

Prefer helper functions over facades. A helper is a plain function you can find and read. A facade can work through a global class alias, with no `use` statement to show where it comes from.

Inject dependencies through the constructor. Do not get them with `app()` inside a method. The constructor shows what a class depends on.

# Global

 * No new dependency if it can be avoided.
 * No boilerplate nobody asked for.
 * Deletion over addition. Boring over clever.
 * Avoid "magic"

Long prose, like LLM prompts, goes in its own file. Keep it in the class only when it is short.

Use tests to lock in good behaviour and to prove bugs. To fix a bug, first write a test that fails because of the bug. Then fix the bug and watch the test pass.

Don't write tests for the sake of testing.

# Code smells to avoid

* Nested if statements  
Return early to keep the code flat.
* Tight coupling  
One change should not force many changes.
