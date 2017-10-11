<?php

// Interface segregation principle

// Many client-specific interfaces are better than one general-purpose interface.

interface LoadableResource
{
    public function load();
}

interface PersistableResource
{
    public function persist();
}

class Settings implements LoadableResource, PersistableResource
{
    public function load()
    {
        // TODO: Implement load() method.
    }

    public function persist()
    {
        // TODO: Implement persist() method.
    }
}

class UserConfig implements LoadableResource, PersistableResource
{
    public function load()
    {
        // TODO: Implement load() method.
    }

    public function persist()
    {
        // TODO: Implement persist() method.
    }
}

class ReadOnlyResource implements LoadableResource
{
    public function load()
    {
        // TODO: Implement load() method.
    }
}

$resources = [
    new Settings(),
    new UserConfig(),
    new ReadOnlyResource(),
];

foreach ($resources as $resource) {
    if ($resource instanceof LoadableResource) {
        $resource->load();
    }
}

// some stuff happens...

foreach ($resources as $resource) {
    if ($resource instanceof PersistableResource) {
        $resource->persist();
    }
}
