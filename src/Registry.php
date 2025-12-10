<?php
declare(strict_types=1);
namespace FediE2EE\PKD\Extensions;

use FediE2EE\PKD\Extensions\AuxDataTypes\{
    AgeV1,
    SshV2
};

/**
 * @api
 */
class Registry
{
    protected array $aliases;
    protected array $auxDataTypes = [];

    /** @api */
    public function __construct()
    {
        foreach ($this->getAuxDataTypeClasses() as $class) {
            $instance = new $class();
            $this->auxDataTypes[$instance->getAuxDataType()] = $instance;
        }
        $this->aliases = [
            'age' => AgeV1::AUX_DATA_TYPE,
            'ssh' => SshV2::AUX_DATA_TYPE,
        ];
    }

    /**
     * @api
     * @param ExtensionInterface $extension
     * @param ?string $alias
     * @return static
     *
     * @throws ExtensionException
     */
    public function addAuxDataType(ExtensionInterface $extension, ?string $alias = null): static
    {
        $type = $extension->getAuxDataType();
        if (array_key_exists($type, $this->auxDataTypes)) {
            if (!$this->auxDataTypes[$type] instanceof $extension) {
                throw new ExtensionException('Extension already registered');
            }
        }
        $this->auxDataTypes[$type] = $alias;
        if (!is_null($alias)) {
            $this->aliases[$alias] = $type;
        }
        return $this;
    }

    /**
     * @return class-string[]
     */
    public function getAuxDataTypeClasses(): array
    {
        return [AgeV1::class, SshV2::class];
    }

    /**
     * @api
     * @param string $auxDataTypeOrAlias
     * @param string[] $allowList
     * @return ExtensionInterface
     * @throws ExtensionException
     */
    public function get(string $auxDataTypeOrAlias, array $allowList): ExtensionInterface
    {
        $lookup = $this->lookup($auxDataTypeOrAlias);
        if (!in_array($lookup->getAuxDataType(), $allowList, true)) {
            throw new ExtensionException('Type not found in allow-list: ' . $auxDataTypeOrAlias);
        }
        return $lookup;
    }

    /**
     * Get a specific auxiliary data extension by its type identifier or alias
     *
     * @param string $auxDataTypeOrAlias
     * @return ExtensionInterface
     * @throws ExtensionException
     */
    public function lookup(string $auxDataTypeOrAlias): ExtensionInterface
    {
        if (array_key_exists($auxDataTypeOrAlias, $this->aliases)) {
            $type = $this->aliases[$auxDataTypeOrAlias];
        } else {
            $type = $auxDataTypeOrAlias;
        }
        if (!array_key_exists($type, $this->auxDataTypes)) {
            throw new ExtensionException('Type not registered: ' . $auxDataTypeOrAlias);
        }
        return $this->auxDataTypes[$type];
    }
}
