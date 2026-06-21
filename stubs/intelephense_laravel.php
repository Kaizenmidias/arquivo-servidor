<?php

namespace {
    if (!function_exists('env')) {
        function env(string $key, mixed $default = null): mixed
        {
            return $default;
        }
    }

    if (!function_exists('now')) {
        function now(): \DateTimeImmutable
        {
            return new \DateTimeImmutable();
        }
    }
}

namespace Illuminate\Contracts\Queue {
    interface ShouldQueue
    {
    }
}

namespace Illuminate\Foundation\Queue {
    trait Queueable
    {
    }
}

namespace Illuminate\Database\Eloquent\Relations {
    class BelongsTo
    {
    }
}

namespace Illuminate\Database\Eloquent {
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    abstract class Model
    {
        public static function findOrFail(mixed $id, array|string $columns = ['*']): static
        {
            return new static();
        }

        public function update(array $attributes = [], array $options = []): bool
        {
            return true;
        }

        protected function belongsTo(
            string $related,
            ?string $foreignKey = null,
            ?string $ownerKey = null,
            ?string $relation = null
        ): BelongsTo {
            return new BelongsTo();
        }
    }
}

namespace Illuminate\Filesystem {
    class FilesystemAdapter
    {
        public function delete(string|array $paths): bool
        {
            return true;
        }

        public function url(string $path): string
        {
            return $path;
        }
    }
}

namespace Illuminate\Support\Facades {
    use Illuminate\Filesystem\FilesystemAdapter;

    class Log
    {
        public static function info(string $message, array $context = []): void
        {
        }

        public static function error(string $message, array $context = []): void
        {
        }
    }

    class Storage
    {
        public static function disk(?string $name = null): FilesystemAdapter
        {
            return new FilesystemAdapter();
        }
    }
}
