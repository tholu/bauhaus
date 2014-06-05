<?php

namespace KraftHaus\Bauhaus\Field;

/**
 * This file is part of the KraftHaus Bauhaus package.
 *
 * (c) KraftHaus <hello@krafthaus.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use KraftHaus\Bauhaus\Field\BaseField;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * Class HasManyField
 * @package KraftHaus\Bauhaus\Field
 */
class HasManyField extends BaseField
{

	/**
	 * Holds the display field name.
	 * @var string
	 */
	protected $displayField;

	/**
	 * Set the display field name.
	 *
	 * @param  string $displayField
	 *
	 * @access public
	 * @return BelongsToField
	 */
	public function display($displayField)
	{
		$this->displayField = $displayField;
		return $this;
	}

	/**
	 * Get the display field name.
	 *
	 * @access public
	 * @return string
	 */
	public function getDisplayField()
	{
		return $this->displayField;
	}

	/**
	 * Render the field.
	 *
	 * @access public
	 * @return mixed|string
	 */
	public function render()
	{
		switch ($this->getContext()) {
			case BaseField::CONTEXT_LIST:
				$model = $this->getName();

				$values = [];
				foreach ($this->getValue() as $item) {
					$values[$item->id] = $item->{$this->getDisplayField()};
				}

				return implode(', ', $values);

				break;
			case BaseField::CONTEXT_FORM:

				$baseModel = $this->getAdmin()->getModel();
				$baseModel = new $baseModel;

				$relatedModel = $baseModel->{$this->getName()}()->getRelated();
				$relatedModel = get_class($relatedModel);

				$items = [];
				foreach ($relatedModel::all() as $item) {
					$items[$item->id] = $item->{$this->getDisplayField()};
				}

				$id = $this->getAdmin()->getFormBuilder()->getIdentifier();

				$values = [];
				foreach ($baseModel::where(Str::singular($baseModel->getTable()) . '_id', $id)->get() as $item) {
					$values[] = (string) $item->id;
				}

				return View::make('krafthaus/bauhaus::models.fields._has_many')
					->with('field',  $this)
					->with('items',  $items)
					->with('values', $values);

				break;
		}
	}

	public function postUpdate($input)
	{
		$model = $this->getName();
		$self  = $this->getAdmin()->getModel();

		foreach ($input[$model] as $item) {
			$model::find($item)->update([strtolower($self) . '_id' => $this->getAdmin()->getFormBuilder()->getIdentifier()]);
		}
	}

}