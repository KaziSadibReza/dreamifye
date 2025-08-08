# Single Page Checkout Templates

This directory contains the template files for the Single Page Checkout functionality.

## Template Structure

```
templates/single-page-checkout/
├── main.php                 # Main container template
├── products-section.php     # Products listing section
├── product-item.php         # Individual product item
├── cart-checkout-section.php# Cart and checkout wrapper
├── cart-items.php          # Cart items display
└── javascript.php          # JavaScript functionality
```

## Template Usage

Templates are loaded using the `SinglePageCheckout::load_template()` method:

```php
// Load a template
echo SinglePageCheckout::load_template('template-name');

// Load with variables
echo SinglePageCheckout::load_template('template-name', array(
    'variable1' => 'value1',
    'variable2' => 'value2'
));
```

## Available Variables

### Global Variables (available in all templates):

- `$atts` - Shortcode attributes array

### Template-specific Variables:

- **products-section.php**: `$atts` (shortcode attributes)
- **product-item.php**: `$product` (WooCommerce product object)
- **cart-items.php**: No additional variables

## Customization

To customize any template:

1. Copy the template file you want to modify
2. Edit the template as needed
3. The changes will be reflected automatically

## Template Hooks

You can add WordPress hooks in any template:

```php
// In any template file
do_action('spc_before_product_item', $product);
// Template content
do_action('spc_after_product_item', $product);
```

## Security

All templates include security checks:

- Direct access prevention
- Proper data escaping
- Input sanitization

## File Naming Convention

- Use lowercase letters and hyphens
- No file extensions in load_template() calls
- Templates must have .php extension
