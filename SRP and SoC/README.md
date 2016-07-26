# Single responsibility principle

“A class should have only one reason to change.” – Robert C. Martin

Examples from SR:

  - AddressFormatter
    - Address entity used to be responsible for both storing data and formatting them
    - I18N was awful
  - ContentService
    - it used to handle CRUD, complicated caching, processing images and processing rich text formats
    - complexity reduced, less complains from PHPMD

# Separation of concerns

Concerns are the different aspects of software functionality.
For instance, the "business logic" of software is a concern,
and the interface through which a person uses this logic is another.

  - MVC
  - business logic in services, slim controllers
  - services separate from repositories (for instance, if we were to switch ORM, ideally we should adjust only repos and entities)
  - no inline JS/CSS

Examples from SR:

  - MessageService acts both as a repository and as a message sender
  - RoleForm directly accesses ORM, has to know about details of the entity

In the `mal` directory there's also a drastic example of not having any SoC whatsoever ;)
