<?php
 declare(strict_types=1);

if(!class_exists('PrettyPrint')) {



/**
 * ============================================================
 * PrettyPrint
 * ============================================================
 * Generic formatter for scalars, arrays, and objects.
 */
class PrettyPrint
{
    protected int $maxDepth;

    public function __construct(int $maxDepth = 5)
    {
        $this->maxDepth = $maxDepth;
    }

    public function format($value, int $depth = 0): string
    {
        if ($depth > $this->maxDepth) {
            return '[Max depth reached]';
        }

        if (is_array($value)) {
            return $this->formatArray($value, $depth);
        }

        if (is_object($value)) {
            return $this->formatObject($value, $depth);
        }

        return $this->formatScalar($value);
    }

    protected function formatArray(array $array, int $depth): string
    {
        $out = "[\n";
        foreach ($array as $key => $value) {
            $out .= str_repeat('  ', $depth + 1)
                . $key . ' => '
                . $this->format($value, $depth + 1)
                . "\n";
        }
        return $out . str_repeat('  ', $depth) . ']';
    }

    protected function formatObject(object $object, int $depth): string
    {
        $out = get_class($object) . " {\n";
        foreach ((array) $object as $key => $value) {
            $out .= str_repeat('  ', $depth + 1)
                . $key . ' => '
                . $this->format($value, $depth + 1)
                . "\n";
        }
        return $out . str_repeat('  ', $depth) . '}';
    }

    protected function formatScalar($value): string
    {
        if (is_string($value)) {
            return '"' . $value . '"';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value === null) {
            return 'null';
        }
        return (string) $value;
    }
}
}