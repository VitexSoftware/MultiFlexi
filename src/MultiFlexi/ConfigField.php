<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi;

/**
 * Description of ConfigField.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class ConfigField
{
    private string $code;
    private string $name;
    private string $description;
    private string $hint;
    private string $source = '';
    private string $note = '';
    private ?string $value;
    private string $type;
    private ?string $defaultValue = null;
    private bool $required = false;
    private bool $isSecret = false; // New property to mark sensitive content
    private bool $isManual = true;
    private bool $multiLine = false;
    private ?string $logo = null; // Add logo property
    private bool $isExpiring = false; // New property to mark expiring fields

    /**
     * ConfigField constructor.
     *
     * @param string $code        Field code
     * @param string $type        Field type
     * @param string $name        Field name
     * @param string $description Field description
     * @param string $hint        Field hint
     * @param string $value       Field value
     */
    public function __construct(string $code, string $type, string $name = '', string $description = '', string $hint = '', ?string $value = null)
    {
        $this->setCode($code);
        $this->setType($type);
        $this->setName(empty($name) ? $code : $name);
        $this->setDescription($description);
        $this->setHint($hint);
        $this->setValue($value);
    }

    /**
     * Set name of the field.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Summary of getName.
     */
    public function getName(): string
    {
        return $this->name;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get Field description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Set Hint for Field.
     */
    public function setHint(string $hint): self
    {
        $this->hint = $hint;

        return $this;
    }
    public function getHint(): string
    {
        return $this->hint;
    }
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Column Key.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }
    public function getSource(): string
    {
        return $this->source;
    }
    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * Recognized config fields types.
     *
     * @param string $type one of 'string', 'file-path', 'email', 'url', 'integer', 'float', 'bool'
     *
     * @throws \InvalidArgumentException
     */
    public function setType(string $type): self
    {
        $allowedTypes = ['string', 'file-path', 'email', 'url', 'integer', 'float', 'bool', 'password', 'set', 'text'];

        if (!\in_array($type, $allowedTypes, true)) {
            throw new \InvalidArgumentException("Invalid type: {$type}. Allowed types are: ".implode(', ', $allowedTypes));
        }

        $this->type = $type;

        return $this->setMultiLine($type === 'text');
    }
    public function getType(): string
    {
        return $this->type;
    }

    public function setDefaultValue(?string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Optional default value.
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Set whether the field contains sensitive content.
     */
    public function setSecret(bool $isSecret): self
    {
        $this->isSecret = $isSecret;

        return $this;
    }

    /**
     * Check if the field contains sensitive content.
     */
    public function isSecret(): bool
    {
        return $this->isSecret;
    }

    /**
     * Set whether the field value is populated manually.
     */
    public function setManual(bool $isManual): self
    {
        $this->isManual = $isManual;

        return $this;
    }

    /**
     * Is the field populated manually ?
     */
    public function isManual(): bool
    {
        return $this->isManual;
    }

    /**
     * Set whether the field should use a textarea (multi-line input).
     */
    public function setMultiLine(bool $multiLine): self
    {
        $this->multiLine = $multiLine;

        return $this;
    }

    /**
     * Check if the field should use a textarea (multi-line input).
     */
    public function isMultiLine(): bool
    {
        return $this->multiLine;
    }

    /**
     * Set logo (unicode emoticon or image file name).
     */
    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo (unicode emoticon or image file name).
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * Set whether the field is expiring (e.g., token or credential with expiration).
     */
    public function setExpiring(bool $isExpiring): self
    {
        $this->isExpiring = $isExpiring;

        return $this;
    }

    /**
     * Check if the field is expiring.
     */
    public function isExpiring(): bool
    {
        return $this->isExpiring;
    }

    /**
     * Get the configuration field as an array.
     *
     * @return array<string, bool|string>
     */
    public function getArray(): array
    {
        return [
            'keyname' => $this->getCode(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'hint' => $this->getHint(),
            'value' => $this->getValue(),
            'type' => $this->getType(),
            'defval' => $this->getDefaultValue(),
            'required' => $this->isRequired(),
            'source' => $this->getSource(),
            'note' => $this->getNote(),
            'secret' => $this->isSecret(),
            'manual' => $this->isManual(),
            'multiline' => $this->isMultiLine(), // Added multiLine to array
            'expiring' => $this->isExpiring(), // Added isExpiring to array
        ];
    }
}
