## MVC
* model: Domain model or Transaction Script
* view: Template view - in most of the time plain html with an integrated template engine

## controller:

### PageController

One controller per page or action
Use the Page Controller pattern to accept input from the page request, invoke the requested actions on the model, and determine the correct view to use for the page.

### FrontController

Centralized control
Front Controller coordinates all of the requests that are made to the Web application. The solution describes using a single controller instead of the distributed model used in Page Controller. This single controller is in the perfect location to enforce application-wide policies, such as security and usage tracking.

## Problem: Fat controllers

The MVC pattern often focuses primarily on the separation between the model and the view, while paying less attention on controllers. In many rich-client scenarios, the separation between controller and view is less critical

### What to do
- Refactoring
- code to Widgets
- Services


### Code to Widgets

**Good**
- Blocks can be reused
- Inheritance
- good for homepage
- can be covered with integration tests
**Bad**
- not visible from the action which widgets are used
- the same data could be requested several times
- you need to know where to stop
- not junior friendly

### Code to Services
**Good**
- Test coverage
- Business logic in a business logic level
**Bad**
- Not always possible - links with view or combination of services
- takes time
- Code samples are here:
