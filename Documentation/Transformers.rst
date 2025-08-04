:navigation-title: Complex Arguments / Transformers

..  _transformers:

========================
What are Transformers?
========================

Transformers are a way to document more complex arguments in your Storybook stories.

The big problem with complex arguments is that they are not easily documented in the Storybook UI.
Storybook only supports basic types like `string`, `number`, `boolean`, etc.

The Storybook extension also automaticall creates Selects for PHP enums and sets the Storybook type to `select`.
For :php:`DateTime`, :php:`DateTimeImmutable` and :php:`DateTimeInterface` it creates a date picker.

for all other complex Types we need a Transformer to convert *basic* types to a more complex type.

.. _typeTransformer:

TypeTransformer
===========

The TYPO3 object :php:`\TYPO3\CMS\Core\Http\Uri` is one of the complex types.
As Storybook sends all Objects from JS-Browserland via JSON to PHP, we dont get the native PHP object.

EXT:storybook provides a Transformer for the :php:`\TYPO3\CMS\Core\Http\Uri` object.

it looks like this:

.. code-block:: php
   :linenos:

    ...

    use Andersundsehr\Storybook\Transformer\Attribute\TypeTransformer;
    use TYPO3\CMS\Core\Http\Uri;

    class ...
    {
        #[TypeTransformer(priority: 100)]
        public function uri(string $url): Uri
        {
            return new Uri($url);
        }
    }


As you can see the Transformers can be really simple.
A TypeTransformer is any Function on any Service that has the `#[TypeTransformer]` attribute.
It can take any number of arguments, the argument types need to be basic types like `string`, `int`, `bool`, etc. or `DateTime`, `DateTimeImmutable`, `DateTimeInterface`, or any :php:`UnitEnum`.

The return type of the function is the complex type that you want to transform to.

All types are required. As that information is used to generate the Storybook UI Controls.

.. _priorityOfTypeTransformers:

priority of TypeTransformers
-----------

You can order the `TypeTransformers` by priority, highest number wins.

To debug the `TypeTransformers` used you can take a look in the TYPO3 Backend Module `System -> Configuration` and select the `EXT:storybook TypeTransformers` Configuration.
There you can see all registered TypeTransformers and their priorities.

.. _argumentTransformers:

Per Argument Transformers
===========

Sometimes we don't want to transform the arguments by type but by the argument name.

For that we can define a `*.transformers.php` file in the same directory as the `*.html` file.

.. literalinclude:: /dummy-project/src/extensions/dummy_extension/Components/TransformerExample/TransformerExample.transformer.php
   :caption: Components/TransformerExample/TransformerExample.transformer.php
   :linenos:

Dependency Injection for Argument Transformers
-----------

You can inject any `public service <https://docs.typo3.org/permalink/t3coreapi:errors-resulting-from-wrong-configuration>`_ into the argument transformer.
For example see above `fileWithDefault`.
You can inject a service that fetches data from an API to populate your objects.
