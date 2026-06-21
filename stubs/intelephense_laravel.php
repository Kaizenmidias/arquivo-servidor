<?php

namespace {
    if (!function_exists('config')) {
        function config(?string $key = null, mixed $default = null): mixed
        {
            return $default;
        }
    }

    if (!function_exists('url')) {
        function url(?string $path = null): string
        {
            return (string) ($path ?? '');
        }
    }

    if (!function_exists('collect')) {
        function collect(iterable|array|null $value = null): \Illuminate\Support\Collection
        {
            return new \Illuminate\Support\Collection();
        }
    }

    if (!function_exists('auth')) {
        function auth(?string $guard = null): object
        {
            return new class {
                public function id(): int|null
                {
                    return null;
                }
            };
        }
    }

    if (!function_exists('rescue')) {
        function rescue(callable $callback, mixed $rescue = null, bool $report = true): mixed
        {
            try {
                return $callback();
            } catch (\Throwable) {
                return $rescue;
            }
        }
    }

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

namespace Illuminate\Database\Migrations {
    abstract class Migration
    {
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
        public static function query(): object
        {
            return new class {
                public function whereIn(string $column, array $values): static
                {
                    return $this;
                }

                public function where(string $column, mixed $operator = null, mixed $value = null): static
                {
                    return $this;
                }

                public function get(array $columns = ['*']): \Illuminate\Support\Collection
                {
                    return new \Illuminate\Support\Collection();
                }

                public function keyBy(string $keyBy): \Illuminate\Support\Collection
                {
                    return new \Illuminate\Support\Collection();
                }
            };
        }

        public static function create(array $attributes = []): static
        {
            return new static();
        }

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

namespace Illuminate\Support {
    class Collection implements \IteratorAggregate, \Countable
    {
        public function filter(?callable $callback = null): static { return $this; }
        public function map(callable $callback): static { return $this; }
        public function values(): static { return $this; }
        public function unique(?string $key = null): static { return $this; }
        public function when(mixed $value, callable $callback, ?callable $default = null): static { return $this; }
        public function prepend(mixed $value, mixed $key = null): static { return $this; }
        public function count(): int { return 0; }
        public function all(): array { return []; }
        public function get(mixed $key, mixed $default = null): mixed { return $default; }
        public function reject(callable $callback): static { return $this; }
        public function has(mixed $key): bool { return false; }
        public function isEmpty(): bool { return true; }
        public function keyBy(string $keyBy): static { return $this; }
        public function getIterator(): \Traversable { return new \ArrayIterator([]); }
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

        public function path(string $path): string
        {
            return $path;
        }

        public function put(string $path, mixed $contents = '', mixed $options = []): bool
        {
            return true;
        }

        public function size(string $path): int
        {
            return 0;
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

    class DB
    {
        public static function table(string $table): object
        {
            return new class {
                public function whereIn(string $column, array $values): static { return $this; }
                public function where(string $column, mixed $operator = null, mixed $value = null): static { return $this; }
                public function update(array $values): int { return 0; }
                public function transaction(callable $callback): mixed { return $callback(); }
            };
        }

        public static function transaction(callable $callback): mixed
        {
            return $callback();
        }
    }

    class Http
    {
        public static function timeout(int $seconds): static
        {
            return new static();
        }

        public function post(string $url, array $data = []): object
        {
            return new \stdClass();
        }
    }
}

namespace {
    class Imagick
    {
        public const FILTER_LANCZOS = 1;

        public function readImage(string $filename): bool { return true; }
        public function getNumberImages(): int { return 1; }
        public function autoOrient(): bool { return true; }
        public function stripImage(): bool { return true; }
        public function getImageWidth(): int { return 0; }
        public function getImageHeight(): int { return 0; }
        public function resizeImage(int $columns, int $rows, int $filter, float $blur, bool $bestfit = false): bool { return true; }
        public function setImageFormat(string $format): bool { return true; }
        public function setImageCompressionQuality(int $quality): bool { return true; }
        public function getImagesBlob(): string { return ''; }
        public function clear(): bool { return true; }
        public function destroy(): bool { return true; }
    }
}
