<?php

namespace Drupal\lightgallery\Field;

/**
 * Field base.
 */
abstract class FieldBase implements FieldInterface {

  protected $name;
  protected $title;
  protected $type;
  protected $description;
  protected $isRequired;
  protected $group;
  protected $defaultValue;
  protected $options;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->name = $this->setName();
    $this->title = $this->setTitle();
    $this->type = $this->setType();
    $this->description = $this->setDescription();
    $this->isRequired = $this->setIsRequired();
    $this->group = $this->setGroup();
    $this->defaultValue = $this->setDefaultValue();
    $this->options = $this->setOptions();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function isRequired() {
    return $this->isRequired;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup() {
    return $this->group;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue() {
    return $this->defaultValue;
  }

  /**
   * {@inheritdoc}
   */
  public function appliesToViews() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function appliesToFieldFormatter() {
    return TRUE;
  }

  /**
   * Sets required flag.
   */
  protected function setIsRequired() {
    return FALSE;
  }

  /**
   * Sets default value.
   */
  protected function setDefaultValue() {
    return TRUE;
  }

  /**
   * Sets options.
   */
  protected function setOptions() {
    return FALSE;
  }

  /**
   * Sets name.
   */
  abstract protected function setName();

  /**
   * Sets title.
   */
  abstract protected function setTitle();

  /**
   * Sets type.
   */
  abstract protected function setType();

  /**
   * Sets description.
   */
  abstract protected function setDescription();

  /**
   * Sets group.
   */
  abstract protected function setGroup();

}
